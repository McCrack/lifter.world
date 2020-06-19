<?php

function arrayToCss($obj){
	$union = [];
	foreach($obj as $key=>$val){
		$union[] = is_array($val) ?  sprintf("%s{\n%s\n}", $key, arrayToCss($val)) : sprintf("\t%s:%s;", $key, $val);
	}
	return implode("\n", $union);
}
function cssToArray($cssText){
	$i=0;
	$level = 0;
	$NCF = true;
	$cssTextRules = [];
	while($i<strlen($cssText)){
		if($cssText[$i]=='"') $NCF ^= 1;
		if($NCF){
			switch($cssText[$i]){
				case ";":
					if($level==0){
						$rule = explode(":", substr($cssText, 0, $i));
						$cssTextRules[trim(reset($rule))] = trim(end($rule));
						$cssText = substr($cssText, ++$i);
						$i=0;
						continue;
					}
				break;
				case "{":
					$level++;
					if($level==1){
						$selector = trim(substr($cssText, 0, $i));
						$cssText = substr($cssText, ++$i);
						$i=0;
						continue;
					}
				break;
				case "}":
					$level--;
					if($level==0){
						$cssTextRules[$selector] = cssToArray(substr($cssText, 0, $i));
						$cssText = substr($cssText, ++$i);
						$i=0;
						continue;
					}
				break;
				default:break;
			}
		}
		$i++;
	}
	return $cssTextRules;
}

?>