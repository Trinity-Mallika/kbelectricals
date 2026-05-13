<?php include("appsession.php");

$limit = 5;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$from_date = $_POST['from_date'] ?? '';
$to_date   = $_POST['to_date'] ?? '';
$where = "1=1";

if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND DATE(d.follow_up_date) BETWEEN '$from_date' AND '$to_date'";
}

$sql = "SELECT d.*,a.account_name,cm.common_name AS product_name
FROM daily_entries d LEFT JOIN account a ON d.account_id = a.account_id
LEFT JOIN common_master cm ON d.common_id = cm.common_id AND cm.type = 'product_display'
WHERE $where 
ORDER BY d.entry_id DESC
LIMIT $start, $limit
";

$res = $obj->executequery($sql);

if (!$res || count($res) == 0) {
    echo "";
    exit;
}

foreach ($res as $key) {
    $day   = date('d', strtotime($key['follow_up_date']));
    $month = date('M', strtotime($key['follow_up_date']));
    $year  = date('Y', strtotime($key['follow_up_date']));
?>

    <div class="entry-card">
        <table class="table table-sm table-borderless mb-0 align-middle">
            <tr>
                <td width="75" class="text-center cursor-pointer"
                    onclick="openModal('<?php echo $key['entry_id']; ?>');">

                    <div class="date-box">
                        <h3><?php echo $day; ?></h3>
                        <small><?php echo $month; ?><br><?php echo $year; ?></small>
                    </div>

                </td>

                <td class="ps-3 cursor-pointer"
                    onclick="openModal('<?php echo $key['entry_id']; ?>');">

                    <p class="entry-line customer">
                        <i class="bi bi-person-circle"></i>
                        <span class="fw-bold"><?php echo $key['account_name']; ?></span>
                    </p>

                    <p class="entry-line product">
                        <i class="bi bi-box-seam"></i>
                        <span class="text-primary"><?php echo $key['product_name']; ?></span>
                    </p>

                    <p class="entry-line mobile">
                        <i class="bi bi-telephone"></i>
                        <span class="text-muted"><?php echo $key['mobile_no']; ?></span>
                    </p>

                </td>

                <td width="40" class="text-end">
                    <div class="btn-group">
                        <?php if ($key['follow_up_date'] == date('Y-m-d')) { ?>
                            <a data-bs-toggle="dropdown" class="menu-btn">
                                <i class="bi bi-three-dots-vertical"></i>
                            </a>
                        <?php } ?>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                            <li>
                                <a class="dropdown-item"
                                    href="add-daily-entry.php?entry_id=<?php echo $key['entry_id']; ?>">
                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item text-danger"
                                    onclick="funDel('<?php echo $key['entry_id']; ?>','<?php echo $key['imgname']; ?>');">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
    </div>
<?php } ?>