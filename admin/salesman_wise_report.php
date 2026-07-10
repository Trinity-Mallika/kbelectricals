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
$thresholdRows = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config WHERE companyid=$companyid");
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



$behaviourItems = $obj->executequery("SELECT * FROM kra_behaviour WHERE companyid=$companyid ORDER BY kra_behaviour_id");

$bscores = [];
$bscoreRows = $obj->executequery("SELECT * FROM kra_behaviour_score WHERE company_id=$companyid AND month='$month' AND year='$year'");
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
                                        <select name="month" id="month" class="chosen-select form-control form-control-sm">
                                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><label>Year</label></strong>
                                        <select name="year" id="year" class="chosen-select form-control form-control-sm">
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
                                                <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm rounded-3 show-kra-card" data-emp="<?= $e['userid'] ?>">
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

                </div>

            </div>

        </div><!-- /container-fluid -->
    </div><!-- /main -->

    <?php include('component/script.php'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const employeeSection = document.querySelector('.employee-kra-list');
            const kraSection = document.querySelector('.kra-score-card');

            // Employee click
            document.addEventListener('click', function(e) {

                const btn = e.target.closest('.show-kra-card');

                if (!btn) return;

                e.preventDefault();

                let emp_id = btn.dataset.emp;
                let month = document.getElementById('month').value;
                let year = document.getElementById('year').value;

                employeeSection.style.display = 'none';

                kraSection.style.display = 'block';
                kraSection.innerHTML =
                    '<div class="text-center p-5"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';

                fetch('ajax/ajax_kra_details.php', {

                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },

                        body: 'emp_id=' + emp_id +
                            '&month=' + month +
                            '&year=' + year

                    })

                    .then(response => response.text())

                    .then(html => {

                        kraSection.innerHTML = html;

                    });

            });

            // Back button
            document.addEventListener('click', function(e) {

                if (!e.target.closest('.btn-back')) return;

                e.preventDefault();

                kraSection.style.display = 'none';
                employeeSection.style.display = 'block';

            });

            // KRA section navigation
            document.addEventListener('click', function(e) {

                const link = e.target.closest('.stat-card-link');

                if (!link) return;

                e.preventDefault();

                document.querySelectorAll(
                    '.avg-counter-visit,.beat-productivity,.product-mix,.overall-business,.behavioural-aspects'
                ).forEach(function(x) {

                    x.style.display = 'none';

                });

                let section = document.querySelector('.' + link.dataset.target);

                if (section) {

                    section.style.display = 'block';

                }

            });

        });
    </script>
</body>

</html>