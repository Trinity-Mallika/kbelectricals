<?php include("../adminsession.php");

$title = "Dashboard";
$pagename = "dashboard.php";
$totalvendor = $obj->getvalfield("account", "count(*)", "type='customer' and companyid='$companyid'");
// current day
$total_daily_entries = $obj->getvalfield("daily_entries", "count(*)", "DATE(createdate)='$createdate' and companyid='$companyid'");
$total_order = $obj->getvalfield("transaction_entry", "count(*)", "billdate='$createdate' and type='order' and companyid='$companyid'");
$total_pay = $obj->getvalfield("transaction_entry", "sum(grand_total)", "billdate='$createdate' and type='payment' and companyid='$companyid'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
    <style>
        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card h6 {
            font-size: 14px;
            opacity: 0.9;
        }

        .dashboard-card h3 {
            margin: 0;
            font-weight: bold;
        }

        .icon {
            font-size: 45px;
            opacity: 0.3;
        }

        .dashboard-header {
            font-weight: 600;
            color: #444;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- Header -->
        <?php include('component/header.php'); ?>
        <!-- Header Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row mt-3">
                <h5 class="dashboard-header mb-3">Dashboard Overview</h5>

                <!-- Total Vendors -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="accounts.php" class="text-decoration-none">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body position-relative">
                                <h6>Total Customers</h6>
                                <h3><?= $totalvendor; ?></h3>
                                <i class="bi bi-people icon position-absolute end-0 bottom-0 me-3 mb-2"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Daily Entries -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="daily_visit_list.php" class="text-decoration-none">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body position-relative">
                                <h6>Today's Visits</h6>
                                <h3><?= $total_daily_entries; ?></h3>
                                <i class="bi bi-person-check icon position-absolute end-0 bottom-0 me-3 mb-2"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Orders -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="order_list.php" class="text-decoration-none">
                        <div class="card dashboard-card bg-warning text-white">
                            <div class="card-body position-relative">
                                <h6>Today's Orders</h6>
                                <h3><?= $total_order; ?></h3>
                                <i class="bi bi-cart icon position-absolute end-0 bottom-0 me-3 mb-2"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Collection -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="payment_list.php" class="text-decoration-none">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body position-relative">
                                <h6>Today's Collection</h6>
                                <h3>₹<?= number_format($total_pay); ?></h3>
                                <i class="bi bi-cash-stack icon position-absolute end-0 bottom-0 me-3 mb-2"></i>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>
    <!-- Content close-->
</body>
<!-- script tag -->
<?php include('component/script.php'); ?>

</html>