<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];

function HexToRgba($hex="#000000", $alpha="1.0"){
	list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
	return  "rgba(".$r.",".$g.",".$b.", ".$alpha.")";
}

if(defined("ARG_2")){
	if(is_numeric(ARG_2)){
		$post = $mySQL->getRow("
		SELECT * FROM gb_pages 
		CROSS JOIN gb_stories USING(PageID)
		CROSS JOIN gb_blogcontent USING(PageID)
		WHERE PageID={int} LIMIT 1", ARG_2);

		define("PAGE_ID", (INT)$post['PageID']);
		$url = BASE_DOMAIN."/".translite($post['header'])."-".$post['PageID'];

		$story = JSON::parse($post['customizer']);

		$animates = [
			"whoosh-in-left",
			"whoosh-in-right",
			"fade-in",
			"fly-in-top",
			"fly-in-bottom",
			"fly-in-right",
			"fly-in-left",
			"pulse",
			"rotate-in-left",
			"rotate-in-right",
			"twirl-in",
			"pan-up",
			"pan-down",
			"pan-left",
			"pan-right",
			"zoom-in",
			"zoom-out",
			"drop"
		];
		$align = [
			"left",
			"right",
			"center",
			"justify"
		];
		$flexes = [
			"flex-start",
			"flex-end",
			"center",
			"space-around",
			"space-between"
		];
		$fonts = [];
	}else{
		define("PAGE_ID", false);
		define("KEYWORD", ARG_3);
	}
}else define("PAGE_ID", false);

$limit = 30;
$cng = new config("../".BASE_FOLDER."/".$config->{"config file"});

$page = defined("ARG_1") ? ARG_1 : 1;
if(defined("KEYWORD")){
	
	$keyid = $mySQL->getRow("SELECT KeyID FROM gb_keywords WHERE KeyWORD LIKE {str} LIMIT 1", KEYWORD)['KeyID'];

	$feed = $mySQL->get("
	SELECT SQL_CALC_FOUND_ROWS * FROM blog_vs_keywords
	CROSS JOIN gb_pages USING(PageID)
	CROSS JOIN gb_stories USING(PageID)
	WHERE KeyID = {int}
	ORDER BY PageID DESC
	LIMIT {int},{int}", $keyid, ($page-1)*$limit, $limit);

}else{
	define("KEYWORD", "");
	$feed = $mySQL->get("
	SELECT SQL_CALC_FOUND_ROWS * FROM gb_stories
	CROSS JOIN gb_pages USING(PageID)
	ORDER BY PageID DESC LIMIT {int}, {int}", ($page-1)*$limit, $limit);
}

$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		
		<style>
		<?$types = ["otf"=>"opentype","ttf"=>"truetype","woff"=>"woff"];
		foreach(array_map("pathinfo", glob("../".BASE_FOLDER."/fonts/*.{otf,ttf,woff}", GLOB_BRACE)) as $file):
			$file['name'] = str_replace("-", " ", $file['filename']);
			$fonts[]=$file?>
    	@font-face{
        	font-family:'<?=$file['name']?>';
        	src:url("//lifter.world/fonts/<?=$file['basename']?>") format('<?=$types[$file['extension']]?>');
    	}
    	<?endforeach?>
    	</style>

    	<style id="caption-color">:root{--caption-color:<?=$story['caption-color']?>}</style>
    	<style id="caption-bg">:root{--caption-bg:<?=HexToRgba($story['caption-bg'], $story['caption-opacity'])?>}</style>
    	<style id="caption-font">:root{--caption-font:<?=$story['caption-font']?>}</style>
    	<style id="caption-size">:root{--caption-size:<?=$story['caption-size']?>px}</style>
    	<style id="text-color">:root{--text-color:<?=$story['text-color']?>}</style>
    	<style id="text-bg">:root{--text-bg:<?=HexToRgba($story['text-bg'], $story['text-opacity'])?>}</style>
    	<style id="text-font">:root{--text-font:<?=$story['text-font']?>}</style>
    	<style id="text-size">:root{--text-size:<?=$story['text-size']?>px}</style>

		<link rel="stylesheet" type="text/css" href="/modules/stories/index.css">
		<script src="/modules/stories/index.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=blogger" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden onchange="STANDBY.screenmode=this.checked">
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label for="rightbar-shower"></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>
					<input id="feed-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div class="tab body-bg light-txt">
						<?if(PAGE_ID):?>
						<div class="h-bar dark-btn-bg">Feed</div>
						<div id="feed">
							<?foreach($feed as $row):?>
							<a class="snippet" href="/stories/<?=$page?>/<?=$row['PageID']?>">
								<div class="preview"><img src="<?=$row['landscape']?>" alt="&#xe906;"></div>
								<div class="header"><?=$row['header']?></div>
								<div class="options">
									<span><?=date("d M, H:i", $row['created'])?></span>
									<span class="<?if($row['published']=="Published"):?>green-txt<?else:?>red-txt<?endif?>"><?=$row['published']?></span>
								</div>
							</a>
							<?endforeach?>
							<div class="h-bar pagination" align="right">
							<?if(($total = ceil($count/$limit))>1):
								if($page>4):
									$j=$page-2?>
									<a>1</a> ... 
								<?else: $j=1; endif;
								for(; $j<$page; $j++):?><a><?=$j?></a><?endfor?>
								<span class="active-txt"><?=$j?></span>
								<?if($j<$total):?>
									<a><?=(++$j)?></a>
									<?if($j<$total):?>
									<?if(($total-$j)>1):?> ... <?endif?>
									<a><?=$total?></a>
									<?endif?>
								<?endif;
							endif?>
							</div>
							<script>
							(function(bar){
								bar.querySelectorAll("div.pagination>a").forEach(function(pg){
									pg.onclick=function(){reloadFeed(LANGUAGE, pg.textContent);}
								});
							})(document.currentScript.parentNode);
							</script>
						</div>
						<?else:?>
						<div class="h-bar white-txt">Keywords</div>
						<a class="light-txt" href="/stories/1"><span class="active-txt">↳</span> All</a>
						<div class="root">
						<?php
						$keywords = $mySQL->getGroup("SELECT KeyWORD FROM gb_keywords ORDER BY rating DESC")['KeyWORD'];
						foreach($keywords as $keyword):?>
							<a href="/stories/1/<?=$keyword?>"><?=$keyword?></a>
						<?endforeach?>
						</div>
						<?endif?>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="feed" class="tool" for="feed-tab" data-translate="title">&#xe902;</label>
					</div>
					<div class="toolbar">
						<label title="navigator" class="tool" data-translate="title" onclick="new Box(null, 'navigator/box')">&#xf07c;</label>
					</div>
					<div class="toolbar">
						<label title="keywords" class="tool" data-translate="title" onclick="new Box(null, 'keywords/box')">&#xe9d3;</label>
						<?if($access['settings']):?>
						<label title="settings" class="tool" data-translate="title" onclick="new Box(null, 'settings/box')">&#xf013;</label>
						<?endif?>
						<?if($access['staff']):?>
						<label title="staff" class="tool" data-translate="title" onclick="new Box(null, 'staff/box')">&#xe972;</label>
						<?endif?>
					</div>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar light-txt">
				<?if(PAGE_ID):?><a class="tool" title="Back" href="/stories/<?=ARG_1?>">❬</a><?endif?>
				<div class="toolbar t">
					<label title="create post" data-translate="title" class="tool" onclick="CreatePost()">&#xe89c;</label>
					<?if(PAGE_ID):?>
					<!--<label title="clone post" data-translate="title" class="tool" onclick="new Box(null, 'blogger/clonebox/<?=$post['PageID']?>')">&#xe925;</label>-->
					<label title="save" data-translate="title" class="tool" onclick="saveStory()">&#xf0c7;</label>
					<button title="remove" form="metadata" data-translate="title" class="tool transparent-bg light-txt" type="reset">&#xe94d;</button>
					<?endif?>
				</div>
				<hr class="separator">
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<?if(PAGE_ID):?>
				<hr class="separator right">
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<?endif?>
			</header>
			<main class="light-txt">
			<?if(PAGE_ID):?>
				<div id="slider">
					<?$set = JSON::parse(gzdecode($post['content']));
					foreach($set as $img):?>
					<form autocomplete="off">
						<?if($img['type']=="img"):?>
						<img src="<?=$img['src']?>">
						<?elseif($img['type']=="video"):?>
						<video src="<?=$img['src']?>" controls></video>
						<?endif?>
						<fieldset>
							<div>
								<div class="select">
									<select name="animate" class="white-txt">
										<?foreach($animates as $animate):?>
										<option <?if($animate==$img['animate']):?>selected<?endif?> value="<?=$animate?>"><?=$animate?></option>
										<?endforeach?>
									</select>
								</div>
								<div class="select">
									<select name="align" class="white-txt">
										<?foreach($align as $mode):?>
										<option <?if($mode==$img['align']):?>selected<?endif?> value="<?=$mode?>"><?=$mode?></option>
										<?endforeach?>
									</select>
								</div>
								<div class="select right">
									<select name="flex" class="white-txt">
										<?foreach($flexes as $flex):?>
										<option <?if($flex==$img['flex']):?>selected<?endif?> value="<?=$flex?>"><?=$flex?></option>
										<?endforeach?>
									</select>
								</div>
							</div>
							<section class="<?=$img['flex']?>">
								<textarea class="<?=$img['align']?>" placeholder="..." name="header"><?=$img['header']?></textarea>
								<textarea class="<?=$img['align']?>" placeholder="..." name="text"><?=$img['text']?></textarea>
							</section>
						</fieldset>
						<script>
						(function(form){
							form.align.onchange=function(){
								form.text.style.textAlign = 
								form.header.style.textAlign = form.align.value;
							}
							form.flex.onchange=function(){
								form.querySelector("section").className = form.flex.value;
							}
						})(document.currentScript.parentNode);
						</script>
					</form>
					<?endforeach?>	
					<script>
					var SLIDER = document.currentScript.parentNode
					</script>
				</div>
				<section>
					<?foreach($set as $img):?>
					<div class="card">
						<label class="drop-card">✕</label>
						<div class="preview">
							<?if($img['type']=="img"):?>
							<img src="<?=$img['src']?>">
							<?elseif($img['type']=="video"):?>
							<video src="<?=$img['src']?>" preload="metadata"></video>
							<?endif?>
						</div>
					</div>
					<?endforeach?>
					<script>
					var timeout,
						MEDIASET = document.currentScript.parentNode;
					MEDIASET.refreshSlideshow = function(){
						MEDIASET.querySelectorAll(".card").forEach(function(img,i){
							img.onclick=function(){
								SLIDER.shotSlide(i*SLIDER.offsetWidth);
							}
						});
						MEDIASET.querySelectorAll("label").forEach(function(label,i){
							label.onclick=function(){
								SLIDER.removeChild( SLIDER.querySelectorAll("form")[i] );
								MEDIASET.removeChild( label.parentNode);
								MEDIASET.refreshSlideshow();
							}
						});
					}
					MEDIASET.refreshSlideshow();
					</script>
				</section>
				<form id="main-form" title="1" autocomplete="off">
					<button name="previous" id="left-btn" data-dir="-1">❰</button>
					<button name="next" id="right-btn" data-dir="1">❱</button>
					<button name="add" id="add-btn">add</button>
					<div id="stury-options" class="b">
						<fieldset><legend>CAPTION</legend>
							<label class="tool" title="Caption Color"><input name="caption-txt" type="color" value="<?=$story['caption-color']?>"></label>
							<label class="tool" title="Caption Background"><input name="caption-bg" type="color"  value="rgba(255,0,0,1)"></label>
							<div class="right">
								Opacity:
								<input type="number" name="caption-opacity" value="<?=$story['caption-opacity']?>" min="0.0" max="1.0" step="0.1">
							</div>
							<br>
							<div class="select">
								<select name="caption-font" class="active-txt">
									<?foreach($fonts as $font):?>
									<option <?if($font['name']==$story['caption-font']):?>selected<?endif?> value="<?=$font['name']?>"><?=$font['name']?></option>
									<?endforeach?>
								</select>
							</div>
							Font Size:
							<input type="number" name="caption-size" value="<?=$story['caption-size']?>" min="10" max="64" step="1">
						</fieldset>
						<fieldset><legend>TEXT</legend>
							<label class="tool" title="Text Color"><input name="text-color" type="color" value="<?=$story['text-color']?>"></label>
							<label class="tool" title="Text Background"><input name="text-bg" type="color" value="<?=$story['text-bg']?>"></label>
							<div class="right">
								Opacity:
								<input type="number" name="text-opacity" value="<?=$story['text-opacity']?>" min="0.0" max="1.0" step="0.1">
							</div>
							<br>
							<div class="select">
								<select name="text-font" class="active-txt">
									<?foreach($fonts as $font):?>
									<option <?if($font['name']==$story['text-font']):?>selected<?endif?> value="<?=$font['name']?>"><?=$font['name']?></option>
									<?endforeach?>
								</select>
							</div>
							Font Size:
							<input type="number" name="text-size" value="<?=$story['text-size']?>" min="10" max="42" step="1">
						</fieldset>
					</div>
					<button id="swap" name="swap">Swap ❱</button>
					<template id="slide-tpl">
						<fieldset>
							<div>
								<div class="select">
									<select name="animate" class="white-txt">
										<?foreach($animates as $animate):?>
										<option value="<?=$animate?>"><?=$animate?></option>
										<?endforeach?>
									</select>
								</div>
								<div class="select">
									<select name="align" class="white-txt">
										<?foreach($align as $mode):?>
										<option value="<?=$mode?>"><?=$mode?></option>
										<?endforeach?>
									</select>
								</div>
								<div class="select right">
									<select name="flex" class="white-txt">
										<?foreach($flexes as $flex):?>
										<option value="<?=$flex?>"><?=$flex?></option>
										<?endforeach?>
									</select>
								</div>
							</div>
							<section>
								<textarea placeholder="..." name="header"><?=$img['header']?></textarea>
								<textarea placeholder="..." name="text"><?=$img['text']?></textarea>
							</section>
						</fieldset>
						<script>
						(function(form){
							form.align.onchange=function(){
								form.text.style.textAlign = 
								form.header.style.textAlign = form.align.value;
							}
							form.flex.onchange=function(){
								form.querySelector("section").className = form.flex.value;
							}
						})(document.currentScript.parentNode);
						</script>
					</template>
					<script>
					(function(form){
						var animate;
						form.onsubmit=function(event){
							event.preventDefault();
						}
						form.next.onclick=
						form.previous.onclick=function(event){
							event.preventDefault();
							let dir = parseInt(event.target.dataset.dir),
							offset = SLIDER.offsetWidth*(dir+(SLIDER.scrollLeft/SLIDER.offsetWidth)>>0);

							if((offset<0) || offset>(SLIDER.scrollWidth-SLIDER.offsetWidth)) return false;
							SLIDER.shotSlide(offset);
						}
						SLIDER.shotSlide = function(offset){
							cancelAnimationFrame(animate);
							animate = requestAnimationFrame(function scrollSlide(){
								if(Math.abs(offset - SLIDER.scrollLeft) > 16){
									SLIDER.scrollLeft += (offset - SLIDER.scrollLeft)/8;
									animate = requestAnimationFrame(scrollSlide);
								}else{
									SLIDER.scrollLeft = offset;
									form.title = 1+(offset/SLIDER.offsetWidth)>>0;
								}
							});
						}
						form.add.onclick=function(event){
							event.preventDefault();
							new Box('{}', "mediaset/navigatorbox",function(box){
								box.querySelector(".box-body>iframe").contentWindow.getSelected().forEach(function(img){

									let frm = doc.create("form",{autocomplete:"off"}, form.querySelector("#slide-tpl").cloneNode(true).content);
										switch(img.type){
											case "image":
												var slide = doc.create("img",{src:img.url,alt:""});
												frm.appendChild(slide.cloneNode(true));
											break;
											case "video":
												frm.appendChild( doc.create("video",{src:img.url,controls:"true"}) );
												var slide = doc.create("video",{src:img.url});
											break;
											default:break;
										}
									SLIDER.appendChild(frm);
									let card = doc.create("div", {class:"card"}, "<label class='drop-card'>✕</label>");
									let preview = doc.create("div", {class:"preview"});
										preview.appendChild( slide );
										card.appendChild( preview );
									MEDIASET.appendChild(card);

								});
								MEDIASET.refreshSlideshow();
								box.drop();
							});
						}
						form['caption-opacity'].oninput=
						form['caption-bg'].onchange=function(){
							var rgba = hexToRgba(form['caption-bg'].value, form['caption-opacity'].value);
							document.querySelector("#caption-bg").textContent = ":root{--caption-bg:"+rgba+"}";
						}
						form['caption-txt'].onchange=function(){
							document.querySelector("#caption-color").textContent = ":root{--caption-color:"+form['caption-txt'].value+"}";
						}
						form['caption-font'].onchange=function(){
							document.querySelector("#caption-font").textContent = ":root{--caption-font:"+form['caption-font'].value+"}";
						}
						form['caption-size'].oninput=function(){
							document.querySelector("#caption-size").textContent = ":root{--caption-size:"+form['caption-size'].value+"px}";
						}
						form['text-opacity'].oninput=
						form['text-bg'].onchange=function(){
							var rgba = hexToRgba(form['text-bg'].value, form['text-opacity'].value);
							document.querySelector("#text-bg").textContent = ":root{--text-bg:"+rgba+"}";
						}
						form['text-color'].onchange=function(){
							document.querySelector("#text-color").textContent = ":root{--text-color:"+form['text-color'].value+"}";
						}
						form['text-font'].onchange=function(){
							document.querySelector("#text-font").textContent = ":root{--text-font:"+form['text-font'].value+"}";
						}
						form['text-size'].oninput=function(){
							document.querySelector("#text-size").textContent = ":root{--text-size:"+form['text-size'].value+"px}";
						}
					})(document.currentScript.parentNode)
					</script>
				</form>
				<?else:?>
				<!--~~~~~~~-->
				<div id="feed">
					<?foreach($feed as $row):?>
					<a class="snippet" href="/stories/<?=$page?>/<?=$row['PageID']?>">
						<div class="preview"><img src="<?=$row['landscape']?>" alt="&#xe906;"></div>
						<div class="header"><?=$row['header']?></div>
						<div class="options">
							<span><?=date("d M, H:i", $row['created'])?></span>
							<span class="<?if($row['published']=="Published"):?>green-txt<?else:?>red-txt<?endif?>"><?=$row['published']?></span>
						</div>
					</a>
					<?endforeach?>
				</div>
				<div class="h-bar pagination white-txt">
				<?if(($total = ceil($count/$limit))>1):
					$root = "/stories";
					if($page>4):
						$j=$page-2?>
						<a href="<?=$root?>/1/<?=KEYWORD?>">1</a> ... 
					<?else: $j=1; endif;
					for(; $j<$page; $j++):?><a href="<?=($root."/".$j."/".KEYWORD)?>"><?=$j?></a><?endfor?>
					<span class="active-txt"><?=$j?></span>
					<?if($j<$total):?>
						<a href="<?=($root."/".(++$j)."/".KEYWORD)?>"><?=$j?></a>
						<?if($j<$total):?>
						<?if(($total-$j)>1):?> ... <?endif?>
						<a href="<?=($total."/".KEYWORD)?>"><?=$total?></a>
						<?endif?>
					<?endif;
				endif?>
				</div>
				<!--~~~~~~~-->
				<?endif?>
			</main>
			<?if(PAGE_ID):?>
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="metadata" class="tab body-bg light-txt" autocomplete="off">
						<div class="h-bar l light-btn-bg">
							<!--~~~~~~~-->
							<!--~~~~~~~-->
							<input name="PageID" value="<?=$post['PageID']?>" type="hidden">
							<small>ID: <output class="active-txt"><?=$post['PageID']?></output></small>
							<div class="right">
								<small><span data-translate="textContent">created</span>:</small>
								<input type="date" name="date" value="<?=date("Y-m-d",$post['created'])?>">
								<input type="time" name="time" value="<?=date("H:i", $post['created'])?>">
							</div>
						</div>
						<!-- COVERS -->
						<input id="landscape-cover-tab" type="radio" name="cover-tab" hidden checked>
						<label for="landscape-cover-tab">Landscape</label>
						<input id="portrait-cover-tab" type="radio" name="cover-tab" hidden>
						<label for="portrait-cover-tab">Portrait</label>
						<div class="black-bg" align="right">
							<div class="select" title="template" data-translate="title">
								<select name="template" class="active-txt"> 
									<?foreach(glob("../".BASE_FOLDER."/themes/".$cng->theme."/includes/stories/*.css") as $file):$file = pathinfo($file)['filename']?>
									<option <?if($file==$post['subtemplate']):?>selected<?endif?> value="<?=$file?>"><?=$file?></option>
									<?endforeach?>
								</select>
							</div>
						</div>
						<div id="cover">
							<iframe frameborder="no"></iframe>
							<script>
							(function(script){
								var frame =  script.previousElementSibling;
								var	navigator = frame.contentWindow, options = [];
								navigator.standby = (window.localStorage['navigator'] || "undefined").jsonToObj() || {};

								if(navigator.standby.subdomain) options.push("subdomain="+navigator.standby.subdomain);
								if(navigator.standby[navigator.standby.subdomain]) options.push("path="+navigator.standby[navigator.standby.subdomain]);

								window.addEventListener("load",function(){
									reauth();
									navigator.location.href="/navigator/folder/image/radio?"+options.join("&");
									frame.onload=function(){
										navigator.onchange=function(event){
											if(event.target.name=="files-on-folder"){
												script.nextElementSibling.src=event.target.value;
											}
										}
									}
								});
							})(document.currentScript)
							</script>
							<img src="<?=$post['landscape']?>" alt="&#xe906;">
						</div>
						<div id="portrait-cover">
							<iframe frameborder="no"></iframe>
							<script>
							(function(script){
								var frame =  script.previousElementSibling;
								var	navigator = frame.contentWindow, options = [];
								navigator.standby = (window.localStorage['navigator'] || "undefined").jsonToObj() || {};

								if(navigator.standby.subdomain) options.push("subdomain="+navigator.standby.subdomain);
								if(navigator.standby[navigator.standby.subdomain]) options.push("path="+navigator.standby[navigator.standby.subdomain]);

								window.addEventListener("load",function(){
									reauth();
									navigator.location.href="/navigator/folder/image/radio?"+options.join("&");
									frame.onload=function(){
										navigator.onchange=function(event){
											if(event.target.name=="files-on-folder"){
												script.nextElementSibling.src=event.target.value;
											}
										}
									}
								});
							})(document.currentScript)
							</script>
							<img src="<?=$post['portrait']?>" alt="&#xe906;">
						</div>
						<!-- OPTIONS -->
						<div id="options" class="right">
							<label><input name="published" <?if($post['published']=="Published"):?>checked<?endif?> type="checkbox" hidden><span>Published</span></label>
							<label><input disabled name="ads" type="checkbox" <?if(($post['Ads']=="YES")):?>checked<?endif?> hidden><span data-translate="textContent">ads</span></label>
						</div>
						<!-- TITLE -->
						<fieldset id="title" class="r"><legend data-translate="textContent">header</legend>
							<label id="get-url" title="get post url" data-translate="title" class="tool">
								<span></span>
								<input onfocus="copyURL(this)" value="<?=$url?>">
							</label>
							<textarea ondblclick="this.select()" name="header" placeholder="..."><?=$post['header']?></textarea>
						</fieldset>
						<!-- DESCRIPTION -->
						<fieldset><legend data-translate="textContent">subheader</legend>
							<textarea name="subheader" placeholder="..."><?=$post['subheader']?></textarea>
						</fieldset>
						<!-- KEYWORDS -->
						<div class="h-bar dark-btn-bg">
							Keywords
							<div class="select right" title="author" data-translate="title">
								<select name="author" class="active-txt">
								<?foreach($mySQL->get("SELECT UserID, Name FROM gb_staff LEFT JOIN gb_community USING(CommunityID)") as $author):?>
									<option value="<?=$author['UserID']?>" <?if($author['UserID']==$post['UserID']):?>selected<?endif?>><?=$author['Name']?></option>
								<?endforeach?>
								</select>
							</div>
						</div>
						<div id="keywords" class="logo-bg">
							<?php
							$tags = $mySQL->getGroup("SELECT KeyWORD FROM gb_keywords ORDER BY rating DESC LIMIT 32")['KeyWORD'];
							foreach($tags as $cell):?>
							<span><?=$cell?></span>
							<?endforeach;
							$tags = $mySQL->getGroup("SELECT KeyWORD FROM blog_vs_keywords CROSS JOIN gb_keywords USING(KeyID) WHERE PageID = {int}", $post['PageID'])['KeyWORD'];?>
							<textarea name="keywords" placeholder="..."><?=implode(", ", $tags)?></textarea>
						</div>
						<script>
						(function(form){
							form.onreset=function(event){
								event.preventDefault();
								confirmBox("remove post", function(){
									XHR.push({
										addressee:"/stories/actions/remove/<?=$post['PageID']?>",
										onsuccess:function(response){
											var path = location.pathname.split(/\//);
											path.pop();
											location.pathname = path.join("/");
										}
									});
								},["logo-bg"]);
							}
							form.querySelectorAll("#keywords>span").forEach(function(word){
								word.onclick=function(){
									var tags = form.keywords.value.trim().split(/,+\s*/g);
									if(isNaN( tags.inArray(word.textContent) )){
										tags.push(word.textContent);
										form.keywords.value = join(", ", tags.filter(function(key){ return key.length }));
									}
								}
							});
						})(document.currentScript.parentNode);
						</script>
					</form>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Metadata" class="tool" for="right-default" data-translate="title">&#xe871;</label>
					<label title="customizer" class="tool" data-translate="title" onclick="new Box(null,'customizer/box/<?=PAGE_ID?>')">&#xe993;</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
			<?endif?>
		</div>
		<script>
		<?if(PAGE_ID):?>
		(function(body){
			body.querySelector("#screenmode").checked = (STANDBY.screenmode=="true");
		})(document.currentScript.parentNode);
		<?endif?>
		
		/**************************/

		var remove = function(){
			<?if(defined("ARG_3")):?>
			window.parent.confirmBox("Delete mediaset?", function(){
				XHR.push({
					addressee:"/mediaset/actions/remove-mediaset/<?=ARG_3?>",
					onsuccess:function(response){
						if(isNaN(response)){
							alert(response);
						}else location.pathname = location.pathname.split(/\//).slice(0,-1).join("/")
					}
				});
			});
			<?else:?>
			window.parent.alertBox("Mediaset is not selected",["logo-bg","h-bar"]);
			<?endif?>
		}
		var saveStory = function(showAlert){
			var MainForm = doc.querySelector("form#main-form");
			var MetaForm = doc.querySelector("form#metadata");
			
			var TimeOffset = new Date().getTimezoneOffset()*60000;
			var box = new Box('{}', "boxfather/savelogbox/modal", function(){	box.drop(); });
			box.onopen = function(){
				XHR.push({
					addressee:"/stories/actions/save",
					body:JSON.encode({
						"PageID":MetaForm.PageID.value,
						"Ads":MetaForm.ads.checked ? "YES" : "NO",
						"published":MetaForm.published.checked ? "Published" : "Not published",
						"landscape":MetaForm.querySelector("#cover>img").currentSrc || "",
						"portrait":MetaForm.querySelector("#portrait-cover>img").currentSrc || "",
						"header":utf8_to_b64( MetaForm.header.value.trim().replace(/"/g,"″") ),
						"subheader":utf8_to_b64( MetaForm.subheader.value.trim().replace(/"/g,"″") ),
						"UserID":MetaForm.author.value,
						"created":(((MetaForm.date.valueAsNumber+MetaForm.time.valueAsNumber)+TimeOffset)/1000),
						"subtemplate":MetaForm.template.value,
						"keywords":(function(keywords){
							let words = [];
							keywords.split(/,+\s*/g).filter(function(key){ return key.length }).forEach(function(itm){
								words.push(itm.translite());
							});
							return words;
						})(MetaForm.keywords.value),
						"story":{
							"caption-color":MainForm['caption-txt'].value,
							"caption-bg":MainForm['caption-bg'].value,
							"caption-opacity":MainForm['caption-opacity'].value,
							"caption-font":MainForm['caption-font'].value,
							"caption-size":MainForm['caption-size'].value,
							"text-color":MainForm['text-color'].value,
							"text-bg":MainForm['text-bg'].value,
							"text-opacity":MainForm['text-opacity'].value,
							"text-font":MainForm['text-font'].value,
							"text-size":MainForm['text-size'].value
						},
						"mediaset":(function(mediaset){
							SLIDER.querySelectorAll("form").forEach(function(form,i){
								let obj = form.querySelector("img,video");
								mediaset.push({
									"src":obj.src,
									"type":obj.nodeName.toLowerCase(),
									"header":utf8_to_b64( form.header.value.trim().replace(/"/g,"”").replace("/'/g","’") ),
									"text":utf8_to_b64( form.text.value.trim().replace(/"/g,"”").replace("/'/g","’") ),
									"animate":form.animate.value,
									"align":form.align.value,
									"flex":form.flex.value
								});
							});
							return mediaset;
						})([])
					}),
					onsuccess:function(response){
						var answer = JSON.parse(response);
						for(var key in answer.log){
							box.body.appendChild(doc.create("div", {}, "<tt><b>"+key+"</b>: "+answer.log[key]+"</tt>"));
						}
						box.align();
						if(answer.log['PageID']){
							box.body.appendChild(doc.create("input",{
								value:answer.url,
								readonly:"true",
								onfocus:"copyURL(this)"
							}));
						}
					}
				});	
			}
		}
		</script>
	</body>
</html>