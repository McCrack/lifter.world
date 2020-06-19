
var doc = document;

/* Ajax **************************************************************************************************************/

if(window.XMLHttpRequest){
	XMLHttpRequest.prototype.ready = true;
	XMLHttpRequest.prototype.stack = [];
	XMLHttpRequest.prototype.defaults = {
		"body":'{}',
		"async":true,
		"protect":false,
		"method":"POST",
		"timeout":15000,
		"Cache-Control":"no-cache",
		"onsuccess":function(response){ return true },
		"onerror":function(response){ console.log(response); },
		"Content-Type":"application/json"	//	"text/plain", "text/xml", "text/html", "application/octet-stream", "multipart/form-data", "application/x-www-form-urlencoded";
	};
	XMLHttpRequest.prototype.push = function(request){
		if(request['addressee']){
			for(var key in XHR.defaults){
				if(XHR.defaults.hasOwnProperty(key)){
					request[key] = request[key] || XHR.defaults[key];
				}	
			}
			XHR.stack.push(request);
			XHR.execute();
		}else console.log("XHR ERROR: Not specified addressee");
	};
	XMLHttpRequest.prototype.execute = function(){
		if(XHR.ready){
			var request = XHR.stack.shift();
			XHR.ready=false;
			
			XHR.open(request.method, request.addressee, request.async);
			XHR.timeout = request.timeout;
			XHR.setRequestHeader("Content-Type", request['Content-Type']);
			
			var indicator = doc.create("div", {id:"loading-indicator", style:"opacity:1.0"});
			doc.body.appendChild(indicator);
			XHR.onreadystatechange=function(){
				if(XHR.readyState==4){
					XHR.ready=true;
					doc.body.removeChild(indicator);

					var headers = XHR.getAllResponseHeaders().trim().split(/[\r\n]+/);
					XHR['header'] = {};
    				headers.forEach(function(line){
      					var parts = line.split(': ');
      					XHR['header'][parts.shift()] = parts.join(': ');
    				});
					
					(XHR.status==200) ? request.onsuccess(XHR.response) : request.onerror(XHR.statusText);
					if(XHR.stack.length) XHR.execute();
				}
			}
			if(request.protect) reauth();
			XHR.send(request.body);
		}
	}
	var XHR = new XMLHttpRequest();
}

/* Box *******************************************************************************************/

function openBox(params, source, onsubmit){
	new Box(params, source,(onsubmit || false))
}
var Box = function(params, source, onsubmit){
	let box = this;
	XHR.push({
		body:params,
		addressee:"/"+source,
		onsuccess:function(response){
			var mount = document.fragment(response);

			box.mount = mount.firstElementChild;
			box.handle = mount.id;
			box.window = box.mount.querySelector(".box");
			box.body = box.window.querySelector(".box-body");

			box.mount.querySelectorAll("script").forEach(function(sct){
				var script = document.createElement("script");
				if(sct.src){
					script.src = sct.src;
				}else script.innerHTML = sct.innerHTML;
				sct.parentNode.replaceChild(script, sct);
			});

			document.body.appendChild(mount);

			if(typeof box.onopen=="function") box.onopen(box.window);
			if (box.mount.classList.contains("modal")) box.mount.onclick=box.drop;
			box.window.addEventListener("click", function(event){
				event.cancelBubble=true;
				//if(location.hash.slice(1)!=box.mount.id) location.hash=box.mount.id;
			});

			box.window.addEventListener("submit",function(event){
				//event.preventDefault();
				if(onsubmit){
					onsubmit(this);
				}else if(box.onsubmit) box.onsubmit(this);
			});	
			box.window.drop = box.drop;
			box.window.align = box.align;
			document.body.style.overflow = "hidden";
		}
	});
	box.drop=function(){
		document.body.removeChild(box.mount);
		document.body.style.overflow = "auto";
	}
	box.align=function(){
		if(box.mount.classList.contains("modal")){
			if(box.window.offsetHeight>(screen.height - 40)){
				box.window.style.top = "20px";
			}else box.window.style.top = "calc(50% - "+(box.window.offsetHeight/2)+"px)";
		}else if(box.mount.offsetHeight>(screen.height - 40)){
			box.mount.style.top = "20px";
		}else box.mount.style.top = "calc(50% - "+(box.mount.offsetHeight/2)+"px)";
	}
}

