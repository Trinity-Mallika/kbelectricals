<?php include("appsession.php");
$pagename = 'visit-entry.php';
$title = 'Add Daily Entry';
$tblname = 'daily_entries';
$tblpkey = 'entry_id';
$btn_name = "CheckOut";
$keyvalue = (isset($_GET["entry_id"])) ? $obj->test_input($_GET["entry_id"]) : 0;
$imgpath = "uploads/daily_entry/";
$current_date = date('Y-m-d');

$res = $obj->executequery("
    SELECT * FROM daily_entries 
    WHERE entry_id = '$keyvalue' 
    LIMIT 1
");

$visitRow = !empty($res) ? $res[0] : null;

$account_id    = $visitRow['account_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $keyvalue = $obj->test_input($_POST['entry_id']);
    $account_id = $obj->test_input($_POST['account_id']);
    $decision_maker_name = $obj->test_input($_POST['decision_maker_name']);
    $mobile_no = $obj->test_input($_POST['mobile_no']);
    $latitude = $obj->test_input($_POST['latitude']);
    $longitude = $obj->test_input($_POST['longitude']);
    $address = $obj->test_input($_POST['address']);

    $common_id = (!empty($_POST['common_id']))
        ? $obj->test_input($_POST['common_id'])
        : '';
    $follow_up_date = $_POST['follow_up_date'];
    $remarks = $obj->test_input($_POST['remarks']);

    if ($account_id == "" || $decision_maker_name == "" || $mobile_no == "" || $remarks == "") {
        echo "error";
        exit;
    }

    $form_data = array(

        'account_id' => $account_id,
        'decision_maker_name' => $decision_maker_name,
        'mobile_no' => $mobile_no,
        'common_id' => $common_id,
        'longitude_out' => $longitude,
        'latitude_out' => $latitude,
        'address_out' => $address,

        'follow_up_date' => $follow_up_date,
        'remarks' => $remarks,
        'createdby' => $loginid,
        'companyid' => $companyid,
        'ipaddress' => $ipaddress
    );

    if ($keyvalue == 0 && $_FILES['imgname']['name'] == "") {
        echo "image_required";
        exit;
    }

    if (!empty($_FILES["imgname"]['name'])) {

        $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);

        if ($filename != "") {
            if ($keyvalue != 0) {
                $old = $obj->getvalfield($tblname, "imgname", "entry_id='$keyvalue'");
                if ($old != "") {
                    @unlink($imgpath . $old);
                }
            }

            $form_data['imgname'] = $filename;
        }
    }
    $createdate = date('Y-m-d H:i:s');

    $form_data['lastupdated'] = date('Y-m-d');
    $form_data['checkout_time'] = $createdate;

    $obj->update_record($tblname, ["entry_id" => $keyvalue], $form_data);

    echo "updated";
    exit;
}




