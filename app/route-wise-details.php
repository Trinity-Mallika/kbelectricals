<?php
include("appsession.php");
$title    = "Route Wise Details";
$pagename = "route-wise-details.php";
$month_digi = date('m');
$year       = date('Y');
$batch_no = isset($_GET['batch_no']) ? $obj->test_input($_GET['batch_no']) : '';
$batch_crit = $batch_no ? "AND rc.batch_no='$batch_no'" : '';

$routes = $obj->executequery("
    SELECT
        r.route_id,
        r.route_name,
        r.batch_no,
        SUM(mt.total_target) AS route_target
    FROM monthly_target mt
    INNER JOIN account        a  ON a.account_id  = mt.account_id
    INNER JOIN route_counter  rc ON rc.account_id = a.account_id
    INNER JOIN route          r  ON r.batch_no    = rc.batch_no
    WHERE mt.createdby = '$loginid'
      AND mt.month = '$month_digi'
      AND mt.year  = '$year'
      $batch_crit
    GROUP BY r.route_id, r.route_name, r.batch_no
    ORDER BY r.route_name
");

$all_counters = $obj->executequery("
    SELECT
        mt.target_id,
        mt.comment,
        a.account_id,
        a.account_name,
        r.route_id
    FROM monthly_target mt
    INNER JOIN account        a  ON a.account_id  = mt.account_id
    INNER JOIN route_counter  rc ON rc.account_id = a.account_id
    INNER JOIN route          r  ON r.batch_no    = rc.batch_no
    WHERE mt.createdby = '$loginid'
      AND mt.month = '$month_digi'
      AND mt.year  = '$year'
      $batch_crit
    ORDER BY a.account_name
");

$all_brands = $obj->executequery("
    SELECT
        mtd.target_id,
        mtd.brand_id,
        mtd.target,
        cm.cat_name
    FROM monthly_target_details mtd
    INNER JOIN monthly_target mt ON mt.target_id = mtd.target_id
    INNER JOIN route_counter  rc ON rc.account_id = mt.account_id
    INNER JOIN category_master cm ON cm.cat_id = mtd.brand_id
    WHERE mt.createdby = '$loginid'
      AND mt.month = '$month_digi'
      AND mt.year  = '$year'
      $batch_crit
");

$all_achieved = $obj->executequery("
    SELECT
        t.account_id,
        td.brand_id,
        SUM(td.net_amt) AS achieved
    FROM transaction_details td
    INNER JOIN transaction_entry t ON t.transaction_id = td.transaction_id
    INNER JOIN monthly_target     mt ON mt.account_id = t.account_id
                                     AND mt.createdby  = '$loginid'
                                     AND mt.month      = '$month_digi'
                                     AND mt.year       = '$year'
    WHERE MONTH(t.billdate) = '$month_digi'
      AND YEAR(t.billdate)  = '$year'
      AND t.type       = 'order'
      AND t.is_approved = 1
    GROUP BY t.account_id, td.brand_id
");

$counters_by_route = [];
foreach ($all_counters as $c) {
    $counters_by_route[$c['route_id']][] = $c;
}

$brands_by_target = [];
foreach ($all_brands as $b) {
    $brands_by_target[$b['target_id']][] = $b;
}

$achieved_map = [];
foreach ($all_achieved as $a) {
    $achieved_map[$a['account_id'] . ':' . $a['brand_id']] = (float)$a['achieved'];
}

function achievement_color(float $pct): string
{
    if ($pct > 100) return 'var(--clr-blue)';   // over-achieved
    if ($pct >= 100) return 'var(--clr-green)';  // exactly 100%
    if ($pct >= 60)  return 'var(--clr-amber)';  // on track
    return 'var(--clr-red)';                     // behind
}

$route_options = $obj->executequery("
    SELECT r.batch_no, r.route_name,
           GROUP_CONCAT(r.day_of_week
               ORDER BY FIELD(r.day_of_week,'Monday','Tuesday','Wednesday',
                              'Thursday','Friday','Saturday')
               SEPARATOR ', ') AS days
    FROM route r
    LEFT JOIN route_plan rp ON rp.batch_no = r.batch_no
    WHERE r.companyid = '$companyid'
      AND rp.sales_executive_id = '$loginid'
    GROUP BY r.batch_no, r.route_name
    ORDER BY r.route_name
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Route Wise Details · KBELECTRICAL</title>
    <?php include("inc/css-file.php"); ?>
    <style>
        :root {
            --clr-green: #16a34a;
            --clr-amber: #d97706;
            --clr-red: #dc2626;
            --clr-blue: #2563eb;
            /* over-achieved */
            --radius: 10px;
        }

        /* ── Route accordion ─────────────────────────────── */
        .route-card {
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            margin-bottom: .75rem;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        }

        .route-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .65rem 1rem;
            cursor: pointer;
            background: #f8fafc;
            border: none;
            width: 100%;
            text-align: left;
            gap: .5rem;
        }

        .route-header:hover {
            background: #f1f5f9;
        }

        .route-name {
            font-weight: 600;
            font-size: .9rem;
            color: #1e293b;
        }

        .route-target {
            font-size: .85rem;
            font-weight: 700;
            color: var(--clr-blue);
            white-space: nowrap;
        }

        .route-pct {
            font-size: .75rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
            color: #fff;
            white-space: nowrap;
        }

        .route-progress {
            height: 4px;
            background: #e5e7eb;
        }

        .route-progress-bar {
            height: 100%;
            transition: width .4s;
        }

        /* ── Counter card ────────────────────────────────── */
        .counter-list {
            padding: .5rem .75rem .75rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .counter-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .counter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .45rem .75rem;
            background: #f1f5f9;
            gap: .5rem;
        }

        .counter-name {
            font-size: .82rem;
            font-weight: 600;
            color: #1e293b;
        }

        .counter-meta {
            font-size: .75rem;
            color: #64748b;
        }

        /* ── Brand rows ──────────────────────────────────── */
        .brand-table {
            width: 100%;
            font-size: .78rem;
            border-collapse: collapse;
        }

        .brand-table th {
            background: #eff6ff;
            color: #3b82f6;
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: .3rem .6rem;
        }

        .brand-table td {
            padding: .3rem .6rem;
            border-top: 1px solid #f1f5f9;
            color: #374151;
        }

        .brand-table tr:hover td {
            background: #fafafa;
        }

        .pct-badge {
            display: inline-block;
            font-size: .68rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 20px;
            color: #fff;
        }

        .mini-bar-wrap {
            background: #e5e7eb;
            border-radius: 3px;
            height: 5px;
            width: 60px;
            display: inline-block;
            vertical-align: middle;
        }

        .mini-bar {
            height: 100%;
            border-radius: 3px;
        }

        /* ── Comment chip ────────────────────────────────── */
        .comment-chip {
            display: flex;
            align-items: flex-start;
            gap: .35rem;
            padding: .35rem .6rem;
            background: #fffbeb;
            border-top: 1px dashed #fcd34d;
            font-size: .75rem;
            color: #92400e;
        }

        /* ── Empty state ─────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #94a3b8;
            font-size: .9rem;
        }

        .empty-state svg {
            margin-bottom: .5rem;
            opacity: .4;
        }

        .chev {
            transition: transform .25s;
            flex-shrink: 0;
        }

        .route-header[aria-expanded="true"] .chev {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>
        <div class="container">

            <!-- Route filter -->
            <div class="col-12 mt-2 mb-3">
                <form class="card border-0 shadow-sm p-3">
                    <label class="form-label fw-semibold small mb-1">Select a Route</label>
                    <select name="batch_no" id="batch_no" class="form-control chosen-select mb-3">
                        <?php foreach ($route_options as $r): ?>
                            <option value="<?= $r['batch_no'] ?>"
                                <?= ($batch_no == $r['batch_no']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['route_name']) ?>
                                [<?= htmlspecialchars($r['days']) ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-sm w-50">Search</button>
                        <a href="<?= $pagename ?>" class="btn btn-red btn-sm w-50">Reset</a>
                    </div>
                </form>
            </div>

            <?php if (empty($routes)): ?>
                <div class="empty-state">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 13l4.553 2.276A1 1 0 0021 21.382V10.618a1 1 0 00-1.447-.894L15 12m0 8V12M9 7l6-3" />
                    </svg>
                    <p>No targets found for this month.</p>
                </div>

            <?php else: ?>
                <?php foreach ($routes as $route):
                    $r_target   = (float)$route['route_target'];
                    $r_achieved = 0;
                    $r_counters = $counters_by_route[$route['route_id']] ?? [];

                    foreach ($r_counters as $c) {
                        foreach ($brands_by_target[$c['target_id']] ?? [] as $b) {
                            $r_achieved += $achieved_map[$c['account_id'] . ':' . $b['brand_id']] ?? 0;
                        }
                    }

                    // No min() cap — show real percentage
                    $r_pct   = $r_target > 0 ? round($r_achieved / $r_target * 100) : 0;
                    $r_color = achievement_color($r_pct);
                    $r_star  = $r_pct > 100 ? ' ⭐' : '';
                    $is_open = ($batch_no == $route['batch_no']);
                ?>
                    <div class="route-card">
                        <button class="route-header"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#route-<?= $route['route_id'] ?>"
                            aria-expanded="<?= $is_open ? 'true' : 'false' ?>">

                            <span class="route-name"><?= htmlspecialchars($route['route_name']) ?></span>

                            <span style="display:flex;align-items:center;gap:.4rem;margin-left:auto;">
                                <span class="route-target">₹<?= number_format($r_target) ?></span>
                                <span class="route-pct" style="background:<?= $r_color ?>;">
                                    <?= $r_pct ?>%<?= $r_star ?>
                                </span>
                                <svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </span>
                        </button>

                        <!-- Progress bar: capped at 100% visually, real % in badge -->
                        <div class="route-progress">
                            <div class="route-progress-bar"
                                style="width:<?= min($r_pct, 100) ?>%;background:<?= $r_color ?>;"></div>
                        </div>

                        <div id="route-<?= $route['route_id'] ?>"
                            class="collapse <?= $is_open ? 'show' : '' ?>">

                            <?php if (empty($r_counters)): ?>
                                <p class="text-muted small p-3 mb-0">No counters assigned.</p>
                            <?php else: ?>
                                <div class="counter-list">
                                    <?php foreach ($r_counters as $c):
                                        $c_brands   = $brands_by_target[$c['target_id']] ?? [];
                                        $c_target   = array_sum(array_column($c_brands, 'target'));
                                        $c_achieved = 0;
                                        foreach ($c_brands as $b) {
                                            $c_achieved += $achieved_map[$c['account_id'] . ':' . $b['brand_id']] ?? 0;
                                        }
                                        // No min() cap
                                        $c_pct   = $c_target > 0 ? round($c_achieved / $c_target * 100) : 0;
                                        $c_color = achievement_color($c_pct);
                                        $c_star  = $c_pct > 100 ? ' ⭐' : '';
                                    ?>
                                        <div class="counter-card">
                                            <div class="counter-header">
                                                <span class="counter-name">
                                                    <?= htmlspecialchars($c['account_name']) ?>
                                                </span>
                                                <span class="counter-meta">
                                                    <span style="color:<?= $c_color ?>;font-weight:700;">
                                                        ₹<?= number_format($c_achieved) ?>
                                                    </span>
                                                    / ₹<?= number_format($c_target) ?>
                                                    <span class="pct-badge" style="background:<?= $c_color ?>;">
                                                        <?= $c_pct ?>%<?= $c_star ?>
                                                    </span>
                                                </span>
                                            </div>

                                            <?php if (!empty($c_brands)): ?>
                                                <table class="brand-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Brand</th>
                                                            <th>Target</th>
                                                            <th>Achieved</th>
                                                            <th>%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($c_brands as $b):
                                                            $b_achieved = $achieved_map[$c['account_id'] . ':' . $b['brand_id']] ?? 0;
                                                            // No min() cap
                                                            $b_pct   = $b['target'] > 0
                                                                ? round($b_achieved / $b['target'] * 100)
                                                                : 0;
                                                            $b_color = achievement_color($b_pct);
                                                            $b_star  = $b_pct > 100 ? ' ⭐' : '';
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($b['cat_name']) ?></td>
                                                                <td>₹<?= number_format($b['target']) ?></td>
                                                                <td>₹<?= number_format($b_achieved) ?></td>
                                                                <td>
                                                                    <div style="display:flex;align-items:center;gap:5px;">
                                                                        <div class="mini-bar-wrap">
                                                                            <!-- bar width capped at 100% visually -->
                                                                            <div class="mini-bar"
                                                                                style="width:<?= min($b_pct, 100) ?>%;background:<?= $b_color ?>;"></div>
                                                                        </div>
                                                                        <span class="pct-badge" style="background:<?= $b_color ?>;">
                                                                            <?= $b_pct ?>%<?= $b_star ?>
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>

                                            <?php if (!empty(trim($c['comment']))): ?>
                                                <div class="comment-chip">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                                    </svg>
                                                    <?= htmlspecialchars($c['comment']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </section>

    <?php include("inc/js-file.php"); ?>
    <script>
        $(document).ready(function() {
            $(".chosen-select").chosen({
                width: "100%",
                search_contains: true
            });
        });
    </script>
</body>

</html>