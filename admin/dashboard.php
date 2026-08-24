<?php include("../adminsession.php");

$title    = "Dashboard";
$pagename = "dashboard.php";
$today      = date('Y-m-d');
$curMonth   = date('m');
$curYear    = date('Y');
$monthStart = date('Y-m-01');

$totalCustomers   = $obj->getvalfield("account", "count(*)", "type='customer'");
$todayQuo         = $obj->getvalfield("transaction_entry", "count(*)", "DATE(createdate)='$today' and type='quotation' and companyid='$companyid'");
$todayOrders      = $obj->getvalfield("transaction_entry", "count(*)", "billdate='$today' and type='order' and companyid='$companyid'");
$todayCollection  = $obj->getvalfield("transaction_entry", "sum(grand_total)", "billdate='$today' and type='payment' and companyid='$companyid'") ?: 0;
$pendingApprovals = $obj->getvalfield("transaction_entry", "count(*)", "type='order' and is_approved=0 and companyid='$companyid'");
$pendingDispatch  = $obj->getvalfield("transaction_entry", "count(*)", "type='order' AND is_approved=1 AND dispatch_status=0 AND companyid='$companyid'");

$monthOrders     = $obj->getvalfield("transaction_entry", "count(*)", "billdate>='$monthStart' and type='order' and companyid='$companyid'");
$monthCollection = $obj->getvalfield("transaction_entry", "sum(grand_total)", "billdate>='$monthStart' and type='payment' and companyid='$companyid'") ?: 0;
$monthVisits     = $obj->getvalfield("daily_entries", "count(*)", "DATE(createdate)>='$monthStart' and companyid='$companyid'");
$activeSchemes   = $obj->getvalfield("scheme_entry", "count(*)", "todate>='$today' and companyid='$companyid'");

$activeCounters = $obj->getvalfield("account a INNER JOIN transaction_entry t ON a.account_id = t.account_id", "COUNT(DISTINCT a.account_id)", "a.type='customer' AND t.type='order' and a.common_id=7");
$inactiveCounters = $obj->getvalfield("account a LEFT JOIN transaction_entry t ON a.account_id=t.account_id AND t.type='order'", "COUNT(DISTINCT a.account_id)", "a.type='customer' AND t.account_id IS NULL and a.common_id=7");
$totalCounters   = $obj->getvalfield("account", "count(*)", "type='customer' and common_id=7");

