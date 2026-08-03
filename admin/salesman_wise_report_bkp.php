<?php include("../adminsession.php");
$title = "KRA Report";
$pagename = "salesman_wise_report.php";
$module = "KRA Report";
$submodule = "Sales KRA Report";
$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : 0;
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$emp_id = isset($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;

/* ─── helpers ────────────────────────────────────────────────────────────── */
function pct_bar($pct, $label = '')
{
    if ($pct >= 75) {
        $cls = 'success';
    } elseif ($pct >= 50) {
        $cls = 'warning';
    } elseif ($pct >= 40) {
        $cls = 'danger';
    } else {
        $cls = 'secondary';
    }
    return '<div class="progress kra-mini-bar" title="' . $label . '">
              <div class="progress-bar bg-' . $cls . '" style="width:' . min(100, $pct) . '%"></div>
            </div>';
}
function eligibility_badge($pct)
{
    if ($pct >= 75)     return '✓ Eligible for Increment + Incentive';
    if ($pct >= 50)     return '↑ Eligible for Increment Only';
    if ($pct >= 40)     return '✗ Not Eligible';
    return '⚠ Performance Improvement Letter';
}

$empFilter = $emp_id > 0 ? "AND u.userid = $emp_id" : "";
$execRows  = $obj->executequery("
                                SELECT u.userid, u.fullname,
                                       mk.visit_value, mk.productivity_value, mk.product_mix_value,
                                       mk.business_value, mk.behaviour_value,
                                       mk.visit_points, mk.productivity_points, mk.product_mix_points,
                                       mk.business_points, mk.behaviour_points,
                                       mk.total_score, mk.achievement_pct,
                                       mi.visit_incentive, mi.sales_incentive,
                                       mi.product_mix_incentive, mi.collection_incentive,
                                       mi.total_incentive
                                FROM user u
                                LEFT JOIN monthly_kra mk ON mk.emp_id = u.userid
                                    AND mk.month = '$month' AND mk.year = '$year'
                                    AND mk.companyid = $companyid
                                LEFT JOIN monthly_incentive mi ON mi.sales_executive_id = u.userid
                                    AND mi.month_name = '$month' AND mi.year = '$year'
                                    AND mi.companyid = $companyid
                                WHERE u.usertype = 'sales' AND u.companyid = $companyid
                                $empFilter
                                ORDER BY u.fullname ASC
                            ");


$classThresholds = [];
$thresholdRows = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config");
foreach ($thresholdRows as $t) $classThresholds[$t['class']] = (float)$t['min_sales'];

$acctPerf      = [];
$totalCounters = [];

foreach ($execRows as $e) {
    $uid = $e['userid'];

    $rows = $obj->executequery("
                                    SELECT a.account_id, a.account_name, a.class,
                                           COALESCE(SUM(te.grand_total), 0) AS sales
                                    FROM route_counter rc
                                    INNER JOIN route_plan rp
                                            ON rp.batch_no = rc.batch_no
                                           AND rp.sales_executive_id = $uid
                                    INNER JOIN account a
                                            ON a.account_id = rc.account_id
                                    LEFT JOIN transaction_entry te
                                            ON te.account_id = a.account_id
                                           AND te.companyid = $companyid
                                           AND MONTH(te.billdate) = '$month'
                                           AND YEAR(te.billdate) = '$year'
                                    WHERE rc.is_active = 1
                                      AND rc.companyid = $companyid
                                    GROUP BY a.account_id, a.account_name, a.class
                                    ORDER BY sales ASC
                                ");

    $list = [];
    foreach ($rows as $r) {
        $min = $classThresholds[$r['class']] ?? 0;
        $list[] = [
            'account' => $r['account_name'],
            'class'   => $r['class'],
            'sales'   => (float)$r['sales'],
            'min'     => $min,
            'active'  => ((float)$r['sales'] >= $min && $min > 0),
        ];
    }
    $acctPerf[$uid]      = $list;
    $totalCounters[$uid] = count($list);
}

function productivityVerdict($accts, $totalCounters)
{
    $active     = array_filter($accts, fn($a) => $a['active']);
    $inactive   = array_filter($accts, fn($a) => !$a['active']);
    $activeCt   = count($active);
    $assignedCt = count($accts);
    $pct        = $totalCounters > 0 ? round(($activeCt / $totalCounters) * 100, 2) : 0;

    return [
        'active_count'   => $activeCt,
        'assigned_count' => $assignedCt,
        'inactive_count' => count($inactive),
        'total_counters' => $totalCounters,
        'pct'            => $pct,
        'inactive_list'  => array_values($inactive),
        'near_miss'      => array_values(array_filter(
            $inactive,
            fn($a) => $a['min'] > 0 && $a['sales'] >= $a['min'] * 0.8
        )),
    ];
}

$kraList = [
    ['key' => 'visit',        'label' => 'Avg Counter Visit',  'icon' => '🏃', 'weight' => 20, 'max' => 2],
    ['key' => 'productivity', 'label' => 'Beat Productivity',   'icon' => '📊', 'weight' => 20, 'max' => 2],
    ['key' => 'product_mix',  'label' => 'Product Mix',          'icon' => '🧩', 'weight' => 20, 'max' => 2],
    ['key' => 'business',     'label' => 'Overall Business',     'icon' => '💼', 'weight' => 30, 'max' => 2],
    ['key' => 'behaviour',    'label' => 'Behavioural Aspects',  'icon' => '🌟', 'weight' => 10, 'max' => 4],
];

$slabs = [];
$slabRows = $obj->executequery("SELECT * FROM kra_config  ORDER BY kra_key,min_value");
foreach ($slabRows as $s) $slabs[$s['kra_key']][] = $s;

$behaviourItems = $obj->executequery("SELECT * FROM kra_behaviour  ORDER BY kra_behaviour_id");

$bscores = [];
$bscoreRows = $obj->executequery("SELECT * FROM kra_behaviour_score WHERE month='$month' AND year='$year'");
foreach ($bscoreRows as $bs) $bscores[$bs['emp_id']][$bs['behaviour_id']] = $bs['score'];

function colorClass($pct)
{
    if ($pct >= 75) return ['text' => 'text-success', 'bar' => 'success', 'hex' => '#28a745'];
    if ($pct >= 50) return ['text' => 'text-warning', 'bar' => 'warning', 'hex' => '#ffc107'];
    if ($pct >= 40) return ['text' => 'text-danger', 'bar' => 'danger', 'hex' => '#dc3545'];
    return ['text' => 'text-info', 'bar' => 'secondary', 'hex' => '#6c757d'];
}

function svgRing($pct, $hex, $size = 44, $stroke = 4)
{
    $r = ($size - $stroke * 2) / 2;
    $circ = 2 * M_PI * $r;
    $offset = $circ - ($pct / 100) * $circ;
    return '<svg width="' . $size . '" height="' . $size . '">
                                  <circle cx="' . ($size / 2) . '" cy="' . ($size / 2) . '" r="' . $r . '" fill="none" stroke="#e9ecef" stroke-width="' . $stroke . '"/>
                                  <circle cx="' . ($size / 2) . '" cy="' . ($size / 2) . '" r="' . $r . '" fill="none" stroke="' . $hex . '" stroke-width="' . $stroke . '"
                                    stroke-dasharray="' . $circ . '" stroke-dashoffset="' . $offset . '" stroke-linecap="round"
                                    transform="rotate(-90 ' . ($size / 2) . ' ' . ($size / 2) . ')" style="transition:stroke-dashoffset .6s"/>
                                </svg>';
}

function kraValLabel($key, $val)
{
    if ($key === 'productivity') return $val . '%';
    if ($key === 'business')     return '₹' . number_format($val, 0) . 'L';
    if ($key === 'behaviour')    return $val . ' pts';
    return $val;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <?php include('component/dashcss.php'); ?>

</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>

    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="container-fluid pb-4">

            <!-- ── Filter card ───────────────────────────────────────── -->
            <div class="row">
                <div class="col-lg-12 mb-2">
                    <form id="kraFilterForm" method="GET" action="<?= $pagename ?>">
                        <div class="card mt-3">
                            <div class="card-header text-white">
                                <?= $module ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong><label>Month</label></strong>
                                        <select name="month" class="chosen-select form-control form-control-sm">
                                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><label>Year</label></strong>
                                        <select name="year" class="chosen-select form-control form-control-sm">
                                            <?php for ($y = date('Y'); $y >= date('Y') - 4; $y--): ?>
                                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><label>Sales Executive</label></strong>
                                        <select name="emp_id" id="emp_id" class="chosen-select form-control form-control-sm">
                                            <option value="0">-- All Executives --</option>
                                            <?php
                                            $execs = $obj->executequery("SELECT userid, fullname FROM user WHERE usertype='sales' AND companyid=$companyid ORDER BY fullname ASC");
                                            foreach ($execs as $row): ?>
                                                <option value="<?= $row['userid'] ?>" <?= $row['userid'] == $emp_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['fullname']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mt-4">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                        <a href="<?= $pagename ?>" class="btn btn-danger btn-sm">
                                            <i class="fa fa-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Main Report Card ──────────────────────────────────── -->
            <div class="row">
                <div class="col-lg-12 employee-kra-list">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            <span id="cardHeaderTitle"><?= $submodule ?> — <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?></span>
                            <small><i class="bi bi-arrow-up-right-square-fill"></i> Click any executive card to see their KRA breakdown.</small>
                        </div>
                        <div class="card-body">
                            <div class=" row">
                                <?php foreach ($execRows as $e):
                                    $mk  = $e;
                                    $totalScore = $mk['total_score'] ?? 0;
                                    $achPct     = round($mk['achievement_pct'] ?? 0, 1);
                                    $totalInc   = $mk['total_incentive'] ?? 0;
                                    $cols       = colorClass($achPct);
                                    $initials   = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $e['fullname']), 0, 2)));
                                ?>
                                    <div class="col-md-6">
                                        <div class="alert alert-primary-card" role="alert">
                                            <div class="d-flex">
                                                <div class="alert-icon fw-semibold">
                                                    <?= $initials ?>
                                                </div>
                                                <div>
                                                    <h6 class="m-0"><?= htmlspecialchars($e['fullname']) ?></h6>
                                                    <small> <?= eligibility_badge($achPct) ?></small>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm rounded-3 show-kra-card">
                                                    <h6 class="m-0"><?= $achPct ?>%</h6>
                                                    <small class="fs-11 fw-semibold">KRA Score</small>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm rounded-3">
                                                    <h6 class="m-0">₹<?= number_format($totalInc) ?></h6>
                                                    <small class="fs-11 fw-semibold">Incentive</small>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div><!-- /card -->
                </div>

                <div class="col-lg-12 mb-2 kra-score-card" style="display:none;">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            <span id="cardHeaderTitle"><?= $submodule ?> — <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?></span>
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
                                            <span>0.00</span>
                                            <span class="fw-bold">0.00 / 2 pts</span>
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
                                            <span>0.00</span>
                                            <span class="fw-bold">0.00 / 2 pts</span>
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
                                            <span>0.00</span>
                                            <span class="fw-bold">0.00 / 2 pts</span>
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
                                            <span>0.00</span>
                                            <span class="fw-bold">0.00 / 2 pts</span>
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
                                            <span>0.00</span>
                                            <span class="fw-bold">0.00 / 2 pts</span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div><!-- /card -->
                </div>
                <div class="col-lg-12 avg-counter-visit">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            Avg Counter Visit
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00</h4>
                                            <small class="text-center">Actual Value</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00 / 2</h4>
                                            <small class="text-center">Points Earned</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1 " role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0%</h4>
                                            <small class="text-center">KRA Score</small>
                                        </div>
                                    </div>

                                    <div class="card card-body rounded-3 mt-2">
                                        <h6>Scoring Slabs — Avg Counter Visit</h6>
                                        <table class="table table-bordered table-sm mt-1 mb-0">
                                            <tr class="table-dark  ">
                                                <th>Range</th>
                                                <th>Points</th>
                                                <th>Status</th>
                                            </tr>
                                            <tr class="table-primary">
                                                <td>0.00 – 7.00</td>
                                                <td>0.00</td>
                                                <td><span class="slab-badge">Current</span></td>
                                            </tr>
                                            <tr>
                                                <td>7.00 – 10.00</td>
                                                <td>0.50</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>10.00 – 15.00</td>
                                                <td>1.00</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>> 15.00</td>
                                                <td>2.00</td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="card card-body">


                                        <table class="table table-bordered mb-0 table-sm" id="targetTable">
                                            <?php

                                            $route_sql = $obj->executequery("
                                                                    SELECT
                                                                        r.route_id,
                                                                        r.route_name,
                                                                        SUM(mt.total_target) route_target
                                                                    FROM monthly_target mt
                                                                    INNER JOIN account        a  ON a.account_id  = mt.account_id
                                                                    INNER JOIN route_counter  rc ON rc.account_id = a.account_id
                                                                    INNER JOIN route          r  ON r.batch_no    = rc.batch_no
                                                                    WHERE mt.createdby=4
                                                                    AND mt.month='$month'
                                                                    AND mt.year='$year'
                                                                    GROUP BY r.route_id
                                                                    ORDER BY r.route_name
                                                                ");

                                            foreach ($route_sql as $route):
                                                /* route-level achieved */
                                                $route_ach_val = $route_ach_map[$route['route_id']] ?? 0;
                                                $rt_tgt = (float)$route['route_target'];
                                                $rt_pct = $rt_tgt > 0 ? round($route_ach_val / $rt_tgt * 100) : 0;
                                                $rt_clr = ach_color($rt_pct);
                                            ?>
                                                <tr class="toggle-row" style="cursor:pointer;">
                                                    <th style="width:35%"><?= htmlspecialchars($route['route_name']) ?></th>
                                                    <th class="text-end">
                                                        Target: ₹<?= number_format($rt_tgt) ?>
                                                    </th>
                                                    <th class="text-end" style="color:<?= $rt_clr ?>;">
                                                        <span class="bg-white ps-2 pe-2 rounded-2"> Achieved: ₹<?= number_format($route_ach_val) ?></span>
                                                    </th>
                                                    <th class="text-end" style="width:80px;">
                                                        <span style="background:<?= $rt_clr ?>;color:#fff;font-size:.72rem;
                                               font-weight:700;padding:2px 8px;border-radius:20px;">
                                                            <?= $rt_pct ?>%<?= $rt_pct > 100 ? ' ⭐' : '' ?>
                                                        </span>
                                                    </th>
                                                    <th class="text-end" style="width:40px;">
                                                        <i class="bi bi-chevron-double-down toggle-icon"></i>
                                                    </th>
                                                </tr>

                                                <tr class="detail-row">
                                                    <td colspan="5" class="p-0 border-top-0">
                                                        <div class="detail-content" style="display:none;">
                                                            <div class="p-2">
                                                                <table class="table table-bordered table-sm mb-0">
                                                                    <tr class="table-primary">
                                                                        <th>Counter</th>
                                                                        <th class="text-center">1st Sunday</th>
                                                                        <th class="text-center">2nd Sunday</th>
                                                                        <th class="text-center">3rd Sunday</th>
                                                                        <th class="text-center">4th Sunday</th>
                                                                    </tr>
                                                                    <?php for ($i = 0; $i < 5; $i++) { ?>
                                                                        <tr>
                                                                            <td>Prakash Ele And Hardwear</td>
                                                                            <td class="text-center">
                                                                                <span style="background:#dc3545;color:#fff;font-size:.72rem; font-weight:700;padding:2px 8px;border-radius:20px;">
                                                                                    Not Visit
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span style="background:#279b36;color:#fff;font-size:.72rem; font-weight:700;padding:2px 8px;border-radius:20px;">
                                                                                    Visited
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span style="background:#279b36;color:#fff;font-size:.72rem; font-weight:700;padding:2px 8px;border-radius:20px;">
                                                                                    Visited
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span style="background:#279b36;color:#fff;font-size:.72rem; font-weight:700;padding:2px 8px;border-radius:20px;">
                                                                                    Visited
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                </table>

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /card -->
                </div>
                <div class="col-lg-12 beat-productivity">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            Beat Productivity
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00%</h4>
                                            <small class="text-center">Actual Value</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00 / 2</h4>
                                            <small class="text-center">Points Earned</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1 " role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0%</h4>
                                            <small class="text-center">KRA Score</small>
                                        </div>
                                    </div>

                                    <div class="card card-body rounded-3 mt-2">
                                        <h6>Scoring Slabs — 📊 Beat Productivity</h6>
                                        <table class="table table-bordered table-sm mt-1 mb-0">
                                            <tr class="table-dark">
                                                <th>Range</th>
                                                <th>Points</th>
                                                <th>Status</th>
                                            </tr>
                                            <tr class="table-primary">
                                                <td>0.00 – 25.00%</td>
                                                <td>0.00</td>
                                                <td><span class="slab-badge">Current</span></td>
                                            </tr>
                                            <tr>
                                                <td>25.00 – 50.00%</td>
                                                <td>0.50</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>50.00 – 70.00%</td>
                                                <td>1.00</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>> 70.00%</td>
                                                <td>2.00</td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <?php $verdict = productivityVerdict($acctPerf[$e['userid']] ?? [], $totalCounters[$e['userid']] ?? 0); ?>
                                <div class="col-md-9">
                                    <div class="card card-body">
                                        <div class="table-responsive" style="height: 400px;">
                                            <table class="table table-bordered table-sm mb-2">
                                                <thead>
                                                    <tr class="table-primary">
                                                        <th class="th-fix">Account</th>
                                                        <th class="th-fix">Class</th>
                                                        <th class="text-right th-fix">Sales</th>
                                                        <th class="text-right th-fix">Min Needed</th>
                                                        <th class="text-center th-fix">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($acctPerf[$e['userid']] ?? [] as $a): ?>
                                                        <tr class="table-danger">
                                                            <td><?= htmlspecialchars($a['account']) ?></td>
                                                            <td><?= htmlspecialchars($a['class']) ?></td>
                                                            <td class="text-right">₹<?= number_format($a['sales']) ?></td>
                                                            <td class="text-right">₹<?= number_format($a['min']) ?></td>
                                                            <td class="text-center">
                                                                <?php if ($a['active']): ?>
                                                                    <span class="badge badge-success bg-success">Active</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger bg-danger">Not Active</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if ($verdict['inactive_count'] > 0): ?>
                                            <p class="kra-note mb-0">
                                                <i class="fa fa-lightbulb-o"></i>
                                                <strong>Suggested action:</strong>
                                                <?php if ($verdict['pct'] < 40): ?>
                                                    Review whether <?= $verdict['inactive_count'] ?> underperforming account(s) should be
                                                    reassigned to a nearby executive, or flag for a joint visit before issuing a PIP.
                                                <?php else: ?>
                                                    Spot-check the lagging accounts above on the next route cycle rather than reassigning yet.
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /card -->
                </div>
                <div class="col-lg-12 product-mix">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            Product Mix
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0</h4>
                                            <small class="text-center">Actual Value</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00 / 2</h4>
                                            <small class="text-center">Points Earned</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1 " role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0%</h4>
                                            <small class="text-center">KRA Score</small>
                                        </div>
                                    </div>

                                    <div class="card card-body rounded-3 mt-2">
                                        <h6>Scoring Slabs — Product Mix</h6>
                                        <table class="table table-bordered table-sm mt-1 mb-0">
                                            <tr class="table-dark">
                                                <th>Range</th>
                                                <th>Points</th>
                                                <th>Status</th>
                                            </tr>
                                            <tr class="table-primary">
                                                <td>0.00 – 2.00</td>
                                                <td>0.00</td>
                                                <td><span class="slab-badge">Current</span></td>
                                            </tr>
                                            <tr>
                                                <td>2.00 – 3.00</td>
                                                <td>0.50</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>3.00 – 4.00</td>
                                                <td>1.00</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>> 4.00</td>
                                                <td>2.00</td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="card card-body">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /card -->
                </div>
                <div class="col-lg-12 overall-business">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            Overall Business
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">₹0L </h4>
                                            <small class="text-center">Actual Value</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00 / 2</h4>
                                            <small class="text-center">Points Earned</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1 " role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0%</h4>
                                            <small class="text-center">KRA Score</small>
                                        </div>
                                    </div>

                                    <div class="card card-body rounded-3 mt-2">
                                        <h6>Scoring Slabs — Overall Business</h6>
                                        <table class="table table-bordered table-sm mt-1 mb-0">
                                            <tr class="table-dark">
                                                <th>Range</th>
                                                <th>Points</th>
                                                <th>Status</th>
                                            </tr>
                                            <tr class="table-primary">
                                                <td>₹0.00 – 10.00L</td>
                                                <td>0.00</td>
                                                <td><span class="slab-badge">Current</span></td>
                                            </tr>
                                            <tr>
                                                <td>₹10.00 – 20.00L</td>
                                                <td>0.75</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>₹20.00 – 30.00L</td>
                                                <td>1.00</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>₹> 30.00L</td>
                                                <td>2.00</td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="card card-body">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /card -->
                </div>
                <div class="col-lg-12 behavioural-aspects">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            Behavioural Aspects
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00 pts</h4>
                                            <small class="text-center">Actual Value</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1" role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0.00 / 4</h4>
                                            <small class="text-center">Points Earned</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-primary-card justify-content-center mb-1 " role="alert">
                                        <div>
                                            <h4 class="m-0 text-center">0%</h4>
                                            <small class="text-center">KRA Score</small>
                                        </div>
                                    </div>

                                    <div class="card card-body rounded-3 mt-2">
                                        <h6>Scoring Slabs — Behavioural Aspects</h6>
                                        <table class="table table-bordered table-sm mt-1 mb-0">
                                            <tr class="table-dark">
                                                <th>Range</th>
                                                <th>Points</th>
                                                <th>Status</th>
                                            </tr>
                                            <!-- <tr class="table-primary">
                                                <td>₹0.00 – 10.00L</td>
                                                <td>0.00</td>
                                                <td><span class="slab-badge">Current</span></td>
                                            </tr>
                                            <tr>
                                                <td>₹10.00 – 20.00L</td>
                                                <td>0.75</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>₹20.00 – 30.00L</td>
                                                <td>1.00</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>₹> 30.00L</td>
                                                <td>2.00</td>
                                                <td></td>
                                            </tr> -->
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <h5 class="mb-3">Behaviour Sub-Criteria Scores</h5>
                                    <?php
                                    $empBScore = $bscores[$e['userid']] ?? [];
                                    foreach ($behaviourItems as $bi):
                                        $bScore = $empBScore[$bi['kra_behaviour_id']] ?? 0;
                                        $bMax   = $bi['max_score'];
                                        $bpct   = $bMax > 0 ? round(($bScore / $bMax) * 100) : 0;
                                        $bc2    = colorClass($bpct);
                                    ?>
                                        <div class="beh-item">
                                            <span class="beh-name"><?= htmlspecialchars($bi['name']) ?></span>
                                            <div class="beh-bar-wrap">
                                                <div class="beh-bar bg-<?= $bc2['bar'] ?>" style="width:<?= $bpct ?>%"></div>
                                            </div>
                                            <span class="beh-score <?= $bc2['text'] ?>"><?= $bScore ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($empBScore)): ?>
                                        <p class="text-muted mb-0 kra-note">No behaviour scores recorded for this period.</p>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div><!-- /card -->
                </div>
            </div>

        </div><!-- /container-fluid -->
    </div><!-- /main -->

    <?php include('component/script.php'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const kraScoreButtons = document.querySelectorAll('.show-kra-card');
            const employeeSection = document.querySelector('.employee-kra-list');
            const kraSection = document.querySelector('.kra-score-card');
            const backButton = document.querySelector('.kra-score-card .btn-back');
            const statCardLinks = document.querySelectorAll('.kra-score-card .stat-card-link');
            const detailSections = document.querySelectorAll('.avg-counter-visit, .beat-productivity, .product-mix, .overall-business, .behavioural-aspects');

            if (!employeeSection || !kraSection) {
                return;
            }

            detailSections.forEach(function(section) {
                section.style.display = 'none';
            });

            function hideAllDetails() {
                detailSections.forEach(function(section) {
                    section.style.display = 'none';
                });
            }

            function showSection(targetClass) {
                hideAllDetails();
                const section = document.querySelector('.' + targetClass);
                if (section) {
                    section.style.display = 'block';
                }
            }

            kraScoreButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    employeeSection.style.display = 'none';
                    kraSection.style.display = 'block';
                    hideAllDetails();
                });
            });

            statCardLinks.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const target = this.dataset.target;
                    if (target) {
                        showSection(target);
                    }
                });
            });

            if (backButton) {
                backButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    kraSection.style.display = 'none';
                    employeeSection.style.display = 'block';
                    hideAllDetails();
                });
            }
        });
    </script>

</body>

</html>