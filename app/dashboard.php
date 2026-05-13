<?php include("appsession.php");

$pagename = 'dashboard.php';
$title = 'Dashboard';
$day = date('d');
$month = date('F');
$year = date('Y');
$weekday = date('l');
$createdate = date('Y-m-d');
$data = $obj->getRouteDashboardData($loginid, $companyid);
$currenttotal = $data['today_target'];
$todayvisit = $data['today_visit'];
$Monthtotal = $data['month_target'];
$monthvisit = $data['month_visit'];
$today_percent = $data['today_percent'];
$month_percent = $data['month_percent'];
$todaysales = $data['todaysales'];
$Monthsales = $data['Monthsales'];
$pending_amount = $data['pending_amount'];
$route_plan_id = $data['route_plan_id'];

$today_green = ($currenttotal > 0)
    ? round(($todayvisit / $currenttotal) * 100, 2)
    : 0;

$today_red = 100 - $today_green;
$today_blue = 0;


// MONTH
$month_green = ($Monthtotal > 0)
    ? round(($monthvisit / $Monthtotal) * 100, 2)
    : 0;

$month_red = 100 - $month_green;
$month_blue = 0;

$month = date('n');
$year  = date('Y');

$monthly_target = $obj->getvalfield(
    "monthly_target",
    "SUM(total_target)",
    "createdby=$loginid AND month=$month AND year=$year"
);

$target_green = ($monthly_target > 0)
    ? round(($Monthsales / $monthly_target) * 100, 2)
    : 0;

$target_green = min($target_green, 100);

$target_red = 100 - $target_green;
$target_blue = 0;

$suffix = 'th';
if ($day == 1 || $day == 21 || $day == 31)
    $suffix = 'st';
elseif ($day == 2 || $day == 22)
    $suffix = 'nd';
elseif ($day == 3 || $day == 23)
    $suffix = 'rd';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>

