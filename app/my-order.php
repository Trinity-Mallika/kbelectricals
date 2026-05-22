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

    $form_data = [
        "account_id"  => $account_id,
        "type"        => $type,
        "billno"      => $billno,
        "billdate"    => $billdate,
        "remark"      => $remark,
        "grand_total" => $grand_total,
        'longitude'   => $longitude,
        'latitude'    => $latitude,
        'address'     => $address,
        "createdby"   => $loginid,
        'companyid'   => $companyid,
        "ipaddress"   => $ipaddress,
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
                    <div class="col-12 mb-2">
                        <label class="form-label">Remark</label>
                        <input class="form-control form-control-sm" name="remark" id="remark" value="<?= $remark ?>" autocomplete="off">
                    </div>
                    <input type="hidden" name="<?= $tblpkey ?>" id="<?= $tblpkey ?>" value="<?= $keyvalue ?>">
                </div>
            </div>
            <div class="card border-0 shadow-sm p-2 mb-3">
                <div class="row g-2 mb-1">
                    <div class="col-6 mb-1">
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
                    <div class="col-6">
                        <select id="category_id" class="form-select form-select-sm"
                            onchange="get_products(this.value)">
                            <option value="">Category</option>
                        </select>
                    </div>
                </div>
                <div class="mb-1">
                    <select id="product_id" class="form-select form-select-sm chosen-select"
                        onchange="get_product_details(this.value)">
                        <option value="">Select Product</option>
                    </select>
                </div>
                <div class="row g-2 align-items-center mt-1">
                    <div class="col-3">
                        <input type="text" id="unit_name" class="form-control form-control-sm text-center" placeholder="Unit" readonly>
                        <input type="hidden" id="unit_id" class="form-control form-control-sm text-center" placeholder="Unit" readonly>
                    </div>
                    <div class="col-3">
                        <input type="number" id="qty" class="form-control form-control-sm text-center fw-bold" placeholder="Qty" autocomplete="off">
                    </div>
                    <div class="col-6">
                        <input type="number" id="rate" class="form-control form-control-sm text-center" placeholder="Rate" autocomplete="off">
                        
                    </div>
                    
                </div>
                <div class="row g-2 align-items-center mt-1">
                    <div class="col-4">
                        <input type="number" id="discount" class="form-control form-control-sm text-center" placeholder="Disc %" autocomplete="off">
                    </div>
                    <div class="col-4">
                        <select id="gst_id" class="form-select form-select-sm" onchange="recalcTotal();">
                            <option value="">GST</option>
                            <?php
                            $sql = $obj->executequery("select * from gst_master");
                            foreach ($sql as $key) { ?>
                                <option value="<?php echo $key['gst_id'] ?>"
                                    data-sgst="<?= $key['sgst'] ?>"
                                    data-cgst="<?= $key['cgst'] ?>"
                                    data-percent="<?= $key['sgst'] + $key['cgst'] ?>"><?php echo $key['gst_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-primary btn-sm w-100" id="add_btn" onclick="save_order_details()">+</button>
                    </div>
                </div>
                <input type="hidden" id="sub_total_hidden">
                <input type="hidden" id="discount_amt_hidden">
                <input type="hidden" id="gst_percent">
                <input type="hidden" id="sgst_percent">
                <input type="hidden" id="cgst_percent">
                <input type="hidden" id="taxable_amt">
                <input type="hidden" id="total_amt_hidden">
                <input type="hidden" id="tran_detail_id">
                <div class="mt-2 text-end fw-bold">
                    Total: ₹<span id="total_amt">0.00</span>
                </div>
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

        $(document).on('input change', '#qty, #rate, #discount, #gst_id', function() {
            recalcTotal();
        });

        function recalcTotal() {
            let qty = parseFloat($('#qty').val()) || 0;
            let rate = parseFloat($('#rate').val()) || 0;
            let discountPercent = parseFloat($('#discount').val()) || 0;
            let sub_total = qty * rate;
            let discount = (sub_total * discountPercent) / 100;
            discount = Math.min(discount, sub_total);
            let taxable = sub_total - discount;

            sub_total = round2(sub_total);
            discount = round2(discount);
            taxable = round2(taxable);

            $('#sub_total_hidden').val(sub_total);
            $('#discount_amt_hidden').val(discount);
            $('#total_amt_hidden').val(taxable);
            $('#total_amt').text(taxable.toFixed(2));
            let selectedOpt = $('#gst_id option:selected');

            let sgst = parseFloat(selectedOpt.data('sgst')) || 0;
            let cgst = parseFloat(selectedOpt.data('cgst')) || 0;

            let gst_pct = sgst + cgst;

            $('#gst_percent').val(gst_pct);
            $('#sgst_percent').val(sgst);
            $('#cgst_percent').val(cgst);

            let sgst_amt = round2(taxable * sgst / 100);
            let cgst_amt = round2(taxable * cgst / 100);
            let gst_amt = round2(sgst_amt + cgst_amt);
            let total = round2(taxable + gst_amt);

            if (taxable > 0) {
                $('#taxable_amt').val(taxable);
                $('#sgst_amt').val(sgst_amt);
                $('#cgst_amt').val(cgst_amt);
                $('#gst_amt').val(gst_amt);
                $('#total_amt_hidden').val(total);
                $('#total_amt').text(total.toFixed(2));
            } else {
                $('#taxable_amt, #sgst_amt, #cgst_amt, #gst_amt, #total_amt').val('');
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
                        recalcTotal();
                    } else {
                        Swal.fire('Error', 'Product details not found', 'error');
                    }
                }
            });
        }

        function save_order_details() {
            let account_id = $('#account_id').val();
            let category_id = $('#category_id').val();
            let product_id = $('#product_id').val();
            let brand_id = $('#brand_id').val();
            let unit_id = $('#unit_id').val();
            let unit_name = $('#unit_name').val();
            let qty = $('#qty').val();
            let rate = $('#rate').val();
            let discount = parseFloat($('#discount').val()) || 0;
            let gst_id = $('#gst_id').val() || 0;
            let gst_percent = parseFloat($('#gst_percent').val()) || 0;
            let sgst_percent = parseFloat($('#sgst_percent').val()) || 0;
            let cgst_percent = parseFloat($('#cgst_percent').val()) || 0;
            let taxable_amt = parseFloat($('#taxable_amt').val()) || 0;
            let gst_amt = parseFloat($('#gst_amt').val()) || 0;
            let sgst_amt = parseFloat($('#sgst_amt').val()) || 0;
            let cgst_amt = parseFloat($('#cgst_amt').val()) || 0;
            let total_amt = $('#total_amt_hidden').val();
            let sub_total = $('#sub_total').val();
            let discount_amt = $('#discount_amt_hidden').val();
            let tran_detail_id = $('#tran_detail_id').val();
            let transaction_id = $('#transaction_id').val();

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
                Swal.fire('Warning', 'Enter valid MRP / Rate', 'warning');
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
                    discount,
                    sub_total,
                    discount_amt,
                    gst_id,
                    gst_percent,
                    sgst_percent,
                    cgst_percent,
                    taxable_amt,
                    gst_amt,
                    sgst_amt,
                    cgst_amt,
                    total_amt
                },
                success(data) {
                    $('#loader').hide();
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

                    resetProductForm();
                    fetch_data();
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
            $('#unit_name, #qty, #rate, #total_amt, #taxable_amt, #gst_amt,#sgst_amt, #cgst_amt ').val('');
            $('#unit_id').val(0);
            $('#discount').val(0);
            $('#tran_detail_id').val(0);
            $('#gst_percent, #sgst_percent, #cgst_percent').val(0);
            $('#add_btn').text('+');
        }

        function EditProduct(category_id, product_id, brand_id, unit_id, unit_name,
            qty, rate, discount, gst_id, tran_detail_id) {

            $('#brand_id').val(brand_id).trigger('change');
            load_category_by_brand(brand_id, category_id);
            get_products(category_id, product_id);

            $('#unit_id').val(unit_id);
            $('#unit_name').val(unit_name);
            $('#qty').val(qty);
            $('#rate').val(rate);
            $('#discount').val(discount || 0);

            $('#gst_id').val(gst_id || '').trigger('change');

            setTimeout(() => {
                recalcTotal();
            }, 50);

            $('#tran_detail_id').val(tran_detail_id);
            $('#add_btn').text('Update');

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

        /* ════════════════════════════════════════════
           Save order header (GPS → POST)
        ════════════════════════════════════════════ */
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
            let account_id = $('#account_id').val();
            let grand_total = $('#grand_total').val();
            let billno = $('#billno').val();
            let billdate = $('#billdate').val();
            let remark = $('#remark').val();
            let transaction_id = $('#<?= $tblpkey ?>').val();

            if (!account_id) {
                Swal.fire('Warning', 'Please select Counter', 'warning');
                return;
            }
            if (!billno.trim()) {
                Swal.fire('Warning', 'Order No. missing', 'warning');
                return;
            }
            if (!billdate) {
                Swal.fire('Warning', 'Select Order Date', 'warning');
                return;
            }

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
                    type: '<?= $type ?>'
                },
                success(data) {
                    $('#loader').hide();
                    if (data.trim() == 'success') Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.href = 'order-list.php');
                    else if (data.trim() == 'updated') Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.href = 'order-list.php');
                    else Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not save order.'
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
    </script>
</body>

</html>