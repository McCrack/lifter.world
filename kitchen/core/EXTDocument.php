<?php

/* DOM ********************************************************************************************************************************************************/

class HTMLDocument extends DOMDocument{
	public function __construct($path){ 
		if(file_exists($path)){
			$this->loadHTMLFile($path);
		}else{
			$this->loadHTML($path);
		}
		$this->registerNodeClass('DOMElement', 'extElement');
		$this->formatOutput=true;
	}
	public function xpath($query, $node=false){
		$xp = new DOMXPath($this);
		return $xp->evaluate($query, $node?$node:$this->documentElement);
	}
	public function getElementByAttribute($attr, $val, $num=0){ return $this->xpath("//*[@".$attr."='".$val."']")->item($num); }
	public function getElementsByAttribute($attr, $val){ return $this->xpath("//*[@".$attr."='".$val."']"); }
	public function createFragment($inner=""){
        $fragment=$this->createDocumentFragment();
        if(is_string($inner)){
			$dom = new DOMDocument;
			$dom->loadHTML("<!DOCTYPE html><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><div id='html-to-dom-input-wrapper'>".$inner."</div>");
			foreach($dom->getElementById("html-to-dom-input-wrapper")->childNodes as $child){
				$fragment->appendChild($this->importNode($child, true));
			}
        }elseif(is_object($inner)){
            $type=get_class($inner);
			if($type=="extElement" || $type=="DOMDocumentFragment" || $type=="DOMElement"){
				$fragment->appendChild($inner);
			}elseif($type=="DOMNodeList"){
				for($i=0; $i<$inner->length; ++$i){ $fragment->appendChild($inner->item($i)); }
			}
        }
        return $fragment;
    }
	public function create($nodeName, $inner=null, $attributes=null){
		$newNode=$this->createElement($nodeName);
		if(is_string($inner)){
			$newNode->appendChild($this->createFragment($inner));
        }elseif(is_object($inner)){
			$type=get_class($inner);
			if($type=="extElement" || $type=="DOMDocumentFragment" || $type=="DOMElement"){
				$newNode->appendChild($inner);
			}elseif($type=="DOMNodeList"){
				for($i=0; $i<$inner->length; ++$i){ $newNode->appendChild($inner->item($i)); }
			}
        }
		if(is_array($attributes)){
			foreach($attributes as $key=>$val){	$newNode->setAttribute($key, $val); }
		}
		return $newNode;
    }
	public function importHTML($str){
		$dom = new DOMDocument;
		$dom->loadHTML("<!DOCTYPE html><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head><body><div id='html-to-dom-input-wrapper'>".$str."</div></body></html>");
		$fragment=$this->createDocumentFragment();
		foreach($dom->getElementById("html-to-dom-input-wrapper")->childNodes as $child){
			$fragment->appendChild($this->importNode($child, true));
		}
		return $fragment;
	}
	public function __toString(){ return $this->saveHTML(); }
	public function __get($name){ return $this->xpath("id('".$name."')")->item(0); }
	public function __set($name, $val){ $this->xpath("id('".$name."')")->item(0)->nodeValue=$val; }
}
class XMLDocument extends HTMLDocument{
	public function __construct($path){
		if(file_exists($path)){
			$this->load($path);
		}else $this->loadXML($path);
		$this->registerNodeClass('DOMElement', 'extElement');
		$this->formatOutput=true;
	}
	public function createFragment($inner=""){
        $fragment=$this->createDocumentFragment();
        if(is_string($inner)){
			$fragment->appendXML($inner);
        }elseif(is_object($inner)){
            $type=get_class($inner);
			if($type=="extElement" || $type=="DOMDocumentFragment" || $type=="DOMElement"){
				$fragment->appendChild($inner);
			}elseif($type=="DOMNodeList"){
				for($i=0; $i<$inner->length; ++$i){
					$fragment->appendChild($inner->item($i));
				}
			}
        }
        return $fragment;
    }
	public function __get($name){ return $this->xpath("//*[@id='".$name."'][1]")->item(0); }
	public function __set($name, $val){ $this->xpath("//*[@id='".$name."'][1]")->item(0)->nodeValue=$val; }
	public function __toString(){ return $this->saveXML(); }
}
class extElement extends DOMElement{
	public function __get($name){ return $this->getAttribute($name); }
	public function __set($name, $value){ $this->setAttribute($name, $value); }
	public function __toString(){ return $this->textContent; }
    public function getElementByAttribute($attr, $val, $num=0){ return $this->ownerDocument->xpath(".//*[@".$attr."='".$val."']", $this)->item($num); }
    public function getElementsByAttribute($attr, $val){ return $this->ownerDocument->xpath(".//*[@".$attr."='".$val."']", $this); }
	public function childs($tagName="*"){ return $this->ownerDocument->xpath($tagName, $this); }
	public function xpath($query){ return $this->ownerDocument->xpath($query, $this); }
	public function __invoke($val=false){
		if($val){
			$this->nodeValue=$val;
		}else return $this->ownerDocument->createFragment($this->childs());
	}
	public function appendHTML($str){
		if(is_string($str)){
		return $this->appendChild($this->ownerDocument->importHTML($str));
		}else return false;
	}
	public function appenChilds($list){
		foreach($list as $item){
			$this->appendChild($item);
		}
	}
	public function importElement($node){
		$node = $this->ownerDocument->importNode($node, true);
		$this->appendChild($node);
	}
	public function importElements($list){
		foreach($list as $node){
			$node = $this->ownerDocument->importNode($node, true);
			$this->appendChild($node);
		}
	}
	public function first($type=1){
		$node=$this->firstChild;
		while($node && $node->nodeType!=$type){ $node=$node->nextSibling; }
		return $node ? $node : false;
	}
	public function last($type=1){
		$node=$this->lastChild;
		while($node && $node->nodeType!=$type){ $node=$node->previousSibling; }
		return $node ? $node : false;
	}
	public function next($type=1){
		$node=$this->nextSibling;
		while($node && $node->nodeType!=$type){
			$node=$node->nextSibling;
		}
		return $node ? $node : false;
	}
	public function previous($type=1){
		$node=$this->previousSibling;
		while($node && $node->nodeType!=$type){
			$node=$node->previousSibling;
		}
		return $node ? $node : false;
	}
	public function insertAfter($newnode){
		$refnode = $this->next(1);
		if($refnode){
			$this->parentNode->insertBefore($newnode, $refnode);
		}else $this->parentNode->appendChild($newnode);
	}
}

?>