<?php
$Go2MarketingAuthToken		= "yQuKB13PWwyKy2FQ1MjW59uff";
$OtpSmsAuthID = "1231";  // Otp sms template 
$OfficialSenderID	="ORLOIN";
$dlttemplateid		= '1307161175035834184';
$DefaultSenderID= "ORLOIN";
function SendMessageViaGo2Marketing($phonenumberwithurl,$message,$senderid,$sendertype='1',$languagetype='0',$templateid="",$dlttemplateid='')
{
	global $Go2MarketingAuthToken,$DefaultSenderID,$SiteDomainName;

	if($_SERVER['IsLocal'] == 'Yes')
	{
		$ResponseArr['status']	= 'success';
		$resultarr['message']	= 1;
		return $ResponseArr;
	}
	$filetype	= "1"; /* filetype : 2-Manual Entry,0-Excel File,1-Dynamic Excel */	
	$language	= $languagetype;	/* language : 0-For English,2-For MultiLingual */
	$credittype	= "7";	/* credittype : 1-Promo,2-Trans */
	if($templateid == '')
	{
		$templateid	= "0";	/* 0-Type Msg, templateid greater than 0- Get Msg from Template */
	}
	if($sendertype < 1)
	{
		$credittype	= "1"; /*for promotional message*/
	}
	if(trim($senderid) =='' || trim($senderid) =='111111')
	{
		//$senderid	= 'BEAVER';
		$senderid	= $DefaultSenderID;
	}

	$isschd				= false; /* isschd : true- For Schedule,false-Not Schedule */
	$schddate 			= date("Y-m-d H:i:s"); /* schddate :yyyy-MM-dd HH:mm:ss */

	$msisdn				= array(); /* msisdn : For static Content  */
	$msisdnlist			= array(); /* msisdnlist : for dynamic content  */
	$isrefno			= true;
	$issmart_domainurl = false;
	$smart_domainurl	= '';
	$long_url			= '';
	$ukey				= $Go2MarketingAuthToken;	/* API Key */

	/* e.g. $message	= "Mesasge 3 : Come and get back to your dream home and contact us
	<arg1>
	Thanks
	Orlo";*/

	$msisdn		= $phonenumberwithurl;

	//$msisdn		= "9811165912,9811168031";
	$dataarr = array (
	'filetype'		=> $filetype,
	'msisdnlist'	=> $msisdn,
	'language'		=> $language,
	'credittype'	=> $credittype,
	'senderid'		=> $senderid,
	'templateid'	=> $templateid,
	'message'		=> $message,
	'ukey'			=> $ukey,
	'isschd'		=> $isschd,
	'schddate'		=> $schddate,
	'isrefno'		=> $isrefno,
	'issmart_domainurl'		=> $issmart_domainurl,
	'smart_domainurl'		=> $smart_domainurl,
	'long_url'		=> $long_url,
	);
	if($dlttemplateid !="")
	{
		$dataarr['dlttemplateid'] = $dlttemplateid;
	}

    $data_string = json_encode($dataarr);
	
	$url = "http://125.16.147.178/VoicenSMS/webresources/CreateSMSCampaignPost";  /* api url endpoint */
	
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS =>$data_string,
	  CURLOPT_SSL_VERIFYHOST => 0,
	  CURLOPT_SSL_VERIFYPEER => 0,
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	$ResponseArr = json_decode($response,true);

	return $ResponseArr;
}
	
	$phone = '9811165912';
$Message = 'Dear <arg1>, 

Your OTP: <arg2> 

<arg3> 
(PLEASE DELETE THIS MESSAGE FOR SECURITY REASONS) 
Team ORLO';

$messagearr[] = array("phoneno"=>$phone,"arg1"=>'viren','arg2'=>'123456','arg3'=>"orlopay.com");
//$smssent = SendMessageViaGo2Marketing($messagearr,$Message,$OfficialSenderID,$OtpSmsAuthID);

$smssent = SendMessageViaGo2Marketing($messagearr,$Message,"",'1','0','',$dlttemplateid);

print_r($smssent);
?>