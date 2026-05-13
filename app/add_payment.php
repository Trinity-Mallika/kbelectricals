<?php include("appsession.php");
$pagename = 'add_payment.php';
$title = 'Add Payment';
$tblname = 'transaction_entry';
$tblpkey = 'transaction_id';
$btn_name = "Save";
$keyvalue = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;

$data = $obj->getRouteDashboardData($loginid, $companyid);
$route_plan_id = $data['route_plan_id'];
$imgpath = "uploads/payment_proof/";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $keyvalue   = $obj->test_input($_POST['transaction_id']);
    $account_id = $obj->test_input($_POST['account_id']);
    $bill_id    = $_POST['bill_id'];
    $paymode    = $obj->test_input($_POST['paymode']);
    $paydate    = $obj->test_input($_POST['paydate']);
    $pay_amt    = $obj->test_input($_POST['pay_amt']);
    $voucher_no = $obj->test_input($_POST['voucher_no']);
    $trans_id   = $obj->test_input($_POST['trans_id']);
    $latitude   = $obj->test_input($_POST['latitude']);
    $longitude  = $obj->test_input($_POST['longitude']);
    $address    = $obj->test_input($_POST['address']);
    $filename = '';

    if ($account_id == "" || $paymode == "" || $paydate == "" || $pay_amt == "") {
        echo "error";
        exit;
    }

    if ($paymode != 'Cash') {
        if (!empty($_FILES["payment_proof"]['name'])) {
            $filename = $obj->uploadImage($imgpath, $_FILES["payment_proof"]);
            if ($filename != "") {

                if ($keyvalue != 0) {
                    $old = $obj->getvalfield($tblname, "imgname", "entry_id='$keyvalue'");
                    if ($old != "") {
                        @unlink($imgpath . $old);
                    }
                }
                // $form_data['imgname'] = $filename;
            }
        }
    } else {
        $filename = "";
    }

    $form_data = array(
        'account_id'    => $account_id,
        'ref_bill_id' => $bill_id,
        'paymode'       => $paymode,
        'imgname'       => $filename,
        'billdate'      => $paydate,
        'grand_total'   => $pay_amt,
        'billno'        => $voucher_no,
        'trans_id'      => $trans_id,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
        'address'       => $address,
        'type'          => 'payment',
        'createdby'     => $loginid,
        'companyid'     => $companyid,
        'ipaddress'     => $ipaddress
    );



    if ($keyvalue == 0) {

        $form_data['createdate'] = $createdate;
        $obj->insert_record($tblname, $form_data);

        echo "success";
        exit;
    } else {

        $form_data['lastupdated'] = $createdate;
        $obj->update_record($tblname, [$tblpkey => $keyvalue], $form_data);

        echo "updated";
        exit;
    }
}


