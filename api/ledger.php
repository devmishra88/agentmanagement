<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
/*set response code - 200 OK*/
http_response_code(200);

include_once "dbconfig.php";

$createdon	= time();

if($_POST['Mode'] == "GetLedger_org")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch ledger.";
    $response['grandtotal']	= 0.00;
	$response['ledgerlist']	= array();

	if($_POST['customerid'] > 0)
	{
		$CustSQL	= "SELECT * FROM ".$Prefix."cust_accounts WHERE customerid=:customerid AND clientid=:clientid order by logdate ASC,id ASC";
		$CustESQL   = array("customerid"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid']);

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);
		$AllPaymentIDs = GetAllPaymentIDsByCustomerIDs((int)$_POST['clientid'],$_POST['customerid']);

		$GrandTotal = 0;

		if($CustNum > 0)
		{
			$response['success']	= true;

			$Index = 0;
			while($CustRow		= pdo_fetch_assoc($CustQuery))
			{
				$LogDate		= $CustRow['logdate'];
				$OrgPaymentID	= $CustRow['paymentid'];
				$Narration		= $CustRow['narration'];
				$Amount			= $CustRow['amount'];
				$Balance		= $CustRow['linetotal'];
				$invoiceid		= $CustRow['invoiceid'];
				$PaymentID		= $AllPaymentIDs[$OrgPaymentID];

				if(strtolower($Narration) == "payment")
				{
					$Narration	= "Payment - Cash";
				}

				if($Amount > 0)
				{
					$AmountDue = $Amount;
					$AmountPaid = 0.00;
				}
				else
				{
					$AmountPaid = $Amount;
					$AmountDue = 0.00;
				}
				$GrandTotal = $Balance;
				if($LogDate > 0)
				{
					$RecordsArr[$Index]['date']	= date("d-M-Y",$LogDate);
				}
				else
				{
					$RecordsArr[$Index]['date']	= '--';
				}
				$RecordsArr[$Index]['item']			= $Narration;
				$RecordsArr[$Index]['paymentid']	= $PaymentID;
				$RecordsArr[$Index]['due']			= number_format($AmountDue,2); 
				$RecordsArr[$Index]['paid']			= number_format($AmountPaid,2);
				$RecordsArr[$Index]['balance']		= number_format($Balance,2);
				$RecordsArr[$Index]['invoiceid']	= (int)$invoiceid;
				$Index++;	
			}

			$response['success']	= true;
			$response['ledgerlist']	= $RecordsArr;
			$response['grandtotal']	= number_format($Balance,2);
			$response['msg']		= "ledger created successfully.";
		}
		else
		{
			if($_POST['iscustomerarea'] > 0)
			{
				$response['msg']	= "No ledger detail found!";
			}
		}

		$NameSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid ORDER BY sequence ASC, customerid ASC, status DESC";
		$NameEsql	= array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid']);

		$NameQuery	= pdo_query($NameSql,$NameEsql);
		$namerows	= pdo_fetch_assoc($NameQuery);

		$customername	= $namerows['name'];
	}
	$response['customername']	= $customername;
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetLedger")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch ledger.";
    $response['grandtotal']	= 0.00;
	$response['ledgerlist']	= array();

	if($_POST['customerid'] > 0)
	{
		$startdate	= 0;
		/*$enddate	= 0;*/
		$enddate	= strtotime(date("Y-m-d"))+86399;

		if($_POST['datetype'] == 'selectdate')
		{
			$startdate	= strtotime($_POST['startdate']);
		}

		$RecordsArr	= array();	
		/*$LedgerArr	= GetLedgerByCustomerID($_POST['clientid'],$_POST['customerid'],$startdate,$enddate);*/
		$LedgerArr		= GetLedgerByCustomerIDNew($_POST['clientid'],$_POST['customerid'],$startdate,$enddate);

		$RecordsArr = $LedgerArr['items'];
		$GrandTotal	= $LedgerArr['grandtotal'];
		if(!empty($RecordsArr))
		{
			$response['success']	= true;
			$response['ledgerlist']	= $RecordsArr;
			$response['grandtotal']	= number_format($GrandTotal,2);
			$response['msg']		= "ledger created successfully.";
		}
		else
		{
			if($_POST['iscustomerarea'] > 0)
			{
				$response['msg']	= "No ledger detail found!";
			}
		}

		$NameSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid ORDER BY sequence ASC, customerid ASC, status DESC";
		$NameEsql	= array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid']);

		$NameQuery	= pdo_query($NameSql,$NameEsql);
		$namerows	= pdo_fetch_assoc($NameQuery);

		$customername	= $namerows['name'];
		$response['customername']	= $customername;
		$json = json_encode($response);
		echo $json;
		die;
	}
}
if($_POST['Mode'] == "GetLedgerByDateRange_org")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch ledger.";
    $response['grandtotal']	= 0.00;

	$startdate	= strtotime($_POST['startdate']);
	$enddate	= strtotime($_POST['enddate']);

	if($_POST['customerid'] > 0 || $_POST['customerphone'] != "")
	{
		$NameSql	= "SELECT cust.* FROM ".$Prefix."customers cust WHERE (id=:id || phone LIKE :phone) AND clientid=:clientid AND deletedon < :deletedon";
		$NameEsql	= array("id"=>(int)$_POST['customerid'],"phone"=>"%".$_POST['customerphone']."%","clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$NameQuery	= pdo_query($NameSql,$NameEsql);
		$NameNum	= pdo_num_rows($NameQuery);

		if($NameNum > 0)
		{
			$NameRows	= pdo_fetch_assoc($NameQuery);

			$customerid		= $NameRows['customerid'];
			$OrgCustName	= $NameRows['name'];

			$customername = $OrgCustName;
			if(trim($customername) == "")
			{
				$customername	= "Unnamed #".$customerid;
			}
		}

		$ExtArg = '';
		$ExtArr	= array("");
		if($_POST['customerphone'] !='')
		{
			$ExtArg = " AND cust.phone =:phone";
			$ExtArr = array("phone"=>$_POST['customerphone']);
		}
		if($_POST['customerid'] !='')
		{
			$ExtArg = " AND cust.id =:id";
			$ExtArr = array("id"=>$_POST['customerid']);
		}

		$custcondition	= "";

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids	= $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}

			$custcondition	.= " AND cust.areaid IN(".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$custcondition	.= " AND cust.lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT acc.*,cust.name as name FROM ".$Prefix."cust_accounts acc,".$Prefix."customers cust WHERE acc.customerid=cust.id $ExtArg AND cust.clientid=:clientid AND (acc.logdate BETWEEN :startdate AND :enddate) AND cust.deletedon < :deletedon ".$custcondition." ORDER BY acc.logdate ASC,acc.id ASC";
		$CustESQL   = array("clientid"=>(int)$_POST['clientid'],"startdate"=>$startdate,"enddate"=>$enddate,'deletedon'=>1);

		$CustESQL2	= array_merge($ExtArr,$CustESQL);
		$CustQuery	= pdo_query($CustSQL,$CustESQL2);
		$CustNum	= pdo_num_rows($CustQuery);

		$Index = 0;

		$PreviousBalance = GetCustomerOutStanding($_POST['customerid'],$_POST['customerphone'],$startdate,'previous');

		$RecordsArr[$Index]['item']		= 'Previous Balance';
		$RecordsArr[$Index]['date']	= '--';
		$RecordsArr[$Index]['paymentid']= '';
		$RecordsArr[$Index]['due']		= 0; 
		$RecordsArr[$Index]['paid']		= 0;
		$RecordsArr[$Index]['balance']	= number_format($PreviousBalance,2);

		if($PreviousBalance > 0)
		{
			$response['success']	= true;
		}

		if($CustNum > 0)
		{
			$response['success']	= true;

			$Index = 1;
			while($CustRow		= pdo_fetch_assoc($CustQuery))
			{
				$LogDate		= $CustRow['logdate'];
				$customerid		= $CustRow['customerid'];
				$OrgCustName	= $CustRow['name'];
				$Narration		= $CustRow['narration'];
				$Amount			= $CustRow['amount'];
				$OrgPaymentID	= $CustRow['paymentid'];
				$Balance		= $CustRow['linetotal'];
				if($Index < 2)
				{
					$AllPaymentIDs = GetAllPaymentIDsByCustomerIDs((int)$_POST['clientid'],$customerid);
			
					/*$customername = $OrgCustName;
					if(trim($customername) == "")
					{
						$customername	= "Unnamed #".$customerid;
					}*/
				}
				$PaymentID = $AllPaymentIDs[$OrgPaymentID];
				if($Amount > 0)
				{
					$AmountDue = $Amount;
					$AmountPaid = 0.00;
				}
				else
				{
					$AmountPaid = $Amount;
					$AmountDue = 0.00;
				}
				$GrandTotal = $Balance;
				if($LogDate > 0)
				{
					$RecordsArr[$Index]['date']	= date("d-M-Y",$LogDate);
				}
				else
				{
					$RecordsArr[$Index]['date']	= '--';
				}	

				$RecordsArr[$Index]['item']		= $Narration;
				$RecordsArr[$Index]['paymentid']= $PaymentID;
				$RecordsArr[$Index]['due']		= number_format($AmountDue,2); 
				$RecordsArr[$Index]['paid']		= number_format($AmountPaid,2);
				$RecordsArr[$Index]['balance']	= number_format($Balance,2);
				$Index++;
			}
			$response['msg']		= "ledger created successfully.";
		}

		$response['ledgerlist']	= $RecordsArr;
		$response['grandtotal']	= number_format($Balance,2);
	}
	$response['customername']	= $customername;
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetLedgerByDateRange")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch ledger.";
    $response['grandtotal']	= 0.00;

	$startdate	= strtotime($_POST['startdate']);
	$enddate	= strtotime($_POST['enddate']);

	if($_POST['customerid'] > 0 || $_POST['customerphone'] != "")
	{
		$custcondition	= "";

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids	= $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}

			$custcondition	.= " AND cust.areaid IN(".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$custcondition	.= " AND cust.lineid IN(".$lineids.")";
		}

		$NameSql	= "SELECT cust.* FROM ".$Prefix."customers cust WHERE (id=:id || phone LIKE :phone) AND clientid=:clientid AND deletedon < :deletedon $custcondition";
		$NameEsql	= array("id"=>(int)$_POST['customerid'],"phone"=>"%".$_POST['customerphone']."%","clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
		
		$NameQuery	= pdo_query($NameSql,$NameEsql);
		$NameNum	= pdo_num_rows($NameQuery);

		if($NameNum > 0)
		{
			$NameRows	= pdo_fetch_assoc($NameQuery);

			$customerid		= $NameRows['customerid'];
			$OrgCustName	= $NameRows['name'];

			$customername = $OrgCustName;
			if(trim($customername) == "")
			{
				$customername	= "Unnamed #".$customerid;
			}
		
			$response['success']	= true;

			$LedgerArr =	GetLedgerByCustomerID($_POST['clientid'],$_POST['customerid'],$startdate,$enddate);

			$RecordsArr = $LedgerArr['items'];
			$GrandTotal	= $LedgerArr['grandtotal'];

			$response['msg']		= "ledger created successfully.";
		}

		$response['ledgerlist']	= $RecordsArr;
		$response['grandtotal']	= number_format($GrandTotal,2);
	}
	$response['customername']	= $customername;
	$json = json_encode($response);
	echo $json;
	die;
}

