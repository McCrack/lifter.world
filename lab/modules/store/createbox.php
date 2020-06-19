<?php
	
	$brancher->auth("store") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();
	
?>

<form id="<?=$handle?>" onsubmit="return saveShortWordlist(this)" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:305px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<sup data-translate="textContent">create model</sup>
	</div>
	<div class="box-body" align="center" style="background-color:#F0F0F0;border:1px solid #CCC;">
			<br>
			<span style="font:16px main;" data-translate="textContent">category</span>:
			<select name="category" style="margin:6px 0px 0px 5px;padding:4px 10px;border:1px solid #AAA;border-radius:3px;min-width:190px;">
<?php
	function staticTree(&$items, $offset="store"){
		if(is_array($items[$offset])){
			foreach($items[$offset] as $key=>$val){
				if($val['name']===SUBPAGE){
					$result.="<option selected value='".$val['PageID']."'>".$val['name']."</option>";
				}else $result.="<option value='".$val['PageID']."'>".$val['name']."</option>";
				$result .= staticTree($items, $key);
			}
			return $result;
		}
	}
	$rows = $mySQL->tree("SELECT `PageID`,`parent`,`name` FROM `gb_sitemap`", "name", "parent");
	print( staticTree($rows) );
?>
			</select>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">create</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>