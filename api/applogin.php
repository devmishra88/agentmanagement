<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
/*set response code - 200 OK*/
http_response_code(200);

include_once "dbconfig.php";

use \Firebase\JWT\JWT;

$createdon	= time();

$AllowedPerm	= array();
$authtoken		= md5($createdon);

if($_POST['Mode'] == "AppLogin")
{
    $response['success']	= false;
    $response['msg']		= "Unable to login, Please try later.";

	$isaccountexist	= false;

	if($_POST['logintype'] == "1")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND status=:status AND deletedon < :deletedon ORDER BY name ASC";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$isaccountexist	= true;

			$rows	= pdo_fetch_assoc($CheckQuery);

			if($rows['password'] == $_POST['password'])
			{
				$response['success']			= true;
				$response['id']					= (int)$rows['id'];
				$response['clientname']			= $rows['name'];
				$response['ispasswordupdate']	= 1;
				$response['islinemanlogin']		= 1;
				$response['ismanagerlogin']		= 0;

				$orgauthtoken	= $rows['authtoken'];

				if(trim($orgauthtoken) == "")
				{
					$UpdateSql	= "UPDATE ".$Prefix."lineman SET authtoken=:authtoken WHERE id=:id";
					$UpdateEsql	= array("authtoken"=>$authtoken,"id"=>(int)$rows['id']);

					$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

					if($UpdateQuery)
					{
						$orgauthtoken	= $authtoken;
					}
				}

				$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
				$Esql	= array("id"=>(int)$rows['clientid'],"clienttype"=>2,"deletedon"=>1);

				$Query		= pdo_query($Sql,$Esql);
				$clientrows	= pdo_fetch_assoc($Query);

				$clientstatus	= $clientrows['status'];

				if($clientstatus < 1)
				{
					$response['success']		= false;
					$response['msg']			= $AccountValidationFailedMsg;

					$json = json_encode($response);
					echo $json;
					die;
				}

				$clientdetail	= array(
					"id"				=>(int)$rows['clientid'],
					"linemanid"			=>(int)$rows['id'],
					"clientname"		=>$clientrows['clientname'],
					"clienttype"		=>(int)$clientrows['clienttype'],
					"ispasswordupdate"	=>1,
					"stateid"			=>(int)$clientrows['stateid'],
					"cityid"			=>(int)$clientrows['cityid'],
					"isbetaaccount"		=>(int)$clientrows['accounttype'],
					"pincode"			=>$clientrows['pincode'],
					"clientphone"		=>$rows['phone'],
					"islineman"			=>1,
					"ismanager"			=>0,
					"areaids"			=>"",
					"personname"		=>$rows['name']
				);

				$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
				$permesql	= array("managerid"=>(int)$rows['id'],"usertype"=>"lineman");

				$permquery	= pdo_query($permsql,$permesql);
				$permnum	= pdo_num_rows($permquery);

				if($permnum > 0)
				{
					$permissionrow	= pdo_fetch_assoc($permquery);

					$permissionrow['canareamanager']	= 0;
					$permissionrow['cansettings']		= 0;
					$permissionrow['changepassword']	= 1;

					foreach($permarr as $key=>$val)
					{
						if(array_key_exists($key,$permissionrow))
						{
							$AllowedPerm[$key]	= (int)$permissionrow[$key];
						}
					}

					if($AllowedPerm["canreports"] > 0)
					{
						$reppermsql	= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
						$reppermesql	= array("managerid"=>(int)$rows['id'],"usertype"=>"lineman");

						$reppermquery	= pdo_query($reppermsql,$reppermesql);
						$reppermnum		= pdo_num_rows($reppermquery);

						if($reppermnum > 0)
						{
							$reppermissionrow	= pdo_fetch_assoc($reppermquery);

							foreach($permarr as $key=>$val)
							{
								if(array_key_exists($key,$reppermissionrow))
								{
									$AllowedPerm[$key]	= (int)$reppermissionrow[$key];
								}
							}
						}
					}
				}
				else
				{
					foreach($permarr as $key=>$val)
					{
						$AllowedPerm[$key]	= 0;
					}
				}

				$clientarr = array_merge($AllowedPerm,$clientdetail);

				/*set response code - 200 OK*/
				http_response_code(200);

				$accesstoken = array(
				   "iss" => $jwtiss,
				   "aud" => $jwtaud,
				   "iat" => $jwtiat,
				   "nbf" => $jwtnbf,
				   "isadminlogin" => false,
				   "adminid" => 0,
				   "clientdata" => $clientarr,
				   "authtoken" => $orgauthtoken
				);

				$jwt = JWT::encode($accesstoken, $jwtkey);

				$response['msg']			= "Logged in successfully.";
				$response['clientdetail']	= $clientarr;
				$response['accesstoken']	= $jwt;
				$response['authtoken']		= $orgauthtoken;
				$response['logintime']		= $createdon;
			}
			else
			{
				/*set response code - 200 OK*/
				http_response_code(200);

				$response['success']	= false;
				$response['msg']		= "Password is incorrect.";
			}
		}
		else
		{
			/*set response code - 400 bad request*/
			http_response_code(200);

			$response['success']	= false;
			$response['msg']		= "No account exist with the entered Mobile Number.";
		}
	}
	else if($_POST['logintype'] == "0")
	{
		$AdminSql	= "SELECT * FROM ".$Prefix."app_admin WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$AdminEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);

		$AdminQuery	= pdo_query($AdminSql,$AdminEsql);
		
		if(is_array($AdminQuery))
		{
			$response['msg']	= $AdminQuery['errormessage'];

			$json = json_encode($response);
			echo $json;
			die;
		}

		$AdminNum	= pdo_num_rows($AdminQuery);

		if($AdminNum > 0)
		{
			$isaccountexist	= true;

			$adminrows	= pdo_fetch_assoc($AdminQuery);

			$adminid	= $adminrows['id'];
			$adminname	= $adminrows['name'];
			$phone		= $adminrows['phone'];

			if($adminrows['password'] == $_POST['password'])
			{
				$response['success']		= true;
				$response['adminid']		= (int)$adminid;
				$response['adminname']		= $adminname;
				$isadminlogin				= true;
				$response['isadminlogin']	= $isadminlogin;
				$response['clientdetail']	= array();
				$response['logintime']		= $createdon;

				$LoginOtp	= GenerateOTP(6);

				$accesstoken = array(
				   "iss" => $jwtiss,
				   "aud" => $jwtaud,
				   "iat" => $jwtiat,
				   "nbf" => $jwtnbf,
				   "isadminlogin" => $isadminlogin,
				   "adminid" => $adminid,
				   "clientdata" => $clientdetail,
				   "authtoken" => $orgauthtoken
				);

				$jwt = JWT::encode($accesstoken, $jwtkey);

				$OtpSql		= "UPDATE ".$Prefix."app_admin SET loginotp=:loginotp,lastlogintime=:lastlogintime WHERE id=:id";
				$OtpEsql	= array("loginotp"=>$LoginOtp,"id"=>(int)$adminid,"lastlogintime"=>time());

				$OtpQuery	= pdo_query($OtpSql,$OtpEsql);

				if($OtpQuery && !is_array($OtpQuery))
				{
		/*$Message = "Dear ".$adminrows['name'].",

Your OTP: ".$LoginOtp."

$SiteDomainName
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)";*/

		$Message = "Dear <arg1>,

Your OTP: <arg2>

<arg3>
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)
Team ORLO";
		/*$messagearr[] = array("phoneno"=>$phone,"arg1"=>'');
		$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"");*/
		
		$messagearr[] = array("phoneno"=>$phone,"arg1"=>$adminrows['name'],"arg2"=>$LoginOtp,"arg3"=>$SiteDomainName);
		$SMSRoute = 7; /*7 - OtpRoute, 2 - Normal Route*/
		$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$DLTSMSAuthID,$SMSRoute);
		
				}

				$response['msg']			= "OTP sent to ".$phone;
				$response['accesstoken']	= $jwt;
			}
			else
			{
				$response['success']	= false;
				$response['msg']		= "Password is incorrect.";
			}
		}
		else
		{
			$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE phone1=:phone1 AND clienttype=:clienttype AND deletedon < :deletedon";
			$CheckEsql	= array("phone1"=>$_POST['phone'],"clienttype"=>2,"deletedon"=>1);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);

			if(is_array($CheckQuery))
			{
				$response['msg']	= $CheckQuery['errormessage'];

				$json = json_encode($response);
				echo $json;
				die;
			}

			$CheckNum	= pdo_num_rows($CheckQuery);

			if($CheckNum > 0)
			{
				$isaccountexist	= true;

				$rows	= pdo_fetch_assoc($CheckQuery);

				$clientstatus	= $rows['status'];

				if($clientstatus < 1)
				{
					$response['success']		= false;
					$response['msg']			= $AccountValidationFailedMsg;

					$json = json_encode($response);
					echo $json;
					die;
				}

				if($rows['password'] == $_POST['password'])
				{
					$response['success']			= true;
					$response['id']					= (int)$rows['id'];
					$response['clientname']			= $rows['clientname'];
					$response['clienttype']			= (int)$rows['clienttype'];
					$response['ispasswordupdate']	= (int)$rows['ispasswordupdate'];
					$isbetaaccount					= (int)$rows['accounttype'];

					$orgauthtoken	= $rows['authtoken'];

					if(trim($orgauthtoken) == "")
					{
						$UpdateSql	= "UPDATE ".$Prefix."clients SET authtoken=:authtoken WHERE id=:id";
						$UpdateEsql	= array("authtoken"=>$authtoken,"id"=>(int)$rows['id']);

						$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

						if($UpdateQuery)
						{
							$orgauthtoken	= $authtoken;
						}
					}

					$clientdetail	= array("id"=>(int)$rows['id'],"clientname"=>$rows['clientname'],"clientphone"=>$rows['phone1'],"clienttype"=>(int)$rows['clienttype'],"ispasswordupdate"=>(int)$rows['ispasswordupdate'],"stateid"=>(int)$rows['stateid'],"cityid"=>(int)$rows['cityid'],"isbetaaccount"=>$isbetaaccount,"pincode"=>$rows['pincode'],"linemanid"=>0,"islineman"=>false,"ismanager"=>false,"areaids"=>"","personname"=>$rows['contactname']);

					$clientarr = array_merge($permarr,$clientdetail);

					$accesstoken = array(
					   "iss" => $jwtiss,
					   "aud" => $jwtaud,
					   "iat" => $jwtiat,
					   "nbf" => $jwtnbf,
					   "isadminlogin" => false,
					   "adminid" => 0,
					   "clientdata" => $clientarr,
					   "authtoken" => $orgauthtoken
					);

					$jwt = JWT::encode($accesstoken, $jwtkey);

					$response['msg']			= "Logged in successfully.";
					$response['clientdetail']	= $clientarr;
					$response['logintime']		= $createdon;
					$response['accesstoken']	= $jwt;
					$response['authtoken']		= $orgauthtoken;
				}
				else
				{
					$response['success']	= false;
					$response['msg']		= "Password is incorrect.";
				}
			}
		}
	}
	else if($_POST['logintype'] == "2")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."area_manager WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$isaccountexist	= true;

			$rows	= pdo_fetch_assoc($CheckQuery);

			if($rows['password'] == $_POST['password'])
			{
				$response['success']			= true;
				$response['id']					= (int)$rows['id'];
				$response['clientname']			= $rows['name'];
				$response['ispasswordupdate']	= 1;
				$response['islinemanlogin']		= false;
				$response['ismanagerlogin']		= true;

				$orgauthtoken	= $rows['authtoken'];

				if(trim($orgauthtoken) == "")
				{
					$UpdateSql	= "UPDATE ".$Prefix."area_manager SET authtoken=:authtoken WHERE id=:id";
					$UpdateEsql	= array("authtoken"=>$authtoken,"id"=>(int)$rows['id']);

					$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

					if($UpdateQuery)
					{
						$orgauthtoken	= $authtoken;
					}
				}

				$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
				$Esql	= array("id"=>(int)$rows['clientid'],"clienttype"=>2,"deletedon"=>1);

				$Query		= pdo_query($Sql,$Esql);
				$clientrows	= pdo_fetch_assoc($Query);

				$clientstatus	= $clientrows['status'];

				if($clientstatus < 1)
				{
					$response['success']		= false;
					$response['msg']			= $AccountValidationFailedMsg;

					$json = json_encode($response);
					echo $json;
					die;
				}

				$AllAssignedArea	= GetAllAssignedAreaByAreaManager($rows['clientid'],$rows['id']);

				$AllAssignedAreaStr	= @implode(",",@array_filter(@array_unique($AllAssignedArea)));

				if(trim($AllAssignedAreaStr) == "")
				{
					$AllAssignedAreaStr	= "-1";
				}

				$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
				$permesql	= array("managerid"=>(int)$rows['id'],"usertype"=>"manager");

				$permquery	= pdo_query($permsql,$permesql);
				$permnum	= pdo_num_rows($permquery);

				if($permnum > 0)
				{
					$permissionrow	= pdo_fetch_assoc($permquery);

					$permissionrow['canareamanager']	= 0;
					$permissionrow['cansettings']		= 0;
					$permissionrow['changepassword']	= 1;

					foreach($permarr as $key=>$val)
					{
						if(array_key_exists($key,$permissionrow))
						{
							$AllowedPerm[$key]	= (int)$permissionrow[$key];
						}
					}

					if($AllowedPerm["canreports"] > 0)
					{
						$reppermsql	= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
						$reppermesql	= array("managerid"=>(int)$rows['id'],"usertype"=>"manager");

						$reppermquery	= pdo_query($reppermsql,$reppermesql);
						$reppermnum		= pdo_num_rows($reppermquery);

						if($reppermnum > 0)
						{
							$reppermissionrow	= pdo_fetch_assoc($reppermquery);

							foreach($permarr as $key=>$val)
							{
								if(array_key_exists($key,$reppermissionrow))
								{
									$AllowedPerm[$key]	= (int)$reppermissionrow[$key];
								}
							}
						}
					}
				}
				else
				{
					foreach($permarr as $key=>$val)
					{
						$AllowedPerm[$key]	= 0;
					}
				}

				$clientdetail	= array(
					"id"				=>(int)$rows['clientid'],
					"areamanagerid"		=>(int)$rows['id'],
					"clientname"		=>$clientrows['clientname'],
					"clienttype"		=>(int)$clientrows['clienttype'],
					"ispasswordupdate"	=>1,
					"stateid"			=>(int)$clientrows['stateid'],
					"cityid"			=>(int)$clientrows['cityid'],
					"isbetaaccount"		=>(int)$clientrows['accounttype'],
					"pincode"			=>$clientrows['pincode'],
					"clientphone"		=>$rows['phone'],
					"islineman"			=>0,
					"ismanager"			=>1,
					"areaids"			=>$AllAssignedAreaStr,
					"personname"		=>$rows['name']
				);

				$clientarr = array_merge($AllowedPerm,$clientdetail);

				/*set response code - 200 OK*/
				http_response_code(200);

				$accesstoken = array(
				   "iss" => $jwtiss,
				   "aud" => $jwtaud,
				   "iat" => $jwtiat,
				   "nbf" => $jwtnbf,
				   "isadminlogin" => false,
				   "adminid" => 0,
				   "clientdata" => $clientarr,
				   "authtoken" => $orgauthtoken
				);

				$jwt = JWT::encode($accesstoken, $jwtkey);

				$response['msg']			= "Logged in successfully.";
				$response['clientdetail']	= $clientarr;
				$response['accesstoken']	= $jwt;
				$response['logintime']		= $createdon;
				$response['authtoken']		= $orgauthtoken;
			}
			else
			{
				/*set response code - 200 OK*/
				http_response_code(200);

				$response['success']	= false;
				$response['msg']		= "Password is incorrect.";
			}
		}
		else
		{
			/*set response code - 400 bad request*/
			http_response_code(200);

			$response['success']	= false;
			$response['msg']		= "No account exist with the entered Mobile Number.";
		}
	}
	if($_POST['logintype'] == "3")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$isaccountexist	= true;

			$rows	= pdo_fetch_assoc($CheckQuery);

			if($rows['password'] == $_POST['password'])
			{
				$response['success']			= true;
				$response['id']					= (int)$rows['id'];
				$response['clientname']			= $rows['name'];
				$response['ispasswordupdate']	= 1;
				$response['ishawkerlogin']		= 1;
				$response['islinemanlogin']		= 0;
				$response['ismanagerlogin']		= 0;

				$orgauthtoken	= $rows['authtoken'];

				if(trim($orgauthtoken) == "")
				{
					$UpdateSql	= "UPDATE ".$Prefix."hawker SET authtoken=:authtoken WHERE id=:id";
					$UpdateEsql	= array("authtoken"=>$authtoken,"id"=>(int)$rows['id']);

					$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

					if($UpdateQuery)
					{
						$orgauthtoken	= $authtoken;
					}
				}

				$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
				$Esql	= array("id"=>(int)$rows['clientid'],"clienttype"=>2,"deletedon"=>1);

				$Query		= pdo_query($Sql,$Esql);
				$clientrows	= pdo_fetch_assoc($Query);

				$clientstatus	= $clientrows['status'];

				if($clientstatus < 1)
				{
					$response['success']		= false;
					$response['msg']			= $AccountValidationFailedMsg;

					$json = json_encode($response);
					echo $json;
					die;
				}

				$clientdetail	= array(
					"id"				=>(int)$rows['clientid'],
					"hawkerid"			=>(int)$rows['id'],
					"clientname"		=>$clientrows['clientname'],
					"clienttype"		=>(int)$clientrows['clienttype'],
					"ispasswordupdate"	=>1,
					"stateid"			=>(int)$clientrows['stateid'],
					"cityid"			=>(int)$clientrows['cityid'],
					"isbetaaccount"		=>(int)$clientrows['accounttype'],
					"pincode"			=>$clientrows['pincode'],
					"clientphone"		=>$rows['phone'],
					"islineman"			=>0,
					"ismanager"			=>0,
					"ishawker"			=>1,
					"areaids"			=>"",
					"personname"		=>$rows['name']
				);

				$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
				$permesql	= array("managerid"=>(int)$rows['id'],"usertype"=>"hawker");

				$permquery	= pdo_query($permsql,$permesql);
				$permnum	= pdo_num_rows($permquery);

				if($permnum > 0)
				{
					$permissionrow	= pdo_fetch_assoc($permquery);

					$permissionrow['canareamanager']	= 0;
					$permissionrow['cansettings']		= 0;
					$permissionrow['changepassword']	= 1;

					foreach($permarr as $key=>$val)
					{
						if(array_key_exists($key,$permissionrow))
						{
							$AllowedPerm[$key]	= (int)$permissionrow[$key];
						}
					}

					if($AllowedPerm["canreports"] > 0)
					{
						$reppermsql	= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
						$reppermesql	= array("managerid"=>(int)$rows['id'],"usertype"=>"hawker");

						$reppermquery	= pdo_query($reppermsql,$reppermesql);
						$reppermnum		= pdo_num_rows($reppermquery);

						if($reppermnum > 0)
						{
							$reppermissionrow	= pdo_fetch_assoc($reppermquery);

							foreach($permarr as $key=>$val)
							{
								if(array_key_exists($key,$reppermissionrow))
								{
									$AllowedPerm[$key]	= (int)$reppermissionrow[$key];
								}
							}
						}
					}
				}
				else
				{
					foreach($permarr as $key=>$val)
					{
						$AllowedPerm[$key]	= 0;
					}
				}

				$clientarr = array_merge($AllowedPerm,$clientdetail);

				/*set response code - 200 OK*/
				http_response_code(200);

				$accesstoken = array(
				   "iss" => $jwtiss,
				   "aud" => $jwtaud,
				   "iat" => $jwtiat,
				   "nbf" => $jwtnbf,
				   "isadminlogin" => false,
				   "adminid" => 0,
				   "clientdata" => $clientarr,
				   "authtoken" => $orgauthtoken
				);

				$jwt = JWT::encode($accesstoken, $jwtkey);

				$response['msg']			= "Logged in successfully.";
				$response['clientdetail']	= $clientarr;
				$response['accesstoken']	= $jwt;
				$response['logintime']		= $createdon;
				$response['authtoken']		= $orgauthtoken;
			}
			else
			{
				/*set response code - 200 OK*/
				http_response_code(200);

				$response['success']	= false;
				$response['msg']		= "Password is incorrect.";
			}
		}
		else
		{
			/*set response code - 400 bad request*/
			http_response_code(200);

			$response['success']	= false;
			$response['msg']		= "No account exist with the entered Mobile Number.";
		}
	}

	if(!$isaccountexist)
	{
		$response['success']	= false;
		$response['msg']		= "No account exist with the entered user.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "VerifyAdmin")
{
    $response['success']	= false;
    $response['msg']		= "Unable to login, Please try later.";

	$isaccountexist	= false;
	
	$timeinterval	= (5 * 60); // 5 minutes interval

	$checktime		= time();

	$AdminSql	= "SELECT * FROM ".$Prefix."app_admin WHERE loginotp=:loginotp AND id=:id AND status=:status AND deletedon < :deletedon";
	$AdminEsql	= array("loginotp"=>$_POST['otp'],"id"=>(int)$_POST['adminid'],"status"=>1,"deletedon"=>1);

	$AdminQuery	= pdo_query($AdminSql,$AdminEsql);
	
	if(is_array($AdminQuery))
	{
		$response['msg']	= $AdminQuery['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$AdminNum	= pdo_num_rows($AdminQuery);

	if($AdminNum > 0)
	{
		$adminrow 	= pdo_fetch_assoc($AdminQuery);
		$lastlogin  = $adminrow['lastlogintime'];

		$diff		= $checktime - $lastlogin;

		if($diff < $timeinterval)
		{
			$response['success']	= true;
			$response['msg']		= "Admin verified successfully";
		}
		else
		{
			$response['success']	= false;
			$response['msg']		= "OTP has expired";
		}
	}
	else
	{
		$response['success']	= false;
		$response['msg']		= "Incorrect OTP entered";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "ResendAdminOTP")
{
    $response['success']	= success;
    $response['msg']		= "Unable to send OTP, Please try later.";

	$isaccountexist	= false;
	
	$timeinterval	= (5 * 60); // 5 minutes interval

	$checktime		= time();

	$AdminSql	= "SELECT * FROM ".$Prefix."app_admin WHERE id=:id AND status=:status AND deletedon < :deletedon";
	$AdminEsql	= array("id"=>(int)$_POST['adminid'],"status"=>1,"deletedon"=>1);

	$AdminQuery	= pdo_query($AdminSql,$AdminEsql);
	
	if(is_array($AdminQuery))
	{
		$response['msg']	= $AdminQuery['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$AdminNum	= pdo_num_rows($AdminQuery);

	if($AdminNum > 0)
	{
		$adminrows 	= pdo_fetch_assoc($AdminQuery);
		$adminid	= $adminrows['id'];
		$adminname	= $adminrows['name'];
		$phone		= $adminrows['phone'];

		$LoginOtp	= GenerateOTP(6);

		$OtpSql		= "UPDATE ".$Prefix."app_admin SET loginotp=:loginotp,lastlogintime=:lastlogintime WHERE id=:id";
		$OtpEsql	= array("loginotp"=>$LoginOtp,"id"=>(int)$adminid,"lastlogintime"=>time());

		$OtpQuery	= pdo_query($OtpSql,$OtpEsql);

		if($OtpQuery && !is_array($OtpQuery))
		{
		
		/*$Message = "Dear ".$adminrows['name'].",

Your OTP: ".$LoginOtp."

$SiteDomainName
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)";*/

		$Message = "Dear <arg1>,

Your OTP: <arg2>

<arg3>
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)
Team ORLO";

		$messagearr[] = array("phoneno"=>$phone,"arg1"=>$adminrows['name'],"arg2"=>$LoginOtp,"arg3"=>$SiteDomainName);
		
		$SMSRoute = 7; /*7 - OtpRoute, 2 - Normal Route*/
		$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$DLTSMSAuthID,$SMSRoute);
		}

		$response['success']	= true;
		$response['msg']	= "OTP sent to ".$phone;
	}
	else
	{
		$response['success']	= true;
		$response['msg']		= "Admin record not found";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
?>