<?php include("../adminsession.php");
$title = "Scheme Progress Report";
$pagename = "scheme_list.php";
$module = "Scheme Progress Report";
$submodule = "Scheme Progress Report";
$btn_name = "Search";
$tblname = "scheme_entry";
$tblpkey = "scheme_id";
$keyvalue = (isset($_GET["scheme_id"])) ? $obj->test_input($_GET["scheme_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$today = date('Y-m-d');
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', strtotime('-30 days'));
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');
$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        :root {
            --brand: #06163a;
            --brand-light: #0d2460;
            --accent: #f0a500;
            --accent2: #e84545;
            --green: #1aad6a;
            --muted: #7b8399;
            --card-bg: #ffffff;
            --page-bg: #f2f5fc;
            --radius: 10px;
        }

        body {
            background: var(--page-bg);
        }

        /* ── Filter card ───────────────────────────── */
        .filter-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid #dce3f0;
            padding: 1rem 1.25rem;
        }

        .filter-card .card-header {
            background: var(--brand);
            border-radius: var(--radius) var(--radius) 0 0;
            color: #fff;
            font-weight: 600;
            padding: .55rem 1rem;
            font-size: .9rem;
        }

        /* ── Scheme block ──────────────────────────── */
        .scheme-block {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid #dce3f0;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .scheme-header {
            background: var(--brand);
            color: #fff;
            padding: .7rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .scheme-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .scheme-meta span {
            background: rgba(255, 255, 255, .15);
            border-radius: 20px;
            padding: 2px 10px;
            font-size: .72rem;
            margin-left: 5px;
        }

        /* ── Slab timeline — expanded card style ──── */
        .slab-timeline-wrap {
            background: linear-gradient(135deg, #f0f5ff 0%, #fafbff 100%);
            border-bottom: 1px solid #dce3f0;
            padding: 1.4rem 2rem 1.6rem;
        }

        .slab-timeline-title {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1.1rem;
        }

        /* Row that holds the nodes + connectors */
        .slab-track {
            display: flex;
            align-items: flex-start;
            width: 100%;
        }

        /* Each slab node takes equal share of space */
        .slab-node {
            flex: 1 1 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            min-width: 80px;
        }

        /* Start node is fixed-width, not flex-1 */
        .slab-node.start-node {
            flex: 0 0 56px;
        }

        /* Connector line sits between nodes, grows to fill gap */
        .slab-connector {
            flex: 1 1 0;
            height: 4px;
            background: #d0d9ee;
            border-radius: 2px;
            margin-top: 22px;
            /* vertically centres with circle */
            min-width: 20px;
        }

        .slab-connector.filled {
            background: var(--green);
        }

        /* Circle badge */
        .slab-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 3px solid #c8d3e8;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .75rem;
            color: var(--muted);
            transition: all .25s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .07);
            flex-shrink: 0;
        }

        .slab-node.achieved .slab-circle {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
            box-shadow: 0 3px 10px rgba(26, 173, 106, .3);
        }

        .slab-node.start-node .slab-circle {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
            width: 46px;
            height: 46px;
            font-size: .65rem;
        }

        /* Info card below each node */
        .slab-info {
            margin-top: .55rem;
            text-align: center;
            width: 100%;
            padding: 0 4px;
        }

        .slab-info .slab-qty {
            font-size: .85rem;
            font-weight: 800;
            color: var(--brand);
            display: block;
            line-height: 1.2;
        }

        .slab-info .slab-level-tag {
            display: inline-block;
            background: #e4eaf5;
            color: var(--brand-light);
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .05em;
            border-radius: 10px;
            padding: 1px 7px;
            margin: 3px 0 4px;
        }

        .slab-node.achieved .slab-info .slab-level-tag {
            background: #d4f5e5;
            color: #0e7d4e;
        }

        .slab-info .slab-reward-box {
            background: #fff;
            border: 1.5px solid #dce3f0;
            border-radius: 8px;
            padding: .28rem .45rem;
            font-size: .72rem;
            color: #1455a4;
            font-weight: 600;
            line-height: 1.35;
            margin-top: 2px;
        }

        .slab-node.achieved .slab-info .slab-reward-box {
            border-color: var(--green);
            background: #f0fff8;
            color: #0e7d4e;
        }

        .slab-node.start-node .slab-info .slab-qty {
            color: var(--muted);
            font-size: .75rem;
        }

        .slab-node.start-node .slab-info .slab-level-tag {
            background: #e4eaf5;
            color: var(--muted);
        }

        /* ── Counter table ─────────────────────────── */
        .counter-table {
            font-size: .82rem;
        }

        .counter-table thead th {
            background: var(--brand-light);
            color: #fff;
            padding: .45rem .65rem;
            border: none;
            white-space: nowrap;
        }

        .counter-table tbody tr:hover {
            background: #f0f5ff;
        }

        .counter-table td {
            vertical-align: middle;
            padding: .45rem .65rem;
        }

        /* ── Progress bar ──────────────────────────── */
        .prog-wrap {
            background: #e8ecf7;
            border-radius: 20px;
            height: 8px;
            min-width: 90px;
            overflow: hidden;
        }

        .prog-fill {
            height: 100%;
            border-radius: 20px;
            background: var(--brand);
            transition: width .5s;
        }

        .prog-fill.c-hot {
            background: var(--accent2);
        }

        .prog-fill.c-warm {
            background: var(--accent);
        }

        .prog-fill.c-done {
            background: var(--green);
        }

        /* ── Pills ─────────────────────────────────── */
        .pill {
            display: inline-block;
            border-radius: 20px;
            padding: 2px 9px;
            font-size: .68rem;
            font-weight: 700;
        }

        .pill-green {
            background: #d4f5e5;
            color: #0e7d4e;
        }

        .pill-orange {
            background: #fef3d4;
            color: #a06800;
        }

        .pill-red {
            background: #fde4e4;
            color: #b91c1c;
        }

        .pill-blue {
            background: #ddeeff;
            color: #1455a4;
        }

        .pill-grey {
            background: #eee;
            color: #555;
        }

        .badge-hot {
            background: #e84545;
            color: #fff;
            border-radius: 12px;
            padding: 1px 8px;
            font-size: .65rem;
            margin-left: 5px;
        }

        .badge-close {
            background: var(--accent);
            color: #fff;
            border-radius: 12px;
            padding: 1px 8px;
            font-size: .65rem;
            margin-left: 5px;
        }

        /* ── Summary strip ─────────────────────────── */
        .summary-strip {
            display: flex;
            gap: .6rem;
            padding: .5rem .9rem .7rem;
            border-bottom: 1px solid #e4eaf5;
            flex-wrap: wrap;
        }

        .sum-box {
            border-radius: 7px;
            padding: .3rem .7rem;
            font-size: .75rem;
            font-weight: 700;
            background: #f0f4ff;
            color: var(--brand);
        }

        .sum-box small {
            display: block;
            font-weight: 400;
            color: var(--muted);
            font-size: .65rem;
        }

        /* ── Slab detail panel ─────────────────────── */
        .slab-detail-row td {
            background: #f6f9ff !important;
            padding: 0 !important;
        }

        .slab-detail-inner {
            padding: .5rem 1.2rem .7rem;
        }

        .slab-detail-inner table {
            font-size: .78rem;
        }

        .slab-detail-inner thead th {
            background: #e4eaf5;
            color: var(--brand);
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>
        <div class="container-fluid">
            <legend><?= $module ?></legend>
            <?php
            $schemes = $obj->executequery("
                SELECT
                    se.scheme_id,
                    se.scheme_name,
                    se.from_date,
                    se.todate,
                    sd.scheme_details_id,
                    sd.qty        AS slab_qty,
                    sd.output     AS slab_reward,
                    sd.product_id
                FROM scheme_entry se
                JOIN scheme_details sd ON sd.scheme_id = se.scheme_id
                WHERE se.todate >= '$today'
                  AND se.companyid = '$companyid'
                ORDER BY se.scheme_id, sd.qty ASC
            ");

            $schemeMap = []; 
            foreach ($schemes as $r) {
                $sid = $r['scheme_id'];
                if (!isset($schemeMap[$sid])) {
                    $schemeMap[$sid] = [
                        'scheme_id'   => $sid,
                        'scheme_name' => $r['scheme_name'],
                        'from_date'   => $r['from_date'],
                        'todate'      => $r['todate'],
                        'slabs'       => [],
                        'product_ids' => [],
                    ];
                }
                $schemeMap[$sid]['slabs'][]      = ['qty' => $r['slab_qty'], 'reward' => $r['slab_reward'], 'product_id' => $r['product_id']];
                $schemeMap[$sid]['product_ids'][] = $r['product_id'];
            }

            $achieved = $obj->executequery("
               SELECT
    se.scheme_id,
    te.account_id,
    a.account_name,
    SUM(oi.qty) AS achieved_qty
FROM scheme_entry se
JOIN (
    SELECT DISTINCT scheme_id, product_id
    FROM scheme_details
) sd ON sd.scheme_id = se.scheme_id
JOIN transaction_entry te
     ON te.billdate BETWEEN se.from_date AND se.todate
    AND te.type = 'order'
    AND te.companyid = '$companyid'
JOIN transaction_details oi
     ON oi.transaction_id = te.transaction_id
    AND oi.product_id = sd.product_id
JOIN account a ON a.account_id = te.account_id
WHERE se.todate >= '$today'
  AND se.companyid = '$companyid'
GROUP BY se.scheme_id, te.account_id
                ORDER BY se.scheme_id, achieved_qty DESC
            ");

            $achievedMap = [];
            foreach ($achieved as $r) {
                $achievedMap[$r['scheme_id']][$r['account_id']] = [
                    'account_name' => $r['account_name'],
                    'achieved'     => (int)$r['achieved_qty'],
                ];
            }
            ?>

            <div class="row mt-3 mb-4">
                <div class="col-lg-12">
                    <?php if (empty($schemeMap)): ?>
                        <div class="alert alert-info">No active schemes found.</div>
                    <?php endif; ?>

                    <?php foreach ($schemeMap as $sid => $scheme):
                        $slabs       = $scheme['slabs'];
                        $counterData = $achievedMap[$sid] ?? [];
                        $totalCounters = count($counterData);
                        $maxSlab  = !empty($slabs) ? $slabs[count($slabs) - 1]['qty'] : 0;
                        $completedCounters = 0;
                        foreach ($counterData as $c) {
                            if ($c['achieved'] >= $maxSlab) $completedCounters++;
                        }
                    ?>
                        <div class="scheme-block">
                            <div class="scheme-header">
                                <div>
                                    <h5>🎯 <?= htmlspecialchars($scheme['scheme_name']) ?></h5>
                                </div>
                                <div class="scheme-meta">
                                    <span>📅 <?= date('d M Y', strtotime($scheme['from_date'])) ?> – <?= date('d M Y', strtotime($scheme['todate'])) ?></span>
                                    <span><?= $totalCounters ?> Counter<?= $totalCounters != 1 ? 's' : '' ?></span>
                                    <span><?= count($slabs) ?> Slab<?= count($slabs) != 1 ? 's' : '' ?></span>
                                </div>
                            </div>
                            <div class="slab-timeline-wrap">
                                <div class="slab-timeline-title">🏆 Scheme Slab Levels &amp; Rewards</div>
                                <div class="slab-track">
                                    <div class="slab-node start-node achieved">
                                        <div class="slab-circle">START</div>
                                        <div class="slab-info">
                                            <span class="slab-qty">0</span>
                                            <span class="slab-level-tag">Base</span>
                                            <div class="slab-reward-box" style="color:var(--muted);border-color:#e0e5f0">No reward yet</div>
                                        </div>
                                    </div>

                                    <?php foreach ($slabs as $i => $slab): ?>
                                        <div class="slab-connector"></div>
                                        <div class="slab-node">
                                            <div class="slab-circle">L<?= $i + 1 ?></div>
                                            <div class="slab-info">
                                                <span class="slab-qty"><?= number_format($slab['qty']) ?> Qty</span>
                                                <span class="slab-level-tag">Level <?= $i + 1 ?></span>
                                                <div class="slab-reward-box"><?= htmlspecialchars($slab['reward']) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                </div>
                            </div>
                            <div class="summary-strip">
                                <?php
                                $hotCount  = 0;
                                $closeCount = 0;
                                $runCount = 0;
                                foreach ($counterData as $c) {
                                    $pct = ($maxSlab > 0) ? ($c['achieved'] / $maxSlab) * 100 : 0;
                                    if ($c['achieved'] >= $maxSlab) {
                                    } // completed
                                    elseif ($pct >= 90) $hotCount++;
                                    elseif ($pct >= 75) $closeCount++;
                                    else                $runCount++;
                                }
                                ?>
                                <div class="sum-box">
                                    <small>Max Slab</small>
                                    <?= number_format($maxSlab) ?> Qty
                                </div>
                                <div class="sum-box" style="background:#d4f5e5;color:#0e7d4e">
                                    <small>Completed</small>
                                    <?= $completedCounters ?> Counters
                                </div>
                                <div class="sum-box" style="background:#fde4e4;color:#b91c1c">
                                    <small>🔥 Hot (≥90%)</small>
                                    <?= $hotCount ?> Counters
                                </div>
                                <div class="sum-box" style="background:#fef3d4;color:#a06800">
                                    <small>⚡ Close (≥75%)</small>
                                    <?= $closeCount ?> Counters
                                </div>
                                <div class="sum-box">
                                    <small>Running</small>
                                    <?= $runCount ?> Counters
                                </div>
                            </div>
                            <div class="table-responsive p-2">
                                <table class="table table-bordered table-sm counter-table mb-1">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Counter Name</th>
                                            <th>Achieved Qty</th>
                                            <?php foreach ($slabs as $i => $slab): ?>
                                                <th>L<?= $i + 1 ?> (<?= number_format($slab['qty']) ?>)</th>
                                            <?php endforeach; ?>
                                            <th>Current Level</th>
                                            <th>Next Slab</th>
                                            <th>Balance Needed</th>
                                            <th>Progress</th>
                                            <th>Reward Earned</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sr = 1;
                                        uasort($counterData, fn($a, $b) => $b['achieved'] <=> $a['achieved']);

                                        foreach ($counterData as $accId => $counter):
                                            $ach     = $counter['achieved'];
                                            $accName = $counter['account_name'];
                                            $currentLevel   = 0;
                                            $currentReward  = '';
                                            $nextSlabQty    = 0;
                                            $nextSlabReward = '';

                                            foreach ($slabs as $i => $slab) {
                                                if ($ach >= $slab['qty']) {
                                                    $currentLevel  = $i + 1;
                                                    $currentReward = $slab['reward'];
                                                } elseif ($nextSlabQty === 0) {
                                                    $nextSlabQty    = $slab['qty'];
                                                    $nextSlabReward = $slab['reward'];
                                                }
                                            }

                                            $isMaxAchieved = ($ach >= $maxSlab && $maxSlab > 0);
                                            $balance = ($nextSlabQty > 0) ? ($nextSlabQty - $ach) : 0;

                                            if ($isMaxAchieved) {
                                                $pct    = 100;
                                                $status = 'Max Achieved';
                                            } elseif ($nextSlabQty > 0) {
                                                $pct    = min(99, round(($ach / $nextSlabQty) * 100));
                                                $status = 'Running';
                                            } else {
                                                $pct    = 0;
                                                $status = 'No Activity';
                                            }

                                            $isHot   = $pct >= 90 && !$isMaxAchieved;
                                            $isClose = $pct >= 75 && !$isMaxAchieved;

                                            $barClass = $isMaxAchieved ? 'c-done' : ($isHot ? 'c-hot' : ($isClose ? 'c-warm' : ''));
                                        ?>
                                            <tr>
                                                <td><?= $sr++ ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($accName) ?></strong>
                                                    <?php if ($isHot):   ?><span class="badge-hot">🔥 Hot</span>
                                                    <?php elseif ($isClose): ?><span class="badge-close">⚡ Close</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong><?= number_format($ach) ?></strong></td>
                                                <?php foreach ($slabs as $i => $slab):
                                                    $lvNum = $i + 1;
                                                    if ($ach >= $slab['qty']):
                                                ?>
                                                        <td class="text-center">
                                                            <span class="pill pill-green">✓ Done</span>
                                                        </td>
                                                    <?php elseif ($lvNum === $currentLevel + 1): ?>
                                                        <td class="text-center">
                                                            <span class="pill pill-orange">In Progress</span><br>
                                                            <span style="font-size:.65rem;color:var(--muted)"><?= number_format($slab['qty'] - $ach) ?> left</span>
                                                        </td>
                                                    <?php else: ?>
                                                        <td class="text-center"><span class="pill pill-grey">—</span></td>
                                                <?php endif;
                                                endforeach; ?>
                                                <td class="text-center">
                                                    <?php if ($currentLevel > 0): ?>
                                                        <span class="pill pill-blue">Level <?= $currentLevel ?></span>
                                                    <?php else: ?>
                                                        <span style="color:var(--muted);font-size:.72rem">Not Started</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= $nextSlabQty > 0 ? number_format($nextSlabQty) . ' Qty' : '<span style="color:var(--green);font-size:.75rem;font-weight:700">Max ✓</span>' ?>
                                                </td>
                                                <td>
                                                    <?php if ($balance > 0): ?>
                                                        <span style="color:#c0392b;font-weight:700"><?= number_format($balance) ?></span>
                                                    <?php else: ?>
                                                        <span style="color:var(--green);font-weight:700">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="min-width:120px">
                                                    <div style="font-size:.68rem;color:var(--muted);margin-bottom:3px"><?= $pct ?>%</div>
                                                    <div class="prog-wrap">
                                                        <div class="prog-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($isMaxAchieved): ?>
                                                        <span class="pill pill-green">Max ✓ <?= htmlspecialchars($currentReward) ?></span>
                                                    <?php elseif ($currentReward): ?>
                                                        <span class="pill pill-blue"><?= htmlspecialchars($currentReward) ?></span>
                                                    <?php else: ?>
                                                        <span style="color:var(--muted);font-size:.7rem">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($status === 'Max Achieved'): ?>
                                                        <span class="pill pill-green">Max Achieved</span>
                                                    <?php elseif ($status === 'Running'): ?>
                                                        <span class="pill pill-orange">Running</span>
                                                    <?php else: ?>
                                                        <span class="pill pill-grey">No Activity</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <?php if (empty($counterData)): ?>
                                            <tr>
                                                <td colspan="<?= 9 + count($slabs) ?>" class="text-center text-muted py-3">No counter activity for this scheme.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</body>
<?php include('component/script.php'); ?>

</html>