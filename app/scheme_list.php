<?php include("appsession.php");
$title = "Scheme List";
$scheme_data = $obj->executequery("
SELECT 
    s.scheme_id,
    s.scheme_name,
    a.account_id,
    a.account_name,
    sd.product_id,
    p.product_name,
    sd.qty as slab_qty,

    IFNULL(t.achieved_qty,0) as achieved_qty

FROM route_plan rp

JOIN route_counter rc 
    ON rc.batch_no = rp.batch_no

JOIN account a 
    ON a.account_id = rc.account_id

JOIN scheme_entry s 
    ON s.companyid = rp.companyid

JOIN scheme_details sd 
    ON s.scheme_id = sd.scheme_id

JOIN product_master p 
    ON p.product_id = sd.product_id
LEFT JOIN (
    SELECT 
        t.account_id,
        td.product_id,
        SUM(td.qty) as achieved_qty
    FROM transaction_entry t
    JOIN transaction_details td 
        ON td.transaction_id = t.transaction_id
    WHERE t.type='order'
    GROUP BY t.account_id, td.product_id
) t 
ON t.account_id = a.account_id 
AND t.product_id = sd.product_id

WHERE rp.sales_executive_id='$loginid'
AND rp.companyid='$companyid'
AND CURDATE() BETWEEN s.from_date AND s.todate

ORDER BY a.account_id, sd.qty ASC
");

$schemes = [];

foreach ($scheme_data as $row) {

    $sid = $row['scheme_id'];
    $aid = $row['account_id'];

    $schemes[$sid]['name'] = $row['scheme_name'];
    if (!isset($schemes[$sid]['accounts'][$aid])) {
        $schemes[$sid]['accounts'][$aid] = [
            'name' => $row['account_name'],
            'achieved' => 0,
            'slabs' => [],
            'products' => []
        ];
    }

    $schemes[$sid]['accounts'][$aid]['achieved'] += $row['achieved_qty'];

    if (!in_array($row['slab_qty'], $schemes[$sid]['accounts'][$aid]['slabs'])) {
        $schemes[$sid]['accounts'][$aid]['slabs'][] = $row['slab_qty'];
    }

    if (!in_array($row['product_name'], $schemes[$sid]['accounts'][$aid]['products'])) {
        $schemes[$sid]['accounts'][$aid]['products'][] = $row['product_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>
</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card attendance-card border-0 shadow-lg mb-2">
                        <div class=" d-flex justify-content-between flex-row align-items-center ">
                            <div>
                                <h5 class="mb-0">Scheme Opportunity</h5>
                                <small class="text-dark">Customers closest to next reward</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($schemes)) { ?>
                    <?php foreach ($schemes as $scheme) { ?>
                        <div class="col-12">
                            <div class="card attendance-card border-0 shadow-lg mb-2">
                                <div class="scheme-box ">
                                    <h6 class="mb-0"><?= $scheme['name'] ?></h6>
                                </div>
                                <table class="table mb-0 ">
                                    <?php foreach ($scheme['accounts'] as $acc) {

                                        $achieved = $acc['achieved'];
                                        $slabs = array_unique($acc['slabs']);
                                        sort($slabs);

                                        $current = 0;
                                        $next = 0;

                                        foreach ($slabs as $slab) {
                                            if ($achieved >= $slab) {
                                                $current = $slab;
                                            } else {
                                                $next = $slab;
                                                break;
                                            }
                                        }

                                        if ($next == 0) {
                                            $status = "🎉 Completed";
                                            $badge = "primary";
                                            $need = 0;
                                            $progress = 100;
                                            $icon = "green";
                                        } else {

                                            $need = $next - $achieved;
                                            $progress = ($achieved / $next) * 100;

                                            if ($progress < 70) continue;

                                            if ($progress >= 75) {
                                                $status = "Very Close";
                                                $badge = "danger";
                                                $icon = "red";
                                            } else {
                                                $status = "On Track";
                                                $badge = "success";
                                                $icon = "green";
                                            }
                                        } ?>
                                        <tr>
                                            <td width="45px">
                                                <div class="icon-badge-<?= $icon ?>">
                                                    <i class="bi bi-shop"></i>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-between ms-1 mb-1">
                                                    <h5><?= $acc['name'] ?></h5>
                                                    <small> <span class="badge rounded-pill text-bg-<?= $badge ?>">
                                                            <?= $status ?>
                                                        </span></small>
                                                </div>
                                                <div class="d-flex justify-content-between ms-1">
                                                    <h6 class="text-secondary">
                                                        <span class="text-<?= $badge ?>">
                                                            <?= $achieved ?>
                                                        </span> / <?= ($next ?: $current) ?>
                                                    </h6>

                                                    <h6><?= round($progress) ?>%</h6>
                                                </div>
                                                <div class="progress ms-1" role="progressbar" aria-label="Danger example"
                                                    aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height: 4px;">
                                                    <div class="progress-bar bg-<?= $badge ?>"
                                                        style="width:<?= $progress ?>%">
                                                    </div>
                                                </div>
                                                <small class="text-secondary">
                                                    Need
                                                    <span class="text-<?= $badge ?>">
                                                        <?= $need ?> <?= implode(', ', $acc['products']) ?>
                                                    </span> more
                                                </small>
                                                <?php if ($current > 0) { ?>
                                                    <div class="small text-success ms-1">
                                                        ✅ Achieved <?= $current ?>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </section>
    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>

</body>


</html>