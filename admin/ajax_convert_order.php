<?php
include("../adminsession.php");

if (isset($_POST['transaction_id'])) {
    $quotation_id = $obj->test_input($_POST['transaction_id']);

    $quotation = $obj->select_record(
        "transaction_entry",
        ["transaction_id" => $quotation_id]
    );

    if (empty($quotation)) {
        echo 0;
        exit;
    }
    $where = ["transaction_id" => $quotation_id];
    $obj->update_record("transaction_entry", $where, ["conversion_status" => '1']);
    $billno = $obj->getcode("transaction_entry", "billno", "1=1 and type='order'");

    $form_data = array(
        "account_id"    => $quotation['account_id'],
        "type"          => "order",
        "net_total_amt" => $quotation['net_total_amt'],
        "cgst"          => $quotation['cgst'],
        "sgst"          => $quotation['sgst'],
        "taxable_amount" => $quotation['taxable_amount'],
        "freight_charges" => $quotation['freight_charges'],
        "gst_percent"   => $quotation['gst_percent'],
        "grand_total"   => $quotation['grand_total'],
        "remark"        => $quotation['remark'],
        "gst"           => $quotation['gst'],
        "is_gst"        => $quotation['is_gst'],
        "freight"       => $quotation['freight'],
        "validity"      => $quotation['validity'],
        "payment"       => $quotation['payment'],
        "billno"        => $billno,
        "billdate"      => date('Y-m-d'),
        "print_columns" => $quotation['print_columns'],
        "createdby"     => $loginid,
        "companyid"     => $companyid,
        "createdate"    => $createdate,
        "parent_transaction_id"    => $quotation_id,
        "ipaddress"     => $ipaddress
    );

    $new_order_id = $obj->insert_record_lastid(
        "transaction_entry",
        $form_data
    );
    // Copy transaction details 
    $resdetail = $obj->executequery("
        SELECT *
        FROM transaction_details
        WHERE transaction_id='$quotation_id'
    ");

    foreach ($resdetail as $row) {
        $detail = array(
            "transaction_id" => $new_order_id,
            "account_id"     => $row['account_id'],

            "product_id"     => $row['product_id'],
            "unit_id"        => $row['unit_id'],
            "unit_name"      => $row['unit_name'],

            "qty"            => $row['qty'],
            "ready_stock"    => $row['ready_stock'],
            "delivery_status" => $row['delivery_status'],

            "category_id"    => $row['category_id'],
            "brand_id"       => $row['brand_id'],

            "rate"           => $row['rate'],
            "price_after_disc" => $row['price_after_disc'],
            "sub_total"      => $row['sub_total'],
            "discount"       => $row['discount'],
            "discount_amt"   => $row['discount_amt'],

            "gst_id"         => $row['gst_id'],
            "taxtype"        => $row['taxtype'],
            "gst_amt"        => $row['gst_amt'],
            "net_amt"        => $row['net_amt'],
            "total_amt"      => $row['total_amt'],
            "type"           => "order",
            "company_id"     => $companyid,
            "companyid"      => $companyid,
            "createdby"      => $loginid,
            "createdate"     => $createdate,
            "ipaddress"      => $ipaddress

        );
        $obj->insert_record("transaction_details", $detail);
    }
    echo $new_order_id;
    exit;
}
