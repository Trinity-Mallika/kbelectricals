<?php include("appsession.php");
$title = "Daily Entry List";
$fromdate = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d', strtotime('-7 days'));
$todate = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
$visit_id = (isset($_GET["visit_id"])) ? $obj->test_input($_GET["visit_id"]) : 0;
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
        .modal-detail-table th {
            width: 180px;
            color: #0d2c54;
            font-weight: 600;
            font-size: 14px;
        }

        .modal-detail-table td {
            font-size: 14px;
        }

        .modal-img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .remarks-box {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #eee;
        }

        .entry-card {
            background: #fff;
            border-radius: 18px;
            margin-bottom: 14px;
            padding: 6px;
            border: 1px solid #edf2f7;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
            transition: .25s;
        }

        .entry-card:active {
            transform: scale(.98);
        }

        .date-box {
            width: 58px;
            height: 72px;
            margin: auto;
            border-radius: 14px;
            background: linear-gradient(45deg, #135481, #169cd8);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 8px 20px rgba(13, 110, 253, .25);
        }

        .date-box h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        .date-box small {
            margin-top: 5px;
            font-size: 11px;
            opacity: .95;
            text-align: center;
            line-height: 1.2;
        }

        .entry-line {
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .entry-line:last-child {
            margin-bottom: 0;
        }

        .entry-line i {
            width: 18px;
            color: #6c757d;
            font-size: 15px;
        }

        .entry-line span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            max-width: 180px;
        }
    </style>
</head>

<body class="dashboard">
    <section class="top-sec ">
        <?php include("inc/header.php"); ?>

        <div class="container">
            <div class="row">
                <form action="" method="Post">
                    <div class="col-12 mt-2">
                        <div class="card border-0 shadow-lg mb-3 p-2">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo $fromdate ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $todate ?>">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary w-100 btn-sm">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="col-12 mb-4" id="dataContainer">


                </div>
                <div class="text-center mb-3" id="loading" style="display:none;">
                    <small>Loading...</small>
                </div>
                <div class="col-12">
                    <div class="card floating-btn p-1">
                        <a href="check-in.php" class="btn btn-primary w-100 btn-sm ">Next Check IN</a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="openModal" tabindex="-1" aria-labelledby="openModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 text-blue" id="openModalLabel">Entry Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="show_entry_data">

                </div>

            </div>
        </div>
    </div>


    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
    <script>
        function openModal(entry_id) {
            var myModal = new bootstrap.Modal(document.getElementById('openModal'), {
                keyboard: false
            });
            myModal.show();
            $.ajax({
                url: 'ajax_get_entry_details.php',
                type: 'POST',
                data: {
                    entry_id: entry_id
                },
                success: function(response) {
                    $('#show_entry_data').html(response);

                    $('#loader').hide();
                }
            });
        }

        function funDel(id, imgname) {

            tblname = 'daily_entries';
            tblpkey = 'entry_id';
            imgpath = 'uploads/daily_entry/';
            if (confirm("Are you sure! You want to delete this record.")) {

                jQuery.ajax({
                    type: 'POST',
                    url: 'ajax/delete_daily_entry.php',
                    data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&imgname=' + imgname + '&imgpath=' + imgpath,
                    dataType: 'html',
                    success: function(data) {
                        location.reload();
                    }
                }); //ajax close
            } //confirm close
        } //fun close


        let start = 0;
        let limit = 5;
        let loading = false;
        let allLoaded = false;

        $(document).ready(function() {

            loadData();

            $(window).scroll(function() {

                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 50) {

                    if (!loading && !allLoaded) {
                        start += limit;
                        loadData();
                    }
                }
            });

            $('form').on('submit', function(e) {
                e.preventDefault();
                start = 0;
                allLoaded = false;
                $('#dataContainer').html('');
                loadData();
            });
        });

        function loadData() {
            loading = true;
            $('#loading').show();
            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();

            $.ajax({
                url: 'ajax_daily_entries.php',
                type: 'POST',
                data: {
                    start: start,
                    from_date: from_date,
                    to_date: to_date
                },
                success: function(response) {
                    if (response.trim() == '') {
                        allLoaded = true;
                        $('#loading').html('<small>No more data</small>');
                    } else {
                        $('#dataContainer').append(response);
                        $('#loading').hide();
                    }

                    loading = false;
                }
            });
        }
    </script>
</body>


</html>