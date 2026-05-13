<?php
include("appsession.php");
if (isset($_POST['entry_id'])) {
    $entry_id = $_POST['entry_id'];
    $res = $obj->select_record("daily_entries", ['entry_id' => $entry_id, "companyid" => $companyid]);
    $account_name = $obj->getvalfield("account", "account_name", "account_id='{$res['account_id']}'");
    $product_name = $obj->getvalfield("common_master", "common_name", "common_id='{$res['common_id']}' AND type='product_display'");
?>
    <table class="table table-borderless table-sm mb-0 modal-detail-table">
        <tr>
            <th width="200"><i class="bi bi-person"></i> Customer Name</th>
            <td><?php echo $account_name; ?></td>
        </tr>
        <tr>
            <th><i class="bi bi-person-badge"></i> Decision Maker</th>
            <td><?php echo $res['decision_maker_name']; ?></td>
        </tr>
        <tr>
            <th><i class="bi bi-telephone"></i> Mobile Number</th>
            <td>
                <a href="tel:<?php echo $res['mobile_no']; ?>">
                    <?php echo $res['mobile_no']; ?>
                </a>
            </td>
        </tr>
        <tr>
            <th><i class="bi bi-box"></i> Product Discussed</th>
            <td><?php echo $product_name; ?></td>
        </tr>
        <tr>
            <th><i class="bi bi-calendar-event"></i> Follow Up Date</th>
            <td>
                <span class="badge bg-warning text-dark">
                    <?php echo $obj->dateformatindia($res['follow_up_date']); ?>
                </span>
            </td>
        </tr>
        <tr>
            <th><i class="bi bi-chat-left-text"></i> Remarks</th>
            <td>
                <div class="remarks-box">
                    <?php echo $res['remarks']; ?>
                </div>
            </td>
        </tr>
        <tr>
            <th><i class="bi bi-image"></i> Image</th>
            <td>
                <?php if ($res['imgname'] != '') { ?>
                    <a href="uploads/daily_entry/<?php echo $res['imgname']; ?>" target="_blank" class="btn btn-sm btn-primary">
                        View
                    </a>
                <?php } else { ?>
                    <span class="text-muted">No Image</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th><i class="bi bi-geo-alt"></i> Location</th>
            <td>
                <?php echo $res['address']; ?><br>
                <?php if ($res['latitude'] != '') { ?>
                    <a target="_blank" class="btn btn-sm btn-primary mt-1"
                        href="https://www.google.com/maps?q=<?php echo $res['latitude']; ?>,<?php echo $res['longitude']; ?>">
                        View Map
                    </a>
                <?php } ?>
            </td>
        </tr>
    </table>
<?php } ?>