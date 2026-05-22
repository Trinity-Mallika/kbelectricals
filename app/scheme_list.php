<?php include("appsession.php");
$title = "Scheme List";
$fromdate = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d', strtotime('-7 days'));
$todate = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
$visit_id = (isset($_GET["visit_id"])) ? $obj->test_input($_GET["visit_id"]) : 0;
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
            <div class="row">
                <div class="col-12">
                    <div class="card attendance-card border-0 shadow-lg mb-2">
                        <div class=" d-flex justify-content-between flex-row align-items-center ">
                            <div>
                                <h5 class="mb-0">Scheme Opportunity</h5>
                                <small class="text-dark">Customers closest to next reward</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-12">
                    <div class="card attendance-card border-0 shadow-lg mb-2">
                        <div class="scheme-box ">
                            <h6 class="mb-0">Scheme Name</h6>
                        </div>
                        <table class="table mb-0 ">
                            <tr>
                                <td width="45px">
                                    <div class="icon-badge-red">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between ms-1 mb-1">
                                        <h5> Maa Tara Electric</h5>
                                        <small><span class="badge rounded-pill text-bg-danger align-content-around">Very
                                                Close</span></small>
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
                            <tr>
                                <td width="45px">
                                    <div class="icon-badge-green">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between ms-1 mb-1">
                                        <h5> Maa Tara Electric</h5>
                                        <small><span class="badge rounded-pill text-bg-success align-content-around"> On
                                                Track</span></small>
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
                </div>
                <div class="col-12">
                    <div class="card attendance-card border-0 shadow-lg mb-2">
                        <div class="scheme-box ">
                            <h6 class="mb-0">Scheme Name</h6>
                        </div>
                        <table class="table mb-0 ">
                            <tr>
                                <td width="45px">
                                    <div class="icon-badge-red">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between ms-1 mb-1">
                                        <h5> Maa Tara Electric</h5>
                                        <small><span class="badge rounded-pill text-bg-danger align-content-around">Very
                                                Close</span></small>
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
                            <tr>
                                <td width="45px">
                                    <div class="icon-badge-green">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between ms-1 mb-1">
                                        <h5> Maa Tara Electric</h5>
                                        <small><span class="badge rounded-pill text-bg-success align-content-around"> On
                                                Track</span></small>
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
                </div>
            </div>
        </div>
    </section>




    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>

</body>


</html>