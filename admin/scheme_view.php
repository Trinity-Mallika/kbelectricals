<?php include("../adminsession.php");
$title = "Scheme Entry";
$pagename = "scheme_entry.php";
$module = "Scheme Entry";
$submodule = "Scheme Entry";
$tblname = "scheme_entry";
$tblpkey = "scheme_id";
$keyvalue = (isset($_GET["scheme_id"])) ? $obj->test_input($_GET["scheme_id"]) : 0;
$action = (isset($_GET["action"])) ? $obj->test_input($_GET["action"]) : "";
$imgpath = "uploaded/scheme_image/";


if (isset($_GET[$tblpkey])) {

    $where = array($tblpkey => $keyvalue);

    $sqledit = $obj->select_record($tblname, $where);

    $scheme_name  =  $sqledit['scheme_name'];

    $from_date  =  $sqledit['from_date'];

    $todate  =  $sqledit['todate'];

    $imgname  =  $sqledit['imgname'];

    $product_id  =  $sqledit['product_id'];

    $qty  =  $sqledit['qty'];

    $output  =  $sqledit['output'];

    $scheme_type  =  $sqledit['scheme_type'];
} else {

    $scheme_name  =  "";
    $from_date  =   "";
    $todate  =   "";
    $imgname  =   "";
    $product_id  =   "";
    $qty  =   "";
    $output  =   "";
    $scheme_type = "";
}



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


                        <form action="" method="post" enctype="multipart/form-data">

                            <div class="card">

                                <div class="card-header text-white">

                                    Scheme Detail
                                    
                                </div>

                                <div class="card-body">
                                    <div class="row">                                        
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Scheme Name <span class="text-danger fw-bold">*</span></label></strong>
                                            <span class="form-control form-control-sm"><?php echo $scheme_name; ?></span>
                                        </div>
                                        <?php if ($imgname != "") { ?>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="account_name">Scheme Image <span class="text-danger fw-bold"></span></label></strong>
                                            <br>
                                                <a href="<?php echo $imgpath . $imgname; ?>" target="_blank">
                                                    <img src="<?php echo $imgpath . $imgname; ?>"
                                                        alt="Scheme Image"
                                                        style="width:100px;">
                                                </a>
                                        </div>
                                        <?php } ?>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for=""> From Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <span class="form-control form-control-sm"><?php echo date('d-m-Y', strtotime($from_date)); ?></span>
                                        </div>
                                        
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for=""> From Date <span class="text-danger fw-bold"></span></label> </strong>
                                            <span class="form-control form-control-sm"><?php echo date('d-m-Y', strtotime($todate)); ?></span>
                                        </div>
                                        
                                        <div class="col-md-3 mb-2">
                                            <strong>
                                                <label>Scheme Type</label>
                                            </strong>

                                            <div class="d-flex align-items-center mt-2">

                                                <div class="form-check me-3">
                                                    <input class="form-check-input scheme_type"
                                                        type="radio"                                                      
                                                        <?php if ($scheme_type == 'qty_wise' || $scheme_type == '') echo 'checked'; ?> disabled>

                                                    <label class="form-check-label" for="qty_wise">
                                                        QTY. Wise
                                                    </label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input scheme_type"
                                                        type="radio"                                                        
                                                        <?php if ($scheme_type == 'amt_wise') echo 'checked'; ?> disabled>

                                                    <label class="form-check-label" for="amt_wise">
                                                        Amt. Wise
                                                    </label>
                                                </div>

                                            </div>
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
                            Scheme Entry Details
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">S. No.</th>
                                            <th>Product Name</th>
                                            <th>Unit</th>
                                            <th>Output</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php

                                        $i = 1;

                                        $sql = "
                                        SELECT
                                            td.*,
                                            p.product_name
                                        FROM scheme_details td
                                        LEFT JOIN product_master p
                                            ON p.product_id = td.product_id
                                        WHERE td.scheme_id = '$keyvalue'
                                        AND td.companyid = '$companyid'
                                        AND td.createdby = '$loginid'
                                        ORDER BY td.scheme_details_id DESC
                                        ";

                                        $res = $obj->executequery($sql);
                                        $row_count = count($res);

                                        if ($row_count > 0) {

                                            foreach ($res as $key) {

                                        ?>

                                                <tr>

                                                    <td class="text-center">
                                                        <?php echo $i++; ?>
                                                    </td>

                                                    <td>
                                                        <?php echo $key['product_name']; ?>
                                                    </td>

                                                    <td>
                                                        <?php

                                                        echo $key['qty'];

                                                        if ($key['scheme_type'] == 'qty_wise') {

                                                            echo ' Qty';

                                                        } else if ($key['scheme_type'] == 'amt_wise') {

                                                            echo ' Rs.';
                                                        }

                                                        ?>
                                                    </td>

                                                    <td>
                                                        <?php echo $key['output']; ?>
                                                    </td>

                                                </tr>

                                        <?php
                                            }
                                        } else {
                                        ?>

                                            <tr>
                                                <td colspan="4" class="text-center text-danger">
                                                    No Record Found
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
                url: 'ajax/delete_master.php',
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
<script>
    function changeQtyLabel() {

        let scheme_type = document.querySelector('input[name="scheme_type"]:checked').value;

        if (scheme_type == 'amt_wise') {

            document.getElementById('qty_label').innerHTML =
                'Amount <span class="text-danger fw-bold"></span>';

        } else {

            document.getElementById('qty_label').innerHTML =
                'QTY <span class="text-danger fw-bold"></span>';
        }
    }

    // page load
    changeQtyLabel();

    // radio change
    document.querySelectorAll('.scheme_type').forEach(function(el) {

        el.addEventListener('change', function() {
            changeQtyLabel();
        });

    });
</script>

</html>