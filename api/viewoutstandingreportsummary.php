<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$_POST	= $_GET;

$totaloutstanding	= 0;
$RecordListArr	= array();
$AreaDetail		= array();

if($_GET['bulkprinting'] == '1')
{
	$TotalRec	= 0;

	$AllAreaArr	= GetAllArea($_POST['clientid']);
	$AllLineArr	= GetAllLine($_POST['clientid']);

	if($_POST['clientid'] > 0)
	{
		$Condition		= "";
		$CustESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$InvoiceCond	= "";
		$InvoiceEsql	= array("clientid2"=>(int)$_POST['clientid'],'deletedon'=>1,"deletedon2"=>1,"ispaid"=>1);

		$PayCond	= "";
		$PayEsql	= array("clientid"=>(int)$_POST['clientid'],"clientid2"=>(int)$_POST['clientid'],"deletedon2"=>1,"paymentdeletedon"=>1);

		$selectedmonth	= date("m",$_GET['startdate_strtotime']);
		$selectedyear	= date("Y",$_GET['startdate_strtotime']);
		$month_last_day	= date('t',$_GET['startdate_strtotime']);

		$invoicedate	= $selectedyear."-".$selectedmonth."-".$month_last_day;

		if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
		{
			$StartDate	= $_GET['startdate_strtotime'];
			$EndDate	= $_GET['enddate_strtotime'];

			$InvoiceCond	.= " AND invoice.invoicedate<=:invoicedate";

			$InvoiceEsql['invoicedate']	= strtotime($invoicedate)+86399;

			/*$InvoiceCond	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

			$InvoiceEsql['invoicemonth']	= $selectedmonth;
			$InvoiceEsql['invoiceyear']		= $selectedyear;*/

			/*$PayCond	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

			$PayEsql['startdate']	= $StartDate;
			$PayEsql['enddate']		= $EndDate;*/

			if($_POST['usefromdate'] > 0)
			{
				$PayCond	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

				$PaymentStartDate	= $_POST['paymentstartdate'];

				$PayEsql['startdate']	= $PaymentStartDate;
				$PayEsql['enddate']	= $EndDate;
			}
			else
			{
				$PayCond	.= " AND payment.paymentdate <=:enddate";

				$PayEsql['enddate']	= $EndDate;
			}
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];

			$InvoiceCond	.= " AND cust.lineid=:lineid";
			$InvoiceEsql['lineid']	= (int)$_POST['lineid'];

			$PayCond	.= " AND cust.lineid=:lineid";
			$PayEsql['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND areaid=:areaid";
			$CustESQL['areaid']	= (int)$_POST['areaid'];

			$InvoiceCond	.= " AND cust.areaid=:areaid";
			$InvoiceEsql['areaid']	= (int)$_POST['areaid'];

			$PayCond	.= " AND cust.areaid=:areaid";
			$PayEsql['areaid']	= (int)$_POST['areaid'];
		}

		if($_POST['hawkerid'] > 0)
		{
			$Condition	.= " AND hawkerid=:hawkerid";
			$CustESQL['hawkerid']	= (int)$_POST['hawkerid'];

			$InvoiceCond	.= " AND cust.hawkerid=:hawkerid";
			$InvoiceEsql['hawkerid']	= (int)$_POST['hawkerid'];

			$PayCond	.= " AND cust.hawkerid=:hawkerid";
			$PayEsql['hawkerid']	= (int)$_POST['hawkerid'];
		}

		if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
		{
			$areaids = $_POST['areaids'];

			if(trim($areaids) == "")
			{
				$areaids	= "-1";
			}
			$Condition	.= " AND areaid IN (".$areaids.")";

			$InvoiceCond	.= " AND cust.areaid IN (".$areaids.")";

			$PayCond		.= " AND cust.areaid IN (".$areaids.")";
		}
		if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
		{
			$lineids	= $_POST['lineids'];

			if(trim($lineids) == "")
			{
				$lineids	= "-1";
			}

			$Condition	.= " AND lineid IN(".$lineids.")";

			$InvoiceCond	.= " AND cust.lineid IN(".$lineids.")";

			$PayCond		.= " AND cust.lineid IN(".$lineids.")";
		}

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$Condition." ORDER BY sequence ASC, customerid ASC";

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		if($CustNum > 0)
		{
			while($custrows = pdo_fetch_assoc($CustQuery))
			{
				$id				= $custrows['id'];
				$areaid			= $custrows['areaid'];
				$lineid			= $custrows['lineid'];
				$openingbalance	= $custrows['openingbalance'];

				$areaname			= $AllAreaArr[$areaid]['name'];
				$linename			= $AllLineArr[$lineid]['name'];

				if($areaid < 1)
				{
					$areaname	= "Unnamed";
				}

				$RecordListArr[$areaid]['name']			= $areaname;
				$RecordListArr[$areaid]['outstanding']	+= $openingbalance;

				$RecordListArr[$areaid]['detail'][$lineid]['name']			= $linename;
				$RecordListArr[$areaid]['detail'][$lineid]['outstanding']	+= $openingbalance;
			}
		}
	}

	$RecordListArr2	= array();

	if(!empty($RecordListArr))
	{
		foreach($RecordListArr as $areaid => $arearows)
		{
			$areaname		= $arearows['name'];
			$outstanding	= $arearows['outstanding'];
			$linedetail		= $arearows['detail'];

			$RecordListArr2[$areaid]['name']		= $areaname;
			$RecordListArr2[$areaid]['outstanding']	+= $outstanding;

			foreach($linedetail as $lineid => $linerows)
			{
				$linename		= $linerows['name'];
				$outstanding	= $linerows['outstanding'];

				$RecordListArr2[$areaid]['detail'][$lineid]['name']			= $linename;
				$RecordListArr2[$areaid]['detail'][$lineid]['outstanding']	+= $outstanding;

				$InvoiceSql		= "SELECT COUNT(invoice.id) as totalinvoice,SUM(invoice.finalamount) AS totalfinalamount FROM ".$Prefix."invoices invoice,".$Prefix."customers cust WHERE cust.id=invoice.customerid AND invoice.deletedon <:deletedon AND invoice.ispaid < :ispaid AND cust.clientid=:clientid2 AND cust.deletedon < :deletedon2 AND cust.lineid=:lineid2".$InvoiceCond;

				$TempInvoiceEsql	= array("lineid2"=>(int)$lineid);

				$InvoiceEsql2	= array_merge($TempInvoiceEsql,$InvoiceEsql);

				$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql2);
				$InvoiceRows	= pdo_fetch_assoc($InvoiceQuery);

				$totalfinalamount	= $InvoiceRows['totalfinalamount'];

				$RecordListArr2[$areaid]['outstanding']						+= $totalfinalamount;
				$RecordListArr2[$areaid]['detail'][$lineid]['outstanding']	+= $totalfinalamount;

				$PaySQL		= "SELECT COUNT(payment.id) as totalpaymentcount,SUM(payment.amount) AS paidamount, SUM(payment.discount) AS discount, SUM(payment.coupon) AS coupon FROM ".$Prefix."customer_payments payment,".$Prefix."customers cust WHERE payment.clientid=:clientid AND cust.clientid=:clientid2 AND cust.deletedon <:deletedon2 AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid AND cust.lineid=:lineid2".$PayCond;

				$TempPayEsql	= array("lineid2"=>(int)$lineid);
				$PayEsql2		= array_merge($TempPayEsql,$PayEsql);

				$PayQuery	= pdo_query($PaySQL,$PayEsql2);
				$PayRows	= pdo_fetch_assoc($PayQuery);

				$paidamount	= $PayRows['paidamount'];
				$discount	= $PayRows['discount'];
				$coupon		= $PayRows['coupon'];

				$totalpayments	= (float)$paidamount+(float)$discount+(float)$coupon;

				$RecordListArr2[$areaid]['outstanding']						-= $totalpayments;
				$RecordListArr2[$areaid]['detail'][$lineid]['outstanding']	-= $totalpayments;
			}
		}
	}

	$AreaDetail	= array();

	$totaloutstanding	= 0;

	if(!empty($RecordListArr2))
	{
		$areaindex	= 0;
		foreach($RecordListArr2 as $areaid => $areadata)
		{
			$index	= 0;
			$Detail	= array();

			if(!empty($areadata['detail']))
			{
				foreach($areadata['detail'] as $lineid=>$detailrows)
				{
					$Detail[$index]['serialno']		= $index+1;
					$Detail[$index]['id']			= $lineid;
					$Detail[$index]['name']			= $detailrows['name'];
					$Detail[$index]['outstanding']	= number_format($detailrows['outstanding'],2);

					$TotalRec++;
					$index++;
				}
			}
			if(!empty($Detail))
			{
				$AreaDetail[$areaindex]['areaid']		= $areaid;
				$AreaDetail[$areaindex]['name']			= $areadata['name'];
				$AreaDetail[$areaindex]['outstanding']	= number_format($areadata['outstanding'],2);
				$AreaDetail[$areaindex]['details']		= $Detail;

				$totaloutstanding	+= $areadata['outstanding'];

				$areaindex++;
			}
		}
	}
}

