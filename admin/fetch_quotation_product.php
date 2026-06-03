<?php
include("../adminsession.php");
$grand_total = 0;
$account_id = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$company_id = isset($_REQUEST['company_id']) ? $obj->test_input($_REQUEST['company_id']) : 0;
$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$type = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';
$btn_name = ($obj->test_input($_REQUEST['transaction_id'])) ? 'Update' : 'Save';

if ($transaction_id > 0) {
    $trasc_data = $obj->select_record("transaction_entry", array("transaction_id" => $transaction_id));
    $cgst = $trasc_data['cgst'];
    $sgst = $trasc_data['sgst'];
    $gst_percent = $trasc_data['gst_percent'];

    $grand_total = $trasc_data['grand_total'];
    $net_total_amt = $trasc_data['net_total_amt'];
} else {
    $gst_percent = 0;
}
?>
<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead>
            <th class="text-center">S. No.</th>
            <th>Brand</th>
            <th>Category/Product Name</th>
            <th>Unit</th>
            <th>MRP</th>
            <th>Qty</th>
            <th>Discount</th>
            <th>Price After Disc.</th>
            <th>GST</th>
            <th>TaxType</th>
            <th>Net Total</th>
            <th class="text-center">Action</th>
        </thead>
        <tbody>
            <?php
            $has_product_gst = false;
            $cgst = 0;
            $sgst = 0;
            $i = 1;
            $net_total_amt = 0;
            $sql = "SELECT td.*,p.product_name,b.cat_name AS brand_name,u.cat_name AS unit_name,c.cat_name AS category_name FROM transaction_details td
LEFT JOIN product_master p ON p.product_id = td.product_id LEFT JOIN category_master b ON b.cat_id = td.brand_id AND b.type='brand' LEFT JOIN category_master c
    ON c.cat_id = td.category_id AND c.type='category' LEFT JOIN category_master u ON u.cat_id = td.unit_id AND u.type='unit'WHERE td.transaction_id = '$transaction_id' AND td.account_id='$account_id'  AND td.type='$type' ORDER BY td.tran_detail_id DESC
";
            $res = $obj->executequery($sql);
            $count = count($res);
            if ($count > 0) {
                foreach ($res as $key) {
                    $gst_id = $key['gst_id'];
                    $gst_name = $obj->getvalfield("gst_master", "gst_name", "gst_id='$gst_id'");
            ?>
                    <tr>
                        <td class="text-center"><?php echo $i++ ?>.</td>
                        <td><?php echo $key['brand_name'] ?></td>
                        <td><b><?php echo $key['category_name'] ?></b><br><?php echo $key['product_name'] ?></td>
                        <td><?php echo $key['unit_name'] ?></td>
                        <td class="text-end"><?php echo $key['rate'] ?></td>
                        <td ><?php echo $key['qty'] ?></td>
                        <td class="text-end"><?php
                            echo (floor($key['discount']) == $key['discount'])
                                ? (int)$key['discount'] . ' %'
                                : $key['discount'] . ' %';
                            ?></td>
                        <td class="text-end"><?php echo $key['price_after_disc'] ?></td>
                        <td>
                            <?php
                            echo ($gst_name) ? $gst_name : '0';
                            ?>
                        </td>

                        <td>
                            <?php
                            echo ($gst_name) ? ucfirst($key['taxtype']) : '';
                            ?>
                        </td>

                        <td class="text-end">
                            <?php echo number_format($key['net_amt'], 2); ?>
                        </td>
                        <td><a class="btn btn-success btn-sm" onclick="EditProduct(
                    '<?php echo $key['brand_id'] ?>',
                    '<?php echo $key['category_id'] ?>',
                    '<?php echo $key['product_id'] ?>',
                    '<?php echo $key['unit_id'] ?>',
                    '<?php echo $key['unit_name'] ?>',
                    '<?php echo $key['qty'] ?>',
                    '<?php echo $key['rate'] ?>',
                    '<?php echo $key['sub_total'] ?>',
                    '<?php echo $key['discount'] ?>',
                    '<?php echo $key['delivery_status'] ?>',
                    '<?php echo $key['total_amt'] ?>',
                    '<?php echo $key['tran_detail_id'] ?>',
                    '<?php echo $key['gst_id'] ?>',
                    '<?php echo $key['taxtype'] ?>',
                   '<?php echo $key['net_amt'] ?>'
                );"><i class="bi bi-pencil"></i></a>
                            <a href="" class="btn btn-danger btn-sm" onclick="delete_record('<?php echo $key['tran_detail_id'] ?>');"><i class="bi bi-trash"></i></a>
                        </td>

                    </tr>
                <?php $net_total_amt += $key['net_amt'];
                    $cgst = $net_total_amt * 0.09;
                    $sgst = $net_total_amt * 0.09;
                    $gst_total = $cgst + $sgst;
                    $grand_total = $net_total_amt + ($net_total_amt * 0.18);
                } ?>

        </tbody>

        <tfoot>
            <tr>
                <th colspan="9" class="text-end">Net Total</th>
                <th id="net_total_display" class="text-end"><?php echo number_format(round($net_total_amt), 2); ?></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    <?php } ?>
    </table>
</div>
<div class="col-md-2 m-2 ">
    <input type="submit" onclick="return checkinputmaster('account_id,billno,billdate')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" <?= ($count > 0) ? "" : "disabled" ?>>
    <input type="hidden" name="net_total_amt" id="net_total_amt" value="<?php echo $net_total_amt; ?>">
    <input type="hidden" name="cgst" id="cgst" value="<?php echo $cgst; ?>">
    <input type="hidden" name="sgst" id="sgst" value="<?php echo $sgst; ?>">
    <input type="hidden" name="grand_total" id="grand_total" value="<?php echo $grand_total; ?>">
    <input type="hidden" name="gst_percent" id="gst_percent_hidden" value="<?php echo $gst_percent; ?>">
    <input type="hidden" name="transaction_id" id="transaction_id" value="<?php echo $transaction_id; ?>">
    <a href="quotation.php" class="btn btn-danger btn-sm"> Reset </a>
</div>