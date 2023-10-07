<?php
header('Access-Control-Allow-Origin: *');
include_once "dbconfig.php";
$createdon	= time();
if($_POST['Mode'] == "AppLogin")
{
    $response['success']	= false;
    $response['msg']		= "Unable to login, Please try later.";

	/*$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE phone1=:phone1 AND password=:password AND clienttype=:clienttype AND deletedon < :deletedon";
	$CheckEsql	= array("phone1"=>$_POST['phone'],"password"=>$_POST['password'],"clienttype"=>2,"deletedon"=>1);*/

	if($_POST['logintype'] == "1")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$rows	= pdo_fetch_assoc($CheckQuery);

			if($rows['password'] == $_POST['password'])
			{
				$response['success']			= true;
				$response['id']					= (int)$rows['id'];
				$response['clientname']			= $rows['name'];
				$response['ispasswordupdate']	= 1;
				$response['islinemanlogin']		= 1;

				$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
				$Esql	= array("id"=>(int)$rows['clientid'],"clienttype"=>2,"deletedon"=>1);

				$Query		= pdo_query($Sql,$Esql);
				$clientrows	= pdo_fetch_assoc($Query);

				$clientdetail	= array(
					"id"				=>(int)$rows['clientid'],
					"linemanid"			=>(int)$rows['id'],
					"clientname"		=>$rows['name'],
					"clienttype"		=>(int)$clientrows['clienttype'],
					"ispasswordupdate"	=>1,
					"stateid"			=>(int)$clientrows['stateid'],
					"cityid"			=>(int)$clientrows['cityid'],
					"isbetaaccount"		=>(int)$clientrows['accounttype'],
					"pincode"			=>$clientrows['pincode'],
					"islineman"			=>1	
				);

				$response['msg']			= "Logged in successfully.";
				$response['clientdetail']	= $clientdetail;
				$response['logintime']		= $createdon;
			}
			else
			{
				$response['success']	= false;
				$response['msg']		= "Password is incorrect.";
			}
		}
		else
		{
			$response['success']	= false;
			$response['msg']		= "No account exist with the entered Mobile Number.";
		}
	}
	else
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE phone1=:phone1 AND clienttype=:clienttype AND deletedon < :deletedon";
		$CheckEsql	= array("phone1"=>$_POST['phone'],"clienttype"=>2,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$rows	= pdo_fetch_assoc($CheckQuery);

			if($rows['password'] == $_POST['password'])
			{
				$response['success']			= true;
				$response['id']					= (int)$rows['id'];
				$response['clientname']			= $rows['clientname'];
				$response['clienttype']			= (int)$rows['clienttype'];
				$response['ispasswordupdate']	= (int)$rows['ispasswordupdate'];
				$isbetaaccount					= (int)$rows['accounttype'];

				$clientdetail	= array("id"=>(int)$rows['id'],"clientname"=>$rows['clientname'],"clienttype"=>(int)$rows['clienttype'],"ispasswordupdate"=>(int)$rows['ispasswordupdate'],"stateid"=>(int)$rows['stateid'],"cityid"=>(int)$rows['cityid'],"isbetaaccount"=>$isbetaaccount,"pincode"=>$rows['pincode']);

				$response['msg']			= "Logged in successfully.";
				$response['clientdetail']	= $clientdetail;
				$response['logintime']		= $createdon;
			}
			else
			{
				$response['success']	= false;
				$response['msg']		= "Password is incorrect.";
			}
		}
		else
		{
			$response['success']	= false;
			$response['msg']		= "No account exist with the entered Mobile Number.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "RecoverPassword")
{
    $response['success']	= false;
    $response['msg']		= "Unable to recover password, Please try later.";

	if($_POST['logintype'] == "1")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);
	}
	else
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE phone1=:phone1 AND clienttype=:clienttype AND deletedon < :deletedon";
		$CheckEsql	= array("phone1"=>$_POST['forgetphone'],"clienttype"=>2,"deletedon"=>1);
	}

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$rows = pdo_fetch_assoc($CheckQuery);

		$password	= $rows['password'];
		
		if($_POST['logintype'] == "1")
		{
			$clientname	= $rows['name'];
		}
		else
		{
			$clientname	= $rows['clientname'];
		}

		$Message = "Dear ".$clientname.",

Your Password: ".$password."

