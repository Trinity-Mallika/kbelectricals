<?php
include("../adminsession.php");
$title = "Monthly Target Approval";
$pagename = "monthly_target_view.php";
$module = "Monthly Target Approval";
$submodule = "Monthly Target Approval List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$createdby = (isset($_GET["createdby"])) ? $obj->test_input($_GET["createdby"]) : "";
$month = (isset($_GET["month"])) ? $obj->test_input($_GET["month"]) : "";
$year  = (isset($_GET["year"])) ? $obj->test_input($_GET["year"]) : "";

$user_name = $obj->getvalfield(
    "user",
    "fullname",
    "userid='$createdby'"
);

$total_counters = $obj->getvalfield(
    "monthly_target",
    "count(*)",
    "createdby='$createdby'
    and month='$month'
    and year='$year'"
);

$grand_target = $obj->getvalfield(
    "monthly_target",
    "ifnull(sum(total_target),0)",
    "createdby='$createdby'
    and month='$month'
    and year='$year'"
);

$approval_status = $obj->getvalfield(
    "monthly_target_approval",
    "status",
    "createdby='$createdby'
    and month='$month'
    and year='$year'"
);
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        .card-header {
            background: #06163a;
        }

        .badge-pending {
            background: #ffc107;
            color: #000;
        }

        .badge-approved {
            background: #198754;
        }

        .badge-rejected {
            background: #dc3545;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- Header -->
        <?php include('component/header.php'); ?>
        <!-- Header Close-->
        <!-- Content -->
        <div class="container-fluid">
            <form>
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="mt-2">
                            <legend><?php echo $title ?></legend>
                            <?php include('component/alert.php'); ?>
                            <div class="card">
                                <div class="card-header text-white">
                                    <?php echo $module ?>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">

                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-primary text-white">
                                                <div class="card-body">
                                                    <small>Sales Executive</small>
                                                    <h5><?= $user_name ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-info text-white">
                                                <div class="card-body">
                                                    <small>Month</small>
                                                    <h5><?= date('F', mktime(0, 0, 0, $month, 1)) . ' ' . $year ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-success text-white">
                                                <div class="card-body">
                                                    <small>Total Counters</small>
                                                    <h5><?= $total_counters ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-warning">
                                                <div class="card-body">
                                                    <small>Grand Target</small>
                                                    <h5>₹<?= number_format($grand_target) ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-header text-white bg-dark">
                            Brand Wise Summary
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-bordered table-sm">

                                    <thead>

                                        <tr>
                                            <th>S.No</th>
                                            <th>Brand</th>
                                            <th class="text-end">Target</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php
                                        $i = 1;

                                        $brand_sql = $obj->executequery("
                SELECT
                    cm.cat_name,
                    SUM(mtd.target) total_target

                FROM monthly_target_details mtd

                LEFT JOIN category_master cm
                    ON cm.cat_id = mtd.brand_id

                WHERE
                    mtd.createdby='$createdby'
                    AND mtd.month='$month'
                    AND mtd.year='$year'

                GROUP BY mtd.brand_id

                ORDER BY total_target DESC
                ");

                                        foreach ($brand_sql as $row) {
                                        ?>

                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $row['cat_name'] ?></td>
                                                <td class="text-end">
                                                    ₹<?= number_format($row['total_target']) ?>
                                                </td>
                                            </tr>

                                        <?php } ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>
                    </div>
                    <div class="card mb-3">

                        <div class="card-header text-white">
                            Area Wise Summary
                        </div>

                        <div class="card-body">

                            <table class="table table-bordered table-sm">

                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Area</th>
                                        <th class="text-end">Target</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $i = 1;

                                    $area_sql = $obj->executequery("
                SELECT
                    am.area_name,
                    SUM(mt.total_target) total_target

                FROM monthly_target mt

                LEFT JOIN account a
                    ON a.account_id = mt.account_id

                LEFT JOIN area_master am
                    ON am.area_id = a.area_id

                WHERE
                    mt.createdby='$createdby'
                    AND mt.month='$month'
                    AND mt.year='$year'

                GROUP BY am.area_id
                ORDER BY total_target DESC
                ");

                                    foreach ($area_sql as $row) {
                                    ?>

                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $row['area_name'] ?></td>
                                            <td class="text-end">
                                                ₹<?= number_format($row['total_target']) ?>
                                            </td>
                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    <div class="card">

                        <div class="card-header text-white">
                            Counter Wise Details
                        </div>

                        <div class="card-body">

                            <div class="accordion" id="counterAccordion">

                                <?php

                                $counter_sql = $obj->executequery("
            SELECT
                mt.*,
                a.account_name,
                am.area_name

            FROM monthly_target mt

            LEFT JOIN account a
                ON a.account_id = mt.account_id

            LEFT JOIN area_master am
                ON am.area_id = a.area_id

            WHERE
                mt.createdby='$createdby'
                AND mt.month='$month'
                AND mt.year='$year'

            ORDER BY mt.total_target DESC
            ");

                                $c = 1;

                                foreach ($counter_sql as $row) {

                                ?>

                                    <div class="accordion-item">

                                        <h2 class="accordion-header">

                                            <button
                                                class="accordion-button collapsed"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#counter<?= $c ?>">

                                                <?= $row['account_name'] ?>
                                                (<?= $row['area_name'] ?>)

                                                <span class="ms-auto me-3 fw-bold text-success">
                                                    ₹<?= number_format($row['total_target']) ?>
                                                </span>

                                            </button>

                                        </h2>

                                        <div
                                            id="counter<?= $c ?>"
                                            class="accordion-collapse collapse">

                                            <div class="accordion-body">

                                                <table class="table table-bordered table-sm">

                                                    <tr>
                                                        <th>Brand</th>
                                                        <th>Target</th>
                                                    </tr>

                                                    <?php

                                                    $details = $obj->executequery("
                                SELECT
                                    mtd.*,
                                    cm.cat_name

                                FROM monthly_target_details mtd

                                LEFT JOIN category_master cm
                                    ON cm.cat_id=mtd.brand_id

                                WHERE mtd.target_id='$row[target_id]'
                                ");

                                                    foreach ($details as $d) {
                                                    ?>

                                                        <tr>
                                                            <td><?= $d['cat_name'] ?></td>
                                                            <td>
                                                                ₹<?= number_format($d['target']) ?>
                                                            </td>
                                                        </tr>

                                                    <?php } ?>

                                                </table>

                                                <?php if (!empty($row['comment'])) { ?>

                                                    <div class="alert alert-warning mb-0">
                                                        <strong>Comment :</strong>
                                                        <?= $row['comment'] ?>
                                                    </div>

                                                <?php } ?>

                                            </div>

                                        </div>

                                    </div>

                                <?php
                                    $c++;
                                }
                                ?>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php include('component/script.php'); ?>
</body>

</html>