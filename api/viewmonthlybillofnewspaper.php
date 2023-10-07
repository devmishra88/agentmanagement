<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$RecordListArr	= array();

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
	$catindex	= 0;
	$RecordListArr	= array();

	$_GET["month"]	= (int)date("m",$_GET['startdate_strtotime']);
	$_GET["year"]	= date("Y",$_GET['startdate_strtotime']);
	$monthname		= date("F",$_GET['startdate_strtotime']);

	if ($_GET['year']%4 == 0)
	{
		$daysInMonth = array(1=>31, 2=>29, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}
	else
	{
		$daysInMonth = array(1=>31, 2=>28, 3=>31, 4=>30, 5=>31, 6=>30, 7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
	}

	$totaldays	= $daysInMonth[$_GET['month']];
	$HolidayArr	= GetHoliday($_GET['clientid']);

    $response['success']	= false;
    $response['msg']		= "Unable to fetch agent inventory detail.";

	$CatCond		= "";
	$CategoryEsql	= array("status"=>1);

	if($_GET['catid'] > 0)
	{
		$CatCond			.= " AND id=:id";
		$CategoryEsql['id']	= (int)$_GET['catid'];
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$CatCond." ORDER BY orderby ASC";

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData	= GetClientInventory($_GET['clientid'],$_GET["stateid"],$_GET["cityid"]);

		$ActiveSubscriptionsData	= GetActiveCustomerSubscriptions($_GET['clientid']);

		$ClientInventoryPricing	= ClientInventoryPricing($_GET['clientid'],$_GET["year"],$_GET["month"]);
		$ClientInventoryPricingByDate	= ClientInventoryPricingByDate($_GET['clientid'],$_GET["year"],$_GET["month"]);

		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid GROUP BY rel.inventoryid ORDER BY inv.name ASC";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_GET["stateid"],"cityid"=>(int)$_GET["cityid"]);

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			$InventoryListArr	= array();
			$idarray			= array();
			$categoryidarray	= array();
			$namearray			= array();
			$pricingtypearray	= array();
			$pricearray			= array();
			$daysarray			= array();
			$InventoryListArr	= array();

			if($InventoryNum > 0)
			{
				$index	= 0;

				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id				= $rows['id'];
					$categoryid		= $rows['categoryid'];
					$name			= $rows['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 1; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}
					$idarray[]			= $id; 
					$categoryidarray[]	= $categoryid; 
					$namearray[]		= $name; 
					$daysarray[]		= $days; 
					$pricingtypearray[]	= $pricingtype; 
					$pricearray[]		= $price; 
				}
			}
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status=:status AND categoryid=:categoryid ORDER BY inv.name ASC";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_GET["clientid"],"deletedon"=>1,"status"=>1);

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2	= pdo_num_rows($InventoryQuery2);
			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id				= $rows2['id'];
					$categoryid		= $rows2['categoryid'];
					$name			= $rows2['name'];
					$days			= "";
					$price			= "";
					$pricingtype	= 1; /*0 - day base, 1 - date base*/

					if(!empty($ClientInventoryPricing[$id]))
					{
						$days			= $ClientInventoryPricing[$id]['days'];
						$price			= $ClientInventoryPricing[$id]['price'];
						$pricingtype	= $ClientInventoryPricing[$id]['pricingtype'];
					}
					$idarray[]			= $id; 
					$categoryidarray[]	= $categoryid; 
					$namearray[]		= $name; 
					$daysarray[]		= $days; 
					$pricingtypearray[]	= $pricingtype; 
					$pricearray[]		= $price; 
				}	
			}
			
			if(!empty($namearray))
			{
				$sortnamearray = array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray,$pricingtypearray,$daysarray);
				
				foreach($namearray as $key => $value)
				{
					$id				= $idarray[$key];
					$categoryid		= $categoryidarray[$key];
					$name			= $value;
					$days			= $daysarray[$key];
					$pricingtype	= $pricingtypearray[$key];
					$price			= $pricearray[$key];
			
					/*if(!empty($ClientInventoryData[$id]) && !empty($ActiveSubscriptionsData[$id]))*/
					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$InventoryListArr[$index]['id']				= (int)$id;
							$InventoryListArr[$index]['categoryid']		= (int)$categoryid;
							$InventoryListArr[$index]['name']			= $name;
							$InventoryListArr[$index]['days']			= $days;
							$InventoryListArr[$index]['price']			= $price;

							$dayindex	= 0;

							/*$dateListArr	= array();
							$isbydatepricingavialable = 0;
							for($dateloop = 1; $dateloop <= $totaldays; $dateloop++)
							{
								$date	= $dateloop;
								$price	= "";

								$canshowrow	= false;
								$hasholiday	= false;

								if(!empty($ClientInventoryPricingByDate[$id]))
								{
									$PricingByDate	= $ClientInventoryPricingByDate[$id];

									if(!empty($PricingByDate[$dateloop]))
									{
										$price	= $PricingByDate[$dateloop]['price'];
									}
								}

								if($price > 0)
								{
									$canshowrow	= true;
								}

								$datetimestamp	= strtotime($_GET['month']."/".$dateloop."/".$_GET['year']);

								if(!empty($HolidayArr))
								{
									foreach($HolidayArr as $HolidayKey=>$HolidayRows)
									{
										$inventoryid	= $HolidayRows['inventoryid'];
										$startdate		= $HolidayRows['startdate'];
										$enddate		= $HolidayRows['enddate'];

										if($inventoryid > 0)
										{
											if(($datetimestamp >= $startdate && $datetimestamp <= $enddate) && $inventoryid == $id)
											{
												$canshowrow	= true;
												$hasholiday	= true;
												break;
											}
										}
										else
										{
											if($datetimestamp >= $startdate && $datetimestamp <= $enddate)
											{
												$canshowrow	= true;
												$hasholiday	= true;
												break;
											}
										}
									}
								}
								if($canshowrow)
								{
									if($dateloop < 10)
									{
										$dateListArr[$dayindex]['displayname']	= "0".$dateloop;
									}
									else
									{
										$dateListArr[$dayindex]['displayname']	= $dateloop;
									}

									$dateListArr[$dayindex]['date']			= $dateloop;
									$dateListArr[$dayindex]['hasholiday']	= false;
									$dateListArr[$dayindex]['dateprice']	= $price;
									$dateListArr[$dayindex]['hasholiday']	= $hasholiday;

									$dayindex++;
								}

								if($isbydatepricingavialable < 1)
								{
									if($price > 0.1)
									{
										$isbydatepricingavialable = 1;
									}
								}
							}
							$InventoryListArr[$index]['datepricing']	= $dateListArr;*/
							if($isbydatepricingavialable > 0)
							{
								$pricingtype = '1';
							}
							$InventoryListArr[$index]['pricingtype']	= $pricingtype;

							/*$inventoryprice		= $ClientInventoryData[$id]['price'];
							$InventoryListArr[$index]['price']	= (float)$inventoryprice;
							$InventoryListArr[$index]['isassigned']	= true;*/

							$index++;
						}
					}
				}
			}
			$RecordListArr[$catindex]['id']			= (int)$catid;
			$RecordListArr[$catindex]['title']		= $cattitle;
			$RecordListArr[$catindex]['recordlist']	= $InventoryListArr;

			$catindex++;
		}
	}
	/*$response['inventorylist']	= $RecordListArr;
	$response['monthname']		= $monthname;
	$response['totaldays']		= (int)$totaldays;*/
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

	$AllAreaArr		= GetAllArea($_GET['clientid']);
	$AllLineArr		= GetAllLine($_GET['clientid']);
	$AllHawkerArr	= GetAllHawker($_GET['clientid']);

	$SelectedFilterStr	= $monthname." - ".$_GET['year']." / ".$_GET['catname'];
	?>
	<div class="OrderWrapper" style="width:100%;"><!--  min-height:1440px; -->
		<div style="width:98%%;" align="center">

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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Price List</div>
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
				<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:17px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
					<td style="border:1px solid #000;" align='center' valign="top" width='60px'>S.No.</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="200px">Newspaper Name</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="100px">Total Days</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="150px">Total Amount</td>
				</tr>
				<?php
				$RowNo			= 0;
				$LoopCounter	= 0;

				foreach($RecordListArr as $Key => $rows)
				{
					$itemrecordlist	= $rows['recordlist'];

					foreach($itemrecordlist as $ItemKey => $itemrows)
					{
						$name		= $itemrows['name'];
						$price		= $itemrows['price'];
						$days		= $itemrows['days'];
						?>
						<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='center' valign="middle">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-height:77px; border:0px;">
									<tr>
										<td align='center' valign="middle" style="border:0px;">
											<?=$j+$LoopCounter+1;?>
										</td>
									</tr>
								</table>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<b><?php echo $name;?></b>
							</td>
							<td style="border:1px solid #000;" align='center' valign="middle">
								<?php echo $days;?>
							</td>
							<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $price;?></td>
						</tr>
						<?php
						$LoopCounter++;
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