<?php
include("../adminsession.php");
$grand_total = 0;
$account_id = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$company_id = isset($_REQUEST['company_id']) ? $obj->test_input($_REQUEST['company_id']) : 0;
$scheme_id = isset($_REQUEST['scheme_id']) ? $obj->test_input($_REQUEST['scheme_id']) : 0;
$type = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';
$btn_name = ($obj->test_input($_REQUEST['scheme_id'])) ? 'Update' : 'Save';


?>
<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead>
            <th class="text-center">S. No.</th>
            <th>Product Name</th>
            <th>Unit</th>
            <th>Output</th>

            <th class="text-center">Action</th>
        </thead>
        <tbody>
            <?php
            $has_product_gst = false;
            $cgst = 0;
            $sgst = 0;
            $i = 1;
            $net_total_amt = 0;
            $sql = "
SELECT
    td.*,
    p.product_name
FROM scheme_details td
LEFT JOIN product_master p
    ON p.product_id = td.product_id
WHERE td.scheme_id = '$scheme_id' and td.companyid='$companyid' and td.createdby='$loginid'
ORDER BY td.scheme_details_id DESC
";
            $res = $obj->executequery($sql);
            $count = count($res);
            if ($count > 0) {
                foreach ($res as $key) {

            ?>
                    <tr>
                        <td><?php echo $i++ ?></td>

                        <td><?php echo $key['product_name'] ?></td>
                        <td> <?php
                                echo $key['qty'];

                                if ($key['scheme_type'] == 'qty_wise') {
                                    echo ' Qty';
                                } else if ($key['scheme_type'] == 'amt_wise') {
                                    echo ' Rs.';
                                }
                                ?></td>
                        <td><?php echo $key['output'] ?></td>

                        <td><a class="btn btn-success btn-sm" onclick="EditProduct(
                
                    '<?php echo $key['product_id'] ?>',
               
                    '<?php echo $key['qty'] ?>',
                    '<?php echo $key['output'] ?>',
                  
                    '<?php echo $key['scheme_details_id'] ?>',
           
                );"><i class="bi bi-pencil"></i></a>
                            <a class="btn btn-danger btn-sm" onclick="delete_record('<?php echo $key['scheme_details_id'] ?>');"><i class="bi bi-trash"></i></a>
                        </td>

                    </tr>
                <?php
                } ?>

        </tbody>


    <?php } ?>
    </table>
</div>
<div class="col-md-2 m-2 ">
    <input type="submit" onclick="return checkinputmaster('scheme_name,from_date,todate,scheme_type');" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" <?= ($count > 0) ? "" : "disabled" ?>>
    <a href="scheme_entry.php" class="btn btn-danger btn-sm"> Reset </a>
</div>