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
?>