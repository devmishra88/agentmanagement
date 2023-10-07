<?
require_once('../assets/vendor/razorpay/Razorpay.php');

use Razorpay\Api\Api;

function add_slashes()
{
	foreach ($_POST as $key => $value)
	{
		//$_POST[$key] = @htmlentities ($_POST[$key] , ENT_QUOTES);
	}
	if (!get_magic_quotes_gpc()) 
	{
		foreach ($_POST as $key => $value)
		{
			if(is_array($value))
			{
				foreach($value as $key1 => $value1)
				{
					if(is_array($value1))
					{
						foreach($value1 as $key2 => $value2)
						{
							$_POST[$key1][$key2] =addslashes($_POST[$key1][$key2]);
						}
					}
					else
					{
						$_POST[$key][$key1] =addslashes($_POST[$key][$key1]);
					}
				}
			}
			else
			{
				$_POST[$key] = addslashes($_POST[$key]);
			}
		}
	}
}
add_slashes();
function remove_slashes()
{
	if ($_POST)
	{
		foreach ($_POST as $key => $value)
		{
			if(is_array($value))
			{
				foreach ($value as $key1 => $value1)
				{
					if(is_array($value1))
					{
						foreach($value1 as $key2 => $value2)
						{
							$_POST[$key1][$key2] =stripslashes($_POST[$key1][$key2]);
						}
					}
					else
					{
						$_POST[$key][$key1] =stripslashes($_POST[$key][$key1]);
					}
				}
			}
			else
			{
				$_POST[$key] = stripslashes($_POST[$key]);
			}
		}
	}
}
function DetectChromBrowser($UserAgent) 
{ 
	if (stripos( $UserAgent, 'Chrome') !== false) 
	{ 
		$IsChrome = 1; 
	} 
	if (stripos( $UserAgent, 'Safari') !== false) 
	{ 
		$IsChrome = 0; 
		if (stripos( $UserAgent, 'Chrome') !== false) 
		{ 
			$IsChrome = 1; 
		}
	} 
	if (stripos( $UserAgent, 'Edge') !== false) 
	{ 
		$IsChrome = 0; 
	}
	return $IsChrome; 
}
function GetBatchNumber($EventID,$isvip)
{
	global $Prefix;

	if($isvip > 0)
	{
		$batchnumber	= 999999;
	}
	else
	{
		$sql	= "SELECT * FROM ".$Prefix."admin";
		$esql	= array();

		$query	= pdo_query($sql,$esql);
		$num	= pdo_num_rows($query);

		if($num > 0)
		{
			$row = pdo_fetch_assoc($query);
			$BatchLimit = $row['order_batch_limit'];
		}

		$sql = "SELECT batchid,isaccepted FROM ".$Prefix."orders WHERE event_code=:event_code AND batchid<>:batchid ORDER BY id DESC LIMIT 1";
		$esql = array("event_code"=>(int)$EventID,"batchid"=>999999);

		$query	= pdo_query($sql,$esql);
		$rowsql	= pdo_fetch_assoc($query);

		$lastbatchid		= $rowsql['batchid'];
		$lastbatchstatus	= $rowsql['isaccepted'];

		if($lastbatchid < 1)
		{
			$batchnumber = 1;
		}
		else if($lastbatchid > 0 && $lastbatchstatus > 0)
		{
			$batchnumber = $lastbatchid + 1;
		}
		else
		{
			$sql	= "SELECT count(*) as C FROM ".$Prefix."orders WHERE event_code=:event_code AND batchid=:batchid";
			$esql	= array("event_code"=>(int)$EventID,"batchid"=>(int)$lastbatchid);

			$query	= pdo_query($sql,$esql);
			$rowsql	= pdo_fetch_assoc($query);

			$totalcount	= $rowsql['C'];
			
			if($totalcount >= $BatchLimit)
			{
				$batchnumber = $lastbatchid + 1;
			}
			else
			{
				$batchnumber = $lastbatchid;
			}
		}
	}
	return $batchnumber;
}
function GetBatchNumber_new($eventid,$isvip)
{
	global $Prefix;

	$Time	= time();

	$sql	= "SELECT * FROM ".$Prefix."admin";
	$esql	= array();

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	if($num > 0)
	{
		$row		= pdo_fetch_assoc($query);
		$BatchLimit	= $row['order_batch_limit'];
	}

	$sql	= "SELECT * FROM ".$Prefix."event_batch WHERE event_code=:event_code AND isvip=:isvip ORDER BY id DESC LIMIT 1";
	$esql	= array("event_code"=>(int)$eventid,"isvip"=>(int)$isvip);

	$query	= pdo_query($sql,$esql);
	$num	= pdo_num_rows($query);

	$addnewbatch	= false;

	if($num > 0)
	{
		$rows	= pdo_fetch_assoc($query);

		$lastbatchid		= $rows['batchid'];
		$lastbatchstatus	= $rows['isaccepted'];
		$totalbatchordered	= $rows['totalbatchordered'];
	}
	else
	{
		$batchno	= $rows['batchno'] + 1;

		$AddSql	= "INSERT INTO ".$Prefix."event_batch 
		event_code			=:event_code,
		batchno				=:batchno,
		isvip				=:isvip,
		batchorderlimit		=:batchorderlimit,
		totalbatchordered	=:totalbatchordered,
		isdeleted			=:isdeleted,
		isaccepted			=:isaccepted,
		createdon			=:createdon";

		$AddEsql	= array(
			"event_code"		=>(int)$eventid,
			"batchno"			=>(int)$batchno,
			"isvip"				=>(int)$isvip,
			"batchorderlimit"	=>(int)$BatchLimit,
			"totalbatchordered"	=>1,
			"isdeleted"			=>0,
			"isaccepted"		=>0,
			"createdon"			=>$Time
		);
	}

	$lastbatchid		= $rows['batchid'];
	$lastbatchstatus	= $rows['isaccepted'];
}
function GetAllOrderBatch($eventid)
{
	global $Prefix;

	$Arr	= array();

	$Sql	= "SELECT DISTINCT(batchid) FROM ".$Prefix."orders WHERE event_code=:event_code ORDER BY batchid ASC, createdon ASC";
	$Esql	= array("event_code"=>(int)$eventid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$isvipbatch = 0;
	$VipArr = array();
	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$batchid	= $rows['batchid'];

			$Sql2	= "SELECT isaccepted,kotid FROM ".$Prefix."orders WHERE batchid=:batchid AND event_code=:event_code ORDER BY createdon ASC LIMIT 1";
			$Esql2	= array("batchid"=>(int)$batchid,"event_code"=>(int)$eventid);

			$Query2	= pdo_query($Sql2,$Esql2);
			$rows2	= pdo_fetch_assoc($Query2);

			$isaccepted	= $rows2['isaccepted'];
			$kotid		= $rows2['kotid'];

			if($batchid == 999999)
			{
				$isvipbatch = 1;
				$VipArr[$batchid]['batchid']		= $batchid;
				$VipArr[$batchid]['name']			= "VIP";
				$VipArr[$batchid]['latestkot']		= $kotid;
				$VipArr[$batchid]['lateststatus']	= $isaccepted;
				$VipArr[$batchid]['totalorders']	= 0;
			}
			else
			{
				$Arr[$batchid]['batchid']		=	$batchid;
				$Arr[$batchid]['name']			= "B".$batchid;
				$Arr[$batchid]['latestkot']		= $kotid;
				$Arr[$batchid]['lateststatus']	= $isaccepted;
				$Arr[$batchid]['totalorders']	= 0;
			}
			
		}
		if($isvipbatch > 0)
		{
			$Arr = @array_merge($VipArr,$Arr);
		}
		
	}
	return $Arr;
}
function GetPendingOrderBatch($eventid)
{
	global $Prefix;

	$Arr	= array();

	$Sql	= "SELECT DISTINCT(batchid) FROM ".$Prefix."orders WHERE event_code=:event_code AND isdelivered<>:isdelivered AND iscanceled=:iscanceled ORDER BY batchid ASC, createdon ASC";
	$Esql	= array("event_code"=>(int)$eventid,"isdelivered"=>1,"iscanceled"=>0);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$isvipbatch = 0;
	$VipArr = array();
	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$batchid	= $rows['batchid'];

			$Sql2	= "SELECT isaccepted,kotid FROM ".$Prefix."orders WHERE batchid=:batchid AND event_code=:event_code ORDER BY createdon ASC LIMIT 1";
			$Esql2	= array("batchid"=>(int)$batchid,"event_code"=>(int)$eventid);

			$Query2	= pdo_query($Sql2,$Esql2);
			$rows2	= pdo_fetch_assoc($Query2);

			$isaccepted	= $rows2['isaccepted'];
			$kotid		= $rows2['kotid'];

			if($batchid == 999999)
			{
				$isvipbatch = 1;
				$VipArr[$batchid]['batchid']		= $batchid;
				$VipArr[$batchid]['name']			= "VIP";
				$VipArr[$batchid]['latestkot']		= $kotid;
				$VipArr[$batchid]['lateststatus']	= $isaccepted;
				$VipArr[$batchid]['totalorders']	= 0;
			}
			else
			{
				$Arr[$batchid]['batchid']		=	$batchid;
				$Arr[$batchid]['name']			= "B".$batchid;
				$Arr[$batchid]['latestkot']		= $kotid;
				$Arr[$batchid]['lateststatus']	= $isaccepted;
				$Arr[$batchid]['totalorders']	= 0;
			}
			
		}
		if($isvipbatch > 0)
		{
			$Arr = @array_merge($VipArr,$Arr);
		}
		
	}
	return $Arr;
}
function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
        'day' => (int)$days,
        'hr' => (int)$hours,
        'min' => (int)$minutes,
        'sec' => (int)$seconds,
    ];

    foreach ($sections as $name => $value){
        if ($value > 0){
            $timeParts[] = $value. ' '.$name.($value == 1 ? '' : 's');
        }
    }

    return implode(', ', $timeParts);
}
function GetEventRunners($EventID)
{
	global	$Prefix;
	$Sql	= "SELECT * FROM ".$Prefix."event_runners WHERE event_code=:event_code";
	$Esql	= array("event_code"=>(int)$EventID);
	$query	= pdo_query($Sql,$Esql);

	$Arr	= array();

	while($Row	= pdo_fetch_assoc($query))
	{
		$ID				= $Row['id'];
		$runnerid		= $Row['runnerid'];
		$Arr[$runnerid]	= $Row;

		$runnername		= $Row['name'];

		if(trim($runnername) != "")
		{
			$runnernameappe	= $runnerid;
			$runnername		= "Runner#".$runnerid." (".$runnername.")";
		}
		else
		{
			$runnernameappe	= $runnerid;
			$runnername		= "Runner#".$runnerid;
		}

		$Arr[$runnerid]['displayname']		= $runnername;
		$Arr[$runnerid]['displaynameappe']	= $runnernameappe;
		$Arr[$runnerid]['name']				= $Row['name'];
	}
	return $Arr;
}
function GetEventItems($EventID)
{
	global	$Prefix;
	$Sql	= "SELECT * FROM ".$Prefix."items WHERE event_code=:event_code";
	$Esql	= array("event_code"=>(int)$EventID);
	$query	= pdo_query($Sql,$Esql);

	$Arr	= array();

	while($Row	= pdo_fetch_assoc($query))
	{
		$ID			=	$Row['id'];	
		$Arr[$ID]	=	$Row;
	}
	return $Arr;
}
function CreateRunnerActivityLog($eventid, $kotid, $activitytype, $runnerid, $time, $newstatus)
{
	global $Prefix;

	if($newstatus == 1)
	{
		$LogSql	= "INSERT INTO ".$Prefix."event_runners_activity_log SET 
		event_code		=:event_code,
		kotid			=:kotid,
		activitytype	=:activitytype,
		runnerid		=:runnerid,
		starttime		=:starttime,
		endtime			=:endtime,
		createdon		=:createdon";

		$LogEsql	= array(
			"event_code"	=>(int)$eventid,
			"kotid"			=>(int)$kotid,
			"activitytype"	=>$activitytype,
			"runnerid"		=>(int)$runnerid,
			"starttime"		=>(int)$time,
			"endtime"		=>0,
			"createdon"		=>$time
		);

		$LogQuery	= pdo_query($LogSql,$LogEsql);
	}
	else
	{
		$LogSql	= "UPDATE ".$Prefix."event_runners_activity_log SET 
		endtime			=:endtime
		WHERE
		event_code		=:event_code
		AND
		activitytype	=:activitytype
		AND
		runnerid		=:runnerid";

		$LogEsql	= array(
			"endtime"		=>(int)$time,
			"event_code"	=>(int)$eventid,
			"activitytype"	=>$activitytype,
			"runnerid"		=>(int)$runnerid
		);

		$LogQuery	= pdo_query($LogSql,$LogEsql);
	}
}
function GetLatestItemUpdationTime($itemid, $eventid)
{
	global	$Prefix;

	$lastassigntime = time();

	$Sql	= "SELECT * FROM ".$Prefix."event_item_delivery_log WHERE product_id=:product_id AND event_code=:event_code ORDER BY id DESC LIMIT 1";
	$Esql	= array("product_id"=>(int)$itemid,"event_code"=>(int)$eventid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$rows = pdo_fetch_assoc($Query);

		$lastassigntime	= $rows['createdon'];
	}

	return $lastassigntime;
}
function GetOrloOrderID($eventid)
{
	global	$Prefix;

	$Sql		= "SELECT ".$Prefix."order_id FROM orlo_orders WHERE event_code=:event_code ORDER BY id DESC LIMIT 1";
	$Esql	= array("event_code"=>(int)$eventid);
	$Query	= pdo_query($Sql,$Esql);
	$Rows	= pdo_fetch_assoc($Query);

	$maxorderid	= $Rows['orlo_order_id'];

	if($maxorderid > 0)
	{
		$maxorderid	= $maxorderid + 1;
	}
	else
	{
		/*$maxorderid	= "1001";*/
		$maxorderid		= "1";
	}

	return $maxorderid;
}
function TimeTaken($opentime,$closetime,$rcs=0)
{
  $dif	= $closetime - $opentime;
  $pds	= array('second','minute','hour','day','week','month','year','decade');
  $lngh	= array(1,60,3600,86400,604800,2630880,31570560,315705600);

  for ($v = count($lngh) - 1; ($v >= 0) && (($no = $dif / $lngh[$v]) <= 1); $v--);
    if ($v < 0)
      $v = 0;
  $_tm = $closetime - ($dif % $lngh[$v]);

  $no = ($rcs ? floor($no) : round($no)); // if last denomination, round

  if ($no != 1)
    $pds[$v] .= 's';
  $x = $no . ' ' . $pds[$v];

  if (($rcs > 0) && ($v >= 1))
    $x .= ' ' . $this->time_ago($_tm,$closetime,$rcs - 1);

  return $x;
}

