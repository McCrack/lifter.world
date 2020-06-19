<?php

require_once "defines.php";

if(file_exists("modules/".SECTION."/config.init")){
	$conf = JSON::load("modules/".SECTION."/config.init");
	$groups = preg_split("/,\s*/", $conf['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	if( in_array(USER_GROUP, $groups) || ($conf['free access']['value']=="YES") ){
		
		$path = "modules/".SECTION;

		foreach($params as $itm) if(file_exists($path."/".$itm)) $path .= "/".array_shift($params); else break;
		foreach($params as $i=>$itm) define("ARG_".($i+1), $itm);

		if(file_exists($path."/".ARG_1.".php")){
			
			include_once $path."/".ARG_1.".php";

		}elseif(file_exists($path."/index.php")){
			
			include_once $path."/index.php";

		}elseif(file_exists("modules/".SECTION."/index.php")){

			include_once "modules/".SECTION."/index.php";

		}else header("Location: /".$config->{"default module"});

	}else die(include_once "login.php" );

}else header("Location: /".$config->{"default module"});

$mySQL->close();

?>