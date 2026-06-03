<?php include("../adminsession.php");
require_once __DIR__ . '/mpdf/vendor/autoload.php';

$footerImages = [
    __DIR__ . '/uploaded/company/kei.png',
    __DIR__ . '/uploaded/company/rr.jpg',
    __DIR__ . '/uploaded/company/siemens.jpg',
    __DIR__ . '/uploaded/company/halonix.jpg',
    __DIR__ . '/uploaded/company/GreatWhite.png'
    // __DIR__ . '/uploaded/company/crompton.png'
];

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 45,
    'margin_bottom' => 35,
    'margin_left' => 5,
    'margin_right' => 5,
]);

$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$keyvalue = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$type = "quotation";
$sqledit = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
$company_id = $sqledit['companyid'];
$account_id = $sqledit['account_id'];
$account_name = $obj->getvalfield("account", "account_name", "account_id='$account_id'");
$remark = $sqledit['remark'];
$billdate = $sqledit['billdate'];
$billno = $sqledit['billno'];
$cgst = $sqledit['cgst'];
$sgst = $sqledit['sgst'];
$gst = $sqledit['gst'];
$is_gst = $sqledit['is_gst'];
$gst_percent = $sqledit['gst_percent'];
$freight = $sqledit['freight'];
$validity = $sqledit['validity'];
$grand_total = $sqledit['grand_total'];

$compdata = $obj->select_record('company_setting', ['company_id' => $company_id]);
$company_name = $compdata['company_name'];
$mobile = $compdata['mobile'];
$address = $compdata['address'];
$email = $compdata['email'];
$term_cond = $compdata['term_cond'];
$gsttinno = $compdata['gst'];
$dispatch_no = $compdata['dispatch_no'];
$accounts_no = $compdata['accounts_no'];
$quo_no = $compdata['quo_no'];
$account_branch = $compdata['account_branch'];
$account_no = $compdata['account_no'];
$ifsc_code = $compdata['ifcs_code'];
$bank_name = $compdata['bank_name'];
$pan = $compdata['pan'];
$comp_logo = $compdata['comp_logo'];
$headerImg = __DIR__ . '/uploaded/company/' . $comp_logo;
$companyInfo = '';

