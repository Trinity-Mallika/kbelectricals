<?php include("appsession.php");
$pagename = 'create-counter.php';
$title = 'Add Counters';
$tblname = 'account';
$tblpkey = 'account_id';
$btn_name = "Save";
$keyvalue = (isset($_GET["account_id"])) ? $obj->test_input($_GET["account_id"]) : 0;
$data = $obj->getRouteDashboardData($loginid, $companyid);
// $route_plan_id = $data['route_plan_id'];

$current_date = date('Y-m-d');



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $batch_no = $obj->test_input($_POST['route_planid']);
    $keyvalue = $obj->test_input($_POST['account_id']);
    $account_name = $obj->test_input($_POST['account_name']);
    $mobile_no = $obj->test_input($_POST['mobile_no']);
    $address = $obj->test_input($_POST['address']);
    $area_id = $obj->test_input($_POST['area_id']);
    $common_id = $obj->test_input($_POST['common_id']);
    $class = $obj->test_input($_POST['class']);
    $type = ($common_id == -1) ? "employee" : "customer";

    if ($account_name == "" || $mobile_no == "" || $common_id == "" || $class == "" || $area_id == "") {
        echo "error";
        exit;
    }
    $count = $obj->getvalfield($tblname, "count(*)", "account_name='$account_name' and area_id='$area_id' and account_id!='$keyvalue'");
    if ($count > 0) {
        echo "duplicate";
        exit;
    } else {
        $form_data = array(
            'account_name' => $account_name,
            'mobile_no' => $mobile_no,
            'address' => $address,
            'common_id' => $common_id,
            'area_id' => $area_id,
            'class' => $class,
            'status' => "inactive",
            'type' => $type,
            'status1' => 0,
            'createdby' => $loginid,
            'companyid' => $companyid,
            'ipaddress' => $ipaddress
        );


        if ($count > 0) {
            echo "duplicate";
            exit;
        } else {
            if ($keyvalue == 0) {
                $form_data['createdate'] = $createdate;
                $account_id = $obj->insert_record_lastid($tblname, $form_data);

                if ($account_id > 0) {

                    $batch_no = $obj->test_input($_POST['route_planid']);

                    // auto sequence
                    $sequence = $obj->getvalfield(
                        "route_counter",
                        "IFNULL(MAX(sequence),0)+1",
                        "batch_no='$batch_no'"
                    );

                    $obj->insert_record("route_counter", [
                        'batch_no' => $batch_no,
                        'account_id' => $account_id,
                        'sequence' => $sequence,
                        'createdate' => $createdate,
                        'ipaddress' => $ipaddress,
                        'companyid' => $companyid,
                        'createdby' => $loginid
                    ]);
                }
                echo "success";
                exit;
            } else {
                $form_data['lastupdated'] = $createdate;
                $obj->update_record($tblname, [$tblpkey => $keyvalue], $form_data);
                echo "updated";
                exit;
            }
        }
    }
}


