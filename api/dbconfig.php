<?php
@ini_set("display_error",1);
error_reporting(1);
session_cache_limiter('none');
@ini_set('session.save_handler', 'files');
session_start();
date_default_timezone_set('Asia/Calcutta');
header('Access-Control-Allow-Origin: *');

$HttpOnly	= 1;
if($_SERVER['IsLocal'] == true)
{
	include_once('../includes/config.php');
}
else
{
	if($configpath == '')
	{
		@include_once('../includes/config.php');
	}
	else
	{
		@include_once($configpath);
	}
}
try {
	$pdo = new PDO($dsn, $db_username, $db_password);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
	catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
	die;
}
if($_SERVER['IsLocal'] == true)
{
	include_once($_SERVER['DOCUMENT_ROOT']."/clients/premsahu/agentmanagement/settings.php");

	$PDfHost = "http://orlopay/api/"; // Server URL 
}
else
{
	include_once($_SERVER['DOCUMENT_ROOT']."/settings.php");
}

$DomPDFFolder = "../assets/dompdf/";
$PDfHost = "http://".$SiteDomainName.""; // Server URL 

include_once "../core/function.php";

include_once "function.php";

include_once "function_pdf.php";

require __DIR__ . '/libs/php-jwt/vendor/autoload.php';
use \Firebase\JWT\JWT;

/*list($r, $g, $b) = sscanf("#414A58", "#%02x%02x%02x");

echo $r."--".$g."--".$b;*/

$Username	= "admin";
$Password	= "demo";

$startyear	= 2020;

$MonthArr	= array("1"=>"January","2"=>"February","3"=>"March","4"=>"April","5"=>"May","6"=>"June","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");

$DaysListArr[0]['id']		= 1;
$DaysListArr[0]['name']		= "Mon";
$DaysListArr[0]['checked']	= true;

$DaysListArr[1]['id']		= 2;
$DaysListArr[1]['name']		= "Tue";
$DaysListArr[1]['checked']	= true;

$DaysListArr[2]['id']		= 3;
$DaysListArr[2]['name']		= "Wed";
$DaysListArr[2]['checked']	= true;

$DaysListArr[3]['id']		= 4;
$DaysListArr[3]['name']		= "Thu";
$DaysListArr[3]['checked']	= true;

$DaysListArr[4]['id']		= 5;
$DaysListArr[4]['name']		= "Fri";
$DaysListArr[4]['checked']	= true;

$DaysListArr[5]['id']		= 6;
$DaysListArr[5]['name']		= "Sat";
$DaysListArr[5]['checked']	= true;

$DaysListArr[6]['id']		= 7;
$DaysListArr[6]['name']		= "Sun";
$DaysListArr[6]['checked']	= true;

$access_token	= GetAccessToken();
$hasvalidtoken	= true;
$jwtdata		= "";

