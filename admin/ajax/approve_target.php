<?php
include("../../adminsession.php");

$createdby = $_REQUEST['createdby'];
$month     = $_REQUEST['month'];
$year      = $_REQUEST['year'];
$status    = $_REQUEST['status'];
$remark    = isset($_REQUEST['remark']) ? $obj->test_input($_REQUEST['remark']) : '';

$approval_id = $obj->getvalfield(
	"monthly_target_approval",
	"approval_id",
	"userid='$createdby'
    and month='$month'
    and year='$year'"
);

$form_data = array(
	"status"        => $status,
	"remark"        => $remark,
	"approved_by"   => $loginid,
	"approved_date" => $createdate
);

if ($approval_id > 0) {

	$where = array(
		"approval_id" => $approval_id
	);

	$obj->update_record(
		"monthly_target_approval",
		$where,
		$form_data
	);
} else {

	$form_data["userid"]   = $createdby;
	$form_data["createdby"]   = $loginid;
	$form_data["month"]       = $month;
	$form_data["year"]        = $year;
	$form_data["companyid"]   = $companyid;
	$form_data["ipaddress"]   = $ipaddress;
	$form_data["createdate"]  = $createdate;

	$obj->insert_record(
		"monthly_target_approval",
		$form_data
	);
}

echo 1;
