<?php
include("../adminsession.php");
$title = "Operations Report";
$pagename = "operations_report-details.php";
$module = "Operations Report";
$submodule = "Operations Report";

$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d');
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
if (!preg_match($dateRegex, $fromdate)) {
    $fromdate = date('Y-m-d');
}
if (!preg_match($dateRegex, $todate)) {
    $todate   = date('Y-m-d');
}

$companyid = isset($_SESSION['companyid']) ? (int) $_SESSION['companyid'] : 1;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = str_replace(["'", '\\'], '', $search);

$allowedViews = ['quotation', 'order', 'conversion', 'order_pending', 'order_cleared', 'items_pending'];
$viewType = (isset($_GET['view']) && in_array($_GET['view'], $allowedViews, true))
    ? $_GET['view']
    : 'quotation';

$viewTitles = [
    'quotation'      => 'Quotation Total',
    'order'          => 'Orders',
    'conversion'     => 'Quotation Conversion',
    'order_pending'  => 'Order Pending for Dispatch',
    'order_cleared'  => 'Total Order Cleared',
    'items_pending'  => 'Items Pending for Dispatch',
];
$viewTitle = $viewTitles[$viewType];

$itemLevelViews = ['items_pending'];
$quotationStyleViews = ['quotation', 'conversion'];
$orderStyleViews = ['order', 'order_pending', 'order_cleared'];

$rows = [];

if (in_array($viewType, $itemLevelViews, true)) {
    $sql = "
    SELECT
        td.product_id,
        p.product_name,
        b.cat_name,

        SUM(td.qty) AS total_qty,
        COUNT(DISTINCT te.transaction_id) AS order_count,

        SUM(td.total_amt) / NULLIF(SUM(td.qty), 0) AS avg_rate,
        SUM(td.total_amt) AS total_amt,

        GROUP_CONCAT(
            DISTINCT CONCAT(
                a.account_name,
                ' (Order: ', te.billno, ')'
            )
            ORDER BY te.billdate DESC
            SEPARATOR ', '
        ) AS customer_orders

    FROM transaction_details td

    INNER JOIN transaction_entry te 
        ON te.transaction_id = td.transaction_id

    LEFT JOIN account a 
        ON a.account_id = te.account_id

    LEFT JOIN product_master p 
        ON p.product_id = td.product_id

    LEFT JOIN category_master b 
        ON b.cat_id = td.brand_id 
        AND b.type = 'brand'

    WHERE te.type = 'order'
      AND td.is_dispatched = 0
      AND te.billdate BETWEEN '$fromdate' AND '$todate'
      AND te.companyid = $companyid

      " . ($search !== '' ? "
        AND (
            te.billno LIKE '%$search%'
            OR a.account_name LIKE '%$search%'
            OR p.product_name LIKE '%$search%'
        )
      " : "") . "

    GROUP BY td.product_id
    ORDER BY p.product_name ASC
";
    $rows = $obj->executequery($sql);
} else {

    $typeCond = in_array($viewType, $quotationStyleViews, true) ? "te.type = 'quotation'" : "te.type = 'order'";

    $extraCond = '';
    if ($viewType === 'conversion') {
        $extraCond = " AND te.conversion_status = 1";
    } elseif ($viewType === 'order_pending') {
        $extraCond = " AND te.dispatch_status = 0";
    } elseif ($viewType === 'order_cleared') {
        $extraCond = " AND te.dispatch_status = 1";
    }

    // For conversion view, pull the resulting order's No/Date/Amount via a
    // deduplicated derived table (assumes one order per converted quotation).
    $convertedJoin = '';
    $convertedCols = '';
    if ($viewType === 'conversion') {
        $convertedCols = ", conv.conv_billno, conv.conv_billdate, conv.conv_amt";
        $convertedJoin = "
        LEFT JOIN (
            SELECT parent_transaction_id, billno AS conv_billno, billdate AS conv_billdate, grand_total AS conv_amt
            FROM transaction_entry
            WHERE type = 'order' AND parent_transaction_id IS NOT NULL AND parent_transaction_id > 0
        ) conv ON conv.parent_transaction_id = te.transaction_id";
    }

    $sql = "
        SELECT
            te.transaction_id,
            te.billno,
            te.billdate,
            a.account_name,
            a.mobile_no,
            te.remark,
            te.is_gst,
            te.grand_total,
            te.is_approved,
            te.dispatch_status,
            te.invoice_no,
            te.invoice_amt,
            te.conversion_status,
            cs.company_name,
            u.fullname
            $convertedCols
        FROM transaction_entry te
        LEFT JOIN account a ON a.account_id = te.account_id
        LEFT JOIN company_setting cs ON cs.company_id = te.companyid
        LEFT JOIN user u ON u.userid = te.createdby
        $convertedJoin
        WHERE $typeCond
          AND te.billdate BETWEEN '$fromdate' AND '$todate'
          AND te.companyid = $companyid
          $extraCond
          " . ($search !== '' ? "AND (te.billno LIKE '%$search%' OR a.account_name LIKE '%$search%' OR a.mobile_no LIKE '%$search%' OR te.invoice_no LIKE '%$search%')" : "") . "
        ORDER BY te.billdate DESC, te.transaction_id DESC
    ";
    $rows = $obj->executequery($sql);
}

