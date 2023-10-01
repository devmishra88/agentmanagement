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

	$outstandingbalance	= 0;

	if($_POST['outstandingamountabove'] != "")
	{
		$outstandingbalance	= (float)$_POST['outstandingamountabove'];
	}

	if($_POST['clientid'] > 0)
	{
		$Condition	= "";
		$CustESQL	= array("clientid"=>(int)$_POST['clientid'],"clientid2"=>(int)$_POST['clientid'],"deletedon"=>1,"outstandingbalance"=>(float)$outstandingbalance);

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND cust.lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND cust.areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND cust.hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];
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

		if($_POST['latepaymentdays'] != "")
		{
			$tempenddate	= strtotime(date("Y-m-d"));
			$startdate		= $tempenddate - ($_POST['latepaymentdays']*86400);
			$enddate		= $tempenddate+86399;

			$Condition	.= " AND cust.id NOT IN(SELECT customerid FROM ".$Prefix."customer_payments WHERE paymentdate BETWEEN :paymentstartdate AND :paymentenddate)";

			$CustESQL['paymentstartdate']	= $startdate;
			$CustESQL['paymentenddate']		= $enddate;
		}

		$CustSQL	= "SELECT cust.* FROM ".$Prefix."customers cust,".$Prefix."line line WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.outstandingbalance > :outstandingbalance AND line.id=cust.lineid AND line.clientid=:clientid2 ".$Condition." ORDER BY line.name ASC,cust.sequence ASC, cust.customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		/*echo $CustSQL;
		print_r($CustESQL);

		$CustSQL3	= "SELECT SUM(outstandingbalance) AS outstandingbalance FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon AND outstandingbalance > :outstandingbalance ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery3	= pdo_query($CustSQL3,$CustESQL);
		$CustRows3	= pdo_fetch_assoc($CustQuery3);

		$totaloutstandingbalance	= $CustRows3['outstandingbalance'];

		$TotalRec	= $CustNum;

		if($CustNum > 0)
		{
			$totalpages	= ceil($CustNum/$perpage);
			$offset		= ($_POST['page'] - 1) * $perpage;
			$addquery	= " LIMIT %d, %d";
		}
		else
		{
			$addquery	= "";
		}

		$CustSQL2	= $CustSQL.$addquery;
		$CustSQL2	= sprintf($CustSQL2, intval($offset), intval($perpage));

		$CustQuery2	= pdo_query($CustSQL2,$CustESQL);
		$CustNum2	= pdo_num_rows($CustQuery2);*/

		$DetailArr	= array();
		$index		= 0;

		if($CustNum > 0)
		{
			$AllAreaArr	= GetAllArea($_POST['clientid']);
			$AllLineArr	= GetAllLine($_POST['clientid']);
			$AllSubLine	= GetAllSubLine($_POST['clientid']);

			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$id					= $custrows['id'];
				$customerid			= $custrows['customerid'];
				$name				= $custrows['name'];
				$phone				= $custrows['phone'];
				$openingbalance		= $custrows['openingbalance'];
				$housenumber		= $custrows['housenumber'];
				$floor				= $custrows['floor'];
				$address1			= $custrows['address1'];
				$lineid				= $custrows['lineid'];
				$linename			= $AllLineArr[$lineid]['name'];
				$areaid				= $custrows['areaid'];
				$areaname			= $AllAreaArr[$areaid]['name'];
				$outstandingbalance	= $custrows['outstandingbalance'];
				$sublinename		= $AllSubLine[$custrows['sublineid']]['name'];

				$PaymentSql2	= "SELECT * FROM ".$Prefix."customer_payments WHERE customerid=:customerid ORDER BY paymentdate DESC LIMIT 1";
				$PaymentEsql2	= array("customerid"=>(int)$id);

				$PaymentQuery2	= pdo_query($PaymentSql2, $PaymentEsql2);
				$PaymentNum2	= pdo_num_rows($PaymentQuery2);

				$paymentamount	= "";
				$paymentdate	= "";

				if($PaymentNum2 > 0)
				{
					$Paymentrow2	= pdo_fetch_assoc($PaymentQuery2);

					$Amount		= (float)$Paymentrow2['amount'];
					$Discount	= (float)$Paymentrow2['discount'];
					$Coupon		= (float)$Paymentrow2['coupon'];

					$paymentamount	= @number_format(($Amount+$Discount+$Coupon),2);

					$paymentdate	= date("d-M-Y",$Paymentrow2['paymentdate']);
				}

				$SubscriptionStatusArr	= GetCustomerStatusBySubscription($id);

				$hassubscription	= $SubscriptionStatusArr['hassubscription'];
				$blockcolor			= $SubscriptionStatusArr['blockcolor'];
				$statusclass		= $SubscriptionStatusArr['statusclass'];

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

				$InvoiceSql		= "SELECT * FROM ".$Prefix."invoices WHERE clientid=:clientid AND deletedon <:deletedon AND customerid=:customerid AND ispaid < :ispaid ORDER BY id DESC LIMIT 1";
				$InvoiceEsql	= array("clientid"=>(int)$_POST['clientid'],'deletedon'=>1,"customerid"=>(int)$id,"ispaid"=>1);

				$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql);
				$InvoiceNum		= pdo_num_rows($InvoiceQuery);

				$invoiceid		= "---";
				$hasinvoiceid	= false;

				if($InvoiceNum > 0)
				{
					$invoicerows	= pdo_fetch_assoc($InvoiceQuery);
					$billno			= $invoicerows['invoiceid'];
					$invoiceid		= $invoicerows['id'];
					$hasinvoiceid	= true;
				}

				$DetailArr[$areaid][$id]['id']					= $id;
				$DetailArr[$areaid][$id]['serialno']			= (int)$serialindex;
				$DetailArr[$areaid][$id]['name']				= $name2;
				$DetailArr[$areaid][$id]['phone']				= $phone;
				$DetailArr[$areaid][$id]['address']				= $addresstr;
				$DetailArr[$areaid][$id]['hasinvoiceid']		= $hasinvoiceid;
				$DetailArr[$areaid][$id]['billno']				= $billno;
				$DetailArr[$areaid][$id]['invoiceid']			= $invoiceid;
				$DetailArr[$areaid][$id]['hassubscription']		= $hassubscription;
				$DetailArr[$areaid][$id]['blockcolor']			= $blockcolor;
				$DetailArr[$areaid][$id]['statusclass']			= $statusclass;
				/*$DetailArr[$areaid][$id]['areaid']			= (int)$areaid;
				$DetailArr[$areaid][$id]['areaname']			= $areaname;*/
				$DetailArr[$areaid][$id]['lineid']				= (int)$lineid;
				$DetailArr[$areaid][$id]['linename']			= $linename;
				$DetailArr[$areaid][$id]['amount']				= @number_format($outstandingbalance,2);
				$DetailArr[$areaid][$id]['outstandingbalance']	= @number_format($outstandingbalance,2);
				$DetailArr[$areaid][$id]['lastpaymentdate']		= $paymentdate;
				$DetailArr[$areaid][$id]['paymentamount']		= $paymentamount;
				$DetailArr[$areaid][$id]['paymentnum']			= (int)$PaymentNum2;

				$index++;
				$serialindex++;
			}

			/*usort($DetailArr, function($a, $b){
				return $a['linename'] <=> $b['linename'];
			});*/
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
			<?php
			foreach($DetailArr as $areaid => $arearows)
			{
				$areaname	= $AllAreaArr[$areaid]['name'];
				?>
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
								<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Late Payment Report</div>
							</div>
							<br>
						</td>
					</tr>
				</table>
				<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center' valign='top'>
					<tr>
						<td valign='top' align='center' colspan="2">
							<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
								<div style="font-size:<?php echo 12*$fonttobeincrease;?>px;font-weight:bold"><?php echo $areaname;?></div>
							</div>
						</td>
					</tr>
				</table>
				<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
					<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
						<td style="border:1px solid #000;" align='center' valign="top" width='60px'>S. No</td>
						<td style="border:1px solid #000;" align='center' valign="top" width="100px">Line</td>
						<td style="border:1px solid #000;" align='center' valign="top" width="120px">Name</td>
						<td style="border:1px solid #000;" align='center' valign="top" width="120px">Address</td>
						<td style="border:1px solid #000;" align='center' valign="top" width="90px">Due</td>
						<td style="border:1px solid #000;" align='center' valign="top" width="130px">Last Payment</td>
					</tr>
					<?php
					$RowNo			= 0;
					$LoopCounter	= 0;

					$Total			= 0;

					foreach($arearows as $DetailKey => $DetailRows)
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
								<?php echo $DetailRows['linename'];?>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<?php echo $DetailRows['name'];?>
								<?php
								if($DetailRows['phone'] != "")
								{
								?>
								<div>(<?php echo $DetailRows['phone'];?>)</div>
								<?php
								}
								?>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<?php echo $DetailRows['address'];?>
							</td>
							<td style="border:1px solid #000;" align='right' valign="middle">
								Rs. <?php echo $DetailRows['outstandingbalance'];?>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle">
								<?php
								if($DetailRows['paymentnum'] > 0)
								{
									?>
									<div>
										Rs. <?php echo $DetailRows['paymentamount'];?> (<?php echo $DetailRows['lastpaymentdate'];?>)
									</div>
									<?php
								}
								else
								{
									echo "---";
								}
								?>
							</td>
						</tr>
						<?php
						$Total	+= $DetailRows['outstandingbalance'];
						
						$LoopCounter++;
					}
					?>
					<tr style='font-size:<?php echo 7*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
						<td style="border:1px solid #000;" align='left' valign="middle" colspan="3">
							&nbsp;
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<b>Total</b>
						</td>
						<td style="border:1px solid #000;" align='right' valign="middle">
							Rs. <?php echo @number_format($Total,2);?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
						</td>
					</tr>
				</table>
				<br>
				<div style="page-break-after:always;">&nbsp;</div>
				<?php
			}
			?>
		</div>
	</div>
	<?
}
?>