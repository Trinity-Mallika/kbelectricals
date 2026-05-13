<?php include("appsession.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];

if ($id > 0) {
	$where = array($tblpkey => $id, "createdby" => $loginid);
	$obj->delete_record("route_counter", $where);
	$obj->delete_record($tblname, $where);
}
