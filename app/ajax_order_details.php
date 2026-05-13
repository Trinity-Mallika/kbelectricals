<?php
include("appsession.php");
if (isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];
    $res = $obj->select_record("transaction_entry", ['transaction_id' => $transaction_id]);
    $account_name = $obj->getvalfield("account", "account_name", "account_id='{$res['account_id']}'");
}
?>
<table class="table table-sm table-borderless m-2">
    <tr>
        <td>Customer Name</td>
        <td>: &nbsp; <?php echo $account_name; ?></td>
    </tr>
    <tr>
        <td>Bill No.</td>
        <td>: &nbsp; <?php echo $res['billno']; ?></td>
    </tr>
    <tr>
        <td>Bill Date</td>
        <td>: &nbsp; <?php echo $obj->dateformatindia($res['billdate']); ?></td>
    </tr>

    <tr>
        <td>Remark</td>
        <td>: &nbsp; <?php echo $res['remark']; ?></td>
    </tr>

    <tr>
        <th><i class="bi bi-geo-alt"></i> Location</th>
        <td>
            <?php echo $res['address']; ?><br>
            <?php if ($res['latitude'] != '') { ?>
                <a target="_blank" class="btn btn-sm btn-primary mt-1"
                    href="https://www.google.com/maps?q=<?php echo $res['latitude']; ?>,<?php echo $res['longitude']; ?>">
                    View Map
                </a>
            <?php } ?>
        </td>
    </tr>
</table>
<?php
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
WHERE td.transaction_id = '$transaction_id' AND td.account_id='{$res['account_id']}' AND td.type='order'
ORDER BY td.tran_detail_id DESC
";
$res = $obj->executequery($sql);
foreach ($res as $key) { ?>

    <div class="col-lg-12 col-12">

        <div class="card border-0 shadow-lg m-2 p-2">
            <table class="table table-sm table-borderless mb-0 align-middle">
                <tr>
                    <th>
                        <span class="text-blue"><?php echo $key['product_name'] ?> (<?php echo $key['category_name'] ?>)</span><br>
                        <small class="fw-lighter">Brand : <?php echo $key['brand_name'] ?>, Unit : <?php echo $key['unit_name'] ?>, Qty : <?php echo $key['qty'] ?>, MRP : <?php echo $key['rate'] ?>, Total Amount : <?php echo $key['total_amt'] ?></small>
                    </th>

                </tr>
            </table>

        </div>

    </div>
<?php } ?>