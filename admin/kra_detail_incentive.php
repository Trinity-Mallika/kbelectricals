<?php include("../adminsession.php");
$title = "INCENTIVE DETAIL";
$pagename = "kra_detail_incentive.php";
$companyid = $_SESSION['companyid'] ?? 0;

$emp_id = (int)($_GET['emp_id'] ?? 0);
$month  = (int)($_GET['month'] ?? date('n'));
$year   = (int)($_GET['year']  ?? date('Y'));

$monthName = date('F', mktime(0, 0, 0, $month, 1));
$qs = "emp_id=$emp_id&month=$month&year=$year";

$emp_name = $obj->getvalfield("user", "fullname", "userid='$emp_id' AND companyid='$companyid'") ?: 'Unknown';

$inc_rows = $obj->executequery("
    SELECT * FROM monthly_incentive
    WHERE sales_executive_id='$emp_id' AND month_name='$month' AND year='$year' AND companyid='$companyid'
    LIMIT 1
");
$inc = !empty($inc_rows) ? $inc_rows[0] : null;

$components = [];
if ($inc) {
    $components = [
        ['label' => 'Visit Incentive', 'metric' => round($inc['avg_visits'], 2) . ' avg visits', 'amount' => $inc['visit_incentive']],
        ['label' => 'Sales Incentive', 'metric' => '₹' . number_format($inc['avg_sales_amount'], 0), 'amount' => $inc['sales_incentive']],
        ['label' => 'Product Mix Incentive', 'metric' => $inc['product_mix_count'] . ' products avg', 'amount' => $inc['product_mix_incentive']],
        ['label' => 'Collection Incentive', 'metric' => round($inc['avg_collection_days'], 1) . ' avg days', 'amount' => $inc['collection_incentive']],
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
                    <h1>Incentive Detail — <?= htmlspecialchars($emp_name) ?></h1>
                    <div class="det-sub"><?= $monthName ?> <?= $year ?></div>
                </div>
                <?php if ($inc): ?>
                    <span class="pill" style="font-size:1rem;">₹<?= number_format($inc['total_incentive'], 0) ?> total</span>
                <?php endif; ?>
            </div>

            <?php if (!$inc): ?>
                <div class="det-section"><div class="det-empty">Incentive has not been processed for this employee/month yet.</div></div>
            <?php else: ?>

                <div class="det-section">
                    <div class="det-section-head"><span>Incentive Component Breakdown</span></div>
                    <table class="det-table">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th>Basis</th>
                                <th class="num-cell">Incentive Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($components as $c): ?>
                                <tr>
                                    <td><?= $c['label'] ?></td>
                                    <td><?= $c['metric'] ?></td>
                                    <td class="num-cell">₹<?= number_format($c['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight:700;background:#f8fafc;">
                                <td colspan="2">Total Incentive</td>
                                <td class="num-cell">₹<?= number_format($inc['total_incentive'], 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($inc['has_overdue_party']): ?>
                    <div class="det-section">
                        <div class="det-section-head" style="background:#e74c3c;"><span>⚠ Overdue Party Flag</span></div>
                        <div style="padding:14px;color:#e74c3c;font-size:0.85rem;">
                            This salesman has at least one overdue party this month, which may affect incentive eligibility.
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <?php include('component/script.php'); ?>
</body>

</html>