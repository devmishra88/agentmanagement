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

if($_POST['Mode'] == "AddSubLine")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add sub line.";

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
		$CheckSql	= "SELECT * FROM ".$Prefix."subline WHERE phone=:phone AND clientid=:clientid AND deletedon < :deletedon";
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
			$response['toastmsg']	= "A sub line already exist with same phone";
		}
		
	}

	if($haserror == false)
	{
		/*$LineSql	= "SELECT * FROM ".$Prefix."line WHERE id=:id";
		$LineEsql	= array("id"=>(int)$_POST['lineid']);

		$LineQuery	= pdo_query($LineSql,$LineEsql);
		$LineNum	= pdo_num_rows($LineQuery);

		$areaid	= "";

		if($LineNum > 0)
		{
			$LineRows	= pdo_fetch_assoc($LineQuery);
			$areaid		= $LineRows['areaid'];
		}*/

		$Sql	= "INSERT INTO ".$Prefix."subline SET 
		clientid	=:clientid,
		areaid		=:areaid,
		lineid		=:lineid,
		name		=:name,
		phone		=:phone,
		remark		=:remark,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"areaid"	=>(int)$_POST['areaid'],
			"lineid"	=>(int)$_POST['lineid'],
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
			$response['msg']		= "Sub Line successfully added.";
			$response['toastmsg']	= "Sub Line successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllSubLine")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch sub line.";

	$Cond	= "";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	if($_POST['areaid'] > 0)
	{
		$Cond	.= " AND areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Cond	.= " AND lineid=:lineid";
		$Esql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Cond	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Cond	.= " AND lineid IN(".$lineids.")";
	}

	$Sql	= "SELECT * FROM ".$Prefix."subline WHERE clientid=:clientid AND deletedon < :deletedon ".$Cond."";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);
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
			$name			= $rows['name'];
			$phone			= $rows['phone'];
			$createdon		= $rows['createdon'];
			$areaid			= $rows['areaid'];
			$lineid			= $rows['lineid'];

			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['areaname']		= $GetAllArea[$areaid]['name'];
			$RecordListArr[$index]['linename']		= $GetAllLine[$lineid]['name'];
			$RecordListArr[$index]['addeddate']		= date("d-M-Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Sub Line listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetSubLineDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch sub line detail.";

	$AllAreaArr = GetAllArea($_POST["clientid"]);
	$AllLineArr = GetAllLine($_POST["clientid"]);

	$sql	= "SELECT * FROM ".$Prefix."subline WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$areaid	= $row['areaid'];
		$lineid	= $row['lineid'];

		$detailArr["name"]		= $row['name'];
		$detailArr["phone"]		= $row['phone'];
		$detailArr["remark"]	= $row['remark'];
		$detailArr["status"]	= $row['status'];
		$detailArr["areaid"]	= $areaid;
		$detailArr["lineid"]	= $lineid;
		$detailArr["areaname"]	= $AllAreaArr[$areaid]['name'];
		$detailArr["linename"]	= $AllLineArr[$lineid]['name'];

		$response['success']	= true;
		$response['msg']		= "Sub Line detail fetched successfully.";
	}

	$response['detail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditSubLine")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to edit sub line.";

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
		$CheckSql	= "SELECT * FROM ".$Prefix."subline WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
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
		/*$LineSql	= "SELECT * FROM ".$Prefix."line WHERE id=:id";
		$LineEsql	= array("id"=>(int)$_POST['lineid']);

		$LineQuery	= pdo_query($LineSql,$LineEsql);
		$LineNum	= pdo_num_rows($LineQuery);

		$areaid	= "";

		if($LineNum > 0)
		{
			$LineRows	= pdo_fetch_assoc($LineQuery);
			$areaid		= $LineRows['areaid'];
		}*/

		$Sql	= "UPDATE ".$Prefix."subline SET 
		areaid		=:areaid,
		lineid		=:lineid,
		name		=:name,
		phone		=:phone,
		remark		=:remark,
		status		=:status
		WHERE
		id			=:id";

		$Esql	= array(
			"areaid"	=>(int)$_POST['areaid'],
			"lineid"	=>(int)$_POST['lineid'],
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
			$response['msg']		= "Sub Line successfully updated.";
			$response['toastmsg']	= "Sub Line successfully updated.";
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
		$Sql	= "UPDATE ".$Prefix."subline SET 
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

	$DelSql		= "UPDATE ".$Prefix."subline SET 
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
if($_POST['Mode'] == "GetSubLine")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch Sub Line.";

	$condition	= "";
	$Esql		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if($_POST['type'] == "substitute")
	{
		$Esql['id']	= (int)$_POST['recordid'];
		$condition	.= " AND id<>:id";
	}

	$Esql['lineid']	= (int)$_POST['lineid'];
	$condition		.= " AND lineid=:lineid";

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

	$Sql	= "SELECT * FROM ".$Prefix."subline WHERE clientid=:clientid AND deletedon < :deletedon ".$condition." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($_POST['type'] == "addeditcustomer")
	{
		$RecordSetArr[$index]['id']		= '';
		$RecordSetArr[$index]['name']	= '-Select-';

		$index++;
	}

	if($_POST['type'] == "customerfilter")
	{
		$RecordSetArr[$index]['id']		= '';
		$RecordSetArr[$index]['name']	= 'None';

		$index++;
	}

	if($_POST['type'] == "substitute")
	{
		$RecordSetArr[$index]['id']		= '0';
		$RecordSetArr[$index]['name']	= 'None';

		$index++;
	}

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$RecordSetArr[$index]['id']		= $id;
			$RecordSetArr[$index]['name']	= $name;

			$index++;
		}
	}
	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Sub Line listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
?>