<?php include("../adminsession.php");

$title = "Page Privellege";
$pagename = "privellege.php";
$module = "Page Privellege";
$submodule = "Page Privellege";
$btn_name = "Save";
$keyvalue = 0;
$tblname = "m_userprivilege";
$tblpkey = "page_id";

if (isset($_GET['page_id'])) {
    $keyvalue = $_GET['page_id'];
}

if (isset($_GET['action'])) {
    $action = addslashes(trim($_GET['action']));
} else {
    $action = "";
}



if (isset($_POST['submit'])) {
    //print_r($_POST); die;
    $menuname = $obj->test_input($_POST['menuname']);
    $page_heading = $obj->test_input($_POST['page_heading']);
    $pagelink = $obj->test_input($_POST['pagelink']);

    //check Duplicate

     $count = $obj->getvalfield($tblname, "count(*)", "pagelink='$pagelink' and $tblpkey !='$keyvalue'");
    if ($count == 0) {
        if ($keyvalue == 0) {
            //insert
            $form_data = array('menuname' => $menuname, 'pagelink' => $pagelink, 'page_heading' => $page_heading, 'ipaddress' => $ipaddress, 'createdate' => $createdate, 'createdby' => $loginid);
            $obj->insert_record($tblname, $form_data);
            $action = 1;
            $process = "insert";
            echo "<script>location='$pagename?action=$action'</script>";
        } else {
            //update
            $form_data = array('menuname' => $menuname, 'pagelink' => $pagelink, 'page_heading' => $page_heading, 'ipaddress' => $ipaddress, 'lastupdated' => $createdate, 'createdby' => $loginid);
            $where = array($tblpkey => $keyvalue);
            $keyvalue = $obj->update_record($tblname, $where, $form_data);
            $action = 2;
            $process = "updated";
        }
    } else {
        $action = 4;
    }
    echo "<script>location='$pagename?action=$action'</script>";
}

if (isset($_GET[$tblpkey])) {
    $btn_name = "Update";
    $where = array($tblpkey => $keyvalue);
    $sqledit = $obj->select_record($tblname, $where);
    $menuname =  $sqledit['menuname'];
    $page_heading =  $sqledit['page_heading'];
    $pagelink =  $sqledit['pagelink'];
} else {
    $menuname =  $obj->getvalfield($tblname, "menuname", "1=1 order by  $tblpkey desc");
    $page_heading =  "";
    $pagelink =  "";
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
</head>



<body class="bg-light">
    <!-- Sidebar -->
    <?php include('component/sidebar.php'); ?>
    <!-- Sidebar Close-->
    <div class="main w-auto">
        <!-- heading -->
        <?php include('component/header.php'); ?>
        <!-- heading Close-->
        <!-- Content -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <fieldset class="mt-2">
                        <legend><?php echo $title; ?></legend>
                        <?php include('component/alert.php'); ?>
                        <div class="card">
                            <div class="card-header text-white">
                                <?php echo $title; ?>
                            </div>
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="category">MenuName <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="menuname" id="menuname" value="<?php echo $menuname; ?>" placeholder="Enter MenuName" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="category">Page Heading <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="page_heading" id="page_heading" value="<?php echo $page_heading; ?>" placeholder="Enter Page Heading" autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong> <label for="category">Page Link <span class="text-danger fw-bold">*</span></label></strong>
                                            <input type="text" class="form-control form-control-sm" name="pagelink" id="pagelink" value="<?php echo $pagelink; ?>" placeholder="Enter Page Link " autocomplete="off">
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <input type="submit" onclick="return checkinputmaster('menuname,page_heading,pagelink')" name="submit" class="btn btn-theme btn-sm" value="<?php echo $btn_name; ?>">
                                            <input type="hidden" name="<?php echo $tblpkey; ?>" id="<?php echo $tblpkey; ?>" value="<?php echo $keyvalue; ?>">
                                            <a href="<?php echo $pagename; ?>" class="btn btn-danger btn-sm"> Reset </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <div class="row mt-4 mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-white">
                            <?php echo $submodule; ?> Record
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-bordered table-sm table-hover">
                                    <thead>
                                        <th>S.No.</th>
                                        <th>MenuName</th>
                                        <th>Page Heading</th>
                                        <th>Page Link</th>
                                        <th class="text-center">Edit</th>
                                        <th class="text-center">Delete</th>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $slno = 1;
                                        $res = $obj->executequery("select * from $tblname order by $tblpkey desc");
                                        foreach ($res as $row_get) {
                                        ?>
                                            <tr>
                                                <td><?php echo $slno++; ?></td>
                                                <td><?php echo $row_get['menuname'] ?></td>
                                                <td><?php echo $row_get['page_heading'] ?></td>
                                                <td><?php echo $row_get['pagelink'] ?></td>
                                                <td class="text-center">
                                                    <a href="<?php echo $pagename; ?>?page_id=<?php echo $row_get['page_id']; ?>"
                                                        title="Edit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>&nbsp;&nbsp;&nbsp;
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" title="Delete" class="btn btn-sm btn-danger"
                                                        onclick="funDel(<?php echo $row_get['page_id']; ?>);"><i
                                                            class="bi bi-trash3-fill"></i></button>
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
    function funDel(id) {
        // alert(id);
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
                data: 'id=' + id + '&tblname=' + tblname + '&tblpkey=' + tblpkey + '&submodule=' + submodule +
                    '&pagename=' + pagename + '&module=' + module,
                dataType: 'html',
                success: function(data) {
                    //alert(data);
                    location = '<?php echo $pagename . "?action=3"; ?>';
                }
            }); //ajax close
        } //confirm close
    } //fun close
    $(document).ready(function() {
        $(".chosen-select").chosen();
        $('#example').DataTable();
        // $('#inputmasl').inputmask("99-99-9999");
        // $('#datepicker').datepicker({
        //     autoclose: true,
        //     format: 'dd-mm-yyyy'
        // })
    });

    function validate(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[a-z,A-Z]|\.|\s/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
    }

    function numberOnly(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\.|\s/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
    }
</script>

</html>