<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
/*set response code - 200 OK*/
http_response_code(200);

include_once "dbconfig.php";
use \Firebase\JWT\JWT;
$createdon	= time();

$AllowedPerm	= array();

if($_POST['Mode'] == "GetOutstandingReport")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch outstanding report.";

	$links		= 5;
	$perpage	= 100;

	$serialindex	= 1;

	if($_POST['perpage'] != '' && $_POST['perpage'] > 0)
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}
	if($_POST['serialindex'] != '')
	{
		$serialindex = $_POST['serialindex'];
	}

	$TotalRec	= 0;

	if($_POST['clientid'] > 0)
	{
		$startdate		= strtotime($_POST['monthyear']);

		$PaymentStartDate	= "";

		if($_POST['usefromdate'] > 0)
		{
			$PaymentStartDate	= strtotime($_POST['paymentstartdate']);
		}

		$enddate		= strtotime($_POST['enddate'])+86399;
		$selectedmonth	= date("m",$startdate);
		$selectedyear	= date("Y",$startdate);

		$Condition		= "";
		$CustESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		if(trim($_POST['monthyear']) != "")
		{
			$StartDate	= strtotime($_POST['monthyear']);
			$EndDate	= strtotime($_POST['enddate'])+86399;
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids = $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND areaid IN (".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustESQL['outstandingbalance']	= 0;

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$TotalRec	= $CustNum;

		if($CustNum > 0)
		{
			$totalpages	= ceil($CustNum/$perpage);
			$offset		= ($_POST['page'] - 1) * $perpage;
			$addquery	= " LIMIT %d, %d";
		}
		else
		{
			$addquery	= "";
		}

		$CustSQL2	= $CustSQL.$addquery;
		$CustSQL2	= sprintf($CustSQL2, intval($offset), intval($perpage));

		if($_POST['viewdetail'] > 0)
		{
			$CustQuery2	= pdo_query($CustSQL2,$CustESQL);
			$CustNum2	= pdo_num_rows($CustQuery2);
		}

		$DetailArr	= array();
		$index		= 0;

		if($CustNum2 > 0 && $_POST['viewdetail'] > 0)
		{
			$GetAllSubLine	= GetAllSubLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery2))
			{
				$id					= $custrows['id'];
				$customerid			= $custrows['customerid'];
				$name				= $custrows['name'];
				$phone				= $custrows['phone'];
				$openingbalance		= $custrows['openingbalance'];
				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$outstandingbalance	= $custrows['outstandingbalance'];
				$sublinename		= $GetAllSubLine[$custrows['sublineid']]['name'];

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

				$name2	= "#".$customerid." ".$name;

				$DetailArr[$index]['id']		= $id;
				$DetailArr[$index]['serialno']	= (($_POST['page'] - 1) * $perpage)+($index+1);
				$DetailArr[$index]['name']		= $name2;
				$DetailArr[$index]['phone']		= $phone;
				$DetailArr[$index]['address']	= $addresstr;
				$DetailArr[$index]['billno']	= $invoiceid;
				$DetailArr[$index]['amount']	= @number_format($outstandingbalance,2);

				$index++;
				$serialindex++;
			}
		}

		$pageListArr	= array();
		$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);

		$totalopeningbalance = GetOutStandingAmount($_POST['clientid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$startdate,'previous');

		$TotalInvoiceArr = GetInvoiceAmount($_POST['clientid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$startdate);

		$CompletePaymentArr	= GetCustomerPayment($_POST['clientid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$PaymentStartDate,$enddate,'all',$_POST['usefromdate']);

		$totalpayments	= $CompletePaymentArr['totalamount'];

		$remaingbalance	= ((float)$TotalInvoiceArr['totalamount'] + (float)$totalopeningbalance) - (float)$totalpayments;
		$graphoutstanding	= $totalopeningbalance + $TotalInvoiceArr['totalamount'];

		$graphpayments	= 0;

		if($remaingbalance > 0 && $graphoutstanding > 0)
		{
			$graphpayments2	= ($remaingbalance / $graphoutstanding) * 100;
			$graphpayments	= (100 - $graphpayments2)/100;
		}

		$recoverydoneprecent	= 0;

		if($totalpayments > 0 && ($TotalInvoiceArr['totalamount']+$totalopeningbalance) > 0)
		{
			$recoverydoneprecent	= ($totalpayments / ((float)$TotalInvoiceArr['totalamount'] + (float)$totalopeningbalance)) * 100;
		}

		$subtotalsummary	= (float)$totalopeningbalance + (float)$TotalInvoiceArr['totalamount'];

		$RecordSet['openingbalance']		= number_format($totalopeningbalance,2);
		$RecordSet['subtotalsummary']		= number_format($subtotalsummary,2);
		$RecordSet['outstandingbalance']	= number_format($remaingbalance,2);
		$RecordSet['totalinvoicebalance']	= number_format($TotalInvoiceArr['totalamount'],2);
		$RecordSet['totalinvoice']			= (int)$TotalInvoiceArr['totalcount'];
		$RecordSet['totalpayments']			= number_format($totalpayments,2);
		$RecordSet['totalpaymentcount']		= (int)$CompletePaymentArr['totalcount'];
		$RecordSet['outstandingdetail']		= $DetailArr;
		$RecordSet['graphpayments']			= $graphpayments;
		$RecordSet['remaingbalance']		= number_format($remaingbalance,2);
		$RecordSet['graphoutstanding']		= number_format($graphoutstanding,2);
		$RecordSet['recoverydoneprecent']	= round($recoverydoneprecent);
		$RecordSet['totalcustomer']			= (int)$TotalRec;

		$pageListArr	= array();
		$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);

		$response['perpage']					= (int)$perpage;
		$response['paginglist']					= $pageListArr;
		$response['showpages']					= false;
		$response['totalpages']					= (int)$totalpages;

		if($totalpages > 1)
		{
			$response['showpages']	= true;
		}

		$response['success']		= true;
		$response['recordset']		= $RecordSet;
		$response['totalrecord']	= $TotalRec;
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetOutstandingReportPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate outstanding report pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName	= "outstanding-report.pdf";

	$StartDate	= "";
	$EndDate	= "";

	$startdate		= strtotime($_POST['monthyear']);
	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$StartDate	= "";

	if($_POST['usefromdate'] > 0)
	{
		$StartDate		= strtotime($_POST['paymentstartdate']);
	}

	if(trim($_POST['monthyear']) != "")
	{
		/*$StartDate	= strtotime($_POST['monthyear']);*/
		$EndDate		= strtotime($_POST['enddate'])+86399;
	}

	/*$File	= "viewoutstandingreport.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&startdate=".$StartDate."&enddate=".$EndDate."&usefromdate=".$_POST['usefromdate']."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewoutstandingreport.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$StartDate."&enddate_strtotime=".$EndDate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "Outstanding report pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCirculationData-till-27-05")
{
	$catindex	= 0;
	$RecordListArr	= array();

	if($_POST['useenddate'] < 1)
	{
		$_POST['enddate']	= $_POST['startdate'];
	}

	$StartDate	= strtotime($_POST['startdate']);
	$CheckDate	= strtotime($_POST['enddate'])+86399;

	$response['success']	= false;
    $response['msg']		= "Unable to fetch circulation report";

	$HolidayArr	= array();

	$Month	= date("m",$StartDate);
	$Year	= date("Y",$StartDate);

	//$ClientInventoryPriceArr	= GetActiveSubscriptionByClientID($_POST['clientid'],$Month,$Year);

	$CurrentInventoryFreqArr	= GetInventoryFrequency();

	$HolidaySQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND customertype<:customertype";
	$HolidayESQL	= array("clientid"=>(int)$_POST['clientid'],"date1"=>$StartDate,"date2"=>$CheckDate,"date3"=>$StartDate,"date4"=>$CheckDate,"deletedon"=>1,'customertype'=>1);

	$HolidayQuery	= pdo_query($HolidaySQL,$HolidayESQL);
	$HolidayNum		= pdo_num_rows($HolidayQuery);

	if($HolidayNum > 0)
	{
		while($HolidayRow = pdo_fetch_assoc($HolidayQuery))
		{
			$HoliStartDate	= $HolidayRow['startdate'];
			$HoliEndDate	= $HolidayRow['enddate'];
			$CustomerType	= $HolidayRow['customertype'];
			$InventoryType	= $HolidayRow['inventorytype'];
			$InventoryID	= $HolidayRow['inventoryid'];
			
			$CalcStartDate = $HoliStartDate;
			
			if($StartDate > $HoliStartDate)
			{	
				$CalcStartDate = $StartDate;
			}

			$CalcEndDate = strtotime(date("m/d/Y",$CheckDate));
			if($HoliEndDate > $CheckDate)
			{
				$CalcEndDate = $HoliEndDate;
			}

			if($CustomerType < 1)
			{
				$AddDate = 0;
				if($InventoryType < 1)
				{
					$AddDate = 1;
				}

				if($AddDate > 0)
				{
					while($CalcStartDate <= $CalcEndDate)
					{
						$HolidayArr[] = $CalcStartDate;

						$CalcStartDate = $CalcStartDate + 86400;
					}
				}
			}
		}
	}

	$Sql	= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND status=:status";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"status"=>1);
	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$InventoryCiculationArr = array();
	$InventoryPurchaseArr 	= array();
	$AllInventoryArr	   	= array();
	$AllInventoryNameArr   	= GetInventoryNames();

	if($Num > 0)
	{
		while($Row = pdo_fetch_assoc($Query))
		{
			$InventoryID	= $Row["inventoryid"];
			
			$InventoryCiculationArr[$InventoryID] = 0;

			$AllInventoryArr[] = $InventoryID;

			/*$CheckESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,'subscriptiondate'=>1,'startdate'=>$StartDate,'enddate'=>$CheckDate,'inventoryid'=>(int)$InventoryID);*/
			
			$CheckESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$CheckCondition	= "";

			if($_POST['lineid'] > 0)
			{
				$CheckCondition	.= " AND cust.lineid=:lineid";
				$CheckESQL['lineid']	= (int)$_POST['lineid'];
			}

			if($_POST['areaid'] > 0)
			{
				$CheckCondition	.= " AND cust.areaid=:areaid";
				$CheckESQL['areaid']	= (int)$_POST['areaid'];
			}

			if($_POST['hawkerid'] > 0)
			{
				$CheckCondition	.= " AND cust.hawkerid=:hawkerid";
				$CheckESQL['hawkerid']	= (int)$_POST['hawkerid'];
			}

			if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
			{
				$areaids = $_POST['areaids'];

				if(trim($areaids) == "")
				{
					$areaids	= "-1";
				}
				$CheckCondition	.= " AND cust.areaid IN (".$areaids.")";
			}
			if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
			{
				$CheckCondition	.= " AND cust.lineid IN(".$lineids.")";
			}

			$CheckSQL	= "SELECT cust.* FROM ".$Prefix."customers cust WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon ".$CheckCondition." GROUP BY cust.id ORDER BY cust.sequence ASC, cust.customerid ASC";

			$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				while($CustRow		= pdo_fetch_assoc($CheckQuery))
				{
					$CustomerID		= $CustRow['id'];
					/*$Qty			= $CustRow['qty'];

					if($Qty < 1)
					{
						$Qty =1;
					}
					
					$IsHoliday		= IsHoliday($CheckDate,$CustomerID,$InventoryID);

					if($IsHoliday < 1)
					{
						$InventoryCiculationArr[$InventoryID] += $Qty;
					}*/

					$Quantity	= getCustomerInventoryQuantityByDateRange($_POST['clientid'], $CustomerID, $InventoryID, $StartDate, $CheckDate, $HolidayArr, $ClientInventoryPriceArr, $CurrentInventoryFreqArr);

					$InventoryCiculationArr[$InventoryID] += $Quantity;
				}
			}
			$CheckCond2	= "";

			$CheckESQL2	= array('inventoryid'=>(int)$InventoryID,"startdate"=>$StartDate,"enddate"=>$CheckDate,"clientid"=>(int)$_POST['clientid']);

			if($_POST['droppingpointid'] > 0)
			{
				$CheckCond2	.= " AND droppingpointid=:droppingpointid";
				$CheckESQL2['droppingpointid']	= (int)$_POST['droppingpointid'];
			}

			$CheckSQL	= "SELECT SUM(noofpices) as qty FROM ".$Prefix."purchase WHERE inventoryid=:inventoryid AND purchasedate BETWEEN :startdate AND :enddate AND clientid=:clientid".$CheckCond2;

			$CheckQuery		= pdo_query($CheckSQL,$CheckESQL2);
			$CheckRow		= pdo_fetch_assoc($CheckQuery);

			$PurchaseQty	= $CheckRow['qty'];
			$InventoryPurchaseArr[$InventoryID] = $PurchaseQty;
		}
	}
	$InventoryStr = '-1';
	if(!empty($AllInventoryArr))
	{
		$InventoryStr = implode(",",$AllInventoryArr);
	}

	$CategorySql	= "SELECT cat.* FROM ".$Prefix."category cat, ".$Prefix."inventory inv WHERE cat.status=:status AND cat.id=inv.categoryid AND inv.id IN (".$InventoryStr.") AND cat.id=:catid GROUP BY cat.id ORDER BY cat.orderby ASC";
	$CategoryEsql	= array("status"=>1,"catid"=>(int)$_POST['catid']);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);
	
	$RecordSet 		= array();

	if($CategoryNum > 0)
	{
		$catindex = 0;
		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];
			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.categoryid=:categoryid AND inv.status=:status ORDER BY inv.name ASC";

			$InventoryEsql	= array("categoryid"=>(int)$catid,"status"=>1);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$RecordSet	= array();
				$index		= 0;

				while($InvRow	= pdo_fetch_assoc($InventoryQuery))
				{
					$TempInvID		= $InvRow['id'];
					$TempInvName	= $InvRow['name'];

					$circulationstr	= '-';
					$purchaseqtystr	= '-';
					$balanceqtystr	= '-';

					if($InventoryCiculationArr[$TempInvID] > 0)
					{
						$circulationstr = (int)$InventoryCiculationArr[$TempInvID];
					}
					if($InventoryPurchaseArr[$TempInvID] > 0)
					{
						$purchaseqtystr = (int)$InventoryPurchaseArr[$TempInvID];
					}
					$balanceqty = (int)($InventoryPurchaseArr[$TempInvID] -  $InventoryCiculationArr[$TempInvID]);
					if($balanceqty > 0)
					{
						$balanceqtystr = (int)$balanceqty;
					}

					if($InventoryCiculationArr[$TempInvID] > 0 || $InventoryPurchaseArr[$TempInvID] > 0 || $balanceqty > 0)
					{
						$RecordSet[$index]['id']			= $TempInvID;
						$RecordSet[$index]['name'] 			= $AllInventoryNameArr[$TempInvID]['name'] ;
						$RecordSet[$index]['circulation']	= "".$circulationstr."" ;
						$RecordSet[$index]['purchase']		= "".$purchaseqtystr."" ;
						$RecordSet[$index]['balance']		= "".$balanceqtystr."";
						
						$index++;
					}
				}
			}
			$RecordListArr[$catindex]['id']			= (int)$catid;
			$RecordListArr[$catindex]['title']		= $cattitle;
			$RecordListArr[$catindex]['recordlist']	= $RecordSet;

			$catindex++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Circulation Report listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCirculationData")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$catid	= $_POST['catid'];

	if($_POST['useenddate'] < 1)
	{
		$_POST['enddate']	= $_POST['startdate'];
	}

	$StartDate	= strtotime($_POST['startdate']);
	$EndDate	= strtotime($_POST['startdate'])+86399;

	$response['success']	= false;
    $response['msg']		= "Unable to fetch circulation report";

	$ExtArg = '';
	$ExtArgArr = array();
	if(@$_POST['droppingpointid'] !='')
	{
		$ExtArg = ' AND id=:id ';
		$ExtArgArr = array('id'=>(int)$_POST['droppingpointid']);
	}

	$sql_drop	= "SELECT * FROM ".$Prefix."dropping_point WHERE deletedon < :deletedon AND status =:status AND clientid=:clientid $ExtArg";
	$esql_drop	= array("status"=>1,"deletedon"=>1,"clientid"=>(int)$_POST['clientid']);
	$TempArr	= array_merge($ExtArgArr,$esql_drop);

	$query_drop = pdo_query($sql_drop,$TempArr);
	$num_drop	= pdo_num_rows($query_drop);

	$DropingPointArr_Circulation = array();

	if($num_drop > 0)
	{
		$index = 0;
		while($row_drop = pdo_fetch_assoc($query_drop))
		{
			$DropingPointID		=	$row_drop['id'];
			$DropingPointName	=	$row_drop['name'];

			$sql_area	= "SELECT GROUP_CONCAT(id) as areaids FROM ".$Prefix."area WHERE droppingpointid=:droppingpointid AND clientid=:clientid AND deletedon < :deletedon";
			$esql_area	= array("droppingpointid"=>(int)$DropingPointID,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
			$query_area	= pdo_query($sql_area,$esql_area);
			$row_area	= pdo_fetch_assoc($query_area);
			$AreaIDs	= $row_area['areaids'];
			
			if($AreaIDs == '')
			{
				$AreaIDs = '-1';
			}

			$sql = "SELECT * FROM orlop_customers WHERE clientid=:clientid AND areaid IN (".$AreaIDs.") AND deletedon < :deletedon order by id DESC LIMIT 10000";

			$esql = array("clientid"=>$_POST['clientid'],"deletedon"=>1);

			$query = pdo_query($sql,$esql);

			$TotalQty = 0;

			$TempArr = array();
			while($row = pdo_fetch_assoc($query))
			{
				$customerid = $row['id'];
				$QtyArr = getCustomerSubscriptionLog($_POST['clientid'],$customerid,$StartDate,$catid);
				
				foreach($QtyArr as $key => $InvData)
				{
					if($catid != $InvData['categoryid'])
					{
						continue;
					}
					$TempArr[$key]['name'] = $InvData['inventoryname'];
					$TempArr[$key]['purchase'] = 0;
					$TempArr[$key]['categoryname'] = $InvData['categoryname'];
					$TempArr[$key]['circulation'] += $InvData['qty'];

					$TotalQty +=  $InvData['qty'];
				}
			}
			
			
			$sql_purchase	= "SELECT * FROM ".$Prefix."purchase WHERE clientid=:clientid AND droppingpointid=:droppingpointid AND purchasedate BETWEEN :date1 AND :date2";
			$esql_purchase	= array("date1"=>$StartDate,"date2"=>$EndDate,"clientid"=>(int)$_POST['clientid'],"droppingpointid"=>(int)$DropingPointID);
			$query_purchase	= pdo_query($sql_purchase,$esql_purchase);
			$num_purchase	= pdo_num_rows($query_purchase);
			
			$TotalPurchase = 0;
			if($num_purchase > 0)
			{
				while($row_purchase	= pdo_fetch_assoc($query_purchase))
				{
					$InventoryID	= $row_purchase['inventoryid'];
					$Purchase		= $row_purchase['noofpices'];
					$TempArr[$InventoryID]['purchase'] += $Purchase;

					$TotalPurchase	+= 	$Purchase;
				}
			
			
			}
			
			foreach($TempArr as $key => $Data)
			{
				$TempQty		= $Data['circulation'];
				$TempPurchase	= $Data['purchase'];
				$TempBalance	= $TempPurchase - $TempQty;
				
				$TempArr[$key]['balance'] = $TempBalance;
			}

			$TempArr = array_values($TempArr);
			$DropingPointArr_Circulation[$index]['balance']			= (int)$TotalPurchase - (int)$TotalQty;
			$DropingPointArr_Circulation[$index]['circulation']		= (int)$TotalQty;
			$DropingPointArr_Circulation[$index]['droppingpointid']		= (int)$DropingPointID;
			$DropingPointArr_Circulation[$index]['droppingpointname']	= $DropingPointName;
			$DropingPointArr_Circulation[$index]['inventorydata']		= $TempArr;
		$index++;
		}

	}



	if(!empty($DropingPointArr_Circulation))
	{
		$response['success']	= true;
		$response['data']		= $DropingPointArr_Circulation;
		$response['msg']		= "Circulation Report listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetCirculationDataPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate circulation pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "circulation-report-detail.pdf";

	$startdate	= strtotime('today');
	$enddate	= strtotime($_POST['circulationdate']);

	/*$File	= "viewcirculationdetail.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&startdate=".$startdate."&enddate=".$enddate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewcirculationdetail.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$startdate."&enddate_strtotime=".$enddate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "Monthly circulation pdf successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetBillStatementByAreaLineReport")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch bill area line statement report.";

	$RecordListArr		= array();

	if($_POST['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"deletedon2"=>1);

		/*$month_last_day	= date('t',$startdate);*/

		$startdate		= strtotime($_POST['monthyear']);
		$selectedmonth	= (int)date("m",$startdate);
		$selectedyear	= date("Y",$startdate);

		if(trim($_POST['monthyear']) != "")
		{
			$Condition	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

			$CustESQL['invoicemonth']	= $selectedmonth;
			$CustESQL['invoiceyear']	= $selectedyear;
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND customer.lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND customer.areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids = $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND customer.areaid IN (".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND customer.lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT customer.*,invoice.invoiceid,invoice.finalamount FROM ".$Prefix."customers customer,".$Prefix."invoices invoice WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=invoice.customerid AND invoice.deletedon <:deletedon2 ".$Condition." ORDER BY customer.sequence ASC, customer.customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$TotalRec	= $CustNum;

		if($CustNum > 0)
		{
			$AllAreaArr	= GetAllArea($_POST['clientid']);
			$AllLineArr	= GetAllLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$id				= $custrows['id'];
				$areaid			= $custrows['areaid'];
				$lineid			= $custrows['lineid'];
				$customerid		= $custrows['customerid'];
				$finalamount	= $custrows['finalamount'];
				$invoiceid		= $custrows['invoiceid'];

				$areaname		= $AllAreaArr[$areaid]['name'];
				$linename		= $AllLineArr[$lineid]['name'];

				if($areaid < 1)
				{
					$areaname	= "Unnamed";
				}

				$RecordListArr[$areaid]['name']				= $areaname;
				
				$RecordListArr[$areaid]['invoiceamount']	+= $finalamount;
				$RecordListArr[$areaid]['invoicecount']		+= 1;

				$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

				$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= $finalamount;
				$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 1;
			}
		}

		$AreaDetail	= array();

		/*$TotalRec	= 0;*/
		$index		= 0;

		if(!empty($RecordListArr))
		{
			$areaindex	= 0;
			foreach($RecordListArr as $areaid => $areadata)
			{
				$index	= 0;
				$Detail	= array();

				if(!empty($areadata['detail']))
				{
					foreach($areadata['detail'] as $lineid=>$detailrows)
					{
						$Detail[$index]['serialno']			= $index+1;
						$Detail[$index]['id']				= $lineid;
						$Detail[$index]['name']				= $detailrows['name'];

						$Detail[$index]['invoiceamount']	= number_format($detailrows['invoiceamount'],2);
						$Detail[$index]['invoicecount']		= $detailrows['invoicecount'];

						/*$TotalRec++;*/
						$index++;
					}
				}

				if(!empty($Detail))
				{
					$AreaDetail[$areaindex]['areaid']		= $areaid;
					$AreaDetail[$areaindex]['name']			= $areadata['name'];

					$AreaDetail[$areaindex]['invoiceamount']= number_format($areadata['invoiceamount'],2);
					$AreaDetail[$areaindex]['invoicecount']	= $areadata['invoicecount'];
					
					$AreaDetail[$areaindex]['details']	= $Detail;

					$areaindex++;
				}
			}
		}

		if(!empty($AreaDetail))
		{
			$response['success']		= true;
			$response['msg']			= "Bill statement area line report fetched successfully.";
			$response['recordset']		= $AreaDetail;
			$response['totalrecord']	= $TotalRec;
		}

		if($TotalRec < 1)
		{
			$response['success']	= false;
			$response['msg']		= "Unable to fetched bill area line statement report.";
		}
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetBillStatementReport")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch bill statement report.";

	$links		= 5;
	$perpage	= 100;

	$serialindex	= 1;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}
	if($_POST['serialindex'] != '')
	{
		$serialindex = $_POST['serialindex'];
	}

	$CustomerIDArr		= array();
	$CustomerNameArr	= array();

	if($_POST['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"deletedon2"=>1);

		/*$month_last_day	= date('t',$startdate);*/

		$startdate		= strtotime($_POST['monthyear']);
		$selectedmonth	= (int)date("m",$startdate);
		$selectedyear	= date("Y",$startdate);

		$curr_month_last_day	= date('t',$startdate);
		$curr_month_checkdate	= strtotime(date($selectedyear.'-'.$selectedmonth.'-'.$curr_month_last_day));

		$tempenddate		= date('Y-m-01', strtotime('+1 month', $startdate));
		$month_last_day		= date('t',strtotime($tempenddate));
		$next_month_enddate	= strtotime(date('Y-m-'.$month_last_day, strtotime($tempenddate)));

		if(trim($_POST['monthyear']) != "")
		{
			/*$StartDate	= strtotime($_POST['startdate']);
			$EndDate	= strtotime($_POST['enddate'])+86399;

			$Condition	.= " AND invoice.invoicedate BETWEEN :startdate AND :enddate";

			$CustESQL['startdate']	= $StartDate;
			$CustESQL['enddate']	= $EndDate;*/

			$Condition	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

			$CustESQL['invoicemonth']	= $selectedmonth;
			$CustESQL['invoiceyear']	= $selectedyear;
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND customer.lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND customer.areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids = $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND customer.areaid IN (".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND customer.lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT customer.*,invoice.invoiceid,invoice.finalamount FROM ".$Prefix."customers customer,".$Prefix."invoices invoice WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=invoice.customerid AND invoice.deletedon <:deletedon2 ".$Condition." ORDER BY invoice.invoiceid ASC, customer.sequence ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$TotalRec	= $CustNum;

		if($CustNum > 0)
		{
			$totalpages	= ceil($CustNum/$perpage);
			$offset		= ($_POST['page'] - 1) * $perpage;
			$addquery	= " LIMIT %d, %d";
		}
		else
		{
			$addquery	= "";
		}

		$CustSQL2	= $CustSQL.$addquery;
		$CustSQL2	= sprintf($CustSQL2, intval($offset), intval($perpage));

		$CustQuery2	= pdo_query($CustSQL2,$CustESQL);
		$CustNum2	= pdo_num_rows($CustQuery2);

		$DetailArr	= array();
		$index		= 0;

		if($CustNum2 > 0)
		{
			$AllSubLine	= GetAllSubLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery2))
			{
				$id					= $custrows['id'];
				$customerid			= $custrows['customerid'];
				$name				= $custrows['name'];
				$phone				= $custrows['phone'];
				$finalamount		= $custrows['finalamount'];
				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$invoiceid			= $custrows['invoiceid'];
				$sublinename		= $AllSubLine[$custrows['sublineid']]['name'];

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

				$name2	= "#".$customerid." ".$name;

				$PreviousBalance	= GetCustomerOutStanding($id,$phone,$curr_month_checkdate,'previous');
				$totaldue			= GetCustomerOutStanding($id,$phone,$next_month_enddate,'current');

				$DetailArr[$index]['id']				= $id;
				$DetailArr[$index]['serialno']			= (int)$serialindex;
				$DetailArr[$index]['name']				= $name2;
				$DetailArr[$index]['phone']				= $phone;
				$DetailArr[$index]['address']			= $addresstr;
				$DetailArr[$index]['billno']			= $invoiceid;
				$DetailArr[$index]['billamount']		= @number_format($finalamount,2);
				$DetailArr[$index]['previousbalance']	= @number_format($PreviousBalance,2);
				$DetailArr[$index]['totaldue']			= @number_format($totaldue,2);

				$index++;
				$serialindex++;
			}
		}

		$pageListArr	= array();
		$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);

		$RecordSet['detail']		= $DetailArr;
		$response['perpage']		= (int)$perpage;
		$response['paginglist']		= $pageListArr;
		$response['showpages']		= false;
		$response['totalrecord']	= $TotalRec;
		$response['totalpages']		= (int)$totalpages;
		$response['serialindex']	= (int)$serialindex;
		$response['success']		= true;
		$response['msg']			= "Bill statement report fetched successfully.";
		$response['recordset']		= $RecordSet;

		if($TotalRec < 1)
		{
			$response['success']	= false;
			$response['msg']		= "Unable to fetched bill statement report.";
		}

		if($totalpages > 1)
		{
			$response['showpages']	= true;
		}
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetBillStatementReportPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate bill statement report pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "bill-statement-report.pdf";

	$startdate		= strtotime($_POST['monthyear']);
	$selectedmonth	= (int)date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$curr_month_last_day	= date('t',$startdate);
	$curr_month_checkdate	= strtotime(date($selectedyear.'-'.$selectedmonth.'-'.$curr_month_last_day));

	$tempenddate		= date('Y-m-01', strtotime('+1 month', $startdate));
	$month_last_day		= date('t',strtotime($tempenddate));
	$next_month_enddate	= strtotime(date('Y-m-'.$month_last_day, strtotime($tempenddate)));

	/*if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;
	}*/

	/*$File	= "viewbillstatementreport.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&selectedmonth=".$selectedmonth."&selectedyear=".$selectedyear."&startdate=".$curr_month_checkdate."&enddate=".$next_month_enddate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewbillstatementreport.php?bulkprinting=1&downloadpdf=1&selectedmonth=".$selectedmonth."&selectedyear=".$selectedyear."&startdate_strtotime=".$curr_month_checkdate."&enddate_strtotime=".$next_month_enddate."&".$FilterDataStr;

	if($_POST['isfulldownload'] < 1)
	{
		$IsCreated	= CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");
	}
	else
	{
		$IsCreated	= true;
	}

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		if($_POST['isfulldownload'] < 1)
		{
			$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		}
		else
		{
			$Response['pdffilepath']	= $ServerAPIURL.$File;
		}
		$Response['msg']			= "bill statement report pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerOutstanding")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer outstanding report.";

	$links		= 5;
	$perpage	= 100;

	$serialindex	= 1;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}
	if($_POST['serialindex'] != '')
	{
		$serialindex = $_POST['serialindex'];
	}

	$CustomerIDArr		= array();
	$CustomerNameArr	= array();

	if($_POST['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"outstandingbalance"=>0);

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids	= $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND areaid IN(".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$CustSQL3	= "SELECT SUM(outstandingbalance) AS outstandingbalance FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery3	= pdo_query($CustSQL3,$CustESQL);
		$CustRows3	= pdo_fetch_assoc($CustQuery3);

		$totaloutstandingbalance	= $CustRows3['outstandingbalance'];

		$TotalRec	= $CustNum;

		if($CustNum > 0)
		{
			$totalpages	= ceil($CustNum/$perpage);
			$offset		= ($_POST['page'] - 1) * $perpage;
			$addquery	= " LIMIT %d, %d";
		}
		else
		{
			$addquery	= "";
		}

		$CustSQL2	= $CustSQL.$addquery;
		$CustSQL2	= sprintf($CustSQL2, intval($offset), intval($perpage));

		$CustQuery2	= pdo_query($CustSQL2,$CustESQL);
		$CustNum2	= pdo_num_rows($CustQuery2);

		$DetailArr	= array();
		$index		= 0;

		if($CustNum2 > 0)
		{
			$AllSubLine	= GetAllSubLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery2))
			{
				$id					= $custrows['id'];
				$customerid			= $custrows['customerid'];
				$name				= $custrows['name'];
				$phone				= $custrows['phone'];
				$openingbalance		= $custrows['openingbalance'];
				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$areaid				= $custrows['areaid'];
				$lineid				= $custrows['lineid'];
				$outstandingbalance	= $custrows['outstandingbalance'];
				$sublinename		= $AllSubLine[$custrows['sublineid']]['name'];

				$SubscriptionStatusArr	= GetCustomerStatusBySubscription($id);

				$hassubscription	= $SubscriptionStatusArr['hassubscription'];
				$blockcolor			= $SubscriptionStatusArr['blockcolor'];
				$statusclass		= $SubscriptionStatusArr['statusclass'];

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

				$name2	= "#".$customerid." ".$name;

				$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon <:deletedon AND customerid=:customerid AND ispaid < :ispaid ORDER BY id DESC LIMIT 1";
				$InvoiceEsql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,"customerid"=>(int)$id,"ispaid"=>1);

				$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
				$InvoiceNum		= pdo_num_rows($InvoiceQuery);

				$invoiceid		= "---";
				$hasinvoiceid	= false;

				if($InvoiceNum > 0)
				{
					$invoicerows	= pdo_fetch_assoc($InvoiceQuery);
					$billno			= $invoicerows['invoiceid'];
					$invoiceid		= $invoicerows['id'];
					$hasinvoiceid	= true;
				}

				$DetailArr[$index]['id']				= $id;
				$DetailArr[$index]['serialno']			= (int)$serialindex;
				$DetailArr[$index]['name']				= $name2;
				$DetailArr[$index]['phone']				= $phone;
				$DetailArr[$index]['address']			= $addresstr;
				$DetailArr[$index]['hasinvoiceid']		= $hasinvoiceid;
				$DetailArr[$index]['billno']			= $billno;
				$DetailArr[$index]['invoiceid']			= $invoiceid;
				$DetailArr[$index]['hassubscription']	= $hassubscription;
				$DetailArr[$index]['blockcolor']		= $blockcolor;
				$DetailArr[$index]['statusclass']		= $statusclass;
				$DetailArr[$index]['areaid']			= (int)$areaid;
				$DetailArr[$index]['lineid']			= (int)$lineid;
				$DetailArr[$index]['amount']			= @number_format($outstandingbalance,2);

				$index++;
				$serialindex++;
			}
		}

		$pageListArr	= array();
		$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);

		$RecordSet['detail']					= $DetailArr;
		$response['perpage']					= (int)$perpage;
		$response['paginglist']					= $pageListArr;
		$response['showpages']					= false;
		$response['totalrecord']				= $TotalRec;
		$response['totalpages']					= (int)$totalpages;
		$response['totaloutstandingbalance']	= @number_format($totaloutstandingbalance,2);
		$response['serialindex']				= (int)$serialindex;
		$response['success']					= true;
		$response['msg']						= "Customer outstanding report fetched successfully.";
		$response['recordset']					= $RecordSet;

		if($totalpages > 1)
		{
			$response['showpages']	= true;
		}
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerOutstandingPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to bill statement pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "customer-outstanding-report.pdf";

	/*$File	= "viewcustomeroutstanding.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewcustomeroutstanding.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "customer outstanding pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPaymentRegisterData_bak")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch payment register";

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=payment.customerid AND payment.deletedon < :paymentdeletedon ".$Condition." GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllLineArr	= GetAllLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$lineid				= $rows['lineid'];
			$linename			= $AllLineArr[$lineid]['name'];
			$paidamount			= $rows['paidamount'];
			$paymentdate		= $rows['paymentdate'];
			$customerpaymentid	= $rows['customerpaymentid'];

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "---";
			}

			$name2	= "#".$customerid." ".$name;

			$RecordListArr[$paymentdate]['name']			= date("d-M-Y",$paymentdate);
			$RecordListArr[$paymentdate]['totalpayment']	+= $paidamount;
		}
	}

	$totalpayment	= 0;
	if(!empty($RecordListArr))
	{
		$lineindex	= 0;
		$lineDetail	= array();

		foreach($RecordListArr as $paymentdate => $linedata)
		{
			if($linedata['totalpayment'] > 0)
			{
				$lineDetail[$lineindex]['paymentdate']	= $paymentdate;
				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['name']			= $linedata['name'];
				$lineDetail[$lineindex]['totalpayment']	= @number_format($linedata['totalpayment'],2);

				$totalpayment	+= $linedata['totalpayment'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}

	if(!empty($lineDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Payment register listed successfully.";
	}
	$response['paymentlist']	= $lineDetail;
	$response['totalrecord']	= $TotalRec;
	$response['totalpayment']	= @number_format($totalpayment,2);

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPaymentRegisterData")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch payment register";

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['paymenttype'] == 'manual')
	{
		$Condition				.= " AND payment.receipttype<>:receipttype AND payment.receipttype IS NOT NULL";
		$ESQL['receipttype']	= "Online";
	}
	else if($_POST['paymenttype'] == 'automatic')
	{
		$Condition				.= " AND payment.receipttype=:receipttype";
		$ESQL['receipttype']	= "Online";
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.discount AS paymentdiscount,payment.coupon AS paymentcoupon,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=payment.customerid AND payment.deletedon < :paymentdeletedon ".$Condition." GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllLineArr	= GetAllLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$lineid				= $rows['lineid'];
			$linename			= $AllLineArr[$lineid]['name'];
			$paymentdate		= $rows['paymentdate'];
			$customerpaymentid	= $rows['customerpaymentid'];
			$paidamount			= (float)$rows['paidamount'];

			$amount				= $paidamount;
			$discount			= (float)$rows['paymentdiscount'];
			$coupon				= (float)$rows['paymentcoupon'];

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "---";
			}

			$name2	= "#".$customerid." ".$name;

			$redabledate	= date("d-M-Y",$paymentdate);

			$RecordListArr[$redabledate]['name']			= $redabledate;
			$RecordListArr[$redabledate]['totalpayment']	+= $paidamount;

			$RecordListArr[$redabledate]['amount']			+= $amount;
			$RecordListArr[$redabledate]['discount']		+= $discount;
			$RecordListArr[$redabledate]['coupon']			+= $coupon;
		}
	}

	$totalpayment	= 0;

	$totalamount	= 0;
	$totaldiscount	= 0;
	$totalcoupon	= 0;

	$lineindex	= 0;
	$lineDetail	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $paymentdate => $linedata)
		{
			if($linedata['totalpayment'] > 0 || $linedata['amount'] > 0 || $linedata['discount'] > 0 || $linedata['coupon'] > 0)
			{
				$paymentdate	= strtotime($paymentdate);

				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['paymentday']	= date("l",$paymentdate);
				$lineDetail[$lineindex]['paymentdate']	= $paymentdate;
				$lineDetail[$lineindex]['name']			= $linedata['name'];
				$lineDetail[$lineindex]['totalpayment']	= @number_format($linedata['totalpayment'],2);

				$lineDetail[$lineindex]['amount']	= @number_format($linedata['amount'],2);
				$lineDetail[$lineindex]['discount']	= @number_format($linedata['discount'],2);
				$lineDetail[$lineindex]['coupon']	= @number_format($linedata['coupon'],2);

				$totalpayment	+= $linedata['totalpayment'];

				$totalamount	+= $linedata['amount'];
				$totaldiscount	+= $linedata['discount'];
				$totalcoupon	+= $linedata['coupon'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}

	$summarytotal	= [];

	$summarytotal['totalamount']	= @number_format($totalamount,2);
	$summarytotal['totaldiscount']	= @number_format($totaldiscount,2);
	$summarytotal['totalcoupon']	= @number_format($totalcoupon,2);

	if(!empty($lineDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Payment register listed successfully.";
	}
	$response['paymentlist']	= $lineDetail;
	$response['totalrecord']	= $TotalRec;
	$response['totalpayment']	= @number_format($totalpayment,2);

	$response['summarytotal']	= $summarytotal;

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPaymentRegisterDetailData")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch payment register";

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	if(trim($_POST['paymentdate']) != "")
	{
		$Condition	.= " AND payment.paymentdate=:paymentdate";
		$ESQL['paymentdate']	= $_POST['paymentdate'];
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=payment.customerid ".$Condition." AND payment.deletedon < :paymentdeletedon GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";
	
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		$AllLineArr	= GetAllLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$lineid				= $rows['lineid'];
			$linename			= $AllLineArr[$lineid]['name'];
			$paidamount			= $rows['paidamount'];
			$paymentdate		= $rows['paymentdate'];
			$customerpaymentid	= $rows['customerpaymentid'];

			$SubscriptionStatusArr	= GetCustomerStatusBySubscription($id);

			$hassubscription	= $SubscriptionStatusArr['hassubscription'];
			$blockcolor			= $SubscriptionStatusArr['blockcolor'];
			$statusclass		= $SubscriptionStatusArr['statusclass'];

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "---";
			}

			$name2	= "#".$customerid." ".$name;

			$RecordListArr[$index]['serialno']			= $index+1;
			$RecordListArr[$index]['id']				= $id;
			$RecordListArr[$index]['customerid']		= $customerid;
			$RecordListArr[$index]['name']				= $name2;
			$RecordListArr[$index]['paidamount']		= $paidamount;
			$RecordListArr[$index]['paymentdate']		= date("d-M-Y",$paymentdate);
			$RecordListArr[$index]['paymentid']			= $customerpaymentid;
			$RecordListArr[$index]['hassubscription']	= $hassubscription;
			$RecordListArr[$index]['statusclass']		= $statusclass;
			$RecordListArr[$index]['blockcolor']		= $blockcolor;

			$index++;
		}
	}

	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Payment register listed successfully.";
	}
	$response['recordset']		= $RecordListArr;
	$response['totalrecord']	= $Num;
	$response['paymentdate']	= date("d-M-Y",$_POST['paymentdate']);

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPaymentRegisterDetailDataByArea")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch payment register";

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	$_POST['startdate']	= date("d-m-Y",$_POST['paymentdate']);
	$_POST['enddate']	= date("d-m-Y",$_POST['paymentdate']);

	/*if(trim($_POST['paymentdate']) != "")
	{
		$Condition	.= " AND payment.paymentdate=:paymentdate";
		$ESQL['paymentdate']	= $_POST['paymentdate'];
	}*/

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['paymenttype'] == 'manual')
	{
		$Condition				.= " AND payment.receipttype<>:receipttype AND payment.receipttype IS NOT NULL";
		$ESQL['receipttype']	= "Online";
	}
	else if($_POST['paymenttype'] == 'automatic')
	{
		$Condition				.= " AND payment.receipttype=:receipttype";
		$ESQL['receipttype']	= "Online";
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	/*$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=payment.customerid ".$Condition." AND payment.deletedon < :paymentdeletedon GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";*/


	$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.discount AS paymentdiscount,payment.coupon AS paymentcoupon,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=payment.customerid AND payment.deletedon < :paymentdeletedon ".$Condition." GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";
	
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	$AreaListArr	= array();

	if($Num > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		/*$AllLineArr	= GetAllLine($_POST['clientid']);*/

		while($rows = pdo_fetch_assoc($Query))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$areaid				= $rows['areaid'];
			$areaname			= $AllAreaArr[$areaid]['name'];
			/*$lineid			= $rows['lineid'];
			$linename			= $AllLineArr[$lineid]['name'];*/
			$paidamount			= $rows['paidamount'];

			$amount				= $paidamount;
			$discount			= (float)$rows['paymentdiscount'];
			$coupon				= (float)$rows['paymentcoupon'];

			$paymentdate		= $rows['paymentdate'];
			$customerpaymentid	= $rows['customerpaymentid'];

			/*$SubscriptionStatusArr	= GetCustomerStatusBySubscription($id);

			$hassubscription	= $SubscriptionStatusArr['hassubscription'];
			$blockcolor			= $SubscriptionStatusArr['blockcolor'];
			$statusclass		= $SubscriptionStatusArr['statusclass'];

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "---";
			}

			$name2	= "#".$customerid." ".$name;

			$RecordListArr[$index]['serialno']			= $index+1;
			$RecordListArr[$index]['id']				= $id;
			$RecordListArr[$index]['customerid']		= $customerid;
			$RecordListArr[$index]['name']				= $name2;
			$RecordListArr[$index]['paidamount']		= $paidamount;
			$RecordListArr[$index]['paymentdate']		= date("d-M-Y",$paymentdate);
			$RecordListArr[$index]['paymentid']			= $customerpaymentid;
			$RecordListArr[$index]['hassubscription']	= $hassubscription;
			$RecordListArr[$index]['statusclass']		= $statusclass;
			$RecordListArr[$index]['blockcolor']		= $blockcolor;
			$AreaListArr[$areaid]['totalpayment']		+= (float)$paidamount;*/

			$AreaListArr[$areaid]['name']		= $areaname;
			$AreaListArr[$areaid]['paidamount']	+= (float)$paidamount;
			$AreaListArr[$areaid]['discount']	+= $discount;
			$AreaListArr[$areaid]['coupon']		+= $coupon;
		}
	}

	$RecordListArr	= array();
	$index			= 0;

	$summarytotal['totalamount']	= 0;
	$summarytotal['totalcoupon']	= 0;
	$summarytotal['totaldiscount']	= 0;

	if(!empty($AreaListArr))
	{
		foreach($AreaListArr as $areaid=>$areapaymentrow)
		{
			$paidamount	= $areapaymentrow['paidamount'];
			$discount	= $areapaymentrow['discount'];
			$coupon		= $areapaymentrow['coupon'];

			if($paidamount > 0 || $discount > 0 || $coupon > 0)
			{
				$RecordListArr[$index]['serialno']		= $index+1;
				$RecordListArr[$index]['areaid']		= (int)$areaid;
				$RecordListArr[$index]['name']			= $areapaymentrow['name'];
				$RecordListArr[$index]['amount']		= @number_format($paidamount,2);
				$RecordListArr[$index]['discount']		= @number_format($discount,2);
				$RecordListArr[$index]['coupon']		= @number_format($coupon,2);

				$summarytotal['totalamount']	+= $paidamount;
				$summarytotal['totaldiscount']	+= $discount;
				$summarytotal['totalcoupon']	+= $coupon;

				$index++;
			}
		}
	}

	$summarytotal['totalamount']	= @number_format($summarytotal['totalamount'],2);
	$summarytotal['totaldiscount']	= @number_format($summarytotal['totaldiscount'],2);
	$summarytotal['totalcoupon']	= @number_format($summarytotal['totalcoupon'],2);

	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Payment register listed successfully.";
	}
	$response['recordset']		= $RecordListArr;
	$response['totalrecord']	= $Num;
	$response['summarytotal']	= $summarytotal;
	$response['paymentdate']	= date("d-m-Y",$_POST['paymentdate']);
	$response['paymentday']		= date("l",$_POST['paymentdate']);

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetPaymentRegisterDataPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to create payment register pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "payment-register-summary.pdf";

	$StartDate	= "";
	$EndDate	= "";

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;
	}

	/*$File	= "viewpaymentregister.php?bulkprinting=1&downloadpdf=1&startdate=".$StartDate."&enddate=".$EndDate."&".$FilterDataStr;*/

	$File	= "viewpaymentregistersummary.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr."&startdate_strtotime=".$StartDate."&enddate_strtotime=".$EndDate;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "payment register pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetPaymentRegisterDetailDataByAreaPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to create payment register pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "payment-register-by-area.pdf";

	$paymentdate	= "";

	if(trim($_POST['paymentdate']) != "")
	{
		$paymentdate	= $_POST['paymentdate'];
	}

	/*$File	= "viewpaymentregister.php?bulkprinting=1&downloadpdf=1&startdate=".$StartDate."&enddate=".$EndDate."&".$FilterDataStr;*/

	$File	= "viewpaymentregisterbyarea.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr."&paymentdate_strtotime=".$paymentdate;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "payment register by area wise pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetBillPrintableData')
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch printable data.";

	$IsBillNumber 	= $_POST['isbillnumber'];
	$IsDateFilter 	= $_POST['isdatefilter'];
	$BillStartFrom 	= $_POST['billnumberfrom'];
	$BillEndTo 		= $_POST['billnumberto'];
	$ClientID 		= $_POST['clientid'];
	$LineID 		= $_POST['lineid'];
	$LinemanID 		= $_POST['linemanid'];
	$HawkerID 		= $_POST['hawkerid'];
	$AreaID 		= $_POST['areaid'];

	$ExtArg 	= "";
	$EsqlArr 	= array();
	
	if($IsDateFilter == true)
	{
		$BillStartDate 	= strtotime($_POST['billstartdate']);
		$BillEndDate 	= strtotime($_POST['billenddate']);

		$ExtArg 	.= " AND (inv.invoicedate BETWEEN :startdate AND :enddate)";
	
		$EsqlArr['startdate'] = $BillStartDate;
		$EsqlArr['enddate'] = $BillEndDate;
	}

	if($IsBillNumber == true)
	{
		if($BillStartFrom > 0 && $BillEndTo > 0)
		{
			$ExtArg 	.= " AND (inv.invoiceid BETWEEN :startnumber AND :endnumber)";
			$EsqlArr['startnumber'] = $BillStartFrom;
			$EsqlArr['endnumber'] = $BillEndTo;
		}
		else if($BillStartFrom > 0 && $BillEndTo < 1)
		{
			$ExtArg 	.= " AND invoiceid >= :startnumber";
			$EsqlArr['startnumber'] = $BillStartFrom;
		}
		else if($BillEndTo > 0 && $BillStartFrom < 1)
		{
			$ExtArg 	.= " AND (inv.invoiceid <= :endnumber)";
			$EsqlArr['endnumber'] = $BillEndTo;
		}
	}
	if($LineID > 0)
	{
		$ExtArg 	.= " AND (cus.lineid = :lineid)";
		$EsqlArr['lineid'] = $LineID;
	}
	if($LinemanID > 0)
	{
		$ExtArg 	.= " AND (cus.linemanid = :linemanid)";
		$EsqlArr['linemanid'] = $LinemanID;
	}
	if($HawkerID > 0)
	{
		$ExtArg 	.= " AND (cus.hawkerid = :hawkerid)";
		$EsqlArr['hawkerid'] = $HawkerID;
	}
	if($AreaID > 0)
	{
		$ExtArg 	.= " AND (cus.areaid = :areaid)";
		$EsqlArr['areaid'] = $AreaID;
	}

	if($_POST['issingledatefilter'] == 1)
	{
		$billprintingdatestart	= strtotime($_POST['billprintingdate']);
		$billprintingdateend	= $billprintingdatestart+86399;

		$ExtArg 	.= " AND (inv.invoicedate BETWEEN :billprintingdatestart AND :billprintingdateend)";
	
		$EsqlArr['billprintingdatestart']	= $billprintingdatestart;
		$EsqlArr['billprintingdateend']		= $billprintingdateend;
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$ExtArg	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$ExtArg	.= " AND lineid IN(".$lineids.")";
	}

	$SQL = "SELECT COUNT(*) AS C FROM ".$Prefix."customers cus,".$Prefix."invoices inv WHERE cus.id=inv.customerid AND inv.clientid=cus.clientid AND cus.clientid=:clientid AND cus.deletedon < :deletedon AND cus.canprintinvoice=:canprintinvoice AND inv.deletedon <:deletedon2 $ExtArg";

	$TotalCustomerSQL = "SELECT COUNT(*) AS C FROM ".$Prefix."customers cus,".$Prefix."invoices inv WHERE cus.id=inv.customerid AND inv.clientid=cus.clientid AND cus.clientid=:clientid AND cus.deletedon < :deletedon AND inv.deletedon <:deletedon2 $ExtArg";

	$EsqlArr['clientid']		= $ClientID;
	$EsqlArr['deletedon']		= 1;
	$EsqlArr['deletedon2']		= 1;

	$TotalCustomerEsql	= $EsqlArr;

	$EsqlArr['canprintinvoice']	= 1;

	$Query = pdo_query($SQL,$EsqlArr);
	$Row   = pdo_fetch_assoc($Query);

	$TotalCount = $Row['C'];

	$TotalCustomerQuery	= pdo_query($TotalCustomerSQL,$TotalCustomerEsql);
	$TotalCustomerRow	= pdo_fetch_assoc($TotalCustomerQuery);

	$TotalCustomerCount	= $TotalCustomerRow['C'];

	$totalcountnotprinted	= $TotalCustomerCount - $TotalCount;

	$response['success']	= true;

    $response['billinvoicedata']['totalcount']				= (int)$TotalCount;
    $response['billinvoicedata']['totalcustomers']			= (int)$TotalCustomerCount;
    $response['billinvoicedata']['totalcountnotprinted']	= (int)$totalcountnotprinted;

	$response['msg']		= "Printable bills.";

	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetBillStatementSummary")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch bill statement summary";

	$AllAreaArr	= GetAllArea($_POST['clientid']);
	$AllLineArr	= GetAllLine($_POST['clientid']);

	$_POST['usefromdate']	= 0;

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$PrevCondition	= "";
	$PrevESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	$NextMonthCondition	= "";
	$NextMonthESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	$BillCondition	= "";
	$BillESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$InvoiceCond	= "";
	$InvoiceEsql	= array("clientid2"=>(int)$_POST['clientid'],'deletedon'=>1,"deletedon2"=>1,"ispaid"=>1);

	$PayCond	= "";
	$PayEsql	= array("clientid"=>(int)$_POST['clientid'],"clientid2"=>(int)$_POST['clientid'],"deletedon2"=>1,"paymentdeletedon"=>1);

	$startdate		= strtotime($_POST['monthyear']);
	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$curr_month_last_day	= date('t',$startdate);
	$curr_month_checkdate	= strtotime(date($selectedyear.'-'.$selectedmonth.'-'.$curr_month_last_day));

	if(trim($_POST['monthyear']) != "")
	{
		/*$StartDate	= strtotime($_POST['startdate']);*/

		$StartDate	= strtotime($_POST['monthyear']);
		/*$EndDate	= $selectedyear."-".$selectedmonth."-".date('t',$StartDate);
		$EndDate	= strtotime($EndDate)+86399;*/
		$EndDate	= strtotime($_POST['enddate'])+86399;

		if($_POST['usefromdate'] > 0)
		{
			$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
			$NextMonthCondition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
		}
		else
		{
			/*$Condition	.= " AND payment.paymentdate <=:enddate";*/
			$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
			$NextMonthCondition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

			$PayCond	.= " AND payment.paymentdate <=:enddate";
		}

		/*$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
		$BillCondition	.= " AND invoice.invoicedate BETWEEN :startdate AND :enddate";*/

		$BillCondition	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

		$BillESQL['invoicemonth']	= $selectedmonth;
		$BillESQL['invoiceyear']	= $selectedyear;

		/*$InvoiceCond	.= " AND invoice.invoicedate<=:invoicedate2";
		
		$InvoiceEsql['invoicedate2']	= $startdate-1; //Commented by vk to improve previous month logic
		
		*/

		$InvoiceCond	.= ' AND UNIX_TIMESTAMP(CONCAT(invoiceyear,"-",invoicemonth,"-01")) < :invoicedate2 ';
		$InvoiceEsql['invoicedate2'] = $StartDate;

		$PayEsql['enddate']	= $startdate-86400;

		if($_POST['usefromdate'] > 0)
		{
			$PaymentStartDate	= strtotime($_POST['paymentstartdate']);

			$ESQL['startdate']	= $PaymentStartDate;
			$ESQL['enddate']	= $curr_month_checkdate+86399;

			$NextMonthESQL['startdate']	= $curr_month_checkdate+86400;
			$NextMonthESQL['enddate']	= $EndDate;
		}
		else
		{
			$ESQL['startdate']	= $startdate;
			$ESQL['enddate']	= $curr_month_checkdate+86399;

			$NextMonthESQL['startdate']	= $curr_month_checkdate+86400;
			$NextMonthESQL['enddate']	= $EndDate;
		}

		/*$BillESQL['startdate']	= $StartDate;
		$BillESQL['enddate']	= $EndDate;*/
	}

	if($_POST['areaid'] > 0)
	{
		$PrevCondition		.= " AND areaid=:areaid";
		$PrevESQL['areaid']	= (int)$_POST['areaid'];

		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];

		$NextMonthCondition	.= " AND cust.areaid=:areaid";
		$NextMonthESQL['areaid']	= (int)$_POST['areaid'];

		$BillCondition	.= " AND cust.areaid=:areaid";
		$BillESQL['areaid']	= (int)$_POST['areaid'];

		$InvoiceCond	.= " AND cust.areaid=:areaid";
		$InvoiceEsql['areaid']	= (int)$_POST['areaid'];

		$PayCond	.= " AND cust.areaid=:areaid";
		$PayEsql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$PrevCondition		.= " AND lineid=:lineid";
		$PrevESQL['lineid']	= (int)$_POST['lineid'];

		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];

		$NextMonthCondition	.= " AND cust.lineid=:lineid";
		$NextMonthESQL['lineid']	= (int)$_POST['lineid'];

		$BillCondition	.= " AND cust.lineid=:lineid";
		$BillESQL['lineid']	= (int)$_POST['lineid'];

		$InvoiceCond	.= " AND cust.lineid=:lineid";
		$InvoiceEsql['lineid']	= (int)$_POST['lineid'];

		$PayCond	.= " AND cust.lineid=:lineid";
		$PayEsql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$PrevCondition		.= " AND hawkerid=:hawkerid";
		$PrevESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$NextMonthCondition	.= " AND cust.hawkerid=:hawkerid";
		$NextMonthESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$BillCondition	.= " AND cust.hawkerid=:hawkerid";
		$BillESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$InvoiceCond	.= " AND cust.hawkerid=:hawkerid";
		$InvoiceEsql['hawkerid']	= (int)$_POST['hawkerid'];

		$PayCond	.= " AND cust.hawkerid=:hawkerid";
		$PayEsql['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$PrevCondition	.= " AND areaid IN (".$areaids.")";

		$Condition		.= " AND cust.areaid IN (".$areaids.")";
		
		$NextMonthCondition		.= " AND cust.areaid IN (".$areaids.")";

		$BillCondition	.= " AND cust.areaid IN (".$areaids.")";

		$InvoiceCond	.= " AND cust.areaid IN (".$areaids.")";

		$PayCond	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$PrevCondition	.= " AND lineid IN(".$lineids.")";

		$Condition		.= " AND cust.lineid IN(".$lineids.")";

		$NextMonthCondition		.= " AND cust.lineid IN(".$lineids.")";

		$BillCondition	.= " AND cust.lineid IN(".$lineids.")";

		$InvoiceCond	.= " AND cust.lineid IN(".$lineids.")";

		$PayCond	.= " AND cust.lineid IN(".$lineids.")";
	}

	$PrevSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$PrevCondition."";

	$PrevQuery	= pdo_query($PrevSQL,$PrevESQL);
	$PrevNum	= pdo_num_rows($PrevQuery);

	if($PrevNum > 0)
	{
		while($prevrows = pdo_fetch_assoc($PrevQuery))
		{
			$id				= $prevrows['id'];
			$phone			= $prevrows['phone'];

			$areaid			= $prevrows['areaid'];
			$lineid			= $prevrows['lineid'];

			$openingbalance	= $prevrows['openingbalance'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			//$PreviousBalance	= GetCustomerOutStanding($id,$phone,$curr_month_checkdate,'previous');

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$RecordListArr[$areaid]['name']				= $areaname;

			$RecordListArr[$areaid]['invoiceamount']	+= 0;
			$RecordListArr[$areaid]['invoicecount']		+= 0;

			$RecordListArr[$areaid]['totalcash']		+= 0;
			$RecordListArr[$areaid]['cashcount']		+= 0;

			$RecordListArr[$areaid]['totaldiscount']	+= 0;
			$RecordListArr[$areaid]['discountcount']	+= 0;

			$RecordListArr[$areaid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['cashcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['discountcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalremaining']	+= 0;
			$RecordListArr[$areaid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['openingbalance']	+= $openingbalance;
			$RecordListArr[$areaid]['previousbalance']	+= $PreviousBalance;

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcountnextmonth']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcountnextmonth']	+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= $openingbalance;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= $PreviousBalance;
		}
	}

	$BillSQL	= "SELECT cust.*,invoice.finalamount AS invoiceamount,invoice.invoicedate AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoices invoice WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.deletedon < :deletedon2 AND cust.id=invoice.customerid ".$BillCondition." GROUP BY invoice.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$BillESQL['deletedon2']	= 1;

	$BillQuery	= pdo_query($BillSQL,$BillESQL);
	$BillNum	= pdo_num_rows($BillQuery);

	if($BillNum > 0)
	{
		while($billrows = pdo_fetch_assoc($BillQuery))
		{
			$id				= $billrows['id'];
			$customerid		= $billrows['customerid'];
			$name			= $billrows['name'];
			$areaid			= $billrows['areaid'];
			$lineid			= $billrows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$invoicedate2		= $billrows['invoicedate'];
			$invoiceamount		= $billrows['invoiceamount'];

			$RecordListArr[$areaid]['name']				= $areaname;
			
			$RecordListArr[$areaid]['invoiceamount']	+= $invoiceamount;
			$RecordListArr[$areaid]['invoicecount']		+= 1;
			
			$RecordListArr[$areaid]['totalcash']		+= 0;
			$RecordListArr[$areaid]['cashcount']		+= 0;

			$RecordListArr[$areaid]['totaldiscount']	+= 0;
			$RecordListArr[$areaid]['discountcount']	+= 0;
			
			$RecordListArr[$areaid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['couponcount']		+= 0;

			$RecordListArr[$areaid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['cashcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['discountcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalremaining']	+= $invoiceamount;
			$RecordListArr[$areaid]['remainingcount']	+= 1;

			$RecordListArr[$areaid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['previousbalance']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= $invoiceamount;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 1;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcountnextmonth']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcountnextmonth']	+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	+= $invoiceamount;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 1;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= 0;
		}
	}

	$BillMonthPaymentSQL	= "SELECT cust.*,payment.amount AS paidamount,payment.discount AS paymentdiscount,payment.coupon AS paymentcoupon,payment.createdon AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$Condition." GROUP BY payment.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$BillMonthPaymentQuery	= pdo_query($BillMonthPaymentSQL,$ESQL);
	$BillMonthPaymentNum	= pdo_num_rows($BillMonthPaymentQuery);

	if($BillMonthPaymentNum > 0)
	{
		while($rows = pdo_fetch_assoc($BillMonthPaymentQuery))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$customerpaymentid	= $rows['customerpaymentid'];
			$areaid				= $rows['areaid'];
			$lineid				= $rows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$paymentdate		= $rows['paymentdate'];
			$paidamount			= $rows['paidamount'];
			$paymentdiscount	= $rows['paymentdiscount'];
			$paymentcoupon		= $rows['paymentcoupon'];

			$totalpayment		= ($paidamount + $paymentdiscount + $paymentcoupon);

			$cashcount	= 0;
			if($paidamount > 0)
			{
				$cashcount	= 1;
			}

			$discountcount	= 0;
			if($paymentdiscount > 0)
			{
				$discountcount	= 1;
			}

			$couponcount	= 0;
			if($paymentcoupon > 0)
			{
				$couponcount	= 1;
			}

			$paymentcount	= 0;
			if($totalpayment > 0)
			{
				$paymentcount	= 1;
			}

			$RecordListArr[$areaid]['name']				= $areaname;

			$RecordListArr[$areaid]['invoiceamount']	+= 0;
			$RecordListArr[$areaid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['totalcash']		+= $paidamount;
			$RecordListArr[$areaid]['cashcount']		+= $cashcount;
			
			$RecordListArr[$areaid]['totaldiscount']	+= $paymentdiscount;
			$RecordListArr[$areaid]['discountcount']	+= $discountcount;
			
			$RecordListArr[$areaid]['totalcoupon']		+= $paymentcoupon;
			$RecordListArr[$areaid]['couponcount']		+= $couponcount;

			$RecordListArr[$areaid]['totalpayment']		+= $totalpayment;
			$RecordListArr[$areaid]['paymentcount']		+= $paymentcount;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['remainingcount']	+= 0;
			
			$RecordListArr[$areaid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['remainingcount']	-= 1;
			}

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= $paidamount;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= $cashcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= $paymentdiscount;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= $discountcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= $paymentcoupon;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= $couponcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= $paymentcount;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']	+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['detail'][$lineid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	-= 1;
			}
		}
	}

	$NextMonthPaymentSQL	= "SELECT cust.*,payment.amount AS paidamount,payment.discount AS paymentdiscount,payment.coupon AS paymentcoupon,payment.createdon AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$NextMonthCondition." GROUP BY payment.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$NextMonthPaymentQuery	= pdo_query($NextMonthPaymentSQL,$NextMonthESQL);
	$NextMonthPaymentNum	= pdo_num_rows($NextMonthPaymentQuery);

	if($NextMonthPaymentNum > 0)
	{
		while($nextmonthpaymentrows = pdo_fetch_assoc($NextMonthPaymentQuery))
		{
			$id					= $nextmonthpaymentrows['id'];
			$customerid			= $nextmonthpaymentrows['customerid'];
			$name				= $nextmonthpaymentrows['name'];
			$customerpaymentid	= $nextmonthpaymentrows['customerpaymentid'];
			$areaid				= $nextmonthpaymentrows['areaid'];
			$lineid				= $nextmonthpaymentrows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$paymentdate		= $nextmonthpaymentrows['paymentdate'];
			$paidamount			= $nextmonthpaymentrows['paidamount'];
			$paymentdiscount	= $nextmonthpaymentrows['paymentdiscount'];
			$paymentcoupon		= $nextmonthpaymentrows['paymentcoupon'];

			$totalpayment		= ($paidamount + $paymentdiscount + $paymentcoupon);

			$cashcount	= 0;
			if($paidamount > 0)
			{
				$cashcount	= 1;
			}

			$discountcount	= 0;
			if($paymentdiscount > 0)
			{
				$discountcount	= 1;
			}

			$couponcount	= 0;
			if($paymentcoupon > 0)
			{
				$couponcount	= 1;
			}

			$paymentcount	= 0;
			if($totalpayment > 0)
			{
				$paymentcount	= 1;
			}

			$RecordListArr[$areaid]['name']				= $areaname;

			$RecordListArr[$areaid]['invoiceamount']	+= 0;
			$RecordListArr[$areaid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['totalcash']		+= 0;
			$RecordListArr[$areaid]['cashcount']		+= 0;
			
			$RecordListArr[$areaid]['totaldiscount']	+= 0;
			$RecordListArr[$areaid]['discountcount']	+= 0;
			
			$RecordListArr[$areaid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['couponcount']		+= 0;

			$RecordListArr[$areaid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['totalcashnextmonth']		+= $paidamount;
			$RecordListArr[$areaid]['cashcountnextmonth']		+= $cashcount;

			$RecordListArr[$areaid]['totaldiscountnextmonth']	+= $paymentdiscount;
			$RecordListArr[$areaid]['discountcountnextmonth']	+= $discountcount;

			$RecordListArr[$areaid]['totalcouponnextmonth']		+= $paymentcoupon;
			$RecordListArr[$areaid]['couponcountnextmonth']		+= $couponcount;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= $totalpayment;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= $paymentcount;

			$RecordListArr[$areaid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['remainingcount']	+= 0;
			
			$RecordListArr[$areaid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['remainingcount']	-= 1;
			}

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalcashnextmonth']		+= $paidamount;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcountnextmonth']		+= $cashcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscountnextmonth']	+= $paymentdiscount;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcountnextmonth']	+= $discountcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcouponnextmonth']		+= $paymentcoupon;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcountnextmonth']		+= $couponcount;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']		+= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']		+= $paymentcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['detail'][$lineid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	-= 1;
			}
		}
	}

	$AreaDetail	= array();

	if(!empty($RecordListArr))
	{
		$areaindex	= 0;
		foreach($RecordListArr as $areaid => $areadata)
		{
			$index	= 0;
			$Detail	= array();

			$areaprevbalance	= 0;

			if(!empty($areadata['detail']))
			{
				foreach($areadata['detail'] as $lineid=>$detailrows)
				{
					$lineprevbalance	= 0;

					$InvoiceSql		= "SELECT SUM(invoice.finalamount) AS totalfinalamount FROM ".$Prefix."invoices invoice,".$Prefix."customers cust WHERE cust.id=invoice.customerid AND invoice.deletedon <:deletedon AND invoice.ispaid < :ispaid AND cust.clientid=:clientid2 AND cust.deletedon < :deletedon2 AND cust.lineid=:lineid2".$InvoiceCond;

					$TempInvoiceEsql	= array("lineid2"=>(int)$lineid);
					
					$InvoiceEsql2	= array_merge($TempInvoiceEsql,$InvoiceEsql);
					
					$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql2);
					$InvoiceRows	= pdo_fetch_assoc($InvoiceQuery);

					$tillprevmonthinvoiceamount	= $InvoiceRows['totalfinalamount'];

					$PrevPaySQL		= "SELECT COUNT(payment.id) as totalpaymentcount,SUM(payment.amount) AS paidamount, SUM(payment.discount) AS discount, SUM(payment.coupon) AS coupon FROM ".$Prefix."customer_payments payment,".$Prefix."customers cust WHERE payment.clientid=:clientid AND cust.clientid=:clientid2 AND cust.deletedon <:deletedon2 AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid AND cust.lineid=:lineid2".$PayCond;

					$TempPrevPayEsql	= array("lineid2"=>(int)$lineid);
					$PrevPayEsql2		= array_merge($TempPrevPayEsql,$PayEsql);

					$PrevPayQuery	= pdo_query($PrevPaySQL,$PrevPayEsql2);
					$PrevPayRows	= pdo_fetch_assoc($PrevPayQuery);

					$tillprevpaidamount	= $PrevPayRows['paidamount'];
					$tillprevdiscount	= $PrevPayRows['discount'];
					$tillprevcoupon		= $PrevPayRows['coupon'];

					$tillprevtotalpayments	= (float)$tillprevpaidamount+(float)$tillprevdiscount+(float)$tillprevcoupon;
					
					$lineprevbalance	= ($tillprevmonthinvoiceamount + $detailrows['openingbalance']) - $tillprevtotalpayments;

					$areaprevbalance	+= $lineprevbalance;


					$Detail[$index]['serialno']			= $index+1;
					$Detail[$index]['id']				= $lineid;
					$Detail[$index]['name']				= $detailrows['name'];

					$Detail[$index]['invoiceamount']	= number_format($detailrows['invoiceamount']);
					$Detail[$index]['invoicecount']		= $detailrows['invoicecount'];

					$Detail[$index]['totalcash']		= number_format($detailrows['totalcash']);
					$Detail[$index]['cashcount']		= $detailrows['cashcount'];

					$Detail[$index]['totaldiscount']	= number_format($detailrows['totaldiscount']);
					$Detail[$index]['discountcount']	= $detailrows['discountcount'];

					$Detail[$index]['totalcoupon']		= number_format($detailrows['totalcoupon']);
					$Detail[$index]['couponcount']		= $detailrows['couponcount'];

					$Detail[$index]['totalpayment']		= number_format($detailrows['totalpayment']);
					$Detail[$index]['paymentcount']		= $detailrows['paymentcount'];

					$Detail[$index]['totalcashnextmonth']		= number_format($detailrows['totalcashnextmonth']);
					$Detail[$index]['cashcountnextmonth']		= $detailrows['cashcountnextmonth'];

					$Detail[$index]['totaldiscountnextmonth']	= number_format($detailrows['totaldiscountnextmonth']);
					$Detail[$index]['discountcountnextmonth']	= $detailrows['discountcountnextmonth'];

					$Detail[$index]['totalcouponnextmonth']		= number_format($detailrows['totalcouponnextmonth']);
					$Detail[$index]['couponcountnextmonth']		= $detailrows['couponcountnextmonth'];

					$Detail[$index]['totalpaymentnextmonth']	= number_format($detailrows['totalpaymentnextmonth']);
					$Detail[$index]['paymentcountnextmonth']	= $detailrows['paymentcountnextmonth'];

					$Detail[$index]['openingbalance']	= number_format($detailrows['openingbalance']);
					/*$Detail[$index]['previousbalance']	= number_format($detailrows['previousbalance'],2);*/

					$Detail[$index]['previousbalance']	= number_format($lineprevbalance);

					/*$previousdue	= $detailrows['previousbalance'] - $detailrows['totalpayment'];*/
					$previousdue	= $lineprevbalance - $detailrows['totalpayment'];

					$Detail[$index]['previousdue']	= number_format($previousdue);

					/*$subtotal	= $detailrows['openingbalance'] + $detailrows['invoiceamount'];*/
					/*$subtotal	= ($detailrows['previousbalance'] + $detailrows['invoiceamount']) - $detailrows['totalpayment'];*/
					$subtotal	= ($lineprevbalance + $detailrows['invoiceamount']) - $detailrows['totalpayment'];

					$Detail[$index]['subtotal']	= @number_format($subtotal);

					$paymentpercentage	= 0;
					/*if($detailrows['totalpayment'] > 0 && $detailrows['invoiceamount'] > 0)
					{
						$paymentpercentage	= ($detailrows['totalpayment']/$detailrows['invoiceamount'])*100;
					}*/

					if($detailrows['totalpaymentnextmonth'] > 0 && $subtotal > 0)
					{
						$paymentpercentage	= ($detailrows['totalpaymentnextmonth']/$subtotal)*100;
					}

					$Detail[$index]['paymentpercentage']	= round($paymentpercentage);

					/*$Detail[$index]['totalremaining']	= number_format($detailrows['totalremaining'],2);*/

					$totalremaining	= $subtotal - $detailrows['totalpaymentnextmonth'];
					
					$Detail[$index]['totalremaining']	= number_format($totalremaining);
					$Detail[$index]['remainingcount']	= $detailrows['remainingcount'];

					$remainingpercentage	= 0;
					/*if($detailrows['totalremaining'] > 0 && $detailrows['invoiceamount'] > 0)*/
					if(($totalremaining) > 0 && $subtotal > 0)
					{
						/*$remainingpercentage	= ($detailrows['totalremaining']/$detailrows['invoiceamount'])*100;*/
						$remainingpercentage	= (($subtotal - $detailrows['totalpaymentnextmonth'])/$subtotal)*100;
					}

					$Detail[$index]['remainingpercentage']	= round($remainingpercentage);

					$TotalRec++;
					$index++;
				}
			}

			if(!empty($Detail))
			{
				$AreaDetail[$areaindex]['areaid']		= $areaid;
				$AreaDetail[$areaindex]['name']			= $areadata['name'];

				$AreaDetail[$areaindex]['invoiceamount']= number_format($areadata['invoiceamount']);
				$AreaDetail[$areaindex]['invoicecount']	= $areadata['invoicecount'];
				
				$AreaDetail[$areaindex]['totalcash']	= number_format($areadata['totalcash']);
				$AreaDetail[$areaindex]['cashcount']	= $areadata['cashcount'];
				
				$AreaDetail[$areaindex]['totaldiscount']	= number_format($areadata['totaldiscount']);
				$AreaDetail[$areaindex]['discountcount']	= $areadata['discountcount'];
				
				$AreaDetail[$areaindex]['totalcoupon']	= number_format($areadata['totalcoupon']);
				$AreaDetail[$areaindex]['couponcount']	= $areadata['couponcount'];

				$AreaDetail[$areaindex]['totalpayment']	= number_format($areadata['totalpayment']);
				$AreaDetail[$areaindex]['paymentcount']	= $areadata['paymentcount'];




				$AreaDetail[$areaindex]['totalcashnextmonth']	= number_format($areadata['totalcashnextmonth']);
				$AreaDetail[$areaindex]['cashcountnextmonth']	= $areadata['cashcountnextmonth'];
				
				$AreaDetail[$areaindex]['totaldiscountnextmonth']	= number_format($areadata['totaldiscountnextmonth']);
				$AreaDetail[$areaindex]['discountcountnextmonth']	= $areadata['discountcountnextmonth'];
				
				$AreaDetail[$areaindex]['totalcouponnextmonth']	= number_format($areadata['totalcouponnextmonth']);
				$AreaDetail[$areaindex]['couponcountnextmonth']	= $areadata['couponcountnextmonth'];

				$AreaDetail[$areaindex]['totalpaymentnextmonth']	= number_format($areadata['totalpaymentnextmonth']);
				$AreaDetail[$areaindex]['paymentcountnextmonth']	= $areadata['paymentcountnextmonth'];

			
				$AreaDetail[$areaindex]['openingbalance']= number_format($areadata['openingbalance']);
				/*$AreaDetail[$areaindex]['previousbalance']= number_format($areadata['previousbalance'],2);*/
				$AreaDetail[$areaindex]['previousbalance']= number_format($areaprevbalance);

				/*$previousdue	= $areadata['previousbalance'] - $areadata['totalpayment'];*/
				$previousdue	= $areaprevbalance - $areadata['totalpayment'];

				$AreaDetail[$areaindex]['previousdue']= number_format($previousdue);

				/*$areasubtotal	= $areadata['openingbalance'] + $areadata['invoiceamount'];*/
				
				$areasubtotal	= ($areaprevbalance + $areadata['invoiceamount']) - $areadata['totalpayment'];

				$totalremaining	= $areasubtotal - $areadata['totalpaymentnextmonth'];

				$AreaDetail[$areaindex]['totalremaining']= number_format($totalremaining);

				$remainingpercentage	= "0";
				if($totalremaining > 0 && $areasubtotal > 0)
				{
					$remainingpercentage	= ($totalremaining / $areasubtotal)*100;
				}

				$AreaDetail[$areaindex]['remainingpercentage']= round($remainingpercentage);

				$paymentpercentage	= "0";
				/*if($areadata['totalpayment'] > 0 && $areadata['invoiceamount'] > 0)
				{
					$paymentpercentage	= ($areadata['totalpayment']/$areadata['invoiceamount'])*100;
				}*/

				if($areasubtotal > 0 && $areadata['totalpaymentnextmonth'] > 0)
				{
					$paymentpercentage	= ($areadata['totalpaymentnextmonth']/$areasubtotal)*100;
				}


				$AreaDetail[$areaindex]['remainingcount']= $areadata['remainingcount'];

				$AreaDetail[$areaindex]['paymentpercentage']	= "".round($paymentpercentage)."";

				$AreaDetail[$areaindex]['subtotal']		= @number_format($areasubtotal);
				$AreaDetail[$areaindex]['monthname']	= $MonthArr[(int)$selectedmonth];

				$AreaDetail[$areaindex]['details']	= $Detail;

				$areaindex++;
			}
		}
	}

	if(!empty($AreaDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Bill statement summary listed successfully.";
	}
	$response['paymentlist']	= $AreaDetail;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetBillStatementSummaryPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to create bill statement summary pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "bill-statement-summary.pdf";

	$StartDate	= "";
	$EndDate	= "";

	$startdate		= strtotime($_POST['monthyear']);
	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$paymentstartdate	= strtotime($_POST['paymentstartdate']);

	if(trim($_POST['monthyear']) != "")
	{
		$StartDate	= strtotime($_POST['monthyear']);
		/*$EndDate	= $selectedyear."-".$selectedmonth."-".date('t',$StartDate);
		$EndDate	= strtotime($EndDate)+86399;*/
		$EndDate	= strtotime($_POST['enddate'])+86399;
	}

	/*$File 		= "viewbillstatementsummary.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&startdate=".$StartDate."&enddate=".$EndDate."&usefromdate=".(int)$_POST['usefromdate']."&paymentstartdate=".$paymentstartdate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewbillstatementsummary.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$StartDate."&enddate_strtotime=".$EndDate."&paymentstartdate=".$paymentstartdate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "bill statement summary pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetOutstandingReportSummary")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch outstanding report summary.";

	$TotalRec	= 0;

	if($_POST['clientid'] > 0)
	{
		$Condition		= "";
		$CustESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$InvoiceCond	= "";
		$InvoiceEsql	= array("clientid2"=>(int)$_POST['clientid'],'deletedon'=>1,"deletedon2"=>1,"ispaid"=>1);

		$PayCond	= "";
		$PayEsql	= array("clientid"=>(int)$_POST['clientid'],"clientid2"=>(int)$_POST['clientid'],"deletedon2"=>1,"paymentdeletedon"=>1);

		$startdate		= strtotime($_POST['monthyear']);
		$selectedmonth	= date("m",$startdate);
		$selectedyear	= date("Y",$startdate);
		$month_last_day	= date('t',$startdate);

		$invoicedate	= $selectedyear."-".$selectedmonth."-".$month_last_day;

		if(trim($_POST['monthyear']) != "")
		{
			$StartDate	= strtotime($_POST['monthyear']);
			$EndDate	= strtotime($_POST['enddate'])+86399;

			$InvoiceCond	.= ' AND UNIX_TIMESTAMP(CONCAT(invoiceyear,"-",invoicemonth,"-01")) < :invoicedate2 ';
			$InvoiceEsql['invoicedate2'] = $StartDate;

			/*$InvoiceCond	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

			$InvoiceEsql['invoicemonth']	= $selectedmonth;
			$InvoiceEsql['invoiceyear']		= $selectedyear;*/

			/*$PayCond	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

			$PayEsql['startdate']	= $StartDate;
			$PayEsql['enddate']		= $EndDate;*/

			if($_POST['usefromdate'] > 0)
			{
				$PayCond	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

				$PaymentStartDate	= strtotime($_POST['paymentstartdate']);

				$PayEsql['startdate']	= $PaymentStartDate;
				$PayEsql['enddate']	= $EndDate;
			}
			else
			{
				$PayCond	.= " AND payment.paymentdate <=:enddate";

				$PayEsql['enddate']	= $EndDate;
			}
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];

			$InvoiceCond	.= " AND cust.lineid=:lineid";
			$InvoiceEsql['lineid']	= (int)$_POST['lineid'];

			$PayCond	.= " AND cust.lineid=:lineid";
			$PayEsql['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];

			$InvoiceCond	.= " AND cust.areaid=:areaid";
			$InvoiceEsql['areaid']	= (int)$_POST['areaid'];

			$PayCond	.= " AND cust.areaid=:areaid";
			$PayEsql['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];

			$InvoiceCond	.= " AND cust.hawkerid=:hawkerid";
			$InvoiceEsql['hawkerid']	= (int)$_POST['hawkerid'];

			$PayCond	.= " AND cust.hawkerid=:hawkerid";
			$PayEsql['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids = $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND areaid IN (".$areaids.")";

			$InvoiceCond	.= " AND cust.areaid IN (".$areaids.")";

			$PayCond		.= " AND cust.areaid IN (".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND lineid IN(".$lineids.")";

			$InvoiceCond	.= " AND cust.lineid IN(".$lineids.")";

			$PayCond		.= " AND cust.lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		if($CustNum > 0)
		{
			$AllAreaArr	= GetAllArea($_POST['clientid']);
			$AllLineArr	= GetAllLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$id				= $custrows['id'];
				$areaid			= $custrows['areaid'];
				$lineid			= $custrows['lineid'];
				$openingbalance	= $custrows['openingbalance'];

				$areaname			= $AllAreaArr[$areaid]['name'];
				$linename			= $AllLineArr[$lineid]['name'];

				if($areaid < 1)
				{
					$areaname	= "Unnamed";
				}

				$RecordListArr[$areaid]['name']			= $areaname;
				$RecordListArr[$areaid]['outstanding']	+= $openingbalance;

				$RecordListArr[$areaid]['detail'][$lineid]['name']			= $linename;
				$RecordListArr[$areaid]['detail'][$lineid]['outstanding']	+= $openingbalance;
			}
		}
	}

	$RecordListArr2	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $areaid => $arearows)
		{
			$areaname		= $arearows['name'];
			$outstanding	= $arearows['outstanding'];
			$linedetail		= $arearows['detail'];

			$RecordListArr2[$areaid]['name']		= $areaname;
			$RecordListArr2[$areaid]['outstanding']	+= $outstanding;

			foreach($linedetail as $lineid => $linerows)
			{
				$linename		= $linerows['name'];
				$outstanding	= $linerows['outstanding'];

				$RecordListArr2[$areaid]['detail'][$lineid]['name']			= $linename;
				$RecordListArr2[$areaid]['detail'][$lineid]['outstanding']	+= $outstanding;

				$InvoiceSql		= "SELECT COUNT(invoice.id) as totalinvoice,SUM(invoice.finalamount) AS totalfinalamount FROM ".$Prefix."invoices invoice,".$Prefix."customers cust WHERE cust.id=invoice.customerid AND invoice.deletedon <:deletedon AND invoice.ispaid < :ispaid AND cust.clientid=:clientid2 AND cust.deletedon < :deletedon2 AND cust.lineid=:lineid2".$InvoiceCond;

				$TempInvoiceEsql	= array("lineid2"=>(int)$lineid);

				$InvoiceEsql2	= array_merge($TempInvoiceEsql,$InvoiceEsql);

				$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql2);
				$InvoiceRows	= pdo_fetch_assoc($InvoiceQuery);

				$totalfinalamount	= $InvoiceRows['totalfinalamount'];

				$RecordListArr2[$areaid]['outstanding']						+= $totalfinalamount;
				$RecordListArr2[$areaid]['detail'][$lineid]['outstanding']	+= $totalfinalamount;

				$PaySQL		= "SELECT COUNT(payment.id) as totalpaymentcount,SUM(payment.amount) AS paidamount, SUM(payment.discount) AS discount, SUM(payment.coupon) AS coupon FROM ".$Prefix."customer_payments payment,".$Prefix."customers cust WHERE payment.clientid=:clientid AND cust.clientid=:clientid2 AND cust.deletedon <:deletedon2 AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid AND cust.lineid=:lineid2".$PayCond;

				$TempPayEsql	= array("lineid2"=>(int)$lineid);
				$PayEsql2		= array_merge($TempPayEsql,$PayEsql);

				$PayQuery	= pdo_query($PaySQL,$PayEsql2);
				$PayRows	= pdo_fetch_assoc($PayQuery);

				$paidamount	= $PayRows['paidamount'];
				$discount	= $PayRows['discount'];
				$coupon		= $PayRows['coupon'];

				$totalpayments	= (float)$paidamount+(float)$discount+(float)$coupon;

				$RecordListArr2[$areaid]['outstanding']						-= $totalpayments;
				$RecordListArr2[$areaid]['detail'][$lineid]['outstanding']	-= $totalpayments;
			}
		}
	}

	$AreaDetail	= array();

	$totaloutstanding	= 0;

	if(!empty($RecordListArr2))
	{
		$areaindex	= 0;
		foreach($RecordListArr2 as $areaid => $areadata)
		{
			$index	= 0;
			$Detail	= array();

			if(!empty($areadata['detail']))
			{
				foreach($areadata['detail'] as $lineid=>$detailrows)
				{
					$Detail[$index]['serialno']		= $index+1;
					$Detail[$index]['id']			= $lineid;
					$Detail[$index]['name']			= $detailrows['name'];
					$Detail[$index]['outstanding']	= number_format($detailrows['outstanding'],2);

					$TotalRec++;
					$index++;
				}
			}
			if(!empty($Detail))
			{
				$AreaDetail[$areaindex]['areaid']		= $areaid;
				$AreaDetail[$areaindex]['name']			= $areadata['name'];
				$AreaDetail[$areaindex]['outstanding']	= number_format($areadata['outstanding'],2);
				$AreaDetail[$areaindex]['details']		= $Detail;

				$totaloutstanding	+= $areadata['outstanding'];

				$areaindex++;
			}
		}
	}

	if(!empty($AreaDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Outstanding report summary listed successfully.";
	}
	$response['paymentlist']		= $AreaDetail;
	$response['totaloutstanding']	= number_format($totaloutstanding,2);
	$response['totalrecord']		= $TotalRec;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetOutstandingReportSummaryPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate outstanding summary report pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "outstanding-area-report.pdf";

	$StartDate	= "";
	$EndDate	= "";

	$startdate		= strtotime($_POST['monthyear']);
	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	if(trim($_POST['monthyear']) != "")
	{
		$StartDate	= strtotime($_POST['monthyear']);
		$EndDate	= strtotime($_POST['enddate'])+86399;
	}

	$PaymentStartDate	= "";

	if($_POST['usefromdate'] > 0)
	{
		$PaymentStartDate	= strtotime($_POST['paymentstartdate']);
	}

	/*$File 		= "viewoutstandingreportsummary.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&startdate=".$StartDate."&enddate=".$EndDate."&usefromdate=".$_POST['usefromdate']."&paymentstartdate=".$PaymentStartDate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewoutstandingreportsummary.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$StartDate."&enddate_strtotime=".$EndDate."&paymentstartdate=".$paymentstartdate."&".$FilterDataStr;

	/*die($ServerAPIURL.$File);*/

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "Outstanding report summary pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetReportMonthYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list month year filter.";

	$currentyear	= date("Y");

	$RecordSetArr	= array();

	$index	= 0;

	$startmonth	= "01";
	$endmonth	= "12";

	if($_POST['type'] == 'billsms')
	{
		$startyear	= 2021;
	}

	for($yearloop = $currentyear; $yearloop >= $startyear; $yearloop--)
	{
		if($yearloop == 2021 && $_POST['type'] == 'billsms')
		{
			$startmonth	= "06";
		}
		for($monthloop = $startmonth; $monthloop <= $endmonth; $monthloop++)
		{
			$monthname	= "";

			$month	= "0".(int)$monthloop;
			if($monthloop > 9)
			{
				$month	= (int)$monthloop;
			}

			$monthname	= date("F",strtotime($month."/01/".$yearloop));

			if($yearloop%4 == 0)
			{
				$daysInMonth = array(1=>31, 2=>29, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
			}
			else
			{
				$daysInMonth = array(1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
			}

			$RecordSetArr[$index]['index']		= $index+1;
			$RecordSetArr[$index]['month']		= $month;
			$RecordSetArr[$index]['year']		= $yearloop;
			$RecordSetArr[$index]['name']		= $monthname." - ".$yearloop;
			$RecordSetArr[$index]['monthname']	= $monthname;
			$RecordSetArr[$index]['lastdate']	= $daysInMonth[(int)$month];

			$index++;
		}
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['recordset']		= $RecordSetArr;
		$response['defaultyear']	= $currentyear;
		$response['msg']			= "Month year filter listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetBillCollectionSummary")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch bill collection summary.";

	$TotalRec	= 0;

	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$PayCondition	= "";
	$PayESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND invoices.invoicedate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;


		$PayCondition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$PayESQL['startdate']	= $StartDate;
		$PayESQL['enddate']		= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];

		$PayCondition		.= " AND cust.areaid=:areaid";
		$PayESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];

		$PayCondition	.= " AND cust.lineid=:lineid";
		$PayESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$PayCondition	.= " AND cust.hawkerid=:hawkerid";
		$PayESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition		.= " AND cust.areaid IN (".$areaids.")";
		$PayCondition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition		.= " AND cust.lineid IN(".$lineids.")";
		$PayCondition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,invoices.ispaid AS ispaid,invoices.totalamount AS totalamount,invoices.invoicedate AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoices invoices WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND invoices.deletedon <:deletedon2 AND cust.id=invoices.customerid ".$Condition." GROUP BY invoices.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$ESQL['deletedon2'] = 1;

	$InvoiceQuery	= pdo_query($SQL,$ESQL);
	$InvoiceNum		= pdo_num_rows($InvoiceQuery);

	if($InvoiceNum > 0)
	{
		while($invoicerows = pdo_fetch_assoc($InvoiceQuery))
		{
			$ispaid			= $invoicerows['ispaid'];
			$totalamount	= $invoicerows['totalamount'];
			
			$areaid			= $invoicerows['areaid'];
			$lineid			= $invoicerows['lineid'];

			$invoicedate	= $invoicerows['invoicedate'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			if($ispaid > 0)
			{
				/*$totalpayments	+= $invoicerows['totalamount'];*/
			}
			else
			{
				$year			= date("Y",$invoicedate);
				$monthname		= date("F",$invoicedate);
				$invoicedate2	= strtotime($year."-".$monthname."-01");

				$RecordListArr[$invoicedate2]['billing']	+= $totalamount;
				$RecordListArr[$invoicedate2]['cash']		+= 0;
				$RecordListArr[$invoicedate2]['coupon']		+= 0;
				$RecordListArr[$invoicedate2]['disc']		+= 0;
				$RecordListArr[$invoicedate2]['totcoll']	+= 0;
				$RecordListArr[$invoicedate2]['balance']	+= $totalamount;
			}
		}
	}

	$PaySQL	= "SELECT cust.*,payment.amount AS totalamount,payment.createdon AS paymentdate,payment.paymentamount AS paymentamount,payment.discount AS discount,payment.coupon AS coupon,payment.paymentmethod AS paymentmethod FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$PayCondition." GROUP BY payment.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$PayQuery	= pdo_query($PaySQL,$PayESQL);
	$PayNum		= pdo_num_rows($PayQuery);

	if($PayNum > 0)
	{
		while($payrows = pdo_fetch_assoc($PayQuery))
		{
			$id				= $payrows['id'];
			$customerid		= $payrows['customerid'];
			$name			= $payrows['name'];
			$areaid			= $payrows['areaid'];
			$lineid			= $payrows['lineid'];

			$paymentdate	= $payrows['paymentdate'];

			$totalamount	= $payrows['totalamount'];
			$paymentamount	= $payrows['paymentamount'];
			$discount		= $payrows['discount'];
			$coupon			= $payrows['coupon'];
			$paymentmethod	= $payrows['paymentmethod'];

			$year			= date("Y",$paymentdate);
			$monthname		= date("F",$paymentdate);

			$paymentdate2	= strtotime($year."-".$monthname."-01");

			$RecordListArr[$paymentdate2]['billing']	+= 0;
			if(strtolower($paymentmethod) == "cash")
			{
				$RecordListArr[$paymentdate2]['cash']	+= $paymentamount;
			}
			else
			{
				$RecordListArr[$paymentdate2]['cash']	+= 0;
			}
			$RecordListArr[$paymentdate2]['coupon']		+= $coupon;
			$RecordListArr[$paymentdate2]['disc']		+= $discount;
			$RecordListArr[$paymentdate2]['totcoll']	+= $totalamount;
			$RecordListArr[$paymentdate2]['balance']	-= $totalamount;
		}
	}

	$index	= 0;
	$Detail	= array();

	if(!empty($RecordListArr))
	{
		$areaindex	= 0;
		foreach($RecordListArr as $coldate => $colrows)
		{
			$Detail[$index]['serialno']		= $index+1;
			$Detail[$index]['year']			= date("Y",$coldate);
			$Detail[$index]['month']		= date("M",$coldate);
			$Detail[$index]['billing']		= number_format($colrows['billing'],2);
			$Detail[$index]['cash']			= number_format($colrows['cash'],2);
			$Detail[$index]['coupon']		= number_format($colrows['coupon'],2);
			$Detail[$index]['disc']			= number_format($colrows['disc'],2);
			$Detail[$index]['totcoll']		= number_format($colrows['totcoll'],2);

			$extstr	= "";

			$balance	= $colrows['balance'];

			if($balance < 1)
			{
				$balance	= 0;
			}

			$Detail[$index]['balance']		= number_format(abs($balance),2);

			$TotalRec++;
			$index++;
		}
	}

	if(!empty($Detail))
	{
		$response['success']	= true;
		$response['msg']		= "bill collection summary listed successfully.";
	}

	$response['paymentlist']	= $Detail;
	$response['totalrecord']	= $TotalRec;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetBillCollectionSummaryPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate bill collection summary pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "bill-collection-summary.pdf";

	$startdate	= "";
	$enddate	= "";

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$startdate	= strtotime($_POST['startdate']);
		$enddate	= strtotime($_POST['enddate'])+86399;
	}

	/*$File	= "viewbillcollectionsummary.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&startdate=".$startdate."&enddate=".$enddate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewbillcollectionsummary.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$startdate."&enddate_strtotime=".$enddate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "bill collection summary pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMagazineSaleDetailData_bak")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch magazine sale detail";

	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND details.createdon BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,details.qty AS itemqty,details.price AS itemprice,details.inventoryname AS inventoryname,details.totalprice AS totalprice,details.createdon AS invoicedate,details.id AS detailid FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllSubLineArr	= GetAllSubLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];
			$customerid		= $rows['customerid'];
			$name			= $rows['name'];
			$address		= $rows['address1'];
			$housenumber	= $rows['housenumber'];
			$floor			= $rows['floor'];

			$itemqty		= $rows['itemqty'];
			$itemprice		= $rows['itemprice'];
			$inventoryname	= $rows['inventoryname'];
			$totalprice		= $rows['totalprice'];
			$invoicedate	= $rows['invoicedate'];
			$detailid		= $rows['detailid'];

			$invoicedate2	= strtotime(date("Y-m-d", $invoicedate));

			$areaid			= $rows['areaid'];
			$lineid			= $rows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];
			$sublinename	= $AllSubLineArr[$rows['sublineid']]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$addressstr = '';
	
			if($housenumber !='')
			{
				$addressstr .= $housenumber;
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
				if($addressstr !='')
				{
					$addressstr .= ", ".$floor." ".$ext;
				}
				else
				{
					$addressstr .= $floor." ".$ext;
				}
			}
			if($address !='')
			{
				if($addressstr !='')
				{
					$addressstr .= ", ".$address;
				}
				else
				{
					$addressstr .= $address;
				}
			}
			if($sublinename !='')
			{
				if($addressstr !='')
				{
					$addressstr .= ", ".$sublinename;
				}
				else
				{
					$addressstr .= $sublinename;
				}
			}

			if(trim($addressstr) =='')
			{
				$addressstr = '--';
			}

			$name2	= "#".$customerid." ".$name;

			$RecordListArr[$invoicedate2]['datetotal']	+= $totalprice;
			$RecordListArr[$invoicedate2]['dateqty']	+= $itemqty;

			$RecordListArr[$invoicedate2]['detail'][$detailid]['id']			= $id;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['customerid']	= $customerid;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['name']			= $name2;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['area']			= $areaname;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['line']			= $linename;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['address']		= $addressstr;

			$RecordListArr[$invoicedate2]['detail'][$detailid]['itemqty']		= $itemqty;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['itemprice']		= $itemprice;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['inventoryname']	= $inventoryname;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['totalprice']	= $totalprice;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['invoicedate']	= $invoicedate;
		}
	}
	if(!empty($RecordListArr))
	{
		$invoiceindex	= 0;
		$DateDetail		= array();

		foreach($RecordListArr as $invoicedate => $invoicerows)
		{
			$index	= 0;
			$Detail	= array();

			if(!empty($invoicerows['detail']))
			{
				foreach($invoicerows['detail'] as $detailkey=>$detailrows)
				{
					$Detail[$index]['serialno']			= $index+1;
					$Detail[$index]['id']				= $detailrows['id'];
					$Detail[$index]['customerid']		= $detailrows['customerid'];
					$Detail[$index]['name']				= $detailrows['name'];
					$Detail[$index]['area']				= $detailrows['area'];
					$Detail[$index]['line']				= $detailrows['line'];
					$Detail[$index]['address']			= $detailrows['address'];
					$Detail[$index]['itemqty']			= $detailrows['itemqty'];
					$Detail[$index]['itemprice']		= number_format($detailrows['itemprice'],2);
					$Detail[$index]['inventoryname']	= $detailrows['inventoryname'];
					$Detail[$index]['totalprice']		= number_format($detailrows['totalprice'],2);
					$Detail[$index]['invoicedate']		= date("d-M-Y",$detailrows['invoicedate']);

					$index++;
				}
			}
			if(!empty($Detail))
			{
				$DateDetail[$invoiceindex]['invoicedate']	= $invoicedate;
				$DateDetail[$invoiceindex]['dateqty']		= $invoicerows['dateqty'];
				$DateDetail[$invoiceindex]['name']			= date("d-M-Y",$invoicedate);
				$DateDetail[$invoiceindex]['totalpayment']	= number_format($invoicerows['datetotal'],2);
				$DateDetail[$invoiceindex]['details']		= $Detail;

				$invoiceindex++;
			}
		}
	}

	if(!empty($DateDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Magazine sale detail listed successfully.";
	}
	$response['paymentlist']	= $DateDetail;

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMagazineSaleSummary-bak")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch magazine sale summary";

	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND details.createdon BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND details.inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT details.qty AS itemqty,details.inventoryid AS inventoryid,details.inventoryname AS inventoryname FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllSubLineArr	= GetAllSubLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];

			$itemqty		= $rows['itemqty'];
			$inventoryid	= $rows['inventoryid'];
			$inventoryname	= $rows['inventoryname'];

			$RecordListArr[$inventoryid]['inventoryid']	= $inventoryid;
			$RecordListArr[$inventoryid]['name']		= $inventoryname;
			$RecordListArr[$inventoryid]['quantity']	+= $itemqty;
		}
	}

	$lineindex	= 0;
	$lineDetail	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $inventoryid => $summaryrow)
		{
			if($summaryrow['quantity'] > 0)
			{
				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['id']			= $summaryrow['inventoryid'];
				$lineDetail[$lineindex]['name']			= $summaryrow['name'];
				$lineDetail[$lineindex]['quantity']		= $summaryrow['quantity'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}

	if(!empty($lineDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Magazine sale summary listed successfully.";
	}
	
	$response['paymentlist']	= $lineDetail;
	$response['totalrecord']	= $TotalRec;

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMagazineSaleSummary")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch magazine sale summary";

	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "AND clientid=:clientid ";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND saledate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND lineid IN(".$lineids.")";
	}

	/*$SQL	= "SELECT details.qty AS itemqty,details.inventoryid AS inventoryid,details.inventoryname AS inventoryname FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";*/

	$SQL	= "SELECT * FROM ".$Prefix."sale WHERE deletedon < :deletedon ".$Condition." ORDER BY id DESC";
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllSubLineArr	= GetAllSubLine($_POST['clientid']);
		$AllInventoryArr	= GetInventoryNames();

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];

			$itemqty		= $rows['noofpices'];
			$inventoryid	= $rows['inventoryid'];
			$inventoryname	= $AllInventoryArr[$inventoryid]['name'];

			$RecordListArr[$inventoryid]['inventoryid']	= $inventoryid;
			$RecordListArr[$inventoryid]['name']		= $inventoryname;
			$RecordListArr[$inventoryid]['quantity']	+= $itemqty;
		}
	}

	$lineindex	= 0;
	$lineDetail	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $inventoryid => $summaryrow)
		{
			if($summaryrow['quantity'] > 0)
			{
				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['id']			= $summaryrow['inventoryid'];
				$lineDetail[$lineindex]['name']			= $summaryrow['name'];
				$lineDetail[$lineindex]['quantity']		= $summaryrow['quantity'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}

	if(!empty($lineDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Magazine sale summary listed successfully.";
	}
	
	$response['paymentlist']	= $lineDetail;
	$response['totalrecord']	= $TotalRec;

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMagazineSaleSummaryPDF")
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to create magazine sale summary pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "magazine-sale-summary.pdf";

	$StartDate	= "";
	$EndDate	= "";

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;
	}

	$File	= "viewmagazinesalesummary.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr."&startdate_strtotime=".$StartDate."&enddate_strtotime=".$EndDate;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "payment register pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSingleMagazineSaleDetail-bak")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch magazine sale detail";

	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND details.createdon BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND details.inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT details.qty AS itemqty,details.inventoryid AS inventoryid,details.inventoryname AS inventoryname,details.createdon AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		/*$AllAreaArr	= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllSubLineArr	= GetAllSubLine($_POST['clientid']);*/

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= (int)$rows['id'];
			$invoicedate	= $rows['invoicedate'];
			$itemqty		= (int)$rows['itemqty'];
			$inventoryid	= (int)$rows['inventoryid'];
			$inventoryname	= $rows['inventoryname'];

			$invoicedate2	= date("Y-m-d", $invoicedate);

			/*$RecordListArr[$inventoryid]['inventoryid']	= $inventoryid;
			$RecordListArr[$inventoryid]['name']			= $inventoryname;
			$RecordListArr[$inventoryid]['quantity']		+= $itemqty;*/

			$RecordListArr[$invoicedate2]['inventoryid']	= $inventoryid;
			$RecordListArr[$invoicedate2]['name']			= $inventoryname;
			$RecordListArr[$invoicedate2]['quantity']		+= $itemqty;
		}
	}

	$lineindex	= 0;
	$lineDetail	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $saledate => $summaryrow)
		{
			if($summaryrow['quantity'] > 0)
			{
				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['date']			= $saledate;
				$lineDetail[$lineindex]['id']			= $summaryrow['inventoryid'];
				$lineDetail[$lineindex]['name']			= $summaryrow['name'];
				$lineDetail[$lineindex]['quantity']		= $summaryrow['quantity'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}

	if(!empty($lineDetail))
	{
		$response['success']	= true;
		$response['msg']		= "Magazine sale detail listed successfully.";
	}
	
	$response['recordset']		= $lineDetail;
	$response['totalrecord']	= $TotalRec;

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSingleMagazineSaleDetail")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch magazine sale detail";

	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND saledate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND lineid IN(".$lineids.")";
	}

	/*$SQL	= "SELECT details.qty AS itemqty,details.inventoryid AS inventoryid,details.inventoryname AS inventoryname,details.createdon AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";
	*/

	$SQL	= "SELECT * FROM ".$Prefix."sale WHERE clientid=:clientid AND deletedon <:deletedon ".$Condition." ORDER BY areaid,lineid,noofpices DESC";
	
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$Result = array();
	if($Num > 0)
	{
		/*$AllAreaArr	= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllSubLineArr	= GetAllSubLine($_POST['clientid']);*/

		$AreaArr	= array();
		$LineArr	= array();
		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= (int)$rows['id'];
			$saledate		= $rows['saledate'];
			$itemqty		= (int)$rows['noofpices'];
			$areaid			= (int)$rows['areaid'];
			$lineid			= (int)$rows['lineid'];

			$AreaArr[$areaid] += $itemqty;
			$LineArr[$lineid] += $itemqty;
		}

		$areastr = @implode(",",@array_keys($AreaArr));
		$linestr = @implode(",",@array_keys($LineArr));
		
		if($areastr !='')
		{
			$sql_area	= "SELECT * FROM ".$Prefix."area WHERE id IN (".$areastr.") AND deletedon < :deletedon ORDER BY name ASC";
			$esql_area	= array('deletedon'=>1);
			$query_area = pdo_query($sql_area,$esql_area);
			$num_area	= pdo_num_rows($query_area);
			if($num_area > 0)
			{
				$index = 0;
				while($row_area = pdo_fetch_assoc($query_area))
				{
					$areaid		= $row_area['id'];
					$areaname	= $row_area['name'];

					$Result[$index]['areaid']	= $areaid;
					$Result[$index]['areaname']	= $areaname;
					$Result[$index]['areatotal']	= $AreaArr[$areaid];

					$sql_line	= "SELECT * FROM ".$Prefix."line WHERE id IN (".$linestr.") AND areaid=:areaid AND deletedon < :deletedon  ORDER BY name ASC";
					$esql_line	= array('deletedon'=>1,"areaid"=>(int)$areaid);
					$query_line = pdo_query($sql_line,$esql_line);
					$num_line	= pdo_num_rows($query_line);
					$TempArr	= array();
					if($num_line > 0)
					{
						$lineindex = 0;
						while($row_line = pdo_fetch_assoc($query_line))
						{
							$lineid		= $row_line['id'];
							$linename	= $row_line['name'];
							$TempArr[$lineindex]['lineid']	=  $lineid;
							$TempArr[$lineindex]['linename']=  $linename;
							$TempArr[$lineindex]['linetotal']=  $LineArr[$lineid];

							$lineindex++;
						}
						$Result[$index]['linedata'] = $TempArr;
					}
				
				$index++;
				}
			}

		}
	}

	if(!empty($Result))
	{
		$response['success']	= true;
		$response['msg']		= "Magazine sale detail listed successfully.";
	}
	
	$response['recordset']		= $Result;

 	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSingleMagazineSaleByLineID")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch magazine sale detail";

	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND saledate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['checkareaid'] > 0)
	{
		$Condition	.= " AND areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['checkareaid'];
	}

	if($_POST['checklineid'] > 0)
	{
		$Condition	.= " AND lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['checklineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND lineid IN(".$lineids.")";
	}

	/*$SQL	= "SELECT details.qty AS itemqty,details.inventoryid AS inventoryid,details.inventoryname AS inventoryname,details.createdon AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";
	*/

	$SQL	= "SELECT * FROM ".$Prefix."sale WHERE clientid=:clientid AND deletedon <:deletedon ".$Condition." ORDER BY saledate DESC,customerid ASC";
	
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$Result = array();
	if($Num > 0)
	{
		$AllAreaArr	= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		/*$AllSubLineArr	= GetAllSubLine($_POST['clientid']);*/

		$Result[0]['areaname'] = $AllAreaArr[$_POST['checkareaid']]['name'];
		$Result[0]['linename'] = $AllLineArr[$_POST['checklineid']]['name'];
		$AreaArr	= array();
		$LineArr	= array();
		$index = 0;
		$TotalRecords = $Num;
		$TotalStock = 0;
		$precustomerid = 0;
		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= (int)$rows['id'];
			$saledate		= $rows['saledate'];
			$itemqty		= (int)$rows['noofpices'];
			$areaid			= (int)$rows['areaid'];
			$lineid			= (int)$rows['lineid'];
			$customerid		= (int)$rows['customerid'];

			$CustomerNameArr	= GetCustomerDetail($customerid);

			if($precustomerid != $customerid && $precustomerid != '') 
			{
				$index++;
				$SaleData[$index]['itemqty'] = $itemqty;
			}
			else
			{
				$SaleData[$index]['itemqty'] += $itemqty;
			}
			$SaleData[$index]['id'] = $id;
			$SaleData[$index]['customername'] = $CustomerNameArr['name'];
		
			$TotalStock += $itemqty;

			$precustomerid = $customerid; 
			//$index++;
		}
		$Result[0]['linedata'] = $SaleData;
		$Result[0]['totalrecords'] = ($index+1);
		$Result[0]['totalstock'] = $TotalStock;
	}

	if(!empty($Result))
	{
		$response['success']	= true;
		$response['msg']		= "Magazine sale detail listed successfully.";
	}
	
	$response['recordset']		= $Result;

 	$json = json_encode($response);
    echo $json;
	die;
}

