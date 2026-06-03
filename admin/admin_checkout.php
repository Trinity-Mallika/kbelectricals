<?php
include("../adminsession.php");

$entry_id = $_POST['entry_id'];
$time = date('H:i:s');
if ($entry_id > 0) {
    $obj->update_record("daily_entries", ["entry_id" => $entry_id], ["is_saved" => 1, "checkout_time" => $time]);
    echo 1;
    exit();
}
echo 2;
exit();
