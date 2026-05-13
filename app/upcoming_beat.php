<?php
include("appsession.php");

$title = "Upcoming Beat List";

$nextDay = date('l', strtotime('+1 day'));

$res = $obj->executequery("
    SELECT
        rc.sequence,
        a.account_id,
        a.account_name,
        a.owner_name,
        a.mobile_no,

        COALESCE(
            SUM(
                CASE
                    WHEN o.transaction_id IS NOT NULL THEN
                        (
                            o.grand_total - COALESCE(
                                (
                                    SELECT SUM(p.grand_total)
                                    FROM transaction_entry p
                                    WHERE p.ref_bill_id = o.transaction_id
                                      AND p.type = 'payment'
                                      AND p.companyid = '$companyid'
                                ),
                                0
                            )
                        )
                    ELSE 0
                END
            ),
            0
        ) AS pending_amount

    FROM route_plan rp

    JOIN route r
        ON r.batch_no = rp.batch_no

    JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
       AND rc.is_active = '1'

    JOIN account a
        ON a.account_id = rc.account_id
       AND a.status1 = '1'

    LEFT JOIN transaction_entry o
        ON o.account_id = a.account_id
       AND o.type = 'order'
       AND o.is_approved = '1'
       AND o.companyid = '$companyid'

    WHERE rp.sales_executive_id = '$loginid'
      AND LOWER(r.day_of_week) = LOWER('$nextDay')
      AND rp.companyid = '$companyid'
      AND rc.companyid = '$companyid'

    GROUP BY
        rc.sequence,
        a.account_id,
        a.account_name,
        a.mobile_no
        
    ORDER BY rc.sequence ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= $title ?></title>
    <?php include("inc/css-file.php"); ?>
</head>

<body class="dashboard">

    <section class="top-sec">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body">
                            <h5 class="mb-1 fw-bold text-blue">
                                Route plan for <?= $nextDay ?>
                            </h5>
                            <small class="text-secondary">
                                Visit schedule & payment follow-up
                            </small>
                        </div>
                    </div>
                    <?php if (!empty($res)) {
                        foreach ($res as $key):
                            $mobile = preg_replace('/[^0-9]/', '', $key['mobile_no']);
                            $shop = $key['account_name'];
                            $balance = number_format($key['pending_amount'], 2);

                            $msg = rawurlencode(
                                "नमस्कार भैया जी 🙏

{$shop} पर कल मेरा विजिट निर्धारित है।

यदि कोई भी Replacement / Service संबंधित सामग्री हो तो कृपया मुझे अवश्य बता दें, ताकि उसका समाधान तुरंत किया जा सके।

आपके लेजर में वर्तमान बकाया राशि ₹{$balance} है। कृपया संभव हो तो भुगतान तैयार रखिएगा, जिससे अकाउंट नियमित बना रहे।

साथ ही कृपया अपने स्टाफ से स्टॉक भी चेक करवा लें। यदि कोई आइटम कम या खत्म हो गया हो तो उसका ऑर्डर भी मैं साथ में बुक कर लूंगा, ताकि माल की उपलब्धता बनी रहे।

धन्यवाद 🙏
…………………..
KB Electricals"
                            );

                            $waLink = !empty($mobile)
                                ? "https://wa.me/91{$mobile}?text={$msg}"
                                : "#";
                            ?>

                            <div class="card border-0 shadow-lg mb-3 p-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="55" class="text-center align-middle">
                                            <h4 class="mb-0 text-blue">
                                                <?= $key['sequence'] ?>.
                                            </h4>
                                        </td>
                                        <td class="border-start">
                                            <p class="ms-1 mb-1 d-flex justify-content-between">
                                                <span> <i class="bi bi-shop"></i>
                                                    <?= htmlspecialchars($key['account_name']) ?></span>
                                                <?php if ($balance > 0) { ?>
                                                    <span class="text-danger fw-bold"> ₹<?= $balance ?></span>
                                                <?php } ?>
                                            </p>
                                            <p class="ms-1 mb-1 d-flex justify-content-between">
                                                <span> <i
                                                        class="bi bi-person"></i><?= htmlspecialchars($account['owner_name'] ?? '-') ?></span>
                                            </p>
                                            <p class="ms-1 mb-1 d-flex justify-content-between">
                                                <span>
                                                    <strong><i class="bi bi-telephone me-1"></i></strong>
                                                    <?= htmlspecialchars($key['mobile_no']) ?>
                                                </span>
                                                <span>
                                                    <?php if (!empty($mobile)) { ?>
                                                        <a href="<?= $waLink ?>" target="_blank"
                                                            class="btn btn-success btn-sm ms-1">
                                                            <i class="bi bi-whatsapp"></i>
                                                            Send WA
                                                        </a>
                                                    <?php } ?>
                                                </span>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                        <?php endforeach; ?>

                    <?php } else { ?>

                        <div class="card border-0 shadow-lg text-center p-4">
                            <i class="bi bi-info-circle fs-1 text-primary"></i>
                            <h6 class="mt-2 mb-1">No Counter Found</h6>
                        </div>

                    <?php } ?>

                </div>
            </div>
        </div>
    </section>

    <?php include("inc/js-file.php"); ?>
</body>

</html>