<?php include("../../adminsession.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];
$module = $_REQUEST['module'];
$submodule = $_REQUEST['submodule'];
$imgname = $_REQUEST['imgname'];
$pagename = $_REQUEST['pagename'];
$imgpath = $_REQUEST['imgpath'];
$where = array($tblpkey => $id);

if ($imgname != "") {
    @unlink("../$imgpath" . $imgname);
}
$res = $obj->delete_record($tblname, $where);


if ($res > 0) {

    echo "<script>location='$pagename?action=3';</script>";
}
