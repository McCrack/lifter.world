<?php

require_once "core/index.php";

$config = new config();

define("BASE_FOLDER",
	isset($_COOKIE['subdomain'])
	? $_COOKIE['subdomain']
	: $config->{"base folder"}
);
define("BASE_DOMAIN", $config->{BASE_FOLDER});

$cng = new config("../".BASE_FOLDER."/".$config->{"config file"});

define("DB_HOST", $cng->{"db host"});
define("DB_NAME", $cng->{"db name"});
define("DB_USER", $cng->{"db user"});
define("DB_PASS", $cng->{"db password"});

require_once("core/db.php");

$mySQL->login();

$host = explode(".", $_SERVER['HTTP_HOST']);
define("HOST", implode(".", array_slice($host, 1)));
define("PROTOCOL", getProtocol());

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);

define("SECTION", empty($params[0]) ? $config->{"default module"} : array_shift($params));

define("DEFAULT_LANG", $config->{"language"});
define(
	"USER_LANG", isset($mySQL->settings['General']['language'])
	? $mySQL->settings['General']['language']
	: DEFAULT_LANG
);

?>