/* Object ************************************************************************************************************/

function inArray(obj, value){
	for(var key in obj) if(obj[key] == value) return key;
	return false;
}
function flip(obj){
	var outObj={};
	obj=obj || {};
	for(var key in obj){
		if(typeof(obj[key]) in {"string":0,"number":0,"boolean":0} || obj[key]===null && obj.hasOwnProperty(key)){
			outObj[obj[key]]=key;
		}
	} return outObj;
}
function join(separator, obj){
	var outArr=[];
	obj=obj || {};
	for(var key in obj){
		if(typeof(obj[key]) in {"string":0,"number":0,"boolean":0} || obj[key]===null && obj.hasOwnProperty(key)){
			outArr.push(obj[key]);
		}
	} return outArr.join(separator);
}

/* Array *************************************************************************************************************/

Array.prototype.inArray = function(value){
	for(var i=this.length; i--;) if(this[i] == value) return i;
	return NaN;
}
Array.prototype.stringify = function(){
	var isArray, item, t, json = [];
	for(var i=0; i<this.length; i++){
		item=this[i];
		t=typeof(item);
		isArray = (item.constructor == Array);
		if(t=="string"){
			item = '"'+item+'"';
		}else if(t=="object" && item!==null){
			item=JSON.encode(item);
		}
		json.push(String(item));
	}
	return '['+String(json)+']';
}

Array.prototype.flip = function(){
	var obj = {};
	for(var i=0; i<this.length; i++){
		obj[this[i]]=i;
	}
	return obj;
}

/* String ************************************************************************************************************/

String.prototype.alert=function(){
	alert(this);
}
String.prototype.log=function(){
	console.log(this);
}
String.prototype.trim=function(){
	return this.replace(/(^\s+)|(\s+$)/g, "");
}
String.prototype.translite=function(){
	var dictionary={
	"а":"a",	"б":"b",	"в":"v",	"г":"g",	"ґ":"g",	"д":"d",
	"е":"e",	"є":"ye",	"ж":"zh",	"з":"z",	"и":"i",	"і":"i",
	"ї":"yi",	"й":"y",	"к":"k",	"л":"l",	"м":"m",	"н":"n",
	"о":"o",	"п":"p",	"р":"r",	"с":"s",	"т":"t",	"у":"u",
	"ф":"f",	"х":"h",	"ы":"y",	"э":"e",	"ё":"e",	"ц":"ts",
	"ч":"ch",	"ш":"sh",	"щ":"shch",	"ю":"yu",	"я":"ya",	" ":"-",
	"ь":"",		"ъ":""};

	var str = this.trim().toLowerCase();
	if(~str.search(/[іїґє]/)){
		dictionary['г'] = "h";
		dictionary['и'] = "y"
		dictionary['х'] = "kh";
	}
	var str = str.replace(/./g, function(x){
		if(dictionary.hasOwnProperty( x )){
			return dictionary[x];
		}else return x.replace(/[^a-z0-9_.-]+/,"");
	});
	return str.replace(/-{2,}/g,"-");
}
String.prototype.levenshtein=function(substr){
	var length1=this.length;
	var length2=substr.length;
	var diff,tab=new Array(); 
	for(var i=length1+1; i--;){
		tab[i]=new Array();
		tab[i].length=length2+1;
		tab[i][0]=i;
	}
	for(var j=length2+1; j--;){tab[0][j]=j;}
	for(var i=1; i<=length1; i++){
		for(var j=1; j<=length2; j++){
			diff=(this.toLowerCase().charAt(i-1)!=substr.toLowerCase().charAt(j-1));
			tab[i][j]=Math.min(Math.min(tab[i-1][j]+1, tab[i][j-1]+1), tab[i-1][j-1]+diff);     
		}
	}
	return tab[length1][length2];
}
String.prototype.parse = function(separator){
	var obj,str = this;
	try{
		obj = JSON.parse(str);
	}catch(e){
		obj = false;
	}
	return obj;
}
String.prototype.explode = function(separator){
	separator = separator || ",";
	var list = this.split(separator);
	for(var i=list.length; i--;){
		list[i] = list[i].trim();
	}
	return list;
}
String.prototype.format=function(){
	var str = this;
	for(var i=0; i<arguments.length; i++){
		pattern = /%\d*[dbx]/.exec(str)[0];
		key=pattern[pattern.length-1];
		value=parseInt(arguments[i]).toString({"d":10, "b":2, "x":16}[key]).toUpperCase();
		lng=parseInt(pattern.substring(1));
		for(var fill="0"; value.length<lng; value=fill+value);
		str = str.replace(pattern, value);
	}
	return str;
}

