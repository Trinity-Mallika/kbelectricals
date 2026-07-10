<?php
include("appsession.php");

$title = "Pending Payment List";
$account_id = (int)($_GET['account_id'] ?? 0);

$account = $obj->select_record("account", ["account_id" => $account_id]);

$opening_balance = (float)($account['opening_balance'] ?? 0);

// Pending Bills
$res = $obj->executequery("
    SELECT
        o.transaction_id,
        o.billno,
        o.invoice_no,
        o.billdate,
        o.invoice_amt AS order_amount,

        COALESCE(pay.paid_amount,0) AS paid_amount,

        GREATEST(
            o.invoice_amt - COALESCE(pay.paid_amount,0),
            0
        ) AS pending_amount,

        DATEDIFF(CURDATE(),o.billdate) AS pending_days

    FROM transaction_entry o

    LEFT JOIN
    (
        SELECT
            ref_bill_id,
            SUM(grand_total + IFNULL(cash_disc,0)) AS paid_amount
        FROM transaction_entry
        WHERE type='payment'
          AND companyid='$companyid'
        GROUP BY ref_bill_id
    ) pay
        ON pay.ref_bill_id=o.transaction_id

    WHERE o.account_id='$account_id'
      AND o.type='order'
      AND o.is_approved='1'
      AND o.invoice_no IS NOT NULL
      AND o.invoice_no<>''
      AND o.companyid='$companyid'

    HAVING pending_amount>0

    ORDER BY o.billdate ASC
");

$total_pending = $opening_balance;

foreach ($res as $r) {
    $total_pending += $r['pending_amount'];
}
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

            <!-- Customer -->
            <div class="card border-0 shadow-lg mb-3 p-3 bg-light-primary">
                <div class="d-flex justify-content-between">

                    <div>

                        <h5 class="mb-2 text-blue">
                            <i class="bi bi-shop"></i>
                            <?= htmlspecialchars($account['account_name']) ?>
                        </h5>

                        <div class="text-secondary mb-1">
                            <i class="bi bi-person"></i>
                            <?= htmlspecialchars($account['owner_name']) ?>
                        </div>

                        <div class="text-secondary mb-1">
                            <i class="bi bi-telephone"></i>
                            <?= htmlspecialchars($account['o_mobile_no']) ?>
                        </div>

                        <div class="text-secondary">
                            <i class="bi bi-geo-alt"></i>
                            <?= htmlspecialchars($account['address']) ?>
                        </div>

                    </div>

                    <div class="text-end">

                        <small>Total Pending</small>

                        <h4 class="text-danger mb-0">
                            ₹<?= number_format($total_pending, 2) ?>
                        </h4>

                    </div>

                </div>
            </div>

            <?php if ($opening_balance > 0) { ?>

                <div class="card border-0 shadow-lg mb-3 p-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <strong class="text-primary">
                                <i class="bi bi-wallet2"></i>
                                Opening Balance
                            </strong>

                            <div class="text-muted small">
                                Previous Outstanding Balance
                            </div>

                        </div>

                        <div class="text-end">

                            <h5 class="text-danger mb-0">
                                ₹<?= number_format($opening_balance, 2) ?>
                            </h5>

                        </div>

                    </div>

                </div>

            <?php } ?>

            <?php if (!empty($res)) { ?>

                <?php foreach ($res as $row) {

                    $status = "Unpaid";
                    $statusClass = "danger";

                    if ($row['paid_amount'] > 0 && $row['pending_amount'] > 0) {
                        $status = "Partially Paid";
                        $statusClass = "warning text-dark";
                    }

                    if ($row['pending_amount'] <= ($row['order_amount'] * 0.10)) {
                        $status = "Nearly Cleared";
                        $statusClass = "success";
                    }

                    $ageBadge =
                        ($row['pending_days'] <= 15)
                        ? "success"
                        : (($row['pending_days'] <= 30) ? "warning" : "danger");

                ?>

                    <div class="card border-0 shadow-lg mb-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <strong class="text-primary">
                                Invoice No. : <?= htmlspecialchars($row['invoice_no']) ?>
                            </strong>

                            <span class="badge bg-<?= $ageBadge ?>">
                                <?= $row['pending_days'] ?> Days
                            </span>

                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <div class="text-muted small mb-3">
                                <i class="bi bi-receipt"></i>
                                Bill No. : <?= htmlspecialchars($row['billno']) ?>
                            </div>
                            <div class="text-muted small mb-3">
                                <i class="bi bi-calendar3"></i>
                                <?= date("d M Y", strtotime($row['billdate'])) ?>
                            </div>

                        </div>


                        <div class="d-flex justify-content-between mb-2">
                            <span>Bill Amount</span>
                            <strong>₹<?= number_format($row['order_amount'], 2) ?></strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Paid</span>
                            <strong class="text-success">
                                ₹<?= number_format($row['paid_amount'], 2) ?>
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Pending</span>
                            <strong class="text-danger">
                                ₹<?= number_format($row['pending_amount'], 2) ?>
                            </strong>
                        </div>

                        <div class="mt-3">
                            <span class="badge bg-<?= $statusClass ?>">
                                <?= $status ?>
                            </span>
                        </div>

                    </div>

                <?php } ?>

            <?php } ?>

            <?php if ($opening_balance <= 0 && empty($res)) { ?>

                <div class="card border-0 shadow-lg text-center p-4">

                    <i class="bi bi-check-circle fs-1 text-success"></i>

                    <h6 class="mt-2">
                        No Pending Bills
                    </h6>

                    <small class="text-muted">
                        This account has no outstanding invoices.
                    </small>

                </div>

            <?php } ?>

        </div>

    </section>

    <?php include("inc/js-file.php"); ?>

</body>

</html>