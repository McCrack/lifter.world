<?php
	
	$brancher->auth("store") or die(include_once("modules/auth/page.php"));
	
	$model_disabled = $item_disabled = "";
	if(SUBPAGE){
		$model = $mySQL->single_row("SELECT * FROM gb_models WHERE PageID = ".SUBPAGE." LIMIT 1");
		if(empty($model)){
			$model_disabled = $item_disabled = "disabled";
		}elseif(PARAMETER){
			$item = $mySQL->single_row("SELECT * FROM `gb_items` WHERE `ItemID` = ".PARAMETER." LIMIT 1");
			if(empty($item)){
				$item_disabled = "disabled";
			}else{
				$discount = $mySQL->single_row("SELECT `DiscountID`,`sticker` FROM `gb_discounts` WHERE `DiscountID` = ".$item['DiscountID']." LIMIT 1");
				$reference = ($item['ItemID']!=$model['ItemID']) ? "" : "checked";
				$stock = $mySQL->single_row("SELECT SUM(`remainder`) AS `remainder` FROM `gb_stock` WHERE `ItemID` = ".$item['ItemID']);
			}
		}else $item_disabled = "disabled";
	}else $model_disabled = $item_disabled = "disabled";
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Gb.showcase</title>
		<link rel="stylesheet" type="text/css" href="/tpls/main.css">
		<link rel="stylesheet" type="text/css" href="/modules/store/tpl/showcase.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/theme.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<script src="/tpls/main.js"></script>
		<script src="/modules/store/tpl/showcase.js"></script>
		<script src="/modules/editor/tpl/editor.js"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=store&d[3]=editor" async charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script>window.onbeforeunload = reauth;</script>
	</head>
	<body class="<?=$standby[SECTION]['bodymode']?>">
		<aside id="leftbar">
			<a href="/" id="goolybeep">	</a>
			<div id="left-panel">
				<div class="tabbar left" onclick="openTab(event.target, 'leftbar')">
					<div class="toolbar">
						<span class="tool" data-tab="modules-list">&#xe5c3;</span>
						<span class="tool" data-tab="items">&#xe902;</span>
					</div>
				</div>
				<div class="tab left" id="modules-list" onclick="executeModule(event.target)">
					<div class="caption" data-translate="textContent">modules</div>
					<div class="root">
						<?=$brancher->tree($brancher->register)?>
					</div>
				</div>
				<div class="tab left" id="items" align="center">
					<div class="caption"><span data-translate="textContent">items</span></div>
					<?$items = $mySQL->group_rows("SELECT `ItemID`,`label`,`selling`,`preview` FROM `gb_items` WHERE `PageID`=".$model['PageID']);
					foreach($items['label'] as $key=>$label) if($items['ItemID'][$key]===PARAMETER):?>
						<a class="sticker selected" href="/store/showcase/<?=($model['PageID']."/".$items['ItemID'][$key])?>"">
							<img src="<?=$items['preview'][$key]?>">
							<div class="model" align="left"><?=($items['ItemID'][$key].'. '.$label)?></div>
						</a>
					<?else:?> 
						<a class="sticker" href="/store/showcase/<?=($model['PageID']."/".$items['ItemID'][$key])?>"">
							<img src="<?=$items['preview'][$key]?>">
							<div class="model" align="left"><?=($items['ItemID'][$key].'. '.$label)?></div>
						</a>
					<?endif?>
				</div>
			</div>
		</aside>
		<div id="topbar" class="panel">
			<div class="toolbar">
				<span class="tool" data-translate="title" title="back to catalog" onclick="backToCatalog()">&#xe045;</span>
				<span class="tool" data-translate="title" title="add item" onclick="addItem()">&#xe901;</span>
				<span class="tool" data-translate="title" title="create model" onclick="createModel()">&#xf15b;</span>
				<span class="tool" data-translate="title" title="save" onclick="saveItem()">&#xe962;</span>
				<span class="tool" data-translate="title" title="remove" onclick="remove()">&#xe9ac;</span>
			</div>
			<div class="toolbar right">
				<span title="screen mode" data-translate="title" id="bodymode" class="tool" onclick="changeBodyMode()">&#xf066;</span>
				<span title="settings" data-translate="title" class="tool" onclick="settingsBox('store')">&#xf013;</span>
			</div>
		</div>
		<form id="environment" autocomplete="off" onsubmit="return saveItem()">
			<div id="cover" align="right">
				<fieldset id="model" <?=$model_disabled?>>
				<div class="left">
					<span>Model ID</span>:<input name="ModelID" value="<?=$model['PageID']?>" readonly style="width:68px"><br>
					<span data-translate="textContent">category</span>:<select name="category" style="width:80px">
					<?php
					$map = $mySQL->tree("SELECT `PageID`,`parent`,`name`,`header` FROM `gb_sitemap`", "name", "parent");
					staticTree($map, "store", $model['CategoryID']);
					?>
					</select>
				</div>
				<span data-translate="textContent">status</span>:<select name="status" style="width:112px">
				<?foreach(["available","not available"] as $status)	if($status===$model['status']):?>
					<option data-translate="textContent" value="<?=$status?>" selected><?=$status?></option>
				<?else:?>
					<option data-translate="textContent" value="<?=$status?>"><?=$status?></option>
				<?endif?>
				</select><br>
				<!--<span data-translate="textContent">articul</span>:<input name="articul" value="<?=$model['articul']?>" style="width:100px" placeholder="...">-->
				<span>Related</span>:<select name="related" style="width:112px">
				<?php
				staticTree($map, "store", $model['RelatedCategoryID'])?>
				</select>
				</fieldset>
				<fieldset>
					<div class="left">
					<span data-translate="textContent">template</span>:<select name="template" style="width:90px"> 
					<?foreach(scandir("../".BASE_FOLDER."/themes/".JSON::load("../".BASE_FOLDER."/config.init")['general']['theme']['value']."/includes/showcase") as $file){
						$file = explode(".", $file);
						if(end($file) === "html") if($file[0] === $post['template']):?>
							<option value="<?=$file[0]?>" selected><?=$file[0]?></option>
						<?else:?>
							<option value="<?=$file[0]?>"><?=$file[0]?></option>
						<?endif;
					}
					?>
					</select>
					</div>
					<span>Brand</span>:<input name="brand" list="brands" value="<?=$model['brand']?>" style="width:112px">
					<datalist id="brands">
					<?foreach($mySQL->query("SELECT tag FROM gb_keywords") as $brand):?>
						<option><?=$brand['tag']?></option>
					<?endforeach?>
					</datalist>
				</fieldset>
				<img id="preview" src="<?=(empty($item['preview'])?"/images/NIA.jpg":$item['preview'])?>" height="220px">
				<script>
				(function(obj){
					reauth();
					var frame = doc.create("iframe",{ "id":"preview", "src":"/uploader/image-frame", "class":"uploader-frame", "height":obj.height});
					frame.onload = function(){
						frame.contentWindow.document.setImage(obj.src);								
					}
					obj.parentNode.replaceChild(frame, obj);
				})(doc.querySelector('#preview'));
				</script>
				<textarea id="pName" name="pName" placeholder="model" data-translate="placeholder"><?=$model['name']?></textarea>
			</div>
			<fieldset id="price-form" <?=$item_disabled?>>
				<div class="left">
					Item ID:<input name="item" value="<?=$item['ItemID']?>" readonly size="2"><br>
				</div>
				<span data-translate="textContent">item</span>:<input name="label" value="<?=$item['label']?>" style="width:82px;">
				<hr size="1" color="#AAA"><br>
				<div class="right" align="left">
					<select name="currency">
					<?foreach(["USD","EUR","UAH"] as $currency) if($currency===$item['currency']):?>
						<option value="<?=$currency?>" selected><?=$currency?></option>
					<?else:?>
						<option value="<?=$currency?>"><?=$currency?></option>
					<?endif?>
					</select><br>
					<select name="units">
					<?foreach(["шт.","м","м²","г.","кг.","л."] as $unit) if($unit===$item['units']):?>
						<option value="<?=$unit?>" selected><?=$unit?></option>
					<?else:?>
						<option value="<?=$unit?>"><?=$unit?></option>
					<?endif?>
					</select>
				</div>
				<div class="right">
					<span data-translate="textContent">purchase</span>:<input name="purchase" value="<?=$item['purchase']?>" size="4" placeholder="0.00"><br>
					<span data-translate="textContent">selling</span>:<input name="selling" value="<?=$item['selling']?>" size="4" placeholder="0.00 грн.">
				</div>
				<br clear="right"><br>
				<hr size="1" color="#AAA"><br>
				<fieldset class="right" align="center"><legend data-translate="textContent">remainder</legend>
					<input name="remainder" value="<?=$stock['remainder']?>" size="3" onclick="stockBox(this)">
				</fieldset>
				<fieldset align="center"><legend data-translate="textContent">stock out</legend>
					<select name="outstock" style="width:90%;">
					<?foreach(["not available","under the order"] as $status) if($status===$item['outstock']):?>
						<option data-translate="textContent" value="<?=$status?>" selected><?=$status?></option>
					<?else:?>
						<option data-translate="textContent" value="<?=$status?>"><?=$status?></option>
					<?endif?>
					</select>
				</fieldset>
				<br>
				<span data-translate="textContent">discount</span>:<input name="discount" value="<?=$discount['sticker']?>" data-id="<?=$discount['DiscountID']?>" onclick="discountBox(this)" size="8" placeholder="%" readonly><br>
				<span data-translate="textContent">dumping</span>:<input name="dumping" value="<?=(empty($item['dumping'])?"":$item['dumping'])?>" pattern="[0-9%]*" size="8" placeholder="%"><br>
				<br><hr size="1" color="#AAA">
				<p align="right">
					<label><span data-translate="textContent">reference</span> <input name="reference" type="checkbox" <?=$reference?> style="vertical-align:middle"></label>
				</p>
			</fieldset>
			<div id="options">
				<div class="panel">
					<span class="tool" data-translate="textContent" style="line-height:32px">options</span>
					<div class="toolbar right">
						<span class="tool" data-translate="title" title="show pattern" onclick="showPattern(patternWithoutValidate(doc.querySelector('#properties')), 'JsonToOptions');">&#xe8ab;</span>
					</div>
				</div>
				<table id="properties" rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
					<colgroup><col width="30"><col><col width="30"></colgroup>
					<tbody>
					<?php
					$category = $mySQL->single_row("SELECT `optionset`,`filterset` FROM `gb_static` WHERE `PageID` = ".$model['CategoryID']." LIMIT 1");
					$options = JSON::parse($model['options']);
					if(empty($options))	$options = JSON::parse($category['optionset']);
					if(empty($options)):?>
						<tr>
							<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
							<td contenteditable="true"></td>
							<th bgcolor="white"><span title="delete row" data-translate="title" class="tool red" onclick="deleteRow(this)">&#xe907;</span></th>
						</tr>
					<?else: foreach($options as $key=>$val):?>
						<tr>
							<th bgcolor="white"><span title="add row" data-translate="title" class="tool" onclick="addRow(this)">&#xe908;</span></th>
							<td contenteditable="true"><?=$val?></td>
							<th bgcolor="white"><span title="delete row" data-translate="title" class="tool red" onclick="deleteRow(this)">&#xe907;</span></th>
						</tr>
					<?endforeach;
					endif?>
					</tbody>
				</table>
			</div>
			<div id="filters">
				<div class="caption" data-translate="textContent">filters</div>
				<?php
				$tagination = $mySQL->single_row("SELECT * FROM `gb_tagination` WHERE `tid` = ".$item['tid']." LIMIT 1");
				$IDs = [];
				$cnt = count($tagination)-1;
				for($j=0; $j<$cnt; $j++) for($i=33; $i--;) if($tagination[$j] & pow(2, $i-1)){ 
					$IDs[] = (32 * $j) + $i; 
				}
				foreach(JSON::parse($category['filterset']) as $name=>$set):?>
				<fieldset <?=$item_disabled?>><legend><?=$name?></legend>
					<?foreach($set as $key=>$vals):?>
					<label><input type="checkbox" <?=(in_array($vals['id'], $IDs) ? "checked" : "")?> value="<?=$vals['id']?>"> <?=$vals['value']?></label>
					<?endforeach?>
				</fieldset>
				<?endforeach?>
			</div>
		</form>
		<div id="rightbar">
			<aside class="tabbar right" onclick="openTab(event.target, 'rightbar')">
				<div class="toolbar">
					<span data-translate="title" title="images" class="tool" data-tab="images">&#xe909;</span>
					<span data-translate="title" title="full description" class="tool" data-tab="description">&#xf0f6;</span>
					<span data-translate="title" title="manual" class="tool" data-tab="manual">&#xf05a;</span>
				</div>
			</aside>
			<div id="images" class="tab" style="background-color:#383E49">
				<div class="caption">
					<span data-translate="textContent">images</span>
					<div class="toolbar right">
						<span onclick="addImages()" title="add slides" data-translate="title" class="tool">&#xf07c;</span>
						<span onclick="this.parent(3).removeSlide()" title="remove slide" data-translate="title" class="tool">&#xf05f;</span>
					</div>
				</div>
				<div class="leftpoint" data-dir="-1"></div>
				<div class="rightpoint" data-dir="1"></div>
				<div id="slideshow" data-current="0"></div>
				<div class="imagelist" align="right" style="padding:4px">
				<?foreach(JSON::parse($item['images']) as $src):?>
					<img src="<?=$src?>">
				<?endforeach?>
				</div>
			</div>
			<div id="description" class="tab">
				<div class="panel">
					<div class="toolbar"><label class="tool" data-translate="textContent">description</label></div>
				</div>
				<textarea id="content"><?=gzdecode($model['description'])?></textarea>
				<script>
				var editor;
				(function(obj){
					var frame = doc.create("iframe",{ src:"/editor/embed","class":"HTMLDesigner", style:"height:calc(100% - 36px);"});
					frame.onload = function(){
						editor = new HTMLDesigner(frame.contentWindow.document);
						editor.setValue(obj.value);
						editor.addCSSFile("/modules/editor/tpl/content.css");
					}
					obj.parentNode.replaceChild(frame, obj);
				})(doc.querySelector('#content'));
				</script>
			</div>
			<div id="manual" class="tab">
<? include_once("modules/manual/embed.php") ?>
			</div>
		</div>
	</body>
</html>

<?php
function staticTree(&$items, $offset, $selected){
	if(is_array($items[$offset])):
		foreach($items[$offset] as $key=>$val):
			$name = empty($val['header']) ? $val['name'] : $val['header'];
			if($val['PageID']===$selected):?>
				<option selected value="<?=$val['PageID']?>"><?=$name?></option>
			<?else:?>
				<option value="<?=$val['PageID']?>"><?=$name?></option>
			<?endif;
			staticTree($items, $key, $selected);
		endforeach;
	endif;
}
?>