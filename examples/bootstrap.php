<?php
$dir = dirname(__FILE__);
ini_set('display_errors', 1);
define('DIR_ROOT', $dir . '/..');
define('DIR_CERT', DIR_ROOT . '/_cert');
define('DIR_VENDOR', DIR_ROOT . '/vendor');
define('DIR_SRC', DIR_ROOT . '/src');
define('DIR_TEMP', DIR_ROOT . '/temp');
define('PLAYGROUND_WSDL',  DIR_SRC . '/Schema/PlaygroundService.wsdl');

require_once DIR_VENDOR . "/autoload.php";
