<?php

$orders = $mySQL->get("SELECT * FROM gb_orders CROSS JOIN gb_community USING(CommunityID) WHERE paid & 2 AND status & 1 ORDER BY OrderID DESC");

?>

<!DOCTYPE html>
<html hasbrowserhandlers="true">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<style>
			body{
				color:#555;
				padding:20px;
				font-family:calibri,helvetica,arial;
			}
			.cell{
				color:#000;
				height:20px;
				margin-right:10px;
				display:inline-block;
				padding:3px 10px 0px 10px;
				border-bottom:1px solid #AAA;
			}
			table>tbody>tr>td{
				font-size:14px;
				line-height:20px;
			}
		</style>
		<script src="/js/gbAPI.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=store" defer charset="utf-8" onload="translate.fragment()"></script>
	</head>
	<body>
		<h1 id="header" style="float:left;margin:0 0 10px 0">Слід на дорозі</h1>
		<div align="right" style="float:right">
			<span name="date" class="cell"><?=date("d.m.Y")?></span><br>
		</div>
		<br clear="both">
		<table width="100%" border="1" rules="rows" cellspacing="0" cellpadding="5">
			<thead>
				<th>№</th>
				<th data-translate="textContent">name</th>
				<th data-translate="textContent">delivery</th>
				<th data-translate="textContent">sum</th>
				<th data-translate="textContent">amount</th>
				<th data-translate="textContent">created</th>
			</thead>
			<tbody>
				<?foreach($orders as $order): $delivery = JSON::parse($order['delivery'])?>
				<tr align="center">
					<td><?=$order['OrderID']?></td>
					<td><?=$order['Name']?><br><b><?=$order['Phone']?></b></td>
					<td><b><?=$delivery['city']?></b><br><?=$delivery['warehouse']?></td>
					<td><?=$order['price']?></td>
					<td><?=$order['amount']?></td>
					<td><?=date("d.m.Y, H:i",$order['created'])?></td>
				</tr>
				<?endforeach?>
			</tbody>
			<tfoot>
					
			</tfoot>
		</table>
		<script>window.print();</script>
	</body>
</html>
