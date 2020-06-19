<?php

if(preg_match("/[A-Z]/", $_GET['params'])) header("Location: ".mb_strtolower($_GET['params'], "utf-8"), true, 301);

/***************************************  GOOLYBEEP ULTIMATE ************************************/

require_once("core/index.php");

$host = explode(".", $_SERVER['HTTP_HOST']);
define("PROTOCOL", getProtocol());
define("SUBDOMAIN", reset($host));
define("HOST", implode(".", array_slice($host, 1)));

$config = new Config();

/***************** Device detect *****************/

if(preg_match("/(android|phone|ipad|tablet|blackberry|bb10|symbian|series|samsung|webos|mobile|opera m|htc|fennec|windowsphone|wp7|wp8)/i",$_SERVER['HTTP_USER_AGENT'])){
	define("MOBILE", true);
	define("DESKTOP", false);
	define("DEVICE", "mobile");
}else{
	define("MOBILE", false);
	define("DESKTOP", true);
	define("DEVICE", "desktop");
}

/*************************************/

define("THEME", $config->{"theme"});

define("DEFAULT_LANG", $config->language);
define("DEFAULT_LOCAL", $config->locality);

/************* Parse URL *************/

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);

/* LANGUAGES DEFINE ******************/

if(in_array($params[0], $config->languageset)){
	define("USER_LANG", $params[0]);
	$params = array_slice($params, 1);
	define("LANG_INDEX", "/".USER_LANG);
}else{
	define("USER_LANG", DEFAULT_LANG);
	define("LANG_INDEX", "");
}

define("USER_LOCAL", $config->{USER_LANG});

$mask = 0;
foreach($config->languageset as $key=>$val){
	if($val!=USER_LANG) $mask |= pow(2, $key);
}
define("LANG_MASK", $mask);

define("ROOT", empty($params[0]) ? $config->{"home page"} : array_shift($params));
foreach($params as $i=>$itm) define("ARG_".($i+1), $itm);

setcookie("AMP_EXP", "amp-story", time()+604800, "/");

/***********************************/

require_once("core/db.php");

$map = $mySQL->getTree("name","parent", "SELECT PageID,name,parent,header FROM gb_sitemap WHERE published & 2 ORDER BY SortID");

include_once("core/wordlist.php");
$wordlist = new Wordlist();

$page = new Data;

$amp = $mySQL->getRow("SELECT PageID,content FROM gb_pages CROSS JOIN gb_amp USING(PageID) WHERE PageID={int}", $page->PageID);

if((BOOL)$amp['PageID']){
	$page->content = $amp['content'];
	define("ADS", ($page->ads=="YES"));
}else{
	header('HTTP/1.0 404 Not Found');
	$page->data = $mySQL->getMaterial("404", ["*"]);
}

include_once("themes/".THEME."/amp.html");

/*********************/

$mySQL->close();


?>