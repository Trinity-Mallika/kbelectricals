<?php 
include_once("../adminsession.php");

$id = intval($_POST['transaction_id'] ?? 0);
$invoice = $obj->test_input($_POST['invoice_no'] ?? '');
$invoice_amt = $obj->test_input($_POST['invoice_amt'] ?? '');

if ($id <= 0 || $invoice == '') {
    echo 0;
    exit;
}

$count = $obj->getvalfield(
    "transaction_entry",
    "count(*)",
    "invoice_no='$invoice' AND transaction_id!='$id'"
);

if ($count > 0) {
    echo 2;
    exit;
}

$update = $obj->update_record(
    "transaction_entry",
    ["transaction_id" => $id],
    [
        "invoice_no" => $invoice,
        "invoice_amt" => $invoice_amt,
        "updateby" => $loginid,
        "up_date" => $createdate
    ]
);

echo 1;
exit;