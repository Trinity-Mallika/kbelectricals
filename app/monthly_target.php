<?php include("appsession.php");
$title = "Monthly Target";
$fromdate = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d', strtotime('-7 days'));
$todate = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
$tblname = 'transaction_entry';
$tblpkey = 'transaction_id';
$where = "";
$data = $obj->getRouteDashboardData($loginid, $companyid);
$route_plan_id = $data['route_plan_id'];
if (!empty($_GET['week_day'])) {

    $week_day = $_GET['week_day'];

    $where .= "     AND r.day_of_week = '$week_day'";
} else {
    $where = "";
    $week_day = date('l');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>
    <style>
        /* SEARCH */

        .search-wrapper {
            position: sticky;
            top: 60px;
            z-index: 10;
            padding-bottom: 10px;
        }

        .search-input {
            height: 48px;
            border: 2px solid #124872;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .06);
            padding-left: 15px;
        }

        /* CARD */

        .counter-card {
            background: #fff;
            border-radius: 22px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
        }

        /* LEFT */

        .counter-name {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
            color: #0f172a;
        }

        .status-badge {
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 30px;
            color: #fff;
            font-weight: 600;
            line-height: 1;
        }

        .counter-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
            line-height: 1.3;
        }

        /* BUTTON */

        .add-btn {
            border-radius: 12px;
            min-width: 75px;
            height: 38px;
            font-weight: 600;
        }

        /* BRAND LIST */

        .brand-list {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .brand-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f7ff;
            border-radius: 14px;
            padding: 5px 10px;
            box-shadow: 0px 1px 2px #d9d9d9;
        }

        .brand-name {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }

        .brand-target {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        /* EMPTY */

        .empty-target {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
        }

        /* COMMENT */

        .comment-box {
            margin-top: 12px;
            background: #fff7ed;
            border-left: 4px solid #f97316;
            border-radius: 12px;
            padding: 10px;
            font-size: 12px;
            color: #7c2d12;
        }

        #ajax_loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 99999;
        }

        .loader-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="row">

                <div class="col-12 mt-2">
                    <form onsubmit="return false;">
                        <div class="card border-0 shadow-lg mb-3 p-2">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label><strong>Month</strong></label>
                                    <select name="month_filter" id="month_filter" class="form-control">
                                        <option value="">--Select Month--</option>

                                        <?php
                                        $current_month = date('m'); // 01,02,03 format

                                        for ($m = 1; $m <= 12; $m++) {

                                            $month_value = str_pad($m, 2, '0', STR_PAD_LEFT); // 01,02,03
                                            $month_name  = date('F', mktime(0, 0, 0, $m, 1));

                                            $selected = ($month_value == $current_month) ? 'selected' : '';

                                            echo "<option value='$month_value' $selected>$month_name</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Days Filter -->
                                <div class="col-6 mb-3">
                                    <label><strong>Week Days</strong></label>
                                    <select name="week_day" id="week_day" class="form-control">
                                        <option value="">--Select Day--</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                    </select>
                                    <script>
                                        document.getElementById('week_day').value = '<?php echo $week_day ?>'
                                    </script>
                                </div>
                                <div class="col-12">
                                    <button type="button"
                                        onclick="load_monthly_target()"
                                        class="btn btn-primary w-100 btn-sm">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <form action="">
                <div class="row">
                    <div class="col-12 mb-5" id="target_data">



                    </div>
                    <div class="text-center mb-3" id="loading" style="display:none;">
                        <small>Loading...</small>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="tragetAdd" tabindex="-1" aria-labelledby="tragetAddLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="tragetAddLabel">Add Target</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <input type="hidden" id="account_id">
                            <input type="hidden" id="month">
                            <input type="hidden" id="year">
                            <input type="hidden" id="target_id">
                            <label for="">Brand</label>

                            <select name="brand_id" id="brand_id" class="form-select form-control shadow-sm">
                                <option value="">--Select Brand--</option>
                                <?php
                                $sql = $obj->executequery("select * from category_master where type='brand' order by cat_id DESC ");
                                foreach ($sql as $key) {
                                ?> <option value="<?php echo $key['cat_id'] ?>"><?php echo $key['cat_name'] ?></option> <?php } ?>

                            </select>


                        </div>
                        <div class="col-12 mb-3">
                            <label for="">Target</label>
                            <input type="text" class="form-control" id="target" placeholder="Enter Traget">
                        </div>
                        <div class="col-12 mb-3">
                            <button type="button" class="btn btn-sm w-100" onclick="add_target_row()">Add</button>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="card bg-success-subtle border-0 p-2 mb-2 mt-1">
                                <table class="table table-bordered table-sm mb-0">
                                    <tr>
                                        <th>Brand</th>
                                        <th>Target</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                    <tbody id="target_list">

                                    </tbody>

                                </table>

                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <textarea id="comment"
                                class="form-control form-control-sm"
                                placeholder="Comment"></textarea>
                        </div>


                        <div class="col-12 ">
                            <a onclick="save_monthly_target()" class="btn btn-sm w-100">Save</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>

    <div id="ajax_loader" style="display:none;">
        <div class="loader-box">
            <div class="spinner-border text-primary"></div>
        </div>
    </div>

</body>

<script>
    function show_loader() {
        $("#ajax_loader").show();
    }

    function hide_loader() {
        $("#ajax_loader").hide();
    }
    load_monthly_target();

    function add_target_row() {
        var account_id = $("#account_id").val();

        var month = $("#month").val();

        var year = $("#year").val();

        var brand_id = $("#brand_id").val();

        var target = $("#target").val();
        var target_id = $("#target_id").val();



        if (brand_id == '') {

            alert("Select Brand");
            return false;
        }



        if (target == '') {

            alert("Enter Target");


            return false;
        }


        show_loader();

        $.ajax({

            url: "save_target_details.php",

            type: "POST",

            data: {

                details_account_id: account_id,
                month: month,
                year: year,
                brand_id: brand_id,
                target_id: target_id,
                target: target

            },

            success: function(res) {
                // alert(res);
                hide_loader();

                if (res == 1) {
                    $("#brand_id").val('');

                    $("#target").val('');

                    load_target_list1(target_id);
                } else if (res == 2) {

                    alert("Brand Already Added");

                } else {

                    alert("Something Went Wrong");

                }
            }

        });
    }

    function load_monthly_target() {

        var month_filter = $("#month_filter").val();
        var week_day = $("#week_day").val();
        show_loader();

        $.ajax({
            url: "load_monthly_target.php",
            type: "POST",
            data: {
                month_filter: month_filter,
                week_day: week_day
            },
            success: function(res) {
                hide_loader();

                $("#target_data").html(res);
            }
        });
    }

    function save_monthly_target() {
        var account_id = $("#account_id").val();
        var month = $("#month").val();
        var year = $("#year").val();
        var target_id = $("#target_id").val();
        var total_target = $("#total_target").val();
        var comment = $("#comment").val();


        show_loader();

        $.ajax({

            url: "save_target_details.php",

            type: "POST",

            data: {

                target_account_id: account_id,
                month: month,
                comment: comment,
                year: year,
                total_target: total_target,
                target_id: target_id,

            },

            success: function(res) {
                console.log(res);
                hide_loader();

                if (res == 1) {
                    load_monthly_target();
                    $("#tragetAdd").modal('hide');

                } else if (res == 2) {
                    load_monthly_target();
                    $("#tragetAdd").modal('hide');


                } else {

                    alert("Something Went Wrong");

                }
            }

        });
    }

    function load_target_list1(target_id = 0) {
        var account_id = $("#account_id").val();

        var month = $("#month").val();

        var year = $("#year").val();


        show_loader();

        $.ajax({

            url: "load_target_list.php",

            type: "POST",

            data: {

                account_id: account_id,
                month: month,
                target_id1: target_id,
                year: year

            },

            success: function(res) {
                // console.log(res);
                hide_loader();
                $("#target_list").html(res);
            }

        });
    }

    function delete_target(target_details_id, target_id) {
        tblname = 'monthly_target_details';
        tblpkey = 'target_details_id';
        show_loader();

        $.ajax({

            url: "delete_master.php",

            type: "POST",

            data: {

                id: target_details_id,
                tblpkey: tblpkey,
                tblname: tblname,

            },

            success: function(res) {
                hide_loader();
                load_target_list1(target_id);
            }

        });


    }



    function open_target_modal(account_id, account_name, target_id = 0, comment) {
        $("#account_id").val(account_id);
        $("#comment").val(comment);
        $("#target_id").val(target_id);
        // alert(account_id);
        $("#tragetAddLabel").html(
            'Add Target - ' + account_name
        );


        $("#month").val($("#month_filter").val());

        $("#year").val(new Date().getFullYear());

        $("#tragetAdd").modal('show');


        load_target_list1(target_id);
    }
</script>

</html>