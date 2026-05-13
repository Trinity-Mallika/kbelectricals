<?php

include("../adminsession.php");

$title = "Brand Wise Rate Setting";
$pagename = "brandwise_rate_setting.php";
$module = "Brand Wise Rate Setting";
$submodule = "Brand Wise Rate Setting";
$btn_name = "Save";
$tblname = "product_master";
$tblpkey = "product_id";
$brand_id = (isset($_GET["brand_id"]) && $_GET["brand_id"] != '') ? $obj->test_input($_GET["brand_id"]) : '';
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";

?>

<!DOCTYPE html>

<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        .card-header {
            background-color: #06163a;

        }
    </style>

</head>



<body class="bg-light">



    <!-- Sidebar -->

    <?php include('component/sidebar.php'); ?>

    <!-- Sidebar Close-->

    <div class="main w-auto">

        <!-- heading -->

        <?php include('component/header.php'); ?>

        <!-- heading Close-->

        <!-- Content -->

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">

                    <fieldset class="mt-2">

                        <legend><?php echo $title; ?></legend>

                        <?php include('component/alert.php'); ?>

                        <div class="card">

                            <div class="card-header text-white">

                                <?php echo $title; ?>

                            </div>

                            <div class="card-body">

                                <form action="" method="post">

                                    <div class="row">


                                        <div class="col-md-3 mb-2">

                                            <strong> <label for="images">Brand Name <span class="text-danger fw-bold">*</span></label></strong>

                                            <select type="text" class="form-control form-control-sm chosen-select" name="brand_id" id="brand_id" onchange="get_url(this.value)">
                                                <option value="">--Select Brand--</option>
                                                <?php

                                                $sql = $obj->executequery("select * from category_master where type='brand' order by cat_id DESC ");

                                                foreach ($sql as $key) {
                                                ?> <option value="<?php echo $key['cat_id'] ?>"><?php echo $key['cat_name'] ?></option> <?php } ?>
                                            </select>

                                            <script>
                                                document.getElementById('brand_id').value = '<?= $brand_id ?>';
                                            </script>

                                        </div>






                                    </div>

                                </form>

                            </div>

                        </div>

                    </fieldset>

                </div>

            </div>

            <div class="row mt-4 mb-4">

                <div class="col-lg-12">

                    <div class="card">

                        <div class="card-header text-white">

                            <?php echo $submodule; ?> RECORD

                        </div>
                        <?php if ($brand_id > 0) { ?>
                            <div class="card-body">

                                <div class="table-responsive">

                                    <table id="example" class="table table-bordered table-sm table-hover">

                                        <thead>

                                            <th class="text-center">Sr. No.</th>
                                            <th>Product Name</th>
                                            <th>Unit Name</th>
                                            <th>MRP</th>

                                        </thead>

                                        <tbody>
                                            <?php
                                            $i = 1;

                                            // 1️⃣ Fetch all products
                                            $products = $obj->executequery("
                                        SELECT product_id, product_name, unit_id
                                        FROM product_master
                                        ORDER BY product_id DESC
                                        ");

                                            // 2️⃣ Fetch all brand rates once
                                            $rate_data = $obj->executequery("
                                        SELECT product_id, unit_id, rate
                                        FROM brand_wise_rate_setting
                                        WHERE brand_id = '$brand_id'
                                        ");

                                            // 3️⃣ Create mapping array for fast access
                                            $rate_arr = [];
                                            foreach ($rate_data as $r) {
                                                $rate_arr[$r['product_id']][$r['unit_id']] = $r['rate'];
                                            }

                                            // 4️⃣ Loop products
                                            foreach ($products as $prod) {

                                                // Explode units
                                                $unit_ids = explode(',', $prod['unit_id']);

                                                foreach ($unit_ids as $unit_id) {

                                                    // Get unit name
                                                    $unit_name = $obj->getvalfield("category_master", "cat_name", "cat_id='$unit_id'");

                                                    // Get rate for this product + unit + brand
                                                    $rate = $rate_arr[$prod['product_id']][$unit_id] ?? '';
                                            ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $i++; ?></td>
                                                        <td><?php echo $prod['product_name']; ?></td>
                                                        <td><?php echo $unit_name; ?></td>
                                                        <td>
                                                            <input type="text"
                                                                class="form-control form-control-sm rate-input"
                                                                data-product_id="<?php echo $prod['product_id']; ?>"
                                                                data-unit_id="<?php echo $unit_id; ?>"
                                                                value="<?php echo $rate; ?>">
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            }
                                            ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php } else {
                            echo "<div class='card-body'><h4 class='text-danger'>Please select brand to set rate.</h4></div>";
                        } ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Content close-->

    </div>



</body>



<!-- script tag -->

<?php include('component/script.php'); ?>

<!-- script tag -->
<script>
    function saveBrandRate(product_id, brand_id, unit_id, rate, td) {

        if (brand_id == '') {
            alert("Please select brand first");
            return false;
        }

        $.ajax({
            url: 'ajax/save_brand_rate.php',
            type: 'POST',
            data: {
                product_id: product_id,
                brand_id: brand_id,
                unit_id: unit_id,
                rate: rate
            },
            success: function(res) {
                console.log("Saved:", res);

                // ✅ Refresh this row only
                refreshTD(product_id, unit_id, brand_id, td);
            },
            error: function(xhr) {
                console.log("Error:", xhr.responseText);
            }
        });
    }

    // Event listener
    $(document).on('blur', '.rate-input', function() {
        var rate = $(this).val();
        var product_id = $(this).data('product_id');
        var unit_id = $(this).data('unit_id');
        var brand_id = $('#brand_id').val();
        var td = $(this).closest('td');

        saveBrandRate(product_id, brand_id, unit_id, rate, td);
    });



    $(document).ready(function() {
        $(".chosen-select").chosen();
        $('#example').DataTable();
    });

    function get_url(brand_id) {
        window.location.href = `brandwise_rate_setting.php?brand_id=${brand_id}`;
    }
</script>

<script>
    function refreshTD(product_id, unit_id, brand_id, td) {
        $.ajax({
            url: 'ajax/get_brand_rate_row.php',
            type: 'POST',
            data: {
                product_id: product_id,
                unit_id: unit_id,
                brand_id: brand_id
            },
            success: function(res) {
                td.html(res); // replace only the content of TD
            },
            error: function(xhr) {
                console.log("Error:", xhr.responseText);
            }
        });
    }
</script>

</html>