if($_POST['Mode'] == 'GetLedgerByDateRangePDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate ledger pdf.";

	$startdate	= strtotime($_POST['startdate']);
	$enddate	= strtotime($_POST['enddate']);

	if($_POST['customerid'] > 0)
	{
		if(!is_dir("../assets/".$_POST['clientid']))
		{
			mkdir("../assets/".$_POST['clientid']);
			mkdir("../assets/".$_POST['clientid']."/ledger/");
		}
		@mkdir("../assets/".$_POST['clientid']."/ledger/", 0777, true);

		$Pdf_FileName 	= 'ledger-'.$_POST['customerid'].".pdf";

		/*$File	= "viewledger.php?clientid=".$_POST['clientid']."&customerid=".$_POST['customerid']."&startdate=".$startdate."&enddate=".$enddate."&areaid=".$_POST['areaid']."&lineid=".$_POST['lineid']."&hasdatefilter=".$_POST['hasdatefilter']."&bulkprinting=1&downloadpdf=1";*/

		$File	= "viewledger.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr."&startdate_strtotime=".$startdate."&enddate_strtotime=".$enddate;

		$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/ledger/".$Pdf_FileName,"");

		if($donotusecache > 0)
		{
			$Pdf_FileName	.= "?t=".time();
		}

		if($IsCreated == true)
		{
			$Response['success']		= true;
			$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/ledger/".$Pdf_FileName;
			$Response['msg']			= "Ledger pdf generated successfully.";
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerLederPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate ledger pdf.";

	if($_POST['customerid'] > 0)
	{
		if(!is_dir("../assets/".$_POST['clientid']))
		{
			mkdir("../assets/".$_POST['clientid']);
			mkdir("../assets/".$_POST['clientid']."/ledger/");
		}
		@mkdir("../assets/".$_POST['clientid']."/ledger/", 0777, true);

		$Pdf_FileName 	= 'ledger-'.$_POST['customerid'].".pdf";

		/*$File	= "viewcustomerledger.php?clientid=".$_POST['clientid']."&customerid=".$_POST['customerid']."&bulkprinting=1&downloadpdf=1";*/

		$File	= "viewcustomerledger.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

		$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/ledger/".$Pdf_FileName,"");

		if($donotusecache > 0)
		{
			$Pdf_FileName	.= "?t=".time();
		}

		if($IsCreated == true)
		{
			$Response['success']		= true;
			$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/ledger/".$Pdf_FileName."?t=".time();
			$Response['msg']			= "Ledger pdf generated successfully.";
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ResendInvoiceMessage")
{
    $response['success']	= false;
    $response['msg']		= "Unable to send invoice message.";

	$_POST['cansendsms']	= 1;

	$messagearr	= array();

	if($_POST['areamanagerid'] > 0 || $_POST['linemanid'] > 0 || $_POST['hawkerid'] > 0)
	{
		$_POST['cansendsms']	= 0;
	}

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($_POST['clientid']);

	if($_POST['cansendsms'] < 1)
	{
		$haserror = true;
		$response['msg']	= "You don't have permission to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	if($_POST['cansendsms'] > 0 && $totalsmscreditsavaiable < 1)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND deletedon <:deletedon";
	$CheckEsql	= array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);

	if(is_array($CheckQuery))
	{
		$response['msg']	= $CheckQuery['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$row	= pdo_fetch_assoc($CheckQuery);

		$clientid		= $row['clientid'];
		$customerid		= $row['id'];
		$customername	= $row['name'];
		$customerphone	= $row['phone'];
		$outstanding	= (float)$row['outstandingbalance'];

		$customernameArr	= @explode(" ",$customername,2);

		$customername2		= $customernameArr[0]." JI";

		if($outstanding > 0)
		{
			$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND customerid=:customerid AND deletedon <:deletedon ORDER BY invoiceid DESC LIMIT 1";
			$InvoiceEsql	= array("clientid"=>(int)$clientid,"customerid"=>(int)$customerid,'deletedon'=>1);

			$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);

			if(is_array($InvoiceQuery))
			{
				$response['msg']	= $InvoiceQuery['errormessage'];

				$json = json_encode($response);
				echo $json;
				die;
			}

			$InvoiceNum		= pdo_num_rows($InvoiceQuery);

			if($InvoiceNum > 0)
			{
				$invoicerow	= pdo_fetch_assoc($InvoiceQuery);

				$billid			= $invoicerow['id'];
				$code			= $invoicerow['securitycode'];
				$invoiceamount	= $invoicerow['finalamount'];
				$invoicemonth	= ucwords($MonthArr[$invoicerow['invoicemonth']]);
				$invoiceyear	= $invoicerow['invoiceyear'];

				$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
				$ClientEsql	= array("id"=>(int)$_POST['clientid']);

				$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
				$ClientRows		= pdo_fetch_assoc($ClientQuery);

				$clientname			= $ClientRows['clientname'];
				$websiteidentifire	= $ClientRows['websiteidentifire'];

				if(trim($websiteidentifire) != "")
				{
					$clientname	= $websiteidentifire.".orlopay.com";
				}

				$newurl	= "www.orlopay.com/i/".$code."/".$billid;

				$invoiceamount	= "Rs.".$invoiceamount;

				$BillingMonth = $invoicemonth.", ".$invoiceyear;	
				$messagearr[] = array("phoneno"=>$customerphone,"arg1"=>$newurl,"arg2"=>"Rs.".$outstanding,"arg5"=>$clientname);

		/*$Message = "Dear <arg2>,

Bill generated for ".$invoicemonth.", ".$invoiceyear.": <arg3>

Invoice: <arg1>

".$clientname."
Team Orlo";
Dear <arg1>, Bill generated for <arg2> View Bill: <arg3> Balance Now: <arg4> <arg5> Team Orlo

*/

$Message = "Dear User,

Your monthly bill is generated.

View Bill: <arg1>

Balance Now: <arg2>

premnews.orlopay.com
Team Orlo";

				$SMSRoute = 2; /*7 - OtpRoute, 2 - Normal Route*/
				$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$InvoiceGenerationID,$SMSRoute);
				
				if($smssent['Status'] == 'Success' || $smssent['status'] == 'success')
				{
					ob_start();
						echo(json_encode($smssent['refno']));
						$smsapierror = ob_get_clean();
					ob_get_flush();

					$AddLogSql = "INSERT INTO ".$Prefix."sms_log SET 
					clientid		=:clientid,
					customerid		=:customerid,
					phone			=:phone,
					message			=:message,
					smscredit		=:smscredit,
					smsmresponse	=:smsmresponse,
					createdon		=:createdon";

					$AddLogEsql = array(
						"clientid"		=>(int)$_POST['clientid'],
						"customerid"	=>(int)$customerid,
						"phone"			=>$customerphone,
						"message"		=>$Message,
						"smscredit"		=>(int)$_POST['smscredits'],
						"smsmresponse"	=>$smsapierror,
						"createdon"		=>time()
					);
					pdo_query($AddLogSql,$AddLogEsql);
				}

				$response['success']	= true;
				$response['msg']		= "Invoice sms successfully resend.";
			}
		}
		else
		{
			$response['msg']	= "You have no outstanding balance. Nothing to pay!";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceMessagePreview")
{
    $response['success']	= false;
    $response['msg']		= "Unable to create invoice message preview.";

	$_POST['cansendsms']	= 1;

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND deletedon <:deletedon";
	$CheckEsql	= array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);

	if(is_array($CheckQuery))
	{
		$response['msg']	= $CheckQuery['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$row	= pdo_fetch_assoc($CheckQuery);

		$clientid		= $row['clientid'];
		$customerid		= $row['id'];
		$customername	= $row['name'];
		$customerphone	= $row['phone'];
		$outstanding	= (float)$row['outstandingbalance'];

		$customernameArr	= @explode(" ",$customername,2);

		$customername2		= $customernameArr[0]." JI";

		if($outstanding > 0)
		{
			$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND customerid=:customerid AND deletedon <:deletedon ORDER BY invoiceid DESC LIMIT 1";
			$InvoiceEsql	= array("clientid"=>(int)$clientid,"customerid"=>(int)$customerid,'deletedon'=>1);

			$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);

			if(is_array($InvoiceQuery))
			{
				$response['msg']	= $InvoiceQuery['errormessage'];

				$json = json_encode($response);
				echo $json;
				die;
			}

			$InvoiceNum		= pdo_num_rows($InvoiceQuery);

			if($InvoiceNum > 0)
			{
				$invoicerow	= pdo_fetch_assoc($InvoiceQuery);

				$billid			= $invoicerow['id'];
				$code			= $invoicerow['securitycode'];
				$invoiceamount	= $invoicerow['finalamount'];
				$invoicemonth	= ucwords($MonthArr[$invoicerow['invoicemonth']]);
				$invoiceyear	= $invoicerow['invoiceyear'];

				$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
				$ClientEsql	= array("id"=>(int)$_POST['clientid']);

				$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
				$ClientRows		= pdo_fetch_assoc($ClientQuery);

				$clientname			= $ClientRows['clientname'];
				$websiteidentifire	= $ClientRows['websiteidentifire'];

				if(trim($websiteidentifire) != "")
				{
					$clientname	= $websiteidentifire.".orlopay.com";
				}

				$newurl	= "www.orlopay.com/i/".$code."/".$billid;

				$invoiceamount	= "Rs.".$invoiceamount;

		/*$Message = "Dear ".$customername2.",

Bill generated for ".$invoicemonth.", ".$invoiceyear.": ".$invoiceamount."

Invoice: ".$newurl."

".$clientname."
Team Orlo";*/

/*$Message = "Dear ".$customername2.",

Bill generated for ".$invoicemonth.", ".$invoiceyear."

View Bill: ".$newurl."

Balance Now: Rs.".$outstanding."

".$clientname."
Team Orlo";
*/

$Message = "Dear User,

Your monthly bill is generated.

View Bill: ".$newurl."

Balance Now: Rs.".$outstanding."

".$clientname."
Team Orlo";

				$smscredits	= ceil(strlen(trim($Message)) / 160);

				$response['success']	= true;
				$response['msg']		= "Invoice messge preview successfully generated.";
				$response['preview']	= nl2br($Message);
				$response['smscredits']	= (int)$smscredits;
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
?>