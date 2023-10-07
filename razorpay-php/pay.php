<?php
require('config.php');
require('razorpay-php/Razorpay.php');
session_start();

// Create the Razorpay Order

use Razorpay\Api\Api;

$api = new Api($keyId, $keySecret);

//
// We create an razorpay order using orders api
// Docs: https://docs.razorpay.com/docs/orders
//
$orderData = [
    'receipt'         => 3456,
    'amount'          => 2000 * 100, // 2000 rupees in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // auto capture
];

//$links = $api->invoice->all();
//$link  = $api->invoice->fetch('inv_00000000000001');

$GapForPaymentLink = (15*60);  // 15 Mins Payment Link

$Amount = 100 * 100; //Amount should be in paisa or 

$expire_by = time() + $GapForPaymentLink;
/*$link  = $api->invoice->create(
					array(
					'customer' => 
								array(
									 "name"=>"Orlopay",
									"email"=> "vkwoodpeckies@gmail.com",
									"contact"=> "9811165912"
									),
					  "type"=> "link",
					  "view_less"=> 1,
					  "amount"=> $Amount,
					  "currency"=> "INR",
					  "description"=> "Payment Link for your subscription for month of January 2020.",
					  "sms_notify"=> 1,
					  "receipt"=> "orlopay#1002",
					  "email_notify"=> 1,
					  "expire_by"=>$expire_by
				)
				);*/

$link  = $api->invoice->edit(
					
					array(
					  "id"=>'inv_EgEYEpv1YqDAc7',
					  "receipt"=> "orlopay#1002",
					  "expire_by"=>time()
				)
				);

echo "<pre>";
print_r($link);
die;
///$link->cancel();
//$link->notifyBy('sms');


die;

$razorpayOrder = $api->order->create($orderData);

$razorpayOrderId = $razorpayOrder['id'];

$_SESSION['razorpay_order_id'] = $razorpayOrderId;

$displayAmount = $amount = $orderData['amount'];

if ($displayCurrency !== 'INR')
{
    $url = "https://api.fixer.io/latest?symbols=$displayCurrency&base=INR";
    $exchange = json_decode(file_get_contents($url), true);

    $displayAmount = $exchange['rates'][$displayCurrency] * $amount / 100;
}

$checkout = 'automatic';

if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true))
{
    $checkout = $_GET['checkout'];
}

$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => "DJ Tiesto",
    "description"       => "Tron Legacy",
    "image"             => "https://s29.postimg.org/r6dj1g85z/daft_punk.jpg",
    "prefill"           => [
    "name"              => "Daft Punk",
    "email"             => "customer@merchant.com",
    "contact"           => "9999999999",
    ],
    "notes"             => [
    "address"           => "Hello World",
    "merchant_order_id" => "12312321",
    ],
    "theme"             => [
    "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];

if ($displayCurrency !== 'INR')
{
    $data['display_currency']  = $displayCurrency;
    $data['display_amount']    = $displayAmount;
}

$json = json_encode($data);

require("checkout/{$checkout}.php");