/* Number ************************************************************************************************************/

function random(min, max){
	min = min || 0;
	max = max || 2147483647;
	return (Math.random() * (max - min + 1) + min)^0;
}

/* COOKIES ***********************************************************************************************************/

var COOKIE = new function(){
	this.get=function(cName){
		var obj = {};
		var cookies=document.cookie.split(/;|=/);
		for(var i=0; i<cookies.length; i++){
			if(cookies[i].trim()===cName) return decodeURI(cookies[++i]);
		}
	}
	this.set=function(name, value, options){
		options = options || {};
		
		var expires = options.expires;
		if(typeof(expires) == "number" && expires){
			var d = new Date();
			d.setTime(d.getTime() + expires * 1000);
			expires = options.expires = d;
		}
		if(expires && expires.toUTCString) {
			options.expires = expires.toUTCString();
		}
		value = encodeURIComponent(value);
		var updatedCookie = name+"="+value;
		for(var key in options){
			if(options.hasOwnProperty(key)){ 
				updatedCookie+="; "+key;
				if(options[key]!==true){
					updatedCookie+="="+options[key];
				}
			}
		}
		document.cookie = updatedCookie;
	}
	this.remove=function(name){
		this.set(name, "", {"expires":-1});
	}
	this.clear=function(){
		for(var key in this){
			if(typeof(this[key])=="string"){
				this.set(key, "", {"expires":-1});
			}
		}
	}
}

/* URL ***************************************************************************************************************/

function parse_url( url ){
	var obj = {
		protocol:"",
		host:"",
		path:[],
		search:{},
		hash:""
	};
	url = url.split("#");
	obj.hash = "#"+url.splice(1).join("#");
	url = url.join().split("?");
	if(url.length>1){
		var search = url.splice(1).join().split(/\&/);
		for(var i=0; i<search.length; i++){
			search[i] = search[i].split("=");
			obj.search[search[i][0]] = search[i][1];
		}
	}
	url = url.join().split(":");
	if(url.length>1){
		obj.protocol = url.splice(0,1)+":";
	}
	url = url.join().replace(/^\/+/, "").split("/");
	obj.host = url.splice(0,1).join();
	obj.path = url;

	return obj;
}


/* HTMLDocument *******************************************************************************************************/

	HTMLDocument = Document || HTMLDocument;
	
	Object.defineProperty(HTMLDocument.prototype, "width", {
		get:function(){
			return self.innerWidth || document.documentElement.clientWidth;
		}
	});
	Object.defineProperty(HTMLDocument.prototype, "height", {
		get:function(){
			return self.innerHeight || document.documentElement.clientHeight;
		}
	});
	Object.defineProperty(HTMLDocument.prototype, "currentScrollTop", {
		get:function(){
			return  window.pageYOffset || document.documentElement.scrollTop;
		}
	});
	Object.defineProperty(HTMLDocument.prototype, "currentScrollLeft", {
		get:function(){
			return  window.pageXOffset || document.documentElement.scrollLeft;
		}
	});

	HTMLDocument.prototype.onready=function(handler){
		 window.addEventListener('load', handler);
	}
	HTMLDocument.prototype.selector = HTMLElement.prototype.selector=function(selector, All){
		if(All){
			var nodes = this.querySelectorAll(selector);
			return Array.prototype.slice.call(nodes);
		}else var nodes = this.querySelector(selector);
		return (nodes || null);
	}
	HTMLDocument.prototype.create=function(tagName, attributes){
		var obj = this.createElement(tagName);
		for(var i=2; i<arguments.length; i++){
			if(typeof(arguments[i])=="string"){
				obj.innerHTML=arguments[i];
			}else if(typeof(arguments[i])==="object"){
				if(arguments[i].nodeType in {"1":null, "3":null, "11":null}){
					obj.appendChild(arguments[i]);
				}else if(arguments[i].constructor == Array){
					obj.appendChilds(arguments[i]);
				}
			}
		}
		for(var key in attributes){
			if(attributes.hasOwnProperty(key)){
				obj.setAttribute(key, attributes[key]);
			}
		}
        return obj;
    }
	HTMLDocument.prototype.fragment=function(content){
		if(content){
			if(typeof(content)=="string"){
				var temp = document.createElement("template");
					temp.innerHTML = content;
				var obj = temp.content;
			}else if(typeof(content)=="object" && content.nodeType in {1:null, 3:null, 11:null}){
				var obj = document.createDocumentFragment();
				obj.appendChilds(content);
			}return obj;
		}else return document.createDocumentFragment();
	}

