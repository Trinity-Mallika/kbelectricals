<?php include("action.php");

$TEST_EMP_ID   = 3;     
$TEST_MONTH    = 6;       
$TEST_YEAR     = 2026;
$TEST_COMPANY  = 1;

$start = date("$TEST_YEAR-$TEST_MONTH-01");
$end   = date("Y-m-t", strtotime($start));

echo "<style>
body { font-family: monospace; background:#0f172a; color:#e2e8f0; padding:20px; font-size:13px; }
h2   { color:#38bdf8; border-bottom:1px solid #334155; padding-bottom:6px; margin-top:30px; }
h3   { color:#a78bfa; margin-top:20px; }
.ok  { color:#4ade80; }
.warn{ color:#facc15; }
.err { color:#f87171; }
.box { background:#1e293b; border:1px solid #334155; border-radius:6px; padding:14px 18px; margin:10px 0; }
.row { display:flex; gap:20px; margin:4px 0; }
.lbl { color:#94a3b8; min-width:240px; }
.val { color:#f1f5f9; font-weight:bold; }
table { border-collapse:collapse; width:100%; margin:10px 0; }
th   { background:#1e3a5f; color:#7dd3fc; padding:6px 12px; text-align:left; }
td   { padding:5px 12px; border-bottom:1px solid #1e293b; }
tr:hover td { background:#1e293b; }
.pass { background:#14532d; }
.fail { background:#450a0a; }
</style>";

echo "<h1 style='color:#f8fafc'>🧪 KRA Debug Test — emp_id=$TEST_EMP_ID | $TEST_MONTH/$TEST_YEAR</h1>";

// ════════════════════════════════════════════════════════════
// STEP 0 — WHO IS THIS EMPLOYEE?
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 0 — Employee Info</h2><div class='box'>";
$emp = $obj->executequery("SELECT * FROM user WHERE userid='$TEST_EMP_ID' AND companyid='$TEST_COMPANY' LIMIT 1");
if (empty($emp)) {
    echo "<span class='err'>❌ Employee NOT FOUND. Check TEST_EMP_ID.</span>";
    exit;
}
$emp = $emp[0];
row("Name",     $emp['fullname']);
row("Username", $emp['username']);
row("Type",     $emp['usertype']);
row("Status",   $emp['status'] == 1 ? "<span class='ok'>Active</span>" : "<span class='err'>Inactive</span>");
echo "</div>";

// ════════════════════════════════════════════════════════════
// STEP 1 — RAW DATA CHECKS
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 1 — Raw Source Data</h2>";

// daily_productivity rows
echo "<h3>1a. daily_productivity rows (visit data)</h3>";
$dpRows = $obj->executequery("SELECT date, visit_count, total_counters, active_counters FROM daily_productivity WHERE emp_id='$TEST_EMP_ID' AND date BETWEEN '$start' AND '$end' AND companyid='$TEST_COMPANY' ORDER BY date");
if (empty($dpRows)) {
    echo "<span class='warn'>⚠ No daily_productivity rows found for this month.</span><br>";
} else {
    table(['Date','Visit Count','Total Counters','Active Counters'], $dpRows);
    $total_visits = array_sum(array_column($dpRows, 'visit_count'));
    $days_worked  = count(array_filter($dpRows, fn($r) => $r['visit_count'] > 0));
    $visit_avg    = $days_worked > 0 ? round($total_visits / $days_worked, 2) : 0;
    echo "<div class='box'>";
    row("Total Visits (sum)",    $total_visits);
    row("Days with visits",      $days_worked);
    row("Visit Avg (per day worked)", "<b>$visit_avg</b>");
    echo "</div>";
}

// route counters
echo "<h3>1b. Route counters assigned to this executive</h3>";
$routeRows = $obj->executequery("SELECT rp.batch_no, rp.week_number, COUNT(DISTINCT rc.account_id) as counters FROM route_plan rp JOIN route_counter rc ON rc.batch_no = rp.batch_no WHERE rp.sales_executive_id='$TEST_EMP_ID' AND rp.companyid='$TEST_COMPANY' AND rc.is_active=1 GROUP BY rp.batch_no, rp.week_number ORDER BY rp.week_number, rp.batch_no");
if (empty($routeRows)) {
    echo "<span class='warn'>⚠ No route counters found. Productivity will be 0.</span><br>";
} else {
    table(['Batch No','Week No','Active Counters'], $routeRows);
    $total_counters = $obj->getvalfield("route_counter rc JOIN route_plan rp ON rp.batch_no = rc.batch_no", "COUNT(DISTINCT rc.account_id)", "rp.sales_executive_id='$TEST_EMP_ID' AND rp.companyid='$TEST_COMPANY' AND rc.is_active=1");
    echo "<div class='box'>";
    row("Total Unique Assigned Counters (denominator)", "<b>$total_counters</b>");
    echo "</div>";
}

// orders this month
echo "<h3>1c. Orders placed this month (transaction_entry)</h3>";
$orderRows = $obj->executequery("SELECT t.transaction_id, t.billdate, a.account_name, a.class, t.grand_total FROM transaction_entry t JOIN account a ON a.account_id = t.account_id WHERE t.createdby='$TEST_EMP_ID' AND t.type='order' AND t.billdate BETWEEN '$start' AND '$end' AND t.is_approved=1 AND t.companyid='$TEST_COMPANY' ORDER BY t.billdate");
if (empty($orderRows)) {
    echo "<span class='warn'>⚠ No approved orders found this month. Business/ProductMix will be 0.</span><br>";
} else {
    table(['ID','Bill Date','Account','Class','Grand Total'], $orderRows);
    $totalBusiness = array_sum(array_column($orderRows, 'grand_total'));
    echo "<div class='box'>";
    row("Total Business (rupees)", number_format($totalBusiness, 2));
    row("Total Business (lakhs)",  "<b>" . round($totalBusiness/100000, 4) . " L</b>");
    echo "</div>";
}

// productivity config
echo "<h3>1d. kra_productivity_config (class thresholds)</h3>";
$pcRows = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config WHERE companyid='$TEST_COMPANY'");
table(['Class','Min Sales (₹)'], $pcRows);

// active accounts check
echo "<h3>1e. Which accounts qualify as ACTIVE for productivity?</h3>";
$accRows = $obj->executequery("SELECT a.account_id, a.account_name, a.class, SUM(t.grand_total) as sales FROM transaction_entry t JOIN account a ON a.account_id = t.account_id WHERE t.type='order' AND t.createdby='$TEST_EMP_ID' AND t.billdate BETWEEN '$start' AND '$end' AND t.is_approved=1 AND t.companyid='$TEST_COMPANY' GROUP BY a.account_id ORDER BY a.class");
if (!empty($accRows)) {
    $configRows    = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config WHERE companyid='$TEST_COMPANY'");
    $classMinSales = [];
    foreach ($configRows as $c) $classMinSales[strtoupper($c['class'])] = $c['min_sales'];

    $active = 0;
    $tableData = [];
    foreach ($accRows as $acc) {
        $cls     = strtoupper($acc['class']);
        $min     = $classMinSales[$cls] ?? null;
        $qualifies = ($min !== null && $acc['sales'] >= $min);
        if ($qualifies) $active++;
        $tableData[] = [
            'account'   => $acc['account_name'],
            'class'     => $acc['class'],
            'sales'     => number_format($acc['sales'], 2),
            'min_needed'=> $min !== null ? number_format($min, 2) : '⚠ NO CONFIG',
            'status'    => $qualifies ? "<span class='ok'>✓ Active</span>" : "<span class='err'>✗ Not Active</span>",
        ];
    }
    echo "<table><tr><th>Account</th><th>Class</th><th>Sales (₹)</th><th>Min Needed (₹)</th><th>Status</th></tr>";
    foreach ($tableData as $r) {
        echo "<tr><td>{$r['account']}</td><td>{$r['class']}</td><td>{$r['sales']}</td><td>{$r['min_needed']}</td><td>{$r['status']}</td></tr>";
    }
    echo "</table>";
    $total_counters_chk = $obj->getvalfield("route_counter rc JOIN route_plan rp ON rp.batch_no = rc.batch_no", "COUNT(DISTINCT rc.account_id)", "rp.sales_executive_id='$TEST_EMP_ID' AND rp.companyid='$TEST_COMPANY' AND rc.is_active=1") ?: 0;
    $productivity_chk = $total_counters_chk > 0 ? round(($active / $total_counters_chk) * 100, 2) : 0;
    echo "<div class='box'>";
    row("Active Accounts", "<b>$active</b>");
    row("Total Counters",  "<b>$total_counters_chk</b>");
    row("Productivity %",  "<b>$productivity_chk%</b>");
    echo "</div>";
}

// product mix
echo "<h3>1f. Product mix per day</h3>";
$mixRows = $obj->executequery("SELECT DATE(t.billdate) as day, COUNT(DISTINCT td.product_id) as products FROM transaction_entry t JOIN transaction_details td ON td.transaction_id = t.transaction_id WHERE t.createdby='$TEST_EMP_ID' AND t.type='order' AND t.billdate BETWEEN '$start' AND '$end' AND t.is_approved=1 AND t.companyid='$TEST_COMPANY' GROUP BY DATE(t.billdate) ORDER BY day");
if (empty($mixRows)) {
    echo "<span class='warn'>⚠ No product mix data found.</span><br>";
} else {
    table(['Day','Distinct Products'], $mixRows);
    $avgMix = round(array_sum(array_column($mixRows, 'products')) / count($mixRows), 2);
    echo "<div class='box'>";
    row("Avg Products/Day", "<b>$avgMix</b>");
    echo "</div>";
}

// behaviour
echo "<h3>1g. Behaviour scores</h3>";
$behRows = $obj->executequery("SELECT kb.name, kbs.score FROM kra_behaviour_score kbs JOIN kra_behaviour kb ON kb.kra_behaviour_id = kbs.behaviour_id WHERE kbs.emp_id='$TEST_EMP_ID' AND kbs.month='$TEST_MONTH' AND kbs.year='$TEST_YEAR'");
if (empty($behRows)) {
    echo "<span class='warn'>⚠ No behaviour scores entered for this month.</span><br>";
} else {
    table(['Criteria','Score'], $behRows);
    $behTotal = min(array_sum(array_column($behRows, 'score')), 4);
    echo "<div class='box'>";
    row("Behaviour Total (capped at 4)", "<b>$behTotal</b>");
    echo "</div>";
}

// ════════════════════════════════════════════════════════════
// STEP 2 — KRA CONFIG SLABS
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 2 — KRA Config Slabs</h2>";
$kraSlabs = $obj->executequery("SELECT kra_key, min_value, max_value, points FROM kra_config WHERE company_id='$TEST_COMPANY' ORDER BY kra_key, min_value");
table(['KRA Key','Min','Max','Points'], $kraSlabs);

// ════════════════════════════════════════════════════════════
// STEP 3 — POINTS CALCULATION (Manual Trace)
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 3 — Points Calculation Trace</h2>";

// Recalculate everything cleanly
$total_visits2 = $obj->getvalfield("daily_productivity","SUM(visit_count)","emp_id='$TEST_EMP_ID' AND visit_count > 0 AND date BETWEEN '$start' AND '$end' AND companyid='$TEST_COMPANY'") ?: 0;
$days_worked2  = $obj->getvalfield("daily_productivity","COUNT(*)","emp_id='$TEST_EMP_ID' AND visit_count > 0 AND date BETWEEN '$start' AND '$end' AND companyid='$TEST_COMPANY'") ?: 1;
$visit_avg2    = round($total_visits2 / $days_worked2, 2);

$total_counters2 = $obj->getvalfield("route_counter rc JOIN route_plan rp ON rp.batch_no = rc.batch_no","COUNT(DISTINCT rc.account_id)","rp.sales_executive_id='$TEST_EMP_ID' AND rp.companyid='$TEST_COMPANY' AND rc.is_active=1") ?: 0;

$accRows2 = $obj->executequery("SELECT a.account_id, a.class, SUM(t.grand_total) as sales FROM transaction_entry t JOIN account a ON a.account_id = t.account_id WHERE t.type='order' AND t.createdby='$TEST_EMP_ID' AND t.billdate BETWEEN '$start' AND '$end' AND t.is_approved=1 AND t.companyid='$TEST_COMPANY' GROUP BY a.account_id");
$configRows2 = $obj->executequery("SELECT class, min_sales FROM kra_productivity_config WHERE companyid='$TEST_COMPANY'");
$classMin2 = [];
foreach ($configRows2 as $c) $classMin2[strtoupper($c['class'])] = $c['min_sales'];
$active2 = 0;
foreach ($accRows2 as $a) { $cls = strtoupper($a['class']); if (isset($classMin2[$cls]) && $a['sales'] >= $classMin2[$cls]) $active2++; }
$productivity2 = $total_counters2 > 0 ? round(($active2/$total_counters2)*100, 2) : 0;

$product_mix2 = $obj->getvalfield("(SELECT COUNT(DISTINCT td.product_id) as mix FROM transaction_entry t JOIN transaction_details td ON td.transaction_id=t.transaction_id WHERE t.createdby='$TEST_EMP_ID' AND t.type='order' AND t.billdate BETWEEN '$start' AND '$end' AND t.is_approved=1 AND t.companyid='$TEST_COMPANY' GROUP BY DATE(t.billdate)) x","AVG(mix)","1=1") ?: 0;

$business2      = $obj->getvalfield("transaction_entry","SUM(grand_total)","createdby='$TEST_EMP_ID' AND type='order' AND billdate BETWEEN '$start' AND '$end' AND is_approved=1 AND companyid='$TEST_COMPANY'") ?: 0;
$business_lakh2 = $business2 / 100000;

$behaviour2 = $obj->getvalfield("kra_behaviour_score","SUM(score)","emp_id='$TEST_EMP_ID' AND month='$TEST_MONTH' AND year='$TEST_YEAR'") ?: 0;
$behaviour2 = min($behaviour2, 4);

$visit_pts2    = $obj->getKraPoints('visit',        $visit_avg2);
$prod_pts2     = $obj->getKraPoints('productivity', $productivity2);
$mix_pts2      = $obj->getKraPoints('product_mix',  $product_mix2);
$biz_pts2      = $obj->getKraPoints('business',     $business_lakh2);

$total_score2  = ($visit_pts2*20) + ($prod_pts2*20) + ($mix_pts2*20) + ($biz_pts2*30) + ($behaviour2*10);
$achievement2  = round(($total_score2/220)*100, 2);

echo "<div class='box'>";
echo "<table>";
echo "<tr><th>KRA</th><th>Raw Value</th><th>Points Earned</th><th>Max Pts</th><th>Weightage</th><th>Weighted Score</th></tr>";
kraRow("Visit (avg/day)",     $visit_avg2,      $visit_pts2, 2, 20);
kraRow("Productivity (%)",    $productivity2.'%', $prod_pts2, 2, 20);
kraRow("Product Mix (avg)",   $product_mix2,    $mix_pts2,  2, 20);
kraRow("Business (₹ lakhs)",  $business_lakh2,  $biz_pts2,  2, 30);
kraRow("Behaviour (pts)",     $behaviour2,      $behaviour2, 4, 10);
echo "<tr style='background:#0f2a4a;font-weight:bold'>
    <td colspan='5' style='text-align:right;color:#7dd3fc'>Total Score</td>
    <td style='color:#38bdf8'>$total_score2 / 220</td></tr>";
echo "<tr style='background:#0f2a4a;font-weight:bold'>
    <td colspan='5' style='text-align:right;color:#7dd3fc'>Achievement %</td>
    <td style='color:#38bdf8'>$achievement2%</td></tr>";
echo "</table></div>";

// eligibility
echo "<div class='box'>";
if ($achievement2 >= 75)     echo "<span class='ok'>✅ ELIGIBLE for Increment + Incentive (&gt;=75%)</span>";
elseif ($achievement2 >= 50) echo "<span class='warn'>⬆ ELIGIBLE for Increment Only (50–75%)</span>";
elseif ($achievement2 >= 40) echo "<span class='warn'>⚠ NOT ELIGIBLE (40–50%)</span>";
else                          echo "<span class='err'>❌ Performance Improvement Letter (&lt;40%)</span>";
echo "</div>";

// ════════════════════════════════════════════════════════════
// STEP 4 — INCENTIVE CALCULATION TRACE
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 4 — Incentive Calculation Trace</h2>";

echo "<h3>4a. Incentive Slabs Config</h3>";
$slabRows = $obj->executequery("SELECT type, min_value, max_value, amount FROM incentive_slabs WHERE company_id='$TEST_COMPANY' ORDER BY type, min_value");
table(['Type','Min','Max','Amount (₹/beat)'], $slabRows);

echo "<h3>4b. Visit data for incentive</h3>";
$visitInc = $obj->executequery("SELECT date, visit_count as val FROM daily_productivity WHERE emp_id='$TEST_EMP_ID' AND date BETWEEN '$start' AND '$end' AND companyid='$TEST_COMPANY' ORDER BY date");
table(['Date','Visit Count'], $visitInc);

echo "<h3>4c. Sales per day for incentive (in lakhs)</h3>";
$salesInc = $obj->executequery("SELECT DATE(billdate) as day, SUM(grand_total)/100000 as val FROM transaction_entry WHERE createdby='$TEST_EMP_ID' AND type='order' AND is_approved=1 AND billdate BETWEEN '$start' AND '$end' AND companyid='$TEST_COMPANY' GROUP BY DATE(billdate)");
table(['Day','Sales (Lakhs)'], $salesInc);

echo "<h3>4d. Product mix per day for incentive</h3>";
$mixInc = $obj->executequery("SELECT DATE(t.billdate) as day, COUNT(DISTINCT td.product_id) as val FROM transaction_entry t JOIN transaction_details td ON td.transaction_id=t.transaction_id WHERE t.createdby='$TEST_EMP_ID' AND t.type='order' AND t.is_approved=1 AND t.billdate BETWEEN '$start' AND '$end' AND t.companyid='$TEST_COMPANY' GROUP BY DATE(t.billdate)");
table(['Day','Products'], $mixInc);

echo "<h3>4e. Collection days per order</h3>";
$collInc = $obj->executequery("SELECT o.transaction_id, o.billdate as order_date, DATE(p.first_payment) as payment_date, DATEDIFF(p.first_payment, o.billdate) as val FROM transaction_entry o JOIN (SELECT ref_bill_id, MIN(createdate) as first_payment FROM transaction_entry WHERE type='payment' AND companyid='$TEST_COMPANY' GROUP BY ref_bill_id) p ON p.ref_bill_id=o.transaction_id WHERE o.type='order' AND o.createdby='$TEST_EMP_ID' AND o.is_approved=1 AND o.billdate BETWEEN '$start' AND '$end' AND o.companyid='$TEST_COMPANY'");
if (empty($collInc)) {
    echo "<span class='warn'>⚠ No payments linked to orders this month. Collection incentive = ₹0.</span><br>";
} else {
    table(['Order ID','Order Date','First Payment Date','Days'], $collInc);
}

// Final incentive
$visit_inc2 = $obj->calculateIncentiveFlexible('visit',       array_map(fn($r)=>['val'=>$r['val']], $visitInc), $TEST_COMPANY);
$sales_inc2 = $obj->calculateIncentiveFlexible('sales',       $salesInc,  $TEST_COMPANY);
$mix_inc2   = $obj->calculateIncentiveFlexible('product_mix', $mixInc,    $TEST_COMPANY);
$coll_inc2  = $obj->calculateIncentiveFlexible('collection',  $collInc,   $TEST_COMPANY);
$total_inc2 = $visit_inc2 + $sales_inc2 + $mix_inc2 + $coll_inc2;

echo "<h3>4f. Incentive Summary</h3><div class='box'>";
echo "<table>";
echo "<tr><th>Component</th><th>Amount (₹)</th></tr>";
echo "<tr><td>Visit Incentive</td><td>₹".number_format($visit_inc2)."</td></tr>";
echo "<tr><td>Sales Incentive</td><td>₹".number_format($sales_inc2)."</td></tr>";
echo "<tr><td>Product Mix Incentive</td><td>₹".number_format($mix_inc2)."</td></tr>";
echo "<tr><td>Collection Incentive</td><td>₹".number_format($coll_inc2)."</td></tr>";
echo "<tr style='background:#0f2a4a;font-weight:bold'><td>TOTAL INCENTIVE</td><td style='color:#4ade80'>₹".number_format($total_inc2)."</td></tr>";
echo "</table>";
echo "<br><small>70% payout (₹".number_format($total_inc2*0.70).") after 3 months · 30% (₹".number_format($total_inc2*0.30).") in April</small>";
echo "</div>";

// ════════════════════════════════════════════════════════════
// STEP 5 — COMPARE WITH SAVED DATA
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 5 — Compare Calculated vs Saved in DB</h2>";

$saved = $obj->executequery("SELECT * FROM monthly_kra WHERE emp_id='$TEST_EMP_ID' AND month='$TEST_MONTH' AND year='$TEST_YEAR' AND companyid='$TEST_COMPANY' LIMIT 1");
$savedInc = $obj->executequery("SELECT * FROM monthly_incentive WHERE sales_executive_id='$TEST_EMP_ID' AND month_name='$TEST_MONTH' AND year='$TEST_YEAR' AND companyid='$TEST_COMPANY' LIMIT 1");

if (empty($saved)) {
    echo "<div class='box'><span class='warn'>⚠ No saved monthly_kra record found. Cron hasn't run yet for this month.</span></div>";
} else {
    $s = $saved[0];
    echo "<table>";
    echo "<tr><th>Field</th><th>Calculated Now</th><th>Saved in DB</th><th>Match?</th></tr>";
    compareRow("visit_value",        $visit_avg2,       $s['visit_value']);
    compareRow("productivity_value", $productivity2,    $s['productivity_value']);
    compareRow("product_mix_value",  round($product_mix2,2), $s['product_mix_value']);
    compareRow("business_value",     round($business_lakh2,4), $s['business_value']);
    compareRow("behaviour_value",    $behaviour2,       $s['behaviour_value']);
    compareRow("visit_points",       $visit_pts2,       $s['visit_points']);
    compareRow("productivity_points",$prod_pts2,        $s['productivity_points']);
    compareRow("product_mix_points", $mix_pts2,         $s['product_mix_points']);
    compareRow("business_points",    $biz_pts2,         $s['business_points']);
    compareRow("total_score",        $total_score2,     $s['total_score']);
    compareRow("achievement_pct",    $achievement2,     $s['achievement_pct']);
    echo "</table>";
}

if (!empty($savedInc)) {
    $si = $savedInc[0];
    echo "<br><table>";
    echo "<tr><th>Incentive Field</th><th>Calculated Now</th><th>Saved in DB</th><th>Match?</th></tr>";
    compareRow("visit_incentive",       $visit_inc2,  $si['visit_incentive']);
    compareRow("sales_incentive",       $sales_inc2,  $si['sales_incentive']);
    compareRow("product_mix_incentive", $mix_inc2,    $si['product_mix_incentive']);
    compareRow("collection_incentive",  $coll_inc2,   $si['collection_incentive']);
    compareRow("total_incentive",       $total_inc2,  $si['total_incentive']);
    echo "</table>";
}

// ════════════════════════════════════════════════════════════
// STEP 6 — SANITY CHECKS
// ════════════════════════════════════════════════════════════
echo "<h2>STEP 6 — Sanity Checks</h2><div class='box'>";
check("Visit avg is a positive number",              $visit_avg2 > 0);
check("Productivity denominator (counters) > 0",     $total_counters2 > 0);
check("Productivity % is between 0 and 100",         $productivity2 >= 0 && $productivity2 <= 100);
check("Business value saved in LAKHS (< 10000)",     $business_lakh2 < 10000);
check("Behaviour capped at 4",                       $behaviour2 <= 4);
check("Total score is between 0 and 220",            $total_score2 >= 0 && $total_score2 <= 220);
check("Achievement % is between 0 and 100",          $achievement2 >= 0 && $achievement2 <= 100);
check("All KRA points within allowed range",
    $visit_pts2 <= 2 && $prod_pts2 <= 2 && $mix_pts2 <= 2 && $biz_pts2 <= 2);

// Check no duplicate monthly_kra rows
$dupCount = $obj->getvalfield("monthly_kra","COUNT(*)","emp_id='$TEST_EMP_ID' AND month='$TEST_MONTH' AND year='$TEST_YEAR' AND companyid='$TEST_COMPANY'");
check("No duplicate monthly_kra rows (count=1 or 0)", $dupCount <= 1);

// Check incentive slab company filter works
$slabCheck = $obj->getvalfield("incentive_slabs","COUNT(*)","company_id='$TEST_COMPANY'");
check("incentive_slabs has rows for this company_id", $slabCheck > 0);

// Check kra_config has rows
$kraConfigCheck = $obj->getvalfield("kra_config","COUNT(*)","company_id='$TEST_COMPANY'");
check("kra_config has rows for this company_id", $kraConfigCheck > 0);

echo "</div>";

echo "<br><hr style='border-color:#334155'><p style='color:#475569'>Test complete. No data was modified.</p>";


// ════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ════════════════════════════════════════════════════════════
function row($label, $value) {
    echo "<div class='row'><span class='lbl'>$label</span><span class='val'>$value</span></div>";
}
function table($headers, $rows) {
    if (empty($rows)) { echo "<span class='warn'>— no rows —</span><br>"; return; }
    echo "<table><tr>";
    foreach ($headers as $h) echo "<th>$h</th>";
    echo "</tr>";
    foreach ($rows as $r) {
        echo "<tr>";
        foreach (array_values($r) as $v) echo "<td>$v</td>";
        echo "</tr>";
    }
    echo "</table>";
}
function kraRow($label, $value, $pts, $max, $weight) {
    $weighted = $pts * $weight;
    echo "<tr>
        <td>$label</td>
        <td>$value</td>
        <td>$pts / $max</td>
        <td>$max</td>
        <td>$weight%</td>
        <td><b>$weighted</b></td>
    </tr>";
}
function compareRow($field, $calculated, $saved) {
    $calc = round((float)$calculated, 4);
    $db   = round((float)$saved, 4);
    $match = abs($calc - $db) < 0.01;
    $cls  = $match ? 'pass' : 'fail';
    $icon = $match ? '✅' : '❌';
    echo "<tr class='$cls'><td>$field</td><td>$calc</td><td>$db</td><td>$icon</td></tr>";
}
function check($label, $passed) {
    $icon = $passed ? "<span class='ok'>✅ PASS</span>" : "<span class='err'>❌ FAIL</span>";
    echo "<div class='row'><span class='lbl'>$label</span><span class='val'>$icon</span></div>";
}