<?php
include("../adminsession.php");

$id = $_POST['id'];

$row = $obj->select_record("incentive_slabs", ["incentive_slabs_id" => $id]);
$type = $row['type'];

$count = $obj->getvalfield(
    "incentive_slabs",
    "COUNT(*)",
    "type='$type'"
);

if ($count <= 1) {
    echo "last_row";
    exit;
}

$next = $obj->executequery("SELECT * FROM incentive_slabs
    WHERE type='$type'
    AND min_value > {$row['min_value']}
    ORDER BY min_value ASC
    LIMIT 1
");

if (!empty($next) && $row['max_value'] !== NULL) {

    $obj->update_record(
        "incentive_slabs",
        ["incentive_slabs_id" => $next[0]['incentive_slabs_id']],
        ["min_value" => $row['min_value']]
    );
}


$obj->delete_record("incentive_slabs", ["incentive_slabs_id" => $id]);

echo "ok";
