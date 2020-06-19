<?php
	
	$rows = $mySQL->get("SELECT SQL_CALC_FOUND_ROWS * FROM gb_keywords");
	$handle = "w:".time();

?>
<div id="<?=$handle?>" class="mount" style="width:720px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<form class="box white-bg">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xe9d3;<?include_once("components/movebox.php")?></div>
		<div class="h-bar logo-bg">
			keywords
		</div>
		<div class="box-body">
			<table width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#CCC">
				<thead>
					<tr class="h-bar-bg">
						<th width="22"></th>
						<th>Keyword</th>
						<th width="80">ID</th>
						<th width="110">Used</th>
					</tr>
				</thead>
				<tbody align="center">
				<?foreach($rows as $row):?>
					<tr>
						<th onclick="new Box('<?=$row['KeyWORD']?>', 'wordlist/box')" title="wordlist" dtat-translate="title" class="tool">&#xe9d2;</th>
						<td><?=$row['KeyWORD']?></td>
						<td><?=$row['KeyID']?></td>
						<td><?=$row['rating']?></td>
					</tr>
				<?endforeach?>
				</tbody>
				<tfoot>
					<tr class="black-bg"><td colspan="4">Total: <?=$mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt']?></td></tr>
				</tfoot>
			</table>
		</div>
		<script>document.currentScript.parentNode.onreset=function(){this.drop()}</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>