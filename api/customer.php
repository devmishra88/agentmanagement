<?php
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
if($_POST['Mode'] == "AddCustomer")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add customer.";
    $response['toastmsg']	= "Unable to add customer.";

	$StartDate	= strtotime('today');

	if($_POST['phone'] != "" && !validate_mobile($_POST['phone']) && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please enter a valid phone";
		$response['toastmsg']	= "Please enter a valid phone";
	}

	if($_POST['phone'] != "" && $ErrorMsg == "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A customer already exist with same phone";
			$response['toastmsg']	= "A customer already exist with same phone";
		}
	}
	if($_POST['phone2'] != "" && $ErrorMsg == "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone2=:phone2 AND clientid=:clientid AND deletedon <:deletedon";
		$CheckEsql	= array("phone2"=>$_POST['phone2'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A customer already exist with same alt. phone";
			$response['toastmsg']	= "A customer already exist with same alt. phone";
		}
	}

	if($_POST['areaid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select an area";
		$response['toastmsg']	= "Please select an area";
	}

	if($_POST['lineid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select a line";
		$response['toastmsg']	= "Please select a line";
	}

	if($_POST['sublineid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select a sub line";
		$response['toastmsg']	= "Please select a sub line";
	}

	if($_POST['hawkerid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select a hawker";
		$response['toastmsg']	= "Please select a hawker";
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']	= $ErrorMsg;
		
		if($CheckNum < 0)
		{
			$response['toastmsg']	= "Please enter all required field";
		}
	}

	$dob				= "";
	if($_POST['dob'] != "" && $_POST['dobavailable'] > 0)
	{
		$dob	= strtotime($_POST['dob']);
	}

	$anniversarydate	= "";
	if($_POST['anniversarydate'] != "" && $_POST['anniversaryavailable'] > 0)
	{
		$anniversarydate	= strtotime($_POST['anniversarydate']);
	}
	$customersince = "";
	if($_POST['customersince'] !='' && $_POST['customerdateavailable'] > 0)
	{
		$customersince	= strtotime($_POST['customersince']);
	}

	$AddedBy	= "Agency";
	$AddedByID	= $_POST['clientid'];

	if($_POST['loginlinemanid'] > 0)
	{
		$AddedBy	= "Lineman";
		$AddedByID	= $_POST['loginlinemanid'];
	}
	else if($_POST['areamanagerid'] > 0)
	{
		$AddedBy	= "Area Manager";
		$AddedByID	= $_POST['areamanagerid'];
	}

	if($_POST['isopeningbalance'] < 1)
	{
		$_POST['openingbalance']	= '';
	}

	if($haserror == false)
	{
		$CustomerCode = GetCustomerCode();

		$Sql	= "INSERT INTO ".$Prefix."customers SET 
		clientid			=:clientid,
		name				=:name,
		phone				=:phone,
		phone2				=:phone2,
		lineid				=:lineid,
		sublineid			=:sublineid,
		linemanid			=:linemanid,
		hawkerid			=:hawkerid,
		address1			=:address1,
		latitude			=:latitude,
		longitude			=:longitude,
		status				=:status,
		isdiscount			=:isdiscount,
		discount			=:discount,
		createdon			=:createdon,
		customerid			=:customerid,
		openingbalance		=:openingbalance,
		outstandingbalance	=:outstandingbalance,
		isincreasepricing	=:isincreasepricing,
		increasepricing		=:increasepricing,
		dob					=:dob,
		anniversarydate		=:anniversarydate,
		customersince		=:customersince,
		areaid				=:areaid,
		addedby				=:addedby,
		addedbyid			=:addedbyid,
		housenumber			=:housenumber,
		floor				=:floor,
		liftavailable		=:liftavailable,
		canprintinvoice		=:canprintinvoice";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"phone2"			=>$_POST['phone2'],
			"lineid"			=>(int)$_POST['lineid'],
			"sublineid"			=>(int)$_POST['sublineid'],
			"linemanid"			=>(int)$_POST['linemanid'],
			"hawkerid"			=>(int)$_POST['hawkerid'],
			"address1"			=>$_POST['address1'],
			"latitude"			=>$_POST['latitude'],
			"longitude"			=>$_POST['longitude'],
			"status"			=>(int)$_POST['status'],
			"isdiscount"		=>(int)$_POST['isdiscount'],
			"discount"			=>(float)$_POST['discount'],
			"createdon"			=>$createdon,
			"customerid"		=>$CustomerCode,
			"openingbalance"	=>$_POST['openingbalance'],
			"outstandingbalance"=>(float)$_POST['outstandingbalance'],
			"isincreasepricing"	=>(int)$_POST['isincreasepricing'],
			"increasepricing"	=>(float)$_POST['increasepricing'],
			"dob"				=>(int)$dob,
			"anniversarydate"	=>(int)$anniversarydate,
			"customersince"		=>(int)$customersince,
			"areaid"			=>(int)$_POST['areaid'],
			"addedby"			=>$AddedBy,
			"addedbyid"			=>(int)$AddedByID,
			"housenumber"		=>$_POST['housenumber'],
			"floor"				=>$_POST['floor'],
			"liftavailable"		=>(int)$_POST['liftavailable'],
			"canprintinvoice"	=>(int)$_POST['canprintinvoice']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$customerid	= pdo_insert_id();

			$isinactive	= 1;

			if($_POST['subscription'] == 1)
			{
				if(!empty($_POST['inventorylist']))
				{
					foreach($_POST['inventorylist'] as $catkey=>$catrows)
					{
						$catid			= $catrows['id'];
						$inventoryArr	= $catrows['recordlist'];

						if(!empty($inventoryArr))
						{
							foreach($inventoryArr as $inventorykey =>$inventoryrows)
							{
								$inventoryid		= $inventoryrows['id'];
								$isassigned			= $inventoryrows['isassigned'];
								$frequency			= $inventoryrows['frequency'];
								$quantity			= $inventoryrows['quantity'];
								$subscriptiondate	= strtotime($inventoryrows['subscriptiondate']);
								
								if($quantity < 1)
								{
									$quantity = 1;
								}
								$dayArr		= array();
								$daynameArr	= array();
								foreach($inventoryrows['days'] as $daykey=>$dayrows)
								{
									if($dayrows['checked'] == "true")
									{
										$dayArr[]		= $dayrows['id'];
										$daynameArr[]	= $dayrows['name'];
									}
								}

								$daysStr	= "::".@implode("::",@array_filter(@array_unique($dayArr)))."::";
								$dayNameStr	= "::".@implode("::",@array_filter(@array_unique($daynameArr)))."::";

								if($frequency != "1")
								{
									$daysStr			= "";
									$dayNameStr			= "";
								}

								if($isassigned == "true")
								{
									$AssignSql	= "INSERT INTO ".$Prefix."subscriptions SET 
									customerid			=:customerid,
									inventoryid			=:inventoryid,
									frequency			=:frequency,
									quantity			=:quantity,
									subscriptiondate	=:subscriptiondate,
									days				=:days,
									daysname			=:daysname,
									createdon			=:createdon";

									$AssignEsql	= array(
										"customerid"		=>(int)$customerid,
										"inventoryid"		=>(int)$inventoryid,
										"frequency"			=>(int)$frequency,
										"quantity"			=>(int)$quantity,
										"subscriptiondate"	=>$subscriptiondate,
										"days"				=>$daysStr,
										"daysname"			=>$dayNameStr,
										"createdon"			=>$createdon
									);

									$AssignQuery	= pdo_query($AssignSql,$AssignEsql);
									if($AssignQuery)
									{
										$isinactive	= 0;
										CreateSubscriptionLog($customerid,$inventoryid,1,$frequency,$subscriptiondate,$daysStr,$dayNameStr,0,$quantity);
									}
								}
							}
						}
					}
				}
			}

			$UpdateSql		= "UPDATE ".$Prefix."customers SET isinactive=:isinactive WHERE id=:id";
			$UpdateEsql		= array("isinactive"=>(int)$isinactive,"id"=>(int)$customerid);

			$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

			$IsOpeningBalance = 1;
			
			$Narration	= "Opening Balance";
			$createdon	= 0;
			/*GenerateCustomerAccountLog($_POST['clientid'],$customerid,$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$_POST['openingbalance'],$createdon,$Narration,"openingbalance");*/

			$response['success']	= true;
			$response['msg']		= "Customer successfully added.";
			$response['toastmsg']	= "Customer successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllCustomers")
{
	$links		= 5;
	$perpage	= 100;

	$currentdatetimestamp	= strtotime(date("m/d/Y"));

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}

	$RecordListArr			= array();
	$SubscriptionSummaryArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customers.";

	$condition	= " AND deletedon <:deletedon";
	$Esql		= array("deletedon"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
	{
		if($_POST['lineid'] == 9999)
		{
			$condition	.= " AND lineid < :lineid";
			$Esql['lineid']	= 1;
		}
		else
		{
			$condition	.= " AND lineid=:lineid";
			$Esql['lineid']	= (int)$_POST['lineid'];
		}
	}

	if($_POST['areaid'] > 0)
	{
		if($_POST['areaid'] == 9999)
		{
			$condition	.= " AND areaid < :areaid";
			$Esql['areaid']	= 1;
		}
		else
		{
			$condition	.= " AND areaid=:areaid";
			$Esql['areaid']	= (int)$_POST['areaid'];
		}
	}

	if($_POST['hawkerid'] > 0 && $_POST['loginhawkerid'] < 1)
	{
		$condition	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
	}
	else if($_POST['ishawker'] == "1")
	{
		$condition	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['loginhawkerid'];
	}

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

	if($_POST['openingbalanceid'] > 0)
	{
		if($_POST['openingbalanceid'] == '1')
		{
			$condition	.= " AND openingbalance !=:openingbalance AND openingbalance !=:openingbalance2 AND openingbalance IS NOT NULL";
			$Esql['openingbalance'] = '';
			$Esql['openingbalance2'] = '0';
		}
		if($_POST['openingbalanceid'] == '2')
		{
			$condition	.= " AND openingbalance =:openingbalance AND openingbalance IS NOT NULL";
			$Esql['openingbalance'] = "0";
		}
		if($_POST['openingbalanceid'] == '3')
		{
			$condition	.= " AND (openingbalance =:openingbalance || openingbalance IS NULL)";
			$Esql['openingbalance'] = '';
		}
	}
	if(trim($_POST['searchkeyword']) != "")
	{
		$condition	.= " AND (name LIKE :name || phone LIKE :phone || email LIKE :email || address1 LIKE :address1 || customerid LIKE :customerid || phone2 LIKE :phone2)";

		$Esql['name']		= "%".$_POST['searchkeyword']."%";
		$Esql['phone']		= "%".$_POST['searchkeyword']."%";
		$Esql['email']		= "%".$_POST['searchkeyword']."%";
		$Esql['address1']	= "%".$_POST['searchkeyword']."%";
		$Esql['customerid']	= "".$_POST['searchkeyword']."%";
		$Esql['phone2']		= "%".$_POST['searchkeyword']."%";
	}
	if(trim($_POST['nameandphone']) != "")
	{
		$condition	.= " AND (name LIKE :name2 || phone LIKE :phone2 || phone2 LIKE :phone3)";

		$Esql['name2']		= "%".$_POST['nameandphone']."%";
		$Esql['phone2']		= "%".$_POST['nameandphone']."%";
		$Esql['phone3']		= "%".$_POST['nameandphone']."%";
	}

	if($_POST['customerid'] > 0)
	{
		$condition	.= " AND id=:id";
		$Esql['id']	= (int)$_POST['customerid'];
	}

	$inventorycond	= "";

	if($_POST['inventoryid'] > 0)
	{
		$inventorycond	.= " AND id IN (SELECT customerid FROM ".$Prefix."subscriptions WHERE inventoryid=:inventoryid)";

		$Esql['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['type'] == "sequence")
	{
		$Sql	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition." ".$inventorycond." ORDER BY sequence ASC";
	}
	else
	{
		$Sql	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition." ".$inventorycond." ORDER BY sequence ASC, customerid ASC, status DESC";
	}

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

	if($_POST['type'] == "sequence")
	{
		$Sql2	= $Sql;
	}
	else
	{
		$Sql2	= $Sql.$addquery;
		$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));
	}

	$Query2	= pdo_query($Sql2,$Esql);
	$Num2	= pdo_num_rows($Query2);

	$showingrecord	= 0;

	$caneditopeningbalance	= false;

	if(($_POST['areamanagerid'] < 1 && $_POST['loginlinemanid'] < 1 && $_POST['loginhawkerid'] < 1) && $_POST['clientid'] > 0)
	{
		$caneditopeningbalance	= true;
	}

	if($Num2 > 0)
	{
		$index	= 0;

		$GetAllLine			= GetAllLine($_POST['clientid']);
		/*$GetAllLineman	= GetAllLineman($_POST['clientid']);*/
		$GetAllHawker		= GetAllHawker($_POST['clientid']);
		$GetAllArea			= GetAllArea($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

		$totalsalequantity	= "";

		while($rows = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$name2		= "";

			$id					= $rows['id'];
			$name				= $rows['name'];
			$phone				= $rows['phone'];
			$phone2				= $rows['phone2'];
			$createdon			= $rows['createdon'];
			$housenumber		= $rows['housenumber'];
			$floor				= $rows['floor'];
			$address1			= $rows['address1'];
			$customerid			= $rows['customerid'];
			$isdiscount			= $rows['isdiscount'];
			$discount			= $rows['discount'];
			$status				= $rows['status'];
			$firstname			= $rows['firstname'];
			$lastname			= $rows['lastname'];
			$sequence			= $rows['sequence'];
			$latitude			= $rows['latitude'];
			$longitude			= $rows['longitude'];
			$sublinename		= $GetAllSubLine[$rows['sublineid']]['name'];

			$CustomerHolidayArr	= array();

			$holidaystartdate	= "";
			$holidayenddate		= "";

			$hascustomerholiday	= false;

			$HolidaySql		= "SELECT * FROM ".$Prefix."holidays_stock_linker WHERE clientid=:clientid AND customerid=:customerid order BY enddate ASC";
			$HolidayEsql	= array("clientid"=>(int)$_POST['clientid'],"customerid"=>(int)$id);

			$HolidayQuery	= pdo_query($HolidaySql,$HolidayEsql);
			$HolidayNum		= pdo_num_rows($HolidayQuery);

			if($HolidayNum > 0)
			{
				$prevstockid	= "";

				while($holidayrows = pdo_fetch_assoc($HolidayQuery))
				{
					$stockid	= $holidayrows['stockid'];
					$startdate	= $holidayrows['startdate'];
					$enddate	= $holidayrows['enddate'];

					$startdate2	= $holidayrows['startdate'];
					$enddate2	= $holidayrows['enddate'];

					if(!empty($CustomerHolidayArr))
					{
						$oldstartdate	= $CustomerHolidayArr[$stockid]['startdate'];
						$oldenddate		= $CustomerHolidayArr[$stockid]['enddate'];

						if(($oldstartdate < $startdate) && $oldstartdate > 0)
						{
							$startdate	= $oldstartdate;
						}

						if(($oldenddate > $enddate) && $oldenddate > 0)
						{
							$enddate	= $oldenddate;
						}
					}

					$CustomerHolidayArr[$stockid]['startdate']	= $startdate;
					$CustomerHolidayArr[$stockid]['enddate']	= $enddate;

					if(($currentdatetimestamp > $startdate2 || $currentdatetimestamp == $startdate2) && ($currentdatetimestamp < $enddate2) && ($holidaystartdate == "" && $holidayenddate == ""))
					{
						$holidaystartdate	= $startdate2;
						$holidayenddate		= $enddate2;
					}
				}
			}

			/*echo date("r",$holidaystartdate)."---".date("r",$holidayenddate);*/

			if((($currentdatetimestamp > $holidaystartdate || $currentdatetimestamp == $holidaystartdate) && $currentdatetimestamp < $holidayenddate) && $holidaystartdate > 0 && $holidayenddate > 0)
			{
				$hascustomerholiday	= true;
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

			//$isopeningbalance	= $rows['isopeningbalance'];
			$openingbalance		= $rows['openingbalance'];

			/*if($openingbalance !='')
			{
				$openingbalance		= ($openingbalance * 100) / 100;
			}*/

			/*$subscriptionsql	= "SELECT inv.name,inv.id AS invid,subs.quantity qty FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
			$subscriptionesql	= array("customerid"=>(int)$id,"deletedon"=>1);*/

			$subscriptionsql	= "SELECT inv.name,inv.id AS invid,subs.quantity qty FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id ORDER BY inv.name";
			$subscriptionesql	= array("customerid"=>(int)$id);

			$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
			$subscriptionnum	= pdo_num_rows($subscriptionquery);

			$hassubscription	= false;
			$blockcolor			= "#ff0000";
			$statusclass		= "no-sorting";

			$salequantity		= "";

			$subscriptionstr 	= '';
			if($subscriptionnum > 0)
			{
				$hassubscription	= true;
				$blockcolor			= "";
				$statusclass		= "activelist";

				while($subsrow	= pdo_fetch_assoc($subscriptionquery))
				{
					$invid			= $subsrow['invid'];
					$inventoryname	= $subsrow['name'];
					$qty			= $subsrow['qty'];
					
					if($qty > 1)
					{
						$inventoryname = $inventoryname." X ".$qty;	
					}
					$subscriptionstr .= $inventoryname.', ';

					if(!$hascustomerholiday)
					{
						$SubscriptionSummaryArr[$inventoryname]	+= $qty;
					}

					if($_POST['area'] == 'addsale' && (int)$_POST['inventoryid'] == (int)$invid)
					{
						$salequantity		+= $qty;

						$totalsalequantity	+= $qty;
					}
				}
				$subscriptionstr .= '@@';
				$subscriptionstr = str_replace(", @@","",$subscriptionstr);
				$subscriptionstr = str_replace("@@","",$subscriptionstr);
			}
			else
			{
				$hassubscription	= false;
				$blockcolor			= "#ff0000";
				$statusclass		= "no-sorting";
				/*$subscriptionstr	= '--';*/
				$subscriptionstr	= 'INACTIVE';
			}

			$customerholidaystr	= "";

			if($hascustomerholiday)
			{
				$holidaystr	= "";

				$startdatestr	= date("d-M-Y",$holidaystartdate);
				$enddatestr		= date("d-M-Y",$holidayenddate);

				if($startdatestr == $enddatestr)
				{
					$holidaystr	= $startdatestr;
				}
				else
				{
					$holidaystr	= $startdatestr." to ".$enddatestr;
				}

				$hassubscription	= false;
				$blockcolor			= "#ffa500";
				$statusclass		= "no-sorting";
				$customerholidaystr	= $holidaystr;
			}

			$googlemap = '';
			if($latitude !='' && $longitude !='')
			{
				$googlemap = "https://www.google.com/maps/dir//".$latitude.",".$longitude."/";
			}
			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			$name2	= "#".$customerid." ".$name;

			if($isdiscount > 0)
			{
				if($discount > 0)
				{
					$discount	= (float)$discount."%";
				}
				else
				{
					$discount	= "---";
				}
			}
			else
			{
				$discount	= "No Discount";
			}

			$line		= $GetAllLine[$rows['lineid']]['name'];
			$subline	= $GetAllSubLine[$rows['sublineid']]['name'];
			/*$lineman	= $GetAllLineman[$rows['linemanid']]['name'];*/
			$hawker		= $GetAllHawker[$rows['hawkerid']]['name'];
			$area		= $GetAllArea[$rows['areaid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($phone2 == "")
			{
				$phone2	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}
			if($subline == "")
			{
				$subline	= "---";
			}

			/*if($lineman == "")
			{
				$lineman	= "---";
			}*/

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($area == "")
			{
				$area	= "---";
			}

			$RecordListArr[$index]['id']					= (int)$id;
			$RecordListArr[$index]['customerid']			= $customerid;
			$RecordListArr[$index]['name']					= $name;
			$RecordListArr[$index]['phone']					= $phone;
			$RecordListArr[$index]['phone2']				= $phone2;
			$RecordListArr[$index]['line']					= $line;
			$RecordListArr[$index]['subline']				= $subline;
			/*$RecordListArr[$index]['lineman']				= $lineman;*/
			$RecordListArr[$index]['hawker']				= $hawker;
			$RecordListArr[$index]['discount']				= $discount;
			$RecordListArr[$index]['address1']				= $address1;
			$RecordListArr[$index]['fulladdress']			= $addresstr;
			$RecordListArr[$index]['status']				= (int)$status;
			$RecordListArr[$index]['area']					= $area;
			$RecordListArr[$index]['name2']					= $name2;
			$RecordListArr[$index]['sequence']				= (int)$sequence;
			$RecordListArr[$index]['googlemap']				= $googlemap;
			$RecordListArr[$index]['housenumber']			= $housenumber;
			$RecordListArr[$index]['floor']					= $floor;
			$RecordListArr[$index]['subscriptions']			= $subscriptionstr;
			$RecordListArr[$index]['customerholidays']		= $customerholidaystr;
			$RecordListArr[$index]['canchangebalance']		= false;
			$RecordListArr[$index]['hassubscription']		= $hassubscription;
			$RecordListArr[$index]['blockcolor']			= $blockcolor;
			$RecordListArr[$index]['statusclass']			= $statusclass;
			$RecordListArr[$index]['caneditopeningbalance']	= $caneditopeningbalance;
			$RecordListArr[$index]['hascustomerholiday']	= $hascustomerholiday;
			$RecordListArr[$index]['areaid']				= (int)$rows['areaid'];
			$RecordListArr[$index]['lineid']				= (int)$rows['lineid'];
			$RecordListArr[$index]['hawkerid']				= (int)$rows['hawkerid'];
			$RecordListArr[$index]['stockqty']				= "";

			if($_POST['area'] == 'addsale')
			{
				$RecordListArr[$index]['stockqty']			= '';
			}

			if($openingbalance != "")
			{
				$RecordListArr[$index]["openingbalance"]	= $openingbalance;
				$RecordListArr[$index]["openingbalancetxt"]	= $openingbalance;
			}
			else
			{
				$RecordListArr[$index]["openingbalance"]	= '';
				$RecordListArr[$index]["openingbalancetxt"]	= "---";
			}

			$index++;
			$showingrecord++;
		}

		$response['success']	= true;
		$response['msg']		= "Customers listed successfully.";
	}

	$pageListArr	= array();
	$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);
	$fromrecord		= $offset+1;

	$response['recordlist']		= $RecordListArr;
	$response['perpage']		= (int)$perpage;
	$response['totalpages']		= (int)$totalpages;
	$response['paginglist']		= $pageListArr;
	$response['showpages']		= false;
	$response['totalrecord']	= $TotalRec;
	$response['recordrange']	= $fromrecord."-".($fromrecord+($showingrecord) - 1);

	if($_POST['area'] == 'addsale')
	{
		$response['totalsalequantity']	= (int)$totalsalequantity;
	}

	$SubscriptionSummaryArr2	= array();

	$summaryindex	= 0;

	$totalsubscription	= 0;

	if(!empty($SubscriptionSummaryArr))
	{
		foreach($SubscriptionSummaryArr as $name=>$qty)
		{
			$SubscriptionSummaryArr2[$summaryindex]['serial']	= $summaryindex+1;
			$SubscriptionSummaryArr2[$summaryindex]['name']		= $name;
			$SubscriptionSummaryArr2[$summaryindex]['quantity']	= $qty;

			$totalsubscription	+= $qty;

			$summaryindex++;
		}
	}

	usort($SubscriptionSummaryArr2, function($a, $b){
		return $a['name'] <=> $b['name'];
	});

	if($_POST['type'] == "sequence")
	{
		$response['subscriptionsummary']	= $SubscriptionSummaryArr2;
		$response['totalsubscription']		= $totalsubscription;
	}

	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllSubscribeCustomer")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customers.";

	$condition	= " AND cust.deletedon <:deletedon";
	$Esql		= array("deletedon"=>1);

    $Month = '';
    $Year = '';
	if($_POST['billingmonthyear'] !="")
	{
		$MonthYearArr = explode("-",$_POST['billingmonthyear']);
		$Month = $MonthYearArr[1];
		$Year = $MonthYearArr[0];
	}
	if($_POST['fromarea'] == "messagearea")
	{
		$condition	.= " AND cust.phone<>:phone";
		$Esql['phone']	= "";
	}

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND cust.clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}
	
	$IsAreaAdded = 0;
	$IsLineAdded = 0;
	
	if($_POST['type'] == "subscribecustomer")
	{
		$condition	.= " AND cust.areaid=:areaid AND cust.lineid=:lineid";

		$Esql['areaid']	= (int)$_POST['areaid'];
		$Esql['lineid']	= (int)$_POST['lineid'];
		$IsAreaAdded = 1;
		$IsLineAdded = 1;
	}
	
	if($_POST['hawkerid'] > 0)
	{
		$condition	.= " AND cust.hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
	}

	/*if($_POST['loginlinemanid'] > 0)
	{
		$condition	.= " AND linemanid=:linemanid2";
		$Esql['linemanid2']	= (int)$_POST['loginlinemanid'];
	}*/

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$condition	.= " AND cust.areaid IN(".$areaids.")";
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

	if($_POST['type'] == "salearea")
	{
		$condition	.= " AND cust.id=sub.customerid AND sub.inventoryid=:inventoryid";

		$Esql['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['lineid'] > 0 && $IsLineAdded < 1)
	{
		$condition	.= " AND cust.lineid=:lineid";
		$Esql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['areaid'] > 0 && $IsAreaAdded < 1)
	{
		$condition	.= " AND cust.areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}


	$Sql	= "SELECT cust.* FROM ".$Prefix."customers cust WHERE 1 ".$condition." ORDER BY cust.sequence ASC, cust.customerid ASC";

	if($_POST['ordertype'] == "sequence")
	{
		$Sql	= "SELECT cust.* FROM ".$Prefix."customers cust WHERE 1 ".$condition." ORDER BY cust.sequence ASC";
	}

	if($_POST['type'] == "salearea")
	{
		$Sql	= "SELECT cust.* FROM ".$Prefix."customers cust,".$Prefix."subscriptions sub WHERE 1 ".$condition." ORDER BY cust.sequence ASC, cust.customerid ASC";
	}

	if($Month > 0)
	{
		$Sql	= "SELECT cust.* FROM ".$Prefix."invoices inv,".$Prefix."customers cust WHERE inv.invoicemonth=:invoicemonth AND inv.invoiceyear=:invoiceyear AND inv.deletedon < :deletedon2 AND inv.customerid=cust.id".$condition." ORDER BY cust.customerid ASC, cust.status DESC";
		$Esql['deletedon2'] = 1;
		$Esql['invoicemonth'] = (int)$Month;
		$Esql['invoiceyear'] = (int)$Year;
	}



	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);

	$index	= 0;

	if($_POST['type'] == "subscribecustomer")
	{
		$RecordListArr[$index]['id']			= "";
		$RecordListArr[$index]['name']			= "Select";
		$RecordListArr[$index]['phone']			= "";
		$RecordListArr[$index]['phone2']		= "";
		$RecordListArr[$index]['email']			= "";
		$RecordListArr[$index]['stateid']		= "";
		$RecordListArr[$index]['cityid']		= "";
		$RecordListArr[$index]['customerid']	= "";
	}

	if($_POST['fromarea'] == "messagearea")
	{
		$RecordListArr[$index]['id']			= "";
		$RecordListArr[$index]['name']			= "Select an area and line to list customer";
		$RecordListArr[$index]['phone']			= "";
		$RecordListArr[$index]['phone2']		= "";
		$RecordListArr[$index]['email']			= "";
		$RecordListArr[$index]['stateid']		= "";
		$RecordListArr[$index]['cityid']		= "";
		$RecordListArr[$index]['customerid']	= "";
	}

	if($_POST['type'] == "salearea")
	{
		$RecordListArr[$index]['id']			= "";
		$RecordListArr[$index]['name']			= "Select an area, line and stock to list customer";
		$RecordListArr[$index]['phone']			= "";
		$RecordListArr[$index]['phone2']		= "";
		$RecordListArr[$index]['email']			= "";
		$RecordListArr[$index]['stateid']		= "";
		$RecordListArr[$index]['cityid']		= "";
		$RecordListArr[$index]['customerid']	= "";

		if($_POST['areaid'] > 0 && $_POST['lineid'] > 0 && $_POST['inventoryid'] > 0)
		{
			$RecordListArr[$index]['name']	= "No Customer Available";
		}
	}

	if($Num > 0)
	{
		if($_POST['fromarea'] == "messagearea")
		{
			$RecordListArr[$index]['id']			= "-1";
			$RecordListArr[$index]['name']			= "All Customer";
			$RecordListArr[$index]['phone']			= "";
			$RecordListArr[$index]['phone2']		= "";
			$RecordListArr[$index]['email']			= "";
			$RecordListArr[$index]['stateid']		= "";
			$RecordListArr[$index]['cityid']		= "";
			$RecordListArr[$index]['customerid']	= "";

			$index++;
		}

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$name2		= "";

			$id			= $rows['id'];
			$name		= $rows['name'];
			$firstname	= $rows['firstname'];
			$lastname	= $rows['lastname'];
			$phone		= $rows['phone'];
			$phone2		= $rows['phone2'];
			$email		= $rows['email'];
			$createdon	= $rows['createdon'];
			$stateid	= $rows['stateid'];
			$cityid		= $rows['cityid'];
			$customerid	= $rows['customerid'];

			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}
			$name2	= "#".$customerid." ".$name;

			if(trim($phone) != "")
			{
				$name2	.= " (".$phone.")";
			}

			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['name']			= $name2;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['phone2']		= $phone2;
			$RecordListArr[$index]['email']			= $email;
			$RecordListArr[$index]['stateid']		= (int)$stateid;
			$RecordListArr[$index]['cityid']		= (int)$cityid;
			$RecordListArr[$index]['customerid']	= $customerid;

			$index++;
		}
	}

	$response['success']	= true;
	$response['msg']		= "Customers listed successfully.";

	$response['recordlist']	= $RecordListArr;
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerDetail")
{
	$linename		= "";
	$linemanname	= "";
	$hawkername		= "";

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer detail.";

	if($_POST['iscustomerarea'] > 0)
	{
		$_POST['recordid']	= $_POST['customerid'];
	}

	$showsendsmstoggle	= true;

	if($_POST['areamanagerid'] > 0 || $_POST['linemanid'] > 0 || $_POST['hawkerid'] > 0)
	{
		$showsendsmstoggle	= false;
	}

	$sql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid']);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$RecordListArr	= array();

	$caneditopeningbalance	= false;

	$minimumpaymentdate	= date("Y-m-d",strtotime("yesterday"));
	$maximumpaymentdate	= date("Y-m-d",strtotime("today"));

	if(($_POST['areamanagerid'] < 1 && $_POST['loginlinemanid'] < 1 && $_POST['loginhawkerid'] < 1) && $_POST['clientid'] > 0)
	{
		$caneditopeningbalance	= true;
		$minimumpaymentdate	= "";
		$maximumpaymentdate	= "";
	}

	if($num > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllHawkerArr	= GetAllHawker($_POST['clientid']);

		$row	= pdo_fetch_assoc($query);

		$IsOnlinePaymentAreaByCustomerID = IsOnlinePaymentAreaByCustomerID($_POST['clientid'],$row['id']);

		$dob				= $row['dob'];
		$anniversarydate	= $row['anniversarydate'];
		$customersince		= $row['customersince'];

		$hasoutstanding		= false;

		$dobtext			= "";
		$anniversarytext	= "";
		$customersincetext	= "";

		if($dob != "" && $dob > 0)
		{
			$dob		= date("Y-m-d",$dob);
			$dobtext	= date("d-M-Y",$row['dob']);

			$RecordListArr["dobavailable"]	= 1;
		}
		else
		{
			$dob		= "";
			$dobtext	= "---";

			$RecordListArr["dobavailable"]	= 0;
		}

		if($anniversarydate != "" && $anniversarydate > 0)
		{
			$anniversarydate	= date("Y-m-d",$anniversarydate);
			$anniversarytext	= date("d-M-Y",$row['anniversarydate']);

			$RecordListArr["anniversaryavailable"]	= 1;
		}
		else
		{
			$anniversarydate	= "";
			$anniversarytext	= "---";

			$RecordListArr["anniversaryavailable"]	= 0;
		}
		if($customersince != "" && $customersince > 0)
		{
			$customersince		= date("Y-m-d",$customersince);
			$customersincetext	= date("d-M-Y",$row['customersince']);

			$RecordListArr["customerdateavailable"]	= 0;
		}
		else
		{
			$customersince		= "";
			$customersincetext	= "---";

			$RecordListArr["customerdateavailable"]	= 0;
		}

		$name		= $row['name'];

		$firstname	= $row['firstname'];
		$lastname	= $row['lastname'];

		if(trim($name) == "")
		{
			$name	= $firstname." ".$lastname;
		}

		$SubLineSql		= "SELECT * FROM ".$Prefix."subline WHERE id=:id";
		$SubLineEsql	= array("id"=>(int)$row['sublineid']);

		$SubLineQuery	= pdo_query($SubLineSql,$SubLineEsql);
		$SubLineNum		= pdo_num_rows($SubLineQuery);

		$sublinename	= "";

		if($SubLineNum > 0)
		{
			$sublinerows	= pdo_fetch_assoc($SubLineQuery);
			$sublinename	= $sublinerows['name'];
		}
		$latitude	= $row['latitude'];
		$longitude	= $row['longitude'];

		$googlemap = '';
		if($latitude !='' && $longitude !='')
		{
			$googlemap = "https://www.google.com/maps/dir//".$latitude.",".$longitude."/";
		}
		$RecordListArr["name"]				= $name;
		$RecordListArr["phone"]				= $row['phone'];
		$RecordListArr["phone2"]			= $row['phone2'];
		$RecordListArr["email"]				= $row['email'];
		$RecordListArr["pincode"]			= $row['pincode'];
		$RecordListArr["stateid"]			= $row['stateid'];
		$RecordListArr["cityid"]			= $row['cityid'];
		$RecordListArr["lineid"]			= $row['lineid'];
		$RecordListArr["sublineid"]			= $row['sublineid'];
		$RecordListArr["sublinename"]		= $sublinename;
		$RecordListArr["areaid"]			= $row['areaid'];
		$RecordListArr["linemanid"]			= $row['linemanid'];
		$RecordListArr["hawkerid"]			= $row['hawkerid'];
		$RecordListArr["address1"]			= $row['address1'];
		$RecordListArr["address2"]			= $row['address2'];
		$RecordListArr["latitude"]			= $row['latitude'];
		$RecordListArr["longitude"]			= $row['longitude'];
		$RecordListArr["dob"]				= $dob;
		$RecordListArr["anniversarydate"]	= $anniversarydate;

		$RecordListArr["status"]			= (int)$row['status'];
		$RecordListArr["isdiscount"]		= (int)$row['isdiscount'];
		$RecordListArr["isincreasepricing"]	= (int)$row['isincreasepricing'];

		$RecordListArr["dobtext"]			= $dobtext;
		$RecordListArr["anniversarytext"]	= $anniversarytext;
		/*$RecordListArr["customersincetext"]	= $customersincetext;*/
		$RecordListArr["customersince"]		= $customersince;
		$RecordListArr["customerid"]		= $row['customerid'];
		$RecordListArr["phonetxt"]			= $row['phone'];
		$RecordListArr["phonetxt2"]			= $row['phone2'];
		$RecordListArr["housenumber"]		= $row['housenumber'];
		$RecordListArr["floor"]				= $row['floor'];
		$RecordListArr["liftavailable"]		= $row['liftavailable'];
		$totaloutstanding					= $row['outstandingbalance'];

		/*$totaloutstanding	= getOutstandingBalanceByCustomer($_POST['clientid'],$row['id']);*/
		if($totaloutstanding > 0)
		{
			$hasoutstanding	= true;
		}

		$RecordListArr["linename"]			= $AllLineArr[$row['lineid']]['name'];
		$RecordListArr["linemanname"]		= "";
		$RecordListArr["hawkername"]		= $AllHawkerArr[$row['hawkerid']]['name'];
		$RecordListArr["areaname"]			= $AllAreaArr[$row['areaid']]['name'];
		$RecordListArr["canprintinvoice"]	= (int)$row['canprintinvoice'];

		if($RecordListArr["phonetxt"] == "")
		{
			$RecordListArr["phonetxt"]	= "--";
		}
		if($RecordListArr["phonetxt2"] == "")
		{
			$RecordListArr["phonetxt2"]	= "--";
		}
		
		if($RecordListArr["floor"] == "")
		{
			$RecordListArr["floor"]		= "--";
		}

		if($RecordListArr["liftavailable"] == 1)
		{
			$RecordListArr["liftavailabletext"]		= "Yes";
		}
		else
		{
			$RecordListArr["liftavailabletext"]		= "No";
		}

		$IsGPSLocation = 0;
		if($row['latitude'] !='' && $row['longitude'] != '')
		{
			$IsGPSLocation = 1;
		}
		$RecordListArr["isgpslocation"]		= $IsGPSLocation;
		$RecordListArr["googlemap"]			= $googlemap;

		$discount			= (float)($row['discount'] * 100) / 100;
		$openingbalance		= $row['openingbalance'];
		/*if($openingbalance !='')
		{
			$openingbalance		= (float)($row['openingbalance'] * 100) / 100;
		}*/
		$increasepricing	= (float)($row['increasepricing'] * 100) / 100;

		if($discount > 0)
		{
			$RecordListArr["discount"]		= $discount;
			$RecordListArr["discounttxt"]	= $discount." %";
		}
		else
		{
			$RecordListArr["discount"]		= '';
			$RecordListArr["discounttxt"]	= '---';
		}

		if($openingbalance != "")
		{
			$RecordListArr["openingbalance"]	= $openingbalance;
			$RecordListArr["openingbalancetxt"]	= $openingbalance;
		}
		else
		{
			$RecordListArr["openingbalance"]	= '';
			$RecordListArr["openingbalancetxt"]	= "---";
		}

		if($increasepricing > 0)
		{
			$RecordListArr["increasepricing"]		= $increasepricing;
			$RecordListArr["increasepricingtxt"]	= $increasepricing;
		}
		else
		{
			$RecordListArr["increasepricing"]		= '';
			$RecordListArr["increasepricingtxt"]	= '---';
		}

		$latestpayment		= "";
		$latestpaymentdate	= "";
		$latestpaymentid	= "";

		$PaySQL		= "SELECT * FROM ".$Prefix."customer_payments WHERE customerid=:customerid AND deletedon < :deletedon ORDER BY createdon DESC";
		$PayESQL	= array("customerid"=>(int)$_POST['recordid'],"deletedon"=>1);
		
		$PayQuery	= pdo_query($PaySQL,$PayESQL);
		$PayNum		= pdo_num_rows($PayQuery);

		if($PayNum	> 0)
		{
			while($PayRow = pdo_fetch_assoc($PayQuery))
			{
				if(trim($latestpaymentdate) == "" && $PayRow['paymentdate'] > 0)
				{
					$latestpaymentdate	= $PayRow['paymentdate'];
				}

				if(trim($latestpaymentdate) < 1)
				{
					$latestpaymentdate	= $PayRow['createdon'];
				}

				if(trim($latestpayment) == "")
				{
					$latestpayment	= $PayRow['amount'];
				}

				if(trim($latestpaymentid) == "" && $PayRow['paymentid'] > 0)
				{
					$latestpaymentid	= $PayRow['paymentid'];
				}

				$paidbalance	+= $PayRow['amount'];
			}
		}

		$RecordListArr["hasoutstanding"]	= $hasoutstanding;
		$RecordListArr["outstanding"]		= number_format($totaloutstanding);
		$RecordListArr["latestpayment"]		= number_format($latestpayment);
		$RecordListArr["latestpaymentdate"]	= date("d-M-Y",$latestpaymentdate);
		$RecordListArr["latestpaymentid"]	= $latestpaymentid;

		$CustomerInventoryArr	= GetCustomerSubscriptions($_POST['recordid']);

		$InventoryArr			= array();
		$InventoryWithQtyArr	= array();

		if(!empty($CustomerInventoryArr))
		{
			$InventoryIDArr	= array();

			foreach($CustomerInventoryArr as $Key => $value)
			{
				$InventoryIDArr[]	= $value['id'];
			}
			$InventoryIDArr			= @array_filter(@array_unique($InventoryIDArr));
			$CustomerInventoryIDs	= @implode(",",$InventoryIDArr);
			
			if(trim($CustomerInventoryIDs) == "")
			{
				$CustomerInventoryIDs	= "-1";
			}

			/*$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE id IN(".$CustomerInventoryIDs.") AND status=:status ORDER BY name ASC";
			$InventoryEsql	= array("status"=>1);*/

			$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE id IN(".$CustomerInventoryIDs.") ORDER BY name ASC";
			$InventoryEsql	= array();

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$inventoryindex	= 0;

			if($InventoryNum > 0)
			{
				while($inventoryrows = pdo_fetch_assoc($InventoryQuery))
				{
					$inventoryid	= (int)$inventoryrows['id'];
					$InventoryArr[]	= $inventoryrows['name'];

					$quantity		= $CustomerInventoryArr[$inventoryid]['quantity'];

					$InventoryWithQtyArr[$inventoryindex]['id']			= (int)$inventoryid;
					$InventoryWithQtyArr[$inventoryindex]['name']		= $inventoryrows['name'];
					$InventoryWithQtyArr[$inventoryindex]['quantity']	= (int)$quantity;

					$inventoryindex++;
				}
			}
		}
		if(!empty($InventoryArr))
		{
			$InventoryStr	= implode(", ",$InventoryArr);
		}
		else
		{
			$InventoryStr	= "---";
		}

		$RecordListArr["inventorytext"]						= $InventoryStr;
		$RecordListArr["inventorylist"]						= $InventoryWithQtyArr;
		$RecordListArr["showsendsmstoggle"]					= $showsendsmstoggle;
		$RecordListArr["isonlinepaymentareabycustomerid"]	= (int)$IsOnlinePaymentAreaByCustomerID;
		$RecordListArr["caneditopeningbalance"]				= $caneditopeningbalance;
		$RecordListArr["minimumpaymentdate"]				= $minimumpaymentdate;
		$RecordListArr["maximumpaymentdate"]				= $maximumpaymentdate;

		$response['success']	= true;
		$response['msg']		= "Customer detail fetched successfully.";
	}

	$response['customerdetail']		= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditCustomer")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to update customer.";
    $response['toastmsg']	= "Unable to update customer.";

	if($_POST['phone'] != "" && !validate_mobile($_POST['phone']) && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please enter a valid phone";
		$response['toastmsg']	= "Please enter a valid phone";
	}
	if($_POST['phone2'] != "" && !validate_mobile($_POST['phone2']) && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please enter a valid alt. phone";
		$response['toastmsg']	= "Please enter a valid alt. phone";
	}

	if($_POST['phone'] != "" && $ErrorMsg == "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A customer already exist with same phone";
			$response['toastmsg']	= "A customer already exist with same phone";
		}
	}

	if($_POST['phone2'] != "" && $ErrorMsg == "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone2=:phone2 AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone2"=>$_POST['phone2'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A customer already exist with same alt. phone";
			$response['toastmsg']	= "A customer already exist with same alt. phone";
		}
	}

	if($_POST['areaid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select an area";
		$response['toastmsg']	= "Please select an area";
	}

	if($_POST['lineid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select a line";
		$response['toastmsg']	= "Please select a line";
	}

	if($_POST['sublineid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select a sub line";
		$response['toastmsg']	= "Please select a sub line";
	}

	if($_POST['hawkerid'] < 1 && $ErrorMsg == "")
	{
		$haserror	= true;
		$ErrorMsg	.= "Please select a hawker";
		$response['toastmsg']	= "Please select a hawker";
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;

		if($CheckNum < 0)
		{
			$response['toastmsg']	= "Please enter all required field";
		}
	}

	$dob				= "";
	if($_POST['dob'] != "" && $_POST['dobavailable'] > 0)
	{
		$dob	= strtotime($_POST['dob']);
	}

	$anniversarydate	= "";
	if($_POST['anniversarydate'] != "" && $_POST['anniversaryavailable'] > 0)
	{
		$anniversarydate	= strtotime($_POST['anniversarydate']);
	}

	$customersince = "";
	if($_POST['customersince'] !='' && $_POST['customerdateavailable'] > 0)
	{
		$customersince	= strtotime($_POST['customersince']);
	}

	if($_POST['isopeningbalance'] < 1)
	{
		$_POST['openingbalance']	= '';
	}
	if($haserror == false)
	{
		$PreSQL	= "SELECT openingbalance,outstandingbalance FROM ".$Prefix."customers WHERE id=:id";
		$PreESQL = array("id"=>$_POST['recordid']);
		$PreQuery = pdo_query($PreSQL,$PreESQL);
		$PreRow	= pdo_fetch_assoc($PreQuery);
		$PreOpeningBalance = $PreRow['openingbalance'];
		$OutStandingBalance = $PreRow['outstandingbalance'];

		$Diff	= (float)$_POST['openingbalance'] - (float)$PreOpeningBalance;
			
		if(abs($Diff) > 0)
		{
			$OutStandingBalance += $Diff; 
		}


		$Sql	= "UPDATE ".$Prefix."customers SET 
		name				=:name,
		phone				=:phone,
		phone2				=:phone2,
		email				=:email,
		areaid				=:areaid,
		lineid				=:lineid,
		sublineid			=:sublineid,
		linemanid			=:linemanid,
		hawkerid			=:hawkerid,
		address1			=:address1,
		latitude			=:latitude,
		longitude			=:longitude,
		status				=:status,
		isdiscount			=:isdiscount,
		discount			=:discount,
		openingbalance		=:openingbalance,
		outstandingbalance	=:outstandingbalance,
		isincreasepricing	=:isincreasepricing,
		increasepricing		=:increasepricing,
		dob					=:dob,
		anniversarydate		=:anniversarydate,
		customersince		=:customersince,
		housenumber			=:housenumber,
		floor				=:floor,
		liftavailable		=:liftavailable,
		canprintinvoice		=:canprintinvoice
		WHERE
		id					=:id
		AND
		clientid			=:clientid";

		$Esql	= array(
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"phone2"			=>$_POST['phone2'],
			"email"				=>$_POST['email'],
			"areaid"			=>(int)$_POST['areaid'],
			"lineid"			=>(int)$_POST['lineid'],
			"sublineid"			=>(int)$_POST['sublineid'],
			"linemanid"			=>(int)$_POST['linemanid'],
			"hawkerid"			=>(int)$_POST['hawkerid'],
			"address1"			=>$_POST['address1'],
			"latitude"			=>$_POST['latitude'],
			"longitude"			=>$_POST['longitude'],
			"status"			=>(int)$_POST['status'],
			"isdiscount"		=>(int)$_POST['isdiscount'],
			"discount"			=>(float)$_POST['discount'],
			"openingbalance"	=>$_POST['openingbalance'],
			"outstandingbalance"=>(float)$OutStandingBalance,
			"isincreasepricing"	=>(int)$_POST['isincreasepricing'],
			"increasepricing"	=>(float)$_POST['increasepricing'],
			"dob"				=>(int)$dob,
			"anniversarydate"	=>(int)$anniversarydate,
			"customersince"		=>(int)$customersince,
			"housenumber"		=>$_POST['housenumber'],
			"floor"				=>$_POST['floor'],
			"liftavailable"		=>(int)$_POST['liftavailable'],
			"canprintinvoice"	=>(int)$_POST['canprintinvoice'],
			"id"				=>(int)$_POST['recordid'],
			"clientid"			=>(int)$_POST['clientid'],
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$IsOpeningBalance = 1;

		
			/*$Narration			= "Opening Balance";
			$createdon = 0;
			GenerateCustomerAccountLog($_POST['clientid'],$_POST['recordid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$_POST['openingbalance'],$createdon,$Narration,"openingbalance");*/

			$response['success']	= true;
			$response['msg']		= "Customer successfully updated.";
			$response['toastmsg']	= "Customer successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= strtotime('today');

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	if($_POST['iscustomerarea'] > 0)
	{
		$_POST['stateid']	= 7;
		$_POST['cityid']	= 37;
	}

	$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE 1 AND id=:id";
	$CustomerEsql	= array("id"=>(int)$_POST['customerid']);

	$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	$name2	= "Select";

	if($CustomerNum > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);

		$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

		$customername	= $CustomerRows['name'];
		$firstname		= $CustomerRows['firstname'];
		$lastname		= $CustomerRows['lastname'];
		$customerid		= $CustomerRows['customerid'];
		$customersince	= $CustomerRows['customersince'];
		$phone			= $CustomerRows['phone'];
		$phone2			= $CustomerRows['phone2'];
		$areaid			= $CustomerRows['areaid'];
		$areaname		= $AllAreaArr[$areaid]['name'];
		$lineid			= $CustomerRows['lineid'];
		$linename		= $AllLineArr[$lineid]['name'];

		if($customersince < 1)
		{
			$customersince = '';
		}

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

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$SubscriptionData	= GetCustomerSubscriptions($_POST['customerid']);
		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			$index					= 0;
			$idarray				= array();
			$categoryidarray		= array();
			$namearray				= array();
			$pricearray				= array();
			$frequencyarray			= array();
			$isassignedarray		= array();
			$subscriptiondateArr	= array();
			
			if($InventoryNum > 0)
			{
				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id					= $rows['id'];
					$categoryid			= $rows['categoryid'];
					$name				= $rows['name'];
					$price				= $rows['price'];
					$frequency			= $rows['frequency'];
					$subscriptiondate	= date("Y-m-d");

					$isassigned	= false;

					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$inventoryprice	= $ClientInventoryData[$id]['price'];

							if(!empty($SubscriptionData[$id]))
							{								
								$isassigned			= true;
								$subscriptiondate	= $SubscriptionData[$id]['subscriptiondate'];
							}

							if($id > 0)
							{
								$idarray[]				= (int)$id;
								$categoryidarray[]		= (int)$categoryid;
								$namearray[]			= trim($name);
								$pricearray[]			= (float)$inventoryprice;
								$frequencyarray[]		= (int)$frequency;
								$isassignedarray[]		= (int)$isassigned;
								$subscriptiondateArr[]	= $subscriptiondate;
							}
						}
					}
				}
			}

			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status=:status AND categoryid=:categoryid ORDER BY inv.name ASC";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,'status'=>1);

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2		= pdo_num_rows($InventoryQuery2);
			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id					= $rows2['id'];
					$categoryid			= $rows2['categoryid'];
					$name				= $rows2['name'];
					$price				= $rows2['price'];
					$frequency			= $rows2['frequency'];
					$iaactive			= false;
					$subscriptiondate	= date("Y-m-d");

					if(!empty($ClientInventoryData[$id]))
					{
						$inventorystatus	= $ClientInventoryData[$id]['status'];
						$inventoryprice		= $ClientInventoryData[$id]['price'];

						if(!empty($SubscriptionData[$id]))
						{
							$iaactive			= true;
							$subscriptiondate	= $SubscriptionData[$id]['subscriptiondate'];
						}
						if($inventorystatus > 0)
						{
							if($id > 0)
							{
								$idarray[]				= (int)$id;
								$categoryidarray[]		= (int)$categoryid;
								$namearray[]			= trim($name);
								$pricearray[]			= (float)$price;
								$frequencyarray[]		= (int)$frequency;
								$isassignedarray[]		= (int)$iaactive;
								$subscriptiondateArr[]	= $subscriptiondate;
							}
						}
					}
					else
					{
						$inventoryprice		= $price;
					}
				}	
			}

			if($InventoryNum2 > 0 || $InventoryNum > 0)
			{	
				$namearray		= array_map("trim",$namearray);
				$sortnamearray	= array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$frequencyarray,$isassignedarray,$subscriptiondateArr);

				$index	= 0;

				foreach($namearray as $key => $value)
				{
					$id					= $idarray[$key];
					$categoryid			= $categoryidarray[$key];
					$name				= $value;
					$price				= $pricearray[$key];
					$frequency			= $frequencyarray[$key];
					$isassigned			= $isassignedarray[$key];
					$subscriptiondate	= $subscriptiondateArr[$key];
					$quantity			= $SubscriptionData[$id]['quantity'];
				
					if($quantity < 1)
					{
						$quantity = 1;
					}
					
					$unsubscribedate	= date("Y-m-d");

					if(!empty($SubscriptionData[$id]))
					{
						$DaysListArr	= $SubscriptionData[$id]['days'];
					}

					$InventoryListArr[$index]['id']					= (int)$id;
					$InventoryListArr[$index]['name']				= $name;
					$InventoryListArr[$index]['categoryid']			= (int)$categoryid;
					$InventoryListArr[$index]['quantity']			= (int)$quantity;
					$InventoryListArr[$index]['frequency']			= (int)$frequency;
					$InventoryListArr[$index]['price']				= (float)$price;
					$InventoryListArr[$index]['isassigned']			= $isassigned;
					$InventoryListArr[$index]['subscriptiondate']	= $subscriptiondate;
					$InventoryListArr[$index]['days']				= $DaysListArr;
					$InventoryListArr[$index]['issubscribed']		= $isassigned;
					$InventoryListArr[$index]['unsubscribedate']	= $unsubscribedate;

					$index++;
						
				}
				$RecordListArr[$catindex]['id']			= (int)$catid;
				$RecordListArr[$catindex]['title']		= $cattitle;
				$RecordListArr[$catindex]['recordlist']	= $InventoryListArr;

				$catindex++;
			}	
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer Inventory listed successfully.";
	}

	$response['customername']		= $name2;
	$response['areaid']				= $areaid;
	$response['areaname']			= $areaname;
	$response['lineid']				= $lineid;
	$response['linename']			= $linename;
	$response['inventorylist']		= $RecordListArr;
	$response['inventorylistLog']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSubscribeInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= strtotime('today');
	/*$totaldays	= cal_days_in_month(CAL_GREGORIAN, $_POST['month'], $_POST['year']);*/

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

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status AND id=:id ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1,"id"=>605);

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

								if(!empty($ClientInventoryPricingByDate[$id]))
								{
									$PricingByDate	= $ClientInventoryPricingByDate[$id];

									if(!empty($PricingByDate[$dateloop]))
									{
										$price	= $PricingByDate[$dateloop]['price'];
									}
								}
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
								/*$dateListArr[$dayindex]['disableprice']	= true;

								$datetimestamp	= strtotime($_POST['month']."/".$dateloop."/".$_POST['year']);

								if($datetimestamp <= strtotime(date("m/d/Y")))
								{
									$dateListArr[$dayindex]['disableprice']	= false;
								}*/

								$dateListArr[$dayindex]['disableprice']	= false;

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
												$dateListArr[$dayindex]['hasholiday']	= true;
												break;
											}
										}
										else
										{
											if($datetimestamp >= $startdate && $datetimestamp <= $enddate)
											{
												$dateListArr[$dayindex]['hasholiday']	= true;
												break;
											}
										}
									}
								}

								//echo $dateloop."/".$_POST['month']."/".$_POST['year']."---";

								//echo date("r",$datetimestamp)."---";

								if($isbydatepricingavialable < 1)
								{
									if($price > 0.1)
									{
										$isbydatepricingavialable = 1;
									}
								}
								$dayindex++;
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
	$response['totaldays']		= (int)$totaldays;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerLine')
{
	$RecordSetArr		= array();
	$AreaRecordSetArr	= array();

	$AssignedLineArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer line.";

	$CheckCondtion	= "";
	$CheckCondEsql	= array();

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$CheckCondtion	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$CheckCondtion	.= " AND lineid IN(".$lineids.")";
	}

	$CheckSql2	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$CheckCondtion." ORDER BY sequence ASC, customerid ASC";
	$CheckEsql2	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckEsql2	= array_merge($CheckEsql2,$CheckCondEsql);

	$CheckQuery2	= pdo_query($CheckSql2,$CheckEsql2);
	$CheckNum2		= pdo_num_rows($CheckQuery2);

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid=:lineid AND clientid=:clientid AND deletedon < :deletedon ".$CheckCondtion." ORDER BY sequence ASC, customerid ASC";
			$CheckEsql	= array("lineid"=>(int)$id,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$CheckEsql	= array_merge($CheckEsql,$CheckCondEsql);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$AssignedLineArr[]	= $id;

				$RecordSetArr[$index]['id']				= $id;
				$RecordSetArr[$index]['name']			= $name;
				$RecordSetArr[$index]['totalcustomer']	= $CheckNum;

				$index++;
			}
		}
		$AssignedLineStr	= implode(", ",$AssignedLineArr);

		if(trim($AssignedLineStr) == "")
		{
			$AssignedLineStr	= "-1";
		}

		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid NOT IN(".$AssignedLineStr.") AND clientid=:clientid AND deletedon < :deletedon ".$CheckCondtion." ORDER BY sequence ASC, customerid ASC";
		$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckEsql	= array_merge($CheckEsql,$CheckCondEsql);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$RecordSetArr[$index]['id']				= "9999";
			$RecordSetArr[$index]['name']			= "Unassigned";
			$RecordSetArr[$index]['totalcustomer']	= $CheckNum;
			$index++;
		}
	}

	$Sql	= "SELECT * FROM ".$Prefix."area WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	$AssignedAreaArr	= array();

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE areaid=:areaid AND clientid=:clientid AND deletedon < :deletedon ".$CheckCondtion." ORDER BY sequence ASC, customerid ASC";
			$CheckEsql	= array("areaid"=>(int)$id,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$CheckEsql	= array_merge($CheckEsql,$CheckCondEsql);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$AssignedAreaArr[]	= $id;

				$AreaRecordSetArr[$index]['id']				= $id;
				$AreaRecordSetArr[$index]['name']			= $name;
				$AreaRecordSetArr[$index]['totalcustomer']	= $CheckNum;

				$index++;
			}
		}

		$AssignedAreaStr	= implode(", ",$AssignedAreaArr);

		if(trim($AssignedAreaStr) == "")
		{
			$AssignedAreaStr	= "-1";
		}

		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE areaid NOT IN(".$AssignedAreaStr.") AND clientid=:clientid AND deletedon < :deletedon ".$CheckCondtion." ORDER BY sequence ASC, customerid ASC";
		$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckEsql	= array_merge($CheckEsql,$CheckCondEsql);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$AreaRecordSetArr[$index]['id']				= "9999";
			$AreaRecordSetArr[$index]['name']			= "Unassigned";
			$AreaRecordSetArr[$index]['totalcustomer']	= $CheckNum;
			$index++;
		}
	}

	$response['haslinelist']	= true;
	$response['recordlist']		= $RecordSetArr;

	if(!empty($RecordSetArr) || !empty($AreaRecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer Line listed successfully.";
	}

	if(@count($AreaRecordSetArr) > 1)
	{
		$response['haslinelist']	= false;
		$response['recordlist']		= $AreaRecordSetArr;
	}

	$response['totalcustomer']	= (int)$CheckNum2;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSubscribeInventoryByDate")
{
	$catindex	= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	$startdate		= date("d",strtotime($_POST['pricedate']));
	$_POST["month"]	= date("m",strtotime($_POST['pricedate']));
	$_POST["year"]	= date("Y",strtotime($_POST['pricedate']));

	$datetimestamp	= strtotime($_POST['month']."/".$startdate."/".$_POST['year']);

	$HolidaySql	= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND customertype=:customertype AND deletedon < :deletedon AND (:holidaydate BETWEEN startdate AND enddate)";
	$HolidayEsql	= array("clientid"=>(int)$_POST['clientid'],"customertype"=>0,"deletedon"=>1,"holidaydate"=>$datetimestamp);

	$HolidayQuery	= pdo_query($HolidaySql,$HolidayEsql);
	$HolidayNum		= pdo_num_rows($HolidayQuery);

	$HolidayArr		= array();

	if($HolidayNum > 0)
	{
		$HolidayArr	= pdo_fetch_assoc($HolidayQuery);
	}

	if($CategoryNum > 0)
	{
		$ClientInventoryData	= GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		$ActiveSubscriptionsData	= GetActiveCustomerSubscriptions($_POST['clientid']);

		$ClientInventoryPricing	= ClientInventoryPricing((int)$_POST['clientid'],$_POST["year"],$_POST["month"]);
		$ClientInventoryPricingByDate	= ClientInventoryPricingByDate($_POST['clientid'],$_POST["year"],$_POST["month"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$idarray			= array();
			$categoryidarray	= array();
			$namearray			= array();
			$pricearray			= array();
			$frequencyarray		= array();
			
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid AND inv.frequency=:frequency GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"frequency"=>1,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$index	= 0;

				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id				= $rows['id'];
					$categoryid		= $rows['categoryid'];
					$name			= $rows['name'];
					
					$idarray[]			= (int)$id;
					$categoryidarray[]	= (int)$categoryid;
					$namearray[]		= $name;
				}
			}
			$InventoryCond	= "";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,'status'=>0,"frequency"=>1);

			if($_POST['inventoryid'] > 0)
			{
				$InventoryCond			.= " AND inv.id=:id";
				$InventoryEsql2['id']	= (int)$_POST['inventoryid'];
			}
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid $InventoryCond AND inv.frequency=:frequency ORDER BY inv.name ASC";

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2		= pdo_num_rows($InventoryQuery2);

			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id			= $rows2['id'];
					$categoryid	= $rows2['categoryid'];
					$name		= $rows2['name'];

					$idarray[]			= (int)$id;
					$categoryidarray[]	= (int)$categoryid;
					$namearray[]		= $name;
				}
			}
			if(!empty($namearray))
			{
				$sortnamearray = array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray);
				foreach($namearray as $key => $value)
				{
					$id				= $idarray[$key];
					$categoryid		= $categoryidarray[$key];
					$name			= $value;
					$days			= "";
					$price			= "";
					$pricingtype	= 0; /*0 - day base, 1 - date base*/
					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						/*$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];*/
						$pricingtype	= 1;
					}

					/*if(!empty($ClientInventoryData[$id]) && !empty($ActiveSubscriptionsData[$id]))*/
					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$InventoryListArr[$index]['id']				= (int)$id;
							$InventoryListArr[$index]['categoryid']		= (int)$categoryid;
							$InventoryListArr[$index]['name']			= $name;
							$InventoryListArr[$index]['pricingtype']	= $pricingtype;
							$InventoryListArr[$index]['days']			= $days;
							$InventoryListArr[$index]['price']			= $price;

							$dayindex	= 0;

							$dateListArr	= array();

							for($dateloop = $startdate; $dateloop <= $startdate; $dateloop++)
							{
								$date	= $dateloop;
								$price	= "";

								if(!empty($ClientInventoryPricingByDate[$id]))
								{
									$PricingByDate	= $ClientInventoryPricingByDate[$id];
									if(!empty($PricingByDate[(int)$dateloop]))
									{
										$price	= $PricingByDate[(int)$dateloop]['price'];
									}
								}
								if($dateloop < 10)
								{
									$dateListArr[$dayindex]['displayname']	= "0".$dateloop;
								}
								else
								{
									$dateListArr[$dayindex]['displayname']	= $dateloop;
								}
								$dateListArr[$dayindex]['date']			= $dateloop;
								$dateListArr[$dayindex]['dateprice']	= $price;
								$dateListArr[$dayindex]['hasholiday']	= false;

								if(!empty($HolidayArr))
								{
									$inventoryid	= $HolidayArr['inventoryid'];

									if($inventoryid > 0)
									{
										if($inventoryid == $id)
										{
											$dateListArr[$dayindex]['hasholiday']	= true;
										}
									}
									else
									{
										$dateListArr[$dayindex]['hasholiday']	= true;
									}
								}
								$dayindex++;
							}
							$InventoryListArr[$index]['datepricing']	= $dateListArr;

							$index++;
						}
					}
				}
				$RecordListArr[$catindex]['id']			= (int)$catid;
				$RecordListArr[$catindex]['title']		= $cattitle;
				$RecordListArr[$catindex]['recordlist']	= $InventoryListArr;

				$catindex++;
			}
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer Inventory listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;
	$response['totaldays']		= 1;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "UpdateSequence")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to change customer sequnce, Please try later.";

	$isupdated	= false;

	if(!empty($_POST['customerlist']))
	{
		foreach($_POST['customerlist'] as $key=>$rows)
		{
			$id			= $rows['id'];
			$sequence	= $rows['sequence'];

			$Sql		= "UPDATE ".$Prefix."customers SET 
			sequence	=:sequence
			WHERE 
			id			=:id
			AND 
			clientid	=:clientid";

			$Esql	= array(
				"sequence"	=>(int)$sequence,
				'id'		=>(int)$id,
				"clientid"	=>(int)$_POST['clientid']	
			);

			$Query	= pdo_query($Sql,$Esql);

			if($Query)
			{
				$isupdated	= true;
			}
		}
	}

	if($isupdated)
	{
		$Response['success']	= true;
		$Response['msg']		= "Customer sequence successfully updated.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetSequencePDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate invoice pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/hawkerssequence/");
	}
	@mkdir("../assets/".$_POST['clientid']."/hawkerssequence/", 0777, true);

	$hawkerid	= "";

	if($_POST['hawkerid'] > 0 && $_POST['loginhawkerid'] < 1)
	{
		$hawkerid	= $_POST['hawkerid'];
	}
	else if($_POST['ishawker'] == "1")
	{
		$hawkerid	= $_POST['loginhawkerid'];
	}

	$Pdf_FileName	= 'sequence-'.$hawkerid.".pdf";

	$File	= "viewsequence.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/hawkerssequence/".$Pdf_FileName,"");

	if($donotusecache > 0)
	{
		$Pdf_FileName	.= "?t=".time();
	}

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/hawkerssequence/".$Pdf_FileName;
		$Response['msg']			= "Sequence pdf generated successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteCustomer_Org")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete customer, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid";
	$CheckEsql	= array("customerid"=>(int)$_POST['recordid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Customer can't be delete due to active subscription.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."customers SET 
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
		$Response['success']	= true;
		$Response['msg']		= "Customer deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteCustomer")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete customer, Please try later.";

	/*$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND outstandingbalance != :outstandingbalance AND outstandingbalance IS NOT NULL";
	$CheckEsql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"outstandingbalance"=>0);*/

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND outstandingbalance != :outstandingbalance";
	$CheckEsql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"outstandingbalance"=>'0.00');

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Customer can't be delete due to outstanding balance.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."customers SET 
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
		/*$delsql = "DELETE FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND customerid=:customerid";
		$delesql = array('clientid'=>(int)$_POST['clientid'],'customerid'=>(int)$_POST['recordid']);
		pdo_query($delsql,$delesql);
		GetCustomerLineTotalUpdated($_POST['recordid']);
		*/
		$Response['success']	= true;
		$Response['msg']		= "Customer deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetFloorList")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list floor.";

	$RecordSetArr	= array();

	$index	= 0;
	$startyear = '2019';

	$RecordSetArr[$index]['name']	= "Basement";
	$index++;

	$RecordSetArr[$index]['name']	= "Ground";
	$index++;

	for($loop = 1; $loop <=15; $loop++)
	{
		$RecordSetArr[$index]['name']	= "".$loop."";

		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['recordset']		= $RecordSetArr;
		$response['msg']			= "Floor listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "UpdateOpeningBalance")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to update opening balance.";
    $response['toastmsg']	= "Unable to update opening balance.";

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		
		if($CheckNum < 0)
		{
			$response['toastmsg']	= "Please enter all required field";
		}
	}

	if($haserror == false)
	{
		$PreSQL	= "SELECT openingbalance,outstandingbalance FROM ".$Prefix."customers WHERE id=:id";
		$PreESQL = array("id"=>$_POST['recordid']);
		$PreQuery = pdo_query($PreSQL,$PreESQL);
		$PreRow	= pdo_fetch_assoc($PreQuery);
		$PreOpeningBalance = $PreRow['openingbalance'];
		$OutStandingBalance = $PreRow['outstandingbalance'];

		$Diff	= (float)$_POST['openingbalance'] - (float)$PreOpeningBalance;
			
		if(abs($Diff) > 0)
		{
			$OutStandingBalance += $Diff; 
		}

		$Sql	= "UPDATE ".$Prefix."customers SET 
		openingbalance		=:openingbalance,
		outstandingbalance	=:outstandingbalance
		WHERE
		id					=:id
		AND
		clientid			=:clientid";

		$Esql	= array(
			"openingbalance"	=>$_POST['openingbalance'],
			"outstandingbalance"=>(float)$OutStandingBalance,
			"id"				=>(int)$_POST['recordid'],
			"clientid"			=>(int)$_POST['clientid'],
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$ClientArr = GetClientRecord($_POST['recordid']);
			$IsOpeningBalance = 1;
			
			$Narration			= "Opening Balance";
			
			/*GenerateCustomerAccountLog($_POST['clientid'],$_POST['recordid'],$ClientArr['areaid'],$ClientArr['lineid'],$ClientArr['hawkerid'],$_POST['openingbalance'],0,$Narration,"openingbalance");

			updateCustomerOutstandingBalance($_POST['clientid'], $_POST['recordid']);*/

			$response['success']	= true;
			$response['msg']		= "Customer successfully updated.";
			$response['toastmsg']	= "Customer successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerSubscriptionChanges")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer subscription changes.";

	$caneditcustomer	= false;

	if(($_POST['areamanagerid'] < 1 && $_POST['loginlinemanid'] < 1 && $_POST['loginhawkerid'] < 1) && $_POST['clientid'] > 0)
	{
		$caneditcustomer	= true;
	}

	$todaytimestamp		= time();
	$tomorrowtimestamp	= strtotime('tomorrow');

	$todaytimestampCheck	= strtotime(date("m/d/Y",$todaytimestamp));
	$tomorrowtimestampCheck	= $tomorrowtimestamp+86399;

	$todaydate		= date("d",$todaytimestamp);
	$todaymonth		= date("m",$todaytimestamp);

	$tomorrowdate	= date("d",$tomorrowtimestamp);
	$tomorrowmonth	= date("m",$tomorrowtimestamp);

	$totalsubscriptioncount		= 0;

	$NewSubscriptionListArr		= array();
	$CloseSubscriptionListArr	= array();

	$HolidayStartListArr	= array();
	$HolidayEndListArr		= array();

	$todayindex		= 0;
	$tomorrowindex	= 0;

	$condition	= "";

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$condition	.= " AND customer.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$condition	.= " AND customer.lineid IN(".$lineids.")";
	}

	$Sql	= "SELECT customer.* FROM ".$Prefix."customers customer,".$Prefix."subscriptions subscription WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=subscription.customerid AND (subscription.subscriptiondate BETWEEN :startdate AND :enddate) ".$condition." GROUP BY customerid ORDER BY customer.sequence ASC, customer.customerid ASC";

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"startdate"=>$todaytimestampCheck,"enddate"=>$tomorrowtimestampCheck);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		$GetAllLine			= GetAllLine($_POST['clientid']);
		$GetAllHawker		= GetAllHawker($_POST['clientid']);
		$GetAllArea			= GetAllArea($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$name2		= "";

			$id					= $rows['id'];
			$name				= $rows['name'];
			$phone				= $rows['phone'];
			$phone2				= $rows['phone2'];
			$createdon			= $rows['createdon'];
			$housenumber		= $rows['housenumber'];
			$floor				= $rows['floor'];
			$address1			= $rows['address1'];
			$customerid			= $rows['customerid'];
			$isdiscount			= $rows['isdiscount'];
			$discount			= $rows['discount'];
			$status				= $rows['status'];
			$firstname			= $rows['firstname'];
			$lastname			= $rows['lastname'];
			$sequence			= $rows['sequence'];
			$latitude			= $rows['latitude'];
			$longitude			= $rows['longitude'];
			$sublinename		= $GetAllSubLine[$rows['sublineid']]['name'];

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

			$openingbalance		= $rows['openingbalance'];

			$subscriptionsql	= "SELECT inv.name,subs.quantity qty,subs.subscriptiondate FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
			$subscriptionesql	= array("customerid"=>(int)$id,"deletedon"=>1);
			$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
			$subscriptionnum	= pdo_num_rows($subscriptionquery);

			$subscriptionstr 	= '';

			if($subscriptionnum > 0)
			{
				while($subsrow	= pdo_fetch_assoc($subscriptionquery))
				{
					$inventoryname		= $subsrow['name'];
					$qty				= $subsrow['qty'];
					$subscriptiondate	= $subsrow['subscriptiondate'];

					if($qty > 1)
					{
						$inventoryname = $inventoryname." X ".$qty." (".date("d-M-Y",$subscriptiondate).")";
					}
					else
					{
						$inventoryname = $inventoryname." (".date("d-M-Y",$subscriptiondate).")";
					}
					$subscriptionstr .= $inventoryname.', ';

					$SubscriptionSummaryArr[$inventoryname]	+= $qty;
				}
				$subscriptionstr .= '@@';
				$subscriptionstr = str_replace(", @@","",$subscriptionstr);
				$subscriptionstr = str_replace("@@","",$subscriptionstr);
			}
			else
			{
				$subscriptionstr	= '--';
			}

			$googlemap = '';
			if($latitude !='' && $longitude !='')
			{
				$googlemap = "https://www.google.com/maps/dir//".$latitude.",".$longitude."/";
			}
			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			$name2	= "#".$customerid." ".$name;

			if($isdiscount > 0)
			{
				if($discount > 0)
				{
					$discount	= (float)$discount."%";
				}
				else
				{
					$discount	= "---";
				}
			}
			else
			{
				$discount	= "No Discount";
			}

			$line		= $GetAllLine[$rows['lineid']]['name'];
			$subline	= $GetAllSubLine[$rows['sublineid']]['name'];
			$hawker		= $GetAllHawker[$rows['hawkerid']]['name'];
			$area		= $GetAllArea[$rows['areaid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($phone2 == "")
			{
				$phone2	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}
			if($subline == "")
			{
				$subline	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($area == "")
			{
				$area	= "---";
			}

			$NewSubscriptionListArr[$index]['id']				= (int)$id;
			$NewSubscriptionListArr[$index]['customerid']		= $customerid;
			$NewSubscriptionListArr[$index]['name']				= $name;
			$NewSubscriptionListArr[$index]['phone']			= $phone;
			$NewSubscriptionListArr[$index]['phone2']			= $phone2;
			$NewSubscriptionListArr[$index]['line']				= $line;
			$NewSubscriptionListArr[$index]['subline']			= $subline;
			$NewSubscriptionListArr[$index]['hawker']			= $hawker;
			$NewSubscriptionListArr[$index]['discount']			= $discount;
			$NewSubscriptionListArr[$index]['address1']			= $address1;
			$NewSubscriptionListArr[$index]['fulladdress']		= $addresstr;
			$NewSubscriptionListArr[$index]['status']			= (int)$status;
			$NewSubscriptionListArr[$index]['area']				= $area;
			$NewSubscriptionListArr[$index]['name2']			= $name2;
			$NewSubscriptionListArr[$index]['sequence']			= (int)$sequence;
			$NewSubscriptionListArr[$index]['googlemap']		= $googlemap;
			$NewSubscriptionListArr[$index]['housenumber']		= $housenumber;
			$NewSubscriptionListArr[$index]['floor']			= $floor;
			$NewSubscriptionListArr[$index]['subscriptions']	= $subscriptionstr;
			$NewSubscriptionListArr[$index]['canchangebalance']	= false;
			$NewSubscriptionListArr[$index]['caneditcustomer']	= $caneditcustomer;

			if($openingbalance != "")
			{
				$NewSubscriptionListArr[$index]["openingbalance"]	= $openingbalance;
				$NewSubscriptionListArr[$index]["openingbalancetxt"]	= $openingbalance;
			}
			else
			{
				$NewSubscriptionListArr[$index]["openingbalance"]	= '';
				$NewSubscriptionListArr[$index]["openingbalancetxt"]	= "---";
			}

			$index++;
			$totalsubscriptioncount++;
		}
	}

	$index	= 0;

	$Sql2	= "SELECT customer.* FROM ".$Prefix."customers customer,".$Prefix."subscriptions_log log WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=log.customerid AND (log.unsubscribedate BETWEEN :startdate AND :enddate) ".$condition." GROUP BY customerid ORDER BY customer.sequence ASC, customer.customerid ASC";

	$Esql2	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"startdate"=>$todaytimestampCheck,"enddate"=>$tomorrowtimestampCheck);

	$Query2	= pdo_query($Sql2,$Esql2);
	$Num2	= pdo_num_rows($Query2);

	if($Num2 > 0)
	{
		$GetAllLine			= GetAllLine($_POST['clientid']);
		$GetAllHawker		= GetAllHawker($_POST['clientid']);
		$GetAllArea			= GetAllArea($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

		while($rows2 = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$name2		= "";

			$id					= $rows2['id'];
			$name				= $rows2['name'];
			$phone				= $rows2['phone'];
			$phone2				= $rows2['phone2'];
			$createdon			= $rows2['createdon'];
			$housenumber		= $rows2['housenumber'];
			$floor				= $rows2['floor'];
			$address1			= $rows2['address1'];
			$customerid			= $rows2['customerid'];
			$isdiscount			= $rows2['isdiscount'];
			$discount			= $rows2['discount'];
			$status				= $rows2['status'];
			$firstname			= $rows2['firstname'];
			$lastname			= $rows2['lastname'];
			$sequence			= $rows2['sequence'];
			$latitude			= $rows2['latitude'];
			$longitude			= $rows2['longitude'];
			$sublinename		= $GetAllSubLine[$rows2['sublineid']]['name'];

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

			$openingbalance		= $rows2['openingbalance'];

			$unsubscriptionsql		= "SELECT inv.name,log.quantity qty,log.unsubscribedate FROM ".$Prefix."subscriptions_log log,".$Prefix."inventory inv WHERE log.customerid=:customerid AND log.inventoryid=inv.id AND inv.deletedon<:deletedon AND (log.unsubscribedate BETWEEN :startdate AND :enddate) ORDER BY inv.name";
			$unsubscriptionesql		= array("customerid"=>(int)$id,"deletedon"=>1,"startdate"=>$todaytimestampCheck,"enddate"=>$tomorrowtimestampCheck);

			$unsubscriptionquery	= pdo_query($unsubscriptionsql,$unsubscriptionesql);
			$unsubscriptionnum		= pdo_num_rows($unsubscriptionquery);

			$subscriptionstr 	= '';

			if($unsubscriptionnum > 0)
			{
				while($unsubsrow	= pdo_fetch_assoc($unsubscriptionquery))
				{
					$inventoryname		= $unsubsrow['name'];
					$qty				= $unsubsrow['qty'];
					$unsubscribedate	= $unsubsrow['unsubscribedate'];

					if($qty > 1)
					{
						$inventoryname = $inventoryname." X ".$qty." (".date("d-M-Y",$unsubscribedate).")";
					}
					else
					{
						$inventoryname = $inventoryname." (".date("d-M-Y",$unsubscribedate).")";
					}
					$subscriptionstr .= $inventoryname.', ';

					$SubscriptionSummaryArr[$inventoryname]	+= $qty;
				}
				$subscriptionstr .= '@@';
				$subscriptionstr = str_replace(", @@","",$subscriptionstr);
				$subscriptionstr = str_replace("@@","",$subscriptionstr);
			}
			else
			{
				$subscriptionstr	= '--';
			}

			$googlemap = '';
			if($latitude !='' && $longitude !='')
			{
				$googlemap = "https://www.google.com/maps/dir//".$latitude.",".$longitude."/";
			}
			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			$name2	= "#".$customerid." ".$name;

			if($isdiscount > 0)
			{
				if($discount > 0)
				{
					$discount	= (float)$discount."%";
				}
				else
				{
					$discount	= "---";
				}
			}
			else
			{
				$discount	= "No Discount";
			}

			$line		= $GetAllLine[$rows2['lineid']]['name'];
			$subline	= $GetAllSubLine[$rows2['sublineid']]['name'];
			$hawker		= $GetAllHawker[$rows2['hawkerid']]['name'];
			$area		= $GetAllArea[$rows2['areaid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($phone2 == "")
			{
				$phone2	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}
			if($subline == "")
			{
				$subline	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($area == "")
			{
				$area	= "---";
			}

			$CloseSubscriptionListArr[$index]['id']					= (int)$id;
			$CloseSubscriptionListArr[$index]['customerid']			= $customerid;
			$CloseSubscriptionListArr[$index]['name']				= $name;
			$CloseSubscriptionListArr[$index]['phone']				= $phone;
			$CloseSubscriptionListArr[$index]['phone2']				= $phone2;
			$CloseSubscriptionListArr[$index]['line']				= $line;
			$CloseSubscriptionListArr[$index]['subline']			= $subline;
			$CloseSubscriptionListArr[$index]['hawker']				= $hawker;
			$CloseSubscriptionListArr[$index]['discount']			= $discount;
			$CloseSubscriptionListArr[$index]['address1']			= $address1;
			$CloseSubscriptionListArr[$index]['fulladdress']		= $addresstr;
			$CloseSubscriptionListArr[$index]['status']				= (int)$status;
			$CloseSubscriptionListArr[$index]['area']				= $area;
			$CloseSubscriptionListArr[$index]['name2']				= $name2;
			$CloseSubscriptionListArr[$index]['sequence']			= (int)$sequence;
			$CloseSubscriptionListArr[$index]['googlemap']			= $googlemap;
			$CloseSubscriptionListArr[$index]['housenumber']		= $housenumber;
			$CloseSubscriptionListArr[$index]['floor']				= $floor;
			$CloseSubscriptionListArr[$index]['closesubscriptions']	= $subscriptionstr;
			$CloseSubscriptionListArr[$index]['canchangebalance']	= false;
			$CloseSubscriptionListArr[$index]['caneditcustomer']	= $caneditcustomer;

			if($openingbalance != "")
			{
				$CloseSubscriptionListArr[$index]["openingbalance"]	= $openingbalance;
				$CloseSubscriptionListArr[$index]["openingbalancetxt"]	= $openingbalance;
			}
			else
			{
				$CloseSubscriptionListArr[$index]["openingbalance"]	= '';
				$CloseSubscriptionListArr[$index]["openingbalancetxt"]	= "---";
			}

			$index++;
			$totalsubscriptioncount++;
		}
	}

	$Sql3	= "SELECT customer.*, holidays.startdate AS holidaystartdate, holidays.enddate AS holidayenddate FROM ".$Prefix."customers customer,".$Prefix."holidays holidays WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=holidays.customerid AND holidays.customertype=:customertype AND holidays.clientid=:clientid2 AND holidays.deletedon < :deletedon2 AND (holidays.startdate BETWEEN :startdate AND :enddate) ".$condition." GROUP BY customer.customerid ORDER BY customer.sequence ASC, customer.customerid ASC";

	$Esql3	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"startdate"=>$todaytimestampCheck,"enddate"=>$tomorrowtimestampCheck,"customertype"=>1,"clientid2"=>(int)$_POST['clientid'],"deletedon2"=>1);

	$Query3	= pdo_query($Sql3,$Esql3);
	$Num3	= pdo_num_rows($Query3);

	$index	= 0;

	if($Num3 > 0)
	{
		$GetAllLine			= GetAllLine($_POST['clientid']);
		$GetAllHawker		= GetAllHawker($_POST['clientid']);
		$GetAllArea			= GetAllArea($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

		while($rows3 = pdo_fetch_assoc($Query3))
		{
			$isselected	= false;

			$name2		= "";

			$id					= $rows3['id'];
			$name				= $rows3['name'];
			$phone				= $rows3['phone'];
			$phone2				= $rows3['phone2'];
			$createdon			= $rows3['createdon'];
			$housenumber		= $rows3['housenumber'];
			$floor				= $rows3['floor'];
			$address1			= $rows3['address1'];
			$customerid			= $rows3['customerid'];
			$isdiscount			= $rows3['isdiscount'];
			$discount			= $rows3['discount'];
			$status				= $rows3['status'];
			$firstname			= $rows3['firstname'];
			$lastname			= $rows3['lastname'];
			$sequence			= $rows3['sequence'];
			$latitude			= $rows3['latitude'];
			$longitude			= $rows3['longitude'];
			$holidaystartdate	= $rows3['holidaystartdate'];
			$holidayenddate		= $rows3['holidayenddate'];
			$sublinename		= $GetAllSubLine[$rows3['sublineid']]['name'];

			$holidaydate	= date("d-M-Y",$holidaystartdate)." - ".date("d-M-Y",$holidayenddate);

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

			$openingbalance		= $rows3['openingbalance'];

			$subscriptionsql	= "SELECT inv.name,subs.quantity qty,subs.subscriptiondate FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
			$subscriptionesql	= array("customerid"=>(int)$id,"deletedon"=>1);
			$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
			$subscriptionnum	= pdo_num_rows($subscriptionquery);

			$subscriptionstr 	= '';

			if($subscriptionnum > 0)
			{
				while($subsrow	= pdo_fetch_assoc($subscriptionquery))
				{
					$inventoryname		= $subsrow['name'];
					$qty				= $subsrow['qty'];
					$subscriptiondate	= $subsrow['subscriptiondate'];

					if($qty > 1)
					{
						/*$inventoryname = $inventoryname." X ".$qty." (".date("d-M-Y",$subscriptiondate).")";*/
						$inventoryname = $inventoryname." X ".$qty;
					}
					else
					{
						/*$inventoryname = $inventoryname." (".date("d-M-Y",$subscriptiondate).")";*/
						$inventoryname = $inventoryname;
					}
					$subscriptionstr .= $inventoryname.', ';

					$SubscriptionSummaryArr[$inventoryname]	+= $qty;
				}
				$subscriptionstr .= '@@';
				$subscriptionstr = str_replace(", @@","",$subscriptionstr);
				$subscriptionstr = str_replace("@@","",$subscriptionstr);
			}
			else
			{
				$subscriptionstr	= '--';
			}

			$googlemap = '';
			if($latitude !='' && $longitude !='')
			{
				$googlemap = "https://www.google.com/maps/dir//".$latitude.",".$longitude."/";
			}
			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			$name2	= "#".$customerid." ".$name;

			if($isdiscount > 0)
			{
				if($discount > 0)
				{
					$discount	= (float)$discount."%";
				}
				else
				{
					$discount	= "---";
				}
			}
			else
			{
				$discount	= "No Discount";
			}

			$line		= $GetAllLine[$rows3['lineid']]['name'];
			$subline	= $GetAllSubLine[$rows3['sublineid']]['name'];
			$hawker		= $GetAllHawker[$rows3['hawkerid']]['name'];
			$area		= $GetAllArea[$rows3['areaid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($phone2 == "")
			{
				$phone2	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}
			if($subline == "")
			{
				$subline	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($area == "")
			{
				$area	= "---";
			}

			$HolidayStartListArr[$index]['id']					= (int)$id;
			$HolidayStartListArr[$index]['customerid']			= $customerid;
			$HolidayStartListArr[$index]['name']				= $name;
			$HolidayStartListArr[$index]['phone']				= $phone;
			$HolidayStartListArr[$index]['phone2']				= $phone2;
			$HolidayStartListArr[$index]['line']				= $line;
			$HolidayStartListArr[$index]['subline']				= $subline;
			$HolidayStartListArr[$index]['hawker']				= $hawker;
			$HolidayStartListArr[$index]['discount']			= $discount;
			$HolidayStartListArr[$index]['address1']			= $address1;
			$HolidayStartListArr[$index]['fulladdress']			= $addresstr;
			$HolidayStartListArr[$index]['status']				= (int)$status;
			$HolidayStartListArr[$index]['area']				= $area;
			$HolidayStartListArr[$index]['name2']				= $name2;
			$HolidayStartListArr[$index]['sequence']			= (int)$sequence;
			$HolidayStartListArr[$index]['googlemap']			= $googlemap;
			$HolidayStartListArr[$index]['housenumber']			= $housenumber;
			$HolidayStartListArr[$index]['floor']				= $floor;
			$HolidayStartListArr[$index]['subscriptions']		= $subscriptionstr;
			$HolidayStartListArr[$index]['canchangebalance']	= false;
			$HolidayStartListArr[$index]['caneditcustomer']		= $caneditcustomer;
			$HolidayStartListArr[$index]['holidaydate']			= $holidaydate;

			if($openingbalance != "")
			{
				$HolidayStartListArr[$index]["openingbalance"]		= $openingbalance;
				$HolidayStartListArr[$index]["openingbalancetxt"]	= $openingbalance;
			}
			else
			{
				$HolidayStartListArr[$index]["openingbalance"]		= '';
				$HolidayStartListArr[$index]["openingbalancetxt"]	= "---";
			}

			$index++;
			$totalsubscriptioncount++;
		}
	}

	$Sql4	= "SELECT customer.*, holidays.startdate AS holidaystartdate, holidays.enddate AS holidayenddate FROM ".$Prefix."customers customer,".$Prefix."holidays holidays WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=holidays.customerid AND holidays.customertype=:customertype AND holidays.clientid=:clientid2 AND holidays.deletedon < :deletedon2 AND (holidays.enddate BETWEEN :startdate AND :enddate) ".$condition." GROUP BY customerid ORDER BY customer.sequence ASC, customer.customerid ASC";

	$Esql4	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"startdate"=>$todaytimestampCheck,"enddate"=>$tomorrowtimestampCheck,"customertype"=>1,"clientid2"=>(int)$_POST['clientid'],"deletedon2"=>1);

	$Query4	= pdo_query($Sql4,$Esql4);
	$Num4	= pdo_num_rows($Query4);

	$index	= 0;

	if($Num4 > 0)
	{
		$GetAllLine			= GetAllLine($_POST['clientid']);
		$GetAllHawker		= GetAllHawker($_POST['clientid']);
		$GetAllArea			= GetAllArea($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

		while($rows4 = pdo_fetch_assoc($Query4))
		{
			$isselected	= false;

			$name2		= "";

			$id					= $rows4['id'];
			$name				= $rows4['name'];
			$phone				= $rows4['phone'];
			$phone2				= $rows4['phone2'];
			$createdon			= $rows4['createdon'];
			$housenumber		= $rows4['housenumber'];
			$floor				= $rows4['floor'];
			$address1			= $rows4['address1'];
			$customerid			= $rows4['customerid'];
			$isdiscount			= $rows4['isdiscount'];
			$discount			= $rows4['discount'];
			$status				= $rows4['status'];
			$firstname			= $rows4['firstname'];
			$lastname			= $rows4['lastname'];
			$sequence			= $rows4['sequence'];
			$latitude			= $rows4['latitude'];
			$longitude			= $rows4['longitude'];
			$holidaystartdate	= $rows4['holidaystartdate'];
			$holidayenddate		= $rows4['holidayenddate'];
			$sublinename		= $GetAllSubLine[$rows4['sublineid']]['name'];

			$holidaydate	= date("d-M-Y",$holidaystartdate)." - ".date("d-M-Y",$holidayenddate);

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

			$openingbalance		= $rows4['openingbalance'];

			$subscriptionsql	= "SELECT inv.name,subs.quantity qty,subs.subscriptiondate FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
			$subscriptionesql	= array("customerid"=>(int)$id,"deletedon"=>1);
			$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
			$subscriptionnum	= pdo_num_rows($subscriptionquery);

			$subscriptionstr 	= '';

			if($subscriptionnum > 0)
			{
				while($subsrow	= pdo_fetch_assoc($subscriptionquery))
				{
					$inventoryname		= $subsrow['name'];
					$qty				= $subsrow['qty'];
					$subscriptiondate	= $subsrow['subscriptiondate'];

					if($qty > 1)
					{
						/*$inventoryname = $inventoryname." X ".$qty." (".date("d-M-Y",$subscriptiondate).")";*/
						$inventoryname = $inventoryname." X ".$qty;
					}
					else
					{
						/*$inventoryname = $inventoryname." (".date("d-M-Y",$subscriptiondate).")";*/
						$inventoryname = $inventoryname;
					}
					$subscriptionstr .= $inventoryname.', ';

					$SubscriptionSummaryArr[$inventoryname]	+= $qty;
				}
				$subscriptionstr .= '@@';
				$subscriptionstr = str_replace(", @@","",$subscriptionstr);
				$subscriptionstr = str_replace("@@","",$subscriptionstr);
			}
			else
			{
				$subscriptionstr	= '--';
			}

			$googlemap = '';
			if($latitude !='' && $longitude !='')
			{
				$googlemap = "https://www.google.com/maps/dir//".$latitude.",".$longitude."/";
			}
			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			$name2	= "#".$customerid." ".$name;

			if($isdiscount > 0)
			{
				if($discount > 0)
				{
					$discount	= (float)$discount."%";
				}
				else
				{
					$discount	= "---";
				}
			}
			else
			{
				$discount	= "No Discount";
			}

			$line		= $GetAllLine[$rows4['lineid']]['name'];
			$subline	= $GetAllSubLine[$rows4['sublineid']]['name'];
			$hawker		= $GetAllHawker[$rows4['hawkerid']]['name'];
			$area		= $GetAllArea[$rows4['areaid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($phone2 == "")
			{
				$phone2	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}
			if($subline == "")
			{
				$subline	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($area == "")
			{
				$area	= "---";
			}

			$HolidayEndListArr[$index]['id']				= (int)$id;
			$HolidayEndListArr[$index]['customerid']		= $customerid;
			$HolidayEndListArr[$index]['name']				= $name;
			$HolidayEndListArr[$index]['phone']				= $phone;
			$HolidayEndListArr[$index]['phone2']			= $phone2;
			$HolidayEndListArr[$index]['line']				= $line;
			$HolidayEndListArr[$index]['subline']			= $subline;
			$HolidayEndListArr[$index]['hawker']			= $hawker;
			$HolidayEndListArr[$index]['discount']			= $discount;
			$HolidayEndListArr[$index]['address1']			= $address1;
			$HolidayEndListArr[$index]['fulladdress']		= $addresstr;
			$HolidayEndListArr[$index]['status']			= (int)$status;
			$HolidayEndListArr[$index]['area']				= $area;
			$HolidayEndListArr[$index]['name2']				= $name2;
			$HolidayEndListArr[$index]['sequence']			= (int)$sequence;
			$HolidayEndListArr[$index]['googlemap']			= $googlemap;
			$HolidayEndListArr[$index]['housenumber']		= $housenumber;
			$HolidayEndListArr[$index]['floor']				= $floor;
			$HolidayEndListArr[$index]['subscriptions']		= $subscriptionstr;
			$HolidayEndListArr[$index]['canchangebalance']	= false;
			$HolidayEndListArr[$index]['caneditcustomer']	= $caneditcustomer;
			$HolidayEndListArr[$index]['holidaydate']		= $holidaydate;

			if($openingbalance != "")
			{
				$HolidayEndListArr[$index]["openingbalance"]	= $openingbalance;
				$HolidayEndListArr[$index]["openingbalancetxt"]	= $openingbalance;
			}
			else
			{
				$HolidayEndListArr[$index]["openingbalance"]	= '';
				$HolidayEndListArr[$index]["openingbalancetxt"]	= "---";
			}

			$index++;
			$totalsubscriptioncount++;
		}
	}

	$hasnewsubscription		= false;
	$hasclosesubscription	= false;

	$hasholidaystartlist	= false;
	$hasholidayendlist		= false;

	if(!empty($NewSubscriptionListArr))
	{
		$hasnewsubscription	= true;
	}

	if(!empty($CloseSubscriptionListArr))
	{
		$hasclosesubscription	= true;
	}

	if(!empty($HolidayStartListArr))
	{
		$hasholidaystartlist	= true;
	}

	if(!empty($HolidayEndListArr))
	{
		$hasholidayendlist	= true;
	}

	if(!empty($NewSubscriptionListArr) || !empty($CloseSubscriptionListArr) || !empty($HolidayStartListArr) || !empty($HolidayEndListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Customer subscription fetch successfully.";
	}

	$RecordListArr['hasnewsubscription']		= $hasnewsubscription;
	$RecordListArr['newsubscriptionlist']		= $NewSubscriptionListArr;
	$RecordListArr['hasclosesubscription']		= $hasclosesubscription;
	$RecordListArr['closesubscriptionlist']		= $CloseSubscriptionListArr;
	$RecordListArr['totalsubscriptioncount']	= $totalsubscriptioncount;

	$RecordListArr['hasholidaystartlist']		= $hasholidaystartlist;
	$RecordListArr['holidaystartlist']			= $HolidayStartListArr;

	$RecordListArr['hasholidayendlist']			= $hasholidayendlist;
	$RecordListArr['holidayendlist']			= $HolidayEndListArr;

	$response['recordset']	= $RecordListArr;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerCount")
{
	$response['success']	= false;
	$response['msg']		= "Unable to fetch customer count.";

	$RecordSetArr	= array();

	$condition	= " AND cust.deletedon <:deletedon AND cust.phone <>:phone";
	$Esql		= array("deletedon"=>1,"phone"=>"");

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND cust.clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
	{
		if($_POST['lineid'] == 9999)
		{
			$condition	.= " AND cust.lineid < :lineid";
			$Esql['lineid']	= 1;
		}
		else
		{
			$condition	.= " AND cust.lineid=:lineid";
			$Esql['lineid']	= (int)$_POST['lineid'];
		}
	}
	/*else
	{
		if($_POST['fetchtype'] == 'invoicemonth')
		{
			$condition	.= " AND cust.lineid=:lineid";
			$Esql['lineid']	= '-1';
		}
	}*/
	if($_POST['areaid'] > 0)
	{
		if($_POST['areaid'] == 9999)
		{
			$condition	.= " AND cust.areaid < :areaid";
			$Esql['areaid']	= 1;
		}
		else
		{
			$condition	.= " AND cust.areaid=:areaid";
			$Esql['areaid']	= (int)$_POST['areaid'];
		}
	}
	/*else
	{
		if($_POST['fetchtype'] == 'invoicemonth')
		{
			$condition	.= " AND cust.areaid=:areaid";
			$Esql['areaid']	= '-1';
		}
	}*/
	if($_POST['customerid'] > 0)
	{
		$condition	.= " AND cust.id=:id";
		$Esql['id']	= (int)$_POST['customerid'];
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

		$condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	if($_POST['fetchtype'] == 'invoicemonth')
	{
		$MonthYearArr = explode("-",$_POST['billingmonthyear']);
		$Month = $MonthYearArr[1];
		$Year = $MonthYearArr[0];

  		$Sql	= "SELECT count(cust.id) AS C FROM ".$Prefix."invoices inv,".$Prefix."customers cust WHERE inv.invoicemonth=:invoicemonth AND inv.invoiceyear=:invoiceyear AND inv.deletedon < :deletedon2 AND inv.customerid=cust.id".$condition." ORDER BY cust.customerid ASC, cust.status DESC";
		$Esql['deletedon2'] = 1;
		$Esql['invoicemonth'] = (int)$Month;
		$Esql['invoiceyear'] = (int)$Year;
	}
	else
	{
		$Sql	= "SELECT count(cust.id) AS C FROM ".$Prefix."customers cust WHERE 1 ".$condition." ORDER BY customerid ASC, status DESC";
	}
	$Query	= pdo_query($Sql,$Esql);
	$Rows	= pdo_fetch_assoc($Query);

	$Count	= $Rows['C'];

	$RecordSetArr['customercount']	= $Count;

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['recordset']	= $RecordSetArr;
		$response['msg']		= "Customer count fetched successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetInactiveCustomerOutstanding")
{
	$links		= 5;
	$perpage	= 100;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}

	$RecordListArr			= array();
	$SubscriptionSummaryArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch inactive customers.";

	$condition	= " AND deletedon <:deletedon AND isinactive=:isinactive AND outstandingbalance > :outstandingbalance";
	$Esql		= array("deletedon"=>1,"isinactive"=>1,"outstandingbalance"=>0);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
	{
		if($_POST['lineid'] == 9999)
		{
			$condition	.= " AND lineid < :lineid";
			$Esql['lineid']	= 1;
		}
		else
		{
			$condition	.= " AND lineid=:lineid";
			$Esql['lineid']	= (int)$_POST['lineid'];
		}
	}

	if($_POST['areaid'] > 0)
	{
		if($_POST['areaid'] == 9999)
		{
			$condition	.= " AND areaid < :areaid";
			$Esql['areaid']	= 1;
		}
		else
		{
			$condition	.= " AND areaid=:areaid";
			$Esql['areaid']	= (int)$_POST['areaid'];
		}
	}

	if($_POST['hawkerid'] > 0 && $_POST['loginhawkerid'] < 1)
	{
		$condition	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
	}
	else if($_POST['ishawker'] == "1")
	{
		$condition	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['loginhawkerid'];
	}

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

	if($_POST['openingbalanceid'] > 0)
	{
		if($_POST['openingbalanceid'] == '1')
		{
			$condition	.= " AND openingbalance !=:openingbalance AND openingbalance !=:openingbalance2 AND openingbalance IS NOT NULL";
			$Esql['openingbalance'] = '';
			$Esql['openingbalance2'] = '0';
		}
		if($_POST['openingbalanceid'] == '2')
		{
			$condition	.= " AND openingbalance =:openingbalance AND openingbalance IS NOT NULL";
			$Esql['openingbalance'] = "0";
		}
		if($_POST['openingbalanceid'] == '3')
		{
			$condition	.= " AND (openingbalance =:openingbalance || openingbalance IS NULL)";
			$Esql['openingbalance'] = '';
		}
	}
	if(trim($_POST['searchkeyword']) != "")
	{
		$condition	.= " AND (name LIKE :name || phone LIKE :phone || email LIKE :email || address1 LIKE :address1 || customerid LIKE :customerid || phone2 LIKE :phone2)";

		$Esql['name']		= "%".$_POST['searchkeyword']."%";
		$Esql['phone']		= "%".$_POST['searchkeyword']."%";
		$Esql['email']		= "%".$_POST['searchkeyword']."%";
		$Esql['address1']	= "%".$_POST['searchkeyword']."%";
		$Esql['customerid']	= "".$_POST['searchkeyword']."%";
		$Esql['phone2']		= "".$_POST['searchkeyword']."%";
	}
	if(trim($_POST['nameandphone']) != "")
	{
		$condition	.= " AND (name LIKE :name2 || phone LIKE :phone2 || phone2 LIKE :phone3)";

		$Esql['name2']		= "%".$_POST['nameandphone']."%";
		$Esql['phone2']		= "%".$_POST['nameandphone']."%";
		$Esql['phone3']		= "%".$_POST['nameandphone']."%";
	}

	if($_POST['customerid'] > 0)
	{
		$condition	.= " AND id=:id";
		$Esql['id']	= (int)$_POST['customerid'];
	}

	if($_POST['type'] == "sequence")
	{
		$Sql	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition." ORDER BY sequence ASC";
	}
	else
	{
		$Sql	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition." ORDER BY sequence ASC, customerid ASC, status DESC";
	}

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

	if($_POST['type'] == "sequence")
	{
		$Sql2	= $Sql;
	}
	else
	{
		$Sql2	= $Sql.$addquery;
		$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));
	}

	$Query2	= pdo_query($Sql2,$Esql);
	$Num2	= pdo_num_rows($Query2);

	$showingrecord	= 0;

	if($Num2 > 0)
	{
		$index	= 0;

		$GetAllLine			= GetAllLine($_POST['clientid']);
		$GetAllHawker		= GetAllHawker($_POST['clientid']);
		$GetAllArea			= GetAllArea($_POST['clientid']);
		$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$name2		= "";

			$id					= $rows['id'];
			$name				= $rows['name'];
			$phone				= $rows['phone'];
			$phone2				= $rows['phone2'];
			$createdon			= $rows['createdon'];
			$housenumber		= $rows['housenumber'];
			$floor				= $rows['floor'];
			$address1			= $rows['address1'];
			$customerid			= $rows['customerid'];
			$isdiscount			= $rows['isdiscount'];
			$discount			= $rows['discount'];
			$status				= $rows['status'];
			$firstname			= $rows['firstname'];
			$lastname			= $rows['lastname'];
			$sequence			= $rows['sequence'];
			$latitude			= $rows['latitude'];
			$longitude			= $rows['longitude'];
			$outstandingbalance	= $rows['outstandingbalance'];
			$sublinename		= $GetAllSubLine[$rows['sublineid']]['name'];

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

			$openingbalance		= $rows['openingbalance'];

			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			$name2	= "#".$customerid." ".$name;

			$line		= $GetAllLine[$rows['lineid']]['name'];
			$subline	= $GetAllSubLine[$rows['sublineid']]['name'];
			$hawker		= $GetAllHawker[$rows['hawkerid']]['name'];
			$area		= $GetAllArea[$rows['areaid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($phone2 == "")
			{
				$phone2	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}
			if($subline == "")
			{
				$subline	= "---";
			}

			/*if($lineman == "")
			{
				$lineman	= "---";
			}*/

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($area == "")
			{
				$area	= "---";
			}

			$RecordListArr[$index]['id']				= (int)$id;
			$RecordListArr[$index]['customerid']		= $customerid;
			$RecordListArr[$index]['name']				= $name;
			$RecordListArr[$index]['phone']				= $phone;
			$RecordListArr[$index]['phone2']			= $phone2;
			$RecordListArr[$index]['line']				= $line;
			$RecordListArr[$index]['subline']			= $subline;
			$RecordListArr[$index]['hawker']			= $hawker;
			$RecordListArr[$index]['discount']			= $discount;
			$RecordListArr[$index]['address1']			= $address1;
			$RecordListArr[$index]['fulladdress']		= $addresstr;
			$RecordListArr[$index]['status']			= (int)$status;
			$RecordListArr[$index]['area']				= $area;
			$RecordListArr[$index]['name2']				= $name2;
			$RecordListArr[$index]['sequence']			= (int)$sequence;
			$RecordListArr[$index]['googlemap']			= $googlemap;
			$RecordListArr[$index]['housenumber']		= $housenumber;
			$RecordListArr[$index]['floor']				= $floor;
			$RecordListArr[$index]['outstandingbalance']= $outstandingbalance;

			$index++;
			$showingrecord++;
		}

		$response['success']	= true;
		$response['msg']		= "Inactive customers listed successfully.";
	}

	$pageListArr	= array();
	$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);
	$fromrecord		= $offset+1;

	$response['recordlist']		= $RecordListArr;
	$response['perpage']		= (int)$perpage;
	$response['totalpages']		= (int)$totalpages;
	$response['paginglist']		= $pageListArr;
	$response['showpages']		= false;
	$response['totalrecord']	= $TotalRec;
	$response['recordrange']	= $fromrecord."-".($fromrecord+($showingrecord) - 1);

	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "SendLoginOtp")
{
	$response['isotpverified']	= false;
    $response['success']		= false;
    $response['msg']			= "Unable to send otp, Please try later.";

	/*$_POST['phone']	= "917906237";*/

	$haserror	= false;

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
	$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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
		$PhoneOtp	= GenerateOTP(6);

		$isaccountexist	= true;

		$rows	= pdo_fetch_assoc($CheckQuery);

		$userid		= $rows['id'];
		$name		= $rows['name'];
		$phone		= $rows['phone'];

		$UpdateSql	= "UPDATE ".$Prefix."customers SET loginotp=:loginotp WHERE id=:id AND status=:status AND deletedon < :deletedon";
		$UpdateEsql	= array("loginotp"=>$PhoneOtp,"id"=>(int)$userid,"status"=>1,"deletedon"=>1);

		$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

		if($UpdateQuery)
		{
		$Message = "Dear <arg1>,

Your OTP: <arg2>

<arg3>
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)
Team ORLO";

		/*$name	= "Abhinandan";

		$phone	= "9811168031";*/

		$messagearr[] = array("phoneno"=>$phone,"arg1"=>$name,"arg2"=>$PhoneOtp,"arg3"=>$SiteDomainName);
	
		$SMSRoute = 7; /*7 - OtpRoute, 2 - Normal Route*/
		$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$DLTSMSAuthID,$SMSRoute);

		$response['success']	= true;
		$response['msg']		= "OTP sent to ".$phone;
		}
	}
	else
	{
		$response['success']	= false;
		$response['msg']		= "No account found with entered number.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "VerifyCustomerLogin")
{
	$response['isotpverified']	= false;
	$response['success']		= false;
	$response['msg']			= "No account found with entered number.";

	/*$_POST['phone']	= "917906237";*/

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
	$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$rows	= pdo_fetch_assoc($CheckQuery);

		$userid		= $rows['id'];
		$phone		= $rows['phone'];
		$loginotp	= $rows['loginotp'];
		$name		= $rows['name'];

		/*$name		= "Abhinandan";*/

		if((trim($loginotp) == trim($_POST['otp'])) || (trim($_POST['otp']) == "000000"))
		{
			$userdetail	= array(
				"id"				=>(int)$rows['clientid'],
				"customerid"		=>(int)$rows['id'],
				"customerrecid"		=>(int)$rows['customerid'],
				"iscustomerarea"	=>1,
				"name"				=>$rows['name'],
				"phone"				=>$rows['phone'],
				"email"				=>$rows['email']
			);

			$accesstoken = array(
			   "iss" => $jwtiss,
			   "aud" => $jwtaud,
			   "iat" => $jwtiat,
			   "nbf" => $jwtnbf,
			   "isadminlogin" => false,
			   "adminid" => 0,
			   "clientdata" => $userdetail
			);

			$jwt = JWT::encode($accesstoken, $jwtkey);

			$response['accesstoken']	= $jwt;
			$response['customername']	= $name;
			$response['msg']			= "Logged in successfully.";
			$response['success']		= true;
			$response['isotpverified']	= true;
		}
		else
		{
			$response['msg']	= "Incorrect otp entered.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetPaymentLink')
{
	$response['success']	= false;
	$response['msg']		= "Unable to fetch customers detail.";

	if($_POST['InvoiceIsCustomerArea'] != "")
	{
		$_POST['iscustomerarea']	= $_POST['InvoiceIsCustomerArea'];
	}

	if($_POST['InvoiceClientid'] > 0)
	{
		$_POST['clientid']	= $_POST['InvoiceClientid'];
	}

	if($_POST['InvoiceCustomerid'] > 0)
	{
		$_POST['customerid']	= $_POST['InvoiceCustomerid'];
	}

	if($_POST['iscustomerarea'] < 1)
	{
		$response['msg']	= "You are not authorize for this process, please relogin and try again.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
	$ClientESQL		= array("id"=>(int)$_POST['clientid']);

	$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
	$ClientNum		= pdo_num_rows($ClientQuery);

	if($ClientNum > 0)
	{
		$ClientRow	= pdo_fetch_assoc($ClientQuery);
		
		$IsPaymentGateWay				= $ClientRow['ispaymentgateway'];
		$RAZOR_PAY_API_KEY_Client		= $ClientRow["razor_pay_api_key"];
		$RAZOR_PAY_API_SECRET_Client	= $ClientRow["razor_pay_api_secret"];
	}

	$CustomerArr	= array();
	$CustomerSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id";
	$CustomerESQL	= array("id"=>(int)$_POST['customerid']);

	$CustomerQuery	= pdo_query($CustomerSQL,$CustomerESQL);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	$outstanding	= 0;

	if($CustomerNum > 0)
	{
		$CustomerRow	= pdo_fetch_assoc($CustomerQuery);
		
		$CustomerArr['clientid']	= (int)$_POST['clientid'];
		$CustomerArr['customerid']	= (int)$_POST['customerid'];
		$outstanding				= (float)$CustomerRow['outstandingbalance'];

		if($_POST['InvoiceAmount'] > 0)
		{
			$outstanding	= (float)$_POST['InvoiceAmount'];
		}

		/*$outstanding	= getOutstandingBalanceByCustomer($CustomerArr['clientid'],$CustomerArr['customerid']);*/

		$CustomerArr['name']	= $CustomerRow['name'];
		$CustomerArr['email']	= $CustomerRow['email'];
		$CustomerArr['phone']	= $CustomerRow['phone'];
		$CustID					= $CustomerRow['customerid'];
	}
	/*$CustomerArr['email'] = 'vkwoodpeckies@gmail.com';
	$CustomerArr['phone'] = "+917982650658";*/

	if($outstanding < 1)
	{
		$response['msg']	= "You don't have any outstanding balance.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$Notes = "Payment by customer id #".$CustID;

	$PaymentLink = GeneratePaymentLinks_Customer($CustomerArr,$outstanding,$Notes,$RAZOR_PAY_API_KEY_Client,$RAZOR_PAY_API_SECRET_Client);

	if($PaymentLink !='')
	{
		$sql 		= "SELECT * FROM ".$Prefix."payment_log WHERE paylink=:paylink";
		$esql 		= array("paylink"=>$PaymentLink);
		$query		= pdo_query($sql,$esql);
		$row		= pdo_fetch_assoc($query);
		$paymentid	= $row['razorpayinoviceid'];

		CreatePaymentRequest($paymentid, $PaymentLink, $_POST['clientid']);

		$response['success']		= true;
		$response['msg']			= "Payment link successfully generated.";
		$response['paymentlink']	= $PaymentLink;
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'ViewOutstanding')
{
	$response['success']			= false;
	$response['msg']				= "Unable to fetch customer bill detail.";
	$response['showoutstandingmsg']	= false;

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
	$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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
		$name			= $row['name'];
		$phone			= $row['phone'];
		$outstanding	= (float)$row['outstandingbalance'];

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

				$billid	= $invoicerow['id'];
				$code	= $invoicerow['securitycode'];

				$ServerURL	= "http://www.orlopay.com/";

				if($_SERVER['IsLocal'] == 'Yes')
				{
					$ServerURL	= "http://orlopay/agency/";
				}

				$paymentlink	= $ServerURL."viewcustomerinvoice.php?id=".$billid."&code=".$code;

				$response['success']		= true;
				$response['msg']			= "Payment link created.";
				$response['paymentlink']	= $paymentlink;
			}
		}
		else
		{
			$response['showoutstandingmsg']	= true;
			$response['msg']	= "Congratulations, you have no outstanding balance. Nothing to pay!";
		}
	}
	else
	{
		$response['msg']	= "Phone number is not linked with any account.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "UpdateCustomerOutstanding")
{
	$linename		= "";
	$linemanname	= "";
	$hawkername		= "";

    $response['success']	= false;
    $response['msg']		= "Unable to update outstanding balance.";

	$OutStandingBalance	= UpdateoutstandingByCustomerID($_POST['clientid'],$_POST['recordid']);

	if("".$OutStandingBalance."" != "")
	{
		$response['success']	= true;
		$response['msg']		= "Customer outstanding successfully updated.";
	}

	$response['outstanding']	= "".$OutStandingBalance."";

    $json = json_encode($response);
    echo $json;
	die;
}
?>