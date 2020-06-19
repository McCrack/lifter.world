<?php

header('Content-Type: text/javascript"; charset=utf-8', true);
header("cache-control: public");
header('content-encoding: gzip');
header('vary: accept-encoding');

ob_start();

?>

var Wordlist = {
	names:[],
	fragment:function(owner){
		owner = owner || document;
		owner.querySelectorAll("*[data-translate]").forEach(function(node){
			var property = node.dataset.translate;
			node[property] = translate[node[property]] || node[property];
		});
	},
	addDictionary:function(dictionary, name){
		if(name){
			if(isNaN( this.names.inArray(name) )){
				this.names.push(name);
			}else return false;
		}
		for(var key in (dictionary || {})){
			if(dictionary.hasOwnProperty(key)){
				this[key] = dictionary[key];
			}
		}
	}
}
var translate = new Proxy(Wordlist, {
	get(target, name){ return target[name] || name; },
	set(target, name, value){ target[name] = value; }
});

<?php

	if(is_string($_GET['d']) && file_exists("localization/".$_GET['d'].".json")){
		$wl = JSON::load("localization/".$_GET['d'].".json");
		print "translate.addDictionary(".JSON::encode($wl[ARG_1]).", '".$_GET['d']."');\n";
	}else{
		if(is_array($_GET['d'])){
			foreach($_GET['d'] as $wordlist){
				if(file_exists("localization/".$wordlist.".json")){
					$wl = JSON::load("localization/".$wordlist.".json");
					print "translate.addDictionary(".JSON::encode($wl[ARG_1]).", '".$wordlist."');\n";
				}
			}
		}else{
			foreach(scandir("localization") as $wordlist){
				if(is_file("localization/".$wordlist)){
					$wordlist = explode(".", $wordlist);
					if(end($wordlist)==="json"){
						$wl = JSON::load("localization/".$wordlist);
						print "translate.addDictionary(".JSON::encode($wl[ARG_1]).", '".reset($wordlist)."');\n";
					}
				}
			}
		}
	}
	print "translate.fragment();";

$content = ob_get_contents();
ob_end_clean();
	
	$content = gzencode($content);

	header('content-length: ' . strlen($content));

	print $content;
?>