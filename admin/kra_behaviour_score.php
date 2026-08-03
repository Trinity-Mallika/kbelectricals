<?php
include("../adminsession.php");
$title = "KRA Behaviour Score";
$pagename = "kra_behaviour_score.php";
$module = "KRA Behaviour Score";
$submodule = "KRA Behaviour Score List";
$tblname = "kra_behaviour_score";
$tblpkey = "kra_behaviour_scoreid";
$selMonth = isset($_GET['month']) ? intval($_GET['month']) : (int)date('m');
$selYear  = isset($_GET['year'])  ? intval($_GET['year'])  : (int)date('Y');
$emp_id   = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tag -->
    <?php include('component/css.php'); ?>
    <style>
        .score-input {
            width: 80px;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- Header -->
        <?php include('component/header.php'); ?>
        <!-- Header Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $title ?></legend>
                        <form action="<?php echo $pagename; ?>" method="get">
                            <div class="card">
                                <div class="card-header text-white">
                                    <?php echo $module ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label>Month<span class="text-danger fw-bold"> *</span></label>
                                            <select name="month" id="selMonth" class="form-control form-control-sm">
                                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                                    <option value="<?= $m ?>" <?= $m == $selMonth ? 'selected' : '' ?>>
                                                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Year<span class="text-danger fw-bold"> *</span></label>
                                            <select name="year" id="selYear" class="form-control form-control-sm">
                                                <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--): ?>
                                                    <option value="<?= $y ?>" <?= $y == $selYear ? 'selected' : '' ?>><?= $y ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong><label>Sales Executive</label></strong>
                                            <select name="emp_id" id="filter_emp_id" class="chosen-select form-control form-control-sm">
                                                <option value="0">-- All Executives --</option>
                                                <?php
                                                $filter_execs = $obj->executequery("SELECT userid, fullname FROM user WHERE usertype='sales' AND companyid=$companyid ORDER BY fullname ASC");
                                                foreach ($filter_execs as $row): ?>
                                                    <option value="<?= $row['userid'] ?>" <?= $row['userid'] == $emp_id ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($row['fullname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md mt-2">
                                            <button type="submit" class="btn btn-theme btn-sm">Load</button>
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-4 mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?>     </div>
                        <div class="card-body">
                            <?php if (empty($execs)): ?>
                                <div class="alert alert-warning mb-0">No sales executives found.</div>
                            <?php elseif (empty($behaviours)): ?>
                                <div class="alert alert-warning mb-0">No behaviour parameters defined.</div>
                            <?php else: ?>
                                <form action="" method="post">
                                    <input type="hidden" name="month" value="<?= $selMonth ?>">
                                    <input type="hidden" name="year" value="<?= $selYear ?>">
                                    <input type="hidden" name="emp_id" value="<?= $emp_id ?>">

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm table-hover">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>S. No.</th>
                                                    <th>Behaviour Parameter</th>
                                                    <th>Max Point</th>
                                                    <?php foreach ($execs as $ex): ?>
                                                        <th><?= htmlspecialchars($ex['fullname']) ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; foreach ($behaviours as $b): ?>
                                                    <tr>
                                                        <td class="text-center"><?= $i++ ?></td>
                                                        <td><?= ucfirst($b['name']) ?></td>
                                                        <td class="text-center"><?= $b['max_score'] ?></td>
                                                        <?php foreach ($execs as $ex):
                                                            $key = $ex['userid'] . "_" . $b['kra_behaviour_id'];
                                                            $val = isset($existing_scores[$key]) ? $existing_scores[$key] : '';
                                                        ?>
                                                            <td class="text-center">
                                                                <input type="number" step="0.01" min="0" max="<?= $b['max_score'] ?>"
                                                                    name="score_<?= $ex['userid'] ?>_<?= $b['kra_behaviour_id'] ?>"
                                                                    class="form-control form-control-sm score-input"
                                                                    value="<?= htmlspecialchars($val) ?>">
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="submit" name="submit" class="btn btn-theme btn-sm">Save Scores</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content Close-->
    </div>

</body>

<!-- Script tags -->
<?php include('component/script.php'); ?>
<script>
    $(document).ready(function() {
        $(".chosen-select").chosen();
    });
</script>

</html>