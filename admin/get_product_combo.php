<?php include("../action.php");
$brand_id = isset($_POST['brand_id']) ? $obj->test_input($_POST['brand_id']) : '';
$category_id = isset($_POST['category_id']) ? $obj->test_input($_POST['category_id']) : '';
$product_id = isset($_POST['product_id']) ? $obj->test_input($_POST['product_id']) : '';

$sql = "SELECT * FROM product_master 
        WHERE category_id = '$category_id' 
        AND brand_id = '$brand_id'
        ORDER BY product_name ASC";
$res = $obj->executequery($sql);
echo "<option value=''>Select</option>";
foreach ($res as $key) {
    if ($key['product_id'] == $product_id) {
        $selected = "selected";
    } else {
        $selected = "";
    }
    echo "<option value='{$key['product_id']}' $selected>{$key['product_name']}</option>";
}
