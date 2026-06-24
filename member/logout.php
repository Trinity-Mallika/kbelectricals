<?php session_start();
unset($_SESSION['member_id']);
unset($_SESSION['member_name']);
echo "<script>location='index.php'</script>";
