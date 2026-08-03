<?php
include("../adminsession.php");

$tran_detail_id = isset($_POST['tran_detail_id']) ? $obj->test_input($_POST['tran_detail_id']) : 0;
$product_id     = isset($_POST['product_id']) ? $obj->test_input($_POST['product_id']) : 0;
$dispatch_qty   = isset($_POST['dispatch_qty']) ? floatval($_POST['dispatch_qty']) : 0;
$action_date    = isset($_POST['action_date']) ? $obj->test_input($_POST['action_date']) : date('Y-m-d');
$transaction_id = isset($_POST['transaction_id']) ? $obj->test_input($_POST['transaction_id']) : 0;
$account_id     = isset($_POST['account_id']) ? $obj->test_input($_POST['account_id']) : 0;
$action         = isset($_POST['action']) ? $obj->test_input($_POST['action']) : 'dispatch';
$remarks        = isset($_POST['remarks']) ? trim($obj->test_input($_POST['remarks'])) : '';

$order_qty = (float)$obj->getvalfield(
    "transaction_details",
    "qty",
    "tran_detail_id='$tran_detail_id'"
);

$already_dispatch = (float)$obj->getvalfield(
    "dispatch_history",
    "IFNULL(SUM(qty),0)",
    "tran_detail_id='$tran_detail_id'"
);

$already_canceled = (float)$obj->getvalfield(
    "cancel_history",
    "IFNULL(SUM(qty),0)",
    "tran_detail_id='$tran_detail_id'"
);

$balance_qty = $order_qty - $already_dispatch - $already_canceled;

if ($dispatch_qty <= 0) {
    echo 2;
    exit;
}

if ($dispatch_qty > $balance_qty) {
    echo 3;
    exit;
}

if ($action == "dispatch") {

    $arr = array(
        "tran_detail_id" => $tran_detail_id,
        "transaction_id" => $transaction_id,
        "account_id"     => $account_id,
        "product_id"     => $product_id,
        "qty"            => $dispatch_qty,
        "dispatch_date"  => $action_date,
        "remarks"        => $remarks,
        "createdby"      => $_SESSION['userid'],
        "ipaddress"      => $ipaddress,
        "companyid"      => $companyid,
        "sessionid"      => $sessionid,
        "createdate"     => date('Y-m-d H:i:s')
    );

    $ins = $obj->insert_record("dispatch_history", $arr);
} elseif ($action == "cancel") {

    $arr = array(
        "tran_detail_id" => $tran_detail_id,
        "transaction_id" => $transaction_id,
        "account_id"     => $account_id,
        "product_id"     => $product_id,
        "qty"            => $dispatch_qty,
        "dispatch_date"  => $action_date,
        "remarks"        => $remarks,
        "createdby"      => $_SESSION['userid'],
        "ipaddress"      => $ipaddress,
        "companyid"      => $companyid,
        "sessionid"      => $sessionid,
        "createdate"     => date('Y-m-d H:i:s')
    );

    $ins = $obj->insert_record("cancel_history", $arr);
} else {
    echo 0;
    exit;
}

if ($ins) {

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

    $obj->executequery("
        UPDATE transaction_details
        SET is_dispatched='$status'
        WHERE tran_detail_id='$tran_detail_id'
    ");

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
    $obj->recalculateTransaction($transaction_id);
    echo 1;
} else {

    echo 0;
}
