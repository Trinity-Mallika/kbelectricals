<?php

include("../adminsession.php");
$title = "Route Master";
$pagename = "route.php";
$module = "Route Master";
$submodule = "Route Master";
$btn_name = "Save";
$tblname = "route";
$tblpkey = "route_id";

if (isset($_GET['batch_no']))
    $keyvalue = $_GET['batch_no'];
else
    $keyvalue = 0;
if (isset($_GET['action'])) {
    $action = $obj->test_input($_GET['action']);
} else {
    $action = "";
}

if (isset($_POST['submit'])) {

    $route_name = $obj->test_input($_POST['route_name']);
    $week_days = $_POST['week_days'] ?? [];
    $batch_no = $_POST['batch_no'];

    if (empty($week_days)) {
        echo "<script>alert('Please select at least one day');</script>";
    } else {

        if ($keyvalue == 0) {
            if (empty($batch_no)) {
                $batch_no = $obj->getcode($tblname, "batch_no", "1=1");
            }

            foreach ($week_days as $day) {

                $count = $obj->getvalfield(
                    $tblname,
                    "count(*)",
                    "route_name='$route_name' AND day_of_week='$day'"
                );

                if ($count == 0) {

                    $obj->insert_record($tblname, [
                        'route_name' => $route_name,
                        'day_of_week' => $day,
                        'batch_no' => $batch_no,
                        'ipaddress' => $ipaddress,
                        'createdate' => $createdate,
                        "companyid" => $companyid,
                        'createdby' => $loginid
                    ]);
                }
            }
        } else {

            $old = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
            $batch_no = $old['batch_no'];
            $existing_days = [];
            $res = $obj->executequery("SELECT route_id, day_of_week FROM $tblname WHERE batch_no='$batch_no'");
            foreach ($res as $row) {
                $existing_days[$row['day_of_week']] = $row['route_id'];
            }

            foreach ($week_days as $day) {
                if (!isset($existing_days[$day])) {
                    $obj->insert_record($tblname, [
                        'route_name' => $route_name,
                        'day_of_week' => $day,
                        'batch_no' => $batch_no,
                        'ipaddress' => $ipaddress,
                        'createdate' => $createdate,
                        "companyid" => $companyid,
                        'createdby' => $loginid
                    ]);
                } else {
                    $obj->update_record(
                        $tblname,
                        ['route_id' => $existing_days[$day]],
                        ['route_name' => $route_name]
                    );
                }
            }

            foreach ($existing_days as $day => $route_id) {

                if (!in_array($day, $week_days)) {
                    $used = $obj->getvalfield(
                        "route_plan",
                        "count(*)",
                        "route_id='$route_id'"
                    );

                    if ($used == 0) {
                        $obj->delete_record($tblname, ["route_id" => $route_id]);
                    }
                }
            }
        }

        echo "<script>location='$pagename?action=1'</script>";
    }
}
if (isset($_GET['batch_no'])) {

    $btn_name = "Update";
    $where = array('batch_no' => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $route_name = $sqledit['route_name'];
    $days_arr = [];
    $res = $obj->executequery("SELECT day_of_week FROM route WHERE route_name='$route_name'");
    foreach ($res as $r) {
        $days_arr[] = $r['day_of_week'];
    }
} else {
    $route_name = "";
    $days_arr = [];
    $batch_no = $obj->getcode($tblname, "batch_no", "1=1");
}
?>

<!DOCTYPE html>

<html lang="en">



<head>
    <!-- meta tag -->

    <?php include('component/css.php'); ?>

    <!-- meta tag -->
    <style>
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

        <!-- heading -->

        <?php include('component/header.php'); ?>

        <!-- heading Close-->

        <!-- Content -->

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">

                    <fieldset class="mt-2">

                        <legend><?php echo $title; ?></legend>

                        <?php include('component/alert.php'); ?>

                        <div class="card">

                            <div class="card-header text-white">

                                <?php echo $title; ?>

                            </div>

                            <div class="card-body">

                                <form action="" method="post">

                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <strong> <label for="images">Route Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="route_name" id="route_name" value="<?php echo $route_name ?>" placeholder="Enter Route Name">
                                        </div>
                                        <div class="col-md-8 mb-2">
                                            <strong>
                                                <label>Week Days <span class="text-danger">*</span></label>
                                            </strong><br>
                                            <?php
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                            foreach ($days as $d) {
                                            ?>
                                                <label class="me-2">
                                                    <input type="checkbox" name="week_days[]" value="<?php echo $d; ?>"
                                                        <?php if (in_array($d, $days_arr)) echo 'checked'; ?>>
                                                    <?php echo $d; ?>
                                                </label>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-2 mb-2">

                                            <br />

                                            <input type="submit" onclick="return checkinputmaster('route_name')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">

                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                            <input type="hidden" name="batch_no" id="batch_no" value="<?php echo $batch_no; ?>">

                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>

                                        </div>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </fieldset>

                </div>

            </div>

            <div class="row mt-4 mb-4">

                <div class="col-lg-12">

                    <div class="card">

                        <div class="card-header text-white">

                            <?php echo $submodule; ?> RECORD

                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table id="example" class="table table-bordered table-sm table-hover">

                                    <thead>
                                        <th>Sr. No.</th>
                                        <th>Route Name</th>
                                        <th>Day</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT 
    batch_no,
    route_name,
    GROUP_CONCAT(day_of_week ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')) AS days
FROM $tblname where companyid='$companyid'
GROUP BY batch_no, route_name
ORDER BY batch_no DESC
");
                                        foreach ($sql as $key) {
                                        ?>

                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $key['route_name']; ?></td>
                                                <td><?php echo $key['days']; ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?batch_no=" . $key['batch_no']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo  $key['batch_no']; ?>');"><i class="bi bi-trash3-fill"></i></button>
                                                </td>
                                            </tr>
                                        <?php }
                                        ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<!-- script tag -->
<script>
    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = 'batch_no';

        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({

                type: 'POST',
                url: 'ajax/delete_master.php',
                data: 'id=' + id +
                    '&tblname=' + tblname +
                    '&tblpkey=' + tblpkey,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close

    $(document).ready(function() {
        $(".chosen-select").chosen();
        $('#example').DataTable();
    });
</script>

</html>