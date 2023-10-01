<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$_POST	= $_GET;

$RecordSet	= array();

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{
	if($_POST['clientid'] > 0)
	{
		$startdate		= $_POST['startdate_strtotime'];
		$enddate		= $_POST['enddate_strtotime'];
		$selectedmonth	= date("m",$startdate);
		$selectedyear	= date("Y",$startdate);

		$Condition		= "";
		$CustESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$paymentstartdate	= "";

		if($_POST['usefromdate'] > 0)
		{
			$paymentstartdate	= $_POST['startdate_strtotime'];
		}

		if(trim($_POST['startdate_strtotime']) != "")
		{
			$startdate	= $_POST['startdate_strtotime'];
			$enddate	= $_POST['enddate_strtotime'];
		}

		if($_POST['lineid'] > 0)
		{
			$Condition	.= " AND lineid=:lineid";
			$CustESQL['lineid']	= (int)$_POST['lineid'];
		}

		if($_POST['areaid'] > 0)
		{
			$Condition	.= " AND areaid=:areaid";
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
			$Condition	.= " AND areaid IN (".$areaids.")";
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

		$CustESQL['outstandingbalance']	= 0;

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		$TotalRec	= $CustNum;

		$DetailArr	= array();
		$index		= 0;

		if($CustNum > 0)
		{
			$GetAllSubLine	= GetAllSubLine($_POST['clientid']);

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
				$outstandingbalance	= $custrows['outstandingbalance'];
				$sublinename		= $GetAllSubLine[$custrows['sublineid']]['name'];

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

				$DetailArr[$index]['id']		= $id;
				$DetailArr[$index]['name']		= $name2;
				$DetailArr[$index]['phone']		= $phone;
				$DetailArr[$index]['address']	= $addresstr;
				$DetailArr[$index]['billno']	= $invoiceid;
				$DetailArr[$index]['amount']	= @number_format($outstandingbalance,2);

				$index++;
				$serialindex++;
			}
		}

		$pageListArr	= array();
		$pageListArr	= Paging($_POST['page'], $perpage, $TotalRec);

		$totalopeningbalance = GetOutStandingAmount($_POST['clientid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$startdate,'previous');

		$TotalInvoiceArr = GetInvoiceAmount($_POST['clientid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$startdate);

		$CompletePaymentArr	= GetCustomerPayment($_POST['clientid'],$_POST['areaid'],$_POST['lineid'],$_POST['hawkerid'],$paymentstartdate,$enddate,'all',$_POST['usefromdate']);

		$totalpayments	= $CompletePaymentArr['totalamount'];

		$remaingbalance	= ((float)$TotalInvoiceArr['totalamount'] + (float)$totalopeningbalance) - (float)$totalpayments;
		$graphoutstanding	= $totalopeningbalance + $TotalInvoiceArr['totalamount'];

		$graphpayments	= 0;

		if($remaingbalance > 0 && $graphoutstanding > 0)
		{
			$graphpayments2	= ($remaingbalance / $graphoutstanding) * 100;
			$graphpayments	= (100 - $graphpayments2)/100;
		}

		$recoverydoneprecent	= 0;

		if($totalpayments > 0 && ($TotalInvoiceArr['totalamount']+$totalopeningbalance) > 0)
		{
			$recoverydoneprecent	= ($totalpayments / ((float)$TotalInvoiceArr['totalamount'] + (float)$totalopeningbalance)) * 100;
		}

		$subtotalsummary	= (float)$totalopeningbalance + (float)$TotalInvoiceArr['totalamount'];

		$RecordSet['openingbalance']		= number_format($totalopeningbalance,2);
		$RecordSet['subtotalsummary']		= number_format($subtotalsummary,2);
		$RecordSet['outstandingbalance']	= number_format($remaingbalance,2);
		$RecordSet['totalinvoicebalance']	= number_format($TotalInvoiceArr['totalamount'],2);
		$RecordSet['totalinvoice']			= (int)$TotalInvoiceArr['totalcount'];
		$RecordSet['totalpayments']			= number_format($totalpayments,2);
		$RecordSet['totalpaymentcount']		= (int)$CompletePaymentArr['totalcount'];
		$RecordSet['outstandingdetail']		= $DetailArr;
		$RecordSet['graphpayments']			= $graphpayments;
		$RecordSet['remaingbalance']		= number_format($remaingbalance,2);
		$RecordSet['graphoutstanding']		= number_format($graphoutstanding,2);
		$RecordSet['recoverydoneprecent']	= round($recoverydoneprecent);
		$RecordSet['totalcustomer']			= (int)$TotalRec;

		$response['success']		= true;
		$response['recordset']		= $RecordSet;
		$response['totalrecord']	= $TotalRec;
	}
}

if(empty($DetailArr))
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
if(!empty($DetailArr))
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
			$SelectedFilterStr	.= "Bill Month : ".date("F-Y", $_GET['startdate_strtotime']);
		}
		else
		{
			$SelectedFilterStr	.= " / "."Bill Month : ".date("F-Y", $_GET['startdate_strtotime']);
		}
	}

	if($_POST['usefromdate'] > 0 && trim($paymentstartdate) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= "Payment Start Date : ".date("j-M-Y", $paymentstartdate);
		}
		else
		{
			$SelectedFilterStr	.= " / Payment Start Date : ".date("j-M-Y", $paymentstartdate);
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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Outstanding (Detail)</div>
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
					<td style="border:1px solid #000;" align='center' valign="top" width="100px" colspan="4">Summary : <? echo $RecordSet['totalcustomer'];?></td>
				</tr>
				<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
					<td style="border:1px solid #000;" align='right' valign="middle" width="150px">
						<b>Total Previous Balance</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle" width="150px">
						<? echo $RecordSet['openingbalance'];?>
					</td>
					<td style="border:1px solid #000;" align='right' valign="middle" width="150px">
						<b>Total Customer</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle" width="150px"><? echo $RecordSet['totalcustomer'];?></td>
				</tr>
				<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
					<td style="border:1px solid #000;" align='right' valign="middle">
						<b>Outstanding Balance</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle">
						<? echo $RecordSet['outstandingbalance'];?>
					</td>
					<td style="border:1px solid #000;" align='right' valign="middle">
						<b>Total Bill</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle"><? echo $RecordSet['totalinvoice'];?></td>
				</tr>
				<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
					<td style="border:1px solid #000;" align='right' valign="middle">
						<b>Sub Total</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle"><? echo $RecordSet['subtotalsummary'];?></td>
					<td style="border:1px solid #000;" align='right' valign="middle">
						<b>Total Bill Amount</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle"><? echo $RecordSet['totalinvoicebalance'];?></td>
				</tr>
				<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
					<td style="border:1px solid #000;" align='right' valign="middle">
						<b>Total Payments</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle">
						<? echo $RecordSet['totalpayments'];?>
					</td>
					<td style="border:1px solid #000;" align='right' valign="middle">
						<b>% of recovery done</b>
					</td>
					<td style="border:1px solid #000;" align='left' valign="middle">
						<? echo $RecordSet['recoverydoneprecent'];?> %
					</td>
				</tr>
			</table>
			<br>
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
					<td style="border:1px solid #000;" align='center' valign="top" width='40px'>S.No.</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="150px">Customer</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="100px">Phone</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="220px">Address</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="100px">Amount</td>
				</tr>
				<?php
				$RowNo			= 0;
				$LoopCounter	= 0;

				foreach($DetailArr as $DetailKey => $DetailRows)
				{
					$name		= $DetailRows['name'];
					$phone		= $DetailRows['phone'];
					$address	= $DetailRows['address'];
					$amount		= $DetailRows['amount'];
					?>
					<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
						<td style="border:1px solid #000;" align='center' valign="middle">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border:0px;">
								<tr>
									<td align='center' valign="middle" style="border:0px;">
										<?=$j+$LoopCounter+1;?>
									</td>
								</tr>
							</table>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<? echo $name;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<? echo $phone;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<? echo $address;?>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $amount;?></td>
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