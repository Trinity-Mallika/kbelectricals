<?php
include_once("../../action.php");

$bill_id = $_POST['bill_id'];

$sql = "
    SELECT 
        d.*, 
        a.account_name 
    FROM transaction_entry d

    LEFT JOIN account a 
        ON d.account_id = a.account_id

    WHERE d.type='payment' and pay_status=1
        AND d.ref_bill_id='$bill_id'

    ORDER BY d.transaction_id DESC
";

$res = $obj->executequery($sql);

$html = '<div class="payment-list">';
if (count($res) > 0) {
    foreach ($res as $key) {

        $badgeClass = ($key['paymode'] == 'Cash')
            ? 'bg-success'
            : (($key['paymode'] == 'Online') ? 'bg-primary' : 'bg-warning');

        $totalAdjusted = $key['grand_total'] + ($key['cash_disc'] ?? 0);

        $html .= '
    <div class="payment-row d-flex justify-content-between align-items-center">

        <div class="left">
            <div class="date">' . $obj->dateformatindia($key['billdate']) . '</div>
        </div>

        <div class="mode badge ' . $badgeClass . '">' . $key['paymode'] . '</div>

        <div class="amount text-end">
            ₹ ' . number_format($totalAdjusted, 2) . '
            ' . ($key['cash_disc'] > 0 ? '
                <div class="small text-success">
                    Payment ₹ ' . number_format($key['grand_total'], 2) . '
                    + Discount ₹ ' . number_format($key['cash_disc'], 2) . '
                </div>
            ' : '') . '
        </div>

    </div>';
    }
} else {
    $html .= '
    <div class="payment-row d-flex justify-content-between align-items-center">
No Payment History.
    </div>
    ';
}

$html .= '</div>';

echo $html;
