<?php
include("appsession.php");

$account_id = $_REQUEST['account_id'];
$month      = $_REQUEST['month'];
$year       = $_REQUEST['year'];
$target_id  = $_REQUEST['target_id1'];


$sql = "
        SELECT 
            td.*,
            cm.cat_name AS brand_name

        FROM monthly_target_details td

        LEFT JOIN category_master cm
            ON cm.cat_id=td.brand_id

        WHERE td.target_id='$target_id' and  td.account_id='$account_id'

    ";

$details = $obj->executequery($sql);

$total = 0;



foreach ($details as $row) {
    $total += $row['target'];
?>

    <tr>

        <td><?php echo $row['brand_name'] ?></td>

        <td><?php echo $row['target'] ?></td>

        <td class="text-center">

            <button type="button"
                class="btn btn-red btn-sm"

                onclick="delete_target('<?php echo $row['target_details_id'] ?>')">

                Del

            </button>

        </td>

    </tr>

<?php } ?>

<tr>

    <th>Total</th>

    <th><?php echo $total ?></th>
    <input type="hidden" id="total_target" value="<?php echo $total ?>">
    <th></th>

</tr>