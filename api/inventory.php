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

if($_POST['Mode'] == "GetAllInventory")
{
	$index			= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent stock detail.";

	$condition		= "";
	$CategoryEsql	= array("status"=>1);

	if($_POST['cattype'] != "")
	{
		$CategoryEsql['type']	= (int)$_POST['cattype'];
		$condition	.= " AND type=:type";
	}

	if($_POST['catid'] != "")
	{
		$CategoryEsql['id']	= (int)$_POST['catid'];
		$condition	.= " AND id=:id";
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$condition." ORDER BY orderby ASC";

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);
		$InventoryListArr	= array();
			
		$index		= 0;
		$idarray			= array();
		$categoryidarray	= array();
		$namearray			= array();
		$pricearray			= array();
		$frequencyarray		= array();
		$isassignedarray	= array();

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];
			$type		= $catrows['type'];

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
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];
							
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$isassignedarray[]	= $inventorystatus;
							$namearray[]		= $name;
							$pricearray[]		= (float)$inventoryprice;
						}
					}
				}
			}
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid ORDER BY inv.name ASC";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,'status'=>0);

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2	= pdo_num_rows($InventoryQuery2);
			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id			= $rows2['id'];
					$categoryid	= $rows2['categoryid'];
					$name		= $rows2['name'];
					$price		= $rows2['price'];
					$frequency	= $rows2['frequency'];

					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];
							
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$isassignedarray[]	= $inventorystatus;
							$namearray[]		= $name;
							$pricearray[]		= (float)$inventoryprice;
						}
					}
				}
			}
			
			if(!empty($namearray))
			{
				if($_POST['type'] != "bulkpurchasestock")
				{
					$InventoryListArr[$index]['id']					= "";
					$InventoryListArr[$index]['name']				= "Select";
					$InventoryListArr[$index]['categoryid']			= "";
					$InventoryListArr[$index]['isassigned']			= "";
					$InventoryListArr[$index]['price']				= "";
					$InventoryListArr[$index]['type']				= "";
					$InventoryListArr[$index]['hasnoofpices']		= "";
					$InventoryListArr[$index]['numberofpieces']		= "";
					$InventoryListArr[$index]['purchaserate']		= "";
					$InventoryListArr[$index]['haspurchaserate']	= "";

					$index++;
				}

				if($_POST['fromarea'] == "restartcustomer" || $_POST['fromarea'] == "salefilter")
				{
					$InventoryListArr[$index]['id']					= "-1";
					$InventoryListArr[$index]['name']				= 'All Stock';
					$InventoryListArr[$index]['categoryid']			= "";
					$InventoryListArr[$index]['isassigned']			= "";
					$InventoryListArr[$index]['price']				= "";
					$InventoryListArr[$index]['type']				= "";
					$InventoryListArr[$index]['hasnoofpices']		= "";
					$InventoryListArr[$index]['numberofpieces']		= "";
					$InventoryListArr[$index]['purchaserate']		= "";
					$InventoryListArr[$index]['haspurchaserate']	= "";

					$index++;
				}

				$sortnamearray = array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$frequencyarray);
				foreach($namearray as $key => $value)
				{
					$id				= $idarray[$key];
					$categoryid		= $categoryidarray[$key];
					$name			= $value;
					$inventoryprice	= $pricearray[$key];
					$isassigned		= $isassignedarray[$key];

					$noofpices			= "";
					$purchaserate		= "";
					$haspurchaserate	= false;
					$hasnoofpices		= false;

					if($_POST['type'] == 'bulkpurchasestock')
					{
						$date	= date("d",strtotime($_POST['purchasedate']));
						$month	= date("m",strtotime($_POST['purchasedate']));
						$year	= date("Y",strtotime($_POST['purchasedate']));
						
						$checksql2	= "SELECT price FROM ".$Prefix."inventory_date_price_linker WHERE 
						year		=:year 
						and
						month		=:month 
						and
						clientid	=:clientid 
						and
						inventoryid	=:inventoryid
						and
						date		=:date";

						$checkesql2	= array(
							"clientid"		=>(int)$_POST['clientid'],
							"inventoryid"	=>(int)$id,
							'month'			=>(int)$month,
							'year'			=>(int)$year,
							'date'			=>(int)$date	
						);

						$checkquery2	= pdo_query($checksql2,$checkesql2);
						$checknum2		= pdo_num_rows($checkquery2);

						if($checknum2 > 0)
						{
							$checkrows2			= pdo_fetch_assoc($checkquery2);
							$purchaserate		= $checkrows2['price'];
							$haspurchaserate	= true;
						}

						$CheckSql	= "SELECT * FROM ".$Prefix."purchase WHERE clientid=:clientid AND purchasedate=:purchasedate AND inventoryid=:inventoryid AND droppingpointid=:droppingpointid";
						$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"purchasedate"=>strtotime($_POST['purchasedate']),"inventoryid"=>(int)$id,"droppingpointid"=>(int)$_POST['droppingpointid']);

						$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
						$CheckNum	= pdo_num_rows($CheckQuery);

						$noofpices	= "";

						if($CheckNum > 0)
						{
							$CheckRows	= pdo_fetch_assoc($CheckQuery);
							$noofpices	= $CheckRows['noofpices'];

							$hasnoofpices	= true;
						}
					}

					$InventoryListArr[$index]['id']					= (int)$id;
					$InventoryListArr[$index]['name']				= $name;
					$InventoryListArr[$index]['categoryid']			= (int)$categoryid;
					$InventoryListArr[$index]['isassigned']			= $inventorystatus;
					$InventoryListArr[$index]['price']				= (float)$inventoryprice;
					$InventoryListArr[$index]['type']				= (int)$type;
					$InventoryListArr[$index]['hasnoofpices']		= $hasnoofpices;
					$InventoryListArr[$index]['numberofpieces']		= $noofpices;
					$InventoryListArr[$index]['purchaserate']		= $purchaserate;
					$InventoryListArr[$index]['haspurchaserate']	= $haspurchaserate;

					$index++;
				}
			}
		}
	}
	
	if(!empty($InventoryListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Stock listed successfully.";
	}
	$response['inventorylist']	= $InventoryListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllClientStock")
{
	$index			= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch stock detail.";

	$condition		= "";
	$CategoryEsql	= array("status"=>1);

	$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE clientid=:clientid AND deletedon <:deletedon ORDER BY name ASC";
	$InventoryEsql	= array('deletedon'=>1,'clientid'=>(int)$_POST['clientid']);

	$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
	$InventoryNum	= pdo_num_rows($InventoryQuery);

	if($InventoryNum > 0)
	{
		$AllCategoryName = GetAllCategory();
		while($rows = pdo_fetch_assoc($InventoryQuery))
		{
			$id			= $rows['id'];
			$categoryid	= $rows['categoryid'];
			$frequencyid= $rows['frequency'];
			$name		= $rows['name'];
			$status		= $rows['status'];
			$shortcode	= $rows['shortcode'];
			
			$statustxt = "In-Active";

			if($status > 0)
			{
				$statustxt = "Active";
			}
			
			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['categoryname']	= $AllCategoryName[$categoryid];
			$RecordListArr[$index]['frequencyname'] = $FrequencyArr[$frequencyid];
			$RecordListArr[$index]['status']		= $statustxt;
			$RecordListArr[$index]['shortcode']		= $shortcode;
		
			$index++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Stock listed successfully.";
	}
	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetStockDetail")
{
	$index			= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch stock detail.";

	$condition		= "";
	$CategoryEsql	= array("status"=>1);

	$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE clientid=:clientid AND deletedon <:deletedon AND id=:id ORDER BY name ASC";
	$InventoryEsql	= array('deletedon'=>1,'clientid'=>(int)$_POST['clientid'],'id'=>(int)$_POST['recordid']);

	$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
	$InventoryNum	= pdo_num_rows($InventoryQuery);

	if($InventoryNum > 0)
	{
		$AllCategoryName = GetAllCategory();
		while($rows = pdo_fetch_assoc($InventoryQuery))
		{
			$id			= $rows['id'];
			$categoryid	= $rows['categoryid'];
			$frequencyid= $rows['frequency'];
			$name		= $rows['name'];
			$status		= $rows['status'];
			$shortcode	= $rows['shortcode'];
			
			
			$RecordListArr['id']			= (int)$id;
			$RecordListArr['name']			= $name;
			$RecordListArr['categoryid']	= $categoryid;
			$RecordListArr['frequencyid']	= $frequencyid;
			$RecordListArr['categoryname']	= $AllCategoryName[$categoryid];
			$RecordListArr['frequencyname'] = $FrequencyArr[$frequencyid];
			$RecordListArr['status']		= $status;
			$RecordListArr['shortcode']		= $shortcode;
		
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Stock details fetched successfully.";
	}
	$response['detail']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAvailableAgentInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= strtotime('today');

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent stock detail.";

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.deletedon<:deleletedon AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"],"deleletedon"=>1);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			$index	= 0;
			$idarray			= array();
			$categoryidarray	= array();
			$namearray			= array();
			$pricearray			= array();
			$frequency			= array();
			
			if($InventoryNum > 0)
			{
				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id			= $rows['id'];
					$categoryid	= $rows['categoryid'];
					$name		= $rows['name'];
					$price		= $rows['price'];
					$frequency	= $rows['frequency'];

					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$inventoryprice		= $ClientInventoryData[$id]['price'];
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$namearray[]		= $name;
							$pricearray[]		= (float)$inventoryprice;
							$frequencyarray[]	= (int)$frequency;
						}
					}
				}
			}	
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid ORDER BY inv.name ASC";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,'status'=>0);

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2	= pdo_num_rows($InventoryQuery2);
			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id			= $rows2['id'];
					$categoryid	= $rows2['categoryid'];
					$name		= $rows2['name'];
					$price		= $rows2['price'];
					$frequency	= $rows2['frequency'];

					$idarray[]			= (int)$id;
					$categoryidarray[]	= (int)$categoryid;
					$namearray[]		= $name;
					$pricearray[]		= (float)$price;
					$frequencyarray[]	= (int)$frequency;
				}
			}
		if($InventoryNum2 > 0 || $InventoryNum > 0)
		{	
			$sortnamearray = array_map("strtolower",$namearray);

			array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$pricearray,$frequencyarray);
				
				$index	= 0;

				foreach($namearray as $key => $value)
				{
					$id			= $idarray[$key];
					$categoryid	= $categoryidarray[$key];
					$name		= $value;
					$price		= $pricearray[$key];
					$frequency	= $frequencyarray[$key];

					$InventoryListArr[$index]['id']					= (int)$id;
					$InventoryListArr[$index]['name']				= $name;
					$InventoryListArr[$index]['quantity']			= 1;
					$InventoryListArr[$index]['categoryid']			= (int)$categoryid;
					$InventoryListArr[$index]['frequency']			= (int)$frequency;
					$InventoryListArr[$index]['price']				= (float)$price;
					$InventoryListArr[$index]['isassigned']			= false;
					$InventoryListArr[$index]['subscriptiondate']	= date("Y-m-d");
					if($_POST['fromarea'] == 'addcustomer')
					{
						$InventoryListArr[$index]['subscriptiondate']	= date("Y-m-01");
					}
					$InventoryListArr[$index]['days']				= $DaysListArr;

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
		$response['msg']		= "Available stock listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "SaveInventoryPricing")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to save stock pricing.";
	$issuccess				= false;

	if($_POST['type'] == "savedateprice")
	{
		$_POST["month"]	= date("m",strtotime($_POST['pricedate']));
		$_POST["year"]	= date("Y",strtotime($_POST['pricedate']));
	}

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year AND status < :status AND isprocessing =:isprocessing";
	$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year'],'isprocessing'=>1,'status'=>1);

	$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$response['success']	= false;
		$response['msg']		= "Unable to save pricing as invoice(s) generation is already in process for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'];
		$issuccess				= false;
	}
	else
	{
		if(!empty($_POST['inventorylist']))
		{
			foreach($_POST['inventorylist'] as $inventorylistkey=>$inventorylistvalue)
			{
				if(!empty($inventorylistvalue['recordlist']))
				{
					foreach($inventorylistvalue['recordlist'] as $recordlist=>$recordlistrows)
					{
						$inventorylinkerid	= "";

						$inventoryid	= $recordlistrows['id'];
						$categoryid		= $recordlistrows['categoryid'];
						$name			= $recordlistrows['name'];
						$days			= $recordlistrows['days'];
						$price			= $recordlistrows['price'];
						$pricingtype	= $recordlistrows['pricingtype'];

						if($pricingtype > 0)
						{
							$days	= "";
							$price	= "";
						}

						$CheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
						year		=:year 
						AND
						month		=:month 
						AND
						clientid	=:clientid 
						AND
						inventoryid	=:inventoryid";

						$CheckEsql	= array(
							"year"			=>(int)$_POST['year'],
							"month"			=>(int)$_POST['month'],
							"clientid"		=>(int)$_POST['clientid'],
							"inventoryid"	=>(int)$inventoryid
						);

						$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
						$CheckNum	= pdo_num_rows($CheckQuery);

						if($CheckNum > 0)
						{
							$checkrows	= pdo_fetch_assoc($CheckQuery);
							$inventorylinkerid	= $checkrows['id'];
							
							$Sql	= "UPDATE ".$Prefix."inventory_days_price_linker SET 
							year		=:year,
							month		=:month,
							clientid	=:clientid,
							inventoryid	=:inventoryid,
							categoryid	=:categoryid,
							name		=:name,
							days		=:days,
							price		=:price,
							pricingtype	=:pricingtype
							WHERE
							id			=:id";

							$Esql	= array(
								"year"			=>(int)$_POST['year'],
								"month"			=>(int)$_POST['month'],
								"clientid"		=>(int)$_POST['clientid'],
								"inventoryid"	=>(int)$inventoryid,
								"categoryid"	=>(int)$categoryid,
								"name"			=>$name,
								"days"			=>(int)$days,
								"price"			=>(float)$price,
								"pricingtype"	=>(int)$pricingtype,
								"id"			=>(int)$inventorylinkerid
							);

							$Query	= pdo_query($Sql,$Esql);

							if($Query)
							{
								$issuccess	= true;
							}
						}
						else
						{
							$Sql	= "INSERT INTO ".$Prefix."inventory_days_price_linker SET 
							year		=:year,
							month		=:month,
							clientid	=:clientid,
							inventoryid	=:inventoryid,
							categoryid	=:categoryid,
							name		=:name,
							days		=:days,
							price		=:price,
							pricingtype	=:pricingtype,
							createdon	=:createdon";

							$Esql	= array(
								"year"			=>(int)$_POST['year'],
								"month"			=>(int)$_POST['month'],
								"clientid"		=>(int)$_POST['clientid'],
								"inventoryid"	=>(int)$inventoryid,
								"categoryid"	=>(int)$categoryid,
								"name"			=>$name,
								"days"			=>(int)$days,
								"price"			=>(float)$price,
								"pricingtype"	=>(int)$pricingtype,
								"createdon"		=>$createdon
							);

							$Query	= pdo_query($Sql,$Esql);

							if($Query)
							{
								$inventorylinkerid	= pdo_insert_id();

								$issuccess	= true;
							}
						}

						if($pricingtype > 0)
						{
							$totalpricingdays	= 0;
							$totalpricing		= 0;

							if(!empty($recordlistrows['datepricing']))
							{
								foreach($recordlistrows['datepricing'] as $pricinglist=>$pricingrows)
								{
									$date		= $pricingrows['date'];
									$dateprice	= $pricingrows['dateprice'];

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
										if($CheckNum2 > 0)
										{
											$checkrows2	= pdo_fetch_assoc($CheckQuery2);
											$checkid2	= $checkrows2['id'];

											$Sql2	= "UPDATE ".$Prefix."inventory_date_price_linker SET 
											year		=:year,
											month		=:month,
											clientid	=:clientid,
											inventoryid	=:inventoryid,
											categoryid	=:categoryid,
											name		=:name,
											date		=:date,
											price		=:price
											WHERE
											id			=:id";

											$Esql2	= array(
												"year"			=>(int)$_POST['year'],
												"month"			=>(int)$_POST['month'],
												"clientid"		=>(int)$_POST['clientid'],
												"inventoryid"	=>(int)$inventoryid,
												"categoryid"	=>(int)$categoryid,
												"name"			=>$name,
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
										if($dateprice > 0)
										{
											$totalpricingdays	+= 1;
										}
										else
										{
											/*$checkmonth = $_POST['month'];
											$checkyear = $_POST['year'];
											$checkdate = $date;
											if($_POST['month'] < 10)
											{
												$checkmonth = "0".$_POST['month'];
											}
											if($checkdate < 10)
											{
												$checkdate = "0".$checkdate;
											}
											
											if(IsClientHoliday($checkholidaydate,$_POST['clientid'],$inventoryid) > 0) 
											{
												echo date("r",$checkholidaydate);
												$totalpricingdays	+= 1;
											}*/
										}
										$totalpricing		+= (float)$dateprice;
									}
									else
									{
										if($CheckNum2 > 0)
										{
											$checkrows2	= pdo_fetch_assoc($CheckQuery2);
											$checkid2	= $checkrows2['id'];

											$DelSql2	= "DELETE FROM ".$Prefix."inventory_date_price_linker WHERE id=:id";
											$DelEsql2	= array("id"=>(int)$checkid2);

											$DelQuery2	= pdo_query($DelSql2,$DelEsql2);
										}
									}
								}
							}

							/*$iscompleted	= 0;

							if(@count($recordlistrows['datepricing']) == $totalpricingdays)
							{
								$iscompleted	= 1;
							}

							if($_POST['type'] != "savedateprice")
							{
								$UpdateInventoryDaysSql		= "UPDATE ".$Prefix."inventory_days_price_linker SET days=:days,price=:price WHERE id=:id";
								$UpdateInventoryDaysEsql	= array("days"=>(int)$totalpricingdays,"price"=>(float)$totalpricing,"id"=>(int)$inventorylinkerid);

								$UpdateInventoryDaysQuery	= pdo_query($UpdateInventoryDaysSql,$UpdateInventoryDaysEsql);
							}*/
						}
						else
						{
							$DelSql3		= "DELETE FROM ".$Prefix."inventory_date_price_linker 
							WHERE 
							year		=:year 
							AND
							month		=:month 
							AND
							clientid	=:clientid 
							AND
							inventoryid	=:inventoryid";
							
							$DelEsql3	= array(
								"year"			=>(int)$_POST['year'],
								"month"			=>(int)$_POST['month'],
								"clientid"		=>(int)$_POST['clientid'],
								"inventoryid"	=>(int)$inventoryid
							);
							$DelQuery3	= pdo_query($DelSql3,$DelEsql3);
						}

						syncInventoryPriceTotal($_POST['clientid'], $inventoryid, $_POST['month'], $_POST['year'], 1);
					}
				}
			}
		}
		if($issuccess)
		{
			$response['success']	= true;
			$response['msg']		= "Stock pricing save sucessfully.";
		}
	}
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAvailableInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$TotalRec	= 0;

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent stock detail.";

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];
			$cattype	= $catrows['type'];
		
			$frequencyindex = 0;
			$FrequencyListArr = array();
			foreach($FrequencyArr as $frequencyid => $frequencyname)
			{
				$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid AND inv.frequency=:frequencyid GROUP BY rel.inventoryid ORDER BY inv.frequency ASC,inv.name ASC";
				$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"],'frequencyid'=>(int)$frequencyid);

				$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
				$InventoryNum	= pdo_num_rows($InventoryQuery);

				$index	= 0;
				$idarray			= array();
				$categoryidarray	= array();
				$namearray			= array();
				$pricearray			= array();
				$InventoryListArr	= array();
				$frequencyarray		= array();
				$isassignedarray	= array();

				if($InventoryNum > 0)
				{
					while($rows = pdo_fetch_assoc($InventoryQuery))
					{
						$id			= $rows['id'];
						$categoryid	= $rows['categoryid'];
						$name		= $rows['name'];
						$price		= $rows['price'];
						$frequency	= $rows['frequency'];

						if(!empty($ClientInventoryData[$id]))
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];
						}
						else
						{
							$inventoryprice = $price;
							$inventorystatus = 0;
						}
						if($id > 0)
						{
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$namearray[]		= $name;
							$pricearray[]		= (float)$inventoryprice;
							$frequencyarray[]	= (int)$frequency;
							$isassignedarray[]	= (int)$inventorystatus;
						}
					}
				}
				
				$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid AND inv.frequency=:frequencyid ORDER BY inv.frequency ASC,inv.name ASC";
				$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,"status"=>0,'frequencyid'=>(int)$frequencyid);

				$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
				$InventoryNum2	= pdo_num_rows($InventoryQuery2);
				if($InventoryNum2 > 0)
				{
					while($rows2 = pdo_fetch_assoc($InventoryQuery2))
					{
						$id			= $rows2['id'];
						$categoryid	= $rows2['categoryid'];
						$name		= $rows2['name'];
						$price		= $rows2['price'];
						$frequency	= $rows2['frequency'];

						if(!empty($ClientInventoryData[$id]))
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];
						}
						else
						{
							$inventoryprice = $price;
							$inventorystatus = 0;
						}
						if($id > 0)
						{
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$namearray[]		= $name;
							$pricearray[]		= (float)$price;
							$frequencyarray[]	= (int)$frequency;
							$isassignedarray[]	= (int)$inventorystatus;
						}
					}	
				}
				if($InventoryNum2 > 0 || $InventoryNum > 0)
				{
					$sortnamearray = array_map("strtolower",$namearray);

					array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$pricearray,$frequencyarray,$isassignedarray);
						
					$index	= 0;

					foreach($namearray as $key => $value)
					{
						$id			= $idarray[$key];
						$categoryid	= $categoryidarray[$key];
						$name		= $value;
						$price		= $pricearray[$key];
						$frequency	= $frequencyarray[$key];
						$isassigned	= $isassignedarray[$key];
						
						if($frequency > 0)
						{
							$freqencyname	= $FrequencyArr[$frequency];
						}
						else
						{
							$freqencyname = 'None';
						}
						
						$InventoryListArr[$index]['id']					= (int)$id;
						$InventoryListArr[$index]['name']				= $name;
						$InventoryListArr[$index]['categoryid']			= (int)$categoryid;
						$InventoryListArr[$index]['frequency']			= (int)$frequency;
						$InventoryListArr[$index]['frequencyname']		= $freqencyname;
						$InventoryListArr[$index]['price']				= (float)$price;
						$InventoryListArr[$index]['isassigned']			= $isassigned;
						$InventoryListArr[$index]['subscriptiondate']	= date("Y-m-d");
						$InventoryListArr[$index]['days']				= $DaysListArr;

						$TotalRec++;

						$index++;
					}
					$FrequencyListArr[$frequencyindex]['name'] 		 = $frequencyname;
					$FrequencyListArr[$frequencyindex]['recordlist'] = $InventoryListArr;
					$frequencyindex++;
				}
					
			}

			$showfrequency = false;
			
			if((int)$cattype < 1)
			{
				$showfrequency = true;
			}
			$RecordListArr[$catindex]['id']			= (int)$catid;
			$RecordListArr[$catindex]['title']		= $cattitle;
			$RecordListArr[$catindex]['showfrequencyname']	= $showfrequency;
			$RecordListArr[$catindex]['frequencylist']	= $FrequencyListArr;

			$catindex++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Stock listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AssignInventory")
{
    $response['success']	= false;
    $response['msg']		= "Unable to assign Stock.";

	$unsubscribedate	= strtotime(date("d-M-Y"));

	$CheckSql	= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND cityid=:cityid AND stateid=:stateid AND inventoryid=:inventoryid";
	$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"cityid"=>(int)$_POST['cityid'],"stateid"=>(int)$_POST['stateid'],"inventoryid"=>(int)$_POST['inventoryid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$checkrows	= pdo_fetch_assoc($CheckQuery);

		$recordid	= $checkrows['id'];
		$status		= $checkrows['status'];

		$isassigned	= 1;

		if($status > 0)
		{
			$isassigned	= 0;
		}

		if($isassigned == 0)
		{
			if($_POST['isconfirm'] != "1")
			{
				$CheckSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."subscriptions WHERE inventoryid=:inventoryid AND customerid IN (SELECT id FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon)";
				$CheckEsql	= array("inventoryid"=>(int)$_POST['inventoryid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

				$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
				$CheckRows	= pdo_fetch_assoc($CheckQuery);
				$CheckNum	= $CheckRows['C'];

				if($CheckNum > 0)
				{
					$response['success']	= false;
					$response['showconfirm']= true;
					$response['subcount']	= $CheckNum;
					$response['msg']		= "Unable to update stock status.";

					$json = json_encode($response);
					echo $json;
					die;
				}
				else
				{
					$canupdatestatus	= true;
				}
			}
			else
			{
				$canupdatestatus	= true;
			}
		}
		else
		{
			$canupdatestatus	= true;
		}

		if($canupdatestatus)
		{
			if($isassigned == 0)
			{
				$inventoryid	= (int)$_POST['inventoryid'];

				$CheckSQL2	= "SELECT * FROM ".$Prefix."subscriptions WHERE inventoryid=:inventoryid AND customerid IN (SELECT id FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon) ORDER BY id DESC";
				$CheckESQL2	= array("inventoryid"=>(int)$inventoryid,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

				$CheckQuery2	= pdo_query($CheckSQL2,$CheckESQL2);
				$CheckNum2		= pdo_num_rows($CheckQuery2);

				if($CheckNum2 > 0)
				{
					while($CheckRow2 = pdo_fetch_assoc($CheckQuery2))
					{
						$customerid			= $CheckRow2['customerid'];
						$frequency			= $CheckRow2['frequency'];
						$subscriptiondate	= $CheckRow2['subscriptiondate'];
						$daysStr			= $CheckRow2['days'];
						$dayNameStr			= $CheckRow2['daysname'];
						$quantity			= $CheckRow2['quantity'];

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
				else
				{
					$isupdated	= 1;
				}
			}
			else
			{
				$isupdated	= 1;
			}

			if($isupdated)
			{
				$Sql	= "UPDATE ".$Prefix."client_inventory_linker SET status=:status WHERE id=:id";
				$Esql	= array("status"=>(int)$isassigned,"id"=>(int)$recordid);

				$Query	= pdo_query($Sql,$Esql);
			}
		}

		/*if($canupdatestatus)
		{
			$Sql	= "UPDATE ".$Prefix."client_inventory_linker SET status=:status WHERE id=:id";
			$Esql	= array("status"=>(int)$isassigned,"id"=>(int)$recordid);

			$Query	= pdo_query($Sql,$Esql);
		}*/

		if($Query)
		{
			$response['success']	= true;
			if($isassigned > 0)
			{
				$response['msg']	= 'Stock successfully assigned to your list';
			}
			else
			{
				$response['msg']	= 'Stock successfully removed from your list';
			}
		}
	}
	else
	{
		$isassigned	= 1;

		$Sql	= "INSERT INTO ".$Prefix."client_inventory_linker SET
		clientid	=:clientid,
		inventoryid	=:inventoryid,
		cityid		=:cityid,
		stateid		=:stateid,
		price		=:price,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"inventoryid"	=>(int)$_POST['inventoryid'],
			"cityid"		=>(int)$_POST['cityid'],
			"stateid"		=>(int)$_POST['stateid'],
			"price"			=>(float)$_POST['price'],
			"status"		=>(int)$isassigned,
			"createdon"		=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			if($isassigned > 0)
			{
				$response['msg']	= 'Stock successfully assigned to your list';
			}
			else
			{
				$response['msg']	= 'Stock successfully removed from your list';
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerInventorySummary")
{
	$totalsubscription	= 0;
	$TotalRec	= 0;

	if($_POST['inventoryid'] > 0)
	{
		$index			= 0;
		$RecordListArr	= array();

		$response['success']	= false;
		$response['msg']		= "Unable to fetch agent stock customer detail.";

		$CustomerNameArr	= array();

		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

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

		$InventoryEsql	= array("id"=>(int)$_POST['inventoryid']);

		$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE id=:id";

		$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
		$InventoryNum	= pdo_num_rows($InventoryQuery);

		if($InventoryNum > 0)
		{
			$inventoryrows	= pdo_fetch_assoc($InventoryQuery);

			$inventoryname	= $inventoryrows['name'];

			$CustomerSql	= "SELECT cust.*, sub.subscriptiondate AS subscriptiondate,sub.quantity AS quantity FROM ".$Prefix."subscriptions sub, ".$Prefix."customers cust WHERE cust.id=sub.customerid AND cust.deletedon < :deletedon AND sub.inventoryid=:inventoryid AND cust.id IN (".$CustomerIDStr.") ORDER BY cust.sequence ASC, cust.customerid ASC";
			$CustomerEsql	= array("inventoryid"=>(int)$_POST['inventoryid'],"deletedon"=>1);

			$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
			$CustomerNum	= pdo_num_rows($CustomerQuery);

			if($CustomerNum > 0)
			{
				$index	= 0;

				$RecordSetArr	= array();

				while($customerrows = pdo_fetch_assoc($CustomerQuery))
				{
					$customerid			= $customerrows['customerid'];
					$name				= $customerrows['name'];
					$subscriptiondate	= $customerrows['subscriptiondate'];

					$name2	= "#".$customerid." ".$name;

					/*$CreatedOnText	= date("d-M-Y h:i A",$subscriptiondate);*/
					$CreatedOnText		= date("d-M-Y",$subscriptiondate);

					$RecordSetArr[$index]['serialno']	= $index+1;
					$RecordSetArr[$index]['customerid']	= $customerid;
					$RecordSetArr[$index]['name']		= $name2;
					$RecordSetArr[$index]['date']		= $CreatedOnText;

					$index++;
				}
			}
		}
		$RecordListArr['inventoryname']		= $inventoryname;
		$RecordListArr['subscriptionlist']	= $RecordSetArr;

		if(!empty($RecordListArr))
		{
			$response['success']	= true;
			$response['msg']		= "stock customer detail listed successfully.";
		}
		$response['recordset']			= $RecordListArr;
		$response['hasdetail']			= true;
		$response['totalsubscription']	= $index;
	}
	else
	{
		$index			= 0;
		$RecordListArr	= array();

		$response['success']	= false;
		$response['msg']		= "Unable to fetch agent stock detail.";

		$CustomerIDArr		= array();
		$CustomerNameArr	= array();

		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$Condition." ORDER BY sequence ASC, customerid ASC";

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

		$condition		= "";
		$CategoryEsql	= array("status"=>1);

		if($_POST['cattype'] != "")
		{
			$CategoryEsql['type']	= (int)$_POST['cattype'];
			$condition	.= " AND type=:type";
		}

		$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$condition." ORDER BY orderby ASC";

		$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
		$CategoryNum	= pdo_num_rows($CategoryQuery);

		if($CategoryNum > 0)
		{
			$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

			$index	= 0;
			
			while($catrows = pdo_fetch_assoc($CategoryQuery))
			{
				$idarray			= array();
				$categoryidarray	= array();
				$namearray			= array();
				$pricearray			= array();
				$frequencyarray		= array();
				$catid				= $catrows['id'];
				$cattitle			= $catrows['title'];

				$InventoryCond	= "";
				$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

				if($_POST['inventoryid'] > 0)
				{
					$InventoryCond			.= " AND inv.id=:id";
					$InventoryEsql['id']	= (int)$_POST['inventoryid'];
				}

				$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid ".$InventoryCond." GROUP BY rel.inventoryid ORDER BY inv.name ASC";

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
							if($ClientInventoryData[$id]['status'] > 0)
							{
								$inventorystatus	= $ClientInventoryData[$id]['status'];
								$inventoryprice		= $ClientInventoryData[$id]['price'];
								
								$idarray[]			= (int)$id;
								$categoryidarray[]	= (int)$categoryid;
								$namearray[]		= $name;
								$pricearray[]		= (float)$inventoryprice;
							}
						}
					}
				}
				$InventoryCond	= "";
				$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_POST["clientid"],"deletedon"=>1,'status'=>0);

				if($_POST['inventoryid'] > 0)
				{
					$InventoryCond			.= " AND inv.id=:id";
					$InventoryEsql2['id']	= (int)$_POST['inventoryid'];
				}
				$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid $InventoryCond ORDER BY inv.name ASC";
				

				$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
				$InventoryNum2	= pdo_num_rows($InventoryQuery2);
				if($InventoryNum2 > 0)
				{
					while($rows2 = pdo_fetch_assoc($InventoryQuery2))
					{
						$id			= $rows2['id'];
						$categoryid	= $rows2['categoryid'];
						$name		= $rows2['name'];
						$price		= $rows2['price'];
						$frequency	= $rows2['frequency'];

						if(!empty($ClientInventoryData[$id]))
						{
							if($ClientInventoryData[$id]['status'] > 0)
							{
								$inventorystatus	= $ClientInventoryData[$id]['status'];
								$inventoryprice		= $ClientInventoryData[$id]['price'];
								
								$idarray[]			= (int)$id;
								$categoryidarray[]	= (int)$categoryid;
								$namearray[]		= $name;
								$pricearray[]		= (float)$inventoryprice;
							}
						}
					}
				}
				if(!empty($namearray))
				{
					$sortnamearray = array_map("strtolower",$namearray);

					array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray);
					foreach($namearray as $key => $value)
					{
						$id				= $idarray[$key];
						$categoryid		= $categoryidarray[$key];
						$name			= $value;
						$inventoryprice	= $pricearray[$key];
						
						if(!empty($ClientInventoryData[$id]))
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];

							$CheckSql	= "SELECT * FROM ".$Prefix."subscriptions WHERE inventoryid=:inventoryid AND customerid IN (".$CustomerIDStr.")";
							$CheckEsql	= array("inventoryid"=>(int)$id);

							$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
							$CheckNum	= pdo_num_rows($CheckQuery);

							if($CheckNum > 0)
							{
								$TempQty = 0;
								while($rowcheck = pdo_fetch_assoc($CheckQuery))
								{
									$qty = $rowcheck['quantity'];
									if($qty < 1)
									{
										$qty = 1;
									}
									$TempQty += $qty;
								}

								$totalsubscription	+= $TempQty;

								$RecordListArr[$index]['id']				= (int)$id;
								$RecordListArr[$index]['serialno']			= $index+1;
								$RecordListArr[$index]['name']				= $name;
								$RecordListArr[$index]['categoryid']		= (int)$categoryid;
								$RecordListArr[$index]['isassigned']		= $inventorystatus;
								$RecordListArr[$index]['price']				= (float)$inventoryprice;
								$RecordListArr[$index]['totalinventory']	= (int)$TempQty;

								$TotalRec++;

								$index++;
							}
						}
					}	
				}
			}
		}
		if(!empty($RecordListArr))
		{
			$response['success']	= true;
			$response['msg']		= "Stock listed successfully.";
		}
		$response['inventorylist']		= $RecordListArr;
		$response['totalsubscription']	= $totalsubscription;
		$response['totalrecord']		= "".$TotalRec."";
		$response['hasdetail']			= false;
	}
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerInventorySummaryPDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "Unable to generate customer stock summary pdf.";

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/report/");
	}
	@mkdir("../assets/".$_POST['clientid']."/report/", 0777, true);

	if($_POST['cattype'] < 1)
	{
		$Pdf_FileName 	= "magazine-subscription-summary.pdf";
	}
	else
	{
		$Pdf_FileName 	= "newspaper-subscription-summary.pdf";
	}

	if($_POST['inventoryid'] > 0)
	{
		$Pdf_FileName 	= "customer-list-by-newspaper.pdf";
	}

	$startdate	= strtotime('today');
	$enddate	= strtotime($_POST['circulationdate']);

	/*$File	= "viewinventorysummary.php?clientid=".$_POST['clientid']."&lineid=".$_POST['lineid']."&linemanid=".$_POST['linemanid']."&hawkerid=".$_POST['hawkerid']."&areaid=".$_POST['areaid']."&cattype=".$_POST['cattype']."&stateid=".$_POST['stateid']."&cityid=".$_POST['cityid']."&inventoryid=".$_POST['inventoryid']."&bulkprinting=1&downloadpdf=1";*/

	$File	= "viewinventorysummary.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/report/".$Pdf_FileName,"");

	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerAPIURL."../assets/".$_POST['clientid']."/report/".$Pdf_FileName;
		$Response['msg']			= "customer stock summary pdf successfully generated.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "InventoryDetail")
{
	$index			= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent stock customer detail.";

	$CustomerNameArr	= array();

	$Condition		= "";

	$CustomerEsql	= array("inventoryid"=>(int)$_POST["recordid"],"deletedon"=>1,"clientid"=>(int)$_POST['clientid']);

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$CustomerEsql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$CustomerEsql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$CustomerEsql['hawkerid']	= (int)$_POST['hawkerid'];
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

	$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE id=:id";
	$InventoryEsql	= array("id"=>(int)$_POST["recordid"]);

	$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
	$InventoryNum	= pdo_num_rows($InventoryQuery);

	if($InventoryNum > 0)
	{
		$AllAreaNames	= GetAllArea($_POST['clientid']);
		$AllLines		= GetAllLine($_POST['clientid']);

		$inventoryrows	= pdo_fetch_assoc($InventoryQuery);

		$inventoryname	= $inventoryrows['name'];

		$CustomerSql	= "SELECT cust.*, sub.subscriptiondate AS subscriptiondate FROM ".$Prefix."subscriptions sub, ".$Prefix."customers cust WHERE cust.id=sub.customerid AND sub.inventoryid=:inventoryid AND cust.deletedon < :deletedon AND cust.clientid=:clientid ".$Condition." ORDER BY cust.sequence ASC, cust.customerid ASC";

		$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
		$CustomerNum	= pdo_num_rows($CustomerQuery);

		if($CustomerNum > 0)
		{
			$index	= 0;

			$RecordSetArr	= array();

			$AllSubLine	= GetAllSubLine($_POST['clientid']);

			while($customerrows = pdo_fetch_assoc($CustomerQuery))
			{
				$customerid			= $customerrows['customerid'];
				$name				= $customerrows['name'];
				$areid				= $customerrows['areaid'];
				$lineid				= $customerrows['lineid'];
				$subscriptiondate	= $customerrows['subscriptiondate'];
				$housenumber		= $customerrows['housenumber'];
				$floor				= $customerrows['floor'];
				$address1			= $customerrows['address1'];
				$sublinename		= $AllSubLine[$customerrows['sublineid']]['name'];

				$areaname	= $AllAreaNames[$areid]['name'];
				$linename	= $AllLines[$lineid]['name'];
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

				$CreatedOnText		= date("d-M-Y",$subscriptiondate);

				$RecordSetArr[$index]['serialno']	= $index+1;
				$RecordSetArr[$index]['customerid']	= $customerid;
				$RecordSetArr[$index]['name']		= $name2;
				$RecordSetArr[$index]['date']		= $CreatedOnText;
				$RecordSetArr[$index]['address']	= $addresstr;
				$RecordSetArr[$index]['area']	= $areaname;
				$RecordSetArr[$index]['line']	= $linename;

				$index++;
			}
		}
	}
	$RecordListArr['inventoryname']		= $inventoryname;
	$RecordListArr['subscriptionlist']	= $RecordSetArr;
	$RecordListArr['totalcustomer']		= $CustomerNum;

	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "stock customer detail listed successfully.";
	}
	$response['recordset']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetStockCategory')
{
	$index			= 0;
	$RecordListArr	= array();

	$response['success']	= false;
	$response['msg']		= "Unable to fetch stock categories.";

	$condition		= "";
	$CategoryEsql	= array("status"=>1,"deletedon"=>1);

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status and deletedon < :deletedon ORDER BY orderby ASC";

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];
		
			$RecordListArr[$index]['id']			= (int)$catid;
			$RecordListArr[$index]['name']			= $cattitle;
			$index++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Stock category listed successfully.";
	}
	$response['recordlist']	= $RecordListArr;

	$json = json_encode($response);
	echo $json;
	die;
	
}

