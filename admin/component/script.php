<script src="assets/js/jquery-3.6.0.min.js"></script>

<script src="assets/js/bootstrap.bundle.min.js"></script>

<script src="assets/choosen-select/chosen.jquery.min.js"></script>

<script src="assets/datatable/js/jquery.dataTables.min.js"></script>

<script src="assets/datatable/js/dataTables.bootstrap5.min.js"></script>

<script src="assets/datepicker/js/bootstrap-datepicker.js"></script>

<script src="assets/js/sweetalert.min.js"></script>

<script src="assets/js/custom.js"></script>

<script src="js/commonfun.js"></script>

<div class="modal fade" id="companyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Select a Company</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" id="company_id" onchange="set_company(this.value);">
                    <option value="">Select</option>
                    <?php $comps = $obj->executequery("SELECT * FROM company_setting");
                    foreach ($comps as $comp) { ?>
                        <option value="<?= $comp['company_id'] ?>"><?= $comp['company_name'] ?></option>
                    <?php } ?>
                </select>
                <script>
                    document.getElementById('company_id').value = '<?php echo $companyid ?>'
                </script>
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
    $('#companyModal').click(function() {
        $('#companyModal').modal('show');
    });

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
</script>