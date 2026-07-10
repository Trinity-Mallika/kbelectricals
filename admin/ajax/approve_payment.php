<?php
include("../../adminsession.php");

header('Content-Type: application/json');

$transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;

if ($transaction_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Transaction ID."
    ]);
    exit;
}

$where = [
    "transaction_id" => $transaction_id
];

$form_data = [
    "pay_status" => 1,
    "updateby"   => $loginid,
    "up_date"    => $createdate
];

$result = $obj->update_record("transaction_entry", $where, $form_data);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Payment status updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update payment status."
    ]);
}