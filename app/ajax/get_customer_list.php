<?php include("../../action.php");
header('Content-Type: application/json');

$loginid = $_GET['loginid'] ?? '';
$companyid = $_GET['companyid'] ?? '';
$category   = $_GET['category'] ?? '';
$month      = intval($_GET['month'] ?? date('n'));
$year       = intval($_GET['year']  ?? date('Y'));
$monthStart = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
$monthEnd   = date('Y-m-t',  mktime(0, 0, 0, $month, 1, $year));
$uid        = intval($loginid);
$cid        = $companyid;

$brandRows = $obj->executequery(
    "SELECT cat_id AS id, cat_name AS name
     FROM category_master
     WHERE type = 'brand'
     ORDER BY cat_name"
);

if (empty($brandRows)) {
    echo json_encode(['brands' => [], 'rows' => []]);
    exit;
}

$brandIds   = array_column($brandRows, 'id');
$brandNames = array_column($brandRows, 'name', 'id');
$brandCols = '';
foreach ($brandIds as $bid) {
    $bid = intval($bid);
    $brandCols .= "
        SUM(CASE WHEN td.brand_id = $bid THEN td.net_amt ELSE 0 END) AS brand_$bid,";
}
$brandCols = rtrim($brandCols, ',');

$pivotBase = "SELECT
        a.account_id,
        a.account_name,
        r.route_id,
        r.batch_no,
        r.route_name,
        $brandCols,
        SUM(td.net_amt) AS row_total,
        COUNT(DISTINCT te.transaction_id) AS invoice_count
    FROM account a
    INNER JOIN route_counter rc
        ON rc.account_id = a.account_id AND rc.is_active = 1
    INNER JOIN route_plan rp
        ON rp.batch_no = rc.batch_no AND rp.sales_executive_id = $uid
    INNER JOIN route r
        ON r.batch_no = rp.batch_no
    INNER JOIN transaction_entry te
        ON te.account_id = a.account_id
       AND te.type = 'order'
       AND te.is_approved = 1
       AND te.companyid = '$cid'
       AND te.billdate BETWEEN '$monthStart' AND '$monthEnd'
    INNER JOIN transaction_details td
        ON td.transaction_id = te.transaction_id
       AND td.type = 'order'
    WHERE a.type = 'customer'
    GROUP BY a.account_id, a.account_name, r.batch_no, r.route_name";

