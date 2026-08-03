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

define('DIST_CLEAN',   50);
define('DIST_WARN',    100);
define('CHECKOUT_ACCURACY_ALLOWANCE_CAP', 150);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $keyvalue            = $obj->test_input($_POST['entry_id']);
    $account_id          = $obj->test_input($_POST['account_id']);
    $decision_maker_name = $obj->test_input($_POST['decision_maker_name']);
    $mobile_no           = $obj->test_input($_POST['mobile_no']);
    $o_mobile_no         = $obj->test_input($_POST['o_mobile_no']);
    $latitude            = $obj->test_input($_POST['latitude']);
    $longitude           = $obj->test_input($_POST['longitude']);
    $address             = $obj->test_input($_POST['address']);
    $accuracy            = isset($_POST['accuracy']) ? (float)$obj->test_input($_POST['accuracy']) : 0;
    $common_id           = (!empty($_POST['common_id'])) ? $obj->test_input($_POST['common_id']) : '';
    $dob                 = (!empty($_POST['dob'])) ? $obj->test_input($_POST['dob']) : '';
    $doa                 = (!empty($_POST['doa'])) ? $obj->test_input($_POST['doa']) : '';
    $no_of_kid           = (!empty($_POST['no_of_kid'])) ? $obj->test_input($_POST['no_of_kid']) : '';
    $no_of_family        = (!empty($_POST['no_of_family'])) ? $obj->test_input($_POST['no_of_family']) : '';
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
    $accuracyAllowance = min($accuracy, CHECKOUT_ACCURACY_ALLOWANCE_CAP);
    $distWarnAllowed  = DIST_WARN + $accuracyAllowance;
    $distCleanAllowed = DIST_CLEAN + $accuracyAllowance;

    if ($distance > $distWarnAllowed) {
        echo json_encode([
            'status'   => 'out_of_range',
            'distance' => $distance_rounded
        ]);
        exit;
    }

    if ($distance > $distCleanAllowed && !$force_checkout) {
        echo json_encode([
            'status'   => 'warned_range',
            'distance' => $distance_rounded
        ]);
        exit;
    }

    $accountFields = ['dob', 'doa', 'no_of_kid', 'no_of_family'];
    $accform = prepareUpdateData($accountFields, $_POST, $acc_data, $obj);

    if (!empty($decision_maker_name)) {
        $accform['owner_name'] = $decision_maker_name;
    }

    if (!empty($mobile_no)) {
        $accform['mobile_no'] = $mobile_no;
    }

    if (!empty($o_mobile_no)) {
        $accform['o_mobile_no'] = $o_mobile_no;
    }


    if (!empty($accform)) {
        $obj->update_record(
            "account",
            ["account_id" => $account_id],
            $accform
        );
    }

    $form_data = [
        'decision_maker_name' => $decision_maker_name,
        'mobile_no'           => $mobile_no,
        'o_mobile_no'           => $o_mobile_no,
        'dob'           => $dob,
        'doa'           => $doa,
        'no_of_kid'           => $no_of_kid,
        'no_of_family'           => $no_of_family,
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

            if (empty($acc_data['counter_image'])) {
                $counterImagesPath = "../admin/uploaded/accounts/";
                if (!is_dir($counterImagesPath)) {
                    @mkdir($counterImagesPath, 0755, true);
                }

                if (@copy($imgpath . $filename, $counterImagesPath . $filename)) {
                    $obj->update_record(
                        "account",
                        ["account_id" => $account_id],
                        ["counter_image" => $filename]
                    );
                }
            }
        }
    }


    if (!empty($_POST['imgname'])) {
        $filename = $obj->test_input($_POST['imgname']);
        if (empty($acc_data['counter_image'])) {
            $counterImagesPath = "../admin/uploaded/accounts/";
            if (!is_dir($counterImagesPath)) {
                @mkdir($counterImagesPath, 0755, true);
            }

            if (@copy($imgpath . $filename, $counterImagesPath . $filename)) {
                $obj->update_record(
                    "account",
                    ["account_id" => $account_id],
                    ["counter_image" => $filename]
                );
            }
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
    $o_mobile_no           = $sqledit['o_mobile_no'];
    $common_id           = $sqledit['common_id'];
    $follow_up_date      = $sqledit['follow_up_date'];
    $remarks             = $sqledit['remarks'];
} else {
    $mobile_no = $decision_maker_name = $common_id = $remarks = "";
    $follow_up_date = date("Y-m-d");
}

if (!empty($acc_data['counter_image'])) {
    $photo = "../admin/uploaded/accounts/" . $acc_data['counter_image'];
} elseif (!empty($visitRow['imgname'])) {
    $photo = "uploads/daily_entry/" . $visitRow['imgname'];
} else {
    $photo = "";
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
                        <div class="col-12 mb-2 mt-2">
                            <hr class="m-0">
                        </div>

                        <!-- Counter Name -->
                        <div class="col-lg-3 mb-2">
                            <label class="form-label">Counter Name <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control" value="<?= $acc_data['account_name'] ?>" readonly>
                            <input type="hidden" name="account_id" value="<?= $account_id ?>">
                        </div>

                        <div class="col-lg-12 mb-2" id="account_details_div"></div>

                        <!-- Owner Name -->
                        <div class="col-lg-3 mb-2">
                            <label class="form-label">Whatsapp Number <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="mobile_no" name="mobile_no"
                                placeholder="Enter Mobile Number" value="<?= $mobile_no ?>"
                                maxlength="10" pattern="[0-9]{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                        </div>


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
                            <input type="text" class="form-control shadow-sm" id="o_mobile_no" name="o_mobile_no"
                                placeholder="Enter Mobile Number" value="<?= $o_mobile_no ?>"
                                maxlength="10" pattern="[0-9]{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                        </div>
                        <?php
                        $showDob       = empty($acc_data['dob']) || $acc_data['dob'] == '0000-00-00';
                        $showDoa       = empty($acc_data['doa']) || $acc_data['doa'] == '0000-00-00';
                        $showKids      = $acc_data['no_of_kid'] === 0 || is_null($acc_data['no_of_kid']);
                        $showFamily    = $acc_data['no_of_family'] === 0 || is_null($acc_data['no_of_family']);

                        if ($showDob || $showDoa || $showKids || $showFamily) { ?>
                            <div class="row">
                                <?php if ($showDob) { ?>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">DOB Of Owner</label>
                                        <input type="date"
                                            class="form-control shadow-sm"
                                            name="dob"
                                            id="dob"
                                            max="1999-12-31">
                                    </div>
                                <?php } ?>
                                <?php if ($showDoa) { ?>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Anniversary Of Owner</label>
                                        <input type="date" class="form-control shadow-sm" name="doa" id="doa">
                                    </div>
                                <?php } ?>
                                <?php if ($showKids) { ?>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">No. Of Kids</label>
                                        <input type="number" class="form-control shadow-sm" name="no_of_kid"
                                            placeholder="Enter No. Of Kids">
                                    </div>
                                <?php } ?>
                                <?php if ($showFamily) { ?>
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
                            <script>
                                document.getElementById('common_id').value = '<?= $common_id ?>';
                            </script>
                        </div>

                        <!-- Photo -->
                        <?php if (empty($photo)) { ?>
                            <div class="col-12 mb-3">
                                <label class="form-label">Photo <span class="text-danger fw-bold">*</span></label>
                                <input type="file" name="imgname" accept="image/*" capture="environment" class="form-control">
                            </div>
                        <?php }
                        if (!empty($photo)) { ?>
                            <div class="col-12 mb-3">
                                <label class="form-label">Photo <span class="text-danger fw-bold">*</span></label>
                                <br>
                                <img src="<?= $photo; ?>"
                                    alt="Image" style="width:120px;border-radius:10px;border:1px solid #ddd;">
                            </div>
                            <input type="hidden" name="imgname" value="<?= ($acc_data['counter_image'] != '') ? $acc_data['counter_image'] : $visitRow['imgname'] ?>">
                        <?php } ?>

                        <!-- Follow-up Date -->
                        <div class="col-lg-3 mb-2">
                            <label class="form-label">Follow Up Date</label>
                            <input type="date" class="form-control shadow-sm" id="follow_up_date"
                                name="follow_up_date" value="<?= $follow_up_date ?>">
                        </div>

                        <!-- Remarks -->
                        <div class="col-lg-3 mb-2">
                            <label class="form-label">Discussion Details / Remarks <span class="text-danger fw-bold">*</span></label>
                            <textarea class="form-control shadow-sm" id="remarks" name="remarks"
                                placeholder="Enter discussion details with remarks"><?= $remarks ?></textarea>
                        </div>

                        <!-- Hidden geo fields -->
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="address" id="address">
                        <input type="hidden" name="accuracy" id="accuracy">
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

    <div id="loader">
        <div class="loader-spinner"></div>
    </div>

    <?php include("inc/js-file.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const GOOGLE_API_KEY = "AIzaSyD60TsOPfBQDMpiGwEWusBT-UBUUM6Y8O8";

        const TARGET_ACCURACY = 25;
        const MAX_WAIT_TIME = 30000;

        function getAccurateLocation(onProgress) {
            return new Promise((resolve, reject) => {

                if (!navigator.geolocation) {
                    reject('Geolocation is not supported by this browser.');
                    return;
                }

                let bestPosition = null;
                let bestAccuracy = Infinity;
                let watchId = null;
                let finished = false;

                function finish() {
                    if (finished) return;
                    finished = true;
                    if (watchId !== null) navigator.geolocation.clearWatch(watchId);

                    if (bestPosition) {
                        resolve({
                            lat: bestPosition.coords.latitude,
                            lng: bestPosition.coords.longitude,
                            accuracy: bestPosition.coords.accuracy
                        });
                    } else {
                        reject('GPS location not found. Please turn on GPS and try again.');
                    }
                }

                watchId = navigator.geolocation.watchPosition(
                    function(position) {
                        let accuracy = position.coords.accuracy;

                        if (accuracy < bestAccuracy) {
                            bestAccuracy = accuracy;
                            bestPosition = position;
                            if (onProgress) onProgress(accuracy);
                        }

                        if (bestAccuracy <= TARGET_ACCURACY) {
                            finish();
                        }
                    },
                    function(error) {
                        if (finished) return;
                        finished = true;
                        if (watchId !== null) navigator.geolocation.clearWatch(watchId);

                        let message = 'Location permission denied. Please allow GPS.';
                        if (error.code === 1) message = 'Location permission denied. Please enable location access.';
                        else if (error.code === 2) message = 'Location unavailable. Please turn on GPS.';
                        else if (error.code === 3) message = 'Location timeout. Please try again.';
                        reject(message);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: MAX_WAIT_TIME
                    }
                );

                setTimeout(finish, MAX_WAIT_TIME);
            });
        }

        async function reverseGeocode(lat, lng) {
            try {
                let res = await fetch(
                    'https://maps.googleapis.com/maps/api/geocode/json?latlng=' +
                    lat + ',' + lng + '&key=' + GOOGLE_API_KEY
                );
                let data = await res.json();

                if (data.status === 'OK' && data.results.length > 0) {
                    return data.results[0].formatted_address;
                }
            } catch (e) {
                console.log('Google geocode failed', e);
            }

            try {
                let res = await fetch(
                    'https://nominatim.openstreetmap.org/reverse?format=json' +
                    '&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1'
                );
                let data = await res.json();
                return data.display_name || '';
            } catch (e) {
                console.log('OSM geocode failed', e);
                return '';
            }
        }
        // ---------------------------------------------------------------

        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });
            get_account_details('<?= $account_id ?>');
        });


        function get_account_details(account_id) {
            if (!account_id) return;
            $('#loader').show();
            $.ajax({
                url: 'ajax/get_account_details.php',
                type: 'POST',
                data: {
                    account_id
                },
                success(response) {
                    let res = JSON.parse(response);
                    $('#account_details_div').html(res.html);
                    $('#o_mobile_no').val(res.mobile);
                    $('#mobile_no').val(res.mobile);
                    $('#decision_maker_name').val(res.decision_maker_name);
                    $('#loader').hide();
                }
            });
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


        async function getLocationAndProceed(btn) {

            Swal.fire({
                title: 'Finding accurate GPS location...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const loc = await getAccurateLocation((accuracy) => {
                    Swal.update({
                        title: 'Improving GPS accuracy...',
                        html: 'Current accuracy: ' + accuracy.toFixed(1) + ' meter'
                    });
                });

                // Best effort still weak after the full wait -> warn, but let
                // the user decide whether to proceed (server-side accuracy
                // allowance also softens the distance check for them).
                if (loc.accuracy > 100) {
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Weak GPS Signal',
                        html: `Best accuracy achieved was ±${Math.round(loc.accuracy)}m.<br>Move to an open area for a better fix, or continue anyway.`,
                        showCancelButton: true,
                        confirmButtonText: 'Continue Anyway',
                        cancelButtonText: 'Try Again'
                    });

                    if (!result.isConfirmed) {
                        enableBtn(btn);
                        return;
                    }
                }

                Swal.fire({
                    title: 'Fetching address...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const address = await reverseGeocode(loc.lat, loc.lng);

                $('#latitude').val(loc.lat);
                $('#longitude').val(loc.lng);
                $('#address').val(address || '');
                $('#accuracy').val(loc.accuracy);

                submitDailyEntryForm(btn);

            } catch (msg) {
                Swal.fire('Location Error', typeof msg === 'string' ? msg : 'Could not fetch location. Please enable GPS and try again.', 'warning');
                enableBtn(btn);
            }
        }

        function submitDailyEntryForm(btn) {

            if (!$('#decision_maker_name').val().trim()) {
                Swal.fire('Enter Owner Name');
                $('#decision_maker_name').focus();
                return enableBtn(btn);
            }
            if (!$('#mobile_no').val().trim()) {
                Swal.fire('Enter Mobile Number');
                $('#mobile_no').focus();
                return enableBtn(btn);
            }

            let newImg = $('input[type="file"][name="imgname"]').val();
            let oldImg = $('input[type="hidden"][name="imgname"]').val();
            if (!newImg && !oldImg) {
                Swal.fire('Upload Image');
                return enableBtn(btn);
            }

            if (!$('#remarks').val().trim()) {
                Swal.fire('Enter Remarks');
                $('#remarks').focus();
                return enableBtn(btn);
            }

            Swal.fire({
                title: 'Saving...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            let formData = new FormData($('#dailyEntryForm')[0]);

            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                success(raw) {
                    let res;
                    try {
                        res = JSON.parse(raw);
                    } catch (e) {
                        res = {
                            status: raw.trim()
                        };
                    }

                    if (res.status === 'out_of_range') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Too Far Away',
                            html: `You are <b>${res.distance} m</b> away from the check-in point.<br>
                           Maximum allowed distance is <b>100 m</b>.<br>
                           Please move closer and try again.`
                        });
                        return enableBtn(btn);
                    }

                    if (res.status === 'warned_range') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Slightly Out of Range',
                            html: `You are <b>${res.distance} m</b> away from the check-in point.<br>
                           Ideal checkout distance is within <b>50 m</b>.<br><br>
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
                        Swal.fire({
                                icon: 'success',
                                title: 'Checked Out Successfully',
                                timer: 1500,
                                showConfirmButton: false
                            })
                            .then(() => {
                                window.location = 'daily-entrylist.php';
                            });
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
            if (btn) {
                btn.disabled = false;
                btn.value = "Check Out";
            }
            $('#force_checkout').val('0'); // reset force flag
        }
    </script>
</body>

</html>