<?php
include("../adminsession.php");

$title = "Sales Report";
$pagename = "sales-details.php";
$module = "Sales Report";
$submodule = "Sales Report";

$emp_id = isset($_GET['emp_id']) ? $_GET['emp_id'] : 0;
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

$allowedViews = ['counter_total', 'order_total', 'beat_active', 'counter_active', 'counter_criteria', 'outstanding'];
$viewType = (isset($_GET['view']) && in_array($_GET['view'], $allowedViews, true))
    ? $_GET['view']
    : 'counter_total';

$viewTitles = [
    'counter_total'     => 'Total No. Of Counter',
    'order_total'       => 'Total Order From Sale / Total Value',
    'beat_active'       => 'Total Beat Active',
    'counter_active'    => 'Total Counter Active',
    'counter_criteria'  => 'Total Counter Active As Par Criteria',
    'outstanding'       => 'Total Outstanding As On Date',
];
$viewTitle = $viewTitles[$viewType];

// Aging buckets used by the 'outstanding' view. Adjust ranges here if the
// business wants different cutoffs.
$agingBuckets = [
    ['label' => '0-30 Days',      'min' => 0,  'max' => 30],
    ['label' => '31-45 Days',     'min' => 31, 'max' => 45],
    ['label' => '46-60 Days',     'min' => 46, 'max' => 60],
    ['label' => '61-90 Days',     'min' => 61, 'max' => 90],
    ['label' => 'Above 90 Days',  'min' => 91, 'max' => PHP_INT_MAX],
];

$whereName = $emp_id > 0 ? " AND userid ='$emp_id'" : '';

$executives = $obj->executequery(
    "SELECT userid, fullname FROM user WHERE usertype = 'sales'  $whereName ORDER BY fullname"
);



function fmtAmt($amt)
{
    return number_format((float) $amt, 2);
}

