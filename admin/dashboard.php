<?php include("../adminsession.php");
$title    = "Dashboard";
$pagename = "dashboard.php";

$today      = date('Y-m-d');
$curMonth   = date('m');
$curYear    = date('Y');
$monthStart = date('Y-m-01');

/* ── TODAY ─────────────────────────────────────────── */
$totalCustomers   = $obj->getvalfield("account", "count(*)", "type='customer' and status1=1");
$todayQuo     = $obj->getvalfield("transaction_entry", "count(*)", "DATE(createdate)='$today' and type='quotation' and companyid='$companyid'");
$todayOrders      = $obj->getvalfield("transaction_entry", "count(*)", "billdate='$today' and type='order' and companyid='$companyid'");
$todayCollection  = $obj->getvalfield("transaction_entry", "sum(grand_total)", "billdate='$today' and type='payment' and companyid='$companyid'") ?: 0;
$pendingApprovals = $obj->getvalfield("transaction_entry", "count(*)", "type='order' and is_approved=0 and companyid='$companyid'");
$pendingDispatch  = $obj->getvalfield("transaction_entry", "count(*)", "type='order' AND is_approved=1 AND dispatch_status=0 AND companyid='$companyid'");

/* ── THIS MONTH ─────────────────────────────────────── */
$monthOrders     = $obj->getvalfield("transaction_entry", "count(*)", "billdate>='$monthStart' and type='order' and companyid='$companyid'");
$monthCollection = $obj->getvalfield("transaction_entry", "sum(grand_total)", "billdate>='$monthStart' and type='payment' and companyid='$companyid'") ?: 0;
$monthVisits     = $obj->getvalfield("daily_entries", "count(*)", "DATE(createdate)>='$monthStart' and companyid='$companyid'");
$activeEmployees = $obj->getvalfield("user", "count(*)", "status=1 and companyid='$companyid'");
$activeSchemes   = $obj->getvalfield("scheme_entry", "count(*)", "todate>='$today' and companyid='$companyid'");

