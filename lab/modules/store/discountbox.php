<?php
	
	$brancher->auth("store") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	
?>
<form id="<?php print($handle); ?>" class="box" onmousedown="boxList.focus(this)" style="max-width:660px">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
var handle = "<?php print $handle; ?>";
boxList[handle].loadDiscount=function(itm, leftbar){
	var form = leftbar.parent(2);
	if(itm.nodeName==="LABEL"){
		XHR.push({
			"addressee":"/store/actions/load-discount/"+itm.dataset.id,
			"onsuccess":function(response){
				var params = JSON.parse(response);
				form.DiscountID.value = params.DiscountID;
				form.label.value = params.sticker;
				form.discount.value = params.discount;
				form.from.value = params.from;
				form.to.value = params.to;
				form.caption.value = params.caption;
				form.essence.value = params.essence;
				
				var items = leftbar.querySelectorAll("label");
				for(var i=items.length; i--;){
					if(itm.contains(items[i])){
						items[i].className = "tree-root-item";
					}else items[i].className = "tree-item";
				}
			}
		});
	}
}
boxList[handle].addDiscount=function(){
	var form = boxList[handle].window;
	var items = form.querySelectorAll(".leftbar>label");
	for(var i=items.length; i--;) items[i].className = "tree-item";
	
	form.DiscountID.value = 0;
	form.label.value = "";
	form.discount.value = 0;
	form.from.value = date("d.m.Y");
	form.to.value = date("d.m.Y");
	form.caption.value = "";
	form.essence.value = "";
}
boxList[handle].saveDiscount=function(){
	var form = boxList[handle].window;
	XHR.push({
		"addressee":"/store/actions/save-discount",
		"body":JSON.stringify({
			"DiscountID":form.DiscountID.value,
			"caption":form.caption.value,
			"from":form.from.value,
			"to":form.to.value,
			"essence":form.essence.value,
			"sticker":form.label.value,
			"discount":parseInt(form.discount.value),
		}),
		"onsuccess":function(response){
			if(isNaN(response)){
				alertBox("Unknow error")
			}else{
				form.DiscountID.value = response;
				var leftbar = form.querySelector(".leftbar");
				var items = leftbar.querySelectorAll("label");
				for(var i=items.length; i--;){
					if(items[i].dataset.id===response){
						items[i].textContent = form.caption.value;
						break;
					}else if(i==(items.length-1)){
						leftbar.appendChild(doc.create("label", {"class":"tree-root-item", "data-id":response}, form.caption.value));
					}
				}
			}
		}
	});
}
boxList[handle].deleteDiscount=function(){
	var form = boxList[handle].window;
	XHR.push({
		"Content-Type":"text/plain",
		"addressee":"/store/actions/delete-discount/"+form.DiscountID.value,
		"onsuccess":function(response){
			if(response>0){
				var item = form.querySelector(".leftbar>label.tree-root-item");
				item.parentNode.removeChild(item);
				form.DiscountID.value = 0;
				form.label.value = "";
				form.discount.value = 0;
				form.from.value = date("d.m.Y");
				form.to.value = date("d.m.Y");
				form.caption.value = "";
				form.essence.value = "";
			}
		}
	});
}
</script>
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<span data-translate="textContent">discounts</span>
	</div>
	<div id="discount-box" class="box-body" style="height:332px">
		<div class="leftbar" onclick="boxList[handle].loadDiscount(event.target, this)">
<?php
	$list = $mySQL->query("SELECT * FROM `gb_discounts`");
	$now = time();
	$items = "";
	$current = ["DiscountID"=>0, "from"=>$now, "to"=>$now];
	foreach($list as $item){
		if($item['DiscountID']===SUBPAGE){
			$items .= "<label class='tree-root-item' data-id='".$item['DiscountID']."'>".$item['caption']."</label>";
			$current = $item;
		}else $items .= "<label class='tree-item' data-id='".$item['DiscountID']."'>".$item['caption']."</label>";
	}
	print($items);
?>
		</div>
		<div class="environment">
			<div class="panel">
				<div class="toolbar">
					<span class="tool" data-translate="title" title="add discount" onclick="boxList[handle].addDiscount()">&#xe901;</span>
					<span class="tool" data-translate="title" title="save discount" onclick="boxList[handle].saveDiscount()">&#xe962;</span>
					<span class="tool" data-translate="title" title="delete discount" onclick="boxList[handle].deleteDiscount()">&#xe9ac;</span>
				</div>
			</div>
			<fieldset class="left"><legend>ID: <input name="DiscountID" pattern="[0-9]*" readonly size="1" value="<?php print($current['DiscountID']); ?>" style="margin:0px;"></legend>
				<div align="right" >
				<span data-translate="textContent">sticker</span>:<input name="label" required pattern=".*" size="8" value="<?php print($current['label']); ?>"><br>
				<span data-translate="textContent">discount</span>:<input name="discount" required pattern="[0-9%]*" size="8" value="<?php print($current['discount']); ?>">
				</div>
			</fieldset>
			<fieldset><legend data-translate="textContent">period</legend>
				<div align="right" >
				<span data-translate="textContent">from</span>: <input name="from" required pattern="[0-9.]+" size="9" value="<?php print(date("d.m.Y",$current['from'])); ?>" onfocus="datepicker(event, 'blue')"><br>
				<span data-translate="textContent">to</span>: <input name="to" required pattern="[0-9.]+" size="9" value="<?php print(date("d.m.Y",$current['to'])); ?>" onfocus="datepicker(event, 'red')">
				</div>
			</fieldset>
			<input name="caption" required pattern=".*" placeholder="name" value="<?php print($current['caption']); ?>" data-translate="placeholder" style="width:calc(100% - 8px);">
			<textarea name="essence"  pattern=".*" placeholder="description" data-translate="placeholder"><?php print($curent['essence']); ?></textarea>
		</div>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">apply</button>
		<button type="reset" data-translate="textContent">reset</button>
		<button data-translate="textContent" onclick="boxList.drop(this.parent(2).id)">cancel</button>
	</div>
</form>