<?php include("appsession.php");

$p_brand_id     = isset($_REQUEST['p_brand_id'])     ? $obj->test_input($_REQUEST['p_brand_id'])     : 0;
$p_category_id  = isset($_REQUEST['p_category_id'])  ? $obj->test_input($_REQUEST['p_category_id'])  : 0;
$p_product_name = isset($_REQUEST['p_product_name']) ? $obj->test_input($_REQUEST['p_product_name']) : '';
$p_unit_id      = isset($_REQUEST['p_unit_id'])      ? $obj->test_input($_REQUEST['p_unit_id'])      : 0;
$p_mrp          = isset($_REQUEST['p_mrp'])          ? $obj->test_input($_REQUEST['p_mrp'])          : 0;


if ($p_brand_id && $p_category_id && $p_product_name && $p_unit_id && $p_mrp) {

    $form_data = array(
        'product_name' => $p_product_name,
        'brand_id'     => $p_brand_id,
        'category_id'  => $p_category_id,
        'unit_id'      => $p_unit_id,
        'rate'         => $p_mrp,          
        'ipaddress'    => $ipaddress,
        'createdate'   => $createdate,
        'createdby'    => $loginid,
        'companyid'    => $companyid
    );

    $last_id = $obj->insert_record_lastid("product_master", $form_data);

    $sql = "SELECT * FROM product_master 
            WHERE category_id = '$p_category_id' 
            AND brand_id = '$p_brand_id'
            ORDER BY product_name ASC";
    $res = $obj->executequery($sql);

    echo "<option value=''>Select Product</option>";
    foreach ($res as $key) {
        $selected = ($key['product_id'] == $last_id) ? "selected" : "";
        echo "<option value='{$key['product_id']}' $selected>{$key['product_name']}</option>";
    }

} else {
    echo "<option value=''>Missing required fields</option>";
}