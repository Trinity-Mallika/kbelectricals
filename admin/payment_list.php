<?php
include("../adminsession.php");
$title = "Payment List";
$pagename = "payment_list.php";
$module = "Payment List";
$submodule = "Payment List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";

$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', strtotime('-30 days'));
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";

$createdby = isset($_GET['createdby']) ? $_GET['createdby'] : '';
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';

$crit = "WHERE t.billdate BETWEEN '$from' AND '$to' 
         AND t.type='payment' 
         AND t.companyid='$companyid'";

if (!empty($createdby)) {
    $crit .= " AND t.createdby = '$createdby'";
}

if (!empty($account_id)) {
    $crit .= " AND t.account_id = '$account_id'";
}
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
                <div class="col-lg-12 mb-2">
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
                                        <strong><label>Payment Received By</label></strong>
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
                </div>
                <div class="col-lg-12 mb-2">
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
                                            <th>Payment Received By</th>
                                            <th>Counter Name</th>
                                            <th>Transaction Id</th>
                                            <th>Invoice No.</th>
                                            <th>Voucher No.</th>
                                            <th>Voucher Date</th>
                                            <th>Pay Mode</th>
                                            <th>Amount</th>
                                            <th>Payment Proof</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $qry = $obj->executequery("
    SELECT 
        t.*, 
        a.account_name, 
        u.fullname, 
        b.invoice_no as ref_invoice_no
    FROM $tblname t
    LEFT JOIN account a ON a.account_id = t.account_id
    LEFT JOIN user u ON u.userid = t.createdby
    LEFT JOIN transaction_entry b ON b.transaction_id = t.ref_bill_id
    $crit 
    ORDER BY t.$tblpkey DESC
");

                                        foreach ($qry as $row) {

                                            // Payment reference logic
                                            $ref = '';
                                            if ($row['paymode'] == 'Cheque') {
                                                $ref = 'Cheque: ' . $row['trans_id'];
                                            } elseif ($row['paymode'] == 'Online') {
                                                $ref = 'Txn: ' . $row['trans_id'];
                                            } else {
                                                $ref = '-';
                                            }

                                            // Paymode badge
                                            $badge = '';
                                            if ($row['paymode'] == 'Cash') {
                                                $badge = '<span class="badge bg-success">Cash</span>';
                                            } elseif ($row['paymode'] == 'Cheque') {
                                                $badge = '<span class="badge bg-warning text-dark">Cheque</span>';
                                            } else {
                                                $badge = '<span class="badge bg-info text-dark">Online</span>';
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $slno++ ?></td>

                                                <td>
                                                    <strong><?= $row['fullname'] ?></strong>
                                                </td>

                                                <td><?= ucfirst($row['account_name']) ?></td>

                                                <td><?= $ref ?></td>

                                                <td>
                                                    <?= $row['ref_invoice_no'] ?: '-' ?>
                                                </td>

                                                <td>
                                                    <?= $row['billno'] ?>
                                                </td>

                                                <td>
                                                    <?= $obj->dateformatindia($row['billdate']) ?>
                                                </td>

                                                <td><?= $badge ?></td>

                                                <td class="text-end">
                                                    ₹<?= number_format($row['grand_total'], 2) ?>
                                                </td>

                                                <td class="text-center">
                                                    <?php if ($row['imgname']) { ?>
                                                        <a class="btn btn-sm btn-outline-primary" target="_blank"
                                                            href="../app/uploads/payment_proof/<?= $row['imgname'] ?>">
                                                            View
                                                        </a>
                                                    <?php } else { ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php } ?>
                                                </td>

                                                <td>
                                                    <?= nl2br($row['address']) ?>

                                                    <?php if ($row['latitude']) { ?>
                                                        <br>
                                                        <a class="btn btn-sm btn-outline-dark mt-1" target="_blank"
                                                            href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>">
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
    </div>
    <!-- Content close-->
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });
</script>

</html>