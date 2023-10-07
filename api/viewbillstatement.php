<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$CustomerIDArr		= array();
$CustomerNameArr	= array();
$DetailArr			= array();

$_POST	= $_GET;

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
    $response['success']	= false;
    $response['msg']		= "Unable to fetch bill statement report.";

	$index	= 0;

	if($_GET['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1,"outstandingbalance"=>0);

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

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		if($CustNum > 0)
		{
			$GetAllSubLine	= GetAllSubLine($_GET['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$id				= $custrows['id'];
				$customerid		= $custrows['customerid'];
				$name			= $custrows['name'];
				$phone			= $custrows['phone'];
				$openingbalance	= $custrows['openingbalance'];
				$areaid			= $custrows['areaid'];
				$lineid			= $custrows['lineid'];

				if(trim($phone) == "")
				{
					$phone	= "---";
				}

				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$sublinename		= $GetAllSubLine[$custrows['sublineid']]['name'];
				$outstandingbalance	= $custrows['outstandingbalance'];

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

				$name2	= "#".$customerid." ".$name;

				$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon <:deletedon AND customerid=:customerid AND ispaid < :ispaid";
				$InvoiceEsql	= array("clientid"=>(int)$_GET['clientid'],'deletedon'=>1,"customerid"=>(int)$id,"ispaid"=>1);

				$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
				$InvoiceNum		= pdo_num_rows($InvoiceQuery);

				$invoiceid	= "---";

				if($InvoiceNum > 0)
				{
					$invoicerows	= pdo_fetch_assoc($InvoiceQuery);
					$invoiceid		= $invoicerows['invoiceid'];
				}

				$DetailArr[$areaid][$lineid][$index]['id']			= $id;
				$DetailArr[$areaid][$lineid][$index]['serialno']	= $index+1;
				$DetailArr[$areaid][$lineid][$index]['name']		= $name;
				$DetailArr[$areaid][$lineid][$index]['address']		= $addresstr;
				$DetailArr[$areaid][$lineid][$index]['phone']		= $phone;
				$DetailArr[$areaid][$lineid][$index]['billno']		= $invoiceid;
				$DetailArr[$areaid][$lineid][$index]['amount']		= @number_format($outstandingbalance,2);

				$index++;
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
if(!empty($DetailArr))
{
	$AllAreaArr		= GetAllArea($_GET['clientid']);
	$AllLineArr		= GetAllLine($_GET['clientid']);

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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Bill Statement</div>
						</div>
						<br>
					</td>
				</tr>
			</table>
			<?php
			foreach($DetailArr as $areaid => $arearows)
			{
				$areaname	= $AllAreaArr[$areaid]['name'];

				foreach($arearows as $lineid => $linerows)
				{
					$linename	= $AllLineArr[$lineid]['name']
					?>
					<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center' valign='top'>
						<tr>
							<td valign='top' align='center' colspan="2">
								<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
									<div style="font-size:<?php echo 12*$fonttobeincrease;?>px;font-weight:bold"><?php echo $areaname." / ".$linename;?></div>
								</div>
							</td>
						</tr>
					</table>
					<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
						<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
							<td style="border:1px solid #000;" align='center' valign="top" width='60px'>S. No</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="120px">Name</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="120px">Address</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="120px">Phone</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="120px">Bl no.</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="90px">Due Balance</td>
						</tr>
						<?php
						$RowNo			= 0;
						$LoopCounter	= 0;

						$Total			= 0;

						foreach($linerows as $DetailKey => $DetailRows)
						{
							?>
							<tr style='font-size:<?php echo 7*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
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
									<?php echo $DetailRows['name'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $DetailRows['address'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $DetailRows['phone'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $DetailRows['billno'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $DetailRows['amount'];?>
								</td>
							</tr>
							<?php
							$Total	+= $DetailRows['amount'];
							
							$LoopCounter++;
						}
						?>
						<tr style='font-size:<?php echo 7*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='left' valign="middle" colspan="4">
								&nbsp;
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<b>Total</b>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<?php echo @number_format($Total,2);?>
							</td>
						</tr>
					</table>
					<br>
					<?php
				}
			}
			?>
		</div>
	</div>
	<?
}
?>