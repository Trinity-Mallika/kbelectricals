<?php
include("../adminsession.php");

$id = $_POST['id'];

$row = $obj->select_record("kra_config", ["kra_config_id" => $id]);
$kra = $row['kra_key'];

$count = $obj->getvalfield(
    "kra_config",
    "COUNT(*)",
    "kra_key='$kra'"
);

if ($count <= 1) {
    echo "last_row";
    exit;
}

$next = $obj->executequery("
    SELECT * FROM kra_config
    WHERE kra_key='$kra'
    AND min_value > {$row['min_value']}
    ORDER BY min_value ASC
    LIMIT 1
");

if (!empty($next) && $row['max_value'] !== NULL) {

    $obj->update_record(
        "kra_config",
        ["kra_config_id" => $next[0]['kra_config_id']],
        ["min_value" => $row['min_value']]
    );
}

$obj->delete_record("kra_config", ["kra_config_id" => $id]);

echo "ok";
