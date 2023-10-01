<?
require('Razorpay.php');

use Razorpay\Api\Api;

$api_key = 'rzp_test_6TqHJPhJxvW9Uu';
$api_secret = 'WNAHoMVL52RJgYKAua1E3m9d';
//$displayCurrency = 'INR';

$api = new Api($api_key, $api_secret);

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
$link  = $api->invoice->create(
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
					  "sms_notify"=> 0,
					  "email_notify"=> 0
				)
			);	
echo "<hr/><pre>" ;
print_r($link);
die;
/*$link  = $api->invoice->edit(
					array(
					  "receipt"=> "orlopay#1004",
					  "expire_by"=>time()
				)
			);*/
///$link->cancel();
//$link->notifyBy('sms');


die;

?>