if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $account_id = $sqledit['account_id'];
    $decision_maker_name = $sqledit['decision_maker_name'];
    $mobile_no = $sqledit['mobile_no'];
    $imgname = $sqledit['imgname'];

    $common_id = $sqledit['common_id'];
    $follow_up_date = $sqledit['follow_up_date'];
    $remarks = $sqledit['remarks'];
} else {
    $mobile = $mobile_no = $imgname = $decision_maker_name = $common_id = $remarks = "";
    $follow_up_date = date("Y-m-d");
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
                            <h4 class="mb-0"> Daily Entry</h4>
                        </div>
                        <div class="col-6 text-end ">
                            <a href="daily-entrylist.php" class="btn btn-sm btn-primary">Visiting List</a>
                        </div>
                        <div class="col-12 mb-2 mt-2">
                            <hr class="m-0">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Counter Name <span class="text-danger fw-bold">*</span></label>
                            <?php
                            $acc = $obj->select_record("account", ["account_id" => $account_id]);
                            ?>
                            <input type="text"
                                class="form-control"
                                value="<?= $acc['account_name'] ?>"
                                readonly>

                            <input type="hidden"
                                name="account_id"
                                value="<?= $account_id ?>">
                        </div>
                        <div class="col-lg-12 mb-2" id="account_details_div">

                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Decision Makers Name <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="decision_maker_name" name="decision_maker_name" placeholder="Enter Decision Makers Name" value="<?php echo $decision_maker_name ?>">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Mobile Number <span class="text-danger fw-bold">*</span></label>
                            <input type="text"
                                class="form-control shadow-sm"
                                id="mobile_no"
                                name="mobile_no"
                                placeholder="Enter Mobile Number"
                                value="<?php echo $mobile_no ?>"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Product Discussed</label>
                            <select class="form-select chosen-select" name="common_id" id="common_id">
                                <option value="">Select</option>
                                <?php $res = $obj->executequery("Select * from common_master where type='product_display'");
                                foreach ($res as $key) {
                                    echo "<option value='{$key['common_id']}'>{$key['common_name']}</option>";
                                } ?>
                            </select>
                            <script>
                                document.getElementById('common_id').value = '<?= $common_id ?>';
                            </script>
                        </div>



                        <input type="hidden" name="imgname" value="<?php echo $visitRow['imgname']; ?>">

                        <div class="col-12 mb-3">
                            <label class="form-label"> Photo <span class="text-danger fw-bold">*</span></label>
                            <input type="file" name="imgname" accept="image/*" capture="environment"
                                class="form-control">
                        </div>
                        <?php if (!empty($visitRow['imgname'])) { ?>
                            <div class="mt-2">
                                <img src="uploads/daily_entry/<?php echo $visitRow['imgname']; ?>"
                                    alt="Image"
                                    style="width: 120px; border-radius: 10px; border:1px solid #ddd;">
                            </div>
                        <?php } ?>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Follow Up date</label>
                            <input type="date" class="form-control shadow-sm" id="follow_up_date" name="follow_up_date" value="<?php echo $follow_up_date; ?>" readonly>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Discussion details with remarks <span class="text-danger fw-bold">*</span></label>
                            <textarea class="form-control shadow-sm" id="remarks" name="remarks" placeholder="Enter Discussion details with remarks"><?= $remarks; ?></textarea>
                        </div>
                        <div class="d-grid mt-4">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="address" id="address">
                            <input type="hidden" name="<?= $tblpkey ?>" value="<?php echo $keyvalue ?>">
                            <input type="submit" name="submit" id="save_order_btn" class="btn btn-primary" value="CheckOut">
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
        getLocationAndProceed(btn);
    });

    function submitDailyEntryForm(btn) {

        if ($('#account_id').val() == '') {
            Swal.fire('Select Customer');
            return enableBtn(btn);
        }

        if ($('#decision_maker_name').val().trim() == '') {
            Swal.fire('Enter Decision Maker Name');
            $('#decision_maker_name').focus();
            return enableBtn(btn);
        }

        if ($('#mobile_no').val().trim() == '') {
            Swal.fire('Enter Mobile Number');
            $('#mobile_no').focus();
            return enableBtn(btn);
        }

        let newImg = $('input[type="file"][name="imgname"]').val();
        let oldImg = $('input[name="imgname"]').val();

        if (!newImg && !oldImg) {
            Swal.fire('Upload Image');
            return enableBtn(btn);
        }

        if ($('#remarks').val().trim() == '') {
            Swal.fire('Enter Remarks');
            $('#remarks').focus();
            return enableBtn(btn);
        }

        let formData = new FormData($('#dailyEntryForm')[0]);

        Swal.fire({
            title: 'Saving...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '',
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
                        window.location = 'daily-entrylist.php';
                    });

                } else if (response.trim() == 'updated') {

                    Swal.fire('Updated Successfully', '', 'success');
                    enableBtn(btn);
                    window.location = 'daily-entrylist.php';

                } else if (response.trim() == 'duplicate') {

                    Swal.fire('Duplicate entry not allowed', '', 'warning');
                    enableBtn(btn);

                } else if (response.trim() == 'error') {

                    Swal.fire('Fill all required fields', '', 'warning');
                    enableBtn(btn);

                } else if (response.trim() == 'image_required') {

                    Swal.fire('Upload Image', '', 'warning');
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
            btn.value = "CheckOut";
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

            console.log("FINAL VALUES:");
            console.log(latitude, longitude, address);

            submitDailyEntryForm(btn);
        }

        if (!navigator.geolocation) {
            Swal.fire("Error", "Geolocation not supported", "error");
            return proceedSave();
        }

        navigator.geolocation.getCurrentPosition(

            function(position) {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;
                console.log("COORDS:", latitude, longitude);

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
                Swal.fire("Location Error", "Saving without location", "warning");
                proceedSave();
            }
        );
    }
</script>

</html>