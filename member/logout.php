<?php
include('../action.php');

// Only clear employee session keys — don't destroy the entire session
// because the admin portal uses the same PHP session.
unset($_SESSION['member_id']);
unset($_SESSION['member_name']);
unset($_SESSION['attendance_coordinator']);
unset($_SESSION['chapter_id']);
unset($_SESSION['shop_id']);

echo "<script>location='index.php'</script>";
?>
