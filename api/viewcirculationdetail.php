<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$RecordListArr	= array();
$DateDetail		= array();

$_POST	= $_GET;

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
	$catindex	= 0;
	$RecordListArr	= array();

	$StartDate	= $_GET['startdate_strtotime'];
	$CheckDate	= $_GET['enddate_strtotime'];

	$Sql	= "SELECT * FROM ".$Prefix."client_inventory_linker WHERE clientid=:clientid AND status=:status";
	$Esql	= array("clientid"=>(int)$_GET['clientid'],"status"=>1);
	$Query	= pdo_query($Sql,$Esql);
	$Num	= pdo_num_rows($Query);
	
	$InventoryCiculationArr = array();
	$InventoryPurchaseArr 	= array();
	$AllInventoryArr		= array();
	$AllInventoryNameArr	= GetInventoryNames();

	if($Num > 0)
	{
		while($Row = pdo_fetch_assoc($Query))
		{
			$InventoryID				= $Row["inventoryid"];
			
			$InventoryCiculationArr[$InventoryID] = 0;

			$AllInventoryArr[] = $InventoryID;

			$CheckESQL	= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1,'subscriptiondate'=>1,'checkdate'=>(int)$CheckDate,'inventoryid'=>(int)$InventoryID);

			$CheckCondition	= "";

			if($_GET['lineid'] > 0)
			{
				$CheckCondition	.= " AND cust.lineid=:lineid";
				$CheckESQL['lineid']	= (int)$_GET['lineid'];
			}

			if($_GET['areaid'] > 0)
			{
				$CheckCondition	.= " AND cust.areaid=:areaid";
				$CheckESQL['areaid']	= (int)$_GET['areaid'];
			}

			if($_GET['linemanid'] > 0)
			{
				$CheckCondition	.= " AND cust.linemanid=:linemanid";
				$CheckESQL['linemanid']	= (int)$_GET['linemanid'];
			}

			if($_GET['hawkerid'] > 0)
			{
				$CheckCondition	.= " AND cust.hawkerid=:hawkerid";
				$CheckESQL['hawkerid']	= (int)$_GET['hawkerid'];
			}

			if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
			{
				$areaids	= $_POST['areaids'];

				if(trim($areaids) == "")
				{
					$areaids	= "-1";
				}

				$CheckCondition	.= " AND cust.areaid IN(".$areaids.")";
			}
			if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
			{
				$lineids	= $_POST['lineids'];

				if(trim($lineids) == "")
				{
					$lineids	= "-1";
				}

				$CheckCondition	.= " AND cust.lineid IN(".$lineids.")";
			}

			$CheckSQL	= "SELECT cust.*,subs.quantity as qty FROM ".$Prefix."customers cust, ".$Prefix."subscriptions subs WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.id=subs.customerid AND (subs.subscriptiondate <:subscriptiondate || subs.subscriptiondate <:checkdate) AND subs.inventoryid =:inventoryid ".$CheckCondition." GROUP BY cust.id ORDER BY cust.sequence ASC, cust.customerid ASC";
			
			$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
			$CheckNum	= pdo_num_rows($CheckQuery);
			if($CheckNum > 0)
			{
				while($CustRow		= pdo_fetch_assoc($CheckQuery))
				{	
					$CustomerID		= $CustRow['id'];
					$Qty			= $CustRow['qty'];
					if($Qty < 1)
					{
						$Qty =1;
					}
					
					$IsHoliday		= IsHoliday($CheckDate,$CustomerID,$InventoryID);

					if($IsHoliday < 1)
					{
						$InventoryCiculationArr[$InventoryID] += $Qty; 	
					}
				}
			}
			$CheckSQL	= "SELECT SUM(noofpices) as qty FROM ".$Prefix."purchase WHERE inventoryid=:inventoryid AND purchasedate=:purchasedate AND clientid=:clientid";
			$CheckESQL  = array('inventoryid'=>(int)$InventoryID,"purchasedate"=>(int)$CheckDate,"clientid"=>(int)$_GET['clientid']);
			$CheckQuery	= pdo_query($CheckSQL,$CheckESQL);
			$CheckRow	= pdo_fetch_assoc($CheckQuery);
			$PurchaseQty = $CheckRow['qty'];
			$InventoryPurchaseArr[$InventoryID] = $PurchaseQty;
		}
	}
	$InventoryStr = '-1';
	if(!empty($AllInventoryArr))
	{
		$InventoryStr = implode(",",$AllInventoryArr);
	}

	$CategorySql	= "SELECT cat.* FROM ".$Prefix."category cat, ".$Prefix."inventory inv WHERE cat.status=:status AND cat.id=inv.categoryid AND inv.id IN (".$InventoryStr.") GROUP BY cat.id ORDER BY cat.orderby ASC";
	$CategoryEsql	= array("status"=>1);

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);
	
	$RecordSet 		= array();
	if($CategoryNum > 0)
	{
		$catindex = 0;

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];
			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.categoryid=:categoryid ORDER BY inv.name ASC";
		
			$InventoryEsql	= array("categoryid"=>(int)$catid);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$grandcirculation = 0;
				$grandpurchase = 0;
				$grandbalance = 0;

				$RecordSet = array();
				$index			= 0;
				while($InvRow	= pdo_fetch_assoc($InventoryQuery))
				{
					$TempInvID		= $InvRow['id'];
					$TempInvName	= $InvRow['name'];

					$RecordSet[$index]['id'] 		  = $TempInvID;
					$RecordSet[$index]['name'] 		  = $AllInventoryNameArr[$TempInvID]['name'] ;
					
					$circulationstr = '-';
					$purchaseqtystr = '-';
					$balanceqtystr = '-';
					if($InventoryCiculationArr[$TempInvID] > 0)
					{
						$circulationstr = (int)$InventoryCiculationArr[$TempInvID];
					}
					if($InventoryPurchaseArr[$TempInvID] > 0)
					{
						$purchaseqtystr = (int)$InventoryPurchaseArr[$TempInvID];
					}
					$balanceqty = (int)($InventoryPurchaseArr[$TempInvID] -  $InventoryCiculationArr[$TempInvID]);
					if($balanceqty > 0)
					{
						$balanceqtystr = (int)$balanceqty;
					}
					$RecordSet[$index]['circulation'] = "".$circulationstr."";
					
					$RecordSet[$index]['purchase'] = "".$purchaseqtystr."" ;
					$RecordSet[$index]['balance'] = $balanceqtystr;
					
					$grandcirculation	+= (int)$InventoryCiculationArr[$TempInvID];
					$grandpurchase		+= (int)$InventoryPurchaseArr[$TempInvID];
					$grandbalance		+= (int)($InventoryPurchaseArr[$TempInvID] -  $InventoryCiculationArr[$TempInvID]);

					$index++;
				}
				
				if($grandcirculation < 1)
				{
					$grandcirculation ='-';
				}
				if($grandpurchase < 1)
				{
					$grandpurchase ='-';
				}
				if($grandbalance < 1)
				{
					$grandbalance ='-';
				}
				$RecordListArr[$catindex]['id']			= (int)$catid;
				$RecordListArr[$catindex]['title']		= $cattitle;
				$RecordListArr[$catindex]['grandcirculation']	= $grandcirculation;
				$RecordListArr[$catindex]['grandpurchase']	= $grandpurchase;
				$RecordListArr[$catindex]['grandbalance']	= $grandbalance;
				$RecordListArr[$catindex]['recordlist']	= $RecordSet;

				$catindex++;
			}
		}
	}
}