if($_POST['Mode'] == 'GetMagazineSaleDetailPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate magazine sale detail pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "magazine-sale-detail.pdf";

	$startdate	= "";
	$enddate	= "";

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$startdate	= strtotime($_POST['startdate']);
		$enddate	= strtotime($_POST['enddate'])+86399;
	}

	/*$File	= "viewmagazinesaledetail.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&startdate=".$startdate."&enddate=".$enddate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewmagazinesaledetail.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$startdate."&enddate_strtotime=".$enddate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "magazine sale detail pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMonthlyBillOfNewspaperSummary")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$_POST["month"]	= (int)date("m",strtotime($_POST['startdate']));
	$_POST["year"]	= date("Y",strtotime($_POST['startdate']));
	$monthname		= date("F",strtotime($_POST['startdate']));

	if ($_POST['year']%4 == 0)
	{
		$daysInMonth = array(1=>31, 2=>29, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}
	else
	{
		$daysInMonth = array(1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}

	$totaldays	= $daysInMonth[$_POST['month']];
	$HolidayArr	= GetHoliday($_POST['clientid']);

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$CatCond		= "";
	$CategoryEsql	= array("status"=>1);

	if($_POST['catid'] > 0)
	{
		$CatCond			.= " AND id=:id";
		$CategoryEsql['id']	= (int)$_POST['catid'];
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$CatCond." ORDER BY orderby ASC";

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData	= GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		$ActiveSubscriptionsData	= GetActiveCustomerSubscriptions($_POST['clientid']);

		$ClientInventoryPricing	= ClientInventoryPricing($_POST['clientid'],$_POST["year"],$_POST["month"]);
		$ClientInventoryPricingByDate	= ClientInventoryPricingByDate($_POST['clientid'],$_POST["year"],$_POST["month"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();
			$idarray			= array();
			$categoryidarray	= array();
			$namearray			= array();
			$pricingtypearray	= array();
			$pricearray			= array();
			$daysarray			= array();
			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$index	= 0;

				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id				= $rows['id'];
					$categoryid		= $rows['categoryid'];
					$name			= $rows['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 1; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}
					$idarray[]			= $id; 
					$categoryidarray[]	= $categoryid; 
					$namearray[]		= $name; 
					$daysarray[]		= $days; 
					$pricingtypearray[]	= $pricingtype; 
					$pricearray[]		= $price; 
				}
			}
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status=:status AND categoryid=:categoryid ORDER BY inv.name ASC";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,"status"=>1);

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2		= pdo_num_rows($InventoryQuery2);

			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id				= $rows2['id'];
					$categoryid		= $rows2['categoryid'];
					$name			= $rows2['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 1; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}
					$idarray[]			= $id; 
					$categoryidarray[]	= $categoryid; 
					$namearray[]		= $name; 
					$daysarray[]		= $days; 
					$pricingtypearray[]	= $pricingtype; 
					$pricearray[]		= $price; 
				}	
			}
			
			if(!empty($namearray))
			{
				$sortnamearray = array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$pricingtypearray,$daysarray);
				
				foreach($namearray as $key => $value)
				{
					$id				= $idarray[$key];
					$categoryid		= $categoryidarray[$key];
					$name			= $value;
					$days			= $daysarray[$key];
					$pricingtype	= $pricingtypearray[$key];
					$price			= $pricearray[$key];
			
					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							if($days < 1)
							{
								$days	= "--";
							}

							if($price < 1)
							{
								$price	= "--";
							}
							$InventoryListArr[$index]['id']				= (int)$id;
							$InventoryListArr[$index]['categoryid']		= (int)$categoryid;
							$InventoryListArr[$index]['name']			= $name;
							$InventoryListArr[$index]['days']			= $days;
							$InventoryListArr[$index]['price']			= $price;

							$dayindex	= 0;

							$dateListArr	= array();
							$isbydatepricingavialable = 0;
							/*for($dateloop = 1; $dateloop <= $totaldays; $dateloop++)
							{
								$date	= $dateloop;
								$price	= "";

								$canshowrow	= false;
								$hasholiday	= false;

								if(!empty($ClientInventoryPricingByDate[$id]))
								{
									$PricingByDate	= $ClientInventoryPricingByDate[$id];

									if(!empty($PricingByDate[$dateloop]))
									{
										$price	= $PricingByDate[$dateloop]['price'];
									}
								}

								if($price > 0)
								{
									$canshowrow	= true;
								}

								$datetimestamp	= strtotime($_POST['month']."/".$dateloop."/".$_POST['year']);

								if(!empty($HolidayArr))
								{
									foreach($HolidayArr as $HolidayKey=>$HolidayRows)
									{
										$inventoryid	= $HolidayRows['inventoryid'];
										$startdate		= $HolidayRows['startdate'];
										$enddate		= $HolidayRows['enddate'];

										if($inventoryid > 0)
										{
											if(($datetimestamp >= $startdate && $datetimestamp <= $enddate) && $inventoryid == $id)
											{
												$canshowrow	= true;
												$hasholiday	= true;
												break;
											}
										}
										else
										{
											if($datetimestamp >= $startdate && $datetimestamp <= $enddate)
											{
												$canshowrow	= true;
												$hasholiday	= true;
												break;
											}
										}
									}
								}
								if($canshowrow)
								{
									if($dateloop < 10)
									{
										$dateListArr[$dayindex]['displayname']	= "0".$dateloop;
									}
									else
									{
										$dateListArr[$dayindex]['displayname']	= $dateloop;
									}

									$dateListArr[$dayindex]['date']			= $dateloop;
									$dateListArr[$dayindex]['hasholiday']	= false;
									$dateListArr[$dayindex]['dateprice']	= $price;
									$dateListArr[$dayindex]['hasholiday']	= $hasholiday;

									$dayindex++;
								}

								if($isbydatepricingavialable < 1)
								{
									if($price > 0.1)
									{
										$isbydatepricingavialable = 1;
									}
								}
							}
							$InventoryListArr[$index]['datepricing']	= $dateListArr;*/
							$InventoryListArr[$index]['serial']			= $index+1;
							$InventoryListArr[$index]['datepricing']	= array();
							if($isbydatepricingavialable > 0)
							{
								$pricingtype = '1';
							}
							$InventoryListArr[$index]['pricingtype']	= $pricingtype;

							$index++;
						}
					}
				}
			}
			$RecordListArr[$catindex]['id']			= (int)$catid;
			$RecordListArr[$catindex]['title']		= $cattitle;
			$RecordListArr[$catindex]['recordlist']	= $InventoryListArr;

			$catindex++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer Inventory listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;
	$response['monthname']		= $monthname;
	$response['totaldays']		= (int)$totaldays;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMonthlyBillOfNewspaperDetail")
{
	$catindex		= 0;
	$RecordListArr	= array();

	$_POST["month"]	= (int)date("m",strtotime($_POST['startdate']));
	$_POST["year"]	= date("Y",strtotime($_POST['startdate']));
	$monthname		= date("F",strtotime($_POST['startdate']));

	if ($_POST['year']%4 == 0)
	{
		$daysInMonth = array(1=>31, 2=>29, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}
	else
	{
		$daysInMonth = array(1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}

	$totaldays	= $daysInMonth[$_POST['month']];
	$HolidayArr	= GetHoliday($_POST['clientid']);

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND inv.id=:inventoryid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
	$InventoryEsql	= array("stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"],"inventoryid"=>(int)$_POST['inventoryid']);

	$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
	$InventoryNum	= pdo_num_rows($InventoryQuery);

	$idarray			= array();
	$categoryidarray	= array();
	$namearray			= array();
	$pricingtypearray	= array();
	$pricearray			= array();
	$daysarray			= array();
	$InventoryListArr	= array();

	if($InventoryNum > 0)
	{
		$ClientInventoryData	= GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		$ActiveSubscriptionsData	= GetActiveCustomerSubscriptions($_POST['clientid']);

		$ClientInventoryPricing	= ClientInventoryPricing($_POST['clientid'],$_POST["year"],$_POST["month"]);
		$ClientInventoryPricingByDate	= ClientInventoryPricingByDate($_POST['clientid'],$_POST["year"],$_POST["month"]);

		$index	= 0;

		while($rows = pdo_fetch_assoc($InventoryQuery))
		{
			$id				= $rows['id'];
			$categoryid		= $rows['categoryid'];
			$name			= $rows['name'];
			$days			= "";
			$price			= "";
			$pricingtype	= 1; /*0 - day base, 1 - date base*/

			if(!empty($ClientInventoryPricing[$id]))
			{
				$days			= $ClientInventoryPricing[$id]['days'];
				$price			= $ClientInventoryPricing[$id]['price'];
				$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
			}
			$idarray[]			= $id; 
			$categoryidarray[]	= $categoryid; 
			$namearray[]		= $name; 
			$daysarray[]		= $days; 
			$pricingtypearray[]	= $pricingtype; 
			$pricearray[]		= $price; 
		}
	}

	$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status=:status AND inv.id=:inventoryid ORDER BY inv.name ASC";
	$InventoryEsql2	= array("inventoryid"=>(int)$_POST['inventoryid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,"status"=>1);

	$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
	$InventoryNum2		= pdo_num_rows($InventoryQuery2);

	if($InventoryNum2 > 0)
	{
		while($rows2 = pdo_fetch_assoc($InventoryQuery2))
		{
			$id				= $rows2['id'];
			$categoryid		= $rows2['categoryid'];
			$name			= $rows2['name'];
			$days			= "";
			$price			= "";
			$pricingtype	= 1; /*0 - day base, 1 - date base*/

			if(!empty($ClientInventoryPricing[$id]))
			{
				$days			= $ClientInventoryPricing[$id]['days'];
				$price			= $ClientInventoryPricing[$id]['price'];
				$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
			}
			$idarray[]			= $id; 
			$categoryidarray[]	= $categoryid; 
			$namearray[]		= $name; 
			$daysarray[]		= $days; 
			$pricingtypearray[]	= $pricingtype; 
			$pricearray[]		= $price; 
		}	
	}
	
	if(!empty($namearray))
	{
		$sortnamearray = array_map("strtolower",$namearray);

		array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$pricingtypearray,$daysarray);
		
		foreach($namearray as $key => $value)
		{
			$id				= $idarray[$key];
			$categoryid		= $categoryidarray[$key];
			$name			= $value;
			$days			= $daysarray[$key];
			$pricingtype	= $pricingtypearray[$key];
			$price			= $pricearray[$key];

			if(!empty($ClientInventoryData[$id]))
			{
				if($ClientInventoryData[$id]['status'] > 0)
				{
					$InventoryListArr['id']				= (int)$id;
					$InventoryListArr['categoryid']		= (int)$categoryid;
					$InventoryListArr['name']			= $name;
					$InventoryListArr['days']			= $days;
					$InventoryListArr['price']			= $price;

					$dayindex	= 0;

					$dateListArr	= array();
					$isbydatepricingavialable = 0;
					for($dateloop = 1; $dateloop <= $totaldays; $dateloop++)
					{
						$date	= $dateloop;
						$price	= "";

						$canshowrow	= false;
						$hasholiday	= false;

						if(!empty($ClientInventoryPricingByDate[$id]))
						{
							$PricingByDate	= $ClientInventoryPricingByDate[$id];

							if(!empty($PricingByDate[$dateloop]))
							{
								$price	= $PricingByDate[$dateloop]['price'];
							}
						}

						if($price > 0)
						{
							$canshowrow	= true;
						}

						$datetimestamp	= strtotime($_POST['month']."/".$dateloop."/".$_POST['year']);

						if(!empty($HolidayArr))
						{
							foreach($HolidayArr as $HolidayKey=>$HolidayRows)
							{
								$inventoryid	= $HolidayRows['inventoryid'];
								$startdate		= $HolidayRows['startdate'];
								$enddate		= $HolidayRows['enddate'];

								if($inventoryid > 0)
								{
									if(($datetimestamp >= $startdate && $datetimestamp <= $enddate) && $inventoryid == $id)
									{
										$canshowrow	= true;
										$hasholiday	= true;
										break;
									}
								}
								else
								{
									if($datetimestamp >= $startdate && $datetimestamp <= $enddate)
									{
										$canshowrow	= true;
										$hasholiday	= true;
										break;
									}
								}
							}
						}
						if($canshowrow)
						{
							if($dateloop < 10)
							{
								$dateListArr[$dayindex]['displayname']	= "0".$dateloop;
							}
							else
							{
								$dateListArr[$dayindex]['displayname']	= $dateloop;
							}

							$dateListArr[$dayindex]['date']			= $dateloop;
							$dateListArr[$dayindex]['hasholiday']	= false;
							$dateListArr[$dayindex]['dateprice']	= $price;
							$dateListArr[$dayindex]['hasholiday']	= $hasholiday;

							$dayindex++;
						}

						if($isbydatepricingavialable < 1)
						{
							if($price > 0.1)
							{
								$isbydatepricingavialable = 1;
							}
						}
					}
					$InventoryListArr['datepricing']	= $dateListArr;
					if($isbydatepricingavialable > 0)
					{
						$pricingtype = '1';
					}
					$InventoryListArr['pricingtype']	= $pricingtype;

					/*$inventoryprice		= $ClientInventoryData[$id]['price'];
					$InventoryListArr[$index]['price']	= (float)$inventoryprice;
					$InventoryListArr[$index]['isassigned']	= true;*/

					$index++;
				}
			}
		}
	}

	if(!empty($InventoryListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer Inventory listed successfully.";
	}
	$response['inventorylist']	= $InventoryListArr;
	$response['monthname']		= $monthname;
	$response['totaldays']		= (int)$totaldays;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMonthlyBillOfNewspaper")
{
	$catindex	= 0;
	$RecordListArr	= array();

	/*$StartDate	= strtotime('today');*/
	$_POST["month"]	= (int)date("m",strtotime($_POST['startdate']));
	$_POST["year"]	= date("Y",strtotime($_POST['startdate']));
	$monthname		= date("F",strtotime($_POST['startdate']));

	if ($_POST['year']%4 == 0)
	{
		$daysInMonth = array(1=>31, 2=>29, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}
	else
	{
		$daysInMonth = array(1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}

	$totaldays	= $daysInMonth[$_POST['month']];
	$HolidayArr	= GetHoliday($_POST['clientid']);

	//echo $_POST['month']."---".$_POST['year'];

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$CatCond		= "";
	$CategoryEsql	= array("status"=>1);

	if($_POST['catid'] > 0)
	{
		$CatCond			.= " AND id=:id";
		$CategoryEsql['id']	= (int)$_POST['catid'];
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$CatCond." ORDER BY orderby ASC";

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData	= GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		$ActiveSubscriptionsData	= GetActiveCustomerSubscriptions($_POST['clientid']);

		$ClientInventoryPricing	= ClientInventoryPricing($_POST['clientid'],$_POST["year"],$_POST["month"]);
		$ClientInventoryPricingByDate	= ClientInventoryPricingByDate($_POST['clientid'],$_POST["year"],$_POST["month"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();
			$idarray			= array();
			$categoryidarray	= array();
			$namearray			= array();
			$pricingtypearray	= array();
			$pricearray			= array();
			$daysarray			= array();
			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$index	= 0;

				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id				= $rows['id'];
					$categoryid		= $rows['categoryid'];
					$name			= $rows['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 1; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}
					$idarray[]			= $id; 
					$categoryidarray[]	= $categoryid; 
					$namearray[]		= $name; 
					$daysarray[]		= $days; 
					$pricingtypearray[]	= $pricingtype; 
					$pricearray[]		= $price; 
				}
			}
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status=:status AND categoryid=:categoryid ORDER BY inv.name ASC";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,"status"=>1);

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2	= pdo_num_rows($InventoryQuery2);
			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id				= $rows2['id'];
					$categoryid		= $rows2['categoryid'];
					$name			= $rows2['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 1; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}
					$idarray[]			= $id; 
					$categoryidarray[]	= $categoryid; 
					$namearray[]		= $name; 
					$daysarray[]		= $days; 
					$pricingtypearray[]	= $pricingtype; 
					$pricearray[]		= $price; 
				}	
			}
			
			if(!empty($namearray))
			{
				$sortnamearray = array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$pricingtypearray,$daysarray);
				
				foreach($namearray as $key => $value)
				{
					$id				= $idarray[$key];
					$categoryid		= $categoryidarray[$key];
					$name			= $value;
					$days			= $daysarray[$key];
					$pricingtype	= $pricingtypearray[$key];
					$price			= $pricearray[$key];
			
					/*if(!empty($ClientInventoryData[$id]) && !empty($ActiveSubscriptionsData[$id]))*/
					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$InventoryListArr[$index]['id']				= (int)$id;
							$InventoryListArr[$index]['categoryid']		= (int)$categoryid;
							$InventoryListArr[$index]['name']			= $name;
							$InventoryListArr[$index]['days']			= $days;
							$InventoryListArr[$index]['price']			= $price;

							$dayindex	= 0;

							$dateListArr	= array();
							$isbydatepricingavialable = 0;
							for($dateloop = 1; $dateloop <= $totaldays; $dateloop++)
							{
								$date	= $dateloop;
								$price	= "";

								$canshowrow	= false;
								$hasholiday	= false;

								if(!empty($ClientInventoryPricingByDate[$id]))
								{
									$PricingByDate	= $ClientInventoryPricingByDate[$id];

									if(!empty($PricingByDate[$dateloop]))
									{
										$price	= $PricingByDate[$dateloop]['price'];
									}
								}

								if($price > 0)
								{
									$canshowrow	= true;
								}

								$datetimestamp	= strtotime($_POST['month']."/".$dateloop."/".$_POST['year']);

								if(!empty($HolidayArr))
								{
									foreach($HolidayArr as $HolidayKey=>$HolidayRows)
									{
										$inventoryid	= $HolidayRows['inventoryid'];
										$startdate		= $HolidayRows['startdate'];
										$enddate		= $HolidayRows['enddate'];

										if($inventoryid > 0)
										{
											if(($datetimestamp >= $startdate && $datetimestamp <= $enddate) && $inventoryid == $id)
											{
												$canshowrow	= true;
												$hasholiday	= true;
												break;
											}
										}
										else
										{
											if($datetimestamp >= $startdate && $datetimestamp <= $enddate)
											{
												$canshowrow	= true;
												$hasholiday	= true;
												break;
											}
										}
									}
								}
								if($canshowrow)
								{
									if($dateloop < 10)
									{
										$dateListArr[$dayindex]['displayname']	= "0".$dateloop;
									}
									else
									{
										$dateListArr[$dayindex]['displayname']	= $dateloop;
									}

									$dateListArr[$dayindex]['date']			= $dateloop;
									$dateListArr[$dayindex]['hasholiday']	= false;
									$dateListArr[$dayindex]['dateprice']	= $price;
									$dateListArr[$dayindex]['hasholiday']	= $hasholiday;

									$dayindex++;
								}

								if($isbydatepricingavialable < 1)
								{
									if($price > 0.1)
									{
										$isbydatepricingavialable = 1;
									}
								}
							}
							$InventoryListArr[$index]['datepricing']	= $dateListArr;
							if($isbydatepricingavialable > 0)
							{
								$pricingtype = '1';
							}
							$InventoryListArr[$index]['pricingtype']	= $pricingtype;

							/*$inventoryprice		= $ClientInventoryData[$id]['price'];
							$InventoryListArr[$index]['price']	= (float)$inventoryprice;
							$InventoryListArr[$index]['isassigned']	= true;*/

							$index++;
						}
					}
				}
			}
			$RecordListArr[$catindex]['id']			= (int)$catid;
			$RecordListArr[$catindex]['title']		= $cattitle;
			$RecordListArr[$catindex]['recordlist']	= $InventoryListArr;

			$catindex++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer Inventory listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;
	$response['monthname']		= $monthname;
	$response['totaldays']		= (int)$totaldays;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetMonthlyBillOfNewspaperPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate monthly bill of newspaper pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= "monthly-newspaper-bill-detail.pdf";

	$startdate	= "";
	$enddate	= "";

	if(trim($_POST['startdate']) != "")
	{
		$startdate	= strtotime($_POST['startdate']);
	}

	/*$File	= "viewmonthlybillofnewspaper.php?clientid=".$_POST['clientid']."&stateid=".$_POST['stateid']."&cityid=".$_POST['cityid']."&catid=".$_POST['catid']."&catname=".$_POST['catname']."&startdate=".$startdate."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewmonthlybillofnewspaper.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$startdate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "Monthly bill of newspaper pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPurchaseSummary")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch purchase summary.";

	$TotalDropRec	= 0;
	$TotalStockRec	= 0;

	$RecordSetArr			= array();

	$DroppingPointNameArr	= array();
	$DroppingPointQtyArr	= array();

	$InventoryNameArr		= array();
	$InventoryQtyArr		= array();

	if($_POST['clientid'] > 0)
	{
		$Condition		= "";
		$PurchaseEsql	= array("clientid"=>(int)$_POST['clientid']);

		if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
		{
			$StartDate	= strtotime($_POST['startdate']);
			$EndDate	= strtotime($_POST['enddate'])+86399;

			$Condition	.= " AND purchasedate BETWEEN :startdate AND :enddate";

			$PurchaseEsql['startdate']	= $StartDate;
			$PurchaseEsql['enddate']	= $EndDate;
		}

		if($_POST['droppingpointtype'] > 0 && $_POST['droppingpointid'] > 0)
		{
			$Condition	.= " AND droppingpointid=:droppingpointid";
			$PurchaseEsql['droppingpointid']	= (int)$_POST['droppingpointid'];
		}

		if($_POST['isstock'] > 0 && $_POST['inventoryid'] > 0)
		{
			$Condition	.= " AND inventoryid=:inventoryid";
			$PurchaseEsql['inventoryid']	= (int)$_POST['inventoryid'];
		}

		$PurchaseSql	= "SELECT * FROM ".$Prefix."purchase WHERE clientid=:clientid ".$Condition." ORDER BY purchasedate ASC";

		$PurchaseQuery	= pdo_query($PurchaseSql,$PurchaseEsql);
		$PurchaseNum	= pdo_num_rows($PurchaseQuery);

		$PurchaseBreakUpArr		= array();
		$InventoryBreakUpArr	= array();

		if($PurchaseNum > 0)
		{
			$DroppingPointArr	= GetAllDroppingPoint($_POST['clientid']);
			$StockNameArr		= GetInventoryNames();
			 
			while($purchaserows = pdo_fetch_assoc($PurchaseQuery))
			{
				$droppingpointid	= $purchaserows['droppingpointid'];
				$inventoryid		= $purchaserows['inventoryid'];
				$noofpices			= $purchaserows['noofpices'];
				$purchasedate		= date("d-M-Y",$purchaserows['purchasedate']);
				$PurchaseBreakUpArr[$droppingpointid][$purchasedate] += $noofpices; 
				
				$DroppingPointNameArr[$droppingpointid]	= $DroppingPointArr[$droppingpointid]['name'];
				$DroppingPointQtyArr[$droppingpointid]	+= $noofpices;

				$InventoryNameArr[$inventoryid]	= $StockNameArr[$inventoryid]['name'];
				$InventoryQtyArr[$inventoryid]	+= $noofpices;
				$InventoryBreakUpArr[$inventoryid][$purchasedate] += $noofpices; 
			}

			$DroppingPointRecordArr	= array();
			$InventoryRecordArr		= array();

			$index	= 0;

			foreach($DroppingPointNameArr as $id => $name)
			{
				$datebreakup = array();
				if(!empty($PurchaseBreakUpArr[$id]))
				{
					$loop = 0;
					foreach($PurchaseBreakUpArr[$id] as $key => $value)
					{
						$datebreakup[$loop] = array("date"=>$key,"qty"=>$value);
					$loop++;
					}
				}

				$DroppingPointRecordArr[$index]['id']		= $id;
				$DroppingPointRecordArr[$index]['serialno']	= $index+1;
				$DroppingPointRecordArr[$index]['name']		= $name;
				$DroppingPointRecordArr[$index]['datesbreakup']= $datebreakup;
				$DroppingPointRecordArr[$index]['qty']		= $DroppingPointQtyArr[$id];

				$TotalDropRec++;
				$index++;
			}

			$index	= 0;

			foreach($InventoryNameArr as $id => $name)
			{
				$datebreakup = array();
				if(!empty($InventoryBreakUpArr[$id]))
				{
					$loop = 0;
					foreach($InventoryBreakUpArr[$id] as $key => $value)
					{
						$datebreakup[$loop] = array("date"=>$key,"qty"=>$value);
						$loop++;
					}
				}
				$InventoryRecordArr[$index]['id']		= $id;
				$InventoryRecordArr[$index]['serialno']	= $index+1;
				$InventoryRecordArr[$index]['name']		= $name;
				$InventoryRecordArr[$index]['datesbreakup']	= $datebreakup;
				$InventoryRecordArr[$index]['qty']		= $InventoryQtyArr[$id];

				$TotalStockRec++;
				$index++;
			}

			$RecordSetArr['droppingpointdata']	= $DroppingPointRecordArr;
			$RecordSetArr['inventorydata']		= $InventoryRecordArr;

			$response['success']		= true;
			$response['msg']			= "Purchase summary report fetched successfully.";
			$response['recordset']		= $RecordSetArr;
			$response['totaldroppoint']	= $TotalDropRec;
			$response['totalstock']		= $TotalStockRec;
		}
	}
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetCloseCustomerList")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch close customer list.";

	$TotalRec		= 0;

	$RecordSetArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		/*$Condition	.= " AND log.subscriptiondate BETWEEN :startdate AND :enddate AND log.unsubscribedate BETWEEN :startdate2 AND :enddate2 ";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;

		$ESQL['startdate2']	= $StartDate;
		$ESQL['enddate2']	= $EndDate;*/

		$Condition	.= " AND log.unsubscribedate BETWEEN :startdate2 AND :enddate2 ";

		$ESQL['startdate2']	= $StartDate;
		$ESQL['enddate2']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND log.inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,log.id as logid,log.inventoryid as inventoryid,log.subscriptiondate AS logsubscriptiondate,log.unsubscribedate AS logunsubscribedate FROM ".$Prefix."customers cust, ".$Prefix."subscriptions_log log WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=log.customerid ".$Condition." GROUP BY log.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$TotalRec	= $Num;

	$InventorySummaryArr	= array();

	if($Num > 0)
	{
		$index	= 0;

		$AllAreaArr			= GetAllArea($_POST['clientid']);
		$AllLineArr			= GetAllLine($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);
		$AllInventoryArr	= GetInventoryNames();

		while($rows = pdo_fetch_assoc($Query))
		{
			$logid					= $rows['logid'];
			$custid					= $rows['id'];
			$customerid				= $rows['customerid'];
			$name					= $rows['name'];
			$logsubscriptiondate	= $rows['logsubscriptiondate'];
			$logunsubscribedate		= $rows['logunsubscribedate'];
			$inventoryid			= $rows['inventoryid'];
			$areaid					= $rows['areaid'];
			$lineid					= $rows['lineid'];
			$phone					= $rows['phone'];
			$housenumber			= $rows['housenumber'];
			$floor					= $rows['floor'];
			$address1				= $rows['address1'];
			$sublinename			= $GetAllSubLine[$rows['sublineid']]['name'];

			$name2					= "#".$customerid." ".$name;

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

			$logsubscriptiondate	= date("d-M-Y",$logsubscriptiondate);
			$logunsubscribedate		= date("d-M-Y",$logunsubscribedate);

			$InventorySummaryArr[$inventoryid]['name']	= $AllInventoryArr[$inventoryid]['name'];
			$InventorySummaryArr[$inventoryid]['qty']	+= 1;

			$InventorySummaryArr[$inventoryid]['detail'][$logid]['custid']		= $custid;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['customerid']	= $customerid;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['name2']			= $name2;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['phone']			= $phone;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['areaid']		= $areaid;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['area']			= $AllAreaArr[$areaid]['name'];
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['line']			= $AllLineArr[$lineid]['name'];
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['fulladdress']	= $addresstr;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['logsubscriptiondate']	= $logsubscriptiondate;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['logunsubscribedate']	= $logunsubscribedate;
		}
	}

	$index		= 0;
	$TotalRec	= 0;

	if(!empty($InventorySummaryArr))
	{
		foreach($InventorySummaryArr as $inventoryid=>$rows)
		{
			$detailArr	= array();

			if(!empty($rows['detail']))
			{
				$detailindex	= 0;

				foreach($rows['detail'] as $logid=>$logrows)
				{
					$detailArr[$detailindex]['logid']				= $logid;
					$detailArr[$detailindex]['custid']				= $logrows['custid'];
					$detailArr[$detailindex]['customerid']			= $logrows['customerid'];
					$detailArr[$detailindex]['customerid']			= $logrows['customerid'];
					$detailArr[$detailindex]['name2']				= $logrows['name2'];
					$detailArr[$detailindex]['phone']				= $logrows['phone'];
					$detailArr[$detailindex]['areaid']				= $logrows['areaid'];
					$detailArr[$detailindex]['area']				= $logrows['area'];
					$detailArr[$detailindex]['line']				= $logrows['line'];
					$detailArr[$detailindex]['fulladdress']			= $logrows['fulladdress'];
					$detailArr[$detailindex]['logsubscriptiondate']	= $logrows['logsubscriptiondate'];
					$detailArr[$detailindex]['logunsubscribedate']	= $logrows['logunsubscribedate'];

					$TotalRec++;

					$detailindex++;
				}
			}

			if(!empty($detailArr))
			{
				$RecordSetArr[$index]['id']		= $inventoryid;
				$RecordSetArr[$index]['name']	= $rows['name'];
				$RecordSetArr[$index]['qty']	= $rows['qty'];
				$RecordSetArr[$index]['detail']	= $detailArr;

				$index++;
			}
		}
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Close customer listed successfully.";
	}
	$response['recordset']		= $RecordSetArr;
	$response['totalrecord']	= $TotalRec;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetCloseCustomerListPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate close customer list pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= 'close-customer.pdf';

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")

	$StartDate	= "";
	$EndDate	= "";

	if(trim($_POST['startdate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
	}

	if(trim($_POST['enddate']) != "")
	{
		$EndDate	= strtotime($_POST['enddate']);
	}

	$File 		= "closecustomer.php?clientid=".$_POST['clientid']."&startdate=".$StartDate."&enddate=".$EndDate."&areaid=".$_POST['areaid']."&lineid=".$_POST['lineid']."&inventoryid=".$_POST['inventoryid']."&areamanagerid=".$_POST['areamanagerid']."&areaids=".$_POST['areaids']."&islineman=".$_POST['islineman']."&linemanareaid=".$_POST['linemanareaid']."&linemanlineids=".$_POST['linemanlineids']."&linemanid=".$_POST['linemanid']."&islineman=".$_POST['islineman']."&ishawker=".$_POST['ishawker']."&hawkerareaid=".$_POST['hawkerareaid']."&hawkerlineids=".$_POST['hawkerlineids']."&bulkprinting=1&downloadpdf=1";

	$ServerAPIURL		= "http://".$SiteDomainName."/api/";

	if($_SERVER['IsLocal'] == 'Yes')
	{
		$ServerAPIURL		= "http://orlopay/agency/api/";
	}

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "Close customer pdf generated successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetStaffDOB")
{
    $response['success']		= false;
	$response['clientstatus']	= true;
    $response['msg']			= "Unable to fetch staff dob.";

	$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
	$CheckEsql	= array("id"=>(int)$_POST['clientid'],"clienttype"=>2,"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);

	if(is_array($CheckQuery))
	{
		$response['msg']	= $CheckQuery['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	if($_POST['areamanagerid'] < 1 && $_POST['loginlinemanid'] < 1 && $_POST['loginhawkerid'] < 1)
	{
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$checkrows		= pdo_fetch_assoc($CheckQuery);

			$clientstatus		= $checkrows['status'];
			$clientauthtoken	= $checkrows['authtoken'];

			if($clientstatus < 1)
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}
			else
			{
				$authtoken	= $checkrows['authtoken'];

				if(($authtoken != $_POST['authtoken']) || $authtoken == "")
				{
					$response['success']		= true;
					$response['clientstatus']	= false;
					$response['msg']			= "Account validation failed! logging out account";

					$json = json_encode($response);
					echo $json;
					die;
				}

				$response['id']					= (int)$checkrows['id'];
				$response['clientname']			= $checkrows['clientname'];
				$response['clienttype']			= (int)$checkrows['clienttype'];
				$response['ispasswordupdate']	= (int)$checkrows['ispasswordupdate'];
				$isbetaaccount					= (int)$checkrows['accounttype'];

				$clientdetail	= array("id"=>(int)$checkrows['id'],"clientname"=>$checkrows['clientname'],"clientphone"=>$checkrows['phone1'],"clienttype"=>(int)$checkrows['clienttype'],"ispasswordupdate"=>(int)$checkrows['ispasswordupdate'],"stateid"=>(int)$checkrows['stateid'],"cityid"=>(int)$checkrows['cityid'],"isbetaaccount"=>$isbetaaccount,"pincode"=>$checkrows['pincode'],"linemanid"=>0,"islineman"=>false,"ismanager"=>false,"areaids"=>"","personname"=>$checkrows['contactname']);

				$clientarr = array_merge($permarr,$clientdetail);

				$accesstoken = array(
				   "iss" => $jwtiss,
				   "aud" => $jwtaud,
				   "iat" => $jwtiat,
				   "nbf" => $jwtnbf,
				   "isadminlogin" => false,
				   "adminid" => 0,
				   "clientdata" => $clientarr,
				   "authtoken" => $authtoken
				);

				$jwt = JWT::encode($accesstoken, $jwtkey);

				$response['clientdetail']	= $clientarr;
				$response['logintime']		= $createdon;
				$response['accesstoken']	= $jwt;
			}
		}
	}

	if($_POST['areamanagerid'] > 0)
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."area_manager WHERE id=:id AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("id"=>(int)$_POST['areamanagerid'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum < 1)
		{
			$response['success']		= true;
			$response['clientstatus']	= false;
			$response['msg']			= "Account validation failed! logging out account";

			$json = json_encode($response);
			echo $json;
			die;
		}
		else
		{
			$checkrows	= pdo_fetch_assoc($CheckQuery);

			$authtoken	= $checkrows['authtoken'];

			if(($authtoken != $_POST['authtoken']) || $authtoken == "")
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$response['id']					= (int)$checkrows['id'];
			$response['clientname']			= $checkrows['name'];
			$response['ispasswordupdate']	= 1;
			$response['islinemanlogin']		= false;
			$response['ismanagerlogin']		= true;

			$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
			$ClientEsql	= array("id"=>(int)$checkrows['clientid'],"clienttype"=>2,"deletedon"=>1);

			$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
			$clientrows		= pdo_fetch_assoc($ClientQuery);

			$clientstatus	= $clientrows['status'];

			if($clientstatus < 1)
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$AllAssignedArea	= GetAllAssignedAreaByAreaManager($checkrows['clientid'],$checkrows['id']);

			$AllAssignedAreaStr	= @implode(",",@array_filter(@array_unique($AllAssignedArea)));

			if(trim($AllAssignedAreaStr) == "")
			{
				$AllAssignedAreaStr	= "-1";
			}

			$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
			$permesql	= array("managerid"=>(int)$checkrows['id'],"usertype"=>"manager");
			
			$permquery	= pdo_query($permsql,$permesql);
			$permnum	= pdo_num_rows($permquery);

			if($permnum > 0)
			{
				$permissionrow	= pdo_fetch_assoc($permquery);

				$permissionrow['canareamanager']	= 0;
				$permissionrow['cansettings']		= 0;
				$permissionrow['changepassword']	= 1;

				foreach($permarr as $key=>$val)
				{
					if(array_key_exists($key,$permissionrow))
					{
						$AllowedPerm[$key]	= (int)$permissionrow[$key];
					}
				}

				if($AllowedPerm["canreports"] > 0)
				{
					$reppermsql	= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
					$reppermesql	= array("managerid"=>(int)$checkrows['id'],"usertype"=>"manager");

					$reppermquery	= pdo_query($reppermsql,$reppermesql);
					$reppermnum		= pdo_num_rows($reppermquery);

					if($reppermnum > 0)
					{
						$reppermissionrow	= pdo_fetch_assoc($reppermquery);

						foreach($permarr as $key=>$val)
						{
							if(array_key_exists($key,$reppermissionrow))
							{
								$AllowedPerm[$key]	= (int)$reppermissionrow[$key];
							}
						}
					}
				}
			}
			else
			{
				foreach($permarr as $key=>$val)
				{
					$AllowedPerm[$key]	= 0;
				}
			}

			$clientdetail	= array(
				"id"				=>(int)$checkrows['clientid'],
				"areamanagerid"		=>(int)$checkrows['id'],
				"clientname"		=>$clientrows['clientname'],
				"clienttype"		=>(int)$clientrows['clienttype'],
				"ispasswordupdate"	=>1,
				"stateid"			=>(int)$clientrows['stateid'],
				"cityid"			=>(int)$clientrows['cityid'],
				"isbetaaccount"		=>(int)$clientrows['accounttype'],
				"pincode"			=>$clientrows['pincode'],
				"clientphone"		=>$checkrows['phone'],
				"islineman"			=>0,
				"ismanager"			=>1,
				"areaids"			=>$AllAssignedAreaStr,
				"personname"		=>$checkrows['name']
			);

			$clientarr = array_merge($AllowedPerm,$clientdetail);

			/*set response code - 200 OK*/
			http_response_code(200);

			$accesstoken = array(
			   "iss" => $jwtiss,
			   "aud" => $jwtaud,
			   "iat" => $jwtiat,
			   "nbf" => $jwtnbf,
			   "isadminlogin" => false,
			   "adminid" => 0,
			   "clientdata" => $clientarr,
			   "authtoken" => $authtoken
			);

			$jwt = JWT::encode($accesstoken, $jwtkey);

			$response['clientdetail']	= $clientarr;
			$response['accesstoken']	= $jwt;
			$response['logintime']		= $createdon;
		}
	}

	if($_POST['loginlinemanid'] > 0)
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE id=:id AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("id"=>(int)$_POST['loginlinemanid'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum < 1)
		{
			$response['success']		= true;
			$response['clientstatus']	= false;
			$response['msg']			= "Account validation failed! logging out account";

			$json = json_encode($response);
			echo $json;
			die;
		}
		else
		{
			$checkrows	= pdo_fetch_assoc($CheckQuery);

			$authtoken	= $checkrows['authtoken'];

			if(($authtoken != $_POST['authtoken']) || $authtoken == "")
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$response['id']					= (int)$checkrows['id'];
			$response['clientname']			= $checkrows['name'];
			$response['ispasswordupdate']	= 1;
			$response['islinemanlogin']		= 1;
			$response['ismanagerlogin']		= 0;

			$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
			$ClientEsql	= array("id"=>(int)$checkrows['clientid'],"clienttype"=>2,"deletedon"=>1);

			$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
			$clientrows		= pdo_fetch_assoc($ClientQuery);

			$clientstatus	= $clientrows['status'];

			if($clientstatus < 1)
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$clientdetail	= array(
				"id"				=>(int)$checkrows['clientid'],
				"linemanid"			=>(int)$checkrows['id'],
				"clientname"		=>$clientrows['clientname'],
				"clienttype"		=>(int)$clientrows['clienttype'],
				"ispasswordupdate"	=>1,
				"stateid"			=>(int)$clientrows['stateid'],
				"cityid"			=>(int)$clientrows['cityid'],
				"isbetaaccount"		=>(int)$clientrows['accounttype'],
				"pincode"			=>$clientrows['pincode'],
				"clientphone"		=>$checkrows['phone'],
				"islineman"			=>1,
				"ismanager"			=>0,
				"areaids"			=>"",
				"personname"		=>$checkrows['name']
			);

			$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
			$permesql	= array("managerid"=>(int)$checkrows['id'],"usertype"=>"lineman");

			$permquery	= pdo_query($permsql,$permesql);
			$permnum	= pdo_num_rows($permquery);

			if($permnum > 0)
			{
				$permissionrow	= pdo_fetch_assoc($permquery);

				$permissionrow['canareamanager']	= 0;
				$permissionrow['cansettings']		= 0;
				$permissionrow['changepassword']	= 1;

				foreach($permarr as $key=>$val)
				{
					if(array_key_exists($key,$permissionrow))
					{
						$AllowedPerm[$key]	= (int)$permissionrow[$key];
					}
				}

				if($AllowedPerm["canreports"] > 0)
				{
					$reppermsql	= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
					$reppermesql	= array("managerid"=>(int)$checkrows['id'],"usertype"=>"lineman");

					$reppermquery	= pdo_query($reppermsql,$reppermesql);
					$reppermnum		= pdo_num_rows($reppermquery);

					if($reppermnum > 0)
					{
						$reppermissionrow	= pdo_fetch_assoc($reppermquery);

						foreach($permarr as $key=>$val)
						{
							if(array_key_exists($key,$reppermissionrow))
							{
								$AllowedPerm[$key]	= (int)$reppermissionrow[$key];
							}
						}
					}
				}
			}
			else
			{
				foreach($permarr as $key=>$val)
				{
					$AllowedPerm[$key]	= 0;
				}
			}

			$clientarr = array_merge($AllowedPerm,$clientdetail);

			/*set response code - 200 OK*/
			http_response_code(200);

			$accesstoken = array(
			   "iss" => $jwtiss,
			   "aud" => $jwtaud,
			   "iat" => $jwtiat,
			   "nbf" => $jwtnbf,
			   "isadminlogin" => false,
			   "adminid" => 0,
			   "clientdata" => $clientarr,
			   "authtoken" => $authtoken
			);

			$jwt = JWT::encode($accesstoken, $jwtkey);

			$response['clientdetail']	= $clientarr;
			$response['accesstoken']	= $jwt;
			$response['logintime']		= $createdon;
		}
	}

	if($_POST['loginhawkerid'] > 0)
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE id=:id AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("id"=>(int)$_POST['loginhawkerid'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum < 1)
		{
			$response['success']		= true;
			$response['clientstatus']	= false;
			$response['msg']			= "Account validation failed! logging out account";

			$json = json_encode($response);
			echo $json;
			die;
		}
		else
		{
			$checkrows	= pdo_fetch_assoc($CheckQuery);

			$authtoken	= $checkrows['authtoken'];

			if(($authtoken != $_POST['authtoken']) || $authtoken == "")
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$response['id']					= (int)$checkrows['id'];
			$response['clientname']			= $checkrows['name'];
			$response['ispasswordupdate']	= 1;
			$response['ishawkerlogin']		= 1;
			$response['islinemanlogin']		= 0;
			$response['ismanagerlogin']		= 0;

			$ClientSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
			$ClientEsql	= array("id"=>(int)$checkrows['clientid'],"clienttype"=>2,"deletedon"=>1);

			$ClientQuery	= pdo_query($ClientSql,$ClientEsql);
			$clientrows		= pdo_fetch_assoc($ClientQuery);

			$clientstatus	= $clientrows['status'];

			if($clientstatus < 1)
			{
				$response['success']		= true;
				$response['clientstatus']	= false;
				$response['msg']			= "Account validation failed! logging out account";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$clientdetail	= array(
				"id"				=>(int)$checkrows['clientid'],
				"hawkerid"			=>(int)$checkrows['id'],
				"clientname"		=>$clientrows['clientname'],
				"clienttype"		=>(int)$clientrows['clienttype'],
				"ispasswordupdate"	=>1,
				"stateid"			=>(int)$clientrows['stateid'],
				"cityid"			=>(int)$clientrows['cityid'],
				"isbetaaccount"		=>(int)$clientrows['accounttype'],
				"pincode"			=>$clientrows['pincode'],
				"clientphone"		=>$checkrows['phone'],
				"islineman"			=>0,
				"ismanager"			=>0,
				"ishawker"			=>1,
				"areaids"			=>"",
				"personname"		=>$checkrows['name']
			);

			$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
			$permesql	= array("managerid"=>(int)$checkrows['id'],"usertype"=>"hawker");

			$permquery	= pdo_query($permsql,$permesql);
			$permnum	= pdo_num_rows($permquery);

			if($permnum > 0)
			{
				$permissionrow	= pdo_fetch_assoc($permquery);

				$permissionrow['canareamanager']	= 0;
				$permissionrow['cansettings']		= 0;
				$permissionrow['changepassword']	= 1;

				foreach($permarr as $key=>$val)
				{
					if(array_key_exists($key,$permissionrow))
					{
						$AllowedPerm[$key]	= (int)$permissionrow[$key];
					}
				}

				if($AllowedPerm["canreports"] > 0)
				{
					$reppermsql	= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
					$reppermesql	= array("managerid"=>(int)$checkrows['id'],"usertype"=>"hawker");

					$reppermquery	= pdo_query($reppermsql,$reppermesql);
					$reppermnum		= pdo_num_rows($reppermquery);

					if($reppermnum > 0)
					{
						$reppermissionrow	= pdo_fetch_assoc($reppermquery);

						foreach($permarr as $key=>$val)
						{
							if(array_key_exists($key,$reppermissionrow))
							{
								$AllowedPerm[$key]	= (int)$reppermissionrow[$key];
							}
						}
					}
				}
			}
			else
			{
				foreach($permarr as $key=>$val)
				{
					$AllowedPerm[$key]	= 0;
				}
			}

			$clientarr = array_merge($AllowedPerm,$clientdetail);

			/*set response code - 200 OK*/
			http_response_code(200);

			$accesstoken = array(
			   "iss" => $jwtiss,
			   "aud" => $jwtaud,
			   "iat" => $jwtiat,
			   "nbf" => $jwtnbf,
			   "isadminlogin" => false,
			   "adminid" => 0,
			   "clientdata" => $clientarr,
			   "authtoken" => $authtoken
			);

			$jwt = JWT::encode($accesstoken, $jwtkey);

			$response['clientdetail']	= $clientarr;
			$response['accesstoken']	= $jwt;
			$response['logintime']		= $createdon;
		}
	}

	$todaytimestamp		= time();
	$tomorrowtimestamp	= strtotime('tomorrow');

	$todaydate		= date("d",$todaytimestamp);
	$todaymonth		= date("m",$todaytimestamp);

	$tomorrowdate	= date("d",$tomorrowtimestamp);
	$tomorrowmonth	= date("m",$tomorrowtimestamp);

	$totalbirthdaycount		= 0;

	$TodayStaffListArr		= array();
	$TomarrowStaffListArr	= array();

	$todayindex		= 0;
	$tomorrowindex	= 0;

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Sql	= "SELECT * FROM ".$Prefix."area_manager WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$AssignedLineArr	= array();

			$id		= $rows['id'];
			$name	= $rows['name'];
			$phone	= $rows['phone'];
			$hasdob	= $rows['hasdob'];
			$dob	= $rows['dob'];

			/*$AssignedLineSql	= "SELECT line.* FROM ".$Prefix."line line, ".$Prefix."assigned_area_linker linker WHERE linker.managerid=:managerid AND linker.areaid=line.areaid AND line.deletedon < :deletedon AND line.status=:status";

			$AssignedLineEsql	= array("managerid"=>(int)$id,"deletedon"=>1,"status"=>1);*/

			$AssignedLineSql	= "SELECT area.* FROM ".$Prefix."area area, ".$Prefix."assigned_area_linker linker WHERE linker.managerid=:managerid AND linker.areaid=area.id AND area.deletedon < :deletedon AND area.status=:status";

			$AssignedLineEsql	= array("managerid"=>(int)$id,"deletedon"=>1,"status"=>1);

			$AssignedLineQuery	= pdo_query($AssignedLineSql,$AssignedLineEsql);
			$AssignedLineNum	= pdo_num_rows($AssignedLineQuery);

			if($AssignedLineNum > 0)
			{
				while($AssignedLineRows = pdo_fetch_assoc($AssignedLineQuery))
				{
					$linename	= $AssignedLineRows['name'];
					$AssignedLineArr[]	= $linename;
				}
			}

			$AssignedLineArr	= @array_filter(@array_unique($AssignedLineArr));
			$AssignedLineStr	= "";

			if(!empty($AssignedLineArr))
			{
				$AssignedLineStr	= implode(", ",$AssignedLineArr);
			}

			if(($hasdob > 0) && ($dob != "" && $dob > 0))
			{
				$dobdate	= date("d",$dob);
				$dobmonth	= date("m",$dob);

				if(($dobdate == $todaydate) && ($dobmonth == $todaymonth))
				{
					$TodayStaffListArr[$todayindex]['name']			= $name;
					$TodayStaffListArr[$todayindex]['phone']		= $phone;
					$TodayStaffListArr[$todayindex]['designation']	= "Area Manager";
					$TodayStaffListArr[$todayindex]['sortcode']		= "AM";
					$TodayStaffListArr[$todayindex]['lines']		= $AssignedLineStr;

					$todayindex++;
					$totalbirthdaycount++;
				}

				if(($dobdate == $tomorrowdate) && ($dobmonth == $tomorrowmonth))
				{
					$TomarrowStaffListArr[$tomorrowindex]['name']			= $name;
					$TomarrowStaffListArr[$tomorrowindex]['phone']			= $phone;
					$TomarrowStaffListArr[$tomorrowindex]['designation']	= "Area Manager";
					$TomarrowStaffListArr[$tomorrowindex]['sortcode']		= "AM";
					$TomarrowStaffListArr[$tomorrowindex]['lines']			= $AssignedLineStr;

					$tomorrowindex++;
					$totalbirthdaycount++;
				}
			}
		}
	}

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$AssignedLineArr	= array();

			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$hasdob		= $rows['hasdob'];
			$dob		= $rows['dob'];

			$LineIdArr	= explode("::",$rows['lineids']);
			$LineIdArr	= @array_filter(@array_unique($LineIdArr));

			$LineIdStr	= implode(",",$LineIdArr);

			if(trim($LineIdStr) == "")
			{
				$LineIdStr	= "-1";
			}

			$AssignedLineSql	= "SELECT * FROM ".$Prefix."line WHERE 1 AND deletedon < :deletedon AND status=:status AND id IN (".$LineIdStr.") ORDER BY name ASC";
			$AssignedLineEsql	= array("deletedon"=>1,"status"=>1);

			$AssignedLineQuery	= pdo_query($AssignedLineSql,$AssignedLineEsql);
			$AssignedLineNum	= pdo_num_rows($AssignedLineQuery);

			if($AssignedLineNum > 0)
			{
				while($AssignedLineRows = pdo_fetch_assoc($AssignedLineQuery))
				{
					$linename	= $AssignedLineRows['name'];
					$AssignedLineArr[]	= $linename;
				}
			}

			$AssignedLineArr	= @array_filter(@array_unique($AssignedLineArr));
			$AssignedLineStr	= "";

			if(!empty($AssignedLineArr))
			{
				$AssignedLineStr	= implode(", ",$AssignedLineArr);
			}

			if(($hasdob > 0) && ($dob != "" && $dob > 0))
			{
				$dobdate	= date("d",$dob);
				$dobmonth	= date("m",$dob);

				if(($dobdate == $todaydate) && ($dobmonth == $todaymonth))
				{
					$TodayStaffListArr[$todayindex]['name']			= $name;
					$TodayStaffListArr[$todayindex]['phone']		= $phone;
					$TodayStaffListArr[$todayindex]['designation']	= "Lineman";
					$TodayStaffListArr[$todayindex]['sortcode']		= "LN";
					$TodayStaffListArr[$todayindex]['lines']		= $AssignedLineStr;

					$todayindex++;
					$totalbirthdaycount++;
				}

				if(($dobdate == $tomorrowdate) && ($dobmonth == $tomorrowmonth))
				{
					$TomarrowStaffListArr[$tomorrowindex]['name']			= $name;
					$TomarrowStaffListArr[$tomorrowindex]['phone']			= $phone;
					$TomarrowStaffListArr[$tomorrowindex]['designation']	= "Lineman";
					$TomarrowStaffListArr[$tomorrowindex]['sortcode']		= "LN";
					$TomarrowStaffListArr[$tomorrowindex]['lines']			= $AssignedLineStr;

					$tomorrowindex++;
					$totalbirthdaycount++;
				}
			}
		}
	}

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$AssignedLineArr	= array();

			$name	= $rows['name'];
			$phone	= $rows['phone'];
			$hasdob	= $rows['hasdob'];
			$dob	= $rows['dob'];

			$LineIdArr	= explode("::",$rows['lineids']);
			$LineIdArr	= @array_filter(@array_unique($LineIdArr));

			$LineIdStr	= implode(",",$LineIdArr);

			if(trim($LineIdStr) == "")
			{
				$LineIdStr	= "-1";
			}

			$AssignedLineSql	= "SELECT * FROM ".$Prefix."line WHERE 1 AND deletedon < :deletedon AND status=:status AND id IN (".$LineIdStr.") ORDER BY name ASC";
			$AssignedLineEsql	= array("deletedon"=>1,"status"=>1);

			$AssignedLineQuery	= pdo_query($AssignedLineSql,$AssignedLineEsql);
			$AssignedLineNum	= pdo_num_rows($AssignedLineQuery);

			if($AssignedLineNum > 0)
			{
				while($AssignedLineRows = pdo_fetch_assoc($AssignedLineQuery))
				{
					$linename	= $AssignedLineRows['name'];
					$AssignedLineArr[]	= $linename;
				}
			}

			$AssignedLineArr	= @array_filter(@array_unique($AssignedLineArr));
			$AssignedLineStr	= "";

			if(!empty($AssignedLineArr))
			{
				$AssignedLineStr	= implode(", ",$AssignedLineArr);
			}

			if(($hasdob > 0) && ($dob != "" && $dob > 0))
			{
				$dobdate	= date("d",$dob);
				$dobmonth	= date("m",$dob);

				if(($dobdate == $todaydate) && ($dobmonth == $todaymonth))
				{
					$TodayStaffListArr[$todayindex]['name']			= $name;
					$TodayStaffListArr[$todayindex]['phone']		= $phone;
					$TodayStaffListArr[$todayindex]['designation']	= "Hawker";
					$TodayStaffListArr[$todayindex]['sortcode']		= "HW";
					$TodayStaffListArr[$todayindex]['lines']		= $AssignedLineStr;

					$todayindex++;
					$totalbirthdaycount++;
				}

				if(($dobdate == $tomorrowdate) && ($dobmonth == $tomorrowmonth))
				{
					$TomarrowStaffListArr[$tomorrowindex]['name']			= $name;
					$TomarrowStaffListArr[$tomorrowindex]['phone']			= $phone;
					$TomarrowStaffListArr[$tomorrowindex]['designation']	= "Hawker";
					$TomarrowStaffListArr[$tomorrowindex]['sortcode']		= "HW";
					$TomarrowStaffListArr[$tomorrowindex]['lines']			= $AssignedLineStr;

					$tomorrowindex++;
					$totalbirthdaycount++;
				}
			}
		}
	}

	$hastodaystaff		= false;
	$hastomarrowstaff	= false;

	if(!empty($TodayStaffListArr))
	{
		$hastodaystaff	= true;
	}

	if(!empty($TomarrowStaffListArr))
	{
		$hastomarrowstaff	= true;
	}

	$ContactEsql	= array("clientid"=>(int)$_POST['clientid'],"status"=>0);

	$ContactSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."contact_request WHERE clientid=:clientid AND status=:status";

	$ContactQuery	= pdo_query($ContactSql,$ContactEsql);
	$ContactRows	= pdo_fetch_assoc($ContactQuery);

	$pendingcontactcount	= (int)$ContactRows['C'];

	if(!empty($TomarrowStaffListArr) || !empty($TodayStaffListArr) || $pendingcontactcount > 0 || !empty($clientarr))
	{
		$response['success']	= true;
		$response['msg']		= "Staff dob and contact request fetched successfully.";
	}

	$RecordListArr['hastodaystaff']			= $hastodaystaff;
	$RecordListArr['todaystafflist']		= $TodayStaffListArr;
	$RecordListArr['hastomarrowstaff']		= $hastomarrowstaff;
	$RecordListArr['tomarrowstafflist']		= $TomarrowStaffListArr;
	$RecordListArr['totalbirthdaycount']	= $totalbirthdaycount;
	$RecordListArr['pendingcontactcount']	= $pendingcontactcount;

	$response['clientstatus']	= true;
	$response['recordset']		= $RecordListArr;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerListByNewspaperPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate customer list by newspaper report pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName	= "outstanding-report.pdf";

	$StartDate	= "";
	$EndDate	= "";

	$startdate		= strtotime($_POST['monthyear']);
	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	if(trim($_POST['monthyear']) != "")
	{
		$StartDate	= strtotime($_POST['monthyear']);
		$EndDate	= strtotime($_POST['enddate'])+86399;
	}

	/*$File	= "viewcustomerlistbynewspaper.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&inventoryid=".$_POST['inventoryid']."&stateid=".$_POST['stateid']."&cityid=".$_POST['cityid']."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewcustomerlistbynewspaper.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "customer list by newspaper report pdf generated successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetRestartCustomerList")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch restart customer list.";

	$TotalRec		= 0;

	$RecordSetArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND sub.subscriptiondate BETWEEN :startdate2 AND :enddate2 ";

		$ESQL['startdate2']	= $StartDate;
		$ESQL['enddate2']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND sub.inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,sub.id as logid,sub.inventoryid as inventoryid,sub.subscriptiondate AS subscriptiondate FROM ".$Prefix."customers cust, ".$Prefix."subscriptions sub WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=sub.customerid ".$Condition." GROUP BY sub.id ORDER BY cust.sequence ASC, cust.customerid ASC, sub.subscriptiondate ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$TotalRec	= $Num;

	$InventorySummaryArr	= array();

	if($Num > 0)
	{
		$index	= 0;

		$AllAreaArr			= GetAllArea($_POST['clientid']);
		$AllLineArr			= GetAllLine($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);
		$AllInventoryArr	= GetInventoryNames();

		while($rows = pdo_fetch_assoc($Query))
		{
			$logid					= $rows['logid'];
			$custid					= $rows['id'];
			$customerid				= $rows['customerid'];
			$name					= $rows['name'];
			$subscriptiondate		= $rows['subscriptiondate'];
			$inventoryid			= $rows['inventoryid'];
			$areaid					= $rows['areaid'];
			$lineid					= $rows['lineid'];
			$phone					= $rows['phone'];
			$housenumber			= $rows['housenumber'];
			$floor					= $rows['floor'];
			$address1				= $rows['address1'];
			$sublinename			= $GetAllSubLine[$rows['sublineid']]['name'];

			$name2					= "#".$customerid." ".$name;

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

			$subscriptiondate	= date("d-M-Y",$subscriptiondate);

			$InventorySummaryArr[$inventoryid]['name']	= $AllInventoryArr[$inventoryid]['name'];
			$InventorySummaryArr[$inventoryid]['qty']	+= 1;

			$InventorySummaryArr[$inventoryid]['detail'][$logid]['custid']		= $custid;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['customerid']	= $customerid;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['name2']			= $name2;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['phone']			= $phone;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['areaid']		= $areaid;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['area']			= $AllAreaArr[$areaid]['name'];
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['line']			= $AllLineArr[$lineid]['name'];
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['fulladdress']	= $addresstr;
			$InventorySummaryArr[$inventoryid]['detail'][$logid]['subscriptiondate']	= $subscriptiondate;
		}
	}

	$index		= 0;
	$TotalRec	= 0;

	if(!empty($InventorySummaryArr))
	{
		foreach($InventorySummaryArr as $inventoryid=>$rows)
		{
			$detailArr	= array();

			if(!empty($rows['detail']))
			{
				$detailindex	= 0;

				foreach($rows['detail'] as $logid=>$logrows)
				{
					$detailArr[$detailindex]['logid']				= $logid;
					$detailArr[$detailindex]['custid']				= $logrows['custid'];
					$detailArr[$detailindex]['customerid']			= $logrows['customerid'];
					$detailArr[$detailindex]['customerid']			= $logrows['customerid'];
					$detailArr[$detailindex]['name2']				= $logrows['name2'];
					$detailArr[$detailindex]['phone']				= $logrows['phone'];
					$detailArr[$detailindex]['areaid']				= $logrows['areaid'];
					$detailArr[$detailindex]['area']				= $logrows['area'];
					$detailArr[$detailindex]['line']				= $logrows['line'];
					$detailArr[$detailindex]['fulladdress']			= $logrows['fulladdress'];
					$detailArr[$detailindex]['subscriptiondate']	= $logrows['subscriptiondate'];

					$TotalRec++;

					$detailindex++;
				}
			}

			if(!empty($detailArr))
			{
				$RecordSetArr[$index]['id']		= $inventoryid;
				$RecordSetArr[$index]['name']	= $rows['name'];
				$RecordSetArr[$index]['qty']	= $rows['qty'];
				$RecordSetArr[$index]['detail']	= $detailArr;

				$index++;
			}
		}
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Restart customer listed successfully.";
	}
	$response['recordset']		= $RecordSetArr;
	$response['totalrecord']	= $TotalRec;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetRestartCustomerListPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate restart customer list pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= 'restart-customer-list.pdf';

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")

	$StartDate	= "";
	$EndDate	= "";

	if(trim($_POST['startdate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
	}

	if(trim($_POST['enddate']) != "")
	{
		$EndDate	= strtotime($_POST['enddate']);
	}

	/*$File = "viewrestartcustomer.php?clientid=".$_POST['clientid']."&startdate=".$StartDate."&enddate=".$EndDate."&areaid=".$_POST['areaid']."&lineid=".$_POST['lineid']."&inventoryid=".$_POST['inventoryid']."&areamanagerid=".$_POST['areamanagerid']."&areaids=".$_POST['areaids']."&islineman=".$_POST['islineman']."&linemanareaid=".$_POST['linemanareaid']."&linemanlineids=".$_POST['linemanlineids']."&linemanid=".$_POST['linemanid']."&islineman=".$_POST['islineman']."&ishawker=".$_POST['ishawker']."&hawkerareaid=".$_POST['hawkerareaid']."&hawkerlineids=".$_POST['hawkerlineids']."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewrestartcustomer.php?bulkprinting=1&downloadpdf=1&startdate_strtotime=".$StartDate."&enddate_strtotime=".$EndDate."&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "Restart customer pdf generated successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerPaymentSummary")
{
	$condition	= "";

	/*$startdate	= strtotime(date('m/01/Y'));
	$enddate	= strtotime(date('m/t/Y'))+86399;*/

	$LastBillDateSQL	= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY invoicedate DESC LIMIT 1";
	$LastBillDateESQL	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1);

	$LastBillDateQuery	= pdo_query($LastBillDateSQL,$LastBillDateESQL);
	$LastBillDateRow	= pdo_fetch_assoc($LastBillDateQuery);

	$lastinvoicedate	= $LastBillDateRow['invoicedate'];

	$EndDate	= strtotime(date("Y-m-d"))+86399;

	$startdate	= $lastinvoicedate;
	$enddate	= $EndDate;

	$PaymentEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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

	if($_POST['hawkerid'] > 0 && $_POST['loginhawkerid'] < 1)
	{
		$condition	.= " AND cust.hawkerid=:hawkerid";
		$PaymentEsql['hawkerid']	= (int)$_POST['hawkerid'];
	}
	else if($_POST['ishawker'] == "1")
	{
		$condition	.= " AND cust.hawkerid=:hawkerid";
		$PaymentEsql['hawkerid']	= (int)$_POST['loginhawkerid'];
	}

	if($_POST['customerid'] > 0)
	{
		$condition	.= " AND cust.id=:id";
		$PaymentEsql['id']	= (int)$_POST['customerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

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

	$PaymentSql	= "SELECT count(payment.id) AS paymentcount, SUM(payment.amount) AS paymentamount,SUM(payment.discount) AS paymentdiscount,SUM(payment.coupon) AS paymentcoupon FROM ".$Prefix."customer_payments payment, ".$Prefix."customers cust WHERE cust.id=payment.customerid AND cust.deletedon <:deletedon AND payment.deletedon < :paymentdeletedon ".$condition." AND payment.paymentdate BETWEEN :startdate AND :enddate ORDER BY payment.paymentdate DESC, cust.sequence ASC, cust.customerid ASC";

	$PaymentEsql2	= $PaymentEsql;

	$PaymentEsql2["startdate"]	= $startdate;
	$PaymentEsql2["enddate"]	= $enddate;
	$PaymentEsql2["paymentdeletedon"]	= 1;

	$PaymentQuery	= pdo_query($PaymentSql,$PaymentEsql2);
	$paymentrows	= pdo_fetch_assoc($PaymentQuery);

	$paymentamount		= $paymentrows['paymentamount'];
	$paymentdiscount	= $paymentrows['paymentdiscount'];
	$paymentcoupon		= $paymentrows['paymentcoupon'];

	$latestpaymentreceived	= $paymentamount+$paymentdiscount+$paymentcoupon;

	if($paymentamount < 1 || $paymentamount == null)
	{
		$paymentamount	= "0";
	}

	$OnlineManualPayment	= GetOnlineManualPayment($condition, $PaymentEsql, $startdate, $enddate);
	$OnlineAutomaticPayment	= GetOnlineAutomaticPayment($condition, $PaymentEsql, $startdate, $enddate);
	$CashPayment			= GetCashPayment($condition, $PaymentEsql, $startdate, $enddate);

	$totalonlinepayment	= $OnlineManualPayment+$OnlineAutomaticPayment;

	$RecordSetArr['totalonlinepayment']		= @number_format($totalonlinepayment,2);
	$RecordSetArr['onlinemanualpayment']	= @number_format($OnlineManualPayment,2);
	$RecordSetArr['onlineautomaticpayment']	= @number_format($OnlineAutomaticPayment,2);
	$RecordSetArr['cashpayment']			= @number_format($CashPayment,2);
	$RecordSetArr['discount']				= @number_format($paymentdiscount,2);
	$RecordSetArr['coupon']					= @number_format($paymentcoupon,2);

	$RecordSetArr['paymentcount']	= $paymentrows['paymentcount'];
	$RecordSetArr['paymentamount']	= @number_format($paymentamount,2);

	$OutstandingSql	= "SELECT COUNT(cust.id) AS outstandingcount, SUM(outstandingbalance) AS outstandingbalance FROM ".$Prefix."customers cust WHERE cust.deletedon <:deletedon AND cust.outstandingbalance > :outstandingbalance ".$condition."";

	$OutstandingEsql	= array_merge(array("outstandingbalance"=>0),$PaymentEsql);

	$OutstandingQuery	= pdo_query($OutstandingSql,$OutstandingEsql);
	$outstandingrows	= pdo_fetch_assoc($OutstandingQuery);

	$totalbalancetobecollect	= 0;

	$OpeningSQL	= "SELECT SUM(openingbalance) AS custopeningbalance FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon";
	$OpeningESQL   = array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$OpeningQuery	= pdo_query($OpeningSQL,$OpeningESQL);
	$OpeningRows	= pdo_fetch_assoc($OpeningQuery);

	$totalopeningbalance	= $OpeningRows['custopeningbalance'];

	$PendingInvoiceSQL	= "SELECT SUM(finalamount) AS invoicetotal FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon < :deletedon";
	$PendingInvoiceESQL	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1);

	$PendingInvoiceQuery	= pdo_query($PendingInvoiceSQL,$PendingInvoiceESQL);
	$PendingInvoiceRow		= pdo_fetch_assoc($PendingInvoiceQuery);

	$invoicetotal	= $PendingInvoiceRow['invoicetotal'];

	$totalbalancetobecollect	= $totalopeningbalance+$invoicetotal;

	$RecordSetArr['outstandingcount']	= $outstandingrows['outstandingcount'];
	$RecordSetArr['outstandingbalance']	= @number_format($outstandingrows['outstandingbalance'],2);

	$outstandingpercentage	= ($outstandingrows['outstandingbalance']/$totalbalancetobecollect)*100;

	$RecordSetArr['outstandingpercentage']	= round($outstandingpercentage);

	$RecordSetArr['showcurrentmonthinfo']	= false;

	if($_POST['areamanagerid'] > 0 || $_POST['linemanid'] > 0 || $_POST['hawkerid'] > 0)
	{
	}
	else
	{
		$RecordSetArr['showcurrentmonthinfo']	= true;

		$LastBillTotalSQL	= "SELECT SUM(finalamount) AS lastbilltotal FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon < :deletedon AND invoicedate BETWEEN :startdate AND :enddate";
		$LastBillTotalESQL	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,"startdate"=>$lastinvoicedate,"enddate"=>$EndDate);

		$LastBillTotalQuery	= pdo_query($LastBillTotalSQL,$LastBillTotalESQL);
		$LastBillTotalRow	= pdo_fetch_assoc($LastBillTotalQuery);

		$lastbilltotal	= $LastBillTotalRow['lastbilltotal'];

		$RecordSetArr['lastbilltotalbalance']	= @number_format($lastbilltotal,2);

		/*$PaymentSql3	= "SELECT SUM(amount) AS paymentamount,SUM(discount) AS paymentdiscount,SUM(coupon) AS paymentcoupon FROM ".$Prefix."customer_payments WHERE 1 AND clientid=:clientid AND deletedon < :deletedon AND paymentdate BETWEEN :startdate AND :enddate";

		$PaymentEsql3	= array("clientid"=>(int)$_POST['clientid'],"startdate"=>$lastinvoicedate,"enddate"=>$EndDate,"deletedon"=>1);

		$PaymentQuery3	= pdo_query($PaymentSql3,$PaymentEsql3);
		$PaymentRow3	= pdo_fetch_assoc($PaymentQuery3);

		$latestpaymentreceived	= $PaymentRow3['paymentamount']+$PaymentRow3['paymentdiscount']+$PaymentRow3['paymentcoupon'];*/

		/*$latestpaymentreceived	= $paymentamount;*/

		$RecordSetArr['lastbillgdate']		= date("d-M-Y",$lastinvoicedate);
		$RecordSetArr['latestpaymenttotal']	= @number_format($latestpaymentreceived,2);

		$runningbalance	= $lastbilltotal - $latestpaymentreceived;

		$RecordSetArr['runningbalance']		= @number_format($runningbalance,2);

		$remaingpercentage	= ($runningbalance/$lastbilltotal)*100;

		$RecordSetArr['remaingpercentage']	= round($remaingpercentage,2);

		$totalonlinepaymentpercentage		= round((($totalonlinepayment/$lastbilltotal)*100),2);
		$onlinemanualpaymentpercentage		= round((($OnlineManualPayment/$lastbilltotal)*100),2);
		$onlineautomaticpaymentpercentage	= round((($OnlineAutomaticPayment/$lastbilltotal)*100),2);
		$cashpaymentpercentage				= round((($CashPayment/$lastbilltotal)*100),2);
		$discountpercentage					= round((($paymentdiscount/$lastbilltotal)*100),2);
		$couponpercentage					= round((($paymentcoupon/$lastbilltotal)*100),2);
		$latestpaymentreceivedpercentage	= round((($latestpaymentreceived/$lastbilltotal)*100),2);

		$RecordSetArr['totalonlinepaymentpercentage']		= $totalonlinepaymentpercentage;
		$RecordSetArr['onlinemanualpaymentpercentage']		= $onlinemanualpaymentpercentage;
		$RecordSetArr['onlineautomaticpaymentpercentage']	= $onlineautomaticpaymentpercentage;
		$RecordSetArr['cashpaymentpercentage']				= $cashpaymentpercentage;
		$RecordSetArr['discountpercentage']					= $discountpercentage;
		$RecordSetArr['couponpercentage']					= $couponpercentage;
		$RecordSetArr['latestpaymentreceivedpercentage']	= $latestpaymentreceivedpercentage;

		$RecordSetArr['paymentcount']	= $paymentrows['paymentcount'];
		$RecordSetArr['paymentamount']	= @number_format($paymentamount,2);

		/*$tempinfo = date("r",$startdate)."---".date("r",$enddate)."---".$PaymentRow3['paymentamount']."---".$PaymentRow3['paymentdiscount']."---".$PaymentRow3['paymentcoupon'];

		$RecordSetArr['tempinfo']	= $tempinfo;*/
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Payment summary fetched successfully.";
	}

	$response['recordset']		= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLatePayment")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer late payment report.";

	$links		= 5;
	$perpage	= 100;

	$serialindex	= 1;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}
	if($_POST['serialindex'] != '')
	{
		$serialindex = $_POST['serialindex'];
	}

	$CustomerIDArr		= array();
	$CustomerNameArr	= array();

	$outstandingbalance	= 0;

	if($_POST['outstandingamountabove'] != "")
	{
		$outstandingbalance	= (float)$_POST['outstandingamountabove'];
	}

	if($_POST['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"outstandingbalance"=>(float)$outstandingbalance);

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids	= $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND areaid IN(".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND lineid IN(".$lineids.")";
		}

		if($_POST['latepaymentdays'] != "")
		{
			$tempenddate	= strtotime(date("Y-m-d"));
			$startdate		= $tempenddate - ($_POST['latepaymentdays']*86400);
			$enddate		= $tempenddate+86399;

			$Condition	.= " AND id NOT IN(SELECT customerid FROM ".$Prefix."customer_payments WHERE paymentdate BETWEEN :paymentstartdate AND :paymentenddate)";

			$CustESQL['paymentstartdate']	= $startdate;
			$CustESQL['paymentenddate']		= $enddate;
		}

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY outstandingbalance DESC, customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$CustSQL3	= "SELECT SUM(outstandingbalance) AS outstandingbalance FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery3	= pdo_query($CustSQL3,$CustESQL);
		$CustRows3	= pdo_fetch_assoc($CustQuery3);

		$totaloutstandingbalance	= $CustRows3['outstandingbalance'];

		$TotalRec	= $CustNum;

		if($CustNum > 0)
		{
			$totalpages	= ceil($CustNum/$perpage);
			$offset		= ($_POST['page'] - 1) * $perpage;
			$addquery	= " LIMIT %d, %d";
		}
		else
		{
			$addquery	= "";
		}

		$CustSQL2	= $CustSQL.$addquery;
		$CustSQL2	= sprintf($CustSQL2, intval($offset), intval($perpage));

		$CustQuery2	= pdo_query($CustSQL2,$CustESQL);
		$CustNum2	= pdo_num_rows($CustQuery2);

		$DetailArr	= array();
		$index		= 0;

		if($CustNum2 > 0)
		{
			$AllAreaArr	= GetAllArea($_POST['clientid']);
			$AllSubLine	= GetAllSubLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery2))
			{
				$id					= $custrows['id'];
				$customerid			= $custrows['customerid'];
				$name				= $custrows['name'];
				$phone				= $custrows['phone'];
				$openingbalance		= $custrows['openingbalance'];
				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$areaid				= $custrows['areaid'];
				$lineid				= $custrows['lineid'];
				$areaname			= $AllAreaArr[$areaid]['name'];
				$outstandingbalance	= $custrows['outstandingbalance'];
				$sublinename		= $AllSubLine[$custrows['sublineid']]['name'];

				$PaymentSql2	= "SELECT * FROM ".$Prefix."customer_payments WHERE customerid=:customerid ORDER BY paymentdate DESC LIMIT 1";
				$PaymentEsql2	= array("customerid"=>(int)$id);

				$PaymentQuery2	= pdo_query($PaymentSql2, $PaymentEsql2);
				$PaymentNum2	= pdo_num_rows($PaymentQuery2);

				$paymentamount	= "";
				$paymentdate	= "";

				if($PaymentNum2 > 0)
				{
					$Paymentrow2	= pdo_fetch_assoc($PaymentQuery2);

					/*$paymentamount	= @number_format($Paymentrow2['amount'],2);*/

					$Amount		= (float)$Paymentrow2['amount'];
					$Discount	= (float)$Paymentrow2['discount'];
					$Coupon		= (float)$Paymentrow2['coupon'];

					$paymentamount	= @number_format(($Amount+$Discount+$Coupon),2);

					$paymentdate	= date("d-M-Y",$Paymentrow2['paymentdate']);
				}

				$SubscriptionStatusArr	= GetCustomerStatusBySubscription($id);

				$hassubscription	= $SubscriptionStatusArr['hassubscription'];
				$blockcolor			= $SubscriptionStatusArr['blockcolor'];
				$statusclass		= $SubscriptionStatusArr['statusclass'];

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

				$name2	= "#".$customerid." ".$name;

				$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon <:deletedon AND customerid=:customerid AND ispaid < :ispaid ORDER BY id DESC LIMIT 1";
				$InvoiceEsql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,"customerid"=>(int)$id,"ispaid"=>1);

				$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
				$InvoiceNum		= pdo_num_rows($InvoiceQuery);

				$invoiceid		= "---";
				$hasinvoiceid	= false;

				if($InvoiceNum > 0)
				{
					$invoicerows	= pdo_fetch_assoc($InvoiceQuery);
					$billno			= $invoicerows['invoiceid'];
					$invoiceid		= $invoicerows['id'];
					$hasinvoiceid	= true;
				}

				$DetailArr[$index]['id']				= $id;
				$DetailArr[$index]['serialno']			= (int)$serialindex;
				$DetailArr[$index]['name']				= $name2;
				$DetailArr[$index]['phone']				= $phone;
				$DetailArr[$index]['address']			= $addresstr;
				$DetailArr[$index]['hasinvoiceid']		= $hasinvoiceid;
				$DetailArr[$index]['billno']			= $billno;
				$DetailArr[$index]['invoiceid']			= $invoiceid;
				$DetailArr[$index]['hassubscription']	= $hassubscription;
				$DetailArr[$index]['blockcolor']		= $blockcolor;
				$DetailArr[$index]['statusclass']		= $statusclass;
				$DetailArr[$index]['areaid']			= (int)$areaid;
				$DetailArr[$index]['areaname']			= $areaname;
				$DetailArr[$index]['lineid']			= (int)$lineid;
				$DetailArr[$index]['amount']			= @number_format($outstandingbalance,2);
				$DetailArr[$index]['outstandingbalance']= @number_format($outstandingbalance,2);
				$DetailArr[$index]['lastpaymentdate']	= $paymentdate;
				$DetailArr[$index]['paymentamount']		= $paymentamount;
				$DetailArr[$index]['paymentnum']		= (int)$PaymentNum2;

				$index++;
				$serialindex++;
			}
		}

		$pageListArr	= array();
		$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);

		$RecordSet['detail']					= $DetailArr;
		$response['perpage']					= (int)$perpage;
		$response['paginglist']					= $pageListArr;
		$response['showpages']					= false;
		$response['totalrecord']				= $TotalRec;
		$response['totalpages']					= (int)$totalpages;
		$response['totaloutstandingbalance']	= @number_format($totaloutstandingbalance,2);
		$response['serialindex']				= (int)$serialindex;
		$response['success']					= true;
		$response['msg']						= "Customer outstanding report fetched successfully.";
		$response['recordset']					= $RecordSet;

		if($totalpages > 1)
		{
			$response['showpages']	= true;
		}
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'GetLatepaymentReportPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate late payment report pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	$Pdf_FileName 	= 'late-payment-report.pdf';

	/*if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")

	$StartDate	= "";
	$EndDate	= "";

	if(trim($_POST['startdate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
	}

	if(trim($_POST['enddate']) != "")
	{
		$EndDate	= strtotime($_POST['enddate']);
	}*/

	$File	= "viewlatepaymentreport.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "late payment pdf generated successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}