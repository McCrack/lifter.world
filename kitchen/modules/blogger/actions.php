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
		if(defined("ARG_3")){
			$language = ARG_3;
		}else{
			$cng = new config("../".BASE_FOLDER."/".$config->{"config file"});
			$language = $cng->language;
		}

		$PageID = $mySQL->inquiry("INSERT INTO gb_pages SET type='post', created={int}, modified={int}", $time,$time)['last_id'];
		$PostID = $mySQL->getRow("SELECT MAX(ID) AS ID FROM gb_blogfeed LIMIT 1")['ID'];
		(INT)$PostID++;
		$mySQL->inquiry("INSERT INTO gb_blogfeed (PageID, ID, language, header) VALUES ({int},{int},{str},'New Article')", $PageID,$PostID,$language);
		$mySQL->inquiry("INSERT INTO gb_blogcontent SET PageID={int}, UserID={int}", $PageID,USER_ID);
		$mySQL->inquiry("INSERT INTO gb_amp SET PageID={int}", $PageID);
		//$mySQL->inquiry("INSERT INTO gb_ina SET PageID={int}", $PageID);

		$year = date("Y");
		$month = strtolower(date("F"));
		//mkpath("../img/data/".$year."/".$month."/".$PostID);
		print JSON::encode([
			"ID"=>$PostID,
			"PageID"=>$PageID,
			"year"=>$year,
			"month"=>$month,
			"language"=>$language
		]);
	break;
	case "clone-post":
		$p = JSON::load('php://input');
		$prototype = $mySQL->getRow("
		SELECT
			ID,SetID,preview,video,category,created,Ads,
			gb_amp.PageID AS amp
		FROM gb_pages
		CROSS JOIN gb_blogfeed USING(PageID)
		LEFT JOIN gb_amp USING(PageID)
		WHERE PageID={int} LIMIT 1",
		ARG_3);
		$PageID = $mySQL->inquiry("INSERT INTO gb_pages SET type='post', created={int}, modified={int}", $prototype['created'],time())['last_id'];		
		$mySQL->inquiry("
		INSERT INTO gb_blogfeed (
			PageID,
			ID,
			SetID,
			language,
			header,
			subheader,
			preview,
			video,
			category,
			Ads
		) VALUES ({int},{int},{int},{str},{str},{str},{str},{str},{str},{str})",
			$PageID,
			$prototype['ID'],
			$prototype['SetID'],
			$p['language'],
			$p['header'],
			$p['subheader'],
			$prototype['preview'],
			$prototype['video'],
			$prototype['category'],
			$prototype['Ads']
		);
		$mySQL->inquiry("
		INSERT INTO gb_blogcontent SELECT {int},content,subtemplate,UserID FROM gb_blogcontent WHERE PageID={int}", $PageID,ARG_3);

		if(!empty($prototype['amp'])) $mySQL->inquiry("INSERT INTO gb_amp SET PageID={int}", $PageID);
		//if(!empty($prototype['ina'])) $mySQL->inquiry("INSERT INTO gb_ina SET PageID={int}", $PageID);

		print JSON::encode([
			"PageID"=>$PageID,
			"language"=>$p['language']
		]);
	break;
	case "save-metadata":
		$p = JSON::load('php://input');

		$keywords = $mySQL->getGroup("SELECT KeyWORD FROM blog_vs_keywords CROSS JOIN gb_keywords USING(KeyID) WHERE PageID={int}", $p['PageID'])['KeyWORD'];
		$keywords = is_array($keywords) ? array_diff($p['keywords'], $keywords) : $p['keywords'];

		if(!empty($keywords)) $mySQL->inquiry("INSERT INTO gb_keywords (KeyWORD) VALUES ('".implode("'),('", $keywords)."') ON DUPLICATE KEY UPDATE rating=rating+1");
		$IDs = $mySQL->getGroup("SELECT KeyID FROM gb_keywords WHERE KeyWORD IN ('".implode("','", $p['keywords'])."')", $p['PageID'])['KeyID'];
		$mySQL->inquiry("DELETE FROM blog_vs_keywords WHERE PageID = {int}", $p['PageID']);
		
		if(!empty($IDs)) $mySQL->inquiry("INSERT INTO blog_vs_keywords (PageID,KeyID) VALUES (".$p['PageID'].",".implode("),(".$p['PageID'].",", $IDs).")");
		
		$p['header'] = base64_decode($p['header']);
		$p['subheader'] = base64_decode($p['subheader']);

		$mySQL->inquiry("UPDATE gb_blogfeed SET SetID=".$p['SetID'].", {set} WHERE PageID={int} LIMIT 1", [
			"header"=>$p['header'],
			"subheader"=>$p['subheader'],
			"preview"=>$p['preview'],
			"video"=>$p['video'],
			"category"=>$p['category'],
			"Ads"=>$p['Ads'],
			"published"=>$p['published']
		], $p['PageID']);
		
		$set = $mySQL->parse("{set}",[
			"UserID"=>$p['UserID'],
			"subtemplate"=>$p['subtemplate']
		]);
		$mySQL->inquiry("UPDATE gb_blogcontent SET {prp} WHERE PageID={int} LIMIT 1", $set,$p['PageID']);
		$mySQL->inquiry("UPDATE gb_pages SET created={int},modified={int} WHERE PageID={int} LIMIT 1", $p['created'],time(), $p['PageID']);

		$answer = [
			"log"=>["PageID"=>$p['PageID']],
			"url"=>PROTOCOL."://".HOST."/".translite($p['header'])."-".$p['PageID']
		];
		$info = $mySQL->getRow("SELECT * FROM gb_blogfeed CROSS JOIN gb_pages USING(PageID) CROSS JOIN gb_blogcontent USING(PageID) WHERE PageID={int} LIMIT 1", $p['PageID']);
		foreach(["ID","header","subheader","preview","video","category","created","UserID","subtemplate","published"] as $key){
			if($info[$key]==$p[$key]){
				$answer['log'][$key] = sprintf("%'.".(82 - strlen($key))."s - <span class='green-txt'>Ok</span>", $p[$key]);
			}else $answer['log'][$key] = sprintf("%'.".(78 - strlen($key))."s - <span class='red-txt'>Failed</span>", $p[$key]);
		}

		print(JSON::encode($answer));
	break;
	case "save-content":
		$mySQL->inquiry("UPDATE gb_pages SET modified={int} WHERE PageID={int}", time(),ARG_3);

		$data = gzencode( file_get_contents('php://input') );
		$mySQL->inquiry("UPDATE gb_blogcontent SET content={str} WHERE PageID={int} LIMIT 1", $data,ARG_3);
		
		$saved = $mySQL->getRow("SELECT content FROM gb_blogcontent WHERE PageID={int} LIMIT 1", ARG_3)['content'];
		print(strcmp($data, $saved) ? 0 : ARG_3);
	break;
	case "save-amp":
		$data = gzencode( file_get_contents('php://input') );
		$mySQL->inquiry("
		INSERT INTO gb_amp SET
			PageID={int},
			content={str}
		ON DUPLICATE KEY UPDATE content={str}",
		ARG_3,$data,$data);

		$saved = $mySQL->getRow("SELECT content FROM gb_amp WHERE PageID={int} LIMIT 1", ARG_3)['content'];
		print(strcmp($data, $saved) ? 0 : ARG_3);
	break;
	case "save-ina":
		$data = gzencode( file_get_contents('php://input') );
		$mySQL->inquiry("
		INSERT INTO gb_ina SET
			PageID={int},
			content={str}
		ON DUPLICATE KEY UPDATE content={str}",
		ARG_3,$data,$data);

		$saved = $mySQL->getRow("SELECT content FROM gb_ina WHERE PageID={int} LIMIT 1", ARG_3)['content'];
		print(strcmp($data, $saved) ? 0 : ARG_3);
	break;
	case "remove":
		print $mySQL->inquiry("DELETE FROM gb_pages WHERE PageID={str} LIMIT 1", ARG_3)['affected_rows'];
	break;
	case "drop-amp":
		$mySQL->inquiry("DELETE FROM gb_amp WHERE PageID={int} LIMIT 1", ARG_3);
	break;
	case "drop-ina":
		$mySQL->inquiry("DELETE FROM gb_ina WHERE PageID={int} LIMIT 1", ARG_3);
	break;
	default:break;
}
?>