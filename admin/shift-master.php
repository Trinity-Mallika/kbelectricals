<?php
include("../adminsession.php");

$title    = "Shift Master";
$pagename = "shift-master.php";
$action   = isset($_GET['action']) ? $obj->test_input($_GET['action']) : '';

/* =========================================================
   SAVE / UPDATE
========================================================= */

if (isset($_POST['save'])) {

  $shift_id   = $obj->test_input($_POST['shift_id']);
  $shift_name = $obj->test_input(trim($_POST['shift_name']));

  $fields = [
    'shift_name'          => $shift_name,
    'start_time'          => $obj->test_input($_POST['start_time']) . ':00',
    'end_time'            => $obj->test_input($_POST['end_time'])   . ':00',
    'lunch_start'         => $_POST['lunch_start'] ? $obj->test_input($_POST['lunch_start']) . ':00' : null,
    'lunch_end'           => $_POST['lunch_end']   ? $obj->test_input($_POST['lunch_end'])   . ':00' : null,
    'grace_in_minutes'    => (int)$_POST['grace_in_minutes'],
    'grace_out_minutes'   => (int)$_POST['grace_out_minutes'],
    'expected_work_hours' => (float)$_POST['expected_work_hours'],
    'status'              => $obj->test_input($_POST['status'])
  ];

  /* Duplicate name check */
  $count = $obj->getvalfield(
    "shift_master",
    "COUNT(*)",
    "LOWER(TRIM(shift_name))=LOWER(TRIM('$shift_name')) AND shift_id!='$shift_id'",1
  );

  if ($count > 0) {
    echo "<script>
                alert('Shift with this name already exists.');
                history.back();
              </script>";
    exit;
  }

  if ($shift_id == "") {
    $obj->insert_record("shift_master", $fields);
    $action = "1";
  } else {
    $obj->update_record(
      "shift_master",
      ["shift_id" => $shift_id],
      $fields
    );
    $action = "2";
  }

  echo "<script>location='shift-master.php?action=$action';</script>";
  exit;
}

/* =========================================================
   DELETE
========================================================= */

if (isset($_GET['del'])) {
  $shift_id = intval($_GET['del']);

  $obj->delete_record(
    "shift_master",
    ["shift_id" => $shift_id]
  );

  echo "<script>location='shift-master.php?msg=del';</script>";
}

/* =========================================================
   EDIT DATA
========================================================= */

$edit = [];

if (isset($_GET['edit'])) {
  $edit = $obj->select_record(
    "shift_master",
    ["shift_id" => $_GET['edit']]
  );
}

/* =========================================================
   FETCH LIST
========================================================= */

