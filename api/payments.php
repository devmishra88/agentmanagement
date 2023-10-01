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

if($_POST['Mode'] == "AddCustomerPayment")
{
    $response['success']	= false;
    $response['msg']		= "Unable to save payment.";

	/*if($_POST['areamanagerid'] > 0 || $_POST['linemanid'] > 0 || $_POST['hawkerid'] > 0)*/
	if(!$isadminlogin)
	{
		$_POST['cansendsms']	= 1;
	}

	$CurrMonthTotalDiscount	= 0;

	$errormessage	= "";

	/*if($_POST['areamanagerid'] > 0 || $_POST['linemanid'] > 0 || $_POST['hawkerid'] > 0)*/
	if(!$isadminlogin)
	{
		$OutStandingBalance	= UpdateoutstandingByCustomerID($_POST['clientid'],$_POST['recordid']);
		
		if($OutStandingBalance <= 0)
		{
			$response['msg']	= 'No outstanding for the customer, so payment cannot be done.';

			$json = json_encode($response);
			echo $json;
			die;
		}

		$paymentdate		= strtotime($_POST['paymentdate']);

		$minimumpaymentdate	= strtotime(date("Y-m-d",strtotime("yesterday")));
		$maximumpaymentdate	= strtotime(date("Y-m-d",strtotime("today")))+86399;

		if(($paymentdate < $minimumpaymentdate) || ($paymentdate > $maximumpaymentdate))
		{
			$haserror = true;

			$errormessage	= "You can not enter date before yesterday.";
		}

		if($errormessage == "")
		{
			$startdate	= strtotime(date('Y-m-01', strtotime($_POST['paymentdate'])));
			$last_date	= date('t',$startdate);

			$enddate	= strtotime(date('Y-m-'.$last_date))+86399;

			$AddedDiscountSql	= "SELECT SUM(discount) AS totaldiscount,SUM(coupon) AS totalcoupon FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND deletedon < :deletedon AND customerid=:customerid AND (paymentdate BETWEEN :startdate AND :enddate)";

			$AddedDiscountEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"customerid"=>(int)$_POST['recordid'],"startdate"=>$startdate,"enddate"=>$enddate);

			$AddedDiscountQuery	= pdo_query($AddedDiscountSql,$AddedDiscountEsql);
			$AddedDiscountRow	= pdo_fetch_assoc($AddedDiscountQuery);

			$totaldiscount	= $AddedDiscountRow['totaldiscount']; 
			$totalcoupon	= $AddedDiscountRow['totalcoupon'];

			$CurrMonthTotalDiscount	= $totaldiscount+$totalcoupon;

			$totaldiscount	= $CurrMonthTotalDiscount+$_POST['discount']+$_POST['coupon'];

			/*if($totaldiscount == (int)"50" || $totaldiscount > (int)"50")*/
			if($totaldiscount > (int)"50")
			{
				$haserror = true;

				if($CurrMonthTotalDiscount > 0)
				{
					/*$response['msg']	= "Sorry, discount already exist you are not allowed give discount for more than Rs.".(50 - (float)$CurrMonthTotalDiscount);*/

					$errormessage	= "Sorry, discount not allowed for more than ".(50 - (float)$CurrMonthTotalDiscount)." rupee.";
				}
				else
				{
					$errormessage	= "Sorry, discount not allowed for more than 50 rupee";
				}
			}
		}

		if($errormessage != "")
		{
			$response['msg']	= $errormessage;

			$json = json_encode($response);
			echo $json;
			die;
		}
	}

	$PaymentNumber	= GetNextPaymentID($_POST['clientid']);

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($_POST['clientid']);

	if($_POST['cansendsms'] > 0 && $totalsmscreditsavaiable < 1)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$CheckSql	= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND deletedon < :deletedon AND customerid=:customerid AND amount=:amount AND paymenttype=:paymenttype AND paymentdate=:paymentdate AND receipttype=:receipttype AND receiptbyid=:receiptbyid";

	$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"customerid"=>(int)$_POST['recordid'],"amount"=>(float)$_POST['paymentamount'],"paymenttype"=>'c',"paymentdate"=>strtotime($_POST['paymentdate']),"receipttype"=>$_POST['receipttype'],"receiptbyid"=>(int)$_POST['receiptbyid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$haserror = true;
		$response['msg']	= "A payment already exists with the same details.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$EditCustomerSql	= "UPDATE ".$Prefix."customers SET 
	canprintinvoice		=:canprintinvoice
	WHERE
	id					=:id
	AND
	clientid			=:clientid";

	$EditCustomerEsql	= array(
		"canprintinvoice"	=>(int)$_POST['canprintinvoice'],
		"id"				=>(int)$_POST['recordid'],
		"clientid"			=>(int)$_POST['clientid']
	);

	$EditCustomerQuery	= pdo_query($EditCustomerSql,$EditCustomerEsql);

	$Sql	= "INSERT INTO ".$Prefix."customer_payments SET 
	clientid			=:clientid,
	customerid			=:customerid,
	amount				=:amount,
	paymenttype			=:paymenttype,
	paymentdate			=:paymentdate,
	createdon			=:createdon,
	remark				=:remark,
	discount			=:discount,
	coupon				=:coupon,
	paymentmethod		=:paymentmethod,
	receipttype			=:receipttype,
	receiptbyid			=:receiptbyid,
	receiptbyname		=:receiptbyname,
	paymentid			=:paymentid,
	paymentaddedby		=:paymentaddedby,
	paymentaddedbyid	=:paymentaddedbyid";

	$Esql	= array(
		"clientid"			=>(int)$_POST['clientid'],
		"customerid"		=>(int)$_POST['recordid'],
		"amount"			=>(float)$_POST['paymentamount'],
		"paymenttype"		=>'c',
		"paymentdate"		=>strtotime($_POST['paymentdate']),
		"createdon"			=>time(),
		"remark"			=>$_POST['remark'],
		"discount"			=>(float)$_POST['discount'],
		"coupon"			=>(float)$_POST['coupon'],
		"paymentmethod"		=>(int)$_POST['paymentmethod'],
		"receipttype"		=>$_POST['receipttype'],
		"receiptbyid"		=>(int)$_POST['receiptbyid'],
		"receiptbyname"		=>$_POST['receiptbyname'],
		"paymentid"			=>(int)$PaymentNumber,
		"paymentaddedby"	=>$paymentaddedby,
		"paymentaddedbyid"	=>(int)$paymentaddedbyid
	);

	$Query = pdo_query($Sql,$Esql);

	if($Query)
	{
		$recordid	= pdo_insert_id();

		
		$OutStandingPayment = (float)$_POST['paymentamount'] + ((float)$_POST['discount']) + ((float)$_POST['coupon']); 

		$ClientArr	= GetClientRecord($_POST['recordid']);
		$IsOpeningBalance = 0;
		if($_POST['paymentamount'] !='' && $_POST['paymentamount'] !='0' && $_POST['paymentamount'] !='0.00')
		{
			$Narration = "Payment";

			if($_POST['paymentmethod'] == "1")
			{
				$Narration = "Payment - Cash";
			}
			else
			{
				$Narration = "Online - Manual Payment";
			}
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['recordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['paymentamount'],strtotime($_POST['paymentdate']),$Narration,'payment',$recordid,'');*/
		}
		if($_POST['discount'] !='' && $_POST['discount'] !='0' && $_POST['discount'] !='0.00')
		{
			$Narration = "Discount";
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['recordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['discount'],strtotime($_POST['paymentdate']),$Narration,'discount',$recordid,'');*/
		}
		if($_POST['coupon'] !='' && $_POST['coupon'] !='0' && $_POST['coupon'] !='0.00')
		{
			$Narration = "Coupon";
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['recordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['coupon'],strtotime($_POST['paymentdate']),$Narration,'coupon',$recordid,'');*/
		}
		/*updateCustomerOutstandingBalance($_POST['clientid'], $_POST['recordid']);*/


		$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance = (outstandingbalance - :outstandingbalance) WHERE id=:id";
		$UpdateEsql	= array("outstandingbalance"=>(float)$OutStandingPayment,"id"=>(int)$_POST['recordid']);
		pdo_query($UpdateSql,$UpdateEsql);


		if($_POST['cansendsms'] > 0)
		{
			$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
			$CustomerEsql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid']);

			$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
			$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

			$customername		= $CustomerRows['name'];
			$customernameArr	= @explode(" ",$customername,2);

			$customername2		= $customernameArr[0]." JI";

			$customerphone		= $CustomerRows['phone'];

			$outstandingbalance	= "Rs.".@number_format($CustomerRows['outstandingbalance'],2);

			$paymentamount		= "Rs.".@number_format($_POST['paymentamount'],2);

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

		$response['success']	= true;
		$response['recordid']	= $recordid;
		$response['name']		= $_POST['name'];
		$response['msg']		= "Payment successfully added.";
		$response['toastmsg']	= "Payment successfully added.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllCustomerPayment")
{
	$perpage = 50;

	if($_POST['iscustomerarea'] > 0)
	{
		$perpage	= 10000;
	}

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}

	$TotalPayments	= 0;

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer payment.";

	$CustomerIDArr		= array();
	$CustomerNameArr	= array();
	$RecordListArr		= array();

	$condition	= "";

	$PaymentEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	/*if(trim($_POST['monthyear']) != "")*/
	if(trim($_POST['startdate']) != "" && trim($_POST['enddate2']) != "")
	{
		/*$StartDate	= strtotime($_POST['monthyear']);
		$EndDate	= strtotime($_POST['enddate'])+86399;*/

		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate2'])+86399;

		$condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$PaymentEsql['startdate']	= $StartDate;
		$PaymentEsql['enddate']		= $EndDate;
	}

	/*$paymenttypefilter	= " payment.amount !=:amount || payment.discount !=:discount || payment.coupon !=:coupon";
	$PaymentEsql['amount'] = "";
	$PaymentEsql['discount']	= "";
	$PaymentEsql['coupon'] = "";*/

	$paymenttypefilter	= "";

	if($_POST['showamountpayment'] > 0)
	{
		$paymenttypefilter		.= "payment.amount !=:amount";
		$PaymentEsql['amount']	= "";
	}

	if($_POST['showdiscount'] > 0)
	{
		if(trim($paymenttypefilter) != "")
		{
			$paymenttypefilter .= " || payment.discount !=:discount";
		}
		else
		{
			$paymenttypefilter .= "payment.discount !=:discount";
		}
		$PaymentEsql['discount']	= "";
	}

	if($_POST['showcoupon'] > 0)
	{
		if(trim($paymenttypefilter) != "")
		{
			$paymenttypefilter .= " || payment.coupon !=:coupon";
		}
		else
		{
			$paymenttypefilter .= "payment.coupon !=:coupon";
		}
		$PaymentEsql['coupon'] = "";
	}

	if(trim($paymenttypefilter) != "")
	{
		$paymenttypefilter	= " AND (".$paymenttypefilter.")";
	}

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND cust.clientid=:clientid";
		$PaymentEsql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
	{
		if($_POST['lineid'] == 9999)
		{
			$condition	.= " AND cust.lineid < :lineid";
			$PaymentEsql['lineid']	= 1;
		}
		else
		{
			$condition	.= " AND cust.lineid=:lineid";
			$PaymentEsql['lineid']	= (int)$_POST['lineid'];
		}
	}

	if($_POST['areaid'] > 0)
	{
		if($_POST['areaid'] == 9999)
		{
			$condition	.= " AND cust.areaid < :areaid";
			$PaymentEsql['areaid']	= 1;
		}
		else
		{
			$condition	.= " AND cust.areaid=:areaid";
			$PaymentEsql['areaid']	= (int)$_POST['areaid'];
		}
	}

	if($_POST['hawkerid'] > 0)
	{
		$condition	.= " AND cust.hawkerid=:hawkerid";
		$PaymentEsql['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0)
	{
		$condition	.= " AND cust.hawkerid=:hawkerid2";
		$PaymentEsql['hawkerid2']	= (int)$_POST['loginhawkerid'];
	}

	if($_POST['customerid'] > 0)
	{
		$condition	.= " AND cust.id=:id";
		$PaymentEsql['id']	= (int)$_POST['customerid'];
	}

	if($_POST['paymenttype'] == 'manual')
	{
		$condition	.= " AND payment.receipttype<>:receipttype AND payment.paymentmethod<>:paymentmethod";

		$PaymentEsql['paymentmethod']	= 1;
		$PaymentEsql['receipttype']		= "Online";
	}
	else if($_POST['paymenttype'] == 'automatic')
	{
		$condition	.= " AND payment.receipttype=:receipttype";
		$PaymentEsql['receipttype']	= "Online";
	}
	else if($_POST['paymenttype'] == 'cashpaymentonly')
	{
		$condition	.= " AND payment.paymentmethod=:paymentmethod AND payment.receipttype<>:receipttype";

		$PaymentEsql['paymentmethod']	= 1;
		$PaymentEsql['receipttype']		= "Online";
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$PaymentSql	= "SELECT cust.*, payment.paymentdate AS paymentdate, payment.amount AS paymentamount, payment.discount AS paymentdiscount, payment.coupon AS paymentcoupon, payment.id AS paymentid, payment.paymentid AS customerpaymentid FROM ".$Prefix."customer_payments payment, ".$Prefix."customers cust WHERE cust.id=payment.customerid AND cust.deletedon <:deletedon ".$condition." ".$paymenttypefilter." AND payment.deletedon < :paymentdeletedon ORDER BY payment.paymentid DESC";

	$PaymentQuery	= pdo_query($PaymentSql,$PaymentEsql);
	$PaymentNum		= pdo_num_rows($PaymentQuery);

	$TotalRec		= $PaymentNum;

	$PaymentSql2	= "SELECT SUM(payment.amount) AS paymentamount,SUM(payment.discount) AS paymentdiscount,SUM(payment.coupon) AS paymentcoupon FROM ".$Prefix."customer_payments payment, ".$Prefix."customers cust WHERE cust.id=payment.customerid AND cust.deletedon <:deletedon ".$condition." ".$paymenttypefilter." AND payment.deletedon < :paymentdeletedon";

	$PaymentQuery	= pdo_query($PaymentSql2,$PaymentEsql);
	$PaymentRows	= pdo_fetch_assoc($PaymentQuery);

	$TotalPayments	= $PaymentRows['paymentamount'];
	$TotalDiscount	= $PaymentRows['paymentdiscount'];
	$TotalCoupon	= $PaymentRows['paymentcoupon'];

	if($PaymentNum > 0)
	{
		$totalpages	= ceil($PaymentNum/$perpage);
		$offset		= ($_POST['page'] - 1) * $perpage;
		$addquery	= " LIMIT %d, %d";
	}
	else
	{
		$addquery	= "";
	}

	$Sql2	= $PaymentSql.$addquery;
	$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));

	$Query2	= pdo_query($Sql2,$PaymentEsql);
	$Num2	= pdo_num_rows($Query2);

	$RecordSetArr	= array();
	$index	= 0;

	$caneditpayment	= false;

	if(($_POST['areamanagerid'] < 1 && $_POST['loginlinemanid'] < 1 && $_POST['loginhawkerid'] < 1) && $_POST['clientid'] > 0)
	{
		$caneditpayment	= true;
	}

	if($Num2 > 0)
	{
		while($paymentrows = pdo_fetch_assoc($Query2))
		{
			$id					= $paymentrows['id'];
			$customerid			= $paymentrows['customerid'];
			$paymentid			= $paymentrows['paymentid'];
			$name				= $paymentrows['name'];
			$paymentdate		= $paymentrows['paymentdate'];
			$paymentamount		= $paymentrows['paymentamount'];
			$paymentamount		= (float)$paymentrows['paymentamount'];
			$discount			= (float)$paymentrows['paymentdiscount'];
			$coupon				= (float)$paymentrows['paymentcoupon'];
			$customerpaymentid	= $paymentrows['customerpaymentid'];

			$name2	= "#".$customerid." ".$name;

			if($paymentdate > 0)
			{
				$CreatedOnText		= date("d-M-Y",$paymentdate);
			}
			else
			{
				$CreatedOnText = '-';
			}

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "--";
			}

			$RecordSetArr[$index]['serialno']			= $index+1;
			$RecordSetArr[$index]['id']					= $id;
			$RecordSetArr[$index]['customerid']			= $customerid;
			$RecordSetArr[$index]['paymentid']			= $paymentid;
			$RecordSetArr[$index]['name']				= $name2;
			$RecordSetArr[$index]['date']				= $CreatedOnText;
			$RecordSetArr[$index]['amount']				= $paymentamount;
			$RecordSetArr[$index]['discount']			= $discount;
			$RecordSetArr[$index]['coupon']				= $coupon;
			$RecordSetArr[$index]['customerpaymentid']	= $customerpaymentid;
			$RecordSetArr[$index]['caneditpayment']		= $caneditpayment;

			$index++;
		}
	}
	else
	{
		if($_POST['iscustomerarea'] > 0)
		{
			$response['msg']	= "No payment detail found!";
		}
	}

	$RecordListArr['paymentlist']	= $RecordSetArr;

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer payment fetched successfully.";
	}

	$pageListArr	= array();
	$pagelistindex	= 0;

	for($pageloop = 1; $pageloop <= $totalpages; $pageloop++)
	{
		$pageListArr[$pagelistindex]['page']	= $pageloop;
		$pageListArr[$pagelistindex]['name']	= "Page ".$pageloop;

		$pagelistindex++;
	}

	$response['recordset']		= $RecordListArr;
	$response['perpage']		= (int)$perpage;
	$response['totalpages']		= (int)$totalpages;
	$response['paginglist']		= $pageListArr;
	$response['showpages']		= false;
	$response['totalrecord']	= $TotalRec;
	$response['totalpayment']	= number_format($TotalPayments,2);
	$response['totaldiscount']	= number_format($TotalDiscount,2);
	$response['totalcoupon']	= number_format($TotalCoupon,2);

	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeletePayment")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete Payment, Please try later.";

	$SelSQL = "SELECT * FROM ".$Prefix."customer_payments WHERE id=:id AND deletedon <:deletedon";
	$SelESQL = array("id"=>$_POST['recordid'],"deletedon"=>1);
	$SelQuery = pdo_query($SelSQL,$SelESQL);
	$SelNum		= pdo_num_rows($SelQuery);
	$AmountToDeduct = 0;
	$CustomerID = 0;
	if($SelNum > 0)
	{
		$SelRow	= pdo_fetch_assoc($SelQuery);

		$CustomerID = (float)$SelRow['customerid'];
		$Amount = (float)$SelRow['amount'];
		$Discount = (float)$SelRow['discount'];
		$Coupon = (float)$SelRow['coupon'];
	
		$AmountToDeduct = $Amount + ($Discount) + ($Coupon); 
	}
	$DelSql		= "UPDATE ".$Prefix."customer_payments
	SET
	deletedon	=:deletedon
	WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$DelEsql	= array(
		'deletedon'	=>time(),
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']	
	);

	$DelQuery	= pdo_query($DelSql,$DelEsql);

	if($DelQuery)
	{
		if(abs($AmountToDeduct) > 0 && $CustomerID > 0)
		{
			$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance = (outstandingbalance + :outstandingbalance) WHERE id=:id";
			$UpdateEsql	= array("outstandingbalance"=>(float)$AmountToDeduct,"id"=>(int)$CustomerID);
			pdo_query($UpdateSql,$UpdateEsql);
		}
		/*$SelSql		= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND paymentid=:paymentid";
		$SelEsql	= array("paymentid"=>(int)$_POST['recordid'],'clientid'=>(int)$_POST['clientid']);
		$SelQuery	= pdo_query($SelSql,$SelEsql);
		$SelNum		= pdo_num_rows($SelQuery);
		if($SelNum > 0)
		{
			$SelRow			= pdo_fetch_assoc($SelQuery);

			$OldLineTotal	= $SelRow['linetotal'];
			$CustomerID		= $SelRow['customerid'];
			$OldAmount		= $SelRow['amount'];
			$OldLogDate		= $SelRow['logdate'];

			$DelSQL		= "DELETE FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND paymentid=:paymentid";

			$DelESQL	= array("paymentid"=>(int)$_POST['recordid'],'clientid'=>(int)$_POST['clientid']);
			pdo_query($DelSQL,$DelESQL);
					
			GetCustomerLineTotalUpdated($CustomerID);

			updateCustomerOutstandingBalance($_POST['clientid'], $CustomerID);
		}*/

		$Response['success']	= true;
		$Response['msg']		= "Payment Record deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPaymentDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch payment detail.";

	$sql	= "SELECT * FROM ".$Prefix."customer_payments WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon ORDER BY paymentid DESC";
	$esql	= array("id"=>(int)$_POST['paymentid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$customerid		= (int)$row['customerid'];
		$amount			= $row['amount'];
		$paymentdate	= $row['paymentdate'];
		$createdon		= $row['createdon'];
		$remark			= $row['remark'];
		$discount		= $row['discount'];
		$coupon			= $row['coupon'];
		$paymentmethod	= $row['paymentmethod'];
		$receipttype	= $row['receipttype'];
		$receiptbyname	= $row['receiptbyname'];
		$paymentid		= $row['paymentid'];

		$Paymentmethodstr	= "Cash";

		if($paymentmethod < 1 && $paymentmethod != "")
		{
			$Paymentmethodstr	= "Online Wallet";
		}

		$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
		$CustomerEsql	= array("id"=>(int)$customerid,"clientid"=>(int)$_POST['clientid']);

		$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
		$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

		$customerid2	= $CustomerRows['customerid'];
		$name			= $CustomerRows['name'];
		$phone			= $CustomerRows['phone'];

		$paymentdatestr ='-';
		if($paymentdate > 0)
		{
			$paymentdatestr = date("Y-m-d",$paymentdate);
		}

		$detailArr["customerid"]	= $customerid2;
		$detailArr["name"]			= $name;
		$detailArr["phone"]			= $phone;
		$detailArr["receiptby"]		= $receiptbyname." (".ucwords($receipttype).")";
		$detailArr["paymentdate"]	= $paymentdatestr;
		$detailArr["amountpaid"]	= @number_format($amount,2);
		$detailArr["paymentmethod"]	= $Paymentmethodstr;
		$detailArr["discount"]		= @number_format($discount,2);
		$detailArr["coupon"]		= @number_format($coupon,2);
		$detailArr["remark"]		= $row['remark'];
		$detailArr["paymentid"]		= $row['paymentid'];

		$response['success']	= true;
		$response['msg']		= "Payment detail fetched successfully.";
	}

	$response['paymentdetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetEditPaymentDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch payment detail.";

	$sql	= "SELECT * FROM ".$Prefix."customer_payments WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon ORDER BY paymentid DESC";
	$esql	= array("id"=>(int)$_POST['paymentid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$customerid		= (int)$row['customerid'];
		$amount			= $row['amount'];
		$paymentdate	= $row['paymentdate'];
		$createdon		= $row['createdon'];
		$remark			= $row['remark'];
		$discount		= $row['discount'];
		$coupon			= $row['coupon'];
		$paymentmethod	= $row['paymentmethod'];
		$receipttype	= $row['receipttype'];
		$receiptbyname	= $row['receiptbyname'];
		$paymentid		= $row['paymentid'];
		$receiptbyid	= $row['receiptbyid'];

		if($paymentdate > 0)
		{
			$paymentdatecal = date("Y-m-d",$paymentdate);
		}
		else
		{
			$paymentdatecal	= date("Y-m-d");
		}

		$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
		$CustomerEsql	= array("id"=>(int)$customerid,"clientid"=>(int)$_POST['clientid']);

		$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
		$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

		$customerid2	= $CustomerRows['customerid'];
		$name			= $CustomerRows['name'];
		$phone			= $CustomerRows['phone'];
		$areaid			= (int)$CustomerRows['areaid'];
		$areaname		= $AllAreaArr[$areaid]['name'];
		$lineid			= (int)$CustomerRows['lineid'];
		$linename		= $AllLineArr[$lineid]['name'];

		$detailArr["paymentmethod"]		= (int)$paymentmethod;
		$detailArr["paymentamount"]		= ($amount * 100)/100;
		$detailArr["discount"]			= ($discount * 100)/100;
		$detailArr["coupon"]			= ($coupon * 100)/100;
		$detailArr["remark"]			= $remark;
		$detailArr["paymentdate"]		= $paymentdatecal;
		$detailArr["receipttype"]		= $receipttype;
		$detailArr["receiptbyid"]		= (int)$receiptbyid;
		$detailArr["receiptbyname"]		= $receiptbyname;
		$detailArr["customerrecordid"]	= (int)$customerid;

		$response['success']	= true;
		$response['msg']		= "Payment detail fetched successfully.";
	}

	$response['detail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditCustomerPayment")
{
    $response['success']	= false;
    $response['msg']		= "Unable to update payment.";

	/*if($_POST['areamanagerid'] > 0 || $_POST['linemanid'] > 0 || $_POST['hawkerid'] > 0)
	{
		$_POST['cansendsms']	= 1;
	}

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($_POST['clientid']);

	if($_POST['cansendsms'] > 0 && $totalsmscreditsavaiable < 1)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}*/

	$_POST['cansendsms']	= 0;

	$EditCustomerSql	= "UPDATE ".$Prefix."customers SET 
	canprintinvoice		=:canprintinvoice
	WHERE
	id					=:id
	AND
	clientid			=:clientid";

	$EditCustomerEsql	= array(
		"canprintinvoice"	=>(int)$_POST['canprintinvoice'],
		"id"				=>(int)$_POST['customerrecordid'],
		"clientid"			=>(int)$_POST['clientid']
	);

	$EditCustomerQuery	= pdo_query($EditCustomerSql,$EditCustomerEsql);


	$SelSQL = "SELECT * FROM ".$Prefix."customer_payments WHERE id=:id AND deletedon <:deletedon";
	$SelESQL = array("id"=>$_POST['paymentid'],"deletedon"=>1);
	$SelQuery = pdo_query($SelSQL,$SelESQL);
	$SelNum		= pdo_num_rows($SelQuery);
	$PreAmountToDeduct = 0;
	$CustomerID = 0;
	if($SelNum > 0)
	{
		$SelRow	= pdo_fetch_assoc($SelQuery);
		$CustomerID = (float)$SelRow['customerid'];
		$Amount = (float)$SelRow['amount'];
		$Discount = (float)$SelRow['discount'];
		$Coupon = (float)$SelRow['coupon'];

		$PreAmountToDeduct = $Amount + ($Discount) + ($Coupon); 
		
	}
	
	$Sql	= "UPDATE ".$Prefix."customer_payments SET 
	amount			=:amount,
	paymenttype		=:paymenttype,
	paymentdate		=:paymentdate,
	remark			=:remark,
	discount		=:discount,
	coupon			=:coupon,
	paymentmethod	=:paymentmethod,
	receipttype		=:receipttype,
	receiptbyid		=:receiptbyid,
	receiptbyname	=:receiptbyname
	WHERE
	id				=:id
	AND
	clientid		=:clientid";

	$Esql	= array(
		"amount"		=>(float)$_POST['paymentamount'],
		"paymenttype"	=>'c',
		"paymentdate"	=>strtotime($_POST['paymentdate']),
		"remark"		=>$_POST['remark'],
		"discount"		=>(float)$_POST['discount'],
		"coupon"		=>(float)$_POST['coupon'],
		"paymentmethod"	=>(int)$_POST['paymentmethod'],
		"receipttype"	=>$_POST['receipttype'],
		"receiptbyid"	=>(int)$_POST['receiptbyid'],
		"receiptbyname"	=>$_POST['receiptbyname'],
		"id"			=>(int)$_POST['paymentid'],
		"clientid"		=>(int)$_POST['clientid'],
	);

	$Query = pdo_query($Sql,$Esql);

	if($Query)
	{
		$recordid	= $_POST['paymentid'];

		$CurrentAmount = (float)$_POST['paymentamount'] + ((float)$_POST['discount']) + ((float)$_POST['coupon']);

		$Diff		= (float)$CurrentAmount - (float)$PreAmountToDeduct;
	
		if(abs($Diff) > 0 && $CustomerID > 0)
		{
			$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance = (outstandingbalance - :outstandingbalance) WHERE id=:id";
			$UpdateEsql	= array("outstandingbalance"=>(float)$Diff,"id"=>(int)$CustomerID);
			pdo_query($UpdateSql,$UpdateEsql);
		}

		/*if($_POST['cansendsms'] > 0)
		{
			$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
			$CustomerEsql	= array("id"=>(int)$_POST['customerrecordid'],"clientid"=>(int)$_POST['clientid']);

			$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
			$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

			$customername		= $CustomerRows['name'];
			$customernameArr	= @explode(" ",$customername,2);

			$customername2		= $customernameArr[0]." JI";

			$customerphone		= $CustomerRows['phone'];

			$paymentamount		= "Rs.".@number_format($_POST['paymentamount'],2);

			$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
			$ClientEsql	= array("id"=>(int)$_POST['clientid']);

			$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
			$ClientRows		= pdo_fetch_assoc($ClientQuery);

			$clientname		= $ClientRows['clientname'];

		$Message = "Dear <arg1>,

Payment Received: <arg2>

Thanks,
<arg3>

Team Orlo";
		
			$messagearr[] = array("phoneno"=>$customerphone,"arg1"=>$customername2,"arg2"=>$paymentamount,"arg3"=>$clientname);
			$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$InvoicePaymentID);

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
		}*/

		$ClientArr = GetClientRecord($_POST['customerrecordid']);
		$IsOpeningBalance = 0;
		if($_POST['paymentamount'] !='' && $_POST['paymentamount'] !='0' && $_POST['paymentamount'] !='0.00')
		{
			$Narration = "Payment";

			if($_POST['paymentmethod'] == "1")
			{
				$Narration = "Payment - Cash";
			}
			else
			{
				$Narration = "Online - Manual Payment";
			}
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['customerrecordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['paymentamount'],strtotime($_POST['paymentdate']),$Narration,'payment',$recordid,'');*/
		}
		if($_POST['discount'] !='' && $_POST['discount'] !='0' && $_POST['discount'] !='0.00')
		{
			$Narration = "Discount";
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['customerrecordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['discount'],strtotime($_POST['paymentdate']),$Narration,'discount',$recordid,'');*/
		}
		if($_POST['coupon'] !='' && $_POST['coupon'] !='0' && $_POST['coupon'] !='0.00')
		{
			$Narration = "Coupon";
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['customerrecordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['coupon'],strtotime($_POST['paymentdate']),$Narration,'coupon',$recordid,'');*/
		}
		/*updateCustomerOutstandingBalance($_POST['clientid'], $_POST['customerrecordid']);*/

		$response['success']	= true;
		$response['recordid']	= $recordid;
		$response['name']		= $_POST['name'];
		$response['msg']		= "Payment successfully updated.";
		$response['toastmsg']	= "Payment successfully updated.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ResendPaymentMessage")
{
    $response['success']	= false;
    $response['msg']		= "Unable to save payment.";

	$_POST['cansendsms']	= 1;

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

	$PaymentSql		= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND id=:id";
	$PaymentEsql	= array("clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid']);

	$PaymentQuery	= pdo_query($PaymentSql,$PaymentEsql);
	$PaymentNum		= pdo_num_rows($PaymentQuery);

	if($PaymentNum > 0)
	{
		$paymentrow	= pdo_fetch_assoc($PaymentQuery);

		$customerid		= $paymentrow['customerid'];
		$amount			= $paymentrow['amount'];
		$paymentdate	= $paymentrow['paymentdate'];
		$discount		= $paymentrow['discount'];
		$coupon			= $paymentrow['coupon'];

		if($_POST['cansendsms'] > 0)
		{
			$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
			$CustomerEsql	= array("id"=>(int)$customerid,"clientid"=>(int)$_POST['clientid']);

			$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
			$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

			$customername		= $CustomerRows['name'];
			$customernameArr	= @explode(" ",$customername,2);

			$customername2		= $customernameArr[0]." JI";

			$customerphone		= $CustomerRows['phone'];

			$outstandingbalance	= "Rs.".@number_format($CustomerRows['outstandingbalance'],2);

			$paymentamount		= "Rs.".@number_format($amount,2);

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

		$Message = "Dear User,

Payment Received: <arg1>

Balance Now: <arg2>


Thanks,
http://premnews.orlopay.com
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
		}

		$response['success']	= true;
		$response['name']		= $customername;
		$response['msg']		= "Message successfully send.";
		$response['toastmsg']	= "Message successfully send.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPaymentMessagePreview")
{
    $response['success']	= false;
    $response['msg']		= "Unable to create message preview.";

	$_POST['cansendsms']	= 1;

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

	$PaymentSql		= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND id=:id";
	$PaymentEsql	= array("clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid']);

	$PaymentQuery	= pdo_query($PaymentSql,$PaymentEsql);
	$PaymentNum		= pdo_num_rows($PaymentQuery);

	if($PaymentNum > 0)
	{
		$paymentrow	= pdo_fetch_assoc($PaymentQuery);

		$customerid		= $paymentrow['customerid'];
		$amount			= $paymentrow['amount'];
		$paymentdate	= $paymentrow['paymentdate'];
		$discount		= $paymentrow['discount'];
		$coupon			= $paymentrow['coupon'];

		if($_POST['cansendsms'] > 0)
		{
			$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
			$CustomerEsql	= array("id"=>(int)$customerid,"clientid"=>(int)$_POST['clientid']);

			$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
			$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

			$customername		= $CustomerRows['name'];
			$customernameArr	= @explode(" ",$customername,2);

			$customername2		= $customernameArr[0]." JI";

			$customerphone		= $CustomerRows['phone'];

			$outstandingbalance	= "Rs.".@number_format($CustomerRows['outstandingbalance'],2);

			$paymentamount		= "Rs.".@number_format($amount,2);

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

		/*$Message = "Dear ".$customername2.",

Payment Received: ".$paymentamount."

Balance Now: ".$outstandingbalance."


Thanks,
".$clientname."
Team Orlo";*/

$Message = "Dear User,

Payment Received: ".$paymentamount."

Balance Now: ".$outstandingbalance."


Thanks,
".$clientname."
Team Orlo";

		}

		$smscredits	= ceil(strlen(trim($Message)) / 160);

		$response['success']	= true;
		$response['name']		= $customername;
		$response['msg']		= "Payment messge preview successfully generated.";
		$response['preview']	= nl2br($Message);
		$response['smscredits']	= (int)$smscredits;
	}

    $json = json_encode($response);
    echo $json;
	die;
}
?>