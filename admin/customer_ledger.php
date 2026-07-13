<?php
include("../adminsession.php");
$title = "Customer Ledger";
$pagename = "customer_ledger.php";
$module = "Customer Ledger";
$submodule = "Customer Ledger";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d');
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');
$account_id   = isset($_GET['account_id'])   ? $_GET['account_id']   : 0;
if ($account_id > 0) {
    $sqledit = $obj->select_record("account", ["account_id" => $account_id]);
    $account_name = $sqledit['account_name'];
    $owner_name = $sqledit['owner_name'];
    $mobile_no = $sqledit['mobile_no'];
    $o_mobile_no = $sqledit['o_mobile_no'];
} else {
    $account_name = "";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->

    <style>
        #printArea {
            font-family: 'Inter', sans-serif;
            color: #161616;
            font-size: 18px !important;
        }

        #printArea h1,
        #printArea h2 {
            font-family: 'Sora', sans-serif;
            color: #0d6efd;
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
                <div class="col-lg-12 mb-2">
                    <form>
                        <div class="card mt-3">
                            <div class="card-header text-white">
                                <?php echo $module; ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <strong><label for="fromdate">From Date <span
                                                    class="text-danger">*</span></label></strong>
                                        <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate"
                                            value="<?php echo $fromdate; ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong><label for="todate">To Date <span
                                                    class="text-danger">*</span></label></strong>
                                        <input type="date" class="form-control form-control-sm" name="todate" id="todate"
                                            value="<?php echo $todate; ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <strong><label>Customer/Counter Name <span
                                                    class="text-danger">*</span></label></strong>
                                        <select name="account_id" id="account_id" class="chosen-select form-control form-control-sm">
                                            <option value="">--Select Counter--</option>
                                            <?php
                                            $sql = $obj->executequery("SELECT account_id, account_name FROM account ORDER BY account_name ASC");
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
                                        <input type="submit" class="btn btn-primary btn-sm" name="search" value="Search" onClick="return checkinputmaster('from_date,to_date,account_id')">
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
                            <div class="float-end">
                                <a href="customer_ledger_mpdf.php?fromdate=<?= $fromdate ?>&todate=<?= $todate ?>&account_id=<?= $account_id; ?>&loginid=<?= $loginid ?>" target="_blank" class="btn btn-sm btn-danger"><i class="fa fa-file-pdf"></i>Export PDF</a>
                            </div>
                        </div>
                        <div class="card-body" id="printArea">
                            <?php if (!empty($account_id)) {

                                $ledger_array = [];

                                $opening_bal = $obj->get_opening_ledger($account_id, $fromdate);

                                $ledger_array[] = [
                                    "led_date"   => $fromdate,
                                    "led_time"   => "00:00:00",
                                    "particular" => "Opening Balance",
                                    "total"      => abs($opening_bal),
                                    "led_type"   => ($opening_bal >= 0) ? "debit" : "credit"
                                ];

                                // Order Entries
                                $purchase = $obj->executequery("SELECT * FROM transaction_entry WHERE account_id='$account_id' AND type='order' AND is_approved='1' AND billdate BETWEEN '$fromdate' AND '$todate' ORDER BY billdate");
                                foreach ($purchase as $row) {
                                    $ledger_array[] = [
                                        "led_date"   => $row['billdate'],
                                        "led_time"   => $row['createdate'],
                                        "particular" => "By Order Entry " . $row['billno'] . " / Invoice No. " . $row['invoice_no'],
                                        "total"      => $row['invoice_amt'],
                                        "led_type"   => "debit"
                                    ];
                                }

                                // Payments
                                $payment = $obj->executequery("
    SELECT 
        p.*,
        o.invoice_no,
        o.billno AS order_billno
    FROM transaction_entry p
    LEFT JOIN transaction_entry o
        ON o.transaction_id = p.ref_bill_id
        AND o.type = 'order'
    WHERE p.account_id='$account_id'
      AND p.type='payment'
      AND p.pay_status=1  AND p.billdate BETWEEN '$fromdate' AND '$todate'
");
                                foreach ($payment as $row) {

                                    $ledger_array[] = [
                                        "led_date"   => $row['billdate'],
                                        "led_time"   => $row['createdate'],
                                        "particular" => "Payment by " . $row['paymode']
                                            . " against " . ucfirst($row['pay_type'])
                                            . (!empty($row['invoice_no']) ? " / Invoice No. " . $row['invoice_no'] : ""),
                                        "total"      => $row['grand_total'],
                                        "led_type"   => "credit"
                                    ];
                                    if ($row['cash_disc'] > 0) {
                                        $ledger_array[] = [
                                            "led_date"   => $row['billdate'],
                                            "led_time"   => $row['createdate'],
                                            "particular" => "Cash Disc" . " against " . ucfirst($row['pay_type']),
                                            "total"      => $row['cash_disc'],
                                            "led_type"   => "credit"
                                        ];
                                    }
                                }

                                usort($ledger_array, function ($a, $b) {
                                    $t1 = strtotime($a['led_date'] . ' ' . $a['led_time']);
                                    $t2 = strtotime($b['led_date'] . ' ' . $b['led_time']);
                                    return $t2 <=> $t1;
                                });
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm border-dark  border-bottom" id="myTable">
                                        <thead class="text-center">
                                            <tr>
                                                <th colspan="6" style="text-align: center;">
                                                    <div style="font-size:20px; font-weight:bold"><?php echo $account_name; ?></div>
                                                    Owner : <?php echo $owner_name; ?><br>
                                                    Mobile : <?php echo $mobile_no; ?> , <?php echo $o_mobile_no; ?><br>
                                                    Date Range :
                                                    <?php echo $obj->dateformatindia($fromdate) . " - " . $obj->dateformatindia($todate) ?>
                                                    </span>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th class="fw-bold text-dark" width="5%">Sr No.</th>
                                                <th class="fw-bold text-dark " width="18%">Date</th>
                                                <th class="fw-bold text-dark">Particular</th>
                                                <th class="fw-bold text-dark" width="12%" class="text-end">Debit</th>
                                                <th class="fw-bold text-dark" width="12%" class="text-end">Credit</th>
                                                <th class="fw-bold text-dark" width="15%" class="text-end">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $slno = 1;
                                            $balance = 0;
                                            $total_debit = 0;
                                            $total_credit = 0;

                                            foreach ($ledger_array as $row) {

                                                $debit = 0;
                                                $credit = 0;

                                                if ($row['led_type'] == 'debit') {

                                                    $debit = round($row['total'], 2);
                                                    $total_debit += $debit;
                                                    $balance += $debit;
                                                } else {

                                                    $credit = round($row['total'], 2);
                                                    $total_credit += $credit;
                                                    $balance -= $credit;
                                                }

                                                $bal_type = ($balance >= 0) ? 'Dr' : 'Cr';

                                            ?>
                                                <tr>
                                                    <td class="text-dark fw-semibold text-center"><?php echo $slno++; ?>.</td>

                                                    <td class="text-dark fw-semibold text-center">
                                                        <?php
                                                        echo $obj->dateformatindia($row['led_date']);

                                                        if (!empty($row['led_time']) && $row['led_time'] != '00:00:00') {
                                                            echo "<br><small>" .
                                                                date('h:i A', strtotime($row['led_time'])) .
                                                                "</small>";
                                                        }
                                                        ?>
                                                    </td>

                                                    <td class="text-dark fw-semibold"><?php echo $row['particular']; ?></td>

                                                    <td class="text-end text-dark fw-semibold">
                                                        <?php echo $debit > 0 ? 'Rs. ' . number_format($debit, 2) : '-'; ?>
                                                    </td>

                                                    <td class="text-end text-dark fw-semibold">
                                                        <?php echo $credit > 0 ? 'Rs. ' . number_format($credit, 2) : '-'; ?>
                                                    </td>

                                                    <td class="text-end text-dark fw-semibold">
                                                        <?php echo 'Rs. ' . number_format(abs($balance), 2) . " " . $bal_type; ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>

                                        <tfoot class="table-light border-dark">
                                            <tr>
                                                <th colspan="3" class="text-end border-dark text-dark fw-bold ">Grand Total</th>

                                                <th class="text-end border-dark text-dark fw-bold">
                                                    <?php echo 'Rs. ' . number_format($total_debit, 2); ?>
                                                </th>

                                                <th class="text-end border-dark text-dark fw-bold">
                                                    <?php echo 'Rs. ' . number_format($total_credit, 2); ?>
                                                </th>

                                                <th class="text-end border-dark text-dark fw-bold">
                                                    <?php
                                                    echo 'Rs. ' . number_format(abs($balance), 2)
                                                        . " " . ($balance >= 0 ? 'Dr' : 'Cr');
                                                    ?>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="vertical-align: bottom;">
                                                <div style="float:left;">
                                                    <strong>Export Date :</strong> <?= date("d F Y h:i A"); ?><br>
                                                    <strong>Exported By :</strong>
                                                    <?= $obj->getvalfield("user", "fullname", "userid='$loginid'"); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="float:right; text-align:center;">
                                                    <img src="uploaded/sign.png" width="120"><br>
                                                    <strong><?= $obj->getvalfield("company_setting", "company_name", "1=1"); ?></strong><br>
                                                    <small>Authorized Signatory</small>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content close-->
</body>
<?php include('component/script.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });
</script>

</html>