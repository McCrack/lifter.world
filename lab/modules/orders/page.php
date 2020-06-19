<?php

	$brancher->auth() or die(include_once("modules/auth/page.php"));

	$sorted = PAGE ? PAGE : "OrderID";
	
	$limit = 50;
	$page = SUBPAGE ? SUBPAGE : 1;
	$where = [1];
	
	if(isSet($_GET['filters'])){
		$filters = explode("-", $_GET['filters']);
	}else $filters = [0,0,0];

	$fields = ["type","status","payment"];
	foreach($filters as $key=>$val){
		if($val) $where[] = $fields[$key]." & ".$val;
	}
	
	$feed = $mySQL->query("SELECT SQL_CALC_FOUND_ROWS * FROM gb_orders LEFT JOIN gb_staff USING(UserID) WHERE ".implode(" AND ", $where)." ORDER BY ".$sorted." DESC LIMIT ".(($page-1)*$limit).", ".$limit);

	$count = reset($mySQL->single_row("SELECT FOUND_ROWS()"));

	$paid = ["<span class='red'>NO</span>","<span class='green'>YES</span>"];

	if(PARAMETER){
		$order = $mySQL->single_row("
		SELECT * FROM gb_orders
		CROSS JOIN gb_community USING(CommunityID)
		WHERE OrderID=".PARAMETER."
		LIMIT 1");
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.orders</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/orders/tpl/orders.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/orders/tpl/orders.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=store" async charset="utf-8"></script>
		<script>
		var SORTED = "<?=$sorted?>";
		var ORDERID = <?=(INT)$order['OrderID']?>;
		window.onbeforeunload = reauth;
		</script>
	</head>
	<body class="<?=$standby[SECTION]['bodymode']?>">
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" title="modules" data-translate="title" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" title="filters" data-translate="title" data-tab="filters">&#xea52;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root"><?=$brancher->tree($brancher->register)?></div>
				</div>
				<form class="tab left" id="filters" autocomplete="off">
					<div class="caption" data-translate="textContent">filters</div>
					<fieldset><legend data-translate="textContent">type</legend>
						<label><input type="checkbox" name="otype" value="1" <?=(($filters[0]&1)?'checked':'')?>> Deal</label>
						<label><input type="checkbox" name="otype" value="2" <?=(($filters[0]&2)?'checked':'')?>> Delivery</label>
						<label><input type="checkbox" name="otype" value="4" <?=(($filters[0]&4)?'checked':'')?>> Reserved</label>
					</fieldset>
					<fieldset><legend data-translate="textContent">status</legend>
						<label><input type="checkbox" name="status" value="1" <?=(($filters[1]&1)?'checked':'')?>> New</label>
						<label><input type="checkbox" name="status" value="2" <?=(($filters[1]&2)?'checked':'')?>> Accepted</label>
						<label><input type="checkbox" name="status" value="4" <?=(($filters[1]&4)?'checked':'')?>> Canceled</label>
					</fieldset>
					<fieldset><legend data-translate="textContent">payment</legend>
						<label><input type="checkbox" name="payment" value="1" <?=(($filters[2]&1)?'checked':'')?>> <span data-translate="textContent">cash</span></label>
						<label><input type="checkbox" name="payment" value="2" <?=(($filters[2]&2)?'checked':'')?>> <span data-translate="textContent">card</span></label>
						<label><input type="checkbox" name="payment" value="4" <?=(($filters[2]&4)?'checked':'')?>> <span data-translate="textContent">on delivery</span></label>
					</fieldset>
					<script>
					(function(form){
						var fset = form.querySelectorAll("fieldset");
						form.onchange=function(){
							var filters = [0,0,0];
							fset.forEach(function(set, i){
								set.querySelectorAll("input").forEach(function(inp){
									filters[i] ^= Number(inp.checked) * inp.value;
								});
							});
							location.search = "filters="+filters.join("-");
						}
					})(document.currentScript.parentNode);
					</script>
				</form>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="create order" onclick="createOrderBox()">&#xf15b;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('store')">&#xf013;</span>
			</div>
		</div>
		<div id="environment">
			<table width="100%" rules="rows" bordercolor="#CCC" cellspacing="0" cellpadding="5">
				<thead>
					<tr class="caption">
						<th><a href="/orders/orderid">ID</a></th>
						<th><a href="/orders/type" data-translate="textContent">type</a></th>
						<th><a href="/orders/status" data-translate="textContent">status</a></th>
						<th><a href="/orders/payment" data-translate="textContent">payment</a></th>
						<th><a data-translate="textContent">paid</a></th>
						<th><a href="/orders/manager" data-translate="textContent">manager</a></th>
						<th><a href="/orders/created" data-translate="textContent">created</a></th>
						<th><a href="/orders/modified" data-translate="textContent">modified</a></th>
						<th></th>
					</tr>
				</thead>
				<tbody onclick="openOrder(event)">
				<?foreach($feed as $row):
					$discount = 0;
					if($row['discount']){
						$discount = floor($row['price'] * $row['discount'] / 100);
					}
					$price = $row['price'] - $discount;
					?>
					<tr align="center" class="<?=$row['status']?>" data-id="<?=$row['OrderID']?>">
						<td><?=$row['OrderID']?></td>
						<td><?=$row['type']?></td>
						<td data-translate="textContent"><?=$row['status']?></td>
						<td align="left"><span data-translate="textContent"><?=$row['payment']?></span>: <?=$price?></td>
						<td><?=$paid[$row['paid']]?></td>
						<td><?=$row['Login']?></td>
						<td><?=date("d M, H:i",$row['created'])?></td>
						<td><?=date("d M, H:i",$row['modified'])?></td>
						<td align="left">
							<img src="/modules/orders/tpl/<?=$row['payment']?>.png" title="<?=$row['payment']?>" data-translate="title">
							<?if($row['type']=="delivery"):?>
							<img src="/modules/orders/tpl/delivery.png" title="delivery" data-translate="title">
							<?endif?>
							<?if($row['discount']):?>
							<img src="/modules/orders/tpl/discount.png" title="discount" data-translate="title">
							<?endif?>
						</td>
					</tr>
				<?endforeach?>
				</tbody>
				<tfoot>
					<tr>
						<td id="pagination" colspan="9" align="right" class="caption">
						<?php
						$total=ceil($count/$limit);	// Total pages
						$path="orders/".$sorted;
						if($total>1){
							if($page>4){
								$j=$page-2;
								$pagination="<a href='/".$path."/1'>1</a> ... ";
							}else $j=1;
							for(; $j<$page; $j++) $pagination.="<a href='/".$path."/".$j."'>".$j."</a>";					
							$pagination.="<a class='selected'>".$j."</a>";
							if($j<$total){
								$pagination.="<a href='/".$path."/".(++$j)."'>".$j."</a>";
								if(($total-$j)>1){
									$pagination.=" ... <a href='/".$path."/".$total."'>".$total."</a>";
								}elseif($j<$total){
									$pagination.="<a href='/".$path."/".$total."'>".$total."</a>";
								}
							}
						}
						print $pagination;
						?>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div id="rightbar">
			<aside class="tabbar right">
				<div class="toolbar">
					<span data-translate="title" title="order" class="tool">&#xf05e;</span>
				</div>
			</aside>
			<div id="order" class="tab" style="display:block">
				<?php
				if(PARAMETER):
				$manager = $mySQL->single_row("
				SELECT * FROM gb_staff
				CROSS JOIN gb_community USING(CommunityID)
				WHERE UserID=".$order['UserID']."
				LIMIT 1")['Name']?>
				<form class="caption">
					OrderID: <input class="tool" name="orderid" size="3" placeholder="0" value="<?=$order['OrderID']?>">
					<div class="toolbar right">
						<!--
						<span class="tool">&#xf021;</span>
						<span class="tool">&#xe962;</span>
						-->
						<span class="tool" onclick="printOrder()"></span>
					</div>
					<script>
					(function(form){
						form.onsubmit = form.onchange = function(event){
							event.preventDefault();
							var path = window.location.pathname.split(/\//);
							path[2] = path[2] || "orderid";
							path[3] = path[3] || 1;
							path[4] = form.orderid.value;
							window.location.pathname = path.join("/")
						}
					})(document.currentScript.parentNode)
					</script>
				</form>
				<fieldset id="client"><legend class="gold"><?=$order['Name']?></legend>
					<div align="right">
						<span class="left tool"><small class="grey">tel:</small><?=$order['Phone']?></span>
						<span class="tool"><?=$order['Email']?></span>
					</div>
				</fieldset>
				<fieldset>
					<div align="right">
						<span class="left grey"><span data-translate="textContent">created</span>: <?=date("d M, H:i",$order['created'])?></span>
						<span class="gold"><span data-translate="textContent">modified</span>: <?=date("d M, H:i",$order['modified'])?></span>
					</div>
					<div class="right" align="right">
						<span class="grey" data-translate="textContent">manager</span>: <span><?=$manager?></span><br>
						
						<label>
							<?if($order['status']=="accepted"):?>
							<input type="checkbox" id="paid" name="paid" <?=($order['paid']?"checked disabled":"")?> autocomplete="off" hidden><span class="grey" data-translate="textContent">paid</span>
							<?else:?>
							<input type="checkbox" id="paid" name="paid" disabled hidden autocomplete="off"><span class="grey" data-translate="textContent">paid</span>
							<?endif?>
							<script>
							(function(label){
								label.onchange = function(){
									var inp = label.first(),
										message = inp.checked ? "Списать товар со склада?" : "Вернуть товар на склад?";
									inp.checked ^= 1;
									confirmBox(message, function(){
										inp.checked ^= 1;
										XHR.push({
											"addressee":"/orders/actions/paid/"+ORDERID+"/"+Number(inp.checked),
											"onsuccess":function(response){
												document.querySelector("#log").innerHTML = response;
											}
										});
										document.querySelector("#status>option[value='canceled']").disabled = inp.checked;
									});
								}
							})(document.currentScript.parentNode)
							</script>
						</label>
					</div>
					<span class="grey" data-translate="textContent">payment</span>: <span data-translate="textContent"><?=$order['payment']?></span>
					<br>
					<span class="grey" data-translate="textContent">type</span>: <span class="gold"><?=$order['type']?></span>
				</fieldset>
				<?if($order['type']=="delivery"):?> 
				<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#999">
					<colgroup><col width="30"><col><col width="50%"><col width="30"></colgroup>
					<tbody>
					<?foreach(JSON::parse($order['delivery']) as $key=>$val):?>
					<tr>
						<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
						<td data-translate="textContent"><?=$key?></td>
						<td onfocus="focusCell(this)" contenteditable="true"><?=$val?></td>
						<th bgcolor="white"><span title="delete row" data-translate="title" class="tool red" onclick="deleteRow(this)">&#xe907;</span></th>
					</tr>
					<?endforeach?>
					</tbody>
				</table>
				<?endif?>
				<div class="panel" align="right">
					<form class="toolbar left" autocomplete="off">
						<?php
							$discount = 0;
							if($order['discount']){
								$discount = floor($order['price'] * $order['discount'] / 100);
							}
							$price = $order['price'] - $discount;
						?>
						<label class="tool" data-translate="textContent">price</label>
						<input id="fullprice" name="fullprice" type="hidden" value="<?=$order['price']?>">
						<input id="price" name="price" class="tool" value="<?=$price?>" size="4" readonly>
						<label class="tool" data-translate="textContent">discount</label>
						<input id="discount" name="discount" class="tool" list="discounts" value="<?=$order['discount']?>%" size="5" placeholder="%">
						<datalist id="discounts">
							<option>3%</option>
							<option>5%</option>
							<option>7%</option>
							<option>10%</option>
							<option>15%</option>
							<option>20%</option>
							<option>25%</option>
							<option>30%</option>
						</datalist>
						<script>
						(function(form){
							form.onsubmit = form.onchange=function(event){
								event.preventDefault();
								var discount = parseInt(form.discount.value) || 0;
								form.discount.value = discount+"%";
								form.price.value = form.fullprice.value - Math.floor(form.fullprice.value * discount / 100);
								XHR.push({
									"addressee":"/orders/actions/discount/"+ORDERID+"/"+discount,
									"onsuccess":function(response){
										document.querySelector("#log").innerHTML = response;
									}
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
					<div class="toolbar">
						<label class="tool" data-translate="textContent">status</label> 
						<select class="tool" name="status" id="status" autocomplete="off" data-status="<?=$order['status']?>">
							<option selected value="<?=$order['status']?>" data-translate="textContent"><?=$order['status']?></option>
							<?foreach(["accepted","canceled"] as $status)
								if($order['paid']>0): break; elseif($status==$order['status']): continue; else:?>
							<option value="<?=$status?>" data-translate="textContent"><?=$status?></option>
							<?endif?>
							<script>
							(function(select){
								select.onchange = function(){
									var newstatus = select.value;
									var message = (newstatus=="accepted") ? "Переместить товар в резерв?" : "Вернуть товар на основной склад?";	
									select.value = select.dataset.status;
									confirmBox(message, function(){
										select.value = newstatus;
										XHR.push({
											"addressee":"/orders/actions/status/"+ORDERID+"/"+select.value,
											"onsuccess":function(response){
												document.querySelector("#log").innerHTML = response;
												document.querySelector("#paid").disabled = (newstatus=="canceled");
											}
										});
									});
								}
							})(document.currentScript.parentNode)
							</script>
						</select>
						<?if(!$order['paid']):?><label class="tool" onclick="modalBox('{}', 'orders/additembox/<?=$order['OrderID']?>')" title="add item" data-translate="title">&#xe901;</label><?endif?>
					</div>
				</div>
				<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#999">
					<colgroup><col width="68"><col><col></colgroup>
					<tbody id="orderbody">
					<?php
					$IDs = [];
					$cart = JSON::parse($order['body']);
					foreach($cart as $id=>$amount) $IDs[] = $id;
					$cng = new config("../".$config->{"base folder"}."/config.init");
					
					$body = $mySQL->query("
					SELECT
						PageID,
						gb_items.ItemID AS ItemID,
						name,
						label,
						brand,
						preview,
						selling,
						currency,
						DiscountID,
						discount,
						caption,
						remainder
					FROM gb_items
					CROSS JOIN gb_stock USING(ItemID)
					CROSS JOIN gb_models USING(PageID)
					LEFT JOIN gb_discounts USING(DiscountID)
					WHERE gb_items.ItemID IN (".implode(",", $IDs).") AND stock LIKE 'main'");
					$total = 0;
					foreach($body as $row):
						$discount = 0;
						$row['selling'] *= $cng->{$row['currency']};
						if($row['DiscountID'] && $row['discount']){
							$discount = floor($row['selling'] * $row['discount'] / 100);
						}
						$price = $row['selling'] - $discount;
						$total += $sum = $cart[$row['ItemID']]*$price;

						$remainder = ($row['remainder']>=$cart[$row['ItemID']]);
					?>
						<tr>
							<td bgcolor="white" align="center"><img src="<?=$row['preview']?>" width="64"></td>
							<td>
								<small>id: <?=($row['PageID'].'-'.$row['ItemID'])?></small><br>
								<b><?=($row['name'].' - '.$row['label'])?></b><br>
								<span class="green"><?=$row['brand']?></span>
							</td>
							<td>
								<?if($discount):?><div class="red"><?=$row['caption']?></div><?endif?>
								<?if($order['status']=="new"):?><input class="amount" value="<?=$cart[$row['ItemID']]?>" data-price="<?=$price?>" data-id="<?=$row['ItemID']?>" size="2" type="number" max="<?=$row['remainder']?>" min="0" autocomplete="off"> 
								<?else:?><b><?=$cart[$row['ItemID']]?></b><?endif?> ✕ <?=$price?><br>
								<b class="sum"><?=$sum?></b> грн
							</td>
							<?if($order['status']=="new"):?>
							<td align="center" class="<?=($remainder?'green':'red')?>">
								<span data-translate="textContent">remainder</span>: <b calss="remainder"><?=$row['remainder']?></b>
							</td>
							<?endif?>
						</tr>
					<?endforeach?>
						<script>
						(function(tbody){
							tbody.onchange = function(event){
								var total = 0,
									cart = {},
									sum = tbody.querySelectorAll(".sum");
								tbody.querySelectorAll(".amount").forEach(function(inp,i){
									cart[inp.dataset.id] = inp.value;
									total += sum[i].textContent = inp.value * inp.dataset.price;
								});
								
								document.querySelector("#price").value = 
								total - Math.floor(parseInt(document.querySelector("#discount").value) * total / 100)
								
								document.querySelector("#fullprice").value = total
								document.querySelector("#total").textContent = total.toFixed(2);
								XHR.push({
									"addressee":"/orders/actions/cart/"+ORDERID,
									"body":JSON.encode(cart),
									"onsuccess":function(response){
										document.querySelector("#log").innerHTML = response;
									}
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</tbody>
				</table>
				<div class="caption">
				<span>Log</span>
				<span class="right"><span data-translate="textContent">sum</span>: <span id="total"><?=money_format("%i", $total)?></span></span>
				</div>
				<form id="comment" autocomplete="off">
					<div id="log"><?=$order['log']?></div>
					<textarea name="comment" placeholder="comment" data-translate="placeholder"></textarea>
					<button type="submit">&#xf1d8;</button>
					<script>
					(function(form){
						form.onsubmit = function(event){
							event.preventDefault();
							XHR.push({
								"addressee":"/orders/actions/comment/"+ORDERID,
								"Content-Type":"text/plain",
								"body":form.comment.value.trim(),
								"onsuccess":function(response){
									form.querySelector("#log").innerHTML = response;
									form.comment.value = "";
								}
							});
						}
					})(document.currentScript.parentNode)
					</script>
				</form>
				<?endif?>
			</div>
		</div>
	</body>
</html>