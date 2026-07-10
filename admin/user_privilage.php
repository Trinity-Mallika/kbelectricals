<?php include("../adminsession.php");

$pagename = "user_privilage.php";
$title = "User Privilage Entry";
$submodule = "User Privilage Master";
$btn_name = "Save";
$keyvalue = 0;
$tblname = "privilage_setting";
$tblpkey = "privilage_id";

if (isset($_GET['userid']))
   $userid = $_GET['userid'];
else
   $userid = '';

$dup = '';

$page_id = '';
$pagedit = "";
$pagedel = "";

if (isset($_GET['action']))
   $action = addslashes(trim($_GET['action']));
else
   $action = "";

if (isset($_POST['submit'])) {

   $userid = $_POST['userid'];
   $page_id = isset($_POST['page_id']) ? $_POST['page_id'] : '';
   $pagedit = isset($_POST['pagedit']) ? $_POST['pagedit'] : '';
   $pagedel = isset($_POST['pagedel']) ? $_POST['pagedel'] : '';

   if ($userid != '') {
      $where = array('userid' => $userid);
      $obj->delete_record($tblname, $where);
      for ($i = 0; $i < sizeof($page_id); $i++) {
         $form_data = array('userid' => $userid, 'page_id' => $page_id[$i], 'ipaddress' => $ipaddress, 'createdate' => $createdate, 'createdby' => $loginid);
         $obj->insert_record($tblname, $form_data);
         $action = 1;
         $process = "insert";
      }

      if (is_countable($pagedit) && count($pagedit) > 0) {
         foreach ($pagedit as $key_edit => $value_edit) {
            $where = array('userid' => $userid, 'page_id' => $key_edit);
            $fdata = array('pagedit' => $value_edit);
            $obj->update_record($tblname, $where, $fdata);
         }
      }
      if (is_countable($pagedel) && count($pagedel) > 0) {
         foreach ($pagedel as $key_del => $value_del) {
            $where = array('userid' => $userid, 'page_id' => $key_del);
            $fdata = array('pagedel' => $value_del);
            $obj->update_record($tblname, $where, $fdata);
         }
      }
   }
   echo "<script>location='$pagename?userid='$userid'&action=$action'</script>";
}

