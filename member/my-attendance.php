<?php

include('session.php');

$userid = $_SESSION['userid'];

$month = $_GET['month'] ?? date('Y-m');

$rows = $obj->executequery("
    SELECT
        a.*,
        m.location_name
    FROM attendance_entry a
    INNER JOIN store_location m
        ON m.location_id = a.location_id
    WHERE a.member_id = '$userid'
    AND DATE_FORMAT(a.scan_time, '%Y-%m') = '$month'
    ORDER BY a.scan_time DESC
");

$total = count($rows);

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Attendance</title>

    <link rel="stylesheet" href="../admin/assets/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/assets/css/bootstrap.min.css">

    <style>
        body {
            background: #f4f7fb;
            margin: 0;
        }

        .top-section {
            background: linear-gradient(135deg, #06163a, #287ab1);
            color: #fff;
            padding: 20px 16px 65px;
            border-radius: 0 0 28px 28px;
        }

        .container {
            max-width: 520px;
        }

        .page-wrap {
            margin-top: -45px;
        }

        .card-box {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            border: 0;
        }

        .attendance-item {
            background: #fff;
            border: 1px solid #eef2f7;
            border-radius: 18px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .filter-card {
            padding: 16px;
        }

        .form-control {
            height: 48px;
            border-radius: 14px;
        }
    </style>
</head>

<body>

    <div class="top-section">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h4 class="mb-0">My Attendance</h4>
                <small>Monthly attendance history</small>
            </div>

            <a href="dashboard.php" class="btn btn-light btn-sm rounded-pill">
                <i class="bi bi-arrow-left"></i>
                Back
            </a>

        </div>

    </div>

    <div class="container page-wrap px-3">

        <div class="card-box filter-card mb-3">

            <form method="GET">

                <label class="form-label fw-semibold">
                    Select Month
                </label>

                <input type="month"
                    name="month"
                    class="form-control"
                    value="<?php echo $month; ?>"
                    onchange="this.form.submit()">

            </form>

            <div class="mt-3">
                <span class="badge bg-primary">
                    Total Present: <?php echo $total; ?>
                </span>
            </div>

        </div>

        <?php if ($rows) { ?>

            <?php foreach ($rows as $row) { ?>

                <div class="attendance-item">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="fw-bold">
                                <?php echo $row['title']; ?>
                            </div>

                            <div class="text-muted small mt-1">
                                <i class="bi bi-geo-alt"></i>
                                <?php echo $row['location_name']; ?>
                            </div>
                        </div>

                        <span class="badge bg-success">
                            Present
                        </span>

                    </div>

                    <div class="text-muted small mt-2">

                        <i class="bi bi-clock"></i>

                        <?php
                        echo date(
                            'd M Y h:i A',
                            strtotime($row['scan_time'])
                        );
                        ?>

                    </div>

                    <?php if (!empty($row['distance_meter'])) { ?>

                        <div class="text-muted small mt-1">

                            <i class="bi bi-broadcast"></i>
                            Distance:
                            <?php echo number_format($row['distance_meter'], 2); ?>
                            meter

                        </div>

                    <?php } ?>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="alert alert-warning rounded-4">
                No attendance found for selected month.
            </div>

        <?php } ?>

    </div>

</body>

</html>