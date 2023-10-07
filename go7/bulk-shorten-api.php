<?php
/*
 * YOURLS API
 *
 * Note about translation : this file should NOT be translation ready
 * API messages and returns are supposed to be programmatically tested, so default English is expected
 *
 */
$_REQUEST['signature']	=  '99ca003d0b';
$_REQUEST['action']		=  'shorturl';
$_REQUEST['format']		=  'simple';

define( 'YOURLS_API', true );
require_once( dirname( __FILE__ ) . '/includes/load-yourls.php' );
yourls_maybe_require_auth();

$keyword = "";
$title	= "";
//$Arr = json_decode($_REQUEST['mobileurlarray']);

function GetShortURLS($Arr)
{
	$resultarr = array();

	foreach ($Arr as $mobilephone => $data)
	{
		$temparr	= array();
		$url		= $data['arg1'];
		if($_SERVER['IsLocal1'] == 'Yes')
		{
			//$linkarr['shorturl'] = 'http://www.google.com';
		}
		else
		{
			$linkarr = yourls_add_new_link( $url, $keyword, $title );
		}
		if($linkarr['shorturl'] !='')
		{
			$temparr['phoneno']	= $data['phoneno']; 
			$temparr['arg1']	= $linkarr['shorturl']; 
			$temparr['arg2']	= $data['arg2']; 
			$temparr['arg3']	= $data['arg3']; 
			$resultarr[]		= $temparr;
		}
		else
		{
			$resultarr[]		= $linkarr;
		}
	}
	return $resultarr;
}
?>