<?php
class Color
{
	var $Row;
	var $TotalColor;
	var $Style;
	function color()
	{
		$this->Row = 0;
		$this->TotalColor = 2;
		$this->Style[0] = 'Row1';
		$this->Style[1] = 'Row2';
	}
	function put()
	{
		switch($this->Row%$this->TotalColor)
		{
			case 0 : $Color = $this->Style[0];$this->Row++;break;
			case 1 : $Color = $this->Style[1];$this->Row++;break;
			default : $Color = 'Submit';$this->Row++;break;
		}
		return $Color;
	}
}
class Browser
{
	var $Name;
	var $Version;
	var $useragent;
	var $matched;
	function Browser()
	{
		$this->useragent = $_SERVER['HTTP_USER_AGENT'];
		if(preg_match('|MSIE ([0-9].[0-9]{1,2})|',$this->useragent,$this->matched))
		{
		    $this->Version = $this->matched[1];
			$this->Name = 'MSIE';
		}
		elseif(preg_match( '|Opera ([0-9].[0-9]{1,2})|',$this->useragent,$this->matched)) 
		{
		    $this->Version = $this->matched[1];
			$this->Name = 'Opera';
		}
		elseif(preg_match('|Firefox/([0-9\.]+)|',$this->useragent,$this->matched)) 
		{
			$this->Version = $this->matched[1];
			$this->Name = 'Firefox';
		}
		elseif(preg_match('|Safari/([0-9\.]+)|',$this->useragent,$this->matched)) 
		{				
			if(preg_match('|Chrome/([0-9\.]+)|',$this->useragent,$this->matched)) 
			{
				$this->Version=$this->matched[1];
				$this->Name = 'Chrome';
			}
			else
			{
				$this->Version=$this->matched[1];
				$this->Name = 'Safari';
			}
		}
		elseif(preg_match('|Chrome/([0-9\.]+)|',$this->useragent,$this->matched))
		{
			$this->Version=$this->matched[1];
			$this->Name = 'Chrome';
		}
		else
		{
			$this->Version = 0;
			$this->Name= 'other';
		}
	}
	function GetName()
	{
		return $this->Name;
	}
	function GetVersion()
	{
		return $this->Version;
	}
	function IsIE()
	{
		if($this->Name == 'MSIE')
		{
			return true;
		}
		return false;
	}
	function GetInfo()
	{
		$Array[0] = $this->Name;
		$Array[1] = $this->Version;
		return $Array;
	}
}
class NumberToText
{
	var $ones;
	var $tens;
	var $others;
	var $inter;
	var $IsInter = false;
	var $Amount;

