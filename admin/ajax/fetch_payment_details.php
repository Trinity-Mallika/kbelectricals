<?php
include("../../adminsession.php");

$transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;

if ($transaction_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Transaction ID."
    ]);
    exit;
}

$row = $obj->select_record(
    "transaction_entry",
    ["transaction_id" => $transaction_id]
);

if ($row) {

    $account_name = $obj->getvalfield(
        "account",
        "account_name",
        "account_id='" . $row['account_id'] . "'"
    );

    echo json_encode([
        "status" => "success",
        "data" => [
            "transaction_id" => $row['transaction_id'],
            "account_id"     => $row['account_id'],
            "account_name"   => $account_name,
            "payment_mode"   => $row['paymode'],
            "grand_total"    => $row['grand_total'],
            "payment_date"   => $row['billdate'],
            "remark"         => $row['remark'],
            "cash_disc"     => $row['cash_disc'],
            "trans_id"     => $row['trans_id'],
            "bank_id"     => $row['bank_id'],
            "billno"     => $row['billno'],
            "imgname"     => $row['imgname'],
        ]
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Payment not found."
    ]);
}