
<?$handle = "b:".time();?>
<div id="<?=$handle?>" class="mount" style="width:760px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<form class="box settings-box white-bg">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xf013;<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg" data-translate="textContent"><?=ARG_2?></div>
		<div class="box-body">
			<table width="100%" rules="cols" cellpadding="6" cellspacing="0" bordercolor="#999">
				<thead><tr class="v-bar-bg"><th colspan="2">Key</th><th>Value</th><th colspan="2">Valid</th></tr></thead>
				<tbody>
					<?foreach(JSON::load("modules/".ARG_2."/config.init") as $key=>$val):?>
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
					<?endforeach?>
				</tbody>
			</table>
		</div>
		<div class="box-footer" align="right">
			<button name="save" type="submit" class="light-btn-bg" data-translate="textContent">save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){form.drop();}
			form.onsubmit=function(){
				var key, settings={};
	
				var cells = form.querySelectorAll("table>tbody>tr>td");
				for(var i=0; i<cells.length; i++){
					key = cells[i].dataset.key || cells[i].textContent.trim();
					settings[key] = {
						"type":cells[i].parentNode.dataset.type || prompt('Please enter type of parameter "'+key+'" (enum, set or string)', "srting") || "string",
						"value":cells[++i].textContent.trim(),
						"valid":cells[++i].textContent.trim().split(/,\s*/)
					};
			
					if(settings[key]['type']=="enum"){
						if(isNaN(settings[key]['valid'].inArray(settings[key]['value']))){
							alert(settings[key]['value']+" is not valid value");
							return false;
						}
					}else if(settings[key]['type']=="set"){
						settings[key]['value'].split(/,\s*/).forEach(function(value){
							if(isNaN(settings[key]['valid'].inArray(value))){
								alert(value+" is not valid value");
								return false;
							}
						});
					}
				}
				XHR.push({
					addressee:"/actions/settings/sv_module/<?=ARG_2?>",
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