function getExecutiveStats(DataOperation $obj, int $emp_id, int $companyid, string $fromdate, string $todate): array
{
    $totalCounters = (int) $obj->getvalfield(
        "route_counter rc JOIN route_plan rp ON rp.batch_no = rc.batch_no",
        "COUNT(DISTINCT rc.account_id)",
        "rp.sales_executive_id = $emp_id
         AND rp.companyid = $companyid
         AND rc.is_active = 1"
    );

    $orderRows = $obj->executequery("
        SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS amt
        FROM transaction_entry
        WHERE createdby = $emp_id
          AND type = 'order'
          AND is_approved = 1
          AND billdate BETWEEN '$fromdate' AND '$todate'
          AND companyid = $companyid
    ");
    $totalOrders = (int) ($orderRows[0]['cnt'] ?? 0);
    $totalValue  = (float) ($orderRows[0]['amt'] ?? 0);

    // A counter counts as "active" only if it has an order (not just a visit) in range.
    $totalCounterActive = (int) $obj->getvalfield(
        "transaction_entry",
        "COUNT(DISTINCT account_id)",
        "createdby = $emp_id
         AND type = 'order'
         AND is_approved = 1
         AND billdate BETWEEN '$fromdate' AND '$todate'
         AND companyid = $companyid"
    );

    // A beat is "active" only if at least 70% of the counters assigned to it are
    // themselves active (i.e. have an order in range).
    $beatRows = $obj->executequery("
        SELECT rp.batch_no,
               COUNT(DISTINCT rc.account_id) AS total_counters,
               COUNT(DISTINCT te.account_id) AS active_counters
        FROM route_plan rp
        JOIN route_counter rc
            ON rc.batch_no = rp.batch_no
            AND rc.companyid = rp.companyid
            AND rc.is_active = 1
        LEFT JOIN (
            SELECT DISTINCT account_id
            FROM transaction_entry
            WHERE createdby = $emp_id
              AND type = 'order'
              AND is_approved = 1
              AND billdate BETWEEN '$fromdate' AND '$todate'
              AND companyid = $companyid
        ) te ON te.account_id = rc.account_id
        WHERE rp.sales_executive_id = $emp_id
          AND rp.companyid = $companyid
        GROUP BY rp.batch_no
    ");
    $totalBeatActive = 0;
    foreach ($beatRows as $b) {
        $total = (int) $b['total_counters'];
        $active = (int) $b['active_counters'];
        if ($total > 0 && ($active / $total) >= 0.70) {
            $totalBeatActive++;
        }
    }

    $accountSales = $obj->executequery("
        SELECT a.account_id, a.class, SUM(t.grand_total) AS sales
        FROM transaction_entry t
        JOIN account a ON a.account_id = t.account_id
        WHERE t.type = 'order'
          AND t.createdby = $emp_id
          AND t.billdate BETWEEN '$fromdate' AND '$todate'
          AND t.is_approved = 1
          AND t.companyid = $companyid
        GROUP BY a.account_id
    ");
    $configRows = $obj->executequery(
        "SELECT class, min_sales FROM kra_productivity_config WHERE companyid = $companyid"
    );
    $classMinSales = [];
    foreach ($configRows as $c) {
        $classMinSales[strtoupper($c['class'])] = $c['min_sales'];
    }
    $totalCounterActiveCriteria = 0;
    foreach ($accountSales as $acc) {
        $class = strtoupper($acc['class']);
        $min = $classMinSales[$class] ?? null;
        if ($min !== null && $acc['sales'] >= $min) {
            $totalCounterActiveCriteria++;
        }
    }

    $routeAccountsSql = "
        SELECT DISTINCT rc.account_id
        FROM route_counter rc
        JOIN route_plan rp ON rp.batch_no = rc.batch_no
        WHERE rp.sales_executive_id = $emp_id
          AND rp.companyid = $companyid
          AND rc.is_active = 1
    ";

    $openingBalance = (float) $obj->getvalfield(
        "account",
        "COALESCE(SUM(opening_balance),0)",
        "account_id IN ($routeAccountsSql)"
    );

    $transactionBalance = (float) $obj->getvalfield(
        "transaction_entry",
        "COALESCE(
            SUM(
                CASE
                    WHEN type = 'order' AND is_approved = 1 AND invoice_no != '' THEN invoice_amt
                    WHEN type = 'payment' AND pay_status = 1 THEN -(grand_total + IFNULL(cash_disc,0))
                    ELSE 0
                END
            ), 0
        )",
        "account_id IN ($routeAccountsSql)
         AND companyid = $companyid
         AND billdate <= '$todate'"
    );

    $totalOutstanding = $openingBalance + $transactionBalance;

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

function getExecutiveVisitData(DataOperation $obj, int $emp_id, int $companyid, string $fromdate, string $todate): array
{
    $sql = "SELECT
            r.route_name,
            r.day_of_week,
            rc.sequence,
            a.account_id,
            a.account_name,
            a.mobile_no,
            COUNT(de.entry_id) AS visits,
            MAX(de.checkin_time) AS last_visit

        FROM route_plan rp

        INNER JOIN route_counter rc
            ON rc.batch_no = rp.batch_no
            AND rc.companyid = rp.companyid
            AND rc.is_active = 1

        INNER JOIN account a
            ON a.account_id = rc.account_id

        INNER JOIN route r
            ON r.batch_no = rp.batch_no
            AND r.companyid = rp.companyid

        LEFT JOIN daily_entries de
            ON de.account_id = a.account_id
            AND de.createdby = rp.sales_executive_id
            AND de.companyid = $companyid
            AND DATE(de.checkin_time) BETWEEN '$fromdate' AND '$todate'

        WHERE rp.sales_executive_id = $emp_id
          AND rp.companyid = $companyid

        GROUP BY
            r.route_name,
            r.day_of_week,
            rc.sequence,
            a.account_id,
            a.account_name,
            a.mobile_no

        ORDER BY
            r.day_of_week,
            rc.sequence
    ";

    $rows = $obj->executequery($sql);

    $visitData = [];

    foreach ($rows as $row) {

        $route = $row['route_name'];
        $accId = $row['account_id'];

        if (!isset($visitData[$route])) {
            $visitData[$route] = [
                'day' => $row['day_of_week'],
                'accounts' => []
            ];
        }

        $visitData[$route]['accounts'][$accId] = [
            'account_name' => $row['account_name'],
            'mobile_no'   => $row['mobile_no'],
            'visits'      => (int) $row['visits'],
            'last_visit'  => $row['last_visit']
        ];
    }

    $totalCounters = 0;
    $totalVisits = 0;

    foreach ($visitData as $route) {
        foreach ($route['accounts'] as $acc) {

            $totalCounters++;
            if ($acc['visits'] > 0) {
                $totalVisits += $acc['visits'];
            }
        }
    }

    return [
        'visitData'       => $visitData,
        'totalCounters'   => $totalCounters,
        'totalVisits'     => $totalVisits
    ];
}
function getExecutiveRouteBreakdown(DataOperation $obj, int $emp_id, int $companyid, string $fromdate, string $todate, string $viewType, array $agingBuckets): array
{
    $baseRows = $obj->executequery("
        SELECT
            r.route_name,
            r.day_of_week,
            a.account_id,
            a.account_name,
            a.mobile_no,
            a.class,
            a.opening_balance
        FROM route_plan rp
        INNER JOIN route_counter rc ON rc.batch_no = rp.batch_no AND rc.companyid = rp.companyid AND rc.is_active = 1
        INNER JOIN account a ON a.account_id = rc.account_id
        INNER JOIN route r ON r.batch_no = rp.batch_no AND r.companyid = rp.companyid
        WHERE rp.sales_executive_id = $emp_id AND rp.companyid = $companyid
        ORDER BY r.day_of_week, rc.sequence
    ");

    $routes = [];
    $accountIds = [];
    foreach ($baseRows as $row) {
        $route = $row['route_name'];
        if (!isset($routes[$route])) {
            $routes[$route] = ['day' => $row['day_of_week'], 'accounts' => []];
        }
        $accId = $row['account_id'];
        if (!isset($routes[$route]['accounts'][$accId])) {
            $routes[$route]['accounts'][$accId] = [
                'account_name'    => $row['account_name'],
                'mobile_no'       => $row['mobile_no'],
                'class'           => $row['class'],
                'opening_balance' => (float) $row['opening_balance'],
                'metric'          => null,
            ];
        }
        $accountIds[$accId] = true;
    }

    $accountIdList = !empty($accountIds) ? implode(',', array_keys($accountIds)) : '0';

    switch ($viewType) {

        case 'counter_active':
            // "Active" = has at least one order in range, not just a visit.
            $metricRows = $obj->executequery("
                SELECT account_id, COUNT(*) AS orders, MAX(billdate) AS last_order
                FROM transaction_entry
                WHERE createdby = $emp_id
                  AND type = 'order'
                  AND is_approved = 1
                  AND billdate BETWEEN '$fromdate' AND '$todate'
                  AND companyid = $companyid
                GROUP BY account_id
            ");
            $metricMap = [];
            foreach ($metricRows as $m) {
                $metricMap[$m['account_id']] = [
                    'orders'     => (int) $m['orders'],
                    'last_order' => $m['last_order'],
                    'active'     => true,
                ];
            }
            foreach ($routes as $routeName => &$r) {
                foreach ($r['accounts'] as $accId => &$acc) {
                    $acc['metric'] = $metricMap[$accId] ?? ['orders' => 0, 'last_order' => null, 'active' => false];
                }
                unset($acc);

                // Only keep counters that actually placed an order in range.
                $r['accounts'] = array_filter($r['accounts'], function ($acc) {
                    return ($acc['metric']['orders'] ?? 0) > 0;
                });
                if (empty($r['accounts'])) {
                    unset($routes[$routeName]);
                }
            }
            unset($r);
            break;

        case 'beat_active':
            $activeRows = $obj->executequery("
                SELECT DISTINCT account_id
                FROM transaction_entry
                WHERE createdby = $emp_id
                  AND type = 'order'
                  AND is_approved = 1
                  AND billdate BETWEEN '$fromdate' AND '$todate'
                  AND companyid = $companyid
            ");
            $activeAccountIds = [];
            foreach ($activeRows as $a) {
                $activeAccountIds[$a['account_id']] = true;
            }
            foreach ($routes as $routeName => &$r) {
                $total = count($r['accounts']);
                $activeCount = 0;
                foreach ($r['accounts'] as $accId => &$acc) {
                    $isActive = isset($activeAccountIds[$accId]);
                    $acc['metric'] = ['active' => $isActive];
                    if ($isActive) {
                        $activeCount++;
                    }
                }
                unset($acc);
                $pct = $total > 0 ? round(($activeCount / $total) * 100) : 0;
                $r['active_counters']  = $activeCount;
                $r['total_beat_counters'] = $total;
                $r['active_pct']       = $pct;
                $r['is_beat_active']   = $pct >= 70;
            }
            unset($r);
            break;

        case 'order_total':
            // Pull each individual order (not just an aggregate) so it can be
            // shown per counter with its invoice no. / date / amount / dispatch status.
            $orderRows = $obj->executequery("
                SELECT transaction_id, account_id, billno, billdate, invoice_no,
                       invoice_amt, grand_total, dispatch_status
                FROM transaction_entry
                WHERE createdby = $emp_id
                  AND type = 'order'
                  AND is_approved = 1
                  AND billdate BETWEEN '$fromdate' AND '$todate'
                  AND companyid = $companyid
                ORDER BY billdate DESC, transaction_id DESC
            ");

            $ordersByAccount = [];
            $orderTransactionIds = [];
            foreach ($orderRows as $o) {
                $accId = $o['account_id'];
                $tranId = (int) $o['transaction_id'];
                $orderTransactionIds[] = $tranId;
                $ordersByAccount[$accId][] = [
                    'transaction_id'   => $tranId,
                    'billno'           => $o['billno'],
                    'billdate'         => $o['billdate'],
                    'invoice_no'       => $o['invoice_no'],
                    'invoice_amt'      => (float) $o['invoice_amt'],
                    'grand_total'      => (float) $o['grand_total'],
                    'dispatch_status'  => (int) $o['dispatch_status'],
                    'items'            => [],
                ];
            }

            // Product line items for those orders, fetched in one shot to avoid N+1 queries.
            $itemsByTransaction = [];
            if (!empty($orderTransactionIds)) {
                $tranIdList = implode(',', $orderTransactionIds);
                $itemRows = $obj->executequery("
                    SELECT td.transaction_id, pm.product_name, td.qty, td.rate, td.total_amt
                    FROM transaction_details td
                    JOIN product_master pm ON pm.product_id = td.product_id
                    WHERE td.transaction_id IN ($tranIdList)
                      AND td.type = 'order'
                    ORDER BY td.tran_detail_id
                ");
                foreach ($itemRows as $it) {
                    $itemsByTransaction[$it['transaction_id']][] = [
                        'product_name' => $it['product_name'],
                        'qty'          => $it['qty'],
                        'rate'         => (float) $it['rate'],
                        'total_amt'    => (float) $it['total_amt'],
                    ];
                }
            }

            foreach ($ordersByAccount as $accId => &$orders) {
                foreach ($orders as &$ord) {
                    $ord['items'] = $itemsByTransaction[$ord['transaction_id']] ?? [];
                }
                unset($ord);
            }
            unset($orders);

            foreach ($routes as $routeName => &$r) {
                foreach ($r['accounts'] as $accId => &$acc) {
                    $orders = $ordersByAccount[$accId] ?? [];
                    $cnt = count($orders);
                    $amt = 0.0;
                    foreach ($orders as $ord) {
                        $amt += $ord['grand_total'];
                    }
                    $acc['metric'] = ['cnt' => $cnt, 'amt' => $amt, 'orders' => $orders];
                }
                unset($acc);

                // Only keep counters that actually have orders in range.
                $r['accounts'] = array_filter($r['accounts'], function ($acc) {
                    return ($acc['metric']['cnt'] ?? 0) > 0;
                });
                if (empty($r['accounts'])) {
                    unset($routes[$routeName]);
                }
            }
            unset($r);
            break;

        case 'counter_criteria':
            $salesRows = $obj->executequery("
                SELECT account_id, COALESCE(SUM(grand_total),0) AS sales
                FROM transaction_entry
                WHERE createdby = $emp_id
                  AND type = 'order'
                  AND is_approved = 1
                  AND billdate BETWEEN '$fromdate' AND '$todate'
                  AND companyid = $companyid
                GROUP BY account_id
            ");
            $salesMap = [];
            foreach ($salesRows as $s) {
                $salesMap[$s['account_id']] = (float) $s['sales'];
            }
            $configRows = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config WHERE companyid = $companyid");
            $classMinSales = [];
            foreach ($configRows as $c) {
                $classMinSales[strtoupper($c['class'])] = (float) $c['min_sales'];
            }
            foreach ($routes as $routeName => &$r) {
                foreach ($r['accounts'] as $accId => &$acc) {
                    $sales = $salesMap[$accId] ?? 0.0;
                    $min = $classMinSales[strtoupper($acc['class'])] ?? null;
                    $qualified = ($min !== null && $sales >= $min);
                    $acc['metric'] = ['sales' => $sales, 'min' => $min, 'qualified' => $qualified];
                }
                unset($acc);

                // Only keep counters that had some sales in range — a counter
                // with 0 sales never had a chance to meet criteria, so it's
                // noise here rather than a meaningful "Not Qualified" result.
                $r['accounts'] = array_filter($r['accounts'], function ($acc) {
                    return ($acc['metric']['sales'] ?? 0) > 0;
                });
                if (empty($r['accounts'])) {
                    unset($routes[$routeName]);
                }
            }
            unset($r);
            break;

        case 'outstanding':
            // Unpaid invoices for these accounts, oldest first (needed to apply
            // payments FIFO against the oldest outstanding invoice).
            $invoiceRows = $obj->executequery("
                SELECT account_id, billdate, invoice_amt
                FROM transaction_entry
                WHERE account_id IN ($accountIdList)
                  AND companyid = $companyid
                  AND type = 'order'
                  AND is_approved = 1
                  AND invoice_no != ''
                  AND billdate <= '$todate'
                ORDER BY account_id, billdate ASC
            ");

            $paymentRows = $obj->executequery("
                SELECT account_id, COALESCE(SUM(grand_total + IFNULL(cash_disc,0)),0) AS paid
                FROM transaction_entry
                WHERE account_id IN ($accountIdList)
                  AND companyid = $companyid
                  AND type = 'payment'
                  AND pay_status = 1
                  AND billdate <= '$todate'
                GROUP BY account_id
            ");
            $paidMap = [];
            foreach ($paymentRows as $p) {
                $paidMap[$p['account_id']] = (float) $p['paid'];
            }

            $invoicesByAccount = [];
            foreach ($invoiceRows as $row) {
                $invoicesByAccount[$row['account_id']][] = [
                    'billdate' => $row['billdate'],
                    'amt'      => (float) $row['invoice_amt'],
                ];
            }

            $todateTs = strtotime($todate);

            $bucketFor = function (int $days) use ($agingBuckets) {
                foreach ($agingBuckets as $b) {
                    if ($days >= $b['min'] && $days <= $b['max']) {
                        return $b['label'];
                    }
                }
                return end($agingBuckets)['label'];
            };

            foreach ($routes as $routeName => &$r) {
                foreach ($r['accounts'] as $accId => &$acc) {

                    $remainingPayment = $paidMap[$accId] ?? 0.0;
                    $buckets = array_fill_keys(array_column($agingBuckets, 'label'), 0.0);

                    // Apply payments oldest-invoice-first; whatever's left unpaid gets aged
                    foreach ($invoicesByAccount[$accId] ?? [] as $inv) {
                        $due = $inv['amt'];
                        if ($remainingPayment > 0) {
                            $applied = min($remainingPayment, $due);
                            $due -= $applied;
                            $remainingPayment -= $applied;
                        }
                        if ($due > 0.01) {
                            $days  = max(0, (int) floor(($todateTs - strtotime($inv['billdate'])) / 86400));
                            $label = $bucketFor($days);
                            $buckets[$label] += $due;
                        }
                    }

                    // Opening balance has no invoice date of its own — treat it as
                    // the oldest money owed and drop it in the last bucket.
                    if (abs($acc['opening_balance']) > 0.01) {
                        $lastLabel = end($agingBuckets)['label'];
                        $buckets[$lastLabel] += $acc['opening_balance'];
                    }

                    $acc['metric'] = [
                        'outstanding' => array_sum($buckets),
                        'buckets'     => $buckets,
                    ];
                }
                unset($acc);

                // Only keep counters that actually carry an outstanding balance.
                $r['accounts'] = array_filter($r['accounts'], function ($acc) {
                    return abs($acc['metric']['outstanding']) > 0.01;
                });

                if (empty($r['accounts'])) {
                    unset($routes[$routeName]);
                }
            }
            unset($r);
            break;

        default:
            break;
    }

    return $routes;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <style>
        .kpi-strip {
            display: flex;
            flex-wrap: wrap;
            background: #f8f9fb;
            border-radius: .5rem;
            overflow: hidden;
        }

        .kpi-item {
            flex: 1 1 140px;
            padding: .55rem .9rem;
            border-right: 1px solid #eef0f2;
        }

        .kpi-item:last-child {
            border-right: none;
        }

        .kpi-item small {
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .65rem;
            color: #5a6572;
            font-weight: 600;
        }

        .kpi-item h5,
        .kpi-item h6 {
            margin: .1rem 0 0;
            font-weight: 700;
        }

        .route-accordion .accordion-button {
            padding: .55rem .85rem;
            background: #eef1f5;
        }

        .route-accordion .accordion-button:not(.collapsed) {
            background: #eaf1ff;
            box-shadow: none;
        }

        .route-name {
            font-size: .92rem;
            font-weight: 600;
            margin: 0;
            color: #1c2733;
        }

        .route-stats {
            font-size: .8rem;
            white-space: nowrap;
            color: #33404d;
            font-weight: 500;
        }

        .route-stats .dot {
            color: #8b93a1;
            margin: 0 .4rem;
            font-weight: 700;
        }

        .coverage-track {
            width: 80px;
            height: 7px;
            border-radius: 4px;
            background: #dfe3e8;
            border: 1px solid #cfd4da;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }

        .coverage-fill {
            height: 100%;
            display: block;
        }

        .day-badge {
            background: #495057;
            color: #fff;
            font-weight: 500;
            font-size: .7rem;
            padding: .2rem .5rem;
            border-radius: .3rem;
        }

        .week-text {
            color: #5a6572;
            font-weight: 500;
        }

        .table-sm td,
        .table-sm th {
            padding: .35rem .55rem;
            font-size: .82rem;
        }

        .accordion-button:not(.collapsed) {
            background-color: #eaf1ff;
        }

        .route-accordion .accordion-button {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .route-accordion .accordion-button::after {
            margin-left: .5rem;
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

                <!-- ===== Filter bar ===== -->
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
                                <div class="col-md-3">
                                    <input type="submit" class="btn btn-primary btn-sm" name="submitSearch" value="Search">
                                    <a href="<?= $pagename ?>?view=<?= htmlspecialchars($viewType) ?>" class="btn btn-danger btn-sm">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 mb-2">
                    <?php if (empty($executives)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inboxes display-4 text-muted"></i>
                            <p class="text-muted mt-3 mb-0">No sales executives found.</p>
                        </div>
                    <?php else: ?>

                        <div class="accordion" id="execAccordion">
                            <?php foreach ($executives as $exec): ?>
                                <?php
                                $emp_id = (int) $exec['userid'];
                                $execCollapseId = 'exec_' . $emp_id;
                                if ($viewType === 'counter_total') {

                                    $visitResult = getExecutiveVisitData(
                                        $obj,
                                        $emp_id,
                                        $companyid,
                                        $fromdate,
                                        $todate
                                    );

                                    $visitData = $visitResult['visitData'] ?? [];

                                    // Total assigned counters
                                    $grandTotalCounters = 0;

                                    // Counters having at least one visit in selected date range
                                    $grandTotalVisited = 0;

                                    // Total number of visits in selected date range
                                    $grandTotalVisits = 0;

                                    foreach ($visitData as $routeData) {
                                        $accounts = $routeData['accounts'] ?? [];
                                        foreach ($accounts as $acc) {
                                            $visits = (int) ($acc['visits'] ?? 0);
                                            $grandTotalCounters++;
                                            if ($visits > 0) {
                                                $grandTotalVisited++;
                                            }
                                            $grandTotalVisits += $visits;
                                        }
                                    }

                                    $grandTotalPending = $grandTotalCounters - $grandTotalVisited;

                                    $grandCoverage = $grandTotalCounters > 0
                                        ? round(($grandTotalVisited / $grandTotalCounters) * 100)
                                        : 0;

                                    $grandBarColor = $grandCoverage >= 90
                                        ? 'success'
                                        : ($grandCoverage >= 70
                                            ? 'primary'
                                            : ($grandCoverage >= 50 ? 'warning' : 'danger'));
                                } else {
                                    $execStats = getExecutiveStats($obj, $emp_id, $companyid, $fromdate, $todate);
                                    $routes = getExecutiveRouteBreakdown($obj, $emp_id, $companyid, $fromdate, $todate, $viewType, $agingBuckets);

                                    if ($viewType === 'outstanding') {
                                        $agingTotals = array_fill_keys(array_column($agingBuckets, 'label'), 0.0);
                                        $outstandingCounterCount = 0;
                                        foreach ($routes as $r) {
                                            foreach ($r['accounts'] as $acc) {
                                                $outstandingCounterCount++;
                                                foreach (($acc['metric']['buckets'] ?? []) as $label => $val) {
                                                    $agingTotals[$label] += $val;
                                                }
                                            }
                                        }
                                    }
                                }
                                ?>
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#<?= $execCollapseId ?>">
                                            <i class="bi bi-person-badge me-2 text-primary"></i>
                                            <?= htmlspecialchars($exec['fullname']) ?>
                                        </button>
                                    </h2>
                                    <div id="<?= $execCollapseId ?>" class="accordion-collapse collapse show" data-bs-parent="#execAccordion">
                                        <div class="accordion-body">
                                            <?php if ($viewType === 'counter_total'): ?>

                                                <div class="kpi-strip mb-3">

                                                    <div class="kpi-item">
                                                        <small>Routes</small>
                                                        <h5><?= count($visitData) ?></h5>
                                                    </div>

                                                    <div class="kpi-item">
                                                        <small>Total Counters</small>
                                                        <h5><?= $grandTotalCounters ?></h5>
                                                    </div>

                                                    <div class="kpi-item">
                                                        <small>Visited</small>
                                                        <h5 class="text-success">
                                                            <?= $grandTotalVisited ?>
                                                        </h5>
                                                    </div>

                                                    <div class="kpi-item">
                                                        <small>Total Visits</small>
                                                        <h5>
                                                            <?= $grandTotalVisits ?>
                                                        </h5>
                                                    </div>

                                                    <div class="kpi-item">
                                                        <small>Pending</small>
                                                        <h5 class="text-danger">
                                                            <?= $grandTotalPending ?>
                                                        </h5>
                                                    </div>

                                                    <div class="kpi-item" style="flex-basis:220px;">
                                                        <small>Coverage</small>

                                                        <div class="d-flex align-items-center">

                                                            <h5 class="mb-0 me-2">
                                                                <?= $grandCoverage ?>%
                                                            </h5>

                                                            <span class="coverage-track">
                                                                <span
                                                                    class="coverage-fill bg-<?= $grandBarColor ?>"
                                                                    style="width:<?= $grandCoverage ?>%">
                                                                </span>
                                                            </span>

                                                        </div>
                                                    </div>

                                                </div>

                                                <?php if (empty($visitData)): ?>

                                                    <div class="text-center text-muted py-4">
                                                        <i class="bi bi-signpost-2 fs-3 d-block mb-2"></i>
                                                        No routes assigned to this executive.
                                                    </div>

                                                <?php else: ?>

                                                    <div class="accordion route-accordion"
                                                        id="routeAccordion_<?= $emp_id ?>">

                                                        <?php
                                                        $routeIdx = 0;

                                                        foreach ($visitData as $route => $data):
                                                            $routeIdx++;

                                                            $accounts = $data['accounts'] ?? [];

                                                            $totalCounter = count($accounts);

                                                            $visited = 0;
                                                            $totalVisits = 0;

                                                            foreach ($accounts as $acc) {

                                                                $visits = (int) ($acc['visits'] ?? 0);

                                                                if ($visits > 0) {
                                                                    $visited++;
                                                                }

                                                                $totalVisits += $visits;
                                                            }

                                                            $pending = $totalCounter - $visited;

                                                            $coverage = $totalCounter > 0
                                                                ? round(($visited / $totalCounter) * 100)
                                                                : 0;

                                                            $barColor = $coverage >= 90
                                                                ? 'success'
                                                                : ($coverage >= 70
                                                                    ? 'primary'
                                                                    : ($coverage >= 50 ? 'warning' : 'danger'));

                                                            $routeCollapseId = 'route_' . $emp_id . '_' . $routeIdx;
                                                        ?>

                                                            <div class="accordion-item mb-2">

                                                                <h2 class="accordion-header">

                                                                    <button class="accordion-button collapsed"
                                                                        type="button"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#<?= $routeCollapseId ?>">

                                                                        <div>

                                                                            <span class="route-name">

                                                                                <i class="bi bi-signpost-split-fill me-1 text-primary"></i>

                                                                                <?= htmlspecialchars($route) ?>

                                                                                <span class="day-badge ms-2">
                                                                                    <?= htmlspecialchars($data['day']) ?>
                                                                                </span>

                                                                            </span>

                                                                        </div>

                                                                        <div class="route-stats text-nowrap">

                                                                            <span>
                                                                                Counters
                                                                                <strong><?= $totalCounter ?></strong>
                                                                            </span>

                                                                            <span class="dot">&bull;</span>

                                                                            <span class="text-success">
                                                                                Visited
                                                                                <strong><?= $visited ?></strong>
                                                                            </span>

                                                                            <span class="dot">&bull;</span>

                                                                            <span>
                                                                                Visits
                                                                                <strong><?= $totalVisits ?></strong>
                                                                            </span>

                                                                            <span class="dot">&bull;</span>

                                                                            <span class="text-danger">
                                                                                Pending
                                                                                <strong><?= $pending ?></strong>
                                                                            </span>

                                                                            <span class="dot">&bull;</span>

                                                                            <span>

                                                                                <strong><?= $coverage ?>%</strong>

                                                                                <span class="coverage-track">
                                                                                    <span
                                                                                        class="coverage-fill bg-<?= $barColor ?>"
                                                                                        style="width:<?= $coverage ?>%">
                                                                                    </span>
                                                                                </span>

                                                                            </span>

                                                                        </div>

                                                                    </button>

                                                                </h2>

                                                                <div id="<?= $routeCollapseId ?>"
                                                                    class="accordion-collapse collapse"
                                                                    data-bs-parent="#routeAccordion_<?= $emp_id ?>">

                                                                    <div class="accordion-body p-0">

                                                                        <div class="table-responsive">

                                                                            <table class="table table-hover table-sm align-middle table-bordered mb-0">

                                                                                <thead class="table-light">

                                                                                    <tr>
                                                                                        <th width="40">#</th>
                                                                                        <th>Counter</th>
                                                                                        <th>Mobile No.</th>
                                                                                        <th class="text-center">Visits</th>
                                                                                        <th>Last Visit</th>
                                                                                        <th class="text-center">Status</th>
                                                                                    </tr>

                                                                                </thead>

                                                                                <tbody>

                                                                                    <?php $i = 1; ?>

                                                                                    <?php foreach ($accounts as $acc): ?>

                                                                                        <?php
                                                                                        $visits = (int) ($acc['visits'] ?? 0);
                                                                                        $lastVisit = $acc['last_visit'] ?? null;
                                                                                        ?>

                                                                                        <tr>

                                                                                            <td>
                                                                                                <?= $i++ ?>
                                                                                            </td>

                                                                                            <td>
                                                                                                <?= htmlspecialchars($acc['account_name']) ?>
                                                                                            </td>

                                                                                            <td>
                                                                                                <?= htmlspecialchars($acc['mobile_no']) ?>
                                                                                            </td>

                                                                                            <td class="text-center">
                                                                                                <strong><?= $visits ?></strong>
                                                                                            </td>

                                                                                            <td>
                                                                                                <?= !empty($lastVisit)
                                                                                                    ? date('d-m-Y H:i', strtotime($lastVisit))
                                                                                                    : '-' ?>
                                                                                            </td>

                                                                                            <td class="text-center">

                                                                                                <?php if ($visits > 0): ?>

                                                                                                    <span class="badge bg-success">
                                                                                                        Visited
                                                                                                    </span>

                                                                                                <?php else: ?>

                                                                                                    <span class="badge bg-danger">
                                                                                                        Pending
                                                                                                    </span>

                                                                                                <?php endif; ?>

                                                                                            </td>

                                                                                        </tr>

                                                                                    <?php endforeach; ?>

                                                                                </tbody>

                                                                            </table>

                                                                        </div>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                        <?php endforeach; ?>

                                                    </div>

                                                <?php endif; ?>

                                            <?php else: ?>

                                                <div class="kpi-strip mb-3">
                                                    <div class="kpi-item">
                                                        <small>Total Counters</small>
                                                        <h5><?= $execStats['total_counter'] ?></h5>
                                                    </div>
                                                    <div class="kpi-item">
                                                        <small>Orders</small>
                                                        <h5><?= $execStats['total_order'] ?></h5>
                                                    </div>
                                                    <div class="kpi-item">
                                                        <small>Order Value</small>
                                                        <h5><?= fmtAmt($execStats['total_value']) ?></h5>
                                                    </div>
                                                    <div class="kpi-item">
                                                        <small>Beats Active</small>
                                                        <h5><?= $execStats['total_beat_active'] ?></h5>
                                                    </div>
                                                    <div class="kpi-item">
                                                        <small>Counters Active</small>
                                                        <h5><?= $execStats['total_counter_active'] ?></h5>
                                                    </div>
                                                    <div class="kpi-item">
                                                        <small>Active As Per Criteria</small>
                                                        <h5><?= $execStats['total_counter_criteria'] ?></h5>
                                                    </div>

                                                    <?php if ($viewType === 'outstanding'): ?>
                                                        <div class="kpi-item">
                                                            <small>Counters Outstanding</small>
                                                            <h5><?= $outstandingCounterCount ?></h5>
                                                        </div>
                                                        <?php foreach ($agingBuckets as $b): ?>
                                                            <div class="kpi-item">
                                                                <small><?= htmlspecialchars($b['label']) ?></small>
                                                                <h5><?= fmtAmt($agingTotals[$b['label']]) ?></h5>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="kpi-item">
                                                            <small>Outstanding</small>
                                                            <h5><?= fmtAmt($execStats['total_outstanding']) ?></h5>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (empty($routes)): ?>

                                                    <div class="text-center text-muted py-4">
                                                        <i class="bi bi-signpost-2 fs-3 d-block mb-2"></i>
                                                        <?php
                                                        $emptyMessages = [
                                                            'outstanding'      => 'No outstanding balances for this executive.',
                                                            'order_total'      => 'No counters with orders in this date range.',
                                                            'counter_active'   => 'No active counters (with orders) in this date range.',
                                                            'counter_criteria' => 'No counters with sales in this date range.',
                                                        ];
                                                        echo $emptyMessages[$viewType] ?? 'No routes assigned to this executive.';
                                                        ?>
                                                    </div>

                                                <?php else: ?>

                                                    <div class="accordion route-accordion" id="routeAccordion_<?= $emp_id ?>">

                                                        <?php $routeIdx = 0; ?>
                                                        <?php foreach ($routes as $route => $data): ?>
                                                            <?php
                                                            $routeIdx++;
                                                            $accounts = $data['accounts'] ?? [];
                                                            $routeCollapseId = 'route_' . $emp_id . '_' . $routeIdx;
                                                            ?>

                                                            <div class="accordion-item mb-2">
                                                                <h2 class="accordion-header">
                                                                    <button class="accordion-button collapsed" type="button"
                                                                        data-bs-toggle="collapse" data-bs-target="#<?= $routeCollapseId ?>">
                                                                        <span class="route-name">
                                                                            <i class="bi bi-signpost-split-fill me-1 text-primary"></i>
                                                                            <?= htmlspecialchars($route) ?>
                                                                            <span class="day-badge ms-2"><?= htmlspecialchars($data['day']) ?></span>
                                                                        </span>
                                                                        <span class="route-stats text-nowrap">
                                                                            <?php if ($viewType === 'beat_active'): ?>
                                                                                <span>
                                                                                    <strong><?= $data['active_counters'] ?? 0 ?></strong>
                                                                                    / <?= $data['total_beat_counters'] ?? count($accounts) ?> active
                                                                                </span>
                                                                                <span class="dot">&bull;</span>
                                                                                <strong><?= $data['active_pct'] ?? 0 ?>%</strong>
                                                                                <?php if (!empty($data['is_beat_active'])): ?>
                                                                                    <span class="badge bg-success ms-1">Active</span>
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-secondary ms-1">Inactive</span>
                                                                                <?php endif; ?>
                                                                            <?php elseif ($viewType === 'order_total'): ?>
                                                                                <?php
                                                                                    $routeOrderCount = 0;
                                                                                    $routeOrderValue = 0.0;
                                                                                    foreach ($accounts as $a) {
                                                                                        $m = $a['metric'] ?? [];
                                                                                        $routeOrderCount += $m['cnt'] ?? 0;
                                                                                        $routeOrderValue += $m['amt'] ?? 0;
                                                                                    }
                                                                                ?>
                                                                                <span><strong><?= $routeOrderCount ?></strong> orders</span>
                                                                                <span class="dot">&bull;</span>
                                                                                <span><strong><?= fmtAmt($routeOrderValue) ?></strong></span>
                                                                            <?php elseif ($viewType === 'outstanding'): ?>
                                                                                <?php
                                                                                    $routeOutstandingTotal = 0.0;
                                                                                    foreach ($accounts as $a) {
                                                                                        $routeOutstandingTotal += $a['metric']['outstanding'] ?? 0;
                                                                                    }
                                                                                ?>
                                                                                <span><strong><?= count($accounts) ?></strong> counters outstanding</span>
                                                                                <span class="dot">&bull;</span>
                                                                                <span><strong><?= fmtAmt($routeOutstandingTotal) ?></strong></span>
                                                                            <?php else: ?>
                                                                                <strong><?= count($accounts) ?></strong> counters
                                                                            <?php endif; ?>
                                                                        </span>
                                                                    </button>
                                                                </h2>
                                                                <div id="<?= $routeCollapseId ?>" class="accordion-collapse collapse"
                                                                    data-bs-parent="#routeAccordion_<?= $emp_id ?>">
                                                                    <div class="accordion-body p-0">

                                                                        <?php if ($viewType === 'order_total'): ?>
                                                                            <div class="table-responsive">
                                                                                <?php foreach ($accounts as $acc): ?>
                                                                                    <?php
                                                                                    $m = $acc['metric'] ?? [];
                                                                                    $orders = $m['orders'] ?? [];
                                                                                    ?>
                                                                                    <details class="border-bottom px-2 py-1">
                                                                                        <summary style="cursor:pointer;" class="d-flex justify-content-between align-items-center py-1">
                                                                                            <span>
                                                                                                <strong><?= htmlspecialchars($acc['account_name']) ?></strong>
                                                                                                <span class="text-muted small ms-1"><?= htmlspecialchars($acc['mobile_no']) ?></span>
                                                                                            </span>
                                                                                            <span class="route-stats">
                                                                                                <strong><?= count($orders) ?></strong> orders
                                                                                                <span class="dot">&bull;</span>
                                                                                                <?= fmtAmt($m['amt'] ?? 0) ?>
                                                                                            </span>
                                                                                        </summary>

                                                                                        <?php if (empty($orders)): ?>
                                                                                            <div class="text-muted small py-2 ps-3">No orders in this date range.</div>
                                                                                        <?php else: ?>
                                                                                            <table class="table table-sm table-borderless mb-2 ms-3" style="width:calc(100% - 1rem);">
                                                                                                <thead>
                                                                                                    <tr class="text-muted small">
                                                                                                        <th>Bill No.</th>
                                                                                                        <th>Invoice No.</th>
                                                                                                        <th>Date</th>
                                                                                                        <th class="text-end">Amount</th>
                                                                                                        <th class="text-center">Status</th>
                                                                                                    </tr>
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                    <?php foreach ($orders as $ord): ?>
                                                                                                        <tr>
                                                                                                            <td colspan="5" class="p-0">
                                                                                                                <details>
                                                                                                                    <summary style="cursor:pointer; list-style:none;" class="d-flex align-items-center py-1">
                                                                                                                        <span style="flex:0 0 18%"><?= htmlspecialchars($ord['billno']) ?></span>
                                                                                                                        <span style="flex:0 0 24%"><?= htmlspecialchars($ord['invoice_no'] ?: '-') ?></span>
                                                                                                                        <span style="flex:0 0 18%"><?= date('d-m-Y', strtotime($ord['billdate'])) ?></span>
                                                                                                                        <span style="flex:0 0 22%" class="text-end pe-2">
                                                                                                                            <?= fmtAmt($ord['invoice_amt'] > 0 ? $ord['invoice_amt'] : $ord['grand_total']) ?>
                                                                                                                        </span>
                                                                                                                        <span style="flex:0 0 18%" class="text-center">
                                                                                                                            <?php if ($ord['dispatch_status'] == 1): ?>
                                                                                                                                <span class="badge bg-success">Dispatched</span>
                                                                                                                            <?php else: ?>
                                                                                                                                <span class="badge bg-warning text-dark">Pending</span>
                                                                                                                            <?php endif; ?>
                                                                                                                        </span>
                                                                                                                    </summary>

                                                                                                                    <?php if (empty($ord['items'])): ?>
                                                                                                                        <div class="text-muted small py-1 ps-4">No product-level detail found for this order.</div>
                                                                                                                    <?php else: ?>
                                                                                                                        <table class="table table-sm mb-2 ms-4" style="width:calc(100% - 2rem);">
                                                                                                                            <thead>
                                                                                                                                <tr class="text-muted small">
                                                                                                                                    <th>Product</th>
                                                                                                                                    <th class="text-end">Qty</th>
                                                                                                                                    <th class="text-end">Rate</th>
                                                                                                                                    <th class="text-end">Amount</th>
                                                                                                                                </tr>
                                                                                                                            </thead>
                                                                                                                            <tbody>
                                                                                                                                <?php foreach ($ord['items'] as $item): ?>
                                                                                                                                    <tr>
                                                                                                                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                                                                                                        <td class="text-end"><?= htmlspecialchars($item['qty']) ?></td>
                                                                                                                                        <td class="text-end"><?= fmtAmt($item['rate']) ?></td>
                                                                                                                                        <td class="text-end"><?= fmtAmt($item['total_amt']) ?></td>
                                                                                                                                    </tr>
                                                                                                                                <?php endforeach; ?>
                                                                                                                            </tbody>
                                                                                                                        </table>
                                                                                                                    <?php endif; ?>
                                                                                                                </details>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    <?php endforeach; ?>
                                                                                                </tbody>
                                                                                            </table>
                                                                                        <?php endif; ?>
                                                                                    </details>
                                                                                <?php endforeach; ?>
                                                                            </div>

                                                                        <?php elseif ($viewType === 'outstanding'): ?>

                                                                            <div class="table-responsive">
                                                                                <table class="table table-hover table-sm align-middle table-bordered mb-0">
                                                                                    <thead class="table-light">
                                                                                        <tr>
                                                                                            <th width="40">#</th>
                                                                                            <th>Counter</th>
                                                                                            <th>Mobile No.</th>
                                                                                            <?php foreach ($agingBuckets as $b): ?>
                                                                                                <th class="text-end"><?= htmlspecialchars($b['label']) ?></th>
                                                                                            <?php endforeach; ?>
                                                                                            <th class="text-end">Total Outstanding</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php
                                                                                        $i = 1;
                                                                                        $routeBucketTotals = array_fill_keys(array_column($agingBuckets, 'label'), 0.0);
                                                                                        $routeGrandTotal = 0.0;
                                                                                        ?>
                                                                                        <?php foreach ($accounts as $acc): ?>
                                                                                            <?php
                                                                                            $m       = $acc['metric'] ?? [];
                                                                                            $buckets = $m['buckets'] ?? [];
                                                                                            $total   = $m['outstanding'] ?? 0;
                                                                                            $routeGrandTotal += $total;
                                                                                            ?>
                                                                                            <tr>
                                                                                                <td><?= $i++ ?></td>
                                                                                                <td><?= htmlspecialchars($acc['account_name']) ?></td>
                                                                                                <td><?= htmlspecialchars($acc['mobile_no']) ?></td>
                                                                                                <?php foreach ($agingBuckets as $b): ?>
                                                                                                    <?php
                                                                                                    $val = $buckets[$b['label']] ?? 0;
                                                                                                    $routeBucketTotals[$b['label']] += $val;
                                                                                                    ?>
                                                                                                    <td class="text-end"><?= $val > 0.01 ? fmtAmt($val) : '-' ?></td>
                                                                                                <?php endforeach; ?>
                                                                                                <td class="text-end"><strong><?= fmtAmt($total) ?></strong></td>
                                                                                            </tr>
                                                                                        <?php endforeach; ?>
                                                                                    </tbody>
                                                                                    <tfoot>
                                                                                        <tr class="table-light">
                                                                                            <th colspan="3" class="text-end">Route Total</th>
                                                                                            <?php foreach ($agingBuckets as $b): ?>
                                                                                                <th class="text-end"><?= fmtAmt($routeBucketTotals[$b['label']]) ?></th>
                                                                                            <?php endforeach; ?>
                                                                                            <th class="text-end"><?= fmtAmt($routeGrandTotal) ?></th>
                                                                                        </tr>
                                                                                    </tfoot>
                                                                                </table>
                                                                            </div>

                                                                        <?php else: ?>

                                                                            <div class="table-responsive">
                                                                                <table class="table table-hover table-sm align-middle table-bordered mb-0">
                                                                                    <thead class="table-light">
                                                                                        <tr>
                                                                                            <th width="40">#</th>
                                                                                            <th>Counter</th>
                                                                                            <th>Mobile No.</th>
                                                                                            <th class="text-center">
                                                                                                <?php
                                                                                                switch ($viewType) {
                                                                                                    case 'counter_active':
                                                                                                        echo 'Status / Orders / Last Order';
                                                                                                        break;
                                                                                                    case 'beat_active':
                                                                                                        echo 'Status';
                                                                                                        break;
                                                                                                    case 'counter_criteria':
                                                                                                        echo 'Sales / Criteria';
                                                                                                        break;
                                                                                                    default:
                                                                                                        echo 'Metric';
                                                                                                }
                                                                                                ?>
                                                                                            </th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php $i = 1; ?>
                                                                                        <?php foreach ($accounts as $acc): ?>
                                                                                            <tr>
                                                                                                <td><?= $i++ ?></td>
                                                                                                <td><?= htmlspecialchars($acc['account_name']) ?></td>
                                                                                                <td><?= htmlspecialchars($acc['mobile_no']) ?></td>
                                                                                                <td class="text-center">
                                                                                                    <?php $m = $acc['metric'] ?? []; ?>
                                                                                                    <?php if ($viewType === 'counter_active'): ?>
                                                                                                        <?php if (!empty($m['active'])): ?>
                                                                                                            <span class="badge bg-success">Active</span>
                                                                                                        <?php else: ?>
                                                                                                            <span class="badge bg-secondary">Inactive</span>
                                                                                                        <?php endif; ?>
                                                                                                        <span class="ms-1">
                                                                                                            <strong><?= (int) ($m['orders'] ?? 0) ?></strong> orders
                                                                                                        </span>
                                                                                                        &bull;
                                                                                                        <?= !empty($m['last_order'])
                                                                                                            ? date('d-m-Y', strtotime($m['last_order']))
                                                                                                            : '-' ?>
                                                                                                    <?php elseif ($viewType === 'beat_active'): ?>
                                                                                                        <?php if (!empty($m['active'])): ?>
                                                                                                            <span class="badge bg-success">Active</span>
                                                                                                        <?php else: ?>
                                                                                                            <span class="badge bg-secondary">Inactive</span>
                                                                                                        <?php endif; ?>
                                                                                                    <?php elseif ($viewType === 'counter_criteria'): ?>
                                                                                                        <?= fmtAmt($m['sales'] ?? 0) ?>
                                                                                                        <?php if (!empty($m['qualified'])): ?>
                                                                                                            <span class="badge bg-success ms-1">Qualified</span>
                                                                                                        <?php else: ?>
                                                                                                            <span class="badge bg-secondary ms-1">Not Qualified</span>
                                                                                                        <?php endif; ?>
                                                                                                        <span class="text-muted small ms-1">
                                                                                                            (Min: <?= $m['min'] !== null ? fmtAmt($m['min']) : 'n/a' ?>)
                                                                                                        </span>
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php endforeach; ?>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>

                                                                        <?php endif; ?>

                                                                    </div>
                                                                </div>
                                                            </div>

                                                        <?php endforeach; ?>

                                                    </div>

                                                <?php endif; ?>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

</body>

<?php include('component/script.php'); ?>

</html>