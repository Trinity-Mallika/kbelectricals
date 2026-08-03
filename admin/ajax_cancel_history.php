<?php
include("../adminsession.php");

$tran_detail_id = $_POST['tran_detail_id'];

$order_qty = $obj->getvalfield(
    "transaction_details",
    "qty",
    "tran_detail_id='$tran_detail_id'"
);

$res = $obj->executequery("SELECT ch.*, u.fullname
    FROM cancel_history ch
    LEFT JOIN user u ON u.userid = ch.createdby
    WHERE ch.tran_detail_id='$tran_detail_id'
    ORDER BY ch.cancel_id DESC
");

$total = 0;
?>

<table class="table table-bordered table-sm table-hover">
    <thead>
        <tr class="table-primary">
            <th width="5%">Sr</th>
            <th>Cancel Qty</th>
            <th>Cancel Date</th>
            <th>Cancel By</th>
            <th>Remarks</th>
        </tr>
    </thead>

    <tbody>

        <?php
        if (count($res) > 0) {

            $i = 1;

            foreach ($res as $row) {

                $total += $row['qty'];
        ?>

                <tr>
                    <td><?php echo $i++ ?></td>

                    <td>
                        <?php echo $row['qty'] ?>
                    </td>

                    <td>
                        <?php echo date('d-m-Y', strtotime($row['dispatch_date'])) ?>
                    </td>

                    <td>
                        <?php echo $row['fullname'] ?>
                    </td>

                    <td>
                        <?php echo $row['remarks'] ?>
                    </td>
                </tr>

            <?php
            }
            ?>

            <tr class="bg-light">
                <th colspan="1">Total</th>

                <th>
                    <?php echo $total ?>
                </th>

                <th colspan="3">
                    Balance :
                    <?php echo ($order_qty - $total) ?>
                </th>
            </tr>

        <?php
        } else {
        ?>

            <tr>
                <td colspan="5" class="text-center text-danger">
                    No Cancel History Found
                </td>
            </tr>

        <?php } ?>

    </tbody>
</table>