<?php

include("../adminsession.php");

$title = "Add Behavioural Points";
$pagename = "add_behavioural_points.php";
$module = "Add Behavioural Points";
$submodule = "Add Behavioural Points";
$btn_name = "Save";
$tblname = "add_behavioural_points";
$tblpkey = "add_point_id";
$keyvalue = (isset($_GET["add_point_id"])) ? $obj->test_input($_GET["add_point_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : 0;

if (isset($_POST['submit'])) {
    $year = $obj->test_input($_POST['year']);
    $month_name = $obj->test_input($_POST['month_name']);
    $sales_executive_id = $obj->test_input($_POST['sales_executive_id']);
    $kra_behaviour_id = $obj->test_input($_POST['kra_behaviour_id']);
    $point = $obj->test_input($_POST['point']);

    //check Duplicate
    $count = $obj->getvalfield(
        $tblname,
        "count(*)",
        "year='$year' 
        AND month_name='$month_name' 
        AND sales_executive_id='$sales_executive_id' 
        AND kra_behaviour_id='$kra_behaviour_id' 
        AND $tblpkey != '$keyvalue'"
    );

    if ($count > 0) {

        $action = 4;

        $process = "Duplicate";

        //echo $dup; die;

    } else //insert

    {

        if ($keyvalue == 0) {
            $form_data = array('year' => $year, 'month_name' => $month_name, 'sales_executive_id' => $sales_executive_id, 'kra_behaviour_id' => $kra_behaviour_id, 'point' => $point,  'ipaddress' => $ipaddress, 'createdate' => $createdate, 'createdby' => $loginid, "companyid" => $companyid);
            $obj->insert_record($tblname, $form_data);
            $action = 1;
            $process = "insert";
        } else {
            //update
            $form_data = array('year' => $year, 'month_name' => $month_name, 'sales_executive_id' => $sales_executive_id, 'kra_behaviour_id' => $kra_behaviour_id, 'point' => $point,  'ipaddress' => $ipaddress, 'lastupdated' => $createdate, "companyid" => $companyid);
            $where = array($tblpkey => $keyvalue);
            $obj->update_record($tblname, $where, $form_data);
            $action = 2;
            $process = "updated";
        }
    }
    echo "<script>location='$pagename?action=$action'</script>";
}

if (isset($_GET[$tblpkey])) {

    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $year = $sqledit['year'];
    $month_name = $sqledit['month_name'];
    $sales_executive_id = $sqledit['sales_executive_id'];
    $kra_behaviour_id = $sqledit['kra_behaviour_id'];
    $point = $sqledit['point'];
} else {
    $year = "";
    $month_name = "";
    $sales_executive_id = "";
    $kra_behaviour_id = "";
    $point = "";
}

?>

<!DOCTYPE html>

<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
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
                                            <strong><label>Year <span class="text-danger">*</span></label></strong>

                                            <select class="form-control form-control-sm chosen-select" name="year" id="year">
                                                <option value="">--Select Year--</option>
                                                <?php
                                                $current_year = date('Y');
                                                for ($y = $current_year - 5; $y <= $current_year + 5; $y++) {
                                                ?>
                                                    <option value="<?= $y; ?>"
                                                        <?= ($y == $year) ? 'selected' : ''; ?>>
                                                        <?= $y; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Month <span class="text-danger">*</span></label></strong>

                                            <select class="form-control form-control-sm chosen-select" name="month_name" id="month_name">
                                                <option value="">--Select Month--</option>
                                                <option value="01" <?= ($month_name == "01") ? 'selected' : ''; ?>>January</option>
                                                <option value="02" <?= ($month_name == "02") ? 'selected' : ''; ?>>February</option>
                                                <option value="03" <?= ($month_name == "03") ? 'selected' : ''; ?>>March</option>
                                                <option value="04" <?= ($month_name == "04") ? 'selected' : ''; ?>>April</option>
                                                <option value="05" <?= ($month_name == "05") ? 'selected' : ''; ?>>May</option>
                                                <option value="06" <?= ($month_name == "06") ? 'selected' : ''; ?>>June</option>
                                                <option value="07" <?= ($month_name == "07") ? 'selected' : ''; ?>>July</option>
                                                <option value="08" <?= ($month_name == "08") ? 'selected' : ''; ?>>August</option>
                                                <option value="09" <?= ($month_name == "09") ? 'selected' : ''; ?>>September</option>
                                                <option value="10" <?= ($month_name == "10") ? 'selected' : ''; ?>>October</option>
                                                <option value="11" <?= ($month_name == "11") ? 'selected' : ''; ?>>November</option>
                                                <option value="12" <?= ($month_name == "12") ? 'selected' : ''; ?>>December</option>
                                            </select>
                                        </div>
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
                                            <strong><label>Behavioural Aspects <span class="text-danger">*</span></label></strong>

                                            <select class="form-control form-control-sm chosen-select" name="kra_behaviour_id" id="kra_behaviour_id">
                                                <option value="">--Select Behaviour--</option>

                                                <?php
                                                $behaviour = $obj->executequery("SELECT * FROM kra_behavioural_aspect");
                                                foreach ($behaviour as $b) {
                                                ?>
                                                    <option value="<?= $b['kra_behaviour_id']; ?>"
                                                        <?= ($b['kra_behaviour_id'] == $kra_behaviour_id) ? 'selected' : '' ?>>
                                                        <?= $b['kra_behaviour_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label>Points <span class="text-danger">*</span></label></strong>
                                            <input type="number"
                                                name="point"
                                                id="point"
                                                class="form-control form-control-sm"
                                                placeholder="Enter Points"
                                                min="0"
                                                max="4"
                                                value="<?= $point ?>"
                                                oninput="
                                                if(this.value > 4) this.value = 4;
                                                if(this.value < 1 && this.value != '') this.value = 1;">

                                        </div>
                                        <div class="col-md-2 mt-4">
                                            <input type="submit" onclick="return checkinputmaster('year,month_name,sales_executive_id,kra_behaviour_id,point')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
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
                                        <th>Year</th>
                                        <th>Month</th>
                                        <th>Sale Executive</th>
                                        <th>Behavioural Aspects</th>
                                        <th>Points</th>
                                        <th>Action</th>

                                    </thead>

                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $months = [
                                            "01" => "January",
                                            "02" => "February",
                                            "03" => "March",
                                            "04" => "April",
                                            "05" => "May",
                                            "06" => "June",
                                            "07" => "July",
                                            "08" => "August",
                                            "09" => "September",
                                            "10" => "October",
                                            "11" => "November",
                                            "12" => "December"
                                        ];

                                        // $sql = $obj->executequery("select * from add_behavioural_points order by add_point_id DESC ");
                                        $sql = $obj->executequery("
                                            SELECT 
                                                abp.*,
                                                kba.kra_behaviour_name,
                                                u.fullname

                                            FROM add_behavioural_points abp

                                            LEFT JOIN kra_behavioural_aspect kba
                                                ON kba.kra_behaviour_id = abp.kra_behaviour_id

                                            LEFT JOIN user u
                                                ON u.userid = abp.sales_executive_id

                                            WHERE u.usertype = 'sales'
                                            AND u.companyid = '$companyid'

                                            ORDER BY abp.add_point_id DESC
                                        ");
                                        foreach ($sql as $key) {
                                            // $behavioural = $obj->getvalfield("kra_behavioural_aspect", "kra_behaviour_name", "kra_behaviour_id='{$key['kra_behaviour_id']}'");
                                            // $sales_executive = $obj->getvalfield("user", "username", "usertype='sales' and companyid='$companyid' and userid='{$key['sales_executive_id']}'");
                                            $month_name = isset($months[$key['month_name']])
                                                ? $months[$key['month_name']]
                                                : $key['month_name'];
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo $key['year']; ?></td>
                                                <td><?php echo $month_name ?></td>
                                                <td><?php echo $key['fullname']; ?></td>
                                                <td><?php echo $key['kra_behaviour_name']; ?></td>
                                                <td><?php echo $key['point']; ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['add_point_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo  $key['add_point_id']; ?>');"><i class="bi bi-trash3-fill"></i></button>
                                                </td>
                                            </tr>
                                        <?php }  ?>
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