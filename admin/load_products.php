<?php include("../adminsession.php");
$transaction_id = $_POST['transaction_id'];
$sql = "
SELECT 
    td.qty,
    p.product_name,
    b.cat_name AS brand_name,
    u.cat_name AS unit_name,
    c.cat_name AS category_name
FROM transaction_details td
LEFT JOIN product_master p 
    ON p.product_id = td.product_id
LEFT JOIN category_master b 
    ON b.cat_id = td.brand_id AND b.type='brand'
    LEFT JOIN category_master c 
    ON c.cat_id = td.category_id AND c.type='category'
LEFT JOIN category_master u 
    ON u.cat_id = td.unit_id AND u.type='unit'
WHERE td.transaction_id = '$transaction_id' AND td.type='order'
ORDER BY td.tran_detail_id DESC
";
$qry = $obj->executequery($sql);

echo "<table class='table table-sm table-bordered'>";
echo "<tr><th>Category</th><th>Product</th><th>Brand</th><th>Unit</th><th>Qty</th></tr>";

foreach ($qry as $row) {
    echo "<tr>";
    echo "<td>" . $row['category_name'] . "</td>";
    echo "<td>" . $row['product_name'] . "</td>";
    echo "<td>" . $row['brand_name'] . "</td>";
    echo "<td>" . $row['unit_name'] . "</td>";
    echo "<td>" . $row['qty'] . "</td>";
    echo "</tr>";
}

echo "</table>";
