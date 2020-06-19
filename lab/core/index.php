<?php

/* CONFIG *************************************************************************************************************************************/

class config{
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
	}
	public function __get($key){
		return $this->list[$key]?$this->list[$key]:$key;
	}
}

/* JSON ***************************************************************************************************************************************/

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

/* FS *****************************************************************************************************************************************/

function copyFolder($source, $dest){
	foreach(scandir($source) as $file){
		if(($file!="." && $file!="..") && is_dir($source."/".$file)){
			mkpath($dest."/".$file);
			copyFolder($source."/".$file, $dest."/".$file);
		}elseif(is_file($source."/".$file)){
			@mkpath( $dest );
			copy($source."/".$file, $dest."/".$file);
		}
	}
}
function mkpath($path){
	$step=array();
	foreach(explode("/", $path) as $item){
		$step[]=$item;
		@mkdir(implode("/",$step));
	}
}
function deletedir($dir){
    foreach(scandir($dir) as $file){
        if($file!="." && $file!=".."){
            if(is_dir($dir."/".$file)){ deletedir($dir."/".$file); }
            elseif(is_file($dir."/".$file)){ unlink($dir."/".$file); }
        }
    }
    return @rmdir($dir);
}
function folderToZip($path, &$zipFile, $local){
	if($zipFile){
		foreach(scandir($path) as $file){
			$fullpath = $path."/".$file;
			if(($file!="." && $file!="..") && is_dir($fullpath)){
				folderToZip($fullpath, $zipFile, $local."/".$file);
			}elseif(is_file($fullpath)) $zipFile->addFile($fullpath, $local."/".$file);
		}
	}else return false;
}

/* EMAIL ****************************************************************************************************************************************/

function sendmail($address, $message, $mode="plain", $theme="Message from the site", $sender){
	if(empty($sender)){
		$sender="site@".$_SERVER['HTTP_HOST'];
	}
	$headers="MIME-Version: 1.0\r\n";
	$headers.="Content-type: text/".$mode."; charset=utf8\r\n";
	if($mode=="plain"){
		$message=wordwrap($message, 70);
	}
	$headers.="From: ".$sender."\r\n";
	$resp = mail($address, "=?utf-8?B?".base64_encode($theme)."?=", $message, $headers);
	return $resp;
}

/* OTHER ********************************************************************************************************************************************************/

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
	$str = preg_replace("/(?(?=\W)[^.-]|^$)/", "", $str);
	return preg_replace("/-{2,}/","-",$str);
}

function round_time($ts, $step){
	return(floor(floor($ts / 60) / 60) * 3600 + floor(date("i", $ts) / $step) * $step * 60);
}

?>