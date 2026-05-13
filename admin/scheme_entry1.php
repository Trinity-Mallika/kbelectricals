<?php include("../adminsession.php");
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


if (isset($_POST['submit'])) {
    $scheme_name = $obj->test_input($_POST['scheme_name']);
    $from_date = ($_POST['from_date']);
    $todate = ($_POST['todate']);
    $imgname = $_FILES['imgname'];
    $product_id = $obj->test_input($_POST['product_id']);
    $qty = $obj->test_input($_POST['qty']);
    $output = $obj->test_input($_POST['output']);
    $scheme_type = $obj->test_input($_POST['scheme_type']);

    //check Duplicate
    $count = $obj->getvalfield("$tblname", "count(*)", "scheme_name='$scheme_name' and $tblpkey!='$keyvalue'");

    if ($count > 0) {
        $action = 4;
        $process = "Duplicate";
    } else //insert
    {
        if ($keyvalue == 0) {

            $form_data = array(
                'scheme_name' => $scheme_name,
                'from_date' => $from_date,
                'todate' => $todate,
                'product_id' => $product_id,
                'qty' => $qty,
                'output' => $output,
                'scheme_type' => $scheme_type,

                'ipaddress' => $ipaddress,
                "companyid" => $companyid,
                'createdate' => $createdate
            );
            $lastid = $obj->insert_record_lastid($tblname, $form_data);
            if (!empty($_FILES["imgname"]['name'])) {
                $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);
                $obj->update_record($tblname, [$tblpkey => $lastid], ['imgname' => $filename]);
            }

            $action = 1;
            $process = "inserted";
        } else {

            //update
            $form_data = array(
                'scheme_name' => $scheme_name,
                'from_date' => $from_date,
                'todate' => $todate,
                'product_id' => $product_id,
                'qty' => $qty,
                'output' => $output,
                'scheme_type' => $scheme_type,
                'ipaddress' => $ipaddress,
                "companyid" => $companyid,
                'lastupdated' => $createdate
            );
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
                    $obj->update_record($tblname, [$tblpkey => $keyvalue], ['imgname' => $filename]);
                }
            }
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

    $scheme_name  =  $sqledit['scheme_name'];

    $from_date  =  $sqledit['from_date'];

    $todate  =  $sqledit['todate'];

    $imgname  =  $sqledit['imgname'];

    $product_id  =  $sqledit['product_id'];

    $qty  =  $sqledit['qty'];
    $output  =  $sqledit['output'];

    $scheme_type  =  $sqledit['scheme_type'];
} else {

    $scheme_name  =  "";
    $from_date  =   "";
    $todate  =   "";
    $imgname  =   "";
    $product_id  =   "";
    $qty  =   "";
    $output  =   "";
    $scheme_type = "";
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

                        <legend><?= $module ?></legend>

                        <?php include('component/alert.php'); ?>

                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="card">

                                <div class="card-header text-white">

                                    <?= $module ?>

                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Scheme Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="scheme_name" id="scheme_name" placeholder="Scheme Name" value="<?php echo $scheme_name; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Scheme Image <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="file" class="form-control form-control-sm" name="imgname" id="imgname" autocomplete="off">
                                            <?php
                                            if ($imgname != "") { ?>
                                                <img src="<?php echo $imgpath . $imgname ?>" alt="" style="width: 100px;">
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no"> From Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="from_date" id="from_date" placeholder="Owner Mobile No." value="<?php echo $from_date; ?>" autocomplete="off">

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">To Date <span class="text-danger fw-bold"></span></label> </strong>
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
                                            <script>
                                                document.getElementById('product_id').value = '<?php echo $product_id; ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="qty" id="qty_label">QTY<span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="text" class="form-control form-control-sm" name="qty" id="qty" value="<?php echo $qty; ?>" autocomplete="off">

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile">Scheme Product<span class="text-danger fw-bold"></span></label></strong>
                                            <textarea class="form-control form-control-sm" name="output" id="output" placeholder="Scheme Product" autocomplete="off"><?php echo $output; ?></textarea>
                                        </div>





                                        <div class="col-md-4 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onclick="return checkinputmaster('scheme_name,from_date,todate,scheme_type,product_id,qty,output');">
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
                                        <th>Scheme Name</th>
                                        <th>Image Name</th>
                                        <th>From date</th>
                                        <th>To Date</th>
                                        <th>Product Name</th>
                                        <th>Unit</th>
                                        <th>Scheme Product</th>

                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $sql_get = $obj->executequery("select * from scheme_entry  order by scheme_id desc");
                                        foreach ($sql_get as $row_get) {
                                            $product_name = $obj->getvalfield("product_master", "product_name", "product_id='{$key['product_id']}'");

                                        ?>
                                            <tr>
                                                <td> <?php echo $slno++; ?></td>
                                                <td><?php echo $row_get['scheme_name']; ?></td>
                                                <td>
                                                    <a href="<?php echo $imgpath . $row_get['imgname'] ?>"><img src="<?php echo $imgpath . $row_get['imgname'] ?>" alt="" style="width: 100px;"></a>
                                                </td>
                                                <td><?php echo $row_get['from_date']; ?></td>
                                                <td><?php echo $row_get['todate']; ?></td>
                                                <td><?php echo $product_name; ?></td>
                                                <td>
                                                    <?php
                                                    echo $row_get['qty'];

                                                    if ($row_get['scheme_type'] == 'qty_wise') {
                                                        echo " Qty";
                                                    } else {
                                                        echo " Rs";
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $row_get['output']; ?></td>

                                                <td class="text-center">
                                                    <a href="?scheme_id=<?php echo  $row_get['scheme_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel(<?php echo  $row_get['scheme_id']; ?>);"><i class="bi bi-trash3-fill"></i></button>
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

        $("#mobile_no").keypress(function(e) {

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
    function changeQtyLabel() {

        let scheme_type = document.querySelector('input[name="scheme_type"]:checked').value;

        if (scheme_type == 'amt_wise') {

            document.getElementById('qty_label').innerHTML =
                'Amount <span class="text-danger fw-bold"></span>';

        } else {

            document.getElementById('qty_label').innerHTML =
                'QTY <span class="text-danger fw-bold"></span>';
        }
    }

    // page load
    changeQtyLabel();

    // radio change
    document.querySelectorAll('.scheme_type').forEach(function(el) {

        el.addEventListener('change', function() {
            changeQtyLabel();
        });

    });
</script>

</html>