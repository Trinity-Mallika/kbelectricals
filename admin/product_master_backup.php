<?php

include("../adminsession.php");

$title = "Product Master";
$pagename = "product_master.php";
$module = "Product Master";
$submodule = "Product Master";
$btn_name = "Save";
$tblname = "product_master";
$tblpkey = "product_id";
$keyvalue = (isset($_GET["product_id"])) ? $obj->test_input($_GET["product_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

if (isset($_POST['submit'])) {
    $brand_id = $obj->test_input($_POST['brand_id']);
    $category_id = $obj->test_input($_POST['category_id']);
    $product_name = $obj->test_input($_POST['product_name']);
    // $unit_id = isset($_POST['unit_id']) ? implode(",", $_POST['unit_id']) : '';
    $unit_id = isset($_POST['unit_id']) ? $obj->test_input($_POST['unit_id']) : '';
    $rate = $obj->test_input($_POST['rate']);
    if ($keyvalue == 0) {
        $form_data = array('product_name' => $product_name, 'brand_id' => $brand_id, 'category_id' => $category_id, 'unit_id' => $unit_id, 'rate' => $rate,  'ipaddress' => $ipaddress, 'createdate' => $createdate, 'createdby' => $loginid, "companyid" => $companyid);
        $obj->insert_record($tblname, $form_data);
        $action = 1;
        $process = "insert";
    } else {
        //update
        $form_data = array('product_name' => $product_name, 'brand_id' => $brand_id, 'category_id' => $category_id, 'unit_id' => $unit_id, 'rate' => $rate,  'ipaddress' => $ipaddress, 'lastupdated' => $createdate, "companyid" => $companyid);
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $action = 2;
        $process = "updated";
    }
    echo "<script>location='$pagename?action=$action'</script>";
}

if (isset($_GET[$tblpkey])) {

    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $product_name = $sqledit['product_name'];
    $category_id = $sqledit['category_id'];
    $brand_id = $sqledit['brand_id'];
    $unit_id = $sqledit['unit_id'];
    $rate = $sqledit['rate'];
} else {
    $unit_id = "";
    $brand_id = "";
    $category_id = "";
    $product_name = "";
    $rate = "";
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
                                            <strong><label>Brand Name <span class="text-danger">*</span></label></strong>

                                            <select class="form-control form-control-sm chosen-select" name="brand_id" id="brand_id">
                                                <option value="">--Select Brand--</option>

                                                <?php
                                                $brands = $obj->executequery("SELECT * FROM category_master WHERE type='brand'");
                                                foreach ($brands as $b) {
                                                ?>
                                                    <option value="<?= $b['cat_id']; ?>"
                                                        <?= ($b['cat_id'] == $brand_id) ? 'selected' : '' ?>>
                                                        <?= $b['cat_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">

                                            <strong> <label for="images">Category Name <span class="text-danger fw-bold">*</span></label></strong>

                                            <!-- <select type="text" class="form-control form-control-sm chosen-select" name="category_id" id="category_id">
                                                <option value="">--Select Category--</option>
                                                <?php

                                                $sql = $obj->executequery("select * from category_master where type='category' order by cat_id DESC ");

                                                foreach ($sql as $key) {
                                                ?> <option value="<?php echo $key['cat_id'] ?>"><?php echo $key['cat_name'] ?></option> <?php } ?>
                                            </select> -->
                                            <select class="form-control form-control-sm chosen-select" name="category_id" id="category_id">
                                                <option value="">--Select Category--</option>
                                            </select>

                                            <script>
                                                document.getElementById('category_id').value = '<?= $category_id ?>'
                                            </script>

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="images">Product Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm " name="product_name" id="product_name" value="<?php echo $product_name ?>" placeholder="Enter Product Name">
                                        </div>


                                        <div class="col-md-3 mb-2">

                                            <strong> <label for="images">Unit Name <span class="text-danger fw-bold">*</span></label></strong>

                                            <select class="form-control form-control-sm chosen-select"
                                                name="unit_id"
                                                id="unit_id"
                                                single>

                                                <?php
                                                $units = $obj->executequery("select * from category_master where type='unit' order by cat_id DESC");

                                                // edit ke liye explode
                                                // $selected_units = explode(',', $unit_id);

                                                // foreach ($units as $u) {
                                                //     $selected = in_array($u['cat_id'], $selected_units) ? 'selected' : '';

                                                foreach ($units as $u) {
                                                    $selected = ($u['cat_id'] == $unit_id) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $u['cat_id']; ?>" <?php echo $selected; ?>>
                                                        <?php echo $u['cat_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>

                                            <script>
                                                document.getElementById('unit_id').value = '<?= $unit_id ?>'
                                            </script>

                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="images">Rate <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="number" class="form-control form-control-sm " name="rate" id="rate" value="<?php echo $rate ?>" placeholder="Enter Rate">
                                        </div>


                                        <div class="col-md-2 mt-4">
                                            <input type="submit" onclick="return checkinputmaster('brand_id,category_id,product_name,unit_id,rate')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">
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
                                        <th>Brand Name</th>
                                        <th>Category Name</th>
                                        <th>Product Name</th>
                                        <th>Unit Name</th>
                                        <th>Rate</th>
                                        <th>Action</th>

                                    </thead>

                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("select * from product_master order by product_id DESC ");
                                        foreach ($sql as $key) {
                                            $brand_name = $obj->getvalfield("category_master", "cat_name", "cat_id='{$key['brand_id']}'");
                                            $category_name = $obj->getvalfield("category_master", "cat_name", "cat_id='{$key['category_id']}'");

                                            $unit_ids = explode(',', $key['unit_id']);

                                            $unit_names = [];

                                            foreach ($unit_ids as $uid) {
                                                $unit_names[] = $obj->getvalfield("category_master", "cat_name", "cat_id='$uid'");
                                            }

                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo $brand_name ?></td>
                                                <td><?php echo $category_name ?></td>
                                                <td><?php echo $key['product_name']; ?></td>
                                                <td><?= implode(", ", $unit_names); ?></td>
                                                <td><?php echo $key['rate']; ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['product_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo  $key['product_id']; ?>');"><i class="bi bi-trash3-fill"></i></button>
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
    $("#brand_id").change(function() {
        var brand_id = $(this).val();

        if (brand_id != "") {
            $.ajax({
                url: "get_category.php",
                type: "POST",
                data: {
                    brand_id: brand_id
                },
                success: function(data) {
                    $("#category_id").html(data);
                    $("#category_id").trigger("chosen:updated");
                }
            });
        } else {
            $("#category_id").html("<option value=''>Select Category</option>");
            $("#category_id").trigger("chosen:updated");
        }
    });
    $(document).ready(function() {

        var brand_id = $("#brand_id").val();
        var selected_category = "<?= $category_id ?>";

        if (brand_id != "") {
            $.ajax({
                url: "get_category.php",
                type: "POST",
                data: {
                    brand_id: brand_id,
                    category_id: selected_category
                },
                success: function(data) {
                    $("#category_id").html(data);
                    $("#category_id").trigger("chosen:updated");
                }
            });
        }

    });

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