if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $account_name  =  $sqledit['account_name'];
    $mobile_no  =  $sqledit['mobile_no'];
    $address  =  $sqledit['address'];
    $common_id  =  $sqledit['common_id'];
    $area_id  =  $sqledit['area_id'];
    $type  =  $sqledit['type'];
    $class  =  $sqledit['class'];
} else {
    $account_name = $mobile_no = $address = $area_id = "";
    $type = $class =  "";
    $common_id = $obj->getvalfield($tblname, "common_id", "1=1 order by $tblpkey desc");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>

</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="card border-0 shadow-lg mb-3">
                <form method="POST" id="dailyEntryForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="mb-0"> Counters</h4>
                        </div>
                        <div class="col-6 text-end ">
                            <a href="counter-list.php" class="btn btn-sm btn-primary">Counter List</a>
                        </div>
                        <div class="col-12 mb-2 mt-2">
                            <hr class="m-0">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Route Name<span class="text-danger fw-bold">*</span></label>
                            <select name="route_planid" id="route_planid" class="chosen-select form-control form-control-sm">
                                <option value="">--Select Route Name--</option>
                                <?php
                                 $sql = $obj->executequery("SELECT
    R.batch_no,
    R.route_name,
    GROUP_CONCAT(
        R.day_of_week
        ORDER BY FIELD(
            day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        )
        SEPARATOR ', '
    ) AS days
FROM route as R left join route_plan as RP on R.batch_no=RP.batch_no
WHERE R.companyid='$companyid' AND RP.sales_executive_id='$loginid'
GROUP BY R.batch_no, R.route_name
ORDER BY R.route_name ASC
");
                                foreach ($sql as $key) { ?>
                                    <option value="<?= $key['batch_no'] ?>"><?= $key['route_name'] ?> [<?= $key['days'] ?>]</option>
                                <?php } ?>
                            </select>
                            <script>
                                document.getElementById('route_planid').value = '<?php echo $route_plan_id; ?>';
                            </script>
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Counter Name <span class="text-danger fw-bold">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="account_name" name="account_name" placeholder="Enter Counter Name" value="<?php echo $account_name ?>">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Mobile Number <span class="text-danger fw-bold"></span></label>
                            <input type="text"
                                class="form-control shadow-sm"
                                id="mobile_no"
                                name="mobile_no"
                                placeholder="Enter Mobile Number"
                                value="<?php echo $mobile_no ?>"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Class <span class="text-danger fw-bold">*</span></label>
                            <select name="class" id="class" class="form-control form-control-sm">
                                <option value="">--Select Class--</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                            <script>
                                document.getElementById('class').value = '<?php echo $class; ?>';
                            </script>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Area <span class="text-danger fw-bold">*</span></label>
                            <select name="area_id" id="area_id" class="chosen-select  form-control form-control-sm">
                                <option value="">--Select Area--</option>
                                <?php
                                $sql = $obj->executequery("select area_id,area_name from area_master order by area_name asc ");
                                foreach ($sql as $key) {
                                ?>
                                    <option value="<?= $key['area_id'] ?>"><?= $key['area_name'] ?></option>
                                <?php } ?>
                            </select>
                            <script>
                                document.getElementById('area_id').value = '<?php echo $area_id; ?>';
                            </script>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label"> Counter Type</label>
                            <select name="common_id" id="common_id" class="chosen-select  form-control form-control-sm">
                                <option value="">--Select Counter Type--</option>
                                <?php
                                $sql = $obj->executequery("select common_id,common_name from common_master where type='acc_type' order by common_id asc ");
                                foreach ($sql as $key) {
                                ?>
                                    <option value="<?= $key['common_id'] ?>"><?= $key['common_name'] ?></option>
                                <?php } ?>
                                <option value="-1">Employee</option>
                            </select>
                            <script>
                                document.getElementById('common_id').value = '<?php echo $common_id; ?>';
                            </script>
                        </div>


                        <div class="col-lg-9 mb-2">
                            <label for="" class="form-label">Address</label>
                            <textarea class="form-control shadow-sm" id="address" name="address" placeholder="Enter Address"><?= $address; ?></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <input type="hidden" name="account_id" id="account_id" value="<?php echo $keyvalue ?>">
                            <input type="submit" name="submit" id="save_order_btn" class="btn btn-primary" value="<?php echo $btn_name ?>">
                        </div>
                    </div>

                </form>

            </div>

        </div>

    </section>
    <div id="loader">
        <div class="loader-spinner"></div>
    </div>
    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $(".chosen-select").chosen({
            width: "100%",
            search_contains: true
        });
    });


    $('#dailyEntryForm').on('submit', function(e) {
        e.preventDefault();
        let btn = document.getElementById('save_order_btn');

        if (btn) {
            btn.disabled = true;
            btn.value = "Processing...";
        }

        submitDailyEntryForm(btn);
    });

    function submitDailyEntryForm(btn) {
        if ($('#route_planid').val().trim() == '') {
            Swal.fire('Select a Route First');
            return enableBtn(btn);
        }
        if ($('#account_name').val().trim() == '') {
            Swal.fire('Enter Counter Name');
            $('#account_name').focus();
            return enableBtn(btn);
        }

        if ($('#class').val().trim() == '') {
            Swal.fire('Select a Class');
            $('#class').focus();
            return enableBtn(btn);
        }
        if ($('#area_id').val().trim() == '') {
            Swal.fire('Select an Area');
            $('#area_id').focus();
            return enableBtn(btn);
        }



        let formData = new FormData($('#dailyEntryForm')[0]);

        Swal.fire({
            title: 'Saving...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: 'create-counter.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function(response) {
                // alert(response);
                if (response.trim() == 'success') {

                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location = 'counter-list.php';
                    });

                } else if (response.trim() == 'updated') {

                    Swal.fire('Updated Successfully', '', 'success');
                    enableBtn(btn);
                    window.location = 'counter-list.php';


                } else if (response.trim() == 'duplicate') {

                    Swal.fire('Duplicate entry not allowed', '', 'warning');
                    enableBtn(btn);


                } else if (response.trim() == 'error') {

                    Swal.fire('Fill all required fields', '', 'warning');
                    enableBtn(btn);

                } else if (response.trim() == 'image_required') {

                    Swal.fire('Upload Image', '', 'warning');
                    enableBtn(btn);

                } else {
                    Swal.fire('Unexpected response: ' + response);
                    enableBtn(btn);
                }
            },

            error: function() {
                Swal.fire('Error saving data', '', 'error');
                enableBtn(btn);
            }
        });
    }

    function enableBtn(btn) {
        if (btn) {
            btn.disabled = false;
            btn.value = "Save";
        }
    }
</script>

</html>