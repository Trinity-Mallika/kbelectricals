<?php
include("../adminsession.php");

require_once 'mpdf/vendor/autoload.php';

$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d');
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');
$account_id   = isset($_GET['account_id']) ? $_GET['account_id'] : 0;
$sqledit = $obj->select_record("account", ["account_id" => $account_id]);
$account_name = $sqledit['account_name'];
$owner_name = $sqledit['owner_name'];
$mobile_no = $sqledit['mobile_no'];
$o_mobile_no = $sqledit['o_mobile_no'];

$ledger_array = [];
$opening_bal = $obj->get_opening_ledger($account_id, $fromdate);
$ledger_array[] = [
    "led_date"   => $fromdate,
    "led_time"   => "00:00:00",
    "particular" => "Opening Balance",
    "total"      => abs($opening_bal),
    "led_type"   => ($opening_bal >= 0) ? "debit" : "credit"
];


$purchase = $obj->executequery("SELECT * FROM transaction_entry WHERE account_id='$account_id' AND type='order' AND is_approved='1' AND billdate BETWEEN '$fromdate' AND '$todate' ORDER BY billdate");
foreach ($purchase as $row) {
    $ledger_array[] = [
        "led_date"   => $row['billdate'],
        "led_time"   => $row['createdate'],
        "particular" => "By Order Entry " . $row['billno'] . " / Invoice No. " . $row['invoice_no'],
        "total"      => $row['invoice_amt'],
        "led_type"   => "debit"
    ];
}

