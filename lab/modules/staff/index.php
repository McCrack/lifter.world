<?php

$tree = $mySQL->getTree( "UserID", "Group", "SELECT * FROM gb_staff ORDER BY `Group`");

$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"settings"=>in_array(USER_GROUP, $settings)
];
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/staff/index.css">
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden disabled>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden disabled="">
			<nav class="h-bar white-txt t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="staff" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="staff-tree" class="tab body-bg light-txt">
						<div class="h-bar white-txt" data-translate="textContent">staff</div>
						<div class="root">
						<?foreach($tree as $key=>$row):?>
							<a><?=$key?></a>
							<div class="root">
							<?foreach($row as $field=>$val):?>
								<a <?if($val['UserID']==ARG_1):?>class="selected"<?endif?> href="/staff/<?=$val['UserID']?>">[<?=$val['UserID']?>] <?=$val['Login']?></a>
							<?endforeach?>
							</div>
						<?endforeach?>
						</div>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="staff" class="tool" for="staff" data-translate="title">&#xe972;</label>
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
			<header class="h-bar r light-txt">
				<div class="toolbar right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main>
				<hr><hr><hr><hr>
				<form id="user-form" onsubmit="return saveUser(this)" autocopmlete="off">
					<?if(defined("ARG_1")): $user = $mySQL->getRow("SELECT * FROM gb_staff LEFT JOIN gb_community USING(CommunityID) WHERE UserID={int} LIMIT 1", ARG_1); endif?>
					<div>
						<div class="v-bar-bg">
							ID: <input name="uid" class="logo-bg" readonly value="<?=$user['UserID']?>" size="2"><?if(defined("ARG_1")):?><a href="/staff">✕</a><?endif?>
						</div>
						<div>
							<div class="select" title="Group:">
								<select name="group" form="user-form" autocopmlete="off" class="active-txt">
								<?
								$groups = $mySQL->getRow("SHOW COLUMNS FROM gb_staff LIKE 'Group'")['Type'];
								eval("\$groups = ".preg_replace("/^enum/", "array", $groups).";");
								foreach($groups as $group):?>
									<option <?if($group==$user['Group']):?>selected<?endif?> value="<?=$group?>"><?=$group?></option>
								<?endforeach?>
								</select>
							</div>
						</div>
					</div>
					<div>
						<img src="/images/avatars/profile.png" align="left" vspace="10">
						<fieldset class="left"><legend>Login:</legend>
							<input name="login" value="<?=$user['Login']?>" pattern="[a-zA-Z0-9_-]+" required>
						</fieldset>
						<fieldset><legend>Password:</legend>
							<input name="password" value="<?=$user['Passwd']?>" required placeholder="MD5">
						</fieldset>
						<fieldset class="left"><legend>Name:</legend>
							<input name="userName" value="<?=$user['Name']?>">
						</fieldset>
						<fieldset><legend>Email:</legend>
							<input name="email" value="<?=$user['Email']?>" required placeholder="@">
						</fieldset>
						<fieldset class="left"><legend>Phone:</legend>
							<input name="phone" value="0<?=$user['Phone']?>" required>
						</fieldset>
						<fieldset><legend>Departament:</legend>
							<input name="departament" value="<?=$user['Departament']?>">
						</fieldset>

						<table id="user-settings" width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#999">
							<colgroup><col width="28"><col width="35%"><col width="55%"><col width="28"></colgroup>
							<thead>
								<tr align="center" class="dark-btn-bg" height="30">
									<th class="tool logo-bg" title="Add Section" onclick="addSection(this.parent(3))">+</th>
									<th>Key</th>
									<th colspan="2">Value</th>
								</tr>
							</thead>
							<tbody>
							<?foreach(JSON::parse($user['settings']) as $section=>$settings):?>
								<tr align="center" class="section" height="30">
									<th class="tool" title="add row" data-translate="title" onclick="addSettingsRow(this.parentNode)">+</th>
									<td colspan="2" class="active-bg"><?=$section?></td>
									<th class="tool" title="Delete Section" onclick="deleteSection(this.parentNode)">✕</th>
								</tr>
								<?foreach($settings as $key=>$val):?>
								<tr align="left">
									<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
									<td align="center" contenteditable="true"><?=$key?></td>
									<td contenteditable="true"><?=$val?></td>
									<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
								</tr>
								<?endforeach;
							endforeach?>
							</tbody>
						</table>
					</div>
					<div align="right" class="box-footer">
						<?if(defined("ARG_1")):?>
							<button type="submit" name="save" class="light-btn-bg" data-translate="textContent" disabled>save</button>
							<button type="reset" class="dark-btn-bg" data-translate="textContent">remove</button>
						<?else:?>
							<button type="submit" name="save" class="light-btn-bg" data-translate="textContent" disabled>create</button>
						<?endif?>
					</div>
					<script>
					(function(form){
						form.oninput=function(){
							form.save.disabled = false;
						}
						form.onsubmit=function(event){
							event.preventDefault();
							XHR.push({
								"addressee":"/actions/staff/sv_user/"+(form.uid.value || 0),
								"body":JSON.stringify({
									login:form.login.value.trim(),
									passwd:form.password.value,
									email:form.email.value.trim(),
									phone:form.phone.value.trim(),
									name:form.userName.value.trim(),
									group:form.group.value,
									departament:form.departament.value.trim(),
									settings:(function(settings,section){
										var cells = form.querySelectorAll("#user-settings>tbody>tr>td");
										for(var i=0; i<cells.length; i++){
											if(cells[i].parentNode.classList.contains("section")){
												section = cells[i].textContent.trim();
												settings[section] = {};
											}else settings[section][cells[i].textContent.trim()] = cells[++i].textContent.trim()
										}
										return settings;
									})({})
								}),
								"onsuccess":function(response){
									if(isNaN(response)){
										alert(response)
									}else if(!form.uid.value){
										location.pathname = "/staff/"+response;
									}else form.save.disabled = true;
								}
							});
						}
						form.onreset=function(event){
							event.preventDefault();
							XHR.push({
								addressee:"/actions/staff/rm_user/"+(form.uid.value || 0),
								onsuccess:function(response){
									isNaN(response) ? alert(response) : location.pathname="/staff";
								}
							});
						}
						form.password.onchange=function(){
							form.password.value = md5(form.password.value);
						}
					})(document.currentScript.parentNode)
					</script>
					<script>
					function addSection(table){
						var tbody = table.querySelector("tbody");
						let section = doc.create("tr",{class:"section",align:"center",height:"30"});
						[
							doc.create("th",{class:"tool",title:"Add Row",onclick:"addSettingsRow(this.parentNode)"},"+"),
							doc.create("td",{class:"dark-btn-bg",colspan:2,contenteditable:"true"}),
							doc.create("th",{class:"tool",title:"Delete Section",onclick:"deleteSection(this.parentNode)"},"✕"),
						].forEach(function(cell){
							section.appendChild(cell);
						});
						tbody.appendChild(section);
						let row = doc.create("tr",{align:"left"});
						[
							doc.create("th",{class:"tool",title:"Add Row",onclick:"addRow(this.parentNode)"},"+"),
							doc.create("td",{contenteditable:"true"}),
							doc.create("td",{contenteditable:"true"}),
							doc.create("th",{class:"tool",title:"Delete Section",onclick:"deleteRow(this.parentNode)"},"✕"),
						].forEach(function(cell){
							row.appendChild(cell);
						});
						tbody.appendChild(row);
					}
					function addSettingsRow(section){
						let row = doc.create("tr",{align:"left"});
						[
							doc.create("th",{class:"tool",title:"Add Row",onclick:"addRow(this.parentNode)"},"+"),
							doc.create("td",{contenteditable:"true"}),
							doc.create("td",{contenteditable:"true"}),
							doc.create("th",{class:"tool",title:"Delete Section",onclick:"deleteRow(this.parentNode)"},"✕"),
						].forEach(function(cell){
							row.appendChild(cell);
						});
						section.insertAfter(row);
					}
					function deleteSection(section){
						row = section.nextElementSibling;
						while(row && !row.classList.contains("section")){
							row.parentNode.removeChild(row);
							row = section.nextElementSibling;
						}
						section.parentNode.removeChild(section);
					}
					</script>
				</form>
			</main>
		</div>
	</body>
</html>