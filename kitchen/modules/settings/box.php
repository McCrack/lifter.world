
<?$handle = "b:".time();?>
<div id="<?=$handle?>" class="mount" style="width:760px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.settings-box>div.box-body{
		display:grid;
	}
	.settings-box>div.box-body>aside{
		overflow:auto;
		filter:brightness(120%);
	}
	@media (max-width:460px){
		.settings-box>div.box-body{grid-template-columns:180px auto;}
	}
	@media (min-width:461px){
		.settings-box>div.box-body{grid-template-columns:240px auto;}
	}
	.h-bar>output[name='module'],
	.h-bar>output[name='subdomain']{
		color:white;
		font-size:18px;
		line-height:18px;
	}
	.h-bar>output[name='subdomain']::after{
		content:" ❭ ";
	}
	</style>
	<script src="/modules/settings/index.js"></script>
	<form class="box settings-box h-bar-bg">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xe995;<?include_once("components/movebox.php")?></div>
		<div class="h-bar logo-bg">
			<output name="subdomain"><?=SUBDOMAIN?></output><output name="module"></output>
		</div>
		<div class="box-body" spellcheck="false">
			<aside class="body-bg">
				<div class="root light-txt">
				<?foreach(scandir("../") as $subdomain) if(file_exists("../".$subdomain."/config.init")):?>
					<a data-subdomain="<?=$subdomain?>">🌎 <?=$subdomain?></a>
					<div class="root">
					<?foreach(scandir("../".$subdomain."/modules") as $module)
					if(file_exists("../".$subdomain."/modules/".$module."/config.init") && ($module!="..")):?>
						<a data-subdomain="<?=$subdomain?>" data-module="<?=$module?>">⚙ <?=$module?></a>
					<?endif?>
					</div>
					<?endif?>
				</div>
				<script>
				(function(aside){
					var form = aside.ancestor("form");
					aside.querySelectorAll("a").forEach(function(itm){
						itm.onclick=function(){
							XHR.push({
								addressee:"/actions/settings/showsubdomain/"+itm.dataset.subdomain+"/"+(itm.dataset.module || ""),
								onsuccess:function(response){
									aside.parentNode.querySelector("table>tbody").innerHTML = response;
									form.subdomain.value = itm.dataset.subdomain;
									form.module.value = (itm.dataset.module || "");
									form.align();
								}
							});
						}
					})
				})(document.currentScript.parentNode)
				</script>
			</aside>
			<main>
				<table width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#999">
					<thead>
						<tr class="h-bar-bg" height="36px"><th colspan="2">Key</th><th>Value</th><th colspan="2">Valid</th></tr>
					</thead>
					<tbody>
					<?php
					$subdomains=$modules=$themes = [];
					foreach(scandir("..") as $folder){
						if(($folder!="." && $folder!="..") && is_dir("../".$folder)) $subdomains[] = $folder;
					}
					foreach(scandir("modules") as $module){
						if(is_dir("modules/".$module) && $module!="." && $module!="..") $modules[] = $module;
					}
					foreach(scandir("themes") as $dir){
						if(is_dir("themes/".$dir) && $dir!="." && $dir!="..") $themes[] = $dir;
					}
					foreach(JSON::load("config.init") as $name=>$section):?>
						<tr class="v-bar-bg" height="36"><td class="section" align="center" colspan="5"><?=$name?></td></tr>
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
					<?endforeach; endforeach?>
					</tbody>
				</table>
			</main>
		</div>
		<div class="box-footer h-bar-bg" align="right">
			<button name="save" type="submit" class="light-btn-bg" data-translate="textContent">save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){form.drop();}
			form.onsubmit=function(){
				var key, section, settings={};
	
				var cells = form.querySelectorAll("table>tbody>tr>td");
				for(var i=0; i<cells.length; i++){
					if(cells[i].classList.contains("section")){
						section = cells[i].dataset.section || cells[i].textContent.trim();
						settings[section] = {};
					}else{
						key = cells[i].dataset.key || cells[i].textContent.trim();
						settings[section][key] = {
							"type":cells[i].parentNode.dataset.type || prompt('Please enter type of parameter "'+key+'" (enum, set or string)', "srting") || "string",
							"value":cells[++i].textContent.trim(),
							"valid":cells[++i].textContent.trim().split(/,\s*/)
						};
						/*~~~ VALIDATION ~~~*/		
						if(settings[section][key]['type']=="enum"){
							if(isNaN(settings[section][key]['valid'].inArray(settings[section][key]['value']))){
								alert(settings[section][key]['value']+" is not valid value");
								return false;
							}
						}else if(settings[section][key]['type']=="set"){
							settings[section][key]['value'].split(/,\s*/).forEach(function(value){
								if(isNaN(settings[section][key]['valid'].inArray(value))){
									alert(value+" is not valid value");
									return false;
								}
							});
						}
					}
				}
				XHR.push({
					addressee:"/actions/settings/save/"+form.subdomain.value+"/"+form.module.value,
					body:JSON.encode(settings),
					onsuccess:function(response){
						isNaN(response) ? alert(response) : form.drop();
					}
				});
			}
		})(document.currentScript.parentNode);
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>
<?function show_enum(){ print implode(", ", func_get_args()); }?>