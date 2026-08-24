<?php
include("../adminsession.php");

$stat     = $_GET['stat'] ?? '';
$fromdate = $_GET['fromdate'] ?? date('Y-m-d');
$todate   = $_GET['todate'] ?? date('Y-m-d');

$allowed = [
    'quotation_total',
    'order_total',
    'order_conversion',
    'dispatch_order_total',
    'dispatch_order_pending',
    'dispatch_order_cleared',
    'dispatch_items_pending'
];

if (!in_array($stat, $allowed)) {
    die('<div class="alert alert-danger">Invalid Request</div>');
}

$heading = '';
$columns = [];
$rows = [];

switch ($stat) {
    case 'quotation_total':

        $heading = "Quotation Details";
        $columns = ['Quotation No.', 'Customer Name', 'Date', 'Amount', 'With GST'];

        $sql = "
        SELECT
            te.billno,
            a.account_name,
            te.billdate,
            te.grand_total,
            te.is_gst
        FROM transaction_entry te
        LEFT JOIN account a ON a.account_id=te.account_id
        WHERE te.type='quotation'
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        ORDER BY te.billdate DESC";

        foreach ($obj->executequery($sql) as $r) {
            $rows[] = [
                $r['billno'],
                ucfirst($r['account_name']),
                $obj->dateformatindia($r['billdate']),
                number_format($r['grand_total'], 2),
                $r['is_gst'] ? 'With GST' : 'No'
            ];
        }

        break;

    case 'order_total':

        $heading = "Order Details";
        $columns = ['Order No', 'Customer', 'Date', 'Amount', 'Status'];

        $sql = "
        SELECT
            te.billno,
            a.account_name,
            te.billdate,
            te.grand_total,
            te.is_approved
        FROM transaction_entry te
        LEFT JOIN account a ON a.account_id=te.account_id
        WHERE te.type='order'
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        ORDER BY te.billdate DESC";

        foreach ($obj->executequery($sql) as $r) {
            $rows[] = [
                $r['billno'],
                $r['account_name'],
                $obj->dateformatindia($r['billdate']),
                number_format($r['grand_total'], 2),
                $r['is_approved'] ? 'Approved' : 'Pending'
            ];
        }

        break;

    case 'order_conversion':

        $heading = "Quotation Converted to Orders";
        $columns = ['Order No.', 'Quotation No.', 'Customer', 'Date'];

        $sql = "
        SELECT
            te.billno,
            tei.transaction_id,
            tei.billno AS quotation_no,
            a.account_name,
            te.billdate
        FROM transaction_entry te
        LEFT JOIN transaction_entry tei ON tei.parent_transaction_id=te.transaction_id AND tei.type='quotation'
        LEFT JOIN account a ON a.account_id=te.account_id 
        WHERE te.type='quotation'
        AND te.conversion_status=1
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        ORDER BY te.billdate DESC";

        foreach ($obj->executequery($sql) as $r) {

            $rows[] = [
                $r['billno'],
                $r['quotation_no'],
                $r['account_name'],
                $obj->dateformatindia($r['billdate'])
            ];
        }

        break;

    case 'dispatch_order_total':

        $heading = "Dispatch Orders";
        $columns = ['Order No', 'Customer', 'Date', 'Status'];

        $sql = "
        SELECT
            te.transaction_id,
            te.billno,
            te.billdate,
            a.account_name,
            SUM(td.qty) qty,
            SUM(dh.qty) dispatch_qty
        FROM transaction_entry te
        INNER JOIN transaction_details td
            ON td.transaction_id=te.transaction_id
        INNER JOIN dispatch_history dh
            ON dh.tran_detail_id=te.tran_detail_id
        LEFT JOIN account a
            ON a.account_id=te.account_id
        WHERE te.type='order'
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        GROUP BY te.transaction_id
        ORDER BY te.billdate DESC";

        foreach ($obj->executequery($sql) as $r) {

            if ($r['dispatch_qty'] == 0)
                $status = "Pending";
            elseif ($r['dispatch_qty'] < $r['qty'])
                $status = "Partial";
            else
                $status = "Completed";

            $rows[] = [
                $r['billno'],
                $r['account_name'],
                $r['billdate'],
                $status
            ];
        }

        break;


    case 'dispatch_order_pending':

        $heading = "Pending Dispatch Orders";
        $columns = ['Order No', 'Customer', 'Date', 'Pending Qty'];

        $sql = "
        SELECT
            te.billno,
            te.billdate,
            a.account_name,
            SUM(td.qty-td.dispatch_qty) pending_qty
        FROM transaction_entry te
        INNER JOIN transaction_details td
            ON td.transaction_id=te.transaction_id
        LEFT JOIN account a
            ON a.account_id=te.account_id
        WHERE te.type='order'
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        GROUP BY te.transaction_id
        HAVING pending_qty>0
        ORDER BY te.billdate";

        foreach ($obj->executequery($sql) as $r) {
            $rows[] = [
                $r['billno'],
                $r['account_name'],
                $r['billdate'],
                $r['pending_qty']
            ];
        }

        break;



    /*-----------------------------------
    COMPLETED DISPATCH
    -----------------------------------*/
    case 'dispatch_order_cleared':

        $heading = "Completed Dispatch Orders";
        $columns = ['Order No', 'Customer', 'Date'];

        $sql = "
        SELECT
            te.billno,
            te.billdate,
            a.account_name
        FROM transaction_entry te
        INNER JOIN transaction_details td
            ON td.transaction_id=te.transaction_id
        LEFT JOIN account a
            ON a.account_id=te.account_id
        WHERE te.type='order'
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        GROUP BY te.transaction_id
        HAVING SUM(td.qty)=SUM(td.dispatch_qty)
        ORDER BY te.billdate DESC";

        foreach ($obj->executequery($sql) as $r) {
            $rows[] = [
                $r['billno'],
                $r['account_name'],
                $r['billdate']
            ];
        }

        break;



    /*-----------------------------------
    PENDING ITEMS
    -----------------------------------*/
    case 'dispatch_items_pending':

        $heading = "Pending Dispatch Items";
        $columns = ['Order No', 'Product', 'Pending Qty'];

        $sql = "
        SELECT
            te.billno,
            pm.product_name,
            (td.qty-td.dispatch_qty) pending_qty
        FROM transaction_entry te
        INNER JOIN transaction_details td
            ON td.transaction_id=te.transaction_id
        LEFT JOIN product_master pm
            ON pm.product_id=td.product_id
        WHERE te.type='order'
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        AND td.dispatch_qty<td.qty
        ORDER BY te.billdate DESC";

        foreach ($obj->executequery($sql) as $r) {
            $rows[] = [
                $r['billno'],
                $r['product_name'],
                $r['pending_qty']
            ];
        }

        break;
}
?>

<div class="p-3">

    <div class="d-flex justify-content-between mb-3">
        <h6 class="mb-0"><?php echo $heading; ?></h6>
        <span class="badge bg-primary"><?php echo count($rows); ?> Records</span>
    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-hover table-sm align-middle">

            <thead class="table-light">

                <tr>

                    <?php foreach ($columns as $c) { ?>
                        <th><?php echo $c; ?></th>
                    <?php } ?>

                </tr>

            </thead>

            <tbody>

                <?php

                if (empty($rows)) {
                    echo '<tr><td colspan="' . count($columns) . '" class="text-center text-muted">No records found.</td></tr>';
                } else {
                    foreach ($rows as $r) {
                        echo "<tr>";

                        foreach ($r as $v) {
                            echo "<td>" . htmlspecialchars($v) . "</td>";
                        }

                        echo "</tr>";
                    }
                }

                ?>

            </tbody>

        </table>

    </div>

</div>