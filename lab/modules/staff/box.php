<?php
$tree = $mySQL->getTree("UserID", "Group", "SELECT * FROM gb_staff ORDER BY `Group`");

$groups = $mySQL->getRow("SHOW COLUMNS FROM gb_staff LIKE 'Group'");
eval("\$groups = ".preg_replace("/^enum/", "array", $groups['Type']).";");

$handle = "b:".time();
?>
<div id="<?=$handle?>" class="mount" style="width:540px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.staff-box>div.box-body{
		padding:8px 12px;
	}
	.staff-box>div.box-body>div{
		padding:10px;
	}
	.staff-box>div.box-body>fieldset{
		margin:0;
		padding:5px 10px;
		border-width:0;
		font-size:15px;
		text-align:left;
		box-sizing:border-box;
	}
	.staff-box>div.box-body select{
		height:24px;
		text-align:center;
	}
	.staff-box>div.box-body input,
	.staff-box>div.box-body .select{
		border-radius:3px;
		border:1px solid #CCC;
		box-shadow:inset 0 0 6px -2px rgba(0,0,0, .3);
		background-image:linear-gradient(to top, #FFF, #EEE);
	}
	.staff-box>div.box-body input{
		padding:5px;
		box-sizing:border-box;
	}
	.staff-box>div.box-body>fieldset>input{
		width:100%;
	}
	.staff-box>div.box-footer>button::after{
		content:attr(title);
	}
	</style>
	<form class="box staff-box light-btn-bg" autocomplete="off">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title" onclick="this.form.drop()">✕</button>
		<div class="box-caption logo-bg">&#xe972;<?include_once("components/movebox.php")?></div>
		<div class="h-bar" data-translate="textContent">staff</div>
		<div class="box-body">
			<div align="right">
				<?
				$group = "";
				$staff = $mySQL->get("SELECT `UserID`,`Login`,`Group` FROM gb_staff ORDER BY `Group`");
				?>
				<div class="select left">
					<select name="performer">
						<optgroup label="without group" data-translate="label">
							<option selected value="" data-translate="textContent">not defined</option>
							<?foreach($staff as $user): if($user['Group']!=$group): $group=$user['Group']?>
						</optgroup>
						<optgroup label="<?=$group?>">
							<?endif?>
								<option value="<?=$user['UserID']?>"><?=$user['Login']?></option>
							<?endforeach?>
						</optgroup>
						<script>
						(function(select){
							select.onchange=function(){
								if(select.value) {XHR.push({
									addressee:"/actions/staff/gt_user/"+select.value,
									onsuccess:function(response){
										try{
											response = JSON.parse(response);

											select.form.uid.value = response.UserID;
											select.form.group.value = response.Group;
											select.form.login.value = response.Login;
											select.form.password.value = response.Passwd;
											select.form.userName.value = response.Name;
											select.form.email.value = response.Email;
											select.form.phone.value = response.Phone;
											select.form.departament.value = response.Departament;

											select.form.save.textContent = translate['save'];
											select.form.save.disabled = false;
											select.form.remove.disabled = false;
											
											var settings = JSON.parse(response.settings);

											var tbody = select.form.querySelector(".box-body>table>tbody");
												tbody.innerHTML = "";
											for(var caption in settings){
												let section = doc.create("tr",{class:"section",align:"center",height:"30"});
												[
													
													doc.create("td",{class:"dark-btn-bg",colspan:4}, caption),
													
												].forEach(function(cell){
													section.appendChild(cell);
												});
												tbody.appendChild(section);
												for(var key in settings[caption]){
													let row = doc.create("tr",{align:"left"});
													[
														doc.create("th",{class:"tool",title:"Add Row",onclick:"addRow(this.parentNode)"},"+"),
														doc.create("td",{contenteditable:"true"}, key),
														doc.create("td",{contenteditable:"true"}, settings[caption][key]),
														doc.create("th",{class:"tool",title:"Delete Section",onclick:"deleteRow(this.parentNode)"},"✕"),
													].forEach(function(cell){
														row.appendChild(cell);
													});
													tbody.appendChild(row);
												}
											}
											select.form.align();
										}catch(e){ console.log(e.name+": "+e.message) }
									}
								})}else select.form.reset();
							}
						})(document.currentScript.parentNode);
						</script>
					</select>
				</div>

				ID: <input name="uid" readonly size="2">
				<div class="select">
					<select name="group">
					<?foreach($groups as $group):?>
						<option value="<?=$group?>"><?=$group?></option>
					<?endforeach?>
					</select>
				</div>
			</div>

			<img src="/images/avatars/profile.png" align="left" width="150">
			<fieldset class="left"><legend>Login:</legend>
				<input name="login" pattern="[a-zA-Z0-9_-]+" required>
			</fieldset>
			<fieldset><legend>Password:</legend>
				<input name="password" required placeholder="MD5">
			</fieldset>
			<fieldset class="left"><legend>Name:</legend>
				<input name="userName">
			</fieldset>
			<fieldset><legend>Email:</legend>
				<input name="email" required placeholder="@">
			</fieldset>
			<fieldset class="left"><legend>Phone:</legend>
				<input name="phone" required>
			</fieldset>
			<fieldset><legend>Departament:</legend>
				<input name="departament">
			</fieldset>
			<br>
			<table id="user-settings" width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#999">
				<colgroup><col width="28"><col width="35%"><col width="55%"><col width="28"></colgroup>
				<tbody>

				</tbody>
			</table>
		</div>
		<div class="box-footer" align="right">
			<button type="submit" name="save" class="light-btn-bg" data-translate="textContent" disabled>create</button>
			<button type="reset" name="remove" class="dark-btn-bg" data-translate="textContent" disabled>remove</button>
		</div>
		<script>
		(function(form){
			form.oninput=function(){
				form.save.disabled = false;
			}
			form.password.onchange=function(){
				form.password.value = md5(form.password.value);
			}
			form.onsubmit=function(){
				XHR.push({
					addressee:"/actions/staff/sv_user/"+(form.uid.value || 0),
					body:JSON.encode({
						login:form.login.value.trim(),
						passwd:form.password.value,
						email:form.email.value.trim(),
						phone:form.phone.value.trim(),
						name:form.userName.value.trim(),
						group:form.group.value,
						departament:form.departament.value.trim(),
						settings:(function(settings,section){
							var cells = form.querySelectorAll(".box-body>table>tbody>tr>td");
							for(var i=0; i<cells.length; i++){
								if(cells[i].parentNode.classList.contains("section")){
									section = cells[i].textContent.trim();
									settings[section] = {};
								}else settings[section][cells[i].textContent.trim()] = cells[++i].textContent.trim()
							}
							return settings;
						})({})
					}),
					onsuccess:function(response){
						if(isNaN(response)){
							alert(response)
						}else form.drop();
					}
				});
			}
			form.remove.onclick=function(event){
				event.preventDefault();
				XHR.push({
					addressee:"/actions/staff/rm_user/"+(form.uid.value || 0),
					onsuccess:function(response){form.drop()}
				});
			}
			form.onreset=function(event){
				//event.preventDefault();
				//form.drop();
			}
		})(document.currentScript.parentNode)
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