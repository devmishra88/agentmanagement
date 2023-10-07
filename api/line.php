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

if($_POST['Mode'] == "AddLine")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add line.";

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
	}
	/*if($_POST['phone'] == "")
	{
		$ErrorMsg	.= "Please enter phone.<br>";
	}

	if($_POST['phone'] != "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."line WHERE phone=:phone AND clientid=:clientid AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A line already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
		
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A line already exist with same phone";
		}
		
	}

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."line SET 
		clientid	=:clientid,
		areaid		=:areaid,
		name		=:name,
		phone		=:phone,
		remark		=:remark,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"areaid"	=>(int)$_POST['areaid'],
			"name"		=>$_POST['name'],
			"phone"		=>$_POST['phone'],
			"remark"	=>$_POST['remarks'],
			"status"	=>(int)$_POST['status'],
			"createdon"	=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];
			$response['msg']		= "Line successfully added.";
			$response['toastmsg']	= "Line successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllLine")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch line.";

	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);
	$Cond	= "";

	if($_POST['areaid'] > 0)
	{
		$Cond	.= " AND areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Cond	.= " AND id=:id";
		$Esql['id']	= (int)$_POST['lineid'];
	}

	if($_POST['ismanager'] == "1")
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Cond	.= " AND areaid IN (".$areaids.")";
	}
	else if($_POST['islineman'] == "1")
	{
		$areaids	= $_POST['linemanareaid'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$lineids	= $_POST['linemanlineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$condition	.= " AND areaid IN(".$areaids.") AND id IN(".$lineids.")";
	}
	else if($_POST['linemanid'] > 0 && $_POST['islineman'] < 1)
	{
		$AssignedAreaAndLineArr = GetAllAssignedAreaAndLineByLineman($_POST['clientid'], $_POST['linemanid']);

		$areaids	= $AssignedAreaAndLineArr['areaid'];
		$lineids	= $AssignedAreaAndLineArr['lineids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Cond	.= " AND areaid IN(".$areaids.") AND id IN(".$lineids.")";
	}
	else if($_POST['ishawker'] == "1")
	{
		$areaids	= $_POST['hawkerareaid'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$lineids	= $_POST['hawkerlineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Cond	.= " AND areaid IN(".$areaids.") AND id IN(".$lineids.")";
	}

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ".$Cond." ORDER BY name ASC";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);
	$TotalRec	= $Num;

	if($Num > 0)
	{
		$GetAllArea	= GetAllArea($_POST['clientid']);
		$GetAllLine	= GetAllLine($_POST['clientid']);

		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$substitute	= "";

			$id				= $rows['id'];
			$areaid			= $rows['areaid'];
			$name			= $rows['name'];
			$phone			= $rows['phone'];
			$createdon		= $rows['createdon'];
			$substituteid	= $rows['substituteid'];

			$areaname		= $GetAllArea[$areaid]['name'];

			if(trim($areaname) == "")
			{
				$areaname	= "---";
			}

			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['areaname']		= $areaname;
			$RecordListArr[$index]['substitute']	= $GetAllLine[$substituteid]['name'];
			$RecordListArr[$index]['addeddate']		= date("d-M-Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Line listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLineDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch line detail.";

	$AllAreaArr = GetAllArea($_POST["clientid"]);

	$sql	= "SELECT * FROM ".$Prefix."line WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$areaid	= $row['areaid'];

		$detailArr["name"]		= $row['name'];
		$detailArr["phone"]		= $row['phone'];
		$detailArr["remark"]	= $row['remark'];
		$detailArr["status"]	= $row['status'];
		$detailArr["areaid"]	= $areaid;
		$detailArr["areaname"]	= $AllAreaArr[$row['areaid']]['name'];

		$response['success']	= true;
		$response['msg']		= "Line detail fetched successfully.";
	}

	$response['linedetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditLine")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add line.";

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
	}
	/*if($_POST['phone'] == "")
	{
		$ErrorMsg	.= "Please enter phone.<br>";
	}

	if($_POST['phone'] != "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."line WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A Line already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']	= $ErrorMsg;
		$response['toastmsg']	= "There is a error to update record.";
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A Line already exist with same phone";
		}
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."line SET 
		areaid		=:areaid,
		name		=:name,
		phone		=:phone,
		remark		=:remark,
		status		=:status
		WHERE
		id			=:id";

		$Esql	= array(
			"areaid"	=>(int)$_POST['areaid'],
			"name"		=>$_POST['name'],
			"phone"		=>$_POST['phone'],
			"remark"	=>$_POST['remarks'],
			"status"	=>(int)$_POST['status'],
			"id"		=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "Line successfully updated.";
			$response['toastmsg']	= "Line successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "UpdateLineSubstitute")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to update substitute.";

	if($ErrorMsg != "")
	{
		$haserror			= true;
		$response['msg']	= $ErrorMsg;
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."line SET 
		substituteid	=:substituteid
		WHERE
		id			=:id";

		$Esql	= array(
			"substituteid"	=>(int)$_POST['substituteid'],
			"id"			=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "Line substitute successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteLine")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete Line, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid=:lineid AND clientid=:clientid AND deletedon < :deletedon";
	$CheckEsql	= array("lineid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Line can't be deleted due to customer exist.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."line SET 
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
		$Response['msg']		= "Line deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLine")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch Line.";

	$condition	= "";
	$Esql		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if($_POST['type'] == "substitute")
	{
		$Esql['id']	= (int)$_POST['recordid'];
		$condition	.= " AND id<>:id";
	}

	if($_POST['type'] != "subline")
	{
		$Esql['areaid']	= (int)$_POST['areaid'];
		$condition		.= " AND areaid=:areaid";
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$condition	.= " AND areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$condition	.= " AND id IN(".$lineids.")";
	}

	if($_POST['type2'] == "addeditlineman" || $_POST['type2'] == "addedithawker")
	{
		$selectedline	= "";

		if($_POST['recordid'] > 0)
		{
			if($_POST['type2'] == "addeditlineman")
			{
				$Sql2	= "SELECT * FROM ".$Prefix."lineman WHERE id<>:id AND deletedon < :deletedon";
			}
			else if($_POST['type2'] == "addedithawker")
			{
				$Sql2	= "SELECT * FROM ".$Prefix."hawker WHERE id<>:id AND deletedon < :deletedon";
			}
			$Esql2	= array("id"=>(int)$_POST['recordid'],"deletedon"=>1);
		}
		else
		{
			if($_POST['type2'] == "addeditlineman")
			{
				$Sql2	= "SELECT * FROM ".$Prefix."lineman WHERE 1 AND deletedon < :deletedon";
			}
			else if($_POST['type2'] == "addedithawker")
			{
				$Sql2	= "SELECT * FROM ".$Prefix."hawker WHERE 1 AND deletedon < :deletedon";
			}
			$Esql2	= array("deletedon"=>1);
		}
		$Query2	= pdo_query($Sql2,$Esql2);
		$Num2	= pdo_num_rows($Query2);

		if($Num2 > 0)
		{
			$SelectedLineArr	= array();
			while($rows2 = pdo_fetch_assoc($Query2))
			{
				$lineids	= @explode("::",$rows2['lineids']);

				$SelectedLineArr = array_merge($lineids,$SelectedLineArr);
			}
		}
		$SelectedLineArr	= @array_unique($SelectedLineArr);
		$SelectedLineArr	= @array_filter($SelectedLineArr);
		$SelectedLineArr	= @array_values($SelectedLineArr);

		if(!empty($SelectedLineArr))
		{
			$SelectedLineStr	= @implode(",",$SelectedLineArr);

			$condition	.= " AND id NOT IN(".$SelectedLineStr.")";
		}
	}

	if($_POST['type'] == "customerfilter")
	{
		$Esql['areaid2']	= 0;
		$condition		.= " AND areaid > :areaid2";
	}

	$SubscribedCustArr	= array();

	if($_POST['area'] == 'addsale')
	{
		$AssignedSql	= " SELECT * FROM ".$Prefix."subscriptions WHERE inventoryid=:inventoryid AND customerid IN(SELECT id FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon)";

		$AssignedEsql	= array('inventoryid'=>(int)$_POST['inventoryid'],'clientid'=>(int)$_POST['clientid'],'deletedon'=>1);

		$AssignedQuery	= pdo_query($AssignedSql,$AssignedEsql);
		$AssignedNum	= pdo_num_rows($AssignedQuery);

		if($AssignedNum > 0)
		{
			while($assignedrow = pdo_fetch_assoc($AssignedQuery))
			{
				$customerid	= $assignedrow['customerid'];

				$SubscribedCustArr[]	= (int)$customerid;
			}
		}
	}

	$SubscribedCustStr	= "";

	$SubscribedCustArr	= @array_unique(@array_filter($SubscribedCustArr));

	if(!empty($SubscribedCustArr))
	{
		$SubscribedCustStr	= @implode(",",$SubscribedCustArr);
	}
	if(trim($SubscribedCustStr) == "")
	{
		$SubscribedCustStr	= "-1";
	}

	$SaleCondition	= "";

	if($_POST['area'] == 'addsale')
	{
		$SaleCondition	= " AND id IN(SELECT lineid FROM ".$Prefix."customers WHERE id IN(".$SubscribedCustStr."))";
	}

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ".$condition.$SaleCondition." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		if($_POST['fromarea'] == "salefilter")
		{
			$RecordSetArr[$index]['id']		= '';
			$RecordSetArr[$index]['name']	= 'Select';
			$index++;
		}

		if($_POST['type'] == "substitute")
		{
			$RecordSetArr[$index]['id']		= '0';
			$RecordSetArr[$index]['name']	= 'None';

			$index++;
		}

		if($_POST['fromarea'] == "billrecovery" || $_POST['fromarea'] == "restartcustomer" || $_POST['fromarea'] == "paymentregister" || $_POST['fromarea'] == "messagearea" || $_POST['fromarea'] == "showallarea")
		{
			$RecordSetArr[$index]['id']		= '-1';
			$RecordSetArr[$index]['name']	= 'All Line';

			$index++;
		}

		if($_POST['fromarea'] == "salefilter")
		{
			$RecordSetArr[$index]['id']		= '';
			$RecordSetArr[$index]['name']	= 'All Line';

			$index++;
		}

		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$RecordSetArr[$index]['id']		= $id;
			$RecordSetArr[$index]['name']	= $name;

			$index++;
		}
		$response['success']	= true;
		$response['msg']		= "Line listed successfully.";
	}
	else
	{
		if($_POST['fromarea'] == "billrecovery" || $_POST['fromarea'] == "messagearea")
		{
			$RecordSetArr[$index]['id']		= '-1';
			$RecordSetArr[$index]['name']	= 'Select an area to fetch line';

			$index++;
		}
		else
		{
			$RecordSetArr[$index]['id']		= "";
			$RecordSetArr[$index]['name']	= "No line available";

			$index++;
		}

		$response['success']	= true;

		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Line listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
?>