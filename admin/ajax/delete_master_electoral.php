<?php include("../../adminsession.php");

$id  = $_REQUEST['id'];

if ($id != '') {

    $where = array("division_number" => $id);

    $obj->delete_record("electoral_data", $where);
}
