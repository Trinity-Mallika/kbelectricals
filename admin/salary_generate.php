<?php

include("../adminsession.php");

$title    = "Salary Generate";
$pagename = "salary_generate.php";

$today = date('Y-m-d');
$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear  = $_GET['year'] ?? date('Y');
$selectedEmp   = intval($_GET['emp_id'] ?? 0);
$msg = '';

/* ================= DELETE SALARY ================= */

if (isset($_GET['del'])) {
  $salary_id = intval($_GET['del']);
  $obj->delete_record('emp_salary', ['salary_id' => $salary_id]);
  $msg = '<div class="alert alert-success"><strong>Salary record deleted!</strong></div>';
}

/* ================= SAVE SALARY ================= */

if (isset($_POST['save_salary'])) {
  $member_id      = intval($_POST['member_id']);
  $month          = $obj->test_input($_POST['month']);
  $year           = $obj->test_input($_POST['year']);
  $total_days     = intval($_POST['total_days']);
  $present_days   = intval($_POST['present_days']);
  $late_days      = intval($_POST['late_days']);
  $absent_days    = intval($_POST['absent_days']);
  $incomplete_days = intval($_POST['incomplete_days']);
  $monthly_salary = floatval($_POST['monthly_salary']);
  $daily_rate     = floatval($_POST['daily_rate']);
  $gross_salary   = floatval($_POST['gross_salary']);
  $ta_amt         = floatval($_POST['ta_amt'] ?? 0);
  $da_amt         = floatval($_POST['da_amt'] ?? 0);
  $bonus_amt      = floatval($_POST['bonus_amt'] ?? 0);
  $overtime_amt   = floatval($_POST['overtime_amt'] ?? 0);
  $fine_amt       = floatval($_POST['fine_amt'] ?? 0);
  $pf_amt         = floatval($_POST['pf_amt'] ?? 0);
  $esic_amt       = floatval($_POST['esic_amt'] ?? 0);
  $advance_paid   = floatval($_POST['advance_paid'] ?? 0);
  $total_earnings = $gross_salary + $ta_amt + $da_amt + $bonus_amt + $overtime_amt;
  $total_deductions = $fine_amt + $pf_amt + $esic_amt + $advance_paid;
  $net_pay        = $total_earnings - $total_deductions;

  $existing = $obj->getvalfield('emp_salary', 'COUNT(*)', "member_id='$member_id' AND month='$month' AND year='$year'");

  if ($existing > 0) {
    $msg = '<div class="alert alert-danger"><strong>Salary already generated for this employee for this month!</strong></div>';
  } else {
    $obj->insert_record('emp_salary', [
      'member_id'       => $member_id,
      'month'           => $month,
      'year'            => $year,
      'total_days'      => $total_days,
      'present_days'    => $present_days,
      'late_days'       => $late_days,
      'absent_days'     => $absent_days,
      'incomplete_days' => $incomplete_days,
      'monthly_salary'  => $monthly_salary,
      'daily_rate'      => $daily_rate,
      'gross_salary'    => $gross_salary,
      'ta_amt'          => $ta_amt,
      'da_amt'          => $da_amt,
      'bonus_amt'       => $bonus_amt,
      'overtime_amt'    => $overtime_amt,
      'fine_amt'        => $fine_amt,
      'pf_amt'          => $pf_amt,
      'esic_amt'        => $esic_amt,
      'advance_paid'    => $advance_paid,
      'total_earnings'  => $total_earnings,
      'total_deductions' => $total_deductions,
      'net_pay'         => $net_pay,
      'createdby'       => $_SESSION['userid']
    ]);
    $msg = '<div class="alert alert-success"><strong>Salary generated successfully!</strong></div>';
  }
}

/* ================= UPDATE SALARY ================= */

