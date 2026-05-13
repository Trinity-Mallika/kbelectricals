<?php
include("appsession.php");

$limit = 5;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;

$from_date = $_POST['from_date'] ?? '';
$to_date   = $_POST['to_date'] ?? '';

// 🔥 WHERE CONDITION
$where = "1=1";
if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND DATE(d.billdate) BETWEEN '$from_date' AND '$to_date'";
}

$sql = "
SELECT 
    d.transaction_id,
    d.billdate,
    d.account_id,
    d.billno,
    SUM(td.qty) AS total_qty,
    ac.account_name
FROM transaction_entry d
LEFT JOIN transaction_details td ON d.transaction_id = td.transaction_id
LEFT JOIN account ac ON d.account_id = ac.account_id
WHERE $where AND d.type='Order'
GROUP BY d.transaction_id
ORDER BY d.transaction_id DESC
LIMIT $start, $limit
";

$res = $obj->executequery($sql);
if (!$res || count($res) == 0) {
    echo "";
    exit;
}

foreach ($res as $key) {
    $day   = date('d', strtotime($key['billdate']));
    $month = date('M', strtotime($key['billdate']));
    $year  = date('Y', strtotime($key['billdate']));
?>

    <div class="card border-0 shadow-lg mb-3 p-2">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <!-- DATE -->
                <td width="65px" class="text-center"
                    onclick="openModal('<?php echo $key['transaction_id']; ?>');">

                    <h3 class="text-blue"><?php echo $day; ?></h3>
                    <small><?php echo $month; ?><br><?php echo $year; ?></small>
                </td>

                <!-- DETAILS -->
                <td class="border-start"
                    onclick="openModal('<?php echo $key['transaction_id']; ?>');">
                    <p class="ms-1 mb-0">
                        <strong>Order No.:</strong> <?php echo $key['billno']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Customer:</strong> <?php echo $key['account_name']; ?>
                    </p>
                    <p class="ms-1 mb-0">
                        <strong>Total QTY:</strong> <?php echo $key['total_qty']; ?>
                    </p>
                </td>

                <!-- ACTION -->
                <td width="20px">
                    <div class="btn-group">
                        <?php if ($key['billdate'] == date('Y-m-d')) { ?>

                            <a data-bs-toggle="dropdown">

                                <i class="bi bi-three-dots-vertical"></i>
                            </a>
                        <?php } ?>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>

                                <a class="dropdown-item"
                                    href="my-order.php?transaction_id=<?php echo $key['transaction_id']; ?>">
                                    Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    onclick="funDel('<?php echo $key['transaction_id']; ?>');">
                                    Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>

            </tr>
        </table>
    </div>

<?php } ?>