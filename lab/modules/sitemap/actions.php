<?php
switch(ARG_2){
	case "reload-tree":
		$rows = $mySQL->getTree("name", "parent","SELECT parent,name,header,language,published FROM gb_sitemap WHERE language LIKE '".ARG_3."'");
		print staticTree($rows, "root", ARG_4);
	break;
	case "assort":
		$p = JSON::load('php://input');
		foreach($p as $i=>$id){
			$mySQL->inquiry("UPDATE gb_sitemap SET SortID={int} WHERE PageID={int}", $i,$id);
		}
	break;
	case "add-page":
		$p = JSON::load('php://input');
		$timestamp = time();
		$PageID = $mySQL->inquiry("INSERT INTO gb_pages (created,modified,type) VALUES ({int},{int},{str})", $timestamp, $timestamp, $p['type'])['last_id'];
		if(empty($PageID)){
			print("unknow error");
		}else{
			$mySQL->inquiry(
				"INSERT INTO gb_sitemap (PageID,parent,name,header,soundex,language) VALUES ({int},{str},{str},{str},{str},{str})",
				$PageID,$p['parent'],$p['name'],$p['header'],soundex($p['name']),$p['language']
			);
			$page = $mySQL->getRow("SELECT PageID FROM gb_sitemap WHERE name LIKE {str} AND language LIKE {str} LIMIT 1", $p['name'],$p['language']);
			if(empty($page)){
				print("unknow error");
			}else{
				$mySQL->inquiry("INSERT INTO gb_static SET PageID={int}", $PageID);
				print($PageID);
			}
		}
	break;
	case "remove-page":
		print $mySQL->inquiry("DELETE FROM gb_pages WHERE PageID={str} LIMIT 1", ARG_3)['affected_rows'];
	break;
	case "save-description":
		$data = file_get_contents('php://input');
		print $mySQL->inquiry("UPDATE gb_static SET description={str} WHERE PageID={int} LIMIT 1", $data, ARG_3)['affected_rows'];
	break;
	case "save-subheader":
		$data = file_get_contents('php://input');
		print $mySQL->inquiry("UPDATE gb_sitemap SET subheader=>{str} WHERE PageID={int} LIMIT 1", $data, ARG_3)['affected_rows'];
	break;
	case "save-header":
		$data = file_get_contents('php://input');
		print $mySQL->update("UPDATE gb_sitemap SET header={str} WHERE PageID={int} LIMIT 1", $data,ARG_3)['affected_rows'];
	break;
	case "save-context":
		$data = file_get_contents('php://input');
		print $mySQL->update("UPDATE gb_static SET context={str} WHERE PageID={int} LIMIT1", $data,ARG_3)['affected_rows'];
	break;
	case "save-metadata":
		$p = JSON::load('php://input');
		
		$time = time();		
		
		$mySQL->inquiry("UPDATE gb_pages SET type={str}, modified={int} WHERE PageID={int} LIMIT 1", $p['type'],$time,$p['id']);
		$mySQL->inquiry("UPDATE gb_sitemap SET 
			name={str},
			parent={str},
			soundex={str},
			preview={str},
			header={str},
			published={str},
			SetID=".$p['SetID']."
			WHERE PageID={int} LIMIT 1",
		$p['name'],$p['parent'],soundex($p['name']),$p['preview'],$p['header'],$p['published'],$p['id']);

		$answer = [];
		$sitemap = $mySQL->getRow("SELECT PageID,SetID,header,published,name,parent,preview FROM gb_sitemap WHERE PageID={int} LIMIT 1",$p['id']);
		foreach($sitemap as $key=>$val){
			if($p[$key]==$val){
				$answer[$key] = sprintf("%'.".(70 - strlen($key))."s - <span class='green-txt'>Ok</span>", $val);
			}else $answer[$key] = sprintf("%'.".(66 - strlen($key))."s - <span class='red-txt'>Failed</span>", $val);
		}
		$answer['PageID'] = $p['id'];
		$p['options'] = JSON::encode($p['options']);
		
		$mySQL->inquiry(
			"UPDATE gb_static SET module={str}, template={str}, title={str}, context={str}, description={str}, optionset={str} WHERE PageID={int}",
			$p['module'],$p['template'],$p['title'],$p['context'],$p['description'],$p['options'], $p['id']
		);

		$static = $mySQL->getRow("SELECT module,template,title,context FROM gb_static WHERE PageID = {int} LIMIT 1", $p['id']);
		foreach($static as $key=>$val){
			if($p[$key]==$val){
				$answer[$key] = sprintf("%'.".(70 - strlen($key))."s - <span class='green-txt'>Ok</span>", $val);
			}else $answer[$key] = sprintf("%'.".(66 - strlen($key))."s - <span class='red-txt'>Failed</span>", $val);
		}
		print(JSON::stringify($answer));
	break;
	case "save-content":
		$mySQL->inquiry("UPDATE gb_pages SET modified=>{int} WHERE PageID={int} LIMIT 1", time(), ARG_3);

		$data = gzencode(file_get_contents('php://input'));
		print $mySQL->inquiry("UPDATE gb_static SET content={str} WHERE PageID={int} LIMIT 1", $data,ARG_3)['affected_rows'];
	break;
	case "schemes":
		$pages = $mySQL->getGroup("SELECT PageID FROM gb_sitemap WHERE name LIKE (SELECT name FROM gb_sitemap WHERE PageID = {int})", ARG_3)['PageID'];
		print $mySQL->inquiry("UPDATE gb_static SET microdata={str} WHERE PageID IN ({arr})", file_get_contents('php://input'), $pages)['affected_rows'];
	break;
	default:break;
}

/****************************************************************************/

function staticTree(&$items, $offset="root", $part="sitemap"){ if(is_array($items[$offset])):?>
	<div class="root">
	<?foreach($items[$offset] as $key=>$val):?>
		<a href="/<?=$part?>/<?=($val['language'].'/'.$val['name'])?>" class="<?if($val['published']==='Published'):?>published-txt<?endif?>"><?=(empty($val['header'])?$val['name']:$val['header'])?></a>
		<?staticTree($items, $key, $part);
	endforeach?>
	</div>
<?endif;}

?>