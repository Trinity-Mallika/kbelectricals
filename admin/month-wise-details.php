<?php
include("../adminsession.php");
$title = "Month Wise Details";
$pagename = "month-wise-details.php";
$tblname = "";
$module = "Month Wise Details";
$submodule = "Month Wise Details";

$selMonth = isset($_GET['month']) ? intval($_GET['month']) : (int)date('m');
$selYear  = isset($_GET['year'])  ? intval($_GET['year'])  : (int)date('Y');
$emp_id  = isset($_GET['emp_id'])  ? intval($_GET['emp_id'])  : 0;

$monthStart = date('Y-m-01', mktime(0, 0, 0, $selMonth, 1, $selYear));
$monthEnd   = date('Y-m-t',  mktime(0, 0, 0, $selMonth, 1, $selYear));
$crit = ($emp_id > 0) ? "AND rp.sales_executive_id='$emp_id'" : '';
$crit1 = ($emp_id > 0) ? "AND createdby='$emp_id'" : '';

$brandRows = $obj->executequery(
    "SELECT cat_id, cat_name FROM category_master WHERE type='brand' ORDER BY cat_name"
);

$totalCustomers = $obj->getvalfield(
    "account a
     INNER JOIN route_counter rc ON rc.account_id=a.account_id AND rc.is_active=1
     INNER JOIN route_plan rp ON rp.batch_no=rc.batch_no $crit",
    "COUNT(DISTINCT a.account_id)",
    "a.type='customer'"
);

$activeCustomers = $obj->getvalfield(
    "account a
     INNER JOIN route_counter rc ON rc.account_id=a.account_id AND rc.is_active=1
     INNER JOIN route_plan rp ON rp.batch_no=rc.batch_no $crit
     INNER JOIN transaction_entry te
         ON te.account_id=a.account_id AND te.type='order' AND te.is_approved=1
        AND te.companyid='$companyid' AND te.billdate BETWEEN '$monthStart' AND '$monthEnd'",
    "COUNT(DISTINCT a.account_id)",
    "a.type='customer'"
);

$counterActive = $obj->getvalfield(
    "account a
    INNER JOIN route_counter rc
        ON rc.account_id=a.account_id
       AND rc.is_active=1
    INNER JOIN route_plan rp
        ON rp.batch_no=rc.batch_no
       $crit
    INNER JOIN transaction_entry te_ever
        ON te_ever.account_id=a.account_id
       AND te_ever.type='order'
       AND te_ever.companyid='$companyid'
       AND te_ever.is_approved=1",
    "COUNT(DISTINCT a.account_id)",
    "a.type='customer'
    AND a.account_id NOT IN (
        SELECT DISTINCT te2.account_id
        FROM transaction_entry te2
        WHERE te2.type='order'
        AND te2.is_approved=1
        AND te2.companyid='$companyid'
        AND te2.billdate BETWEEN '$monthStart' AND '$monthEnd'
    )"
);
$inactiveCustomers = (int)$totalCustomers - (int)$activeCustomers - (int)$counterActive;

$totalInvoices = $obj->getvalfield(
    "transaction_entry",
    "COUNT(*)",
    "type='order' AND is_approved=1 AND companyid='$companyid'
    $crit1 AND billdate BETWEEN '$monthStart' AND '$monthEnd'"
);

$totalBusiness = $obj->getvalfield(
    "transaction_entry",
    "COALESCE(SUM(grand_total),0)",
    "type='order' AND is_approved=1 AND companyid='$companyid'
    $crit1 AND billdate BETWEEN '$monthStart' AND '$monthEnd'"
) ?: 0;

