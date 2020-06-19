<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];

if(defined("ARG_3")){
	if(is_numeric(ARG_3)){
		$post = $mySQL->getRow("
		SELECT
		PageID,ID,SetID,language,published,header,subheader,preview,category,created,video,Ads,
		subtemplate,UserID,gb_blogcontent.content AS content,
		gb_amp.PageID AS amp,
		gb_ina.PageID AS ina
		FROM gb_pages 
		CROSS JOIN gb_blogfeed USING(PageID)
		CROSS JOIN gb_blogcontent USING(PageID)
		LEFT JOIN gb_amp USING(PageID) 
		LEFT JOIN gb_ina USING(PageID) 
		WHERE PageID={int} LIMIT 1", ARG_3);

		define("PAGE_ID", (INT)$post['PageID']);

		if($post['language']==$cng->language){
			$url = BASE_DOMAIN."/".translite($post['header'])."-".$post['PageID'];
		}else $url = BASE_DOMAIN."/".$post['language']."/".translite($post['header'])."-".$post['PageID'];

	}else{
		define("PAGE_ID", false);
		define("KEYWORD", ARG_3);
	}
}else define("PAGE_ID", false);

$limit = 30;
$cng = new config("../".BASE_FOLDER."/".$config->{"config file"});
$language = defined("ARG_1") ? ARG_1 : $cng->language;
$page = defined("ARG_2") ? ARG_2 : 1;
if(defined("KEYWORD")){
	
	$keyid = $mySQL->getRow("SELECT KeyID FROM gb_keywords WHERE KeyWORD LIKE {str} LIMIT 1", KEYWORD)['KeyID'];

	$feed = $mySQL->get("
	SELECT SQL_CALC_FOUND_ROWS * FROM blog_vs_keywords CROSS JOIN gb_pages USING(PageID)
	CROSS JOIN gb_blogfeed USING(PageID)
	WHERE KeyID = {int} AND language LIKE {str}
	ORDER BY ID DESC
	LIMIT {int},{int}", $keyid, $language, ($page-1)*$limit, $limit);

}else{
	define("KEYWORD", "");
	$feed = $mySQL->get("SELECT SQL_CALC_FOUND_ROWS * FROM gb_blogfeed LEFT JOIN gb_pages USING(PageID) WHERE language LIKE {str} ORDER BY ID DESC LIMIT {int}, {int}", $language, ($page-1)*$limit, $limit);
}

$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/blogger/index.css">
		<script src="/modules/blogger/index.js?1"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=blogger" defer charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body>
		<input id="mediaset-shower" type="checkbox" hidden>
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
						<div class="h-bar white-txt">
							Feed
							<div class="select right">
								<select class="active-txt" name="language">
									<?foreach($cng->languageset as $lang):?>
										<option <?if($lang === $language):?>selected<?endif?> value="<?=$lang?>"><?=$lang?></option>
									<?endforeach?>
									<script>
									(function(select){
										select.onchange=function(){
											LANGUAGE = select.value;
											reloadFeed(select.value, 1);
										}
									})(document.currentScript.parentNode);
									</script>
								</select>
							</div>
						</div>
						<div id="feed">
							<?foreach($feed as $row):?>
							<a class="snippet" href="/blogger/<?=$row['language']?>/<?=$page?>/<?=$row['PageID']?>">
								<div class="preview"><img src="<?=$row['preview']?>" alt="&#xe906;"></div>
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
						<a class="light-txt" href="/blogger/<?=$language?>/1"><span class="active-txt">↳</span> All</a>
						<div class="root">
						<?php
						$keywords = $mySQL->getGroup("SELECT KeyWORD FROM gb_keywords ORDER BY rating DESC")['KeyWORD'];
						foreach($keywords as $keyword):?>
							<a href="/blogger/<?=$language?>/1/<?=$keyword?>"><?=$keyword?></a>
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
						<label title="mediaset" class="tool" data-translate="title" onclick="new Box(null, 'mediaset/box')">&#xe94b;</label>
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
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							if(event.target.id!="left-default") STANDBY.leftbar = event.target.id;
						}});
    					if(STANDBY.leftbar) bar[STANDBY.leftbar].checked = true;
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar light-txt">
				<?if(PAGE_ID):?><a class="tool" title="Back" href="/blogger/<?=ARG_1?>/<?=ARG_2?>">❬</a><?endif?>
				<div class="toolbar t">
					<label title="create post" data-translate="title" class="tool" onclick="CreatePost()">&#xe89c;</label>
					<?if(PAGE_ID):?>
					<label title="clone post" data-translate="title" class="tool" onclick="new Box(null, 'blogger/clonebox/<?=$post['PageID']?>')">&#xe925;</label>
					<button title="save" form="metadata" data-translate="title" class="tool transparent-bg light-txt" type="submit">&#xf0c7;</button>
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
				<hr class="separator right">
				<div class="toolbar t right">
					<label id="mediaset-shower-btn" for="mediaset-shower" title="show mediaset" class="tool" data-translate="title">&#xe94b;</label>
					<label for="reset" title="reset mediaset" class="tool" data-translate="title">&#xf021;</label>
				</div>
				<?else:?>
				<div class="toolbar right">
					<div class="select">
						<select class="active-txt" name="language">
							<?foreach($cng->languageset as $lang):?>
								<option <?if($lang==$language):?>selected<?endif?> value="<?=$lang?>"><?=$lang?></option>
							<?endforeach?>
							<script>
							(function(select){
								select.onchange=function(){
									LANGUAGE = select.value;
									reloadFeed(select.value, 1);
								}
							})(document.currentScript.parentNode);
							</script>
						</select>
					</div>
				</div>
				<?endif?>
			</header>
			<main class="light-txt">
				<?if(PAGE_ID):?>
				<form id="mediaset" class="tab">
					<input id="reset" type="reset" hidden>
					<input name="setid" value="<?=$post['SetID']?>" type="hidden">
					<iframe width="100%" height="500" frameborder="no"></iframe>
					<script>
					(function(form){
						var frame = form.querySelector("iframe");
						var imgset = frame.contentWindow;	
						window.addEventListener("load",function(){
							frame.onload=function(){
								form.setid.value = imgset.SETID || "NULL"
								frame.height = imgset.document.body.scrollHeight+20;
							}

							reauth();
							imgset.location.href = (<?if(empty($post['SetID'])):?>false<?else:?>true<?endif?>) 
								? "/mediaset/set/<?=$post['SetID']?>"
								: "/mediaset/set";
						});
						form.onreset=function(event){
							event.preventDefault();
							imgset.location.href = "/mediaset/set";
						}
					})(document.currentScript.parentNode);
					</script>
				</form>
				<iframe src="/editor/embed" width="100%" height="100%" frameborder="no"></iframe>
				<script>var EDITOR = document.currentScript.previousElementSibling.contentWindow;</script>
				<?else:?>
				<!--~~~~~~~-->
				<div id="feed">
					<?foreach($feed as $row):?>
					<a class="snippet" href="/blogger/<?=$row['language']?>/<?=$page?>/<?=$row['PageID']?>">
						<div class="preview">
							<?if(empty($row['video'])):?>
							<img src="<?=$row['preview']?>" alt="&#xe906;">
							<?else:?>
							<video src="<?=$row['video']?>" poster="<?=$row['preview']?>" muted></video>
							<?endif?>
						</div>
						<div class="header"><?=$row['header']?></div>
						<div class="options">
							<span><?=date("d M, H:i", $row['created'])?></span>
							<span class="<?if($row['published']=="Published"):?>green-txt<?else:?>red-txt<?endif?>"><?=$row['published']?></span>
						</div>
					</a>
					<?endforeach?>
					<script>
					(function(tile){
						tile.querySelectorAll(".preview>video").forEach(function(video){
							if(video.networkState) video.onmouseover=function(){
								video.play();
								video.onmouseout=function(){
									video.load(); 
								}
							}
						});
					})(document.currentScript.parentNode);
					</script>
				</div>
				<div class="h-bar pagination white-txt">
				<?if(($total = ceil($count/$limit))>1):
					$root = "/blogger/".$language;
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
							<small>ID: <output name="ID"><?=$post['ID']?></output> / <output class="active-txt"><?=$post['PageID']?></output></small>
							<div class="select">
								<select name="language">
									<?$pages = $mySQL->get("SELECT * FROM gb_blogfeed WHERE ID={int}", $post['ID'])?>
									<?foreach($pages as $pg):?>
									<option <?if($pg['PageID'] == $post['PageID']):?>selected<?endif?> value="<?=$pg['language']?>/<?=$page?>/<?=$pg['PageID']?>"><?=$pg['language']?></option>
									<?endforeach?>
									<script>
									(function(select){
										select.onchange=function(){
											location.href = "/blogger/"+select.value;
										}
									})(document.currentScript.parentNode)
									</script>
								</select>
							</div>
							<div class="right">
								<small><span data-translate="textContent">created</span>:</small>
								<input type="date" name="date" value="<?=date("Y-m-d",$post['created'])?>">
								<input type="time" name="time" value="<?=date("H:i", $post['created'])?>">
							</div>
						</div>
						<!-- COVERS -->
						<input id="image-cover-tab" type="radio" name="cover-tab" hidden checked>
						<label for="image-cover-tab">Image Cover</label>
						<input id="video-cover-tab" type="radio" name="cover-tab" hidden>
						<label for="video-cover-tab">Video Cover</label>
						<div class="black-bg" align="right">
							<?php
							$categories = $mySQL->get("SELECT name FROM gb_sitemap WHERE parent LIKE 'blog' AND language LIKE {str}", $language);
							if(count($categories)):?>
							<div class="select" title="category" data-translate="title">
								<select name="category" class="active-txt">
									<?foreach($categories as $category):?>
									<option <?if($post['category']==$category['name']):?>selected<?endif?> value="<?=$category['name']?>"><?=$category['name']?></option>
									<?endforeach?>
								</select>
							</div>
							<?else:?>
							<input type="hidden" name="category" value="articles">
							<?endif?>
							<div class="select" title="template" data-translate="title">
								<select name="template" class="active-txt"> 
									<?foreach(glob("../".BASE_FOLDER."/themes/".$cng->theme."/includes/blog/*.html") as $file):$file = pathinfo($file)['filename']?>
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
							<img src="<?=$post['preview']?>" alt="">
						</div>
						<div id="video-cover">
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
									navigator.location.href="/navigator/folder/video/radio?"+options.join("&");
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
							<video src="<?=$post['video']?>" controls>
						</div>
						<!-- OPTIONS -->
						<div id="options" class="right">
							<label><input name="published" <?if($post['published']=="Published"):?>checked<?endif?> type="checkbox" hidden><span>Published</span></label>
							<label><input name="amp" type="checkbox" <?if(!empty($post['amp'])):?>checked<?endif?> hidden><span>Google AMP</span></label>
							<label><input name="ina" type="checkbox" <?if(!empty($post['ina'])):?>checked<?endif?> hidden><span>Instant Articles</span></label>
							<label><input name="ads" type="checkbox" <?if(($post['Ads']=="YES")):?>checked<?endif?> hidden><span data-translate="textContent">ads</span></label>
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
							form.onsubmit=function(event){
								event.preventDefault();
								var box = new Box('{}', "boxfather/savelogbox/modal", function(){	box.drop(); });
								box.onopen = function(){SavePost(box, form)}
							}
							form.onreset=function(event){
								event.preventDefault();
								confirmBox("remove post", function(){
									XHR.push({
										addressee:"/blogger/actions/remove/<?=$post['PageID']?>",
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

					<input id="codefullscreen" name="codefullscreen" type="checkbox" hidden autocomplete="off" form="rightbar-tabs">
					<input id="code-editor-tab" name="tabs" type="radio" form="rightbar-tabs" hidden>
					<div id="code" class="tab">
						<div class="h-bar dark-btn-bg">
							<span class="tool">&#xeae4;</span> HTML
							<div class="toolbar r right">
								<label for="codefullscreen" title="screen mode" data-translate="title" class="screenmode-btn"></label>
							</div>
						</div>
						<xmp><?=gzdecode($post['content'])?></xmp>
						<script>
						var	CODE = ace.edit(document.currentScript.previousElementSibling);
							CODE.setTheme("ace/theme/twilight");
							CODE.getSession().setMode("ace/mode/html");
							CODE.setShowInvisibles(false);
							CODE.setShowPrintMargin(false);
							CODE.resize();
							CODE.session.on('change', function(event){
								if(CODE.curOp && CODE.curOp.command.name){
									html_change = true;
									setTimeout(function(){
										if(html_change) EDITOR.setContent(CODE.session.getValue());
										html_change = false;
									},1000);
								}
							});
						EDITOR.onload = function(){
							EDITOR.CODE = CODE;
							EDITOR.setContent( CODE.session.getValue() );
							EDITOR.save = function(){
								XHR.push({
									"Content-Type":"text/html",
									"addressee":"/blogger/actions/save-content/<?=$post['PageID']?>",
									"body":EDITOR.getContent()
								});
							}
						}
						window.addEventListener("keydown",function(event){
							if((event.ctrlKey || event.metaKey) && event.keyCode==83){
								event.preventDefault();
								EDITOR.save();
							}
						});
						</script>
					</div>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Metadata" class="tool" for="right-default" data-translate="title">&#xe871;</label>
					<label title="customizer" class="tool" data-translate="title" onclick="new Box(null,'customizer/box/<?=PAGE_ID?>')">&#xe993;</label>
					<label title="code editor" class="tool" for="code-editor-tab" data-translate="title">&#xeae4;</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							STANDBY.rightbar = event.target.id;
						}});
						if(STANDBY.rightbar) bar[STANDBY.rightbar].checked = true;
						bar.codefullscreen.onchange=function(){
							CODE.resize();
						}
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

		var LANGUAGE = "<?=$language?>";
		function reloadFeed(language, page, PageID){
			LANGUAGE = language;
			var path = location.pathname.split(/\//);
				path[2] = language;
				path[3] = page || 1;
				path[4] = PageID || path[4] || "";
			XHR.push({
				addressee:"/blogger/actions/reload/"+language+"/"+page,
				onsuccess:function(response){
					window.history.pushState("", "feed", path.join("/"));
					let feed = doc.querySelector("#feed")
						feed.innerHTML = response;
						feed.querySelectorAll("div.pagination>a").forEach(function(pg){
							pg.onclick=function(){reloadFeed(language, pg.textContent);}
					});
				}
			});
		}
		</script>
	</body>
</html>