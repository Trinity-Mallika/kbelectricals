<?php include("appsession.php");
$pagename = 'dashboard.php';
$title    = 'Dashboard';
$day     = date('d');
$weekday = date('l');
$month   = date('F');
$month_digi   = date('m');
$year    = date('Y');
$suffix  = match (true) {
    in_array($day, [1, 21, 31]) => 'st',
    in_array($day, [2, 22])    => 'nd',
    in_array($day, [3, 23])    => 'rd',
    default                   => 'th'
};

$data          = $obj->getRouteDashboardData($loginid, $companyid);
$currenttotal  = $data['today_target'];
$todayvisit    = $data['today_visit'];
$Monthtotal    = $data['month_target'];
$monthvisit    = $data['month_visit'];
$today_percent = $data['today_percent'];
$month_percent = $data['month_percent'];
$todaysales    = $data['todaysales'];
$Monthsales    = $data['Monthsales'];
$pending_amount = $data['pending_amount'];
$route_plan_id = $data['route_plan_id'];
$batch_no      = $data['batch_no'];

$today_green = $currenttotal > 0 ? min(100, round(($todayvisit / $currenttotal) * 100, 2)) : 0;
$month_green = $Monthtotal   > 0 ? min(100, round(($monthvisit  / $Monthtotal)  * 100, 2)) : 0;

$mn = date('n');
$yr = date('Y');
$monthly_target = $obj->getvalfield(
    "monthly_target",
    "SUM(total_target)",
    "createdby=$loginid AND month=$mn AND year=$yr"
) ?: 0;
$target_green = $monthly_target > 0 ? min(100, round(($Monthsales / $monthly_target) * 100, 2)) : 0;


function barColor(float $pct): string
{
    if ($pct >= 75) return '#27ae60';   // green
    if ($pct >= 40) return '#2980b9';   // blue
    return '#e74c3c';                   // red
}

