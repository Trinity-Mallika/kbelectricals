<?php
include("../adminsession.php");
$title = "Attendance In / Out Setting";
$pagename = "setting.php";
$module = "Attendance In / Out Setting";
$submodule = "In / Out Setting List";
$btn_name = "Update";
$tblname = "setting";
$tblpkey = "setting_id";
$keyvalue = (isset($_GET["setting_id"])) ? $obj->test_input($_GET["setting_id"]) : 1;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";


if (isset($_POST["submit"])) {

    $form_data = array(
        "in_time" => $obj->test_input($_POST["in_time"]),
        "in_margin" => $obj->test_input($_POST["in_margin"]),
        "out_time" => $obj->test_input($_POST["out_time"]),
        "out_margin" => $obj->test_input($_POST["out_margin"]),
        "late_grace_minutes" => $obj->test_input($_POST["late_grace_minutes"]),
        "late_allowed_days" => $obj->test_input($_POST["late_allowed_days"]),
        "salary_deduction_days" => $obj->test_input($_POST["salary_deduction_days"]),
        "createdby" => $loginid,
        "companyid" => $companyid,
        "ipaddress" => $ipaddress,
        "lastupdated" => $createdate
    );

    $where = array($tblpkey => $keyvalue);
    $obj->update_record($tblname, $where, $form_data);

    header("Location: setting.php?action=2");
    exit;
}

$row = $obj->select_record($tblname, array($tblpkey => $keyvalue));
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
                        <form action="" method="post">
                            <div class="card">
                                <div class="card-header text-white">
                                    <?php echo $module ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-3 mb-3">
                                            <label><b>Office In Time</b></label>
                                            <input type="time" class="form-control form-control-sm" name="in_time" value="<?= @$row['in_time']; ?>">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label><b>In Margin (Minutes)</b></label>
                                            <input type="number" min="0" max="120" class="form-control form-control-sm" name="in_margin" value="<?= @$row['in_margin']; ?>">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label><b>Office Out Time</b></label>
                                            <input type="time" class="form-control form-control-sm" name="out_time" value="<?= @$row['out_time']; ?>">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label><b>Out Margin (Minutes)</b></label>
                                            <input type="number" min="0" max="120" class="form-control form-control-sm" name="out_margin" value="<?= @$row['out_margin']; ?>">
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label><b>Grace Time Before Late (Minutes)</b></label>
                                            <input type="number" min="0" class="form-control form-control-sm" name="late_grace_minutes" value="<?= @$row['late_grace_minutes']; ?>">
                                            <small class="text-muted">Example: 15</small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label><b>Late Days Allowed</b></label>
                                            <input type="number" min="1" class="form-control form-control-sm" name="late_allowed_days" value="<?= @$row['late_allowed_days']; ?>">
                                            <small class="text-muted">Example: 3 late days</small>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label><b>Salary Deduction (Days)</b></label>
                                            <input type="number" step="0.5" min="0.5" class="form-control form-control-sm" name="salary_deduction_days" value="<?= @$row['salary_deduction_days']; ?>">
                                            <small class="text-muted">Example: 1 Day</small>
                                        </div>

                                        <div class="col-md mt-2 mt-2">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('in_time,in_margin,out_time,out_margin')">
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
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