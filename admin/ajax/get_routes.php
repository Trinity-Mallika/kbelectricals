<?php
include("../../adminsession.php");

$user_id = $obj->test_input($_POST['user_id']);

$html = "<option value=''>Select a Route</option>";

$sql = $obj->executequery("
    SELECT
        R.batch_no,
        R.route_name,
        GROUP_CONCAT(
            R.day_of_week
            ORDER BY FIELD(
                R.day_of_week,
                'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
            )
            SEPARATOR ', '
        ) AS days
    FROM route AS R
    LEFT JOIN route_plan AS RP ON R.batch_no = RP.batch_no
    WHERE R.companyid='$companyid'
    AND RP.sales_executive_id='$user_id'
    GROUP BY R.batch_no, R.route_name
    ORDER BY R.route_name ASC
");

foreach ($sql as $key) {

    $html .= "<option value='{$key['batch_no']}'>
                {$key['route_name']} [{$key['days']}]
              </option>";
}

echo $html;