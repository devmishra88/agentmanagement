<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
set_time_limit(0);
/*set response code - 200 OK*/
http_response_code(200);

include_once "dbconfig.php";
$createdon	= time();

if($_POST['Mode'] == "GetInvoiceYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list year.";

	$currentyear	= date("Y");
	/*$startyear	= $currentyear - $InvoiceYears;*/

	$RecordSetArr	= array();

	$index	= 0;

	for($yearloop = $currentyear; $yearloop >= $startyear; $yearloop--)
	{
		$RecordSetArr[$index]['index']	= $index+1;
		$RecordSetArr[$index]['year']	= $yearloop;

		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['recordset']		= $RecordSetArr;
		$response['defaultyear']	= $currentyear;
		$response['msg']			= "Year listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceMonthByYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list month.";

	$RecordSetArr	= array();
	$index	= 0;

	$totalamount	= 0;

	if($_POST['year'] != "")
	{
		$currentyear	= date("Y");

		$startmonth	= "01";
		$endmonth	= "12";

		if($_POST['year'] == $currentyear)
		{
			$endmonth	= date("m");
		}

		for($monthloop = $startmonth; $monthloop <= $endmonth; $monthloop++)
		{
			$month	= "0".(int)$monthloop;
			if($monthloop > 9)
			{
				$month	= (int)$monthloop;
			}

			//$hasinvoice	= CheckClientInvoiceByYearMonth($_POST['clientid'], $month, $_POST['year']);
			$hasinvoice = false;
			$InvoiceSQL 	= "SELECT COUNT(*) AS C, SUM(finalamount) as Amt, invoicedate FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid AND deletedon <:deletedon";
			$InvoiceESQL 	= array("invoicemonth"=>(int)$month,"invoiceyear"=>(int)$_POST["year"],"clientid"=>(int)$_POST['clientid'],'deletedon'=>1);

			$InvoiceQuery		= pdo_query($InvoiceSQL,$InvoiceESQL);
			$InvoiceRow			= pdo_fetch_assoc($InvoiceQuery);
			$TotalInvoice		= $InvoiceRow["C"];
			$TotalInvoiceAmt	= $InvoiceRow["Amt"];
			$CurrentMonthInvoiceDate	= $InvoiceRow["invoicedate"];
			
			if($TotalInvoice > 0)
			{
				$hasinvoice = true;
			}

			$CheckMonth = $month;
			$CheckYear 	= $_POST['year'];

			if($CheckMonth == 12)
			{
				$CheckMonth = 1;
				$CheckYear  += 1;
			}
			else
			{
				$CheckMonth += 1;
			}
			$InvoiceSQL 	= "SELECT COUNT(*) AS C FROM ".$Prefix."invoices WHERE invoicemonth>=:invoicemonth AND invoiceyear>=:invoiceyear AND clientid=:clientid AND deletedon <:deletedon";
			$InvoiceESQL 	= array("invoicemonth"=>(int)$CheckMonth,"invoiceyear"=>(int)$CheckYear,"clientid"=>(int)$_POST['clientid'],'deletedon'=>1);

			$InvoiceQuery		= pdo_query($InvoiceSQL,$InvoiceESQL);
			$InvoiceRow			= pdo_fetch_assoc($InvoiceQuery);
			$IsFutureInvoice	= $InvoiceRow["C"];

			$QueueCheckSQL		= "SELECT COUNT(*) as C FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND year=:year AND month=:month AND isprocessing=:isprocessing AND status <:status";
			$QueueCheckESQL		= array('clientid'=>(int)$_POST['clientid'],"isprocessing"=>1,"year"=>$_POST['year'],"month"=>$month,"status"=>1);
			
			$QueueCheckQuery	= pdo_query($QueueCheckSQL,$QueueCheckESQL);
			$QueueRowCheck		= pdo_fetch_assoc($QueueCheckQuery);

			$QueueCheckCount	= $QueueRowCheck["C"];

			$hasinvoicerequestinqueue	= false;

			if($QueueCheckCount > 0)
			{
				$hasinvoicerequestinqueue	= true;
			}

			$CanRegenerateInvoice = true;
			if($IsFutureInvoice > 0)
			{
				$CanRegenerateInvoice = false;
			}

			$totalamount	+= (float)$TotalInvoiceAmt;

			$monthname	= date("F",strtotime($month."/01/".$_POST['year']));

			$RecordSetArr[$index]['index']						= $index+1;
			$RecordSetArr[$index]['id']							= (int)$month;
			$RecordSetArr[$index]['count']						= (int)$TotalInvoice;
			$RecordSetArr[$index]['total']						= (float)$TotalInvoiceAmt;
			$RecordSetArr[$index]['hasinvoice']					= $hasinvoice;
			$RecordSetArr[$index]['canregeneratelink']			= $CanRegenerateInvoice;
			$RecordSetArr[$index]['name']						= $monthname;
			$RecordSetArr[$index]['hasinvoicerequestinqueue']	= $hasinvoicerequestinqueue;

			$index++;
		}
	}

	$custcondition	= "";

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$custcondition	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$custcondition	.= " AND lineid IN(".$lineids.")";
	}

	$OutStandingSql		= "SELECT SUM(outstandingbalance) as totaloutstandingbalance FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon ".$custcondition."";
	$OutStandingEsql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1);

	$OutStandingQuery	= pdo_query($OutStandingSql,$OutStandingEsql);
	$OutStandingRows	= pdo_fetch_assoc($OutStandingQuery);

	$totaloutstandingbalance	= (float)$OutStandingRows['totaloutstandingbalance'];

	$InactiveOutStandingSql		= "SELECT SUM(outstandingbalance) AS inactiveoutstandingbalance, COUNT(id) AS customercount FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND isinactive=:isinactive AND outstandingbalance > :outstandingbalance ".$custcondition."";
	$InactiveOutStandingEsql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,"isinactive"=>1,"outstandingbalance"=>0);

	$InactiveOutStandingQuery	= pdo_query($InactiveOutStandingSql,$InactiveOutStandingEsql);
	$InactiveOutStandingRows	= pdo_fetch_assoc($InactiveOutStandingQuery);

	$inactiveoutstandingbalance	= (float)$InactiveOutStandingRows['inactiveoutstandingbalance'];
	$customercount				= (int)$InactiveOutStandingRows['customercount'];

	if(!empty($RecordSetArr))
	{
		$response['success']			= true;
		$response['totalamount']		= $totalamount;
		$response['totaloutstanding']	= $totaloutstandingbalance;
		$response['inactiveoutstanding']= $inactiveoutstandingbalance;
		$response['customercount']		= $customercount;
		$response['recordset']			= $RecordSetArr;
		$response['msg']				= "Year listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'CreateInvoices')
{
    $response['success']	= false;
    $response['msg']		= "Unable to create invoices.";

	$SQL	= "SELECT * FROM ".$Prefix."admin ";
	$ESQL	= array();

	$AdmQuery	= pdo_query($SQL,$ESQL);
	$AdmNum		= pdo_num_rows($AdmQuery);
	if($AdmNum > 0)
	{
		$AdmRow	= pdo_fetch_assoc($AdmQuery);
		$GlobalCovenienceCharge = $AdmRow['conveniencecharge'];
	}

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($_POST['clientid']);

	$GetAllCity		= GetAllCityNames();
	$GetAllStates	= GetAllStateNames();

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND status <:status AND isprocessing <:isprocessing ORDER BY id ASC";
	$CheckESQL = array("status"=>1,'clientid'=>(int)$_POST['clientid'],"isprocessing"=>1);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($totalsmscreditsavaiable < 1000 && $_POST['cansendsms'] > 0)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	if(($totalsmscreditsavaiable < 1000) && $_POST['cansendsms'] > 0 && $CheckNum > 0)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$CurrentTime = time();

	if($CheckNum > 0)
	{
		$CurrentInventoryFreqArr	= GetInventoryFrequency();

		$InventoryNameArr	= GetInventoryNames();

		$InventoryCatNameArr	= GetAllCategory();

		$AlphaCode	= AlphaCode(2);

		$CampaignMsg	= "";

		while($CheckRow = pdo_fetch_assoc($CheckQuery))
		{
			$RequestID		= $CheckRow['id'];
			$ClientID		= $CheckRow['clientid'];
			$Month			= $CheckRow['month'];
			$Year			= $CheckRow['year'];
			$isprocessing	= $CheckRow['isprocessing'];

			$CheckSQL2		= "SELECT COUNT(*) as C FROM ".$Prefix."invoice_request_queue WHERE id=:id AND year=:year AND month=:month AND isprocessing < :isprocessing AND status <:status";
			$CheckESQL2		= array("isprocessing"=>1,'id'=>(int)$RequestID,"year"=>$Year,"month"=>$Month,"status"=>1);
			
			$CheckQuery2	= pdo_query($CheckSQL2,$CheckESQL2);
			$RowCheck		= pdo_fetch_assoc($CheckQuery2);

			$CheckCount		= $RowCheck["C"];

			if($CheckCount > 0)
			{
				$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET isprocessing=:isprocessing,billdate=:billdate WHERE id=:id";
				$UpdateESQL	= array("isprocessing"=>1,"billdate"=>strtotime($_POST['billdate']),"id"=>(int)$RequestID);

				pdo_query($UpdateSQL,$UpdateESQL);
			}
			else
			{
				continue;
			}

			$InvoiceNumber  = GetNextInvoiceID($ClientID);

			$ClientSQL		= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND deletedon < :deletedon";
			$ClientESQL		= array("id"=>(int)$ClientID,"deletedon"=>1);

			$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
			$ClientNum		= pdo_num_rows($ClientQuery);

			if($ClientNum > 0)
			{
				$ClientRow	= pdo_fetch_assoc($ClientQuery);

				$conveniencechargetype	= $ClientRow['conveniencechargetype'];
				$isservicecharge		= $ClientRow['isservicecharge'];
				$servicechargetype		= $ClientRow['servicechargetype'];
				$servicecharge			= $ClientRow['servicecharge'];

				if($conveniencechargetype > 0)
				{
					$conveniencecharge = $ClientRow['conveniencecharge'];
				}
				else
				{
					$conveniencecharge = $GlobalCovenienceCharge;
				}
			}
			else
			{
				continue;
			}

			$ClientInventoryPriceArr = GetActiveSubscriptionByClientID($ClientID,$Month,$Year);
			$ClientInventoryStr = '';
			if(!empty($ClientInventoryPriceArr))
			{
				foreach($ClientInventoryPriceArr as $key => $value)
				{
					$ClientInventoryStr .=$key.","; 
				}
				$ClientInventoryStr .= "@@";
				$ClientInventoryStr = str_replace(",@@","",$ClientInventoryStr);
			}
			else
			{
				$ClientInventoryStr = '-1';
			}

			$DateMonth	 = $Month;

			if($DateMonth < 10)
			{
				$DateMonth = "0".$DateMonth;
			}

			$CheckStartDate		= strtotime($DateMonth."/01/".$Year);
			$LastDayofMonth		= date("t",$CheckStartDate);				
			$CheckEndDate		= strtotime($DateMonth."/".$LastDayofMonth."/".$Year)+86399;

			$InvoiceDate	= strtotime($DateMonth."/01/".$Year);
			$InvoiceDateEnd = $InvoiceDate+86399;

			$HolidaySQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND customertype<:customertype";
			$HolidayESQL	= array("clientid"=>(int)$ClientID,"date1"=>(int)$CheckStartDate,"date2"=>(int)$CheckEndDate,"date3"=>(int)$CheckStartDate,"date4"=>(int)$CheckEndDate,"deletedon"=>1,'customertype'=>1);

			$HolidayQuery	= pdo_query($HolidaySQL,$HolidayESQL);
			$HolidayNum		= pdo_num_rows($HolidayQuery);

			$GlobalHolidayDeductionAllArr		= array();

			if($HolidayNum > 0)
			{
				while($HolidayRow = pdo_fetch_assoc($HolidayQuery))
				{
					$HoliStartDate	= $HolidayRow['startdate'];
					$HoliEndDate	= $HolidayRow['enddate'];
					$CustomerType	= $HolidayRow['customertype'];
					$CustomerID		= $HolidayRow['customerid'];
					$InventoryType	= $HolidayRow['inventorytype'];
					$InventoryID	= $HolidayRow['inventoryid'];
					
					$PreDiff = 0;
					$CalcStartDate = $HoliStartDate;
					
					if($CheckStartDate > $HoliStartDate)
					{	
						$CalcStartDate = $CheckStartDate;
					}

					$CalcEndDate = strtotime(date("m/d/Y",$CheckEndDate));
					if($HoliEndDate < $CheckEndDate)
					{
						$CalcEndDate = $HoliEndDate;
					}

					if($CustomerType < 1)
					{
						foreach($ClientInventoryPriceArr as $key => $value)
						{
							$AddDate = 0;
							if($InventoryType < 1)
							{
								$AddDate = 1;
							}
							else if($key == $InventoryID)
							{
								$AddDate = 1;
							}
							if($AddDate > 0)
							{
								while($CalcStartDate <= $CalcEndDate)
								{
									$GlobalHolidayDeductionAllArr[$key][] = $CalcStartDate;

									$CalcStartDate = $CalcStartDate + 86400;
								} 	
							}
						}
					}
				}
			}

			$CheckInvoiceSQL	= "SELECT * FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid AND deletedon <:deletedon GROUP BY customerid";
			$CheckInvoiceESQL	= array("invoicemonth"=>(int)$Month,"invoiceyear"=>(int)$Year,"clientid"=>(int)$ClientID,'deletedon'=>1);
			$CheckInvoiceQuery	= pdo_query($CheckInvoiceSQL,$CheckInvoiceESQL);

			$CheckInvoiceNum	= pdo_num_rows($CheckInvoiceQuery);
			$CustomersArr		= array();
			$InvoiceCustArr		= array();

			if($CheckInvoiceNum > 0)
			{
				while($CheckInvoiceRow = pdo_fetch_assoc($CheckInvoiceQuery))
				{
					$TepmCustomerID			= $CheckInvoiceRow['customerid']; 
					$TempInvoiceID			= $CheckInvoiceRow['id']; 
					$TempInvoiceNumber		= $CheckInvoiceRow['invoiceid']; 
					$TempFinalAmount		= $CheckInvoiceRow['finalamount']; 
					
					$CustomersArr[] = $TepmCustomerID; 	
					
					$InvoiceCustArr[$TepmCustomerID]['id'] 		 = $TempInvoiceID;
					$InvoiceCustArr[$TepmCustomerID]['invoiceid'] = $TempInvoiceNumber;
					$InvoiceCustArr[$TepmCustomerID]['finalamount'] = $TempFinalAmount;
				}
			}
			$ExtEsqlArr = array();
			$CustomerStr = "";
			$extarg = " ";

			/*$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status $extarg ORDER BY sequence ASC,customerid ASC";
			$CustESQL	= array("clientid"=>(int)$ClientID,"deletedon"=>1,"status"=>1);*/

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

				$custcondition	.= " AND cust.lineid IN(".$lineids.") ";
			}
			
			$CustSQL	= "SELECT cust.* FROM ".$Prefix."customers cust,".$Prefix."area ar,".$Prefix."line ln WHERE cust.clientid=:clientid AND cust.deletedon <:deletedon AND cust.status=:status AND ar.status=:status2 AND ln.status=:status3 AND ar.clientid=:clientid2 AND ln.clientid=:clientid3 AND ar.deletedon <:deletedon2 AND ln.deletedon <:deletedon3 AND cust.areaid=ar.id AND cust.lineid=ln.id ".$custcondition." $extarg ORDER BY ar.name ASC,ln.name ASC, cust.sequence ASC,cust.customerid ASC";
			
			$CustESQL	= array("clientid"=>(int)$ClientID,"deletedon"=>1,"clientid2"=>(int)$ClientID,"deletedon2"=>1,"clientid3"=>(int)$ClientID,"deletedon3"=>1,"status"=>1,"status2"=>1,"status3"=>1);

			$CustESQL2	= array_merge($CustESQL,$ExtEsqlArr);

			$CustQuery	= pdo_query($CustSQL,$CustESQL2);
			$CustNum	= pdo_num_rows($CustQuery);

			$TotalCustomers = $CustNum;

			$InvoiceCreatedCounter = 0;
			$CustomerCounter = 0;
			
			$NewInvoiceCustomerArr 			= array();
			if($CustNum > 0)
			{
				while($CustRow	= pdo_fetch_assoc($CustQuery))
				{
					$SubscriptionArr	= array();
					$CustomerHolidayDeductionArr 	= $GlobalHolidayDeductionAllArr;
					
					$CustomerID			= $CustRow["id"];
					$CustomerNumber		= $CustRow["customerid"];
					$CustomerName		= $CustRow["name"];
					$CustomerEmail		= $CustRow["email"];
					$CustomerAddress1	= $CustRow["address1"];
					$CustomerAddress2	= $CustRow["address2"];
					$CustomerCityID		= $CustRow["cityid"];
					$CustomerStateID	= $CustRow["stateid"];
					$CustomerPinCode	= $CustRow["pincode"];
					$CustomerPhone		= $CustRow["phone"];
					$IsDiscount			= $CustRow["isdiscount"];
					$DiscountPercent	= $CustRow["discount"];
					$OpeningBalance		= $CustRow["openingbalance"];
					$outstandingbalance		= $CustRow["outstandingbalance"];
				
					$IsPaymentGateWay	= $CustRow["ispaymentgateway"];
					$RAZOR_PAY_API_KEY_Client	= $CustRow["razor_pay_api_key"];
					$RAZOR_PAY_API_SECRET_Client	= $CustRow["razor_pay_api_secret"];
					
					$ClientArr['areaid']	= $CustRow['areaid'];
					$ClientArr['lineid']	= $CustRow['lineid'];
					$ClientArr['sublineid'] = $CustRow['sublineid'];
					$ClientArr['linemanid']	= $CustRow['linemanid'];
					$ClientArr['hawkerid']	= $CustRow['hawkerid'];

					if(trim($OpeningBalance) =='' || $OpeningBalance == NULL)
					{
						$CustomerCounter++;
						
						continue;
					}

					$CustHolidaySQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND customertype=:customertype AND customerid=:customerid";
					$CustHolidayESQL	= array("clientid"=>(int)$ClientID,"date1"=>(int)$CheckStartDate,"date2"=>(int)$CheckEndDate,"date3"=>(int)$CheckStartDate,"date4"=>(int)$CheckEndDate,"deletedon"=>1,'customertype'=>1,'customerid'=>$CustomerID);

					$CustHolidayQuery	= pdo_query($CustHolidaySQL,$CustHolidayESQL);
					$CustHolidayNum		= pdo_num_rows($CustHolidayQuery);
					if($CustHolidayNum > 0)
					{
						while($CustHolidayRow = pdo_fetch_assoc($CustHolidayQuery))
						{
							$CustHoliStartDate	= $CustHolidayRow['startdate'];
							$CustHoliEndDate	= $CustHolidayRow['enddate'];
							$CustCustomerType	= $CustHolidayRow['customertype'];
							$CustCustomerID		= $CustHolidayRow['customerid'];
							$CustInventoryType	= $CustHolidayRow['inventorytype'];
							$CustInventoryID	= $CustHolidayRow['inventoryid'];
							
							$CalcStartDate = $CustHoliStartDate;
							
							if($CheckStartDate > $CustHoliStartDate)
							{	
								$CalcStartDate = $CheckStartDate;
							}

							$CalcEndDate = strtotime(date("m/d/Y",$CheckEndDate));
							if($CustHoliEndDate < $CheckEndDate)
							{
								$CalcEndDate = $CustHoliEndDate;
							}

							foreach($ClientInventoryPriceArr as $key => $value)
							{
								$AddDate = 0;
								
								if($CustInventoryType < 1)
								{
									$AddDate = 1;
								}
								else if($key == $CustInventoryID)
								{
									$AddDate = 1;
								}
								
								if($AddDate > 0)
								{
									$TempStartDate = $CalcStartDate;
									$TempEndDate = $CalcEndDate;
									while($TempStartDate <= $TempEndDate)
									{
										$CustomerHolidayDeductionArr[$key][] = $TempStartDate;

										$TempStartDate = $TempStartDate + 86400;
									} 	
								}
							}
						}
					}
					
					/*$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) AND inventoryid IN (".$ClientInventoryStr.")";
					$SubscriptionESQL	= array("subscriptiondate"=>$CheckEndDate,"customerid"=>$CustomerID);
					*/ /*Commented BY VK to only include daily inventory*/

					$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) AND inventoryid IN (".$ClientInventoryStr.") AND frequency=:frequency";
					$SubscriptionESQL	= array("subscriptiondate"=>$CheckEndDate,"customerid"=>$CustomerID,"frequency"=>1);
					
					$SubscriptionQuery		= pdo_query($SubscriptionSQL,$SubscriptionESQL);
					$SubscriptionNum		= pdo_num_rows($SubscriptionQuery);
					$InventoryStartDateArr  = array();

					$TempIndex = 0;

					/*$CurrentInventoryFreqArr 	= GetInventoryFrequency();*/

					$SubsHolidayArr = array();
					if($SubscriptionNum > 0)
					{
						$TempFullMonthInventoryArr = array();
						$TempStartDate		= $CheckStartDate;
						$TempEndDate		= $CheckEndDate;
						
						while($SRow	= pdo_fetch_assoc($SubscriptionQuery))
						{
							$DaysArr	 = array();

							$InventoryID = $SRow['inventoryid'];
							$Quantity	 = $SRow['quantity'];
							
							if($Quantity < 1)
							{
								$Quantity = 1;
							}
							$StartDate	 = $SRow['subscriptiondate'];
							
							if($CurrentInventoryFreqArr[$InventoryID]  == '1')
							{
								$DaysArr	 = explode("::",$SRow['days']);
							}

							@array_filter($DaysArr);
							@array_unique($DaysArr);
							
							$SubsHolidayArr = $CustomerHolidayDeductionArr[$InventoryID];
							@array_filter($SubsHolidayArr);
							@array_unique($SubsHolidayArr);
							
							$TotalHolidays	= @count($SubsHolidayArr);

							$StartDate	 = strtotime(date("m/d/Y",$StartDate));

							$UsePartialBilling = 0;
							if($StartDate > 0)
							{
								if($StartDate >= $CheckStartDate && $StartDate <= $CheckEndDate)
								{
									$UsePartialBilling = 1;
								}
							}
							
							if($StartDate < $CheckEndDate AND $TotalHolidays < $LastDayofMonth)
							{
								$SubscriptionArr[$TempIndex][$InventoryID]['inventoryid']	= $InventoryID;
								$SubscriptionArr[$TempIndex][$InventoryID]['quantity']		= $Quantity;
								$SubscriptionArr[$TempIndex][$InventoryID]['inventoryid']	= $InventoryID;
								if($StartDate <= $CheckStartDate)
								{
									$InventoryStartDateArr[$InventoryID] = $CheckStartDate;
									$SubscriptionArr[$TempIndex][$InventoryID]['startdate']		= $CheckStartDate;
								}
								else
								{
									$InventoryStartDateArr[$InventoryID] = $StartDate;
									$SubscriptionArr[$TempIndex][$InventoryID]['startdate']		= $StartDate;
								}
								$SubscriptionArr[$TempIndex][$InventoryID]['enddate']		= $CheckEndDate;
								$SubscriptionArr[$TempIndex][$InventoryID]['partialbilling']= $UsePartialBilling;
								$SubscriptionArr[$TempIndex][$InventoryID]['frequency']= $CurrentInventoryFreqArr[$InventoryID];

								$SubscriptionArr[$TempIndex][$InventoryID]['billabledays']= $DaysArr;
							
								if($StartDate <= $CheckStartDate)
								{
									$TempFullMonthInventoryArr[] = $InventoryID;
								}
							}
							else
							{
								$TempFullMonthInventoryArr[] = $InventoryID;
							}
						$TempIndex++;
						}
					}

					$TempInventoryIDStr = '';
					if(!empty($TempFullMonthInventoryArr))
					{
						$TempInventoryIDsr = " AND inventoryid NOT IN (".implode(",",$TempFullMonthInventoryArr).")";
					}

					/*$SubscriptionLogSQL	= "SELECT * FROM ".$Prefix."subscriptions_log WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) ORDER BY inventoryid,subscriptiondate ASC,unsubscribedate ASC";				
					$SubscriptionLogESQL	= array("customerid"=>$CustomerID,"subscriptiondate"=>(int)$CheckEndDate);*/
					 /*Commented BY VK to only include daily inventory*/

					$SubscriptionLogSQL	= "SELECT * FROM ".$Prefix."subscriptions_log WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) AND frequency=:frequency ORDER BY inventoryid,subscriptiondate ASC,unsubscribedate ASC";				
					$SubscriptionLogESQL	= array("customerid"=>$CustomerID,"subscriptiondate"=>(int)$CheckEndDate,"frequency"=>1);

					$SubscriptionLogQuery	= pdo_query($SubscriptionLogSQL,$SubscriptionLogESQL);
					$SubscriptionLogNum		= pdo_num_rows($SubscriptionLogQuery);

					$SubscriptionLogArr 	= array();

					if($SubscriptionLogNum > 0)
					{
						$InventoryStatusArr	= array();
						$InventoryDaysArr	= array();
						$InventoryFreqArr	= array();

						while($SRow	= pdo_fetch_assoc($SubscriptionLogQuery))
						{
							$InventoryID 	= $SRow['inventoryid'];
							$Quantity 		= $SRow['quantity'];
							$SubscriptionDate= $SRow['subscriptiondate'];
							$UnsubDate   	= $SRow['unsubscribedate'];
							$Status			= $SRow['status'];
							$Frequency		= $SRow['frequency'];
							$CreatedOn		= strtotime(date("m/d/Y",$CreatedOn));
							
							if($UnsubDate < $CheckStartDate && $UnsubDate > 0)
							{
								continue;
							}
							if($Quantity < 1)
							{
								$Quantity = 1;
							}

							if($Frequency  == '1')
							{
								$DaysArr	 = explode("::",$SRow['days']);
							}	
							$SubscriptionArr[$TempIndex][$InventoryID]['billabledays']= $DaysArr;
							$SubscriptionArr[$TempIndex][$InventoryID]['quantity']	= $Quantity;
							$SubscriptionArr[$TempIndex][$InventoryID]['frequency']= $Frequency;
						
							$SubscriptionArr[$TempIndex][$InventoryID]['islogentry'] = 1;
							$SubscriptionArr[$TempIndex][$InventoryID]['partialbilling']= 1;
							if($SubscriptionDate <= $CheckStartDate)
							{
								$SubscriptionArr[$TempIndex][$InventoryID]['startdate']		= (int)$CheckStartDate;
							}
							else
							{
								$SubscriptionArr[$TempIndex][$InventoryID]['startdate']		= (int)$SubscriptionDate;
							}
								
							if($UnsubDate > 0)
							{
								if($UnsubDate > $CheckEndDate)
								{
									$UnsubDate = $CheckEndDate;
								}
								$SubscriptionArr[$TempIndex][$InventoryID]['enddate'] =(int)$UnsubDate;	
							}

							$CheckSubslogDateArr[$InvetoryID] = $SubscriptionDate;	
							$TempIndex++;
						}
					}
					if(!empty($SubscriptionArr))
					{
						$GrandTotal	= 0;
						$InvoiceDetailArr = array();
						$CheckStartDateArr = array();
						foreach($SubscriptionArr as $key => $TempValue)
						{
							foreach($TempValue as $InventoryID => $value2)
							{
								$SubsHolidayArr = $CustomerHolidayDeductionArr[$InventoryID];

								$PartialBilling		= $value2['partialbilling'];
								$StartDate			= (int)$value2['startdate'];
								$OrgStartDate		= (int)$value2['startdate'];
								$EndDate			= (int)$value2['enddate'];
								$IsLogEntry			= (int)$value2['islogentry'];
								$BillableDaysArr    = $value2['billabledays'];
								$SubsFrequency		= $value2['frequency'];
								$Quantity			= $value2['quantity'];

								if(in_array($OrgStartDate,$CheckStartDateArr[$InventoryID]))
								{
									//continue;	 /*comment by vk for checking duplicate log records*/
								}
								if($CheckStartDate > $StartDate)
								{
									$StartDate = $CheckStartDate;
								}
								if($EndDate < 1)
								{
									$EndDate 		= $CheckEndDate;

									if($StartDate == $InventoryStartDateArr[$InventoryID] && $IsLogEntry > 0)
									{
										continue;
									}
								}	

								if($SubsFrequency == 1)
								{
									$TempCheckStartDate = $StartDate;
									$TempCheckEndDate 	= $EndDate;
									if(!empty($BillableDaysArr))
									{
										while($TempCheckStartDate <= $TempCheckEndDate)
										{
											$BillableCheckDay	= date("N",$TempCheckStartDate);
											
											if(!in_array($BillableCheckDay,$BillableDaysArr))
											{
												$SubsHolidayArr[] = $TempCheckStartDate;	
											}

											$TempCheckStartDate = $TempCheckStartDate + 86400;
										}
									}
								}
								@array_filter($SubsHolidayArr);
								@array_unique($SubsHolidayArr);
								$TotalHolidays	= @count($SubsHolidayArr);

								$PricingType		= $ClientInventoryPriceArr[$InventoryID]['pricingtype'];

								if($PartialBilling > 0 || $TotalHolidays > 0)
								{
									if($PricingType > 0)
									{
										$StartDay		= date("d",$StartDate);
										$EndDay			= date("d",$EndDate);
										$DatePricingArr =  $ClientInventoryPriceArr[$InventoryID]['dailyprice'];

										$TotalCost	= 0;
										$NoDays		= 0;

										foreach($DatePricingArr as $Day => $Price)
										{
											$CheckDay	= $Day;
											if($CheckDay < 10)
											{
												$CheckDay	= "0".$CheckDay;
											}
											$CheckDate = strtotime($DateMonth."/".$CheckDay."/".$Year);
											
											if(in_array($CheckDate,$SubsHolidayArr))
											{
												continue;
											}

											if($Day >= $StartDay AND $Day <=$EndDay)
											{
												$TotalCost	+=$Price;
												
												$NoDays	+= 1;
											}
										}
									}
									else
									{
										$TempPrice	= $ClientInventoryPriceArr[$InventoryID]['price'];
										$TempDays	= $ClientInventoryPriceArr[$InventoryID]['days'];
										
										$UnitPrice	= $TempPrice / $TempDays;
										$TotalDays	= floor(($CheckEndDate - $StartDate) / 86400);
										$UnitDays	= $TotalDays / $LastDayofMonth;
										
										$Holidays	= floor($UnitDays * $TotalHolidays);

										$NoDays		= (floor($UnitDays * $LastDayofMonth) - $Holidays) ;
										$TotalCost  = round(($NoDays * $UnitPrice),2);
									}
								}
								else
								{
									$TotalCost			= $ClientInventoryPriceArr[$InventoryID]['price'];
									$NoDays				= $ClientInventoryPriceArr[$InventoryID]['days'];
								}
								$InventoryName		= $ClientInventoryPriceArr[$InventoryID]['inventoryname'];

								$InventoryCatID		= $ClientInventoryPriceArr[$InventoryID]['categoryid'];
								$InventoryCatName	= $ClientInventoryPriceArr[$InventoryID]['categoryname'];

								if($TotalCost > 0)
								{
									$TotalPrice		=(float)$TotalCost * (int)$Quantity;

									$GrandTotal	= $GrandTotal + $TotalPrice;
									$InvoiceDetESQL = array(
										"clientid"		=>(int)$ClientID,
										"customerid"	=>(int)$CustomerID,
										"qty"			=>(int)$Quantity,
										"frequency"		=>(int)$SubsFrequency,
										"noofdays"		=>(int)$NoDays,
										"price"			=>(float)$TotalCost,
										"inventoryname"	=>$InventoryName,
										"inventoryid"	=>(int)$InventoryID,
										"inventorycatname"=>$InventoryCatName,
										"inventorycatid"=>(int)$InventoryCatID,
										"totalprice"	=>(float)$TotalPrice,
										"item_start_date"=>(int)$StartDate,
										"item_end_date"	=> (int)$EndDate
									);
									$InvoiceDetailArr[] = $InvoiceDetESQL;
								}
							}
						}
						
						$SalesSQL	= "SELECT * FROM ".$Prefix."sale WHERE customerid=:customerid AND (saledate BETWEEN :startdate AND :enddate ) AND deletedon < :deletedon ORDER BY saledate ASC";
						$SalesESQL	= array("startdate"=>$CheckStartDate,"enddate"=>$CheckEndDate,"customerid"=>$CustomerID,"deletedon"=>1);
						$SalesQuery	= pdo_query($SalesSQL,$SalesESQL);
						$SalesNum	= pdo_num_rows($SalesQuery);
						
						if($SalesNum > 0)
						{
							while($SalesRow = pdo_fetch_assoc($SalesQuery))
							{
								$InventoryID	= $SalesRow["inventoryid"];
								$SaleDate		= $SalesRow["saledate"];
								$Quantity		= $SalesRow["noofpices"];
								$TotalCost		= $SalesRow["salerate"];

								$StartDate		= strtotime(date("m/d/Y",$SaleDate));
								$EndDate		= $StartDate + 86399;

								$InventoryName		= $InventoryNameArr[$InventoryID]['name'];
								$InventoryCatID		= $InventoryNameArr[$InventoryID]['categoryid'];

								$InventoryCatName	= $InventoryCatNameArr[$InventoryCatID];
								$SubsFrequency		= $CurrentInventoryFreqArr[$InventoryID];
								
								$TotalPrice = (float)$TotalCost * (int)$Quantity;
								
								$NoDays	= 1;

								$GrandTotal	= $GrandTotal + $TotalPrice;
									
									$InvoiceDetESQL = array(
										"clientid"		=>(int)$ClientID,
										"customerid"	=>(int)$CustomerID,
										"qty"			=>(int)$Quantity,
										"frequency"		=>(int)$SubsFrequency,
										"noofdays"		=>(int)$NoDays,
										"price"			=>(float)$TotalCost,
										"inventoryname"	=>$InventoryName,
										"inventoryid"	=>(int)$InventoryID,
										"inventorycatname"=>$InventoryCatName,
										"inventorycatid"=>(int)$InventoryCatID,
										"totalprice"	=>(float)$TotalPrice,
										"item_start_date"=>(int)$StartDate,
										"item_end_date"	=> (int)$EndDate
									);
									$InvoiceDetailArr[] = $InvoiceDetESQL;
							}
						}

						if(!empty($InvoiceDetailArr))
						{
							$InvoiceTillDate	= strtotime($_POST['billdate']);
							$OldInvoiceID		= $InvoiceCustArr[$CustomerID]['id'];

							if($IsDiscount > 0)
							{
								$Discount =(float)($GrandTotal * ($DiscountPercent/100));
								$FinalAmountToPay =  (float)($GrandTotal - $Discount);
							}
							else
							{
								$Discount = 0;
								$FinalAmountToPay	= (float)$GrandTotal;
							}

							$CovenienceCharge =(float)($FinalAmountToPay * ($conveniencecharge/100));

							if($isservicecharge > 0)
							{
								if($servicechargetype > 0)
								{
									$totalservicecharge	= (float)($FinalAmountToPay * ($servicecharge/100));
								}
								else
								{
									$totalservicecharge	= (float)$servicecharge;
								}
							}
							else
							{
								$totalservicecharge	= 0;
							}

							$FinalAmountToPay	= $FinalAmountToPay + ceil($CovenienceCharge) + ceil($totalservicecharge);

							$InvoiceSQL		= "INSERT INTO ".$Prefix."invoices SET
											clientid			=:clientid,
											customerid			=:customerid,
											invoicedate			=:invoicedate,
											invoicemonth		=:invoicemonth,
											invoiceyear			=:invoiceyear,
											customername		=:customername,  
											customeraddress1	=:customeraddress1,
											customeraddress2	=:customeraddress2,
											customercity		=:customercity,
											customerstate		=:customerstate,
											customerpincode		=:customerpincode,
											customerphone		=:customerphone,
											customerstateid		=:customerstateid,
											customercityid		=:customercityid,
											invoiceid			=:invoiceid,
											totalamount			=:totalamount,
											finalamount			=:finalamount,
											discount			=:discount,
											conveniencecharge	=:conveniencecharge,
											servicecharge		=:servicecharge,
											previousbalance		=:previousbalance,
											securitycode		=:securitycode,
											createdon			=:createdon";
							
							//$PreviousBalance = getOutstandingBalanceByCustomer($ClientID, $CustomerID);	
							$PreviousBalance = $outstandingbalance;	

							if($OldInvoiceID > 0)
							{
								$InvoiceNumber  = $InvoiceCustArr[$CustomerID]['invoiceid'];
								$PreviousBalance -= $InvoiceCustArr[$CustomerID]['finalamount'];
							}

							/*$AlphaCode	= AlphaCode(2);*/

							$InvoiceESQL	= array(
										"clientid"			=>(int)$ClientID,
										"customerid"		=>(int)$CustomerID,
										"invoicedate"		=>(int)$InvoiceTillDate,
										"invoicemonth"		=>(int)$Month,
										"invoiceyear"		=>(int)$Year,
										"customername"		=> $CustomerName,
										"customeraddress1"	=>$CustomerAddress1,
										"customeraddress2"	=>$CustomerAddress2,
										"customercity"		=>$GetAllCity[$CustomerCityID],
										"customerstate"		=>$GetAllStates[$CustomerStateID],
										"customerpincode"	=>$CustomerPinCode,
										"customerphone"		=>$CustomerPhone,
										"customerstateid"	=>(int)$CustomerStateID,
										"customercityid"	=>(int)$CustomerCityID,
										"invoiceid"			=>(int)$InvoiceNumber,
										"totalamount"		=>(float)$GrandTotal,
										"finalamount"		=>(float)$FinalAmountToPay,
										"discount"			=>(float)$Discount,
										"conveniencecharge"=>(float)ceil($CovenienceCharge),
										"servicecharge"		=>(float)ceil($totalservicecharge),
										"previousbalance"	=>(float)$PreviousBalance,
										"securitycode"		=>$AlphaCode,
										"createdon"			=>(int)$CurrentTime
									);

							if($OldInvoiceID > 0)
							{
								$InvoiceSQL			= str_replace("INSERT INTO ","UPDATE ",$InvoiceSQL);
								$InvoiceSQL			.= " WHERE id=:id";

								$InvoiceESQL['id']	= $OldInvoiceID;
							}
							$InvoiceQuery	= pdo_query($InvoiceSQL,$InvoiceESQL);
							if($InvoiceQuery)
							{
								$NewInvoiceCustomerArr[] = $CustomerID;

								if($OldInvoiceID > 0)
								{
									$InvoiceID	= $OldInvoiceID;

									$DelSQL		= "DELETE FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
									$DelESQL	= array("invoiceid"=>(int)$InvoiceID);
									pdo_query($DelSQL,$DelESQL);
								}
								else
								{
									$InvoiceID	= pdo_insert_id();
									$InvoiceNumber++;
								}
								foreach($InvoiceDetailArr as $invoicekey => $invoicedetail)
								{
									$InvoiceDetSQL = "INSERT INTO ".$Prefix."invoice_details SET
									invoiceid		= :invoiceid, 
									clientid		= :clientid, 
									customerid		= :customerid, 
									inventoryid		= :inventoryid, 
									qty				= :qty, 
									frequency		= :frequency, 
									noofdays		= :noofdays, 
									price			= :price, 
									inventoryname	= :inventoryname, 
									inventorycatid	= :inventorycatid, 
									inventorycatname= :inventorycatname, 
									totalprice		= :totalprice,
									item_start_date = :item_start_date, 
									item_end_date 	= :item_end_date, 
									createdon		= :createdon
									";

									$InvoiceDetESQL 				= $invoicedetail;
									$InvoiceDetESQL['invoiceid']	= (int)$InvoiceID;
									$InvoiceDetESQL['createdon']	= (int)$CurrentTime;
									$DetailQuery = pdo_query($InvoiceDetSQL,$InvoiceDetESQL);
								}

								if($FinalAmountToPay > 0)
								{
									$CustomerArr['name'] = $CustomerName;
									$CustomerArr['customernumber'] = $CustomerNumber;
									if($IsLiveProcess > 0)
									{
										$CustomerArr['phone'] = $CustomerPhone;
										$CustomerArr['email'] = $CustomerEmail;
									}
									else
									{
										$CustomerArr['phone'] = $BetaPhone;
										$CustomerArr['email'] = $BetaEmail;
									}
									$Notes	= "Payment For Invoice#".$InvoiceNumber. " for Month Of ".$MonthArr[$Month]." ".$Year;
									if($IsLiveProcess < 1)
									{
										//$FinalAmountToPay = 2;
									}
									/*if($FinalAmountToPay > 0 && $IsPaymentGateWay > 0)
									{
										GeneratePaymentLinks($ClientID,$RAZOR_PAY_API_KEY_Client,$RAZOR_PAY_API_SECRET_Client,$CustomerArr,$Amount,$Notes,$InvoiceID);
									}*/
								}
								$InvoiceCreatedCounter++;
							}
							$IsOpeningBalance = 0;

							$Narration	= "Invoice for ".$MonthArr[$Month].", ".$Year;

							//GenerateCustomerAccountLog($ClientID,$CustomerID,$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$FinalAmountToPay,$InvoiceTillDate,$Narration,"invoice",'',$InvoiceID,$Month,$Year);	

							/*************** Added BY *************************/
							$outstandingbalance = $PreviousBalance + $FinalAmountToPay; 
														
							$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=:outstandingbalance WHERE id=:id";
							$UpdateEsql	= array("outstandingbalance"=>$outstandingbalance,"id"=>(int)$CustomerID);

							$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);
						}
						else
						{
							$OldInvoiceID 	= $InvoiceCustArr[$CustomerID]['id'];
							if($OldInvoiceID > 0)
							{
								$PreviousBalance = $outstandingbalance - $InvoiceCustArr[$CustomerID]['finalamount'];
							
								$DelSQL		= "DELETE FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
								$DelESQL	= array("invoiceid"=>(int)$OldInvoiceID);
								pdo_query($DelSQL,$DelESQL);

								$OldInvoiceInfoSql	= "SELECT * FROM ".$Prefix."invoices WHERE id=:id";
								$OldInvoiceInfoEsql	= array("id"=>(int)$OldInvoiceID);

								$OldInvoiceInfoQuery	= pdo_query($OldInvoiceInfoSql,$OldInvoiceInfoEsql);
								$OldInvoiceInfoNum		= pdo_num_rows($OldInvoiceInfoQuery);

								if($OldInvoiceInfoNum > 0)
								{
									$OldInvoiceInfoRow	= pdo_fetch_assoc($OldInvoiceInfoQuery);
									$tempcustomerid		= $OldInvoiceInfoRow['customerid'];
									$invoiceamount		= $OldInvoiceInfoRow['finalamount'];

									$DelSQL		= "DELETE FROM ".$Prefix."invoices WHERE id=:id";
									$DelESQL	= array("id"=>(int)$OldInvoiceID);
									$DelInvoice	= pdo_query($DelSQL,$DelESQL);

									if($DelInvoice)
									{
										$UpdateCustInfoSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=(outstandingbalance - :outstandingbalance) WHERE id=:id";
										$UpdateCustInfoEsql	= array("outstandingbalance"=>(float)$invoiceamount,"id"=>(int)$tempcustomerid);

										$UpdateCustInfoQuery = pdo_query($UpdateCustInfoSql,$UpdateCustInfoEsql);
									}
								}

								$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=:outstandingbalance WHERE id=:id";
								$UpdateEsql	= array("outstandingbalance"=>$PreviousBalance,"id"=>(int)$CustomerID);

								$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

								/*$SelSql		= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND invoiceid=:invoiceid";
								$SelEsql	= array("invoiceid"=>(int)$OldInvoiceID,'clientid'=>(int)$ClientID);
								$SelQuery	= pdo_query($SelSql,$SelEsql);
								$SelNum		= pdo_num_rows($SelQuery);
								if($SelNum > 0)
								{
									$SelRow			= pdo_fetch_assoc($SelQuery);

									$CustRecordID	= $SelRow['customerid'];
									
									$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid=:invoiceid AND clientid=:clientid AND customerid=:customerid";
									$DelESQL = array("invoiceid"=>(int)$OldInvoiceID,'clientid'=>(int)$ClientID,'customerid'=>(int)$CustRecordID);
									pdo_query($DelSQL,$DelESQL);

									//GetCustomerLineTotalUpdated($CustRecordID);
								}*/
							}
						}
					}
					else
					{
						$OldInvoiceID 	= $InvoiceCustArr[$CustomerID]['id'];
						if($OldInvoiceID > 0)
						{
							$DelSQL		= "DELETE FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
							$DelESQL	= array("invoiceid"=>(int)$OldInvoiceID);
							pdo_query($DelSQL,$DelESQL);

							$OldInvoiceInfoSql	= "SELECT * FROM ".$Prefix."invoices WHERE id=:id";
							$OldInvoiceInfoEsql	= array("id"=>(int)$OldInvoiceID);

							$OldInvoiceInfoQuery	= pdo_query($OldInvoiceInfoSql,$OldInvoiceInfoEsql);
							$OldInvoiceInfoNum		= pdo_num_rows($OldInvoiceInfoQuery);

							if($OldInvoiceInfoNum > 0)
							{
								$OldInvoiceInfoRow	= pdo_fetch_assoc($OldInvoiceInfoQuery);
								$tempcustomerid		= $OldInvoiceInfoRow['customerid'];
								$invoiceamount		= $OldInvoiceInfoRow['finalamount'];

								$DelSQL		= "DELETE FROM ".$Prefix."invoices WHERE id=:id";
								$DelESQL	= array("id"=>(int)$OldInvoiceID);
								$DelInvoice	= pdo_query($DelSQL,$DelESQL);

								if($DelInvoice)
								{
									$UpdateCustInfoSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=(outstandingbalance - :outstandingbalance) WHERE id=:id";
									$UpdateCustInfoEsql	= array("outstandingbalance"=>(float)$invoiceamount,"id"=>(int)$tempcustomerid);

									$UpdateCustInfoQuery = pdo_query($UpdateCustInfoSql,$UpdateCustInfoEsql);
								}
							}

							
							/*$SelSql		= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND invoiceid=:invoiceid";
							$SelEsql	= array("invoiceid"=>(int)$OldInvoiceID,'clientid'=>(int)$ClientID);
							$SelQuery	= pdo_query($SelSql,$SelEsql);
							$SelNum		= pdo_num_rows($SelQuery);
							if($SelNum > 0)
							{
								$SelRow			= pdo_fetch_assoc($SelQuery);

								$CustRecordID	= $SelRow['customerid'];

								$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid=:invoiceid AND clientid=:clientid AND customerid=:customerid";
								$DelESQL = array("invoiceid"=>(int)$OldInvoiceID,'clientid'=>(int)$ClientID,'customerid'=>(int)$CustRecordID);
								pdo_query($DelSQL,$DelESQL);

								//GetCustomerLineTotalUpdated($CustRecordID);
							}*/
						}	
					}
					$CustomerCounter++;
				}
				if($CustNum == $CustomerCounter)
				{
					$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET status=:status,isprocessing=:isprocessing,totalinvoicegenerated=:totalinvoicegenerated,totalcustomers=:totalcustomers WHERE id=:id";
					$UpdateESQL	= array("status"=>1,"isprocessing"=>0,"id"=>(int)$RequestID,"totalinvoicegenerated"=>(int)$InvoiceCreatedCounter,"totalcustomers"=>(int)$TotalCustomers);
					pdo_query($UpdateSQL,$UpdateESQL);
				}
				if(!empty($CustomersArr))
				{
					foreach($CustomersArr as $key => $value)
					{
						if(!in_array($value,$NewInvoiceCustomerArr))
						{
							$OldInvoiceID 	= $InvoiceCustArr[$value]['id'];
							if($OldInvoiceID > 0)
							{
								$DelSQL		= "DELETE FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
								$DelESQL	= array("invoiceid"=>(int)$OldInvoiceID);
								pdo_query($DelSQL,$DelESQL);

								$OldInvoiceInfoSql	= "SELECT * FROM ".$Prefix."invoices WHERE id=:id";
								$OldInvoiceInfoEsql	= array("id"=>(int)$OldInvoiceID);

								$OldInvoiceInfoQuery	= pdo_query($OldInvoiceInfoSql,$OldInvoiceInfoEsql);
								$OldInvoiceInfoNum		= pdo_num_rows($OldInvoiceInfoQuery);

								if($OldInvoiceInfoNum > 0)
								{
									$OldInvoiceInfoRow	= pdo_fetch_assoc($OldInvoiceInfoQuery);
									$tempcustomerid		= $OldInvoiceInfoRow['customerid'];
									$invoiceamount		= $OldInvoiceInfoRow['finalamount'];

									$DelSQL		= "DELETE FROM ".$Prefix."invoices WHERE id=:id";
									$DelESQL	= array("id"=>(int)$OldInvoiceID);
									$DelInvoice	= pdo_query($DelSQL,$DelESQL);

									if($DelInvoice)
									{
										$UpdateCustInfoSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=(outstandingbalance - :outstandingbalance) WHERE id=:id";
										$UpdateCustInfoEsql	= array("outstandingbalance"=>(float)$invoiceamount,"id"=>(int)$tempcustomerid);

										$UpdateCustInfoQuery = pdo_query($UpdateCustInfoSql,$UpdateCustInfoEsql);
									}
								}

								/*$SelSql		= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND invoiceid=:invoiceid";
								$SelEsql	= array("invoiceid"=>(int)$OldInvoiceID,'clientid'=>(int)$ClientID);
								$SelQuery	= pdo_query($SelSql,$SelEsql);
								$SelNum		= pdo_num_rows($SelQuery);
								if($SelNum > 0)
								{
									$SelRow			= pdo_fetch_assoc($SelQuery);

									$CustRecordID	= $SelRow['customerid'];

									$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid=:invoiceid AND clientid=:clientid AND customerid=:customerid";
									$DelESQL = array("invoiceid"=>(int)$OldInvoiceID,'clientid'=>(int)$ClientID,'customerid'=>(int)$CustRecordID);
									pdo_query($DelSQL,$DelESQL);

									//GetCustomerLineTotalUpdated($CustRecordID);
								}*/
							}
						}
					}		
				}
			}
			else
			{
				$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET status=:status,isprocessing=:isprocessing WHERE id=:id";
				$UpdateESQL	= array("status"=>1,"isprocessing"=>0,"id"=>(int)$RequestID);
				pdo_query($UpdateSQL,$UpdateESQL);
			}

			//updateCustomerOutstandingBalance($ClientID, 0);

			if($_POST['cansendsms'] > 0)
			{
				$invoicemonthyear	= $Year."-".$Month."-01";

				$CampaignStatusArr	= ScheduleInvoiceSMSCampaign($ClientID,$invoicemonthyear);

				$CampaignStatus		= $CampaignStatusArr['success'];
				$CampaignMsg		= $CampaignStatusArr['msg'];
			}
		}

		if(trim($CampaignMsg) != "")
		{
			$SuccessMessage = (int)$InvoiceCreatedCounter." Invoices created successfully and ".$CampaignMsg;
		}
		else
		{
			$SuccessMessage = (int)$InvoiceCreatedCounter." Invoices created successfully";
		}

		$response['success']		= true;
		$response['totalcustomer']	= (int)$TotalCustomers;
		$response['totalinvoices']	= (int)$InvoiceCreatedCounter;
		$response['msg']			= $SuccessMessage;
	}
	else
	{
		$response['success']	= true;
		$response['msg']		= "No new pending request found.";
	}
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'CreateInvoices_new')
{
    $response['success']	= false;
    $response['msg']		= "Unable to create invoices.";

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($_POST['clientid']);

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND status <:status AND isprocessing <:isprocessing ORDER BY id ASC";
	$CheckESQL = array("status"=>1,'clientid'=>(int)$_POST['clientid'],"isprocessing"=>1);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($totalsmscreditsavaiable < 1000 && $_POST['cansendsms'] > 0)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	if(($totalsmscreditsavaiable < 1000) && $_POST['cansendsms'] > 0 && $CheckNum > 0)
	{
		$haserror = true;
		$response['msg']	= "You don't have sufficient credit to send sms.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$isqueueupdated	= false;

	if($CheckNum > 0)
	{
		while($CheckRow = pdo_fetch_assoc($CheckQuery))
		{
			$RequestID		= $CheckRow['id'];
			$ClientID		= $CheckRow['clientid'];
			$Month			= $CheckRow['month'];
			$Year			= $CheckRow['year'];
			$isprocessing	= $CheckRow['isprocessing'];

			$CheckSQL2		= "SELECT COUNT(*) as C FROM ".$Prefix."invoice_request_queue WHERE id=:id AND year=:year AND month=:month AND isprocessing < :isprocessing AND status <:status";
			$CheckESQL2		= array("isprocessing"=>1,'id'=>(int)$RequestID,"year"=>$Year,"month"=>$Month,"status"=>1);
			
			$CheckQuery2	= pdo_query($CheckSQL2,$CheckESQL2);
			$RowCheck		= pdo_fetch_assoc($CheckQuery2);

			$CheckCount		= $RowCheck["C"];

			if($CheckCount > 0)
			{
				$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET billdate=:billdate,cansendsms=:cansendsms WHERE id=:id";
				$UpdateESQL	= array("billdate"=>strtotime($_POST['billdate']),"cansendsms"=>(int)$_POST['cansendsms'],"id"=>(int)$RequestID);

				$UpdateQuery	= pdo_query($UpdateSQL,$UpdateESQL);

				if($UpdateQuery)
				{
					$CheckInvoiceSQL	= "SELECT * FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid AND deletedon <:deletedon GROUP BY customerid";
					$CheckInvoiceESQL	= array("invoicemonth"=>(int)$Month,"invoiceyear"=>(int)$Year,"clientid"=>(int)$ClientID,'deletedon'=>1);
					$CheckInvoiceQuery	= pdo_query($CheckInvoiceSQL,$CheckInvoiceESQL);
					$CheckInvoiceNum	= pdo_num_rows($CheckInvoiceQuery);
					if($CheckInvoiceNum > 0)
					{
						while($CheckInvoiceRow = pdo_fetch_assoc($CheckInvoiceQuery))
						{
							$InvoiceID	= $CheckInvoiceRow['id'];

							$DelSQL		= "DELETE FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
							$DelESQL	= array("invoiceid"=>(int)$InvoiceID);
							pdo_query($DelSQL,$DelESQL);

							$OldInvoiceInfoSql	= "SELECT * FROM ".$Prefix."invoices WHERE id=:id";
							$OldInvoiceInfoEsql	= array("id"=>(int)$InvoiceID);

							$OldInvoiceInfoQuery	= pdo_query($OldInvoiceInfoSql,$OldInvoiceInfoEsql);
							$OldInvoiceInfoNum		= pdo_num_rows($OldInvoiceInfoQuery);

							if($OldInvoiceInfoNum > 0)
							{
								$OldInvoiceInfoRow	= pdo_fetch_assoc($OldInvoiceInfoQuery);
								$tempcustomerid		= $OldInvoiceInfoRow['customerid'];
								$invoiceamount		= $OldInvoiceInfoRow['finalamount'];

								$DelSQL		= "DELETE FROM ".$Prefix."invoices WHERE id=:id";
								$DelESQL	= array("id"=>(int)$InvoiceID);
								$DelInvoice	= pdo_query($DelSQL,$DelESQL);

								if($DelInvoice)
								{
									$UpdateCustInfoSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=(outstandingbalance - :outstandingbalance) WHERE id=:id";
									$UpdateCustInfoEsql	= array("outstandingbalance"=>(float)$invoiceamount,"id"=>(int)$tempcustomerid);

									$UpdateCustInfoQuery = pdo_query($UpdateCustInfoSql,$UpdateCustInfoEsql);
								}
							}

							/*$SelSql		= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND invoiceid=:invoiceid";
							$SelEsql	= array("invoiceid"=>(int)$InvoiceID,'clientid'=>(int)$ClientID);
							
							$SelQuery	= pdo_query($SelSql,$SelEsql);
							$SelNum		= pdo_num_rows($SelQuery);
							
							if($SelNum > 0)
							{
								$SelRow			= pdo_fetch_assoc($SelQuery);

								$CustRecordID	= $SelRow['customerid'];
								
								$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid=:invoiceid AND clientid=:clientid AND customerid=:customerid";
								$DelESQL = array("invoiceid"=>(int)$InvoiceID,'clientid'=>(int)$ClientID,'customerid'=>(int)$CustRecordID);
								
								pdo_query($DelSQL,$DelESQL);

								GetCustomerLineTotalUpdated($CustRecordID);
							}
							*/
						}
					}

					$isqueueupdated	= true;
				}
			}
		}

		if($isqueueupdated)
		{
			$response['success']	= true;
			$response['msg']		= "Invoice generation request successfully added in queue.";

		}
	}
	else
	{
		$response['success']	= true;
		$response['msg']		= "No new pending request found.";
	}
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GenerateInvoiceRequest")
{
	$Response['pricingerror']	= false;
	$Response['success']		= false;
	$Response['canswitchrun']	= false;

    $Response['msg']			= "Oops something went wrong. Please try again.";

	if(strlen($_POST['month']) < 1)
	{
		$_POST['month'] = "0".$_POST['month'];
	}

	$GetClientActiveInventory = GetClientActiveInventory($_POST['clientid']);

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

	$SubscriptionSql	= "SELECT sub.* FROM ".$Prefix."subscriptions sub,".$Prefix."customers cust WHERE sub.customerid=cust.id AND cust.clientid=:clientid AND cust.status=:status AND sub.frequency=:frequency AND deletedon < :deletedon ".$custcondition." GROUP BY sub.inventoryid ORDER BY cust.sequence ASC, cust.customerid ASC";

	$SubscriptionEsql	= array("clientid"=>(int)$_POST['clientid'],"status"=>1,'deletedon'=>1,"frequency"=>1);

	$SubscriptionQuery	= pdo_query($SubscriptionSql,$SubscriptionEsql);
	$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);

	$ActiveInvIDArr		= array();
	$ActiveInvNameArr	= array();
	$InventoryPriceCheckError = '';
	if($SubscriptionNum > 0)
	{
		$InventoryNameArr	= GetInventoryNames();

		while($SubRows = pdo_fetch_assoc($SubscriptionQuery))
		{
			$inventoryid	= (int)$SubRows['inventoryid'];
			$inventoryname	= $InventoryNameArr[$inventoryid]['name'];
			$frequency		= $InventoryNameArr[$inventoryid]['frequency'];
			if(in_array($inventoryid,$GetClientActiveInventory) && $frequency == '1')	
			{
				$PriceCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
				year		=:year
				AND
				month		=:month
				AND
				days		>:days
				AND
				clientid	=:clientid
				AND inventoryid=:inventoryid";
			
				$PriceCheckEsql	= array(
					"year"			=>(int)$_POST['year'],
					"month"			=>(int)$_POST['month'],
					"days"			=>0,
					"inventoryid"	=>(int)$inventoryid,
					"clientid"		=>(int)$_POST['clientid']
				);
			
				$PriceCheckQuery	= pdo_query($PriceCheckSql,$PriceCheckEsql);
				$PriceCheckNum		= pdo_num_rows($PriceCheckQuery);
				if($PriceCheckNum < 1)
				{
					$ActiveInvNameArr[]	= $inventoryname;
				}
			}
		}
	}

	if(!empty($ActiveInvNameArr))
	{
		$ActiveInvNameStr	= @implode(", ",@array_filter(@array_unique($ActiveInvNameArr)));
	}

	if($ActiveInvNameStr !='')
	{
		$Response['success']		= false;
		$Response['pricingerror']	= true;
			
		$Response['msg']			= "Please update pricing for ".$ActiveInvNameStr." for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

		$json = json_encode($Response);
		echo $json;
		die;
	}
	if($_POST['bypass'] < 1)
	{
		$condition	= "";

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids	= $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}

			$condition	.= " AND areaid IN(".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$condition	.= " AND lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT COUNT(*) c FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status ".$condition." ORDER BY id ASC";
		$CustESQL	= array("deletedon"=>1,'status'=>1,'clientid'=>(int)$_POST['clientid']);
		$CustQuery  = pdo_query($CustSQL,$CustESQL);
		$CustRow	= pdo_fetch_assoc($CustQuery);
		$TotalCustomers = $CustRow['c'];

		$CustSQL	= "SELECT COUNT(*) c FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status AND (openingbalance =:openingbalance || openingbalance IS NULL) ".$condition." ORDER BY id ASC";
		$CustESQL			= array("deletedon"=>1,'status'=>1,'clientid'=>(int)$_POST['clientid'],'openingbalance'=>'');
		$CustQuery  		= pdo_query($CustSQL,$CustESQL);
		$CustRow			= pdo_fetch_assoc($CustQuery);
		$NoOpeningBalance 	= $CustRow['c'];

		if($NoOpeningBalance > 0)
		{
			$response['success']				= false;
			$response['openingbalanceerror']	= true;
			$response['msg']	= (int)$NoOpeningBalance." customer invoices will not be generated due to no entry for opening balance out of ".(int)$TotalCustomers." customers.";
			$json = json_encode($response);
			echo $json;
			die;
		}
	}

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year";
	$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year']);
	
	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$RowCheck	=	pdo_fetch_assoc($CheckQuery);

		$Status		=	$RowCheck['status'];

		if($Status < 1)
		{
			$Response['success']		= false;
			$Response['msg']			= "Bill generation for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." is already in queue to be processed.";
			$Response['toastmsg']		= "Bill generation for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." is already in queue to be processed.";
		}
		else
		{
			$Response['success']		= false;
			$Response['msg']			= "Bill(s) already generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
			$Response['toastmsg']		= "Bill(s) already generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
		}
	}
	else
	{
		$Sql	= "INSERT INTO ".$Prefix."invoice_request_queue SET 
		clientid	=:clientid,
		month		=:month,
		year		=:year,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"month"			=>(int)$_POST['month'],
			"year"			=>(int)$_POST["year"],
			"createdon"		=>time()
		);
		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$Response['success']		= true;
			$Response['canswitchrun']	= true;
			$Response['msg']		= "Bill(s) generation request for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." has been added to queue.";
			$Response['toastmsg']	= "Bill(s) generation request for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." has been added to queue.";
		}	
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ReGenerateInvoiceRequest")
{
	$Response['success']		= false;
    $Response['pricingerror']	= false;
	$Response['canswitchrun']	= false;

    $Response['msg']			= "Oops something went wrong. Please try again.";
	
	if(strlen($_POST['month']) < 1)
	{
		$_POST['month'] = "0".$_POST['month'];
	}

	$CheckMonth = $_POST['month'];
	$CheckYear 	= $_POST['year'];

	if($_POST['bypass'] < 1)
	{
		$condition	= "";

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids	= $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}

			$condition	.= " AND areaid IN(".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$condition	.= " AND lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT COUNT(*) c FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status ".$condition." ORDER BY id ASC";
		$CustESQL	= array("deletedon"=>1,'status'=>1,'clientid'=>(int)$_POST['clientid']);
		$CustQuery  = pdo_query($CustSQL,$CustESQL);
		$CustRow	= pdo_fetch_assoc($CustQuery);
		$TotalCustomers = $CustRow['c'];

		$CustSQL	= "SELECT COUNT(*) c FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status AND (openingbalance =:openingbalance || openingbalance IS NULL) ".$condition." ORDER BY id ASC";
		$CustESQL			= array("deletedon"=>1,'status'=>1,'clientid'=>(int)$_POST['clientid'],'openingbalance'=>'');
		$CustQuery  		= pdo_query($CustSQL,$CustESQL);
		$CustRow			= pdo_fetch_assoc($CustQuery);
		$NoOpeningBalance 	= $CustRow['c'];

		if($NoOpeningBalance > 0 )
		{
			$response['success']				= false;
			$response['openingbalanceerror']	= true;
			$response['msg']	= (int)$NoOpeningBalance." customer invoices will not be generated due to no entry for opening balance out of ".(int)$TotalCustomers." customers.";
			$json = json_encode($response);
			echo $json;
			die;
		}
	}
	/*$SubscriptionSql	= "SELECT sub.* FROM ".$Prefix."subscriptions sub,".$Prefix."client_inventory_linker inv WHERE sub.inventoryid=inv.inventoryid AND inv.clientid=:clientid AND inv.status=:status GROUP BY sub.inventoryid";*/
	$GetClientActiveInventory = GetClientActiveInventory($_POST['clientid']);

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

	$SubscriptionSql	= "SELECT sub.* FROM ".$Prefix."subscriptions sub,".$Prefix."customers cust WHERE sub.customerid=cust.id AND cust.clientid=:clientid AND cust.status=:status AND deletedon < :deletedon ".$custcondition." GROUP BY sub.inventoryid ORDER BY cust.sequence ASC, cust.customerid ASC";

	$SubscriptionEsql	= array("clientid"=>(int)$_POST['clientid'],"status"=>1,'deletedon'=>1);

	$SubscriptionQuery	= pdo_query($SubscriptionSql,$SubscriptionEsql);
	$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);

	$ActiveInvIDArr		= array();
	$ActiveInvNameArr	= array();
	$InventoryPriceCheckError = '';
	if($SubscriptionNum > 0)
	{
		$InventoryNameArr	= GetInventoryNames();

		while($SubRows = pdo_fetch_assoc($SubscriptionQuery))
		{
			$inventoryid	= (int)$SubRows['inventoryid'];
			$inventoryname	= $InventoryNameArr[$inventoryid]['name'];
			$frequency		= $InventoryNameArr[$inventoryid]['frequency'];
			if(in_array($inventoryid,$GetClientActiveInventory) && $frequency == '1')	
			{
				$PriceCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
				year		=:year
				AND
				month		=:month
				AND
				days		>:days
				AND
				clientid	=:clientid
				AND inventoryid=:inventoryid";
			
				$PriceCheckEsql	= array(
					"year"			=>(int)$_POST['year'],
					"month"			=>(int)$_POST['month'],
					"days"			=>0,
					"inventoryid"	=>(int)$inventoryid,
					"clientid"		=>(int)$_POST['clientid']
				);
			
				$PriceCheckQuery	= pdo_query($PriceCheckSql,$PriceCheckEsql);
				$PriceCheckNum		= pdo_num_rows($PriceCheckQuery);
				if($PriceCheckNum < 1)
				{
					$ActiveInvNameArr[]	= $inventoryname;
				}
			}
		}
	}

	if(!empty($ActiveInvNameArr))
	{
		$ActiveInvNameStr	= @implode(", ",@array_filter(@array_unique($ActiveInvNameArr)));
	}

	if($ActiveInvNameStr !='')
	{
		$Response['success']		= false;
		$Response['pricingerror']	= true;

		$Response['msg']			= "Please update pricing for ".$ActiveInvNameStr." for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

		$json = json_encode($Response);
		echo $json;
		die;
	}
	if($CheckMonth == 12)
	{
		$CheckMonth = 1;
		$CheckYear  += 1;
	}
	else
	{
		$CheckMonth += 1;
	}

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear and deletedon < :deletedon";
	$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"invoicemonth"=>(int)$CheckMonth,"invoiceyear"=>(int)$CheckYear,'deletedon'=>1);

	$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);
	
	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Unable to process invoice generation request for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].", Since there are future invoices are already created.";
		$issuccess				= false;
		$json = json_encode($Response);
		echo $json;
		die;
	}

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year AND status < :status AND isprocessing =:isprocessing";
	$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year'],'isprocessing'=>1,'status'=>1);

	$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);
	
	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Unable to save pricing as invoice(s) generation is already in process for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'];
		$issuccess				= false;
	}	
	else
	{
		$PriceCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
		year		=:year
		AND
		month		=:month
		AND
		clientid	=:clientid";

		$PriceCheckEsql	= array(
			"year"			=>(int)$_POST['year'],
			"month"			=>(int)$_POST['month'],
			"clientid"		=>(int)$_POST['clientid']
		);

		$PriceCheckQuery	= pdo_query($PriceCheckSql,$PriceCheckEsql);
		$PriceCheckNum		= pdo_num_rows($PriceCheckQuery);
		if($PriceCheckNum < 1)
		{
			$Response['success']		= false;
			$Response['pricingerror']	= true;
			$Response['msg']			= "Please update pricing for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

			$json = json_encode($Response);
			echo $json;
			die;
		}

		/*$CompletedCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
		year		=:year
		AND
		month		=:month
		AND
		clientid	=:clientid
		AND
		price	< :price";

		$CompletedCheckEsql	= array(
			"year"			=>(int)$_POST['year'],
			"month"			=>(int)$_POST['month'],
			"clientid"		=>(int)$_POST['clientid'],
			"price"	=> 0.1
		);

		$CompletedCheckQuery	= pdo_query($CompletedCheckSql,$CompletedCheckEsql);
		$CompletedCheckNum		= pdo_num_rows($CompletedCheckQuery);

		if($CompletedCheckNum > 0)
		{
			$Response['success']		= false;
			$Response['pricingerror']	= true;

			$Response['msg']			= "Please add all inventory pricing for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

			$json = json_encode($Response);
			echo $json;
			die;
		}*/

		$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year";
		$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year']);
		
		$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$RowCheck	=	pdo_fetch_assoc($CheckQuery);

			$Status			=	$RowCheck['status'];
			$ID				=	$RowCheck['id'];

			$Sql	= "UPDATE ".$Prefix."invoice_request_queue SET 
			status		=:status,
			isprocessing=:isprocessing
			WHERE
			id	= :id";
			$Esql	= array(
				"status"		=>0,
				"isprocessing"	=>0,
				"id"			=>(int)$ID
			);
			$Query	= pdo_query($Sql,$Esql);

			$Response['success']		= true;
			$Response['canswitchrun']	= true;

			$Response['msg']			= "Bill(s) queue re-generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
			$Response['toastmsg']		= "Bill(s) queue re-generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceFilterYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list year.";

	$currentyear	= date("Y");
	/*$startyear	= $currentyear - $InvoiceYears;*/

	$RecordSetArr	= array();

	$index	= 0;
	$startyear = '2019';
	for($yearloop = $currentyear; $yearloop >= $startyear; $yearloop--)
	{
		$RecordSetArr[$index]['name']	= "".$yearloop."";

		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['recordset']		= $RecordSetArr;
		$response['msg']			= "Year listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMonthByYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list month.";

	$RecordSetArr	= array();
	$index	= 0;


	$startmonth	= "01";
	$endmonth	= "12";

	for($monthloop = $startmonth; $monthloop <= $endmonth; $monthloop++)
	{
		if($monthloop < 10)
		{
			$month	= "0".(int)$monthloop;
		}
		else
		{
			$month = $monthloop;			
		}
		if($monthloop > 9)
		{
			$month2	= (int)$monthloop;
		}

		$RecordSetArr[$index]['month']		= (int)$month;
		$RecordSetArr[$index]['name']		= FUllMonthName($month);
		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['recordset']	= $RecordSetArr;
		$response['msg']		= "Month listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerInvoices")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch invoices.";

	$RecordSet				= array();

	$InvoiceEsql	= array("clientid"=>(int)$_POST['clientid']);
	$condition		= "";

	$custcondition	= "";

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$custcondition	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$custcondition	.= " AND lineid IN(".$lineids.")";
	}

	$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE 1 AND id=:id AND deletedon < :deletedon ".$custcondition." ORDER BY sequence ASC, customerid ASC";
	$CustomerEsql	= array("id"=>(int)$_POST['customerid'],"deletedon"=>1);

	$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	$name2	= "Select";

	if($CustomerNum > 0)
	{
		$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

		$customername	= $CustomerRows['name'];
		$firstname		= $CustomerRows['firstname'];
		$lastname		= $CustomerRows['lastname'];
		$customerid		= $CustomerRows['customerid'];
		$phone			= $CustomerRows['phone'];

		if(trim($customername) == "")
		{
			$customername	= $firstname." ".$lastname;
		}
		$name2	= "#".$customerid." ".$customername;

		if(trim($phone) != "")
		{
			$name2	.= " (".$phone.")";
		}
	}

	if($_POST['customerid'] > 0)
	{
		$condition		.= " AND customerid=:customerid";
		$InvoiceEsql['customerid']	= (int)$_POST['customerid'];
	}

	$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE 1 ".$condition." AND clientid=:clientid AND ispaid < :ispaid AND deletedon < :deletedon";
	$InvoiceEsql['ispaid']	= 1;
	$InvoiceEsql['deletedon']	= 1;

	$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
	$InvoiceNum		= pdo_num_rows($InvoiceQuery);

	$index	= 0;

	if($InvoiceNum > 0)
	{
		while($rows = pdo_fetch_assoc($InvoiceQuery))
		{
			$RecordSet[$index]['id']		= $rows['id'];
			$RecordSet[$index]['createdon']	= date("j F, Y",$rows['createdon']);
			$RecordSet[$index]['amount']	= $rows['totalamount'];

			$index++;
		}
	}
	else
	{
		if($_POST['iscustomerarea'] > 0)
		{
			$response['msg']	= "No invoice detail found!";
		}
	}

	if(!empty($RecordSet))
	{
		$response['success']		= true;
		$response['msg']			= "Unable to fetch invoices.";
		$response['invoicelist']	= $RecordSet;
	}
	$response['customername']	= $name2;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch invoice detail.";
	$RecordSet				= array();

	$SQL		= "SELECT * FROM ".$Prefix."invoices WHERE id=:id AND deletedon <:deletedon";
	$ESQL		= array("id"=>(int)$_POST['orderid'],'deletedon'=>1);

	$OrderQuery	= pdo_query($SQL,$ESQL);
	$OrderNum	= pdo_num_rows($OrderQuery);

	if($OrderNum > 0)
	{
		$orderrows = pdo_fetch_assoc($OrderQuery);

		$Floor				= "";
		$OrderID			= $orderrows['id'];
		$ClientID			= $orderrows['clientid'];
		$CustomerID			= $orderrows['customerid'];
		$InvoiceNumber		= $orderrows['invoiceid'];
		$InvoiceMonth		= $orderrows['invoicemonth'];
		$InvoiceYear		= $orderrows['invoiceyear'];
		$InvoiceUnixDate	= $orderrows['invoicedate'];
		$InvoiceDate		= date("d-M-Y",$InvoiceUnixDate);
		$IsPaid				= $orderrows['ispaid'];
		$PaidStatus			= "Not Paid";

		$CustomerNumber	= GetCustomerID($CustomerID);

		if($IsPaid > 0)
		{
			$PaidStatus	= 'Paid';
		}

		$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
		$ClientESQL		= array("id"=>(int)$ClientID);

		$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
		$ClientNum		= pdo_num_rows($ClientQuery);
		
		if($ClientNum > 0)
		{
			$ClientRow		= pdo_fetch_assoc($ClientQuery);
			
			$AgentName		= $ClientRow['clientname'];
			$AgentAddress	= $ClientRow['invoiceaddress'];
			$AgentPhone		= $ClientRow['invoicephone'];		
		}

		$CustomerName		= $orderrows["customername"];
		$CustomerPhone		= $orderrows["customerphone"];
		$CreatedOn			= $orderrows['createdon'];
		
		$Address			= @$orderrows['customeraddress1'];
		$Address2			= @$orderrows['customeraddress2'];
		$PinCode			= @$orderrows['customerpincode'];

		if(trim($CustomerPhone) =="")
		{
			$CustomerPhone = "--";
		}

		$CityName			= $orderrows['customercity'];
		$StateName			= $orderrows['customerstate'];
		$StateName			= $orderrows['customerstate'];
		$Discount			= $orderrows['discount'];
		$ServiceCharge		= $orderrows['servicecharge'];
		$TotalAmount		= $orderrows['totalamount'];
		$FinalAmount		= $orderrows['finalamount'];
		$ConvenienceCharge	= $orderrows['conveniencecharge'];
		$PreviousBalance	= $orderrows['previousbalance'];

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND deletedon < :deletedon";
		$CustESQL	= array("id"=>(int)$CustomerID,"deletedon"=>1);

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		if($CustNum > 0)
		{
            $AllLineArr		= GetAllLine($ClientID);
            $AllLinemanArr	= GetAllLineman($ClientID);
			$AllSubLine		= GetAllSubLine($ClientID);

            $CustRow		= pdo_fetch_assoc($CustQuery);

			$housenumber	= $CustRow['housenumber'];
			$floor			= $CustRow['floor'];
			$address1		= $CustRow['address1'];

            $LineID			= $CustRow['lineid'];
			$LinemanID		= $CustRow['linemanid'];
			$sublineid		= $CustRow['sublineid'];
            $LineName		= $AllLineArr[$LineID]['name']; 
            $LineManName	= $AllLinemanArr[$LinemanID]['name']; 
			$sublinename	= $AllSubLine[$sublineid]['name'];
		}

		$addresstr = '';

		if($housenumber !='')
		{
			$addresstr .= $housenumber;
		}
		if($floor !='')
		{
			$ext = '';
			if($floor !='Basement')
			{
				$ext = 'floor';
			}
			if($floor =='Ground')
			{
				$floor	= "G.";
				$ext	= 'F.';
			}
			if($addresstr !='')
			{
				$addresstr .= ", ".$floor." ".$ext;
			}
			else
			{
				$addresstr .= $floor." ".$ext;
			}
		}
		if($address1 !='')
		{
			if($addresstr !='')
			{
				$addresstr .= ", ".$address1;
			}
			else
			{
				$addresstr .= $address1;
			}
		}
		if($sublinename !='')
		{
			if($addresstr !='')
			{
				$addresstr .= ", ".$sublinename;
			}
			else
			{
				$addresstr .= $sublinename;
			}
		}

		if(trim($addresstr) =='')
		{
			$addresstr = '--';
		}

		$FullAddress	= $addresstr;

		/*$FullAddress	= $Address;

		if(trim($Address2) !='')
		{
			if(trim($Address) !="")
			{
				$FullAddress .=" ".$Address2;
			}
			else
			{
				$FullAddress =$Address2;
			}
		}*/

		$IsKYCDocs = "";

		$OrderDetSQL		= "SELECT * FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid ORDER BY item_start_date,inventoryname ASC";
		$OrderDetESQL		= array("invoiceid"=>(int)$OrderID);
		$OrderDetailQuery	= pdo_query($OrderDetSQL,$OrderDetESQL);
		$Num				= pdo_num_rows($OrderDetailQuery);
		$GrandTotal		= 0;
		$TotalSaving	= 0;

		$RecordSet['custnumber']	= $CustomerNumber;
		$RecordSet['custname']		= $CustomerName;
		$RecordSet['custaddress']	= $FullAddress;
		$RecordSet['custphone']		= $CustomerPhone;
		$RecordSet['invoicenumber']	= $InvoiceNumber;
		$RecordSet['invoicedate']	= $InvoiceDate;
		$RecordSet['invoicemonth']	= $MonthArr[$InvoiceMonth];
		$RecordSet['invoiceyear']	= $InvoiceYear;

		$index	= 0;
		$ItemDetailArr	= array();

		if($Num > 0)
		{
			while($ordrow = pdo_fetch_assoc($OrderDetailQuery))
			{
				$ODID			= $ordrow["id"];
				$Price			= $ordrow["price"];
				$Quantity		= $ordrow["qty"];
				$Frequency		= $ordrow["frequency"];
				$NoofDays		= $ordrow["noofdays"];
				$TotalPrice		= $ordrow["totalprice"];
				$ItemName		= $ordrow["inventoryname"];
				$InvStartDate	= $ordrow["item_start_date"];
				$InvEndDate		= $ordrow["item_end_date"];
				
				if($Frequency != 1)
				{
					$NoofDays	= '-';
				}
				$StartDate = date("d-M-Y",$InvStartDate);
				$EndDate   = date("d-M-Y",$InvEndDate);
				if(trim($StartDate) !=trim($EndDate))
				{
					$ItemNaration	= "( ".$StartDate." - ".$EndDate." )";
				}
				else
				{
					$ItemNaration	= "( ".$StartDate." )";
				}
				$LineTotal		= $TotalPrice;
				$GrandTotal		+= $LineTotal;

				$ItemDetailArr[$index]['index']			= $index+1;
				$ItemDetailArr[$index]['itemname']		= $ItemName;
				$ItemDetailArr[$index]['itemnarration']	= $ItemNaration;
				$ItemDetailArr[$index]['price']			= @number_format($Price,2);
				$ItemDetailArr[$index]['quantity']		= $Quantity;
				$ItemDetailArr[$index]['noofdays']		= $NoofDays;
				$ItemDetailArr[$index]['linetotal']		= @number_format($LineTotal,2);

				$index++;
			}
		}
		
		//$PreviousBalance = GetPreviousBalanceTillDate($CustomerID,$InvoiceYear,$InvoiceMonth);

		$TempTotal	= $GrandTotal - $Discount;

		$FinalAmount = $FinalAmount + $PreviousBalance;

		$RecordSet['itemdetails']		= $ItemDetailArr;
		$RecordSet['subtotal']			= @number_format($GrandTotal,2);
		$RecordSet['discount']			= @number_format($Discount,2);
		$RecordSet['servicecharge']		= @number_format($ServiceCharge,2);
		$RecordSet['previousbalance']	= @number_format($PreviousBalance,2);
		$RecordSet['conveniencecharge']	= @number_format($ConvenienceCharge,2);
		$RecordSet['finalamount']		= @number_format($FinalAmount,2);
	}

	if(!empty($RecordSet))
	{
		$response['success']		= true;
		$response['msg']			= "Bill detail fetched successfully.";
		$response['invoicedetail']	= $RecordSet;
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetAllInvoices")
{
	$perpage = 100;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}

	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch invoices.";

	$condition	= " AND inv.deletedon <:deletedon AND cust.deletedon <:deletedon2";
	$Esql		= array("deletedon"=>1,"deletedon2"=>1);
	$FilterMonth = '';
	$FilterYear = '';
	if($_POST['year'] > 0)
	{
		$condition	.= " AND inv.invoiceyear=:invoiceyear";
		$Esql['invoiceyear']	= (int)$_POST['year'];
		$FilterYear = $_POST['year'];
	}
	if($_POST['month'] > 0)
	{
		$condition	.= " AND inv.invoicemonth=:invoicemonth";
		$Esql['invoicemonth']	= (int)$_POST['month'];
		$FilterMonth = FullMonthName($_POST['month']);
	}
	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND inv.clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
	{
		$condition	.= " AND cust.lineid=:lineid";
		$Esql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['areaid'] > 0)
	{
		$condition	.= " AND cust.areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$condition	.= " AND cust.hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
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

	if(trim($_POST['searchkeyword']) != "")
	{
		$condition	.= " AND (cust.name LIKE :name || cust.phone LIKE :phone || cust.email LIKE :email || cust.address1 LIKE :address1 || cust.customerid LIKE :customerid)";

		$Esql['name']		= "%".$_POST['searchkeyword']."%";
		$Esql['phone']		= "%".$_POST['searchkeyword']."%";
		$Esql['email']		= "%".$_POST['searchkeyword']."%";
		$Esql['address1']	= "%".$_POST['searchkeyword']."%";
		$Esql['customerid']	= "".$_POST['searchkeyword']."%";
	}

	$Sql	= "SELECT inv.*,cust.name as customername,cust.customerid as customerid, cust.phone as customerphone,cust.lineid as customerlineid, cust.linemanid as customerlinemanid, cust.hawkerid as customerhawkerid FROM ".$Prefix."customers cust, ".$Prefix."invoices inv WHERE cust.id=inv.customerid ".$condition." GROUP BY inv.invoiceid ORDER BY inv.invoiceid ASC,inv.createdon DESC";
	
	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);
	$TotalRec	= $Num;

	if($Num > 0)
	{
		$totalpages	= ceil($Num/$perpage);
		$offset		= ($_POST['page'] - 1) * $perpage;
		$addquery	= " LIMIT %d, %d";
	}
	else
	{
		$addquery	= "";
	}

	$Sql2	= $Sql.$addquery;
	$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));
	$Query2	= pdo_query($Sql2,$Esql);
	$Num2	= pdo_num_rows($Query2);

	if($Num2 > 0)
	{
		$index	= 0;

		$GetAllLine		= GetAllLine($_POST['clientid']);
		$GetAllLineman	= GetAllLineman($_POST['clientid']);
		$GetAllHawker	= GetAllHawker($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$id			= $rows['id'];
			$name		= $rows['customername'];
			$phone		= $rows['customerphone'];
			$createdon	= $rows['createdon'];
			$customerid	= $rows['customerid'];
			$month		= $rows['invoicemonth'];
			$year		= $rows['invoiceyear'];
			$amount		= $rows['finalamount'];
			$invoiceid	= $rows['invoiceid'];
			
			$line		= $GetAllLine[$rows['customerlineid']]['name'];
			$lineman	= $GetAllLineman[$rows['customerlinemanid']]['name'];
			$hawker		= $GetAllHawker[$rows['customerhawkerid']]['name'];

			$name	= "#".$customerid.' '.$name;

			if(trim($phone) !='')
			{
				$name	.= " (".$phone.')';
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}

			if($lineman == "")
			{
				$lineman	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['customerid']	= $customerid;
			$RecordListArr[$index]['invoiceid']		= $invoiceid;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['line']			= $line;
			$RecordListArr[$index]['lineman']		= $lineman;
			$RecordListArr[$index]['hawker']		= $hawker;
			$RecordListArr[$index]['amount']		= $amount;
			$RecordListArr[$index]['month']			= ShortMonthName($month).", ".$year;
			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Bill listed successfully.";
	}

	$pageListArr	= array();
	$pagelistindex	= 0;

	for($pageloop = 1; $pageloop <= $totalpages; $pageloop++)
	{
		$pageListArr[$pagelistindex]['page']	= $pageloop;
		$pageListArr[$pagelistindex]['name']	= "Page ".$pageloop;

		$pagelistindex++;
	}

	$response['recordlist']		= $RecordListArr;
	$response['perpage']		= (int)$perpage;
	$response['totalpages']		= (int)$totalpages;
	$response['paginglist']		= $pageListArr;
	$response['showpages']		= false;
	$response['totalrecord']	= $TotalRec;
	$response['filtermonth']	= $FilterMonth;
	$response['filteryear']		= $FilterYear;
	
	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteInvoice")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete invoice, Please try later.";

	
	$SelSQL	 = "SELECT * FROM ".$Prefix."invoices WHERE id=:id AND clientid=:clientid";
	$SelESQL = array((int)$_POST['recordid'],"clientid"	=>(int)$_POST['clientid']);
	$SelQuery = pdo_query($SelSQL,$SelESQL);

	$SelNum	 = pdo_num_rows($SelQuery);
	$InvoiceBalance = 0;
	$CustomerID = 0;
	if($SelNum > 0)
	{
		$SelRow	= pdo_fetch_assoc($SelQuery);
		$InvoiceBalance = $SelRow['finalamount']; 
		$CustomerID = $SelRow['customerid']; 
	}

	$DelSql		= "UPDATE ".$Prefix."invoices SET 
	deletedon	=:deletedon 
	WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$DelEsql	= array(
		"deletedon"	=>time(),
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']	
	);

	$DelQuery	= pdo_query($DelSql,$DelEsql);

	if($DelQuery)
	{
		if(abs($InvoiceBalance) > 0 && $CustomerID > 0)
		{
			$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance = (outstandingbalance - :outstandingbalance) WHERE id=:id";
			$UpdateEsql	= array("outstandingbalance"=>$InvoiceBalance,"id"=>(int)$CustomerID);
			pdo_query($UpdateSql,$UpdateEsql);
		}
		/*$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid=:invoiceid AND clientid=:clientid";
		$DelESQL = array("invoiceid"=>(int)$_POST['recordid'],'clientid'=>(int)$_POST['clientid']);
		pdo_query($DelSQL,$DelESQL);

		$SelSql		= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND invoiceid=:invoiceid";
		$SelEsql	= array("invoiceid"=>(int)$_POST['recordid'],'clientid'=>(int)$_POST['clientid']);
		$SelQuery	= pdo_query($SelSQL,$SelEsql);
		$SelNum		= pdo_num_rows($SelQuery);
		if($SelNum > 0)
		{
			$SelRow			= pdo_fetch_assoc($SelQuery);
			$CusRecordID	= $SelRow['customerid'];
			
			$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid=:invoiceid AND clientid=:clientid AND customerid=:customerid";
			$DelESQL = array("invoiceid"=>(int)$_POST['recordid'],'clientid'=>(int)$_POST['clientid'],'customerid'=>(int)$_POST['clientid']);
			pdo_query($DelSQL,$DelESQL);
			
			GetCustomerLineTotalUpdated($CustRecordID);
		}
		*/

		$Response['success']	= true;
		$Response['msg']		= "Bill deleted successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetInvoicePDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate invoice pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/invoices/");
	}

	$Pdf_FileName 	= 'invoice-'.$_POST['invoiceid'].".pdf";
	
	$File	= "viewinvoice.php?invoiceid=".$_POST['invoiceid'];

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/invoices/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/invoices/".$Pdf_FileName;
		$Response['msg']			= "Bill pdf generated successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteAgencyInvoice")
{
	$Response['pricingerror']	= false;
	$Response['success']		= false;
    $Response['msg']			= "Oops unable to delete invocie. Please try again.";

	/*$Sql	= "SELECT GROUP_CONCAT(id) AS ids FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid";
	$Esql	= array("invoicemonth"=>$_POST['month'],"invoiceyear"=>$_POST['year'],"clientid"=>(int)$_POST['clientid']);

	$Query	= pdo_query($Sql,$Esql);
	$rows	= pdo_fetch_assoc($Query);

	$ids	= $rows['ids'];*/

	$IDsArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid";
	$Esql	= array("invoicemonth"=>$_POST['month'],"invoiceyear"=>$_POST['year'],"clientid"=>(int)$_POST['clientid']);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$CustomerIDs = array();
	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$customerid	= $rows['customerid'];
			$finalamount= $rows['finalamount'];
			$IDsArr[]	= $id;

			$CustomerIDs[$customerid] +=(float)$finalamount;
		}
	}

	$IDsArr	= @array_filter(@array_unique($IDsArr));

	if(!empty($IDsArr))
	{
		$ids	= implode(",",$IDsArr);
	}

	if(trim($ids) != "")
	{
		/*$DelSql		= "DELETE FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND id IN(".$ids.") AND clientid=:clientid";
		$DelEsql	= array("invoicemonth"=>$_POST['month'],"invoiceyear"=>$_POST['year'],"clientid"=>(int)$_POST['clientid']);

		$DelQuery	= pdo_query($DelSql,$DelEsql);*/

		$OldInvoiceInfoSql	= "SELECT * FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND id IN(".$ids.") AND clientid=:clientid";
		$OldInvoiceInfoEsql	= array("invoicemonth"=>$_POST['month'],"invoiceyear"=>$_POST['year'],"clientid"=>(int)$_POST['clientid']);

		$OldInvoiceInfoQuery	= pdo_query($OldInvoiceInfoSql,$OldInvoiceInfoEsql);
		$OldInvoiceInfoNum		= pdo_num_rows($OldInvoiceInfoQuery);

		if($OldInvoiceInfoNum > 0)
		{
			while($OldInvoiceInfoRow = pdo_fetch_assoc($OldInvoiceInfoQuery))
			{
				$invoiceid		= $OldInvoiceInfoRow['id'];
				$tempcustomerid	= $OldInvoiceInfoRow['customerid'];
				$invoiceamount	= $OldInvoiceInfoRow['finalamount'];

				$DelSQL		= "DELETE FROM ".$Prefix."invoices WHERE id=:id";
				$DelESQL	= array("id"=>(int)$invoiceid);
				$DelInvoice	= pdo_query($DelSQL,$DelESQL);

				if($DelInvoice)
				{
					$UpdateCustInfoSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=(outstandingbalance - :outstandingbalance) WHERE id=:id";
					$UpdateCustInfoEsql	= array("outstandingbalance"=>(float)$invoiceamount,"id"=>(int)$tempcustomerid);

					$UpdateCustInfoQuery = pdo_query($UpdateCustInfoSql,$UpdateCustInfoEsql);
				}
			}
		}

		/*$DelSQL = "DELETE FROM ".$Prefix."cust_accounts WHERE invoiceid IN (".$ids.") AND clientid=:clientid";
		$DelESQL = array('clientid'=>(int)$_POST['clientid']);
		pdo_query($DelSQL,$DelESQL);

		if(!empty($IDArr))
		{
			foreach($IDArr as $key => $CustomerID)
			{
				GetCustomerLineTotalUpdated($CustomerID);
			}
		}
		$DelSql2	= "DELETE FROM ".$Prefix."invoice_details WHERE clientid=:clientid AND invoiceid IN(".$ids.")";
		$DelEsql2	= array("clientid"=>(int)$_POST['clientid']);

		$DelQuery2	= pdo_query($DelSql2,$DelEsql2);

		if(!empty($CustomerIDs))
		{
			foreach($CustomerIDs as $CustomerID => $AmountToDeduct)
			{
				if(abs($AmountToDeduct) > 0 && $CustomerID > 0)
				{
					$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance = (outstandingbalance - :outstandingbalance) WHERE id=:id";
					$UpdateEsql	= array("outstandingbalance"=>$AmountToDeduct,"id"=>(int)$CustomerID);
					pdo_query($UpdateSql,$UpdateEsql);
				}
			}
		}*/

		$delsql		= "DELETE FROM ".$Prefix."invoice_request_queue WHERE year=:year AND month=:month AND clientid=:clientid";
		$delesql	= array("month"=>$_POST['month'],"year"=>$_POST['year'],"clientid"=>(int)$_POST['clientid']);
	
		pdo_query($delsql,$delesql);

		$Response['success']	= true;
		$Response['msg']		= "Bill(s) has been deleted for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "MarkInvoiceRequestCompleted")
{
	$Response['pricingerror']	= false;
	$Response['success']		= false;
    $Response['msg']			= "Oops unable to stop pending queue. Please try again.";

	$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET status=:status,isprocessing=:isprocessing WHERE clientid=:clientid AND month=:month AND year=:year";
	$UpdateESQL	= array("status"=>1,"isprocessing"=>0,"clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year']);

	$UpdateQuery	= pdo_query($UpdateSQL,$UpdateESQL);

	if($UpdateQuery)
	{
		$Response['success']	= true;
		$Response['msg']		= "Bill(s) generation has been stopped for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
?>