<?php
include("appsession.php");

$visit_id = $_POST['visit_id'];

if (!$visit_id) {
    echo "error";
    exit;
}
$obj->delete_record("daily_entries", [
    "entry_id" => $visit_id
]);


echo "success";
