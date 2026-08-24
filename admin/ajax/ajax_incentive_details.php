<?php include("../../adminsession.php");

$emp_id = intval($_POST['emp_id']);
$month  = intval($_POST['month']);
$year   = intval($_POST['year']);
$start = date("$year-$month-01");
$end   = date("Y-m-t", strtotime($start));


function getVisitRows($obj, $companyid, $emp_id, $start, $end)
{
    return $obj->executequery("
        SELECT date, visit_count AS val
        FROM daily_productivity
        WHERE emp_id = $emp_id
          AND date BETWEEN '$start' AND '$end'
          AND companyid = $companyid
        ORDER BY date
    ");
}

function getSalesRows($obj, $companyid, $emp_id, $start, $end)
{
    return $obj->executequery("
        SELECT DATE(billdate) AS date, SUM(grand_total) / 100000 AS val
        FROM transaction_entry
        WHERE createdby = $emp_id
          AND type = 'order'
          AND is_approved = 1
          AND billdate BETWEEN '$start' AND '$end'
          AND companyid = $companyid
        GROUP BY DATE(billdate)
        ORDER BY date
    ");
}

function getMixRows($obj, $companyid, $emp_id, $start, $end)
{
    return $obj->executequery("
        SELECT DATE(t.billdate) AS date, COUNT(DISTINCT td.product_id) AS val
        FROM transaction_entry t
        INNER JOIN transaction_details td ON td.transaction_id = t.transaction_id
        WHERE t.createdby = $emp_id
          AND t.type = 'order'
          AND t.is_approved = 1
          AND t.billdate BETWEEN '$start' AND '$end'
          AND t.companyid = $companyid
        GROUP BY DATE(t.billdate)
        ORDER BY date
    ");
}

function getCollectionRows($obj, $companyid, $emp_id, $start, $end)
{
    return $obj->executequery("
        SELECT
            o.transaction_id,
            o.account_id,
            a.account_name,
            o.billdate,
            p.first_payment,
            DATEDIFF(p.first_payment, o.billdate) AS val
        FROM transaction_entry o
        INNER JOIN account a ON a.account_id = o.account_id
        INNER JOIN (
            SELECT ref_bill_id, MIN(createdate) AS first_payment
            FROM transaction_entry
            WHERE type = 'payment' AND pay_status = 1
              AND companyid = $companyid
            GROUP BY ref_bill_id
        ) p ON p.ref_bill_id = o.transaction_id
        WHERE o.type = 'order'
          AND o.createdby = $emp_id
          AND o.is_approved = 1
          AND o.billdate BETWEEN '$start' AND '$end'
          AND o.companyid = $companyid
        ORDER BY o.billdate
    ");
}

/* replicate Model::calculateIncentiveFlexible(), returning the full working
   (slabs, qualified/non split, rates) instead of just the final amount */
function breakdownIncentive($obj, $type, $rows, $companyid)
{
    $slabs = $obj->executequery("
        SELECT min_value, max_value, amount
        FROM incentive_slabs
        WHERE type = '$type' AND company_id = $companyid
        ORDER BY min_value ASC
    ");

    $amtFor = function ($value) use ($obj, $type, $companyid) {
        if ($value === null) return 0;
        $row = $obj->executequery("
            SELECT amount FROM incentive_slabs
            WHERE type = '$type' AND company_id = $companyid
              AND $value >= min_value
              AND ($value < max_value OR max_value IS NULL)
            ORDER BY min_value DESC
        ");
        return isset($row[0]['amount']) ? (float) $row[0]['amount'] : 0;
    };

    if (empty($rows)) {
        return [
            'rows' => [],
            'slabs' => $slabs,
            'min_val' => null,
            'all_qualified' => false,
            'qualified' => [],
            'non_qualified' => [],
            'avg_q' => 0,
            'avg_n' => 0,
            'rate_q' => 0,
            'rate_n' => 0,
            'amt_q' => 0,
            'amt_n' => 0,
            'total' => 0,
        ];
    }

    $minValRow = $obj->executequery("
        SELECT MIN(min_value) AS min_val
        FROM incentive_slabs
        WHERE type = '$type' AND amount > 0 AND company_id = $companyid
    ");
    $min_val = isset($minValRow[0]['min_val']) ? (float) $minValRow[0]['min_val'] : null;

    $qualified = [];
    $non       = [];
    foreach ($rows as $r) {
        $v = (float) $r['val'];
        if ($min_val !== null && $v >= $min_val) {
            $qualified[] = $r;
        } else {
            $non[] = $r;
        }
    }

    $all_qualified = (count($non) === 0);

    $avg_q = count($qualified) ? array_sum(array_column($qualified, 'val')) / count($qualified) : null;
    $avg_n = count($non)       ? array_sum(array_column($non, 'val'))       / count($non)       : null;

    $rate_q = $amtFor($avg_q);
    $amt_q  = $rate_q * count($qualified);
    $rate_n = $all_qualified ? 0 : $amtFor($avg_n);
    $amt_n  = $rate_n * count($non);

    return [
        'rows' => $rows,
        'slabs' => $slabs,
        'min_val' => $min_val,
        'all_qualified' => $all_qualified,
        'qualified' => $qualified,
        'non_qualified' => $non,
        'avg_q' => $avg_q ?? 0,
        'avg_n' => $avg_n ?? 0,
        'rate_q' => $rate_q,
        'rate_n' => $rate_n,
        'amt_q' => $amt_q,
        'amt_n' => $amt_n,
        'total' => $amt_q + $amt_n,
    ];
}

function getSalesByRoute($obj, $companyid, $emp_id, $month, $year)
{
    $sql = "
        SELECT
            r.route_name, r.day_of_week, rc.batch_no,
            a.account_id, a.account_name,
            COALESCE(SUM(
                CASE WHEN te.type = 'order' AND te.is_approved = 1 AND te.createdby = $emp_id
                     THEN te.grand_total ELSE 0 END
            ), 0) AS sales
        FROM route_plan rp
        INNER JOIN route_counter rc ON rc.batch_no = rp.batch_no AND rc.companyid = rp.companyid AND rc.is_active = 1
        INNER JOIN account a ON a.account_id = rc.account_id
        INNER JOIN route r ON r.batch_no = rp.batch_no AND r.companyid = rp.companyid
        LEFT JOIN transaction_entry te ON te.account_id = a.account_id
            AND te.companyid = $companyid
            AND MONTH(te.billdate) = $month AND YEAR(te.billdate) = $year
        WHERE rp.sales_executive_id = $emp_id AND rp.companyid = $companyid
        GROUP BY r.route_name, r.day_of_week, rc.batch_no, a.account_id, a.account_name
        ORDER BY r.route_name, a.account_name
    ";

    $rows = $obj->executequery($sql);
    $beats = [];
    foreach ($rows as $row) {
        $route = $row['route_name'];
        if (!isset($beats[$route])) {
            $beats[$route] = ['day' => $row['day_of_week'], 'accounts' => [], 'total' => 0];
        }
        $beats[$route]['accounts'][] = ['account_name' => $row['account_name'], 'sales' => (float) $row['sales']];
        $beats[$route]['total'] += (float) $row['sales'];
    }
    return $beats;
}

function getMixInvoicesByRoute($obj, $companyid, $emp_id, $month, $year)
{
    $sql = "
        SELECT
            r.route_name, r.day_of_week, rc.batch_no,
            a.account_id, a.account_name,
            t.transaction_id, t.billdate,
            td.product_id, td.qty, td.total_amt
        FROM route_plan rp
        INNER JOIN route_counter rc ON rc.batch_no = rp.batch_no AND rc.companyid = rp.companyid AND rc.is_active = 1
        INNER JOIN account a ON a.account_id = rc.account_id
        INNER JOIN route r ON r.batch_no = rp.batch_no AND r.companyid = rp.companyid
        INNER JOIN transaction_entry t ON t.account_id = a.account_id
            AND t.companyid = $companyid
            AND t.type = 'order' AND t.is_approved = 1 AND t.createdby = $emp_id
            AND MONTH(t.billdate) = $month AND YEAR(t.billdate) = $year
        INNER JOIN transaction_details td ON td.transaction_id = t.transaction_id
        WHERE rp.sales_executive_id = $emp_id AND rp.companyid = $companyid
        ORDER BY r.route_name, a.account_name, t.billdate
    ";

    $rows = $obj->executequery($sql);
    $beats = [];
    foreach ($rows as $row) {
        $route = $row['route_name'];
        $accId = $row['account_id'];
        if (!isset($beats[$route])) {
            $beats[$route] = ['day' => $row['day_of_week'], 'accounts' => []];
        }
        if (!isset($beats[$route]['accounts'][$accId])) {
            $beats[$route]['accounts'][$accId] = ['account_name' => $row['account_name'], 'lines' => [], 'distinct_products' => []];
        }
        $beats[$route]['accounts'][$accId]['lines'][] = [
            'transaction_id' => $row['transaction_id'],
            'billdate'       => $row['billdate'],
            'product_id'     => $row['product_id'],
            'qty'            => $row['qty'],
            'amt'            => (float) $row['total_amt'],
        ];
        $beats[$route]['accounts'][$accId]['distinct_products'][$row['product_id']] = true;
    }
    foreach ($beats as &$b) {
        $beatProducts = [];
        foreach ($b['accounts'] as $acc) {
            foreach (array_keys($acc['distinct_products']) as $pid) $beatProducts[$pid] = true;
        }
        $b['distinct_product_count'] = count($beatProducts);
    }
    unset($b);
    return $beats;
}

function getVisitByRoute($obj, $companyid, $emp_id, $month, $year)
{
    $sql = "
        SELECT r.route_name, r.day_of_week, a.account_id, a.account_name, de.checkin_time
        FROM route_plan rp
        INNER JOIN route_counter rc ON rc.batch_no = rp.batch_no AND rc.companyid = rp.companyid AND rc.is_active = 1
        INNER JOIN account a ON a.account_id = rc.account_id
        INNER JOIN route r ON r.batch_no = rp.batch_no AND r.companyid = rp.companyid
        LEFT JOIN daily_entries de ON de.account_id = a.account_id
            AND de.createdby = rp.sales_executive_id
            AND MONTH(de.checkin_time) = $month AND YEAR(de.checkin_time) = $year
        WHERE rp.sales_executive_id = $emp_id AND rp.companyid = $companyid
        ORDER BY r.day_of_week, rc.sequence
    ";

    $rows = $obj->executequery($sql);
    $beats = [];
    foreach ($rows as $row) {
        $route = $row['route_name'];
        $accId = $row['account_id'];
        if (!isset($beats[$route])) {
            $beats[$route] = ['day' => $row['day_of_week'], 'accounts' => []];
        }
        if (!isset($beats[$route]['accounts'][$accId])) {
            $beats[$route]['accounts'][$accId] = ['account_name' => $row['account_name'], 'visit_dates' => []];
        }
        if (!empty($row['checkin_time'])) {
            $d = date('Y-m-d', strtotime($row['checkin_time']));
            $beats[$route]['accounts'][$accId]['visit_dates'][$d] = true;
        }
    }
    foreach ($beats as &$b) {
        $b['total_counters']   = count($b['accounts']);
        $b['visited_counters'] = count(array_filter($b['accounts'], fn($a) => count($a['visit_dates']) > 0));
    }
    unset($b);
    return $beats;
}

function getRouteMap($obj, $companyid, $emp_id)
{
    $rows = $obj->executequery("
        SELECT rc.account_id, r.route_name, r.day_of_week
        FROM route_plan rp
        INNER JOIN route_counter rc ON rc.batch_no = rp.batch_no AND rc.companyid = rp.companyid AND rc.is_active = 1
        INNER JOIN route r ON r.batch_no = rp.batch_no AND r.companyid = rp.companyid
        WHERE rp.sales_executive_id = $emp_id AND rp.companyid = $companyid
    ");
    $map = [];
    foreach ($rows as $r) $map[$r['account_id']] = ['route_name' => $r['route_name'], 'day' => $r['day_of_week']];
    return $map;
}


$visitRows      = getVisitRows($obj, $companyid, $emp_id, $start, $end);
$salesRows      = getSalesRows($obj, $companyid, $emp_id, $start, $end);
$mixRows        = getMixRows($obj, $companyid, $emp_id, $start, $end);
$collectionRows = getCollectionRows($obj, $companyid, $emp_id, $start, $end);

$visitBD = breakdownIncentive($obj, 'visit', $visitRows, $companyid);
$salesBD = breakdownIncentive($obj, 'sales', $salesRows, $companyid);
$mixBD   = breakdownIncentive($obj, 'product_mix', $mixRows, $companyid);
$collBD  = breakdownIncentive($obj, 'collection', $collectionRows, $companyid);

$grandTotal = $visitBD['total'] + $salesBD['total'] + $mixBD['total'] + $collBD['total'];

$salesByRoute = getSalesByRoute($obj, $companyid, $emp_id, $month, $year);
$mixByRoute   = getMixInvoicesByRoute($obj, $companyid, $emp_id, $month, $year);
$visitByRoute = getVisitByRoute($obj, $companyid, $emp_id, $month, $year);
$routeMap     = getRouteMap($obj, $companyid, $emp_id);

foreach ($collectionRows as &$cr) {
    $cr['route_name'] = $routeMap[$cr['account_id']]['route_name'] ?? '—';
    $cr['day']        = $routeMap[$cr['account_id']]['day'] ?? '—';
}
unset($cr);
$collectionByRoute = [];
foreach ($collectionRows as $cr) {
    $route = $cr['route_name'];
    if (!isset($collectionByRoute[$route])) $collectionByRoute[$route] = ['day' => $cr['day'], 'rows' => []];
    $collectionByRoute[$route]['rows'][] = $cr;
}

$stored = $obj->executequery("
    SELECT visit_incentive, sales_incentive, product_mix_incentive, collection_incentive, total_incentive
    FROM monthly_incentive
    WHERE sales_executive_id = $emp_id AND month_name = $month AND year = $year AND companyid = $companyid
");
$stored = $stored[0] ?? null;

$empRow  = $obj->executequery("SELECT fullname FROM user WHERE userid = $emp_id");
$empName = $empRow[0]['fullname'] ?? '';

function renderSlabTable($slabs, $unitSuffix = '')
{
    echo '<table class="table table-bordered table-sm mt-1 mb-0"><tr class="table-dark"><th>Range</th><th>Amount</th></tr>';
    foreach ($slabs as $s) {
        $min = (float) $s['min_value'];
        $max = $s['max_value'];
        $range = ($max === null || $max === '')
            ? '>= ' . $min . $unitSuffix
            : $min . $unitSuffix . ' – ' . $max . $unitSuffix;
        echo '<tr><td>' . $range . '</td><td>₹' . number_format((float) $s['amount']) . '</td></tr>';
    }
    echo '</table>';
}
?>

<div class="card">
    <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <span>Incentive — <?= htmlspecialchars($empName) ?> — <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?></span>
        <button type="button" class="btn btn-outline-light btn-sm btn-back" style="white-space: nowrap;">
            <i class="bi bi-arrow-left"></i> Back
        </button>
    </div>

    <div class="card-body kra-row">
        <div class="stat-grid w-100">
            <a href="#0" class="stat-card-link" data-target="inc-visit" style="--c:#1a6ca8">
                <div class="stat-card">
                    <div class="title">Avg Visit/Beat</div>
                    <span class="progress-label"><?= count($visitRows) ?> day(s)</span>
                    <div class="stat-card-bottom">
                        <span>&nbsp;</span>
                        <span class="fw-bold">₹<?= number_format($visitBD['total']) ?></span>
                    </div>
                </div>
            </a>
            <a href="#0" class="stat-card-link" data-target="inc-sales" style="--c:#27ae60">
                <div class="stat-card">
                    <div class="title">Avg Sales/Beat</div>
                    <span class="progress-label"><?= count($salesRows) ?> day(s)</span>
                    <div class="stat-card-bottom">
                        <span>&nbsp;</span>
                        <span class="fw-bold">₹<?= number_format($salesBD['total']) ?></span>
                    </div>
                </div>
            </a>
            <a href="#0" class="stat-card-link" data-target="inc-mix" style="--c:#f39c12">
                <div class="stat-card">
                    <div class="title">Product Mix/Beat</div>
                    <span class="progress-label"><?= count($mixRows) ?> day(s)</span>
                    <div class="stat-card-bottom">
                        <span>&nbsp;</span>
                        <span class="fw-bold">₹<?= number_format($mixBD['total']) ?></span>
                    </div>
                </div>
            </a>
            <a href="#0" class="stat-card-link" data-target="inc-collection" style="--c:#8e44ad">
                <div class="stat-card">
                    <div class="title">Avg Collection Days</div>
                    <span class="progress-label"><?= count($collectionRows) ?> bill(s)</span>
                    <div class="stat-card-bottom">
                        <span>&nbsp;</span>
                        <span class="fw-bold">₹<?= number_format($collBD['total']) ?></span>
                    </div>
                </div>
            </a>
            <div style="--c:#343a40">
                <div class="stat-card">
                    <div class="title">Total Incentive</div>
                    <span class="progress-label">All KRAs combined</span>
                    <div class="stat-card-bottom">
                        <span>&nbsp;</span>
                        <span class="fw-bold">₹<?= number_format($grandTotal) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($stored && (int) $stored['total_incentive'] !== (int) round($grandTotal)) { ?>
            <div class="alert alert-warning mt-2 mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Stored total (₹<?= number_format($stored['total_incentive']) ?>) differs from the live
                recompute above (₹<?= number_format($grandTotal) ?>). Re-run <code>processMonthlyIncentive()</code>
                for this employee/month to refresh <code>monthly_incentive</code>.
            </div>
        <?php } ?>
    </div>
</div>

<div class="kra-details">

    <!-- ── Visit ─────────────────────────────────────────────────────── -->
    <div class="col-lg-12 inc-visit" style="display:none;">
        <div class="card">
            <div class="card-header text-white">Avg Visit/Beat/Month — Incentive Calculation</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-body rounded-3">
                            <h6>Slabs — Rs/Beat Qualified</h6>
                            <?php renderSlabTable($visitBD['slabs']); ?>
                        </div>
                        <div class="card card-body rounded-3 mt-2">
                            <h6>How the total was built</h6>
                            <p class="mb-1">Qualification threshold: <strong><?= $visitBD['min_val'] !== null ? $visitBD['min_val'] : '—' ?></strong> visits/day.</p>
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <th>Group</th>
                                    <th>Days</th>
                                    <th>Avg</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                                <tr class="table-success">
                                    <td>Qualified (≥ threshold)</td>
                                    <td><?= count($visitBD['qualified']) ?></td>
                                    <td><?= round($visitBD['avg_q'], 2) ?></td>
                                    <td>₹<?= number_format($visitBD['rate_q']) ?></td>
                                    <td>₹<?= number_format($visitBD['amt_q']) ?></td>
                                </tr>
                                <?php if (!$visitBD['all_qualified']) { ?>
                                    <tr class="table-danger">
                                        <td>Below threshold</td>
                                        <td><?= count($visitBD['non_qualified']) ?></td>
                                        <td><?= round($visitBD['avg_n'], 2) ?></td>
                                        <td>₹<?= number_format($visitBD['rate_n']) ?></td>
                                        <td>₹<?= number_format($visitBD['amt_n']) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td>₹<?= number_format($visitBD['total']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card mb-2">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    Overall Visit Qualification
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-end">Visits</th>
                                            <th class="text-center">Qualifies</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($visitRows as $r) {
                                            $q = $visitBD['min_val'] !== null && (float) $r['val'] >= $visitBD['min_val'];
                                        ?>
                                            <tr>
                                                <td><?= $obj->dateformatindia($r['date']) ?></td>
                                                <td class="text-end"><?= $r['val'] ?></td>
                                                <td class="text-center"><?= $q ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if (empty($visitRows)) { ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No visit days recorded this month.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php foreach ($visitByRoute as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small><strong>Visited: <?= $data['visited_counters'] ?> / <?= $data['total_counters'] ?></strong></small>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($data['day']) ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Counter</th>
                                                <th>Visit Dates</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['accounts'] as $acc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($acc['account_name']) ?></td>
                                                    <td>
                                                        <?php if (empty($acc['visit_dates'])) { ?>
                                                            <span class="badge bg-danger">Not visited</span>
                                                        <?php } else { ?>
                                                            <?= implode(', ', array_keys($acc['visit_dates'])) ?>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($visitByRoute)) { ?>
                            <div class="alert alert-secondary">No routes assigned to this employee.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Sales ─────────────────────────────────────────────────────── -->
    <div class="col-lg-12 inc-sales" style="display:none;">
        <div class="card">
            <div class="card-header text-white">Avg Sales/Beat — Incentive Calculation</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-body rounded-3">
                            <h6>Slabs — Rs/Beat Qualified (Lakh)</h6>
                            <?php renderSlabTable($salesBD['slabs'], 'L'); ?>
                        </div>
                        <div class="card card-body rounded-3 mt-2">
                            <h6>How the total was built</h6>
                            <p class="mb-1">Qualification threshold: <strong><?= $salesBD['min_val'] !== null ? $salesBD['min_val'] : '—' ?> Lakh</strong>/day.</p>
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <th>Group</th>
                                    <th>Days</th>
                                    <th>Avg (L)</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                                <tr class="table-success">
                                    <td>Qualified (≥ threshold)</td>
                                    <td><?= count($salesBD['qualified']) ?></td>
                                    <td><?= round($salesBD['avg_q'], 2) ?></td>
                                    <td>₹<?= number_format($salesBD['rate_q']) ?></td>
                                    <td>₹<?= number_format($salesBD['amt_q']) ?></td>
                                </tr>
                                <?php if (!$salesBD['all_qualified']) { ?>
                                    <tr class="table-danger">
                                        <td>Below threshold</td>
                                        <td><?= count($salesBD['non_qualified']) ?></td>
                                        <td><?= round($salesBD['avg_n'], 2) ?></td>
                                        <td>₹<?= number_format($salesBD['rate_n']) ?></td>
                                        <td>₹<?= number_format($salesBD['amt_n']) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td>₹<?= number_format($salesBD['total']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Sales (Lakh)</th>
                                    <th class="text-center">Qualifies</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesRows as $r) {
                                    $q = $salesBD['min_val'] !== null && (float) $r['val'] >= $salesBD['min_val'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['date']) ?></td>
                                        <td class="text-end"><?= round($r['val'], 2) ?></td>
                                        <td class="text-center"><?= $q ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($salesRows)) { ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No approved orders this month.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <h6 class="mt-3">Route-wise Sales Breakdown This Month</h6>
                        <?php foreach ($salesByRoute as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small><strong>Total: ₹<?= number_format($data['total']) ?></strong></small>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($data['day']) ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Counter</th>
                                                <th class="text-end">Sales (Month)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['accounts'] as $acc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($acc['account_name']) ?></td>
                                                    <td class="text-end">₹<?= number_format($acc['sales']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($salesByRoute)) { ?>
                            <div class="alert alert-secondary">No routes assigned to this employee.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Product Mix ───────────────────────────────────────────────── -->
    <div class="col-lg-12 inc-mix" style="display:none;">
        <div class="card">
            <div class="card-header text-white">Product Mix/Beat — Incentive Calculation</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-body rounded-3">
                            <h6>Slabs — Rs/Beat Qualified</h6>
                            <?php renderSlabTable($mixBD['slabs'], ' products'); ?>
                        </div>
                        <div class="card card-body rounded-3 mt-2">
                            <h6>How the total was built</h6>
                            <p class="mb-1">Qualification threshold: <strong><?= $mixBD['min_val'] !== null ? $mixBD['min_val'] : '—' ?></strong> distinct products/day.</p>
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <th>Group</th>
                                    <th>Days</th>
                                    <th>Avg</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                                <tr class="table-success">
                                    <td>Qualified (≥ threshold)</td>
                                    <td><?= count($mixBD['qualified']) ?></td>
                                    <td><?= round($mixBD['avg_q'], 2) ?></td>
                                    <td>₹<?= number_format($mixBD['rate_q']) ?></td>
                                    <td>₹<?= number_format($mixBD['amt_q']) ?></td>
                                </tr>
                                <?php if (!$mixBD['all_qualified']) { ?>
                                    <tr class="table-danger">
                                        <td>Below threshold</td>
                                        <td><?= count($mixBD['non_qualified']) ?></td>
                                        <td><?= round($mixBD['avg_n'], 2) ?></td>
                                        <td>₹<?= number_format($mixBD['rate_n']) ?></td>
                                        <td>₹<?= number_format($mixBD['amt_n']) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td>₹<?= number_format($mixBD['total']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Distinct Products</th>
                                    <th class="text-center">Qualifies</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mixRows as $r) {
                                    $q = $mixBD['min_val'] !== null && (float) $r['val'] >= $mixBD['min_val'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['date']) ?></td>
                                        <td class="text-end"><?= $r['val'] ?></td>
                                        <td class="text-center"><?= $q ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($mixRows)) { ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No approved orders this month.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <div class="alert alert-secondary mt-2 mb-2">
                            <i class="bi bi-info-circle"></i>
                            Product-name lookup isn't wired up yet below — it currently shows <code>product_id</code>.
                            Tell me your product-name table/column and I'll swap it in.
                        </div>

                        <h6 class="mt-3">Route-wise Invoice Detail This Month</h6>
                        <?php foreach ($mixByRoute as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small><strong>Distinct Products: <?= $data['distinct_product_count'] ?></strong></small>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($data['day']) ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($data['accounts'] as $acc): ?>
                                        <h6 class="mt-1"><?= htmlspecialchars_decode($acc['account_name']) ?></h6>
                                        <table class="table table-bordered table-sm mb-2">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Date</th>
                                                    <th>Product</th>
                                                    <th class="text-end">Qty</th>
                                                    <th class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($acc['lines'] as $line): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($line['transaction_id']) ?></td>
                                                        <td><?= htmlspecialchars($line['billdate']) ?></td>
                                                        <td>Product #<?= htmlspecialchars($line['product_id']) ?></td>
                                                        <td class="text-end"><?= htmlspecialchars($line['qty']) ?></td>
                                                        <td class="text-end">₹<?= number_format($line['amt']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($mixByRoute)) { ?>
                            <div class="alert alert-secondary">No invoices with products found for this employee this month.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Collection ────────────────────────────────────────────────── -->
    <div class="col-lg-12 inc-collection" style="display:none;">
        <div class="card">
            <div class="card-header text-white">Avg Collection Days/Beat — Incentive Calculation</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-body rounded-3">
                            <h6>Slabs — Rs/Beat Qualified</h6>
                            <?php renderSlabTable($collBD['slabs'], ' days'); ?>
                        </div>
                        <div class="card card-body rounded-3 mt-2">
                            <h6>How the total was built</h6>
                            <p class="mb-1">
                                Qualification threshold: <strong><?= $collBD['min_val'] !== null ? $collBD['min_val'] : '—' ?></strong> days
                                (only bills with a matched payment count here).
                            </p>
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <th>Group</th>
                                    <th>Bills</th>
                                    <th>Avg Days</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                                <tr class="table-success">
                                    <td>Qualified (≥ threshold)</td>
                                    <td><?= count($collBD['qualified']) ?></td>
                                    <td><?= round($collBD['avg_q'], 2) ?></td>
                                    <td>₹<?= number_format($collBD['rate_q']) ?></td>
                                    <td>₹<?= number_format($collBD['amt_q']) ?></td>
                                </tr>
                                <?php if (!$collBD['all_qualified']) { ?>
                                    <tr class="table-danger">
                                        <td>Below threshold</td>
                                        <td><?= count($collBD['non_qualified']) ?></td>
                                        <td><?= round($collBD['avg_n'], 2) ?></td>
                                        <td>₹<?= number_format($collBD['rate_n']) ?></td>
                                        <td>₹<?= number_format($collBD['amt_n']) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="fw-bold">
                                    <td colspan="4">Total</td>
                                    <td>₹<?= number_format($collBD['total']) ?></td>
                                </tr>
                            </table>
                            <small class="text-muted d-block mt-2">
                                Note: your incentive plan describes this slab as "&lt; 30 / 30–45 / 45–60 days"
                                (fewer days = better), but <code>calculateIncentiveFlexible()</code> treats a
                                <em>higher</em> value as qualifying — same rule as the other three KRAs. Worth
                                confirming <code>incentive_slabs</code> for <code>type='collection'</code> is
                                seeded to match your intent.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Account</th>
                                    <th>Bill Date</th>
                                    <th>First Payment</th>
                                    <th class="text-end">Days</th>
                                    <th class="text-center">Qualifies</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($collectionRows as $r) {
                                    $q = $collBD['min_val'] !== null && (float) $r['val'] >= $collBD['min_val'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars_decode($r['account_name']) ?></td>
                                        <td><?= htmlspecialchars($r['billdate']) ?></td>
                                        <td><?= htmlspecialchars($r['first_payment']) ?></td>
                                        <td class="text-end"><?= $r['val'] ?></td>
                                        <td class="text-center"><?= $q ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (empty($collectionRows)) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No bills with a matched payment yet this month.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <h6 class="mt-3">Route-wise Collection Breakdown</h6>
                        <?php foreach ($collectionByRoute as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small><strong>Bills: <?= count($data['rows']) ?></strong></small>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($data['day']) ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Account</th>
                                                <th>Bill Date</th>
                                                <th>Paid</th>
                                                <th class="text-end">Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['rows'] as $r): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($r['account_name']) ?></td>
                                                    <td><?= htmlspecialchars($r['billdate']) ?></td>
                                                    <td><?= htmlspecialchars($r['first_payment']) ?></td>
                                                    <td class="text-end"><?= $r['val'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($collectionByRoute)) { ?>
                            <div class="alert alert-secondary">No matched bills to group by route.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>