// --- Summary totals for whatever is currently on screen ---
$totalCount = count($rows);
$totalAmount = 0.0;
foreach ($rows as $r) {
    $totalAmount += in_array($viewType, $itemLevelViews, true)
        ? (float) $r['total_amt']
        : (float) $r['grand_total'];
}

function fmtAmt($amt)
{
    return number_format((float) $amt, 2);
}

function dispatchBadge($status)
{
    return ((int) $status === 1)
        ? '<span class="badge bg-success">Dispatched</span>'
        : '<span class="badge bg-warning text-dark">Pending</span>';
}

function approveBadge($status)
{
    return ((int) $status === 1)
        ? '<span class="badge bg-success">Approved</span>'
        : '<span class="badge bg-warning text-dark">Pending</span>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
    <style>
        /* Summary Card */
        .summary-card {
            border-radius: 14px;
            background: #fff;
            border: 1px solid #eef0f4 !important;
            overflow: hidden;
            margin-top: 10px;
        }

        /* Individual item */
        .summary-item {
            position: relative;
            padding: 0 28px;
            min-width: 180px;
        }

        .summary-item:first-child {
            padding-left: 0;
        }

        .summary-item:last-child {
            padding-right: 0;
        }

        /* Vertical separator */
        .summary-item:not(:last-child)::after {
            content: "";
            position: absolute;
            right: 0;
            top: 5px;
            height: 38px;
            width: 1px;
            background: #e9ecef;
        }

        /* Label */
        .summary-label {
            display: block;
            color: #8a94a6;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 3px;
        }

        /* Value */
        .summary-value {
            display: block;
            color: #1f2937;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
        }

        /* Amount */
        .summary-amount {
            color: #03214e;
            font-size: 17px;
        }

        /* Date icon / number icon */
        .summary-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            background: #eaf1fa;
            color: #03214e;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 767px) {

            .summary-card-body {
                padding: 12px 15px !important;
            }

            .summary-item {
                width: 100%;
                padding: 10px 0;
            }

            .summary-item:not(:last-child)::after {
                display: none;
            }

            .summary-item:not(:last-child) {
                border-bottom: 1px solid #eef0f3;
            }
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
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <legend class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5"><?= $module ?> &mdash; <?= htmlspecialchars($viewTitle) ?></span>

                        <div>
                            <a href="javascript:void(0);" onclick="window.close();" class="btn btn-sm btn-danger">
                                <i class="bi bi-arrow-left-circle"></i> Back
                            </a>
                        </div>
                    </legend>
                </div>

                <!-- ===== Self-contained filter bar ===== -->
                <div class="col-lg-12 mb-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2">
                            <form class="row align-items-end g-2">
                                <input type="hidden" name="view" value="<?= htmlspecialchars($viewType) ?>">
                                <div class="col-md-3">
                                    <strong><label for="fromdate" class="small">From Date</label></strong>
                                    <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate"
                                        value="<?= htmlspecialchars($fromdate) ?>">
                                </div>
                                <div class="col-md-3">
                                    <strong><label for="todate" class="small">To Date</label></strong>
                                    <input type="date" class="form-control form-control-sm" name="todate" id="todate"
                                        value="<?= htmlspecialchars($todate) ?>">
                                </div>
                                <div class="col-md-3">
                                    <strong><label for="search" class="small">Search</label></strong>
                                    <input type="text" class="form-control form-control-sm" name="search" id="search"
                                        placeholder="Order No / Account / Mobile / Product"
                                        value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="submit" class="btn btn-primary btn-sm" name="submitSearch" value="Search">
                                    <a href="<?= $pagename ?>?view=<?= htmlspecialchars($viewType) ?>" class="btn btn-danger btn-sm">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ===== Summary strip ===== -->
                <div class="col-lg-4 mb-3">

                    <div class="card summary-card shadow-sm">

                        <div class="card-body summary-card-body">

                            <!-- Date Range -->
                            <div class="summary-item d-flex align-items-center">

                                <div class="summary-icon">
                                    <i class="bi bi-calendar3"></i>
                                </div>

                                <div>
                                    <small class="summary-label">
                                        Date Range
                                    </small>

                                    <span class="summary-value">
                                        <?= date('d-m-Y', strtotime($fromdate)) ?>
                                        <span class="text-muted fw-normal mx-1">to</span>
                                        <?= date('d-m-Y', strtotime($todate)) ?>
                                    </span>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card summary-card shadow-sm">

                        <div class="card-body summary-card-body">


                            <!-- Total Count -->
                            <div class="summary-item d-flex align-items-center">

                                <div class="summary-icon">
                                    <i class="bi bi-list"></i>
                                </div>

                                <div>
                                    <small class="summary-label">
                                        Total Count
                                    </small>

                                    <span class="summary-value">
                                        <?= $totalCount ?>
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card summary-card shadow-sm">

                        <div class="card-body summary-card-body">

                            <!-- Total Amount -->
                            <div class="summary-item d-flex align-items-center">

                                <div class="summary-icon">
                                    <i class="bi bi-currency-rupee"></i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-currency-rupee" viewBox="0 0 16 16">
                                        <path d="M4 3.06h2.726c1.22 0 2.12.575 2.325 1.724H4v1.051h5.051C8.855 7.001 8 7.558 6.788 7.558H4v1.317L8.437 14h2.11L6.095 8.884h.855c2.316-.018 3.465-1.476 3.688-3.049H12V4.784h-1.345c-.08-.778-.357-1.335-.793-1.732H12V2H4z" />
                                    </svg>
                                </div>

                                <div>
                                    <small class="summary-label">
                                        Total Amount
                                    </small>

                                    <span class="summary-value summary-amount">
                                        ₹ <?= fmtAmt($totalAmount) ?>
                                    </span>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-12 mb-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">

                                    <?php if (in_array($viewType, $itemLevelViews, true)): ?>
                                        <!-- ===== Items Pending for dispatch table ===== -->
                                        <table id="example" class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr class="table-info">
                                                    <th>SrNo.</th>
                                                    <th>Brand</th>
                                                    <th>Product</th>
                                                    <th>Customer & Order No.</th>
                                                    <th>Qty</th>
                                                    <th>No. of Orders</th>
                                                    <th style="text-align: right;">Rate</th>
                                                    <th style="text-align: right;">Value</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php if (empty($rows)): ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-3">
                                                            No records found for the selected date range.
                                                        </td>
                                                    </tr>
                                                <?php else: ?>

                                                    <?php
                                                    $sr = 1;
                                                    foreach ($rows as $r):
                                                    ?>
                                                        <tr>
                                                            <td><?= $sr++ ?>.</td>

                                                            <td>
                                                                <?= htmlspecialchars($r['cat_name'] ?? '') ?>
                                                            </td>

                                                            <td>
                                                                <?= htmlspecialchars($r['product_name'] ?? '') ?>
                                                            </td>

                                                            <td>
                                                                <?= htmlspecialchars($r['customer_orders'] ?? '') ?>
                                                            </td>

                                                            <td style="text-align: right;">
                                                                <?= htmlspecialchars($r['total_qty'] ?? 0) ?>
                                                            </td>

                                                            <td style="text-align: center;">
                                                                <?= htmlspecialchars($r['order_count'] ?? 0) ?>
                                                            </td>

                                                            <td style="text-align: right;">
                                                                <?= fmtAmt($r['avg_rate'] ?? 0) ?>
                                                            </td>

                                                            <td style="text-align: right;">
                                                                <?= fmtAmt($r['total_amt'] ?? 0) ?>
                                                            </td>
                                                        </tr>

                                                    <?php endforeach; ?>

                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                    <?php elseif (in_array($viewType, $quotationStyleViews, true)): ?>
                                        <table id="example" class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr class="table-info">
                                                    <th>SrNo.</th>
                                                    <th>Quotation No.</th>
                                                    <th>Quotation Date</th>
                                                    <?php if ($viewType === 'conversion'): ?>
                                                        <th>Order No.</th>
                                                        <th>Order Date</th>
                                                        <th style="text-align: right;">Order Amount</th>
                                                    <?php endif; ?>
                                                    <th>Customer Name</th>
                                                    <th>Mobile No.</th>
                                                    <th>Remark</th>
                                                    <th>Created By</th>
                                                    <th style="text-align: right;">Net_Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($rows)): ?>
                                                    <tr>
                                                        <td colspan="<?= $viewType === 'conversion' ? 11 : 8 ?>" class="text-center text-muted py-3">No records found for the selected date range.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $sr = 1;
                                                    foreach ($rows as $r): ?>
                                                        <tr>
                                                            <td><?= $sr++ ?>.</td>
                                                            <td><?= htmlspecialchars($r['billno']) ?></td>
                                                            <td><?= date('d-m-Y', strtotime($r['billdate'])) ?></td>
                                                            <?php if ($viewType === 'conversion'): ?>
                                                                <td><?= htmlspecialchars($r['conv_billno'] ?? '') ?></td>
                                                                <td><?= !empty($r['conv_billdate']) ? date('d-m-Y', strtotime($r['conv_billdate'])) : '' ?></td>
                                                                <td style="text-align: right;"><?= isset($r['conv_amt']) ? fmtAmt($r['conv_amt']) : '' ?></td>
                                                            <?php endif; ?>
                                                            <td><?= htmlspecialchars($r['account_name'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($r['mobile_no'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($r['remark'] ?? '') ?></td>
                                                            <td>
                                                                <?= htmlspecialchars($r['fullname']); ?>
                                                            </td>
                                                            <td style="text-align: right;"><?= fmtAmt($r['grand_total']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                    <?php else: ?>
                                        <!-- ===== Order / Order Pending / Order Cleared table ===== -->
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr class="table-info">
                                                    <th>SrNo.</th>
                                                    <th>Order No.</th>
                                                    <th>Order Date</th>
                                                    <th>Customer Name</th>
                                                    <th>Mobile No.</th>
                                                    <th>Remark</th>
                                                    <th style="text-align: right;">Net_Amount</th>
                                                    <th>Invoice No.</th>
                                                    <th>Created By</th>
                                                    <th style="text-align: center;">Order Status</th>
                                                    <th style="text-align: center;">Dispatch Status</th>
                                                    <th style="text-align: center;">Print</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($rows)): ?>
                                                    <tr>
                                                        <td colspan="12" class="text-center text-muted py-3">No records found for the selected date range.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php $sr = 1;
                                                    foreach ($rows as $r): ?>
                                                        <tr>
                                                            <td><?= $sr++ ?>.</td>
                                                            <td><?= htmlspecialchars($r['billno']) ?></td>
                                                            <td><?= date('d-m-Y', strtotime($r['billdate'])) ?></td>
                                                            <td><?= htmlspecialchars($r['account_name'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($r['mobile_no'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($r['remark'] ?? '') ?></td>
                                                            <td style="text-align: right;"><?= fmtAmt($r['grand_total']) ?></td>
                                                            <td><?= htmlspecialchars($r['invoice_no'] ?? '') ?></td>
                                                            <td>
                                                                <?= htmlspecialchars($r['fullname']); ?>
                                                            </td>
                                                            <td style="text-align: center;"><?= approveBadge($r['is_approved']) ?></td>
                                                            <td style="text-align: center;"><?= dispatchBadge($r['dispatch_status']) ?></td>
                                                            <td style="text-align: center;">
                                                                <a href="print_order.php?id=<?= (int) $r['transaction_id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="bi bi-printer"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>

                                </div>
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
        $("#example").DataTable();
    });
</script>

</html>