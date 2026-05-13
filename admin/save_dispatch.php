<?php
include("../adminsession.php");

$tran_detail_id = $_POST['tran_detail_id'];
$product_id     = $_POST['product_id'];
$dispatch_qty   = $_POST['dispatch_qty'];
$dispatch_date  = $_POST['dispatch_date'];
$transaction_id  = $_POST['transaction_id'];
$account_id  = $_POST['account_id'];
$dispatch_date  = $_POST['dispatch_date'];
$remarks        = trim($_POST['remarks']);



/* ORDER QTY */
$order_qty = $obj->getvalfield(
    "transaction_details",
    "qty",
    "tran_detail_id='$tran_detail_id'"
);



/* ALREADY DISPATCHED */
$already_dispatch = $obj->getvalfield(
    "dispatch_history",
    "ifnull(sum(qty),0)",
    "tran_detail_id='$tran_detail_id'"
);



/* BALANCE */
$balance = $order_qty - $already_dispatch;



/* VALIDATION */
if ($dispatch_qty <= 0) {
    echo 2;
    exit;
}

if ($dispatch_qty > $balance) {
    echo 3;
    exit;
}



/* SAVE DISPATCH */
$arr = array(
    "tran_detail_id" => $tran_detail_id,
    "transaction_id" => $transaction_id,
    "account_id" => $account_id,
    "product_id"     => $product_id,
    "qty"            => $dispatch_qty,
    "dispatch_date"  => $dispatch_date,
    "remarks"        => $remarks,
    "createdby"     => $_SESSION['userid'],
    "createdate"   => date('Y-m-d H:i:s')
);

$ins = $obj->insert_record("dispatch_history", $arr);



/* TOTAL DISPATCH AFTER SAVE */
$total_dispatch = $already_dispatch + $dispatch_qty;



/* UPDATE STATUS */
if ($total_dispatch >= $order_qty) {

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



/* SUCCESS */
if ($ins) {
    echo 1;
} else {
    echo 0;
}
