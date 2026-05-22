<?php include("appsession.php");
$pagename = 'visit-entry.php';
$title = 'Add Daily Entry';
$tblname = 'daily_entries';
$tblpkey = 'entry_id';
$btn_name = "CheckOut";
$keyvalue = (isset($_GET["entry_id"])) ? $obj->test_input($_GET["entry_id"]) : 0;
$imgpath = "uploads/daily_entry/";
$current_date = date('Y-m-d');

$res = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
$visitRow = !empty($res) ? $res : null;
$account_id  = $visitRow['account_id'];
$is_saved    = $visitRow['is_saved'];
$acc_data    = $obj->select_record("account", ['account_id' => $account_id]);

if ($is_saved == 1) {
    echo "<script>location='daily-entrylist.php'</script>";
    die;
}

function prepareUpdateData($fields, $postData, $oldData, $obj)
{
    $finalData = [];
    foreach ($fields as $field) {
        if (isset($postData[$field]) && $postData[$field] !== '') {
            $finalData[$field] = $obj->test_input($postData[$field]);
        } else {
            $finalData[$field] = $oldData[$field];
        }
    }
    return $finalData;
}

function getDistanceMeters($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a    = sin($dLat / 2) * sin($dLat / 2)
          + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
          * sin($dLon / 2) * sin($dLon / 2);
    $c    = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

define('DIST_CLEAN',   30);
define('DIST_WARN',    75);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $keyvalue            = $obj->test_input($_POST['entry_id']);
    $account_id          = $obj->test_input($_POST['account_id']);
    $decision_maker_name = $obj->test_input($_POST['decision_maker_name']);
    $mobile_no           = $obj->test_input($_POST['mobile_no']);
    $latitude            = $obj->test_input($_POST['latitude']);
    $longitude           = $obj->test_input($_POST['longitude']);
    $address             = $obj->test_input($_POST['address']);
    $common_id           = (!empty($_POST['common_id'])) ? $obj->test_input($_POST['common_id']) : '';
    $follow_up_date      = $_POST['follow_up_date'];
    $remarks             = $obj->test_input($_POST['remarks']);
    $force_checkout      = (isset($_POST['force_checkout']) && $_POST['force_checkout'] == '1');

    $lat_in = $obj->getvalfield($tblname, "latitude",  "entry_id='$keyvalue'");
    $lon_in = $obj->getvalfield($tblname, "longitude", "entry_id='$keyvalue'");

    if (empty($lat_in) || empty($lon_in)) {
        echo json_encode(['status' => 'no_checkin_location']);
        exit;
    }

    $distance = getDistanceMeters($lat_in, $lon_in, $latitude, $longitude);
    $distance_rounded = round($distance, 2);

    if ($distance > DIST_WARN) {
        echo json_encode([
            'status'   => 'out_of_range',
            'distance' => $distance_rounded
        ]);
        exit;
    }

    if ($distance > DIST_CLEAN && !$force_checkout) {
        echo json_encode([
            'status'   => 'warned_range',
            'distance' => $distance_rounded
        ]);
        exit;
    }

    $accountFields = ['dob', 'doa', 'no_of_kid', 'no_of_family'];
    $accform = prepareUpdateData($accountFields, $_POST, $acc_data, $obj);

    $form_data = [
        'decision_maker_name' => $decision_maker_name,
        'mobile_no'           => $mobile_no,
        'common_id'           => $common_id,
        'longitude_out'       => $longitude,
        'latitude_out'        => $latitude,
        'address_out'         => $address,
        'follow_up_date'      => $follow_up_date,
        'remarks'             => $remarks,
        'checkout_distance'   => $distance_rounded,
        'is_saved'            => 1,
        'createdby'           => $loginid,
        'companyid'           => $companyid,
        'sessionid'           => $sessionid,
        'ipaddress'           => $ipaddress
    ];

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

    $form_data['lastupdated']   = $createdate;
    $form_data['checkout_time'] = $createdate;

    $obj->update_record($tblname, ["entry_id" => $keyvalue], $form_data);

    echo json_encode(['status' => 'updated']);
    exit;
}


