<?php include("../adminsession.php");

$title = "Route Plan";
$pagename = "assign_route.php";
$module = "Route Plan";
$submodule = "Route Plan";
$btn_name = "Save";
$tblname = "route_plan";
$tblpkey = "route_planid";
$keyvalue = (isset($_GET["route_planid"])) ? $obj->test_input($_GET["route_planid"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

if (isset($_POST['submit'])) {
    $sales_executive_id = $obj->test_input($_POST['sales_executive_id']);
    $batch_no = $obj->test_input($_POST['batch_no']);
    $week_number = $obj->test_input($_POST['week_number']);

    if ($keyvalue == 0) {
        $form_data = array(
            'sales_executive_id' => $sales_executive_id,
            'batch_no' => $batch_no,
            'week_number' => $week_number,
            'createdby' => $loginid,
            'createdate' => $createdate,
            'companyid' => $companyid,
            'ipaddress' => $ipaddress
        );
        $obj->insert_record($tblname, $form_data);
        $action = 1;
    } else {

        $form_data = array(
            'sales_executive_id' => $sales_executive_id,
            'batch_no' => $batch_no,
            'week_number' => $week_number,
            'createdby' => $loginid,
            'lastupdated' => $createdate,
            'companyid' => $companyid,
            'ipaddress' => $ipaddress
        );
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $action = 2;
    }

    echo "<script>location='$pagename?action=$action'</script>";
}

if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $row = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
    $sales_executive_id = $row['sales_executive_id'];
    $batch_no = $row['batch_no'];
    $week_number = $row['week_number'];
} else {
    $sales_executive_id = $batch_no = "";
    $week_number = "1";
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
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="images">Sales Executive <span
                                                        class="text-danger fw-bold">*</span></label></strong>
                                            <select class="form-control form-control-sm chosen-select"
                                                name="sales_executive_id" id="sales_executive_id">
                                                <option value="">--Select Sales Executive--</option>
                                                <?php
                                                $sql = $obj->executequery("select * from user where usertype='sales' and companyid='$companyid' order by username ASC ");
                                                foreach ($sql as $key) {
                                                    ?>
                                                    <option value="<?php echo $key['userid'] ?>">
                                                        <?php echo $key['fullname'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('sales_executive_id').value = '<?= $sales_executive_id ?>'
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="batch_no">Route Name <span
                                                        class="text-danger fw-bold">*</span></label></strong>
                                            <select class="form-control form-control-sm chosen-select" name="batch_no"
                                                id="batch_no">
                                                <option value="">--Select Route--</option>
                                                <?php
                                                $categories = $obj->executequery("SELECT
    batch_no,
    route_name,
    GROUP_CONCAT(
        day_of_week
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
FROM route
WHERE companyid='$companyid'
GROUP BY batch_no, route_name
ORDER BY route_name ASC
");
                                                foreach ($categories as $c) { ?>
                                                    <option value="<?= $c['batch_no']; ?>">
                                                        <?= $c['route_name']; ?> [<?= $c['days']; ?>]
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('batch_no').value = '<?= $batch_no ?>'
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="images">Week <span
                                                        class="text-danger fw-bold"></span></label></strong>
                                            <select class="form-control form-control-sm chosen-select"
                                                name="week_number" id="week_number">
                                                <?php for ($i = 1; $i < 6; $i++) {
                                                    echo "<option value='{$i}'>{$i}</option>";
                                                } ?>
                                            </select>
                                            <script>
                                                document.getElementById('week_number').value = '<?= $week_number ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit"
                                                onclick="return checkinputmaster('sales_executive_id,batch_no')"
                                                name="submit" class="btn btn-theme btn-sm"
                                                value="<?php echo $btn_name; ?>">
                                            <input type="hidden" name="<?php echo $tblpkey; ?>"
                                                id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
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
                                        <th class="text-center">Sr. No.</th>
                                        <th>Sales Executive</th>
                                        <th>Route Name</th>
                                        <th>Day Name</th>
                                        <th>Week</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT
    rp.*,
    u.fullname,
    r.route_name,
    GROUP_CONCAT(
        DISTINCT r.day_of_week
        ORDER BY FIELD(
            r.day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        )
        SEPARATOR ', '
    ) AS days
FROM route_plan rp
LEFT JOIN user u
    ON u.userid = rp.sales_executive_id
LEFT JOIN route r
    ON r.batch_no = rp.batch_no
WHERE rp.companyid='$companyid'
GROUP BY rp.route_planid
ORDER BY rp.route_planid DESC
");
                                        foreach ($sql as $key) { ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo $key['fullname']; ?></td>
                                                <td><?= $key['route_name']; ?> </td>
                                                <td><?= $key['days']; ?></td>
                                                <td><?= $key['week_number']; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?route_planid=" . $key[$tblpkey]; ?>"
                                                        class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="funDel('<?php echo $key[$tblpkey]; ?>');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
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
        <!-- Content close-->
    </div>
</body>
<!-- script tag -->
<?php include('component/script.php'); ?>
<!-- script tag -->
<script>
    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey,
                dataType: 'html',
                success: function (data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';

                }
            }); //ajax close
        } //confirm close
    } //fun close

    $(document).ready(function () {
        $(".chosen-select").chosen();
        $('#example').DataTable();
    });
</script>

</html>