<?php include("../adminsession.php");
$title = "Order View";
$pagename = "order_view.php";
$module = "Order View";
$submodule = "Order View";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$transaction_id = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$qry = $obj->executequery("
    SELECT t.*, 
           a.account_name, 
           a.mobile_no, 
           u.fullname, 
           SUM(td.qty) AS total_qty
    FROM $tblname t
    LEFT JOIN transaction_details td 
        ON t.transaction_id = td.transaction_id
    LEFT JOIN account a 
        ON a.account_id = t.account_id
    LEFT JOIN user u 
        ON u.userid = t.createdby
    WHERE t.transaction_id = '$transaction_id'
    GROUP BY t.transaction_id
    ORDER BY t.$tblpkey DESC
");
if (!empty($qry)) {

    $sqledit = $qry[0];

    $account_name = $sqledit['account_name'];
    $account_id = $sqledit['account_id'];
    $mobile_no = $sqledit['mobile_no'];
    $remark     = $sqledit['remark'];
    $billdate   = $sqledit['billdate'];
    $billno     = $sqledit['billno'];
    $total_qty = $sqledit['total_qty'];
    $grand_total = $sqledit['grand_total'];
    $invoice_no = $sqledit['invoice_no'];
    $invoice_amt = $sqledit['invoice_amt'];
    $dispatch_status = $sqledit['dispatch_status'];
    $updateby = $sqledit['updateby'];
    $up_date = $sqledit['up_date'];
    $is_gst      = $sqledit['is_gst'];
}


if (isset($_REQUEST['order_trans_id'])) {
    $order_trans_id = $_REQUEST['order_trans_id'];
    $obj->update_record("$tblname", ['transaction_id' => $order_trans_id], ['is_approved' => 1, 'approve_date' => date('Y-m-d')]);
    echo 1;
    die;
}
if (isset($_REQUEST['dis_trans_details_id'])) {
    $dis_trans_details_id  = $_REQUEST['dis_trans_details_id'];
    $obj->update_record("transaction_details", ['tran_detail_id' => $dis_trans_details_id], ['is_dispatched' => 1, 'dispatch_date' => date('Y-m-d')]);
    echo 1;
    die;
}
?>

<!DOCTYPE html>

<html lang="en">



<head>

    <!-- meta tag -->

    <?php include('component/css.php'); ?>

    <!-- meta tag -->
</head>

<body class="bg-light">
    <!-- Sidebar -->

    <?php include('component/sidebar.php'); ?>

    <!-- Sidebar Close-->

    <div class="main w-auto">

        <!-- Header -->

        <?php include('component/header.php'); ?>

        <!-- Header Close-->

        <!-- Content -->

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">

                    <fieldset class="mt-2">

                        <legend class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5"><?= $module ?></span>

                            <div>
                                <?php if ($sqledit['is_approved'] == 1) { ?>
                                    <span class="badge bg-success px-3 py-2 me-2">Approved</span>
                                <?php } else { ?>
                                    <a href="order-entry.php?transaction_id=<?php echo $transaction_id; ?>" title="Edit" class="btn btn-sm btn-success me-2"> <i class="bi bi-pencil-square"></i> Edit </a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger me-2"
                                        onclick="funDel('<?= $transaction_id; ?>','<?= $transaction_id; ?>');">
                                        <i class="bi bi-trash3-fill"></i> Delete
                                    </button>
                                    <span class="badge bg-warning px-3 py-2 text-dark me-2" style="cursor: pointer;" onclick="order_approve('<?= $transaction_id; ?>')">Click to approve order</span>
                                <?php } ?>
                                <a href="order_list.php" class="btn btn-sm btn-danger">Back</a>
                            </div>
                        </legend>

                        <div class="card">
                            <div class="card-header text-white">
                                Order Details
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-12">
                                        <div class="card shadow-sm border-0 mb-3">
                                            <div class="card-body">

                                                <div class="row">

                                                    <div class="col-md-8">

                                                        <h4 class="mb-1">
                                                            <?= $account_name ?>
                                                        </h4>

                                                        <div class="text-muted">
                                                            <i class="bi bi-telephone"></i>
                                                            <?= $mobile_no ?>
                                                        </div>

                                                        <div class="mt-2">
                                                            <span class="badge bg-light text-dark border">
                                                                Order #<?= $billno ?>
                                                            </span>

                                                            <span class="badge bg-light text-dark border">
                                                                <?= $obj->dateformatindia($billdate) ?>
                                                            </span>

                                                            <span class="badge bg-light text-dark border">
                                                                Created By- <?= $sqledit['fullname'] ?>
                                                            </span>
                                                        </div>

                                                    </div>

                                                    <div class="col-md-4 text-end">

                                                        <div class="h3 text-success mb-0">
                                                            ₹<?= number_format($grand_total, 2) ?>
                                                        </div>

                                                        <small class="text-muted">
                                                            Order Amount
                                                        </small>

                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-1">
                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center">
                                                <small class="text-muted">Total Qty</small>
                                                <h3 class="mb-0 text-success">
                                                    <?= $total_qty ?>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center">

                                                <small class="text-muted d-block mb-2">
                                                    Invoice No.
                                                </small>

                                                <div id="invoice_display">

                                                    <?php if (!empty($invoice_no)) { ?>

                                                        <span class="badge bg-info fs-6 px-3 py-2">
                                                            <?= htmlspecialchars($invoice_no) ?>

                                                            <?php if ($sqledit['is_approved'] == 1) { ?>
                                                                <i class="bi bi-pencil-square ms-2 inv-btn"
                                                                    style="cursor:pointer"
                                                                    data-id="<?= $transaction_id ?>"
                                                                    data-order="<?= htmlspecialchars($billno) ?>"
                                                                    data-invoice_amt="<?= ($invoice_amt == 0) ? $grand_total : $invoice_amt; ?>"
                                                                    data-invoice="<?= htmlspecialchars($invoice_no) ?>">
                                                                </i>
                                                            <?php } ?>

                                                        </span>

                                                    <?php } else { ?>

                                                        <span class="badge bg-secondary fs-6 px-3 py-2">

                                                            Not Added

                                                            <?php if ($sqledit['is_approved'] == 1) { ?>
                                                                <i class="bi bi-plus-circle ms-2 inv-btn"
                                                                    style="cursor:pointer"
                                                                    data-id="<?= $transaction_id ?>"
                                                                    data-order="<?= htmlspecialchars($billno) ?>"
                                                                    data-invoice_amt="<?= ($invoice_amt == 0) ? $grand_total : $invoice_amt; ?>"
                                                                    data-invoice="">
                                                                </i>
                                                            <?php } ?>

                                                        </span>

                                                    <?php } ?>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center">
                                                <small class="text-muted">Invoice Amount</small>

                                                <h5 class="mb-0 text-primary">
                                                    ₹<?= number_format($invoice_amt, 2) ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center">

                                                <small class="text-muted">Status</small>

                                                <div class="mt-2">

                                                    <?php if ($sqledit['is_approved'] == 1) { ?>
                                                        <span class="badge bg-success fs-6">
                                                            Approved
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-warning text-dark fs-6">
                                                            Pending
                                                        </span>
                                                    <?php } ?>

                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <?php
                $total = 0;
                $sql = "SELECT 
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
WHERE td.transaction_id = '$transaction_id'  AND td.type='order'
ORDER BY td.tran_detail_id DESC";
                $i = 1;
                $res = $obj->executequery($sql);
                $row_count = count($res);
                ?>
                <div class="col-lg-12 mt-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center text-white">

                            <div>
                                <i class="bi bi-box-seam"></i>
                                Order Products
                            </div>

                            <div>
                                <span class="badge bg-light text-dark me-2">
                                    <?= $row_count ?> Items
                                </span>

                                <?php if ($sqledit['is_approved'] == 1) { ?>
                                    <?php if ($dispatch_status == 0) { ?>
                                        <button type="button"
                                            class="btn btn-sm btn-light text-primary fw-bold"
                                            onclick="bulk_dispatch()">
                                            <i class="bi bi-truck"></i>
                                            Dispatch Selected
                                        </button>
                                    <?php } ?>
                                <?php } ?>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th class="text-center">S. No.</th>
                                        <th>Brand</th>
                                        <th>Category/Product Name</th>
                                        <th>Unit</th>
                                        <th class="text-end">Rate</th>
                                        <th>Qty</th>
                                        <th>Dispatch Qty</th>
                                        <th>Discount</th>
                                        <th class="text-end">Price After Disc.</th>
                                        <?php if ($is_gst == 0) { ?>
                                            <th>GST</th>
                                        <?php } ?>
                                        <th class="text-end">Total Amount</th>
                                        <th class=""> Dispatch</th>
                                        <?php if ($dispatch_status == 0) { ?>
                                            <th width="5%" class="text-center">
                                                <input type="checkbox" id="check_all" title="Select All">
                                            </th>
                                        <?php } ?>
                                    </thead>
                                    <tbody>
                                        <?php $net_total_amt = 0;
                                        $colspan = 10;
                                        foreach ($res as $key) {
                                            $gst_id = $key['gst_id'];
                                            $sub_total   = (float)$key['sub_total'];
                                            $gst_name = $obj->getvalfield("gst_master", "gst_name", "gst_id='$gst_id'");
                                            $dispatch_qty = $obj->getvalfield("dispatch_history", "sum(qty)", "tran_detail_id='{$key['tran_detail_id']}' and transaction_id='$transaction_id'");
                                        ?>

                                            <tr>
                                                <td class="text-center"><?php echo $i++ ?>.</td>
                                                <td><?php echo $key['brand_name'] ?></td>
                                                <td><b><?php echo $key['category_name'] ?></b><br><?php echo $key['product_name'] ?></td>
                                                <td><?php echo $key['unit_name'] ?></td>
                                                <td class="text-end">Rs. <?php echo $key['rate'] ?></td>
                                                <td><?php echo $key['qty'] ?></td>
                                                <td><?php echo $dispatch_qty ?></td>
                                                <td><?php
                                                    echo (floor($key['discount']) == $key['discount'])
                                                        ? (int)$key['discount'] . ' %'
                                                        : $key['discount'] . ' %';
                                                    ?></td>
                                                <td class="text-end">Rs. <?php echo $key['price_after_disc'] ?></td>
                                                <?php if ($is_gst == 0) { ?>
                                                    <td>
                                                        <?php
                                                        echo ($gst_name) ? $gst_name : '0';
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                                <td class="text-end">
                                                    Rs. <?php echo number_format($key['net_amt'], 2); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($key['is_dispatched'] == 0) { ?>
                                                        <span class="badge bg-warning text-dark">Pending</span><br>
                                                        <?php if ($sqledit['is_approved'] == 1) { ?>
                                                            <button class="btn btn-sm btn-outline-primary mt-1"
                                                                onclick="order_dispatch('<?php echo $key['tran_detail_id'] ?>',
            '<?php echo $key['product_id'] ?>',
            '<?php echo $key['qty'] ?>',
            '<?php echo $key['product_name'] ?>')">
                                                                Dispatch
                                                            </button>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <span class="badge bg-success">Delivered</span>
                                                    <?php } ?>
                                                </td>
                                                <?php if ($dispatch_status == 0) { ?>
                                                    <td class="text-center">
                                                        <?php if ($sqledit['is_approved'] == 1) { ?>
                                                            <?php if ($key['is_dispatched'] == 0) { ?>
                                                                <input type="checkbox"
                                                                    class="dispatch_checkbox"
                                                                    data-tran_detail_id="<?php echo $key['tran_detail_id'] ?>"
                                                                    data-product_id="<?php echo $key['product_id'] ?>"
                                                                    data-qty="<?php echo $key['qty'] ?>">
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                <?php
                                                } ?>
                                            </tr>
                                        <?php $net_total_amt += $key['net_amt'];
                                            if ($is_gst == "1") {
                                                $gst_percent = 18;
                                                $cgst = ($net_total_amt * 9) / 100;
                                                $sgst = ($net_total_amt * 9) / 100;
                                                $gst_total = $cgst + $sgst;
                                                $grand_total = $net_total_amt + $gst_total;
                                            } else {
                                                $cgst = 0;
                                                $sgst = 0;
                                                $gst_total = 0;
                                                $grand_total = $net_total_amt;
                                            }
                                        } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="<?= $colspan - $is_gst ?>" class="text-end">Net Total</th>
                                            <th class="text-end">Rs. <?php echo number_format(round($net_total_amt), 2); ?></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                        <?php if ($is_gst == "1") { ?>
                                            <tr>
                                                <th colspan="<?= $colspan - $is_gst ?>" class="text-end">GST @ 18%</th>
                                                <th class="text-end">Rs. <?php echo number_format(round($gst_total), 2); ?></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                            <tr>
                                                <th colspan="<?= $colspan - $is_gst ?>" class="text-end">Grand Total</th>
                                                <th class="text-end">Rs. <?php echo number_format(round($grand_total), 2); ?></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        <?php } ?>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Content close-->
        </div>

        <div class="modal fade" id="dispatchModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-truck"></i> Product Dispatch
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="dispatchForm">
                            <input type="hidden" name="tran_detail_id" id="tran_detail_id">
                            <input type="hidden" name="product_id" id="product_id">
                            <div class="dispatch-box mb-3">
                                <div class="row g-3">

                                    <div class="col-md-8">
                                        <label class="form-label">Product</label>
                                        <input type="text" id="product_name"
                                            class="form-control" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Dispatch Date</label>
                                        <input type="date" name="dispatch_date"
                                            value="<?= date('Y-m-d') ?>"
                                            class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="dispatch-box mb-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Order Qty</label>
                                        <input type="text" id="order_qty"
                                            class="form-control text-center bg-light" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Balance Qty</label>
                                        <input type="text" id="balance_qty"
                                            class="form-control text-center bg-light" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-primary">Dispatch Qty</label>
                                        <input type="number" name="dispatch_qty"
                                            id="dispatch_qty"
                                            class="form-control text-center border-primary"
                                            placeholder="Enter Qty"
                                            required>
                                    </div>

                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks"
                                    id="remarks"
                                    class="form-control"
                                    rows="2"
                                    placeholder="Optional remarks..."></textarea>
                            </div>
                            <div class="text-center">
                                <button type="button"
                                    class="btn btn-primary px-3"
                                    onclick="save_dispatch()">
                                    <i class="bi bi-check-circle"></i> Save
                                </button>
                            </div>
                        </form>
                        <hr>
                        <h6 class="fw-bold mb-2">Dispatch History</h6>
                        <div id="dispatch_history" class="history-box"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="invoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="invoiceModalLabel">Add Invoice No. For <span id="order_ref"></span></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <strong><label for="">Invoice No.</label><span class="text-danger fw-bold">*</span></strong>
                                <input type="text" id="invoice_no" class="form-control" placeholder="Enter Invoice No." autocomplete="off">
                            </div>
                            <div class="col-lg-12">
                                <strong><label for="">Invoice Amt</label><span class="text-danger fw-bold">*</span></strong>
                                <input type="number" id="invoice_amt" class="form-control" placeholder="Enter Invoice Amt" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="transaction_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="save_invoice();">Save</button>
                    </div>
                </div>
            </div>
        </div>
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<!-- script tag -->
<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });
    $(document).on('click', '.inv-btn[data-id]', function() {
        let id = $(this).data('id');
        let order = $(this).data('order');
        let invoice = $(this).data('invoice');
        let invoice_amt = $(this).data('invoice_amt');

        add_invoice(id, order, invoice, invoice_amt);
    });

    function add_invoice(transaction_id, order_no, invoice = '', invoice_amt) {
        $('#invoiceModal').modal('show');

        $('#transaction_id').val(transaction_id);
        $('#order_ref').text(order_no);

        $('#invoice_no').val(invoice).focus();
        $('#invoice_amt').val(invoice_amt).focus();
    }

    function save_invoice() {
        let id = $('#transaction_id').val();
        let invoice = $('#invoice_no').val().trim();
        let invoice_amt = $('#invoice_amt').val().trim();

        if (invoice === '') {
            alert('Invoice No. is required');
            $('#invoice_no').focus();
            return;
        }

        $.ajax({
            url: 'save_invoice.php',
            type: 'POST',
            data: {
                transaction_id: id,
                invoice_no: invoice,
                invoice_amt: invoice_amt
            },
            beforeSend: function() {
                $('#invoiceModal .btn-primary').prop('disabled', true).text('Saving...');
            },
            success: function(res) {
                if (res == 1) {

                    $('#invoiceModal').modal('hide');

                    $('#invoice_display').html(
                        '<span class="badge bg-info px-3 py-2">' + invoice + '</span>'
                    );

                    // optional feedback
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Invoice updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });

                } else if (res == 2) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Invoice',
                        text: 'This invoice number already exists'
                    });

                    $('#invoice_no').focus();

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong'
                    });

                }
            },
            complete: function() {
                $('#invoiceModal .btn-primary').prop('disabled', false).text('Save');
            }
        });
    }
