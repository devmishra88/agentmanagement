<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$CustomerIDArr		= array();
$CustomerNameArr	= array();
$DetailArr			= array();

if($_GET['bulkprinting'] == '1')
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch bill statement report.";

	$index	= 0;

	$_POST	= $_GET;

	if($_POST['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"deletedon2"=>1);

		if(trim($_POST['selectedmonth']) != "" && trim($_POST['selectedyear']) != "")
		{
			$StartDate	= $_POST['startdate_strtotime'];
			$EndDate	= $_POST['enddate_strtotime'];

			$Condition	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

			$CustESQL['invoicemonth']	= $_POST['selectedmonth'];
			$CustESQL['invoiceyear']	= $_POST['selectedyear'];
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND customer.lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND customer.areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids = $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND customer.areaid IN (".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND customer.lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT customer.*,invoice.invoiceid,invoice.finalamount FROM ".$Prefix."customers customer,".$Prefix."invoices invoice WHERE customer.clientid=:clientid AND customer.deletedon < :deletedon AND customer.id=invoice.customerid AND invoice.deletedon <:deletedon2 ".$Condition." ORDER BY customer.areaid DESC, customer.lineid DESC, invoice.invoiceid ASC, customer.sequence ASC, customer.customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$DetailArr	= array();
		$index		= 0;

		if($CustNum > 0)
		{
			$AllSubLine	= GetAllSubLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$id					= $custrows['id'];
				$customerid			= $custrows['customerid'];
				$areaid				= $custrows['areaid'];
				$lineid				= $custrows['lineid'];
				$name				= $custrows['name'];
				$phone				= $custrows['phone'];
				$finalamount		= $custrows['finalamount'];
				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$invoiceid			= $custrows['invoiceid'];
				$sublinename		= $AllSubLine[$custrows['sublineid']]['name'];

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
				/*if($sublinename !='')
				{
					if($addresstr !='')
					{
						$addresstr .= ", ".$sublinename;
					}
					else
					{
						$addresstr .= $sublinename;
					}
				}*/

				if(trim($addresstr) =='')
				{
					$addresstr = '--';
				}

				if(trim($sublinename) =='')
				{
					$sublinename	= "--";
				}

				/*$name2	= "#".$customerid." ".$name;*/
				$name2		= $name;

				$PreviousBalance	= GetCustomerOutStanding($id,$phone,$_POST['startdate_strtotime'],'previous');
				$totaldue			= GetCustomerOutStanding($id,$phone,$_POST['enddate_strtotime'],'current');

				$DetailArr[$areaid][$lineid][$index]['id']			= $id;
				$DetailArr[$areaid][$lineid][$index]['serialno']	= (int)$serialindex;
				$DetailArr[$areaid][$lineid][$index]['name']		= $name2;
				$DetailArr[$areaid][$lineid][$index]['phone']		= $phone;
				$DetailArr[$areaid][$lineid][$index]['address']		= $addresstr;
				$DetailArr[$areaid][$lineid][$index]['billno']		= $invoiceid;
				$DetailArr[$areaid][$lineid][$index]['billamount']	= @number_format($finalamount,2);
				$DetailArr[$areaid][$lineid][$index]['previousbalance']	= @number_format($PreviousBalance,2);
				$DetailArr[$areaid][$lineid][$index]['totaldue']		= @number_format($totaldue,2);
				$DetailArr[$areaid][$lineid][$index]['sublinename']		= $sublinename;

				$index++;
				$serialindex++;
			}
		}
	}
}

if(empty($DetailArr))
{
	echo "<div align='center'><font color='#ff0000;'>No record Found</font></div>";
	die;
}

$loop	= 0;
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
			font-size:25px;
			margin: 0px;
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
if(!empty($DetailArr))
{
	$AllAreaArr		= GetAllArea($_GET['clientid']);
	$AllLineArr		= GetAllLine($_GET['clientid']);
	$AllHawkerArr	= GetAllHawker($_GET['clientid']);

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

	$SelectedFilterStr	= "";

	if(trim($_GET['selectedmonth']) != "" && trim($_GET['selectedyear']) != "")
	{
		$MonthName	= date("F",strtotime($_POST['selectedyear']."-".$_POST['selectedmonth']."-01"));
		
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $MonthName." - ".$_POST['selectedyear'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$MonthName." - ".$_POST['selectedyear'];
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
			<?php
			$listedlineloop	= 1;
			foreach($DetailArr as $areaid=>$arearows)
			{
				foreach($arearows as $lineid=>$linerows)
				{
				?>
				<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center' valign='top'>
					<tr>
						<td valign='top' align='left' style="width:50%">
							<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
								<div style="font-size:18px;font-weight:bold"><?php echo $AgentName?></div>
							</div>
						</td>
						<td valign='top' align='right'>
							<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
								<div style="font-size:18px;font-weight:bold">Bill Statement</div>
							</div>
						</td>
					</tr>
					<tr>
						<td valign='top' align='center' colspan="2">
							<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
								<div style="font-size:15px;font-weight:bold;margin-top:8px;margin-bottom:8px;"><?php echo $SelectedFilterStr;?></div>
							</div>
						</td>
					</tr>
				</table>
				<table border='0' cellpadding='2' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
					<?php
					/*if($_POST['areaid'] < 1)
					{*/
					?>
					<tr>
						<td valign='top' align='center' colspan="7">
							<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
								<div style="font-size:15px;font-weight:bold">
									<?php echo $AllLineArr[$lineid]['name'];?>
								</div>
							</div>
						</td>
					</tr>
					<?php
					/*}*/
					?>
					<tr style='font-size:15px; color: #000;line-height:16px; vertical-align: middle; background-color:#fff; -webkit-print-color-adjust:exact;'>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width='45px'>S. No</td>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="150px">Name</td>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="280px">Address</td>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="45px">Phone</td>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="55px">Bill No.</td>
						<?php /*?><td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="70px">Bill Amt.</td>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="80px">Previous Amt.</td><?php */?>
						<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="50px">Due</td>
					</tr>
					<?php
					$RowNo			= 0;
					$LoopCounter	= 0;
					$Total			= 0;

					foreach($linerows as $key => $detailrows)
					{
						?>
						<tr style='font-size:11.5px; color: #000; vertical-align: middle;'>
							<td style="border:0px solid #000;" align='center' valign="middle">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border:0px;">
									<tr>
										<td align='center' valign="middle" style="border:0px;">
											<b><?=$LoopCounter+1;?></b>
										</td>
									</tr>
								</table>
							</td>
							<td style="border:0px solid #000;" align='left' valign="middle">
								<?php echo $detailrows['name'];?>
							</td>
							<td style="border:0px solid #000;" align='left' valign="middle">
								<?php echo $detailrows['address'];?>
							</td>
							<td style="border:0px solid #000;" align='left' valign="middle">
								<?php echo $detailrows['phone'];?>
							</td>
							<td style="border:0px solid #000;" align='right' valign="middle">
								<?php echo $detailrows['billno'];?>
							</td>
							<?php /*?><td style="border:0px solid #000;" align='right' valign="middle">
								<?php echo $detailrows['billamount'];?>
							</td>
							<td style="border:0px solid #000;" align='right' valign="middle">
								<?php echo $detailrows['previousbalance'];?>
							</td><?php */?>
							<td style="border:0px solid #000;" align='right' valign="middle">
								<?php echo $detailrows['totaldue'];?>
							</td>
						</tr>
						<?php
						/*$Total	+= $detailrows['billamount'];*/
						$Total	+= $detailrows['totaldue'];
						
						$LoopCounter++;
					}
					?>
					<tr style='font-size:12.5px; color: #000; vertical-align: middle;'>
						<td style="border-top:1px solid #000;" align='left' valign="middle" colspan="2">
							&nbsp;
						</td>
						<td style="border-top:1px solid #000;" align='left' valign="middle">
							<b>Total</b>
						</td>
						<td style="border-top:1px solid #000;" align='right' valign="middle">
							&nbsp;
						</td>
						<td style="border-top:1px solid #000;" align='right' valign="middle">
							&nbsp;
						</td>
						<td style="border-top:1px solid #000;" align='right' valign="middle">
							<?php echo @number_format($Total,2);?>
						</td>
					</tr>
				</table>
				<?
					if($listedlineloop < count($arearows))
					{
					?>
					<div style="page-break-after: always;"></div>
					<?
					}
					$listedlineloop++;
				}
			}
			?>
			<br>
		</div>
	</div>
	<?
}
?>