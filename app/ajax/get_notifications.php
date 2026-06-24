<?php

include("../../action.php");

$companyid = $obj->test_input($_POST['companyid'] ?? 0);
$loginid   = $obj->test_input($_POST['loginid'] ?? 0);

$data = [];

$sql = $obj->executequery("
    SELECT 
        d.entry_id,
        d.follow_up_date,
        d.remarks,
        a.account_name
    FROM daily_entries d
    LEFT JOIN account a ON a.account_id = d.account_id
    WHERE d.companyid = '$companyid'
      AND d.createdby = '$loginid'
      AND DATE(d.follow_up_date) = CURDATE()
    ORDER BY d.follow_up_date ASC
");

foreach ($sql as $row) {

    $data[] = [
        'id'      => $row['entry_id'],
        'title'   => $row['account_name'],
        'message' => 'Today',
        'remark'  => $row['remarks']
    ];
}

echo json_encode([
    'count' => count($data),
    'data'  => $data
]);