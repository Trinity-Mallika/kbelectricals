<?php
include("../adminsession.php");
$title = "Payment";
$pagename = "payment.php";
$module = "Payment Entry";
$submodule = "Payment Entry";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$keyvalue = (isset($_GET["transaction_id"])) ? $obj->test_input($_GET["transaction_id"]) : 0;
$imgpath = "uploaded/payment_proof/";


if (isset($_POST['submit'])) {
    $keyvalue   = $obj->test_input($_POST['transaction_id']);
    $account_id = $obj->test_input($_POST['account_id']);
    $bill_id = $obj->test_input($_POST['bill_id']);
    $pay_type = ($bill_id == "opening") ? "$bill_id" : "bill";
    $paymode    = $obj->test_input($_POST['paymode']);
    $paydate    = $obj->test_input($_POST['paydate']);
    $pay_amt    = $obj->test_input($_POST['pay_amt']);
    $cash_disc    = $obj->test_input($_POST['cash_disc']);
    $voucher_no = $obj->test_input($_POST['voucher_no']);
    $trans_id   = $obj->test_input($_POST['trans_id']);
    $bank_id = isset($_POST['bank_id']) ? $obj->test_input($_POST['bank_id']) : '';
    $filename = '';

    if ($account_id == "" || $paymode == "" || $paydate == "" || $pay_amt == "") {
        echo "error";
        exit;
    }

    if ($paymode != 'Cash') {

        if (!empty($_FILES["payment_proof"]['name'])) {

            $filename = $obj->uploadImage($imgpath, $_FILES["payment_proof"]);

            if ($filename != "" && $keyvalue != 0) {

                $old = $obj->getvalfield(
                    $tblname,
                    "imgname",
                    "$tblpkey='$keyvalue'"
                );

                if ($old != "") {
                    @unlink($imgpath . $old);
                }
            }
        } elseif ($keyvalue != 0) {

            $filename = $obj->getvalfield(
                $tblname,
                "imgname",
                "$tblpkey='$keyvalue'"
            );
        }
    } else {
        $filename = "";
    }

    $form_data = array(
        'account_id'    => $account_id,
        'ref_bill_id' => $bill_id,
        'paymode'       => $paymode,
        'imgname'       => $filename,
        'billdate'      => $paydate,
        'grand_total'   => $pay_amt,
        'cash_disc'   => $cash_disc,
        'billno'        => $voucher_no,
        'trans_id'      => $trans_id,
        'bank_id'      => $bank_id,
        'type'          => 'payment',
        'pay_status'          => '1',
        'pay_type'      => $pay_type,
        'createdby'     => $loginid,
        'companyid'     => $companyid,
        'ipaddress'     => $ipaddress
    );

    if ($keyvalue == 0) {
        $form_data['createdate'] = $createdate;
        $obj->insert_record($tblname, $form_data);
        $action = 1;
        $process = "Insert";
    } else {
        $form_data['lastupdated'] = $createdate;
        $obj->update_record($tblname, [$tblpkey => $keyvalue], $form_data);
        $action = 2;
        $process = "Update";
    }
    echo "<script>location='$pagename?action=$action&account_id=$account_id'</script>";
}


if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $account_id = $sqledit['account_id'];
    $paymode = $sqledit['paymode'];
    $paydate = $sqledit['billdate'];
    $pay_amt = $sqledit['grand_total'];
    $cash_disc = $sqledit['cash_disc'];
    $voucher_no = $sqledit['billno'];
    $payment_proof = $sqledit['imgname'];
    $trans_id = $sqledit['trans_id'];
    $bill_id = $sqledit['ref_bill_id'];
    $pending_amt = "";
} else {
    $pay_amt  = $payment_proof = $trans_id = $bill_id = $cash_disc = "";
    $paydate = date('Y-m-d');
    $pending_amt = "";
    $voucher_no = '';
    $paymode = 'Cash';
    $account_id = (isset($_GET["account_id"])) ? $obj->test_input($_GET["account_id"]) : 0;
}


