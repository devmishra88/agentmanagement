<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$_POST	= $_GET;

$OrderID = trim($_GET['invoiceid']);

$fonttobeincrease	= 1.5;

if($_GET['bulkprinting'] == '1')
{		
	$IsBillNumber 		= $_GET['isbillnumber'];
	$IsDateFilter 		= $_GET['isdatefilter'];
	$BillStartFrom 		= $_GET['billnumberfrom'];
	$BillEndTo 			= $_GET['billnumberto'];
	$ClientID 			= $_GET['clientid'];
	$LineID 			= $_GET['lineid'];
	$LinemanID 			= $_GET['linemanid'];
	$HawkerID 			= $_GET['hawkerid'];
	$AreaID 			= $_GET['areaid'];
	$issingledatefilter = $_GET['issingledatefilter'];
	$billprintingdate	= $_GET['billprintingdate'];

	$ExtArg 	= "";
	$ESQL 	= array();
	
	if($IsDateFilter == true)
	{
		$BillStartDate 	= strtotime($_GET['billstartdate']);
		$BillEndDate 	= strtotime($_GET['billenddate']);
		//echo date("r",$BillEndDate);
		$ExtArg 	.= " AND (inv.invoicedate BETWEEN :startdate AND :enddate)";
	
		$ESQL['startdate'] = $BillStartDate;
		$ESQL['enddate'] = $BillEndDate;
	}
	
	if($BillStartFrom > 0 && $BillEndTo > 0)
	{
		$ExtArg 	.= " AND (inv.invoiceid BETWEEN :startnumber AND :endnumber)";
		$ESQL['startnumber'] = $BillStartFrom;
		$ESQL['endnumber'] = $BillEndTo;
	}
	else if($BillStartFrom > 0 && $BillEndTo < 1)
	{
		$ExtArg 	.= " AND invoiceid >= :startnumber";
		$ESQL['startnumber'] = $BillStartFrom;
	}
	else if($BillEndTo > 0 && $BillStartFrom < 1)
	{
		$ExtArg 	.= " AND (inv.invoiceid <= :endnumber)";
		$ESQL['endnumber'] = $BillEndTo;
	}
	if($LineID > 0)
	{
		$ExtArg 	.= " AND (cus.lineid = :lineid)";
		$ESQL['lineid'] = $LineID;
	}
	if($LinemanID > 0)
	{
		$ExtArg 	.= " AND (cus.linemanid = :linemanid)";
		$ESQL['linemanid'] = $LinemanID;
	}
	if($HawkerID > 0)
	{
		$ExtArg 	.= " AND (cus.hawkerid = :hawkerid)";
		$ESQL['hawkerid'] = $HawkerID;
	}
	if($AreaID > 0)
	{
		$ExtArg 	.= " AND (cus.areaid = :areaid)";
		$ESQL['areaid'] = $AreaID;
	}
	if($_GET['issingledatefilter'] == 1)
	{
		$billprintingdatestart	= strtotime($_GET['billprintingdate']);
		$billprintingdateend	= $billprintingdatestart+86399;

		$ExtArg 	.= " AND (inv.invoicedate BETWEEN :billprintingdatestart AND :billprintingdateend)";

		$ESQL['billprintingdatestart']	= $billprintingdatestart;
		$ESQL['billprintingdateend']	= $billprintingdateend;
	}

	if($_POST['ismanager'] > 0 && $_POST['areamanagerid'] > 0)
	{
		$areaids	= $_POST['areaids'];

		if(trim($areaids) == "")
		{
			$areaids	= "-1";
		}

		$ExtArg	.= " AND cus.areaid IN(".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$ExtArg	.= " AND cus.lineid IN(".$lineids.")";
	}

	$SQL = "SELECT inv.* FROM ".$Prefix."customers cus,".$Prefix."invoices inv WHERE cus.id=inv.customerid AND inv.clientid=cus.clientid AND cus.clientid=:clientid AND cus.deletedon < :deletedon AND cus.canprintinvoice=:canprintinvoice AND inv.deletedon <:deletedon2 ".$ExtArg." ORDER BY invoiceid ASC";

	$ESQL['clientid']			= (int)$ClientID;
	$ESQL['deletedon']			= 1;
	$ESQL['canprintinvoice']	= 1;
	$ESQL['deletedon2']			= 1;
}
else
{
	$SQL	= "SELECT * FROM ".$Prefix."invoices WHERE id=:id AND deletedon <:deletedon ORDER BY invoiceid ASC";
	$ESQL	= array("id"=>(int)$OrderID,'deletedon'=>1);
}
$OrderQuery	= pdo_query($SQL,$ESQL);

