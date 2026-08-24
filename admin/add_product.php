<?php
include("../adminsession.php");

$account_id      = isset($_REQUEST['account_id']) ? $obj->test_input($_REQUEST['account_id']) : 0;
$company_id      = isset($_REQUEST['company_id']) ? $obj->test_input($_REQUEST['company_id']) : 0;
$ready_stock     = isset($_REQUEST['ready_stock']) ? $obj->test_input($_REQUEST['ready_stock']) : 0;
$product_id      = isset($_REQUEST['product_id']) ? $obj->test_input($_REQUEST['product_id']) : 0;
$delivery_status = isset($_REQUEST['delivery_status']) ? $obj->test_input($_REQUEST['delivery_status']) : 0;
$category_id     = isset($_REQUEST['category_id']) ? $obj->test_input($_REQUEST['category_id']) : 0;
$gst_id          = isset($_REQUEST['gst_id']) ? $obj->test_input($_REQUEST['gst_id']) : 0;
$taxtype         = isset($_REQUEST['taxtype']) ? $obj->test_input($_REQUEST['taxtype']) : "";
$type            = isset($_REQUEST['type']) ? $obj->test_input($_REQUEST['type']) : '';
$brand_id        = isset($_REQUEST['brand_id']) ? $obj->test_input($_REQUEST['brand_id']) : 0;
$qty             = isset($_REQUEST['qty']) ? (float)$obj->test_input($_REQUEST['qty']) : 0;
$unit_id         = isset($_REQUEST['unit_id']) ? $obj->test_input($_REQUEST['unit_id']) : '';
$unit_name       = isset($_REQUEST['unit_name']) ? $obj->test_input($_REQUEST['unit_name']) : '';
$rate            = isset($_REQUEST['rate']) ? (float)$obj->test_input($_REQUEST['rate']) : 0;
$transaction_id  = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$tran_detail_id  = isset($_REQUEST['tran_detail_id']) ? $obj->test_input($_REQUEST['tran_detail_id']) : 0;
$discount        = isset($_REQUEST['discount']) ? (float)$obj->test_input($_REQUEST['discount']) : 0;
$update_mrp      = isset($_REQUEST['update_mrp']) ? $obj->test_input($_REQUEST['update_mrp']) : 0;

$gst_percent = 0;
if ($gst_id > 0) {
    $gst_percent = (float)$obj->getvalfield("gst_master", "gst_percent", "gst_id='$gst_id'");
}


if ($gst_percent <= 0 && $gst_id > 0) {
    $gst_percent = 18;
}

$disc_per_unit = ($rate * $discount) / 100;
$price_after_disc = max($rate - $disc_per_unit, 0);
$sub_total = round($price_after_disc * $qty, 2);
$discount_amt = round($disc_per_unit * $qty, 2);
$taxable = $sub_total;
$gst_amt = 0;
$net_amt = $sub_total;

if ($gst_percent > 0) {

    if ($taxtype == "exclusive") {

        $taxable = $sub_total;
        $gst_amt = round(($taxable * $gst_percent) / 100, 2);
        $net_amt = round($taxable + $gst_amt, 2);

    } elseif ($taxtype == "inclusive") {

        $net_amt = $sub_total;
        $taxable = round(($net_amt * 100) / (100 + $gst_percent), 2);
        $gst_amt = round($net_amt - $taxable, 2);
    }
}

$total_amt = $taxable;

$count = $obj->getvalfield(
    "transaction_details",
    "count(*)",
    "product_id='$product_id'
    and transaction_id='$transaction_id'
    and account_id='$account_id'
    and company_id='$company_id'
    and tran_detail_id!='$tran_detail_id'
    and type='$type'
    and createdby='$loginid'"
);

if ($count == 0) {

    if ($update_mrp == 1) {
        $obj->update_record(
            "product_master",
            ['product_id' => $product_id],
            ["rate" => $rate]
        );
    }

    $form_data = array(
        'product_id'       => $product_id,
        'unit_id'          => $unit_id,
        'unit_name'        => $unit_name,
        'qty'              => $qty,
        'rate'             => $rate,
        'price_after_disc' => $price_after_disc,
        'sub_total'        => $sub_total,
        'discount'         => $discount,
        'discount_amt'     => $discount_amt,
        'gst_amt'          => $gst_amt,
        'total_amt'        => $total_amt,
        'net_amt'          => $net_amt,
        'gst_id'           => $gst_id,
        'taxtype'          => $taxtype,
        'category_id'      => $category_id,
        'brand_id'         => $brand_id,
        'ready_stock'      => $ready_stock,
        'delivery_status'  => $delivery_status,
        'transaction_id'   => $transaction_id,
        'type'             => $type,
        'account_id'       => $account_id,
        'company_id'       => $company_id,
        'ipaddress'        => $ipaddress,
        'createdby'        => $loginid
    );

    if ($tran_detail_id == 0) {

        $form_data['companyid'] = $companyid;
        $form_data['createdate'] = $createdate;

        $obj->insert_record("transaction_details", $form_data);

        echo "1";

    } else {

        $form_data['lastupdated'] = $createdate;

        $obj->update_record(
            "transaction_details",
            ['tran_detail_id' => $tran_detail_id],
            $form_data
        );

        echo "2";
    }

} else {

    echo "3";
}