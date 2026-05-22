<?php
include_once("../../action.php");

$account_id = $_POST['account_id'];

$res = $obj->executequery("
    SELECT 
        t.transaction_id,
        t.billno,
        t.invoice_no,
        t.billdate,

        IFNULL(SUM(td.qty * td.rate),0) AS total_amt,

        IFNULL(SUM(p.grand_total),0) AS total_paid

    FROM transaction_entry t

    LEFT JOIN transaction_details td 
        ON td.transaction_id = t.transaction_id

    LEFT JOIN transaction_entry p
        ON p.ref_bill_id = t.transaction_id
        AND p.type = 'payment'

    WHERE t.account_id = '$account_id'
    AND t.type = 'order'
    AND t.invoice_no != ''

    GROUP BY t.transaction_id
    ORDER BY t.transaction_id DESC
");

$html = '<option value="">Select Bill</option>';

foreach ($res as $row) {

    $total = $row['total_amt'];
    $paid = $row['total_paid'];
    $pending = $total - $paid;

    if ($pending <= 0) continue;

    $html .= '<option 
        value="' . $row['transaction_id'] . '"
        data-total="' . $total . '"
        data-pending="' . $pending . '"
    >
        ' . $row['invoice_no'] . ' (₹' . $total . ' | Pending: ₹' . $pending . ') / ' . $obj->dateformatindia($row['billdate']) . '
    </option>';
}

echo json_encode([
    "html" => $html
]);