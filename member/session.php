<?php include('../action.php');
if (!isset($_SESSION['member_id'])) {
    echo "<script>location='index.php'</script>";
    exit;
}
$member_id = $_SESSION['member_id'];
