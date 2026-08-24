<?php
include("appsession.php");

if (isset($_POST['transaction_id'])) {

    $transaction_id = $_POST['transaction_id'];

    $res = $obj->select_record(
        "transaction_entry",
        [
            'transaction_id' => $transaction_id
        ]
    );

    $account_name = $obj->getvalfield(
        "account",
        "account_name",
        "account_id='{$res['account_id']}'"
    );

    if ($res['pay_type'] == "opening") {
        $against = "Opening Balance";
    } else {
        $against = $obj->getvalfield(
            "transaction_entry",
            "IF(invoice_no<>'',invoice_no,billno)",
            "transaction_id='{$res['ref_bill_id']}'"
        );
    }

    $ref = '-';

    if ($res['paymode'] == 'Cheque') {
        $ref = $res['trans_id'];
    } elseif ($res['paymode'] == 'Online') {
        $ref = $res['trans_id'];
    }
?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">

            <h6 class="fw-bold text-primary mb-3">
                <i class="bi bi-person-circle"></i>
                <?= $account_name ?>
            </h6>

            <div class="row small">

                <div class="col-5 text-muted">Against</div>
                <div class="col-7 fw-semibold"><?= $against ?></div>

                <div class="col-5 text-muted mt-2">Amount</div>
                <div class="col-7 fw-bold text-success mt-2">
                    ₹<?= number_format($res['grand_total'], 2) ?>
                </div>

                <div class="col-5 text-muted mt-2">Mode</div>
                <div class="col-7 mt-2"><?= $res['paymode'] ?></div>

                <div class="col-5 text-muted mt-2">Date</div>
                <div class="col-7 mt-2">
                    <?= $obj->dateformatindia($res['billdate']) ?>
                </div>

                <?php if ($res['paymode'] == 'Cash') { ?>
                    <div class="col-5 text-muted mt-2">Voucher</div>
                    <div class="col-7 mt-2"><?= $res['billno'] ?></div>
                <?php } else { ?>
                    <div class="col-5 text-muted mt-2">Reference</div>
                    <div class="col-7 mt-2 text-break">
                        <?= $ref ?>
                    </div>
                <?php } ?>

            </div>

            <?php if (!empty($res['address'])) { ?>
                <hr class="my-2">

                <div class="small">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <?= $res['address'] ?>
                </div>
            <?php } ?>

            <?php if (!empty($res['imgname'])) { ?>
                <hr class="my-2">

                <a href="<?= '../admin/payment_proof/' . $res['imgname'] ?>" target="_blank">
                    <img src="<?= '../admin/payment_proof/' . $res['imgname'] ?>"
                        class="img-thumbnail"
                        style="width:90px">
                </a>
            <?php } ?>

        </div>
    </div>
<?php } ?>