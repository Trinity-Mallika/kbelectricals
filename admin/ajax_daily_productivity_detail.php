<?php
include("../adminsession.php");

$emp_id = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;
$date   = isset($_POST['date']) ? $_POST['date'] : '';

if ($emp_id <= 0 || empty($date)) {
    echo '<div class="alert alert-danger">Invalid request.</div>';
    exit;
}

$productivity = $obj->executequery("
    SELECT *
    FROM daily_productivity
    WHERE emp_id = '$emp_id'
      AND date = '$date'
    LIMIT 1
");

$dp = !empty($productivity) ? $productivity[0] : [];

$visit_count     = (int)($dp['visit_count'] ?? 0);
$total_counters  = (int)($dp['total_counters'] ?? 0);
$coverage = $total_counters > 0
    ? round(($visit_count / $total_counters) * 100, 1)
    : 0;

$sql = "
    SELECT
        de.entry_id,
        de.account_id,
        de.checkin_time,
        de.checkout_time,
        de.checkout_distance,
        de.remarks,
        de.latitude,
        de.longitude,
        de.latitude_out,
        de.longitude_out,
        de.address,
        de.address_out,
        a.status,
        a.account_name

    FROM daily_entries de

    LEFT JOIN account a
        ON a.account_id = de.account_id

    WHERE de.createdby = '$emp_id'
      AND DATE(de.checkin_time) = '$date'

    ORDER BY de.checkin_time ASC
";

$visits = $obj->executequery($sql);
$active_counters = count(array_filter($visits, function ($row) {
    return $row['status'] == 'active';
}));
?>

<!-- Summary Cards -->
<div class="row g-2 mb-4">

    <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body text-center">
                <small class="text-muted">Total Counters</small>
                <h3 class="mb-0">
                    <?= $total_counters; ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body text-center">
                <small class="text-muted">Active Counters</small>
                <h3 class="mb-0">
                    <?= $active_counters; ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body text-center">
                <small class="text-muted">Visited</small>
                <h3 class="mb-0 text-primary">
                    <?= $visit_count; ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
            <div class="card-body text-center">
                <small class="text-muted">Coverage</small>
                <h3 class="mb-0 <?= $coverage >= 80 ? 'text-success' : ($coverage >= 50 ? 'text-warning' : 'text-danger'); ?>">
                    <?= $coverage; ?>%
                </h3>
            </div>
        </div>
    </div>

</div>


<!-- Visit Details -->

<div class="d-flex justify-content-between align-items-center mb-2">

    <h6 class="mb-0">
        <i class="bi bi-geo-alt"></i>
        Counter Visit Details
    </h6>

    <span class="badge bg-primary">
        <?= count($visits); ?> Visits
    </span>

</div>


<div class="table-responsive">

    <table class="table table-bordered table-sm table-hover align-middle">

        <thead class="table-light">
            <tr>
                <th width="50">#</th>
                <th>Counter</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th class="text-center">Duration</th>
                <th class="text-center">Distance</th>
                <th>Remarks</th>
                <th class="text-center">Location</th>
            </tr>
        </thead>

        <tbody>

            <?php
            if (!empty($visits)) {

                $slno = 1;

                foreach ($visits as $row) {
                    $duration = '-';

                    if (
                        !empty($row['checkin_time']) &&
                        !empty($row['checkout_time'])
                    ) {

                        $start = new DateTime($row['checkin_time']);
                        $end   = new DateTime($row['checkout_time']);

                        $diff = $start->diff($end);

                        $duration = '';

                        if ($diff->h > 0) {
                            $duration .= $diff->h . 'h ';
                        }

                        $duration .= $diff->i . 'm';
                    }
            ?>

                    <tr>

                        <td>
                            <?= $slno++; ?>
                        </td>

                        <!-- Counter -->
                        <td>
                            <strong>
                                <?= htmlspecialchars($row['account_name'] ?? 'Unknown'); ?>
                            </strong>

                            <br>

                            <small class="text-muted">
                                ID: <?= $row['account_id']; ?>
                            </small>
                        </td>


                        <!-- Check In -->
                        <td>

                            <?php if (!empty($row['checkin_time'])) { ?>

                                <strong>
                                    <?= date('h:i A', strtotime($row['checkin_time'])); ?>
                                </strong>

                                <?php if (!empty($row['address'])) { ?>

                                    <br>

                                    <small class="text-muted">
                                        <?= htmlspecialchars($row['address']); ?>
                                    </small>

                                <?php } ?>

                            <?php } else { ?>

                                <span class="text-muted">-</span>

                            <?php } ?>

                        </td>


                        <!-- Check Out -->
                        <td>

                            <?php if (!empty($row['checkout_time'])) { ?>

                                <strong>
                                    <?= date('h:i A', strtotime($row['checkout_time'])); ?>
                                </strong>

                                <?php if (!empty($row['address_out'])) { ?>

                                    <br>

                                    <small class="text-muted">
                                        <?= htmlspecialchars($row['address_out']); ?>
                                    </small>

                                <?php } ?>

                            <?php } else { ?>

                                <span class="badge bg-warning text-dark">
                                    Not Checked Out
                                </span>

                            <?php } ?>

                        </td>


                        <!-- Duration -->
                        <td class="text-center">
                            <?= $duration; ?>
                        </td>


                        <!-- Checkout Distance -->
                        <td class="text-center">

                            <?php if ($row['checkout_distance'] !== null) { ?>

                                <?= number_format($row['checkout_distance'], 2); ?> m

                            <?php } else { ?>

                                -

                            <?php } ?>

                        </td>


                        <!-- Remarks -->
                        <td>
                            <?= !empty($row['remarks'])
                                ? htmlspecialchars($row['remarks'])
                                : '-'; ?>
                        </td>


                        <!-- Location -->
                        <td class="text-center">

                            <?php
                            if (
                                !empty($row['latitude']) &&
                                !empty($row['longitude'])
                            ) {
                            ?>

                                <a
                                    href="https://www.google.com/maps?q=<?= $row['latitude']; ?>,<?= $row['longitude']; ?>"
                                    target="_blank"
                                    class="btn btn-outline-primary btn-sm"
                                    title="View Check-in Location">

                                    <i class="bi bi-geo-alt-fill"></i>

                                </a>

                            <?php } else { ?>

                                -

                            <?php } ?>

                        </td>

                    </tr>

                <?php
                }
            } else {
                ?>

                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">

                        <i class="bi bi-info-circle"></i>
                        No counter visits found for this date.

                    </td>
                </tr>

            <?php } ?>

        </tbody>

    </table>

</div>