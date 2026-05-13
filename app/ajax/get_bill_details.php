<?php
include_once("../../action.php");

$bill_id = $_POST['bill_id'];

$sql = "
    SELECT 
        d.*, 
        a.account_name 
    FROM transaction_entry d

    LEFT JOIN account a 
        ON d.account_id = a.account_id

    WHERE d.type='payment' 
        AND d.ref_bill_id='$bill_id'

    ORDER BY d.transaction_id DESC
";

$res = $obj->executequery($sql);

// Data Available
if ($res && count($res) > 0) {

    $html = '
    <div class="card p-2 bg-light">
        <table class="table table-bordered mb-0 table-sm">

            <tr>
                <th>Payment Date</th>
                <th>Paymode</th>
                <th>Amount</th>
            </tr>
    ';

    foreach ($res as $key) {

        $html .= '
            <tr>

                <td class="bg-light">
                    ' . $obj->dateformatindia($key['billdate']) . '
                </td>

                <td class="bg-light">
                    ' . $key['paymode'] . '
                </td>

                <td class="bg-light">
                    ' . $key['grand_total'] . '
                </td>

            </tr>
        ';
    }

    $html .= '
        </table>
    </div>
    ';

    echo $html;
} else {

    echo "";
}
