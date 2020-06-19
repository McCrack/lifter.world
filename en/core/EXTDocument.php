<?php

class HTMLDocument extends DOMDocument{
	public function __construct($path){
		if(file_exists($path)){
			$this->loadHTMLFile($path, LIBXML_NOWARNING);
		}
		$this->registerNodeClass('DOMElement', 'extElement');
		$this->formatOutput=true;
	}
	public function __toString(){ return $this->saveHTML(); }
	public function __get($name){ return $this->xpath("id('".$name."')")->item(0); }
	
	public function xpath($query, $node=false){
		$xp = new DOMXPath($this);
		
		$xp->registerNamespace('atom', "http://www.w3.org/2005/Atom");

		$nodelist = $xp->evaluate($query, $node?$node:$this->documentElement);
		return $nodelist;
	}
	public function createFragment($content=null){
        if(is_string($content)){
			$fragment = $this->importHTML($content);
        }elseif(is_object($content)){
			$fragment=$this->createDocumentFragment();
			switch(get_class($content)){
				case "extElement":
				case "DOMElement":
				case "DOMDocumentFragment":
					$fragment->appendChild($content);
				break;
				case "NodeList":
				case "DOMNodeList":
					for($i=0; $i<$content->length; ++$i){
						$fragment->appendChild($content->item($i));
					}
				break;
				default: break;
			}
        }else $fragment = $this->createDocumentFragment();
        return $fragment;
    }
	public function create($nodeName, $content=null, $attributes=[]){
		$newNode=$this->createElement($nodeName);
		if($content){
			$newNode->appendChild( $this->createFragment($content) );
        }
		foreach($attributes as $key=>$val){
			$newNode->setAttribute($key, $val);
		}
		return $newNode;
    }
	public function importHTML($html){
		$dom = new DOMDocument;
		$dom->loadHTML("<!DOCTYPE html><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'></head><body><div id='html-to-dom-input-wrapper'>".$html."</div></body></html>");
		$fragment = $this->createDocumentFragment();
		foreach($dom->getElementById("html-to-dom-input-wrapper")->childNodes as $child){
			$fragment->appendChild($this->importNode($child, true));
		}
		return $fragment;
	}
}
class extElement extends DOMElement{
	function __construct(){
		parent::__construct();
	}
	public function __get($name){ return $this->getAttribute($name); }
	public function __set($name, $value){ $this->setAttribute($name, $value); }
	public function appendHTML( $html ){
		if(is_string($html)){
			$this->appendChild( $this->ownerDocument->importHTML( $html ) );
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