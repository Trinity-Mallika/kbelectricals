<?php
include("../adminsession.php");
$title = "Order List";
$pagename = "order_list.php";
$module = "Order List";
$submodule = "Order List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$invoice_pending = $_GET['invoice_pending'] ?? '';
$days = $_GET['days'] ?? '';
$dispatch_pending = $_GET['dispatch_pending'] ?? '';
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d');
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";

if ($invoice_pending || $dispatch_pending) {
    $fromdate = '2000-01-01';
    $from = '2000-01-01 00:00:00';
    $to   = date('Y-m-d 23:59:59');
}

$createdby = isset($_GET['createdby']) ? $_GET['createdby'] : '';
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dstatus = isset($_GET['dstatus']) ? $_GET['dstatus'] : '';

$crit = "WHERE t.billdate BETWEEN '$from' AND '$to' 
         AND t.type='order' 
         AND t.companyid='$companyid'";
$summaryCrit = "WHERE billdate BETWEEN '$from' AND '$to'
                AND type='order'
                AND companyid='$companyid'";

if (!empty($createdby)) {
    $crit .= " AND t.createdby = '$createdby'";
    $summaryCrit .= " AND createdby='$createdby'";
}

if ($status != '') {
    $crit .= " AND t.is_approved = '$status'";
    $summaryCrit .= " AND is_approved='$status'";
}

if (!empty($account_id)) {
    $crit .= " AND t.account_id = '$account_id'";
    $summaryCrit .= " AND account_id='$account_id'";
}

if ($dstatus == '0' || $dstatus == '1') {
    echo $dstatus;
    $crit .= "AND t.is_approved = 1
               AND t.dispatch_status  = '$dstatus'";
    $summaryCrit .= "  AND is_approved = 1
                      AND dispatch_status ='$dstatus'";
}
if ($invoice_pending) {
    $crit .= " AND t.is_approved = 1
               AND (t.invoice_no IS NULL OR t.invoice_no='')";

    $summaryCrit .= " AND is_approved = 1
                      AND (invoice_no IS NULL OR invoice_no='')";
}

