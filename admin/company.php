<?php include("../adminsession.php");

$pagename = "company.php";
$module = "CompanyMaster";
$submodule = $title = "COMPANY MASTER";
$tblname = "company_setting";
$tblpkey = "company_id";
// $keyvalue = "1";
$keyvalue = $companyid;
$imgpath = "uploaded/company/";
if (isset($_GET['action'])) {
    $action = addslashes(trim($_GET['action']));
} else {
    $action = "";
}

// Default values (to avoid undefined variable error)
$company_name = "";
$short_name = "";
$mobile = "";
$address = "";
$email = "";
$term_cond = "";
$gsttinno = "";
$contact_no = "";
$bank_name = "";
$ifsc_code = "";
$account_no = "";
$account_branch = "";
$pan = "";
$comp_logo = "";

if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $company_name = $sqledit['company_name'];
    $short_name = $sqledit['short_name'];
    $mobile = $sqledit['mobile'];
    $address = $sqledit['address'];
    $email = $sqledit['email'];
    $term_cond = $sqledit['term_cond'];
    $gsttinno = $sqledit['gst'];
    $contact_no = $sqledit['contact_no'];
    $account_branch = $sqledit['account_branch'];
    $account_no = $sqledit['account_no'];
    $ifsc_code = $sqledit['ifcs_code'];
    $bank_name = $sqledit['bank_name'];
    $pan = $sqledit['pan'];
    $comp_logo = $sqledit['comp_logo'];
}


