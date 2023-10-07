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
if($_POST['Mode'] == "AddImage")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to add image.";
    $imagefile ='';

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
    }
    if($_FILES['imagefile']['name'] !='')
    {
      	$Ext = strrchr($_FILES['imagefile']['name'],".");
        $rand = substr(md5(uniqid(microtime())), 0, 3);
        $imagefile = str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$_FILES['imagefile']['name']));
        $imagefile = str_replace(" ",'_',$imagefile);
        $Move2 = @move_uploaded_file($_FILES['imagefile']['tmp_name'], $Uploadimage.$imagefile);
        if(!$Move2)
        {
            $ErrorMsg .= "<br/>Unable to upload image.";
        }
    }
    if($ErrorMsg != "")
	{
		$haserror	= true;
        $response['msg']		= $ErrorMsg;
		$response['toastmsg']	= "Please enter all required field.";
    }
    else
    {   
        $CheckSql	= "SELECT * FROM ".$Prefix."gallery WHERE name=:name AND clientid=:clientid AND deletedon <:deletedon";
		$CheckEsql	= array("name"=>$_POST['name'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
        
        $CheckQuery = pdo_query($CheckSql,$CheckEsql);
        $CheckNum   = pdo_num_rows($CheckQuery);
        
        if($CheckNum > 0)
		{
			$response['toastmsg']	= "A image already exist with same name.";
            $haserror = true;
        }
	}

	if($haserror == false)
	{
		$Sql	= "INSERT INTO ".$Prefix."gallery SET 
		clientid	=:clientid,
		name		=:name,
		imagefile	=:imagefile,
		status		=:status,
		orderby		=:orderby,
		createdon	=:createdon";

		$Esql	= array(
			"clientid"	=>(int)$_POST['clientid'],
			"name"		=>$_POST['name'],
			"imagefile"	=>$imagefile,
			"status"	=>(int)$_POST['status'],
			"orderby"	=>(int)$_POST['order'],
			"createdon"	=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query)
		{
			$recordid	= pdo_insert_id();
			
			$response['success']	= true;
			$response['recordid']	= $recordid;
			$response['name']		= $_POST['name'];
			$response['msg']		= "Image successfully added.";
			$response['toastmsg']	= "Image successfully added.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetAllImages")
{
	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch images.";

	$Sql	= "SELECT * FROM ".$Prefix."gallery WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$imagepath		= "";
			$statustext		= "Active";

			$id				= $rows['id'];
			$name			= $rows['name'];
			$orderby		= $rows['orderby'];
			$imagefile		= $rows['imagefile'];
			$status			= $rows['status'];
			$createdon		= $rows['createdon'];

			if($status < 1)
			{
				$statustext	= "Inactive";
			}

			if($imagefile !='' AND file_exists($Uploadimage.$imagefile))
			{
				$imagepath = $Uploadimage.$imagefile;
			}

			$imagepath	= str_replace("../","",$imagepath);

			$RecordListArr[$index]['id']		= (int)$id;
			$RecordListArr[$index]['name']		= $name;
			$RecordListArr[$index]['image']		= $imagepath;
			$RecordListArr[$index]['status']	= $statustext;
			$RecordListArr[$index]['order']		= (int)$orderby;
		
			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "record listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "GetImageDetail")
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch image detail.";

	$sql	= "SELECT * FROM ".$Prefix."gallery WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);
        
		$imagefile	= $row['imagefile'];

		if($imagefile !='' AND file_exists($Uploadimage.$imagefile))
		{
			$imagepath = $Uploadimage.$imagefile;
		}

		$imagepath	= str_replace("../","",$imagepath);

		$detailArr["name"]		= $row['name'];
		$detailArr["orgfile"]	= $row['imagefile'];
		$detailArr["imagefile"]	= $imagepath;
		$detailArr["status"]	= $row['status'];
		$detailArr["order"]		= $row['orderby'];

		$response['success']	= true;
		$response['msg']		= "Image detail fetched successfully.";
	}

	$response['detail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "EditImage")
{
	$haserror	= false;
    $response['success']	= false;
    $response['msg']		= "Unable to save image.";
	$imagefile ='';

	if($_POST['name'] == "")
	{
		$ErrorMsg	.= "Please enter name.<br>";
    }
    else if($_FILES['imagefile']['name'] !='')
    {
      	$Ext = strrchr($_FILES['imagefile']['name'],".");
        $rand = substr(md5(uniqid(microtime())), 0, 3);
        $imagefile = str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$_FILES['imagefile']['name']));
        $imagefile = str_replace(" ",'_',$imagefile);
        $Move2 = @move_uploaded_file($_FILES['imagefile']['tmp_name'], $Uploadimage.$imagefile);
        if(!$Move2)
        {
            $ErrorMsg .= "<br/>Unable to upload image.";
        }
    }
    else
    {
        $CheckSql	= "SELECT * FROM ".$Prefix."gallery WHERE name=:name AND clientid=:clientid AND deletedon <:deletedon AND id <> :id";
		$CheckEsql	= array("name"=>$_POST['name'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"id"=>(int)$_POST['recordid']);
       
        $CheckQuery = pdo_query($CheckSql,$CheckEsql);
        $CheckNum   = pdo_num_rows($CheckQuery);
       
        if($CheckNum > 0)
		{
            $haserror	= true;
			$response['toastmsg']	= "A image already exist with same name";
		}
	}

	if($haserror == false)
	{
        $ExtArg = '';
        $ExtArr = array();
        if($imagefile != "")
        {
            $ExtArg = ',imagefile =:imagefile';
            $ExtArr['imagefile'] = $imagefile;
        }

		$Sql	= "UPDATE ".$Prefix."gallery SET 
		name		=:name,
        status		=:status,
		orderby		=:orderby
        $ExtArg
		WHERE
		id			=:id";

		$Esql	= array(
			"name"		=>$_POST['name'],
			"status"	=>(int)$_POST['status'],
			"orderby"	=>(int)$_POST['order'],
			"id"		=>(int)$_POST['recordid']
		);
		
        $Arr    = array_merge($ExtArr,$Esql);
        
		$Query	= pdo_query($Sql,$Arr);
		
		if($Query)
		{
			$response['success']	= true;
			$response['msg']		= "Image successfully updated.";
			$response['toastmsg']	= "Image successfully updated.";
		}
	}

    $json = json_encode($response);
    echo $json;
	die;
}
if($_POST['Mode'] == "DeleteImage")
{
	$time	= time();

	$Response['success']	= false;
	$Response['msg']		= "Unable to delete image, Please try later.";
   
    $Sql	= "SELECT * FROM ".$Prefix."gallery WHERE clientid=:clientid AND deletedon < :deletedon AND id=:id";
	$Esql	= array("clientid"=>(int)$_POST["clientid"],"deletedon"=>1,'id'=>(int)$_POST['recordid']);

	$Query	= pdo_query($Sql,$Esql);
    $Num	= pdo_num_rows($Query);
    if($Num > 0)
    {
        /*$row        = pdo_fetch_assoc($Query);
        $imagefile  = $row['imagefile'];
        if($imagefile !='')
        {
			@unlink($Uploadimage.$imagefile);
        }*/
	
        $DelSql		= "UPDATE ".$Prefix."gallery SET 
        deletedon	=:deletedon 
        WHERE 
        id			=:id
        AND 
        clientid	=:clientid";

        $DelEsql	= array(
            "deletedon"	=>$time,
            'id'		=>(int)$_POST['recordid'],
            "clientid"	=>(int)$_POST['clientid']	
        );

        $DelQuery	= pdo_query($DelSql,$DelEsql);

        if($DelQuery)
        {
            $Response['success']	= true;
            $Response['msg']		= "Image deleted successfully.";
        }
    }
    $json = json_encode($Response);
    echo $json;
	die;
}
?>