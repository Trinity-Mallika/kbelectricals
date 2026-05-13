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



    <style>
        /* Chrome, Safari, Edge, Opera */

        input::-webkit-outer-spin-button,

        input::-webkit-inner-spin-button {

            -webkit-appearance: none;

            margin: 0;

        }



        /* Firefox */

        input[type=number] {

            -moz-appearance: textfield;

        }



        .card-header {

            background-color: #06163a;

        }
    </style>

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

                        <legend><?= $module ?> <a href="javascript:history.back()"
                                class="btn btn-sm btn-danger float-end mr-2">
                                Back
                            </a></legend>


                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="card">

                                <div class="card-header text-white">

                                    Order Details
                                    <?php if ($sqledit['is_approved'] == 0) { ?>
                                        <a class=" btn btn-sm btn-secondary float-end" onclick="order_approve('<?php echo $sqledit['transaction_id'] ?>')"> Approve</a>
                                    <?php } else { ?>
                                        <span class="text-white"><a class=" btn btn-sm btn-success float-end"><b>Approved</b></a></span>
                                    <?php } ?>
                                </div>

                                <div class="card-body">
                                    <div class="row mt-1">
                                        <div class="col-md-3">
                                            <h6> Counter Name : <b> <span><?php echo $account_name; ?></span></b></h6>
                                        </div>
                                        <div class="col-md-3">
                                            <h6> Order No. : <b> <span><?php echo $billno; ?></span></b></h6>
                                        </div>
                                        <div class="col-md-3">
                                            <h6> Order Date : <b> <span><?php echo $obj->dateformatindia($billdate); ?></b></h6>
                                        </div>
                                        <div class="col-md-3">
                                            <h6> Total Product Qty : <b> <span><?php echo $total_qty; ?></b></h6>
                                        </div>
                                        <!-- <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Counter Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <span class="form-control form-control-sm"><?php echo $account_name; ?></span>

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Order No.<span class="text-danger fw-bold">*</span></label></strong>
                                            <span class="form-control form-control-sm"><?php echo $billno; ?></span>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no"> Order Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <span class="form-control form-control-sm"><?php echo $obj->dateformatindia($billdate); ?></span>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no"> Total Product Qty <span class="text-danger fw-bold"></span></label> </strong>
                                            <span class="form-control form-control-sm"><?php echo $total_qty; ?></span>
                                        </div> -->

                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-4 mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-white">
                            Order Product Details
                            <button type="button"
                                class="btn btn-sm btn-primary float-end"
                                onclick="bulk_dispatch()">

                                Dispatch Selected Product

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
                                            <input type="checkbox" id="check_all">
                                        </th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 0;
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
                                                <td><?php echo $key['category_name'] ?><br>
                                                    <?php echo $key['product_name'] ?></td>

                                                <td><?php echo $key['unit_name'] ?> </td>
                                                <td><?php echo $key['qty'] ?> </td>
                                                <td><?php echo $key['rate'] ?></td>
                                                <td class="text-end"><?php echo number_format($key['total_amt'], 2) ?></td>

                                                <td>
                                                    <?php if ($key['is_dispatched'] == 0) { ?><a class=" btn btn-sm btn-secondary" onclick="order_dispatch('<?php echo $key['tran_detail_id'] ?>',
            '<?php echo $key['product_id'] ?>',
            '<?php echo $key['qty'] ?>',
            '<?php echo $key['product_name'] ?>')"> Dispatch</a>
                                                    <?php } else { ?>
                                                        <button class="btn btn-sm btn-success"
                                                            onclick="order_dispatch('<?php echo $key['tran_detail_id'] ?>',
            '<?php echo $key['product_id'] ?>',
            '<?php echo $key['qty'] ?>',
            '<?php echo $key['product_name'] ?>')">
                                                            Delivered
                                                        </button>
                                                    <?php } ?>
                                                </td>

                                                <td> <?php if ($key['is_dispatched'] == 0) { ?>

                                                        <input type="checkbox"
                                                            class="dispatch_checkbox"

                                                            data-tran_detail_id="<?php echo $key['tran_detail_id'] ?>"
                                                            data-product_id="<?php echo $key['product_id'] ?>"
                                                            data-qty="<?php echo $key['qty'] ?>">

                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php
                                            $total += $key['total_amt'];
                                        } ?>



                                    </tbody>
                                    <tfoot>

                                        <tr>
                                            <td colspan="6">Total</td>
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
        <div class="modal-dialog ">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Product Dispatch</h5>
                    <button type="button" class="close" onclick="$('#dispatchModal').modal('hide');">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="dispatchForm">
                        <input type="hidden" name="tran_detail_id" id="tran_detail_id">
                        <input type="hidden" name="product_id" id="product_id">

                        <div class="row">

                            <div class="col-md-8 mb-3">
                                <label>Product</label>
                                <input type="text" id="product_name"
                                    class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Dispatch Date</label>
                                <input type="date" name="dispatch_date"
                                    value="<?php echo date('Y-m-d') ?>"
                                    class="form-control form-control-sm" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Order Qty</label>
                                <input type="text" id="order_qty"
                                    class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Balance Qty</label>
                                <input type="text" id="balance_qty"
                                    class="form-control form-control-sm bg-light" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Dispatch Qty</label>
                                <input type="number" name="dispatch_qty"
                                    id="dispatch_qty"
                                    class="form-control form-control-sm"
                                    required>
                            </div>


                            <div class="col-md-12 mb-3">
                                <label>Remarks</label>

                                <textarea name="remarks"
                                    id="remarks"
                                    class="form-control form-control-sm"></textarea>
                            </div>

                            <div class="col-md-12">
                                <button type="button"
                                    class="btn btn-primary btn-sm form-control"
                                    onclick="save_dispatch()">
                                    Save
                                </button>
                            </div>

                        </div>

                    </form>

                    <hr>

                    <h6>Dispatch History</h6>

                    <div id="dispatch_history"></div>

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

    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        pagename = '<?php echo $pagename; ?>';
        submodule = '<?php echo $submodule; ?>';
        module = '<?php echo $module; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close

    $(document).ready(function() {

        //called when key is pressed in textbox

        $("#mobile_no").keypress(function(e) {

            //if the letter is not digit then display error and don't type anything

            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                //display error message

                $("#errmsg").html("Digits Only").show().fadeOut("slow");

                return false;

            }

        });

    });