if (isset($_POST['submit'])) {

    $countRes = $obj->executequery("SELECT COUNT(*) as total FROM $tblname");
    $total = $countRes[0]['total'];

    // if ($total >= 2 && empty($_GET[$tblpkey]))
    if ($total >= 2 && $keyvalue == 0) {
        echo "<script>alert('Only 2 companies allowed');</script>";
        echo "<script>location='$pagename'</script>";
        exit;
    }

    $company_name = $obj->test_input($_POST['company_name']);
    $short_name = $obj->test_input($_POST['short_name']);
    $mobile   = $obj->test_input($_POST['mobile']);
    $address  = $obj->test_input($_POST['address']);
    $email  = $obj->test_input($_POST['email']);
    $term_cond  = $obj->test_input($_POST['term_cond']);
    $gsttinno = $obj->test_input($_POST['gsttinno']);
    $contact_no = $obj->test_input($_POST['contact_no']);
    $bank_name = $obj->test_input($_POST['bank_name']);
    $ifsc_code = $obj->test_input($_POST['ifsc_code']);
    $account_no = $obj->test_input($_POST['account_no']);
    $account_branch = $obj->test_input($_POST['account_branch']);
    $pan = $obj->test_input($_POST['pan']);

    //update
    $form_data = array(
        'contact_no' => $contact_no,
        'company_name' => $company_name,
        'short_name' => $short_name,
        'mobile' => $mobile,
        'address' => $address,
        'email' => $email,
        'bank_name' => $bank_name,
        'ifcs_code' => $ifsc_code,
        'account_no' => $account_no,
        'account_branch' => $account_branch,
        'term_cond' => $term_cond,
        'gst' => $gsttinno,
        'ipaddress' => $ipaddress,
        'lastupdated' => $createdate,
        'createdby' => $loginid,
        'pan' => $pan
    );
    $where = array($tblpkey => $keyvalue);
    $imageFileType = strtolower(pathinfo($_FILES["imgname"]['name'], PATHINFO_EXTENSION));
    if ($imageFileType == 'png' || $imageFileType == 'jpg' || $imageFileType == 'jpeg') {
        $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);
        $form_data["comp_logo"] = $filename;
    }

    if ($keyvalue == 0) {
        // INSERT
        $form_data["createdate"] = $createdate;
        $obj->insert_record($tblname, $form_data);
        $action = 1;
    } else {
        // UPDATE
        $form_data["lastupdated"] = $createdate;
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $action = 2;
    }

    $process = ($keyvalue == 0) ? "Insert" : "Update";

    echo "<script>location='$pagename?action=$action'</script>";
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
                        <legend><?php echo $title; ?></legend>
                        <?php include('component/alert.php'); ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-header text-white">
                                    Company Setting
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <strong> <label>Company Name <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="company_name" placeholder="Enter Company Name" value="<?php echo $company_name; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <strong> <label>Short Name <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="short_name" placeholder="Enter Short Name" value="<?php echo $short_name; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <strong> <label>Mobile <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="mobile" placeholder="Enter Mobile Number" value="<?php echo $mobile; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <strong> <label>Contact No.</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="contact_no" placeholder="Enter Contact Number" value="<?php echo $contact_no; ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong> <label>Email</label></strong>
                                            <input type="email" class="form-control form-control-sm" name="email" placeholder="Enter Email" value="<?php echo $email; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <strong> <label>Address</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="address" placeholder="Enter Address" value="<?php echo $address; ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong> <label>Bank Name</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="bank_name" placeholder="Enter Bank Name" value="<?php echo $bank_name; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <strong> <label>Account Number</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="account_no" placeholder="Enter Account Number" value="<?php echo $account_no; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <strong> <label>Branch</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="account_branch" placeholder="Enter Branch" value="<?php echo $account_branch; ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong> <label>IFSC Code</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="ifsc_code" placeholder="Enter IFSC Code" value="<?php echo $ifsc_code; ?>" maxlength="11"
                                                minlength="11"
                                                style="text-transform: uppercase;"
                                                pattern="[A-Z]{4}0[A-Z0-9]{6}"
                                                title="Enter valid IFSC code (e.g., SBIN0001234)">
                                        </div>

                                        <div class="col-md-4">
                                            <strong> <label>GST</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="gsttinno" placeholder="Enter GST" value="<?php echo $gsttinno; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <strong> <label>PAN</label></strong>
                                            <input type="text" class="form-control form-control-sm" name="pan" placeholder="Enter PAN" value="<?php echo $pan; ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong> <label>Terms & Conditions</label></strong>
                                            <textarea class="form-control form-control-sm" rows="4" name="term_cond" placeholder="Enter Terms & Conditions"><?php echo $term_cond; ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <strong> <label>Upload Logo</label></strong>
                                            <input type="file" name="imgname" id="imgname" class="form-control form-control-sm">
                                            <?php if ($comp_logo != '') { ?>
                                                <img src="<?= $imgpath . $comp_logo; ?>" width="150">
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="text-end">
                                        <button type="submit" name="submit" class="btn btn-success btn-sm">
                                            <?= ($keyvalue > 0) ? 'Update' : 'Save' ?>
                                        </button>
                                        <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
                                        <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>

            <!-- <div class="row mt-4 mb-4">
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
                                        <th>Company Name</th>
                                        <th>Short Name</th>
                                        <th>Mobile </th>
                                        <th>Contact No.</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Bank Name</th>
                                        <th>Account Number</th>
                                        <th>Branch</th>
                                        <th>IFSC Code</th>
                                        <th>GST</th>
                                        <th>PAN</th>
                                        <th>Terms & Conditions</th>
                                        <th>Upload Logo</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $res = $obj->executequery("SELECT * FROM $tblname ORDER BY $tblpkey DESC");
                                        $i = 1;

                                        foreach ($res as $row) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $i++ ?></td>
                                                <td><?= $row['company_name'] ?></td>
                                                <td><?= $row['short_name'] ?></td>
                                                <td><?= $row['mobile'] ?></td>
                                                <td><?= $row['contact_no'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= $row['address'] ?></td>
                                                <td><?= $row['bank_name'] ?></td>
                                                <td><?= $row['account_no'] ?></td>
                                                <td><?= $row['account_branch'] ?></td>
                                                <td><?= $row['ifcs_code'] ?></td>
                                                <td><?= $row['gst'] ?></td>
                                                <td><?= $row['pan'] ?></td>
                                                <td><?= $row['term_cond'] ?></td>
                                                <td><?php if ($row['comp_logo'] != '') { ?>
                                                        <img src="<?= $imgpath . $row['comp_logo']; ?>" width="80">
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?= $pagename . "?" . $tblpkey . "=" . $row[$tblpkey]; ?>"
                                                        class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <!-- Content close-->
</body>



<!-- script tag -->

<?php include('component/script.php'); ?>

<!-- script tag -->

<script>
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



</html>