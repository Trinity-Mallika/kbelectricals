<?php
include("appsession.php");

$title = "Pending Payment List";
$account_id = (int)($_GET['account_id'] ?? 0);

$account = $obj->select_record("account", ["account_id" => $account_id]);

// Opening Balance
$opening_balance = (float)($account['opening_balance'] ?? 0);

// Opening Balance Payment
$opening_balance_paid = (float)$obj->getvalfield(
    "transaction_entry",
    "COALESCE(
        SUM(
            CASE
                WHEN type='payment' and pay_type='opening'
                     AND pay_status=1
                THEN (grand_total + IFNULL(cash_disc,0))

                ELSE 0
            END
        ),0
    ) AS balance",
    "account_id=$account_id
     AND companyid=$companyid"
);

$opening_current = $opening_balance - $opening_balance_paid;

$openingStatus = $opening_current > 0 ? 'Pending' : 'Cleared';
$openingClass  = $opening_current > 0 ? 'danger' : 'success';
$openingIcon   = $opening_current > 0 ? 'exclamation-circle' : 'check-circle';

// Total Outstanding (Ledger)
$total_pending = $opening_balance + (float)$obj->getvalfield(
    "transaction_entry",
    "COALESCE(
        SUM(
            CASE
                WHEN type='order'
                     AND is_approved=1
                     AND invoice_no<>''
                THEN invoice_amt

                WHEN type='payment'
                     AND pay_status=1
                THEN -(grand_total + IFNULL(cash_disc,0))

                ELSE 0
            END
        ),0
    ) AS balance",
    "account_id=$account_id
     AND companyid=$companyid"
);

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

        DATEDIFF(CURDATE(), o.billdate) AS pending_days

    FROM transaction_entry o

    LEFT JOIN
    (
        SELECT
            ref_bill_id,
            SUM(grand_total + IFNULL(cash_disc,0)) AS paid_amount
        FROM transaction_entry
        WHERE type='payment'
          AND pay_status=1
          AND companyid='$companyid'
        GROUP BY ref_bill_id
    ) pay
        ON pay.ref_bill_id=o.transaction_id

    WHERE o.account_id='$account_id'
      AND o.type='order'
      AND o.is_approved='1'
      AND o.invoice_no<>''
      AND o.companyid='$companyid'

    HAVING pending_amount>0

    ORDER BY o.billdate ASC
");
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row text-center">

                            <div class="col-4 border-end">
                                <small class="d-block">Opening</small>
                                <strong>
                                    ₹<?= number_format($opening_balance, 2) ?>
                                </strong>
                            </div>

                            <div class="col-4 border-end">
                                <small class="d-block">Paid</small>
                                <strong class="text-success">
                                    ₹<?= number_format($opening_balance_paid, 2) ?>
                                </strong>
                            </div>

                            <div class="col-4">
                                <small class="d-block">Balance</small>
                                <strong class="<?= $opening_current > 0 ? 'text-danger' : 'text-success' ?>">
                                    ₹<?= number_format($opening_current, 2) ?>
                                </strong>
                            </div>
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
                        : (($row['pending_days'] <= 30) ? "warning text-black" : "danger");

                ?>

                    <div class="card border-0 shadow-lg mb-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <strong class="text-primary">
                                Invoice No. : <?= htmlspecialchars_decode($row['invoice_no']) ?>
                            </strong>

                            <span class="badge bg-<?= $statusClass ?> me-2">
                                <?= $status ?>
                            </span>

                            <span class="badge bg-<?= $ageBadge ?>">
                                <?= $row['pending_days'] ?> Days
                            </span>

                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <div class="text-muted small mb-3">
                                <i class="bi bi-receipt"></i>
                                Bill No. : <?= htmlspecialchars_decode($row['billno']) ?>
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