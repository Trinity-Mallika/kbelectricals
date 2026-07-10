<?php
include("action.php");

$month = date('m', strtotime('-1 month'));
$year  = date('Y');

$users = $obj->executequery("SELECT userid,companyid 
    FROM user 
    WHERE usertype='sales' AND status='1'");

foreach ($users as $u) {

    $emp = $u['userid'];
    $companyid = $u['companyid'];

    $obj->processMonthlyKRA($emp, $month, $year, $companyid);
    $obj->processMonthlyIncentive($emp, $month, $year, $companyid);
}
