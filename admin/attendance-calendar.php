<?php

include("../adminsession.php");

$title    = "Attendance Calendar";
$pagename = "attendance-calendar.php";
$user_id = intval($_GET['user_id'] ?? 0);
$month     = $_GET['month'] ?? date('Y-m');

// Validate month format (YYYY-MM)
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}

// Default to first active employee if none selected
if (!$user_id) {
    $first = $obj->executequery("
        SELECT userid FROM user
        WHERE status = 1 ORDER BY fullname ASC LIMIT 1
    ");
    if (!empty($first)) {
        $user_id = (int)$first[0]['userid'];
    }
}

$employees = $obj->executequery("
    SELECT userid, fullname,salary
    FROM user 
    WHERE status = 1 and usertype!='admin'
    ORDER BY fullname
");

/* =========================
   FETCH ALL SESSIONS for this member+month, grouped by date
========================= */

$byDate = [];
if ($user_id) {
    $rows = $obj->executequery("
        SELECT a.attendance_id, a.scan_time, a.out_time, a.type,
               a.shop_id, s.company_name AS shop_name
        FROM bni_attendance a
        LEFT JOIN company_setting s ON s.company_id = a.shop_id
        WHERE a.userid = '$user_id'
          AND DATE_FORMAT(a.scan_time, '%Y-%m') = '" . $obj->test_input($month) . "'
        ORDER BY a.scan_time ASC
    ");

    foreach ($rows as $r) {
        $date = substr($r['scan_time'], 0, 10); // YYYY-MM-DD
        $inTs  = strtotime($r['scan_time']);
        $outTs = $r['out_time'] ? strtotime($r['out_time']) : null;

        if (!isset($byDate[$date])) {
            $byDate[$date] = [
                'first_in'    => $r['scan_time'],
                'last_out'    => $r['out_time'],
                'worked_sec'  => 0,
                'sessions'    => 0,
                'has_open'    => false,
                'shops'       => [],
                'all_rows'    => [],
            ];
        }
        if ($outTs) {
            $byDate[$date]['worked_sec'] += ($outTs - $inTs);
            $byDate[$date]['last_out'] = $r['out_time'];
        } else {
            $byDate[$date]['has_open'] = true;
        }
        $byDate[$date]['sessions']++;
        if (!empty($r['shop_name']) && !in_array($r['shop_name'], $byDate[$date]['shops'])) {
            $byDate[$date]['shops'][] = $r['shop_name'];
        }
        $byDate[$date]['all_rows'][] = $r;
    }
}

$empMember = $obj->select_record('user', ['userid' => $user_id]);
$shift = $obj->getShift($empMember['shift_id'] ?? null);
$shiftStartTime = $shift['start_time'];   // "10:30:00"
$shiftEndTime   = $shift['end_time'];     // "19:00:00"
$graceInSec     = (int)(($shift['grace_in_minutes']  ?? $shift['grace_minutes']) ?? 0) * 60;
$graceOutSec    = (int)(($shift['grace_out_minutes'] ?? $shift['grace_minutes']) ?? 0) * 60;
$expectedWorkSec = (float)($shift['expected_work_hours'] ?? 0) * 3600;

list($year, $mon) = explode('-', $month);
$year = (int)$year;
$mon  = (int)$mon;

$totalDays        = function_exists('cal_days_in_month')
                     ? (int)cal_days_in_month(CAL_GREGORIAN, $mon, $year)
                     : (int)date('t', strtotime("$year-$mon-01"));
$firstDayOfWeek   = (int)date('N', strtotime("$year-$mon-01")); 
$today            = date('Y-m-d');

$firstDayCol = $firstDayOfWeek % 7;
function classifyDay($dateStr, $byDate, $shiftStartTime, $shiftEndTime, $graceInSec, $graceOutSec, $expectedWorkSec, $today) {
    $ts = strtotime($dateStr);
    $dow = (int)date('N', $ts); // 1=Mon..7=Sun
    $isSunday = ($dow === 7);
    $isFuture = ($dateStr > $today);

    if (!isset($byDate[$dateStr])) {
        if ($isFuture) return 'future';
        if ($isSunday) return 'weekend';
        return 'absent';
    }

    $day = $byDate[$dateStr];

    // If has an open IN session, can't determine completeness yet
    if ($day['has_open']) return 'pending';

    // Check if worked enough hours (incomplete = no salary for this day)
    if ($expectedWorkSec > 0 && $day['worked_sec'] < $expectedWorkSec) {
        return 'incomplete';
    }

    // Worked enough — check late/early for sub-classification (all count as complete for salary)
    $firstInTs = strtotime($day['first_in']);
    $lastOutTs = $day['last_out'] ? strtotime($day['last_out']) : null;

    $shiftStartTs = strtotime($dateStr . ' ' . $shiftStartTime);
    $shiftEndTs   = strtotime($dateStr . ' ' . $shiftEndTime);

    $isLate  = ($firstInTs > ($shiftStartTs + $graceInSec));
    $isEarly = ($lastOutTs && $lastOutTs < ($shiftEndTs - $graceOutSec));

    if ($isLate && $isEarly)  return 'late-early';
    if ($isLate)              return 'late';
    if ($isEarly)             return 'early';
    return 'complete';
}

// Helper: does this status count as a paid day?
function isPaidDay($cls) {
    return in_array($cls, ['complete', 'late', 'early', 'late-early']);
}

// Build summary stats
$completeDays   = 0;  // worked >= expected (includes late/early sub-types)
$incompleteDays = 0;  // worked < expected — no salary
$pendingDays    = 0;  // has open session — can't determine yet
$absentDays     = 0;  // working day, no attendance
$weekendDays    = 0;  // Sundays
$futureDays     = 0;
$lateDays       = 0;  // subset of complete
$earlyDays      = 0;  // subset of complete
$totalSec       = 0;

for ($d = 1; $d <= $totalDays; $d++) {
    $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $d);
    $cls = classifyDay($dateStr, $byDate, $shiftStartTime, $shiftEndTime, $graceInSec, $graceOutSec, $expectedWorkSec, $today);

    switch ($cls) {
        case 'complete':
        case 'late':
        case 'early':
        case 'late-early':
            $completeDays++;
            if (in_array($cls, ['late','late-early'])) $lateDays++;
            if (in_array($cls, ['early','late-early'])) $earlyDays++;
            if (isset($byDate[$dateStr])) {
                $totalSec += $byDate[$dateStr]['worked_sec'];
            }
            break;
        case 'incomplete': $incompleteDays++; break;
        case 'pending':    $pendingDays++;    break;
        case 'absent':     $absentDays++;     break;
        case 'weekend':    $weekendDays++;    break;
        case 'future':     $futureDays++;     break;
    }
}

function formatDuration($sec) {
    $h = floor($sec / 3600);
    $m = floor(($sec % 3600) / 60);
    if ($h > 0) return "{$h}h {$m}m";
    return "{$m}m";
}

// Selected employee details
$emp = null;
foreach ($employees as $e) {
    if ((int)$e['userid'] === $user_id) { $emp = $e; break; }
}

// Salary calculation (MUST be after $emp is set)
$monthlySalary = (float)($emp['monthly_salary'] ?? 0);
$dailyRate = $totalDays > 0 ? ($monthlySalary / $totalDays) : 0;
$calculatedSalary = $dailyRate * $completeDays;

// Previous/next month navigation
$prevMonth = date('Y-m', strtotime("$year-$mon-01 -1 month"));
$nextMonth = date('Y-m', strtotime("$year-$mon-01 +1 month"));

?>

<!DOCTYPE html>
<html>

<head>
    <?php include('component/css.php'); ?>
    <style>
        .cal-cell {
            border: 1px solid #e8edf5;
            background: #fff;
            min-height: 110px;
            padding: 6px 8px;
            font-size: 12px;
            transition: transform .12s, box-shadow .12s;
            position: relative;
            cursor: default;
        }
        .cal-cell:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(6,22,58,.12);
            z-index: 5;
        }
        .cal-cell .date-num {
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
        }
        .cal-cell .in-time { color: #16a34a; font-weight: 600; }
        .cal-cell .out-time { color: #f59e0b; font-weight: 600; }
        .cal-cell .dur {
            display: inline-block;
            background: rgba(40,122,177,.12);
            color: #1a56a0;
            padding: 1px 6px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 700;
            font-family: monospace;
            margin-top: 2px;
        }
        .cal-cell .badge-late {
            background: #fee2e2; color: #dc2626;
            font-size: 9px; padding: 1px 5px; border-radius: 50px;
            font-weight: 700;
        }
        .cal-cell .badge-early {
            background: #fef3c7; color: #f59e0b;
            font-size: 9px; padding: 1px 5px; border-radius: 50px;
            font-weight: 700;
        }
        .cal-cell .shops {
            font-size: 9px; color: #1a56a0;
            margin-top: 3px;
        }

        /* Day-state backgrounds */
        .cal-cell.complete    { background: #f0fdf4; border-color: #86efac; }
        .cal-cell.late        { background: #fff7ed; border-color: #fdba74; }
        .cal-cell.early       { background: #fefce8; border-color: #fde047; }
        .cal-cell.late-early  { background: #fff7ed; border-color: #fdba74; }
        .cal-cell.incomplete  { background: #fed7aa; border-color: #fb923c; }
        .cal-cell.incomplete .date-num { color: #c2410c; }
        .cal-cell.incomplete::after {
            content: 'I'; position: absolute; right: 6px; bottom: 4px;
            color: #c2410c; font-weight: 700; font-size: 18px; opacity: .35;
        }
        .cal-cell.pending     { background: #dbeafe; border-color: #93c5fd; }
        .cal-cell.pending .date-num { color: #1e40af; }
        .cal-cell.absent      { background: #fef2f2; border-color: #fca5a5; }
        .cal-cell.absent .date-num { color: #dc2626; }
        .cal-cell.absent::after {
            content: 'A'; position: absolute; right: 6px; bottom: 4px;
            color: #dc2626; font-weight: 700; font-size: 18px; opacity: .35;
        }
        .cal-cell.weekend     { background: #f1f5f9; }
        .cal-cell.weekend .date-num { color: #94a3b8; }
        .cal-cell.future      { background: #fff; opacity: .55; }
        .cal-cell.today {
            box-shadow: inset 0 0 0 2px #287ab1;
        }
        .cal-cell.today .date-num {
            color: #287ab1;
        }

        .cal-head {
            background: #06163a;
            color: #fff;
            padding: 8px;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        .summary-pill .dot {
            width: 8px; height: 8px; border-radius: 50%;
        }

        .legend-dot {
            display: inline-block; width: 12px; height: 12px;
            border-radius: 3px; margin-right: 4px;
            vertical-align: middle;
        }

        .day-detail-popover {
            display: none;
            position: absolute;
            top: 100%; left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(6,22,58,.18);
            padding: 10px 12px;
            font-size: 11px;
            min-width: 220px;
            z-index: 100;
            color: #0f172a;
        }
        .cal-cell:hover .day-detail-popover {
            display: block;
        }
        .day-detail-popover h6 {
            font-size: 12px; margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e8edf5;
        }
        .day-detail-popover .ss-row {
            display: flex; align-items: center; gap: 6px;
            padding: 3px 0;
        }
        .day-detail-popover .ss-num {
            background: #e2e8f0; color: #475569;
            width: 16px; height: 16px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 700;
        }
    </style>
</head>

<body class="bg-light">

    <?php include('component/sidebar.php'); ?>

    <div class="main w-auto">

        <?php include('component/header.php'); ?>

        <div class="container-fluid py-3">

            <!-- HEADER + FILTERS -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Attendance Calendar</h5>
                        <div class="d-flex gap-2">
                            <a href="?member_id=<?= $member_id ?>&month=<?= $prevMonth ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <span class="form-control form-control-sm text-center" style="max-width:160px;">
                                <?= date('F Y', strtotime("$year-$mon-01")) ?>
                            </span>
                            <a href="?member_id=<?= $member_id ?>&month=<?= $nextMonth ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Employee</label>
                            <select name="member_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php foreach ($employees as $e) { ?>
                                    <option value="<?= $e['userid'] ?>" <?= ($user_id == $e['userid']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($e['fullname']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Month</label>
                            <input type="month" name="month" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($month) ?>"
                                   onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="?member_id=<?= $user_id ?>&month=<?= date('Y-m') ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-arrow-clockwise"></i> This Month
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!$user_id || !$emp) { ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No active employee found. Please add an employee first.
                </div>
            <?php } else { ?>

            <!-- EMPLOYEE INFO + SUMMARY -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($emp['fullname']) ?></h5>
                            <small class="text-muted">
                                <?php if (!empty($emp['designation'])) echo htmlspecialchars($emp['designation']); ?>
                                <?php if (!empty($emp['shop_name'])) echo ' · ' . htmlspecialchars($emp['shop_name']); ?>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="summary-pill bg-success-subtle text-success">
                            <span class="dot bg-success"></span>
                            Complete: <b><?= $completeDays ?></b> days
                        </span>
                        <span class="summary-pill bg-warning-subtle text-warning">
                            <span class="dot bg-warning"></span>
                            Incomplete: <b><?= $incompleteDays ?></b>
                        </span>
                        <span class="summary-pill bg-info-subtle text-info">
                            <span class="dot bg-info"></span>
                            Pending: <b><?= $pendingDays ?></b>
                        </span>
                        <span class="summary-pill bg-danger-subtle text-danger">
                            <span class="dot bg-danger"></span>
                            Absent: <b><?= $absentDays ?></b>
                        </span>
                        <span class="summary-pill bg-primary-subtle text-primary">
                            <span class="dot bg-primary"></span>
                            Total Hours: <b><?= formatDuration($totalSec) ?></b>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-2" style="font-size:11px;">
                        <span class="text-muted">Late: <b><?= $lateDays ?></b></span>
                        <span class="text-muted">·</span>
                        <span class="text-muted">Early Leave: <b><?= $earlyDays ?></b></span>
                        <span class="text-muted">·</span>
                        <span class="text-muted">Sundays: <b><?= $weekendDays ?></b></span>
                        <?php if ($futureDays > 0) { ?>
                        <span class="text-muted">·</span>
                        <span class="text-muted">Upcoming: <b><?= $futureDays ?></b></span>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- SALARY CALCULATION CARD -->
            <?php if ($monthlySalary > 0) { ?>
            <div class="card border-0 shadow-sm rounded-4 mb-3" style="background:linear-gradient(135deg,#06163a,#1a56a0);color:#fff;">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-currency-rupee"></i> Salary Calculation — <?= date('F Y', strtotime("$year-$mon-01")) ?></h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:12px;">
                                <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em;">Monthly Salary</div>
                                <div style="font-size:1.4rem;font-weight:700;">₹<?= number_format($monthlySalary, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:12px;">
                                <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em;">Daily Rate (<?= $totalDays ?> days)</div>
                                <div style="font-size:1.4rem;font-weight:700;">₹<?= number_format($dailyRate, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:12px;">
                                <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em;">Complete Days (paid)</div>
                                <div style="font-size:1.4rem;font-weight:700;"><?= $completeDays ?> <small style="font-size:.6em;opacity:.7;">/ <?= $totalDays ?></small></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="background:rgba(22,163,74,.25);border-radius:10px;padding:12px;border:1px solid rgba(22,163,74,.4);">
                                <div style="font-size:.65rem;opacity:.8;text-transform:uppercase;letter-spacing:.05em;">Calculated Salary</div>
                                <div style="font-size:1.6rem;font-weight:700;color:#86efac;">₹<?= number_format($calculatedSalary, 2) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if ($incompleteDays > 0) { ?>
                    <div style="margin-top:10px;font-size:12px;opacity:.85;">
                        <i class="bi bi-info-circle"></i>
                        <?= $incompleteDays ?> incomplete day<?= $incompleteDays > 1 ? 's' : '' ?> not counted
                        (worked less than <?= number_format((float)$shift['expected_work_hours'], 1) ?>h).
                        <?php if ($incompleteDays > 0) { ?>
                            Deducted: <b>₹<?= number_format($dailyRate * $incompleteDays, 2) ?></b>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <?php if ($pendingDays > 0) { ?>
                    <div style="margin-top:6px;font-size:12px;opacity:.85;">
                        <i class="bi bi-hourglass-split"></i>
                        <?= $pendingDays ?> pending day<?= $pendingDays > 1 ? 's have' : ' has' ?> open sessions —
                        salary will be updated once marked OUT.
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } else { ?>
            <div class="alert alert-warning mb-3" style="font-size:13px;">
                <i class="bi bi-exclamation-triangle"></i>
                <b>Monthly salary not set</b> for this employee.
                Set it in <a href="member-master.php?edit=<?= $member_id ?>">Employee Master</a> to see salary calculations.
            </div>
            <?php } ?>

            <!-- LEGEND -->
            <div class="mb-3" style="font-size:12px;">
                <span class="text-muted me-3"><span class="legend-dot" style="background:#f0fdf4;border:1px solid #86efac;"></span>Complete (paid)</span>
                <span class="text-muted me-3"><span class="legend-dot" style="background:#fff7ed;border:1px solid #fdba74;"></span>Late (paid)</span>
                <span class="text-muted me-3"><span class="legend-dot" style="background:#fefce8;border:1px solid #fde047;"></span>Early (paid)</span>
                <span class="text-muted me-3"><span class="legend-dot" style="background:#fed7aa;border:1px solid #fb923c;"></span>Incomplete (unpaid)</span>
                <span class="text-muted me-3"><span class="legend-dot" style="background:#dbeafe;border:1px solid #93c5fd;"></span>Pending</span>
                <span class="text-muted me-3"><span class="legend-dot" style="background:#fef2f2;border:1px solid #fca5a5;"></span>Absent</span>
                <span class="text-muted me-3"><span class="legend-dot" style="background:#f1f5f9;"></span>Sunday</span>
                <span class="text-muted"><span class="legend-dot" style="background:#fff;border:1px solid #e8edf5;"></span>Future</span>
            </div>

            <!-- CALENDAR GRID -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered m-0" style="table-layout:fixed;">
                            <thead>
                                <tr>
                                    <th class="cal-head" style="background:#0d214d;">Sun</th>
                                    <th class="cal-head">Mon</th>
                                    <th class="cal-head">Tue</th>
                                    <th class="cal-head">Wed</th>
                                    <th class="cal-head">Thu</th>
                                    <th class="cal-head">Fri</th>
                                    <th class="cal-head" style="background:#0d214d;">Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $day = 1;
                                $cellsThisMonth = $firstDayCol + $totalDays;
                                $totalCells = ceil($cellsThisMonth / 7) * 7;

                                for ($i = 0; $i < $totalCells; $i++) {
                                    $col = $i % 7;
                                    if ($col === 0) echo '<tr>';

                                    if ($i < $firstDayCol || $i >= $cellsThisMonth) {
                                        echo '<td style="background:#fafbfc;border-color:#f1f5f9;"></td>';
                                    } else {
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $day);
                                        $cls = classifyDay($dateStr, $byDate, $shiftStartTime, $shiftEndTime, $graceInSec, $graceOutSec, $expectedWorkSec, $today);
                                        $isToday = ($dateStr === $today);
                                        $dayData = $byDate[$dateStr] ?? null;

                                        $firstInDisp  = $dayData ? date('h:i A', strtotime($dayData['first_in'])) : '';
                                        $lastOutDisp  = ($dayData && $dayData['last_out']) ? date('h:i A', strtotime($dayData['last_out'])) : '';
                                        $durDisp      = $dayData ? formatDuration($dayData['worked_sec']) : '';
                                        $sessionsCnt  = $dayData ? $dayData['sessions'] : 0;

                                        // Late/Early/Short durations (split grace IN/OUT)
                                        $lateSec = 0;
                                        $earlySec = 0;
                                        $shortSec = 0;
                                        if ($dayData) {
                                            $firstInTs = strtotime($dayData['first_in']);
                                            $lastOutTs = $dayData['last_out'] ? strtotime($dayData['last_out']) : null;
                                            $shiftStartTs = strtotime($dateStr . ' ' . $shiftStartTime);
                                            $shiftEndTs   = strtotime($dateStr . ' ' . $shiftEndTime);
                                            if ($firstInTs > ($shiftStartTs + $graceInSec)) {
                                                $lateSec = $firstInTs - ($shiftStartTs + $graceInSec);
                                            }
                                            if ($lastOutTs && $lastOutTs < ($shiftEndTs - $graceOutSec)) {
                                                $earlySec = ($shiftEndTs - $graceOutSec) - $lastOutTs;
                                            }
                                            if ($lastOutTs && $expectedWorkSec > 0
                                                && $dayData['worked_sec'] < $expectedWorkSec) {
                                                $shortSec = (int)($expectedWorkSec - $dayData['worked_sec']);
                                            }
                                        }
                                        ?>
                                        <td class="cal-cell <?= $cls ?> <?= $isToday ? 'today' : '' ?>">
                                            <div class="date-num"><?= $day ?></div>

                                            <?php if ($dayData) { ?>
                                                <div class="in-time">
                                                    <i class="bi bi-box-arrow-in-right"></i> <?= $firstInDisp ?>
                                                </div>
                                                <?php if ($lastOutDisp) { ?>
                                                    <div class="out-time">
                                                        <i class="bi bi-box-arrow-right"></i> <?= $lastOutDisp ?>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="text-success" style="font-size:10px;">
                                                        <i class="bi bi-hourglass-split"></i> Open
                                                    </div>
                                                <?php } ?>

                                                <div class="dur"><?= $durDisp ?></div>
                                                <?php if ($sessionsCnt > 1) { ?>
                                                    <small class="text-muted" style="font-size:9px;">
                                                        <?= $sessionsCnt ?> sessions
                                                    </small>
                                                <?php } ?>

                                                <?php if ($lateSec > 0) { ?>
                                                    <div><span class="badge-late">Late <?= formatDuration($lateSec) ?></span></div>
                                                <?php } ?>
                                                <?php if ($earlySec > 0) { ?>
                                                    <div><span class="badge-early">Early <?= formatDuration($earlySec) ?></span></div>
                                                <?php } ?>
                                                <?php if ($shortSec > 0) { ?>
                                                    <div><span class="badge-late">Short <?= formatDuration($shortSec) ?></span></div>
                                                <?php } ?>

                                                <?php if (!empty($dayData['shops'])) { ?>
                                                    <div class="shops">
                                                        <i class="bi bi-shop"></i>
                                                        <?= htmlspecialchars(implode(', ', $dayData['shops'])) ?>
                                                    </div>
                                                <?php } ?>

                                                <!-- HOVER POPOVER: all sessions -->
                                                <?php if ($sessionsCnt > 1) { ?>
                                                <div class="day-detail-popover">
                                                    <h6><?= date('D, d M Y', strtotime($dateStr)) ?></h6>
                                                    <?php foreach ($dayData['all_rows'] as $idx => $ss) {
                                                        $ssIn  = date('h:i A', strtotime($ss['scan_time']));
                                                        $ssOut = $ss['out_time'] ? date('h:i A', strtotime($ss['out_time'])) : '<i>Open</i>';
                                                        $ssDur = $ss['out_time']
                                                            ? formatDuration(strtotime($ss['out_time']) - strtotime($ss['scan_time']))
                                                            : '—';
                                                    ?>
                                                        <div class="ss-row">
                                                            <span class="ss-num"><?= $idx + 1 ?></span>
                                                            <?php if (!empty($ss['shop_name'])) { ?>
                                                                <small style="color:#1a56a0;font-weight:600;"><?= htmlspecialchars($ss['shop_name']) ?></small>
                                                            <?php } ?>
                                                            <span style="color:#16a34a;font-weight:600;"><?= $ssIn ?></span>
                                                            <span style="color:#94a3b8;">→</span>
                                                            <span style="color:#f59e0b;font-weight:600;"><?= $ssOut ?></span>
                                                            <span style="margin-left:auto;color:#64748b;font-family:monospace;"><?= $ssDur ?></span>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>

                                            <?php } elseif ($cls === 'incomplete') { ?>
                                                <div class="text-warning" style="font-size:10px; margin-top:8px; font-weight:600;">
                                                    <i class="bi bi-exclamation-triangle"></i> Incomplete
                                                    <div style="font-size:9px;opacity:.7;">
                                                        <?= formatDuration($dayData['worked_sec']) ?> / <?= number_format((float)$shift['expected_work_hours'], 1) ?>h
                                                    </div>
                                                </div>
                                            <?php } elseif ($cls === 'pending') { ?>
                                                <div class="text-primary" style="font-size:10px; margin-top:8px; font-weight:600;">
                                                    <i class="bi bi-hourglass-split"></i> Pending
                                                    <div style="font-size:9px;opacity:.7;">Open session</div>
                                                </div>
                                            <?php } elseif ($cls === 'absent') { ?>
                                                <div class="text-danger" style="font-size:10px; margin-top:8px;">
                                                    <i class="bi bi-x-circle"></i> No attendance
                                                </div>
                                            <?php } elseif ($cls === 'weekend') { ?>
                                                <div class="text-muted" style="font-size:10px; margin-top:8px;">
                                                    <i class="bi bi-cup"></i> Sunday
                                                </div>
                                            <?php } ?>
                                        </td>
                                        <?php
                                        $day++;
                                    }

                                    if ($col === 6) echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php } ?>

        </div>

    </div>

    <?php include('component/script.php'); ?>

</body>

</html>
