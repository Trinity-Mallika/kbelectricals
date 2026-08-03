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
                                            $sql = $obj->executequery("SELECT userid, fullname FROM user where usertype='sales' ORDER BY fullname ASC");
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
                                            $sql = $obj->executequery("SELECT account_id, account_name FROM account  ORDER BY account_name ASC");
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
                                            <th>Action</th>
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

                                            if ($row['pay_type'] == 'opening' && $row['ref_bill_id'] == 0) {
                                                $ref_invoice_no = 'Opening';
                                            } else {
                                                $ref_invoice_no = $row['ref_invoice_no'];
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
                                                    <?= $ref_invoice_no ?>
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
                                                            href="uploaded/payment_proof/<?= $row['imgname'] ?>">
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
                                                <td>
                                                    <?php if ($row['pay_status'] == "0") { ?>
                                                        <a href="javascript:void(0)"
                                                            class="btn btn-sm btn-primary"
                                                            onclick="approve_payment('<?= $row[$tblpkey]; ?>');"
                                                            title="Approve Payment">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                        </a>

                                                            <a href="javascript:void(0)" onclick="open_modal('<?= $row[$tblpkey]; ?>');"
                                                                class="btn btn-sm btn-success"
                                                                title="Edit">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>

                                                            <a href="javascript:void(0)"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="funDel('<?= $row[$tblpkey]; ?>','<?= $row['imgname']; ?>');"
                                                                title="Delete">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                    <?php
                                                        
                                                    } ?>

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

    <!-- Modal -->
    <div class="modal fade" id="editModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editModalLabel">Edit Payment</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <strong><label for="">Customer Name <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" id="account_name" placeholder="Customer Name" readonly>
                                <input type="hidden" class="form-control form-control-sm" id="account_id_m" name="account_id_m">
                            </div>
                            <div class="col-lg-12 mb-2">
                                <strong><label for="">Bill No. <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" id="bill_no" placeholder="Bill No." readonly>
                                <input type="hidden" class="form-control form-control-sm" id="bill_id_m" name="bill_id_m">
                                <input type="hidden" id="transaction_id_m" name="transaction_id_m">

                            </div>
                            <div class="col-lg-12 mb-2">
                                <strong><label for="">Pending Amount <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" id="pending_amt_m" name="pending_amt_m" placeholder="Pending Amount" readonly>
                            </div>
                            <div class="col-lg-12 mb-2">
                                <strong><label for="">Pay Mode <span class="text-danger fw-bold">*</span></label></strong>
                                <select class="form-control form-control-sm" id="paymode_m" name="paymode_m" placeholder="Enter Pay Mode">
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                            <div class="col-lg-12 mb-2 conditional-field" id="proof_div" style="display:none;">
                                <strong><label>Payment Proof <span class="text-danger">*</span></label></strong>
                                <input type="file" class="form-control form-control-sm" name="payment_proof" id="payment_proof" accept=".jpg,.jpeg,.png">
                                <input type="hidden" id="old_payment_proof" name="old_payment_proof">
                                <a id="proof_link" href="#" target="_blank" style="display:none;">View Proof</a>
                            </div>
                            <div class="col-lg-12 mb-2 conditional-field" id="tansaction_div" style="display:none;">
                                <strong><label id="trans_label">Transaction ID <span class="text-danger">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" name="trans_id_m" id="trans_id_m" placeholder="Transaction ID">
                            </div>
                            <div class="col-lg-12 mb-2 conditional-field" id="reciept_div">
                                <strong><label for="">Reciept No. <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" id="voucher_no_m" name="voucher_no_m" placeholder="Enter Reciept No.">
                            </div>
                            <div class="col-lg-12 mb-2">
                                <strong><label for="" id="pay_date_l">Payment Date <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="date" class="form-control form-control-sm" id="paydate_m" name="paydate_m" placeholder="Enter Payment Date">
                            </div>
                            <div class="col-lg-12 mb-2">
                                <strong><label for="">Cash Discount <small class="text-danger fw-bold">(If Applicable)</small></label></strong>
                                <input type="text" class="form-control form-control-sm" id="cash_disc_m" name="cash_disc_m" placeholder="Enter Cash Discount">
                            </div>
                            <div class="col-lg-12 mb-2">
                                <strong><label for="" id="pay_amt_l">Payment Amount <span class="text-danger fw-bold">*</span></label></strong>
                                <input type="text" class="form-control form-control-sm" id="pay_amt_m" name="pay_amt_m" placeholder="Enter Payment Amount">
                            </div>
                            <div class="col-lg-12 mb-2 conditional-field" id="bank_div" style="display:none;">
                                <strong><label for="">Bank Name <span class="text-danger fw-bold">*</span></label></strong>
                                <select class="form-control form-control-sm" id="bank_id_m" name="bank_id_m">
                                    <option value="">Select Bank</option>
                                    <?php $res = $obj->executequery("Select * from bank_master");
                                    foreach ($res as $banks) { ?>
                                        <option value="<?= $banks['bank_id'] ?>"><?= $banks['bank_name'] ?></option>
                                    <?php } ?>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-lg-12" id="remark_div">
                                <strong><label for="">Remark <span class="text-danger fw-bold"></span></label></strong>
                                <input type="text" class="form-control form-control-sm" id="remark_m" name="remark" placeholder="Enter Remarks">
                            </div>
                            <div class="col-md-12 mt-4">
                                <input type="hidden" id="transaction_id_m">
                                <button type="submit" class="btn btn-sm btn-primary">Update Payment</button>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();

        const star = ' <span class="text-danger fw-bold">*</span>';

        $('#paymode_m').change(function() {

            let mode = $(this).val();
            $('.conditional-field').hide();
            if (mode === 'Cheque') {

                $('#proof_div, #tansaction_div').show();

                $('#trans_label').html('Cheque No.' + star);
                $('#trans_id_m').attr('placeholder', 'Enter Cheque No.');

                $('#pay_date_l').html('Cheque Date' + star);
                $('#pay_amt_l').html('Cheque Amount' + star);

            } else if (mode === 'Online') {

                $('#tansaction_div, #bank_div').show();

                $('#trans_label').html('Transaction ID' + star);
                $('#trans_id_m').attr('placeholder', 'Enter Transaction ID');

                $('#pay_date_l').html('Payment Date' + star);
                $('#pay_amt_l').html('Payment Amount' + star);

            } else if (mode === 'Cash') {

                $('#reciept_div').show();

                $('#pay_date_l').html('Payment Date' + star);
                $('#pay_amt_l').html('Payment Amount' + star);
            }

        });
    });


    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const account_id = $('#account_id_m').val();
        const bill_id = $('#bill_id_m').val();
        const paymode = $('#paymode_m').val();
        const pay_amt = $('#pay_amt_m').val();
        const transaction_id = $('#transaction_id_m').val();

        if (!account_id || !bill_id || !paymode || !pay_amt) {
            alert('Please fill all required fields');
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'ajaxsave_payment.php',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert('Error saving payment.');
            }
        });
    });

    function open_modal(id) {
        $.ajax({
            type: 'POST',
            url: 'ajax/fetch_payment_details.php',
            data: {
                transaction_id: id
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#transaction_id_m').val(res.data.transaction_id);
                    $('#account_id_m').val(res.data.account_id);
                    $('#account_name').val(res.data.account_name);
                    get_bills(res.data.account_id, res.data.transaction_id, res.data.ref_bill_id)
                    $('#paymode_m').val(res.data.payment_mode).trigger('change');
                    $('#trans_id_m').val(res.data.trans_id);
                    $('#voucher_no_m').val(res.data.billno);
                    $('#paydate_m').val(res.data.payment_date);
                    $('#cash_disc_m').val(res.data.cash_disc);
                    $('#pay_amt_m').val(parseFloat(res.data.grand_total).toFixed(2));
                    $('#bank_id_m').val(res.data.bank_id);
                    $('#remark_m').val(res.data.remark);
                    $('#old_payment_proof').val(res.data.imgname);

                    if (res.data.imgname) {
                        $('#proof_link')
                            .attr('href', '../uploads/payment_proof/' + res.data.imgname)
                            .show();
                    } else {
                        $('#proof_link')
                            .attr('href', '#')
                            .hide();
                    }
                    const modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();
                } else {
                    alert('Error fetching payment details');
                }
            },
            error: function() {
                alert('Error fetching payment details');
            }
        });
    }


    function get_bills(account_id, keyvalue, bill_id) {
        $.ajax({
            type: "POST",
            url: "ajax_get_customer_bills.php",
            data: {
                account_id: account_id,
                keyvalue: keyvalue,
                bill_id: bill_id
            },
            dataType: "json",
            success: function(res) {
                if (res.length > 0) {
                    var bill = res[0];
                    $("#bill_no").val(
                        bill.title +
                        " | Amt : ₹" + parseFloat(bill.amount).toFixed(2) +
                        " | Pending : ₹" + parseFloat(bill.pending).toFixed(2)
                    );
                    $("#bill_id_m").val(bill.id);
                    $("#pending_amt_m").val(parseFloat(bill.pending).toFixed(2));
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("Unable to load bill details.");
            }
        });
    }





    function funDel(id, imgname) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        imgpath = 'uploaded/payment_proof/';
        if (confirm("Are you sure! You want to delete this record.")) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master_img.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&imgname=' + imgname + '&imgpath=' + imgpath,
                dataType: 'html',
                success: function(data) {
                    location.reload();
                }
            });
        }
    }

    function approve_payment(transaction_id) {
        if (confirm("Are you sure you want to approve this payment?")) {
            $.ajax({
                type: "POST",
                url: "ajax/approve_payment.php",
                data: {
                    transaction_id: transaction_id
                },
                dataType: "json",
                success: function(res) {
                    if (res.status === "success") {
                        alert(res.message);
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    }
</script>

</html>