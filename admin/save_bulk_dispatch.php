<?php
include("../adminsession.php");

$products = json_decode($_POST['products'], true);
$transaction_id = $_POST['transaction_id']; 

foreach ($products as $row) {

    $tran_detail_id = $row['tran_detail_id'];
    $product_id     = $row['product_id'];
    $order_qty      = $row['qty'];

    $already_dispatch = $obj->getvalfield(
        "dispatch_history",
        "IFNULL(SUM(qty),0)",
        "tran_detail_id='$tran_detail_id'"
    );

    $balance_qty = $order_qty - $already_dispatch;

    if ($balance_qty <= 0) {
        continue;
    }

    $arr = array(
        "tran_detail_id" => $tran_detail_id,
        "product_id"     => $product_id,
        "qty"            => $balance_qty,
        "dispatch_date"  => date('Y-m-d'),
        "remarks"        => 'Bulk Dispatch',
        "createdby"      => $_SESSION['userid'],
        "createdate"     => date('Y-m-d H:i:s')
    );

    $obj->insert_record("dispatch_history", $arr);

    $final_dispatch = $already_dispatch + $balance_qty;

    if ($final_dispatch >= $order_qty) {

        $obj->executequery("
            UPDATE transaction_details
            SET is_dispatched='1'
            WHERE tran_detail_id='$tran_detail_id'
        ");

    } else {

        $obj->executequery("
            UPDATE transaction_details
            SET is_dispatched='0'
            WHERE tran_detail_id='$tran_detail_id'
        ");
    }
}

$pending_count = $obj->getvalfield(
    "transaction_details",
    "COUNT(*)",
    "transaction_id='$transaction_id' AND is_dispatched='0'"
);

if ($pending_count == 0) {
    $obj->executequery("
        UPDATE transaction_entry
        SET dispatch_status='1'
        WHERE transaction_id='$transaction_id'
    ");

} else {
    $obj->executequery("
        UPDATE transaction_entry
        SET dispatch_status='0'
        WHERE transaction_id='$transaction_id'
    ");
}

echo 1;