</script>
<script>
    function changeQtyLabel() {

        let scheme_type = document.querySelector('input[name="scheme_type"]:checked').value;

        if (scheme_type == 'amt_wise') {

            document.getElementById('qty_label').innerHTML =
                'Amount <span class="text-danger fw-bold"></span>';

        } else {

            document.getElementById('qty_label').innerHTML =
                'QTY <span class="text-danger fw-bold"></span>';
        }
    }

    // page load
    changeQtyLabel();

    // radio change
    document.querySelectorAll('.scheme_type').forEach(function(el) {

        el.addEventListener('change', function() {
            changeQtyLabel();
        });


    });

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
        var products = [];

        $(".dispatch_checkbox:checked").each(function() {

            products.push({
                tran_detail_id: $(this).data('tran_detail_id'),
                product_id: $(this).data('product_id'),
                qty: $(this).data('qty')
            });

        });


        if (products.length == 0) {
            swal({
                title: "Warning",
                text: "Select Product",
                icon: "warning"
            });

            return false;
        }


        swal({
            title: "Are you sure?",
            text: "Dispatch selected products?",
            icon: "warning",
            buttons: true,
            dangerMode: false,

        }).then((willDispatch) => {

            if (willDispatch) {

                $.ajax({

                    url: "save_bulk_dispatch.php",
                    type: "POST",

                    data: {
                        products: JSON.stringify(products)
                    },

                    success: function(res) {
                        alert(res);
                        if (res == 1) {
                            swal({
                                title: "Success",
                                text: "Products Dispatched Successfully",
                                icon: "success"
                            });

                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            swal({
                                title: "Error",
                                text: "Something Went Wrong",
                                icon: "error"
                            });
                        }
                    }

                });

            }

        });

    }
</script>

</html>