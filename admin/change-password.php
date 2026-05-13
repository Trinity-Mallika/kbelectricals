<?php
include("../adminsession.php");

$title = "Change Password";
$pagename = "change-password.php";
$tblname = "user";
$module = "Change Password";
$submodule = "Change Password";
if (isset($_GET['action']))
   $action = addslashes(trim($_GET['action']));
else
   $action = "";
if (isset($_POST['sub'])) {
   $oldpass = $_POST['oldpass'];
   $newpass = $_POST['newpass'];
   $confirmpass = $_POST['confirmpass'];
   $loginid = $_SESSION['userid'];
   $where = array('password' => $oldpass, 'userid' => $loginid);
   $res = $obj->count_method($tblname, $where);
   if ($res != 0) {
      if ($newpass == $confirmpass) {   //echo ("hii"); die;
         //$sql_data = mysqli   2qw3_query($con,"SET NAMES utf8");
         $where = array('userid' => $loginid);
         $fields = array('password' => $newpass);
         //print_r($fields);
         $sql_get = $obj->update_record("user", $where, $fields);
         $action = 2;
         echo "<script>location='$pagename?action=$action'</script>";
      } else
         echo "Password Not Matched";
   } else
      echo "<script>alert('Wrong Password')</script>";
   echo "<script>
closeframe();
function closeframe()
{
parent.location='change-password.php';
parent.jQuery.fancybox.close()
}
</script>";
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

      .card-header {
         background-color: #06163a;
      }
   </style>
   <script>
      function checkOldPass() {
         var oldpass = document.getElementById("oldpass").value;
         // test = "xyz";
         if (navigator.appName == "Microsoft Internet Explorer")
            obj3 = new ActiveXObject("Msxml2.XMLHTTP");
         else
            obj3 = new XMLHttpRequest();
         obj3.open("post", "check_old_pass.php?" + oldpass, true);
         obj3.send(null);
         obj3.onreadystatechange = function() {
            if (obj3.readyState == 4) {
               var idname = obj3.responseText;
               // alert(idname);
               //document.getElementById('msg').value = idname;
               document.getElementById('msg1').innerHTML = idname;
            }
         }
      }

      function checkPassEqual() {
         var newpass = document.getElementById("newpass").value;
         var confirmpass = document.getElementById("confirmpass").value;
         //alert(newpass);
         //alert(confirmpass);
         if (newpass != "" && confirmpass != "") {
            if (newpass == confirmpass) {

               document.getElementById('msg2').innerHTML = "Password matched";
               document.getElementById('msg2').style.color = "green";
            } else {

               document.getElementById('msg2').innerHTML = "Password not matched";
               document.getElementById('msg2').style.color = "red";
            }
         }
      }
   </script>
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
                  <form action="" method="post">
                     <div class="card">
                        <div class="card-header text-white">
                           Change Password
                        </div>
                        <div class="card-body">
                           <div class="mb-3 row">
                              <label for="oldpass" class="col-sm-2 col-form-label">Old Password <span class="text-danger fw-bold">*</span></label>
                              <div class="col-sm-4">
                                 <input type="text" class="form-control form-control-sm" name="oldpass" id="oldpass" onChange="checkOldPass()" placeholder="Old Password">
                                 <small id="msg1"></small>
                              </div>
                           </div>
                           <div class="mb-3 row">
                              <label for="newpass" class="col-sm-2 col-form-label">New Password <span class="text-danger fw-bold">*</span></label>
                              <div class="col-sm-4">
                                 <input type="password" class="form-control form-control-sm" name="newpass" id="newpass" maxlength="12" placeholder="New Password" onKeyUp="checkPassEqual()">
                              </div>
                           </div>
                           <div class="mb-3 row">
                              <label for="password" class="col-sm-2 col-form-label">Confirm Password</Address><span class="text-danger fw-bold"> *</span></label>
                              <div class="col-sm-4">
                                 <input type="text" class="form-control form-control-sm" name="confirmpass" id="confirmpass" placeholder="Confirm Password" autocomplete="off" onKeyUp="checkPassEqual()">
                                 <small id="msg2"></small>
                              </div>
                           </div>
                           <div class="mb-3 row">
                              <div class="col-sm-10 offset-sm-2">
                                 <button type="submit" name="sub" class="btn btn-success btn-sm" onClick="return checkinputmaster('oldpass,newpass,confirmpass')">Change</button>
                                 <a href="<?php echo $pagename; ?>" name="reset" id="reset" class="btn btn-danger btn-sm">Reset</a>
                              </div>
                           </div>
                        </div>
                     </div>
                  </form>
               </fieldset>
            </div>
         </div>
      </div>
      <!-- Content close-->
   </div>

</body>

<!-- script tag -->
<?php include('component/script.php'); ?>

<!-- script tag -->


</html>