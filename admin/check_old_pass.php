<?php
include("../adminsession.php");
$oldpass = $_SERVER['QUERY_STRING'];
$loginid = $_SESSION['userid'];
// $sql =mysql_query("select password from user where password = '$oldpass' and userid ='$loginid'");
//echo "select password from user where password = '$oldpass' and userid ='$loginid'";die;
$where = array('password' => $oldpass, 'userid' => $loginid);
$cnt = $obj->count_method("user", $where);
//$cnt = mysql_num_rows($sql);
//echo $sql;
$idname = "";

if ($cnt != 0)
   $idname = "<span style='color:green'>Old passsword is correct</span>";
else
   $idname = "<span style='color:red'>Old password is wrong</span>";

echo $idname;