	function ToText($Num = 0,$other)
	{
		if($other == 0)
		{
			$Th = (int) ($Num / 1000);
			$H = ($Num / 100) % 10;
			$T = $Num % 100;

			$Text = '';
			if($H > 0)
			{
				$Text = $this->ones[$H] . " hundred";
			}
			if($T < 20)
			{
				$Text .= $this->ones[$T];
			}
			else
			{
				$Text .= $this->tens[(int)($T/10)].$this->ones[(int)($T%10)];
			}
			if(trim($Text) != '')
			{
				$Text .= $this->others[$other];
			}
			if($Th > 0)
			{
				return $this->ToText($Th,$other+1).$Text;
			}
			else
			{
				return $Text;
			}
		}
		else
		{
			$H = (int)($Num / 100);
			$T = $Num % 100;

			if($T < 20)
			{
				$Text = $this->ones[$T];
			}
			else
			{
				$Text .= $this->tens[(int)($T/10)].$this->ones[(int)($T%10)];
			}
			if(trim($Text) != '')
			{
				$Text .= $this->others[$other];
			}
			if($H > 0)
			{
				return $this->ToText($H,$other+1).$Text;
			}
			else
			{
				return $Text;
			}
		}
	}
	function ToInterText($Num = 0,$other)
	{
		$Th = (int) ($Num / 1000);
		$H = ($Num / 100) % 10;
		$T = $Num % 100;

		$Text = '';
		if($H > 0)
		{
			$Text = $this->ones[$H] . " hundred";
		}
		if($T < 20)
		{
			$Text .= $this->ones[$T];
		}
		else
		{
			$Text .= $this->tens[(int)($T/10)].$this->ones[(int)($T%10)];
		}
		if(trim($Text) != '')
		{
			$Text .= $this->inter[$other];
		}
		if($Th > 0)
		{
			return $this->ToInterText($Th,$other+1).$Text;
		}
		else
		{
			return $Text;
		}
	}
	function NumberToText($Num,$Inter = 0)
	{
		$this->ones[0] = '';
		$this->ones[1] = ' one';
		$this->ones[2] = ' two';
		$this->ones[3] = ' three';
		$this->ones[4] = ' four';
		$this->ones[5] = ' five';
		$this->ones[6] = ' six';
		$this->ones[7] = ' seven';
		$this->ones[8] = ' eight';
		$this->ones[9] = ' nine';
		$this->ones[10] = ' ten';
		$this->ones[11] = ' eleven';
		$this->ones[12] = ' twelve';
		$this->ones[13] = ' thirteen';
		$this->ones[14] = ' fourteen';
		$this->ones[15] = ' fifteen';
		$this->ones[16] = ' sixteen';
		$this->ones[17] = ' seventeen';
		$this->ones[18] = ' eighteen';
		$this->ones[19] = ' nineteen';

		$this->tens[0] = '';
		$this->tens[1] = '';
		$this->tens[2] = ' twenty';
		$this->tens[3] = ' thirty';
		$this->tens[4] = ' forty';
		$this->tens[5] = ' fifty';
		$this->tens[6] = ' sixty';
		$this->tens[7] = ' seventy';
		$this->tens[8] = ' eighty';
		$this->tens[9] = ' ninety';

		$this->others[0] = '';
		$this->others[1] = ' thousand';
		$this->others[2] = ' lakh';
		$this->others[3] = ' crore';
		$this->others[4] = ' arawb';
		$this->others[5] = ' Kharawb';
		$this->others[6] = ' neel';
		$this->others[7] = ' padma';
		$this->others[8] = ' shankh';
		$this->others[9] = ' mahashankh';

		$this->inter[0] = '';
		$this->inter[1] = ' thousand';
		$this->inter[2] = ' million';
		$this->inter[3] = ' billion';
		$this->inter[4] = ' trillion';
		$this->inter[5] = ' quadrillion';
		$this->inter[6] = ' quintillion';
		$this->inter[7] = ' sextillion';
		$this->inter[8] = ' septillion';
		$this->inter[9] = ' octillion';
		$this->inter[10] = ' nonillion';

		$this->Amount = $Num;
		if($Inter == 1)
		{
			$this->IsInter = true;
		}
	}
	function GetText()
	{
		$Paise = (int)str_replace(".","",strrchr($this->Amount,"."));
		if(strlen($Paise) == 1)
		{
			$Paise *= 10;
		}
		$Num = (int)$this->Amount;
		if($Num < 0)
		{
			if($Paise > 0)
			{
				$Result[0] = "negative ".$this->ToText($Num,0);
				$Result[1] = $this->ToText($Paise,0);
			}
			else
			{
				$Result = "negative ".$this->ToText($Num,0);
			}
		}
		elseif($Num == 0)
		{
			if($Paise > 0)
			{
				$Result[0] = "zero";
				$Result[1] = $this->ToText($Paise,0);
			}
			else
			{
				$Result = "zero";
			}
		}
		else
		{
			if($Paise > 0)
			{
				$Result[0] = $this->ToText($Num,0);
				$Result[1] = $this->ToText($Paise,0);
			}
			else
			{
				$Result = $this->ToText($Num,0);
			}
		}
		return $Result;
	}
	function GetGlobalText()
	{
		$Paise = str_replace(".","",strrchr($this->Amount,"."));
		$Num = (int)$this->Amount;
		if($Num < 0)
		{
			if($Paise > 0)
			{
				$Result[0] = "negative ".$this->ToInterText($Num,0);
				$Result[1] = $this->ToInterText($Paise,0);
			}
			else
			{
				$Result = "negative ".$this->ToInterText($Num,0);
			}
		}
		elseif($Num == 0)
		{
			if($Paise > 0)
			{
				$Result[0] = "zero";
				$Result[1] = $this->ToInterText($Paise,0);
			}
			else
			{
				$Result = "zero";
			}
		}
		else
		{
			if($Paise > 0)
			{
				$Result[0] = $this->ToInterText($Num,0);
				$Result[1] = $this->ToInterText($Paise,0);
			}
			else
			{
				$Result = $this->ToInterText($Num,0);
			}
		}
		return $Result;
	}
}
function CheckEmail($email) //Function to check email validity
{
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) 
	{
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}
	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) 
	{
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]))
		{
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1]))
	{ // Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2)
		{
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++)
		{
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i]))
			{
				return false;
			}
		}
	}
	return true;
}
function ImageResize($SourceFile,$DestinationFile,$Width=0,$Height=0)
{
	global $StaffWidth;
	global $StaffHeight;
	if($Width == 0)
	{
		$Width=$StaffWidth;
	}
	if($Height == 0)
	{
		$Height=$StaffHeight;
	}
	$photo = urldecode($SourceFile);
	$twidth = $Width;
	$theight = $Height;
	$FileName = basename($photo);

	$Ext = strrchr($FileName,".");
	$rand = substr(md5(uniqid(microtime())), 0, 3);
	$ImageFile = str_replace($Ext,'_'.$rand.$Ext,str_replace("_","-",$FileName));
	$ImageFile = str_replace(" ","-",$ImageFile);
	
	$DestinationFile = str_replace($FileName,$ImageFile,$DestinationFile);

	$Type = strtolower(strrchr($FileName,'.'));
	list($width, $height, $type, $attr) = getimagesize($photo);
	if($width < $twidth)
	{
		$twidth = $width;
	}
	IMcreatethumb($photo, $twidth, $DestinationFile);
	if(file_exists($DestinationFile))
	{
		return $ImageFile;
	}
	else
	{		
		if($Type == ".png")
		{
			$simg = imagecreatefrompng($photo);
		}
		else if($Type == ".jpg" || $Type == ".jpeg")
		{
			$simg = imagecreatefromjpeg($photo);
		}
		else
		{
			$simg = imagecreatefromgif($photo);
		}
		if(!$simg)
		{
			return 0;
		}
		else
		{
			$currwidth = imagesx($simg);
			$currheight = imagesy($simg);
			if($currwidth== '' || $currheight == '')
			{
				return 0;
			}
			else
			{
				if($theight != '' && $twidth == '')
				{
					$image_ratio = $currwidth / $currheight;
					$image_new_height = $theight;
					$image_new_width = $image_ratio*$image_new_height;
				}
				if($theight == '' && $twidth != '')
				{
					$image_ratio = $currwidth / $currheight;
					$image_new_width = $twidth;
					$image_new_height = $image_new_width/$image_ratio;
				}
				if($theight > 0 && $twidth > 0)
				{
					if ($currheight < $theight)
					{
						if ($currwidth < $twidth)
						{
							$image_new_width = $currwidth;
							$image_new_height = $currheight;
						}
						else
						{
							if ($currwidth > $currheight)
							{
								$image_ratio = $currwidth / $twidth;
								$image_new_height = $currheight/$image_ratio;
								$image_new_width = $twidth;
							}
							else 
							{
								$image_ratio = $currheight / $theight;
								$image_new_height = $theight;
								$image_new_width = $currwidth/$image_ratio;
							}
						}
					}
					else
					{
						if ($currwidth > $twidth)
						{
							if ($currheight < $currwidth)
							{
								$image_ratio = $currwidth / $twidth;
								$image_new_height = $currheight/$image_ratio;
								$image_new_width = $currwidth/$image_ratio;
							}
							else
							{
								$image_ratio = $currheight / $theight;
								$image_new_height = $currheight/$image_ratio;
								$image_new_width = $currwidth/$image_ratio;
							}
						}
						else 
						{
							$image_ratio = $currheight / $theight;
							$image_new_height = $currheight/$image_ratio;
							$image_new_width = $currwidth/$image_ratio;
						}

					}
				}
			}
		}
		$thumbnail = imagecreatetruecolor($image_new_width, $image_new_height);
		$Imah = imagecopyresampled($thumbnail, $simg, 0, 0, 0, 0, $image_new_width, $image_new_height, $currwidth, $currheight);

		if($Type == ".png")
		{
			imagejpeg($thumbnail,$DestinationFile);
		}
		else if($Type == ".jpg" || $Type == ".jpeg")
		{
			imagejpeg($thumbnail,$DestinationFile);
		}
		else
		{
			imagejpeg($thumbnail,$DestinationFile);
		}

		imagedestroy($simg);
		imagedestroy($thumbnail);
		return $ImageFile;
	}
}
function UploadFile($Filename,$Folder,$NewName = '')
{
	$Ext = strrchr($_FILES[$Filename]['name'],".");
	$Name = str_replace($Ext,"",$_FILES[$Filename]['name']);
	if($NewName != '')
	{
		$Name = $NewName;
	}
	$rand = '_'.substr(md5(uniqid(microtime())), 0, 3);
	$ImageFile = $Name.$rand.$Ext;
	$Move = move_uploaded_file($_FILES[$Filename]['tmp_name'], $Folder.$ImageFile);
	if($Move)
	{
		return $ImageFile;
	}
	else
	{
		return false;
	}
}
function DownloadFile($file)
{
	if (!is_file($file)) { die("<b>404 File not found!</b>"); }
	$len = filesize($file);
	$filename = basename($file);
	$file_extension = strtolower(substr(strrchr($filename,"."),1));
	$file_no_ext = str_replace(".".$file_extension,"",$filename);
//	$newfilename = str_replace(" ","_",str_replace($file_no_ext,$file_no_ext."_".ShowDate(time()),$filename));
	$newfilename = str_replace(" ","_",ShowFileName($filename));

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
function Status($Value)
{
	switch($Value)
	{
		case 0 : $Status = 'Disabled'; break;
		case 1 : $Status = 'Active'; break;
		case 2 : $Status = 'Closed'; break;
		default : $Status = 'No Data provided'; break;
	}
	return $Status;
}
function Timestamp($Date,$Sep = "/")
{
	$seldate = explode($Sep, $Date);
	$MM = $seldate[0];
	$DD = $seldate[1];
	$YY = $seldate[2];
	$timestamp = mktime(0, 0, 0, $MM, $DD, $YY);
	return $timestamp;
}
function CreateTime($Date,$H,$M,$AP,$Sep = "/")
{
	if($AP == '')
	{
		$AP = 'am';
	}
	$seldate = explode($Sep, $Date);
	if(count($seldate) < 3)
	{
		$MM = date('m',$Date);
		$DD = date('d',$Date);
		$YY = date('y',$Date);
	}
	else
	{
		$MM = $seldate[0];
		$DD = $seldate[1];
		$YY = $seldate[2];
	}
	if(strtolower($AP) == 'pm' && $H < 12)
	{
		$H += 12;
	}
	if(strtolower($AP) == 'am' && $H == 12)
	{
		$H = 0;
	}
	$Time = mktime($H,$M,0,$MM,$DD,$YY);
	return $Time;
}
function GetTime($Timestamp)
{
	$Time["h"] = date("h",$Timestamp);
	$Time["m"] = date("i",$Timestamp);
	$Time["ap"] = date("a",$Timestamp);
	return $Time;
}
function SecToDay($SS,$String = 1)
{
	$MM		= floor($SS/60);
	$HH		= floor($MM/60);
	$Days	= floor($HH/24);
	$Min	= ($MM%60);
	$Hour	= ($HH%24);
	if($Days > 0)
	{
		$str = $Days."day";
		if ($Days > 1)
		{
			$str .= "s";
		}
		if ($Hour > 0)
		{
			$str .= " ".$Hour." hr";
		}
		if ($Hour > 1)
		{
			$str .= "s";
		}
		if ($Min > 0)
		{
			$str .= " ".$Min." min";
		}
		if ($Min > 1)
		{
			$str .= "s";
		}
	}
	else If($Hour>0)
	{
		$str = $Hour." hr";
		if ($Hour > 1)
		{
			$str .= "s";
		}
		if ($Min > 0)
		{
			$str .= " ".$Min." min";
		}
		if ($Min > 1)
		{
			$str .= "s";
		}
	}
	else if ($Min > 0)
	{
			$str = " ".$Min." min";
			if ($Min > 1)
			{
				$str .= "s";
			}
	}
	else
	{
		$str = "-";
	}
	if($String == 1)
	{
		return $str;
	}
	else
	{
		return array($Days,$Hour,$Min);
	}
}
function SecToHour($SS,$String = 1)
{
	$MM		= floor($SS/60);
	$Hour	= floor($MM/60);
	$Min	= ($MM%60);
	If($Hour>0)
	{
		$str = $Hour." hr";
		if ($Hour > 1)
		{
			$str .= "s";
		}
		if ($Min > 0)
		{
			$str .= " ".$Min." min";
		}
		if ($Min > 1)
		{
			$str .= "s";
		}
	}
	else if ($Min > 0)
	{
			$str = " ".$Min." min";
			if ($Min > 1)
			{
				$str .= "s";
			}
	}
	else
	{
		$str = "-";
	}
	if($String == 1)
	{
		return $str;
	}
	else
	{
		return array($Hour,$Min);
	}
}
function TimeTrack($Timestamp)
{
	$SS = (int)(time() - $Timestamp);
	if((date("Y")-date("Y",$Timestamp)) < 2)
	{
		if((date("Y")-date("Y",$Timestamp)) < 1)
		{
			if((date("m")-date("m",$Timestamp)) < 2)
			{
				if((date("m")-date("m",$Timestamp)) < 1)
				{
					if(floor((date("d")-date("d",$Timestamp))/7) < 2)
					{
						if(floor((date("d")-date("d",$Timestamp))/7) < 1)
						{
							if((date("d")-date("d",$Timestamp)) < 2)
							{
								if((date("d")-date("d",$Timestamp)) < 1)
								{
									if($SS < 60)
									{
										$MSG = ($SS > 1)?$SS." secs ago":$SS." sec ago";
									}
									else
									{
										$Time = SecToDay($SS,0);
										if($Time[2] != 0)
										{
											$MSG = ($Time[2] > 1)?$Time[2]." mins ago":$Time[2]." min ago";
										}
										if($Time[1] != 0)
										{
											$MSG = ($Time[1] > 1)?$Time[1]." hours ago":$Time[1]." hour ago";
										}
									}
								}
								else
								{
									$MSG = "Yesterday";
								}
							}
							else
							{
								$MSG = floor(date("d")-date("d",$Timestamp))." days ago";
							}
						}
						else
						{
							$MSG = "Last Week";
						}
					}
					else
					{
						$MSG = floor((date("d")-date("d",$Timestamp))/7)." weeks ago";
					}
				}
				else
				{
					$MSG = "Last Month";
				}
			}
			else
			{
				$MSG = (date("m")-date("m",$Timestamp))." months ago";
			}
		}
		else
		{
			if($SS < (180*24*3600))
			{
				if((12 + date("m")-date("m",$Timestamp)) < 1)
				{
					if(floor((date("d")-date("d",$Timestamp))/7) < 2)
					{
						if(floor((date("d")-date("d",$Timestamp))/7) < 1)
						{
							if((date("d")-date("d",$Timestamp)) < 2)
							{
								if((date("d")-date("d",$Timestamp)) < 1)
								{
									if($SS < 60)
									{
										$MSG = ($SS > 1)?$SS." secs ago":$SS." sec ago";
									}
									else
									{
										$MSG = SecToDay($SS);
									}
								}
								else
								{
									$MSG = "Yesterday";
								}
							}
							else
							{
								$MSG = floor(date("d")-date("d",$Timestamp))." days ago";
							}
						}
						else
						{
							$MSG = "Last Week";
						}
					}
					else
					{
						$MSG = floor((date("d")-date("d",$Timestamp))/7)." weeks ago";
					}
				}
				else
				{
					$MSG = "Last Month";
				}
			}
			else
			{
				$MSG = "Last Year";
			}
		}
	}
	else
	{
		$MSG = (date("Y")-date("Y",$Timestamp))." years ago";
	}
	return $MSG;
}
function StaffName($ID)
{
	$query = mysql_query("SELECT firstname,lastname FROM om_staff WHERE id='".$ID."'");
	$Name = @mysql_result($query,0,"firstname")." ".@mysql_result($query,0,"lastname");
	return $Name;
}
function StaffNickName($ID)
{
	$query = mysql_query("SELECT nickname FROM om_staff WHERE id='".$ID."'");
	$Name = @mysql_result($query,0,"nickname");
	return $Name;
}
function StaffImage($ID)
{
	$query = mysql_query("SELECT imagefile FROM om_staff WHERE id='".$ID."'");
	$Image = @mysql_result($query,0,"imagefile");
	return $Image;
}
function StaffSalary($ID)
{
	$query = mysql_query("SELECT salarypermonth FROM om_staff WHERE id='".$ID."'");
	$Salary = @mysql_result($query,0,"salarypermonth");
	return $Salary;
}
function GetStaffInfo($ID,$Value = '*')
{
	if(is_array($Value))
	{
		foreach($Value as $field)
		{
			$GetFields .= $field.", ";
		}
		$GetFields .= "#";
		$GetFields = str_replace(", #","",$GetFields);
	}
	else
	{
		$GetFields = $Value;
	}
	$query = mysql_query("SELECT ".$GetFields." FROM om_staff WHERE id='".$ID."'");
	if($query)
	{
		$result = mysql_fetch_assoc($query);
		if(is_array($Value))
		{
			return $result;
		}
		else
		{
			return $result[$Value];
		}
	}
	else
	{
		return false;
	}
}
function ShowTime($Timestamp)
{
	return date("h:i a",$Timestamp);
}
function ShowDate($Time)
{
	return date("d-M-Y",$Time);
}
function ShowDay($Value)
{
	switch($Value)
	{
		case 0 : $Day = "Sunday";break;
		case 1 : $Day = "Monday";break;
		case 2 : $Day = "Tuesday";break;
		case 3 : $Day = "Wednusday";break;
		case 4 : $Day = "Thursday";break;
		case 5 : $Day = "Friday";break;
		case 6 : $Day = "Saturday";break;
		default : return;break;
	}
	return $Day;
}
function HeaderLocation($URL)
{
	header("Location:".$URL);
	die;
}
function SessionID()
{
	return md5($_SESSION['WPStockAdminID'].$_SESSION['WPStockLoginTime']);
}
function CountPermision($Variable)
{
	if(count($Variable) > 0)
	{
		foreach($Variable as $Key => $Value)
		{
			$Total += (int)$Value;
		}
		return $Total;
	}
	else
	{
		return $Variable;
	}
}
function SetPermission($StaffID)
{
	//$DeleteQuery = mysql_query("DELETE FROM wpi_sessions WHERE expire < '".(time()-(24*3600))."'");
	$StaffQuery = mysql_query("SELECT status FROM stk_staff WHERE id='".$StaffID."'");
	$Status = @mysql_result($StaffQuery,0,"status");
	if($Status == 0)
	{
		$_SESSION['WPStockPermissions'] = 0;
		//echo $_SESSION['WPStockAdminID'];
		$_SESSION['WPStockAdminID']	 = '';
		$_SESSION['WPStockLoginTime']	 = '';
		$_SESSION['WPStockModule']		 = '';
	}
	else
	{
		
		$Query = mysql_query("SELECT * FROM stk_permissions WHERE staffid='".$StaffID."'");

		$Num   = mysql_num_fields($Query);
		$i     = 2;
		while($Num > $i)
		{
			$Array[str_replace("mod_","",mysql_field_name($Query,$i))]      = @mysql_result($Query,0,mysql_field_name($Query,$i));
			$FieldNames[str_replace("mod_","",mysql_field_name($Query,$i))] = mysql_field_name($Query,$i);
			$i++;
		}
		$_SESSION['WPStockPermissions'] = $Array;
		$_SESSION['WPStockFields']      = $FieldNames;	
	}
}
function GetPermission($Key)
{
	if(array_key_exists(strtolower($Key),$_SESSION['WPStockPermissions']))
	{
		//echo $_SESSION['WPStockPermissions'][$Key];
		return $_SESSION['WPStockPermissions'][$Key];
	}
	else
	{
		return 77;
	}
}
function GetField($Key)
{
	if(array_key_exists(strtolower($Key),$_SESSION['WPStockFields']))
	{
		return $_SESSION['WPStockFields'][$Key];
	}
	else
	{
		return;
	}
}
function GetDataPermission($Num)
{
	$Self = ($Num % 10);
	$Other = floor($Num / 10);
	switch($Self)
	{
		case 1 : 	{
						$DataPermission[0][0] = 1;
						$DataPermission[0][1] = 0;
						$DataPermission[0][2] = 0;
						break;
					}
		case 2 : 	{	
						$DataPermission[0][0] = 0;
						$DataPermission[0][1] = 1;
						$DataPermission[0][2] = 0;
						break;
					}
		case 3 : 	{	
						$DataPermission[0][0] = 1;
						$DataPermission[0][1] = 1;
						$DataPermission[0][2] = 0;
						break;
					}
		case 4 :	{
						$DataPermission[0][0] = 0;
						$DataPermission[0][1] = 0;
						$DataPermission[0][2] = 1;
						break;
					}
		case 5 :	{
						$DataPermission[0][0] = 1;
						$DataPermission[0][1] = 0;
						$DataPermission[0][2] = 1;
						break;
					}
		case 6 :	{
						$DataPermission[0][0] = 0;
						$DataPermission[0][1] = 1;
						$DataPermission[0][2] = 1;
						break;
					}
		case 7 :	{
						$DataPermission[0][0] = 1;
						$DataPermission[0][1] = 1;
						$DataPermission[0][2] = 1;
						break;
					}
		default  :	{
						$DataPermission[0][0] = 0;
						$DataPermission[0][1] = 0;
						$DataPermission[0][2] = 0;
						break;
					}
	}
	switch($Other)
	{
		case 1   : 	{
						$DataPermission[1][0] = 1;
						$DataPermission[1][1] = 0;
						$DataPermission[1][2] = 0;
						break;
					}
		case 2 : 	{	
						$DataPermission[1][0] = 0;
						$DataPermission[1][1] = 1;
						$DataPermission[1][2] = 0;
						break;
					}
		case 3 : 	{	
						$DataPermission[1][0] = 1;
						$DataPermission[1][1] = 1;
						$DataPermission[1][2] = 0;
						break;
					}
		case 4 :	{
						$DataPermission[1][0] = 0;
						$DataPermission[1][1] = 0;
						$DataPermission[1][2] = 1;
						break;
					}
		case 5 :	{
						$DataPermission[1][0] = 1;
						$DataPermission[1][1] = 0;
						$DataPermission[1][2] = 1;
						break;
					}
		case 6 :	{
						$DataPermission[1][0] = 0;
						$DataPermission[1][1] = 1;
						$DataPermission[1][2] = 1;
						break;
					}
		case 7 :	{
						$DataPermission[1][0] = 1;
						$DataPermission[1][1] = 1;
						$DataPermission[1][2] = 1;
						break;
					}
		default  :	{
						$DataPermission[1][0] = 0;
						$DataPermission[1][1] = 0;
						$DataPermission[1][2] = 0;
						break;
					}
	}
	return $DataPermission;
}
function ReadCheck($ID=0,$PageCheck)
{
	if($ID > 0)
	{
		if($PageCheck[1][0] == 1 && $_SESSION["WPStockAdminID"] == $ID)
		{
			return '2';
		}
		else
		{
			if($PageCheck[0][0] == 1 && $_SESSION["WPStockAdminID"] == $ID)
			{
				return '1';
			}
			else
			{
				return '0';
			}
		}
	}
	else
	{
		if(($PageCheck[0][0] == 1) && ($PageCheck[1][0] == 1))
		{
			return '2';
		}
		else
		{
			return '0';
		}
	}
}
function EditCheck($ID=0,$PageCheck)
{
	if($ID > 0)
	{
		if($PageCheck[1][1] == 1 && $_SESSION["WPStockAdminID"] == $ID)
		{
			return '2';
		}
		else
		{
			if($PageCheck[0][1] == 1 && $_SESSION["WPStockAdminID"] == $ID)
			{
				return '1';
			}
			else
			{
				return '0';
			}
		}
	}
	else
	{
		if(($PageCheck[0][1] == 1 ) && ($PageCheck[1][1] == 1))
		{
			return '2';
		}
		else
		{
			return '0';
		}
	}
}
function DeleteCheck($ID=0,$PageCheck)
{
	if($ID > 0)
	{
		if($PageCheck[1][2] == 1 && $_SESSION["WPStockAdminID"] == $ID)
		{
			return '2';
		}
		else
		{
			if($PageCheck[0][2] == 1 && $_SESSION["WPStockAdminID"] == $ID)
			{
				return '1';
			}
			else
			{
				return '0';
			}
		}
	}
	else
	{
		if(($PageCheck[0][2] == 1) && ($PageCheck[1][2] == 1))
		{
			return '2';
		}
		else
		{
			return '0';
		}
	}
}
function AdminCheck($PageCheck)
{
	if(DeleteCheck(0,$PageCheck) == true && EditCheck(0,$PageCheck) == true && ReadCheck(0,$PageCheck) == true)
	{
		return true;
	}
	else
	{
		return false;
	}
}
function DateRangeArray($Start,$End)
{
	$Inc=(24*60*60);
	$Dates = Array();
	while($Start <= $End)
	{
		$StartDate = $Start;
		$Dates[$StartDate] = Array();
		$Start += $Inc;
	}
	return $Dates;
}
function CloseWindow($ErrorMSG)
{
?>
	<script>
		<?
		if($ErrorMSG != '')
		{
		?>
			alert("<?=$ErrorMSG;?>");
		<?
		}
		?>
		window.close();
	</script>
<?
}
function cal($Month,$Year,$strday='0')//default sunday(0) starting day - ..Saturday(8),Monday(6),Tuesday(5)
{
	$Month = (int)$Month;
	$Year = (int)$Year;
	if($Year=='')
	{
		$Year = date("Y",time());
	}
	if($Month=='')
	{
		$Month = date("n",time());
	}
	if($Month>12 || $Month<1)
	{
		return 0;
	}
	if($Year>3000 || $Year<1941)
	{
		return 0;
	}
	if($strday>6 || $strday<0)
	{
		return 0;
	}

	$todaytime = mktime(0,0,0,DATE(m),DATE(d),DATE(y));
	$dayinamonth = array("","31","28","31","30","31","30","31","31","30","31","30","31");
	$timestamp = mktime(0, 0, 0, $Month, 1, $Year);
	$Firstday = date("w",$timestamp);
	$sub = ($Firstday+$strday);//+6 If first day is Monday, +7 If First day is Sunday
	if($Month==2 AND ($Year%4)==0)
	{
		$Monthdays = 29;
	}
	else
	{
		$Monthdays = $dayinamonth[$Month];
	}
	$cal = array();
	for($i=1;$i<=6;$i++)
	{
		for($j=1;$j<=7;$j++)
		{
			$datecal = (($i*7)+$j-7) - $sub;
				if($datecal>0 AND $datecal<=$Monthdays)
				{
				$cal[$i][$j] = $datecal;
				}
		}
	}
	return $cal;
}
function GetVariables($Except=0)
{
	foreach($_GET as $key => $value)
	{
		if($Except != 0)
		{
			if(count($Except) > 0)
			{
				if(in_array($key,$Except))
				{
					continue;
				}
			}
		}
		$extraArg .= $key."=".urlencode($value)."&";
	}
	$extraArg =  str_replace(" ","%20",$extraArg);
	return $extraArg;
}
function AccessKey($Word)
{
	$Alpha = substr(strstr($Word,"_"),1,1);
	if(trim($Alpha) != '')
	{
		$Access["Key"] = $Alpha;
		$Access["Word"] = str_replace("_".$Alpha,"<u>".$Alpha."</u>",$Word);
		return $Access;
	}
	else
	{
		return false;
	}
}
function ShowFileName($FileName)
{
	$FileCode = str_replace(strrchr($FileName,'.'),'',strrchr($FileName,'_'));
	return str_replace($FileCode,'',$FileName);
}
function GotoModule()
{
	global $MF;
	global $Module;
	foreach($Module as $Value)
	{
		if($Value['name'] == $_SESSION['WPStockModule'])
		{
			$Index = $Value['foldername'].'/'.$Value['firstpage'];
			break;
		}
	}
	HeaderLocation($Index);
}
function MakeLinks($text) 
{
	$text = eregi_replace('(((f|ht){1}(tp|tps)://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)','<a href="\\1">\\1</a>',$text);
	$text = eregi_replace('([[:space:]]{0,1})(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)','\\1<a href="http://\\2">\\2</a>',$text);
	$text = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})','<a href="mailto:\\1">\\1</a>',$text);
	return $text;
}
function AddHttp($text)
{
	if(!preg_match("/http|https/",$text))
	{
		$text = eregi_replace('(([-a-zA-Z0-9@:%_\+.~#?&//=]+).[a-zA-Z0-9]{2,6})|(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)|([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})','http://\\1',$text);
		return $text;
	}
	else
	{
		return $text;
	}
}
function GetTextBetweenTags($tag, $html, $strict=0)
{
    /*** a new dom object ***/
    $dom = new domDocument;

    /*** load the html into the object ***/
    if($strict==1)
    {
        @$dom->loadXML($html);
    }
    else
    {
        @$dom->loadHTML($html);
    }

    /*** discard white space ***/
    $dom->preserveWhiteSpace = false;

    /*** the tag by its tag name ***/
    $content = $dom->getElementsByTagname($tag);

    /*** the array to return ***/
    $out = array();
    foreach ($content as $item)
    {
        /*** add node value to the out array ***/
        $out[] = $item->nodeValue;
    }
    /*** return the results ***/
    return $out;
}
function wsFileSize($file, $precision = 0)
{
	$size = filesize(addslashes($file));
    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'kB', 'B');
    $total = count($sizes);

    while($total-- && $size > 1024) $size /= 1024;
    return round($size, $precision)." ".$sizes[$total];
}
function EmailHeader()
{
	$eol = "\n";
	$headers = $eol;
	$headers .= "X-Mailer: PHP v ".phpversion().$eol;
	$headers .="MIME-Version: 1.0".$eol;
	$headers .='Content-Type: text/html; charset="ISO-8859-1"'.$eol;
	$headers .='Content-Transfer-Encoding: 8bit'.$eol;
	return $headers;
}
function EmailMessage($Msg,$Title='')
{
	$EmailMessage = '
	<table cellspacing="5" cellpadding="5" border="0">
		<tr>
			<td align="left" style="font-size:28px;border-bottom:2px solid #000000;">'.$Title.'</td>
		</tr>
		<tr>
			<td>
			'.$Msg.'
			</td>
		</tr>
	</table>
	';
	return $EmailMessage;
}
function StaffCondition($Condition)
{
	if($Condition == '')
	{
		$Condition = " WHERE status = '1' ";
	}
	else
	{
		$Condition .= " AND status = '1' ";
	}
	return $Condition;
}
function FileTypeImg($filename)
{
	global $MF;
	$FileInfo = pathinfo($filename);
	$ext = strtolower($FileInfo['extension']);
	switch($ext)
	{
		case "pdf": $Image = 'acrobat_pdf.png';break;
		case "png": 
		case "bmp": 
		case "jpeg": 
		case "jpg": 
		case "gif": $Image = 'fireworks_gif.png';break;
		case "docx": 
		case "doc": $Image = 'generic_text.png';break;
		case "mov": $Image = 'quicktime_mov.png';break;
		case "wmv": $Image = 'windows_wmv.png';break;
		case "rar":
		case "zip": $Image = 'generic_zipped.png';break;
		case "swf": $Image = 'flash_swf.png';break;
		case "flv": $Image = 'flash_flv.png';break;
		case "fla": $Image = 'flash_fla.png';break;
		case "psd": $Image = 'photoshop_psd.png';break;
		case "pptx": 
		case "ppt": $Image = 'generic_powerpoint.png';break;
		case "css": $Image = 'dreamweaver_css.png';break;
		case "ai": $Image = 'illustrator_ai.png';break;
		case "eps": $Image = 'illustrator_eps.png';break;
		case "as": $Image = 'flash_as.png';break;
		case "jsfl": $Image = 'flash_jsfl.png';break;
		case "html": $Image = 'dreamweaver_code.png';break;
		case "xlsx": 
		case "xls": $Image = 'excel.gif';break;
		case "txt": $Image = 'txt.gif';break;

		default :  $Image = 'txt.gif';break;
	}
	$ShowImage = '<img src="images/"'.$Image.'" style="border:none;">';
	return $ShowImage;
}
function Editor($EditorFor,$Args = 0)
{
	global $EditorFolder;
	$Height = '200';
	$Width	= '100%';
	$Toolbar= 'WebSewakPMS';
	$Path	= $EditorFolder;
	if(is_array($Args))
	{
		$Width		= ($Args['width'] != '')?$Args['width']:$Width;
		$Width		= ($Args[0] != '')?$Args[0]:$Width;

		$Height		= ($Args['height'] != '')?$Args['height']:$Height;
		$Height		= ($Args[1] != '')?$Args[1]:$Height;

		$Toolbar	= ($Args['toolbar'] != '')?$Args['toolbar']:$Toolbar;
		$Toolbar	= ($Args[2] != '')?$Args[2]:$Toolbar;

		$Path		= ($Args['path'] != '')?$Args['path']:$Path;
		$Path		= ($Args[3] != '')?$Args[3]:$Path;
	}
	$Editor = new FCKeditor($EditorFor);
	$Editor->BasePath	= $Path;
	$Editor->Width		= $Width;
	$Editor->Height		= $Height;
	$Editor->ToolbarSet = $Toolbar;
	$Editor->Value		= html_entity_decode(stripslashes($_POST[$EditorFor]));
	$Editor->Create() ;
	?>
		<input type="hidden" id="EditorStatus" name="EditorStatus" value="0">
		<script>
		function FCKeditor_OnComplete(<?=$EditorFor;?>)
		{
			document.getElementById("EditorStatus").value = 1;
		}
		</script>
	<?
}

