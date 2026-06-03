<?php
include("appsession.php");

$current_date = date('Y-m-d');
$current_month_start = date('Y-m-01');
$start_window = date('Y-m-d', strtotime("$current_month_start -2 days"));
$end_window   = date('Y-m-d', strtotime("$current_month_start +3 day"));

$is_within_window = (
    $current_date >= $start_window &&
    $current_date <= $end_window
);

if (!$is_within_window) {

    echo '
    <div class="counter-card searchable-card text-center">

        <div class="py-4">

            <div class="mb-2" style="font-size:40px;">
                🔒
            </div>

            <div class="counter-name text-danger mb-2">
                Access Restricted
            </div>

            <div class="counter-meta" style="font-size:14px;">
                Monthly target can only be filled between
                <br>
                <strong>' . date('d M Y', strtotime($start_window)) . '</strong>
                to
                <strong>' . date('d M Y', strtotime($end_window)) . '</strong>
            </div>

        </div>

    </div>';

    exit();
}
$where = "";
$target_month = date('m', strtotime($current_month_start));
$target_year  = date('Y', strtotime($current_month_start));

$week_day = $_POST['week_day'];

if ($week_day != '') {
    $where .= " AND r.day_of_week='$week_day'";
}
$res = $obj->executequery("SELECT 
        rc.sequence,
        a.account_id,
        a.account_name,
        a.mobile_no,
        a.address,
        a.status,

        am.area_name,

        r.route_name,
        r.day_of_week,

        rp.sales_executive_id,
        rp.week_number

    FROM route_plan rp

    INNER JOIN route_counter rc 
        ON rc.batch_no = rp.batch_no

    INNER JOIN account a 
        ON a.account_id = rc.account_id

    LEFT JOIN area_master am 
        ON am.area_id = a.area_id

    LEFT JOIN route r 
        ON r.batch_no = rp.batch_no

    WHERE rp.companyid = '$companyid'

    AND rp.sales_executive_id = '$loginid'

    $where

    ORDER BY rc.sequence ASC

");

$approval_status = $obj->getvalfield(
    "monthly_target_approval",
    "status",
    "userid='$loginid'
    AND month='$target_month'
    AND year='$target_year'"
);
?>

<!-- SEARCH -->

<div class="search-wrapper sticky-top">

    <input type="text"
        id="counterSearch"
        class="form-control search-input"
        placeholder="Search counter...">

</div>

<?php if ($approval_status == 'Approved') { ?>

    <div class="alert alert-success mt-2">

        <strong>Target Approved</strong><br>

        Monthly target has been approved by management.
        Editing has been locked.

    </div>

<?php } ?>

<?php
$overall_total = 0;
$slno = 1;
foreach ($res as $key) {
    $target_id = (int)$obj->getvalfield(
        "monthly_target",
        "target_id",
        "month='$target_month'
        AND year='$target_year'
        AND account_id='$key[account_id]' and createdby='$loginid'"
    );

    $comment = $obj->getvalfield(
        "monthly_target",
        "comment",
        "target_id='$target_id'"
    );

?>

    <div class="counter-card searchable-card">

        <!-- TOP -->

        <div class="d-flex justify-content-between align-items-start gap-2">

            <!-- LEFT -->

            <div class="flex-grow-1 pe-2">

                <div class="counter-number">
                    #<?php echo str_pad($slno++, 2, '0', STR_PAD_LEFT) ?>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-1 mt-1">

                    <div class="counter-name">
                        <?php echo $key['account_name'] ?>
                    </div>



                </div>

                <div class="counter-meta">
                    <?php echo $key['area_name'] ?>
                    <span class="status-badge bg-<?php echo ($key['status'] == 'inactive')
                                                        ? 'danger'
                                                        : 'success' ?>">

                        <?php echo ucfirst($key['status']) ?>

                    </span>
                </div>

            </div>

            <!-- RIGHT -->
            <?php if ($approval_status == 'Approved') { ?>

                <span class="badge bg-success p-2">
                    Approved
                </span>

            <?php } elseif ($approval_status == 'Rejected') { ?>

                <span class="badge bg-danger p-2">
                    Rejected
                </span>

            <?php } else { ?>

                <button class="btn btn-sm"
                    type="button"
                    onclick="open_target_modal(
        '<?php echo $key['account_id'] ?>',
        '<?php echo $key['account_name'] ?>',
        '<?php echo $target_id ?>',
        '<?php echo $comment ?>'
    )">

                    <?php echo ($target_id > 0) ? 'Edit' : 'Add' ?>

                </button>

            <?php } ?>

        </div>

        <!-- BRAND TARGETS -->

        <?php

        $details = $obj->executequery("
            SELECT 
                td.*,
                cm.cat_name AS brand_name

            FROM monthly_target_details td

            LEFT JOIN category_master cm
                ON cm.cat_id = td.brand_id

            WHERE td.target_id='$target_id'

            AND td.account_id='$key[account_id]'

        ");

        ?>

        <div class="brand-list">

            <?php
            $total_target = 0;
            if (!empty($details)) {

                foreach ($details as $row) {

            ?>

                    <div class="brand-item">

                        <div class="brand-name">
                            <?php echo $row['brand_name'] ?>
                        </div>

                        <div class="brand-target">
                            ₹<?php echo number_format($row['target']) ?>
                        </div>

                    </div>

                <?php $total_target += $row['target'];
                }
                ?>
                <div class="brand-item">

                    <div class="brand-name">
                        Total
                    </div>

                    <div class="brand-target">
                        ₹<?php echo number_format($total_target) ?>
                    </div>

                </div>
            <?php
            } else {
            ?>

                <div class="empty-target">

                    No target added

                </div>

            <?php } ?>

        </div>

        <!-- COMMENT -->

        <?php if ($comment != '') { ?>

            <div class="comment-box">

                <?php echo $comment ?>

            </div>

        <?php } ?>

    </div>

<?php $overall_total += $total_target;
} ?>
<div class="counter-card mt-3 border border-primary">

    <div class="d-flex justify-content-between align-items-center">

        <div>
            <div class="counter-name text-primary">
                Overall Target
            </div>

            <div class="counter-meta">
                Total of all counters
            </div>
        </div>

        <div style="font-size:22px;font-weight:700;color:#0d6efd;">
            ₹<?= number_format($overall_total) ?>
        </div>

    </div>

</div>
<script>
    $("#counterSearch").on("keyup", function() {

        var value = $(this).val().toLowerCase();

        $(".searchable-card").filter(function() {

            $(this).toggle(

                $(this).text().toLowerCase().indexOf(value) > -1

            );

        });

    });
</script>