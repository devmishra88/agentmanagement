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

if($_POST['Mode'] == "ChangeAgencyPassword")
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
			$Sql	= "UPDATE ".$Prefix."clients SET password=:password,ispasswordupdate=:ispasswordupdate,authtoken=:authtoken WHERE id=:id";
			$Esql	= array("password"=>trim($_POST['newpassword']),"ispasswordupdate"=>1,"authtoken"=>"","id"=>(int)$_POST['recordid']);

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
			$Sql	= "UPDATE ".$Prefix."lineman SET password=:password,authtoken=:authtoken WHERE id=:id";
			$Esql	= array("password"=>trim($_POST['newpassword']),"authtoken"=>"","id"=>(int)$_POST['recordid']);

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
if($_POST['Mode'] == "ChangeManagerPassword")
{
	$haserror	= false;

    $response['success']	= false;
    $response['msg']		= "Unable to update password.";

	$CheckSql	= "SELECT * FROM ".$Prefix."area_manager WHERE id=:id AND status=:status";
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
			$Sql	= "UPDATE ".$Prefix."area_manager SET password=:password,authtoken=:authtoken WHERE id=:id";
			$Esql	= array("password"=>trim($_POST['newpassword']),"authtoken"=>"","id"=>(int)$_POST['recordid']);

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
if($_POST['Mode'] == "ChangeHawkerPassword")
{
	$haserror	= false;

    $response['success']	= false;
    $response['msg']		= "Unable to update password.";

	$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE id=:id AND status=:status";
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
			$Sql	= "UPDATE ".$Prefix."hawker SET password=:password,authtoken=:authtoken WHERE id=:id";
			$Esql	= array("password"=>trim($_POST['newpassword']),"authtoken"=>"","id"=>(int)$_POST['recordid']);

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
?>