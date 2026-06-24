<?php include("../adminsession.php");
$title = "VISIT DETAIL";
$pagename = "kra_detail_visits.php";
$companyid = $_SESSION['companyid'] ?? 0;

$emp_id = (int)($_GET['emp_id'] ?? 0);
$month  = (int)($_GET['month'] ?? date('n'));
$year   = (int)($_GET['year']  ?? date('Y'));

$monthName = date('F', mktime(0, 0, 0, $month, 1));
$start = date("$year-$month-01");
$end   = date("Y-m-t", strtotime($start));
$qs = "emp_id=$emp_id&month=$month&year=$year";

$emp_name = $obj->getvalfield("user", "fullname", "userid='$emp_id' AND companyid='$companyid'") ?: 'Unknown';

$days = $obj->executequery("
    SELECT date, visit_count, total_counters, active_counters
    FROM daily_productivity
    WHERE emp_id='$emp_id'
    AND date BETWEEN '$start' AND '$end'
    AND companyid='$companyid'
    ORDER BY date ASC
");

$visit_total = 0;
$visit_days  = 0;
foreach ($days as $d) {
    $visit_total += $d['visit_count'];
    if ($d['visit_count'] > 0) $visit_days++;
}
$visit_avg = $visit_days > 0 ? round($visit_total / $visit_days, 1) : 0;
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
                    <h1>Visit Detail — <?= htmlspecialchars($emp_name) ?></h1>
                    <div class="det-sub"><?= $monthName ?> <?= $year ?></div>
                </div>
                <div class="det-summary">
                    <span class="pill"><?= $visit_total ?> total visits</span>
                    <span class="pill"><?= $visit_avg ?> avg/active day</span>
                    <span class="pill"><?= $visit_days ?> active days</span>
                </div>
            </div>

            <div class="det-section">
                <div class="det-section-head"><span>Day-by-Day Visit Log</span></div>
                <table class="det-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th class="num-cell">Visits</th>
                            <th class="num-cell">Total Counters</th>
                            <th class="num-cell">Active Counters</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($days)): ?>
                            <?php foreach ($days as $d): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($d['date'])) ?></td>
                                    <td><?= date('D', strtotime($d['date'])) ?></td>
                                    <td ><?= $d['visit_count'] ?></td>
                                    <td ><?= $d['total_counters'] ?? '-' ?></td>
                                    <td ><?= $d['active_counters'] ?? '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="det-empty">No visit records found for this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="note-text" style="font-size:0.72rem;color:#b0bec5;">
                Note: visits are logged per day, not per beat .
            </div>
        </div>
    </div>

    <?php include('component/script.php'); ?>
</body>

</html>