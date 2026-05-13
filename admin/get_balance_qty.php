<?php
include("../adminsession.php");

$tran_detail_id = $_POST['tran_detail_id'];
$order_qty      = $_POST['order_qty'];

$dispatch_qty = $obj->getvalfield(
    "dispatch_history",
    "ifnull(sum(qty),0)",
    "tran_detail_id='$tran_detail_id'"
);

$balance = $order_qty - $dispatch_qty;

echo $balance;
