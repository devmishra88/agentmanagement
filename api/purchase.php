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

if($_POST['Mode'] == "AddPurchase")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add purchase.";

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
	}

	$purchaserate	= $_POST['purchaserate'];
	$issuedate		= strtotime($_POST['issuedate']);

	if($_POST['type'] == 1)
	{
		/*$purchaserate	= 0;*/
		$issuedate		= 0;
	}

	$datetimestamp	= strtotime($_POST['purchasedate']);

	$HolidaySql	= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND customertype=:customertype AND deletedon < :deletedon AND (:holidaydate BETWEEN startdate AND enddate)";
	$HolidayEsql	= array("clientid"=>(int)$_POST['clientid'],"customertype"=>0,"deletedon"=>1,"holidaydate"=>$datetimestamp);

	$HolidayQuery	= pdo_query($HolidaySql,$HolidayEsql);
	$HolidayNum		= pdo_num_rows($HolidayQuery);

	if($HolidayNum > 0)
	{
		$haserror	= true;
		$response['msg']		= "The purchase cannot be added due to the holiday";
		$response['toastmsg']	= "The purchase cannot be added due to the holiday";
	}

	if(!$haserror)
	{
		if($_POST['hasbulkentry'] < 1)
		{
			$CheckSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."purchase WHERE clientid=:clientid AND purchasedate=:purchasedate AND inventoryid=:inventoryid AND droppingpointid=:droppingpointid";
			$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"purchasedate"=>strtotime($_POST['purchasedate']),"inventoryid"=>(int)$_POST['inventoryid'],"droppingpointid"=>(int)$_POST['droppingpointid']);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckRows	= pdo_fetch_assoc($CheckQuery);

			$CheckCount	= $CheckRows['C'];

			if($CheckCount > 0)
			{
				$haserror	= true;
				$response['msg']		= "A record already exist with the same detail";
				$response['toastmsg']	= "A record already exist with the same detail";
			}
		}
	}

	$isrecordupdated	= false;

	if($haserror == false)
	{
		if($_POST['hasbulkentry'] < 1)
		{
			$Sql	= "INSERT INTO ".$Prefix."purchase SET 
			clientid		=:clientid,
			inventoryid		=:inventoryid,
			droppingpointid	=:droppingpointid,
			purchasedate	=:purchasedate,
			noofpices		=:noofpices,
			createdon		=:createdon,
			issuedate		=:issuedate";

			$Esql	= array(
				"clientid"		=>(int)$_POST['clientid'],
				"inventoryid"	=>(int)$_POST['inventoryid'],
				"droppingpointid"=>(int)$_POST['droppingpointid'],
				"purchasedate"	=>strtotime($_POST['purchasedate']),
				"noofpices"		=>(int)$_POST['noofpices'],
				"createdon"		=>$createdon,
				"issuedate"		=>$issuedate
			);

			$Query	= pdo_query($Sql,$Esql);

			if($Query)
			{
				$recordid	= pdo_insert_id();

				$date			= date("d",strtotime($_POST['purchasedate']));
				$_POST["month"]	= date("m",strtotime($_POST['purchasedate']));
				$_POST["year"]	= date("Y",strtotime($_POST['purchasedate']));
				$dateprice		= $purchaserate;
				$inventoryid	= $_POST['inventoryid'];

				$CheckSql2	= "SELECT * FROM ".$Prefix."inventory_date_price_linker WHERE 
				year		=:year 
				AND
				month		=:month 
				AND
				clientid	=:clientid 
				AND
				inventoryid	=:inventoryid
				AND
				date		=:date";

				$CheckEsql2	= array(
					"year"			=>(int)$_POST['year'],
					"month"			=>(int)$_POST['month'],
					"clientid"		=>(int)$_POST['clientid'],
					"inventoryid"	=>(int)$inventoryid,
					"date"			=>(int)$date
				);

				$CheckQuery2	= pdo_query($CheckSql2,$CheckEsql2);
				$CheckNum2		= pdo_num_rows($CheckQuery2);

				if($dateprice != "")
				{
					$InvSql		= "SELECT * FROM ".$Prefix."inventory WHERE id=:id";
					$InvEsql	= array("id"=>(int)$inventoryid);

					$InvQuery	= pdo_query($InvSql,$InvEsql);
					$InvRows	= pdo_fetch_assoc($InvQuery);

					$categoryid	= $InvRows['categoryid'];
					$name		= $InvRows['name'];

					if($CheckNum2 > 0)
					{
						$checkrows2	= pdo_fetch_assoc($CheckQuery2);
						$checkid2	= $checkrows2['id'];

						$Sql2	= "UPDATE ".$Prefix."inventory_date_price_linker SET 
						year		=:year,
						month		=:month,
						clientid	=:clientid,
						inventoryid	=:inventoryid,
						date		=:date,
						price		=:price
						WHERE
						id			=:id";

						$Esql2	= array(
							"year"			=>(int)$_POST['year'],
							"month"			=>(int)$_POST['month'],
							"clientid"		=>(int)$_POST['clientid'],
							"inventoryid"	=>(int)$inventoryid,
							"date"			=>(int)$date,
							"price"			=>(float)$dateprice,
							"id"			=>(int)$checkid2
						);

						$Query2	= pdo_query($Sql2,$Esql2);

						if($Query2)
						{
							$issuccess	= true;
						}
					}
					else
					{
						$Sql2	= "INSERT INTO ".$Prefix."inventory_date_price_linker SET 
						year		=:year,
						month		=:month,
						clientid	=:clientid,
						inventoryid	=:inventoryid,
						categoryid	=:categoryid,
						name		=:name,
						date		=:date,
						price		=:price,
						createdon	=:createdon";

						$Esql2	= array(
							"year"			=>(int)$_POST['year'],
							"month"			=>(int)$_POST['month'],
							"clientid"		=>(int)$_POST['clientid'],
							"inventoryid"	=>(int)$inventoryid,
							"categoryid"	=>(int)$categoryid,
							"name"			=>$name,
							"date"			=>(int)$date,
							"price"			=>(float)$dateprice,
							"createdon"		=>$createdon
						);

						$Query2	= pdo_query($Sql2,$Esql2);

						if($Query2)
						{
							$issuccess	= true;
						}
					}

					syncInventoryPriceTotal($_POST['clientid'], $inventoryid, $_POST['month'], $_POST['year'], 1);
				}

				$response['success']	= true;
				$response['recordid']	= $recordid;
				$response['name']		= $_POST['name'];
				$response['msg']		= "Purchase added successfully.";
			}
		}
		else
		{
			$ErrorInventoryArr	= array();

			$hasbulkrecordtosave	= false;

			foreach($_POST['bulkinventorylist'] as $key => $inventoryrows)
			{
				$inventoryid		= $inventoryrows['id'];
				$numberofpieces		= $inventoryrows['numberofpieces'];
				$purchaserate		= $inventoryrows['purchaserate'];
				$haspurchaserate	= $inventoryrows['haspurchaserate'];
				$hasnoofpices		= $inventoryrows['hasnoofpices'];
				$inventoryname		= $inventoryrows['name'];

				if(($haspurchaserate == "false" || $hasnoofpices == "false") && ($numberofpieces != "" || $purchaserate != ""))
				{
					if(($numberofpieces == "0" && $hasnoofpices == "false") || ($purchaserate == "0" && $haspurchaserate == "false"))
					{
						$ErrorInventoryArr[]	= $inventoryname;
					}

					if(($numberofpieces > 0 && (($purchaserate == "0" || $purchaserate == "") && $haspurchaserate == "false")) || ($purchaserate > 0 && ($numberofpieces == "0" || $numberofpieces == "") && $haspurchaserate == "false"))
					{
						$ErrorInventoryArr[]	= $inventoryname;
					}
				}
			}

			if(empty($ErrorInventoryArr))
			{
				foreach($_POST['bulkinventorylist'] as $key => $inventoryrows)
				{
					$inventoryid		= $inventoryrows['id'];
					$numberofpieces		= $inventoryrows['numberofpieces'];
					$purchaserate		= $inventoryrows['purchaserate'];
					$haspurchaserate	= $inventoryrows['haspurchaserate'];
					$hasnoofpices		= $inventoryrows['hasnoofpices'];

					if(($haspurchaserate == "false" || $hasnoofpices == "false") && ($numberofpieces > 0 || $purchaserate > 0))
					{
						if($hasnoofpices == "false" && $numberofpieces > 0)
						{
							$Sql	= "INSERT INTO ".$Prefix."purchase SET 
							clientid		=:clientid,
							inventoryid		=:inventoryid,
							droppingpointid	=:droppingpointid,
							purchasedate	=:purchasedate,
							noofpices		=:noofpices,
							createdon		=:createdon,
							issuedate		=:issuedate";

							$Esql	= array(
								"clientid"		=>(int)$_POST['clientid'],
								"inventoryid"	=>(int)$inventoryid,
								"droppingpointid"=>(int)$_POST['droppingpointid'],
								"purchasedate"	=>strtotime($_POST['purchasedate']),
								"noofpices"		=>(int)$numberofpieces,
								"createdon"		=>$createdon,
								"issuedate"		=>$issuedate
							);

							$Query	= pdo_query($Sql,$Esql);

							if($Query)
							{
								$hasbulkrecordtosave	= true;
							}
						}
						else
						{
							$Query	= false;
						}

						if($Query || ($purchaserate > 0 && $haspurchaserate == "false"))
						{
							if($Query)
							{
								$recordid	= pdo_insert_id();
							}
							$isrecordupdated	= true;

							$date			= date("d",strtotime($_POST['purchasedate']));
							$_POST["month"]	= date("m",strtotime($_POST['purchasedate']));
							$_POST["year"]	= date("Y",strtotime($_POST['purchasedate']));

							$CheckSql2	= "SELECT * FROM ".$Prefix."inventory_date_price_linker WHERE 
							year		=:year 
							AND
							month		=:month 
							AND
							clientid	=:clientid 
							AND
							inventoryid	=:inventoryid
							AND
							date		=:date";

							$CheckEsql2	= array(
								"year"			=>(int)$_POST['year'],
								"month"			=>(int)$_POST['month'],
								"clientid"		=>(int)$_POST['clientid'],
								"inventoryid"	=>(int)$inventoryid,
								"date"			=>(int)$date
							);

							$CheckQuery2	= pdo_query($CheckSql2,$CheckEsql2);
							$CheckNum2		= pdo_num_rows($CheckQuery2);

							if($purchaserate > 0)
							{
								$InvSql		= "SELECT * FROM ".$Prefix."inventory WHERE id=:id";
								$InvEsql	= array("id"=>(int)$inventoryid);

								$InvQuery	= pdo_query($InvSql,$InvEsql);
								$InvRows	= pdo_fetch_assoc($InvQuery);

								$categoryid	= $InvRows['categoryid'];
								$name		= $InvRows['name'];

								if($CheckNum2 > 0)
								{
									$checkrows2	= pdo_fetch_assoc($CheckQuery2);
									$checkid2	= $checkrows2['id'];

									$Sql2	= "UPDATE ".$Prefix."inventory_date_price_linker SET 
									year		=:year,
									month		=:month,
									clientid	=:clientid,
									inventoryid	=:inventoryid,
									date		=:date,
									price		=:price
									WHERE
									id			=:id";

									$Esql2	= array(
										"year"			=>(int)$_POST['year'],
										"month"			=>(int)$_POST['month'],
										"clientid"		=>(int)$_POST['clientid'],
										"inventoryid"	=>(int)$inventoryid,
										"date"			=>(int)$date,
										"price"			=>(float)$purchaserate,
										"id"			=>(int)$checkid2
									);

									$Query2	= pdo_query($Sql2,$Esql2);

									if($Query2)
									{
										$issuccess	= true;
										$hasbulkrecordtosave	= true;
									}
								}
								else
								{
									$Sql2	= "INSERT INTO ".$Prefix."inventory_date_price_linker SET 
									year		=:year,
									month		=:month,
									clientid	=:clientid,
									inventoryid	=:inventoryid,
									categoryid	=:categoryid,
									name		=:name,
									date		=:date,
									price		=:price,
									createdon	=:createdon";

									$Esql2	= array(
										"year"			=>(int)$_POST['year'],
										"month"			=>(int)$_POST['month'],
										"clientid"		=>(int)$_POST['clientid'],
										"inventoryid"	=>(int)$inventoryid,
										"categoryid"	=>(int)$categoryid,
										"name"			=>$name,
										"date"			=>(int)$date,
										"price"			=>(float)$purchaserate,
										"createdon"		=>$createdon
									);

									$Query2	= pdo_query($Sql2,$Esql2);

									if($Query2)
									{
										$issuccess	= true;
										$hasbulkrecordtosave	= true;
									}
								}

								syncInventoryPriceTotal($_POST['clientid'], $inventoryid, $_POST['month'], $_POST['year'], 1);
							}
						}
					}
				}
			}
		}
	}

	$iserrorshown	= false;

	if($isrecordupdated && !$iserrorshown)
	{
		$iserrorshown	= true;

		$response['success']	= true;
		$response['recordid']	= $recordid;
		$response['name']		= $_POST['name'];
		$response['msg']		= "Purchase added successfully.";
	}

	if(!empty($ErrorInventoryArr) && !$iserrorshown)
	{
		$iserrorshown	= true;

		$response['success']	= false;
		$response['msg']		= "There is error in purchase stock entry for ".(implode(", ",$ErrorInventoryArr)).".";
	}

	if(!$hasbulkrecordtosave && empty($ErrorInventoryArr) && !$iserrorshown)
	{
		$iserrorshown	= true;

		$response['success']	= false;
		$response['msg']		= "Nothing found to update.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllPurchase")
{
	$RecordListIndex	= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch purchase order list.";

	$purchasetotal	= 0;

	$PurchaseDateArr	= array();

	$condition	= "";

	$Esql	= array("clientid"=>(int)$_POST["clientid"]);

	if(trim($_POST['purchasestartdate']) != "")
	{
		$condition	.= " AND purchasedate=:purchasedate";
		$Esql['purchasedate']	= strtotime($_POST['purchasestartdate']);
	}

	/*if(trim($_POST['purchasestartdate']) != "" && trim($_POST['purchaseenddate']) != "")
	{
		$StartDate	= strtotime($_POST['purchasestartdate']);
		$EndDate	= strtotime($_POST['purchaseenddate'])+86399;

		$condition	.= " AND purchasedate BETWEEN :startdate AND :enddate";

		$Esql['startdate']	= $StartDate;
		$Esql['enddate']	= $EndDate;
	}*/

	if(trim($_POST['droppingpointid']) != "")
	{
		$condition	.= " AND droppingpointid=:droppingpointid";
		$Esql['droppingpointid']	= (int)$_POST['droppingpointid'];
	}

	if(trim($_POST['inventoryid']) != "")
	{
		$condition	.= " AND inventoryid=:inventoryid";
		$Esql['inventoryid']	= (int)$_POST['inventoryid'];
	}

	$DroppingPointArr	= array();
	$DroppingPointStr	='';

	if($_POST['ismanager'] > 0)
	{
		$DroppingPointStr = '-1';
		$DroppingPointArr = GetAllDroppingPointByAreaManager($_POST['clientid'],$_POST['areamanagerid']);
		if(!empty($DroppingPointArr))
		{
			$DroppingPointStr = implode(",",$DroppingPointArr);
		}

		$condition	.= " AND droppingpointid IN (".$DroppingPointStr.") ";
	}
	
	$Sql	= "SELECT DISTINCT(purchasedate) FROM ".$Prefix."purchase WHERE clientid=:clientid ".$condition." ORDER BY purchasedate DESC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$TotalRec	= 0;

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$purchasedate	= $rows['purchasedate'];

			$PurchaseDateArr[$purchasedate]	= $purchasedate;
		}
	}
	else
	{
		$PurchaseDateArr	= array();

		$purchasedate	= strtotime($_POST['purchasestartdate']);

		$PurchaseDateArr[$purchasedate]	= $purchasedate;
	}

	if(!empty($PurchaseDateArr))
	{
		$DroppingPointArr = GetAllDroppingPoint($_POST["clientid"]);

		foreach($PurchaseDateArr as $key=>$value)
		{
			$index	= 0;
			$RecordSetArr	= array();

			$PurchaseEsql	= array("clientid"=>(int)$_POST["clientid"],"purchasedate"=>$value);

			$condtion2	= "";

			if(trim($_POST['droppingpointid']) != "")
			{
				$condtion2	.= " AND purchase.droppingpointid=:droppingpointid";
				$PurchaseEsql['droppingpointid']	= (int)$_POST['droppingpointid'];
			}

			if(trim($_POST['inventoryid']) != "")
			{
				$condtion2	.= " AND purchase.inventoryid=:inventoryid";
				$PurchaseEsql['inventoryid']	= (int)$_POST['inventoryid'];
			}
			if($DroppingPointStr !='')
			{
				$condtion2	.= " AND purchase.droppingpointid IN (".$DroppingPointStr.")";
			}

			$PurchaseSql	= "SELECT inv.*,purchase.noofpices AS qty,purchase.issuedate AS issuedate,purchase.droppingpointid AS droppingpointid,purchase.id AS purchaseid FROM ".$Prefix."purchase purchase,".$Prefix."inventory inv WHERE inv.id=purchase.inventoryid AND purchase.clientid=:clientid AND purchase.purchasedate=:purchasedate ".$condtion2." GROUP BY purchase.id ORDER BY purchase.purchasedate DESC";

			$PurchaseQuery	= pdo_query($PurchaseSql,$PurchaseEsql);
			$PurchaseNum	= pdo_num_rows($PurchaseQuery);

			$TotalRec	+= $PurchaseNum;

			if($PurchaseNum > 0)
			{
				while($PurchaseRows = pdo_fetch_assoc($PurchaseQuery))
				{
					$name				= $PurchaseRows['name'];
					$qty				= $PurchaseRows['qty'];
					$issuedate			= $PurchaseRows['issuedate'];
					$droppingpointid	= $PurchaseRows['droppingpointid'];
					$purchaseid			= $PurchaseRows['purchaseid'];
					$inventoryid		= $PurchaseRows['id'];

					$purchaserate = getPurchasePrice($_POST["clientid"], $inventoryid, strtotime($_POST['purchasestartdate']));

					if($purchaserate == "")
					{
						$purchaserate	= "---";
					}

					$RecordSetArr[$index]['serial']				= $index+1;
					$RecordSetArr[$index]['purchaseid']			= (int)$purchaseid;
					$RecordSetArr[$index]['name']				= $name;
					$RecordSetArr[$index]['qty']				= $qty;
					$RecordSetArr[$index]['issuedate']			= "";
					$RecordSetArr[$index]['purchaserate']		= $purchaserate;
					$RecordSetArr[$index]['droppingpoint']		= $DroppingPointArr[$droppingpointid]['name'];

					$purchasetotal	+= $qty;

					/*if($purchaserate > 0)
					{
						$RecordSetArr[$index]['purchaserate']	= number_format($purchaserate,2);
					}*/

					if($issuedate > 0)
					{
						$RecordSetArr[$index]['issuedate']		= date("j F, Y",$issuedate);
					}

					$index++;
				}

				$RecordListArr[$RecordListIndex]['index']		= $RecordListIndex+1;
				$RecordListArr[$RecordListIndex]['date']		= date("d-M-Y",$value);
				$RecordListArr[$RecordListIndex]['purchase']	= $RecordSetArr;

				$RecordListIndex++;
			}
			/*else
			{
				$condtion2		= "";

				$PurchaseEsql	= array("clientid"=>(int)$_POST['clientid'],"cityid"=>(int)$_POST["cityid"],"stateid"=>(int)$_POST["stateid"],"status"=>1);

				if(trim($_POST['inventoryid']) != "")
				{
					$condtion2	.= " AND inv.id=:id";
					$PurchaseEsql['id']	= (int)$_POST['inventoryid'];
				}

				$PurchaseSql	= "SELECT inv.* FROM ".$Prefix."client_inventory_linker linker,".$Prefix."inventory inv WHERE inv.id=linker.inventoryid AND linker.clientid=:clientid AND linker.cityid=:cityid AND linker.stateid=:stateid AND linker.status=:status ".$condtion2." GROUP BY inv.id ORDER BY inv.name DESC";

				$PurchaseQuery	= pdo_query($PurchaseSql,$PurchaseEsql);
				$PurchaseNum	= pdo_num_rows($PurchaseQuery);

				$TotalRec		= $PurchaseNum;

				if($PurchaseNum > 0)
				{
					while($PurchaseRows = pdo_fetch_assoc($PurchaseQuery))
					{
						$name				= $PurchaseRows['name'];
						$qty				= $PurchaseRows['qty'];
						$issuedate			= $PurchaseRows['issuedate'];
						$droppingpointid	= $PurchaseRows['droppingpointid'];
						$purchaseid			= $PurchaseRows['purchaseid'];
						$inventoryid		= $PurchaseRows['id'];

						if($purchaserate == "")
						{
							$purchaserate	= "---";
						}

						$RecordSetArr[$index]['serial']				= $index+1;
						$RecordSetArr[$index]['purchaseid']			= "";
						$RecordSetArr[$index]['name']				= $name;
						$RecordSetArr[$index]['qty']				= "--";
						$RecordSetArr[$index]['issuedate']			= "--";
						$RecordSetArr[$index]['purchaserate']		= "--";
						$RecordSetArr[$index]['droppingpoint']		= $DroppingPointArr[$droppingpointid]['name'];

						$purchasetotal	+= 0;

						$index++;
					}

					$RecordListArr[$RecordListIndex]['index']		= $RecordListIndex+1;
					$RecordListArr[$RecordListIndex]['date']		= date("d-M-Y",$value);
					$RecordListArr[$RecordListIndex]['purchase']	= $RecordSetArr;

					$RecordListIndex++;
				}
			}*/
		}
	}

	if(!empty($RecordListArr))
	{
		$response['success']		= true;
		$response['totalqty']		= $purchasetotal;
		$response['totalrecord']	= $TotalRec;
		$response['msg']			= "Purchase order list fetched successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeletePurchase")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete Purchase, Please try later.";

	$DelSql		= "DELETE FROM ".$Prefix."purchase
	WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$DelEsql	= array(
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']	
	);

	$DelQuery	= pdo_query($DelSql,$DelEsql);

	if($DelQuery)
	{
		$Response['success']	= true;
		$Response['msg']		= "Purchase Record deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetPurchaseDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch purchase detail.";

	$sql	= "SELECT * FROM ".$Prefix."purchase WHERE id=:id AND clientid=:clientid";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"]);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		/*$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"inventoryid"	=>(int)$_POST['inventoryid'],
			"droppingpointid"=>(int)$_POST['droppingpointid'],
			"purchasedate"	=>strtotime($_POST['purchasedate']),
			"noofpices"		=>(int)$_POST['noofpices'],
			"createdon"		=>$createdon,
			"purchaserate"	=>(float)$purchaserate,
			"issuedate"		=>$issuedate
		);*/

		$InventoryNamesArr	= GetInventoryNames();
		$DroppingPointArr	= GetAllDroppingPoint($_POST['clientid']);

		$detailArr["inventoryid"]		= $row['inventoryid'];
		$detailArr["inventoryname"]		= $InventoryNamesArr[$row['inventoryid']]['name'];

		$categoryid	= $InventoryNamesArr[$row['inventoryid']]['categoryid'];

		$SqlCat		= "SELECT * FROM ".$Prefix."category WHERE id=:id";
		$EsqlCat	= array("id"=>(int)$categoryid);

		$QueryCat	= pdo_query($SqlCat,$EsqlCat);
		$RowsCat	= pdo_fetch_assoc($QueryCat);

		$type	= (int)$RowsCat['type'];

		$detailArr["droppingpointid"]	= $row['droppingpointid'];
		$detailArr["droppingpointname"]	= $DroppingPointArr[$row['droppingpointid']]['name'];
		$detailArr["purchasedate"]		= $row['purchasedate'];
		$detailArr["noofpices"]			= $row['noofpices'];
		$detailArr["issuedate"]			= "";
		$detailArr["type"]				= $type;

		if($row['purchasedate'] > 0)
		{
			$detailArr["purchasedate"]	= date("Y-m-d",$row['purchasedate']);
		}
		if($row['issuedate'] > 0)
		{
			$detailArr["issuedate"]		= date("Y-m-d",$row['issuedate']);
		}

		$response['success']	= true;
		$response['msg']		= "Purchase detail fetched successfully.";
	}

	$response['detail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditPurchase")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add purchase.";

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
	}

	$purchaserate	= $_POST['purchaserate'];
	$issuedate		= strtotime($_POST['issuedate']);

	if($_POST['type'] == 1)
	{
		/*$purchaserate	= 0;*/
		$issuedate		= 0;
	}

	$CheckSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."purchase WHERE clientid=:clientid AND purchasedate=:purchasedate AND inventoryid=:inventoryid AND droppingpointid=:droppingpointid AND id<>:id";
	$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"purchasedate"=>strtotime($_POST['purchasedate']),"inventoryid"=>(int)$_POST['inventoryid'],"droppingpointid"=>(int)$_POST['droppingpointid'],"id"=>(int)$_POST['recordid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckRows	= pdo_fetch_assoc($CheckQuery);

	$CheckCount	= $CheckRows['C'];

	if($CheckCount > 0)
	{
		$haserror	= true;
		$response['msg']		= "A record already exist with the same detail";
		$response['toastmsg']	= "A record already exist with the same detail";
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."purchase SET 
		clientid		=:clientid,
		inventoryid		=:inventoryid,
		droppingpointid	=:droppingpointid,
		purchasedate	=:purchasedate,
		noofpices		=:noofpices,
		createdon		=:createdon,
		purchaserate	=:purchaserate,
		issuedate		=:issuedate
		WHERE
		id				=:id";

		$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"inventoryid"	=>(int)$_POST['inventoryid'],
			"droppingpointid"=>(int)$_POST['droppingpointid'],
			"purchasedate"	=>strtotime($_POST['purchasedate']),
			"noofpices"		=>(int)$_POST['noofpices'],
			"createdon"		=>$createdon,
			"purchaserate"	=>(float)$purchaserate,
			"issuedate"		=>$issuedate,
			"id"			=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];
			$response['msg']		= "Purchase edited successfully.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetPurchaseRate')
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to fetch purchase rates.";

	$purchasedate	= strtotime($_POST['purchasedate']);
	$inventoryid	= $_POST['inventoryid'];

	$date			= date("d",strtotime($_POST['purchasedate']));
	$month			= date("m",strtotime($_POST['purchasedate']));
	$year			= date("Y",strtotime($_POST['purchasedate']));

	$checksql2	= "select price from ".$Prefix."inventory_date_price_linker WHERE 
	year		=:year 
	and
	month		=:month 
	and
	clientid	=:clientid 
	and
	inventoryid	=:inventoryid
	and
	date		=:date";

	$checkesql2	= array("clientid"=>(int)$_POST['clientid'],"inventoryid"=>(int)$inventoryid,'month'=>(int)$month,'year'=>(int)$year,'date'=>(int)$date);

	$checkquery	= pdo_query($checksql2,$checkesql2);
	$checknum	= pdo_num_rows($checkquery);
	if($checknum > 0)
	{
		$checkrows	= pdo_fetch_assoc($checkquery);
		$purchaserate = $checkrows['price'];
	}

	$haserror	= false;
	
	$response['success']		= true;
    $response['purchaserate']	= (float)$purchaserate;
    $response['msg']			= "Purchase rate fetched successfully.";
	$json = json_encode($response);
    echo $json;
	die;
}
?>