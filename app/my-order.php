<?php include("appsession.php");
$pagename    = 'my-order.php';
$title       = 'Order Entry';
$tblname     = 'transaction_entry';
$tblpkey     = 'transaction_id';
$keyvalue    = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$account_id    = (isset($_GET["account_id"])) ? $obj->test_input($_GET["account_id"]) : 0;
$imgpath     = "uploads/daily_entry/";
$type        = 'order';
$billno      = $obj->getcode($tblname, "billno", "1=1 and type='$type'");
$data        = $obj->getRouteDashboardData($loginid, $companyid);
$route_plan_id = $data['route_plan_id'];

if (isset($_POST['add_counter'])) {
    $batch_no     = $obj->test_input($_POST['route_planid']);
    $account_name = $obj->test_input($_POST['account_name']);
    $mobile_no    = $obj->test_input($_POST['mobile_no']);
    $address      = $obj->test_input($_POST['address']);
    $area_id      = $obj->test_input($_POST['area_id']);
    $common_id    = $obj->test_input($_POST['common_id']);
    $class        = $obj->test_input($_POST['class']);
    $acc_type     = ($common_id == -1) ? "employee" : "customer";

    if ($account_name == "" || $area_id == "" || $class == "") {
        echo "error";
        exit;
    }

    $count = $obj->getvalfield("account", "count(*)", "account_name='$account_name' AND area_id='$area_id'");
    if ($count > 0) {
        echo "duplicate";
        exit;
    }

    $account_id = $obj->insert_record_lastid("account", [
        'account_name' => $account_name,
        'mobile_no'    => $mobile_no,
        'address'      => $address,
        'common_id'    => $common_id,
        'area_id'      => $area_id,
        'class'        => $class,
        'status'       => "inactive",
        'type'         => $acc_type,
        'status1'      => 0,
        'createdby'    => $loginid,
        'companyid'    => $companyid,
        'ipaddress'    => $ipaddress,
        'createdate'   => date('Y-m-d H:i:s')
    ]);

    if ($account_id > 0) {
        $sequence = $obj->getvalfield("route_counter", "IFNULL(MAX(sequence),0)+1", "batch_no='$batch_no'");
        $obj->insert_record("route_counter", [
            'batch_no'   => $batch_no,
            'account_id' => $account_id,
            'sequence'   => $sequence,
            'createdate' => date('Y-m-d H:i:s'),
            'ipaddress'  => $ipaddress,
            'companyid'  => $companyid,
            'createdby'  => $loginid
        ]);
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

if (isset($_POST['account_id'])) {
    $keyvalue    = $obj->test_input($_POST['transaction_id']);
    $account_id  = $obj->test_input($_POST['account_id']);
    $billno      = $obj->test_input($_POST['billno']);
    $billdate    = $obj->test_input($_POST['billdate']);
    $remark      = $obj->test_input($_POST['remark']);
    $grand_total = $obj->test_input($_POST['grand_total']);
    $latitude    = $obj->test_input($_POST['latitude']);
    $longitude   = $obj->test_input($_POST['longitude']);
    $address     = $obj->test_input($_POST['address']);
    $overall_gst_pct = $obj->test_input($_POST['overall_gst_pct']);
    $overall_gst_amt = $obj->test_input($_POST['overall_gst_amt']);


    $form_data = [
        "account_id"       => $account_id,
        "type"             => $type,
        "billno"           => $billno,
        "billdate"         => $billdate,
        "remark"           => $remark,
        "grand_total"      => $grand_total,
        "gst_percent"  => $overall_gst_pct,
        "overall_gst_amt"  => $overall_gst_amt,
        'longitude'        => $longitude,
        'latitude'         => $latitude,
        'address'          => $address,
        "createdby"        => $loginid,
        'companyid'        => $companyid,
        "ipaddress"        => $ipaddress,
    ];

    if ($keyvalue == 0) {

        $form_data["createdate"] = $createdate;

        $lastid = $obj->insert_record_lastid($tblname, $form_data);

        $obj->update_record(
            'transaction_details',
            ['transaction_id' => 0, 'type' => $type, 'account_id' => $account_id],
            ['transaction_id' => $lastid]
        );

        echo "success";
    } else {

        $form_data["lastupdated"] = $createdate;

        $obj->update_record($tblname, [$tblpkey => $keyvalue], $form_data);

        echo "updated";
    }

    die;
}
if ($keyvalue > 0) {
    $sqledit     = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
    $account_id = $sqledit['account_id'];
    $billdate    = $sqledit['billdate'];
    $remark      = $sqledit['remark'];
    $billno      = $sqledit['billno'];
} else {
    $billdate    = date('Y-m-d');
    $remark      = "";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= $title ?></title>
    <?php include("inc/css-file.php"); ?>
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>
        <div class="container">
            <div class="card border-0 shadow-lg mb-3">
                <div class="row">
                    <div class="col-6">
                        <h4 class="mb-0">Order Entry</h4>
                    </div>
                    <div class="col-6 text-end">
                        <a href="order-list.php" class="btn btn-sm btn-primary">Order List</a>
                    </div>
                    <div class="col-12 mb-2 mt-2">
                        <hr class="m-0">
                    </div>
                    <div class="col-12 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">
                                Counter Name <span class="text-danger fw-bold">*</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-primary p-1" onclick="openModal()">
                                + Add
                            </button>
                        </div>
                        <select class="form-select chosen-select" name="account_id" id="account_id"
                            onchange="onCounterChange(this.value);"
                            <?= $keyvalue > 0 ? 'disabled' : '' ?>>
                            <option value="">Select</option>
                            <?php
                            $res = $obj->executequery("SELECT DISTINCT a.account_id, a.account_name,
                                   cm.common_name AS account_type, am.area_name
                            FROM route_plan rp
                            JOIN route_counter rc ON rc.batch_no = rp.batch_no
                            JOIN account a        ON a.account_id = rc.account_id
                            LEFT JOIN common_master cm ON cm.common_id = a.common_id AND cm.type = 'acc_type'
                            LEFT JOIN area_master am   ON am.area_id = a.area_id
                            WHERE rp.companyid = '$companyid' AND rc.companyid = '$companyid'
                            ORDER BY a.account_name ASC
                        ");
                            foreach ($res as $key) {
                                echo "<option value='{$key['account_id']}'>"
                                    . "{$key['account_name']} [{$key['account_type']}] / {$key['area_name']}"
                                    . "</option>";
                            } ?>
                        </select>
                        <input type="hidden" name="account_id" id="account_id" value="<?= $account_id ?>" <?= $keyvalue == 0 ? 'disabled' : '' ?>>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="form-label">Order No. <span class="text-danger fw-bold">*</span></label>
                        <input class="form-control form-control-sm" name="billno" id="billno" value="<?= $billno ?>" readonly>
                    </div>
                    <div class="col-6 mb-2">
                        <label class="form-label">Order Date <span class="text-danger fw-bold">*</span></label>
                        <input class="form-control form-control-sm" type="date" name="billdate" id="billdate" value="<?= $billdate ?>">
                    </div>
                    <div class="col-8">
                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" id="gst_enabled" onchange="toggleGSTMode()">
                                <label class="form-check-label small fw-semibold" for="gst_enabled">Apply GST?</label>
                            </div>
                            <div id="gst_type_options" style="display:none;">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="gst_mode" id="gst_productwise"
                                        value="productwise" onchange="toggleGSTMode()">
                                    <label class="form-check-label small" for="gst_productwise">Per Item</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="gst_mode" id="gst_overall"
                                        value="overall" onchange="toggleGSTMode()">
                                    <label class="form-check-label small" for="gst_overall">Overall</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label">Remark</label>
                        <input class="form-control form-control-sm" name="remark" id="remark" value="<?= $remark ?>" autocomplete="off">
                    </div>
                    <input type="hidden" name="<?= $tblpkey ?>" id="<?= $tblpkey ?>" value="<?= $keyvalue ?>">
                </div>
            </div>
            <div class="card border-0 shadow-sm p-2 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Order Items</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#product_modal">
                        <i class="bi bi-plus-lg"></i> Add Product
                    </button>
                </div>
                <!-- Brand + Category -->
                <div class="row g-2 mb-1">

                    <div class="col-6">
                        <label class="form-label" for="">Brand</label>
                        <select id="brand_id" class="form-select form-select-sm"
                            onchange="load_category_by_brand(this.value)">
                            <option value="">Brand</option>
                            <?php
                            $sql = $obj->executequery("SELECT * FROM category_master WHERE type='brand' ORDER BY cat_id DESC");
                            foreach ($sql as $key) {
                                echo "<option value='{$key['cat_id']}'>{$key['cat_name']}</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-6"> <label class="form-label" for="">Category</label>
                        <select id="category_id" class="form-select form-select-sm"
                            onchange="get_products(this.value)">
                            <option value="">Category</option>
                        </select>
                    </div>
                </div>

                <!-- Product -->
                <div class="mb-1">
                    <select id="product_id" class="form-select form-select-sm chosen-select"
                        onchange="get_product_details(this.value)">
                        <option value="">Select Product</option>

                    </select>
                </div>

                <!-- Qty + Rate -->
                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="small-input">
                            <label for="">Unit :</label>
                            <input type="text" id="unit_name" class="form-control form-control-sm text-center" readonly>
                            <input type="hidden" id="unit_id">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small-input">
                            <label for="">Qty :</label>
                            <input type="number" id="qty" class="form-control form-control-sm text-center fw-bold">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small-input">
                            <label for="">Rate :</label>
                            <input type="number" id="rate" class="form-control form-control-sm text-center">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small-input">
                            <label for="">Disc % :</label>
                            <input type="number" id="discount" class="form-control form-control-sm text-center">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="small-input">
                            <label for="">Final Price :</label>
                            <input type="number" id="price_after_disc"
                                class="form-control form-control-sm text-center fw-bold text-success"
                                readonly>
                        </div>
                    </div>
                    <div class="col-6" id="gst_block" style="display:none;">
                        <select id="gst_id" class="form-select form-select-sm" onchange="recalcTotal();">
                            <option value="">-- Select GST --</option>
                            <?php
                            $sql = $obj->executequery("select * from gst_master");
                            foreach ($sql as $key) { ?>
                                <option value="<?= $key['gst_id'] ?>"
                                    data-sgst="<?= $key['sgst'] ?>"
                                    data-cgst="<?= $key['cgst'] ?>"
                                    data-percent="<?= $key['sgst'] + $key['cgst'] ?>">
                                    <?= $key['gst_name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Action -->
                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="form-check">
                            <input type="checkbox" id="update_mrp" class="form-check-input" value="1">
                            <label class="form-check-label fw-semibold" for="checkDefault">
                                Update MRP
                            </label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold pt-1 text-end">
                            Total : ₹<span id="total_amt">0.00</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary btn-sm w-100" id="add_btn" onclick="save_order_details()">ADD</button>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" id="sub_total_hidden">
                <input type="hidden" id="discount_amt_hidden">
                <input type="hidden" id="gst_percent">
                <input type="hidden" id="gst_amt">
                <input type="hidden" id="taxable_amt">
                <input type="hidden" id="total_amt_hidden">
                <input type="hidden" id="tran_detail_id">

            </div>
            <div class="row" id="show_order"></div>

        </div>
    </section>

    <!-- ── Add Counter Modal ── -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Counter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="form-label">Route Name <span class="text-danger fw-bold">*</span></label>
                            <select id="route_planid" class="chosen-select form-control form-control-sm">
                                <option value="">-- Select Route --</option>
                                <?php
                                $sql = $obj->executequery("
                                SELECT R.batch_no, R.route_name,
                                       GROUP_CONCAT(R.day_of_week ORDER BY FIELD(day_of_week,
                                           'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')
                                           SEPARATOR ', ') AS days
                                FROM route AS R
                                LEFT JOIN route_plan AS RP ON R.batch_no = RP.batch_no
                                WHERE R.companyid='$companyid' AND RP.sales_executive_id='$loginid'
                                GROUP BY R.batch_no, R.route_name
                                ORDER BY R.route_name ASC
                            ");
                                foreach ($sql as $key) {
                                    echo "<option value='{$key['batch_no']}'>{$key['route_name']} [{$key['days']}]</option>";
                                } ?>
                            </select>
                            <script>
                                document.getElementById('route_planid').value = '<?= $route_plan_id ?>';
                            </script>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Counter Name <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control" id="m_account_name" placeholder="Counter Name">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="m_mobile_no" placeholder="10-digit mobile"
                                maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Class <span class="text-danger fw-bold">*</span></label>
                            <select id="m_class" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Area <span class="text-danger fw-bold">*</span></label>
                            <select id="m_area_id" class="chosen-select form-control form-control-sm">
                                <option value="">-- Select Area --</option>
                                <?php
                                $sql = $obj->executequery("SELECT area_id, area_name FROM area_master ORDER BY area_name ASC");
                                foreach ($sql as $key) {
                                    echo "<option value='{$key['area_id']}'>{$key['area_name']}</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">Counter Type</label>
                            <select id="m_common_id" class="chosen-select form-control form-control-sm">
                                <option value="">-- Select Type --</option>
                                <?php
                                $sql = $obj->executequery("SELECT common_id, common_name FROM common_master WHERE type='acc_type' ORDER BY common_id ASC");
                                foreach ($sql as $key) {
                                    echo "<option value='{$key['common_id']}'>{$key['common_name']}</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="m_address" placeholder="Enter Address" rows="2"></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-primary px-4" onclick="add_counter();">+ Add Counter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div id="loader" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.7);display:none;z-index:9999;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:40px;height:40px;border:4px solid #ccc;border-top:4px solid #007bff;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
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
                            <select id="p_brand_id" class="form-select form-select-sm" onchange="pm_load_category(this.value)">
                                <option value="">-- Select Brand --</option>
                                <?php
                                $brands = $obj->executequery("SELECT * FROM category_master WHERE type='brand' ORDER BY cat_id DESC");
                                foreach ($brands as $b) {
                                    echo "<option value='{$b['cat_id']}'>{$b['cat_name']}</option>";
                                } ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-12">
                            <label class="form-label">Category <span class="text-danger fw-bold">*</span></label>
                            <select id="p_category_id" class="form-select form-select-sm">
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
    <style>
        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
    </style>

    <?php include("inc/js-file.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });
            document.getElementById('account_id').value = '<?= $account_id ?>';
            $('#account_id').trigger('chosen:updated');
            fetch_data();
        });

        $(document).on('input change', '#qty, #rate, #discount', function() {
            recalcTotal();
        });

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

        function resetProductFields() {
            $('#unit_name, #qty, #rate, #price_after_disc').val('');
            $('#total_amt_hidden, #taxable_amt, #gst_amt').val(0);
            $('#sub_total_hidden, #discount_amt_hidden').val(0);
            $('#total_amt').text('0.00');
            $('#unit_id').val(0);
            $('#discount').val(0);
            $('#tran_detail_id').val(0);
            $('#gst_percent').val(0);
            $('#add_btn').text('ADD');
            $('#gst_id').val('').trigger('change');
            restoreLastSelection();
            $('#product_id').val('').trigger('chosen:updated');
        }

        $(document).on('change', '#gst_id', function() {
            let gstMode = $('input[name="gst_mode"]:checked').val();
            if (gstMode === 'overall') {
                updateOverallGST();
            } else {
                recalcTotal();
            }
        });

        function toggleGSTMode() {
            let enabled = $('#gst_enabled').is(':checked');

            if (!enabled) {
                $('#gst_type_options').hide();
                $('#gst_block').hide();
                $('#overall_gst_row').hide();
                $('input[name="gst_mode"]').prop('checked', false);
                $('#gst_id').val('');
                $('#gst_percent, #gst_amt, #taxable_amt').val(0);
                updateOverallGST();
                recalcTotal();
                return;
            }

            $('#gst_type_options').show();

            let hasProductGST = checkExistingProductGST();

            if (hasProductGST) {
                $('#gst_overall').prop('disabled', true)
                    .closest('.form-check')
                    .attr('title', 'Items already have product-wise GST');
                $('#gst_productwise').prop('disabled', false);

                if ($('input[name="gst_mode"]:checked').val() !== 'productwise') {
                    $('#gst_productwise').prop('checked', true);
                }
            } else {
                $('#gst_overall').prop('disabled', false);
                $('#gst_productwise').prop('disabled', false);

                if (!$('input[name="gst_mode"]:checked').val()) {
                    $('#gst_productwise').prop('checked', true);
                }
            }

            let currentMode = $('input[name="gst_mode"]:checked').val();

            if (currentMode === 'productwise') {
                $('#gst_block').show();
                $('#overall_gst_row').hide();
                recalcTotal();
                updateOverallGST();

            } else if (currentMode === 'overall') {
                $('#gst_block').hide();
                $('#gst_percent').val(0);
                $('#gst_amt').val(0);
                $('#overall_gst_row').show();
                recalcTotal();
                updateOverallGST();

            } else {
                $('#gst_block').hide();
                $('#overall_gst_row').hide();
                $('#gst_percent').val(0);
                $('#gst_amt').val(0);
                recalcTotal();
                updateOverallGST();
            }
        }

        function checkExistingProductGST() {
            let found = false;
            $('#show_order [data-gst-percent]').each(function() {
                if (parseFloat($(this).data('gst-percent')) > 0) {
                    found = true;
                    return false;
                }
            });
            return found;
        }

        /* ── Per-line recalc ────────────────────────────── */
        function calculate_total() {

            let qty = parseFloat($('#qty').val()) || 0;
            let rate = parseFloat($('#rate').val()) || 0;
            let discP = parseFloat($('#discount').val()) || 0;

            // GST %
            let gst_percent = 0;

            if ($('#gst_id').val() != '') {
                gst_percent = parseFloat(
                    $('#gst_id option:selected').text().match(/\d+(\.\d+)?/)
                ) || 0;
            }

            let taxtype = $('#taxtype').val();

            // Discount Per Unit
            let disc_per_unit = (rate * discP) / 100;

            // Price After Discount Per Unit
            let price_after_disc = Math.max(rate - disc_per_unit, 0);

            $('#price_after_disc').val(price_after_disc.toFixed(2));

            // Sub Total
            let sub_total = price_after_disc * qty;

            // Total Discount
            let discount_amt = disc_per_unit * qty;

            let taxable = 0;
            let gst_amt = 0;
            let net_amt = 0;

            // EXCLUSIVE GST
            if (taxtype == 'exclusive') {

                taxable = sub_total;

                gst_amt = (taxable * gst_percent) / 100;

                net_amt = taxable + gst_amt;
            }

            // INCLUSIVE GST
            else if (taxtype == 'inclusive') {

                net_amt = sub_total;

                taxable = (net_amt * 100) / (100 + gst_percent);

                gst_amt = net_amt - taxable;
            }

            // Set Values
            $('#sub_total').val(sub_total.toFixed(2));

            $('#discount_amt').val(discount_amt.toFixed(2));

            $('#gst_amt').val(gst_amt.toFixed(2));

            $('#net_total').val(net_amt.toFixed(2));
        }
        /* ── Overall GST on grand total ─────────────────── */
        function updateOverallGST() {
            let gstEnabled = $('#gst_enabled').is(':checked');
            let gstMode = $('input[name="gst_mode"]:checked').val();
            let subGrand = parseFloat($('#grand_total_base').val()) || 0;

            if (gstEnabled && gstMode === 'overall') {
                let gst_pct = parseFloat($('#og_pct').text()) || 0;

                if (gst_pct > 0) {
                    let gst_amt = round2(subGrand * gst_pct / 100);
                    let grandTotal = round2(subGrand + gst_amt);

                    $('#og_amt').text(gst_amt.toFixed(2));
                    $('#overall_gst_row').show();
                    $('#grand_total_display').text(grandTotal.toFixed(2));
                    $('#grand_total').val(grandTotal);
                } else {
                    $('#overall_gst_row').show();
                    $('#grand_total_display').text(subGrand.toFixed(2));
                    $('#grand_total').val(subGrand);
                }
            } else {
                $('#overall_gst_row').hide();
                $('#grand_total_display').text(subGrand.toFixed(2));
                $('#grand_total').val(subGrand);
            }
        }

        function round2(num) {
            return Math.round((num + Number.EPSILON) * 100) / 100;
        }

        function onCounterChange(val) {
            location = "?account_id=" + val;
        }


        function fetch_data() {
            let transaction_id = $('#<?= $tblpkey ?>').val();
            let account_id = $('#account_id').val();
            $.ajax({
                type: 'POST',
                url: 'show_order_details.php',
                data: {
                    account_id,
                    transaction_id,
                    type: '<?= $type ?>'
                },
                success(data) {
                    $('#show_order').html(data);
                    toggleGSTMode();
                    updateOverallGST();
                }
            });
        }

        function delete_record(id) {
            $.ajax({
                type: 'POST',
                url: 'delete_master.php',
                data: {
                    id,
                    tblname: 'transaction_details',
                    tblpkey: 'tran_detail_id'
                },
                success() {
                    fetch_data();
                }
            });
        }

        function load_category_by_brand(brand_id, category_id = 0) {
            let account_id = $('#account_id').val();
            if (!account_id) {
                Swal.fire('Warning', 'Select Counter Name first', 'warning');
                $('#brand_id').val('').trigger('chosen:updated');
                return;
            }
            if (brand_id) {
                $.ajax({
                    url: '../admin/get_category.php',
                    type: 'POST',
                    data: {
                        brand_id,
                        category_id
                    },
                    success(data) {
                        $('#category_id').html(data).trigger('chosen:updated');
                    }
                });
            } else {
                $('#category_id').html("<option value=''>Select</option>").trigger('chosen:updated');
            }
        }

        function get_products(category_id, product_id = 0) {
            let brand_id = $('#brand_id').val();
            if (!brand_id) {
                Swal.fire('Warning', 'Select Brand first', 'warning');
                $('#category_id').val('').trigger('chosen:updated');
                return;
            }
            $.ajax({
                type: 'POST',
                url: '../admin/get_product_combo.php',
                data: {
                    category_id,
                    brand_id,
                    product_id
                },
                success(data) {
                    $('#product_id').html(data).trigger('chosen:updated');
                }
            });
        }

        function get_product_details(product_id) {
            if (!product_id) return;

            $.ajax({
                type: 'POST',
                url: '../admin/get_product_details.php',
                data: {
                    product_id
                },
                dataType: 'json',
                success(res) {
                    if (res.status === 'success') {
                        $('#rate').val(res.rate);
                        $('#unit_id').val(res.unit_id);
                        $('#unit_name').val(res.unit_name);
                    } else {
                        Swal.fire('Error', 'Product details not found', 'error');
                    }
                }
            });
        }


        function pm_load_category(brand_id) {
            const catSelect = document.getElementById('p_category_id');
            catSelect.innerHTML = '<option value="">-- Select Category --</option>';
            if (!brand_id) return;

            $.ajax({
                url: '../admin/get_category.php',
                type: 'POST',
                data: {
                    brand_id: brand_id
                },
                success: function(res) {
                    catSelect.innerHTML = res;
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
                        url: '../admin/get_category.php',
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
                    document.getElementById('p_brand_id').value = '';
                    document.getElementById('p_category_id').innerHTML = '<option value="">-- Select Category --</option>';
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

        function recalcTotal() {
            let qty = parseFloat($('#qty').val()) || 0;
            let rate = parseFloat($('#rate').val()) || 0;
            let discP = parseFloat($('#discount').val()) || 0;

            let disc_per_unit = (rate * discP) / 100;
            let price_after_disc = Math.max(rate - disc_per_unit, 0);
            $('#price_after_disc').val(price_after_disc.toFixed(2));

            let sub_total = round2(price_after_disc * qty);
            let discount_amt = round2(disc_per_unit * qty);
            let taxable = sub_total;

            let gstEnabled = $('#gst_enabled').is(':checked');
            let gstMode = $('input[name="gst_mode"]:checked').val();

            let gst_pct = 0,
                gst_amt = 0;

            if (gstEnabled && gstMode === 'productwise') {
                let opt = $('#gst_id option:selected');
                let sgst = parseFloat(opt.data('sgst')) || 0;
                let cgst = parseFloat(opt.data('cgst')) || 0;
                gst_pct = sgst + cgst;
                gst_amt = round2(taxable * gst_pct / 100);
            }
            // overall or none → gst_pct/gst_amt stay 0 on the line

            $('#gst_percent').val(gst_pct);
            $('#sub_total_hidden').val(sub_total);
            $('#discount_amt_hidden').val(discount_amt);
            $('#taxable_amt').val(taxable);
            $('#gst_amt').val(gst_amt);

            let total = round2(taxable + gst_amt);
            $('#total_amt_hidden').val(total);
            $('#total_amt').text(total.toFixed(2));
        }

        /* ── Add product to order ───────────────────────── */
        function save_order_details() {
            let account_id = $('#account_id').val();
            let category_id = $('#category_id').val();
            let product_id = $('#product_id').val();
            let brand_id = $('#brand_id').val();
            let unit_id = $('#unit_id').val();
            let unit_name = $('#unit_name').val();
            let qty = $('#qty').val();
            let rate = $('#rate').val();
            let price_after_disc = $('#price_after_disc').val();
            let discount = parseFloat($('#discount').val()) || 0;
            let discount_amt = $('#discount_amt_hidden').val(); // ← fixed
            let sub_total = $('#sub_total_hidden').val(); // ← fixed
            let taxable_amt = parseFloat($('#taxable_amt').val()) || 0;
            let total_amt = $('#total_amt_hidden').val();
            let tran_detail_id = $('#tran_detail_id').val() || 0;
            let transaction_id = $('#transaction_id').val();
            let update_mrp = document.getElementById('update_mrp').checked ? 1 : 0;
            let gstEnabled = $('#gst_enabled').is(':checked');
            let gstMode = $('input[name="gst_mode"]:checked').val();

            // Only send GST on the line if productwise
            let gst_id = (gstEnabled && gstMode === 'productwise') ? ($('#gst_id').val() || 0) : 0;
            let gst_percent = (gstEnabled && gstMode === 'productwise') ? (parseFloat($('#gst_percent').val()) || 0) : 0;
            let gst_amt = (gstEnabled && gstMode === 'productwise') ? (parseFloat($('#gst_amt').val()) || 0) : 0;

            if (!account_id) {
                Swal.fire('Warning', 'Select Counter Name', 'warning');
                return;
            }
            if (!category_id) {
                Swal.fire('Warning', 'Select Category', 'warning');
                return;
            }
            if (!product_id) {
                Swal.fire('Warning', 'Select Product', 'warning');
                return;
            }
            if (!brand_id) {
                Swal.fire('Warning', 'Select Brand', 'warning');
                return;
            }
            if (!qty || qty <= 0) {
                Swal.fire('Warning', 'Enter valid Quantity', 'warning');
                return;
            }
            if (!rate || rate <= 0) {
                Swal.fire('Warning', 'Enter valid Rate', 'warning');
                return;
            }
            if (!total_amt || total_amt <= 0) {
                Swal.fire('Warning', 'Total Amount is invalid', 'warning');
                return;
            }

            $('#loader').show();
            $.ajax({
                type: 'POST',
                url: 'ajax_add_order.php',
                data: {
                    account_id,
                    category_id,
                    transaction_id,
                    product_id,
                    brand_id,
                    unit_id,
                    unit_name,
                    tran_detail_id,
                    type: '<?= $type ?>',
                    qty,
                    rate,
                    price_after_disc,
                    discount,
                    discount_amt,
                    sub_total,
                    taxable_amt,
                    total_amt,
                    gst_id,
                    gst_percent,
                    gst_amt,
                    update_mrp
                },
                success(data) {
                    $('#loader').hide();
                    resetProductFields();
                    fetch_data();
                    let d = data.trim();
                    if (d == '1') Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    else if (d == '2') Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    else if (d == '3') Swal.fire({
                        icon: 'warning',
                        title: 'Already Added',
                        text: 'This product is already in the order.'
                    });
                    else Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not add product.'
                    });
                },
                error() {
                    $('#loader').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong!'
                    });
                }
            });
        }

        function resetProductForm() {
            ['brand_id', 'category_id', 'product_id', 'gst_id'].forEach(id =>
                $('#' + id).val('').trigger('chosen:updated')
            );
            $('#unit_name, #qty, #rate, #price_after_disc').val('');
            $('#total_amt_hidden, #taxable_amt, #gst_amt').val(0);
            $('#sub_total_hidden, #discount_amt_hidden').val(0);
            $('#total_amt').text('0.00');
            $('#unit_id').val(0);
            $('#discount').val(0);
            $('#tran_detail_id').val(0);
            $('#gst_percent').val(0);
            $('#add_btn').text('ADD');
        }

        function EditProduct(category_id, product_id, brand_id, unit_id, unit_name,
            qty, rate, discount, gst_id, tran_detail_id) {
            $('#brand_id').val(brand_id).trigger('change');
            load_category_by_brand(brand_id, category_id);
            $('#category_id').val(category_id);
            get_products(category_id, product_id);

            $('#unit_id').val(unit_id);
            $('#unit_name').val(unit_name);
            $('#qty').val(qty);
            $('#rate').val(rate);
            $('#discount').val(discount || 0);
            $('#gst_id').val(gst_id || '').trigger('change');
            $('#tran_detail_id').val(tran_detail_id);
            $('#add_btn').text('Update');

            setTimeout(() => recalcTotal(), 50);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function openModal() {
            $('#staticBackdrop').modal('show');
        }

        function add_counter() {
            let formData = new FormData();
            formData.append('add_counter', 1);
            formData.append('route_planid', $('#route_planid').val());
            formData.append('account_name', $('#m_account_name').val());
            formData.append('mobile_no', $('#m_mobile_no').val());
            formData.append('address', $('#m_address').val());
            formData.append('area_id', $('#m_area_id').val());
            formData.append('common_id', $('#m_common_id').val());
            formData.append('class', $('#m_class').val());

            Swal.fire({
                title: 'Saving...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            $.ajax({
                url: 'my-order.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success(res) {
                    res = res.trim();
                    if (res === 'success') Swal.fire('Saved!', 'Counter added successfully', 'success').then(() => location.reload());
                    else if (res === 'duplicate') Swal.fire('Duplicate', 'Counter already exists in this area', 'warning');
                    else Swal.fire('Error', 'Could not add counter', 'error');
                }
            });
        }

        /* ── Save order header ──────────────────────────── */
        function getLocationAndProceed(btn) {
            $(btn).prop('disabled', true).text('Saving...');
            if (!navigator.geolocation) {
                Save_data();
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let lon = position.coords.longitude;
                    fetch('location.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                latitude: lat,
                                longitude: lon
                            })
                        })
                        .then(r => r.json())
                        .then(data => Save_data(lat, lon, data.address || ''))
                        .catch(() => Save_data(lat, lon, ''));
                },
                function() {
                    Save_data();
                }
            );
        }

        function Save_data(latitude = '', longitude = '', address = '') {

            // 🔹 Resolve account_id properly (dropdown OR hidden fallback)
            let account_id = $('#account_id').val() || $('#hidden_account_id').val();

            let grand_total = parseFloat($('#grand_total').val()) || 0;
            let billno = $('#billno').val().trim();
            let billdate = $('#billdate').val();
            let remark = $('#remark').val();
            let transaction_id = $('#<?= $tblpkey ?>').val();

            // 🔴 Validations
            if (!account_id) {
                Swal.fire('Warning', 'Please select Counter', 'warning');
                return;
            }

            if (!billno) {
                Swal.fire('Warning', 'Order No. missing', 'warning');
                return;
            }

            if (!billdate) {
                Swal.fire('Warning', 'Select Order Date', 'warning');
                return;
            }

            if (grand_total <= 0) {
                Swal.fire('Warning', 'Invalid Grand Total', 'warning');
                return;
            }

            // 🔹 GST Logic (clean separation)
            let gstEnabled = $('#gst_enabled').is(':checked');
            let gstMode = $('input[name="gst_mode"]:checked').val() || 'none';

            let overall_gst_pct = 0;
            let overall_gst_amt = 0;

            if (gstEnabled && gstMode === 'overall') {

                overall_gst_pct = parseFloat($('#og_pct').text()) || 0;
                let subGrand = parseFloat($('#grand_total_base').val()) || 0;

                if (overall_gst_pct > 0 && subGrand > 0) {
                    overall_gst_amt = round2((subGrand * overall_gst_pct) / 100);
                }
            }

            // 🔹 Loader ON
            $('#loader').show();

            $.ajax({
                type: 'POST',
                url: '',
                data: {
                    latitude,
                    longitude,
                    address,
                    account_id,
                    grand_total,
                    billno,
                    billdate,
                    remark,
                    transaction_id,
                    type: '<?= $type ?>',
                    overall_gst_pct,
                    overall_gst_amt,
                    gst_mode: gstMode
                },

                success(data) {
                    $('#loader').hide();

                    let res = data.trim();

                    if (res === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => location.href = 'order-list.php');

                    } else if (res === 'updated') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => location.href = 'order-list.php');

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Could not save order.'
                        });
                    }
                },

                error() {
                    $('#loader').hide();

                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong!'
                    });
                }
            });
        }
    </script>
</body>

</html>