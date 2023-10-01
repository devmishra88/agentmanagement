<?
$db_url	= "localhost";
$db_name = "orlopay_db"; //write here the DB name, you have to use a single database for the whole script
$database_catalog = $db_name;
$db_username = "orlopay_user";    //obviously, username here

$db_password = 'UlO7fXcDaOedpPjD';

$Prefix ="orlop_";
/*if($_SERVER['IsLocal'] == 'Yes' || $_SERVER['SERVER_NAME'] == 'agency.orlopay.com' || $_SERVER['SERVER_NAME'] == 'orlopay.com'|| $_SERVER['SERVER_NAME'] == 'www.orlopay.com')*/

$hostname	= $_SERVER['SERVER_NAME'];
$parsedUrl	= parse_url($hostname);
$host		= explode('.', $parsedUrl['path']);

$domainname	= $host[1].".".$host[2];

if($domainname == 'orlonow.com')
{
	$db_name = "orlonow_db"; //write here the DB name, you have to use a single database for the whole script
	$database_catalog = $db_name;
	$db_username = "orlonow_user";    //obviously, username here
}

$RemoteDataBaseServer = "postgres";

$port = 'port=5432;'; // PostGRESQL port
$dsn = 'pgsql:host=localhost;'.$port.'dbname='.$database_catalog;

$port = ''; // MariaDB port Localhost

$RemoteDataBaseServer = "mysql";

if($_SERVER['IsLocal'] == 'Yes')
{
    $port = 'port=3307;'; // MariaDB port Localhost
}
$dsn = 'mysql:host=localhost;'.$port.'dbname='.$database_catalog;

// variables used for jwt
$jwtkey	= "agency2020";
$jwtiss	= "https://".$_SERVER['SERVER_NAME'];
$jwtaud = "https://".$_SERVER['SERVER_NAME'];
$jwtiat = 1595308248;
$jwtnbf = 1595308312;
?>