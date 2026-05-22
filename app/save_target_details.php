<?php
include("appsession.php");
if (isset($_POST['details_account_id'])) {
    $account_id = $_POST['details_account_id'];
    $month      = $_POST['month'];
    $year       = $_POST['year'];
    $brand_id   = $_POST['brand_id'];
    $target     = $_POST['target'];
    $target_id     = $_POST['target_id'];
    $arr2 = array(
        "target_id"   => $target_id,
        "account_id"   => $account_id,
        "brand_id"    => $brand_id,
        "target"      => $target,
        "month"       => $month,
        "year"        => $year,
        "createdby"   => $loginid,
        "ipaddress"   => $_SERVER['REMOTE_ADDR'],
        "createdate"  => date('Y-m-d H:i:s'),
    );
    $obj->insert_record("monthly_target_details", $arr2);
    echo 1;
    die;
}
if (isset($_POST['target_account_id'])) {

    $account_id   = $_POST['target_account_id'];
    $month        = $_POST['month'];
    $year         = $_POST['year'];
    $total_target = $_POST['total_target'];
    $target_id    = $_POST['target_id'];
    $comment      = $_POST['comment'];
    // print_r($_POST);
    $check = $obj->getvalfield(
        "monthly_target",
        "target_id",
        "account_id='$account_id' AND month='$month' AND year='$year' and createdby='$loginid'"
    );

    $arr2 = array(
        "account_id"   => $account_id,
        "total_target" => $total_target,
        "month"        => $month,
        "year"         => $year,
        "comment"      => $comment,
        "createdby"    => $loginid,
        "ipaddress"    => $_SERVER['REMOTE_ADDR'],
        "createdate"   => date('Y-m-d H:i:s'),
    );

    if ($check == '') {
        $lastid = $obj->insert_record_lastid("monthly_target", $arr2);
        $obj->update_record("monthly_target_details", ['target_id' => 0, 'month' => $month, 'year' => $year, 'account_id' => $account_id], ['target_id' => $lastid]);
        echo 1;
        die;
    } else {
        $obj->update_record(
            "monthly_target",
            ['target_id' => $check],
            [
                "total_target" => $total_target,
                "comment"  => $comment
            ]

        );
        echo 2;
        die;
    }
}
