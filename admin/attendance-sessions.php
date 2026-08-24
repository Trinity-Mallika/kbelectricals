<?php

include("../adminsession.php");

$title    = "Employee Sessions";
$pagename = "attendance-sessions.php";

/* =========================
   INPUTS  (member_id required, date required)
========================= */

$member_id = intval($_GET['member_id'] ?? 0);
$date      = $_GET['date'] ?? date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  $date = date('Y-m-d');
}

/* =========================
   DELETE (single session)
========================= */

if (isset($_GET['del'])) {
  $attendance_id = intval($_GET['del']);
  $obj->delete_record("bni_attendance", ["attendance_id" => $attendance_id]);
  $retain = "member_id=$member_id&date=" . urlencode($date);
  echo "<script>location='$pagename?$retain&msg=deleted'</script>";
  exit;
}

/* =========================
   EDIT SESSION
========================= */

if (isset($_POST['edit_session'])) {
  $attendance_id = intval($_POST['attendance_id']);
  $scan_time     = trim($_POST['scan_time']);
  $out_time      = trim($_POST['out_time']);
  $type          = trim($_POST['type']);

  // Validate datetime format
  if (!empty($scan_time) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $scan_time)) {
    $scan_time .= ':00';
  }
  if (!empty($out_time) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $out_time)) {
    $out_time .= ':00';
  }

  $fields = [
    'scan_time' => $scan_time,
    'out_time'  => !empty($out_time) ? $out_time : null,
    'type'      => $type,
  ];

  $obj->update_record(
    "bni_attendance",
    ["attendance_id" => $attendance_id],
    $fields
  );

  $retain = "member_id=$member_id&date=" . urlencode($date);
  echo "<script>location='$pagename?$retain&msg=updated'</script>";
  exit;
}

/* =========================
   FETCH EMPLOYEE
========================= */

