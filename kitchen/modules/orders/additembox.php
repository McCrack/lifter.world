<?php
	
	$brancher->auth("orders") or die(include_once("modules/auth/alert.html"));
	$handle = "b".time();
	
?>

<form id="<?=$handle?>" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:320px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<sup data-translate="textContent">add item</sup>
	</div>
	<div class="box-body">
		<div class="caption" align="right">
			<span data-translate="textContent">code</span>: <input name="code" class="tool" placeholder="..." size="8" autocomplete="off">
		</div>
		<section onchange="form.addToOrder(event.target)" style="min-height:300px;background-color:white">
					
		</section>
	</div>
	<script>
	var timeout;
	var form = document.currentScript.parentNode;
	form.code.oninput = function(){
		var code = this.value;
		clearTimeout(timeout);
		timeout = setTimeout(function(){
			XHR.push({
				"addressee":"/orders/actions/serchproduct/"+code,
				"onsuccess":function(response){
					var section = form.querySelector("section");
					section.innerHTML = response;
					wordlist.fragment(section);
				}
			});
		},1200);
	}
	form.addToOrder = function(obj){
		if(obj.type=="number") return true;
		var cart = {};
		cart[obj.dataset.id] = form.amount.value;
		XHR.push({
			"addressee":"/orders/actions/addtoorder/<?=SUBPAGE?>",
			"body":JSON.encode(cart),
			"onsuccess":function(response){
				form.reset();
				document.querySelector("#orderbody").innerHTML+=response;
			}
		});
	}
	</script>
</form>