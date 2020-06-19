<?php

require_once("core/index.php");

$config = new Config();

define("DEFAULT_LANG", $config->language);	// Set default language

$params = preg_split("/\//", mb_strtolower(urldecode($_GET['params']), "utf-8"), -1, PREG_SPLIT_NO_EMPTY);

define("PROTOCOL", getProtocol());

define("ROOT", empty($params[0]) ? false : array_shift($params));
define("MODULE", empty($params[0]) ? false : array_shift($params));
foreach($params as $i=>$itm) define("ARG_".($i+1), $itm);

if(is_dir("ajax/".ROOT)){
	if(MODULE){
		if(file_exists("ajax/".ROOT."/".MODULE.".php")){
			include_once("ajax/".ROOT."/".MODULE.".php");
		}elseif(file_exists("ajax/".ROOT."/index.php")){
			include_once("ajax/".ROOT."/index.php");
		}
	}elseif(file_exists("ajax/".ROOT."/index.php")){
		include_once("ajax/".ROOT."/index.php");
	}
}

?>