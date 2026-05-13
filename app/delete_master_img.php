<?php include("appsession.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];
$imgname = $_REQUEST['imgname'];
$imgpath = $_REQUEST['imgpath'];
$where = array($tblpkey => $id);

if ($imgname != "") {
	@unlink("../$imgpath" . $imgname);
}
$res = $obj->delete_record($tblname, $where);

echo 1;
