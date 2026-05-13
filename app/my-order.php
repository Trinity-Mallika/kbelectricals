<?php include("appsession.php");
$pagename = 'my-order.php';
$title = 'Order Entry';
$tblname = 'transaction_entry';
$tblpkey = 'transaction_id';
$btn_name = "Save";
$keyvalue = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$imgpath = "uploads/daily_entry/";
$weekday = date('l');
$type = 'order';

$billno = $obj->getcode($tblname, "billno",  "1=1 and type='$type' ");
$data = $obj->getRouteDashboardData($loginid, $companyid);
$route_plan_id = $data['route_plan_id'];



$current_date = date('Y-m-d');
// Month ka first day
$first_day = date('Y-m-01', strtotime($current_date));

// Current day of month
$current_day = date('d', strtotime($current_date));

// Week number (1 to 5)
$week_no = ceil(($current_day + date('N', strtotime($first_day)) - 1) / 7);


if (isset($_POST['add_counter'])) {

    $batch_no = $obj->test_input($_POST['route_planid']);
    $account_name = $obj->test_input($_POST['account_name']);
    $mobile_no = $obj->test_input($_POST['mobile_no']);
    $address = $obj->test_input($_POST['address']);
    $area_id = $obj->test_input($_POST['area_id']);
    $common_id = $obj->test_input($_POST['common_id']);
    $class = $obj->test_input($_POST['class']);

    $type = ($common_id == -1) ? "employee" : "customer";

    if ($account_name == "" || $area_id == "" || $class == "") {
        echo "error";
        exit;
    }

    // Duplicate check
    $count = $obj->getvalfield(
        "account",
        "count(*)",
        "account_name='$account_name' AND area_id='$area_id'"
    );

    if ($count > 0) {
        echo "duplicate";
        exit;
    }

    $form_data = [
        'account_name' => $account_name,
        'mobile_no' => $mobile_no,
        'address' => $address,
        'common_id' => $common_id,
        'area_id' => $area_id,
        'class' => $class,
        'status' => "inactive",
        'type' => $type,
        'status1' => 0,
        'createdby' => $loginid,
        'companyid' => $companyid,
        'ipaddress' => $ipaddress,
        'createdate' => date('Y-m-d H:i:s')
    ];

    // INSERT INTO ACCOUNT
    $account_id = $obj->insert_record_lastid("account", $form_data);

    if ($account_id > 0) {

        // AUTO SEQUENCE
        $sequence = $obj->getvalfield(
            "route_counter",
            "IFNULL(MAX(sequence),0)+1",
            "batch_no='$batch_no'"
        );

        // INSERT INTO ROUTE COUNTER
        $obj->insert_record("route_counter", [
            'batch_no' => $batch_no,
            'account_id' => $account_id,
            'sequence' => $sequence,
            'createdate' => date('Y-m-d H:i:s'),
            'ipaddress' => $ipaddress,
            'companyid' => $companyid,
            'createdby' => $loginid
        ]);

        // $obj->insert_record("route_plan", [
        //     'batch_no'   => $batch_no,
        //     'week_number' => $week_no,
        //     'sales_executive_id'   => $loginid,
        //     'createdate' => date('Y-m-d H:i:s'),
        //     'ipaddress'  => $ipaddress,
        //     'companyid'  => $companyid,
        //     'createdby'  => $loginid
        // ]);

        echo "success";
    } else {
        echo "error";
    }

    exit;
}



if (isset($_POST['account_id'])) {
    $keyvalue = $obj->test_input($_POST['transaction_id']);
    $account_id = $obj->test_input($_POST['account_id']);
    $billno = $obj->test_input($_POST['billno']);
    $billdate = $obj->test_input($_POST['billdate']);
    $remark = $obj->test_input($_POST['remark']);
    $grand_total = $obj->test_input($_POST['grand_total']);

    $latitude = $obj->test_input($_POST['latitude']);
    $longitude = $obj->test_input($_POST['longitude']);
    $address = $obj->test_input($_POST['address']);

    $form_data = array(
        "account_id" => $account_id,
        "type" => $type,
        "billno" => $billno,
        "billdate" => $billdate,
        "remark" => $remark,
        "grand_total" => $grand_total,
        'longitude' => $longitude,
        'latitude' => $latitude,
        'address' => $address,
        "createdby" => $loginid,
        'createdate' => $createdate,
        'companyid' => $companyid,
        "ipaddress" => $ipaddress,
    );

    if ($keyvalue == 0) {
        $form_data["createdate"] = $createdate;
        $lastid = $obj->insert_record_lastid($tblname, $form_data);
        $obj->update_record('transaction_details', ['transaction_id' => 0, 'type' => $type, 'account_id' => $account_id], ['transaction_id' => $lastid]);

        $action = 1;
        $process = "Insert";
        echo "success";
    } else {
        $form_data["lastupdated"] = $createdate;
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $action = 2;
        $process = "Update";
        echo "updated";
    }
    die;
}

