<?
$_REQUEST['signature'] = '99ca003d0b';
define( 'YOURLS_API', true );
require_once( dirname( __FILE__ ) . '/includes/load-yourls.php' );
yourls_maybe_require_auth();

$count = 0;
$keyword = '';
$title = '';
while($loop < 2000)
{
	$url	= "http://www.xyzu".$loop.".com";

	$linkarr = yourls_add_new_link( $url, $keyword, $title );
		
	echo "<pre>";
	print_r($linkarr);
	echo "</pre>";
	if(empty($linkarr))
	{
		die( $loop." Short Url - No response <br/>");
	}
$loop++;
}

?>