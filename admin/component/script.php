<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/choosen-select/chosen.jquery.min.js"></script>
<script src="assets/datatable/js/jquery.dataTables.min.js"></script>
<script src="assets/datatable/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/datepicker/js/bootstrap-datepicker.js"></script>
<script src="assets/js/sweetalert.min.js"></script>
<script src="assets/js/custom.js"></script>
<script src="js/commonfun.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


<div class="modal fade" id="companyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">
                    Select Company
                </h5>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>

            <div class="modal-body">

                <select class="form-select" id="company_id" onchange="set_company(this.value)">
                    <option value="">Select Company</option>

                    <?php
                    $comps = $obj->executequery("SELECT * FROM company_setting ORDER BY company_id");

                    foreach ($comps as $comp) {
                    ?>
                        <option value="<?= $comp['company_id']; ?>"
                            <?= (isset($_SESSION['companyid']) && $_SESSION['companyid'] == $comp['company_id']) ? 'selected' : ''; ?>>
                            <?= $comp['company_name']; ?>
                        </option>
                    <?php } ?>

                </select>

            </div>

        </div>
    </div>
</div>

<script>
    <?php if (isset($_SESSION['companyid']) == '' || isset($_SESSION['companyid']) == 0) { ?>
        $(document).ready(function() {
            $('#companyModal').modal('show');
        });
    <?php } ?>

    function set_company(company_id = '') {
        if (company_id != '') {
            jQuery.ajax({
                type: 'POST',
                url: 'ajax_setcompany.php',
                data: 'company_id=' + company_id,
                dataType: 'html',
                success: function(data) {
                    location.reload();
                }
            }); //ajax close
        }
    }

    let show_user = '<?= $_SESSION['usertype'] ?>';

    $(document).ready(function() {

        $(".chosen-select").chosen({
            width: "100%",
            search_contains: true
        });

        let options = {
            pageLength: 100,

            lengthMenu: [
                [100, 200, 500, -1],
                [100, 200, 500, "All"]
            ]
        };

        if (show_user === "admin") {

            options.dom =
                "<'row align-items-center mb-3'" +
                "<'col-md-3'l>" +
                "<'col-md-5 text-center'B>" +
                "<'col-md-4'f>" +
                ">" +
                "rt" +
                "<'row mt-3'" +
                "<'col-md-6'i>" +
                "<'col-md-6'p>" +
                ">";

            options.buttons = [{
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Download PDF',
                    className: 'btn btn-danger btn-sm'
                },
                {
                    extend: 'print',
                    text: 'Print Table',
                    className: 'btn btn-primary btn-sm'
                }
            ];
        }

        $('#example').DataTable(options);

    });
</script>