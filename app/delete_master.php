<?php include("appsession.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];

if ($id > 0) {
	$where = array($tblpkey => $id);

	$obj->delete_record($tblname, $where);
}