if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $account_id = $sqledit['account_id'];
    $paymode = $sqledit['paymode'];
    $paydate = $sqledit['billdate'];
    $pay_amt = $sqledit['grand_total'];
    $voucher_no = $sqledit['billno'];
    $payment_proof = $sqledit['imgname'];
    $trans_id = $sqledit['trans_id'];
    $pending_amt = "";
} else {
    $account_id = $paymode = $pay_amt  = $payment_proof = $trans_id = "";
    $paydate = date('Y-m-d');
    $pending_amt = "";
    $voucher_no = $obj->getcode('transaction_entry', 'billno', 'type="payment"');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>

</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="card border-0 shadow-lg mb-3">
                <form method="POST" id="dailyEntryForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="mb-0"> <?php echo $title; ?></h4>
                        </div>
                        <div class="col-6 text-end ">
                            <a href="payment_list.php" class="btn btn-sm btn-primary">Payment List</a>
                        </div>
                        <div class="col-12 mb-2 mt-2">
                            <hr class="m-0">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Customer Name <span class="text-danger fw-bold">*</span></label>
                            <select class="form-select chosen-select" name="account_id" id="account_id" onchange="get_account_details(this.value);">
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
                                document.getElementById('account_id').value = '<?= $account_id ?>';
                            </script>
                        </div>


                        <div class="col-lg-12 mb-2" id="account_details_div">

                        </div>
                        <div class="col-lg-3 mb-2">
                            <label class="form-label">Select Bill <span class="text-danger">*</span></label>
                            <select name="bill_id" id="bill_id" class="form-select chosen-select" onchange="get_bill_details(this.value)">
                                <option value="">Select Bill</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-2" id="bill_details_div">

                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Pending Amount <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="pending_amt" name="pending_amt" placeholder="Pending Amount" value="<?php echo $pending_amt ?>" readonly>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Pay Mode <span class="text-danger fw-bold">*</span></label>
                            <select class="form-control chosen-select shadow-sm" id="paymode" name="paymode" placeholder="Enter Pay Mode" value="<?php echo $paymode ?>">
                                <option value="">Select Pay Mode</option>
                                <option value="Cash" <?php if ($paymode == "Cash") echo "selected"; ?>>Cash</option>
                                <option value="Cheque" <?php if ($paymode == "Cheque") echo "selected"; ?>>Cheque</option>
                                <option value="Online" <?php if ($paymode == "Online") echo "selected"; ?>>Online</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2" id="proof_div" style="display:none;">
                            <label class="form-label">Payment Proof <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="payment_proof" id="payment_proof" accept=".jpg,.jpeg,.png">
                            <?php if ($payment_proof != "") { ?>
                                <img src="uploads/payment_proof/<?php echo $payment_proof; ?>" alt="" style="width: 80px;" class="mt-2">
                            <?php } ?>
                        </div>
                        <input type="hidden" id="old_img" value="<?= $payment_proof ?>">

                        <div class="col-lg-3 mb-2" id="tansaction_div" style="display:none;">
                            <label class="form-label">Transection id <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" name="trans_id" id="trans_id" placeholder="Transection Id" value="<?php echo $trans_id ?>">

                        </div>


                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Voucher No. <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="voucher_no" name="voucher_no" placeholder="Enter Voucher No." value="<?php echo $voucher_no ?>" readonly>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Payment Date <span class="text-danger fw-bold">*</span></label>
                            <input type="date" class="form-control shadow-sm" id="paydate" name="paydate" placeholder="Enter Payment Date" value="<?php echo $paydate ?>">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Payment Amount <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="pay_amt" name="pay_amt" placeholder="Enter Payment Amount" value="<?php echo $pay_amt ?>">
                        </div>
                        <div class="d-grid mt-4">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="address" id="address">
                            <input type="hidden" name="<?= $tblpkey ?>" id="<?= $tblpkey ?>" value="<?php echo $keyvalue ?>">
                            <input type="submit" name="submit" id="save_order_btn" class="btn btn-primary" value="<?php echo $btn_name ?>">
                        </div>
                    </div>
                </form>

            </div>

        </div>

    </section>
    <div id="loader">
        <div class="loader-spinner"></div>
    </div>
    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        $(".chosen-select").chosen({
            width: "100%",
            search_contains: true
        });

        get_account_details('<?= $account_id; ?>');
        $('#paymode').trigger('change');
    });

    // $('#paymode').change(function() {
    //     let mode = $(this).val();

    //     if (mode !== 'Cash' && mode !== '') {
    //         $('#proof_div').show();
    //         $('#tansaction_div').show();
    //     } else {
    //         $('#proof_div').hide();
    //         $('#tansaction_div').hide();
    //     }
    // });

    $('#paymode').change(function() {
        let mode = $(this).val();

        if (mode === 'Cheque') {
            $('#proof_div').show();
            $('#tansaction_div').show();
        } else if (mode === 'Online') {
            $('#proof_div').show();
            $('#tansaction_div').show();
        } else {
            $('#proof_div').hide();
            $('#tansaction_div').hide();
        }
    });



    function load_bills(account_id) {

        $.ajax({
            url: 'ajax/get_customer_bills.php',
            type: 'POST',
            data: {
                account_id: account_id
            },
            success: function(response) {

                let res = JSON.parse(response);

                $('#bill_id').html(res.html);
                $('#bill_id').trigger('chosen:updated');
            }
        });
    }

    $('#bill_id').on('change', function() {

        let total = $('#bill_id option:selected').data('total') || 0;
        let pending = $('#bill_id option:selected').data('pending') || 0;

        // show total amount
        $('#pending_amt').val(pending);

        // auto fill payment
        $('#pay_amt').val(pending);
    });

    // 🔥 Restrict payment amount
    $('#pay_amt').on('input', function() {

        let pending = $('#bill_id option:selected').data('pending') || 0;
        let entered = parseFloat($(this).val()) || 0;

        if (entered > pending) {
            Swal.fire('Payment cannot be greater than pending amount');
            $(this).val(pending);
        }
    });

    function get_account_details(account_id) {
        if (account_id != '') {
            $('#loader').show();

            $.ajax({
                url: 'ajax/get_account_details.php',
                type: 'POST',
                data: {
                    account_id: account_id
                },
                success: function(response) {
                    let res = JSON.parse(response);

                    $('#account_details_div').html(res.html);
                    $('#mobile_no').val(res.mobile);
                    $('#decision_maker_name').val(res.decision_maker_name);

                    load_bills(account_id);

                    $('#loader').hide();
                }
            });
        }
    }

    function get_bill_details(bill_id) {

        if (bill_id != '') {

            $('#loader').show();

            $.ajax({
                url: 'ajax/get_bill_details.php',
                type: 'POST',
                data: {
                    bill_id: bill_id
                },

                success: function(response) {

                    $('#bill_details_div').html(response);

                    $('#loader').hide();
                }
            });
        }
    }


    $('#dailyEntryForm').on('submit', function(e) {
        e.preventDefault();

        let btn = document.getElementById('save_order_btn');

        if (btn) {
            btn.disabled = true;
            btn.value = "Processing...";
        }

        if (!validateForm(btn)) {
            return;
        }

        getLocationAndProceed(btn);
    });

    function validateForm(btn) {
        let transaction_id = $('#transaction_id').val() || 0;
        let paymode = $('#paymode').val();
        let oldImg = $('#old_img').val();
        let newFile = $('#payment_proof').val();
        let pending = $('#bill_id option:selected').data('pending') || 0;
        let pay_amt = parseFloat($('#pay_amt').val()) || 0;

        oldImg = oldImg ? oldImg.trim() : '';

        if (pay_amt > pending) {
            Swal.fire('Payment amount exceeds pending amount');
            enableBtn(btn);
            return false;
        }

        if ($('#bill_id').val() == '') {
            Swal.fire('Select Bill');
            enableBtn(btn);
            return false;
        }

        if ($('#account_id').val() == '') {
            Swal.fire('Select Customer');
            enableBtn(btn);
            return false;
        }



        if (paymode.trim() == '') {
            Swal.fire('Enter Payment Mode');
            $('#paymode').focus();
            enableBtn(btn);
            return false;
        }

        // ✅ Correct validation
        // if (paymode !== 'Cash') {
        //     if (oldImg === '' && newFile === '') {
        //         Swal.fire('Upload Payment Proof');
        //         enableBtn(btn);
        //         return false;
        //     }
        // }

        // ✅ Cheque → proof mandatory
        if (paymode === 'Cheque') {
            if (oldImg === '' && newFile === '') {
                Swal.fire('Upload Payment Proof');
                enableBtn(btn);
                return false;
            }
        }

        // ✅ Online → trans_id mandatory
        if (paymode === 'Online') {
            let trans_id = $('#trans_id').val().trim();

            if (trans_id === '') {
                Swal.fire('Enter Transaction ID');
                enableBtn(btn);
                return false;
            }
        }

        if ($('#pay_amt').val().trim() == '') {
            Swal.fire('Enter Payment Amount');
            $('#pay_amt').focus();
            enableBtn(btn);
            return false;
        }

        return true;
    }


    function submitDailyEntryForm(btn) {

        let formData = new FormData($('#dailyEntryForm')[0]);

        Swal.fire({
            title: 'Saving...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: 'add_payment.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // alert(response);
                if (response.trim() == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location = 'payment_list.php';
                    });

                } else if (response.trim() == 'updated') {
                    Swal.fire('Updated Successfully', '', 'success');
                    enableBtn(btn);
                    window.location = 'payment_list.php';

                } else if (response.trim() == 'error') {

                    Swal.fire('Fill all required fields', '', 'warning');
                    enableBtn(btn);

                } else {
                    Swal.fire('Unexpected response: ' + response);
                    enableBtn(btn);
                }
            },

            error: function() {
                Swal.fire('Error saving data', '', 'error');
                enableBtn(btn);
            }
        });
    }

    function enableBtn(btn) {
        if (btn) {
            btn.disabled = false;
            btn.value = "Save";
        }
    }

    function getLocationAndProceed(btn) {

        let latitude = '';
        let longitude = '';
        let address = '';

        function proceedSave() {
            $('#latitude').val(latitude);
            $('#longitude').val(longitude);
            $('#address').val(address);
            submitDailyEntryForm(btn);
        }

        if (!navigator.geolocation) {
            return proceedSave();
        }

        navigator.geolocation.getCurrentPosition(

            function(position) {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;

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
                        proceedSave();
                    })
                    .catch(error => {
                        console.log("Address fetch error:", error);
                        proceedSave();
                    });
            },

            function(error) {
                console.log("Location error:", error);
                Swal.fire("Location Error", "location not detected", "warning");
                proceedSave();
            }
        );
    }
</script>

</html>