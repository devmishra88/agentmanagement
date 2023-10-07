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

if($_POST['Mode'] == "AddSMSCredit")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add SMS credit request.";

	if($_POST['packageid'] == "")
	{
		$ErrorMsg	.= "Please select a package.<br>";
	}
    
    if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
	}

	if($haserror == false)
	{
		$ispaid = 0;
		$paymentstatus = 'pending';
		if($_POST['packagecost'] < 0.1)
		{
			$ispaid = 1;
			$paymentstatus = 'paid';
		}
		$Sql	= "INSERT INTO ".$Prefix."sms_credit_log SET 
		clientid		=:clientid,
		packageid		=:packageid,
		smscredits		=:smscredits,
		packagecost		=:packagecost,
		ispaid			=:ispaid,
		paymentstatus	=:paymentstatus,
		createdon		=:createdon,
		packagetype		=:packagetype";

		$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"packageid"		=>(int)$_POST['packageid'],
			"smscredits"	=>$_POST['smscredits'],
			"packagecost"	=>CleanPriceString($_POST['packagecost']),
			"ispaid"		=>(int)$ispaid,
			"paymentstatus"	=>$paymentstatus,
			"createdon"		=>$createdon,
			"packagetype"	=>(int)$_POST['packagetype']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$paymentlink ='';
			$paymentid	='';
				
			if($ispaid < 1)
			{
				$clientsql		= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
				$clientesql 	= array("id"=>(int)$_POST['clientid']);
				$clientquery	= pdo_query($clientsql,$clientesql);
				$clientrow		= pdo_fetch_assoc($clientquery);
			
				$CustomerArr['name'] = $clientrow['clientname'];
				
				$CustomerArr['email'] = $clientrow['contactemail'];
				$CustomerArr['phone'] = $clientrow['phone1'];

				$Amount		= (float)extract_numbers($_POST['packagecost']);
				
				$Notes		= "Payment for ".$_POST['smscredits']." credits by ".$clientrow['clientname'];

				$paymentlink = GeneratePaymentLinks_SMSCredit($CustomerArr,$Amount,$Notes,$recordid);
				if($paymentlink !='')
				{
					$sql 		= "SELECT * FROM ".$Prefix."sms_credit_log WHERE id=:id";
					$esql 		= array("id"=>(int)$recordid);
					$query		= pdo_query($sql,$esql);
					$row		= pdo_fetch_assoc($query);
					$paymentid	= $row['razorpayid'];

					CreatePaymentRequest($paymentid, $paymentlink, $_POST['clientid']);
				}
			}

			$response['success']		= true;
			$response['recordid']		= $recordid;
			$response['paymentlink']	= $paymentlink;
			$response['paymentid']		= $paymentid;
			$response['msg']			= "SMS credit request successfully submitted.";
			$response['toastmsg']		= "SMS credit request successfully submitted.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSMSCreditLog_bak")
{
	$time	= time();

	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch contacts.";

	$condition	= " AND deletedon <:deletedon AND credittype=:credittype";
	$Esql		= array("deletedon"=>1,"credittype"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	$DateArr			= array();
	$ItemNameArr		= array();
	$SMSCostArr			= array();
	$SMSCreditArr		= array();
	$SMSDebitArr		= array();
	$SMSStatusArr		= array();
	$SMSStatusArr2		= array();
	$PackageTypeArr		= array();

	$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);

	$SqlCampaign	= "SELECT * FROM ".$Prefix."campaign WHERE clientid=:clientid";
	$EsqlCampaign	= array('clientid'=>(int)$_POST['clientid']);

	$QueryCampaign	= pdo_query($SqlCampaign,$EsqlCampaign);
	$NumCampaign	= pdo_num_rows($QueryCampaign);

	if($Num > 0 || $NumCampaign > 0)
	{
		$index	= 0;

		if($Num > 0)
		{
			$GetAllSMSPackage		= GetAllSMSPackages($_POST['clientid']);

			$totalsmscreditsavaiable    = 0; 
			$totalsmscreditsinrequest   = 0; 
			$totalsmscreditsused        = 0;
			$totalactivecredits         = 0;
			$totalrefunds		        = 0;
			$totalpendingrecharge		= 0;

			$sqlcount	= "SELECT SUM(totalsent * smscredit) AS C FROM ".$Prefix."campaign WHERE clientid=:clientid";
			$esqlcount	= array('clientid'=>(int)$_POST['clientid']);

			$querycount	= pdo_query($sqlcount,$esqlcount);

			$totalsmscreditsusedrow   = pdo_fetch_assoc($querycount);
			$totalsmscreditsused      = $totalsmscreditsusedrow['C'];
			
			while($rows = pdo_fetch_assoc($Query))
			{
				$isselected	= false;

				$PackageTypeText	= "";

				$id		    	= $rows['id'];
				$packageid		= $rows['packageid'];
				$packagename    = $GetAllSMSPackage[$packageid]["name"];
				$packagecost	= $rows['packagecost'];
				$smscredit		= $rows['smscredits'];
				$createdon	    = $rows['createdon'];
				/*$createdon	= date("d/m/Y",$rows['createdon']);*/
				$status		    = $rows['status'];
				$addedbyadmin   = $rows['addedbyadmin'];
				$reason			= $rows['reason'];
				$type			= $rows['type'];
				$logtype		= $rows['logtype'];
				$packagetype	= $rows['packagetype'];

				if($packagetype > 0)
				{
					$PackageTypeText	= "Trans.";
				}
				else
				{
					$PackageTypeText	= "Promo.";
				}
				if($logtype == 'debit')
				{
					$PackageTypeText	= 'Refund';
					$campaignid			= $rows['campid'];
					$packagename		= $rows['campaignname'];

					if(trim($packagename) == "")
					{
						$packagename	= "SMS Campaign #".$campaignid;
					}
					else
					{
						$packagename	.= " #".$campaignid;
					}
				}

				/*$statustxt    = 'Req. Pending';

				if($status > 0)
				{
					$totalactivecredits += $smscredit; 
					$statustxt = "Recharge";
				}
				else
				{
					$totalpendingrecharge	+= 1;
					$totalsmscreditsinrequest += $smscredit;
				}*/

				if($status > 0)
				{
					if($type > 0)
					{
						if($logtype == 'debit')
						{
							$totalrefunds += $smscredit;
						}
						else
						{
							$totalactivecredits += $smscredit; 
						}
						$statustxt = "Recharge";

						if($addedbyadmin > 0)
						{
							$packagename	= $reason;
							$packagecost	= "0";
						}

						$DateArr[]			= $createdon;
						$ItemNameArr[]		= $packagename;
						$SMSCostArr[]		= $packagecost;
						$SMSCreditArr[]		= $smscredit;
						$SMSDebitArr[]		= "---";
						$SMSStatusArr[]		= $statustxt;
						$SMSStatusArr2[]	= (int)$status;
						$PackageTypeArr[]	= $PackageTypeText;
					}
					else
					{
						$totalsmscreditsused      += $smscredit;

						$statustxt = "Spent";

						if($addedbyadmin > 0)
						{
							$packagename	= $reason;
						}

						$DateArr[]			= $createdon;
						$ItemNameArr[]		= $packagename;
						$SMSCostArr[]		= "---";
						$SMSCreditArr[]		= "---";
						$SMSDebitArr[]		= $smscredit;
						$SMSStatusArr[]		= $statustxt;
						$SMSStatusArr2[]	= 0;
						$PackageTypeArr[]	= "---";
					}
				}
				else
				{
					$totalpendingrecharge	+= 1;
				}

				/*$RecordListArr[$index]['id']			= (int)$id;
				$RecordListArr[$index]['createdon']		= $createdon;
				$RecordListArr[$index]['packagename']	= $packagename;
				$RecordListArr[$index]['packagecost']	= $packagecost;
				$RecordListArr[$index]['smscredits']	= $smscredit;
				$RecordListArr[$index]['status']		= (int)$status;
				$RecordListArr[$index]['statustxt']		= $statustxt;

				$index++;*/
			}
		}

		if($NumCampaign > 0)
		{
			while($RowsCampaign = pdo_fetch_assoc($QueryCampaign))
			{
				$campaignid		= $RowsCampaign['id'];
				$createdon		= $RowsCampaign['createdon'];
				$campaignname	= $RowsCampaign['campaignname'];

				if(trim($campaignname) == "")
				{
					$campaignname	= "SMS Campaign #".$campaignid;
				}
				else
				{
					$campaignname	= "SMS Campaign #".$campaignid." (".$campaignname.")";
				}

				$SqlCount2		= "SELECT SUM(smscredit) AS C FROM ".$Prefix."campaign_history WHERE clientid=:clientid AND issent >:issent AND campid=:campid";
				$EsqlCount2		= array('issent'=>0,'clientid'=>(int)$_POST['clientid'],"campid"=>(int)$campaignid);

				$QueryCount2	= pdo_query($SqlCount2,$EsqlCount2);
				$creditusedrows	= pdo_fetch_assoc($QueryCount2);

				$smscreditsused	= $creditusedrows['C'];

				$DebitText	= "Pending";

				if($smscreditsused > 0)
				{
					$DebitText	= "Spent";
				}
				else
				{
					$smscreditsused	= 0;
				}

				$DateArr[]			= $createdon;
				$ItemNameArr[]		= $campaignname;
				$SMSCostArr[]		= "---";
				$SMSCreditArr[]		= "---";
				$SMSDebitArr[]		= $smscreditsused;
				$SMSStatusArr[]		= "Spent";
				$SMSStatusArr2[]	= 0;
				$PackageTypeArr[]	= "---";
			}
		}

		/*$SqlPayment	= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND smsmresponse<>:smsmresponse AND smsmresponse IS NOT NULL";
		$EsqlPayment	= array('clientid'=>(int)$_POST['clientid'],"smsmresponse"=>"");

		$QueryPayment	= pdo_query($SqlPayment,$EsqlPayment);
		$PaymentNum		= pdo_num_rows($QueryPayment);

		$uniquepaymentdate	= array();

		if($PaymentNum > 0)
		{
			while($paymentrows = pdo_fetch_assoc($QueryPayment))
			{
				$paymentdate	= $paymentrows['paymentdate'];

				if($paymentdate > 0)
				{
					$uniquepaymentdate[]	= date("r",strtotime(date("d-m-Y",$paymentdate)));
				}
			}

			$uniquepaymentdate	= @array_filter(@array_unique($uniquepaymentdate));

			print_r($uniquepaymentdate);

			die;
		}*/



		array_multisort($DateArr, SORT_ASC, $ItemNameArr, $SMSCostArr, $SMSCreditArr, $SMSDebitArr, $SMSStatusArr, $SMSStatusArr2, $PackageTypeArr);

		if(!empty($DateArr))
		{
			$Index		= 0;
			$SMSBalance	= 0;

			foreach($DateArr as $key => $value)
			{
				$ItemName		= $ItemNameArr[$key];
				$SMSCost		= $SMSCostArr[$key]; 
				$SMSCredit		= $SMSCreditArr[$key];
				$SMSDebit		= $SMSDebitArr[$key];
				$SMSStatusText	= $SMSStatusArr[$key];
				$SMSStatus		= $SMSStatusArr2[$key];
				$PackageType	= $PackageTypeArr[$key];

				if($SMSCredit > 0)
				{
					$SMSBalance	=  $SMSBalance + $SMSCredit; 
				}
				if($SMSDebit > 0)
				{
					$SMSBalance	=  $SMSBalance - $SMSDebit;
				}

				$date	= date("d/m/Y",$value);

				if(date("Y",$value) == date("Y",$time))
				{
					$date	= date("d/m",$value);
				}

				$transtime	= date("h:i a",$value);

				if($Index < 1)
				{
					$RecordListArr[$Index]['serial']		= $Index+1;
					$RecordListArr[$Index]['date']			= $date;
					$RecordListArr[$Index]['time']			= $transtime;
					$RecordListArr[$Index]['item']			= "Opening Balance";
					$RecordListArr[$Index]['smscost']		= "0.00";
					$RecordListArr[$Index]['smscredit']		= "0";
					$RecordListArr[$Index]['smsdebit']		= "0";
					$RecordListArr[$Index]['smsstatus']		= 0;
					$RecordListArr[$Index]['smsstatustxt']	= "---";
					$RecordListArr[$Index]['smsbalance']	= "0";
					$RecordListArr[$Index]['packagetype']	= "";

					$Index++;
				}

				$RecordListArr[$Index]['serial']		= $Index+1;
				$RecordListArr[$Index]['date']			= $date;
				$RecordListArr[$Index]['time']			= $transtime;
				$RecordListArr[$Index]['item']			= $ItemName;
				$RecordListArr[$Index]['smscost']		= number_format($SMSCost);
				$RecordListArr[$Index]['smscredit']		= number_format($SMSCredit);
				$RecordListArr[$Index]['smsdebit']		= number_format($SMSDebit);
				$RecordListArr[$Index]['smsstatus']		= $SMSStatus;
				$RecordListArr[$Index]['smsstatustxt']	= $SMSStatusText;
				$RecordListArr[$Index]['smsbalance']	= number_format($SMSBalance);
				$RecordListArr[$Index]['packagetype']	= $PackageType;

				$Index++;
			}
		}

		$totalsmscreditsavaiable = ((int)$totalactivecredits - (int)$totalsmscreditsused);

		$response['success']			= true;
		$response['hascredit']			= true;
		$response['haspendingcredit']	= false;

        $response['availablecredit']	= $totalsmscreditsavaiable;
        $response['usedcredit']			= $totalsmscreditsused;
        $response['requestedcredit']	= $totalsmscreditsinrequest;
       
		$response['totalpurchase']		= number_format($totalactivecredits);
        $response['totalused']			= number_format((int)$totalsmscreditsused);
        $response['totalbalance']		= number_format((int)$totalsmscreditsavaiable);
        $response['totalpending']		= number_format((int)$totalpendingrecharge);
		$response['totalrefunds']		= number_format((int)$totalrefunds);

        $response['msg']		= "SMS Credit Log listed successfully.";

	}
	else
	{
		$condition	= " AND deletedon <:deletedon AND status<:status";
		$Esql		= array("deletedon"=>1,"status"=>1);

		if($_POST['clientid'] > 0)
		{
			$condition	.= " AND clientid=:clientid";
			$Esql['clientid']	= (int)$_POST['clientid'];
		}

		$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

		$Query		= pdo_query($Sql,$Esql);
		$Num		= pdo_num_rows($Query);

		if($Num > 0)
		{
			$index	= 0;

			$GetAllSMSPackage	= GetAllSMSPackages($_POST['clientid']);

			while($rows = pdo_fetch_assoc($Query))
			{
				$isselected	= false;

				$id		    	= $rows['id'];
				$packageid		= $rows['packageid'];
				$packagename    = $GetAllSMSPackage[$packageid]["name"];
				$packagecost	= $rows['packagecost'];
				$smscredit		= $rows['smscredits'];
				$createdon		= $rows['createdon'];
				$createdon		= date("d/m/Y",$rows['createdon']);
				$status		    = $rows['status'];
				$statustxt		= 'Req. Pending';
				$type			= $rows['type'];

				$date	= date("d/m/Y",$createdon);

				if(date("Y",$createdon) == date("Y",$time))
				{
					$date	= date("d/m",$createdon);
				}

				$transtime	= date("h:i a",$createdon);

				if($type > 0)
				{
					if($status > 0)
					{
						$totalactivecredits += $smscredit; 
						$statustxt = "Recharge";
					}
					else
					{
						$totalsmscreditsinrequest += $smscredit;  
					}

					$RecordListArr[$index]['serial']		= (int)$index+1;
					$RecordListArr[$index]['id']			= (int)$id;
					$RecordListArr[$index]['date']			= $date;
					$RecordListArr[$index]['time']			= $transtime;
					$RecordListArr[$index]['packagename']	= $packagename;
					$RecordListArr[$index]['packagecost']	= $packagecost;
					$RecordListArr[$index]['smscredits']	= $smscredit;
					$RecordListArr[$index]['status']		= (int)$status;
					$RecordListArr[$index]['statustxt']		= $statustxt;

					$index++;
				}
			}

			$response['success']			= true;
			$response['hascredit']			= false;
			$response['haspendingcredit']	= true;
			$response['msg']				= "Pending SMS Credit Log listed successfully.";
		}
	}

	$response['recordlist']		= $RecordListArr;
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSMSCreditLog")
{
	$time	= time();

	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch contacts.";

	$condition	= " AND deletedon <:deletedon AND credittype=:credittype";
	$Esql		= array("deletedon"=>1,"credittype"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['startdate'] != "" && $_POST['enddate'] != "" && $_POST['datetype'] == 'selectdate')
	{
		$condition	.= " AND createdon BETWEEN :date1 AND :date2";

		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Esql['date1']	= $StartDate;
		$Esql['date2']	= $EndDate;
	}

	$DateArr			= array();
	$ItemNameArr		= array();
	$SMSCostArr			= array();
	$SMSCreditArr		= array();
	$SMSDebitArr		= array();
	$SMSStatusArr		= array();
	$SMSStatusArr2		= array();
	$PackageTypeArr		= array();

	$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);

	$SqlCampaign	= "SELECT * FROM ".$Prefix."campaign WHERE clientid=:clientid";
	$EsqlCampaign	= array('clientid'=>(int)$_POST['clientid']);

	$QueryCampaign	= pdo_query($SqlCampaign,$EsqlCampaign);
	$NumCampaign	= pdo_num_rows($QueryCampaign);

	$totalsmscreditsavaiable    = 0; 
	$totalsmscreditsinrequest   = 0; 
	$totalsmscreditsused        = 0;
	$totalactivecredits         = 0;
	$totalrefunds		        = 0;
	$totalpendingrecharge		= 0;

	$totalcampaignused			= 0;
	$totalpaymentused			= 0;

	$campaigncond	= "";
	$esqlcount		= array('clientid'=>(int)$_POST['clientid']);

	if($_POST['startdate'] != "" && $_POST['enddate'] != "" && $_POST['datetype'] == 'selectdate')
	{
		$campaigncond	.= " AND scheduleddate BETWEEN :date1 AND :date2";

		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$esqlcount['date1']	= $StartDate;
		$esqlcount['date2']	= $EndDate;
	}

	$sqlcount	= "SELECT SUM(totalsent * smscredit) AS C FROM ".$Prefix."campaign WHERE clientid=:clientid ".$campaigncond."";

	$querycount	= pdo_query($sqlcount,$esqlcount);

	$totalsmscreditsusedrow	= pdo_fetch_assoc($querycount);
	$totalsmscreditsused	= $totalsmscreditsusedrow['C'];

	$totalcampaignused		= $totalsmscreditsused;


	if($Num > 0 || $NumCampaign > 0)
	{
		$index	= 0;

		if($Num > 0)
		{
			$GetAllSMSPackage	= GetAllSMSPackages($_POST['clientid']);

			while($rows = pdo_fetch_assoc($Query))
			{
				$isselected	= false;

				$PackageTypeText	= "";

				$id		    	= $rows['id'];
				$packageid		= $rows['packageid'];
				$packagename    = $GetAllSMSPackage[$packageid]["name"];
				$packagecost	= $rows['packagecost'];
				$smscredit		= $rows['smscredits'];
				$createdon	    = $rows['createdon'];
				/*$createdon	= date("d/m/Y",$rows['createdon']);*/
				$status		    = $rows['status'];
				$addedbyadmin   = $rows['addedbyadmin'];
				$reason			= $rows['reason'];
				$type			= $rows['type'];
				$logtype		= $rows['logtype'];
				$packagetype	= $rows['packagetype'];

				if($packagetype > 0)
				{
					$PackageTypeText	= "Trans.";
				}
				else
				{
					$PackageTypeText	= "Promo.";
				}
				if($logtype == 'debit')
				{
					$PackageTypeText	= 'Refund';
					$campaignid			= $rows['campid'];
					$packagename		= $rows['campaignname'];

					if(trim($packagename) == "")
					{
						$packagename	= "SMS Campaign #".$campaignid;
					}
					else
					{
						$packagename	.= " #".$campaignid;
					}
				}

				/*$statustxt    = 'Req. Pending';

				if($status > 0)
				{
					$totalactivecredits += $smscredit; 
					$statustxt = "Recharge";
				}
				else
				{
					$totalpendingrecharge	+= 1;
					$totalsmscreditsinrequest += $smscredit;
				}*/

				if($status > 0)
				{
					if($type > 0)
					{
						if($logtype == 'debit')
						{
							$totalrefunds += $smscredit;
						}
						else
						{
							$totalactivecredits += $smscredit; 
						}
						$statustxt = "Recharge";

						if($addedbyadmin > 0)
						{
							$packagename	= $reason;
							$packagecost	= "0";
						}

						$DateArr[]			= $createdon;
						$ItemNameArr[]		= $packagename;
						$SMSCostArr[]		= $packagecost;
						$SMSCreditArr[]		= $smscredit;
						$SMSDebitArr[]		= "---";
						$SMSStatusArr[]		= $statustxt;
						$SMSStatusArr2[]	= (int)$status;
						$PackageTypeArr[]	= $PackageTypeText;
					}
					else
					{
						$totalsmscreditsused      += $smscredit;

						$statustxt = "Spent";

						if($addedbyadmin > 0)
						{
							$packagename	= $reason;
						}

						$DateArr[]			= $createdon;
						$ItemNameArr[]		= $packagename;
						$SMSCostArr[]		= "---";
						$SMSCreditArr[]		= "---";
						$SMSDebitArr[]		= $smscredit;
						$SMSStatusArr[]		= $statustxt;
						$SMSStatusArr2[]	= 0;
						$PackageTypeArr[]	= "---";
					}
				}
				else
				{
					$totalpendingrecharge	+= 1;
				}

				/*$RecordListArr[$index]['id']			= (int)$id;
				$RecordListArr[$index]['createdon']		= $createdon;
				$RecordListArr[$index]['packagename']	= $packagename;
				$RecordListArr[$index]['packagecost']	= $packagecost;
				$RecordListArr[$index]['smscredits']	= $smscredit;
				$RecordListArr[$index]['status']		= (int)$status;
				$RecordListArr[$index]['statustxt']		= $statustxt;

				$index++;*/
			}
		}

		if($NumCampaign > 0)
		{
			while($RowsCampaign = pdo_fetch_assoc($QueryCampaign))
			{
				$campaignid		= $RowsCampaign['id'];
				$createdon		= $RowsCampaign['createdon'];
				$campaignname	= $RowsCampaign['campaignname'];

				if(trim($campaignname) == "")
				{
					$campaignname	= "SMS Campaign #".$campaignid;
				}
				else
				{
					$campaignname	= "SMS Campaign #".$campaignid." (".$campaignname.")";
				}

				$SqlCount2		= "SELECT SUM(smscredit) AS C FROM ".$Prefix."campaign_history WHERE clientid=:clientid AND issent >:issent AND campid=:campid";
				$EsqlCount2		= array('issent'=>0,'clientid'=>(int)$_POST['clientid'],"campid"=>(int)$campaignid);

				$QueryCount2	= pdo_query($SqlCount2,$EsqlCount2);
				$creditusedrows	= pdo_fetch_assoc($QueryCount2);

				$smscreditsused	= $creditusedrows['C'];

				$DebitText	= "Pending";

				if($smscreditsused > 0)
				{
					$DebitText	= "Spent";
				}
				else
				{
					$smscreditsused	= 0;
				}

				$DateArr[]			= $createdon;
				$ItemNameArr[]		= $campaignname;
				$SMSCostArr[]		= "---";
				$SMSCreditArr[]		= "---";
				$SMSDebitArr[]		= $smscreditsused;
				$SMSStatusArr[]		= "Spent";
				$SMSStatusArr2[]	= 0;
				$PackageTypeArr[]	= "---";
			}
		}

		$paymentcond	= "";

		$EsqlPayment	= array('clientid'=>(int)$_POST['clientid'],"smsmresponse"=>"");

		if($_POST['startdate'] != "" && $_POST['enddate'] != "" && $_POST['datetype'] == 'selectdate')
		{
			$paymentcond	.= " AND paymentdate BETWEEN :date1 AND :date2";

			$StartDate	= strtotime($_POST['startdate']);
			$EndDate	= strtotime($_POST['enddate'])+86399;

			$EsqlPayment['date1']	= $StartDate;
			$EsqlPayment['date2']	= $EndDate;
		}

		$SqlPayment	= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND smsmresponse<>:smsmresponse AND smsmresponse IS NOT NULL ".$paymentcond."";

		$QueryPayment	= pdo_query($SqlPayment,$EsqlPayment);
		$PaymentNum		= pdo_num_rows($QueryPayment);

		$totalsmscreditsused	+= $PaymentNum;

		$totalpaymentused		= $PaymentNum;


		$misccond	= "";

		$EsqlCountMiscLog	= array('clientid'=>(int)$_POST['clientid'],"smsmresponse"=>"");

		if($_POST['startdate'] != "" && $_POST['enddate'] != "" && $_POST['datetype'] == 'selectdate')
		{
			$misccond	.= " AND createdon BETWEEN :date1 AND :date2";

			$StartDate	= strtotime($_POST['startdate']);
			$EndDate	= strtotime($_POST['enddate'])+86399;

			$EsqlCountMiscLog['date1']	= $StartDate;
			$EsqlCountMiscLog['date2']	= $EndDate;
		}

		$SqlCountMiscLog	= "SELECT COUNT(*) AS C FROM ".$Prefix."sms_log WHERE clientid=:clientid AND smsmresponse<>:smsmresponse AND smsmresponse IS NOT NULL ".$misccond."";

		$QueryCountMiscLog	= pdo_query($SqlCountMiscLog,$EsqlCountMiscLog);
		$totalsmscreditsusedlogrow	= pdo_fetch_assoc($QueryCountMiscLog);

		$totalmiscused	= $totalsmscreditsusedlogrow['C'];

		$totalsmscreditsused	+= $totalmiscused;

		$totalsmscreditsavaiable = ((int)$totalactivecredits - (int)$totalsmscreditsused);

		$response['success']			= true;
		$response['hascredit']			= true;
		$response['haspendingcredit']	= false;

        $response['availablecredit']	= $totalsmscreditsavaiable;
        $response['usedcredit']			= $totalsmscreditsused;
        $response['requestedcredit']	= $totalsmscreditsinrequest;

		$totalcreditused	= (int)$totalcampaignused + (int)$totalpaymentused + (int)$totalmiscused;

		$response['totalpurchase']		= number_format($totalactivecredits);
        $response['totalused']			= number_format((int)$totalsmscreditsused);
        $response['totalbalance']		= number_format((int)$totalsmscreditsavaiable);
        $response['totalpending']		= number_format((int)$totalpendingrecharge);
		$response['totalrefunds']		= number_format((int)$totalrefunds);
		$response['totalcampaignused']	= number_format((int)$totalcampaignused);
		$response['totalpaymentused']	= number_format((int)$totalpaymentused);
		$response['totalmiscused']		= number_format((int)$totalmiscused);
		$response['totalcreditused']	= number_format((int)$totalcreditused);

        $response['msg']		= "SMS Credit Log listed successfully.";
	}
	else
	{
		$condition	= " AND deletedon <:deletedon AND status<:status";
		$Esql		= array("deletedon"=>1,"status"=>1);

		if($_POST['clientid'] > 0)
		{
			$condition	.= " AND clientid=:clientid";
			$Esql['clientid']	= (int)$_POST['clientid'];
		}

		$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

		$Query		= pdo_query($Sql,$Esql);
		$Num		= pdo_num_rows($Query);

		if($Num > 0)
		{
			$index	= 0;

			$GetAllSMSPackage	= GetAllSMSPackages($_POST['clientid']);

			while($rows = pdo_fetch_assoc($Query))
			{
				$isselected	= false;

				$id		    	= $rows['id'];
				$packageid		= $rows['packageid'];
				$packagename    = $GetAllSMSPackage[$packageid]["name"];
				$packagecost	= $rows['packagecost'];
				$smscredit		= $rows['smscredits'];
				$createdon		= $rows['createdon'];
				$createdon		= date("d/m/Y",$rows['createdon']);
				$status		    = $rows['status'];
				$statustxt		= 'Req. Pending';
				$type			= $rows['type'];

				$date	= date("d/m/Y",$createdon);

				if(date("Y",$createdon) == date("Y",$time))
				{
					$date	= date("d/m",$createdon);
				}

				$transtime	= date("h:i a",$createdon);

				if($type > 0)
				{
					if($status > 0)
					{
						$totalactivecredits += $smscredit; 
						$statustxt = "Recharge";
					}
					else
					{
						$totalsmscreditsinrequest += $smscredit;  
					}

					$RecordListArr[$index]['serial']		= (int)$index+1;
					$RecordListArr[$index]['id']			= (int)$id;
					$RecordListArr[$index]['date']			= $date;
					$RecordListArr[$index]['time']			= $transtime;
					$RecordListArr[$index]['packagename']	= $packagename;
					$RecordListArr[$index]['packagecost']	= $packagecost;
					$RecordListArr[$index]['smscredits']	= $smscredit;
					$RecordListArr[$index]['status']		= (int)$status;
					$RecordListArr[$index]['statustxt']		= $statustxt;

					$index++;
				}
			}

			$response['success']			= true;
			$response['hascredit']			= false;
			$response['haspendingcredit']	= true;
			$response['msg']				= "Pending SMS Credit Log listed successfully.";
		}
	}

	$response['recordlist']		= $RecordListArr;
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetSMSPackages')
{
	$RecordSetArr		= array();
	$AreaRecordSetArr	= array();

	$AssignedListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch sms packages.";

	$condition	= "";
	$Esql		= array("deletedon"=>1,'status'=>1,"credittype"=>(int)$_POST['credittype']);

	if($_POST['packagetype'] != "")
	{
		$condition	.= " AND packagetype=:packagetype";
		$Esql['packagetype']	= (int)$_POST['packagetype'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."sms_packages WHERE status=:status AND deletedon < :deletedon AND credittype=:credittype ".$condition."";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];
			$name			= $rows['name'];
			$price			= $rows['price'];
			$credits		= $rows['credits'];
			$packagetype	= $rows['packagetype'];

			$packagetext	= "Promotional";

			if($packagetype > 0)
			{
				$packagetext	= "Transactional";

			}

			$RecordSetArr[$index]['id']				= $id;
			/*$RecordSetArr[$index]['name']		= $name." - ".$credits." Credits - Rs. ".number_format($price);*/
			$RecordSetArr[$index]['name']			= $name;
			$RecordSetArr[$index]['credits']		= $credits;
			$RecordSetArr[$index]['credittext']		= number_format($credits);
			$RecordSetArr[$index]['price']			= number_format($price);
			$RecordSetArr[$index]['packagetext']	= $packagetext;

			$index++;
		}

    }
	$PackageType	= "Lead credit";

	if($_POST['credittype'] > 0)
	{
		$PackageType	= "sms credit";
	}

    $BankDetails    = '
    
   Please make payment to the our account and '.$PackageType.' will be added to your account with in 24 hours.<br/><br/>
    -----------------------------------------------
    Bank Name : HDFC <br/>
    Account Name : Woodpecker Technologies Private Limited <br/>
    Acount Number : 09352560001324 <br/>
    IFSC Code : HDFC0000935 <br/> 
    -----------------------------------------------
    <br/><br/>
    
    Thanks';
	$response['recordlist']		= $RecordSetArr;
	$response['bankdetails']	= $BankDetails;

	if(!empty($RecordSetArr) || !empty($AreaRecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Sms packages listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPendingCreditLog")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch pending credit.";

	$RecordListArr	= array();

	$condition	= " AND deletedon <:deletedon AND status<:status";
	$Esql		= array("deletedon"=>1,"status"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);
	$TotalRec	= $Num;

	if($Num > 0)
	{
		$index	= 0;

		$GetAllSMSPackage	= GetAllSMSPackages($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$id		    	= $rows['id'];
			$packageid		= $rows['packageid'];
			$packagename    = $GetAllSMSPackage[$packageid]["name"];
			$packagecost	= $rows['packagecost'];
			$smscredit		= $rows['smscredits'];
			$credittype		= $rows['credittype'];
			$createdon	    = $rows['createdon'];
			$createdon		= date("d/m/Y",$rows['createdon']);
			$status		    = $rows['status'];
			$Paylink	    = $rows['paylink'];
			$Paymentid	    = $rows['razorpayid'];
			$ispaid		    = $rows['ispaid'];
            $statustxt		= 'Req. Pending';
            $type			= $rows['type'];
			if($ispaid > 0)
			{
				$Paylink = '';	
			}
			if($type > 0)
			{
				if($status > 0)
				{
					$totalactivecredits += $smscredit; 
					$statustxt = "Recharge";
				}
				else
				{
					$totalsmscreditsinrequest += $smscredit;  
				}

				$RecordListArr[$index]['serial']		= (int)$index+1;
				$RecordListArr[$index]['id']			= (int)$id;
				$RecordListArr[$index]['createdon']		= $createdon;
				$RecordListArr[$index]['packagename']	= $packagename;
				$RecordListArr[$index]['credittype']	= $credittype;
				$RecordListArr[$index]['packagecost']	= $packagecost;
				$RecordListArr[$index]['smscredits']	= $smscredit;
				$RecordListArr[$index]['status']		= (int)$status;
				$RecordListArr[$index]['paylink']		= $Paylink;
				$RecordListArr[$index]['paymentid']		= $Paymentid;
				$RecordListArr[$index]['statustxt']		= $statustxt;

				$index++;
			}
		}

		$totalsmscreditsavaiable = ((int)$totalactivecredits - (int)$totalsmscreditsused);

		$response['success']	= true;
        $response['availablecredit']	= $totalsmscreditsavaiable;
        $response['usedcredit']			= $totalsmscreditsused;
        $response['requestedcredit']	= $totalsmscreditsinrequest;

		$response['totalpurchase']		= (int)$totalactivecredits;
        $response['totalused']			= (int)$totalsmscreditsused;
        $response['totalbalance']		= (int)$totalsmscreditsavaiable;

        $response['msg']		= "Pending SMS Credit Log listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSMSCredits")
{
	$time	= time();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch available credit.";

	/*$condition	= " AND deletedon <:deletedon AND credittype=:credittype";
	$Esql		= array("deletedon"=>1,"credittype"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);

	$totalactivecredits			= 0;
	$totalsmscreditsused		= 0;
	$totalsmscreditsavaiable	= 0;

	if($Num > 0)
	{
		$sqlcount	= "SELECT SUM(smscredit) AS C FROM ".$Prefix."campaign_history WHERE clientid=:clientid AND issent >:issent ";
		$esqlcount	= array('issent'=>0,'clientid'=>(int)$_POST['clientid']);

		$querycount	= pdo_query($sqlcount,$esqlcount);

		$totalsmscreditsusedrow   = pdo_fetch_assoc($querycount);
		$totalsmscreditsused      = $totalsmscreditsusedrow['C'];
		
		while($rows = pdo_fetch_assoc($Query))
		{
			$smscredit		= $rows['smscredits'];
			$status		    = $rows['status'];
			$type			= $rows['type'];

			if($status > 0)
			{
				if($type > 0)
				{
					$totalactivecredits		+= $smscredit;
				}
				else
				{
					$totalsmscreditsused	+= $smscredit;
				}
			}
		}

		$totalsmscreditsavaiable	= ((int)$totalactivecredits - (int)$totalsmscreditsused);
	}
	*/


	$CreditLogSql	= "SELECT SUM(smscredits) as S,`type`,logtype FROM ".$Prefix."sms_credit_log WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status AND credittype=:credittype GROUP BY type,logtype";
	$CreditLogEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>1,'credittype'=>1);
	
	$CreditQuery	= pdo_query($CreditLogSql,$CreditLogEsql);
	$availableleadcredit	= 0;
	$totalleadcreditsused	= 0;
	while($RowCredit = pdo_fetch_assoc($CreditQuery))
	{
		$Type		 = $RowCredit['type']; 
		$LogType	 = $RowCredit['logtype']; 
		$Total		 = $RowCredit['S'];
		
		if($Type > 0)
		{
			if($LogType == 'debit')
			{
				$totalrefunds += $Total;
			}
			else
			{
				$availableleadcredit += $Total; 
			}
		}
		else
		{
			$totalsmscreditsused      += $Total;
		}
	}	

	$SmsLogSql	= "SELECT SUM(totalsent * smscredit) as S FROM ".$Prefix."campaign WHERE clientid=:clientid ";
	$SmsLogEsql	= array("clientid"=>(int)$_POST['clientid']);
	$SmsQuery	= pdo_query($SmsLogSql,$SmsLogEsql);
	$SmsRow		= pdo_fetch_assoc($SmsQuery);

	$totalsmscreditsused += $SmsRow['S'];

	
	$totalsmscreditsavaiable	= ((int)$availableleadcredit - (int)$totalsmscreditsused);

	$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
	$CheckEsql	= array("id"=>(int)$_POST['clientid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$clientrows	= pdo_fetch_assoc($CheckQuery);

	$clientname	= $clientrows['clientname'];
	$phone1		= $clientrows['phone1'];

	$response['success']		= true;
	$response['hascredit']		= true;
	$response['credits']		= number_format($totalsmscreditsavaiable);
	$response['clientname']		= $clientname;
	$response['clientphone']	= $phone1;

	$response['msg']	= "Available credit fetch successfully.";

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSMSLeadCreditLog")
{
	$time	= time();

	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch lead credit.";

	$condition	= " AND deletedon <:deletedon AND credittype=:credittype";
	$Esql		= array("deletedon"=>1,"credittype"=>0);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	$DateArr		= array();
	$ItemNameArr	= array();
	$SMSCostArr		= array();
	$SMSCreditArr	= array();
	$SMSDebitArr	= array();
	$SMSStatusArr	= array();
	$SMSStatusArr2	= array();

	$Sql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$condition." ORDER BY status DESC, createdon DESC";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);

	$SqlCampaign	= "SELECT * FROM ".$Prefix."campaign WHERE clientid=:clientid";
	$EsqlCampaign	= array('clientid'=>(int)$_POST['clientid']);

	$QueryCampaign	= pdo_query($SqlCampaign,$EsqlCampaign);
	$NumCampaign	= pdo_num_rows($QueryCampaign);

	if($Num > 0 || $NumCampaign > 0)
	{
		$index	= 0;

		if($Num > 0)
		{
			$GetAllSMSPackage		= GetAllSMSPackages($_POST['clientid']);

			$totalsmscreditsavaiable    = 0; 
			$totalsmscreditsinrequest   = 0; 
			$totalsmscreditsused        = 0;
			$totalactivecredits         = 0;
			$totalpendingrecharge		= 0;

			$sqlcount	= "SELECT SUM(smscredit) AS C FROM ".$Prefix."campaign_history WHERE clientid=:clientid AND issent >:issent AND leadcredit > :leadcredit";
			$esqlcount	= array('issent'=>0,'clientid'=>(int)$_POST['clientid'],"leadcredit"=>0);

			$querycount	= pdo_query($sqlcount,$esqlcount);

			$totalsmscreditsusedrow	= pdo_fetch_assoc($querycount);
			$totalsmscreditsused	= $totalsmscreditsusedrow['C'];

			while($rows = pdo_fetch_assoc($Query))
			{
				$isselected	= false;

				$id		    	= $rows['id'];
				$packageid		= $rows['packageid'];
				$packagename    = $GetAllSMSPackage[$packageid]["name"];
				$packagecost	= $rows['packagecost'];
				$smscredit		= $rows['smscredits'];
				$createdon	    = $rows['createdon'];
				$status		    = $rows['status'];
				$addedbyadmin   = $rows['addedbyadmin'];
				$reason			= $rows['reason'];
				$type			= $rows['type'];

				if($status > 0)
				{
					if($type > 0)
					{
						$totalactivecredits += $smscredit; 
						$statustxt = "Recharge";

						if($addedbyadmin > 0)
						{
							$packagename	= $reason;
							$packagecost	= "0";
						}

						$DateArr[]			= $createdon;
						$ItemNameArr[]		= $packagename;
						$SMSCostArr[]		= $packagecost;
						$SMSCreditArr[]		= $smscredit;
						$SMSDebitArr[]		= "---";
						$SMSStatusArr[]		= $statustxt;
						$SMSStatusArr2[]	= (int)$status;
					}
					else
					{
						$totalsmscreditsused      += $smscredit;

						$statustxt = "Spent";

						if($addedbyadmin > 0)
						{
							$packagename	= $reason;
						}

						$DateArr[]			= $createdon;
						$ItemNameArr[]		= $packagename;
						$SMSCostArr[]		= "---";
						$SMSCreditArr[]		= "---";
						$SMSDebitArr[]		= $smscredit;
						$SMSStatusArr[]		= $statustxt;
						$SMSStatusArr2[]	= 0;
					}
				}
				else
				{
					$totalpendingrecharge	+= 1;
				}
			}
		}

		if($NumCampaign > 0)
		{
			while($RowsCampaign = pdo_fetch_assoc($QueryCampaign))
			{
				$campaignid		= $RowsCampaign['id'];
				$createdon		= $RowsCampaign['createdon'];
				$campaignname	= $RowsCampaign['campaignname'];
				$totalsent		= $RowsCampaign['totalsent'];
				$smscredit		= $RowsCampaign['smscredit'];
				if(trim($campaignname) == "")
				{
					$campaignname	= "SMS Campaign #".$campaignid;
				}

				$SqlCount2		= "SELECT SUM(smscredit) AS C FROM ".$Prefix."campaign_history WHERE clientid=:clientid AND issent >:issent AND campid=:campid AND leadcredit > :leadcredit";
				$EsqlCount2		= array('issent'=>0,'clientid'=>(int)$_POST['clientid'],"campid"=>(int)$campaignid,"leadcredit"=>0);

				$QueryCount2	= pdo_query($SqlCount2,$EsqlCount2);
				$creditusedrows	= pdo_fetch_assoc($QueryCount2);

				$smscreditsused	= $creditusedrows['C'];

				$DebitText	= "Pending";

				if($smscreditsused > 0)
				{
					$DebitText	= "Spent";
				}
				else
				{
					$smscreditsused	= 0;
				}

				if($smscreditsused > 0)
				{
					$LastUseSql		= "SELECT leadcreditusedate FROM ".$Prefix."campaign_history WHERE clientid=:clientid AND issent >:issent AND campid=:campid AND leadcredit > :leadcredit ORDER BY id DESC LIMIT 1";
					$LastUseEsql	= array('clientid'=>(int)$_POST['clientid'],'issent'=>0,"campid"=>(int)$campaignid,"leadcredit"=>0);

					$LastUseQuery	= pdo_query($LastUseSql,$LastUseEsql);
					$lastusedrows	= pdo_fetch_assoc($LastUseQuery);

					$leadcreditusedate	= $lastusedrows['leadcreditusedate'];

					$DateArr[]			= $leadcreditusedate;
					$ItemNameArr[]		= $campaignname;
					$SMSCostArr[]		= "---";
					$SMSCreditArr[]		= "---";
					$SMSDebitArr[]		= $smscreditsused;
					$SMSStatusArr[]		= "Spent";
					$SMSStatusArr2[]	= 0;
				}
			}
		}

		array_multisort($DateArr, SORT_ASC, $ItemNameArr, $SMSCostArr, $SMSCreditArr, $SMSDebitArr, $SMSStatusArr, $SMSStatusArr2);
		
		if(!empty($DateArr))
		{
			$Index		= 0;
			$SMSBalance	= 0;

			foreach($DateArr as $key => $value)
			{
				$ItemName		= $ItemNameArr[$key];
				$SMSCost		= $SMSCostArr[$key]; 
				$SMSCredit		= $SMSCreditArr[$key];
				$SMSDebit		= $SMSDebitArr[$key];
				$SMSStatusText	= $SMSStatusArr[$key];
				$SMSStatus		= $SMSStatusArr2[$key];

				if($SMSCredit > 0)
				{
					$SMSBalance	=  $SMSBalance + $SMSCredit; 
				}
				if($SMSDebit > 0)
				{
					$SMSBalance	=  $SMSBalance - $SMSDebit;
				}

				$date	= date("d/m/Y",$value);

				if(date("Y",$value) == date("Y",$time))
				{
					$date	= date("d/m",$value);
				}

				$transtime	= date("h:i a",$value);

				if($Index < 1)
				{
					$RecordListArr[$Index]['serial']		= $Index+1;
					$RecordListArr[$Index]['date']			= $date;
					$RecordListArr[$Index]['time']			= $transtime;
					$RecordListArr[$Index]['item']			= "Opening Balance";
					$RecordListArr[$Index]['smscost']		= "0.00";
					$RecordListArr[$Index]['smscredit']		= "0";
					$RecordListArr[$Index]['smsdebit']		= "0";
					$RecordListArr[$Index]['smsstatus']		= 0;
					$RecordListArr[$Index]['smsstatustxt']	= "---";
					$RecordListArr[$Index]['smsbalance']	= "0";

					$Index++;
				}

				$RecordListArr[$Index]['serial']		= $Index+1;
				$RecordListArr[$Index]['date']			= $date;
				$RecordListArr[$Index]['time']			= $transtime;
				$RecordListArr[$Index]['item']			= $ItemName;
				$RecordListArr[$Index]['smscost']		= number_format($SMSCost);
				$RecordListArr[$Index]['smscredit']		= number_format($SMSCredit);
				$RecordListArr[$Index]['smsdebit']		= number_format($SMSDebit);
				$RecordListArr[$Index]['smsstatus']		= $SMSStatus;
				$RecordListArr[$Index]['smsstatustxt']	= $SMSStatusText;
				$RecordListArr[$Index]['smsbalance']	= number_format($SMSBalance);

				$Index++;
			}
		}

		$totalsmscreditsavaiable = ((int)$totalactivecredits - (int)$totalsmscreditsused);

		$response['success']			= true;
		$response['hascredit']			= true;
		$response['haspendingcredit']	= false;

        $response['availablecredit']	= $totalsmscreditsavaiable;
        $response['usedcredit']			= $totalsmscreditsused;
        $response['requestedcredit']	= $totalsmscreditsinrequest;

		$response['totalpurchase']		= number_format($totalactivecredits);
        $response['totalused']			= number_format((int)$totalsmscreditsused);
        $response['totalbalance']		= number_format($totalsmscreditsavaiable);
        $response['totalpending']		= (int)$totalpendingrecharge;

        $response['msg']		= "SMS Credit Log listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'verifypayments')
{
	/*$sql	= "SELECT COUNT(*) as C FROM ".$Prefix."sms_credit_log WHERE paymentstatus=:paymentstatus AND razorpayid=:razorpayid ";
	$esql   = array("razorpayid"=>$_POST['paymentid'],'paymentstatus'=>'paid');
	
	$query	= pdo_query($sql,$esql);
	$rowcount = pdo_fetch_assoc($query);

	if($rowcount["C"] > 0)
	{
		$response['paymentstatus']	= "paid";
	}
	else
	{
		$response['paymentstatus']	= "";
	}*/

	$sql	= "SELECT COUNT(*) as C FROM ".$Prefix."payment_log
	WHERE (status=:status || status=:status2) AND razorpayinoviceid=:razorpayinoviceid AND paymenttype=:paymenttype ";
	$esql   = array("razorpayinoviceid"=>$_POST['paymentid'],'status'=>'paid','status2'=>'captured',"paymenttype"=>"smscredit");
	
	$query	= pdo_query($sql,$esql);
	$rowcount = pdo_fetch_assoc($query);

	if($rowcount["C"] > 0)
	{
		$response['paymentstatus']	= "paid";
	}
	else
	{
		$response['paymentstatus']	= "";
	}

	$response['success']		= true;
	$json = json_encode($response);
    echo $json;
	die;

}
if($_POST['Mode'] == "AddLeadCredit")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add Lead credit request.";

	if($_POST['packageid'] == "")
	{
		$ErrorMsg	.= "Please select a package.<br>";
	}
    
    if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
	}

	if($haserror == false)
	{
		$ispaid = 0;
		$paymentstatus = 'pending';
		if($_POST['packagecost'] < 0.1)
		{
			$ispaid = 1;
			$paymentstatus = 'paid';
		}
		$Sql	= "INSERT INTO ".$Prefix."sms_credit_log SET 
		clientid		=:clientid,
		packageid		=:packageid,
		smscredits		=:smscredits,
		packagecost		=:packagecost,
		ispaid			=:ispaid,
		paymentstatus	=:paymentstatus,
		createdon		=:createdon,
		credittype		=:credittype";

		$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"packageid"		=>(int)$_POST['packageid'],
			"smscredits"	=>$_POST['smscredits'],
			"packagecost"	=>CleanPriceString($_POST['packagecost']),
			"ispaid"		=>(int)$ispaid,
			"paymentstatus"	=>$paymentstatus,
			"createdon"		=>$createdon,
			"credittype"	=>0
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$paymentlink ='';
			$paymentid	='';
				
			if($ispaid < 1)
			{
				$clientsql		= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
				$clientesql 	= array("id"=>(int)$_POST['clientid']);
				$clientquery	= pdo_query($clientsql,$clientesql);
				$clientrow	= pdo_fetch_assoc($clientquery);
			
				$CustomerArr['name'] = $clientrow['clientname'];
				
				$CustomerArr['email'] = $clientrow['contactemail'];
				$CustomerArr['phone'] = $clientrow['phone1'];

				$Amount		= (float)extract_numbers($_POST['packagecost']);
				
				$Notes		= "Payment for ".$_POST['smscredits']." credits by ".$clientrow['clientname'];

				$paymentlink =	GeneratePaymentLinks_SMSCredit($CustomerArr,$Amount,$Notes,$recordid);
				if($paymentlink !='')
				{
					$sql 	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE id=:id";
					$esql 	= array("id"=>(int)$recordid);
					$query  = pdo_query($sql,$esql);
					$row	= pdo_fetch_assoc($query);
					$paymentid = $row['razorpayid'];
				}
			}
			$response['success']		= true;
			$response['recordid']		= $recordid;
			$response['paymentlink']	= $paymentlink;
			$response['paymentid']		= $paymentid;
			$response['msg']			= "Lead credit request successfully submitted.";
			$response['toastmsg']		= "Lead credit request successfully submitted.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeletePendingCreditRequest")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete campaign, Please try later.";

	$DelSql		= "DELETE FROM ".$Prefix."sms_credit_log WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$DelEsql	= array(
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']	
	);

	$DelQuery	= pdo_query($DelSql,$DelEsql);

	if($DelQuery && !is_array($DelQuery))
	{
		$Response['success']	= true;
		$Response['msg']		= "Pending request deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'getpaymentlink')
{
	$sql	= "SELECT paylink FROM ".$Prefix."payment_log
	WHERE paylink !=:paylink AND razorpayinoviceid=:razorpayinoviceid AND paymenttype=:paymenttype";
	$esql   = array("razorpayinoviceid"=>$_POST['paymentid'],'paylink'=>'',"paymenttype"=>'smscredit');
	
	$query		= pdo_query($sql,$esql);
	$rowcount	= pdo_fetch_assoc($query);

	$response['paymentlink']	= $rowcount['paylink'];

	$response['success']	= true;
	$json = json_encode($response);
    echo $json;
	die;
}
?>