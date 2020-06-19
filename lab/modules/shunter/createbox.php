<?php
	$handle = "s:".time();

	$group = "";
	$staff = $mySQL->get("SELECT `CommunityID`,`Name`,`Group` FROM gb_staff CROSS JOIN gb_community USING(CommunityID) ORDER BY `Group`");

	$types = array_diff($mySQL->getGroup("SELECT type FROM gb_task_shunter GROUP BY type")['type'], ["article","repost","video","image"]);

	$questionnaire = JSON::load("patterns/json/questionnaire.json");
?>
<div id="<?=$handle?>" class="mount" style="width:680px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.task-box div.box-body{
		padding:10px 10px 0 10px;
	}
	.task-box img{
		width:300px;
		height:160px;
		object-fit:cover;
		position:relative;
	}
	.task-box img::after{
		content:attr(alt);
		top:0;
		left:0;
		width:100%;
		height:100%;
		color:#DDD;
		text-align:center;
		position:absolute;
		font:64px/160px tools;
		background-color:#111;
	}
	.task-box input{
		border:1px solid #AAA;
		box-sizing:border-box;
		background-image:linear-gradient(to top, #FFF, #DDD);
	}
	.task-box>div.box-body>fieldset{
		float:right;
		padding:0 0px;
		border-width:0;
		box-sizing:border-box;
		width:calc(100% - 310px);
	}
	.task-box input[name="image"]{
		width:260px;
		padding:8px;
	}
	.task-box input[name="link"]{
		padding:8px;
		width:calc(100% - 40px);
	}
	.task-box input[name="tasktype"]{
		padding:8px;
	}
	.task-box input[name="rank"]{
		padding:5px;
		width:46px;
	}
	.task-box select[name="performer"]{
		height:36px;
		text-align:center;
		max-width:120px;
	}
	.task-box textarea{
		padding:12px;
		border:1px solid #AAA;
		box-sizing:border-box;
	}
	.task-box textarea[name="header"]{
		width:100%;
		resize:none;
		height:124px;
		font:bold 16px calibri,helvetica,arial;
	}
	.task-box textarea[name="task"]{
		width:300px;
		height:148px;
		margin-top:8px;
		resize:vertical;
		font:15px calibri,helvetica,arial;
	}
	.task-box>div.box-body>table{
		float:right;
		margin:2px 0;
		width:calc(100% - 308px);
	}
	.task-box .select::before{
		color:#00ADF0;
		font-size:14px;
		content:attr(title)": ";
	}

	.questionnaire>fieldset{
		margin:5px 0;
		border:1px solid #BBB;
		background-color:#FAFAFA;
		box-shadow:inset 0 0 7px -3px rgba(0,0,0, .4);
	}
	.questionnaire>fieldset>legend{
		font-size:18px;
		font-weight:bold;
	}
	.questionnaire>fieldset>label{
		margin:5px 0;
		display:block;
		font-size:14px;
		cursor:pointer;
	}
	.questionnaire>fieldset>label>span{
		line-height:1;
		display:inline-block;
		vertical-align:middle;
		width:calc(100% - 40px);
	}
	.questionnaire>fieldset>label>input:checked+span{
		color:#0080C0;
	}
	</style>
	<form class="box task-box light-btn-bg" autocomplete="off">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">&#xe9b5;<?include_once("components/movebox.php")?></div>
		<div class="h-bar">TaskBox</div>
		<div class="box-body">
			<fieldset>
				<input name="link" placeholder="link" data-translate="placeholder">
				<div class="toolbar r">
					<label title="get opengraph data" class="tool" data-translate="title"><input name="prowler" type="checkbox" hidden>&#xe905;</label>
				</div>
				<textarea name="header" placeholder="header" data-translate="placeholder"></textarea>
				<div class="select right" title="performer" data-translate="title">
					<select name="performer">
						<optgroup label="without group" data-translate="label">
							<option selected value="NULL" data-translate="textContent">not defined</option>
						<?foreach($staff as $user): if($user['Group']!=$group): $group=$user['Group']?>
						</optgroup>
						<optgroup label="<?=$group?>">
						<?endif?>
							<option value="<?=$user['CommunityID']?>"><?=$user['Name']?></option>
						<?endforeach?>
						</optgroup>
					</select>
				</div>
				<div class="input-with-select">
					<input name="tasktype" value="article" list="tasktypes" placeholder="type" data-translate="placeholder" size="14" required>
					<datalist id="tasktypes" onmousedown="this.previousElementSibling.value=event.target.textContent">
						<?foreach(["article","repost","video","image"] as $type):?>
						<option><?=$type?></option>
						<?endforeach?>
						<?foreach($types as $type):?>
						<option><?=$type?></option>
						<?endforeach?>
					</datalist>
				</div>
			</fieldset>
			<div class="toolbar l">
				<label title="select image" class="tool" data-translate="title"><input name="imgbox" type="checkbox" hidden>&#xf07c;</label>
				<input name="image" placeholder="Image URL">
			</div>
			<img src="" alt="&#xe94a;" align="left">
			
			<table rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
				<colgroup><col width="28"><col><col width="28"></colgroup>
				<tbody>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
				</tbody>
			</table>

			<textarea name="task" placeholder="task" data-translate="placeholder"></textarea>
			<br clear="both">
			<div class="questionnaire">
				<?foreach($questionnaire['questions'] as $variable=>$obj):?>
				<fieldset><legend><?=$obj['question']?></legend>
					<?foreach($obj['answers'] as $answer=>$val):?>
					<label>
						<input type="radio" name="<?=$variable?>" value="<?=$val?>" <?if(!$val):?>checked<?endif?> required>
						<span><?=$answer?></span>
					</label>
					<?endforeach?>
				</fieldset>
				<?endforeach?>
			</div>
		</div>
		<div class="box-footer" align="right">
			<div class="toolbar left">
				<span data-translate="textContent">rank</span>: <input name="rank" value="0" type="number">
			</div>
			<button type="submit" class="light-btn-bg" data-translate="textContent" name="send">create</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			var timeout,
				formula = "<?=$questionnaire['formula']?>";
			form.onreset=function(event){form.drop()}
			form.onsubmit=function(){
				XHR.push({
					addressee:"/shunter/actions/create",
					body:JSON.encode({
						rank:form.rank.value,
						header:encodeURI(form.header.value.replace(/"/g,"″")),
						image:form.image.value,
						type:form.tasktype.value.trim().translite(),
						performer:form.performer.value.trim(),
						link:form.link.value.trim(),
						task:encodeURI(form.task.value.replace(/"/g,"″")),
						optionset:(function(properties,val){
							form.querySelectorAll(".box-body>table>tbody>tr>td").forEach(function(cell){
								let val = cell.textContent.trim();
								if(val) properties.push(val);
							});
							return properties;
						})([])
					}),
					onsuccess:function(response){
						/*
						var path = location.pathname.split(/\//);
							path[1] = "shunter";
							path[2] = "<?=ARG_2?>";
							path[3] = response;
						location.href = path.join("/");
						*/
						location.reload();
					}
				});
			}
			form.image.oninput=function(){
				form.querySelector(".box-body>img").src = form.image.value;
			}
			form.imgbox.onchange=function(event){
				new Box(null, "boxfather/imagebox",function(frm){
					form.image.value =
					form.querySelector(".box-body>img").src = frm.querySelector("iframe").contentWindow.getSelectedURLs();
				});
			}
			form.prowler.onchange=function(event){
				XHR.push({
					addressee:"/shunter/actions/prowler",
					body:form.link.value,
					onsuccess:function(response){
						try{
							response = JSON.parse(response);

							form.header.value = response['og:title'];
							form.image.value =
							form.querySelector(".box-body>img").src = response['og:image'];
						}catch(e){alert(response)}
					}
				});
			}
			form.onchange=function(event){
				if(event.target.type!="radio") return true;

				var DATA = {};
				form.querySelectorAll(".questionnaire>fieldset>label>input:checked").forEach(function(inp){
					DATA[inp.name] = parseFloat(inp.value);
				});
				try{
					var expression = formula.replace(/[a-z]+/gi, function(variable){
						return DATA[variable] || 0;
					});
					eval("form.rank.value = "+expression);
				}catch(e){ console.log(e.name+": "+e.message) }
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