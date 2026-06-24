<?php
include("../../action.php");
$route_plan_ids = $_POST['route_plan_ids'];

$route_plan_ids = array_map('intval', $route_plan_ids);

$route_plan_ids_sql = implode(',', $route_plan_ids);
$companyid     = (int)$_POST['companyid'];

$sql = "SELECT
    x.sequence,
    x.account_id,
    x.account_name,
    SUM(x.pending_amount) AS pending_amount

FROM
(
    SELECT
        rc.sequence,
        a.account_id,
        a.account_name,
        a.opening_balance AS pending_amount

    FROM route_plan rp

    INNER JOIN route_counter rc
        ON rc.batch_no = rp.batch_no

    INNER JOIN account a
        ON a.account_id = rc.account_id

    WHERE rp.route_planid IN ($route_plan_ids_sql)
      AND rp.companyid = '$companyid'
      AND rc.companyid = '$companyid'
      AND rc.is_active = 1


    UNION ALL


    SELECT
        rc.sequence,
        a.account_id,
        a.account_name,
        (
            (
                CASE
                    WHEN o.invoice_no IS NOT NULL AND o.invoice_no <> ''
                    THEN o.invoice_amt
                    ELSE o.grand_total
                    AND  o.is_approved=1
                END
            )
            -
            COALESCE
            (
                (
                    SELECT SUM(p.grand_total)
                    FROM transaction_entry p
                    WHERE p.ref_bill_id = o.transaction_id
                      AND p.type = 'payment'
                      AND p.companyid = '$companyid'
                ),
                0
            )
        ) AS pending_amount

    FROM route_plan rp
    INNER JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
    INNER JOIN account a
        ON a.account_id = rc.account_id

    INNER JOIN transaction_entry o
        ON o.account_id = a.account_id
        AND o.type = 'order'
        AND o.is_approved = '1'
        AND o.companyid = '$companyid'

    WHERE rp.route_planid IN ($route_plan_ids_sql)
      AND rp.companyid = '$companyid'
      AND rc.companyid = '$companyid'
      AND rc.is_active = 1

) x

GROUP BY
    x.sequence,
    x.account_id,
    x.account_name

HAVING SUM(x.pending_amount) > 0

ORDER BY x.sequence ASC";

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
        <div
            class="card attendance-card border-0 shadow-lg mb-2 d-flex justify-content-between flex-row align-items-center">
            <h6 class="mb-0 text-blue">
                <i class="bi bi-person"></i>
                &nbsp;<?= htmlspecialchars($row['account_name']) ?>
            </h6>

            <h5 class="mb-0 text-danger">
                <i class="bi bi-currency-rupee"></i>
                <?= number_format($row['pending_amount'], 2) ?>
            </h5>
        </div>
    </a>
<?php
}
?>