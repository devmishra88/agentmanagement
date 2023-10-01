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

$usertype	= "hawker";

if($_POST['Mode'] == "AddHawker")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add hawker.";

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
	}

	if($_POST['phone'] != "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE phone=:phone AND clientid=:clientid AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A hawker already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field";
		
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A hawker already exist with same phone";
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

		$Sql	= "INSERT INTO ".$Prefix."hawker SET 
		clientid			=:clientid,
		areaid				=:areaid,
		lineids				=:lineids,
		name				=:name,
		phone				=:phone,
		phone2				=:phone2,
		status				=:status,
		remark				=:remark,
		password			=:password,
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
			"areaid"			=>(int)$_POST['areaid'],
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"phone2"			=>$_POST['phone2'],
			"status"			=>(int)$_POST['status'],
			"remark"			=>$_POST['remarks'],
			"password"			=>$_POST['password'],
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
			$response['msg']		= "hawker successfully added.";
			$response['toastmsg']	= "hawker successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllHawker")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch hawker.";

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

	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		$LineIDArr	= @explode(",",$lineids);
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
			$Cond	.= $LineIDStr;
		}
	}

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ".$Cond."";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$TotalRec	= $Num;

	if($Num > 0)
	{
		$index	= 0;

		$AllAreaArr = GetAllArea($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$LineNameArr	= array();

			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];
			$phone2		= $rows['phone2'];
			$createdon	= $rows['createdon'];
			$areaid		= $rows['areaid'];
			$lineids	= $rows['lineids'];
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

			if(trim($phone) == "")
			{
				$phone	= "---";
			}

			if(trim($phone2) == "")
			{
				$phone2	= "---";
			}

			$lineIdArr	= @explode("::",$lineids);
			$lineIdArr	= @array_filter(@array_unique($lineIdArr));

			$lineIdStr	= @implode(",",$lineIdArr);

			if(trim($lineIdStr) == "")
			{
				$lineIdStr	= "-1";
			}

			$AreaName	= $AllAreaArr[$areaid]['name'];

			if(trim($AreaName) == "")
			{
				$AreaName	= "---";
			}

			$LineSql	= "SELECT * FROM ".$Prefix."line WHERE id IN (".$lineIdStr.") ORDER BY name ASC";
			$LineEsql	= array();

			$LineQuery	= pdo_query($LineSql,$LineEsql);
			$LineNum	= pdo_num_rows($LineQuery);

			if($LineNum > 0)
			{
				while($linerows = pdo_fetch_assoc($LineQuery))
				{
					$linename		= $linerows['name'];
					$LineNameArr[]	= $linename;
				}
			}

			if(!empty($LineNameArr))
			{
				$LineNameStr	= implode(", ",$LineNameArr);
			}
			else
			{
				$LineNameStr	= "---";
			}

			$RecordListArr[$index]['id']		= $id;
			$RecordListArr[$index]['name']		= $name;
			$RecordListArr[$index]['phone']		= $phone;
			$RecordListArr[$index]['phone2']	= $phone2;
			$RecordListArr[$index]['areaname']	= $AreaName;
			$RecordListArr[$index]['linesname']	= $LineNameStr;
			$RecordListArr[$index]['addeddate']	= date("d-M-Y",$createdon);
			$RecordListArr[$index]['dob']		= $dob;
			$RecordListArr[$index]['dobtext']	= $dobtext;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "hawker listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;
	$response['totalrecord']	= $TotalRec;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetHawkerDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch hawker detail.";

	$sql	= "SELECT * FROM ".$Prefix."hawker WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
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
		$detailArr["phone2"]			= $row['phone2'];
		$detailArr["status"]			= $row['status'];
		$detailArr["remark"]			= $row['remark'];
		$detailArr["password"]			= $row['password'];
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
		$response['msg']		= "hawker detail fetched successfully.";
	}

	$response['hawkerdetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditHawker")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to save hawker.";

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
	/*if($_POST['phone'] == "")
	{
		$ErrorMsg	.= "Please enter phone.<br>";
	}

	if($_POST['phone'] != "")
	{
		$CheckSql	= "SELECT * FROM ".$Prefix."hawker WHERE phone=:phone AND clientid=:clientid AND id<>:id AND deletedon < :deletedon";
		$CheckEsql	= array("phone"=>$_POST['phone'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid'],"deletedon"=>1);

		$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
		$CheckNum	= pdo_num_rows($CheckQuery);

		if($CheckNum > 0)
		{
			$haserror	= true;
			$ErrorMsg	.= "A hawker already exist with same phone.<br>";
		}
	}*/

	if($ErrorMsg != "")
	{
		$haserror	= true;
		$response['msg']	= $ErrorMsg;
		$response['toastmsg']	= "There is a error to update record.";
		if($CheckNum > 0)
		{
			$response['toastmsg']	= "A hawker already exist with same phone";
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

		$Sql	= "UPDATE ".$Prefix."hawker SET 
		lineids				=:lineids,
		areaid				=:areaid,
		name				=:name,
		phone				=:phone,
		phone2				=:phone2,
		status				=:status,
		remark				=:remark,
		password			=:password,
		hasdob				=:hasdob,
		dob					=:dob,
		isidproofrequired	=:isidproofrequired,
		idproofdetail		=:idproofdetail
		".$ExtArg."
		WHERE
		id			=:id";

		$Esql	= array(
			"lineids"			=>$lineids,
			"areaid"			=>(int)$_POST['areaid'],
			"name"				=>$_POST['name'],
			"phone"				=>$_POST['phone'],
			"phone2"			=>$_POST['phone2'],
			"status"			=>(int)$_POST['status'],
			"remark"			=>$_POST['remarks'],
			"password"			=>$_POST['password'],
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
			$response['msg']		= "hawker successfully updated.";
			$response['toastmsg']	= "hawker successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteHawker")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete hawker, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."customers WHERE hawkerid=:hawkerid AND clientid=:clientid AND deletedon < :deletedon";
	$CheckEsql	= array("hawkerid"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "Hawker can't be deleted due to customer exist.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$DelSql		= "UPDATE ".$Prefix."hawker SET 
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
		$Response['msg']		= "hawker deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetHawker")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch hawker.";

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
	$cond	= "";

	if($_POST['type'] == "sequencefilter")
	{
		$cond				.= " AND areaid=:areaid AND lineids LIKE :lineids AND lineids NOT LIKE :lineids2 AND areaid<>:areaid2";
		
		$Esql['areaid']		= (int)$_POST['areaid'];
		$Esql['areaid2']	= 0;
		$Esql['lineids']	= "%::".$_POST['lineid']."::%";
		$Esql['lineids2']	= "%::::%";
	}
	else
	{
		if($_POST['areaid'] > 0)
		{
			$cond			.= " AND areaid=:areaid";
			$Esql['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['lineid'] > 0)
		{
			$cond				.= " AND lineids LIKE :lineids";
			$Esql['lineids']	= "%::".$_POST['lineid']."::%";
		}
	}

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

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ".$cond." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($_POST['type'] == "sequencefilter")
	{
		$RecordSetArr[$index]['id']		= '';
		$RecordSetArr[$index]['name']	= 'Select';

		$index++;
	}

	if($_POST['fromarea'] == "salefilter")
	{
		$RecordSetArr[$index]['id']		= '';
		$RecordSetArr[$index]['name']	= 'All Hawker';

		$index++;
	}

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
	}

	$response['success']	= true;
	$response['msg']		= "hawker listed successfully.";

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

	$DelSql		= "UPDATE ".$Prefix."hawker SET 
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
if($_POST['Mode'] == "CheckHawker")
{
	$RecordSetArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch single hawker.";

	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
	$cond	= "";

	if($_POST['type'] == "sequencefilter")
	{
		$cond				.= " AND areaid=:areaid AND lineids LIKE :lineids AND lineids NOT LIKE :lineids2 AND areaid<>:areaid2";
		
		$Esql['areaid']		= (int)$_POST['areaid'];
		$Esql['areaid2']	= 0;
		$Esql['lineids']	= "%::".$_POST['lineid']."::%";
		$Esql['lineids2']	= "%::::%";
	}
	else
	{
		if($_POST['areaid'] > 0)
		{
			$cond			.= " AND areaid=:areaid";
			$Esql['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['lineid'] > 0)
		{
			$cond				.= " AND lineids LIKE :lineids";
			$Esql['lineids']	= "%::".$_POST['lineid']."::%";
		}
	}

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

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon ".$cond." ORDER BY name ASC";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num == 1)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id		= $rows['id'];
			$name	= $rows['name'];

			$RecordSetArr['id']		= $id;
			$RecordSetArr['name']	= $name;

			$index++;
		}
	}

	if($Num == 1)
	{
		$response['success']	= true;
		$response['msg']		= "Single hawker found.";
	}

	$response['recordlist']	= $RecordSetArr;

    $json = json_encode($response);
    echo $json;
	die;
}