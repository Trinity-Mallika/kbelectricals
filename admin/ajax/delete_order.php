<?php include("../../action.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];
$parent_transaction_id  = $_REQUEST['parent_transaction_id'];

if ($id > 0) {
    $where = array($tblpkey => $id);
    $obj->delete_record('dispatch_history', $where);
    $obj->delete_record('transaction_details', $where);
    $obj->delete_record($tblname, $where);
    if ($parent_transaction_id > 0) {
        $where = array($tblpkey => $parent_transaction_id);
        $obj->update_record($tblname, $where, ['conversion_status' => 0]);
    }
}