if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $account_id1 = $sqledit['account_id'];
    $billdate = $sqledit['billdate'];
    $remark = $sqledit['remark'];
    $billno = $sqledit['billno'];
} else {
    $billdate = date('Y-m-d');
    $account_id1 = 0;
    $remark = "";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= $title ?></title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>

</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">

            <form method="GET">
                <div class="card border-0 shadow-lg mb-3">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="mb-0"> Order Entry</h4>
                        </div>
                        <div class="col-6 text-end ">
                            <a href="order-list.php" class="btn btn-sm btn-primary">Order List</a>
                        </div>
                        <div class="col-12 mb-2 mt-2">
                            <hr class="m-0">
                        </div>
                        <div class="col-lg-12 col-12 mb-2">

                            <label for="" class="form-label w-100"> Counter Name
                                <span class="text-danger fw-bold">*</span>
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-primary p-1 mt-1 float-end " onclick="openModal();">+Add</a>
                            </label>
                            <select class="form-select chosen-select" name="account_id" id="account_id" onchange="fetch_data();" <?php if ($keyvalue > 0) { ?> disabled <?php } ?>>
                                <option value="">Select</option>
                                <?php
                                $res = $obj->executequery("
    SELECT DISTINCT
        a.account_id,
        a.account_name,
        cm.common_name AS account_type,
        am.area_name

    FROM route_plan rp
    JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
    JOIN account a
        ON a.account_id = rc.account_id
    LEFT JOIN common_master cm
        ON cm.common_id = a.common_id
       AND cm.type = 'acc_type'
    LEFT JOIN area_master am
        ON am.area_id = a.area_id

    WHERE rp.companyid = '$companyid'
      AND rc.companyid = '$companyid'

    ORDER BY a.account_name ASC
");

                                foreach ($res as $key) {
                                    echo "<option value='{$key['account_id']}'>
            {$key['account_name']} [{$key['account_type']}] /  {$key['area_name']} 
          </option>";
                                }
                                ?>
                            </select>
                            <script>
                                document.getElementById('account_id').value = '<?= $account_id1 ?>';
                            </script>

                            <input type="hidden" name="account_id" id="account_id" value="<?php echo $account_id1 ?>" <?php if ($keyvalue == 0) { ?> disabled <?php } ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-6">
                            <label for="" class="form-label"> Order No. <span class="text-danger fw-bold">*</span></label>
                            <input class="form-control form-sm" name="billno" id="billno" value="<?php echo $billno ?>" readonly>
                        </div>
                        <div class="col-lg-6 col-6">
                            <label for="" class="form-label"> Order Date <span class="text-danger fw-bold">*</span></label>
                            <input class="form-control form-sm" type="date" name="billdate" id="billdate" value="<?php echo $billdate ?>">

                        </div>
                        <input type="hidden" name="<?php echo $tblpkey ?>" id="<?php echo $tblpkey ?>" value="<?php echo $keyvalue; ?>">

                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <label for="" class="form-label"> Remark</label>
                            <input class="form-control form-sm" name="remark" id="remark" value="<?php echo $remark ?>">
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-lg mb-3">
                    <div class="row">
                        <div class="col-lg-6 col-6 mb-2">
                            <label for="" class="form-label"> Brand <span class="text-danger fw-bold">*</span></label>
                            <select name="brand_id" id="brand_id" class="form-select form-control shadow-sm" onchange="load_category_by_brand(this.value);">
                                <option value="">--Select Brand--</option>
                                <?php

                                $sql = $obj->executequery("select * from category_master where type='brand' order by cat_id DESC ");

                                foreach ($sql as $key) {
                                ?> <option value="<?php echo $key['cat_id'] ?>"><?php echo $key['cat_name'] ?></option> <?php } ?>

                            </select>
                        </div>

                        <div class="col-lg-6 col-6 mb-2">
                            <label for="" class="form-label"> Category <span class="text-danger fw-bold">*</span></label>
                            <select name="category_id" id="category_id" class="form-select form-control shadow-sm" onchange="get_products(this.value)">
                                <option value="">-- Select Category --</option>
                                <?php
                                $sql = " SELECT  * from category_master where type='category' order by cat_name asc ";
                                $res = $obj->executequery($sql);
                                foreach ($res as $key) {

                                    echo "<option 
                            value='{$key['cat_id']}'

                            >
                            {$key['cat_name']}
                            </option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-lg-12 col-12 mb-2">
                            <label for="" class="form-label"> Products <span class="text-danger fw-bold">*</span></label>
                            <select name="product_id" id="product_id" class="form-select form-control shadow-sm chosen-select" onchange="get_product_details(this.value);">
                                <option value="">-- Select Products --</option>

                            </select>
                        </div>

                        <div class="col-lg-6 col-6 mb-2">
                            <label for="" class="form-label"> Unit <span class="text-danger fw-bold">*</span></label>

                            <input type="text" name="unit_name" id="unit_name" class="form-select form-control shadow-sm" readonly>
                            <input type="hidden" name="unit_id" id="unit_id" value="0">
                        </div>
                        <div class="col-lg-6 col-6 mb-2">
                            <label for="" class="form-label"> Qty <span class="text-danger fw-bold">*</span></label>
                            <input type="text" name="qty" id="qty" class="form-select form-control shadow-sm">
                            <input type="hidden" name="tran_detail_id" id="tran_detail_id" value="0">
                        </div>

                        <div class="col-lg-6 col-6 mb-2">
                            <label for="" class="form-label"> MRP <span class="text-danger fw-bold">*</span></label>
                            <input type="text" name="rate" id="rate" class="form-select form-control shadow-sm">

                        </div>

                        <div class="col-lg-6 col-6 mb-2">
                            <label for="" class="form-label"> Total Ammount <span class="text-danger fw-bold">*</span></label>
                            <input type="text" name="total_amt" id="total_amt" class="form-select form-control shadow-sm" readonly>
                        </div>

                        <div class="col-lg-12">
                            <a onclick="save_order_details()" class="btn btn-sm mt-3 w-100">Add</a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row" id="show_order">

            </div>




        </div>

    </section>
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Add New Counter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Route Name<span class="text-danger fw-bold">*</span>
                            </label>
                            <select name="route_planid" id="route_planid"
                                class="chosen-select form-control form-control-sm">
                                <option value="">--Select Route Name--</option>
                                <?php


                                $sql = $obj->executequery("SELECT
                                        R.batch_no,
                                        R.route_name,
                                        GROUP_CONCAT(
                                        R.day_of_week
                                        ORDER BY FIELD(
                                        day_of_week,
                                        'Monday',
                                        'Tuesday',
                                        'Wednesday',
                                        'Thursday',
                                        'Friday',
                                        'Saturday'
                                        )
                                        SEPARATOR ', '
                                        ) AS days
                                        FROM route as R left join route_plan as RP on R.batch_no=RP.batch_no
                                        WHERE R.companyid='$companyid' AND RP.sales_executive_id='$loginid'
                                        GROUP BY R.batch_no, R.route_name
                                        ORDER BY R.route_name ASC
                                        ");

                                foreach ($sql as $key) { ?>
                                    <option value="<?= $key['batch_no'] ?>"><?= $key['route_name'] ?> [<?= $key['days'] ?>]
                                    </option>
                                <?php } ?>
                            </select>
                            <script>
                                document.getElementById('route_planid').value = '<?php echo $route_plan_id; ?>';
                            </script>
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Counter Name <span
                                    class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="account_name" name="account_name"
                                placeholder="Enter Counter Name">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Mobile Number <span
                                    class="text-danger fw-bold"></span></label>
                            <input type="text" class="form-control shadow-sm" id="mobile_no" name="mobile_no"
                                placeholder="Enter Mobile Number" maxlength="10" pattern="[0-9]{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Class <span class="text-danger fw-bold">*</span></label>
                            <select name="class" id="class" class="form-control form-control-sm">
                                <option value="">--Select Class--</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Area <span class="text-danger fw-bold">*</span></label>
                            <select name="area_id" id="area_id" class="chosen-select  form-control form-control-sm">
                                <option value="">--Select Area--</option>
                                <?php
                                $sql = $obj->executequery("select area_id,area_name from area_master order by area_name asc ");
                                foreach ($sql as $key) {
                                ?>
                                    <option value="<?= $key['area_id'] ?>"><?= $key['area_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Counter Type</label>
                            <select name="common_id" id="common_id" class="chosen-select  form-control form-control-sm">
                                <option value="">--Select Counter Type--</option>
                                <?php
                                $sql = $obj->executequery("select common_id,common_name from common_master where type='acc_type' order by common_id asc ");
                                foreach ($sql as $key) {
                                ?>
                                    <option value="<?= $key['common_id'] ?>"><?= $key['common_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-9 mb-3">
                            <label for="" class="form-label">Address</label>
                            <textarea class="form-control shadow-sm" id="address" name="address"
                                placeholder="Enter Address"></textarea>
                        </div>
                        <div class="col-lg-3 text-center ">
                            <button type="button" class="btn btn-primary" onclick="add_counter();">+Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div id="loader" style="
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(255,255,255,0.6);
    display:none;
    z-index:9999;
">
        <div style="
        position:absolute;
        top:50%;
        left:50%;
        transform:translate(-50%,-50%);
        width:40px;
        height:40px;
        border:4px solid #ccc;
        border-top:4px solid #007bff;
        border-radius:50%;
        animation: spin 0.8s linear infinite;
    "></div>
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

    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
    <script>
        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });

        });

        // Auto calculate total amount
        $(document).ready(function() {
            $('#qty, #rate').on('keyup change', function() {

                let qty = parseFloat($('#qty').val()) || 0;
                let rate = parseFloat($('#rate').val()) || 0;

                let total = qty * rate;

                $('#total_amt').val(total.toFixed(2)); // 2 decimal
            });
        });


        function add_counter() {

            let formData = new FormData();

            formData.append('add_counter', 1);
            formData.append('route_planid', $('#route_planid').val());
            formData.append('account_name', $('#account_name').val());
            formData.append('mobile_no', $('#mobile_no').val());
            formData.append('address', $('#address').val());
            formData.append('area_id', $('#area_id').val());
            formData.append('common_id', $('#common_id').val());
            formData.append('class', $('#class').val());

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

                success: function(res) {
                    alert(res);
                    res = res.trim();

                    if (res === 'success') {

                        Swal.fire('Saved Successfully', '', 'success');

                        // Reload dropdown
                        location.reload();

                    } else if (res === 'duplicate') {

                        Swal.fire('Duplicate Counter', '', 'warning');

                    } else {

                        Swal.fire('Error', res, 'error');
                    }
                }
            });
        }

        function openModal() {
            $("#staticBackdrop").modal("show");
        }


        function fetch_data() {
            let transaction_id = document.getElementById('transaction_id').value;
            let account_id = document.getElementById('account_id').value;
            console.log(account_id);
            jQuery.ajax({
                type: 'POST',
                url: 'show_order_details.php',
                data: {
                    account_id: account_id,
                    transaction_id: transaction_id,
                    type: '<?php echo $type; ?>',
                },
                dataType: 'html',
                success: function(data) {
                    // alert(data);
                    document.getElementById("show_order").innerHTML = data;
                }
            });
        }

        fetch_data(<?php echo $keyvalue ?>);

        function delete_record(id) {
            jQuery.ajax({
                type: 'POST',
                url: 'delete_master.php',
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

        function get_product_details(product_id) {
            jQuery.ajax({
                type: 'POST',
                url: '../admin/get_product_details.php',
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

        function save_order_details() {

            let unit_name = $('#unit_name').val();
            let account_id = $('#account_id').val();
            let category_id = $('#category_id').val();
            let product_id = $('#product_id').val();
            let brand_id = $('#brand_id').val();
            let unit_id = $('#unit_id').val();
            let qty = $('#qty').val();
            let rate = $('#rate').val();
            let total_amt = $('#total_amt').val();
            let tran_detail_id = $('#tran_detail_id').val();
            let transaction_id = $('#transaction_id').val();
            let type = '<?= $type ?>';
            if (account_id == '') {
                Swal.fire('Select Account');
                $('#account_id').focus();
                return;
            }

            if (category_id == '') {
                Swal.fire('Select Category');
                $('#category_id').focus();
                return;
            }

            if (product_id == '') {
                Swal.fire('Select Product');
                $('#product_id').focus();
                return;
            }

            if (brand_id == '') {
                Swal.fire('Select Brand');
                $('#brand_id').focus();
                return;
            }

            if (unit_id == '') {
                Swal.fire('Select Unit');
                $('#unit_id').focus();
                return;
            }

            if (qty == '' || qty <= 0) {
                Swal.fire('Enter valid Quantity');
                $('#qty').focus();
                return;
            }

            if (rate == '' || rate <= 0) {
                Swal.fire('Enter valid MRP');
                $('#rate').focus();
                return;
            }

            if (total_amt == '' || total_amt <= 0) {
                Swal.fire('Enter valid Quantity');
                $('#total_amt').focus();
                return;
            }

            // ✅ SHOW LOADER
            $('#loader').show();

            $.ajax({
                type: 'POST',
                url: 'ajax_add_order.php',
                data: {
                    account_id: account_id,
                    category_id: category_id,
                    transaction_id: transaction_id,
                    unit_name: unit_name,
                    product_id: product_id,
                    brand_id: brand_id,
                    unit_id: unit_id,
                    tran_detail_id: tran_detail_id,
                    type: type,
                    qty: qty,
                    rate: rate,
                    total_amt: total_amt
                },
                success: function(data) {
                    // alert(data);
                    $('#loader').hide(); // ✅ HIDE LOADER

                    if (data.trim() == '1') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Added!',
                            timer: 1000,
                            showConfirmButton: false
                        });

                    } else if (data.trim() == '2') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            timer: 1000,
                            showConfirmButton: false
                        });

                    } else if (data.trim() == '3') {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Already Added',
                            text: 'This product is already added in order!'
                        });

                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error adding product!'
                        });
                    }

                    // reset
                    $('#unit_id').val('').trigger('chosen:updated');
                    $('#brand_id').val('').trigger('chosen:updated');
                    $('#product_id').val('').trigger('chosen:updated');
                    $('#category_id').val('').trigger('chosen:updated');
                    $('#qty').val('');
                    $('#rate').val('')
                    $('#total_amt').val('');
                    $('#tran_detail_id').val(0);

                    fetch_data();
                },
                error: function() {
                    $('#loader').hide(); // ✅ IMPORTANT

                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong!'
                    });
                }
            });
        }

        function getLocationAndProceed(btn) {
            $(btn).prop('disabled', true);

            navigator.geolocation.getCurrentPosition(

                function(position) {
                    let latitude = position.coords.latitude;
                    let longitude = position.coords.longitude;

                    fetch('location.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                latitude: latitude,
                                longitude: longitude
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            address = data.address || '';
                            Save_data(latitude, longitude, address);
                        })
                        .catch(error => {
                            console.log("Address fetch error:", error);
                            Save_data(latitude, longitude, address);
                        });
                },

                function(error) {
                    console.log("Location error:", error);
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
            let transaction_id = $('#transaction_id').val();
            let type = '<?= $type ?>';

            if (account_id == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please select Customer!'
                });
                $('#account_id').focus();
                return;
            }

            if (billno.trim() == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please enter Order No!'
                });
                $('#billno').focus();
                return;
            }

            if (billdate == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please select Order Date!'
                });
                $('#billdate').focus();
                return;
            }

            $('#loader').show();

            $.ajax({
                type: 'POST',
                url: '',
                data: {
                    latitude: latitude,
                    longitude: longitude,
                    address: address,
                    account_id: account_id,
                    grand_total: grand_total,
                    billno: billno,
                    billdate: billdate,
                    remark: remark,
                    transaction_id: transaction_id,
                    type: type
                },
                dataType: 'html',
                success: function(data) {
                    // alert(data);
                    $('#loader').hide();
                    if (data.trim() == 'success') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'Order saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'order-list.php';
                        });

                    } else if (data.trim() == 'updated') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Order updated successfully',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'order-list.php';
                        });

                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error saving order!'
                        });
                    }
                },

                error: function() {

                    $('#loader').hide();

                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong!'
                    });
                }
            });
        }

        function EditProduct(category_id, product_id, brand_id, unit_id, unit_name, qty, rate, total_amt, tran_detail_id) {
            document.getElementById('category_id').value = category_id;
            document.getElementById('brand_id').value = brand_id;
            $('#brand_id').val(brand_id).trigger('chosen:updated');
            load_category_by_brand(brand_id, category_id);
            get_products(category_id, product_id);
            document.getElementById('unit_id').value = unit_id;
            document.getElementById('unit_name').value = unit_name;
            document.getElementById('qty').value = qty;
            document.getElementById('rate').value = rate;
            document.getElementById('total_amt').value = total_amt;
            document.getElementById('tran_detail_id').value = tran_detail_id;
            document.getElementById('add_btn').value = 'Update';
        }

        function load_category_by_brand(brand_id, category_id = 0) {

            let account_id = $('#account_id').val();

            if (!account_id) {
                Swal.fire('Select Counter Name');
                $('#account_id').focus();
                $('#brand_id').val('').trigger('chosen:updated');
                return false;
            }

            if (brand_id != "") {
                $.ajax({
                    url: "../admin/get_category.php",
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
                url: '../admin/get_product_combo.php',
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
    </script>
</body>





</html>