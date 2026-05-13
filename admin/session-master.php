<?php

include("../adminsession.php");

$title = "Session Master";

$pagename = "session-master.php";

$module = "Master";

$submodule = "SESSION MASTER";

$btn_name = "Save";

$keyvalue = 0;

$tblname = "m_session";

$tblpkey = "sessionid";

//print_r($sessionid);die;

if (isset($_GET['sessionid']))

   $keyvalue = $_GET['sessionid'];

else

   $keyvalue = 0;

if (isset($_GET['action']))

   $action = addslashes(trim($_GET['action']));

else

   $action = "";

$dup = "";

$fromdate = $todate =  $session_name = "";

$date = date('d-m-Y');



if (isset($_GET['st'])) {

   $st = $_GET['st'];

   $s = $_GET['status'];

   if ($s != '') {

      $where = array('status' => 1);

      $myArray = array("status" => 0);

      $obj->update_record($tblname, $where, $myArray);

      $where = array($tblpkey => $st);

      $myArray = array("status" => 1);

      $obj->update_record($tblname, $where, $myArray);
   }
}

if (isset($_POST['submit'])) {



   $keyvalue = $obj->test_input($_POST['sessionid']);



   $fromdate =  $obj->test_input($_POST['fromdate']);



   $todate  =  $obj->test_input($_POST['todate']);



   $session_name =  $obj->test_input($_POST['session_name']);



   //check Duplicate



   $cwhere = array("session_name" => $_POST['session_name']);



   $count = $obj->count_method("m_session", $cwhere);



   //print_r($count);



   if ($count > 0 && $keyvalue == 0) {

      $dup = "<div class='alert alert-danger'>

			<strong>Error!</strong> Duplicate Record.

			</div>";

      //echo $dup; die;

   } else //insert

   {

      if ($keyvalue == 0) {

         $form_data = array('fromdate' => $fromdate, 'todate' => $todate, 'session_name' => $session_name, 'ipaddress' => $ipaddress, 'createdate' => $createdate);

         $obj->insert_record($tblname, $form_data);

         $action = 1;

         $process = "insert";
      } else {

         //update

         $form_data = array('fromdate' => $fromdate, 'todate' => $todate, 'session_name' => $session_name, 'ipaddress' => $ipaddress, 'lastupdated' => $createdate);

         $where = array($tblpkey => $keyvalue);

         $keyvalue = $obj->update_record($tblname, $where, $form_data);

         $action = 2;

         $process = "updated";
      }

      echo "<script>location='$pagename?action=$action'</script>";
   }
}

