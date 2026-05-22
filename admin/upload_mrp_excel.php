<?php include("../adminsession.php");
$title = 'Excel Product Upload';
$module = "Excel Product Upload";
$pagename = "upload_mrp_excel.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/SimpleXLSX.php';

function normalize($str)
{
    $str = html_entity_decode($str);
    $str = strtolower($str);
    $str = trim($str);
    $str = str_replace(['*', '(', ')', '.', ','], ' ', $str);
    $str = str_replace(['sqmm'], 'mm', $str);
    $str = preg_replace('/\s+/', ' ', $str);

    return $str;
}

if (isset($_POST['submit'])) {

    if (empty($_FILES['excel_file']['tmp_name'])) {
        die("No file uploaded.");
    }

    $file_name = $_FILES['excel_file']['name'];
    $file_tmp  = $_FILES['excel_file']['tmp_name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $rows = [];

    if ($file_ext == 'xlsx') {

        if (!$xlsx = SimpleXLSX::parse($file_tmp)) {
            die("Invalid Excel file.");
        }

        $rows = $xlsx->rows();
    } elseif ($file_ext == 'csv') {

        if (($handle = fopen($file_tmp, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        } else {
            die("Unable to read CSV file.");
        }
    } else {
        die("Only XLSX and CSV files are allowed.");
    }

    $obj->begin();

    $updated = 0;
    $skipped = 0;
    $skipped_rows = [];

    try {

        foreach ($rows as $index => $r) {

            if ($index == 0) continue;

            $brand_name    = normalize($r[1] ?? '');
            $category_name = normalize($r[2] ?? '');
            $product_name  = normalize($r[4] ?? '');
            $rate          = trim($r[5] ?? '');
            $rate = str_replace(',', '', $rate);

            $row_data = [
                'row' => $index,
                'brand' => $brand_name,
                'category' => $category_name,
                'product' => $product_name,
                'rate' => $rate,
                'reason' => ''
            ];

            if ($brand_name == '' || $category_name == '' || $product_name == '') {
                $row_data['reason'] = 'Missing required fields';
            } elseif (!is_numeric($rate)) {
                $row_data['reason'] = 'Invalid rate';
            } else {

                $b = addslashes($brand_name);
                $c = addslashes($category_name);
                $p = addslashes($product_name);

                $words = explode(' ', $brand_name);
                $conditions = [];

                foreach ($words as $w) {
                    if (strlen($w) > 2) {
                        $conditions[] = "LOWER(cat_name) LIKE '%$w%'";
                    }
                }

                $where = implode(" AND ", $conditions);

                $brand_id = $obj->getvalfield(
                    "category_master",
                    "cat_id",
                    "$where AND type='brand'"
                );
                $words = explode(' ', $category_name);
                $conditions = [];

                foreach ($words as $w) {
                    if (strlen($w) > 2) {
                        $conditions[] = "LOWER(cat_name) LIKE '%$w%'";
                    }
                }

                $where = implode(" AND ", $conditions);

                $category_id = $obj->getvalfield(
                    "category_master",
                    "cat_id",
                    "$where AND brand_id='$brand_id'"
                );
                if (!$brand_id || !$category_id) {
                    $row_data['reason'] = 'Brand/Category not found';
                } else {

                    $p = normalize($r[4] ?? '');

                    $product_condition = [];
                    $words = explode(' ', $p);

                    foreach ($words as $w) {
                        if (strlen($w) > 2) { 
                            $product_condition[] = "LOWER(product_name) LIKE '%$w%'";
                        }
                    }

                    $product_where = implode(" AND ", $product_condition);

                    $product_id = $obj->getvalfield(
                        "product_master",
                        "product_id",
                        "brand_id='$brand_id' 
     AND category_id='$category_id' 
     AND $product_where"
                    );
                    if (!$product_id) {
                        $row_data['reason'] = 'Product not found';
                    } else {

                        $obj->executequery("
                            UPDATE product_master 
                            SET rate='$rate' 
                            WHERE product_id='$product_id'
                        ");

                        $updated++;
                        continue;
                    }
                }
            }

            $skipped_rows[] = $row_data;
            $skipped++;
        }

        $obj->commit();
    } catch (Exception $e) {
        $obj->rollback();
        die("Error: " . $e->getMessage());
    }

    echo "<h4>Updated: $updated | Skipped: $skipped</h4>";

    if (!empty($skipped_rows)) {

        echo "<h4 style='color:red;'>Skipped Records</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>Row</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Product</th>
                <th>Rate</th>
                <th>Reason</th>
              </tr>";

        foreach ($skipped_rows as $row) {
            echo "<tr>
                    <td>{$row['row']}</td>
                    <td>{$row['brand']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['product']}</td>
                    <td>{$row['rate']}</td>
                    <td style='color:red;'>{$row['reason']}</td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "<script>
            alert('All records updated successfully');
            location='$pagename';
        </script>";
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        .card-header {
            background-color: #06163a;
        }
    </style>
</head>

<body class="bg-light">
    <?php include('component/sidebar.php'); ?>
    <div class="main w-auto">
        <?php include('component/header.php'); ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $module; ?></legend>
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-header text-white"><?php echo $module; ?>
                                    <a href="export_products.php" class="btn btn-danger btn-sm float-end">Download Excel</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label for="heading">Upload Excel<span class="text-danger fw-bold">*</span></label>
                                            <input type="file" class="form-control form-control-sm" name="excel_file" id="imgname" accept=".xlsx,.csv">
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="Save" onClick="return checkinputmaster('imgname');">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</body>

<?php include('component/script.php'); ?>

</html>