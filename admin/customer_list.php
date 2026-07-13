<?php include("../adminsession.php");
$title = "Counters / Customer List";
$pagename = "customer_list.php";
$module = "Counters / Customer List";
$submodule = "Counter/Customer List";
$btn_name = "Save";
$tblname = "account";
$tblpkey = "account_id";
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$user_id = (isset($_GET["user_id"])) ? $obj->test_input($_GET["user_id"]) : "";
$common_id = (isset($_GET["common_id"])) ? $obj->test_input($_GET["common_id"]) : "";
$class = (isset($_GET["class"])) ? $obj->test_input($_GET["class"]) : "";
$account_name = (isset($_GET["account_name"])) ? $obj->test_input($_GET["account_name"]) : "";

$counterTypes = $obj->executequery("
    SELECT
        cm.common_name,
        COUNT(*) AS total
    FROM account a
    INNER JOIN common_master cm
        ON cm.common_id = a.common_id
    WHERE a.type='customer'
      AND a.status1 != 0
    GROUP BY a.common_id, cm.common_name
    HAVING COUNT(*) > 10
    ORDER BY total DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
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
                        <legend><?= $module ?>
                            <a href="accounts.php" class="btn btn-sm btn-primary float-end">Add Customer/Counter</a>
                        </legend>
                        <?php include('component/alert.php'); ?>
                        <form>
                            <div class="card">
                                <div class="card-header text-white">
                                    <?= $module ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="user_id">Referred By<span class="text-danger fw-bold"></span> </label></strong>
                                            <select name="user_id" id="user_id" class="chosen-select form-control form-control-sm">
                                                <option value="">--Select Referred By--</option>
                                                <?php
                                                $sql = $obj->executequery("select userid,fullname,usertype from user where status='1' order by userid asc ");
                                                foreach ($sql as $key) {
                                                ?>
                                                    <option value="<?= $key['userid'] ?>"><?= $key['fullname'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('user_id').value = '<?php echo $user_id; ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Counter / Customer Name <span class="text-danger fw-bold"></span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="account_name" id="account_name" placeholder="Counter/Customer Name" value="<?php echo $account_name; ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="common_id">Counter Type<span class="text-danger fw-bold"></span> </label></strong>
                                            <select name="common_id" id="common_id" class="chosen-select form-control form-control-sm">
                                                <option value="">--Select Counter Type--</option>
                                                <?php
                                                $sql = $obj->executequery("select common_id,common_name from common_master where type='acc_type' and common_id!='6' order by common_id asc ");
                                                foreach ($sql as $key) {
                                                ?>
                                                    <option value="<?= $key['common_id'] ?>"><?= $key['common_name'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <script>
                                                document.getElementById('common_id').value = '<?php echo $common_id; ?>';
                                            </script>
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                            <strong> <label for="class">Class<span class="text-danger fw-bold"></span> </label></strong>
                                            <select name="class" id="class" class="chosen-select  form-control form-control-sm">
                                                <option value="">--Select Class--</option>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                            </select>
                                            <script>
                                                document.getElementById('class').value = '<?php echo $class; ?>';
                                            </script>
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="Search">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
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
                            <div class="row mb-3">
                                <?php
                                $colors = ['primary', 'success', 'dark', 'danger', 'info', 'secondary'];
                                $i = 0;
                                ?>

                                <div class="row mb-3">
                                    <?php foreach ($counterTypes as $type) {
                                        $color = $colors[$i % count($colors)];
                                        $i++;
                                    ?>
                                        <div class="col-lg-2 col-md-3 col-6 mb-3">
                                            <div class="card border-0 bg-<?= $color ?> text-white shadow">
                                                <div class="card-body text-center py-3">
                                                    <h2 class="mb-0"><?= $type['total'] ?></h2>
                                                    <small><?= $type['common_name'] ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th>Sr. No.</th>
                                        <th>Customer Name</th>
                                        <th>Customer Details</th>
                                        <th>Contact Details</th>
                                        <th>Type</th>
                                        <th>Class</th>
                                        <th>Area</th>
                                        <th>Referred By</th>
                                        <th>Dates</th>
                                        <th>Opening Balance</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <?php if ($usertype == "admin") { ?>
                                            <th>Created By</th>
                                        <?php } ?>
                                        <th class="text-center">Action</th>
                                        <th style="display:none;">Customer Name</th>
                                        <th style="display:none;">Owner Name</th>
                                        <th style="display:none;">WhatsApp</th>
                                        <th style="display:none;">Owner Mobile</th>
                                        <th style="display:none;">Type</th>
                                        <th style="display:none;">Class</th>
                                        <th style="display:none;">Family Members</th>
                                        <th style="display:none;">Kids</th>
                                        <th style="display:none;">Area</th>
                                        <th style="display:none;">DOB</th>
                                        <th style="display:none;">DOA</th>
                                        <th style="display:none;">Opening Balance</th>
                                        <th style="display:none;">Opening Date</th>
                                        <th style="display:none;">Status</th>
                                        <th style="display:none;">Address</th>
                                        <th style="display:none;">Assigned To</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $sql_get = $obj->executequery("
SELECT
    a.*,
    cm.common_name,
    am.area_name,
    u.fullname AS referred_by_name,
    uc.fullname AS created_name,

    (
        SELECT r.route_name
        FROM route_counter rc
        INNER JOIN route r ON r.batch_no = rc.batch_no
        WHERE rc.account_id = a.account_id
          AND rc.is_active = 1
        LIMIT 1
    ) AS route_name,

    (
        SELECT u2.fullname
        FROM route_counter rc
        INNER JOIN route_plan rp ON rp.batch_no = rc.batch_no
        INNER JOIN user u2 ON u2.userid = rp.sales_executive_id
        WHERE rc.account_id = a.account_id
          AND rc.is_active = 1
        LIMIT 1
    ) AS sales_executive_name,

    (
    SELECT COUNT(*)
    FROM transaction_entry te
    WHERE te.account_id = a.account_id
) AS txn_count

FROM account a

LEFT JOIN user u
    ON u.userid = a.userid

LEFT JOIN user uc
    ON uc.userid = a.createdby

LEFT JOIN common_master cm
    ON cm.common_id = a.common_id

LEFT JOIN area_master am
    ON am.area_id = a.area_id

WHERE a.status1 != 0
AND a.type = 'customer'

ORDER BY a.account_id DESC
");
                                        foreach ($sql_get as $row_get) {
                                            $area_name = $row_get['area_name'];

                                        ?>
                                            <tr>
                                                <td> <?php echo $slno++; ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark">
                                                        <?= $row_get['account_name'] ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row_get['owner_name'])) { ?>
                                                        <div class="small mt-1">
                                                            <i class="bi bi-person-fill text-primary"></i>
                                                            <strong>Owner:</strong>
                                                            <?= $row_get['owner_name'] ?>
                                                        </div>
                                                    <?php } ?>

                                                    <div class="mt-1">
                                                        <span class="badge bg-info text-dark">
                                                            Family: <?= $row_get['no_of_family'] ?: 0 ?>
                                                        </span>

                                                        <span class="badge bg-warning text-dark">
                                                            Kids: <?= $row_get['no_of_kid'] ?: 0 ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row_get['mobile_no'])) { ?>
                                                        <div>
                                                            <i class="bi bi-whatsapp text-success"></i>
                                                            <?= $row_get['mobile_no'] ?>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if (!empty($row_get['o_mobile_no'])) { ?>
                                                        <small class="text-muted d-block">
                                                            <i class="bi bi-telephone-fill"></i>
                                                            <?= $row_get['o_mobile_no'] ?>
                                                        </small>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">
                                                        <?= $row_get['common_name'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!empty($row_get['class'])) { ?>
                                                        <span class="badge bg-info text-dark">
                                                            <?= $row_get['class'] ?>
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="text-muted">-</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <i class="bi bi-geo-alt-fill text-danger"></i>
                                                    <?= $area_name ?>
                                                </td>
                                                <td>
                                                    <i class="bi bi-person-check-fill text-success"></i>
                                                    <?= $row_get['referred_by_name'] ?: '-' ?>
                                                </td>

                                                <td>
                                                    <small>
                                                        <strong>DOB</strong><br>
                                                        <?= $obj->dateformatindia($row_get['dob']) ?>
                                                    </small>

                                                    <hr class="my-1">

                                                    <small>
                                                        <strong>DOA</strong><br>
                                                        <?= $obj->dateformatindia($row_get['doa']) ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <div class="fw-bold text-success">
                                                        ₹ <?= number_format($row_get['opening_balance'], 2) ?>
                                                    </div>

                                                    <small class="text-muted d-block">
                                                        <?= $obj->dateformatindia($row_get['opening_date']) ?>
                                                    </small>
                                                </td>

                                                <td> <?php if ($row_get['status'] == 'active') { ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row_get['route_name'])) { ?>
                                                        <span class="badge bg-success">
                                                            <?= $row_get['route_name'] ?> - <?= $row_get['sales_executive_name'] ?>
                                                        </span>
                                                    <?php } else { ?>
                                                        -
                                                    <?php } ?>
                                                </td>
                                                <?php if ($usertype == "admin") { ?>
                                                    <td><?= $row_get['created_name'] ?></td>
                                                <?php } ?>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">

                                                        <a href="accounts.php?account_id=<?= $row_get['account_id']; ?>"
                                                            title="Edit"
                                                            class="btn btn-outline-success">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <?php if ($row_get['txn_count'] == 0) { ?>
                                                            <button type="button"
                                                                title="Delete"
                                                                class="btn btn-outline-danger"
                                                                onclick="funDel(<?= $row_get['account_id']; ?>);">
                                                                <i class="bi bi-trash3-fill"></i>
                                                            </button>
                                                        <?php } ?>
                                                        <a href="electrician.php?account_id_map=<?= $row_get['account_id']; ?>"
                                                            title="Add Electrician"
                                                            class="btn btn-outline-primary">
                                                            <i class="bi bi-lightning-charge-fill"></i>
                                                        </a>

                                                    </div>
                                                </td>
                                                <td style="display:none;"><?= $row_get['account_name'] ?></td>
                                                <td style="display:none;"><?= $row_get['owner_name'] ?></td>
                                                <td style="display:none;"><?= $row_get['mobile_no'] ?></td>
                                                <td style="display:none;"><?= $row_get['o_mobile_no'] ?></td>
                                                <td style="display:none;"><?= $row_get['common_name'] ?></td>
                                                <td style="display:none;"><?= $row_get['class'] ?></td>
                                                <td style="display:none;"><?= $row_get['no_of_family'] ?></td>
                                                <td style="display:none;"><?= $row_get['no_of_kid'] ?></td>
                                                <td style="display:none;"><?= $area_name ?></td>
                                                <td style="display:none;"><?= $row_get['dob'] ?></td>
                                                <td style="display:none;"><?= $row_get['doa'] ?></td>
                                                <td style="display:none;"><?= $row_get['opening_balance'] ?></td>
                                                <td style="display:none;"><?= $row_get['opening_date'] ?></td>
                                                <td style="display:none;"><?= ucfirst($row_get['status']) ?></td>
                                                <td style="display:none;"><?= $row_get['address'] ?></td>
                                                <td style="display:none;">
                                                    <?php if (!empty($row_get['route_name'])) { ?>
                                                        <?= $row_get['sales_executive_name']; ?>
                                                    <?php } else { ?>
                                                        -
                                                    <?php } ?>
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
        $('#example1').DataTable({

            pageLength: 100,

            lengthMenu: [
                [100, 200, 500, -1],
                [100, 200, 500, "All"]
            ],

            dom: "<'row align-items-center mb-3'\
                <'col-md-3'l>\
                <'col-md-5 text-center'B>\
                <'col-md-4'f>\
            >" +
                "rt" +
                "<'row mt-3'\
                <'col-md-6'i>\
                <'col-md-6'p>\
            >",

            buttons: [

                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-success btn-sm',

                    exportOptions: {
                        columns: [14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29]
                    }
                },

                {
                    extend: 'pdfHtml5',
                    text: 'Download PDF',
                    className: 'btn btn-danger btn-sm',

                    exportOptions: {
                        columns: [14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29]
                    }
                },

                {
                    extend: 'print',
                    text: 'Print Table',
                    className: 'btn btn-primary btn-sm',

                    exportOptions: {
                        columns: [14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29]
                    }
                }

            ]

        });
    });
</script>

</html>