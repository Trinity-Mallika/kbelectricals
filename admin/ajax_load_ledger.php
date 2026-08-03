<?php include("../adminsession.php");

$account_id = isset($_POST['account_id']) ? $_POST['account_id'] : 0;
if (!empty($account_id)) {

    $ledger_array = [];

    // Opening Balance
    $opening_bal  = $obj->getvalfield(
        "account",
        "opening_balance",
        "account_id='$account_id'"
    );

    $opening_date = $obj->getvalfield(
        "account",
        "opening_date",
        "account_id='$account_id'"
    );

    $ledger_array[] = [
        "led_date"   => $opening_date,
        "led_time"   => "00:00:00",
        "particular" => "Opening Balance",
        "total"      => $opening_bal,
        "led_type"   => "debit"
    ];

    // Order Entries
    $purchase = $obj->executequery("SELECT * FROM transaction_entry WHERE account_id='$account_id' AND type='order' AND is_approved='1'");
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
    AND o.type='order'
WHERE p.account_id='$account_id'
AND p.type='payment'
");

    foreach ($payment as $row) {

        $particular = "Payment by " . $row['paymode'];

        if ($row['pay_type'] == "bill") {
            $particular .= " against Bill";
            if (!empty($row['invoice_no'])) {
                $particular .= " / Invoice No. " . $row['invoice_no'];
            }
        } else {
            $particular .= " against Opening Balance";
        }

        $particular .= " " . (
            $row['pay_status'] == 0
            ? "<br><span class='badge bg-warning text-dark'>Pending</span>"
            : "<br><span class='badge bg-success'>Approved</span>"
        );

        $ledger_array[] = [
            "led_date"   => $row['billdate'],
            "led_time"   => $row['createdate'],
            "particular" => $particular,
            "total"      => $row['grand_total'],
            "led_type"   => "credit"
        ];

        if ($row['cash_disc'] > 0) {
            $ledger_array[] = [
                "led_date"   => $row['billdate'],
                "led_time"   => $row['createdate'],
                "particular" => "Cash Discount against " .
                    ($row['pay_type'] == "bill"
                        ? (!empty($row['invoice_no']) ? "Invoice No. " . $row['invoice_no'] : "Bill")
                        : "Opening Balance"),
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

    <table class="table table-bordered table-hover table-sm">
        <thead class="table-dark">
            <tr>
                <th width="5%">#</th>
                <th width="18%">Date</th>
                <th>Particular</th>
                <th width="12%" class="text-end">Debit</th>
                <th width="12%" class="text-end">Credit</th>
                <th width="15%" class="text-end">Balance</th>
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
                    <td><?php echo $slno++; ?>.</td>

                    <td>
                        <?php
                        echo $obj->dateformatindia($row['led_date']);

                        if (!empty($row['led_time']) && $row['led_time'] != '00:00:00') {
                            echo "<br><small>" .
                                date('h:i A', strtotime($row['led_time'])) .
                                "</small>";
                        }
                        ?>
                    </td>

                    <td><?php echo $row['particular']; ?></td>

                    <td class="text-end">
                        <?php echo $debit > 0 ? number_format($debit, 2) : '-'; ?>
                    </td>

                    <td class="text-end">
                        <?php echo $credit > 0 ? number_format($credit, 2) : '-'; ?>
                    </td>

                    <td class="text-end">
                        <?php echo number_format(abs($balance), 2) . " " . $bal_type; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>

        <tfoot class="table-light">
            <tr>
                <th colspan="3" class="text-end">Grand Total</th>

                <th class="text-end">
                    <?php echo number_format($total_debit, 2); ?>
                </th>

                <th class="text-end">
                    <?php echo number_format($total_credit, 2); ?>
                </th>

                <th class="text-end">
                    <?php
                    echo number_format(abs($balance), 2)
                        . " " . ($balance >= 0 ? 'Dr' : 'Cr');
                    ?>
                </th>
            </tr>
        </tfoot>
    </table>
<?php
}

?>