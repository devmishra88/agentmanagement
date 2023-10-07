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

if($_POST['Mode'] == "AddCampaign")
{
	$haserror = false;
	$Response['success']	= false;
	$Response['msg']		= "Unable to save campaign, Please try later.";

	$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
	$Esql	= array("id"=>(int)$_POST['clientid']);

	$Query	= pdo_query($Sql,$Esql);
	$Rows	= pdo_fetch_assoc($Query);

	$clientname	= $Rows['clientname'];

	$totalrecords = 0;
	
	$scheduleddate = time();

	if($_POST['areaid'] == "" && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "Please select an area.";
	}
	if($_POST['lineid'] == "" && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "Please select a line.";
	}
	if($_POST['customerid'] == "" && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "Please select a customer.";
	}
	if(trim($_POST['message']) =='' && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "Please enter a message.";
	}
	if((int)$_POST['isscheduled'] > 0 && $haserror == false)
	{
		$currentdate	= strtotime('today');
		$Currenttime	= time();

		$scheduleddate = strtotime($_POST['scheduleddate']." ".$_POST['scheduledtime']);

		if($scheduleddate < $currentdate)
		{
			$haserror = true;
			$Response['msg']	= "Please select a future date.";
		}
		else
		{
			if(trim($_POST['scheduledtime']) =='')
			{
				$haserror = true;
				$Response['msg']	= "Please select a schedulted time.";
			}
		}
	}

	$custcondition	= "";
	$CustEsql		= array();

	$custcondition	= " AND cust.deletedon <:deletedon AND cust.phone <>:phone";
	$CustEsql		= array("deletedon"=>1,"phone"=>"");

	if($_POST['clientid'] > 0)
	{
		$custcondition	.= " AND cust.clientid=:clientid";
		$CustEsql['clientid']	= (int)$_POST['clientid'];
	}
	if($_POST['customerid'] > 0)
	{
		$custcondition	.= " AND cust.id=:id";
		$CustEsql['id']	= (int)$_POST['customerid'];
	}

	if($_POST['lineid'] > 0)
	{
		if($_POST['lineid'] == 9999)
		{
			$custcondition	.= " AND cust.lineid < :lineid";
			$CustEsql['lineid']	= 1;
		}
		else
		{
			$custcondition	.= " AND cust.lineid=:lineid";
			$CustEsql['lineid']	= (int)$_POST['lineid'];
		}
	}

	if($_POST['areaid'] > 0)
	{
		if($_POST['areaid'] == 9999)
		{
			$custcondition	.= " AND cust.areaid < :areaid";
			$CustEsql['areaid']	= 1;
		}
		else
		{
			$custcondition	.= " AND cust.areaid=:areaid";
			$CustEsql['areaid']	= (int)$_POST['areaid'];
		}
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$custcondition	.= " AND cust.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$custcondition	.= " AND cust.lineid IN(".$lineids.")";
	}

	if($_POST['smstype'] == 'invoicesms')
	{
		$MonthYearArr = explode("-",$_POST['monthyear']);
		$Month	= $MonthYearArr[1];
		$Year	= $MonthYearArr[0];

		$CustomerSql	= "SELECT cust.* FROM ".$Prefix."invoices inv,".$Prefix."customers cust WHERE inv.invoicemonth=:invoicemonth AND inv.invoiceyear=:invoiceyear AND inv.deletedon < :deletedon2 AND inv.customerid=cust.id".$custcondition." ORDER BY cust.customerid ASC, cust.status DESC";
		$CustEsql['deletedon2']		= 1;
		$CustEsql['invoicemonth']	= (int)$Month;
		$CustEsql['invoiceyear']	= (int)$Year;
	}
	else
	{
		$CustomerSql	= "SELECT * FROM ".$Prefix."customers cust WHERE 1 ".$custcondition." ORDER BY customerid ASC, status DESC";
	}

	$CustomerQuery	= pdo_query($CustomerSql,$CustEsql);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	$creditwilluse	= (int)$CustomerNum*(int)$_POST['smscredit'];

	/*$Checkcond	= " AND deletedon <:deletedon AND credittype=:credittype AND packagetype=:packagetype";
	$CheckEsql	= array("deletedon"=>1,"credittype"=>1,"packagetype"=>(int)$_POST['sendertype']);*/

	if($creditwilluse < 1 && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "No record found to schedule campaign.";
	}

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($_POST['clientid']);

	if(($totalsmscreditsavaiable < $creditwilluse) && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "You don't have sufficient credit to schedule campaign.";
	}

	if($haserror == false)
	{
		$monthyear		= 0;
		$invoicemonth	= 0;
		$invoiceyear	= 0;

		if($_POST['smstype'] == 'invoicesms')
		{
			$monthyear	= strtotime($_POST['monthyear'])+((60*60)*4);

			/*$monthyearArr	= explode("-",$_POST['monthyear']);
			$invoicemonth	= $monthyearArr[1];
			$invoiceyear	= $monthyearArr[0];
			*/

			$invoicemonth	= date("m",$monthyear);
			$invoiceyear	= date("Y",$monthyear);
		}

		$Sql	= "INSERT INTO ".$Prefix."campaign SET 
		clientid		=:clientid,
		smstype			=:smstype,
		monthyear		=:monthyear,
		areaid			=:areaid,
		lineid			=:lineid,
		customerid		=:customerid,
		smscredit		=:smscredit,
		message			=:message,
		scheduleddate	=:scheduleddate,
		invoicemonth	=:invoicemonth,
		invoiceyear		=:invoiceyear,
		createdon		=:createdon";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"smstype"			=>$_POST['smstype'],
			"monthyear"			=>$monthyear,
			"areaid"			=>$_POST['areaid'],
			"lineid"			=>$_POST['lineid'],
			"customerid"		=>$_POST['customerid'],
			"smscredit"			=>$_POST['smscredit'],
			"message"			=>$_POST['message'],
			"scheduleddate"		=>$scheduleddate,
			"invoicemonth"		=>(int)$invoicemonth,
			"invoiceyear"		=>(int)$invoiceyear,
			"createdon"			=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query && !is_array($Query))
		{
			$campaignid	= pdo_insert_id();

			$Totalrecords	= 0;

			if($CustomerNum > 0)
			{
				while($CustomerRows = pdo_fetch_assoc($CustomerQuery))
				{
					$contactid			= $CustomerRows['id'];
					$phone				= $CustomerRows['phone'];
					$name				= $CustomerRows['name'];

					if(trim($name) == "")
					{
						$name	= "Customer";
					}

					if($phone !='' && strlen($phone) == 10)
					{
						$message	= $_POST['message'];

						$SqlHistory	= "INSERT INTO ".$Prefix."campaign_history SET 
						campid			=:campid,
						message			=:message,
						smscredit		=:smscredit,
						createdon		=:createdon,
						contactid		=:contactid,
						phonenumber		=:phonenumber,
						clientid		=:clientid";

						$EsqlHistory	= array(
							"campid"		=>(int)$campaignid,
							"message"		=>$message,
							"smscredit"		=>(int)$_POST['smscredit'],
							"createdon"		=>$createdon,
							"contactid"		=>$contactid,
							"phonenumber"	=>$phone,
							"clientid"		=>(int)$_POST['clientid']
						);

						$HistoryQuery	= pdo_query($SqlHistory,$EsqlHistory);

						if($HistoryQuery && !is_array($HistoryQuery))
						{
							$Totalrecords++;
						}
					}
				}

				$UpdateSQL	  = "UPDATE ".$Prefix."campaign SET totalrecords=:totalrecords,isdataprocessed=:isdataprocessed,isdatainprogress=:isdatainprogress WHERE id=:id";
				$UpdateESQL	  = array("id"=>(int)$campaignid,'totalrecords'=>(int)$Totalrecords,"isdataprocessed"=>1,'isdatainprogress'=>0);
				$query_update = pdo_query($UpdateSQL,$UpdateESQL);
			}

			$Response['success']	= true;
			$Response['msg']		= "Campaign added successfully.";
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "GetCampaignDetail")
{
	$RecordSetArr	= array();
	$ListArr		= array();

	$Response['success']	= false;
	$Response['msg']		= "Unable to fetch messaging list, Please try later.";

	$Cond	= " AND clientid=:clientid AND status=:status AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,'status'=>1);

	if($_POST['staffid'] > 0)
	{
		$Cond	.= " AND (staffid=:staffid || canstaffuse=:canstaffuse)";
		$Esql['staffid']		= (int)$_POST['staffid'];
		$Esql['canstaffuse']	= 1;
	}

	$Sql	= "SELECT * FROM ".$Prefix."phone_list WHERE 1 ".$Cond." ORDER BY name ASC";

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

		$countsql = "SELECT * FROM ".$Prefix."phone_list WHERE clientid=:clientid AND deletedon <:deletedon AND status=:status AND totalactivecontacts >:totalactivecontacts ORDER BY name ASC";
		$countesql = array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>1,'totalactivecontacts'=>0);
		
		$countquery = pdo_query($countsql,$countesql);
		$countnum	= pdo_num_rows($countquery);
		if($countnum > 0)
		{
			while($rowlist = pdo_fetch_assoc($countquery))
			{
				$id		= $rowlist['id'];
				$name	= $rowlist['name'];
				$count	= $rowlist['totalactivecontacts'];
	
				if($count > 0)
				{
					$ListArr[$index]['id']			= $id;
					$ListArr[$index]['name']		= $name." (".$count.")";
					$ListArr[$index]['ischecked']	= false;
	
					$index++;
				}	
			}
		}
		
	}

	$Sql	= "SELECT * FROM ".$Prefix."client_sender_log WHERE clientid=:clientid AND transactional=:transactional AND deletedon < :deletedon ORDER BY senderid ASC";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],'transactional'=>1,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num == 1)
	{
		$rows	= pdo_fetch_assoc($Query);
		$id		= $rows['id'];
		$name	= $rows['senderid'];

		$RecordSetArr['defaultsender']['id']	= $id;
		$RecordSetArr['defaultsender']['name']	= $name;
	}
	else
	{
		$RecordSetArr['defaultsender']['id']	= '';
		$RecordSetArr['defaultsender']['name']	= 'Select';
	}

	if($Num < 1)
	{
		$RecordSetArr['defaultsender']['id']	= '-1';
		$RecordSetArr['defaultsender']['name']	= $DefaultSenderID;
	}

	$RecordSetArr['recordlist']		= $ListArr;

	if(!empty($RecordSetArr))
	{
		$Response['success']		= true;
		$Response['recordlist']		= $RecordSetArr;
		$Response['campaignlist']	= $ListArr;
		$Response['msg']			= "campaign detail fetched successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "EditCampaign")
{
	$haserror = false;
	$Response['success']	= false;
	$Response['msg']		= "Unable to save campaign, Please try later.";
	
	$scheduleddate = time();

	$sql	= "SELECT * FROM ".$Prefix."campaign WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);

	if(is_array($query))
	{
		$response['msg']	= $query['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$totalsent = $row['totalsent'];
		if($totalsent > 0 )
		{
			$response['dialogmsg']	= "Campaign cannot be edited, as it is already in queue to send sms";
			$response['msg']		= "Campaign cannot be edited, as it is already in queue to send sms";
			$response['toastmsg']	= "Campaign cannot be edited, as it is already in queue to send sms";

			$json = json_encode($response);
			echo $json;
			die;
		}
	}
	if($_POST['campaignname'] == "")
	{
		$haserror = true;
		$Response['msg']	= "Please enter campaign name to send campaign.";
	}
	else if($_POST['campaignname'] != "")
	{
		$CheckSql	= "SELECT COUNT(*) AS C FROM ".$Prefix."campaign WHERE campaignname=:campaignname AND clientid=:clientid AND id<>:id";
		$CheckEsql	= array("campaignname"=>$_POST['campaignname'],"clientid"=>(int)$_POST['clientid'],"id"=>(int)$_POST['recordid']);

		$CheckQuery		= pdo_query($CheckSql,$CheckEsql);
		$CheckRows		= pdo_fetch_assoc($CheckQuery);

		$checknum	= $CheckRows['C'];

		if($checknum > 0)
		{
			$haserror = true;
			$Response['msg']	= "Campaign already exists with the same name.";
		}
	}

	$listArr = @explode(",",$_POST['selectedlist']);

	if($_POST['datatype'] === 'list')
	{
		if(empty($listArr) && $_POST['listtype'] == 1)
		{	
			$haserror = true;
			$Response['msg']		= "Please select atleast one list to send campaign.";
		}

		if($_POST['listid'] == "" && $_POST['listtype'] == 0)
		{
			$haserror = true;
			$Response['msg']	= "Please select a list to send campaign.";
		}
		
		if($_POST['listtype'] == 0 && ($_POST['fromposition'] == "" || $_POST['toposition'] == "") && $haserror == false)
		{
			$haserror = true;
			$Response['msg']	= "From and To position can not be blank.";
		}
	}

	if((int)$_POST['isscheduled'] > 0 && $haserror == false)
	{
		$currentdate	= strtotime('today');
		$Currenttime	= time();	
		
		$CheckFutureDate = 0;

		$scheduleddate = strtotime($_POST['scheduleddate']." ".$_POST['scheduledtime']);

		if($scheduleddate < $currentdate && $CheckFutureDate > 0)
		{
			$haserror = true;
			$Response['msg']		= "Please select a future date.";
		}
		else
		{
			if(trim($_POST['scheduledtime']) =='')
			{
				$haserror = true;
				$Response['msg']		= "Please select a schedulted time.";
			}
		}
	}

	if(trim($_POST['message']) =='' && $haserror == false)
	{
		$haserror = true;
		$Response['msg']	= "Please enter a message.";
	}

	/*$availablecredit		= 0;
	$totalsmscreditsused	= 0;

	$Checkcond	= " AND deletedon <:deletedon AND credittype=:credittype AND packagetype=:packagetype";
	$CheckEsql	= array("deletedon"=>1,"credittype"=>1,"packagetype"=>(int)$_POST['sendertype']);

	if($_POST['clientid'] > 0)
	{
		$Checkcond	.= " AND clientid=:clientid";
		$CheckEsql['clientid']	= (int)$_POST['clientid'];
	}

	$CheckSql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$Checkcond." ORDER BY status DESC, createdon DESC";

	$CheckQuery		= pdo_query($CheckSql,$CheckEsql);
	$CheckNum		= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		while($checkrows = pdo_fetch_assoc($CheckQuery))
		{
			$type	= $checkrows['type'];

			if($type > 0)
			{
				$availablecredit		+= (int)$checkrows['smscredits'];
			}
			else
			{
				$totalsmscreditsused	+= (int)$checkrows['smscredits'];
			}
		}
	}
	

	$SqlCount	= "SELECT SUM(history.smscredit) AS C FROM ".$Prefix."campaign_history history,".$Prefix."campaign campaign WHERE campaign.id=history.campid AND campaign.sendertype=:sendertype AND history.clientid=:clientid AND history.issent >:issent";
	$EsqlCount	= array("sendertype"=>(int)$_POST['sendertype'],'clientid'=>(int)$_POST['clientid'],'issent'=>0);

	$QueryCount	= pdo_query($SqlCount,$EsqlCount);
	$totalsmscreditsusedrow	= pdo_fetch_assoc($QueryCount);
	
	$totalsmscreditsused		+= $totalsmscreditsusedrow['C'];
	$totalsmscreditsavaiable	= ((int)$availablecredit - (int)$totalsmscreditsused);
	*/
	$listidstr	= @implode(",",@array_filter(@array_unique($listArr)));

	if(trim($listidstr) == "")
	{
		$listidstr	= $_POST['selectedlist'];
	}

	if(trim($listidstr) == "")
	{
		$listidstr	= "-1";
	}

	if($_POST['datatype'] === 'list')
	{
		if($_POST['listtype'] == 1)
		{
			$ContactSql		= "SELECT SUM(totalactivecontacts) AS C FROM ".$Prefix."phone_list WHERE clientid=:clientid AND deletedon <:deletedon AND id IN (".$listidstr.")";
			$ContactEsql		= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1);
		
			$ContactQuery	= pdo_query($ContactSql,$ContactEsql);
			$ContactRow		= pdo_fetch_assoc($ContactQuery);
			$ContactNum		= $ContactRow['C'];
		}
		else
		{
			$querylimit = (($_POST['toposition'] - $_POST['fromposition']) +1);

			$offset		=  $_POST['fromposition'] - 1;

			if($offset < 1)
			{
				$offset = 0;
			}

			$ContactNum = $querylimit;
		}
	}
	$totalrecords = $ContactNum; 
	if($haserror == false)
	{
		$groupid	= (int)$_POST['groupid'];

		if($_POST['groupname'] != "" && $_POST['isnewgroup'] > 0)
		{
			$GroupSql	= "INSERT INTO ".$Prefix."campaign_group SET 
			clientid	=:clientid,
			name		=:name,
			status		=:status,
			canstaffuse	=:canstaffuse,
			createdon	=:createdon";

			$GroupEsql	= array(
				"clientid"		=>(int)$_POST['clientid'],
				"name"			=>$_POST['groupname'],
				"status"		=>1,
				"canstaffuse"	=>1,
				"createdon"		=>$createdon
			);

			$GroupQuery	= pdo_query($GroupSql,$GroupEsql);

			if($GroupQuery && !is_array($GroupQuery))
			{
				$groupid	= pdo_insert_id();
			}
		}

		$listids	= "::".implode("::",array_filter(array_unique($listArr)))."::";

		$Sql	= "UPDATE ".$Prefix."campaign SET 
		clientid			=:clientid,
		filterlist			=:filterlist,
		scheduleddate		=:scheduleddate,
		message				=:message,
		smscredit			=:smscredit,
		totalrecords		=:totalrecords,
		listtype			=:listtype,
		fromposition		=:fromposition,
		toposition			=:toposition,
		listid				=:listid,
		campaignname		=:campaignname,
		status				=:status,
		senderrecordid		=:senderrecordid,
		senderid			=:senderid,
		sendertype			=:sendertype,
		language			=:language,
		groupid				=:groupid
		WHERE
		id					=:id";

		$Esql	= array(
			"clientid"			=>(int)$_POST['clientid'],
			"filterlist"		=>$listids,
			"scheduleddate"		=>$scheduleddate,
			"message"			=>$_POST['message'],
			"smscredit"			=>$_POST['smscredit'],
			"totalrecords"		=>$totalrecords,
			"listtype"			=>(int)$_POST['listtype'],
			"fromposition"		=>(int)$_POST['fromposition'],
			"toposition"		=>(int)$_POST['toposition'],
			"listid"			=>(int)$_POST['listid'],
			"campaignname"		=>$_POST['campaignname'],
			"status"			=>0,
			"senderrecordid"	=>(int)$_POST['senderrecordid'],
			"senderid"			=>$_POST['senderid'],
			"sendertype"		=>(int)$_POST['sendertype'],
			"language"			=>(int)$_POST['language'],
			"groupid"			=>(int)$groupid,
			"id"				=>(int)$_POST['recordid']
		);

		$Query	= pdo_query($Sql,$Esql);

		if($Query && !is_array($Query))
		{
			$campaignid	= $_POST['recordid'];

			if($_POST['datatype'] === 'list')
			{
				$sql  = "DELETE FROM ".$Prefix."campaign_history WHERE campid=:campid";
				$esql = array('campid'=>(int)$_POST['recordid']);

				$delquery = pdo_query($sql,$esql);

				$listidstr	= @implode(",",@array_filter(@array_unique($listArr)));

				if(trim($listidstr) == "")
				{
					$listidstr	= $_POST['selectedlist'];
				}

				if($_POST['listtype'] == 1)
				{
					$condition = " AND cl.listid IN (".$listidstr.") order by cl.id";
					$Esql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,'status'=>1);
				}
				else
				{
					$querylimit = (($_POST['toposition'] - $_POST['fromposition']) +1);

					$offset		=  $_POST['fromposition'] - 1;

					if($offset < 1)
					{
						$offset = 0;
					}

					$condition = " AND cl.listid=:listid order by cl.id LIMIT $offset, $querylimit";
					$Esql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,'status'=>1,"listid"=>(int)$_POST['listid']);
				}

				$smscredits	= (int)$_POST['smscredit'];

				$createdon  = time();
				
				$trackablelink = GetURLFromString($_POST['message']);
				
				$BigSQL = "INSERT IGNORE INTO ".$Prefix."campaign_history(campid,message,trackablelink,smscredit,createdon,contactid,phonenumber,clientid) SELECT '$campaignid','".addslashes($_POST['message'])."','".$trackablelink."','$smscredits','$createdon',cl.id,cl.phone,cl.clientid FROM ".$Prefix."phone_contacts cl INNER JOIN ".$Prefix."phone_list ls ON cl.listid = ls.id WHERE cl.deletedon <:deletedon AND cl.status=:status AND cl.clientid=:clientid $condition";
				
				$QueryContact = pdo_query($BigSQL,$Esql);

				/*while($RowContact = pdo_fetch_assoc($QueryContact))
				{
					$contactid	= $RowContact['id'];
					$phonenumber= $RowContact['phone'];

					$smscredits	= ceil(strlen(trim($_POST['message'])) / 160);

					$sqlhistory	= "INSERT INTO ".$Prefix."campaign_history SET 
					clientid			=:clientid,
					campid				=:campid,
					contactid			=:contactid,
					phonenumber			=:phonenumber,
					message				=:message,
					smscredit			=:smscredit,
					createdon			=:createdon";

					$esqlhistory	= array(
						"clientid"			=>(int)$_POST['clientid'],
						"campid"			=>$campaignid,
						"contactid"			=>$contactid,
						"phonenumber"		=>$phonenumber,
						"message"			=>$_POST['message'],
						"smscredit"			=>$smscredits,
						"createdon"			=>time()
					);

					$Query	= pdo_query($sqlhistory,$esqlhistory);
				}*/
			}

			$Response['success']	= true;
			$Response['msg']		= "Campaign edited successfully.";
		}
		else
		{
			$response['msg']	= $Query['errormessage'];
		}
	}
    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "GetAllCampaign")
{
	$perpage = 10;

	if($_POST['perpage'] != '')
	{
		$perpage = $_POST['perpage'];
	}
	if($_POST['page'] == '')
	{
		$_POST['page'] = 1;
	}

	$RecordListArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch campaigns.";

	$showarchive	= false;

	$StatusIDArr	= array();

	if(trim($_POST['statusids']) != "")
	{
		$StatusIDArr	= @explode(",",$_POST['statusids']);
		$StatusIDArr	= @array_unique($StatusIDArr);
	}

	if(!empty($StatusIDArr))
	{
		if(in_array("9999", $StatusIDArr))
		{
			$showarchive	= true;
		}
	}

	$condition	= " AND clientid=:clientid AND deletedon < :deletedon";
	$Esql		= array('clientid'=>(int)$_POST['clientid'],"deletedon"=>1);

	if(!$showarchive)
	{
		$condition	.= " AND isarchive=:isarchive";
		$Esql["isarchive"]	= 0;
	}
	if($showarchive == true && count($StatusIDArr) == 1)
	{
		$condition	.= " || (isarchive=:isarchive2)";
		$Esql["isarchive2"]	= 1;
	}

	if($_POST['statusid'] != "")
	{
		$condition	.= " AND status=:status ";
		$Esql['status']	= (int)$_POST['statusid'];
	}

	if($_POST['staffid'] > 0)
	{
		$condition	.= " AND staffid=:staffid ";
		$Esql['staffid']	= (int)$_POST['staffid'];
	}

	if(trim($_POST['campids']) != "")
	{
		$campidstr	= $_POST['campids'];
		if(trim($campidstr) == "")
		{
			$campidstr	= "-1";
		}
		$condition		.= " AND id IN(".$campidstr.")";
	}

	if(!empty($StatusIDArr))
	{
		$StatusIDArr	= @array_diff($StatusIDArr, [9999]);

		$statusidstr	= @implode(",",$StatusIDArr);

		if(trim($statusidstr) != "")
		{
			$condition	.= " AND status IN(".$statusidstr.")";
		}
	}

	if($_POST['groupid'] > 0)
	{
		$condition	.= " AND groupid=:groupid ";
		$Esql['groupid']	= (int)$_POST['groupid'];
	}

	$Sql	= "SELECT * FROM ".$Prefix."campaign WHERE 1 ".$condition." ORDER BY status ASC, scheduleddate DESC";

	$Query	= pdo_query($Sql,$Esql);
	if(is_array($Query))
	{
		$response['msg']	= $Query['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}
	$Num		= pdo_num_rows($Query);
	$TotalRec	= $Num;

	if($Num > 0)
	{
		$totalpages	= ceil($Num/$perpage);
		$offset		= ($_POST['page'] - 1) * $perpage;
		$addquery	= " LIMIT %d, %d";
	}
	else
	{
		$addquery	= "";
	}

	$Sql2	= $Sql.$addquery;
	$Sql2	= sprintf($Sql2, intval($offset), intval($perpage));
	$Query2	= pdo_query($Sql2,$Esql);
	
	if(is_array($Query2))
	{
		$response['msg']	= $Query2['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}
	$Num2	= pdo_num_rows($Query2);

	if($Num2 > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query2))
		{
			$filterliststr	= "";
			$isselected		= false;
			$ispartial		= false;

			$listid			= "";
			$id				= $rows['id'];
			$filterList		= $rows['filterlist'];
			$message		= $rows['message'];
			$isdataprocessed= $rows['isdataprocessed'];
			$totalrecords	= $rows['totalrecords'];
			$totalsent		= $rows['totalsent'];
			$totalrefunds	= $rows['totalrefunds'];
			/*$total_delivered= $rows['totaldelivered'];*/
			$total_delivered= 0;
			$createdon		= $rows['createdon'];
			$status			= $rows['status'];
			$completeddate	= $rows['completedon'];
			$scheduleddate	= $rows['scheduleddate'];
			$scheduleddate	= date("j F, Y h:i A",$scheduleddate);
			$campaindate	= date("j F, Y",$createdon);
			$listtype		= (int)$rows['listtype'];
			$fromposition	= (int)$rows['fromposition'];
			$toposition		= (int)$rows['toposition'];
			$listid			= (int)$rows['listid'];
			$campaignname	= $rows['campaignname'];
			$istrackable	= (int)$rows['istrackable'];
			$language		= (int)$rows['language'];
			$pageid			= (int)$rows['pageid'];

			$trackablestatus = 'No';
			if($istrackable > 0)
			{
				$trackablestatus = 'Yes';
			}
			else
			{
				$pageid = 0;
				$pagename = '';
			}

			$completionstatus = 'Pending';
			$compeleteddatetext = '';
			if($status == 1)
			{
				$completionstatus = 'In Process';
			}
			else if($status == 2)
			{
				$completionstatus = 'Completed';
				if($completeddate > 0)
				{
					$compeleteddatetext = date("j F, Y h:i A",$completeddate);
				}
				else
				{
					$compeleteddatetext = "---";
				}
			}
			else if($status == 3)
			{
				$completionstatus = 'Paused';
			}
			else if($status == 4)
			{
				$completionstatus = 'Incomplete';
			}
			if($listtype == 1)
			{
				$filterlistarr 	= @explode("::",$filterList);

				@array_filter($filterlistarr);
				@array_unique($filterlistarr);
				
				$filterliststr = '';
				if(!empty($filterlistarr))
				{
					foreach($filterlistarr as $key => $value)
					{
						if(trim($AllListsArr[$value]['name']) != '')
						{
							$filterliststr .= $AllListsArr[$value]['name'].", ";
						}
					}
				}

				if(trim($filterliststr) !="")
				{
					$filterliststr .= "@@";
					$filterliststr	= str_replace(", @@","",$filterliststr);
					$filterliststr	= str_replace("@@","",$filterliststr);
				}
			}
			else
			{
				$filterliststr	= "";

				if($fromposition > 0 && $toposition > 0)
				{
					$ispartial	= true;
				}

				if($listid < 1)
				{
					$filterliststr	= "Partial List (".$fromposition." - ".$toposition.")";
				}
				else
				{
					$filterliststr = $AllListsArr[$listid]['name'];
				}
			}

			if(trim($filterliststr) == "")
			{
				$filterliststr	= "---";
			}
			
			

			if($total_delivered < 1)
			{
				$total_delivered	= "Unknown";
			}
			$languagetxt = 'English';
			if($language == 2)
			{
				$languagetxt = 'Multilingual';
			}
			$RecordListArr[$index]['id']				= (int)$id;
			$RecordListArr[$index]['campaignlisttext']	= $filterliststr;
			$RecordListArr[$index]['messagetext']		= $message;
			$RecordListArr[$index]['campaigndate']		= $campaindate;
			$RecordListArr[$index]['scheduleddate']		= $scheduleddate;
			$RecordListArr[$index]['completeddate']		= $compeleteddatetext;
			$RecordListArr[$index]['totalmessages']		= $totalrecords;
			$RecordListArr[$index]['isdataprocessed']	= $isdataprocessed;
			$RecordListArr[$index]['totalsent']			= $totalsent;
			$RecordListArr[$index]['completed']			= $completionstatus;
			$RecordListArr[$index]['status']			= $status;
			$RecordListArr[$index]['campaignname']		= $campaignname;
			$RecordListArr[$index]['trackablestatus']	= $trackablestatus;
			$RecordListArr[$index]['pageid']			= $pageid;
			$RecordListArr[$index]['pagename']			= $pagename;
			$RecordListArr[$index]['ispartial']			= $ispartial;
			$RecordListArr[$index]['languagetxt']		= $languagetxt;
			$RecordListArr[$index]['fromposition']		= (int)$fromposition;
			$RecordListArr[$index]['toposition']		= (int)$toposition;
			/*$RecordListArr[$index]['totaldelivered']	= $total_delivered;*/

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "campaign listed successfully.";
	}

	$pageListArr	= array();
	$pagelistindex	= 0;

	for($pageloop = 1; $pageloop <= $totalpages; $pageloop++)
	{
		$pageListArr[$pagelistindex]['page']	= $pageloop;
		$pageListArr[$pagelistindex]['name']	= "Page ".$pageloop;

		$pagelistindex++;
	}

	$response['recordlist']		= $RecordListArr;
	$response['perpage']		= (int)$perpage;
	$response['totalpages']		= (int)$totalpages;
	$response['paginglist']		= $pageListArr;
	$response['showpages']		= false;
	$response['totalrecord']	= $TotalRec;

	if($totalpages > 1)
	{
		$response['showpages']	= true;
	}
	
    $json = json_encode($response);
    echo $json;
	die;
}

if($_POST['Mode'] == 'GetEditCampaignDetail')
{
	$response['success']	= false;
	$response['dialogmsg']  = '';
    $response['msg']		= "Unable to fetch campaign detail.";

	$sql	= "SELECT * FROM ".$Prefix."campaign WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$esql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST["clientid"],"deletedon"=>1);

	$query	= pdo_query($sql,$esql);

	if(is_array($query))
	{
		$response['msg']	= $query['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$num	= pdo_num_rows($query);

	$detailArr	= array();

	if($num > 0)
	{
		$row	= pdo_fetch_assoc($query);

		$totalsent = $row['totalsent'];

		if($totalsent > 0 AND $_POST['actiontype'] != 'resend')
		{
			$response['dialogmsg']	= "Campaign cannot be edited, as it is already in queue to send sms";
			$response['msg']		= "Campaign cannot be edited, as it is already in queue to send sms";
			$response['toastmsg']	= "Campaign cannot be edited, as it is already in queue to send sms";

			$json = json_encode($response);
			echo $json;
			die;
		}

		$listidArr	= @explode("::",$row['filterlist']);
		$listidArr	= @array_unique($listidArr);
		$listidArr	= @array_filter($listidArr);
		$listidArr	= @array_values($listidArr);

		$listidstr	= @implode(",",$defaultlist);

		$message			= $row['message'];
		$scheduleddateunix	= $row['scheduleddate'];
		$listtype			= $row['listtype'];
		$fromposition		= $row['fromposition'];
		$toposition			= $row['toposition'];
		$listid				= $row['listid'];
		$istrackable		= $row['istrackable'];
		$pageid				= $row['pageid'];
		$groupid			= $row['groupid'];
		$campaignname		= $row['campaignname'];
		$senderrecordid		= $row['senderrecordid'];
		$senderid			= $row['senderid'];
		$sendertype			= $row['sendertype'];
		$language			= $row['language'];
		$scheduleddate		= date("Y-m-d",$scheduleddateunix);
		$scheduledtime		= date("H:i",$scheduleddateunix);
		$datatype			= $row['datatype'];
		$pagename			= $AllPageArr[$pageid]['name'];
		$groupname			= $AllGroupArr[$groupid]['name'];

		if(trim($datatype) == "")
		{
			$datatype	= "list";
		}

		if(trim($senderrecordid) == "" || trim($senderid) == "")
		{
			$senderrecordid	= "-1";
			$senderid		= $DefaultSenderID;
		}
		
		$detailArr["listids"]		= $listidArr;
		$detailArr["message"]		= $message;
		$detailArr["scheduleddate"]	= $scheduleddate;
		$detailArr["scheduledtime"]	= $scheduledtime;
		$detailArr["listtype"]		= (int)$listtype;
		$detailArr["fromposition"]	= (int)$fromposition;
		$detailArr["toposition"]	= (int)$toposition;
		$detailArr["listid"]		= (int)$listid;
		$detailArr["listname"]		= $AllListArr[$listid]['name'];
		$detailArr["campaignname"]	= $campaignname;
		$detailArr["istrackable"]	= (int)$istrackable;
		$detailArr["pageid"]		= (int)$pageid;
		$detailArr["pagename"]		= $pagename;
		$detailArr["senderrecordid"]= $senderrecordid;
		$detailArr["senderid"]		= $senderid;
		$detailArr["sendertype"]	= (int)$sendertype;
		$detailArr["language"]		= (int)$language;
		$detailArr["datatype"]		= $datatype;
		$detailArr["groupid"]		= (int)$groupid;
		$detailArr["groupname"]		= $groupname;

		$response['success']	= true;
		$response['msg']		= "Campaign detail fetched successfully.";
	}

	$response['campaigndetail']	= $detailArr;

    $json = json_encode($response);
    echo $json;
	die;
}

if($_POST['Mode'] == "DeleteCampaign")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to delete campaign, Please try later.";

	$DelSql		= "UPDATE ".$Prefix."campaign SET 
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

	if($DelQuery && !is_array($DelQuery))
	{
        $DelSql2	= "UPDATE ".$Prefix."campaign_history SET 
        deletedon	=:deletedon 
        WHERE 
        campid		=:campid
        AND 
        clientid	=:clientid";

        $DelEsql2	= array(
            "deletedon"	=>time(),
            'campid'	=>(int)$_POST['recordid'],
            "clientid"	=>(int)$_POST['clientid']	
        );

		$DelQuery2	= pdo_query($DelSql2,$DelEsql2);

		$Response['success']	= true;
		$Response['msg']		= "Campaign deleted successfully.";
	}

    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "GetSmsLogHistory")
{
	$time	= time();

	$RecordListArr	= array();
	$GraphDataArr	= array();
	$DeviceCountArr	= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch contacts.";

	$condition	= " AND log.deletedon <:deletedon";
	$Esql		= array("deletedon"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND log.clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['recordid'] > 0)
	{
		$condition	.= " AND log.campid=:campid";
		$Esql['campid']	= (int)$_POST['recordid'];
	}

	if($_POST['interestedonly'] > 0)
	{
		$condition	.= " AND log.isinterested=:isinterested";
		$Esql['isinterested']	= 1;
	}

	$Sql	= "SELECT log.* FROM ".$Prefix."campaign_history log,".$Prefix."campaign camp WHERE 1 ".$condition." AND camp.id=log.campid ORDER BY FIELD(log.isinterested,1,2,0), log.isclicked DESC, log.createdon DESC";

	$Query	= pdo_query($Sql,$Esql);
	
	if(is_array($Query))
	{
		$response['msg']	= $Query['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$Num		= pdo_num_rows($Query);
	$TotalRec	= $Num;

	$totalopened		= 0;
	$totalsent			= 0;
	$totalinterested	= 0;
	$totalreferred		= 0;

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isselected	= false;

			$timeago		= "";

			$id		    	= $rows['id'];
			$campid			= $rows['campid'];
			$phonenumber	= $rows['phonenumber'];
			$createdon		= $rows['createdon'];
			$isinterested	= $rows['isinterested'];
			$isclicked		= $rows['isclicked'];
			$issent			= $rows['issent'];
			$mobile_device	= $rows['mobile_device'];
			$isreferred		= $rows['isreferred'];
			$lastopenedtime	= $rows['lastopenedtime'];
			$name			= $rows['name'];
			$leadcredit		= $rows['leadcredit'];

			if(trim($campaignname) == "")
			{
				$campaignname	= "Campaign #".$campid;
			}

			$sentdate	= date("d/m/Y",$createdon);

			if(date("Y",$createdon) == date("Y",$time))
			{
				$sentdate	= date("d/m",$createdon);
			}

			$senttime	= date("h:i a",$createdon);

			if($leadcredit < 1)
			{
				$phonenumber	= makeSecurePhoneNumber($phonenumber);
			}

			if($lastopenedtime > 0)
			{
				$timeago	= PendingTime($lastopenedtime,time());
			}

			if(trim($mobile_device) == "")
			{
				$mobile_device	= "Unknown";
			}

			if($isclicked < 1)
			{
				$Status	= "Not Opened";
			}
			else
			{
				/*$Status	= "Opened (".$isclicked.") time(s)";*/
				$Status		= "".$isclicked."";
				$totalopened++;
			}

			if($issent > 0)
			{
				$totalsent++;
			}
			else
			{
				$Status	= "Not Sent";
			}
			$interestedtext = '-';
			/*if($isinterested == 1)
			{
				$totalinterested++;
				$interestedtext = 'Yes';
			}
			else if($isinterested == 2)
			{
				$interestedtext = 'No';
			}

			if($isreferred > 0)
			{
				$totalreferred	+= 1;
			}
			*/
			/*if($isclicked > 0)
			{*/
				$RecordListArr[$index]['index']				= (int)$index+1;
				$RecordListArr[$index]['id']				= (int)$id;
				$RecordListArr[$index]['campaignname']		= $campaignname;
				$RecordListArr[$index]['phonenumber']		= $phonenumber;
				$RecordListArr[$index]['status']			= (int)$isclicked;
				$RecordListArr[$index]['statustext']		= $Status;
				$RecordListArr[$index]['interestedtext']	= $interestedtext;
				$RecordListArr[$index]['mobile_device']		= $mobile_device;
				$RecordListArr[$index]['isreferred']		= (int)$isreferred;
				$RecordListArr[$index]['timeago']			= $timeago;
				$RecordListArr[$index]['name']				= $name;
				$RecordListArr[$index]['leadcredit']		= (int)$leadcredit;
				$RecordListArr[$index]['sentdate']			= $sentdate;
				$RecordListArr[$index]['senttime']			= $senttime;

				$DeviceCountArr[$mobile_device]	+= 1;
			//}
			$index++;
		}

		$LabelArr			= array();
		$DataArr			= array();
		$BackgroundColorArr	= array();

		if(!empty($DeviceCountArr))
		{
			$deviceloop	= 0;
			foreach($DeviceCountArr as $device => $totalcount)
			{
				$LabelArr[]	= $device;
				$DataArr[]	= $totalcount;

				$bgcolor	= $GraphColorArr[$deviceloop]['name'];

				if(trim($bgcolor) == "")
				{
					$bgcolor	= '#2A8000';
				}

				$BackgroundColorArr[]	= hex2rgba($bgcolor,'0.5');

				$deviceloop++;
			}
		}

		$response['totalavailable']	= (int)$Num;
		$response['totalopened']	= $totalopened;
		$response['totalsent']		= $totalsent;
		$response['totalinterested']= (int)$totalinterested;
		$response['totalreferred']	= (int)$totalreferred;
		$response['graphlabels']	= $LabelArr;
		$response['graphdata']		= $DataArr;
		$response['bgcolordata']	= $BackgroundColorArr;
        $response['msg']			= "SMS campaign Log listed successfully.";
	}

	$response['recordlist']		= $RecordListArr;

	if(!empty($RecordListArr))
	{
		$response['success']		= true;
	}
	
    $json = json_encode($response);
    echo $json;
	die;
}

/*if($_POST['Mode'] == "DownloadLog")
{
	$ReportDir	= "../assets/".$_POST['clientid']."/report/";

	@mkdir($ReportDir, 0777, true);

    $response['success']	= false;
    $response['msg']		= "Unable to fetch contacts.";

	$hasfilter	= 0;

	$condition	= " AND log.deletedon <:deletedon";
	$Esql		= array("deletedon"=>1);

	if($_POST['clientid'] > 0)
	{
		$condition	.= " AND log.clientid=:clientid";
		$Esql['clientid']	= (int)$_POST['clientid'];
	}

	if($_POST['recordid'] > 0)
	{
		$condition	.= " AND log.campid=:campid";
		$Esql['campid']	= (int)$_POST['recordid'];
	}

	if($_POST['interestedonly'] > 0)
	{
		$condition	.= " AND isinterested=:isinterested";
		$Esql['isinterested']	= 1;
	}

	if(trim($_POST['campids']) != "")
	{
		$hasfilter	= 1;

		$campidstr	= $_POST['campids'];
		if(trim($campidstr) == "")
		{
			$campidstr	= "-1";
		}
		$condition		.= " AND log.campid IN(".$campidstr.")";
	}

	if(trim($_POST['statusids']) != "")
	{
		$hasfilter	= 1;
		$statusidstr	= $_POST['statusids'];
		if(trim($statusidstr) == "")
		{
			$statusidstr	= "-1";
		}
		$condition		.= " AND log.statusid IN(".$statusidstr.")";
	}

	if($_POST['phonenumber'] > 0)
	{
		$hasfilter	= 1;
		$condition	.= " AND log.phonenumber=:phonenumber";
		$Esql['phonenumber']	= (int)$_POST['phonenumber'];
	}

	if($_POST['isinterested'] != -1 AND $_POST['isinterested'] !='')
	{
		$condition		.= " AND log.isinterested=:isinterested";
		$Esql['isinterested']	= $_POST['isinterested'];
	}

	if($_POST['isreferred'] > 0)
	{
		$hasfilter	= 1;
		$condition		.= " AND log.isreferred=:isreferred";
		$Esql['isreferred']	= 1;
	}

	if($_POST['leadtype'] != "")
	{
		if($_POST['leadtype'] == 1)
		{
			$condition		.= " AND log.leadcredit < :leadcredit AND log.isinterested=:isinterested";
			$Esql['leadcredit']		= 1;
			$Esql['isinterested']	= 1;
		}

		if($_POST['leadtype'] == 2 && $hasfilter < 1)
		{
			$condition	= " AND log.clientid=:clientid AND log.isarchive=:isarchive ";
			$Esql		= array("clientid"=>(int)$_POST['clientid'],"isarchive"=>0);
		}

		if($_POST['leadtype'] == 3 && $hasfilter < 1)
		{
			$condition	= " AND log.clientid=:clientid AND log.isarchive=:isarchive AND log.isinterested=:isinterested";
			$Esql		= array("clientid"=>(int)$_POST['clientid'],"isarchive"=>0,"isinterested"=>1);
		}

		if($_POST['leadtype'] == 4 && $hasfilter < 1)
		{
			$condition	= " AND log.clientid=:clientid AND log.isarchive=:isarchive AND log.isinterested=:isinterested AND log.statusid NOT IN(4,5)";
			$Esql		= array("clientid"=>(int)$_POST['clientid'],"isarchive"=>0,"isinterested"=>1);
		}
	}

	if($_POST['Type'] == 'LeadArea')
	{
		$Sql	= "SELECT log.*,camp.campaignname FROM ".$Prefix."campaign_history log,".$Prefix."campaign camp WHERE 1 ".$condition." AND log.issent=:issent AND log.isclicked >:isclicked AND log.campid=camp.id ORDER BY FIELD(log.isinterested,1,2,0), log.createdon ASC";

		$Esql["issent"]		= 1;
		$Esql["isclicked"]	= 0;
	}
	else
	{
		$Sql	= "SELECT log.*,camp.campaignname FROM ".$Prefix."campaign_history log,".$Prefix."campaign camp WHERE 1 ".$condition." AND log.campid=camp.id ORDER BY FIELD(log.isinterested,1,2,0) DESC,log.createdon ASC";
	}
	$Query		= pdo_query($Sql,$Esql);

	if(is_array($Query))
	{
		$response['msg']	= $Query['errormessage'];

		$json = json_encode($response);
		echo $json;
		die;
	}

	$Num		= pdo_num_rows($Query);

	$DataDownload	= "";

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$DataRow = false;

			$isselected	= false;

			$selectdate			= "";
			$time				= "";

			$id		    		= $rows['id'];
			$phonenumber		= $rows['phonenumber'];
			$campid				= $rows['campid'];
			$campaignname		= $rows['campaignname'];
			$createdon			= $rows['createdon'];
			$isclicked			= $rows['isclicked'];
			$mobile_device		= $rows['mobile_device'];
			$isinterested		= $rows['isinterested'];
			$leadcredit			= $rows['leadcredit'];

			$status				= $rows['statusname'];
			$name				= $rows['name'];
			$state				= $rows['state'];
			$city				= $rows['city'];
			$pincode			= $rows['pincode'];
			$callbackdateunix	= $rows['callbackdatetime'];
			$category			= $rows['catname'];
			$remark				= $rows['remark'];
			$statusid			= (int)$rows['statusid'];

			if(strtolower($category) == "select")
			{
				$category	= "";
			}

			if($statusid == 7 && $callbackdateunix != "")
			{
				$selectdate			= "";
				$time				= "";

				$selectdate	= date("Y-m-d",$callbackdateunix);
				$time		= date("H:i",$callbackdateunix);
			}

			if($pincode < 1)
			{
				$pincode	= "";
			}

			if($pincode != "")
			{
				$PinSql	= "SELECT * FROM ".$Prefix."pincodes WHERE pincode=:pincode";
				$PinEsql	= array("pincode"=>$pincode);

				$PinQuery	= pdo_query($PinSql,$PinEsql);
				$PinNum		= pdo_num_rows($PinQuery);

				if($PinNum > 0)
				{
					$pinrows	= pdo_fetch_assoc($PinQuery);

					$state	= $pinrows['state'];
					$city	= $pinrows['city'];
				}
			}

			$tagidArr	= @explode("::",$rows['tags']);
			$tagidArr	= @array_unique($tagidArr);
			$tagidArr	= @array_filter($tagidArr);
			$tagidArr	= @array_values($tagidArr);

			$tagidStr	= @implode(",",$tagidArr);

			if(trim($tagidStr) == "")
			{
				$tagidStr	= "-1";
			}

			$TagNameArr	= array();

			$TagSql		= "SELECT * FROM ".$Prefix."lead_tag WHERE clientid=:clientid AND deletedon < :deletedon AND id IN(".$tagidStr.") ORDER BY name ASC";
			$TagEsql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

			$TagQuery	= pdo_query($TagSql,$TagEsql);
			$TagNum		= pdo_num_rows($TagQuery);

			if($TagNum > 0)
			{
				while($tagrows = pdo_fetch_assoc($TagQuery))
				{
					$tagname	= $tagrows['name'];

					$TagNameArr[]	= $tagname;
				}
			}

			$TagsName	= @implode(", ",$TagNameArr);

			if($leadcredit < 1)
			{
				$phonenumber	= makeSecurePhoneNumber($phonenumber);
			}

			$interestedtext = 'Not Answered';
			if($isinterested == '1')
			{
				$interestedtext = 'Yes';
			}
			else if($isinterested == '2')
			{
				$interestedtext = 'No';
			}

			if(trim($mobile_device) == "")
			{
				$mobile_device	= "Unknown";
			}

			if(trim($campaignname) == "")
			{
				$campaignname	= "Campaign #".$campid;
			}
			
			if($isclicked > 0)
			{
				//$Status	= "Opened (".$isclicked.") time(s)";
				$Opened	= "".$isclicked."";
				$totalopened++;

				$DataRow .= "\"".(int)($index+1)."\",\"".addslashes($campaignname)."\",\"".addslashes($category)."\",\"".addslashes($TagsName)."\",\"".addslashes($city)."\",\"".addslashes($state)."\",\"".addslashes($pincode)."\",\"".addslashes($remark)."\",\"".addslashes($status)."\",\"".$selectdate."\",\"".$time."\",\"".addslashes($name)."\",\"".$phonenumber."\",\"".addslashes($mobile_device)."\",\"".addslashes($interestedtext)."\",\"".$Opened."\"\r\n";
				$DataDownload .= $DataRow;

				$index++;
			}
		}
	}
	if($_POST['Type'] == 'LeadArea')
	{
		$Headers .="S.No,Campaign,Category,Tag,City,State,Pin Code,Remark,Status,Select Date,Time,Name,Phone Number,Phone Model,Is Intrested?,Opened Count\r\n";
	}
	else
	{
		$Headers .="S.No,Campaign,Phone,Phone Model,Is Intrested?,Opened Count\r\n";
	}

	$NewContent = $Headers.$DataDownload;

	$filename		= "report";
	$csv_filename	= $filename."_".date("Y-m-d_H-i",time()).".csv";

	$fd	= fopen($ReportDir.$csv_filename, "w");
	$iscreated = fputs($fd, $NewContent);
	fclose($fd);

	if(file_exists($ReportDir.$csv_filename))
	{
		$response['success']		= true;
		$response['reportfilepath']	= $ServerAPIURL."download.php?file=".base64_encode($ReportDir.$csv_filename);
		$response['msg']			= "Log report excel generated successfully.";
	}

    $json = json_encode($response);
    echo $json;
	die;
}*/

if($_POST['Mode'] == "PauseCampaign")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to pause campaign, Please try later.";

	$CheckSql	= "SELECT * FROM ".$Prefix."campaign WHERE id=:id AND clientid=:clientid AND status=:status";
	$CheckEsql	= array("id"=>(int)$_POST['recordid'],"clientid"=>(int)$_POST['clientid'],"status"=>2);
	
	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$Response['success']	= false;
		$Response['msg']		= "The campaign has been completed hence cannot pause.";

		$json = json_encode($Response);
		echo $json;
		die;
	}

	$Sql		= "UPDATE ".$Prefix."campaign SET 
	status	=:status
	WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$Esql	= array(
		"status"	=>3,
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']
	);

	$Query	= pdo_query($Sql,$Esql);

	if($Query && !is_array($Query))
	{
		$Response['success']	= true;
		$Response['msg']		= "Campaign paused successfully.";
	}
	else
	{
		$Response['msg']	= $Query['errormessage'];
	}

    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "ResumeCampaign")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to pause Resume, Please try later.";

	$Sql		= "UPDATE ".$Prefix."campaign SET 
	status	=:status,
	cronids	=:cronids
	WHERE 
	id			=:id
	AND 
	clientid	=:clientid";

	$Esql	= array(
		"status"	=>0,
		"cronids"	=>'',
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']	
	);

	$Query	= pdo_query($Sql,$Esql);

	if($Query && !is_array($Query))
	{
		$sql = "UPDATE ".$Prefix."campaign_history SET cronid=:cronid WHERE issent < :issent AND campid=:campid";
		$esql = array("cronid"=>0,"issent"=>1,"campid"=>(int)$_POST['recordid']);
	
		pdo_query($sql,$esql);

		$Response['success']	= true;
		$Response['msg']		= "Campaign resume successfully.";
	}
	else
	{
		$Response['msg']	= $Query['errormessage'];
	}

    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "GetCampaignStatus")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to fetch status.";

	$StatusArr[0]['id']		= "0";
	$StatusArr[0]['name']	= "Pending";

	$StatusArr[1]['id']		= "1";
	$StatusArr[1]['name']	= "In Process";

	$StatusArr[2]['id']		= "2";
	$StatusArr[2]['name']	= "Completed";

	$StatusArr[3]['id']		= "3";
	$StatusArr[3]['name']	= "Paused";

	$StatusArr[4]['id']		= "4";
	$StatusArr[4]['name']	= "Incomplete";

	if(!empty($StatusArr))
	{
		$Response['success']	= true;
		$Response['msg']		= "Status fetched successfully.";
		$Response['recordlist']	= $StatusArr;
	}

    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "GetCampaignFilter")
{
	$response['success']	= false;
	$response['msg']		= "Unable to fetch campaign filter.";

	$RecordListArr	= array();

	$Cond	= " AND camp.clientid=:clientid AND log.issent=:issent AND log.isarchive=:isarchive AND log.isclicked >:isclicked";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"issent"=>1,"isarchive"=>0,"isclicked"=>0);

	if($_POST['staffid'] > 0)
	{
		$Cond	.= " AND camp.staffid=:staffid";
		$Esql['staffid']	= (int)$_POST['staffid'];
	}

	$Sql	= "SELECT camp.* FROM ".$Prefix."campaign camp, ".$Prefix."campaign_history log WHERE 1 AND camp.id=log.campid ".$Cond." GROUP BY camp.id";

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

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];
			$campaignname	= $rows['campaignname'];

			if(trim($campaignname) == "")
			{
				$campaignname	= "Campaign #".$id;
			}
			else
			{
				$campaignname	= $campaignname." #".$id;
			}

			$RecordListArr[$index]['id']	= $id;
			$RecordListArr[$index]['name']	= $campaignname;

			$index++;
		}

		$response['success']	= true;
		$response['msg']		= "Campaign Record listed successfully.";
	}

	$response['recordlist']	= $RecordListArr;

    $json = json_encode($response);
    echo $json;
	die;
}

