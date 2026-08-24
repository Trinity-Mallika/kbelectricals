<?php
include("appsession.php");

$limit = 5;
$start = isset($_POST['start']) ? max(0, intval($_POST['start'])) : 0;
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';
$account_id = isset($_POST['account_id']) ? $_POST['account_id'] : '';
$where = "1=1";
if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND DATE(d.billdate) BETWEEN '$from_date' AND '$to_date'";
}
if (!empty($account_id)) {
    $where .= " AND d.account_id='$account_id'";
}

$sql = "SELECT 
    d.transaction_id,
    d.billdate,
    d.account_id,
    d.billno, 
    d.dispatch_status, 
    SUM(td.qty) AS total_qty,
    ac.account_name,
    ac.mobile_no
FROM transaction_entry d
LEFT JOIN transaction_details td ON d.transaction_id = td.transaction_id
LEFT JOIN account ac ON d.account_id = ac.account_id
WHERE $where AND d.type='Order' and d.createdby='$loginid'
GROUP BY d.transaction_id
ORDER BY d.transaction_id DESC
LIMIT $start, $limit
";

$res = $obj->executequery($sql);
if (!$res || count($res) == 0) {
    echo "";
    exit;
}


function status_badge($status)
{
    if ($status == 1) {
        return '<span class="status-pill status-dispatched"><i class="bi bi-check2-circle"></i> Dispatched</span>';
    }

    return '<span class="status-pill status-pending"><i class="bi bi-clock-history"></i> Pending</span>';
}

foreach ($res as $key) {
    $billTimestamp = strtotime($key['billdate']);
    $day   = date('d', $billTimestamp);
    $month = date('M', $billTimestamp);
    $year  = date('Y', $billTimestamp);
    $isToday = $key['billdate'] == date('Y-m-d');
    $tid = (int) $key['transaction_id'];
?>

    <div class="order-list-card position-relative">
        <div class="order-date-box" onclick="openModal(<?= $tid ?>)">
            <span class="order-day"><?= htmlspecialchars($day) ?></span>
            <span class="order-month"><?= htmlspecialchars($month) ?></span>
            <span class="order-year"><?= htmlspecialchars($year) ?></span>
        </div>

        <div class="order-main" onclick="openModal(<?= $tid ?>)">
            <div class="order-top-row">
                <div class="order-title">Order No. <?= htmlspecialchars($key['billno']) ?></div>

            </div>

            <div class="order-meta-grid">
                <div class="order-meta-item">
                    <i class="bi bi-person-fill"></i>
                    <span><?= htmlspecialchars($key['account_name']) ?></span>
                </div>
                <div class="order-meta-item">
                    <i class="bi bi-telephone-fill"></i>
                    <span><?= htmlspecialchars($key['mobile_no']) ?></span>
                </div>
                <div class="order-meta-item justify-content-between">
                    <span><i class="bi bi-box-seam me-1"></i>
                        <span>Total Qty: <?= htmlspecialchars($key['total_qty']) ?></span></span>
                    <?= status_badge($key['dispatch_status']) ?>
                </div>
            </div>
        </div>

        <?php if ($isToday) { ?>
            <div class="order-actions">
                <div class="dropdown">
                    <button class="order-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li>
                            <a class="dropdown-item" href="my-order.php?transaction_id=<?= $tid ?>">Edit</a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="funDel(<?= $tid ?>); return false;">Delete</a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

<?php } ?>