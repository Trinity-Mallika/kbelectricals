<?php
include("../adminsession.php");
$title = "Scheme Entry";
$pagename = "scheme_entry.php";
$module = "Scheme Entry";
$submodule = "Scheme Entry";
$btn_name = "Save";
$tblname = "scheme_entry";
$tblpkey = "scheme_id";
$keyvalue = (isset($_GET["scheme_id"])) ? $obj->test_input($_GET["scheme_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$imgpath = "uploaded/scheme_image/";
$short_name = "";
$res = $obj->select_record("company_setting", ["company_id" => $companyid]);

if (!empty($res) && isset($res['short_name'])) {
    $short_name = $res['short_name'];
}

if (isset($_POST['submit'])) {
    $keyvalue = $obj->test_input($_POST['scheme_id']);
    $scheme_name = $obj->test_input($_POST['scheme_name']);
    $from_date = ($_POST['from_date']);
    $todate = ($_POST['todate']);
    $imgname = $_FILES['imgname'];
    $scheme_type = $obj->test_input($_POST['scheme_type']);
    $terms_conds = $obj->test_input($_POST['terms_conds']);
    $form_data = array(
        // "company_id" => $company_id,
        'scheme_name' => $scheme_name,
        'from_date' => $from_date,
        'todate' => $todate,
        'scheme_type' => $scheme_type,
        'terms_conds' => $terms_conds,
        "createdby" => $loginid,
        "companyid" => $companyid,
        "ipaddress" => $ipaddress,
    );

    if ($keyvalue == 0) {
        $form_data["createdate"] = $createdate;
        $lastid = $obj->insert_record_lastid($tblname, $form_data);
        $obj->update_record('scheme_details', ['scheme_id' => 0, 'companyid' => $companyid, "createdby" => $loginid], ['scheme_id' => $lastid]);
        if (!empty($_FILES["imgname"]['name'])) {
            $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);
            $obj->update_record($tblname, [$tblpkey => $lastid], ['imgname' => $filename]);
        }
        $action = 1;
        $process = "Insert";
        echo "<script>location='$pagename?action=$action'</script>";
    } else {
        $form_data["lastupdated"] = $createdate;
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        if (!empty($_FILES["imgname"]['name'])) {

            $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);

            if ($filename != "") {

                if ($keyvalue != 0) {

                    $old = $obj->getvalfield($tblname, "imgname", "scheme_id='$keyvalue'");
                    if ($old != "") {
                        @unlink($imgpath . $old);
                    }
                }
                $obj->update_record($tblname, $where, ['imgname' => $filename]);
            }
        }
        $action = 2;
        $process = "Update";
    }

    echo "<script>location='$pagename?action=$action'</script>";
}

