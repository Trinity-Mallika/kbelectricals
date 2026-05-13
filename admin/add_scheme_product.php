<?php
include("../adminsession.php");

$product_id = isset($_REQUEST['product_id']) ? $obj->test_input($_REQUEST['product_id']) : 0;
$qty = isset($_REQUEST['qty']) ? $obj->test_input($_REQUEST['qty']) : "";
$output = isset($_REQUEST['output']) ? $obj->test_input($_REQUEST['output']) : '';
$scheme_type = isset($_REQUEST['scheme_type']) ? $obj->test_input($_REQUEST['scheme_type']) : '';

$scheme_id = isset($_REQUEST['scheme_id']) ? $obj->test_input($_REQUEST['scheme_id']) : 0;
$scheme_details_id = isset($_REQUEST['scheme_details_id']) ? $obj->test_input($_REQUEST['scheme_details_id']) : 0;


if ($scheme_details_id == 0) {
    $form_data = array(
        'product_id' => $product_id,
        'qty' => $qty,
        'output' => $output,
        'scheme_type' => $scheme_type,

        'ipaddress' => $ipaddress,
        'createdby' => $loginid,
        "companyid" => $companyid,
        'createdate' => $createdate
    );

    $obj->insert_record("scheme_details", $form_data);
    echo "1";
} else {
    $form_data = array(
        'product_id' => $product_id,
        'qty' => $qty,
        'output' => $output,
        'scheme_type' => $scheme_type,

        'scheme_id' => $scheme_id,
        'companyid' => $companyid,
        'ipaddress' => $ipaddress,
        'createdby' => $loginid,
        'lastupdated' => $createdate
    );

    $obj->update_record("scheme_details", ['scheme_details_id' => $scheme_details_id], $form_data);
    echo "2";
}
