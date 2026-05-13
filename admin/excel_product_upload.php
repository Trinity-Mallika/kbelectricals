<?php include("../adminsession.php");
$title = 'Excel Product Upload';
$module = "Excel Product Upload";
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/SimpleXLSX.php';

function clean_val($val)
{
    return ucwords(strtolower(trim(str_replace("\xC2\xA0", '', $val))));
}
$duplicate_accounts = [];
$row_index = 1;
// ✅ GET FROM SESSION (DO NOT RESET AFTER THIS)
$duplicate_accounts = $_SESSION['duplicate_accounts'] ?? [];
$error_rows = $_SESSION['error_rows'] ?? [];

unset($_SESSION['duplicate_accounts'], $_SESSION['error_rows']);

if (isset($_POST['submit'])) {

    if (empty($_FILES['excel_file']['tmp_name'])) {
        die("No file uploaded.");
    }

    if (!$xlsx = SimpleXLSX::parse($_FILES['excel_file']['tmp_name'])) {
        die("Invalid Excel file.");
    }

    $obj->begin();

    try {

        $firstRow = true;

        foreach ($xlsx->rows() as $r) {

            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $brand_name    = clean_val($r[1] ?? '');
            $category_name = clean_val($r[2] ?? '');
            $product_name  = clean_val($r[3] ?? '');

            if (!$brand_name || !$category_name || !$product_name) continue;

            $duplicate_accounts = []; // पहले define करो

            // =====================
            // ✅ BRAND
            // =====================
            $brand_id = $obj->getvalfield(
                "category_master",
                "cat_id",
                "LOWER(cat_name)=LOWER('$brand_name') AND type='brand'"
            );

            if ($brand_id) {
                $duplicate_accounts[] = [
                    'row' => $row_index,
                    'brand_name' => $brand_name,
                    'category_name' => '',
                    'product_name' => '',
                    'type' => 'Brand Duplicate'
                ];
            } else {
                $brand_id = $obj->insert_record_lastid("category_master", [
                    'cat_name'   => $brand_name,
                    'type'       => 'brand',
                    'ipaddress'  => $ipaddress,
                    'createdate' => $createdate
                ]);
            }

            // =====================
            // ✅ CATEGORY
            // =====================
            $category_id = $obj->getvalfield(
                "category_master",
                "cat_id",
                "LOWER(cat_name)=LOWER('$category_name') AND type='category' and brand_id='$brand_id'"
            );
            if ($category_id) {
                $duplicate_accounts[] = [
                    'row' => $row_index,
                    'brand_name' => $brand_name,
                    'category_name' => $category_name,
                    'product_name' => '',
                    'type' => 'Category Duplicate'
                ];
            } else {
                $category_id = $obj->insert_record_lastid("category_master", [
                    'cat_name'   => $category_name,
                    'brand_id' => $brand_id,
                    'type'       => 'category',
                    'ipaddress'  => $ipaddress,
                    'createdate' => $createdate
                ]);
            }

            // =====================
            // ✅ PRODUCT
            // =====================
            $product_exist = $obj->getvalfield(
                "product_master",
                "product_id",
                "LOWER(product_name)=LOWER('$product_name') 
     AND brand_id='$brand_id' 
     AND category_id='$category_id'"
            );

            if ($product_exist) {
                $duplicate_accounts[] = [
                    'row' => $row_index,
                    'brand_name' => $brand_name,
                    'category_name' => $category_name,
                    'product_name' => $product_name,
                    'type' => 'Product Duplicate'
                ];
            } else {
                $obj->insert_record("product_master", [
                    'product_name' => $product_name,
                    'brand_id'     => $brand_id,
                    'category_id'  => $category_id,
                    'unit_id'         => 1,
                    'ipaddress'    => $ipaddress,
                    'createdate'   => $createdate,
                    'createdby'    => $loginid,
                    'companyid'    => $companyid
                ]);
            }
        }

        $obj->commit();
    } catch (Exception $e) {
        $obj->rollback();
        die("Error: " . $e->getMessage());
    }
    $_SESSION['duplicate_accounts'] = $duplicate_accounts;
    echo "<script>location='$pagename';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

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
                                <div class="card-header text-white"><?php echo $module; ?></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label for="heading">Upload Excel<span class="text-danger fw-bold">*</span></label>
                                            <input type="file" class="form-control form-control-sm" name="excel_file" id="imgname" accept=".xlsx">
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="Save" onClick="return checkinputmaster('imgname');">
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
                <div class="col-lg-12 mt-2">
                    <div class="card">
                        <div class="card-header text-white">List</div>
                        <div class="card-body">
                            <div class="row">

                                <?php if (!empty($duplicate_accounts)) { ?>

                                    <h5 class="mb-3 text-danger">
                                        Duplicate Records (<?= count($duplicate_accounts) ?>)
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Row</th>
                                                    <th>Brand</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php foreach ($duplicate_accounts as $d) { ?>
                                                    <tr>
                                                        <td><?= $d['row'] ?></td>
                                                        <td><?= $d['brand_name'] ?></td>
                                                        <td><?= $d['category_name'] ?></td>
                                                        <td><?= $d['product_name'] ?></td>
                                                        <td>
                                                            <span class="badge bg-danger">
                                                                <?= $d['type'] ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php } else { ?>

                                    <div class="alert alert-success mb-0">
                                        No duplicate records found.
                                    </div>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>

<?php include('component/script.php'); ?>