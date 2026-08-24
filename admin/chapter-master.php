<?php
include("../adminsession.php");

$title = "Shop Master";
$pagename = "chapter-master.php";
$action = isset($_GET['action']) ? $obj->test_input($_GET['action']) : '';

if (isset($_POST['save'])) {

  $chapter_id   = $_POST['chapter_id'];
  $chapter_name = trim($_POST['chapter_name']);

  $fields = array(
    "chapter_name" => $chapter_name,
    "status"       => $_POST['status'],
    "ipaddress"    => $ipaddress,
    "createdby"    => $loginid
  );

  $count = $obj->getvalfield(
    "chapter_master",
    "COUNT(*)",
    "LOWER(TRIM(chapter_name))=LOWER(TRIM('$chapter_name')) AND chapter_id!='" . $chapter_id . "'"
  );

  if ($count > 0) {
    echo "<script>
                alert('Shop already exists.');
                history.back();
              </script>";
    exit;
  }

  if ($chapter_id == "") {
    $fields['createdate'] = $createdate;
    $obj->insert_record("chapter_master", $fields);
    $action = "1";
  } else {
    $fields['lastupdated'] = $createdate;
    $obj->update_record(
      "chapter_master",
      array("chapter_id" => $chapter_id),
      $fields
    );
    $action = "2";
  }

  echo "<script>location='chapter-master.php?action=$action';</script>";
  exit;
}

if (isset($_GET['del'])) {
  $obj->delete_record(
    "chapter_master",
    array("chapter_id" => $_GET['del'])
  );

  echo "<script>location='chapter-master.php?msg=del';</script>";
}

$edit = array();

if (isset($_GET['edit'])) {
  $edit = $obj->select_record(
    "chapter_master",
    array("chapter_id" => $_GET['edit'])
  );
}

$rows = $obj->executequery("
    SELECT *
    FROM chapter_master
    ORDER BY chapter_name
");
?>

<!doctype html>
<html>

<head>
  <?php include("component/css.php"); ?>
</head>

<body class="bg-light">

  <?php include("component/sidebar.php"); ?>

  <div class="main">
    <?php include("component/header.php"); ?>

    <div class="container-fluid py-3">
      <div class="row">

        <!-- Shop Entry Form -->
        <div class="col-md-4">
          <?php include("component/alert.php"); ?>
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
              <h5 class="mb-3">Shop Entry</h5>

              <form method="post">
                <input type="hidden" name="chapter_id" value="<?php echo $edit['chapter_id'] ?? ''; ?>">

                <div class="mb-3">
                  <label class="form-label">
                    Shop Name<span class="text-danger fw-bold">*</span>
                  </label>
                  <input type="text" name="chapter_name" id="chapter_name" class="form-control"
                    placeholder="Enter Shop Name"
                    value="<?php echo $edit['chapter_name'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                  <label class="form-label">
                    Status<span class="text-danger fw-bold">*</span>
                  </label>
                  <select name="status" id="status" class="form-select">
                    <option value="1" <?php if (($edit['status'] ?? 1) == 1) echo "selected"; ?>>
                      Active
                    </option>
                    <option value="0" <?php if (($edit['status'] ?? 1) == 0) echo "selected"; ?>>
                      Inactive
                    </option>
                  </select>
                </div>

                <button type="submit" class="btn btn-primary w-100" name="save" onclick="return checkinputmaster('chapter_name,status');">
                  <i class="bi bi-save"></i>
                  <?php echo !empty($edit['chapter_id']) ? 'Update Shop' : 'Save Shop'; ?>
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Shop List -->
        <div class="col-md-8">
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
              <h5 class="mb-3">Shop List</h5>

              <div class="table-responsive">
                <table class="table table-bordered table-striped datatable">
                  <thead>
                    <tr>
                      <th width="60">#</th>
                      <th>Shop Name</th>
                      <th>Status</th>
                      <th width="140">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $i = 1;
                    foreach ($rows as $row) {
                    ?>
                      <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $row['chapter_name']; ?></td>
                        <td>
                          <?php if ($row['status'] == 1) { ?>
                            <span class="badge bg-success">Active</span>
                          <?php } else { ?>
                            <span class="badge bg-danger">Inactive</span>
                          <?php } ?>
                        </td>
                        <td>
                          <a href="?edit=<?php echo $row['chapter_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <a href="?del=<?php echo $row['chapter_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this chapter?')">
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

  <?php include("component/script.php"); ?>

</body>

</html>
