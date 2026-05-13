<?php include("appsession.php");

$limit = 5;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;

$from_date = $_POST['from_date'] ?? '';
$to_date   = $_POST['to_date'] ?? '';

$sql = "
SELECT 
    a.*,
    cm.common_name,
    am.area_name
FROM account a
LEFT JOIN common_master cm ON cm.common_id = a.common_id
LEFT JOIN area_master am ON am.area_id = a.area_id
WHERE a.createdby='$loginid' and a.companyid='$companyid'
";

if (!empty($from_date) && !empty($to_date)) {
    $sql .= " AND DATE(a.createdate) BETWEEN '$from_date' AND '$to_date'";
}

$sql .= " ORDER BY a.account_id DESC LIMIT $start, $limit";

$res = $obj->executequery($sql);
if (!$res || count($res) == 0) {
    echo "";
    exit;
}

foreach ($res as $key) {
    $day   = date('d', strtotime($key['createdate']));
    $month = date('M', strtotime($key['createdate']));
    $year  = date('Y', strtotime($key['createdate']));
?>

    <div class="card border-0 shadow-lg mb-3 p-2">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td width="65px" class="text-center">

                    <h3 class="text-blue"><?php echo $day; ?></h3>
                    <small><?php echo $month; ?><br><?php echo $year; ?></small>
                </td>
                <td class="border-start">
                    <p class="ms-1 mb-0">
                        <strong>Counter :</strong> <?php echo $key['account_name']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Counter Type :</strong> <?= $key['common_name']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Class :</strong> <?php echo $key['class']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Area :</strong> <?= $key['area_name']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Mobile :</strong> <?php echo $key['mobile_no']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Address :</strong> <?php echo $key['address']; ?>
                    </p>
                </td>
                <?php if ($key['status1'] == 0) { ?>
                    <td width="20px">
                        <div class="dropdown rside-dropdown">
                            <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu shadow-sm">
                                <li><a class="dropdown-item fw-medium text-success" href="create-counter.php?account_id=<?php echo $key['account_id']; ?>"><i class="bi bi-pencil-fill"></i> Edit </a></li>
                                <li><a class="dropdown-item fw-medium text-danger" onclick="funDel('<?php echo $key['account_id']; ?>')"><i class="bi bi-trash-fill"></i> Delete </a></li>
                            </ul>
                        </div>
                    </td>
                <?php } ?>
            </tr>
        </table>
    </div>

<?php } ?>