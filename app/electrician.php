<?php
include("appsession.php");
$pagename  = 'electrician.php';
$title     = 'Add Counters';
$tblname   = 'account';
$tblpkey   = 'account_id';
$btn_name  = "Save";
$keyvalue  = isset($_GET["account_id"]) ? $obj->test_input($_GET["account_id"]) : 0;
$type = "electrician";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyvalue = $_POST['account_id'];
    $account_id_map       = $obj->test_input($_POST['account_id_map'] ?? 0);
    $electrician_name = $obj->test_input($_POST['electrician_name'] ?? '');
    $whatsapp_no      = $obj->test_input($_POST['whatsapp_no'] ?? '');
    $latitude      = $obj->test_input($_POST['latitude']);
    $longitude      = $obj->test_input($_POST['longitude']);
    $location_address      = $obj->test_input($_POST['location_address']);

    $dup = $obj->getvalfield("account", "count(*)", "mobile_no='$whatsapp_no' AND account_id_map='$account_id_map' AND account_id!='$keyvalue'");

    if ($dup > 0) {
        echo "duplicate";
        exit;
    }


    if ($keyvalue == 0) {

        $form_data = [
            'account_name' => $electrician_name,
            'mobile_no'      => $whatsapp_no,
            "status"       => "active",
            "userid"       =>  $loginid,
            "account_id_map" => $account_id_map,
            "status1"      => 1,
            'common_id'      => 6, // electrician
            'type'       => $type,
            'latitude'       => $latitude,
            'location_address'       => $location_address,
            'longitude'       => $longitude,
            'createdate'       => $createdate,
            'createdby'        => $loginid,
            'ipaddress'        => $ipaddress,
            'companyid'        => $companyid
        ];

        $obj->insert_record($tblname, $form_data);

        echo "success";
        exit;
    } else {
        $form_data = [
            'account_name' => $electrician_name,
            'mobile_no'      => $whatsapp_no,
            "status"       => "active",
            "account_id_map" => $account_id_map,
            "status1"      => 1,
            'common_id'      => 6, // electrician
            'type'       => $type,
            'latitude'       => $latitude,
            'location_address'       => $location_address,
            'longitude'       => $longitude,
            'lastupdated'       => $createdate,
            'ipaddress'        => $ipaddress,
            'companyid'        => $companyid
        ];

        $obj->update_record(
            $tblname,
            [$tblpkey => $keyvalue],
            $form_data
        );

        echo "updated";
        exit;
    }
}


if (isset($_GET[$tblpkey])) {
    $btn_name = 'Update';
    $sqledit  = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
    extract($sqledit, EXTR_OVERWRITE);
} else {
    $mobile_no = $account_name = '';
    $account_id = 0;
}