/* HTMLFragment / HTMLElement *****************************************************************************************/
	
	DocumentFragment.prototype.first=HTMLElement.prototype.first=function(tagName){
		tagName = tagName || "";
		tagName = tagName.toUpperCase();
		var node = this.firstElementChild;
		if(tagName){
			while(node && node.nodeName!=tagName){
				node = node.nextElementSibling;
			}
		}
		return node || null;
    }
	DocumentFragment.prototype.last=HTMLElement.prototype.last=function(tagName){
		tagName = tagName || "";
		tagName = tagName.toUpperCase();
		var node = this.lastElementChild;
		if(tagName){
			while(node && node.nodeName!=tagName){
				node = node.previousElementSibling;
			}
		}
		return node || null;
    }
    DocumentFragment.prototype.appendChildren = HTMLElement.prototype.appendChildren = function(nodeList){
		for(var i=0; i<nodeList.length; i++){
			this.appendChild(nodeList[i]);
		}
		return this.children;
	}

/* HTMLElement *******************************************************************************************************/

	HTMLElement.prototype.next=function(tagName){
		tagName = tagName || "";
		tagName = tagName.toUpperCase();
		var node = this.nextElementSibling;
		if(tagName){
			while(node && node.nodeName!=tagName){
				node = node.nextElementSibling;
			}
		}
		return node || null;
    }
	HTMLElement.prototype.previous=function(tagName){
		tagName = tagName || "";
		tagName = tagName.toUpperCase();
		var node = this.previousElementSibling;
		if(tagName){
			while(node && node.nodeName!=tagName){
				node = node.previousElementSibling;
			}
		}
		return node || null;
    }
	HTMLElement.prototype.parent=function(level){
		switch( typeof(level) ){
			case "string":
				level = level.toUpperCase();
				var node = this.parentNode;
				while(node && node.nodeName != level){
					node = node.parentNode;
				}
			break;
			case "number":
				level=level || 1;
				var node = this;
				for(; level--;){
					if(node){ node = node.parentNode; }
				}
			break;
			default:
				var node = this.parentNode;
			break;
		}
		return node;
    }
	HTMLElement.prototype.insertToBegin=function(node){
		if(node){
			var first;
			if(first = this.firstChild){
				first = this.insertBefore(node, first);
			}else first = this.appendChild(node);
			return first;
		}else return false;
    }
	HTMLElement.prototype.insertBeforeNode=function(node){
		if(typeof node==="string"){
			this.insertAdjacentHTML("afterBegin", node);
		}else if(typeof node==="object"){
			this.insertAdjacentElement("afterBegin", node)
		}else return false;
    }
	HTMLElement.prototype.insertAfter=function(node){
		if(typeof node==="string"){
			this.insertAdjacentHTML("afterEnd", node);
		}else if(typeof node==="object"){
			this.insertAdjacentElement("afterEnd", node)
		}else return false;
    }
	HTMLElement.prototype.build=function(list, clean){
		if(clean) this.innerHTML = "";
		var fragment = document.build(list);
		this.appendChild(fragment);
		return fragment;
	}
	HTMLElement.prototype.getCss=function(rule, pseudo){
		pseudo = pseudo || "";
		var obj = window.getComputedStyle(this, "");
		return obj.getPropertyValue(rule);
	}
	Object.defineProperty(HTMLElement.prototype, "fullScrollTop", {
		get:function(){
			var srl = 0;
			var obj = this;
			while(obj.nodeType==1){
				srl += obj.scrollTop;
				obj = obj.parentNode;
			}
			return srl;
		}
	});
	Object.defineProperty(HTMLElement.prototype, "fullScrollLeft", {
		get:function(){
			var srl = 0;
			var obj = this;
			while(obj.nodeType==1){
				srl += obj.scrollLeft;
				obj = obj.parentNode;
			}
			return srl;
		}
	});
	
