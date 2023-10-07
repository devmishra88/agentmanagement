<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
set_time_limit(0);
/*set response code - 200 OK*/
http_response_code(200);

include_once "dbconfig.php";

$clientid = 10;
if($_GET['step'] == 1)
{
	$condition	= "";
	
	if($_GET['id'] > 0)
	{
		$id = $_GET['id'];
	}
	$CustESQL	= array("clientid"=>(int)$clientid,"deletedon"=>1);


	$OffSet = 0;
	
	if((int)$_GET['coutner'] > 0)
	{
		$OffSet = (int)$_GET['coutner'] - 1;
	}

	$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE 1  AND clientid=:clientid AND deletedon < :deletedon ORDER BY id ASC LIMIT $OffSet,10 ";

	$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE 1  AND clientid=:clientid AND deletedon < :deletedon ORDER BY id ASC  ";
	
	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	$Counter = (int)$_GET['counter'] + 10;
	if($CustNum > 0)
	{
		while($CustRow	= pdo_fetch_assoc($CustQuery))
		{
			$recordid					= $CustRow['id'];
			
			$outstandingbalance	= getOutstandingBalanceByCustomer($clientid, $recordid);

			$UpdateSql	= "UPDATE ".$Prefix."customers SET outstandingbalance=:outstandingbalance WHERE id=:id";
			$UpdateEsql	= array("outstandingbalance"=>$outstandingbalance,"id"=>(int)$recordid);

			$UpdateQuery	= pdo_query($UpdateSql,$UpdateEsql);
	
		echo $recordid."<br/>";
		}
		//header("location:?step=1&coutner=".$Counter);
		//die;
	}
	echo "done";
}
else if($_GET['step'] == 2)
{
	$invoicemonthyear	= "2022-06-01";

	$CampaignStatusArr	= ScheduleInvoiceSMSCampaign($clientid,$invoicemonthyear);
	
	echo "done2";
}
?>