$raw_schemes = $obj->executequery("
    SELECT s.scheme_id, s.scheme_name,
           a.account_id, a.account_name,
           sd.qty AS slab_qty,
           p.product_name,
           IFNULL(t.achieved_qty, 0) AS achieved_qty
    FROM route_counter rc
    JOIN account a        ON a.account_id = rc.account_id
    JOIN scheme_entry s   ON s.companyid  = rc.companyid
    JOIN scheme_details sd ON s.scheme_id = sd.scheme_id
    JOIN product_master p  ON p.product_id = sd.product_id
    LEFT JOIN (
        SELECT t.account_id, td.product_id, SUM(td.qty) AS achieved_qty
        FROM transaction_entry t
        JOIN transaction_details td ON td.transaction_id = t.transaction_id
        WHERE t.type = 'order'
        GROUP BY t.account_id, td.product_id
    ) t ON t.account_id = a.account_id AND t.product_id = sd.product_id
    WHERE rc.batch_no in ($batch_no) AND rc.companyid = '$companyid'
      AND CURDATE() BETWEEN s.from_date AND s.todate
    ORDER BY a.account_id, sd.qty ASC
");

$schemes = [];
foreach ($raw_schemes as $row) {
    $sid = $row['scheme_id'];
    $aid = $row['account_id'];
    $schemes[$sid]['name'] ??= $row['scheme_name'];
    $schemes[$sid]['accounts'][$aid] ??= ['name' => $row['account_name'], 'achieved' => 0, 'slabs' => [], 'products' => []];
    $schemes[$sid]['accounts'][$aid]['achieved'] += $row['achieved_qty'];
    if (!in_array($row['slab_qty'], $schemes[$sid]['accounts'][$aid]['slabs']))
        $schemes[$sid]['accounts'][$aid]['slabs'][] = $row['slab_qty'];
    if (!in_array($row['product_name'], $schemes[$sid]['accounts'][$aid]['products']))
        $schemes[$sid]['accounts'][$aid]['products'][] = $row['product_name'];
}

foreach ($schemes as $sid => &$scheme) {
    $qualified = [];
    foreach ($scheme['accounts'] as $aid => $acc) {
        $slabs = array_unique($acc['slabs']);
        sort($slabs);
        $achieved = $acc['achieved'];
        $current = $next = 0;
        foreach ($slabs as $s) {
            if ($achieved >= $s) $current = $s;
            else {
                $next = $s;
                break;
            }
        }
        if ($next == 0) {
            $pct = 100;
        } else {
            $pct = round(($achieved / $next) * 100);
            if ($pct < 70) continue;          // skip < 70%
        }
        $qualified[$aid] = compact('acc', 'current', 'next', 'pct', 'achieved');
    }
    if (empty($qualified)) {
        unset($schemes[$sid]);
        continue;
    }
    $scheme['qualified'] = $qualified;
}
unset($scheme);
$current_date = date('Y-m-d');
$next_month_start = date('Y-m-01', strtotime('+1 month'));
$current_month_start = date('Y-m-01');

$start_window = date('Y-m-d', strtotime("$current_month_start -2 days"));
$end_window   = date('Y-m-d', strtotime("$current_month_start +2 days"));

$show_monthly_target = (
    $current_date >= $start_window &&
    $current_date <= $end_window
);

$show_monthly_target = ($current_date >= $start_window && $current_date <= $end_window);

$actions = [
    ['icon' => 'bi-geo-alt-fill',   'label' => 'Daily Visit',     'href' => 'check-in.php',       'btn' => 'Check-In'],
    ['icon' => 'bi-shop-window',    'label' => 'New Counter',     'href' => 'create-counter.php', 'btn' => '+ Add'],
    ['icon' => 'bi-cart-check-fill', 'label' => 'Order Entry',     'href' => 'my-order.php',       'btn' => '+ Add'],
    ['icon' => 'bi-cash-coin',      'label' => 'Payment Entry',   'href' => 'add_payment.php',    'btn' => '+ Add'],

    $show_monthly_target ?
        ['icon' => 'bi-bullseye', 'label' => 'Monthly Target', 'href' => 'monthly_target.php', 'btn' => '+ Add']
        : null,
    ['icon' => 'bi-card-list', 'label' => 'Customer List', 'href' => 'customer-list.php',  'btn' => 'View'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <?php include("inc/css-file.php"); ?>
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>
        <div class="container pb-4">
            <div class="card border-0 shadow-lg mb-3 today-date-card pt-1 pb-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h2 class="today-date"><?= $day ?><sup><?= $suffix ?></sup></h2>
                        <div class="ms-3">
                            <h5 class="text-blue mb-1"><?= $weekday ?></h5>
                            <h6 class="text-secondary mb-0"><?= $month ?>, <?= $year ?></h6>
                        </div>
                    </div>
                    <img src="img/icon/calendar.png" alt="" width="40px">
                </div>
            </div>
            <h5 class="sub-title mb-2 head-text">Performance Dashboard</h5>
            <div class="perf-grid">
                <div class="perf-card">
                    <div class="pc-label">Today's Visit</div>
                    <div class="pc-bar"><span style="width:<?= $today_green ?>%;background:<?= barColor($today_green) ?>"></span></div>
                    <div class="pc-foot"><span><?= $todayvisit ?>/<?= $currenttotal ?></span><span><?= $today_percent ?>%</span></div>
                </div>
                <div class="perf-card" style="display:flex;flex-direction:column;justify-content:space-between">
                    <div class="pc-label">Today's Sales</div>
                    <div class="pc-val">₹<?= number_format($todaysales) ?></div>
                </div>
                <div class="perf-card">
                    <div class="pc-label">Monthly Visit</div>
                    <div class="pc-bar"><span style="width:<?= $month_green ?>%;background:<?= barColor($month_green) ?>"></span></div>
                    <div class="pc-foot"><span><?= $monthvisit ?>/<?= $Monthtotal ?></span><span><?= $month_percent ?>%</span></div>
                </div>
                <div class="perf-card" style="display:flex;flex-direction:column;justify-content:space-between">
                    <div class="pc-label">Monthly Sales</div>
                    <div class="pc-val">₹<?= number_format($Monthsales) ?></div>
                </div>
                <div class="perf-card full">
                    <a href="route-wise-details.php">
                        <div class="pc-label">Month Target vs Achievement</div>
                        <div class="pc-bar">
                            <span style="width:<?= $target_green ?>%;background:<?= barColor($target_green) ?>"></span>
                        </div>
                        <div class="pc-foot">
                            <span>₹<?= number_format($Monthsales) ?> / ₹<?= number_format($monthly_target) ?></span>
                            <span><?= $target_green ?>%</span>
                        </div>
                    </a>
                </div>

            </div>
            <div class="pending-card" data-bs-toggle="offcanvas" data-bs-target="#pendingPayment" style="cursor:pointer">
                <div class="pc-left">
                    <div class="pc-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <h6>Today's Route Pending Payment</h6>
                        <small>Tap to view details</small>
                    </div>
                </div>
                <div class="pc-amt">₹<?= number_format($pending_amount, 2) ?></div>
            </div>
            <?php if (!empty($schemes)): ?>
                <div class="scheme-section">
                    <div class="ss-head">
                        <div>
                            <h5>Scheme Opportunity</h5>
                            <small>Customers closest to next reward</small>
                        </div>
                        <a href="scheme_list.php" class="text-blue fw-semibold" style="font-size:.78rem">View All</a>
                    </div>

                    <?php foreach ($schemes as $scheme):
                        if (empty($scheme['qualified'])) continue; ?>
                        <div class="scheme-name-badge"><?= htmlspecialchars($scheme['name']) ?></div>

                        <?php foreach ($scheme['qualified'] as $aid => $q):
                            $acc      = $q['acc'];
                            $achieved = $q['achieved'];
                            $next     = $q['next'];
                            $current  = $q['current'];
                            $pct      = $q['pct'];
                            $need     = max(0, $next - $achieved);
                            $isMax    = ($next == 0);
                            $iconCls  = $isMax || $pct >= 75 ? 'red' : 'green';
                            if ($isMax) {
                                $badge = 'success';
                                $label = '🎉 Max';
                            } elseif ($pct >= 75) {
                                $badge = 'danger';
                                $label = 'Very Close';
                            } else {
                                $badge = 'warning';
                                $label = 'On Track';
                            }
                        ?>
                            <div class="sch-row">
                                <div class="sch-icon <?= $iconCls ?>"><i class="bi bi-shop"></i></div>
                                <div class="sch-body">
                                    <div class="sb-row2">
                                        <div class="sb-name"><?= htmlspecialchars($acc['name']) . $aid ?></div>
                                        <span class="badge-xs text-bg-<?= $badge ?>"><?= $label ?></span>
                                    </div>
                                    <div class="sb-nums">
                                        <b><?= $achieved ?></b> / <?= $isMax ? $current : $next ?> &nbsp;·&nbsp; <?= $pct ?>%
                                    </div>
                                    <div class="sch-bar"><span style="width:<?= $pct ?>%;background:<?= $isMax ? '#27ae60' : ($pct >= 75 ? '#e74c3c' : '#f39c12') ?>"></span></div>
                                    <div class="sch-need">
                                        <?php if ($isMax): ?>
                                            <b class="done">✅ All slabs achieved</b>
                                        <?php else: ?>
                                            Need <b><?= $need ?> <?= implode(', ', $acc['products']) ?></b> more
                                            <?php if ($current > 0): ?>&nbsp;· <span style="color:#27ae60">✅ <?= $current ?> achieved</span><?php endif; ?>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="qa-section">
                <div class="qa-head">
                    <i class="bi bi-lightning-charge-fill me-2" style="color:#f39c12"></i>
                    Quick Actions
                </div>

                <div class="qa-grid">
                    <?php foreach ($actions as $a): ?>
                        <?php if (empty($a)) continue; ?>
                        <div class="qa-item">
                            <div class="qa-left">
                                <i class="bi <?= $a['icon'] ?>"></i>
                                <span><?= $a['label'] ?></span>
                            </div>
                            <a href="<?= $a['href'] ?>" class="btn-qa"><?= $a['btn'] ?></a>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="pendingPayment" style="height:60%;border-radius:20px 20px 0 0;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Today's Route Pending Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body small bg-body-tertiary rounded-top-4" id="pendingPaymentBody">
            <div class="text-center p-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2 mb-0">Loading...</p>
            </div>
        </div>
    </div>

    <?php include("inc/js-file.php"); ?>
    <script>
        $(document).ready(function() {
            let loaded = false;
            $('#pendingPayment').on('show.bs.offcanvas', function() {
                if (loaded) return;
                $.post('ajax/get_pending_payment.php', {
                        route_plan_ids: <?= json_encode(explode(',', $route_plan_id)) ?>,
                        companyid: <?= $companyid ?>
                    },
                    function(res) {
                        $('#pendingPaymentBody').html(res);
                        loaded = true;
                    }
                ).fail(function() {
                    $('#pendingPaymentBody').html('<div class="alert alert-danger m-3">Failed to load data</div>');
                });
            });
        });
    </script>
</body>

</html>