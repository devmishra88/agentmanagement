<?php
set_time_limit(0);
$ByPass = 1;
include_once "dbconfig.php";

if((int)$_GET['start'] < 1)
{
	$_GET['start'] = 1;
}

$offset = (int)($_GET['start'] -1) * 100 ;

$Limit = "LIMIT ".$offset.", 100";

$sql	= "SELECT * FROM ".$Prefix."customers WHERE deletedon < :deletedon ORDER BY id ASC $Limit";
$esql	= array("deletedon"=>1);
$query  = pdo_query($sql,$esql);
$num	= pdo_num_rows($query);
if($num > 0)
{
	while($row = pdo_fetch_assoc($query))
	{
		$CustomerID	= $row['id'];
		$ClientID	= $row['clientid'];

		$LedgerArr = GetLedgerByCustomerID($ClientID,$CustomerID,0,0);
		$OutstandingAmount = $LedgerArr['grandtotal'];
		
		echo $UpdateSQL	= "UPDATE ".$Prefix."customers SET outstandingbalance=:outstandingbalance WHERE id=:id";
		$UpdateESQL = array("id"=>(int)$CustomerID,"outstandingbalance"=>(float)$OutstandingAmount);
		print_r($UpdateESQL);
		pdo_query($UpdateSQL,$UpdateESQL);
		echo "<hr>";
	}
	echo $_GET['start'];
	$NewPage =($_GET['start']+1);
	sleep(1);
	?>
		<script>
			window.location.href="?start=<?php echo $NewPage;?>";
		</script>
	<?
	die;
}
?>