$crit = " where t.account_id='$account_id' and t.type='payment'";

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
                <div class="col-lg-4 mb-2">
                    <form method="post" enctype="multipart/form-data">
                        <div class="card mt-3">
                            <div class="card-header text-white">
                                <?php echo $module; ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 mb-2">
                                        <strong><label for=""> Customer Name <span class="text-danger fw-bold">*</span></label></strong>
                                        <select class="form-select form-select-sm chosen-select" name="account_id" id="account_id" onchange="set_url(this.value);">
                                            <option value="">Select</option>
                                            <?php
                                            $res = $obj->executequery("SELECT a.account_id,a.account_name,cm.common_name AS account_type,am.area_name FROM account a LEFT JOIN common_master cm ON cm.common_id = a.common_id AND cm.type = 'acc_type' LEFT JOIN area_master am ON am.area_id = a.area_id ORDER BY a.account_name ASC");
                                            foreach ($res as $key) {
                                            ?>
                                                <option value="<?= $key['account_id']; ?>">
                                                    <?= $key['account_name']; ?>
                                                    [<?= $key['account_type']; ?>]
                                                    <?= !empty($key['area_name']) ? ' / ' . $key['area_name'] : ''; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <script>
                                            document.getElementById('account_id').value = '<?= $account_id ?>';
                                        </script>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <strong><label>Select a Bill<span class="text-danger fw-bold">*</span></label></strong>
                                        <select name="bill_id" id="bill_id" class="form-select form-select-sm chosen-select" onchange="handleBillChange(this.value);">
                                            <option value="">Select Bill</option>
                                            <?php
                                            $opening_amt = (float)$obj->getvalfield(
                                                "account",
                                                "opening_balance",
                                                "account_id='$account_id'"
                                            );

                                            $opening_paid = (float)$obj->getvalfield(
                                                "transaction_entry",
                                                "IFNULL(SUM(grand_total + IFNULL(cash_disc,0)),0)",
                                                "account_id='$account_id'
    AND type='payment'
    AND pay_type='opening'
    AND transaction_id!='$keyvalue'"
                                            );

                                            $opening_pending = $opening_amt - $opening_paid;

                                            /* Opening Balance */
                                            if ($opening_pending > 0) {

                                                echo '<option
            value="opening"
            data-total="' . $opening_amt . '"
            data-pending="' . $opening_pending . '"
            selected>
            Opening Balance (Pending ₹' . number_format($opening_pending, 2) . ')
          </option>';
                                            }

                                            $res = $obj->executequery("
SELECT
    t.transaction_id,
    t.billno,
    t.invoice_no,
    t.billdate,
    t.grand_total AS total_amt,

    IFNULL(
        SUM(
            p.grand_total + IFNULL(p.cash_disc,0)
        ),
        0
    ) AS total_paid

FROM transaction_entry t

LEFT JOIN transaction_entry p
    ON p.ref_bill_id = t.transaction_id
    AND p.type='payment'
    AND p.pay_type='bill'
    AND p.transaction_id!='$keyvalue'

WHERE t.account_id='$account_id'
AND t.type='order'
AND t.is_approved=1
AND t.invoice_no<>''

GROUP BY t.transaction_id
ORDER BY t.billdate ASC, t.transaction_id ASC
");

                                            foreach ($res as $row) {

                                                $total   = (float)$row['total_amt'];
                                                $paid    = (float)$row['total_paid'];
                                                $pending = $total - $paid;
                                                if ($opening_pending > 0) {
                                                    $disabled = "disabled";
                                                } else {
                                                    $disabled = ($pending <= 0) ? "disabled" : "";
                                                }

                                                $selected = ($bill_id == $row['transaction_id']) ? "selected" : "";

                                                echo '<option
            value="' . $row['transaction_id'] . '"
            data-total="' . $total . '"
            data-pending="' . $pending . '"
            ' . $disabled . '
            ' . $selected . '>';

                                                if ($pending <= 0) {
                                                    echo '✅ ';
                                                } elseif ($opening_pending > 0) {
                                                    echo '🔒 ';
                                                }

                                                echo $row['invoice_no']
                                                    . ' (₹' . number_format($total, 2)
                                                    . ' | Pending ₹' . number_format($pending, 2)
                                                    . ') / ' . $obj->dateformatindia($row['billdate']);

                                                echo '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <strong><label for="">Pending Amount <span class="text-danger fw-bold">*</span></label></strong>
                                        <input type="text" class="form-control form-control-sm" id="pending_amt" name="pending_amt" placeholder="Pending Amount" value="<?php echo $pending_amt ?>" readonly>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <strong><label for="">Pay Mode <span class="text-danger fw-bold">*</span></label></strong>
                                        <select class="form-control form-control-sm" id="paymode" name="paymode" placeholder="Enter Pay Mode">
                                            <option value="">Select Pay Mode</option>
                                            <option value="Cash" <?php if ($paymode == "Cash") echo "selected"; ?>>Cash</option>
                                            <option value="Cheque" <?php if ($paymode == "Cheque") echo "selected"; ?>>Cheque</option>
                                            <option value="Online" <?php if ($paymode == "Online") echo "selected"; ?>>Online</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mb-2 conditional-field" id="proof_div" style="display:none;">
                                        <strong><label>Payment Proof <span class="text-danger">*</span></label></strong>
                                        <input type="file" class="form-control form-control-sm" name="payment_proof" id="payment_proof" accept=".jpg,.jpeg,.png">
                                        <?php if ($payment_proof != "") { ?>
                                            <img src="uploads/payment_proof/<?php echo $payment_proof; ?>" alt="" style="width: 80px;" class="mt-2">
                                        <?php } ?>
                                    </div>
                                    <div class="col-lg-12 mb-2 conditional-field" id="tansaction_div" style="display:none;">
                                        <strong><label id="trans_label">Transaction ID <span class="text-danger">*</span></label></strong>
                                        <input type="text" class="form-control form-control-sm" name="trans_id" id="trans_id" placeholder="Transaction ID" value="<?php echo $trans_id ?>">
                                    </div>
                                    <div class="col-lg-12 mb-2 conditional-field" id="reciept_div">
                                        <strong><label for="">Reciept No. <span class="text-danger fw-bold">*</span></label></strong>
                                        <input type="text" class="form-control form-control-sm" id="voucher_no" name="voucher_no" placeholder="Enter Reciept No." value="<?php echo $voucher_no ?>">
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <strong><label for="" id="pay_date_l">Payment Date <span class="text-danger fw-bold">*</span></label></strong>
                                        <input type="date" class="form-control form-control-sm" id="paydate" name="paydate" placeholder="Enter Payment Date" value="<?php echo $paydate ?>">
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <strong><label for="">Cash Discount <small class="text-danger fw-bold">(If Applicable)</small></label></strong>
                                        <input type="text" class="form-control form-control-sm" id="cash_disc" name="cash_disc" placeholder="Enter Cash Discount" value="<?php echo $cash_disc ?>">
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <strong><label for="" id="pay_amt_l">Payment Amount <span class="text-danger fw-bold">*</span></label></strong>
                                        <input type="text" class="form-control form-control-sm" id="pay_amt" name="pay_amt" placeholder="Enter Payment Amount" value="<?php echo $pay_amt ?>">
                                    </div>
                                    <div class="col-lg-12 mb-2 conditional-field" id="bank_div" style="display:none;">
                                        <strong><label for="">Bank Name <span class="text-danger fw-bold">*</span></label></strong>
                                        <select class="form-control form-control-sm" id="bank_id" name="bank_id">
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
                                        <input type="text" class="form-control form-control-sm" id="remark" name="remark" placeholder="Enter Remarks" value="<?php echo $pay_amt ?>">
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <input type="hidden" name="<?= $tblpkey ?>" id="<?= $tblpkey ?>" value="<?php echo $keyvalue ?>">
                                        <input type="submit" name="submit" class="btn btn-sm btn-primary" value="<?php echo $btn_name ?>">
                                        <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" id="reset">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-8 mb-2">
                    <div class="card mt-3">
                        <div class="card-header text-white">
                            Customer Ledger
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="table-responsive" id="ledger-data">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?> Record
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-hover table-sm align-middle">
                                    <thead>
                                        <tr class="table-primary">
                                            <th width="60">Sr No.</th>
                                            <th>Counter Name</th>
                                            <th>Bill No.</th>
                                            <th>Receipt / Cheque / Transaction No.</th>
                                            <th width="120">Payment Date</th>
                                            <th width="100">Pay Mode</th>
                                            <th width="120" class="text-end">Amount</th>
                                            <th width="100">Proof</th>
                                            <th>Remark</th>
                                            <th width="120">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $qry = $obj->executequery("
                SELECT
                    t.*,
                    a.account_name,
                    b.invoice_no AS ref_invoice_no
                FROM $tblname t
                LEFT JOIN account a
                    ON a.account_id = t.account_id
                LEFT JOIN transaction_entry b
                    ON t.ref_bill_id = b.transaction_id
                $crit
                ORDER BY t.$tblpkey DESC
            ");

                                        foreach ($qry as $row) {

                                            if ($row['paymode'] == 'Cheque') {

                                                $ref = 'Cheque : ' . $row['trans_id'];

                                                $badge = '<span class="badge bg-warning text-dark">Cheque</span>';
                                            } elseif ($row['paymode'] == 'Online') {

                                                $ref = 'Txn : ' . $row['trans_id'];

                                                $badge = '<span class="badge bg-info text-dark">Online</span>';
                                            } else {

                                                $ref = '-';

                                                $badge = '<span class="badge bg-success">Cash</span>';
                                            }
                                        ?>
                                            <tr>

                                                <td><?= $slno++; ?></td>

                                                <td>
                                                    <strong><?= ucfirst($row['account_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <?= $row['ref_invoice_no']; ?>
                                                </td>
                                                <td>
                                                    <?= $ref; ?>
                                                </td>
                                                <td>
                                                    <?= $obj->dateformatindia($row['billdate']); ?>
                                                </td>
                                                <td>
                                                    <?= $badge; ?>
                                                </td>
                                                <td class="text-end">
                                                    ₹<?= number_format($row['grand_total'], 2); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (!empty($row['imgname'])) { ?>
                                                        <a href="<?= $imgpath . $row['imgname']; ?>"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    <?php } else { ?>
                                                        <span class="badge bg-secondary">N/A</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?= $row['remark']; ?>
                                                </td>
                                                <td>

                                                    <a href="<?= $pagename ?>?transaction_id=<?= $row[$tblpkey]; ?>"
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
        handleBillChange();
        load_ledger(<?= $account_id; ?>);
    });

    function set_url(account_id) {
        if (account_id > 0) {
            location = "?account_id=" + account_id;
        }
    }


    $('#pay_amt, #cash_disc').on('input', function() {

        let pending = parseFloat($('#bill_id option:selected').data('pending')) || 0;
        let cash_disc = parseFloat($('#cash_disc').val()) || 0;
        let pay_amt = parseFloat($('#pay_amt').val()) || 0;

        if (cash_disc > pending) {
            alert('Discount cannot exceed pending amount');
            $('#cash_disc').val(pending);
            cash_disc = pending;
        }

        let maxPay = pending - cash_disc;

        if ($(this).attr('id') == 'pay_amt') {

            if (pay_amt > maxPay) {
                alert('Payment cannot exceed pending amount');
                $('#pay_amt').val(maxPay.toFixed(2));
            }

        } else {

            $('#pay_amt').val(maxPay.toFixed(2));

        }

    });


    function handleBillChange(bill_id) {

        bill_id = $('#bill_id').val();

        if (!bill_id) {
            $('#pending_amt').val('');
            $('#pay_amt').val('');
            $('#cash_disc').val('');

            return;
        }
        let pending = parseFloat(
            $('#bill_id option:selected').data('pending')
        ) || 0;

        $('#pending_amt').val(pending.toFixed(2));

        $('#pay_amt')
            .val(pending)
            .trigger('input');

    };

    const star = ' <span class="text-danger fw-bold">*</span>';

    $('#paymode').change(function() {

        let mode = $(this).val();

        $('.conditional-field').hide();
        $('#trans_id, #voucher_no').val('');

        if (mode === 'Cheque') {

            $('#proof_div, #tansaction_div').show();

            $('#trans_label').html('Cheque No.' + star);
            $('#trans_id').attr('placeholder', 'Enter Cheque No.');

            $('#pay_date_l').html('Cheque Date' + star);
            $('#pay_amt_l').html('Cheque Amount' + star);

        } else if (mode === 'Online') {

            $('#tansaction_div, #bank_div').show();

            $('#trans_label').html('Transaction ID' + star);
            $('#trans_id').attr('placeholder', 'Enter Transaction ID');

            $('#pay_date_l').html('Payment Date' + star);
            $('#pay_amt_l').html('Payment Amount' + star);

        } else if (mode === 'Cash') {

            $('#reciept_div').show();

            $('#voucher_no').val('');
            $('#trans_id').val('');

            $('#pay_date_l').html('Payment Date' + star);
            $('#pay_amt_l').html('Payment Amount' + star);
        }

    });

    function funDel(id, imgname) {

        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        imgpath = '<?php echo $imgpath; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master_img.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&imgname=' + imgname + '&imgpath=' + imgpath,
                dataType: 'html',
                success: function(data) {
                    location.reload();
                }
            }); //ajax close
        } //confirm close
    } //fun close


    function load_ledger(account_id) {
        if (account_id > 0) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax_load_ledger.php',
                data: 'account_id=' + account_id,
                dataType: 'html',
                success: function(data) {
                    document.getElementById("ledger-data").innerHTML = data;
                }
            }); //ajax close
        }
    } //fun close


    function numberOnly(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\.|\s/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
    }
</script>

</html>