if (isset($_GET[$tblpkey])) {

   $btn_name = "Update";
}

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
            <div class="col-lg-12">
               <fieldset class="mt-2">
                  <?php include('component/alert.php'); ?>
                  <form action="" method="post">
                     <div class="card">
                        <div class="card-header text-white">
                           <?php echo $submodule; ?>
                        </div>
                        <div class="card-body">
                           <div class="row">
                              <div class="col-md-3">
                                 <strong> <label for="userid">Select User<span class="text-danger fw-bold"> *</span></label></strong>
                                 <div class="input-group mb-3">
                                    <select autofocus name="userid" id="userid" class="chosen-select form-control" onchange="getusertype(this.value);">
                                       <option value="">---Select User Type---</option>
                                       <?php
                                       $result = $obj->executequery("Select * from user where usertype NOt IN ('admin','sales') order by fullname asc");
                                       foreach ($result as $row_get) {
                                       ?>
                                          <option value="<?php echo $row_get['userid']; ?>"><?php echo $row_get['fullname']; ?></option>
                                       <?php  } ?>
                                    </select>
                                    <script>
                                       document.getElementById('userid').value = '<?php echo  $userid; ?>';
                                    </script>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php if ($userid > 0) { ?>
                        <div class="row mt-4 mb-4">
                           <div class="col-lg-12">
                              <div class="card">
                                 <div class="card-header text-white">
                                    <?php echo $submodule; ?> List
                                 </div>
                                 <div class="card-body">
                                    <div class="table-responsive">
                                       <table class="table table-bordered table-sm table-hover">
                                          <thead>
                                             <tr>
                                                <th width="13%">
                                                   <input type="checkbox" id="checkAllPages"> View
                                                </th>
                                                <th width="13%">
                                                   <input type="checkbox" id="checkAllEdit"> Edit
                                                </th>
                                                <th width="14%">
                                                   <input type="checkbox" id="checkAllDelete"> Delete
                                                </th>
                                             </tr>
                                          </thead>

                                          <?php
                                          $sql_get = $obj->executequery("SELECT * FROM m_userprivilege ORDER BY  page_id ASC");

                                          $current_menu = "";

                                          foreach ($sql_get as $row_get) {

                                             // Show Heading Once
                                             if ($current_menu != $row_get['menuname']) {
                                                $current_menu = $row_get['menuname'];
                                          ?>
                                                <tr>
                                                   <td colspan="3" style="background:#f8f9fa;border-top:2px solid #0d6efd;">
                                                      <span class="fw-bold text-primary fs-5">
                                                         <?= strtoupper($current_menu) ?>
                                                      </span>
                                                   </td>
                                                </tr>
                                             <?php
                                             }

                                             $page_id = $row_get['page_id'];

                                             $where = array(
                                                "page_id" => $page_id,
                                                "userid"  => $userid
                                             );

                                             $module_page = $obj->count_method("privilage_setting", $where);

                                             $pagedit = $obj->getvalfield(
                                                "privilage_setting",
                                                "pagedit",
                                                "page_id='$page_id' AND userid='$userid'"
                                             );

                                             $pagedel = $obj->getvalfield(
                                                "privilage_setting",
                                                "pagedel",
                                                "page_id='$page_id' AND userid='$userid'"
                                             );
                                             ?>

                                             <tr>
                                                <td style="width:30%">
                                                   <label class="fw-semibold" style="width:100%;">
                                                      <input type="checkbox"
                                                         name="page_id[]" class="view-permission"
                                                         value="<?= $page_id; ?>"
                                                         <?= ($module_page != 0) ? 'checked' : ''; ?>>
                                                      &nbsp;<?= $row_get['page_heading']; ?>
                                                   </label>
                                                </td>

                                                <td>
                                                   <label>
                                                      <input type="checkbox" class="edit-permission"
                                                         name="pagedit[<?= $page_id; ?>]"
                                                         value="1"
                                                         <?= ($pagedit == 1) ? 'checked' : ''; ?>>
                                                      &nbsp;<span class="fw-bold text-primary">Edit</span>
                                                   </label>
                                                </td>

                                                <td>
                                                   <label>
                                                      <input type="checkbox" class="delete-permission"
                                                         name="pagedel[<?= $page_id; ?>]"
                                                         value="1"
                                                         <?= ($pagedel == 1) ? 'checked' : ''; ?>>
                                                      &nbsp;<span class="fw-bold text-danger">Delete</span>
                                                   </label>
                                                </td>

                                             </tr>

                                          <?php } ?>
                                       </table>
                                    </div>

                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 text-center mb-2">
                           <button type="submit" name="submit" class="btn btn-primary" onClick="return checkinputmaster('userid'); ">
                              <?php echo $btn_name; ?></button>
                           <a href="<?php echo $pagename; ?>" name="reset" id="reset" class="btn btn-success">Reset</a>
                        </div>
                     <?php } ?>
                  </form>
               </fieldset>
            </div>
         </div>
      </div>

   </div>
   <!-- Content close-->



</body>

<!-- script tag -->
<?php include('component/script.php'); ?>
<!-- script tag -->
<script>
   $(document).ready(function() {
      $(".chosen-select").chosen({
         width: '100%'
      });
   });
</script>

<script>
   function getusertype(userid) {
      if (userid != '') {
         window.location.href = '?userid=' + userid;
      }
   }
</script>
<script>
   document.addEventListener("DOMContentLoaded", function() {
      document.getElementById("checkAllPages").addEventListener("change", function() {
         document.querySelectorAll("input[name='page_id[]']").forEach(cb => cb.checked = this.checked);
      });

      document.getElementById("checkAllEdit").addEventListener("change", function() {
         document.querySelectorAll("input[name^='pagedit']").forEach(cb => cb.checked = this.checked);
      });

      document.getElementById("checkAllDelete").addEventListener("change", function() {
         document.querySelectorAll("input[name^='pagedel']").forEach(cb => cb.checked = this.checked);
      });
   });

   $(".view-permission").change(function() {

      let row = $(this).closest("tr");

      if (!$(this).is(":checked")) {
         row.find(".edit-permission").prop("checked", false);
         row.find(".delete-permission").prop("checked", false);
      }
   });

   $(".edit-permission").change(function() {

      if ($(this).is(":checked")) {
         $(this).closest("tr")
            .find(".view-permission")
            .prop("checked", true);
      }
   });

   $(".delete-permission").change(function() {

      if ($(this).is(":checked")) {

         let row = $(this).closest("tr");

         row.find(".view-permission").prop("checked", true);

         // Optional
         row.find(".edit-permission").prop("checked", true);
      }
   });
</script>

</html>