
var doc = document;

/* Ajax **************************************************************************************************************/

if(window.XMLHttpRequest){
	XMLHttpRequest.prototype.ready = true;
	XMLHttpRequest.prototype.stack = [];
	XMLHttpRequest.prototype.defaults = {
		"body":'{}',
		"async":true,
		"protect":true,
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
			
			var indicator = doc.create("div", {id:"loading-indicator"});
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
	XMLHttpRequest.prototype.uploader = function(files, addressee, onsuccess){
		onsuccess = onsuccess || function(){ return true }
		var box = new Box('{}', "boxfather/uploadlogbox/modal");
		box.onopen = function(){
			var seek = 0;
			box.progressbar = box.window.querySelector("#progress");
			var BLOCK_SIZE = 2097152;
			for(var i=files.length; i--;){
				for(var j=0; j<files[i].size; j+=BLOCK_SIZE){
					seek = j + BLOCK_SIZE;
					XHR.push({
						"seek":seek,
						"not_last":i,
						"size":files[i].size,
						"Content-Type":"application/octet-stream",
						"addressee":addressee+"&file="+files[i].name.translite()+"&seek="+j,
						"body":files[i].slice(j, j+BLOCK_SIZE),
						"onsuccess":function(response){
							box.progressbar.max = this.size;
							box.progressbar.value = this.seek;
							if(this.seek >= this.size){
								if(this.not_last){
									box.body.appendChild(doc.create("div", {}, response));
								}else{
									box.drop();
									onsuccess(response);
								}
							}
						}					
					});
				}
			}
		}
	}
	var XHR = new XMLHttpRequest();
}

/* Box ************************************************************************************************************/

function openBox(params, source, onsubmit, protect){
	new Box(params, source,(onsubmit || false), (protect || false))
}
var Box = function(params, source, onsubmit, protect){
	let box = this;
	XHR.push({
		body:params,
		addressee:"/"+source,
		reauth:protect || true,
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

			if(typeof box.onopen==="function") box.onopen(box.window);
			if (box.mount.classList.contains("modal")) box.mount.onclick=box.drop;
			box.window.addEventListener("click", function(event){
				event.cancelBubble=true;
				if(location.hash.slice(1)!=box.mount.id) location.hash=box.mount.id;
			});

			box.window.addEventListener("submit",function(event){
				event.preventDefault();
				if(onsubmit){
					onsubmit(this);
				}else if(box.onsubmit) box.onsubmit(this);
			});	
			box.window.drop = box.drop;
			box.window.align = box.align;
		}
	});
	box.drop=function(){
		document.body.removeChild(box.mount);
		location.hash="";
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

/* CONTEXT MENU ******************************************************************************************************/

var ContextMenu = new function(){
	var box = this;
	var menu = null;
	this.create = function(event, obj, content){
		event.preventDefault();
		let offset = [];

		if(event.clientY > (doc.height / 2)){
			offset = ["bottom:"+(doc.height - event.clientY - 16)+"px"];
		}else offset = ["top:"+(event.clientY - 16)+"px"];

		if(event.clientX > (doc.width / 2)){
			offset.push("right:"+(doc.width - event.clientX - 16)+"px");
		}else offset.push("left:"+(event.clientX - 16)+"px");

		menu = menu || doc.create("div",{id:"contextMenu"});
		menu.innerHTML = "";
		menu.appendChild(content(obj));
		menu.style.cssText = offset.join(";");
		doc.body.appendChild(menu);

		doc.addEventListener("click", this.drop);
		menu.onclick = function(event){
			event.cancelBubble = true;
			box.drop();
		}
		return menu;
	}
	this.drop = function(){
		doc.removeEventListener("click", box.drop);
		doc.body.removeChild(menu);
		menu = null;
	}
}

/* Object ************************************************************************************************************/

function inArray(obj, value){
	for(var key in obj) if(obj.hasOwnProperty(key) && (obj[key] == value)) return key;
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
function join(selector, obj){
	var outArr=[];
	obj=obj || {};
	for(var key in obj){
		if(typeof(obj[key]) in {"string":0,"number":0,"boolean":0} || obj[key]===null && obj.hasOwnProperty(key)){
			outArr.push(obj[key]);
		}
	} return outArr.join(selector);
}

/* Array *************************************************************************************************************/

Array.prototype.inArray = function(value){
	for(var i=this.length; i--;) if(this[i] == value) return i;
	return NaN;
}
Array.prototype.toJSON = function(){
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

String.prototype.trim=function(){
	return this.replace(/(^\s+)|(\s+$)/g, "");
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

String.prototype.isFormat=function(reg){
	var str = this;
	var pattern = new RegExp(reg || ".");
	if(!pattern.test(str)){
		alertBox("incorrect format");
		return false;
	}else return true;
}
String.prototype.jsonToObj=function(){
	var obj,str = this;
	try{
		obj = JSON.parse(str);
	}catch(e){
		obj = false;
	}
	return obj;
}
String.prototype.format=function(numbers){
	var str = this;
	for(var i=0; i<numbers.length; i++){
		pattern = /%\d*[dbx]/.exec(str)[0];
		key=pattern[pattern.length-1];
		value=parseInt(numbers[i]).toString({"d":10, "b":2, "x":16}[key]);
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

function splitParams(str){
	var path = str.replace(/^\?/, "").split(/\&/);
	var params={}, temp=[];
	for(var i=0; i<path.length; i++){
		temp = path[i].split(/=/);
		params[temp[0]] = temp[1];
	}
	return params;
}

/* HTMLElement *******************************************************************************************************/

	HTMLDocument = Document || HTMLDocument;
	
	HTMLDocument.prototype.width = self.innerWidth || doc.documentElement.clientWidth;
	HTMLDocument.prototype.height = self.innerHeight || doc.documentElement.clientHeight;
	
	

	HTMLDocument.prototype.create=function(tagName, attributes, content){
		if(tagName){
			var obj = this.createElement(tagName);
			if(content){
				if(typeof(content)=="string"){
					obj.innerHTML=content;
				}else if(typeof(content)==="object"){
					if(["1","3","11"].inArray(content.nodeType)){
						obj.appendChild(content);
					}else if(content.constructor == Array) obj.appendChilds(content);
				}
			}
			for(var key in attributes){
				if(attributes.hasOwnProperty(key)){
					obj.setAttribute(key, attributes[key]);
				}
			}
			return obj;
		}else return document.createDocumentFragment();
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
	HTMLElement.prototype.first=function(){
        var node=this.firstChild;
        while(node && node.nodeType!=1){
			node=node.nextSibling;
		}
        return node || null;
    }
	HTMLElement.prototype.last=function(){
        var node=this.lastChild;
        while(node && node.nodeType!=1){
			node=node.previousSibling;
		}
        return node || null;
    }
	HTMLElement.prototype.next=function(){
		var node=this.nextSibling;
		while(node && node.nodeType!=1){
			node = node.nextSibling;
		}
		return node || null;
    }
	HTMLElement.prototype.previous=function(){
		var node=this.previousSibling;
		while(node && node.nodeType!=1){
			node = node.previousSibling;
		}
		return node || null;
    }
	HTMLElement.prototype.parent=function(level){
		level=level || 1;
		var node=this;
		for(; level--;){
			if(node){ node=node.parentNode; }
		}
		return node;
    }
	HTMLElement.prototype.ancestor=function(tagName){
		if(tagName){
			tagName=tagName.toUpperCase();
			var node=this.parentNode;
			while(node && node.nodeName!=tagName){
				node=node.parentNode;
			}
			return node || null;
		}else return false;
    }
	HTMLElement.prototype.insertToBegin=function(node){
		if(node){
			var first;
			if(first=this.firstChild){
				first = this.insertBefore(node, first);
			}else{
				first = this.appendChild(node);
			}
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
	HTMLElement.prototype.childElements=function(){
		var children = this.childNodes;
		var childrenList = [];
		if(children.length){
			for(var i=0; i<children.length; i++){
				if(children[i].nodeType==1){
					childrenList.push(children[i]);
				}
			}
		}
		return childrenList;
    }
	HTMLElement.prototype.appendChilds = DocumentFragment.prototype.appendChilds = function(nodeList){
		nodeList.forEach(function(node){
			this.appendChild(node);
		});
	}
	HTMLElement.prototype.getCss=function(rule){
		var obj = window.getComputedStyle(this, "");
		return obj.getPropertyValue(rule);
	}
	HTMLElement.prototype.fullScrollTop=function(){
		var srl = 0;
		var obj = this;
		while(obj.nodeType==1){
			srl += obj.scrollTop;
			obj = obj.parentNode;
		}
		return srl;
	}
	HTMLElement.prototype.fullScrollLeft=function(){
		var srl = 0;
		var obj = this;
		while(obj.nodeType==1){
			srl += obj.scrollLeft;
			obj = obj.parentNode;
		}
		return srl;
	}
	HTMLElement.prototype.swap=function(dir,itr){
		itr = itr || 1;
		for(var i=0; i<itr; i++) if(dir){
			var node = this.nextElementSibling;
			if(node) node.insertAdjacentElement("afterEnd", this);
		}else{
			var node = this.previousElementSibling;
			if(node) node.insertAdjacentElement("beforebegin", this);
		}
    }
	HTMLImageElement.prototype.reload=function(){
		var path = this.src.split(/#/);
			path[1] = new Date().getTime();
		this.src=path.join("#");
	}
	HTMLInputElement.prototype.change=function(val, onchange){
		this.value = val;
		if(onchange) onchange();
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

session.__proto__.open=function(){
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
	pattern=pattern||"d.m.Y";
	var today= timestamp ? new Date(timestamp) : new Date();
	var params=pattern.trim().split(/\W+/);
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

window.showModalDialog = window.showModalDialog || function(url, winName){
	options = "location=no,menubar=no,resizable=no,scrollbars=no,toolbar=no,directories=no,status=no";
	window.open(url, winName, options); 
}

var softScroll = {
	"x": function(position){
		var delta = position - (window.pageXOffset || document.documentElement.scrollLeft);
		var dir = delta / (delta = Math.abs(delta));
		interval = setInterval(function(){
			step=(delta/10)^0;
			delta-=step;
			window.scrollBy(step*dir, 0)
			if(delta<10){
				window.scrollBy(delta, 0)
				clearInterval(interval);
			}
		},5)
	},
	"y": function(position){
		var delta = position - (window.pageYOffset || document.documentElement.scrollTop);
		var dir = delta / (delta = Math.abs(delta));
		interval = setInterval(function(){
			step=(delta/10)^0;
			delta-=step;
			window.scrollBy(0, step*dir)
			if(delta<10){
				window.scrollBy(0, delta*dir)
				clearInterval(interval);
			}
		},5)
	}
}

/* BASE64 ************************************************************************************************************/

function utf8_to_b64(str) {
    return window.btoa(unescape(encodeURIComponent(str)));
}

function b64_to_utf8(str) {
    return decodeURIComponent(escape(window.atob(str)));
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