if(empty($AreaDetail))
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
			font-size:14px;
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
if(!empty($AreaDetail))
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

	/*$AllAreaArr	= GetAllArea($_GET['clientid']);
	$AllLineArr		= GetAllLine($_GET['clientid']);*/
	$AllHawkerArr	= GetAllHawker($_GET['clientid']);

	$SelectedFilterStr	= "";

	if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= "Bill Month : ".date("F-Y", $_GET['startdate_strtotime']);
		}
		else
		{
			$SelectedFilterStr	.= " / "."Bill Month : ".date("F-Y", $_GET['startdate_strtotime']);
		}
	}

	if($_POST['usefromdate'] > 0 && trim($PaymentStartDate) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= "Payment Start Date : ".date("j-M-Y", $PaymentStartDate);
		}
		else
		{
			$SelectedFilterStr	.= " / Payment Start Date : ".date("j-M-Y", $PaymentStartDate);
		}
	}

	if(trim($_GET['enddate_strtotime']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= "Payment Upto Date : ".date("j-M-Y", $_GET['enddate_strtotime']);
		}
		else
		{
			$SelectedFilterStr	.= " / Payment Upto Date : ".date("j-M-Y", $_GET['enddate_strtotime']);
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

	if($_POST['hawkerid'] > 0)
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $AllHawkerArr[$_POST['hawkerid']]['name'];
		}
		else
		{
			$SelectedFilterStr	.= " / ".$AllHawkerArr[$_POST['hawkerid']]['name'];
		}
	}
	?>
	<div class="OrderWrapper" style="width:100%;"><!--  min-height:1440px; -->
		<div style="width:98%;" align="center">
			<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center' valign='top'>
				<tr>
					<td valign='top' align='left' style="width:50%">
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:18px;font-weight:bold"><?php echo $AgentName?></div>
						</div>
					</td>
					<td valign='top' align='right'>
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:18px;font-weight:bold">Outstanding (Summary)</div>
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
				foreach($AreaDetail as $areaid => $arearows)
				{
					if(!empty($arearows['details']))
					{
						$RowNo			= 0;
						$LoopCounter	= 0;
						?>
						<tr>
							<td valign='top' align='center' colspan="3">
								<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
									<div style="font-size:15px;font-weight:bold">
										<?php echo $arearows['name'];?>
									</div>
								</div>
							</td>
						</tr>
						<tr style='font-size:15px; color: #000;line-height:16px; vertical-align: middle; background-color:#fff; -webkit-print-color-adjust:exact;'>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width='60px'>S. No</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="400px">Line</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="70px">Due Balance</td>
						</tr>
						<?php
						foreach($arearows['details'] as $lineid =>$linerows)
						{
							?>
							<tr style='font-size:11.5px; color: #000; vertical-align: middle;'>
								<td style="border:0px solid #000;" align='center' valign="middle">
									<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border:0px;">
										<tr>
											<td align='center' valign="middle" style="border:0px;">
												<b><?=$j+$LoopCounter+1;?></b>
											</td>
										</tr>
									</table>
								</td>
								<td style="border:0px solid #000;" align='left' valign="middle">
									<?php echo $linerows['name'];?>
								</td>
								<td style="border:0px solid #000;" align='left' valign="middle">
									<?php echo $linerows['outstanding'];?>
								</td>
							</tr>
							<?php
							$LoopCounter++;
						}
						?>
						<tr style='font-size:12.5px; color: #000; vertical-align: middle;'>
							<td style="border-top:1px solid #000;" align='left' valign="middle">
								&nbsp;
							</td>
							<td style="border-top:1px solid #000;" align='left' valign="middle">
								<b>Area Total</b>
							</td>
							<td style="border-top:1px solid #000;" align='left' valign="middle">
								<?php echo $arearows['outstanding'];?>
							</td>
						</tr>
						<?
					}
				}
				?>
			</table>
		</div>
	</div>
	<?
}
?>