if(!empty($access_token) && $access_token != "null")
{
	$jwtdata = JWT::decode($access_token, $jwtkey, array('HS256'));

	if(empty($jwtdata))
	{
		$hasvalidtoken	= false;
	}
	else
	{
		$jw_isadminlogin	= $jwtdata->isadminlogin;
		$jw_adminid			= $jwtdata->adminid;

		$clientdata			= $jwtdata->clientdata;

		$_POST['clientid']			= trim($clientdata->id);
		$_POST['stateid']			= trim($clientdata->stateid);
		$_POST['cityid']			= trim($clientdata->cityid);
		$_POST['areamanagerid']		= trim($clientdata->areamanagerid);
		$_POST['areaids']			= trim($clientdata->areaids);
		$_POST['islineman']			= trim($clientdata->islineman);
		$_POST['ismanager']			= trim($clientdata->ismanager);
		$_POST['loginlinemanid']	= trim($clientdata->linemanid);
		$_POST['ishawker']			= trim($clientdata->ishawker);
		$_POST['loginhawkerid']		= trim($clientdata->hawkerid);
		$_POST['iscustomerarea']	= trim($clientdata->iscustomerarea);

		$paymentaddedby		= "admin";
		$paymentaddedbyid	= (int)$_POST['clientid'];
		$isadminlogin		= true;

		if($_POST['iscustomerarea'] > 0)
		{
			$_POST['customerid']	= trim($clientdata->customerid);
			$_POST['customerrecid']	= trim($clientdata->customerrecid);

			$paymentaddedby		= "customer";
			$paymentaddedbyid	= (int)$_POST['customerid'];
		}

		$_POST['authtoken']			= $jwtdata->authtoken;

		if(($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0) || ($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$AreaAndLineArr = GetAssignedAreaAndLine();

			$isadminlogin	= false;

			$_POST['areaids']	= trim($AreaAndLineArr['areaids']);
			$_POST['lineids']	= trim($AreaAndLineArr['lineids']);

			if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
			{
				$paymentaddedby		= "areamanager";
				$paymentaddedbyid	= (int)$_POST['areamanagerid'];
			}
			if($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0)
			{
				$paymentaddedby		= "lineman";
				$paymentaddedbyid	= (int)$_POST['loginlinemanid'];
			}
			if($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0)
			{
				$paymentaddedby		= "hawker";
				$paymentaddedbyid	= (int)$_POST['loginhawkerid'];
			}
		}

		/*print_r($AreaAndLineArr);

		$_POST['linemanareaid']		= "";
		$_POST['linemanlineids']	= "";

		if($_POST['areamanagerid'] > 0)
		{
			$AllAssignedArea	= GetAllAssignedAreaByAreaManager($_POST['clientid'],$_POST['areamanagerid']);
			$AllAssignedAreaStr	= @implode(",",@array_filter(@array_unique($AllAssignedArea)));

			$_POST['areaids']	= $AllAssignedAreaStr;
		}

		if($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0)
		{
			$AreaAndLineArr = GetAllAssignedAreaAndLineByLineman($_POST['clientid'], $_POST['loginlinemanid']);

			$_POST['linemanareaid']		= (int)$AreaAndLineArr['areaid'];
			$_POST['linemanlineids']	= $AreaAndLineArr['lineids'];
		}

		$_POST['hawkerareaid']	= "";
		$_POST['hawkerlineids']	= "";

		if($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0)
		{
			$AreaAndLineArr = GetAllAssignedAreaAndLineByHawker($_POST['clientid'], $_POST['loginhawkerid']);

			$_POST['hawkerareaid']	= (int)$AreaAndLineArr['areaid'];
			$_POST['hawkerlineids']	= $AreaAndLineArr['lineids'];
		}*/
	}
}
else
{
	$hasvalidtoken	= false;
}
if(!$_POST && !$_GET)
{
	include_once "index.php";
	die;
}
if(!$hasvalidtoken && ($_POST['Mode'] != "AppLogin" && $_POST['Mode'] != "RecoverPassword" && $_POST['Mode'] != "GetAllClients" && $_POST['Mode'] != "DownloadReport" && $_POST['Mode'] != "AddContactRequest" && $_POST['Mode'] != "SendLoginOtp" && $_POST['Mode'] != "VerifyCustomerLogin" && $_POST['Mode'] != "ViewOutstanding" && $_POST['InvoiceClientid'] == "" && $ByPass != 1))
{
    $response['success']		= false;
    $response['fourcelogout']	= true;
    $response['msg']			= "Invalid access, we are logging off you.";

    $json = json_encode($response);
    echo $json;
	die;
}

$AccountValidationFailedMsg	= "Account validation failed! Please contact support!";

$donotusecache	= 1;

$FilterDataStr	= Posts_R();

$ServerAPIURL	= "http://".$SiteDomainName."/api/";

if($_SERVER['HTTPS'] == "on")
{
	$ServerAPIURL	= "https://".$SiteDomainName."/api/";
}

if($_SERVER['IsLocal'] == 'Yes')
{
	$ServerAPIURL	= "http://wpserver/clients/premsahu/agentmanagement/api/";
}
