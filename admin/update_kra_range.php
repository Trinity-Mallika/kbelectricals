<?php
include("../adminsession.php");

$id    = $_POST['id'];
$field = $_POST['field'];
$value = $_POST['value'];
$value = ($value === '' || $value === null) ? NULL : $value;
$row = $obj->select_record("kra_config", ["kra_config_id"=>$id]);
$kra = $row['kra_key'];

if ($field == 'max' && $value === NULL) {
    $infExists = $obj->getvalfield(
        "kra_config",
        "COUNT(*)",
        "kra_key='$kra'
         AND kra_config_id!='$id'
         AND max_value IS NULL"
    );

    if ($infExists > 0) {
        echo "infinity_exists";
        exit;
    }
}

if ($value !== NULL) {

    if ($field == 'min') {

        $exists = $obj->getvalfield(
            "kra_config",
            "COUNT(*)",
            "kra_key='$kra'
             AND kra_config_id!='$id'
             AND (
                ($value BETWEEN min_value AND IFNULL(max_value, 999999))
             )"
        );

    } else if ($field == 'max') {

        $exists = $obj->getvalfield(
            "kra_config",
            "COUNT(*)",
            "kra_key='$kra'
             AND kra_config_id!='$id'
             AND (
                (min_value BETWEEN {$row['min_value']} AND $value)
             )"
        );

    }

    if ($exists > 0) {
        echo "overlap";
        exit;
    }
}

if ($field == 'min') {

    $obj->update_record("kra_config", ["kra_config_id"=>$id], [
        "min_value"=>$value
    ]);
}

if ($field == 'max') {

    $obj->update_record("kra_config", ["kra_config_id"=>$id], [
        "max_value"=>$value
    ]);
}

echo "ok";