if(empty($RecordListArr))
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
if(!empty($RecordListArr))
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
				<b>Circulation</b>
			</div>
			<br style="clear:both;" />
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<?php
				foreach($RecordListArr as $RecordListKey => $RecordListRows)
				{
					if(!empty($RecordListRows['recordlist']))
					{
						$RowNo			= 0;
						$LoopCounter	= 0;
						?>
						<tr style='font-size:<?php echo 18*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='center' valign="middle" colspan="5">
								<b><?php echo $RecordListRows['title'];?></b>
							</td>
						</tr>
						<tr style='font-size:<?php echo 16*$fonttobeincrease;?>px; color: #fff;line-height:17px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
							<td style="border:1px solid #000;" align='center' valign="top" width='60px'>S. No</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="120px">Stock Name</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="90px">Pur.</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="90px">Cir.</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="90px">Bal.</td>
						</tr>
						<?php
						foreach($RecordListRows['recordlist'] as $detailkey =>$detailrows)
						{
							?>
							<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
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
									<?php echo $detailrows['name'];?>
								</td>
								<td style="border:1px solid #000;" align='center' valign="middle">
									<?php echo $detailrows['purchase'];?>
								</td>
								<td style="border:1px solid #000;" align='center' valign="middle">
									<?php echo $detailrows['circulation'];?>
								</td>
								<td style="border:1px solid #000;" align='center' valign="middle">
									<?php echo $detailrows['balance'];?>
								</td>
							</tr>
							<?php
							$LoopCounter++;
						}
						?>
						<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='right' valign="middle" colspan="2">
								<b>Grand Total</b>
							</td>
							<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $RecordListRows['grandpurchase'];?></td>
							<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $RecordListRows['grandcirculation'];?></td>
							<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $RecordListRows['grandbalance'];?></td>
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