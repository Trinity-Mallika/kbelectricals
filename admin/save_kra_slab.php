<?php
include("../adminsession.php");

$obj->insert_record("kra_config", [
    "kra_key" => $_POST['kra_key'],
    "min_value" => $_POST['min_value'],
    "max_value" => $_POST['max_value'] ?: NULL,
    "points" => $_POST['points'],
    "companyid" => $_SESSION['companyid']
]);