<?php
include("../adminsession.php");

if (isset($_POST['account_id'])) {

    $account_id = (int)$_POST['account_id'];
    $approved_by = $_SESSION['userid'];
    $approved_date = date('Y-m-d H:i:s');

    $update = [
        "status1" => 1,
        "approved_by" => $approved_by,
        "approved_date" => $approved_date
    ];

    $where = ["account_id" => $account_id];

    $obj->update_record("account", $where, $update);
    $obj->update_record("route_counter", $where, ["is_active" => 1]);

    echo "success";
}
