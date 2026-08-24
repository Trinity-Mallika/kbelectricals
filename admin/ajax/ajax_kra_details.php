<?php include("../../adminsession.php");

$emp_id = intval($_POST['emp_id']);
$month  = intval($_POST['month']);
$year   = intval($_POST['year']);

$classThresholds = [];
$thresholdRows = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config WHERE companyid=$companyid");
foreach ($thresholdRows as $t) $classThresholds[strtoupper(trim($t['class']))] = (float) $t['min_sales'];

function getBeatProductivity($obj, $companyid, $emp_id, $month, $year, $classThresholds)
{
    $sql = "
        SELECT
            r.route_name,
            r.day_of_week,
            rc.batch_no,
            a.account_id,
            a.account_name,
            a.class,
            COALESCE(SUM(
                CASE WHEN te.type = 'order' AND te.is_approved = 1 THEN te.grand_total ELSE 0 END
            ), 0) AS sales
        FROM route_plan rp
        INNER JOIN route_counter rc
            ON rc.batch_no = rp.batch_no
           AND rc.companyid = rp.companyid
           AND rc.is_active = 1
        INNER JOIN account a
            ON a.account_id = rc.account_id
        INNER JOIN route r
            ON r.batch_no = rp.batch_no
           AND r.companyid = rp.companyid
        LEFT JOIN transaction_entry te
            ON te.account_id = a.account_id
           AND te.companyid = $companyid
           AND MONTH(te.billdate) = $month
           AND YEAR(te.billdate) = $year
        WHERE rp.sales_executive_id = $emp_id
          AND rp.companyid = $companyid
        GROUP BY r.route_name, r.day_of_week, rc.batch_no, a.account_id, a.account_name, a.class
        ORDER BY r.route_name, a.account_name
    ";

    $rows = $obj->executequery($sql);

    $beatData = [];
    foreach ($rows as $row) {
        $route = $row['route_name'];

        if (!isset($beatData[$route])) {
            $beatData[$route] = [
                'day'      => $row['day_of_week'],
                'batch_no' => $row['batch_no'],
                'accounts' => [],
            ];
        }

        $class = strtoupper(trim($row['class']));
        $min   = $classThresholds[$class] ?? 0;
        $sales = (float) $row['sales'];

        $beatData[$route]['accounts'][] = [
            'account_name' => $row['account_name'],
            'class'        => $class,
            'sales'        => $sales,
            'min'          => $min,
            'active'       => ($min > 0 && $sales >= $min),
        ];
    }

    // roll up total/active/pct per beat
    foreach ($beatData as &$b) {
        $total  = count($b['accounts']);
        $active = count(array_filter($b['accounts'], fn($a) => $a['active']));
        $b['total_counters']  = $total;
        $b['active_counters'] = $active;
        $b['pct_active']      = $total > 0 ? round(($active / $total) * 100, 1) : 0;
    }
    unset($b);

    return $beatData;
}

