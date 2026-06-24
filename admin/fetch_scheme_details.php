<?php
include("../adminsession.php");
$account_id = $obj->test_input($_REQUEST['account_id'] ?? 0);
$scheme_id  = $obj->test_input($_REQUEST['scheme_id'] ?? 0);
$type       = $obj->test_input($_REQUEST['type'] ?? '');

$btn_name = $scheme_id ? 'Update' : 'Save';

$sql = "
SELECT
    td.*,
    p.product_name
FROM scheme_details td
LEFT JOIN product_master p ON p.product_id = td.product_id
WHERE td.scheme_id = '$scheme_id'
  AND td.companyid = '$companyid'
  AND td.createdby = '$loginid'
ORDER BY td.scheme_details_id DESC
";

$rows  = $obj->executequery($sql);
$count = count($rows);

function e($val)
{
    return htmlspecialchars($val ?? '');
}

function formatUnit($qty, $type)
{
    return $type === 'qty_wise'
        ? "$qty Qty"
        : ($type === 'amt_wise' ? "$qty Rs." : $qty);
}
?>

<div class="table-responsive">
    <table class="table table-bordered table-sm table-hover">
        <thead>
            <tr>
                <th class="text-center">S. No.</th>
                <th>Product Name</th>
                <th>Unit</th>
                <th>Output</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($count > 0): ?>
                <?php $i = 1;
                foreach ($rows as $row): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>

                        <td><?= e($row['product_name']) ?></td>

                        <td><?= formatUnit($row['qty'], $row['scheme_type']) ?></td>

                        <td><?= e($row['output']) ?></td>

                        <td class="text-center">
                            <button
                                class="btn btn-success btn-sm"
                                onclick="EditProduct(
                                '<?= e($row['product_id']) ?>',
                                '<?= e($row['qty']) ?>',
                                '<?= e($row['output']) ?>',
                                '<?= e($row['scheme_details_id']) ?>'
                            )">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button
                                class="btn btn-danger btn-sm"
                                onclick="delete_record('<?= e($row['scheme_details_id']) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No records found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="col-md-2 m-2">
    <input
        type="submit"
        name="submit"
        value="<?= $btn_name ?>"
        onclick="return checkinputmaster('scheme_name,from_date,todate,scheme_type');"
        class="btn btn-theme btn-sm"
        <?= $count ? "" : "disabled" ?>>

    <a href="scheme_entry.php" class="btn btn-danger btn-sm">Reset</a>
</div>