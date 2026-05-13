<?php include("../../action.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];

if ($id > 0) {
    $where = array($tblpkey => $id);

    $obj->delete_record('transaction_details', $where);
    $obj->delete_record($tblname, $where);
}
