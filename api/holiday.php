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

if($_POST['Mode'] == "AddHoliday")
{
	$addedby	= "admin";
	if($_POST['iscustomerarea'] > 0)
	{
		$_POST['CustomerID']	= $_POST['customerid'];
		$addedby				= "user";
	}

	if($_POST['CustomerType'] < 1)
	{
		$_POST['CustomerID'] =0;
	}
	else
	{
		$_POST['InventoryType'] = 0;
	}
	if($_POST['InventoryType'] < 1)
	{
		$_POST['InventoryID'] =0;
	}

	$ErrorMessage = '';

	if($_POST['CustomerType'] > 0)
	{
		if($_POST['CustomerID'] < 1)
		{
			$ErrorMessage = "Please select a customer to add holiday.<br/>";
		}
	}
	if($_POST['InventoryType'] > 0)
	{
		if($_POST['InventoryID'] < 1)
		{
			$ErrorMessage .= "Please select an inventory to add holiday.<br/>";
		}
	}

	$StartDate	= strtotime($_POST['StartDate']);
	$EndDate	= strtotime($_POST['EndDate']) + 86399;

	if(($StartDate > $EndDate) && $ErrorMessage == "")
	{
		$ErrorMessage = "Start date can't be greater then End Date.<br/>";
		$Response['toastmsg']	= "Start date can't be greater then End Date.";
	}

	if(($_POST['iscustomerarea'] > 0) && ($StartDate + (86400*7)) > strtotime($_POST['EndDate']))
	{
		$ErrorMessage = "Start and end date should have seven days gap";
		$Response['toastmsg']	= $ErrorMessage;
	}

	if($ErrorMessage == "")
	{
		$StartDate	= strtotime($_POST['StartDate']);
		$EndDate	= strtotime($_POST['EndDate']) + 86399;

		$Response['success']	= false;
		$Response['msg']		= "Oops something went wrong. Please try again.";
		$Response['toastmsg']	= "Oops something went wrong. Please try again.";

		$CheckSQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND customertype=:customertype AND customerid=:customerid AND inventorytype=:inventorytype AND inventoryid=:inventoryid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon";
		$CheckESQL		= array("clientid"=>(int)$_POST['clientid'],"customertype"=>(int)$_POST['CustomerType'],"customerid"=>$_POST["CustomerID"],"inventorytype"=>(int)$_POST['InventoryType'],'inventoryid'=>(int)$_POST['InventoryID'],"date1"=>(int)$StartDate,"date2"=>(int)$EndDate,"date3"=>(int)$StartDate,"date4"=>(int)$EndDate,"deletedon"=>1);

		$CheckQuery		= pdo_query($CheckSQL,$CheckESQL);
		$CheckNum		= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$Response['success']	= false;
			$Response['msg']		= "Holiday period already exists for the selected criteria.";
			$Response['toastmsg']	= "Holiday period already exists for the selected criteria.";
		}
		else
		{
			$Sql	= "INSERT INTO ".$Prefix."holidays SET 
			clientid		=:clientid,
			customertype	=:customertype,
			customerid		=:customerid,
			inventorytype	=:inventorytype,
			inventoryid		=:inventoryid,
			startdate		=:startdate,
			enddate			=:enddate,
			reason			=:reason,
			createdon		=:createdon,
			addedby			=:addedby";

			$Esql	= array(
				"clientid"			=>(int)$_POST['clientid'],
				"customertype"		=>$_POST['CustomerType'],
				"customerid"		=>$_POST['CustomerID'],
				"inventorytype"		=>$_POST['InventoryType'],
				"inventoryid"		=>(int)$_POST['InventoryID'],
				"startdate"			=>(int)$StartDate,
				"enddate"			=>(int)$EndDate,
				"reason"			=>$_POST['Reason'],
				"createdon"			=>time(),
				"addedby"			=>$addedby
			);

			$Query	= pdo_query($Sql,$Esql);

			if($Query)
			{
				$holidayid	= pdo_insert_id();

				if($_POST['CustomerID'] > 0 && $_POST['CustomerType'] == 1)
				{
					$subscriptionsql	= "SELECT inv.name,subs.quantity qty,subs.inventoryid FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
					$subscriptionesql	= array("customerid"=>(int)$_POST['CustomerID'],"deletedon"=>1);
					
					$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
					$subscriptionnum	= pdo_num_rows($subscriptionquery);

					if($subscriptionnum > 0)
					{
						while($subsrow	= pdo_fetch_assoc($subscriptionquery))
						{
							$inventoryid	 = $subsrow['inventoryid'];
							$inventoryname	 = $subsrow['name'];
							$qty			 = $subsrow['qty'];

							$AddLinkerSql	= "INSERT INTO ".$Prefix."holidays_stock_linker SET 
							clientid	=:clientid,
							holidayid	=:holidayid,
							customerid	=:customerid,
							stockid		=:stockid,
							stockname	=:stockname,
							qty			=:qty,
							startdate	=:startdate,
							enddate		=:enddate,
							createdon	=:createdon";

							$AddLinkerEsql	= array(
								"clientid"		=>(int)$_POST['clientid'],
								"holidayid"		=>(int)$holidayid,
								"customerid"	=>(int)$_POST['CustomerID'],
								"stockid"		=>(int)$inventoryid,
								"stockname"		=>$inventoryname,
								"qty"			=>(int)$qty,
								"startdate"		=>$StartDate,
								"enddate"		=>$EndDate,
								"createdon"		=>time()
							);

							$AddLinkerQuery	= pdo_query($AddLinkerSql,$AddLinkerEsql);
						}
					}
				}

				$Response['success']	= true;
				$Response['msg']		= "Holiday period added successfully.";
				$Response['toastmsg']	= "Holiday period added successfully.";
			}
			else
			{
				$Response['success']	= false;
				$Response['msg']		= "Unable to add holiday period.";
				$Response['toastmsg']	= "Unable to add holiday period.";
			}
		}
	}
	else
	{
		$Response['success']	= false;
		$Response['msg']		= $ErrorMessage;
	}
	$json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditHoliday")
{
	/*$_POST['ClientID'] = "1";
	$_POST['CustomerType'] = "0"; // 0 - All Customers, 1 - Specific
	$_POST['CustomerID'] = "1"; // specific customer
	$_POST['InventoryType'] = "0"; // 0 - All Inventory, 1 - Specific
	$_POST['InventoryID'] = "1"; // specific inventory
	
	$_POST['StartDate']	 = "04/1/2020";
	$_POST['EndDate']	 = "04/18/2020";
	$_POST['Reason']	 = "Due to standard reasons.";
	$_POST['id']		 = 1; // Record ID */

	if($_POST['CustomerType'] < 1)
	{
		$_POST['CustomerID'] =0;
	}
	else
	{
		$_POST['InventoryType'] = 0;
	}
	if($_POST['InventoryType'] < 1)
	{
		$_POST['InventoryID'] =0;
	}

	$ErrorMessage = '';

	if($_POST['CustomerType'] > 0)
	{
		if($_POST['CustomerID'] < 1)
		{
			$ErrorMessage = "Please select a customer to update holiday.<br/>";
		}
	}
	if($_POST['InventoryType'] > 0)
	{
		if($_POST['InventoryID'] < 1)
		{
			$ErrorMessage .= "Please select an inventory to update holiday.<br/>";
		}
	}

	$StartDate	= strtotime($_POST['StartDate']);
	$EndDate	= strtotime($_POST['EndDate']) + 86399;

	if(($StartDate > $EndDate) && $ErrorMessage == "")
	{
		$ErrorMessage = "Start date can't be greater then End Date.<br/>";
		$Response['toastmsg']	= "Start date can't be greater then End Date.";
	}

	if($ErrorMessage == "")
	{
		$StartDate	= strtotime($_POST['StartDate']);
		$EndDate	= strtotime($_POST['EndDate']) + 86399;

		$Response['success']	= false;
		$Response['msg']		= "Oops something went wrong. Please try again.";
		$Response['toastmsg']	= "Oops something went wrong. Please try again.";

		$CheckSQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND customertype=:customertype AND customerid=:customerid AND inventorytype=:inventorytype AND inventoryid=:inventoryid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND id <>:id";
		$CheckESQL		= array("clientid"=>(int)$_POST['ClientID'],"customertype"=>(int)$_POST['CustomerType'],"customerid"=>$_POST["CustomerID"],"inventorytype"=>(int)$_POST['InventoryType'],'inventoryid'=>(int)$_POST['InventoryID'],"date1"=>(int)$StartDate,"date2"=>(int)$EndDate,"date3"=>(int)$StartDate,"date4"=>(int)$EndDate,"deletedon"=>1,"id"=>(int)$_POST['id']);

		$CheckQuery		=	pdo_query($CheckSQL,$CheckESQL);
		$CheckNum		=   pdo_num_rows($CheckQuery);
		if($CheckNum > 0)
		{
			$Response['success']	= false;
			$Response['msg']		= "Holiday period already exists for the selected criteria.";
			$Response['toastmsg']	= "Holiday period already exists for the selected criteria.";
		}
		else
		{
			$Sql	= "UPDATE ".$Prefix."holidays SET 
			clientid		=:clientid,
			customertype	=:customertype,
			customerid		=:customerid,
			inventorytype	=:inventorytype,
			inventoryid		=:inventoryid,
			startdate		=:startdate,
			enddate			=:enddate,
			reason			=:reason
			WHERE	
			id				=:id
			";

			$Esql	= array(
				"clientid"			=>(int)$_POST['ClientID'],
				"customertype"		=>$_POST['CustomerType'],
				"customerid"		=>$_POST['CustomerID'],
				"inventorytype"		=>$_POST['InventoryType'],
				"inventoryid"		=>(int)$_POST['InventoryID'],
				"startdate"			=>(int)$StartDate,
				"enddate"			=>(int)$EndDate,
				"reason"			=>$_POST['Reason'],
				"id"				=>(int)$_POST['id']
			);

			$Query	= pdo_query($Sql,$Esql);
			if($Query)
			{
				$holidayid	= $_POST['id'];

				$DelLinkerSql	= "DELETE FROM ".$Prefix."holidays_stock_linker WHERE clientid=:clientid AND holidayid=:holidayid AND customerid=:customerid";
				$DelLinkerEsql	= array("clientid"=>(int)$_POST['clientid'],"holidayid"=>$holidayid,"customerid"=>(int)$_POST['CustomerID']);

				$DelLinkerQuery	= pdo_query($DelLinkerSql,$DelLinkerEsql);

				if($_POST['CustomerID'] > 0 && $_POST['CustomerType'] == 1)
				{
					$subscriptionsql	= "SELECT inv.name,subs.quantity qty,subs.inventoryid FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
					$subscriptionesql	= array("customerid"=>(int)$_POST['CustomerID'],"deletedon"=>1);

					$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
					$subscriptionnum	= pdo_num_rows($subscriptionquery);

					if($subscriptionnum > 0)
					{
						while($subsrow	= pdo_fetch_assoc($subscriptionquery))
						{
							$inventoryid	 = $subsrow['inventoryid'];
							$inventoryname	 = $subsrow['name'];
							$qty			 = $subsrow['qty'];

							$AddLinkerSql	= "INSERT INTO ".$Prefix."holidays_stock_linker SET 
							clientid	=:clientid,
							holidayid	=:holidayid,
							customerid	=:customerid,
							stockid		=:stockid,
							stockname	=:stockname,
							qty			=:qty,
							startdate	=:startdate,
							enddate		=:enddate,
							createdon	=:createdon";

							$AddLinkerEsql	= array(
								"clientid"		=>(int)$_POST['clientid'],
								"holidayid"		=>(int)$holidayid,
								"customerid"	=>(int)$_POST['CustomerID'],
								"stockid"		=>(int)$inventoryid,
								"stockname"		=>$inventoryname,
								"qty"			=>(int)$qty,
								"startdate"		=>$StartDate,
								"enddate"		=>$EndDate,
								"createdon"		=>time()
							);

							$AddLinkerQuery	= pdo_query($AddLinkerSql,$AddLinkerEsql);
						}
					}
				}
				$Response['success']	= true;
				$Response['msg']		= "Holiday period updated successfully.";
				$Response['toastmsg']		= "Holiday period updated successfully.";
			}
			else
			{
				$Response['success']	= false;
				$Response['msg']		= "Unable to update holiday period.";
				$Response['toastmsg']	= "Unable to update holiday period.";
			}
		}
	}
	else
	{
		$Response['success']	= false;
		$Response['msg']		= $ErrorMessage;
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteHoliday")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete holiday.";

	if($_POST['iscustomerarea'] > 0)
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."holidays WHERE id=:id AND clientid=:clientid";
		$CheckEsql	= array('id'=>(int)$_POST['HolidayID'],"clientid"=>(int)$_POST['clientid']);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$checkrow	= pdo_fetch_assoc($CheckQuery);

			$startdate	= $checkrow['startdate'];

			$checkdate	= strtotime(date("m/d/y")) + 129600;

			if($startdate < $checkdate)
			{
				$Response['success']	= false;
				$Response['msg']		= "Holiday can be deleted before 36 hours only.";

				$json = json_encode($Response);
				echo $json;
				die;
			}
		}
	}

	$UpdateSQL = "UPDATE ".$Prefix."holidays SET deletedon=:deletedon WHERE id=:id AND clientid=:clientid";

	$UpdateESQL = array("deletedon"=>time(),'id'=>(int)$_POST['HolidayID'],"clientid"=>(int)$_POST['clientid']);

	$Query	= pdo_query($UpdateSQL,$UpdateESQL);

	if($Query)
	{
		$DelLinkerSql	= "DELETE FROM ".$Prefix."holidays_stock_linker WHERE clientid=:clientid AND holidayid=:holidayid";
		$DelLinkerEsql	= array("clientid"=>(int)$_POST['clientid'],"holidayid"=>(int)$_POST['HolidayID']);

		$DelLinkerQuery	= pdo_query($DelLinkerSql,$DelLinkerEsql);
	}

	if($Query)
	{
		$Response['success']	= true;
		$Response['msg']		= "Holiday deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetHolidayDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch holiday detail.";

	$sql	= "SELECT * FROM ".$Prefix."holidays WHERE id=:id AND clientid=:clientid";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"]);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$cuctomerArr	= array();
	$InventoryArr	= array();
	$detailArr		= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$detailArr["holidaytype"]	= (int)$row['customertype'];
		$detailArr["customerid"]	= (int)$row['customerid'];
		$detailArr["inventorytype"]	= (int)$row['inventorytype'];
		$detailArr["inventoryid"]	= (int)$row['inventoryid'];
		$detailArr["startdate"]		= date("Y-m-d",$row['startdate']);
		$detailArr["enddate"]		= date("Y-m-d",$row['enddate']);
		$detailArr["reason"]		= $row['reason'];

		$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE 1 AND id=:id AND deletedon < :deletedon ORDER BY sequence ASC, customerid ASC";
		$CustomerEsql	= array("id"=>(int)$detailArr["customerid"],"deletedon"=>1);

		$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
		$CustomerNum	= pdo_num_rows($CustomerQuery);

		$name2	= "Customer";

		if($CustomerNum > 0)
		{
			$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

			$customername	= $CustomerRows['name'];
			$firstname		= $CustomerRows['firstname'];
			$lastname		= $CustomerRows['lastname'];
			$customerid		= $CustomerRows['customerid'];
			$phone			= $CustomerRows['phone'];

			$name2	= "#".$customerid." ".$customername;

			if(trim($phone) != "")
			{
				$name2	.= " (".$phone.")";
			}
		}
		$response["customername"]		= $name2;

		$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE 1 AND id=:id";
		$InventoryEsql	= array("id"=>(int)$detailArr["inventoryid"]);

		$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
		$InventoryNum	= pdo_num_rows($InventoryQuery);

		$name2	= "Select";

		if($InventoryNum > 0)
		{
			$InventoryRows	= pdo_fetch_assoc($InventoryQuery);

			$inventoryname	= $InventoryRows['name'];

			$name2	= $inventoryname;

		}
		$response["inventoryname"]		= $name2;
		$response['success']	= true;
		$response['msg']		= "holiday detail fetched successfully.";
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

	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon ".$custcondition." ORDER BY sequence ASC, customerid ASC";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);
	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$customername2 = "";	
			$id			= $rows['id'];
			$customerid	= $rows['customerid'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$stateid	= $rows['stateid'];
			$cityid		= $rows['cityid'];
			
			if($name !="")
			{
				$customername2	= "#".$customerid." ".$name;
			}	
			if(trim($phone) != "")
			{
				$customername2	.= " (".$phone.")";
			}

			$cuctomerArr[$index]['id']		= (int)$id;
			$cuctomerArr[$index]['name']	= $customername2;
			$cuctomerArr[$index]['phone']	= $phone;
			$cuctomerArr[$index]['stateid']	= (int)$stateid;
			$cuctomerArr[$index]['cityid']	= (int)$cityid;

			$index++;
		}
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$index	= 0;

		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			if($InventoryNum > 0)
			{
				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id			= $rows['id'];
					$categoryid	= $rows['categoryid'];
					$name		= $rows['name'];
					$price		= $rows['price'];

					if(!empty($ClientInventoryData[$id]))
					{
						$inventorystatus	= $ClientInventoryData[$id]['status'];
						$inventoryprice		= $ClientInventoryData[$id]['price'];

						$InventoryArr[$index]['id']			= (int)$id;
						$InventoryArr[$index]['name']		= $name;
						$InventoryArr[$index]['categoryid']	= (int)$categoryid;
						$InventoryArr[$index]['isassigned']	= $inventorystatus;
						$InventoryArr[$index]['price']		= (float)$inventoryprice;

						$index++;
					}
				}
			}
		}
	}

	$response['detail']			= $detailArr;
	$response['customerlist']	= $cuctomerArr;
	$response['inventorylist']	= $InventoryArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetHolidayList")
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

	$CheckESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
	$condition	= "";

	if($_POST['customertype'] == 1)
	{
		$CheckESQL['deletedon2']	= 1;
	}

	if($_POST['customertype'] == 1)
	{
		if($_POST['areaid'] > 0)
		{
			$condition	.= " AND cust.areaid=:areaid";
			$CheckESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['lineid'] > 0)
		{
			$condition	.= " AND cust.lineid=:lineid";
			$CheckESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$condition	.= " AND cust.hawkerid=:hawkerid";
			$CheckESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

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
	}

	if($_POST['iscustomerarea'] > 0)
	{
		$condition	.= " AND hol.customertype=:customertype AND hol.customerid=:customerid AND hol.addedby=:addedby";

		$CheckESQL['customertype']	= 1;
		$CheckESQL['customerid']	= (int)$_POST['customerid'];
		$CheckESQL['addedby']		= "user";

		$perpage	= 1000;
	}

	if($_POST['customertype'] == 1)
	{
		$CheckSQL	= "SELECT hol.* FROM ".$Prefix."holidays hol,".$Prefix."customers cust WHERE hol.clientid=:clientid AND hol.deletedon <:deletedon AND cust.deletedon < :deletedon2 AND hol.customerid=cust.id ".$condition." GROUP BY hol.createdon ORDER BY hol.enddate DESC";
	}
	else
	{
		$CheckSQL	= "SELECT hol.* FROM ".$Prefix."holidays hol WHERE hol.clientid=:clientid AND hol.deletedon <:deletedon ".$condition." ORDER BY hol.enddate DESC";
	}

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$Num		= pdo_num_rows($CheckQuery);

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

	$Sql2	= $CheckSQL.$addquery;
	$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));
	$Query2	= pdo_query($Sql2,$CheckESQL);
	$Num2	= pdo_num_rows($Query2);	
	$Arr	= array();

	if($Num2 > 0)
	{
		$AllCustomerArr = GetAllCustomerNameByClientID($_POST['clientid']);
		$AllInventory	= GetInventoryNames();
		$Index			= 0; 
		while($CheckRow = pdo_fetch_assoc($Query2))
		{
			$HolidayID		= $CheckRow['id'];
			$CustomerType	= $CheckRow['customertype'];
			$CustomerID		= $CheckRow['customerid'];
			$InventoryType	= $CheckRow['inventorytype'];
			$InventoryID	= $CheckRow['inventoryid'];
			$StartDateUnix	= $CheckRow['startdate'];
			$EndDateUnix	= $CheckRow['enddate'];
			$Reason			= $CheckRow['reason'];
			$createdon		= $CheckRow['createdon'];
			$addedby		= $CheckRow['addedby'];

			if($addedby == "user")
			{
				$addedby	= "Customer";
			}
			else
			{
				$addedby	= "Owner";
			}

			$canuserdelete	= true;

			if(strtotime(date("m/d/y",$createdon)) < strtotime(date("m/d/y")))
			{
				$canuserdelete	= false;
			}

			$CustomerTypeStr 	= "All Customers";
			$InventoryTypeStr	= "";
			$CustomerName 		= "";
			$InventoryName 		= "";
			if($CustomerType < 1)
			{
				$InventoryTypeStr	= "Global";
			}
			else
			{
				$CustomerTypeStr 	= "Individual Customer";
				//$InventoryTypeStr = "Global"; 
				$CustName	= $AllCustomerArr[$CustomerID]['name'];
				$CustPhone	= $AllCustomerArr[$CustomerID]['phone'];
				$CustID		= $AllCustomerArr[$CustomerID]['customerid'];
				
				$CustomerName	= "#".$CustID." ".$CustName;
				if($CustPhone !="")
				{
					$CustomerName .=  " (".$CustPhone.")";
				}
				$InventoryType = 0;
				$InventoryID   = 0;
			}

			if($InventoryType < 1)
			{
				//$InventoryName	= "ALL";
				$InventoryName = "";
			}
			else
			{
				$InventoryTypeStr = "Individual"; 
				$InventoryName	= $AllInventory[$InventoryID]['name'];
			}

			$StartDate		= date("d-M-Y",$StartDateUnix);
			$EndDate		= date("d-M-Y",$EndDateUnix);
			
			$Arr[$Index]['id']				= $HolidayID;
			$Arr[$Index]['customertype']	= $CustomerTypeStr;
			$Arr[$Index]['customerid']		= $CustomerID;
			$Arr[$Index]['customername']	= $CustomerName;
			$Arr[$Index]['inventorytype']	= $InventoryTypeStr;
			$Arr[$Index]['inventoryid']		= $InventoryID;
			$Arr[$Index]['inventoryname']	= $InventoryName;
			$Arr[$Index]['startdateunix']	= $StartDateUnix;
			$Arr[$Index]['enddateunix']		= $EndDateUnix;
			$Arr[$Index]['startdate']		= $StartDate;
			$Arr[$Index]['enddate']			= $EndDate;
			$Arr[$Index]['reason']			= $Reason;
			$Arr[$Index]['addeddate']		= date("d-M-Y",$createdon);
			$Arr[$Index]['addedby']			= ucwords($addedby);
			$Arr[$Index]['canuserdelete']	= $canuserdelete;
			
			$Index++;
		}
		$Response['success']	= true;
		$Response['msg']		= $CheckNum." Holiday(s) found.";
		$Response['toastmsg']	= $CheckNum." Holiday(s) found.";
	}
	else
	{
		$Response['success']	= false;
		$Response['msg']		= "No Holiday(s) Added Yet.";
		$Response['toastmsg']	= "No Holidays Added Yet.";
	}
	$pageListArr	= array();
	$pagelistindex	= 0;

	for($pageloop = 1; $pageloop <= $totalpages; $pageloop++)
	{
		$pageListArr[$pagelistindex]['page']	= $pageloop;
		$pageListArr[$pagelistindex]['name']	= "Page ".$pageloop;

		$pagelistindex++;
	}

	$Response['recordlist']		= $Arr;
	$Response['perpage']		= (int)$perpage;
	$Response['totalpages']		= (int)$totalpages;
	$Response['paginglist']		= $pageListArr;
	$Response['showpages']		= false;
	$Response['totalrecord']	= $TotalRec;

	if($totalpages > 1)
	{
		$Response['showpages']	= true;
	}	
    $json = json_encode($Response);
    echo $json;
	die;
}
?>