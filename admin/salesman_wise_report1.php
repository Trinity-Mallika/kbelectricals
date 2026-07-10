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
    if ($pct >= 75)     return '<span class="badge badge-success">✓ Eligible for Increment + Incentive</span>';
    if ($pct >= 50)     return '<span class="badge badge-warning text-dark">↑ Eligible for Increment Only</span>';
    if ($pct >= 40)     return '<span class="badge badge-danger">✗ Not Eligible</span>';
    return '<span class="badge badge-secondary">⚠ Performance Improvement Letter</span>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        /* ── KRA custom styles ─────────────────────────────────── */
        :root {
            --kra-green: #28a745;
            --kra-amber: #ffc107;
            --kra-red: #dc3545;
            --kra-blue: #007bff;
            --kra-gray: #6c757d;
        }

        /* Phase panels */
        .kra-phase {
            display: none;
        }

        .kra-phase.active {
            display: block;
        }

        /* Executive list cards */
        .exec-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #fff;
            cursor: pointer;
            transition: box-shadow .15s, transform .15s;
        }

        .exec-card:hover {
            box-shadow: 0 4px 18px rgba(0, 0, 0, .12);
            transform: translateY(-2px);
        }

        .exec-card-header {
            background: #132a40;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .exec-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6f42c1, #007bff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 13px;
            flex-shrink: 0;
        }

        .exec-name {
            color: #fff;
            font-weight: 600;
            font-size: 15px;
        }

        .exec-meta {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .exec-meta-item {
            text-align: right;
        }

        .exec-meta-label {
            font-size: 10px;
            color: #adb5bd;
            text-transform: uppercase;
            letter-spacing: .5px;
            display: block;
        }

        .exec-meta-value {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
        }

        .exec-kra-row {
            padding: 12px 18px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .exec-kra-col {
            text-align: center;
        }

        .exec-kra-label {
            font-size: 10px;
            color: #6c757d;
            margin-top: 4px;
            display: block;
        }

        .exec-kra-pts {
            font-size: 12px;
            font-weight: 700;
        }

        /* Radial ring */
        .kra-ring {
            position: relative;
            display: inline-block;
        }

        .kra-ring svg {
            transform: rotate(-90deg);
        }

        .kra-ring-label {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        /* KRA breakdown cards (Phase 2) */
        .kra-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            transition: box-shadow .15s, transform .15s;
            height: 100%;
        }

        .kra-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .10);
            transform: translateY(-2px);
        }

        .kra-card-body {
            padding: 16px;
        }

        .kra-card-icon {
            font-size: 22px;
        }

        .kra-card-title {
            font-size: 13px;
            font-weight: 700;
            color: #343a40;
            margin: 6px 0 2px;
            line-height: 1.3;
        }

        .kra-card-weight {
            font-size: 10px;
            color: #6c757d;
        }

        .kra-card-footer {
            padding: 8px 16px;
            border-top: 1px solid #f1f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kra-card-value {
            font-size: 12px;
            color: #6c757d;
        }

        .kra-card-pts {
            font-size: 12px;
            font-weight: 700;
        }

        /* Stat tiles */
        .stat-tile {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background: #fff;
            padding: 14px 18px;
            text-align: center;
            border-top: 3px solid;
        }

        .stat-tile-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .5px;
            display: block;
        }

        .stat-tile-value {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.15;
        }

        .stat-tile-sub {
            font-size: 11px;
            color: #adb5bd;
        }

        /* Slab table */
        .slab-table {
            font-size: 13px;
        }

        .slab-table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 12px;
            color: #495057;
        }

        .slab-row-active td {
            background: #e8f4fd;
            font-weight: 700;
            color: #0056b3;
        }

        .slab-badge {
            background: #007bff;
            color: #fff;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
        }

        /* Behaviour bars */
        .beh-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .beh-item:last-child {
            border-bottom: none;
        }

        .beh-name {
            flex: 1;
            font-size: 13px;
            color: #343a40;
        }

        .beh-bar-wrap {
            width: 130px;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }

        .beh-bar {
            height: 100%;
            border-radius: 3px;
            transition: width .5s;
        }

        .beh-score {
            font-size: 13px;
            font-weight: 600;
            width: 30px;
            text-align: right;
        }

        /* Breadcrumb row */
        .kra-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6c757d;
            flex-wrap: wrap;
            margin-bottom: 4px;
        }

        .kra-breadcrumb a {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .kra-breadcrumb a:hover {
            text-decoration: underline;
        }

        .kra-breadcrumb .sep {
            color: #adb5bd;
        }

        .kra-breadcrumb .current {
            color: #343a40;
            font-weight: 600;
        }

        /* Incentive breakdown */
        .inc-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #f1f3f5;
            font-size: 13px;
        }

        .inc-row:last-child {
            border-bottom: none;
        }

        .inc-total {
            display: flex;
            justify-content: space-between;
            padding: 10px 0 0;
            font-weight: 700;
            font-size: 15px;
        }

        /* Back button */
        .btn-back {
            font-size: 13px;
        }

        /* Progress bar tiny */
        .kra-mini-bar {
            height: 6px;
            border-radius: 3px;
            margin-top: 4px;
            background: #e9ecef;
        }

        .kra-mini-bar .progress-bar {
            border-radius: 3px;
        }

        /* Note */
        .kra-note {
            font-size: 12px;
            color: #6c757d;
            font-style: italic;
        }

        /* Loading spinner */
        .phase-transition {
            animation: fadeSlideIn .22s ease;
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateX(12px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* responsive KRA grid on mobile */
        @media (max-width: 575px) {
            .exec-kra-row {
                grid-template-columns: repeat(3, 1fr);
            }

            .exec-meta {
                gap: 12px;
            }

            .exec-meta-value {
                font-size: 15px;
            }
        }
    </style>
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
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                            <span id="cardHeaderTitle"><?= $submodule ?> — <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?></span>
                            <!-- Breadcrumb lives inside header -->
                            <div class="kra-breadcrumb" id="kraBreadcrumb" style="display:none!important;">
                                <a onclick="goPhase(1)" id="bc1">All Executives</a>
                                <span class="sep" id="bcSep2" style="display:none">›</span>
                                <a onclick="goPhase(2)" id="bc2" style="display:none"></a>
                                <span class="sep" id="bcSep3" style="display:none">›</span>
                                <span class="current" id="bc3" style="display:none"></span>
                            </div>
                        </div>

                        <div class="card-body">

                            <?php
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

                            $slabs = [];
                            $slabRows = $obj->executequery("SELECT * FROM kra_config WHERE company_id=$companyid ORDER BY kra_key,min_value");
                            foreach ($slabRows as $s) $slabs[$s['kra_key']][] = $s;

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

                            if (empty($execRows)):
                            ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fa fa-inbox fa-3x mb-3"></i>
                                    <p>No KRA data found for the selected period.</p>
                                </div>
                            <?php else: ?>

                                <div class="kra-phase active phase-transition" id="phase1">
                                    <p class="kra-note mb-3">
                                        <i class="fa fa-hand-pointer-o"></i>
                                        Click any executive card to see their KRA breakdown.
                                    </p>

                                    <?php foreach ($execRows as $e):
                                        $mk  = $e;
                                        $totalScore = $mk['total_score'] ?? 0;
                                        $achPct     = round($mk['achievement_pct'] ?? 0, 1);
                                        $totalInc   = $mk['total_incentive'] ?? 0;
                                        $cols       = colorClass($achPct);
                                        $initials   = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $e['fullname']), 0, 2)));
                                    ?>
                                        <div class="exec-card" onclick="openEmployee(<?= $e['userid'] ?>, '<?= addslashes(htmlspecialchars($e['fullname'])) ?>')">
                                            <div class="exec-card-header">
                                                <div class="d-flex align-items-center gap-2" style="gap:10px">
                                                    <div class="exec-avatar"><?= $initials ?></div>
                                                    <div>
                                                        <div class="exec-name"><?= htmlspecialchars($e['fullname']) ?></div>
                                                        <?= eligibility_badge($achPct) ?>
                                                    </div>
                                                </div>
                                                <div class="exec-meta">
                                                    <div class="exec-meta-item">
                                                        <span class="exec-meta-label">KRA Score</span>
                                                        <span class="exec-meta-value <?= $cols['text'] ?>"><?= $achPct ?>%</span>
                                                    </div>
                                                    <div class="exec-meta-item">
                                                        <span class="exec-meta-label">Total Pts</span>
                                                        <span class="exec-meta-value text-white"><?= $totalScore ?><small class="text-danger" style="font-size:11px">/220</small></span>
                                                    </div>
                                                    <div class="exec-meta-item">
                                                        <span class="exec-meta-label">Incentive</span>
                                                        <span class="exec-meta-value text-success">₹<?= number_format($totalInc) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="exec-kra-row">
                                                <?php foreach ($kraList as $kra):
                                                    $pts  = $mk[$kra['key'] . '_points'] ?? 0;
                                                    $max  = $kra['max'];
                                                    $kpct = $max > 0 ? round(($pts / $max) * 100) : 0;
                                                    $kc   = colorClass($kpct);
                                                ?>
                                                    <div class="exec-kra-col">
                                                        <div class="kra-ring" style="width:40px;height:40px;margin:0 auto;">
                                                            <?= svgRing($kpct, $kc['hex'], 40, 4) ?>
                                                            <div class="kra-ring-label <?= $kc['text'] ?>" style="font-size:9px"><?= $kpct ?>%</div>
                                                        </div>
                                                        <span class="exec-kra-label"><?= $kra['icon'] ?> <?= explode(' ', $kra['label'])[0] ?></span>
                                                        <span class="exec-kra-pts <?= $kc['text'] ?>"><?= $pts ?>/<?= $max ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="kra-phase phase-transition" id="phase2">
                                    <button class="btn btn-outline-secondary btn-sm btn-back mb-3" onclick="goPhase(1)">
                                        <i class="fa fa-arrow-left"></i> Back to All Executives
                                    </button>

                                    <?php foreach ($execRows as $e):
                                        $mk     = $e;
                                        $achPct = round($mk['achievement_pct'] ?? 0, 1);
                                        $cols   = colorClass($achPct);
                                        $totalInc = $mk['total_incentive'] ?? 0;
                                    ?>
                                        <div class="kra-emp-section phase-transition" id="emp_<?= $e['userid'] ?>" style="display:none">
                                            <div class="row mb-3">
                                                <div class="col-sm-4 col-6 mb-2">
                                                    <div class="stat-tile" style="border-top-color:<?= $cols['hex'] ?>">
                                                        <span class="stat-tile-label">Total Score</span>
                                                        <div class="stat-tile-value <?= $cols['text'] ?>"><?= $mk['total_score'] ?? 0 ?></div>
                                                        <div class="stat-tile-sub">out of 220 pts</div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 col-6 mb-2">
                                                    <div class="stat-tile" style="border-top-color:<?= $cols['hex'] ?>">
                                                        <span class="stat-tile-label">Achievement</span>
                                                        <div class="stat-tile-value <?= $cols['text'] ?>"><?= $achPct ?>%</div>
                                                        <div class="stat-tile-sub"><?= eligibility_badge($achPct) ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 col-6 mb-2">
                                                    <div class="stat-tile" style="border-top-color:#28a745">
                                                        <span class="stat-tile-label">Total Incentive</span>
                                                        <div class="stat-tile-value text-success">₹<?= number_format($totalInc) ?></div>
                                                        <div class="stat-tile-sub">This month</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <p class="kra-note mb-2">
                                                <i class="fa fa-hand-pointer-o"></i>
                                                Click any KRA card for detailed slab breakdown.
                                            </p>
                                            <div class="row mb-3">
                                                <?php foreach ($kraList as $kra):
                                                    $pts  = $mk[$kra['key'] . '_points'] ?? 0;
                                                    $max  = $kra['max'];
                                                    $kpct = $max > 0 ? round(($pts / $max) * 100) : 0;
                                                    $kc   = colorClass($kpct);
                                                    $val  = $mk[$kra['key'] . '_value'] ?? 0;
                                                ?>
                                                    <div class="col-sm-4 col-6 mb-3">
                                                        <div class="kra-card"
                                                            onclick="openKRA(<?= $e['userid'] ?>, '<?= $kra['key'] ?>', '<?= addslashes($kra['label']) ?>', '<?= $kra['icon'] ?>')">
                                                            <div class="kra-card-body">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <div class="kra-card-icon"><?= $kra['icon'] ?></div>
                                                                        <div class="kra-card-title"><?= $kra['label'] ?></div>
                                                                        <div class="kra-card-weight">Weightage: <?= $kra['weight'] ?>%</div>
                                                                    </div>
                                                                    <div class="kra-ring" style="width:50px;height:50px;flex-shrink:0">
                                                                        <?= svgRing($kpct, $kc['hex'], 50, 5) ?>
                                                                        <div class="kra-ring-label <?= $kc['text'] ?>" style="font-size:10px"><?= $kpct ?>%</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="kra-card-footer">
                                                                <span class="kra-card-value"><?= kraValLabel($kra['key'], $val) ?></span>
                                                                <span class="kra-card-pts <?= $kc['text'] ?>"><?= $pts ?> / <?= $max ?> pts</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="card border-0 shadow-sm">
                                                <div class="card-header" style="background:#d4edda;color:#155724;font-weight:600">
                                                    💰 Incentive Breakdown
                                                </div>
                                                <div class="card-body">
                                                    <?php
                                                    $incItems = [
                                                        ['Visit Incentive',       $mk['visit_incentive']        ?? 0],
                                                        ['Sales Incentive',       $mk['sales_incentive']        ?? 0],
                                                        ['Product Mix Incentive', $mk['product_mix_incentive']  ?? 0],
                                                        ['Collection Incentive',  $mk['collection_incentive']   ?? 0],
                                                    ];
                                                    foreach ($incItems as [$label, $val]):
                                                    ?>
                                                        <div class="inc-row">
                                                            <span class="text-muted"><?= $label ?></span>
                                                            <span class="text-success font-weight-bold">₹<?= number_format($val) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <div class="inc-total border-top pt-2">
                                                        <span>Total Incentive</span>
                                                        <span class="text-success">₹<?= number_format($totalInc) ?></span>
                                                    </div>
                                                    <p class="kra-note mt-2 mb-0">
                                                        <i class="fa fa-info-circle"></i>
                                                        70% payout after every 3 months · 30% in April subject to no pending dues &gt;90 days.
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- ════════════════════════════════════════════
                                                 Performance Insight (summary, Phase 2)
                                            ════════════════════════════════════════════ -->
                                            <?php $verdict = productivityVerdict($acctPerf[$e['userid']] ?? [], $totalCounters[$e['userid']] ?? 0); ?>

                                            <div class="card border-0 shadow-sm mt-3">
                                                <div class="card-header" style="background:<?= $verdict['pct'] < 40 ? '#f8d7da' : ($verdict['pct'] < 75 ? '#fff3cd' : '#d4edda') ?>;
                color:<?= $verdict['pct'] < 40 ? '#721c24' : ($verdict['pct'] < 75 ? '#856404' : '#155724') ?>;font-weight:600">
                                                    <i class="fa fa-stethoscope"></i> Performance Insight
                                                </div>
                                                <div class="card-body">
                                                    <?php if ($verdict['assigned_count'] === 0): ?>
                                                        <p class="mb-0 text-muted">No assigned accounts found for this executive this month.</p>
                                                    <?php else: ?>
                                                        <p class="mb-2">
                                                            <?php if ($verdict['pct'] < 40): ?>
                                                                <strong class="text-danger">⚠ Low conversion.</strong>
                                                            <?php elseif ($verdict['pct'] < 75): ?>
                                                                <strong class="text-warning">↑ Below target.</strong>
                                                            <?php else: ?>
                                                                <strong class="text-success">✓ On track.</strong>
                                                            <?php endif; ?>
                                                            Only <strong><?= $verdict['active_count'] ?></strong> of
                                                            <strong><?= $verdict['total_counters'] ?></strong> assigned counters
                                                            (<strong><?= $verdict['pct'] ?>%</strong>) are hitting their sales threshold this month.
                                                            <?= $verdict['inactive_count'] ?> account(s) fell short.
                                                        </p>

                                                        <?php if (!empty($verdict['near_miss'])): ?>
                                                            <p class="kra-note mb-2">
                                                                <i class="fa fa-info-circle"></i>
                                                                <?= count($verdict['near_miss']) ?> account(s) are within 20% of their target —
                                                                a small push could move them to "Active" before month-end.
                                                            </p>
                                                        <?php endif; ?>

                                                        <a class="btn btn-sm btn-outline-primary" data-toggle="collapse" href="#acctDetail_<?= $e['userid'] ?>">
                                                            View account-level breakdown
                                                        </a>

                                                        <div class="collapse mt-2" id="acctDetail_<?= $e['userid'] ?>">
                                                            <table class="table table-sm slab-table mb-2">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Account</th>
                                                                        <th>Class</th>
                                                                        <th class="text-right">Sales</th>
                                                                        <th class="text-right">Min Needed</th>
                                                                        <th class="text-center">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($acctPerf[$e['userid']] ?? [] as $a): ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($a['account']) ?></td>
                                                                            <td><?= htmlspecialchars($a['class']) ?></td>
                                                                            <td class="text-right">₹<?= number_format($a['sales']) ?></td>
                                                                            <td class="text-right">₹<?= number_format($a['min']) ?></td>
                                                                            <td class="text-center">
                                                                                <?php if ($a['active']): ?>
                                                                                    <span class="badge badge-success">Active</span>
                                                                                <?php else: ?>
                                                                                    <span class="badge badge-danger">Not Active</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>

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
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                        </div><!-- /emp_X -->
                                    <?php endforeach; ?>
                                </div><!-- /phase2 -->


                                <!-- ══════════════════════════════════════════════
                                 PHASE 3 — Single KRA detail + slab table
                            ══════════════════════════════════════════════ -->
                                <div class="kra-phase phase-transition" id="phase3">
                                    <button class="btn btn-outline-secondary btn-sm btn-back mb-3" onclick="goPhase(2)">
                                        <i class="fa fa-arrow-left"></i> Back to KRA Breakdown
                                    </button>

                                    <?php foreach ($execRows as $e):
                                        $mk = $e;
                                        foreach ($kraList as $kra):
                                            $key   = $kra['key'];
                                            $pts   = $mk[$key . '_points'] ?? 0;
                                            $max   = $kra['max'];
                                            $kpct  = $max > 0 ? round(($pts / $max) * 100) : 0;
                                            $kc    = colorClass($kpct);
                                            $val   = $mk[$key . '_value'] ?? 0;
                                            $kraSlabs = $slabs[$key] ?? [];
                                    ?>
                                            <div class="kra-detail-section phase-transition"
                                                id="kra_<?= $e['userid'] ?>_<?= $key ?>"
                                                style="display:none">

                                                <!-- Score tiles -->
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 col-6 mb-2">
                                                        <div class="stat-tile" style="border-top-color:#007bff">
                                                            <span class="stat-tile-label">Actual Value</span>
                                                            <div class="stat-tile-value text-primary"><?= kraValLabel($key, $val) ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 col-6 mb-2">
                                                        <div class="stat-tile" style="border-top-color:<?= $kc['hex'] ?>">
                                                            <span class="stat-tile-label">Points Earned</span>
                                                            <div class="stat-tile-value <?= $kc['text'] ?>"><?= $pts ?> / <?= $max ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 col-6 mb-2">
                                                        <div class="stat-tile" style="border-top-color:<?= $kc['hex'] ?>">
                                                            <span class="stat-tile-label">KRA Score</span>
                                                            <div class="stat-tile-value <?= $kc['text'] ?>"><?= $kpct ?>%</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Slab table -->
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header text-white">
                                                        <i class="fa fa-table"></i> Scoring Slabs — <?= $kra['icon'] . ' ' . $kra['label'] ?>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <?php if (!empty($kraSlabs)): ?>
                                                            <table class="table table-bordered slab-table mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Range</th>
                                                                        <th class="text-center">Points</th>
                                                                        <th class="text-center">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($kraSlabs as $s):
                                                                        $label = $s['min_value'];
                                                                        if ($s['max_value'] === null) {
                                                                            $rangeLabel = '> ' . $s['min_value'];
                                                                        } else {
                                                                            $rangeLabel = $s['min_value'] . ' – ' . $s['max_value'];
                                                                        }
                                                                        if ($key === 'productivity') {
                                                                            $rangeLabel .= '%';
                                                                        }
                                                                        if ($key === 'business') {
                                                                            $rangeLabel = '₹' . $rangeLabel . 'L';
                                                                        }

                                                                        // Active row detection
                                                                        $isActive = false;
                                                                        if ($s['max_value'] === null) {
                                                                            $isActive = ($val >= $s['min_value'] && (float)$s['points'] == (float)$pts);
                                                                        } else {
                                                                            $isActive = ($val >= $s['min_value'] && $val < $s['max_value'] && (float)$s['points'] == (float)$pts);
                                                                        }
                                                                    ?>
                                                                        <tr <?= $isActive ? 'class="slab-row-active"' : '' ?>>
                                                                            <td><?= $rangeLabel ?></td>
                                                                            <td class="text-center"><?= $s['points'] ?></td>
                                                                            <td class="text-center">
                                                                                <?php if ($isActive): ?>
                                                                                    <span class="slab-badge">Current</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        <?php else: ?>
                                                            <p class="p-3 mb-0 text-muted">No slab configuration found.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- ════════════════════════════════════════════
                                                     Performance Insight (detail, Phase 3)
                                                     Only for the KRAs where account-level detail
                                                     is meaningful.
                                                ════════════════════════════════════════════ -->
                                                <?php if (in_array($key, ['productivity', 'visit', 'business'])): ?>
                                                    <?php $verdict3 = productivityVerdict($acctPerf[$e['userid']] ?? [], $totalCounters[$e['userid']] ?? 0); ?>

                                                    <div class="card border-0 shadow-sm mb-3">
                                                        <div class="card-header text-white">
                                                            <i class="fa fa-search"></i> Account-Level Breakdown
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <?php if (empty($acctPerf[$e['userid']])): ?>
                                                                <p class="p-3 mb-0 text-muted">No accounts assigned this period.</p>
                                                            <?php else: ?>
                                                                <table class="table table-bordered slab-table mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Account</th>
                                                                            <th>Class</th>
                                                                            <th class="text-right">Sales</th>
                                                                            <th class="text-right">Min Needed</th>
                                                                            <th class="text-center">Status</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($acctPerf[$e['userid']] as $a): ?>
                                                                            <tr <?= !$a['active'] ? 'class="table-danger"' : '' ?>>
                                                                                <td><?= htmlspecialchars($a['account']) ?></td>
                                                                                <td><?= htmlspecialchars($a['class']) ?></td>
                                                                                <td class="text-right">₹<?= number_format($a['sales']) ?></td>
                                                                                <td class="text-right">₹<?= number_format($a['min']) ?></td>
                                                                                <td class="text-center">
                                                                                    <?php if ($a['active']): ?>
                                                                                        <span class="slab-badge" style="background:#28a745">Active</span>
                                                                                    <?php else: ?>
                                                                                        <span class="slab-badge" style="background:#dc3545">Not Active</span>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($verdict3['inactive_count'] > 0): ?>
                                                            <div class="card-footer bg-white">
                                                                <p class="kra-note mb-0">
                                                                    <i class="fa fa-lightbulb-o"></i>
                                                                    <strong>Suggested action:</strong>
                                                                    <?= $verdict3['inactive_count'] ?> of <?= $verdict3['assigned_count'] ?> assigned accounts
                                                                    are below target
                                                                    (<?= $verdict3['active_count'] ?>/<?= $verdict3['total_counters'] ?> = <?= $verdict3['pct'] ?>%
                                                                    of all assigned counters active).
                                                                    <?php if ($verdict3['pct'] < 40): ?>
                                                                        Consider reassigning the weakest accounts to a peer executive with capacity,
                                                                        or schedule a joint field visit before the next review cycle.
                                                                    <?php else: ?>
                                                                        Monitor on the next cycle — no reassignment needed yet.
                                                                    <?php endif; ?>
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Behaviour sub-items (only for behaviour KRA) -->
                                                <?php if ($key === 'behaviour'): ?>
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-header text-white">
                                                            <i class="fa fa-star"></i> Behaviour Sub-Criteria Scores
                                                        </div>
                                                        <div class="card-body">
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
                                                <?php endif; ?>

                                            </div><!-- /kra_X_Y -->
                                    <?php endforeach; // foreach kraList
                                    endforeach; // foreach execRows 
                                    ?>

                                </div><!-- /phase3 -->

                            <?php endif; // if execRows not empty 
                            ?>

                        </div><!-- /card-body -->
                    </div><!-- /card -->
                </div>
            </div>

        </div><!-- /container-fluid -->
    </div><!-- /main -->

    <?php include('component/script.php'); ?>

    <script>
        /* ── Phase & state ──────────────────────────────────────────── */
        let currentPhase = 1;
        let currentEmpId = null;
        let currentEmpName = null;
        let currentKraKey = null;
        let currentKraLabel = null;

        function goPhase(n) {
            document.querySelectorAll('.kra-phase').forEach(el => el.classList.remove('active'));
            document.getElementById('phase' + n).classList.add('active');
            currentPhase = n;
            updateBreadcrumb();
            updateCardHeader();
        }

        function openEmployee(empId, empName) {
            // Hide all emp sections in phase2
            document.querySelectorAll('.kra-emp-section').forEach(el => el.style.display = 'none');
            const sec = document.getElementById('emp_' + empId);
            if (sec) sec.style.display = 'block';
            currentEmpId = empId;
            currentEmpName = empName;
            goPhase(2);
        }

        function openKRA(empId, kraKey, kraLabel, kraIcon) {
            document.querySelectorAll('.kra-detail-section').forEach(el => el.style.display = 'none');
            const sec = document.getElementById('kra_' + empId + '_' + kraKey);
            if (sec) sec.style.display = 'block';
            currentKraKey = kraKey;
            currentKraLabel = kraIcon + ' ' + kraLabel;
            goPhase(3);
        }

        function updateBreadcrumb() {
            const bc = document.getElementById('kraBreadcrumb');
            const bc2 = document.getElementById('bc2'),
                sep2 = document.getElementById('bcSep2');
            const bc3 = document.getElementById('bc3'),
                sep3 = document.getElementById('bcSep3');

            if (currentPhase === 1) {
                bc.style.display = 'none';
            } else {
                bc.style.removeProperty('display');
                bc2.textContent = currentEmpName;
                bc2.style.display = currentPhase >= 2 ? '' : 'none';
                sep2.style.display = currentPhase >= 2 ? '' : 'none';
                bc2.style.color = currentPhase > 2 ? '' : '';
                if (currentPhase === 2) {
                    bc3.style.display = 'none';
                    sep3.style.display = 'none';
                } else {
                    bc3.textContent = currentKraLabel;
                    bc3.style.display = '';
                    sep3.style.display = '';
                }
            }
        }

        function updateCardHeader() {
            const titles = {
                1: '<?= addslashes($submodule) ?> — <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?>',
                2: () => currentEmpName + ' — KRA Breakdown',
                3: () => currentEmpName + ' › ' + currentKraLabel,
            };
            const el = document.getElementById('cardHeaderTitle');
            const t = titles[currentPhase];
            el.textContent = typeof t === 'function' ? t() : t;
        }

        /* ── Init ───────────────────────────────────────────────────── */
        $(document).ready(function() {
            $(".chosen-select").chosen();

            // If emp_id filter was set, auto-open that employee
            <?php if ($emp_id > 0): ?>
                <?php
                $emp_name_row = $obj->executequery("SELECT fullname FROM user WHERE userid=$emp_id AND companyid=$companyid LIMIT 1");
                $ename = !empty($emp_name_row) ? addslashes($emp_name_row[0]['fullname']) : '';
                ?>
                openEmployee(<?= $emp_id ?>, '<?= $ename ?>');
            <?php endif; ?>
        });
    </script>
</body>

</html>