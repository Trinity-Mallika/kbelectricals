<?php
include("../adminsession.php");

$id = $_POST['id'];
$tblname = $_POST['tblname'];
$tblpkey = $_POST['tblpkey'];

// पहले record निकालो
$where = array($tblpkey => $id);
$row = $obj->select_record($tblname, $where);

if ($row) {

    // Image Delete
    if (!empty($row['image_upload']) && file_exists("../uploads/document/image/".$row['image_upload'])) {
        unlink("../uploads/document/image/".$row['image_upload']);
    }

    // PDF Delete
    if (!empty($row['pdf_image']) && file_exists("../uploads/document/pdf/".$row['pdf_image'])) {
        unlink("../uploads/document/pdf/".$row['pdf_image']);
    }

    // Excel Delete
    if (!empty($row['excel_sheet']) && file_exists("../uploads/document/excel/".$row['excel_sheet'])) {
        unlink("../uploads/document/excel/".$row['excel_sheet']);
    }

    // Database Record Delete
    $obj->delete_record($tblname, $where);
}

echo 1;
?>