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

$usertype	= "lineman";

if($_POST['Mode'] == "AddLineman")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add line man.";

	/*$LineArr	= @explode(",",$_POST['selectedline']);*/
	$LineArr	= $_POST['selectedline'];

	$dob		= "";
	if($_POST['hasdob'] > 0 && $_POST['dob'] != "")
	{
		$dob	= strtotime($_POST['dob']);
	}

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
	}

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";

		$CheckSql	= "SELECT * FROM ".$Prefix."lineman WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A line man already exist with same phone";
		}
		
	}

	$imagefile1	= "";

    if($_FILES['imagefile1']['name'] !='' && !$haserror)
    {
      	$Ext		= strrchr($_FILES['imagefile1']['name'],".");
        $rand		= substr(md5(uniqid(microtime())), 0, 3);
        $imagefile1	= str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$_FILES['imagefile1']['name']));
        $imagefile1	= str_replace(" ",'_',$imagefile1);
        $Move1		= @move_uploaded_file($_FILES['imagefile1']['tmp_name'], $staffidproof.$imagefile1);

        if(!$Move1)
        {
			$haserror	= true;
            $ErrorMsg = "Unable to upload Attachment 1.";
			$response['toastmsg']	= $ErrorMsg;
        }
    }

	$imagefile2	= "";

    if($_FILES['imagefile2']['name'] !='' && !$haserror)
    {
      	$Ext		= strrchr($_FILES['imagefile2']['name'],".");
        $rand		= substr(md5(uniqid(microtime())), 0, 3);
        $imagefile2	= str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$_FILES['imagefile2']['name']));
        $imagefile2	= str_replace(" ",'_',$imagefile2);
        $Move2		= @move_uploaded_file($_FILES['imagefile2']['tmp_name'], $staffidproof.$imagefile2);

        if(!$Move2)
        {
			$haserror	= true;
            $ErrorMsg = "Unable to upload Attachment 2.";
			$response['toastmsg']	= $ErrorMsg;
        }
    }

	if($_POST['isidproofrequired'] < 1)
	{
		$_POST['idproofdetail']	= "";
	}

	$_POST['selectedline']	= json_decode(stripslashes($_POST['selectedline']));
	$_POST['permissions']	= json_decode(stripslashes($_POST['permissions']));

	if($haserror == false)
	{
		$lineids	= "::".implode("::",array_filter(array_unique($_POST['selectedline'])))."::";

		$Sql	= "INSERT INTO ".$Prefix."lineman SET 
		clientid			=:clientid,
		lineids				=:lineids,
		name				=:name,
		phone				=:phone,
		password			=:password,
		remark				=:remark,
		status				=:status,
		areaid				=:areaid,
		hasdob				=:hasdob,
		dob					=:dob,
		isidproofrequired	=:isidproofrequired,
		idproofdetail		=:idproofdetail,
		imagefile1			=:imagefile1,
		imagefile2			=:imagefile2,
		createdon			=:createdon";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"lineids"			=>$lineids,
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"password"			=>$_POST['password'],
			"remark"			=>$_POST['remarks'],
			"status"			=>(int)$_POST['status'],
			"areaid"			=>(int)$_POST['areaid'],
			"hasdob"			=>(int)$_POST['hasdob'],
			"dob"				=>(int)$dob,
			"isidproofrequired"	=>(int)$_POST['isidproofrequired'],
			"idproofdetail"		=>$_POST['idproofdetail'],
			"imagefile1"		=>$imagefile1,
			"imagefile2"		=>$imagefile2,
			"createdon"			=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();

			$ExtStr		='';
			$ExtEsql	= array();
			if(!empty($_POST['permissions']))
			{
				foreach($_POST['permissions'] as $key => $value)
				{
					$fldname	= $value->id;
					$fldvalue	= $value->ischecked;

					if($fldvalue == 'true' || $fldvalue == "1")
					{
						$fldvalue = 1;
					}
					$ExtStr			.=",".$fldname."=:".$fldname;
					$ExtEsql["".$fldname.""] 	= (int)$fldvalue; 
				}
				
				$InsertSQL	= "INSERT INTO ".$Prefix."permissions SET 
								clientid	= :clientid,
								managerid	= :managerid,
								usertype	= :usertype,
								createdon	= :createdon
								".$ExtStr."
								";
				$InsertESQL	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid,"usertype"=>$usertype,"createdon"=>time());

				$Arr 	= array_merge($InsertESQL,$ExtEsql);
				pdo_query($InsertSQL,$Arr);
			}

			$reportpermissionkey	= array_search('canreports', array_column($_POST['permissions'], 'id'));

			$reportpermissionlist	= $_POST['permissions'][$reportpermissionkey];

			$ExtStr2	='';
			$ExtEsql2	= array();

			if(!empty($reportpermissionlist->reportlist))
			{
				foreach($reportpermissionlist->reportlist as $key2 => $value2)
				{
					$fldname2	= $value2->id;
					$fldvalue2	= $value2->ischecked;

					if($fldvalue2 == 'true' || $fldvalue2 == "1")
					{
						$fldvalue2 = 1;
					}
					$ExtStr2			.=",".$fldname2."=:".$fldname2;
					$ExtEsql2["".$fldname2.""] 	= (int)$fldvalue2;
				}

				$InsertSQL2	= "INSERT INTO ".$Prefix."report_permissions SET 
								clientid	=:clientid,
								managerid	=:managerid,
								usertype	=:usertype,
								createdon	=:createdon
								".$ExtStr2."
								";
				$InsertESQL2	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid,"usertype"=>$usertype,"createdon"=>time());

				$Arr2	= array_merge($InsertESQL2,$ExtEsql2);

				pdo_query($InsertSQL2,$Arr2);
			}

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

	$Cond	= "";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	if($_POST['areaid'] > 0)
	{
		$Cond	.= " AND areaid=:areaid";
		$Esql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Cond	.= " AND lineids like :lineids";
		$Esql['lineids']	= "%::".(int)$_POST['lineid']."::%";
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Cond	.= " AND areaid IN(".$areaids.")";
	}

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon ".$Cond."";

	$Query		= pdo_query($Sql,$Esql);
	$Num		= pdo_num_rows($Query);
	$TotalRec	= $Num;

	if($Num > 0)
	{
		$AllAreaArr	= GetAllArea($_POST["clientid"]);
		$AllLineArr	= GetAllLine($_POST["clientid"]);

		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$LineNameArr	= array();

			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$areaid		= $rows['areaid'];
			$lineids	= $rows['lineids'];
			$lineidArr	= @explode("::",$rows['lineids']);
			$createdon	= $rows['createdon'];
			$areaname	= $AllAreaArr[$areaid]['name'];
			$hasdob		= (int)$rows['hasdob'];
			$dob		= $rows['dob'];

			$dobtext		= "";

			if(($hasdob > 0) && ($dob != "" && $dob > 0))
			{
				$dob		= date("Y-m-d",$dob);
				$dobtext	= date("d-M-Y",$rows['dob']);
			}
			else
			{
				$dob		= "";
				$dobtext	= "---";
			}

			if(trim($areaname) == "")
			{
				$areaname	= "---";
			}

			$lineidArr	= @array_filter(@array_unique($lineidArr));

			if(!empty($lineidArr))
			{
				foreach($lineidArr as $key=>$value)
				{
					$LineNameArr[]	= $AllLineArr[$value]['name'];
				}

				$LineNameArr	= @array_filter(@array_unique($LineNameArr));
			}
			$lines	= @implode(",",$LineNameArr);

			if(trim($lines) == "")
			{
				$lines	= "---";
			}

			$RecordListArr[$index]['id']		= $id;
			$RecordListArr[$index]['name']		= $name;
			$RecordListArr[$index]['phone']		= $phone;
			$RecordListArr[$index]['area']		= $areaname;
			$RecordListArr[$index]['lines']		= $lines;
			$RecordListArr[$index]['addeddate']	= date("d-M-Y",$createdon);
			$RecordListArr[$index]['dob']		= $dob;
			$RecordListArr[$index]['dobtext']	= $dobtext;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Line man listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

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
		$AreaNameArr	= GetAllArea($_POST["clientid"]);

		$row	= pdo_fetch_assoc($query);

		$imagefile1		= $row['imagefile1'];
		$imagefile2		= $row['imagefile2'];

		if($imagefile1 !='' AND file_exists($staffidproof.$imagefile1))
		{
			$imagepath1	= $staffidproof.$imagefile1;
		}

		if($imagefile2 !='' AND file_exists($staffidproof.$imagefile2))
		{
			$imagepath2	= $staffidproof.$imagefile2;
		}

		$hasdob			= (int)$row['hasdob'];
		$dob			= $row['dob'];

		$dobtext		= "";

		if(($hasdob > 0) && ($dob != "" && $dob > 0))
		{
			$dob		= date("Y-m-d",$dob);
			$dobtext	= date("d-M-Y",$row['dob']);
		}
		else
		{
			$dob		= "";
			$dobtext	= "---";
		}

		$lineidArr	= @explode("::",$row['lineids']);
		$lineidArr	= @array_unique($lineidArr);
		$lineidArr	= @array_filter($lineidArr);
		$lineidArr	= @array_values($lineidArr);

		$detailArr["name"]				= $row['name'];
		$detailArr["phone"]				= $row['phone'];
		$detailArr["password"]			= $row['password'];
		$detailArr["remark"]			= $row['remark'];
		$detailArr["status"]			= $row['status'];
		$detailArr["areaid"]			= (int)$row['areaid'];
		$detailArr["areaname"]			= $AreaNameArr[$row['areaid']]['name'];
		$detailArr["lineids"]			= $lineidArr;
		$detailArr["hasdob"]			= (int)$hasdob;
		$detailArr["dob"]				= $dob;
		$detailArr["isidproofrequired"]	= (int)$row['isidproofrequired'];
		$detailArr["idproofdetail"]		= $row['idproofdetail'];
		$detailArr["imagefile1"]		= $imagepath1;
		$detailArr["imagefile2"]		= $imagepath2;

		$permsql	= "SELECT * FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
		$permesql	= array("managerid"=>(int)$row['id'],"usertype"=>$usertype);
		$permquery	= pdo_query($permsql,$permesql);
		$permnum	= pdo_num_rows($permquery);

		if($permnum > 0)
		{
			$dbpermisionrow	= pdo_fetch_assoc($permquery);

			foreach($PermissionArr as $key => $Permissions)
			{
				if($dbpermisionrow["".$Permissions['id'].""] > 0)
				{
					$PermissionArr[$key]['ischecked'] = true;
				}
				else
				{
					$PermissionArr[$key]['ischecked'] = false;
				}

				if($Permissions['id'] == 'canreports')
				{
					$reportpermsql		= "SELECT * FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
					$reportpermesql		= array("managerid"=>(int)$row['id'],"usertype"=>$usertype);
					
					$reportpermquery	= pdo_query($reportpermsql,$reportpermesql);
					$reportpermnum		= pdo_num_rows($reportpermquery);

					if($reportpermnum > 0)
					{
						$reportdbpermisionrow	= pdo_fetch_assoc($reportpermquery);

						foreach($Permissions['reportlist'] as $reportkey => $ReportPermissions)
						{
							if($reportdbpermisionrow["".$ReportPermissions['id'].""] > 0)
							{
								$Permissions['reportlist'][$reportkey]['ischecked'] = true;
							}
							else
							{
								$Permissions['reportlist'][$reportkey]['ischecked'] = false;
							}
						}
					}

					$PermissionArr[$key]['reportlist']	= $Permissions['reportlist'];
				}
			}
		}
		$detailArr['permissions']	= $PermissionArr;

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

	$dob		= "";
	if($_POST['hasdob'] > 0 && $_POST['dob'] != "")
	{
		$dob	= strtotime($_POST['dob']);
	}

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
	}
	/*if($_POST['phone'] == "")
	{
		$ErrorMsg	.= "Please enter phone.<br>";
	}*/

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
	}

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

	$imagefile1	= "";

    if($_FILES['imagefile1']['name'] !='' && !$haserror)
    {
      	$Ext		= strrchr($_FILES['imagefile1']['name'],".");
        $rand		= substr(md5(uniqid(microtime())), 0, 3);
        $imagefile1	= str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$_FILES['imagefile1']['name']));
        $imagefile1	= str_replace(" ",'_',$imagefile1);
        $Move1		= @move_uploaded_file($_FILES['imagefile1']['tmp_name'], $staffidproof.$imagefile1);

        if(!$Move1)
        {
			$haserror	= true;
            $ErrorMsg = "Unable to upload Attachment 1.";
			$response['msg']	= $ErrorMsg;
        }
    }

	$imagefile2	= "";

    if($_FILES['imagefile2']['name'] !='' && !$haserror)
    {
      	$Ext		= strrchr($_FILES['imagefile2']['name'],".");
        $rand		= substr(md5(uniqid(microtime())), 0, 3);
        $imagefile2	= str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$_FILES['imagefile2']['name']));
        $imagefile2	= str_replace(" ",'_',$imagefile2);
        $Move2		= @move_uploaded_file($_FILES['imagefile2']['tmp_name'], $staffidproof.$imagefile2);

        if(!$Move2)
        {
			$haserror	= true;
            $ErrorMsg = "Unable to upload Attachment 2.";
			$response['msg']	= $ErrorMsg;
        }
    }

	if($_POST['isidproofrequired'] < 1)
	{
		$_POST['idproofdetail']	= "";
	}

	$_POST['selectedline']	= json_decode(stripslashes($_POST['selectedline']));
	$_POST['permissions']	= json_decode(stripslashes($_POST['permissions']));

	if($haserror == false)
	{
		$lineids	= "::".implode("::",array_filter(array_unique($_POST['selectedline'])))."::";

        $ExtArg	= '';
        $ExtArr	= array();

		if($imagefile1 != "")
        {
            $ExtArg .= ',imagefile1	=:imagefile1';
            $ExtArr['imagefile1']	= $imagefile1;
        }

		if($imagefile1 != "" && $_POST['preimagefile1'] != "")
		{
			@unlink($_POST['preimagefile1']);
		}

		if($imagefile2 != "")
        {
            $ExtArg .= ',imagefile2	=:imagefile2';
            $ExtArr['imagefile2']	= $imagefile2;
        }

		if($imagefile2 != "" && $_POST['preimagefile2'] != "")
		{
			@unlink($_POST['preimagefile2']);
		}

		$Sql	= "UPDATE ".$Prefix."lineman SET 
		lineids				=:lineids,
		name				=:name,
		phone				=:phone,
		password			=:password,
		remark				=:remark,
		status				=:status,
		areaid				=:areaid,
		hasdob				=:hasdob,
		dob					=:dob,
		isidproofrequired	=:isidproofrequired,
		idproofdetail		=:idproofdetail
		".$ExtArg."
		WHERE
		id					=:id";

		$Esql	= array(
			"lineids"			=>$lineids,
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"password"			=>$_POST['password'],
			"remark"			=>$_POST['remarks'],
			"status"			=>(int)$_POST['status'],
			"areaid"			=>(int)$_POST['areaid'],
			"hasdob"			=>(int)$_POST['hasdob'],
			"dob"				=>(int)$dob,
			"isidproofrequired"	=>(int)$_POST['isidproofrequired'],
			"idproofdetail"		=>$_POST['idproofdetail'],
			"id"				=>(int)$_POST['recordid']
		);

		$Arr	= array_merge($ExtArr,$Esql);
		$Query	= pdo_query($Sql,$Arr);

		if($Query)
		{
			$recordid	= $_POST['recordid'];

			$Delsql		= "DELETE FROM ".$Prefix."permissions WHERE managerid=:managerid AND usertype=:usertype";
			$DelEsql	= array("managerid"=>(int)$recordid,"usertype"=>$usertype);
			pdo_query($Delsql,$DelEsql);

			$ReportDelsql		= "DELETE FROM ".$Prefix."report_permissions WHERE managerid=:managerid AND usertype=:usertype";
			$ReportDelEsql	= array("managerid"=>(int)$recordid,"usertype"=>$usertype);
			pdo_query($ReportDelsql,$ReportDelEsql);

			$ExtStr		='';
			$ExtEsql	= array();
			if(!empty($_POST['permissions']))
			{
				foreach($_POST['permissions'] as $key => $value)
				{
					$fldname	= $value->id;
					$fldvalue	= $value->ischecked;

					if($fldvalue == 'true' || $fldvalue == "1")
					{
						$fldvalue = 1;
					}
					$ExtStr			.=",".$fldname."=:".$fldname;
					$ExtEsql["".$fldname.""] 	= (int)$fldvalue; 
				}

				$InsertSQL	= "INSERT INTO ".$Prefix."permissions SET 
								clientid	= :clientid,
								managerid	= :managerid,
								usertype	=:usertype,
								createdon	= :createdon
								".$ExtStr."
								";
				$InsertESQL	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid,"usertype"=>$usertype,"createdon"=>time());
				
				$Arr 	= array_merge($InsertESQL,$ExtEsql);
				$query = pdo_query($InsertSQL,$Arr);
			}

			$reportpermissionkey	= array_search('canreports', array_column($_POST['permissions'], 'id'));

			$reportpermissionlist	= $_POST['permissions'][$reportpermissionkey];

			$ExtStr2	='';
			$ExtEsql2	= array();

			if(!empty($reportpermissionlist->reportlist))
			{
				foreach($reportpermissionlist->reportlist as $key2 => $value2)
				{
					$fldname2	= $value2->id;
					$fldvalue2	= $value2->ischecked;

					if($fldvalue2 == 'true' || $fldvalue2 == "1")
					{
						$fldvalue2 = 1;
					}
					$ExtStr2			.=",".$fldname2."=:".$fldname2;
					$ExtEsql2["".$fldname2.""] 	= (int)$fldvalue2;
				}

				$InsertSQL2	= "INSERT INTO ".$Prefix."report_permissions SET 
								clientid	= :clientid,
								managerid	= :managerid,
								usertype	=:usertype,
								createdon	= :createdon
								".$ExtStr2."
								";
				$InsertESQL2	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid,"usertype"=>$usertype,"createdon"=>time());

				$Arr2	= array_merge($InsertESQL2,$ExtEsql2);

				pdo_query($InsertSQL2,$Arr2);
			}

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

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE linemanid=:linemanid AND clientid=:clientid AND deletedon < :deletedon";
	$CheckEsql	= array("linemanid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

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

	$Cond	= "";

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Cond	.= " AND areaid IN(".$areaids.")";
	}

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon ".$Cond." ORDER BY name ASC";
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
if($_POST['Mode'] == "DeleteAttachment")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete attached, Please try later.";

	$DelEsql	= array(
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']
	);

	if($_POST['preimagefile1'] != "")
	{
		@unlink($_POST['preimagefile1']);

		$ExtArg .= 'imagefile1	=:imagefile1';
		$DelEsql['imagefile1']	= '';
	}

	if($_POST['preimagefile2'] != "")
	{
		@unlink($_POST['preimagefile2']);

		$ExtArg .= 'imagefile2	=:imagefile2';
		$DelEsql['imagefile2']	= '';
	}

	$DelSql		= "UPDATE ".$Prefix."lineman SET 
	".$ExtArg."
	WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$DelQuery	= pdo_query($DelSql,$DelEsql);

	if($DelQuery)
	{
		$Response['success']	= true;
		$Response['msg']		= "Attachment deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
?>