<?php
include("../../adminsession.php");

$followup_id = $_POST['followup_id'];

$where = array(
    "followup_id" => $followup_id
);

if($obj->delete_record("quotation_followup", $where))
{
    echo 1;
}
else
{
    echo 0;
}