</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="card border-0 shadow-lg mb-3 today-date-card pt-1 pb-1">
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h2 class="today-date"> <?= $day ?><sup><?= $suffix ?></sup></h2>
                        <div class="ms-3">
                            <h5 class="text-blue mb-1"><?= $weekday ?></h5>
                            <h6 class="text-secondary mb-0"><?= $month ?>, <?= $year ?></h6>
                        </div>
                    </div>
                    <img src="img/icon/calendar.png" alt="" width="40px">
                </div>
            </div>

            <div class="row mt-3 mb-2">
                <div class="col-12 position-relative">
                    <h5 class="sub-title mb-3 head-text">Performance Dashboard</h5>
                </div>
                <div class="col-7 mb-2 pe-1">
                    <a href="javascript:void(0)">
                        <div class="card progress-card light-primary">
                            <div class="d-flex align-items-center ">
                                <h6 class="card-title mb-0 sub-title">Today's Visit</h6>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-2">
                                    <div class="progress-stacked" style="height:5px">

                                        <?php if ($today_red > 0) { ?>
                                            <div class="progress" style="width:<?= $today_red ?>%">
                                                <div class="progress-bar bg-danger"></div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($today_blue > 0) { ?>
                                            <div class="progress" style="width:<?= $today_blue ?>%">
                                                <div class="progress-bar bg-info"></div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($today_green > 0) { ?>
                                            <div class="progress" style="width:<?= $today_green ?>%">
                                                <div class="progress-bar bg-success"></div>
                                            </div>
                                        <?php } ?>

                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="fw-semibold sub-title"><?= $todayvisit; ?> / <?= $currenttotal; ?>
                                            Visit's</small>
                                        <small class="fw-semibold sub-title"> <?= $today_percent ?>% </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-5 mb-2 ps-1">
                    <a href="javascript:void(0)">
                        <div class="card progress-card light-primary">
                            <div class="d-flex align-items-center ">
                                <h6 class="card-title mb-0 sub-title">Today Sales</h6>
                            </div>
                            <h5 class="mb-0 mt-2 text-blue">Rs. <?= number_format($todaysales); ?></h5>
                        </div>
                    </a>
                </div>
                <div class="col-7 mb-2 pe-1">
                    <a href="javascript:void(0)">
                        <div class="card progress-card light-primary">
                            <div class="d-flex align-items-center ">
                                <h6 class="card-title mb-0 sub-title">Monthly Visit</h6>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-2">
                                    <div class="progress-stacked" style="height:5px">

                                        <div class="progress" style="width:<?= $month_red ?>%">
                                            <div class="progress-bar progress-bar-striped bg-danger"></div>
                                        </div>

                                        <div class="progress" style="width:<?= $month_blue ?>%">
                                            <div class="progress-bar progress-bar-striped bg-info"></div>
                                        </div>

                                        <div class="progress" style="width:<?= $month_green ?>%">
                                            <div class="progress-bar progress-bar-striped bg-success"></div>
                                        </div>

                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="fw-semibold sub-title"><?= $monthvisit; ?> / <?= $Monthtotal; ?>
                                            Visit's </small>
                                        <small class="fw-semibold sub-title"> <?= $month_percent; ?>% </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-5 mb-2 ps-1">
                    <a href="javascript:void(0)">
                        <div class="card progress-card light-primary">
                            <div class="d-flex align-items-center ">
                                <h5 class="card-title mb-0 sub-title">Monthly Sales</h5>
                            </div>
                            <h4 class="mb-0 mt-2 text-blue">Rs. <?= number_format($Monthsales); ?></h4>
                        </div>
                    </a>
                </div>
                <div class="col-12 mb-2 pe-1">
                    <a href="javascript:void(0)">
                        <div class="card progress-card light-primary">
                            <div class="d-flex align-items-center ">
                                <h6 class="card-title mb-0 sub-title">Month Target vs Achievement</h6>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-2">
                                    <div class="progress-stacked" style="height:5px">

                                        <?php if ($target_red > 0) { ?>
                                            <div class="progress" style="width:<?= $target_red ?>%">
                                                <div class="progress-bar bg-danger"></div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($target_blue > 0) { ?>
                                            <div class="progress" style="width:<?= $target_blue ?>%">
                                                <div class="progress-bar bg-info"></div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($target_green > 0) { ?>
                                            <div class="progress" style="width:<?= $target_green ?>%">
                                                <div class="progress-bar bg-success"></div>
                                            </div>
                                        <?php } ?>

                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="fw-semibold sub-title">
                                            <?= number_format($Monthsales) ?> /
                                            <?= number_format($monthly_target) ?>
                                            Achievement's
                                        </small>
                                        <small class="fw-semibold sub-title">
                                            <?= $target_green ?>%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center bg-blue"
                data-bs-toggle="offcanvas" data-bs-target="#pendingPayment" aria-controls="pendingPayment">
                <h6 class="mb-0"><i class="bi bi-cash"></i> &nbsp;Pending Payment</h6>
                <h5 class="mb-0"><i class="bi bi-currency-rupee"></i><?= number_format($pending_amount, 2); ?></h5>
            </div>
            <div class="card attendance-card border-0 shadow-lg mb-2">
                <div class=" d-flex justify-content-between flex-row align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Scheme Opportunity</h5>
                        <small class="text-secondary">Customers closest to next reward</small>
                    </div>
                    <!-- <a href="#0" class="text-blue fw-semibold">View All >></a> -->
                </div>
                <table class="table mb-0 table-borderless">
                    <tr class="border-top">
                        <td width="50px">
                            <div class="icon-badge-red">
                                <i class="bi bi-shop"></i>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between ms-1 mb-1">
                                <h5> Maa Tara Electric</h5>
                                <h6><span class="badge rounded-pill text-bg-danger align-content-around">Very
                                        Close</span></h6>
                            </div>
                            <div class="d-flex justify-content-between ms-1">
                                <h6 class="text-secondary"><span class="text-danger">80 </span>/ 100 Coils</h6>
                                <h6>80%</h6>
                            </div>
                            <div class="progress ms-1" role="progressbar" aria-label="Danger example"
                                aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: 80%"></div>
                            </div>
                            <small class="text-secondary">Need <span class="text-danger">20 Coils</span> more</small>
                        </td>
                    </tr>
                    <tr class="border-top">
                        <td width="50px">
                            <div class="icon-badge-green">
                                <i class="bi bi-shop"></i>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between ms-1 mb-1">
                                <h5> Maa Tara Electric</h5>
                                <h6><span class="badge rounded-pill text-bg-success align-content-around"> On
                                        Track</span></h6>
                            </div>
                            <div class="d-flex justify-content-between ms-1">
                                <h6 class="text-secondary"><span class="text-success">70 </span>/ 100 Coils</h6>
                                <h6>70%</h6>
                            </div>
                            <div class="progress ms-1" role="progressbar" aria-label="success example"
                                aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: 70%"></div>
                            </div>
                            <small class="text-secondary">Need <span class="text-success">30 Coils</span> more</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div
                class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
                <h6 class="mb-0 text-blue">
                    <i class="bi bi-geo-alt-fill"></i> &nbsp;Daily Visit Entry
                </h6>
                <a href="check-in.php" class="btn btn-sm">Check-In</a>
            </div>

            <div
                class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
                <h6 class="mb-0 text-blue">
                    <i class="bi bi-shop-window"></i> &nbsp;Add New Counter
                </h6>
                <a href="create-counter.php" class="btn btn-sm">+ Add</a>
            </div>

            <div
                class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
                <h6 class="mb-0 text-blue">
                    <i class="bi bi-cart-check-fill"></i> &nbsp;Order Entry
                </h6>
                <a href="my-order.php" class="btn btn-sm">+ Add</a>
            </div>

            <div
                class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
                <h6 class="mb-0 text-blue">
                    <i class="bi bi-cash-coin"></i> &nbsp;Payment Entry
                </h6>
                <a href="add_payment.php" class="btn btn-sm">+ Add</a>
            </div>

            <div
                class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
                <h6 class="mb-0 text-blue">
                    <i class="bi bi-bullseye"></i> &nbsp;Monthly Target
                </h6>
                <a href="monthly_target.php" class="btn btn-sm">+ Add</a>
            </div>

            <div
                class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
                <h6 class="mb-0 text-blue">
                    <i class="bi bi-whatsapp"></i> &nbsp;Send WhatsApp
                </h6>
                <a href="upcoming_beat.php" class="btn btn-sm">Show</a>
            </div>
        </div>
    </section>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="pendingPayment" aria-labelledby="pendingPaymentLabel"
        style="height:60%;border-radius:20px 20px 0 0;">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Pending Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body small bg-body-tertiary rounded-top-4" id="pendingPaymentBody">

            <div class="text-center p-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2 mb-0">Loading...</p>
            </div>

        </div>
    </div>

    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
</body>
<script>
    $(document).ready(function() {

        let loaded = false;

        $('#pendingPayment').on('show.bs.offcanvas', function() {

            if (loaded) return;

            $.ajax({
                url: "ajax/get_pending_payment.php",
                type: "POST",
                data: {
                    route_plan_id: <?= $route_plan_id ?>,
                    companyid: <?= $companyid ?>,
                },
                beforeSend: function() {
                    $("#pendingPaymentBody").html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 mb-0">Loading...</p>
                    </div>
                `);
                },
                success: function(res) {
                    $("#pendingPaymentBody").html(res);
                    loaded = true;
                },
                error: function() {
                    $("#pendingPaymentBody").html(`
                    <div class="alert alert-danger">
                        Failed to load data
                    </div>
                `);
                }
            });

        });

    });
</script>

</html>