<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$RecordsArr	= array();

$fonttobeincrease	= 1;

$_POST	= $_GET;

if($_GET['bulkprinting'] == '1')
{	
	$clientid		= $_GET['clientid'];
	$customerid 	= $_GET['customerid'];
	$startdate 		= $_GET['startdate'];
	$enddate 		= $_GET['enddate'];

	if($_POST['customerid'] > 0)
	{
		$CustSQL	= "SELECT cust.* FROM ".$Prefix."customers cust WHERE (id=:id) AND clientid=:clientid AND deletedon < :deletedon";
		$CustESQL   = array("id"=>(int)$_POST['customerid'],"clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);

		if($CustNum > 0)
		{
			$RecordsArr =  array();	
			$LedgerArr =	GetLedgerByCustomerID($_POST['clientid'],$_POST['customerid'],0,0);

			$RecordsArr = $LedgerArr['items'];
			$GrandTotal	= $LedgerArr['grandtotal'];
		}

		$response['success']	= true;
		$response['ledgerlist']	= $RecordsArr;
		$response['grandtotal']	= number_format($GrandTotal,2);
		$response['msg']		= "ledger created successfully.";
	}
}
if(empty($RecordsArr))
{
	echo "<div align='center'><font color='#ff0000;'>No record Found</font></div>";
	die;
}
$loop		= 0;
/* Dummy Data */
$_GET['OrderType'] = 0;
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

if(!empty($RecordsArr))
{
	$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
	$ClientESQL		= array("id"=>(int)$clientid);

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

	if(trim($_GET['startdate']) != "" && trim($_GET['enddate']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= date("j-M-Y", $_GET['startdate'])." - ".date("j-M-Y", $_GET['enddate']);
		}
		else
		{
			$SelectedFilterStr	.= " / ".date("j-M-Y", $_GET['startdate'])." - ".date("j-M-Y", $_GET['enddate']);
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

	$CustSQL		= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND deletedon < :deletedon";
	$CustESQL		= array("id"=>(int)$customerid,"deletedon"=>1);

	$CustQuery	= pdo_query($CustSQL,$CustESQL);
	$CustNum	= pdo_num_rows($CustQuery);

	$AllLineArr	= GetAllLine($clientid);
	$AllSubLine	= GetAllSubLine($clientid);

	$CustRow	= pdo_fetch_assoc($CustQuery);

	$LineID		    = $CustRow['lineid'];
	$LinemanID	    = $CustRow['linemanid'];
	$LineName       = $AllLineArr[$LineID]['name']; 

	$CustomerName	= $CustRow["name"];
	$CustomerPhone	= $CustRow["phone"];
	$CreatedOn		= $CustRow['createdon'];
	$sublineid		= $CustRow['sublineid'];

	$PinCode		= @$CustRow['pincode'];

	$housenumber	= $CustRow['housenumber'];
	$floor			= $CustRow['floor'];
	$address1		= $CustRow['address1'];
	$sublinename	= $AllSubLine[$sublineid]['name'];

	if(trim($CustomerPhone) =="")
	{
		$CustomerPhone = "--";
	}

	$CityName		= $CustRow['customercity'];
	$StateName		= $CustRow['customerstate'];
	$StateName		= $CustRow['customerstate'];
	$customerid		= $CustRow['customerid'];

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
					</td>
				</tr>
			</table>
			<hr style="color:#000; background-color:#000; height:1px; border:0px; -webkit-print-color-adjust:exact;" />
			<br style="clear:both;" />
			<div style="font-size:<?php echo 16*$fonttobeincrease;?>px;">
				<b>Ledger</b>
			</div>
			<br />
			<div style="border:1px solid #000; margin-top:5px;">
				<div style="background-color:#000; padding:1px 0px; color:#fff; -webkit-print-color-adjust:exact;">
					<div style="width:50%; text-align:left; float:left; text-indent:8px;">
						Customer Details
					</div>
					<br style="clear:both;" />
				</div>
				<table border='0' cellpadding='0' style="margin-left:5px;">
					<tr>
						<td valign='top' align='left' width="150px">
							<b>Customer: </b>
						</td>
						<td valign='top' align='left' width="200px">
							<?php echo "#".$customerid." - ".$CustomerName;?>
						</td>
						<td valign='top' align='right' width="150px">
							<b>Phone(s): </b>
						</td>
						<td valign='top' align='left' width="200px">
							<?php echo $CustomerPhone;?>
						</td>
					</tr>
					<tr>
						<td valign='top' align='left' width="150px">
							<b>Address: </b>
						</td>
						<td valign='top' align='left' width="200px">
							<?=$addresstr;?>
						</td>
						<td valign='top' align='left'>
						</td>
						<td valign='top' align='left'>
						</td>
					</tr>
				</table>
			</div>
			<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
				<tr style='font-size:<?php echo 16*$fonttobeincrease;?>px; color: #fff;line-height:17px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
					<td style="border:1px solid #000;" align='center' valign="top" width='60px'>S.No.</td>
					<td style="border:1px solid #000;" align='center' valign="top" width="100px">Date</td>
					<td style="border:1px solid #000;" align='center' valign="top"  width="150px">Item</td>
					<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Debit</td>
					<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Credit</td>
					<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Balance</td>
				</tr>
				<?php
				$RowNo			= 0;
				$LoopCounter	= 0;

				foreach($RecordsArr as $Key=>$rows)
				{
					$date		= $rows['date'];
					$item		= $rows['item'];
					$due		= $rows['due'];
					$paid		= abs($rows['paid']);
					$paid		= number_format($paid,2);
					$balance	= $rows['balance'];
				
					if($item == "Opening Balance")
					{
						$due	= "";
						$paid	= "";
					}
					?>
					<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
						<td style="border:1px solid #000;" align='center' valign="middle">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-height:50px; border:0px;">
								<tr>
									<td align='center' valign="middle" style="border:0px;">
										<?=$j+$LoopCounter+1;?>
									</td>
								</tr>
							</table>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle">
							<b><? echo $date;?></b>
						</td>
						<td style="border:1px solid #000;" align='left' valign="middle"><?php echo $item;?></td>
						<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $due;?></td>
						<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $paid;?></td>
						<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $balance;?></td>
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