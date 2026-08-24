<?php
include("../adminsession.php");
$title = "Pending Payment List";
$pagename = "pending_payment.php";
$module = "Pending Payment List";
$submodule = "Pending Payment List";

$tblname = "transaction_entry";
$tblpkey = "transaction_id";
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

        .modal-card {
            background: aliceblue;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
        }

        .summary-card {
            border: 1px solid #e3e6ea;
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            height: 100%;
        }

        .summary-card .label {
            font-size: 12px;
            color: #6c757d;
        }

        .summary-card .value {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .badge-aging-fresh {
            background: #198754;
        }

        .badge-aging-warn {
            background: #fd7e14;
        }

        .badge-aging-danger {
            background: #dc3545;
        }

        .badge-ob-pending {
            background: #dc3545;
        }

        .badge-ob-clear {
            background: #198754;
        }

        .ob-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #dc3545;
            margin-left: 5px;
        }

        .btn-call-now {
            animation: pulseDanger 1.6s infinite;
        }

        @keyframes pulseDanger {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, .45);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
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
            <div class="card mt-4">
                <div class="card-header text-white">
                    <span><?php echo $module; ?> Record</span>
                </div>
                <div class="card-body">
                    <?php
                    $total_opening = (float)$obj->getvalfield(
                        "account",
                        "COALESCE(SUM(opening_balance),0)",
                        "1=1"
                    );

                    $total_opening_paid = (float)$obj->getvalfield(
                        "transaction_entry",
                        "COALESCE(SUM(grand_total),0)",
                        "type='payment'
         AND pay_type='opening'
         AND pay_status=1"
                    );

                    $net_opening = $total_opening - $total_opening_paid;
                    $filterCustomer         = isset($_GET['filterCustomer']) ? trim($_GET['filterCustomer']) : '';
                    $filterAging            = isset($_GET['filterAging']) ? trim($_GET['filterAging']) : '';
                    $filterOpeningOnly      = isset($_GET['filterOpeningOnly']);
                    $filterNotContactedOnly = isset($_GET['filterNotContactedOnly']);

                    $valid_aging_buckets = array('0-15', '16-30', '31-60', '60+');
                    if (!in_array($filterAging, $valid_aging_buckets, true)) {
                        $filterAging = '';
                    }

                    $total_order      = 0;
                    $total_invoice    = 0;
                    $total_paid       = 0;
                    $total_cash_disc  = 0;
                    $total_pending    = 0;
                    $ob_customers_count  = 0;
                    $ob_customers_amount = 0;
                    $seen_ob_accounts    = array();

                    $customer_filter_options = array();

                    $qry = $obj->executequery("SELECT
                            t.transaction_id,
                            t.account_id,
                            t.billno,
                            t.billdate,
                            t.grand_total,
                            t.invoice_no,
                            t.invoice_amt,
                            t.up_date,
                            t.remark,
                            a.account_name,
                            a.mobile_no,

                            COALESCE((
                                SELECT SUM(p.grand_total)
                                FROM transaction_entry p
                                WHERE p.ref_bill_id = t.transaction_id
                                AND p.type = 'payment'
                                AND p.pay_type = 'bill'
                                AND p.pay_status = 1
                            ), 0) AS paid_amount,

                            COALESCE((
                                SELECT SUM(p.cash_disc)
                                FROM transaction_entry p
                                WHERE p.ref_bill_id = t.transaction_id
                                AND p.type = 'payment'
                                AND p.pay_type = 'bill'
                                AND p.pay_status = 1
                            ), 0) AS cash_disc,

                            (
                                COALESCE(a.opening_balance,0)
                                - COALESCE((
                                    SELECT SUM(op.grand_total)
                                    FROM transaction_entry op
                                    WHERE op.account_id = a.account_id
                                    AND op.type = 'payment'
                                    AND op.pay_type = 'opening'
                                    AND op.pay_status = 1
                                ), 0)
                            ) AS opening_due,
                            (
                                SELECT MAX(f.follow_date)
                                FROM quotation_followup f
                                WHERE f.transaction_id = t.transaction_id
                                AND f.type = 'payment'
                            ) AS last_followup_date,

                            (
                                SELECT COUNT(*)
                                FROM quotation_followup f
                                WHERE f.transaction_id = t.transaction_id
                                AND f.type = 'payment'
                            ) AS followup_count

                        FROM $tblname t

                        LEFT JOIN account a
                            ON a.account_id = t.account_id

                        WHERE t.type = 'order'
                        AND t.is_approved = 1
                        AND t.invoice_no <> ''

                        ORDER BY t.transaction_id ASC

                    ");

                    $rows_html = "";
                    $slno = 1;
                    $today_ts = strtotime(date('Y-m-d'));

                    foreach ($qry as $rowget) {

                        $order_amount   = (float)$rowget['grand_total'];
                        $invoice_amount = (float)$rowget['invoice_amt'];
                        $paid_amount    = (float)$rowget['paid_amount'];
                        $cash_disc      = (float)$rowget['cash_disc'];
                        $opening_due    = (float)$rowget['opening_due'];

                        $pending_amount = $invoice_amount - $paid_amount - $cash_disc;

                        if ($pending_amount <= 0) {
                            continue;
                        }

                        $customer_filter_options[$rowget['account_name']] = true;
                        $invoice_date_raw = !empty($rowget['up_date']) ? $rowget['up_date'] : $rowget['billdate'];

                        $days_pending = 0;
                        if (!empty($rowget['billdate'])) {
                            $inv_ts = strtotime($rowget['billdate']);
                            if ($inv_ts !== false) {
                                $days_pending = max(0, (int)floor(($today_ts - $inv_ts) / 86400));
                            }
                        }

                        if ($days_pending <= 15) {
                            $aging_class = "badge-aging-fresh";
                            $aging_bucket = "0-15";
                        } elseif ($days_pending <= 30) {
                            $aging_class = "badge-aging-warn";
                            $aging_bucket = "16-30";
                        } elseif ($days_pending <= 60) {
                            $aging_class = "badge-aging-danger";
                            $aging_bucket = "31-60";
                        } else {
                            $aging_class = "badge-aging-danger";
                            $aging_bucket = "60+";
                        }

                        $last_followup_date = $rowget['last_followup_date'];
                        if (!empty($last_followup_date)) {
                            $fu_ts = strtotime($last_followup_date);
                            $days_since_contact = max(0, (int)floor(($today_ts - $fu_ts) / 86400));
                            $last_followup_display = $obj->dateformatindia($last_followup_date);
                        } else {
                            $days_since_contact = 999999;
                            $last_followup_display = '<span class="text-danger fw-semibold">Never Contacted</span>';
                        }

                        if ($days_since_contact === 999999) {
                            $btn_class = "btn-danger btn-call-now";
                            $btn_label = "Follow Up";
                        } elseif ($days_since_contact >= 7) {
                            $btn_class = "btn-warning";
                            $btn_label = "Follow Up Due";
                        } else {
                            $btn_class = "btn-outline-secondary";
                            $btn_label = "Follow Up";
                        }

                        $ob_flag = $opening_due > 0 ? "yes" : "no";

                        if ($filterCustomer !== '' && strcasecmp(ucwords($rowget['account_name']), $filterCustomer) !== 0) {
                            continue;
                        }
                        if ($filterAging !== '' && $aging_bucket !== $filterAging) {
                            continue;
                        }
                        if ($filterOpeningOnly && $opening_due <= 0) {
                            continue;
                        }
                        if ($filterNotContactedOnly && $days_since_contact !== 999999) {
                            continue;
                        }

                        $total_order     += $order_amount;
                        $total_invoice   += $invoice_amount;
                        $total_paid      += $paid_amount;
                        $total_cash_disc += $cash_disc;
                        $total_pending   += $pending_amount;

                        if ($opening_due > 0 && !isset($seen_ob_accounts[$rowget['account_id']])) {
                            $seen_ob_accounts[$rowget['account_id']] = true;
                            $ob_customers_count++;
                            $ob_customers_amount += $opening_due;
                        }

                        $rows_html .= '<tr>';
                        $rows_html .= '<td>' . $slno++ . '</td>';
                        $rows_html .= '<td>
                            <div class="fw-semibold">' . ucwords($rowget['account_name']) .'</div>
                        </td>';
                        $rows_html .= '<td>' . htmlspecialchars($rowget['mobile_no']) . '</td>';
                        $rows_html .= '<td><span class="fw-semibold">' . htmlspecialchars($rowget['billno']) . '</span></td>';
                        $rows_html .= '<td>' . $obj->dateformatindia($rowget['billdate']) . '</td>';
                        $rows_html .= '<td><span class="fw-semibold">' . htmlspecialchars($rowget['invoice_no']) . '</span></td>';
                        $rows_html .= '<td>' . (!empty($rowget['up_date']) ?  date('d-m-Y   ', strtotime($rowget['up_date'])) : '-') . '</td>';
                        $rows_html .= '<td data-order="' . $days_pending . '" class="text-center">
                            <span class="badge ' . $aging_class . '">' . $days_pending . ' d</span>
                        </td>';

                        $rows_html .= '<td class="text-end" data-order="' . $order_amount . '">' . number_format($order_amount, 2) . '</td>';
                        $rows_html .= '<td class="text-end" data-order="' . $invoice_amount . '">' . number_format($invoice_amount, 2) . '</td>';
                        $rows_html .= '<td class="text-end text-success" data-order="' . $paid_amount . '">' . number_format($paid_amount, 2) . '</td>';
                        $rows_html .= '<td class="text-end" data-order="' . $cash_disc . '">' . number_format($cash_disc, 2) . '</td>';
                        $rows_html .= '<td class="text-end" data-order="' . $pending_amount . '">
                            <span class="badge bg-danger fs-6">Rs ' . number_format($pending_amount, 2) . '</span>
                        </td>';

                        $rows_html .= '<td class="text-end" data-order="' . $opening_due . '">' .
                            ($opening_due > 0
                                ? '<span class="badge badge-ob-pending">Rs ' . number_format($opening_due, 2) . '</span>'
                                : '-')
                            . '</td>';

                        $rows_html .= '<td>' . $last_followup_display . '</td>';
                        $rows_html .= '<td class="d-none">' . $days_since_contact . '</td>';
                        $rows_html .= '<td class="text-center">
                            <button
                                type="button"
                                class="btn btn-sm ' . $btn_class . ' btnFollowUp"
                                data-id="' . $rowget['transaction_id'] . '"
                                data-customer="' . htmlspecialchars($rowget['account_name']) . '"
                                data-mobile="' . htmlspecialchars($rowget['mobile_no']) . '"
                                data-order="' . htmlspecialchars($rowget['billno']) . '"
                                data-invoice="' . htmlspecialchars($rowget['invoice_no']) . '"
                                data-pending="' . $pending_amount . '"
                                data-opening="' . $opening_due . '">
                                <i class="bi bi-telephone-outbound"></i> ' . $btn_label . '
                            </button>
                        </td>';
                        $rows_html .= '<td class="d-none">' . $aging_bucket . '</td>';
                        $rows_html .= '<td class="d-none">' . $ob_flag . '</td>';
                        $rows_html .= '</tr>';
                    }

                    ksort($customer_filter_options);
                    ?>

                    <div class="row row-cols-2 row-cols-md-5 g-2 mb-3">

                        <div class="col">
                            <div class="summary-card">
                                <div class="label">Opening Balance (All)</div>
                                <div class="value">&#8377; <?php echo number_format($net_opening, 2); ?></div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="summary-card">
                                <div class="label">Customers w/ Opening Due</div>
                                <div class="value text-danger">
                                    <?php echo $ob_customers_count; ?>
                                    <small class="text-muted">(&#8377; <?php echo number_format($ob_customers_amount, 2); ?>)</small>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="summary-card">
                                <div class="label">Total Invoiced</div>
                                <div class="value">&#8377; <?php echo number_format($total_invoice, 2); ?></div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="summary-card">
                                <div class="label">Total Paid</div>
                                <div class="value text-success">&#8377; <?php echo number_format($total_paid, 2); ?></div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="summary-card">
                                <div class="label">Total Pending</div>
                                <div class="value text-danger">&#8377; <?php echo number_format($total_pending, 2); ?></div>
                            </div>
                        </div>

                    </div>

                    <form method="get" action="<?php echo htmlspecialchars($pagename); ?>">
                        <div id="filterBar" class="row g-2 mb-2 align-items-end">
                            <div class="col-md-3 col-6">
                                <label class="form-label mb-1">Customer</label>
                                <select name="filterCustomer" id="filterCustomer" class="form-select form-select-sm chosen-select">
                                    <option value="">All Customers</option>
                                    <?php foreach ($customer_filter_options as $cname => $x): ?>
                                        <?php $cname_display = ucwords($cname); ?>
                                        <option value="<?php echo htmlspecialchars($cname_display); ?>"
                                            <?php echo (strcasecmp($cname_display, $filterCustomer) === 0) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cname_display); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 col-6">
                                <label class="form-label mb-1">Over Due</label>
                                <select name="filterAging" id="filterAging" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="0-15" <?php echo $filterAging === '0-15' ? 'selected' : ''; ?>>0-15 days</option>
                                    <option value="16-30" <?php echo $filterAging === '16-30' ? 'selected' : ''; ?>>16-30 days</option>
                                    <option value="31-60" <?php echo $filterAging === '31-60' ? 'selected' : ''; ?>>31-60 days</option>
                                    <option value="60+" <?php echo $filterAging === '60+' ? 'selected' : ''; ?>>60+ days</option>
                                </select>
                            </div>

                            <div class="col-md-3 col-6 d-flex align-items-center">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="filterOpeningOnly" id="filterOpeningOnly"
                                        <?php echo $filterOpeningOnly ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="filterOpeningOnly">
                                        Opening balance due only
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3 col-6 d-flex align-items-center">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="filterNotContactedOnly" id="filterNotContactedOnly"
                                        <?php echo $filterNotContactedOnly ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="filterNotContactedOnly">
                                        Never contacted only
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3 col-6 d-flex align-items-center gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <a href="<?php echo htmlspecialchars($pagename); ?>" class="btn btn-sm btn-outline-danger">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white">
                    <span><?php echo $submodule; ?> Record</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">

                        <table id="example" class="table table-bordered table-sm table-hover align-middle">

                            <thead class="table-primary">
                                <tr>
                                    <th>SrNo.</th>
                                    <th>Customer Name</th>
                                    <th>Mobile No.</th>
                                    <th>Order No.</th>
                                    <th>Order Date</th>
                                    <th>Invoice No.</th>
                                    <th>Invoice Date</th>
                                    <th class="text-center">Overdue Days</th>
                                    <th class="text-end">Order Amount</th>
                                    <th class="text-end">Invoice Amount</th>
                                    <th class="text-end">Paid Amount</th>
                                    <th class="text-end">Cash Disc.</th>
                                    <th class="text-end">Pending Amount</th>
                                    <th class="text-end">Opening Bal Due</th>
                                    <th>Last Follow-up</th>
                                    <th class="d-none">Days Since Contact</th>
                                    <th class="text-center">Follow Up</th>
                                    <th class="d-none">Aging Bucket</th>
                                    <th class="d-none">Opening Flag</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php echo $rows_html; ?>
                            </tbody>

                            <tfoot>
                                <tr class="fw-bold">
                                    <th colspan="7" class="text-end">Total</th>
                                    <th></th>
                                    <th class="text-end"><?php echo number_format($total_order, 2); ?></th>
                                    <th class="text-end"><?php echo number_format($total_invoice, 2); ?></th>
                                    <th class="text-end text-success"><?php echo number_format($total_paid, 2); ?></th>
                                    <th class="text-end"><?php echo number_format($total_cash_disc, 2); ?></th>
                                    <th class="text-end text-danger"><?php echo number_format($total_pending, 2); ?></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>

                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="followUpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Payment Follow Up
                        <small class="text-muted d-block" id="followUpSubtitle" style="font-size: 13px;"></small>
                    </h5>
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
    <!-- Content close-->
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
        var customer = $(this).data("customer");
        var mobile = $(this).data("mobile");
        var orderNo = $(this).data("order");
        var invoiceNo = $(this).data("invoice");
        var pending = parseFloat($(this).data("pending")) || 0;
        var opening = parseFloat($(this).data("opening")) || 0;

        $("#transaction_id").val(transaction_id);
        $("#follow_date").val("");
        $("#remark").val("");

        $("#followUpSubtitle").text(
            customer + " | " + mobile + " | Order #" + orderNo + " | Invoice #" + invoiceNo +
            " | Pending: Rs " + pending.toFixed(2)
        );

        if (opening > 0) {
            $("#openingDueAmt").text(opening.toFixed(2));
        }

        $("#followupList").load("ajax/followup_list.php", {
            transaction_id: transaction_id,
            type: 'payment'
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
                type: "payment"
            },
            success: function(res) {

                if ($.trim(res) == "1") {

                    $("#follow_date").val("");
                    $("#remark").val("");

                    $("#followupList").load("ajax/followup_list.php", {
                        transaction_id: $("#transaction_id").val(),
                        type: "payment"
                    });

                    Swal.fire({
                        icon: "success",
                        title: "Follow up saved",
                        timer: 1200,
                        showConfirmButton: false
                    }).then(function() {
                        // Reload so aging/last-contact badges refresh
                        location.reload();
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
                                    type: 'payment'
                                });

                            });

                        }

                    });

            }

        });

    });
</script>

</html>