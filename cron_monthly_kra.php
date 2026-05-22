<?php
include("action.php");

$month = date('m', strtotime('-1 month'));
$year  = date('Y');

$users = $obj->executequery("
    SELECT userid 
    FROM user 
    WHERE usertype='sales' AND status='1'
");

foreach ($users as $u) {

    $emp = $u['userid'];

    $obj->processMonthlyKRA($emp, $month, $year);
    $obj->processMonthlyIncentive($emp, $month, $year);
}
?>