<?php
include("appsession.php");

$limit = 5;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$account_id = isset($_POST['account_id']) ? $_POST['account_id'] : '';
$from_date = $_POST['from_date'] ?? '';
$to_date   = $_POST['to_date'] ?? '';
$sql = "
    SELECT 
        d.*,
        a.account_name,
        o.billno AS order_bill_no,
        o.invoice_no,

        CASE
            WHEN d.pay_type = 'opening' THEN 'Opening Balance'
            ELSE COALESCE(o.invoice_no, o.billno)
        END AS against_bill

    FROM transaction_entry d

    INNER JOIN account a
        ON a.account_id = d.account_id

    LEFT JOIN transaction_entry o
        ON o.transaction_id = d.ref_bill_id
        AND o.type = 'order'

    WHERE d.type = 'payment'

    AND (
        d.pay_type = 'opening'

        OR EXISTS (
            SELECT 1
            FROM route_counter rc

            INNER JOIN route_plan rp
                ON rp.batch_no = rc.batch_no
                AND rp.sales_executive_id = '$loginid'

            WHERE rc.account_id = d.account_id
              AND rc.is_active = 1
        )
    )
";

if (!empty($from_date) && !empty($to_date)) {
    $sql .= " 
        AND DATE(d.billdate) BETWEEN '$from_date' AND '$to_date'
    ";
}
if (!empty($account_id)) {
    $sql .= " AND d.account_id='$account_id'";
}

$sql .= "
    ORDER BY d.transaction_id DESC
    LIMIT $start, $limit
";

$res = $obj->executequery($sql);

if (!$res || count($res) == 0) {
    echo "";
    exit;
}

foreach ($res as $key) {

    $day   = date('d', strtotime($key['billdate']));
    $month = date('M', strtotime($key['billdate']));
    $year  = date('Y', strtotime($key['billdate']));
?>

    <div class="card border-0 shadow-lg mb-3 p-2">

        <table class="table table-sm table-borderless mb-0">

            <tr>

                <!-- DATE -->
                <td width="65px"
                    class="text-center"
                    onclick="openModal('<?php echo $key['transaction_id']; ?>');">

                    <h3 class="text-blue mb-0">
                        <?php echo $day; ?>
                    </h3>

                    <small>
                        <?php echo $month; ?><br>
                        <?php echo $year; ?>
                    </small>

                </td>

                <!-- PAYMENT DETAILS -->
                <td class="border-start"
                    onclick="openModal('<?php echo $key['transaction_id']; ?>');">

                    <p class="ms-1 mb-0">
                        <strong>Counter:</strong>
                        <?php echo htmlspecialchars_decode($key['account_name']); ?>
                    </p>

                    <p class="ms-1 mb-0">
                        <strong>Against:</strong>
                        <?php echo htmlspecialchars($key['against_bill'] ?? ''); ?>
                    </p>

                    <p class="ms-1 mb-0">
                        <strong>Paid Amount:</strong>
                        ₹<?php echo number_format((float)$key['grand_total'], 2); ?>
                    </p>

                    <p class="ms-1 mb-0">
                        <strong>Pay Mode:</strong>
                        <?php echo htmlspecialchars($key['paymode']); ?>
                    </p>

                </td>

                <!-- EDIT -->
                <td width="20px">

                    <div class="btn-group">

                        <?php if (date('Y-m-d', strtotime($key['billdate'])) == date('Y-m-d')) { ?>

                            <a class="btn btn-sm"
                                href="add_payment.php?transaction_id=<?php echo $key['transaction_id']; ?>">

                                <i class="bi bi-pencil"></i>

                            </a>

                        <?php } ?>

                    </div>

                </td>

            </tr>

        </table>

    </div>

<?php
}
?>