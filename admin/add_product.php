<?php
include("../adminsession.php");
$account_id = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$company_id = isset($_REQUEST['company_id']) ? $obj->test_input($_REQUEST['company_id']) : 0;
$ready_stock = isset($_REQUEST['ready_stock']) ? $obj->test_input($_REQUEST['ready_stock']) : 0;
$product_id = isset($_REQUEST['product_id']) ? $obj->test_input($_REQUEST['product_id']) : 0;
$delivery_status = isset($_REQUEST['delivery_status']) ? $obj->test_input($_REQUEST['delivery_status']) : 0;
$category_id = isset($_REQUEST['category_id']) ? $obj->test_input($_REQUEST['category_id']) : 0;
$gst_id  = isset($_REQUEST['gst_id']) ? $obj->test_input($_REQUEST['gst_id']) : 0;
$taxtype = isset($_REQUEST['taxtype']) ? $obj->test_input($_REQUEST['taxtype']) : "";
$net_amt = isset($_REQUEST['net_amt']) ? $obj->test_input($_REQUEST['net_amt']) : "";
$type = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';

$brand_id = isset($_REQUEST['brand_id']) ? $obj->test_input($_REQUEST['brand_id']) : 0;
$qty = isset($_REQUEST['qty']) ? $obj->test_input($_REQUEST['qty']) : 0;
$unit_id = isset($_REQUEST['unit_id']) ? $obj->test_input($_REQUEST['unit_id']) : '';
$unit_name = isset($_REQUEST['unit_name']) ? $obj->test_input($_REQUEST['unit_name']) : '';

$sub_total = isset($_REQUEST['sub_total']) ? $obj->test_input($_REQUEST['sub_total']) : 0;
$rate = isset($_REQUEST['rate']) ? $obj->test_input($_REQUEST['rate']) : 0;
$total_amt = isset($_REQUEST['total_amt']) ? $obj->test_input($_REQUEST['total_amt']) : 0;
$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$tran_detail_id = isset($_REQUEST['tran_detail_id']) ? $obj->test_input($_REQUEST['tran_detail_id']) : 0;
$discount = isset($_REQUEST['discount']) ? $obj->test_input($_REQUEST['discount']) : 0;
$discount_amt = isset($_REQUEST['discount_amt']) ? $obj->test_input($_REQUEST['discount_amt']) : 0;


$count = $obj->getvalfield("transaction_details", "count(*)", "product_id='$product_id' and transaction_id ='$transaction_id' and account_id='$account_id' and company_id='$company_id' and tran_detail_id !='$tran_detail_id' and type='$type' and createdby='$loginid'");
if ($count == 0) {
    if ($tran_detail_id == 0) {
        $form_data = array(
            'product_id' => $product_id,
            'unit_id' => $unit_id,
            'unit_name' => $unit_name,
            'qty' => $qty,
            'ready_stock' => $ready_stock,
            'delivery_status' => $delivery_status,
            'category_id' => $category_id,
            'rate' => $rate,
            'total_amt' => $total_amt,
            'transaction_id' => $transaction_id,
            'type' => $type,
            'discount' => $discount,
            'brand_id' => $brand_id,
            'sub_total' => $sub_total,
            'discount_amt' => $discount_amt,
            'account_id' => $account_id,
            'company_id' => $company_id,
            "gst_id" => $gst_id,
            "taxtype" => $taxtype,
            "net_amt" => $net_amt,
            'ipaddress' => $ipaddress,
            'createdby' => $loginid,
            "companyid" => $companyid,
            'createdate' => $createdate
        );

        $obj->insert_record("transaction_details", $form_data);
        echo "1";
    } else {
        $form_data = array(
            'product_id' => $product_id,
            'unit_id' => $unit_id,
            'unit_name' => $unit_name,
            'qty' => $qty,
            'rate' => $rate,
            'category_id' => $category_id,
            'delivery_status' => $delivery_status,
            'ready_stock' => $ready_stock,
            'total_amt' => $total_amt,
            'transaction_id' => $transaction_id,
            'type' => $type,
            'discount' => $discount,
            'brand_id' => $brand_id,
            'sub_total' => $sub_total,
            'discount_amt' => $discount_amt,
            'account_id' => $account_id,
            "gst_id" => $gst_id,
            "taxtype" => $taxtype,
            "net_amt" => $net_amt,
            'company_id' => $company_id,
            'ipaddress' => $ipaddress,
            'createdby' => $loginid,
            'lastupdated' => $createdate
        );

        $obj->update_record("transaction_details", ['tran_detail_id' => $tran_detail_id], $form_data);
        echo "2";
    }
} else {
    echo 3;
}
