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
                                            <th>Sr No.</th>
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
    SELECT 
        t.*,
        a.account_name,
        u.fullname,
        COALESCE(td.total_qty, 0) AS total_qty
    FROM $tblname t
    LEFT JOIN (
        SELECT transaction_id, SUM(qty) AS total_qty
        FROM transaction_details
        GROUP BY transaction_id
    ) td ON t.transaction_id = td.transaction_id
    LEFT JOIN account a ON a.account_id = t.account_id
    LEFT JOIN user u ON u.userid = t.createdby
    $crit
    ORDER BY t.$tblpkey DESC
");
                                        foreach ($qry as $rowget) {

                                            $statusHtml = '';
                                            if ($rowget['is_approved'] == 0) {
                                                $statusHtml = '<span class="badge bg-danger">Pending</span>';
                                            } else {
                                                $statusHtml = '<span class="badge bg-success">Approved</span>';
                                            }

                                            // Invoice Status
                                            $invoiceHtml = '';
                                            if (!empty($rowget['invoice_no'])) {
                                                $invoiceHtml = '<span class="badge bg-info">Inv: ' . $rowget['invoice_no'] . '</span>';
                                            } else if ($rowget['is_approved'] == 1) {
                                                $invoiceHtml = '<span 
    class="badge bg-primary add-invoice-btn" 
    style="cursor:pointer;"
    data-id="' . $rowget['transaction_id'] . '"
    data-order="' . $rowget['billno'] . '">
    Add Invoice +
</span>';
                                            }

                                            // Location
                                            $mapBtn = '';
                                            if (!empty($rowget['latitude'])) {
                                                $mapBtn = '<br><a class="btn btn-sm btn-secondary" target="_blank"
        href="https://www.google.com/maps?q=' . $rowget['latitude'] . ',' . $rowget['longitude'] . '">Map</a>';
                                            }

                                            $location = !empty($rowget['address'])
                                                ? nl2br(htmlspecialchars($rowget['address'])) . $mapBtn
                                                : '<span class="text-muted">N/A</span>';
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $slno++; ?></td>
                                                <td><?= htmlspecialchars($rowget['fullname']); ?></td>
                                                <td><?= htmlspecialchars($rowget['account_name']); ?></td>
                                                <td><?= $rowget['billno']; ?></td>
                                                <td><?= $obj->dateformatindia($rowget['billdate']); ?></td>
                                                <td><?= $rowget['total_qty']; ?></td>
                                                <td><?= $location; ?></td>
                                                <td>
                                                    <?= $statusHtml; ?><br>
                                                    <?= $invoiceHtml; ?>
                                                </td>
                                                <td>
                                                    <a href="order_view.php?transaction_id=<?= $rowget['transaction_id'] ?>"
                                                        class="btn btn-sm btn-warning">View</a>
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

    <div class="modal fade" id="invoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="invoiceModalLabel">Add Invoice No. For <span id="order_ref"></span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <strong><label for="">Invoice No.</label><span class="text-danger fw-bold">*</span></strong>
                            <input type="text" id="invoice_no" class="form-control" placeholder="Enter Invoice No." autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="transaction_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="save_invoice();">Save</button>
                </div>
            </div>
        </div>
    </div>
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });

    $(document).on('click', '.add-invoice-btn', function() {
        let id = $(this).data('id');
        let order = $(this).data('order');

        add_invoice(id, order);
    });

    function add_invoice(transaction_id, order_no) {
        $('#invoiceModal').modal('show');

        $('#transaction_id').val(transaction_id);
        $('#order_ref').text(order_no);

        $('#invoice_no').val('').focus();
    }

    function save_invoice() {
        let id = $('#transaction_id').val();
        let invoice = $('#invoice_no').val().trim();

        if (invoice === '') {
            alert('Invoice No. is required');
            $('#invoice_no').focus();
            return;
        }

        $.ajax({
            url: 'save_invoice.php',
            type: 'POST',
            data: {
                transaction_id: id,
                invoice_no: invoice
            },
            beforeSend: function() {
                $('#invoiceModal .btn-primary').prop('disabled', true).text('Saving...');
            },
            success: function(res) {
                if (res == 1) {

                    $('#invoiceModal').modal('hide');

                    let btn = $('.add-invoice-btn[data-id="' + id + '"]');

                    btn.replaceWith(
                        '<span class="badge bg-info">Inv: ' + invoice + '</span>'
                    );

                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Invoice added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });

                } else if (res == 2) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Invoice',
                        text: 'This invoice number already exists'
                    });

                    $('#invoice_no').focus();

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to save invoice'
                    });

                }
            },
            complete: function() {
                $('#invoiceModal .btn-primary').prop('disabled', false).text('Save');
            }
        });
    }
</script>

</html>