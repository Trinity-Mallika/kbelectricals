<?php
include("../adminsession.php");
$title = 'Document Master';
$module = "Document Master";
$submodule = "Document Master List";
$pagename = "document_master.php";
$tblname = "document_master";
$tblpkey = "document_id";
$keyvalue = (isset($_GET["document_id"])) ? $obj->test_input($_GET["document_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$imgpath = "uploaded/documents/";

if (isset($_POST['submit'])) {
    $document_name = $obj->test_input($_POST['document_name']);

    $dup = $obj->getvalfield(
        $tblname,
        "count(*)",
        "document_name='$document_name' AND document_id!='$keyvalue'"
    );

    if ($dup > 0) {
        $action = 4;
    } else {

        $form_data = array(
            "document_name" => $document_name,
            "createdby" => $loginid,
            "createdate" => $createdate,
            "ipaddress" => $ipaddress
        );

        if ($keyvalue == 0) {

            $lastid =  $obj->insert_record_lastid(
                $tblname,
                $form_data
            );
            if (!empty($_FILES["imgname"]['name'])) {
                $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);
                $obj->update_record($tblname, [$tblpkey => $lastid], ['imgname' => $filename]);
            }

            $action = 1;
        } else {

            unset($form_data['createdate']);

            $form_data['lastupdated'] = $createdate;

            $where = array(

                $tblpkey => $keyvalue

            );

            $obj->update_record(

                $tblname,

                $where,

                $form_data

            );
            if (!empty($_FILES["imgname"]['name'])) {

                $filename = $obj->uploadImage($imgpath, $_FILES["imgname"]);

                if ($filename != "") {

                    if ($keyvalue != 0) {

                        $old = $obj->getvalfield($tblname, "imgname", "document_id='$keyvalue'");
                        if ($old != "") {
                            @unlink($imgpath . $old);
                        }
                    }
                    $obj->update_record($tblname, $where, ['imgname' => $filename]);
                }
            }

            $action = 2;
        }
    }

    echo "<script>

    location='$pagename?action=$action';

    </script>";
}
if ($keyvalue > 0) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $document_name = $sqledit['document_name'];
    $imgname = $sqledit['imgname'];
    $img = ",imgname";
} else {
    $btn_name = "Save";
    $document_name = "";
    $imgname = "";
    $img = "";
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
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-header text-white"><?php echo $module; ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong><label for="cat_name" class="form-label"> Document Name <span class="text-danger">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" placeholder="Enter Document Name" id="document_name" name="document_name" value="<?php echo $document_name; ?>" autocomplete="off" />
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong><label for="cat_name" class="form-label">Upload File <span class="text-danger">*</span></label></strong>
                                            <input
                                                type="file"
                                                class="form-control form-control-sm"
                                                name="imgname"
                                                id="imgname"
                                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv">
                                            <?php
                                            if ($imgname != "") {

                                                $ext = strtolower(pathinfo($imgname, PATHINFO_EXTENSION));

                                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                            ?>
                                                    <br>
                                                    <img src="<?php echo $imgpath . $imgname; ?>" width="80">
                                                <?php
                                                } else {
                                                ?>
                                                    <br>
                                                    <a href="<?php echo $imgpath . $imgname; ?>" target="_blank" class="btn btn-info btn-sm">
                                                        View Document
                                                    </a>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('document_name<?= $img; ?>');">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm">Reset</a>
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
                                        <th>Document Name</th>
                                        <th>Uploaded File </th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("SELECT * FROM $tblname  ORDER BY document_id desc");
                                        foreach ($sql as $key) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $i++; ?></td>
                                                <td><?php echo ucfirst($key['document_name']); ?> </td>
                                                <td><?php
                                                    if ($key['imgname'] != "") {
                                                        $ext = strtolower(pathinfo($key['imgname'], PATHINFO_EXTENSION));

                                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                            echo '<img src="' . $imgpath . $key['imgname'] . '" width="80">';
                                                        } else {
                                                            echo '<a href="' . $imgpath . $key['imgname'] . '" target="_blank" class="btn btn-sm btn-primary">
                View Document
              </a>';
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['document_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo $key['document_id']; ?>','<?php echo $key['imgname']; ?>');">
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
        //$(".chosen-select").chosen();
        $("#example").DataTable();
    });

    function funDel(id, imgname) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        pagename = '<?php echo $pagename; ?>';
        submodule = '<?php echo $submodule; ?>';
        module = '<?php echo $module; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master_img.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close
</script>

</html>