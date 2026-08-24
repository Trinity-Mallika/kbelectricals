<?php include("../adminsession.php");
$title = "Counter List";
$pagename = "counter_list.php";
$module = "Counter List";
$submodule = "Counter List";
$btn_name = "Save";
$tblname = "account";
$tblpkey = "account_id";
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : 0;
$status          = isset($_GET['status']) ? $_GET['status'] : '';
$filter          = isset($_GET['filter']) ? $_GET['filter'] : '';
$today      = date('Y-m-d');
$monthStart = date('Y-m-01');

$classCreditLimits = [
    'A' => 1100000.00,
    'B' => 50000.00,
    'C' => 25000.00,
];
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
                                    <div class="col-md-6 mt-4">
                                        <strong><label>Filter's</label></strong>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="filter" id="never_visited" value="never_visited" <?= ($filter == "never_visited") ? "checked" : ""; ?>>
                                            <label class="form-check-label" for="never_visited">
                                                Counter Not Visited Once
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="filter" id="crossing_limit" value="crossing_limit" <?= ($filter == "crossing_limit") ? "checked" : ""; ?>>
                                            <label class="form-check-label" for="crossing_limit">
                                                Crossing Monthly Credit Limit
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="filter" id="missing_details" value="missing_details" <?= ($filter == "missing_details") ? "checked" : ""; ?>>
                                            <label class="form-check-label" for="missing_details">
                                                Missing Basic Details
                                            </label>
                                        </div>
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
                            <?php echo $submodule; ?> Record as per <?= date("M Y") ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="22%">Counter Details</th>
                                            <th width="13%">Route</th>
                                            <th width="13%">Assigned To</th>
                                            <th width="13%">Owner Details</th>
                                            <th width="8%">Area</th>
                                            <th width="7%">Class</th>
                                            <th width="10%"><?= ($filter == "never_visited") ? "Last Order On" : "Status" ?></th>
                                            <?php if ($filter == "never_visited") { ?>
                                                <th width="10%">Visit Status</th>
                                            <?php } ?>
                                            <?php if ($filter == "crossing_limit") { ?>
                                                <th width="10%">This Month / Limit</th>
                                            <?php } ?>
                                            <?php if ($filter == "missing_details") { ?>
                                                <th width="12%">Missing Fields</th>
                                            <?php } ?>
                                            <th width="10%">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $where = "WHERE t.type='customer'";

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

                                        if ($filter == "never_visited") {
                                            $where .= " AND EXISTS (
        SELECT 1
        FROM route_counter rc2
        WHERE rc2.account_id = t.account_id
        AND rc2.is_active = 1
    )
    AND NOT EXISTS (
        SELECT 1
        FROM daily_entries de2
        WHERE de2.account_id = t.account_id
        AND de2.companyid = $companyid
        AND DATE(de2.createdate) BETWEEN '$monthStart' AND '$today'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM transaction_entry te2
        WHERE te2.account_id = t.account_id
        AND te2.type = 'order'
        AND te2.companyid = $companyid
        AND te2.billdate BETWEEN '$monthStart' AND '$today'
    )
    AND NOT (
        DATE(t.createdate) BETWEEN '$monthStart' AND '$today'
        AND EXISTS (
            SELECT 1
            FROM route_plan rp2
            WHERE rp2.sales_executive_id = t.createdby
        )
    )";
                                        }
                                        // Shortcut: Missing Basic Details
                                        if ($filter == "missing_details") {
                                            $where .= " AND (
                    t.mobile_no IS NULL OR t.mobile_no = ''
                    OR t.o_mobile_no IS NULL OR t.o_mobile_no = ''
                    OR t.owner_name IS NULL OR t.owner_name = ''
                    OR t.dob IS NULL OR t.dob = '0000-00-00'
                    OR t.doa IS NULL OR t.doa = '0000-00-00'
                )";
                                        }

                                        // Shortcut: Crossing Monthly Credit Limit
                                        if ($filter == "crossing_limit") {
                                            $where .= " AND (
                    (t.class = 'A' AND COALESCE(mb.monthly_balance,0) >= " . $classCreditLimits['A'] . ")
                    OR (t.class = 'B' AND COALESCE(mb.monthly_balance,0) >= " . $classCreditLimits['B'] . ")
                    OR (t.class = 'C' AND COALESCE(mb.monthly_balance,0) >= " . $classCreditLimits['C'] . ")
                )";
                                        }

                                        $qry = $obj->executequery("SELECT 
                    t.*,
                    a.area_name,
                    u.fullname,
                    r.route_name,
                    us.fullname as emp_name,
                    te.total_orders,
                    te.last_order_date,
                    de3.last_visit_date,
                    mb.monthly_balance,

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

                LEFT JOIN user us
                    ON us.userid = rp.sales_executive_id

                LEFT JOIN (
                    SELECT
                        account_id,
                        COUNT(*) AS total_orders,
                        MAX(billdate) AS last_order_date
                    FROM transaction_entry
                    WHERE type='order'
                    GROUP BY account_id
                ) te ON te.account_id = t.account_id

                LEFT JOIN (
                    SELECT
                        account_id,
                        MAX(DATE(createdate)) AS last_visit_date
                    FROM daily_entries
                    WHERE companyid = $companyid
                    GROUP BY account_id
                ) de3 ON de3.account_id = t.account_id

                LEFT JOIN (
                    SELECT
                        account_id,
                        COALESCE(SUM(
                            CASE
                                WHEN type = 'payment' AND pay_status = 1 THEN (grand_total + IFNULL(cash_disc,0))
                                ELSE 0
                            END
                        ), 0) AS monthly_balance
                    FROM transaction_entry
                    WHERE companyid = $companyid
                    AND billdate BETWEEN '$monthStart' AND '$today'
                    GROUP BY account_id
                ) mb ON mb.account_id = t.account_id

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
                                                <td><?= $row_get['emp_name']; ?></td>
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

                                                    <?php if ($filter == "never_visited") { ?>
                                                        <?php if (!empty($row_get['last_order_date'])) { ?>
                                                            <small class="text-muted">
                                                                <?= $obj->dateformatindia($row_get['last_order_date']); ?>
                                                            </small>
                                                        <?php } ?>
                                                        <?php } else {
                                                        if ($row_get['total_orders'] > 0) { ?>

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

                                                    <?php }
                                                    }
                                                    ?>

                                                </td>

                                                <?php if ($filter == "never_visited") { ?>
                                                    <td>
                                                        <?php if (empty($row_get['last_visit_date'])) { ?>
                                                            <span class="badge bg-danger">Never Visited</span>
                                                        <?php } else { ?>
                                                            <small class="text-muted">
                                                                <?= $obj->dateformatindia($row_get['last_visit_date']); ?>
                                                            </small>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>

                                                <?php if ($filter == "crossing_limit") { ?>
                                                    <td>
                                                        <?php
                                                        $class = strtoupper(trim($row_get['class']));
                                                        $limit = $classCreditLimits[$class] ?? 0;
                                                        ?>
                                                        <span class="badge bg-danger">
                                                            ₹<?= number_format((float) $row_get['monthly_balance']); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            Limit: ₹<?= number_format($limit); ?>
                                                        </small>
                                                    </td>
                                                <?php } ?>

                                                <?php if ($filter == "missing_details") { ?>
                                                    <td>
                                                        <?php
                                                        $missing = [];
                                                        if (empty($row_get['mobile_no'])) $missing[] = 'Mobile';
                                                        if (empty($row_get['o_mobile_no'])) $missing[] = 'Owner Mobile';
                                                        if (empty($row_get['owner_name'])) $missing[] = 'Owner Name';
                                                        if (empty($row_get['dob']) || $row_get['dob'] == '0000-00-00') $missing[] = 'DOB';
                                                        if (empty($row_get['doa']) || $row_get['doa'] == '0000-00-00') $missing[] = 'DOA';
                                                        ?>
                                                        <small class="text-danger">
                                                            <?= implode(', ', $missing); ?>
                                                        </small>
                                                    </td>
                                                <?php } ?>

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