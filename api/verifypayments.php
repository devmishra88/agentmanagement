<?php
$ByPass	= 1;
include_once "dbconfig.php";
$createdon	= time();

/*$data	= json_decode( file_get_contents( 'php://input' ), true );*/
$data	= json_decode(stripslashes($_POST['razorpaydata']),true);

ob_start();
print_r($data);
$message = ob_get_clean();
ob_get_flush();

$file = fopen("paymentdetail_agency.txt","w");
@fwrite($file,$message);
fclose($file);

/*$Event	= $data['event'];*/
$Payload	= $data['payload']['payment']['entity'];

ob_start();
print_r($Payload);
$PayloadMessage = ob_get_clean();
ob_get_flush();

$file = fopen("payload_data.txt","w");
@fwrite($file,$PayloadMessage);
fclose($file);

if(!empty($Payload))
{
	$Status					= $Payload['status']; // captured, failed, authorized
	$TransactionInvoiceID	= $Payload['invoice_id'];
	$PaymentMethod			= $Payload['method'];
	$Payamount				= $Payload['amount'] / 100;
	$RazorPaymentID			= $Payload['id'];
	$ErrorCode				= $Payload['error_code'];
	$ErrorDescription		= $Payload['error_description'];

	$CheckSql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE TRIM(razorpayid)=:razorpayid";
	$CheckEsql	= array("razorpayid"=>trim($TransactionInvoiceID));
	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);


	$CheckSql2		= "SELECT * FROM ".$Prefix."payment_log WHERE TRIM(razorpayinoviceid)=:razorpayinoviceid";
	$CheckEsql2		= array("razorpayinoviceid"=>trim($TransactionInvoiceID));
	$CheckQuery2	= pdo_query($CheckSql2,$CheckEsql2);
	$CheckNum2		= pdo_num_rows($CheckQuery2);

	ob_start();
	echo $CheckSql;
	echo "<br>";
	print_r($CheckEsql);
	echo "<br>";
	echo "Total Num".$CheckNum;
	$PayloadMessage = ob_get_clean();
	ob_get_flush();

	$file = fopen("check_data.txt","w");
	@fwrite($file,$PayloadMessage);
	fclose($file);

	$CreditID = "";
	$DuplicatePaymentCheck = 0;
	if($CheckNum > 0 || $CheckNum2 > 0)
	{
		if($CheckNum2 > 0)
		{
			$CheckRow	= pdo_fetch_assoc($CheckQuery2);
		}
		else
		{
			$CheckRow	= pdo_fetch_assoc($CheckQuery);
		}
		$CreditID	= $CheckRow["id"];
		$ClientID	= $CheckRow["clientid"];
		$CustomerID	= $CheckRow["customerid"];
		$PaymentType= $CheckRow["paymenttype"];
		
		if($CheckNum2 > 0)
		{
			$CheckSQL  = "SELECT COUNT(*) as paymentcount FROM ".$Prefix."payment_log WHERE razorpayinoviceid =:razorpayinoviceid AND  paymentid=:paymentid AND status=:status AND status !=:status2" ;
			$CheckESQL = array("razorpayinoviceid"=>trim($TransactionInvoiceID),"paymentid"=>$RazorPaymentID,"status"=>$Status,"status2"=>'');
			$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
			$CheckRow  =  pdo_fetch_assoc($CheckQuery);
			$DuplicatePaymentCheck = $CheckRow['paymentcount'];

			if($DuplicatePaymentCheck < 1)
			{
				$InsertSQL = "INSERT INTO ".$Prefix."payment_log SET
						paymenttype			=:paymenttype,
						creditid			=:creditid,
						razorpayinoviceid	=:razorpayinoviceid,
						paylink				=:paylink,
						paymentid			=:paymentid,
						payamount			=:payamount,
						response			=:response,
						paymethod			=:paymethod,
						error_code			=:error_code,
						error_description	=:error_description,
						status				=:status,
						createdon			=:createdon";

				$InsertESQL = array(
						"paymenttype"		=>$PaymentType,
						"creditid"			=>(int)$CreditID,
						"razorpayinoviceid"	=>$TransactionInvoiceID,
						"paylink"			=>$PaymentLink,
						"paymentid"			=>$RazorPaymentID,
						"response"			=>$message,
						"paymethod"			=>$PaymentMethod,
						"payamount"			=>$Payamount,
						"error_code"		=>$ErrorCode,
						"error_description"	=>$ErrorDescription,
						"status"			=>$Status,
						"createdon"			=>$createdon
					);

				$InsertQuery	= pdo_query($InsertSQL,$InsertESQL);
			}
		}

		if(is_array($InsertQuery))
		{
			ob_start();
			echo $InsertSQL;
			echo "-----";
			print_r($InsertESQL);
			echo "---------";
			print_r($InsertQuery);
			echo "---------";
			$message2 = ob_get_contents();
			ob_end_clean();
			ob_flush();

			$file = fopen("error_sql.txt","w");
			@fwrite($file,$message2);
			fclose($file);

			mail("vkwoodpeckies@gmail.com","orlopay query","query has issue.".$message,"from:noreply@orlopay.com");
		}
		else
		{
			mail("vkwoodpeckies@gmail.com","orlopay query",$message,"from:noreply@orlopay.com");

			ob_start();
			echo $InsertSQL;
			echo "-----";
			print_r($InsertESQL);
			echo "---------";
			print_r($InsertQuery);
			echo "---------";
			$message2 = ob_get_clean();
			ob_get_flush();

			$file = fopen("success_sql.txt","w");
			@fwrite($file,$message2);
			fclose($file);
		}

		if($CreditID > 0 && $CheckNum > 0)
		{
			if($Status == 'paid' || $Status == 'captured')
			{
				$UpdateSQL	= "UPDATE ".$Prefix."sms_credit_log SET 
				ispaid			=:ispaid,
				status			=:status,
				paymentstatus	=:paymentstatus 
				WHERE 
				id				=:id";

				$UpdateESQL = array(
					"ispaid"		=>1,
					'status'		=>1,
					'paymentstatus'	=>'paid',
					"id"			=>(int)$CreditID	
				);

				pdo_query($UpdateSQL,$UpdateESQL);
			}
			elseif($Status == 'failed')
			{
				$UpdateSQL	= "UPDATE ".$Prefix."sms_credit_log SET 
				ispaid			=:ispaid,
				paymentstatus	=:paymentstatus 
				WHERE 
				id				=:id";

				$UpdateESQL = array(
					"ispaid"		=>1,
					"id"			=>(int)$CreditID,
					'paymentstatus'	=>'failed'
				);

				pdo_query($UpdateSQL,$UpdateESQL);
			}
		}
		else if($PaymentType == 'payments' && $CheckNum2 > 0 && $DuplicatePaymentCheck < 1)
		{
			if($Status == 'captured')
			{
				$PaymentNumber	= GetNextPaymentID($ClientID);

				$Sql	= "INSERT INTO ".$Prefix."customer_payments SET 
				clientid		=:clientid,
				customerid		=:customerid,
				amount			=:amount,
				paymenttype		=:paymenttype,
				paymentdate		=:paymentdate,
				createdon		=:createdon,
				paymentmethod	=:paymentmethod,
				receipttype		=:receipttype,
				receiptbyid		=:receiptbyid,
				receiptbyname	=:receiptbyname,
				razorpayid		=:razorpayid,
				remark			=:remark,
				paymentid		=:paymentid";

				$Esql	= array(
					"clientid"		=>(int)$ClientID,
					"customerid"	=>(int)$CustomerID,
					"amount"		=>(float)$Payamount,
					"paymenttype"	=>'c',
					"paymentdate"	=>$createdon,
					"createdon"		=>$createdon,
					"paymentmethod"	=>(int)0,
					"receipttype"	=>"Online",
					"receiptbyid"	=>(int)0,
					"receiptbyname"	=>"Online Payment",
					"remark"		=>"Online Payment",
					"razorpayid"	=>trim($TransactionInvoiceID),
					"paymentid"		=>(int)$PaymentNumber,
				);

				$Query = pdo_query($Sql,$Esql);

				if($Query)
				{
					$recordid	= pdo_insert_id();

					$ClientArr	= GetClientRecord($CustomerID);

					$Narration = "Online - Auto Payment";
					
					/*GenerateCustomerAccountLog($ClientID,$CustomerID,$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$Payamount,$createdon,$Narration,'payment',$recordid,'');

					updateCustomerOutstandingBalance($ClientID, $CustomerID);*/


					$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance = (outstandingbalance - :outstandingbalance) WHERE id=:id";
					$UpdateEsql	= array("outstandingbalance"=>(float)$Payamount,"id"=>(int)$CustomerID);
					pdo_query($UpdateSql,$UpdateEsql);


					$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
					$CustomerEsql	= array("id"=>(int)$CustomerID,"clientid"=>(int)$ClientID);

					$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
					$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

					$customername		= $CustomerRows['name'];
					$customernameArr	= @explode(" ",$customername,2);

					$customername2		= $customernameArr[0]." JI";

					$customerphone		= $CustomerRows['phone'];

					$outstandingbalance	= "Rs.".@number_format($CustomerRows['outstandingbalance'],2);

					$paymentamount		= "Rs.".@number_format($Payamount,2);

					$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
					$ClientEsql	= array("id"=>(int)$ClientID);

					$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
					$ClientRows		= pdo_fetch_assoc($ClientQuery);

					$clientname			= $ClientRows['clientname'];
					$websiteidentifire	= $ClientRows['websiteidentifire'];

					if(trim($websiteidentifire) != "")
					{
						$clientname	= $websiteidentifire.".orlopay.com";
					}

		$Message = "Dear User,

Payment Received: <arg1>

Balance Now: <arg2>


Thanks,
premnews.orlopay.com
Team Orlo";

			$messagearr[] = array("phoneno"=>$customerphone,"arg1"=>$paymentamount,"arg2"=>$outstandingbalance);

					$SMSRoute = 2; /*7 - OtpRoute, 2 - Normal Route*/
					$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$InvoicePaymentID,$SMSRoute);

					if($smssent['Status'] == 'Success' || $smssent['status'] == 'success')
					{
						ob_start();
							echo(json_encode($smssent['refno']));
							$smsapierror = ob_get_clean();
						ob_get_flush();

						$updatesql = "UPDATE ".$Prefix."customer_payments SET 
								smsmresponse	=:smsmresponse
								WHERE
								id				=:id";

						$updateesql = array(
									"smsmresponse"	=>$smsapierror,
									"id"			=>(int)$recordid
								);
						pdo_query($updatesql,$updateesql);
					}
				}
			}
		}
	}
}
?>