$emp = $obj->executequery("
    SELECT m.*, c.chapter_name AS shop_name
    FROM bni_members m
    LEFT JOIN chapter_master c ON c.chapter_id = m.chapter_id
    WHERE m.member_id = '$member_id'
    LIMIT 1
")[0] ?? null;

if (!$emp) {
  // Invalid member_id — bounce back to report
  echo "<script>location='attendance-report.php'</script>";
  exit;
}

/* =========================
   FETCH ALL SESSIONS for this employee+date
========================= */

$rows = $obj->executequery("
    SELECT a.*, s.chapter_name AS shop_name
    FROM bni_attendance a
    LEFT JOIN chapter_master s ON s.chapter_id = a.shop_id
    WHERE a.member_id = '$member_id'
      AND DATE(a.scan_time) = '" . $obj->escape($date) . "'
    ORDER BY a.attendance_id ASC
");

/* =========================
   COMPUTE AGGREGATES
========================= */

$hasOpen   = false;
$workedSec = 0;
$firstInTs = null;
$lastOutTs = null;

foreach ($rows as $r) {
  $inTs  = strtotime($r['scan_time']);
  $outTs = $r['out_time'] ? strtotime($r['out_time']) : null;
  if ($firstInTs === null) $firstInTs = $inTs;
  if ($outTs !== null) {
    $lastOutTs  = $outTs;
    $workedSec += ($outTs - $inTs);
  } else {
    $hasOpen = true;
  }
}

/* =========================
   SHIFT for late/early (per employee)
======================== */

$empMember = $obj->select_record('bni_members', ['member_id' => $member_id]);
$shift = $obj->getShift($empMember['shift_id'] ?? null);
$shiftStartTs = strtotime($date . ' ' . $shift['start_time']);
$shiftEndTs   = strtotime($date . ' ' . $shift['end_time']);
$graceInSec   = (int)(($shift['grace_in_minutes']  ?? $shift['grace_minutes']) ?? 0) * 60;
$graceOutSec  = (int)(($shift['grace_out_minutes'] ?? $shift['grace_minutes']) ?? 0) * 60;
$expectedWorkSec = (float)($shift['expected_work_hours'] ?? 0) * 3600;

$lateSec  = ($firstInTs && $firstInTs > ($shiftStartTs + $graceInSec))
  ? ($firstInTs - ($shiftStartTs + $graceInSec))
  : 0;
$earlySec = ($lastOutTs && $lastOutTs < ($shiftEndTs - $graceOutSec))
  ? (($shiftEndTs - $graceOutSec) - $lastOutTs)
  : 0;
$shortSec = (!$hasOpen && $expectedWorkSec > 0 && $workedSec < $expectedWorkSec)
  ? (int)($expectedWorkSec - $workedSec)
  : 0;

// Day status for salary
$dayStatus = 'absent';
if (!empty($rows)) {
  if ($hasOpen) {
    $dayStatus = 'pending';
  } elseif ($expectedWorkSec > 0 && $workedSec < $expectedWorkSec) {
    $dayStatus = 'incomplete';
  } else {
    $dayStatus = 'complete';
  }
}

/* =========================
   HELPER
========================= */

function formatDuration($sec)
{
  $h = floor($sec / 3600);
  $m = floor(($sec % 3600) / 60);
  if ($h > 0) return "{$h}h {$m}m";
  return "{$m}m";
}

/* =========================
   PREV / NEXT DAY NAVIGATION
========================= */

$prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
$today    = date('Y-m-d');

?>

<!DOCTYPE html>
<html>

<head>
  <?php include('component/css.php'); ?>
  <style>
    .emp-card {
      background: linear-gradient(135deg, #06163a, #287ab1);
      color: #fff;
      border-radius: 16px;
      padding: 16px 20px;
    }

    .emp-card h4 {
      margin: 0;
      font-weight: 700;
    }

    .emp-card .sub {
      opacity: .85;
      font-size: 13px;
      margin-top: 3px;
    }

    .stat-mini {
      background: rgba(255, 255, 255, .13);
      border-radius: 10px;
      padding: 10px 14px;
      text-align: center;
    }

    .stat-mini .v {
      font-size: 1.1rem;
      font-weight: 700;
      line-height: 1;
    }

    .stat-mini .l {
      font-size: .65rem;
      opacity: .8;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-top: 4px;
    }

    .session-row td {
      vertical-align: top;
    }

    .session-num {
      background: #e2e8f0;
      color: #475569;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 12px;
    }

    .session-num.open {
      background: #16a34a;
      color: #fff;
    }

    .time-in {
      color: #16a34a;
      font-weight: 600;
    }

    .time-out {
      color: #f59e0b;
      font-weight: 600;
    }

    .time-out.open {
      color: #16a34a;
    }

    .dur-badge {
      font-family: monospace;
      font-size: .8rem;
      background: rgba(40, 122, 177, .12);
      color: #1a56a0;
      padding: 3px 8px;
      border-radius: 50px;
      font-weight: 700;
    }

    .open-badge {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: .6;
      }
    }
  </style>
</head>

<body class="bg-light">

  <?php include('component/sidebar.php'); ?>

  <div class="main w-auto">

    <?php include('component/header.php'); ?>

    <div class="container-fluid py-3">

      <!-- BACK LINK + DATE NAV -->
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <a href="attendance-report.php?date=<?php echo urlencode($date); ?>&shop_id=<?php echo intval($_GET['shop_id'] ?? 0); ?>"
          class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left"></i> Back to Report
        </a>

        <div class="d-flex gap-2 align-items-center">
          <a href="?member_id=<?php echo $member_id; ?>&date=<?php echo $prevDate; ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-left"></i>
          </a>
          <span class="form-control form-control-sm text-center" style="min-width:160px;">
            <?php echo date('D, d M Y', strtotime($date)); ?>
          </span>
          <a href="?member_id=<?php echo $member_id; ?>&date=<?php echo $nextDate; ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-right"></i>
          </a>
          <?php if ($date !== $today) { ?>
            <a href="?member_id=<?php echo $member_id; ?>&date=<?php echo $today; ?>" class="btn btn-outline-primary btn-sm">
              Today
            </a>
          <?php } ?>
        </div>
      </div>

      <!-- EMPLOYEE INFO CARD -->
      <div class="emp-card shadow-sm mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h4><?php echo htmlspecialchars($emp['member_name']); ?></h4>
            <div class="sub">
              <?php
              $parts = [];
              if (!empty($emp['designation'])) $parts[] = htmlspecialchars($emp['designation']);
              if (!empty($emp['shop_name']))   $parts[] = '<i class="bi bi-shop"></i> ' . htmlspecialchars($emp['shop_name']);
              if (!empty($emp['mobile']))      $parts[] = '<i class="bi bi-phone"></i> ' . htmlspecialchars($emp['mobile']);
              echo implode(' · ', $parts);
              ?>
            </div>
          </div>

          <?php if (!empty($rows)) { ?>
            <div class="d-flex gap-2 flex-wrap">
              <div class="stat-mini">
                <div class="v"><?php echo count($rows); ?></div>
                <div class="l">Sessions</div>
              </div>
              <div class="stat-mini">
                <div class="v"><?php echo formatDuration($workedSec); ?></div>
                <div class="l">Worked</div>
              </div>
              <div class="stat-mini">
                <div class="v"><?php echo $firstInTs ? date('h:i A', $firstInTs) : '—'; ?></div>
                <div class="l">First IN</div>
              </div>
              <div class="stat-mini">
                <div class="v"><?php echo $lastOutTs ? date('h:i A', $lastOutTs) : ($hasOpen ? 'Open' : '—'); ?></div>
                <div class="l">Last OUT</div>
              </div>
            </div>
          <?php } ?>
        </div>

        <?php
        // Day status badge (for salary)
        $dayStatusBadges = [
          'complete'   => '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Day Complete (paid)</span>',
          'incomplete' => '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill"></i> Day Incomplete (unpaid — worked ' . formatDuration($workedSec) . ' / ' . number_format((float)$shift['expected_work_hours'], 1) . 'h)</span>',
          'pending'    => '<span class="badge bg-info text-dark"><i class="bi bi-hourglass-split"></i> Day Pending (open session)</span>',
        ];
        if (!empty($rows) && isset($dayStatusBadges[$dayStatus])) {
          echo '<div class="mt-2" style="font-size:12px;">' . $dayStatusBadges[$dayStatus] . '</div>';
        }
        ?>

        <?php if (!empty($rows) && ($lateSec > 0 || $earlySec > 0 || $shortSec > 0)) { ?>
          <div class="mt-2" style="font-size:12px;">
            <?php if ($lateSec > 0) { ?>
              <span class="badge bg-danger">
                <i class="bi bi-clock-history"></i> Late <?php echo formatDuration($lateSec); ?>
              </span>
            <?php } ?>
            <?php if ($earlySec > 0 && !$hasOpen) { ?>
              <span class="badge bg-warning text-dark">
                <i class="bi bi-box-arrow-right"></i> Early <?php echo formatDuration($earlySec); ?>
              </span>
            <?php } ?>
            <?php if ($shortSec > 0) { ?>
              <span class="badge bg-danger">
                <i class="bi bi-hourglass-bottom"></i>
                Short by <?php echo formatDuration($shortSec); ?>
                <small style="opacity:.8">(expected <?php echo number_format((float)$shift['expected_work_hours'], 2); ?>h)</small>
              </span>
            <?php } ?>
          </div>
        <?php } ?>
      </div>

      <!-- SESSIONS TABLE -->
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
              <i class="bi bi-list-check"></i>
              All Sessions
              <small class="text-muted" style="font-size:.7em;">
                on <?php echo date('d M Y', strtotime($date)); ?>
              </small>
            </h5>
            <a href="attendance-calendar.php?member_id=<?php echo $member_id; ?>&month=<?php echo substr($date, 0, 7); ?>"
              class="btn btn-outline-primary btn-sm">
              <i class="bi bi-calendar3"></i> View Calendar
            </a>
          </div>

          <?php if (empty($rows)) { ?>
            <div class="text-center py-5 text-muted">
              <i class="bi bi-inbox" style="font-size:3rem; opacity:.3; display:block; margin-bottom:10px;"></i>
              <h6>No attendance recorded on this day</h6>
              <small>This employee did not mark any IN/OUT on <?php echo date('d M Y', strtotime($date)); ?></small>
            </div>
          <?php } else { ?>

            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                  <tr>
                    <th width="50">#</th>
                    <th>Shop</th>
                    <th>IN Time</th>
                    <th>OUT Time</th>
                    <th>Duration</th>
                    <th>Type</th>
                    <th>GPS / Notes</th>
                    <th width="120">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $i => $r) {
                    $inTs  = strtotime($r['scan_time']);
                    $outTs = $r['out_time'] ? strtotime($r['out_time']) : null;
                    $isOpen = !$outTs;
                    $durSec = $outTs ? ($outTs - $inTs) : 0;
                  ?>
                    <tr class="session-row">
                      <td>
                        <span class="session-num <?php echo $isOpen ? 'open' : ''; ?>">
                          <?php echo $i + 1; ?>
                        </span>
                      </td>
                      <td>
                        <?php if (!empty($r['shop_name'])) { ?>
                          <span class="badge bg-primary">
                            <i class="bi bi-shop"></i>
                            <?php echo htmlspecialchars($r['shop_name']); ?>
                          </span>
                        <?php } else { ?>
                          <span class="text-muted">—</span>
                        <?php } ?>
                      </td>
                      <td>
                        <div class="time-in">
                          <i class="bi bi-box-arrow-in-right"></i>
                          <?php echo date('h:i:s A', $inTs); ?>
                        </div>
                        <small class="text-muted"><?php echo date('d M Y', $inTs); ?></small>
                      </td>
                      <td>
                        <?php if ($isOpen) { ?>
                          <span class="badge bg-success open-badge">
                            <i class="bi bi-hourglass-split"></i> Open
                          </span>
                        <?php } else { ?>
                          <div class="time-out">
                            <i class="bi bi-box-arrow-right"></i>
                            <?php echo date('h:i:s A', $outTs); ?>
                          </div>
                          <small class="text-muted"><?php echo date('d M Y', $outTs); ?></small>
                        <?php } ?>
                      </td>
                      <td>
                        <?php if ($isOpen) { ?>
                          <span class="text-muted">—</span>
                        <?php } else { ?>
                          <span class="dur-badge"><?php echo formatDuration($durSec); ?></span>
                        <?php } ?>
                      </td>
                      <td>
                        <?php if ($r['type'] == 'Manual') { ?>
                          <span class="badge bg-info text-dark">Manual</span>
                        <?php } else { ?>
                          <span class="badge bg-secondary">GPS</span>
                        <?php } ?>
                      </td>
                      <td>
                        <small>
                          <?php if ($r['type'] == 'Manual') { ?>
                            <span class="text-primary">
                              <i class="bi bi-person-check-fill"></i>
                              Marked manually
                              <?php if (!empty($r['ip_address'])) { ?>
                                <br><span class="text-muted">IP: <?php echo htmlspecialchars($r['ip_address']); ?></span>
                              <?php } ?>
                            </span>
                          <?php } else { ?>
                            <?php if (!empty($r['distance_meter'])) { ?>
                              <span class="badge bg-info mb-1">
                                <?php echo number_format($r['distance_meter'], 2); ?> m from QR
                              </span>
                              <br>
                            <?php } ?>
                            <?php if (!empty($r['gps_accuracy'])) { ?>
                              <span class="text-muted">GPS accuracy: ±<?php echo number_format($r['gps_accuracy'], 1); ?> m</span>
                              <br>
                            <?php } ?>
                            <?php echo htmlspecialchars($r['address'] ?? ''); ?>
                            <?php if (!empty($r['latitude']) && !empty($r['longitude'])) { ?>
                              <br>
                              <a href="https://maps.google.com/?q=<?php echo $r['latitude']; ?>,<?php echo $r['longitude']; ?>"
                                target="_blank" class="text-decoration-none" style="font-size:.7rem;">
                                <i class="bi bi-geo-alt"></i>
                                (<?php echo $r['latitude']; ?>, <?php echo $r['longitude']; ?>)
                              </a>
                            <?php } ?>
                          <?php } ?>
                        </small>
                      </td>
                      <td>
                        <button type="button" class="btn btn-warning btn-sm"
                          onclick="editSession(<?php echo $r['attendance_id']; ?>, '<?php echo htmlspecialchars($r['scan_time'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($r['out_time'] ?? '', ENT_QUOTES); ?>', '<?php echo $r['type']; ?>')"
                          title="Edit">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?del=<?php echo $r['attendance_id']; ?>&member_id=<?php echo $member_id; ?>&date=<?php echo urlencode($date); ?>"
                          onclick="return confirm('Delete this session?')"
                          class="btn btn-danger btn-sm">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

          <?php } ?>

        </div>
      </div>

    </div>

  </div>

  <?php include('component/script.php'); ?>

  <!-- Edit Session Modal -->
  <div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="editSessionModalLabel">
              <i class="bi bi-pencil"></i> Edit Session
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="edit_session" value="1">
            <input type="hidden" name="attendance_id" id="edit_attendance_id">

            <div class="mb-3">
              <label class="form-label fw-bold">IN Time <span class="text-danger">*</span></label>
              <input type="datetime-local" name="scan_time" id="edit_scan_time" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">OUT Time</label>
              <input type="datetime-local" name="out_time" id="edit_out_time" class="form-control">
              <small class="text-muted">Leave blank if session is still open</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
              <select name="type" id="edit_type" class="form-select" required>
                <option value="GPS">GPS</option>
                <option value="Manual">Manual</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function editSession(attendanceId, scanTime, outTime, type) {
      document.getElementById('edit_attendance_id').value = attendanceId;

      // Convert "YYYY-MM-DD HH:MM:SS" to "YYYY-MM-DDTHH:MM" for datetime-local input
      if (scanTime) {
        document.getElementById('edit_scan_time').value = scanTime.replace(' ', 'T').substring(0, 16);
      }

      if (outTime) {
        document.getElementById('edit_out_time').value = outTime.replace(' ', 'T').substring(0, 16);
      } else {
        document.getElementById('edit_out_time').value = '';
      }

      document.getElementById('edit_type').value = type;

      var modal = new bootstrap.Modal(document.getElementById('editSessionModal'));
      modal.show();
    }
  </script>

</body>

</html>
