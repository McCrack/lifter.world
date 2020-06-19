
/* Standby ***************************/

var Standby = (window.localStorage[SECTION] || "undefined").jsonToObj() || {};
var STANDBY = new Proxy(Standby,{
	get(target, name){ return target[name] || null; },
	set(target, name, value){
		target[name] = value;
		window.localStorage[SECTION] = JSON.encode(Standby);
	}
});

/* Table rows ******************************************************/

function addRow(row){
	var newRow = doc.create("tr");
		newRow.appendChild( doc.create("th", {class:"tool",onclick:"addRow(this.parentNode)"}, "+") );
	row.querySelectorAll("td").forEach(function(cell){
		newRow.appendChild(doc.create("td", {contenteditable:"true"}));
	});
	newRow.appendChild(doc.create("th", {class:"tool",onclick:"deleteRow(this.parentNode)"}, "✕"));
	row.insertAdjacentElement("afterEnd", newRow)
}
function deleteRow(row, onDelete){
	row.parentNode.removeChild(row);
	if(onDelete) onDelete();
}

/* Patterns ********************************************************/

function showPattern(json, onapply){
	if(json){
		onapply = onapply || function(){};
		var edt;
		var pattern = new Box('{}', "boxfather/patternbox/json");
		pattern.onopen=function(){
			edt = ace.edit(pattern.body.querySelector("main"));
			edt.setTheme("ace/theme/solarized_dark");
			edt.getSession().setMode("ace/mode/json");
			
			edt.setShowInvisibles(true);
			edt.setShowPrintMargin(false);
			edt.session.setValue(json);
			edt.focus();
			edt.resize();
		}
		pattern.onsubmit = function(){
			onapply(edt.getValue());
		}
	}
}
function showHTMLPattern(onapply){
	var edt;
	var pattern = new Box('{}', "boxfather/patternbox/html");
	pattern.onopen=function(){
		edt = ace.edit(pattern.body.querySelector("main"));
		edt.setTheme("ace/theme/twilight");
		edt.getSession().setMode("ace/mode/html");
		edt.setShowInvisibles(true);
		edt.setShowPrintMargin(false);
		edt.resize();
	}
	pattern.onsubmit = function(){
		onapply(edt.getValue());
	}
}

/* Wordlist ********************************************************/

function showWordlistBox(str){
	if(typeof str!="string"){
		if(typeof str!="object"){
			return false;
		}else str = str.textContent;
	}
	var box = new Box('{"key":"'+str+'"}', "wordlist/box");
	return false;
}


/* Context menu ************************************************************************/

var showContextMenu = function(items, event){
	var context = this;
	context.drop_menu_tout;
	let top = (event.clientY-16);
	let left = (event.clientX+10);
	context.menu = document.create("div", {"class":"context-menu", "style":"top:"+top+"px;left:"+left+"px"}, items);
	document.body.appendChild(context.menu);
	context.menu.onmouseover=function(){ clearTimeout(context.drop_menu_tout); }
	
	document.onclick = function(){
		document.body.removeChild(context.menu);
		document.onclick = null;
	}
	context.menu.onmouseout = function(){
		context.drop_menu_tout = setTimeout(function(){
			document.onclick();
		}, 300);
	}
}

/* Alerts ******************************************************************************/

var alertBox = function(msg, classes){
	if(isNaN( translate.names.inArray("alerts") )){
		XHR.push({
			addressee:"/xhr/wordlist/addwordlist/"+LANGUAGE+"/alerts",
			onsuccess:function(response){
				try{
					var dictionary = JSON.parse(response);
					for(var key in (dictionary || {})){
						if(dictionary.hasOwnProperty(key)){
							translate[key] = dictionary[key];
						}
					}
					translate.names.push("alerts") 
				}catch(e){ console.log(e.name) }
			}
		});
	}
	classes = classes || ["light-btn-bg"];
	var box = new Box(JSON.encode(classes), "boxfather/alertbox");
	box.onopen = function(){
		box.window.alert.value = translate[msg];
	}
}
var confirmBox = function(msg, onsubmit, classes){
	if(isNaN( translate.names.inArray("alerts") )){
		XHR.push({
			addressee:"/xhr/wordlist/addwordlist/"+LANGUAGE+"/alerts",
			onsuccess:function(response){
				try{
					var dictionary = JSON.parse(response);
					for(var key in (dictionary || {})){
						if(dictionary.hasOwnProperty(key)){
							translate[key] = dictionary[key];
						}
					}
					translate.names.push("alerts") 
				}catch(e){ console.log(e.name) }
			}
		});
	}
	classes = classes || ["light-btn-bg"];
	var box = new Box(JSON.encode(classes), "boxfather/confirmbox", onsubmit);
	box.onopen = function(){
		box.window.alert.value = translate[msg];
	}
}
var promptBox = function(msg, onsubmit, classes){
	if(isNaN( translate.names.inArray("alerts") )){
		XHR.push({
			addressee:"/xhr/wordlist/addwordlist/"+LANGUAGE+"/alerts",
			onsuccess:function(response){
				try{
					var dictionary = JSON.parse(response);
					for(var key in (dictionary || {})){
						if(dictionary.hasOwnProperty(key)){
							translate[key] = dictionary[key];
						}
					}
					translate.names.push("alerts") 
				}catch(e){ console.log(e.name) }
			}
		});
	}
	classes = classes || ["light-btn-bg"];
	var box = new Box(JSON.encode(classes), "boxfather/promptbox", onsubmit);
	box.onopen = function(form){
		form.alert.value = translate[msg];
		form.field.focus();
	}
	return box;
}