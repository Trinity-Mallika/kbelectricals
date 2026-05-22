<?php include("../action.php");

if (isset($_SESSION['salesuserid']) && $_SESSION['salesuserid'] != "" && $_SESSION['usertype'] == "sales") {
    $ipaddress = $obj->get_client_ip();
    $loginid = $_SESSION['salesuserid'];
    $usertype = $_SESSION['usertype'];
    $companyid = $_SESSION['companyid'];
    $createdate = date('Y-m-d H:i:s');
    $sessionid = $obj->getvalfield("m_session", "sessionid", "status=1");
    $_SESSION['sessionid'] = $sessionid;
} else {
    echo "<script>location='index.php?msg=invalid'</script>";
    die;
}
