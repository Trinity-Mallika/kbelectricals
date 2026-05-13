<?php
include_once("../../action.php");

$account_id = $_POST['account_id'];

$res = $obj->executequery("
    SELECT 
        t.transaction_id,
        t.billno,
        t.billdate,

        IFNULL(SUM(td.qty * td.rate),0) as total_amt,

        IFNULL((
            SELECT SUM(p.grand_total)
            FROM transaction_entry p
            WHERE p.ref_bill_id = t.transaction_id
            AND p.type = 'payment'
        ),0) as total_paid

    FROM transaction_entry t

    LEFT JOIN transaction_details td 
        ON td.transaction_id = t.transaction_id

    WHERE t.account_id = '$account_id'
    AND t.type = 'order'

    GROUP BY t.transaction_id
    ORDER BY t.transaction_id DESC
");

$html = '<option value="">Select Bill</option>';

foreach ($res as $row) {

    $total = $row['total_amt'];
    $billdate = $row['billdate'];
    $pending = $row['total_amt'] - $row['total_paid'];

    // skip fully paid
    if ($pending <= 0) continue;

    $html .= '<option 
            value="' . $row['transaction_id'] . '"
            data-total="' . $total . '"
            data-pending="' . $pending . '"
        >
            ' . $row['billno'] . ' (Total: ₹' . $total . ')./' . $obj->dateformatindia($billdate) . '
          </option>';
}

echo json_encode([
    "html" => $html
]);