/* NodeList **********************************************************************************************************/
	
	NodeList.prototype.on = function(e, handler){
		for(var i=this.length; i--;){
			this[i].addEventListener(e, handler);
		}
	}
	NodeList.prototype.set = function(property, value){
		for(var i=this.length; i--;){
			this[i][property] = value;
		}
	}
	
/* JSON **************************************************************************************************************/

JSON.encode=function(obj, level){
	level = level || 0;
	var t = typeof(obj);
	if(typeof obj!="object"){
		return '"'+String(obj)+'"';
	}else{
		var t="",
			json = [],
			isArray = (obj && obj.constructor == Array);
		for(var i=0; i<level; i++) t += '\t';
		for(var key in obj){
			if(obj.hasOwnProperty(key)){
				if(typeof obj[key]==="object"){
					var item = JSON.encode(obj[key], level+1);
				}else var item = '"'+String(obj[key]).trim()+'"';
				json.push( (isArray ? '' : '"'+key.replace(/"/g,"&quot;").trim()+'":')+item );
			}
		}
		return isArray ? '[\n\t'+t+json.join(',\n\t'+t)+'\n'+t+']' : '{\n\t'+t+json.join(',\n\t'+t)+'\n'+t+'}';
	}
};
/*
JSON.parse = JSON.parse || function(str){
	if(str==="") str = '""';
	eval("var obj="+str+";");
	return obj;
}
*/
/* Session ***********************************************************************************************************/

var session = window.sessionStorage || new function(){
	try{
		JSON.parse(window.name);
	}catch(e){ window.name = "{}"; }
	
	this.getItem = function(varName){
		return JSON.parse(window.name)[varName] || null;
	}
	this.setItem = function(varName, val){
		var temp=JSON.parse(window.name);
			temp[varName]=val;
			window.name=JSON.stringify(temp);
	}
}

var storage = window.localStorage || session;

session.__proto__.open = function(){
	var today = new Date();
		today.setUTCHours(0,0,0,0);
	var oldTimestamp = session.getItem("today");
	var newTimestamp = today.getTime();
	if(newTimestamp > oldTimestamp){
		session.setItem("today", today.getTime());
		return false;
	}else return true;
}
function reauth(){
	var cookies=document.cookie.split(/;\s*/g);
	for(var i=cookies.length; i--;){
		var cookie=cookies[i].split(/=/g);
		if(cookie[0]==="key"){
			document.cookie = "finger="+encodeURIComponent( md5( session.getItem("login") + session.getItem("passwd") + decodeURI(cookie[1])))+"; path=/";
			break;
		}
	}
}

/* Date **************************************************************************************************************/

function date(pattern, timestamp){
	var M = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
	var F = ["January","February","March","April","May","June","July","August","September","October","November","December"];
	pattern = pattern||"d.m.Y";
	var today = timestamp ? new Date(timestamp) : new Date();
	var params = pattern.trim().split(/\W+/);
	var set={
		"d":"%02d".format([today.getDate()]),
		"m":"%02d".format([today.getMonth()+1]),
		"M":M[today.getMonth()],
		"F":F[today.getMonth()],
		"Y":"%04d".format([today.getFullYear()]),
		"H":"%02d".format([today.getHours()]),
		"i":"%02d".format([today.getMinutes()]),
		"s":"%02d".format([today.getSeconds()]),
		"D":today.getDay(),
		"U":((today.getTime()/1000)^0)
	}
	for(var i=0; i<params.length; i++){
		pattern=pattern.replace(params[i], set[params[i]]);
	}
	return pattern;
}

/* Other *************************************************************************************************************/

var Interval = function(callback, itr, dur){
	if(!itr) return false;
	var interval = this;
	this.i = 0;
	this.duration = dur;
	this.iterations = itr;
	this.shot = function(){
		interval.timer = setTimeout(function(){
			callback( interval.i++ );
			if(--interval.iterations){
				interval.shot();
			}
		}, this.duration);
	}
	this.clear = function(){
		clearTimeout(interval.timer);
	}
	this.shot();
}