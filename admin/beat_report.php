<?php

include("../adminsession.php");

$title = "Beat Report";
$pagename = "beat_report.php";
$module = "Beat Report";
$submodule = "Beat Report";
$companyid = isset($_SESSION['companyid']) ? (int) $_SESSION['companyid'] : 1;
$fromdate = isset($_GET['fromdate'])
    ? trim($_GET['fromdate'])
    : date('Y-m-01');

$todate = isset($_GET['todate'])
    ? trim($_GET['todate'])
    : date('Y-m-d');


$dateRegex = '/^\d{4}-\d{2}-\d{2}$/';

if (!preg_match($dateRegex, $fromdate)) {
    $fromdate = date('Y-m-01');
}

if (!preg_match($dateRegex, $todate)) {
    $todate = date('Y-m-d');
}

$allowedModes = ['all', 'least', 'most', 'active'];

$mode = (
    isset($_GET['mode']) &&
    in_array($_GET['mode'], $allowedModes, true)
)
    ? $_GET['mode']
    : 'all';


$emp_id_filter = isset($_GET['emp_id']) ? (int) $_GET['emp_id'] : 0;

$salesExecutives = $obj->executequery("
    SELECT userid, fullname
    FROM user
    WHERE usertype = 'sales'
      AND companyid = $companyid
    ORDER BY fullname
");

$beatRows = $obj->executequery("
    SELECT
        rp.batch_no,
        r.route_name,

        u.fullname AS assigned_name,
        u.mobile AS assigned_mobile,

        COUNT(DISTINCT rc.account_id) AS total_counters,

        COUNT(DISTINCT CASE
            WHEN te.account_id IS NOT NULL
            THEN rc.account_id
        END) AS active_counters,

        COUNT(DISTINCT te.transaction_id) AS total_orders

    FROM route_plan rp

    JOIN route_counter rc
        ON rc.batch_no = rp.batch_no
        AND rc.companyid = rp.companyid
        AND rc.is_active = 1

    JOIN route r
        ON r.batch_no = rp.batch_no
        AND r.companyid = rp.companyid

    LEFT JOIN user u
        ON u.userid = rp.sales_executive_id

    LEFT JOIN transaction_entry te
        ON te.account_id = rc.account_id
        AND te.type = 'order'
        AND te.is_approved = 1
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        AND te.companyid = $companyid

    WHERE rp.companyid = $companyid
        " . ($emp_id_filter > 0 ? "AND rp.sales_executive_id = $emp_id_filter" : "") . "

    GROUP BY
        rp.batch_no,
        r.route_name,
        u.fullname,
        u.mobile

    ORDER BY total_orders ASC
");


$leastOrders = null;
$mostOrders = null;

foreach ($beatRows as $row) {

    $orders = (int)($row['total_orders'] ?? 0);

    if ($leastOrders === null || $orders < $leastOrders) {
        $leastOrders = $orders;
    }

    if ($mostOrders === null || $orders > $mostOrders) {
        $mostOrders = $orders;
    }
}

$counterRows = $obj->executequery("
    SELECT
        rc.batch_no,

        a.account_id,
        a.account_name,
        a.mobile_no,
        a.owner_name,
        a.o_mobile_no,

        COUNT(te.transaction_id) AS order_count,
        MAX(te.billdate) AS last_order_date,
        COALESCE(SUM(te.grand_total), 0) AS order_value

    FROM route_counter rc

    JOIN account a
        ON a.account_id = rc.account_id

    LEFT JOIN transaction_entry te
        ON te.account_id = rc.account_id
        AND te.type = 'order'
        AND te.is_approved = 1
        AND te.billdate BETWEEN '$fromdate' AND '$todate'
        AND te.companyid = $companyid

    WHERE rc.companyid = $companyid
        AND rc.is_active = 1

    GROUP BY
        rc.batch_no,
        a.account_id,
        a.account_name,
        a.mobile_no,
        a.owner_name,
        a.o_mobile_no
");


$countersByBeat = [];

foreach ($counterRows as $c) {

    $hasMissingDetails = (
        empty($c['mobile_no']) ||
        empty($c['o_mobile_no']) ||
        empty($c['owner_name'])
    );

    $orderCount = (int) $c['order_count'];
    $hasOrder = $orderCount > 0;

    $status = $hasOrder ? 'ok' : 'no_order';


    $countersByBeat[$c['batch_no']][] = [

        'account_id' => $c['account_id'],

        'account_name' => $c['account_name'],

        'mobile_no' => $c['mobile_no'],

        'order_count' => $orderCount,

        'order_value' => (float) $c['order_value'],

        'last_order_date' => $c['last_order_date'],

        'has_order' => $hasOrder,

        'missing_details' => $hasMissingDetails,

        'status' => $status
    ];
}

function fmtDate($d)
{
    return !empty($d)
        ? date('d-m-Y', strtotime($d))
        : '-';
}

function fmtAmt($amt)
{
    return number_format((float) $amt, 2);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <!-- meta tag -->

    <?php include('component/css.php'); ?>

    <style>

        .beat-row {
            cursor: pointer;
        }

        .beat-row:hover {
            background-color: #f8f9fb;
        }

        .beat-detail-row {
            display: none;
        }

        .beat-detail-row.show {
            display: table-row;
        }

        .toggle-icon {
            transition: transform .15s ease;
            display: inline-block;
        }

        .beat-row.expanded .toggle-icon {
            transform: rotate(90deg);
        }

    </style>

    <!-- meta tag -->

</head>


<body class="bg-light">


    <!-- Sidebar -->

    <?php include('component/sidebar.php'); ?>

    <!-- Sidebar Close -->


    <div class="main w-auto">


        <!-- Header -->

        <?php include('component/header.php'); ?>

        <!-- Header Close -->


        <!-- Content -->

        <div class="container-fluid">


            <!-- Filter -->

            <div class="row">

                <div class="col-lg-12 mb-2">

                    <div class="card mt-3">

                        <div class="card-header text-white">

                            <?php echo $module; ?>

                        </div>


                        <div class="card-body">

                            <form>

                                <div class="row">


                                    <div class="col-md-2">

                                        <strong>
                                            <label for="fromdate">
                                                From Date
                                            </label>
                                        </strong>

                                        <input
                                            type="date"
                                            class="form-control form-control-sm"
                                            name="fromdate"
                                            id="fromdate"
                                            value="<?php echo htmlspecialchars($fromdate); ?>"
                                        >

                                    </div>


                                    <div class="col-md-2">

                                        <strong>
                                            <label for="todate">
                                                To Date
                                            </label>
                                        </strong>

                                        <input
                                            type="date"
                                            class="form-control form-control-sm"
                                            name="todate"
                                            id="todate"
                                            value="<?php echo htmlspecialchars($todate); ?>"
                                        >

                                    </div>


                                    <div class="col-md-3">

                                        <strong>
                                            <label for="emp_id">
                                                Sales Executive
                                            </label>
                                        </strong>

                                        <select
                                            class="form-select form-select-sm"
                                            name="emp_id"
                                            id="emp_id"
                                        >

                                            <option value="0">
                                                All
                                            </option>

                                            <?php foreach ($salesExecutives as $exec): ?>

                                                <option
                                                    value="<?= (int) $exec['userid']; ?>"
                                                    <?= $emp_id_filter === (int) $exec['userid'] ? 'selected' : '' ?>
                                                >
                                                    <?= htmlspecialchars($exec['fullname']); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>


                                    <div class="col-md-2">

                                        <strong>
                                            <label for="mode">
                                                Show
                                            </label>
                                        </strong>

                                        <select
                                            class="form-select form-select-sm"
                                            name="mode"
                                            id="mode"
                                        >

                                            <option
                                                value="all"
                                                <?= $mode === 'all' ? 'selected' : '' ?>
                                            >
                                                All Beats
                                            </option>

                                            <option
                                                value="least"
                                                <?= $mode === 'least' ? 'selected' : '' ?>
                                            >
                                                Least Orders
                                            </option>

                                            <option
                                                value="active"
                                                <?= $mode === 'active' ? 'selected' : '' ?>
                                            >
                                                Active
                                            </option>

                                            <option
                                                value="most"
                                                <?= $mode === 'most' ? 'selected' : '' ?>
                                            >
                                                Most Orders
                                            </option>

                                        </select>

                                    </div>


                                    <div class="col-md-3 mt-4">

                                        <input
                                            type="submit"
                                            class="btn btn-primary btn-sm"
                                            name="search"
                                            value="Search"
                                        >

                                        <a
                                            href="<?php echo $pagename; ?>"
                                            class="btn btn-danger btn-sm"
                                            id="reset"
                                        >
                                            Reset
                                        </a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Report -->

            <div class="row">

                <div class="col-lg-12 mb-2">

                    <div class="card mt-1">


                        <div class="card-header text-white">

                            <?php echo $submodule; ?> Record

                            <span class="text-muted small">

                                (
                                <?= date('d-m-Y', strtotime($fromdate)); ?>

                                to

                                <?= date('d-m-Y', strtotime($todate)); ?>
                                )

                            </span>

                        </div>


                        <div class="card-body">


                            <div class="table-responsive">


                                <table
                                    id="example"
                                    class="table table-bordered table-striped table-hover align-middle"
                                >


                                    <thead class="table-primary">

                                        <tr>

                                            <th width="5%">
                                                #
                                            </th>

                                            <th width="18%">
                                                Beat / Route
                                            </th>

                                            <th width="14%">
                                                Assigned To
                                            </th>

                                            <th width="10%">
                                                Total Counters
                                            </th>

                                            <th width="10%">
                                                Active Counters
                                            </th>

                                            <th width="8%">
                                                Orders
                                            </th>

                                            <th width="10%">
                                                Status
                                            </th>

                                            <th width="25%">
                                                Reason
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                        <?php

                                        $slno = 1;


                                        foreach ($beatRows as $b) {
                                            $total = (int)($b['total_counters'] ?? 0);

                                            $active = (int)($b['active_counters'] ?? 0);

                                            $orders = (int)($b['total_orders'] ?? 0);

                                            if ($total === 0) {
                                                continue;
                                            }

                                            $pct = round(
                                                ($active / $total) * 100,
                                                1
                                            );

                                            $isLeast = (
                                                $leastOrders !== null &&
                                                $orders === $leastOrders
                                            );

                                            $isMost = (
                                                $mostOrders !== null &&
                                                $orders === $mostOrders
                                            );


                                            if ($mode === 'least' && !$isLeast) {
                                                continue;
                                            }

                                            if ($mode === 'most' && !$isMost) {
                                                continue;
                                            }

                                            if ($mode === 'active' && ($isLeast || $isMost)) {
                                                continue;
                                            }
                                            $counters =
                                                $countersByBeat[$b['batch_no']] ?? [];

                                            $noOrderCount = 0;

                                            $missingDetails = 0;


                                            foreach ($counters as $c) {

                                                if ($c['status'] === 'no_order') {
                                                    $noOrderCount++;
                                                }

                                                if ($c['missing_details']) {
                                                    $missingDetails++;
                                                }
                                            }


                                            $reasons = [];


                                            if ($isLeast) {

                                                $reasons[] =
                                                    'Lowest order count in selected date range: '
                                                    . $orders
                                                    . ' order'
                                                    . ($orders == 1 ? '' : 's');
                                            }


                                            if ($noOrderCount > 0) {

                                                $reasons[] =
                                                    $noOrderCount
                                                    . ' counter'
                                                    . ($noOrderCount == 1 ? '' : 's')
                                                    . ' with no order';
                                            }


                                            if ($missingDetails > 0) {

                                                $reasons[] =
                                                    $missingDetails
                                                    . ' missing details';
                                            }


                                            if ($isMost) {

                                                $reasons[] =
                                                    'Highest order count in selected date range: '
                                                    . $orders
                                                    . ' order'
                                                    . ($orders == 1 ? '' : 's');
                                            }


                                            $reasonHtml =
                                                !empty($reasons)
                                                    ? implode('<br>', $reasons)
                                                    : '<span class="text-muted">—</span>';


                                            if ($isLeast) {

                                                $statusBadge =
                                                    '<span class="badge bg-danger">
                                                        Least Active
                                                    </span>';

                                            } elseif ($isMost) {

                                                $statusBadge =
                                                    '<span class="badge bg-success">
                                                        Most Active
                                                    </span>';

                                            } else {

                                                $statusBadge =
                                                    '<span class="badge bg-primary">
                                                        Active
                                                    </span>';
                                            }


                                            $rowId =
                                                'beat_' .
                                                preg_replace(
                                                    '/[^a-zA-Z0-9_]/',
                                                    '_',
                                                    $b['batch_no']
                                                );

                                        ?>


                                            <tr
                                                class="beat-row"
                                                onclick="toggleBeat(
                                                    '<?= htmlspecialchars($rowId, ENT_QUOTES); ?>',
                                                    this
                                                )"
                                            >


                                                <td>

                                                    <?= $slno++; ?>

                                                </td>


                                                <td>

                                                    <strong class="text-primary">

                                                        <?= htmlspecialchars(
                                                            $b['route_name']
                                                        ); ?>

                                                    </strong>

                                                    <br>

                                                    <small class="text-muted">

                                                        Batch No.:
                                                        <?= htmlspecialchars(
                                                            $b['batch_no']
                                                        ); ?>

                                                    </small>

                                                </td>


                                                <td>

                                                    <?php if (!empty($b['assigned_name'])): ?>

                                                        <?= htmlspecialchars(
                                                            $b['assigned_name']
                                                        ); ?>

                                                        <br>

                                                        <small class="text-muted">

                                                            <?= htmlspecialchars(
                                                                $b['assigned_mobile']
                                                            ); ?>

                                                        </small>

                                                    <?php else: ?>

                                                        <span class="text-muted">
                                                            Unassigned
                                                        </span>

                                                    <?php endif; ?>

                                                </td>


                                                <td>

                                                    <?= $total; ?>

                                                </td>


                                                <td>

                                                    <?= $active; ?>

                                                    <small class="text-muted">

                                                        (<?= $pct; ?>%)

                                                    </small>

                                                </td>


                                                <td>

                                                    <strong>

                                                        <?= $orders; ?>

                                                    </strong>

                                                </td>


                                                <td>

                                                    <?= $statusBadge; ?>

                                                </td>


                                                <td>

                                                    <small>

                                                        <?= $reasonHtml; ?>

                                                    </small>

                                                    <i
                                                        class="bi bi-chevron-right toggle-icon float-end"
                                                    ></i>

                                                </td>


                                            </tr>


                                            <!-- Beat Details -->

                                            <tr
                                                class="beat-detail-row"
                                                id="<?= htmlspecialchars(
                                                    $rowId,
                                                    ENT_QUOTES
                                                ); ?>"
                                            >

                                                <td
                                                    colspan="8"
                                                    class="p-0"
                                                >

                                                    <div
                                                        class="p-2 bg-light border-top"
                                                    >


                                                        <?php if (empty($counters)): ?>


                                                            <div
                                                                class="text-muted small py-2 px-2"
                                                            >

                                                                No counters found
                                                                for this beat.

                                                            </div>


                                                        <?php else: ?>


                                                            <table
                                                                class="table table-sm table-bordered mb-0 bg-white"
                                                            >


                                                                <thead class="table-light">

                                                                    <tr>

                                                                        <th width="5%">
                                                                            #
                                                                        </th>

                                                                        <th>
                                                                            Counter
                                                                        </th>

                                                                        <th>
                                                                            Mobile No.
                                                                        </th>

                                                                        <th class="text-center">
                                                                            Order Count
                                                                        </th>

                                                                        <th class="text-end">
                                                                            Order Value
                                                                        </th>

                                                                        <th>
                                                                            Last Order Date
                                                                        </th>

                                                                        <th class="text-center">
                                                                            Basic Details
                                                                        </th>

                                                                        <th class="text-center">
                                                                            Status
                                                                        </th>

                                                                    </tr>

                                                                </thead>


                                                                <tbody>


                                                                    <?php

                                                                    $ci = 1;

                                                                    foreach ($counters as $c):

                                                                    ?>


                                                                        <tr>


                                                                            <td>

                                                                                <?= $ci++; ?>

                                                                            </td>


                                                                            <td>

                                                                                <?= htmlspecialchars(
                                                                                    $c['account_name']
                                                                                ); ?>

                                                                            </td>


                                                                            <td>

                                                                                <?= htmlspecialchars(
                                                                                    $c['mobile_no']
                                                                                ); ?>

                                                                            </td>


                                                                            <td class="text-center">

                                                                                <?= $c['order_count']; ?>

                                                                            </td>


                                                                            <td class="text-end">

                                                                                <?= fmtAmt(
                                                                                    $c['order_value']
                                                                                ); ?>

                                                                            </td>


                                                                            <td>

                                                                                <?= fmtDate(
                                                                                    $c['last_order_date']
                                                                                ); ?>

                                                                            </td>


                                                                            <td class="text-center">


                                                                                <?php if ($c['missing_details']): ?>


                                                                                    <span
                                                                                        class="badge bg-warning text-dark"
                                                                                    >

                                                                                        Incomplete

                                                                                    </span>


                                                                                <?php else: ?>


                                                                                    <span
                                                                                        class="badge bg-light text-muted border"
                                                                                    >

                                                                                        Complete

                                                                                    </span>


                                                                                <?php endif; ?>


                                                                            </td>


                                                                            <td class="text-center">


                                                                                <?php if ($c['status'] === 'no_order'): ?>


                                                                                    <span
                                                                                        class="badge bg-danger"
                                                                                    >

                                                                                        No Order

                                                                                    </span>


                                                                                <?php else: ?>


                                                                                    <span
                                                                                        class="badge bg-success"
                                                                                    >

                                                                                        Ordered

                                                                                    </span>


                                                                                <?php endif; ?>


                                                                            </td>


                                                                        </tr>


                                                                    <?php endforeach; ?>


                                                                </tbody>

                                                            </table>


                                                        <?php endif; ?>


                                                    </div>

                                                </td>

                                            </tr>


                                        <?php } ?>


                                    </tbody>

                                </table>


                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>


    <!-- Content close -->


</body>


<!-- script tag -->

<?php include('component/script.php'); ?>


<script>

    function toggleBeat(rowId, headerRow) {

        var row = document.getElementById(rowId);

        if (!row) {
            return;
        }

        row.classList.toggle('show');

        headerRow.classList.toggle('expanded');
    }

</script>


</html>