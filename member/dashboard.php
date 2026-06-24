<?php
// die;
include('session.php');


$userid = $_SESSION['userid'];

$userData = $obj->executequery("
    SELECT *
    FROM user
    WHERE userid='$userid'
    LIMIT 1
");

if (empty($userData)) {
    session_destroy();
    echo "<script>location='index.php';</script>";
    exit;
}

$user = $userData[0];

$totalAttendance = $obj->getvalfield(
    'attendance_entry',
    'count(*)',
    "member_id='$userid'"
);

$latestMeeting = $obj->executequery("
    SELECT *
    FROM store_location
    WHERE status = 1
    ORDER BY location_id DESC
    LIMIT 1
");

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
                        <?php echo $user['fullname']; ?>
                    </h4>

                    <small>

                        <?php echo ucfirst($user['usertype']); ?>

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

            <div class="col-6">
                <a href="my-attendance.php" class="text-decoration-none text-dark">

                    <div class="card-box stat-card">

                        <div class="stat-icon bg-primary-subtle text-primary">

                            <i class="bi bi-check2-square"></i>

                        </div>

                        <h3>

                            <?php echo $totalAttendance; ?>

                        </h3>

                        <div class="small-label">
                            Total Attendance
                        </div>

                    </div>
                </a>
            </div>

            <div class="col-6">

                <div class="card-box stat-card">

                    <div class="stat-icon bg-success-subtle text-success">

                        <i class="bi bi-geo-alt"></i>

                    </div>

                    <h3>
                        GPS
                    </h3>

                    <div class="small-label">
                        GPS Verified
                    </div>

                </div>

            </div>

        </div>

        <!-- QR CARD -->

        <?php

        /* TODAY ATTENDANCE CHECK */

        //         $todayAttendance = $obj->executequery("
        //     SELECT
        //         a.*,
        //     FROM attendance_entry a
        //     INNER JOIN store_location m
        //         ON m.location_id = a.location_id
        //     WHERE a.member_id = '$userid'
        //     AND DATE(a.scan_time) = CURDATE()
        //     ORDER BY a.attendance_id DESC
        //     LIMIT 1
        // ");

        $todayAttendance = $obj->executequery("
    SELECT *
    FROM attendance_entry
    WHERE member_id='$userid'
    AND DATE(scan_time)=CURDATE()
    ORDER BY attendance_id DESC
    LIMIT 1
");

        ?>

        <?php if ($todayAttendance) {

            $att = $todayAttendance[0];

        ?>

            <!-- TODAY ATTENDANCE -->

            <div class="card-box attendance-success mb-3">

                <div class="success-icon">

                    <i class="bi bi-check-circle-fill"></i>

                </div>

                <h5 class="attendance-title">

                    Attendance Marked

                </h5>

                <p class="attendance-text">

                    Your attendance has already been marked today.

                </p>

                <div class="attendance-info">

                    <div class="info-row">

                        <span>
                            <i class="bi bi-calendar-event"></i>
                            Meeting
                        </span>

                        <b>
                            <?php echo $att['title']; ?>
                        </b>

                    </div>

                    <div class="info-row">

                        <span>
                            <i class="bi bi-clock"></i>
                            Time
                        </span>

                        <b>
                            <?php
                            echo date(
                                'd M Y h:i A',
                                strtotime($att['scan_time'])
                            );
                            ?>
                        </b>

                    </div>

                    <div class="info-row">

                        <span>
                            <i class="bi bi-check2-circle"></i>
                            Status
                        </span>

                        <b class="text-success">
                            Present
                        </b>

                    </div>

                </div>

            </div>

        <?php } else { ?>

            <!-- SCAN CARD -->

            <div class="card-box scan-card mb-3">

                <div class="scan-icon">

                    <i class="bi bi-qr-code-scan"></i>

                </div>

                <h5 class="scan-title">

                    Mark Your Attendance

                </h5>

                <p class="scan-text">

                    Scan the meeting QR code and allow GPS verification to confirm your presence.

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

        <?php if ($latestMeeting) { ?>

            <!-- <div class="card-box p-3">

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <h5 class="mb-0">

                        <i class="bi bi-calendar-event text-primary"></i>

                        Latest Meeting

                    </h5>

                    <span class="badge bg-success">

                        Active

                    </span>

                </div>

                <div class="meeting-box">



                    <div class="small-label mt-2">

                        <i class="bi bi-geo-alt"></i>

                        <?php //echo $latestMeeting[0]['location_name']; 
                        ?>

                    </div>

                </div>

            </div> -->

        <?php } ?>

    </div>

</body>

</html>