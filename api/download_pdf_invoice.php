<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once "dbconfig.php";

if(!is_dir("../assets/".$_POST['clientid']))
{
	mkdir("../assets/".$_POST['clientid']);
	mkdir("../assets/".$_POST['clientid']."/invoices/");
}

/*$File = "viewinvoice.php?clientid=".$_GET['clientid']."&areaid=".$_GET['areaid']."&lineid=".$_GET['lineid']."&linemanid=".$_GET['linemanid']."&hawkerid=".$_GET['hawkerid']."&hawkerid=".$_GET['hawkerid']."&billnumberfrom=".$_GET['billnumberfrom']."&billnumberto=".$_GET['billnumberto']."&bulkprinting=1&downloadpdf=1";*/
$File	= "viewinvoice.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

$Pdf_FileName = "bulkprinting.pdf";
$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_GET['clientid']."/invoices/".$Pdf_FileName,"1");
?>