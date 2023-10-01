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

if($_POST['Mode'] == "AddDroppingPoint")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add dropping point.";

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
			$response['toastmsg']	= "A dropping point already exist with same phone";
		}
		
	}

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."dropping_point SET 
		clientid	=:clientid,
		name		=:name,
		remark		=:remark,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"name"		=>$_POST['name'],
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
			$response['msg']		= "Dropping point successfully added.";
			$response['toastmsg']	= "Dropping point successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllDroppingPoint")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch dropping point.";
	$DroppingPointArr = array();
	$condition = '';
	$DroppingPointStr ='';
	if($_POST['ismanager'] > 0)
	{
		$DroppingPointStr = '-1';
		$DroppingPointArr = GetAllDroppingPointByAreaManager($_POST['clientid'],$_POST['areamanagerid']);
		if(!empty($DroppingPointArr))
		{
			$DroppingPointStr = implode(",",$DroppingPointArr);
		}

		$condition	.= " AND id IN (".$DroppingPointStr.") ";
	}
	$Sql	= "SELECT * FROM ".$Prefix."dropping_point WHERE clientid=:clientid AND deletedon < :deletedon $condition";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);
	$TotalRec	= $Num;

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$substitute	= "";

			$id				= $rows['id'];
			$name			= $rows['name'];
			$remarks		= $rows['remark'];
			$createdon		= $rows['createdon'];
			
			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['remarks']		= $remarks;
			$RecordListArr[$index]['addeddate']		= date("d-M-Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Dropping Point listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetDroppingPointDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch dropping point detail.";

	$sql	= "SELECT * FROM ".$Prefix."dropping_point WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$detailArr["name"]		= $row['name'];
		$detailArr["remark"]	= $row['remark'];
		$detailArr["status"]	= $row['status'];

		$response['success']	= true;
		$response['msg']		= "Dropping point detail fetched successfully.";
	}

	$response['droppingpointdetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditDroppingPoint")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to edit dropping point.";

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
			$response['toastmsg']	= "A dropping point already exist with same phone";
		}
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."dropping_point SET 
		name		=:name,
		remark		=:remark,
		status		=:status
		WHERE
		id			=:id";

		$Esql	= array(
			"name"		=>$_POST['name'],
			"remark"	=>$_POST['remarks'],
			"status"	=>(int)$_POST['status'],
			"id"		=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "Dropping point successfully updated.";
			$response['toastmsg']	= "Dropping point successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteDroppingPoint")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete dropping point, Please try later.";

	$DelSql		= "UPDATE ".$Prefix."dropping_point SET 
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
		$Response['msg']		= "Dropping point deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetDroppingPoint")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch dropping point.";

	$condition	= "";
	$Esql		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$condition = '';
	$DroppingPointStr ='';
	if($_POST['ismanager'] > 0)
	{
		$DroppingPointStr = '-1';
		$DroppingPointArr = GetAllDroppingPointByAreaManager($_POST['clientid'],$_POST['areamanagerid']);
		if(!empty($DroppingPointArr))
		{
			$DroppingPointStr = implode(",",$DroppingPointArr);
		}

		$condition	.= " AND id IN (".$DroppingPointStr.") ";
	}
	$Sql	= "SELECT * FROM ".$Prefix."dropping_point WHERE clientid=:clientid AND deletedon < :deletedon ".$condition." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		if($_POST['type'] == "customerfilter")
		{
			$RecordSetArr[$index]['id']		= '';
			$RecordSetArr[$index]['name']	= 'None';

			$index++;
		}

		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$hasline	= true;

			/*if($_POST['type'] == "customerfilter")
			{
				$CheckSql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon AND areaid=:areaid ORDER BY name ASC";
				$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"areaid"=>(int)$id);

				$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
				$CheckNum	= pdo_num_rows($CheckQuery);

				if($CheckNum < 1)
				{
					$hasline	= false;
				}
			}*/

			if($hasline)
			{
				$RecordSetArr[$index]['id']		= $id;
				$RecordSetArr[$index]['name']	= $name;

				$index++;
			}
		}
		$response['success']	= true;
		$response['msg']		= "Dropping point listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
?>