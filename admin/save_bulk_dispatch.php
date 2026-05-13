<?php
include("../adminsession.php");

$products = json_decode($_POST['products'], true);

foreach ($products as $row) {

    $tran_detail_id = $row['tran_detail_id'];
    $product_id     = $row['product_id'];
    $order_qty      = $row['qty'];



    /* ALREADY DISPATCHED QTY */
    $already_dispatch = $obj->getvalfield(
        "dispatch_history",
        "ifnull(sum(qty),0)",
        "tran_detail_id='$tran_detail_id'"
    );



    /* BALANCE QTY */
    $balance_qty = $order_qty - $already_dispatch;



    /* SKIP IF ALREADY FULLY DISPATCHED */
    if ($balance_qty <= 0) {
        continue;
    }



    /* SAVE ONLY BALANCE QTY */
    $arr = array(

        "tran_detail_id" => $tran_detail_id,
        "product_id"     => $product_id,
        "qty"            => $balance_qty,
        "dispatch_date"  => date('Y-m-d'),
        "remarks"        => 'Bulk Dispatch',
        "createdby"     => $_SESSION['userid'],
        "createdate"   => date('Y-m-d H:i:s')

    );

    $obj->insert_record("dispatch_history", $arr);



    /* FINAL TOTAL DISPATCH */
    $final_dispatch = $already_dispatch + $balance_qty;



    /* UPDATE STATUS */
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

echo 1;