function GetEventTable($koteventid)
{
	global $Prefix;

	$EventTableArr	= array();

	$TableSql	= "SELECT * FROM ".$Prefix."seats WHERE event_code=:event_code";
	$TableEsql	= array("event_code"=>(int)$koteventid);

	$TableQuery	= pdo_query($TableSql,$TableEsql);
	$TableNum	= pdo_num_rows($TableQuery);

	if($TableNum > 0)
	{
		while($tablerows = pdo_fetch_assoc($TableQuery))
		{
			$orlo_id	= $tablerows['orlo_id'];
			$seattype	= $tablerows['seattype'];
			$orlo_num	= $tablerows['orlo_num'];

			$EventTableArr[$orlo_id]['seattype']	= $seattype;
			$EventTableArr[$orlo_id]['seatnumber']	= $orlo_num;
		}
	}

	return $EventTableArr;
}
function GetRunnerSummary($eventid)
{
	global $Prefix;

	$recarr	= array();

	$recarr['total']		= 0;
	$recarr['engaged']		= 0;
	$recarr['available']	= 0;
	$recarr['onbreak']		= 0;

	$Sql	= "SELECT * FROM ".$Prefix."event_runners WHERE event_code=:event_code";
	$Esql	= array("event_code"=>(int)$eventid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$isrunner		= $rows['isrunner'];
			$currenttokenid	= $rows['currenttokenid'];
			$isabsent		= $rows['isabsent'];
			$isbreak		= $rows['isbreak'];
			$isactive		= $rows['isactive'];
			$lastassigntime	= $rows['lastassigntime'];
			$isbreak		= 0;

			if($isrunner == 1 && $isactive == 1)
			{
				$recarr['total']	+= 1;
			}

			if($isrunner == 1 && $currenttokenid > 0 && $isabsent == 0 && $isbreak == 0 && $isactive == 1)
			{
				$recarr['engaged']	+= 1;
			}

			if($isrunner == 1 && $currenttokenid < 1 && $isabsent == 0 && $isbreak == 0 && $isactive == 1 && $lastassigntime > 0)
			{
				$recarr['available']	+= 1;
			}

			if($isrunner == 1 && $currenttokenid < 1 && $isabsent == 0 && $isbreak > 0 && $isactive == 1)
			{
				$recarr['onbreak']	+= 1;
			}
		}
	}
	return $recarr;
}
function GetAllItemCategory()
{
	global $Prefix;

	$CategoryArr	= array();

	$CatSql		= "SELECT * FROM ".$Prefix."category WHERE status=:status ORDER BY title ASC";
	$CatEsql	= array("status"=>1);

	$CatQuery	= pdo_query($CatSql,$CatEsql);
	$CatNum		= pdo_num_rows($CatQuery);

	if($CatNum > 0)
	{
		while($catrows = pdo_fetch_assoc($CatQuery))
		{
			$id		= $catrows['id'];
			$title	= $catrows['title'];

			$CategoryArr[$id]	= $title;
		}
	}

	return $CategoryArr;
}
function GetPendingPrintOrder($eventid = 0, $selectedstaffs = "-1")
{
	global $Prefix;

	$ResArr	= array("showpendingprint"=>false,"pendingprint"=>0);

	$Sql	= "SELECT * FROM ".$Prefix."orders WHERE 1 AND event_code=:event_code AND kotid IN (".$selectedstaffs.") AND isprinted<>:isprinted AND isaccepted=:isaccepted AND isrunnerassigned=:isrunnerassigned AND isdelivered<>:isdelivered";
	$Esql	= array("event_code"=>(int)$eventid,"isprinted"=>1,"isaccepted"=>1,"isrunnerassigned"=>1,"isdelivered"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$OrderIDArr	= array();

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$OrderIDArr[]	= $rows['orlo_order_id'];
		}
	}

	$OrderIDStr		= "-1";
	$OrderIDStr2	= "-1";
	if(!empty($OrderIDArr))
	{
		$OrderIDArr		= @array_filter(@array_unique($OrderIDArr));
		$OrderIDStr		= @implode(",",$OrderIDArr);
		$OrderIDStr2	= @implode("-::-",$OrderIDArr);
	}

	$Sql	= "SELECT DISTINCT(tokenno) FROM ".$Prefix."orders_details WHERE 1 AND orlo_order_id IN (".$OrderIDStr.") AND event_code=:event_code AND runnerid > :runnerid AND tokenno > :tokenno AND iscanceled=:iscanceled AND isprinted < :isprinted AND isdelivered=:isdelivered";
	$Esql	= array("event_code"=>(int)$eventid,"runnerid"=>0,"tokenno"=>0,"iscanceled"=>0,"isprinted"=>1,"isdelivered"=>0);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$ResArr["showpendingprint"]	= true;
		$ResArr["pendingprint"]		= $Num;
		$ResArr["pendingorderids"]	= $OrderIDStr2;
	}

	return $ResArr;
}
function GetGuestComplain($eventid = 0)
{
	global $Prefix, $SeatTypeArr;

	$currenttime	= time();

	$ResArr	= array("showguestcomplain"=>false,"totalguestcomplain"=>0);

	$Sql	= "SELECT * FROM ".$Prefix."runners_complain_log WHERE 1 AND event_code=:event_code AND isresolved<>:isresolved";
	$Esql	= array("event_code"=>(int)$eventid,"isresolved"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$EventTableArr	= GetEventTable($eventid);
		$Runners		= GetEventRunners($eventid);

		$DetailArr	= array();
		$index		= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$isitemreorder		= false;
			$issuspendedrunner	= false;

			$complainid		= $rows['id'];
			$orlo_id		= $rows['orlo_id'];
			$runnerid		= $rows['runnerid'];
			$isreorder		= $rows['isreorder'];
			$issuspended	= $Runners[$runnerid]['issuspended'];

			if($isreorder > 0)
			{
				$isitemreorder	= true;
			}

			if($issuspended > 0)
			{
				$issuspendedrunner	= true;
			}

			$seattype	= $EventTableArr[$orlo_id]['seattype'];
			$seatnumber	= $EventTableArr[$orlo_id]['seatnumber'];

			$deliverytime	= TimeTaken($rows['deliveredtime'],$currenttime);

			$DetailArr[$index]['index']					= $index+1;
			$DetailArr[$index]['complainid']			= $complainid;
			$DetailArr[$index]['product_id']			= $rows['product_id'];
			$DetailArr[$index]['product_name']			= $rows['product_name'];
			$DetailArr[$index]['product_name_hindi']	= $rows['product_name_hindi'];
			$DetailArr[$index]['qty']					= $rows['qty'];
			$DetailArr[$index]['seattype']				= $SeatTypeArr[$seattype];
			$DetailArr[$index]['seatnumber']			= $seatnumber;
			$DetailArr[$index]['runnerid']				= $runnerid;
			$DetailArr[$index]['runner']				= $Runners[$runnerid]['displayname'];
			$DetailArr[$index]['isreorder']				= $isitemreorder;
			$DetailArr[$index]['deliverytime']			= $deliverytime;
			$DetailArr[$index]['issuspended']			= $issuspendedrunner;
			$DetailArr[$index]['isupdated']				= false;

			$index++;
		}

		$ResArr["showguestcomplain"]	= true;
		$ResArr["totalguestcomplain"]	= $Num;
		$ResArr["complaindetails"]		= $DetailArr;
	}

	return $ResArr;
}
function GetGuestDetail($eventid = 0, $guestid = 0)
{
	global $Prefix;

	$RecArr	= array();

	$GuestSql	= "SELECT * FROM ".$Prefix."guest WHERE guestid=:guestid AND event_code=:event_code";
	$GuestEsql	= array("guestid"=>(int)$guestid,"event_code"=>(int)$eventid);

	$GuestQuery	= pdo_query($GuestSql,$GuestEsql);
	$GuestNum	= pdo_num_rows($GuestQuery);

	if($GuestNum > 0)
	{
		$guestrows	= pdo_fetch_assoc($GuestQuery);

		$RecArr['guestid']		= $guestrows['guestid'];
		$RecArr['guestname']	= $guestrows['name'];
	}

	return $RecArr;
}
function GetSeatDetail($eventid = 0, $orlo_id = 0)
{
	global $Prefix, $SeatTypeArr;

	$RecArr	= array();

	$TableSql	= "SELECT * FROM ".$Prefix."seats WHERE orlo_id=:orlo_id AND event_code=:event_code";
	$TableEsql	= array("orlo_id"=>(int)$orlo_id,"event_code"=>(int)$eventid);

	$TableQuery	= pdo_query($TableSql,$TableEsql);
	$TableNum	= pdo_num_rows($TableQuery);

	if($TableNum > 0)
	{
		$tablerows	= pdo_fetch_assoc($TableQuery);

		$seattype	= $tablerows['seattype'];
		$orlo_num	= $tablerows['orlo_num'];

		$RecArr['seattype']		= $SeatTypeArr[$seattype];
		$RecArr['seatnumber']	= $orlo_num;
	}

	return $RecArr;
}
function GetAutoPrintStatus($eventid = 0, $staffid = 0)
{
	global $Prefix;

	$currenttime	= time();

	$RecArr['isautoprintactive']	= 0;
	$RecArr['lastprintedtime']		= "";
	$RecArr['lastupdatetime']		= "";
	$RecArr['showspoolingerror']	= false;
	$RecArr['spoolingerrortime']	= "";

	$Sql	= "SELECT * FROM ".$Prefix."auto_print_status WHERE event_code=:event_code AND kotid=:kotid";
	$Esql	= array("event_code"=>(int)$eventid,"kotid"=>(int)$staffid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$rows	= pdo_fetch_assoc($Query);

		$isautoprintactive	= false;

		if($rows['isautoprint'] > 0)
		{
			$isautoprintactive	= true;
		}

		$lastprintedtime	= TimeTaken($rows['lastprintedtime'],$currenttime);
		$lastupdatetime		= TimeTaken($rows['lastupdatetime'],$currenttime);

		$RecArr['isautoprintactive']	= $isautoprintactive;
		$RecArr['lastprintedtime']		= $lastprintedtime;
		$RecArr['lastupdatetime']		= $lastupdatetime;
	}

	$OrderSql	= "SELECT * FROM ".$Prefix."orders WHERE isrunnerassigned=:isrunnerassigned AND runnerassigntime >:runnerassigntime AND isprinted=:isprinted AND isdelivered=:isdelivered AND event_code=:event_code AND iscanceled=:iscanceled ORDER BY runnerassigntime ASC LIMIT 1";
	$OrderEsql	= array("isrunnerassigned"=>1,"runnerassigntime"=>0,"isprinted"=>0,"isdelivered"=>0,"event_code"=>(int)$eventid,"iscanceled"=>0);

	$OrderQuery	= pdo_query($OrderSql,$OrderEsql);
	$OrderNum	= pdo_num_rows($OrderQuery);

	$OrderDetailSql	= "SELECT * FROM ".$Prefix."orders_details WHERE event_code=:event_code AND isdelivered=:isdelivered AND iscanceled=:iscanceled AND iscancelitem=:iscancelitem LIMIT 1";
	$OrderDetailEsql	= array("event_code"=>(int)$eventid,"isdelivered"=>0,"iscanceled"=>0,"iscancelitem"=>0);

	$OrderDetailQuery	= pdo_query($OrderDetailSql,$OrderDetailEsql);
	$OrderDetailNum		= pdo_num_rows($OrderDetailQuery);

	if($OrderNum > 0 && $OrderDetailNum > 0)
	{
		$OrderRows	= pdo_fetch_assoc($OrderQuery);

		$runnerassigntime	= $OrderRows['runnerassigntime'];

		if($currenttime > ($runnerassigntime+5))
		{
			$runnerassigntime	= TimeTaken($runnerassigntime,$currenttime);

			$RecArr['showspoolingerror']	= true;
			$RecArr['spoolingerrortime']	= $runnerassigntime;
		}
	}

	return $RecArr;
}
function SyncUpcomingOrder($eventid, $koteid)
{
	global $Prefix;

	$EngagedRunnerLoop = 0;

	$Runners	= GetEventRunners($eventid);

	$UpcomingOrderArr	= array();

	$RunnerSql	= "SELECT * FROM ".$Prefix."event_runners WHERE event_code=:event_code AND isrunner=:isrunner ORDER BY name ASC";
	$RunnerEsql	= array("event_code"=>(int)$eventid,"isrunner"=>1);

	$RunnerQuery	= pdo_query($RunnerSql,$RunnerEsql);
	$RunnerNum		= pdo_num_rows($RunnerQuery);

	if($RunnerNum > 0)
	{
		$EngagedRunnerArr	= array();

		while($Runnerrows = pdo_fetch_assoc($RunnerQuery))
		{
			$name			= $Runnerrows['name'];
			$runnerid		= $Runnerrows['runnerid'];
			$lastassigntime	= $Runnerrows['lastassigntime'];
			$currenttokenid	= $Runnerrows['currenttokenid'];
			$isactive		= $Runnerrows['isactive'];
			$isabsent		= $Runnerrows['isabsent'];

			$runnername		= $Runners[$runnerid]['displayname'];

			if($currenttokenid > 0)
			{
				$DetailSql	= "SELECT * FROM ".$Prefix."orders_details WHERE event_code=:event_code AND tokenno=:tokenno AND iscanceled=:iscanceled AND isdelivered<>:isdelivered";
				$DetailEsql	= array("event_code"=>(int)$eventid,"tokenno"=>(int)$currenttokenid,"iscanceled"=>0,"isdelivered"=>1);

				$DetailQuery	= pdo_query($DetailSql,$DetailEsql);
				$DetailNum		= pdo_num_rows($DetailQuery);

				$PendingTime	= TimeTaken($lastassigntime,$time);

				$EngagedRunnerArr[$loop]['index']			= $loop+1;
				$EngagedRunnerArr[$loop]['name']			= $runnername;
				$EngagedRunnerArr[$loop]['runnerid']		= $runnerid;
				$EngagedRunnerArr[$loop]['tokenno']			= $currenttokenid;
				$EngagedRunnerArr[$loop]['status']			= "engaged since ".$PendingTime;
				$EngagedRunnerArr[$loop]['itemquantity']	= $DetailNum;

				$loop++;
			}
		}
		$UpcomingOrderArr['engagedrunner']	= $EngagedRunnerArr;
	}

	$EventSql	= "SELECT * FROM ".$Prefix."events WHERE event_code=:event_code";
	$EventEsql	= array("event_code"=>(int)$eventid);

	$EventQuery	= pdo_query($EventSql,$EventEsql);
	$EventRows	= pdo_fetch_assoc($EventQuery);

	$isdefaultevent	= false;

	$isdefault	= $EventRows['isdefault'];

	if($isdefault > 0)
	{
		$isdefaultevent	= true;
	}

	$RunnerSummary	= GetRunnerSummary($eventid);
	$UpcomingOrderArr['totalrunner']		= (int)$RunnerSummary['total'];
	$UpcomingOrderArr['engagedrunner']		= (int)$RunnerSummary['engaged'];
	$UpcomingOrderArr['availablerunner']	= (int)$RunnerSummary['available'];

	$PendingPrintOrder	= GetPendingPrintOrder($eventid, $koteid);
	$UpcomingOrderArr['showpendingprint']	= $PendingPrintOrder['showpendingprint'];
	$UpcomingOrderArr['pendingprint']		= $PendingPrintOrder['pendingprint'];
	$UpcomingOrderArr['pendingorderids']	= $PendingPrintOrder['pendingorderids'];

	$AutoPrintStatus	= GetAutoPrintStatus($eventid, $koteid);
	$UpcomingOrderArr['isautoprintactive']	= $AutoPrintStatus['isautoprintactive'];
	$UpcomingOrderArr['lastupdatetime']		= $AutoPrintStatus['lastupdatetime'];
	$UpcomingOrderArr['showspoolingerror']	= $AutoPrintStatus['showspoolingerror'];
	$UpcomingOrderArr['spoolingerrortime']	= $AutoPrintStatus['spoolingerrortime'];
	$UpcomingOrderArr['isdefaultevent']		= $isdefaultevent;

	$GuestComplainData	= GetGuestComplain($eventid);
	$UpcomingOrderArr['showguestcomplain']	= $GuestComplainData['showguestcomplain'];
	$UpcomingOrderArr['totalguestcomplain']	= $GuestComplainData['totalguestcomplain'];
	$UpcomingOrderArr['complaindetails']	= $GuestComplainData['complaindetails'];

	$PendingOrdersData	= GetEventPendingOrders($eventid, $koteid);
	foreach($PendingOrdersData as $OrdersDataKey => $OrdersDataValue)
	{
		$UpcomingOrderArr[$OrdersDataKey]	= $OrdersDataValue;
	}

	$DeliveredOrdersData	= GetDeliveredOrders($eventid, $koteid);
	foreach($DeliveredOrdersData as $OrdersDataKey => $OrdersDataValue)
	{
		$UpcomingOrderArr[$OrdersDataKey]	= $OrdersDataValue;
	}

	return $UpcomingOrderArr;
}
function GetRunnerAssignedTime($EventID,$TokenNumber,$RunnerID)
{
	global $Prefix;

	$Sql	= "SELECT * FROM ".$Prefix."event_runners_delivery_log WHERE event_code=:event_code AND runnerid=:runnerid AND tokenid=:tokenid";
	$Esql	= array("event_code"=>(int)$EventID,"runnerid"=>(int)$RunnerID,"tokenid"=>(int)$TokenNumber);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$Row			= pdo_fetch_assoc($Query);
		/*$lastassigntime = $Row['lastassigntime'];*/
		$lastassigntime	= $Row['createdon'];
	}
	else
	{
		$RunnerSql	= "SELECT * FROM ".$Prefix."event_runners WHERE event_code=:event_code AND isrunner=:isrunner AND runnerid=:runnerid ORDER BY ispriority DESC, lastdeliverytime ASC, runnerid ASC";
		$RunnerEsql	= array("event_code"=>(int)$EventID,"isrunner"=>1,"runnerid"=>(int)$RunnerID);

		$RunnerQuery	= pdo_query($RunnerSql,$RunnerEsql);
		$RunnerNum		= pdo_num_rows($RunnerQuery);

		if($RunnerNum > 0)
		{
			$RunnerRows	= pdo_fetch_assoc($RunnerQuery);
			$lastassigntime	= $RunnerRows['lastassigntime'];
		}
	}

	return $lastassigntime;
}
function GetRunnerAssignedByToken($EventID,$TokenNumber)
{
	global $Prefix;

	$Arr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."event_runners_delivery_log WHERE event_code=:event_code AND tokenid=:tokenid ORDER BY id DESC LIMIT 1";
	$Esql	= array("event_code"=>(int)$EventID,"tokenid"=>(int)$TokenNumber);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$Arr	= pdo_fetch_assoc($Query);
	}

	return $Arr;
}
function GetWaitingTimeAndPendingQueue($orlo_id, $event_id)
{
	global $Prefix;

	$Success	= false;

	$RecordArr	= array();

	/*$TableOrderSql	= "SELECT * FROM ".$Prefix."orders WHERE orlo_id=:orlo_id AND event_id=:event_id AND isdelivered<>:isdelivered AND iscanceled=:iscanceled AND isrunnerassigned<>:isrunnerassigned ORDER BY id ASC LIMIT 1";

	$TableOrderEsql	= array("orlo_id"=>(int)$orlo_id,"event_id"=>(int)$event_id,"isdelivered"=>1,"iscanceled"=>0,"isrunnerassigned"=>1);*/

	$TableOrderSql	= "SELECT * FROM ".$Prefix."orders WHERE event_code=:event_code AND isdelivered<>:isdelivered AND iscanceled=:iscanceled AND isrunnerassigned<>:isrunnerassigned ORDER BY id ASC LIMIT 1";

	$TableOrderEsql	= array("event_code"=>(int)$event_id,"isdelivered"=>1,"iscanceled"=>0,"isrunnerassigned"=>1);
	$TableOrderQuery	= pdo_query($TableOrderSql,$TableOrderEsql);
	$TableOrderNum		= pdo_num_rows($TableOrderQuery);

	if($TableOrderNum > 0)
	{
		$TableOrderRows			= pdo_fetch_assoc($TableOrderQuery);
		$TableOrderCreatedon	= $TableOrderRows['createdon'];
	}

	$LatestOrderCreatedon	= time();

	if($TableOrderCreatedon > 0 && $LatestOrderCreatedon > 0)
	{
		if($TableOrderCreatedon > $LatestOrderCreatedon)
		{
			$WaitingTime	= TimeTaken($LatestOrderCreatedon,$TableOrderCreatedon);
		}
		else
		{
			$WaitingTime	= TimeTaken($TableOrderCreatedon,$LatestOrderCreatedon);
		}
		$Success	= true;
	}

	$PendingQueueSql	= "SELECT * FROM ".$Prefix."orders WHERE event_code=:event_code AND isrunnerassigned<>:isrunnerassigned AND isdelivered<>:isdelivered AND iscanceled=:iscanceled ORDER BY id ASC";

	$PendingQueueEsql	= array("event_code"=>(int)$event_id,"isrunnerassigned"=>1,"isdelivered"=>1,"iscanceled"=>0);

	$PendingQueueQuery	= pdo_query($PendingQueueSql,$PendingQueueEsql);
	$PendingQueueNum	= pdo_num_rows($PendingQueueQuery);

	$QueuePositionLoop		= 0;

	$CurrentQueuePosition	= 0;

	if($PendingQueueNum > 0)
	{
		$Success	= true;

		while($PendingQueueRows = pdo_fetch_assoc($PendingQueueQuery))
		{
			$QueuePositionLoop++;

			$order_orlo_id	= $PendingQueueRows['orlo_id'];

			if(($order_orlo_id == $orlo_id) && $CurrentQueuePosition < 1)
			{
				$CurrentQueuePosition	= $QueuePositionLoop;
			}
		}
	}

	$RecordArr['waitingtime']	= $WaitingTime;
	$RecordArr['pendingqueue']	= $CurrentQueuePosition+1;
	$RecordArr['success']		= $Success;

	return $RecordArr;
}
function GetTotalTokenByEvent($eventid)
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."event_runners_delivery_log WHERE event_code=:event_code";
	$Esql	= array("event_code"=>(int)$eventid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$runnerid	= $rows['runnerid'];
			$tokenid	= $rows['tokenid'];

			if($tokenid > 0)
			{
				$RecArr[$runnerid]	+= 1;
			}
		}
	}

	return $RecArr;
}
function GetDeliverdItemsByEventID($EventID)
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."event_item_delivery_log WHERE event_code=:event_code";
	$Esql	= array("event_code"=>(int)$EventID);

	$Query	= pdo_query($Sql,$Esql);
	
	while($rows = pdo_fetch_assoc($Query))
	{
		$runnerid	= $rows['runnerid'];

		$RecArr[$runnerid]	+= 1;
	}

	return $RecArr;
}
function GetOrlo_OrderID($OrderID)
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."orders WHERE id=:id";
	$Esql	= array("id"=>(int)$OrderID);

	$Query	= pdo_query($Sql,$Esql);

	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$row			= pdo_fetch_assoc($Query);
		$Orlo_Order_Id	= $row['orlo_order_id']; 
	}

	return $Orlo_Order_Id;
}
function CheckUseAutoPrint($eventid)
{
	global $Prefix;

	$useautoprint	= 0;

	$checkSql	= "SELECT * FROM ".$Prefix."events WHERE event_code=:event_code";
	$checkEsql	= array("event_code"=>(int)$eventid);

	$checkQuery	= pdo_query($checkSql,$checkEsql);
	$checkNum	= pdo_num_rows($checkQuery);

	if($checkNum > 0)
	{
		$checkrows	= pdo_fetch_assoc($checkQuery);

		$useautoprint	= $checkrows['useautoprint'];
	}
	return $useautoprint;
}
function GetOrderIDByOrloOrderID($eventid,$orlo_order_id)
{
	global $Prefix;

	$order_id	= "";

	$Sql	= "SELECT * FROM ".$Prefix."orders WHERE orlo_order_id=:orlo_order_id AND event_code=:event_code";
	$Esql	= array("orlo_order_id"=>(int)$orlo_order_id,"event_code"=>$eventid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$row		= pdo_fetch_assoc($Query);
		$order_id	= $row['id']; 
	}

	return $order_id;
}
function MarkOrderPrintDone($orderid, $eventid)
{
	global $Prefix;

	$isprinted	= false;

	$OrderIDsArr	= array();

	$OrderIDsArr	= @explode("-::-",$orderid);
	$OrderIDsArr	= @array_filter(@array_unique($OrderIDsArr));

	if(!empty($OrderIDsArr))
	{
		$OrderIDsStr	= implode(",",$OrderIDsArr);
	}
	else
	{
		$OrderIDsStr	= "-1";
	}

	$Sql3	= "UPDATE ".$Prefix."orders SET isprinted=:isprinted WHERE id IN (".$OrderIDsStr.")";
	$Esql3	= array("isprinted"=>1);

	$Query3	= pdo_query($Sql3,$Esql3);


 	$Sql	= "SELECT * FROM ".$Prefix."orders WHERE id IN(".$OrderIDsStr.")";
	$Esql	= array();

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);
	
	$OrderIDsStr2 = '';
	if($Num > 0)
	{
		while($row2 = pdo_fetch_assoc($Query))
		{
			$OrderIDsStr2	.= $row2['orlo_order_id'].","; 
		}
		
		$OrderIDsStr2 .="@@";
		$OrderIDsStr2	= str_replace(",@@","",$OrderIDsStr2);
	}
	else
	{
		$OrderIDsStr2 = "-1";
	}

	$Sql2	= "UPDATE ".$Prefix."orders_details SET isprinted=:isprinted WHERE orlo_order_id IN (".$OrderIDsStr2.") AND event_code=:event_code AND runnerid > :runnerid AND tokenno > :tokenno AND iscanceled=:iscanceled AND iscancelitem=:iscancelitem";
	$Esql2	= array("event_code"=>(int)$orderid,"isprinted"=>1,"runnerid"=>0,"tokenno"=>0,"iscanceled"=>0,"iscancelitem"=>0);

	$Query2	= pdo_query($Sql2,$Esql2);

	if($Query2 || $Query3)
	{
		$isprinted	= true;
	}
	return $isprinted;
}
function GetEventPendingOrders($eventid, $kotid)
{
	global $Prefix, $SeatTypeArr;

	$pendingbatchcount	= 0;
	$acceptedbatchcount	= 0;

	$EventBatchsArr			= array();
	$EventAcceptedBatchArr	= array();

	$pendingindex	= 0;
	$acceptedindex	= 0;

	$colorindex		= 1;
	$colorindex2	= 1;

	$BatchArr	= GetPendingOrderBatch($eventid);

	foreach($BatchArr as $Key=>$Value)
	{
		$BatchID = $Value['batchid'];

		$Sql	= "SELECT * FROM ".$Prefix."orders WHERE 1 AND batchid=:batchid AND isaccepted=:isaccepted AND event_code=:event_code AND isdelivered<>:isdelivered AND iscanceled=:iscanceled ORDER BY createdon ASC";
		$Esql	= array("batchid"=>(int)$BatchID,"isaccepted"=>0,"event_code"=>(int)$eventid,"isdelivered"=>1,"iscanceled"=>0);

		$Query	= pdo_query($Sql,$Esql);
		$Num	= pdo_num_rows($Query);

		if($Num > 0)
		{
			if($colorindex > 4)
			{
				$colorindex	= 1;
			}

			if($BatchID == 999999)
			{
				$EventBatchsArr[$pendingindex]['colorindex']	= "vip";
			}
			else
			{
				$EventBatchsArr[$pendingindex]['colorindex']	= $colorindex;
			}

			$EventBatchsArr[$pendingindex]['batchid']		= (int)$BatchID;
			$EventBatchsArr[$pendingindex]['batchname']		= $Value['name'];
			$EventBatchsArr[$pendingindex]['totalorders']	= $Num;

			$pendingbatchcount++;

			$pendingindex++;
			$colorindex++;
		}

		$Sql2	= "SELECT * FROM ".$Prefix."orders WHERE 1 AND kotid=:kotid AND batchid=:batchid AND isaccepted=:isaccepted AND isrunnerassigned < :isrunnerassigned AND event_code=:event_code AND isdelivered<>:isdelivered AND iscanceled=:iscanceled ORDER BY createdon ASC";
		$Esql2	= array("kotid"=>(int)$kotid,"batchid"=>(int)$BatchID,"isaccepted"=>1,"isrunnerassigned"=>1,"event_code"=>(int)$eventid,"isdelivered"=>1,"iscanceled"=>0);

		$Query2	= pdo_query($Sql2,$Esql2);
		$Num2	= pdo_num_rows($Query2);

		if($Num2 > 0)
		{
			if($colorindex2 > 4)
			{
				$colorindex2	= 1;
			}

			if($BatchID == 999999)
			{
				$EventAcceptedBatchArr[$acceptedindex]['colorindex']	= "vip";
			}
			else
			{
				$EventAcceptedBatchArr[$acceptedindex]['colorindex']	= $colorindex2;
			}

			$EventAcceptedBatchArr[$acceptedindex]['batchid']		= (int)$BatchID;
			$EventAcceptedBatchArr[$acceptedindex]['batchname']	= $Value['name'];
			$EventAcceptedBatchArr[$acceptedindex]['totalorders']	= $Num2;

			$TableSql	= "SELECT * FROM ".$Prefix."seats WHERE event_code=:event_code";
			$TableEsql	= array("event_code"=>(int)$eventid);

			$TableQuery	= pdo_query($TableSql,$TableEsql);
			$TableNum	= pdo_num_rows($TableQuery);

			if($TableNum > 0)
			{
				while($tablerows = pdo_fetch_assoc($TableQuery))
				{
					$orlo_id	= $tablerows['orlo_id'];
					$seattype	= $tablerows['seattype'];
					$orlo_num	= $tablerows['orlo_num'];

					$EventTableArr[$orlo_id]['seattype']	= $seattype;
					$EventTableArr[$orlo_id]['seatnumber']	= $orlo_num;
				}
			}

			$OrdersArr	= array();

			while($rows2 = pdo_fetch_assoc($Query2))
			{
				$orderid		= $rows2['id'];
				$orlo_id		= $rows2['orlo_id'];
				$orlo_order_id	= $rows2['orlo_order_id'];

				$OrderDetailSql		= "SELECT * FROM ".$Prefix."orders_details WHERE orlo_order_id=:orlo_order_id AND event_code=:event_code AND isdelivered<>:isdelivered AND tokenno < :tokenno AND runnerid < :runnerid AND iscanceled=:iscanceled AND iscancelitem=:iscancelitem";
				$OrderDetailEsql	= array("orlo_order_id"=>(int)$orlo_order_id,"event_code"=>(int)$eventid,"isdelivered"=>1,"tokenno"=>1,"runnerid"=>1,"iscanceled"=>0,"iscancelitem"=>0);

				$OrderDetailQuery	= pdo_query($OrderDetailSql,$OrderDetailEsql);
				$OrderDetailNum		= pdo_num_rows($OrderDetailQuery);

				$EventTable	= $EventTableArr[$orlo_id];

				$OrdersArr[$orderid]['id']				= $orderid;
				$OrdersArr[$orderid]['orlo_order_id']	= $orlo_order_id;
				$OrdersArr[$orderid]['name']			= "Order #".$orlo_order_id;
				$OrdersArr[$orderid]['seattype']		= $SeatTypeArr[$EventTable['seattype']];
				$OrdersArr[$orderid]['seatnumber']		= $EventTable['seatnumber'];
				$OrdersArr[$orderid]['totalitem']		= $OrderDetailNum;
			}
			$EventAcceptedBatchArr[$acceptedindex]['orders']	= $OrdersArr;

			$acceptedindex++;
			$acceptedbatchcount++;

			$colorindex2++;
		}
	}

	$Sql	= "SELECT * FROM ".$Prefix."orders WHERE 1 AND kotid=:kotid AND isdelivered<>:isdelivered AND event_code=:event_code AND iscanceled=:iscanceled ORDER BY createdon ASC";
	$Esql	= array("kotid"=>(int)$kotid,"isdelivered"=>1,"event_code"=>(int)$eventid,"iscanceled"=>0);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index = 0;

	$LastFiveToken	= array();

	if($Num > 0)
	{
		$IndexArr				= array();
		$TokennoArr				= array();
		$RunneridArr			= array();
		$NameArr				= array();
		$OrderidArr				= array();
		$OrloOrderIdArr			= array();
		$RunnerAssignTimeArr	= array();

		while($rows = pdo_fetch_assoc($Query))
		{
			$orderid		= $rows['id'];
			$orlo_order_id	= $rows['orlo_order_id'];
			$isvip			= $rows['isvip'];

			$vipname	= "";

			if($isvip > 0)
			{
				$vipname	= "(VIP)";
			}

			$TokenSql		= "SELECT details.*,orders.runnerassigntime AS runnerassigntime FROM ".$Prefix."orders_details details,".$Prefix."orders orders WHERE details.orlo_order_id=:orlo_order_id AND details.event_code=:event_code AND details.isdelivered<>:isdelivered AND details.tokenno >:tokenno AND details.runnerid >:runnerid AND details.iscanceled=:iscanceled AND details.iscancelitem=:iscancelitem GROUP BY details.tokenno ORDER BY details.tokenno DESC,orders.runnerassigntime DESC";
			$TokenEsql	= array("orlo_order_id"=>(int)$orlo_order_id,"event_code"=>(int)$eventid,"isdelivered"=>1,"tokenno"=>0,"runnerid"=>0,"iscanceled"=>0,"iscancelitem"=>0);

			$TokenQuery	= pdo_query($TokenSql,$TokenEsql);
			$TokenNum	= pdo_num_rows($TokenQuery);

			if($TokenNum > 0)
			{
				while($tokenrows = pdo_fetch_assoc($TokenQuery))
				{
					/*if($index > 4)
					{
						break;
					}*/
					$tokenno				= $tokenrows['tokenno'];
					$runnerid				= $tokenrows['runnerid'];
					$runnerassigntime		= $tokenrows['runnerassigntime']+$index;
					$orlo_order_id			= $tokenrows['orlo_order_id'];

					$IndexArr[]				= $index;
					$TokennoArr[]			= $tokenno;
					$RunneridArr[]			= $runnerid;
					$NameArr[]				= "Token #".$tokenno." ".$vipname;
					$OrderidArr[]			= $orderid;
					$OrloOrderIdArr[]		= $orlo_order_id;
					$RunnerAssignTimeArr[]	= $runnerassigntime;

					$index++;
				}
			}
		}

		$index	= 0;

		foreach($RunnerAssignTimeArr as $assigntimekey => $assigntimevalue)
		{
			if($index > 4)
			{
				break;
			}
			$LastFiveToken[$assigntimekey]['tokenno']		= $TokennoArr[$assigntimekey];
			$LastFiveToken[$assigntimekey]['index']			= $IndexArr[$assigntimekey];
			$LastFiveToken[$assigntimekey]['runnerid']		= $RunneridArr[$assigntimekey];
			$LastFiveToken[$assigntimekey]['name']			= $NameArr[$assigntimekey];
			$LastFiveToken[$assigntimekey]['orderid']		= $OrderidArr[$assigntimekey];
			$LastFiveToken[$assigntimekey]['orlo_order_id']	= $OrloOrderIdArr[$assigntimekey];
		}
	}
	$RecordSet	= array();

	$RecordSet['eventbatchs']			= $EventBatchsArr;
	$RecordSet['eventacceptedbatchs']	= $EventAcceptedBatchArr;
	$RecordSet['eventorders']			= array();
	$RecordSet['lastfivetoken']			= $LastFiveToken;
	$RecordSet['pendingbatchcount']		= $pendingbatchcount;
	$RecordSet['acceptedbatchcount']	= $acceptedbatchcount;

	return $RecordSet;
}
function GetDeliveredOrders($eventid, $kotid)
{
	global $Prefix, $SeatTypeArr;

	$DeliveredOrderArr	= array();

	$deliveredindex	= 0;
	$colorindex		= 1;

	$TableSql	= "SELECT * FROM ".$Prefix."seats WHERE event_code=:event_code";
	$TableEsql	= array("event_code"=>(int)$eventid);

	$TableQuery	= pdo_query($TableSql,$TableEsql);
	$TableNum	= pdo_num_rows($TableQuery);

	if($TableNum > 0)
	{
		while($tablerows = pdo_fetch_assoc($TableQuery))
		{
			$orlo_id	= $tablerows['orlo_id'];
			$seattype	= $tablerows['seattype'];
			$seatnumber	= $tablerows['orlo_num'];

			$CheckSql	= "SELECT DISTINCT(orlo_order_id) FROM ".$Prefix."orders_details WHERE tokenno > :tokenno AND runnerid > :runnerid AND isdelivered=:isdelivered AND event_code=:event_code AND iscanceled=:iscanceled AND iscancelitem=:iscancelitem ORDER BY category_id ASC";
			$CheckEsql	= array("tokenno"=>0,"runnerid"=>0,"isdelivered"=>1,"event_code"=>(int)$eventid,"iscanceled"=>0,"iscancelitem"=>0);

			$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
			$CheckNum	= pdo_num_rows($CheckQuery);

			$DeliveredOrderIDArr	= array();

			if($CheckNum > 0)
			{
				while($checkrows = pdo_fetch_assoc($CheckQuery))
				{
					$DeliveredOrderIDArr[]	= $checkrows['orlo_order_id'];
				}
			}

			$DeliveredOrderIDArr	= @array_filter(@array_unique($DeliveredOrderIDArr));
			$DeliveredOrderIDStr	= implode(",",$DeliveredOrderIDArr);

			if(empty($DeliveredOrderIDArr))
			{
				$DeliveredOrderIDStr	= "-1";
			}

			$Sql2	= "SELECT * FROM ".$Prefix."orders WHERE 1 AND kotid=:kotid AND orlo_id=:orlo_id AND isdelivered=:isdelivered AND event_code=:event_code AND orlo_order_id IN(".$DeliveredOrderIDStr.") AND iscanceled=:iscanceled ORDER BY id DESC";
			$Esql2	= array("kotid"=>(int)$kotid,"orlo_id"=>(int)$orlo_id,"isdelivered"=>1,"event_code"=>(int)$eventid,"iscanceled"=>0);

			$Query2	= pdo_query($Sql2,$Esql2);
			$Num2	= pdo_num_rows($Query2);

			if($Num2 > 0)
			{
				$orderindex	= 0;
				$OrdersArr	= array();

				if($colorindex > 4)
				{
					$colorindex	= 1;
				}

				$DeliveredOrderArr[$deliveredindex]['index']		= $deliveredindex;
				$DeliveredOrderArr[$deliveredindex]['orlo_id']		= $orlo_id;
				$DeliveredOrderArr[$deliveredindex]['seatnumber']	= $seatnumber;
				$DeliveredOrderArr[$deliveredindex]['seattype']		= $SeatTypeArr[$seattype];
				$DeliveredOrderArr[$deliveredindex]['colorindex']	= $colorindex;

				while($orderrows = pdo_fetch_assoc($Query2))
				{
					$orderid		= $orderrows['id'];
					$orlo_order_id	= $orderrows['orlo_order_id'];
					$orlo_id		= $orderrows['orlo_id'];
					$isvip			= $orderrows['isvip'];

					$TokenSql		= "SELECT * FROM ".$Prefix."orders_details WHERE orlo_order_id=:orlo_order_id AND event_code=:event_code AND isdelivered=:isdelivered AND tokenno > :tokenno AND runnerid > :runnerid AND iscanceled=:iscanceled AND iscancelitem=:iscancelitem GROUP BY tokenno ORDER BY tokenno ASC";
					$TokenEsql	= array("orlo_order_id"=>(int)$orlo_order_id,"event_code"=>(int)$eventid,"isdelivered"=>1,"tokenno"=>0,"runnerid"=>0,"iscanceled"=>0,"iscancelitem"=>0);

					$TokenQuery	= pdo_query($TokenSql,$TokenEsql);
					$TokenNum	= pdo_num_rows($TokenQuery);

					if($TokenNum > 0)
					{
						while($tokenrows = pdo_fetch_assoc($TokenQuery))
						{
							$tokenno	= $tokenrows['tokenno'];
							$runnerid	= $tokenrows['runnerid'];

							$OrderDetailSql		= "SELECT * FROM ".$Prefix."orders_details WHERE orlo_order_id=:orlo_order_id AND event_code=:event_code AND tokenno=:tokenno AND isdelivered=:isdelivered AND iscanceled=:iscanceled AND iscancelitem=:iscancelitem";
							$OrderDetailEsql	= array("orlo_order_id"=>(int)$orlo_order_id,"event_code"=>(int)$eventid,"tokenno"=>(int)$tokenno,"isdelivered"=>1,"iscanceled"=>0,"iscancelitem"=>0);

							$OrderDetailQuery	= pdo_query($OrderDetailSql,$OrderDetailEsql);
							$OrderDetailNum		= pdo_num_rows($OrderDetailQuery);

							$vipname	= "";
							if($isvip > 0)
							{
								$vipname	= "(VIP)";
							}

							$OrdersArr[$orderindex]['tokenno']			= $tokenno;
							$OrdersArr[$orderindex]['runnerid']			= $runnerid;
							$OrdersArr[$orderindex]['name']				= "Token #".$tokenno." ".$vipname;
							$OrdersArr[$orderindex]['totalitem']		= $OrderDetailNum;
							$OrdersArr[$orderindex]['orderid']			= $orderid;
							$OrdersArr[$orderindex]['orlo_order_id']	= $orlo_order_id;

							$orderindex++;
						}
					}
				}

				$DeliveredOrderArr[$deliveredindex]['orders']	= $OrdersArr;
				$deliveredindex++;

				$colorindex++;
			}
		}
	}

	$RecordSet	= array();

	$RecordSet['hasdeliveredorder']	= false;
	$RecordSet['deliveredorder']	= $DeliveredOrderArr;
	if(!empty($RecordSet['deliveredorder']))
	{
		$RecordSet['hasdeliveredorder']	= true;
	}

	return $RecordSet;
}
function GetClientInventory($clientid,$stateid,$cityid)
{
	global $Prefix;

	$RecordSet	= array();

	$Sql	= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND cityid=:cityid AND stateid=:stateid";
	$Esql	= array("clientid"=>(int)$clientid,"cityid"=>(int)$cityid,"stateid"=>(int)$stateid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$RecordSet[$rows['inventoryid']]['status']	= (int)$rows['status'];
			$RecordSet[$rows['inventoryid']]['price']	= $rows['price'];
		}
	}

	return $RecordSet;
}
function GetClientActiveInventory($clientid)
{
	global $Prefix;

	$RecordSet	= array();

	$Sql	= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND status=:status";
	$Esql	= array("clientid"=>(int)$clientid,'status'=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$RecordSet[]	= (int)$rows['inventoryid'];
		}
	}

	return $RecordSet;
}
function GetAllCustomerNameByClientID($ClientID)
{
	global $Prefix;

	$SQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ORDER BY sequence ASC, customerid ASC";
	$ESQL	= array("clientid"=>(int)$ClientID,"deletedon"=>1);
	$Query  = pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	
	$Arr	= array();
	if($Num > 0)
	{
		while($Row	= pdo_fetch_assoc($Query))
		{
			$ID			= $Row['id'];
			$Name		= $Row['name'];
			$CustomerID	= $Row['customerid'];
			$Phone		= $Row['phone'];

			$Arr[$ID]['name'] = $Name;
			$Arr[$ID]['phone'] = $Phone;
			$Arr[$ID]['customerid'] = $CustomerID;
		}
	}
	return $Arr;
}
function GetInventoryNames()
{
	global $Prefix;
	$SQL	= "SELECT * FROM ".$Prefix."inventory ORDER BY id ASC";
	$ESQL	= array();
	$Query  = pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	
	$Arr	= array();
	if($Num > 0)
	{
		while($Row	= pdo_fetch_assoc($Query))
		{
			$ID			= $Row['id'];
			$categoryid	= $Row['categoryid'];
			$Name		= $Row['name'];

			$Arr[$ID]['categoryid'] = $categoryid;
			$Arr[$ID]['name']		= $Name;
		}
	}
	return $Arr;
}
function GetCustomerSubscriptions($customerid)
{
	global $Prefix, $DaysListArr;

	$RecordSetArr	= array();

	$SQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid ";
	$ESQL	= array("customerid"=>(int)$customerid);

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$inventoryid		= $rows['inventoryid'];
			$quantity			= $rows['quantity'];
			$subscriptiondate	= $rows['subscriptiondate'];
			$daysArr			= @explode("::",$rows['days']);

			$NewDaysArr		= array();

			$index	= 0;

			if(!empty($daysArr))
			{
				foreach($DaysListArr as $daykey=>$dayrows)
				{
					$id			= $dayrows['id'];
					$name		= $dayrows['name'];
					$checked	= $dayrows['checked'];

					if(!@in_array($id, $daysArr))
					{
						$checked	= false;
					}

					$NewDaysArr[$index]['id']		= (int)$id;
					$NewDaysArr[$index]['name']		= $name;
					$NewDaysArr[$index]['checked']	= $checked;

					$index++;
				}
			}
			else
			{
				$NewDaysArr	= $DaysListArr;
			}

			$RecordSetArr[$inventoryid]['id']				= (int)$inventoryid;
			$RecordSetArr[$inventoryid]['quantity']			= (int)$quantity;
			$RecordSetArr[$inventoryid]['days']				= $NewDaysArr;
			$RecordSetArr[$inventoryid]['subscriptiondate']	= date("Y-m-d",$subscriptiondate);
		}
	}

	return $RecordSetArr;
}
function GetActiveCustomerSubscriptions($clientid)
{
	global $Prefix;

	$RecordSetArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon <:deletedon ORDER BY sequence ASC, customerid ASC";
	$Esql	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$customerid	= $rows['id'];

			$SubscriptionSql	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid";
			$SubscriptionEsql	= array("customerid"=>(int)$customerid);

			$SubscriptionQuery	= pdo_query($SubscriptionSql,$SubscriptionEsql);
			$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);

			if($SubscriptionNum > 0)
			{
				while($subscriptionrows = pdo_fetch_assoc($SubscriptionQuery))
				{
					$inventoryid	= $subscriptionrows['inventoryid'];

					$RecordSetArr[$inventoryid]	= (int)$inventoryid;
				}
			}
		}
	}

	return $RecordSetArr;
}
function CheckClientInvoiceByYearMonth($clientid, $month, $year)
{
	global $Prefix;

	$hasinvoce	= false;

	$CheckSQL  = "SELECT * FROM ".$Prefix."invoice_request_queue WHERE clientid=:clientid AND month=:month AND year=:year AND status >:status";
	$CheckESQL = array("clientid"=>(int)$clientid,"month"=>(int)$month,"year"=>(int)$year,'status'=>0);
	$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$hasinvoce	= true;
	}

	return $hasinvoce;
}
function GetAllStates($CountryID ='')
{
	global $Prefix;
	
	$ESQL	= array();
	$ExtArg	= "";

	if($CountryID > 0)
	{
		$ExtArg = " AND countryid=:countryid";
		$ESQL['countryid'] = (int)$CountryID; 
	}

	$AllRecordArr	= array();
	$SQL	= "SELECT * FROM ".$Prefix."states WHERE 1 ".$ExtArg." ORDER BY name ASC";
	
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($Rows = pdo_fetch_assoc($Query))
		{
			$ID		= trim($Rows['id']);
			$Name	= strtolower(trim($Rows['name']));

			$AllRecordArr["".$Name.""]	= $ID;
		}
	}

	return $AllRecordArr;
}
function GetAllCity($CountryID ='',$StateID='')
{
	global $Prefix;

	$ESQL	= array();
	$ExtArg	= "";

	if($CountryID > 0)
	{
		$ExtArg	= " AND countryid=:countryid";
		$ESQL['countryid'] = (int)$CountryID; 
	}

	if($StateID > 0)
	{
		$ExtArg	= " AND stateid =:stateid";
		$ESQL['stateid'] = (int)$StateID; 
	}

	$AllRecordArr	= array();
	
	$SQL	= "SELECT * FROM ".$Prefix."cities WHERE 1 ".$ExtArg." ORDER BY name ASC";
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	
	if($Num > 0)
	{
		while($Rows = pdo_fetch_assoc($Query))
		{
			$ID		= trim($Rows['id']);
			$Name	= strtolower(trim($Rows['name']));
			$StateID = trim($Rows['stateid']);

			$AllRecordArr[$StateID]["".$Name.""]	= $ID;
		}
	}

	return $AllRecordArr;
}

