<?php
include("../adminsession.php");

$account_id = $_POST['account_id'] ?? '';

echo '<option value="">Select Account</option>';

$res = $obj->executequery("
    SELECT account_id, account_name
    FROM account
    WHERE status1='1'
    ORDER BY account_name ASC
");

foreach($res as $row){

    $selected = ($row['account_id'] == $account_id) ? 'selected' : '';

    echo '<option value="'.$row['account_id'].'" '.$selected.'>'
            .$row['account_name'].
         '</option>';
}
?>