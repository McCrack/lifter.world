<?php

/* LANGUAGE **********************************************/

include_once("core/wordlist.php");
$wordlist = new Wordlist();

define("LANG_INDEX", (USER_LANG==DEFAULT_LANG) ? "" : "/".USER_LANG);

$canonical = PROTOCOL."://".$_SERVER['HTTP_HOST']."/".$page->name;

$title = $page->header." - ".$config->{"site name"};
$description = $page->subheader;

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
				"name"=>$page->header,
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
define("TEMPLATE", $page->template);
//define("SUBTEMPLATE", $page['subtemplate']);

include_once("themes/".THEME."/typical.html");

?>