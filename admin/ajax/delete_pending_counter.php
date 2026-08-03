<?php include("../../adminsession.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];

if ($id > 0) {
	$where = array($tblpkey => $id);
	$obj->delete_record("route_counter", array($tblpkey => $id, "is_active" => 0));
	$obj->delete_record($tblname, $where);
}
