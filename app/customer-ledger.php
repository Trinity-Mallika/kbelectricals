<?php include("appsession.php");
$title = "Customer Ledger List";
$fromdate = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d', strtotime('-7 days'));
$todate = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
$tblname = 'transaction_entry';
$tblpkey = 'transaction_id';
$account_id    = (isset($_GET["account_id"])) ? $obj->test_input($_GET["account_id"]) : 0;
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>
    <style>
        table tr th,
        table tr td {
            font-size: 13px;
        }

        tr.table-blue th {
            background-color: #124069 !important;
            color: white;
        }
    </style>
</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>
        <div class="container">
            <div class="row">
                <form>
                    <div class="col-12 mt-2">
                        <div class="card border-0 shadow-lg mb-3 p-2">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo $fromdate ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $todate ?>">
                                </div>
                                <div class="col-12 mb-2">
                                    <select class="form-select chosen-select" name="account_id" id="account_id">
                                        <option value="">Select a Counter</option>
                                        <?php
                                        $res = $obj->executequery("SELECT DISTINCT a.account_id, a.account_name,
                                   cm.common_name AS account_type, am.area_name
                            FROM route_plan rp
                            JOIN route_counter rc ON rc.batch_no = rp.batch_no
                            JOIN account a        ON a.account_id = rc.account_id
                            LEFT JOIN common_master cm ON cm.common_id = a.common_id AND cm.type = 'acc_type'
                            LEFT JOIN area_master am   ON am.area_id = a.area_id
                            WHERE rp.sales_executive_id = '$loginid'
                            ORDER BY a.account_name ASC
                        ");
                                        foreach ($res as $key) {
                                            echo "<option value='{$key['account_id']}'>"
                                                . "{$key['account_name']} [{$key['account_type']}] / {$key['area_name']}"
                                                . "</option>";
                                        } ?>
                                    </select>
                                    <script>
                                        document.getElementById('account_id').value = '<?= $account_id ?>';
                                    </script>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary w-100 btn-sm" onclick="return checkinputmaster('from_date,to_date,account_id');">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="col-12">
                    <a href="../admin/customer_ledger_mpdf.php?fromdate=<?= $fromdate ?>&todate=<?= $todate ?>&account_id=<?= $account_id; ?>&loginid=<?= $loginid ?>" target="_blank" class="btn btn-sm btn-danger float-end mb-2"><i class="fa fa-file-pdf"></i>Export PDF</a>
                </div>
                <div class="col-12 mb-4">

                    <div class="card-body">
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
                                <table class="table table-bordered table-hover table-sm border-dark">
                                    <thead class="text-center">
                                        <tr class="table-blue">
                                            <th class="fw-bold" width="5%">Sr No.</th>
                                            <th class="fw-bold " width="18%">Date</th>
                                            <th class="fw-bold">Particular</th>
                                            <th class="fw-bold text-end" width="12%">Debit</th>
                                            <th class="fw-bold text-end" width="12%">Credit</th>
                                            <th class="fw-bold text-end" width="15%">Balance</th>
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
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
    <script>
        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });
        });
    </script>
</body>


</html>