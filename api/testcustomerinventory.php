<?php
$ByPass = 1;
set_time_limit(0);
include_once "dbconfig.php";

echo "script start time:--".date("h:i:s A")."<br/>";

$_POST['clientid'] = 10;
$startdate	= strtotime("06/02/2023");
$enddate	= $startdate + 86399;

$ExtArgArr = array();
$sql_drop	= "SELECT * FROM ".$Prefix."dropping_point WHERE deletedon < :deletedon AND status =:status AND clientid=:clientid";
$esql_drop	= array("status"=>1,"deletedon"=>1,"clientid"=>(int)$_POST['clientid']);
$TempArr	= array_merge($ExtArgArr,$esql_drop);
$query_drop = pdo_query($sql_drop,$TempArr);
$num_drop	= pdo_num_rows($query_drop);

$DropingPointArr_Circulation = array();

if($num_drop > 0)
{
	while($row_drop = pdo_fetch_assoc($query_drop))
	{
		$DropingPointID	=	$row_drop['id'];
		
		$sql_purchase	= "SELECT SUM(noofpices) as C FROM ".$Prefix."purchase WHERE clientid=:clientid AND droppingpointid=:droppingpointid AND purchasedate BETWEEN :date1 AND :date2";
		$esql_purchase	= array("date1"=>$startdate,"date2"=>$enddate,"clientid"=>(int)$_POST['clientid'],"droppingpointid"=>(int)$DropingPointID);

		$query_purchase	= pdo_query($sql_purchase,$esql_purchase);
		$row_purchase	= pdo_fetch_assoc($query_purchase);

		$DropingPointArr_Circulation[$DropingPointID]['purchase']		= (int)$row_purchase['C'];

		$sql_area	= "SELECT GROUP_CONCAT(id) as areaids FROM ".$Prefix."area WHERE droppingpointid=:droppingpointid AND clientid=:clientid AND deletedon < :deletedon";
		$esql_area	= array("droppingpointid"=>(int)$DropingPointID,"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);
		$query_area	= pdo_query($sql_area,$esql_area);
		$row_area	= pdo_fetch_assoc($query_area);
		$AreaIDs	= $row_area['areaids'];
		if($AreaIDs == '')
		{
			$AreaIDs = '-1';
		}

		$sql = "SELECT * FROM orlop_customers WHERE clientid=:clientid AND areaid IN (".$AreaIDs.") AND deletedon < :deletedon order by id DESC LIMIT 10000";

		$esql = array("clientid"=>$_POST['clientid'],"deletedon"=>1);

		$query = pdo_query($sql,$esql);

		$TotalQty = 0;
		while($row = pdo_fetch_assoc($query))
		{
			$customerid = $row['id'];
			$QtyArr = getCustomerSubscriptionLog($_POST['clientid'],$customerid,$startdate);
			
			foreach($QtyArr as $key => $value)
			{
				$TotalQty += $value;
			}
		}
		$DropingPointArr_Circulation[$DropingPointID]['circulation']	= $TotalQty;

	}
}
echo "<pre>";
print_r($DropingPointArr_Circulation);

echo "script end time:--".date("h:i:s A");
die;


?>