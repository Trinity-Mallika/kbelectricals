<?php
include("../adminsession.php");
$title = "Monthly Target Approval";
$pagename = "monthly_target_approval.php";
$module = "Monthly Target Approval";
$submodule = "Monthly Target Approval List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$month_filter = (isset($_GET["month_filter"])) ? $obj->test_input($_GET["month_filter"]) : "";
$year_filter = (isset($_GET["year_filter"])) ? $obj->test_input($_GET["year_filter"]) : "";
$createdby = (isset($_GET["createdby"])) ? $obj->test_input($_GET["createdby"]) : "";
$status_filter = (isset($_GET["status_filter"])) ? $obj->test_input($_GET["status_filter"]) : "";
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
                                    <div class="row mt-2">
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="gst_id">Month <span class="text-danger fw-bold"></span></label></strong>
                                            <select class="form-select form-select-sm chosen-select" name="month_filter" id="month_filter">
                                                <option value="">--Select Month--</option>
                                                <?php
                                                for ($i = 1; $i <= 12; $i++) {
                                                ?>
                                                    <option value="<?= $i ?>">
                                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('month_filter').value = '<?= $month_filter ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="gst_id">Year <span class="text-danger fw-bold"></span></label></strong>
                                            <select class="form-select form-select-sm chosen-select" name="year_filter" id="year_filter">
                                                <option value="">--Select Month--</option>
                                                <?php
                                                for ($y = date('Y'); $y >= 2024; $y--) {
                                                ?>
                                                    <option value="<?= $y ?>">
                                                        <?= $y ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('year_filter').value = '<?= $year_filter ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3">
                                            <strong><label>Sales Executive</label></strong>
                                            <select name="createdby" id="createdby" class="chosen-select form-control form-control-sm">
                                                <option value="">--Select Executive--</option>
                                                <?php
                                                $sql = $obj->executequery("SELECT userid, fullname FROM user where usertype='sales' ORDER BY fullname ASC");
                                                foreach ($sql as $row) {
                                                ?>
                                                    <option value="<?= $row['userid']; ?>">
                                                        <?= $row['fullname']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('createdby').value = '<?= $createdby ?>';
                                            </script>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong><label>Status</label></strong>
                                            <select name="status_filter" id="status_filter" class="chosen-select form-control form-control-sm">
                                                <option value="">All</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Approved">Approved</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit" class="btn btn-primary btn-sm" name="search" value="Search">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" id="reset">Reset</a>
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
                    <div class="card">
                        <div class="card-header text-white">
                            Monthly Target Approval
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example"
                                    class="table table-bordered table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Sales Executive</th>
                                            <th>Month</th>
                                            <th>Year</th>
                                            <th>Total Counters</th>
                                            <th class="text-end">Total Target</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT
    mt.createdby,
    u.fullname,
    mt.month,
    mt.year,

    COUNT(*) total_counters,
    SUM(mt.total_target) grand_target,

    IFNULL(mta.status,'Pending') approval_status,

    MAX(mt.createdate) createdate

FROM monthly_target mt

LEFT JOIN user u
ON u.userid = mt.createdby

LEFT JOIN monthly_target_approval mta
ON mta.userid = mt.createdby
AND mta.month = mt.month
AND mta.year = mt.year

WHERE mt.companyid='$companyid'

GROUP BY
    mt.createdby,
    mt.month,
    mt.year

ORDER BY
    mt.year DESC,
    CAST(mt.month AS UNSIGNED) DESC
");
                                        foreach ($sql as $row) {
                                            $status = $row['approval_status'];
                                        ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $row['fullname'] ?></td>
                                                <td><?= date('F', mktime(0, 0, 0, $row['month'], 1)) ?></td>
                                                <td><?= $row['year'] ?></td>
                                                <td><?= $row['total_counters'] ?></td>
                                                <td class="text-end">₹<?= number_format($row['grand_target']) ?></td>
                                                <td>
                                                    <?php
                                                    if ($status == 'Approved') {

                                                        echo '<span class="badge bg-success">Approved</span>';
                                                    } elseif ($status == 'Rejected') {

                                                        echo '<span class="badge bg-danger">Rejected</span>';
                                                    } else {

                                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?= date(
                                                        'd-m-Y',
                                                        strtotime(
                                                            $row['createdate']
                                                        )
                                                    ) ?>
                                                </td>
                                                <td>
                                                    <a href="monthly_target_view.php?createdby=<?= $row['createdby'] ?>&month=<?= $row['month'] ?>&year=<?= $row['year'] ?>"
                                                        class="btn btn-info btn-sm">
                                                        View
                                                    </a>
                                                    <?php
                                                    if ($status == 'Pending') {
                                                    ?>
                                                        <button
                                                            class="btn btn-success btn-sm"
                                                            onclick="approve_target('<?= $row['createdby'] ?>','<?= $row['month'] ?>','<?= $row['year'] ?>')">
                                                            Approve
                                                        </button>
                                                    <?php } ?>
                                                </td>
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
    <?php include('component/script.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $("#example").DataTable();
        });

        function approve_target(createdby, month, year) {
            Swal.fire({
                title: 'Approve Target?',
                icon: 'question',
                showCancelButton: true
            }).then((result) => {

                if (result.isConfirmed) {

                    $.post(
                        'ajax/approve_target.php', {
                            createdby,
                            month,
                            year,
                            status: 'Approved'
                        },
                        function() {

                            Swal.fire(
                                'Approved',
                                'Target approved successfully',
                                'success'
                            ).then(() => {
                                location.reload();
                            });

                        }
                    );

                }

            });
        }
    </script>

</body>

</html>