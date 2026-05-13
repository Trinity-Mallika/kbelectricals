<?php include("appsession.php");
$title = "Order List";
$fromdate = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d', strtotime('-7 days'));
$todate = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
$tblname = 'transaction_entry';
$tblpkey = 'transaction_id';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>KBELECTRICAL</title>
    <!-- css links  files -->
    <?php include("inc/css-file.php"); ?>

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
                        <a href="my-order.php" class="btn btn-primary w-100 btn-sm ">Add Order</a>
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
                    <h1 class="modal-title fs-5 text-blue" id="openModalLabel">Transaction Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="show_transaction_data">

                </div>

            </div>
        </div>
    </div>


    <!-- js script files -->
    <?php include("inc/js-file.php"); ?>
    <script>
        function openModal(transaction_id) {
            var myModal = new bootstrap.Modal(document.getElementById('openModal'), {
                keyboard: false
            });
            myModal.show();
            $.ajax({
                url: 'ajax_order_details.php',
                type: 'POST',
                data: {
                    transaction_id: transaction_id
                },
                success: function(response) {
                    $('#show_transaction_data').html(response);

                    $('#loader').hide();
                }
            });
        }

        function funDel(id) {

            tblname = '<?php echo $tblname; ?>';
            tblpkey = '<?php echo $tblpkey; ?>';
            if (confirm("Are you sure! You want to delete this record.")) {

                jQuery.ajax({
                    type: 'POST',
                    url: '../admin/ajax/delete_quotation.php',
                    data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey,
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

            // 🔥 SCROLL EVENT
            $(window).scroll(function() {

                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 50) {

                    if (!loading && !allLoaded) {
                        start += limit;
                        loadData();
                    }
                }
            });

            // 🔥 FILTER SUBMIT
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
                url: 'ajax_order_list.php',
                type: 'POST',
                data: {
                    start: start,
                    from_date: from_date,
                    to_date: to_date
                },
                success: function(response) {

                    if (response.trim() == '') {
                        allLoaded = true; // no more data
                        $('#loading').html('<small>No data</small>');
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