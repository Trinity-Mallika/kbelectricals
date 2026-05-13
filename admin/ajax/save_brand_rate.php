<?php
include("../../adminsession.php");

$product_id = $_POST['product_id'];
$brand_id   = $_POST['brand_id'];
$rate       = $_POST['rate'];
$unit_id       = $_POST['unit_id'];

$prod_data = $obj->select_record("product_master", array("product_id" => $product_id));
$product_name = $prod_data['product_name'];
// check record exist
$check = $obj->getvalfield(
    "brand_wise_rate_setting",
    "brand_wise_id",
    "product_id='$product_id' 
     AND brand_id='$brand_id' 
     AND unit_id='$unit_id'"
);

if ($check != '') {

    // UPDATE
    $form_data = array(
        'rate' => $rate,
        'lastupdated' => $createdate,
        'ipaddress' => $ipaddress
    );

    $where = array(
        'product_id' => $product_id,
        'brand_id' => $brand_id,
        'unit_id' => $unit_id
    );

    $obj->update_record("brand_wise_rate_setting", $where, $form_data);

    echo "updated";
} else {

    // INSERT
    $form_data = array(
        'product_id' => $product_id,
        'product_name' => $product_name,
        'unit_id' => $unit_id,
        'brand_id' => $brand_id,
        'rate' => $rate,
        'createdate' => $createdate,
        'ipaddress' => $ipaddress
    );

    $obj->insert_record("brand_wise_rate_setting", $form_data);

    echo "inserted";
}
