<?php

/* DATA **************************************************/

class Data{
	public $SiteName,$root,$logo,$canonical;
	public $data=[],$languageset=[],$schemes=[];
	public function __construct($list){
		$this->SiteName = $GLOBALS['config']->{"site name"};
		$this->root = 
		$this->canonical = PROTOCOL."://".$_SERVER['HTTP_HOST'];
		$this->logo = $this->root."/".$GLOBALS['config']->logo;
		$id = array_pop(explode("-", ROOT));
		if(is_numeric($id)){
			$this->data = $GLOBALS['mySQL']->getRow("
			SELECT
				name,
				module,
				template,
				microdata
			FROM gb_sitemap
			CROSS JOIN gb_static USING(PageID)
			WHERE name LIKE (SELECT type FROM gb_pages WHERE PageID={int} LIMIT 1)
			ORDER BY (POW(2,language-1) & {int}) LIMIT 1",
			$id, LANG_MASK);
		}else $GLOBALS['mySQL']->status = [];

		if($GLOBALS['mySQL']->status['affected_rows']>0){
			define("PAGE_ID", $id);
			$subpage = $this->{$this->data['name']}();

			$this->data = array_merge($this->data, $subpage);
			$this->data['name'] = translite($this->data['header'])."-".$this->data['PageID'];
			$this->title = implode(" - ",[
				$this->data['header'],
				$this->SiteName
			]);
			$this->data['description'] = $this->data['subheader'];
			$this->data['Created_ISO_8601'] = date("c", $this->data['created']);
			$this->data['Modified_ISO_8601'] = date("c", $this->data['modified']);

			$this->data['author'] = $GLOBALS['mySQL']->getRow("SELECT Name FROM gb_staff LEFT JOIN gb_community USING(CommunityID) WHERE UserID={int} LIMIT 1", $this->data['UserID'])['Name'];
			
		}else{
			/*~~~~~~~~~~~~~~~*/
			$KeyID = $GLOBALS['mySQL']->getRow("SELECT KeyID FROM gb_keywords WHERE KeyWORD LIKE {str}", ROOT)['KeyID'];
			if($GLOBALS['mySQL']->status['affected_rows']>0){
				$this->data = $GLOBALS['mySQL']->getMaterial($GLOBALS['config']->{"home page"}, ["*"]);
				define("KEY_ID", $KeyID);
			}else $this->data = $GLOBALS['mySQL']->getMaterial(ROOT, ["*"]);
			/*~~~~~~~~~~~~~~~*/
			//$this->data = $GLOBALS['mySQL']->getMaterial(ROOT, ["*"]);

			if($GLOBALS['mySQL']->status['affected_rows']>0){
				$this->title = implode(" - ",[
					$this->data['title'],
					$this->SiteName
				]);
				$this->data['category'] = $this->data['parent'];
				$this->data['Created_ISO_8601'] = date("c", $data->created);
				$this->data['Modified_ISO_8601'] = date("c", $data->modified);
			}else{
				$this->data = $GLOBALS['mySQL']->getMaterial("404", ["*"]);
				header('HTTP/1.0 404 Not Found');
			}
		}
		foreach(explode(",", $this->data['microdata']) as $scheme) if(file_exists("schemes/".$scheme.".json")){
			try{
				$microdata = JSON::load("schemes/".$scheme.".json");
				$this->buildScheme($microdata);
				$this->schemes[$scheme] = $microdata;
				//print_r($this->schemes);
			}catch(Exception $e){}
		}
	}
	public function __call($named, $args){
		
		
	}

	/*~~~~~~~*/
	
	private function story(){
		if($GLOBALS['mySQL']->status['affected_rows']>0){
			return $GLOBALS['mySQL']->getRow("SELECT * FROM gb_pages CROSS JOIN gb_stories USING(PageID) CROSS JOIN gb_blogcontent USING(PageID) WHERE PageID={int} LIMIT 1", PAGE_ID);
		}else{
			header('HTTP/1.0 404 Not Found');
			return $GLOBALS['mySQL']->getMaterial("404", ["*"]);
		}
	}
	private function post(){
		if($GLOBALS['mySQL']->status['affected_rows']>0){
			return $GLOBALS['mySQL']->getRow("SELECT * FROM gb_pages CROSS JOIN gb_blogfeed USING(PageID) CROSS JOIN gb_blogcontent USING(PageID) WHERE PageID={int} LIMIT 1", PAGE_ID);
		}else{
			header('HTTP/1.0 404 Not Found');
			return $GLOBALS['mySQL']->getMaterial("404", ["*"]);
		}
	}
	
	/*~~~~~~~*/

	private function buildScheme(&$obj){
		foreach($obj as $key=>&$itm){
			if( is_array($itm) ){
				$this->buildScheme($itm);
			}elseif( preg_match("@^md:(.*)@i", $itm, $matches) ){
				$itm = $this->{$matches[1]};
			}
		}
	}
	/*~~~~~~~*/
	public function __get($key){
		return isset($this->data[$key]) ? $this->data[$key] : $key;
	}
	public function __set($key,$val){
		$this->data[$key] = $val;
	}
	public function getContent(){
		return gzdecode($this->data['content']);
	}
}