if (isset($_POST['update_salary'])) {
  $salary_id      = intval($_POST['salary_id']);
  $ta_amt         = floatval($_POST['ta_amt'] ?? 0);
  $da_amt         = floatval($_POST['da_amt'] ?? 0);
  $bonus_amt      = floatval($_POST['bonus_amt'] ?? 0);
  $overtime_amt   = floatval($_POST['overtime_amt'] ?? 0);
  $fine_amt       = floatval($_POST['fine_amt'] ?? 0);
  $pf_amt         = floatval($_POST['pf_amt'] ?? 0);
  $esic_amt       = floatval($_POST['esic_amt'] ?? 0);
  $advance_paid   = floatval($_POST['advance_paid'] ?? 0);
  $gross_salary   = floatval($_POST['gross_salary']);
  $total_earnings = $gross_salary + $ta_amt + $da_amt + $bonus_amt + $overtime_amt;
  $total_deductions = $fine_amt + $pf_amt + $esic_amt + $advance_paid;
  $net_pay        = $total_earnings - $total_deductions;

  $obj->update_record('emp_salary', ['salary_id' => $salary_id], [
    'ta_amt'          => $ta_amt,
    'da_amt'          => $da_amt,
    'bonus_amt'       => $bonus_amt,
    'overtime_amt'    => $overtime_amt,
    'fine_amt'        => $fine_amt,
    'pf_amt'          => $pf_amt,
    'esic_amt'        => $esic_amt,
    'advance_paid'    => $advance_paid,
    'total_earnings'  => $total_earnings,
    'total_deductions' => $total_deductions,
    'net_pay'         => $net_pay,
  ]);
  $msg = '<div class="alert alert-success"><strong>Salary record updated!</strong></div>';
}

/* ================= EDIT MODE ================= */

$editRecord = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId > 0) {
  $editRows = $obj->executequery("SELECT es.*, m.member_name FROM emp_salary es LEFT JOIN bni_members m ON m.member_id = es.member_id WHERE es.salary_id='$editId'");
  if (!empty($editRows)) {
    $editRecord = $editRows[0];
  }
}

/* ================= ATTENDANCE COMPUTATION ================= */

$empData = null;
$attendanceStats = null;

