<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");
global $Prefix; // Please do not delete this.

$catindex	= 0;
$RecordListArr	= array();

$fonttobeincrease	= 1;

$_POST	= $_GET;

if($_GET['bulkprinting'] == '1')
{
	$AllAreaArr	= GetAllArea($_GET['clientid']);
	$AllLineArr	= GetAllLine($_GET['clientid']);

	$TotalRec	= 0;

	$catindex	= 0;
	$RecordListArr	= array();

	$PrevCondition	= "";
	$PrevESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$Condition	= "";
	$ESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	$NextMonthCondition	= "";
	$NextMonthESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1,"paymentdeletedon"=>1);

	$BillCondition	= "";
	$BillESQL		= array("clientid"=>(int)$_POST['clientid'],"deletedon"=>1);

	$InvoiceCond	= "";
	$InvoiceEsql	= array("clientid2"=>(int)$_POST['clientid'],'deletedon'=>1,"deletedon2"=>1,"ispaid"=>1);

	$PayCond	= "";
	$PayEsql	= array("clientid"=>(int)$_POST['clientid'],"clientid2"=>(int)$_POST['clientid'],"deletedon2"=>1,"paymentdeletedon"=>1);

	$startdate		= $_POST['startdate_strtotime'];
	$selectedmonth	= date("m",$startdate);
	$selectedyear	= date("Y",$startdate);

	$curr_month_last_day	= date('t',$startdate);
	$curr_month_checkdate	= strtotime(date($selectedyear.'-'.$selectedmonth.'-'.$curr_month_last_day));

	if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
	{
		/*$StartDate= $_POST['monthyear'];*/
		$StartDate	= $_POST['startdate_strtotime'];
		$EndDate	= $_POST['enddate_strtotime'];

		if($_POST['usefromdate'] > 0)
		{
			$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
			$NextMonthCondition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
		}
		else
		{
			$Condition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";
			$NextMonthCondition	.= " AND payment.paymentdate BETWEEN :startdate AND :enddate";

			$PayCond	.= " AND payment.paymentdate <=:enddate";
		}

		$BillCondition	.= " AND invoice.invoicemonth=:invoicemonth AND invoice.invoiceyear=:invoiceyear";

		$BillESQL['invoicemonth']	= $selectedmonth;
		$BillESQL['invoiceyear']	= $selectedyear;

		/*$InvoiceCond	.= " AND invoice.invoicedate<=:invoicedate2";
		
		$InvoiceEsql['invoicedate2']	= $startdate-1; //Commented by vk to improve previous month logic
		
		*/

		$InvoiceCond	.= ' AND UNIX_TIMESTAMP(CONCAT(invoiceyear,"-",invoicemonth,"-01")) < :invoicedate2 ';
		$InvoiceEsql['invoicedate2'] = $StartDate;

		$PayEsql['enddate']	= $startdate-86400;

		if($_POST['usefromdate'] > 0)
		{
			$PaymentStartDate	= $_POST['paymentstartdate'];

			$ESQL['startdate']	= $PaymentStartDate;
			$ESQL['enddate']	= $curr_month_checkdate+86399;

			$NextMonthESQL['startdate']	= $curr_month_checkdate+86400;
			$NextMonthESQL['enddate']	= $EndDate;
		}
		else
		{
			$ESQL['startdate']	= $startdate;
			$ESQL['enddate']	= $curr_month_checkdate+86399;

			$NextMonthESQL['startdate']	= $curr_month_checkdate+86400;
			$NextMonthESQL['enddate']	= $EndDate;
		}
	}

	if($_POST['areaid'] > 0)
	{
		$PrevCondition		.= " AND areaid=:areaid";
		$PrevESQL['areaid']	= (int)$_POST['areaid'];

		$Condition	.= " AND cust.areaid=:areaid";
		$ESQL['areaid']	= (int)$_POST['areaid'];

		$NextMonthCondition	.= " AND cust.areaid=:areaid";
		$NextMonthESQL['areaid']	= (int)$_POST['areaid'];

		$BillCondition	.= " AND cust.areaid=:areaid";
		$BillESQL['areaid']	= (int)$_POST['areaid'];

		$InvoiceCond	.= " AND cust.areaid=:areaid";
		$InvoiceEsql['areaid']	= (int)$_POST['areaid'];

		$PayCond	.= " AND cust.areaid=:areaid";
		$PayEsql['areaid']	= (int)$_POST['areaid'];
	}

	if($_POST['lineid'] > 0)
	{
		$PrevCondition		.= " AND lineid=:lineid";
		$PrevESQL['lineid']	= (int)$_POST['lineid'];

		$Condition	.= " AND cust.lineid=:lineid";
		$ESQL['lineid']	= (int)$_POST['lineid'];

		$NextMonthCondition	.= " AND cust.lineid=:lineid";
		$NextMonthESQL['lineid']	= (int)$_POST['lineid'];

		$BillCondition	.= " AND cust.lineid=:lineid";
		$BillESQL['lineid']	= (int)$_POST['lineid'];

		$InvoiceCond	.= " AND cust.lineid=:lineid";
		$InvoiceEsql['lineid']	= (int)$_POST['lineid'];

		$PayCond	.= " AND cust.lineid=:lineid";
		$PayEsql['lineid']	= (int)$_POST['lineid'];
	}

	if($_POST['hawkerid'] > 0)
	{
		$PrevCondition		.= " AND hawkerid=:hawkerid";
		$PrevESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$Condition	.= " AND cust.hawkerid=:hawkerid";
		$ESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$NextMonthCondition	.= " AND cust.hawkerid=:hawkerid";
		$NextMonthESQL['hawkerid']	= (int)$_POST['hawkerid'];

		$BillCondition	.= " AND cust.hawkerid=:hawkerid";
		$BillESQL['hawkerid']	= (int)$_POST['hawkerid'];

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
		$PrevCondition	.= " AND areaid IN (".$areaids.")";

		$Condition		.= " AND cust.areaid IN (".$areaids.")";
		
		$NextMonthCondition		.= " AND cust.areaid IN (".$areaids.")";

		$BillCondition	.= " AND cust.areaid IN (".$areaids.")";

		$InvoiceCond	.= " AND cust.areaid IN (".$areaids.")";

		$PayCond	.= " AND cust.areaid IN (".$areaids.")";
	}
	if(($_POST['islineman'] > 0 && $_POST['loginlinemanid'] > 0) || ($_POST['ishawker'] > 0 && $_POST['loginhawkerid'] > 0))
	{
		$lineids	= $_POST['lineids'];

		if(trim($lineids) == "")
		{
			$lineids	= "-1";
		}

		$PrevCondition	.= " AND lineid IN(".$lineids.")";

		$Condition		.= " AND cust.lineid IN(".$lineids.")";

		$NextMonthCondition		.= " AND cust.lineid IN(".$lineids.")";

		$BillCondition	.= " AND cust.lineid IN(".$lineids.")";

		$InvoiceCond	.= " AND cust.lineid IN(".$lineids.")";

		$PayCond	.= " AND cust.lineid IN(".$lineids.")";
	}

	$PrevSQL	= "SELECT * FROM ".$Prefix."customers WHERE clientid=:clientid AND deletedon < :deletedon ".$PrevCondition."";

	$PrevQuery	= pdo_query($PrevSQL,$PrevESQL);
	$PrevNum	= pdo_num_rows($PrevQuery);

	if($PrevNum > 0)
	{
		while($prevrows = pdo_fetch_assoc($PrevQuery))
		{
			$id				= $prevrows['id'];
			$phone			= $prevrows['phone'];

			$areaid			= $prevrows['areaid'];
			$lineid			= $prevrows['lineid'];

			$openingbalance	= $prevrows['openingbalance'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			/*$PreviousBalance	= GetCustomerOutStanding($id,$phone,$curr_month_checkdate,'previous');*/

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$RecordListArr[$areaid]['name']				= $areaname;

			$RecordListArr[$areaid]['invoiceamount']	+= 0;
			$RecordListArr[$areaid]['invoicecount']		+= 0;

			$RecordListArr[$areaid]['totalcash']		+= 0;
			$RecordListArr[$areaid]['cashcount']		+= 0;

			$RecordListArr[$areaid]['totaldiscount']	+= 0;
			$RecordListArr[$areaid]['discountcount']	+= 0;

			$RecordListArr[$areaid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['cashcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['discountcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalremaining']	+= 0;
			$RecordListArr[$areaid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['openingbalance']	+= $openingbalance;
			$RecordListArr[$areaid]['previousbalance']	+= $PreviousBalance;

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcountnextmonth']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcountnextmonth']	+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= $openingbalance;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= $PreviousBalance;
		}
	}

	$BillSQL	= "SELECT cust.*,invoice.finalamount AS invoiceamount,invoice.invoicedate AS invoicedate FROM ".$Prefix."customers cust, ".$Prefix."invoices invoice WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND cust.deletedon < :deletedon2 AND cust.id=invoice.customerid ".$BillCondition." GROUP BY invoice.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$BillESQL['deletedon2']	= 1;

	$BillQuery	= pdo_query($BillSQL,$BillESQL);
	$BillNum	= pdo_num_rows($BillQuery);

	if($BillNum > 0)
	{
		while($billrows = pdo_fetch_assoc($BillQuery))
		{
			$id				= $billrows['id'];
			$customerid		= $billrows['customerid'];
			$name			= $billrows['name'];
			$areaid			= $billrows['areaid'];
			$lineid			= $billrows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$invoicedate		= $billrows['invoicedate'];
			$invoiceamount		= $billrows['invoiceamount'];

			$RecordListArr[$areaid]['name']				= $areaname;
			
			$RecordListArr[$areaid]['invoiceamount']	+= $invoiceamount;
			$RecordListArr[$areaid]['invoicecount']		+= 1;
			
			$RecordListArr[$areaid]['totalcash']		+= 0;
			$RecordListArr[$areaid]['cashcount']		+= 0;

			$RecordListArr[$areaid]['totaldiscount']	+= 0;
			$RecordListArr[$areaid]['discountcount']	+= 0;
			
			$RecordListArr[$areaid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['couponcount']		+= 0;

			$RecordListArr[$areaid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['cashcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['discountcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalremaining']	+= $invoiceamount;
			$RecordListArr[$areaid]['remainingcount']	+= 1;

			$RecordListArr[$areaid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['previousbalance']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= $invoiceamount;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 1;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalcashnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcountnextmonth']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscountnextmonth']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcountnextmonth']	+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcouponnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcountnextmonth']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	+= $invoiceamount;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 1;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= 0;
		}
	}

	$BillMonthPaymentSQL	= "SELECT cust.*,payment.amount AS paidamount,payment.discount AS paymentdiscount,payment.coupon AS paymentcoupon,payment.createdon AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$Condition." GROUP BY payment.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$BillMonthPaymentQuery	= pdo_query($BillMonthPaymentSQL,$ESQL);
	$BillMonthPaymentNum	= pdo_num_rows($BillMonthPaymentQuery);

	if($BillMonthPaymentNum > 0)
	{
		while($rows = pdo_fetch_assoc($BillMonthPaymentQuery))
		{
			$id					= $rows['id'];
			$customerid			= $rows['customerid'];
			$name				= $rows['name'];
			$customerpaymentid	= $rows['customerpaymentid'];
			$areaid				= $rows['areaid'];
			$lineid				= $rows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$paymentdate		= $rows['paymentdate'];
			$paidamount			= $rows['paidamount'];
			$paymentdiscount	= $rows['paymentdiscount'];
			$paymentcoupon		= $rows['paymentcoupon'];

			$totalpayment		= ($paidamount + $paymentdiscount + $paymentcoupon);

			$cashcount	= 0;
			if($paidamount > 0)
			{
				$cashcount	= 1;
			}

			$discountcount	= 0;
			if($paymentdiscount > 0)
			{
				$discountcount	= 1;
			}

			$couponcount	= 0;
			if($paymentcoupon > 0)
			{
				$couponcount	= 1;
			}

			$paymentcount	= 0;
			if($totalpayment > 0)
			{
				$paymentcount	= 1;
			}

			$RecordListArr[$areaid]['name']				= $areaname;

			$RecordListArr[$areaid]['invoiceamount']	+= 0;
			$RecordListArr[$areaid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['totalcash']		+= $paidamount;
			$RecordListArr[$areaid]['cashcount']		+= $cashcount;
			
			$RecordListArr[$areaid]['totaldiscount']	+= $paymentdiscount;
			$RecordListArr[$areaid]['discountcount']	+= $discountcount;
			
			$RecordListArr[$areaid]['totalcoupon']		+= $paymentcoupon;
			$RecordListArr[$areaid]['couponcount']		+= $couponcount;

			$RecordListArr[$areaid]['totalpayment']		+= $totalpayment;
			$RecordListArr[$areaid]['paymentcount']		+= $paymentcount;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= 0;

			$RecordListArr[$areaid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['remainingcount']	+= 0;
			
			$RecordListArr[$areaid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['remainingcount']	-= 1;
			}

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= $paidamount;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= $cashcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= $paymentdiscount;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= $discountcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= $paymentcoupon;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= $couponcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= $paymentcount;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']	+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['detail'][$lineid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	-= 1;
			}
		}
	}

	$NextMonthPaymentSQL	= "SELECT cust.*,payment.amount AS paidamount,payment.discount AS paymentdiscount,payment.coupon AS paymentcoupon,payment.createdon AS paymentdate,payment.paymentid AS customerpaymentid FROM ".$Prefix."customers cust, ".$Prefix."customer_payments payment WHERE cust.clientid=:clientid AND cust.deletedon < :deletedon AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid ".$NextMonthCondition." GROUP BY payment.id ORDER BY cust.sequence ASC, cust.customerid ASC";

	$NextMonthPaymentQuery	= pdo_query($NextMonthPaymentSQL,$NextMonthESQL);
	$NextMonthPaymentNum	= pdo_num_rows($NextMonthPaymentQuery);

	if($NextMonthPaymentNum > 0)
	{
		while($nextmonthpaymentrows = pdo_fetch_assoc($NextMonthPaymentQuery))
		{
			$id					= $nextmonthpaymentrows['id'];
			$customerid			= $nextmonthpaymentrows['customerid'];
			$name				= $nextmonthpaymentrows['name'];
			$customerpaymentid	= $nextmonthpaymentrows['customerpaymentid'];
			$areaid				= $nextmonthpaymentrows['areaid'];
			$lineid				= $nextmonthpaymentrows['lineid'];

			$areaname		= $AllAreaArr[$areaid]['name'];
			$linename		= $AllLineArr[$lineid]['name'];

			if($areaid < 1)
			{
				$areaname	= "Unnamed";
			}

			$paymentdate		= $nextmonthpaymentrows['paymentdate'];
			$paidamount			= $nextmonthpaymentrows['paidamount'];
			$paymentdiscount	= $nextmonthpaymentrows['paymentdiscount'];
			$paymentcoupon		= $nextmonthpaymentrows['paymentcoupon'];

			$totalpayment		= ($paidamount + $paymentdiscount + $paymentcoupon);

			$cashcount	= 0;
			if($paidamount > 0)
			{
				$cashcount	= 1;
			}

			$discountcount	= 0;
			if($paymentdiscount > 0)
			{
				$discountcount	= 1;
			}

			$couponcount	= 0;
			if($paymentcoupon > 0)
			{
				$couponcount	= 1;
			}

			$paymentcount	= 0;
			if($totalpayment > 0)
			{
				$paymentcount	= 1;
			}

			$RecordListArr[$areaid]['name']				= $areaname;

			$RecordListArr[$areaid]['invoiceamount']	+= 0;
			$RecordListArr[$areaid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['totalcash']		+= 0;
			$RecordListArr[$areaid]['cashcount']		+= 0;
			
			$RecordListArr[$areaid]['totaldiscount']	+= 0;
			$RecordListArr[$areaid]['discountcount']	+= 0;
			
			$RecordListArr[$areaid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['couponcount']		+= 0;

			$RecordListArr[$areaid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['totalcashnextmonth']		+= $paidamount;
			$RecordListArr[$areaid]['cashcountnextmonth']		+= $cashcount;

			$RecordListArr[$areaid]['totaldiscountnextmonth']	+= $paymentdiscount;
			$RecordListArr[$areaid]['discountcountnextmonth']	+= $discountcount;

			$RecordListArr[$areaid]['totalcouponnextmonth']		+= $paymentcoupon;
			$RecordListArr[$areaid]['couponcountnextmonth']		+= $couponcount;

			$RecordListArr[$areaid]['totalpaymentnextmonth']	+= $totalpayment;
			$RecordListArr[$areaid]['paymentcountnextmonth']	+= $paymentcount;

			$RecordListArr[$areaid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['remainingcount']	+= 0;
			
			$RecordListArr[$areaid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['remainingcount']	-= 1;
			}

			$RecordListArr[$areaid]['detail'][$lineid]['name']				= $linename;

			$RecordListArr[$areaid]['detail'][$lineid]['invoiceamount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['invoicecount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcash']			+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcount']			+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscount']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcoupon']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcount']		+= 0;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalpayment']		+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcount']		+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['totalcashnextmonth']		+= $paidamount;
			$RecordListArr[$areaid]['detail'][$lineid]['cashcountnextmonth']		+= $cashcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totaldiscountnextmonth']	+= $paymentdiscount;
			$RecordListArr[$areaid]['detail'][$lineid]['discountcountnextmonth']	+= $discountcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalcouponnextmonth']		+= $paymentcoupon;
			$RecordListArr[$areaid]['detail'][$lineid]['couponcountnextmonth']		+= $couponcount;

			$RecordListArr[$areaid]['detail'][$lineid]['totalpaymentnextmonth']		+= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['paymentcountnextmonth']		+= $paymentcount;
			
			$RecordListArr[$areaid]['detail'][$lineid]['totalremaining']	-= $totalpayment;
			$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	+= 0;

			$RecordListArr[$areaid]['detail'][$lineid]['openingbalance']	+= 0;
			$RecordListArr[$areaid]['detail'][$lineid]['previousbalance']	+= 0;

			if($RecordListArr[$areaid]['detail'][$lineid]['totalremaining'] < 1)
			{
				$RecordListArr[$areaid]['detail'][$lineid]['remainingcount']	-= 1;
			}
		}
	}

	$AreaDetail	= array();

	if(!empty($RecordListArr))
	{
		$areaindex	= 0;
		foreach($RecordListArr as $areaid => $areadata)
		{
			$index	= 0;
			$Detail	= array();

			$areaprevbalance	= 0;

			if(!empty($areadata['detail']))
			{
				foreach($areadata['detail'] as $lineid=>$detailrows)
				{
					$lineprevbalance	= 0;

					$InvoiceSql		= "SELECT SUM(invoice.finalamount) AS totalfinalamount FROM ".$Prefix."invoices invoice,".$Prefix."customers cust WHERE cust.id=invoice.customerid AND invoice.deletedon <:deletedon AND invoice.ispaid < :ispaid AND cust.clientid=:clientid2 AND cust.deletedon < :deletedon2 AND cust.lineid=:lineid2".$InvoiceCond;

					$TempInvoiceEsql	= array("lineid2"=>(int)$lineid);

					$InvoiceEsql2	= array_merge($TempInvoiceEsql,$InvoiceEsql);

					$InvoiceQuery	= pdo_query($InvoiceSql,$InvoiceEsql2);
					$InvoiceRows	= pdo_fetch_assoc($InvoiceQuery);

					$tillprevmonthinvoiceamount	= $InvoiceRows['totalfinalamount'];

					$PrevPaySQL		= "SELECT COUNT(payment.id) as totalpaymentcount,SUM(payment.amount) AS paidamount, SUM(payment.discount) AS discount, SUM(payment.coupon) AS coupon FROM ".$Prefix."customer_payments payment,".$Prefix."customers cust WHERE payment.clientid=:clientid AND cust.clientid=:clientid2 AND cust.deletedon <:deletedon2 AND payment.deletedon < :paymentdeletedon AND cust.id=payment.customerid AND cust.lineid=:lineid2".$PayCond;

					$TempPrevPayEsql	= array("lineid2"=>(int)$lineid);
					$PrevPayEsql2		= array_merge($TempPrevPayEsql,$PayEsql);

					$PrevPayQuery	= pdo_query($PrevPaySQL,$PrevPayEsql2);
					$PrevPayRows	= pdo_fetch_assoc($PrevPayQuery);

					$tillprevpaidamount	= $PrevPayRows['paidamount'];
					$tillprevdiscount	= $PrevPayRows['discount'];
					$tillprevcoupon		= $PrevPayRows['coupon'];

					$tillprevtotalpayments	= (float)$tillprevpaidamount+(float)$tillprevdiscount+(float)$tillprevcoupon;

					$lineprevbalance	= ($tillprevmonthinvoiceamount + $detailrows['openingbalance']) - $tillprevtotalpayments;

					$areaprevbalance	+= $lineprevbalance;


					$Detail[$index]['serialno']			= $index+1;
					$Detail[$index]['id']				= $lineid;
					$Detail[$index]['name']				= $detailrows['name'];

					$Detail[$index]['invoiceamount']	= number_format($detailrows['invoiceamount']);
					$Detail[$index]['invoicecount']		= $detailrows['invoicecount'];

					$Detail[$index]['totalcash']		= number_format($detailrows['totalcash']);
					$Detail[$index]['cashcount']		= $detailrows['cashcount'];

					$Detail[$index]['totaldiscount']	= number_format($detailrows['totaldiscount']);
					$Detail[$index]['discountcount']	= $detailrows['discountcount'];

					$Detail[$index]['totalcoupon']		= number_format($detailrows['totalcoupon']);
					$Detail[$index]['couponcount']		= $detailrows['couponcount'];

					$Detail[$index]['totalpayment']		= number_format($detailrows['totalpayment']);
					$Detail[$index]['paymentcount']		= $detailrows['paymentcount'];

					$Detail[$index]['totalcashnextmonth']		= number_format($detailrows['totalcashnextmonth']);
					$Detail[$index]['cashcountnextmonth']		= $detailrows['cashcountnextmonth'];

					$Detail[$index]['totaldiscountnextmonth']	= number_format($detailrows['totaldiscountnextmonth']);
					$Detail[$index]['discountcountnextmonth']	= $detailrows['discountcountnextmonth'];

					$Detail[$index]['totalcouponnextmonth']		= number_format($detailrows['totalcouponnextmonth']);
					$Detail[$index]['couponcountnextmonth']		= $detailrows['couponcountnextmonth'];

					$Detail[$index]['totalpaymentnextmonth']	= number_format($detailrows['totalpaymentnextmonth']);
					$Detail[$index]['paymentcountnextmonth']	= $detailrows['paymentcountnextmonth'];

					$Detail[$index]['openingbalance']	= number_format($detailrows['openingbalance']);

					$Detail[$index]['previousbalance']	= number_format($lineprevbalance);

					$previousdue	= $lineprevbalance - $detailrows['totalpayment'];

					$Detail[$index]['previousdue']	= number_format($previousdue);

					$subtotal	= ($lineprevbalance + $detailrows['invoiceamount']) - $detailrows['totalpayment'];

					$Detail[$index]['subtotal']	= @number_format($subtotal);

					$paymentpercentage	= 0;

					if($detailrows['totalpaymentnextmonth'] > 0 && $subtotal > 0)
					{
						$paymentpercentage	= ($detailrows['totalpaymentnextmonth']/$subtotal)*100;
					}

					$Detail[$index]['paymentpercentage']	= round($paymentpercentage);

					$totalremaining	= $subtotal - $detailrows['totalpaymentnextmonth'];
					
					$Detail[$index]['totalremaining']	= number_format($totalremaining);
					$Detail[$index]['remainingcount']	= $detailrows['remainingcount'];

					$remainingpercentage	= 0;
					if(($totalremaining) > 0 && $subtotal > 0)
					{
						$remainingpercentage	= (($subtotal - $detailrows['totalpaymentnextmonth'])/$subtotal)*100;
					}

					$Detail[$index]['remainingpercentage']	= round($remainingpercentage);

					$TotalRec++;
					$index++;
				}
			}

			if(!empty($Detail))
			{
				$AreaDetail[$areaindex]['areaid']		= $areaid;
				$AreaDetail[$areaindex]['name']			= $areadata['name'];

				$AreaDetail[$areaindex]['invoiceamount']= number_format($areadata['invoiceamount']);
				$AreaDetail[$areaindex]['invoicecount']	= $areadata['invoicecount'];
				
				$AreaDetail[$areaindex]['totalcash']	= number_format($areadata['totalcash']);
				$AreaDetail[$areaindex]['cashcount']	= $areadata['cashcount'];
				
				$AreaDetail[$areaindex]['totaldiscount']	= number_format($areadata['totaldiscount']);
				$AreaDetail[$areaindex]['discountcount']	= $areadata['discountcount'];
				
				$AreaDetail[$areaindex]['totalcoupon']	= number_format($areadata['totalcoupon']);
				$AreaDetail[$areaindex]['couponcount']	= $areadata['couponcount'];

				$AreaDetail[$areaindex]['totalpayment']	= number_format($areadata['totalpayment']);
				$AreaDetail[$areaindex]['paymentcount']	= $areadata['paymentcount'];

				$AreaDetail[$areaindex]['totalcashnextmonth']	= number_format($areadata['totalcashnextmonth']);
				$AreaDetail[$areaindex]['cashcountnextmonth']	= $areadata['cashcountnextmonth'];
				
				$AreaDetail[$areaindex]['totaldiscountnextmonth']	= number_format($areadata['totaldiscountnextmonth']);
				$AreaDetail[$areaindex]['discountcountnextmonth']	= $areadata['discountcountnextmonth'];
				
				$AreaDetail[$areaindex]['totalcouponnextmonth']	= number_format($areadata['totalcouponnextmonth']);
				$AreaDetail[$areaindex]['couponcountnextmonth']	= $areadata['couponcountnextmonth'];

				$AreaDetail[$areaindex]['totalpaymentnextmonth']	= number_format($areadata['totalpaymentnextmonth']);
				$AreaDetail[$areaindex]['paymentcountnextmonth']	= $areadata['paymentcountnextmonth'];

			
				$AreaDetail[$areaindex]['openingbalance']= number_format($areadata['openingbalance']);

				$AreaDetail[$areaindex]['previousbalance']= round($areaprevbalance);

				$previousdue	= $areaprevbalance - $areadata['totalpayment'];

				$AreaDetail[$areaindex]['previousdue']= round($previousdue);
				
				$areasubtotal	= ($areaprevbalance + $areadata['invoiceamount']) - $areadata['totalpayment'];

				$totalremaining	= $areasubtotal - $areadata['totalpaymentnextmonth'];

				$AreaDetail[$areaindex]['totalremaining']= number_format($totalremaining);

				$remainingpercentage	= "0";
				if($totalremaining > 0 && $areasubtotal > 0)
				{
					$remainingpercentage	= ($totalremaining / $areasubtotal)*100;
				}

				$AreaDetail[$areaindex]['remainingpercentage']= round($remainingpercentage);

				$paymentpercentage	= "0";

				if($areasubtotal > 0 && $areadata['totalpaymentnextmonth'] > 0)
				{
					$paymentpercentage	= ($areadata['totalpaymentnextmonth']/$areasubtotal)*100;
				}


				$AreaDetail[$areaindex]['remainingcount']= $areadata['remainingcount'];

				$AreaDetail[$areaindex]['paymentpercentage']	= "".round($paymentpercentage)."";

				$AreaDetail[$areaindex]['subtotal']		= @number_format($areasubtotal);
				$AreaDetail[$areaindex]['monthname']	= $MonthArr[(int)$selectedmonth];

				$AreaDetail[$areaindex]['details']	= $Detail;

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

	$AllHawkerArr	= GetAllHawker($_GET['clientid']);

	$SelectedFilterStr	= "";

	if(trim($_GET['startdate_strtotime']) != "" && trim($_GET['enddate_strtotime']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= date("F-Y", $_GET['startdate_strtotime']);
		}
		else
		{
			$SelectedFilterStr	.= " / ".date("F-Y", $_GET['startdate_strtotime']);
		}
	}

	if($_GET['usefromdate'] > 0 && trim($_GET['paymentstartdate']) != "")
	{
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= "Payment Start Date : ".date("j-M-Y", $_GET['paymentstartdate']);
		}
		else
		{
			$SelectedFilterStr	.= " / Payment Start Date : ".date("j-M-Y", $_GET['paymentstartdate']);
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

	if($_GET['areaid'] != "")
	{
		$areaname	= $AllAreaArr[$_GET['areaid']]['name'];
		if($_GET['areaid'] == "-1")
		{
			$areaname	= "All Area";
		}
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $areaname;
		}
		else
		{
			$SelectedFilterStr	.= " / ".$areaname;
		}
	}

	if($_GET['lineid'] != "")
	{
		$linename	= $AllLineArr[$_GET['lineid']]['name'];
		if($_GET['areaid'] == "-1")
		{
			$linename	= "All Line";
		}
		if(trim($SelectedFilterStr) == "")
		{
			$SelectedFilterStr	.= $linename;
		}
		else
		{
			$SelectedFilterStr	.= " / ".$linename;
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
			<table border='0' cellpadding='0' cellspacing='1' width='100%' align='center' valign='top'>
				<tr>
					<td valign='top' align='left' style="width:50%">
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:18px;font-weight:bold"><?php echo $AgentName?></div>
						</div>
					</td>
					<td valign='top' align='right'>
						<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
							<div style="font-size:18px;font-weight:bold">Bill & Recovery Summary</div>
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
				foreach($AreaDetail as $RecordListKey => $RecordListRows)
				{
					if(!empty($RecordListRows['details']))
					{
						$RowNo			= 0;
						$LoopCounter	= 0;
						?>
						<tr>
							<td valign='top' align='center' colspan="12">
								<div style="font-family:Arial, Helvetica, sans-serif; color:#000;">
									<div style="font-size:15px;font-weight:bold">
										<?php echo $RecordListRows['name'];?>
									</div>
								</div>
							</td>
						</tr>
						<tr style='font-size:15px; color: #000;line-height:16px; vertical-align: middle; background-color:#fff; -webkit-print-color-adjust:exact;'>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width='35px'>S. No</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="60px">Line</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="50px">Prev.<br>Bal.</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="50px">Prev.<br>Payment</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="55px">Prev.<br>Due</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="55px"><?php echo $RecordListRows['monthname'];?> Bill Amt</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="55px"><?php echo $RecordListRows['monthname'];?> Bal.</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="45px">Cash<br>(till date)</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="45px">Disc.<br>(till date)</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="50px">Coupon<br>(till date)</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="50px">Payment<br>(till date)</td>
							<td style="border-top:1px solid #000;border-bottom:1px solid #000;" align='center' valign="top" width="50px">Rem.<br>(till date)</td>
						</tr>
						<?php
						foreach($RecordListRows['details'] as $detailkey =>$detailrows)
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
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['previousbalance'];?>
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['totalpayment'];?>
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['previousdue'];?>
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['invoiceamount'];?> (<?php echo $detailrows['invoicecount'];?>)
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['subtotal'];?>
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['totalcashnextmonth'];?> (<?php echo $detailrows['cashcountnextmonth'];?>)
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['totaldiscountnextmonth'];?> (<?php echo $detailrows['discountcountnextmonth'];?>)
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['totalcouponnextmonth'];?> (<?php echo $detailrows['couponcountnextmonth'];?>)
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['totalpaymentnextmonth'];?> (<?php echo $detailrows['paymentcountnextmonth'];?>)
								</td>
								<td style="border:0px solid #000;" align='right' valign="middle">
									<?php echo $detailrows['totalremaining'];?>
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
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['previousbalance'];?>
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['totalpayment'];?>
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['previousdue'];?>
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['invoiceamount'];?> (<?php echo $RecordListRows['invoicecount'];?>)
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['subtotal'];?>
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['totalcashnextmonth'];?> (<?php echo $RecordListRows['cashcountnextmonth'];?>)
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['totaldiscountnextmonth'];?> (<?php echo $RecordListRows['discountcountnextmonth'];?>)
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['totalcouponnextmonth'];?> (<?php echo $RecordListRows['couponcountnextmonth'];?>)
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['totalpaymentnextmonth'];?> (<?php echo $RecordListRows['paymentcountnextmonth'];?>)
							</td>
							<td style="border-top:1px solid #000;" align='right' valign="middle">
								<?php echo $RecordListRows['totalremaining'];?>
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