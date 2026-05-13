<?php
include("appsession.php");
$id  = $_REQUEST['id'];
$tblname  = $_REQUEST['tblname'];
$tblpkey  = $_REQUEST['tblpkey'];
if ($id > 0) {
    $obj->delete_record('monthly_target_details', [$tblpkey => $id]);
    $obj->delete_record($tblname, [$tblpkey => $id]);
}




echo 1;
