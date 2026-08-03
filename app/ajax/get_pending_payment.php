<?php
include("../../action.php");

$route_plan_ids = $_POST['route_plan_ids'] ?? [];

$route_plan_ids = array_map('intval', $route_plan_ids);

if (empty($route_plan_ids)) {
    exit;
}

$route_plan_ids_sql = implode(',', $route_plan_ids);
$companyid = (int)$_POST['companyid'];

$sql = "SELECT
            rc.sequence,
            a.account_id,
            a.account_name,

            (
                a.opening_balance
                +
                COALESCE(SUM(
                    CASE
                        WHEN te.type='order'
                             AND te.is_approved=1
                             AND te.invoice_no<>''
                        THEN te.invoice_amt
                        ELSE 0
                    END
                ),0)
                -
                COALESCE(SUM(
                    CASE
                        WHEN te.type='payment'
                             AND te.pay_status=1
                        THEN te.grand_total + IFNULL(te.cash_disc,0)
                        ELSE 0
                    END
                ),0)
            ) AS pending_amount

        FROM route_plan rp

        INNER JOIN route_counter rc
            ON rc.batch_no = rp.batch_no
           AND rc.companyid = rp.companyid
           AND rc.is_active = 1

        INNER JOIN account a
            ON a.account_id = rc.account_id

        LEFT JOIN transaction_entry te
            ON te.account_id = a.account_id
           AND te.companyid = '$companyid'

        WHERE rp.route_planid IN ($route_plan_ids_sql)
          AND rp.companyid = '$companyid'

        GROUP BY
            rc.sequence,
            a.account_id,
            a.account_name,
            a.opening_balance

        HAVING pending_amount > 0

        ORDER BY
            rc.sequence,
            a.account_name";

$res = $obj->executequery($sql);

if (empty($res)) {
    echo '
    <div class="text-center py-5">
        <i class="bi bi-check-circle fs-1 text-success"></i>
        <h6 class="mt-2 mb-0">No Pending Payment</h6>
        <small class="text-muted">All counters are clear.</small>
    </div>';
    exit;
}

foreach ($res as $row) {
?>
    <a href="pending_order.php?account_id=<?= $row['account_id'] ?>">
        <div class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
            <h6 class="mb-0 text-blue">
                <i class="bi bi-person"></i>
                &nbsp;<?= htmlspecialchars_decode($row['account_name']) ?>
            </h6>

            <h6 class="mb-0 text-danger">
                <i class="bi bi-currency-rupee"></i>
                <?= number_format($row['pending_amount'], 2) ?>
            </h6>
        </div>
    </a>
<?php
}
?>