/* ── BRAND-WISE TARGET vs ACHIEVEMENT ───────────────── */
$brandTargets = $obj->executequery("
SELECT
    t.brand_name,
    t.brand_target,
    IFNULL(a.brand_achieved,0) AS brand_achieved

FROM
(
    SELECT
        mtd.brand_id,
        cm.cat_name AS brand_name,
        SUM(mtd.target) AS brand_target

    FROM monthly_target_details mtd

    INNER JOIN category_master cm
        ON cm.cat_id = mtd.brand_id
        AND cm.type='brand'

    WHERE mtd.month='$curMonth'
      AND mtd.year='$curYear'
      AND mtd.companyid='$companyid'

    GROUP BY mtd.brand_id
) t

LEFT JOIN
(
    SELECT
        td.brand_id,
        SUM(td.total_amt) AS brand_achieved

    FROM transaction_details td

    INNER JOIN transaction_entry te
        ON te.transaction_id=td.transaction_id

    WHERE te.type='order'
      AND te.is_approved=1
      AND MONTH(te.billdate)='$curMonth'
      AND YEAR(te.billdate)='$curYear'
      AND te.companyid='$companyid'

    GROUP BY td.brand_id
) a
ON a.brand_id=t.brand_id

ORDER BY t.brand_target DESC
");
/* Overall target from monthly_target_details */
$monthTarget  = array_sum(array_column($brandTargets, 'brand_target')) ?: 0;
$targetPct    = $monthTarget > 0 ? min(100, round(($monthCollection / $monthTarget) * 100)) : 0;

/* ── PER-REP PERFORMANCE (this month) ───────────────── */
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

/* ── RECENT QUOTATION ──────────────────────────────────── */
$recentQuo = $obj->executequery("
    SELECT te.transaction_id,te.billno, te.billdate, te.grand_total, te.is_gst,
           a.account_name
    FROM transaction_entry te
    LEFT JOIN account a ON a.account_id = te.account_id
    WHERE te.type='quotation' AND te.companyid='$companyid'
    ORDER BY te.createdate DESC LIMIT 8
");

/* ── RECENT ORDERS ──────────────────────────────────── */
$recentOrders = $obj->executequery("
    SELECT te.transaction_id,te.billno, te.billdate, te.grand_total, te.is_approved,
           a.account_name, u.fullname AS salesrep
    FROM transaction_entry te
    LEFT JOIN account a ON a.account_id = te.account_id
    LEFT JOIN user    u ON u.userid = te.createdby
    WHERE te.type='order' AND te.companyid='$companyid'
    ORDER BY te.createdate DESC LIMIT 8
");

/* ── TODAY FIELD ACTIVITY ────────────────────────────── */
$todayActivity = $obj->executequery("
    SELECT u.fullname, COUNT(de.entry_id) AS visits,
           MAX(de.createdate) AS last_seen
    FROM daily_entries de
    JOIN user u ON u.userid = de.createdby
    WHERE DATE(de.createdate)='$today' AND de.companyid='$companyid'
    GROUP BY de.createdby ORDER BY visits DESC LIMIT 6
");

/* ── KRA LEADERS (latest available month) ────────────── */
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
$kraMonthLabel = !empty($topKRA) ? date('M Y', mktime(0, 0, 0, (int)$topKRA[0]['month'], 1, (int)$topKRA[0]['year'])) : date('M Y');

/* ── SALES TREND (last 6 months) ─────────────────────── */
$salesTrend  = $obj->executequery("
    SELECT DATE_FORMAT(billdate,'%b %y') AS mon, SUM(grand_total) AS total
    FROM transaction_entry
    WHERE type='payment' AND companyid='$companyid'
      AND billdate >= DATE_SUB('$today', INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(billdate,'%Y-%m')
    ORDER BY MIN(billdate) ASC
");
$trendLabels = array_column($salesTrend, 'mon');
$trendData   = array_column($salesTrend, 'total');

/* ── SCHEME PROGRESS ─────────────────────────────────── */
$schemeSlabs = $obj->executequery("
    SELECT se.scheme_id, se.scheme_name, sd.qty AS slab_qty, sd.output,
           u.userid, u.fullname, SUM(oi.qty) AS achieved
    FROM scheme_entry se
    JOIN scheme_details sd ON sd.scheme_id = se.scheme_id
    JOIN transaction_entry te
         ON te.billdate BETWEEN se.from_date AND se.todate
        AND te.type='order' AND te.companyid='$companyid'
    JOIN transaction_details oi
         ON oi.transaction_id = te.transaction_id
        AND oi.product_id = sd.product_id
    JOIN user u ON u.userid = te.createdby
    WHERE se.todate >= '$today' AND se.companyid='$companyid'
    GROUP BY se.scheme_id, sd.qty, u.userid ORDER BY sd.qty ASC
");
$final = [];
foreach ($schemeSlabs as $row) {
    $key = $row['scheme_id'] . '_' . $row['userid'];
    if (!isset($final[$key])) {
        $final[$key] = [
            'scheme_name' => $row['scheme_name'],
            'fullname' => $row['fullname'],
            'achieved' => $row['achieved'],
            'current_slab' => 0,
            'next_slab' => 0,
            'reward' => ''
        ];
    }
    if ($row['achieved'] >= $row['slab_qty']) {
        $final[$key]['current_slab'] = $row['slab_qty'];
        $final[$key]['reward']       = $row['output'];
    }
    if ($row['achieved'] < $row['slab_qty'] && $final[$key]['next_slab'] == 0)
        $final[$key]['next_slab'] = $row['slab_qty'];
}
foreach ($final as &$f) {
    if ($f['next_slab'] > 0) {
        $f['pct']     = min(99, round(($f['achieved'] / $f['next_slab']) * 100));
        $f['balance'] = $f['next_slab'] - $f['achieved'];
        $f['status']  = 'Running';
    } else {
        $f['pct'] = 100;
        $f['balance'] = 0;
        $f['status'] = 'Max Achieved';
    }
}
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
            <?php if ($usertype == "admin") { ?>
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
                    <a href="order_list.php?filter=dispatch" class="stat-card" style="--c:#e74c3c">
                        <div class="stat-label">Pending Dispatch</div>
                        <div class="stat-value"><?= number_format($pendingDispatch) ?></div>
                        <div class="stat-sub">Approved, not shipped</div>
                        <i class="bi bi-truck stat-icon"></i>
                    </a>
                </div>

                <!-- ── Overall target strip ── -->
                <!-- <div class="target-strip">
                    <div class="ts-head">
                        <div class="ts-title"><i class="bi bi-bullseye me-2" style="color:var(--blue)"></i>Monthly Target vs Collection — <?= date('F Y') ?></div>
                        <div class="ts-pct"><?= $targetPct ?>%</div>
                    </div>
                    <div class="ts-bar-wrap">
                        <div class="ts-bar-fill" style="width:<?= $targetPct ?>%"></div>
                    </div>
                    <div class="ts-meta">
                        <span>Collected: <strong>₹<?= number_format($monthCollection) ?></strong></span>
                        <span>Target: <strong>₹<?= $monthTarget > 0 ? number_format($monthTarget) : 'Not set' ?></strong></span>
                        <span>Month Orders: <strong><?= number_format($monthOrders) ?></strong></span>
                        <span>Month Visits: <strong><?= number_format($monthVisits) ?></strong></span>
                        <span>Active Staff: <strong><?= $activeEmployees ?></strong></span>
                        <span>Active Schemes: <strong><?= $activeSchemes ?></strong></span>
                    </div>
                </div> -->

                <!-- ── Quick actions ── -->
                <div class="mt-3 panel">
                    <div class="panel-head"><span class="ph-title"><i class="bi bi-grid me-1"></i> Quick Actions</span></div>
                    <div class="quick-grid">
                        <a href="accounts.php" class="quick-btn"><i class="bi bi-person-plus"></i>Add Customer</a>
                        <!-- <a href="order_add.php" class="quick-btn"><i class="bi bi-cart-plus"></i>New Order</a>
                        <a href="payment_list.php" class="quick-btn"><i class="bi bi-cash"></i>Payments</a> -->
                        <a href="order_list.php?status=0" class="quick-btn"><i class="bi bi-hourglass-split"></i>Approve Orders</a>
                        <a href="scheme_list.php" class="quick-btn"><i class="bi bi-tag"></i>Schemes</a>
                        <a href="monthly_target_approval.php" class="quick-btn"><i class="bi bi-bullseye"></i>View Targets</a>
                        <a href="route.php" class="quick-btn"><i class="bi bi-map"></i>Routes</a>
                        <a href="user-master.php" class="quick-btn"><i class="bi bi-people"></i>Staff</a>
                    </div>
                </div>

                <!-- ── Brand-wise targets ── -->
                <?php if (!empty($brandTargets)): ?>
                    <div class="sec-label"><i class="bi bi-bar-chart-steps me-1"></i> Brand-wise Target vs Achievement — <?= date('F Y') ?></div>
                    <div class="panel">
                        <div class="brand-grid">
                            <?php foreach ($brandTargets as $b):
                                $bpct     = $b['brand_target'] > 0 ? min(100, round(($b['brand_achieved'] / $b['brand_target']) * 100)) : 0;
                                $isOver   = $bpct >= 100;
                            ?>
                                <div class="brand-card">
                                    <div class="brand-name" title="<?= htmlspecialchars($b['brand_name']) ?>"><?= htmlspecialchars($b['brand_name']) ?></div>
                                    <div class="brand-nums">
                                        <span>Achieved: <strong>₹<?= number_format($b['brand_achieved']) ?></strong></span>
                                        <span>Target: <strong>₹<?= number_format($b['brand_target']) ?></strong></span>
                                    </div>
                                    <div class="bpbar-wrap">
                                        <div class="bpbar-fill <?= $isOver ? 'over' : '' ?>" style="width:<?= $bpct ?>%"></div>
                                    </div>
                                    <div class="brand-pct <?= $isOver ? '' : '' ?>" style="color:<?= $isOver ? '#27ae60' : 'var(--blue)' ?>"><?= $bpct ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

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

                <!-- ── Recent orders + Today's activity ── -->
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
                    <!-- <div class="panel">
                        <div class="panel-head">
                            <span class="ph-title"><i class="bi bi-map me-1"></i> Today's Field Activity</span>
                            <a href="daily_visit_list.php">View all →</a>
                        </div>
                        <?php if (empty($todayActivity)): ?>
                            <div class="empty-note">No field activity today</div>
                            <?php else: foreach ($todayActivity as $v):
                                $ini = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $v['fullname']), 0, 2)));
                                $lastTime = !empty($v['last_seen']) ? date('h:i A', strtotime($v['last_seen'])) : '';
                            ?>
                                <div class="visit-row">
                                    <div class="va-avatar"><?= $ini ?></div>
                                    <div style="flex:1">
                                        <div class="va-name"><?= htmlspecialchars($v['fullname']) ?></div>
                                        <?php if ($lastTime): ?><div class="va-meta">Last at <?= $lastTime ?></div><?php endif; ?>
                                    </div>
                                    <div class="va-cnt"><?= $v['visits'] ?> visits</div>
                                </div>
                        <?php endforeach;
                        endif; ?>
                    </div> -->

                </div>

                <!-- ── Sales chart + KRA + Scheme ── -->
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

                    <div class="panel">
                        <div class="panel-head">
                            <span class="ph-title"><i class="bi bi-trophy me-1"></i> KRA Leaders — <?= $kraMonthLabel ?></span>
                            <a href="monthly_kra_list.php">All →</a>
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

                </div>

                <!-- ── Scheme progress ── -->
                <?php if (!empty($final)): ?>
                    <div class="mt-3 panel">
                        <div class="panel-head">
                            <span class="ph-title"><i class="bi bi-gift me-1"></i> Scheme Progress</span>
                            <a href="scheme_list.php">Manage →</a>
                        </div>
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Scheme</th>
                                    <th>Employee</th>
                                    <th>Achieved</th>
                                    <th>Next Slab</th>
                                    <th>Progress</th>
                                    <th>Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($final as $s): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['scheme_name']) ?></td>
                                        <td><?= htmlspecialchars($s['fullname']) ?></td>
                                        <td><strong><?= number_format($s['achieved']) ?></strong></td>
                                        <td><?= $s['next_slab'] > 0 ? number_format($s['next_slab']) . ' <span style="font-size:.62rem;color:var(--muted)">(need ' . number_format($s['balance']) . ' more)</span>' : '—' ?></td>
                                        <td style="min-width:90px">
                                            <div style="font-size:.68rem;color:var(--muted);margin-bottom:3px"><?= $s['pct'] ?>%</div>
                                            <div class="sch-pct-wrap">
                                                <div class="sch-pct-fill <?= $s['pct'] >= 100 ? 'done' : '' ?>" style="width:<?= $s['pct'] ?>%"></div>
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


            <?php } else { ?>

                <div class="stat-grid">
                    <a href="javascript:void(0)" class="stat-card" style="--c:#1a6ca8">
                        <div class="stat-label">Total Customers</div>
                        <div class="stat-value"><?= number_format($totalCustomers) ?></div>
                        <div class="stat-sub">Registered accounts</div>
                        <i class="bi bi-people stat-icon"></i>
                    </a>
                    <a href="javascript:void(0)" class="stat-card" style="--c:#27ae60">
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
                    <a href="javascript:void(0)" class="stat-card" style="--c:#8e44ad">
                        <div class="stat-label">Today's Collection</div>
                        <div class="stat-value">₹<?= number_format($todayCollection) ?></div>
                        <div class="stat-sub">Cash + bank</div>
                        <i class="bi bi-cash-stack stat-icon"></i>
                    </a>
                    <a href="order_list.php?filter=dispatch" class="stat-card" style="--c:#e74c3c">
                        <div class="stat-label">Pending Dispatch</div>
                        <div class="stat-value"><?= number_format($pendingDispatch) ?></div>
                        <div class="stat-sub">Approved, not shipped</div>
                        <i class="bi bi-truck stat-icon"></i>
                    </a>
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
                                            <div style="font-size:.62rem;color:var(--muted)"><?= $r['billdate'] ?></div>
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
                                            <div>
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

        function open_pdf() {
            window.location();
        }
    </script>
</body>

</html>