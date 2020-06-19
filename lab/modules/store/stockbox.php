<?php
	
	$brancher->auth("store") or die(include_once("modules/auth/alert.html"));
	
	$handle = "b".time();

?>

<form id="<?php print($handle); ?>" onsubmit="return saveShortWordlist(this)" class="box" onreset="boxList.drop(this.id)" onmousedown="boxList.focus(this)" style="max-width:400px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<div class="box-title">
		<span class="close-box" title="close" data-translate="title" onclick="boxList.drop(this.parent(2).id)"></span>
		<sup data-translate="textContent">stock</sup>
	</div>
	<div class="box-body">
		<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
			<colgroup><col width="30"><col><col width="120"><col width="30"></colgroup>
			<thead>
				<tr bgcolor="#444" style="color:#EEE">
					<th></th>
					<th data-translate="textContent">stock</th>
					<th data-translate="textContent">remainder</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
<?php

$rows = "";
$color=16777215;
$names = $mySQL->group_rows("SELECT `stock` FROM `gb_stock` GROUP BY `stock`");
if(empty($names)){
	$rows .= "
	<tr bgcolor='#".dechex($color^=1052688)."'>
		<th bgcolor='white'><span title='add row' data-translate='title' class='tool' onclick='addRow(this)'>&#xe908;</span></th>
		<td contenteditable='true'></td>
		<td contenteditable='true'></td>
		<th bgcolor='white'><span title='delete row' data-translate='title' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>
	</tr>";
}else{
	$items = $mySQL->group_rows("SELECT `remainder` FROM `gb_stock` WHERE `ItemID`=".SUBPAGE);
	foreach($names['stock'] as $key=>$stock){
		$rows .= "
		<tr bgcolor='#".dechex($color^=1052688)."'>
			<th bgcolor='white'><span title='add row' data-translate='title' class='tool' onclick='addRow(this)'>&#xe908;</span></th>
			<td contenteditable='true'>".$stock."</td>
			<td contenteditable='true'>".(INT)$items['remainder'][$key]."</td>
			<th bgcolor='white'><span title='delete row' data-translate='title' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>
		</tr>";
	}
}
print($rows);

?>
			</tbody>
		</table>
	</div>
	<div class="box-footer">
		<button type="submit" data-translate="textContent">save</button>
		<button type="reset" data-translate="textContent">cancel</button>
	</div>
</form>