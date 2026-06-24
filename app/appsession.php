<?php
include("../action.php");

$timeout = 86400; // 24 hours

if (
    isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout
) {
    session_unset();
    session_destroy();

    echo "<script>location='index.php?msg=session_expired'</script>";
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

if (
    isset($_SESSION['salesuserid']) &&
    $_SESSION['salesuserid'] != "" &&
    isset($_SESSION['usertype']) &&
    $_SESSION['usertype'] == "sales"
) {

    $ipaddress = $obj->get_client_ip();
    $loginid   = $_SESSION['salesuserid'];
    $usertype  = $_SESSION['usertype'];
    $createdate = date('Y-m-d H:i:s');

    $_SESSION['sessionid'] = $obj->getvalfield(
        "m_session",
        "sessionid",
        "status=1"
    );

    $_SESSION['companyid'] = $obj->getvalfield(
        "user",
        "companyid",
        "userid='$loginid'"
    );

    $companyid = $_SESSION['companyid'];
    $sessionid = $_SESSION['sessionid'];

} else {

    echo "<script>location='index.php?msg=invalid'</script>";
    exit;
}