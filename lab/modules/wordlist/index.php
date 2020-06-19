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
		<script src="/modules/wordlist/index.js"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8" defer></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=wordlist" defer charset="utf-8"></script>
		<style>
		#wrapper>main{background:none;}
		#wrapper>main>table{box-shadow:12px 12px 4px -6px rgba(0,0,0, .5);}
		#dictionaries a{ font-family:tools, EmojiSymbols, calibri }
		</style>
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
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="dictionaries-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="dictionaries" class="tab body-bg" id="word-list">
						<div class="h-bar white-txt" data-translate="textContent">wordlist</div>
						<div class="root light-txt">
						<?foreach(scandir("../") as $subdomain) if($subdomain!="." && $subdomain!=".."):?>
							<a href="/wordlist/<?=$subdomain?>">&#xf07c; <?=$subdomain?></a>
							<?if(is_dir("../".$subdomain."/localization")):?>
							<div class="root">
							<?foreach(scandir("../".$subdomain."/localization") as $file) if(is_file("../".$subdomain."/localization/".$file)):
								$file = reset(explode(".",$file))?>
								<a href="/wordlist/<?=$subdomain?>/<?=$file?>">📄 <?=$file?></a>
							<?endif?>
							</div>
							<?endif?>
						<?endif?>
						</div>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="wordlists" class="tool" for="dictionaries-tab">📜</label>
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
				<div class="toolbar t">
					<?if(defined("ARG_1")):?>
					<label class="tool" data-translate="title" title="create wordlist" onclick="createWordlist()">📄</label>
					<?if(defined("ARG_2")):?>
					<label class="tool" data-translate="title" title="save wordlist" onclick="saveWordlist()">&#xf0c7;</label>
					<label class="tool" data-translate="title" title="remove wordlist" onclick="removeWordlist()">&#xe94d;</label>
					<?endif; endif?>
				</div>
				<div class="toolbar r right">
				<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
				<?endif?>
				</div>
			</header>
			<main>
				<?if(defined("ARG_1") && defined("ARG_2")):?>
				<table id="wordlist" width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#BBB">
					<thead>
						<tr class="h-bar-bg">
							<td class="l" width="36"><label class="tool" data-translate="title" title="show pattern" onclick="showPattern(wordlistToJson(), jsontowordlist)">⌘</label></td>
							<td align="center"><b>Keys</b></td>
							<?php
							$wl = JSON::load("../".ARG_1."/localization/".ARG_2.".json");
							$keys = [];
							foreach($wl as $lang=>$list):
								$keys=array_merge($keys,$list)?>
								<th><?=$lang?></th>
							<?endforeach?>
							<td class="r" width="36"><label class="tool" data-translate="title" title="add language" onclick="addLanguage()">🌎</label></td>
						</tr>
					</thead>
					<tbody>
					<?if(empty($keys)):?>
						<tr>
							<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
							<td align="center" contenteditable="true"></td>
							<?foreach($wl as $lang=>$list):?>
							<td contenteditable="true"></td>
							<?endforeach?>
							<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
						</tr>
					<?else: foreach($keys as $key=>$val):?>
						<tr>
							<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
							<td align="center" contenteditable="true"><?=$key?></td>
							<?foreach($wl as $lang=>$list):?>
							<td contenteditable="true"><?=$wl[$lang][$key]?></td>
							<?endforeach?>
							<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
						</tr>
					<?endforeach; endif?>
					</tbody>
				</table>
				<?endif?>
			</main>
		</div>
	</body>
</html>