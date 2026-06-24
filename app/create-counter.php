<?php
include("appsession.php");
$pagename  = 'create-counter.php';
$title     = 'Add Counters';
$tblname   = 'account';
$tblpkey   = 'account_id';
$btn_name  = "Save";
$keyvalue  = isset($_GET["account_id"]) ? $obj->test_input($_GET["account_id"]) : 0;
$current_date = date('Y-m-d');
$data         = $obj->getRouteDashboardData($loginid, $companyid);
$batchNosSql  = $data['batch_no'];
$imgpath = "../admin/uploaded/accounts/";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batch_no     = $obj->test_input($_POST['route_planid']  ?? '');
    $keyvalue     = $obj->test_input($_POST['account_id']    ?? 0);
    $account_name = $obj->test_input($_POST['account_name']  ?? '');
    $mobile_no    = $obj->test_input($_POST['mobile_no']     ?? '');
    $owner_name   = $obj->test_input($_POST['owner_name']    ?? '');
    $owner_mobile = $obj->test_input($_POST['owner_mobile']  ?? '');
    $address      = $obj->test_input($_POST['address']       ?? '');
    $area_id      = $obj->test_input($_POST['area_id']       ?? '');
    $common_id    = $obj->test_input($_POST['common_id']     ?? '');
    $class        = $obj->test_input($_POST['class']         ?? '');
    $type         = 'customer';
    $latitude        = $obj->test_input($_POST['latitude'] ?? '');
    $longitude       = $obj->test_input($_POST['longitude'] ?? '');
    $location_address = $obj->test_input($_POST['location_address'] ?? '');

    if (!$account_name || !$common_id || !$class || !$area_id) {
        echo 'error';
        exit;
    }

    $count = $obj->getvalfield(
        $tblname,
        "count(*)",
        "account_name='$account_name' AND area_id='$area_id' AND account_id!='$keyvalue'"
    );
    if ($count > 0) {
        echo 'duplicate';
        exit;
    }

    $counter_image  = $obj->getvalfield("account", "counter_image", "account_id='$keyvalue'");
    $visiting_image = $obj->getvalfield("account", "visiting_image", "account_id='$keyvalue'");

    if (!empty($_FILES['counter_image']['tmp_name'])) {

        $imageFileType = strtolower(pathinfo($_FILES['counter_image']['name'], PATHINFO_EXTENSION));

        if (in_array($imageFileType, ['png', 'jpeg', 'jpg', 'webp'])) {

            if (!empty($counter_image) && file_exists($imgpath . $counter_image)) {
                @unlink($imgpath . $counter_image);
            }

            $counter_image = $obj->uploadImage($imgpath, $_FILES['counter_image']);
        }
    }

    if (!empty($_FILES['visiting_image']['tmp_name'])) {

        $imageFileType = strtolower(pathinfo($_FILES['visiting_image']['name'], PATHINFO_EXTENSION));

        if (in_array($imageFileType, ['png', 'jpeg', 'jpg', 'webp'])) {

            if (!empty($visiting_image) && file_exists($imgpath . $visiting_image)) {
                @unlink($imgpath . $visiting_image);
            }

            $visiting_image = $obj->uploadImage($imgpath, $_FILES['visiting_image']);
        }
    }


    $form_data = [
        'account_name'      => $account_name,
        'mobile_no'         => $mobile_no,
        'owner_name'        => $owner_name,
        'o_mobile_no'      => $owner_mobile,
        'address'           => $address,
        'common_id'         => $common_id,
        'area_id'           => $area_id,
        'class'             => $class,
        'latitude'          => $latitude,
        'longitude'         => $longitude,
        'location_address'  => $location_address,
        'status'            => 'inactive',
        'type'              => $type,
        'status1'           => 0,
        'createdby'         => $loginid,
        'companyid'         => $companyid,
        'ipaddress'         => $ipaddress,
        'counter_image'     => $counter_image,
        'visiting_image'    => $visiting_image,
    ];

    if ($keyvalue == 0) {
        $form_data['createdate'] = $createdate;
        $account_id = $obj->insert_record_lastid($tblname, $form_data);

        if ($account_id > 0 && $common_id == 7 && $batch_no) {
            $sequence = $obj->getvalfield(
                'route_counter',
                'IFNULL(MAX(sequence),0)+1',
                "batch_no='$batch_no'"
            );
            $obj->insert_record('route_counter', [
                'batch_no'   => $batch_no,
                'account_id' => $account_id,
                'sequence'   => $sequence,
                'createdate' => $createdate,
                'ipaddress'  => $ipaddress,
                'companyid'  => $companyid,
                'createdby'  => $loginid,
            ]);
        }
        echo 'success';
        exit;
    } else {
        $form_data['lastupdated'] = $createdate;
        $obj->update_record($tblname, [$tblpkey => $keyvalue], $form_data);
        echo 'updated';
        exit;
    }
}


