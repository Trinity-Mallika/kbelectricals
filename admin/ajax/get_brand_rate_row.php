<?php
include("../../adminsession.php");

$product_id = $_POST['product_id'];
$unit_id    = $_POST['unit_id'];
$brand_id   = $_POST['brand_id'];

// Get latest rate
$rate = $obj->getvalfield(
    "brand_wise_rate_setting",
    "rate",
    "product_id='$product_id' AND brand_id='$brand_id' AND unit_id='$unit_id'"
);

// Return the TD content (input box)
?>
<input type="text"
    class="form-control form-control-sm rate-input"
    data-product_id="<?php echo $product_id; ?>"
    data-unit_id="<?php echo $unit_id; ?>"
    value="<?php echo $rate; ?>">