<?php
include("appsession.php");

$account_id = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$product_id = isset($_REQUEST['product_id']) ? $obj->test_input($_REQUEST['product_id']) : 0;
$category_id = isset($_REQUEST['category_id']) ? $obj->test_input($_REQUEST['category_id']) : 0;
$type = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';
$unit_name = isset($_REQUEST['unit_name']) ? $obj->test_input($_REQUEST['unit_name']) : '';

$brand_id = isset($_REQUEST['brand_id']) ? $obj->test_input($_REQUEST['brand_id']) : 0;
$qty = isset($_REQUEST['qty']) ? $obj->test_input($_REQUEST['qty']) : 0;
$rate = isset($_REQUEST['rate']) ? $obj->test_input($_REQUEST['rate']) : 0;
$total_amt = isset($_REQUEST['total_amt']) ? $obj->test_input($_REQUEST['total_amt']) : 0;
$unit_id = isset($_REQUEST['unit_id']) ? $obj->test_input($_REQUEST['unit_id']) : '';

$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$tran_detail_id = isset($_REQUEST['tran_detail_id']) ? $obj->test_input($_REQUEST['tran_detail_id']) : 0;
if ($product_id > 0) {
    //check qty insert
    $count = $obj->getvalfield("transaction_details", "count(*)", "product_id='$product_id' and transaction_id ='$transaction_id' and account_id='$account_id' and tran_detail_id !='$tran_detail_id' and type='$type'");
    $from_update = ($transaction_id > 0) ? 1 : 0;
    if ($count == 0) {


        if ($tran_detail_id == 0) {
            $form_data = array(
                'unit_name' => $unit_name,
                'product_id' => $product_id,
                'unit_id' => $unit_id,
                'qty' => $qty,
                'rate' => $rate,
                'total_amt' => $total_amt,
                'category_id' => $category_id,
                'transaction_id' => $transaction_id,
                'type' => $type,
                'brand_id' => $brand_id,
                'account_id' => $account_id,
                'ipaddress' => $ipaddress,
                'createdby' => $loginid,
                'companyid' => $companyid,
                'createdate' => $createdate
            );
            // print_r($form_data);
            $obj->insert_record("transaction_details", $form_data);
            echo "1";
        } else {
            $form_data = array(
                'product_id' => $product_id,
                'unit_id' => $unit_id,
                'qty' => $qty,
                'rate' => $rate,
                'total_amt' => $total_amt,
                'category_id' => $category_id,
                'transaction_id' => $transaction_id,
                'type' => $type,
                'brand_id' => $brand_id,
                'account_id' => $account_id,
                'ipaddress' => $ipaddress,
                'companyid' => $companyid,
                'createdby' => $loginid,
                'lastupdated' => $createdate
            );

            $obj->update_record("transaction_details", ['tran_detail_id' => $tran_detail_id], $form_data);
            echo "2";
        }
    } else {
        echo 3;
    }
}
