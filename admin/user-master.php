<?php include("../adminsession.php");
$title = "User Master";

$pagename = "user-master.php";

$module = "Add Users";

$submodule = "User Master";

$btn_name = "Save";

$keyvalue = "0";

$tblname = "user";

$tblpkey = "userid";

$username = "";

$password = "";

$mobile = "";

$fullname  = "";

$status  = "";

$usertype = "";
$route_id = 0;

$dup = "";



if (isset($_GET['action']))

    $action = addslashes(trim($_GET['action']));

else

    $action = "";



if (isset($_GET['userid'])) {

    $keyvalue = $_GET['userid'];
} else {

    $keyvalue = 0;
}



if (isset($_POST['submit'])) {

    $username = $obj->test_input($_POST['username']);

    $password = $obj->test_input($_POST['password']);

    $mobile = $obj->test_input($_POST['mobile']);

    $fullname = $obj->test_input($_POST['fullname']);

    $route_id = $obj->test_input($_POST['route_id']);

    $status = $obj->test_input($_POST['enable']);



    $usertype = $obj->test_input($_POST['usertype']);

    //check Duplicate

    $count = $obj->getvalfield("$tblname", "count(*)", "username='$username' and $tblpkey!='$keyvalue'");

    if ($count > 0) {

        $action = 4;

        $process = "Duplicate";

        //echo $dup; die;

    } else //insert

    {



        if ($keyvalue == 0) {



            $form_data = array(
                'username' => $username,
                'password' => $password,
                'mobile' => $mobile,
                'fullname' => $fullname,
                'route_id' => $route_id,
                'status' => $status,
                'usertype' => $usertype,
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
                'username' => $username,
                'password' => $password,
                'mobile' => $mobile,
                'fullname' => $fullname,
                'route_id' => $route_id,
                'status' => $status,
                'usertype' => $usertype,
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
}





if (isset($_GET[$tblpkey])) {

    $btn_name = "Update";

    $where = array($tblpkey => $keyvalue);

    $sqledit = $obj->select_record($tblname, $where);

    $username  =  $sqledit['username'];

    $password  =  $sqledit['password'];

    $mobile  =  $sqledit['mobile'];

    $fullname  =  $sqledit['fullname'];

    $route_id  =  $sqledit['route_id'];

    $status  =  $sqledit['status'];

    $usertype  =  $sqledit['usertype'];
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

        <!-- Header -->

        <?php include('component/header.php'); ?>

        <!-- Header Close-->

        <!-- Content -->

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">

                    <fieldset class="mt-2">

                        <legend>User Master</legend>

                        <?php include('component/alert.php'); ?>

                        <form action="" method="post">

                            <div class="card">

                                <div class="card-header text-white">

                                    Create User

                                </div>





                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-3 mb-2">

                                            <strong> <label for="username">User Name <span class="text-danger fw-bold">*</span></label></strong>

                                            <input type="text" autofocus class="form-control form-control-sm" onkeypress="return allowOnlyLetters(event,this);" name="username" id="username" placeholder="User Name" value="<?php echo $username; ?>" autocomplete="off">

                                        </div>



                                        <div class="col-md-3 mb-2">


                                            <strong> <label for="password">Password <span class="text-danger fw-bold">*</span></label>

                                            </strong>

                                            <input type="password" class="form-control form-control-sm" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>" autocomplete="off">

                                        </div>

                                        <div class="col-md-3 mb-2">


                                            <strong> <label for="mobile">Contact No. <span class="text-danger fw-bold">*</span></label></strong>

                                            <input type="text" class="form-control form-control-sm" name="mobile" id="mobile" placeholder="Contact No." value="<?php echo $mobile; ?>" maxlength="10" autocomplete="off">

                                            <span id="errmsg" class="text-danger"></span>

                                        </div>


                                        <div class="col-md-3 mb-2">


                                            <strong> <label for="name">Fullname <span class="text-danger fw-bold">*</span></label></strong>

                                            <input type="text" class="form-control form-control-sm" onkeypress="return allowOnlyLetters(event,this);" name="fullname" id="fullname" placeholder="Fullname" value="<?php echo $fullname; ?>" autocomplete="off">

                                            <span id="errmsg" class="text-danger"></span>

                                        </div>

                                        <div class="col-md-3 mb-2">


                                            <strong> <label for="enable">Status<span class="text-danger fw-bold">*</span> </label></strong>

                                            <select name="enable" id="enable" class="chosen-select  form-control form-control-sm">

                                                <option value="">--Select status--</option>

                                                <option value="1">Enable</option>

                                                <option value="0">Disable</option>

                                            </select>

                                            <script>
                                                document.getElementById('enable').value = '<?php echo $status; ?>';
                                            </script>

                                        </div>

                                        <div class="col-md-3 mb-2">


                                            <strong> <label for="usertype">Usertype <span class="text-danger fw-bold">*</span></label></strong>

                                            <select name="usertype" id="usertype" class="chosen-select  form-control form-control-sm">

                                                <option value="">--Select user type--</option>

                                                <option value="sales">Sales Executive</option>
                                                <option value="user">User</option>

                                            </select>

                                            <script>
                                                document.getElementById('usertype').value = '<?php echo $usertype; ?>';
                                            </script>

                                        </div>

                                        <div class="col-md-3 mb-2"><br />

                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onclick="return validateUserForm();">

                                            <a href="<?php echo $pagename;

                                                        ?>" class="btn btn-danger btn-sm"> Reset </a>

                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">

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
                                        <th>User Name</th>
                                        <th>Password</th>
                                        <th>User Type</th>
                                        <th>Mobile No.</th>
                                        <th>Full Name</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $sql_get = $obj->executequery("select * from user order by userid desc");
                                        foreach ($sql_get as $row_get) {
                                            if ($row_get['usertype'] != "admin" && $row_get['companyid'] == $companyid) { ?>
                                                <tr>
                                                    <td> <?php echo $slno++; ?></td>
                                                    <td><?php echo $row_get['username']; ?></td>
                                                    <td><?php echo $row_get['password']; ?></td>
                                                    <td><?php echo $row_get['usertype']; ?></td>
                                                    <td><?php echo $row_get['mobile']; ?></td>
                                                    <td><?php echo $row_get['fullname']; ?></td>
                                                    <td><?php echo ($row_get['status'] == 1) ? "Enable" : "Disable"; ?></td>
                                                    <td class="text-center">
                                                        <a href="user-master.php?userid=<?php echo $row_get['userid']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                        <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel(<?php echo $row_get['userid']; ?>);"><i class="bi bi-trash3-fill"></i></button>
                                                    </td>
                                                </tr>
                                            <?php } else if ($row_get['usertype'] == "admin") { ?>
                                                <tr>
                                                    <td> <?php echo $slno++; ?></td>
                                                    <td><?php echo $row_get['username']; ?></td>
                                                    <td><?php echo $row_get['password']; ?></td>
                                                    <td><?php echo $row_get['usertype']; ?></td>
                                                    <td><?php echo $row_get['mobile']; ?></td>
                                                    <td><?php echo $row_get['fullname']; ?></td>
                                                    <td><?php echo ($row_get['status'] == 1) ? "Enable" : "Disable"; ?></td>
                                                    <td class="text-center">
                                                        <i class="bi bi-x-circle text-danger fs-6"></i>
                                                    </td>
                                                </tr>
                                        <?php }
                                        } ?>
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
        $(".chosen-select").chosen({
            width: "100%"
        });
        $('#example').DataTable();

    });


    function funDel(id)



    { //alert(id);



        tblname = '<?php echo $tblname; ?>';



        tblpkey = '<?php echo $tblpkey; ?>';



        pagename = '<?php echo $pagename; ?>';



        submodule = '<?php echo $submodule; ?>';



        module = '<?php echo $module; ?>';



        //alert(module);



        if (confirm("Are you sure! You want to delete this record."))



        {



            jQuery.ajax({



                type: 'POST',



                url: 'ajax/delete_master.php',



                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module,



                dataType: 'html',



                success: function(data) {

                    //alert(data);

                    location = '<?php echo $pagename . "?action=3"; ?>';

                }



            }); //ajax close



        } //confirm close



    } //fun close



    $(document).ready(function() {

        //called when key is pressed in textbox

        $("#mobile").keypress(function(e) {

            //if the letter is not digit then display error and don't type anything

            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                //display error message

                $("#errmsg").html("Digits Only").show().fadeOut("slow");

                return false;

            }

        });

    });
</script>

<script>
    function allowOnlyLetters(e, t) {

        if (window.event) {

            var charCode = window.event.keyCode;

        } else if (e) {

            var charCode = e.which;

        } else {

            return true;

        }

        if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || (charCode == 32))

            return true;

        else {

            alert("Please enter only alphabets");

            return false;

        }

    }
</script>



</html>