<?php
include("../adminsession.php");

$account_name = $obj->test_input($_POST['account_name']);
$mobile_no    = $obj->test_input($_POST['mobile_no']);
$owner_name   = $obj->test_input($_POST['owner_name']);
$o_mobile_no  = $obj->test_input($_POST['o_mobile_no']);
$common_id    = $obj->test_input($_POST['common_id']);
$area_name    = $obj->test_input($_POST['area_name']);
$area_id      = $obj->test_input($_POST['area_id']);
$batch_no     = isset($_POST['batch_no']) ? $obj->test_input($_POST['batch_no']) : '';
$user_id      = isset($_POST['user_id']) ? $obj->test_input($_POST['user_id']) : '';
$electrician_name      = isset($_POST['electrician_name']) ? $obj->test_input($_POST['electrician_name']) : '';
$electrician_mobile      = isset($_POST['electrician_mobile']) ? $obj->test_input($_POST['electrician_mobile']) : '';
$account_id_map      = isset($_POST['account_id_map']) ? $obj->test_input($_POST['account_id_map']) : 0;
$force_save = isset($_POST['force_save']) ? (int)$_POST['force_save'] : 0;

if (empty($area_id) && !empty($area_name)) {

    $chk_area_id = $obj->getvalfield(
        "area_master",
        "area_id",
        "area_name='$area_name'"
    );

    if (!empty($chk_area_id)) {

        $area_id = $chk_area_id;
    } else {

        $area_id = $obj->insert_record_lastid("area_master", array(
            "area_name"  => $area_name,
            "createdby"  => $loginid,
            "companyid"  => $companyid,
            "sessionid"  => $sessionid,
            "createdate" => $createdate
        ));
    }
}

if ($common_id != '6') {
    $duplicate_id = $obj->getvalfield(
        "account",
        "account_id",
        "mobile_no='$mobile_no' AND type='customer'"
    );

    $count_name = $obj->getvalfield(
        "account",
        "count(*)",
        "account_name='$account_name' AND type='customer'"
    );

    // Same mobile = hard stop
    if (!empty($duplicate_id)) {
        echo "duplicate";
        exit;
    }

    // Same name = ask Yes/No
    if ($count_name > 0 && $force_save == 0) {
        echo "duplicate_name";
        exit;
    }

    $insert_data = array(
        "account_name" => $account_name,
        "mobile_no"    => $mobile_no,
        "owner_name"   => $owner_name,
        "o_mobile_no"  => $o_mobile_no,
        "common_id"    => $common_id,
        "area_id"      => $area_id,
        "type"         => "customer",
        "status"       => "active",
        "userid"       => $user_id,
        "status1"      => 1,
        "ipaddress"    => $ipaddress,
        "createdby"    => $loginid,
        "companyid"    => $companyid,
        "sessionid"    => $sessionid,
        "createdate"   => $createdate
    );

    $last_id = $obj->insert_record_lastid("account", $insert_data);


    if (!empty($batch_no)) {

        $sequence = $obj->getvalfield(
            "route_counter",
            "IFNULL(MAX(sequence),0)+1",
            "batch_no='$batch_no'"
        );

        $obj->insert_record("route_counter", [
            'batch_no'   => $batch_no,
            'account_id' => $last_id,
            'sequence'   => $sequence,
            'is_active'  => 1,
            'createdate' => $createdate,
            'ipaddress'  => $ipaddress,
            'companyid'  => $companyid,
            'createdby'  => $loginid
        ]);
    }
} else {

    $duplicate_id = $obj->getvalfield(
        "account",
        "account_id",
        "mobile_no='$electrician_mobile'
         AND type='electrician'
         AND account_id_map='$account_id_map'"
    );

    $count_name = $obj->getvalfield(
        "account",
        "count(*)",
        "account_name='$electrician_name'
         AND type='electrician'
         AND account_id_map='$account_id_map'"
    );

    if (!empty($duplicate_id)) {
        echo "duplicate";
        exit;
    }

    if ($count_name > 0 && $force_save == 0) {
        echo "duplicate_name";
        exit;
    }

    $insert_data = array(
        "account_name"   => $electrician_name,
        "mobile_no"      => $electrician_mobile,
        "common_id"      => $common_id,
        "type"           => "electrician",
        "status"         => "active",
        "userid"         => $user_id,
        "account_id_map" => $account_id_map,
        "status1"        => 1,
        "ipaddress"      => $ipaddress,
        "createdby"      => $loginid,
        "companyid"      => $companyid,
        "sessionid"      => $sessionid,
        "createdate"     => $createdate
    );

    $last_id = $obj->insert_record_lastid("account", $insert_data);
}

echo $last_id;
