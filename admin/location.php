<?php include("../adminsession.php");
$title = "Route Wise Counter";
$pagename = "route_wise_counter.php";
$module = "Route Wise Counter";
$submodule = "Route Wise Counter";
$btn_name = "Save";
$keyvalue = 0;
$tblname = "location";
$tblpkey = "location_id";

if (isset($_GET['location_id']))
    $keyvalue = $_GET['location_id'];
else
    $keyvalue = 0;
if (isset($_GET['action'])) {
    $action = $obj->test_input($_GET['action']);
} else {
    $action = "";
}

if (isset($_POST['submit'])) {
    $keyvalue = $obj->test_input($_POST['location_id']);
    $sequence = $obj->test_input($_POST['sequence']);
    $route_id = $obj->test_input($_POST['route_id']);
    $account_id = $obj->test_input($_POST['account_id']);
    $count_sequence = $obj->getvalfield(
        $tblname,
        "count(*)",
        "sequence='$sequence' AND route_id='$route_id' AND location_id!='$keyvalue'"
    );

    // 🔴 Check duplicate account in same route
    $count_account = $obj->getvalfield(
        $tblname,
        "count(*)",
        "account_id='$account_id' AND route_id='$route_id' AND location_id!='$keyvalue'"
    );

    if ($count_sequence > 0) {
        $action = 4;
        $process = "Duplicate Sequence in same Route";
    } elseif ($count_account > 0) {
        $action = 4;
        $process = "Duplicate Account in same Route";
    } else {
        if ($keyvalue == 0) {

            $form_data = array(
                'sequence' => $sequence,
                'route_id' => $route_id,
                'account_id' => $account_id,
                'ipaddress' => $ipaddress,
                'createdate' => $createdate,
                'companyid' => $companyid,
                'createdby' => $loginid
            );

            $lastid = $obj->insert_record($tblname, $form_data);




            $action = 1;

            $process = "insert";
        } else {

            //update

            $form_data = array(
                'sequence' => $sequence,
                'route_id' => $route_id,
                'account_id' => $account_id,
                'ipaddress' => $ipaddress,
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
    $sequence = $sqledit['sequence'];
    $route_id = $sqledit['route_id'];
    $account_id = $sqledit['account_id'];
} else {
    $sequence = "";
    $route_id = "";
    $account_id = "";
}

// query builder

?>

<!DOCTYPE html>

<html lang="en">



<head>
    <!-- meta tag -->

    <?php include('component/css.php'); ?>

    <!-- meta tag -->

    <script src="assets/js/nice-editor.js"></script>

    <script type="text/javascript">
        //bkLib.onDomLoaded(nicEditors.allTextAreas);
    </script>

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



        .nicEdit-panelContain {

            border: none !important;

            border-radius: 8px 8px 0px 0px;

        }



        .nicEdit-panel {

            background: #06163a;

            padding: 5px;

            margin: 0px !important;

            display: flex;

            justify-content: center;

        }



        .nicEdit-main {

            min-height: 170px !important;

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

                                <form action="" method="post" enctype="multipart/form-data">

                                    <div class="row">

                                        <div class="col-md-4 mb-2">
                                            <strong><label>Route <span class="text-danger">*</span></label></strong>
                                            <select name="route_id" id="route_id" class="form-control chosen-select">
                                                <option value="">Select Route</option>
                                                <?php
                                                $categories = $obj->executequery("SELECT * FROM route ORDER BY route_name desc");
                                                foreach ($categories as $c) { ?>
                                                    <option value="<?php echo $c['route_id']; ?>"><?php echo $c['route_name']; ?></option>
                                                <?php } ?>


                                            </select>
                                            <script>
                                                document.getElementById('route_id').value = '<?php echo $route_id ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><label>Account Name</label></strong>
                                            <select class="form-control form-control-sm chosen-select" name="account_id" id="account_id" onchange="get_url(this.value);">
                                                <option value="">Select</option>
                                                <?php $res = $obj->executequery("Select account_id,account_name from account order by account_name asc");
                                                foreach ($res as $key) {
                                                    echo "<option value='{$key['account_id']}'>{$key['account_name']}</option>";
                                                } ?>
                                            </select>
                                            <script>
                                                document.getElementById('account_id').value = '<?php echo $account_id  ?>';
                                            </script>
                                        </div>

                                        <div class="col-md-4 mb-2">

                                            <strong> <label for="images">Sequence <span class="text-danger fw-bold">*</span></label></strong>

                                            <input type="number" class="form-control form-control-sm" name="sequence" id="sequence" value="<?php echo $sequence ?>" placeholder="Enter Sequence">



                                        </div>



                                        <div class="col-md-2 mb-2">

                                            <br />

                                            <input type="submit" onclick="return checkinputmaster('route_id,account_id,sequence')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">

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


                                        <th class="text-center">Route Name</th>
                                        <th class="text-center">Account Name</th>
                                        <th class="text-center">Sequence</th>


                                        <th class="text-center">Action</th>

                                    </thead>

                                    <tbody>

                                        <?php



                                        $i = 1;

                                        $sql = $obj->executequery("
SELECT
    t.*,
    r.route_name,
    a.account_name
FROM $tblname t
LEFT JOIN route r
    ON r.route_id = t.route_id
LEFT JOIN account a
    ON a.account_id = t.account_id
ORDER BY t.$tblpkey DESC
");

                                        foreach ($sql as $key) {



                                        ?>

                                            <tr>

                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $key['route_name']; ?></td>
                                                <td><?php echo $key['account_name']; ?></td>
                                                <td><?php echo $key['sequence']; ?></td>
                                                <td class="text-center">

                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['location_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>

                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo  $key['location_id']; ?>');"><i class="bi bi-trash3-fill"></i></button>

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

        <!-- Content close-->

    </div>



</body>



<!-- script tag -->

<?php include('component/script.php'); ?>

<!-- script tag -->

<script>
    function funDel(id) {

        // alert(id);

        tblname = '<?php echo $tblname; ?>';

        tblpkey = '<?php echo $tblpkey; ?>';

        pagename = '<?php echo $pagename; ?>';

        submodule = '<?php echo $submodule; ?>';

        module = '<?php echo $module; ?>';


        //alert(module);

        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({

                type: 'POST',

                url: 'ajax/delete_master.php',

                data: 'id=' + id +
                    '&tblname=' + tblname +
                    '&tblpkey=' + tblpkey +
                    '&submodule=' + submodule +
                    '&pagename=' + pagename +
                    '&module=' + module,


                dataType: 'html',

                success: function(data) {

                    //alert(data);

                    location = '<?php echo $pagename . "?action=3"; ?>';

                }

            }); //ajax close

        } //confirm close

    } //fun close

    $(document).ready(function() {

        $(".chosen-select").chosen();

        $('#example').DataTable();

        // $('#inputmasl').inputmask("99-99-9999");

        // $('#datepicker').datepicker({

        //     autoclose: true,

        //     format: 'dd-mm-yyyy'

        // })



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

        if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123))

            return true;

        else {

            //  alert("Please enter only alphabets");

            return false;

        }

    }
</script>



</html>