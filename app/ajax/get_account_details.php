<?php
include_once("../../action.php");

$account_id = $_POST['account_id'];
$row = $obj->select_record("account", ["account_id" => $account_id]);

$area_name = $obj->getvalfield("area_master", "area_name", "area_id='{$row['area_id']}'");

$html = '
<div class="card p-2 bg-light">
    <table class="table table-borderless mb-0 table-sm">
        <tr>
            <td class="bg-light"><label class="form-label mb-0">Location/Area</label></td>
            <td class="bg-light fs-6">' . $area_name . '</td>
        </tr>
        <tr>
            <td class="bg-light"><label class="form-label mb-0">Site Address</label></td>
            <td class="bg-light fs-6">' . $row['address'] . '</td>
        </tr>
    </table>
</div>
';

echo json_encode([
    "html" => $html,
    "mobile" => $row['mobile_no'],
    "decision_maker_name" => $row['account_name']
]);
