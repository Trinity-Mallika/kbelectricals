<?php
include("../adminsession.php");

$account_name = $obj->test_input($_POST['account_name']);
$mobile_no    = $obj->test_input($_POST['mobile_no']);
$owner_name   = $obj->test_input($_POST['owner_name']);
$o_mobile_no  = $obj->test_input($_POST['o_mobile_no']);
$common_id    = $obj->test_input($_POST['common_id']);
$area_id      = $obj->test_input($_POST['area_id']);

$last_id = $obj->insert_record_lastid("account", array(
    "account_name" => $account_name,
    "mobile_no"    => $mobile_no,
    "owner_name"   => $owner_name,
    "o_mobile_no"  => $o_mobile_no,
    "common_id"    => $common_id,
    "area_id"      => $area_id,
    "status"      => "active",
    "status1"       => 1,
    "ipaddress" => $ipaddress,
    "createdby" => $loginid,
    "companyid" => $companyid,
    "sessionid" => $sessionid,
    "createdate" => $createdate,
));

echo $last_id;