function HighlightWords($text, $words)
{
	if(!is_array($words))
	{
		return $text;
	}
	foreach ($words as $word)
	{
		$word = preg_quote($word);
		$text = @preg_replace("/($word)/i", '<span class="SearchResult">\1</span>', $text);
	}
	return $text;
}

function SmallText($Text,$Target = '',$Count = 80)
{
	if(strlen($Text) < $Count)
	{
		return $Text;
	}
	if($Target == '')
	{
		return $Text;
	}
	$Pos = stripos($Text,$Target);
	if(($Pos-floor($Count/2)) > 0)
	{
		$Start = ($Pos-floor($Count/2));
	}
	else
	{
		$Start = 0;
	}
	$Text = substr($Text,$Start,$Count);
	return "...".$Text."...";
}
function RemoveSlashes()
{
	if ($_POST)
	{
		foreach ($_POST as $key => $value)
		{
			$_POST[$key] = stripslashes($_POST[$key]);
		}
	}
}
function StatusBar($Width)
{
	?>
	<table cellpadding="0" cellspacing="0" style="width:100%;background-color:#FFF;border:1px solid #000;">
		<tr>
			<td style="width:<?=$Width?>%;background-color:<?=($Width > 99)?"#A00":"#0A0"?>;text-align:center;color:#FFF;">
				<?
				if($Width > 40)
				{
				?>
					<b><?=$Width;?>%</b>
				<?
				}
				?>
			</td>
			<td style="width:<?=(100-$Width);?>%;text-align:center;">
				<?
				if($Width < 41)
				{
				?>
					<b><?=$Width;?>%</b>
				<?
				}
				?>
			</td>
		</tr>
	</table>
	<?
}
function MonthDayCount($mm,$YYYY)
{
	if($mm < 1 || $mm > 12)
	{
		return "Invalid month";
	}
	if($YYYY < 1000 || $YYYY > 9999)
	{
		return "Invalid Year";
	}

	switch($mm)
	{
		case 1 :
		case 3 :
		case 5 :
		case 7 :
		case 8 :
		case 10 :
		case 12 : $MaxDays = 31; break;
		case 4 :
		case 6 :
		case 9 :
		case 11 : $MaxDays = 30; break;
		case 2 :
		{
			$MaxDays = 28;
			if(($YYYY % 4) == 0)
			{
				$MaxDays = 29;
			}
			if(($YYYY % 400) == 0)
			{
				$MaxDays = 28;
			}
		}
		break;
	}
	return $MaxDays;
}
function MultiText($Name,$Array = '')
{
	global $MF;
	?>
	<script type="text/javascript" src="js/multitext.js"></script>
	<script type="text/javascript">
		var MultiText_<?=$Name;?> = new MultiText('<?=$Name;?>');
		<?
		if(is_array($Array))
		{
			foreach($Array as $Value)
			{
			?>
			MultiText.addValue('<?=$Value;?>');
			<?
			}
		}
		?>
	</script>
	<?
}
function ColumnTable($DataArray,$Cols,$border = 0,$bordercolor = '#000000',$Name='')
{
	$Cols = ($Cols > 0)?$Cols:1;
	$Name = ($Name == '')?'ColumnTable':$Name;
	if(is_array($DataArray))
	{
		$Rows = ceil(count($DataArray)/$Cols);
		$MaxCells = ($Rows*$Cols);
		$CellCount = count($DataArray);
		$Width = floor(100/$Cols);
		?>
		<table id="<?=$Name;?>" cellspacing="0" cellpadding="0" width="100%" border="<?=$border;?>" bordercolor="<?=$bordercolor;?>" align="center">
			<?
			foreach($DataArray as $Key => $Value)
			{
				if(($Key%$Cols) == 0)
				{
					if($Key > 0)
					{
						?>
						</tr>
						<?
					}
					?>
					<tr>
					<?
				}
				?>
				<td width="<?=$Width;?>%" align="center"><?=$Value;?></td>
				<?
				if($Key == ($CellCount-1))
				{
					if($MaxCells != $CellCount AND $CellCount > $Cols)
					{
						for($Loop = 0;$Loop < ($MaxCells-$CellCount);$Loop++)
						{
							?><td width="<?=$Width;?>%" align="center">&nbsp;</td><?
						}
					}
					?>
					</tr>
					<?
				}
			}
			?>
		</table>
		<?
	}
	else
	{
		?>
		<table id="<?=$Name;?>" cellspacing="0" cellpadding="0" width="100%" border="<?=$border;?>" bordercolor="<?=$bordercolor;?>">
			<tr>
				<td><?=$DataArray;?></td>
			</tr>
		</table>
		<?
	}
}
function ImImage($SourceFile,$Width=100,$Height=100)
{
	global $MF;
	$photo = urldecode($SourceFile);
	$twidth = $Width;
	$theight = $Height;
	$FileName = basename($photo);

	$ImageFile = 'im_'.$Width.'_'.$FileName;
	$DestinationFile = 'uploads/im_images/'.$ImageFile;

	if(file_exists($DestinationFile))
	{
		return $DestinationFile;
	}

	$Type = strtolower(strrchr($FileName,'.'));
	list($width, $height, $type, $attr) = getimagesize($photo);
	if($width < $twidth)
	{
		$twidth = $width;
	}
	if(file_exists($DestinationFile))
	{
		unlink($DestinationFile);
	}
	$convertString = "convert $photo -resize $twidth $DestinationFile";
	$Check = exec($convertString);
	if(file_exists($DestinationFile))
	{
		return $DestinationFile;
	}
	else
	{		
		if($Type == ".png")
		{
			$simg = imagecreatefrompng($photo);
		}
		else if($Type == ".jpg" || $Type == ".jpeg")
		{
			$simg = imagecreatefromjpeg($photo);
		}
		else
		{
			$simg = imagecreatefromgif($photo);
		}
		if(!$simg)
		{
			return 0;
		}
		else
		{
			$currwidth = imagesx($simg);
			$currheight = imagesy($simg);
			if($currwidth== '' || $currheight == '')
			{
				return 0;
			}
			else
			{
				if($theight != '' && $twidth == '')
				{
					$image_ratio = $currwidth / $currheight;
					$image_new_height = $theight;
					$image_new_width = $image_ratio*$image_new_height;
				}
				if($theight == '' && $twidth != '')
				{
					$image_ratio = $currwidth / $currheight;
					$image_new_width = $twidth;
					$image_new_height = $image_new_width/$image_ratio;
				}
				if($theight > 0 && $twidth > 0)
				{
					if ($currheight < $theight)
					{
						if ($currwidth < $twidth)
						{
							$image_new_width = $currwidth;
							$image_new_height = $currheight;
						}
						else
						{
							if ($currwidth > $currheight)
							{
								$image_ratio = $currwidth / $twidth;
								$image_new_height = $currheight/$image_ratio;
								$image_new_width = $twidth;
							}
							else 
							{
								$image_ratio = $currheight / $theight;
								$image_new_height = $theight;
								$image_new_width = $currwidth/$image_ratio;
							}
						}
					}
					else
					{
						if ($currwidth > $twidth)
						{
							if ($currheight < $currwidth)
							{
								$image_ratio = $currwidth / $twidth;
								$image_new_height = $currheight/$image_ratio;
								$image_new_width = $currwidth/$image_ratio;
							}
							else
							{
								$image_ratio = $currheight / $theight;
								$image_new_height = $currheight/$image_ratio;
								$image_new_width = $currwidth/$image_ratio;
							}
						}
						else 
						{
							$image_ratio = $currheight / $theight;
							$image_new_height = $currheight/$image_ratio;
							$image_new_width = $currwidth/$image_ratio;
						}

					}
				}
			}
		}
		$thumbnail = imagecreatetruecolor($image_new_width, $image_new_height);
		$Imah = imagecopyresampled($thumbnail, $simg, 0, 0, 0, 0, $image_new_width, $image_new_height, $currwidth, $currheight);

		if($Type == ".png")
		{
			imagejpeg($thumbnail,$DestinationFile);
		}
		else if($Type == ".jpg" || $Type == ".jpeg")
		{
			imagejpeg($thumbnail,$DestinationFile);
		}
		else
		{
			imagejpeg($thumbnail,$DestinationFile);
		}

		imagedestroy($simg);
		imagedestroy($thumbnail);
		return $DestinationFile;
	}
}

