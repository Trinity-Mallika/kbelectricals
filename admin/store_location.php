<?php
include("../adminsession.php");
$title = "Store Location";
$pagename = "store_location.php";
$module = "Store Location";
$submodule = "Store Location List";
$btn_name = "Save";
// $tblname = "setting";
// $tblpkey = "setting_id";
// $keyvalue = (isset($_GET["setting_id"])) ? $obj->test_input($_GET["setting_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

/* =========================================================
   SAVE / UPDATE
========================================================= */

if (isset($_POST['save'])) {
    $token = !empty($_POST['qr_token'])
        ? $_POST['qr_token']
        : bin2hex(random_bytes(16));

    $fields = [


        'location_name'   => $_POST['location_name'],
        'latitude'        => $_POST['latitude'],
        'longitude'       => $_POST['longitude'],
        'radius_meter'    => $_POST['radius_meter'],
        'qr_token'        => $token,
        'status'          => $_POST['status']

    ];

    /* INSERT */

    if ($_POST['location_id'] == '') {
        $obj->insert_record(
            'store_location',
            $fields
        );
    }

    /* UPDATE */ else {
        $obj->update_record(
            'store_location',
            ['location_id' => $_POST['location_id']],
            $fields
        );
    }

    echo "
    <script>
        location='store_location.php?msg=save'
    </script>";
}

/* =========================================================
   DELETE
========================================================= */

if (isset($_GET['del'])) {
    $location_id = intval($_GET['del']);

    $obj->delete_record(
        "store_location",
        ['location_id' => $location_id]
    );

    echo "
    <script>
        location='store_location.php?msg=del'
    </script>";
}

/* =========================================================
   EDIT DATA
========================================================= */

$edit = [];

