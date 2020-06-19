<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff)
];
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/settings/index.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=settings" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden disabled>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden disabled>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="domains-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="domains" class="tab body-bg">
						<div class="root light-txt">
						<?foreach(scandir("../") as $subdomain) if(file_exists("../".$subdomain."/config.init")) if($subdomain===ARG_1):?>
							<a href="/settings/<?=$subdomain?>">🌎 <?=$subdomain?></a>
							<div class="root">
							<?foreach(scandir("../".$subdomain."/modules") as $module) if(is_dir("../".$subdomain."/modules/".$module) && ($module!=".") && ($module!="..")):?>
								<a <?if($module==ARG_2):?>class="active-txt"<?endif?> href="/settings/<?=$subdomain?>/<?=$module?>"><?=$module?></a>
							<?endif?>
							</div>
						<?else:?><a href="/settings/<?=$subdomain?>">🌎 <?=$subdomain?></a><?endif?>
						</div>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="domains" class="tool" for="domains-tab" data-translate="title">&#xe902;</label>
					</div>
					<div class="toolbar">
						<label title="navigator" class="tool" data-translate="title" onclick="new Box(null, 'navigator/box')">&#xf07c;</label>
						<label title="mediaset" class="tool" data-translate="title" onclick="new Box(null, 'mediaset/box')">&#xe94b;</label>
					</div>
					<div class="toolbar">
						<label title="default subdomain" class="tool" data-translate="title" onclick="new Box(JSON.encode(['light-btn-bg']), 'boxfather/subdomainselect')">&#xe995;</label>
						<label title="Spyware search" class="tool" onclick="new Box(null,'verifications/box')">&#xeae3;</label>
						<label title="keywords" class="tool" data-translate="title" onclick="new Box(null, 'keywords/box')">&#xe9d3;</label>
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
			<header class="h-bar t logo-bg">
				<div class="toolbar">
					<?if(defined("ARG_1")):?>
					<label class="tool" data-translate="title" title="save" onclick="saveSettings()">💾</label>
					<label class="tool" data-translate="title" title="show pattern" onclick="showPattern(settingsToJson(), 'JsonToSettings')">⌘</label>
					<?endif?>
				</div>
			</header>
			<main>
				<table spellcheck="false" class="settings" width="100%" rules="cols" cellpadding="6" cellspacing="0" bordercolor="#999">
					<col width="26"><col width="154"><col><col><col width="26">
					<thead>
						<?if(defined("ARG_1")):?><tr class="v-bar-bg"><th colspan="2">Key</th><th>Value</th><th colspan="2">Valid</th></tr><?endif?>
					</thead>
					<tbody>
					<?php
					if(defined("ARG_2")){?>
						<tr class="h-bar-bg"><td class="section h-bar" align="center" colspan="5"><?=ARG_2?></td></tr>
						<?foreach(JSON::load("../".ARG_1."/modules/".ARG_2."/config.init") as $key=>$val):?>
						<tr data-type="<?=$val['type']?>">
							<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
							<td align="center" data-translate="textContent" data-key="<?=$key?> <?=(empty($key) ? "contenteditable='true'" : "")?>"><?=$key?></td>
							<td contenteditable="true"><?=$val['value']?></td>
							<td contenteditable="true">
							<?switch($key):
								case "status": print implode(", ", ["enabled","disabled"]); break;
								case "access": eval("show_".$mySQL->getRow("SHOW COLUMNS FROM gb_staff LIKE 'Group'")['Type'].";"); break;
								default: print implode(", ", $val['valid']); break;
							endswitch?>
							</td>
							<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
						</tr>
						<?endforeach;

					}elseif(defined("ARG_1")){
						$subdomains=$modules=$themes = [];
						foreach(scandir("..") as $folder){
							if(($folder!="." && $folder!="..") && is_dir("../".$folder)) $subdomains[] = $folder;
						}
						foreach(scandir("../".ARG_1."/modules") as $module){
							if(is_dir("../".ARG_1."/modules/".$module) && $module!="." && $module!="..") $modules[] = $module;
						}
						foreach(scandir("../".ARG_1."/themes") as $dir){
							if(is_dir("../".ARG_1."/themes/".$dir) && $dir!="." && $dir!="..") $themes[] = $dir;
						}
						foreach(JSON::load("../".ARG_1."/config.init") as $name=>$section):?>
						<tr class="h-bar-bg"><td class="section h-bar" align="center" colspan="5"><?=$name?></td></tr>
						<?foreach($section as $key=>$val):?>
						<tr data-type="<?=$val['type']?>">
							<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
							<td align="center" data-translate="textContent" data-key="<?=$key?> <?=(empty($key) ? "contenteditable='true'" : "")?>"><?=$key?></td>
							<td contenteditable="true"><?=$val['value']?></td>
							<td contenteditable="true">
							<?switch($key):
								case "subdomain":
								case "mobile subdomain":
								case "desktop subdomain":
								case "base folder": print implode(", ",$subdomains); break;
								case "default module": print implode(", ", $modules); break;
								case "theme":
								case "mobile theme": print implode(", ", $themes); break;
								default: print implode(", ", $val['valid']); break;
							endswitch?>
							</td>
							<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
						</tr>
						<?endforeach; endforeach;
					}?>
					</tbody>
				</table>
			</main>
		</div>
		<script>
		(function(body){
			body.querySelector("#screenmode").checked = (STANDBY.screenmode=="true");
		})(document.currentScript.parentNode);
		</script>
	</body>
</html>
<?function show_enum(){ print implode(", ", func_get_args()); }?>
