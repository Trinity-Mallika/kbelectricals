<?php
include("../adminsession.php");
$title = "Month Wise Details";
$pagename = "month-wise-details.php";
$tblname = "";
$module = "Month Wise Details";
$submodule = "Month Wise Details";

$selMonth = isset($_GET['month']) ? intval($_GET['month']) : (int)date('m');
$selYear  = isset($_GET['year'])  ? intval($_GET['year'])  : (int)date('Y');
$emp_id   = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;

$monthStart = date('Y-m-01', mktime(0, 0, 0, $selMonth, 1, $selYear));
$monthEnd   = date('Y-m-t',  mktime(0, 0, 0, $selMonth, 1, $selYear));
$crit  = ($emp_id > 0) ? "AND rp.sales_executive_id='$emp_id'" : '';
$crit1 = ($emp_id > 0) ? "AND createdby='$emp_id'" : '';

// ---- Brands (fetched once) ----
$brandRows = $obj->executequery(
    "SELECT cat_id AS id, cat_name AS name FROM category_master WHERE type='brand' and cat_id!='35' ORDER BY cat_name"
);

// ---- Stat card numbers ----
$totalCustomers = $obj->getvalfield(
    "account a
     INNER JOIN route_counter rc ON rc.account_id=a.account_id AND rc.is_active=1
     INNER JOIN route_plan rp ON rp.batch_no=rc.batch_no $crit",
    "COUNT(DISTINCT a.account_id)",
    "a.type='customer'"
);

// Customers who ordered (approved) within the selected month -> meets criteria
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