if (isset($_GET['edit'])) {
    $location_id = intval($_GET['edit']);

    $result = $obj->executequery("
        SELECT *
        FROM store_location
        WHERE location_id='$location_id'
    ");

    if ($result) {
        $edit = $result[0];
    }
}

/* =========================================================
   FETCH LIST
========================================================= */

$rows = $obj->executequery("
    SELECT *
    FROM store_location
    ORDER BY location_id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tag -->
    <?php include('component/css.php'); ?>
    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        .card-header {
            background-color: #06163a;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- Header -->
        <?php include('component/header.php'); ?>
        <!-- Header Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $title ?></legend>
                        <?php include('component/alert.php'); ?>
                        <div class="row g-3">

                            <!-- =====================================================
                 FORM SECTION
            ====================================================== -->

                            <div class="col-lg-4">

                                <div class="card border-0 shadow-sm rounded-4">

                                    <div class="card-body">

                                        <h5 class="mb-3">
                                            Location Entry
                                        </h5>

                                        <form method="POST">

                                            <input type="hidden"
                                                name="location_id"
                                                value="<?php echo $edit['location_id'] ?? ''; ?>">

                                            <input type="hidden"
                                                name="qr_token"
                                                value="<?php echo $edit['qr_token'] ?? ''; ?>">


                                            <!-- LOCATION -->

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Location Name
                                                </label>

                                                <input type="text"
                                                    name="location_name"
                                                    class="form-control"
                                                    placeholder="Enter venue / chapter location"
                                                    required
                                                    value="<?php echo $edit['location_name'] ?? ''; ?>">

                                            </div>

                                            <!-- LAT / LONG -->

                                            <div class="row">

                                                <div class="col-6 mb-3">

                                                    <label class="form-label">
                                                        Latitude
                                                    </label>

                                                    <input type="text"
                                                        name="latitude"
                                                        class="form-control"
                                                        placeholder="21.2514"
                                                        required
                                                        value="<?php echo $edit['latitude'] ?? ''; ?>">

                                                </div>

                                                <div class="col-6 mb-3">

                                                    <label class="form-label">
                                                        Longitude
                                                    </label>

                                                    <input type="text"
                                                        name="longitude"
                                                        class="form-control"
                                                        placeholder="81.6296"
                                                        required
                                                        value="<?php echo $edit['longitude'] ?? ''; ?>">

                                                </div>

                                            </div>

                                            <!-- CURRENT LOCATION -->

                                            <div class="mb-3">

                                                <button type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    onclick="getVenueLocation()">

                                                    <i class="bi bi-geo-alt"></i>
                                                    Use Current Location

                                                </button>

                                            </div>
                                            <div id="gps_status"></div>
                                            <!-- RADIUS -->

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Allowed Radius Meter
                                                </label>

                                                <input type="number"
                                                    name="radius_meter"
                                                    class="form-control"
                                                    placeholder="Enter radius in meter"
                                                    required
                                                    value="<?php echo $edit['radius_meter'] ?? 10; ?>">

                                            </div>

                                            <!-- STATUS -->

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Status
                                                </label>

                                                <select name="status"
                                                    class="form-select">

                                                    <option value="1"
                                                        <?php echo (($edit['status'] ?? 1) == 1) ? 'selected' : ''; ?>>
                                                        Active
                                                    </option>

                                                    <option value="0"
                                                        <?php echo (($edit['status'] ?? 1) == 0) ? 'selected' : ''; ?>>
                                                        Inactive
                                                    </option>

                                                </select>

                                            </div>

                                            <!-- BUTTON -->

                                            <button type="submit"
                                                name="save"
                                                class="btn btn-primary w-100">

                                                <i class="bi bi-save"></i>
                                                Save Location

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                            <!-- =====================================================
                 TABLE SECTION
            ====================================================== -->

                            <div class="col-lg-8">

                                <div class="card border-0 shadow-sm rounded-4">

                                    <div class="card-body">

                                        <h5 class="mb-3">
                                            Location List
                                        </h5>

                                        <div class="table-responsive">

                                            <table class="table table-bordered table-striped datatable">

                                                <thead>

                                                    <tr>
                                                        <th>Venue</th>
                                                        <th>Radius</th>
                                                        <th>QR</th>
                                                        <th>Action</th>

                                                    </tr>

                                                </thead>

                                                <tbody>

                                                    <?php foreach ($rows as $r) { ?>

                                                        <tr>

                                                            <td>
                                                                <?php echo $r['location_name']; ?>
                                                            </td>

                                                            <td>
                                                                <?php echo $r['radius_meter']; ?>m
                                                            </td>

                                                            <td>

                                                                <a href="qr-display.php?location_id=<?php echo $r['location_id']; ?>"
                                                                    class="btn btn-dark btn-sm">

                                                                    QR

                                                                </a>

                                                            </td>

                                                            <td>

                                                                <a href="?edit=<?php echo $r['location_id']; ?>"
                                                                    class="btn btn-info btn-sm">

                                                                    Edit

                                                                </a>

                                                                <a href="?del=<?php echo $r['location_id']; ?>"
                                                                    onclick="return confirm('Delete?')"
                                                                    class="btn btn-danger btn-sm">

                                                                    Delete

                                                                </a>

                                                            </td>

                                                        </tr>

                                                    <?php } ?>

                                                </tbody>

                                            </table>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Close-->
    </div>

</body>

<!-- Script tags -->
<?php include('component/script.php'); ?>

<script>
    function syncQrTime() {
        let meetingStart =
            document.getElementById('meeting_start').value;

        let meetingEnd =
            document.getElementById('meeting_end').value;

        /* AUTO SET QR START */

        document.getElementById('qr_valid_from').value =
            meetingStart;

        /* AUTO SET QR END */

        document.getElementById('qr_valid_to').value =
            meetingEnd;
    }

    /* PAGE LOAD AUTO */

    window.onload = function() {
        syncQrTime();
    };
</script>
<script>
    const GOOGLE_API_KEY = "AIzaSyD60TsOPfBQDMpiGwEWusBT-UBUUM6Y8O8";

    let gpsLoaderInterval = null;

    function startGpsLoader() {
        let dots = 0;

        gpsLoaderInterval = setInterval(function() {
            dots++;

            if (dots > 3) {
                dots = 1;
            }

            let dotText = '.'.repeat(dots);

            document.getElementById('gps_status').innerHTML = `

            <div class="alert alert-warning text-center">

                <div class="spinner-border text-primary mb-2"></div>

                <h6 class="mb-1">
                    Finding Accurate GPS ${dotText}
                </h6>

                <small>
                    Please wait...<br>
                    GPS + WiFi + Mobile Data ON rakho
                </small>

            </div>

        `;

        }, 500);
    }

    function stopGpsLoader() {
        clearInterval(gpsLoaderInterval);
    }

    function getVenueLocation() {
        if (!navigator.geolocation) {
            alert('Location not supported');
            return;
        }

        startGpsLoader();

        let bestPosition = null;
        let bestAccuracy = 999999;
        let watchId = null;

        watchId = navigator.geolocation.watchPosition(

            async function(position) {
                    let accuracy = position.coords.accuracy;

                    console.log(
                        'Accuracy:',
                        accuracy
                    );

                    /* BETTER GPS FOUND */

                    if (accuracy < bestAccuracy) {
                        bestAccuracy = accuracy;
                        bestPosition = position;

                        document.getElementById('gps_status').innerHTML = `

                    <div class="alert alert-info">

                        <b>Better GPS Found</b>

                        <hr>

                        Latitude:
                        ${position.coords.latitude.toFixed(8)}

                        <br>

                        Longitude:
                        ${position.coords.longitude.toFixed(8)}

                        <br>

                        Accuracy:
                        ${accuracy.toFixed(2)} Meter

                    </div>

                `;
                    }

                    /* TARGET ACCURACY */

                    if (bestAccuracy <= 15) {
                        navigator.geolocation.clearWatch(watchId);

                        stopGpsLoader();

                        useBestLocation(bestPosition);
                    }
                },

                function(error) {
                    stopGpsLoader();

                    let msg = 'GPS Error';

                    if (error.code == 1) {
                        msg = 'Please allow location permission';
                    } else if (error.code == 2) {
                        msg = 'Location unavailable';
                    } else if (error.code == 3) {
                        msg = 'GPS timeout';
                    }

                    document.getElementById('gps_status').innerHTML = `

                <div class="alert alert-danger">
                    ${msg}
                </div>

            `;
                },

                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 60000
                }
        );

        /* FORCE STOP AFTER 30 SEC */

        setTimeout(function() {
            navigator.geolocation.clearWatch(watchId);

            stopGpsLoader();

            if (bestPosition) {
                useBestLocation(bestPosition);
            } else {
                document.getElementById('gps_status').innerHTML = `

                <div class="alert alert-danger">

                    GPS location not found.<br>
                    Please move outdoor and try again.

                </div>

            `;
            }

        }, 30000);
    }

    async function useBestLocation(position) {
        let lat = position.coords.latitude;
        let lng = position.coords.longitude;
        let accuracy = position.coords.accuracy;

        document.querySelector('[name=latitude]').value =
            lat.toFixed(8);

        document.querySelector('[name=longitude]').value =
            lng.toFixed(8);

        let address = await getGoogleAddress(lat, lng);

        if (document.querySelector('[name=location_address]')) {
            document.querySelector('[name=location_address]').value =
                address;
        }

        let accuracyClass =
            accuracy <= 15 ?
            'success' :
            'warning';

        document.getElementById('gps_status').innerHTML = `

        <div class="alert alert-${accuracyClass}">

            <h6>
                GPS Location Found
            </h6>

            <hr>

            <b>Latitude:</b>
            ${lat.toFixed(8)}

            <br>

            <b>Longitude:</b>
            ${lng.toFixed(8)}

            <br>

            <b>Accuracy:</b>
            ${accuracy.toFixed(2)} Meter

            <hr>

            <b>Detected Address:</b>

            <br>

            ${address}

        </div>

    `;
    }

    async function getGoogleAddress(lat, lng) {
        try {
            let url =
                "https://maps.googleapis.com/maps/api/geocode/json?latlng=" +
                lat + "," + lng +
                "&key=" + GOOGLE_API_KEY;

            let response = await fetch(url);

            let data = await response.json();

            if (
                data.status === "OK" &&
                data.results.length > 0
            ) {
                return data.results[0].formatted_address;
            }
        } catch (e) {
            console.log(e);
        }

        return "Address not found";
    }
</script>

<script>
    $(document).ready(function() {
        $(".chosen-select").chosen();
        $("#example").DataTable();
    });

    function funDel(id, imgname) {
        if (confirm("Are you sure you want to delete this record?")) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master.php',
                data: {
                    id: id,
                    tblname: '<?php echo $tblname; ?>',
                    tblpkey: '<?php echo $tblpkey; ?>',
                },
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            });
        }
    }

    function numberOnly(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\.|\s/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
    }
</script>

</html>