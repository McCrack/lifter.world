<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	
	//$cng = new config("../".BASE_FOLDER."/config.init");

	if(isset($_GET['path']) && file_exists($_GET['path'])){
		define("OPENED", true);
	}else define("OPENED", false);

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/code-editor/tpl/code-editor.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=uploader" defer></script>
		<style>
		#wrapper>xmp{
			margin:0;
			height:calc(100vh - 36px);
		}
		#playground{
			font-size:0;
		}
		#playground>iframe{
			height:calc(100% - 36px);
		}
		</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" checked hidden>
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
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
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
						/*
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							if(event.target.id!="left-default") STANDBY.leftbar = event.target.id;
						}});
    					if(STANDBY.leftbar) bar[STANDBY.leftbar].checked = true;
    					*/
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar dark-btn-bg">
				<div class="toolbar t">
					<label title="save" data-translate="title" class="tool" onclick="saveFile()">&#xf0c7;</label>
					<label title="HTML Patterns" class="tool" onclick="showPatternBox('html', 'twilight')">⌘</label>
				</div>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<xmp><?include_once("modules/customizer/form.html")?></xmp>
			<script>
			var editor = ace.edit(document.currentScript.previousElementSibling);
			editor.setTheme("ace/theme/twilight");
			editor.getSession().setMode("ace/mode/html");
			
			editor.setShowInvisibles(false);
			editor.setShowPrintMargin(false);
			editor.resize();
			var timeout,
				changed = false;
			editor.session.on('change', function(event){
				if(editor.curOp && editor.curOp.command.name){
					changed = true;
					clearTimeout(timeout);
					timeout = setTimeout(function(){
						if(changed) saveForm();
					}, 4000);
				}
			});
			window.addEventListener("keydown",function(event){
				if((event.ctrlKey || event.metaKey) && event.keyCode==83){
					event.preventDefault();
					saveForm();
				}
			});
			function saveForm(){
				XHR.push({
					addressee:"/actions/customizer/sv_customize_form",
					body:editor.session.getValue(),
					onsuccess:function(response){
						changed = false;
						clearTimeout(timeout);
						embed.location.reload();
					}
				});
			}
			</script>
			<!--~~~~~~~~~~~~~~-->
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<div id="playground" class="tab body-bg light-txt" autocomplete="off">
						<div class="box-caption active-bg">&#xe992;</div>
						<div class="h-bar">Page customizer</div>
						<iframe src="" width="100%" frameborder="no"></iframe>
						<script>
						reauth();
						var embed = document.currentScript.previousElementSibling.contentWindow;
							embed.location.href="/customizer/embed";
						</script>
					</div>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Playground" class="tool" for="right-default">&#xe871;</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
			<!--~~~~~~~~~~~~~~-->
		</div>
	</body>
</html>