$brandRows = $obj->executequery(
    "SELECT cat_id, cat_name FROM category_master WHERE type='brand' ORDER BY cat_name"
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <?php include('component/dashcss.php'); ?>
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
        <!-- Header -->
        <?php include('component/header.php'); ?>
        <!-- Header Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="">
                        <legend><?php echo $title; ?></legend>
                    </fieldset>
                </div>
                <div class="col-lg-3">
                    <form>
                        <div class="card">
                            <div class="card-header text-white">
                                <?= $module ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12 mb-2">
                                        <strong><label>Period</label></strong>
                                        <div class="input-group">
                                            <select name="month" id="selMonth" class="form-control form-control-sm">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= $m == $selMonth ? 'selected' : '' ?>>
                                                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                            <span class="input-group-text bg-white border-top-0 border-bottom-0 p-1" id="basic-addon1"></span>
                                            <select name="year" id="selYear" class="form-control form-control-sm ">
                                                <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--): ?>
                                                    <option value="<?= $y ?>" <?= $y == $selYear ? 'selected' : '' ?>><?= $y ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong><label>Sales Executive</label></strong>
                                        <select name="emp_id" id="emp_id" class="chosen-select form-control form-control-sm">
                                            <option value="0">-- All Executives --</option>
                                            <?php
                                            $execs = $obj->executequery("SELECT userid, fullname FROM user WHERE usertype='sales' AND companyid=$companyid ORDER BY fullname ASC");
                                            foreach ($execs as $row): ?>
                                                <option value="<?= $row['userid'] ?>" <?= $row['userid'] == $emp_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['fullname']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mt-2">
                                        <button type="submit" class="btn btn-sm btn-info form-control">Go</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class=" mt-2">
                        <div class="row kra-row p-0 ">
                            <a href="#0" class="stat-card-link col-12" data-target="total-customer" style="--c:#1a6ca8">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= $totalCustomers; ?></div>
                                    <span class="progress-label">Total Counters</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/customers.png" alt="" width="60px">
                                    </div>
                                </div>
                            </a>

                            <a href="#0" class="stat-card-link col-12" data-target="active-as-per-criteria" style="--c:#27ae60">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= $counterActive; ?></div>
                                    <span class="progress-label">Active as per Criteria</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/checklist.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="counter-active-not-meet" style="--c:#f39c12">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= $counterActive; ?></div>
                                    <span class="progress-label">Counter Active but not meeting criteria</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/meeting.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="invoice-no" style="--c:#8e44ad">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= $totalInvoices; ?></div>
                                    <span class="progress-label">Total No. of Invoices</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/invoice.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="inactive" style="--c:#e74c3c">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= $inactiveCustomers; ?></div>
                                    <span class="progress-label">Inactive</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/inactive.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="total-business-close" style="--c:#c926e7">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0">₹<?= $totalBusiness ?>K</div>
                                    <span class="progress-label">Total Business Closed</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/business-close.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>

                        </div>
                    </div>
                </div>
                <div class="col-lg-9 details-card">
                    <div class="card total-customer">
                        <div class="card-header text-white">
                            Total Customers
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table table-bordered">
                                        <tr class="table-primary">
                                            <th>S. No.</th>
                                            <th>Customer Name</th>
                                            <th>Whatsapp No.</th>
                                            <th>Owner No.</th>
                                        </tr>
                                        <?php $customers = $obj->executequery("SELECT DISTINCT
                    a.account_id,
                    a.account_name,
                    a.o_mobile_no,
                    a.mobile_no
                FROM account a
                INNER JOIN route_counter rc
                    ON rc.account_id = a.account_id AND rc.is_active = 1
                INNER JOIN route_plan rp
                    ON rp.batch_no = rc.batch_no
                  $crit
                WHERE a.type = 'customer'
                ORDER BY a.account_name");
                                        $i = 1;
                                        foreach ($customers as $key) { ?>
                                            <tr>
                                                <td><?= $i++; ?>.</td>
                                                <td><?= $key['account_name'] ?></td>
                                                <td><?= $key['mobile_no'] ?></td>
                                                <td><?= $key['o_mobile_no'] ?></td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card active-as-per-criteria">
                        <div class="card-header text-white">
                            Active as per Criteria
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Routes</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Routes --</option>
                                        <option value="0">Routes 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Brands --</option>
                                        <?php foreach ($brandRows as $b): ?>
                                            <option value="<?= $b['cat_id'] ?>"><?= htmlspecialchars($b['cat_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" name="" id="" class="form-control form-control-sm ">
                                </div>
                                <div class="col-lg-3"><br>
                                    <a href="#0" class="btn btn-sm btn-success"> Search</a>
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>Route</th>
                                                <th>Customer Name</th>
                                                <th>Great White</th>
                                                <th>Halonix </th>
                                                <th>KEI Wires & Cables</th>
                                                <th>Other</th>
                                                <th>RR Kabel</th>
                                                <th>Saraswati Pipes</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $lastBrandCols = '';
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
           $crit

        INNER JOIN route r
            ON r.batch_no=rp.batch_no

        INNER JOIN transaction_entry te
            ON te.account_id=a.account_id
           AND te.type='order'
           AND te.companyid='$companyid'

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
            AND te2.companyid='$companyid'
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

                                            ?>
                                            <tr>
                                                <td>Beat 1 - Urkura</td>
                                                <td>
                                                    Vinayak H/W &amp; Electrical, Raipur <br>
                                                    <small class="text-secondary">Last: 2026-06-29</small>
                                                </td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center table-info">₹9.1K</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="2">Total</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>₹9.1K</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card counter-active-not-meet">
                        <div class="card-header text-white">
                            Counter Active but not meeting criteria
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Routes</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Routes --</option>
                                        <option value="0">Routes 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Brands --</option>
                                        <option value="0">Brands 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" name="" id="" class="form-control form-control-sm ">
                                </div>
                                <div class="col-lg-3"><br>
                                    <a href="#0" class="btn btn-sm btn-success"> Search</a>
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>Route</th>
                                                <th>Customer Name</th>
                                                <th>Great White</th>
                                                <th>Halonix </th>
                                                <th>KEI Wires & Cables</th>
                                                <th>Other</th>
                                                <th>RR Kabel</th>
                                                <th>Saraswati Pipes</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Beat 1 - Urkura</td>
                                                <td>
                                                    Vinayak H/W &amp; Electrical, Raipur <br>
                                                    <small class="text-secondary">Last: 2026-06-29</small>
                                                </td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center table-info">₹9.1K</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="2">Total</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>₹9.1K</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card invoice-no">
                        <div class="card-header text-white">
                            Total No. of Invoices
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Routes</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Routes --</option>
                                        <option value="0">Routes 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Brands --</option>
                                        <option value="0">Brands 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" name="" id="" class="form-control form-control-sm ">
                                </div>
                                <div class="col-lg-3"><br>
                                    <a href="#0" class="btn btn-sm btn-success"> Search</a>
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>Route</th>
                                                <th>Customer Name</th>
                                                <th>Great White</th>
                                                <th>Halonix </th>
                                                <th>KEI Wires & Cables</th>
                                                <th>Other</th>
                                                <th>RR Kabel</th>
                                                <th>Saraswati Pipes</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Beat 1 - Urkura</td>
                                                <td>
                                                    Vinayak H/W &amp; Electrical, Raipur <br>
                                                    <small class="text-secondary">Last: 2026-06-29</small>
                                                </td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center table-info">₹9.1K</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="2">Total</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>₹9.1K</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card inactive">
                        <div class="card-header text-white">
                            Inactive
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table table-bordered">
                                        <tr class="table-danger">
                                            <th>S. No.</th>
                                            <th>Customer Name</th>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>Customer Name 1</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card total-business-close">
                        <div class="card-header text-white">
                            Total Business Closed
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Routes</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Routes --</option>
                                        <option value="0">Routes 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select name="" id="" class="chosen-select form-control form-control-sm">
                                        <option value="">--Select All Brands --</option>
                                        <option value="0">Brands 1</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" name="" id="" class="form-control form-control-sm ">
                                </div>
                                <div class="col-lg-3"><br>
                                    <a href="#0" class="btn btn-sm btn-success"> Search</a>
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>Route</th>
                                                <th>Customer Name</th>
                                                <th>Great White</th>
                                                <th>Halonix </th>
                                                <th>KEI Wires & Cables</th>
                                                <th>Other</th>
                                                <th>RR Kabel</th>
                                                <th>Saraswati Pipes</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Beat 1 - Urkura</td>
                                                <td>
                                                    Vinayak H/W &amp; Electrical, Raipur <br>
                                                    <small class="text-secondary">Last: 2026-06-29</small>
                                                </td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">₹9.1K</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center">-</td>
                                                <td class=" text-center table-info">₹9.1K</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="2">Total</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>₹9.1K</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>-</th>
                                                <th>₹9.1K</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.details-card .card');
        const links = document.querySelectorAll('.stat-card-link');

        cards.forEach(function(card) {
            card.classList.add('d-none');
        });

        const firstCard = document.querySelector('.card.total-customer');
        if (firstCard) {
            firstCard.classList.remove('d-none');
        }

        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                if (!target) return;

                cards.forEach(function(card) {
                    card.classList.add('d-none');
                });

                const targetCard = document.querySelector('.card.' + target);
                if (targetCard) {
                    targetCard.classList.remove('d-none');
                }
            });
        });
    });
</script>

</html>