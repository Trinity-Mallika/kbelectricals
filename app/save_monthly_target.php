<?php
include("appsession.php");

$account_id = $_POST['account_id'];
$month      = $_POST['month'];
$year       = $_POST['year'];
$comment    = $_POST['comment'];

$targets = json_decode($_POST['targets'], true);



/* TOTAL TARGET */
$total_target = 0;

foreach ($targets as $t) {
    $total_target += $t['target'];
}



/* SAVE MAIN TABLE */
$arr = array(

    "account_id"   => $account_id,
    "target"       => $total_target,
    "month"        => $month,
    "year"         => $year,
    "comment"      => $comment,
    "createdby"    => $loginid,
    "ipaddress"    => $ipaddress,
    "createdate"   => date('Y-m-d H:i:s'),
    "lastupdated"  => date('Y-m-d'),
    "companyid"    => $companyid,
    "sessionid"    => $sessionid

);

$target_id = $obj->insert_record("monthly_target", $arr);




/* SAVE DETAILS */
foreach ($targets as $row) {
    $arr2 = array(

        "target_id"   => $target_id,
        "brand_id"    => $row['brand_id'],
        "target"      => $row['target'],
        "month"       => $month,
        "year"        => $year,
        "createdby"   => $loginid,
        "ipaddress"   => $ipaddress,
        "createdate"  => date('Y-m-d H:i:s'),
        "lastupdated" => date('Y-m-d')

    );

    $obj->insert_record("monthly_target_details", $arr2);
}

echo 1;
