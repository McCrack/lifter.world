<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];

$cng = new config("../".BASE_FOLDER."/".$config->{"config file"});

defined("ARG_1") or define("ARG_1", "2-1");

$filters = explode("-", ARG_1);
$where = [1];
foreach(["paid", "status"] as $i=>$key){
	if($filters[$i]>0) $where[] = $key." & ".$filters[$i];
}

$limit = 50;
$page = defined("ARG_2") ? ARG_2 : 1;
define("ORDER_ID", defined("ARG_3") ? ARG_3 : false);

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/orders/index.css">
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=store" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" <?if(ORDER_ID):?>checked<?endif?> hidden>
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

					<input id="filters-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<form id="filters" class="tab body-bg light-txt">
						<div class="h-bar" data-translate="textContent">filters</div>
						<fieldset><legend data-translate="textContent">has paid</legend>
							<label><input type="checkbox" name="paid" value="1" <?=(($filters[0]&1)?'checked':'')?>> <span data-translate="textContent">NO</span></label>
							<label><input type="checkbox" name="paid" value="2" <?=(($filters[0]&2)?'checked':'')?>> <span data-translate="textContent">YES</span></label>
						</fieldset>
						<fieldset><legend data-translate="textContent">status</legend>
							<label><input type="checkbox" name="status" value="1" <?=(($filters[1]&1)?'checked':'')?>> <span data-translate="textContent">new</span></label>
							<label><input type="checkbox" name="status" value="2" <?=(($filters[1]&2)?'checked':'')?>> <span data-translate="textContent">accepted</span></label>
							<label><input type="checkbox" name="status" value="4" <?=(($filters[1]&4)?'checked':'')?>> <span data-translate="textContent">shipped</span></label>
							<label><input type="checkbox" name="status" value="8" <?=(($filters[1]&8)?'checked':'')?>> <span data-translate="textContent">canceled</span></label>
						</fieldset>
						<script>
						(function(form){
							var fset = form.querySelectorAll("fieldset");
							form.onchange=function(){
								var filters = [0,0];
								fset.forEach(function(set, i){
									set.querySelectorAll("input").forEach(function(inp){
										filters[i] ^= Number(inp.checked) * inp.value;
									});
								});
								let path = location.pathname.split(/\//);
									path[2] = filters.join("-");
									path[3] = 1;
								location.pathname = path.join("/");
							}
						})(document.currentScript.parentNode);
						</script>
					</form>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="filters" class="tool" for="filters-tab" data-translate="title">&#xea52;</label>
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
			<header class="h-bar light-txt">
				<div class="toolbar l">
					<label title="print" class="tool" data-translate="title" onclick="printOrdersList()">🖨</label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<?if(ORDER_ID):?>
				<div class="toolbar t right">
					<label for="screenmode" id="screenmode-btn" title="screen mode" data-translate="title"></label>
				</div>
				<?endif?>
			</header>
			<main>
				<table width="100%" cellspacing="0" cellpadding="5" class="page-analytics">
					<thead>
						<tr class="dark-btn-bg">
							<th>ID</th>
							<th data-translate="textContent">status</th>
							<th data-translate="textContent">price</th>
							<th data-translate="textContent">amount</th>
							<th data-translate="textContent">has paid</th>
							<th data-translate="textContent">manager</th>
							<th data-translate="textContent">created</th>
							<th data-translate="textContent">modified</th>
						</tr>
					</thead>
					<tbody>
					<?$orders = $mySQL->get("SELECT SQL_CALC_FOUND_ROWS * FROM gb_orders LEFT JOIN gb_staff USING(UserID) WHERE ".implode(" AND ", $where)." ORDER BY OrderID DESC LIMIT {int}, {int}", ($page-1)*$limit, $limit);
					$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];
					foreach($orders as $order):?>
						<tr align="center" class="<?=$order['status']?>" data-id="<?=$order['OrderID']?>">
							<td><?=$order['OrderID']?></td>
							<td data-translate="textContent"><?=$order['status']?></td>
							<td><?=$order['price']?></td>
							<td><?=$order['amount']?></td>
							<td data-translate="textContent" class="<?if($order['paid']=="YES"):?>active-txt<?else:?>red-txt<?endif?>"><?=$order['paid']?></td>
							<td><?=$order['Login']?></td>
							<td><?=date("d M, H:i",$order['created'])?></td>
							<td><?=date("d M, H:i",$order['modified'])?></td>
						</tr>
					<?endforeach?>
						<script>
						(function(tbody){
							tbody.querySelectorAll("tr").forEach(function(row){
								row.onclick=function(){
									let path = location.pathname.split(/\//)
										path[2] = path[2] || "<?=ARG_1?>";
										path[3] = path[3] || 1;
										path[4] = row.dataset.id;
									location.pathname = path.join("/");
								}
							});
						})(document.currentScript.parentNode)
						</script>
					</tbody>
					<tfoot>
						<tr>
							<td class="pagination dark-btn-bg h-bar" colspan="8" align="right">
							<?php
							$total=ceil($count/$limit);
							$path="orders/".ARG_1;
							if($total>1):
								if($page>4):
									$j=$page-2;?>
									<a href="/<?=$path?>/1">1</a> ... 
								<?else: $j=1; endif;
								for(; $j<$page; $j++):?><a href="/<?=($path."/".$j)?>"><?=$j?></a><?endfor?>
								<span class="active-txt"><?=$j?></span>
								<?if($j<$total):?>
									<a href="/<?=($path."/".(++$j))?>"><?=$j?></a>
									<?if(($total-$j)>1):?>
										... <a href="/<?=($path."/".$total)?>"><?=$total?></a>
									<?elseif($j<$total):?>
										<a href="/<?($path."/".$total)?>"><?=$total?></a>
									<?endif;
								endif;
							endif
							?>		
							</td>
						</tr>
					</tfoot>
				</table>
			</main>
			<?if(ORDER_ID):
			$order = $mySQL->getRow("SELECT * FROM gb_orders LEFT JOIN gb_community USING(CommunityID) WHERE OrderID={int} LIMIT 1", ORDER_ID);
			$manager = $mySQL->getRow("SELECT Login,Name FROM gb_staff CROSS JOIN gb_community USING(CommunityID) WHERE UserID={int} LIMIT 1", $order['UserID'])?>
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="order" class="tab light-btn-bg" autocomplete="off">
						<div class="h-bar light-btn-bg">
							ID: <input name="OrderID" value="<?=$order['OrderID']?>" type="number">
							<div class="toolbar right">
								<div class="select active-txt" title="status" data-translate="title">
									<select name="status">
										<?foreach(["new","accepted","shipped","canceled"] as $status):?>
										<option <?if($status==$order['status']):?>selected<?endif?> value="<?=$status?>" data-translate="textContent"><?=$status?></option>
										<?endforeach?>
									</select>
								</div>
							</div>
						</div>
						<div class="h-bar dark-btn-bg">
							<span data-translate="textContent">sum</span>:
							<input name="price" value="<?=$order['price']?>" placeholder="0.00" readonly>
							●
							<input name="amount" value="<?=$order['amount']?>" type="number" min="1" title="amount" data-translate="title">
							<div class="toolbar right">
								<label><input name="paid" <?if($order['paid']=="YES"):?>checked<?endif?> type="checkbox" hidden><span data-translate="textContent">has paid</span></label>
							</div>
						</div>
						<fieldset id="client" class="body-bg light-txt">
							<div><span class="active-txt" data-translate="textContent">name</span>:<input name="name" type="text" value="<?=$order['Name']?>"></div>
							<div><span class="active-txt" data-translate="textContent">phone</span>:<input name="phone" type="tel" value="<?=$order['Phone']?>"></div>
							<div><span class="active-txt">E-Mail</span>:<input value="<?=$order['Email']?>" name="email" type="email"></div>
						</fieldset>
						<fieldset id="delivery" class="body-bg light-txt">
						<?foreach(JSON::parse($order['delivery']) as $key=>$val):?>
							<div><span class="active-txt" data-translate="textContent"><?=$key?></span>:<input name="<?=$key?>" value="<?=$val?>"></div>
						<?endforeach?>
						</fieldset>
						<div class="h-bar dark-btn-bg">
							<span class="tool">&#xf02b;</span> <span class="green-txt" data-translate="textContent">message</span>
							<div class="toolbar right">
								<small data-translate="textContent">manager</small>: <small><?=$manager['Name']?></small> <small class="active-txt">[ <?=$manager['Login']?> ]</small>
							</div>
						</div>
						<fieldset id="delivery" class="body-bg">
							<textarea name="message" readonly placeholder="..." class="green-txt"><?=$order['message']?></textarea>
						</fieldset>
						<div class="h-bar light-btn-bg"><span class="tool">&#xf0c8;</span> Log</div>
						<div id="log" class="black-bg">
							<div>
								<?=$order['log']?>
							</div>
							<textarea name="comment" placeholder="..."></textarea>
							<button type="submit" class="active-bg">&#xe163;</button>
						</div>
						<script>
						(function(form){
							var timeout,
								log = form.querySelector("#log>div");
							form.onsubmit=function(event){
								event.preventDefault();
								XHR.push({
									addressee:"/orders/actions/add-comment/"+ORDERID,
									body:form.comment.value.trim().replace(/"/g,"″"),
									onsuccess:function(response){
										log.innerHTML = response;
										form.comment.value = "";
									}
								});
							}
							form.OrderID.oninput=function(){
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									var path = location.pathname.split(/\//);
										path[4] = form.OrderID.value;
										location.pathname = path.join("/")
								}, 500);
							}
							form.status.onchange=function(){
								XHR.push({
									addressee:"/orders/actions/change-status/"+ORDERID+"/"+form.status.value,
									onsuccess:function(response){
										log.innerHTML = response;
									}
								});
							}
							form.amount.oninput=function(){
								form.price.value = ( (BP + (PF * 2)) + ((form.amount.value - 1) * BP) + ((form.amount.value>1) ? PF : 0) ).toFixed(2);
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									XHR.push({
										addressee:"/orders/actions/change-amount/"+ORDERID+"/"+form.amount.value,
										onsuccess:function(response){
											log.innerHTML = response;
										}
									});
								}, 1000);
							}
							form.paid.onchange=function(){
								XHR.push({
									addressee:"/orders/actions/change-paid/"+ORDERID+"/"+(form.paid.checked ? "YES" : "NO"),
									onsuccess:function(response){
										log.innerHTML = response;
									}
								});
							}
							form.city.onchange=
							form.country.onchange=
							form.tracking.onchange=
							form.warehouse.onchange=function(){
								XHR.push({
									addressee:"/orders/actions/change-delivery-data/"+ORDERID,
									body:JSON.encode({
										tracking:form.tracking.value.trim(),
										country:form.country.value.trim().replace(/"/g,"″"),
										city:form.city.value.trim().replace(/"/g,"″"),
										warehouse:form.warehouse.value.trim().replace(/"/g,"″")
									}),
									onsuccess:function(response){
										log.innerHTML = response;
									}
								});
							}
						})(document.currentScript.parentNode);
						</script>
						<!--~~~~~~~~~-->
					</form>
				</div>
				<form id="rightbar-tabs" class="v-bar r" data-default="manual-tab" autocomplete="off">
					<label title="order" class="tool" for="right-default" data-translate="title">&#xe9d3;</label>
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
		var ORDERID = <?=(INT)ORDER_ID?>;
		var BP = <?=$cng->{"base price"}?>;
		var PF = <?=$cng->{"price factor"}?>;
		function printOrdersList(){
			reauth()
			window.open("/orders/blank/", "Orders List", "dialogWidth:796px;dialogHeight:550px;center:on;resizable:off;scroll:on;");
		}
		</script>
	</body>
</html>