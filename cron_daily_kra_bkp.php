<?php
include("action.php");

/* DATE RANGE */
$start = new DateTime('2026-04-01');
$end   = new DateTime('2026-04-30');
$end->modify('+1 day'); // include last day

/* STEP 1: Get all sales users once */
$users = $obj->executequery("
    SELECT userid 
    FROM user 
    WHERE usertype='sales' AND status='1'
");

$userIds = array_column($users, 'userid');
if (empty($userIds)) return;

$userList = implode(",", array_map('intval', $userIds));

/* LOOP THROUGH EACH DAY */
for ($date = $start; $date < $end; $date->modify('+1 day')) {

    $currentDate = $date->format('Y-m-d');
    $day         = $date->format('l');

    echo "Processing: $currentDate <br>";

    /* STEP 2: Entry Data */
    $entryData = $obj->executequery("
        SELECT 
            createdby AS emp_id,
            COUNT(*) AS visit_count,
            COUNT(DISTINCT account_id) AS active_counters
        FROM daily_entries
        WHERE DATE(createdate)='$currentDate' 
            AND is_saved='1'
            AND createdby IN ($userList)
        GROUP BY createdby
    ");

    $entryMap = [];
    foreach ($entryData as $row) {
        $entryMap[$row['emp_id']] = $row;
    }

    /* STEP 3: Route Data */
    $routeData = $obj->executequery("
        SELECT 
            rp.sales_executive_id AS emp_id,
            COUNT(DISTINCT rc.account_id) AS total_counters
        FROM route_counter rc
        JOIN route r ON rc.batch_no = r.batch_no
        JOIN route_plan rp ON rp.batch_no = r.batch_no
        WHERE FIND_IN_SET('$day', r.day_of_week)
            AND rp.sales_executive_id IN ($userList)
        GROUP BY rp.sales_executive_id
    ");

    $routeMap = [];
    foreach ($routeData as $row) {
        $routeMap[$row['emp_id']] = $row['total_counters'];
    }

    /* STEP 4: Insert */
    foreach ($userIds as $emp) {

        $exists = $obj->getvalfield(
            "daily_productivity",
            "COUNT(*)",
            "emp_id='$emp' AND date='$currentDate'"
        );

        if ($exists > 0) continue;

        $visit  = $entryMap[$emp]['visit_count'] ?? 0;
        $active = $entryMap[$emp]['active_counters'] ?? 0;
        $total  = $routeMap[$emp] ?? 0;

        if ($visit > 0) {
            $obj->insert_record("daily_productivity", [
                "emp_id" => $emp,
                "date" => $currentDate,
                "visit_count" => $visit,
                "active_counters" => $active,
                "total_counters" => $total,
                "company_id" => 1
            ]);
        }
    }
}