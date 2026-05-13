<?php
include("../adminsession.php");
// session_start();
// print_r($_SESSION);
// echo $companyid;
// exit;
$title = "Quotation Entry";
$pagename = "quotation.php";
$module = "Quotation Entry";
$submodule = "Quotation Entry List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$keyvalue = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$type = "quotation";
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$account_id = isset($_GET['account_id']) ? $obj->test_input($_GET['account_id']) : '';


$taxtype = isset($taxtype) ? $taxtype : 'exclusive';
$short_name = "";

$res = $obj->select_record("company_setting", ["company_id" => $companyid]);

if (!empty($res) && isset($res['short_name'])) {
    $short_name = $res['short_name'];
}

$billno = $obj->getquocode($tblname, "billno", $short_name, "1=1 and type='$type' ");
$billdate = date("Y-m-d");
if (isset($_POST['submit'])) {
    $keyvalue = $obj->test_input($_POST['transaction_id']);
    $account_id = $obj->test_input($_POST['account_id']);
    $billno = $obj->test_input($_POST['billno']);
    $billdate = $obj->test_input($_POST['billdate']);
    $gst = $obj->test_input($_POST['gst']);
    $is_gst = $obj->test_input($_POST['is_gst']);
    $freight = $obj->test_input($_POST['freight']);
    $validity = $obj->test_input($_POST['validity']);
    $payment = $obj->test_input($_POST['payment']);
    $remark = $obj->test_input($_POST['remark']);
    $cgst = $obj->test_input($_POST['cgst']);
    $sgst = $obj->test_input($_POST['sgst']);
    $gst_percent = $obj->test_input($_POST['gst_percent']);
    $grand_total = $obj->test_input($_POST['grand_total']);
    $net_total_amt = $obj->test_input($_POST['net_total_amt']);
    $form_data = array(
        // "company_id" => $company_id,
        "account_id" => $account_id,
        "type" => $type,
        "net_total_amt" => $net_total_amt,
        "cgst" => $cgst,
        "sgst" => $sgst,
        "gst_percent" => $gst_percent,
        "grand_total" => $grand_total,
        "remark" => $remark,
        "gst" => $gst,
        "is_gst" => $is_gst,
        "freight" => $freight,
        "validity" => $validity,
        "payment" => $payment,
        "billno" => $billno,
        "billdate" => $billdate,
        "createdby" => $loginid,
        "company_id" => $companyid,
        'createdate' => $createdate,
        "ipaddress" => $ipaddress,
    );

    if ($keyvalue == 0) {
        $form_data["createdate"] = $createdate;
        $lastid = $obj->insert_record_lastid($tblname, $form_data);
        $obj->update_record('transaction_details', ['transaction_id' => 0, 'type' => $type, 'account_id' => $account_id, 'company_id' => $companyid, "createdby" => $loginid], ['transaction_id' => $lastid]);

        $action = 1;
        $process = "Insert";
        echo "<script>location='$pagename?action=$action'</script>";
    } else {
        $form_data["lastupdated"] = $createdate;
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $action = 2;
        $process = "Update";
    }

    echo "<script>location='$pagename?action=$action'</script>";
}

