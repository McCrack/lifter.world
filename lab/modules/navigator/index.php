<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=uploader" defer charset="utf-8"></script>
		<style>#wrapper>main{font-size:0;}</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden disabled>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label></label>
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
				</form>
			</aside>
			<header class="h-bar light-btn-bg">
				<div class="toolbar t">
					<label onclick="NAVIGATOR.upload()" title="upload" data-translate="title" class="tool">&#xe905;</label>
					<label onclick="NAVIGATOR.createFolder()" title="create folder" data-translate="title" class="tool">&#xe2cc;</label>
					<label onclick="NAVIGATOR.remove()" title="remove" data-translate="title" class="tool">&#xe94d;</label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<div class="toolbar t right">
					<label onclick="NAVIGATOR.prowler()" title="Prowler" data-translate="title" class="tool">🌎</label>
					<label onclick="NAVIGATOR.createMediaset()" title="create mediaset" data-translate="title" class="tool">&#xe94b;</label>
					<label onclick="NAVIGATOR.selectAll()" title="select all" data-translate="title" class="tool">&#xe948;</label>
				</div>
			</header>
			<main>
				<iframe id="navigator" width="100%" height="100%" frameborder="no"></iframe>
				<script>
				reauth();
				var options=[];
				if(STANDBY.subdomain) options.push("subdomain="+STANDBY.subdomain);
				if(STANDBY[STANDBY.subdomain]) options.push("path="+STANDBY[STANDBY.subdomain]);
				var NAVIGATOR = document.currentScript.previousElementSibling.contentWindow;
				NAVIGATOR.location.href="/navigator/folder/.*?"+options.join("&");
				</script>
			</main>
		</div>
	</body>
</html>