<?php include("appsession.php");

$account_id     = isset($_REQUEST['account_id'])     ? $obj->test_input($_REQUEST['account_id'])     : 0;
$product_id     = isset($_REQUEST['product_id'])     ? $obj->test_input($_REQUEST['product_id'])     : 0;
$category_id    = isset($_REQUEST['category_id'])    ? $obj->test_input($_REQUEST['category_id'])    : 0;
$type           = isset($_REQUEST['type'])           ? $obj->test_input($_REQUEST['type'])           : '';
$brand_id       = isset($_REQUEST['brand_id'])       ? $obj->test_input($_REQUEST['brand_id'])       : 0;
$unit_id        = isset($_REQUEST['unit_id'])        ? $obj->test_input($_REQUEST['unit_id'])        : 0;
$unit_name      = isset($_REQUEST['unit_name'])      ? $obj->test_input($_REQUEST['unit_name'])      : '';
$qty            = isset($_REQUEST['qty'])            ? $obj->test_input($_REQUEST['qty'])            : 0;
$rate           = isset($_REQUEST['rate'])           ? $obj->test_input($_REQUEST['rate'])           : 0;
$discount       = isset($_REQUEST['discount'])       ? (float)$obj->test_input($_REQUEST['discount'])      : 0;
$discount_amt   = isset($_REQUEST['discount_amt'])   ? (float)$obj->test_input($_REQUEST['discount_amt'])  : 0;
$gst_id         = isset($_REQUEST['gst_id'])         ? $obj->test_input($_REQUEST['gst_id'])         : 0;
$sgst_percent   = isset($_REQUEST['sgst_percent'])   ? (float)$obj->test_input($_REQUEST['sgst_percent']) : 0;
$cgst_percent   = isset($_REQUEST['cgst_percent'])   ? (float)$obj->test_input($_REQUEST['cgst_percent']) : 0;
$igst_percent   = isset($_REQUEST['igst_percent'])   ? (float)$obj->test_input($_REQUEST['igst_percent']) : 0;
$gst_percent    = $sgst_percent + $cgst_percent + $igst_percent;
$sub_total      = isset($_REQUEST['sub_total'])      ? (float)$obj->test_input($_REQUEST['sub_total'])     : 0;
$net_amt        = isset($_REQUEST['taxable_amt'])    ? (float)$obj->test_input($_REQUEST['taxable_amt'])   : 0;
$total_amt      = isset($_REQUEST['total_amt'])      ? (float)$obj->test_input($_REQUEST['total_amt'])     : 0;
$taxtype        = 'exclusive';

$transaction_id = isset($_REQUEST['transaction_id']) ? $obj->test_input($_REQUEST['transaction_id']) : 0;
$tran_detail_id = isset($_REQUEST['tran_detail_id']) ? $obj->test_input($_REQUEST['tran_detail_id']) : 0;

if ($product_id > 0) {

    $count = $obj->getvalfield(
        "transaction_details",
        "count(*)",
        "product_id='$product_id'
         AND transaction_id='$transaction_id'
         AND account_id='$account_id'
         AND tran_detail_id != '$tran_detail_id'
         AND type='$type' and createdby='$loginid'"
    );

    if ($count == 0) {

        $form_data = [
            'transaction_id' => $transaction_id,
            'type'           => $type,
            'account_id'     => $account_id,
            'category_id'    => $category_id,
            'product_id'     => $product_id,
            'brand_id'       => $brand_id,
            'unit_id'        => $unit_id,
            'unit_name'      => $unit_name,
            'qty'            => $qty,
            'rate'           => $rate,
            'discount'       => $discount,      
            'discount_amt'   => $discount_amt,  
            'gst_id'         => $gst_id,
            'taxtype'        => $taxtype,       
            'sub_total'      => $sub_total,     
            'net_amt'        => $net_amt,       
            'total_amt'      => $total_amt,     
            'ipaddress'      => $ipaddress,
            'companyid'      => $companyid,
            'createdby'      => $loginid,
        ];

        if ($tran_detail_id == 0) {
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
}