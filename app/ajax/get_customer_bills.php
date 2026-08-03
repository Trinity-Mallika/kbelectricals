<?php
include_once("../../action.php");

$account_id = $_POST['account_id'];
$keyvalue = $_POST['keyvalue'];
$bill_id = $_POST['bill_id'];

$opening_amt = (float)$obj->getvalfield(
    "account",
    "opening_balance",
    "account_id='$account_id'"
);

$opening_paid = (float)$obj->getvalfield(
    "transaction_entry",
    "IFNULL(SUM(grand_total + IFNULL(cash_disc,0)),0)",
    "account_id='$account_id'
     AND type='payment' 
     AND pay_type='opening' and transaction_id!='$keyvalue'"
);

$opening_pending = $opening_amt - $opening_paid;

$html = '<option value="">Select Bill</option>';

if ($opening_pending > 0) {

    $html .= '<option
                value="opening"
                data-total="' . $opening_amt . '"
                data-pending="' . $opening_pending . '"
                selected>
                Opening Balance (Pending ₹' . number_format($opening_pending, 2) . ')
              </option>';
}

$res = $obj->executequery("
SELECT 
    t.transaction_id,
    t.billno,
    t.invoice_no,
    t.billdate,
    t.grand_total AS total_amt,

    IFNULL(
        SUM(
            p.grand_total + IFNULL(p.cash_disc,0)
        ),
        0
    ) AS total_paid

FROM transaction_entry t

LEFT JOIN transaction_entry p
    ON p.ref_bill_id = t.transaction_id
    AND p.type='payment'
    AND p.pay_type='bill'
    AND p.transaction_id!='$keyvalue'

WHERE t.account_id='$account_id'
AND t.type='order'
AND t.is_approved=1
AND t.invoice_no<>''

GROUP BY t.transaction_id
ORDER BY t.billdate,t.transaction_id
");

foreach ($res as $row) {
    $total   = (float)$row['total_amt'];
    $paid    = (float)$row['total_paid'];
    $pending = $total - $paid;

    if ($opening_pending > 0) {
        $disabled = "disabled";
    } else {
        $disabled = ($pending <= 0) ? "disabled" : "";
    }

    $selected = ($bill_id == $row['transaction_id']) ? "selected" : "";

    $html .= '<option
                value="' . $row['transaction_id'] . '"
                data-total="' . $total . '"
                data-pending="' . $pending . '"
                ' . $disabled . '
                ' . $selected . '>';

    if ($pending <= 0) {
        $html .= "✅ ";
    } elseif ($opening_pending > 0) {
        $html .= "🔒 ";
    }

    $html .= $row['invoice_no'] .
        ' (₹' . number_format($total, 2) .
        ' | Pending ₹' . number_format($pending, 2) .
        ') / ' . $obj->dateformatindia($row['billdate']);

    $html .= '</option>';
}

echo json_encode([
    "html" => $html
]);
