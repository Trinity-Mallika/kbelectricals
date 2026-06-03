<?php
include("../adminsession.php");
require_once __DIR__ . '/mpdf/vendor/autoload.php';

$footerImages = [
    __DIR__ . '/uploaded/company/kei.png',
    __DIR__ . '/uploaded/company/rr.jpg',
    __DIR__ . '/uploaded/company/siemens.jpg',
    __DIR__ . '/uploaded/company/halonix.jpg',
    __DIR__ . '/uploaded/company/GreatWhite.png',
];


$mpdf = new \Mpdf\Mpdf([
    'margin_top'    => 45,
    'margin_bottom' => 35,
    'margin_left'   => 5,
    'margin_right'  => 5,
]);

$tblname        = "transaction_entry";
$tblpkey        = "transaction_id";
$transaction_id = isset($_GET["transaction_id"]) ? $obj->test_input($_GET["transaction_id"]) : 0;
$sqledit    = $obj->select_record($tblname, [$tblpkey => $transaction_id]);
$company_id = $sqledit['companyid'];
$account_id = $sqledit['account_id'];
$account_name = $obj->getvalfield("account", "account_name", "account_id='$account_id'");
$mobile_no    = $obj->getvalfield("account", "mobile_no",    "account_id='$account_id'");
$billno       = $sqledit['billno'];
$billdate     = $sqledit['billdate'];
$remark       = $sqledit['remark'];
$is_approved  = $sqledit['is_approved'];
$approve_date = $sqledit['approve_date'] ?? '';
$invoice_no   = $sqledit['invoice_no'];

$compdata       = $obj->select_record('company_setting', ['company_id' => $company_id]);
$company_name   = $compdata['company_name'];
$mobile         = $compdata['mobile'];
$address        = $compdata['address'];
$email          = $compdata['email'];
$gsttinno       = $compdata['gst'];
$bank_name      = $compdata['bank_name'];
$account_branch = $compdata['account_branch'];
$account_no     = $compdata['account_no'];
$ifsc_code      = $compdata['ifcs_code'];
$term_cond      = $compdata['term_cond'];
$comp_logo      = $compdata['comp_logo'];
$headerImg      = __DIR__ . '/uploaded/company/' . $comp_logo;

/* ── Header (identical structure to quotation) ────────────────────── */
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

/* ── Footer (identical to quotation) ──────────────────────────────── */
$footerHTML = '<table width="100%" style="text-align:center;"><tr>';
foreach ($footerImages as $img) {
    $footerHTML .= '<td><img src="' . $img . '" style="height:12mm;margin-right:20px;"></td>';
}
$footerHTML .= '</tr></table>';
$mpdf->SetHTMLFooter($footerHTML);

/* ── Fetch line items ─────────────────────────────────────────────── */
$sql = "
    SELECT
        td.*,
        p.product_name,
        b.cat_name  AS brand_name,
        u.cat_name  AS unit_name,
        c.cat_name  AS category_name
    FROM   transaction_details td
    LEFT JOIN product_master  p ON p.product_id = td.product_id
    LEFT JOIN category_master b ON b.cat_id = td.brand_id    AND b.type = 'brand'
    LEFT JOIN category_master c ON c.cat_id = td.category_id AND c.type = 'category'
    LEFT JOIN category_master u ON u.cat_id = td.unit_id     AND u.type = 'unit'
    WHERE  td.transaction_id = '$transaction_id' AND td.type = 'order'
    ORDER  BY td.tran_detail_id ASC
";
$items = $obj->executequery($sql);

/* ── Totals ───────────────────────────────────────────────────────── */
$grand_total = 0;
$total_qty   = 0;
foreach ($items as $row) {
    $grand_total += $row['total_amt'];
    $total_qty   += $row['qty'];
}

/* ── Helpers ──────────────────────────────────────────────────────── */
function fmt_date($d)
{
    return $d ? date('d M Y', strtotime($d)) : '—';
}

