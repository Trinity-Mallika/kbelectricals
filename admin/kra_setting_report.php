<?php include("../adminsession.php");
$title = "KRA SETTING";
$pagename = "kra_setting_report.php";
$module = "KRA SETTING";
$submodule = "KRA SETTING";
$btn_name = "Save";
$tblname = "account";
$tblpkey = "account_id";
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : 0;
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', strtotime('-30 days'));
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
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
            <div class="row">

                <div class="col-lg-4 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            Average Counter Visit/Day/Beat
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>Number of Counter.</th>
                                            <th>Point </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;



                                        $qry = $obj->executequery("SELECT * FROM kra_daily_visit_setting  order by kra_daily_visit_setting_id desc");
                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?= $row_get['minimum_counter'] ?>< <?= $row_get['maximum_counter'] ?></td>

                                                <td><?= $row_get['point']; ?></td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            Beat Wise Productivity(% counter active
                            )

                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>% of counter in Beat
                                            </th>
                                            <th>Point
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;



                                        $qry = $obj->executequery("SELECT * FROM kra_beat_wise_productivity  order by kra_beat_wise_productivity_id desc");
                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?= $row_get['minimum_counter'] ?>< <?= $row_get['maximum_counter'] ?></td>

                                                <td><?= $row_get['point']; ?></td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            Overall Business/Month/Average


                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>
                                                Basic Amount

                                            </th>
                                            <th>Point
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $qry = $obj->executequery("SELECT * FROM kra_overall_business_setting  order by kra_overall_business_setting_id asc");
                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?= $row_get['minimum_counter'] ?>< <?= $row_get['maximum_counter'] ?></td>

                                                <td><?= $row_get['point']; ?></td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            Weightage on Overall Score: 20%
                            For activation


                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>
                                                Class

                                            </th>
                                            <th>Per Month Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $qry = $obj->executequery("SELECT * FROM kra_counter_activation_setting  order by kra_actvation_id asc");
                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?= $row_get['class'] ?></td>

                                                <td><?= $row_get['valid_amt']; ?></td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            Product Mix

                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>Number of Product per Beat
                                            </th>
                                            <th>Point </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;



                                        $qry = $obj->executequery("SELECT * FROM kra_product_mix  order by kra_product_mix_id asc");
                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?= $row_get['number_of_product'] ?></td>

                                                <td><?= $row_get['point']; ?></td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content close-->
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });
</script>
<script>
    $(document).on('click', '.approve-btn', function() {
        let btn = $(this);
        let account_id = btn.data('id');

        if (!confirm("Approve this account?")) return;

        $.ajax({
            url: "approve_account.php",
            type: "POST",
            data: {
                account_id: account_id
            },
            success: function(res) {
                alert(res);
                if (res == 'success') {
                    btn.closest('td').html('<span class="badge bg-success">Approved</span>');
                } else {
                    alert("Failed to approve.");
                }
            }
        });
    });
</script>

</html>