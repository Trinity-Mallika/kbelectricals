<?php
include("appsession.php");

$title = "Pending Payment List";
$account_id = (int)($_GET['account_id'] ?? 0);

$account = $obj->executequery("
    SELECT
        account_name,
        owner_name,
        o_mobile_no,
        address
    FROM account
    WHERE account_id='$account_id'
      AND companyid='$companyid'
    LIMIT 1
");
$account = $account[0] ?? [];

// pending bills
$res = $obj->executequery("
    SELECT
        o.transaction_id,
        o.billno,
        o.billdate,
        o.grand_total AS order_amount,

        COALESCE(SUM(p.grand_total),0) AS paid_amount,

        (o.grand_total - COALESCE(SUM(p.grand_total),0)) AS pending_amount,

        DATEDIFF(CURDATE(), o.billdate) AS pending_days

    FROM transaction_entry o

    LEFT JOIN transaction_entry p
        ON p.ref_bill_id = o.transaction_id
        AND p.type='payment'
        AND p.companyid='$companyid'

    WHERE o.account_id='$account_id'
      AND o.type='order'
      AND o.is_approved='1'
      AND o.companyid='$companyid'

    GROUP BY
        o.transaction_id,
        o.billno,
        o.billdate,
        o.grand_total

    HAVING pending_amount > 0

    ORDER BY o.billdate ASC
");

$total_pending = array_sum(array_column($res, 'pending_amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <?php include("inc/css-file.php"); ?>
</head>
<body class="dashboard">

<section class="top-sec">
    <?php include("inc/header.php"); ?>

    <div class="container">

        <!-- Header -->
        <div class="card border-0 shadow-lg mb-3 p-3 bg-light-primary">
            <div class="d-flex justify-content-between align-items-start">

                <div>
                    <h5 class="mb-2 text-blue">
                        <i class="bi bi-shop"></i>
                        <?= htmlspecialchars($account['account_name'] ?? '-') ?>
                    </h5>

                    <p class="mb-1 text-secondary">
                        <i class="bi bi-person"></i>
                        <?= htmlspecialchars($account['owner_name'] ?? '-') ?>
                    </p>

                    <p class="mb-1 text-secondary">
                        <i class="bi bi-telephone"></i>
                        <?= htmlspecialchars($account['o_mobile_no'] ?? '-') ?>
                    </p>

                    <p class="mb-0 text-secondary">
                        <i class="bi bi-geo-alt"></i>
                        <?= htmlspecialchars($account['address'] ?? '-') ?>
                    </p>
                </div>

                <div class="text-end">
                    <small class="text-secondary">Total Pending</small>
                    <h4 class="mb-0 text-danger">
                        ₹<?= number_format($total_pending, 2) ?>
                    </h4>
                </div>

            </div>
        </div>

        <!-- Bills -->
        <?php if (!empty($res)) { ?>

            <?php foreach ($res as $row):

                $status = "Unpaid";
                $statusClass = "danger";

                if ($row['paid_amount'] > 0 && $row['pending_amount'] > 0) {
                    $status = "Partially Paid";
                    $statusClass = "warning text-black";
                }

                if ($row['pending_amount'] <= ($row['order_amount'] * 0.10)) {
                    $status = "Nearly Cleared";
                    $statusClass = "success";
                }

                $ageBadge =
                    ($row['pending_days'] <= 15) ? 'success' :
                    (($row['pending_days'] <= 30) ? 'warning' : 'danger');

            ?>

            <div class="card border-0 shadow-lg mb-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-blue">
                        Bill No: <?= htmlspecialchars($row['billno']) ?>
                    </strong>

                    <span class="badge bg-<?= $ageBadge ?>">
                        <?= $row['pending_days'] ?> Days
                    </span>
                </div>

                <small class="text-secondary d-block mb-2">
                    <i class="bi bi-calendar3"></i>
                    <?= date("d M Y", strtotime($row['billdate'])) ?>
                </small>

                <div class="d-flex justify-content-between mb-1">
                    <small>Bill Amount</small>
                    <strong>₹<?= number_format($row['order_amount'], 2) ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-1">
                    <small>Paid</small>
                    <strong class="text-success">
                        ₹<?= number_format($row['paid_amount'], 2) ?>
                    </strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <small>Pending</small>
                    <strong class="text-danger">
                        ₹<?= number_format($row['pending_amount'], 2) ?>
                    </strong>
                </div>

                <span class="badge bg-<?= $statusClass ?>">
                    <?= $status ?>
                </span>
            </div>

            <?php endforeach; ?>

        <?php } else { ?>

            <div class="card border-0 shadow-lg text-center p-4">
                <i class="bi bi-check-circle fs-1 text-success"></i>
                <h6 class="mt-2 mb-1">No Pending Bills</h6>
                <small class="text-muted">This account has no outstanding invoices.</small>
            </div>

        <?php } ?>

    </div>
</section>

<?php include("inc/js-file.php"); ?>
</body>
</html>