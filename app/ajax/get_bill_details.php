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

    WHERE d.type='payment' 
        AND d.ref_bill_id='$bill_id'

    ORDER BY d.transaction_id DESC
";

$res = $obj->executequery($sql);

$html = '<div class="payment-list">';

foreach ($res as $key) {

    $badgeClass = ($key['paymode'] == 'Cash') ? 'bg-success' : (($key['paymode'] == 'Online') ? 'bg-primary' : 'bg-warning');

    $html .= '
    <div class="payment-row d-flex justify-content-between align-items-center">

        <div class="left">
            <div class="date">' . $obj->dateformatindia($key['billdate']) . '</div>
        </div>
        <div class="mode badge ' . $badgeClass . '">' . $key['paymode'] . '</div>
        <div class="amount text-end">
            ₹ ' . number_format($key['grand_total'], 2) . '
        </div>

    </div>
    ';
}

$html .= '</div>';

echo $html;