function GetAllCityNames($CountryID ='',$StateID='')
{
	global $Prefix;

	$ESQL = array();
	$ExtArg ="";
	if($CountryID != '')
	{
		$ExtArg = " AND countryid =:countryid";
		$ESQL['countryid'] = (int)$CountryID; 
	}
	if($StateID != '')
	{
		$ExtArg = " AND stateid =:stateid";
		$ESQL['stateid'] = (int)$StateID; 
	}
	$AllRecordArr	= array();
	
	$SQL	= "SELECT * FROM ".$Prefix."cities WHERE 1=1 $ExtArg ORDER BY name ASC";
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	if($Num > 0)
	{
		while($Rows = pdo_fetch_assoc($Query))
		{
			$ID		= trim($Rows['id']);
			$Name	= trim($Rows['name']);

			$AllRecordArr[$ID]	= $Name;
		}
	}
	return $AllRecordArr;
}

function GetCustomerCode()
{
	global $Prefix;

	$CountSql	= "SELECT * FROM ".$Prefix."customers WHERE customerid ORDER BY customerid DESC LIMIT 1";
	$CountEsql	= array();

	$CountQuery	= pdo_query($CountSql,$CountEsql);
	$CountNum	= pdo_num_rows($CountQuery);
	
	if($CountNum > 0)
	{
		$CustomerRow	= pdo_fetch_assoc($CountQuery);
		$CustomerCode	= $CustomerRow['customerid'];
		$CustomerCode	= $CustomerCode + 1;
	}
	else
	{
		$CustomerCode	= "1001";
	}

	return $CustomerCode;
}
function ClientInventoryPricing($clientid, $year, $month)
{
	global $Prefix;

	$RecordSetArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
	year		=:year 
	AND
	month		=:month 
	AND
	clientid	=:clientid";

	$Esql	= array(
		"year"			=>(int)$year,
		"month"			=>(int)$month,
		"clientid"		=>(int)$clientid
	);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$itemid			= $rows['inventoryid'];
			$pricingtype	= $rows['pricingtype'];
			$days			= (int)$rows['days'];
			$price			= ($rows['price']*100)/100;

			if($days > 0)
			{
			}
			else
			{
				$days	= "";
			}
			if($price > 0)
			{
			}
			else
			{
				$price	= "";
			}

			$RecordSetArr[$itemid]['days']			= $days;
			$RecordSetArr[$itemid]['price']			= $price;
			$RecordSetArr[$itemid]['pricingtype']	= (int)$pricingtype;
		}
	}

	return $RecordSetArr;
}
function ClientInventoryPricingByDate($clientid, $year, $month)
{
	global $Prefix;

	$RecordSetArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."inventory_date_price_linker WHERE 
	year		=:year 
	AND
	month		=:month 
	AND
	clientid	=:clientid";

	$Esql	= array(
		"year"			=>(int)$year,
		"month"			=>(int)$month,
		"clientid"		=>(int)$clientid
	);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$itemid			= $rows['inventoryid'];
			$pricingtype	= $rows['pricingtype'];
			$date			= (int)$rows['date'];
			$price			= ($rows['price']*100)/100;

			if($date < 1)
			{
				$date	= "";
			}

			if($price > 0)
			{
			}
			else
			{
				$price	= "";
			}

			$RecordSetArr[$itemid][$date]['date']	= $date;
			$RecordSetArr[$itemid][$date]['price']	= $price;
		}
	}

	return $RecordSetArr;
}
function GetAllLine($clientid)
{
	global $Prefix;

	$RecordListArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."line WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];

			$RecordListArr[$id]['id']	= $id;
			$RecordListArr[$id]['name']	= $name;

		}
	}

	return $RecordListArr;
}
function GetAllSubLine($clientid)
{
	global $Prefix;

	$RecordListArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."subline WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
			$phone		= $rows['phone'];

			$RecordListArr[$id]['id']	= $id;
			$RecordListArr[$id]['name']	= $name;

		}
	}

	return $RecordListArr;
}
function GetAllLineman($clientid)
{
	global $Prefix;

	$RecordListArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];

			$RecordListArr[$id]['id']	= $id;
			$RecordListArr[$id]['name']	= $name;

		}
	}

	return $RecordListArr;
}
function GetAllHawker($clientid)
{
	global $Prefix;

	$RecordListArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];

			$RecordListArr[$id]['id']	= $id;
			$RecordListArr[$id]['name']	= $name;

		}
	}

	return $RecordListArr;
}
function ShortMonthName($Number)
{
	switch($Number)
	{
		case "01" : $Name = "Jan"; break;
		case "02" : $Name = "Feb"; break;
		case "03" : $Name = "Mar"; break;
		case "04" : $Name = "Apr"; break;
		case "05" : $Name = "May"; break;
		case "06" : $Name = "Jun"; break;
		case "07" : $Name = "Jul"; break;
		case "08" : $Name = "Aug"; break;
		case "09" : $Name = "Sept"; break;
		case "10" : $Name = "Oct"; break;
		case "11" : $Name = "Nov"; break;
		case "12" : $Name = "Dec"; break;

		default : return;break;
	}
	return $Name;
}
function FUllMonthName($Number)
{
	switch($Number)
	{
		case "1" : 
		case "01" : $Name = "January"; break;
		case "2" : 
		case "02" : $Name = "February"; break;
		case "3" : 
		case "03" : $Name = "March"; break;
		case "4" : 
		case "04" : $Name = "April"; break;
		case "5" : 
		case "05" : $Name = "May"; break;
		case "6" : 
		case "06" : $Name = "June"; break;
		case "7" : 
		case "07" : $Name = "July"; break;
		case "8" : 
		case "08" : $Name = "August"; break;
		case "9" : 
		case "09" : $Name = "September"; break;
		case "10" : $Name = "October"; break;
		case "11" : $Name = "November"; break;
		case "12" : $Name = "December"; break;

		default : return;break;
	}
	return $Name;
}
function GetCustomerID($ID)
{
	global $Prefix;

	$SQL	= "SELECT customerid FROM ".$Prefix."customers WHERE id=:id ORDER BY customerid DESC LIMIT 1 ";
	$ESQL	= array("id"=>(int)$ID);
	$CountQuery	= pdo_query($SQL,$ESQL);
	$CountNum	=	pdo_num_rows($CountQuery);
	$CustCode	= "";
	if($CountNum > 0)
	{
		$Row =   pdo_fetch_assoc($CountQuery);
		$CustCode	=	$Row['customerid'];
	}
return $CustCode;
}
function GetAllStateNames($CountryID ='')
{
	global $Prefix;
	
	$ESQL = array();
	$ExtArg ="";
	if($CountryID != '')
	{
		$ExtArg = " AND countryid =:countryid";
		$ESQL['countryid'] = (int)$CountryID; 
	}

	$AllRecordArr	= array();
	$SQL	= "SELECT * FROM ".$Prefix."states WHERE 1=1 $ExtArg ORDER BY name ASC";
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	if($Num > 0)
	{
		while($Rows = pdo_fetch_assoc($Query))
		{
			$ID		= trim($Rows['id']);
			$Name	= trim($Rows['name']);

			$AllRecordArr[$ID]	= $Name;
		}
	}
	return $AllRecordArr;
}
function GetNextInvoiceID($AgentID)
{
	global $Prefix;
	$SQL	= "SELECT invoiceid FROM ".$Prefix."invoices WHERE invoiceid !=:invoiceid AND clientid=:clientid ORDER BY invoiceid DESC LIMIT 1 ";
	$ESQL	= array("invoiceid"=>"","clientid"=>(int)$AgentID);
	$CountQuery	= pdo_query($SQL,$ESQL);
	$CountNum	=	pdo_num_rows($CountQuery);
	if($CountNum > 0)
	{
		$Row =   pdo_fetch_assoc($CountQuery);
		$InvCode	=	$Row['invoiceid'];
		$InvCode	=	$InvCode + 1;
	}
	else
	{
		$InvCode	=	"1001";
	}

	return $InvCode;
}
function GetActiveSubscriptionByClientID($ClientID,$Month,$Year)
{
	global $Prefix;
	
	/*$CheckSQL		= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND price > :price";*/
	$CheckSQL		= "SELECT linker.*,inv.name as inventoryname,inv.categoryid as categoryid FROM ".$Prefix."client_inventory_linker linker, ".$Prefix."inventory inv WHERE linker.clientid=:clientid AND linker.inventoryid=inv.id AND inv.deletedon <:deletedon ORDER BY inventoryname ASC";
	$CheckESQL		= array("clientid"=>(int)$ClientID,"deletedon"=>1);

	$CheckQuery		=	pdo_query($CheckSQL,$CheckESQL);
	$CheckNum		=   pdo_num_rows($CheckQuery);
	$Arr			=   array();
	if($CheckNum > 0)
	{
		$PricingSQL		= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE clientid=:clientid AND month=:month AND year=:year";
		$PricingESQL	= array("clientid"=>(int)$ClientID,"year"=>(int)$Year,"month"=>(int)$Month);

		$PricingQuery	= pdo_query($PricingSQL,$PricingESQL);

		$PricingNum		= pdo_num_rows($PricingQuery);
		
		$PricingArray	= array();
		if($PricingNum > 0)
		{
			while($PricingRow	= pdo_fetch_assoc($PricingQuery))
			{
				$InventoryID	= $PricingRow['inventoryid'];
				$Price			= $PricingRow['price'];
				$PricingType	= $PricingRow['pricingtype'];
				$Days			= $PricingRow['days'];
				$PricingArray[$InventoryID]['price'] = $Price;
				$PricingArray[$InventoryID]['days'] = $Days;
				$PricingArray[$InventoryID]['pricingtype'] = $PricingType;
				
				$DailyPriceArr = array();
				if($PricingType > 0)
				{
					$PricingSQL2		= "SELECT * FROM ".$Prefix."inventory_date_price_linker WHERE clientid=:clientid AND month=:month AND year=:year AND inventoryid=:inventoryid";
					$PricingESQL2	= array("clientid"=>(int)$ClientID,"year"=>(int)$Year,"month"=>(int)$Month,"inventoryid"=>(int)$InventoryID);

					$PricingQuery2	= pdo_query($PricingSQL2,$PricingESQL2);
					$PricingNum2	= pdo_num_rows($PricingQuery2);

					if($PricingNum2 > 0)
					{
						while($PricingDailyRow	=	pdo_fetch_assoc($PricingQuery2))
						{
							$Date				=	$PricingDailyRow['date'];
							$Price				=	$PricingDailyRow['price'];
							$DailyPriceArr[$Date]= 	$Price;
						}
					}

				}
				$PricingArray[$InventoryID]['dailyprice'] =  $DailyPriceArr;
			}
		}
		$CategoryNameArr		= GetAllCategory();
		while($CheckRow		= pdo_fetch_assoc($CheckQuery))
		{
			$InventoryID 	= $CheckRow['inventoryid'];
			$CategoryID 	= $CheckRow['categoryid'];
			$InventoryName 	= $CheckRow['inventoryname'];
			$Price		 	= $CheckRow['price'];
			if($PricingArray[$InventoryID]['price'] > 0)
			{
				$Arr[$InventoryID]['price']			= $PricingArray[$InventoryID]['price']; 
				$Arr[$InventoryID]['inventoryname']	= $InventoryName; 
				$Arr[$InventoryID]['categoryid']	= $CategoryID; 
				$Arr[$InventoryID]['categoryname']	= $CategoryNameArr[$CategoryID]; 
				$Arr[$InventoryID]['days']			= $PricingArray[$InventoryID]['days']; 
				$Arr[$InventoryID]['dailyprice']	= $PricingArray[$InventoryID]['dailyprice'];
				$Arr[$InventoryID]['pricingtype']	= $PricingArray[$InventoryID]['pricingtype']; 
			}
		}
	}

	return $Arr;
}
function GetAllCategory($ID = "")
{
	global $Prefix;
	$AllRecordArr	= array();

	$Condition	= "";
	$ESQL = array();
	if($ID != "" && $ID > 0)
	{
		$Condition	.= " AND id=:id";
		$ESQL['id'] = (int)$_GET['id'];
	}
	$SQL	= "SELECT * FROM ".$Prefix."category WHERE 1=1 ".$Condition." ORDER BY title ASC";
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	if($Num > 0)
	{
		while($Rows = pdo_fetch_assoc($Query))
		{
			$ID		= trim($Rows['id']);
			$Name	= trim($Rows['title']);

			$AllRecordArr[$ID]	= $Name;
		}
	}
	return $AllRecordArr;
}
function GeneratePaymentLinks($CustomerArr,$Amount,$Notes,$InvoiceID)
{
	return true;
	global $RAZOR_PAY_API_KEY, $RAZOR_PAY_API_SECRET,$Prefix;
	
	$api = new Api($RAZOR_PAY_API_KEY, $RAZOR_PAY_API_SECRET);

	$CustomerName = "Customer #".$CustomerArr["customernumber"];
	$Amount = $Amount * 100; //Amount should be in paisa or 
	$link  = $api->invoice->create(
					array(
					'customer' => 
								array(
									"email"=> $CustomerArr['email'],
									"contact"=> $CustomerArr['phone']
									),
					  "type"=> "link",
					  "view_less"=> 1,
					  "amount"=> $Amount,
					  "currency"=> "INR",
					  "description"=> $Notes,
					  "sms_notify"=> 1,
					  "email_notify"=> 1
					)
				);
	ob_start();
	print_r($link);
	$response = ob_get_contents();
	ob_end_clean();
	ob_flush();

	$TransactionInvoiceID		= $link["id"];
	$PaymentLink	= $link["short_url"];
	$LinkStatus		= $link["status"];

	$PaymentLinkStatus = 1;
	if(trim($PaymentLink) =="")
	{
		$PaymentLinkStatus = 0;
	}
	
	$InsertSQL = "INSERT INTO ".$Prefix."inv_payment_req_log SET
			invoiceid =:invoiceid,
			razorpayinoviceid =:razorpayinoviceid,
			paylink =:paylink,
			response =:response,
			status =:status,
			createdon =:createdon
	";
	$InsertESQL = array(
			"invoiceid"	=> $InvoiceID,
			"razorpayinoviceid"=>$TransactionInvoiceID,
			"paylink"	=>$PaymentLink,
			"response"	=> $response,
			"status"	=>$LinkStatus,
			"createdon" =>time()
			);

	pdo_query($InsertSQL,$InsertESQL);

	if($PaymentLinkStatus > 0)
	{
		$UpdateSQL = "UPDATE ".$Prefix."invoices SET paylink=:paylink,razorpayid=:razorpayid WHERE id=:id" ;
		$UpdateESQL = array("paylink"=>$PaymentLink,"razorpayid"=>$TransactionInvoiceID,"id"=>(int)$InvoiceID);
		pdo_query($UpdateSQL,$UpdateESQL);
	}
}
function IsClientHoliday($Date,$ClientID,$InventoryID='')
{
	global $Prefix;
	$ExtArg = '';
	$EsqlArr = array();
	if($InventoryID > 0)
	{
		$ExtArg .= " || ((inventorytype=:inventorytype3 AND inventoryid=:inventoryid) || (inventorytype < :inventorytype4))";
		$EsqlArr['customertype3']='1';
		$EsqlArr['inventorytype3']='1';
		$EsqlArr['inventorytype4']='1';
		$EsqlArr['inventoryid']=(int)$InventoryID;
	}
	$SQL = "SELECT COUNT(*) AS C FROM ".$Prefix."holidays WHERE (:date1 BETWEEN startdate AND enddate) AND (customertype =:customertype $ExtArg) ";
	$EsqlArr['date1'] = $Date;
	$EsqlArr['customertype'] = 0;
	$query 	= pdo_query($SQL,$EsqlArr);

	$countrow = pdo_fetch_assoc($query);

	return $countrow['C'];

}
function IsHoliday($Date,$CustomerID='',$InventoryID='')
{
	global $Prefix;
	$ExtArg = '';
	$EsqlArr = array();
	if($CustomerID > 0)
	{
		$ExtArg = " || (customertype =:customertype2 AND customerid=:customerid)";
		$EsqlArr = array('customertype2'=>'1','customerid'=>$CustomerID);
	}
	if($InventoryID > 0)
	{
		$ExtArg .= " || (customertype <:customertype3 AND inventorytype=:inventorytype3 AND inventoryid=:inventoryid)";
		$EsqlArr['customertype3']='1';
		$EsqlArr['inventorytype3']='1';
		$EsqlArr['inventoryid']=(int)$InventoryID;
	}
	$SQL = "SELECT COUNT(*) AS C FROM ".$Prefix."holidays WHERE (:date1 BETWEEN startdate AND enddate) AND ((customertype =:customertype AND inventorytype=:inventorytype) $ExtArg) ";
	$EsqlArr['date1'] = $Date;
	$EsqlArr['customertype'] = 0;
	$EsqlArr['inventorytype'] = 0;
	$query 	= pdo_query($SQL,$EsqlArr);

	$countrow = pdo_fetch_assoc($query);

	return $countrow['C'];

}