orlopay.com
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)";

		$FromPhone	= "+91".str_replace("+91","",$SourcePhoneNumber);

		SendMessageViaPlivo($FromPhone,$_POST['forgetphone'],$Message);
		$response = array('success' => true, 'msg' => "Password sent to ".$_POST['forgetphone']);
	}
	else
	{
		$response = array('success' => false, 'msg' => "Mobile number does not exists.");
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetProfileDetail")
{
	$CityListArr	= array();
	$StateListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch detail.";

	$sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
	$esql	= array("id"=>(int)$_POST['recordid']);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$profiledetailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$profiledetailArr["clientname"]		= $row['clientname'];
		$profiledetailArr["clienttype"]		= $row['clienttype'];
		$profiledetailArr["distributorid"]	= $row['distributorid'];
		$profiledetailArr["pincode"]		= $row['pincode'];
		$profiledetailArr["stateid"]		= $row['stateid'];
		$profiledetailArr["cityid"]			= $row['cityid'];
		$profiledetailArr["address1"]		= $row['address1'];
		$profiledetailArr["address2"]		= $row['address2'];
		$profiledetailArr["contactname"]	= $row['contactname'];
		$profiledetailArr["contactemail"]	= $row['contactemail'];
		$profiledetailArr["phone1"]			= $row['phone1'];
		$profiledetailArr["phone2"]			= $row['phone2'];
		$profiledetailArr["password"]		= $row['password'];
		$profiledetailArr["iswhatsapp1"]	= (int)$row['iswhatsapp1'];
		$profiledetailArr["paymenttype"]	= $row['paymenttype'];

		$StateSql	= "SELECT * FROM ".$Prefix."states ORDER BY name ASC";
		$StateEsql	= array();

		$StateQuery	= pdo_query($StateSql,$StateEsql);
		$StateNum	= pdo_num_rows($StateQuery);

		$stateindex	= 0;

		if($StateNum > 0)
		{
			while($staterows = pdo_fetch_assoc($StateQuery))
			{
				$isselected	= false;

				$stateid	= $staterows['id'];
				$statename	= $staterows['name'];

				if($profiledetailArr["stateid"] == $stateid)
				{
					$isselected	= true;
				}

				$StateListArr[$stateindex]['id']			= $stateid;
				$StateListArr[$stateindex]['name']			= $statename;
				$StateListArr[$stateindex]['isselected']	= $isselected;

				$stateindex++;
			}
		}

		$CitySql	= "SELECT * FROM ".$Prefix."cities WHERE stateid=:stateid ORDER BY name ASC";
		$CityEsql	= array("stateid"=>(int)$profiledetailArr["stateid"]);

		$CityQuery	= pdo_query($CitySql,$CityEsql);
		$CityNum	= pdo_num_rows($CityQuery);

		$cityindex	= 0;

		if($CityNum > 0)
		{
			while($cityrows = pdo_fetch_assoc($CityQuery))
			{
				$isselected	= false;

				$cityid		= $cityrows['id'];
				$cityname	= $cityrows['name'];

				if($profiledetailArr["cityid"] == $cityid)
				{
					$isselected	= true;
				}

				$CityListArr[$cityindex]['id']			= $cityid;
				$CityListArr[$cityindex]['name']		= $cityname;
				$CityListArr[$cityindex]['isselected']	= $isselected;

				$cityindex++;
			}
		}

		$response['success']	= true;
		$response['msg']		= "Profile detail fetched successfully.";
	}

	$response['profiledetail']	= $profiledetailArr;
	$response['statelist']		= $StateListArr;
	$response['citylist']		= $CityListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetState")
{
	$StateListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch detail.";

	$StateSql	= "SELECT * FROM ".$Prefix."states ORDER BY name ASC";
	$StateEsql	= array();

	$StateQuery	= pdo_query($StateSql,$StateEsql);
	$StateNum	= pdo_num_rows($StateQuery);

	$stateindex	= 0;

	if($StateNum > 0)
	{
		while($staterows = pdo_fetch_assoc($StateQuery))
		{
			$stateid	= $staterows['id'];
			$statename	= $staterows['name'];

			$StateListArr[$stateindex]['id']	= $stateid;
			$StateListArr[$stateindex]['name']	= $statename;

			$stateindex++;
		}
		$response['success']	= true;
		$response['msg']		= "State listed successfully.";
	}

	$response['statelist']	= $StateListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetStateCity")
{
	$CityListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch detail.";

	$CitySql	= "SELECT * FROM ".$Prefix."cities WHERE stateid=:stateid ORDER BY name ASC";
	$CityEsql	= array("stateid"=>(int)$_POST["stateid"]);

	$CityQuery	= pdo_query($CitySql,$CityEsql);
	$CityNum	= pdo_num_rows($CityQuery);

	if($CityNum > 0)
	{
		$cityindex	= 0;

		while($cityrows = pdo_fetch_assoc($CityQuery))
		{
			$isselected	= false;

			$cityid		= $cityrows['id'];
			$cityname	= $cityrows['name'];

			$CityListArr[$cityindex]['id']		= $cityid;
			$CityListArr[$cityindex]['name']	= $cityname;

			$cityindex++;
		}

		$response['success']	= true;
		$response['msg']		= "City listed successfully.";
	}

	$response['citylist']		= $CityListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "SaveProfileDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to update detail.";

	$iswhatsapp1	= 0;

	if($_POST['iswhatsapp1'] == "true" || $_POST['iswhatsapp1'] > 0)
	{
		$iswhatsapp1	= 1;
	}

	$Sql	= "UPDATE ".$Prefix."clients SET 
	contactname		=:contactname,
	contactemail	=:contactemail,
	phone1			=:phone1,
	phone2			=:phone2,
	iswhatsapp1		=:iswhatsapp1,
	pincode			=:pincode,
	stateid			=:stateid,
	cityid			=:cityid,
	address1		=:address1,
	address2		=:address2
	WHERE
	id				=:id";

	$Esql	= array(
		"contactname"	=>$_POST['contactname'],
		"contactemail"	=>$_POST['contactemail'],
		"phone1"		=>$_POST['phone1'],
		"phone2"		=>$_POST['phone2'],
		"iswhatsapp1"	=>(int)$iswhatsapp1,
		"pincode"		=>$_POST['pincode'],
		"stateid"		=>(int)$_POST['stateid'],
		"cityid"		=>(int)$_POST['cityid'],
		"address1"		=>$_POST['address1'],
		"address2"		=>$_POST['address2'],
		"id"			=>(int)$_POST['recordid']
	);

	$Query	= pdo_query($Sql,$Esql);

	if($Query)
	{
		$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
		$Esql	= array("id"=>(int)$_POST['recordid']);

		$Query	= pdo_query($Sql,$Esql);
		$rows	= pdo_fetch_assoc($Query);

		$clientdetail	= array("id"=>(int)$rows['id'],"clientname"=>$rows['clientname'],"clienttype"=>(int)$rows['clienttype'],"ispasswordupdate"=>(int)$rows['ispasswordupdate'],"stateid"=>(int)$rows['stateid'],"cityid"=>(int)$rows['cityid'],"pincode"=>$rows['pincode']);

		$response['success']		= true;
		$response['clientdetail']	= $clientdetail;
		$response['msg']			= "Profile updated successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ChangeDefaultPassword")
{
	$haserror	= false;

    $response['success']	= false;
    $response['msg']		= "Unable to update password.";

	$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND status=:status";
	$CheckEsql	= array("id"=>(int)$_POST['recordid'],"status"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$rows	= pdo_fetch_assoc($CheckQuery);

		$clientdetail	= array("id"=>(int)$rows['id'],"clientname"=>$rows['clientname'],"clienttype"=>(int)$rows['clienttype'],"ispasswordupdate"=>(int)$rows['ispasswordupdate'],"stateid"=>(int)$rows['stateid'],"cityid"=>(int)$rows['cityid'],"pincode"=>$rows['pincode']);

		if(($_POST['password'] != $rows['password']) && $_POST['ispasswordupdate'] > 0)
		{
			$haserror	= true;
			$response['msg'] = "Current password is incorrect.";
		}
		else
		{
			if($_POST['newpassword'] == "")
			{
				$haserror	= true;
				$response['msg'] = "Please enter your new password.";
			}
			else if(($_POST['newpassword'] != $_POST['confirmpassword']) && ($_POST['newpassword'] != ""))
			{
				$haserror	= true;
				$response['msg'] = "Passwords do not match.";
			}
			else if(($_POST['newpassword'] == $rows['password']) && ($_POST['newpassword'] != ""))
			{
				$haserror	= true;
				$response['msg'] = "New Password can not be same as old password.";
			}
		}

		if(!$haserror)
		{
			$Sql	= "UPDATE ".$Prefix."clients SET password=:password,ispasswordupdate=:ispasswordupdate WHERE id=:id";
			$Esql	= array("password"=>trim($_POST['newpassword']),"ispasswordupdate"=>1,"id"=>(int)$_POST['recordid']);

			$Query	= pdo_query($Sql,$Esql);

			if($Query)
			{
				$clientdetail['ispasswordupdate']	= 1;

				$response['success']		= true;
				$response['msg']			= "Password updated successfully.";
				$response['logintime']		= $createdon;
				$response['clientdetail']	= $clientdetail;
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ChangeLinemanPassword")
{
	$haserror	= false;

    $response['success']	= false;
    $response['msg']		= "Unable to update password.";

	$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE id=:id AND status=:status";
	$CheckEsql	= array("id"=>(int)$_POST['recordid'],"status"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$rows	= pdo_fetch_assoc($CheckQuery);

		$clientdetail	= array("id"=>(int)$rows['id'],"clientname"=>$rows['name'],"clienttype"=>2);

		if(($_POST['password'] != $rows['password']) && $_POST['ispasswordupdate'] > 0)
		{
			$haserror	= true;
			$response['msg'] = "Current password is incorrect.";
		}
		else
		{
			if($_POST['newpassword'] == "")
			{
				$haserror	= true;
				$response['msg'] = "Please enter your new password.";
			}
			else if(($_POST['newpassword'] != $_POST['confirmpassword']) && ($_POST['newpassword'] != ""))
			{
				$haserror	= true;
				$response['msg'] = "Passwords do not match.";
			}
			else if(($_POST['newpassword'] == $rows['password']) && ($_POST['newpassword'] != ""))
			{
				$haserror	= true;
				$response['msg'] = "New Password can not be same as old password.";
			}
		}

		if(!$haserror)
		{
			$Sql	= "UPDATE ".$Prefix."lineman SET password=:password WHERE id=:id";
			$Esql	= array("password"=>trim($_POST['newpassword']),"id"=>(int)$_POST['recordid']);

			$Query	= pdo_query($Sql,$Esql);

			if($Query)
			{
				$response['success']		= true;
				$response['msg']			= "Password updated successfully.";
				$response['logintime']		= $createdon;
				$response['clientdetail']	= $clientdetail;
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
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
		name		=:name,
		phone		=:phone,
		remark		=:remark,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
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

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
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
			$substituteid	= $rows['substituteid'];

			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['substitute']	= $GetAllLine[$substituteid]['name'];
			$RecordListArr[$index]['addeddate']		= date("j F, Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Line listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLineDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch line detail.";

	$sql	= "SELECT * FROM ".$Prefix."line WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$detailArr["name"]		= $row['name'];
		$detailArr["phone"]		= $row['phone'];
		$detailArr["remark"]	= $row['remark'];
		$detailArr["status"]	= $row['status'];

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
		name		=:name,
		phone		=:phone,
		remark		=:remark,
		status		=:status
		WHERE
		id			=:id";

		$Esql	= array(
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

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid=:lineid AND clientid=:clientid";
	$CheckEsql	= array("lineid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid']);

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
if($_POST['Mode'] == "AddCustomer")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add customer.";
    $response['toastmsg']	= "Unable to add customer.";

	$StartDate	= strtotime('today');

	/*
	if($_POST['phone'] == "")
	{
		$ErrorMsg	.= "Please enter phone.<br>";
	}
	if($_POST['email'] == "")
	{
		$ErrorMsg	.= "Please enter email.<br>";
	}
	if($_POST['pincode'] == "")
	{
		$ErrorMsg	.= "Please enter pincode.<br>";
	}
	if($_POST['stateid'] == "")
	{
		$ErrorMsg	.= "Please select a state.<br>";
	}
	if($_POST['cityid'] == "")
	{
		$ErrorMsg	.= "Please select a city.<br>";
	}*/
	if($_POST['lineid'] == "")
	{
		$ErrorMsg	.= "Please select a Line.<br>";
	}
	if($_POST['linemanid'] == "")
	{
		$ErrorMsg	.= "Please select a Lineman.<br>";
	}
	if($_POST['hawkerid'] == "")
	{
		$ErrorMsg	.= "Please select a Hawker.<br>";
	}
	if($_POST['address1'] == "")
	{
		$ErrorMsg	.= "Please enter customer address.<br>";
	}
	if($_POST['isdiscount'] > 0)
	{
		if($_POST['discount'] > 0)
		{
		}
		else
		{
			$ErrorMsg	.= "Please enter discount.<br>";
		}
	}

	/*if($_POST['isopeningbalance'] > 0)
	{
		if($_POST['openingbalance'] > 0)
		{
		}
		else
		{
			$ErrorMsg	.= "Please enter opening balance.";
		}
	}

	if($_POST['isincreasepricing'] > 0)
	{
		if($_POST['increasepricing'] > 0)
		{
		}
		else
		{
			$ErrorMsg	.= "Please enter increase pricing.";
		}
	}*/

	if($_POST['phone'] != "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A customer already exist with same phone.<br>";
			$response['toastmsg']	= "A customer already exist with same phone";
		}
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		
		if($CheckNum < 0)
		{
			$response['toastmsg']	= "Please enter all required field";
		}
	}

	$dob				= "";
	if($_POST['dob'] != "")
	{
		$dob	= strtotime($_POST['dob']);
	}

	$anniversarydate	= "";
	if($_POST['anniversarydate'] != "")
	{
		$anniversarydate	= strtotime($_POST['anniversarydate']);
	}

	if($haserror == false)
	{
		$CustomerCode = GetCustomerCode();

		$Sql	= "INSERT INTO ".$Prefix."customers SET 
		clientid			=:clientid,
		name				=:name,
		phone				=:phone,
		lineid				=:lineid,
		linemanid			=:linemanid,
		hawkerid			=:hawkerid,
		address1			=:address1,
		status				=:status,
		isdiscount			=:isdiscount,
		discount			=:discount,
		createdon			=:createdon,
		customerid			=:customerid,
		isopeningbalance	=:isopeningbalance,
		openingbalance		=:openingbalance,
		isincreasepricing	=:isincreasepricing,
		increasepricing		=:increasepricing,
		dob					=:dob,
		anniversarydate		=:anniversarydate";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"lineid"			=>(int)$_POST['lineid'],
			"linemanid"			=>(int)$_POST['linemanid'],
			"hawkerid"			=>(int)$_POST['hawkerid'],
			"address1"			=>$_POST['address1'],
			"status"			=>(int)$_POST['status'],
			"isdiscount"		=>(int)$_POST['isdiscount'],
			"discount"			=>(float)$_POST['discount'],
			"createdon"			=>$createdon,
			"customerid"		=>$CustomerCode,
			"isopeningbalance"	=>(int)$_POST['isopeningbalance'],
			"openingbalance"	=>(float)$_POST['openingbalance'],
			"isincreasepricing"	=>(int)$_POST['isincreasepricing'],
			"increasepricing"	=>(float)$_POST['increasepricing'],
			"dob"				=>(int)$dob,
			"anniversarydate"	=>(int)$anniversarydate
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			if($_POST['subscription'] == 1)
			{
				$customerid	= pdo_insert_id();

				$subscriptiondate	= "";

				if($_POST['subscriptiontype'] < 1 && trim($_POST['subscriptiondate']) != "")
				{
					$StartDate	= strtotime($_POST['subscriptiondate']);
				}
				
				if(!empty($_POST['inventorylist']))
				{
					foreach($_POST['inventorylist'] as $catkey=>$catrows)
					{
						$catid			= $catrows['id'];
						$inventoryArr	= $catrows['recordlist'];

						if(!empty($inventoryArr))
						{
							foreach($inventoryArr as $inventorykey =>$inventoryrows)
							{
								$inventoryid	= $inventoryrows['id'];
								$isassigned		= $inventoryrows['isassigned'];

								if($isassigned == "true")
								{
									$AssignSql	= "INSERT INTO ".$Prefix."subscriptions SET 
									customerid	=:customerid,
									inventoryid	=:inventoryid,
									startdate	=:startdate,
									enddate		=:enddate,
									createdon	=:createdon";

									$AssignEsql	= array(
										"customerid"	=>(int)$customerid,
										"inventoryid"	=>(int)$inventoryid,
										"startdate"		=>(int)$StartDate,
										"enddate"		=>0,
										"createdon"		=>$createdon
									);

									$AssignQuery	= pdo_query($AssignSql,$AssignEsql);
								}
							}
						}
					}
				}
			}

			$response['success']	= true;
			$response['msg']		= "Customer successfully added.";
			$response['toastmsg']	= "Customer successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllCustomers")
{
	$perpage = 2;

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
    $response['msg']		= "Unable to fetch customers.";

	$condition	= " AND deletedon <:deletedon";
	$Esql		= array("deletedon"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
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

	if($_POST['linemanid'] > 0)
	{
		$condition	.= " AND linemanid=:linemanid";
		$Esql['linemanid']	= (int)$_POST['linemanid'];
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
	}

	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition." ORDER BY status DESC,createdon DESC";

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

		$GetAllLine		= GetAllLine($_POST['clientid']);
		$GetAllLineman	= GetAllLineman($_POST['clientid']);
		$GetAllHawker	= GetAllHawker($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$createdon	= $rows['createdon'];
			$address1	= $rows['address1'];
			$customerid	= $rows['customerid'];
			$isdiscount	= $rows['isdiscount'];
			$discount	= $rows['discount'];
			$status		= $rows['status'];
			$firstname	= $rows['firstname'];
			$lastname	= $rows['lastname'];

			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}

			if($isdiscount > 0)
			{
				if($discount > 0)
				{
					$discount	= (float)$discount."%";
				}
				else
				{
					$discount	= "---";
				}
			}
			else
			{
				$discount	= "No Discount";
			}

			$line		= $GetAllLine[$rows['lineid']]['name'];
			$lineman	= $GetAllLineman[$rows['linemanid']]['name'];
			$hawker		= $GetAllHawker[$rows['hawkerid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}

			if($lineman == "")
			{
				$lineman	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['customerid']	= $customerid;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['line']			= $line;
			$RecordListArr[$index]['lineman']		= $lineman;
			$RecordListArr[$index]['hawker']		= $hawker;
			$RecordListArr[$index]['discount']		= $discount;
			$RecordListArr[$index]['address1']		= $address1;
			$RecordListArr[$index]['status']		= (int)$status;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Customers listed successfully.";
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
if($_POST['Mode'] == "GetAllSubscribeCustomer")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customers.";

	$condition	= " AND deletedon <:deletedon";
	$Esql		= array("deletedon"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition."";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$name2		= "";

			$id			= $rows['id'];
			$name		= $rows['name'];
			$firstname	= $rows['firstname'];
			$lastname	= $rows['lastname'];
			$phone		= $rows['phone'];
			$email		= $rows['email'];
			$createdon	= $rows['createdon'];
			$stateid	= $rows['stateid'];
			$cityid		= $rows['cityid'];
			$address1	= $rows['address1'];
			$customerid	= $rows['customerid'];

			if(trim($name) == "")
			{
				$name	= $firstname." ".$lastname;
			}
			$name2	= "#".$customerid." ".$name;

			if(trim($phone) != "")
			{
				$name2	.= " (".$phone.")";
			}

			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['name']			= $name2;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['email']			= $email;
			$RecordListArr[$index]['stateid']		= (int)$stateid;
			$RecordListArr[$index]['cityid']		= (int)$cityid;
			$RecordListArr[$index]['customerid']	= $customerid;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Customers listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerDetail")
{
	$CityListArr	= array();
	$StateListArr	= array();
	$LineArr		= array();
	$LinemanArr		= array();
	$HawkerArr		= array();

	$linename		= "";
	$linemanname	= "";
	$hawkername		= "";

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer detail.";

	$sql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid']);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$RecordListArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$dob				= $row['dob'];
		$anniversarydate	= $row['anniversarydate'];

		$dobtext			= "";
		$anniversarytext	= "";

		if($dob != "" && $dob > 0)
		{
			$dob		= date("Y-m-d",$dob);
			$dobtext	= date("j F, Y",$row['dob']);
		}
		else
		{
			$dobtext	= "---";
		}

		if($anniversarydate != "" && $anniversarydate > 0)
		{
			$anniversarydate	= date("Y-m-d",$anniversarydate);
			$anniversarytext	= date("j F, Y",$row['anniversarydate']);
		}
		else
		{
			$anniversarytext	= "---";
		}

		$name		= $row['name'];

		$firstname	= $row['firstname'];
		$lastname	= $row['lastname'];

		if(trim($name) == "")
		{
			$name	= $firstname." ".$lastname;
		}

		$RecordListArr["name"]				= $name;
		$RecordListArr["phone"]				= $row['phone'];
		$RecordListArr["email"]				= $row['email'];
		$RecordListArr["pincode"]			= $row['pincode'];
		$RecordListArr["stateid"]			= $row['stateid'];
		$RecordListArr["cityid"]			= $row['cityid'];
		$RecordListArr["lineid"]			= $row['lineid'];
		$RecordListArr["linemanid"]			= $row['linemanid'];
		$RecordListArr["hawkerid"]			= $row['hawkerid'];
		$RecordListArr["address1"]			= $row['address1'];
		$RecordListArr["address2"]			= $row['address2'];
		$RecordListArr["dob"]				= $dob;
		$RecordListArr["anniversarydate"]	= $anniversarydate;

		$RecordListArr["status"]			= (int)$row['status'];
		$RecordListArr["isdiscount"]		= (int)$row['isdiscount'];
		$RecordListArr["isopeningbalance"]	= (int)$row['isopeningbalance'];
		$RecordListArr["isincreasepricing"]	= (int)$row['isincreasepricing'];

		$RecordListArr["dobtext"]			= $dobtext;
		$RecordListArr["anniversarytext"]	= $anniversarytext;
		$RecordListArr["customerid"]		= $row['customerid'];
		$RecordListArr["phonetxt"]			= $row['phone'];
		if($RecordListArr["phonetxt"] == "")
		{
			$RecordListArr["phonetxt"]		= "--";
		}

		$discount			= (float)($row['discount'] * 100) / 100;
		$openingbalance		= (float)($row['openingbalance'] * 100) / 100;
		$increasepricing	= (float)($row['increasepricing'] * 100) / 100;

		if($discount > 0)
		{
			$RecordListArr["discount"]		= $discount;
			$RecordListArr["discounttxt"]	= $discount." %";
		}
		else
		{
			$RecordListArr["discount"]		= '';
			$RecordListArr["discounttxt"]	= '---';
		}

		if($openingbalance > 0)
		{
			$RecordListArr["openingbalance"]	= $openingbalance;
			$RecordListArr["openingbalancetxt"]	= $openingbalance;
		}
		else
		{
			$RecordListArr["openingbalance"]	= '';
			$RecordListArr["openingbalancetxt"]	= "---";
		}

		if($increasepricing > 0)
		{
			$RecordListArr["increasepricing"]		= $increasepricing;
			$RecordListArr["increasepricingtxt"]	= $increasepricing;
		}
		else
		{
			$RecordListArr["increasepricing"]		= '';
			$RecordListArr["increasepricingtxt"]	= '---';
		}

		$CustomerInventoryArr	= GetCustomerSubscriptions($_POST['recordid']);

		$InventoryArr	= array();

		if(!empty($CustomerInventoryArr))
		{
			$InventoryIDArr	= array();

			foreach($CustomerInventoryArr as $Key => $value)
			{
				$InventoryIDArr[]	= $value['id'];
			}
			$InventoryIDArr			= @array_filter(@array_unique($InventoryIDArr));
			$CustomerInventoryIDs	= @implode(",",$InventoryIDArr);
			
			if(trim($CustomerInventoryIDs) == "")
			{
				$CustomerInventoryIDs	= "-1";
			}

			$InventorySql	= "select * FROM ".$Prefix."inventory WHERE id IN(".$CustomerInventoryIDs.") AND status=:status ORDER BY name ASC";
			$InventoryEsql	= array("status"=>1);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			if($InventoryNum > 0)
			{
				while($inventoryrows = pdo_fetch_assoc($InventoryQuery))
				{
					$InventoryArr[]	= $inventoryrows['name'];
				}
			}
		}
		if(!empty($InventoryArr))
		{
			$InventoryStr	= implode(", ",$InventoryArr);
		}
		else
		{
			$InventoryStr	= "---";
		}

		$RecordListArr["inventorytext"]	= $InventoryStr;

		$StateSql	= "SELECT * FROM ".$Prefix."states";
		$StateEsql	= array();

		$StateQuery	= pdo_query($StateSql,$StateEsql);
		$StateNum	= pdo_num_rows($StateQuery);

		$stateindex	= 0;

		if($StateNum > 0)
		{
			while($staterows = pdo_fetch_assoc($StateQuery))
			{
				$isselected	= false;

				$stateid	= $staterows['id'];
				$statename	= $staterows['name'];

				if($RecordListArr["stateid"] == $stateid)
				{
					$isselected	= true;
				}

				$StateListArr[$stateindex]['id']			= $stateid;
				$StateListArr[$stateindex]['name']			= $statename;
				$StateListArr[$stateindex]['isselected']	= $isselected;

				$stateindex++;
			}
		}

		$CitySql	= "SELECT * FROM ".$Prefix."cities WHERE stateid=:stateid";
		$CityEsql	= array("stateid"=>(int)$RecordListArr["stateid"]);

		$CityQuery	= pdo_query($CitySql,$CityEsql);
		$CityNum	= pdo_num_rows($CityQuery);

		$cityindex	= 0;

		if($CityNum > 0)
		{
			while($cityrows = pdo_fetch_assoc($CityQuery))
			{
				$isselected	= false;

				$cityid		= $cityrows['id'];
				$cityname	= $cityrows['name'];

				if($RecordListArr["cityid"] == $cityid)
				{
					$isselected	= true;
				}

				$CityListArr[$cityindex]['id']			= $cityid;
				$CityListArr[$cityindex]['name']		= $cityname;
				$CityListArr[$cityindex]['isselected']	= $isselected;

				$cityindex++;
			}
		}

		$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
		$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);

		$index	= 0;

		if($Num > 0)
		{
			while($rows = pdo_fetch_assoc($Query))
			{
				$id		= $rows['id'];
				$name	= $rows['name'];

				$LineArr[$index]['id']		= $id;
				$LineArr[$index]['name']	= $name;

				if($id == $RecordListArr["lineid"])
				{
					$linename	= $name;
				}

				$index++;
			}
		}

		$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
		$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);

		$index	= 0;

		if($Num > 0)
		{
			while($rows = pdo_fetch_assoc($Query))
			{
				$id		= $rows['id'];
				$name	= $rows['name'];

				$LinemanArr[$index]['id']	= $id;
				$LinemanArr[$index]['name']	= $name;

				if($id == $RecordListArr["linemanid"])
				{
					$linemanname	= $name;
				}

				$index++;
			}
		}

		$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
		$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);

		$index	= 0;

		if($Num > 0)
		{
			while($rows = pdo_fetch_assoc($Query))
			{
				$id		= $rows['id'];
				$name	= $rows['name'];

				$HawkerArr[$index]['id']	= $id;
				$HawkerArr[$index]['name']	= $name;

				if($id == $RecordListArr["hawkerid"])
				{
					$hawkername	= $name;
				}

				$index++;
			}
		}

		$response['success']	= true;
		$response['msg']		= "Customer detail fetched successfully.";
	}

	$RecordListArr["linename"]		= $linename;
	$RecordListArr["linemanname"]	= $linemanname;
	$RecordListArr["hawkername"]	= $hawkername;

	$response['customerdetail']		= $RecordListArr;
	$response['statelist']			= $StateListArr;
	$response['citylist']			= $CityListArr;
	$response['linelist']			= $LineArr;
	$response['linemanlist']		= $LinemanArr;
	$response['hawkerlist']			= $HawkerArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditCustomer")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to update customer.";
    $response['toastmsg']	= "Unable to update customer.";

	/*
	if($_POST['phone'] == "")
	{
		$ErrorMsg	.= "Please enter phone.<br>";
	}
	if($_POST['email'] == "")
	{
		$ErrorMsg	.= "Please enter email.<br>";
	}
	if($_POST['pincode'] == "")
	{
		$ErrorMsg	.= "Please enter pincode.<br>";
	}
	if($_POST['stateid'] == "")
	{
		$ErrorMsg	.= "Please select a state.<br>";
	}
	if($_POST['cityid'] == "")
	{
		$ErrorMsg	.= "Please select a city.<br>";
	}*/
	if($_POST['lineid'] == "")
	{
		$ErrorMsg	.= "Please select a Line.<br>";
	}
	if($_POST['linemanid'] == "")
	{
		$ErrorMsg	.= "Please select a Lineman.<br>";
	}
	if($_POST['hawkerid'] == "")
	{
		$ErrorMsg	.= "Please select a Hawker.<br>";
	}
	if($_POST['address1'] == "")
	{
		$ErrorMsg	.= "Please enter customer address.<br>";
	}
	if($_POST['isdiscount'] > 0)
	{
		if($_POST['discount'] > 0)
		{
		}
		else
		{
			$ErrorMsg	.= "Please enter discount.<br>";
		}
	}

	if($_POST['phone'] != "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A customer already exist with same phone.<br>";
			$response['toastmsg']	= "A customer already exist with same phone";
		}
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;

		if($CheckNum < 0)
		{
			$response['toastmsg']	= "Please enter all required field";
		}
	}

	$dob				= "";
	if($_POST['dob'] != "")
	{
		$dob	= strtotime($_POST['dob']);
	}

	$anniversarydate	= "";
	if($_POST['anniversarydate'] != "")
	{
		$anniversarydate	= strtotime($_POST['anniversarydate']);
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."customers SET 
		name				=:name,
		phone				=:phone,
		email				=:email,
		lineid				=:lineid,
		linemanid			=:linemanid,
		hawkerid			=:hawkerid,
		address1			=:address1,
		status				=:status,
		isdiscount			=:isdiscount,
		discount			=:discount,
		isopeningbalance	=:isopeningbalance,
		openingbalance		=:openingbalance,
		isincreasepricing	=:isincreasepricing,
		increasepricing		=:increasepricing,
		dob					=:dob,
		anniversarydate		=:anniversarydate
		WHERE
		id					=:id
		AND
		clientid			=:clientid";

		$Esql	= array(
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"email"				=>$_POST['email'],
			"lineid"			=>(int)$_POST['lineid'],
			"linemanid"			=>(int)$_POST['linemanid'],
			"hawkerid"			=>(int)$_POST['hawkerid'],
			"address1"			=>$_POST['address1'],
			"status"			=>(int)$_POST['status'],
			"isdiscount"		=>(int)$_POST['isdiscount'],
			"discount"			=>(float)$_POST['discount'],
			"isopeningbalance"	=>(int)$_POST['isopeningbalance'],
			"openingbalance"	=>(float)$_POST['openingbalance'],
			"isincreasepricing"	=>(int)$_POST['isincreasepricing'],
			"increasepricing"	=>(float)$_POST['increasepricing'],
			"dob"				=>(int)$dob,
			"anniversarydate"	=>(int)$anniversarydate,
			"id"				=>(int)$_POST['recordid'],
			"clientid"			=>(int)$_POST['clientid'],
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			/*if($_POST['subscription'] == 1)
			{
				$customerid	= $_POST['recordid'];

				if(!empty($_POST['inventorylist']))
				{
					foreach($_POST['inventorylist'] as $catkey=>$catrows)
					{
						$catid			= $catrows['id'];
						$inventoryArr	= $catrows['recordlist'];

						if(!empty($inventoryArr))
						{
							foreach($inventoryArr as $inventorykey =>$inventoryrows)
							{
								$inventoryid	= $inventoryrows['id'];
								$isassigned		= $inventoryrows['isassigned'];

								if($isassigned == "true")
								{
									$StartDate	= strtotime('today');

									$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND enddate=:enddate AND inventoryid=:inventoryid";
									$CheckESQL	= array("enddate"=>(int)$StartDate,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

									$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
									$CheckNum	= pdo_num_rows($CheckQuery);

									if($CheckNum > 0)
									{
										$UpdateSQL = "UPDATE ".$Prefix."subscriptions SET enddate=:enddate WHERE customerid=:customerid AND inventoryid=:inventoryid";
										$UpdateESQL = array("enddate"=>0,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

										pdo_query($UpdateSQL,$UpdateESQL);
									}
									else
									{
										$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid AND enddate <:enddate";
										$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid,"enddate"=>1);
										$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
										$CheckNum	= pdo_num_rows($CheckQuery);

										if($CheckNum < 1)
										{
											$AssignSql	= "INSERT INTO ".$Prefix."subscriptions SET 
											customerid	=:customerid,
											inventoryid	=:inventoryid,
											startdate	=:startdate,
											enddate		=:enddate,
											createdon	=:createdon";

											$AssignEsql	= array(
												"customerid"	=>(int)$customerid,
												"inventoryid"	=>(int)$inventoryid,
												"startdate"		=>(int)$StartDate,
												"enddate"		=>0,
												"createdon"	=>time()
											);

											$AssignQuery	= pdo_query($AssignSql,$AssignEsql);
										}
									}
								}
								else
								{
									$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid ORDER BY id DESC LIMIT 1";
									$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

									$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
									$CheckNum	= pdo_num_rows($CheckQuery);

									if($CheckNum > 0)
									{
										$CheckRow = pdo_fetch_assoc($CheckQuery);

										$UpdateSQL = "UPDATE ".$Prefix."subscriptions SET enddate=:enddate WHERE customerid=:customerid AND inventoryid=:inventoryid";
										$UpdateESQL = array("enddate"=>$StartDate,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

										pdo_query($UpdateSQL,$UpdateESQL);
									}
								}
							}
						}
					}
				}
			}*/

			$response['success']	= true;
			$response['msg']		= "Customer successfully updated.";
			$response['toastmsg']	= "Customer successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteCustomer")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete customer, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid";
	$CheckEsql	= array("customerid"=>(int)$_POST['recordid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Customer can't be delete due to active subscription.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."customers SET 
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
		$Response['msg']		= "Customer deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAvailableInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

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

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			if($InventoryNum > 0)
			{
				$InventoryListArr	= array();

				$index	= 0;

				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id			= $rows['id'];
					$categoryid	= $rows['categoryid'];
					$name		= $rows['name'];
					$price		= $rows['price'];

					$InventoryListArr[$index]['id']			= (int)$id;
					$InventoryListArr[$index]['name']		= $name;
					$InventoryListArr[$index]['categoryid']	= (int)$categoryid;

					if(!empty($ClientInventoryData[$id]))
					{
						$inventorystatus	= $ClientInventoryData[$id]['status'];
						$inventoryprice		= $ClientInventoryData[$id]['price'];
						
						$InventoryListArr[$index]['isassigned']	= $inventorystatus;
						$InventoryListArr[$index]['price']		= (float)$inventoryprice;
					}
					else
					{
						$InventoryListArr[$index]['isassigned']	= 0;
						$InventoryListArr[$index]['price']		= (float)$price;
					}

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
		$response['msg']		= "Inventory listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AssignInventory")
{
    $response['success']	= false;
    $response['msg']		= "Unable to assign Inventory.";

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

		$Sql	= "UPDATE ".$Prefix."client_inventory_linker SET status=:status WHERE id=:id";
		$Esql	= array("status"=>(int)$isassigned,"id"=>(int)$recordid);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			if($isassigned > 0)
			{
				$response['msg']	= 'Inventory successfully assigned to your list';
			}
			else
			{
				$response['msg']	= 'Inventory successfully removed from your list';
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
				$response['msg']	= 'Inventory successfully assigned to your list';
			}
			else
			{
				$response['msg']	= 'Inventory successfully removed from your list';
			}
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GenerateInvoiceRequest")
{
	/*$MonthArr	= array("1"=>"January","2"=>"February","3"=>"March","4"=>"April","5"=>"May","6"=>"June","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");*/

	$Response['pricingerror']	= false;
	$Response['success']	= false;
    $Response['msg']		= "Oops something went wrong. Please try again.";
	
	if(strlen($_POST['month']) < 1)
	{
		$_POST['month'] = "0".$_POST['month'];
	}

	$PriceCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
	year		=:year
	AND
	month		=:month
	AND
	clientid	=:clientid";

	$PriceCheckEsql	= array(
		"year"			=>(int)$_POST['year'],
		"month"			=>(int)$_POST['month'],
		"clientid"		=>(int)$_POST['clientid']
	);

	$PriceCheckQuery	= pdo_query($PriceCheckSql,$PriceCheckEsql);
	$PriceCheckNum		= pdo_num_rows($PriceCheckQuery);

	if($PriceCheckNum < 1)
	{
		$Response['success']		= false;
		$Response['pricingerror']	= true;
			
		$Response['msg']			= "Please update pricing for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$CompletedCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
	year		=:year
	AND
	month		=:month
	AND
	clientid	=:clientid
	AND
	iscompleted	<>:iscompleted";

	$CompletedCheckEsql	= array(
		"year"			=>(int)$_POST['year'],
		"month"			=>(int)$_POST['month'],
		"clientid"		=>(int)$_POST['clientid'],
		"iscompleted"	=>1
	);

	$CompletedCheckQuery	= pdo_query($CompletedCheckSql,$CompletedCheckEsql);
	$CompletedCheckNum		= pdo_num_rows($CompletedCheckQuery);

	if($CompletedCheckNum > 0)
	{
		$Response['success']		= false;
		$Response['pricingerror']	= true;
		$Response['msg']			= "Please add all inventory pricing for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year";
	$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year']);
	
	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$RowCheck	=	pdo_fetch_assoc($CheckQuery);

		$Status		=	$RowCheck['status'];

		if($Status < 1)
		{
			$Response['success']		= false;
			$Response['msg']			= "Invoice generation for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." is already in queue to be processed.";
			$Response['toastmsg']		= "Invoice generation for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." is already in queue to be processed.";
		}
		else
		{
			$Response['success']		= false;
			$Response['msg']			= "Invoice(s) already generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
			$Response['toastmsg']		= "Invoice(s) already generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
		}
	}
	else
	{
		$Sql	= "INSERT INTO ".$Prefix."invoice_request_queue SET 
		clientid	=:clientid,
		month		=:month,
		year		=:year,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"		=>(int)$_POST['clientid'],
			"month"			=>(int)$_POST['month'],
			"year"			=>(int)$_POST["year"],
			"createdon"		=>time()
		);
		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$Response['success']	= true;
			$Response['msg']		= "Invoice(s) generation request for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." has been added to queue.";
			$Response['toastmsg']	= "Invoice(s) generation request for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." has been added to queue.";
		}	
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ReGenerateInvoiceRequest")
{

	$Response['success']		= false;
    $Response['pricingerror']	= false;
    $Response['msg']		= "Oops something went wrong. Please try again.";
	
	if(strlen($_POST['month']) < 1)
	{
		$_POST['month'] = "0".$_POST['month'];
	}

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year AND status < :status AND isprocessing =:isprocessing";
	$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year'],'isprocessing'=>1,'status'=>1);

	$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);
	
	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Unable to save pricing as invoice(s) generation is already in process for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'];
		$issuccess				= false;
	}	
	else
	{
		$PriceCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
		year		=:year
		AND
		month		=:month
		AND
		clientid	=:clientid";

		$PriceCheckEsql	= array(
			"year"			=>(int)$_POST['year'],
			"month"			=>(int)$_POST['month'],
			"clientid"		=>(int)$_POST['clientid']
		);

		$PriceCheckQuery	= pdo_query($PriceCheckSql,$PriceCheckEsql);
		$PriceCheckNum		= pdo_num_rows($PriceCheckQuery);
		if($PriceCheckNum < 1)
		{
			$Response['success']		= false;
			$Response['pricingerror']	= true;
			$Response['msg']			= "Please update pricing for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

			$json = json_encode($Response);
			echo $json;
			die;
		}

		$CompletedCheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
		year		=:year
		AND
		month		=:month
		AND
		clientid	=:clientid
		AND
		iscompleted	<>:iscompleted";

		$CompletedCheckEsql	= array(
			"year"			=>(int)$_POST['year'],
			"month"			=>(int)$_POST['month'],
			"clientid"		=>(int)$_POST['clientid'],
			"iscompleted"	=>1
		);

		$CompletedCheckQuery	= pdo_query($CompletedCheckSql,$CompletedCheckEsql);
		$CompletedCheckNum		= pdo_num_rows($CompletedCheckQuery);

		if($CompletedCheckNum > 0)
		{
			$Response['success']		= false;
			$Response['pricingerror']	= true;

			$Response['msg']			= "Please add all inventory pricing for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year']." to generate invoices.";

			$json = json_encode($Response);
			echo $json;
			die;
		}

		$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year";
		$CheckESQL = array("clientid"=>(int)$_POST['clientid'],"month"=>(int)$_POST['month'],"year"=>(int)$_POST['year']);
		
		$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$RowCheck	=	pdo_fetch_assoc($CheckQuery);

			$Status			=	$RowCheck['status'];
			$ID				=	$RowCheck['id'];

			$Sql	= "UPDATE ".$Prefix."invoice_request_queue SET 
			status		=:status,
			isprocessing=:isprocessing
			WHERE
			id	= :id";
			$Esql	= array(
				"status"		=>0,
				"isprocessing"	=>0,
				"id"			=>(int)$ID
			);
			$Query	= pdo_query($Sql,$Esql);

			$Response['success']		= false;
			$Response['msg']			= "Invoice(s) queue re-generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
			$Response['toastmsg']		= "Invoice(s) queue re-generated for ".$MonthArr[(int)$_POST['month']]." ".$_POST['year'].".";
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AddSubscription")
{
	/*$_POST['CustomerID'] = 1;
	$_POST['InventoryID'] = 1;*/

	$StartDate	= strtotime('today');

    $Response['success']	= false;
    $Response['msg']		= "Oops something went wrong. Please try again.";

	$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND enddate=:enddate AND inventoryid=:inventoryid";
	$CheckESQL	= array("enddate"=>(int)$StartDate,"customerid"=>(int)$_POST['CustomerID'],"inventoryid"=>(int)$_POST["InventoryID"]);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$UpdateSQL = "UPDATE ".$Prefix."subscriptions SET enddate=:enddate WHERE customerid=:customerid AND inventoryid=:inventoryid";
		$UpdateESQL = array("enddate"=>0,"customerid"=>(int)$_POST['CustomerID'],"inventoryid"=>(int)$_POST["InventoryID"]);

		pdo_query($UpdateSQL,$UpdateESQL);

		$Response['success']	= true;
		$Response['msg']		= "Subscription updated successfully.";
		$Response['toastmsg']	= "Subscription updated successfully.";
	}
	else
	{
		$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid AND enddate <:enddate";
		$CheckESQL	= array("customerid"=>(int)$_POST['CustomerID'],"inventoryid"=>(int)$_POST["InventoryID"],"enddate"=>1);
		$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum < 1)
		{
			$Sql	= "INSERT INTO ".$Prefix."subscriptions SET 
			customerid	=:customerid,
			inventoryid	=:inventoryid,
			startdate	=:startdate,
			enddate		=:enddate,
			createdon	=:createdon";

			$Esql	= array(
				"customerid"	=>(int)$_POST['CustomerID'],
				"inventoryid"	=>(int)$_POST['InventoryID'],
				"startdate"		=>(int)$StartDate,
				"enddate"		=>0,
				"createdon"	=>time()
			);

			$Query	= pdo_query($Sql,$Esql);

			if($Query)
			{
				$Response['success']	= true;
				$Response['msg']		= "Subscription added successfully.";
				$Response['toastmsg']	= "Subscription added successfully.";
			}
		}
		else
		{
			$Response['success']	= true;
			$Response['msg']		= "Subscription already exists.";
			$Response['toastmsg']	= "Subscription already exists.";
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "RemoveSubscription")
{
	/*$_POST['CustomerID'] = 1;
	$_POST['InventoryID'] = 1;*/

	$EndDate = strtotime('today');

    $Response['success']	= false;
    $Response['msg']		= "Oops something went wrong. Please try again.";

	$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid ORDER BY id DESC LIMIT 1";
	$CheckESQL	= array("customerid"=>(int)$_POST['CustomerID'],"inventoryid"=>(int)$_POST["InventoryID"]);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);

	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$CheckRow = pdo_fetch_assoc($CheckQuery);

		$UpdateSQL = "UPDATE ".$Prefix."subscriptions SET enddate=:enddate WHERE customerid=:customerid AND inventoryid=:inventoryid";
		$UpdateESQL = array("enddate"=>$EndDate,"customerid"=>(int)$_POST['CustomerID'],"inventoryid"=>(int)$_POST["InventoryID"]);

		pdo_query($UpdateSQL,$UpdateESQL);

		$Response['success']	= true;
		$Response['msg']		= "Subscription removed successfully.";
		$Response['toastmsg']	= "Subscription removed successfully.";
	}
    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AddHoliday")
{
	/*$_POST['ClientID'] = "1";
	$_POST['CustomerType'] = "0"; // 0 - All Customers, 1 - Specific
	$_POST['CustomerID'] = "1"; // specific customer
	$_POST['InventoryType'] = "0"; // 0 - All Inventory, 1 - Specific
	$_POST['InventoryID'] = "1"; // specific inventory
	
	$_POST['StartDate']	 = "04/1/2020";
	$_POST['EndDate']	 = "04/18/2020";
	$_POST['Reason']	 = "Due to standard reasons.";
	
	if($_POST['CustomerType'] < 1)
	{
		$_POST['CustomerID'] =0;
	}
	if($_POST['InventoryType'] < 1)
	{
		$_POST['InventoryID'] =0;
	}
	*/

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

	if($ErrorMessage == "")
	{
		$StartDate	= strtotime($_POST['StartDate']);
		$EndDate	= strtotime($_POST['EndDate']) + 86399;

		$Response['success']	= false;
		$Response['msg']		= "Oops something went wrong. Please try again.";
		$Response['toastmsg']	= "Oops something went wrong. Please try again.";

		$CheckSQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND customertype=:customertype AND customerid=:customerid AND inventorytype=:inventorytype AND inventoryid=:inventoryid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon";
		$CheckESQL		= array("clientid"=>(int)$_POST['ClientID'],"customertype"=>(int)$_POST['CustomerType'],"customerid"=>$_POST["CustomerID"],"inventorytype"=>(int)$_POST['InventoryType'],'inventoryid'=>(int)$_POST['InventoryID'],"date1"=>(int)$StartDate,"date2"=>(int)$EndDate,"date3"=>(int)$StartDate,"date4"=>(int)$EndDate,"deletedon"=>1);

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
			$Sql	= "INSERT INTO ".$Prefix."holidays SET 
			clientid		=:clientid,
			customertype	=:customertype,
			customerid		=:customerid,
			inventorytype	=:inventorytype,
			inventoryid		=:inventoryid,
			startdate		=:startdate,
			enddate			=:enddate,
			reason			=:reason,
			createdon		=:createdon";

			$Esql	= array(
				"clientid"			=>(int)$_POST['ClientID'],
				"customertype"		=>$_POST['CustomerType'],
				"customerid"		=>$_POST['CustomerID'],
				"inventorytype"		=>$_POST['InventoryType'],
				"inventoryid"		=>(int)$_POST['InventoryID'],
				"startdate"			=>(int)$StartDate,
				"enddate"			=>(int)$EndDate,
				"reason"			=>$_POST['Reason'],
				"createdon"			=>time()
			);

			$Query	= pdo_query($Sql,$Esql);
			if($Query)
			{
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

	$UpdateSQL = "UPDATE ".$Prefix."holidays SET deletedon=:deletedon WHERE id=:id AND clientid=:clientid";
	
	$UpdateESQL = array("deletedon"=>time(),'id'=>(int)$_POST['HolidayID'],"clientid"=>(int)$_POST['ClientID']);

	$Query	= pdo_query($UpdateSQL,$UpdateESQL);

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

		$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE 1 AND id=:id";
		$CustomerEsql	= array("id"=>(int)$detailArr["customerid"]);

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


	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon";
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
if($_POST['Mode'] == "GetHolidayListByClientID")
{
	$perpage = 2;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}
	$CheckSQL		= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND deletedon <:deletedon ORDER BY enddate DESC";
	$CheckESQL		= array("clientid"=>(int)$_POST['ClientID'],"deletedon"=>1);

	$CheckQuery		=	pdo_query($CheckSQL,$CheckESQL);
	$Num			=   pdo_num_rows($CheckQuery);

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
	$Arr			=   array();

	if($Num2 > 0)
	{
		$AllCustomerArr = GetAllCustomerNameByClientID($_POST['ClientID']);
		$AllInventory	= GetInventoryNames();
		$Index      = 0; 
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

			$StartDate		= date("j F, Y",$StartDateUnix);
			$EndDate		= date("j F, Y",$EndDateUnix);
			
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
			$Arr[$Index]['addeddate']		= date("j F, Y",$createdon);
			
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

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ".$condition." ORDER BY name ASC";

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

		if($_POST['type'] == "substitute")
		{
			$RecordSetArr[$index]['id']		= '0';
			$RecordSetArr[$index]['name']	= 'None';

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

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= strtotime('today');

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE 1 AND id=:id";
	$CustomerEsql	= array("id"=>(int)$_POST['customerid']);

	$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	$name2	= "Select";

	if($CustomerNum > 0)
	{
		$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

		$customername	= $CustomerRows['name'];
		$firstname		= $CustomerRows['firstname'];
		$lastname		= $CustomerRows['lastname'];
		$customerid		= $CustomerRows['customerid'];
		$phone			= $CustomerRows['phone'];

		if(trim($customername) == "")
		{
			$customername	= $firstname." ".$lastname;
		}
		$name2	= "#".$customerid." ".$customername;

		if(trim($phone) != "")
		{
			$name2	.= " (".$phone.")";
		}
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$SubscriptionData	= GetCustomerSubscriptions($_POST['customerid'],$StartDate);
		$ClientInventoryData = GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

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
							$InventoryListArr[$index]['id']			= (int)$id;
							$InventoryListArr[$index]['name']		= $name;
							$InventoryListArr[$index]['categoryid']	= (int)$categoryid;

							$inventoryprice		= $ClientInventoryData[$id]['price'];

							$InventoryListArr[$index]['price']	= (float)$inventoryprice;

							if(!empty($SubscriptionData[$id]))
							{
								$InventoryListArr[$index]['isassigned']			= true;
								$InventoryListArr[$index]['subscriptiondate']	= date("j F, Y",$SubscriptionData[$id]['startdate']);
							}
							else
							{
								$InventoryListArr[$index]['isassigned']			= false;
								$InventoryListArr[$index]['subscriptiondate']	= '';
							}

							$index++;
						}
					}
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
		$response['msg']		= "Customer Inventory listed successfully.";
	}
	$response['customername']	= $name2;
	$response['inventorylist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllInventory")
{
	$index			= 0;
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

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


						$RecordListArr[$index]['id']			= (int)$id;
						$RecordListArr[$index]['name']			= $name;
						$RecordListArr[$index]['categoryid']	= (int)$categoryid;
						$RecordListArr[$index]['isassigned']	= $inventorystatus;
						$RecordListArr[$index]['price']			= (float)$inventoryprice;

						$index++;
					}
				}
			}
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Inventory listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list year.";

	$currentyear	= date("Y");
	/*$startyear	= $currentyear - $InvoiceYears;*/

	$RecordSetArr	= array();

	$index	= 0;

	for($yearloop = $currentyear; $yearloop >= $startyear; $yearloop--)
	{
		$RecordSetArr[$index]['index']	= $index+1;
		$RecordSetArr[$index]['year']	= $yearloop;

		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['recordset']		= $RecordSetArr;
		$response['defaultyear']	= $currentyear;
		$response['msg']			= "Year listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceFilterYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list year.";

	$currentyear	= date("Y");
	/*$startyear	= $currentyear - $InvoiceYears;*/

	$RecordSetArr	= array();

	$index	= 0;
	$startyear = '2019';
	for($yearloop = $currentyear; $yearloop >= $startyear; $yearloop--)
	{
		$RecordSetArr[$index]['name']	= "".$yearloop."";

		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']		= true;
		$response['recordset']		= $RecordSetArr;
		$response['msg']			= "Year listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}

/*$_POST['Mode']	= "GetInvoiceMonthByYear";
$_POST['year']	= 2020;*/

if($_POST['Mode'] == "GetInvoiceMonthByYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list month.";

	$RecordSetArr	= array();
	$index	= 0;

	if($_POST['year'] != "")
	{
		$currentyear	= date("Y");

		$startmonth	= "01";
		$endmonth	= "12";

		if($_POST['year'] == $currentyear)
		{
			$endmonth	= date("m");
		}

		for($monthloop = $startmonth; $monthloop <= $endmonth; $monthloop++)
		{
			$month	= "0".(int)$monthloop;
			if($monthloop > 9)
			{
				$month	= (int)$monthloop;
			}

			
			$hasinvoice	= CheckClientInvoiceByYearMonth($_POST['clientid'], $month, $_POST['year']);

			$InvoiceSQL 	= "SELECT COUNT(*) AS C, SUM(finalamount) as Amt FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid";
			$InvoiceESQL 	= array("invoicemonth"=>(int)$month,"invoiceyear"=>(int)$_POST["year"],"clientid"=>(int)$_POST['clientid']);
		
			$InvoiceQuery		= pdo_query($InvoiceSQL,$InvoiceESQL);
			$InvoiceRow			= pdo_fetch_assoc($InvoiceQuery);
			$TotalInvoice		= $InvoiceRow["C"];
			$TotalInvoiceAmt	= $InvoiceRow["Amt"];

			$RecordSetArr[$index]['index']		= $index+1;
			$RecordSetArr[$index]['id']		= (int)$month;
			$RecordSetArr[$index]['count']		= (int)$TotalInvoice;
			$RecordSetArr[$index]['total']		= (float)$TotalInvoiceAmt;
			$RecordSetArr[$index]['hasinvoice']	= $hasinvoice;
			$RecordSetArr[$index]['name']		= date("F",strtotime($month."/01/".$_POST['year']));
			$index++;
		}
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['recordset']	= $RecordSetArr;
		$response['msg']		= "Year listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetMonthByYear")
{
	$response['success']	= false;
	$response['msg']		= "Unable to list month.";

	$RecordSetArr	= array();
	$index	= 0;


	$startmonth	= "01";
	$endmonth	= "12";

	for($monthloop = $startmonth; $monthloop <= $endmonth; $monthloop++)
	{
		if($monthloop < 10)
		{
			$month	= "0".(int)$monthloop;
		}
		else
		{
			$month = $monthloop;			
		}
		if($monthloop > 9)
		{
			$month2	= (int)$monthloop;
		}

		$RecordSetArr[$index]['month']		= (int)$month;
		$RecordSetArr[$index]['name']		= FUllMonthName($month);
		$index++;
	}

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['recordset']	= $RecordSetArr;
		$response['msg']		= "Month listed successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetStateByPinCode")
{
	$response['success'] = false;
	$response['msg']	= "Unable to fetch state and city by pin code.";

	$pincode	= $_POST["pincode"];

	$Sql	= "SELECT * FROM ".$Prefix."pincodes WHERE pincode=:pincode";
	$Esql	= array("pincode"=>$pincode);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$GetAllStates	= GetAllStates();
		$GetAllCity		= GetAllCity();

		$rows	= pdo_fetch_assoc($Query);

		$State	= $rows['state'];
		$City	= $rows['city'];

		$StateID	= $GetAllStates[strtolower(trim($State))];
		$CityID		= $GetAllCity[$StateID][strtolower(trim($City))];

		$response['success']	= true;
		$response['msg']		= "State and City fetched by pin code.";

		$response['state']	= $StateID;
		$response['city']	= $CityID;
	}

	$json = json_encode($response);

	echo $json;
}
if($_POST['Mode'] == "GetSubscribeInventory")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= strtotime('today');
	/*$totaldays	= cal_days_in_month(CAL_GREGORIAN, $_POST['month'], $_POST['year']);*/

	if ($_POST['year']%4 == 0)
	{
		$daysInMonth = array(1=>31, 2=>29, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}
	else
	{
		$daysInMonth = array(1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}

	$totaldays	= $daysInMonth[$_POST['month']];

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData	= GetClientInventory($_POST['clientid'],$_POST["stateid"],$_POST["cityid"]);

		$ActiveSubscriptionsData	= GetActiveCustomerSubscriptions($_POST['clientid']);

		$ClientInventoryPricing	= ClientInventoryPricing($_POST['clientid'],$_POST["year"],$_POST["month"]);
		$ClientInventoryPricingByDate	= ClientInventoryPricingByDate($_POST['clientid'],$_POST["year"],$_POST["month"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$index	= 0;

				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id				= $rows['id'];
					$categoryid		= $rows['categoryid'];
					$name			= $rows['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 0; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}

					if(!empty($ClientInventoryData[$id]) && !empty($ActiveSubscriptionsData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$InventoryListArr[$index]['id']				= (int)$id;
							$InventoryListArr[$index]['categoryid']		= (int)$categoryid;
							$InventoryListArr[$index]['name']			= $name;
							$InventoryListArr[$index]['pricingtype']	= $pricingtype;
							$InventoryListArr[$index]['days']			= $days;
							$InventoryListArr[$index]['price']			= $price;

							$dayindex	= 0;

							$dateListArr	= array();

							for($dateloop = 1; $dateloop <= $totaldays; $dateloop++)
							{
								$date	= $dateloop;
								$price	= "";

								if(!empty($ClientInventoryPricingByDate[$id]))
								{
									$PricingByDate	= $ClientInventoryPricingByDate[$id];

									if(!empty($PricingByDate[$dateloop]))
									{
										$price	= $PricingByDate[$dateloop]['price'];
									}
								}
								if($dateloop < 10)
								{
									$dateListArr[$dayindex]['displayname']	= "0".$dateloop;
								}
								else
								{
									$dateListArr[$dayindex]['displayname']	= $dateloop;
								}
								$dateListArr[$dayindex]['date']			= $dateloop;
								$dateListArr[$dayindex]['dateprice']	= $price;

								$dayindex++;
							}
							$InventoryListArr[$index]['datepricing']	= $dateListArr;

							/*$inventoryprice		= $ClientInventoryData[$id]['price'];
							$InventoryListArr[$index]['price']	= (float)$inventoryprice;
							$InventoryListArr[$index]['isassigned']	= true;*/

							$index++;
						}
					}
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
		$response['msg']		= "Customer Inventory listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;
	$response['totaldays']		= (int)$totaldays;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AddLineman")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add line man.";

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
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND clientid=:clientid AND deletedon < :deletedon";
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
			$response['toastmsg']	= "A line man already exist with same phone";
		}
		
	}

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."lineman SET 
		clientid	=:clientid,
		name		=:name,
		phone		=:phone,
		password	=:password,
		remark		=:remark,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"name"		=>$_POST['name'],
			"phone"		=>$_POST['phone'],
			"password"	=>$_POST['password'],
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
			$response['msg']		= "Line man successfully added.";
			$response['toastmsg']	= "Line man successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllLineman")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch line man.";

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$createdon	= $rows['createdon'];

			$RecordListArr[$index]['id']		= $id;
			$RecordListArr[$index]['name']		= $name;
			$RecordListArr[$index]['phone']		= $phone;
			$RecordListArr[$index]['addeddate']	= date("j F, Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Line man listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLinemanDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch line man detail.";

	$sql	= "SELECT * FROM ".$Prefix."lineman WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$detailArr["name"]		= $row['name'];
		$detailArr["phone"]		= $row['phone'];
		$detailArr["password"]	= $row['password'];
		$detailArr["remark"]	= $row['remark'];
		$detailArr["status"]	= $row['status'];

		$response['success']	= true;
		$response['msg']		= "Line man detail fetched successfully.";
	}

	$response['linemandetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditLineman")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to save line man.";

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
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A Line man already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']	= $ErrorMsg;
		$response['toastmsg']	= "There is a error to update record.";
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A Line man already exist with same phone";
		}
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."lineman SET 
		name		=:name,
		phone		=:phone,
		password	=:password,
		remark		=:remark,
		status		=:status
		WHERE
		id			=:id";

		$Esql	= array(
			"name"		=>$_POST['name'],
			"phone"		=>$_POST['phone'],
			"password"	=>$_POST['password'],
			"remark"	=>$_POST['remarks'],
			"status"	=>(int)$_POST['status'],
			"id"		=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "line man successfully updated.";
			$response['toastmsg']	= "line man successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteLineman")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete Line man, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE linemanid=:linemanid AND clientid=:clientid";
	$CheckEsql	= array("linemanid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Lineman can't be deleted due to customer exist.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."lineman SET 
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
		$Response['msg']		= "Line man deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLineman")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch Line man.";

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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

			$RecordSetArr[$index]['id']		= $id;
			$RecordSetArr[$index]['name']	= $name;

			$index++;
		}
		$response['success']	= true;
		$response['msg']		= "Line listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AddHawker")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add hawker.";

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
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE phone=:phone AND clientid=:clientid AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A hawker already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
		
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A hawker already exist with same phone";
		}
		
	}

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."hawker SET 
		clientid	=:clientid,
		name		=:name,
		phone		=:phone,
		status		=:status,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"name"		=>$_POST['name'],
			"phone"		=>$_POST['phone'],
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
			$response['msg']		= "hawker successfully added.";
			$response['toastmsg']	= "hawker successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllHawker")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch hawker.";

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$createdon	= $rows['createdon'];

			$RecordListArr[$index]['id']		= $id;
			$RecordListArr[$index]['name']		= $name;
			$RecordListArr[$index]['phone']		= $phone;
			$RecordListArr[$index]['addeddate']	= date("j F, Y",$createdon);

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "hawker listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetHawkerDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch hawker detail.";

	$sql	= "SELECT * FROM ".$Prefix."hawker WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$detailArr["name"]		= $row['name'];
		$detailArr["phone"]		= $row['phone'];
		$detailArr["status"]	= $row['status'];

		$response['success']	= true;
		$response['msg']		= "hawker detail fetched successfully.";
	}

	$response['hawkerdetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditHawker")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to save hawker.";

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
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A hawker already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']	= $ErrorMsg;
		$response['toastmsg']	= "There is a error to update record.";
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A hawker already exist with same phone";
		}
	}

	if($haserror == false)
	{
		$Sql	= "UPDATE ".$Prefix."hawker SET 
		name		=:name,
		phone		=:phone,
		status		=:status
		WHERE
		id			=:id";

		$Esql	= array(
			"name"		=>$_POST['name'],
			"phone"		=>$_POST['phone'],
			"status"	=>(int)$_POST['status'],
			"id"		=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "hawker successfully updated.";
			$response['toastmsg']	= "hawker successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteHawker")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete hawker, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE hawkerid=:hawkerid AND clientid=:clientid";
	$CheckEsql	= array("hawkerid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid']);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Hawker can't be deleted due to customer exist.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."hawker SET 
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
		$Response['msg']		= "hawker deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetHawker")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch hawker.";

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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

			$RecordSetArr[$index]['id']		= $id;
			$RecordSetArr[$index]['name']	= $name;

			$index++;
		}
		$response['success']	= true;
		$response['msg']		= "hawker listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

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
    $response['msg']		= "Unable to fetch agent inventory detail.";

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

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_POST["stateid"],"cityid"=>(int)$_POST["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

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
							$InventoryListArr[$index]['id']			= (int)$id;
							$InventoryListArr[$index]['name']		= $name;
							$InventoryListArr[$index]['categoryid']	= (int)$categoryid;

							$inventoryprice		= $ClientInventoryData[$id]['price'];

							$InventoryListArr[$index]['price']	= (float)$inventoryprice;

							$InventoryListArr[$index]['isassigned']	= false;

							$index++;
						}
					}
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
		$response['msg']		= "Available Inventory listed successfully.";
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
    $response['msg']		= "Unable to save inventory pricing.";
	$issuccess				= false;

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
							pricingtype	=:pricingtype,
							iscompleted	=:iscompleted
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
								"iscompleted"	=>1,
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
							iscompleted	=:iscompleted,
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
								"iscompleted"	=>1,
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
									$date			= $pricingrows['date'];
									$dateprice		= $pricingrows['dateprice'];

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
										$totalpricingdays	+= 1;
										$totalpricing		+= (float)$dateprice;
									}
									else
									{
										if($CheckNum2 > 0)
										{
											$checkrows2	= pdo_fetch_assoc($CheckQuery2);
											$checkid2	= $checkrows2['id'];

											$DelSql		= "DELETE FROM ".$Prefix."inventory_date_price_linker WHERE id=:id";
											
											$DelEsql2	= array("id"=>(int)$checkid2);
											$DelQuery2	= pdo_query($DelSql2,$DelEsql2);
										}
									}
								}
							}

							$iscompleted	= 0;
							
							if(@count($recordlistrows['datepricing']) == $totalpricingdays)
							{
								$iscompleted	= 1;
							}

							$UpdateInventoryDaysSql		= "UPDATE ".$Prefix."inventory_days_price_linker SET days=:days,price=:price,iscompleted=:iscompleted WHERE id=:id";
							$UpdateInventoryDaysEsql	= array("days"=>(int)$totalpricingdays,"iscompleted"=>(int)$iscompleted,"price"=>(float)$totalpricing,"id"=>(int)$inventorylinkerid);

							$UpdateInventoryDaysQuery	= pdo_query($UpdateInventoryDaysSql,$UpdateInventoryDaysEsql);

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
					}
				}
			}
		}
		if($issuccess)
		{
			$response['success']	= true;
			$response['msg']		= "Inventory pricing save sucessfully.";
		}
	}
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "AddCustomerPayment")
{
    $response['success']	= false;
    $response['msg']		= "Unable to save payment.";

	$Sql	= "INSERT INTO ".$Prefix."customer_payments SET 
	clientid	=:clientid,
	customerid	=:customerid,
	amount		=:amount,
	paymenttype	=:paymenttype,
	createdon	=:createdon";

	$Esql	= array(
		"clientid"	=>(int)$_POST['clientid'],
		"customerid"=>(int)$_POST['recordid'],
		"amount"	=>(float)$_POST['paymentamount'],
		"paymenttype"	=>'c',
		"createdon"	=>time()
	);
	$Query = pdo_query($Sql,$Esql);
	if($Query)
	{
		$recordid	= pdo_insert_id();

		$response['success']	= true;
		$response['recordid']	= $recordid;
		$response['name']		= $_POST['name'];
		$response['msg']		= "Payment successfully added.";
		$response['toastmsg']	= "Payment successfully added.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "UpdateSubscription")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to update Subscription.";

	$isupdated	= false;
	$hasinvoice	= false;

	$customerid	= $_POST['customerid'];

	$CheckInvoiceSql	= "SELECT * FROM ".$Prefix."invoices WHERE customerid=:customerid AND clientid=:clientid AND ispaid<>:ispaid";
	$CheckInvoiceEsql	= array("customerid"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid'],"ispaid"=>1);

	$CheckInvoiceQuery	= pdo_query($CheckInvoiceSql,$CheckInvoiceEsql);
	$CheckInvoiceNum	= pdo_num_rows($CheckInvoiceQuery);

	if($CheckInvoiceNum > 0)
	{
		/*$response['msg'] = "Subscription can't be change due to an unpaid invoice.";

		$haserror	= true;*/
		$hasinvoice	= true;
	}

	if(!$haserror)
	{
		if(!empty($_POST['inventorylist']))
		{
			foreach($_POST['inventorylist'] as $catkey=>$catrows)
			{
				$catid			= $catrows['id'];
				$inventoryArr	= $catrows['recordlist'];

				if(!empty($inventoryArr))
				{
					foreach($inventoryArr as $inventorykey =>$inventoryrows)
					{
						$inventoryid	= $inventoryrows['id'];
						$isassigned		= $inventoryrows['isassigned'];

						if($isassigned == "true")
						{
							if($_POST['subscriptiontype'] < 1 && trim($_POST['subscriptiondate']) != "")
							{
								$StartDate	= strtotime($_POST['subscriptiondate']);
							}

							$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid";
							$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

							$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
							$CheckNum	= pdo_num_rows($CheckQuery);

							if($CheckNum > 0)
							{
								$UpdateSQL = "UPDATE ".$Prefix."subscriptions SET startdate=:startdate,enddate=:enddate WHERE customerid=:customerid AND inventoryid=:inventoryid";
								$UpdateESQL = array("startdate"=>(int)$StartDate,"enddate"=>0,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);
								pdo_query($UpdateSQL,$UpdateESQL);
								$isupdated	= true;
							}
							else
							{
								$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid AND enddate <:enddate";
								$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid,"enddate"=>1);
								$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
								$CheckNum	= pdo_num_rows($CheckQuery);

								if($CheckNum < 1)
								{
									$AssignSql	= "INSERT INTO ".$Prefix."subscriptions SET 
									customerid	=:customerid,
									inventoryid	=:inventoryid,
									startdate	=:startdate,
									enddate		=:enddate,
									createdon	=:createdon";

									$AssignEsql	= array(
										"customerid"	=>(int)$customerid,
										"inventoryid"	=>(int)$inventoryid,
										"startdate"		=>(int)$StartDate,
										"enddate"		=>0,
										"createdon"	=>time()
									);

									$AssignQuery	= pdo_query($AssignSql,$AssignEsql);

									if($AssignQuery)
									{
										$isupdated	= true;
									}
								}
							}
						}
						else
						{
							if($hasinvoice == false)
							{
								$CheckSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND inventoryid=:inventoryid ORDER BY id DESC LIMIT 1";
								$CheckESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

								$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
								$CheckNum	= pdo_num_rows($CheckQuery);

								if($CheckNum > 0)
								{
									$CheckRow = pdo_fetch_assoc($CheckQuery);

									$UpdateSQL = "UPDATE ".$Prefix."subscriptions SET enddate=:enddate WHERE customerid=:customerid AND inventoryid=:inventoryid";
									$UpdateESQL = array("enddate"=>$StartDate,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

									$UpdateQuery = pdo_query($UpdateSQL,$UpdateESQL);

									if($UpdateQuery)
									{
										$isupdated	= true;
									}
								}
							}
						}
					}
				}
			}
		}
	}

	if($isupdated)
	{
		$response['success']	= true;
		$response['msg']		= "Subscription successfully updated.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetLedger")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch ledger.";
    $response['grandtotal']	= 0.00;

	if($_POST['customerid'] > 0)
	{
		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
		$CustESQL   = array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		if($CustNum > 0)
		{
			$CustRow			= pdo_fetch_assoc($CustQuery);
			$IsOpeningBalance	= $CustRow['isopeningbalance'];
			$Createdon			= $CustRow['createdon'];
			if($IsOpeningBalance > 0)
			{
				$OpeningBalance		= $CustRow['openingbalance'];
			}
			else
			{
				$OpeningBalance		= 0;
			}

			$customername	= $CustRow['name'];
			if(trim($customername) == "")
			{
				$customername	= "Unnamed #".$customerid;
			}

			$DateArr		= array();
			$ItemNameArr	= array();
			$AmountDueArr	= array();
			$AmountPaidArr		= array();

			if($OpeningBalance > 0)
			{
				$DateArr[]			= $Createdon;
				$ItemNameArr[]		= "Opening Balance";
				$AmountDueArr[]		= 0;
				$AmountPaidArr[]		= 0;
			}

			$InvoiceSQL		= "SELECT * FROM ".$Prefix."invoices WHERE customerid=:customerid ORDER BY invoicedate";
			$InvoiceESQL	= array("customerid"=>(int)$_POST['customerid']);
			$InvoiceQuery	= pdo_query($InvoiceSQL,$InvoiceESQL);
			$InvoiceNum		= pdo_num_rows($InvoiceQuery);
			if($InvoiceNum	> 0)
			{
				While($InvoiceRow = pdo_fetch_assoc($InvoiceQuery))
				{
					$Month			= $InvoiceRow['invoicemonth'];
					$Year			= $InvoiceRow['invoiceyear'];
					$Desc			= "Invoice for ".ShortMonthName($Month).", ".$Year;
					$ItemNameArr[]	= $Desc;
					$DateArr[]		= $InvoiceRow['invoicedate'];
					$AmountDueArr[]	= $InvoiceRow['finalamount'];

					$AmountPaidArr[]	= 0;
				}
			}
			$PaySQL		= "SELECT * FROM ".$Prefix."customer_payments WHERE customerid=:customerid ORDER BY createdon ASC";
			$PayESQL	= array("customerid"=>(int)$_POST['customerid']);
			$PayQuery	= pdo_query($PaySQL,$PayESQL);
			$PayNum		= pdo_num_rows($PayQuery);
			
			if($PayNum	> 0)
			{
				While($PayRow = pdo_fetch_assoc($PayQuery))
				{
					$Desc			= "Payment";
					$ItemNameArr[]	= $Desc;
					$DateArr[]		= $PayRow['createdon'];
					$AmountPaidArr[]	= $PayRow['amount'];
					$AmountDueArr[]	= 0;
				}
			}

			array_multisort($DateArr,SORT_ASC,$ItemNameArr,$AmountPaidArr,$AmountDueArr);
			$RecordsArr = array();
			if(!empty($DateArr))
			{
				$Index = 0;
				$Balance	= 0;
				$GrandTotal	= 0;
				foreach($DateArr as $key => $value)
				{
					$ItemDesc			= $ItemNameArr[$key];
					$AmountDue			= $AmountDueArr[$key]; 
					$AmountPaid			= $AmountPaidArr[$key];

					$RecordsArr[$Index]['date']	= date("d/m/Y",$value); 
					
					if($ItemDesc == "Opening Balance")
					{
						$RecordsArr[$Index]['balance']	= $OpeningBalance; 
						$Balance						= $OpeningBalance;
						if($Balance > 0)
						{
							$AmountDue = $Balance;
						}
						else
						{
							$AmountPaid = $Balance;
						}
					}
					else
					{
						if($AmountDue > 0)
						{
							$Balance	=  $Balance + $AmountDue; 
						}
						if($AmountPaid > 0)
						{
							$Balance	=  $Balance - $AmountPaid; 
						}
					}

					$RecordsArr[$Index]['item']		= $ItemDesc;
					$RecordsArr[$Index]['due']		= number_format($AmountDue,2); 
					$RecordsArr[$Index]['paid']		= number_format($AmountPaid,2);
					$RecordsArr[$Index]['balance']	= number_format($Balance,2);
					
					$Index++;
				}
			}
			$response['success']	= true;
			$response['ledgerlist']	= $RecordsArr;
			$response['grandtotal']	= number_format($Balance,2);
			$response['msg']		= "ledger created successfully.";
		}
	}
	$response['customername']	= $customername;
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetOutstandingReport")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch outstanding report.";

	$totalopeningbalance		= 0;
	$totalcustomer				= 0;
	$totalinvoice				= 0;
	$totalpayments				= 0;
	$totaloutstandingbalance	= 0;

	if($_POST['clientid'] > 0)
	{
		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon";
		$CustESQL   = array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$totalcustomer	= $CustNum;

		if($CustNum > 0)
		{
			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$totalopeningbalance	+= $custrows['openingbalance'];
			}
		}


		$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid";
		$InvoiceEsql	= array("clientid"=>(int)$_POST['clientid']);

		$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
		$InvoiceNum		= pdo_num_rows($InvoiceQuery);

		$totalinvoice	= $InvoiceNum;

		if($InvoiceNum > 0)
		{
			while($invoicerows = pdo_fetch_assoc($InvoiceQuery))
			{
				$ispaid	= $invoicerows['ispaid'];
				
				if($ispaid > 0)
				{
					/*$totalpayments	+= $invoicerows['totalamount'];*/
				}
				else
				{
					$totaloutstandingbalance	+= $invoicerows['totalamount'];
				}
			}
		}

		$PaySQL		= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid ORDER BY createdon ASC";
		$PayESQL	= array("clientid"=>(int)$_POST['clientid']);
		$PayQuery	= pdo_query($PaySQL,$PayESQL);
		$PayNum		= pdo_num_rows($PayQuery);
		
		if($PayNum	> 0)
		{
			While($PayRow = pdo_fetch_assoc($PayQuery))
			{
				$totalpayments	+= $PayRow['amount'];
			}
		}

		/*$totalpayments	= 100;*/

		$remaingbalance	= $totaloutstandingbalance - $totalpayments;

		$RecordSet['openingbalance']		= number_format($totalopeningbalance,2);
		$RecordSet['totalcustomer']			= (int)$totalcustomer;
		$RecordSet['totalinvoice']			= (int)$totalinvoice;
		$RecordSet['totalpayments']			= number_format($totalpayments,2);
		$RecordSet['graphpayments']			= (float)(($totalpayments / $totaloutstandingbalance) * 100)/100;
		$RecordSet['outstandingbalance']	= number_format($totaloutstandingbalance,2);
		$RecordSet['remaingbalance']		= number_format($remaingbalance,2);

		$response['success']	= true;
		$response['msg']		= "outstanding report fetched successfully.";
		$response['recordset']	= $RecordSet;
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetCustomerInvoices")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch invoices.";

	$RecordSet				= array();

	$InvoiceEsql	= array("clientid"=>(int)$_POST['clientid']);
	$condition		= "";

	$CustomerSql	= "SELECT * FROM ".$Prefix."customers WHERE 1 AND id=:id";
	$CustomerEsql	= array("id"=>(int)$_POST['customerid']);

	$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	$name2	= "Select";

	if($CustomerNum > 0)
	{
		$CustomerRows	= pdo_fetch_assoc($CustomerQuery);

		$customername	= $CustomerRows['name'];
		$firstname		= $CustomerRows['firstname'];
		$lastname		= $CustomerRows['lastname'];
		$customerid		= $CustomerRows['customerid'];
		$phone			= $CustomerRows['phone'];

		if(trim($customername) == "")
		{
			$customername	= $firstname." ".$lastname;
		}
		$name2	= "#".$customerid." ".$customername;

		if(trim($phone) != "")
		{
			$name2	.= " (".$phone.")";
		}
	}

	if($_POST['customerid'] > 0)
	{
		$condition		.= " AND customerid=:customerid";
		$InvoiceEsql['customerid']	= (int)$_POST['customerid'];
	}

	$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE 1 ".$condition." AND clientid=:clientid AND ispaid < :ispaid";
	$InvoiceEsql['ispaid']	= 1;

	$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
	$InvoiceNum		= pdo_num_rows($InvoiceQuery);

	$index	= 0;

	if($InvoiceNum > 0)
	{
		while($rows = pdo_fetch_assoc($InvoiceQuery))
		{
			$RecordSet[$index]['id']		= $rows['id'];
			$RecordSet[$index]['createdon']	= date("j F, Y",$rows['createdon']);
			$RecordSet[$index]['amount']	= $rows['totalamount'];

			$index++;
		}
	}

	if(!empty($RecordSet))
	{
		$response['success']		= true;
		$response['msg']			= "Unable to fetch invoices.";
		$response['invoicelist']	= $RecordSet;
	}
	$response['customername']	= $name2;

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetInvoiceDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch invoice detail.";
	$RecordSet				= array();

	$SQL		= "SELECT * FROM ".$Prefix."invoices WHERE id=:id";
	$ESQL		= array("id"=>(int)$_POST['orderid']);

	$OrderQuery	= pdo_query($SQL,$ESQL);
	$OrderNum	= pdo_num_rows($OrderQuery);

	if($OrderNum > 0)
	{
		$orderrows = pdo_fetch_assoc($OrderQuery);

		$Floor				= "";
		$OrderID			= $orderrows['id'];
		$ClientID			= $orderrows['clientid'];
		$CustomerID			= $orderrows['customerid'];
		$InvoiceNumber		= $orderrows['invoiceid'];
		$InvoiceMonth		= $orderrows['invoicemonth'];
		$InvoiceYear		= $orderrows['invoiceyear'];
		$InvoiceUnixDate	= $orderrows['invoicedate'];
		$InvoiceDate		= date("j F, Y",$InvoiceUnixDate);
		$IsPaid				= $orderrows['ispaid'];
		$PaidStatus			= "Not Paid";

		$CustomerNumber	= GetCustomerID($CustomerID);

		if($IsPaid > 0)
		{
			$PaidStatus	= 'Paid';
		}

		$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
		$ClientESQL		= array("id"=>($ClientID));

		$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
		$ClientNum		= pdo_num_rows($ClientQuery);
		
		if($ClientNum > 0)
		{
			$ClientRow		= pdo_fetch_assoc($ClientQuery);
			
			$AgentName		= $ClientRow['clientname'];
			$AgentAddress	= $ClientRow['invoiceaddress'];
			$AgentPhone		= $ClientRow['invoicephone'];		
		}

		$CustomerName		= $orderrows["customername"];
		$CustomerPhone		= $orderrows["customerphone"];
		$CreatedOn			= $orderrows['createdon'];
		
		$Address			= @$orderrows['customeraddress1'];
		$Address2			= @$orderrows['customeraddress2'];
		$PinCode			= @$orderrows['customerpincode'];
		
		if(trim($CustomerPhone) =="")
		{
			$CustomerPhone = "--";
		}

		$CityName			= $orderrows['customercity'];
		$StateName			= $orderrows['customerstate'];
		$StateName			= $orderrows['customerstate'];
		$Discount			= $orderrows['discount'];	
		$TotalAmount		= $orderrows['totalamount'];	
		$FinalAmount		= $orderrows['finalamount'];	
		$ConvenienceCharge	= $orderrows['conveniencecharge'];	
		
		$FullAddress	= $Address;

		if(trim($Address2) !='')
		{
			if(trim($Address) !="")
			{
				$FullAddress .=" ".$Address2;
			}
			else
			{
				$FullAddress =$Address2;
			}
		}

		$IsKYCDocs = "";

		$OrderDetSQL		= "SELECT * FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
		$OrderDetESQL		= array("invoiceid"=>(int)$OrderID);
		$OrderDetailQuery	= pdo_query($OrderDetSQL,$OrderDetESQL);
		$Num				= pdo_num_rows($OrderDetailQuery);
		$GrandTotal		= 0;
		$TotalSaving	= 0;

		$RecordSet['custnumber']	= $CustomerNumber;
		$RecordSet['custname']		= $CustomerName;
		$RecordSet['custaddress']	= $FullAddress;
		$RecordSet['custphone']		= $CustomerPhone;
		$RecordSet['invoicenumber']	= $InvoiceNumber;
		$RecordSet['invoicedate']	= $InvoiceDate;
		$RecordSet['invoicemonth']	= $MonthArr[$InvoiceMonth];
		$RecordSet['invoiceyear']	= $InvoiceYear;

		$index	= 0;
		$ItemDetailArr	= array();

		if($Num > 0)
		{
			while($ordrow = pdo_fetch_assoc($OrderDetailQuery))
			{
				$ODID			= $ordrow["id"];
				$Price			= $ordrow["price"];
				$Quantity		= $ordrow["qty"];
				$TotalPrice		= $ordrow["totalprice"];
				$ItemName		= $ordrow["inventoryname"];
				$LineTotal		= $TotalPrice;
				$GrandTotal		+= $LineTotal;

				$ItemDetailArr[$index]['index']		= $index+1;
				$ItemDetailArr[$index]['itemname']	= $ItemName;
				$ItemDetailArr[$index]['price']		= @number_format($Price,2);
				$ItemDetailArr[$index]['quantity']	= $Quantity;
				$ItemDetailArr[$index]['linetotal']	= @number_format($LineTotal,2);

				$index++;
			}
		}

		$TempTotal	= $GrandTotal - $Discount;

		$RecordSet['itemdetails']	= $ItemDetailArr;
		$RecordSet['subtotal']		= @number_format($GrandTotal,2);
		$RecordSet['discount']		= @number_format($Discount,2);
		$RecordSet['finalamount']	= @number_format($TempTotal,2);
	}

	if(!empty($RecordSet))
	{
		$response['success']		= true;
		$response['msg']			= "Invoice detail fetched successfully.";
		$response['invoicedetail']	= $RecordSet;
	}

	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == 'CreateInvoices')
{
    $response['success']	= false;
    $response['msg']		= "Unable to create invoices.";

	$SQL	= "SELECT * FROM ".$Prefix."admin ";
	$ESQL	= array();

	$AdmQuery	= pdo_query($SQL,$ESQL);
	$AdmNum		= pdo_num_rows($AdmQuery);
	if($AdmNum > 0)
	{
		$AdmRow	= pdo_fetch_assoc($AdmQuery);
		$GlobalCovenienceCharge = $AdmRow['conveniencecharge'];
	}

	$GetAllCity = GetAllCityNames();
	$GetAllStates = GetAllStateNames();
	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND status <:status AND isprocessing <:isprocessing ORDER BY id ASC";
	$CheckESQL = array("status"=>1,'clientid'=>$_POST['clientid'],"isprocessing"=>1);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);

	$CheckNum	= pdo_num_rows($CheckQuery);
	$CurrentTime = time();
	if($CheckNum > 0)
	{
		while($CheckRow = pdo_fetch_assoc($CheckQuery))
		{
			$RequestID	= $CheckRow['id'];
			$ClientID	= $CheckRow['clientid'];
			$Month		= $CheckRow['month'];
			$Year		= $CheckRow['year'];
			$isprocessing	= $CheckRow['isprocessing'];

			$CheckSQL2		= "SELECT COUNT(*) as C FROM ".$Prefix."invoice_request_queue WHERE id=:id AND year=:year AND month=:month AND isprocessing < :isprocessing AND status <:status";
			$CheckESQL2		= array("isprocessing"=>1,'id'=>(int)$RequestID,"year"=>$Year,"month"=>$Month,"status"=>1);
			$CheckQuery2	= pdo_query($CheckSQL2,$CheckESQL2);
			$RowCheck		= pdo_fetch_assoc($CheckQuery2);
			$CheckCount	= $RowCheck["C"];

			if($CheckCount > 0)
			{
				$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET isprocessing=:isprocessing WHERE id=:id";
				$UpdateESQL = array("isprocessing"=>1,"id"=>(int)$RequestID);
				
				pdo_query($UpdateSQL,$UpdateESQL);
			}
			else
			{
				continue;
			}
			
			$InvoiceNumber  = GetNextInvoiceID($ClientID);

			$ClientSQL		= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND deletedon < :deletedon";
			$ClientESQL		= array("id"=>(int)$ClientID,"deletedon"=>1);
			
			$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
			$ClientNum		= pdo_num_rows($ClientQuery);
			if($ClientNum > 0)
			{
			   $ClientRow	= pdo_fetch_assoc($ClientQuery);
			   $conveniencechargetype = $ClientRow['conveniencechargetype'];
			   if($conveniencechargetype > 0)
			   {
				$conveniencecharge = $ClientRow['conveniencecharge'];
			   }
			   else
			   {
				  $conveniencecharge = $GlobalCovenienceCharge;
			   }
			}
			else
			{
				continue;
			}

			$ClientInventoryPriceArr = GetActiveSubscriptionByClientID($ClientID,$Month,$Year);
			$ClientInventoryStr = '';
			if(!empty($ClientInventoryPriceArr))
			{
				foreach($ClientInventoryPriceArr as $key => $value)
				{
					  $ClientInventoryStr .=$key.","; 
				}
				$ClientInventoryStr .= "@@";
				$ClientInventoryStr = str_replace(",@@","",$ClientInventoryStr);
			}
			else
			{
				$ClientInventoryStr = '-1';
			}
			
			$CheckStartDate		= strtotime($Month."/01/".$Year);
			$LastDayofMonth		= date("t",$StartDate);				
			$CheckEndDate		= strtotime($Month."/".$LastDayofMonth."/".$Year)+86399;	
				

			$InvoiceDate	= strtotime($Month."/01/".$Year);
			$InvoiceDateEnd = $InvoiceDate+86399;
			

			$CheckInvoiceSQL	= "SELECT * FROM ".$Prefix."invoices WHERE invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND clientid=:clientid GROUP BY customerid";
			$CheckInvoiceESQL	= array("invoicemonth"=>(int)$Month,"invoiceyear"=>(int)$Year,"clientid"=>(int)$ClientID);
			$CheckInvoiceQuery	= pdo_query($CheckInvoiceSQL,$CheckInvoiceESQL);

			$CheckInvoiceNum	= pdo_num_rows($CheckInvoiceQuery);
			$CustomersArr		= array();
			$InvoiceCustArr		= array();

			if($CheckInvoiceNum > 0)
			{
				while($CheckInvoiceRow = pdo_fetch_assoc($CheckInvoiceQuery))
				{
					$TepmCustomerID			= $CheckInvoiceRow['customerid']; 
					$TempInvoiceID			= $CheckInvoiceRow['id']; 
					$TempInvoiceNumber		= $CheckInvoiceRow['invoiceid']; 
					
					$CustomersArr[] = $TepmCustomerID; 	
					
					$InvoiceCustArr[$TepmCustomerID]['id'] 		 = $TempInvoiceID;
					$InvoiceCustArr[$TepmCustomerID]['invoiceid'] = $TempInvoiceNumber;
				}
			}
			$ExtEsqlArr = array();
			$CustomerStr = "";
			$extarg = "";
			/*if(!empty($CustomersArr))
			{
				$CustomerStr	= implode(",",$CustomersArr);
				$extarg			= " AND NOT find_in_set(id, :allcustomerids)";
				$ExtEsqlArr['allcustomerids']  = $CustomerStr;
			}*/
			$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status $extarg ORDER BY id ASC";
			
			$CustESQL	= array("clientid"=>(int)$ClientID,"deletedon"=>1,"status"=>1);
			$CustESQL2	= array_merge($CustESQL,$ExtEsqlArr);
			
			$CustQuery	= pdo_query($CustSQL,$CustESQL2);
			$CustNum	= pdo_num_rows($CustQuery);
			
			$InvoiceCreatedCounter = 0;
			$CustomerCounter = 0;
			if($CustNum > 0)
			{
				while($CustRow	= pdo_fetch_assoc($CustQuery))
				{
					$SubscriptionArr	= array();
					
					$CustomerID			= $CustRow["id"];
					$CustomerName		= $CustRow["name"];
					$CustomerEmail		= $CustRow["email"];
					$CustomerAddress1	= $CustRow["address1"];
					$CustomerAddress2	= $CustRow["address2"];
					$CustomerCityID		= $CustRow["cityid"];
					$CustomerStateID	= $CustRow["stateid"];
					$CustomerPinCode	= $CustRow["pincode"];
					$CustomerPhone		= $CustRow["phone"];
					$IsDiscount			= $CustRow["isdiscount"];
					$DiscountPercent	= $CustRow["discount"];

					/*$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (enddate <:enddate ) AND inventoryid IN (".$ClientInventoryStr.")";
					$SubscriptionESQL	= array("startdate"=>$CheckEndDate,"customerid"=>$CustomerID); */

					$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (enddate <:enddate ) AND inventoryid IN (".$ClientInventoryStr.")";
					$SubscriptionESQL	= array("enddate"=>1,"customerid"=>$CustomerID);

					$SubscriptionQuery	= pdo_query($SubscriptionSQL,$SubscriptionESQL);
					$SubscriptionNum		= pdo_num_rows($SubscriptionQuery);
					if($SubscriptionNum > 0)
					{
						$TempStartDate	= $CheckStartDate;
						$TempEndDate	= $CheckEndDate;
						while($SRow	= pdo_fetch_assoc($SubscriptionQuery))
						{
							$InventoryID = $SRow['inventoryid'];
							$StartDate	 = $SRow['startdate'];
							//$EndDate	 = $SRow['enddate'];
							
							$UsePartialBilling = 0;
							if($StartDate > 0)
							{
								if($StartDate >= $CheckStartDate && $StartDate <= $CheckEndDate)
								{
									$UsePartialBilling = 1;
								}
							}
								
							if($StartDate < $CheckEndDate)
							{
								$SubscriptionArr[$InventoryID]['inventoryid']	= $InventoryID;
								$SubscriptionArr[$InventoryID]['startdate']		= $StartDate;
								$SubscriptionArr[$InventoryID]['partialbilling']= $UsePartialBilling;
							}
						}
					}
					if(!empty($SubscriptionArr))
					{
						$InvoiceTillDate = strtotime('today');
						$OldInvoiceID 	= $InvoiceCustArr[$CustomerID]['id'];
						
						$InvoiceSQL		= "INSERT INTO ".$Prefix."invoices SET
										   clientid		=:clientid,
										   customerid	=:customerid,
										   invoicedate	=:invoicedate,
										   invoicemonth	=:invoicemonth,
										   invoiceyear	=:invoiceyear,
											customername=:customername,  
										   customeraddress1	=:customeraddress1,
										   customeraddress2	=:customeraddress2,
										   customercity	=:customercity,
										   customerstate=:customerstate,
										   customerpincode=:customerpincode,
										   customerphone=:customerphone,
										   customerstateid=:customerstateid,
										   customercityid	=:customercityid,
										   invoiceid	=:invoiceid,
										   createdon	=:createdon
											";
						
						if($OldInvoiceID > 0)
						{
							$InvoiceNumber  = $InvoiceCustArr[$CustomerID]['invoiceid'];
						}
						$InvoiceESQL	= array(
									"clientid"			=>(int)$ClientID,
									"customerid"		=>(int)$CustomerID,
									"invoicedate"		=>(int)$InvoiceTillDate,
									"invoicemonth"		=>(int)$Month,
									"invoiceyear"		=>(int)$Year,
									"customername"		=> $CustomerName,
									"customeraddress1"	=>$CustomerAddress1,
									"customeraddress2"	=>$CustomerAddress2,
									"customercity"		=>$GetAllCity[$CustomerCityID],
									"customerstate"		=>$GetAllStates[$CustomerStateID],
									"customerpincode"	=>$CustomerPinCode,
									"customerphone"		=>$CustomerPhone,
									"customerstateid"	=>(int)$CustomerStateID,
									"customercityid"	=>(int)$CustomerCityID,
									"invoiceid"			=>(int)$InvoiceNumber,
									"createdon"			=>(int)$CurrentTime
										);

						if($OldInvoiceID > 0)
						{
							$InvoiceSQL			= str_replace("INSERT INTO ","UPDATE ",$InvoiceSQL);
							$InvoiceSQL			.= "WHERE id=:id"; 
							
							$InvoiceESQL['id']	= $OldInvoiceID;	
							
						}
						$InvoiceQuery	= pdo_query($InvoiceSQL,$InvoiceESQL);
						if($InvoiceQuery)
						{
							if($OldInvoiceID > 0)
							{
								$InvoiceID	= $OldInvoiceID;

								$DelSQL		= "DELETE FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid";
								$DelESQL	= array("invoiceid"=>(int)$InvoiceID);
								pdo_query($DelSQL,$DelESQL);
							}
							else
							{
								$InvoiceID	= pdo_insert_id();
								$InvoiceNumber++;
							}
						
							$GrandTotal	= 0;
							foreach($SubscriptionArr as $key => $value)
							{
								$InventoryID		= $value['inventoryid'];
								$PartialBilling		= $value['partialbilling'];
								$StartDate			= $value['startdate'];

								$PricingType		= $ClientInventoryPriceArr[$InventoryID]['pricingtype'];

								if($PartialBilling > 0)
								{
									if($PricingType > 0)
									{
										$StartDay		= date("d",$StartDate);
										$DatePricingArr =  $ClientInventoryPriceArr[$InventoryID]['dailyprice'];

										$TotalCost		= 0;
										$NoDays		= 0;
										
										foreach($DatePricingArr as $Day => $Price)
										{
											if($Day >= $StartDay)
											{
												$TotalCost	+=$Price;
												
												$NoDays	+= 1;
											}
										}
									}
									else
									{
										$TempPrice	= $ClientInventoryPriceArr[$InventoryID]['price'];
										$TempDays	= $ClientInventoryPriceArr[$InventoryID]['days'];
										
										$UnitPrice	= $TempPrice / $TempDays;
										$TotalDays	= floor(($CheckEndDate - $StartDate) / 86400);
										$UnitDays	= $TotalDays / $LastDayofMonth;
										
										$NoDays		= floor($UnitDays * $LastDayofMonth);
										$TotalCost  = round(($NoDays * $UnitPrice),2);
									}
								}
								else
								{
									$TotalCost			= $ClientInventoryPriceArr[$InventoryID]['price'];
									$NoDays				= $ClientInventoryPriceArr[$InventoryID]['days'];
								}
							   
								$InventoryName		= $ClientInventoryPriceArr[$InventoryID]['inventoryname'];
								$InventoryCatID		= $ClientInventoryPriceArr[$InventoryID]['categoryid'];
								$InventoryCatName	= $ClientInventoryPriceArr[$InventoryID]['categoryname'];
									
								$GrandTotal	= $GrandTotal + $TotalCost;
								
								$InvoiceDetSQL = "INSERT INTO ".$Prefix."invoice_details SET
								invoiceid		= :invoiceid, 
								clientid		= :clientid, 
								customerid		= :customerid, 
								inventoryid		= :inventoryid, 
								qty				= :qty, 
								price			= :price, 
								inventoryname	= :inventoryname, 
								inventorycatid	= :inventorycatid, 
								inventorycatname= :inventorycatname, 
								totalprice		= :totalprice, 
								createdon		= :createdon
								";

								$InvoiceDetESQL = array(
									"invoiceid"		=>(int)$InvoiceID,
									"clientid"		=>(int)$ClientID,
									"customerid"	=>(int)$CustomerID,
									"qty"			=>(int)$NoDays,
									"price"			=>(float)$TotalCost,
									"inventoryname"	=>$InventoryName,
									"inventoryid"	=>(int)$InventoryID,
									"inventorycatname"=>$InventoryCatName,
									"inventorycatid"=>(int)$InventoryCatID,
									"totalprice"	=>(float)$TotalCost,
									"createdon"		=>(int)$CurrentTime
								);
								$DetailQuery = pdo_query($InvoiceDetSQL,$InvoiceDetESQL);
							}

							if($IsDiscount > 0)
							{
								$Discount = $GrandTotal * ($DiscountPercent/100);
								$FinalAmountToPay =  $GrandTotal - $Discount;
							}
							else
							{
								$Discount = 0;
								$FinalAmountToPay	= $GrandTotal;
							}

							$CovenienceCharge = $FinalAmountToPay * ($conveniencecharge/100);

							//$CovenienceCharge = ceil($FinalAmountToPay * ($conveniencecharge/100));

							$FinalAmountToPay	= $FinalAmountToPay + ceil($CovenienceCharge);
						  	$UpdateSQL	= "UPDATE ".$Prefix."invoices SET totalamount=:totalamount,finalamount=:finalamount,discount=:discount,conveniencecharge=:conveniencecharge WHERE id=:id";
							$UpdateESQL	= array("totalamount"=>(float)$GrandTotal,"finalamount"=>(float)$FinalAmountToPay,"discount"=>(float)$Discount,"conveniencecharge"=>$CovenienceCharge,"id"=>(int)$InvoiceID);
							pdo_query($UpdateSQL,$UpdateESQL);

							if($FinalAmountToPay > 0)
							{
								$CustomerArr['name'] = $CustomerName;
								if($IsLiveProcess > 0)
								{
									$CustomerArr['phone'] = $CustomerPhone;
									$CustomerArr['email'] = $CustomerEmail;
								}
								else
								{
									$CustomerArr['phone'] = $BetaPhone;
									$CustomerArr['email'] = $BetaEmail;
								}
								$Notes	= "Payment For Invoice#".$InvoiceNumber. " for Month Of ".$MonthArr[$Month]." ".$Year;
								if($IsLiveProcess < 1)
								{
									$FinalAmountToPay = 2;
								}
								if($FinalAmountToPay > 0)
								{
									GeneratePaymentLinks($CustomerArr,$FinalAmountToPay,$Notes,$InvoiceID);
								}
							}

							$InvoiceCreatedCounter++;
												
						}
					}
					else
					{
						$UpdateSQL	= "UPDATE ".$Prefix."customers SET status=:status WHERE id=:id";
						$UpdateESQL	= array("status"=>0,"id"=>(int)$CustomeriD);
						pdo_query($UpdateSQL,$UpdateESQL);
					}
				$CustomerCounter++;
				}
				if($CustNum == $CustomerCounter)
				{
					$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET status=:status,isprocessing=:isprocessing,totalinvoicegenerated=:totalinvoicegenerated WHERE id=:id";
					$UpdateESQL	= array("status"=>1,"isprocessing"=>0,"id"=>(int)$RequestID,"totalinvoicegenerated"=>(int)$InvoiceCreatedCounter);
					pdo_query($UpdateSQL,$UpdateESQL);
				}
			}
			else
			{
				$UpdateSQL	= "UPDATE ".$Prefix."invoice_request_queue SET status=:status,isprocessing=:isprocessing WHERE id=:id";
				$UpdateESQL	= array("status"=>1,"isprocessing"=>0,"id"=>(int)$RequestID);
				pdo_query($UpdateSQL,$UpdateESQL);
			}
		}
		$response['success']	= true;
		$response['msg']		= "Invoices created successfully.";
	}
	else
	{
		$response['success']	= true;
		$response['msg']		= "No new pending request found.";
	}
	$json = json_encode($response);
	echo $json;
	die;
}
if($_POST['Mode'] == "GetAllInvoices")
{
	$perpage = 2;

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
    $response['msg']		= "Unable to fetch invoices.";

	$condition	= " AND inv.deletedon <:deletedon AND cust.deletedon <:deletedon2";
	$Esql		= array("deletedon"=>1,"deletedon2"=>1);
	$FilterMonth = '';
	$FilterYear = '';
	if($_POST['year'] > 0)
	{
		$condition	.= " AND inv.invoiceyear=:invoiceyear";
		$Esql['invoiceyear']	= (int)$_POST['year'];
		$FilterYear = $_POST['year'];
	}
	if($_POST['month'] > 0)
	{
		$condition	.= " AND inv.invoicemonth=:invoicemonth";
		$Esql['invoicemonth']	= (int)$_POST['month'];
		$FilterMonth = FullMonthName($_POST['month']);
	}
	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND inv.clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['lineid'] > 0)
	{
		$condition	.= " AND cust.lineid=:lineid";
		$Esql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['linemanid'] > 0)
	{
		$condition	.= " AND cust.linemanid=:linemanid";
		$Esql['linemanid']	= (int)$_POST['linemanid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$condition	.= " AND cust.hawkerid=:hawkerid";
		$Esql['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if(trim($_POST['searchkeyword']) != "")
	{
		$condition	.= " AND (cust.name LIKE :name || cust.phone LIKE :phone || cust.email LIKE :email || cust.address1 LIKE :address1 || cust.customerid LIKE :customerid)";

		$Esql['name']		= "%".$_POST['searchkeyword']."%";
		$Esql['phone']		= "%".$_POST['searchkeyword']."%";
		$Esql['email']		= "%".$_POST['searchkeyword']."%";
		$Esql['address1']	= "%".$_POST['searchkeyword']."%";
		$Esql['customerid']	= "".$_POST['searchkeyword']."%";
	}

	$Sql	= "SELECT inv.*,cust.name as customername,cust.customerid as customerid, cust.phone as customerphone,cust.lineid as customerlineid, cust.linemanid as customerlinemanid, cust.hawkerid as customerhawkerid FROM ".$Prefix."customers cust, ".$Prefix."invoices inv WHERE cust.id=inv.customerid ".$condition." ORDER BY inv.invoiceid DESC,inv.createdon DESC";

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

		$GetAllLine		= GetAllLine($_POST['clientid']);
		$GetAllLineman	= GetAllLineman($_POST['clientid']);
		$GetAllHawker	= GetAllHawker($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query2))
		{
			$isselected	= false;

			$id			= $rows['id'];
			$name		= $rows['customername'];
			$phone		= $rows['customerphone'];
			$createdon	= $rows['createdon'];
			$customerid	= $rows['customerid'];
			$month		= $rows['invoicemonth'];
			$year		= $rows['invoiceyear'];
			$amount		= $rows['finalamount'];
			$invoiceid	= $rows['invoiceid'];
			
			$line		= $GetAllLine[$rows['customerlineid']]['name'];
			$lineman	= $GetAllLineman[$rows['customerlinemanid']]['name'];
			$hawker		= $GetAllHawker[$rows['customerhawkerid']]['name'];

			if(trim($name) == "")
			{
				$name	= "---";
			}

			$name	= "#".$customerid.' '.$name;

			if(trim($phone) !='')
			{
				$name	.= " (".$phone.')';
			}

			if($phone == "")
			{
				$phone	= "---";
			}

			if($line == "")
			{
				$line	= "---";
			}

			if($lineman == "")
			{
				$lineman	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			if($hawker == "")
			{
				$hawker	= "---";
			}

			$RecordListArr[$index]['id']			= (int)$id;
			$RecordListArr[$index]['customerid']	= $customerid;
			$RecordListArr[$index]['invoiceid']		= $invoiceid;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['phone']			= $phone;
			$RecordListArr[$index]['line']			= $line;
			$RecordListArr[$index]['lineman']		= $lineman;
			$RecordListArr[$index]['hawker']		= $hawker;
			$RecordListArr[$index]['amount']		= $amount;
			$RecordListArr[$index]['month']			= ShortMonthName($month).", ".$year;
			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Invoice listed successfully.";
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
	$response['filtermonth']	= $FilterMonth;
	$response['filteryear']		= $FilterYear;
	
	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}
	
    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == 'GetCustomerLine')
{
	$RecordSetArr	= array();

	$AssignedLineArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch customer line.";

	$CheckSql2	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon";
	$CheckEsql2	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery2	= pdo_query($CheckSql2,$CheckEsql2);
	$CheckNum2		= pdo_num_rows($CheckQuery2);

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid=:lineid AND clientid=:clientid";
			$CheckEsql	= array("lineid"=>(int)$id,"clientid"=>(int)$_POST['clientid']);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$AssignedLineArr[]	= $id;

				$RecordSetArr[$index]['id']				= $id;
				$RecordSetArr[$index]['name']			= $name;
				$RecordSetArr[$index]['totalcustomer']	= $CheckNum;

				$index++;
			}
		}
		$AssignedLineStr	= implode(", ",$AssignedLineArr);

		if(trim($AssignedLineStr) == "")
		{
			$AssignedLineStr	= "-1";
		}

		$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid NOT IN(".$AssignedLineStr.") AND clientid=:clientid AND deletedon < :deletedon";
		$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$RecordSetArr[$index]['id']				= "9999";
			$RecordSetArr[$index]['name']			= "Unassigned";
			$RecordSetArr[$index]['totalcustomer']	= $CheckNum;
			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Customer Line listed successfully.";
	}

	$response['recordlist']		= $RecordSetArr;
	$response['totalcustomer']	= (int)$CheckNum2;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteInvoice")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete invoice, Please try later.";

	$DelSql		= "UPDATE ".$Prefix."invoices SET 
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
		$Response['msg']		= "Invoice deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetCirculationData")
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= strtotime('today');

	$CheckDate	= strtotime($_POST['circulationdate']);
	
	$response['success']	= false;
    $response['msg']		= "Unable to fetch circulation report";

	$Sql	= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND status=:status";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"status"=>1);
	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);
	
	$InventoryCiculationArr = array();
	$AllInventoryArr	   = array();
	$AllInventoryNameArr   = GetInventoryNames();
	if($Num > 0)
	{
		while($Row = pdo_fetch_assoc($Query))
		{
			$InventoryID				= $Row["inventoryid"];
			
			$InventoryCiculationArr[$InventoryID] = 0;

			$AllInventoryArr[] = $InventoryID;	
			
			$CheckSQL	= "SELECT cust.* FROM ".$Prefix."customers cust, ".$Prefix."subscriptions subs WHERE cust.clientid=:clientid AND cust.id=subs.customerid AND subs.enddate < :enddate AND (subs.startdate <:startdate || subs.startdate <:checkdate) AND subs.inventoryid =:inventoryid GROUP BY cust.id";
			
			$CheckESQL	= array("clientid"=>(int)$_POST['clientid'],"enddate"=>1,'startdate'=>1,'checkdate'=>(int)$CheckDate,'inventoryid'=>(int)$InventoryID);
			
			$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
			$CheckNum	= pdo_num_rows($CheckQuery);
			if($CheckNum > 0)
			{
				while($CustRow		= pdo_fetch_assoc($CheckQuery))
				{	
					$CustomerID		= $CustRow['id'];
					
					$IsHoliday		= IsHoliday($CheckDate,$CustomerID,$InventoryID);

					if($IsHoliday < 1)
					{
						$InventoryCiculationArr[$InventoryID] += 1; 	
					}
				}
			}
		}
	}
	//print_r($InventoryCiculationArr);
	$InventoryStr = '-1';
	if(!empty($AllInventoryArr))
	{
		$InventoryStr = implode(",",$AllInventoryArr);
	}

	$CategorySql	= "SELECT cat.* FROM ".$Prefix."category cat, ".$Prefix."inventory inv WHERE cat.status=:status AND cat.id=inv.categoryid AND inv.id IN (".$InventoryStr.") GROUP BY cat.id ORDER BY cat.orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);
	
	$RecordSet 		= array();
	if($CategoryNum > 0)
	{
		
		$catindex = 0;
		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];
			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.categoryid=:categoryid ORDER BY inv.name ASC";
		
			$InventoryEsql	= array("categoryid"=>(int)$catid);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$RecordSet = array();
				$index			= 0;
				while($InvRow	= pdo_fetch_assoc($InventoryQuery))
				{
					$TempInvID		= $InvRow['id'];
					$TempInvName	= $InvRow['name'];
					$RecordSet[$index]['id'] 		  = $TempInvID;
					$RecordSet[$index]['name'] 		  = $AllInventoryNameArr[$TempInvID]['name'] ;
					$RecordSet[$index]['circulation'] = "".(int)$InventoryCiculationArr[$TempInvID]."" ;
					
					$index++;
				}
			}
			
			$RecordListArr[$catindex]['id']			= (int)$catid;
			$RecordListArr[$catindex]['title']		= $cattitle;
			$RecordListArr[$catindex]['recordlist']	= $RecordSet;

			$catindex++;
		}
	}
	if(!empty($RecordListArr))
	{
		$response['success']	= true;
		$response['msg']		= "Circulation Report listed successfully.";
	}
	$response['inventorylist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
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

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE lineid=:lineid AND clientid=:clientid";
			$CheckEsql	= array("lineid"=>(int)$id,"clientid"=>(int)$_POST['clientid']);

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

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE linemanid=:linemanid AND clientid=:clientid";
			$CheckEsql	= array("linemanid"=>(int)$id,"clientid"=>(int)$_POST['clientid']);

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

			$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE hawkerid=:hawkerid AND clientid=:clientid";
			$CheckEsql	= array("hawkerid"=>(int)$id,"clientid"=>(int)$_POST['clientid']);

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
if($_POST['Mode'] == 'GetInvoicePDF')
{
	$Response['success']		= false;
	$Response['pdffilepath']	= '';
	$Response['msg']			= "unable to generate invoice pdf.";

	$Pdf_FileName 	= 'invoice-'.$_POST['invoiceid'].".pdf";
	
	$File			= "viewinvoice.php?invoiceid=".$_POST['invoiceid'];
	
	$ServerAPIURL		= "http://agency.orlopay.com/api/";

	if($_SERVER['IsLocal'] == 'Yes')
	{
		$ServerAPIURL		= "http://orlopay/api/";
	}

	if(!is_dir("../assets/".$_POST['clientid']))
	{
		mkdir("../assets/".$_POST['clientid']);
		mkdir("../assets/".$_POST['clientid']."/invoices/");
	}
	$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_POST['clientid']."/invoices/".$Pdf_FileName,"");
	if($IsCreated == true)
	{
		$Response['success']		= true;
		$Response['pdffilepath']	= $ServerURL."assets/".$_POST['clientid']."/invoices/".$Pdf_FileName;
		$Response['msg']			= "Invoice pdf generated successfully.";
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
?>