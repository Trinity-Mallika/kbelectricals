<?php
include("../adminsession.php");
$title = "Electrician Entry";
$pagename = "electrician.php";
$module = "Electrician Entry";
$submodule = "Electrician Entry List";
$btn_name = "Save";
$tblname = "account";
$tblpkey = "account_id";
$keyvalue = (isset($_GET["account_id"])) ? $obj->test_input($_GET["account_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$type = "electrician";


if (isset($_POST['submit'])) {
    $electrician_name = $obj->test_input($_POST['electrician_name']);
    $whatsapp_no = $obj->test_input($_POST['whatsapp_no']);
    $account_id_map = $obj->test_input($_POST['account_id_map']);
    $user_id = $obj->test_input($_POST['user_id']);
    $dup = $obj->getvalfield("$tblname", "count(*)", "mobile_no= '$electrician_name' AND account_id_map='$account_id_map' AND  $tblpkey != '$keyvalue'");

    if ($dup > 0) {
        $action = 4;
        echo "<script>location='$pagename?action=$action'</script>";
    } else {

        $form_data = array(
            "account_name" => $electrician_name,
            "mobile_no" => $whatsapp_no,
            "account_id_map" => $account_id_map,
            "type" => $type,
            "status"       => "active",
            "userid"       =>  $user_id,
            "status1"      => 1,
            'common_id'      => 6, // electrician
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
    $electrician_name = $sqledit['account_name'];
    $whatsapp_no = $sqledit['mobile_no'];
    $account_id_map = $sqledit['account_id_map'];
    $user_id = $sqledit['userid'];
} else {
    $electrician_name = "";
    $whatsapp_no = "";
    $user_id = "";
    $account_id_map = (isset($_GET['account_id_map'])) ? $_GET['account_id_map'] : "";
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
                                        <div class="col-md-3 mb-2">
                                            <strong><label for="account_id_map" class="form-label">Counter/Customer Name<span class="text-danger">*</span></label></strong>
                                            <select name="account_id_map" id="account_id_map" class="form-control form-control-sm chosen-select">
                                                <option value="">--Select Counter/Customer--</option>
                                                <?php
                                                $counter = $obj->executequery("SELECT * FROM account ORDER BY account_id ASC");
                                                foreach ($counter as $u) { ?>
                                                    <option value="<?php echo $u['account_id']; ?>"><?php echo $u['account_name']; ?></option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('account_id_map').value = '<?php echo $account_id_map ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label for="electrician_name" class="form-label">Electrician Name <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" placeholder="Enter Electrician Name" id="electrician_name" name="electrician_name" value="<?php echo $electrician_name ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label for="whatsapp_no" class="form-label">Whatsapp Number <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" placeholder="Enter Whatsapp Number" id="whatsapp_no" name="whatsapp_no" value="<?php echo $whatsapp_no ?>" autocomplete="off" onkeypress='numberOnly(event)' maxlength='10' />
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="user_id" class="form-label">Referred By<span class="text-danger fw-bold">*</span> </label></strong>
                                            <select id="user_id" class="chosen-select form-control form-control-sm">
                                                <option value="">--Select Referred By--</option>
                                                <?php
                                                $sql = $obj->executequery("select userid,fullname,usertype from user where status='1' order by userid asc ");
                                                foreach ($sql as $key) {
                                                ?>
                                                    <option value="<?= $key['userid'] ?>"><?= $key['fullname'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('user_id').value = '<?php echo $user_id ?>';
                                            </script>
                                        </div>
                                        <div class="col-md mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('account_id,electrician_name,whatsapp_no,user_id')">
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
                                        <th>Counter/Customer Name</th>
                                        <th>Electrician Name</th>
                                        <th>Whatsapp Number</th>
                                        <th>Referred By</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $crit = "where type='$type'";
                                        if ($account_id_map > 0) {
                                            $crit .= " and account_id_map='$account_id_map'";
                                        }
                                        $sql = $obj->executequery("SELECT * FROM $tblname $crit ORDER BY account_id desc");
                                        foreach ($sql as $key) {
                                            $account_name = $obj->getvalfield("account", "account_name", "account_id='{$key['account_id_map']}'");
                                            $fullname = $obj->getvalfield("user", "fullname", "userid='{$key['userid']}'");
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo ucfirst($account_name); ?> </td>
                                                <td><?php echo ucfirst($key['account_name']); ?> </td>
                                                <td><?php echo $key['mobile_no']; ?> </td>
                                                <td><?php echo ucfirst($fullname); ?> </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['account_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo $key['account_id']; ?>');">
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