<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$index				= 0;

$RecordListArr		= array();
$CustomerIDArr		= array();
$CustomerNameArr	= array();

$_POST	= $_GET;

$fonttobeincrease	= 1.5;

$totalsubscription	= 0;

$AllSubLine	= GetAllSubLine($_POST['clientid']);

if($_GET['bulkprinting'] == '1')
{
	$Condition	= "";
	$CustESQL	= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1);

	if($_GET['lineid'] > 0)
	{
		$Condition	.= " AND lineid=:lineid";
		$CustESQL['lineid']	= (int)$_GET['lineid'];
	}

	if($_GET['areaid'] > 0)
	{
		$Condition	.= " AND areaid=:areaid";
		$CustESQL['areaid']	= (int)$_GET['areaid'];
	}

	if($_GET['linemanid'] > 0)
	{
		$Condition	.= " AND linemanid=:linemanid";
		$CustESQL['linemanid']	= (int)$_GET['linemanid'];
	}

	if($_GET['hawkerid'] > 0)
	{
		$Condition	.= " AND hawkerid=:hawkerid";
		$CustESQL['hawkerid']	= (int)$_GET['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Condition	.= " AND areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND lineid IN(".$lineids.")";
	}

	/*$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$Condition." ORDER BY sequence ASC, customerid ASC";

	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	$totalcustomer	= $CustNum;

	if($CustNum > 0)
	{
		while($custrows = pdo_fetch_assoc($CustQuery))
		{
			$id				= $custrows['id'];
			$customerid		= $custrows['customerid'];
			$name			= $custrows['name'];

			$name2	= "#".$customerid." ".$name;

			$CustomerNameArr[$id]	= $name2;
			$CustomerIDArr[]		= $id;
		}
	}

	$CustomerIDStr	= @implode(",",@array_filter(@array_unique($CustomerIDArr)));

	if(trim($CustomerIDStr) == "")
	{
		$CustomerIDStr	= "-1";
	}*/

	$condtion		= "";
	$CategoryEsql	= array("status"=>1);

	if($_GET['cattype'] != "")
	{
		$CategoryEsql['type']	= (int)$_GET['cattype'];
		$condtion	.= " AND type=:type";
	}

	$CategorySql	= "SELECT * FROM ".$Prefix."category WHERE status=:status ".$condtion." ORDER BY orderby ASC";

	$CategoryQuery	= pdo_query($CategorySql,$CategoryEsql);
	$CategoryNum	= pdo_num_rows($CategoryQuery);

	if($CategoryNum > 0)
	{
		$ClientInventoryData = GetClientInventory($_GET['clientid'],$_GET["stateid"],$_GET["cityid"]);
		
		$index = 0;
		while($catrows = pdo_fetch_assoc($CategoryQuery))
		{
			$idarray = array();
			$categoryidarray = array();
			$namearray = array();
			$pricearray = array();
			$frequencyarray = array();

			$catid		= $catrows['id'];
			$cattitle	= $catrows['title'];

			$InventoryCond	= "";
			$InventoryEsql	= array("categoryid"=>(int)$catid,"stateid"=>(int)$_GET["stateid"],"cityid"=>(int)$_GET["cityid"]);

			if($_GET['inventoryid'] > 0)
			{
				$InventoryCond			.= " AND inv.id=:id";
				$InventoryEsql['id']	= (int)$_GET['inventoryid'];
			}

			$InventorySql	= "SELECT inv.* FROM ".$Prefix."inventory inv,".$Prefix."inventory_state_city rel WHERE 1 AND inv.categoryid=:categoryid AND inv.id=rel.inventoryid AND rel.stateid=:stateid AND rel.cityid=:cityid ".$InventoryCond." GROUP BY rel.inventoryid ORDER BY inv.name ASC";

			$InventoryQuery	= pdo_query($InventorySql,$InventoryEsql);
			$InventoryNum	= pdo_num_rows($InventoryQuery);

			if($InventoryNum > 0)
			{
				while($rows = pdo_fetch_assoc($InventoryQuery))
				{
					$id			= $rows['id'];
					$categoryid	= $rows['categoryid'];
					$name		= $rows['name'];
					$price		= $rows['price'];

					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];
							
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$namearray[]		= $name;
							$pricearray[]		= (float)$inventoryprice;
						}
					}
				}
			}
			$InventoryCond	= "";
			$InventoryEsql2	= array("categoryid"=>(int)$catid,"clientid"=>(int)$_GET["clientid"],"deletedon"=>1,'status'=>0);

			if($_GET['inventoryid'] > 0)
			{
				$InventoryCond			.= " AND inv.id=:id";
				$InventoryEsql2['id']	= (int)$$_GET['inventoryid'];
			}
			$InventorySql2	= "SELECT inv.* FROM ".$Prefix."inventory inv WHERE inv.clientid=:clientid AND deletedon<:deletedon AND status>:status AND categoryid=:categoryid ".$InventoryCond." ORDER BY inv.name ASC";

			$InventoryQuery2	= pdo_query($InventorySql2,$InventoryEsql2);
			$InventoryNum2		= pdo_num_rows($InventoryQuery2);

			if($InventoryNum2 > 0)
			{
				while($rows2 = pdo_fetch_assoc($InventoryQuery2))
				{
					$id			= $rows2['id'];
					$categoryid	= $rows2['categoryid'];
					$name		= $rows2['name'];
					$price		= $rows2['price'];
					$frequency	= $rows2['frequency'];

					if(!empty($ClientInventoryData[$id]))
					{
						if($ClientInventoryData[$id]['status'] > 0)
						{
							$inventorystatus	= $ClientInventoryData[$id]['status'];
							$inventoryprice		= $ClientInventoryData[$id]['price'];
							
							$idarray[]			= (int)$id;
							$categoryidarray[]	= (int)$categoryid;
							$namearray[]		= $name;
							$pricearray[]		= (float)$inventoryprice;
						}
					}
				}
			}
			if(!empty($namearray))
			{
				$sortnamearray = array_map("strtolower",$namearray);

				array_multisort($sortnamearray,SORT_ASC,$namearray,$idarray,$categoryidarray,$pricearray);
				foreach($namearray as $key => $value)
				{
					$id				= $idarray[$key];
					$categoryid		= $categoryidarray[$key];
					$name			= $value;
					$inventoryprice	= $pricearray[$key];

					$index	= 0;

					if(!empty($ClientInventoryData[$id]))
					{
						$inventorystatus	= $ClientInventoryData[$id]['status'];
						$inventoryprice		= $ClientInventoryData[$id]['price'];

						$CustomerSql	= "SELECT cust.*, sub.subscriptiondate AS subscriptiondate,sub.quantity AS subquantity FROM ".$Prefix."subscriptions sub, ".$Prefix."customers cust WHERE cust.id=sub.customerid AND sub.inventoryid=:inventoryid AND cust.deletedon < :deletedon AND cust.clientid=:clientid ".$Condition." ORDER BY cust.sequence ASC, cust.customerid ASC";

						$CustomerEsql	= $CustESQL;

						$CustomerEsql['inventoryid']	= (int)$id;

						$CustomerQuery	= pdo_query($CustomerSql,$CustomerEsql);
						$CustomerNum	= pdo_num_rows($CustomerQuery);

						if($CustomerNum > 0)
						{
							while($customerrows = pdo_fetch_assoc($CustomerQuery))
							{
								$qty				= $customerrows['quantity'];

								if($qty < 1)
								{
									$qty			= 1;
								}

								$customerid			= $customerrows['customerid'];
								$customername		= $customerrows['name'];
								$subscriptiondate	= $customerrows['subscriptiondate'];
								$housenumber		= $customerrows['housenumber'];
								$floor				= $customerrows['floor'];
								$address1			= $customerrows['address1'];
								$sublinename		= $AllSubLine[$customerrows['sublineid']]['name'];

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

								$name2	= "#".$customerid." ".$customername;

								$CreatedOnText		= date("d-M-Y",$subscriptiondate);

								$totalsubscription	+= $qty;

								$RecordListArr[$id]['inventory']			= $name;
								$RecordListArr[$id]['totalinventory']		+= (int)$qty;

								$RecordListArr[$id]['detail'][$index]['serialno']	= $index+1;
								$RecordListArr[$id]['detail'][$index]['customerid']	= $customerid;
								$RecordListArr[$id]['detail'][$index]['name']		= $name2;
								$RecordListArr[$id]['detail'][$index]['date']		= $CreatedOnText;
								$RecordListArr[$id]['detail'][$index]['address']	= $addresstr;

								$index++;
							}
						}
					}
				}	
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

	$AllAreaArr			= GetAllArea($_GET['clientid']);
	$AllLineArr			= GetAllLine($_GET['clientid']);
	$AllHawkerArr		= GetAllHawker($_GET['clientid']);
	$AllInventoryArr	= GetInventoryNames();

	$SelectedFilterStr	= "";

	if($_GET['inventoryid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllInventoryArr[$_GET['inventoryid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllInventoryArr[$_GET['inventoryid']]['name'];
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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">
							Customer
							<?
							if($_GET['cattype'] < 1)
							{
								echo "List by Magazine";
							}
							else
							{
								echo "List by Newspaper";
							}
							?>
							</div>
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
			<?php
			foreach($RecordListArr as $recordkey=>$recordrows)
			{
				?>
				<br style="clear:both;"/>
				<b style="font-size:<?php echo 15*$fonttobeincrease;?>px;"><?php
				echo $recordrows['inventory']." (".$recordrows['totalinventory'].")";
				?></b>
				<br /><br />
				<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
					<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
						<td style="border:1px solid #000;" align='center' valign="top" width='40px'>S.No.</td>
						<td style="border:1px solid #000;" align='center' valign="top" width="180px">Customer</td>
						<td style="border:1px solid #000;" align='center' valign="top"  width="200px">Address</td>
						<td style="border:1px solid #000;" align='center' valign="top"  width="220px">Date</td>
					</tr>
					<?php
					foreach($recordrows['detail'] as $detailkey=>$detailrows)
					{
						?>
						<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='center' valign="middle">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border:0px;">
									<tr>
										<td align='center' valign="middle" style="border:0px;">
											<?php echo $detailrows['serialno'];?>
										</td>
									</tr>
								</table>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<?php echo $detailrows['name'];?>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<?php echo $detailrows['address'];?>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $detailrows['date'];?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
			}
			?>
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