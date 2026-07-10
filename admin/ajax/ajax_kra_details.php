<?php

include("../../adminsession.php");

$emp_id = $_POST['emp_id'];
$month  = $_POST['month'];
$year   = $_POST['year'];

$current = $obj->executequery("
SELECT
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
    AND mk.month='$month'
    AND mk.year='$year'
    AND mk.companyid=$companyid
LEFT JOIN monthly_incentive mi
    ON mi.sales_executive_id=u.userid
    AND mi.month_name='$month'
    AND mi.year='$year'
    AND mi.companyid=$companyid
WHERE u.userid=$emp_id
")[0];

$slabs = [];
$slabRows = $obj->executequery("SELECT * FROM kra_config WHERE company_id=$companyid ORDER BY kra_key,min_value");
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
       AND MONTH(de.checkin_time)='$month'
       AND YEAR(de.checkin_time)='$year'

    WHERE
        rp.sales_executive_id='$emp_id'
        AND rp.companyid='$companyid'

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

    // Week 1 repeats in Week 3 and Week 5
    if (in_array(1, $weeks)) {
        $routeData['assigned_weeks'][3] = true;
        $routeData['assigned_weeks'][5] = true;
    }

    // Week 2 repeats in Week 4
    if (in_array(2, $weeks)) {
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
                        <span class="fw-bold"><?= number_format($current['behaviour_points'], 2) ?> / 2 pts</span>
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
                                        $range = ($slab['max_value'] <= $slab['min_value'])
                                            ? $slab['min_value'] . ' +'
                                            : $slab['min_value'] . ' - ' . $slab['max_value'];
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
</div>