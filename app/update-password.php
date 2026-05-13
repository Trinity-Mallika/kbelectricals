<?php include("appsession.php");

$old = $_POST['old_password'];
$new = $_POST['new_password'];
$confirm = $_POST['confirm_password'];

if ($new != $confirm) {
    echo "notmatch";
    exit;
}

// check old password
$dbpass = $obj->getvalfield("user", "password", "userid='$loginid'");

if ($dbpass != $old) {
    echo "wrong";
    exit;
}

try {
    $obj->update_record("user", ["userid" => $loginid], ["password" => $confirm]);

    echo "success";
} catch (Exception $e) {
    echo "error";
}
