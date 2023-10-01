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

if($_POST['Mode'] == "GetMessageingDetail")
{
	$RecordSetArr	= array();
	$LineArr		= array();
	$LinemanArr		= array();
	$HawkerArr		= array();
	$InventoryArr	= array();

	$Response['success']	= false;
	$Response['msg']		= "Unable to fetch messaging list, Please try later.";

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid=:lineid AND clientid=:clientid AND deletedon < :deletedon ORDER BY sequence ASC, customerid ASC";
			$CheckEsql	= array("lineid"=>(int)$id,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$LineArr[$index]['id']			= $id;
				$LineArr[$index]['name']		= $name;
				$LineArr[$index]['ischecked']	= false;

				$index++;
			}
		}
	}

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE linemanid=:linemanid AND clientid=:clientid AND deletedon < :deletedon ORDER BY sequence ASC, customerid ASC";
			$CheckEsql	= array("linemanid"=>(int)$id,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$LinemanArr[$index]['id']			= $id;
				$LinemanArr[$index]['name']			= $name;
				$LinemanArr[$index]['ischecked']	= false;

				$index++;
			}
		}
	}

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE hawkerid=:hawkerid AND clientid=:clientid AND deletedon < :deletedon ORDER BY sequence ASC, customerid ASC";
			$CheckEsql	= array("hawkerid"=>(int)$id,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$HawkerArr[$index]['id']		= $id;
				$HawkerArr[$index]['name']		= $name;
				$HawkerArr[$index]['ischecked']	= false;

				$index++;
			}
		}
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	$catindex	= 0;

	if($CategoryNum > 0)
	{
		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryItemArr	= array();

			if($InventoryNum > 0)
			{
				$index	= 0;

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
							$InventoryItemArr[$index]['id']				= (int)$id;
							$InventoryItemArr[$index]['name']			= $name;
							$InventoryItemArr[$index]['categoryid']		= (int)$categoryid;
							$InventoryItemArr[$index]['checked']		= false;

							$index++;
						}
					}
				}

				$InventoryArr[$catindex]['id']				= (int)$catid;
				$InventoryArr[$catindex]['recordlist']		= $InventoryItemArr;
				$InventoryArr[$catindex]['title']			= $cattitle;
				$InventoryArr[$catindex]['checked']			= false;
				$InventoryArr[$catindex]['indeterminate']	= false;

				$catindex++;
			}
		}
	}

	$RecordSetArr['linelist']		= $LineArr;
	$RecordSetArr['linemanlist']	= $LinemanArr;
	$RecordSetArr['hawkerlist']		= $HawkerArr;
	$RecordSetArr['inventorylist']	= $InventoryArr;

	if(!empty($RecordSetArr))
	{
		$Response['success']	= true;
		$Response['recordlist']	= $RecordSetArr;
		$Response['msg']		= "messaging list fetched successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "SendMessage")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to send message, Please try latter.";

	$lineidsArr		= array();
	$linelistArr	= array();

	$linemanidsArr	= array();
	$linemanlistArr	= array();

	$hawkeridsArr	= array();
	$hawkerlistArr	= array();

	$inventorycatidsArr		= array();
	$inventorycategoryArr	= array();

	$inventoryidsArr	= array();
	$inventorynameArr	= array();

	if(!empty($_POST['linelist']))
	{
		foreach($_POST['linelist'] as $key=>$rows)
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
			$ischecked	= $rows['ischecked'];

			if($ischecked == "true")
			{
				$lineidsArr[]	= $id;
				$linelistArr[]	= $name;
			}
		}
	}

	if(!empty($_POST['linemanlist']))
	{
		foreach($_POST['linemanlist'] as $key=>$rows)
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
			$ischecked	= $rows['ischecked'];

			if($ischecked == "true")
			{
				$linemanidsArr[]	= $id;
				$linemanlistArr[]	= $name;
			}
		}
	}

	if(!empty($_POST['hawkerlist']))
	{
		foreach($_POST['hawkerlist'] as $key=>$rows)
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
			$ischecked	= $rows['ischecked'];

			if($ischecked == "true")
			{
				$hawkeridsArr[]		= $id;
				$hawkerlistArr[]	= $name;
			}
		}
	}

	if(!empty($_POST['inventorylist']))
	{
		foreach($_POST['inventorylist'] as $catkey=>$catrows)
		{
			$catid			= $catrows['id'];
			$cattitle		= $catrows['title'];
			$checked		= $catrows['checked'];
			$inventoryArr	= $catrows['recordlist'];

			if(!empty($inventoryArr))
			{
				foreach($inventoryArr as $inventorykey =>$inventoryrows)
				{
					$inventoryid	= $inventoryrows['id'];
					$inventoryname	= $inventoryrows['name'];
					$checked		= $inventoryrows['checked'];

					if($checked == "true")
					{
						$inventorycatidsArr[]	= $catid;
						$inventorycategoryArr[]	= $cattitle;

						$inventoryidsArr[]	= $inventoryid;
						$inventorynameArr[]	= $inventoryname;
					}
				}
			}
		}
	}

	$lineids		= implode(",",array_filter(array_unique($lineidsArr)));
	$linelist		= implode(", ",array_filter(array_unique($linelistArr)));

	$linemanids		= implode(",",array_filter(array_unique($linemanidsArr)));
	$linemanlist	= implode(", ",array_filter(array_unique($linemanlistArr)));

	$hawkerids		= implode(",",array_filter(array_unique($hawkeridsArr)));
	$hawkerlist		= implode(", ",array_filter(array_unique($hawkerlistArr)));

	$inventorycatids	= implode(",",array_filter(array_unique($inventorycatidsArr)));
	$inventorycategory	= implode(", ",array_filter(array_unique($inventorycategoryArr)));

	$inventoryids	= implode(",",array_filter(array_unique($inventoryidsArr)));
	$inventoryname	= implode(", ",array_filter(array_unique($inventorynameArr)));


	$Sql	= "INSERT INTO ".$Prefix."message_campain SET 
	clientid			=:clientid,
	isallline			=:isallline,
	isalllineman		=:isalllineman,
	isallhawker			=:isallhawker,
	lineids				=:lineids,
	linelist			=:linelist,
	linemanids			=:linemanids,
	linemanlist			=:linemanlist,
	hawkerids			=:hawkerids,
	hawkerlist			=:hawkerlist,
	inventorycatids		=:inventorycatids,
	inventorycategory	=:inventorycategory,
	inventoryids		=:inventoryids,
	inventorylist		=:inventorylist,
	message				=:message,
	createdon			=:createdon";

	$Esql	= array(
		"clientid"			=>(int)$_POST['clientid'],
		"isallline"			=>$_POST['isallline'],
		"isalllineman"		=>$_POST['isalllineman'],
		"isallhawker"		=>$_POST['isallhawker'],
		"lineids"			=>$lineids,
		"linelist"			=>$linelist,
		"linemanids"		=>$linemanids,
		"linemanlist"		=>$linemanlist,
		"hawkerids"			=>$hawkerids,
		"hawkerlist"		=>$hawkerlist,
		"inventorycatids"	=>$inventorycatids,
		"inventorycategory"	=>$inventorycategory,
		"inventoryids"		=>$inventoryids,
		"inventorylist"		=>$inventoryname,
		"message"			=>$_POST['message'],
		"createdon"			=>$createdon,
	);


	$Query	= pdo_query($Sql,$Esql);

	if($Query)
	{
		$Response['success']	= true;
		$Response['msg']		= "Message send successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllCampain")
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
    $response['msg']		= "Unable to fetch send messages.";

	$condition	= " AND clientid=:clientid";
	$Esql		= array('clientid'=>(int)$_POST['clientid']);

	/*if($_POST['lineid'] > 0)
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

	if($_POST['hawkerid'] > 0)
	{
		$condition	.= " AND hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if(trim($_POST['searchkeyword']) != "")
	{
		$condition	.= " AND (name LIKE :name || phone LIKE :phone || email LIKE :email || address1 LIKE :address1 || customerid LIKE :customerid)";

		$Esql['name']		= "%".$_POST['searchkeyword']."%";
		$Esql['phone']		= "%".$_POST['searchkeyword']."%";
		$Esql['email']		= "%".$_POST['searchkeyword']."%";
		$Esql['address1']	= "%".$_POST['searchkeyword']."%";
		$Esql['customerid']	= "".$_POST['searchkeyword']."%";
	}

	if($_POST['customerid'] > 0)
	{
		$condition	.= " AND id=:id";
		$Esql['id']	= (int)$_POST['customerid'];
	}*/

	$Sql	= "SELECT * FROM ".$Prefix."message_campain WHERE 1 ".$condition." ORDER BY createdon DESC";

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

		while($rows = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$id				= $rows['id'];
			$linelist		= $rows['linelist'];
			$linemanlist	= $rows['linemanlist'];
			$hawkerlist		= $rows['hawkerlist'];
			$category		= $rows['inventorycategory'];
			$inventorylist	= $rows['inventorylist'];
			$message		= $rows['message'];
			$createdon		= $rows['createdon'];
			$campaindate	= date("d-M-Y",$createdon);

			if(trim($linelist) == "")
			{
				$linelist	= "---";
			}

			if(trim($linemanlist) == "")
			{
				$linemanlist	= "---";
			}

			if(trim($hawkerlist) == "")
			{
				$hawkerlist	= "---";
			}

			if(trim($category) == "")
			{
				$category	= "---";
			}

			if(trim($inventorylist) == "")
			{
				$inventorylist	= "---";
			}

			if(trim($message) == "")
			{
				$message	= "---";
			}

			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['linelist']		= $linelist;
			$RecordListArr[$index]['linemanlist']	= $linemanlist;
			$RecordListArr[$index]['hawkerlist']	= $hawkerlist;
			$RecordListArr[$index]['category']		= $category;
			$RecordListArr[$index]['inventorylist']	= $inventorylist;
			$RecordListArr[$index]['messagetext']	= $message;
			$RecordListArr[$index]['campaindate']	= $campaindate;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Messages listed successfully.";
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

	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}
	
    $json = json_encode($response);
    echo $json;
	die;
}
?>