/* CONFIG **********************************************************/

class Config{
	public $list=array();
	public function __construct($path="config.init"){
		$this->list=JSON::load($path);
		if(empty($this->list)){ exit("<b>ERROR:</b> config file not found."); }		
		foreach($this->list as $section=>$items){
			foreach($items as $key=>$val){
				$this->{$key}=$val['value'];
			}
		}
		$this->{"languageset"} = $this->list['general']['language']['valid'];
		$this->{"localityset"} = $this->list['general']['locality']['valid'];
	}
	public function __get($key){
		return $this->list[$key]?$this->list[$key]:$key;
	}
}

/* JSON ************************************************************/

class JSON{
	public static function save($path, &$array){
		if(is_string($array)){
			$json=$array;
		}elseif(is_array($array)||is_object($array)){
			$json=self::traversing($array);
		}		
		return file_put_contents($path, $json);
	}
	public static function load($path, $assoc=true){
		$str=file_get_contents($path);
		return json_decode($str, $assoc);
	}
	public static function parse($str, $assoc=true){ return json_decode($str, $assoc); }
	public static function encode($str){ return json_encode($str, JSON_UNESCAPED_UNICODE); }
	public static function stringify($array){ return self::traversing($array); }
	private static function traversing($value){
		if(is_int($value)){
			return (string)$value;   
		}elseif(is_string($value)){
			$value=str_replace(array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t"), array('\\\\', '\/', '\"', '\r', '\n', '\b', '\f', '\t'), $value);
			$convmap=array(0x80, 0xFFFF, 0, 0xFFFF);
			$result="";
			for($i=mb_strlen($value); $i--;){
				$mb_char = mb_substr($value, $i, 1);
				if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)){
					$result = sprintf("\\u%04x", $match[1]) . $result;
				}else $result = $mb_char . $result;
			}
			return '"' . $result . '"';   
		}elseif(is_float($value)){ return str_replace(",", ".", $value);         
		}elseif(is_null($value)){ return 'null';
		}elseif(is_bool($value)){ return $value ? 'true' : 'false';
		}elseif(is_array($value)){
			$keys=array_keys($value);
			$with_keys=array_keys($keys)!==$keys;
		}elseif(is_object($value)){
			$with_keys=true;
		}else return '';
		$result=array();
		if($with_keys){
			foreach($value as $key=>$v){
				$result[]=self::traversing((string)$key).':'.self::traversing($v);    
			}
			return '{'.implode(',', $result).'}';     
		}else{
			foreach ($value as $key=>$val) {
				$result[]=self::traversing($val);    
			}
			return '['.implode(',', $result).']';
		}
	}
}

/* OTHER ***********************************************************/


function translite($str=""){
	$dictionary=[
	"а"=>"a",	"б"=>"b",	"в"=>"v",	"г"=>"g",	"ґ"=>"g",	"д"=>"d",
	"е"=>"e",	"є"=>"ye",	"ж"=>"zh",	"з"=>"z",	"и"=>"i",	"і"=>"i",
	"ї"=>"yi",	"й"=>"y",	"к"=>"k",	"л"=>"l",	"м"=>"m",	"н"=>"n",
	"о"=>"o",	"п"=>"p",	"р"=>"r",	"с"=>"s",	"т"=>"t",	"у"=>"u",
	"ф"=>"f",	"х"=>"h",	"ы"=>"y",	"э"=>"e",	"ё"=>"e",	"ц"=>"ts",
	"ч"=>"ch",	"ш"=>"sh",	"щ"=>"shch","ю"=>"yu",	"я"=>"ya",	"ь"=>"",
	"ъ"=>"",	" "=>"-"];

	$str = mb_strtolower( trim($str), "UTF-8");
	if(preg_match("/і|ї|ґ|є/",$str)){
		$dictionary['г'] = "h";
		$dictionary['и'] = "y";
		$dictionary['х'] = "kh";
	}
	$str = strtr($str, $dictionary);
	$str = preg_replace("/[^a-z0-9_.-]/", "", $str);
	return preg_replace("/-{2,}/","-",$str);
}

function check_browser_language($set, $language){
	if(array_key_exists("HTTP_ACCEPT_LANGUAGE", $_SERVER)){
		$subset = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if(!empty($subset)){
			if(preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $subset, $subset)){
				$subset = array_combine($subset[1], $subset[2]);
				foreach($subset as $n => $v){
					$n = strtok($n, '-');
					if(in_array($n, $set)){
						$language = $n;
						break;
					}
				}
			}
		}
	}return $language;
}
function round_time($ts, $step){
	return(floor(floor($ts / 60) / 60) * 3600 + floor(date("i", $ts) / $step) * $step * 60);
}

function getProtocol(){
	if(isset($_SERVER['HTTPS'])){
		if(("on" == strtolower($_SERVER['HTTPS'])) || ("1" == $_SERVER['HTTPS'])){
			$protocol = "https";
		}
	}elseif(isset($_SERVER['SERVER_PORT']) && ("443" == $_SERVER['SERVER_PORT'])){
		$protocol = "https";
	}else $protocol = "http";
	return $protocol;
}

?>