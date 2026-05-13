<?php
include("appsession.php");

$where = "";

$month_filter = $_POST['month_filter'];
$week_day     = $_POST['week_day'];

if ($week_day != '') {
    $where .= " AND r.day_of_week='$week_day'";
}
$res = $obj->executequery("

    SELECT 
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

$current_month = date('m');
$current_year  = date('Y');

?>

<!-- SEARCH -->

<div class="search-wrapper sticky-top">

    <input type="text"
        id="counterSearch"
        class="form-control search-input"
        placeholder="Search counter...">

</div>

<?php

foreach ($res as $key) {

    $target_id = (int)$obj->getvalfield(
        "monthly_target",
        "target_id",
        "month='$current_month'
        AND year='$current_year'
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
                    #<?php echo str_pad($key['sequence']++, 2, '0', STR_PAD_LEFT) ?>
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

                <?php
                }
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

<?php } ?>

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