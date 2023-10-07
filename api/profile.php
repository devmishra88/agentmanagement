<?php
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

		$profiledetailArr["clientname"]			= $row['clientname'];
		$profiledetailArr["clienttype"]			= $row['clienttype'];
		$profiledetailArr["distributorid"]		= $row['distributorid'];
		$profiledetailArr["pincode"]			= $row['pincode'];
		$profiledetailArr["stateid"]			= $row['stateid'];
		$profiledetailArr["cityid"]				= $row['cityid'];
		$profiledetailArr["address1"]			= $row['address1'];
		$profiledetailArr["address2"]			= $row['address2'];
		$profiledetailArr["contactname"]		= $row['contactname'];
		$profiledetailArr["contactemail"]		= $row['contactemail'];
		$profiledetailArr["phone1"]				= $row['phone1'];
		$profiledetailArr["phone2"]				= $row['phone2'];
		$profiledetailArr["password"]			= $row['password'];
		$profiledetailArr["iswhatsapp1"]		= (int)$row['iswhatsapp1'];
		$profiledetailArr["paymenttype"]		= $row['paymenttype'];
		$profiledetailArr["websiteemail"]		= $row['websiteemail'];
		$profiledetailArr["websitephone1"]		= $row['websitephone1'];
		$profiledetailArr["websiteiswhatsapp1"]	= (int)$row['websiteiswhatsapp1'];
		$profiledetailArr["websitephone2"]		= $row['websitephone2'];
		$profiledetailArr["websiteiswhatsapp2"]	= (int)$row['websiteiswhatsapp2'];
		$profiledetailArr["websiteaddress"]		= $row['websiteaddress'];

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

		$isbetaaccount	= (int)$rows['accounttype'];

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

		$response['success']		= true;
		$response['clientdetail']	= $clientdetail;
		$response['accesstoken']	= $jwt;
		$response['msg']			= "Profile updated successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "SaveWebsiteDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to update website detail.";

	$Sql	= "UPDATE ".$Prefix."clients SET 
	websiteemail		=:websiteemail,
	websitephone1		=:websitephone1,
	websiteiswhatsapp1	=:websiteiswhatsapp1,
	websitephone2		=:websitephone2,
	websiteiswhatsapp2	=:websiteiswhatsapp2,
	websiteaddress		=:websiteaddress
	
	WHERE
	id					=:id";

	$Esql	= array(
		"websiteemail"			=>$_POST['websiteemail'],
		"websitephone1"			=>$_POST['websitephone1'],
		"websiteiswhatsapp1"	=>(int)$_POST['websiteiswhatsapp1'],
		"websitephone2"			=>$_POST['websitephone2'],
		"websiteiswhatsapp2"	=>(int)$_POST['websiteiswhatsapp2'],
		"websiteaddress"		=>$_POST['websiteaddress'],
		"id"					=>(int)$_POST['recordid']
	);

	$Query	= pdo_query($Sql,$Esql);

	if($Query)
	{
		$response['success']		= true;
		$response['clientdetail']	= $clientdetail;
		$response['accesstoken']	= $jwt;
		$response['msg']			= "website detail updated successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}
?>