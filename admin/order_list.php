<?php
include("../adminsession.php");
$title = "Order List";
$pagename = "order_list.php";
$module = "Order List";
$submodule = "Order List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";

$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', strtotime('-30 days'));
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";

// $crit = "WHERE t.billdate BETWEEN '$from' AND '$to' and t.type='order' and t.companyid='$companyid'";

$createdby = isset($_GET['createdby']) ? $_GET['createdby'] : '';
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';

$crit = "WHERE t.billdate BETWEEN '$from' AND '$to' 
         AND t.type='order' 
         AND t.companyid='$companyid'";

// Apply Executive filter
if (!empty($createdby)) {
    $crit .= " AND t.createdby = '$createdby'";
}

// Apply Counter filter
if (!empty($account_id)) {
    $crit .= " AND t.account_id = '$account_id'";
}
if (isset($_REQUEST['order_trans_id'])) {
    $order_trans_id = $_REQUEST['order_trans_id'];
    $obj->update_record("$tblname", ['transaction_id' => $order_trans_id], ['is_approved' => 1, 'approve_date' => date('Y-m-d')]);
    echo 1;
    die;
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
                                        <strong><label>Order Received By</label></strong>
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
                                            <th width="20"></th>
                                            <th width="50">Sr No.</th>
                                            <th>Order recived By</th>
                                            <th>Counter Name</th>
                                            <th>Order No.</th>
                                            <th>Order Date</th>
                                            <th>Total Qty</th>
                                            <th>Location</th>
                                            <th>Order Status</th>
                                            <th>Order View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $qry = $obj->executequery("
    SELECT t.*, a.account_name, a.mobile_no, u.fullname, SUM(td.qty) AS total_qty
    FROM $tblname t
    LEFT JOIN transaction_details td ON t.transaction_id = td.transaction_id
    LEFT JOIN account a ON a.account_id = t.account_id
    LEFT JOIN user u ON u.userid = t.createdby
    $crit 
    GROUP BY t.transaction_id
    ORDER BY t.$tblpkey DESC
");

                                        foreach ($qry as $rowget) {
                                        ?>
                                            <tr data-id="<?php echo $rowget['transaction_id']; ?>">
                                                <td class="details-control text-center">
                                                    <span class="badge bg-primary toggle-row" style="cursor:pointer;">
                                                        <i class="bi bi-plus"></i>
                                                    </span>
                                                </td>
                                                <td><?php echo $slno++; ?></td>
                                                <td><strong><?php echo $rowget['fullname']; ?></strong></td>
                                                <td><?php echo ucfirst($rowget['account_name']); ?></td>
                                                <td><?php echo $rowget['billno']; ?></td>
                                                <td><?php echo $obj->dateformatindia($rowget['billdate']); ?></td>
                                                <td><?php echo $rowget['total_qty']; ?></td>
                                                <td><?php echo nl2br($rowget['address']); ?>
                                                    <?php if ($rowget['latitude'] != '') { ?>
                                                        <br>
                                                        <a class="btn btn-sm btn-primary" target="_blank"
                                                            href="https://www.google.com/maps?q=<?php echo $rowget['latitude']; ?>,<?php echo $rowget['longitude']; ?>">
                                                            Map
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($rowget['is_approved'] == 0) { ?><span class="text-danger"><b>Pending</b></span>
                                                    <?php } else { ?>
                                                        <span class="text-success"><b>Approved</b></span>
                                                    <?php } ?>
                                                </td>
                                                <td><a href="order_view.php?transaction_id=<?php echo $rowget['transaction_id'] ?>" class="btn btn-sm btn-warning">View</a></td>

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

        $(".chosen-select").chosen();
    });
</script>
<script>
    function order_approve(transaction_id) {

        swal({
                title: "Are you sure?",
                text: "You want to approve this order!",
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((willApprove) => {

                if (willApprove) {

                    $.ajax({
                        url: "",
                        type: "POST",
                        data: {
                            order_trans_id: transaction_id
                        },
                        success: function(res) {

                            if (res == '1') {

                                swal("Approved!", "Order has been approved.", "success")
                                    .then(() => {
                                        location.reload();
                                    });

                            } else {
                                swal("Error!", "Failed to approve.", "error");
                            }

                        }
                    });

                }

            });
    }
    $(document).ready(function() {

        var table = $('#example').DataTable({
            "order": [
                [1, "asc"]
            ],
            "pageLength": 25
        });

        $('#example tbody').on('click', 'td.details-control', function() {

            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var btn = $(this).find("i");
            var id = tr.data("id");

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                btn.removeClass("bi-dash").addClass("bi-plus");
            } else {
                $.ajax({
                    url: "load_products.php",
                    type: "POST",
                    data: {
                        transaction_id: id
                    },
                    success: function(data) {
                        row.child(data).show();
                        tr.addClass('shown');
                    }
                });

                btn.removeClass("bi-plus").addClass("bi-dash");
            }

        });

    });
</script>

</html>