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

$usertype	= "manager";

if($_POST['Mode'] == "AddAreaManager")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add area manager.";

	$dob		= "";
	if($_POST['hasdob'] > 0 && $_POST['dob'] != "")
	{
		$dob	= strtotime($_POST['dob']);
	}

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.";
	}

	if($ErrorMsg == "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."area_manager WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);
		
		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	= "A area manager already exist with same phone";
		}
	}
	if($ErrorMsg  !='')
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= $ErrorMsg;
	}

	$imagefile1	= "";

    if($_FILES['imagefile1']['name'] !='' && $ErrorMsg == "")
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
        }
    }

	$imagefile2	= "";

    if($_FILES['imagefile2']['name'] !='' && $ErrorMsg == "")
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
        }
    }

	if($_POST['isidproofrequired'] < 1)
	{
		$_POST['idproofdetail']	= "";
	}

	$_POST['arealist']			= json_decode(stripslashes($_POST['arealist']));
	$_POST['droppingpointlist']	= json_decode(stripslashes($_POST['droppingpointlist']));
	$_POST['permissions']		= json_decode(stripslashes($_POST['permissions']));

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."area_manager SET 
		clientid			=:clientid,
		name				=:name,
		phone				=:phone,
		password			=:password,
		remark				=:remark,
		status				=:status,
		hasdob				=:hasdob,
		dob					=:dob,
		isidproofrequired	=:isidproofrequired,
		idproofdetail		=:idproofdetail,
		imagefile1			=:imagefile1,
		imagefile2			=:imagefile2,
		createdon			=:createdon";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"phone"				=>$_POST['phone'],
			"password"			=>$_POST['password'],
			"name"				=>$_POST['name'],
			"remark"			=>$_POST['remarks'],
			"status"			=>(int)$_POST['status'],
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

			foreach($_POST['arealist'] as $areakey=>$arearows)
			{
				$areaid		= $arearows->id;
				$isassigned	= $arearows->isassigned;

				if($isassigned == "true" || $isassigned == "1")
				{
					$AreaSql	= "INSERT INTO ".$Prefix."assigned_area_linker SET 
					clientid	=:clientid,
					managerid	=:managerid,
					areaid		=:areaid,
					createdon	=:createdon";

					$AreaEsql	= array(
						"clientid"	=>(int)$_POST['clientid'],
						"managerid"	=>(int)$recordid,
						"areaid"	=>(int)$areaid,
						"createdon"	=>time()
					);

					$AreaQuery	= pdo_query($AreaSql,$AreaEsql);
				}
			}

			foreach($_POST['droppingpointlist'] as $droppingpointkey=>$droppingpintlist)
			{
				$droppingpointid	= $droppingpintlist->id;
				$isassigned			= $droppingpintlist->isassigned;

				if($isassigned == "true" || $isassigned == "1")
				{
					$DroppingPointSql	= "INSERT INTO ".$Prefix."assigned_dropping_point_linker SET 
					clientid	=:clientid,
					managerid	=:managerid,
					droppingpointid=:droppingpointid,
					createdon	=:createdon";

					$DroppingPointEsql	= array(
						"clientid"	=>(int)$_POST['clientid'],
						"managerid"	=>(int)$recordid,
						"droppingpointid"	=>(int)$droppingpointid,
						"createdon"	=>time()
					);

					$DroppingPointQuery	= pdo_query($DroppingPointSql,$DroppingPointEsql);
				}
			}

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
			$response['msg']		= "Area manager successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllAreaManager")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch area manager.";

	$Sql	= "SELECT * FROM ".$Prefix."area_manager WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$TotalRec	= $Num;

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$substitute	= "";

			$id				= $rows['id'];
			$name			= $rows['name'];
			$createdon		= $rows['createdon'];
			$substituteid	= $rows['substituteid'];
			$phone			= $rows['phone'];
			$hasdob			= (int)$rows['hasdob'];
			$dob			= $rows['dob'];

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

			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['addeddate']		= date("d-M-Y",$createdon);
			$RecordListArr[$index]['dob']			= $dob;
			$RecordListArr[$index]['dobtext']		= $dobtext;
			$RecordListArr[$index]['phone']			= $phone;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Area Manager listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAreaManagerDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch area manager detail.";

	$sql	= "SELECT * FROM ".$Prefix."area_manager WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
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

		$detailArr["name"]				= $row['name'];
		$detailArr["remark"]			= $row['remark'];
		$detailArr["phone"]				= $row['phone'];
		$detailArr["password"]			= $row['password'];
		$detailArr["status"]			= $row['status'];
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
		$response['msg']		= "Area manager detail fetched successfully.";
	}

	$response['areadetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditAreaManager")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to edit area manager.";

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name";
	}

	$dob		= "";
	if($_POST['hasdob'] > 0 && $_POST['dob'] != "")
	{
		$dob	= strtotime($_POST['dob']);
	}

	if($ErrorMsg == "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."area_manager WHERE phone=:phone AND clientid=:clientid AND deletedon <:deletedon AND id<>:id";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"id"=>(int)$_POST['recordid']);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);
		
		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	= "A area manager already exist with same phone";
		}
	}
	if($ErrorMsg  !='')
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= $ErrorMsg;
	}

	$imagefile1	= "";

    if($_FILES['imagefile1']['name'] !='' && $ErrorMsg == "")
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
        }
    }

	$imagefile2	= "";

    if($_FILES['imagefile2']['name'] !='' && $ErrorMsg == "")
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
        }
    }

	if($_POST['isidproofrequired'] < 1)
	{
		$_POST['idproofdetail']	= "";
	}

	$_POST['arealist']			= json_decode(stripslashes($_POST['arealist']));
	$_POST['droppingpointlist']	= json_decode(stripslashes($_POST['droppingpointlist']));
	$_POST['permissions']		= json_decode(stripslashes($_POST['permissions']));

	if($haserror == false)
	{
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

		$Sql	= "UPDATE ".$Prefix."area_manager SET 
		clientid			=:clientid,
		name				=:name,
		phone				=:phone,
		password			=:password,
		remark				=:remark,
		status				=:status,
		hasdob				=:hasdob,
		dob					=:dob,
		isidproofrequired	=:isidproofrequired,
		idproofdetail		=:idproofdetail
		".$ExtArg."
		WHERE
		id					=:id";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"phone"				=>$_POST['phone'],
			"password"			=>$_POST['password'],
			"name"				=>$_POST['name'],
			"remark"			=>$_POST['remarks'],
			"status"			=>(int)$_POST['status'],
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

			$NotAssignAreaArr	= array();

			foreach($_POST['arealist'] as $areakey=>$arearows)
			{
				$areaid		= $arearows->id;
				$isassigned	= $arearows->isassigned;

				if($isassigned == "true" || $isassigned == "1")
				{
					$CheckAreaSql	= "SELECT * FROM ".$Prefix."assigned_area_linker WHERE clientid=:clientid AND managerid=:managerid AND areaid=:areaid";
					$CheckAreaEsql	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid,"areaid"=>(int)$areaid);

					$CheckAreaQuery	= pdo_query($CheckAreaSql,$CheckAreaEsql);
					$CheckAreaNum	= pdo_num_rows($CheckAreaQuery);

					if($CheckAreaNum < 1)
					{
						$AreaSql	= "INSERT INTO ".$Prefix."assigned_area_linker SET 
						clientid	=:clientid,
						managerid	=:managerid,
						areaid		=:areaid,
						createdon	=:createdon";

						$AreaEsql	= array(
							"clientid"	=>(int)$_POST['clientid'],
							"managerid"	=>(int)$recordid,
							"areaid"	=>(int)$areaid,
							"createdon"	=>time()
						);

						$AreaQuery	= pdo_query($AreaSql,$AreaEsql);
					}
				}
				else
				{
					$NotAssignAreaArr[]	= $areaid;
				}
			}

			if(!empty($NotAssignAreaArr))
			{
				$NotAssignAreaStr	= @implode("," ,@array_filter(@array_unique($NotAssignAreaArr)));

				if(trim($NotAssignAreaStr) == "")
				{
					$NotAssignAreaStr	= "-1";
				}

				$DelSql	= "DELETE FROM ".$Prefix."assigned_area_linker WHERE areaid IN(".$NotAssignAreaStr.") AND clientid=:clientid AND managerid=:managerid";
				$DelEsql	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid);

				$DelQuery	= pdo_query($DelSql,$DelEsql);
			}

			foreach($_POST['droppingpointlist'] as $droppinglistkey=>$droppingpointrows)
			{
				$droppingpointid	= $droppingpintlist->id;
				$isassigned			= $droppingpintlist->isassigned;

				if($isassigned == "true" || $isassigned == "1")
				{
					$CheckDroppingSql	= "SELECT * FROM ".$Prefix."assigned_dropping_point_linker WHERE clientid=:clientid AND managerid=:managerid AND droppingpointid=:droppingpointid";
					$CheckDroppingEsql	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid,"droppingpointid"=>(int)$droppingpointid);

					$CheckDroppingQuery	= pdo_query($CheckDroppingSql,$CheckDroppingEsql);
					$CheckDroppingNum	= pdo_num_rows($CheckDroppingQuery);

					if($CheckDroppingNum < 1)
					{
						$CheckDroppingSql	= "INSERT INTO ".$Prefix."assigned_dropping_point_linker SET 
						clientid	=:clientid,
						managerid	=:managerid,
						droppingpointid		=:droppingpointid,
						createdon	=:createdon";

						$CheckDroppingEsql	= array(
							"clientid"	=>(int)$_POST['clientid'],
							"managerid"	=>(int)$recordid,
							"droppingpointid"	=>(int)$droppingpointid,
							"createdon"	=>time()
						);

						$AreaQuery	= pdo_query($CheckDroppingSql,$CheckDroppingEsql);
					}
				}
				else
				{
					$NotDroppingPointaArr[]	= $droppingpointid;
				}
			}

			if(!empty($NotDroppingPointaArr))
			{
				$NotDroppingPointStr	= @implode("," ,@array_filter(@array_unique($NotDroppingPointaArr)));

				if(trim($NotDroppingPointStr) == "")
				{
					$NotDroppingPointStr	= "-1";
				}

				$DelSql	= "DELETE FROM ".$Prefix."assigned_dropping_point_linker WHERE droppingpointid IN(".$NotDroppingPointStr.") AND clientid=:clientid AND managerid=:managerid";
				$DelEsql	= array("clientid"=>(int)$_POST['clientid'],"managerid"=>(int)$recordid);

				$DelQuery	= pdo_query($DelSql,$DelEsql);
			}

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
			$response['recordid']	= $recordid;

			$response['name']		= $_POST['name'];

			$response['msg']		= "Area manager successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteAreaManager")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete Area Manager, Please try later.";

	/*$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE areaid=:areaid AND clientid=:clientid AND deletedon < :deletedon";
	$CheckEsql	= array("areaid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Area manager can't be deleted due to customer exist.";

		$json = json_encode($Response);
		echo $json;
		die;
	}*/

	$DelSql		= "UPDATE ".$Prefix."area_manager SET 
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
		$Response['msg']		= "Area manager deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAreaManager")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch Area Manager.";

	$condition	= "";
	$Esql		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Sql	= "SELECT * FROM ".$Prefix."area_manager WHERE clientid=:clientid AND deletedon < :deletedon ".$condition." ORDER BY name ASC";

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

			$hasline	= true;

			if($_POST['type'] == "customerfilter")
			{
				$CheckSql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon AND areaid=:areaid ORDER BY name ASC";
				$CheckEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"areaid"=>(int)$id);

				$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
				$CheckNum	= pdo_num_rows($CheckQuery);

				if($CheckNum < 1)
				{
					$hasline	= false;
				}
			}

			if($hasline)
			{
				$RecordSetArr[$index]['id']		= $id;
				$RecordSetArr[$index]['name']	= $name;

				$index++;
			}
		}
		$response['success']	= true;
		$response['msg']		= "Area manager listed successfully.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAreaByAreaManager")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch area.";

	$AllAssignedArea	= GetAllAssignedAreaByAreaManager($_POST['clientid'],$_POST['recordid']);

	$Sql	= "SELECT * FROM ".$Prefix."area WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$substitute	= "";

			$id				= $rows['id'];
			$name			= $rows['name'];
			$createdon		= $rows['createdon'];
			$substituteid	= $rows['substituteid'];

			$RecordListArr[$index]['id']			= $id;
			$RecordListArr[$index]['name']			= $name;
			$RecordListArr[$index]['addeddate']		= date("d-M-Y",$createdon);
			$RecordListArr[$index]['isassigned']	= false;

			if(in_array($id,$AllAssignedArea))
			{
				$RecordListArr[$index]['isassigned']	= true;
			}

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Area listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetDroppingPointByAreaManager")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch area.";
	$AllDroppingPoints	= GetAllDroppingPointByAreaManager($_POST['clientid'],$_POST['recordid']);
	
	$Sql	= "SELECT * FROM ".$Prefix."dropping_point WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY name ASC";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;
			$substitute	= "";

			$id				= $rows['id'];
			$name			= $rows['name'];
			$createdon		= $rows['createdon'];
			$status			= (int)$rows['status'];

			if($status > 0 || in_array($id,$AllDroppingPoints))
			{
				$statustxt = '';
				if($status < 1)
				{
					$statustxt = " (In-Active)";	
				}
				$RecordListArr[$index]['id']			= $id;
				$RecordListArr[$index]['name']			= $name.$statustxt;
				$RecordListArr[$index]['addeddate']		= date("d-M-Y",$createdon);
				$RecordListArr[$index]['isassigned']	= false;
			}
			if(in_array($id,$AllDroppingPoints))
			{
				$RecordListArr[$index]['isassigned']	= true;
			}

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "dropping point listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

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

	$DelSql		= "UPDATE ".$Prefix."area_manager SET 
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