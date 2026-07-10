<?php
include("../adminsession.php");
$grand_total = 0;
$account_id = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$company_id = isset($_REQUEST['company_id']) ? $obj->test_input($_REQUEST['company_id']) : 0;
$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$type = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';
$btn_name = ($obj->test_input($_REQUEST['transaction_id'])) ? 'Update' : 'Save';
$currentMode = ($obj->test_input($_REQUEST['currentMode'])) ? $_REQUEST['currentMode'] : '';
if ($transaction_id > 0) {
    $trasc_data = $obj->select_record("transaction_entry", array("transaction_id" => $transaction_id));
    $cgst = $trasc_data['cgst'];
    $sgst = $trasc_data['sgst'];
    $gst_percent = $trasc_data['gst_percent'];
    $grand_total = $trasc_data['grand_total'];
    $net_total_amt = $trasc_data['net_total_amt'];
     $freight_charges = $trasc_data['freight_charges'];
} else {
    $gst_percent = 0;
        $freight_charges = 0;
}
?>
<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead>
            <th class="text-center">S. No.</th>
            <th>Category/Product Name</th>
            <th>Brand</th>
            <th>Unit</th>
            <th class="text-end">Rate</th>
            <th>Qty</th>
            <th>Discount</th>
            <th class="text-end">Price After Disc.</th>
            <th>GST</th>
            <th>TaxType</th>
            <th class="text-end">Total Amount</th>
            <th class="text-center">Action</th>
        </thead>
        <tbody>
            <?php
            $has_product_gst = false;
            $cgst = 0;
            $sgst = $gst_total = 0;
            $i = 1;
            $net_total_amt = 0;
            $crit = "WHERE td.transaction_id = '$transaction_id' and td.account_id='$account_id' AND td.type='$type'";

            if ($transaction_id > 0) {
                $crit .= " ";
            } else {
                $crit .= " AND td.createdby='$loginid'"; // or $createdby variable
            }
            $sql = "SELECT td.*,p.product_name,b.cat_name AS brand_name,u.cat_name AS unit_name,c.cat_name AS category_name FROM transaction_details td
LEFT JOIN product_master p ON p.product_id = td.product_id LEFT JOIN category_master b ON b.cat_id = td.brand_id AND b.type='brand' LEFT JOIN category_master c
    ON c.cat_id = td.category_id AND c.type='category' LEFT JOIN category_master u ON u.cat_id = td.unit_id AND u.type='unit' $crit  ORDER BY td.tran_detail_id DESC
";
            $res = $obj->executequery($sql);
            $count = count($res);
            if ($count > 0) {
                foreach ($res as $key) {
                    $gst_id = $key['gst_id'];
                    $sub_total   = (float)$key['sub_total'];
                    $gst_name = $obj->getvalfield("gst_master", "gst_name", "gst_id='$gst_id'");
                    $gst_percent = $obj->getvalfield("gst_master", "gst_percent", "gst_id='{$key['gst_id']}'");
            ?>
                    <tr data-subtotal="<?= $sub_total ?>" data-gst-percent="<?= $gst_percent ?>">
                        <td class="text-center"><?php echo $i++ ?>.</td>
                        <td><b><?php echo $key['category_name'] ?></b><br><?php echo $key['product_name'] ?></td>
                        <td><?php echo $key['brand_name'] ?></td>
                        <td><?php echo $key['unit_name'] ?></td>
                        <td class="text-end">Rs. <?php echo $key['rate'] ?></td>
                        <td><?php echo $key['qty'] ?></td>
                        <td><?php
                            echo (floor($key['discount']) == $key['discount'])
                                ? (int)$key['discount'] . ' %'
                                : $key['discount'] . ' %';
                            ?></td>
                        <td class="text-end">Rs. <?php echo $key['price_after_disc'] ?></td>
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
                            Rs. <?php echo number_format($key['net_amt'], 2); ?>
                        </td>
                        <td class="text-center"><a class="btn btn-success btn-sm" onclick="EditProduct(
                    '<?php echo $key['brand_id'] ?>',
                    '<?php echo $key['category_id'] ?>',
                    '<?php echo $key['product_id'] ?>',
                    '<?php echo $key['unit_id'] ?>',
                    '<?php echo $key['unit_name'] ?>',
                    '<?php echo $key['qty'] ?>',
                    '<?php echo $key['rate'] ?>',
                    '<?php echo $key['sub_total'] ?>',
                    '<?php echo $key['discount'] ?>',
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
                } ?>

        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" class="text-end">Net Total</th>
                <th class="text-end">Rs. <?php echo number_format(round($net_total_amt), 2); ?></th>
                <th></th>
            </tr>
            <?php if ($currentMode == "overall") { ?>
                <input type="hidden" name="gst_percent" id="gst_percent_hidden" value="18">
                    <input type="hidden" name="overall_gst_amt" value="<?php echo $gst_total; ?>">
                <tr>
                    <th colspan="10" class="text-end">Freight Charges</th>
                    <th class="text-end d-flex justify-content-end gap-1 align-items-center"><span>Rs. </span> <input type="number" class="form-control form-control-sm w-50 text-end" name="freight_charges" id="freight_charges" value="<?= $freight_charges; ?>" oninput="calculateGST();"></th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="10" class="text-end">Taxable Amount</th>
                    <th class="text-end">
                        <span id="taxable_amount_display">0.00</span>
                         <input type="hidden" name="taxable_amount" id="taxable_amount">
                    </th>
                </tr>
                <tr>
                    <th colspan="10" class="text-end">SGST @ 9%</th>
                    <th class="text-end" id="cgst_display">Rs. </th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="10" class="text-end">CGST @ 9%</th>
                    <th class="text-end" id="sgst_display">Rs. </th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="10" class="text-end">Grand Total(inc. GST)</th>
                    <th class="text-end" id="grand_total_display">Rs. </th>
                    <th></th>
                </tr>
            <?php } ?>
        </tfoot>
    <?php } ?>
    </table>
</div>
<div class="col-md-2 m-2 ">
    <input type="submit" onclick="return checkinputmaster('account_id,billno,billdate')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" <?= ($count > 0) ? "" : "disabled" ?>>
    <input type="hidden" name="net_total_amt" id="net_total_amt" value="<?php echo $net_total_amt; ?>">
    <input type="hidden" name="cgst" value="<?php echo $cgst; ?>">
    <input type="hidden" name="sgst" value="<?php echo $sgst; ?>">

    <input type="hidden" name="grand_total" id="grand_total" value="<?php echo $net_total_amt; ?>">

    <input type="hidden" name="transaction_id" id="transaction_id" value="<?php echo $transaction_id; ?>">
    <a href="order-entry.php" class="btn btn-danger btn-sm"> Reset </a>
</div>