if($_POST['Mode'] == 'GetStockFrequency')
{
	$index			= 0;
	$RecordListArr	= array();

	$response['success']	= false;
	$response['msg']		= "Unable to fetch stock frequency.";

	$condition		= "";
	$CategoryEsql	= array("status"=>1,"deletedon"=>1);

	if(!empty($FrequencyArr))
	{
		foreach($FrequencyArr as $key => $value)
		{
			$catid		= $key;
			$cattitle	= $value;
		
			$RecordListArr[$index]['id']			= (int)$catid;
			$RecordListArr[$index]['name']			= $cattitle;
			$index++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Stock category listed successfully.";
	}
	$response['recordlist']	= $RecordListArr;

	$json = json_encode($response);
	echo $json;
	die;
	
}
if($_POST['Mode'] == "AddStock")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add stock.";

	if($_POST['name'] !='')
	{
		$checkSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."inventory WHERE name=:name AND categoryid=:categoryid AND deletedon < :deletedon  AND (clientid < :clientid || clientid =:clientid2)";
		$checkEsql	= array("name"=>$_POST['name'],'categoryid'=>(int)$_POST['categoryid'],'deletedon'=> 1,"clientid"=>1,"clientid2"=>(int)$_POST['clientid']);

		$checkQuery	= pdo_query($checkSql,$checkEsql);
		$checkRows	= pdo_fetch_assoc($checkQuery);

		$checkCount = $checkRows['C'];
		if($checkCount > 0)
		{
			$ErrorMsg .= "Record with the same name for the selected category already exist.";
			
			$haserror	= true;
			$response['success']	= false;
			$response['msg']		= $ErrorMsg;
			$response['toastmsg']	= $ErrorMsg;
		}
	}
	if($ErrorMsg !='')
	{
		if(trim($_POST['shortcode']) == '')
		{
			$ErrorMsg .= "Please enter the short code.";
			
		}
		else
		{
			$checkSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."inventory WHERE shortcode=:shortcode AND deletedon < :deletedon  AND (clientid < :clientid || clientid =:clientid2)";
			$checkEsql	= array("shortcode"=>trim($_POST['shortcode']),'deletedon'=> 1,"clientid"=>1,"clientid2"=>(int)$_POST['clientid']);

			$checkQuery	= pdo_query($checkSql,$checkEsql);
			$checkRows	= pdo_fetch_assoc($checkQuery);

			$checkCount = $checkRows['C'];
			if($checkCount > 0)
			{
				$ErrorMsg .= "stock with the same short code already exist.";
				
				$haserror	= true;
				$response['success']	= false;
				$response['msg']		= $ErrorMsg;
				$response['toastmsg']	= $ErrorMsg;
			}
		}
	}
	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."inventory SET 
		clientid   =:clientid,
		categoryid =:categoryid,
		name	   =:name,
		shortcode  =:shortcode,
		status	   =:status,
		frequency  =:frequency,
		createdon  =:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"categoryid"=>(int)$_POST['categoryid'],
			"frequency"	=>$_POST['frequencyid'],
			"name"		=>$_POST['name'],
			"shortcode"	=>$_POST['shortcode'],
			"status"	=>1,
			"createdon"	=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$isassigned	= 1;

			$Sql	= "INSERT INTO ".$Prefix."client_inventory_linker SET
			clientid	=:clientid,
			inventoryid	=:inventoryid,
			cityid		=:cityid,
			stateid		=:stateid,
			status		=:status,
			createdon	=:createdon";

			$Esql	= array(
				"clientid"		=>(int)$_POST['clientid'],
				"inventoryid"	=>(int)$recordid,
				"cityid"		=>(int)$_POST['cityid'],
				"stateid"		=>(int)$_POST['stateid'],
				"status"		=>(int)$isassigned,
				"createdon"		=>time()
			);

			$Query	= pdo_query($Sql,$Esql);

			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];
			$response['msg']		= "Stock added successfully added.";
			$response['toastmsg']	= "Stock added successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditStock")
{
	$haserror	= false;
    $response['success']	= false;
	$response['msg']		= "Unable to add stock.";
	
	$InventorySql	= "SELECT * FROM ".$Prefix."inventory WHERE clientid=:clientid AND deletedon <:deletedon AND id=:id ORDER BY name ASC";
	$InventoryEsql	= array('deletedon'=>1,'clientid'=>(int)$_POST['clientid'],'id'=>(int)$_POST['recordid']);

	$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
	$InventoryNum	= pdo_num_rows($InventoryQuery);
	
	if($InventoryNum > 0)
	{
		$prerow				= pdo_fetch_assoc($InventoryQuery);
		$previousname 		= $prerow['name'];
		$previousshortcode 	= $prerow['stockcode'];
	}
	if($_POST['name'] !='' AND trim(strtolower($previousname)) != trim(strtolower($_POST['name'])))
	{
		$checkSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."inventory WHERE name=:name AND categoryid=:categoryid AND deletedon < :deletedon AND (clientid < :clientid || clientid =:clientid2) AND id<>:id";
		$checkEsql	= array("name"=>$_POST['name'],'categoryid'=>(int)$_POST['categoryid'],'deletedon'=> 1,"clientid"=>1,"clientid2"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid']);

		$checkQuery	= pdo_query($checkSql,$checkEsql);
		$checkRows	= pdo_fetch_assoc($checkQuery);

		$checkCount = $checkRows['C'];
		if($checkCount > 0)
		{
			$ErrorMsg .= "Record with the same name for the selected category already exist.";
			
			$haserror	= true;
			$response['success']	= false;
			$response['msg']		= $ErrorMsg;
			$response['toastmsg']	= $ErrorMsg;
		}
	}
	if($ErrorMsg !='')
	{
		if(trim($_POST['shortcode']) == '')
		{
			$ErrorMsg .= "Please enter the short code.";
			
		}
		else if( trim(strtolower($previousshortcode)) != trim(strtolower($_POST['shortcode'])) )
		{
			$checkSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."inventory WHERE shortcode=:shortcode AND deletedon < :deletedon  AND (clientid < :clientid || clientid =:clientid2) AND id<>:id";
			$checkEsql	= array("shortcode"=>trim($_POST['shortcode']),'deletedon'=> 1,"clientid"=>1,"clientid2"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid']);

			$checkQuery	= pdo_query($checkSql,$checkEsql);
			$checkRows	= pdo_fetch_assoc($checkQuery);

			$checkCount = $checkRows['C'];
			if($checkCount > 0)
			{
				$ErrorMsg .= "stock with the same short code already exist.";
				
				$haserror	= true;
				$response['success']	= false;
				$response['msg']		= $ErrorMsg;
				$response['toastmsg']	= $ErrorMsg;
			}
		}
	}
	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."inventory SET 
		clientid   =:clientid,
		categoryid =:categoryid,
		name	   =:name,
		shortcode  =:shortcode,
		status	   =:status,
		frequency  =:frequency
		WHERE
		id			=:id
		";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"categoryid"=>(int)$_POST['categoryid'],
			"frequency"	=>$_POST['frequencyid'],
			"name"		=>$_POST['name'],
			"shortcode"	=>$_POST['shortcode'],
			"status"	=>(int)1,
			"id"		=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= $_POST['recordid'];

			$daysStr	= "::1::2::3::4::5::6::7::";
			$dayNameStr	= "::Mon::Tue::Wed::Thu::Fri::Sat::Sun::";

			if($_POST['frequencyid'] != 1)
			{
				$daysStr	= "";
				$dayNameStr	= "";
			}

			$UpdateSubscriptionSql	= "UPDATE ".$Prefix."subscriptions SET 
			frequency			=:frequency,
			days				=:days,
			daysname			=:daysname
			WHERE
			inventoryid			=:inventoryid";

			$UpdateSubscriptionEsql	= array(
				"frequency"			=>(int)$_POST['frequencyid'],
				"days"				=>$daysStr,
				"daysname"			=>$dayNameStr,
				"inventoryid"		=>(int)$recordid
			);

			$UpdateQuery	= pdo_query($UpdateSubscriptionSql,$UpdateSubscriptionEsql);

			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];
			$response['msg']		= "Stock edited successfully added.";
			$response['toastmsg']	= "Stock edited successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteStock")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete stock, Please try later.";

	$DelSql		= "UPDATE ".$Prefix."inventory SET 
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
		$Response['msg']		= "Stock deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}