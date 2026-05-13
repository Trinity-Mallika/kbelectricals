<?php include("../adminsession.php");
$title = "Counter List";
$pagename = "accounts_list.php";
$module = "Counter List";
$submodule = "Counter List";
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
                <div class="col-lg-12 mb-2">
                    <form>
                        <div class="card mt-3">
                            <div class="card-header text-white">
                                <?php echo $module; ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong><label for="fromdate">From Date</label></strong>
                                        <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate"
                                            value="<?php echo $fromdate; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <strong><label for="todate">To Date</label></strong>
                                        <input type="date" class="form-control form-control-sm" name="todate" id="todate"
                                            value="<?php echo $todate; ?>">
                                    </div>

                                    <?php
                                    $createdby = isset($_GET['createdby']) ? $_GET['createdby'] : '';
                                    $account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
                                    ?>

                                    <div class="col-md-3">
                                        <strong><label>Added By</label></strong>
                                        <select name="createdby" id="createdby" class="chosen-select form-control form-control-sm">
                                            <option value="">--Select Executive--</option>
                                            <?php
                                            $sql = $obj->executequery("SELECT userid, fullname FROM user ORDER BY fullname ASC");
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

                                    <div class="col-md-3">
                                        <strong><label>Counter Name</label></strong>
                                        <select name="account_id" id="account_id" class="chosen-select form-control form-control-sm">
                                            <option value="">--Select Counter--</option>
                                            <?php
                                            $sql = $obj->executequery("SELECT account_id, account_name FROM account WHERE companyid='$companyid' ORDER BY account_name ASC");
                                            foreach ($sql as $row) {
                                            ?>
                                                <option value="<?= $row['account_id']; ?>">
                                                    <?= $row['account_name']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <script>
                                            document.getElementById('account_id').value = '<?= $account_id ?>';
                                        </script>
                                    </div>
                                    <div class="col-md-3 mt-4">
                                        <input type="submit" class="btn btn-primary btn-sm" name="search" value="Search">
                                        <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" id="reset">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?> Record
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>Sr. No.</th>
                                            <th>Added By</th>
                                            <th>Route Name - Day</th>
                                            <th>Counter Name</th>
                                            <th>Mobile No.</th>
                                            <th>Counter Type</th>
                                            <th>Area</th>
                                            <th>Class</th>
                                            <th>Address</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $where = "WHERE t.createdate BETWEEN '$from' AND '$to'
          AND t.status1 = 0
          AND t.companyid = '$companyid'";

                                        if (!empty($createdby)) {
                                            $where .= " AND t.createdby = '$createdby'";
                                        }

                                        if (!empty($account_id)) {
                                            $where .= " AND t.account_id = '$account_id'";
                                        }

                                        $qry = $obj->executequery("
    SELECT
        t.*,
        a.area_name,
        u.fullname,
        r.route_name,

        GROUP_CONCAT(
            DISTINCT r.day_of_week
            ORDER BY FIELD(
                r.day_of_week,
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            )
            SEPARATOR ', '
        ) AS day_of_week,

        CASE
            WHEN t.common_id = -1 THEN 'Employee'
            ELSE cm.common_name
        END AS common_name

    FROM account t

    LEFT JOIN area_master a
        ON a.area_id = t.area_id

    LEFT JOIN user u
        ON u.userid = t.createdby

    LEFT JOIN common_master cm
        ON cm.common_id = t.common_id

    LEFT JOIN route_counter rc
        ON rc.account_id = t.account_id

    LEFT JOIN route r
        ON r.batch_no = rc.batch_no

    LEFT JOIN route_plan rp
        ON rp.batch_no = r.batch_no

    $where

    GROUP BY t.account_id
    ORDER BY t.account_id DESC
");
                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?= $slno++; ?></td>
                                                <td><?= $row_get['fullname']; ?></td>
                                                <td><?= $row_get['route_name']; ?> - <?= $row_get['day_of_week'] ?></td>
                                                <td><?= $row_get['account_name']; ?></td>
                                                <td><?= $row_get['mobile_no']; ?></td>
                                                <td><?= $row_get['common_name']; ?></td>
                                                <td><?= $row_get['area_name']; ?></td>
                                                <td><?= $row_get['class']; ?></td>
                                                <td><?= $row_get['address']; ?></td>
                                                <td>
                                                    <?php if ($row_get['status1'] == 0) { ?>
                                                        <button
                                                            class="btn btn-warning btn-sm approve-btn"
                                                            data-id="<?= $row_get['account_id']; ?>">
                                                            Approve
                                                        </button>
                                                    <?php } else { ?>
                                                        <span class="badge bg-success">Approved</span>
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