if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);

    $scheme_name  =  $sqledit['scheme_name'];
    $from_date  =  $sqledit['from_date'];
    $todate  =  $sqledit['todate'];
    $imgname  =  $sqledit['imgname'];
    $terms_conds = $sqledit['terms_conds'];
    $scheme_type  =  $sqledit['scheme_type'];
} else {
    $scheme_name  =  "";
    $from_date = date('Y-m-d');
    $todate    = date('Y-m-d', strtotime('+1 month'));
    $imgname  =   "";
    $terms_conds = "";
    $scheme_type = "";
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
            <form action="" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="mt-2">
                            <legend><?php echo $title ?></legend>
                            <?php include('component/alert.php'); ?>
                            <div class="card">
                                <div class="card-header text-white">
                                    <?php echo $module ?>
                                    <a href="scheme_list.php" class="btn btn-sm btn-warning float-end">Scheme List</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <input type="hidden" name="scheme_id" value="<?php echo $keyvalue; ?>">

                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Scheme Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="scheme_name" id="scheme_name" placeholder="Scheme Name" value="<?php echo $scheme_name; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Scheme Image <span class="text-danger fw-bold"></span></label></strong>
                                            <input type="file" class="form-control form-control-sm" name="imgname" id="imgname" accept="image/*"
                                                autocomplete="off">
                                            <?php
                                            if ($imgname != "") { ?>

                                                <a href="<?php echo $imgpath . $imgname ?>" class="btn btn-sm btn-primary" target="_blank">View</a>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no"> From Date <span class="text-danger fw-bold">*</span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="from_date" id="from_date" placeholder="Owner Mobile No." value="<?php echo $from_date; ?>" autocomplete="off">

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">To Date <span class="text-danger fw-bold">*</span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="todate" id="todate" placeholder="Office Mobile No." value="<?php echo $todate; ?>" autocomplete="off">

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong>
                                                <label>Scheme Type <span class="text-danger fw-bold">*</span></label>
                                            </strong>

                                            <div class="d-flex align-items-center mt-2">

                                                <div class="form-check me-3">
                                                    <input class="form-check-input scheme_type"
                                                        type="radio"
                                                        name="scheme_type"
                                                        id="qty_wise"
                                                        value="qty_wise"
                                                        <?php if ($scheme_type == 'qty_wise' || $scheme_type == '') echo 'checked'; ?>>

                                                    <label class="form-check-label" for="qty_wise">
                                                        QTY. Wise
                                                    </label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input scheme_type"
                                                        type="radio"
                                                        name="scheme_type"
                                                        id="amt_wise"
                                                        value="amt_wise"
                                                        <?php if ($scheme_type == 'amt_wise') echo 'checked'; ?>>

                                                    <label class="form-check-label" for="amt_wise">
                                                        Amt. Wise
                                                    </label>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-9 mb-2">
                                            <strong><label>Terms & Conditions</label></strong>
                                            <textarea class="form-control" name="terms_conds"><?= $terms_conds ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-lg-12 mt-4">
                        <div class="card">
                            <div class="card-header text-white">
                                Product Entry
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <strong> <label for="class">Product Name<span class="text-danger fw-bold">*</span> </label></strong>
                                        <select name="product_id" id="product_id" class="chosen-select  form-control form-control-sm">
                                            <option value="">--Select Product Name--</option>
                                            <?php
                                            $sql = $obj->executequery("select * from product_master order by product_id DESC ");
                                            foreach ($sql as $key) { ?>
                                                <option value="<?php echo $key['product_id'] ?>"><?php echo $key['product_name'] ?></option>

                                            <?php } ?>
                                        </select>

                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong> <label for="qty" id="qty_label">QTY<span class="text-danger fw-bold"></span></label> </strong>
                                        <input type="text" class="form-control form-control-sm" name="qty" id="qty" value="" autocomplete="off">

                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong> <label for="mobile">Scheme Product<span class="text-danger fw-bold"></span></label></strong>
                                        <textarea class="form-control form-control-sm" name="output" id="output" placeholder="Scheme Product" autocomplete="off"></textarea>
                                    </div>

                                    <input type="hidden" id="scheme_details_id" value="0">
                                    <div class="col-md-2 mt-4 ">
                                        <input type="button" id="add_btn" class="btn btn-theme btn-sm" onclick="add_product()" value="Add">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-4">
                        <div class="card">
                            <div class="card-header text-white">
                                <?php echo $submodule; ?>
                            </div>
                            <div class="card-body" id="fetch_data">

                            </div>

                        </div>


                    </div>
                </div>
            </form>

        </div>
        <!-- Content Close-->
    </div>

</body>

<!-- Script tags -->
<?php include('component/script.php'); ?>
<script>
    $(document).ready(function() {
        $(".chosen-select").chosen();
        fetch_data('<?php echo $keyvalue ?>');
    });


    function delete_record(id) {
        // alert(id);
        jQuery.ajax({
            type: 'POST',
            url: 'ajax/delete_master.php',
            data: {
                id: id,
                tblname: 'scheme_details',
                tblpkey: 'scheme_details_id',
            },
            dataType: 'html',
            success: function(data) {
                // alert(data);
                fetch_data('<?php echo $keyvalue ?>');
            }
        });

    }

    function fetch_data(scheme_id) {
        let company_id = '<?= $companyid; ?>';

        jQuery.ajax({
            type: 'POST',
            url: 'fetch_scheme_details.php',
            data: {
                scheme_id: scheme_id,
            },
            dataType: 'html',
            success: function(data) {
                // alert(data);
                document.getElementById("fetch_data").innerHTML = data;
            }
        });

    }

    function EditProduct(product_id, qty, output, scheme_details_id) {

        $('#product_id').val(product_id).trigger('chosen:updated');
        $('#qty').val(qty);
        $('#output').val(output);

        $('#scheme_details_id').val(scheme_details_id);
        $('#add_btn').val('Update');

    }



    function add_product() {

        let product_id = document.getElementById('product_id').value;
        let qty = document.getElementById('qty').value;
        let scheme_details_id = document.getElementById('scheme_details_id').value;
        let scheme_type = document.querySelector('input[name="scheme_type"]:checked').value;
        let output = document.getElementById('output').value.trim();
        let scheme_id = '<?php echo $keyvalue ?>';
        let company_id = '<?= $companyid; ?>';
        if (product_id == '') {
            alert('Please select Product Name');
            return false;
        }

        if (qty == '') {
            alert('Please add Qty');
            return false;
        }

        if (output == '') {
            alert('Please Scheme Product');
            return false;
        }

        jQuery.ajax({
            type: 'POST',
            url: 'add_scheme_product.php',
            data: {
                product_id: product_id,
                qty: qty,
                scheme_details_id: scheme_details_id,
                output: output,
                scheme_type: scheme_type,

            },
            dataType: 'html',
            success: function(data) {
                // alert(data);

                if (data == 1 || data == 2) {
                    fetch_data(scheme_id);
                } else if (data == 3) {
                    alert('This product already added. Please update the existing product.');
                    return;
                }
                $('#product_id').val('').trigger('chosen:updated'); // ❌ no change trigger
                $('#qty').val("");
                $('#add_btn').val('Add');
                $('#output').val('');
            }
        });
    }
</script>
<script>
    function changeQtyLabel() {

        let scheme_type = document.querySelector('input[name="scheme_type"]:checked').value;

        if (scheme_type == 'qty_wise') {

            document.getElementById('qty_label').innerHTML =
                'QTY <span class="text-danger fw-bold"></span>';

        } else {

            document.getElementById('qty_label').innerHTML =
                'Amount <span class="text-danger fw-bold"></span>';

        }
    }

    // page load par
    changeQtyLabel();

    // radio change par
    document.querySelectorAll('.scheme_type').forEach(function(radio) {

        radio.addEventListener('change', changeQtyLabel);

    });
</script>

</html>