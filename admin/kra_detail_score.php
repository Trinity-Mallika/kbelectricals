<?php include("../adminsession.php");
$title = "KRA SCORE DETAIL";
$pagename = "kra_detail_score.php";
$companyid = $_SESSION['companyid'] ?? 0;

$emp_id = (int)($_GET['emp_id'] ?? 0);
$month  = (int)($_GET['month'] ?? date('n'));
$year   = (int)($_GET['year']  ?? date('Y'));

$monthName = date('F', mktime(0, 0, 0, $month, 1));
$qs = "emp_id=$emp_id&month=$month&year=$year";

$emp_name = $obj->getvalfield("user", "fullname", "userid='$emp_id' AND companyid='$companyid'") ?: 'Unknown';

$kra_rows = $obj->executequery("
    SELECT * FROM monthly_kra
    WHERE emp_id='$emp_id' AND month='$month' AND year='$year' AND companyid='$companyid'
    LIMIT 1
");
$kra = !empty($kra_rows) ? $kra_rows[0] : null;

/* Behaviour score breakdown by parameter */
$behaviour_rows = $obj->executequery("
    SELECT kb.name, kbs.score
    FROM kra_behaviour kb
    LEFT JOIN kra_behaviour_score kbs
           ON kbs.behaviour_id = kb.kra_behaviour_id
          AND kbs.emp_id='$emp_id'
          AND kbs.month='$month'
          AND kbs.year='$year'
    WHERE kb.companyid='$companyid'
");

$components = [];
if ($kra) {
    $components = [
        ['label' => 'Avg Visit / Day / Beat', 'value' => $kra['visit_value'], 'points' => $kra['visit_points'], 'weight' => 20],
        ['label' => 'Beat Wise Productivity (%)', 'value' => $kra['productivity_value'], 'points' => $kra['productivity_points'], 'weight' => 20],
        ['label' => 'Product Mix', 'value' => $kra['product_mix_value'], 'points' => $kra['product_mix_points'], 'weight' => 20],
        ['label' => 'Overall Business (Lakh)', 'value' => $kra['business_value'], 'points' => $kra['business_points'], 'weight' => 30],
        ['label' => 'Behaviour Score', 'value' => $kra['behaviour_value'], 'points' => $kra['behaviour_points'], 'weight' => 10],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <?php include('det_styles.php'); ?>
    <style>
        .ach-badge-lg {
            display: inline-block; padding: 8px 20px; border-radius: 24px;
            font-weight: 700; font-size: 1.4rem;
        }
        .ach-high { background: #eafaf1; color: #27ae60; }
        .ach-mid  { background: #fff8e1; color: #f39c12; }
        .ach-low  { background: #fff5f5; color: #e74c3c; }
    </style>
</head>

<body class="bg-light">

    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="det-wrapper">
            <a class="det-back" href="salesman_wise_report.php?<?= $qs ?>">← Back to Salesman Report</a>

            <div class="det-header">
                <div>
                    <h1>KRA Score Detail — <?= htmlspecialchars($emp_name) ?></h1>
                    <div class="det-sub"><?= $monthName ?> <?= $year ?></div>
                </div>
                <?php if ($kra): ?>
                    <?php $achClass = $kra['achievement_pct'] >= 80 ? 'ach-high' : ($kra['achievement_pct'] >= 50 ? 'ach-mid' : 'ach-low'); ?>
                    <span class="ach-badge-lg <?= $achClass ?>"><?= round($kra['achievement_pct'], 1) ?>%</span>
                <?php endif; ?>
            </div>

            <?php if (!$kra): ?>
                <div class="det-section"><div class="det-empty">KRA has not been processed for this employee/month yet.</div></div>
            <?php else: ?>

                <div class="det-section">
                    <div class="det-section-head"><span>KRA Component Breakdown</span></div>
                    <table class="det-table">
                        <thead>
                            <tr>
                                <th>KRA Parameter</th>
                                <th class="num-cell">Achieved Value</th>
                                <th class="num-cell">Points</th>
                                <th class="num-cell">Weight</th>
                                <th class="num-cell">Weighted Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($components as $c): ?>
                                <tr>
                                    <td><?= $c['label'] ?></td>
                                    <td class="num-cell"><?= round($c['value'], 2) ?></td>
                                    <td class="num-cell"><?= round($c['points'], 2) ?></td>
                                    <td class="num-cell">×<?= $c['weight'] ?></td>
                                    <td class="num-cell"><?= round($c['points'] * $c['weight'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight:700;background:#f8fafc;">
                                <td colspan="4">Total Score (out of 220)</td>
                                <td class="num-cell"><?= round($kra['total_score'], 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="det-section">
                    <div class="det-section-head"><span>Behaviour Score Breakdown (Max 4)</span></div>
                    <table class="det-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th class="num-cell">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($behaviour_rows)): ?>
                                <?php foreach ($behaviour_rows as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['name']) ?></td>
                                        <td class="num-cell"><?= $b['score'] !== null ? round($b['score'], 2) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="det-empty">No behaviour scores recorded for this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <?php include('component/script.php'); ?>
</body>

</html>