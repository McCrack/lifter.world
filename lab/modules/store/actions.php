<?php

$brancher->auth() or die("Access denied!");

switch(SUBPAGE){
	case "reload":
		$p = JSON::load('php://input');
		
		$where = [];
		$categories = $mySQL->tree("SELECT `parent`,`name`,`PageID` FROM `gb_sitemap`", "name", "parent");
		$cat_list = getNestedCategoriesID($categories, $p['category']);
		$category = $mySQL->single_row("SELECT `PageID` FROM `gb_sitemap` WHERE `name` LIKE '".$p['category']."' LIMIT 1");
		$cat_list[] = $category['PageID'];
		$where[] = "CategoryID IN (".implode(", ", $cat_list).")";
		
		if($p['native']&1) $where[] = "DiscountID IS NOT NULL";
		if($p['native']&2) $where[] = "dumping > 0";
		
		$limit = 24;
		$page = 1;
		
		if(empty($p['filters'])){
			$models = $mySQL->query("
			SELECT SQL_CALC_FOUND_ROWS * FROM `gb_models`
			CROSS JOIN `gb_items` USING(`ItemID`)
			WHERE ".implode(" AND ", $where)."
			ORDER BY gb_models.PageID DESC
			LIMIT 0, ".$limit);
		}else{
			$section = explode("-", $p['filters']);
			foreach($section as $key=>$val){
				if($val>0){
					$where[] = "(`".$key."` & ".$val.")";
				}
			}
			$models = $mySQL->query("
			SELECT SQL_CALC_FOUND_ROWS * FROM `gb_tagination`
			CROSS JOIN `gb_models` USING(`tid`)
			CROSS JOIN `gb_items` USING(`ItemID`)
			WHERE ".implode(" AND ", $where)."
			ORDER BY gb_models.PageID DESC
			LIMIT 0, ".$limit);
		}
		$stikers = "";
		foreach($models as $model){
			$stikers .= "
			<a class='sticker' href='/showcase/".$model['PageID']."/".$model['ItemID']."'>
				<img src='".$model['preview']."'>
				<div class='name'>".$model['name']."</div>
				<div class='options'>
					<span>".date("d M, H:i", $model['created'])."</span>
					<span>".$model['status']."</span>
				</div>
			</a>";
		}
		print($stikers);
		
		$count = $mySQL->single_row("SELECT FOUND_ROWS()");
		$count = reset($count);
		$pagination = "";
		$total = ceil($count/$limit);	// Total pages
		if($total > 1){
			$path = "store/".$p['category']."/".$p['native']."/".$p['filters'];
			$pagination.="
			<a class='selected'>1</a>
			<a href='/".$path."/2'>2</a>
			...
			<a href='/".$path."/".$total."'>".$total."</a>";
		}
		print("<div id='pagination' align='right'>".$pagination."</div>");
	break;
	case "create":
		$PageID = $mySQL->insert("gb_pages", ["type"=>"showcase","created"=>time(),"modified"=>time()]);
		$ItemID = $mySQL->insert("gb_items", ["PageID"=>$PageID]);
		$mySQL->insert("gb_models", ["PageID"=>$PageID, "ItemID"=>$ItemID, "CategoryID"=>PARAMETER, "RelatedCategoryID"=>PARAMETER, "tid"=>2]);
		print($PageID."/".$ItemID);
	break;
	case "add":
		$model = $mySQL->single_row("SELECT * FROM `gb_models` CROSS JOIN `gb_items` USING(`ItemID`) WHERE gb_models.PageID=".PARAMETER." LIMIT 1");
		if(empty($model)){
			$ItemID = $mySQL->insert("gb_items", ["PageID"=>PARAMETER]);
			$mySQL->query("UPDATE `gb_models` SET `ItemID`=".$ItemID." WHERE `PageID`=".PARAMETER." LIMIT 1");
		}else $ItemID = $mySQL->insert("gb_items", [
			"PageID"=>$model['PageID'], 
			"tid"=>$model['tid'], 
			"purchase"=>$model['purchase'], 
			"selling"=>$model['selling'],
			"currency"=>$model['currency'],
			"units"=>$model['units'],
			"outstock"=>$model['outstock']
		]);

		$mySQL->query("UPDATE `gb_pages` SET `modified`=".time()." WHERE `PageID`=".PARAMETER." LIMIT 1");
		
		print($ItemID);
	break;
	case "save":
		$p = JSON::load('php://input');
		if(!empty($p['ItemID'])){
			if(empty($p['filters'])){
				$tid = 2;
			}else{
				$mask = $filterset = [];
				foreach($p['filters'] as $set){
					foreach($set as $section=>$value){
						$filterset[$section] |= $value;
					}
				}
				foreach($filterset as $key=>$val){
					$mask[] = "`".$key."`=".$val;
				}
				$tegination = $mySQL->single_row("SELECT `tid` FROM `gb_tagination` USE INDEX(`section`) WHERE ".implode(" AND ", $mask)." LIMIT 1");
				if(empty($tegination['tid'])){
					$tid = $mySQL->single_row("INSERT INTO `gb_tagination` SET ".implode(", ", $mask));
				}else $tid = $tegination['tid'];
			}
			$images = JSON::stringify($p['images']);
			$mySQL->query("
			UPDATE `gb_items` SET
				`label`='".$p['label']."',
				`tid`=".$tid.",
				`preview`='".$p['preview']."',
				`DiscountID`=".(empty($p['DiscountID']) ? "NULL" : $p['DiscountID']).",
				`dumping`=".$p['dumping'].",
				`outstock`='".$p['outstock']."',
				`purchase`='".$p['purchase']."',
				`selling`='".$p['selling']."',
				`currency`='".$p['currency']."',
				`images` = '".$images."',
				`units`='".$p['units']."'
			WHERE
				`ItemID`='".$p['ItemID']."'
			LIMIT 1");
			
// Складання фільтрів для моделі
			$items = $mySQL->group_rows("SELECT `tid` FROM `gb_items` WHERE `PageID`=".$p['PageID']);
			$fields  = $mySQL->group_rows("SELECT * FROM `gb_tagination` WHERE `tid` IN (".implode(",", $items['tid']).")");
			unset($fields['tid']);
			
			$section = $mask = [];
			foreach($fields as $key=>$field){
				foreach($field as $val){
					$section[$key] |= $val;
				}
				$mask[] = "`".$key."`=".$section[$key];
			}
			$tegination = $mySQL->single_row("SELECT `tid` FROM `gb_tagination` USE INDEX(`section`) WHERE ".implode(" AND ", $mask)." LIMIT 1");
			if(empty($tegination['tid'])){
				$tid = $mySQL->single_row("INSERT INTO `gb_tagination` SET ".implode(", ", $mask));
			}else $tid = $tegination['tid'];
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		}else $tid = "`tid`";
		
		$mySQL->query("
		UPDATE `gb_models` SET
			".(isset($p['reference']) ? ("`ItemID`=".$p['ItemID'].",") : "")."
			CategoryID=".$p['CategoryID'].",
			RelatedCategoryID=".$p['RelatedCategoryID'].",
			tid=".$tid.",
			status='".$p['status']."',
			name='".$p['name']."',
			brand='".$p['brand']."',
			options='".JSON::encode($p['options'])."',
			subtemplate = '".$p['template']."'
		WHERE
			`PageID`='".$p['PageID']."'
		LIMIT 1");

		$mySQL->query("UPDATE gb_pages SET modified=".time()." WHERE PageID=".$p['PageID']." LIMIT 1");

		$mySQL->query("INSERT INTO `gb_keywords` SET tag='".$p['brand']."'");
	break;
	case "save-description":
		$data = gzencode(file_get_contents('php://input'));
		$saved = $mySQL->update("gb_models",  array("description"=>mysql_escape_string($data)), "`PageID`=".PARAMETER);
		print($saved);
	break;
	case "save-stock":
		$p = JSON::load('php://input');
		foreach($p as $stock=>$remainder){
			$remainder = (INT)$remainder;
			$mySQL->query("
			INSERT INTO `gb_stock` SET
				`ItemID`=".PARAMETER.",
				`stock`='".$stock."',
				`remainder`=".$remainder."
			ON DUPLICATE KEY UPDATE
				`remainder`=".$remainder."
			");
			$total += $remainder;
		}
		print($total);
	break;
	case "load-discount":
		$discount = $mySQL->single_row("SELECT * FROM `gb_discounts` WHERE `DiscountID`=".PARAMETER." LIMIT 1");
		$discount['from'] = date("d.m.Y", $discount['from']);
		$discount['to'] = date("d.m.Y", $discount['to']);
		print(JSON::encode($discount));
	break;
	case "save-discount":
		$p = JSON::load('php://input');
		
		$from = explode(".", $p['from']);
		$p['from'] = mktime(0,0,0, (INT)$from[1], (INT)$from[0], (INT)$from[2]);
		$to = explode(".", $p['to']);
		$p['to'] = mktime(0,0,0, (INT)$to[1], (INT)$to[0], (INT)$to[2]);
		
		if(empty($p['DiscountID'])){
			$id = $mySQL->insert("gb_discounts", $p);
		}else{
			$mySQL->query("
			UPDATE `gb_discounts` SET
				`caption`='".$p['caption']."',
				`from`=".$p['from'].",
				`to`=".$p['to'].",
				`essence`='".$p['essence']."',
				`sticker`='".$p['sticker']."',
				`discount`=".$p['discount']."
			WHERE
				`DiscountID`=".$p['DiscountID']." LIMIT 1");
			$id = $p['DiscountID'];
		}
		print($id);
	break;
	case "delete-discount":
		$del = $mySQL->query("DELETE FROM `gb_discounts` WHERE `DiscountID` = ".PARAMETER);
		print($del);
	break;
	case "save-options":
		$data = file_get_contents('php://input');
		$category = $mySQL->single_row("SELECT `PageID` FROM `gb_sitemap` WHERE `name` LIKE '".PARAMETER."' LIMIT 1");
		$mySQL->query("UPDATE `gb_static` SET `optionset`='".$data."' WHERE `PageID`=".$category['PageID']." LIMIT 1");
	break;
	case "save-filterset":
		$p = JSON::load('php://input');
		
		$fields = $mySQL->group_rows("SELECT * FROM `gb_tagination` LIMIT 1");
		$fields = count($fields) - 2;
		foreach($p as $name=>$set){
			$p[$name] = $fSet = [];
			$cnt = count($set);
			for($i=0; $i<$cnt; $i++){
				$translit = translite($set[$i], "_", true);
				$fSet[] = $translit;
				$p[$name][$translit]['value'] = $set[$i];
			}
			$exists = $mySQL->group_rows("SELECT * FROM `gb_filters` WHERE `translit` IN ('".implode("','", $fSet)."')");
			$cnt = count($exists['id']);
			if(is_array($exists) && ($cnt > 0)){
				for($i=0; $i<$cnt; $i++){
					$p[$name][$exists['translit'][$i]]['id'] = $exists['id'][$i];
				}
				$nFliters = array_diff($fSet, $exists['translit']);
			}else $nFliters = &$fSet;
			foreach($nFliters as $translit){
				$id = $mySQL->insert("gb_filters", array("value"=>$p[$name][$translit]['value'], "translit"=>$translit));
				$p[$name][$translit]['id'] = $id;
				$section = ($id/32)>>0;
				if($section > $fields){
					$mySQL->query("ALTER TABLE `gb_tagination` ADD `".$section."` INT UNSIGNED DEFAULT 0");
					$fields++;
				}
			}
		}
		$category = $mySQL->single_row("SELECT `PageID` FROM `gb_sitemap` WHERE `name` LIKE '".PARAMETER."' LIMIT 1");
		$mySQL->query("UPDATE `gb_static` SET `filterset`='".JSON::encode($p)."' WHERE `PageID`=".$category['PageID']." LIMIT 1");
		
	break;
	case "remove-item":
		$del = $mySQL->query("DELETE FROM `gb_items` WHERE `ItemID` = ".PARAMETER);
		print($del);
	break;
	case "remove-model":
		$del = $mySQL->query("DELETE FROM gb_pages WHERE `PageID` = ".PARAMETER);
		if($del){
			$del = $mySQL->query("DELETE FROM gb_models WHERE `PageID` = ".PARAMETER);
			print($del);
		}else print(0);
	break;
	default:break;
}

/******************************************************************************/

function getNestedCategoriesID(&$items, $offset){
	$result = [];
	if(is_array($items[$offset])){
		foreach($items[$offset] as $key=>$val){
			$result[] = $val['PageID'];
			$result = array_merge($result, getNestedCategoriesID($items, $key));
		}
	}
	return $result;
}

?>