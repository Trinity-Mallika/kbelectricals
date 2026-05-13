<?php
include("../adminsession.php");
session_destroy();
echo "<script>location='../index.php?msg=logout' </script>" ;

?>