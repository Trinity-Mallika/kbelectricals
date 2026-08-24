<?php
include("../adminsession.php");
$title      = "Monthly Target Approval";
$pagename   = "monthly_target_view.php";
$module     = "Monthly Target Approval";
$submodule  = "Monthly Target Approval List";
$btn_name   = "Save";
$tblname    = "transaction_entry";
$tblpkey    = "transaction_id";

$action     = isset($_GET["action"]) ? $obj->test_input($_GET["action"]) : "";
$createdby  = isset($_GET["createdby"]) ? intval($_GET["createdby"]) : 0;
$month      = isset($_GET["month"]) ? $_GET["month"] : (int)date('m');
$year       = isset($_GET["year"])  ? $_GET["year"]  : (int)date('Y');


if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}
if ($year < 2000 || $year > (int)date('Y') + 1) {
    $year = (int)date('Y');
}

function ach_color(float $pct): string
{
    if ($pct > 100) return '#0d6efd';   // blue  = over-achieved
    if ($pct >= 100) return '#198754';  // green = exactly 100 %
    if ($pct >= 60)  return '#d97706';  // amber = on track
    return '#dc3545';                   // red   = behind
}
function pct_badge(float $pct): string
{
    $color = ach_color($pct);
    $star  = $pct > 100 ? '⭐' : '';
    return "<span style='display:inline-block;font-size:.7rem;font-weight:700;
                padding:2px 7px;border-radius:20px;color:#fff;background:{$color};'>
                {$pct}%{$star}</span>";
}
function mini_bar(float $pct): string
{
    $color   = ach_color($pct);
    $bar_pct = min($pct, 100);
    return "<div style='display:flex;align-items:center;gap:5px;'>
                <div style='background:#e5e7eb;border-radius:3px;height:5px;width:55px;display:inline-block;vertical-align:middle;'>
                    <div style='width:{$bar_pct}%;height:100%;border-radius:3px;background:{$color};'></div>
                </div>
                " . pct_badge($pct) . "
            </div>";
}
function status_badge(?string $status = ''): string
{
    $status = strtolower(trim((string)$status));
    if ($status === 'approved') return "<span class='badge badge-approved'>Approved</span>";
    if ($status === 'rejected') return "<span class='badge badge-rejected'>Rejected</span>";
    return "<span class='badge badge-pending'>Pending</span>";
}

$user_name        = '';
$total_counters    = 0;
$grand_target       = 0.0;
$approval_status    = '';
$grand_achieved      = 0.0;
$grand_pct           = 0;
$achieved_map        = [];
$account_total_achieved = [];
$account_brand_details_map = [];
$brand_ach_map       = [];
$route_ach_map       = [];
$route_ach_all_map = [];

