<?php

include("../adminsession.php");

$title    = "Attendance Report";
$pagename = "attendance-report.php";

/* ================= DELETE SINGLE ================= */

if (isset($_GET['del'])) {
  $attendance_id = intval($_GET['del']);

  $obj->delete_record(
    "bni_attendance",
    ["attendance_id" => $attendance_id]
  );

  // Preserve filter on redirect
  $qs = $_SERVER['QUERY_STRING'] ?? '';
  parse_str($qs, $params);
  unset($params['del']);
  $retain = http_build_query($params);
  echo "<script>location='$pagename" . ($retain ? "?$retain" : "") . "&msg=deleted'</script>";
  exit;
}

/* ================= DELETE BULK ================= */

if (isset($_POST['bulk_delete'])) {
  if (!empty($_POST['attendance_ids'])) {
    foreach ($_POST['attendance_ids'] as $id) {
      $attendance_id = intval($id);

      $obj->delete_record(
        "bni_attendance",
        ["attendance_id" => $attendance_id]
      );
    }

    echo "<script>location='$pagename?msg=bulk_deleted'</script>";
    exit;
  }
}

/* ================= FILTERS ================= */

$shop_id   = intval($_GET['shop_id'] ?? 0);
$filter_date = $_GET['date'] ?? date('Y-m-d');

// Build WHERE clause
$conditions = [];
if ($shop_id > 0) {
  $conditions[] = "a.shop_id = '$shop_id'";
}
if ($filter_date != '') {
  $conditions[] = "DATE(a.scan_time) = '" . $obj->test_input($filter_date) . "'";
}
$where = "";
if (count($conditions) > 0) {
  $where = " WHERE " . implode(' AND ', $conditions);
}

/* ================= DATA ================= */

