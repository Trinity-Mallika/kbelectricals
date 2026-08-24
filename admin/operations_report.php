<?php
include("../adminsession.php");
$title = "Operations Report";
$pagename = "operations_report.php";
$module = "Operations Report";
$submodule = "Operations Report";
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


function getSummary(DataOperation $obj, string $sql): array
{
    $rows = $obj->executequery($sql);
    if (!empty($rows) && isset($rows[0])) {
        $row = $rows[0];
        return [
            'count'  => (int) ($row['cnt'] ?? 0),
            'amount' => (float) ($row['amt'] ?? 0),
        ];
    }
    return ['count' => 0, 'amount' => 0.0];
}

$dateWhere = "billdate BETWEEN '$fromdate' AND '$todate' AND companyid = $companyid";

// 1. Quotation Total
$quotationTotal = getSummary($obj, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS amt
    FROM transaction_entry
    WHERE type = 'quotation' AND $dateWhere
");

// 2. Orders
$ordersTotal = getSummary($obj, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS amt
    FROM transaction_entry
    WHERE type = 'order' AND $dateWhere
");

// 3. Quotation Conversion (quotations that were converted to orders)
$quotationConversion = getSummary($obj, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS amt
    FROM transaction_entry
    WHERE type = 'quotation' AND conversion_status = 1 AND $dateWhere
");

// 4. No. Of Order (dispatch overview) - same order set as #2
$dispatchOrderCount = $ordersTotal;

// 5. Order Pending for dispatch
$orderPending = getSummary($obj, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS amt
    FROM transaction_entry
    WHERE type = 'order' AND dispatch_status = 0 AND $dateWhere
");

// 6. Total Order Cleared
$orderCleared = getSummary($obj, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS amt
    FROM transaction_entry
    WHERE type = 'order' AND dispatch_status = 1 AND $dateWhere
");

// 7. Items Pending for dispatch (line-item level, orders only)
$itemsPending = getSummary($obj, "
    SELECT COUNT(*) AS cnt, COALESCE(SUM(td.total_amt),0) AS amt
    FROM transaction_details td
    INNER JOIN transaction_entry te ON td.transaction_id = te.transaction_id
    WHERE te.type = 'order'
      AND td.is_dispatched != 1
      AND te.billdate BETWEEN '$fromdate' AND '$todate'
      AND te.companyid = $companyid
");

function fmtAmt($amt)
{
    return number_format((float) $amt, 2);
}

function detailsLink($pagename, $fromdate, $todate, $extra = [])
{
    $params = array_merge(['fromdate' => $fromdate, 'todate' => $todate], $extra);
    return $pagename . '?' . http_build_query($params);
}

$quotationConversionRate = $quotationTotal['count'] > 0
    ? ($quotationConversion['count'] / $quotationTotal['count']) * 100
    : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <style>
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #344767;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title:after {
            content: '';
            flex: 1;
            height: 2px;
            background: #eceff5;
        }

        .op-overview .card {
            overflow: hidden;
            transition: .25s;
        }

        .op-overview .top-card-label {
            padding: 8px;
            color: white;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
        }

        .op-overview .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, .08);
        }

        .op-overview .col-md-3:nth-child(2) a .card .top-card-label {
            background: #4e73df;
        }

        .op-overview .col-md-3:nth-child(3) a .card .top-card-label {
            background: #1cc88a;
        }

        .op-overview .col-md-3:nth-child(4) a .card .top-card-label {
            background: #36b9cc;
        }

        .op-overview .col-md-3:nth-child(5) a .card .top-card-label {
            background: #f63e3e;
        }

        .op-overview .col-md-4 a .card .top-card-label {
            background: #36b9cc;
        }

        .conversion-content {
            display: flex;
            align-items: center;
        }

        .conversion-details {
            flex: 1;
            padding-right: 15px;
        }

        .conversion-percentage {
            width: 90px;
            min-width: 90px;
            border-left: 1px solid #eee;
            padding-left: 15px;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .conversion-percentage strong {
            font-size: 18px;
            line-height: 1.2;
            margin-top: 3px;
        }
    </style>
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

            <div class="row op-overview mt-4">
                <div class="section-title">
                    <i class="bi bi-bar-chart-line-fill text-primary"></i>
                    Operations Overview
                </div>
                <div class="col-md-3">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'quotation']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">
                            <h6 class="top-card-label"><i class="bi bi-file-earmark-text"></i> Quotation Total</h6>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                <h4 class="mb-0"><?php echo $quotationTotal['count']; ?></h4>
                            </div>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                <h4 class="mb-0"><?php echo fmtAmt($quotationTotal['amount']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'order']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">
                            <h6 class="top-card-label"><i class="bi bi-cart-check"></i> Orders</h6>

                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                <h4 class="mb-0"><?php echo $ordersTotal['count']; ?></h4>
                            </div>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                <h4 class="mb-0"><?php echo fmtAmt($ordersTotal['amount']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'conversion']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">

                            <h6 class="top-card-label">
                                <i class="bi bi-arrow-repeat"></i> Quotation Conversion
                            </h6>

                            <div class="conversion-content">

                                <div class="conversion-details">
                                    <div class="d-flex justify-content-between text-black align-items-baseline">
                                        <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                        <h4 class="mb-0"><?php echo $quotationConversion['count']; ?></h4>
                                    </div>

                                    <div class="d-flex justify-content-between text-black align-items-baseline">
                                        <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                        <h4 class="mb-0"><?php echo fmtAmt($quotationConversion['amount']); ?></h4>
                                    </div>
                                </div>

                                <div class="conversion-percentage">
                                    <small class="text-black-50 fs-12 fw-semibold">
                                        Conversion
                                    </small>

                                    <strong class="text-success">
                                        <?php echo number_format($quotationConversionRate, 2); ?>%
                                    </strong>
                                </div>

                            </div>

                        </div>
                    </a>
                </div>

            </div>

            <div class="row op-overview mt-4">
                <div class="section-title">
                    <i class="bi bi-truck text-success"></i>
                    Dispatch Overview
                </div>
                <div class="col-md-3">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'order']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">
                            <h6 class="top-card-label"><i class="bi bi-box-seam"></i> No. Of Order</h6>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                <h4 class="mb-0"><?php echo $dispatchOrderCount['count']; ?></h4>
                            </div>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                <h4 class="mb-0"><?php echo fmtAmt($dispatchOrderCount['amount']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'order_pending']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">
                            <h6 class="top-card-label"><i class="bi bi-clock-history"></i> Order Pending for dispatch</h6>

                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                <h4 class="mb-0"><?php echo $orderPending['count']; ?></h4>
                            </div>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                <h4 class="mb-0"><?php echo fmtAmt($orderPending['amount']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'order_cleared']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">
                            <h6 class="top-card-label"><i class="bi bi-check-circle"></i> Total Order Cleared</h6>

                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                <h4 class="mb-0"><?php echo $orderCleared['count']; ?></h4>
                            </div>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                <h4 class="mb-0"><?php echo fmtAmt($orderCleared['amount']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo detailsLink('operations_report-details.php', $fromdate, $todate, ['view' => 'items_pending']); ?>" target="_blank">
                        <div class="card card-body border-0 rounded-4 shadow-sm">
                            <h6 class="top-card-label"><i class="bi bi-box-seam"></i> Items Pending for dispatch</h6>

                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Count</small>
                                <h4 class="mb-0"><?php echo $itemsPending['count']; ?></h4>
                            </div>
                            <div class="d-flex justify-content-between text-black align-items-baseline">
                                <small class="text-black-50 fs-12 fw-semibold">Amount</small>
                                <h4 class="mb-0"><?php echo fmtAmt($itemsPending['amount']); ?></h4>
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