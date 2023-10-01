<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
	$ClientID 		= $_GET['clientid'];
	$LineID 		= $_GET['lineid'];
	$LinemanID 		= $_GET['linemanid'];
	$HawkerID 		= $_GET['hawkerid'];
	$AreaID 		= $_GET['areaid'];

	$currentdatetimestamp	= strtotime(date("m/d/Y"));

	$ExtArg 	= "";
	$ESQL 	= array();

	if($LineID > 0)
	{
		$ExtArg 	.= " AND (cus.lineid = :lineid)";
		$ESQL['lineid'] = $LineID;
	}
	if($LinemanID > 0)
	{
		$ExtArg 	.= " AND (cus.linemanid = :linemanid)";
		$ESQL['linemanid'] = $LinemanID;
	}
	if($HawkerID > 0)
	{
		$ExtArg 	.= " AND (cus.hawkerid = :hawkerid)";
		$ESQL['hawkerid'] = $HawkerID;
	}
	if($AreaID > 0)
	{
		$ExtArg 	.= " AND (cus.areaid = :areaid)";
		$ESQL['areaid'] = $AreaID;
	}

	$SQL = "SELECT cus.* FROM ".$Prefix."customers cus WHERE 1 AND cus.clientid=:clientid AND cus.deletedon < :deletedon ".$ExtArg." ORDER BY cus.sequence ASC, cus.customerid ASC";

	$ESQL['clientid']	= (int)$ClientID;
	$ESQL['deletedon']	= 1;
}

$Query			= pdo_query($SQL,$ESQL);
$Num			= pdo_num_rows($Query);
$TotalRecords	= $Num;

if($Num < 1)
{
	echo "<div align='center'><font color='#ff0000;'>No record Found</font></div>";
	die;
}
$loop		= 0;
/* Dummy Data */
$_POST['OrderType'] = 0;
/* Dummy Data */
if(trim(@$_GET['Mode']) != "SendToCustomer")
{
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<title><?=$appname;?></title>
	<meta name="Generator" content="EditPlus">
	<meta name="Author" content="">
	<meta name="Keywords" content="">
	<meta name="Description" content="">
	<style type="text/css">
		body
		{
			margin:0px;
			padding:0px;
			font-family:"Helvetica";
			font-size:<?php echo 14*$fonttobeincrease;?>px;
		}
        @media print
		{
			@page
			{
				size: A4;
			}
			.OrderWrapper
			{
				border:0px solid #000;
				margin-top:0.3cm;
				/*margin-right:0.5cm;
				margin-left:0.5cm;*/
				size: 8.5in 11in;
			}
		}
	</style>
	</head>

	<body <?if($_GET['bulkprinting'] =='1' && $_GET['downloadpdf'] !='1'){?>onload='window.print()'<?}?>>
	<?
}
else
{
	ob_start();
}

