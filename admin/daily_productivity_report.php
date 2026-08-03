<?php
include("../adminsession.php");
$title = "Daily Productivity Report";
$pagename = "daily_productivity_report.php";
$module = "Daily Productivity Report";
$submodule = "Sales Executive Productivity";
$btn_name = "Save";
$tblname = "daily_productivity";
$tblpkey = "daily_productivity_id";
$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d');
$todate   = isset($_GET['todate']) ? $_GET['todate'] : date('Y-m-d');

$sql = "
    SELECT 
        dp.*,
        u.fullname AS emp_name
    FROM daily_productivity dp
    LEFT JOIN user u ON u.userid = dp.emp_id
    WHERE dp.date BETWEEN '$fromdate' AND '$todate'
    ORDER BY dp.date DESC, u.fullname ASC
";

$qry = $obj->executequery($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
    <!-- meta tag -->
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
                <div class="col-lg-12 mb-2">
                    <form>
                        <div class="card mt-3">
                            <div class="card-header text-white">
                                <?php echo $module; ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong><label for="fromdate">From Date</label></strong>
                                        <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate"
                                            value="<?php echo $fromdate; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <strong><label for="todate">To Date</label></strong>
                                        <input type="date" class="form-control form-control-sm" name="todate" id="todate"
                                            value="<?php echo $todate; ?>">
                                    </div>
                                    <div class="col-md-3 mt-4">
                                        <input type="submit" class="btn btn-primary btn-sm" name="search" value="Search">
                                        <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" id="reset">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 mb-2">
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?> Record
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <div class="table-responsive">
                                    <table id="example" class="table table-bordered table-sm table-hover align-middle">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>Sr.</th>
                                                <th>Date</th>
                                                <th>Sales Executive</th>
                                                <th class="text-center">Visits</th>
                                                <th class="text-center">Total Counters</th>
                                                <th class="text-center">Coverage</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Detail</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $slno = 1;

                                            foreach ($qry as $row) {

                                                $visit_count     = (int)$row['visit_count'];
                                                $total_counters  = (int)$row['total_counters'];
                                                $active_counters = (int)$row['active_counters'];

                                                $coverage = $total_counters > 0
                                                    ? round(($visit_count / $total_counters) * 100, 1)
                                                    : 0;

                                                if ($coverage >= 80) {
                                                    $status = '<span class="badge bg-success">Good</span>';
                                                } elseif ($coverage >= 50) {
                                                    $status = '<span class="badge bg-warning text-dark">Average</span>';
                                                } else {
                                                    $status = '<span class="badge bg-danger">Low</span>';
                                                }
                                            ?>
                                                <tr>
                                                    <td><?= $slno++; ?></td>

                                                    <td>
                                                        <?= date('d-m-Y', strtotime($row['date'])); ?>
                                                    </td>

                                                    <td>
                                                        <strong><?= htmlspecialchars($row['emp_name'] ?? 'N/A'); ?></strong>
                                                    </td>

                                                    <td class="text-center">
                                                        <span class="badge bg-primary">
                                                            <?= $visit_count; ?>
                                                        </span>
                                                    </td>

                                                    <td class="text-center">
                                                        <?= $total_counters; ?>
                                                    </td>

                                                    <td class="text-center">
                                                        <strong><?= $coverage; ?>%</strong>
                                                    </td>

                                                    <td class="text-center">
                                                        <?= $status; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary"
                                                            onclick="viewProductivityDetail(
            <?= (int)$row['emp_id']; ?>,
            '<?= $row['date']; ?>',
            '<?= htmlspecialchars($row['emp_name'], ENT_QUOTES); ?>'
        )">
                                                            <i class="bi bi-eye"></i> View
                                                        </button>
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
    </div>
    <!-- Content close-->

    <div class="modal fade" id="productivityDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">
                            <i class="bi bi-person-lines-fill"></i>
                            Daily Productivity Detail
                        </h5>

                        <small class="text-muted" id="detailSubtitle"></small>
                    </div>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body" id="productivityDetailBody">

                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <div class="mt-2">Loading...</div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<script>
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });

    function viewProductivityDetail(emp_id, date, emp_name) {

        $('#detailSubtitle').html(
            '<strong>' + emp_name + '</strong> | ' + date
        );

        $('#productivityDetailBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <div class="mt-2">Loading productivity details...</div>
        </div>
    `);

        $('#productivityDetailModal').modal('show');

        $.ajax({
            url: 'ajax_daily_productivity_detail.php',
            type: 'POST',
            data: {
                emp_id: emp_id,
                date: date
            },
            success: function(response) {
                $('#productivityDetailBody').html(response);
            },
            error: function() {
                $('#productivityDetailBody').html(`
                <div class="alert alert-danger">
                    Unable to load productivity details.
                </div>
            `);
            }
        });
    }
</script>

</html>