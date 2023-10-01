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

if($_POST['Mode'] == "AddArea")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add area.";

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
		
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A area already exist with same phone";
		}
		
	}

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."area SET 
		clientid		=:clientid,
		name			=:name,
		remark			=:remark,
		status			=:status,
		createdon		=:createdon,
		droppingpointid	=:droppingpointid,
		isonlinepayment	=:isonlinepayment";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"name"				=>$_POST['name'],
			"remark"			=>$_POST['remarks'],
			"status"			=>(int)$_POST['status'],
			"createdon"			=>$createdon,
			"droppingpointid"	=>(int)$_POST['droppingpointid'],
			"isonlinepayment"	=>(int)$_POST['isonlinepayment']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];
			$response['msg']		= "Area successfully added.";
			$response['toastmsg']	= "Area successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllArea")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch area.";

	$Cond	= "";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	if($_POST['droppingpointid'] > 0)
	{
		$Cond	.= " AND droppingpointid=:droppingpointid";
		$Esql['droppingpointid']	= (int)$_POST["droppingpointid"];
	}

	if($_POST['areaid'] > 0)
	{
		$Cond		.= " AND id=:id";
		$Esql['id']	= (int)$_POST["areaid"];
	}

	if(($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0) || ($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Cond	.= " AND id IN(".$areaids.")";
	}

	$Sql	= "SELECT * FROM ".$Prefix."area WHERE clientid=:clientid AND deletedon < :deletedon ".$Cond." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$TotalRec	= $Num;

	if($Num > 0)
	{
		$index	= 0;

		$AllDroppingPointArr	= GetAllDroppingPoint($_POST["clientid"]);

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$substitute	= "";

			$id					= $rows['id'];
			$name				= $rows['name'];
			$createdon			= $rows['createdon'];
			$substituteid		= $rows['substituteid'];
			$droppingpointid	= $rows['droppingpointid'];
			$droppingpointname	= $AllDroppingPointArr[$droppingpointid]['name'];

			if($droppingpointname == "")
			{
				$droppingpointname	= "---";
			}

			$RecordListArr[$index]['id']				= $id;
			$RecordListArr[$index]['name']				= $name;
			$RecordListArr[$index]['name']				= $name;
			$RecordListArr[$index]['droppingpointname']	= $droppingpointname;
			$RecordListArr[$index]['addeddate']			= date("d-M-Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Area listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAreaDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch area detail.";

	$sql	= "SELECT * FROM ".$Prefix."area WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$AllDroppingPointArr	= GetAllDroppingPoint($_POST["clientid"]);

		$row	= pdo_fetch_assoc($query);

		$droppingpointid	= $row['droppingpointid'];
		$droppingpointname	= $AllDroppingPointArr[$droppingpointid]['name'];

		if($droppingpointid < 1)
		{
			$droppingpointid	= "";
			$droppingpointname	= "Select";
		}

		$detailArr["name"]				= $row['name'];
		$detailArr["remark"]			= $row['remark'];
		$detailArr["status"]			= $row['status'];
		$detailArr["droppingpointid"]	= $droppingpointid;
		$detailArr["droppingpointname"]	= $droppingpointname;
		$detailArr["isonlinepayment"]	= (int)$row['isonlinepayment'];

		$response['success']	= true;
		$response['msg']		= "Area detail fetched successfully.";
	}

	$response['areadetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditArea")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add area.";

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']	= $ErrorMsg;
		$response['toastmsg']	= "There is a error to update record.";
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A Area already exist with same phone";
		}
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."area SET 
		name			=:name,
		remark			=:remark,
		status			=:status,
		droppingpointid	=:droppingpointid,
		isonlinepayment	=:isonlinepayment
		WHERE
		id				=:id";

		$Esql	= array(
			"name"				=>$_POST['name'],
			"remark"			=>$_POST['remarks'],
			"status"			=>(int)$_POST['status'],
			"droppingpointid"	=>(int)$_POST['droppingpointid'],
			"isonlinepayment"	=>(int)$_POST['isonlinepayment'],
			"id"				=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "Area successfully updated.";
			$response['toastmsg']	= "Area successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteArea")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete Area, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE areaid=:areaid AND clientid=:clientid AND deletedon < :deletedon";
	$CheckEsql	= array("areaid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Area can't be deleted due to customer exist.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."area SET 
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
		$Response['msg']		= "Area deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetArea")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch Area.";

	$condition	= "";
	$Esql		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0) || ($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$condition	.= " AND id IN(".$areaids.")";
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
		$SaleCondition	= " AND id IN(SELECT areaid FROM ".$Prefix."customers WHERE id IN(".$SubscribedCustStr."))";
	}

	$Sql	= "SELECT * FROM ".$Prefix."area WHERE clientid=:clientid AND deletedon < :deletedon ".$condition.$SaleCondition." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		if($_POST['type'] == "customerfilter" && $_POST['fromarea'] != "billstatementsummary")
		{
			$RecordSetArr[$index]['id']		= '';
			$RecordSetArr[$index]['name']	= 'None';

			$index++;
		}
		else if($_POST['fromarea'] == "billstatementsummary")
		{
			$RecordSetArr[$index]['id']		= '';
			$RecordSetArr[$index]['name']	= 'Select';

			$index++;
		}
		else if($_POST['fromarea'] == "salefilter" || $_POST['area'] == "addsale")
		{
			$RecordSetArr[$index]['id']		= '';
			$RecordSetArr[$index]['name']	= 'All Area';

			$index++;
		}

		if($_POST['fromarea'] == "billrecovery" || $_POST['fromarea'] == "restartcustomer" || $_POST['fromarea'] == "paymentregister" || $_POST['fromarea'] == "messagearea" || $_POST['fromarea'] == "showallarea")
		{
			$RecordSetArr[$index]['id']		= '-1';
			$RecordSetArr[$index]['name']	= 'All Area';

			$index++;
		}

		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$hasline	= true;

			if($_POST['type'] == "customerfilter")
			{
				$CheckSql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon AND areaid=:areaid ORDER BY name ASC";
				$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"areaid"=>(int)$id);

				$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
				$CheckNum	= pdo_num_rows($CheckQuery);

				if($CheckNum < 1)
				{
					$hasline	= false;
				}
			}

			if($hasline)
			{
				$RecordSetArr[$index]['id']		= $id;
				$RecordSetArr[$index]['name']	= $name;

				$index++;
			}
		}
		$response['success']	= true;
		$response['msg']		= "Area listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
?>