<?php
include("appsession.php");
include("firebase.php");
$pagename = "new-order.php";
$module = "New Order";
$submodule = "New Order";
$btn_name = "Save";
$title = "New Order";
$keyvalue = 0;
$tblname = "new_orders";
$tblpkey = "new_orders_id";
$imgpath = "uploads/";

if (isset($_GET['new_orders_id']))
    $keyvalue = $_GET['new_orders_id'];
else
    $keyvalue = 0;
// get value for alert
if (isset($_GET['action'])) {
    $action = $obj->test_input($_GET['action']);
} else {
    $action = "";
}

if (isset($_POST['submit'])) {

    $keyvalue = $obj->test_input($_POST['new_orders_id']);
    $kaarigar_id = $obj->test_input($_POST['kaarigar_id']);
    $reg_id = $obj->getvalfield("user", "userid", "kaarigar_id='$kaarigar_id'");

    $description = $obj->test_input($_POST['description']);
    $photo =  $_FILES['photo'];


    if ($keyvalue == 0) {
        $form_data = array('kaarigar_id' => $kaarigar_id, 'description' => $description,  'ipaddress' => $ipaddress, 'createdate' => $createdate, 'createdby' => $loginid);
        $lastid = $obj->insert_record_lastid($tblname, $form_data);
        $res1 = $obj->executequery("select token from user_token where userid='$reg_id' ");
        foreach ($res1 as $key) {
            sendNotification(
                $key['token'],
                "⚡ New Order Assigned",
                $description,
                "https://trinitysoftwares.in/myprojects/bhorawat-app/my-order.php?status=Pending"
            );
        }
        $imageFileType = strtolower(pathinfo($_FILES["photo"]['name'], PATHINFO_EXTENSION));
        if ($imageFileType == 'png' || $imageFileType == 'jpg' || $imageFileType == 'jpeg') {
            $filename = $obj->uploadImageCompress($imgpath, $_FILES["photo"]);
            $form_data1 = array("photo" => $filename);
            $where1 = array($tblpkey => $lastid);
            $obj->update_record($tblname, $where1, $form_data1);
        }

        $action = 1;
        $process = "insert";
    } else {
        //update
        $form_data = array('kaarigar_id' => $kaarigar_id, 'description' => $description, 'ipaddress' => $ipaddress, 'lastupdated' => $createdate);
        $where = array($tblpkey => $keyvalue);
        $obj->update_record($tblname, $where, $form_data);
        $imageFileType = strtolower(pathinfo($_FILES["photo"]['name'], PATHINFO_EXTENSION));
        if ($imageFileType == 'png' || $imageFileType == 'jpg' || $imageFileType == 'jpeg') {
            $old = $obj->getvalfield($tblname, "photo", "new_orders_id='$keyvalue'");
            // echo $old;
            // die;
            if ($old != "") {
                // echo "rteur";
                @unlink("$imgpath" . $old);
            }
            $filename = $obj->uploadImageCompress($imgpath, $_FILES["photo"]);
            $form_data1 = array("photo" => $filename);
            $where1 = array($tblpkey => $keyvalue);
            $obj->update_record($tblname, $where1, $form_data1);
        }
        $action = 2;
        $process = "updated";
    }
    // die;
    echo "<script>location='$pagename?action=$action'</script>";
    //}
}
if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);

    $kaarigar_id = $sqledit['kaarigar_id'];
    $description = $sqledit['description'];
    $photo = $sqledit['photo'];
    $img_check = ",";
} else {
    $photo = "";
    $description = "";
    $kaarigar_id = "";
    $img_check = ",photo";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Bhorawat</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>

</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="card border-0 shadow-lg mb-3">
                <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <div class="mb-3">
                        <label for="" class="form-label">Select Karigar<span class="text-danger fw-bold">*</span></label>
                        <select name="kaarigar_id" id="kaarigar_id" class="form-select shadow-sm">
                            <option value="">Select Karigar</option>

                            <?php
                            // include("config.php");
                            $res = $obj->executequery("select * from kaarigar order by kaarigar_id desc");
                            foreach ($res as $key) {


                            ?>
                                <option value="<?php echo $key['kaarigar_id']; ?>">
                                    <?php echo $key['kaarigar_name']; ?>
                                </option>
                            <?php } ?>

                        </select>
                        <script>
                            document.getElementById('kaarigar_id').value = '<?= $kaarigar_id ?>';
                        </script>
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Image<span class="text-danger fw-bold">*</span></label>
                        <input type="file" class="form-control shadow-sm" id="photo" name="photo" accept="image/*">
                        <?php if (!empty($photo)) { ?>
                            <div class="mt-2">
                                <img src="uploads/<?= $photo ?>" width="120" class="rounded">
                            </div>
                        <?php } ?>
                        <input type="hidden" id="old_photo" value="<?= $photo ?>">
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Item description<span class="text-danger fw-bold">*</span></label>
                        <textarea name="description" id="description" rows="8" class="form-control shadow-sm"><?= $description ?></textarea>
                    </div>
                    <div class="d-grid mt-4">
                        <input type="submit" name="submit" class="btn" value="<?php echo $btn_name  ?>">

                    </div>
                    <input type="hidden" name="new_orders_id" value="<?= $keyvalue ?>">
                </form>
            </div>

        </div>
        <div id="loader" style="
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;
    height:100%;
    background:rgba(255,255,255,0.7);
    z-index:9999;
    text-align:center;
    padding-top:200px;
    font-size:20px;">
            Loading...
        </div>
    </section>

    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
</body>




<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function validateForm() {

        let kaarigar_id = document.getElementById("kaarigar_id").value.trim();
        let description = document.getElementById("description").value.trim();
        let photo = document.getElementById("photo").value;

        let order_id = document.querySelector('[name="new_orders_id"]').value;
        let old_photo = document.getElementById("old_photo").value;

        let missing = [];

        // 🔹 Required fields
        if (kaarigar_id === "") {
            missing.push("Karigar");
        }

        if (description === "") {
            missing.push("Description");
        }

        // 🔹 Insert case (new record)
        if (order_id == 0 && photo === "") {
            missing.push("Photo");
        }

        // 🔹 Edit case (extra safety)
        if (order_id != 0 && photo === "" && old_photo === "") {
            missing.push("Photo");
        }

        // 🔹 Show alert
        if (missing.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields Missing',
                text: missing.join(", ") + " required !!"
            });
            return false; // ❌ stop form
        }

        // 🔹 Loader (optional)
        let btn = document.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = "Saving...";
        }

        return true; // ✅ allow submit
    }
    document.querySelector("form").addEventListener("submit", function() {

        document.getElementById("loader").style.display = "block";

    });
</script>

</html>