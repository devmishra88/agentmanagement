<?php 
include_once "dbconfig.php";

if($_POST['Mode'] == 'getpaylink')
{
	$ClientID = $_POST['clientid'];
	
	$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
	$ClientESQL		= array("id"=>($ClientID));

	$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
	$ClientNum		= pdo_num_rows($ClientQuery);

	if($ClientNum > 0)
	{
		$ClientRow	= pdo_fetch_assoc($ClientQuery);
		
		$IsPaymentGateWay	= $ClientRow['ispaymentgateway'];
		$RAZOR_PAY_API_KEY_Client	= $ClientRow["razor_pay_api_key"];
		$RAZOR_PAY_API_SECRET_Client	= $ClientRow["razor_pay_api_secret"];
	}
	$CustomerArr = array();
	$CustomerSQL		= "SELECT * FROM ".$Prefix."customers WHERE id=:id";
	$CustomerESQL		= array("id"=>($_POST['customerid']));

	$CustomerQuery	= pdo_query($CustomerSQL,$CustomerESQL);
	$CustomerNum		= pdo_num_rows($CustomerQuery);
	
	$CustID	= 0;
	if($CustomerNum > 0)
	{
		$CustomerRow	= pdo_fetch_assoc($CustomerQuery);
		$CustomerArr['clientid']	= (int)$ClientID;
		$CustomerArr['customerid']	= (int)$_POST['customerid'];
		$CustomerArr['name']		= $CustomerRow['name'];
		$CustomerArr['email']		= $CustomerRow['email'];
		$CustomerArr['phone']		= $CustomerRow['phone'];
		$CustID						= $CustomerRow['customerid'];
	}
	
	$Notes = "Payment by customer id #".$CustID;

	$PaymentLink = GeneratePaymentLinks_Customer($CustomerArr,$_POST['amount'],$Notes,$RAZOR_PAY_API_KEY_Client,$RAZOR_PAY_API_SECRET_Client);

	if($PaymentLink !='')
	{
		$sql 		= "SELECT * FROM ".$Prefix."payment_log WHERE paylink=:paylink";
		$esql 		= array("paylink"=>$PaymentLink);
		$query		= pdo_query($sql,$esql);
		$row		= pdo_fetch_assoc($query);
		$paymentid	= $row['razorpayinoviceid'];

		CreatePaymentRequest($paymentid, $PaymentLink, $ClientID);
	}

echo $PaymentLink;
}
?>