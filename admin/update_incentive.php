<?php
include("../adminsession.php");

$id    = $_POST['id'];
$field = $_POST['field'];
$value = $_POST['value'];

$value = ($value === '' || $value === null) ? NULL : $value;

$row  = $obj->select_record("incentive_slabs", ["incentive_slabs_id"=>$id]);
$type = $row['type'];

if ($field == 'max' && $value === NULL) {

    $inf = $obj->getvalfield(
        "incentive_slabs",
        "COUNT(*)",
        "type='$type'
         AND incentive_slabs_id!='$id'
         AND max_value IS NULL"
    );

    if ($inf > 0) {
        echo "infinity_exists";
        exit;
    }
}

if ($field == 'max' && $value !== NULL) {

    if ($value <= $row['min_value']) {
        echo "invalid_range";
        exit;
    }
}

if ($value !== NULL && ($field == 'min' || $field == 'max')) {

    if ($field == 'min') {

        $exists = $obj->getvalfield(
            "incentive_slabs",
            "COUNT(*)",
            "type='$type'
             AND incentive_slabs_id!='$id'
             AND (
                ($value BETWEEN min_value AND IFNULL(max_value,999999))
             )"
        );

    } else {

        $exists = $obj->getvalfield(
            "incentive_slabs",
            "COUNT(*)",
            "type='$type'
             AND incentive_slabs_id!='$id'
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

$obj->update_record("incentive_slabs", ["incentive_slabs_id"=>$id], [
    $field => $value
]);

echo "ok";