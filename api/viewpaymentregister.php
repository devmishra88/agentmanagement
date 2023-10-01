<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$catindex	= 0;
$RecordListArr	= array();

$fonttobeincrease	= 1.5;

$_POST	= $_GET;

if($_GET['bulkprinting'] == '1')
{
	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_GET['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	if(trim($_POST['startdate_strtotime']) != "" && trim($_POST['enddate_strtotime']) != "")
	{
		$StartDate	= strtotime($_POST['startdate_strtotime']);
		$EndDate	= strtotime($_POST['enddate_strtotime'])+86399;

		$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

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
		$areaids = $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}
		$Condition	.= " AND cust.areaid IN (".$areaids.")";
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

	$SQL	= "SELECT cust.*,payment.amount AS paidamount,payment.paymentdate AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$Condition." GROUP BY payment.id ORDER BY payment.paymentdate ASC, cust.sequence ASC, cust.customerid ASC";

	$Query	= pdo_query($SQL,$ESQL);
	$Num	= pdo_num_rows($Query);

	if($Num > 0)
	{
		$AllLineArr		= GetAllLine($_GET['clientid']);
		$GetAllSubLine	= GetAllSubLine($_GET['clientid']);

		while($rows = pdo_fetch_assoc($Query))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$phone				= $rows['phone'];
			$lineid				= $rows['lineid'];
			$linename			= $AllLineArr[$lineid]['name'];
			$paidamount			= $rows['paidamount'];
			$paymentdate		= $rows['paymentdate'];
			$housenumber		= $rows['housenumber'];
			$floor				= $rows['floor'];
			$address1			= $rows['address1'];
			$customerpaymentid	= $rows['customerpaymentid'];
			$sublinename		= $GetAllSubLine[$rows['sublineid']]['name'];

			if($customerpaymentid < 1)
			{
				$customerpaymentid	= "---";
			}

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

			/*$RecordListArr[$lineid]['name']			= $linename;
			$RecordListArr[$lineid]['totalpayment']	+= $paidamount;

			$RecordListArr[$lineid]['detail'][$paymentdate]['id']					= $id;
			$RecordListArr[$lineid]['detail'][$paymentdate]['customerid']			= $customerid;
			$RecordListArr[$lineid]['detail'][$paymentdate]['name']					= $name2;
			$RecordListArr[$lineid]['detail'][$paymentdate]['phone']				= $phone;
			$RecordListArr[$lineid]['detail'][$paymentdate]['address']				= $addresstr;
			$RecordListArr[$lineid]['detail'][$paymentdate]['paidamount']			= $paidamount;
			$RecordListArr[$lineid]['detail'][$paymentdate]['paymentdate']			= $paymentdate;
			$RecordListArr[$lineid]['detail'][$paymentdate]['customerpaymentid']	= $customerpaymentid;*/

			$RecordListArr[$paymentdate]['name']			= date("d-M-Y",$paymentdate);
			$RecordListArr[$paymentdate]['totalpayment']	+= $paidamount;

			$RecordListArr[$paymentdate]['detail'][$id]['id']					= $id;
			$RecordListArr[$paymentdate]['detail'][$id]['customerid']			= $customerid;
			$RecordListArr[$paymentdate]['detail'][$id]['name']					= $name2;
			$RecordListArr[$paymentdate]['detail'][$id]['phone']				= $phone;
			$RecordListArr[$paymentdate]['detail'][$id]['address']				= $addresstr;
			$RecordListArr[$paymentdate]['detail'][$id]['paidamount']			= $paidamount;
			$RecordListArr[$paymentdate]['detail'][$id]['paymentdate']			= $paymentdate;
			$RecordListArr[$paymentdate]['detail'][$id]['customerpaymentid']	= $customerpaymentid;
		}
	}
	if(!empty($RecordListArr))
	{
		$lineindex	= 0;
		$lineDetail	= array();
		foreach($RecordListArr as $linekey => $linedata)
		{
			$index	= 0;
			$Detail	= array();

			if(!empty($linedata['detail']))
			{
				foreach($linedata['detail'] as $detailkey=>$detailrows)
				{
					$Detail[$index]['serialno']		= $index+1;
					$Detail[$index]['id']			= $detailrows['id'];
					$Detail[$index]['customerid']	= $detailrows['customerid'];
					$Detail[$index]['name']			= $detailrows['name'];
					$Detail[$index]['paymentid']	= $detailrows['customerpaymentid'];
					$Detail[$index]['phone']		= $detailrows['phone'];
					$Detail[$index]['address']		= $detailrows['address'];
					$Detail[$index]['paidamount']	= number_format($detailrows['paidamount'],2);
					$Detail[$index]['paymentdate']	= date("d-M-Y",$detailrows['paymentdate']);

					$index++;
				}
			}
			if(!empty($Detail))
			{
				$lineDetail[$lineindex]['lineid']		= $linekey;
				$lineDetail[$lineindex]['name']			= $linedata['name'];
				$lineDetail[$lineindex]['totalpayment']	= number_format($linedata['totalpayment'],2);
				$lineDetail[$lineindex]['details']		= $Detail;

				$lineindex++;
			}
		}
	}
}

if(empty($lineDetail))
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
if(!empty($lineDetail))
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
			$SelectedFilterStr	.= date("j-M-Y", $StartDate)." - ".date("j-M-Y", $EndDate);
		}
		else
		{
			$SelectedFilterStr	.= " / ".date("j-M-Y", $StartDate)." - ".date("j-M-Y", $EndDate);
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
							<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;font-weight:bold">Payment Register</div>
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
				<?php
				foreach($lineDetail as $RecordListKey => $RecordListRows)
				{
					if(!empty($RecordListRows['details']))
					{
						$RowNo			= 0;
						$LoopCounter	= 0;
						?>
						<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='center' valign="middle" colspan="7">
								<b><?php echo $RecordListRows['name']." (Rs. - ".$RecordListRows['totalpayment'].")";?></b>
							</td>
						</tr>
						<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 14*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
							<td style="border:1px solid #000;" align='center' valign="top" width='40px'>S. No</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="120px">Customer</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="70px">Phone</td>
							<td style="border:1px solid #000;" align='center' valign="top"  width="200px">Address</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="70px">Payment ID</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="70px">Payment</td>
							<td style="border:1px solid #000;" align='center' valign="top" width="70px">Date</td>
						</tr>
						<?php
						foreach($RecordListRows['details'] as $detailkey =>$detailrows)
						{
							?>
							<tr style='font-size:<?php echo 8*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
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
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['phone'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['address'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['paymentid'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['paidamount'];?>
								</td>
								<td style="border:1px solid #000;" align='left' valign="middle">
									<?php echo $detailrows['paymentdate'];?>
								</td>
							</tr>
							<?php
							$LoopCounter++;
						}
						?>
						<tr style='font-size:<?php echo 12*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='right' valign="middle" colspan="5">
								<b>Total</b>
							</td>
							<td style="border:1px solid #000;" align='left' valign="middle" colspan="2"><?php echo $RecordListRows['totalpayment'];?></td>
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