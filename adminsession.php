<?php
include("action.php");

$timeout = 86400;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();

    header("Location: ../index.php?msg=session_expired");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

if (
    !isset($_SESSION['userid']) ||
    empty($_SESSION['userid']) ||
    !isset($_SESSION['usertype']) ||
    empty($_SESSION['usertype'])
) {
    header("Location: ../index.php?msg=invalid");
    exit;
}

if (!in_array($_SESSION['usertype'], ['admin', 'user'])) {
    header("Location: ../index.php?msg=invalid");
    exit;
}

$ipaddress = $obj->get_client_ip();
$loginid   = $_SESSION['userid'];
$usertype  = $_SESSION['usertype'];
$companyid = $_SESSION['companyid'] ?? 0;
$createdate = date('Y-m-d H:i:s');
$sessionid = $obj->getvalfield("m_session", "sessionid", "status=1");
$_SESSION['sessionid'] = $sessionid;

?>