// Customers with an active counter who HAVE ordered before, but NOT this month -> "counter active, not meeting criteria"
$counterActive = $obj->getvalfield(
    "account a
    INNER JOIN route_counter rc ON rc.account_id=a.account_id AND rc.is_active=1
    INNER JOIN route_plan rp ON rp.batch_no=rc.batch_no $crit
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
if ($inactiveCustomers < 0) $inactiveCustomers = 0;

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

// ---- Total Customers table (rendered server-side, same as before) ----
$customers = $obj->executequery("SELECT DISTINCT
        a.account_id,
        r.route_name,
        a.account_name,
        a.owner_name,
        a.o_mobile_no,
        a.mobile_no
    FROM account a
    INNER JOIN route_counter rc ON rc.account_id = a.account_id AND rc.is_active = 1
    INNER JOIN route_plan rp ON rp.batch_no = rc.batch_no $crit
    INNER JOIN route r ON r.batch_no = rp.batch_no
    WHERE a.type = 'customer'
    ORDER BY r.route_name");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('component/css.php'); ?>
    <?php include('component/dashcss.php'); ?>
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
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
                    <fieldset class="">
                        <legend><?php echo $title; ?></legend>
                    </fieldset>
                </div>
                <div class="col-lg-3">
                    <form method="get">
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
                                    <div class="title fs-4 mb-0"><?= (int)$totalCustomers; ?></div>
                                    <span class="progress-label">Total Counters</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/customers.png" alt="" width="60px">
                                    </div>
                                </div>
                            </a>

                            <a href="#0" class="stat-card-link col-12" data-target="active-as-per-criteria" style="--c:#27ae60">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= (int)$activeCustomers; ?></div>
                                    <span class="progress-label">Active as per Criteria</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/checklist.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="counter-active-not-meet" style="--c:#f39c12">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= (int)$counterActive; ?></div>
                                    <span class="progress-label">Counter Active but not meeting criteria</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/meeting.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="invoice-no" style="--c:#8e44ad">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= (int)$totalInvoices; ?></div>
                                    <span class="progress-label">Total No. of Invoices</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/invoice.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="inactive" style="--c:#e74c3c">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0"><?= (int)$inactiveCustomers; ?></div>
                                    <span class="progress-label">Inactive</span>
                                    <div class=" stat-icon opacity-100 pt-2">
                                        <img src="assets/img/inactive.png" alt="" width="55px">
                                    </div>
                                </div>
                            </a>
                            <a href="#0" class="stat-card-link col-12" data-target="total-business-close" style="--c:#c926e7">
                                <div class="stat-card">
                                    <div class="title fs-4 mb-0">₹<?= number_format((float)$totalBusiness / 1000, 1) ?>K</div>
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

                    <!-- Total Customers (server-rendered) -->
                    <div class="card total-customer">
                        <div class="card-header text-white">Total Customers</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table id="example" class="table table-bordered table-sm table-hover">
                                            <thead>
                                                <tr class="table-primary">
                                                    <th>S. No.</th>
                                                    <th>Route Name</th>
                                                    <th>Customer Name</th>
                                                    <th>Whatsapp No.</th>
                                                    <th>Owner Name</th>
                                                    <th>Owner No.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1;
                                                foreach ($customers as $key): ?>
                                                    <tr>
                                                        <td><?= $i++; ?>.</td>
                                                        <td><?= htmlspecialchars($key['route_name']) ?></td>
                                                        <td><?= htmlspecialchars($key['account_name']) ?></td>
                                                        <td><?= htmlspecialchars($key['mobile_no']) ?></td>
                                                        <td><?= htmlspecialchars($key['owner_name']) ?></td>
                                                        <td><?= htmlspecialchars($key['o_mobile_no']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active as per Criteria (AJAX: category=active) -->
                    <div class="card active-as-per-criteria">
                        <div class="card-header text-white">Active as per Criteria</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select class="form-control form-control-sm brand-filter" data-table="tbl-active-as-per-criteria">
                                        <option value="">-- All Brands --</option>
                                        <?php foreach ($brandRows as $b): ?>
                                            <option value="brand_<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" class="form-control form-control-sm name-filter" data-table="tbl-active-as-per-criteria">
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered" id="tbl-active-as-per-criteria">
                                        <thead>
                                            <tr class="table-primary"></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Loading…</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary"></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Counter Active but not meeting criteria (AJAX: category=counter) -->
                    <div class="card counter-active-not-meet">
                        <div class="card-header text-white">Counter Active but not meeting criteria</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select class="form-control form-control-sm brand-filter" data-table="tbl-counter-active-not-meet">
                                        <option value="">-- All Brands --</option>
                                        <?php foreach ($brandRows as $b): ?>
                                            <option value="brand_<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" class="form-control form-control-sm name-filter" data-table="tbl-counter-active-not-meet">
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered" id="tbl-counter-active-not-meet">
                                        <thead>
                                            <tr class="table-primary"></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Loading…</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary"></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total No. of Invoices (AJAX: category=invoices) -->
                    <div class="card invoice-no">
                        <div class="card-header text-white">Total No. of Invoices</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select class="form-control form-control-sm brand-filter" data-table="tbl-invoice-no">
                                        <option value="">-- All Brands --</option>
                                        <?php foreach ($brandRows as $b): ?>
                                            <option value="brand_<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" class="form-control form-control-sm name-filter" data-table="tbl-invoice-no">
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered" id="tbl-invoice-no">
                                        <thead>
                                            <tr class="table-primary"></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Loading…</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary"></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive (AJAX: category=inactive) -->
                    <div class="card inactive">
                        <div class="card-header text-white">Inactive</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <table class="table table-bordered" id="tbl-inactive">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>S. No.</th>
                                                <th>Route Name</th>
                                                <th>Customer Name</th>
                                                <th>Owner Name</th>
                                                <th>Whatsapp No.</th>
                                                <th>Owner No.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">Loading…</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Business Closed (AJAX: category=business) -->
                    <div class="card total-business-close">
                        <div class="card-header text-white">Total Business Closed</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <strong><label>All Brands</label></strong>
                                    <select class="form-control form-control-sm brand-filter" data-table="tbl-total-business-close">
                                        <option value="">-- All Brands --</option>
                                        <?php foreach ($brandRows as $b): ?>
                                            <option value="brand_<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <strong><label>Search Customer</label></strong>
                                    <input type="text" class="form-control form-control-sm name-filter" data-table="tbl-total-business-close">
                                </div>
                                <div class="col-lg-12">
                                    <hr>
                                </div>
                                <div class="col-lg-12 mt-1 mb-0">
                                    <table class="table table-bordered" id="tbl-total-business-close">
                                        <thead>
                                            <tr class="table-primary"></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Loading…</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary"></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
<?php include('component/script.php'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const AJAX_URL = 'ajax/get_customer_list.php';
        const COMPANY_ID = <?= json_encode($companyid) ?>;
        const SEL_MONTH = <?= json_encode($selMonth) ?>;
        const SEL_YEAR = <?= json_encode($selYear) ?>;
        const EMP_ID = <?= json_encode($emp_id) ?>;

        // Maps card class -> {category, type, showLastOrder, isQty}
        const CARD_CONFIG = {
            'active-as-per-criteria': {
                category: 'active',
                type: 'pivot'
            },
            'counter-active-not-meet': {
                category: 'counter',
                type: 'pivot',
                showLastOrder: true
            },
            'invoice-no': {
                category: 'invoices',
                type: 'pivot',
                isQty: true
            },
            'inactive': {
                category: 'inactive',
                type: 'customers'
            },
            'total-business-close': {
                category: 'business',
                type: 'pivot'
            }
        };

        const cache = {}; // category -> payload (so switching cards / re-filtering doesn't refetch)

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function(s) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [s];
            });
        }

        function formatMoney(n) {
            return '₹' + (parseFloat(n) || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function formatQty(v) {
            return 'Pcs - ' + Math.round(parseFloat(v) || 0);
        }

        function fetchCategory(category) {
            const params = new URLSearchParams({
                loginid: EMP_ID,
                companyid: COMPANY_ID,
                category: category,
                month: SEL_MONTH,
                year: SEL_YEAR
            });
            return fetch(AJAX_URL + '?' + params.toString()).then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            });
        }

        function renderPivotTable(tableId, payload, opts) {
            const brands = payload.brands || [];
            const rows = payload.rows || [];
            const thead = document.querySelector('#' + tableId + ' thead tr');
            const tbody = document.querySelector('#' + tableId + ' tbody');
            const tfoot = document.querySelector('#' + tableId + ' tfoot tr');
            const colCount = brands.length + 3;

            let head = '<th>Route</th><th>Customer Name</th>';
            brands.forEach(function(b) {
                head += '<th>' + escapeHtml(b.name) + '</th>';
            });
            head += '<th>Total</th>';
            thead.innerHTML = head;

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="text-center text-muted py-3">No records found</td></tr>';
                tfoot.innerHTML = '';
                return;
            }

            const fmt = opts.isQty ? formatQty : formatMoney;
            const brandTotals = {};
            brands.forEach(function(b) {
                brandTotals[b.id] = 0;
            });
            let grandTotal = 0;

            let body = '';
            rows.forEach(function(row) {
                body += '<tr>';
                body += '<td>' + escapeHtml(row.route_name || '-') + '</td>';
                let nameCell = escapeHtml(row.account_name);
                if (opts.showLastOrder && row.last_order_date) {
                    nameCell += '<br><small class="text-secondary">Last: ' + escapeHtml(row.last_order_date) + '</small>';
                }
                body += '<td>' + nameCell + '</td>';
                brands.forEach(function(b) {
                    const val = parseFloat(row['brand_' + b.id]) || 0;
                    brandTotals[b.id] += val;
                    body += '<td class="text-end">' + (val ? fmt(val) : '-') + '</td>';
                });
                const rowTotal = parseFloat(row.row_total) || 0;
                grandTotal += rowTotal;
                body += '<td class="text-end table-info">' + fmt(rowTotal) + '</td>';
                body += '</tr>';
            });
            tbody.innerHTML = body;

            let foot = '<th colspan="2">Total</th>';
            brands.forEach(function(b) {
                foot += '<th class="text-end">' + (brandTotals[b.id] ? fmt(brandTotals[b.id]) : '-') + '</th>';
            });
            foot += '<th class="text-end">' + fmt(grandTotal) + '</th>';
            tfoot.innerHTML = foot;
        }

        function renderCustomerTable(tableId, payload) {
            const customers = payload.customers || [];
            const tbody = document.querySelector('#' + tableId + ' tbody');

            if (!customers.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No records found</td></tr>';
                return;
            }

            let body = '';

            customers.forEach(function(c, i) {
                body += `
            <tr class="text-danger fw-bold">
                <td>${i + 1}</td>
                <td>${escapeHtml(c.route_name)}</td>
                <td>${escapeHtml(c.account_name)}</td>
                <td>${escapeHtml(c.owner_name || '-')}</td>
                <td>${escapeHtml(c.mobile_no || '-')}</td>
                <td>${escapeHtml(c.o_mobile_no || '-')}</td>
            </tr>
        `;
            });

            tbody.innerHTML = body;
        }

        function render(cardName, payload) {
            const cfg = CARD_CONFIG[cardName];
            const tableId = 'tbl-' + cardName;
            if (cfg.type === 'pivot') {
                renderPivotTable(tableId, payload, cfg);
            } else {
                renderCustomerTable(tableId, payload);
            }
        }

        function loadCard(cardName) {
            const cfg = CARD_CONFIG[cardName];
            if (!cfg) return; // total-customer is server-rendered, nothing to do

            if (cache[cfg.category]) {
                render(cardName, cache[cfg.category]);
                return;
            }

            const tbody = document.querySelector('#tbl-' + cardName + ' tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Loading…</td></tr>';

            fetchCategory(cfg.category)
                .then(function(payload) {
                    cache[cfg.category] = payload;
                    render(cardName, payload);
                })
                .catch(function(err) {
                    if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3">Failed to load data</td></tr>';
                    console.error(err);
                });
        }

        // Client-side filter (brand / name) applied on already-loaded rows
        function applyFilters(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const brandSelect = document.querySelector('.brand-filter[data-table="' + tableId + '"]');
            const nameInput = document.querySelector('.name-filter[data-table="' + tableId + '"]');
            const brandKey = brandSelect ? brandSelect.value : '';
            const nameQuery = nameInput ? nameInput.value.trim().toLowerCase() : '';

            const headers = Array.prototype.map.call(table.querySelectorAll('thead th'), function(th) {
                return th.textContent.trim();
            });
            const brandColIndex = brandKey ? headers.findIndex(function(h) {
                return brandSelect.options[brandSelect.selectedIndex].text === h;
            }) : -1;

            Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function(tr) {
                const cells = tr.querySelectorAll('td');
                if (!cells.length) return;
                let visible = true;

                if (nameQuery) {
                    const nameText = (cells[1] ? cells[1].textContent : '').toLowerCase();
                    visible = visible && nameText.indexOf(nameQuery) !== -1;
                }
                if (visible && brandColIndex > -1 && cells[brandColIndex]) {
                    visible = visible && cells[brandColIndex].textContent.trim() !== '-';
                }
                tr.style.display = visible ? '' : 'none';
            });
        }

        document.querySelectorAll('.brand-filter, .name-filter').forEach(function(el) {
            el.addEventListener('input', function() {
                applyFilters(this.getAttribute('data-table'));
            });
            el.addEventListener('change', function() {
                applyFilters(this.getAttribute('data-table'));
            });
        });

        // Card switching
        const cards = document.querySelectorAll('.details-card .card');
        const links = document.querySelectorAll('.stat-card-link');

        cards.forEach(function(card) {
            card.classList.add('d-none');
        });
        const firstCard = document.querySelector('.card.total-customer');
        if (firstCard) firstCard.classList.remove('d-none');

        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                if (!target) return;

                cards.forEach(function(card) {
                    card.classList.add('d-none');
                });
                const targetCard = document.querySelector('.card.' + target);
                if (targetCard) targetCard.classList.remove('d-none');

                loadCard(target);
            });
        });
    });
</script>

</html>