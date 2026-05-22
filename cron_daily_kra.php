<?php
include("action.php");

$yesterday = date('Y-m-d', strtotime('-1 day'));
$users = $obj->executequery("
    SELECT userid 
    FROM user 
    WHERE usertype='sales' AND status='1'
");

foreach ($users as $u) {
    $emp = $u['userid'];

    $exists = $obj->getvalfield(
        "daily_productivity",
        "COUNT(*)",
        "emp_id='$emp' AND date='$yesterday'"
    );

    if ($exists > 0) continue;

    $visit = $obj->getvalfield(
        "daily_entries",
        "COUNT(*)",
        "createdby='$emp' AND DATE(createdate)='$yesterday' AND is_saved='1'"
    );

    $active = $obj->getvalfield(
        "daily_entries",
        "COUNT(DISTINCT account_id)",
        "createdby='$emp' AND DATE(createdate)='$yesterday' AND is_saved='1'"
    );

    $day = date('l', strtotime($yesterday));

    $total = $obj->getvalfield(
        "route_counter rc 
         JOIN route r ON rc.batch_no = r.batch_no
         JOIN route_plan rp ON rp.batch_no = r.batch_no",
        "COUNT(DISTINCT rc.account_id)",
        "rp.sales_executive_id='$emp'
         AND FIND_IN_SET('$day', r.day_of_week)"
    );

    if ($visit > 0) {
        $obj->insert_record("daily_productivity", [
            "emp_id" => $emp,
            "date" => $yesterday,
            "visit_count" => $visit,
            "active_counters" => $active,
            "total_counters" => $total,
            "company_id" => 1
        ]);
    }
}