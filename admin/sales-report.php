<?php include("../adminsession.php");

$title = "Sales Report";
$pagename = "sales-report.php";
$module = "Sales Report";
$submodule = "Sales Report";
$emp_id = isset($_GET['emp_id']) ? $_GET['emp_id'] : 0;
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-01');
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
if (!preg_match($dateRegex, $fromdate)) {
    $fromdate = date('Y-m-01');
}
if (!preg_match($dateRegex, $todate)) {
    $todate   = date('Y-m-d');
}

$companyid = isset($_SESSION['companyid']) ? (int) $_SESSION['companyid'] : 1;


function fmtAmt($amt)
{
    return number_format((float) $amt, 2);
}

function getOverallSalesStats(
    DataOperation $obj,
    int $companyid,
    string $fromdate,
    string $todate,
    int $emp_id
): array {

    // Employee filters
    $empRouteCondition = $emp_id > 0
        ? " AND rp.sales_executive_id = '$emp_id'"
        : "";

    $empOrderCondition = $emp_id > 0
        ? " AND te.createdby = '$emp_id'"
        : "";

    // 1. Total No. Of Counter
    $totalCounters = (int) $obj->getvalfield(
        "route_counter rc
         JOIN route_plan rp
            ON rp.batch_no = rc.batch_no
            AND rp.companyid = rc.companyid
         JOIN user u
            ON u.userid = rp.sales_executive_id
            AND u.usertype = 'sales'",
        "COUNT(DISTINCT rc.account_id)",
        "rc.is_active = 1
         AND rc.companyid = $companyid"
            . $empRouteCondition
    );


    // 2 & 3. Total Orders + Total Order Value
    $orderRows = $obj->executequery("
        SELECT
            COUNT(*) AS cnt,
            COALESCE(SUM(te.grand_total), 0) AS amt
        FROM transaction_entry te
        JOIN user u
            ON u.userid = te.createdby
            AND u.usertype = 'sales'
        WHERE te.type = 'order'
          AND te.is_approved = 1
          AND te.billdate >= '$fromdate'
          AND te.billdate < DATE_ADD('$todate', INTERVAL 1 DAY)
          AND te.companyid = $companyid
          $empOrderCondition
    ");

    $totalOrders = (int) ($orderRows[0]['cnt'] ?? 0);
    $totalValue  = (float) ($orderRows[0]['amt'] ?? 0);


    // 4. Total Beat Active
    // Beat is active when >= 75% of its counters
    // have at least 1 sales order during the period.
    $totalBeatActive = (int) $obj->getvalfield(
        "(
            SELECT
                rc.batch_no,
                COUNT(DISTINCT rc.account_id) AS total_counters,

                COUNT(DISTINCT CASE
                    WHEN te.account_id IS NOT NULL
                    THEN rc.account_id
                END) AS active_counters

            FROM route_counter rc

            INNER JOIN route_plan rp
                ON rp.batch_no = rc.batch_no
                AND rp.companyid = rc.companyid

            LEFT JOIN transaction_entry te
                ON te.account_id = rc.account_id
                AND te.type = 'order'
                AND te.is_approved = 1
                AND te.billdate >= '$fromdate'
                AND te.billdate < DATE_ADD('$todate', INTERVAL 1 DAY)
                AND te.companyid = $companyid

            WHERE rc.is_active = 1
              AND rc.companyid = $companyid
              $empRouteCondition

            GROUP BY rc.batch_no

            HAVING active_counters >= (total_counters * 0.75)

        ) AS beat_data",
        "COUNT(*)",
        "1=1"
    );


    // 5. Total Counter Active
    // Counter is active when it has at least 1 approved sales order.
    $totalCounterActive = (int) $obj->getvalfield(
        "transaction_entry te
         JOIN user u
            ON u.userid = te.createdby
            AND u.usertype = 'sales'",
        "COUNT(DISTINCT te.account_id)",
        "te.type = 'order'
         AND te.is_approved = 1
         AND te.billdate >= '$fromdate'
         AND te.billdate < DATE_ADD('$todate', INTERVAL 1 DAY)
         AND te.companyid = $companyid"
            . $empOrderCondition
    );


    // 6. Total Counter Active As Per Criteria
    $accountSales = $obj->executequery("
        SELECT
            a.account_id,
            a.class,
            SUM(t.grand_total) AS sales

        FROM transaction_entry t

        JOIN account a
            ON a.account_id = t.account_id

        JOIN user u
            ON u.userid = t.createdby
            AND u.usertype = 'sales'

        WHERE t.type = 'order'
          AND t.is_approved = 1
          AND t.billdate >= '$fromdate'
          AND t.billdate < DATE_ADD('$todate', INTERVAL 1 DAY)
          AND t.companyid = $companyid
          " . ($emp_id > 0 ? " AND t.createdby = '$emp_id'" : "") . "

        GROUP BY a.account_id
    ");


    $configRows = $obj->executequery(
        "SELECT class, min_sales
         FROM kra_productivity_config
         WHERE companyid = $companyid"
    );

    $classMinSales = [];

    foreach ($configRows as $c) {
        $classMinSales[strtoupper($c['class'])] = $c['min_sales'];
    }

    $totalCounterActiveCriteria = 0;

    foreach ($accountSales as $acc) {

        $class = strtoupper($acc['class']);
        $min   = $classMinSales[$class] ?? null;

        if ($min !== null && $acc['sales'] >= $min) {
            $totalCounterActiveCriteria++;
        }
    }


    // 7. Total Outstanding As On Date
    // Only accounts belonging to the selected employee's
    // active routes when emp_id is provided.
    $routeAccountsSql = "
        SELECT DISTINCT rc.account_id

        FROM route_counter rc

        JOIN route_plan rp
            ON rp.batch_no = rc.batch_no
            AND rp.companyid = rc.companyid

        JOIN user u
            ON u.userid = rp.sales_executive_id
            AND u.usertype = 'sales'

        WHERE rp.companyid = $companyid
          AND rc.companyid = $companyid
          AND rc.is_active = 1
          $empRouteCondition
    ";


    // Opening Balance
    $openingBalance = (float) $obj->getvalfield(
        "account",
        "COALESCE(SUM(opening_balance), 0)",
        "account_id IN ($routeAccountsSql)"
    );


    // Opening Balance Payments
    $openingPayment = (float) $obj->getvalfield(
        "transaction_entry",
        "COALESCE(SUM(grand_total + IFNULL(cash_disc, 0)), 0)",
        "account_id IN ($routeAccountsSql)
         AND companyid = $companyid
         AND type = 'payment'
         AND pay_type = 'opening'
         AND pay_status = 1
         AND billdate <= '$todate'"
            . ($emp_id > 0 ? " AND createdby = '$emp_id'" : "")
    );

    $openingOutstanding = $openingBalance - $openingPayment;


    // Bill Amount
    $billAmount = (float) $obj->getvalfield(
        "transaction_entry",
        "COALESCE(SUM(invoice_amt), 0)",
        "account_id IN ($routeAccountsSql)
         AND companyid = $companyid
         AND type = 'order'
         AND is_approved = 1
         AND invoice_no != ''
         AND billdate <= '$todate'"
            . ($emp_id > 0 ? " AND createdby = '$emp_id'" : "")
    );


    // Bill Payments
    $billPayment = (float) $obj->getvalfield(
        "transaction_entry",
        "COALESCE(SUM(grand_total + IFNULL(cash_disc, 0)), 0)",
        "account_id IN ($routeAccountsSql)
         AND companyid = $companyid
         AND type = 'payment'
         AND pay_type = 'bill'
         AND ref_bill_id > 0
         AND pay_status = 1
         AND billdate <= '$todate'"
            . ($emp_id > 0 ? " AND createdby = '$emp_id'" : "")
    );

    $billOutstanding = $billAmount - $billPayment;


    // Final Outstanding
    $totalOutstanding = $openingOutstanding + $billOutstanding;


    return [
        'total_counter'          => $totalCounters,
        'total_order'            => $totalOrders,
        'total_value'            => $totalValue,
        'total_beat_active'      => $totalBeatActive,
        'total_counter_active'   => $totalCounterActive,
        'total_counter_criteria' => $totalCounterActiveCriteria,
        'total_outstanding'      => $totalOutstanding,
    ];
}
$stats = getOverallSalesStats($obj, $companyid, $fromdate, $todate, $emp_id);

function detailsLink(string $view, string $fromdate, string $todate, int $emp_id): string
{
    return 'sales-details.php?' . http_build_query([
        'view'     => $view,
        'fromdate' => $fromdate,
        'todate'   => $todate,
        'emp_id'   => $emp_id,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <?php include('component/dashcss.php'); ?>
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
                    <div class="card mt-3">
                        <div class="card-header text-white">
                            <?php echo $module; ?>
                        </div>
                        <div class="card-body">
                            <form>
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
                                    <div class="col-md-3">
                                        <strong><label for="todate">Sales Executive</label></strong>
                                        <select name="emp_id" id="emp_id" class="form-select chosen-select">
                                            <option value="0">All</option>
                                            <?php $exes = $obj->executequery("Select * from user where usertype='sales'");
                                            foreach ($exes as $emps) { ?>
                                                <option value="<?= $emps['userid'] ?>"><?= $emps['fullname'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <script>
                                            document.getElementById('emp_id').value = '<?= $emp_id; ?>';
                                        </script>
                                    </div>
                                    <div class="col-md-3 mt-4">
                                        <input type="submit" class="btn btn-primary btn-sm" name="search" value="Search">
                                        <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" id="reset">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row op-overview mt-1 g-3">
                <div class="col-lg-12">
                    <div class="section-title">
                        <i class="bi bi-box "></i>
                        Sales Report
                    </div>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('counter_total', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon success"><i class="bi bi-card-checklist"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total No. Of Counter</div>
                                <div class="cs-val"><?php echo $stats['total_counter']; ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('order_total', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon info"><i class="bi bi-box-seam"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total Order From Sale</div>
                                <div class="cs-val"><?php echo $stats['total_order']; ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('order_total', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon warning"><i class="bi bi-cash"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total Value</div>
                                <div class="cs-val">Rs. <?php echo fmtAmt($stats['total_value']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('beat_active', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon danger"><i class="bi bi-check2-square"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total Beat Active</div>
                                <div class="cs-val"><?php echo $stats['total_beat_active']; ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('counter_active', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon purple"><i class="bi bi-clipboard-check"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total Counter Active</div>
                                <div class="cs-val"><?php echo $stats['total_counter_active']; ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('counter_criteria', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon teal"><i class="bi bi-diagram-2"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total Counter Active As Per Criteria</div>
                                <div class="cs-val"><?php echo $stats['total_counter_criteria']; ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="<?php echo detailsLink('outstanding', $fromdate, $todate, $emp_id); ?>" style="text-decoration: none;" target="_blank">
                        <div class="cs-box">
                            <div class="cs-icon pink"><i class="bi bi-calendar-check"></i></div>
                            <div>
                                <div class="cs-label fw-semibold">Total Outstanding As On Date</div>
                                <div class="cs-val">Rs. <?php echo fmtAmt($stats['total_outstanding']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Content close-->
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

</html>