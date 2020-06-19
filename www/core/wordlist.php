<?php

class Wordlist{
	public $dictionary=array();
	public function __construct($wordlist="", $lang=USER_LANG){
		if(is_array($wordlist)){
			foreach($wordlist as $file){
				if(file_exists("localization/".$file.".json")){
					$subwl = JSON::load("localization/".$file.".json");
					$this->dictionary = array_merge($this->dictionary, $subwl[$lang]);
				}
			}
		}elseif(empty($wordlist)){
			foreach(scandir("localization") as $file){
				if(is_file("localization/".$file)){
					$ext = explode(".", $file);
					if(end($ext)==="json"){
						$subwl = JSON::load("localization/".$file);
						if(isset($subwl[$lang])) $this->dictionary = array_merge($this->dictionary, $subwl[$lang]);
					}
				}
			}
		}elseif(is_string($wordlist)){
			if(file_exists("localization/".$wordlist.".json")){
				$wl = JSON::load("localization/".$wordlist.".json");
				$this->dictionary = &$wl[$lang];
			}else{
				foreach(scandir("localization") as $file){
					if(is_file("localization/".$file)){
						$ext = explode(".", $file);
						if(end($ext)==="json"){
							$subwl = JSON::load("localization/".$file);
							if(isset($subwl[$lang])) $this->dictionary = array_merge($this->dictionary, $subwl[$lang]);
						}
					}
				}
			}
		}
	}
	public function __get($key){
		return empty($this->dictionary[$key]) ? $key : $this->dictionary[$key];
	}
}

?>