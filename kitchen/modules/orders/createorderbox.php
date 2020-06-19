<?php
	
	$brancher->auth("orders") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	
?>

<form id="<?=$handle?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:980px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<sup data-translate="textContent">new order</sup>
	</div>
	<div class="box-body">
		<div class="order">
			<fieldset class="left" style="text-align:right">
				<input name="citizen" value="0" type="hidden">
				<span data-translate="textContent" class="gold">phone</span>: <input name="phone" autocomplete="off" required><br>
				<span data-translate="textContent" class="gold">client name</span>: <input name="clientname" required><br>
				<span>Email</span>: <input name="email">
			</fieldset>
			<fieldset class="left" style="text-align:right">
				<span data-translate="textContent">type</span>: <div class="select"><select name="orderType">
					<option value="deal">Deal</option>
					<option value="reserved">Reserved</option>
					<option value="delivery">Delivery</option>
				</select></div><br>
				<span data-translate="textContent">payment</span>: <div class="select"><select name="payment">
					<option value="cash" data-translate="textContent">cash</option>
					<option value="card" data-translate="textContent">card</option>
					<option value="on delivery" data-translate="textContent">on delivery</option>
				</select></div>
			</fieldset>
			<fieldset class="delivery" disabled>
				<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#999">
					<colgroup><col width="30"><col><col width="50%"><col width="30"></colgroup>
					<tbody>
						<tr>
							<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
							<td data-translate="textContent">tracking</td>
							<td contenteditable="true"></td>
							<th bgcolor="white"><span title="delete row" data-translate="title" class="tool red" onclick="deleteRow(this)">&#xe907;</span></th>
						</tr>
						<tr>
							<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
							<td data-translate="textContent">city</td>
							<td contenteditable="true"></td>
							<th bgcolor="white"><span title="delete row" data-translate="title" class="tool red" onclick="deleteRow(this)">&#xe907;</span></th>
						</tr>
						<tr>
							<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
							<td data-translate="textContent">office</td>
							<td contenteditable="true"></td>
							<th bgcolor="white"><span title="delete row" data-translate="title" class="tool red" onclick="deleteRow(this)">&#xe907;</span></th>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
		<div class="products">
			<div>
				<div class="caption">
					<span data-translate="textContent">code</span>: <input name="code" class="tool" placeholder="..." size="8" autocomplete="off">
				</div>
				<aside onchange="form.addToNewOrder(event.target)">
					
				</aside>
			</div>
			<div>
				<div class="panel">
					<div class="toolbar"><label class="tool" data-translate="textContent">order body</label></div>
					<div class="toolbar right">
						<label class="tool" data-translate="textContent">sum</label><input class="tool" name="price" placeholder="0.00" size="6">
					</div>
				</div>
				<div class="environment">
					<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#999">
						<colgroup><col width="68"><col><col><col width="36px"></colgroup>
						<tbody onchange="form.recalculate()">

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">create</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
	<script>
	var timeout;
	var form = document.currentScript.parentNode;
	form.phone.oninput=function(){
		var phone = this.value.replace(/\D/g, "").replace(/^38/, "");
		this.value = phone;
		clearTimeout(timeout);
		timeout = setTimeout(function(){
			if(phone.length<10) return true;
			XHR.push({
				"addressee":"/orders/actions/serchclient",
				"body":phone,
				"onsuccess":function(response){
					response = JSON.parse(response);
					if(response && parseInt(response['status'])){
						form.citizen.value = response.citizen['CommunityID'];
						form.clientname.value = response.citizen['Name'];
						form.email.value = response.citizen['Email'];
					}else{
						form.citizen.value = 0;
						form.clientname.value = "";
						form.email.value = "";
					}
				}
			});
		},1200);
	}
	form.orderType.onchange=function(){
		form.querySelector(".delivery").disabled = this.value!="delivery";
	}
	form.code.oninput = function(){
		var code = this.value;
		clearTimeout(timeout);
		timeout = setTimeout(function(){
			XHR.push({
				"addressee":"/orders/actions/serchproduct/"+code,
				"onsuccess":function(response){
					var aside = form.querySelector(".products>div>aside");
					aside.innerHTML = response;
					wordlist.fragment(aside);
				}
			});
		},1200);
	}
	form.addToNewOrder = function(obj){
		if(obj.type=="number") return true;
		XHR.push({
			"addressee":"/orders/actions/add/"+obj.dataset.id+"-"+form.amount.value,
			"onsuccess":function(response){
				form.querySelector(".environment>table>tbody").innerHTML += response;
				form.recalculate();
			}
		});
	}
	form.dropRow = function(row){
		row.parentNode.removeChild(row);
		form.recalculate();
	}
	form.recalculate = function(){
		var total = 0;
		var prices = form.querySelectorAll(".environment>table>tbody>tr .price");
		var sums = form.querySelectorAll(".environment>table>tbody>tr .sum");
		var amounts = form.querySelectorAll(".environment>table>tbody>tr input[type='number']");
		form.querySelectorAll(".environment>table>tbody>tr input[type='checkbox']").forEach(function(item, i){
			var sum = (item.checked) ? (amounts[i].value * item.value) : (amounts[i].value * item.dataset.price);
			prices[i].textContent = (item.checked) ? item.value : item.dataset.price;
			sums[i].textContent = sum;
			total += sum;
		});
		form.price.value = total.toFixed(2);
	}
	</script>
</form>