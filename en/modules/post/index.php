<?php

$logo = getimagesize($page->logo);

$page->canonical .= LANG_INDEX."/".$page->name;

define("ADS", ($page->Ads=="YES") ? true : false);

$tags = $mySQL->getGroup("SELECT KeyWORD FROM blog_vs_keywords CROSS JOIN gb_keywords USING(KeyID) WHERE PageID={int} ORDER BY rating DESC", PAGE_ID)['KeyWORD'];

/* BREAD CRUMBS ******************************************/

$page->schemes['breadcrumbs']['itemListElement'][] = [
	"@type"=>"ListItem",
	"position"=>2,
	"name"=>$wordlist->{$tags[0]},
	"item"=>$page->root."/".$tags[0]
];
$page->schemes['breadcrumbs']['itemListElement'][] = [
	"@type"=>"ListItem",
	"position"=>3,
	"name"=>$page->header,
	"item"=>$page->canonical
];

$alternates = $mySQL->getGroup("SELECT PageID,header,language FROM gb_blogfeed WHERE ID={int} AND published & 2", $page->ID);
foreach($alternates['language'] as $i=>$lang){
	if($lang==DEFAULT_LANG){
		$page->x_default = 
		$page->languageset[$lang] = $page->canonical."/".translite($alternates['header'][$i])."-".$alternates['PageID'][$i];
	}else $page->languageset[$lang] = $page->canonical."/".$lang."/".translite($alternates['header'][$i])."-".$alternates['PageID'][$i];
}

$keywords = $mySQL->getGroup("SELECT KeyWORD FROM gb_keywords ORDER BY rating DESC LIMIT 32")['KeyWORD'];

include_once("themes/".THEME."/post.html");

?>