if (isset($_GET[$tblpkey])) {
    $btn_name            = "Update";
    $where               = [$tblpkey => $keyvalue];
    $sqledit             = $obj->select_record($tblname, $where);
    $account_id          = $sqledit['account_id'];
    $decision_maker_name = $sqledit['decision_maker_name'];
    $mobile_no           = $sqledit['mobile_no'];
    $imgname             = $sqledit['imgname'];
    $common_id           = $sqledit['common_id'];
    $follow_up_date      = $sqledit['follow_up_date'];
    $remarks             = $sqledit['remarks'];
} else {
    $mobile_no = $imgname = $decision_maker_name = $common_id = $remarks = "";
    $follow_up_date = date("Y-m-d");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <?php include("inc/css-file.php"); ?>
</head>
<body class="dashboard">
<section class="top-sec">
    <?php include("inc/header.php"); ?>

    <div class="container">
        <div class="card border-0 shadow-lg mb-3">
            <form method="POST" id="dailyEntryForm" enctype="multipart/form-data" autocomplete="off">
                <div class="row">

                    <!-- Header -->
                    <div class="col-6">
                        <h4 class="mb-0">Daily Entry</h4>
                    </div>
                    <div class="col-6 text-end">
                        <a href="daily-entrylist.php" class="btn btn-sm btn-primary">Visiting List</a>
                    </div>
                    <div class="col-12 mb-2 mt-2"><hr class="m-0"></div>

                    <!-- Counter Name -->
                    <div class="col-lg-3 mb-2">
                        <label class="form-label">Counter Name <span class="text-danger fw-bold">*</span></label>
                        <input type="text" class="form-control" value="<?= $acc_data['account_name'] ?>" readonly>
                        <input type="hidden" name="account_id" value="<?= $account_id ?>">
                    </div>

                    <div class="col-lg-12 mb-2" id="account_details_div"></div>

                    <!-- Owner Name -->
                    <div class="col-lg-3 mb-2">
                        <label class="form-label">Owner Name <span class="text-danger fw-bold">*</span></label>
                        <input type="text" class="form-control shadow-sm" id="decision_maker_name"
                               name="decision_maker_name" placeholder="Enter Owner Name"
                               value="<?= $decision_maker_name ?>">
                    </div>

                    <!-- Owner Mobile -->
                    <div class="col-lg-3 mb-2">
                        <label class="form-label">Owner Mobile Number <span class="text-danger fw-bold">*</span></label>
                        <input type="text" class="form-control shadow-sm" id="mobile_no" name="mobile_no"
                               placeholder="Enter Mobile Number" value="<?= $mobile_no ?>"
                               maxlength="10" pattern="[0-9]{10}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                    </div>

                    <!-- Optional account fields -->
                    <?php if (
                        empty($acc_data['dob']) && empty($acc_data['doa']) &&
                        empty($acc_data['no_of_kid']) && empty($acc_data['no_of_family'])
                    ) { ?>
                        <div class="row">
                            <?php if (empty($acc_data['dob'])) { ?>
                                <div class="col-6 mb-2">
                                    <label class="form-label">DOB Of Owner</label>
                                    <input type="date" class="form-control shadow-sm" name="dob" id="dob">
                                </div>
                            <?php } ?>
                            <?php if (empty($acc_data['doa'])) { ?>
                                <div class="col-6 mb-2">
                                    <label class="form-label">Anniversary Of Owner</label>
                                    <input type="date" class="form-control shadow-sm" name="doa" id="doa">
                                </div>
                            <?php } ?>
                            <?php if (empty($acc_data['no_of_kid'])) { ?>
                                <div class="col-6 mb-2">
                                    <label class="form-label">No. Of Kids</label>
                                    <input type="number" class="form-control shadow-sm" name="no_of_kid"
                                           placeholder="Enter No. Of Kids">
                                </div>
                            <?php } ?>
                            <?php if (empty($acc_data['no_of_family'])) { ?>
                                <div class="col-6 mb-2">
                                    <label class="form-label">No. Of Family Memb.</label>
                                    <input type="number" class="form-control shadow-sm" name="no_of_family"
                                           placeholder="Enter No. Of Family Memb.">
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <!-- Product Discussed -->
                    <div class="col-lg-3 mb-2">
                        <label class="form-label">Product Discussed</label>
                        <select class="form-select chosen-select" name="common_id" id="common_id">
                            <option value="">Select</option>
                            <?php
                            $res = $obj->executequery("SELECT * FROM common_master WHERE type='product_display'");
                            foreach ($res as $key) {
                                echo "<option value='{$key['common_id']}'>{$key['common_name']}</option>";
                            } ?>
                        </select>
                        <script>document.getElementById('common_id').value = '<?= $common_id ?>';</script>
                    </div>

                    <!-- Photo -->
                    <input type="hidden" name="imgname" value="<?= $visitRow['imgname'] ?>">
                    <div class="col-12 mb-3">
                        <label class="form-label">Photo <span class="text-danger fw-bold">*</span></label>
                        <input type="file" name="imgname" accept="image/*" capture="environment" class="form-control">
                    </div>
                    <?php if (!empty($visitRow['imgname'])) { ?>
                        <div class="mt-2">
                            <img src="uploads/daily_entry/<?= $visitRow['imgname'] ?>"
                                 alt="Image" style="width:120px;border-radius:10px;border:1px solid #ddd;">
                        </div>
                    <?php } ?>

                    <!-- Follow-up Date -->
                    <div class="col-lg-3 mb-2">
                        <label class="form-label">Follow Up Date</label>
                        <input type="date" class="form-control shadow-sm" id="follow_up_date"
                               name="follow_up_date" value="<?= $follow_up_date ?>" readonly>
                    </div>

                    <!-- Remarks -->
                    <div class="col-lg-3 mb-2">
                        <label class="form-label">Discussion Details / Remarks <span class="text-danger fw-bold">*</span></label>
                        <textarea class="form-control shadow-sm" id="remarks" name="remarks"
                                  placeholder="Enter discussion details with remarks"><?= $remarks ?></textarea>
                    </div>

                    <!-- Hidden geo fields -->
                    <input type="hidden" name="latitude"  id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="address"   id="address">
                    <input type="hidden" name="force_checkout" id="force_checkout" value="0">
                    <input type="hidden" name="<?= $tblpkey ?>" value="<?= $keyvalue ?>">

                    <div class="d-grid mt-4">
                        <input type="submit" name="submit" id="save_order_btn"
                               class="btn btn-primary" value="Check Out">
                    </div>

                </div>
            </form>
        </div>
    </div>
</section>

<div id="loader"><div class="loader-spinner"></div></div>

<?php include("inc/js-file.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const DIST_CLEAN = 30;  
const DIST_WARN  = 75; 
$(document).ready(function () {
    $(".chosen-select").chosen({ width: "100%", search_contains: true });
    get_account_details('<?= $account_id ?>');
});


function get_account_details(account_id) {
    if (!account_id) return;
    $('#loader').show();
    $.ajax({
        url: 'ajax/get_account_details.php',
        type: 'POST',
        data: { account_id },
        success(response) {
            let res = JSON.parse(response);
            $('#account_details_div').html(res.html);
            $('#mobile_no').val(res.mobile);
            $('#decision_maker_name').val(res.decision_maker_name);
            $('#loader').hide();
        }
    });
}

$('#dailyEntryForm').on('submit', function (e) {
    e.preventDefault();
    let btn = document.getElementById('save_order_btn');
    if (btn) { btn.disabled = true; btn.value = "Processing..."; }
    getLocationAndProceed(btn);
});


function getLocationAndProceed(btn) {
    if (!navigator.geolocation) {
        Swal.fire("Error", "Geolocation not supported on this device", "error");
        enableBtn(btn);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            if (position.coords.accuracy > 100) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Weak GPS Signal',
                    text: `Accuracy is ±${Math.round(position.coords.accuracy)}m. Please move to an open area and try again.`
                });
                enableBtn(btn);
                return;
            }

            let lat = position.coords.latitude;
            let lon = position.coords.longitude;

            fetch('location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ latitude: lat, longitude: lon })
            })
            .then(r => r.json())
            .then(data => {
                $('#latitude').val(lat);
                $('#longitude').val(lon);
                $('#address').val(data.address || '');
                submitDailyEntryForm(btn);
            })
            .catch(() => {
                $('#latitude').val(lat);
                $('#longitude').val(lon);
                submitDailyEntryForm(btn);
            });
        },
        function (error) {
            Swal.fire("Location Error", "Could not fetch location. Please enable GPS and try again.", "warning");
            enableBtn(btn);
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

function submitDailyEntryForm(btn) {

    if (!$('#decision_maker_name').val().trim()) {
        Swal.fire('Enter Owner Name'); $('#decision_maker_name').focus(); return enableBtn(btn);
    }
    if (!$('#mobile_no').val().trim()) {
        Swal.fire('Enter Mobile Number'); $('#mobile_no').focus(); return enableBtn(btn);
    }

    let newImg = $('input[type="file"][name="imgname"]').val();
    let oldImg = $('input[type="hidden"][name="imgname"]').val();
    if (!newImg && !oldImg) {
        Swal.fire('Upload Image'); return enableBtn(btn);
    }

    if (!$('#remarks').val().trim()) {
        Swal.fire('Enter Remarks'); $('#remarks').focus(); return enableBtn(btn);
    }

    Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    let formData = new FormData($('#dailyEntryForm')[0]);

    $.ajax({
        url: '',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        success(raw) {
            let res;
            try { res = JSON.parse(raw); } catch (e) { res = { status: raw.trim() }; }

            if (res.status === 'out_of_range') {
                Swal.fire({
                    icon: 'error',
                    title: 'Too Far Away',
                    html: `You are <b>${res.distance} m</b> away from the check-in point.<br>
                           Maximum allowed distance is <b>${DIST_WARN} m</b>.<br>
                           Please move closer and try again.`
                });
                return enableBtn(btn);
            }

            if (res.status === 'warned_range') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Slightly Out of Range',
                    html: `You are <b>${res.distance} m</b> away from the check-in point.<br>
                           Ideal checkout distance is within <b>${DIST_CLEAN} m</b>.<br><br>
                           Do you still want to checkout?`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Checkout',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#f0a500',
                }).then(result => {
                    if (result.isConfirmed) {
                        $('#force_checkout').val('1');   
                        submitDailyEntryForm(btn);    
                    } else {
                        enableBtn(btn);
                    }
                });
                return;
            }

            if (res.status === 'no_checkin_location') {
                Swal.fire('Error', 'No check-in location found. Please check in first.', 'error');
                return enableBtn(btn);
            }

            if (res.status === 'updated') {
                Swal.fire({ icon: 'success', title: 'Checked Out Successfully', timer: 1500, showConfirmButton: false })
                    .then(() => { window.location = 'daily-entrylist.php'; });
                return;
            }

            Swal.fire('Unexpected response', JSON.stringify(res), 'warning');
            enableBtn(btn);
        },

        error() {
            Swal.fire('Error', 'Could not save data. Please try again.', 'error');
            enableBtn(btn);
        }
    });
}

function enableBtn(btn) {
    if (btn) { btn.disabled = false; btn.value = "Check Out"; }
    $('#force_checkout').val('0');   // reset force flag
}
</script>
</body>
</html>