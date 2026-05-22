<?php
include("../adminsession.php");
$title = "KRA Behavioural Aspect";
$pagename = "kra_behavioural_aspect.php";
$module = "KRA Behavioural Aspect";
$submodule = "KRA Behavioural Aspect List";
$btn_name = "Save";
$tblname = "kra_behavioural_aspect";
$tblpkey = "kra_behaviour_id";
$keyvalue = (isset($_GET["kra_behaviour_id"])) ? $obj->test_input($_GET["kra_behaviour_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";


if (isset($_POST['submit'])) {
    $kra_behaviour_name = $obj->test_input($_POST['kra_behaviour_name']);
    $dup = $obj->getvalfield("$tblname", "count(*)", "kra_behaviour_name= '$kra_behaviour_name' AND  $tblpkey != '$keyvalue'");

    if ($dup > 0) {
        $action = 4;
        echo "<script>location='$pagename?action=$action'</script>";
    } else {

        $form_data = array(
            "kra_behaviour_name" => $kra_behaviour_name,
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
    }
    echo "<script>location='$pagename?action=$action'</script>";
}

if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $kra_behaviour_name = $sqledit['kra_behaviour_name'];
} else {
    $kra_behaviour_name = "";
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
                                            <strong><label for="kra_behaviour_name" class="form-label"> KRA Behavioural Aspect Name <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" placeholder="Enter KRA Behavioural Aspect Name" id="kra_behaviour_name" name="kra_behaviour_name" value="<?php echo $kra_behaviour_name ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md mt-2 "><br />
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('kra_behaviour_name')">
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
                                        <th>KRA Behavioural Aspect Name</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT * FROM $tblname ORDER BY kra_behaviour_id desc");
                                        foreach ($sql as $key) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo ucfirst($key['kra_behaviour_name']); ?> </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['kra_behaviour_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <!-- <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php //echo $key['kra_behaviour_id']; 
                                                                                                                                                ?>');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button> -->
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
</script>

</html>