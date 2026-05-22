<?php include("../adminsession.php");


$filename = "product_list_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");

$output = fopen('php://output', 'w');

fputcsv($output, ['Sr No', 'Brand Name', 'Category Name', 'Product Code', 'Product Name', 'MRP', 'PKG']);

$sql = "SELECT 
            b.cat_name AS brand_name,
            c.cat_name AS category_name,
            p.product_name,
            p.product_code,
            p.package,
            p.rate
        FROM product_master p

        LEFT JOIN category_master b 
            ON b.cat_id = p.brand_id 
           AND b.type = 'brand'

        LEFT JOIN category_master c 
            ON c.cat_id = p.category_id 
           AND c.type = 'category'

        ORDER BY b.cat_name ASC, c.cat_name ASC, p.product_name ASC";

$res = $obj->executequery($sql);
$sr = 1;
foreach ($res as $row) {
    fputcsv($output, [
        $sr++,
        htmlspecialchars_decode($row['brand_name']),
        htmlspecialchars_decode($row['category_name']),
        htmlspecialchars_decode($row['product_code']),
        htmlspecialchars_decode($row['product_name']),
        $row['rate'],
        $row['package']
    ]);
}

fclose($output);
exit;
