<?php
include("../adminsession.php");
$title = "Setting";
$pagename = "setting.php";
$module = "Setting";
$submodule = "Setting List";
$btn_name = "Save";
$tblname = "setting";
$tblpkey = "setting_id";
$keyvalue = (isset($_GET["setting_id"])) ? $obj->test_input($_GET["setting_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

if (isset($_POST['submit'])) {
    $in_time = $obj->test_input($_POST['in_time']);
    $in_margin = $obj->test_input($_POST['in_margin']);
    $out_time = $obj->test_input($_POST['out_time']);
    $out_margin = $obj->test_input($_POST['out_margin']);

    // $dup = $obj->getvalfield("$tblname", "count(*)", "in_time= '$in_time' AND  $tblpkey != '$keyvalue'");

    // if ($dup > 0) {
    // $action = 4;
    // echo "<script>location='$pagename?action=$action'</script>";
    // } else {

    $form_data = array(
        "in_time" => $in_time,
        "in_margin" => $in_margin,
        "out_time" => $out_time,
        "out_margin" => $out_margin,
        "createdby" => $loginid,
        'createdate' => $createdate,
        "ipaddress" => $ipaddress,
        "companyid" => $companyid
    );

    if ($keyvalue == 0) {
        $form_data["createdate"] = $createdate;
        $obj->insert_record($tblname, $form_data);
        $action = 1;
        $process = "Insert";
        echo "<script>location='$pagename?action=$action'</script>";
    } else {
        $form_data["lastupdated"] = $createdate;
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $action = 2;
        $process = "Update";
    }
    // }
    echo "<script>location='$pagename?action=$action'</script>";
}

if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $in_time = $sqledit['in_time'];
    $in_margin = $sqledit['in_margin'];
    $out_time = $sqledit['out_time'];
    $out_margin = $sqledit['out_margin'];
} else {
    $in_time = "";
    $in_margin = "";
    $out_time = "";
    $out_margin = "";
}
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
                                        <div class="col-md-4 mb-2">
                                            <strong><label for="in_time" class="form-label"> IN Time <span class="text-danger">*</span></label></strong>
                                            <input type="time" class="form-control form-control-sm" id="in_time" name="in_time" value="<?php echo $in_time ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong><label for="in_margin" class="form-label"> In Margin(minutes) <span class="text-danger">*</span></label></strong>
                                            <input type="text" onkeypress='numberOnly(event)' maxlength="2" class="form-control form-control-sm" placeholder="Enter In Margin" id="in_margin" name="in_margin" value="<?php echo $in_margin ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong><label for="out_time" class="form-label"> Out Time <span class="text-danger">*</span></label></strong>
                                            <input type="time" class="form-control form-control-sm" id="out_time" name="out_time" value="<?php echo $out_time ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong><label for="out_margin" class="form-label"> Out Margin(minutes) <span class="text-danger">*</span></label></strong>
                                            <input type="text" onkeypress='numberOnly(event)' maxlength="2" class="form-control form-control-sm" placeholder="Enter Out Margin" id="out_margin" name="out_margin" value="<?php echo $out_margin ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md mt-2 "><br />
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('in_time,in_margin,out_time,out_margin')">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>

            <div class="row mt-4 mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead class="text-center">
                                        <th class="text-center">S. No.</th>
                                        <th>In Time</th>
                                        <th>In Margin (minutes)</th>
                                        <th>Out Time</th>
                                        <th>Out Margin (minutes)</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT * FROM $tblname ORDER BY setting_id desc");
                                        foreach ($sql as $key) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo $key['in_time']; ?> </td>
                                                <td><?php echo $key['in_margin']; ?> </td>
                                                <td><?php echo $key['out_time']; ?> </td>
                                                <td><?php echo $key['out_margin']; ?> </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['setting_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo $key['setting_id']; ?>');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
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