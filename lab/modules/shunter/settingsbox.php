<?php
	$handle = "s:".time();

	$group = "";
	$staff = $mySQL->get("SELECT `CommunityID`,`Name`,`Group` FROM gb_staff CROSS JOIN gb_community USING(CommunityID) ORDER BY `Group`");
	$types = $mySQL->getGroup("SELECT type FROM gb_task_shunter GROUP BY type")['type'];
?>
<div id="<?=$handle?>" class="mount" style="width:420px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.settings-box>div.box-body{
		padding:10px 20px;
	}
	.settings-box fieldset{
		text-align:justify;
		text-align-last:justify;
		border:1px solid #555;
		border-width:1px 0 0 0;

		column-count:2;
		-moz-column-count:2;
		-webkit-column-count:2;
	}
	.settings-box fieldset>legend{
		font-size:14px;
		text-transform:capitalize;
	}
	.settings-box label{
		color:#999;
		line-height:28px;
		display:inline-block;
		text-transform:capitalize;
	}
	.settings-box label>tt{
		font-size:12px;
	}
	.settings-box label>input[type='checkbox']+tt::before{
		content:"\ea53";
		font:14px tools;
	}
	.settings-box label>input[type='checkbox']:checked+tt::before{
		color:gold;
		content:"\ea52";
	}
	.settings-box div.select{
		text-align:right;
	}
	.settings-box div.select::before{
		content:attr(title)": ";
	}
	.settings-box div.select>select{

	}
	</style>
	<form class="box settings-box light-btn-bg" autocomplete="off">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">&#xe995;<?include_once("components/movebox.php")?></div>
		<div class="h-bar" data-translate="textContent">filters</div>
		<div class="box-body black-bg">
			<fieldset><legend data-translate="textContent" class="active-txt">hidden tabs</legend>
				<?$hidden_tabs = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden tabs'], -1, PREG_SPLIT_NO_EMPTY);?>
				<label><input name="tabs" value="statuses" type="checkbox" <?if(in_array("statuses", $hidden_tabs)):?>checked<?endif?> hidden> <tt><?=sprintf("%'.20s","Statuses")?></tt></label>
				<label><input name="tabs" value="types" type="checkbox" <?if(in_array("types", $hidden_tabs)):?>checked<?endif?> hidden> <tt><?=sprintf("%'.20s","Types")?></tt></label>
				<label><input name="tabs" value="performers" type="checkbox" <?if(in_array("performers", $hidden_tabs)):?>checked<?endif?> hidden> <tt><?=sprintf("%'.20s","Performers")?></tt></label>
				<label><input name="tabs" value="stream" type="checkbox" <?if(in_array("stream", $hidden_tabs)):?>checked<?endif?> hidden> <tt><?=sprintf("%'.20s","stream")?></tt></label>
			</fieldset>
			
			<fieldset><legend data-translate="textContent" class="active-txt">hidden statuses</legend>
				<?$hidden_s = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden statuses'], -1, PREG_SPLIT_NO_EMPTY);
				$statuses = $mySQL->getRow("SHOW COLUMNS FROM gb_task_shunter LIKE 'status'")['Type'];
				eval("\$statuses = ".preg_replace("/^enum/", "array", $statuses).";");
				foreach($statuses as $status):?>
				<label>
					<input name="statuses" type="checkbox" <?if(in_array($status, $hidden_s)):?>checked<?endif?> value="<?=$status?>" hidden><tt><?=sprintf("%'.20s",$status)?></tt>
				</label>
				<?endforeach?>
			</fieldset>

			<fieldset><legend data-translate="textContent" class="active-txt">hidden performers</legend>
			<?$staff = $mySQL->get("SELECT CommunityID,Name FROM gb_staff CROSS JOIN gb_community USING(CommunityID)");
			$hidden_u = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden performers'], -1, PREG_SPLIT_NO_EMPTY);
			foreach($staff as $user):?>
				<label><input name="performers" type="checkbox" <?if(in_array($user['CommunityID'], $hidden_u)):?>checked<?endif?> value="<?=$user['CommunityID']?>" hidden><tt><?=sprintf("%'.20s",translite($user['Name']))?></tt></label>
			<?endforeach?>
			</fieldset>

			<fieldset><legend data-translate="textContent" class="red-txt">showed types</legend>
			<?$hidden_t = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['showed types'], -1, PREG_SPLIT_NO_EMPTY);
			$types = $mySQL->getGroup("SELECT type FROM gb_task_shunter GROUP BY type")['type'];
			foreach($types as $type):?>
				<label><input name="types" type="checkbox" <?if(in_array($type, $hidden_t)):?>checked<?endif?> value="<?=$type?>" hidden><tt><?=sprintf("%'.20s",$type)?></tt></label>
			<?endforeach?>
			</fieldset>
		</div>
		<div class="box-footer black-bg" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent">save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			var timeout;
			form.onreset=function(event){form.drop()}
			form.onsubmit=function(){
				let settings = {
					"hidden tabs":[],
					"showed types":[],
					"hidden statuses":[],
					"hidden performers":[]
				}
				form.querySelectorAll("input[name='tabs']:checked").forEach(function(inp){
					settings['hidden tabs'].push(inp.value);
				});
				settings['hidden tabs'] = settings['hidden tabs'].join(", ");
				form.querySelectorAll("input[name='statuses']:checked").forEach(function(inp){
					settings['hidden statuses'].push(inp.value);
				});
				settings['hidden statuses'] = settings['hidden statuses'].join(", ");
				form.querySelectorAll("input[name='performers']:checked").forEach(function(inp){
					settings['hidden performers'].push(inp.value);
				});
				settings['hidden performers'] = settings['hidden performers'].join(", ");
				form.querySelectorAll("input[name='types']:checked").forEach(function(inp){
					settings['showed types'].push(inp.value);
				});
				settings['showed types'] = settings['showed types'].join(", ");
				XHR.push({
					addressee:"/shunter/actions/user-settings",
					body:JSON.encode(settings),
					onsuccess:function(){
						location.reload();
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