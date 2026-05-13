<?php include("../adminsession.php");
require_once __DIR__ . '/mpdf/vendor/autoload.php';

$footerImages = [
    __DIR__ . '/uploaded/company/kei.png',
    __DIR__ . '/uploaded/company/rr.jpg',
    __DIR__ . '/uploaded/company/siemens.jpg',
    __DIR__ . '/uploaded/company/halonix.jpg',
    __DIR__ . '/uploaded/company/GreatWhite.png',
    __DIR__ . '/uploaded/company/crompton.png'
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
$company_id = $sqledit['company_id'];
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
$contact_no = $compdata['contact_no'];
$account_branch = $compdata['account_branch'];
$account_no = $compdata['account_no'];
$ifsc_code = $compdata['ifcs_code'];
$bank_name = $compdata['bank_name'];
$pan = $compdata['pan'];
$comp_logo = $compdata['comp_logo'];
$headerImg = __DIR__ . '/uploaded/company/' . $comp_logo;

$mpdf->SetHTMLHeader('
<table width="100%" style="border:1px solid #000; font-size:10pt;">
<tr>
<td width="20%"><img src="' . $headerImg . '" style="height:18mm;"></td>
<td width="80%" style="text-align:right;">
<b style="font-size:16pt;">M/S ' . $company_name . '</b><br>
' . $address . '<br>
TEL: ' . $mobile . '<br>
Email: ' . $email . '<br>
GST NO: ' . $gsttinno . '
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
    <h2 class="center"><u>QUOTATION</u></h2>
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
        <tr>
            <td colspan="11">

                <?= $sqledit['remark'] ?>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <th>S No</th>
            <th>Brand</th>
            <th>Item Description</th>
            <th>Unit</th>
            <th>Quantity</th>
            <th>MRP</th>
            <th>Discount</th>
            <th>Sub Total</th> <!-- <th>Net Rate</th> -->
            <th>Price after discount</th>
            <!-- <th>Total</th> -->

            <?php if ($is_gst == 1) { ?>
                <th>Taxable</th>
                <th>GST</th>
                <th>TaxType</th>
            <?php } ?>
            <th>Net Total</th>
            <th>Delivery</th>
        </tr>

        <?php
        $i = 1;
        $total = 0;
        $sql = "SELECT td.*,p.product_name,b.cat_name brand,u.cat_name unit,c.cat_name category, g.gst_name
FROM transaction_details td
LEFT JOIN product_master p ON p.product_id=td.product_id
LEFT JOIN category_master b ON b.cat_id=td.brand_id
LEFT JOIN category_master u ON u.cat_id=td.unit_id
LEFT JOIN category_master c ON c.cat_id=td.category_id
LEFT JOIN gst_master g ON g.gst_id = td.gst_id
WHERE td.transaction_id='$keyvalue'";

        $res = $obj->executequery($sql);
        $has_product_gst = false;
        foreach ($res as $row) {
            if ($row['gst_id'] > 0) {
                $has_product_gst = true;
            }
            $price_after = $row['rate'] - ($row['rate'] * $row['discount'] / 100);
        ?>
            <tr>
                <td class="center"><?= $i++ ?>.</td>
                <td><?= $row['brand'] ?></td>
                <td><b><?php echo $row['category'] ?></b><br><?= $row['product_name'] ?></td>
                <td><?= $row['unit'] ?></td>
                <td class="center"><?= $row['qty'] ?></td>
                <td class="right"><?= number_format($row['rate'], 2) ?></td>
                <td class="center"><?= $row['discount'] ?>%</td>
                <td class="right"><?= number_format($row['sub_total'], 2) ?></td>
                <td class="right"><?= number_format($price_after, 2) ?></td>
                <!-- <td class="right"><?= number_format($row['total_amt'], 2) ?></td> -->
                <?php if ($is_gst == 1) { ?>
                    <td class="right"><?= number_format($row['total_amt'], 2) ?></td>

                    <td class="center">
                        <?= ($row['gst_name']) ? $row['gst_name'] : '0%' ?>
                    </td>

                    <td class="center">
                        <?= ucfirst($row['taxtype']) ?>
                    </td>
                <?php } ?>

                <td class="right">
                    <?= number_format($row['net_amt'], 2) ?>
                </td>
                <td><?= $row['ready_stock'] ? 'Ready stock' : $row['delivery_status'] ?></td>
            </tr>
        <?php $total += $row['net_amt'];
        } ?>

        <?php $colspan = ($is_gst == 1) ? 12 : 9; ?>

        <tr>
            <td colspan="<?= $colspan ?>" class="right"><b>Total</b></td>
            <td class="right"><b><?= number_format($total, 2) ?></b></td>
            <td></td>

        </tr>


        <?php if ($is_gst == 1 && !$has_product_gst) { // if ($cgst > 0) 
        ?>


            <tr>
                <td colspan="<?= $colspan ?>" class="right"><b>GST %</b></td>
                <td class="right"><b><?= $gst_percent ?>%</b></td>
                <td></td>

            </tr>

            <tr>
                <td colspan="<?= $colspan ?>" class="right"><b>CGST</b></td>
                <td class="right"><b><?= number_format($cgst, 2) ?></b></td>
                <td></td>

            </tr>

            <tr>
                <td colspan="<?= $colspan ?>" class="right"><b>SGST</b></td>
                <td class="right"><b><?= number_format($sgst, 2) ?></b></td>
                <td></td>

            </tr>

            <tr>
                <td colspan="<?= $colspan ?>" class="right"><b>Grand Total</b></td>
                <td class="right"><b><?= number_format($grand_total, 2) ?></b></td>
                <td></td>

            </tr>

        <?php } ?>


    </table>
    <table>
        <tr>
            <td width="50%">
                <?php if ($is_gst == 1) { ?>
                    GST: <?= $sqledit['gst'] ?><br>
                <?php } ?>

                Validity: <?= $sqledit['validity'] ?><br>
                Freight: <?= $sqledit['freight'] ?><br>
                Payment: <?= $sqledit['payment'] ?>
            </td>
            <td width="50%">
                Bank Name: <?= $compdata['bank_name'] ?><br>
                Branch: <?= $compdata['account_branch'] ?><br>
                IFSC: <?= $compdata['ifcs_code'] ?><br>
                Account No: <?= $compdata['account_no'] ?>
            </td>
        </tr>
    </table>

</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output();
