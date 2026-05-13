<?php
include("action.php");

if (isset($_POST['login'])) {

	$username  = trim($_POST['admin_name']);
	$password  = trim($_POST['admin_pwd']);
	$createdate = date('Y-m-d');

	if ($username === "" || $password === "") {
		echo "<script>location='index.php?msg=blank'</script>";
		exit;
	}

	$user = $obj->login_method("user", $username, $password);

	if ($user) {
		$_SESSION['userid']   = $user['userid'];
		$_SESSION['usertype'] = $user['usertype'];
		echo "<script>location='admin/dashboard.php'</script>";
		exit;
	}

	echo "<script>location='index.php?msg=error'</script>";
}
