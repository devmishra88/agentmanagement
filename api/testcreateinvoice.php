<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once("dbconfig.php");

$ClientID	= 10;
/*$customerid	= 5108;*/
$customerid	= 1523;
$invoiceid	= "";
$Month		= 5;
$Year		= 2022;

$customerid	= 2626;
$invoiceid	= "";
$Month		= 6;
$Year		= 2022;

$resmsg	= CreateCustomerInvoice($ClientID, $customerid, $invoiceid, $Month, $Year);

print_r($resmsg);
?>