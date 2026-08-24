<?php
include("../adminsession.php");

$title = "Order Entry";
$pagename = "order-entry.php";
$module = "Order Entry";
$submodule = "Order Entry List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$keyvalue = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$type = "order";
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$account_id = isset($_GET['account_id']) ? $obj->test_input($_GET['account_id']) : '';
$short_name = "";

$res = $obj->select_record("company_setting", ["company_id" => $companyid]);

if (!empty($res) && isset($res['short_name'])) {
    $short_name = $res['short_name'];
}

if (isset($_POST['submit'])) {
    $account_id = $obj->test_input($_POST['account_id']);
    $billno = $obj->test_input($_POST['billno']);
    $billdate = $obj->test_input($_POST['billdate']);
    $remark = $obj->test_input($_POST['remark']);
    $gst_percent = isset($_POST['gst_percent']) ? $obj->test_input($_POST['gst_percent']) : 0;
    $cgst = isset($_POST['cgst']) ? $obj->test_input($_POST['cgst']) : 0;
    $sgst = isset($_POST['sgst']) ? $obj->test_input($_POST['sgst']) : 0;
    $taxable_amount = isset($_POST['taxable_amount']) ? $obj->test_input($_POST['taxable_amount']) : 0;
    $freight_charges = isset($_POST['freight_charges']) ? $obj->test_input($_POST['freight_charges']) : 0;
    $overall_gst_amt = $obj->test_input($_POST['overall_gst_amt']);
    $grand_total = $obj->test_input($_POST['grand_total']);
    $net_total_amt = $obj->test_input($_POST['net_total_amt']);
    $round_off = $obj->test_input($_POST['round_off']);
    $is_gst = ($gst_percent > 0) ? 1 : 0;

    $form_data = array(
        "account_id" => $account_id,
        "type" => $type,
        "net_total_amt" => $net_total_amt,
        "round_off" => $round_off,
        "freight_charges" => $freight_charges,
        "taxable_amount" => $taxable_amount,
        "cgst" => $cgst,
        "sgst" => $sgst,
        "is_gst" => $is_gst,
        "gst_percent" => $gst_percent,
        "overall_gst_amt" => $overall_gst_amt,
        "grand_total" => $grand_total,
        "remark" => $remark,
        "billno" => $billno,
        "billdate" => $billdate,
        "companyid" => $companyid,
        'createdate' => $createdate,
        "ipaddress" => $ipaddress,
    );

    if ($keyvalue == 0) {
        $form_data["createdby"] = $loginid;
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
    $gst_percent = $sqledit['gst_percent'];
} else {
    $remark = "";
    $gst_percent = 0;
    $billno      = $obj->getcode($tblname, "billno", "1=1 and type='$type'");
    $billdate = date("Y-m-d");
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
                                    <a href="order_list.php" class="btn btn-sm btn-warning float-end">Order List</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <input type="hidden" name="transaction_id" value="<?php echo $keyvalue; ?>">
                                        <div class="col-md-3 mb-2">
                                            <label class="w-100 ">
                                                <strong>
                                                    Account Name <span class="text-danger">*</span>
                                                </strong>
                                                <button type="button" class="float-end badge text-bg-primary"
                                                    style="cursor:pointer;" onclick="add_account();">
                                                    Add+
                                                </button>
                                            </label>
                                            <select class="form-control form-control-sm chosen-select" name="account_id" id="account_id" onchange="get_url1(this.value);" <?php echo ($keyvalue > 0) ? 'disabled' : ''; ?>>
                                                <option value="">Select</option>
                                                <?php $res = $obj->executequery("Select account_id,account_name from account order by account_name asc");

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
                                            <strong><label>Order No. <span class="text-danger">*</span></label></strong>
                                            <input type="text" name="billno" id="billno" value="<?= $billno; ?>" class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Order Date <span class="text-danger">*</span></label></strong>
                                            <input type="date" name="billdate" id="billdate" value="<?= $billdate ?>" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <div class="form-check form-check-inline mb-0">
                                                    <input class="form-check-input" type="checkbox" id="gst_enabled" onchange="toggleGSTMode()" value="1" checked>
                                                    <label class="form-check-label small fw-semibold" for="gst_enabled">Show GST?</label>
                                                </div>
                                                <div id="gst_type_options" style="display:none;">
                                                    <div class="form-check form-check-inline mb-0">
                                                        <input class="form-check-input" type="radio" name="gst_mode" id="gst_productwise"
                                                            value="productwise" onchange="toggleGSTMode()">
                                                        <label class="form-check-label small" for="gst_productwise">Inclusive</label>
                                                    </div>
                                                    <div class="form-check form-check-inline mb-0">
                                                        <input class="form-check-input" type="radio" name="gst_mode" id="gst_overall"
                                                            value="overall" onchange="toggleGSTMode()">
                                                        <label class="form-check-label small" for="gst_overall">Overall</label>
                                                    </div>
                                                </div>
                                            </div>
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
                                    <div class="col-md-2">
                                        <strong> <label for="images">Brand Name <span class="text-danger fw-bold">*</span></label></strong>
                                        <select type="text" class="form-control form-control-sm chosen-select" id="brand_id" onchange="load_category_by_brand(this.value);">
                                            <option value="">--Select Brand--</option>
                                            <?php

                                            $sql = $obj->executequery("select * from category_master where type='brand' order by cat_id DESC ");

                                            foreach ($sql as $key) {
                                            ?> <option value="<?php echo $key['cat_id'] ?>"><?php echo $key['cat_name'] ?></option> <?php } ?>
                                        </select>
                                    </div>

                                    <!-- CATEGORY (EMPTY INITIALLY) -->
                                    <div class="col-md-2">
                                        <strong><label>Category Name<span class="text-danger">*</span></label></strong>
                                        <select class="form-select form-select-sm chosen-select" id="category_id" onchange="get_products(this.value)">
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="w-100 ">
                                            <strong>
                                                Product Name <span class="text-danger">*</span>
                                            </strong>
                                            <button type="button" class="float-end badge text-bg-primary"
                                                style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#product_modal">
                                                Add+
                                            </button>
                                        </label>
                                        <select class="form-select form-select-sm chosen-select" id="product_id" onchange="get_product_details(this.value);">
                                            <option value="">Select</option>

                                        </select>
                                    </div>

                                    <div class="col-md-1 mb-2">
                                        <strong> <label for="images">Unit Name<span class="text-danger fw-bold">*</span></label></strong>

                                        <input type="hidden" class="form-control form-control-sm " id="unit_id">
                                        <input type="text" class="form-control form-control-sm " id="unit_name" readonly>
                                    </div>

                                    <div class="col-md-2 mb-2">
                                        <strong><label>Rate</label></strong>
                                        <input type="number" id="rate" class="form-control form-control-sm" onkeyup="calculate_total()" placeholder="Enter Rate">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <strong><label>Update Rate</label></strong>
                                        <br>
                                        <input type="checkbox" id="update_mrp" class="form-check-input" value="1">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <strong><label>Qty</label></strong>
                                        <input type="number" id="qty" class="form-control form-control-sm" onkeyup="calculate_total()" placeholder="Enter Qty">
                                    </div>

                                    <input type="hidden" id="sub_total">
                                    <input type="hidden" id="total_amt">

                                    <div class="col-md-2 mb-2">
                                        <strong><label>Discount<span class="text-danger"> (%)</span></label></strong>
                                        <input type="number" id="discount" class="form-control form-control-sm" onkeyup="calculate_total()" placeholder="Enter Discount(%)">
                                        <input type="hidden" id="discount_amt" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <strong><label>Price After Disc.</label></strong>
                                        <input type="number" id="price_after_disc" class="form-control form-control-sm" readonly>
                                    </div>
                                    <div class="col-md-2 mb-2" id="gst_block">
                                        <input type="hidden" id="gst_id" value="3">
                                        <input type="hidden" id="gst_percent" value="18">
                                        <strong><label for="">Net Price After Disc.</label></strong>
                                        <input type="number" id="price_after_disc_gst" class="form-control form-control-sm" placeholder="Net Price After Disc." readonly>
                                    </div>
                                    <input type="hidden" id="gst_amt">
                                    <div class="col-md-2 mb-2">
                                        <strong><label>Net Total</label></strong>
                                        <input type="number" id="net_total" class="form-control form-control-sm" readonly>
                                    </div>

                                    <input type="hidden" id="taxtype" value="exclusive">
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
    <div class="modal fade" id="accountNameAdd" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="accountNameAddLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="accountNameAddLabel">Add Customer</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong> <label for="common_id">Counter Type<span class="text-danger fw-bold">*</span> </label></strong>
                            <select name="common_id" id="common_id" class="chosen-select form-control form-control-sm" onchange="get_users(this.value);">
                                <option value="">--Select Counter Type--</option>
                                <?php
                                $sql = $obj->executequery("select common_id,common_name from common_master where type='acc_type' order by common_id asc ");
                                foreach ($sql as $key) {
                                ?>
                                    <option value="<?= $key['common_id'] ?>"><?= $key['common_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <strong> <label for="user_id">Referred By<span class="text-danger fw-bold">*</span> </label></strong>
                            <select id="user_id" class="chosen-select form-control form-control-sm" onchange="toggleRoute()">
                                <option value="">--Select Referred By--</option>
                            </select>
                        </div>
                        <div id="route_div" style="display:none;">
                            <div class="col-md-12 mb-2">
                                <strong><label for="batch_no">Route Name <span class="text-danger fw-bold">*</span></label></strong>
                                <select id="batch_no" class="chosen-select form-control form-control-sm">
                                    <option value="">--Select Route--</option>
                                    <?php
                                    $sql = $obj->executequery("SELECT batch_no,route_name FROM route WHERE companyid='$companyid' GROUP BY batch_no,route_name ORDER BY route_name ASC");
                                    foreach ($sql as $key) { ?>
                                        <option value="<?= $key['batch_no'] ?>"><?= $key['route_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2">
                                <strong><label for="class">Class Name <span class="text-danger fw-bold">*</span></label></strong>
                                <select id="class" class="form-control form-control-sm">
                                    <option value="">--Select Class--</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                </select>
                            </div>
                        </div>
                        <div id="electrician_div" style="display:none;">
                            <div class="col-md-12 mb-2">
                                <strong>
                                    <label for="electrician_name">
                                        Electrician Name <span class="text-danger fw-bold">*</span>
                                    </label>
                                </strong>
                                <input type="text" class="form-control form-control-sm"
                                    id="electrician_name"
                                    placeholder="Electrician Name">
                            </div>

                            <div class="col-md-12 mb-2">
                                <strong>
                                    <label for="electrician_mobile">
                                        Electrician Whatsapp No. <span class="text-danger fw-bold">*</span>
                                    </label>
                                </strong>
                                <input type="text" class="form-control form-control-sm"
                                    id="electrician_mobile"
                                    maxlength="10"
                                    placeholder="Electrician Whatsapp No.">
                            </div>
                            <div class="col-md-12 mb-2">
                                <strong><label for="account_id_map">Counter Name <span class="text-danger fw-bold">*</span></label></strong>
                                <select id="account_id_map" class="chosen-select form-control form-control-sm">
                                    <option value="">--Select Counter--</option>
                                    <?php
                                    $sql = $obj->executequery("SELECT account_name,account_id FROM account WHERE type='customer' ORDER BY account_name ASC");
                                    foreach ($sql as $key) { ?>
                                        <option value="<?= $key['account_id'] ?>"><?= $key['account_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <strong>
                                <label for="area_name">
                                    Area <span class="text-danger fw-bold">* [Search existing or type a new area]</span>
                                </label>
                            </strong>

                            <input type="text"
                                class="form-control form-control-sm"
                                id="area_name"
                                placeholder="Enter 3 characters to search area"
                                autocomplete="off">
                            <input type="hidden" id="area_id" name="area_id">
                            <div id="area_list" class="list-group" style="display:none;position:absolute;z-index:9999;width:95%;max-height:200px;overflow-y:auto;">
                            </div>
                        </div>
                        <div id="normal_customer_fields">
                            <div class="col-md-12 mb-2">
                                <strong> <label for="account_name">Counter/Customer Name <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" name="account_name" id="account_name" placeholder="Counter/Customer  Name" autocomplete="off">
                            </div>
                            <div class="col-md-12 mb-2">
                                <strong> <label for="mobile_no">Whatsapp No. <span class="text-danger fw-bold">*</span></label> </strong>
                                <input type="text" class="form-control form-control-sm" name="mobile_no" id="mobile_no" placeholder="Whatsapp No." maxlength="10" autocomplete="off">
                            </div>
                            <div class="col-md-12 mb-2">
                                <strong> <label for="account_name">Owner Name <span class="text-danger fw-bold"></span></label></strong>
                                <input type="text" class="form-control form-control-sm" name="owner_name" id="owner_name" placeholder="Owner Name" autocomplete="off">
                            </div>
                            <div class="col-md-12 mb-2">
                                <strong> <label for="mobile_no">Owner Mobile No. <span class="text-danger fw-bold"></span></label> </strong>
                                <input type="text" class="form-control form-control-sm" name="o_mobile_no" id="o_mobile_no" placeholder="Owner Mobile No." maxlength="10" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="save_account()">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="product_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="product_modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="product_modalLabel">Add Product</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Brand <span class="text-danger fw-bold">*</span></label>
                            <select id="p_brand_id" class="chosen-select form-select form-select-sm" onchange="pm_load_category(this.value)">
                                <option value="">-- Select Brand --</option>
                                <?php
                                $brands = $obj->executequery("SELECT * FROM category_master WHERE type='brand' ORDER BY cat_name asc");
                                foreach ($brands as $b) {
                                    echo "<option value='{$b['cat_id']}'>{$b['cat_name']}</option>";
                                } ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-12">
                            <label class="form-label">Category <span class="text-danger fw-bold">*</span></label>
                            <select id="p_category_id" class=" chosen-select form-select form-select-sm">
                                <option value="">-- Select Category --</option>
                            </select>
                        </div>

                        <!-- Product Name -->
                        <div class="col-12">
                            <label class="form-label">Product Name <span class="text-danger fw-bold">*</span></label>
                            <input type="text" id="p_product_name" class="form-control form-control-sm" placeholder="Enter Product Name">
                        </div>

                        <!-- Unit -->
                        <div class="col-12">
                            <label class="form-label">Unit <span class="text-danger fw-bold">*</span></label>
                            <select id="p_unit_id" class="form-select form-select-sm">
                                <option value="">-- Select Unit --</option>
                                <?php
                                $units = $obj->executequery("SELECT * FROM category_master WHERE type='unit' ORDER BY cat_id DESC");
                                foreach ($units as $u) {
                                    echo "<option value='{$u['cat_id']}'>{$u['cat_name']}</option>";
                                } ?>
                            </select>
                        </div>

                        <!-- MRP / Rate -->
                        <div class="col-12">
                            <label class="form-label">MRP / Rate <span class="text-danger fw-bold">*</span></label>
                            <input type="number" id="p_mrp" class="form-control form-control-sm text-center" placeholder="0.00">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="save_product_btn" onclick="save_new_product()">
                        <i class="bi bi-plus-lg"></i> Save Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

<!-- Script tags -->
<?php include('component/script.php'); ?>
<script>
    function toggleGSTMode() {
        let enabled = $('#gst_enabled').is(':checked');

        if (!enabled) {
            $('#gst_type_options').hide();
            $('#gst_id').val(0);
            $('#gst_percent').val(0);
            $('input[name="gst_mode"]').prop('checked', false);
            $('#gst_id').val('');
            $('#gst_block').hide();
            calculate_total();
            return;
        }

        $('#gst_type_options').show();

        let currentMode = $('input[name="gst_mode"]:checked').val();

        if (currentMode === 'productwise') {
            $('#gst_id').val(3);
            $('#gst_percent').val(18);
            $('#gst_block').show();
        } else if (currentMode === 'overall') {
            $('#gst_id').val(0);
            $('#gst_percent').val(0);
            $('#gst_block').hide();
        } else {
            $('input[name="gst_mode"][value="productwise"]').prop('checked', true);
            $('#gst_id').val(3);
            $('#gst_percent').val(18);
            $('#gst_block').show();
        }

        fetch_data('<?php echo $keyvalue ?>');
        calculate_total();
    }

    function lockGSTModeIfProductsExist() {
        let productCount = $('#fetch_data table tbody tr').length;
        let is_gst = checkExistingProductGST();
        if (productCount > 0) {
            if (is_gst > 0) {
                $('#gst_overall').prop('disabled', true);
            } else {
                $('#gst_productwise').prop('disabled', true);
            }

        } else {
            $('#gst_productwise').prop('disabled', false);
            $('#gst_overall').prop('disabled', false);
        }
    }


    function checkExistingProductGST() {
        let found = false;
        $('#fetch_data [data-gst-percent]').each(function() {
            if (parseFloat($(this).data('gst-percent')) > 0) {
                found = true;
                return false;
            }
        });
        return found;
    }



    function pm_load_category(brand_id) {
        if (!brand_id) return;

        $.ajax({
            url: 'get_category.php',
            type: 'POST',
            data: {
                brand_id: brand_id
            },
            success: function(res) {
                $('#p_category_id').html(res).trigger('chosen:updated');
            }
        });
    }

    function save_new_product() {
        const brand = document.getElementById('p_brand_id').value;
        const category = document.getElementById('p_category_id').value;
        const name = document.getElementById('p_product_name').value.trim();
        const unit = document.getElementById('p_unit_id').value;
        const mrp = document.getElementById('p_mrp').value;

        if (!brand || !category || !name || !unit || !mrp) {
            alert('Please fill all required fields.');
            return;
        }

        const btn = document.getElementById('save_product_btn');
        btn.disabled = true;
        btn.innerText = 'Saving...';

        $.ajax({
            url: 'ajax_save_product.php',
            type: 'POST',
            data: {
                p_brand_id: brand,
                p_category_id: category,
                p_product_name: name,
                p_unit_id: unit,
                p_mrp: mrp
            },
            success: function(res) {
                let savedBrand = document.getElementById('p_brand_id').value;
                let savedCategory = document.getElementById('p_category_id').value;
                $('#brand_id').val(savedBrand).trigger('chosen:updated');
                $.ajax({
                    url: 'get_category.php',
                    type: 'POST',
                    data: {
                        brand_id: savedBrand
                    },
                    success: function(catOptions) {
                        $('#category_id').html(catOptions).trigger('chosen:updated');
                        $('#category_id').val(savedCategory).trigger('chosen:updated');
                        $('#product_id').html(res).trigger('chosen:updated');
                        const selectedVal = $('#product_id').val();
                        if (selectedVal) get_product_details(selectedVal);
                    }
                });

                bootstrap.Modal.getInstance(document.getElementById('product_modal')).hide();
                $('#p_brand_id').val('').trigger('chosen:updated');
                $('#p_category_id').val('').trigger('chosen:updated');
                document.getElementById('p_product_name').value = '';
                document.getElementById('p_unit_id').value = '';
                document.getElementById('p_mrp').value = '';
            },
            error: function() {
                alert('Something went wrong. Please try again.');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerText = 'Save Product';
            }
        });
    }

    function toggleRoute() {
        var user_type = $("#user_id option:selected").data("type");
        var user_id = $("#user_id").val();
        var counter_type = $("#common_id").val();

        if (counter_type == '6') {
            $("#electrician_div").show();
            $("#normal_customer_fields").hide();
            $("#account_name,#mobile_no,#owner_name,#o_mobile_no,#area_name").val('');
        } else {
            $("#electrician_div").hide();
            $("#electrician_name,#electrician_mobile").val('');
            $("#normal_customer_fields").show();
        }
        // Route for Counter
        if (user_type == 'sales' && counter_type == '7') {
            $("#route_div").show();
            get_routes(user_id);
        } else {
            $("#route_div").hide();
            $("#batch_no").val('').trigger("chosen:updated");
        }
    }


    function get_routes(user_id) {

        $.ajax({
            url: "ajax/get_routes.php",
            type: "POST",
            data: {
                user_id: user_id
            },
            success: function(data) {
                $("#batch_no").html(data);
                $("#batch_no").trigger("chosen:updated");
            }
        });
    }

    function get_users(common_id) {

        toggleRoute();

        $.ajax({
            url: "ajax/get_users.php",
            type: "POST",
            data: {
                common_id: common_id
            },
            success: function(data) {
                $("#user_id").html(data);
                $("#user_id").trigger("chosen:updated");
            }
        });
    }

    $("#area_name").keyup(function() {

        let term = $(this).val();

        if (term.length < 1) {
            $("#area_list").hide();
            return;
        }

        $.ajax({
            url: "ajax/get_area_list.php",
            type: "POST",
            data: {
                term: term
            },
            success: function(data) {

                if (data.trim() != '') {
                    $("#area_list").html(data).show();
                } else {
                    $("#area_list").hide();
                }
            }
        });

    });

    $(document).on("click", ".area-item", function() {

        $("#area_id").val($(this).data("id"));
        $("#area_name").val($(this).data("name"));

        $("#area_list").hide();
    });

    $(document).click(function(e) {

        if (!$(e.target).closest('#area_name,#area_list').length) {
            $("#area_list").hide();
        }

    });


    function add_account() {

        $('#account_name').val('');
        $('#mobile_no').val('');
        $('#owner_name').val('');
        $('#o_mobile_no').val('');
        $('#batch_no').val('').trigger('chosen:updated');
        $('#user_id').val('').trigger('chosen:updated');
        $('#common_id').val('7').trigger('chosen:updated');
        get_users(7);
        $('#class').val('');
        $('#area_name').val('');
        $('#area_id').val('');
        $('#accountNameAdd').modal('show');
    }

    function save_account(force_save = 0) {

        var user_id = $('#user_id').val().trim();
        var account_name = $('#account_name').val().trim();
        var mobile_no = $('#mobile_no').val().trim();
        var owner_name = $('#owner_name').val().trim();
        var o_mobile_no = $('#o_mobile_no').val().trim();
        var common_id = $('#common_id').val();
        var batch_no = $('#batch_no').val();
        var mclass = $('#class').val();
        var area_name = $('#area_name').val().trim();
        var area_id = $('#area_id').val();

        var electrician_name = $('#electrician_name').val().trim();
        var electrician_mobile = $('#electrician_mobile').val().trim();
        var account_id_map = $('#account_id_map').val().trim();

        var user_type = $("#user_id option:selected").data("type");

        if (user_id == '') {
            alert('Select Referred By');
            $('#user_id').focus();
            return false;
        }

        if (common_id == '') {
            alert('Select Counter Type');
            return false;
        }

        // Electrician Validation
        if (common_id == '6') {

            if (electrician_name == '') {
                alert('Enter Electrician Name');
                $('#electrician_name').focus();
                return false;
            }

            if (electrician_mobile == '') {
                alert('Enter Electrician Whatsapp No.');
                $('#electrician_mobile').focus();
                return false;
            }

            if (account_id_map == '') {
                alert('Select a Counter Name');
                $('#account_id_map').focus();
                return false;
            }

        } else {

            if (account_name == '') {
                alert('Enter Customer Name');
                $('#account_name').focus();
                return false;
            }

            if (mobile_no == '') {
                alert('Enter Whatsapp No.');
                $('#mobile_no').focus();
                return false;
            }

            if (user_type == 'sales' && common_id == '7' && batch_no == '') {
                alert('Select Route');
                $('#batch_no').focus();
                return false;
            }

            if (user_type == 'sales' && common_id == '7' && mclass == '') {
                alert('Select a Class');
                $('#mclass').focus();
                return false;
            }

            if (area_name == '') {
                alert('Enter Area Name');
                $('#area_name').focus();
                return false;
            }
        }

        $.ajax({
            url: "ajax_save_account.php",
            type: "POST",
            data: {
                user_id: user_id,
                account_name: account_name,
                mobile_no: mobile_no,
                owner_name: owner_name,
                o_mobile_no: o_mobile_no,
                common_id: common_id,
                batch_no: batch_no,
                class: mclass,
                area_name: area_name,
                area_id: area_id,
                electrician_name: electrician_name,
                electrician_mobile: electrician_mobile,
                account_id_map: account_id_map,
                force_save: force_save
            },
            success: function(res) {
                res = $.trim(res);
                if (res == 'duplicate') {
                    alert('Customer/Electrician already exists');
                    return false;
                }

                if (res == 'duplicate_name') {

                    var duplicateName = (common_id == '6') ?
                        electrician_name :
                        account_name;

                    var typeName = (common_id == '6') ?
                        'electrician' :
                        'customer';

                    if (confirm(
                            'An ' + typeName + ' with the name "' + duplicateName +
                            '" already exists.\n\nDo you still want to save?'
                        )) {
                        save_account(1);
                    }

                    return false;
                }
                if (res > 0) {

                    $('#accountNameAdd').modal('hide');
                    get_account_list(res);

                    $('#user_id').val('').trigger('chosen:updated');
                    $('#account_name').val('');
                    $('#mobile_no').val('');
                    $('#owner_name').val('');
                    $('#o_mobile_no').val('');
                    $('#electrician_name').val('');
                    $('#electrician_mobile').val('');
                    $('#account_id_map').val('').trigger('chosen:updated');
                    $('#common_id').val('7').trigger('chosen:updated');
                    $('#batch_no').val('').trigger('chosen:updated');
                    $('#area_name').val('');
                    $('#area_id').val('');

                } else {
                    alert('Unable to save record');
                }
            }
        });
    }


    function get_account_list(account_id = '') {
        if (account_id > 0) {
            location = '?account_id=' + account_id;
        }
    }

    function get_url1(account_id) {
        if (account_id > 0) {
            location = '?account_id=' + account_id;
        }
    }

    $(document).ready(function() {
        $(".chosen-select").chosen({
            width: "100%"
        });

        let gst_percent = parseFloat('<?= $gst_percent ?>') || 0;
        let is_gst = checkExistingProductGST();
        if (gst_percent > 0) {
            $('#gst_enabled').prop('checked', true);
            $('#gst_overall').prop('checked', true);
        } else if (is_gst == 1) {
            $('#gst_enabled').prop('checked', true);
        }

        fetch_data('<?php echo $keyvalue ?>');
        toggleGSTMode();
    });

    function get_product_details(product_id) {
        jQuery.ajax({
            type: 'POST',
            url: 'get_product_details.php',
            data: {
                product_id: product_id
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#rate').val(res.rate);
                    $('#unit_id').val(res.unit_id);
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
            alert('Please select ustomer name first');
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
                brand_id: brand_id,
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
        let discP = parseFloat($('#discount').val()) || 0;

        let gst_percent = parseFloat($('#gst_percent').val()) || 0;
        let gst_use = $('#gst_productwise').is('checked', true);
        let taxtype = $('#taxtype').val();

        let disc_per_unit = (rate * discP) / 100;
        let price_after_disc = Math.max(rate - disc_per_unit, 0);
        $('#price_after_disc').val(price_after_disc.toFixed(2));
        let sub_total = price_after_disc * qty;
        let discount_amt = disc_per_unit * qty;

        let price_after_disc_gst = price_after_disc * 1.18;
        let taxable = 0;
        let gst_amt = 0;
        let net_amt = 0;

        if (taxtype == 'exclusive') {
            taxable = sub_total;
            gst_amt = (taxable * gst_percent) / 100;
            net_amt = taxable + gst_amt;
        } else if (taxtype == 'inclusive') {
            net_amt = sub_total;
            taxable = (net_amt * 100) / (100 + gst_percent);
            gst_amt = net_amt - taxable;
        }

        $('#price_after_disc_gst').val(price_after_disc_gst.toFixed(2));
        $('#sub_total').val(sub_total.toFixed(2));
        $('#total_amt').val(taxable.toFixed(2));
        $('#discount_amt').val(discount_amt.toFixed(2));
        $('#gst_amt').val(gst_amt.toFixed(2));
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
        let enabled = $('#gst_enabled').is(':checked');
        let currentMode = '';

        if (enabled) {
            currentMode = $('input[name="gst_mode"]:checked').val() || '';
        }

        jQuery.ajax({
            type: 'POST',
            url: 'fetch_order_product.php',
            data: {
                account_id: account_id,
                company_id: company_id,
                transaction_id: transaction_id,
                currentMode,
                type: type,
            },
            dataType: 'html',
            success: function(data) {
                document.getElementById("fetch_data").innerHTML = data;
                lockGSTModeIfProductsExist();
                calculateGST();
            }
        });
    }

    function EditProduct(brand_id, category_id, product_id, unit_id, unit_name, qty, rate, sub_total, discount, total_amt, tran_detail_id, gst_id, taxtype, net_amt) {
        $('#brand_id').val(brand_id).trigger('chosen:updated');
        load_category_by_brand(brand_id, category_id);
        get_products(category_id, product_id);
        $('#qty').val(qty);
        $('#rate').val(rate);
        $('#unit_id').val(unit_id);
        $('#unit_name').val(unit_name);
        $('#sub_total').val(sub_total);
        $('#discount').val(discount);
        $('#total_amt').val(total_amt);
        $('#gst_id').val(gst_id).trigger('chosen:updated');
        $('#net_total').val(net_amt);
        $('#m_tran_detail_id').val(tran_detail_id);
        $('#add_btn').val('Update');
        calculate_total();
    }

    function saveLastSelection() {
        localStorage.setItem('last_brand_id', $('#brand_id').val());
        localStorage.setItem('last_category_id', $('#category_id').val());
    }

    function restoreLastSelection() {
        let lastCategory = localStorage.getItem('last_category_id');
        let lastBrand = localStorage.getItem('last_brand_id');

        if (lastCategory) {
            $('#category_id').val(lastCategory).trigger('change');
        }

        setTimeout(() => {
            if (lastBrand) {
                $('#brand_id').val(lastBrand).trigger('change');
            }
        }, 300);
    }


    function add_product() {
        let update_mrp = document.getElementById('update_mrp').checked ? 1 : 0;
        let product_id = document.getElementById('product_id').value.trim();
        let category_id = document.getElementById('category_id').value;
        let brand_id = document.getElementById('brand_id').value;
        let unit_id = document.getElementById('unit_id').value;
        let unit_name = document.getElementById('unit_name').value;
        let qty = document.getElementById('qty').value.trim();
        let rate = document.getElementById('rate').value.trim();
        let price_after_disc = document.getElementById('price_after_disc').value.trim();
        let discount = document.getElementById('discount').value;
        let total_amt = document.getElementById('total_amt').value.trim();
        let gst_amt = document.getElementById('gst_amt').value.trim();
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


        jQuery.ajax({
            type: 'POST',
            url: 'add_product.php',
            data: {
                unit_name: unit_name,
                product_id: product_id,
                gst_amt: gst_amt,
                category_id: category_id,
                brand_id: brand_id,
                unit_id: unit_id,
                qty: qty,
                rate: rate,
                price_after_disc: price_after_disc,
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
                type: type,
                update_mrp: update_mrp
            },
            dataType: 'html',
            success: function(data) {
                if (data == 1 || data == 2) {
                    fetch_data(transaction_id);
                } else if (data == 3) {
                    alert('This product already added. Please update the existing product.');
                    return;
                }

                restoreLastSelection();
                $('#product_id').val('').trigger('chosen:updated');
                $('#qty').val("");
                $('#add_btn').val('Add');
                $('#unit_id').val('');
                $('#gst_amt').val('');
                $('#unit_name').val('');
                $('#rate').val('');
                $('#net_total').val('');
                $('#price_after_disc').val('');
                $('#discount').val('');
                $('#discount_amt').val('');
                $('#sub_total').val('');
                $('#total_amt').val('');
                $('#m_tran_detail_id').val(0);
            }
        });
    }

    function calculateGST() {

        if ($('#gst_percent_hidden').length === 0) return;

        let net_total = parseFloat($('#net_total_amt').val()) || 0;
        let gst_percent = parseFloat($('#gst_percent_hidden').val()) || 0;
        let freight = parseFloat($('#freight_charges').val()) || 0;

        let taxable_amount = net_total + freight;

        let gst_amount = (taxable_amount * gst_percent) / 100;
        let cgst = gst_amount / 2;
        let sgst = gst_amount / 2;

        let grand_total = taxable_amount + gst_amount;

        let rounded_total = Math.round(grand_total);
        let round_off = rounded_total - grand_total;

        if ($('#taxable_amount').length)
            $('#taxable_amount').val(taxable_amount.toFixed(2));

        if ($('#taxable_amount_display').length)
            $('#taxable_amount_display').text(taxable_amount.toFixed(2));

        if ($('#cgst_display').length)
            $('#cgst_display').text(cgst.toFixed(2));

        if ($('#sgst_display').length)
            $('#sgst_display').text(sgst.toFixed(2));

        if ($('#grand_total_display').length)
            $('#grand_total_display').text(rounded_total.toFixed(2));

        $('#cgst').val(cgst.toFixed(2));
        $('#sgst').val(sgst.toFixed(2));
        $('#round_off').val(round_off.toFixed(2));
        $('#grand_total').val(rounded_total.toFixed(2));
        $('#gst_percent_hidden').val(gst_percent);
    }
</script>

</html>