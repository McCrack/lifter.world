<?php

$cache = [];

class feed{
	public static function __callStatic($title, $fields){
		if($r = self::query($title, $fields)){
			$feed = "";
			while($row = $r->fetch_assoc()){
				if($row['title']===PAGE) continue;
				$feed .= "<a href='/".$title."/".$row['title']."' class='sticker'><figure>";
				foreach($row as $field=>$val) $feed .= self::{$field}($val);
				$feed .= "</figure></a> ";
			}
			$r->close();
		}
		return $feed;
	}
	static function blog($offset=0, $limit=6){
		global $mySQL;
		$mySQL->real_query("SELECT `ID`,`preview`,`header`,`subheader`,`created` FROM `gb_blogfeed` CROSS JOIN `gb_pages` USING(`PageID`) WHERE `created`<".time()." AND `published` & 2 GROUP BY `ID` ORDER BY `created` DESC LIMIT ".$offset.", ".$limit);
		if($r = $mySQL->store_result()){
			$feed = "";
			while($row = $r->fetch_assoc()){
				$feed .= "
				<a href='/blog/".$row['ID']."/".translite($row['header'],"-",true)."' class='sticker'><figure>
					<img class='preview' src='".$row['preview']."'>
					<figcaption class='header'>".$row['header']."</figcaption>
					<figcaption class='subheader'>".$row['subheader']."</figcaption>
					<figcaption class='options'>".date("d F", $row['created'])."</figcaption>
				</figure></a> ";
			}
			$r->close();
		}
		return $feed;
	}
	private static function query($root, $fields){
		global $mySQL;
		$mySQL->real_query("SELECT ".implode(",", $fields)." FROM `gb_sitemap` CROSS JOIN `gb_static` USING(`PageID`) WHERE `parent` LIKE '".$root."' ORDER BY `PageID`");
		return $mySQL->store_result();
	}
	public static function optionset($value, $cnt=3){ 
		$optionset = "";
		$options = JSON::parse($value);
		for($i=$cnt; $i--;){
			list($key, $val) = each($options);
			$optionset .= "<div>".$key.": ".$val."</div>";
		}
		return "<figcaption class='optionset'>".$optionset."</figcaption>";
	}
	public static function preview($value){ return "<img class='preview' src='".$value."'>"; }
	public static function title($value){ return "<figcaption class='header'>".$GLOBALS['wdl']->{$value}."</figcaption>"; }
	public static function header($value){ return "<figcaption class='header'>".$value."</figcaption>"; }
	public static function description($value){ return "<figcaption class='subheader'>".$value."</figcaption>"; }
}
class material{
	public static function __callStatic($title, $fields){
		global $mySQL;
		$row = $mySQL->getMaterial($title);
		return gzdecode( $row['content'] );
	}
	static function auto(){
		global $page;
		return gzdecode( $page['content'] );
	}
}
class hashmenu{
	public static function __callStatic($name, $arguments){
		global $cache;
		if(empty($cache['hashmenu'][$name])) $cache['hashmenu'][$name] = self::query($name, "/".$name);
		return $cache['hashmenu'][$name];
	}
	static function root(){
		global $cache;
		if(empty($cache['hashmenu']['root'])) $cache['hashmenu']['root'] = self::query();
		return $cache['hashmenu']['root'];
	}
	static function query($parent="root", $root=""){
		$menu = "";
		global $mySQL;
		$mySQL->real_query("SELECT `title` FROM `gb_sitemap` WHERE `parent` LIKE '".$parent."' AND `published` & 2");
		if($r = $mySQL->store_result()){
			while($row = $r->fetch_assoc()) $menu .= "<a href='".$root."/#".$row['title']."'>".$GLOBALS['wdl']->{$row['title']}."</a>";
			$r->free();
		}
		return $menu;
	}
}
class menu{
	public static function __callStatic($name, $arguments){
		global $cache;
		if(empty($cache['hashmenu'][$name])) $cache['hashmenu'][$name] = self::query($name, "/".$name);
		return $cache['hashmenu'][$name];
	}
	static function root(){
		global $cache;
		if(empty($cache['hashmenu']['root'])) $cache['hashmenu']['root'] = self::query();
		return $cache['hashmenu']['root'];
	}
	static function query($parent="root", $root=""){
		$menu = "";
		global $mySQL;
		$mySQL->real_query("SELECT `title` FROM `gb_sitemap` WHERE `parent` LIKE '".$parent."' AND `published` & 2");
		if($r = $mySQL->store_result()){
			while($row = $r->fetch_assoc()) $menu .= "<a href='".$root."/".$row['title']."'>".$GLOBALS['wdl']->{$row['title']}."</a>";
			$r->free();
		}		
		return $menu;
	}
}
?>