</script>
<script>
    function order_approve(transaction_id) {

        swal({
                title: "Are you sure?",
                text: "You want to approve this order!",
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((willApprove) => {

                if (willApprove) {

                    $.ajax({
                        url: "",
                        type: "POST",
                        data: {
                            order_trans_id: transaction_id
                        },
                        success: function(res) {

                            if (res == '1') {

                                swal("Approved!", "Order has been approved.", "success")
                                    .then(() => {
                                        location.reload();
                                    });

                            } else {
                                swal("Error!", "Failed to approve.", "error");
                            }

                        }
                    });

                }

            });
    }
</script>
<script>
    function order_dispatch(tran_detail_id, product_id, qty, product_name) {
        $("#tran_detail_id").val(tran_detail_id);
        $("#product_id").val(product_id);
        $("#order_qty").val(qty);
        $("#product_name").val(product_name);

        $("#dispatch_qty").val('');

        $.ajax({
            url: "get_balance_qty.php",
            type: "POST",
            data: {
                tran_detail_id: tran_detail_id,
                order_qty: qty
            },
            success: function(res) {
                $("#balance_qty").val(res);
            }
        });

        load_dispatch_history(tran_detail_id);

        $("#dispatchModal").modal('show');
    }



    function load_dispatch_history(tran_detail_id) {
        $.ajax({
            url: "ajax_dispatch_history.php",
            type: "POST",
            data: {
                tran_detail_id: tran_detail_id
            },
            success: function(res) {
                $("#dispatch_history").html(res);
            }
        });
    }



    function save_dispatch() {
        var formData = $("#dispatchForm").serialize();
        var transaction_id = '<?php echo $transaction_id ?>';
        var account_id = '<?php echo $account_id ?>';
        formData += '&transaction_id=' + transaction_id;
        formData += '&account_id=' + account_id;
        $.ajax({
            url: "save_dispatch.php",
            type: "POST",
            data: formData,
            success: function(res) {
                if (res == 1) {
                    swal({
                        title: "Success",
                        text: "Dispatch Saved Successfully",
                        icon: "success",
                        button: "OK"
                    });
                    load_dispatch_history($("#tran_detail_id").val());
                    order_dispatch($("#tran_detail_id").val(), $("#product_id").val(), $("#order_qty").val(), $("#product_name").val());

                    $("#dispatch_qty").val('');
                    $("#remarks").val('');

                    setTimeout(function() {
                        // location.reload();
                    }, 1000);
                } else if (res == 2) {
                    swal({
                        title: "Warning",
                        text: "Enter Valid Dispatch Qty",
                        icon: "warning",
                        button: "OK"
                    });
                } else if (res == 3) {
                    swal({
                        title: "Error",
                        text: "Dispatch Qty Exceeds Balance Qty",
                        icon: "error",
                        button: "OK"
                    });
                } else {
                    swal({
                        title: "Error",
                        text: "Something Went Wrong",
                        icon: "error",
                        button: "OK"
                    });
                }
            }
        });
    }
</script>
<script>
    $("#check_all").click(function() {
        $(".dispatch_checkbox").prop(
            'checked',
            $(this).prop('checked')
        );
    });

    function bulk_dispatch() {

        let approve = parseInt('<?= $sqledit['is_approved']; ?>') || 0;
        let transaction_id = parseInt('<?= $sqledit['transaction_id']; ?>') || 0;
        var account_id = '<?php echo $account_id ?>';
        if (approve !== 1) {
            swal({
                title: "Warning",
                text: "The order has not been approved.",
                icon: "warning"
            });
            return;
        }

        let products = [];

        $(".dispatch_checkbox:checked").each(function() {
            products.push({
                tran_detail_id: $(this).data('tran_detail_id'),
                product_id: $(this).data('product_id'),
                qty: $(this).data('qty')
            });
        });

        if (products.length === 0) {
            swal({
                title: "Warning",
                text: "Select at least one product",
                icon: "warning"
            });
            return;
        }

        swal({
            title: "Are you sure?",
            text: "Dispatch selected products?",
            icon: "warning",
            buttons: true
        }).then((confirm) => {

            if (!confirm) return;
            let btn = $("#dispatch_btn");
            btn.prop("disabled", true).text("Processing...");

            $.ajax({
                url: "save_bulk_dispatch.php",
                type: "POST",
                data: {
                    products: JSON.stringify(products),
                    transaction_id,
                    account_id
                },

                beforeSend: function() {
                    swal({
                        title: "Processing...",
                        text: "Dispatch in progress",
                        buttons: false,
                        closeOnClickOutside: false
                    });
                },

                success: function(res) {

                    if (res == 1) {
                        swal({
                            title: "Success",
                            text: "Products dispatched successfully",
                            icon: "success"
                        });

                        setTimeout(() => location.reload(), 1000);

                    } else {
                        swal({
                            title: "Error",
                            text: "Dispatch failed",
                            icon: "error"
                        });

                        btn.prop("disabled", false).text("Dispatch");
                    }
                },

                error: function() {
                    swal({
                        title: "Error",
                        text: "Server not responding",
                        icon: "error"
                    });

                    btn.prop("disabled", false).text("Dispatch");
                }
            });

        });
    }
</script>

</html>