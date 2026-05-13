<?php include("../adminsession.php");

$company_id = isset($_POST['company_id']) ? (int)$obj->test_input($_POST['company_id']) : 0;

if ($company_id > 0) {
    $_SESSION['companyid'] = $company_id;
}