if (isset($_GET[$tblpkey])) {

   $btn_name = "Update";

   $where = array($tblpkey => $keyvalue);

   $sqledit = $obj->select_record($tblname, $where);

   $fromdate = $sqledit['fromdate'];

   $todate = $sqledit['todate'];

   $session_name = $sqledit['session_name'];
} else {

   $fromdate = date('Y-m-d');

   $todate = date('Y-m-d');
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



      /* Firefox */

      input[type=number] {

         -moz-appearance: textfield;

      }



      .toggle {

         height: 0;

         width: 0;

         visibility: hidden;

      }



      .lswitch {

         cursor: pointer;

         text-indent: -9999px;

         width: 50px;

         height: 25px;

         background: grey;

         display: block;

         border-radius: 100px;

         position: relative;

         margin-top: -20px;



      }



      .lswitch:after {

         content: '';

         position: absolute;

         top: 4px;

         left: 5px;

         width: 20px;

         height: 18px;

         background: #fff;

         border-radius: 90px;

         transition: 0.3s;

      }



      .toggle:checked+label {

         background: #bada55;

      }



      .toggle:checked+label:after {

         left: calc(100% - 3px);

         transform: translateX(-100%);

      }



      .lswitch:active:after {

         width: 30px;

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

                  <legend><?php echo $title; ?></legend>

                  <?php include('component/alert.php'); ?>

                  <form action="" method="POST">

                     <?php echo  $dup;  ?>

                     <div class="card">

                        <div class="card-header text-white">

                           Session Master

                        </div>

                        <div class="card-body">

                           <div class="row">

                              <div class="col-md mb-2">

                                 <label for="fromdate">From Date <span class="text-danger fw-bold">*</span></label>

                                 <input type="date" autofocus class="form-control form-control-sm" name="fromdate" id="fromdate" placeholder='dd-mm-yyyy' value="<?php echo  $fromdate; ?>">

                              </div>

                              <div class="col-md mb-2">

                                 <label for="todate">To Date <span class="text-danger fw-bold">*</span></label>

                                 <input type="date" class="form-control form-control-sm" name="todate" id="todate" placeholder='dd-mm-yyyy' value="<?php echo  $todate; ?>">

                              </div>

                              <div class="col-md mb-2">

                                 <label for="session_name">Session <span class="text-danger fw-bold">*</span></label>

                                 <input type="text" class="form-control form-control-sm" name="session_name" id="session_name" value="<?php echo $session_name; ?>" autocomplete="off" placeholder="yyyy-yy">

                              </div>

                              <div class="col-md mb-2">

                                 <br />

                                 <input type="submit" class="btn btn-theme btn-sm" name="submit" value="<?php echo $btn_name; ?>" onClick="return checkinputmaster('fromdate,todate,session_name');">

                                 <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm" name="reset" id="reset"> Reset </a>

                              </div>





                           </div>

                           <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">

                        </div>

                     </div>

                  </form>

               </fieldset>

            </div>

         </div>

         <div class="row mt-4 mb-4">

            <div class="col-lg-12">

               <div class="card">

                  <div class="card-header text-white">

                     <?php echo $submodule; ?> RECORD

                  </div>

                  <div class="card-body">

                     <div class="table-responsive">

                        <table id="dyntable" class="table table-bordered table-sm table-hover">

                           <thead>

                              <th>Sr. No.</th>

                              <th>From Date</th>

                              <th>To Date</th>

                              <th>Session Name</th>

                              <th>Status</th>



                              <th class="text-center">Action</th>

                           </thead>

                           <tbody>



                              <?php

                              $slno = 1;

                              $res = $obj->executequery("select * from m_session order by sessionid desc");

                              foreach ($res as $row_get) {

                              ?>

                                 <tr>

                                    <td><?php echo $slno++; ?></td>

                                    <td><?php echo $obj->dateformatindia($row_get['fromdate']); ?></td>

                                    <td><?php echo $obj->dateformatindia($row_get['todate']); ?></td>

                                    <td><?php echo $row_get['session_name']; ?></td>

                                    <td class="text-center">

                                       <input class="toggle" type="checkbox" id="switch<?php echo $row_get['sessionid']; ?>" onClick="return change_status('<?php echo $row_get['sessionid']; ?>','<?php echo $row_get['status']; ?>');" <?php if ($row_get['status'] == 1) {

                                                                                                                                                                                                                                             echo "checked";
                                                                                                                                                                                                                                          } ?> />

                                       <label class="lswitch" for="switch<?php echo $row_get['sessionid']; ?>">Toggle</label>

                                    </td>

                                    <td class="text-center">

                                       <a href="session-master.php?sessionid=<?php echo $row_get['sessionid']; ?>" title="Edit" class="btn btn-sm btn-outline-success">

                                          <i class="bi bi-pencil-square"></i>

                                       </a>
                                       <button type="button" title="Delete" class="btn btn-sm btn-danger" onclick="funDel(<?php echo $row_get['sessionid']; ?>);"><i class="bi bi-trash3-fill"></i></button>


                                    </td>

                                 </tr>

                              <?php  } ?>

                           </tbody>

                        </table>

                     </div>



                  </div>

               </div>

            </div>

         </div>

      </div>

      <!-- Content close-->

   </div>



</body>



<!-- script tag -->

<?php include('component/script.php'); ?>

<!-- script tag -->

<script>
   $(document).ready(function() {

      // $('.nice-select').niceSelect();

      // $('#example').DataTable();

      $('#fromdate').inputmask("99-99-9999");

      $('#todate').inputmask("99-99-9999");

      $('#session_name').inputmask("9999-99");

   });



   // change Status

   function change_status(st, status) {

      if (st != "") {

         if (confirm("Are you sure! You want to active this session.")) {

            location = '<?php echo $pagename; ?>?st=' + st + '&status=' + status;

         } else {

            location = '<?php echo $pagename; ?>';

         }

      }

   }

   // delete operation

   function funDel(id) { //alert(id);



      tblname = '<?php echo $tblname; ?>';



      tblpkey = '<?php echo $tblpkey; ?>';



      pagename = '<?php echo $pagename; ?>';



      submodule = '<?php echo $submodule; ?>';



      module = '<?php echo $module; ?>';



      //alert(module);



      if (confirm("Are you sure! You want to delete this record.")) {



         jQuery.ajax({



            type: 'POST',



            url: 'ajax/delete_master.php',



            data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule + '&pagename=' + pagename + '&module=' + module,



            dataType: 'html',



            success: function(data) {



               //alert(data);



               location = '<?php echo $pagename . "?action=3"; ?>';



            }



         }); //ajax close



      } //confirm close



   } //fun close
</script>



</html>