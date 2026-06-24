<?php include("../adminsession.php");
$title = "COUNTER DETAIL";
$pagename = "kra_detail_counters.php";
$companyid = $_SESSION['companyid'] ?? 0;

$emp_id = (int)($_GET['emp_id'] ?? 0);
$month  = (int)($_GET['month'] ?? date('n'));
$year   = (int)($_GET['year']  ?? date('Y'));

$monthName = date('F', mktime(0, 0, 0, $month, 1));
$start = date("$year-$month-01");
$end   = date("Y-m-t", strtotime($start));
$qs = "emp_id=$emp_id&month=$month&year=$year";

$emp_name = $obj->getvalfield("user", "fullname", "userid='$emp_id' AND companyid='$companyid'") ?: 'Unknown';

$beats = $obj->executequery("
    SELECT DISTINCT r.batch_no, r.route_name
    FROM route_plan rp
    JOIN route r ON r.batch_no = rp.batch_no
    WHERE rp.sales_executive_id='$emp_id'
    AND rp.companyid='$companyid'
    ORDER BY r.batch_no ASC
");

/* Group counter data per beat/route, each with its own subtotal */
$beat_groups = [];
$total_active = 0;
$total_inactive = 0;

foreach ($beats as $beat) {
    $batch_no = $beat['batch_no'];

    $counters = $obj->executequery("
        SELECT a.account_id, a.account_name, a.class,
               COALESCE(SUM(t.grand_total), 0) as sales
        FROM route_counter rc
        JOIN account a ON a.account_id = rc.account_id
        LEFT JOIN transaction_entry t
               ON t.account_id = a.account_id
              AND t.type='order'
              AND t.createdby='$emp_id'
              AND t.billdate BETWEEN '$start' AND '$end'
              AND t.is_approved=1
              AND t.companyid='$companyid'
        WHERE rc.batch_no='$batch_no'
        GROUP BY a.account_id, a.account_name, a.class
        ORDER BY a.account_name ASC
    ");

    $rows = [];
    $beat_active = 0;
    $beat_inactive = 0;

    foreach ($counters as $c) {
        $min_sales = $obj->getvalfield(
            "kra_productivity_config",
            "min_sales",
            "class='{$c['class']}' AND companyid='$companyid'"
        );

        $is_active = ($min_sales !== null && $c['sales'] >= $min_sales);
        if ($is_active) {
            $beat_active++;
            $total_active++;
        } else {
            $beat_inactive++;
            $total_inactive++;
        }

        $rows[] = [
            'account_name' => $c['account_name'],
            'class' => $c['class'],
            'sales' => $c['sales'],
            'min_sales' => $min_sales,
            'is_active' => $is_active,
        ];
    }

    $beat_groups[] = [
        'route_name' => $beat['route_name'],
        'batch_no' => $batch_no,
        'rows' => $rows,
        'active' => $beat_active,
        'inactive' => $beat_inactive,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <?php include('det_styles.php'); ?>
</head>

<body class="bg-light">

    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="det-wrapper">
            <a class="det-back" href="salesman_wise_report.php?<?= $qs ?>">← Back to Salesman Report</a>

            <div class="det-header">
                <div>
                    <h1>Active / Inactive Counter Detail — <?= htmlspecialchars($emp_name) ?></h1>
                    <div class="det-sub"><?= $monthName ?> <?= $year ?></div>
                </div>
                <div class="det-summary">
                    <span class="pill" style="background:#eafaf1;border-color:#bce8c9;color:#27ae60;"><?= $total_active ?> active</span>
                    <span class="pill" style="background:#fff5f5;border-color:#fbd5d5;color:#e74c3c;"><?= $total_inactive ?> inactive</span>
                </div>
            </div>

            <?php if (!empty($beat_groups)): ?>
                <?php foreach ($beat_groups as $g): ?>
                    <div class="det-section">
                        <div class="det-section-head" style="display:flex;justify-content:space-between;align-items:center;">
                            <span><?= htmlspecialchars($g['route_name']) ?></span>
                            <span style="color:#fff;font-size:0.75rem;font-weight:600;">
                                <?= $g['active'] ?> active &nbsp;/&nbsp; <?= $g['inactive'] ?> inactive
                            </span>
                        </div>
                        <table class="det-table">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Class</th>
                                    <th class="num-cell">Sales (Month)</th>
                                    <th class="num-cell">Min Required</th>
                                    <th class="center-cell">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($g['rows'])): ?>
                                    <?php foreach ($g['rows'] as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['account_name']) ?></td>
                                            <td><span class="badge-class"><?= htmlspecialchars($r['class']) ?></span></td>
                                            <td class="num-cell">₹<?= number_format($r['sales'], 2) ?></td>
                                            <td class="num-cell"><?= $r['min_sales'] !== null ? '₹' . number_format($r['min_sales'], 2) : '—' ?></td>
                                            <td class="center-cell">
                                                <?php if ($r['is_active']): ?>
                                                    <span class="status-active">Active</span>
                                                <?php else: ?>
                                                    <span class="status-inactive">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="det-empty">No counters in this beat</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="det-section"><div class="det-empty">No beats assigned to this salesman</div></div>
            <?php endif; ?>
        </div>
    </div>

    <?php include('component/script.php'); ?>
</body>

</html>