$OrderNum		= @pdo_num_rows($OrderQuery);
if($OrderNum < 1)
{
	echo "<div align='center'><font color='#ff0000;'>No New Order Found</font></div>";
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

	<body <?if($_GET['bulkprinting'] =='1' && $_GET['downloadpdf'] !='1' && $_GET['frompage'] !='admin'){?>onload='window.print()'<?}?>>
	<?
}
else
{
	ob_start();
}

if($OrderNum > 0)
{
	$AllLineArr		= GetAllLine($ClientID);
	$AllLinemanArr	= GetAllLineman($ClientID);
	$AllSubLine		= GetAllSubLine($ClientID);

	while($rows = pdo_fetch_assoc($OrderQuery))
	{
		$Floor				= "";
		$OrderID			= $rows['id'];
		$ClientID			= $rows['clientid'];
		$CustomerID			= $rows['customerid'];
		$InvoiceNumber		= $rows['invoiceid'];
		$InvoiceMonth		= $rows['invoicemonth'];
		$InvoiceYear		= $rows['invoiceyear'];
		$InvoiceUnixDate	= $rows['invoicedate'];
		$InvoiceDate		= date("d-M-Y",$InvoiceUnixDate);
		$IsPaid				= $rows['ispaid'];
		$PaidStatus			= "Not Paid";

		$CustomerNumber	= GetCustomerID($CustomerID);

		if($IsPaid > 0)
		{
			$PaidStatus	= 'Paid';
		}

		$ClientSQL		= "SELECT * FROM ".$Prefix."clients	WHERE id=:id";
		$ClientESQL		= array("id"=>($ClientID));

		$ClientQuery	= pdo_query($ClientSQL,$ClientESQL);
		$ClientNum		= pdo_num_rows($ClientQuery);
		
		if($ClientNum > 0)
		{
			$ClientRow		= pdo_fetch_assoc($ClientQuery);
			
			$AgentName		= $ClientRow['clientname'];
			$AgentAddress	= $ClientRow['invoiceaddress'];
			$AgentPhone		= $ClientRow['invoicephone'];
		
		}

		$CustSQL	= "SELECT * FROM ".$Prefix."customers WHERE id=:id AND deletedon < :deletedon";
		$CustESQL	= array("id"=>(int)$CustomerID,"deletedon"=>1);

		$CustQuery	= pdo_query($CustSQL,$CustESQL);
		$CustNum	= pdo_num_rows($CustQuery);
		
		if($CustNum > 0)
		{
            $CustRow		= pdo_fetch_assoc($CustQuery);

			$housenumber	= $CustRow['housenumber'];
			$floor			= $CustRow['floor'];
			$address1		= $CustRow['address1'];

            $LineID			= $CustRow['lineid'];
			$LinemanID		= $CustRow['linemanid'];
			$sublineid		= $CustRow['sublineid'];
            $LineName		= $AllLineArr[$LineID]['name']; 
            $LineManName	= $AllLinemanArr[$LinemanID]['name']; 
			$sublinename	= $AllSubLine[$sublineid]['name'];
		}
		
		$CustomerName		= $rows["customername"];
		$CustomerPhone		= $rows["customerphone"];
		$CreatedOn			= $rows['createdon'];

		$Address			= @$rows['customeraddress1'];
		$Address2			= @$rows['customeraddress2'];
		$PinCode			= @$rows['customerpincode'];

		if(trim($CustomerPhone) =="")
		{
			$CustomerPhone = "--";
		}

		$CityName			= $rows['customercity'];
		$StateName			= $rows['customerstate'];
		$StateName			= $rows['customerstate'];
		$Discount			= $rows['discount'];
		$TotalAmount		= $rows['totalamount'];
		$FinalAmount		= $rows['finalamount'];
		$ConvenienceCharge	= $rows['conveniencecharge'];
		$ServiceCharge		= $rows['servicecharge'];
		$PreviousBalance	= $rows['previousbalance'];

		//$PreviousBalance = GetPreviousBalanceTillDate($CustomerID,$InvoiceYear,$InvoiceMonth);
		
		//$FinalAmount	= $FinalAmount + $PreviousBalance;

		/*$FullAddress	= $Address;

		if(trim($Address2) !='')
		{
			if(trim($Address) !="")
			{
				$FullAddress .="<br>".$Address2;
			}
			else
			{
				$FullAddress =$Address2;
			}
		}*/

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

		$IsKYCDocs = "";
		
		//$FullAddress	.= "<br>".$CityName.' / '.$StateName.' / '.$PinCode;

			?>
			<div class="OrderWrapper" style="width:100%;<?if($_GET['bulkprinting'] == '1'){?>page-break-after: always;"<?}?>><!--  min-height:1440px; -->
				<div style="width:98%;" align="center">
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
					<div style="float:right;">
						<?=$Type;?>
					</div>
					<div style="clear:both;font-size:<?php echo 18*$fonttobeincrease;?>px;">
						<b>Invoice</b>
					</div>
					<div style="border:1px solid #000; margin-top:5px;">
						<div style="background-color:#000; padding:3px 0px; color:#fff; -webkit-print-color-adjust:exact;">
							<div style="width:50%;text-align:left; text-indent:8px;">
								Customer Details
							</div>
						</div>
						<table border='0' cellpadding='0' cellspacing='0' width='100%' valign='top'>
							<tr>
								<td valign='top' align='left' width='50%'>
									<table border='0' cellpadding='0' style="margin-left:5px;">
										<tr>
                                            <td valign='top' align="left">
                                                <b>Line:</b> 
                                            </td>
                                            <td valign='top' align='left'>
												<?php echo $LineName;?>
											</td>
                                        </tr>
                                        <tr>
											<td valign='top' align='left'>
												<b>Customer ID: </b>
											</td>
											<td valign='top' align='left'>
												<?php echo $CustomerNumber;?>
											</td>
										</tr>
										<tr>
											<td valign='top' align='left'>
												<b>Name: </b>
											</td>
											<td valign='top' align='left'>
												<?=$CustomerName;?>
											</td>
										</tr>
										<tr>
											<td valign='top' align='left'>
												<b>Address: </b>
											</td>
											<td valign='top' align='left'>
												<?=$addresstr;?>
											</td>
										</tr>
										<tr>
											<td valign='top' align='left'>
												<b>Phone(s): </b>
											</td>
											<td valign='top' align='left'>
												<?=$CustomerPhone;?>
											</td>
										</tr>
									</table>
								</td>
								<td valign='top' align='left' width='50%'>
									<table border='0' cellpadding='0' style="margin-left:5px;">
										<tr>
											<td valign='top' align='left'>
												<b>Bill No: </b>
											</td>
											<td valign='top' align='left'>
												<?=$InvoiceNumber;?>
											</td>
										</tr>
										<tr>
											<td valign='top' align='left'>
												<b>Bill Date: </b>
											</td>
											<td valign='top' align='left'>
												<?=$InvoiceDate;?>
											</td>
										</tr><tr>
											<td valign='top' align='left'>
												<b>Bill Month: </b>
											</td>
											<td valign='top' align='left'>
											<?php echo $MonthArr[$InvoiceMonth];?> <?php echo $InvoiceYear;?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
					<table border='0' cellpadding='5' cellspacing='0' width='100%' style='border-collapse:collapse;' align='center'>
						<tr style='font-size:<?php echo 17*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 18*$fonttobeincrease;?>px; vertical-align: middle; background-color:#000; -webkit-print-color-adjust:exact;'>
							<td style="border:1px solid #000;" align='center' valign="top" width='60px'>S.No.</td>
							<td style="border:1px solid #000;" align='center' valign="top">Particular(s)</td>
							<?/*?><td style="border:1px solid #000;" align='center' valign="top" width='150px'>Unit Price</td><?*/?>
							<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Qty</td>
							<td style="border:1px solid #000;" align='center' valign="top" width='100px'>Days</td>
							<td style="border:1px solid #000;" valign="top" align="center" width='150px'>Line Total</td>
						</tr>
						<?php
						$OrderDetSQL		= "SELECT * FROM ".$Prefix."invoice_details WHERE invoiceid=:invoiceid ORDER BY item_start_date ASC, inventoryname ASC ";
						$OrderDetESQL		= array("invoiceid"=>(int)$OrderID);
						$OrderDetailQuery	= pdo_query($OrderDetSQL,$OrderDetESQL);
						$Num				= @pdo_num_rows($OrderDetailQuery);
						$GrandTotal	= 0;
						$TotalSaving= 0;
						if($Num > 0)
						{
							$RowNo = 0;
							$LoopCounter  = 0;
							while($ordrow = pdo_fetch_assoc($OrderDetailQuery))
							{
								$ODID			= $ordrow["id"];
								$Price			= $ordrow["price"];
								$Quantity		= $ordrow["qty"];
								$Frequency		= $ordrow["frequency"];
								$NoofDays		= $ordrow["noofdays"];
								$TotalPrice		= $ordrow["totalprice"];
								$ItemName		= $ordrow["inventoryname"];
								$InvStartDate	= $ordrow["item_start_date"];
								$InvEndDate		= $ordrow["item_end_date"];
								
								if($Frequency !='1')
								{
									$NoofDays = '-';	
								}

								$StartDate = date("d-M-Y",$InvStartDate);
								$EndDate   = date("d-M-Y",$InvEndDate);
								if(trim($StartDate) !=trim($EndDate))
								{
									$ItemNaration	= "( ".$StartDate." - ".$EndDate." )";
								}
								else
								{
									$ItemNaration	= "( ".$StartDate." )";
								}
								
								//$LineTotal	= $Quantity * $Price;
								$LineTotal		= $TotalPrice;
								$GrandTotal += $LineTotal;
								?>
								<tr style='font-size:<?php echo 16*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
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
										<b><? echo $ItemName;?></b> <br>
										<span style='font-style:italic;font-size:<?php echo 12*$fonttobeincrease;?>px;'><?php echo $ItemNaration;?></span>
									</td>
									<?/*?><td style="border:1px solid #000;" align='right' valign="middle">Rs. <?=number_format($Price,2);?></td><?*/?>
									<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $Quantity;?></td>
									<td style="border:1px solid #000;" align='center' valign="middle"><?php echo $NoofDays;?></td>
									<td style="border:1px solid #000;" valign="middle" align="right">
										Rs. <?php echo number_format($LineTotal,2);?>
									</td>
								</tr>
								<?php
								$LoopCounter++;
							}
						}
						for($i=$Num; $i< 2; $i++)
						{
							?>
							<tr style='font-size:<?php echo 17*$fonttobeincrease;?>px; color: #fff;line-height:<?php echo 18*$fonttobeincrease;?>px; vertical-align: middle;'>
								<td style="border:1px solid #000;" align='center' valign="top"><div style="min-height:77px;">&nbsp;</div></td>
								<td style="border:1px solid #000;" align='center' valign="top">&nbsp;</td>
								<td style="border:1px solid #000;" align='center' valign="top">&nbsp;</td>
								<td style="border:1px solid #000;" align='center' valign="top">&nbsp;</td>
								<td style="border:1px solid #000;" align='center' valign="top">&nbsp;</td>
								<?/*?><td style="border:1px solid #000;" align='center' valign="top">&nbsp;</td><?*/?>
							</tr>
							<?php
						}
						?>
						<tr style='font-size:<?php echo 16*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='left' valign="top" colspan='3'>
							</td>
						
							<td style="border:1px solid #000;" align='right' valign="top">Sub Total</td>
							<td style="border:1px solid #000;" align='right' valign="top">Rs.  <?=number_format($GrandTotal,2);?></td>
						</tr>
						<?php
						if($ConvenienceCharge > 0)
						{
						?>
							<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
								<td style="border:1px solid #000;" align='left' valign="top" colspan='3'>
								</td>
								<td style="border:1px solid #000;" align='right' valign="top">Convenience Charge</td>
								<td style="border:1px solid #000;" align='right' valign="top">Rs.  <?=number_format($ConvenienceCharge,2);?></td>
							</tr>
						<?php
						}
						if($ServiceCharge > 0)
						{
						?>
							<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
								<td style="border:1px solid #000;" align='left' valign="top" colspan='3'>
								</td>
								<td style="border:1px solid #000;" align='right' valign="top">Service Charge</td>
								<td style="border:1px solid #000;" align='right' valign="top">Rs.  <?=number_format($ServiceCharge,2);?></td>
							</tr>
						<?php
						}
						if($Discount > 0)
						{
						?>
							<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
								<td style="border:1px solid #000;" align='left' valign="top" colspan='3'>
								</td>
								<td style="border:1px solid #000;" align='right' valign="top">Discount</td>
								<td style="border:1px solid #000;" align='right' valign="top">Rs.  <?=number_format($Discount,2);?></td>
							</tr>
						<?php
						}
						if($PreviousBalance !='0')
						{		
							?>
							<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
								<td style="border:1px solid #000;" align='left' valign="top" colspan='3'>
								</td>
								<td style="border:1px solid #000;" align='right' valign="top">Previous Balance</td>
								<td style="border:1px solid #000;" align='right' valign="top">Rs.  <?=number_format($PreviousBalance,2);?></td>
							</tr>
							<?php
						}
						?>
						<tr style='font-size:<?php echo 15*$fonttobeincrease;?>px; color: #000; vertical-align: middle;'>
							<td style="border:1px solid #000;" align='left' valign="top" colspan='3'>
								<u>Amount Chargeable (In Words):</u>
								<div>
									<?php
									$TempTotal	= ($GrandTotal + $ServiceCharge + $ConvenienceCharge + $PreviousBalance) - $Discount;

									$Number		= new NumberToText($TempTotal);
									$Currency	= 'INR';
									if($Currency == "INR")
									{
										$Strings = $Number->GetText();
									}
									else
									{
										$Strings = $Number->GetGlobalText();
									}
									if(is_array($Strings))
									{
										/*$InWords = ucwords($CurrencyLongName."".$Strings[0]." and".$Strings[1]." ".$CurrencyMinorName." only");*/
										$InWords = ucwords("Rupees ".$Strings[0]." and".$Strings[1]." Paise only");
									}
									else
									{
										$InWords = ucwords($CurrencyLongName."".$Strings." "."only");
									}
									echo $InWords;
									?>
								</div>
							</td>
							<td style="border:1px solid #000;" align='right' valign="top">Final Amount</td>
							<td style="border:1px solid #000;" align='right' valign="top">Rs.  <?=number_format($TempTotal,2);?></td>
						</tr>
					</table>
					<table border='0' cellpadding='2' cellspacing='2' width='100%'>
						<tr>
							<td valign='bottom' align='left'>
								<b><u>Note:</u></b> <br />
								<div style="font-size:<?php echo 12*$fonttobeincrease;?>px;">
								    Please pay your bill before 7th for every month <br />
								</div>
                                <br/>
                                <div style="background-color:#000; padding:3px; color:#fff; -webkit-print-color-adjust:exact;width:50%;">
                                   Contact us for pamplets distributions.
                                </div>
                        	</td>
							<td valign='top' align='right'>
                            <br><br>
                            <br>
                                <div style="font-size:<?php echo 22*$fonttobeincrease;?>px;">
                                </div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		$loop++;
	}
}
?>