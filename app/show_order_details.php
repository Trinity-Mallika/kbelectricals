<?php
include("appsession.php");

$account_id     = isset($_REQUEST['account_id'])     ? $obj->test_input($_REQUEST['account_id'])     : 0;
$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$type           = isset($_REQUEST['type'])           ? $obj->test_input($_REQUEST['type'])           : '';
$btn_name       = ($transaction_id) ? 'Update' : 'Save';

$sql = "
    SELECT 
        td.*,
        p.product_name,
        b.cat_name AS brand_name,
        u.cat_name AS unit_name,
        c.cat_name AS category_name
    FROM transaction_details td
    LEFT JOIN product_master p  ON p.product_id  = td.product_id
    LEFT JOIN category_master b ON b.cat_id = td.brand_id     AND b.type = 'brand'
    LEFT JOIN category_master c ON c.cat_id = td.category_id  AND c.type = 'category'
    LEFT JOIN category_master u ON u.cat_id = td.unit_id      AND u.type = 'unit'
    WHERE td.transaction_id = '$transaction_id'
      AND td.account_id = '$account_id'
      AND td.type = '$type'
    ORDER BY td.tran_detail_id DESC
";
$res       = $obj->executequery($sql);
$row_count = count($res);

$sub_grand = 0;
foreach ($res as $key):
    $sub_total   = (float)$key['sub_total'];
    $net_amt   = (float)$key['net_amt'];
    $disc        = (float)$key['discount'];
    $disc_amt    = (float)$key['discount_amt'];
    $gst_percent = $obj->getvalfield("gst_master", "gst_percent", "gst_id='{$key['gst_id']}'");
    $gst_amt     = (float)($key['gst_amt']     ?? 0);
    $sub_grand  += $net_amt;
?>

    <div class="col-12 mb-2" data-subtotal="<?= $sub_total ?>" data-gst-percent="<?= $gst_percent ?>">
        <div class="card border-0 shadow-sm p-2">
            <table class="table table-sm table-borderless mb-0 align-middle">
                <tr>
                    <th style="width:100%">
                        <span class="text-primary fw-semibold">
                            <?= htmlspecialchars($key['product_name']) ?>
                            <small class="fw-normal text-muted">(<?= htmlspecialchars($key['category_name']) ?>)</small>
                        </span>
                        <br>
                        <small class="text-muted">
                            Brand: <strong><?= htmlspecialchars_decode($key['brand_name']) ?></strong>
                            &nbsp;|&nbsp;
                            Unit: <strong><?= htmlspecialchars($key['unit_name']) ?></strong>
                        </small>

                        <br>
                        <small class="text-muted">
                            Qty: <strong><?= $key['qty'] ?></strong>
                            &nbsp;|&nbsp;
                            MRP: <strong>₹<?= number_format($key['rate'], 2) ?></strong>
                            <?php if ($disc > 0): ?>
                                Disc: <strong><?= $disc ?>%</strong>
                                (−₹<?= number_format($disc_amt, 2) ?>)
                                &nbsp;|&nbsp;
                                MRP after disc: <strong>₹<?= number_format($key['price_after_disc'], 2) ?></strong>
                            <?php endif; ?>

                            <?php if ($gst_percent > 0): ?>
                                &nbsp;|&nbsp;
                                GST: <strong><?= $gst_percent ?>%</strong>
                                (+₹<?= number_format($gst_amt, 2) ?>)
                            <?php endif; ?>
                        </small>
                        <br>
                        <span class="fw-bold text-success small">
                            Total: ₹<?= number_format($net_amt, 2) ?>
                        </span>
                    </th>
                    <td class="border-start ps-2 text-center" style="white-space:nowrap">
                        <a onclick="EditProduct(
                        '<?= $key['category_id'] ?>',
                        '<?= $key['product_id'] ?>',
                        '<?= $key['brand_id'] ?>',
                        '<?= $key['unit_id'] ?>',
                        '<?= htmlspecialchars($key['unit_name']) ?>',
                        '<?= $key['qty'] ?>',
                        '<?= $key['rate'] ?>',
                        '<?= $disc ?>',
                        '<?= $key['gst_id'] ?>',
                        '<?= $key['tran_detail_id'] ?>'
                    );" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>

                    <td class="text-center" style="white-space:nowrap">
                        <a onclick="delete_record('<?= $key['tran_detail_id'] ?>');"
                            class="btn btn-sm btn-danger">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>

<?php endforeach; ?>

<input type="hidden" id="grand_total_base" value="<?= $sub_grand ?>">

<div class="col-12 mb-2" id="overall_gst_row" style="display:none;">
    <div class="d-flex justify-content-between align-items-center px-2 py-1
                bg-light border rounded">
        <span class="fw-semibold"> Overall GST
            (<span id="og_pct">18</span>%)
            on ₹<?= number_format($sub_grand, 2) ?></span>
        <strong class="text-success fs-6">+₹<span id="og_amt"></span></strong>
    </div>
</div>

<!-- Grand total display row -->
<div class="col-12 mb-2">
    <div class="d-flex justify-content-between align-items-center px-2 py-1
                bg-light border rounded">
        <span class="fw-semibold">Grand Total</span>
        <strong class="text-success fs-6">₹<span id="grand_total_display"><?= number_format($sub_grand, 2) ?></span></strong>
    </div>
</div>

<!-- Hidden grand_total posted with the Save form -->
<input type="hidden" name="grand_total" id="grand_total" value="<?= $sub_grand ?>">

<!-- Save button -->
<div class="col-12 mb-3">
    <div class="card border-0 p-1">
        <button onclick="getLocationAndProceed(this)"
            class="btn btn-primary w-100 btn-sm"
            <?= ($row_count == 0) ? 'disabled' : '' ?>>
            <?= $btn_name ?> Order
        </button>
    </div>
</div>