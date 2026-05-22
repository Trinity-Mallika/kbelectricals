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
    $invoice_no = $sqledit['invoice_no'];
    $updateby = $sqledit['updateby'];
    $up_date = $sqledit['up_date'];
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
                                    <span class="badge bg-warning px-3 py-2 text-dark me-2" style="cursor: pointer;" onclick="order_approve('<?= $transaction_id; ?>')">Pending</span>
                                <?php } ?>
                                <a href="javascript:history.back()" class="btn btn-sm btn-danger">Back</a>
                            </div>
                        </legend>

                        <div class="card">

                            <div class="card-header text-white">
                                Order Details
                            </div>

                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <small>Counter</small>
                                            <div><?= $account_name ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <small>Order No</small>
                                            <div class="text-primary fw-bold">#<?= $billno ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <small>Order Date</small>
                                            <div><?= $obj->dateformatindia($billdate) ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <small>Total Qty</small>
                                            <div class="text-success fw-bold"><?= $total_qty ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-1">
                                        <div class="info-box d-flex justify-content-between align-items-center">
                                            <div>
                                                <small>Invoice No.</small><br>
                                                <span id="invoice_display">
                                                    <?php if (!empty($invoice_no)) { ?>
                                                        <span class="badge bg-info px-3 py-2"><?= htmlspecialchars($invoice_no) ?></span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-secondary px-3 py-2">Not Added</span>
                                                    <?php } ?>
                                                </span>
                                            </div>

                                            <?php if ($sqledit['is_approved'] == 1) { ?>
                                                <button class="btn btn-sm btn-outline-success inv-btn mt-4 ms-2"
                                                    data-id="<?= $transaction_id ?>"
                                                    data-order="<?= htmlspecialchars($billno) ?>"
                                                    data-invoice="<?= htmlspecialchars($invoice_no) ?>">
                                                    <?php if (!empty($invoice_no)) { ?>
                                                        <i class="bi bi-pencil-square"></i>
                                                    <?php } else { ?>
                                                        <i class="bi bi-plus-square"></i>
                                                    <?php } ?>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="col-lg-12 mt-4">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center">
                            <span>Order Product Details</span>

                            <button type="button"
                                class="btn btn-sm btn-light text-primary fw-bold"
                                onclick="bulk_dispatch()">
                                <i class="bi bi-truck"></i> Dispatch Selected
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th class="text-center">S. No.</th>
                                        <th>Brand Name</th>
                                        <th>Category/Product Name</th>
                                        <th>Unit</th>
                                        <th>QTY</th>
                                        <th>rate</th>
                                        <th class="text-end"> Total</th>
                                        <th class=""> Dispatch</th>
                                        <th width="5%">
                                            <input type="checkbox" id="check_all" title="Select All">
                                        </th>
                                    </thead>
                                    <tbody>
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
ORDER BY td.tran_detail_id DESC
";
                                        $i = 1;
                                        $res = $obj->executequery($sql);
                                        $row_count = count($res);

                                        foreach ($res as $key) {
                                        ?>

                                            <tr>

                                                <td><?php echo $i++ ?></td>
                                                <td><?php echo $key['brand_name'] ?></td>
                                                <td>
                                                    <div class="fw-semibold"><?= $key['product_name'] ?></div>
                                                    <small class="text-muted"><?= $key['category_name'] ?></small>
                                                </td>

                                                <td><?php echo $key['unit_name'] ?> </td>
                                                <td><?php echo $key['qty'] ?> </td>
                                                <td><?php echo $key['rate'] ?></td>
                                                <td class="text-end"><?php echo number_format($key['total_amt'], 2) ?></td>

                                                <td>

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

                                                <td>
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
                                            </tr>
                                        <?php
                                            $total += $key['total_amt'];
                                        } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="fw-bold text-end">Grand Total</td>
                                            <td class="text-end"><?php echo number_format($total, 2) ?></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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

        add_invoice(id, order, invoice);
    });

    function add_invoice(transaction_id, order_no, invoice = '') {
        $('#invoiceModal').modal('show');

        $('#transaction_id').val(transaction_id);
        $('#order_ref').text(order_no);

        $('#invoice_no').val(invoice).focus();
    }

    function save_invoice() {
        let id = $('#transaction_id').val();
        let invoice = $('#invoice_no').val().trim();

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
                invoice_no: invoice
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
                    products: JSON.stringify(products)
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