// ── Product Mix: distinct brands sold per beat, this month ─────────────────
function getProductMix($obj, $companyid, $emp_id, $month, $year)
{
    $sql = "
        SELECT
            r.route_name,
            r.day_of_week,
            rc.batch_no,
            a.account_id,
            a.account_name,
            cm.cat_id   AS brand_id,
            cm.cat_name AS brand_name,
            COALESCE(SUM(td.total_amt), 0) AS brand_sales
        FROM route_plan rp
        INNER JOIN route_counter rc
            ON rc.batch_no = rp.batch_no
           AND rc.companyid = rp.companyid
           AND rc.is_active = 1
        INNER JOIN account a
            ON a.account_id = rc.account_id
        INNER JOIN route r
            ON r.batch_no = rp.batch_no
           AND r.companyid = rp.companyid
        INNER JOIN transaction_entry te
            ON te.account_id = a.account_id
           AND te.companyid = $companyid
           AND te.type = 'order'
           AND te.is_approved = 1
           AND MONTH(te.billdate) = $month
           AND YEAR(te.billdate) = $year
        INNER JOIN transaction_details td
            ON td.transaction_id = te.transaction_id
           AND td.companyid = $companyid
        INNER JOIN category_master cm
            ON cm.cat_id = td.brand_id
           AND cm.type = 'brand'
        WHERE rp.sales_executive_id = $emp_id
          AND rp.companyid = $companyid
        GROUP BY r.route_name, r.day_of_week, rc.batch_no, a.account_id, a.account_name, cm.cat_id, cm.cat_name
        ORDER BY r.route_name, a.account_name, cm.cat_name
    ";

    $rows = $obj->executequery($sql);

    $beatData  = [];
    $allBrands = [];
    $brandTotals = [];

    foreach ($rows as $row) {
        $route = $row['route_name'];
        $accId = $row['account_id'];

        if (!isset($beatData[$route])) {
            $beatData[$route] = [
                'day'      => $row['day_of_week'],
                'batch_no' => $row['batch_no'],
                'accounts' => [],
            ];
        }

        if (!isset($beatData[$route]['accounts'][$accId])) {
            $beatData[$route]['accounts'][$accId] = [
                'account_name' => $row['account_name'],
                'brands'       => [],
            ];
        }

        $beatData[$route]['accounts'][$accId]['brands'][] = [
            'brand_name' => $row['brand_name'],
            'sales'      => (float) $row['brand_sales'],
        ];

        $allBrands[$row['brand_id']] = true;

        if (!isset($brandTotals[$row['brand_name']])) {
            $brandTotals[$row['brand_name']] = 0;
        }
        $brandTotals[$row['brand_name']] += (float) $row['brand_sales'];
    }

    // roll up distinct brand count (and names) per beat
    foreach ($beatData as &$b) {
        $beatBrands = [];
        foreach ($b['accounts'] as $acc) {
            foreach ($acc['brands'] as $br) {
                $beatBrands[$br['brand_name']] = true;
            }
        }
        $b['brand_count'] = count($beatBrands);
        $b['brand_names'] = array_keys($beatBrands);
    }
    unset($b);

    arsort($brandTotals);

    return [
        'beats'        => $beatData,
        'total_brands' => count($allBrands),
        'brand_totals' => $brandTotals,
    ];
}

// ── Behavioural Aspects: manager-entered category scores ───────────────────
function getBehaviourScores($obj, $companyid, $emp_id, $month, $year)
{
    $sql = "
        SELECT
            kb.kra_behaviour_id,
            kb.name,
            kb.max_score,
            kbs.score
        FROM kra_behaviour kb
        LEFT JOIN kra_behaviour_score kbs
            ON kbs.behaviour_id = kb.kra_behaviour_id
           AND kbs.emp_id = $emp_id
           AND kbs.month = $month
           AND kbs.year = $year
           AND kbs.companyid = $companyid
        WHERE kb.companyid = $companyid
        ORDER BY kb.kra_behaviour_id
    ";

    return $obj->executequery($sql);
}

$productivityData = getBeatProductivity($obj, $companyid, $emp_id, $month, $year, $classThresholds);
$productMixData   = getProductMix($obj, $companyid, $emp_id, $month, $year);
$behaviourScores  = getBehaviourScores($obj, $companyid, $emp_id, $month, $year);

