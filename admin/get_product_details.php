<?php
include("../action.php");

$product_id = isset($_POST['product_id']) ? $obj->test_input($_POST['product_id']) : '';

$response = [];

if ($product_id != '') {

    $sql = "SELECT * FROM product_master WHERE product_id = '$product_id'";
    $res = $obj->executequery($sql);

    if (!empty($res)) {
        $row = $res[0];

        $unit_id = $row['unit_id'];
          $unit_name = $obj->getvalfield("category_master", "cat_name", "cat_id='$unit_id'");

        $response = [
            'status' => 'success',
            'rate' => $row['rate'],
            'unit_id' => $unit_id,
            'unit_name' => $unit_name
        ];
    } else {
        $response['status'] = 'not_found';
    }
} else {
    $response['status'] = 'error';
}

echo json_encode($response);