$overduePayment = $obj->executequery("SELECT
        COUNT(*)            AS overdue_count,
        COALESCE(SUM(te.grand_total),0) AS overdue_amount
    FROM transaction_entry te
    WHERE te.type   = 'order' and te.invoice_no<>''
      AND te.is_approved = 1
      AND te.companyid   = '$companyid'
      AND DATEDIFF('$today', te.billdate) > 45
      AND te.transaction_id NOT IN (
          SELECT DISTINCT p.ref_bill_id
          FROM transaction_entry p
          WHERE p.type='payment'
            AND p.companyid='$companyid'
            AND p.ref_bill_id IS NOT NULL
      )
");
$overdueCount  = $overduePayment[0]['overdue_count']  ?? 0;
$overdueAmount = $overduePayment[0]['overdue_amount'] ?? 0;

$pendingSummary = $obj->executequery("
SELECT
    SUM(
        CASE
            WHEN is_approved = 1
             AND dispatch_status = 0
             AND DATEDIFF('$today', billdate) > 10
            THEN 1 ELSE 0
        END
    ) AS disp_count,

    SUM(
        CASE
            WHEN is_approved = 1
             AND dispatch_status = 0
             AND DATEDIFF('$today', billdate) > 10
            THEN grand_total ELSE 0
        END
    ) AS disp_amount,

    SUM(
        CASE
            WHEN is_approved = 1
             AND (invoice_no IS NULL OR invoice_no = '')
            THEN 1 ELSE 0
        END
    ) AS inv_count,

    SUM(
        CASE
            WHEN is_approved = 1
             AND (invoice_no IS NULL OR invoice_no = '')
            THEN grand_total ELSE 0
        END
    ) AS inv_amount

FROM transaction_entry
WHERE type='order'
  AND companyid='$companyid'
");

$longDispCount      = $pendingSummary[0]['disp_count'] ?? 0;
$longDispAmount     = $pendingSummary[0]['disp_amount'] ?? 0;

$invoicePendingCount  = $pendingSummary[0]['inv_count'] ?? 0;

$repTargets = $obj->executequery("
    SELECT
        u.userid,
        u.fullname,
        COALESCE(mt.rep_target, 0) AS rep_target,
        COALESCE(ach.rep_achieved, 0) AS rep_achieved
    FROM user u

    LEFT JOIN (
        SELECT
            createdby,
            SUM(total_target) AS rep_target
        FROM monthly_target
        WHERE month='$curMonth'
          AND year='$curYear'
        GROUP BY createdby
    ) mt ON mt.createdby = u.userid

    LEFT JOIN (
        SELECT
            rp.sales_executive_id,
            SUM(td.net_amt) AS rep_achieved
        FROM route_plan rp

        INNER JOIN route_counter rc
            ON rc.batch_no = rp.batch_no
           AND rc.is_active = 1

        INNER JOIN transaction_entry te
            ON te.account_id = rc.account_id
           AND te.type = 'order'
           AND te.is_approved = 1
           AND MONTH(te.billdate) = '$curMonth'
           AND YEAR(te.billdate) = '$curYear'

        INNER JOIN transaction_details td
        ON td.transaction_id = te.transaction_id AND td.brand_id != 35

        GROUP BY rp.sales_executive_id
    ) ach ON ach.sales_executive_id = u.userid

    WHERE u.companyid='$companyid'
      AND u.status=1
      AND u.usertype='sales'

    ORDER BY mt.rep_target DESC
");

$schemeByCounter = $obj->executequery("SELECT
        se.scheme_id,
        se.scheme_name,
        sd.qty        AS slab_qty,
        sd.output,
        te.account_id,
        a.account_name,
        SUM(oi.qty)   AS achieved
    FROM scheme_entry se
    JOIN scheme_details sd  ON sd.scheme_id = se.scheme_id
    JOIN transaction_entry te
         ON te.billdate BETWEEN se.from_date AND se.todate
        AND te.type='order'
        AND te.companyid='$companyid'
    JOIN transaction_details oi
         ON oi.transaction_id = te.transaction_id
        AND oi.product_id = sd.product_id
    JOIN account a ON a.account_id = te.account_id
    WHERE se.todate >= '$today'
      AND se.companyid='$companyid'
    GROUP BY se.scheme_id, sd.qty, te.account_id
    ORDER BY se.scheme_id, sd.qty ASC
");

$counterSchemes = [];
foreach ($schemeByCounter as $row) {
    $key = $row['scheme_id'] . '_' . $row['account_id'];
    if (!isset($counterSchemes[$key])) {
        $counterSchemes[$key] = [
            'scheme_name'   => $row['scheme_name'],
            'account_name'  => $row['account_name'],
            'achieved'      => $row['achieved'],
            'current_slab'  => 0,
            'next_slab'     => 0,
            'reward'        => ''
        ];
    }
    if ($row['achieved'] >= $row['slab_qty']) {
        $counterSchemes[$key]['current_slab'] = $row['slab_qty'];
        $counterSchemes[$key]['reward']       = $row['output'];
    }
    if ($row['achieved'] < $row['slab_qty'] && $counterSchemes[$key]['next_slab'] == 0)
        $counterSchemes[$key]['next_slab'] = $row['slab_qty'];
}

foreach ($counterSchemes as &$f) {
    if ($f['next_slab'] > 0) {
        $f['pct']     = min(99, round(($f['achieved'] / $f['next_slab']) * 100));
        $f['balance'] = $f['next_slab'] - $f['achieved'];
        $f['status']  = 'Running';
    } else {
        $f['pct']     = 100;
        $f['balance'] = 0;
        $f['status']  = 'Max Achieved';
    }
}
unset($f);

// Sort: closest to completing next slab first (highest pct first)
usort($counterSchemes, fn($a, $b) => $b['pct'] <=> $a['pct']);

// ── Existing queries kept ─────────────────────────────────────────────────
$repPerf = $obj->executequery("
    SELECT
        u.userid,
        u.fullname,
        COUNT(DISTINCT te.transaction_id)       AS orders,
        COALESCE(SUM(CASE WHEN te.type='payment' THEN te.grand_total END), 0) AS collection,
        (SELECT COUNT(*) FROM daily_entries de2
         WHERE de2.createdby = u.userid
           AND DATE(de2.createdate) >= '$monthStart'
           AND de2.companyid = '$companyid')     AS visits,
        (SELECT COUNT(*) FROM daily_entries de3
         WHERE de3.createdby = u.userid
           AND DATE(de3.createdate) = '$today'
           AND de3.companyid = '$companyid')     AS today_visits
    FROM user u
    LEFT JOIN transaction_entry te
           ON te.createdby = u.userid
          AND te.companyid = '$companyid'
          AND te.billdate >= '$monthStart'
    WHERE u.companyid = '$companyid'
      AND u.status = 1
      AND u.usertype = 'sales'
    GROUP BY u.userid
    ORDER BY collection DESC
");

$recentQuo = $obj->executequery("
    SELECT te.transaction_id, te.billno, te.billdate, te.grand_total, te.is_gst,
           a.account_name
    FROM transaction_entry te
    LEFT JOIN account a ON a.account_id = te.account_id
    WHERE te.type='quotation' AND te.companyid='$companyid'
    ORDER BY te.createdate DESC LIMIT 8
");

$recentOrders = $obj->executequery("
    SELECT te.transaction_id, te.billno, te.billdate, te.grand_total, te.is_approved,
           a.account_name, u.fullname AS salesrep
    FROM transaction_entry te
    LEFT JOIN account a ON a.account_id = te.account_id
    LEFT JOIN user    u ON u.userid = te.createdby
    WHERE te.type='order' AND te.companyid='$companyid'
    ORDER BY te.createdate DESC LIMIT 8
");

$todayActivity = $obj->executequery("
    SELECT u.fullname, COUNT(de.entry_id) AS visits,
           MAX(de.createdate) AS last_seen
    FROM daily_entries de
    JOIN user u ON u.userid = de.createdby
    WHERE DATE(de.createdate)='$today' AND de.companyid='$companyid'
    GROUP BY de.createdby ORDER BY visits DESC LIMIT 6
");

$topKRA = $obj->executequery("
    SELECT u.fullname, mk.total_score, mk.achievement_pct, mk.month, mk.year
    FROM monthly_kra mk
    JOIN user u ON u.userid = mk.emp_id
    WHERE (mk.month, mk.year) = (
        SELECT month, year FROM monthly_kra
        WHERE companyid='$companyid'
        ORDER BY year DESC, month DESC LIMIT 1
    ) AND mk.companyid='$companyid'
    ORDER BY mk.total_score DESC LIMIT 5
");
$kraMonthLabel = !empty($topKRA)
    ? date('M Y', mktime(0, 0, 0, (int)$topKRA[0]['month'], 1, (int)$topKRA[0]['year']))
    : date('M Y');

$salesTrend = $obj->executequery("
    SELECT DATE_FORMAT(billdate,'%b %y') AS mon, SUM(grand_total) AS total
    FROM transaction_entry
    WHERE type='payment' AND companyid='$companyid'
      AND billdate >= DATE_SUB('$today', INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(billdate,'%Y-%m')
    ORDER BY MIN(billdate) ASC
");
$trendLabels = array_column($salesTrend, 'mon');
$trendData   = array_column($salesTrend, 'total');

$neverVisitedCount = (int) $obj->getvalfield(
    "route_counter rc
     JOIN account a ON a.account_id = rc.account_id
     LEFT JOIN daily_entries de ON de.account_id = rc.account_id
         AND DATE(de.createdate) BETWEEN '$monthStart' AND '$today'
     LEFT JOIN transaction_entry te ON te.account_id = rc.account_id
         AND te.type = 'order'
         AND te.companyid = $companyid
         AND te.billdate BETWEEN '$monthStart' AND '$today'
     LEFT JOIN route_plan rp ON rp.sales_executive_id = a.createdby",
    "COUNT(DISTINCT rc.account_id)",
    "rc.is_active = 1
     AND de.entry_id IS NULL
     AND te.transaction_id IS NULL
     AND NOT (
         DATE(a.createdate) BETWEEN '$monthStart' AND '$today'
         AND rp.sales_executive_id IS NOT NULL
     )"
);

$beatRows = $obj->executequery("
    SELECT 
        rp.batch_no,
        r.route_name,
        COUNT(DISTINCT rc.account_id) AS total_counters,
        COUNT(DISTINCT CASE WHEN te.account_id IS NOT NULL THEN rc.account_id END) AS active_counters,
        COUNT(DISTINCT te.transaction_id) AS total_orders
    FROM route_plan rp

    JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
        AND rc.companyid = rp.companyid
        AND rc.is_active = 1

    JOIN route r
        ON r.batch_no = rp.batch_no
        AND r.companyid = rp.companyid

    LEFT JOIN transaction_entry te
        ON te.account_id = rc.account_id
        AND te.type = 'order'
        AND te.is_approved = 1
        AND te.billdate BETWEEN '$monthStart' AND '$today'
        AND te.companyid = $companyid

    WHERE rp.companyid = $companyid

    GROUP BY rp.batch_no, r.route_name
");

$leastOrders = null;

foreach ($beatRows as $b) {
    $orders = (int) $b['total_orders'];

    if ($leastOrders === null || $orders < $leastOrders) {
        $leastOrders = $orders;
    }
}

$leastActiveBeatCount = 0;

foreach ($beatRows as $b) {
    $total = (int) $b['total_counters'];

    if ($total === 0) {
        continue;
    }

    $orders = (int) $b['total_orders'];

    if ($leastOrders !== null && $orders === $leastOrders) {
        $leastActiveBeatCount++;
    }
}

$classCreditLimits = [
    'A' => 1100000.00, // 11 Lakh
    'B' => 50000.00,
    'C' => 25000.00,
];

$monthlyRows = $obj->executequery("
    SELECT a.account_id, a.class,
        COALESCE(SUM(
            CASE
                WHEN t.type = 'order' AND t.is_approved = 1 AND t.invoice_no != '' THEN t.invoice_amt
                WHEN t.type = 'payment' AND t.pay_status = 1 THEN -(t.grand_total + IFNULL(t.cash_disc,0))
                ELSE 0
            END
        ), 0) AS monthly_balance
    FROM account a
    LEFT JOIN transaction_entry t
        ON t.account_id = a.account_id
        AND t.companyid = $companyid
        AND t.billdate BETWEEN '$monthStart' AND '$today'
    WHERE a.companyid = $companyid
    GROUP BY a.account_id, a.class
");

$crossingPaymentLimitCount = 0;
foreach ($monthlyRows as $row) {
    $class = strtoupper(trim($row['class']));
    $limit = $classPaymentLimits[$class] ?? null;
    if ($limit === null) {
        continue;
    }
    $monthlyBalance = (float) $row['monthly_balance'];
    if ($monthlyBalance >= $limit) {
        $crossingPaymentLimitCount++;
    }
}

$missingDetailsCount = (int) $obj->getvalfield(
    "account",
    "COUNT(*)",
    "type='customer'
     AND (
        mobile_no IS NULL OR mobile_no = ''
        OR o_mobile_no IS NULL OR o_mobile_no = ''
        OR owner_name IS NULL OR owner_name = ''
        OR dob IS NULL OR dob = '0000-00-00'
        OR doa IS NULL OR doa = '0000-00-00'
     )"
);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <?php include('component/dashcss.php'); ?>

</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="dash-wrap">

            <!-- Header -->
            <div class="dash-header">
                <div class="dash-title">Dashboard Overview</div>
                <div class="dash-date"><i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?></div>
            </div>



            <!-- ── Row 1: Stat cards ── -->
            <div class="stat-grid">
                <a href="accounts.php" class="stat-card" style="--c:#1a6ca8">
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value"><?= number_format($totalCustomers) ?></div>
                    <div class="stat-sub">Registered accounts</div>
                    <i class="bi bi-people stat-icon"></i>
                </a>

                <a href="quotation_list.php" class="stat-card" style="--c:#27ae60">
                    <div class="stat-label">Today's Quotation</div>
                    <div class="stat-value"><?= number_format($todayQuo) ?></div>
                    <i class="bi bi-file-earmark-text stat-icon"></i>
                </a>
                <a href="order_list.php" class="stat-card" style="--c:#f39c12">
                    <div class="stat-label">Today's Orders</div>
                    <div class="stat-value"><?= number_format($todayOrders) ?></div>
                    <div class="stat-sub">
                        <?php if ($pendingApprovals > 0): ?>
                            <span class="pill pill-warn"><?= $pendingApprovals ?> pending</span>
                        <?php else: ?>
                            <span class="pill pill-ok">All approved</span>
                        <?php endif; ?>
                    </div>
                    <i class="bi bi-cart3 stat-icon"></i>
                </a>
                <a href="payment_list.php" class="stat-card" style="--c:#8e44ad">
                    <div class="stat-label">Today's Collection</div>
                    <div class="stat-value">₹<?= number_format($todayCollection) ?></div>
                    <div class="stat-sub">Cash + bank</div>
                    <i class="bi bi-cash-stack stat-icon"></i>
                </a>
                <a href="order_list.php?dispatch_pending=1" class="stat-card" style="--c:#e74c3c">
                    <div class="stat-label">Orders Pending for Dispatch</div>
                    <div class="stat-value"><?= number_format($pendingDispatch) ?></div>
                    <div class="stat-sub">Approved, not shipped</div>
                    <i class="bi bi-truck stat-icon"></i>
                </a>
            </div>

            <!-- ── Quick actions ── -->
            <div class="mt-3 panel">
                <div class="panel-head">
                    <span class="ph-title">
                        <i class="bi bi-grid me-1"></i> Quick Actions
                    </span>
                </div>

                <div class="quick-grid">
                    <a href="quotation.php" class="quick-btn">
                        <i class="bi bi-file-earmark-text"></i>
                        New Quotation
                    </a>

                    <a href="order-entry.php" class="quick-btn">
                        <i class="bi bi-cart-check"></i>
                        New Order
                    </a>

                    <a href="payment.php" class="quick-btn">
                        <i class="bi bi-cash-coin"></i>
                        Add Payment
                    </a>

                    <a href="pending_payment.php" class="quick-btn">
                        <i class="bi bi-hourglass-split"></i>
                        Pending Payments
                    </a>

                    <a href="accounts.php" class="quick-btn">
                        <i class="bi bi-person-plus"></i>
                        Add Customer
                    </a>


                    <a href="order_list.php?status=0" class="quick-btn">
                        <i class="bi bi-clipboard-check"></i>
                        Approve Orders
                    </a>

                    <a href="payment_list.php" class="quick-btn">
                        <i class="bi bi-patch-check"></i>
                        Approve Payments
                    </a>
                    <?php if ($usertype == "admin") { ?>
                        <a href="accounts_list.php" class="quick-btn">
                            <i class="bi bi-person-check"></i>
                            Approve Counters
                        </a>

                        <a href="daily_visit_list.php" class="quick-btn">
                            <i class="bi bi-calendar2-check"></i>
                            Daily Visits
                        </a>

                        <a href="monthly_target_approval.php" class="quick-btn">
                            <i class="bi bi-bullseye"></i>
                            Monthly Targets
                        </a>

                        <a href="scheme_list.php" class="quick-btn">
                            <i class="bi bi-gift"></i>
                            Schemes
                        </a>

                        <a href="route.php" class="quick-btn">
                            <i class="bi bi-signpost-split"></i>
                            Routes
                        </a>

                        <a href="user-master.php" class="quick-btn">
                            <i class="bi bi-people"></i>
                            Staff
                        </a>

                    <?php } ?>

                </div>
            </div>
            <?php if ($usertype == "admin") { ?>
                <?php if (!empty($repTargets)): ?>
                    <div class="sec-label"><i class="bi bi-person-check me-1"></i> Sales Rep Target Achievement — <?= date('F Y') ?></div>

                    <div class="rep-target-grid">
                        <?php foreach ($repTargets as $rt):
                            $rpct   = $rt['rep_target'] > 0
                                ? min(100, round(($rt['rep_achieved'] / $rt['rep_target']) * 100))
                                : 0;
                            $isOver = $rpct >= 100;
                            $initials = implode('', array_map(
                                fn($w) => strtoupper($w[0]),
                                array_slice(explode(' ', $rt['fullname']), 0, 2)
                            ));
                        ?>
                            <div class="rep-target-card shadow-sm <?= $isOver ? 'over' : '' ?>">
                                <a href="monthly_target_view.php?createdby=<?= $rt['userid'] ?>&month=<?= $curMonth ?>&year=<?= $curYear ?>" style="text-decoration: none;" target="_blank">
                                    <div class="rtc-name">
                                        <span class="rep-avatar" style="display:inline-flex;width:26px;height:26px;font-size:.65rem;margin-right:6px;vertical-align:middle"><?= $initials ?></span>
                                        <?= htmlspecialchars($rt['fullname']) ?>
                                        <?php if ($isOver): ?>
                                            <span class="pill pill-ok" style="font-size:.58rem;padding:1px 6px;margin-left:4px">✓ Done</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rtc-nums">
                                        <span>Achieved: <strong>₹<?= number_format($rt['rep_achieved']) ?></strong></span>
                                        <span>Target: <strong>₹<?= number_format($rt['rep_target']) ?></strong></span>
                                    </div>
                                    <div class="rtc-bar-wrap">
                                        <div class="rtc-bar-fill <?= $isOver ? 'over' : '' ?>" style="width:<?= $rpct ?>%"></div>
                                    </div>
                                    <div class="rtc-pct" style="color:<?= $isOver ? '#27ae60' : 'var(--blue)' ?>"><?= $rpct ?>%</div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <div class="sec-label"><i class="bi bi-shop me-1"></i> Counter Status</div>
                <div class="counter-status-row">
                    <a href="counter_list.php?status=active" style="text-decoration: none;">
                        <div class="cs-box">
                            <div class="cs-icon active"><i class="bi bi-check-circle-fill"></i></div>
                            <div>
                                <div class="cs-label">Active Counters</div>
                                <div class="cs-val"><?= number_format($activeCounters) ?></div>
                            </div>
                        </div>
                    </a>
                    <a href="counter_list.php?status=inactive" style="text-decoration: none;">
                        <div class="cs-box">
                            <div class="cs-icon inactive"><i class="bi bi-x-circle-fill"></i></div>
                            <div>
                                <div class="cs-label">Inactive Counters</div>
                                <div class="cs-val"><?= number_format($inactiveCounters) ?></div>
                            </div>
                        </div>
                    </a>
                    <a href="counter_list.php" style="text-decoration: none;">
                        <div class="cs-box" style="border-color:#e8eef4">
                            <div class="cs-icon" style="background:#eef4fb;color:#1a6ca8"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="cs-label">Total Counters</div>
                                <div class="cs-val"><?= number_format($totalCounters) ?></div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
            <div class="sec-label mt-3">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Attention Required
            </div>

            <div class="alert-cards-row">
                <!-- Dispatch Pending -->
                <a href="order_list.php?dispatch_pending=1&days=10">
                    <div class="alert-card warn">
                        <div class="alert-icon"><i class="bi bi-truck"></i></div>
                        <div style="flex:1">
                            <div class="alert-label">Orders Pending for Dispatch &gt; 10 Days</div>
                            <div class="alert-val"><?= number_format($longDispCount) ?></div>
                        </div>
                    </div>
                </a>
                <?php if ($usertype == "admin") { ?>
                    <!-- Overdue Payment -->
                    <a href="pending_payment.php?filterAging=31-60">
                        <div class="alert-card danger">
                            <div class="alert-icon"><i class="bi bi-cash-coin"></i></div>
                            <div style="flex:1">
                                <div class="alert-label">Payment Overdue &gt; 45 Days</div>
                                <div class="alert-val">₹<?= number_format($overdueAmount) ?></div>
                            </div>
                        </div>
                    </a>
                <?php } ?>
                <!-- Invoice Pending -->
                <a href="order_list.php?filter=invoice_pending">
                    <div class="alert-card info">
                        <div class="alert-icon"><i class="bi bi-receipt"></i></div>
                        <div style="flex:1">
                            <div class="alert-label">Invoice Pending</div>
                            <div class="alert-val"><?= number_format($invoicePendingCount) ?></div>
                        </div>
                    </div>
                </a>
                <?php if ($usertype == "admin") { ?>
                    <!-- Counter Never Visited -->
                    <a href="counter_list.php?filter=never_visited">
                        <div class="alert-card primary">
                            <div class="alert-icon"><i class="bi bi-geo-alt"></i></div>
                            <div style="flex:1">
                                <div class="alert-label">Counter Not been visited Once</div>
                                <div class="alert-val"><?= number_format($neverVisitedCount) ?></div>
                            </div>
                        </div>
                    </a>

                    <!-- Least Active Beat -->
                    <a href="beat_report.php?mode=least">
                        <div class="alert-card pink">
                            <div class="alert-icon"><i class="bi bi-signpost-split"></i></div>
                            <div style="flex:1">
                                <div class="alert-label">Beat which is least Active</div>
                                <div class="alert-val">
                                    <?= $leastActiveBeatCount; ?>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Counters Crossing the Limit -->
                    <a href="counter_list.php?filter=crossing_limit">
                        <div class="alert-card success">
                            <div class="alert-icon"><i class="bi bi-graph-up-arrow"></i></div>
                            <div style="flex:1">
                                <div class="alert-label">No. Of Counter Crossing the limit</div>
                                <div class="alert-val"><?= $crossingPaymentLimitCount ?></div>
                            </div>
                        </div>
                    </a>

                    <!-- Counters Missing Basic Details -->
                    <a href="counter_list.php?missing_details=1">
                        <div class="alert-card orange">
                            <div class="alert-icon"><i class="bi bi-person-lines-fill"></i></div>
                            <div style="flex:1">
                                <div class="alert-label">Counter Not having Basic Details</div>
                                <div class="alert-val"><?= number_format($missingDetailsCount) ?></div>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>
            <!-- ── Rep performance this month ── -->
            <?php if (!empty($repPerf)): ?>
                <div class="sec-label"><i class="bi bi-person-lines-fill me-1"></i> Sales Rep Performance — <?= date('F Y') ?></div>
                <div class="panel">
                    <div class="rep-row rep-head">
                        <div>Representative</div>
                        <div style="text-align:center">Month Visits</div>
                        <div style="text-align:center">Orders</div>
                        <div style="text-align:center">Collection</div>
                        <div style="text-align:center">Today</div>
                    </div>
                    <?php foreach ($repPerf as $r):
                        $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $r['fullname']), 0, 2)));
                    ?>
                        <div class="rep-row">
                            <div class="rep-name">
                                <div class="rep-avatar"><?= $initials ?></div>
                                <?= htmlspecialchars($r['fullname']) ?>
                            </div>
                            <div class="rep-cell"><?= $r['visits'] ?></div>
                            <div class="rep-cell"><?= $r['orders'] ?></div>
                            <div class="rep-cell"><strong>₹<?= number_format($r['collection']) ?></strong></div>
                            <div class="rep-cell">
                                <?php if ($r['today_visits'] > 0): ?>
                                    <span class="pill pill-ok"><?= $r['today_visits'] ?></span>
                                <?php else: ?>
                                    <span class="pill" style="background:#f0f4f8;color:var(--muted)">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ── Recent orders + Quotations ── -->
            <div class="sec-label"><i class="bi bi-activity me-1"></i> Live Activity</div>
            <div class="g2">
                <div class="panel">
                    <div class="panel-head">
                        <span class="ph-title"><i class="bi bi-receipt me-1"></i> Recent Quotations</span>
                        <a href="quotation_list.php">View all →</a>
                    </div>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Quotation No.</th>
                                <th>Customer</th>
                                <th>With GST</th>
                                <th>Amount</th>
                                <th>Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentQuo)): ?>
                                <tr>
                                    <td colspan="5" class="empty-note">No quotation found</td>
                                </tr>
                                <?php else: foreach ($recentQuo as $r): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($r['billno']) ?></strong>
                                            <div style="font-size:.62rem;color:var(--muted)"><?= $obj->dateformatindia($r['billdate']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($r['account_name']) ?></td>
                                        <td><?= ($r['is_gst'] == 1) ? "Yes" : 'No' ?></td>
                                        <td><strong>₹<?= number_format($r['grand_total']) ?></strong></td>
                                        <td>
                                            <div class="text-center">
                                                <a href="quotation_pdf.php?transaction_id=<?= $r['transaction_id'] ?>"
                                                    class="fs-6" title="Click To Print" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <span class="ph-title"><i class="bi bi-receipt me-1"></i> Recent Orders</span>
                        <a href="order_list.php">View all →</a>
                    </div>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Bill No.</th>
                                <th>Customer</th>
                                <th>Rep</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="5" class="empty-note">No orders found</td>
                                </tr>
                                <?php else: foreach ($recentOrders as $r): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($r['billno']) ?></strong>
                                            <div style="font-size:.62rem;color:var(--muted)"><?= $obj->dateformatindia($r['billdate']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($r['account_name']) ?></td>
                                        <td><?= htmlspecialchars($r['salesrep'] ?? '—') ?></td>
                                        <td><strong>₹<?= number_format($r['grand_total']) ?></strong></td>
                                        <td>
                                            <?php if ($r['is_approved']): ?>
                                                <span class="pill pill-ok">Approved</span>
                                            <?php else: ?>
                                                <span class="pill pill-warn">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <a href="print_order.php?transaction_id=<?= $r['transaction_id'] ?>"
                                                    class="fs-6" title="Click To Print" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Sales chart + KRA ── -->
            <div class="sec-label"><i class="bi bi-graph-up me-1"></i> Analytics & Performance</div>
            <div class="g3">

                <div class="panel" style="grid-column:span 2">
                    <div class="panel-head">
                        <span class="ph-title"><i class="bi bi-bar-chart-line me-1"></i> Monthly Collection Trend (Last 6 Months)</span>
                    </div>
                    <div class="chart-wrap" style="height:210px">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                <?php if ($usertype == "admin") { ?>
                    <div class="panel">
                        <div class="panel-head">
                            <span class="ph-title"><i class="bi bi-trophy me-1"></i> KRA Leaders — <?= $kraMonthLabel ?></span>
                            <a href="salesman_wise_report.php">All →</a>
                        </div>
                        <?php if (empty($topKRA)): ?>
                            <div class="empty-note">No KRA data available</div>
                            <?php else:
                            $rc = ['g', 's', 'b', '', ''];
                            foreach ($topKRA as $i => $k): ?>
                                <div class="kra-row">
                                    <div class="kra-rank <?= $rc[$i] ?? '' ?>"><?= $i + 1 ?></div>
                                    <div class="kra-name"><?= htmlspecialchars($k['fullname']) ?></div>
                                    <div>
                                        <div class="kra-score"><?= $k['total_score'] ?> pts</div>
                                        <div class="kra-pct"><?= $k['achievement_pct'] ?>% achieved</div>
                                    </div>
                                </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                <?php } ?>
            </div>
            <?php if ($usertype == "admin") { ?>
                <?php if (!empty($counterSchemes)): ?>
                    <div class="mt-3 panel">
                        <div class="panel-head">
                            <span class="ph-title"><i class="bi bi-gift me-1"></i> Scheme Progress by Counter</span>
                            <a href="scheme_progress.php">Manage →</a>
                        </div>
                        <p style="font-size:.72rem;color:var(--muted);margin:0 0 10px">
                            <i class="bi bi-info-circle me-1"></i>
                            Counters sorted by progress. <span style="background:#fff3cd;padding:1px 6px;border-radius:4px;font-weight:600">Yellow rows</span> are ≥75% toward next slab — follow up now to push them over.
                        </p>
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Counter Name</th>
                                    <th>Scheme</th>
                                    <th>Achieved Qty</th>
                                    <th>Next Slab</th>
                                    <th>Balance Needed</th>
                                    <th>Progress</th>
                                    <th>Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($counterSchemes as $s):
                                    $isHot   = $s['pct'] >= 90 && $s['status'] !== 'Max Achieved';
                                    $isClose = $s['pct'] >= 75 && $s['status'] !== 'Max Achieved';
                                ?>
                                    <tr class="<?= $isClose ? 'sch-hot' : '' ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($s['account_name']) ?></strong>
                                            <?php if ($isHot): ?>
                                                <span class="close-badge">🔥 Hot</span>
                                            <?php elseif ($isClose): ?>
                                                <span class="close-badge">⚡ Close</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($s['scheme_name']) ?></td>
                                        <td><strong><?= number_format($s['achieved']) ?></strong></td>
                                        <td><?= $s['next_slab'] > 0 ? number_format($s['next_slab']) : '—' ?></td>
                                        <td>
                                            <?php if ($s['balance'] > 0): ?>
                                                <span style="color:#c0392b;font-weight:600"><?= number_format($s['balance']) ?> more</span>
                                            <?php else: ?>
                                                <span style="color:#27ae60;font-weight:600">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="min-width:110px">
                                            <div style="font-size:.68rem;color:var(--muted);margin-bottom:3px"><?= $s['pct'] ?>%</div>
                                            <div class="sch-pct-wrap">
                                                <div class="sch-pct-fill <?= $s['pct'] >= 100 ? 'done' : ($isClose ? 'close' : '') ?>"
                                                    style="width:<?= $s['pct'] ?>%;<?= $isClose && $s['pct'] < 100 ? 'background:#f39c12' : '' ?>"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($s['status'] === 'Max Achieved'): ?>
                                                <span class="pill pill-ok">Max ✓</span>
                                            <?php elseif ($s['reward']): ?>
                                                <span class="pill pill-blue"><?= htmlspecialchars($s['reward']) ?></span>
                                            <?php else: ?>
                                                <span style="color:var(--muted);font-size:.7rem">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php } ?>
        </div><!-- /dash-wrap -->
    </div><!-- /main -->

    <?php include('component/script.php'); ?>
    <script>
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($trendLabels ?: ['No data']) ?>,
                    datasets: [{
                        label: 'Collection (₹)',
                        data: <?= json_encode(array_map('floatval', $trendData ?: [0])) ?>,
                        backgroundColor: 'rgba(26,108,168,.15)',
                        borderColor: '#1a6ca8',
                        borderWidth: 2,
                        borderRadius: 5,
                        hoverBackgroundColor: 'rgba(26,108,168,.28)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: c => '₹ ' + Number(c.raw).toLocaleString('en-IN')
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: '#eef2f6'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                callback: v => v >= 100000 ? '₹' + (v / 100000).toFixed(1) + 'L' : v >= 1000 ? '₹' + (v / 1000).toFixed(0) + 'K' : '₹' + v
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>