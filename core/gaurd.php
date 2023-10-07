<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
$NoGuard[0] = 'index.php';
$NoGuard[1] = 'changepass.php';
/*
$GetVariables = (GetVariables() != '')?'?'.GetVariables():'';

$GetsVars = @substr($GetVariables,1);
$arrGetVars = @explode("&",$GetsVars);
if($_SESSION[$SessionCookiePrefix.'AdminType'] != '1' && $_SESSION[$SessionCookiePrefix.'AdminType'] == '0')
{
	if($_SESSION[$SessionCookiePrefix.'AdminID'] != '' && $FileName != 'index.php')
	{
		SetPermission($_SESSION[$SessionCookiePrefix.'AdminID']);
	}

	if(count($_SESSION[$SessionCookiePrefix.'Permissions']) > 0)
	{
		foreach(@array_keys($_SESSION[$SessionCookiePrefix.'Permissions']) as $Value)
		{
			if(preg_match("/".$Value."/i",$FileName))
			{
				if(GetPermission($Value) == 0)
				{	
					$NoAccess = 1;
					break;
				}
				else
				{
					$PageCheckName = $Value;
					$PageCheck     = GetPermission($Value);
					$PageCheck     = GetDataPermission($PageCheck);
					if(preg_match("/add|edit/i",$FileName)&&($PageCheck[1][1] == 0)&&($PageCheck[0][1] == 0))
					{
						$NoAccess = 1;
						break;
					}
				}
			}
		}
	}
}*/
?>