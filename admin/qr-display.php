<?php
include("../adminsession.php");
$title = "QR Display";
$pagename = "qr-display.php";
$module = "QR Display";
$submodule = "QR Display List";
$btn_name = "Save";
// $tblname = "setting";
// $tblpkey = "setting_id";
// $keyvalue = (isset($_GET["setting_id"])) ? $obj->test_input($_GET["setting_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

/* =========================
   GET MEETING
========================= */

$location_id = intval($_GET['location_id'] ?? 0);

if (!$location_id) {
    $latest = $obj->executequery("
        SELECT *
        FROM store_location
        ORDER BY location_id DESC
        LIMIT 1
    ");

    if ($latest) {
        $location_id = $latest[0]['location_id'];
    }
}

$meeting = [];

if ($location_id) {
    $result = $obj->executequery("
        SELECT *
        FROM store_location
        WHERE location_id = '$location_id'
    ");

    if ($result) {
        $meeting = $result[0];
    }
}

/* =========================
   MEETING LIST
========================= */

$meetings = $obj->executequery("
    SELECT *
    FROM store_location
    ORDER BY location_id DESC
");

/* =========================
   QR URL
========================= */

$protocol = (
    isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] == 'on'
) ? 'https' : 'http';

$base_url =
    $protocol .
    '://' .
    $_SERVER['HTTP_HOST'] .
    dirname(dirname($_SERVER['PHP_SELF']));

$scan_url = '';

if ($meeting) {
    $scan_url =
        $base_url .
        '/member/scan.php?token=' .
        $meeting['qr_token'];
}

$qr_api =
    'https://api.qrserver.com/v1/create-qr-code/?size=360x360&margin=18&data=' .
    urlencode($scan_url);

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


        @media print {
            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
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

                            <!-- Select Location Name -->

                            <div class="col-lg-4">

                                <div class="card border-0 shadow-sm rounded-4">

                                    <div class="card-body">

                                        <h5 class="mb-3">
                                            Select Location Name
                                        </h5>

                                        <form method="GET">

                                            <select name="location_id"
                                                class="form-select mb-3"
                                                onchange="this.form.submit()">

                                                <option value="">
                                                    Select Location Name for QR
                                                </option>

                                                <?php foreach ($meetings as $m) { ?>

                                                    <option value="<?php echo $m['location_id']; ?>"
                                                        <?php echo ($location_id == $m['location_id']) ? 'selected' : ''; ?>>

                                                        <?php
                                                        echo $m['location_name'];
                                                        ?>

                                                    </option>

                                                <?php } ?>

                                            </select>

                                        </form>

                                        <?php if ($meeting) { ?>

                                            <p>
                                                <b>Venue:</b>
                                                <?php echo $meeting['location_name']; ?>
                                            </p>

                                            <p>
                                                <b>Radius:</b>
                                                <?php echo $meeting['radius_meter']; ?> meter
                                            </p>

                                        <?php } ?>

                                    </div>

                                </div>

                            </div>

                            <!-- QR DISPLAY -->

                            <div class="col-lg-8">

                                <?php if ($meeting) { ?>

                                    <div class="card border-0 shadow-lg ">

                                        <div class="card-body text-center p-4 print-area">

                                            <p class="text-info mb-3">

                                                <i class="bi bi-geo-alt"></i>

                                                <?php echo $meeting['location_name']; ?>

                                            </p>

                                            <div class="qr-box shadow">

                                                <img src="<?php echo $qr_api; ?>"
                                                    alt="Location QR"
                                                    class="img-fluid"
                                                    id="qrImage">

                                            </div>

                                            <div class="mt-4 no-print">

                                                <label class="text-start d-block mb-1">
                                                    QR Attendance URL
                                                </label>

                                                <div class="input-group">

                                                    <input type="text"
                                                        id="scanUrl"
                                                        class="form-control copy-url"
                                                        value="<?php echo $scan_url; ?>"
                                                        readonly
                                                        placeholder="QR attendance URL">

                                                    <button type="button"
                                                        class="btn btn-info"
                                                        onclick="copyUrl()">

                                                        <i class="bi bi-clipboard"></i>
                                                        Copy URL

                                                    </button>

                                                </div>

                                            </div>

                                            <div class="d-flex gap-2 justify-content-center flex-wrap mt-4 no-print">

                                                <!-- <a href="<?php //echo $scan_url; 
                                                                ?>"
                                                    class="btn btn-light rounded-pill"
                                                    target="_blank">

                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                    Open Scan URL

                                                </a> -->

                                                <a href="<?php echo $qr_api; ?>"
                                                    class="btn btn-success rounded-pill"
                                                    download="bni-meeting-qr.png">

                                                    <i class="bi bi-download"></i>
                                                    Download QR

                                                </a>

                                                <button type="button"
                                                    class="btn btn-light rounded-pill"
                                                    onclick="window.print()">

                                                    <i class="bi bi-printer"></i>
                                                    Print QR

                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                <?php } else { ?>

                                    <div class="alert alert-warning">
                                        Please create meeting first.
                                    </div>

                                <?php } ?>

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