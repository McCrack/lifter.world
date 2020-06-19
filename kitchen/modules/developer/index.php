<?php

$folders = preg_split("/,\s*/", JSON::load("modules/developer/config.init")['folders']['value'], -1, PREG_SPLIT_NO_EMPTY);

$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];

$iconset = [
	"application"=>"&#xeae3;",
	"text"=>"&#xe926",
	"mp3"=>"&#xe928",
	"mp4"=>"&#xe929", 
	"video"=>"&#xe92a",
	"zip"=>"&#xe92b",
	"html"=>"&#xeae4", 
	"css"=>"&#xeae6",
	"php"=>"&#xf069",
	"json"=>"&#xe8ab",
	"init"=>"&#xe995",
	"image"=>"&#xe927",
	"js"=>"&#xf013"
];
function navigate($path, $chain){
	foreach(glob($path."/*", GLOB_ONLYDIR) as $i=>$root):
		$dir = basename($root);
		$subchain = $chain."-".$dir?>
	<input id="<?=$subchain?>" name="<?=$subchain?>" value="<?=$root?>" type="checkbox" hidden>
	<label for="<?=$subchain?>" data-path="<?=$root?>"><?=$dir?></label>
	<div class="root"><?navigate($root, $subchain)?></div>
	<?endforeach;
	global $iconset;
	foreach(array_filter(glob($path."/*"), "is_file") as $file):
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

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/modules/developer/index.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=uploader" defer></script>
		<style>
		a.folder+div.root{
			display:none;
		}
		#wrapper>header{
			padding-left:0;
		}
		#wrapper>header>div.tabbar{
			max-width:calc(100% - 50px);
		}
		#wrapper>header>div.tabbar>label.tab{
			font-size:15px;
			max-width:100px;
			line-height:36px;
			position:relative;
			vertical-align:top;
			white-space:nowrap;
			display:inline-block;
			padding:0 15px 0 12px;
			border-right:1px solid #AAA;
		}
		#wrapper>header>div.tabbar>label.changed{
			color:#777;
			text-decoration:underline;
			text-decoration-color:black;
		}
		#wrapper>header>div.tabbar>label.selected{
			box-shadow:inset 0 0 5px -1px white;
			background:linear-gradient(to top, #FFF, #DDD);
		}
		#wrapper>header>div.tabbar>label.tab>sup{
			color:#333;
			top:0;
			right:2px;
			cursor:pointer;
			font-size:16px;
			line-height:16px;
			position:absolute;
		}
		#wrapper>main>input+iframe{
			width:100%;
			height:100%;
			display:none;
			border-width:0;
		}
		#wrapper>main>input:checked+iframe{
			display:block;
		}
		</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden disabled>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden disabled>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label for="rightbar-shower"></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="explorer-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="explorer" class="tab body-bg">
						<div class="h-bar white-txt">Explorer</div>
						<form class="root light-txt" autocomplete="off">
							<?foreach(glob("../*", GLOB_ONLYDIR) as $dir): $subdomain=basename($dir)?>
							<input id="<?=$subdomain?>" type="checkbox" name="<?=$subdomain?>" value="<?=$dir?>" hidden>
							<label for="<?=$subdomain?>" data-path="<?=$dir?>"><?=$subdomain?></label>
							<div class="root">
								<?foreach($folders as $i=>$folder) if(is_dir($dir."/".$folder)):?>
								<input id="<?=$subdomain?>-<?=$folder?>" name="<?=$subdomain?>-<?=$folder?>" type="checkbox" value="<?=($dir."/".$folder)?>" hidden>
								<label for="<?=$subdomain?>-<?=$folder?>" data-path="<?=($dir."/".$folder)?>"><?=$folder?></label>
								<div class="root">
								<?navigate($dir."/".$folder, $subdomain."-".$folder)?>
								</div>
								<?endif?>
							</div>
							<?endforeach?>
							<script>
							(function(form){
								form.onchange=function(event){
									STANDBY.path = event.target.value;
								}
								if(STANDBY.path){
									var chain = [];
									STANDBY.path.split(/\//).splice(1).forEach(function(dir){
										chain.push(dir);
										form[chain.join("-")].checked = true;
									});
								}
								form.querySelectorAll("a.file").forEach(function(itm){
									itm.onclick=function(){
										if(itm.dataset.type==="text" || itm.dataset.type==="application"){
											if(handle = inArray(editable, itm.dataset.path)){
												TABBAR.querySelector("label[for='"+handle+"']").click();
												return false;
											}
											handle = "t-"+Date.now();
											editable[handle] = itm.dataset.path;
											loadFile(handle, itm.dataset.path);
										}
									}
								});
							})(document.currentScript.parentNode);
							</script>
						</form>
						<script>
						(function(root){
							root.oncontextmenu=function(event){
								ContextMenu.create(event, event.target, context);
							}
						})(document.currentScript.parentNode);
						var context = function(obj){
							STANDBY.copylist = STANDBY.copylist || [];
							STANDBY.movelist = STANDBY.movelist || [];
							var menu = document.createDocumentFragment();

							if(obj.classList.contains("file")){
								if(obj.dataset.type=="zip"){
									menu.appendChild(document.querySelector("#unzip-context").cloneNode(true).content);
								}
								menu.appendChild(document.querySelector("#download-context").cloneNode(true).content);
							}else{
								if(STANDBY.copylist.length || STANDBY.movelist.length){
									menu.appendChild(document.querySelector("#paste-context").cloneNode(true).content);
								}
								menu.appendChild(document.querySelector("#folder-context").cloneNode(true).content);
							}
							menu.appendChild(document.querySelector("#context").cloneNode(true).content);
							menu.querySelectorAll(".context-item").forEach(function(itm){
								itm.onclick = function(){
									actions[itm.dataset.action](obj);
								}
							});
							translate.fragment(menu);
							return menu;
						}
						</script>
					</div>
					<!-- CONTEXT MENU -->
					<template id="context">
						<div class="context-item" data-action="copy" data-translate="textContent">copy</div>
						<div class="context-item" data-action="cut" data-translate="textContent">cut</div>
						<div class="context-item" data-action="rename" data-translate="textContent">rename</div>
						<hr size="1" color="#CCC">
						<div class="context-item" data-action="remove" data-icon="&#xe94d;" data-translate="textContent">delete</div>
					</template>
					<template id="folder-context">
						<div class="context-item" data-action="upload" data-icon="⬆" data-translate="textContent">upload</div>
						<div class="context-item" data-action="createFolder" data-icon="&#xe2cc;" data-translate="textContent">create folder</div>
						<div class="context-item" data-action="createFile" data-icon="&#xe89c;" data-translate="textContent">create file</div>
						<div class="context-item" data-action="zip" data-icon="&#xe92b;" data-translate="textContent">create archive</div>
						<hr size="1" color="#CCC">
					</template>
					<template id="paste-context">
						<div class="context-item" data-action="paste" data-icon="&#xe925;" data-translate="textContent">paste</div>
						<hr size="1" color="#CCC">
					</template>
					<template id="unzip-context">
						<div class="context-item" data-action="unzip" data-icon="&#xe92b;" data-translate="textContent">unzip</div>
					</template>
					<template id="download-context">
						<div class="context-item" data-action="download" data-icon="⬇" data-translate="textContent">download</div>
						<hr size="1" color="#CCC">
					</template>
					<!--~~~~~~~~~~~~~~-->
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="explorer" class="tool" for="explorer-tab" data-translate="title">&#xe902;</label>
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
				<div class="toolbar t tabbar">

				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main>
				
			</main>
		</div>
		<script>
		STANDBY.editable = STANDBY.editable || {};
		var TABBAR = doc.querySelector("#wrapper>header>div.tabbar");

		var editable = new Proxy(STANDBY.editable,{
			get(target, name){ return target[name] || null; },
			set(target, name, value){
				target[name] = value;
				window.localStorage[SECTION] = JSON.stringify(Standby);
			},
			deleteProperty: function(target, property){
				delete(target[property]);
				window.localStorage[SECTION] = JSON.stringify(Standby);
			}
		});

		window.onload=function(){
			setTimeout(function(){
				for(handle in editable){
					if(editable.hasOwnProperty(handle)) loadFile(handle, editable[handle]);
				};
			}, 1000);
		}
		</script>
	</body>
</html>