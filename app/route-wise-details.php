<?php
include("appsession.php");
$title    = "Route Wise Details";
$pagename = "route-wise-details.php";
$month_digi = date('m');
$year       = date('Y');
$batch_no = isset($_GET['batch_no']) ? $obj->test_input($_GET['batch_no']) : '';
$view = isset($_GET['view']) && $_GET['view'] === 'daily' ? 'daily' : 'monthly';
$today_dow = date('l');
$route_filter_today = $view === 'daily' ? " AND r.day_of_week = '$today_dow' " : "";
$route_filter_rc = !empty($batch_no) ? " AND rc.batch_no='$batch_no' " : "";
$route_filter_r  = !empty($batch_no) ? " AND r.batch_no='$batch_no' " : "";

/* FIX 1: WHERE 1 added so batch/day filters actually filter (they were
   silently attaching to the LEFT JOIN ON clause before) */
$routes = $obj->executequery("
SELECT
    r.route_id, r.route_name, r.batch_no,
    COALESCE(SUM(mt.total_target),0) AS route_target
FROM (SELECT DISTINCT batch_no FROM route_plan WHERE sales_executive_id='$loginid') rp
INNER JOIN route r ON r.batch_no = rp.batch_no
LEFT JOIN route_counter rc ON rc.batch_no = r.batch_no AND rc.is_active=1
LEFT JOIN monthly_target mt ON mt.account_id = rc.account_id
    AND mt.createdby='$loginid' AND mt.month='$month_digi' AND mt.year='$year'
WHERE 1
$route_filter_r
$route_filter_today
GROUP BY r.route_id, r.route_name, r.batch_no
ORDER BY r.route_name
");

$all_counters = $obj->executequery("
    SELECT a.account_id, a.account_name, r.route_id,
        COALESCE(mt.target_id, 0) AS target_id,
        COALESCE(mt.comment, '') AS comment,
        CASE WHEN mt.target_id IS NULL THEN 0 ELSE 1 END AS has_target
    FROM route_counter rc
    INNER JOIN route r ON r.batch_no = rc.batch_no
    INNER JOIN (SELECT DISTINCT batch_no FROM route_plan WHERE sales_executive_id='$loginid') rp
        ON rp.batch_no = rc.batch_no
    INNER JOIN account a ON a.account_id = rc.account_id
    LEFT JOIN monthly_target mt ON mt.account_id = a.account_id
        AND mt.createdby = '$loginid' AND mt.month = '$month_digi' AND mt.year = '$year'
    WHERE rc.is_active = 1
    $route_filter_rc
    $route_filter_today
    ORDER BY r.route_name, a.account_name
");

/* FIX 2: no route_counter join here — it duplicated every brand row for
   accounts sitting on 2+ batches (targets came out doubled) */
$all_brands = $obj->executequery("
SELECT mtd.target_id, mtd.brand_id, mtd.target, cm.cat_name
FROM monthly_target_details mtd
INNER JOIN monthly_target mt ON mt.target_id = mtd.target_id
INNER JOIN category_master cm ON cm.cat_id = mtd.brand_id
WHERE mt.createdby='$loginid'
  AND mt.month='$month_digi'
  AND mt.year='$year'
");

/* FIX 3: DISTINCT account subquery instead of a direct route_counter join —
   the old join doubled sales for accounts on multiple batches.
   cat_name added so untargeted brand sales can be displayed. */
$all_achieved = $obj->executequery("
SELECT t.account_id, td.brand_id, cm.cat_name, SUM(td.net_amt) AS achieved
FROM transaction_entry t
INNER JOIN transaction_details td ON td.transaction_id = t.transaction_id
INNER JOIN category_master cm ON cm.cat_id = td.brand_id
INNER JOIN (
    SELECT DISTINCT rc.account_id
    FROM route_counter rc
    INNER JOIN (SELECT DISTINCT batch_no FROM route_plan WHERE sales_executive_id='$loginid') rp
        ON rp.batch_no = rc.batch_no
    WHERE rc.is_active = 1
    " . (!empty($batch_no) ? " AND rc.batch_no='$batch_no' " : "") . "
) x ON x.account_id = t.account_id
WHERE t.type='order' AND t.is_approved=1
  AND td.brand_id != 35
  AND MONTH(t.billdate)='$month_digi' AND YEAR(t.billdate)='$year'
GROUP BY t.account_id, td.brand_id, cm.cat_name
");

$counters_by_route = [];
foreach ($all_counters as $c) {
    $counters_by_route[$c['route_id']][] = $c;
}

$brands_by_target = [];
foreach ($all_brands as $b) {
    $brands_by_target[$b['target_id']][] = $b;
}

/* FIX 4: build the same maps as admin side — total sales per account and
   per-account brand detail, so untargeted sales are visible everywhere */
$achieved_map              = [];
$account_total_achieved    = [];
$account_brand_details_map = [];
$brand_ach_map             = [];
$brand_name_map            = [];
foreach ($all_achieved as $a) {
    $acc    = $a['account_id'];
    $brand  = (int)$a['brand_id'];
    $amount = (float)$a['achieved'];

    $achieved_map[$acc . ':' . $brand] = $amount;
    $account_total_achieved[$acc] = ($account_total_achieved[$acc] ?? 0) + $amount;
    $account_brand_details_map[$acc][] = [
        'cat_name' => $a['cat_name'],
        'brand_id' => $brand,
        'target'   => 0,
    ];
    $brand_ach_map[$brand] = ($brand_ach_map[$brand] ?? 0) + $amount;
    $brand_name_map[$brand] = $a['cat_name'];
}

/* FIX 5: brand summary computed in PHP from the two maps instead of the old
   SQL that joined route_plan (sales were multiplied by assigned weeks!).
   Targets are summed per unique target_id over the filtered counter set, so
   batch/day filters apply consistently and no account is counted twice. */
$brand_target_map = [];
$seen_target_ids  = [];
foreach ($all_counters as $c) {
    $tid = $c['target_id'];
    if (!$tid || isset($seen_target_ids[$tid])) continue;
    $seen_target_ids[$tid] = true;
    foreach ($brands_by_target[$tid] ?? [] as $b) {
        $bid = (int)$b['brand_id'];
        $brand_target_map[$bid] = ($brand_target_map[$bid] ?? 0) + (float)$b['target'];
        $brand_name_map[$bid]   = $b['cat_name'];
    }
}

$brand_summary = [];
foreach ($brand_name_map as $bid => $bname) {
    $t = $brand_target_map[$bid] ?? 0;
    $ach = $brand_ach_map[$bid] ?? 0;
    if ($t <= 0 && $ach <= 0) continue;
    $brand_summary[] = ['brand_id' => $bid, 'cat_name' => $bname, 'target' => $t, 'achieved' => $ach];
}
usort($brand_summary, fn($a, $b) => [$b['achieved'], $b['target']] <=> [$a['achieved'], $a['target']]);

function achievement_color(float $pct): string
{
    if ($pct > 100) return 'var(--clr-blue)';
    if ($pct >= 100) return 'var(--clr-green)';
    if ($pct >= 60)  return 'var(--clr-amber)';
    return 'var(--clr-red)';
}

/* FIX 6: DISTINCT so days don't repeat once per route_plan week row */
$route_options = $obj->executequery("
    SELECT r.batch_no, r.route_name,
           GROUP_CONCAT(DISTINCT r.day_of_week
               ORDER BY FIELD(r.day_of_week,'Monday','Tuesday','Wednesday',
                              'Thursday','Friday','Saturday')
               SEPARATOR ', ') AS days
    FROM route r
    INNER JOIN route_plan rp ON rp.batch_no = r.batch_no
    WHERE rp.sales_executive_id = '$loginid'
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

        .brand-scroll {
            display: flex;
            overflow-x: auto;
            gap: 12px;
            padding-bottom: 6px;
            scrollbar-width: none;
        }

        .brand-scroll::-webkit-scrollbar {
            display: none;
        }

        .brand-card {
            min-width: 165px;
            background: #fff;
            border-radius: 14px;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            flex-shrink: 0;
        }

        .brand-name {
            font-weight: 600;
            font-size: 14px;
        }

        .brand-sale {
            color: #0d6efd;
            font-weight: 700;
            font-size: 18px;
            margin-top: 4px;
        }
    </style>
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>
        <div class="container">
            <?php if ($view == "monthly") { ?>
                <!-- Route filter -->
                <div class="col-12 mt-2 mb-3">
                    <form class="card border-0 shadow-sm p-3">
                        <label class="form-label fw-semibold small mb-1">Select a Route</label>
                        <select name="batch_no" id="batch_no" class="form-control chosen-select mb-3">
                            <option value="">All Routes</option>
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
            <?php } ?>
            <?php if (empty($routes)): ?>
                <div class="empty-state">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 13l4.553 2.276A1 1 0 0021 21.382V10.618a1 1 0 00-1.447-.894L15 12m0 8V12M9 7l6-3" />
                    </svg>
                    <p>No targets found for this month.</p>
                </div>

            <?php else: ?>
                <div class="brand-summary mb-3">

                    <h6 class="mb-2 fw-bold bg-white p-1 rounded-4">
                        🏆 Brand Wise
                    </h6>

                    <div class="brand-scroll">

                        <?php foreach ($brand_summary as $b):

                            $target = (float)$b['target'];
                            $ach = (float)$b['achieved'];

                            $pct = $target > 0
                                ? round(($ach / $target) * 100)
                                : 0;

                            $clr = achievement_color($pct);

                        ?>

                            <div class="brand-card">

                                <div class="brand-name">
                                    <?= htmlspecialchars($b['cat_name']) ?>
                                </div>

                                <div class="brand-sale">
                                    ₹<?= number_format($ach) ?>
                                </div>

                                <div class="progress mt-2" style="height:6px;">
                                    <div class="progress-bar"
                                        style="
                        width:<?= min($pct, 100) ?>%;
                        background:<?= $clr ?>;">
                                    </div>
                                </div>

                                <div class="mt-1 d-flex justify-content-between small">

                                    <span>
                                        Target
                                        ₹<?= number_format($target) ?>
                                    </span>

                                    <span style="color:<?= $clr ?>;font-weight:700;">
                                        <?= $pct ?>%
                                        <?= $pct > 100 ? '⭐' : '' ?>
                                    </span>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>
                <?php foreach ($routes as $route):
                    $r_target   = (float)$route['route_target'];
                    $r_achieved = 0;
                    $r_counters = $counters_by_route[$route['route_id']] ?? [];

                    // FIX: all sales of the route's counters, incl. untargeted brands
                    foreach ($r_counters as $c) {
                        $r_achieved += $account_total_achieved[$c['account_id']] ?? 0;
                    }

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
                                    <?php $slno = 1;
                                    foreach ($r_counters as $c):
                                        $c_brands = $brands_by_target[$c['target_id']] ?? [];

                                        // FIX: append sales-only brands (no target on this counter)
                                        $targeted_ids = array_column($c_brands, 'brand_id');
                                        foreach ($account_brand_details_map[$c['account_id']] ?? [] as $extra) {
                                            if (!in_array($extra['brand_id'], $targeted_ids)) {
                                                $c_brands[] = $extra;   // target = 0
                                            }
                                        }
                                        usort($c_brands, fn($a, $b) => strcmp($a['cat_name'], $b['cat_name']));

                                        $c_target   = array_sum(array_column($c_brands, 'target'));
                                        $c_achieved = $account_total_achieved[$c['account_id']] ?? 0;  // ALL sales
                                        $c_pct      = $c_target > 0 ? round($c_achieved / $c_target * 100) : 0;
                                        $c_color = achievement_color($c_pct);
                                        $c_star  = $c_pct > 100 ? ' ⭐' : '';
                                    ?>
                                        <div class="counter-card">
                                            <div class="counter-header">
                                                <span class="counter-name">
                                                    <?= $slno++; ?>. <?= htmlspecialchars($c['account_name']) ?>
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
                                                            $b_tgt = (float)$b['target'];
                                                            $b_pct = $b_tgt > 0 ? round($b_achieved / $b_tgt * 100) : 0;
                                                            $b_color = achievement_color($b_pct);
                                                            $b_star  = $b_pct > 100 ? ' ⭐' : '';
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($b['cat_name']) ?><?= $b_tgt <= 0 ? ' *' : '' ?></td>
                                                                <td><?= $b_tgt > 0 ? '₹' . number_format($b_tgt) : '—' ?></td>
                                                                <td>₹<?= number_format($b_achieved) ?></td>
                                                                <td>
                                                                    <?php if ($b_tgt > 0): ?>
                                                                        <div style="display:flex;align-items:center;gap:5px;">
                                                                            <div class="mini-bar-wrap">
                                                                                <div class="mini-bar"
                                                                                    style="width:<?= min($b_pct, 100) ?>%;background:<?= $b_color ?>;"></div>
                                                                            </div>
                                                                            <span class="pct-badge" style="background:<?= $b_color ?>;">
                                                                                <?= $b_pct ?>%<?= $b_star ?>
                                                                            </span>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="counter-meta">Sales only</span>
                                                                    <?php endif; ?>
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