$current = $obj->executequery("SELECT
    u.userid,
    u.fullname,
    mk.visit_value,
    mk.productivity_value,
    mk.product_mix_value,
    mk.business_value,
    mk.behaviour_value,
    mk.visit_points,
    mk.productivity_points,
    mk.product_mix_points,
    mk.business_points,
    mk.behaviour_points,
    mk.total_score,
    mk.achievement_pct,
    mi.total_incentive
FROM user u
LEFT JOIN monthly_kra mk
    ON mk.emp_id=u.userid
    AND mk.month=$month
    AND mk.year=$year
    AND mk.companyid=$companyid
LEFT JOIN monthly_incentive mi
    ON mi.sales_executive_id=u.userid
    AND mi.month_name=$month
    AND mi.year=$year
    AND mi.companyid=$companyid
WHERE u.userid=$emp_id
")[0];

$slabs = [];
$slabRows = $obj->executequery("SELECT * FROM kra_config WHERE company_id=$companyid ORDER BY kra_key,min_value asc");
foreach ($slabRows as $s) $slabs[$s['kra_key']][] = $s;

function getAvgCounterVisit($obj, $companyid, $emp_id, $month, $year)
{
    $sql = "
   SELECT
    r.route_name,
    r.day_of_week,
    rp.week_number,
    a.account_id,
    a.account_name,
    de.checkin_time
    FROM route_plan rp

    INNER JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
       AND rc.companyid = rp.companyid
       AND rc.is_active = 1

    INNER JOIN account a
        ON a.account_id = rc.account_id

    INNER JOIN route r
        ON r.batch_no = rp.batch_no
       AND r.companyid = rp.companyid

    LEFT JOIN daily_entries de
        ON de.account_id = a.account_id
       AND de.createdby = rp.sales_executive_id
       AND MONTH(de.checkin_time)=$month
       AND YEAR(de.checkin_time)=$year

    WHERE
        rp.sales_executive_id=$emp_id
        AND rp.companyid=$companyid

    ORDER BY
        r.day_of_week,
        rc.sequence
    ";

    return $obj->executequery($sql);
}

$visitRows = getAvgCounterVisit(
    $obj,
    $companyid,
    $emp_id,
    $month,
    $year
);

$visitData = [];

foreach ($visitRows as $row) {

    $route = $row['route_name'];
    $accId = $row['account_id'];

    if (!isset($visitData[$route])) {

        $visitData[$route] = [
            'day' => $row['day_of_week'],
            'assigned_weeks' => [],
            'accounts' => []
        ];
    }

    $visitData[$route]['assigned_weeks'][$row['week_number']] = true;

    if (!isset($visitData[$route]['accounts'][$accId])) {

        $visitData[$route]['accounts'][$accId] = [
            'account_name' => $row['account_name'],
            'weeks' => []
        ];
    }

    if (!empty($row['checkin_time'])) {

        $week = ceil(date('j', strtotime($row['checkin_time'])) / 7);

        if ($week > 5) {
            $week = 5;
        }

        $visitData[$route]['accounts'][$accId]['weeks'][$week] = true;
    }
}

foreach ($visitData as &$routeData) {

    $weeks = array_keys($routeData['assigned_weeks']);

    // Week 1 circulates across ALL weeks
    if (in_array(1, $weeks)) {
        $routeData['assigned_weeks'][1] = true;
        $routeData['assigned_weeks'][2] = true;
        $routeData['assigned_weeks'][3] = true;
        $routeData['assigned_weeks'][4] = true;
        $routeData['assigned_weeks'][5] = true;
    }

    // Week 2 repeats only in Week 4
    if (in_array(2, $weeks)) {
        $routeData['assigned_weeks'][2] = true;
        $routeData['assigned_weeks'][4] = true;
    }

    ksort($routeData['assigned_weeks']);
}
unset($routeData);

?>

<div class="card">
    <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <span id="cardHeaderTitle"> <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?></span>
        <button type="button" class="btn btn-outline-light btn-sm btn-back" style="white-space: nowrap;">
            <i class="bi bi-arrow-left"></i> Back
        </button>
    </div>

    <div class="card-body kra-row">
        <div class="stat-grid w-100">
            <a href="#0" class="stat-card-link" data-target="avg-counter-visit" style="--c:#1a6ca8">
                <div class="stat-card">
                    <div class="title">Avg Counter Visit</div>
                    <span class="progress-label">Weightage: 20%</span>
                    <div class=" stat-icon opacity-100">
                        <img src="assets/img/run.png" alt="" width="55px">
                    </div>
                    <div class="stat-card-bottom">
                        <span><?= $current['visit_value'] ?></span>
                        <span class="fw-bold"><?= number_format($current['visit_points'], 2) ?> / 2 pts</span>
                    </div>
                </div>

            </a>
            <a href="#0" class="stat-card-link" data-target="beat-productivity" style="--c:#27ae60">
                <div class="stat-card">
                    <div class="title">Beat Productivity</div>
                    <span class="progress-label">Weightage: 20%</span>
                    <div class=" stat-icon opacity-100">
                        <img src="assets/img/productivity.png" alt="" width="55px">
                    </div>
                    <div class="stat-card-bottom">
                        <span><?= $current['productivity_value'] ?>%</span>
                        <span class="fw-bold"><?= number_format($current['productivity_points'], 2) ?> / 2 pts</span>
                    </div>
                </div>
            </a>
            <a href="#0" class="stat-card-link" data-target="product-mix" style="--c:#f39c12">
                <div class="stat-card">
                    <div class="title">Product Mix</div>
                    <span class="progress-label">Weightage: 20%</span>
                    <div class=" stat-icon opacity-100">
                        <img src="assets/img/product.png" alt="" width="55px">
                    </div>
                    <div class="stat-card-bottom">
                        <span><?= $current['product_mix_value'] ?></span>
                        <span class="fw-bold"><?= number_format($current['product_mix_points'], 2) ?> / 2 pts</span>
                    </div>
                </div>
            </a>
            <a href="#0" class="stat-card-link" data-target="overall-business" style="--c:#8e44ad">
                <div class="stat-card">
                    <div class="title">Overall Business</div>
                    <span class="progress-label">Weightage: 30%</span>
                    <div class=" stat-icon opacity-100">
                        <img src="assets/img/business.png" alt="" width="55px">
                    </div>
                    <div class="stat-card-bottom">
                        <span><?= $current['business_value'] ?></span>
                        <span class="fw-bold"><?= number_format($current['business_points'], 2) ?> / 2 pts</span>
                    </div>
                </div>
            </a>
            <a href="#0" class="stat-card-link" data-target="behavioural-aspects" style="--c:#e74c3c">
                <div class="stat-card">
                    <div class="title">Behavioural Aspects</div>
                    <span class="progress-label">Weightage: 10%</span>
                    <div class=" stat-icon opacity-100">
                        <img src="assets/img/communication.png" alt="" width="55px">
                    </div>
                    <div class="stat-card-bottom">
                        <span><?= $current['behaviour_value'] ?></span>
                        <span class="fw-bold"><?= number_format($current['behaviour_points'], 2) ?> / 5 pts</span>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>
<div class="kra-details">
    <div class="col-lg-12 avg-counter-visit" style="display:none;">
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                Avg Counter Visit
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= $current['visit_value'] ?></h4>
                                <small class="text-center">Actual Value</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= number_format($current['visit_points'], 2) ?> / 2</h4>
                                <small class="text-center">Points Earned</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1 " role="alert">
                            <div> <?php $visitScore = ($current['visit_points'] / 2) * 100; ?>
                                <h4 class="m-0 text-center"><?= round($visitScore) ?>%</h4>
                                <small class="text-center">KRA Score</small>
                            </div>
                        </div>

                        <div class="card card-body rounded-3 mt-2">
                            <h6>Scoring Slabs — Avg Counter Visit</h6>
                            <table class="table table-bordered table-sm mt-1 mb-0">
                                <tr class="table-dark">
                                    <th>Range</th>
                                    <th>Points</th>
                                    <th>Status</th>
                                </tr>
                                <?php foreach ($slabs['visit'] as $slab): ?>
                                    <?php
                                    $currentValue = $current['visit_value'];
                                    $isCurrent = false;

                                    if ($slab['max_value'] == 0) {
                                        $isCurrent = $currentValue >= $slab['min_value'];
                                    } else {
                                        $isCurrent =
                                            $currentValue >= $slab['min_value'] &&
                                            $currentValue < $slab['max_value'];
                                    }
                                    ?>
                                    <tr class="<?= $isCurrent ? 'table-primary' : '' ?>">
                                        <?php
                                        $minValue = (float)$slab['min_value'];
                                        $maxValue = (float)$slab['max_value'];

                                        $range = ($maxValue <= $minValue)
                                            ? $minValue . ' +'
                                            : $minValue . ' - ' . $maxValue;
                                        ?>

                                        <td><?= $range ?></td>
                                        <td>
                                            <?= $slab['points'] ?>
                                        </td>
                                        <td>
                                            <?php if ($isCurrent): ?>
                                                <span class="slab-badge">
                                                    Current
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <?php foreach ($visitData as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small>
                                            <strong> Assigned Weeks :
                                                <?= implode(", ", array_keys($data['assigned_weeks'])) ?></strong>
                                        </small>
                                        <span class="badge bg-warning text-dark">
                                            <?= htmlspecialchars($data['day']) ?>
                                        </span>
                                    </div>

                                </div>
                                <div class="card-body">
                                    <?php

                                    $totalCounter = count($data['accounts']);

                                    $visited = 0;

                                    foreach ($data['accounts'] as $acc) {

                                        foreach ($acc['weeks'] as $v) {

                                            if ($v) {

                                                $visited++;

                                                break;
                                            }
                                        }
                                    }

                                    ?>
                                    <div class="mb-2">
                                        <span class="badge bg-primary">
                                            Counters :
                                            <?= $totalCounter ?>
                                        </span>
                                        <span class="badge bg-success">
                                            Visited :
                                            <?= $visited ?>
                                        </span>
                                        <span class="badge bg-danger">
                                            Pending :
                                            <?= $totalCounter - $visited ?>
                                        </span>
                                    </div>
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Counter</th>
                                                <?php foreach (array_keys($data['assigned_weeks']) as $week): ?>
                                                    <th class="text-center">Week <?= $week ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['accounts'] as $acc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($acc['account_name']) ?></td>
                                                    <?php foreach (array_keys($data['assigned_weeks']) as $week): ?>
                                                        <td class="text-center">
                                                            <?= !empty($acc['weeks'][$week])
                                                                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                                                                : '<i class="bi bi-x-circle-fill text-danger"></i>'; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12 beat-productivity" style="display:none;">
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                Beat Wise Productivity
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= $current['productivity_value'] ?>%</h4>
                                <small class="text-center">Actual Value</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= number_format($current['productivity_points'], 2) ?> / 2</h4>
                                <small class="text-center">Points Earned</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div> <?php $prodScore = ($current['productivity_points'] / 2) * 100; ?>
                                <h4 class="m-0 text-center"><?= round($prodScore) ?>%</h4>
                                <small class="text-center">KRA Score</small>
                            </div>
                        </div>

                        <div class="card card-body rounded-3 mt-2">
                            <h6>Scoring Slabs — Beat Productivity</h6>
                            <table class="table table-bordered table-sm mt-1 mb-0">
                                <tr class="table-dark">
                                    <th>Range</th>
                                    <th>Points</th>
                                    <th>Status</th>
                                </tr>
                                <?php foreach ($slabs['productivity'] as $slab): ?>
                                    <?php
                                    $currentValue = (float) $current['productivity_value'];
                                    $isCurrent = false;

                                    if ($slab['max_value'] == 0) {
                                        $isCurrent = $currentValue >= $slab['min_value'];
                                    } else {
                                        $isCurrent =
                                            $currentValue >= $slab['min_value'] &&
                                            $currentValue < $slab['max_value'];
                                    }
                                    ?>
                                    <tr class="<?= $isCurrent ? 'table-primary' : '' ?>">
                                        <?php
                                        $range = ($slab['max_value'] <= $slab['min_value'])
                                            ? $slab['min_value'] . '% +'
                                            : $slab['min_value'] . '% - ' . $slab['max_value'] . '%';
                                        ?>
                                        <td><?= $range ?></td>
                                        <td><?= $slab['points'] ?></td>
                                        <td>
                                            <?php if ($isCurrent): ?>
                                                <span class="slab-badge">Current</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                            <small class="text-muted d-block mt-2">
                                Activation threshold: A Class ₹<?= number_format($classThresholds['A'] ?? 0) ?>/mo,
                                B Class ₹<?= number_format($classThresholds['B'] ?? 0) ?>/mo,
                                C Class ₹<?= number_format($classThresholds['C'] ?? 0) ?>/mo
                            </small>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <?php foreach ($productivityData as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small>
                                            <strong>% Active: <?= $data['pct_active'] ?>%</strong>
                                        </small>
                                        <span class="badge bg-warning text-dark">
                                            <?= htmlspecialchars($data['day']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="badge bg-primary">
                                            Counters : <?= $data['total_counters'] ?>
                                        </span>
                                        <span class="badge bg-success">
                                            Active : <?= $data['active_counters'] ?>
                                        </span>
                                        <span class="badge bg-danger">
                                            Inactive : <?= $data['total_counters'] - $data['active_counters'] ?>
                                        </span>
                                        <?php if ($data['pct_active'] < 70) { ?>
                                            <span class="badge bg-secondary">Below 70% Target</span>
                                        <?php } ?>
                                    </div>
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Counter</th>
                                                <th class="text-center">Class</th>
                                                <th class="text-end">Sales (Month)</th>
                                                <th class="text-end">Threshold</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['accounts'] as $acc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($acc['account_name']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info text-white"><?= $acc['class'] ?: '—' ?></span>
                                                    </td>
                                                    <td class="text-end">₹<?= number_format($acc['sales']) ?></td>
                                                    <td class="text-end">
                                                        <?= $acc['min'] > 0 ? '₹' . number_format($acc['min']) : '—' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $acc['active']
                                                            ? '<span class="badge bg-success">Active</span>'
                                                            : '<span class="badge bg-danger">Inactive</span>'; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 product-mix" style="display:none;">
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                Product Mix
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= $current['product_mix_value'] ?></h4>
                                <small class="text-center">Distinct Brands Sold</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= number_format($current['product_mix_points'], 2) ?> / 2</h4>
                                <small class="text-center">Points Earned</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div> <?php $mixScore = ($current['product_mix_points'] / 2) * 100; ?>
                                <h4 class="m-0 text-center"><?= round($mixScore) ?>%</h4>
                                <small class="text-center">KRA Score</small>
                            </div>
                        </div>

                        <div class="card card-body rounded-3 mt-2">
                            <h6>Scoring Slabs — Product Mix</h6>
                            <table class="table table-bordered table-sm mt-1 mb-0">
                                <tr class="table-dark">
                                    <th>Brands</th>
                                    <th>Points</th>
                                    <th>Status</th>
                                </tr>
                                <?php foreach ($slabs['product_mix'] as $slab): ?>
                                    <?php
                                    $currentValue = (float) $current['product_mix_value'];
                                    $isCurrent = false;

                                    if ($slab['max_value'] == 0 || is_null($slab['max_value'])) {
                                        $isCurrent = $currentValue >= $slab['min_value'];
                                    } else {
                                        $isCurrent =
                                            $currentValue >= $slab['min_value'] &&
                                            $currentValue < $slab['max_value'];
                                    }
                                    ?>
                                    <tr class="<?= $isCurrent ? 'table-primary' : '' ?>">
                                        <?php
                                        $minValue = (float) $slab['min_value'];
                                        $maxValue = (float) $slab['max_value'];

                                        $range = ($maxValue <= $minValue)
                                            ? $minValue . ' +'
                                            : $minValue . ' - ' . $maxValue;
                                        ?>
                                        <td><?= $range ?></td>
                                        <td><?= $slab['points'] ?></td>
                                        <td>
                                            <?php if ($isCurrent): ?>
                                                <span class="slab-badge">Current</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <strong>Overall Brand-wise Sales (This Employee, This Month)</strong>
                            </div>
                            <div class="card-body">
                                <?php if (empty($productMixData['brand_totals'])) { ?>
                                    <div class="alert alert-secondary mb-0">No brand sales recorded this month.</div>
                                <?php } else { ?>
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Brand</th>
                                                <th class="text-end">Total Sales (Month)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($productMixData['brand_totals'] as $brandName => $total): ?>
                                                <tr>
                                                    <td>
                                                        <?= htmlspecialchars($brandName) ?>
                                                    </td>
                                                    <td class="text-end">₹<?= number_format($total) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php } ?>
                            </div>
                        </div>

                        <?php foreach ($productMixData['beats'] as $route => $data): ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small>
                                            <strong>
                                                Brands Sold: <?= $data['brand_count'] ?>
                                                (<?= htmlspecialchars(implode(', ', $data['brand_names'])) ?>)
                                            </strong>
                                        </small>
                                        <span class="badge bg-warning text-dark">
                                            <?= htmlspecialchars($data['day']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Counter</th>
                                                <th>Brands Sold</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['accounts'] as $acc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($acc['account_name']) ?></td>
                                                    <td>
                                                        <span>
                                                            <?php
                                                            $brandList = [];

                                                            foreach ($acc['brands'] as $br) {
                                                                $brandList[] = htmlspecialchars($br['brand_name']) .
                                                                    ' (₹' . number_format($br['sales']) . ')';
                                                            }

                                                            echo implode(', ', $brandList);
                                                            ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($productMixData['beats'])) { ?>
                            <div class="alert alert-secondary">No brand sales recorded for this employee this month.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 overall-business" style="display:none;">
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                Overall Business
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= $current['business_value'] ?></h4>
                                <small class="text-center">Actual Value</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= number_format($current['business_points'], 2) ?> / 2</h4>
                                <small class="text-center">Points Earned</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div> <?php $bizScore = ($current['business_points'] / 2) * 100; ?>
                                <h4 class="m-0 text-center"><?= round($bizScore) ?>%</h4>
                                <small class="text-center">KRA Score</small>
                            </div>
                        </div>

                        <div class="card card-body rounded-3 mt-2">
                            <h6>Scoring Slabs — Overall Business (Lakh)</h6>
                            <table class="table table-bordered table-sm mt-1 mb-0">
                                <tr class="table-dark">
                                    <th>Range</th>
                                    <th>Points</th>
                                    <th>Status</th>
                                </tr>
                                <?php foreach ($slabs['business'] as $slab): ?>
                                    <?php
                                    $currentValue = (float) $current['business_value'];
                                    $isCurrent = false;

                                    if ($slab['max_value'] == 0 || is_null($slab['max_value'])) {
                                        $isCurrent = $currentValue >= $slab['min_value'];
                                    } else {
                                        $isCurrent =
                                            $currentValue >= $slab['min_value'] &&
                                            $currentValue < $slab['max_value'];
                                    }
                                    ?>
                                    <tr class="<?= $isCurrent ? 'table-primary' : '' ?>">
                                        <?php
                                        $range = ($slab['max_value'] <= $slab['min_value'])
                                            ? number_format($slab['min_value']) . ' +'
                                            : number_format($slab['min_value']) . ' - ' . number_format($slab['max_value']);
                                        ?>
                                        <td><?= $range ?></td>
                                        <td><?= $slab['points'] ?></td>
                                        <td>
                                            <?php if ($isCurrent): ?>
                                                <span class="slab-badge">Current</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <?php foreach ($productivityData as $route => $data): ?>
                            <?php $beatTotal = array_sum(array_column($data['accounts'], 'sales')); ?>
                            <div class="card mb-2">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= htmlspecialchars($route) ?></strong>
                                        <small>
                                            <strong>Total Invoice: ₹<?= number_format($beatTotal) ?></strong>
                                        </small>
                                        <span class="badge bg-warning text-dark">
                                            <?= htmlspecialchars($data['day']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Counter</th>
                                                <th class="text-center">Class</th>
                                                <th class="text-end">Invoice Amount (Month)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['accounts'] as $acc): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars_decode($acc['account_name']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info text-white"><?= $acc['class'] ?: '—' ?></span>
                                                    </td>
                                                    <td class="text-end">₹<?= number_format($acc['sales']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 behavioural-aspects" style="display:none;">
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                Behavioural Aspects
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= $current['behaviour_value'] ?></h4>
                                <small class="text-center">Actual Value</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div>
                                <h4 class="m-0 text-center"><?= number_format($current['behaviour_points'], 2) ?> / 5</h4>
                                <small class="text-center">Points Earned</small>
                            </div>
                        </div>
                        <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                            <div> <?php $behScore = ($current['behaviour_points'] / 5) * 100; ?>
                                <h4 class="m-0 text-center"><?= round($behScore) ?>%</h4>
                                <small class="text-center">KRA Score</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card mb-2">
                            <div class="card-header bg-primary text-white">
                                <strong>Category-wise Score</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Category</th>
                                            <th class="text-center">Score</th>
                                            <th class="text-center">Max</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($behaviourScores as $b): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($b['name']) ?></td>
                                                <td class="text-center">
                                                    <?= is_null($b['score']) ? '—' : number_format($b['score'], 2) ?>
                                                </td>
                                                <td class="text-center"><?= number_format($b['max_score'], 2) ?></td>
                                                <td class="text-center">
                                                    <?php if (is_null($b['score'])) { ?>
                                                        <span class="badge bg-secondary">Not Scored</span>
                                                    <?php } elseif ($b['score'] >= $b['max_score']) { ?>
                                                        <span class="badge bg-success">Full Marks</span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-warning text-dark">Partial</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>