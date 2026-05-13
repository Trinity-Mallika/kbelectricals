<?php include("appsession.php");
$pagename = 'check-in.php';
$title = 'Check In';
$tblname = 'daily_entries';
$tblpkey = 'visit_id';
$imgpath = "uploads/checkin/";
$btn_name = "Save";
$keyvalue = (isset($_GET["entry_id"])) ? $obj->test_input($_GET["entry_id"]) : 0;
$data = $obj->getRouteDashboardData($loginid, $companyid);
$route_plan_id = $data['route_plan_id'];
$current_date = date('Y-m-d');



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            echo "success";
        } else {
            echo "error";
        }

        exit;
    }


    $account_id = $obj->test_input($_POST['account_id']);
    $latitude = $obj->test_input($_POST['latitude']);
    $longitude = $obj->test_input($_POST['longitude']);
    $address = $obj->test_input($_POST['address']);

    if ($account_id == '') {
        echo "error";
        exit;
    }

    // $openVisit = $obj->getvalfield(
    //     "daily_entries",
    //     "visit_id",
    //     "createdby='$loginid'
    //      AND visit_status='checked_in'
    //      AND companyid='$companyid'"
    // );

    $openVisit = $obj->getvalfield(
        "daily_entries",
        "entry_id",
        "createdby='$loginid'
     AND companyid='$companyid'
      AND DATE(createdate)=CURDATE()
     AND checkout_time IS NULL"
    );

    if ($openVisit > 0) {
        echo "open_visit";
        exit;
    }

    $exists = $obj->getvalfield(
        "daily_entries",
        "count(*)",
        "account_id='$account_id'
         AND createdby='$loginid'
         AND DATE(createdate)=CURDATE()
         AND companyid='$companyid'"
    );

    if ($exists > 0) {
        echo "duplicate";
        exit;
    }

    $batch_no = $obj->getvalfield(
        "route_plan",
        "batch_no",
        "route_planid='$route_plan_id'"
    );




    $createdate = date('Y-m-d H:i:s');

    $entry_id = $obj->insert_record_lastid("daily_entries", [
        'account_id' => $account_id,
        'decision_maker_name' => '', // or from form
        'mobile_no' => '', // or from form
        'common_id' => 0,  // if needed
        'follow_up_date' => date('Y-m-d'), // or from form
        'remarks' => '', // optional
        'longitude' => $longitude,
        'latitude' => $latitude,
        'address' => $address,
        'createdby' => $loginid,
        'ipaddress' => $ipaddress,
        'createdate' => $createdate,
        'lastupdated' => date('Y-m-d'),
        'companyid' => $companyid,
        'sessionid' => 0,
        'checkin_time' => $createdate
    ]);
    echo $entry_id;
    exit;
}




