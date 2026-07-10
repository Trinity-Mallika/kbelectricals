<?php
include("../adminsession.php");
$title = "Over Due List";
$pagename = "overdue_list.php";
$module = "Over Due List";
$submodule = "Over Due List";
$btn_name = "Save";
$tblname = "transaction_entry";
$tblpkey = "transaction_id";
$overdue = isset($_GET['overdue']) ? intval($_GET['overdue']) : 45;
$today   = date('Y-m-d');
$crit = "WHERE t.type='order' 
         AND t.is_approved = 1
         AND t.companyid='$companyid'
         AND t.invoice_no IS NOT NULL AND t.invoice_no <> ''
         AND DATEDIFF('$today', t.billdate) > '$overdue'
         AND t.transaction_id NOT IN (
             SELECT DISTINCT p.ref_bill_id
             FROM transaction_entry p
             WHERE p.type='payment'
               AND p.companyid='$companyid'
               AND p.ref_bill_id IS NOT NULL
         )";

$summaryCrit = "WHERE type='order'
                AND is_approved = 1
                AND companyid='$companyid'
                AND invoice_no IS NOT NULL AND invoice_no <> ''
                AND DATEDIFF('$today', billdate) > '$overdue'
                AND transaction_id NOT IN (
                    SELECT DISTINCT p.ref_bill_id
                    FROM transaction_entry p
                    WHERE p.type='payment'
                      AND p.companyid='$companyid'
                      AND p.ref_bill_id IS NOT NULL
                )";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- meta tag -->
    <?php include('component/css.php'); ?>
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

        .modal-card {
            background: aliceblue;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
        }
    </style>
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
            <div class="row mt-2">
                <div class="col-lg-12 mb-2">
                    <?php
                    $summary = $obj->executequery("SELECT
    COUNT(DISTINCT account_id) AS total_customers,
    COUNT(*) AS total_bills,
   SUM(
    CASE
        WHEN invoice_no IS NOT NULL
        AND invoice_no <> ''
        THEN invoice_amt
        ELSE 0
    END
) AS total_invoice_amt
FROM transaction_entry
$summaryCrit
");

                    $summary = $summary[0];
                    ?>
                    <div class="row g-3 mb-4">

                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card shadow-sm border-0 bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Total Customers</div>
                                    <h3 class="mb-0"><?= $summary['total_customers'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">No. Of Bills</div>
                                    <h3 class="mb-0"><?= $summary['total_bills'] ?? 0 ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6 col-6">
                            <div class="card shadow-sm border-0 bg-dark text-white h-100">
                                <div class="card-body text-center">
                                    <div class="small">Invoice Amount</div>
                                    <h4 class="mb-0">
                                        ₹<?= number_format($summary['total_invoice_amt'] ?? 0, 2) ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card mt-4">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?> Record
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <tr class="table-primary">
                                            <th>Sr No.</th>
                                            <th>Customer Details</th>
                                            <th>Mapped Counter To</th>
                                            <th>Route Name</th>
                                            <th>Invoices</th>
                                            <th>Total Overdue Amount</th>
                                            <th>Oldest Overdue</th>
                                            <th>Follow Up</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $slno = 1;

                                        $qry = $obj->executequery("SELECT
    t.*,
    a.account_name,
    rt.route_name,
    u.fullname,
    DATEDIFF('$today', t.billdate) AS days_overdue
FROM $tblname t

LEFT JOIN account a ON a.account_id = t.account_id
LEFT JOIN route_counter mc ON mc.account_id = a.account_id
LEFT JOIN route rt ON rt.batch_no = mc.batch_no
LEFT JOIN route_plan rp ON rp.batch_no = mc.batch_no
LEFT JOIN user u ON u.userid = rp.sales_executive_id

$crit

ORDER BY t.$tblpkey DESC
");

                                        // Group all overdue invoices under their customer (account)
                                        $grouped = [];
                                        foreach ($qry as $row) {
                                            $accId = $row['account_id'];
                                            if (!isset($grouped[$accId])) {
                                                $grouped[$accId] = [
                                                    'account_name' => $row['account_name'],
                                                    'fullname'     => $row['fullname'],
                                                    'route_name'   => $row['route_name'],
                                                    'invoices'     => [],
                                                    'total_amt'    => 0,
                                                    'max_days'     => 0,
                                                ];
                                            }
                                            $grouped[$accId]['invoices'][] = [
                                                'transaction_id' => $row['transaction_id'],
                                                'billno'         => $row['billno'],
                                                'billdate'       => $row['billdate'],
                                                'invoice_no'     => $row['invoice_no'],
                                                'invoice_amt'    => $row['invoice_amt'],
                                                'days_overdue'   => $row['days_overdue'],
                                            ];
                                            $grouped[$accId]['total_amt'] += $row['invoice_amt'];
                                            $grouped[$accId]['max_days'] = max($grouped[$accId]['max_days'], (int) $row['days_overdue']);
                                        }

                                        function overdueBadge($days)
                                        {
                                            $badgeClass = 'bg-warning text-dark';
                                            if ($days > 60) {
                                                $badgeClass = 'bg-dark';
                                            } elseif ($days > 30) {
                                                $badgeClass = 'bg-danger';
                                            }
                                            return '<span class="badge ' . $badgeClass . '">' . $days . ' day' . ($days == 1 ? '' : 's') . '</span>';
                                        }

                                        foreach ($grouped as $accId => $cust) {
                                            $detailId = 'detail_' . $accId;
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $slno++; ?></td>
                                                <td>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($cust['account_name']); ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($cust['fullname'] ?? '-'); ?></td>
                                                <td>
                                                    <?= !empty($cust['route_name']) ? htmlspecialchars($cust['route_name']) : '-'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        data-bs-toggle="collapse" data-bs-target="#<?= $detailId; ?>">
                                                        <i class="bi bi-list-ul"></i> <?= count($cust['invoices']); ?> Invoice<?= count($cust['invoices']) == 1 ? '' : 's'; ?>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">
                                                        ₹ <?= number_format($cust['total_amt'], 2); ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?= overdueBadge($cust['max_days']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary btnFollowUp"
                                                        data-id="<?= $accId; ?>"
                                                        data-customer="<?= htmlspecialchars($cust['account_name']); ?>">
                                                        <i class="bi bi-telephone-outbound"></i> Follow Up
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="8" class="p-0 border-0">
                                                    <div class="collapse" id="<?= $detailId; ?>">
                                                        <div class="p-2">
                                                            <table class="table table-sm table-bordered mb-0 bg-white">
                                                                <thead>
                                                                    <tr class="table-secondary">
                                                                        <th>Order No.</th>
                                                                        <th>Order Date</th>
                                                                        <th>Invoice No.</th>
                                                                        <th>Invoice Amount</th>
                                                                        <th>Overdue Days</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($cust['invoices'] as $inv) { ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($inv['billno']); ?></td>
                                                                            <td><?= $obj->dateformatindia($inv['billdate']); ?></td>
                                                                            <td><?= htmlspecialchars($inv['invoice_no']); ?></td>
                                                                            <td>₹ <?= number_format($inv['invoice_amt'], 2); ?></td>
                                                                            <td><?= overdueBadge((int) $inv['days_overdue']); ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
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
    <!-- Content close-->
    <div class="modal fade" id="followUpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Overdue Follow Up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="transaction_id">

                    <div class="modal-card">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Follow Date</label>
                                <input type="date" class="form-control form-control-sm" id="follow_date">
                            </div>

                            <div class="col-md-6">
                                <label>Remark</label>
                                <textarea class="form-control form-control-sm" id="remark" placeholder="Remark...."></textarea>
                            </div>

                            <div class="col-md-2">
                                <br>
                                <button class="btn btn-info btn-sm" id="btnSaveFollowup">Save</button>
                            </div>
                        </div>
                    </div>

                    <div id="followupList"></div>

                </div>
            </div>
        </div>
    </div>
</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $(".chosen-select").chosen();
    });

    $(document).on("click", ".btnFollowUp", function() {
        var transaction_id = $(this).data("id");
        $("#transaction_id").val(transaction_id);
        $("#follow_date").val("");
        $("#remark").val("");
        $("#followupList").load("ajax/followup_list.php", {
            transaction_id: transaction_id,
            type: 'overdue_pay'
        });

        var followModal = new bootstrap.Modal(document.getElementById('followUpModal'), {
            backdrop: 'static',
            keyboard: false
        });

        followModal.show();

    });


    $(document).on("click", "#btnSaveFollowup", function() {

        let follow_date = $("#follow_date").val();
        let remark = $("#remark").val().trim();

        if (follow_date == "") {
            Swal.fire("Please select follow up date");
            return;
        }

        if (remark == "") {
            Swal.fire("Please enter remark");
            return;
        }

        $.ajax({
            url: "ajax/save_followup.php",
            type: "POST",
            data: {
                transaction_id: $("#transaction_id").val(),
                follow_date: follow_date,
                remark: remark,
                type: "overdue_pay"
            },
            success: function(res) {

                if ($.trim(res) == "1") {

                    $("#follow_date").val("");
                    $("#remark").val("");

                    $("#followupList").load("ajax/followup_list.php", {
                        transaction_id: $("#transaction_id").val(),
                        type: "overdue_pay"
                    });

                    Swal.fire({
                        icon: "success",
                        title: "Follow up saved",
                        timer: 1200,
                        showConfirmButton: false
                    });

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Unable to save follow up"
                    });

                }

            }
        });

    });

    $(document).on("click", ".btnDeleteFollowup", function() {

        let followup_id = $(this).data("id");

        Swal.fire({
            title: "Delete?",
            text: "Do you want to delete this follow up?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Delete"
        }).then((result) => {

            if (result.isConfirmed) {

                $.post("ajax/delete_followup.php", {
                        followup_id: followup_id
                    },
                    function(res) {

                        if ($.trim(res) == "1") {

                            Swal.fire({
                                icon: "success",
                                title: "Deleted Successfully"
                            }).then(function() {

                                $("#followupList").load("ajax/followup_list.php", {
                                    transaction_id: $("#transaction_id").val(),
                                    type: "overdue_pay"
                                });

                            });

                        }

                    });

            }

        });

    });
</script>

</html>