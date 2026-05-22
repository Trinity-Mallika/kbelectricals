<?php
include("appsession.php");
$grand_total = 0;
$account_id = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$type = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';
$btn_name = ($obj->test_input($_REQUEST['transaction_id'])) ? 'Update' : 'Save';

$grand_total = 0;
$sql = "
SELECT 
    td.*,
    p.product_name,
    b.cat_name AS brand_name,
    u.cat_name AS unit_name,
    c.cat_name AS category_name
FROM transaction_details td
LEFT JOIN product_master p 
    ON p.product_id = td.product_id
LEFT JOIN category_master b 
    ON b.cat_id = td.brand_id AND b.type='brand'
    LEFT JOIN category_master c 
    ON c.cat_id = td.category_id AND c.type='category'
LEFT JOIN category_master u 
    ON u.cat_id = td.unit_id AND u.type='unit'
WHERE td.transaction_id = '$transaction_id' AND td.account_id='$account_id' AND td.type='$type'
ORDER BY td.tran_detail_id DESC
";
$res = $obj->executequery($sql);
$row_count = count($res);


foreach ($res as $key) {
?>

    <div class="col-lg-12 col-12">

        <div class="card border-0 shadow-lg mb-3 p-2">
            <table class="table table-sm table-borderless mb-0 align-middle">
                <tr>
                    <th> <span class="text-blue"><?php echo $key['product_name'] ?> (<?php echo $key['category_name'] ?>)</span><br>
                        <small class="fw-lighter">Brand : <?php echo $key['brand_name'] ?>, Unit : <?php echo $key['unit_name'] ?>, Qty : <?php echo $key['qty'] ?>, MRP : <?php echo $key['rate'] ?>, Total Amount : <?php echo $key['total_amt'] ?></small>
                    </th>
                    <td class="border-start">
                        <a onclick="EditProduct(
        '<?php echo $key['category_id'] ?>',
        '<?php echo $key['product_id'] ?>',
        '<?php echo $key['brand_id'] ?>',
        '<?php echo $key['unit_id'] ?>',
        '<?php echo $key['unit_name'] ?>',
        '<?php echo $key['qty'] ?>',
        '<?php echo $key['rate'] ?>',
        '<?php echo $key['discount'] ?>',
        '<?php echo $key['gst_id'] ?>',
        '<?php echo $key['tran_detail_id'] ?>'
    );" class="btn btn-sm btn-green">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>
                    <td>
                        <a onclick="delete_record('<?php echo $key['tran_detail_id'] ?>');" class="btn btn-sm btn-red"><i class="bi bi-trash-fill"></i></a>
                    </td>
                </tr>
            </table>

        </div>

    </div>
<?php
    $grand_total += $key['total_amt'];
} ?>
<input type="hidden" name="grand_total" id="grand_total" value="<?php echo $grand_total ?>">
<div class="col-12">
    <div class="card p-1">
        <button onclick="getLocationAndProceed(this)" class="btn btn-primary w-100 btn-sm " <?php echo ($row_count == 0) ? 'disabled' : ''; ?>>Save</button>
    </div>

</div>