$rows = $obj->executequery("
    SELECT
        a.*,
        m.member_name,
        m.mobile,
        m.designation,
        m.shift_id,
        mt.title,
        s.chapter_name AS shop_name
    FROM bni_attendance AS a
    JOIN bni_members AS m ON m.member_id = a.member_id
    JOIN bni_meetings AS mt ON mt.meeting_id = a.meeting_id
    LEFT JOIN chapter_master AS s ON s.chapter_id = a.shop_id
    $where
    ORDER BY a.scan_time DESC
");

// Compute summary stats
$totalSessions  = count($rows);
$distinctEmployees = count(array_unique(array_column($rows, 'member_id')));
$totalWorkedSec = 0;
$gpsCount = 0;
$manualCount = 0;

foreach ($rows as $r) {
  if (!empty($r['out_time'])) {
    $totalWorkedSec += strtotime($r['out_time']) - strtotime($r['scan_time']);
  }
  if ($r['type'] === 'GPS') $gpsCount++;
  else $manualCount++;
}

function formatDuration($sec) {
  $h = floor($sec / 3600);
  $m = floor(($sec % 3600) / 60);
  if ($h > 0) return "{$h}h {$m}m";
  return "{$m}m";
}

// Group by employee for per-employee summary
$employeeSummary = [];
foreach ($rows as $r) {
  $mid = $r['member_id'];
  if (!isset($employeeSummary[$mid])) {
    $employeeSummary[$mid] = [
      'name' => $r['member_name'],
      'shop_name' => $r['shop_name'],
      'shift_id' => $r['shift_id'] ?? null,
      'sessions' => 0,
      'worked' => 0,
      'first_in' => null,
      'last_out' => null,
      'has_open' => false,
    ];
  }
  $employeeSummary[$mid]['sessions']++;
  $inTs = strtotime($r['scan_time']);
  if ($employeeSummary[$mid]['first_in'] === null || $inTs < $employeeSummary[$mid]['first_in']) {
    $employeeSummary[$mid]['first_in'] = $inTs;
  }
  if (!empty($r['out_time'])) {
    $outTs = strtotime($r['out_time']);
    $employeeSummary[$mid]['worked'] += ($outTs - $inTs);
    if ($employeeSummary[$mid]['last_out'] === null || $outTs > $employeeSummary[$mid]['last_out']) {
      $employeeSummary[$mid]['last_out'] = $outTs;
    }
  } else {
    $employeeSummary[$mid]['has_open'] = true;
  }
}

$shops = $obj->executequery("
    SELECT chapter_id, chapter_name
    FROM chapter_master
    WHERE status = 1
    ORDER BY chapter_name
");

?>

<!DOCTYPE html>
<html>

<head>
  <?php include('component/css.php'); ?>
  <style>
    .summary-card {
      border: 0; border-radius: 14px; color: #fff; overflow: hidden;
    }
    .summary-card .val { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .summary-card .lbl { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; opacity: .85; }
    .sg1 { background: linear-gradient(135deg, #06163a, #287ab1); }
    .sg2 { background: linear-gradient(135deg, #14213d, #00a8e8); }
    .sg3 { background: linear-gradient(135deg, #0b2545, #2ec4b6); }
    .sg4 { background: linear-gradient(135deg, #1d3557, #457b9d); }
    .duration-badge {
      font-family: monospace; font-size: .8rem;
    }
    .open-badge { animation: pulse 2s infinite; }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .6; }
    }
  </style>
</head>

<body class="bg-light">

  <?php include('component/sidebar.php'); ?>

  <div class="main w-auto">

    <?php include('component/header.php'); ?>

    <div class="container-fluid py-3">

      <!-- SUMMARY CARDS -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card summary-card sg1 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="val"><?php echo $distinctEmployees; ?></div>
                <div class="lbl">Present Employees</div>
              </div>
              <i class="bi bi-people" style="font-size:2rem;opacity:.5"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card summary-card sg2 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="val"><?php echo $totalSessions; ?></div>
                <div class="lbl">Total Sessions</div>
              </div>
              <i class="bi bi-list-check" style="font-size:2rem;opacity:.5"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card summary-card sg3 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="val"><?php echo formatDuration($totalWorkedSec); ?></div>
                <div class="lbl">Total Worked</div>
              </div>
              <i class="bi bi-stopwatch" style="font-size:2rem;opacity:.5"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card summary-card sg4 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="val"><?php echo $gpsCount; ?> / <?php echo $manualCount; ?></div>
                <div class="lbl">GPS / Manual</div>
              </div>
              <i class="bi bi-geo-alt" style="font-size:2rem;opacity:.5"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <h5 class="mb-0">
              Attendance Report
              <small class="text-muted" style="font-size:.6em;">
                one row per employee — click "Sessions" to see details
              </small>
            </h5>

            <div>
              <button class="btn btn-success"
                onclick="exportTableToExcel('reportTable','attendance-report')">
                <i class="bi bi-download"></i>
                Export Excel
              </button>
            </div>

          </div>

          <!-- FILTERS -->
          <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="pagename" value="<?php echo $pagename; ?>">

            <div class="col-md-3">
              <label class="form-label small fw-bold">Date</label>
              <input type="date" name="date" class="form-control"
                value="<?php echo htmlspecialchars($filter_date); ?>"
                onchange="this.form.submit()">
            </div>

            <div class="col-md-3">
              <label class="form-label small fw-bold">Shop</label>
              <select name="shop_id" class="form-select" onchange="this.form.submit()">
                <option value="0">All Shops</option>
                <?php foreach ($shops as $s) { ?>
                  <option value="<?php echo $s['chapter_id']; ?>"
                    <?php echo ($shop_id == $s['chapter_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($s['chapter_name']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
              <a href="<?php echo $pagename; ?>" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-clockwise"></i> Reset Filters
              </a>
            </div>
          </form>

          <!-- PER-EMPLOYEE SUMMARY TABLE (one row per employee) -->
          <div class="table-responsive">
            <table id="reportTable"
              class="table table-bordered table-striped align-middle datatable">
              <thead class="table-dark">
                <tr>
                  <th>S.No</th>
                  <th>Employee</th>
                  <th>Shop</th>
                  <th class="text-center">Sessions</th>
                  <th>First IN</th>
                  <th>Last OUT</th>
                  <th>Worked</th>
                  <th>Status</th>
                  <th>Late / Early</th>
                  <th>Day Status</th>
                  <th width="160">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;
                if (empty($employeeSummary)) {
                  echo '<tr><td colspan="11" class="text-center text-muted py-4">
                          <i class="bi bi-inbox" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                          No attendance records found for the selected filters.
                        </td></tr>';
                }

                foreach ($employeeSummary as $mid => $s) {
                  // Per-employee shift for late/early calc
                  $empShift = $obj->getShift($s['shift_id'] ?? null);
                  $empShiftStartTs = strtotime($filter_date . ' ' . $empShift['start_time']);
                  $empShiftEndTs   = strtotime($filter_date . ' ' . $empShift['end_time']);
                  $empGraceInSec   = (int)(($empShift['grace_in_minutes']  ?? $empShift['grace_minutes']) ?? 0) * 60;
                  $empGraceOutSec  = (int)(($empShift['grace_out_minutes'] ?? $empShift['grace_minutes']) ?? 0) * 60;
                  $empExpectedWorkSec = (float)($empShift['expected_work_hours'] ?? 0) * 3600;

                  // Per-employee late/early calc (split grace IN/OUT)
                  $empLateSec  = ($s['first_in'] && $s['first_in'] > ($empShiftStartTs + $empGraceInSec))
                               ? ($s['first_in'] - ($empShiftStartTs + $empGraceInSec)) : 0;
                  $empEarlySec = ($s['last_out'] && $s['last_out'] < ($empShiftEndTs - $empGraceOutSec))
                               ? (($empShiftEndTs - $empGraceOutSec) - $s['last_out']) : 0;
                  $empShortSec = (!$s['has_open'] && $empExpectedWorkSec > 0 && $s['worked'] < $empExpectedWorkSec)
                               ? (int)($empExpectedWorkSec - $s['worked']) : 0;

                  // Day status for salary
                  if ($s['has_open']) {
                      $empDayStatus = 'pending';
                  } elseif ($empExpectedWorkSec > 0 && $s['worked'] < $empExpectedWorkSec) {
                      $empDayStatus = 'incomplete';
                  } else {
                      $empDayStatus = 'complete';
                  }
                ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td>
                      <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                    </td>
                    <td>
                      <?php if (!empty($s['shop_name'])) { ?>
                        <span class="badge bg-primary">
                          <i class="bi bi-shop"></i>
                          <?php echo htmlspecialchars($s['shop_name']); ?>
                        </span>
                      <?php } else { ?>
                        <span class="text-muted">—</span>
                      <?php } ?>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-secondary"><?php echo $s['sessions']; ?></span>
                    </td>
                    <td>
                      <?php echo $s['first_in'] ? date('h:i A', $s['first_in']) : '<span class="text-muted">—</span>'; ?>
                    </td>
                    <td>
                      <?php if ($s['last_out']) { ?>
                        <?php echo date('h:i A', $s['last_out']); ?>
                      <?php } elseif ($s['has_open']) { ?>
                        <span class="badge bg-success open-badge">
                          <i class="bi bi-hourglass-split"></i> Open
                        </span>
                      <?php } else { ?>
                        <span class="text-muted">—</span>
                      <?php } ?>
                    </td>
                    <td>
                      <span class="badge bg-info text-dark duration-badge">
                        <?php echo formatDuration($s['worked']); ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($s['has_open']) { ?>
                        <span class="badge bg-success">Checked IN</span>
                      <?php } else { ?>
                        <span class="badge bg-secondary">Checked OUT</span>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if ($empLateSec > 0) { ?>
                        <span class="badge bg-danger-subtle text-danger" style="font-size:.65rem">
                          Late <?php echo formatDuration($empLateSec); ?>
                        </span>
                      <?php } ?>
                      <?php if ($empEarlySec > 0 && !$s['has_open']) { ?>
                        <span class="badge bg-warning-subtle text-warning" style="font-size:.65rem">
                          Early <?php echo formatDuration($empEarlySec); ?>
                        </span>
                      <?php } ?>
                      <?php if ($empShortSec > 0) { ?>
                        <span class="badge bg-danger-subtle text-danger" style="font-size:.65rem"
                              title="Expected <?php echo $empShift['expected_work_hours']; ?>h, short by <?php echo formatDuration($empShortSec); ?>">
                          Short <?php echo formatDuration($empShortSec); ?>
                        </span>
                      <?php } ?>
                      <?php if ($empLateSec == 0 && $empEarlySec == 0 && $empShortSec == 0) { ?>
                        <span class="badge bg-success-subtle text-success" style="font-size:.65rem">On time</span>
                      <?php } ?>
                    </td>
                    <td>
                      <?php
                      $dsBadges = [
                          'complete'   => '<span class="badge bg-success" style="font-size:.65rem">Complete (paid)</span>',
                          'incomplete' => '<span class="badge bg-warning text-dark" style="font-size:.65rem">Incomplete (unpaid)</span>',
                          'pending'    => '<span class="badge bg-info text-dark" style="font-size:.65rem">Pending</span>',
                      ];
                      echo $dsBadges[$empDayStatus] ?? '<span class="text-muted">—</span>';
                      ?>
                    </td>
                    <td>
                      <a href="attendance-sessions.php?member_id=<?php echo $mid; ?>&date=<?php echo urlencode($filter_date); ?>&shop_id=<?php echo $shop_id; ?>"
                         class="btn btn-primary btn-sm" title="View all sessions">
                        <i class="bi bi-list-check"></i> Sessions
                      </a>
                      <a href="attendance-calendar.php?member_id=<?php echo $mid; ?>&month=<?php echo substr($filter_date, 0, 7); ?>"
                         class="btn btn-outline-secondary btn-sm" title="Calendar view">
                        <i class="bi bi-calendar3"></i>
                      </a>
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

  <?php include('component/script.php'); ?>

  <script>
    function exportTableToExcel(tableID, filename = '') {
      var dataType = 'application/vnd.ms-excel';
      var tableSelect = document.getElementById(tableID);
      var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

      filename = filename ? filename + '.xls' : 'excel_data.xls';

      var downloadLink = document.createElement("a");
      document.body.appendChild(downloadLink);

      downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
      downloadLink.download = filename;
      downloadLink.click();
    }
  </script>

</body>

</html>