$sql = $obj->executequery("SELECT e.*,a.account_name as counter_name
            FROM account e
            LEFT JOIN account a ON a.account_id=e.account_id
            WHERE e.type='$type' and e.userid='$loginid'
            ORDER BY e.account_id DESC
        ");
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
                            <h5 class="mb-0 fw-bold">Electrician Entry</h5>
                        </div>
                    </div>
                    <hr class="mt-0 mb-3">
                    <div class="row g-2">
                        <div class="col-12 mb-2">
                            <label class="form-label">
                                Counter Name <span class="text-danger fw-bold">*</span>
                            </label>

                            <select class="form-select chosen-select" name="account_id_map" id="account_id_map">
                                <option value="">Select</option>
                                <?php
                                $res = $obj->executequery("SELECT DISTINCT a.account_id, a.account_name,
                                   cm.common_name AS account_type, am.area_name
                            FROM route_plan rp
                            JOIN route_counter rc ON rc.batch_no = rp.batch_no
                            JOIN account a        ON a.account_id = rc.account_id
                            LEFT JOIN common_master cm ON cm.common_id = a.common_id AND cm.type = 'acc_type'
                            LEFT JOIN area_master am   ON am.area_id = a.area_id
                            WHERE rp.sales_executive_id = '$loginid'
                            ORDER BY a.account_name ASC
                        ");

                                foreach ($res as $key) {
                                    $selected = ($account_id_map == $key['account_id']) ? 'selected' : '';
                                    echo "<option value='{$key['account_id']}' $selected>{$key['account_name']} [{$key['account_type']}] / {$key['area_name']}</option>";
                                }
                                ?>
                            </select>

                        </div>

                        <div class="col-12 mb-2">
                            <label class="form-label">Electrician Name <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control" id="electrician_name" name="electrician_name"
                                placeholder="Enter Electrician Name" value="<?= htmlspecialchars($account_name) ?>">
                        </div>

                        <div class="col-12 mb-2">
                            <label class="form-label">WhatsApp Number <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control" id="whatsapp_no" name="whatsapp_no"
                                placeholder="10-digit number" maxlength="10" value="<?= htmlspecialchars($mobile_no) ?>"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                        </div>


                        <div class="col-12 mt-2">
                            <input type="hidden" name="elect_id" value="<?= $keyvalue ?>">
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="location_address" id="location_address">
                            <input type="submit" id="save_order_btn" class="btn btn-primary w-100" value="<?= $btn_name ?>">
                        </div>

                    </div>
                </form>
            </div>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-person-workspace text-primary"></i>
                        Electrician List
                    </h6>

                    <span class="badge bg-primary">
                        <?= count($sql) ?> Records
                    </span>
                </div>

                <div class="card-body p-2" id="kaarigarTable">
                    <?php $i = 1;
                    foreach ($sql as $row) {
                    ?>
                        <div class="border rounded p-2 mb-2 bg-white">

                            <div class="d-flex justify-content-between align-items-start">

                                <div class="flex-grow-1">

                                    <div class="fw-semibold text-dark">
                                        <?= $i++; ?>. <?= $row['account_name'] ?>
                                    </div>

                                    <div class="small text-muted text-truncate">
                                        <i class="bi bi-shop"></i>
                                        <?= $row['counter_name'] ?>
                                    </div>

                                    <div class="small">
                                        <a href="tel:<?= $row['mobile_no'] ?>"
                                            class="text-decoration-none me-2">
                                            <i class="bi bi-telephone-fill text-success"></i>
                                            <?= $row['mobile_no'] ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($row['location_address'])) { ?>
                                        <div class="small">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <?= $row['location_address'] ?>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm py-0 px-1"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                                href="<?= $pagename . '?' . $tblpkey . '=' . $row[$tblpkey] ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger"
                                                href="javascript:void(0)"
                                                onclick="funDel(<?= $row[$tblpkey] ?>)">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                        </div>
                    <?php } ?>
                </div>
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
        });

        $('#dailyEntryForm').on('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('save_order_btn');

            btn.disabled = true;
            btn.value = 'Processing...';

            getLocationAndProceed(btn);
        });

        function submitForm(btn) {

            if (!$('#account_id_map').val()) {
                Swal.fire('Please Select Counter');
                return enableBtn(btn);
            }

            if (!$('#electrician_name').val().trim()) {
                Swal.fire('Please Enter Electrician Name');
                $('#electrician_name').focus();
                return enableBtn(btn);
            }

            if (!$('#whatsapp_no').val().trim()) {
                Swal.fire('Please Enter Whatsapp No.');
                $('#whatsapp_no').focus();
                return enableBtn(btn);
            }

            const formData = new FormData($('#dailyEntryForm')[0]);

            $.ajax({
                url: 'electrician.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    response = response.trim();

                    if (response == 'success') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Saved Successfully'
                        }).then(() => {
                            window.location = 'electrician.php';
                        });

                    } else if (response == 'updated') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated Successfully'
                        }).then(() => {
                            window.location = 'electrician.php';
                        });

                    } else if (response == 'duplicate') {

                        Swal.fire(
                            'Duplicate entry. Electrician already exists for this counter.'
                        );

                        enableBtn(btn);

                    } else {

                        Swal.fire(response);
                        enableBtn(btn);
                    }
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

        function funDel(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    jQuery.ajax({
                        type: "POST",
                        url: "delete_master.php",
                        data: {
                            id: id,
                            tblname: '<?= $tblname ?>',
                            tblpkey: '<?= $tblpkey ?>'
                        },
                        success: function() {
                            Swal.fire('Deleted!', 'User has been deleted.', 'success').then(() => {
                                location = '<?= $pagename ?>';
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>