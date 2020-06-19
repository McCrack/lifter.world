<?php

require_once "defines.php";

if(file_exists("modules/".SECTION."/config.init")){
	$conf = new config("modules/".SECTION."/config.init");
	$groups = preg_split("/,\s*/", $conf->access['value'], -1, PREG_SPLIT_NO_EMPTY);

	if(in_array(USER_GROUP, $groups) || ($conf->{'free access'}['value']=="YES")){
		
		Actions::{array_shift($params)}($params);

	}else die("access denied");
}else exit("Not Found");

class Actions{
	public static function __callStatic($command, $args){
		$args = $args[0];
		define("COMMAND", $command);
		if(file_exists("modules/".SECTION."/actions.php")){
			foreach($args as $i=>$arg) define("ARG_".($i+1), $arg);
			include_once("modules/".SECTION."/actions.php");
		}
	}
	
	/*~~~~~~~ FS ~~~~~~~*/
	public static function mkpath(){
		mkdir($GLOBALS['HTTP_RAW_POST_DATA'], 0777, true);
	}
	public static function rmpath(){
		$lst = JSON::load("php://input");
		foreach($lst as $item){
			if(is_dir($item['path'])){
				deletedir($item['path']);
			}elseif(is_file($item['path'])) unlink($item['path']);
		}
	}
	public static function tozip(){
		$path = explode("/", $GLOBALS['HTTP_RAW_POST_DATA']);
		$zip = new ZipArchive;
		$zip->open($GLOBALS['HTTP_RAW_POST_DATA'].".zip", ZipArchive::CREATE);
		folderToZip($GLOBALS['HTTP_RAW_POST_DATA'], $zip, array_pop($path));
		$zip->close();
	}
	public static function unzip(){
		$type = mime_content_type($GLOBALS['HTTP_RAW_POST_DATA']);
		if($type==="application/zip"){
			$zip = new ZipArchive;
			if($zip->open($GLOBALS['HTTP_RAW_POST_DATA'])){
				$path = explode("/", $GLOBALS['HTTP_RAW_POST_DATA']);
				$zip->extractTo( implode("/", array_slice($path, 0, -1)) );
				$zip->close();
			}
		}
		print $GLOBALS['HTTP_RAW_POST_DATA'];
	}
	public static function fs_rename(){
		$p = JSON::load('php://input');
		rename($p['old'], $p['new']);
	}
	public static function copy(){
		$p = JSON::load('php://input');
		foreach($p as $src){
			$file = basename($src);

			if(is_file($src)){
				copy($src, $_GET['path']."/".$file );
			}elseif(is_dir($src)) copyFolder($src, $_GET['path']."/".$file );
		}
	}
	public static function move(){
		$p = JSON::load('php://input');
		foreach($p as $src){
			$file = basename($src);
			rename($src, $_GET['path']."/".$file);
		}
	}
	public static function download(){
		$fsize = filesize($_GET['path']);
		header('Pragma: public');
		header("Content-Length: ".$fsize); 
		header("Content-Type: ".mime_content_type($_GET['path']));

		$file = explode("/", $_GET['path']);
		header("Content-Disposition: attachment; filename=".array_pop($file));
		header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');

		readfile($_GET['path']);
	}
	public static function upload(){
		$data = $GLOBALS['HTTP_RAW_POST_DATA'];
		$path = $_GET['path'];
		
		if(empty($_GET['seek'])){				
			$size = file_put_contents($_GET['path']."/".$_GET['file'], $data);
		}else{
			$file = fopen($_GET['path']."/".$_GET['file'], "a");
			$size = fwrite($file, $data);
			fclose($file);
		}
		$size = ($size/1022)>>0;
		printf("<samp>%s%'.".(60 - strlen($_GET['file']))."d KB</samp>", $_GET['file'], $size);
	}
	public static function import(){
		$p = JSON::load('php://input');	// Get images list
		foreach($p as $itm){
			$file = file_get_contents($itm['url']);
			file_put_contents($_GET['path']."/".$itm['filename'], $file);
		}
	}
	/*~~~~~~~~~~~~~~~~~~*/
	/*~~~~ WORDLIST ~~~~*/
	public static function ad_wordlist(){
		$p = JSON::load('php://input');
		@mkdir("../".$p['domain']."/localization", 0777, true);
		if(file_exists("../".$p['domain']."/localization/".$p['name'].".json")){
			print("Wordlist already exists.");
		}else{

			$pattern = [];
			if(file_exists("../".$p['domain']."/config.init")){
				$cng = new config("../".$p['domain']."/config.init");
				foreach($cng->languageset as $lang){
					$pattern[$lang] = [""=>""];
				}
			}
			print JSON::save("../".$p['domain']."/localization/".$p['name'].".json", $pattern);
		}
	}
	public static function rm_wordlist($args){
		print unlink("../".$args[0]."/localization/".$args[1].".json");
	}
	public static function sv_wordlist($args){
		print file_put_contents("../".$args[0]."/localization/".$args[1].".json", $GLOBALS['HTTP_RAW_POST_DATA']);
	}
	public static function sh_key_wordlist(){
		$p = JSON::load('php://input');
		$wordlist = JSON::load($p['path'])?>
		<?foreach($wordlist as $lang=>$words):?>
			<tr><th><?=$lang?></th><td contenteditable="true"><?=$words[$p['key']]?></td></tr>
		<?endforeach;
	}
	public static function sv_key_wordlist(){
		$p = JSON::load('php://input');
		$wordlist = JSON::load($p['path']);
		foreach($wordlist as $key=>&$lang){
			$lang = array_merge($lang, $p['wordlist'][$key]);
		}
		JSON::save($p['path'], $wordlist);
	}
	/*~~~~~~~~~~~~~~~~~~*/
	/*~~~~~ STAFF ~~~~~~*/
	public static function rm_user($args){
		if($args[0]) print $GLOBALS['mySQL']->inquiry("DELETE FROM gb_staff WHERE UserID={int} LIMIT 1", $args[0]);
	}
	public static function gt_user($args){
		$user = $GLOBALS['mySQL']->getRow("SELECT * FROM gb_staff LEFT JOIN gb_community USING(CommunityID) WHERE UserID={int} LIMIT 1", $args[0]);
		print JSON::encode($user);
	}
	public static function sv_user($args){
		$p = JSON::load('php://input');
		if($args[0]){
			$set = [
				"Login"=>$p['login'],
				"Passwd"=>$p['passwd'],
				"Group"=>$p['group'],
				"Departament"=>$p['departament'],
				"Name"=>$p['name'],
				"Email"=>$p['email'],
				"Phone"=>$p['phone']
			];
			if(!empty($p['settings'])) $set['settings'] = JSON::encode($p['settings']);
			$GLOBALS['mySQL']->inquiry("UPDATE gb_staff LEFT JOIN gb_community USING(CommunityID) SET {set} WHERE UserID = {int}", $set, $args[0]);
		}else{
			
			$CommunityID = $GLOBALS['mySQL']->inquiry("INSERT INTO gb_community SET {set}", [
				"Email"=>$p['email'],
				"Name"=>$p['name'],
				"Phone"=>(INT)$p['phone']
			])['last_id'];

			print $GLOBALS['mySQL']->inquiry("INSERT INTO gb_staff SET {set}", [
				"CommunityID"=>$CommunityID,
				"Login"=>$p['login'],
				"Passwd"=>$p['passwd'],
				"Group"=>$p['group'],
				"Departament"=>$p['departament'],
				"settings"=>JSON::encode([
					"General"=>[
						"language"=>$config->language
					]
				])
			])['last_id'];
		}
	}
	/*~~~~~~~~~~~~~~~~~~*/
	/*~~~~ MEDIASET ~~~~*/
	public static function ad_mediaset(){
		$p = JSON::load("php://input");
		print $GLOBALS['mySQL']->inquiry("INSERT INTO gb_media SET {set}", [
			"Name"=>$p['Name'],
			"language"=>$p['language'],
			"Category"=>$p['Category'],
			"Mediaset"=>JSON::encode($p['Mediaset'])
		])['last_id'];
	}
	public static function sv_mediaset($args){
		print $GLOBALS['mySQL']->inquiry("UPDATE gb_media SET Mediaset={str} WHERE SetID={int} LIMIT 1",$GLOBALS['HTTP_RAW_POST_DATA'],$args[0])['affected_rows'];
	}
	public static function rm_mediaset($args){
		print $GLOBALS['mySQL']->inquiry("DELETE FROM gb_media WHERE SetID={int} LIMIT 1", $args[0])['affected_rows'];
	}
	/*~~~~~~~~~~~~~~~~~~*/
	/*~~~ CUSTOMIZER ~~~*/
	public static function sv_customize_form(){
		file_put_contents("modules/customizer/form.html", $GLOBALS['HTTP_RAW_POST_DATA']);
	}
	public static function sv_customize_options($args){
		$GLOBALS['mySQL']->inquiry("UPDATE gb_pages SET customizer={str} WHERE PageID = {int}", JSON::encode($_POST), $args[0]);
	}
	/*~~~~~~~~~~~~~~~~~~*/
	/*~~~ COMMUNITY ~~~~*/
	public static function sv_citizen($args){
		$GLOBALS['mySQL']->inquiry("UPDATE gb_community SET options={str} WHERE CommunityID = {int} LIMIT 1", $GLOBALS['HTTP_RAW_POST_DATA'], $args[0])['affected_rows'];
	}
	public static function ad_to_staff($args){
		print $GLOBALS['mySQL']->inquiry("INSERT INTO gb_staff SET CommunityID={int}", $args[0])['last_id'];		
	}
	public static function ch_citizen_review($args){
		$GLOBALS['mySQL']->inquiry("UPDATE gb_community SET review={str} WHERE CommunityID = {int} LIMIT 1", $GLOBALS['HTTP_RAW_POST_DATA'], $args[0])['affected_rows'];
	}
}

?>