function GetAllArea($clientid)
{
	global $Prefix;

	$RecordListArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."area WHERE clientid=:clientid AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];

			$RecordListArr[$id]['id']	= $id;
			$RecordListArr[$id]['name']	= $name;

		}
	}

	return $RecordListArr;
}
function CreateSubscriptionLog($CustomerID,$InventoryID,$Status,$frequency,$subscriptiondate,$days,$daysname,$unsubscribedate,$quantity)
{
	global $Prefix;

	$SQL	= "INSERT INTO ".$Prefix."subscriptions_log SET
	customerid			=:customerid,	 	
	inventoryid			=:inventoryid,
	status				=:status,
	frequency			=:frequency,
	quantity			=:quantity,
	subscriptiondate	=:subscriptiondate,
	days				=:days,
	daysname			=:daysname,
	createdon			=:createdon,
	unsubscribedate		=:unsubscribedate";

	$ESQL		= array(
		"customerid" 		=>(int)$CustomerID,
		"inventoryid" 		=>(int)$InventoryID,
		"status" 			=>(int)$Status,
		"quantity" 			=>(int)$quantity,
		"frequency"			=>$frequency,
		"subscriptiondate"	=>$subscriptiondate,
		"days"				=>$days,
		"daysname"			=>$daysname,
		"createdon"			=>time(),
		"unsubscribedate"	=>$unsubscribedate
	);
	if($unsubscribedate > 0)
	{
		$SQL	= "UPDATE ".$Prefix."subscriptions_log SET
		unsubscribedate		=:unsubscribedate
		WHERE
		customerid			=:customerid AND	 	
		inventoryid			=:inventoryid AND
		status				=:status AND
		unsubscribedate		<:unsubscribedate2
		";
	
		$ESQL		= array(
			"customerid" 		=>(int)$CustomerID,
			"inventoryid" 		=>(int)$InventoryID,
			"status" 			=>1,
			"unsubscribedate"	=>(int)$unsubscribedate,
			"unsubscribedate2"	=>1,
		);	
	}
	
	$Query = pdo_query($SQL,$ESQL);

	if($Query)
	{
		return true;
	}
	else
	{
		return false;
	}
}
function GetInventoryLog($CustomerID,$InventoryID)
{
	global $Prefix;
	$SQL	= "SELECT * FROM ".$Prefix."subscriptions_log WHERE customerid=:customerid AND inventoryid=:inventoryid ORDER BY createdon DESC";
	$ESQL	= array("customerid"=>(int)$CustomerID,'inventoryid'=>(int)$InventoryID);
	
	$Query  = pdo_query($SQL,$ESQL);
	$Num    = pdo_num_rows($Query);
	$Arr	=  array();
	if($Num > 0)
	{
		$Index = 0;
		while($row = pdo_fetch_assoc($Query))
		{
			$InventoryID = $row['inventoryid'];
			$CreatedOn   = $row['createdon'];
			$Status      = $row['status'];
			
			$StatusText	 = 'Added';

			$Arr[$Index]['id'] = $row['id']; 
			$Arr[$Index]['date'] = date("d-M-Y",$CreatedOn); 
			
			if($Status < 1)
			{
				$StatusText = "Removed";	
			}
		$Index++;
		}
		$Arr[$Index]['status'] = $StatusText; 
	}
	return $Arr;
}
function GetCustomerDetail($id)
{
	global $Prefix;

	$CustomerInfoArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."customers WHERE id=:id";
	$Esql	= array("id"=>(int)$id);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);
	
	if($Num > 0)
	{
		$rows	= pdo_fetch_assoc($Query);

		$name2		= "";

		$id			= $rows['id'];
		$name		= $rows['name'];
		$phone		= $rows['phone'];
		$email		= $rows['email'];
		$createdon	= $rows['createdon'];
		$stateid	= $rows['stateid'];
		$cityid		= $rows['cityid'];
		$address1	= $rows['address1'];
		$customerid	= $rows['customerid'];

		if(trim($name) == "")
		{
			$name	= $firstname." ".$lastname;
		}
		$name2	= "#".$customerid." ".$name;

		$CustomerInfoArr['name'] = $name2;
	}

	return $CustomerInfoArr;
}
function GetInventoryFrequency()
{
	global $Prefix;
	$SQL	= "SELECT * FROM ".$Prefix."inventory ORDER BY id ASC";
	$ESQL	= array();
	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	$Arr	= array();
	if($Num > 0)
	{
		while($Row = pdo_fetch_assoc($Query))
		{
			$InventoryID		= $Row['id'];
			$Arr[$InventoryID] 	= $Row['frequency'];	 
		}
	}

	return $Arr;
}
function GetOpeningBalance($clientid, $customerid, $startdate, $enddate)
{
	global $Prefix;

	$openingbalance	= 0;

	$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$CustESQL   = array("id"=>(int)$customerid,"clientid"=>(int)$clientid,"deletedon"=>1);

	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	if($CustNum > 0)
	{
		$CustRow			= pdo_fetch_assoc($CustQuery);
		$Createdon			= $CustRow['createdon'];

		$openingbalance		= $CustRow['openingbalance'];

		$DateArr		= array();
		$ItemNameArr	= array();
		$AmountDueArr	= array();
		$AmountPaidArr	= array();

		if($OpeningBalance > 0)
		{
			$DateArr[]			= $Createdon;
			$ItemNameArr[]		= "Opening Balance";
			$AmountDueArr[]		= 0;
			$AmountPaidArr[]	= 0;
		}

		$InvoiceSQL		= "SELECT * FROM ".$Prefix."invoices WHERE customerid=:customerid AND invoicedate BETWEEN :startdate AND :enddate AND deletedon <:deletedon ORDER BY invoicedate";
		$InvoiceESQL	= array("customerid"=>(int)$customerid,"startdate"=>$startdate,"enddate"=>$enddate,'deletedon'=>1);

		$InvoiceQuery	= pdo_query($InvoiceSQL,$InvoiceESQL);
		$InvoiceNum		= pdo_num_rows($InvoiceQuery);

		if($InvoiceNum	> 0)
		{
			while($InvoiceRow = pdo_fetch_assoc($InvoiceQuery))
			{
				$openingbalance	-= $InvoiceRow['finalamount'];
			}
		}

		$PaySQL		= "SELECT * FROM ".$Prefix."customer_payments WHERE customerid=:customerid AND paymentdate BETWEEN :startdate AND :enddate ORDER BY createdon ASC";
		$PayESQL	= array("customerid"=>(int)$customerid,"startdate"=>$startdate,"enddate"=>$enddate);
		
		$PayQuery	= pdo_query($PaySQL,$PayESQL);
		$PayNum		= pdo_num_rows($PayQuery);
		
		if($PayNum	> 0)
		{
			while($PayRow = pdo_fetch_assoc($PayQuery))
			{
				$openingbalance	+= $PayRow['amount'];
			}
		}
	}

	return $openingbalance;
}
function GetAllMagazineCategoryID()
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."category WHERE type=:type AND deletedon < :deletedon";
	$Esql	= array('type'=>0,'deletedon'=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id	= $rows['id'];

			$RecArr[]	= $id;
		}
	}

	$RecArr = array_filter(array_unique($RecArr));

	return $RecArr;
}
function GetAllNewspaperCategoryID()
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."category WHERE type=:type AND deletedon < :deletedon";
	$Esql	= array('type'=>1,'deletedon'=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$id	= $rows['id'];

			$RecArr[]	= $id;
		}
	}

	$RecArr = array_filter(array_unique($RecArr));

	return $RecArr;
}
function GetAllAssignedAreaByAreaManager($clientid, $id = 0)
{
	global $Prefix;

	$Arr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."assigned_area_linker WHERE managerid=:managerid AND clientid=:clientid";
	$Esql	= array("managerid"=>(int)$id,"clientid"=>$clientid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$areaid	= $rows['areaid'];

			$Arr[]	= $areaid;
		}
	}

	return $Arr;
}
function GetAccessToken()
{
	/*$headers	= apache_request_headers();
	return $headers['Authorization'];*/

	$auth_token = null;

	if($_SERVER['IsLocal'] == 'Yes')
	{
		$headers	= apache_request_headers();
		return $headers['Authorization'];
	}
	else
	{
		if(isset($_SERVER['HTTP_AUTHORIZATION']))
		{
			$auth_token = $_SERVER['HTTP_AUTHORIZATION'];
		}
		else if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
		{
			$auth_token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
		}
	}

	return $auth_token;
}
function GetAllDroppingPoint($clientid)
{
	global $Prefix;

	$Arr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."dropping_point WHERE clientid=:clientid";
	$Esql	= array("clientid"=>(int)$clientid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$name	= $rows['name'];

			$Arr[$rows['id']]['name']	= $name;
		}
	}
	return $Arr;
}
function GetHoliday($clientid)
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND customertype=:customertype AND deletedon < :deletedon";
	$Esql	= array("clientid"=>(int)$clientid,"customertype"=>0,"deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	$index	= 0;

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$RecArr[$index]['inventoryid']	= (int)$rows['inventoryid'];
			$RecArr[$index]['startdate']	= (int)$rows['startdate'];
			$RecArr[$index]['enddate']		= (int)$rows['enddate'];
			$RecArr[$index]['reason']		= $rows['reason'];

			$index++;
		}
	}

	return $RecArr;
}
function GetAllAssignedAreaAndLineByLineman($clientid, $linemanid)
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."lineman WHERE clientid=:clientid AND id=:id";
	$Esql	= array("clientid"=>(int)$clientid,"id"=>(int)$linemanid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$rows	= pdo_fetch_assoc($Query);

		$areaid		= $rows['areaid'];
		$LineIdsArr	= @explode("::",$rows['lineids']);
		$LineIdsArr	= @array_filter(@array_unique($LineIdsArr));
		$LineIds	= @implode(",",$LineIdsArr);

		$RecArr['areaid']	= (int)$areaid;
		$RecArr['lineids']	= $LineIds;
	}

	return $RecArr;
}
function GetAllAssignedAreaAndLineByHawker($clientid, $hawkerid)
{
	global $Prefix;

	$RecArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."hawker WHERE clientid=:clientid AND id=:id";
	$Esql	= array("clientid"=>(int)$clientid,"id"=>(int)$hawkerid);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$rows	= pdo_fetch_assoc($Query);

		$areaid		= $rows['areaid'];
		$LineIdsArr	= @explode("::",$rows['lineids']);
		$LineIdsArr	= @array_filter(@array_unique($LineIdsArr));
		$LineIds	= @implode(",",$LineIdsArr);

		$RecArr['areaid']	= (int)$areaid;
		$RecArr['lineids']	= $LineIds;
	}

	return $RecArr;
}
function syncInventoryPriceTotal($clientid, $inventoryid, $month, $year, $pricingtype)
{
	global $Prefix;

	$createdon		= time();

	/* $pricingtype, 0 - day base, 1 - date base */

	$TotalDaysSql	= "SELECT COUNT(*) as C FROM ".$Prefix."inventory_date_price_linker WHERE 
	clientid	=:clientid 
	AND 
	inventoryid	=:inventoryid 
	AND 
	month		=:month 
	AND 
	year		=:year 
	AND 
	price		>:price";

	$TotalDaysEsql	= array(
		"clientid"		=>(int)$clientid,
		"inventoryid"	=>(int)$inventoryid,
		"month"			=>$month,
		"year"			=>$year,
		"price"			=>0
	);

	$TotalDaysQuery	= pdo_query($TotalDaysSql,$TotalDaysEsql);
	$TotalDaysRows	= pdo_fetch_assoc($TotalDaysQuery);

	$TotalDays	= $TotalDaysRows['C'];

	$TotalCostSql	= "SELECT sum(price) as pricetotal FROM ".$Prefix."inventory_date_price_linker WHERE 
	clientid	=:clientid 
	AND 
	inventoryid	=:inventoryid 
	AND 
	month		=:month 
	AND 
	year		=:year 
	AND 
	price		>:price";

	$TotalCostEsql	= array(
		"clientid"		=>(int)$clientid,
		"inventoryid"	=>(int)$inventoryid,
		"month"			=>$month,
		"year"			=>$year,
		"price"			=>0
	);

	$TotalCostQuery	= pdo_query($TotalCostSql,$TotalCostEsql);
	$TotalCostRows	= pdo_fetch_assoc($TotalCostQuery);

	$TotalCost	= $TotalCostRows['pricetotal'];

	$InvSql		= "SELECT * FROM ".$Prefix."inventory WHERE id=:id";
	$InvEsql	= array("id"=>(int)$inventoryid);

	$InvQuery	= pdo_query($InvSql,$InvEsql);
	$InvRows	= pdo_fetch_assoc($InvQuery);

	$categoryid	= $InvRows['categoryid'];
	$name		= $InvRows['name'];


	$CheckSql	= "SELECT * FROM ".$Prefix."inventory_days_price_linker WHERE 
	year		=:year 
	AND
	month		=:month 
	AND
	clientid	=:clientid 
	AND
	inventoryid	=:inventoryid";

	$CheckEsql	= array(
		"year"			=>(int)$year,
		"month"			=>(int)$month,
		"clientid"		=>(int)$clientid,
		"inventoryid"	=>(int)$inventoryid
	);

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		$checkrows			= pdo_fetch_assoc($CheckQuery);
		$inventorylinkerid	= $checkrows['id'];

		$Sql	= "UPDATE ".$Prefix."inventory_days_price_linker SET 
		year		=:year,
		month		=:month,
		clientid	=:clientid,
		inventoryid	=:inventoryid,
		days		=:days,
		price		=:price,
		pricingtype	=:pricingtype
		WHERE
		id			=:id";

		$Esql	= array(
			"year"			=>(int)$year,
			"month"			=>(int)$month,
			"clientid"		=>(int)$clientid,
			"inventoryid"	=>(int)$inventoryid,
			"days"			=>(int)$TotalDays,
			"price"			=>(float)$TotalCost,
			"pricingtype"	=>(int)$pricingtype,
			"id"			=>(int)$inventorylinkerid
		);

		$Query	= pdo_query($Sql,$Esql);
	}
	else
	{
		$Sql	= "INSERT INTO ".$Prefix."inventory_days_price_linker SET 
		year		=:year,
		month		=:month,
		clientid	=:clientid,
		inventoryid	=:inventoryid,
		categoryid	=:categoryid,
		name		=:name,
		days		=:days,
		price		=:price,
		pricingtype	=:pricingtype,
		createdon	=:createdon";

		$Esql	= array(
			"year"			=>(int)$year,
			"month"			=>(int)$month,
			"clientid"		=>(int)$clientid,
			"inventoryid"	=>(int)$inventoryid,
			"categoryid"	=>(int)$categoryid,
			"name"			=>$name,
			"days"			=>(int)$TotalDays,
			"price"			=>(float)$TotalCost,
			"pricingtype"	=>(int)$pricingtype,
			"createdon"		=>$createdon
		);

		$Query	= pdo_query($Sql,$Esql);
	}
}
function SendMessageViaGo2Marketing($phonenumberwithurl,$message,$senderid,$sendertype='1',$languagetype='0',$templateid="",$dlttemplateid='')
{
	global $Go2MarketingAuthToken,$DefaultSenderID;

	if($_SERVER['IsLocal'] == 'Yes')
	{
		$ResponseArr['Status']	= 'Success';
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
function GenerateOTP($Passwordlen)
{
	$Source[0] = "0123456789";

	$Min = 0;
	$i=0;
	while($i < $Passwordlen)
	{
		$Max = strlen($Source[$i % @count($Source)])-1;
		$Rand = rand($Min,$Max);
		$Password .= substr($Source[$i % @count($Source)],$Rand,1);
		$i++;
	}
	return $Password;
}
function validate_mobile($mobile)
{
	return preg_match('/^[0-9]{10}+$/', $mobile);
}
function GetAllDroppingPointByAreaManager($clientid, $id = 0)
{
	global $Prefix;

	$Arr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."assigned_dropping_point_linker WHERE managerid=:managerid AND clientid=:clientid";
	$Esql	= array("managerid"=>(int)$id,"clientid"=>$clientid);
	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		while($rows = pdo_fetch_assoc($Query))
		{
			$droppingpointid	= $rows['droppingpointid'];

			$Arr[]	= $droppingpointid;
		}
	}

	return $Arr;
}
function GetPreviousBalanceTillDate($CustomerID,$InvoiceYear,$InvoiceMonth)
{
	global $Prefix;
	$OpeningBalance = 0;

	$GrandTotal = 0;

	$SQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND deletedon < :deletedon ORDER BY id ASC";
	$ESQL	= array('id'=>(int)$CustomerID,'deletedon'=>1);
	$CustQuery = pdo_query($SQL,$ESQL);
	$CustRow	= pdo_fetch_assoc($CustQuery);
	$OpeningBalance = (float)$CustRow['openingbalance'];
	
	$GrandTotal	= $OpeningBalance;

	$SQL	= "SELECT finalamount,invoicemonth FROM ".$Prefix."invoices WHERE customerid=:customerid AND invoiceyear<=:invoiceyear AND deletedon < :deletedon ORDER BY id ASC";
	$ESQL	= array("invoiceyear"=>(int)$InvoiceYear,'customerid'=>(int)$CustomerID,'deletedon'=>1);

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);	
	if($Num > 0)
	{
		while($rowinvoice = pdo_fetch_assoc($Query))
		{
			$CheckInvoiceMonth = $rowinvoice['invoicemonth'];
			$FinalPayment = $rowinvoice['finalamount'];
			
			if($CheckInvoiceMonth < $InvoiceMonth)
			{
				$GrandTotal	+= $FinalPayment;
			}
		}
	}
	$PaymentCheckDate = strtotime(date($InvoiceMonth."/01"."/".$InvoiceYear));
	$SQL	= "SELECT SUM(amount) AS s FROM ".$Prefix."customer_payments WHERE customerid=:customerid AND paymentdate <=:paymentdate ORDER BY id ASC";
	$ESQL	= array('customerid'=>(int)$CustomerID,'createdon'=>(int)$PaymentCheckDate);

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);	
	if($Num > 0)
	{
		$rowpayment = pdo_fetch_assoc($Query);
		$FinalPayment = $rowpayment['s'];
		$GrandTotal	-= $FinalPayment;
	}

	return $GrandTotal;
}
function getOutstandingBalanceByCustomer($clientid, $id)
{
	global $Prefix;

	$outstandingbalance	= 0;

	$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND clientid=:clientid AND deletedon < :deletedon";
	$CustESQL   = array("id"=>(int)$id,"clientid"=>(int)$clientid,"deletedon"=>1);

	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	if($CustNum > 0)
	{
		$CustRow			= pdo_fetch_assoc($CustQuery);

		$OpeningBalance		= $CustRow['openingbalance'];

		$outstandingbalance	+= $OpeningBalance;

		$InvoiceSQL		= "SELECT SUM(finalamount) AS invoicetotal FROM ".$Prefix."invoices WHERE customerid=:customerid AND deletedon < :deletedon";
		$InvoiceESQL	= array("customerid"=>(int)$id,'deletedon'=>1);

		$InvoiceQuery	= pdo_query($InvoiceSQL,$InvoiceESQL);
		$InvoiceRow		= pdo_fetch_assoc($InvoiceQuery);

		$invoicetotal	= $InvoiceRow['invoicetotal'];

		$outstandingbalance	+= $invoicetotal;

		$PaySQL		= "SELECT SUM(amount) AS paidtotal, SUM(discount) AS discounttotal, SUM(coupon) AS coupontotal FROM ".$Prefix."customer_payments WHERE customerid=:customerid AND deletedon < :deletedon ORDER BY createdon ASC";
		$PayESQL	= array("customerid"=>(int)$id,"deletedon"=>1);

		$PayQuery	= pdo_query($PaySQL,$PayESQL);
		$PayRow		= pdo_fetch_assoc($PayQuery);

		$paidtotal		= $PayRow['paidtotal'];
		$discounttotal	= $PayRow['discounttotal'];
		$coupontotal	= $PayRow['coupontotal'];

		$outstandingbalance	-= $paidtotal+$discounttotal+$coupontotal;
	}

	return $outstandingbalance;
}
function updateCustomerOutstandingBalance($clientid, $id)
{
	global $Prefix;

	$outstandingbalance	= 0;

	$condition	= "";
	$CustESQL	= array("clientid"=>(int)$clientid,"deletedon"=>1);

	if($id > 0)
	{
		$condition		.= " AND id=:id";
		$CustESQL['id']	= (int)$id;
	}

	$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE 1 ".$condition." AND clientid=:clientid AND deletedon < :deletedon ORDER BY sequence ASC, customerid ASC";

	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	if($CustNum > 0)
	{
		while($CustRow	= pdo_fetch_assoc($CustQuery))
		{
			$id					= $CustRow['id'];
			$outstandingbalance	= getOutstandingBalanceByCustomer($clientid, $id);

			$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=:outstandingbalance WHERE id=:id";
			$UpdateEsql	= array("outstandingbalance"=>$outstandingbalance,"id"=>(int)$id);

			$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);
		}
	}
}
function Paging($page = 1, $perpage = 20, $totalrecord)
{
	$pageListArr = array();

	$links		= 5; $pagelistindex = 0;
	$last		= ceil($totalrecord / $perpage);

	$start	= (((int)$page - $links ) > 0 ) ? (int)$page - $links : 1;
    $end	= (((int)$page + $links ) < $last ) ? (int)$page + $links : $last;

	if($page > 1)
	{
		$pageListArr[$pagelistindex]['page']	= (int)$page - 1;
		$pageListArr[$pagelistindex]['name']	= "Previous Page";
		$pageListArr[$pagelistindex]['index']	= $pagelistindex+1;

		$pagelistindex++;
	}

	if($start > 1)
	{
		$pageListArr[$pagelistindex]['page']	= 1;
		$pageListArr[$pagelistindex]['name']	= "First Page";
		$pageListArr[$pagelistindex]['index']	= $pagelistindex+1;

		$pagelistindex++;
	}

	for($pageloop = $start; $pageloop <= $end; $pageloop++)
	{
		$pageListArr[$pagelistindex]['page']	= $pageloop;
		$pageListArr[$pagelistindex]['name']	= "Page ".$pageloop;
		$pageListArr[$pagelistindex]['index']	= $pagelistindex+1;

		$pagelistindex++;
	}

	if($end < $last)
	{
		$pageListArr[$pagelistindex]['page']	= $last;
		$pageListArr[$pagelistindex]['name']	= "Last Page";
		$pageListArr[$pagelistindex]['index']	= $pagelistindex+1;

		$pagelistindex++;
	}

	return $pageListArr;
}
function getPurchasePrice($clientid, $id, $purchasedate)
{
	global $Prefix;

	$date	= date("d",$purchasedate);
	$month	= date("m",$purchasedate);
	$year	= date("Y",$purchasedate);

	$purchaserate	= "";

	$checksql2	= "SELECT price FROM ".$Prefix."inventory_date_price_linker WHERE 
	year		=:year 
	and
	month		=:month 
	and
	clientid	=:clientid 
	and
	inventoryid	=:inventoryid
	and
	date		=:date";

	$checkesql2	= array(
		"clientid"		=>(int)$_POST['clientid'],
		"inventoryid"	=>(int)$id,
		'month'			=>(int)$month,
		'year'			=>(int)$year,
		'date'			=>(int)$date	
	);

	$checkquery	= pdo_query($checksql2,$checkesql2);
	$checknum	= pdo_num_rows($checkquery);

	if($checknum > 0)
	{
		$checkrows			= pdo_fetch_assoc($checkquery);
		$purchaserate		= $checkrows['price'];
	}

	return $purchaserate;
}
function GetNextPaymentID($AgentID)
{
	global $Prefix;
	$SQL	= "SELECT paymentid FROM ".$Prefix."customer_payments WHERE paymentid !=:paymentid AND clientid=:clientid ORDER BY paymentid DESC LIMIT 1";
	$ESQL	= array("paymentid"=>"","clientid"=>(int)$AgentID);
	$CountQuery	= pdo_query($SQL,$ESQL);
	$CountNum	=	pdo_num_rows($CountQuery);
	if($CountNum > 0)
	{
		$Row		= pdo_fetch_assoc($CountQuery);
		$PayCode	= $Row['paymentid'];
		$PayCode	= $PayCode + 1;
	}
	else
	{
		$PayCode	= "1001";
	}

	return $PayCode;
}
function GenerateCustomerAccountLog($ClientID,$CustomerID,$AreaID,$LineID,$HawkerID,$Amount,$LogDate,$Narration,$PaymentType,$PaymentID='',$InvoiceID='',$InvoiceMonth='',$InvoiceYear='')
{
 	global $Prefix;
	
	if($LogDate < 1)
	{
		$CheckSQL	= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND customerid=:customerid AND logdate <:logdate";
		$CheckESQL	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID,'logdate'=>1);
	}
	else if($PaymentID > 0)
	{
		$Amount = "-".$Amount;
		$CheckSQL	= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND customerid=:customerid AND paymentid=:paymentid AND paymenttype=:paymenttype";
		$CheckESQL	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID,"paymentid"=>(int)$PaymentID,'paymenttype'=>$PaymentType);
	}
	else if($InvoiceID > 0)
	{
		$CheckSQL	= "SELECT * FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND customerid=:customerid AND invoiceid=:invoiceid";
		$CheckESQL	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID,"invoiceid"=>(int)$InvoiceID);
	}

	$UpdateRecordID = 0;

	$CheckQuery = pdo_query($CheckSQL,$CheckESQL);
	
	$CheckNum	= pdo_num_rows($CheckQuery);
	if($CheckNum > 0)
	{
		$CheckRow	= pdo_fetch_assoc($CheckQuery);
		$UpdateRecordID	= $CheckRow['id'];
	}
	

	if($LogDate < 1)
	{
		$LineTotal = $Amount;
		$presql		= "SELECT linetotal,amount FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND customerid=:customerid AND logdate < :logdate ORDER BY logdate DESC,id DESC LIMIT 1";
		$preesql	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID,'logdate'=>1);
		$prequery	= pdo_query($presql,$preesql);
		$prenum		= pdo_num_rows($prequery);
		$prelinetotal	= 0;
		$preamount	= 0;

		if($prenum > 0)
		{
			$prerow	= pdo_fetch_assoc($prequery);
			$prelinetotal	= $prerow['linetotal'];
			$preamount		= $prerow['amount'];
	
			//$LineTotal	= $prelinetotal + $Amount; 
		}

	}
	else
	{
		$extrarg	= '';
		$extarr		= array();
		if($UpdateRecordID > 0)
		{
			$extrarg	= " AND id < :id";
			$extarr		= array("id"=>(int)$UpdateRecordID);
		}
		$presql		= "SELECT linetotal,amount FROM ".$Prefix."cust_accounts WHERE clientid=:clientid AND customerid=:customerid AND logdate <= :logdate $extrarg ORDER BY logdate DESC,id ASC LIMIT 1";
		$preesql	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID,'logdate'=>(int)$LogDate);
		$esql3		= array_merge($extarr,$preesql);
		$prequery	= pdo_query($presql,$esql3);
		$prenum		= pdo_num_rows($prequery);
		$prelinetotal	= 0;
		$preamount	= 0;
		if($prenum > 0)
		{
			$prerow	= pdo_fetch_assoc($prequery);
			$prelinetotal	= $prerow['linetotal'];
			$preamount		= $prerow['amount'];
		}
		$LineTotal	= $prelinetotal + $Amount; 
	}
		
	if($UpdateRecordID)
	{
 		$sqlaccount = "UPDATE ".$Prefix."cust_accounts SET 
		areaid				= :areaid,
		lineid				= :lineid,
		hawkerid			= :hawkerid,
		amount				= :amount,
		logdate				= :logdate,
		narration			= :narration,
		paymentid			= :paymentid,
		invoiceid			= :invoiceid,
		invoicemonth		= :invoicemonth,
		invoiceyear			= :invoiceyear,
		paymenttype			= :paymenttype,
		linetotal			= :linetotal
		WHERE
		id					= :id
		";
		
		$sqleaccount	= array (	
		"areaid"			=>(int)$AreaID,
		"lineid"			=>(int)$LineID,
		"hawkerid"			=>(int)$HawkerID,
		"amount"			=>(float)$Amount,
		"logdate"			=>(int)$LogDate,
		"narration"			=>$Narration,
		"paymentid"			=>(int)$PaymentID,
		"invoiceid"			=>(int)$InvoiceID,
		"invoicemonth"		=>(int)$InvoiceMonth,
		"invoiceyear"		=>(int)$InvoiceYear,
		"linetotal"			=>(int)$LineTotal,
		"paymenttype"		=>$PaymentType,
		"id"				=>(int)$UpdateRecordID
		);
	}
	else
	{
		$sqlaccount = "INSERT INTO ".$Prefix."cust_accounts SET 
		clientid			= :clientid,
		customerid			= :customerid,
		areaid				= :areaid,
		lineid				= :lineid,
		hawkerid			= :hawkerid,
		amount				= :amount,
		logdate				= :logdate,
		narration			= :narration,
		paymentid			= :paymentid,
		linetotal			= :linetotal,
		paymenttype			= :paymenttype,
		invoiceid			= :invoiceid,
		invoicemonth		= :invoicemonth,
		invoiceyear			= :invoiceyear
		";
		
		$sqleaccount	= array (	
		"clientid"			=>(int)$ClientID,
		"customerid"		=>(int)$CustomerID,
		"areaid"			=>(int)$AreaID,
		"lineid"			=>(int)$LineID,
		"hawkerid"			=>(int)$HawkerID,
		"amount"			=>(float)$Amount,
		"logdate"			=>$LogDate,
		"narration"			=>$Narration,
		"linetotal"			=>(int)$LineTotal,
		"paymentid"			=>(int)$PaymentID,
		"paymenttype"		=>$PaymentType,
		"invoiceid"			=>(int)$InvoiceID,
		"invoicemonth"		=>(int)$InvoiceMonth,
		"invoiceyear"		=>(int)$InvoiceYear
		);
	}
	$query  = pdo_query($sqlaccount,$sqleaccount);
	if($query)
	{
		$RecordID = $UpdateRecordID;
		
		/*if($RecordID < 1)
		{
		   $RecordID = pdo_insert_id();
		}
		$Diff = $Amount - $preamount;
		
		$updatesql = "UPDATE ".$Prefix."cust_accounts SET linetotal= (linetotal +:linetotal) WHERE clientid=:clientid AND customerid=:customerid AND logdate >=:logdate AND id <>:id";
		$updateesql =  array("linetotal"=>(float)$Diff,"clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID,'logdate'=>(int)$LogDate,'id'=>(int)$RecordID);
		
		$query2 = pdo_query($updatesql,$updateesql);*/
	}
	if($LogDate < 1)
	{
	   $sqlaccount = "UPDATE ".$Prefix."cust_accounts SET 
		areaid				= :areaid,
		lineid				= :lineid,
		hawkerid			= :hawkerid
		WHERE
		clientid			= :clientid
		AND
		customerid			= :customerid
		";
		
		$sqleaccount	= array (	
		"areaid"			=>(int)$AreaID,
		"lineid"			=>(int)$LineID,
		"hawkerid"			=>(int)$HawkerID,
		"clientid"			=>(int)$ClientID,
		"customerid"		=>(int)$CustomerID
		);
		$query = pdo_query($sqlaccount,$sqleaccount);
	}
	GetCustomerLineTotalUpdated($CustomerID);
}
function GetCustomerLineTotalUpdated($CustomerID)
{
	global $Prefix;

	$SQL	= "SELECT * FROM ".$Prefix."cust_accounts WHERE customerid=:customerid ORDER BY logdate ASC,id ASC";
	$ESQL	= array("customerid"=>$CustomerID);
	$Query  = pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);
	if($Num > 0)
	{
		$PreLineTotal = 0;
		while($Row = pdo_fetch_assoc($Query))
		{
			$ID 		= $Row['id'];
			$LineTotal  = $Row['linetotal'];
			$Amount  	= $Row['amount'];
			
			$NewLineTotal = $PreLineTotal + $Amount;

			$UpdateSQL	= "UPDATE ".$Prefix."cust_accounts SET linetotal=:linetotal WHERE id=:id";
			$UpdateESQL	= array("linetotal"=>(float)$NewLineTotal,"id"=>(int)$ID);
			$UpdateQuery = pdo_query($UpdateSQL,$UpdateESQL);
			
			$PreLineTotal = $NewLineTotal;
		}

	}
}
function GetCustomerOutStanding($CustomerID,$CustomerPhone,$CheckDate = '',$Type='current')
{
	global $Prefix;

	$LineTotal = 0;

	$ExtArg = '';
	$ExtArr = array();
	
	if($CustomerPhone !='')
	{
		$ExtArg = " AND cust.phone =:phone";
		$ExtArr["phone"]=$_POST['customerphone'];
	}
	if($CustomerID !='')
	{
		$ExtArr = array();
		$ExtArg = " AND cust.id =:id";
		$ExtArr["id"]=$CustomerID;
	}
	if($CheckDate !='')
	{
		if($Type == 'previous')
		{
			$ExtArg .= ' AND acc.logdate < :logdate ';
		}
		else
		{
			$ExtArg .= ' AND acc.logdate <= :logdate ';
		}
		$ExtArr['logdate'] =(int)$CheckDate;
	}


	$CustSQL	= "SELECT acc.linetotal FROM ".$Prefix."cust_accounts acc,".$Prefix."customers cust WHERE acc.customerid=cust.id $ExtArg AND cust.clientid=acc.clientid AND cust.deletedon<:deletedon order by acc.logdate DESC,acc.id DESC LIMIT 1";
	$CustESQL   = array('deletedon'=>1);

	$CustESQL2 = array_merge($ExtArr,$CustESQL);
	$CustQuery	= pdo_query($CustSQL,$CustESQL2);
	$CustNum	= pdo_num_rows($CustQuery);
	if($CustNum > 0)
	{
		$LineTotalRow	= pdo_fetch_assoc($CustQuery);
		$LineTotal		= $LineTotalRow["linetotal"];
	}

	return $LineTotal;
}
function GetClientRecord($ID)
{
 	global $Prefix;
	$CheckSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id";
	$CheckESQL	= array("id"=>(int)$ID);

	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);
	$Arr = array();
	if($CheckNum > 0)
	{
		$Arr	= pdo_fetch_assoc($CheckQuery);
	}
	return $Arr;
}
function GetAllPaymentIDsByCustomerIDs($ClientID,$CustomerID)
{
	global $Prefix;
	$Arr	= array();

	$CheckSQL	= "SELECT * FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND customerid=:customerid order BY paymentid";
	$CheckESQL	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$CustomerID);
	$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
	$CheckNum	= pdo_num_rows($CheckQuery);
	if($CheckNum > 0)
	{	
		while($CheckRow	= pdo_fetch_assoc($CheckQuery))
		{
			$ID				= 	$CheckRow['id'];
			$PaymentID		= 	$CheckRow['paymentid'];
			$Arr[$ID]		= $PaymentID;
		}
	}
	return $Arr;
}
function GetOutStandingAmount($ClientID,$AreaID,$LineID,$HawkerID,$CheckDate = '',$Type='current')
{
	global $Prefix;

	$LineTotal = 0;

	$ExtArg = '';
	$ExtArr = array();
	
	$ExtArg2 = '';
	$ExtArr2 = array();
	
	if($AreaID !='')
	{
		$ExtArg .= " AND cust.areaid =:areaid";
		$ExtArr["areaid"]=(int)$AreaID;
	}
	if($LineID !='')
	{
		$ExtArg .= " AND cust.lineid =:lineid";
		$ExtArr["lineid"]=(int)$LineID;
	}
	if($HawkerID !='')
	{
		$ExtArg .= " AND cust.hawkerid =:hawkerid";
		$ExtArr["hawkerid"]=(int)$HawkerID;
	}
	if($CheckDate !='')
	{
		$CheckDate2 = strtotime(date("m/d/Y",$CheckDate).' - 1 Month');
		$CheckInvoiceMonth = date("m",$CheckDate2);
		$CheckInvoiceYear = date("Y",$CheckDate2);
		if($Type == 'previous')
		{
			$ExtArg2 .= " AND (acc.logdate <:logdate || (acc.invoicemonth<=:invoicemonth AND acc.invoiceyear<=:invoiceyear AND invoiceid >:invoiceid ))";
			$ExtArr2["logdate"]=(int)$CheckDate;
			//$ExtArr2["logdate2"]=(int)$CheckDate;
			$ExtArr2["invoiceid"]=0;
			$ExtArr2["invoicemonth"]=$CheckInvoiceMonth;
			$ExtArr2["invoiceyear"]=$CheckInvoiceYear;
		}
		else
		{
			$ExtArg2 .= " AND acc.logdate <=:logdate";
			$ExtArr2["logdate"]=(int)$CheckDate;
		}
	}
	
	$CustSQL	= "SELECT cust.id as id FROM ".$Prefix."customers cust WHERE cust.clientid=:clientid $ExtArg AND cust.deletedon<:deletedon ORDER BY id ASC";
	$CustESQL   = array('clientid'=>(int)$ClientID,'deletedon'=>1);

	$GrandTotal = 0;
	$CustESQL2 = array_merge($ExtArr,$CustESQL);
	$CustQuery	= pdo_query($CustSQL,$CustESQL2);
	$CustNum	= pdo_num_rows($CustQuery);
	if($CustNum > 0)
	{
		while($CustRow		= pdo_fetch_assoc($CustQuery))
		{
			$CustomerID		= $CustRow['id'];

			$AccSQL	= "SELECT acc.linetotal FROM ".$Prefix."cust_accounts acc WHERE acc.customerid=:id $ExtArg2 AND acc.clientid=:clientid ORDER BY acc.logdate DESC, acc.id DESC LIMIT 1";
			$AccESQL   = array('clientid'=>(int)$ClientID,'id'=>(int)$CustomerID);

			$AccESQL2 = array_merge($ExtArr2,$AccESQL);
			$AccQuery	= pdo_query($AccSQL,$AccESQL2);
			$AccNum	= pdo_num_rows($AccQuery);
			if($AccNum > 0)
			{
				$LineTotalRow	= pdo_fetch_assoc($AccQuery);
				$LineTotal		= $LineTotalRow["linetotal"];
				$GrandTotal		+= $LineTotal;
			}
		}
	}

	return $GrandTotal;
}
function GetInvoiceAmount($ClientID, $AreaID, $LineID, $HawkerID, $startdate)
{
	global $Prefix;

	$RecArr = array();

	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$ExtArg	= ' AND clientid=:clientid AND invoiceid >:invoiceid AND invoicemonth=:invoicemonth AND invoiceyear=:invoiceyear AND paymenttype=:paymenttype';
	
	$ExtArr	= array("clientid"=>(int)$ClientID,"invoiceid"=>0,"invoicemonth"=>$selectedmonth,"invoiceyear"=>$selectedyear,"paymenttype"=>'invoice');

	if($AreaID !='')
	{
		$ExtArg .= " AND areaid =:areaid";
		$ExtArr["areaid"]=(int)$AreaID;
	}
	if($LineID !='')
	{
		$ExtArg .= " AND lineid =:lineid";
		$ExtArr["lineid"]=(int)$LineID;
	}
	if($HawkerID !='')
	{
		$ExtArg .= " AND hawkerid =:hawkerid";
		$ExtArr["hawkerid"]=(int)$HawkerID;
	}

	$Sql	= "SELECT SUM(amount) AS invoicetotal, COUNT(id) AS recordcount FROM ".$Prefix."cust_accounts WHERE 1 ".$ExtArg."";

	$Query	= pdo_query($Sql,$ExtArr);
	$Rows	= pdo_fetch_assoc($Query);

	$invoicetotal	= $Rows['invoicetotal'];

	if($invoicetotal < 0.0001)
	{
		$invoicetotal	= 0;
	}

	$RecArr['totalamount']	= $invoicetotal;
	$RecArr['totalcount']	= $Rows['recordcount'];

	return $RecArr;
}
function GetCustomerPayment($ClientID, $AreaID, $LineID, $HawkerID, $startdate, $enddate, $paymenttype, $usefromdate)
{
	global $Prefix;

	$RecArr = array();

	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$ExtArg2	= "";
	$ExtArr2	= array();

	if($paymenttype == "all")
	{
		/*$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND (paymenttype=:paymenttype1 || paymenttype=:paymenttype2 || paymenttype=:paymenttype3) AND logdate BETWEEN :startdate AND :enddate';

		$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype1"=>"payment","paymenttype2"=>"coupon","paymenttype3"=>"discount","startdate"=>$startdate,"enddate"=>$enddate);*/

		if($usefromdate > 0)
		{
			$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND (paymenttype=:paymenttype1 || paymenttype=:paymenttype2 || paymenttype=:paymenttype3) AND logdate BETWEEN :startdate AND :enddate';

			$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype1"=>"payment","paymenttype2"=>"coupon","paymenttype3"=>"discount","startdate"=>$startdate,"enddate"=>$enddate);
		}
		else
		{
			$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND (paymenttype=:paymenttype1 || paymenttype=:paymenttype2 || paymenttype=:paymenttype3) AND logdate <=:enddate';

			$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype1"=>"payment","paymenttype2"=>"coupon","paymenttype3"=>"discount","enddate"=>$enddate);
		}
	}
	else
	{
		/*$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND paymenttype=:paymenttype AND logdate BETWEEN :startdate AND :enddate';

		$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype"=>$paymenttype,"startdate"=>$startdate,"enddate"=>$enddate);*/

		if($usefromdate > 0)
		{
			$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND paymenttype=:paymenttype AND logdate BETWEEN :startdate AND :enddate';

			$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype"=>$paymenttype,"startdate"=>$startdate,"enddate"=>$enddate);
		}
		else
		{
			/*$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND (paymenttype=:paymenttype1 || paymenttype=:paymenttype2 || paymenttype=:paymenttype3) AND logdate <=:enddate';

			$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype1"=>"payment","paymenttype2"=>"coupon","paymenttype3"=>"discount","enddate"=>$enddate);*/

			$ExtArg	= ' AND clientid=:clientid AND paymentid >:paymentid AND paymenttype=:paymenttype AND logdate <=:enddate';

			$ExtArr	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype"=>$paymenttype,"enddate"=>$enddate);
		}
	}

	/*$ExtArg2	= ' AND clientid=:clientid AND paymentid >:paymentid AND paymenttype=:paymenttype AND logdate BETWEEN :startdate AND :enddate';

	$ExtArr2	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype"=>"payment","startdate"=>$startdate,"enddate"=>$enddate);*/

	if($usefromdate > 0)
	{
		$ExtArg2	= ' AND clientid=:clientid AND paymentid >:paymentid AND paymenttype=:paymenttype AND logdate BETWEEN :startdate AND :enddate';

		$ExtArr2	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype"=>"payment","startdate"=>$startdate,"enddate"=>$enddate);
	}
	else
	{
		$ExtArg2	= ' AND clientid=:clientid AND paymentid >:paymentid AND paymenttype=:paymenttype AND logdate <=:enddate';

		$ExtArr2	= array("clientid"=>(int)$ClientID,"paymentid"=>0,"paymenttype"=>"payment","enddate"=>$enddate);
	}

	if($AreaID !='')
	{
		$ExtArg .= " AND areaid =:areaid";
		$ExtArr["areaid"]=(int)$AreaID;

		$ExtArg2 .= " AND areaid =:areaid";
		$ExtArr2["areaid"]=(int)$AreaID;
	}
	if($LineID !='')
	{
		$ExtArg .= " AND lineid =:lineid";
		$ExtArr["lineid"]=(int)$LineID;

		$ExtArg2 .= " AND lineid =:lineid";
		$ExtArr2["lineid"]=(int)$LineID;
	}
	if($HawkerID !='')
	{
		$ExtArg .= " AND hawkerid =:hawkerid";
		$ExtArr["hawkerid"]=(int)$HawkerID;

		$ExtArg2 .= " AND hawkerid =:hawkerid";
		$ExtArr2["hawkerid"]=(int)$HawkerID;
	}

	$Sql	= "SELECT SUM(amount) AS paymenttotal, COUNT(id) AS recordcount FROM ".$Prefix."cust_accounts WHERE 1 ".$ExtArg."";

	$Sql2	= "SELECT SUM(amount) AS paymenttotal, COUNT(id) AS recordcount FROM ".$Prefix."cust_accounts WHERE 1 ".$ExtArg2."";

	$Query	= pdo_query($Sql,$ExtArr);
	$Rows	= pdo_fetch_assoc($Query);

	$TotalAmount	= abs($Rows['paymenttotal']);
	$TotalCount		= $Rows['recordcount'];

	if($paymenttype == "all")
	{
		$Query2	= pdo_query($Sql2,$ExtArr2);
		$Rows2	= pdo_fetch_assoc($Query2);

		$TotalCount	= $Rows2['recordcount'];
	}

	$RecArr['totalamount']	= $TotalAmount;
	$RecArr['totalcount']	= $TotalCount;

	return $RecArr;
}
function makeSecurePhoneNumber($phonenumber)
{
	$firstdigit		= substr($phonenumber,0,-9);
	$lastfivedigit	= substr($phonenumber,5,5);

	$newphone	= $firstdigit."****".$lastfivedigit;

	return $newphone;
}
function getCustomerInventoryQuantityByDateRange($clientid, $customerid, $inventoryid, $startdate, $enddate, $HolidayArr, $ClientInventoryPriceArr1=array(), $CurrentInventoryFreqArr)
{
	global $Prefix;

	$totalquantity	= 0;

	$CheckStartDate	= $startdate;
	$CheckEndDate	= $enddate;

	$HolidaySQL	= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND customertype=:customertype AND customerid=:customerid";
	
	$HolidayESQL	= array("clientid"=>(int)$clientid,"date1"=>(int)$startdate,"date2"=>(int)$enddate,"date3"=>(int)$startdate,"date4"=>(int)$enddate,"deletedon"=>1,'customertype'=>1,'customerid'=>$customerid);

	$HolidayQuery	= pdo_query($HolidaySQL,$HolidayESQL);
	$HolidayNum		= pdo_num_rows($HolidayQuery);

	if($HolidayNum > 0)
	{
		while($HolidayRow = pdo_fetch_assoc($HolidayQuery))
		{
			$CustHoliStartDate	= $HolidayRow['startdate'];
			$CustHoliEndDate	= $HolidayRow['enddate'];
			$CustCustomerType	= $HolidayRow['customertype'];
			$CustCustomerID		= $HolidayRow['customerid'];
			$CustInventoryType	= $HolidayRow['inventorytype'];
			$CustInventoryID	= $HolidayRow['inventoryid'];

			$CalcStartDate = $CustHoliStartDate;
			
			if($startdate > $CustHoliStartDate)
			{
				$CalcStartDate = $startdate;
			}

			$CalcEndDate = strtotime(date("m/d/Y",$enddate));
			if($CustHoliEndDate > $enddate)
			{
				$CalcEndDate = $CustHoliEndDate;
			}
			$AddDate = 0;
			
			if($CustInventoryType < 1)
			{
				$AddDate = 1;
			}
			
			if($AddDate > 0)
			{
				$TempStartDate	= $CalcStartDate;
				$TempEndDate	= $CalcEndDate;

				while($TempStartDate <= $TempEndDate)
				{
					$HolidayArr[] = $TempStartDate;

					$TempStartDate = $TempStartDate + 86400;
				} 	
			}
		}
	}

	$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) AND inventoryid=:inventoryid";
	$SubscriptionESQL	= array("subscriptiondate"=>$enddate,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

	$SubscriptionQuery	= pdo_query($SubscriptionSQL,$SubscriptionESQL);
	$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);
	
	$InventoryStartDateArr  = array();

	$TempIndex = 0;

	$SubsHolidayArr	= array();
	
	if($SubscriptionNum > 0)
	{
		$TempFullMonthInventoryArr	= array();

		$TempStartDate		= $startdate;
		$TempEndDate		= $enddate;
		
		while($SRow	= pdo_fetch_assoc($SubscriptionQuery))
		{
			$DaysArr	 = array();

			$InventoryID = $SRow['inventoryid'];
			$Quantity	 = $SRow['quantity'];
			
			if($Quantity < 1)
			{
				$Quantity = 1;
			}
			$StartDate	 = $SRow['subscriptiondate'];
			
			if($CurrentInventoryFreqArr[$InventoryID]  == '1')
			{
				$DaysArr	= @array_unique(@array_filter(@explode("::",$SRow['days'])));
			}

			/*$TotalHolidays	= @count($SubsHolidayArr);*/

			$StartDate	= strtotime(date("m/d/Y",$StartDate));

			$UsePartialBilling = 0;
			if($StartDate > 0)
			{
				if($StartDate >= $CheckStartDate && $StartDate <= $CheckEndDate)
				{
					$UsePartialBilling = 1;
				}
			}

			/*if($StartDate < $CheckEndDate AND $TotalHolidays < $LastDayofMonth)*/
			if($StartDate < $CheckEndDate)
			{
				$SubscriptionArr[$TempIndex]['inventoryid']	= $InventoryID;
				$SubscriptionArr[$TempIndex]['quantity']	= $Quantity;
				$SubscriptionArr[$TempIndex]['inventoryid']	= $InventoryID;

				if($StartDate <= $CheckStartDate)
				{
					$InventoryStartDateArr[$InventoryID]		= $CheckStartDate;
					$SubscriptionArr[$TempIndex]['startdate']	= $CheckStartDate;
				}
				else
				{
					$InventoryStartDateArr[$InventoryID]		= $StartDate;
					$SubscriptionArr[$TempIndex]['startdate']	= $StartDate;
				}
				$SubscriptionArr[$TempIndex]['enddate']			= $CheckEndDate;
				$SubscriptionArr[$TempIndex]['partialbilling']	= $UsePartialBilling;
				$SubscriptionArr[$TempIndex]['frequency']		= $CurrentInventoryFreqArr[$InventoryID];

				$SubscriptionArr[$TempIndex]['billabledays']	= $DaysArr;
			
				if($StartDate <= $CheckStartDate)
				{
					$TempFullMonthInventoryArr[] = $InventoryID;
				}
			}
			else
			{
				$TempFullMonthInventoryArr[] = $InventoryID;
			}
			$TempIndex++;
		}
	}

	$SubscriptionLogSQL	= "SELECT * FROM ".$Prefix."subscriptions_log WHERE customerid=:customerid AND inventoryid=:inventoryid AND (subscriptiondate <=:subscriptiondate ) ORDER BY inventoryid,subscriptiondate ASC,unsubscribedate ASC";				
	$SubscriptionLogESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid,"subscriptiondate"=>(int)$enddate);

	$SubscriptionLogQuery	= pdo_query($SubscriptionLogSQL,$SubscriptionLogESQL);
	$SubscriptionLogNum		= pdo_num_rows($SubscriptionLogQuery);

	$SubscriptionLogArr 	= array();

	if($SubscriptionLogNum > 0)
	{
		$InventoryStatusArr	= array();
		$InventoryDaysArr	= array();
		$InventoryFreqArr	= array();

		while($SRow	= pdo_fetch_assoc($SubscriptionLogQuery))
		{
			$InventoryID 		= $SRow['inventoryid'];
			$Quantity 			= $SRow['quantity'];
			$SubscriptionDate	= $SRow['subscriptiondate'];
			$UnsubDate   		= $SRow['unsubscribedate'];
			$Status				= $SRow['status'];
			$Frequency			= $SRow['frequency'];
			$CreatedOn			= strtotime(date("m/d/Y",$CreatedOn));

			if($UnsubDate < $CheckStartDate && $UnsubDate > 0)
			{
				continue;
			}
			if($Quantity < 1)
			{
				$Quantity = 1;
			}

			if($Frequency  == '1')
			{
				$DaysArr	= @array_unique(@array_filter(@explode("::",$SRow['days'])));
			}

			$SubscriptionArr[$TempIndex]['inventoryid']		= $InventoryID;
			$SubscriptionArr[$TempIndex]['billabledays']	= $DaysArr;
			$SubscriptionArr[$TempIndex]['quantity']		= $Quantity;
			$SubscriptionArr[$TempIndex]['frequency']		= $Frequency;
			$SubscriptionArr[$TempIndex]['islogentry']		= 1;
			$SubscriptionArr[$TempIndex]['partialbilling']	= 1;

			if($SubscriptionDate <= $CheckStartDate)
			{
				$SubscriptionArr[$TempIndex]['startdate']	= (int)$CheckStartDate;
			}
			else
			{
				$SubscriptionArr[$TempIndex]['startdate']	= (int)$SubscriptionDate;
			}
				
			if($UnsubDate > 0)
			{
				if($UnsubDate > $CheckEndDate)
				{
					$UnsubDate = $CheckEndDate;
				}
				$SubscriptionArr[$TempIndex]['enddate'] =(int)$UnsubDate;	
			}

			$CheckSubslogDateArr[$InvetoryID] = $SubscriptionDate;	
			$TempIndex++;
		}
	}

	if(!empty($SubscriptionArr))
	{
	}
}
function getCustomerInventoryQuantityByDateRange_org($clientid, $customerid, $inventoryid, $startdate, $enddate, $HolidayArr, $ClientInventoryPriceArr1=array(), $CurrentInventoryFreqArr)
{
	global $Prefix;

	$totalquantity	= 0;

	$CheckStartDate	= $startdate;
	$CheckEndDate	= $enddate;

	/*$LinkerSql	= "SELECT SUM(days) AS totaldays FROM ".$Prefix."inventory_days_price_linker WHERE inventoryid=:inventoryid AND createdon BETWEEN :startdate AND :enddate";
	$LinkerEsql	= array("inventoryid"=>(int)$inventoryid,"startdate"=>$startdate,"enddate"=>$enddate);

	$LinkerQuery	= pdo_query($LinkerSql,$LinkerEsql);
	$LinkerRows		= pdo_fetch_assoc($LinkerQuery);

	$totaldays		= $LinkerRows['totaldays'];

	$DailyPriceArr	= array();

	$PricingType	= $ClientInventoryPriceArr[$inventoryid]['pricingtype'];

	if($PricingType > 0)
	{
		$PricingSQL2		= "SELECT * FROM ".$Prefix."inventory_date_price_linker WHERE clientid=:clientid AND inventoryid=:inventoryid AND createdon BETWEEN :startdate AND :enddate";
		$PricingESQL2	= array("clientid"=>(int)$clientid,"inventoryid"=>(int)$inventoryid,"startdate"=>$startdate,"enddate"=>$enddate);

		$PricingQuery2	= pdo_query($PricingSQL2,$PricingESQL2);
		$PricingNum2	= pdo_num_rows($PricingQuery2);

		if($PricingNum2 > 0)
		{
			while($PricingDailyRow	= pdo_fetch_assoc($PricingQuery2))
			{
				$Date					= $PricingDailyRow['date'];
				$month					= $PricingDailyRow['month'];
				$year					= $PricingDailyRow['year'];

				$timestamp				= strtotime($month ."/".$Date."/".$year); 

				$Price						= $PricingDailyRow['price'];
				$DailyPriceArr[$timestamp]	= $Price;
			}
		}
	}*/

	$HolidaySQL	= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND customertype=:customertype AND customerid=:customerid";
	
	$HolidayESQL	= array("clientid"=>(int)$clientid,"date1"=>(int)$startdate,"date2"=>(int)$enddate,"date3"=>(int)$startdate,"date4"=>(int)$enddate,"deletedon"=>1,'customertype'=>1,'customerid'=>$customerid);

	$HolidayQuery	= pdo_query($HolidaySQL,$HolidayESQL);
	$HolidayNum		= pdo_num_rows($HolidayQuery);

	if($HolidayNum > 0)
	{
		while($HolidayRow = pdo_fetch_assoc($HolidayQuery))
		{
			$CustHoliStartDate	= $HolidayRow['startdate'];
			$CustHoliEndDate	= $HolidayRow['enddate'];
			$CustCustomerType	= $HolidayRow['customertype'];
			$CustCustomerID		= $HolidayRow['customerid'];
			$CustInventoryType	= $HolidayRow['inventorytype'];
			$CustInventoryID	= $HolidayRow['inventoryid'];
			
			$CalcStartDate = $CustHoliStartDate;
			
			if($startdate > $CustHoliStartDate)
			{
				$CalcStartDate = $startdate;
			}

			$CalcEndDate = strtotime(date("m/d/Y",$enddate));
			if($CustHoliEndDate > $enddate)
			{
				$CalcEndDate = $CustHoliEndDate;
			}
			$AddDate = 0;
			
			if($CustInventoryType < 1)
			{
				$AddDate = 1;
			}
			
			if($AddDate > 0)
			{
				$TempStartDate	= $CalcStartDate;
				$TempEndDate	= $CalcEndDate;

				while($TempStartDate <= $TempEndDate)
				{
					$HolidayArr[] = $TempStartDate;

					$TempStartDate = $TempStartDate + 86400;
				} 	
			}
		}
	}

	$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) AND inventoryid=:inventoryid";
	$SubscriptionESQL	= array("subscriptiondate"=>$enddate,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

	$SubscriptionQuery	= pdo_query($SubscriptionSQL,$SubscriptionESQL);
	$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);
	
	$InventoryStartDateArr  = array();

	$TempIndex = 0;

	$SubsHolidayArr	= array();
	
	if($SubscriptionNum > 0)
	{
		$TempFullMonthInventoryArr	= array();

		$TempStartDate		= $startdate;
		$TempEndDate		= $enddate;
		
		while($SRow	= pdo_fetch_assoc($SubscriptionQuery))
		{
			$DaysArr	 = array();

			$InventoryID = $SRow['inventoryid'];
			$Quantity	 = $SRow['quantity'];
			
			if($Quantity < 1)
			{
				$Quantity = 1;
			}
			$StartDate	 = $SRow['subscriptiondate'];
			
			if($CurrentInventoryFreqArr[$InventoryID]  == '1')
			{
				$DaysArr	= @array_unique(@array_filter(@explode("::",$SRow['days'])));
			}

			/*@array_filter($DaysArr);
			@array_unique($DaysArr);

			$SubsHolidayArr = $HolidayArr;

			@array_filter($SubsHolidayArr);
			@array_unique($SubsHolidayArr);
			
			$TotalHolidays	= @count($SubsHolidayArr);*/

			$StartDate	= strtotime(date("m/d/Y",$StartDate));

			$UsePartialBilling = 0;
			if($StartDate > 0)
			{
				if($StartDate >= $CheckStartDate && $StartDate <= $CheckEndDate)
				{
					$UsePartialBilling = 1;
				}
			}

			/*if($StartDate < $CheckEndDate AND $TotalHolidays < $LastDayofMonth)*/
			if($StartDate < $CheckEndDate)
			{
				$SubscriptionArr[$TempIndex]['inventoryid']	= $InventoryID;
				$SubscriptionArr[$TempIndex]['quantity']	= $Quantity;
				$SubscriptionArr[$TempIndex]['inventoryid']	= $InventoryID;

				if($StartDate <= $CheckStartDate)
				{
					$InventoryStartDateArr[$InventoryID]		= $CheckStartDate;
					$SubscriptionArr[$TempIndex]['startdate']	= $CheckStartDate;
				}
				else
				{
					$InventoryStartDateArr[$InventoryID]		= $StartDate;
					$SubscriptionArr[$TempIndex]['startdate']	= $StartDate;
				}
				$SubscriptionArr[$TempIndex]['enddate']			= $CheckEndDate;
				$SubscriptionArr[$TempIndex]['partialbilling']	= $UsePartialBilling;
				$SubscriptionArr[$TempIndex]['frequency']		= $CurrentInventoryFreqArr[$InventoryID];

				$SubscriptionArr[$TempIndex]['billabledays']	= $DaysArr;
			
				if($StartDate <= $CheckStartDate)
				{
					$TempFullMonthInventoryArr[] = $InventoryID;
				}
			}
			else
			{
				$TempFullMonthInventoryArr[] = $InventoryID;
			}
			$TempIndex++;
		}
	}

	$SubscriptionLogSQL	= "SELECT * FROM ".$Prefix."subscriptions_log WHERE customerid=:customerid AND inventoryid=:inventoryid AND (subscriptiondate <=:subscriptiondate ) ORDER BY inventoryid,subscriptiondate ASC,unsubscribedate ASC";				
	$SubscriptionLogESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid,"subscriptiondate"=>(int)$enddate);

	$SubscriptionLogQuery	= pdo_query($SubscriptionLogSQL,$SubscriptionLogESQL);
	$SubscriptionLogNum		= pdo_num_rows($SubscriptionLogQuery);

	$SubscriptionLogArr 	= array();

	if($SubscriptionLogNum > 0)
	{
		$InventoryStatusArr	= array();
		$InventoryDaysArr	= array();
		$InventoryFreqArr	= array();

		while($SRow	= pdo_fetch_assoc($SubscriptionLogQuery))
		{
			$InventoryID 		= $SRow['inventoryid'];
			$Quantity 			= $SRow['quantity'];
			$SubscriptionDate	= $SRow['subscriptiondate'];
			$UnsubDate   		= $SRow['unsubscribedate'];
			$Status				= $SRow['status'];
			$Frequency			= $SRow['frequency'];
			$CreatedOn			= strtotime(date("m/d/Y",$CreatedOn));

			if($UnsubDate < $CheckStartDate && $UnsubDate > 0)
			{
				continue;
			}
			if($Quantity < 1)
			{
				$Quantity = 1;
			}

			if($Frequency  == '1')
			{
				$DaysArr	= @array_unique(@array_filter(@explode("::",$SRow['days'])));
			}

			$SubscriptionArr[$TempIndex]['inventoryid']		= $InventoryID;
			$SubscriptionArr[$TempIndex]['billabledays']	= $DaysArr;
			$SubscriptionArr[$TempIndex]['quantity']		= $Quantity;
			$SubscriptionArr[$TempIndex]['frequency']		= $Frequency;
			$SubscriptionArr[$TempIndex]['islogentry']		= 1;
			$SubscriptionArr[$TempIndex]['partialbilling']	= 1;

			if($SubscriptionDate <= $CheckStartDate)
			{
				$SubscriptionArr[$TempIndex]['startdate']	= (int)$CheckStartDate;
			}
			else
			{
				$SubscriptionArr[$TempIndex]['startdate']	= (int)$SubscriptionDate;
			}
				
			if($UnsubDate > 0)
			{
				if($UnsubDate > $CheckEndDate)
				{
					$UnsubDate = $CheckEndDate;
				}
				$SubscriptionArr[$TempIndex]['enddate'] =(int)$UnsubDate;	
			}

			$CheckSubslogDateArr[$InvetoryID] = $SubscriptionDate;	
			$TempIndex++;
		}
	}

	if(!empty($SubscriptionArr))
	{
		foreach($SubscriptionArr as $key => $subscriptionrows)
		{
			$SubsHolidayArr		= $HolidayArr;

			$inventoryid		= $subscriptionrows['inventoryid'];
			$quantity			= $subscriptionrows['quantity'];
			$startdate			= $subscriptionrows['startdate'];
			$enddate			= $subscriptionrows['enddate'];
			$partialbilling		= $subscriptionrows['partialbilling'];
			$frequency			= $subscriptionrows['frequency'];
			$BillableDaysArr	= $subscriptionrows['billabledays'];
			$islogentry			= $subscriptionrows['islogentry'];

			if($CheckStartDate > $startdate)
			{
				$startdate	= $CheckStartDate;
			}

			if($enddate < 1)
			{
				$enddate	= $CheckEndDate;

				if($startdate == $InventoryStartDateArr[$InventoryID] && $islogentry > 0)
				{
					continue;
				}
			}

			if($frequency == 1)
			{
				$TempCheckStartDate = $startdate;
				$TempCheckEndDate 	= $enddate;

				if(!empty($BillableDaysArr))
				{
					while($TempCheckStartDate <= $TempCheckEndDate)
					{
						$BillableCheckDay	= date("N",$TempCheckStartDate);
						
						if(!in_array($BillableCheckDay,$BillableDaysArr))
						{
							$SubsHolidayArr[] = $TempCheckStartDate;	
						}

						$TempCheckStartDate = $TempCheckStartDate + 86400;
					}
				}
			}

			$SubsHolidayArr	= @array_filter(@array_unique($SubsHolidayArr));

			$TotalHolidays	= @count($SubsHolidayArr);

			if($partialbilling > 0 )
			{
				if($frequency == 1)
				{
					$StartDay		= date("d",$startdate);
					$EndDay			= date("d",$enddate);

					$NoDays	= 0;
					

					$TempCheckStartDate = $startdate;
					$TempCheckEndDate 	= $enddate;
					
					if(!empty($BillableDaysArr))
					{
						while($TempCheckStartDate <= $TempCheckEndDate)
						{
							$BillableCheckDay	= date("N",$TempCheckStartDate);
							
							if(in_array($BillableCheckDay,$BillableDaysArr) AND  !in_array($TempCheckStartDate,$SubsHolidayArr)  AND  !in_array($TempCheckStartDate,$HolidayArr))
							{
								$NoDays	+= 1;	
							}

							$TempCheckStartDate = $TempCheckStartDate + 86400;
						}
					}
					else
					{
						$NoDays	= Daybetweendates($startdate,$enddate);
					}

					/*foreach($DailyPriceArr as $CheckDate => $Price)
					{
						if(in_array($CheckDate,$SubsHolidayArr))
						{
							continue;
						}

						if($CheckDate >= $startdate AND $CheckDate <=$enddate)
						{
							$NoDays	+= 1;
						}
					}*/
				}
				else
				{
					$NoDays	= Daybetweendates($startdate,$enddate);
				}
			}
			else
			{
				$NoDays	= Daybetweendates($startdate,$enddate);
			}
			
			$totalquantity	= (int)$NoDays*(int)$quantity;
		}
	}
	return $totalquantity;
}
function getCustomerInventoryQuantityByDateRange_org_devesh($clientid, $customerid, $inventoryid, $startdate, $enddate, $HolidayArr, $ClientInventoryPriceArr, $CurrentInventoryFreqArr)
{
	global $Prefix;

	$totalquantity	= 0;

	$CheckStartDate	= $startdate;
	$CheckEndDate	= $enddate;

	$LinkerSql	= "SELECT SUM(days) AS totaldays FROM ".$Prefix."inventory_days_price_linker WHERE inventoryid=:inventoryid AND createdon BETWEEN :startdate AND :enddate";
	$LinkerEsql	= array("inventoryid"=>(int)$inventoryid,"startdate"=>$startdate,"enddate"=>$enddate);

	$LinkerQuery	= pdo_query($LinkerSql,$LinkerEsql);
	$LinkerRows		= pdo_fetch_assoc($LinkerQuery);

	$totaldays		= $LinkerRows['totaldays'];

	$DailyPriceArr	= array();

	$PricingType	= $ClientInventoryPriceArr[$inventoryid]['pricingtype'];

	if($PricingType > 0)
	{
		$PricingSQL2		= "SELECT * FROM ".$Prefix."inventory_date_price_linker WHERE clientid=:clientid AND inventoryid=:inventoryid AND createdon BETWEEN :startdate AND :enddate";
		$PricingESQL2	= array("clientid"=>(int)$clientid,"inventoryid"=>(int)$inventoryid,"startdate"=>$startdate,"enddate"=>$enddate);

		$PricingQuery2	= pdo_query($PricingSQL2,$PricingESQL2);
		$PricingNum2	= pdo_num_rows($PricingQuery2);

		if($PricingNum2 > 0)
		{
			while($PricingDailyRow	= pdo_fetch_assoc($PricingQuery2))
			{
				$Date					= $PricingDailyRow['date'];
				$month					= $PricingDailyRow['month'];
				$year					= $PricingDailyRow['year'];

				$timestamp				= strtotime($month ."/".$Date."/".$year); 

				$Price						= $PricingDailyRow['price'];
				$DailyPriceArr[$timestamp]	= $Price;
			}
		}
	}

	$HolidaySQL	= "SELECT * FROM ".$Prefix."holidays WHERE clientid=:clientid AND ((startdate BETWEEN :date1 AND :date2) || (enddate BETWEEN :date3 AND :date4)) AND deletedon <:deletedon AND customertype=:customertype AND customerid=:customerid";
	$HolidayESQL	= array("clientid"=>(int)$clientid,"date1"=>(int)$startdate,"date2"=>(int)$enddate,"date3"=>(int)$startdate,"date4"=>(int)$enddate,"deletedon"=>1,'customertype'=>1,'customerid'=>$customerid);

	$HolidayQuery	= pdo_query($HolidaySQL,$HolidayESQL);
	$HolidayNum		= pdo_num_rows($HolidayQuery);

	if($HolidayNum > 0)
	{
		while($HolidayRow = pdo_fetch_assoc($HolidayQuery))
		{
			$CustHoliStartDate	= $HolidayRow['startdate'];
			$CustHoliEndDate	= $HolidayRow['enddate'];
			$CustCustomerType	= $HolidayRow['customertype'];
			$CustCustomerID		= $HolidayRow['customerid'];
			$CustInventoryType	= $HolidayRow['inventorytype'];
			$CustInventoryID	= $HolidayRow['inventoryid'];
			
			$CalcStartDate = $CustHoliStartDate;
			
			if($startdate > $CustHoliStartDate)
			{
				$CalcStartDate = $startdate;
			}

			$CalcEndDate = strtotime(date("m/d/Y",$enddate));
			if($CustHoliEndDate > $enddate)
			{
				$CalcEndDate = $CustHoliEndDate;
			}
			$AddDate = 0;
			
			if($CustInventoryType < 1)
			{
				$AddDate = 1;
			}
			
			if($AddDate > 0)
			{
				$TempStartDate	= $CalcStartDate;
				$TempEndDate	= $CalcEndDate;

				while($TempStartDate <= $TempEndDate)
				{
					$HolidayArr[] = $TempStartDate;

					$TempStartDate = $TempStartDate + 86400;
				} 	
			}
		}
	}

	$SubscriptionSQL	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid AND (subscriptiondate <=:subscriptiondate ) AND inventoryid=:inventoryid";
	$SubscriptionESQL	= array("subscriptiondate"=>$enddate,"customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid);

	$SubscriptionQuery	= pdo_query($SubscriptionSQL,$SubscriptionESQL);
	$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);
	
	$InventoryStartDateArr  = array();

	$TempIndex = 0;

	$SubsHolidayArr	= array();
	
	if($SubscriptionNum > 0)
	{
		$TempFullMonthInventoryArr	= array();

		$TempStartDate		= $startdate;
		$TempEndDate		= $enddate;
		
		while($SRow	= pdo_fetch_assoc($SubscriptionQuery))
		{
			$DaysArr	 = array();

			$InventoryID = $SRow['inventoryid'];
			$Quantity	 = $SRow['quantity'];
			
			if($Quantity < 1)
			{
				$Quantity = 1;
			}
			$StartDate	 = $SRow['subscriptiondate'];
			
			if($CurrentInventoryFreqArr[$InventoryID]  == '1')
			{
				$DaysArr	= @array_unique(@array_filter(@explode("::",$SRow['days'])));
			}

			/*@array_filter($DaysArr);
			@array_unique($DaysArr);

			$SubsHolidayArr = $HolidayArr;

			@array_filter($SubsHolidayArr);
			@array_unique($SubsHolidayArr);
			
			$TotalHolidays	= @count($SubsHolidayArr);*/

			$StartDate	= strtotime(date("m/d/Y",$StartDate));

			$UsePartialBilling = 0;
			if($StartDate > 0)
			{
				if($StartDate >= $CheckStartDate && $StartDate <= $CheckEndDate)
				{
					$UsePartialBilling = 1;
				}
			}

			/*if($StartDate < $CheckEndDate AND $TotalHolidays < $LastDayofMonth)*/
			if($StartDate < $CheckEndDate)
			{
				$SubscriptionArr[$TempIndex]['inventoryid']	= $InventoryID;
				$SubscriptionArr[$TempIndex]['quantity']	= $Quantity;
				$SubscriptionArr[$TempIndex]['inventoryid']	= $InventoryID;

				if($StartDate <= $CheckStartDate)
				{
					$InventoryStartDateArr[$InventoryID]		= $CheckStartDate;
					$SubscriptionArr[$TempIndex]['startdate']	= $CheckStartDate;
				}
				else
				{
					$InventoryStartDateArr[$InventoryID]		= $StartDate;
					$SubscriptionArr[$TempIndex]['startdate']	= $StartDate;
				}
				$SubscriptionArr[$TempIndex]['enddate']			= $CheckEndDate;
				$SubscriptionArr[$TempIndex]['partialbilling']	= $UsePartialBilling;
				$SubscriptionArr[$TempIndex]['frequency']		= $CurrentInventoryFreqArr[$InventoryID];

				$SubscriptionArr[$TempIndex]['billabledays']	= $DaysArr;
			
				if($StartDate <= $CheckStartDate)
				{
					$TempFullMonthInventoryArr[] = $InventoryID;
				}
			}
			else
			{
				$TempFullMonthInventoryArr[] = $InventoryID;
			}
			$TempIndex++;
		}
	}

	$SubscriptionLogSQL	= "SELECT * FROM ".$Prefix."subscriptions_log WHERE customerid=:customerid AND inventoryid=:inventoryid AND (subscriptiondate <=:subscriptiondate ) ORDER BY inventoryid,subscriptiondate ASC,unsubscribedate ASC";				
	$SubscriptionLogESQL	= array("customerid"=>(int)$customerid,"inventoryid"=>(int)$inventoryid,"subscriptiondate"=>(int)$enddate);

	$SubscriptionLogQuery	= pdo_query($SubscriptionLogSQL,$SubscriptionLogESQL);
	$SubscriptionLogNum		= pdo_num_rows($SubscriptionLogQuery);

	$SubscriptionLogArr 	= array();

	if($SubscriptionLogNum > 0)
	{
		$InventoryStatusArr	= array();
		$InventoryDaysArr	= array();
		$InventoryFreqArr	= array();

		while($SRow	= pdo_fetch_assoc($SubscriptionLogQuery))
		{
			$InventoryID 		= $SRow['inventoryid'];
			$Quantity 			= $SRow['quantity'];
			$SubscriptionDate	= $SRow['subscriptiondate'];
			$UnsubDate   		= $SRow['unsubscribedate'];
			$Status				= $SRow['status'];
			$Frequency			= $SRow['frequency'];
			$CreatedOn			= strtotime(date("m/d/Y",$CreatedOn));

			if($UnsubDate < $CheckStartDate && $UnsubDate > 0)
			{
				continue;
			}
			if($Quantity < 1)
			{
				$Quantity = 1;
			}

			if($Frequency  == '1')
			{
				$DaysArr	= @array_unique(@array_filter(@explode("::",$SRow['days'])));
			}

			$SubscriptionArr[$TempIndex]['inventoryid']		= $InventoryID;
			$SubscriptionArr[$TempIndex]['billabledays']	= $DaysArr;
			$SubscriptionArr[$TempIndex]['quantity']		= $Quantity;
			$SubscriptionArr[$TempIndex]['frequency']		= $Frequency;
			$SubscriptionArr[$TempIndex]['islogentry']		= 1;
			$SubscriptionArr[$TempIndex]['partialbilling']	= 1;

			if($SubscriptionDate <= $CheckStartDate)
			{
				$SubscriptionArr[$TempIndex]['startdate']	= (int)$CheckStartDate;
			}
			else
			{
				$SubscriptionArr[$TempIndex]['startdate']	= (int)$SubscriptionDate;
			}
				
			if($UnsubDate > 0)
			{
				if($UnsubDate > $CheckEndDate)
				{
					$UnsubDate = $CheckEndDate;
				}
				$SubscriptionArr[$TempIndex]['enddate'] =(int)$UnsubDate;	
			}

			$CheckSubslogDateArr[$InvetoryID] = $SubscriptionDate;	
			$TempIndex++;
		}
	}

	if(!empty($SubscriptionArr))
	{
		foreach($SubscriptionArr as $key => $subscriptionrows)
		{
			$SubsHolidayArr		= $HolidayArr;

			$inventoryid		= $subscriptionrows['inventoryid'];
			$quantity			= $subscriptionrows['quantity'];
			$startdate			= $subscriptionrows['startdate'];
			$enddate			= $subscriptionrows['enddate'];
			$partialbilling		= $subscriptionrows['partialbilling'];
			$frequency			= $subscriptionrows['frequency'];
			$BillableDaysArr	= $subscriptionrows['billabledays'];
			$islogentry			= $subscriptionrows['islogentry'];

			if($CheckStartDate > $startdate)
			{
				$startdate	= $CheckStartDate;
			}

			if($enddate < 1)
			{
				$enddate	= $CheckEndDate;

				if($startdate == $InventoryStartDateArr[$InventoryID] && $islogentry > 0)
				{
					continue;
				}
			}

			if($frequency == 1)
			{
				$TempCheckStartDate = $startdate;
				$TempCheckEndDate 	= $enddate;

				if(!empty($BillableDaysArr))
				{
					while($TempCheckStartDate <= $TempCheckEndDate)
					{
						$BillableCheckDay	= date("N",$TempCheckStartDate);
						
						if(!in_array($BillableCheckDay,$BillableDaysArr))
						{
							$SubsHolidayArr[] = $TempCheckStartDate;	
						}

						$TempCheckStartDate = $TempCheckStartDate + 86400;
					}
				}
			}

			$SubsHolidayArr	= @array_filter(@array_unique($SubsHolidayArr));

			$TotalHolidays	= @count($SubsHolidayArr);

			if($partialbilling > 0 || $TotalHolidays > 0)
			{
				if($PricingType > 0)
				{
					$StartDay		= date("d",$startdate);
					$EndDay			= date("d",$enddate);

					$NoDays	= 0;

					foreach($DailyPriceArr as $CheckDate => $Price)
					{
						if(in_array($CheckDate,$SubsHolidayArr))
						{
							continue;
						}

						if($CheckDate >= $startdate AND $CheckDate <=$enddate)
						{
							$NoDays	+= 1;
						}
					}
				}
				else
				{
					$NoDays	= $totaldays - $TotalHolidays;
				}
			}
			else
			{
				$NoDays	= $totaldays - $TotalHolidays;
			}

			$totalquantity	= (int)$NoDays*(int)$quantity;
		}
	}
	return $totalquantity;
}
function Daybetweendates($date1,$date2)
{
	$date1=date_create(date("Y-m-d",$date1));
	$date2=date_create(date("Y-m-d",$date2));
	$diff=date_diff($date1,$date2);
	
	return $diff->format("%a");
}
function hex2rgba($color, $opacity = false) {
 
	$default = 'rgb(0,0,0)';
 
	//Return default if no color provided
	if(empty($color))
          return $default; 
 
	//Sanitize $color if "#" is provided 
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }
 
        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }
 
        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);
 
        //Check if opacity is set(rgba or rgb)
        if($opacity){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }
 
        //Return rgb(a) color string
        return $output;
}
function AlphaCode($Passwordlen)
{
	$Source[0]		= "abcdefghijklmnopqrstuvwxyz";

	$Min = 0;
	$i=0;
	while($i < $Passwordlen)
	{
		$Max = strlen($Source[$i % count($Source)])-1;
		$Rand = rand($Min,$Max);
		$Password .= substr($Source[$i % count($Source)],$Rand,1);
		$i++;
	}
	return $Password;
}
function ScheduleInvoiceSMSCampaign($clientid,$invoicemonthyear)
{
	global $Prefix;

	$message = "Dear %name%,\n\nBill generated for %month%, %year%: %amount%\n\nInvoice: %link%\n\n%agency%\nTeam Orlo";

	$smscredit	= 1;

	$smstype = 'invoicesms';

	$haserror = false;
	$Response['success']	= false;
	$Response['msg']		= "Unable to save campaign, Please try later.";

	$Sql	= "SELECT * FROM ".$Prefix."clients WHERE id=:id";
	$Esql	= array("id"=>(int)$clientid);

	$Query	= pdo_query($Sql,$Esql);
	$Rows	= pdo_fetch_assoc($Query);

	$clientname	= $Rows['clientname'];

	$totalrecords	= 0;

	$scheduleddate = time();

	$custcondition	= "";
	$CustEsql		= array();

	$custcondition	= " AND cust.deletedon <:deletedon AND cust.phone <>:phone";
	$CustEsql		= array("deletedon"=>1,"phone"=>"");

	if($clientid > 0)
	{
		$custcondition	.= " AND cust.clientid=:clientid";
		$CustEsql['clientid']	= (int)$clientid;
	}

	if($smstype == 'invoicesms')
	{
		$MonthYearArr = explode("-",$invoicemonthyear);
		$Month	= $MonthYearArr[1];
		$Year	= $MonthYearArr[0];

		$CustomerSql	= "SELECT cust.* FROM ".$Prefix."invoices inv,".$Prefix."customers cust WHERE inv.invoicemonth=:invoicemonth AND inv.invoiceyear=:invoiceyear AND inv.deletedon < :deletedon2 AND inv.customerid=cust.id".$custcondition." ORDER BY cust.customerid ASC, cust.status DESC";
		$CustEsql['deletedon2']		= 1;
		$CustEsql['invoicemonth']	= (int)$Month;
		$CustEsql['invoiceyear']	= (int)$Year;
	}

	$CustomerQuery	= pdo_query($CustomerSql,$CustEsql);
	$CustomerNum	= pdo_num_rows($CustomerQuery);

	/*$creditwilluse	= (int)$CustomerNum*(int)$_POST['smscredit'];*/
	$creditwilluse		= (int)$CustomerNum;

	$totalsmscreditsavaiable	= GetAvailableSMSCredit($clientid);

	if($totalsmscreditsavaiable < $creditwilluse)
	{
		$haserror = true;
		$Response['msg']	= "you don't have sufficient credit to schedule campaign.";
	}

	if($haserror == false)
	{
		$monthyear		= 0;
		$invoicemonth	= 0;
		$invoiceyear	= 0;

		if($smstype == 'invoicesms')
		{
			$monthyear	= strtotime($invoicemonthyear)+((60*60)*4);

			$invoicemonth	= date("m",$monthyear);
			$invoiceyear	= date("Y",$monthyear);
		}

		$Sql	= "INSERT INTO ".$Prefix."campaign SET 
		clientid		=:clientid,
		smstype			=:smstype,
		monthyear		=:monthyear,
		smscredit		=:smscredit,
		message			=:message,
		scheduleddate	=:scheduleddate,
		invoicemonth	=:invoicemonth,
		invoiceyear		=:invoiceyear,
		createdon		=:createdon";

		$Esql	= array(
			"clientid"			=>(int)$clientid,
			"smstype"			=>$smstype,
			"monthyear"			=>$monthyear,
			"smscredit"			=>(int)$smscredit,
			"message"			=>$message,
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
					$contactid	= $CustomerRows['id'];
					$phone		= $CustomerRows['phone'];
					$name		= $CustomerRows['name'];

					if(trim($name) == "")
					{
						$name	= "Customer";
					}

					if($phone !='' && strlen($phone) == 10)
					{
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
							"smscredit"		=>(int)$smscredit,
							"createdon"		=>$createdon,
							"contactid"		=>$contactid,
							"phonenumber"	=>$phone,
							"clientid"		=>(int)$clientid
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
	return $Response;
}
function CleanPriceString($Price)
{
	$str = str_replace(array("Rs.","RS.","INR",","),"",$Price);

	return trim($str);
}
function extract_numbers($string)
{
	//@preg_match_all('!\d+(?:\.\d+)?!', $string, $match);	
	$Number = str_replace(array(",","$"," "),"",$string);
	//$Number	= $match[1][0];
	return (float)$Number;
}
function GeneratePaymentLinks_SMSCredit($CustomerArr,$Amount,$Notes,$CreditID)
{
	
	global $RAZOR_PAY_API_KEY, $RAZOR_PAY_API_SECRET,$Prefix;

	$api = new Api($RAZOR_PAY_API_KEY, $RAZOR_PAY_API_SECRET);

	$Amount = $Amount * 100; //Amount should be in paisa or 

	$link  = $api->invoice->create(
					array(
					'customer' => 
								array(
									 "name"=>$CustomerArr['name'],
									"email"=> $CustomerArr['email'],
									"contact"=> $CustomerArr['phone']
									),
					  "type"=> "link",
					  "view_less"=> 1,
					  "amount"=> $Amount,
					  "currency"=> "INR",
					  "description"=> $Notes,
					  "sms_notify"=> 1,
					  "email_notify"=> 1
					)
				);
	ob_start();
	$response = ob_get_contents();
	ob_end_clean();
	ob_flush();
	
	$TransactionInvoiceID	= $link["id"];
	$PaymentLink			= $link["short_url"];
	$LinkStatus				= $link["status"];

	$PaymentLinkStatus = 1;
	if(trim($PaymentLink) =="")
	{
		$PaymentLinkStatus = 0;
	}
	
	$InsertSQL = "INSERT INTO ".$Prefix."payment_log SET
			paymenttype			= :paymenttype,
			creditid 			= :creditid,
			razorpayinoviceid 	= :razorpayinoviceid,
			paylink 			= :paylink,
			response 			= :response,
			status 				= :status,
			createdon 			= :createdon
	";
	$InsertESQL = array(
			"paymenttype"		=> "smscredit",
			"creditid"			=> $CreditID,
			"razorpayinoviceid"	=> $TransactionInvoiceID,
			"paylink"			=> $PaymentLink,
			"response"			=> $response,
			"status"			=> $LinkStatus,
			"createdon" 		=> time()
			);

	pdo_query($InsertSQL,$InsertESQL);

	if($PaymentLinkStatus > 0)
	{
		$UpdateSQL = "UPDATE ".$Prefix."sms_credit_log SET paylink=:paylink,razorpayid=:razorpayid WHERE id=:id" ;
		$UpdateESQL = array("paylink"=>$PaymentLink,"razorpayid"=>$TransactionInvoiceID,"id"=>(int)$CreditID);
		pdo_query($UpdateSQL,$UpdateESQL);
	}

	return $PaymentLink;
}
function GetAllSMSPackages($clientid)
{
	global $Prefix;

	$RecordListArr	= array();

	$Sql	= "SELECT * FROM ".$Prefix."sms_packages WHERE deletedon < :deletedon";
	$Esql	= array("deletedon"=>1);

	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$index	= 0;

		while($rows = pdo_fetch_assoc($Query))
		{
			$id			= $rows['id'];
			$name		= $rows['name'];
		
			$RecordListArr[$id]['id']	= $id;
			$RecordListArr[$id]['name']	= $name;

		}
	}

	return $RecordListArr;
}
function GetAvailableSMSCredit($clientid)
{
	global $Prefix;

	$availablecredit		= 0;
	$totalsmscreditsused	= 0;

	$Checkcond	= " AND deletedon <:deletedon AND credittype=:credittype";
	$CheckEsql	= array("deletedon"=>1,"credittype"=>1);

	if($clientid > 0)
	{
		$Checkcond	.= " AND clientid=:clientid";
		$CheckEsql['clientid']	= (int)$clientid;
	}
	$CheckSql	= "SELECT * FROM ".$Prefix."sms_credit_log WHERE 1 ".$Checkcond." ORDER BY status DESC, createdon DESC";

	$CheckQuery	= pdo_query($CheckSql,$CheckEsql);
	$CheckNum	= pdo_num_rows($CheckQuery);

	if($CheckNum > 0)
	{
		while($checkrows = pdo_fetch_assoc($CheckQuery))
		{
			$type	= $checkrows['type'];
			$status	= $checkrows['status'];

			if($status > 0)
			{
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
	}

	$SqlCount	= "SELECT SUM(history.smscredit) AS C FROM ".$Prefix."campaign_history history,".$Prefix."campaign campaign WHERE campaign.id=history.campid AND history.clientid=:clientid AND history.issent >:issent AND ispayable > :ispayable";
	$EsqlCount	= array('clientid'=>(int)$clientid,'issent'=>0,"ispayable"=>0);

	$QueryCount	= pdo_query($SqlCount,$EsqlCount);
	$totalsmscreditsusedrow	= pdo_fetch_assoc($QueryCount);

	$SqlCountPayment	= "SELECT COUNT(*) AS C FROM ".$Prefix."customer_payments WHERE clientid=:clientid AND smsmresponse<>:smsmresponse AND smsmresponse IS NOT NULL AND ispayable > :ispayable";
	$EsqlCountPayment	= array('clientid'=>(int)$clientid,"smsmresponse"=>"","ispayable"=>0);

	$QueryCountPayment	= pdo_query($SqlCountPayment,$EsqlCountPayment);
	$totalsmscreditsusedpaymentrow	= pdo_fetch_assoc($QueryCountPayment);

	$totalsmscreditsused		+= $totalsmscreditsusedrow['C'];
	$totalsmscreditsused		+= $totalsmscreditsusedpaymentrow['C'];

	$totalsmscreditsavaiable	= ((int)$availablecredit - (int)$totalsmscreditsused);

	return $totalsmscreditsavaiable;
}
function CreatePaymentRequest($razorpayid, $paymentlink, $clientid)
{
	global $Prefix;

	$data_string	= json_encode($data);

	$url = "http://pay.orlopay.com/api/createpaymentrequest.php";

	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => [
			'paymentlink'	=>$paymentlink,
			'razorpayid'	=>$razorpayid,
			'clientid'		=>$clientid,
			'domain'		=>$_SERVER['SERVER_NAME'],
			'requestedtime'	=>time(),
		],
		CURLOPT_RETURNTRANSFER	=> true
	]);
	$output = curl_exec($ch);
	curl_close($ch);

	$responseData = json_decode($output);

	return $responseData;
}
function GetCustomerStatusBySubscription($id)
{
	global $Prefix;

	$SubscriptionSummaryArr	= array();

	$DataSet	= array();

	$subscriptionsql	= "SELECT inv.name,subs.quantity qty FROM ".$Prefix."subscriptions subs,".$Prefix."inventory inv WHERE subs.customerid=:customerid AND subs.inventoryid=inv.id AND inv.deletedon<:deletedon ORDER BY inv.name";
	$subscriptionesql	= array("customerid"=>(int)$id,"deletedon"=>1);

	$subscriptionquery	= pdo_query($subscriptionsql,$subscriptionesql);
	$subscriptionnum	= pdo_num_rows($subscriptionquery);

	$hassubscription	= false;
	$blockcolor			= "#ff0000";
	$statusclass		= "no-sorting";

	$subscriptionstr 	= '';
	if($subscriptionnum > 0)
	{
		$hassubscription	= true;
		$blockcolor			= "";
		$statusclass		= "activelist";

		/*while($subsrow	= pdo_fetch_assoc($subscriptionquery))
		{
			$inventoryname	 = $subsrow['name'];
			$qty			 = $subsrow['qty'];
			if($qty > 1)
			{
				$inventoryname = $inventoryname." X ".$qty;	
			}
			$subscriptionstr .= $inventoryname.', ';

			$SubscriptionSummaryArr[$inventoryname]	+= $qty;
		}
		$subscriptionstr .= '@@';
		$subscriptionstr = str_replace(", @@","",$subscriptionstr);
		$subscriptionstr = str_replace("@@","",$subscriptionstr);*/
	}
	else
	{
		$hassubscription	= false;
		$blockcolor			= "#ff0000";
		$statusclass		= "no-sorting";
		$subscriptionstr	= 'INACTIVE';
	}

	$DataSet['hassubscription']		= $hassubscription;
	$DataSet['blockcolor']			= $blockcolor;
	$DataSet['statusclass']			= $statusclass;
	/*$DataSet['subscriptionstr']	= $subscriptionstr;
	$DataSet['subscriptionsummary']	= $SubscriptionSummaryArr;*/

	return $DataSet;
}
?>