<?php
include("../adminsession.php");

$filename = "account_list_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Sr No',
    'Account ID',
    'Account Name',
    'Area Name'
]);

$sql = "
SELECT
    a.account_id,
    a.account_name,
    am.area_name

FROM account a

LEFT JOIN area_master am
    ON am.area_id = a.area_id

ORDER BY
    am.area_name,
    a.account_name
";

$res = $obj->executequery($sql);

$sr = 1;

foreach ($res as $row) {

    fputcsv($output, [
        $sr++,
        $row['account_id'],
        htmlspecialchars_decode($row['account_name']),
        htmlspecialchars_decode($row['area_name'])
    ]);
}

fclose($output);
exit;