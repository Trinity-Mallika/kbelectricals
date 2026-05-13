<?php
include("../action.php");

$brand_id = isset($_POST['brand_id']) ? $obj->test_input($_POST['brand_id']) : '';
$category_id = isset($_POST['category_id']) ? $obj->test_input($_POST['category_id']) : '';

echo "<option value=''>Select Category</option>";

if ($brand_id != "") {
    $sql = "SELECT * FROM category_master 
            WHERE type='category' AND brand_id='$brand_id'";

    $res = $obj->executequery($sql);

    foreach ($res as $key) {
        if ($key['cat_id'] == $category_id) {
            $selected = "selected";
        } else {
            $selected = "";
        }

        echo "<option value='" . $key['cat_id'] . "' $selected>" . $key['cat_name'] . "</option>";
    }
}
