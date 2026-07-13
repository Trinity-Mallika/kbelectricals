<?php
include("../adminsession.php");
$title      = "Monthly Target Approval";
$pagename   = "monthly_target_view.php";
$module     = "Monthly Target Approval";
$submodule  = "Monthly Target Approval List";
$btn_name   = "Save";
$tblname    = "transaction_entry";
$tblpkey    = "transaction_id";
$action     = (isset($_GET["action"]))    ? $obj->test_input($_GET["action"])    : "";
$createdby  = (isset($_GET["createdby"])) ? $obj->test_input($_GET["createdby"]) : "";
$month      = (isset($_GET["month"]))     ? $obj->test_input($_GET["month"])     : "";
$year       = (isset($_GET["year"]))      ? $obj->test_input($_GET["year"])      : "";

$user_name = $obj->getvalfield("user", "fullname", "userid='$createdby'");

$total_counters = $obj->getvalfield(
    "monthly_target",
    "count(*)",
    "createdby='$createdby' and month='$month' and year='$year'"
);

$grand_target = $obj->getvalfield(
    "monthly_target",
    "ifnull(sum(total_target),0)",
    "createdby='$createdby' and month='$month' and year='$year'"
);

$approval_status = $obj->getvalfield(
    "monthly_target_approval",
    "status",
    "createdby='$createdby' and month='$month' and year='$year'"
);


$grand_achieved_row = $obj->executequery("
    SELECT
        SUM(te.grand_total) AS grand_achieved
    FROM route_plan rp

    INNER JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
       AND rc.is_active = 1

    INNER JOIN transaction_entry te
        ON te.account_id = rc.account_id
       AND te.type = 'order'
       AND te.is_approved = 1
       AND MONTH(te.billdate) = '$month'
       AND YEAR(te.billdate) = '$year'

    WHERE rp.sales_executive_id = '$createdby'
");

$grand_achieved = (float)($grand_achieved_row[0]['grand_achieved'] ?? 0);
$grand_target_f = (float)$grand_target;
$grand_pct      = $grand_target_f > 0 ? round($grand_achieved / $grand_target_f * 100) : 0;

/* ── Helper ───────────────────────────────────────────────────── */
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
    $star  = $pct > 100 ? ' ⭐' : '';
    return "<span style='display:inline-block;font-size:.7rem;font-weight:700;
                padding:2px 7px;border-radius:20px;color:#fff;background:{$color};'>
                {$pct}%{$star}</span>";
}
function mini_bar(float $pct): string
{
    $color   = ach_color($pct);
    $bar_pct = min($pct, 100); // bar width capped at 100% so it doesn't overflow
    return "<div style='display:flex;align-items:center;gap:5px;'>
                <div style='background:#e5e7eb;border-radius:3px;height:5px;width:55px;display:inline-block;vertical-align:middle;'>
                    <div style='width:{$bar_pct}%;height:100%;border-radius:3px;background:{$color};'></div>
                </div>
                " . pct_badge($pct) . "
            </div>";
}

