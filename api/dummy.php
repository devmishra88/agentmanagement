<?php
$ByPass	= 1;
include_once "dbconfig.php";

//$data	= json_decode( file_get_contents( 'php://input' ), true );
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
	$PaymentID				= $Payload['id'];
	$ErrorCode				= $Payload['error_code'];
	$ErrorDescription		= $Payload['error_description'];

	$CheckSql	= "SELECT id FROM ".$Prefix."payment_log WHERE TRIM(razorpayinoviceid)=:razorpayinoviceid";
	$CheckEsql	= array("razorpayinoviceid"=>trim($TransactionInvoiceID));
	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

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
	if($CheckNum > 0)
	{
		$CheckRow	= pdo_fetch_assoc($CheckQuery);
		$CreditID	= $CheckRow["id"];
		$ClientID	= $CheckRow["clientid"];
		$CustomerID	= $CheckRow["customerid"];
		$PaymentType= $CheckRow["paymenttype"];

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
				"paymentid"			=>$PaymentID,
				"response"			=>$message,
				"paymethod"			=>$PaymentMethod,
				"payamount"			=>$Payamount,
				"error_code"		=>$ErrorCode,
				"error_description"	=>$ErrorDescription,
				"status"			=>$Status,
				"createdon"			=>time()
			);

		$InsertQuery	= pdo_query($InsertSQL,$InsertESQL);

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

		if($CreditID > 0)
		{
			if($Status == 'paid' || $Status == 'captured')
			{
				$UpdateSQL	= "UPDATE ".$Prefix."payment_log SET 
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
				$UpdateSQL	= "UPDATE ".$Prefix."payment_log SET 
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
		else if($PaymentType == 'payments')
		{
			if($Status == 'paid' || $Status == 'captured')
			{
				$PaymentNumber	= GetNextPaymentID($_POST['clientid']);

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
					paymentid		=:paymentid";

					$Esql	= array(
						"clientid"		=>(int)$ClientID,
						"customerid"	=>(int)$CustomerID,
						"amount"		=>(float)$Payamount,
						"paymenttype"	=>'c',
						"paymentdate"	=>time(),
						"createdon"		=>time(),
						"paymentmethod"	=>(int)0,
						"receipttype"	=>"Online",
						"receiptbyid"	=>(int)0,
						"receiptbyname"	=>"Online Payment",
						"paymentid"		=>(int)$PaymentNumber,
					);

					$Query = pdo_query($Sql,$Esql);
			}

		}
	}
}
?>