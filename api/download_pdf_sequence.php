<?php
$ByPass	= 1;
header('Access-Control-Allow-Origin: *');
include_once "dbconfig.php";

if(!is_dir("../assets/".$_GET['clientid']))
{
	mkdir("../assets/".$_GET['clientid']);
	mkdir("../assets/".$_GET['clientid']."/hawkerssequence/");
}
@mkdir("../assets/".$_GET['clientid']."/hawkerssequence/", 0777, true);

/*$File	= "viewsequence.php?clientid=".$_GET['clientid']."&areaid=".$_GET['areaid']."&lineid=".$_GET['lineid']."&linemanid=".$_GET['linemanid']."&hawkerid=".$_GET['hawkerid']."&hawkerid=".$_GET['hawkerid']."&bulkprinting=1&downloadpdf=1";*/

$File	= "viewsequence.php?bulkprinting=1&downloadpdf=1&".$FilterDataStr;

$Pdf_FileName = "sequenceprint.pdf";
$IsCreated  = CreatePDF($ServerAPIURL.$File,"../assets/".$_GET['clientid']."/hawkerssequence/".$Pdf_FileName,"1");
?>