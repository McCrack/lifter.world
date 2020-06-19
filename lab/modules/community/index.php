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
		<link rel="stylesheet" type="text/css" href="/modules/community/index.css">
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" async charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" <?if(defined("ARG_2")):?>checked<?endif?> hidden>
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
				</form>
			</aside>
			<header class="h-bar dark-btn-bg">
				<form id="search" class="toolbar">
					<button title="reset" type="reset" class="tool transparent-bg light-txt" data-translate="title">&#xf021;</button>
					<input name="search" pattern="[a-zA-Zа-яА-Я0-9. @-_]{5,}" placeholder="find" data-translate="placeholder" value="<?=$_GET['search']?>" size="30">
					<small>in</small>
					<div class="select">
						<select name="in" class="active-txt">
							<option value="phone">Phones</option>
							<option value="Name" <?=(($_GET['in']==="Name")?"selected":"")?>>Names</option>
							<option value="Email" <?=(($_GET['in']==="Email")?"selected":"")?>>Emails</option>
						</select>
					</div>
					<button type="submit" class="tool transparent-bg light-txt">🔍</button>
					<script>
					(function(form){
						form.onreset=function(event){
							location.search = "";
						}
					})(document.currentScript.parentNode);
					</script>
				</form>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<?if(defined("ARG_2")):?>
				<div class="toolbar t right">
					<label for="screenmode" id="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<?endif?>
			</header>
			<main>
				<table width="100%" cellspacing="0" cellpadding="6" class="page-analytics">
					<thead>
						<tr class="light-btn-bg"><th width="36">ID</th><th>Name</th><th>Phone</th><th>Email</th></tr>
					</thead>
					<tbody onclick="openCitizen(event.target)">
					<?php
					$limit = 50;
					$page = defined("ARG_1") ? ARG_1 : 1;

					if(empty($_GET['search'])){
						$where = "1";
					}elseif($_GET['in']=="phone"){
						$where = $mySQL->parse("Phone = {int}", (INT)$_GET['search']);
					}else $where = $mySQL->parse("{fld} LIKE {str}", $_GET['in'], "%".trim($_GET['search'])."%");

					$list = $mySQL->get("SELECT SQL_CALC_FOUND_ROWS * FROM gb_community WHERE {prp} ORDER BY CommunityID DESC LIMIT {int}, {int}", $where,($page-1)*$limit, $limit);
					$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];

						foreach($list as $row):?>
						<tr align="center" data-id="<?=$row['CommunityID']?>">
							<td><?=$row['CommunityID']?></td>
							<td><?=$row['Name']?></td>
							<td>0<?=$row['Phone']?></td>
							<td><?=$row['Email']?></td>
						</tr>
						<?endforeach?>
						<script>
						(function(tbody, page){
							tbody.querySelectorAll("tr").forEach(function(row){
								row.onclick=function(){
									location.pathname = "/community/"+page+"/"+row.dataset.id;
								}
							});
						})(document.currentScript.parentNode, (location.pathname.split(/\//)[2] || 1))
						</script>
					</tbody>
					<tfoot>
						<tr class="dark-btn-bg">
							<td colspan="2">Total: <b><?=$count?></b></td>
							<td class="pagination" colspan="3" align="right">
							<?php
							$total=ceil($count/$limit);
							$path="community";
							if($total>1):
								if($page>4):
									$j=$page-2;?>
									<a href="/<?=$path?>/1">1</a> ... 
								<?else: $j=1; endif;
								for(; $j<$page; $j++):?><a href="/<?=($path."/".$j)?>"><?=$j?></a><?endfor?>
								<a class="selected"><?=$j?></a>
								<?if($j<$total):?>
									<a href="/<?=($path."/".(++$j))?>"><?=$j?></a>
									<?if(($total-$j)>1):?>
										... <a href="/<?=($path."/".$total)?>"><?=$total?></a>
									<?elseif($j<$total):?>
										<a href="/<?($path."/".$total)?>"><?=$total?></a>
									<?endif;
								endif;
							endif?>		
							</td>
						</tr>
					</tfoot>
				</table>
			</main>
			<?if(defined("ARG_2")): $citizen = $mySQL->getRow("SELECT * FROM gb_community LEFT JOIN gb_staff USING(CommunityID) WHERE CommunityID = {int} LIMIT 1", ARG_2);?>
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="citizen" class="tab light-btn-bg">
						<input name="CommunityID" value="<?=$citizen['CommunityID']?>" type="hidden">
						<div id="general" class="body-bg light-txt">
							<span><small>Name:</small> <?=$citizen['Name']?></span>
							<span><small>Email:</small> <?=$citizen['Email']?></span>
							<span><small>Phone:</small> 0<?=$citizen['Phone']?></span>
							<span><small>Reputation:</small> <?=$citizen['reputation']?></span>
		
							<?if(empty($citizen['UserID'])):?>
							<button name="staff" class="logo-bg">Add to staff</button>
							<?else:?>
							<span><small>Login:</small> <?=$citizen['Login']?></span>
							<span><small>Group:</small> <?=$citizen['Group']?></span>
							<span><small>Departament:</small> <?=$citizen['Departament']?></span>
							<?endif?>
						</div>
						<div class="h-bar light-btn-bg" align="right">
							<span class="left" data-translate="textContent">options</span>
							<button name="options" class="light-btn-bg" data-translate="textContent">save</button>
						</div>
						<div id="options" class="dark-txt"> <!-- Options -->
							<table width="100%" rules="cols" cellspacing="0" cellpadding="4" bordercolor="#CCC">
								<col width="30"><col><col><col width="30">
								<thead>
									<tr class="active-bg"><th colspan="2">Key</th><th colspan="2">Value</th></tr>
								</thead>
								<tbody>
								<?php
								$options = JSON::parse($citizen['options']);
								if(!empty($options)): foreach($options as $key=>$val):?>
									<tr>
										<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
										<td contenteditable="true"><?=$key?></td>
										<td contenteditable="true"><?=$val?></td>
										<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
									</tr>
								<?endforeach; else:?>
									<tr>
										<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
										<td contenteditable="true"></td>
										<td contenteditable="true"></td>
										<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
									</tr>
								<?endif?>
								</tbody>
							</table>
						</div>	<!-- End options -->
						<!--
						<div id="review">
							<div class="h-bar dark-btn-bg" align="right">
								<span class="left" data-translate="textContent">review</span>
								<button name="save" type="submit" class="light-btn-bg" data-translate="textContent">save</button>
							</div>
							<textarea name="review" placeholder="..." autocomplete="off"><?=$citizen['review']?></textarea>
						</div>
						-->
						<script>
						(function(form, e){
							//frame.onload = function(){e = new HTMLDesigner(frame.contentWindow.document);}
							form.options.onclick=function(event){
								event.preventDefault();
								if(form.CommunityID.value){
									XHR.push({
										addressee:"/actions/community/sv_citizen/"+form.CommunityID.value,
										body:JSON.encode((function(){
											var options = {};
											var cells = form.querySelectorAll("#options>table>tbody>tr>td");
											for(var i=0; i<cells.length; i+=2){
												let key = cells[i].textContent.trim().replace(/'|\\/g,"").replace(/"/g,"″");
												if(key) options[key] = cells[i+1].textContent.trim().replace(/'|\\/g,"").replace(/"/g,"″");
											}
											return options;
										})())
									});
								}
							}
							/*
							form.save.onclick=function(event){
								event.preventDefault();
								XHR.push({
									addressee:"/actions/community/ch_citizrn_review/"+form.CommunityID.value,
									body:form.review.value.trim()
								});
							}
							*/
							form.onsubmit=function(event){
								event.preventDefault();
								XHR.push({
									addressee:"/actions/community/ad_to_staff/"+form.CommunityID.value,
									onsuccess:function(response){
										if(isNaN(response)){
											alertBox(response);
										}else new Box(null, "staff/box/"+response);
									}
								});
							}
						})(document.currentScript.parentNode);
						</script>
						<!--~~~~~~~~~-->
					</form>
				</div>
				<form id="rightbar-tabs" class="v-bar r" data-default="manual-tab" autocomplete="off">
					<label title="Citizen" class="tool" for="right-default">👤</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						/*
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){STANDBY.rightbar = event.target.id;}});
						if(STANDBY.rightbar) bar[STANDBY.rightbar].checked = true;
						*/
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
			<?endif?>
		</div>
		<script>
		</script>
	</body>
</html>