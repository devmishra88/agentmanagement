<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$_POST	= $_GET;

$RecordListArr	= array();
$DateDetail		= array();

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
	$AllMagazineCatArr	= GetAllMagazineCategoryID();

	$AllMagazineCatIDStr	= implode(",",$AllMagazineCatArr);

	if(trim($AllMagazineCatIDStr) == "")
	{
		$AllMagazineCatIDStr	= "-1";
	}

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1);

	if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
	{
		$StartDate	= $_GET['startdate_strtotime'];
		$EndDate	= $_GET['enddate_strtotime'];

		$Condition	.= " AND details.createdon BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;
	}

	if($_GET['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_GET['areaid'];
	}

	if($_GET['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_GET['lineid'];
	}

	if($_GET['linemanid'] > 0)
	{
		$Condition	.= " AND cust.linemanid=:linemanid";
		$ESQL['linemanid']	= (int)$_GET['linemanid'];
	}

	if($_GET['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_GET['hawkerid'];
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

	$SQL	= "SELECT cust.*,details.qty AS itemqty,details.price AS itemprice,details.inventoryname AS inventoryname,details.totalprice AS totalprice,details.createdon AS invoicedate,details.id AS detailid FROM ".$Prefix."customers cust, ".$Prefix."invoice_details details WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=details.customerid AND details.inventorycatid IN(".$AllMagazineCatIDStr.") ".$Condition." GROUP BY details.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllAreaArr	= GetAllArea($_GET['clientid']);
		$AllLineArr	= GetAllLine($_GET['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id				= $rows['id'];
			$customerid		= $rows['customerid'];
			$name			= $rows['name'];
			$address		= $rows['address1'];

			$itemqty		= $rows['itemqty'];
			$itemprice		= $rows['itemprice'];
			$inventoryname	= $rows['inventoryname'];
			$totalprice		= $rows['totalprice'];
			$invoicedate	= $rows['invoicedate'];
			$detailid		= $rows['detailid'];

			$invoicedate2	= strtotime(date("Y-m-d", $invoicedate));

			$areaid			= $rows['areaid'];
			$lineid			= $rows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$name2	= "#".$customerid." ".$name;

			$RecordListArr[$invoicedate2]['datetotal']	+= $totalprice;
			$RecordListArr[$invoicedate2]['dateqty']	+= $itemqty;

			$RecordListArr[$invoicedate2]['detail'][$detailid]['id']			= $id;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['customerid']	= $customerid;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['name']			= $name2;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['area']			= $areaname;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['line']			= $linename;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['address']		= $address;

			$RecordListArr[$invoicedate2]['detail'][$detailid]['itemqty']		= $itemqty;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['itemprice']		= $itemprice;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['inventoryname']	= $inventoryname;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['totalprice']	= $totalprice;
			$RecordListArr[$invoicedate2]['detail'][$detailid]['invoicedate']	= $invoicedate;
		}
	}
	if(!empty($RecordListArr))
	{
		$invoiceindex	= 0;

		foreach($RecordListArr as $invoicedate => $invoicerows)
		{
			$index	= 0;
			$Detail	= array();

			if(!empty($invoicerows['detail']))
			{
				foreach($invoicerows['detail'] as $detailkey=>$detailrows)
				{
					$Detail[$index]['serialno']			= $index+1;
					$Detail[$index]['id']				= $detailrows['id'];
					$Detail[$index]['customerid']		= $detailrows['customerid'];
					$Detail[$index]['name']				= $detailrows['name'];
					$Detail[$index]['area']				= $detailrows['area'];
					$Detail[$index]['line']				= $detailrows['line'];
					$Detail[$index]['address']			= $detailrows['address'];
					$Detail[$index]['itemqty']			= $detailrows['itemqty'];
					$Detail[$index]['itemprice']		= number_format($detailrows['itemprice'],2);
					$Detail[$index]['inventoryname']	= $detailrows['inventoryname'];
					$Detail[$index]['totalprice']		= number_format($detailrows['totalprice'],2);
					$Detail[$index]['invoicedate']		= date("j F, Y",$detailrows['invoicedate']);

					$index++;
				}
			}
			if(!empty($Detail))
			{
				$DateDetail[$invoiceindex]['invoicedate']	= $invoicedate;
				$DateDetail[$invoiceindex]['dateqty']		= $invoicerows['dateqty'];
				$DateDetail[$invoiceindex]['name']			= date("j F, Y",$invoicedate);
				$DateDetail[$invoiceindex]['totalpayment']	= number_format($invoicerows['datetotal'],2);
				$DateDetail[$invoiceindex]['details']		= $Detail;

				$invoiceindex++;
			}
		}
	}
}

if(empty($DateDetail))
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
if(!empty($DateDetail))
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
	?>
	<div class="OrderWrapper" style="width:100%;"><!--  min-height:1440px; -->
		<div style="width:98%%;" align="center">
			<table border='0' cellpadding='0' cellspacing='5' width='100%' align='center' valign='top'>
				<tr>
					<td valign='top' align='center'>
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:<?php echo 24*$fonttobeincrease;?>px;"><?php echo $AgentName?></div>
							<?if($AgentAddress !=""){?><div style="font-size:<?php echo 18*$fonttobeincrease;?>px;"><?php echo nl2br($AgentAddress);?></div><?}?>
							<?if($AgentPhone !=""){?><div style="font-size:<?php echo 14*$fonttobeincrease;?>px;">Mob: <?php echo $AgentPhone;?></div><?}?>
						</div>
						<br>
					</td>
				</tr>
			</table>
			<hr style="color:#000; background-color:#000; height:1px; border:0px; -webkit-print-color-adjust:exact;" />
			<div style="float:right;">
				<?=$Type;?>
			</div>
			<br style="clear:both;" />
			<div style="font-size:<?php echo 18*$fonttobeincrease;?>px;">
				<b>Magazine Sale Detail</b>
			</div>
			<br style="clear:both;" />
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<?php
				foreach($DateDetail as $invoicedate => $invoicerows)
				{
					if(!empty($invoicerows['details']))
					{
						$RowNo			= 0;
						$LoopCounter	= 0;
						?>
						<tr style='font-size:<?php echo 18*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='center' valign="middle" colspan="7">
								<b><?php echo $invoicerows['name'];?></b>
							</td>
						</tr>
						<tr style='font-size:<?php echo 16*$fonttobeincrease;?>px; color: #fff;line-height:17px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
							<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Magazine</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="90px">Area</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="90px">Line</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="150px">Address</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="50px">Copy</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="80px">Rate</td>
							<td style="border:1px solid #000;" align='center' valign="top"  width="100px">Amount</td>
						</tr>
						<?php
						foreach($invoicerows['details'] as $detailkey =>$detailrows)
						{
							?>
							<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
								<td style="border:1px solid #000;" align='center' valign="middle">
									<table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-height:50px; border:0px;">
										<tr>
											<td align='center' valign="middle" style="border:0px;">
												<b><?=$detailrows['inventoryname'];?></b>
											</td>
										</tr>
									</table>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['area'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['line'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['address'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['itemqty'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['itemprice'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $detailrows['totalprice'];?></td>
							</tr>
							<?php
							$LoopCounter++;
						}
						?>
						<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='right' valign="middle" colspan="4">
								<b>Date Wise Total</b>
							</td>
							<td style="border:1px solid #000;" align='right' valign="middle">
								<?php echo $invoicerows['dateqty'];?>
							</td>
							<td style="border:1px solid #000;" align='right' valign="middle">
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $invoicerows['totalpayment'];?></td>
						</tr>
						<?
					}
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