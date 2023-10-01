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

if($_POST['Mode'] == "AddSale")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add purchase.";

	$isrecordadded	= false;

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
	}

	$salerate	= $_POST['salerate'];

	/*print_r($_POST);
	die;*/

	if($haserror == false)
	{
		if(!empty($_POST['customerlist']))
		{
			foreach($_POST['customerlist'] as $key=>$val)
			{
				if($salerate > 0 && $val['stockqty'] > 0)
				{
					$delsql = "DELETE FROM ".$Prefix."sale WHERE customerid=:customerid AND inventoryid=:inventoryid AND saledate=:saledate";
					$delesql= array("customerid"=>(int)$val['id'],"inventoryid"=>(int)$_POST['inventoryid'],"saledate"=>(int)strtotime($_POST['saledate']));
					
					pdo_query($delsql,$delesql);

					$Sql	= "INSERT INTO ".$Prefix."sale SET 
					clientid		=:clientid,
					saledate		=:saledate,
					categoryid		=:categoryid,
					inventoryid		=:inventoryid,
					areaid			=:areaid,
					lineid			=:lineid,
					hawkerid		=:hawkerid,
					customerid		=:customerid,
					salerate		=:salerate,
					noofpices		=:noofpices,
					createdon		=:createdon";

					$Esql	= array(
						"clientid"		=>(int)$_POST['clientid'],
						"saledate"		=>strtotime($_POST['saledate']),
						"categoryid"	=>(int)$_POST['catid'],
						"inventoryid"	=>(int)$_POST['inventoryid'],
						"areaid"		=>(int)$val['areaid'],
						"lineid"		=>(int)$val['lineid'],
						"hawkerid"		=>(int)$val['hawkerid'],
						"customerid"	=>(int)$val['id'],
						"salerate"		=>(float)$salerate,
						"noofpices"		=>(int)$val['stockqty'],
						"createdon"		=>$createdon,
					);

					$Query	= pdo_query($Sql,$Esql);

					if($Query)
					{
						$isrecordadded	= true;
					}
				}
			}
		}

		if($isrecordadded)
		{
			/*$recordid	= pdo_insert_id();
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];*/

			$response['success']	= true;
			$response['msg']		= "Sale added successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditSale")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to edit purchase.";

	$isrecordadded	= false;

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
	}

	if($haserror == false)
	{
		if(!empty($_POST['customerlist']))
		{
			foreach($_POST['customerlist'] as $key=>$val)
			{
				if($val['id'] > 0)
				{
					if((float)$val['salerate'] < 0.1 || (float)$val['stockqty'] < 0.1)
					{
						$delsql = "DELETE FROM ".$Prefix."sale WHERE id=:id";
						$delesql= array("id"=>(int)$_POST['saleid']);
						pdo_query($delsql,$delesql);

					}
					else 
					{
						$Sql	= "UPDATE ".$Prefix."sale SET 
						saledate		=:saledate,
						salerate		=:salerate,
						noofpices		=:noofpices
						WHERE 	id		=:saleid";

						$Esql	= array(
							"saledate"		=>strtotime($_POST['saledate']),
							"salerate"		=>(float)$val['salerate'],
							"noofpices"		=>(int)$val['stockqty'],
							"saleid"		=>(int)$val['saleid']
						);
						$Query	= pdo_query($Sql,$Esql);

						if($Query)
						{
							$isrecordadded	= true;
						}
					}
				}
			}
		}

		if($isrecordadded)
		{
			/*$recordid	= pdo_insert_id();
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];*/

			$response['success']	= true;
			$response['msg']		= "Sale updated successfully.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllSale")
{
	$RecordListIndex	= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch sale list.";

	$SaleDateArr	= array();

	$condtion	= "";

	$Esql	= array("clientid"=>(int)$_POST["clientid"]);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$condtion	.= " AND (saledate BETWEEN :startdate AND :enddate)";
		$Esql['startdate']	= strtotime($_POST['startdate']);
		$Esql['enddate']	= strtotime($_POST['enddate']);
	}

	$Sql	= "SELECT DISTINCT(saledate) FROM ".$Prefix."sale WHERE clientid=:clientid ".$condtion." ORDER BY saledate DESC LIMIT 2";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$saledate	= $rows['saledate'];

			$SaleDateArr[$saledate]	= $saledate;
		}
	}

	$TotalRec	= 0;

	if(!empty($SaleDateArr))
	{
		foreach($SaleDateArr as $key=>$value)
		{
			$grandtotal	= 0;

			$index	= 0;
			$RecordSetArr	= array();

			$PurchaseSql	= "SELECT inv.*,sale.noofpices AS qty,sale.salerate AS salerate,sale.customerid AS customerid FROM ".$Prefix."sale sale,".$Prefix."inventory inv WHERE inv.id=sale.inventoryid AND sale.clientid=:clientid AND sale.saledate=:saledate GROUP BY sale.id ORDER BY sale.saledate DESC";
			$PurchaseEsql	= array("clientid"=>(int)$_POST["clientid"],"saledate"=>$value);

			$PurchaseQuery	= pdo_query($PurchaseSql,$PurchaseEsql);
			$PurchaseNum	= pdo_num_rows($PurchaseQuery);

			$TotalRec	+= $PurchaseNum;

			if($PurchaseNum > 0)
			{
				while($SaleRows = pdo_fetch_assoc($PurchaseQuery))
				{
					$linetotal	= 0;
					$name		= $SaleRows['name'];
					$qty		= $SaleRows['qty'];
					$salerate	= $SaleRows['salerate'];
					$customerid	= $SaleRows['customerid'];

					$CustomerInfoArr	= GetCustomerDetail($customerid);

					$linetotal	= (int)$qty*(float)$salerate;

					$grandtotal	+= $linetotal;

					$RecordSetArr[$index]['serial']		= $index+1;
					$RecordSetArr[$index]['name']		= $name;
					$RecordSetArr[$index]['qty']		= $qty;
					$RecordSetArr[$index]['salerate']	= "--";
					$RecordSetArr[$index]['linetotal']	= number_format($linetotal,2);
					$RecordSetArr[$index]['customer']	= $CustomerInfoArr['name'];

					if($salerate > 0)
					{
						$RecordSetArr[$index]['salerate']	= number_format($salerate,2);
					}

					$index++;
				}

				$RecordListArr[$RecordListIndex]['index']		= $RecordListIndex+1;
				$RecordListArr[$RecordListIndex]['date']		= date("d-M-Y",$value);
				$RecordListArr[$RecordListIndex]['sales']		= $RecordSetArr;
				$RecordListArr[$RecordListIndex]['grandtotal']	= number_format($grandtotal,2);

				$RecordListIndex++;
			}
		}
	}

	if(!empty($RecordListArr))
	{
		$response['success']		= true;
		$response['totalrecord']	= $TotalRec;
		$response['msg']			= "Sale list fetched successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllSaleNew")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch sale list.";

	$RecordSetArr	= array();

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"clientid2"=>(int)$_POST['clientid'],"deletedon"=>1,"deletedon2"=>1);
	$condition		= "";
	$salecondition	= " AND deletedon < :deletedon2";

	if($_POST['areaid'] > 0)
	{
		$condition	.= " AND areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$condition	.= " AND lineid=:lineid";
		$Esql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$condition	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
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

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$salecondition		.= " AND (saledate BETWEEN :startdate AND :enddate)";

		$Esql['startdate']	= strtotime($_POST['startdate']);
		$Esql['enddate']	= strtotime($_POST['enddate']);
	}

	if(trim($_POST['stockid']) > 0)
	{
		$salecondition		.= " AND inventoryid=:inventoryid";

		$Esql['inventoryid']	= (int)$_POST['stockid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."sale WHERE clientid=:clientid ".$salecondition." AND customerid IN (SELECT id FROM ".$Prefix."customers WHERE 1 AND clientid=:clientid2 ".$condition." AND deletedon < :deletedon) ORDER BY saledate ASC";
	
	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllStockName	= GetInventoryNames();

		$TempArr		= array();
		$TempDateArr	= array();

		while($rows = pdo_fetch_assoc($Query))
		{
			$inventoryid	= $rows['inventoryid'];
			$noofpices		= $rows['noofpices'];
			$salerate		= $rows['salerate'];
			$saledate		= $rows['saledate'];

			$categoryid		= $AllStockName[$inventoryid]['categoryid'];

			if($categoryid == $_POST['catid'])
			{
				$TempArr[$saledate][$inventoryid]	+= (int)$noofpices;

				/*$TempDateArr[$inventoryid]	= $saledate;*/
			}
		}

		$index	= 0;

		foreach($TempArr as $tempsaledate=>$val)
		{
			$saledate		= "---";

			//$tempsaledate	= $TempDateArr[$key];

			if($tempsaledate > 0)
			{
				$saledate	= date("d-M-Y",$tempsaledate);
			}
			foreach($val as $key => $val2)
			{
				$RecordSetArr[$index]['stockid']	= $key;
				$RecordSetArr[$index]['name']		= $AllStockName[$key]['name'];
				$RecordSetArr[$index]['count']		= $val2;
				$RecordSetArr[$index]['saledate']	= $saledate;
				$RecordSetArr[$index]['saledateunix']	= $tempsaledate;

				$index++;
			}
		}
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['totalrecord']	= $index;
		$response['msg']			= "Sale list fetched successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLatestMagazinePrice")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch sale.";

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
	$cond	= " deletedon < :deletedon";

	if($_POST['areaid'] != "")
	{
		$cond	.= " AND areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}
	if($_POST['lineid'] != "")
	{
		$cond	.= " AND lineid=:lineid";
		$Esql['lineid']	= (int)$_POST['lineid'];
	}
	if($_POST['hawkerid'] != "")
	{
		$cond	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
	}
	if($_POST['inventoryid'] != "")
	{
		$cond	.= " AND inventoryid=:inventoryid";
		$Esql['inventoryid']	= (int)$_POST['inventoryid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."sale WHERE clientid=:clientid ".$cond." ORDER BY createdon DESC LIMIT 1";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$rows = pdo_fetch_assoc($Query);

		$id			= $rows['id'];
		$salerate	= $rows['salerate'];

		$RecordSetArr['id']			= $id;
		$RecordSetArr['salerate']	= (float)$salerate;

		$response['success']	= true;
		$response['msg']		= "Sale detail found successfully.";
	}

	$response['recordset']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'DeleteSale')
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete sale, Please try later.";

	$datetodelete	= $_POST['saledate'];

	$DelSql		= "UPDATE ".$Prefix."sale
	SET
	deletedon	=:deletedon
	WHERE 
	inventoryid			=:inventoryid
	AND 
	saledate			=:saledate
	AND 
	clientid	=:clientid";

	$DelEsql	= array(
		'deletedon'	=>time(),
		'inventoryid'	=>(int)$_POST['recordid'],
		'saledate'		=>(int)$datetodelete,
		"clientid"		=>(int)$_POST['clientid']	
	);

	$DelQuery	= pdo_query($DelSql,$DelEsql);
	if($DelQuery)
	{
		$Response['success']	= true;
		$Response['msg']		= "Sale Record deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetEditSaleDetail')
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to fetch sale data.";

	$sql	= "SELECT * FROM ".$Prefix."sale WHERE inventoryid=:inventoryid AND saledate=:saledate AND deletedon <:deletedon";
	$esql	= array("inventoryid"=>(int)$_POST['stockid'],"saledate"=>(int)$_POST['saledate'],"deletedon"=>1);
	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);
	if($num > 0)
	{
		$TempCustomerQty	  = array();
		$TempCustomerSaleRate = array();
		$TempCustomerSaleID = array();
		$SaleRate	= 0;
		$GetInventoryNameArr = GetInventoryNames();
		while($row = pdo_fetch_assoc($query))
		{
			$SaleID		= $row['id'];
			$CustomerID	= $row['customerid'];
			$Qty		= $row['noofpices'];
			$SaleRate	= ($row['salerate'] * 100) / 100;
			$TempCustomerQty[$CustomerID] = $Qty;
			$TempCustomerSaleRate[$CustomerID] = $SaleRate;
			$TempCustomerSaleID[$CustomerID] = $SaleID;
		}
		$CustomerIDArr = @array_keys($TempCustomerQty);
		$CustomerIDstr = @implode(",",$CustomerIDArr);
		$RecordListArr = array();
		$TotalRecord   = 0;
		$TotalSaleQuantity = 0;
		if($CustomerIDstr !='')
		{
			$CustSql	= "SELECT * FROM ".$Prefix."customers WHERE id IN (".$CustomerIDstr.") ORDER BY name ASC";
			$CustEsql	= array();
			$CustQuery	= pdo_query($CustSql,$CustEsql);
			$CustNum	= pdo_num_rows($CustQuery);
			$TotalRecord = $CustNum;

			$index	= 0;

			$GetAllLine			= GetAllLine($_POST['clientid']);
			/*$GetAllLineman	= GetAllLineman($_POST['clientid']);*/
			$GetAllHawker		= GetAllHawker($_POST['clientid']);
			$GetAllArea			= GetAllArea($_POST['clientid']);
			$GetAllSubLine		= GetAllSubLine($_POST['clientid']);

			while($rows	= pdo_fetch_assoc($CustQuery))
			{
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
				$RecordListArr[$index]['housenumber']			= $housenumber;
				$RecordListArr[$index]['floor']					= $floor;
				$RecordListArr[$index]['areaid']				= (int)$rows['areaid'];
				$RecordListArr[$index]['lineid']				= (int)$rows['lineid'];
				$RecordListArr[$index]['hawkerid']				= (int)$rows['hawkerid'];
				$RecordListArr[$index]['stockqty']				= $TempCustomerQty[$id];
				$RecordListArr[$index]['salerate']				= $TempCustomerSaleRate[$id];
				$RecordListArr[$index]['saleid']				= $TempCustomerSaleID[$id];

				$TotalSaleQuantity	+=	$TempCustomerQty[$id];

			$index++;
			}
			$response['success']	= true;
		}

		$response['customerlist']		= $RecordListArr;
		$response['totalrecord']		= (int)$TotalRecord;
		$response['paginglist']		= $pageListArr;
		$response['totalsalequantity']		= $TotalSaleQuantity;
		$response['inventoryname']	= $GetInventoryNameArr[$_POST['stockid']]['name'];
		$response['saledate']	= date("Y-m-d",$_POST['saledate']);
	}
	$json = json_encode($response);
    echo $json;
	die;
}