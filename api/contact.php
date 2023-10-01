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

if($_POST['Mode'] == "AddContactRequest")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add contact request.";

	if($haserror == false)
	{
		if($_POST['requesttype'] == 'subscriptionclosure')
		{
			$CheckSql	= "SELECT * FROM ".$Prefix."contact_request WHERE clientid=:clientid AND customerid=:customerid AND inventoryid=:inventoryid AND status<>:status";
			$CheckEsql	= array("clientid"=>(int)$_POST['clientid'], "customerid"=>(int)$_POST['customerid'],"inventoryid"=>(int)$_POST['inventoryid'],"status"=>1);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$response['success']	= false;
				$response['msg']		= "A request is already in queue we will contact you soon.";

				$json = json_encode($response);
				echo $json;
				die;
			}

			$UserSql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
			$UserEsql	= array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid']);

			$UserQuery	= pdo_query($UserSql,$UserEsql);
			$UserNum	= pdo_num_rows($UserQuery);

			if($UserNum > 0)
			{
				$AllAreaArr	= GetAllArea($_POST['clientid']);

				$AllInventoryNamesArr	= GetInventoryNames();

				$userrow	= pdo_fetch_assoc($UserQuery);

				$name	= $userrow['name'];
				$phone	= $userrow['phone'];
				$email	= $userrow['email'];
				$areaid	= $userrow['areaid'];
				$lineid	= $userrow['lineid'];

				$_POST['message']	= "".$name." (#".$_POST['customerrecid'].") ".$phone." Of ".$AllAreaArr[$areaid]['name']." want to stop ".$AllInventoryNamesArr[$_POST['inventoryid']]['name']." from ".date("d-M-Y",$createdon)."";

				$Sql	= "INSERT INTO ".$Prefix."contact_request SET 
				clientid		=:clientid,
				customerid		=:customerid,
				inventoryid		=:inventoryid,
				areaid			=:areaid,
				lineid			=:lineid,
				name			=:name,
				email			=:email,
				phone			=:phone,
				message			=:message,
				requesttype		=:requesttype,
				stopdate		=:stopdate,
				createdon		=:createdon";

				$Esql	= array(
					"clientid"			=>(int)$_POST['clientid'],
					"customerid"		=>(int)$_POST['customerid'],
					"inventoryid"		=>(int)$_POST['inventoryid'],
					"areaid"			=>(int)$areaid,
					"lineid"			=>(int)$lineid,
					"name"				=>$name,
					"email"				=>$email,
					"phone"				=>$phone,
					"message"			=>$_POST['message'],
					"requesttype"		=>$_POST['requesttype'],
					"stopdate"			=>strtotime(date("d-M-Y",$createdon)),
					"createdon"			=>$createdon
				);
			}
			else
			{
				$response['success']	= false;
				$response['msg']		= "Customer detail not found.";

				$json = json_encode($response);
				echo $json;
				die;
			}
		}
		else
		{
			$Sql	= "INSERT INTO ".$Prefix."contact_request SET 
			clientid		=:clientid,
			name			=:name,
			email			=:email,
			phone			=:phone,
			message			=:message,
			requesttype		=:requesttype,
			createdon		=:createdon";

			$Esql	= array(
				"clientid"			=>(int)$_POST['clientid'],
				"name"				=>$_POST['name'],
				"email"				=>$_POST['email'],
				"phone"				=>$_POST['phone'],
				"message"			=>$_POST['message'],
				"requesttype"		=>$_POST['requesttype'],
				"createdon"			=>$createdon
			);
		}

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];

			if($_POST['requesttype'] == 'subscriptionclosure')
			{
				$response['msg']	= "subscription closure request successfully submitted, we will contact you soon.";
			}
			else
			{
				$response['msg']	= "Your contact request successfully submitted.";
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllContacts")
{
	$links		= 5;
	$perpage	= 100;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}

	$RecordListArr			= array();
	$SubscriptionSummaryArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customers.";

	$condition	= "";
	$Esql		= array();

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."contact_request WHERE 1 ".$condition." ORDER BY createdon ASC, status DESC";

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

	if($_POST['type'] == "sequence")
	{
		$Sql2	= $Sql;
	}
	else
	{
		$Sql2	= $Sql.$addquery;
		$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));
	}

	$Query2	= pdo_query($Sql2,$Esql);
	$Num2	= pdo_num_rows($Query2);

	$showingrecord	= 0;

	if($Num2 > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query2))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$email		= $rows['email'];
			$message	= $rows['message'];
			$status		= (int)$rows['status'];
			$createdon	= $rows['createdon'];

			$RecordListArr[$index]['id']		= (int)$id;
			$RecordListArr[$index]['name']		= $name;
			$RecordListArr[$index]['phone']		= $phone;
			$RecordListArr[$index]['email']		= $email;
			$RecordListArr[$index]['message']	= $message;
			$RecordListArr[$index]['status']	= $status;
			$RecordListArr[$index]['date']		= date("d-M-Y",$createdon);

			$index++;
			$showingrecord++;
		}

		$response['success']	= true;
		$response['msg']		= "Contact listed successfully.";
	}

	$pageListArr	= array();
	$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);
	$fromrecord		= $offset+1;

	$response['recordlist']		= $RecordListArr;
	$response['perpage']		= (int)$perpage;
	$response['totalpages']		= (int)$totalpages;
	$response['paginglist']		= $pageListArr;
	$response['showpages']		= false;
	$response['totalrecord']	= $TotalRec;
	$response['recordrange']	= $fromrecord."-".($fromrecord+($showingrecord) - 1);

	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "MarkComplete")
{
	$haserror	= false;
    $response['success']	= false;

    $response['msg']		= "Unable to update status.";
    $response['toastmsg']	= "Unable to update status.";

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		
		if($CheckNum < 0)
		{
			$response['toastmsg']	= "Please enter all required field";
		}
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."contact_request SET 
		status		=:status
		WHERE
		id			=:id
		AND
		clientid	=:clientid";

		$Esql	= array(
			"status"	=>1,
			"id"		=>(int)$_POST['recordid'],
			"clientid"	=>(int)$_POST['clientid'],
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "Status successfully updated.";
			$response['toastmsg']	= "Status successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteContactRequest")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete contact, Please try later.";

	$DelSql		= "DELETE FROM ".$Prefix."contact_request
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
		$Response['msg']		= "Contact request deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
?>