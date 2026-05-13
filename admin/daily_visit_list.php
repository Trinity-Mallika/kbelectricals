<?php
include("../adminsession.php");
$title = "Daily Visiting List";
$pagename = "daily_visit_list.php";
$module = "Daily Visiting List";
$submodule = "Daily Visiting List";
$btn_name = "Save";
$tblname = "daily_entries";
$tblpkey = "entry_id";
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', strtotime('-30 days'));
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";

// $crit = "WHERE de.createdate BETWEEN '$from' AND '$to' and de.companyid='$companyid'";

$createdby = isset($_GET['createdby']) ? $_GET['createdby'] : '';
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';

$crit = "WHERE de.createdate BETWEEN '$from' AND '$to' 
         AND de.companyid='$companyid'";

// Apply Executive filter
if (!empty($createdby)) {
    $crit .= " AND de.createdby = '$createdby'";
}

// Apply Counter filter
if (!empty($account_id)) {
    $crit .= " AND de.account_id = '$account_id'";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
    <style>
        .badge-follow {
            background: #ffc107;
            color: #000;
            font-size: 11px;
        }

        .btn-map {
            padding: 2px 6px;
            font-size: 11px;
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

                <form>
                    <div class="card mt-3">
                        <div class="card-header text-white">
                            <?php echo $module; ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong><label for="fromdate">From Date</label></strong>
                                    <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate"
                                        value="<?php echo $fromdate; ?>">
                                </div>
                                <div class="col-md-3">
                                    <strong><label for="todate">To Date</label></strong>
                                    <input type="date" class="form-control form-control-sm" name="todate" id="todate"
                                        value="<?php echo $todate; ?>">
                                </div>


                                <div class="col-md-3">
                                    <strong><label>Executive</label></strong>
                                    <select name="createdby" id="createdby" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select Executive--</option>
                                        <?php
                                        $sql = $obj->executequery("SELECT userid, fullname FROM user ORDER BY fullname ASC");
                                        foreach ($sql as $row) {
                                        ?>
                                            <option value="<?= $row['userid']; ?>">
                                                <?= $row['fullname']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <script>
                                        document.getElementById('createdby').value = '<?= $createdby ?>';
                                    </script>
                                </div>

                                <div class="col-md-3">
                                    <strong><label>Counter Name</label></strong>
                                    <select name="account_id" id="account_id" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select Counter--</option>
                                        <?php
                                        $sql = $obj->executequery("SELECT account_id, account_name FROM account WHERE companyid='$companyid' ORDER BY account_name ASC");
                                        foreach ($sql as $row) {
                                        ?>
                                            <option value="<?= $row['account_id']; ?>">
                                                <?= $row['account_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <script>
                                        document.getElementById('account_id').value = '<?= $account_id ?>';
                                    </script>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <input type="submit" class="btn btn-primary btn-sm" name="search" value="Search">
                                    <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" id="reset">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="card mt-4">
                    <div class="card-header text-white">
                        <?php echo $submodule; ?> Record
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table table-bordered table-sm table-hover">
                                <thead>
                                    <tr class="table-primary">
                                        <th width="50">Sr No.</th>
                                        <th>Executive</th>
                                        <th>Counter</th>
                                        <th>Decision Maker</th>
                                        <th>Mobile</th>
                                        <th>Site /Retail Counter Photo </th>
                                        <th>Product Discussed</th>
                                        <th>Follow Up</th>
                                        <th>Discussion details</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $slno = 1;
                                    $qry = $obj->executequery("
            SELECT de.*, a.account_name, a.mobile_no, u.fullname,c.common_name
            FROM $tblname de
            LEFT JOIN account a ON a.account_id = de.account_id
            LEFT JOIN common_master c ON c.common_id = de.common_id and c.type='product_display'
            LEFT JOIN user u ON u.userid = de.createdby
            $crit 
            ORDER BY de.$tblpkey DESC
        ");

                                    foreach ($qry as $rowget) {
                                    ?>
                                        <tr>
                                            <td><?php echo $slno++; ?></td>

                                            <td>
                                                <strong><?php echo $rowget['fullname']; ?></strong><br>
                                                <small class="text-muted">
                                                    <?php echo date('d M Y h:i A', strtotime($rowget['createdate'])); ?>
                                                </small>
                                            </td>

                                            <td><?php echo ucfirst($rowget['account_name']); ?></td>

                                            <td><?php echo $rowget['decision_maker_name']; ?></td>

                                            <td>
                                                <a href="tel:<?php echo $rowget['mobile_no']; ?>">
                                                    <?php echo $rowget['mobile_no']; ?>
                                                </a>
                                            </td>

                                            <td>
                                                <?php if ($rowget['imgname'] != '') { ?>
                                                    <a href="../app/uploads/daily_entry/<?php echo $rowget['imgname']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                        View
                                                    </a>
                                                <?php } else { ?>
                                                    <span class="text-muted">No Image</span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo ucfirst($rowget['common_name']); ?></td>

                                            <td>
                                                <span class="badge badge-follow">
                                                    <?php echo $obj->dateformatindia($rowget['follow_up_date']); ?>
                                                </span>
                                            </td>

                                            <td><?php echo ucfirst($rowget['remarks']); ?></td>

                                            <td>
                                                <small><?php echo $rowget['address']; ?></small><br>
                                                <?php if ($rowget['latitude'] != '') { ?>
                                                    <a class="btn btn-sm btn-primary btn-map" target="_blank"
                                                        href="https://www.google.com/maps?q=<?php echo $rowget['latitude']; ?>,<?php echo $rowget['longitude']; ?>">
                                                        Map
                                                    </a>
                                                <?php } ?>
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
    <!-- Content close-->
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

<script>
    $(document).ready(function() {
        $("#example").DataTable();
        $(".chosen-select").chosen();
    });
</script>

</html>