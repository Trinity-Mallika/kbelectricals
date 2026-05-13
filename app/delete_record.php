<?php
include("appsession.php");

if (isset($_POST['id'], $_POST['tblname'], $_POST['tblpkey'])) {

    $id      = (int)$_POST['id'];
    $tblname = $obj->test_input($_POST['tblname']);
    $tblpkey = $obj->test_input($_POST['tblpkey']);

    $where = [$tblpkey => $id];

    $obj->delete_record($tblname, $where);

    echo 1;
}
?>