<?php
include("../adminsession.php");
// session_start();
// print_r($_SESSION);
$title = "Category Master";
$pagename = "category_master.php";
$module = "Category Master";
$submodule = "Category Master List";
$btn_name = "Save";
$tblname = "category_master";
$tblpkey = "cat_id";
$keyvalue = (isset($_GET["cat_id"])) ? $obj->test_input($_GET["cat_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$type = "category";

$brand_list = $obj->executequery("SELECT cat_id, cat_name FROM category_master WHERE type='brand' ORDER BY cat_name ASC");

if (isset($_POST['submit'])) {
    $cat_name = $obj->test_input($_POST['cat_name']);
    $brand_id = $obj->test_input($_POST['brand_id']);
    $dup = $obj->getvalfield("$tblname", "count(*)", "cat_name= '$cat_name' and type='$type' AND  $tblpkey != '$keyvalue'");

    if ($dup > 0) {
        $action = 4;
        echo "<script>location='$pagename?action=$action'</script>";
    } else {

        $form_data = array(
            "cat_name" => $cat_name,
            "brand_id" => $brand_id,
            "type" => $type,
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
    $cat_name = $sqledit['cat_name'];
    $brand_id = $sqledit['brand_id'];
} else {
    $cat_name = "";
    $brand_id = "";
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
                                            <strong><label class="form-label">Select Brand <span class="text-danger">*</span></label></strong>
                                            <select name="brand_id" id="brand_id" class="chosen-select form-control form-control-sm">
                                                <option value="">Select Brand</option>
                                                <?php foreach ($brand_list as $brand) { ?>
                                                    <option value="<?php echo $brand['cat_id']; ?>"
                                                        <?php if ($brand['cat_id'] == $brand_id) echo "selected"; ?>>
                                                        <?php echo $brand['cat_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong><label for="cat_name" class="form-label"> Category Name <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" placeholder="Enter Category Name" id="cat_name" name="cat_name" value="<?php echo $cat_name ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md mt-2 "><br />
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('brand_id,cat_name')">
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
                                        <th>Brand Name</th>
                                        <th>Category Name</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("
                                            SELECT c.*, b.cat_name as brand_name
                                            FROM category_master c
                                            LEFT JOIN category_master b ON c.brand_id = b.cat_id
                                            WHERE c.type='category'
                                            ORDER BY c.cat_id DESC
                                        ");
                                        // $sql = $obj->executequery("SELECT * FROM $tblname where type='$type' ORDER BY cat_id desc");
                                        foreach ($sql as $key) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo ucfirst($key['brand_name']); ?></td>
                                                <td><?php echo ucfirst($key['cat_name']); ?> </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['cat_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo $key['cat_id']; ?>');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
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