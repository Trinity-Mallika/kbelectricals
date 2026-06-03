<?php
include_once("../../action.php");

$account_id = $_POST['account_id'];

$opening_amt = (float)$obj->getvalfield(
    "account",
    "opening_balance",
    "account_id='$account_id'"
);

$opening_paid = (float)$obj->getvalfield(
    "transaction_entry",
    "IFNULL(SUM(grand_total),0)",
    "account_id='$account_id'
     AND type='payment'
     AND pay_type='opening'"
);

$opening_pending = $opening_amt - $opening_paid;

$html = '<option value="">Select Bill</option>';

/* First clear Opening Balance */
if ($opening_pending > 0) {

    $html .= '<option
                value="opening"
                data-total="' . $opening_amt . '"
                data-pending="' . $opening_pending . '">
                Opening Balance (Pending ₹' . number_format($opening_pending, 2) . ')
              </option>';
} else {

    /* Show invoices only after opening balance is cleared */
    $res = $obj->executequery("
        SELECT 
            t.transaction_id,
            t.billno,
            t.invoice_no,
            t.billdate,
            t.grand_total AS total_amt,
            IFNULL(SUM(p.grand_total),0) AS total_paid

        FROM transaction_entry t

        LEFT JOIN transaction_entry p
            ON p.ref_bill_id = t.transaction_id
            AND p.type = 'payment'
            AND p.pay_type = 'bill'

        WHERE t.account_id = '$account_id'
        AND t.type = 'order'
        AND t.is_approved = 1
        AND t.invoice_no <> ''

        GROUP BY t.transaction_id
        ORDER BY t.billdate ASC, t.transaction_id ASC
    ");

    foreach ($res as $row) {

        $total   = (float)$row['total_amt'];
        $paid    = (float)$row['total_paid'];
        $pending = $total - $paid;

        if ($pending <= 0) {
            continue;
        }

        $html .= '<option
                    value="' . $row['transaction_id'] . '"
                    data-total="' . $total . '"
                    data-pending="' . $pending . '">
                    ' . $row['invoice_no'] . '
                    (₹' . number_format($total, 2) . '
                    | Pending: ₹' . number_format($pending, 2) . ')
                    / ' . $obj->dateformatindia($row['billdate']) . '
                  </option>';
    }
}

echo json_encode([
    "html" => $html
]);