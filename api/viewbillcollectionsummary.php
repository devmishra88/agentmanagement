<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$RecordListArr	= array();
$Detail			= array();

$fonttobeincrease	= 1.5;

$_POST	= $_GET;

if($_GET['bulkprinting'] == '1')
{
	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1);

	$PayCondition	= "";
	$PayESQL		= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
	{
		$StartDate	= $_GET['startdate_strtotime'];
		$EndDate	= $_GET['enddate_strtotime'];

		$Condition	.= " AND invoices.invoicedate BETWEEN :startdate AND :enddate";

		$ESQL['startdate']	= $StartDate;
		$ESQL['enddate']	= $EndDate;

		$PayCondition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

		$PayESQL['startdate']	= $StartDate;
		$PayESQL['enddate']		= $EndDate;
	}

	if($_GET['areaid'] > 0)
	{
		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_GET['areaid'];

		$PayCondition		.= " AND cust.areaid=:areaid";
		$PayESQL['areaid']	= (int)$_GET['areaid'];
	}

	if($_GET['lineid'] > 0)
	{
		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_GET['lineid'];

		$PayCondition	.= " AND cust.lineid=:lineid";
		$PayESQL['lineid']	= (int)$_GET['lineid'];
	}

	if($_GET['linemanid'] > 0)
	{
		$Condition	.= " AND cust.linemanid=:linemanid";
		$ESQL['linemanid']	= (int)$_GET['linemanid'];

		$PayCondition	.= " AND cust.linemanid=:linemanid";
		$PayESQL['linemanid']	= (int)$_GET['linemanid'];
	}

	if($_GET['hawkerid'] > 0)
	{
		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_GET['hawkerid'];

		$PayCondition	.= " AND cust.hawkerid=:hawkerid";
		$PayESQL['hawkerid']	= (int)$_GET['hawkerid'];
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$Condition	.= " AND cust.areaid IN(".$areaids.")";
		
		$PayCondition	.= " AND cust.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$Condition	.= " AND cust.lineid IN(".$lineids.")";
		
		$PayCondition	.= " AND cust.lineid IN(".$lineids.")";
	}

	$SQL	= "SELECT cust.*,invoices.ispaid AS ispaid,invoices.totalamount AS totalamount,invoices.invoicedate AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoices invoices WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND invoices.deletedon < :deletedon2 AND cust.id=invoices.customerid ".$Condition." GROUP BY invoices.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$ESQL['deletedon2'] = 1;

	$InvoiceQuery	= pdo_query($SQL,$ESQL);
	$InvoiceNum		= pdo_num_rows($InvoiceQuery);

	if($InvoiceNum > 0)
	{
		while($invoicerows = pdo_fetch_assoc($InvoiceQuery))
		{
			$ispaid			= $invoicerows['ispaid'];
			$totalamount	= $invoicerows['totalamount'];
			
			$areaid			= $invoicerows['areaid'];
			$lineid			= $invoicerows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];
			$invoicedate	= $invoicerows['invoicedate'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			if($ispaid > 0)
			{
				/*$totalpayments	+= $invoicerows['totalamount'];*/
			}
			else
			{
				$year			= date("Y",$invoicedate);
				$monthname		= date("F",$invoicedate);
				$invoicedate2	= strtotime($year."-".$monthname."-01");

				$RecordListArr[$invoicedate2]['billing']	+= $totalamount;
				$RecordListArr[$invoicedate2]['cash']		+= 0;
				$RecordListArr[$invoicedate2]['coupon']		+= 0;
				$RecordListArr[$invoicedate2]['disc']		+= 0;
				$RecordListArr[$invoicedate2]['totcoll']	+= 0;
				$RecordListArr[$invoicedate2]['balance']	+= $totalamount;
			}
		}
	}

	$PaySQL	= "SELECT cust.*,payment.amount AS totalamount,payment.createdon AS paymentdate,payment.paymentamount AS paymentamount,payment.discount AS discount,payment.coupon AS coupon,payment.paymentmethod AS paymentmethod FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$PayCondition." GROUP BY payment.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$PayQuery	= pdo_query($PaySQL,$PayESQL);
	$PayNum		= pdo_num_rows($PayQuery);

	if($PayNum > 0)
	{
		while($payrows = pdo_fetch_assoc($PayQuery))
		{
			$id				= $payrows['id'];
			$customerid		= $payrows['customerid'];
			$name			= $payrows['name'];
			$areaid			= $payrows['areaid'];
			$lineid			= $payrows['lineid'];

			$paymentdate	= $payrows['paymentdate'];

			$totalamount	= $payrows['totalamount'];
			$paymentamount	= $payrows['paymentamount'];
			$discount		= $payrows['discount'];
			$coupon			= $payrows['coupon'];
			$paymentmethod	= $payrows['paymentmethod'];

			$year			= date("Y",$paymentdate);
			$monthname		= date("F",$paymentdate);

			$paymentdate2	= strtotime($year."-".$monthname."-01");

			$RecordListArr[$paymentdate2]['billing']	+= 0;
			if(strtolower($paymentmethod) == "cash")
			{
				$RecordListArr[$paymentdate2]['cash']	+= $paymentamount;
			}
			else
			{
				$RecordListArr[$paymentdate2]['cash']	+= 0;
			}
			$RecordListArr[$paymentdate2]['coupon']		+= $coupon;
			$RecordListArr[$paymentdate2]['disc']		+= $discount;
			$RecordListArr[$paymentdate2]['totcoll']	+= $totalamount;
			$RecordListArr[$paymentdate2]['balance']	-= $totalamount;
		}
	}

	$index	= 0;

	if(!empty($RecordListArr))
	{
		$areaindex	= 0;
		foreach($RecordListArr as $coldate => $colrows)
		{
			$Detail[$index]['serialno']		= $index+1;
			$Detail[$index]['year']			= date("Y",$coldate);
			$Detail[$index]['month']		= date("M",$coldate);
			$Detail[$index]['billing']		= @number_format($colrows['billing'],2);
			$Detail[$index]['cash']			= @number_format($colrows['cash'],2);
			$Detail[$index]['coupon']		= @number_format($colrows['coupon'],2);
			$Detail[$index]['disc']			= @number_format($colrows['disc'],2);
			$Detail[$index]['totcoll']		= @number_format($colrows['totcoll'],2);

			$extstr	= "";

			$balance	= $colrows['balance'];

			if($balance < 1)
			{
				$balance	= 0;
			}

			$Detail[$index]['balance']		= @number_format(abs($balance),2);

			$index++;
		}
	}
}

if(empty($Detail))
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
if(!empty($Detail))
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
			$SelectedFilterStr	.= date("F-Y", $_GET['startdate_strtotime'])." - ".date("F-Y", $_GET['enddate_strtotime']);
		}
		else
		{
			$SelectedFilterStr	.= " / ".date("F-Y", $_GET['startdate_strtotime'])." - ".date("F-Y", $_GET['enddate_strtotime']);
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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Report Collection Summary</div>
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
					<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Month, Year</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="90px">Billing</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="90px">Cash</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="90px">Coupon</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="90px">Disc</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="90px">Tot. Coll</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="90px">Balance</td>
				</tr>
				<?php
				$RowNo			= 0;
				$LoopCounter	= 0;

				foreach($Detail as $key => $colrows)
				{
					?>
					<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
						<td style="border:1px solid #000;" align='center' valign="middle">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-height:50px; border:0px;">
								<tr>
									<td align='center' valign="middle" style="border:0px;">
										<b><?=$colrows['month'].", ".$colrows['year'];?></b>
									</td>
								</tr>
							</table>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $colrows['billing'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $colrows['cash'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $colrows['coupon'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $colrows['disc'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<?php echo $colrows['totcoll'];?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $colrows['balance'];?></td>
					</tr>
					<?php
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