if ($selectedEmp > 0) {
  $empData = $obj->executequery("SELECT * FROM bni_members WHERE member_id='$selectedEmp' AND status=1");
  if (!empty($empData)) {
    $empData = $empData[0];
    $empShift = $obj->getShift($empData['shift_id'] ?? null);
    $graceIn = (int)(($empShift['grace_in_minutes'] ?? $empShift['grace_minutes']) ?? 0);

    $monthStart = "$selectedYear-$selectedMonth-01";
    $monthEnd   = date('Y-m-t', strtotime($monthStart));
    $totalDays  = (int)cal_days_in_month(CAL_GREGORIAN, intval($selectedMonth), intval($selectedYear));

    $monthDays = $obj->executequery("
      SELECT DATE(scan_time) AS day, MIN(scan_time) AS first_in,
             MAX(out_time) AS last_out
      FROM bni_attendance
      WHERE member_id='$selectedEmp'
        AND DATE(scan_time) BETWEEN '$monthStart' AND '$monthEnd'
      GROUP BY DATE(scan_time)
      ORDER BY DATE(scan_time)
    ");

    $presentDays   = 0;
    $lateDays      = 0;
    $incompleteDays = 0;
    $expectedWorkSec = (float)($empShift['expected_work_hours'] ?? 0) * 3600;

    foreach ($monthDays as $row) {
      $presentDays++;
      $shiftStartTs = strtotime($row['day'] . ' ' . $empShift['start_time']) + ($graceIn * 60);
      if (strtotime($row['first_in']) > $shiftStartTs) {
        $lateDays++;
      }
      if (!empty($row['last_out']) && $expectedWorkSec > 0) {
        $workedSec = strtotime($row['last_out']) - strtotime($row['first_in']);
        if ($workedSec < $expectedWorkSec) {
          $incompleteDays++;
        }
      }
    }

    $absentDays = $totalDays - $presentDays;
    if ($absentDays < 0) $absentDays = 0;

    $monthlySalary = floatval($empData['monthly_salary']);
    $dailyRate = $totalDays > 0 ? round($monthlySalary / $totalDays, 2) : 0;
    $grossSalary = round($dailyRate * $presentDays, 2);

    $attendanceStats = [
      'total_days'      => $totalDays,
      'present_days'    => $presentDays,
      'late_days'       => $lateDays,
      'absent_days'     => $absentDays,
      'incomplete_days' => $incompleteDays,
      'monthly_salary'  => $monthlySalary,
      'daily_rate'      => $dailyRate,
      'gross_salary'    => $grossSalary,
    ];
  }
}

/* ================= RECORD LIST ================= */

$records = $obj->executequery("
  SELECT es.*, m.member_name
  FROM emp_salary es
  LEFT JOIN bni_members m ON m.member_id = es.member_id
  ORDER BY es.year DESC, es.month DESC, m.member_name ASC
");

$employees = $obj->executequery("SELECT member_id, member_name FROM bni_members WHERE status=1 ORDER BY member_name");

?>

<!DOCTYPE html>
<html>

<head>
  <?php include('component/css.php'); ?>
  <style>
    .summary-box {
      border: 0;
      border-radius: 14px;
      overflow: hidden;
      color: #fff;
    }

    .summary-box .val {
      font-size: 1.6rem;
      font-weight: 700;
    }

    .summary-box .lbl {
      font-size: .7rem;
      text-transform: uppercase;
      letter-spacing: .04em;
      opacity: .85;
    }

    .sb-green {
      background: linear-gradient(135deg, #065f46, #10b981);
    }

    .sb-blue {
      background: linear-gradient(135deg, #1e3a5f, #3b82f6);
    }

    .sb-red {
      background: linear-gradient(135deg, #7f1d1d, #ef4444);
    }

    .sb-yellow {
      background: linear-gradient(135deg, #78350f, #f59e0b);
    }

    .sb-purple {
      background: linear-gradient(135deg, #4c1d95, #8b5cf6);
    }

    .salary-section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      margin-top: 16px;
    }

    .salary-section h6 {
      font-weight: 700;
      margin-bottom: 12px;
      color: #333;
    }

    input[type="number"] {
      -moz-appearance: textfield;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
  </style>
</head>

<body class="bg-light">

  <?php include('component/sidebar.php'); ?>

  <div class="main w-auto">

    <?php include('component/header.php'); ?>

    <div class="container-fluid py-3">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Salary Generate</h4>
      </div>

      <?php echo $msg; ?>

      <!-- SEARCH FORM -->
      <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
          <form method="GET">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-bold">Employee <span class="text-danger">*</span></label>
                <select name="emp_id" class="form-select" required>
                  <option value="">-- Select Employee --</option>
                  <?php foreach ($employees as $emp) { ?>
                    <option value="<?= $emp['member_id'] ?>" <?= ($selectedEmp == $emp['member_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($emp['member_name']) ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">Month <span class="text-danger">*</span></label>
                <select name="month" class="form-select" required>
                  <?php for ($m = 1; $m <= 12; $m++) {
                    $mVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                  ?>
                    <option value="<?= $mVal ?>" <?= ($selectedMonth == $mVal) ? 'selected' : '' ?>>
                      <?= date('F', strtotime("$mVal/1/2026")) ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-bold">Year <span class="text-danger">*</span></label>
                <select name="year" class="form-select" required>
                  <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++) { ?>
                    <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>><?= $y ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary" onclick="return checkinputmaster('emp_id,month,year')">
                  <i class="bi bi-search"></i> Search
                </button>
                <a href="salary_generate.php" class="btn btn-outline-secondary">
                  <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <?php if ($editRecord || ($empData && $attendanceStats)) { ?>

        <!-- ATTENDANCE SUMMARY -->
        <?php if ($attendanceStats) { ?>
          <div class="row g-3 mb-3">
            <div class="col">
              <div class="card summary-box sb-blue shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                  <div>
                    <div class="val"><?= $attendanceStats['total_days'] ?></div>
                    <div class="lbl">Total Days</div>
                  </div>
                  <i class="bi bi-calendar3" style="font-size:1.5rem;opacity:.5"></i>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card summary-box sb-green shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                  <div>
                    <div class="val"><?= $attendanceStats['present_days'] ?></div>
                    <div class="lbl">Present</div>
                  </div>
                  <i class="bi bi-check-circle" style="font-size:1.5rem;opacity:.5"></i>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card summary-box sb-yellow shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                  <div>
                    <div class="val"><?= $attendanceStats['late_days'] ?></div>
                    <div class="lbl">Late</div>
                  </div>
                  <i class="bi bi-clock-history" style="font-size:1.5rem;opacity:.5"></i>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card summary-box sb-purple shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                  <div>
                    <div class="val"><?= $attendanceStats['incomplete_days'] ?></div>
                    <div class="lbl">Incomplete</div>
                  </div>
                  <i class="bi bi-exclamation-triangle" style="font-size:1.5rem;opacity:.5"></i>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card summary-box sb-red shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                  <div>
                    <div class="val"><?= $attendanceStats['absent_days'] ?></div>
                    <div class="lbl">Absent</div>
                  </div>
                  <i class="bi bi-x-circle" style="font-size:1.5rem;opacity:.5"></i>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>

        <!-- SALARY CALCULATION -->
        <form method="POST">
          <?php if ($editRecord) { ?>
            <input type="hidden" name="update_salary" value="1">
            <input type="hidden" name="salary_id" value="<?= $editRecord['salary_id'] ?>">
            <input type="hidden" name="gross_salary" value="<?= $editRecord['gross_salary'] ?>">
          <?php } else { ?>
            <input type="hidden" name="save_salary" value="1">
            <input type="hidden" name="member_id" value="<?= $selectedEmp ?>">
            <input type="hidden" name="month" value="<?= $selectedMonth ?>">
            <input type="hidden" name="year" value="<?= $selectedYear ?>">
            <input type="hidden" name="total_days" value="<?= $attendanceStats['total_days'] ?>">
            <input type="hidden" name="present_days" value="<?= $attendanceStats['present_days'] ?>">
            <input type="hidden" name="late_days" value="<?= $attendanceStats['late_days'] ?>">
            <input type="hidden" name="absent_days" value="<?= $attendanceStats['absent_days'] ?>">
            <input type="hidden" name="incomplete_days" value="<?= $attendanceStats['incomplete_days'] ?>">
            <input type="hidden" name="monthly_salary" value="<?= $attendanceStats['monthly_salary'] ?>">
            <input type="hidden" name="daily_rate" value="<?= $attendanceStats['daily_rate'] ?>">
            <input type="hidden" name="gross_salary" value="<?= $attendanceStats['gross_salary'] ?>">
          <?php } ?>

          <div class="row g-3">

            <!-- EARNINGS -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                  <h6 class="fw-bold text-success"><i class="bi bi-arrow-up-circle"></i> Salary Breakdown</h6>
                  <table class="table table-sm">
                    <tr>
                      <td>Employee</td>
                      <td class="fw-bold"><?= htmlspecialchars($editRecord['member_name'] ?? $empData['member_name']) ?></td>
                    </tr>
                    <tr>
                      <td>Month / Year</td>
                      <td><?= date('F Y', strtotime(($editRecord['month'] ?? $selectedMonth) . '/1/' . ($editRecord['year'] ?? $selectedYear))) ?></td>
                    </tr>
                    <tr>
                      <td>Monthly Salary</td>
                      <td class="fw-bold">₹<?= number_format($editRecord['monthly_salary'] ?? $attendanceStats['monthly_salary'], 2) ?></td>
                    </tr>
                    <tr>
                      <td>Daily Rate</td>
                      <td>₹<?= number_format($editRecord['daily_rate'] ?? $attendanceStats['daily_rate'], 2) ?> (<?= $editRecord['total_days'] ?? $attendanceStats['total_days'] ?> days)</td>
                    </tr>
                    <tr>
                      <td>Present Days</td>
                      <td class="fw-bold text-success"><?= $editRecord['present_days'] ?? $attendanceStats['present_days'] ?></td>
                    </tr>
                    <tr>
                      <td>Gross Salary</td>
                      <td class="fw-bold text-success" style="font-size:1.1rem">₹<span id="gross_salary_display"><?= number_format($editRecord['gross_salary'] ?? $attendanceStats['gross_salary'], 2) ?></span></td>
                    </tr>
                  </table>

                  <h6 class="fw-bold text-primary mt-3"><i class="bi bi-plus-circle"></i> Earnings</h6>
                  <table class="table table-sm">
                    <tr>
                      <td>TA</td>
                      <td><input type="number" name="ta_amt" id="ta_amt" class="form-control form-control-sm" value="<?= $editRecord['ta_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr>
                      <td>DA</td>
                      <td><input type="number" name="da_amt" id="da_amt" class="form-control form-control-sm" value="<?= $editRecord['da_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr>
                      <td>Bonus</td>
                      <td><input type="number" name="bonus_amt" id="bonus_amt" class="form-control form-control-sm" value="<?= $editRecord['bonus_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr>
                      <td>Overtime</td>
                      <td><input type="number" name="overtime_amt" id="overtime_amt" class="form-control form-control-sm" value="<?= $editRecord['overtime_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr class="table-success">
                      <td class="fw-bold">Total Earnings</td>
                      <td class="fw-bold">₹<span id="total_earnings_display">0.00</span></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <!-- DEDUCTIONS -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                  <h6 class="fw-bold text-danger"><i class="bi bi-minus-circle"></i> Deductions</h6>
                  <table class="table table-sm">
                    <tr>
                      <td>Fine</td>
                      <td><input type="number" name="fine_amt" id="fine_amt" class="form-control form-control-sm" value="<?= $editRecord['fine_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr>
                      <td>PF</td>
                      <td><input type="number" name="pf_amt" id="pf_amt" class="form-control form-control-sm" value="<?= $editRecord['pf_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr>
                      <td>ESIC</td>
                      <td><input type="number" name="esic_amt" id="esic_amt" class="form-control form-control-sm" value="<?= $editRecord['esic_amt'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr>
                      <td>Advance Paid</td>
                      <td><input type="number" name="advance_paid" id="advance_paid" class="form-control form-control-sm" value="<?= $editRecord['advance_paid'] ?? 0 ?>" step="0.01" onkeyup="calcNet()"></td>
                    </tr>
                    <tr class="table-danger">
                      <td class="fw-bold">Total Deductions</td>
                      <td class="fw-bold">₹<span id="total_deductions_display">0.00</span></td>
                    </tr>
                  </table>

                  <div class="mt-4 p-3 rounded-3" style="background: linear-gradient(135deg, #06163a, #287ab1); color: #fff;">
                    <div class="d-flex justify-content-between align-items-center">
                      <h5 class="mb-0 fw-bold">NET PAY</h5>
                      <h4 class="mb-0 fw-bold">₹<span id="net_pay_display">0.00</span></h4>
                    </div>
                  </div>

                  <div class="mt-3">
                    <?php if ($editRecord) { ?>
                      <button type="submit" name="update_salary" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Update Salary
                      </button>
                      <a href="salary_generate.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="bi bi-x-circle"></i> Cancel Edit
                      </a>
                    <?php } else { ?>
                      <button type="submit" name="save_salary" class="btn btn-success w-100">
                        <i class="bi bi-save"></i> Generate Salary
                      </button>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>

      <?php } ?>

      <!-- RECORD LIST -->
      <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body">
          <h5 class="mb-3">Generated Salaries</h5>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle datatable">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Employee</th>
                  <th>Month/Year</th>
                  <th>Total Days</th>
                  <th>Present</th>
                  <th>Late</th>
                  <th>Absent</th>
                  <th class="text-end">Gross</th>
                  <th class="text-end">Earnings</th>
                  <th class="text-end">Deductions</th>
                  <th class="text-end">Net Pay</th>
                  <th>Date</th>
                  <th></th>
                  <th width="120">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($records)) { ?>
                  <tr>
                    <td colspan="13" class="text-center text-muted py-4">
                      <i class="bi bi-inbox" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                      No salary records found.
                    </td>
                  </tr>
                  <?php } else {
                  $i = 1;
                  foreach ($records as $r) { ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td class="fw-bold"><?= htmlspecialchars($r['member_name']) ?></td>
                      <td><?= date('F Y', strtotime($r['year'] . '-' . $r['month'] . '-01')) ?></td>
                      <td><?= $r['total_days'] ?></td>
                      <td><span class="badge bg-success"><?= $r['present_days'] ?></span></td>
                      <td><span class="badge bg-warning text-dark"><?= $r['late_days'] ?></span></td>
                      <td><span class="badge bg-danger"><?= $r['absent_days'] ?></span></td>
                      <td class="text-end">₹<?= number_format($r['gross_salary'], 2) ?></td>
                      <td class="text-end text-success">₹<?= number_format($r['total_earnings'], 2) ?></td>
                      <td class="text-end text-danger">₹<?= number_format($r['total_deductions'], 2) ?></td>
                      <td class="text-end fw-bold">₹<?= number_format($r['net_pay'], 2) ?></td>
                      <td><small class="text-muted"><?= date('d-m-Y', strtotime($r['created_at'])) ?></small></td>
                      <td>
                        <a href="salary_slip.php?salary_id=<?= $r['salary_id'] ?>" class="btn btn-info btn-sm" target="_blank" title="Print Salary Slip">
                          <i class="bi bi-printer"></i> Print
                        </a>
                      </td>
                      <td>
                        <a href="?edit=<?= $r['salary_id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <a href="?del=<?= $r['salary_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this salary record?')" title="Delete">
                          <i class="bi bi-trash"></i>
                        </a>
                      </td>
                    </tr>
                <?php }
                } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include('component/script.php'); ?>

  <script>
    function calcNet() {
      var gross = parseFloat(document.querySelector('[name="gross_salary"]').value) || 0;
      var ta = parseFloat(document.getElementById('ta_amt').value) || 0;
      var da = parseFloat(document.getElementById('da_amt').value) || 0;
      var bonus = parseFloat(document.getElementById('bonus_amt').value) || 0;
      var overtime = parseFloat(document.getElementById('overtime_amt').value) || 0;
      var fine = parseFloat(document.getElementById('fine_amt').value) || 0;
      var pf = parseFloat(document.getElementById('pf_amt').value) || 0;
      var esic = parseFloat(document.getElementById('esic_amt').value) || 0;
      var advance = parseFloat(document.getElementById('advance_paid').value) || 0;

      var totalEarnings = gross + ta + da + bonus + overtime;
      var totalDeductions = fine + pf + esic + advance;
      var netPay = totalEarnings - totalDeductions;

      document.getElementById('total_earnings_display').textContent = totalEarnings.toFixed(2);
      document.getElementById('total_deductions_display').textContent = totalDeductions.toFixed(2);
      document.getElementById('net_pay_display').textContent = netPay.toFixed(2);
    }

    $(document).ready(function() {
      if (document.querySelector('[name="gross_salary"]')) {
        calcNet();
      }
      $('.datatable').DataTable();
    });
  </script>

</body>

</html>
