<?php

include("../adminsession.php");

$title    = "Employee Master";
$pagename = "member-master.php";
$action = isset($_GET['action']) ? $obj->test_input($_GET['action']) : '';

if (isset($_POST['save'])) {

  $member_id  = $_POST['member_id'];
  $chapter_id = trim($_POST['chapter_id']);
  $shop_id    = trim($_POST['chapter_id']);
  $mobile     = trim($_POST['mobile']);

  $fields = [
    'chapter_id'             => $chapter_id,
    'shop_id'                => $shop_id,
    'member_name'            => trim($_POST['member_name']),
    'mobile'                 => $mobile,
    'email'                  => trim($_POST['email']),
    'designation'            => trim($_POST['designation']),
    'monthly_salary'         => trim($_POST['monthly_salary']) ?: 0,
    'password'               => $_POST['password'],
    'attendance_coordinator' => isset($_POST['attendance_coordinator']) ? 1 : 0,
    'status'                 => $_POST['status'],
    'shift_id'               => $_POST['shift_id']
  ];

  $count = $obj->getvalfield(
    "bni_members",
    "COUNT(*)",
    "mobile='$mobile' AND member_id!='$member_id'"
  );

  if ($count > 0) {
    echo "<script>
                location='member-master.php?action=4';
              </script>";
    exit;
  }

  if ($member_id == '') {

    $obj->insert_record('bni_members', $fields);
    $action = 1;
  } else {

    $obj->update_record(
      'bni_members',
      ['member_id' => $member_id],
      $fields
    );
    $action = 2;
  }

  echo "<script>location='member-master.php?action=$action';</script>";
  exit;
}

if (isset($_GET['del'])) {
  $member_id = intval($_GET['del']);

  $obj->delete_record(
    'bni_members',
    ['member_id' => $member_id]
  );

  echo "
    <script>
        location='member-master.php?msg=del';
    </script>";
}

$edit = [];