function count_days( $a, $b )
{
	$gd_a = getdate( $a );
	$gd_b = getdate( $b );
	$a_new = mktime( 0, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year'] );
	$b_new = mktime( 24, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year'] );
	return round( abs( $a_new - $b_new ) / 86400 );
}

function SundayArray($Month,$Year)
{
	$j = 0;
	$TotalDays = MonthDayCount($Month,$Year);
	for($i=1; $i<=$TotalDays; $i++)
	{
		$GetDate = mktime(0,0,0,$Month,$i,$Year);
		$Day = date('N',$GetDate);
		if($Day == 7)
		{
			$SundayArray[$j] = $GetDate;
			$j++;
		}
	}
	return $SundayArray;
}
function SortByName($Array1, $Array2)
{
	foreach($Array2 as $Key=>$Value)
	{
		$Temp[$Value] = $Array1[$Value];
	}
	return $Temp;
}
function RemoveDuplicateKey($Array)
{
	foreach($Array as $Key=>$Value)
	{
		if($Temp[$Key] == '' || $Temp[$Key] == null)
		{
			$Temp[$Key] = $Value;
		}
	}
	return $Temp;
}
/*function SundayDates($Start, $End)
{
	$j = 0;
	$StartMonth = date('n',$Start);
	$EndMonth   = date('n',$End);
	$StartYear  = date('Y',$Start);
	$EndYear    = date('Y',$End);
	if($StartYear < $EndYear)
	{

	}
	else
	{
		if($StartMonth < $EndMonth)
		{
			$StartTotalDays = MonthDayCount($StartMonth,$StartYear);
			for($i = 1; $i <= $StartTotalDays; $i++)
			{
				$GetDate = mktime(0,0,0,$StartMonth,$i,$StartYear);
				$Day = date('N',$GetDate);
				if($Day == 7 && $GetDate > $Start)
				{
					$SundayArray[$j] = $GetDate;
					$j++;
				}
			}
			$EndTotalDays = MonthDayCount($EndMonth,$StartYear);
			for($i = 1; $i <= $EndTotalDays; $i++)
			{
				$GetDate = mktime(0,0,0,$EndMonth,$i,$StartYear);
				$Day = date('N',$GetDate);
				if($Day == 7 && $GetDate < $End)
				{
					$SundayArray[$j] = $GetDate;
					$j++;
				}
			}
		}
		else
		{
			$SundayArray = SundayArray($StartMonth,$StartYear)
		}
	}
	return $SundayArray;
}*/
function SundayDates($Start, $End)
{
	$j         = 0;
	$TempStart = $Start;
	$TempEnd   = $End;
	for($i = $TempStart; $i <= $TempEnd; $i=$i+(24*60*60))
	{
		$Day = date('N',$i);
		if($Day == 7)
		{
			$SundayArray[$j] = $i;
			$j++;
		}
	}
	return $SundayArray;
}
function IsAdminUser($ID)
{
	$SQL	= "SELECT * FROM om_permission WHERE staffid=:staffid";
	$ESQL	= array("staffid"=>(int)$ID);
	$TypeQuery = pdo_query($SQL,$ESQL);
	if(@pdo_num_rows($TypeQuery) > 0)
	{
		$Row = pdo_fetch_assoc($TypeQuery);
		$Permissions = $Row['mod_staff'];
		if($Permissions == 77)
			return "Admin";
		else
			return "User";
	}
	else
		return false;
}
function GetEMailsOnLeave()
{
	$LeaveMailUser = mysql_query("SELECT * FROM om_staff WHERE mailonleave='1'");
	$NumOfRows = mysql_num_rows($LeaveMailUser);
	if($NumOfRows > 0)
	{
		for($i = 0; $i < $NumOfRows; $i++)
		{
			$ID    = mysql_result($LeaveMailUser, $i, 'id');
			$Email = mysql_result($LeaveMailUser, $i, 'email');
			$EmailUser[$ID] = $Email;
		}
	}
	return $EmailUser;
}
function StaffEmailID($ID)
{
	$EmailQuery = mysql_query("SELECT email FROM om_staff WHERE id='".$ID."'");
	if(@mysql_num_rows($EmailQuery) > 0)
	{
		return @mysql_result($EmailQuery, 0, 'email');
	}
	else
	{
		return;
	}
}
function EditLeavePermission($ID)
{
	$PermissionQuery = mysql_query("SELECT mailonleave FROM om_staff WHERE id='".$ID."'");
	if(@mysql_num_rows($PermissionQuery) > 0)
	{
		return @mysql_result($PermissionQuery, 0, 'mailonleave');
	}
	else
	{
		return 0;
	}	
}
function DesignationName($ID)
{
	$DesignationQuery = mysql_query("SELECT * FROM om_designation WHERE id='".$ID."'");
	if(@mysql_num_rows($DesignationQuery) > 0)
	{
		return @mysql_result($DesignationQuery, 0, 'name');
	}
	else
	{
		return 0;
	}
}
function ShowNoAccessAlert()
{
	?>
	<script type="text/javascript">
		alert("You are not authorized to perform this action");
	</script>
	<?php
}
function NoAccessDisplay()
{
	?>
	<div style="font-family:Lucida Grande,arial,sans-serif; font-size:32px; font-weight:normal; text-align:center; color:red;">
		You are not authorized to perform this action
	</div>
	<?php
}
function PDONumRow($obj)
{
	if($obj)
	{
		return $obj->rowCount();
	}
}
function pdo_query($q, array $equery = null)
{
	global $pdo;

	$Arr	= array();

	try
	{
		$InsertQuery = $pdo-> prepare($q);
		if(is_array($equery) AND count($equery) > 0)
		{
			$InsertQuery->execute($equery);
		}
		else
		{
			$InsertQuery->execute($equery);
		}

		if($InsertQuery)
		{
			$Arr['errorcode']		= "";
			$Arr['errormessage']	= "";
		}
	}
	catch(PDOException $e) {

		$Arr['errorcode']		= $e->getCode();
		$Arr['errorinfo']		= $e->errorInfo[1];

		if($_SERVER['IsLocal'] == true)
		{
			$Arr['errormessage']	= $e->getMessage();
		}
		else
		{
			$Arr['errormessage']	= "Unable to process your request, please try latter";
		}

		return $Arr;

		/*echo $e->getMessage();
		echo $q;
		print_r($equery);
		print_r($e->errorInfo());*/
	}
	return $InsertQuery;
}
function pdo_num_rows($obj)
{
	if($obj)
	{
		return $obj->rowCount();
	}
}
function pdo_insert_id()
{
	global $pdo;
	return $pdo->lastInsertId();
}
function pdo_fetch_assoc($query)
{
	$rows = $query->fetch(PDO::FETCH_ASSOC);
	if(is_array($rows))
	{
		$rows = @array_map("stripslashes",$rows);
	}
	return $rows;
}
function pdo_fixed_rows($query,$index)
{
	$rows = $query->fetch(PDO::FETCH_ASSOC,$index);
	if(is_array($rows))
	{
		$rows = @array_map("stripslashes",$rows);
	}
	return $rows;
}
?>