if ($createdby > 0) {
    $user_name = $obj->getvalfield("user", "fullname", "userid='$createdby'");
    $grand_target = (float)$obj->getvalfield(
        "monthly_target",
        "ifnull(sum(total_target),0)",
        "createdby='$createdby' and month='$month' and year='$year'"
    );

    $approval_status = (string)$obj->getvalfield("monthly_target_approval", "IFNULL(status,'Pending')", "userid='$createdby' and month='$month' and year='$year'");

    // NEW: account_id -> target_id, and target_id -> {brand_id: true} set
    $account_target_id_map = [];
    $mt_id_rows = $obj->executequery("
        SELECT account_id, target_id
        FROM monthly_target
        WHERE createdby='$createdby' AND month='$month' AND year='$year'
    ");
    foreach ($mt_id_rows as $mtr) {
        $account_target_id_map[$mtr['account_id']] = $mtr['target_id'];
    }

    $target_brand_set = [];
    $tbd_rows = $obj->executequery("
        SELECT target_id, brand_id
        FROM monthly_target_details
        WHERE createdby='$createdby' AND month='$month' AND year='$year'
    ");
    foreach ($tbd_rows as $tb) {
        $target_brand_set[$tb['target_id']][$tb['brand_id']] = true;
    }

    $all_achieved_rows = $obj->executequery("
    SELECT
        t.account_id,
        td.brand_id,
        cm.cat_name,
        SUM(td.net_amt) AS achieved
    FROM transaction_entry t
    INNER JOIN transaction_details td
        ON td.transaction_id = t.transaction_id
    INNER JOIN category_master cm
        ON cm.cat_id = td.brand_id
    INNER JOIN (
        SELECT DISTINCT rc.account_id
        FROM route_counter rc
        INNER JOIN route_plan rp ON rp.batch_no = rc.batch_no
        WHERE rp.sales_executive_id='$createdby'
          AND rc.is_active=1
    ) x ON x.account_id = t.account_id
    WHERE t.type='order'
      AND t.is_approved=1
      AND td.brand_id != 35
      AND MONTH(t.billdate)='$month'
      AND YEAR(t.billdate)='$year'
    GROUP BY t.account_id, td.brand_id, cm.cat_name
    ");

    foreach ($all_achieved_rows as $a) {
        $acc    = $a['account_id'];
        $brand  = (int)$a['brand_id'];
        $amount = (float)$a['achieved'];

        $achieved_map[$acc . ':' . $brand] = $amount;
        $account_brand_details_map[$acc][] = [
            'cat_name' => $a['cat_name'],
            'brand_id' => $brand,
            'achieved' => $amount,
        ];

        // Grand total & brand-wise summary: keep ALL brand sales (matches mobile's carousel)
        $account_total_achieved[$acc] = ($account_total_achieved[$acc] ?? 0) + $amount;
        $brand_ach_map[$brand] = ($brand_ach_map[$brand] ?? 0) + $amount;
        $grand_achieved += $amount;

        // NEW: route-level achieved only counts if this account+brand actually has a target (matches mobile logic)
        $target_id = $account_target_id_map[$acc] ?? null;
        if ($target_id !== null && isset($target_brand_set[$target_id][$brand])) {
            $account_total_achieved_for_route[$acc] = ($account_total_achieved_for_route[$acc] ?? 0) + $amount;
        }
    }
    $grand_pct = $grand_target > 0 ? round($grand_achieved / $grand_target * 100) : 0;

    $account_route_rows = $obj->executequery("
SELECT DISTINCT rc.account_id, rm.route_id
FROM (SELECT DISTINCT batch_no FROM route_plan WHERE sales_executive_id='$createdby') rp
INNER JOIN route_counter rc ON rc.batch_no = rp.batch_no AND rc.is_active = 1
INNER JOIN route rm ON rm.batch_no = rp.batch_no
");
    foreach ($account_route_rows as $ar) {
        $route_id = $ar['route_id'];

        $route_ach_map[$route_id] = ($route_ach_map[$route_id] ?? 0)
            + ($account_total_achieved_for_route[$ar['account_id']] ?? 0);

        $route_ach_all_map[$route_id] = ($route_ach_all_map[$route_id] ?? 0)
            + ($account_total_achieved[$ar['account_id']] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        .route-card {
            border: 1px solid #e5e7eb;
            border-left: 4px solid var(--route-accent, #0d6efd);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            transition: border-left-width .15s, box-shadow .15s;
        }

        .route-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        /* Highlighted (expanded) route card */
        .route-card:has(.route-toggle.active) {
            border-left-width: 6px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .1);
        }

        .route-head {
            padding: 8px 14px;
            background: #fff;
            transition: background .15s;
        }

        .route-toggle.active {
            background: color-mix(in srgb, var(--route-accent, #0d6efd) 9%, #fff);
        }

        .route-toggle.active .route-name-text {
            color: var(--route-accent, #0d6efd);
        }

        .route-toggle.active .route-name {
            font-weight: 700;
        }

        .route-toggle-icon {
            transition: .25s;
        }

        .route-toggle.active .route-toggle-icon {
            transform: rotate(90deg);
            color: var(--route-accent, #0d6efd);
        }

        .route-name {
            font-size: 14px;
            font-weight: 600;
            color: #212529;
            line-height: 1.2;
        }

        .route-name-text {
            display: inline-block;
            max-width: 100%;
            word-break: break-word;
        }

        .day-pill {
            background: #374151;
            color: #fff;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        .week-pill {
            background: transparent;
            color: #2563eb;
            font-size: 11px;
            font-weight: 600;
            padding: 0;
            border: none;
        }

        .route-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px 14px;
            align-items: center;
        }

        .route-stats {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12.5px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            color: #495057;
        }

        .stat-item strong {
            font-size: 13px;
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

        .day-pill {
            font-size: 10px;
            padding: 2px 8px;
        }

        .week-pill {
            font-size: 10px;
            color: #2563eb;
            background: none;
            padding: 0;
        }

        .ach-badge {
            font-size: 10.5px;
            padding: 2px 9px;
            border-radius: 20px;
            color: #fff;
            font-weight: 600;
        }

        .kpi-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            background: none;
            box-shadow: none;
        }

        .kpi-item {
            flex: 1;
            min-width: 130px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-left: 4px solid transparent;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .kpi-item small {
            font-size: 9.5px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }

        .kpi-item h5 {
            margin-top: 2px;
            margin-bottom: 0;
            font-size: 19px;
            font-weight: 700;
        }

        .table-sm td,
        .table-sm th {
            padding: .35rem .55rem;
            font-size: .8rem;
        }

        thead.table-light th {
            font-weight: 600;
            color: #475569;
        }

        .badge-approved {
            background: #198754;
        }

        .badge-pending {
            background: #ffc107;
            color: #000;
        }

        .badge-rejected {
            background: #dc3545;
        }

        .text-achieved {
            color: #198754;
            font-weight: 600;
        }

        /* General compaction */
        .card-header {
            padding: .5rem .9rem;
            font-size: .92rem;
        }

        .card-body {
            padding: .85rem;
        }

        .route-card.mb-3,
        .kpi-strip.mb-4 {
            margin-bottom: .65rem !important;
        }

        fieldset legend {
            font-size: 1.05rem;
            margin-bottom: .4rem;
        }

        .row.mt-3 {
            margin-top: .65rem !important;
        }
    </style>
</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend>
                            <?= $title ?>
                            <?php if ($createdby > 0) echo status_badge($approval_status); ?>
                        </legend>
                        <?php include('component/alert.php'); ?>
                        <form action="<?= $pagename ?>" method="get">
                            <div class="card">
                                <div class="card-header bg-dark text-white"><?= $module ?></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label>Month<span class="text-danger fw-bold"> *</span></label>
                                            <select name="month" id="selMonth" class="form-control form-control-sm">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"
                                                        <?= str_pad($m, 2, '0', STR_PAD_LEFT) == $month ? 'selected' : '' ?>>
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
                                            <strong><label>Sales Executive<span class="text-danger fw-bold"> *</span></label></strong>
                                            <select name="createdby" id="filter_createdby" class="chosen-select form-control form-control-sm">
                                                <option value="0">-- Select Sales Executive --</option>
                                                <?php
                                                $filter_execs = $obj->executequery("SELECT userid, fullname FROM user WHERE usertype='sales' AND companyid=$companyid ORDER BY fullname ASC");
                                                foreach ($filter_execs as $row): ?>
                                                    <option value="<?= $row['userid'] ?>" <?= $row['userid'] == $createdby ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($row['fullname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md mt-2">
                                            <button type="submit" class="btn btn-theme btn-sm" onclick="return checkinputmaster('selMonth,selYear,filter_createdby');">Search</button>
                                            <a href="<?= $pagename ?>" class="btn btn-danger btn-sm"> Reset </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>

            <?php if ($createdby > 0): ?>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <small>Sales Executive</small>
                                <h5 class="mb-0"><?= htmlspecialchars($user_name) ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body">
                                <small>Month</small>
                                <h5 class="mb-0"><?= date('F', mktime(0, 0, 0, $month, 1)) . ' ' . $year ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-submenu text-white">
                            <div class="card-body">
                                <small>Grand Target</small>
                                <h5 class="mb-0">₹<?= number_format($grand_target) ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <small>Achieved</small>
                                <h5 class="mb-0">₹<?= number_format($grand_achieved) ?> <?= pct_badge($grand_pct) ?></h5>

                                <div class="ach-bar-wrap">
                                    <div class="ach-bar" style="width:<?= min($grand_pct, 100) ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">

                    <!-- ── Brand Wise Summary ──────────────────────────── -->
                    <div class="col-lg-6 mb-3">
                        <div class="card mb-3 h-100">
                            <div class="card-header bg-dark text-white">Brand Wise Summary</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Brand</th>
                                                <th class="text-end">Target</th>
                                                <th class="text-end">Achieved</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            $brand_target_map = [];
                                            $tgt_rows = $obj->executequery("
        SELECT brand_id, SUM(target) AS total_target
        FROM monthly_target_details
        WHERE createdby='$createdby'
          AND month='$month'
          AND year='$year'
        GROUP BY brand_id
    ");
                                            foreach ($tgt_rows as $tr) {
                                                $brand_target_map[(int)$tr['brand_id']] = (float)$tr['total_target'];
                                            }

                                            $brand_sql = $obj->executequery("
        SELECT cat_id AS brand_id, cat_name
        FROM category_master
        WHERE type='brand'
        ORDER BY cat_id ASC
    ");

                                            $brand_rows = [];
                                            foreach ($brand_sql as $row) {
                                                $bid   = (int)$row['brand_id'];
                                                $b_tgt = $brand_target_map[$bid] ?? 0;
                                                $b_ach = $brand_ach_map[$bid] ?? 0;
                                                if ($b_tgt <= 0 && $b_ach <= 0) continue;

                                                $brand_rows[] = [
                                                    'cat_name' => $row['cat_name'],
                                                    'target'   => $b_tgt,
                                                    'achieved' => $b_ach,
                                                ];
                                            }

                                            usort($brand_rows, function ($a, $b) {
                                                return [$b['target'], $b['achieved']] <=> [$a['target'], $a['achieved']];
                                            });

                                            foreach ($brand_rows as $row):
                                                $b_tgt = $row['target'];
                                                $b_ach = $row['achieved'];
                                                $b_pct = $b_tgt > 0 ? round($b_ach / $b_tgt * 100) : 0;
                                            ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($row['cat_name']) ?>
                                                        <?php if ($b_tgt <= 0 && $b_ach > 0): ?>
                                                            <span class="badge bg-warning text-dark">No Target</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">₹<?= number_format($b_tgt) ?></td>
                                                    <td class="text-end text-achieved">₹<?= number_format($b_ach) ?></td>
                                                    <td>
                                                        <?php if ($b_tgt > 0): ?>
                                                            <?= mini_bar($b_pct) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Sales only</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <?php if (empty($brand_rows)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No brand data found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Route Wise Summary ──────────────────────────── -->
                    <div class="col-lg-6 mb-3">
                        <div class="card mb-3 h-100">
                            <div class="card-header bg-primary text-white">Route Wise Summary</div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Route Name</th>
                                            <th class="text-end">Target</th>
                                            <th class="text-end">Achieved</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $route_summary = $obj->executequery("
        SELECT r.route_id, r.route_name,
               COALESCE(SUM(mt.total_target),0) AS total_target
        FROM (SELECT DISTINCT batch_no
              FROM route_plan
              WHERE sales_executive_id = '$createdby') rp
        INNER JOIN route r ON r.batch_no = rp.batch_no
        LEFT JOIN route_counter rc
            ON rc.batch_no = r.batch_no AND rc.is_active = 1
        LEFT JOIN monthly_target mt
            ON mt.account_id = rc.account_id
           AND mt.createdby = '$createdby'
           AND mt.month = '$month'
           AND mt.year = '$year'
        GROUP BY r.route_id, r.route_name
        ORDER BY r.route_name
    ");

                                        $sum_tgt = 0;
                                        $sum_ach = 0;

                                        foreach ($route_summary as $row):
                                            $r_tgt = (float)$row['total_target'];
                                            $r_ach = $route_ach_all_map[$row['route_id']] ?? 0;   // ALL sales, always

                                            $r_pct = $r_tgt > 0 ? round(($r_ach / $r_tgt) * 100) : 0;
                                            $sum_tgt += $r_tgt;
                                            $sum_ach += $r_ach;
                                        ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td>
                                                    <?= htmlspecialchars($row['route_name']) ?>
                                                    <?php if ($r_tgt <= 0 && $r_ach > 0): ?>
                                                        <span class="badge bg-warning text-dark">No Target</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">₹<?= number_format($r_tgt) ?></td>
                                                <td class="text-end text-achieved">₹<?= number_format($r_ach) ?></td>
                                                <td>
                                                    <?php if ($r_tgt > 0): ?>
                                                        <?= mini_bar($r_pct) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><?= $r_ach > 0 ? 'Sales only' : '-' ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <?php if ($i === 1): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No routes found</td>
                                            </tr>
                                        <?php else: ?>
                                            <tr class="table-light fw-bold">
                                                <td colspan="2" class="text-end">Total</td>
                                                <td class="text-end">₹<?= number_format($sum_tgt) ?></td>
                                                <td class="text-end text-achieved">₹<?= number_format($sum_ach) ?></td>
                                                <td><?= $sum_tgt > 0 ? mini_bar(round($sum_ach / $sum_tgt * 100)) : '-' ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Route Wise Counter Details ─────────────────────── -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-2">
                            <div class="card-header bg-primary text-white">
                                Route Wise Counter Details
                                <button type="button" class="btn btn-light btn-sm mb-0 float-end" id="exportExcel">
                                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                                </button>
                            </div>
                            <div class="card-body">
                                <?php
                                $route_sql = $obj->executequery("SELECT
        r.route_id,
        r.route_name,
        r.batch_no,
        r.day_of_week,
        COALESCE(SUM(mt.total_target),0) AS route_target
    FROM (SELECT DISTINCT batch_no
          FROM route_plan
          WHERE sales_executive_id = '$createdby') rp
    INNER JOIN route r
        ON r.batch_no = rp.batch_no
    LEFT JOIN route_counter rc
        ON rc.batch_no = r.batch_no AND rc.is_active = 1
    LEFT JOIN monthly_target mt
        ON mt.account_id = rc.account_id
        AND mt.createdby = '$createdby'
        AND mt.month = '$month'
        AND mt.year = '$year'
    WHERE 1
    GROUP BY r.route_id, r.route_name, r.batch_no, r.day_of_week
    ORDER BY r.day_of_week, MIN(rc.sequence)
");

                                $totalRoutes = count($route_sql);

                                $batch_nos = array_values(array_unique(array_column($route_sql, 'batch_no')));
                                $counter_count_map = [];
                                $visited_count_map = [];

                                if (!empty($batch_nos)) {
                                    $batch_list = "'" . implode("','", array_map([$obj, 'test_input'], $batch_nos)) . "'";

                                    $counter_rows = $obj->executequery("
                                       SELECT rc.batch_no, COUNT(DISTINCT rc.account_id) AS cnt
FROM route_counter rc
INNER JOIN account a ON a.account_id = rc.account_id
WHERE rc.is_active=1 AND rc.batch_no IN ($batch_list)
GROUP BY rc.batch_no");
                                    foreach ($counter_rows as $cr) {
                                        $counter_count_map[$cr['batch_no']] = (int)$cr['cnt'];
                                    }

                                    $visited_rows = $obj->executequery("
                                        SELECT rc.batch_no, COUNT(DISTINCT de.account_id) AS visited
                                        FROM route_counter rc
                                        INNER JOIN daily_entries de ON de.account_id = rc.account_id
                                        WHERE rc.batch_no IN ($batch_list)
                                          AND rc.is_active = 1
                                          AND de.createdby = '$createdby'
                                          AND MONTH(de.checkin_time) = '$month'
                                          AND YEAR(de.checkin_time)  = '$year'
                                        GROUP BY rc.batch_no
                                    ");
                                    foreach ($visited_rows as $vr) {
                                        $visited_count_map[$vr['batch_no']] = (int)$vr['visited'];
                                    }
                                }

                                $target_brand_map = [];
                                $target_detail_rows = $obj->executequery("
                                    SELECT mtd.target_id, cm.cat_name, mtd.brand_id, mtd.target
                                    FROM monthly_target_details mtd
                                    INNER JOIN category_master cm ON cm.cat_id = mtd.brand_id
                                    WHERE mtd.createdby='$createdby'
                                      AND mtd.month='$month'
                                      AND mtd.year='$year'
                                    ORDER BY cm.cat_name
                                ");
                                foreach ($target_detail_rows as $td) {
                                    $target_brand_map[$td['target_id']][] = $td;
                                }

                                $grandCounters = 0;
                                $grandVisited  = 0;
                                foreach ($route_sql as $r) {
                                    $grandCounters += $counter_count_map[$r['batch_no']] ?? 0;
                                    $grandVisited  += $visited_count_map[$r['batch_no']] ?? 0;
                                }
                                $grandPending  = $grandCounters - $grandVisited;
                                $grandCoverage = $grandCounters ? round(($grandVisited / $grandCounters) * 100) : 0;
                                $grandBarColor = $grandCoverage >= 90 ? 'success' : ($grandCoverage >= 70 ? 'primary' : ($grandCoverage >= 50 ? 'warning' : 'danger'));
                                ?>
                                <div class="kpi-strip mb-4">
                                    <div class="kpi-item kpi-routes">
                                        <small>Routes</small>
                                        <h5><?= $totalRoutes ?></h5>
                                    </div>
                                    <div class="kpi-item kpi-counters">
                                        <small>Total Counters</small>
                                        <h5><?= $grandCounters ?></h5>
                                    </div>
                                    <div class="kpi-item kpi-visited">
                                        <small>Visited</small>
                                        <h5 class="text-success"><?= $grandVisited ?></h5>
                                    </div>
                                    <div class="kpi-item kpi-pending">
                                        <small>Pending</small>
                                        <h5 class="text-danger"><?= $grandPending ?></h5>
                                    </div>
                                    <div class="kpi-item kpi-coverage" style="flex-basis:220px;">
                                        <small>Overall Coverage</small>
                                        <div class="d-flex align-items-center">
                                            <h5 class="mb-0 me-2"><?= $grandCoverage ?>%</h5>
                                            <span class="coverage-track ">
                                                <span class="coverage-fill bg-<?= $grandBarColor ?>"
                                                    style="width:<?= $grandCoverage ?>%"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <?php foreach ($route_sql as $route):
                                    $rt_tgt        = (float)$route['route_target'];
                                    $route_ach_val = $route_ach_all_map[$route['route_id']] ?? 0;
                                    $rt_pct        = $rt_tgt > 0 ? round($route_ach_val / $rt_tgt * 100) : 0;
                                    $rt_clr = ach_color($rt_pct);
                                    $batch_no = $route['batch_no'];

                                    $routeCounters = $counter_count_map[$batch_no] ?? 0;
                                    $routeVisited  = $visited_count_map[$batch_no] ?? 0;
                                    $routePending  = $routeCounters - $routeVisited;
                                    $routeCoverage = $routeCounters ? round(($routeVisited / $routeCounters) * 100) : 0;
                                    $routeBarColor = $routeCoverage >= 90 ? 'success' : ($routeCoverage >= 70 ? 'primary' : ($routeCoverage >= 50 ? 'warning' : 'danger'));

                                    $wk_rows = $obj->executequery("
            SELECT DISTINCT week_number
            FROM route_plan
            WHERE batch_no = '$batch_no'
              AND sales_executive_id = '$createdby'
        ");
                                    $assigned_weeks = [];
                                    foreach ($wk_rows as $wr) {
                                        $assigned_weeks[(int)$wr['week_number']] = true;
                                    }
                                    if (isset($assigned_weeks[1])) {
                                        $assigned_weeks[1] = true;
                                        $assigned_weeks[2] = true;
                                        $assigned_weeks[3] = true;
                                        $assigned_weeks[4] = true;
                                        $assigned_weeks[5] = true;
                                    }
                                    if (isset($assigned_weeks[2])) {
                                        $assigned_weeks[2] = true;
                                        $assigned_weeks[4] = true;
                                    }
                                    ksort($assigned_weeks);

                                    $visit_rows = $obj->executequery("
            SELECT rc.account_id, de.checkin_time
            FROM route_counter rc
            LEFT JOIN daily_entries de
                ON de.account_id = rc.account_id
               AND de.createdby  = '$createdby'
               AND MONTH(de.checkin_time) = '$month'
               AND YEAR(de.checkin_time)  = '$year'
            WHERE rc.batch_no  = '$batch_no'
              AND rc.is_active = 1
        ");
                                    $visitWeeks = [];
                                    foreach ($visit_rows as $vr) {
                                        if (!empty($vr['checkin_time'])) {
                                            $wk = ceil(date('j', strtotime($vr['checkin_time'])) / 7);
                                            if ($wk > 5) $wk = 5;
                                            $visitWeeks[$vr['account_id']][$wk] = true;
                                        }
                                    }
                                ?>

                                    <div class="card route-card mb-3" data-target="<?= number_format($rt_tgt) ?>" data-achieved="<?= number_format($route_ach_val) ?>" data-pct="<?= $rt_pct ?>%<?= $rt_pct > 100 ? ' ⭐' : '' ?>">
                                        <div class="route-head route-toggle" style="--route-accent:<?= $rt_clr ?>;cursor:pointer;">
                                            <div class="row align-items-center g-3">
                                                <div class="col-lg-5">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-chevron-right route-toggle-icon fs-6 me-2"></i>
                                                        <i class="bi bi-signpost-split-fill text-primary me-2"></i>
                                                        <div>
                                                            <div class="route-name mb-1">
                                                                <span class="route-name-text">
                                                                    <?= htmlspecialchars($route['route_name']) ?>
                                                                </span>
                                                            </div>
                                                            <div class="d-flex flex-wrap align-items-center gap-1">
                                                                <span class="day-pill">
                                                                    <?= htmlspecialchars($route['day_of_week']) ?>
                                                                </span>
                                                                <?php foreach (array_keys($assigned_weeks) as $wk) { ?>
                                                                    <span class="week-pill">W<?= $wk ?></span>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-7">
                                                    <div class="route-stats">
                                                        <span class="stat-item">
                                                            <i class="bi bi-people"></i>
                                                            <strong><?= $routeCounters ?></strong>
                                                        </span>
                                                        <span class="stat-item text-success">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            <strong><?= $routeVisited ?></strong>
                                                        </span>
                                                        <span class="stat-item text-danger">
                                                            <i class="bi bi-x-circle-fill"></i>
                                                            <strong><?= $routePending ?></strong>
                                                        </span>
                                                        <span class="stat-item">
                                                            <div class="coverage-track">
                                                                <span class="coverage-fill bg-<?= $routeBarColor ?>" style="width:<?= $routeCoverage ?>%"></span>
                                                            </div>
                                                            <strong><?= $routeCoverage ?>%</strong>
                                                        </span>
                                                        <span class="stat-item">
                                                            🎯 ₹<?= number_format($rt_tgt) ?>
                                                        </span>
                                                        <span class="stat-item text-success">
                                                            💰 ₹<?= number_format($route_ach_val) ?>
                                                        </span>
                                                        <span class="ach-badge" style="background:<?= $rt_clr ?>">
                                                            <?= $rt_pct ?>%
                                                            <?= $rt_pct > 100 ? "⭐" : "" ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="route-detail-wrap" style="display:none;">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-sm align-middle table-bordered mb-0 route-detail-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Counter</th>
                                                            <th>Class</th>
                                                            <?php foreach (array_keys($assigned_weeks) as $wk): ?>
                                                                <th class="text-center">W<?= $wk ?></th>
                                                            <?php endforeach; ?>
                                                            <th>Brand</th>
                                                            <th class="text-end">Target</th>
                                                            <th class="text-end">Achieved</th>
                                                            <th class="text-end">Outstanding</th>
                                                            <th>%</th>
                                                            <th>Comment</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $counter_sql = $obj->executequery("SELECT a.account_id,a.account_name,a.class,mt.target_id,mt.comment,
    CASE WHEN mt.target_id IS NULL THEN 0 ELSE 1 END AS has_target
    FROM route_counter rc
    INNER JOIN account a ON a.account_id = rc.account_id
    INNER JOIN route r ON r.batch_no = rc.batch_no
    LEFT JOIN monthly_target mt ON mt.account_id = a.account_id
        AND mt.createdby = '$createdby' AND mt.month = '$month' AND mt.year = '$year'
    WHERE r.route_id='{$route['route_id']}'
      AND rc.is_active = 1
    ORDER BY a.account_name");
                                                        foreach ($counter_sql as $counter):
                                                            $outstanding = $obj->get_ledger_balance($counter['account_id']);

                                                            if ($counter['has_target']) {
                                                                $brand_sql = $target_brand_map[$counter['target_id']] ?? [];

                                                                // append achieved-only brands (sales with no target on this counter)
                                                                $targeted_ids = array_column($brand_sql, 'brand_id');
                                                                foreach ($account_brand_details_map[$counter['account_id']] ?? [] as $extra) {
                                                                    if (!in_array($extra['brand_id'], $targeted_ids)) {
                                                                        $brand_sql[] = [
                                                                            'cat_name' => $extra['cat_name'] . ' *',   // mark as untargeted
                                                                            'brand_id' => $extra['brand_id'],
                                                                            'target'   => 0,
                                                                        ];
                                                                    }
                                                                }
                                                                usort($brand_sql, fn($a, $b) => strcmp($a['cat_name'], $b['cat_name']));
                                                            } else {
                                                                $brand_sql = array_map(function ($row) {
                                                                    return [
                                                                        'cat_name' => $row['cat_name'],
                                                                        'brand_id' => $row['brand_id'],
                                                                        'target'   => 0,
                                                                    ];
                                                                }, $account_brand_details_map[$counter['account_id']] ?? []);
                                                                usort($brand_sql, fn($a, $b) => strcmp($a['cat_name'], $b['cat_name']));
                                                            }

                                                            if (empty($brand_sql)):
                                                                $counter_ach = $account_total_achieved[$counter['account_id']] ?? 0;
                                                        ?>
                                                                <tr>
                                                                    <td>
                                                                        <?= htmlspecialchars_decode($counter['account_name']) ?>
                                                                        <?php if (!$counter['has_target']) { ?>
                                                                            <span class="badge bg-warning text-dark">No Target</span>
                                                                        <?php } ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($counter['class'])) { ?>
                                                                            <?= $counter['class'] ?>
                                                                        <?php } else { ?>
                                                                            -
                                                                        <?php } ?>
                                                                    </td>
                                                                    <?php foreach (array_keys($assigned_weeks) as $wk): ?>
                                                                        <td class="text-center">
                                                                            <?php if (!empty($visitWeeks[$counter['account_id']][$wk])): ?>
                                                                                <i class="bi bi-check-circle-fill text-success"></i>
                                                                            <?php else: ?>
                                                                                <i class="bi bi-x-circle-fill text-danger"></i>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    <?php endforeach; ?>
                                                                    <td class="text-muted"><?= $counter_ach > 0 ? 'Sales (No Brand Target)' : 'No Brand' ?></td>
                                                                    <td class="text-end">₹0</td>
                                                                    <td class="text-end text-success">₹<?= number_format($counter_ach) ?></td>
                                                                    <td class="text-end">₹<?= number_format($outstanding, 2); ?></td>
                                                                    <td><?= $counter_ach > 0 ? '<span class="text-muted small">Sales only</span>' : mini_bar(0) ?></td>
                                                                    <td><?= htmlspecialchars((string)$counter['comment']) ?></td>
                                                                </tr>
                                                                <?php else:
                                                                $rowspan = count($brand_sql);
                                                                $first = true;
                                                                foreach ($brand_sql as $brand):
                                                                    $b_ach = $achieved_map[$counter['account_id'] . ':' . $brand['brand_id']] ?? 0;
                                                                    $b_tgt = (float)$brand['target'];
                                                                    $b_pct = $b_tgt > 0 ? round($b_ach / $b_tgt * 100) : 0;
                                                                ?>
                                                                    <tr>
                                                                        <?php if ($first): ?>
                                                                            <td rowspan="<?= $rowspan ?>" class="align-middle">
                                                                                <?= htmlspecialchars_decode($counter['account_name']) ?>
                                                                                <?php if (!$counter['has_target']) { ?>
                                                                                    <span class="badge bg-warning text-dark">No Target</span>
                                                                                <?php } ?>
                                                                            </td>
                                                                            <td rowspan="<?= $rowspan ?>" class="align-middle">
                                                                                <?php if (!empty($counter['class'])) { ?>
                                                                                    <?= $counter['class'] ?>
                                                                                <?php } else { ?>
                                                                                    -
                                                                                <?php } ?>
                                                                            </td>
                                                                            <?php foreach (array_keys($assigned_weeks) as $wk): ?>
                                                                                <td rowspan="<?= $rowspan ?>" class="align-middle text-center">
                                                                                    <?php if (!empty($visitWeeks[$counter['account_id']][$wk])): ?>
                                                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                                                    <?php else: ?>
                                                                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                            <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                                        <td><?= htmlspecialchars($brand['cat_name']) ?></td>
                                                                        <td class="text-end">₹<?= number_format($b_tgt) ?></td>
                                                                        <td class="text-end text-achieved">₹<?= number_format($b_ach) ?></td>
                                                                        <?php if ($first): ?>
                                                                            <td rowspan="<?= $rowspan ?>" class="align-middle text-end">₹<?= number_format($outstanding, 2); ?></td>
                                                                        <?php endif; ?>
                                                                        <td><?= mini_bar($b_pct) ?></td>
                                                                        <?php if ($first): ?>
                                                                            <td rowspan="<?= $rowspan ?>" class="align-middle text-muted small"><?= htmlspecialchars($counter['comment']) ?></td>
                                                                        <?php endif; ?>
                                                                    </tr>
                                                            <?php $first = false;
                                                                endforeach;
                                                            endif; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>


                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div><!-- /.container-fluid -->
    </div><!-- /.main -->

    <?php include('component/script.php'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script>
        $(document).ready(function() {
            $(".chosen-select").chosen();
        });

        $(document).on('click', '.route-toggle', function() {
            let $current = $(this);

            $('.route-toggle').not($current).removeClass('active');
            $('.route-detail-wrap').not($current.next()).slideUp(200);

            $current.toggleClass('active');
            $current.next('.route-detail-wrap').stop(true, true).slideToggle(200);

        });

        $('#exportExcel').click(function() {
            function rgbToHex(rgb) {
                if (!rgb) return 'FFFFFFFF';
                let m = rgb.match(/\d+/g);
                if (!m) return 'FFFFFFFF';
                let hex = m.slice(0, 3).map(function(x) {
                    let h = parseInt(x).toString(16);
                    return h.length === 1 ? '0' + h : h;
                }).join('');
                return 'FF' + hex.toUpperCase();
            }

            let headerRow = ['Counter', 'Class', 'W1', 'W2', 'W3', 'W4', 'W5', 'Brand', 'Target', 'Achieved', 'Outstanding', '%', 'Comment'];
            let data = [headerRow];
            let merges = [];
            let cellStyles = {};

            let headerStyle = {
                fill: {
                    fgColor: {
                        rgb: 'FF374151'
                    }
                },
                font: {
                    color: {
                        rgb: 'FFFFFFFF'
                    },
                    bold: true
                }
            };
            for (let c = 0; c < headerRow.length; c++) {
                cellStyles[`0_${c}`] = headerStyle;
            }

            $('.route-card').each(function() {
                let $card = $(this);
                let routeName = $card.find('.route-name-text').text().trim();

                let styleAttr = $card.find('.route-toggle').attr('style') || '';
                let accentMatch = styleAttr.match(/--route-accent:\s*([^;]+)/);
                let accentHex = 'FF0D6EFD';
                if (accentMatch) {
                    let hexVal = accentMatch[1].trim().replace('#', '').toUpperCase();
                    if (hexVal === 'DC3545') hexVal = 'E7727D';
                    if (hexVal.length === 6) accentHex = 'FF' + hexVal;
                }

                let routeTarget = $card.attr('data-target') || '';
                let routeAchieved = $card.attr('data-achieved') || '';
                let routePct = $card.attr('data-pct') || '';

                let $table = $card.find('.route-detail-table');

                let weekColToNumber = {};
                $table.find('thead th').each(function(colIdx) {
                    let m = $(this).text().trim().match(/^W(\d)$/);
                    if (m) weekColToNumber[colIdx] = parseInt(m[1], 10);
                });
                let weekCols = Object.keys(weekColToNumber).map(Number).sort((a, b) => a - b);
                let weekCount = weekCols.length;
                let fullRowLength = 8 + weekCount;
                let subsequentRowLength = 4;

                // ── heading row: route name merged, plus target/achieved/% ──
                let headingRowIdx = data.length;
                let headingRow = new Array(headerRow.length).fill('');
                headingRow[0] = routeName;
                headingRow[8] = 'Target: ' + routeTarget;
                headingRow[9] = 'Achieved: ' + routeAchieved;
                headingRow[11] = routePct;
                data.push(headingRow);

                merges.push({
                    s: {
                        r: headingRowIdx,
                        c: 0
                    },
                    e: {
                        r: headingRowIdx,
                        c: 7
                    }
                });

                let headingStyle = {
                    fill: {
                        fgColor: {
                            rgb: accentHex
                        }
                    },
                    font: {
                        color: {
                            rgb: 'FFFFFFFF'
                        },
                        bold: true,
                        sz: 12
                    }
                };
                for (let c = 0; c < headerRow.length; c++) {
                    cellStyles[`${headingRowIdx}_${c}`] = headingStyle;
                }

                let currentCounter = '',
                    currentStatus = '',
                    currentWeeks = {},
                    currentComment = '';

                $table.find('tbody tr').each(function() {
                    let cols = $(this).find('td');
                    let rowData, weekYesNo = null;

                    if (cols.length === fullRowLength) {
                        currentCounter = cols.eq(0).text().trim();
                        currentStatus = cols.eq(1).text().trim();

                        currentWeeks = {};
                        weekYesNo = {};
                        weekCols.forEach(function(colIdx) {
                            let weekNum = weekColToNumber[colIdx];
                            let hasVisit = cols.eq(colIdx).find('.bi-check-circle-fill').length > 0;
                            currentWeeks[weekNum] = hasVisit ? 'Yes' : 'No';
                            weekYesNo[weekNum] = hasVisit;
                        });

                        let brandStart = 2 + weekCount;
                        let brand = cols.eq(brandStart).text().trim();
                        let target = cols.eq(brandStart + 1).text().trim();
                        let ach = cols.eq(brandStart + 2).text().trim();
                        let outstanding = cols.eq(brandStart + 3).text().trim();
                        let pct = cols.eq(brandStart + 4).text().trim();
                        currentComment = cols.eq(brandStart + 5).text().trim();

                        rowData = [currentCounter, currentStatus,
                            currentWeeks[1] || '', currentWeeks[2] || '', currentWeeks[3] || '', currentWeeks[4] || '', currentWeeks[5] || '',
                            brand, target, ach, outstanding, pct, currentComment
                        ];
                    } else if (cols.length === subsequentRowLength) {
                        let brand = cols.eq(0).text().trim();
                        let target = cols.eq(1).text().trim();
                        let ach = cols.eq(2).text().trim();
                        let pct = cols.eq(3).text().trim();

                        rowData = [currentCounter, currentStatus,
                            currentWeeks[1] || '', currentWeeks[2] || '', currentWeeks[3] || '', currentWeeks[4] || '', currentWeeks[5] || '',
                            brand, target, ach, '', pct, ''
                        ];
                    }

                    let rowIdx = data.length;
                    data.push(rowData);

                    // % column: no color styling — plain text only

                    if (weekYesNo) {
                        weekCols.forEach(function(colIdx) {
                            let weekNum = weekColToNumber[colIdx];
                            let colPos = 1 + weekNum;
                            cellStyles[`${rowIdx}_${colPos}`] = {
                                fill: {
                                    fgColor: {
                                        rgb: weekYesNo[weekNum] ? 'FFD1E7DD' : 'FFF8D7DA'
                                    }
                                },
                                font: {
                                    color: {
                                        rgb: weekYesNo[weekNum] ? 'FF198754' : 'FFDC3545'
                                    },
                                    bold: true
                                }
                            };
                        });
                    }
                });
            });

            let ws = XLSX.utils.aoa_to_sheet(data);
            ws['!cols'] = [{
                    wch: 26
                }, {
                    wch: 12
                },
                {
                    wch: 5
                }, {
                    wch: 5
                }, {
                    wch: 5
                }, {
                    wch: 5
                }, {
                    wch: 5
                },
                {
                    wch: 20
                }, {
                    wch: 14
                }, {
                    wch: 14
                }, {
                    wch: 12
                }, {
                    wch: 10
                }, {
                    wch: 30
                },
            ];
            ws['!merges'] = merges;

            Object.keys(cellStyles).forEach(function(key) {
                let [r, c] = key.split('_').map(Number);
                let ref = XLSX.utils.encode_cell({
                    r: r,
                    c: c
                });
                if (ws[ref]) ws[ref].s = cellStyles[key];
            });

            let wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Route Target');
            XLSX.writeFile(wb, 'Route_Wise_Target_Report.xlsx');
        });
    </script>
</body>

</html>