if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $account_id = $sqledit['account_id'];
    $remark = $sqledit['remark'];
    $billdate = $sqledit['billdate'];
    $billno = $sqledit['billno'];
    $gst = $sqledit['gst'];
    $is_gst = $sqledit['is_gst'];
    $freight = $sqledit['freight'];
    $validity = $sqledit['validity'];
    $payment = $sqledit['payment'];
} else {
    $remark = $gst = $freight = $validity = $payment = "";
    $is_gst = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tag -->
    <?php include('component/css.php'); ?>
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
            <form action="" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="mt-2">
                            <legend><?php echo $title ?></legend>
                            <?php include('component/alert.php'); ?>
                            <div class="card">
                                <div class="card-header text-white">
                                    <?php echo $module ?>
                                    <a href="quotation_list.php" class="btn btn-sm btn-warning float-end">Quotation List</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <input type="hidden" name="transaction_id" value="<?php echo $keyvalue; ?>">
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Account Name <span class="text-danger">*</span></label></strong>
                                            <select class="form-control form-control-sm chosen-select" name="account_id" id="account_id" onchange="get_url1(this.value);" <?php echo ($keyvalue > 0) ? 'disabled' : ''; ?>>
                                                <option value="">Select</option>
                                                <?php $res = $obj->executequery("Select account_id,account_name from account WHERE companyid   = '$companyid' order by account_name asc");

                                                foreach ($res as $key) {
                                                    $selected = ($account_id == $key['account_id']) ? "selected" : "";
                                                    echo "<option value='{$key['account_id']}'>{$key['account_name']}</option>";
                                                } ?>
                                            </select>
                                            <script>
                                                document.getElementById('account_id').value = '<?php echo $account_id  ?>';
                                            </script>
                                            <input type="hidden" name="account_id" id="account_id" <?php echo ($keyvalue == 0) ? 'disabled' : ''; ?> value="<?php echo $account_id  ?>">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Quotation No. <span class="text-danger">*</span></label></strong>
                                            <input type="text" name="billno" id="billno" value="<?= $billno; ?>" class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Quotation Date <span class="text-danger">*</span></label></strong>
                                            <input type="date" name="billdate" id="billdate" value="<?= $billdate ?>" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>GST <span class="text-danger"></span></label></strong>

                                            <input type="text" name="gst" id="gst" value="<?= $gst; ?>" placeholder="Enter GST" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Validity <span class="text-danger"></span></label></strong>
                                            <input type="text" name="validity" id="validity" value="<?= $validity; ?>" placeholder="Enter Validity" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Freight <span class="text-danger"></span></label></strong>
                                            <input type="text" name="freight" id="freight" value="<?= $freight; ?>" placeholder="Enter Freight" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Payment <span class="text-danger"></span></label></strong>
                                            <input type="text" name="payment" id="payment" value="<?= $payment; ?>" placeholder="Enter Payment" class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-3 mb-2 d-flex align-items-center">

                                            <div class="form-check ms-2">
                                                <input class="form-check-input" type="checkbox" id="gst_checkbox"
                                                    <?= ($is_gst == 1) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="gst_checkbox">
                                                    Show GST
                                                </label>
                                            </div>

                                            <input type="hidden" name="is_gst" id="is_gst" value="<?= $is_gst == 1 ? 1 : 0; ?>">
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <strong><label>Remarks</label></strong>
                                            <textarea name="remark" class="form-control form-control-sm" placeholder="Enter Remarks"><?php echo $remark; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-lg-12 mt-4">
                        <div class="card">
                            <div class="card-header text-white">
                                Product Entry
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong> <label for="images">Brand Name <span class="text-danger fw-bold">*</span></label></strong>
                                        <select type="text" class="form-control form-control-sm chosen-select" name="brand_id" id="brand_id" onchange="load_category_by_brand(this.value);">
                                            <option value="">--Select Brand--</option>
                                            <?php

                                            $sql = $obj->executequery("select * from category_master where type='brand' order by cat_id DESC ");

                                            foreach ($sql as $key) {
                                            ?> <option value="<?php echo $key['cat_id'] ?>"><?php echo $key['cat_name'] ?></option> <?php } ?>
                                        </select>
                                    </div>

                                    <!-- CATEGORY (EMPTY INITIALLY) -->
                                    <div class="col-md-3">
                                        <strong><label>Category Name<span class="text-danger">*</span></label></strong>
                                        <select class="form-select form-select-sm chosen-select" id="category_id" onchange="get_products(this.value)">
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><label>Product<span class="text-danger">*</span></label></strong>
                                        <select class="form-select form-select-sm chosen-select" id="product_id" onchange="get_product_details(this.value);">
                                            <option value="">Select</option>

                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <strong> <label for="images">Unit Name<span class="text-danger fw-bold">*</span></label></strong>

                                        <input type="hidden" class="form-control form-control-sm " name="unit_id" id="unit_id">
                                        <input type="text" class="form-control form-control-sm " name="unit_name" id="unit_name" readonly>
                                    </div>

                                    <div class="col-md-2">
                                        <strong><label>MRP</label></strong>
                                        <input type="number" id="rate" class="form-control form-control-sm" onkeyup="calculate_total()">
                                    </div>
                                    <div class="col-md-2">
                                        <strong><label>Qty</label></strong>
                                        <input type="number" id="qty" class="form-control form-control-sm" onkeyup="calculate_total()">
                                    </div>
                                    <div class="col-md-2">
                                        <strong><label>Sub Total</label></strong>
                                        <input type="number" id="sub_total" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <strong><label>Discount<span class="text-danger"> (%)</span></label></strong>
                                        <input type="number" id="discount" class="form-control form-control-sm" onkeyup="calculate_total()">
                                        <input type="hidden" id="discount_amt" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <strong><label>Taxable</label></strong>
                                        <input type="number" id="total_amt" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <strong> <label for="images">GST <span class="text-danger fw-bold"></span></label></strong>
                                        <select type="text" class="form-control form-control-sm chosen-select" name="gst_id" id="gst_id" onchange="calculate_total()">
                                            <option value="">--Select GST--</option>
                                            <?php
                                            $sql = $obj->executequery("select * from gst_master where gst_id not in(1,2) order by gst_name DESC ");

                                            foreach ($sql as $key) {

                                            ?> <option value="<?php echo $key['gst_id'] ?>"><?php echo $key['gst_name'] ?></option> <?php } ?>
                                        </select>

                                    </div>
                                    <div class="col-md-2">
                                        <strong> <label for="images">Tax Type <span class="text-danger fw-bold"></span></label></strong>
                                        <select name="taxtype" id="taxtype" class="form-control form-control-sm chosen-select" onchange="calculate_total()">
                                            <option value="inclusive" <?= ($taxtype == 'inclusive') ? 'selected' : '' ?>>Inclusive</option>
                                            <option value="exclusive" <?= ($taxtype == 'exclusive' || empty($taxtype)) ? 'selected' : '' ?>>Exclusive</option>
                                        </select>
                                        <script>
                                            document.getElementById('taxtype').value = '<?php echo $taxtype; ?>';
                                        </script>

                                    </div>
                                    <div class="col-md-2">
                                        <strong><label>Net Total</label></strong>
                                        <input type="number" id="net_total" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <strong><label>Ready Stock</label></strong>
                                        <br>
                                        <input type="checkbox" id="ready_stock" class="form-check-input" checked>
                                    </div>
                                    <div class="col-md-2" id="delivery_div" style="display: none;">
                                        <strong><label>Delivery Status</label></strong>
                                        <input type="text" id="delivery_status" class="form-control form-control-sm">
                                    </div>

                                    <input type="hidden" id="m_tran_detail_id" value="0">
                                    <div class="col-md-2 mt-4 ">
                                        <input type="button" id="add_btn" class="btn btn-theme btn-sm" onclick="add_product()" value="Add">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-4">
                        <div class="card">
                            <div class="card-header text-white">
                                <?php echo $submodule; ?>
                            </div>
                            <div class="card-body" id="fetch_data">

                            </div>

                        </div>


                    </div>
                </div>
            </form>

        </div>
        <!-- Content Close-->
    </div>

</body>

<!-- Script tags -->
<?php include('component/script.php'); ?>
<script>
    $('#ready_stock').change(function() {

        if ($(this).is(':checked')) {
            // ✅ Ready stock checked → hide delivery
            $('#delivery_div').hide();
            $('#delivery_status').val('');
        } else {
            // ❌ Not checked → show delivery
            $('#delivery_div').show();
        }

    });

    $('#gst_checkbox').change(function() {
        if ($(this).is(':checked')) {
            $('#is_gst').val(1);
        } else {
            $('#is_gst').val(0);
        }
    });

    function get_url1(account_id) {
        if (account_id > 0) {
            location = 'quotation.php?account_id=' + account_id;
        }
    }


    $(document).ready(function() {
        $(".chosen-select").chosen();
        fetch_data('<?php echo $keyvalue ?>');
    });



    function get_product_details(product_id) {
        jQuery.ajax({
            type: 'POST',
            url: 'get_product_details.php',
            data: {
                product_id: product_id
            },
            dataType: 'json', // ✅ important
            success: function(res) {
                // alert(res);
                if (res.status === 'success') {

                    // Set rate
                    $('#rate').val(res.rate);

                    $('#unit_id').val(res.unit_id);

                    // OR if you just want to show name
                    $('#unit_name').val(res.unit_name);

                } else {
                    alert('Product not found');
                }
            }
        });
    }


    function load_category_by_brand(brand_id, category_id = 0) {

        let account_id = $('#account_id').val();

        if (!account_id) {
            alert('Please select account name first');
            $('#category_id').val('').trigger('chosen:updated');
            return false;
        }

        if (brand_id != "") {
            $.ajax({
                url: "get_category.php",
                type: "POST",
                data: {
                    brand_id: brand_id,
                    category_id: category_id
                },
                success: function(data) {
                    $("#category_id").html(data);
                    $("#category_id").trigger("chosen:updated");
                }
            });
        } else {
            $("#category_id").html("<option value=''>Select</option>");
            $("#category_id").trigger("chosen:updated");
        }
    }

    function get_products(category_id, product_id = 0) {

        let brand_id = document.getElementById('brand_id').value;



        if (!brand_id) {
            alert('Please select brand first');
            $('#category_id').val('').trigger('chosen:updated');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: 'get_product_combo.php',
            data: {
                category_id: category_id,
                brand_id: brand_id, // ✅ ADD THIS
                product_id: product_id
            },
            success: function(data) {
                $('#product_id').html(data);
                $('#product_id').trigger('chosen:updated');
            }
        });
    }

    function calculate_total() {

        let qty = parseFloat($('#qty').val()) || 0;
        let rate = parseFloat($('#rate').val()) || 0;
        let discount = parseFloat($('#discount').val()) || 0;

        let gst_percent = parseFloat($('#gst_id option:selected').text().match(/\d+/)) || 0;
        let taxtype = $('#taxtype').val();

        let sub_total = qty * rate;

        let discount_amt = (sub_total * discount) / 100;
        let amount_after_discount = sub_total - discount_amt;

        let taxable = 0;
        let net_amt = 0;
        let gst_amt = 0;

        if (taxtype == 'exclusive') {
            taxable = amount_after_discount;
            gst_amt = (taxable * gst_percent) / 100;
            net_amt = taxable + gst_amt;
        } else if (taxtype == 'inclusive') {
            net_amt = amount_after_discount;
            taxable = (net_amt * 100) / (100 + gst_percent);
            gst_amt = net_amt - taxable;
        }

        $('#sub_total').val(sub_total.toFixed(2));
        $('#discount_amt').val(discount_amt.toFixed(2));
        $('#total_amt').val(taxable.toFixed(2));
        $('#net_total').val(net_amt.toFixed(2));
    }

    function delete_record(id) {
        jQuery.ajax({
            type: 'POST',
            url: 'ajax/delete_master.php',
            data: {
                id: id,
                tblname: 'transaction_details',
                tblpkey: 'tran_detail_id',
            },
            dataType: 'html',
            success: function(data) {
                fetch_data('<?php echo $keyvalue ?>');
            }
        });

    }

    function fetch_data(transaction_id) {
        let company_id = '<?= $companyid; ?>';
        let account_id = '<?= $account_id; ?>';
        let type = '<?= $type; ?>';

        jQuery.ajax({
            type: 'POST',
            url: 'fetch_quotation_product.php',
            data: {
                account_id: account_id,
                company_id: company_id,
                transaction_id: transaction_id,
                type: type,
            },
            dataType: 'html',
            success: function(data) {
                document.getElementById("fetch_data").innerHTML = data;
                calculateGST();
            }
        });

    }

    function EditProduct(brand_id, category_id, product_id, unit_id, unit_name, qty, rate, sub_total, discount, ready_stock, delivery_status, total_amt, tran_detail_id, gst_id, taxtype, net_amt) {

        $('#brand_id').val(brand_id).trigger('chosen:updated');

        load_category_by_brand(brand_id, category_id);
        get_products(category_id, product_id);
        $(document).ready(function() {

            if ($('#ready_stock').is(':checked')) {
                $('#delivery_div').hide();
            } else {
                $('#delivery_div').show();
            }

        });
        $('#ready_stock').prop('checked', ready_stock == 1);
        $('#qty').val(qty);
        $('#rate').val(rate);
        $('#unit_id').val(unit_id);
        $('#unit_name').val(unit_name);
        $('#sub_total').val(sub_total);
        $('#delivery_status').val(delivery_status);
        $('#discount').val(discount);
        $('#total_amt').val(total_amt);

        $('#gst_id').val(gst_id).trigger('chosen:updated');
        $('#taxtype').val(taxtype).trigger('chosen:updated');
        $('#net_total').val(net_amt);

        $('#m_tran_detail_id').val(tran_detail_id);
        $('#add_btn').val('Update');
        calculateGST();

    }



    function add_product() {

        let ready_stock = document.getElementById('ready_stock').checked ? 1 : 0;
        let delivery_status = document.getElementById('delivery_status').value;
        let product_id = document.getElementById('product_id').value.trim();

        let category_id = document.getElementById('category_id').value;
        let brand_id = document.getElementById('brand_id').value;
        let unit_id = document.getElementById('unit_id').value;
        let unit_name = document.getElementById('unit_name').value;
        let qty = document.getElementById('qty').value.trim();
        let rate = document.getElementById('rate').value.trim();
        let discount = document.getElementById('discount').value;
        let total_amt = document.getElementById('total_amt').value.trim();
        let sub_total = document.getElementById('sub_total').value.trim();
        let discount_amt = document.getElementById('discount_amt').value.trim();

        let tran_detail_id = document.getElementById('m_tran_detail_id').value;
        let gst_id = document.getElementById('gst_id').value;
        let taxtype = document.getElementById('taxtype').value;
        let net_amt = document.getElementById('net_total').value;
        let transaction_id = '<?php echo $keyvalue ?>';
        let company_id = '<?= $companyid; ?>';
        let account_id = '<?= $account_id; ?>';

        let type = '<?= $type; ?>';



        if (account_id == '') {
            alert('Please select Account Name');
            return false;
        }

        if (brand_id == '') {
            alert('Please select Brand Name');
            return false;
        }

        if (category_id == '') {
            alert('Please select Category Name');
            return false;
        }
        if (product_id == '') {
            alert('Please select Product Name');
            return false;
        }



        if (qty == '' || qty <= 0) {
            alert('Please enter valid Quantity');
            return false;
        }

        if (rate == '' || rate <= 0) {
            alert('Please enter valid Rate');
            return false;
        }

        if (sub_total == '' || sub_total <= 0) {
            alert('Sub Total must be greater than 0');
            return false;
        }

        if (total_amt == '' || total_amt <= 0) {
            alert('Total Amount must be greater than 0');
            return false;
        }
        // ✅ Validation
        if (ready_stock == 0 && delivery_status == '') {
            alert('Please select Delivery Status');
            $('#delivery_status').focus();
            return false;
        }

        // ✅ AJAX call
        jQuery.ajax({
            type: 'POST',
            url: 'add_product.php',
            data: {
                ready_stock: ready_stock,
                unit_name: unit_name,
                product_id: product_id,
                category_id: category_id,
                delivery_status: delivery_status,
                brand_id: brand_id,
                unit_id: unit_id,
                qty: qty,
                rate: rate,
                discount: discount,
                total_amt: total_amt,
                discount_amt: discount_amt,
                sub_total: sub_total,
                tran_detail_id: tran_detail_id,
                account_id: account_id,
                company_id: company_id,
                gst_id: gst_id,
                taxtype: taxtype,
                net_amt: net_amt,
                transaction_id: transaction_id,
                type: type
            },
            dataType: 'html',
            success: function(data) {
                if (data == 1 || data == 2) {
                    fetch_data(transaction_id);
                } else if (data == 3) {
                    alert('This product already added. Please update the existing product.');
                    return;
                }

                // 🔥 Reset form fields
                $('#category_id').val('').trigger('chosen:updated'); // ❌ no change trigger
                $('#product_id').val('').trigger('chosen:updated'); // ❌ no change trigger
                $('#brand_id').val('').trigger('chosen:updated');
                $('#qty').val("");

                $('#ready_stock').prop('checked', false);
                $('#add_btn').val('Add');
                $('#unit_id').val('');
                $('#unit_name').val('');
                $('#rate').val('');
                $('#delivery_status').val('');
                $('#net_total').val('');
                $('#discount').val('');
                $('#discount_amt').val('');
                $('#sub_total').val('');
                $('#total_amt').val('');
                $('#gst_id').val('').trigger('chosen:updated');
                $('#taxtype').val('').trigger('chosen:updated');
                $('#sub_total').val('');





            }
        });
    }
