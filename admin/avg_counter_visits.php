<?php
include("../adminsession.php");
$title = "Avg Counter Visit";
$pagename = "avg_counter_visits.php";
$module = "Avg Counter Visit";
$submodule = "Avg Counter Visit List";

$month = isset($_GET['month']) ? intval($_GET['month']) : (int)date('m');
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : (int)date('Y');
$emp_id = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;

// Guard against out-of-range values from tampered query strings
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}
if ($year < 2000 || $year > (int)date('Y') + 1) {
    $year = (int)date('Y');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tag -->
    <?php include('component/css.php'); ?>
    <style>
        .score-input {
            width: 80px;
        }

        /* Compact KPI ribbon at the top */
        .kpi-strip {
            display: flex;
            flex-wrap: wrap;
            background: #fff;
            border-radius: .5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        .kpi-item {
            flex: 1 1 160px;
            padding: .85rem 1.1rem;
            border-right: 1px solid #eef0f2;
            border-left: 3px solid transparent;
        }

        .kpi-item:last-child {
            border-right: none;
        }

        .kpi-item.kpi-routes {
            border-left-color: #6c757d;
        }

        .kpi-item.kpi-counters {
            border-left-color: #0d6efd;
        }

        .kpi-item.kpi-visited {
            border-left-color: #198754;
        }

        .kpi-item.kpi-pending {
            border-left-color: #dc3545;
        }

        .kpi-item.kpi-coverage {
            border-left-color: #0dcaf0;
        }

        .kpi-item small {
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .68rem;
            color: #5a6572;
            font-weight: 600;
        }

        .kpi-item h5 {
            margin: .1rem 0 0;
            font-weight: 700;
            font-size: 1.3rem;
        }

        /* Compact per-route report row */
        .route-card {
            border: 1px solid #dde1e5;
        }

        .route-card .route-head {
            padding: .6rem .9rem;
            background: #eef1f5;
            border-bottom: 1px solid #dde1e5;
            border-left: 4px solid var(--route-accent, #0d6efd);
        }

        .route-head .route-name {
            font-size: .95rem;
            font-weight: 600;
            margin: 0;
            color: #1c2733;
        }

        .route-head .week-text {
            color: #5a6572;
            font-weight: 500;
        }

        .route-stats {
            font-size: .82rem;
            white-space: nowrap;
            color: #33404d;
            font-weight: 500;
        }

        .route-stats .dot {
            color: #8b93a1;
            margin: 0 .45rem;
            font-weight: 700;
        }

        .coverage-track {
            width: 90px;
            height: 8px;
            border-radius: 4px;
            background: #dfe3e8;
            border: 1px solid #cfd4da;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }

        .coverage-fill {
            height: 100%;
            display: block;
        }

        .day-badge {
            background: #495057;
            color: #fff;
            font-weight: 500;
            font-size: .72rem;
            padding: .2rem .55rem;
            border-radius: .3rem;
        }

        .table-sm td,
        .table-sm th {
            padding: .4rem .6rem;
            font-size: .85rem;
        }

        thead.table-light th {
            color: #33404d;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- Header -->
        <?php include('component/header.php'); ?>
        <!-- Header Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $title ?></legend>
                        <form action="<?php echo $pagename; ?>" method="get">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <?php echo $module ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label>Month<span class="text-danger fw-bold"> *</span></label>
                                            <select name="month" id="selMonth" class="form-control form-control-sm">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Year<span class="text-danger fw-bold"> *</span></label>
                                            <select name="year" id="selYear" class="form-control form-control-sm">
                                                <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--): ?>
                                                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong><label>Sales Executive</label></strong>
                                            <select name="emp_id" id="filter_emp_id" class="chosen-select form-control form-control-sm">
                                                <option value="0">-- All Executives --</option>
                                                <?php
                                                $filter_execs = $obj->executequery("SELECT userid, fullname FROM user WHERE usertype='sales' AND companyid=$companyid ORDER BY fullname ASC");
                                                foreach ($filter_execs as $row): ?>
                                                    <option value="<?= $row['userid'] ?>" <?= $row['userid'] == $emp_id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($row['fullname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md mt-2">
                                            <button type="submit" class="btn btn-theme btn-sm" onclick="return checkinputmaster('selMonth,selYear,filter_emp_id');">Search</button>
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>
            <?php if ($emp_id > 0) { ?>
                <div class="row mt-4 mb-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <?php echo $submodule; ?>
                            </div>
                            <div class="card-body">
                                <?php
                                // Build WHERE dynamically so "All Executives" (emp_id = 0) actually
                                // shows every route instead of matching a literal executive id of 0.
                                $whereSql = "WHERE 1=1";
                                if ($emp_id > 0) {
                                    $whereSql .= " AND rp.sales_executive_id = $emp_id";
                                }

                                $sql = "SELECT
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
                            AND MONTH(de.checkin_time) = $month
                            AND YEAR(de.checkin_time) = $year

                            $whereSql

                            ORDER BY
                            r.day_of_week,
                            rc.sequence
                            ";

                                $visitRows = $obj->executequery($sql);

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

                                // ---- Overall summary across every route (for the top summary bar) ----
                                $grandTotalCounters = 0;
                                $grandTotalVisited = 0;
                                foreach ($visitData as $data) {
                                    $grandTotalCounters += count($data['accounts']);
                                    foreach ($data['accounts'] as $acc) {
                                        if (!empty($acc['weeks'])) {
                                            $grandTotalVisited++;
                                        }
                                    }
                                }
                                $grandTotalPending = $grandTotalCounters - $grandTotalVisited;
                                $grandCoverage = $grandTotalCounters ? round(($grandTotalVisited / $grandTotalCounters) * 100) : 0;
                                $grandBarColor = $grandCoverage >= 90 ? 'success' : ($grandCoverage >= 70 ? 'primary' : ($grandCoverage >= 50 ? 'warning' : 'danger'));
                                ?>

                                <?php
                                // Label so the report always makes it obvious what scope is being shown
                                $scopeLabel = "All Executives";
                                if ($emp_id > 0) {
                                    foreach ($filter_execs as $row) {
                                        if ($row['userid'] == $emp_id) {
                                            $scopeLabel = $row['fullname'];
                                            break;
                                        }
                                    }
                                }
                                ?>

                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                    <div class="small" style="color:#495057;">
                                        <i class="bi bi-funnel me-1"></i>
                                        Showing: <strong><?= htmlspecialchars($scopeLabel) ?></strong>
                                        &nbsp;|&nbsp;
                                        <?= date('F', mktime(0, 0, 0, $month, 1)) ?> <?= $year ?>
                                    </div>
                                </div>

                                <?php if (empty($visitData)): ?>

                                    <div class="text-center py-5">
                                        <i class="bi bi-inboxes display-4 text-muted"></i>
                                        <p class="text-muted mt-3 mb-0">
                                            No route data found for the selected month, year, and executive.
                                        </p>
                                    </div>

                                <?php else: ?>

                                    <!-- Compact overall KPI ribbon -->
                                    <div class="kpi-strip mb-4">
                                        <div class="kpi-item kpi-routes">
                                            <small>Routes</small>
                                            <h5><?= count($visitData) ?></h5>
                                        </div>
                                        <div class="kpi-item kpi-counters">
                                            <small>Total Counters</small>
                                            <h5><?= $grandTotalCounters ?></h5>
                                        </div>
                                        <div class="kpi-item kpi-visited">
                                            <small>Visited</small>
                                            <h5 class="text-success"><?= $grandTotalVisited ?></h5>
                                        </div>
                                        <div class="kpi-item kpi-pending">
                                            <small>Pending</small>
                                            <h5 class="text-danger"><?= $grandTotalPending ?></h5>
                                        </div>
                                        <div class="kpi-item kpi-coverage" style="flex-basis:220px;">
                                            <small>Overall Coverage</small>
                                            <div class="d-flex align-items-center">
                                                <h5 class="mb-0 me-2"><?= $grandCoverage ?>%</h5>
                                                <span class="coverage-track">
                                                    <span class="coverage-fill bg-<?= $grandBarColor ?>"
                                                        style="width:<?= $grandCoverage ?>%"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php foreach ($visitData as $route => $data): ?>
                                        <?php
                                        $totalCounter = count($data['accounts']);
                                        $visited = 0;

                                        foreach ($data['accounts'] as $acc) {
                                            if (!empty($acc['weeks'])) {
                                                $visited++;
                                            }
                                        }

                                        $pending = $totalCounter - $visited;
                                        $coverage = $totalCounter ? round(($visited / $totalCounter) * 100) : 0;

                                        if ($coverage >= 90) {
                                            $barColor = "success";
                                        } elseif ($coverage >= 70) {
                                            $barColor = "primary";
                                        } elseif ($coverage >= 50) {
                                            $barColor = "warning";
                                        } else {
                                            $barColor = "danger";
                                        }

                                        // Map bootstrap color name to a hex value for the left accent border
                                        $accentHex = [
                                            'success' => '#198754',
                                            'primary' => '#0d6efd',
                                            'warning' => '#ffc107',
                                            'danger'  => '#dc3545',
                                        ][$barColor];
                                        ?>
                                        <div class="card route-card mb-3">
                                            <div class="route-head d-flex justify-content-between align-items-center flex-wrap gap-2"
                                                style="--route-accent: <?= $accentHex ?>;">
                                                <div>
                                                    <p class="route-name">
                                                        <i class="bi bi-signpost-split-fill me-1 text-primary"></i>
                                                        <?= htmlspecialchars($route) ?>
                                                        <span class="day-badge ms-2">
                                                            <?= htmlspecialchars($data['day']) ?>
                                                        </span>
                                                        <span class="week-text small">
                                                            &middot; W<?= implode(", W", array_keys($data['assigned_weeks'])) ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="route-stats text-nowrap">
                                                    <span>Counters <strong><?= $totalCounter ?></strong></span>
                                                    <span class="dot">&bull;</span>
                                                    <span class="text-success">Visited <strong><?= $visited ?></strong></span>
                                                    <span class="dot">&bull;</span>
                                                    <span class="text-danger">Pending <strong><?= $pending ?></strong></span>
                                                    <span class="dot">&bull;</span>
                                                    <span>
                                                        <strong><?= $coverage ?>%</strong>
                                                        <span class="coverage-track">
                                                            <span class="coverage-fill bg-<?= $barColor ?>" style="width:<?= $coverage ?>%"></span>
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm align-middle table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="40">#</th>
                                                            <th>Counter</th>
                                                            <?php foreach (array_keys($data['assigned_weeks']) as $week): ?>
                                                                <th class="text-center">Week <?= $week ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $i = 1;
                                                        foreach ($data['accounts'] as $acc): ?>
                                                            <tr>
                                                                <td><?= $i++ ?></td>
                                                                <td><?= htmlspecialchars($acc['account_name']) ?></td>
                                                                <?php foreach (array_keys($data['assigned_weeks']) as $week): ?>
                                                                    <td class="text-center">
                                                                        <?php if (!empty($acc['weeks'][$week])): ?>
                                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                                        <?php else: ?>
                                                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- Content Close-->
    </div>

</body>

<!-- Script tags -->
<?php include('component/script.php'); ?>
<script>
    $(document).ready(function() {
        $(".chosen-select").chosen();
    });
</script>

</html>