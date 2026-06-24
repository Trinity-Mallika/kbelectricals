<?php include("../adminsession.php");
$title = "BUSINESS DETAIL";
$pagename = "kra_detail_business.php";
$companyid = $_SESSION['companyid'] ?? 0;

$emp_id = (int)($_GET['emp_id'] ?? 0);
$month  = (int)($_GET['month'] ?? date('n'));
$year   = (int)($_GET['year']  ?? date('Y'));

$monthName = date('F', mktime(0, 0, 0, $month, 1));
$start = date("$year-$month-01");
$end   = date("Y-m-t", strtotime($start));
$qs = "emp_id=$emp_id&month=$month&year=$year";

$emp_name = $obj->getvalfield("user", "fullname", "userid='$emp_id' AND companyid='$companyid'") ?: 'Unknown';

/* Orders for this salesman in this period, with beat name + account name */
$orders = $obj->executequery("
    SELECT t.transaction_id, t.billno, t.billdate, t.grand_total, t.is_approved,
           a.account_name, a.class,
           r.route_name
    FROM transaction_entry t
    JOIN account a ON a.account_id = t.account_id
    LEFT JOIN route_counter rc ON rc.account_id = t.account_id
    LEFT JOIN route r ON r.batch_no = rc.batch_no
    WHERE t.createdby='$emp_id'
    AND t.type='order'
    AND t.billdate BETWEEN '$start' AND '$end'
    AND t.is_approved=1
    AND t.companyid='$companyid'
    ORDER BY t.billdate ASC
");

$total_business = 0;
foreach ($orders as $o) $total_business += $o['grand_total'];
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
                    <h1>Business Detail — <?= htmlspecialchars($emp_name) ?></h1>
                    <div class="det-sub"><?= $monthName ?> <?= $year ?></div>
                </div>
                <div class="det-summary">
                    <span class="pill">₹<?= number_format($total_business, 0) ?> total business</span>
                    <span class="pill"><?= count($orders) ?> orders</span>
                </div>
            </div>

            <div class="det-section">
                <div class="det-section-head"><span>Order-Wise Business</span></div>
                <table class="det-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Bill No</th>
                            <th>Account</th>
                            <th>Class</th>
                            <th>Beat</th>
                            <th class="num-cell">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($o['billdate'])) ?></td>
                                    <td><?= htmlspecialchars($o['billno']) ?></td>
                                    <td><?= htmlspecialchars($o['account_name']) ?></td>
                                    <td><span class="badge-class"><?= htmlspecialchars($o['class']) ?></span></td>
                                    <td><?= htmlspecialchars($o['route_name'] ?? '—') ?></td>
                                    <td class="num-cell">₹<?= number_format($o['grand_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="det-empty">No approved orders found for this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include('component/script.php'); ?>
</body>

</html>