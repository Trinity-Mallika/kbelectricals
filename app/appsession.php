<?php include("../action.php");

if (isset($_SESSION['salesuserid']) && $_SESSION['salesuserid'] != "" && $_SESSION['usertype'] == "sales") {
    $ipaddress = $obj->get_client_ip();
    $loginid = $_SESSION['salesuserid'];
    $usertype = $_SESSION['usertype'];
    $createdate = date('Y-m-d H:i:s');
    $_SESSION['sessionid'] = $obj->getvalfield("m_session", "sessionid", "status=1");
    $_SESSION['companyid'] = $obj->getvalfield("user", "companyid", "userid='$loginid'");
    $companyid = $_SESSION['companyid'];
    $sessionid = $_SESSION['sessionid'];
} else {
    echo "<script>location='index.php?msg=invalid'</script>";
    die;
}