</script>
<script>
    function calculateGST() {

        // ✅ Safe fetch using jQuery (no null error)
        let net_total = parseFloat($('#net_total_amt').val()) || 0;
        let gst_percent = parseFloat($('#gst_percent').val()) || 0;

        // ❌ If elements not loaded yet, stop
        if ($('#net_total_amt').length === 0) return;

        let half_gst = gst_percent / 2;

        // ✅ GST calculation
        let gst_amount = (net_total * gst_percent) / 100;
        let cgst = gst_amount / 2;
        let sgst = gst_amount / 2;
        let grand_total = net_total + gst_amount;

        // ✅ Display values (check existence before updating)
        if ($('#cgst_display').length) $('#cgst_display').text(cgst.toFixed(2));
        if ($('#sgst_display').length) $('#sgst_display').text(sgst.toFixed(2));
        if ($('#grand_total_display').length) $('#grand_total_display').text(grand_total.toFixed(2));

        if ($('#cgst_percent_display').length) $('#cgst_percent_display').text(half_gst);
        if ($('#sgst_percent_display').length) $('#sgst_percent_display').text(half_gst);

        // ✅ Hidden fields update
        $('#cgst').val(cgst.toFixed(2));
        $('#sgst').val(sgst.toFixed(2));
        $('#grand_total').val(grand_total.toFixed(2));
        $('#gst_percent_hidden').val(gst_percent);
    }
</script>

</html>