if($_POST['Mode'] == 'GetCampaignSummary')
{
	$RecordSetArr		= array();

    $response['success']	= false;
    $response['msg']		= "Unable to fetch campaign summary.";

	$Pendingcampaign	= 0;
	$InProcesscampaign	= 0;
	$Completedcampaign	= 0;
	$Pausedcampaign		= 0;
	$Incompletecampaign	= 0;
	$Archivecampaign	= 0;

	$Cond	= " AND clientid=:clientid AND deletedon < :deletedon AND status<>:status AND isarchive=:isarchive";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>1,"isarchive"=>0);

	if($_POST['staffid'] > 0)
	{
		$Cond	.= " AND staffid=:staffid ";
		$Esql['staffid']	= (int)$_POST['staffid'];
	}

	$Sql	= "SELECT COUNT(*) AS C,status FROM ".$Prefix."campaign WHERE 1 ".$Cond." ORDER BY FIELD(status,0,3,2)";

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$status		= $rows['status'];
			$count		= $rows['C'];

			if($status == 0)
			{
				$Pendingcampaign	= $count;
			}
			else if($status == 2)
			{
				$Completedcampaign	= $count;
			}
			else if($status == 3)
			{
				$Pausedcampaign	 	= $count;
			}
			else if($status == 4)
			{
				$Incompletecampaign	= $count;
			}
		}
	}

	$Cond	= " AND clientid=:clientid AND deletedon < :deletedon AND status=:status AND isarchive=:isarchive";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>2,"isarchive"=>1);

	if($_POST['staffid'] > 0)
	{
		$Cond	.= " AND staffid=:staffid";
		$Esql['staffid']	= (int)$_POST['staffid'];
	}

	$Sql	= "SELECT COUNT(*) as archivecampaign FROM ".$Prefix."campaign WHERE 1 ".$Cond."";

	$Query	= pdo_query($Sql,$Esql);
	$rows	= pdo_fetch_assoc($Query);

	$Archivecampaign	= $rows['archivecampaign'];

	
	$Cond	= " AND clientid=:clientid AND deletedon < :deletedon AND status=:status AND isinsufficientcredit=:isinsufficientcredit";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>3,"isinsufficientcredit"=>1);

	if($_POST['staffid'] > 0)
	{
		$Cond	.= " AND staffid=:staffid";
		$Esql['staffid']	= (int)$_POST['staffid'];
	}

	$Sql	= "SELECT COUNT(*) as isinsufficientcredit FROM ".$Prefix."campaign WHERE 1 ".$Cond."";

	$Query	= pdo_query($Sql,$Esql);
	$rows	= pdo_fetch_assoc($Query);

	$isinsufficientcredit	= $rows['isinsufficientcredit'];
	$response['isinsufficientcredit']	= (int)$isinsufficientcredit;
	
	$RecordSetArr[$index]['status']			= 0;
	$RecordSetArr[$index]['name']			= "Pending";
	$RecordSetArr[$index]['totalcampaign']	= $Pendingcampaign;
	$RecordSetArr[$index]['classname']		= "pendingsummary";
	$RecordSetArr[$index]['icon_classname']	= "hourglass_tophalf_fill";
	$index++;

	$RecordSetArr[$index]['status']			= 2;
	$RecordSetArr[$index]['name']			= "Completed";
	$RecordSetArr[$index]['totalcampaign']	= $Completedcampaign;
	$RecordSetArr[$index]['classname']		= "completedsummary";
	$RecordSetArr[$index]['icon_classname']	= "arrow_2_circlepath_circle_fill";
	$index++;

	$RecordSetArr[$index]['status']			= 3;
	$RecordSetArr[$index]['name']			= "Paused";
	$RecordSetArr[$index]['totalcampaign']	= $Pausedcampaign;
	$RecordSetArr[$index]['classname']		= "pausedsummary";
	$RecordSetArr[$index]['icon_classname']	= "playpause_fill";
	$index++;

	$RecordSetArr[$index]['status']			= 4;
	$RecordSetArr[$index]['name']			= "Incomplete";
	$RecordSetArr[$index]['totalcampaign']	= $Incompletecampaign;
	$RecordSetArr[$index]['classname']		= "incompletesummary";
	$RecordSetArr[$index]['icon_classname']	= "arrow_up_arrow_down_circle_fill";
	$index++;

	$RecordSetArr[$index]['status']			= 9999;
	$RecordSetArr[$index]['name']			= "Archive";
	$RecordSetArr[$index]['totalcampaign']	= $Archivecampaign;
	$RecordSetArr[$index]['classname']		= "archivesummary";
	$RecordSetArr[$index]['icon_classname']	= "archivebox_fill";
	$index++;

	$Cond	= " AND clientid=:clientid AND deletedon < :deletedon AND (status=:status || status=:status2) AND isarchive=:isarchive";
	$Esql	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"status"=>1,'status2'=>4,"isarchive"=>0);

	if($_POST['staffid'] > 0)
	{
		$Cond	.= " AND staffid=:staffid";
		$Esql['staffid']	= (int)$_POST['staffid'];
	}

	$Sql	= "SELECT SUM(totalrecords) as totalrecords, SUM(totalsent) as totalsent,count(*) as c FROM ".$Prefix."campaign WHERE 1 ".$Cond."";

	$Query	= pdo_query($Sql,$Esql);
	$rows	= pdo_fetch_assoc($Query);
	
	$totalmessage = $rows['totalrecords'];
	$totalsentmessage = $rows['totalsent'];
	$totalinprocesscampaign = $rows['c'];


	if($totalinprocesscampaign > 0 )
	{
		$inprocessmsg	= "";

		$inprocessmsg	= $totalsentmessage." out of ".$totalmessage." msg sent";

		if($totalsentmessage > 0 || $totalmessage > 0)
		{
			$response['hasinprocesscampaign']	= true;
			$response['totalinprocesscampaign']	= $totalinprocesscampaign;
			$response['inprocessmsg']			= $inprocessmsg;
		}
		else
		{
			$response['hasinprocesscampaign']	= false;
			$response['totalinprocesscampaign']	= 0;
		}
	}
	else
	{
		$response['hasinprocesscampaign']	= true;
		$response['totalinprocesscampaign']	= 0;
	}

	$response['haslistrecords']	= true;
	$response['recordlist']		= $RecordSetArr;

	if(!empty($RecordSetArr))
	{
		$response['success']	= true;
		$response['msg']		= "Campaign listed successfully.";
	}

	$response['totalcampaign']	= (int)$Pendingcampaign+ (int)$InProcesscampaign+ (int)$Completedcampaign + (int)$Pausedcampaign + (int)$Archivecampaign;

    $json = json_encode($response);
    echo $json;
	die;
}