if($Num > 0)
{
	$AllAreaArr			= GetAllArea($ClientID);
	$AllLineArr			= GetAllLine($ClientID);
	$AllLineManArr		= GetAllLineman($ClientID);
	$AllHawkerArr		= GetAllHawker($ClientID);
	$GetAllSubLine		= GetAllSubLine($ClientID);
	$AllInventoryArr	= GetInventoryNames();

	$ReportAreaName = $AllAreaArr[$AreaID]['name'];
	$ReportLineName = $AllLineArr[$LineID]['name'];
	$ReportHawkerName = $AllHawkerArr[$HawkerID]['name'];

	$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
	$ClientESQL		= array("id"=>(int)$ClientID);

	$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
	$ClientNum		= pdo_num_rows($ClientQuery);
	
	if($ClientNum > 0)
	{
		$ClientRow		= pdo_fetch_assoc($ClientQuery);
		
		$AgentName		= $ClientRow['clientname'];
		$AgentAddress	= $ClientRow['invoiceaddress'];
		$AgentPhone		= $ClientRow['invoicephone'];
	}

	$SelectedFilterStr	= "";

	if(trim($ReportAreaName) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $ReportAreaName;
		}
		else
		{
			$SelectedFilterStr	.= " / ".$ReportAreaName;
		}
	}

	if(trim($ReportLineName) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $ReportLineName;
		}
		else
		{
			$SelectedFilterStr	.= " / ".$ReportLineName;
		}
	}

	if(trim($ReportHawkerName) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $ReportHawkerName;
		}
		else
		{
			$SelectedFilterStr	.= " / ".$ReportHawkerName;
		}
	}
	?>
	<div class="OrderWrapper" style="width:100%;"><!--  min-height:1440px; -->
		<div style="width:98%%;" align="center">
			<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center' valign='top'>
				<tr>
					<td valign='top' align='left' style="width:50%">
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold"><?php echo $AgentName?></div>
						</div>
						<br>
					</td>
					<td valign='top' align='right'>
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Sequency</div>
						</div>
						<br>
					</td>
				</tr>
				<tr>
					<td valign='top' align='center' colspan="2">
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 12*$fonttobeincrease;?>px;font-weight:bold">
							<?php echo $SelectedFilterStr;?></div>
						</div>
					</td>
				</tr>
			</table>
			<?php
			$RowNo			= 0;
			$LoopCounter	= 0;

			$RecordListArr			= array();
			$SubscriptionSummaryArr	= array();
			$totalsubscription		= 0;

			$recordindex	= 0;

			while($rows = pdo_fetch_assoc($Query))
			{
				$InventoryNameArr	= array();

				$id				= $rows['id'];
				$customerid		= $rows['customerid'];
				$name			= $rows['name'];
				$openingbalance	= $rows['openingbalance'];
				$address		= $rows['address1'];
				$housenumber	= $rows['housenumber'];
				$floor			= $rows['floor'];
				$sequence		= $rows['sequence'];
				$area			= $AllAreaArr[$rows['areaid']]['name'];
				$line			= $AllLineArr[$rows['lineid']]['name'];
				$lineman		= $AllLineManArr[$rows['linemanid']]['name'];
				$hawker			= $AllHawkerArr[$rows['hawkerid']]['name'];
				$sublinename	= $GetAllSubLine[$rows['sublineid']]['name'];

				$CustomerHolidayArr	= array();

				$holidaystartdate	= "";
				$holidayenddate		= "";

				$hascustomerholiday	= false;

				$HolidaySql		= "SELECT * FROM ".$Prefix."holidays_stock_linker WHERE clientid=:clientid AND customerid=:customerid";
				$HolidayEsql	= array("clientid"=>(int)$ClientID,"customerid"=>(int)$id);

				$HolidayQuery	= pdo_query($HolidaySql,$HolidayEsql);
				$HolidayNum		= pdo_num_rows($HolidayQuery);

				if($HolidayNum > 0)
				{
					$prevstockid	= "";

					while($holidayrows = pdo_fetch_assoc($HolidayQuery))
					{
						$stockid	= $holidayrows['stockid'];
						$startdate	= $holidayrows['startdate'];
						$enddate	= $holidayrows['enddate'];

						if(!empty($CustomerHolidayArr))
						{
							$oldstartdate	= $CustomerHolidayArr[$stockid]['startdate'];
							$oldenddate		= $CustomerHolidayArr[$stockid]['enddate'];

							if(($oldstartdate < $startdate) && $oldstartdate > 0)
							{
								$startdate	= $oldstartdate;
							}

							if(($oldenddate > $enddate) && $oldenddate > 0)
							{
								$enddate	= $oldenddate;
							}
						}

						$CustomerHolidayArr[$stockid]['startdate']	= $startdate;
						$CustomerHolidayArr[$stockid]['enddate']	= $enddate;

						$holidaystartdate	= $startdate;
						$holidayenddate		= $enddate;
					}
				}

				if(($currentdatetimestamp > $holidaystartdate && $currentdatetimestamp < $holidayenddate) && $holidaystartdate > 0 && $holidayenddate > 0)
				{
					$hascustomerholiday	= true;
				}

				if(!$hascustomerholiday)
				{
					$SubscriptionSql	= "SELECT * FROM ".$Prefix."subscriptions WHERE customerid=:customerid";
					$SubscriptionEsql	= array("customerid"=>(int)$id);

					$SubscriptionQuery	= pdo_query($SubscriptionSql,$SubscriptionEsql);
					$SubscriptionNum	= pdo_num_rows($SubscriptionQuery);

					if($SubscriptionNum > 0)
					{
						while($subscriptionrows = pdo_fetch_assoc($SubscriptionQuery))
						{
							$inventoryid	= $subscriptionrows['inventoryid'];
							$quantity		= $subscriptionrows['quantity'];
							$inventoryname  = $AllInventoryArr[$inventoryid]['name'];

							$SubscriptionSummaryArr[$inventoryname]	+= $quantity;
							$totalsubscription	+= $quantity;

							if($quantity > 1)
							{
								$inventoryname .= " X ".$quantity;
							}
							$InventoryNameArr[]	= $inventoryname;
						}
					}

					$InventoryNameStr	= "---";

					if(!empty($InventoryNameArr))
					{
						$InventoryNameStr	= @implode(", ",@array_filter(@array_unique($InventoryNameArr)));
					}
				}
				else
				{
					$holidaystr	= "";

					$startdatestr	= date("d-M-Y",$holidaystartdate);
					$enddatestr		= date("d-M-Y",$holidayenddate);

					if($startdatestr == $enddatestr)
					{
						$holidaystr	= $startdatestr;
					}
					else
					{
						$holidaystr	= $startdatestr." to ".$enddatestr;
					}

					$InventoryNameStr	= 'Holiday : '.$holidaystr;
				}

				$addressstr = '';
		
				if($housenumber !='')
				{
					$addressstr .= $housenumber;
				}
				if($floor !='')
				{
					$ext = '';
					if($floor !='Basement')
					{
						$ext = 'floor';
					}
					if($floor =='Ground')
					{
						$floor	= "G.";
						$ext	= 'F.';
					}
					if($addressstr !='')
					{
						$addressstr .= ", ".$floor." ".$ext;
					}
					else
					{
						$addressstr .= $floor." ".$ext;
					}
				}
				if($address !='')
				{
					if($addressstr !='')
					{
						$addressstr .= ", ".$address;
					}
					else
					{
						$addressstr .= $address;
					}
				}
				if($sublinename !='')
				{
					if($addressstr !='')
					{
						$addressstr .= ", ".$sublinename;
					}
					else
					{
						$addressstr .= $sublinename;
					}
				}

				if(trim($addressstr) =='')
				{
					$addressstr = '--';
				}

				$name2	= "#".$customerid." ".$name;

				$RecordListArr[$recordindex]['name']			= $name2;
				$RecordListArr[$recordindex]['address']			= $addressstr;
				$RecordListArr[$recordindex]['inventoryname']	= $InventoryNameStr;

				$recordindex++;
			}
			?>
			<hr style="color:#000; background-color:#000; height:1px; border:0px; -webkit-print-color-adjust:exact;" />
			<b>Summary :</b> <?php echo $totalsubscription;?>
			<br/><br/>
			<?php
			ksort($SubscriptionSummaryArr, SORT_STRING);

			$stocksummarystr = "";
			foreach($SubscriptionSummaryArr as $stockname => $quantity)
			{
				if(trim($stocksummarystr) == "")
				{
					$stocksummarystr	.= $stockname." - ".$quantity;
				}
				else
				{
					$stocksummarystr	.= ", ".$stockname." - ".$quantity;
				}
			}
			echo $stocksummarystr;
			?>
			<br /><br />
			<b>Total Customers :</b> <?php echo $TotalRecords;?>
			<br />
			<hr style="color:#000; background-color:#000; height:1px; border:0px; -webkit-print-color-adjust:exact;" />
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
					<td style="border:1px solid #000;" align='center' valign="top" width='40px'>S.No.</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="180px">Customer</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="200px">Address</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="220px">Particular(s)</td>
				</tr>
				<?php
				foreach($RecordListArr as $recordkey=>$recordrows)
				{
					?>
					<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
						<td style="border:1px solid #000;" align='center' valign="middle">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border:0px;">
								<tr>
									<td align='center' valign="middle" style="border:0px;">
										<?=$j+$LoopCounter+1;?>
									</td>
								</tr>
							</table>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<? echo $recordrows['name'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $recordrows['address'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $recordrows['inventoryname'];?></td>
					</tr>
					<?php
					$LoopCounter++;
				}
				?>
			</table>
		</div>
	</div>
	<?
}
?>