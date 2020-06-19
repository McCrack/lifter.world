<?php

$alternates = $mySQL->getGroup("SELECT language,name FROM gb_sitemap WHERE name LIKE {str} AND published & 2", $page->name);
foreach($alternates['language'] as $i=>$lang) if($lang==DEFAULT_LANG){
	$page->x_default = 
	$page->languageset[$lang] = $page->canonical;
}else $page->languageset[$lang] = $page->canonical."/".$lang;

define("NOW", time());
define("YESTERDAY", NOW-86400);

/* bread crumbs ******************************************/

$keywords = $mySQL->getGroup("SELECT KeyWORD FROM gb_keywords ORDER BY rating DESC LIMIT 32")['KeyWORD'];

/* FEED **************************************************/

$page->canonical .= LANG_INDEX;

if(defined("KEY_ID")){

	$page->canonical .= "/".ROOT;

	$page->header = $wordlist->{ROOT};
	$page->title = $page->header." - ".$page->SiteName;

	define("ADS", (BOOL)(ROOT!="sex"));

	$page->schemes['breadcrumbs']['itemListElement'][] = [
		"@type"=>"ListItem",
		"position"=>2,
		"name"=>$page->header,
		"item"=>$page->canonical
	];

	$feed = $mySQL->get("
	SELECT * FROM blog_vs_keywords
	CROSS JOIN gb_blogfeed USING(PageID) 
	CROSS JOIN gb_pages USING(PageID) 
	WHERE
		KeyID = {int} AND
		published & 2 AND
		created<{int}
	ORDER BY created DESC", KEY_ID,time());

}else{
	$page->title = $page->SiteName;
	define("ADS", true);

	$feed = $mySQL->get("
	SELECT * FROM gb_blogfeed
	CROSS JOIN gb_pages USING(PageID) 
	WHERE
		published & 2
	ORDER BY created DESC LIMIT 18");

	/*
	$feed = $mySQL->get("
	SELECT * FROM gb_blogfeed
	CROSS JOIN gb_pages USING(PageID) 
	WHERE
		language LIKE {str}
		AND published & 2
		AND (created BETWEEN {int} AND {int})
	ORDER BY created DESC", USER_LANG,YESTERDAY,NOW);
	*/
}

foreach($feed as $i=>&$item){
	$item['entity'] = translite($item['header'])."-".$item['PageID'];
	$page->schemes['carousel']['itemListElement'][] = [
		"@type"=>"ListItem",
		"position"=>($i+1),
		"url"=>$page->root."".LANG_INDEX."/".$item['entity']
	];
}

/* Template **********************************************/

include_once("themes/".THEME."/feed.html");

?>