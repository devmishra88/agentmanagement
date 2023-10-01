<?
require_once $DomPDFFolder.'autoload.inc.php';
use Dompdf\Dompdf;

function Redirect($URL)
{
	header("Location:".$URL);
	die;
}
function DownloadPDF($file)
{
	if (!is_file($file)) { die("<b>404 File not found!</b>"); }
	$len = filesize($file);
	$filename = basename($file);
	$file_extension = strtolower(substr(strrchr($filename,"."),1));
	$file_no_ext = str_replace(".".$file_extension,"",$filename);
	$newfilename = str_replace(" ","_",$filename);

	switch( $file_extension ) 
	{
		case "pdf": $ctype="application/pdf"; break;
		case "exe": $ctype="application/octet-stream"; break;
		case "zip": $ctype="application/zip"; break;
		case "doc": $ctype="application/msword"; break;
		case "xls": $ctype="application/vnd.ms-excel"; break;
		case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpeg":
		case "jpg": $ctype="image/jpg"; break;
		case "mp3": $ctype="audio/mpeg"; break;
		case "wav": $ctype="audio/x-wav"; break;
		case "mpeg":
		case "mpg":
		case "mpe": $ctype="video/mpeg"; break;
		case "mov": $ctype="video/quicktime"; break;
		case "avi": $ctype="video/x-msvideo"; break;
		case "htm":
		case "html":$ctype="text/html"; break;
		case "css":$ctype="text/css"; break;
		case "txt":$ctype="text/plain"; break;

		//The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
		case "php": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;

		default: $ctype="application/force-download";
	}

   header("Pragma: public");
   header("Expires: 0");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Cache-Control: public");
   header("Content-Description: File Transfer");
  
   header("Content-Type: $ctype");

   $header="Content-Disposition: attachment; filename=".$newfilename.";";
   header($header );
   header("Content-Transfer-Encoding: binary");
   header("Content-Length: ".$len);
   @readfile($file);
   exit;
}
function CreatePDF($URL,$NewPDF,$OutputMode)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $URL);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	ob_start();
	curl_exec($ch);
	$html = ob_get_contents();
	ob_end_clean();
	curl_close($ch);
	
	//echo curl_error($ch);
	$dompdf = new Dompdf();
	$dompdf->loadHtml($html);

	$dompdf->setPaper("a4","portrait");
	$dompdf->render();
	
	$NewFile = fopen($NewPDF,'w');
	file_put_contents($NewPDF,$dompdf->output($NewPDF));
	fclose($NewFile);
	if($OutputMode == '2')
	{
		Redirect($NewPDFURL);
	}
	if($OutputMode == '1')
	{
		DownloadPDF($NewPDF);
	}
	else
	{
		if(file_exists($NewPDF))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
function CreatePDFNew($URL,$NewPDF,$OutputMode)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $URL);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	ob_start();
	curl_exec($ch);
	$html = ob_get_contents();
	ob_end_clean();
	curl_close($ch);
	//ob_flush();
	//echo "ss".$html;
	//echo curl_error($ch);
	//die;
	$dompdf = new Dompdf();
	$dompdf->loadHtml($html);

	$dompdf->setPaper("a4","portrait");
	$dompdf->render();

	$NewPDFURL	= $NewPDF;
	$NewFile = fopen($NewPDF,'w');
	file_put_contents($NewPDF,$dompdf->output($NewPDF));
	fclose($NewFile);
	
	if($OutputMode == '2')
	{
		Redirect($NewPDFURL);
	}
	if($OutputMode == '1')
	{
		DownloadPDF($NewPDF);
	}
	else
	{
		if(file_exists($NewPDF))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>
