<?php

if(preg_match("/[A-Z]/", $_GET['params'])) header("Location: ".mb_strtolower($_GET['params'], "utf-8"));

require_once("core/index.php");

$host = explode(".", $_SERVER['HTTP_HOST']);
define("PROTOCOL", getProtocol());
define("SUBDOMAIN", reset($host));
define("HOST", implode(".", array_slice($host, 1)));

$config = new Config();

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);

/* LANGUAGES DEFINE ******************/

/*
define("DEFAULT_LANG", $config->language);
define("DEFAULT_LOCAL", $config->locality);


$mask = 0;
if(in_array($params[0], $config->languageset)){
	define("USER_LANG", $params[0]);
	$params = array_slice($params, 1);
}else define("USER_LANG", DEFAULT_LANG);
foreach($config->languageset as $key=>$val){
	if($val!=USER_LANG) $mask |= pow(2, $key);
}
define("LANG_MASK", $mask);
*/

require_once("core/db.php");

$feed = $mySQL->get("
SELECT 
	PageID, gb_ina.content,
	created,modified,
	header,subheader,preview,
	gb_community.Name
FROM gb_ina 
CROSS JOIN gb_pages USING(PageID) 
CROSS JOIN gb_blogfeed USING(PageID)
CROSS JOIN gb_blogcontent USING(PageID)
CROSS JOIN gb_staff USING(UserID)
CROSS JOIN gb_community USING(CommunityID)
WHERE published & 2 AND created < {int} 
ORDER BY created DESC 
LIMIT 100", time());

$mySQL->close();

header("Content-type: text/xml; charset=utf-8");

print "<?xml version='1.0' encoding='utf-8'?>"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
	<title>LIFTER WORLD</title>
	<link>https://lifter.world/</link>
	<description>Lifter - проект, который позволяет вам рассказывать друг другу в facebook интересные истории и давать интересные советы.</description>
	<language>ru_RU</language>
	<generator>Goolybeep</generator>
	<?foreach($feed as $item): $canonical = PROTOCOL."://".HOST."/".translite($item['header'])."-".$item['PageID']?>
		<item>
			<title><?=$item['header']?></title>
			<link><?=$canonical?></link>
			<guid><?=$canonical?></guid>
			<pubDate><?=date("c", $item['created'])?></pubDate>
			<author><?=$item['Name']?></author>
			<description><?=$item['subheader']?></description>
			<content:encoded>
			<![CDATA[
				<!DOCTYPE html>
				<html lang="ru_RU" prefix="op: http://media.facebook.com/op#">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					<meta property="op:markup_version" content="v1.0">
					<meta property="fb:article_style" content="default">
					<meta property="fb:use_automatic_ad_placement" content="true">
					<link rel="canonical" href="<?=$canonical?>">
				</head>
				<body>
					<article>
						<figure class='op-tracker'>
							<iframe>
								<script type='text/javascript'>
								(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
								ga('create', 'UA-51627207-1', 'auto');
								ga('send', 'pageview');
								</script>
							</iframe>
						</figure>
						<header>
							<figure class='op-ad'>
								<iframe width='300' height='250' style='border:0; margin:0;' src='https://www.facebook.com/adnw_request?placement=1598651023780649_1635796960066055&adtype=banner300x250'></iframe>
							</figure>
							<figure>
								<?if(empty($item['video'])):?>
								<img src="<?=$item['preview']?>">
								<?else: $uri = parse_url($item['video'])?>
								<video>
									<source src="<?=$item['video']?>" type="<?=mime_content_type("../".array_shift(explode(".",$uri['host']))."".$uri['path'])?>"/>
								</video>
								<?endif?>
							</figure>
							<h1><?=$item['header']?></h1>
							<h2><?=$item['subheader']?></h2>
							<time class="op-published" dateTime="<?=date("c", $item['created'])?>"><?=date("d F, H:i",$item['created'])?></time>
							<time class="op-modified" dateTime="<?=date("c", $item['modified'])?>"><?=date("d F, H:i", $item['modified'])?></time>
							<address><?=$item['Name']?></address>
						</header>
						<?=gzdecode($item['content'])?>
					</article>
				</body>
				</html>
			]]>
			</content:encoded>
		</item>
	<?endforeach?>
</channel>
</rss>