$payment = $obj->executequery("
    SELECT 
        p.*,
        o.invoice_no,
        o.billno AS order_billno
    FROM transaction_entry p
    LEFT JOIN transaction_entry o
        ON o.transaction_id = p.ref_bill_id
        AND o.type = 'order'
    WHERE p.account_id='$account_id'
      AND p.type='payment'
      AND p.pay_status=1 AND p.billdate BETWEEN '$fromdate' AND '$todate'
");
foreach ($payment as $row) {
    $ledger_array[] = [
        "led_date"   => $row['billdate'],
        "led_time"   => $row['createdate'],
        "particular" => "Payment by " . $row['paymode']
            . " against " . ucfirst($row['pay_type'])
            . (!empty($row['invoice_no']) ? " / Invoice No. " . $row['invoice_no'] : ""),
        "total"      => $row['grand_total'],
        "led_type"   => "credit"
    ];
    if ($row['cash_disc'] > 0) {
        $ledger_array[] = [
            "led_date"   => $row['billdate'],
            "led_time"   => $row['createdate'],
            "particular" => "Cash Disc" . " against " . ucfirst($row['pay_type']),
            "total"      => $row['cash_disc'],
            "led_type"   => "credit"
        ];
    }
}

usort($ledger_array, function ($a, $b) {
    $t1 = strtotime($a['led_date'] . ' ' . $a['led_time']);
    $t2 = strtotime($b['led_date'] . ' ' . $b['led_time']);
    return $t2 <=> $t1;
});

$company_name = $obj->getvalfield("company_setting", "company_name", "1=1");

$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, table td, table th {
        border: 1px solid #000;
    }
    th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
        padding: 5px;
    }
    td {
        padding: 5px;
    }
    .header-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
        text-align: center;
    }
    .header-info-box {
        width:100%;
     }
    .header-logo {
        width:20%;
        float:left;
    }
    .header-info {
        width: 60%;
        float: right;
        font-size: 12px;
        line-height: 1.4;
        text-align:center;
    }
    .text-end {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .grand-total {
        font-weight: bold;
    }
    .footer-table {
        margin-top: 20px;
        border: none;
    }
    .footer-table td {
        border: none;
        padding: 5px 0;
    }
    .footer-left {
        text-align: left;
        font-size: 12px;
        vertical-align:bottom;
    }
    .footer-right {
        text-align: right;
        font-size: 12px;
    }
    .sign-image {
        max-width: 100px;
        margin-bottom: 5px;
    }
</style>


<table>
    <tr>
        <th style="border:0px;"><div class="header-title">' . htmlspecialchars($account_name) . '</div></th>
    </tr>
    <tr>
        <td style="border:0px;" class="header-info">
            
                <strong>Owner:</strong> ' . htmlspecialchars($owner_name) . '<br>
                <strong>Mobile:</strong> ' . htmlspecialchars($mobile_no) . ', ' . htmlspecialchars($o_mobile_no) . '<br>
                <strong>Date Range:</strong> ' . $obj->dateformatindia($fromdate) . ' - ' . $obj->dateformatindia($todate) . '
            
        </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th width="5%">Sr No.</th>
            <th width="15%">Date</th>
            <th width="45%">Particular</th>
            <th width="12%">Debit</th>
            <th width="12%">Credit</th>
            <th width="15%">Balance</th>
        </tr>
    </thead>
    <tbody>';

$slno = 1;
$balance = 0;
$total_debit = 0;
$total_credit = 0;

foreach ($ledger_array as $row) {
    $debit = 0;
    $credit = 0;

    if ($row['led_type'] == 'debit') {
        $debit = round($row['total'], 2);
        $total_debit += $debit;
        $balance += $debit;
    } else {
        $credit = round($row['total'], 2);
        $total_credit += $credit;
        $balance -= $credit;
    }

    $bal_type = ($balance >= 0) ? 'Dr' : 'Cr';

    $html .= '<tr>
        <td class="text-center">' . $slno++ . '</td>
        <td class="text-center">' . htmlspecialchars($obj->dateformatindia($row['led_date']));

    if (!empty($row['led_time']) && $row['led_time'] != '00:00:00') {
        $html .= '<br><small>' . date('h:i A', strtotime($row['led_time'])) . '</small>';
    }

    $html .= '</td>
        <td>' . htmlspecialchars($row['particular']) . '</td>
        <td class="text-end">' . ($debit > 0 ? number_format($debit, 2) : '-') . '</td>
        <td class="text-end">' . ($credit > 0 ? number_format($credit, 2) : '-') . '</td>
        <td class="text-end"><strong>' . number_format(abs($balance), 2) . ' ' . $bal_type . '</strong></td>
    </tr>';
}

$html .= '</tbody>
    <tfoot>
        <tr class="grand-total">
            <td colspan="3" class="text-end">Grand Total</td>
            <td class="text-end">' . number_format($total_debit, 2) . '</td>
            <td class="text-end">' . number_format($total_credit, 2) . '</td>
            <td class="text-end">' . number_format(abs($balance), 2) . ' ' . ($balance >= 0 ? 'Dr' : 'Cr') . '</td>
        </tr>
    </tfoot>
</table>

<table class="footer-table">
    <tr>
        <td class="footer-left">
            <strong>Export Date:</strong> ' . date("d F Y h:i A") . '<br>
            <strong>Exported By:</strong> ' . htmlspecialchars($obj->getvalfield("user", "fullname", "userid='$loginid'")) . '
        </td>
        <td class="footer-right">
            <div style="text-align: right;">
                <img src="uploaded/sign.png" class="sign-image" alt="Signature"><br>
                <strong>' . htmlspecialchars($company_name) . '</strong><br>
                <small>Authorized Signatory</small>
            </div>
        </td>
    </tr>
</table>';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 5,
    'margin_footer' => 5,
]);

$mpdf->SetTitle('Customer Ledger - ' . $account_name);
$mpdf->SetAuthor('KB Electricals');

$watermark_path = __DIR__ . '/uploaded/logo.png';
if (file_exists($watermark_path)) {
    $mpdf->SetWatermarkImage($watermark_path, 0.15, 'type=png'); // 15% opacity
    $mpdf->showWatermarkImage = true;
}

$mpdf->WriteHTML($html);

$filename = str_replace(' ', '_', $account_name) . '_' . date('Y-m-d') . '.pdf';
$mpdf->Output($filename,'I');
