<?php
include("../adminsession.php");

$account_id = $_POST['account_id'];
$keyvalue = $_POST['keyvalue'];
$bill_id = $_POST['bill_id'] ?? 0;
$data =[];
$opening_amt = (float)$obj->getvalfield(
    "account",
    "opening_balance",
    "account_id='$account_id'"
);

$opening_paid = (float)$obj->getvalfield(
    "transaction_entry",
    "IFNULL(SUM(grand_total + IFNULL(cash_disc,0)),0)",
    "account_id='$account_id'
     AND type='payment' and pay_status=1
     AND pay_type='opening' and transaction_id!='$keyvalue'"
);

$opening_pending = $opening_amt - $opening_paid;

$html = '<option value="">Select Bill</option>';

if ($opening_pending > 0) {

    $data[] = [
        "id"        => "opening",
        "type"      => "opening",
        "title"     => "Opening Balance",
        "amount"    => $opening_amt,
        "pending"   => $opening_pending,
        "date"      => "",
    ];
    
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
    ON p.ref_bill_id=t.transaction_id
   AND p.type='payment'
   AND p.pay_status=1
   AND p.pay_type='bill'
   AND p.transaction_id!='$keyvalue'

WHERE t.account_id='$account_id'
AND t.type='order'
AND t.is_approved=1
AND t.invoice_no<>''

GROUP BY t.transaction_id
ORDER BY t.billdate ASC,t.transaction_id ASC
");

foreach ($res as $row) {

    $total   = (float)$row['total_amt'];
    $paid    = (float)$row['total_paid'];
    $pending = $total - $paid;

    if ($pending <= 0) {
        continue;
    }

    $data[] = [
        "id"        => $row['transaction_id'],
        "type"      => "invoice",
        "title"     => $row['invoice_no'],
        "amount"    => $total,
        "pending"   => $pending,
        "date"      => $obj->dateformatindia($row['billdate']),
    ];
}

echo json_encode($data);
