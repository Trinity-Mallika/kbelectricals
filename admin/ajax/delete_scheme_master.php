<?php 
include("../../adminsession.php");

$id       = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$tblname  = isset($_REQUEST['tblname']) ? $_REQUEST['tblname'] : '';
$tblpkey  = isset($_REQUEST['tblpkey']) ? $_REQUEST['tblpkey'] : '';

if ($id > 0) {

    // =========================
    // GET IMAGE NAME FIRST
    // =========================
    $sql = $obj->executequery("SELECT imgname FROM scheme_entry WHERE scheme_id='$id'");

    if (!empty($sql)) {

        $imgname = $sql[0]['imgname'];

        // =========================
        // DELETE IMAGE FILE
        // =========================
        if ($imgname != "") {

            $image_path = "../uploaded/scheme_image/" . $imgname;

            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }

    // =========================
    // DELETE FROM CHILD TABLE
    // =========================
    $where_details = array('scheme_id' => $id);
    $obj->delete_record('scheme_details', $where_details);

    // =========================
    // DELETE FROM MAIN TABLE
    // =========================
    $where_main = array($tblpkey => $id);
    $obj->delete_record($tblname, $where_main);

    echo 1;

} else {

    echo 0;
}
?>