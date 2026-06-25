<?php include("appsession.php");

$title    = "Month Wise Details";
$pagename = "customer_wise_view.php";

$selMonth = isset($_GET['month']) ? intval($_GET['month']) : (int)date('m');
$selYear  = isset($_GET['year'])  ? intval($_GET['year'])  : (int)date('Y');

$monthStart = date('Y-m-01', mktime(0, 0, 0, $selMonth, 1, $selYear));
$monthEnd   = date('Y-m-t',  mktime(0, 0, 0, $selMonth, 1, $selYear));

$brandRows = $obj->executequery(
    "SELECT cat_id, cat_name FROM category_master WHERE type='brand' ORDER BY cat_name"
);

$totalCustomers = $obj->getvalfield(
    "account a
     INNER JOIN route_counter rc ON rc.account_id=a.account_id AND rc.is_active=1
     INNER JOIN route_plan rp    ON rp.batch_no=rc.batch_no AND rp.sales_executive_id='$loginid'",
    "COUNT(DISTINCT a.account_id)",
    "a.type='customer'"
);

$activeCustomers = $obj->getvalfield(
    "account a
     INNER JOIN route_counter rc ON rc.account_id=a.account_id AND rc.is_active=1
     INNER JOIN route_plan rp    ON rp.batch_no=rc.batch_no AND rp.sales_executive_id='$loginid'
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
       AND rp.sales_executive_id='$loginid'
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
     AND createdby='$loginid' AND billdate BETWEEN '$monthStart' AND '$monthEnd'"
);

$totalBusiness = $obj->getvalfield(
    "transaction_entry",
    "COALESCE(SUM(grand_total),0)",
    "type='order' AND is_approved=1 AND companyid='$companyid'
     AND createdby='$loginid' AND billdate BETWEEN '$monthStart' AND '$monthEnd'"
) ?: 0;

