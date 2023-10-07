<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$catindex	= 0;
$RecordListArr	= array();

$fonttobeincrease	= 1.5;

$_POST	= $_GET;

$summarytotal	= array();

if($_GET['bulkprinting'] == '1')
{
	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$catindex	= 0;
	$RecordListArr	= array();

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	if(trim($_POST['startdate']) != "" && trim($_POST['enddate']) != "")
	{
		$StartDate	= strtotime($_POST['startdate']);
		$EndDate	= strtotime($_POST['enddate'])+86399;

		$Condition	.= " AND details.createdon BETWEEN :startdate AND :enddate";

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

	if($_POST['inventoryid'] > 0)
	{
		$Condition	.= " AND details.inventoryid=:inventoryid";
		$ESQL['inventoryid']	= (int)$_POST['inventoryid'];
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

	$SQL	= "SELECT details.qty AS itemqty,details.inventoryid AS inventoryid,details.inventoryname AS inventoryname FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllAreaArr		= GetAllArea($_POST['clientid']);
		$AllLineArr		= GetAllLine($_POST['clientid']);
		$AllSubLineArr	= GetAllSubLine($_POST['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];

			$itemqty		= $rows['itemqty'];
			$inventoryid	= $rows['inventoryid'];
			$inventoryname	= $rows['inventoryname'];

			$RecordListArr[$inventoryid]['inventoryid']	= $inventoryid;
			$RecordListArr[$inventoryid]['name']		= $inventoryname;
			$RecordListArr[$inventoryid]['quantity']	+= $itemqty;
		}
	}

	$lineindex	= 0;
	$lineDetail	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $inventoryid => $summaryrow)
		{
			if($summaryrow['quantity'] > 0)
			{
				$lineDetail[$lineindex]['serialno']		= $lineindex+1;
				$lineDetail[$lineindex]['id']			= $summaryrow['inventoryid'];
				$lineDetail[$lineindex]['name']			= $summaryrow['name'];
				$lineDetail[$lineindex]['quantity']		= $summaryrow['quantity'];

				$TotalRec++;
				$lineindex++;
			}
		}
	}
}

if(empty($lineDetail))
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
if(!empty($lineDetail))
{
	$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
	$ClientESQL		= array("id"=>(int)$_GET['clientid']);

	$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
	$ClientNum		= pdo_num_rows($ClientQuery);

	if($ClientNum > 0)
	{
		$ClientRow		= pdo_fetch_assoc($ClientQuery);
		
		$AgentName		= $ClientRow['clientname'];
		$AgentAddress	= $ClientRow['invoiceaddress'];
		$AgentPhone		= $ClientRow['invoicephone'];
	}

	$AllAreaArr		= GetAllArea($_GET['clientid']);
	$AllLineArr		= GetAllLine($_GET['clientid']);
	$AllHawkerArr	= GetAllHawker($_GET['clientid']);

	$SelectedFilterStr	= "";

	if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= date("j-M-Y", $StartDate)." - ".date("j-M-Y", $EndDate);
		}
		else
		{
			$SelectedFilterStr	.= " / ".date("j-M-Y", $StartDate)." - ".date("j-M-Y", $EndDate);
		}
	}

	if($_GET['areaid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllAreaArr[$_GET['areaid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllAreaArr[$_GET['areaid']]['name'];
		}
	}

	if($_GET['lineid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllLineArr[$_GET['lineid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllLineArr[$_GET['lineid']]['name'];
		}
	}

	if($_GET['hawkerid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllHawkerArr[$_GET['hawkerid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllHawkerArr[$_GET['hawkerid']]['name'];
		}
	}
	if(@trim($_GET['inventoryid']) > 0 && @trim($_GET['inventoryname']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $_GET['inventoryname'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$_GET['inventoryname'];
		}
	}
	?>
	<div class="OrderWrapper" style="width:100%;"><!--  min-height:1440px; -->
		<div style="width:98%;" align="center">
			<table border='0' cellpadding='0' cellspacing='5' width='100%' align='center' valign='top'>
				<tr>
					<td valign='top' align='left' style="width:50%">
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold"><?php echo $AgentName?></div>
						</div>
						<br>
					</td>
					<td valign='top' align='right'>
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Magazine Sale Summary</div>
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
			<br style="clear:both;" />
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
					<td style="border:1px solid #000;" align='center' valign="top" width='40px'>S. No</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="120px">Magazine</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="100px">Quantity</td>
				</tr>
				<?php
				$RowNo			= 0;
				$LoopCounter	= 0;

				foreach($lineDetail as $RecordListKey => $RecordListRows)
				{
					?>
					<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
						<td style="border:1px solid #000;" align='center' valign="middle">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-height:50px; border:0px;">
								<tr>
									<td align='center' valign="middle" style="border:0px;">
										<b><?=$LoopCounter+1;?></b>
									</td>
								</tr>
							</table>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $RecordListRows['name'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $RecordListRows['quantity'];?>
						</td>
					</tr>
					<?
					$LoopCounter++;
				}
				?>
			</table>
			<table border='0' cellpadding='2' cellspacing='2' width='100%'>
				<tr>
					<td valign='bottom' align='left'>
					</td>
					<td valign='top' align='right'>
					<br>
						<div style="font-size:<?php echo 22*$fonttobeincrease;?>px;">
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?
}
?>