<?php include("../adminsession.php");
$title = "Create Counters / Customer";
$pagename = "accounts.php";
$module = "Add Counters / Customer";
$submodule = "Counter/Customer Master";
$btn_name = "Save";
$tblname = "account";
$tblpkey = "account_id";
$keyvalue = (isset($_GET["account_id"])) ? $obj->test_input($_GET["account_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";



if (isset($_POST['submit'])) {
    $account_name = $obj->test_input($_POST['account_name']);
    $owner_name = $obj->test_input($_POST['owner_name']);
    $o_mobile_no = $obj->test_input($_POST['o_mobile_no']);
    $mobile_no = $obj->test_input($_POST['mobile_no']);
    $address = $obj->test_input($_POST['address']);
    $opening_balance = $obj->test_input($_POST['opening_balance']);
    $opening_date = $obj->test_input($_POST['opening_date']);
    $area_id = $obj->test_input($_POST['area_id']);
    $common_id = $obj->test_input($_POST['common_id']);
    $type = ($common_id == -1) ? "employee" : "customer";
    $no_of_kid  =   $obj->test_input($_POST['no_of_kid']);
    $no_of_family  =   $obj->test_input($_POST['no_of_family']);
    $doa  =  $_POST['doa'];
    $dob  =  $_POST['dob'];

    if ($common_id != 7) {
        $status = '';
        $class  = '';
    } else {
        $status = $obj->test_input($_POST['status']);
        $class  = $obj->test_input($_POST['class']);
    }

    //check Duplicate
    $count = $obj->getvalfield("$tblname", "count(*)", "account_name='$account_name' and $tblpkey!='$keyvalue'");

    if ($count > 0) {
        $action = 4;
        $process = "Duplicate";
    } else //insert
    {
        if ($keyvalue == 0) {

            $form_data = array(
                'account_name' => $account_name,
                'owner_name' => $owner_name,
                'o_mobile_no' => $o_mobile_no,
                'mobile_no' => $mobile_no,
                'address' => $address,
                'common_id' => $common_id,
                'area_id' => $area_id,
                'status' => $status,
                'class' => $class,
                'type' => $type,
                'no_of_kid' => $no_of_kid,
                'no_of_family' => $no_of_family,
                'doa' => $doa,
                'dob' => $dob,
                'opening_balance' => $opening_balance,
                'opening_date' => $opening_date,
                'status1' => 1,
                'ipaddress' => $ipaddress,
                "companyid" => $companyid,
                'createdate' => $createdate
            );
            $obj->insert_record($tblname, $form_data);
            $action = 1;
            $process = "inserted";
        } else {

            //update
            $form_data = array(
                'account_name' => $account_name,
                'owner_name' => $owner_name,
                'o_mobile_no' => $o_mobile_no,
                'mobile_no' => $mobile_no,
                'address' => $address,
                'common_id' => $common_id,
                'area_id' => $area_id,
                'status' => $status,
                'class' => $class,
                'opening_balance' => $opening_balance,
                'opening_date' => $opening_date,
                'type' => $type,
                'no_of_kid' => $no_of_kid,
                'no_of_family' => $no_of_family,
                'doa' => $doa,
                'dob' => $dob,
                'ipaddress' => $ipaddress,
                "companyid" => $companyid,
                'lastupdated' => $createdate
            );
            $where = array($tblpkey => $keyvalue);
            $obj->update_record($tblname, $where, $form_data);
            $action = 2;

            $process = "updated";
        }
    }

    echo "<script>location='$pagename?action=$action'</script>";
    die;
}






if (isset($_GET[$tblpkey])) {

    $btn_name = "Update";

    $where = array($tblpkey => $keyvalue);

    $sqledit = $obj->select_record($tblname, $where);
    $account_name  =  $sqledit['account_name'];
    $owner_name  =  $sqledit['owner_name'];
    $o_mobile_no  =  $sqledit['o_mobile_no'];
    $mobile_no  =  $sqledit['mobile_no'];
    $address  =  $sqledit['address'];
    $opening_balance  =  $sqledit['opening_balance'];
    $opening_date  =  $sqledit['opening_date'];
    $common_id  =  $sqledit['common_id'];
    $area_id  =  $sqledit['area_id'];
    $status  =  $sqledit['status'];
    $type  =  $sqledit['type'];
    $class  =  $sqledit['class'];
    $no_of_kid  =  $sqledit['no_of_kid'];
    $no_of_family  =  $sqledit['no_of_family'];
    $doa  =  $sqledit['doa'];
    $dob  =  $sqledit['dob'];
} else {

    $account_name = $owner_name = $opening_balance = $o_mobile_no = $mobile_no = $address = $area_id =  "";
    $type = $class = $no_of_kid = $no_of_family = $doa = $dob = "";
    $common_id = "7";
    $status = "active";
    $opening_date = date('Y-m-d');
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

        <!-- Header -->

        <?php include('component/header.php'); ?>

        <!-- Header Close-->

        <!-- Content -->

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">

                    <fieldset class="mt-2">

                        <legend><?= $module ?></legend>

                        <?php include('component/alert.php'); ?>

                        <form action="" method="post">

                            <div class="card">

                                <div class="card-header text-white">

                                    <?= $module ?>

                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Counter / Customer Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="account_name" id="account_name" placeholder="Counter/Customer Name" value="<?php echo $account_name; ?>" autocomplete="off">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Owner Name <span class="text-danger fw-bold"></span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="owner_name" id="owner_name" placeholder="Owner Name" value="<?php echo $owner_name; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">Whatsapp No. <span class="text-danger fw-bold">*</span></label> </strong>
                                            <input type="text" class="form-control form-control-sm" name="mobile_no" id="mobile_no" placeholder="Whatsapp No." value="<?php echo $mobile_no; ?>" maxlength="10" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">Owner Mobile No. <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="text" class="form-control form-control-sm" name="o_mobile_no" id="o_mobile_no" placeholder="Owner Mobile No." value="<?php echo $o_mobile_no; ?>" maxlength="10" autocomplete="off">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="common_id">Counter Type<span class="text-danger fw-bold">*</span> </label></strong>
                                            <select name="common_id" id="common_id" class="chosen-select form-control form-control-sm">
                                                <option value="">--Select Counter Type--</option>
                                                <?php
                                                $sql = $obj->executequery("select common_id,common_name from common_master where type='acc_type' order by common_id asc ");
                                                foreach ($sql as $key) {
                                                ?>
                                                    <option value="<?= $key['common_id'] ?>"><?= $key['common_name'] ?></option>
                                                <?php } ?>
                                                <option value="-1">Employee</option>
                                            </select>
                                            <script>
                                                document.getElementById('common_id').value = '<?php echo $common_id; ?>';
                                            </script>
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2 retailer-fields">
                                            <strong> <label for="class">Class<span class="text-danger fw-bold">*</span> </label></strong>
                                            <select name="class" id="class" class="chosen-select  form-control form-control-sm">
                                                <option value="">--Select Class--</option>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                            </select>
                                            <script>
                                                document.getElementById('class').value = '<?php echo $class; ?>';
                                            </script>
                                        </div>

                                        <div class="col-lg-3 col-md-4 col-sm-6 retailer-fields">
                                            <strong> <label for="status">Status<span class="text-danger fw-bold">*</span> </label></strong>
                                            <select name="status" id="status" class="chosen-select  form-control form-control-sm">
                                                <option value="">--Select Status--</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                            <script>
                                                document.getElementById('status').value = '<?php echo $status; ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="area_id">Area<span class="text-danger fw-bold">*</span> </label></strong>
                                            <select name="area_id" id="area_id" class="chosen-select  form-control form-control-sm">
                                                <option value="">--Select Area--</option>
                                                <?php
                                                $sql = $obj->executequery("select area_id,area_name from area_master order by area_name asc ");
                                                foreach ($sql as $key) {
                                                ?>
                                                    <option value="<?= $key['area_id'] ?>"><?= $key['area_name'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('area_id').value = '<?php echo $area_id; ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">No. Of Kids <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="number" class="form-control form-control-sm" name="no_of_kid" id="no_of_kid" placeholder="No. Of Kids" value="<?php echo $no_of_kid; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">D.O.B <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="dob" id="dob" value="<?php echo $dob; ?>" maxlength="10" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">No. Of Family Members <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="number" class="form-control form-control-sm" name="no_of_family" id="no_of_family" placeholder="No. Of Family Members " value="<?php echo $no_of_family; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">D.O.A <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="doa" id="doa" placeholder="Opening Date" value="<?php echo $doa; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">Opening Balance <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="number" class="form-control form-control-sm" name="opening_balance" id="opening_balance" placeholder="Opening Balance" value="<?php echo $opening_balance; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">Opening Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="opening_date" id="opening_date" placeholder="Opening Date" value="<?php echo $opening_date; ?>" maxlength="10" autocomplete="off">
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                                            <strong> <label for="mobile">Address <span class="text-danger fw-bold"></span></label></strong>
                                            <textarea class="form-control form-control-sm" name="address" id="address" placeholder="Address" autocomplete="off"><?php echo $address; ?></textarea>
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onclick="return checkinputmaster('account_name,common_id,area_id');">
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
                            <?php echo $submodule; ?> List
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th>Sr. No.</th>
                                        <th>Counter/Customer Name</th>
                                        <th>Owner Name</th>
                                        <th>Whatsapp No.</th>
                                        <th>Owner Mobile No.</th>
                                        <th>Counter Type</th>
                                        <th>Class</th>
                                        <th>D.O.B</th>
                                        <th>D.O.A</th>
                                        <th>No. of Kids</th>
                                        <th>No. of Family Members</th>
                                        <th>Opening Balance</th>
                                        <th>Opening Date</th>
                                        <th>Address</th>
                                        <th>Area</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $sql_get = $obj->executequery("select * from account where status1 != 0 order by account_id desc");
                                        foreach ($sql_get as $row_get) {
                                            $common_name = ($row_get['common_id'] == -1) ? "Employee" : $obj->getvalfield("common_master", "common_name", "common_id='" . $row_get['common_id'] . "'");
                                            $area_name = $obj->getvalfield("area_master", "area_name", "area_id='{$row_get['area_id']}'");
                                        ?>
                                            <tr>
                                                <td> <?php echo $slno++; ?></td>
                                                <td><?php echo $row_get['account_name']; ?></td>
                                                <td><?php echo $row_get['owner_name']; ?></td>
                                                <td><?php echo $row_get['mobile_no']; ?></td>
                                                <td><?php echo $row_get['o_mobile_no']; ?></td>
                                                <td><?php echo $common_name; ?></td>
                                                <td><?php echo $row_get['class']; ?></td>
                                                <td><?php echo $obj->dateformatindia($row_get['dob']); ?></td>
                                                <td><?php echo $obj->dateformatindia($row_get['doa']); ?></td>
                                                <td><?php echo $row_get['no_of_kid']; ?></td>
                                                <td><?php echo $row_get['no_of_family']; ?></td>
                                                <td><?php echo $row_get['opening_balance']; ?></td>
                                                <td><?php echo $obj->dateformatindia($row_get['opening_date']); ?></td>
                                                <td><?php echo $row_get['address']; ?></td>
                                                <td><?php echo $area_name; ?></td>
                                                <td><?php ucfirst($row_get['status']) ?></td>
                                                <td class="text-center">
                                                    <a href="accounts.php?account_id=<?php echo  $row_get['account_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel(<?php echo  $row_get['account_id']; ?>);"><i class="bi bi-trash3-fill"></i></button>
                                                </td>
                                            </tr>
                                        <?php  } ?>
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
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });

    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        pagename = '<?php echo $pagename; ?>';
        submodule = '<?php echo $submodule; ?>';
        module = '<?php echo $module; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close

    $(document).ready(function() {

        //called when key is pressed in textbox

        $("#mobile_no,#o_mobile_no").keypress(function(e) {

            //if the letter is not digit then display error and don't type anything

            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                //display error message

                $("#errmsg").html("Digits Only").show().fadeOut("slow");

                return false;

            }

        });

    });

    function toggleRetailerFields() {

        var retailerText = $("#common_id option:selected").text().trim();

        if (retailerText.toLowerCase() == "retailer") {

            $(".retailer-fields").show();

        } else {

            $(".retailer-fields").hide();

            $("#class").val('').trigger("chosen:updated");
            $("#status").val('').trigger("chosen:updated");
        }
    }

    $(document).ready(function() {

        toggleRetailerFields();

        $("#common_id").change(function() {
            toggleRetailerFields();
        });
    });
</script>

</html>