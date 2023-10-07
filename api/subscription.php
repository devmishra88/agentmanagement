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

if($_POST['Mode'] == "UpdateSubscription")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to update Subscription.";

	$isupdated	= false;
	$hasinvoice	= false;

	$customerid	= $_POST['customerid'];

	/*$CheckInvoiceSql	= "SELECT * FROM ".$Prefix."invoices WHERE customerid=:customerid AND clientid=:clientid AND ispaid<>:ispaid";
	$CheckInvoiceEsql	= array("customerid"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid'],"ispaid"=>1);

	$CheckInvoiceQuery	= pdo_query($CheckInvoiceSql,$CheckInvoiceEsql);
	$CheckInvoiceNum	= pdo_num_rows($CheckInvoiceQuery);

	if($CheckInvoiceNum > 0)
	{
		$hasinvoice	= true;
	}*/

	$hasinvoice = false;

	if(!$haserror)
	{
		$isupdated	= true;

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
						$inventoryname		= $inventoryrows['name'];
						$isassigned			= $inventoryrows['isassigned'];
						$quantity			= $inventoryrows['quantity'];
						$frequency			= $inventoryrows['frequency'];
						$subscriptiondate	= strtotime($inventoryrows['subscriptiondate']);
						$unsubscribedate	= strtotime($inventoryrows['unsubscribedate']);

						if((int)$quantity < 1)
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
							$daysStr	= "";
							$dayNameStr	= "";
						}

						if($isassigned == "true" || $isassigned == 1)
						{
							$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid";
							$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

							$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
							$CheckNum	= pdo_num_rows($CheckQuery);

							if($CheckNum < 1)
							{
								$AssignSql	= "INSERT INTO ".$Prefix."subscriptions SET 
								customerid			=:customerid,
								inventoryid			=:inventoryid,
								frequency			=:frequency,
								subscriptiondate	=:subscriptiondate,
								quantity			=:quantity,
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
									"createdon"			=>time()
								);

								$AssignQuery	= pdo_query($AssignSql,$AssignEsql);

								if($AssignQuery)
								{
									$isupdated	= true;
									CreateSubscriptionLog($customerid,$inventoryid,1,$frequency,$subscriptiondate,$daysStr,$dayNameStr,0,$quantity);
								}
							}
							else
							{
								$CheckRows	= pdo_fetch_assoc($CheckQuery);
								$CheckID	= $CheckRows['id'];							

								$AssignSql	= "UPDATE ".$Prefix."subscriptions SET 
								customerid			=:customerid,
								inventoryid			=:inventoryid,
								frequency			=:frequency,
								quantity			=:quantity,
								subscriptiondate	=:subscriptiondate,
								days				=:days,
								daysname			=:daysname
								WHERE
								id					=:id";

								$AssignEsql	= array(
									"customerid"		=>(int)$customerid,
									"inventoryid"		=>(int)$inventoryid,
									"frequency"			=>(int)$frequency,
									"quantity"			=>(int)$quantity,
									"subscriptiondate"	=>$subscriptiondate,
									"days"				=>$daysStr,
									"daysname"			=>$dayNameStr,
									"id"				=>(int)$CheckID
								);

								$AssignQuery	= pdo_query($AssignSql,$AssignEsql);

								if($AssignQuery)
								{
									/*if(($subscriptiondate != $CheckRows['subscriptiondate']) || ($daysStr != $CheckRows['days']) || ($dayNameStr != $CheckRows['daysname']) && ($inventoryid == $CheckRows['inventoryid']))
									{*/
										$SQL	= "UPDATE ".$Prefix."subscriptions_log SET
											subscriptiondate	=:subscriptiondate,
											quantity			=:quantity
											WHERE
											customerid			=:customerid AND	 	
											inventoryid			=:inventoryid AND
											status				=:status AND
											unsubscribedate		<:unsubscribedate";
										
										$ESQL = array(
													"customerid" 		=>(int)$customerid,
													"inventoryid" 		=>(int)$inventoryid,
													"quantity" 			=>(int)$quantity,
													"subscriptiondate"	=>$subscriptiondate,
													"status" 			=>1,
													"unsubscribedate"	=>1,
												);	

										$UpdateQuery = pdo_query($SQL,$ESQL);

										//CreateSubscriptionLog($customerid,$inventoryid,1,$frequency,$subscriptiondate,$daysStr,$dayNameStr,0);
									/*}*/
								}
							}
						}
						else
						{
							if($hasinvoice == false)
							{
								if($subscriptiondate > $unsubscribedate)
								{
									$ErrorMessage .="Close date must be greater than subscription date for ".$inventoryname."\n";
								}
								else
								{
									$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid ORDER BY id DESC LIMIT 1";
									$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

									$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
									$CheckNum	= pdo_num_rows($CheckQuery);

									/*if($inventoryid == 18)
									{
										echo $CheckNum;
									}*/

									if($CheckNum > 0)
									{
										$CheckRow = pdo_fetch_assoc($CheckQuery);

										$frequency			= $CheckRow['frequency'];
										$subscriptiondate	= $CheckRow['subscriptiondate'];
										$daysStr			= $CheckRow['days'];
										$dayNameStr			= $CheckRow['daysname'];

										$DELSQL = "DELETE FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid";
										$DELESQL = array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);
										$DELQuery = pdo_query($DELSQL,$DELESQL);

										if($DELQuery)
										{
											$isupdated	= true;
											CreateSubscriptionLog($customerid,$inventoryid,0,$frequency,$subscriptiondate,$daysStr,$dayNameStr,$unsubscribedate,$quantity);
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	if($isupdated)
	{
		$subscriptionsql	= "SELECT inv.name,subs.quantity qty FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
		$subscriptionesql	= array("customerid"=>(int)$customerid,"deletedon"=>1);
		$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
		$subscriptionnum	= pdo_num_rows($subscriptionquery);

		$isinactive	= 1;

		if($subscriptionnum > 0)
		{
			$isinactive	= 0;
		}
		else
		{
			$isinactive	= 1;
		}

		$UpdateSql		= "UPDATE ".$Prefix."customers SET isinactive=:isinactive WHERE id=:id";
		$UpdateEsql		= array("isinactive"=>(int)$isinactive,"id"=>(int)$customerid);

		$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

		$response['success']	= true;
		if($ErrorMessage !='')
		{
			$response['success']	= false;
			$response['msg']		= $ErrorMessage;
		}
		else
		{
			$response['msg']		= "Subscription successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerInventoryLog')
{
    $response['success']	= false;
    $response['msg']		= "No Log available.";

	$CustomerIDArr		= array();
	$CustomerNameArr	= array();

	$Condition	= "";
	$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['customerid'],"deletedon"=>1);

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

		$Condition		.= " AND lineid IN(".$lineids.")";
	}

	$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon ".$Condition." ORDER BY sequence ASC, customerid ASC";

	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	$totalcustomer	= $CustNum;

	if($CustNum > 0)
	{
		while($custrows = pdo_fetch_assoc($CustQuery))
		{
			$id				= $custrows['id'];
			$customerid		= $custrows['customerid'];
			$name			= $custrows['name'];

			$name2	= "#".$customerid." ".$name;

			$CustomerNameArr[$id]	= $name2;
			$CustomerIDArr[]		= $id;
		}
	}

	$CustomerIDStr	= @implode(",",@array_filter(@array_unique($CustomerIDArr)));

	if(trim($CustomerIDStr) == "")
	{
		$CustomerIDStr	= "-1";
	}

	$SQL	= "SELECT log.*, inv.name as inventoryname FROM ".$Prefix."subscriptions_log log, ".$Prefix."inventory inv WHERE log.customerid IN (".$CustomerIDStr.") AND inv.id=log.inventoryid ORDER BY inv.name ASC";
	$ESQL	= array();
	
	$Query  = pdo_query($SQL,$ESQL);
	$Num    = pdo_num_rows($Query);
	$Arr	=  array();
	$Index	= 0;

	if($Num > 0)
	{
		while($Row = pdo_fetch_assoc($Query))
		{
			$logid 				= $Row['id'];
			$inventoryid		= $Row['inventoryid'];
			$InventoryName 		= $Row['inventoryname'];
			$CreatedOn			= $Row['createdon'];
			$CreatedOnText		= date("d-M-Y h:i A",$CreatedOn);
			$subscriptiondate	= $Row['subscriptiondate'];
			$unsubscribedate	= $Row['unsubscribedate'];
			$Status				= $Row['status'];
			$quantity			= $Row['quantity'];

			$isclosed			= false;

			//$StatusText		= "Subscribe";
			$unsubscribedatestr = '';
			if($subscriptiondate > 0)
			{
				$activitydate	= date("d-M-Y",$subscriptiondate);
			}
			else
			{
				$activitydate	= date("d-M-Y",$CreatedOn);
			}	
			if($unsubscribedate > 0)
			{
				$unsubscribedatestr	= date("d-M-Y",$unsubscribedate);
				$isclosed	= true;
			}
			else
			{
				$unsubscribedatestr = '-'; 
			}
			/*if($Status < 1)
			{
				$StatusText = "Unsubscribe";
				
				if($unsubscribedate > 0)
				{
					$activitydate	= date("d-M-Y",$unsubscribedate);
				}
				else
				{
					$activitydate	= date("d-M-Y",$CreatedOn);
				}
			}*/

			$LogText 						= $InventoryName.' '.$StatusText.' on '.$CreatedOnText.".";
			$Arr[$Index]['logdate']			= $CreatedOnText;
			$Arr[$Index]['name'] 			= $InventoryName; 
			$Arr[$Index]['statustext'] 		= $StatusText; 
			$Arr[$Index]['status'] 			= (int)$Status;
			$Arr[$Index]['inventoryid']		= $inventoryid;
			$Arr[$Index]['activitydate']	= $activitydate;
			$Arr[$Index]['unsubscribedate']	= $unsubscribedatestr;
			$Arr[$Index]['logid']			= (int)$logid;
			$Arr[$Index]['quantity']		= (int)$quantity;
			$Arr[$Index]['isclosed']		= $isclosed;

			$Index++;
		}

		$response['success']		= true;
		$response['recordlist']		= $Arr;
		$response['customername']	= $name2;
	}
	else
	{
		if($_POST['iscustomerarea'] > 0)
		{
			$response['msg']	= "No subscription detail found!";
		}
	}

	$json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteSubscriptionLog")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete log, Please try later.";

	$DelSql		= "DELETE FROM ".$Prefix."subscriptions_log
	WHERE 
	id			=:id";

	$DelEsql	= array(
		'id'		=>(int)$_POST['recordid']
	);

	$DelQuery	= pdo_query($DelSql,$DelEsql);

	if($DelQuery)
	{
		$CheckSQL = "SELECT COUNT(*) AS C from ".$Prefix."subscriptions_log WHERE customerid=:customerid AND inventoryid=:inventoryid";
		$CheckESQL = array("customerid"=>(int)$_POST['customerid'],"inventoryid"=>(int)$_POST['inventoryid']);
		$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
		$CheckRow	= pdo_fetch_assoc($CheckQuery);
		if($CheckRow['C'] < 1)
		{
			$DelSql	= "DELETE FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid";
			$DelEsql = array("customerid"=>(int)$_POST['customerid'],"inventoryid"=>(int)$_POST['inventoryid']);
			pdo_query($DelSql,$DelEsql);
		}

		$subscriptionsql	= "SELECT inv.name,subs.quantity qty FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
		$subscriptionesql	= array("customerid"=>(int)$_POST['customerid'],"deletedon"=>1);
		$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
		$subscriptionnum	= pdo_num_rows($subscriptionquery);

		$isinactive		= 1;

		if($subscriptionnum > 0)
		{
			$isinactive		= 0;
		}
		else
		{
			$isinactive		= 1;
		}

		$UpdateSql	= "UPDATE ".$Prefix."customers SET isinactive=:isinactive WHERE id=:id";
		$UpdateEsql	= array("isinactive"=>(int)$isinactive,"id"=>(int)$_POST['customerid']);

		$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

		$Response['success']	= true;
		$Response['msg']		= "Subscription log deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "CloseSubscription")
{
    $response['success']	= false;
    $response['msg']		= "Unable to update Subscription.";

	$customerid		= $_POST['customerid'];
	$inventoryid	= $_POST['inventoryid'];

	$unsubscribedate	= strtotime(date('d-m-Y'));

	$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid ORDER BY id DESC LIMIT 1";
	$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$CheckRow = pdo_fetch_assoc($CheckQuery);

		$frequency			= $CheckRow['frequency'];
		$subscriptiondate	= $CheckRow['subscriptiondate'];
		$daysStr			= $CheckRow['days'];
		$dayNameStr			= $CheckRow['daysname'];

		$DELSQL = "DELETE FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid";
		$DELESQL = array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);
		$DELQuery = pdo_query($DELSQL,$DELESQL);

		if($DELQuery)
		{
			$isupdated	= true;
			CreateSubscriptionLog($customerid,$inventoryid,0,$frequency,$subscriptiondate,$daysStr,$dayNameStr,$unsubscribedate,$quantity);
		}

		if($isupdated)
		{
			$subscriptionsql	= "SELECT inv.name,subs.quantity qty FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
			$subscriptionesql	= array("customerid"=>(int)$customerid,"deletedon"=>1);
			
			$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
			$subscriptionnum	= pdo_num_rows($subscriptionquery);

			$isinactive	= 1;

			if($subscriptionnum > 0)
			{
				$isinactive	= 0;
			}
			else
			{
				$isinactive	= 1;
			}

			$UpdateSql		= "UPDATE ".$Prefix."customers SET isinactive=:isinactive WHERE id=:id";
			$UpdateEsql		= array("isinactive"=>(int)$isinactive,"id"=>(int)$customerid);

			$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

			$response['success']	= true;

			if($ErrorMessage !='')
			{
				$response['success']	= false;
				$response['msg']		= $ErrorMessage;
			}
			else
			{
				$response['msg']		= "Subscription successfully updated.";
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AddSubscription")
{
    $response['success']	= false;
    $response['msg']		= "Unable to add subscription.";

	$isupdated	= false;

	$customerid			= (int)$_POST['customerid'];
	$inventoryid		= (int)$_POST['inventoryid'];
	$isassigned			= 1;
	$quantity			= 1;
	$frequency			= 1;
	$subscriptiondate	= strtotime($_POST['subscriptiondate']);

	$checkdate		= strtotime(date('d-m-Y'))+86400;

	if($subscriptiondate < $checkdate)
	{
		$response['msg']	= "Please select future date to add subscription.";

		$json = json_encode($response);
		echo $json;
		die;
	}

	$daysStr	= "::1::2::3::4::5::6::7::";
	$dayNameStr	= "::Mon::Tue::Wed::Thu::Fri::Sat::Sun::";

	if($frequency != "1")
	{
		$daysStr	= "";
		$dayNameStr	= "";
	}

	if($isassigned > 0)
	{
		$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid";
		$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

		$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum < 1)
		{
			$AssignSql	= "INSERT INTO ".$Prefix."subscriptions SET 
			customerid			=:customerid,
			inventoryid			=:inventoryid,
			frequency			=:frequency,
			subscriptiondate	=:subscriptiondate,
			quantity			=:quantity,
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
				"createdon"			=>time()
			);

			$AssignQuery	= pdo_query($AssignSql,$AssignEsql);

			if($AssignQuery)
			{
				$isupdated	= true;
				CreateSubscriptionLog($customerid,$inventoryid,1,$frequency,$subscriptiondate,$daysStr,$dayNameStr,0,$quantity);
			}
		}
		else
		{
			$CheckRows	= pdo_fetch_assoc($CheckQuery);
			$CheckID	= $CheckRows['id'];							

			$AssignSql	= "UPDATE ".$Prefix."subscriptions SET 
			customerid			=:customerid,
			inventoryid			=:inventoryid,
			frequency			=:frequency,
			quantity			=:quantity,
			subscriptiondate	=:subscriptiondate,
			days				=:days,
			daysname			=:daysname
			WHERE
			id					=:id";

			$AssignEsql	= array(
				"customerid"		=>(int)$customerid,
				"inventoryid"		=>(int)$inventoryid,
				"frequency"			=>(int)$frequency,
				"quantity"			=>(int)$quantity,
				"subscriptiondate"	=>$subscriptiondate,
				"days"				=>$daysStr,
				"daysname"			=>$dayNameStr,
				"id"				=>(int)$CheckID
			);

			$AssignQuery	= pdo_query($AssignSql,$AssignEsql);

			if($AssignQuery)
			{
				$isupdated	= true;

				$SQL	= "UPDATE ".$Prefix."subscriptions_log SET
					subscriptiondate	=:subscriptiondate,
					quantity			=:quantity
					WHERE
					customerid			=:customerid AND	 	
					inventoryid			=:inventoryid AND
					status				=:status AND
					unsubscribedate		<:unsubscribedate";
				
				$ESQL = array(
							"customerid" 		=>(int)$customerid,
							"inventoryid" 		=>(int)$inventoryid,
							"quantity" 			=>(int)$quantity,
							"subscriptiondate"	=>$subscriptiondate,
							"status" 			=>1,
							"unsubscribedate"	=>1,
						);	

				$UpdateQuery = pdo_query($SQL,$ESQL);
			}
		}
	}

	if(!$isupdated)
	{
		$response['success']	= false;
		$response['msg']		= $ErrorMessage;
	}
	else
	{
		$response['success']	= true;
		$response['msg']		= "Subscription successfully added.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
?>