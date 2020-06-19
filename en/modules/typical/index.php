<?php

/* LANGUAGE **********************************************/

include_once("core/wordlist.php");
$wordlist = new Wordlist();

define("LANG_INDEX", (USER_LANG==DEFAULT_LANG) ? "" : "/".USER_LANG);

$languageset = [];
$alternates = $mySQL->getGroup("SELECT PageID,header,language FROM gb_blogfeed WHERE ID={int} AND published & 2", $page['ID']);
foreach($alternates['language'] as $i=>$lang){
	if($lang==DEFAULT_LANG){
		$page['name'] = 
		$languageset[$lang] = translite($alternates['header'][$i])."-".$alternates['PageID'][$i];
	}else $languageset[$lang] = $lang."/".translite($alternates['header'][$i])."-".$alternates['PageID'][$i];
}

$canonical = PROTOCOL."://".$_SERVER['HTTP_HOST']."".LANG_INDEX."/".$page['name'];

$title = $page['header']." - ".$config->{"site name"};
$description = $page['subheader'];

/* bread crumbs ******************************************/

$breadcrumb = [
	"@context"=>"http://schema.org",
	"@type"=>"BreadcrumbList",
	"itemListElement"=>[
		[
			"@type"=>"ListItem",
			"position"=>1,
			"item"=>[
				"@type"=>"WebPage",
				"name"=>$config->{"site name"},
				"id"=>"https://".$_SERVER['HTTP_HOST']
			]
		],[
			"@type"=>"ListItem",
			"position"=>2,
			"item"=>[
				"@type"=>"WebPage",
				"name"=>JSON::parse($page['optionset'])['breadcrumbs'],
				"id"=>$canonical
			]
		]
	]
];


/* Menu **************************************************/

$map = $mySQL->getTree("name","parent", "SELECT PageID,name,parent,header FROM gb_sitemap WHERE published & 2 ORDER BY SortID");

/* FONTS *************************************************/

$fontTypes = [
	"otf"=>"opentype",
	"ttf"=>"truetype",
	"woff"=>"woff"
];

/* Template **********************************************/

define("THEME", $config->{"theme"});
define("TEMPLATE", $page['template']);
//define("SUBTEMPLATE", $page['subtemplate']);

include_once("themes/".THEME."/typical.html");

?>