<?php
include("../adminsession.php");

$products = json_decode($_POST['products'], true);
$transaction_id = $_POST['transaction_id'];
$account_id     = $_POST['account_id'];
$action         = isset($_POST['action']) ? $_POST['action'] : 'dispatch';

foreach ($products as $row) {

    $tran_detail_id = $row['tran_detail_id'];
    $product_id     = $row['product_id'];
    $order_qty      = $row['qty'];

    $already_dispatch = (float)$obj->getvalfield(
        "dispatch_history",
        "IFNULL(SUM(qty),0)",
        "tran_detail_id='$tran_detail_id'"
    );

    $already_cancel = (float)$obj->getvalfield(
        "cancel_history",
        "IFNULL(SUM(qty),0)",
        "tran_detail_id='$tran_detail_id'"
    );

    $balance_qty = $order_qty - $already_dispatch - $already_cancel;

    if ($balance_qty <= 0) {
        continue;
    }

    $arr = array(
        "tran_detail_id" => $tran_detail_id,
        "transaction_id" => $transaction_id,
        "account_id"     => $account_id,
        "product_id"     => $product_id,
        "qty"            => $balance_qty,
        "dispatch_date"  => date('Y-m-d'),
        "remarks"        => "Bulk " . ucfirst($action),
        "createdby"      => $_SESSION['userid'],
        "ipaddress"      => $ipaddress,
        "companyid"      => $companyid,
        "sessionid"      => $sessionid,
        "createdate"     => date('Y-m-d H:i:s')
    );

    if ($action == "dispatch") {
        $obj->insert_record("dispatch_history", $arr);
    } else {
        $obj->insert_record("cancel_history", $arr);
        $obj->recalculateTransaction($transaction_id);
    }

    $total_dispatch = (float)$obj->getvalfield(
        "dispatch_history",
        "IFNULL(SUM(qty),0)",
        "tran_detail_id='$tran_detail_id'"
    );

    $total_cancel = (float)$obj->getvalfield(
        "cancel_history",
        "IFNULL(SUM(qty),0)",
        "tran_detail_id='$tran_detail_id'"
    );

    $completed_qty = $total_dispatch + $total_cancel;
    if ($completed_qty >= $order_qty) {
        if ($total_dispatch == $order_qty) {
            $status = 1;
        } elseif ($total_cancel == $order_qty) {
            $status = 2;
        } else {
            $status = 3;
        }
    } else {
        $status = 0;
    }

    $obj->executequery("UPDATE transaction_details SET is_dispatched='$status' WHERE tran_detail_id='$tran_detail_id'");
}

// Update transaction status
$pending_count = $obj->getvalfield(
    "transaction_details td",
    "COUNT(*)",
    "td.transaction_id='$transaction_id'
     AND td.qty >
     (
        IFNULL((SELECT SUM(qty)
                FROM dispatch_history dh
                WHERE dh.tran_detail_id=td.tran_detail_id),0)
        +
        IFNULL((SELECT SUM(qty)
                FROM cancel_history ch
                WHERE ch.tran_detail_id=td.tran_detail_id),0)
     )"
);

$dispatch_status = ($pending_count == 0) ? 1 : 0;

$obj->executequery("
    UPDATE transaction_entry
    SET dispatch_status='$dispatch_status'
    WHERE transaction_id='$transaction_id'
");

echo 1;