/* ── Pre-fetch all achieved per account+brand (flat map) ─────── */
$all_achieved_rows = $obj->executequery("
    SELECT
        t.account_id,
        td.brand_id,
        SUM(td.net_amt) AS achieved
    FROM transaction_details td
    INNER JOIN transaction_entry t  ON t.transaction_id = td.transaction_id
    INNER JOIN monthly_target    mt ON mt.account_id    = t.account_id
                                    AND mt.createdby    = '$createdby'
                                    AND mt.month        = '$month'
                                    AND mt.year         = '$year'
    WHERE MONTH(t.billdate) = '$month'
      AND YEAR(t.billdate)  = '$year'
      AND t.type            = 'order'
      AND t.is_approved     = 1
    GROUP BY t.account_id, td.brand_id
");
$achieved_map = [];
foreach ($all_achieved_rows as $a) {
    $achieved_map[$a['account_id'] . ':' . $a['brand_id']] = (float)$a['achieved'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        .card-header {
            background: #06163a;
        }

        .badge-pending {
            background: #ffc107;
            color: #000;
        }

        .badge-approved {
            background: #198754;
        }

        .badge-rejected {
            background: #dc3545;
        }

        .toggle-row {
            transition: all .3s ease;
        }

        .toggle-row.active {
            background: #12416a;
        }

        .toggle-row.active th {
            color: #fff;
        }

        .toggle-icon {
            transition: transform .3s ease;
        }

        .toggle-row.active .toggle-icon {
            transform: rotate(180deg);
        }

        /* achievement progress bar inside summary cards */
        .ach-bar-wrap {
            background: rgba(255, 255, 255, .3);
            border-radius: 4px;
            height: 5px;
            margin-top: 6px;
        }

        .ach-bar {
            height: 100%;
            border-radius: 4px;
            background: rgba(255, 255, 255, .95);
        }

        /* brand / route table achieved column */
        .text-achieved {
            color: #198754;
            font-weight: 700;
        }
    </style>
</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>

        <div class="container-fluid">
            <form>
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="mt-2">
                            <legend><?= $title ?></legend>
                            <?php include('component/alert.php'); ?>
                            <div class="card">
                                <div class="card-header text-white"><?= $module ?></div>
                                <div class="card-body">
                                    <div class="row g-3">

                                        <!-- Sales Executive -->
                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-primary text-white">
                                                <div class="card-body">
                                                    <small>Sales Executive</small>
                                                    <h5 class="mb-0"><?= $user_name ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Month -->
                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-info text-white">
                                                <div class="card-body">
                                                    <small>Month</small>
                                                    <h5 class="mb-0"><?= date('F', mktime(0, 0, 0, $month, 1)) . ' ' . $year ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Counters -->
                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-success text-white">
                                                <div class="card-body">
                                                    <small>Total Counters</small>
                                                    <h5 class="mb-0"><?= $total_counters ?></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Grand Target + Achieved -->
                                        <div class="col-md-3">
                                            <div class="card border-0 shadow-sm bg-submenu text-white">
                                                <div class="card-body">
                                                    <small>Grand Target</small>
                                                    <h5 class="mb-0">₹<?= number_format($grand_target) ?></h5>
                                                    <small>Achieved: <strong>₹<?= number_format($grand_achieved) ?></strong>
                                                        <?= pct_badge($grand_pct) ?>
                                                    </small>
                                                    <div class="ach-bar-wrap">
                                                        <div class="ach-bar" style="width:<?= min($grand_pct, 100) ?>%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div><!-- /.row -->
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </form>

            <div class="row mt-3">

                <!-- ── Brand Wise Summary ──────────────────────────────── -->
                <div class="col-lg-6 mb-3">
                    <div class="card mb-3 h-100">
                        <div class="card-header text-white bg-dark">Brand Wise Summary</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr class="table-primary">
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
                                        $brand_sql = $obj->executequery("
                                        SELECT
                                            cm.cat_name,
                                            mtd.brand_id,
                                            SUM(mtd.target) AS total_target
                                        FROM monthly_target_details mtd
                                        LEFT JOIN category_master cm ON cm.cat_id = mtd.brand_id
                                        WHERE mtd.createdby='$createdby'
                                          AND mtd.month='$month'
                                          AND mtd.year='$year'
                                        GROUP BY mtd.brand_id
                                        ORDER BY total_target DESC
                                    ");

                                        $brand_achieved_rows = $obj->executequery("
SELECT
    td.brand_id,
    SUM(td.total_amt) AS achieved
FROM route_plan rp

INNER JOIN route_counter rc
    ON rc.batch_no = rp.batch_no
   AND rc.is_active = 1

INNER JOIN transaction_entry t
    ON t.account_id = rc.account_id
   AND t.type='order'
   AND t.is_approved=1
   AND MONTH(t.billdate)='$month'
   AND YEAR(t.billdate)='$year'

INNER JOIN transaction_details td
    ON td.transaction_id=t.transaction_id

WHERE rp.sales_executive_id='$createdby'

GROUP BY td.brand_id
");
                                        $brand_ach_map = [];
                                        foreach ($brand_achieved_rows as $ba) {
                                            $brand_ach_map[$ba['brand_id']] = (float)$ba['achieved'];
                                        }

                                        foreach ($brand_sql as $row):
                                            $b_ach = $brand_ach_map[$row['brand_id']] ?? 0;
                                            $b_tgt = (float)$row['total_target'];
                                            $b_pct = $b_tgt > 0 ? round($b_ach / $b_tgt * 100) : 0;
                                        ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($row['cat_name']) ?></td>
                                                <td class="text-end">₹<?= number_format($b_tgt) ?></td>
                                                <td class="text-end text-achieved">₹<?= number_format($b_ach) ?></td>
                                                <td><?= mini_bar($b_pct) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Route Wise Summary ──────────────────────────────── -->
                <div class="col-lg-6 mb-3">
                    <div class="card mb-3 h-100">
                        <div class="card-header text-white">Route Wise Summary</div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr class="table-primary">
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
                                    SELECT
                                        rm.route_name,
                                        rm.route_id,
                                        SUM(mt.total_target) AS total_target
                                    FROM monthly_target mt
                                    LEFT JOIN account        a  ON a.account_id  = mt.account_id
                                    LEFT JOIN route_counter  rc ON a.account_id  = rc.account_id
                                    LEFT JOIN route          rm ON rm.batch_no   = rc.batch_no
                                    WHERE mt.createdby='$createdby'
                                      AND mt.month='$month'
                                      AND mt.year='$year'
                                    GROUP BY rm.route_id
                                    ORDER BY route_id ASC
                                ");


                                    $route_ach_rows = $obj->executequery("
SELECT
    rm.route_id,
    SUM(td.net_amt) AS achieved
FROM route_plan rp

INNER JOIN route_counter rc
    ON rc.batch_no=rp.batch_no
   AND rc.is_active=1

INNER JOIN route rm
    ON rm.batch_no=rp.batch_no

INNER JOIN transaction_entry t
    ON t.account_id=rc.account_id
   AND t.type='order'
   AND t.is_approved=1
   AND MONTH(t.billdate)='$month'
   AND YEAR(t.billdate)='$year'

INNER JOIN transaction_details td
    ON td.transaction_id=t.transaction_id

WHERE rp.sales_executive_id='$createdby'

GROUP BY rm.route_id
");
                                    $route_ach_map = [];
                                    foreach ($route_ach_rows as $ra) {
                                        $route_ach_map[$ra['route_id']] = (float)$ra['achieved'];
                                    }

                                    foreach ($route_summary as $row):
                                        $r_ach = $route_ach_map[$row['route_id']] ?? 0;
                                        $r_tgt = (float)$row['total_target'];
                                        $r_pct = $r_tgt > 0 ? round($r_ach / $r_tgt * 100) : 0;
                                    ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($row['route_name']) ?></td>
                                            <td class="text-end">₹<?= number_format($r_tgt) ?></td>
                                            <td class="text-end">₹<?= number_format($r_ach) ?></td>
                                            <td><?= mini_bar($r_pct) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ── Route Wise Counter Details ─────────────────────── -->
                <div class="col-lg-12">
                    <div class="card mb-2">
                        <div class="card-header text-white">
                            Route Wise Counter Details
                            <button type="button" class="btn btn-light btn-sm mb-0 float-end" id="exportExcel">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </button>
                        </div>
                        <div class="card-body">
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
                                WHERE mt.createdby='$createdby'
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
                                                            <th>Brand</th>
                                                            <th class="text-end">Target</th>
                                                            <th class="text-end">Achieved</th>
                                                            <th>%</th>
                                                            <th>Comment</th>
                                                        </tr>

                                                        <?php
                                                        $counter_sql = $obj->executequery("
SELECT
    a.account_id,
    a.account_name,
    mt.target_id,
    mt.comment,
    CASE
        WHEN mt.target_id IS NULL THEN 0
        ELSE 1
    END AS has_target
FROM route_counter rc
INNER JOIN account a
    ON a.account_id = rc.account_id

INNER JOIN route r
    ON r.batch_no = rc.batch_no

LEFT JOIN monthly_target mt
    ON mt.account_id = a.account_id
   AND mt.createdby = '$createdby'
   AND mt.month = '$month'
   AND mt.year = '$year'

WHERE r.route_id='{$route['route_id']}'
ORDER BY a.account_name
");
                                                        foreach ($counter_sql as $counter):
                                                            if ($counter['has_target']) {

                                                                $brand_sql = $obj->executequery("
        SELECT
            cm.cat_name,
            mtd.brand_id,
            mtd.target
        FROM monthly_target_details mtd
        INNER JOIN category_master cm
            ON cm.cat_id=mtd.brand_id
        WHERE mtd.target_id='{$counter['target_id']}'
    ");
                                                            } else {

                                                                $brand_sql = $obj->executequery("
        SELECT
            cm.cat_name,
            td.brand_id,
            0 AS target
        FROM transaction_details td
        INNER JOIN transaction_entry t
            ON t.transaction_id=td.transaction_id

        INNER JOIN category_master cm
            ON cm.cat_id=td.brand_id

        WHERE
            t.account_id='{$counter['account_id']}'
            AND MONTH(t.billdate)='$month'
            AND YEAR(t.billdate)='$year'
            AND t.type='order'
            AND t.is_approved=1

        GROUP BY td.brand_id
    ");
                                                            }

                                                            $rowspan = max(1, count($brand_sql));
                                                            $first   = true;
                                                        ?>
                                                            <?php foreach ($brand_sql as $brand):
                                                                $b_ach = $achieved_map[$counter['account_id'] . ':' . $brand['brand_id']] ?? 0;
                                                                $b_tgt = (float)$brand['target'];
                                                                $b_pct = $b_tgt > 0 ? round($b_ach / $b_tgt * 100) : 0;
                                                            ?>
                                                                <tr>
                                                                    <?php if ($first): ?>
                                                                        <td rowspan="<?= $rowspan ?>" class="align-middle">

                                                                            <?= htmlspecialchars($counter['account_name']) ?>

                                                                            <?php if (!$counter['has_target']) { ?>
                                                                                <span class="badge bg-warning text-dark">
                                                                                    No Target
                                                                                </span>

                                                                            <?php } ?>

                                                                        </td>
                                                                    <?php endif; ?>

                                                                    <td><?= htmlspecialchars($brand['cat_name']) ?></td>
                                                                    <td class="text-end">₹<?= number_format($b_tgt) ?></td>
                                                                    <td class="text-end text-achieved">₹<?= number_format($b_ach) ?></td>
                                                                    <td><?= mini_bar($b_pct) ?></td>

                                                                    <?php if ($first): ?>
                                                                        <td rowspan="<?= $rowspan ?>" class="align-middle text-muted small">
                                                                            <?= htmlspecialchars($counter['comment']) ?>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php $first = false;
                                                            endforeach; ?>
                                                        <?php endforeach; ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div><!-- /col -->

            </div>
        </div><!-- /.container-fluid -->
    </div><!-- /.main -->

    <?php include('component/script.php'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Toggle accordion -->
    <script>
        $(document).on('click', '.toggle-row', function() {
            let content = $(this).next('.detail-row').find('.detail-content');
            content.stop(true, true).slideToggle(300);
            $(this).toggleClass('active');
            let icon = $(this).find('.toggle-icon');
            if ($(this).hasClass('active')) {
                icon.removeClass('bi-chevron-double-down').addClass('bi-chevron-double-up');
            } else {
                icon.removeClass('bi-chevron-double-up').addClass('bi-chevron-double-down');
            }
        });
    </script>

    <!-- Export Excel (updated for 6-column detail table) -->
    <script>
        $('#exportExcel').click(function() {
            let data = [
                ['Route', 'Counter', 'Brand', 'Target', 'Achieved', '%', 'Comment']
            ];

            $('.toggle-row').each(function() {
                let cells = $(this).find('th');
                let routeName = cells.eq(0).text().trim();
                let routeTarget = cells.eq(1).text().replace('Target:', '').trim();
                let routeAch = cells.eq(2).text().replace('Achieved:', '').trim();
                let routePct = cells.eq(3).text().trim();

                data.push([routeName, '', '', routeTarget, routeAch, routePct, '']);

                let currentCounter = '',
                    currentComment = '';

                $(this).next('.detail-row').find('table tr').each(function(index) {
                    if (index === 0) return; // skip header

                    let cols = $(this).find('td');

                    if (cols.length === 6) {
                        currentCounter = cols.eq(0).text().trim();
                        let brand = cols.eq(1).text().trim();
                        let target = cols.eq(2).text().trim();
                        let ach = cols.eq(3).text().trim();
                        let pct = cols.eq(4).text().trim();
                        currentComment = cols.eq(5).text().trim();
                        data.push(['', currentCounter, brand, target, ach, pct, currentComment]);

                    } else if (cols.length === 4) {
                        // subsequent brand rows (rowspan hides counter + comment)
                        let brand = cols.eq(0).text().trim();
                        let target = cols.eq(1).text().trim();
                        let ach = cols.eq(2).text().trim();
                        let pct = cols.eq(3).text().trim();
                        data.push(['', currentCounter, brand, target, ach, pct, '']);
                    }
                });

                data.push(['', '', '', '', '', '', '']);
            });

            let ws = XLSX.utils.aoa_to_sheet(data);
            ws['!cols'] = [{
                wch: 25
            }, {
                wch: 35
            }, {
                wch: 25
            }, {
                wch: 15
            }, {
                wch: 15
            }, {
                wch: 8
            }, {
                wch: 40
            }];
            let wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Route Target');
            XLSX.writeFile(wb, 'Route_Wise_Target_Report.xlsx');
        });
    </script>
</body>

</html>