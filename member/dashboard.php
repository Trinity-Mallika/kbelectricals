<?php include('session.php');

$member_id = $_SESSION['member_id'];
$chapter_id = $_SESSION['chapter_id'];
$member = $obj->executequery("SELECT * FROM user  WHERE userid='$member_id'")[0];

$totalAttendance = $obj->getvalfield(
    'bni_attendance',
    'count(*)',
    "userid='$member_id'"
);

// This month's attendance: distinct days present vs total days in month
$thisMonth    = date('Y-m');                       // e.g. "2026-07"
$thisMonthNum = (int)date('n');                    // 1-12
$thisYearNum  = (int)date('Y');
$totalDaysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, $thisMonthNum, $thisYearNum);
$daysPresent = (int)$obj->getvalfield(
    'bni_attendance',
    'COUNT(DISTINCT DATE(scan_time))',
    "userid='$member_id' AND DATE_FORMAT(scan_time, '%Y-%m')='$thisMonth'"
);

// Late days this month: count distinct days where first scan_time exceeded shift grace window
$shiftForLate = $obj->getShift($member['shift_id'] ?? null);
$graceInLate  = (int)(($shiftForLate['grace_in_minutes'] ?? $shiftForLate['grace_minutes']) ?? 0);
$monthDayRows = $obj->executequery("
    SELECT DATE(scan_time) AS day, MIN(scan_time) AS first_in
    FROM bni_attendance
    WHERE userid = '$member_id'
      AND DATE_FORMAT(scan_time, '%Y-%m') = '$thisMonth'
    GROUP BY DATE(scan_time)
");
$lateDays = 0;
foreach ($monthDayRows as $row) {
    $dayStartTs = strtotime($row['day'] . ' ' . $shiftForLate['start_time']) + ($graceInLate * 60);
    if (strtotime($row['first_in']) > $dayStartTs) {
        $lateDays++;
    }
}

$latestMeeting = $obj->executequery("
    SELECT m.*, c.company_name AS shop_name
    FROM bni_meetings m
    LEFT JOIN company_setting c ON c.company_id = m.company_id
    WHERE m.status = 1
    ORDER BY m.meeting_id DESC
    LIMIT 1
");

/* =========================================================
   Today's sessions for this employee (multi-session model)
========================================================= */

$today = date('Y-m-d');

$todaySessions = $obj->executequery("
    SELECT
        a.attendance_id,
        a.shop_id,
        a.scan_time AS in_time,
        a.out_time,
        a.type,
        s.company_name AS shop_name
    FROM bni_attendance a
    LEFT JOIN company_setting s ON s.company_id = a.shop_id
    WHERE a.userid = '$member_id'
      AND DATE(a.scan_time) = '$today'
    ORDER BY a.attendance_id ASC
");

// Compute aggregate state
$hasOpen        = false;
$workedSec      = 0;
$firstInTs      = null;
$lastOutTs      = null;

foreach ($todaySessions as $s) {
    $inTs  = strtotime($s['in_time']);
    $outTs = $s['out_time'] ? strtotime($s['out_time']) : null;

    if ($firstInTs === null) $firstInTs = $inTs;
    if ($outTs !== null) {
        $lastOutTs  = $outTs;
        $workedSec += ($outTs - $inTs);
    } else {
        $hasOpen = true;
    }
}

$todayState = empty($todaySessions) ? 'absent' : ($hasOpen ? 'in' : 'out');

// Shift comparisons (loaded from shift_master table per employee)
$shift = $obj->getShift($member['shift_id'] ?? null);
$shiftStartTs = strtotime($today . ' ' . $shift['start_time']);
$shiftEndTs   = strtotime($today . ' ' . $shift['end_time']);

// Split grace: separate IN grace and OUT grace (with legacy fallback)
$graceInSec  = (int)(($shift['grace_in_minutes']  ?? $shift['grace_minutes']) ?? 0) * 60;
$graceOutSec = (int)(($shift['grace_out_minutes'] ?? $shift['grace_minutes']) ?? 0) * 60;

// Expected work hours for the shift (e.g. 8.00)
$expectedWorkSec = (float)($shift['expected_work_hours'] ?? 0) * 3600;

// Late: only count if IN > (shift_start + grace_in).
// Reported lateness is measured from (shift_start + grace_in), not shift_start.
$lateSec  = ($firstInTs && $firstInTs > ($shiftStartTs + $graceInSec))
          ? ($firstInTs - ($shiftStartTs + $graceInSec))
          : 0;

// Early: only count if OUT < (shift_end - grace_out).
// Reported early-leave is measured from (shift_end - grace_out), not shift_end.
$earlySec = ($lastOutTs && $lastOutTs < ($shiftEndTs - $graceOutSec))
          ? (($shiftEndTs - $graceOutSec) - $lastOutTs)
          : 0;

// Short hours: only when all sessions closed, compare worked vs expected
$shortSec = (!$hasOpen && $expectedWorkSec > 0 && $workedSec < $expectedWorkSec)
          ? ($expectedWorkSec - $workedSec)
          : 0;

// Day status for salary: complete / incomplete / pending / absent
$dayStatus = 'absent';
if (!empty($todaySessions)) {
    if ($hasOpen) {
        $dayStatus = 'pending';
    } elseif ($expectedWorkSec > 0 && $workedSec < $expectedWorkSec) {
        $dayStatus = 'incomplete';
    } else {
        $dayStatus = 'complete';
    }
}

// Shift display strings
$shiftStartDisp = date('h:i A', $shiftStartTs);
$shiftEndDisp   = date('h:i A', $shiftEndTs);
$lunchStartDisp = $shift['lunch_start'] ? date('h:i A', strtotime($today . ' ' . $shift['lunch_start'])) : '—';
$lunchEndDisp   = $shift['lunch_end']   ? date('h:i A', strtotime($today . ' ' . $shift['lunch_end']))   : '—';

// Helper: format seconds as "Xh Ym"
function formatDuration($sec) {
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    if ($h > 0) return "{$h}h {$m}m";
    return "{$m}m";
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Member Dashboard
    </title>

    <link rel="stylesheet"
        href="../admin/assets/font/bootstrap-icons.css">

    <link rel="stylesheet"
        href="../admin/assets/css/bootstrap.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f7fb;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        .container {
            max-width: 430px;
        }

        .top-section {

            background:
                linear-gradient(135deg,
                    #06163a,
                    #287ab1);

            color: #fff;

            border-radius:
                0 0 30px 30px;

            padding:
                20px 14px 70px;
        }

        .profile-box {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .profile-circle {

            height: 50px;
            width: 50px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, .18);

            display: grid;
            place-items: center;

            font-size: 21px;

            flex: 0 0 50px;
        }

        .profile-box h4 {

            font-size: 18px;

            margin: 0;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-box small {

            display: block;

            max-width: 180px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            color: #cde8ff;
        }

        .attendance-success {
            padding: 24px;
            text-align: center;
        }

        .success-icon {

            width: 72px;
            height: 72px;

            border-radius: 22px;

            background:
                linear-gradient(135deg,
                    #e8fff1,
                    #f4fff8);

            color: #16a34a;

            display: grid;
            place-items: center;

            font-size: 34px;

            margin: 0 auto 14px;
        }

        .attendance-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .attendance-text {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .attendance-info {

            background: #f8fbff;

            border-radius: 18px;

            padding: 14px;

            text-align: left;
        }

        .info-row {

            display: flex;

            justify-content: space-between;

            gap: 10px;

            padding: 10px 0;

            border-bottom:
                1px solid #eef2f7;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-row span {
            color: #6c757d;
            font-size: 14px;
        }

        .logout-btn {

            border-radius: 50px;

            font-size: 12px;

            padding: 6px 10px;
        }

        .dashboard-wrapper {

            margin-top: -42px;

            padding-bottom: 20px;
        }

        .card-box {

            border: 0;

            border-radius: 20px;

            background: #fff;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, .08);
        }

        .stat-card {

            text-align: center;

            padding: 14px 8px;

            min-height: 135px;
        }

        .stat-icon {

            height: 38px;
            width: 38px;

            border-radius: 12px;

            display: grid;
            place-items: center;

            margin: auto auto 8px;

            font-size: 19px;
        }

        .stat-card h3 {
            font-size: 22px;
            margin-bottom: 2px;
        }

        .small-label {

            font-size: 13px;
            color: #6c757d;

            word-break: break-word;
        }

        .scan-btn {

            height: 32px;

            border-radius: 16px;

            font-size: 15px;

            font-weight: 600;
        }

        .primary-btn {

            background:
                linear-gradient(135deg,
                    #0d6efd,
                    #287ab1);

            border: 0;
        }

        .meeting-box {

            background: #f7fbff;

            border-radius: 16px;

            padding: 14px;
        }

        .meeting-title {

            font-size: 15px;

            font-weight: 600;

            word-break: break-word;
        }

        .card-box h5 {
            font-size: 20px;
        }

        .card-box p {
            font-size: 16px;
            line-height: 1.45;
        }

        .scan-card {
            padding: 24px;
            text-align: center;
        }

        .scan-icon {
            width: 66px;
            height: 66px;
            border-radius: 22px;
            background: linear-gradient(135deg, #eaf2ff, #f7fbff);
            color: #0d6efd;
            display: grid;
            place-items: center;
            font-size: 32px;
            margin: 0 auto 14px;
        }

        .scan-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .scan-text {
            font-size: 14px !important;
            color: #6c757d;
            line-height: 1.55;
            margin-bottom: 18px;
        }

        .latest-btn {
            height: 3 2px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 600;
            color: #0d6efd;
            background: #f3f8ff;
            border: 1px solid #cfe2ff;
        }

        @media(max-width:420px) {
            .container {
                max-width: 100%;
            }

            .top-section {
                padding-bottom: 65px;
            }

            .stat-card {
                min-height: 125px;
            }

            .card-box h5 {
                font-size: 18px;
            }

            .card-box p {
                font-size: 15px;
            }

            .scan-btn {
                font-size: 14px;
            }
        }
    </style>

</head>

<body>

    <!-- HEADER -->

    <div class="top-section">

        <div class="d-flex justify-content-between align-items-start">

            <div class="profile-box">

                <div class="profile-circle">
                    <i class="bi bi-person"></i>
                </div>

                <div>

                    <h4>
                        Hi,
                        <?php echo $member['fullname']; ?>
                    </h4>

                    <small>

                        <?php
                        echo htmlspecialchars(
                            $member['usertype']
                        );
                        ?>

                    </small>

                </div>

            </div>

            <a href="logout.php"
                class="btn btn-light btn-sm logout-btn">

                <i class="bi bi-box-arrow-right"></i>

                Logout

            </a>

        </div>

    </div>

    <!-- BODY -->

    <div class="container dashboard-wrapper px-3">

        <!-- STATS -->

        <div class="row g-3 mb-3">

            <div class="col-md-3 col-6">
                <a href="my-attendance.php" class="text-decoration-none text-dark">

                    <div class="card-box stat-card">

                        <div class="stat-icon bg-primary-subtle text-primary">

                            <i class="bi bi-check2-square"></i>

                        </div>

                        <h3>

                            <?php echo $totalAttendance; ?>

                        </h3>

                        <div class="small-label">
                            Total Sessions
                        </div>

                    </div>
                </a>
            </div>

            <div class="col-md-3 col-6">

                <a href="my-attendance.php?month=<?php echo $thisMonth; ?>" class="text-decoration-none text-dark">
                    <div class="card-box stat-card">

                        <div class="stat-icon bg-info-subtle text-info">

                            <i class="bi bi-calendar-check"></i>

                        </div>

                        <h3>
                            <?php echo $daysPresent; ?>
                            <small style="font-size:.55em; color:#6c757d; font-weight:500;">
                                / <?php echo $totalDaysInMonth; ?>
                            </small>
                        </h3>

                        <div class="small-label">
                            Days Present
                            <small class="text-muted d-block" style="font-size:10px;">
                                in <?php echo date('F Y'); ?>
                            </small>
                        </div>

                    </div>
                </a>

            </div>

            <div class="col-md-3 col-6">

                <div class="card-box stat-card">

                    <div class="stat-icon bg-success-subtle text-success">

                        <i class="bi bi-stopwatch"></i>

                    </div>

                    <h3>
                        <?php echo formatDuration($workedSec); ?>
                    </h3>

                    <div class="small-label">
                        Today's Hours
                    </div>

                </div>

            </div>

            <div class="col-md-3 col-6">

                <a href="my-attendance.php?month=<?php echo $thisMonth; ?>" class="text-decoration-none text-dark">
                    <div class="card-box stat-card">

                        <div class="stat-icon bg-danger-subtle text-danger">

                            <i class="bi bi-clock-history"></i>

                        </div>

                        <h3>
                            <?php echo $lateDays; ?>
                        </h3>

                        <div class="small-label">
                            Late Days
                            <small class="text-muted d-block" style="font-size:10px;">
                                in <?php echo date('F Y'); ?>
                            </small>
                        </div>

                    </div>
                </a>

            </div>


        </div>

        <!-- QR CARD -->

        <?php
        /* =========================================================
           TODAY'S STATUS (multi-session IN/OUT)
        ========================================================== */
        ?>

        <?php if (!empty($todaySessions)) { ?>

            <!-- TODAY'S SESSIONS CARD -->

            <div class="card-box attendance-success mb-3">

                <div class="success-icon"
                     style="<?php echo $hasOpen ? '' : 'background:linear-gradient(135deg,#fff7ed,#ffedd5);color:#f59e0b;'; ?>">

                    <i class="bi <?php echo $hasOpen ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right'; ?>"></i>

                </div>

                <h5 class="attendance-title">

                    <?php if ($hasOpen): ?>
                        You are Checked IN
                    <?php else: ?>
                        Shift Completed
                    <?php endif; ?>

                    <?php
                    $dayStatusInfo = [
                        'complete'   => ['label' => 'Day Complete',    'class' => 'bg-success',         'icon' => 'bi-check-circle-fill'],
                        'incomplete' => ['label' => 'Day Incomplete',  'class' => 'bg-warning text-dark','icon' => 'bi-exclamation-triangle-fill'],
                        'pending'    => ['label' => 'Day Pending',     'class' => 'bg-info text-dark',   'icon' => 'bi-hourglass-split'],
                        'absent'     => ['label' => '',                'class' => '',                    'icon' => ''],
                    ];
                    $dsi = $dayStatusInfo[$dayStatus] ?? null;
                    if ($dsi && $dsi['label']):
                    ?>
                    <span class="badge <?= $dsi['class'] ?>" style="font-size:.5em; vertical-align:middle; margin-left:8px;">
                        <i class="bi <?= $dsi['icon'] ?>"></i> <?= $dsi['label'] ?>
                    </span>
                    <?php endif; ?>

                </h5>

                <p class="attendance-text">

                    Today's attendance summary

                </p>

                <!-- Pills row: worked hours, late, early -->

                <div class="mb-2" style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">

                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-stopwatch"></i>
                        Worked <?php echo formatDuration($workedSec); ?>
                    </span>

                    <?php if ($lateSec > 0): ?>
                        <span class="badge bg-danger-subtle text-danger">
                            <i class="bi bi-clock-history"></i>
                            Late <?php echo formatDuration($lateSec); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($earlySec > 0 && !$hasOpen): ?>
                        <span class="badge bg-warning-subtle text-warning">
                            <i class="bi bi-box-arrow-right"></i>
                            Early <?php echo formatDuration($earlySec); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($shortSec > 0): ?>
                        <span class="badge bg-danger-subtle text-danger">
                            <i class="bi bi-hourglass-bottom"></i>
                            Short by <?php echo formatDuration($shortSec); ?>
                        </span>
                    <?php endif; ?>

                </div>

                <!-- Sessions table -->

                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered align-middle" style="font-size:12px;">
                        <thead class="table-light">
                            <tr>
                                <th width="30">#</th>
                                <th>Shop</th>
                                <th>IN</th>
                                <th>OUT</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todaySessions as $i => $s):
                                $isOpen = empty($s['out_time']);
                                $inTs  = strtotime($s['in_time']);
                                $outTs = $s['out_time'] ? strtotime($s['out_time']) : null;
                                $durSec = $outTs ? ($outTs - $inTs) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php echo $isOpen ? 'bg-success' : 'bg-secondary'; ?>" style="font-size:10px;">
                                            <?php echo $i + 1; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['shop_name'])): ?>
                                            <span style="color:#1a56a0; font-weight:600;">
                                                <i class="bi bi-shop"></i>
                                                <?php echo htmlspecialchars($s['shop_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color:#16a34a; font-weight:600;">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                            <?php echo date('h:i A', $inTs); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isOpen): ?>
                                            <span class="badge bg-success" style="font-size:9px;">
                                                <i class="bi bi-hourglass-split"></i> Open
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#f59e0b; font-weight:600;">
                                                <i class="bi bi-box-arrow-right"></i>
                                                <?php echo date('h:i A', $outTs); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isOpen): ?>
                                            <span class="text-muted">ongoing</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark" style="font-size:10px; font-family:monospace;">
                                                <?php echo formatDuration($durSec); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($hasOpen): ?>
                    <a href="scan.php" class="btn primary-btn text-white scan-btn w-100 mt-3">
                        <i class="bi bi-box-arrow-right"></i>
                        Mark OUT
                    </a>
                <?php else: ?>
                    <a href="scan.php" class="btn primary-btn text-white scan-btn w-100 mt-3">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Mark IN
                    </a>
                <?php endif; ?>

                <?php if ($latestMeeting) { ?>
                    <a href="scan.php?token=<?php echo $latestMeeting[0]['qr_token']; ?>"
                        class="btn latest-btn w-100 mt-3">
                        <i class="bi bi-lightning-charge"></i>
                        Quick Attendance
                    </a>
                <?php } ?>

            </div>

        <?php } else { ?>

            <!-- SCAN CARD (no sessions today) -->

            <div class="card-box scan-card mb-3">

                <div class="scan-icon">

                    <i class="bi bi-qr-code-scan"></i>

                </div>

                <h5 class="scan-title">

                    Mark Your Attendance

                </h5>

                <p class="scan-text">

                    Scan the QR code to mark your IN time. Shift:
                    <b><?php echo $shiftStartDisp; ?> - <?php echo $shiftEndDisp; ?></b>

                </p>

                <a href="scan.php"
                    class="btn primary-btn text-white scan-btn w-100">

                    <i class="bi bi-camera"></i>

                    Scan QR Code

                </a>

                <?php if ($latestMeeting) { ?>

                    <a href="scan.php?token=<?php echo $latestMeeting[0]['qr_token']; ?>"
                        class="btn latest-btn w-100 mt-3">

                        <i class="bi bi-lightning-charge"></i>

                        Quick Attendance

                    </a>

                <?php } ?>

            </div>

        <?php } ?>
        <!-- MEETING -->
    </div>

</body>

</html>