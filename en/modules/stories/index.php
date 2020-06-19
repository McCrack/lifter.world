<?php

$logo = getimagesize($page->logo);

$page->canonical .= LANG_INDEX."/".$page->name;

//$tags = $mySQL->getGroup("SELECT KeyWORD FROM blog_vs_keywords CROSS JOIN gb_keywords USING(KeyID) WHERE PageID={int} ORDER BY rating DESC", PAGE_ID)['KeyWORD'];

/* BREAD CRUMBS ******************************************/

$page->schemes['breadcrumbs']['itemListElement'][] = [
	"@type"=>"ListItem",
	"position"=>2,
	"name"=>$wordlist->stories,
	"item"=>$page->root."/stories"
];
$page->schemes['breadcrumbs']['itemListElement'][] = [
	"@type"=>"ListItem",
	"position"=>3,
	"name"=>$page->header,
	"item"=>$page->canonical
];
/*
$alternates = $mySQL->getGroup("SELECT PageID,header,language FROM gb_blogfeed WHERE ID={int} AND published & 2", $page->ID);
foreach($alternates['language'] as $i=>$lang){
	if($lang==DEFAULT_LANG){
		$page->x_default = 
		$page->languageset[$lang] = $page->canonical."/".translite($alternates['header'][$i])."-".$alternates['PageID'][$i];
	}else $page->languageset[$lang] = $page->canonical."/".$lang."/".translite($alternates['header'][$i])."-".$alternates['PageID'][$i];
}
*/
//$keywords = $mySQL->getGroup("SELECT KeyWORD FROM gb_keywords ORDER BY rating DESC LIMIT 32")['KeyWORD'];

$content = $page->getContent();
$story = JSON::parse($content);

$customize = JSON::parse($page->customizer);

function HexToRgba($hex="#000000", $alpha="1.0"){
	list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
	return  "rgba(".$r.",".$g.",".$b.", ".$alpha.")";
}

include_once("themes/".THEME."/story.html");

?>