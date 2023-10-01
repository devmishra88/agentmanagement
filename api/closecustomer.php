<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$SubscriptionSummaryArr	= array();

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
	$_POST	= $_GET;

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= $_POST['startdate'];
		$EndDate	= $_POST['enddate'];

		/*$Condition	.= " AND log.subscriptiondate BETWEEN :startdate AND :enddate AND log.unsubscribedate BETWEEN :startdate2 AND :enddate2 ";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;

		$ESQL['startdate2']	= $StartDate;
		$ESQL['enddate2']	= $EndDate;*/

		$Condition	.= " AND log.unsubscribedate BETWEEN :startdate2 AND :enddate2 ";

		$ESQL['startdate2']	= $StartDate;
		$ESQL['enddate2']	= $EndDate;
	}

	if($_POST['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND log.inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
	}

	if($_POST['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Condition	.= " AND cust.areaid IN (".$areaids.")";
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

	$SQL	= "SELECT cust.*,log.id as logid,log.inventoryid as inventoryid,log.subscriptiondate AS logsubscriptiondate,log.unsubscribedate AS logunsubscribedate FROM ".$Prefix."customers cust, ".$Prefix."subscriptions_log log WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=log.customerid ".$Condition." GROUP BY log.id ORDER BY cust.sequence ASC, cust.customerid ASC";
}

$Query	= pdo_query($SQL,$ESQL);
$Num	= pdo_num_rows($Query);

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
	$AllAreaArr			= GetAllArea($_POST['clientid']);
	$AllLineArr			= GetAllLine($_POST['clientid']);
	$AllInventoryArr	= GetInventoryNames();
	$AllSubLine			= GetAllSubLine($_POST['clientid']);

	$SelectedFilterStr	= "";

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= date("d-M-Y",$_POST['startdate'])." - ".date("d-M-Y",$_POST['enddate']);
		}
		else
		{
			$SelectedFilterStr	.= " / ".date("d-M-Y",$_POST['startdate'])." - ".date("d-M-Y",$_POST['enddate']);
		}
	}

	if($_POST['areaid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllAreaArr[$_POST['areaid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllAreaArr[$_POST['areaid']]['name'];
		}
	}

	if($_POST['lineid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllLineArr[$_POST['lineid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllLineArr[$_POST['lineid']]['name'];
		}
	}

	if($_POST['inventoryid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllInventoryArr[$_POST['inventoryid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllInventoryArr[$_POST['inventoryid']]['name'];
		}
	}

	$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
	$ClientESQL		= array("id"=>(int)$_POST['clientid']);

	$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
	$ClientNum		= pdo_num_rows($ClientQuery);
	
	if($ClientNum > 0)
	{
		$ClientRow		= pdo_fetch_assoc($ClientQuery);

		$AgentName		= $ClientRow['clientname'];
		$AgentAddress	= $ClientRow['invoiceaddress'];
		$AgentPhone		= $ClientRow['invoicephone'];
	}

	$RecordSetArr	= array();
	$index			= 0;

	while($rows = pdo_fetch_assoc($Query))
	{
		$logid					= $rows['logid'];
		$custid					= $rows['id'];
		$customerid				= $rows['customerid'];
		$name					= $rows['name'];
		$logsubscriptiondate	= $rows['logsubscriptiondate'];
		$logunsubscribedate		= $rows['logunsubscribedate'];
		$inventoryid			= $rows['inventoryid'];
		$areaid					= $rows['areaid'];
		$lineid					= $rows['lineid'];
		$phone					= $rows['phone'];
		$housenumber			= $rows['housenumber'];
		$floor					= $rows['floor'];
		$address1				= $rows['address1'];
		$sublinename			= $AllSubLine[$rows['sublineid']]['name'];

		$name2					= "#".$customerid." ".$name;

		if(trim($phone) == "")
		{
			$phone	= "---";
		}

		$addresstr = '';
		
		if($housenumber !='')
		{
			$addresstr .= $housenumber;
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
			if($addresstr !='')
			{
				$addresstr .= ", ".$floor." ".$ext;
			}
			else
			{
				$addresstr .= $floor." ".$ext;
			}
		}
		if($address1 !='')
		{
			if($addresstr !='')
			{
				$addresstr .= ", ".$address1;
			}
			else
			{
				$addresstr .= $address1;
			}
		}
		if($sublinename !='')
		{
			if($addresstr !='')
			{
				$addresstr .= ", ".$sublinename;
			}
			else
			{
				$addresstr .= $sublinename;
			}
		}

		if(trim($addresstr) =='')
		{
			$addresstr = '--';
		}

		$inventoryname	= $AllInventoryArr[$inventoryid]['name'];

		$RecordSetArr[$index]['name']				= $name2;
		$RecordSetArr[$index]['phone']				= $phone;
		$RecordSetArr[$index]['address']			= $addresstr;
		$RecordSetArr[$index]['arealinestr']		= $AllAreaArr[$areaid]['name']." / ".$AllLineArr[$lineid]['name'];
		$RecordSetArr[$index]['inventory']			= $inventoryname;
		$RecordSetArr[$index]['subscriptiondate']	= date("d-M-Y",$logsubscriptiondate)." - ".date("d-M-Y",$logunsubscribedate);

		$SubscriptionSummaryArr[$inventoryname]	+= 1;

		$index++;
	}
	?>
	<div class="OrderWrapper" style="width:100%;"><!--  min-height:1440px; -->
		<div style="width:98%;" align="center">
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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Close Customer</div>
						</div>
						<br>
					</td>
				</tr>
				<tr>
					<td valign='top' align='center' colspan="2">
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 12*$fonttobeincrease;?>px;font-weight:bold"><?php echo $SelectedFilterStr;?></div>
						</div>
					</td>
				</tr>
			</table>
			<hr style="color:#000; background-color:#000; height:1px; border:0px; -webkit-print-color-adjust:exact;" />
			<?
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
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
					<td style="border:1px solid #000;" align='center' valign="top" width='20px'>S.No.</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="120px">Customer</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="50px">Phone</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="150px">Address</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="100px">Area / Line</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="80px">Stock</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="110px">Subscription Period</td>
				</tr>
				<?php
				$RowNo			= 0;
				$LoopCounter	= 0;

				foreach($RecordSetArr as $key=>$rows)
				{
					$name					= $rows['name'];
					$phone					= $rows['phone'];
					$address				= $rows['address'];
					$arealinestr			= $rows['arealinestr'];
					$inventory				= $rows['inventory'];
					$subscriptiondate		= $rows['subscriptiondate'];
					?>
					<tr style='font-size:<?php echo 7*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
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
							<? echo $name;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<? echo $phone;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $address;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $arealinestr;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $inventory;?></td>
						<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $subscriptiondate;?></td>
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