if (isset($_GET['edit'])) {
  $member_id = intval($_GET['edit']);

  $result = $obj->executequery("
        SELECT *
        FROM bni_members
        WHERE member_id = '$member_id'
    ");

  if ($result) {
    $edit = $result[0];
  }
}

$rows = $obj->executequery("
    SELECT
        m.*,
        c.chapter_name,
        s.shift_name
    FROM bni_members m
    LEFT JOIN chapter_master c
        ON c.chapter_id = m.chapter_id
    LEFT JOIN shift_master s
        ON s.shift_id = m.shift_id
    ORDER BY c.chapter_name,m.member_name
");

$shifts = $obj->executequery("SELECT * FROM shift_master WHERE status = 1 ORDER BY shift_name");

?>

<!DOCTYPE html>
<html>

<head>
  <?php include('component/css.php'); ?>
</head>

<body class="bg-light">

  <?php include('component/sidebar.php'); ?>

  <div class="main w-auto">

    <?php include('component/header.php'); ?>

    <div class="container-fluid py-3">

      <div class="row g-3">

        <!-- MEMBER FORM -->

        <div class="col-lg-4">

          <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body">

              <h5 class="mb-3">
                Employee Entry
              </h5>

              <form method="POST">

                <input type="hidden"
                  name="member_id"
                  value="<?php echo $edit['member_id'] ?? ''; ?>">

                <div class="mb-3">

                  <label class="form-label">
                    Shop Name<span class="text-danger fw-bold">*</span>
                  </label>

                  <?php
                  $chapters = $obj->executequery("SELECT * FROM chapter_master ORDER BY chapter_name");
                  $selectedChapter = count($chapters) == 1
                    ? $chapters[0]['chapter_id']
                    : ($edit['chapter_id'] ?? '');
                  ?>

                  <select name="chapter_id" id="chapter_id" class="form-select mb-3" required>
                    <option value="">Select Shop Name</option>

                    <?php foreach ($chapters as $m) { ?>
                      <option value="<?php echo $m['chapter_id']; ?>"
                        <?php echo ($selectedChapter == $m['chapter_id']) ? 'selected' : ''; ?>>
                        <?php echo $m['chapter_name']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Shift<span class="text-danger fw-bold">*</span>
                  </label>

                  <select name="shift_id" class="form-select mb-3" required>
                    <option value="">Select Shift</option>

                    <?php foreach ($shifts as $sh) { ?>
                      <option value="<?php echo $sh['shift_id']; ?>"
                        <?php echo (($edit['shift_id'] ?? '') == $sh['shift_id']) ? 'selected' : ''; ?>>
                        <?php echo $sh['shift_name']; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Employee Name<span class="text-danger fw-bold">*</span>
                  </label>

                  <input type="text"
                    name="member_name" id="member_name"
                    class="form-control"
                    placeholder="Enter member full name"
                    required
                    value="<?php echo $edit['member_name'] ?? ''; ?>">

                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Mobile / Login ID<span class="text-danger fw-bold">*</span>
                  </label>

                  <input type="text"
                    name="mobile" id="mobile"
                    class="form-control"
                    placeholder="Enter mobile number"
                    required
                    value="<?php echo $edit['mobile'] ?? ''; ?>">

                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Email
                  </label>

                  <input type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter email address"
                    value="<?php echo $edit['email'] ?? ''; ?>">

                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Designation
                  </label>

                  <input type="text"
                    name="designation"
                    class="form-control"
                    placeholder="Enter designation"
                    value="<?php echo $edit['designation'] ?? ''; ?>">

                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Monthly Salary
                  </label>

                  <input type="number"
                    name="monthly_salary"
                    class="form-control"
                    placeholder="Enter Salary"
                    value="<?php echo $edit['monthly_salary'] ?? ''; ?>">

                </div>

                <!-- <div class="mb-3">

                  <label class="form-label">
                    Company
                  </label>

                  <input type="text"
                    name="company_name"
                    class="form-control"
                    placeholder="Enter company name"
                    value="<?php echo $edit['company_name'] ?? ''; ?>">

                </div>

                <div class="mb-3">

                  <label class="form-label">
                    Category
                  </label>

                  <input type="text"
                    name="category"
                    class="form-control"
                    placeholder="Enter business category"
                    value="<?php echo $edit['category'] ?? ''; ?>">

                </div> -->

                <div class="mb-3">

                  <label class="form-label">
                    Password<span class="text-danger fw-bold">*</span>
                  </label>

                  <input type="text"
                    name="password" id="password"
                    class="form-control"
                    placeholder="Enter login password"
                    required
                    value="<?php echo $edit['password'] ?? '123456'; ?>">

                </div>

                <div class="row">

                  <div class="col-md-6 mb-3">

                    <label class="form-label">
                      Status <span class="text-danger fw-bold">*</span>
                    </label>

                    <select name="status" class="form-select" required>
                      <option value="1" <?= (($edit['status'] ?? 1) == 1) ? 'selected' : ''; ?>>
                        Active
                      </option>

                      <option value="0" <?= (($edit['status'] ?? 1) == 0) ? 'selected' : ''; ?>>
                        Inactive
                      </option>
                    </select>

                  </div>

                  <div class="col-md-6 d-flex align-items-end mb-3">

                    <div class="form-check">

                      <input
                        class="form-check-input"
                        type="checkbox"
                        id="attendance_coordinator"
                        name="attendance_coordinator"
                        value="1"
                        <?= !empty($edit['attendance_coordinator']) ? 'checked' : ''; ?>>

                      <label class="form-check-label fw-semibold" for="attendance_coordinator">
                        Att. Manager
                      </label>

                    </div>

                  </div>

                </div>

                <button type="submit"
                  name="save"
                  class="btn btn-primary w-100" onclick="return checkinputmaster('chapter_id,shift_id,member_name,mobile,password,status');">

                  <i class="bi bi-save"></i>
                  <?php echo !empty($edit['member_id']) ? 'Update Employee' : 'Save Employee'; ?>
                </button>

              </form>

            </div>

          </div>

        </div>

        <!-- MEMBER LIST -->

        <div class="col-lg-8">

          <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body">

              <h5 class="mb-3">
                Employee List
              </h5>

              <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle datatable">

                  <thead class="table-light">

                    <tr>
                      <th width="40">#</th>
                      <th>Employee</th>
                      <th>Shop</th>
                      <th>Shift</th>
                      <th>Mobile</th>
                      <th>Designation</th>
                      <th>Salary</th>
                      <!-- <th>Company</th>
                      <th>Category</th> -->
                      <th class="text-center">Att.</th>
                      <th>Status</th>
                      <th width="120">Action</th>
                    </tr>

                  </thead>

                  <tbody>

                    <?php
                    $i = 1;
                    foreach ($rows as $r) {
                    ?>

                      <tr>

                        <td><?php echo $i++; ?></td>

                        <td>
                          <strong><?php echo $r['member_name']; ?></strong>
                          <?php if (!empty($r['email'])) { ?>
                            <br>
                            <small class="text-muted">
                              <?php echo $r['email']; ?>
                            </small>
                          <?php } ?>
                        </td>

                        <td>
                          <?php echo $r['chapter_name']; ?>
                        </td>

                        <td>
                          <?php if (!empty($r['shift_name'])) { ?>
                            <span class="badge bg-primary">
                              <?php echo $r['shift_name']; ?>
                            </span>
                          <?php } else { ?>
                            <span class="text-muted">—</span>
                          <?php } ?>
                        </td>

                        <td>
                          <?php echo $r['mobile']; ?>
                          <?php if (!empty($r['password'])) { ?>
                            <br>
                            <small class="text-muted">
                              Pwd- <?php echo $r['password']; ?>
                            </small>
                          <?php } ?>
                        </td>

                        <td>
                          <?= $r['designation'] ?>
                        </td>

                        <td>
                          <?php if (!empty($r['monthly_salary'])) { ?>
                            <span class="badge bg-info text-dark">
                              ₹<?= number_format($r['monthly_salary']) ?>
                            </span>
                          <?php } else { ?>
                            <span class="text-muted">—</span>
                          <?php } ?>
                        </td>

                        <!-- <td>
                          <?php echo $r['company_name']; ?>
                        </td>

                        <td>
                          <?php echo $r['category']; ?>
                        </td> -->

                        <td class="text-center">

                          <?php if ($r['attendance_coordinator']) { ?>

                            <span class="badge bg-success">
                              Yes
                            </span>

                          <?php } else { ?>

                            <span class="badge bg-secondary">
                              No
                            </span>

                          <?php } ?>

                        </td>

                        <td>

                          <?php if ($r['status']) { ?>

                            <span class="badge bg-success">
                              Active
                            </span>

                          <?php } else { ?>

                            <span class="badge bg-danger">
                              Inactive
                            </span>

                          <?php } ?>

                        </td>

                        <td>

                          <a href="?edit=<?php echo $r['member_id']; ?>"
                            class="btn btn-warning btn-sm"
                            title="Edit">

                            <i class="bi bi-pencil"></i>

                          </a>

                          <a href="?del=<?php echo $r['member_id']; ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this member?')"
                            title="Delete">

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

        </div>

      </div>

    </div>

  </div>

  <?php include('component/script.php'); ?>

</body>

</html>
