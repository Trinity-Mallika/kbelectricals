<?php
include("action.php");

$data = $obj->executequery("
    SELECT a.account_id, a.class, SUM(t.amount) as sales
    FROM account a
    LEFT JOIN transaction_entry t 
        ON t.account_id=a.account_id
        AND t.type='order'
        AND t.date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY a.account_id
");

foreach ($data as $d) {

    $sales = $d['sales'];
    $class = $d['class'];

    $active = 0;

    if ($class == 'A' && $sales >= 150000) $active = 1;
    elseif ($class == 'B' && $sales >= 75000) $active = 1;
    elseif ($class == 'C' && $sales >= 36000) $active = 1;

    $obj->executequery("
        UPDATE account 
        SET is_active='$active'
        WHERE account_id='{$d['account_id']}'
    ");
}
?>