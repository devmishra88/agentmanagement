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

$authtoken	= md5($createdon);

if($_POST['Mode'] == "GetAllClients")
{
	$colorArr	= array("blue","green","red","pink");

	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch list.";

	$Sql	= "SELECT * FROM ".$Prefix."clients WHERE clienttype=:clienttype AND deletedon < :deletedon AND status=:status";
	$Esql	= array("clienttype"=>2,"deletedon"=>1,"status"=>1);

	$Query	= pdo_query($Sql,$Esql);
	if(is_array($Query))
	{
		$response['msg']	= $Query['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		$colorloop	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$accesstoken		= array();

			$id					= $rows['id'];
			$clientname			= $rows['clientname'];
			$ispasswordupdate	= $rows['ispasswordupdate'];
			$isbetaaccount		= (int)$rows['accounttype'];
			$ispasswordupdate	= 1;
			$orgauthtoken		= $rows['authtoken'];

			if(trim($orgauthtoken) == "")
			{
				$UpdateSql	= "UPDATE ".$Prefix."clients SET authtoken=:authtoken WHERE id=:id";
				$UpdateEsql	= array("authtoken"=>$authtoken,"id"=>(int)$id);

				$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);

				if($UpdateQuery)
				{
					$orgauthtoken	= $authtoken;
				}
			}

			$clientdetail	= array("id"=>(int)$rows['id'],"clientname"=>$rows['clientname'],"clientphone"=>$rows['phone1'],"clienttype"=>(int)$rows['clienttype'],"ispasswordupdate"=>(int)$ispasswordupdate,"stateid"=>(int)$rows['stateid'],"cityid"=>(int)$rows['cityid'],"isbetaaccount"=>$isbetaaccount,"pincode"=>$rows['pincode'],"linemanid"=>0,"islineman"=>false,"ismanager"=>false,"areaids"=>"","personname"=>$rows['contactname']);

			$clientarr = array_merge($permarr,$clientdetail);

			$accesstoken = array(
			   "iss" => $jwtiss,
			   "aud" => $jwtaud,
			   "iat" => $jwtiat,
			   "nbf" => $jwtnbf,
			   "isadminlogin" => $jw_isadminlogin,
			   "adminid" => $jw_adminid,
			   "clientdata" => $clientarr,
			   "authtoken" => $orgauthtoken
			);

			$jwt = JWT::encode($accesstoken, $jwtkey);

			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $clientname;
			$RecordListArr[$index]['clientdetail']	= $clientarr;
			$RecordListArr[$index]['accesstoken']	= $jwt;
			$RecordListArr[$index]['color']			= $colorArr[$colorloop];

			$index++;

			$colorloop++;
			if($colorloop > 3)
			{
				$colorloop	= 0;
			}
		}

		$response['success']	= true;
		$response['msg']		= "Client listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAvailableStaffList")
{
	$RecordSetArr	= array();

	$response['success']	= false;
	
    $response['msg']		= "Unable to fetch Staff List.";

	$RecordFound 	= 0;
	$index	= 0;

	$RecordSetArr[$index]['id']			= '';
	$RecordSetArr[$index]['type']		= '';
	$RecordSetArr[$index]['name']		= 'Select';
	$RecordSetArr[$index]['orgname']	= '';

	$index++;

	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE deletedon < :deletedon AND status=:status AND id=:id AND clientid=:clientid";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>1,"id"=>(int)$_POST['customerid']);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$rows	= pdo_fetch_assoc($Query);
		$AreaID = $rows['areaid'];
		$LineID = $rows['lineid'];
	}

	$Num	= 0;

	if($_POST['ismanager'] != "1" && $_POST['islineman'] != "1" && $_POST['ishawker'] != "1")
	{
		$Sql	= "SELECT * FROM ".$Prefix."clients WHERE clienttype=:clienttype AND deletedon < :deletedon AND status=:status AND id=:id";
		$Esql	= array("clienttype"=>2,"deletedon"=>1,"status"=>1,"id"=>(int)$_POST['clientid']);

		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);
	}

	if($Num > 0)
	{
		$RecordFound = 1;
		$rows	= pdo_fetch_assoc($Query);

		$id		= $rows['id'];
		$name	= $rows['contactname'];

		$RecordSetArr[$index]['id']			= $id;
		$RecordSetArr[$index]['type']		= "admin";
		$RecordSetArr[$index]['name']		= $name." (Admin)";
		$RecordSetArr[$index]['orgname']	= $name;

		$index++;
	}

	$Num	= 0;

	if($_POST['ismanager'] == "1" || ($_POST['ismanager'] != "1" && $_POST['islineman'] != "1" && $_POST['ishawker'] != "1"))
	{
		$condition	= "";
		$Esql		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"areaid"=>(int)$AreaID);

		$Sql	= "SELECT manager.* FROM ".$Prefix."area_manager manager,".$Prefix."assigned_area_linker linker WHERE manager.clientid=:clientid AND manager.deletedon < :deletedon AND linker.areaid=:areaid AND linker.managerid = manager.id ".$condition." ORDER BY manager.name ASC";
		
		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);
	}

	if($Num > 0)
	{
		$RecordFound = 1;
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$RecordSetArr[$index]['id']			= $id;
			$RecordSetArr[$index]['type']		= "area manager";
			$RecordSetArr[$index]['name']		= $name." (Area Manager)";
			$RecordSetArr[$index]['orgname']	= $name;

			$index++;
		}
	}

	$Num	= 0;

	if(($_POST['ismanager'] == "1" || $_POST['islineman'] == "1") || ($_POST['ismanager'] != "1" && $_POST['islineman'] != "1" && $_POST['ishawker'] != "1"))
	{
		$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon AND areaid=:areaid AND lineids LIKE :lineids ORDER BY name ASC";
		$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"areaid"=>(int)$AreaID,"lineids"=>"%::".(int)$LineID."::%");

		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);

		if($Num > 0)
		{
			$RecordFound = 1;
			while($rows = pdo_fetch_assoc($Query))
			{
				$id		= $rows['id'];
				$name	= $rows['name'];

				$RecordSetArr[$index]['id']			= $id;
				$RecordSetArr[$index]['type']		= "lineman";
				$RecordSetArr[$index]['name']		= $name." (Lineman)";
				$RecordSetArr[$index]['orgname']	= $name;

				$index++;
			}
		}
	}

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
	$cond	= "";

	$cond	.= " AND areaid=:areaid AND lineids LIKE :lineids AND lineids NOT LIKE :lineids2 AND areaid<>:areaid2";
	
	$Esql['areaid']		= (int)$AreaID;
	$Esql['areaid2']	= 0;
	$Esql['lineids']	= "%::".(int)$LineID."::%";
	$Esql['lineids2']	= "%::::%";

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$cond	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$LineIDArr	= @explode(",",$_POST['lineids']);
		$LineIDStr	= "";

		if(!empty($LineIDArr))
		{
			$LineIDStr = " AND (";

			foreach($LineIDArr as $key=>$val)
			{
				$LineIDStr .= "lineids LIKE :lineids_".$key." OR ";
				$Esql['lineids_'.$key] = "%::".$val."::%";
			}
			$LineIDStr = substr_replace( $LineIDStr, "", -3 );
			$LineIDStr .= ')';
		}

		if(trim($LineIDStr) != "")
		{
			$cond	.= $LineIDStr;
		}
	}

	$Num	= 0;

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ".$cond." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$RecordFound = 1;
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$RecordSetArr[$index]['id']			= $id;
			$RecordSetArr[$index]['type']		= "hawker";
			$RecordSetArr[$index]['name']		= $name." (Hawker)";
			$RecordSetArr[$index]['orgname']	= $name;

			$index++;
		}
	}
	if($RecordFound > 0)
	{
		$response['success']	= true;
		$response['msg']		= "Record listed successfully.";
	}
	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
?>