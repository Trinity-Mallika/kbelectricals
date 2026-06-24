<?php

include('../action.php');
// if (!isset($_SESSION['userid'])) {
//     echo "<script>location='index.php';</script>";
//     exit;
// }

// $userid = $_SESSION['userid'];


if (
    !isset($_SESSION['userid']) ||
    !isset($_SESSION['usertype']) ||
    $_SESSION['usertype'] != 'employee'
) {

    session_unset();
    session_destroy();

    echo "<script>location='index.php';</script>";
    exit;
}

$userid = $_SESSION['userid'];
