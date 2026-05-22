<?php include("action.php");

if (isset($_SESSION['usertype']) && $_SESSION['usertype'] != "" && isset($_SESSION['userid']) && $_SESSION['userid'] != "") {

	$ipaddress = $obj->get_client_ip();
	$loginid = $_SESSION['userid'];
	$usertype = $_SESSION['usertype'];
	$sessionid = $obj->getvalfield("m_session", "sessionid", "status=1");
	$_SESSION['sessionid'] = $sessionid;
	$companyid = isset($_SESSION['companyid']) ? $_SESSION['companyid'] : 0;
	$createdate = date('Y-m-d H:i:s');
} else {
	echo "<script>location='../index.php?msg=invalid'</script>";
	die;
}
