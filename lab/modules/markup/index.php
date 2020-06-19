<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	$iconset = [
		"application"=>"&#xeae3;",
		"text"=>"&#xe926",
		"zip"=>"&#xe92b",
		"html"=>"&#xeae4", 
		"css"=>"&#xeae6",
		"php"=>"&#xf069",
		"json"=>"&#xe8ab",
		"init"=>"&#xe995",
		"js"=>"&#xf013"
	];

	if(defined("ARG_2")){
		$material = $mySQL->getRow("
		SELECT * FROM gb_sitemap
		CROSS JOIN gb_pages USING(PageID)
		CROSS JOIN gb_static USING(PageID)
		WHERE 
			name LIKE {str} AND
			language LIKE {str}
		LIMIT 1", ARG_2, ARG_1);
		$content = gzdecode($material['content']);
		define("MATERIAL_ID", $material['PageID']);
		define("MATERIAL_NAME", $material['name']);
	}else{
		define("MATERIAL_ID", false);
		define("MATERIAL_NAME", "root");
	}
	$cng = new config("../".BASE_FOLDER."/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/code-editor/tpl/code-editor.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" defer charset="utf-8"></script>
		<style>
		#wrapper>header>a.tool{
			color:#DDD;
			padding:0 5px;
			font-size:15px;
			line-height:36px;
			margin-left:-10px;
			display:inline-block;
			vertical-align:middle;
			background-color:#111;
			border-right:1px solid #555;
		}
		#wrapper>header>a.tool:hover{
			color:white;
			border-color:white;
			background-color:#008DD0;
		}
		#tile{
			padding:10px;
			display:grid;
			grid-gap:10px;
			grid-template-columns:repeat(auto-fill, minmax(260px, min-content));
		}
		#tile>a.snippet{
			background-color:white;
			box-shadow:10px 10px 5px -8px rgba(0,0,0, .5);
		}
		#tile>a.snippet>div.header{
			color:#444;
			font-size:15px;
		}
		#wrapper>main>xmp{
			margin:0;
			width:100%;
			height:100%;
		}
		#sitemap .select{margin:5px 0}
		#sitemap .select>select{font-size:16px}
		#sitemap a.root-itm::before{
			content:"↳";
			margin:0 4px;
			color:#00ADF0;
			font-family:EmojiSymbols, tools;
		}
		#sitemap a.published-txt{
			color:#C94;
		}
		</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden>
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

					<input id="sitemap-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="sitemap" class="tab body-bg light-txt">
						<div class="h-bar white-txt">
							<span data-translate="textContent">sitemap</span>
							<div class="select right">
								<select class="active-txt">
									<?php 
									$language = defined("ARG_1") ? ARG_1 : $cng->language;
									foreach($cng->languageset as $lang):?>
										<option <?if($lang === $language):?>selected<?endif?> value="<?=$lang?>"><?=$lang?></option>
									<?endforeach?>
									<script>
									(function(select){
										select.onchange=function(){
											LANGUAGE = select.value;
											reloadTree(select.value);
										}
									})(document.currentScript.parentNode);
									</script>
								</select>
							</div>
						</div>
						<a class="root-itm light-txt" href="/markup/<?=$language?>">Root</a>
						<?php
						function staticTree(&$items, $offset="root"){
							if(is_array($items[$offset])):?>
							<div class="root">
								<?foreach($items[$offset] as $key=>$val):?>
								<a href="/markup/<?=($val['language'].'/'.$val['name'])?>" class="<?if($val['PageID']==MATERIAL_ID):?>active-txt<?elseif($val['published']==='Published'):?>published-txt<?endif?>"><?=(empty($val['header'])?$val['name']:$val['header'])?></a>
								<?staticTree($items, $key);
								endforeach?>
							</div>
							<?endif;
						}
						$tree = $mySQL->getTree("name", "parent", "SELECT * FROM gb_sitemap WHERE language LIKE '".$language."' ORDER BY PageID ASC");
						staticTree($tree)?>
						<script>
						(function(map){
							map.onscroll=function(){STANDBY.mapScrollTop = map.scrollTop;}
						})(document.currentScript.parentNode)
						</script>
					</div>

					<input id="patterns-tab" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div class="tab body-bg">
						<div class="h-bar r white-txt">
							Patterns
							<label class="tool right" title="Patterns Box" onclick="showPatternBox('html', 'twilight')">&#xe8ab;</label>
						</div>
						<div class="root light-txt">
							<?function patterns_tree($root, $level=0){
								foreach(glob($root."/*", GLOB_ONLYDIR) as $i=>$dir):?>
								<input id="p-<?=($level.'-'.$i)?>" type="checkbox" value="<?=$dir?>" hidden>
								<label for="p-<?=($level.'-'.$i)?>" data-path="<?=$dir?>"><?=basename($dir)?></label>
								<div class="root"><?=patterns_tree($dir, $level+1)?></div>
								<?endforeach;
								global $iconset;
								foreach(array_filter(glob($root."/*"), "is_file") as $file):
									$info = pathinfo($file);
									$type = explode("/",mime_content_type($file));
									$symbol = $iconset['application'];		
									if(isset($iconset[$info['extension']])){
										$symbol = $iconset[$info['extension']];
									}elseif(isset($iconset[$type[1]])){
										$symbol = $iconset[$type[1]];
									}elseif(isset($iconset[$type[0]])) $symbol = $iconset[$type[0]]?>
									<a class="file" data-path="<?=$file?>" data-type="<?=(($type[1]=="zip") ? $type[1] : $type[0])?>" data-name="<?=$info['basename']?>"><?=$symbol?></a>
								<?endforeach;
							}
							patterns_tree("patterns/html")?>
							<script>
							(function(root){
								root.querySelectorAll("a").forEach(function(pattern){
									pattern.ondblclick=function(event){
										event.preventDefault();
										XHR.push({
											addressee:"/patterns/actions/get-pattern?path="+event.target.dataset.path,
											onsuccess:function(response){
												editor.session.insert(editor.selection.getCursor(), response);
											}
										});
									}
								});
							})(document.currentScript.parentNode)
							</script>
						</div>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="sitemap" class="tool" for="sitemap-tab" data-translate="title">&#xe902;</label>
						<label title="Patterns" class="tool" for="patterns-tab">⌘</label>
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
			<header class="h-bar light-btn-bg">
				<?if(defined("ARG_2")):?><a class="tool" title="Back" href="/markup/<?=ARG_1?>">❬</a><?endif?>
				<div class="toolbar t">
					<label title="create page" data-translate="title" class="tool" onclick="new Box('<?=MATERIAL_NAME?>','sitemap/addpagebox/'+LANGUAGE+'/markup')">&#xe89c;</label>
					<?if(defined("ARG_2")):?>
					<label title="save" data-translate="title" class="tool" onclick="saveContent()">&#xf0c7;</label>
					<label title="remove" data-translate="title" class="tool" onclick="deletePage()">&#xe94d;</label>
					<?endif?>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main>
				<?if(defined("ARG_2")):?>
				<xmp><?=$content?></xmp>
				<script>
				var editor = ace.edit(document.currentScript.previousElementSibling);
				editor.setTheme("ace/theme/twilight");
				editor.getSession().setMode("ace/mode/html");
				editor.setShowInvisibles(false);
				editor.setShowPrintMargin(false);
				editor.resize();

				function saveContent(){
					XHR.push({
						"Content-Type":"text/html",
						"addressee":"/sitemap/actions/save-content/<?=MATERIAL_ID?>", 
						"body":editor.getValue()
					});
				}
				function deletePage(){
					XHR.push({
						addressee:"/sitemap/actions/remove-page/<?=MATERIAL_ID?>", 
						onsuccess:function(response){
							if(isNaN(response)){
								alertBox(response);
							}else location.pathname = "/markup/"+LANGUAGE;
						}
					});
				}
				window.addEventListener("keydown",function(event){
					if((event.ctrlKey || event.metaKey) && event.keyCode==83){
						event.preventDefault();
						saveContent();
					}
				});
				</script>
				<?else:?>
				<div id="tile">
				<?$roots = [];
					foreach($tree['root'] as $key=>$itm): $roots[]=$key?>
					<a class="snippet" href="/markup/<?=($itm['language'].'/'.$itm['name'])?>">
						<div class="preview"><img src="<?=$itm['preview']?>"></div>
						<div class="header"><?=(empty($itm['header'])?$itm['name']:$itm['header'])?></div>
						<div class="options">
							<span><?=$itm['language']?></span>
							<span <?if($itm['published']=="Published"):?>class="green-txt"<?endif?>"><?=$itm['published']?></span>
						</div>
					</a>
					<?endforeach?>
				
					<?foreach($roots as $key) foreach($tree[$key] as $itm):?>
					<a class="snippet" href="/markup/<?=($itm['language'].'/'.$itm['name'])?>">
						<div class="preview"><img src="<?=$itm['preview']?>"></div>
						<div class="header"><?=(empty($itm['header'])?$itm['name']:$itm['header'])?></div>
						<div class="options">
							<span><?=$itm['language']?></span>
							<span <?if($itm['published']=="Published"):?>class="green-txt"<?endif?>"><?=$itm['published']?></span>
						</div>
					</a>
					<?endforeach?>
				</div>
				<?endif?>
				<script>
				var LANGUAGE = "<?=$language?>";
				function reloadTree(lang){
					var path = location.pathname.split(/\//);
					path[2] = lang;
					XHR.push({
						addressee:"/sitemap/actions/reload-tree/"+lang+"/markup",
						onsuccess:function(response){
							doc.querySelector("#sitemap>.root").outerHTML = response;
							window.history.pushState("", "tree", path.join("/"));
						}
					});
				}
				</script>
			</main>
		</div>
	</body>
</html>