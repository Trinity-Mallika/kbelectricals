<?php
include("../../adminsession.php");

$term = $_POST['term'];

$res = $obj->executequery("
    SELECT area_id,area_name
    FROM area_master
    WHERE area_name LIKE '%$term%'
    ORDER BY area_name
");

foreach($res as $row){
?>
<div class="list-group-item area-item"
     data-id="<?= $row['area_id'] ?>"
     data-name="<?= $row['area_name'] ?>">
    <?= $row['area_name'] ?>
</div>
<?php
}
?>