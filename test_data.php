<?php
include("action.php");

$emp_id = 7;
$companyid = 1;

$start_date = '2026-04-01';

for ($i = 0; $i < 31; $i++) {

    $date = date('Y-m-d', strtotime("$start_date +$i days"));
    $day  = date('l', strtotime($date));

    $route = $obj->executequery("
        SELECT R.batch_no
        FROM route R
        JOIN route_plan RP ON R.batch_no = RP.batch_no
        WHERE RP.sales_executive_id = '$emp_id'
        AND R.companyid = '$companyid'
        AND FIND_IN_SET('$day', R.day_of_week)
        LIMIT 1
    ");

    if (empty($route)) continue;

    $batch_no = $route[0]['batch_no'];

    $accounts = $obj->executequery("
        SELECT account_id 
        FROM route_counter
        WHERE batch_no = '$batch_no'
        LIMIT 5
    ");

    $time = 10;

    foreach ($accounts as $acc) {

        $account_id = $acc['account_id'];

        $checkin = "$date $time:00:00";
        $checkout = "$date " . ($time + 1) . ":00:00";

        $obj->insert_record("daily_entries", [
            "account_id"   => $account_id,
            "createdby"    => $emp_id,
            "createdate"   => $date,
            "checkin_time" => $checkin,
            "checkout_time"=> $checkout,
            "latitude"     => "22.1",
            "longitude"    => "82.1",
            "latitude_out" => "22.1",
            "longitude_out"=> "82.1",
            "is_saved"     => 1,
            "companyid"    => $companyid
        ]);

        $time++;
    }
}
?>