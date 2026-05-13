<?php
include("../adminsession.php");

$title = "Route Wise Counter";
$submodule = "Route Wise Counter";
$pagename = "route_wise_counter.php";
$tblname = "route_counter";
$tblpkey = "route_counter_id";
$btn_name = "Save";
$keyvalue = $_GET[$tblpkey] ?? 0;
$action = $_GET['action'] ?? 0;
$batch_no = $_GET['batch_no'] ?? $obj->getvalfield($tblname, "batch_no", "1=1 order by $tblpkey desc");

if (isset($_POST['submit'])) {
    $batch_no   = $obj->test_input($_POST['batch_no']);
    $account_id   = $obj->test_input($_POST['account_id']);
    $sequence   = $obj->test_input($_POST['sequence']);

    $dup = $obj->getvalfield(
        $tblname,
        "count(*)",
        "batch_no='$batch_no'
         AND account_id='$account_id'
         AND companyid='$companyid'
         AND $tblpkey!='$keyvalue'"
    );

    if ($dup > 0) {
        $action = 4;
        $process = "Counter already assigned in this route";
    } else {

        if ($keyvalue == 0) {

            $obj->insert_record($tblname, [
                'batch_no'   => $batch_no,
                'account_id' => $account_id,
                'sequence'   => $sequence,
                'createdate' => $createdate,
                'ipaddress'  => $ipaddress,
                'companyid'  => $companyid,
                'createdby'  => $loginid
            ]);

            $action = 1;
        } else {

            $obj->update_record(
                $tblname,
                [$tblpkey => $keyvalue],
                [
                    'batch_no'     => $batch_no,
                    'account_id'   => $account_id,
                    'sequence'     => $sequence,
                    'lastupdated'  => $createdate
                ]
            );

            $action = 2;
        }

        echo "<script>location='$pagename?action=$action'</script>";
        exit;
    }
}

if ($keyvalue != 0) {
    $btn_name = "Update";
    $row = $obj->select_record($tblname, [$tblpkey => $keyvalue]);
    $batch_no   = $row['batch_no'];
    $account_id = $row['account_id'];
    $sequence   = $row['sequence'];
} else {
    $account_id = "";
    $sequence = $obj->getvalfield(
        $tblname,
        "ifnull(max(sequence),0)+1",
        "batch_no='$batch_no' AND companyid='$companyid'"
    );
}
?>

<!DOCTYPE html>

<html lang="en">



<head>
    <!-- meta tag -->

    <?php include('component/css.php'); ?>

    <!-- meta tag -->

    <style>
        .card-header {
            background-color: #06163a;
        }
    </style>

</head>

<body class="bg-light">
    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- heading -->
        <?php include('component/header.php'); ?>
        <!-- heading Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $title; ?></legend>
                        <?php include('component/alert.php'); ?>
                        <div class="card">
                            <div class="card-header text-white">
                                <?php echo $title; ?>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <strong><label for="batch_no">Route <span class="text-danger fw-bold">*</span></label></strong>
                                            <select name="batch_no" id="batch_no" class="form-select chosen-select" onchange="get_url(this.value);">
                                                <option value="">Select Route</option>
                                                <?php
                                                $res = $obj->executequery("SELECT batch_no,route_name,GROUP_CONCAT(day_of_week ORDER BY FIELD(day_of_week,'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday'
                    ) SEPARATOR ', ') AS days FROM route
            WHERE companyid='$companyid'
            GROUP BY batch_no, route_name
            ORDER BY route_name
        ");

                                                foreach ($res as $row) {
                                                    echo "<option value='{$row['batch_no']}'>
                    {$row['route_name']} [{$row['days']}]
                  </option>";
                                                }
                                                ?>
                                            </select>
                                            <script>
                                                document.getElementById('batch_no').value = '<?= $batch_no ?>';
                                            </script>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <strong><label for="route_id">Counter <span class="text-danger fw-bold">*</span></label></strong>
                                            <select name="account_id" id="account_id" class="form-select chosen-select">
                                                <option value="">Select Counter</option>
                                                <?php
                                                $res = $obj->executequery("
                    SELECT a.account_id,a.account_name,ar.area_name
                    FROM account a
                    LEFT JOIN area_master ar ON ar.area_id=a.area_id
                    WHERE a.status1='1'
                    AND a.companyid='$companyid'
                    ORDER BY a.account_name
                ");

                                                foreach ($res as $r) {
                                                    echo "<option value='$r[account_id]'>
                            $r[account_name] / $r[area_name]
                          </option>";
                                                }
                                                ?>
                                            </select>
                                            <script>
                                                document.getElementById('account_id').value = '<?= $account_id ?>';
                                            </script>
                                        </div>


                                        <div class="col-md-2 mb-2">
                                            <strong><label for="route_id">Sequence <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="number" name="sequence" value="<?= $sequence ?>" class="form-control form-control-sm">
                                        </div>


                                        <div class="col-md-2 mt-4">
                                            <input type="submit" name="submit" value="<?= $btn_name ?>" class="btn btn-theme btn-sm" onclick="return checkinputmaster('route_id,account_id,sequence');">
                                            <a href="<?= $pagename ?>" class="btn btn-danger btn-sm">Reset</a>
                                        </div>

                                    </div>
                                </form>

                            </div>

                        </div>

                    </fieldset>

                </div>

            </div>

            <div class="row mt-4 mb-4">

                <div class="col-lg-12">

                    <div class="card">

                        <div class="card-header text-white">

                            <?php echo $submodule; ?> RECORD

                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th>Sr. No.</th>
                                        <th>Route / Week Name</th>
                                        <th>Counter Name</th>
                                        <th>Sequence</th>
                                        <th>Action</th>

                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = $obj->executequery("
    SELECT
        t.*,
        r.route_name,
        GROUP_CONCAT(
            DISTINCT r.day_of_week
            ORDER BY FIELD(
                r.day_of_week,
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            )
            SEPARATOR ', '
        ) AS days,
        a.account_name
    FROM $tblname t
    JOIN route r
        ON t.batch_no = r.batch_no
    JOIN account a
        ON t.account_id = a.account_id
    WHERE t.companyid='$companyid'
      AND t.batch_no='$batch_no'
      AND a.status1='1'
    GROUP BY t.route_counter_id
    ORDER BY t.sequence DESC
");

                                        foreach ($sql as $key) { ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo $key['route_name']; ?> / <?= $key['days']; ?></td>
                                                <td><?php echo $key['account_name']; ?></td>
                                                <td><?php echo $key['sequence']; ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename . "?" . $tblpkey . "=" . $key['route_counter_id']; ?>"
                                                        title="Edit" class="btn btn-sm btn-outline-success"><i
                                                            class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger"
                                                        onclick="funDel('<?php echo $key['route_counter_id']; ?>');"><i
                                                            class="bi bi-trash3-fill"></i></button>
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
        <!-- Content close-->
    </div>
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<!-- script tag -->

<script>
    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';

        if (confirm("Are you sure! You want to delete this record.")) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_master.php',
                data: 'id=' + id +
                    '&tblname=' + tblname +
                    '&tblpkey=' + tblpkey,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close

    function get_url(route_id) {
        if (route_id > 0) {
            location = "?batch_no=" + route_id;
        }
    }

    $(document).ready(function() {
        $(".chosen-select").chosen();
        $('#example').DataTable();
    });
</script>



</html>