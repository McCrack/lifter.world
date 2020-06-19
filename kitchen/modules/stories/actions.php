<?switch(ARG_2){
	case "reload":
		$limit = 30;
		$page = ARG_4;
		$rows = $mySQL->get("SELECT SQL_CALC_FOUND_ROWS * FROM gb_blogfeed LEFT JOIN gb_pages USING(PageID) WHERE language LIKE {str} GROUP BY ID ORDER BY PageID DESC LIMIT {int},{int}", ARG_3,($page-1)*$limit, $limit);
		$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];
		foreach($rows as $row):?>
			<a class="snippet" href="/blogger/<?=(ARG_3."/".$page."/".$row['PageID'])?>">
				<img src="<?=$row['preview']?>" alt="">
				<div class="header"><?=$row['header']?></div>
				<div class="options">
					<span><?=date("d M Y", $row['created'])?></span>
					<span><?=$row['published']?></span>
				</div>
			</a>
		<?endforeach?>
		<div class="h-bar pagination" align="right">
		<?if(($total = ceil($count/$limit))>1):
			if($page>4):
				$j=$page-2?>
				<a>1</a> ... 
			<?else: $j=1; endif;
			for(; $j<$page; $j++):?><a><?=$j?></a><?endfor?>
			<span class="active-txt"><?=$j?></span>
			<?if($j<$total):?>
				<a><?=(++$j)?></a>
				<?if($j<$total):?>
					<?if(($total-$j)>1):?> ... <?endif?>
					<a><?=$total?></a>
				<?endif;
			endif;
		endif;
	break;
	case "create-post":
		$time = time();

		$PageID = $mySQL->inquiry("INSERT INTO gb_pages SET {prp}", $mySQL->parse("{set}",[
			"type"=>"story",
			"created"=>$time,
			"modified"=>$time,
			"customizer"=>JSON::encode([
				"caption-color"=>"#FFFFFF",
				"caption-bg"=>"#000000",
				"caption-opacity"=>"1",
				"caption-font"=>"Oswald",
				"caption-size"=>"20",
				"text-color"=>"#000000",
				"text-bg"=>"#FFFFFF",
				"text-opacity"=>"1",
				"text-font"=>"open sans",
				"text-size"=>"15",
			])
		]))['last_id'];
		
		$mySQL->inquiry("INSERT INTO gb_stories (PageID, header) VALUES ({int},'New Story')", $PageID);
		$mySQL->inquiry("INSERT INTO gb_blogcontent SET PageID={int}, UserID={int} content={str}", $PageID, USER_ID, gzencode('{}'));

		print $PageID;
	break;
	case "save":
		$p = JSON::load('php://input');

		$keywords = $mySQL->getGroup("SELECT KeyWORD FROM blog_vs_keywords CROSS JOIN gb_keywords USING(KeyID) WHERE PageID={int}", $p['PageID'])['KeyWORD'];
		$keywords = is_array($keywords) ? array_diff($p['keywords'], $keywords) : $p['keywords'];

		if(!empty($keywords)) $mySQL->inquiry("INSERT INTO gb_keywords (KeyWORD) VALUES ('".implode("'),('", $keywords)."') ON DUPLICATE KEY UPDATE rating=rating+1");
		$IDs = $mySQL->getGroup("SELECT KeyID FROM gb_keywords WHERE KeyWORD IN ('".implode("','", $p['keywords'])."')", $p['PageID'])['KeyID'];
		$mySQL->inquiry("DELETE FROM blog_vs_keywords WHERE PageID = {int}", $p['PageID']);
		
		if(!empty($IDs)) $mySQL->inquiry("INSERT INTO blog_vs_keywords (PageID,KeyID) VALUES (".$p['PageID'].",".implode("),(".$p['PageID'].",", $IDs).")");

		$p['header'] = base64_decode($p['header']);
		$p['subheader'] = base64_decode($p['subheader']);

		$mySQL->inquiry("UPDATE gb_stories SET {prp} WHERE PageID={int} LIMIT 1", $mySQL->parse("{set}",[
			"header"=>$p['header'],
			"subheader"=>$p['subheader'],
			"landscape"=>$p['landscape'],
			"portrait"=>$p['portrait'],
			"Ads"=>$p['Ads'],
			"published"=>$p['published']
		]),$p['PageID']);
		
		foreach($p['mediaset'] as &$set){
			$set['header'] = base64_decode($set['header']);
			$set['text'] = base64_decode($set['text']);
		}

		$mySQL->inquiry("UPDATE gb_blogcontent SET {prp} WHERE PageID={int} LIMIT 1", $mySQL->parse("{set}",[
			"UserID"=>$p['UserID'],
			"subtemplate"=>$p['subtemplate'],
			"content"=>gzencode(JSON::encode($p['mediaset']))
		]),$p['PageID']);

		$mySQL->inquiry("UPDATE gb_pages SET created={int},modified={int},customizer={str} WHERE PageID={int} LIMIT 1", $p['created'],time(),JSON::encode($p['story']),$p['PageID']);

		$answer = [
			"log"=>["PageID"=>$p['PageID']],
			"url"=>PROTOCOL."://".HOST."/".translite($p['header'])."-".$p['PageID']
		];
		$info = $mySQL->getRow("SELECT * FROM gb_stories CROSS JOIN gb_pages USING(PageID) CROSS JOIN gb_blogcontent USING(PageID) WHERE PageID={int} LIMIT 1", $p['PageID']);
		foreach(["header","subheader","landscape","portrait","created","UserID","subtemplate","published"] as $key){
			if($info[$key]==$p[$key]){
				$answer['log'][$key] = sprintf("%'.".(82 - strlen($key))."s - <span class='green-txt'>Ok</span>", $p[$key]);
			}else $answer['log'][$key] = sprintf("%'.".(78 - strlen($key))."s - <span class='red-txt'>Failed</span>", $p[$key]);
		}
		print(JSON::encode($answer));
	break;
	case "remove":
		print $mySQL->inquiry("DELETE FROM gb_pages WHERE PageID={str} LIMIT 1", ARG_3)['affected_rows'];
	break;
	default:break;
}
?>