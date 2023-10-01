<?php
@ini_set("display_error",1);
error_reporting(1);
session_cache_limiter('none');
@ini_set('session.save_handler', 'files');
session_start();
date_default_timezone_set('Asia/Calcutta');

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

$ServerURL		= "http://agency.orlopay.com/web/";
$ServerAPIURL	= "http://agency.orlopay.com/api/";

if($_SERVER['IsLocal'] == true)
{
	include_once($_SERVER['DOCUMENT_ROOT']."/agency/settings.php");

	$PDfHost	= "http://orlopay/api/"; // Server URL
	$ServerURL	= "http://orlopay/agency/web/";
	$ServerAPIURL = "http://orlopay/agency/api/";
}
else
{
	include_once($_SERVER['DOCUMENT_ROOT']."/settings.php");

	$hostname	= $_SERVER['SERVER_NAME'];
	$parsedUrl	= parse_url($hostname);
	$host		= explode('.', $parsedUrl['path']);

	$domainname		= $host[1].".".$host[2];

	$ServerURL		= "http://agency.".$domainname."/web/";
	$ServerAPIURL	= "http://agency.".$domainname."/api/";
}
$Uploadimage			= "../media/images/"; // secondbigthumb 300x300
$DataFiles				= "../data/";
$UploadDocs				= "../media/docs/";
$UploadTempimage		= "../media/temp/";
$LargeThumb				= "../media/large_thumb/"; //150x150
$OrgImages				= "../media/org/"; // for original images
$uploadJobsFiles		= "../media/jobfiles/"; // for original images

/*$ClientID	= 10;*/
$subdomain	= "premnews";

if($_SERVER['IsLocal'] != true)
{
	$hostname	= $_SERVER['SERVER_NAME'];
	$parsedUrl	= parse_url($hostname);
	$host		= explode('.', $parsedUrl['path']);

	$subdomain	= $host[0];
}

include_once "function.php";
include_once "../core/function.php";

/*$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
$Esql	= array("id"=>(int)$ClientID,"clienttype"=>2,"deletedon"=>1);*/

$Sql	= "SELECT * FROM ".$Prefix."clients WHERE websiteidentifire=:websiteidentifire AND clienttype=:clienttype AND deletedon < :deletedon";
$Esql	= array("websiteidentifire"=>$subdomain,"clienttype"=>2,"deletedon"=>1);

$siteprefix	= "/";

if($domainname == 'orlonow.com')
{
	$siteprefix	= "/web/";

	$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id AND clienttype=:clienttype AND deletedon < :deletedon";
	$Esql	= array("id"=>10,"clienttype"=>2,"deletedon"=>1);
}

$Query	= pdo_query($Sql,$Esql);
$Num	= pdo_num_rows($Query);

if($Num > 0)
{
	$rows = pdo_fetch_assoc($Query);

	$clientname		= $rows['clientname'];
	$websiteaddress	= $rows['websiteaddress'];
	$phone1			= $rows['websitephone1'];
	$iswhatsapp1	= $rows['websiteiswhatsapp1'];
	$phone2			= $rows['websitephone2'];
	$iswhatsapp2	= $rows['websiteiswhatsapp2'];
	$websiteemail	= $rows['websiteemail'];

	$countryid		= $rows['countryid'];
	$stateid		= $rows['stateid'];
	$cityid			= $rows['cityid'];
	$tagline		= $rows['tagline'];
	$aboutusdesc	= $rows['aboutusdesc'];
	$logo			= $rows['imagefile'];
	$ClientID		= $rows['id'];

	if($tagline != "")
	{
		$tagline	= html_entity_decode($tagline);
	}

	if($aboutusdesc != "")
	{
		$aboutusdesc	= html_entity_decode($aboutusdesc);
	}
}
else
{
	if($_SERVER['IsLocal'] != true)
	{
		header("location:http://web.".$domainname."");
		die;
	}
}
$loginpaymentlink	= $siteprefix."bill-payment.php";
?>