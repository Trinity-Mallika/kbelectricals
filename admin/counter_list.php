<?php include("../adminsession.php");
$title = "Counter List";
$pagename = "counter_list.php";
$module = "Counter List";
$submodule = "Counter List";
$btn_name = "Save";
$tblname = "account";
$tblpkey = "account_id";
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
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
                                        <strong><label>Status</label></strong>
                                        <select name="status" id="status" class="chosen-select form-control form-control-sm">
                                            <option value="">All</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">In Active</option>
                                        </select>
                                        <script>
                                            document.getElementById('status').value = '<?= $status ?>';
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
                                <table id="example" class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Counter Details</th>
                                            <th width="15%">Route</th>
                                            <th width="15%">Owner Details</th>
                                            <th width="10%">Area</th>
                                            <th width="8%">Class</th>
                                            <th width="12%">Status</th>
                                            <th width="10%">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $where = "WHERE t.common_id=7 AND t.type='customer'";

                                        if ($status == 'active') {
                                            $where .= " AND EXISTS (
                    SELECT 1
                    FROM transaction_entry te
                    WHERE te.account_id = t.account_id
                    AND te.type='order'
                )";
                                        }

                                        if ($status == 'inactive') {
                                            $where .= " AND NOT EXISTS (
                    SELECT 1
                    FROM transaction_entry te
                    WHERE te.account_id = t.account_id
                    AND te.type='order'
                )";
                                        }

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

                    te.total_orders,
                    te.last_order_date,

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

                LEFT JOIN (
                    SELECT
                        account_id,
                        COUNT(*) AS total_orders,
                        MAX(billdate) AS last_order_date
                    FROM transaction_entry
                    WHERE type='order'
                    GROUP BY account_id
                ) te ON te.account_id = t.account_id

                $where

                GROUP BY t.account_id
                ORDER BY t.account_id DESC
            ");

                                        foreach ($qry as $row_get) {
                                        ?>
                                            <tr>

                                                <td>
                                                    <?= $slno++; ?>
                                                </td>

                                                <td>
                                                    <strong class="text-primary">
                                                        <?= $row_get['account_name']; ?>
                                                    </strong>

                                                    <br>

                                                    <small class="text-muted">
                                                        <i class="fa fa-phone"></i>
                                                        <?= $row_get['mobile_no']; ?>
                                                    </small>

                                                    <br>

                                                    <small class="text-secondary">
                                                        <?= $row_get['address']; ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <?php if (!empty($row_get['route_name'])) { ?>
                                                        <span class="badge bg-primary">
                                                            <?= $row_get['route_name']; ?>
                                                        </span>
                                                    <?php } ?>

                                                    <br>

                                                    <small>
                                                        <?= $row_get['day_of_week']; ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <strong>
                                                        <?= $row_get['owner_name']; ?>
                                                    </strong>

                                                    <br>

                                                    <small class="text-muted">
                                                        <?= $row_get['o_mobile_no']; ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <?= $row_get['area_name']; ?>
                                                </td>

                                                <td>
                                                    <span class="badge bg-info text-dark">
                                                        <?= $row_get['class']; ?>
                                                    </span>
                                                </td>

                                                <td>

                                                    <?php if ($row_get['total_orders'] > 0) { ?>

                                                        <span class="badge bg-success">
                                                            Active
                                                        </span>

                                                        <br>

                                                        <small>
                                                            Orders :
                                                            <?= $row_get['total_orders']; ?>
                                                        </small>

                                                        <?php if (!empty($row_get['last_order_date'])) { ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                Last :
                                                                <?= $obj->dateformatindia($row_get['last_order_date']); ?>
                                                            </small>
                                                        <?php } ?>

                                                    <?php } else { ?>

                                                        <span class="badge bg-danger">
                                                            Inactive
                                                        </span>

                                                    <?php } ?>

                                                </td>

                                                <td>

                                                    <?= $obj->dateformatindia($row_get['createdate']); ?>

                                                    <br>

                                                    <small class="text-muted">
                                                        <?= $row_get['fullname']; ?>
                                                    </small>

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
        $(".chosen-select").chosen();
    });
</script>

</html>