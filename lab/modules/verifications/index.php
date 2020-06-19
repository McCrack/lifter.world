<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	//$cng = new config("../".BASE_FOLDER."/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" async charset="utf-8" onload="translate.fragment()"></script>
		<style>#wrapper>header>div.toolbar>label.tool>a{ color:inherit }</style>
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
			<header class="h-bar logo-bg">
				<div class="toolbar t">
					<label class="tool" title="Create Hash Map"><a href="/actions/verifications/build_map">&#xeae3;</a></label>
					<label class="tool" title="Match Hashes" onclick="MatchHashesMap()">&#xe925;</label>
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
		function MatchHashesMap(){
			var inp = doc.create("input", {type:"file",name:"files[]",accept:"*.json"});
			inp.onchange = function(){
				var file = inp.files[0];
				var reader = new FileReader();
  				reader.onload = function(e){
    				var content = e.target.result;
    				XHR.push({
    					addressee:"/actions/verifications/match_hashes",
    					body:content,
    					onsuccess:function(response){
    						document.querySelector("#wrapper>main").innerHTML = response;
    					}
    				});
  				};
  				reader.readAsText(file);
			}
			inp.click();
		}
		</script>
	</body>
</html>