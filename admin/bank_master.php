<?php
include("../adminsession.php");
$title = "Bank Master";
$pagename = "bank_master.php";
$module = "Bank Master";
$submodule = "Bank Master List";
$btn_name = "Save";
$tblname = "bank_master";
$tblpkey = "bank_id";
$keyvalue = (isset($_GET[$tblpkey])) ? $obj->test_input($_GET[$tblpkey]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

if (isset($_POST['submit'])) {
    $bank_name = $obj->test_input($_POST['bank_name']);
    $branch_name = $obj->test_input($_POST['branch_name']);
    $account_holder_name = $obj->test_input($_POST['account_holder_name']);
    $account_no = $obj->test_input($_POST['account_no']);
    $ifsc_code = $obj->test_input($_POST['ifsc_code']);
    $account_type = $obj->test_input($_POST['account_type']);
    $address = $obj->test_input($_POST['address']);

    $dup = $obj->getvalfield("$tblname", "count(*)", "account_no= '$account_no' AND  $tblpkey != '$keyvalue'");

    if ($dup > 0) {
        $action = 4;
        echo "<script>location='$pagename?action=$action'</script>";
    } else {

        $form_data = array(
            "bank_name" => $bank_name,
            "branch_name" => $branch_name,
            "account_holder_name" => $account_holder_name,
            "account_no" => $account_no,
            "ifsc_code" => $ifsc_code,
            "account_type" => $account_type,
            "address" => $address,
            "createdby" => $loginid,
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
    $bank_name = $sqledit['bank_name'];
    $branch_name = $sqledit['branch_name'];
    $account_holder_name = $sqledit['account_holder_name'];
    $account_no = $sqledit['account_no'];
    $ifsc_code = $sqledit['ifsc_code'];
    $account_type = $sqledit['account_type'];
    $address = $sqledit['address'];
} else {
    $bank_name = $branch_name = $account_holder_name = $account_no = $ifsc_code = $account_type = $address =  "";
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
                        <form method="post" autocomplete="off">
                            <div class="card">
                                <div class="card-header text-white">
                                    <?php echo $module ?>
                                </div>

                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">
                                                Bank Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="bank_name"
                                                class="form-control form-control-sm"
                                                value="<?= $bank_name ?>"
                                                placeholder="Enter Bank Name">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">
                                                Account No. <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="account_no"
                                                class="form-control form-control-sm"
                                                value="<?= $account_no ?>"
                                                placeholder="Enter Account No.">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">Branch Name</label>
                                            <input type="text" name="branch_name"
                                                class="form-control form-control-sm"
                                                value="<?= $branch_name ?>"
                                                placeholder="Enter Branch Name">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">
                                                Account Holder <span class="text-danger"></span>
                                            </label>
                                            <input type="text" name="account_holder_name"
                                                class="form-control form-control-sm"
                                                value="<?= $account_holder_name ?>"
                                                placeholder="Enter Account Holder">
                                        </div>



                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">IFSC Code</label>
                                            <input type="text" name="ifsc_code"
                                                class="form-control form-control-sm"
                                                value="<?= $ifsc_code ?>"
                                                placeholder="Enter IFSC Code">
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">Account Type</label>
                                            <select name="account_type" class="form-control form-control-sm">
                                                <option value="Savings" <?= ($account_type == 'Savings') ? 'selected' : '' ?>>Savings</option>
                                                <option value="Current" <?= ($account_type == 'Current') ? 'selected' : '' ?>>Current</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label fw-bold">Address</label>
                                            <textarea name="address" class="form-control form-control-sm"
                                                placeholder="Enter Address"><?= $address ?></textarea>
                                        </div>
                                        <!-- Buttons -->
                                        <div class="col-md-12 mt-3">
                                            <input type="submit"
                                                name="submit"
                                                class="btn btn-theme btn-sm"
                                                value="<?php echo $btn_name; ?>" onclick="checkinputmaster('bank_name,account_no');">

                                            <a href="<?php echo $pagename; ?>"
                                                class="btn btn-danger btn-sm">Reset</a>

                                            <input type="hidden"
                                                name="<?php echo $tblpkey; ?>"
                                                value="<?php echo $keyvalue; ?>">
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
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Bank Name </th>
                                            <th>Account No.</th>
                                            <th>Branch Name</th>
                                            <th>Account Holder</th>
                                            <th>IFSC Code</th>
                                            <th>Type</th>
                                            <th>Address</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT * FROM $tblname ORDER BY bank_id DESC");
                                        foreach ($sql as $row) {
                                        ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $row['bank_name'] ?></td>
                                                <td><?= $row['account_no'] ?></td>
                                                <td><?= $row['branch_name'] ?></td>
                                                <td><?= $row['account_holder_name'] ?></td>
                                                <td><?= $row['ifsc_code'] ?></td>
                                                <td><?= $row['account_type'] ?></td>
                                                <td><?= $row['address'] ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $row['bank_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo $row['bank_id']; ?>');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
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