if ($dispatch_pending) {

    $crit .= " AND t.is_approved = 1
               AND t.dispatch_status = 0";

    $summaryCrit .= " AND is_approved = 1
                      AND dispatch_status = 0";

    if (!empty($days)) {
        $crit .= " AND DATEDIFF(CURDATE(), DATE(t.billdate)) >= " . (int)$days;
        $summaryCrit .= " AND DATEDIFF(CURDATE(), DATE(billdate)) >= " . (int)$days;
    }

    $dstatus = 0;
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
                                    <div class="col-md-3 mb-2">
                                        <strong><label for="fromdate">From Date</label></strong>
                                        <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate"
                                            value="<?php echo $fromdate; ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong><label for="todate">To Date</label></strong>
                                        <input type="date" class="form-control form-control-sm" name="todate" id="todate"
                                            value="<?php echo $todate; ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong><label>Order Status</label></strong>
                                        <select name="status" id="status" class="chosen-select form-control form-control-sm">
                                            <option value="">--Select Status--</option>
                                            <option value="0">Pending</option>
                                            <option value="1">Approved</option>
                                        </select>
                                        <script>
                                            document.getElementById('status').value = '<?= $status ?>';
                                        </script>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong><label>Dispatch Status</label></strong>
                                        <select name="dstatus" id="dstatus" class="chosen-select form-control form-control-sm">
                                            <option value="">--Select Status--</option>
                                            <option value="0">Pending</option>
                                            <option value="1">Approved</option>
                                        </select>
                                        <script>
                                            document.getElementById('dstatus').value = '<?= $dstatus ?>';
                                        </script>
                                    </div>

                                    <div class="col-md-3 mb-2">
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

                                    <div class="col-md-3 mb-2">
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
                    <?php
                    $summary = $obj->executequery("
SELECT
    SUM(CASE WHEN is_approved=1 THEN 1 ELSE 0 END) AS approved_orders,
    SUM(CASE WHEN is_approved=0 THEN 1 ELSE 0 END) AS pending_orders,
    SUM(CASE WHEN is_approved=1 and dispatch_status=0 THEN 1 ELSE 0 END) AS dispatch_pending,
    SUM(CASE WHEN invoice_no<>'' AND invoice_no IS NOT NULL THEN 1 ELSE 0 END) AS invoice_count,
    SUM(grand_total) AS total_order_amt,
   SUM(
    CASE
        WHEN invoice_no IS NOT NULL
        AND invoice_no <> ''
        THEN invoice_amt
        ELSE 0
    END
) AS total_invoice_amt
FROM transaction_entry
$summaryCrit
");

                    $summary = $summary[0];
                    ?>
                    <div class="row g-3 mb-4">

                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card shadow-sm border-0 bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Approved Orders</div>
                                    <h3 class="mb-0"><?= $summary['approved_orders'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Pending Orders</div>
                                    <h3 class="mb-0"><?= $summary['pending_orders'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Pending Dispatch</div>
                                    <h3 class="mb-0"><?= $summary['dispatch_pending'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card shadow-sm border-0 bg-secondary text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Invoice Added</div>
                                    <h3 class="mb-0"><?= $summary['invoice_count'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 col-6">
                            <div class="card shadow-sm border-0 bg-warning h-100">
                                <div class="card-body text-center">
                                    <div class="small">Order Amount</div>
                                    <h4 class="mb-0">
                                        ₹<?= number_format($summary['total_order_amt'] ?? 0, 2) ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 col-6">
                            <div class="card shadow-sm border-0 bg-dark text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Invoice Amount</div>
                                    <h4 class="mb-0">
                                        ₹<?= number_format($summary['total_invoice_amt'] ?? 0, 2) ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                    </div>
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
                                            <th>Order Details</th>
                                            <th>Customer</th>
                                            <th>Created By</th>
                                            <th>Total Qty</th>
                                            <th>Order Amount</th>
                                            <th>Invoice No.</th>
                                            <th>Invoice Amount</th>
                                            <th>Order Status</th>
                                            <th>Dispatch Status</th>
                                            <th>Overdue Days</th>
                                            <th>Order View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $qry = $obj->executequery("SELECT
    t.*,
    a.account_name,
    u.fullname,
    COALESCE(td.total_qty,0) AS total_qty,
    COALESCE(dh.dispatch_qty,0) AS dispatch_qty,
    DATEDIFF(CURDATE(), t.billdate) AS days_overdue,
    IFNULL(
        (
            SELECT SUM(p.grand_total)
            FROM transaction_entry p
            WHERE p.ref_bill_id = t.transaction_id
            AND p.type='payment'
        ),
    0) AS paid_amt
FROM $tblname t

LEFT JOIN (
    SELECT transaction_id,
           SUM(qty) AS total_qty
    FROM transaction_details
    GROUP BY transaction_id
) td ON td.transaction_id=t.transaction_id

LEFT JOIN (
    SELECT transaction_id,
           COUNT(*) AS dispatch_qty
    FROM dispatch_history
    GROUP BY transaction_id
) dh ON dh.transaction_id=t.transaction_id

LEFT JOIN account a ON a.account_id=t.account_id
LEFT JOIN user u ON u.userid=t.createdby

$crit

ORDER BY t.$tblpkey DESC
");


                                        foreach ($qry as $rowget) {

                                            $final_invoice_amt = ($rowget['invoice_amt'] == 0) ? $rowget['grand_total'] : $rowget['invoice_amt'];
                                            $statusHtml = '';
                                            if ($rowget['is_approved'] == 0) {
                                                $statusHtml = '<span class="badge bg-danger">Pending</span>';
                                            } else {
                                                $statusHtml = '<span class="badge bg-success">Approved</span>';
                                            }

                                            $DispHtml = '';
                                            if ($rowget['dispatch_status'] == 0) {
                                                $DispHtml = '<span class="badge bg-danger">Pending</span>';
                                            } else {
                                                $DispHtml = '<span class="badge bg-success">Delivered</span>';
                                            }

                                            // Invoice Status
                                            $invoiceHtml = '';
                                            if (!empty($rowget['invoice_no'])) {
                                                $invoiceHtml = '<span id="fetch_inv"> ' . $rowget['invoice_no'] . '</span>' . '<span 
    class="badge bg-primary add-invoice-btn" 
    style="cursor:pointer;"
    data-id="' . $rowget['transaction_id'] . '"
    data-order="' . $rowget['billno'] . '"
    data-invoice_no="' . $rowget['invoice_no'] . '"
    data-invoice_amt="' . $final_invoice_amt . '">
 <i class="bi bi-pencil-square"></i>
</span>';
                                            } else if ($rowget['is_approved'] == 1) {
                                                $invoiceHtml = '<span 
    class="badge bg-primary add-invoice-btn" 
    style="cursor:pointer;"
    data-id="' . $rowget['transaction_id'] . '"
    data-order="' . $rowget['billno'] . '"
    data-invoice_amt="' . $final_invoice_amt . '">
    Add Invoice +
</span>';
                                            }

                                            // Overdue Days
                                            $overdueDaysHtml = '';
                                            $isUnpaid = ($rowget['grand_total'] > $rowget['paid_amt']);
                                            if ($isUnpaid) {
                                                $days = (int)$rowget['days_overdue'];
                                                $badgeClass = 'bg-danger';
                                                if ($days <= 30) {
                                                    $badgeClass = 'bg-warning text-dark';
                                                } elseif ($days <= 60) {
                                                    $badgeClass = 'bg-danger';
                                                } else {
                                                    $badgeClass = 'bg-dark';
                                                }
                                                $overdueDaysHtml = '<span class="badge ' . $badgeClass . '">' . $days . ' day' . ($days == 1 ? '' : 's') . '</span>';
                                            } else {
                                                $overdueDaysHtml = '<span class="badge bg-success">Paid</span>';
                                            }


                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $slno++; ?></td>
                                                <td>
                                                    <div class="fw-bold text-primary">
                                                        <?= $rowget['billno']; ?>
                                                    </div>

                                                    <small class="text-muted d-block">
                                                        <?= $obj->dateformatindia($rowget['billdate']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($rowget['account_name']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i class="bi bi-person-fill text-success"></i>
                                                    <?= htmlspecialchars($rowget['fullname']); ?>
                                                </td>
                                                <td class="text-center">

                                                    <?= $rowget['total_qty']; ?>

                                                </td>
                                                <td>
                                                    <div class="fw-bold">
                                                        ₹ <?= number_format($rowget['grand_total'], 2); ?>
                                                    </div>
                                                </td>
                                                <td> <?= $invoiceHtml; ?></td>
                                                <td>
                                                    <div class="fw-bold">
                                                        ₹ <?= number_format($rowget['invoice_amt'], 2); ?>
                                                    </div>
                                                </td>

                                                <td>
                                                    <?= $statusHtml; ?>
                                                </td>
                                                <td>
                                                    <?= $DispHtml; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= $overdueDaysHtml; ?>
                                                </td>
                                                <td>
                                                    <div class="text-center d-flex justify-content-center gap-2">
                                                        <?php
                                                        $canEditDelete = ($rowget['dispatch_qty'] == 0);

                                                        if ($canEditDelete) {

                                                            $chkedit = $obj->check_editBtn($pagename, $loginid);
                                                            if ($chkedit > 0 || $_SESSION['usertype'] == 'admin') {
                                                        ?>

                                                                <a href="order-entry.php?transaction_id=<?= $rowget['transaction_id']; ?>"
                                                                    class="btn btn-sm btn-outline-success">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </a>
                                                            <?php }
                                                            $chkdel = $obj->check_delBtn($pagename, $loginid);
                                                            if ($chkdel > 0 || $_SESSION['usertype'] == 'admin') {
                                                            ?>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger"
                                                                    onclick="funDel('<?= $rowget['transaction_id']; ?>','<?= $rowget['parent_transaction_id']; ?>');">
                                                                    <i class="bi bi-trash3-fill"></i>
                                                                </button>
                                                        <?php }
                                                        } ?>
                                                        <a href="order_view.php?transaction_id=<?= $rowget['transaction_id'] ?>"
                                                            class="btn btn-sm btn-warning">
                                                            View
                                                        </a>

                                                        <a href="print_order.php?transaction_id=<?= $rowget['transaction_id'] ?>"
                                                            class="btn btn-sm btn-primary"
                                                            title="Click To Print"
                                                            target="_blank">
                                                            <i class="bi bi-printer"></i>
                                                        </a>

                                                    </div>
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
                    <h1 class="modal-title fs-5" id="invoiceModalLabel">For Order No. <span id="order_ref"></span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <strong><label for="">Invoice No.</label><span class="text-danger fw-bold">*</span></strong>
                            <input type="text" id="invoice_no" class="form-control" placeholder="Enter Invoice No." autocomplete="off">
                        </div>
                        <div class="col-lg-12">
                            <strong><label for="">Invoice Amt</label><span class="text-danger fw-bold">*</span></strong>
                            <input type="text" id="invoice_amt" class="form-control" placeholder="Enter Invoice Amt" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="transaction_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="save_invoice();" id="sav_inv">Save</button>
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
        let invoice_amt = $(this).data('invoice_amt');
        let invoice_no = $(this).data('invoice_no');

        add_invoice(id, order, invoice_amt, invoice_no);
    });

    function add_invoice(transaction_id, order_no, invoice_amt, invoice_no = '') {
        $('#invoiceModal').modal('show');

        $('#transaction_id').val(transaction_id);
        $('#order_ref').text(order_no);

        $('#invoice_no').val(invoice_no).focus();
        $('#invoice_amt').val(invoice_amt).focus();
        if (invoice_no == '') {
            $('#sav_inv').text('Save');
        } else {
            $('#sav_inv').text('Update');
        }
    }

    function save_invoice() {
        let id = $('#transaction_id').val();
        let invoice = $('#invoice_no').val().trim();
        let invoice_amt = $('#invoice_amt').val().trim();
        let order_no = $('#order_ref').text();

        if (invoice === '') {
            alert('Invoice No. is required');
            $('#invoice_no').focus();
            return;
        }

        if (invoice_amt === '' || invoice_amt === 0) {
            alert("Invoice Amt can't be blank or zero");
            $('#invoice_amt').focus();
            return;
        }

        $.ajax({
            url: 'save_invoice.php',
            type: 'POST',
            data: {
                transaction_id: id,
                invoice_no: invoice,
                invoice_amt: invoice_amt
            },
            beforeSend: function() {
                $('#invoiceModal .btn-primary').prop('disabled', true).text('Saving...');
            },
            success: function(res) {
                if (res == 1) {

                    $('#invoiceModal').modal('hide');

                    let btn = $('.add-invoice-btn[data-id="' + id + '"]');
                    $('#fetch_inv').html(invoice);
                    btn.replaceWith('<span class="badge bg-primary add-invoice-btn" style="cursor:pointer;" data-id="' + id + '"  data-order="' + order_no +
                        '"   data-invoice_no="' + invoice + '"    data-invoice_amt="' + invoice_amt + '"> <i class="bi bi-pencil-square"></i> </span>'
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

    function funDel(id, parent_transaction_id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        pagename = '<?php echo $pagename; ?>';
        submodule = '<?php echo $submodule; ?>';
        type = 'order';
        module = '<?php echo $module; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_order.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module + "&type=" + type + "&parent_transaction_id=" + parent_transaction_id,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>' + '&search=search';
                }
            }); //ajax close
        } //confirm close
    } //fun close
</script>

</html>