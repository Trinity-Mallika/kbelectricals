<?php
include("../../adminsession.php");

$transaction_id = $_POST['transaction_id'];
$type = $_POST['type'];

$sql = $obj->executequery("
SELECT *
FROM quotation_followup
WHERE transaction_id='$transaction_id' and type='$type'
ORDER BY followup_id DESC
");
?>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Sr</th>
            <th>Date</th>
            <th>Remark</th>
             <th width="80">Action</th>
        </tr>
    </thead>

    <tbody>

    <?php
    $i=1;

    foreach($sql as $row)
    {
    ?>

        <tr id="row_<?php echo $row['followup_id']; ?>">
            <td><?php echo $i++;?></td>
            <td><?php echo $obj->dateformatindia($row['follow_date']);?></td>
            <td><?php echo $row['remark'];?></td>
            <td class="text-center">
                <button class="btn btn-danger btn-sm btnDeleteFollowup"
                        data-id="<?php echo $row['followup_id']; ?>">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>

    <?php } ?>

    </tbody>

</table>