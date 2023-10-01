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

$createdon	= time();

if($_POST['Mode'] == "RecoverPassword")
{

    $response['success']	= false;
    $response['msg']		= "Unable to recover password, Please try later.";

	if($_POST['logintype'] == "1")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);
	}
	else if($_POST['logintype'] == "0")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."clients WHERE phone1=:phone1 AND clienttype=:clienttype AND deletedon < :deletedon";
		$CheckEsql	= array("phone1"=>$_POST['forgetphone'],"clienttype"=>2,"deletedon"=>1);
	}
	else if($_POST['logintype'] == "2")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."area_manager WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);
	}
	else if($_POST['logintype'] == "3")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE phone=:phone AND status=:status AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"status"=>1,"deletedon"=>1);
	}

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$rows = pdo_fetch_assoc($CheckQuery);

		$password	= $rows['password'];
		
		if($_POST['logintype'] == "1" || $_POST['logintype'] == "2" || $_POST['logintype'] == "3")
		{
			$clientname	= $rows['name'];
		}
		else
		{
			$clientname	= $rows['clientname'];
		}

		/*$Message = "Dear ".$clientname.",

Your Password: ".$password."

orlopay.com
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)";*/

		$Message = "Dear <arg1>,

Your Password: <arg2>

<arg3>
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS)
Team ORLO";
		//$_POST['forgetphone'] = "9811165912";
		$messagearr[] = array("phoneno"=>$_POST['forgetphone'],"arg1"=>$clientname,"arg2"=>$password,"arg3"=>"agency.orlopay.com");
		
		$SMSRoute = 7; /*7 - OtpRoute, 2 - Normal Route*/
		$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$DLTSMSForgotID,$SMSRoute);		

		/*$messagearr[] = array("phoneno"=>$_POST['forgetphone'],"arg1"=>'');
		$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"");*/

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
?>