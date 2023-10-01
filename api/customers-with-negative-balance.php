<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

?>
<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>Customers With Negative Outstanding</title>
 </head>
 <body>
  <table cellpadding="5" cellspacing="0" border="1" width="80%" align="center">
	<tr>
		<td align="center">Customer Name</td>
		<td align="center">Customer Mobile</td>
		<td align="center">Opening Balance</td>
		<td align="center">Outstanding</td>
		<td align="center">Options</td>
	</tr>
	<?php
		$SQL	= "SELECT * FROM ".$Prefix."customers WHERE outstandingbalance < :outstandingbalance AND deletedon < :deletedon";
		$ESQL	= array("outstandingbalance"=>0,"deletedon"=>1);
		$Query  = pdo_query($SQL,$ESQL);
		$Num	= pdo_num_rows($Query);
		if($Num > 0)
		{
			while($row = pdo_fetch_assoc($Query))
			{
				$ID			= $row['id'];
				$ClientID	= $row['clientid'];
				$Name		= $row['name'];
				$Mobile		= $row['phone'];
				$CustomerID	= $row['customerid'];
				$OutStanding= $row['outstandingbalance'];
				$OpeningBalance	= $row['openingbalance'];
		?>
		<tr>
			<td align="center"><?php echo $Name.' ( '.$CustomerID.' )';?> </td>
			<td align="center"><?php echo $Mobile;?></td>
			<td align="center"><?php echo $OpeningBalance;?></td>
			<td align="center"><?php echo $OutStanding;?></td>
			<td align="center"><a href="#" onclick="window.open('viewledger.php?customerid=<?php echo $ID;?>&clientid=<?php echo $ClientID;?>&startdate_strtotime=0&enddate_strtotime=<?php echo time();?>&bulkprinting=1&frompage=negative','viewleader','width=800,height=400,resizable=1');return false;">View Ledger</a></td>
		</tr>
		<?
			}
		}
	?>
  </table>
 </body>
</html>