try {
    switch ($category) {

        case 'total':
            $customers = $obj->executequery("SELECT DISTINCT
                    a.account_id,
                    a.account_name,
                    a.status1
                FROM account a
                INNER JOIN route_counter rc
                    ON rc.account_id = a.account_id AND rc.is_active = 1
                INNER JOIN route_plan rp
                    ON rp.batch_no = rc.batch_no
                   AND rp.sales_executive_id = $uid
                WHERE a.type = 'customer'
                ORDER BY a.account_name");
            break;


        case 'active':
            $rows = $obj->executequery($pivotBase . "ORDER BY row_total DESC, a.account_name");
            break;

        case 'counter':

            $lastBrandCols = '';
            foreach ($brandIds as $bid) {
                $bid = intval($bid);
                $lastBrandCols .= "
            SUM(CASE WHEN td.brand_id=$bid THEN td.net_amt ELSE 0 END) AS brand_$bid,";
            }
            $lastBrandCols = rtrim($lastBrandCols, ',');

            $rows = $obj->executequery("
        SELECT
            a.account_id,
            a.account_name,
            r.route_id,
            r.batch_no,
            r.route_name,
            MAX(te.billdate) AS last_order_date,
            $lastBrandCols,
            SUM(td.net_amt) AS row_total,
            COUNT(DISTINCT te.transaction_id) AS invoice_count

        FROM account a

        INNER JOIN route_counter rc
            ON rc.account_id=a.account_id
           AND rc.is_active=1

        INNER JOIN route_plan rp
            ON rp.batch_no=rc.batch_no
           AND rp.sales_executive_id=$uid

        INNER JOIN route r
            ON r.batch_no=rp.batch_no

        INNER JOIN transaction_entry te
            ON te.account_id=a.account_id
           AND te.type='order'
           AND te.companyid='$cid'

        INNER JOIN transaction_details td
            ON td.transaction_id=te.transaction_id
           AND td.type='order'

        WHERE
            a.type='customer'

        AND NOT EXISTS
        (
            SELECT 1
            FROM transaction_entry te2
            WHERE te2.account_id=a.account_id
            AND te2.type='order'
            AND te2.is_approved=1
            AND te2.companyid='$cid'
            AND te2.billdate BETWEEN '$monthStart' AND '$monthEnd'
        )

        GROUP BY
            a.account_id,
            a.account_name,
            r.route_id,
            r.batch_no,
            r.route_name

        ORDER BY
            last_order_date DESC,
            a.account_name
    ");

            break;

        case 'inactive':
            $customers = $obj->executequery("SELECT DISTINCT
                    a.account_id,
                    a.account_name,
                    a.status1
                FROM account a
                INNER JOIN route_counter rc
                    ON rc.account_id = a.account_id AND rc.is_active = 1
                INNER JOIN route_plan rp
                    ON rp.batch_no = rc.batch_no
                   AND rp.sales_executive_id = $uid
                LEFT JOIN transaction_entry te
                    ON te.account_id = a.account_id
                   AND te.type = 'order'
                   AND te.companyid = $cid
                WHERE a.type = 'customer'
                  AND te.transaction_id IS NULL
                ORDER BY a.account_name
            ");
            break;

        case 'invoices':
            $invBrandCols = '';
            foreach ($brandIds as $bid) {
                $bid = intval($bid);
                $invBrandCols .= "SUM(CASE WHEN td.brand_id = $bid THEN td.qty ELSE 0 END) AS brand_$bid,";
            }

            $invBrandCols = rtrim($invBrandCols, ',');

            $rows = $obj->executequery("SELECT
                    a.account_id,
                    a.account_name,
                    r.route_id,
                    r.batch_no,
                    r.route_name,
                    $invBrandCols,
                    SUM(td.qty)     AS row_total,
                    COUNT(DISTINCT te.transaction_id) AS invoice_count
                FROM account a
                INNER JOIN route_counter rc ON rc.account_id = a.account_id AND rc.is_active = 1
                INNER JOIN route_plan rp    ON rp.batch_no = rc.batch_no AND rp.sales_executive_id = $uid
                INNER JOIN route r          ON r.batch_no = rp.batch_no
                INNER JOIN transaction_entry te
                    ON te.account_id = a.account_id
                   AND te.type = 'order' AND te.is_approved = 1
                   AND te.companyid = '$cid'
                   AND te.createdby = $uid
                   AND te.billdate BETWEEN '$monthStart' AND '$monthEnd'
                INNER JOIN transaction_details td
                    ON td.transaction_id = te.transaction_id AND td.type = 'order'
                WHERE a.type = 'customer'
                GROUP BY a.account_id, a.account_name, r.batch_no, r.route_name
                ORDER BY invoice_count DESC, a.account_name
            ");
            break;

        case 'business':
            $rows = $obj->executequery($pivotBase . "
                ORDER BY row_total DESC, a.account_name
            ");
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid category']);
            exit;
    }

    if (isset($customers)) {
        echo json_encode([
            'brands'    => [],
            'rows'      => [],
            'customers' => $customers,
        ]);
    } else {
        foreach ($rows as &$row) {
            foreach ($brandIds as $bid) {
                $key = 'brand_' . $bid;
                $row[$key] = (float)($row[$key] ?? 0);
            }
            $row['row_total']     = (float)($row['row_total']     ?? 0);
            $row['invoice_count'] = (int)  ($row['invoice_count'] ?? 0);
        }
        unset($row);

        echo json_encode([
            'brands'    => $brandRows,
            'rows'      => $rows,
            'customers' => [],
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
