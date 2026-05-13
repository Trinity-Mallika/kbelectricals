<?php
include("appsession.php");
if (isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];
    $res = $obj->select_record("transaction_entry", ['transaction_id' => $transaction_id, "companyid" => $companyid]);
    $account_name = $obj->getvalfield("account", "account_name", "account_id='{$res['account_id']}' and companyid='$companyid'");
    $bill_no = $obj->getvalfield("transaction_entry", "billno", "transaction_id='{$res['ref_bill_id']}' and companyid='$companyid'");
}
?>
<table class="table table-sm table-borderless m-2">
    <tr>
        <td>Customer Name</td>
        <td>: &nbsp; <?php echo $account_name; ?></td>
    </tr>
    <tr>
        <td>Bill No.</td>
        <td>: &nbsp; <?php echo $bill_no; ?></td>
    </tr>
    <tr>
        <td>Voucher No.</td>
        <td>: &nbsp; <?php echo $res['billno']; ?></td>
    </tr>
    <tr>
        <td>Voucher Date</td>
        <td>: &nbsp; <?php echo $obj->dateformatindia($res['billdate']); ?></td>
    </tr>
    <tr>
        <td>Paymode</td>
        <td>: &nbsp; <?php echo $res['paymode']; ?></td>
    </tr>
    <tr>
        <td>Location</td>
        <td>: &nbsp; <?php echo $res['address']; ?></td>
    </tr>
    <tr>
        <td>Payment Proof</td>
        <td>: &nbsp;<a href="<?php echo 'uploads/payment_proof/' . $res['imgname']; ?>"> <img style="width: 100px;" src="<?php echo 'uploads/payment_proof/' . $res['imgname']; ?>" alt=""></a></td>
    </tr>
    <tr>
        <td>Transaction Id</td>
        <td>: &nbsp; <?php echo $res['trans_id']; ?></td>
    </tr>
</table>