if (isset($_GET[$tblpkey])) {
    $btn_name = 'Update';
    $sqledit  = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
    extract($sqledit, EXTR_OVERWRITE);
} else {
    $account_name = $mobile_no = $owner_name = $owner_mobile = $address = $area_id = $route_plan_id = '';
    $type = $class = '';
    $common_id = '7';
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
            <div class="card border-0 shadow-lg mb-3 p-3">
                <form method="POST" id="dailyEntryForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="row mb-2">
                        <div class="col-6">
                            <h5 class="mb-0 fw-bold">Counters</h5>
                        </div>
                        <div class="col-6 text-end">
                            <a href="counter-list.php" class="btn btn-sm btn-primary">Counter List</a>
                        </div>
                    </div>
                    <hr class="mt-0 mb-3">
                    <div class="row g-2">
                        <div class="col-lg-12 col-12">
                            <label class="form-label form-label-sm">Counter Type <span class="text-danger">*</span></label>
                            <select name="common_id" id="common_id" class="chosen-select form-control form-control-sm" onchange="toggleFields()">
                                <option value="">-- Select --</option>
                                <?php
                                $sql = $obj->executequery("SELECT common_id, common_name FROM common_master WHERE type='acc_type' ORDER BY common_id ASC");
                                foreach ($sql as $k): ?>
                                    <option value="<?= $k['common_id'] ?>" <?= ($common_id == $k['common_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['common_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-12 col-12" id="route_div">
                            <label class="form-label form-label-sm">Route Name <span class="text-danger" id="route_star">*</span></label>
                            <select name="route_planid" id="route_planid" class="chosen-select form-control form-control-sm">
                                <option value="">-- Select Route --</option>
                                <?php
                                $sql = $obj->executequery("
                                SELECT R.batch_no, R.route_name,
                                    GROUP_CONCAT(R.day_of_week
                                        ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')
                                        SEPARATOR ', ') AS days
                                FROM route R
                                LEFT JOIN route_plan RP ON R.batch_no = RP.batch_no
                                WHERE R.companyid = '$companyid' AND RP.sales_executive_id = '$loginid'
                                GROUP BY R.batch_no, R.route_name
                                ORDER BY R.route_name ASC
                            ");
                                foreach ($sql as $k): ?>
                                    <option value="<?= $k['batch_no'] ?>" <?= ($batchNosSql == $k['batch_no']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['route_name']) ?> [<?= htmlspecialchars($k['days']) ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-12 col-12">
                            <label class="form-label form-label-sm">Area <span class="text-danger">*</span></label>
                            <select name="area_id" id="area_id" class="chosen-select form-control form-control-sm">
                                <option value="">-- Select Area --</option>
                                <?php
                                $sql = $obj->executequery("SELECT area_id, area_name FROM area_master ORDER BY area_name ASC");
                                foreach ($sql as $k): ?>
                                    <option value="<?= $k['area_id'] ?>" <?= ($area_id == $k['area_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['area_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-12 col-12">
                            <label class="form-label form-label-sm">Class <span class="text-danger">*</span></label>
                            <select name="class" id="class" class="form-control form-control-sm">
                                <option value="">-- Select Class --</option>
                                <option value="A" <?= ($class == 'A') ? 'selected' : '' ?>>A</option>
                                <option value="B" <?= ($class == 'B') ? 'selected' : '' ?>>B</option>
                                <option value="C" <?= ($class == 'C') ? 'selected' : '' ?>>C</option>
                            </select>
                        </div>

                        <div class="col-lg-12 col-12 mb-1">
                            <label class="form-label form-label-sm">Counter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="account_name" name="account_name"
                                placeholder="Enter Counter Name" value="<?= htmlspecialchars($account_name) ?>">
                        </div>

                        <div class="col-lg-12 col-12 mb-1">
                            <label class="form-label form-label-sm">WhatsApp Number</label>
                            <input type="text" class="form-control form-control-sm" id="mobile_no" name="mobile_no"
                                placeholder="10-digit number" maxlength="10" value="<?= htmlspecialchars($mobile_no) ?>"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                        </div>

                        <div class="col-lg-12 col-12 mb-1">
                            <label class="form-label form-label-sm">
                                Owner Name <span class="text-danger" id="owner_name_star">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="owner_name" name="owner_name"
                                placeholder="Enter Owner Name" value="<?= htmlspecialchars($owner_name ?? '') ?>">
                        </div>
                        <div class="col-lg-12 col-12 mb-1" >
                            <label class="form-label form-label-sm">
                                Owner Mobile No. <span class="text-danger" id="owner_mobile_star">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="owner_mobile" name="owner_mobile"
                                placeholder="10-digit number" maxlength="10" value="<?= htmlspecialchars($owner_mobile ?? '') ?>"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                        </div>

                        <div class="col-lg-4 col-12 mb-1">
                            <label class="form-label form-label-sm">
                                Counter Image <?= ($keyvalue == 0) ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            <input type="file" class="form-control form-control-sm" id="counter_image" name="counter_image"
                                accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="col-lg-4 col-12 mb-1">
                            <label class="form-label form-label-sm">Visiting Card Image</label>
                            <input type="file" class="form-control form-control-sm" id="visiting_image" name="visiting_image"
                                accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="col-12">
                            <label class="form-label form-label-sm">Address</label>
                            <textarea class="form-control form-control-sm" id="address" name="address"
                                rows="2" placeholder="Enter Address"><?= htmlspecialchars($address) ?></textarea>
                        </div>

                        <div class="col-12 mt-2">
                            <input type="hidden" name="account_id" value="<?= $keyvalue ?>">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="location_address" id="location_address">
                            <input type="submit" id="save_order_btn" class="btn btn-primary w-100" value="<?= $btn_name ?>">
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
        const IS_EDIT = <?= ($keyvalue > 0) ? 'true' : 'false' ?>;

        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });
            toggleFields();
        });

        function toggleFields() {
            const common_id = document.getElementById('common_id').value;
            const isCustomer = (common_id == 7);

            if (isCustomer) {
                $('#route_div').show();
            } else {
                $('#route_div').hide();
                $('#route_planid').val('').trigger('chosen:updated');
            }
        }

        $('#dailyEntryForm').on('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('save_order_btn');

            btn.disabled = true;
            btn.value = 'Processing...';

            getLocationAndProceed(btn);
        });

        function submitForm(btn) {
            const common_id = $('#common_id').val();
            const isCustomer = (common_id == 7);

            if (!common_id) {
                Swal.fire('Select a Counter Type');
                return enableBtn(btn);
            }
            if (isCustomer && !$('#route_planid').val()) {
                Swal.fire('Select a Route');
                return enableBtn(btn);
            }
            if (!$('#area_id').val()) {
                Swal.fire('Select an Area');
                $('#area_id').focus();
                return enableBtn(btn);
            }
            if (!$('#account_name').val().trim()) {
                Swal.fire('Enter Counter Name');
                $('#account_name').focus();
                return enableBtn(btn);
            }
            if (!$('#class').val()) {
                Swal.fire('Select a Class');
                $('#class').focus();
                return enableBtn(btn);
            }

            if (!IS_EDIT && !$('#counter_image').val()) {
                Swal.fire('Please upload a Counter Image');
                return enableBtn(btn);
            }

            const formData = new FormData($('#dailyEntryForm')[0]);

            Swal.fire({
                title: 'Saving...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: 'create-counter.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success(response) {
                    const r = response.trim();
                    if (r === 'success') {
                        Swal.fire({
                                icon: 'success',
                                title: 'Saved Successfully',
                                timer: 1500,
                                showConfirmButton: false
                            })
                            .then(() => window.location = 'counter-list.php');
                    } else if (r === 'updated') {
                        Swal.fire('Updated Successfully', '', 'success')
                            .then(() => window.location = 'counter-list.php');
                    } else if (r === 'duplicate') {
                        Swal.fire('Duplicate entry — same counter name exists in this area', '', 'warning');
                        enableBtn(btn);
                    } else if (r === 'invalid_image') {
                        Swal.fire('Only JPG, PNG images allowed', '', 'warning');
                        enableBtn(btn);
                    } else if (r === 'error') {
                        Swal.fire('Please fill all required fields', '', 'warning');
                        enableBtn(btn);
                    } else {
                        Swal.fire('Unexpected response: ' + response);
                        enableBtn(btn);
                    }
                },
                error() {
                    Swal.fire('Network error — please try again', '', 'error');
                    enableBtn(btn);
                }
            });
        }

        function getLocationAndProceed(btn) {

            let latitude = '';
            let longitude = '';
            let address = '';

            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Location Required',
                    text: 'Geolocation is not supported by this browser.'
                });

                enableBtn(btn);
                return;
            }

            Swal.fire({
                title: 'Getting Location...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            navigator.geolocation.getCurrentPosition(

                function(position) {

                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;

                    fetch('location.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                latitude: latitude,
                                longitude: longitude
                            })
                        })
                        .then(response => response.json())
                        .then(data => {

                            address = data.address || '';

                            $('#latitude').val(latitude);
                            $('#longitude').val(longitude);
                            $('#location_address').val(address);

                            submitForm(btn);
                        })
                        .catch(() => {

                            $('#latitude').val(latitude);
                            $('#longitude').val(longitude);
                            $('#location_address').val('');

                            submitForm(btn);
                        });
                },

                function(error) {

                    let msg = 'Please allow location access to continue.';

                    if (error.code === error.PERMISSION_DENIED) {
                        msg = 'Location permission denied. Please enable location access.';
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Location Required',
                        text: msg
                    });

                    enableBtn(btn);
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        function enableBtn(btn) {
            btn.disabled = false;
            btn.value = IS_EDIT ? 'Update' : 'Save';
        }
    </script>
</body>

</html>