if($_POST['Mode'] == "ArchiveCampaign")
{
	$Response['success']	= false;
	$Response['msg']		= "Unable to pause Resume, Please try later.";
	
	$Sql		= "UPDATE ".$Prefix."campaign SET 
	isarchive	=:isarchive
	WHERE
	status		=:status
	AND
	id			=:id
	AND 
	clientid	=:clientid";

	$Esql	= array(
		"isarchive"	=>1,
		"status"	=>2,
		'id'		=>(int)$_POST['recordid'],
		"clientid"	=>(int)$_POST['clientid']	
	);

	$Query	= pdo_query($Sql,$Esql);

	if($Query && !is_array($Query))
	{
		$sql = "UPDATE ".$Prefix."campaign_history SET isarchive=:isarchive WHERE issent=:issent AND campid=:campid";
		$esql = array("isarchive"=>1,"issent"=>1,"campid"=>(int)$_POST['recordid']);
	
		pdo_query($sql,$esql);

		$Response['success']	= true;
		$Response['msg']		= "Campaign archive successfully.";
	}
	else
	{
		$Response['msg']	= $Query['errormessage'];
	}

    $json = json_encode($Response);
    echo $json;
	die;
}

if($_POST['Mode'] == "GetSMSSummary")
{
	$response['success']	= false;
    $response['msg']		= "Unable to fetch sms history";

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN(".$areaids.")";
	}

	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=payment.customerid AND payment.smsmresponse IS NOT NULL ".$Condition." GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";

	$Query		= pdo_query($SQL,$ESQL);
	$PaymentNum	= pdo_num_rows($Query);

	$Condition			= "";
	$GeneralCondition	= "";

	$ESQL			= array("clientid"=>(int)$_POST['clientid'],"status"=>2,"smstype"=>'invoicesms');
	$GeneralESQL	= array("clientid"=>(int)$_POST['clientid'],"status"=>2,"smstype"=>'generalsms');

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition			.= " AND campaign.scheduleddate BETWEEN :startdate AND :enddate";
		$GeneralCondition	.= " AND campaign.scheduleddate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;

		$GeneralESQL['startdate']	= $StartDate;
		$GeneralESQL['enddate']		= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND campaign.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];

		$GeneralCondition	.= " AND campaign.areaid=:areaid";
		$GeneralESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND campaign.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];

		$GeneralCondition	.= " AND campaign.lineid=:lineid";
		$GeneralESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND campaign.areaid IN (".$areaids.")";

		$GeneralCondition	.= " AND campaign.areaid IN (".$areaids.")";
	}

	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND campaign.lineid IN(".$lineids.")";

		$GeneralCondition	.= " AND campaign.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT history.* FROM ".$Prefix."campaign campaign, ".$Prefix."campaign_history history WHERE campaign.clientid=:clientid AND campaign.clientid=history.clientid AND campaign.status = :status AND campaign.id=history.campid AND campaign.smstype=:smstype ".$Condition." GROUP BY history.id ORDER BY campaign.id ASC";
	
	$GeneralSQL	= "SELECT history.* FROM ".$Prefix."campaign campaign, ".$Prefix."campaign_history history WHERE campaign.clientid=:clientid AND campaign.clientid=history.clientid AND campaign.status = :status AND campaign.id=history.campid AND campaign.smstype=:smstype ".$GeneralCondition." GROUP BY history.id ORDER BY campaign.id ASC";

	$GeneralQuery		= pdo_query($GeneralSQL,$GeneralESQL);
	$GeneralHistpryNum	= pdo_num_rows($GeneralQuery);

	$Query		= pdo_query($SQL,$ESQL);
	$HistpryNum	= pdo_num_rows($Query);

	/*$CampaignSQL	= "SELECT campaign.* FROM ".$Prefix."campaign campaign, ".$Prefix."campaign_history history WHERE campaign.clientid=:clientid AND campaign.status = :status AND campaign.id=history.campid ".$Condition." GROUP BY campaign.id ORDER BY campaign.id ASC";

	$CampaignQuery	= pdo_query($CampaignSQL,$ESQL);
	$CampaignNum	= pdo_num_rows($CampaignQuery);

	if($Num > 0)
	{
		$AllLineArr	= GetAllLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$lineid				= $rows['lineid'];
			$paidamount			= $rows['paidamount'];
			$paymentdate		= $rows['paymentdate'];
			$customerpaymentid	= $rows['customerpaymentid'];

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "---";
			}

			$name2	= "#".$customerid." ".$name;

			$RecordListArr[$paymentdate]['name']			= date("d-M-Y",$paymentdate);
			$RecordListArr[$paymentdate]['totalpayment']	+= $paidamount;
		}
	}

	$totalpayment	= 0;
	if(!empty($RecordListArr))
	{
		$lineindex	= 0;
		$lineDetail	= array();

		foreach($RecordListArr as $paymentdate => $linedata)
		{
			if($linedata['totalpayment'] > 0)
			{
				$lineDetail[$lineindex]['paymentdate']	= $paymentdate;
				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['name']			= $linedata['name'];
				$lineDetail[$lineindex]['totalpayment']	= @number_format($linedata['totalpayment'],2);

				$totalpayment	+= $linedata['totalpayment'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}*/

	$lineindex	= 0;

	$lineDetail[$lineindex]['serialno']		= $lineindex+1;
	$lineDetail[$lineindex]['name']			= "General SMS";
	$lineDetail[$lineindex]['count']		= $GeneralHistpryNum;
	$lineindex++;

	$lineDetail[$lineindex]['serialno']		= $lineindex+1;
	$lineDetail[$lineindex]['name']			= "Invoice SMS";
	$lineDetail[$lineindex]['count']		= $HistpryNum;
	$lineindex++;

	$lineDetail[$lineindex]['serialno']		= $lineindex+1;
	$lineDetail[$lineindex]['name']			= "Payment SMS";
	$lineDetail[$lineindex]['count']		= $PaymentNum;
	$lineindex++;

	if(!empty($lineDetail))
	{
		$response['success']	= true;
		$response['msg']		= "SMS history listed successfully.";
	}
	$response['historylist']	= $lineDetail;
	$response['totalrecord']	= 2;
	$response['totalsmssend']	= $GeneralHistpryNum + $HistpryNum + $PaymentNum;

 	$json = json_encode($response);
    echo $json;
	die;
}
?>