$mpdf->SetHTMLHeader('
<table width="100%" style="border:1px solid #000;border-collapse:collapse;">
    <tr>
        <td width="25%" style="padding:8px;text-align:center;vertical-align:middle;">
            <img src="' . $headerImg . '" style="max-height:22mm;">
        </td>

        <td width="75%" style="padding:6px;text-align:right;vertical-align:top;font-size:9pt;">
            <div style="font-size:18pt;font-weight:bold;">
                M/S ' . strtoupper($company_name) . '
            </div>

            ' . (!empty($address) ? $address . '<br>' : '') . '

            <table width="100%" style="font-size:9pt;border:none;">
                <tr>
                    <td align="right" style="border:none;">
                        ' . (!empty($mobile) ? '<b>Mobile :</b> ' . $mobile . '<br>' : '') . '
                        ' . (!empty($dispatch_no) ? '<b>Dispatch :</b> ' . $dispatch_no . '<br>' : '') . '
                        ' . (!empty($accounts_no) ? '<b>Accounts :</b> ' . $accounts_no . '<br>' : '') . '
                        ' . (!empty($quo_no) ? '<b>Inquiry :</b> ' . $quo_no . '<br>' : '') . '
                        ' . (!empty($email) ? '<b>Email :</b> ' . $email . '<br>' : '') . '
                        ' . (!empty($gsttinno) ? '<b>GSTIN :</b> ' . $gsttinno : '') . '
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
');

$footerHTML = '<table width="100%" style="text-align:center;">
    <tr>';

foreach ($footerImages as $img) {
    $footerHTML .= '<td>
            <img src="' . $img . '" style="height:12mm;margin: right 20px;">
        </td>';
}

$footerHTML .= '</tr>
</table>';

$mpdf->SetHTMLFooter($footerHTML);

ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUOTATION</title>
    <style>
        body {
            font-size: 9pt;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background: #8fa9c4;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2 style="
    text-align:center;
    border:1px solid #000;
    padding:6px;
    margin-bottom:0;
    background:#f2f2f2;
">
        QUOTATION
    </h2>
    <table>
        <tr>
            <td colspan="5">
                TO: <b><?= strtoupper($account_name) ?></b><br>
                Dear Sir,
            </td>
            <td colspan="5"><b>Quotation No:</b> <?= $sqledit['billno'] ?><br>
                <b>Date:</b> <?= date('d M Y', strtotime($sqledit['billdate'])) ?>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <th>S No</th>
            <th>Item Description</th>
            <th>Brand</th>
            <th>Unit</th>
            <th>Quantity</th>
            <!-- <th>MRP</th>
            <th>Discount</th> -->
            <th class="right" width="120">Price After Disc.</th>
            <th class="right"> <?= ($is_gst == 1) ? "Total Value" : "Net Total" ?></th>
            <th>Delivery</th>
        </tr>

        <?php
        $i = 1;
        $total = 0;
        $tax_amount = 0;
        $sql = "SELECT td.*,p.product_name,b.cat_name brand,u.cat_name unit,c.cat_name category, g.gst_name
FROM transaction_details td
LEFT JOIN product_master p ON p.product_id=td.product_id
LEFT JOIN category_master b ON b.cat_id=td.brand_id
LEFT JOIN category_master u ON u.cat_id=td.unit_id
LEFT JOIN category_master c ON c.cat_id=td.category_id
LEFT JOIN gst_master g ON g.gst_id = td.gst_id
WHERE td.transaction_id='$keyvalue' order by td.tran_detail_id ASC";

        $res = $obj->executequery($sql);
        foreach ($res as $row) {
            $nettotal = ($is_gst == 1) ? $row['total_amt'] : $row['net_amt'];
        ?>
            <tr>
                <td class="center"><?= $i++ ?>.</td>
                <td><b><?php echo $row['category'] ?></b><br><?= $row['product_name'] ?></td>
                <td><?= $row['brand'] ?></td>
                <td><?= $row['unit'] ?></td>
                <td class="center"><?= $row['qty'] ?></td>
                <!-- <td class="right"><?= number_format($row['rate'], 2) ?></td>
                <td class="center"><?= $row['discount'] ?>%</td> -->
                <td class="right">Rs. <?php echo $row['price_after_disc'] ?></td>
                <td class="right">
                    Rs. <?= number_format($nettotal, 2) ?>
                </td>
                <td><?= $row['ready_stock'] ? 'Ready stock' : $row['delivery_status'] ?></td>
            </tr>
        <?php $total += $nettotal;
            $tax_amount += $row['gst_amt'];
        } ?>
        <tr>
            <td colspan="6" class="right"><b>Total</b></td>
            <td class="right"><b>Rs. <?= number_format(round($total), 2) ?></b></td>
            <td></td>
        </tr>
    </table>

    <table style="margin-top:12px;">
        <tr>
            <td>
                <b>Remarks : </b>
                <?= nl2br($sqledit['remark']) ?>
            </td>
        </tr>
    </table>
    <table style="margin-top:10px;">

        <tr>
            <td width="50%" valign="top">

                <b>Commercial Details</b><br><br>

                <?php if ($is_gst == 1) { ?>
                    GST : <?= $sqledit['gst'] ?> <?php if ($is_gst == 1) { ?>
                        @18% Amount: <b>Rs. <?= number_format($tax_amount, 2) ?></b><br>
                    <?php } else { ?>
                        <br>
                    <?php } ?>
                <?php } ?>

                <?php if (!empty($sqledit['validity'])) { ?>
                    Validity : <?= $sqledit['validity'] ?><br>
                <?php } ?>

                <?php if (!empty($sqledit['freight'])) { ?>
                    Freight : <?= $sqledit['freight'] ?><br>
                <?php } ?>

                <?php if (!empty($sqledit['payment'])) { ?>
                    Payment Terms : <?= $sqledit['payment'] ?><br>
                <?php } ?>

            </td>

            <td width="50%" valign="top">

                <b>Bank Details</b><br><br>

                <?= !empty($bank_name) ? 'Bank : ' . $bank_name . '<br>' : '' ?>
                <?= !empty($account_branch) ? 'Branch : ' . $account_branch . '<br>' : '' ?>
                <?= !empty($ifsc_code) ? 'IFSC : ' . $ifsc_code . '<br>' : '' ?>
                <?= !empty($account_no) ? 'A/C No : ' . $account_no . '<br>' : '' ?>

            </td>

        </tr>

    </table>
    <?php if (!empty($term_cond)) { ?>

        <table style="margin-top:12px;">
            <tr>
                <td>
                    <b>Terms & Conditions</b>
                    <hr>

                    <?= nl2br($term_cond) ?>
                </td>
            </tr>
        </table>

    <?php } ?>
    <table style="margin-top:20px;border:none;">
        <tr>

            <td width="60%" style="border:none;">
                Thanking You,<br>
                We look forward to your valuable order.
            </td>

            <td width="40%" style="border:none;text-align:right;">
                <b>For, <?= strtoupper($company_name) ?></b>

                <br><br><br><br>

                Authorized Signatory
            </td>

        </tr>
    </table>

</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output($account_name . "_" . $billno . ".pdf", "I");
