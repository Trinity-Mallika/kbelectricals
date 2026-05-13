<?php
include("../adminsession.php");
$product_id = isset($_POST['product_id']) ? $obj->test_input($_POST['product_id']) : '';
$brand_id = isset($_POST['brand_id']) ? $obj->test_input($_POST['brand_id']) : '';
$unit_id = isset($_POST['unit_id']) ? $obj->test_input($_POST['unit_id']) : '';

$rate = $obj->getvalfield("brand_wise_rate_setting", "rate", "product_id='$product_id' AND brand_id='$brand_id' AND unit_id='$unit_id'");
echo $rate;