/* ── Build HTML ───────────────────────────────────────────────────── */
ob_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ORDER</title>
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
            padding: 4px 5px;
        }

        th {
            background: #8fa9c4;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .total-row {
            background: #dce6f1;
            font-weight: bold;
        }

        .badge-ok {
            background: #c6efce;
            color: #276221;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8pt;
        }

        .badge-warn {
            background: #ffeb9c;
            color: #9c6500;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8pt;
        }

        .badge-appr {
            background: #c6efce;
            color: #276221;
            padding: 2px 8px;
            font-size: 9pt;
            font-weight: bold;
        }

        .badge-pend {
            background: #ffeb9c;
            color: #9c6500;
            padding: 2px 8px;
            font-size: 9pt;
            font-weight: bold;
        }

        .muted {
            color: #555;
            font-size: 8pt;
        }
    </style>
</head>

<body>

    <h2 class="center"><u>ORDER</u></h2>

    <!-- TO / ORDER META -->
    <table>
        <tr>
            <td colspan="5">
                TO: <b><?= strtoupper($account_name) ?></b><br>
                <?php if (!empty($mobile_no)): ?>
                    Mobile: <?= $mobile_no ?><br>
                <?php endif; ?>
                Dear Sir,
            </td>
            <td colspan="5">
                <b>Order No:</b> <?= $billno ?><br>
                <b>Date:</b> <?= fmt_date($billdate) ?>
                <?php if (!empty($invoice_no)): ?>
                    <br><b>Invoice No:</b> <?= htmlspecialchars($invoice_no) ?>
                <?php endif; ?>
            </td>
        </tr>

    </table>

    <!-- PRODUCT TABLE -->
    <table>
        <thead>
            <tr>
                <th width="4%">S No</th>
                <th width="15%">Brand</th>
                <th width="32%">Item Description</th>
                <th width="6%">Unit</th>
                <th width="7%">Qty</th>
                <th width="11%">Rate (Rs.)</th>
                <th width="13%">Total (Rs.)</th>
                <th width="12%">Dispatch</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($items as $row):
                $dispatched = ($row['is_dispatched'] == 1);
                $nettotal = ($is_gst == 1) ? $row['total_amt'] : $row['net_amt'];
            ?>
                <tr>
                    <td class="center"><?= $i++ ?>.</td>
                    <td><?= htmlspecialchars($row['brand_name']) ?></td>
                    <td>
                        <b><?= htmlspecialchars($row['category_name']) ?></b><br>
                        <?= htmlspecialchars($row['product_name']) ?>
                    </td>
                    <td class="center"><?= htmlspecialchars($row['unit_name']) ?></td>
                    <td class="center"><?= $row['qty'] ?></td>
                    <td class="right"><?php echo $row['price_after_disc'] ?></td>
                    <td class="right"><?= number_format($nettotal, 2) ?></td>
                    <td class="center">
                        <?php if ($dispatched): ?>
                            <span class="badge-ok">Delivered</span>
                            <?php if (!empty($row['dispatch_date'])): ?>
                                <br><span class="muted"><?= fmt_date($row['dispatch_date']) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge-warn">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- TOTAL ROW -->
            <tr class="total-row">
                <td colspan="4" class="right">Total</td>
                <td class="center"><?= $total_qty ?></td>
                <td></td>
                <td class="right">Rs. <?= number_format($grand_total, 2) ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table>
        <tr>
            <td colspan="11">Remark : <?= htmlspecialchars($remark) ?></td>
        </tr>
    </table>

    <table style="margin-top:6px; border:none;">
        <tr>
            <td width="50%" style="height:25mm; vertical-align:bottom; border:none;">&nbsp;</td>
            <td width="50%" class="right" style="height:25mm; vertical-align:bottom; border:none;">
                For M/S <?= htmlspecialchars($company_name) ?><br><br>
                ___________________________<br>
                <span class="muted">Authorised Signatory</span>
            </td>
        </tr>
    </table>

</body>

</html>
<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output("Order_{$billno}.pdf", \Mpdf\Output\Destination::INLINE);