$openVisit = $obj->executequery("
    SELECT
        vc.entry_id,
        vc.checkin_time,
        a.account_name,
        a.mobile_no,
        ar.area_name
    FROM daily_entries vc
    JOIN account a ON a.account_id = vc.account_id
    LEFT JOIN area_master ar ON ar.area_id = a.area_id
    WHERE vc.createdby = '$loginid'
      AND vc.companyid = '$companyid'
      AND vc.checkout_time IS NULL
      AND DATE(vc.checkin_time) = CURDATE()
    ORDER BY vc.entry_id DESC
    LIMIT 1
");

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
            <?php if (!empty($openVisit)) {
                $ov = $openVisit[0];
            ?>
                <div class="card border-0 shadow-lg mb-3 bg-light-warning">
                    <div class="row align-items-center">

                        <div class="col-12">
                            <h5 class="mb-0 text-warning">
                                <i class="bi bi-clock-history"></i>
                                Already Checked In
                            </h5>
                        </div>

                        <div class="col-12 mt-2 mb-2">
                            <hr class="m-0">
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong class="text-dark">
                                    <?= $ov['account_name'] ?>
                                </strong>
                                <span class="badge bg-warning text-dark mt-1">
                                    Active Visit
                                </span>
                            </div>

                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-geo-alt"></i>
                                <?= $ov['area_name'] ?>
                            </small>

                            <small class="text-muted d-block">
                                <i class="bi bi-telephone"></i>
                                <?= $ov['mobile_no'] ?>
                            </small>

                            <small class="text-success d-block mt-1">
                                <i class="bi bi-clock"></i>
                                Checked in:
                                <?= date('d M Y h:i A', strtotime($ov['checkin_time'])) ?>
                            </small>

                            <div class="mt-3 row g-2">
                                <div class="col-8">
                                    <a href="visit-entry.php?entry_id=<?= $ov['entry_id'] ?>"
                                        class="btn btn-primary btn-sm rounded-pill w-100">
                                        Continue Visit
                                    </a>
                                </div>

                                <div class="col-4">
                                    <button type="button" onclick="deleteVisit(<?= $ov['entry_id'] ?>)"
                                        class="btn btn-red btn-sm rounded-pill w-100">
                                        Delete
                                    </button>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="card border-0 shadow-lg mb-3">
                    <form method="POST" id="dailyEntryForm" enctype="multipart/form-data" autocomplete="off">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="mb-0"> Check In</h4>
                            </div>
                            <div class="col-6 text-end ">
                                 <a href="daily-entrylist.php" class="btn btn-sm btn-primary">Visiting List</a>
                            </div>
                            <div class="col-12 mb-2 mt-2">
                                <hr class="m-0">
                            </div>
                            <div class="col-lg-12 mb-2">
                                <label for="" class="form-label w-100 "> Counter Name <span
                                        class="text-danger fw-bold">*</span> <a href="javascript:void(0)"
                                        class="btn btn-sm btn-primary p-1 mt-1 float-end " onclick="openModal();">+
                                        Add</a></label>
                                <select class="form-select chosen-select" name="account_id" id="account_id"
                                    onchange="get_account_details(this.value);">
                                    <option value="">Select</option>
                                    <?php
                                    $res = $obj->executequery("SELECT
        rc.sequence,
        a.account_id,
        a.account_name,
        cm.common_name
    FROM route_plan rp
    JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
    JOIN account a
        ON a.account_id = rc.account_id
    LEFT JOIN common_master cm
        ON cm.common_id = a.common_id
    WHERE rp.route_planid = '$route_plan_id'
        AND rp.companyid = '$companyid'
        AND rc.companyid = '$companyid'
    
        AND (cm.type = 'acc_type' OR cm.type IS NULL)
    ORDER BY rc.sequence ASC
");

                                    foreach ($res as $key) {
                                        echo "<option value='{$key['account_id']}'>
            {$key['sequence']}. {$key['account_name']} [{$key['common_name']}]
          </option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-12 mb-2" id="account_details_div">

                            </div>

                            <div class="d-grid mt-4">
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                                <input type="hidden" name="address" id="address">
                                <input type="submit" name="submit" id="save_order_btn" class="btn btn-primary"
                                    value="Check In">
                            </div>
                        </div>

                    </form>

                </div>
            <?php } ?>
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
            url: 'check-in.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {

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

    function get_account_details(account_id) {
        if (account_id !== '') {
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
                    $('#loader').hide();
                },

                error: function() {
                    $('#loader').hide();
                    Swal.fire('Error', 'Unable to fetch account details', 'error');
                }
            });
        } else {
            $('#account_details_div').html('');
        }
    }

    $('#dailyEntryForm').on('submit', function(e) {
        e.preventDefault();

        let btn = document.getElementById('save_order_btn');

        if (btn) {
            btn.disabled = true;
            btn.value = "Checking In...";
        }

        getLocationAndProceed(btn);
    });

    function submitCheckInForm(btn) {

        if ($('#account_id').val() === '') {
            Swal.fire('Select Counter');
            return enableBtn(btn);
        }

        let formData = new FormData($('#dailyEntryForm')[0]);

        Swal.fire({
            title: 'Checking In...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: 'check-in.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(response) {

                response = response.trim();

                // visit_id returned
                if ($.isNumeric(response)) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Check In Successful',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => {
                        window.location =
                            'visit-entry.php?entry_id=' + response;
                    });

                } else if (response === 'open_visit') {

                    Swal.fire(
                        'Pending Visit',
                        'Complete previous checkout first',
                        'warning'
                    );
                    enableBtn(btn);

                } else if (response === 'duplicate') {

                    Swal.fire(
                        'Already Checked In',
                        'This counter already checked today',
                        'warning'
                    );
                    enableBtn(btn);

                } else if (response === 'image_required') {

                    Swal.fire(
                        'Photo Required',
                        'Upload check-in photo',
                        'warning'
                    );
                    enableBtn(btn);

                } else if (response === 'error') {

                    Swal.fire(
                        'Missing Data',
                        'Select counter properly',
                        'warning'
                    );
                    enableBtn(btn);

                } else {

                    Swal.fire(
                        'Unexpected Response',
                        response,
                        'error'
                    );
                    enableBtn(btn);

                }
            },

            error: function() {
                Swal.fire(
                    'Error',
                    'Unable to save check-in',
                    'error'
                );
                enableBtn(btn);
            }
        });
    }

    function enableBtn(btn) {
        if (btn) {
            btn.disabled = false;
            btn.value = "Check In";
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

            submitCheckInForm(btn);
        }

        if (!navigator.geolocation) {
            proceedSave();
            return;
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
                    .catch(() => {
                        proceedSave();
                    });

            },

            function() {
                proceedSave();
            }
        );
    }


    function deleteVisit(visit_id) {
        Swal.fire({
            title: 'Delete this visit?',
            text: 'This checked-in record will be removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: 'delete_visit.php',
                    type: 'POST',
                    data: {
                        visit_id: visit_id
                    },

                    success: function(res) {

                        console.log(res); // 🔥 ADD THIS
                        // alert(res); // 🔥 ADD THIS

                        if (res.trim() === 'success') {
                            Swal.fire(
                                'Deleted',
                                'Visit removed successfully',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error',
                                'Unable to delete visit',
                                'error'
                            );
                        }

                    }
                });

            }

        });
    }
</script>

</html>