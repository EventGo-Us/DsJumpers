<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['SECRET_KEY'];
$url_base = $_ENV['URL_BASE'];
$google_api_key = $_ENV['GOOGLE_API_KEY'];

$host       = $_ENV['host'];
$db_name    = $_ENV['db_name'];
$username   = $_ENV['username'];
$password   = $_ENV['password'];
$port       = $_ENV['port'];

$CFendpoint     = $_ENV['endpoint'];
$CFkey          = $_ENV['key'];
$CFsecret       = $_ENV['secret'];
$CFpublicurl    = $_ENV['publicurl'];

define('HOST', $host);
define('DB_NAMEP', $db_name);    
define('USERNAME', $username);
define('PASSWORD', $password);
define('PORT',$port);


if (isset($_SESSION['nombre_db'])){
    define('DB_NAME', $_SESSION['nombre_db']);
}
else{
    define('DB_NAME', '');
}

define('SECRET_KEY', $secret_key);
//define('URL_BASE', 'http://gaxybrincolines.com');
define('URL_BASE', $url_base);
// ... otras configuraciones de la aplicación
date_default_timezone_set('America/Mexico_City');

define('GOOGLE_API_KEY', $google_api_key);

define('CFENDPOINT',$CFendpoint);
define('CFKEY',$CFkey);
define('CFSECRET',$CFsecret);
define('CFPUBLICURL',$CFpublicurl);


//AJUSTAR  .htaccess e index.php en api y api-web
?>