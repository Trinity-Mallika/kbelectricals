<?php
include("../../adminsession.php");

$common_id = $obj->test_input($_POST['common_id']);

$html = "<option value=''>Select a Referred By</option>";

$crit = ($common_id == 7) ? " AND usertype='sales'" : " AND usertype!='sales'";

$sql = $obj->executequery("SELECT userid, fullname, usertype  FROM user  WHERE status='1' $crit  ORDER BY userid ASC");

foreach ($sql as $key) {

    $usertype = strtolower($key['usertype']);

    $html .= "<option value='{$key['userid']}' data-type='{$usertype}'>
                {$key['fullname']}
              </option>";
}

echo $html;