$cards = [
    ['key' => 'total',    'label' => 'Total Customers',                       'icon' => 'bi-people-fill',            'cls' => 'cat-total',    'val' => (int)$totalCustomers,   'type' => 'list'],
    ['key' => 'active',   'label' => 'Active as per Criteria',                'icon' => 'bi-check-circle-fill',      'cls' => 'cat-active',   'val' => (int)$activeCustomers,  'type' => 'brand'],
    ['key' => 'counter',  'label' => 'Counter Active but not meeting criteria', 'icon' => 'bi-exclamation-circle-fill', 'cls' => 'cat-counter',  'val' => (int)$counterActive,    'type' => 'brand'],
    ['key' => 'inactive', 'label' => 'Inactive',                              'icon' => 'bi-x-circle-fill',          'cls' => 'cat-inactive', 'val' => (int)$inactiveCustomers, 'type' => 'list'],
    ['key' => 'invoices', 'label' => 'Total No. of Invoices',                 'icon' => 'bi-receipt',                'cls' => 'cat-invoices', 'val' => (int)$totalInvoices,    'type' => 'brand'],
    ['key' => 'business', 'label' => 'Total Business Closed',                 'icon' => 'bi-currency-rupee',         'cls' => 'cat-business', 'val' => (float)$totalBusiness,  'type' => 'brand'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= $title ?> · KBELECTRICAL</title>
    <?php include("inc/css-file.php"); ?>
    <style>
        .category-card {
            cursor: pointer;
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            transition: transform .15s;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .category-card:active {
            transform: scale(.97);
        }

        .category-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 18px;
        }

        .card-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .card-label {
            font-size: .88rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .card-tap {
            font-size: .68rem;
            color: #aaa;
        }

        .card-count {
            font-size: 1.5rem;
            font-weight: 700;
            min-width: 50px;
            text-align: right;
        }

        .cat-total {
            border-left: 4px solid #1565C0;
        }

        .cat-total .card-label,
        .cat-total .card-count {
            color: #1565C0;
        }

        .cat-total .card-icon {
            background: #E3F2FD;
            color: #1565C0;
        }

        .cat-active {
            border-left: 4px solid #2E7D32;
        }

        .cat-active .card-label,
        .cat-active .card-count {
            color: #2E7D32;
        }

        .cat-active .card-icon {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .cat-counter {
            border-left: 4px solid #E65100;
        }

        .cat-counter .card-label,
        .cat-counter .card-count {
            color: #E65100;
        }

        .cat-counter .card-icon {
            background: #FFF3E0;
            color: #E65100;
        }

        .cat-inactive {
            border-left: 4px solid #C62828;
        }

        .cat-inactive .card-label,
        .cat-inactive .card-count {
            color: #C62828;
        }

        .cat-inactive .card-icon {
            background: #FFEBEE;
            color: #C62828;
        }

        .cat-invoices {
            border-left: 4px solid #6A1B9A;
        }

        .cat-invoices .card-label,
        .cat-invoices .card-count {
            color: #6A1B9A;
        }

        .cat-invoices .card-icon {
            background: #F3E5F5;
            color: #6A1B9A;
        }

        .cat-business {
            border-left: 4px solid #00695C;
        }

        .cat-business .card-label,
        .cat-business .card-count {
            color: #00695C;
        }

        .cat-business .card-icon {
            background: #E0F2F1;
            color: #00695C;
        }

        .month-bar {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .07);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .month-bar label {
            font-size: .75rem;
            color: #666;
            margin: 0;
            white-space: nowrap;
        }

        .month-bar select {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px 8px;
            font-size: .82rem;
            color: #333;
            background: #f9f9f9;
        }

        .month-bar .btn-go {
            background: #1565C0;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: .82rem;
            font-weight: 600;
        }

        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 1040;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }

        .drawer-overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        .customer-drawer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 20px 20px 0 0;
            z-index: 1050;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            transform: translateY(100%);
            transition: transform .3s cubic-bezier(.32, 1.1, .55, 1);
            box-shadow: 0 -4px 24px rgba(0, 0, 0, .15);
        }

        .customer-drawer.show {
            transform: translateY(0);
        }

        .drawer-handle {
            width: 40px;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
            margin: 10px auto 0;
            flex-shrink: 0;
        }

        .drawer-header {
            padding: 12px 18px 10px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .drawer-title {
            font-size: .95rem;
            font-weight: 700;
            color: #222;
            margin: 0;
        }

        .drawer-subtitle {
            font-size: .72rem;
            color: #999;
            margin-top: 2px;
        }

        .drawer-close {
            background: #f2f2f2;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: .95rem;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .drawer-filters {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .drawer-filters select,
        .drawer-filters input {
            flex: 1;
            min-width: 110px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: .8rem;
            background: #f9f9f9;
        }

        .drawer-filters select:focus,
        .drawer-filters input:focus {
            border-color: #1565C0;
            outline: none;
        }

        .drawer-body {
            overflow-y: auto;
            flex: 1;
        }

        .cust-item {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            border-bottom: 1px solid #f5f5f5;
            gap: 12px;
        }

        .cust-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .cust-info {
            flex: 1;
            min-width: 0;
        }

        .cust-name {
            font-size: .88rem;
            font-weight: 600;
            color: #222;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cust-meta {
            font-size: .72rem;
            color: #999;
            margin-top: 2px;
        }

        .cust-badge {
            font-size: .68rem;
            padding: 3px 9px;
            border-radius: 20px;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .badge-active {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-inactive {
            background: #FFEBEE;
            color: #C62828;
        }

        .badge-total {
            background: #E3F2FD;
            color: #1565C0;
        }

        .brand-table-wrap {
            overflow-x: auto;
        }

        .brand-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            min-width: 520px;
        }

        .brand-table thead th {
            background: #f5f7fa;
            padding: 9px 12px;
            text-align: center;
            font-weight: 700;
            color: #444;
            border-bottom: 2px solid #e0e0e0;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .brand-table thead th:first-child,
        .brand-table thead th:nth-child(2) {
            text-align: left;
        }

        .brand-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .brand-table tbody tr:hover {
            background: #fafbff;
        }

        .brand-table tbody td {
            padding: 9px 12px;
            text-align: center;
            color: #333;
        }

        .brand-table tbody td:first-child {
            text-align: left;
            font-size: .72rem;
            color: #888;
        }

        .brand-table tbody td:nth-child(2) {
            text-align: left;
            font-weight: 600;
            color: #222;
        }

        .brand-table tfoot td {
            padding: 9px 12px;
            text-align: center;
            font-weight: 700;
            color: #1565C0;
            background: #EEF4FF;
            border-top: 2px solid #c5d8f5;
        }

        .brand-table tfoot td:first-child,
        .brand-table tfoot td:nth-child(2) {
            text-align: left;
        }

        .cell-val {
            font-weight: 600;
            color: #222;
        }

        .cell-zero {
            color: #ddd;
        }

        .col-total-cell {
            background: #f0f7ff;
            font-weight: 700;
            color: #1565C0;
        }

        .drawer-loading,
        .drawer-empty {
            text-align: center;
            padding: 40px 20px;
            color: #bbb;
            font-size: .88rem;
        }
    </style>
</head>

<body class="dashboard">
    <section class="top-sec">
        <?php include("inc/header.php"); ?>
        <div class="container py-2">
            <div class="month-bar">
                <label>Period</label>
                <select id="selMonth">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $selMonth ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select id="selYear">
                    <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $selYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button class="btn-go" onclick="reloadPage()">Go</button>
            </div>

            <!-- Cards -->
            <?php foreach ($cards as $c):
                $display = $c['key'] === 'business'
                    ? '₹' . ($c['val'] >= 100000
                        ? number_format($c['val'] / 100000, 1) . 'L'
                        : number_format($c['val'] / 1000, 1) . 'K')
                    : number_format($c['val']);
            ?>
                <div class="card category-card <?= $c['cls'] ?>"
                    onclick="openDrawer('<?= $c['key'] ?>', '<?= addslashes($c['label']) ?>', '<?= $c['type'] ?>')">
                    <div class="card-body">
                        <div class="card-left">
                            <div class="card-icon"><i class="bi <?= $c['icon'] ?>"></i></div>
                            <div>
                                <div class="card-label"><?= $c['label'] ?></div>
                                <div class="card-tap">Tap to view list</div>
                            </div>
                        </div>
                        <div class="card-count"><?= $display ?></div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <!-- Overlay -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

    <!-- Drawer -->
    <div class="customer-drawer" id="customerDrawer">
        <div class="drawer-handle"></div>
        <div class="drawer-header">
            <div>
                <div class="drawer-title" id="drawerTitle">Details</div>
                <div class="drawer-subtitle" id="drawerSubtitle"></div>
            </div>
            <button class="drawer-close" onclick="closeDrawer()">✕</button>
        </div>

        <!-- Filters (shown for brand-type, hidden for list-type) -->
        <div class="drawer-filters" id="drawerFilters">
            <select id="filterRoute" onchange="applyFilter()">
                <option value="">All Routes</option>
            </select>
            <select id="filterBrand" onchange="applyFilter()">
                <option value="">All Brands</option>
                <?php foreach ($brandRows as $b): ?>
                    <option value="<?= $b['cat_id'] ?>"><?= htmlspecialchars($b['cat_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="filterSearch" placeholder="Search customer…" oninput="applyFilter()">
        </div>

        <div class="drawer-body" id="drawerBody"></div>
    </div>

    <?php include("inc/js-file.php"); ?>
    <script>
        let rawData = [];
        let allBrands = [];
        let currentCategory = '';
        let currentType = ''; 
        let currentMonth = <?= $selMonth ?>;
        let currentYear = <?= $selYear  ?>;
        const loginid = '<?= $loginid ?>';
        const companyid = '<?= $companyid ?>';

        const avatarStyle = {
            total: ['#E3F2FD', '#1565C0'],
            inactive: ['#FFEBEE', '#C62828'],
        };

        function reloadPage() {
            const m = document.getElementById('selMonth').value;
            const y = document.getElementById('selYear').value;
            window.location.href = '?month=' + m + '&year=' + y;
        }

        function openDrawer(category, label, type) {
            currentCategory = category;
            currentType = type;

            document.getElementById('drawerTitle').textContent = label;
            document.getElementById('drawerSubtitle').textContent = '';
            document.getElementById('filterRoute').value = '';
            document.getElementById('filterBrand').value = '';
            document.getElementById('filterSearch').value = '';

            document.getElementById('drawerFilters').style.display =
                type === 'brand' ? 'flex' : 'none';

            document.getElementById('drawerBody').innerHTML =
                '<div class="drawer-loading"><div class="spinner-border text-primary mb-2"></div><br>Loading…</div>';

            document.getElementById('drawerOverlay').classList.add('show');
            document.getElementById('customerDrawer').classList.add('show');
            document.body.style.overflow = 'hidden';

            fetch('ajax/get_customer_list.php?category=' + category +
                    '&month=' + currentMonth + '&year=' + currentYear +
                    '&loginid=' + loginid + '&companyid=' + companyid)
                .then(r => r.json())
                .then(data => {
                    if (type === 'list') {
                        rawData = data.customers ?? [];
                        allBrands = [];
                        document.getElementById('drawerSubtitle').textContent =
                            rawData.length + ' customer' + (rawData.length !== 1 ? 's' : '');
                        renderList(rawData);
                    } else {
                        rawData = data.rows ?? [];
                        allBrands = data.brands ?? [];

                        const seen = new Map();
                        rawData.forEach(r => {
                            if (!seen.has(r.batch_no)) seen.set(r.batch_no, r.route_name);
                        });
                        const rs = document.getElementById('filterRoute');
                        rs.innerHTML = '<option value="">All Routes</option>';
                        seen.forEach((name, id) => {
                            const o = document.createElement('option');
                            o.value = id;
                            o.textContent = name || '—';
                            rs.appendChild(o);
                        });

                        document.getElementById('drawerSubtitle').textContent =
                            rawData.length + ' customer' + (rawData.length !== 1 ? 's' : '');
                        applyFilter();
                    }
                })
                .catch(() => {
                    document.getElementById('drawerBody').innerHTML =
                        '<div class="drawer-empty">⚠️ Failed to load. Please try again.</div>';
                });
        }

        function closeDrawer() {
            document.getElementById('drawerOverlay').classList.remove('show');
            document.getElementById('customerDrawer').classList.remove('show');
            document.body.style.overflow = '';
        }

        function renderList(rows) {
            const body = document.getElementById('drawerBody');
            if (!rows.length) {
                body.innerHTML = '<div class="drawer-empty">No records found.</div>';
                return;
            }

            const [bg, fg] = avatarStyle[currentCategory] || ['#f0f0f0', '#555'];
            const isInactive = currentCategory === 'inactive';

            let html = '';
            rows.forEach((c, i) => {
                const initials = (c.account_name || '?')
                    .split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);

                let badgeCls = 'badge-total',
                    badgeTxt = 'Customer';
                if (isInactive) {
                    badgeCls = 'badge-inactive';
                    badgeTxt = 'Inactive';
                }

                const metaParts = [];
                if (c.route_name) metaParts.push('📍 ' + c.route_name);
                if (c.mobile_no) metaParts.push('📞 ' + c.mobile_no);
                if (c.last_order_date) metaParts.push('Last order: ' + c.last_order_date);
                if (!c.last_order_date && isInactive) metaParts.push('No orders yet');

                html += `
        <div class="cust-item">
            <div class="cust-avatar" style="background:${bg};color:${fg}">${esc(initials)}</div>
            <div class="cust-info">
                <div class="cust-name">${esc(c.account_name)}</div>
                <div class="cust-meta">${esc(metaParts.join('  ·  '))}</div>
            </div>
            <span class="cust-badge ${badgeCls}">${badgeTxt}</span>
        </div>`;
            });
            body.innerHTML = html;
        }

        function applyFilter() {
            if (currentType === 'list') return;

            const route = document.getElementById('filterRoute').value;
            const brand = document.getElementById('filterBrand').value;
            const search = document.getElementById('filterSearch').value.toLowerCase().trim();

            let filtered = rawData.filter(r => {
                if (route && String(r.batch_no) !== route) return false;
                if (search && !(r.account_name || '').toLowerCase().includes(search)) return false;
                if (brand) {
                    const v = parseFloat(r['brand_' + brand] ?? 0);
                    if (v === 0) return false;
                }
                return true;
            });

            document.getElementById('drawerSubtitle').textContent =
                filtered.length + ' customer' + (filtered.length !== 1 ? 's' : '');

            const visibleBrands = brand ?
                allBrands.filter(b => String(b.id) === brand) :
                allBrands;

            renderBrandTable(filtered, visibleBrands);
        }

        function renderBrandTable(rows, brands) {
            const body = document.getElementById('drawerBody');
            if (!rows.length) {
                body.innerHTML = '<div class="drawer-empty">No records found.</div>';
                return;
            }

            const isAmt = ['business', 'active', 'counter'].includes(currentCategory);

            const colTotals = {};
            brands.forEach(b => {
                colTotals['brand_' + b.id] = 0;
            });
            let grandTotal = 0;
            rows.forEach(r => {
                brands.forEach(b => {
                    colTotals['brand_' + b.id] += parseFloat(r['brand_' + b.id] ?? 0);
                });
                grandTotal += parseFloat(r.row_total ?? 0);
            });

            let html = `<div class="brand-table-wrap"><table class="brand-table"><thead><tr>
        <th>Route</th><th>Customer</th>`;
            brands.forEach(b => {
                html += `<th>${esc(b.name)}</th>`;
            });
            html += `<th style="background:#dce8ff">Total</th></tr></thead><tbody>`;

            rows.forEach(r => {
                html += `<tr><td>${esc(r.route_name ?? '—')}</td><td>${esc(r.account_name)}`;
                if (r.last_order_date) html += `<br><span style="font-size:.65rem;color:#aaa">Last: ${r.last_order_date}</span>`;
                html += `</td>`;
                brands.forEach(b => {
                    const v = parseFloat(r['brand_' + b.id] ?? 0);
                    html += v > 0 ?
                        `<td class="cell-val">${isAmt ? '₹'+fmt(v) : v}</td>` :
                        `<td class="cell-zero">—</td>`;
                });
                const tot = parseFloat(r.row_total ?? 0);
                html += `<td class="col-total-cell">${isAmt ? '₹'+fmt(tot) : tot}</td></tr>`;
            });

            // footer totals
            html += `</tbody><tfoot><tr><td colspan="2"><strong>Total</strong></td>`;
            brands.forEach(b => {
                const v = colTotals['brand_' + b.id];
                html += `<td>${isAmt ? '₹'+fmt(v) : (v || '—')}</td>`;
            });
            html += `<td style="color:#00695C">₹${fmt(grandTotal)}</td></tr></tfoot></table></div>`;

            body.innerHTML = html;
        }

        function fmt(n) {
            n = parseFloat(n) || 0;
            if (n >= 100000) return (n / 100000).toFixed(1) + 'L';
            if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
            return n.toFixed(0);
        }

        function esc(s) {
            return String(s ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeDrawer();
        });
    </script>
</body>

</html>