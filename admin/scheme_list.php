<?php include("../adminsession.php");
$title = "Scheme Entry List";
$pagename = "scheme_list.php";
$module = "Scheme Entry List";
$submodule = "Scheme Entry List";
$btn_name = "Search";
$tblname = "scheme_entry";
$tblpkey = "scheme_id";
$keyvalue = (isset($_GET["scheme_id"])) ? $obj->test_input($_GET["scheme_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$imgpath = "uploaded/scheme_image/";

$fromdate = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', strtotime('-30 days'));
$todate   = isset($_GET['todate'])   ? $_GET['todate']   : date('Y-m-d');

$from = $fromdate . " 00:00:00";
$to   = $todate . " 23:59:59";

// $crit = "WHERE t.billdate BETWEEN '$from' AND '$to' and t.type='order' and t.companyid='$companyid'";

$createdby = isset($_GET['createdby']) ? $_GET['createdby'] : '';
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';

$crit = "WHERE createdate BETWEEN '$from' AND '$to' 
    ";




?>

<!DOCTYPE html>

<html lang="en">



<head>

    <!-- meta tag -->

    <?php include('component/css.php'); ?>

    <!-- meta tag -->



    <style>
        /* Chrome, Safari, Edge, Opera */

        input::-webkit-outer-spin-button,

        input::-webkit-inner-spin-button {

            -webkit-appearance: none;

            margin: 0;

        }



        /* Firefox */

        input[type=number] {

            -moz-appearance: textfield;

        }



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

        <!-- Header -->

        <?php include('component/header.php'); ?>

        <!-- Header Close-->

        <!-- Content -->

        <div class="container-fluid">

            <div class="row">

                <div class="col-lg-12">

                    <fieldset class="mt-2">

                        <legend><?= $module ?></legend>

                        <?php include('component/alert.php'); ?>

                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="card">

                                <div class="card-header text-white">

                                    <?= $module ?>

                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no"> From Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="fromdate" id="fromdate" placeholder="Owner Mobile No." value="<?php echo $fromdate; ?>" autocomplete="off">

                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="mobile_no">To Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <input type="date" class="form-control form-control-sm" name="todate" id="todate" placeholder="Office Mobile No." value="<?php echo $todate; ?>" autocomplete="off">

                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <input type="submit" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>" onclick="return checkinputmaster('scheme_name');">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
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
                            <?php echo $submodule; ?> List
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th>Sr. No.</th>
                                        <th>Scheme Name</th>
                                        <th>Image Name</th>
                                        <th>From date</th>
                                        <th>To Date</th>
                                        <th>Scheme Date</th>
                                        <th>Scheme View</th>
                                        <th class="text-center">Action</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $sql_get = $obj->executequery("select * from scheme_entry $crit order by scheme_id desc");
                                        foreach ($sql_get as $row_get) {
                                        ?>
                                            <tr>
                                                <td> <?php echo $slno++; ?></td>
                                                <td><?php echo $row_get['scheme_name']; ?></td>
                                                <td>
                                                    <a href="<?php echo $imgpath . $row_get['imgname'] ?>"><img src="<?php echo $imgpath . $row_get['imgname'] ?>" alt="" style="width: 100px;"></a>
                                                </td>
                                                <td><?php echo date('d-m-Y', strtotime($row_get['from_date'])); ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($row_get['todate'])); ?></td>
                                                <td><?php echo date('d-m-Y h:i:s A', strtotime($row_get['createdate'])); ?></td>
                                                <td><a href="scheme_view.php?scheme_id=<?php echo $row_get['scheme_id'] ?>" class="btn btn-sm btn-warning">View</a></td>
                                                <td class="text-center">
                                                    <a href="scheme_entry.php?scheme_id=<?php echo  $row_get['scheme_id']; ?>" title="Edit" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel(<?php echo  $row_get['scheme_id']; ?>);"><i class="bi bi-trash3-fill"></i></button>
                                                </td>
                                            </tr>
                                        <?php  } ?>
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
    $(document).ready(function() {
        $('#example').DataTable();
        $(".chosen-select").chosen();
    });

    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        pagename = '<?php echo $pagename; ?>';
        submodule = '<?php echo $submodule; ?>';
        module = '<?php echo $module; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {

            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_scheme_master.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close

    $(document).ready(function() {

        //called when key is pressed in textbox

        $("#mobile_no").keypress(function(e) {

            //if the letter is not digit then display error and don't type anything

            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {

                //display error message

                $("#errmsg").html("Digits Only").show().fadeOut("slow");

                return false;

            }

        });

    });
</script>


</html>