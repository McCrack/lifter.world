<?php

require_once("core/db.php");


$feed = $mySQL->get("
SELECT * FROM gb_blogfeed 
CROSS JOIN gb_pages USING(PageID) 
WHERE published & 2
ORDER BY created DESC
LIMIT {int}, 20", MODULE);


/*
$feed = $mySQL->get("
SELECT * FROM gb_blogfeed 
CROSS JOIN gb_pages USING(PageID) 
WHERE (created BETWEEN {int} AND {int}) AND published & 2
ORDER BY created DESC", (MODULE - 86400), MODULE);
*/

?>

<div class="next-page grid">
	<?foreach($feed as $snippet):?>
	<a href="/<?=translite($snippet['header']).'-'.$snippet['PageID']?>">
		<div class="snippet">
			<div class="preview"><img src="<?=$snippet['preview']?>" alt="<?=$snippet['header']?>"></div>
			<div class="caption"><?=$snippet['header']?></div>
			<br>
		</div>
	</a>
	<?endforeach?>
</div>