$rows = $obj->executequery("
    SELECT *
    FROM shift_master
    ORDER BY shift_id ASC
");

?>

<!doctype html>
<html>

<head>
  <?php include("component/css.php"); ?>
  <style>
    .shift-card {
      border-left: 4px solid #287ab1;
    }

    .shift-card.active-shift {
      border-left-color: #16a34a;
    }

    .time-badge {
      font-family: monospace;
      font-size: .85rem;
      font-weight: 700;
      background: rgba(40, 122, 177, .12);
      color: #1a56a0;
      padding: 3px 8px;
      border-radius: 50px;
    }

    .grace-pill {
      font-size: .7rem;
      padding: 2px 7px;
      border-radius: 50px;
      font-weight: 600;
    }

    .grace-pill.in {
      background: #dcfce7;
      color: #16a34a;
    }

    .grace-pill.out {
      background: #fef3c7;
      color: #f59e0b;
    }
  </style>
</head>

<body class="bg-light">

  <?php include("component/sidebar.php"); ?>

  <div class="main w-auto">

    <?php include("component/header.php"); ?>

    <div class="container-fluid py-3">

      <div class="row">

        <!-- FORM SECTION -->
        <div class="col-md-4">
          <?php include("component/alert.php"); ?>
          <div class="card shadow-sm border-0 rounded-4">

            <div class="card-body">

              <h5 class="mb-3">
                Shift Entry
              </h5>

              <form method="post">

                <input type="hidden"
                  name="shift_id"
                  value="<?php echo $edit['shift_id'] ?? ''; ?>">

                <div class="mb-3">
                  <label class="form-label">
                    Shift Name<span class="text-danger fw-bold">*</span>
                  </label>
                  <input
                    type="text"
                    name="shift_name"
                    id="shift_name"
                    class="form-control"
                    placeholder="e.g. General Shift, Morning Shift"
                    required
                    value="<?php echo htmlspecialchars($edit['shift_name'] ?? ''); ?>">
                </div>

                <div class="row">
                  <div class="col-6 mb-3">
                    <label class="form-label">
                      Start Time<span class="text-danger fw-bold">*</span>
                    </label>
                    <input
                      type="time"
                      name="start_time"
                      id="start_time"
                      class="form-control"
                      required
                      value="<?php echo isset($edit['start_time']) ? substr($edit['start_time'], 0, 5) : '10:30'; ?>">
                  </div>
                  <div class="col-6 mb-3">
                    <label class="form-label">
                      End Time<span class="text-danger fw-bold">*</span>
                    </label>
                    <input
                      type="time"
                      name="end_time"
                      id="end_time"
                      class="form-control"
                      required
                      value="<?php echo isset($edit['end_time']) ? substr($edit['end_time'], 0, 5) : '19:00'; ?>">
                  </div>
                </div>

                <div class="row">
                  <div class="col-6 mb-3">
                    <label class="form-label">
                      Lunch Start
                      <!-- <small class="text-muted">(optional)</small> -->
                    </label>
                    <input
                      type="time"
                      name="lunch_start"
                      class="form-control"
                      value="<?php echo isset($edit['lunch_start']) && $edit['lunch_start'] ? substr($edit['lunch_start'], 0, 5) : ''; ?>">
                  </div>
                  <div class="col-6 mb-3">
                    <label class="form-label">
                      Lunch End
                      <!-- <small class="text-muted">(optional)</small> -->
                    </label>
                    <input
                      type="time"
                      name="lunch_end"
                      class="form-control"
                      value="<?php echo isset($edit['lunch_end']) && $edit['lunch_end'] ? substr($edit['lunch_end'], 0, 5) : ''; ?>">
                  </div>
                </div>

                <hr class="my-3">
                <h6 class="text-muted mb-3">
                  <i class="bi bi-shield-check"></i> Grace Periods
                </h6>

                <div class="row">
                  <div class="col-6 mb-3">
                    <label class="form-label">
                      Grace IN (min)
                      <i class="bi bi-info-circle text-muted" title="Late flag triggers only if employee arrives past (shift start + this grace)"></i>
                    </label>
                    <input
                      type="number"
                      name="grace_in_minutes"
                      class="form-control"
                      min="0" max="120"
                      placeholder="0"
                      value="<?php echo htmlspecialchars($edit['grace_in_minutes'] ?? '0'); ?>">
                  </div>
                  <div class="col-6 mb-3">
                    <label class="form-label">
                      Grace OUT (min)
                      <i class="bi bi-info-circle text-muted" title="Early-leave flag triggers only if employee leaves before (shift end - this grace)"></i>
                    </label>
                    <input
                      type="number"
                      name="grace_out_minutes"
                      class="form-control"
                      min="0" max="120"
                      placeholder="0"
                      value="<?php echo htmlspecialchars($edit['grace_out_minutes'] ?? '0'); ?>">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">
                    Expected Work Hours / Day
                    <i class="bi bi-info-circle text-muted" title="If employee works less than this in a day, a 'Short by' badge appears"></i>
                  </label>
                  <div class="input-group">
                    <input
                      type="number"
                      name="expected_work_hours"
                      class="form-control"
                      min="0" max="24" step="0.25"
                      placeholder="8.00"
                      value="<?php echo htmlspecialchars($edit['expected_work_hours'] ?? '8.00'); ?>">
                    <span class="input-group-text">hours</span>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">
                    Status<span class="text-danger fw-bold">*</span>
                  </label>
                  <select name="status" class="form-select">
                    <option value="1" <?php echo (($edit['status'] ?? 1) == 1) ? 'selected' : ''; ?>>
                      Active
                    </option>
                    <option value="0" <?php echo (($edit['status'] ?? 1) == 0) ? 'selected' : ''; ?>>
                      Inactive
                    </option>
                  </select>
                </div>

                <button type="submit"
                  class="btn btn-primary w-100"
                  name="save"
                  onclick="return checkinputmaster('shift_name,start_time,end_time,status');">
                  <i class="bi bi-save"></i>
                  <?php echo !empty($edit['shift_id']) ? 'Update Shift' : 'Save Shift'; ?>
                </button>

              </form>

            </div>

          </div>

        </div>

        <!-- LIST SECTION -->
        <div class="col-md-8">

          <div class="card shadow-sm border-0 rounded-4">

            <div class="card-body">

              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Shift List</h5>
                <small class="text-muted">
                  The first <b>Active</b> shift is used as default for all attendance calculations
                </small>
              </div>

              <div class="table-responsive">

                <table class="table table-bordered table-striped datatable">

                  <thead>
                    <tr>
                      <th width="50">#</th>
                      <th>Shift Name</th>
                      <th>Timings</th>
                      <th>Lunch</th>
                      <th>Grace</th>
                      <th>Expected</th>
                      <th>Status</th>
                      <th width="140">Action</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php
                    $i = 1;
                    foreach ($rows as $row) {
                      $isActive = ($row['status'] == 1);
                    ?>
                      <tr class="shift-card <?php echo $isActive ? 'active-shift' : ''; ?>">
                        <td><?php echo $i++; ?></td>

                        <td>
                          <strong><?php echo htmlspecialchars($row['shift_name']); ?></strong>
                        </td>

                        <td>
                          <span class="time-badge">
                            <?php echo date('h:i A', strtotime($row['start_time'])); ?>
                            →
                            <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                          </span>
                        </td>

                        <td>
                          <?php if (!empty($row['lunch_start']) && !empty($row['lunch_end'])) { ?>
                            <span class="time-badge" style="background:rgba(245,158,11,.12);color:#f59e0b;">
                              <?php echo date('h:i A', strtotime($row['lunch_start'])); ?>
                              -
                              <?php echo date('h:i A', strtotime($row['lunch_end'])); ?>
                            </span>
                          <?php } else { ?>
                            <span class="text-muted">—</span>
                          <?php } ?>
                        </td>

                        <td>
                          <span class="grace-pill in">IN <?php echo (int)$row['grace_in_minutes']; ?>m</span>
                          <span class="grace-pill out">OUT <?php echo (int)$row['grace_out_minutes']; ?>m</span>
                        </td>

                        <td>
                          <span class="badge bg-info text-dark">
                            <?php echo number_format((float)$row['expected_work_hours'], 2); ?>h
                          </span>
                        </td>

                        <td>
                          <?php if ($isActive) { ?>
                            <span class="badge bg-success">Active</span>
                          <?php } else { ?>
                            <span class="badge bg-danger">Inactive</span>
                          <?php } ?>
                        </td>

                        <td>
                          <a href="?edit=<?php echo $row['shift_id']; ?>"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <a href="?del=<?php echo $row['shift_id']; ?>"
                            onclick="return confirm('Delete this shift? Attendance calculations will fall back to the next active shift.')"
                            class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                          </a>
                        </td>

                      </tr>
                    <?php } ?>
                  </tbody>

                </table>

              </div>

            </div>

          </div>

          <!-- INFO NOTE -->
          <div class="alert alert-info mt-3" style="font-size:13px;">
            <h6 class="alert-heading">
              <i class="bi bi-info-circle-fill"></i> How shift settings affect attendance
            </h6>
            <ul class="mb-0">
              <li><b>Start Time + Grace IN</b>: Employee arriving within this window is "on time". Past it = "Late" badge.</li>
              <li><b>End Time − Grace OUT</b>: Employee leaving before this is flagged "Early".</li>
              <li><b>Expected Work Hours</b>: If worked &lt; this when all sessions closed, "Short by Xm" badge appears.</li>
              <li><b>Lunch Start/End</b>: Currently informational only — lunch time is NOT auto-deducted from worked hours.</li>
              <li>The first <b>Active</b> shift (lowest shift_id) is used everywhere — dashboard, manual attendance, report, calendar, sessions.</li>
              <li>Changes take effect immediately on next page load (no cache beyond a single request).</li>
            </ul>
          </div>

        </div>

      </div>

    </div>

  </div>

  <?php include("component/script.php"); ?>

</body>

</html>
