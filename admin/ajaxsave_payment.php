<?php
include("../adminsession.php");

$keyvalue   = $obj->test_input($_POST['transaction_id_m']);
$account_id = $obj->test_input($_POST['account_id_m']);
$bill_id = $obj->test_input($_POST['bill_id_m']);
$pay_type = ($bill_id == "opening") ? "$bill_id" : "bill";
$paymode    = $obj->test_input($_POST['paymode_m']);
$paydate    = $obj->test_input($_POST['paydate_m']);
$pay_amt    = $obj->test_input($_POST['pay_amt_m']);
$cash_disc    = $obj->test_input($_POST['cash_disc_m']);
$voucher_no = isset($_POST['voucher_no_m']) ? $obj->test_input($_POST['voucher_no_m']) : '';
$trans_id = isset($_POST['trans_id_m']) ? $obj->test_input($_POST['trans_id_m']) : '';
$bank_id = isset($_POST['bank_id_m']) ? $obj->test_input($_POST['bank_id_m']) : '';
$old_payment_proof = isset($_POST['old_payment_proof'])
    ? $obj->test_input($_POST['old_payment_proof'])
    : '';

$filename = '';
$imgpath = __DIR__ . "/uploaded/payment_proof/";
if ($account_id == "" || $paymode == "" || $paydate == "" || $pay_amt == "") {
    echo "error";
    exit;
}

$filename = $old_payment_proof;

if ($paymode != 'Cash') {

    if (!empty($_FILES["payment_proof"]['name'])) {

        $filename = $obj->uploadImage($imgpath, $_FILES["payment_proof"]);

        if ($filename != "" && $old_payment_proof != "" && file_exists($imgpath . $old_payment_proof)) {
            unlink($imgpath . $old_payment_proof);
        }
    }
} else {

    if ($old_payment_proof != "" && file_exists($imgpath . $old_payment_proof)) {
        unlink($imgpath . $old_payment_proof);
    }

    $filename = "";
}
$form_data = array(
    'account_id'    => $account_id,
    'ref_bill_id' => $bill_id,
    'paymode'       => $paymode,
    'imgname'       => $filename,
    'billdate'      => $paydate,
    'grand_total'   => $pay_amt,
    'billno'        => $voucher_no,
    'trans_id'      => $trans_id,
    'bank_id'      => $bank_id,
    'type'          => 'payment',
    'cash_disc'      => $cash_disc,
    'pay_type'      => $pay_type,
    'companyid'     => $companyid,
    'ipaddress'     => $ipaddress
);


$form_data['lastupdated'] = $createdate;
$obj->update_record("transaction_entry", ["transaction_id" => $keyvalue], $form_data);
echo json_encode([
    'status' => 'success',
    'message' => 'Payment updated successfully.'
]);
