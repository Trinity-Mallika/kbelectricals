<?php
include("../adminsession.php");
$title = "Quotation List";
$pagename = "quotation_list.php";
$module = "Quotation List";
$submodule = "Quotation List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$type = "quotation";
// From to date search
if (isset($_GET['fromdate']) && isset($_GET['todate'])) {
    $fromdate = $obj->test_input($_GET['fromdate']);
    $todate = $obj->test_input($_GET['todate']);
} else {
    $fromdate = date('Y-m-d');
    $todate = date('Y-m-d');
}
$crit = " and billdate between '$fromdate' and '$todate'";

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
                <div class="col-lg-12 mt-2">
                    <a href="quotation.php" class="btn btn-success btn-sm float-end">ADD New +</a>
                </div>
                <div>
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

                    <div class="card mt-4">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?> Record
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>SrNo.</th>
                                            <th>Quotation No.</th>
                                            <th>Company Name</th>
                                            <th>Account Name</th>
                                            <th>Mobile No.</th>
                                            <th>Quotation Date</th>
                                            <th>Remark</th>
                                            <th style="text-align: right;">Net_Amount</th>
                                            <th style="text-align: center;">Print</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;
                                        $total_amt = 0;
                                        $qry = $obj->executequery("
                                            SELECT 
                                                t.*,
                                                a.account_name,
                                                a.mobile_no,
                                                c.company_name
                                            FROM $tblname t
                                            LEFT JOIN account a 
                                                ON a.account_id = t.account_id
                                            LEFT JOIN company_setting c 
                                                ON c.company_id = t.company_id
                                            WHERE t.type = '$type' $crit
                                            ORDER BY t.transaction_id DESC
                                        ");
                                        foreach ($qry as $rowget) {
                                        ?>
                                            <tr>
                                                <td><?php echo $slno++; ?></td>
                                                <td><?php echo $rowget['billno']; ?></td>
                                                <td><?php echo ucfirst($rowget['company_name']); ?></td>
                                                <td><?php echo ucfirst($rowget['account_name']); ?></td>
                                                <td><?php echo ($rowget['mobile_no']); ?></td>
                                                <td><?php echo $obj->dateformatindia($rowget['billdate']); ?></td>
                                                <td><?php echo ucfirst($rowget['remark']); ?></td>
                                                <td style="text-align:right;"><?php echo number_format($rowget['net_total_amt'], 2); ?></td>
                                                <td style="text-align: center;">
                                                    <a href="quotation_pdf.php?transaction_id=<?php echo $rowget['transaction_id']; ?>" class="btn btn-primary btn-sm" target="_blank">
                                                        <i class="bi bi-printer-fill"></i>
                                                    </a>
                                                </td>
                                                <td>

                                                    <a class="btn btn-sm btn-outline-success" href="quotation.php?transaction_id=<?php echo $rowget['transaction_id']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                </td>
                                                <td>

                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel('<?php echo $rowget['transaction_id']; ?>');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                            $total_amt += $rowget['net_total_amt'];
                                        } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6">Total</th>
                                            <th style="text-align: right;"><?php echo number_format($total_amt, 2); ?></th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content close-->
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

<script>
    function funDel(id) {
        tblname = '<?php echo $tblname; ?>';
        tblpkey = '<?php echo $tblpkey; ?>';
        pagename = '<?php echo $pagename; ?>';
        submodule = '<?php echo $submodule; ?>';
        type = '<?php echo $type; ?>';
        module = '<?php echo $module; ?>';
        if (confirm("Are you sure! You want to delete this record.")) {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax/delete_quotation.php',
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module + "&type=" + type,
                dataType: 'html',
                success: function(data) {
                    location = '<?php echo $pagename . "?action=3"; ?>' + '&search=search';
                }
            }); //ajax close
        } //confirm close
    } //fun close
</script>

</html>