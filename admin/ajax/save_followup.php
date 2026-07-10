<?php
include("../../adminsession.php");

$form_data = array(

    "transaction_id" => $_POST['transaction_id'],

    "follow_date" => $_POST['follow_date'],
    "type" => $_POST['type'],
    "remark" => $_POST['remark'],

    "createdate" => $createdate,

    "createdby" => $loginid

);

$obj->insert_record("quotation_followup", $form_data);

echo 1;
