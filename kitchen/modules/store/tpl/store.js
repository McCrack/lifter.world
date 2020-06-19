
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
}

/*********************************************************/

function satNativeFilter(val){
	var path = location.pathname.split(/\//);
		path[2] = path[2] || 0;
		path[3] = (path[3] || 0)^val;
	
	Reload(path);
}
function satFilter(val){
	var path = location.pathname.split(/\//);
	var section = (val / 32)>>0;
	var set = (path[4] || "0").split(/-/);
	set[section] = (set[section] || 0)^Math.pow(2, (val % 32)-1);
	for(var i=section; i--;) set[i] = set[i] || 0;
	path[2] = path[2] || 0;
	path[3] = path[3] || 0;
	path[4] = set.join("-");
	
	Reload(path);
}
function Reload(path){
	XHR.push({
		"addressee":"/store/actions/reload",
		"body":JSON.stringify({
			"category":path[2],
			"native":path[3],
			"filters":(path[4] || 0)
		}),
		"onsuccess":function(response){
			path = path.join("/");
			doc.querySelector("#environment").innerHTML = response;
			window.history.replaceState(null, "Filter set", path);
			window.localStorage.setItem("catalog", path);
		}
	});
}

/*********************************************************/

function createModel(){
	var path = location.pathname.split(/\//);
	var box = modalBox('{}', "store/createbox/"+(path[2] || 0), function(form){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/store/actions/create/"+form.category.value,
			"onsuccess":function(response){
				parseInt(response) ? location.pathname = "/store/showcase/"+response : alertBox(response);
			}
		});
	}, true);
}

/*********************************************************/

function saveFilterset(){
	var filters = {};
	var path = location.pathname.split(/\//);
	var set = doc.querySelectorAll("#filterset>form>table.set");
	for(var j=0; j<set.length; j++){
		var setName = set[j].querySelector("thead>tr>td").textContent.trim();
		if(setName){
			filters[setName] = {};
			var cells = set[j].querySelectorAll("tbody>tr>td");
			for(var i=cells.length; i--;){
				filters[setName][i] = cells[i].textContent.trim();//.toLowerCase();
			}
		}
	}
	XHR.push({
		"addressee":"/store/actions/save-filterset/"+(path[2] || "store"),
		"body":JSON.stringify(filters)
	});
}
function saveOptions(){
	var row, properties = [];
	var path = location.pathname.split(/\//);
	var cells = doc.querySelectorAll("#optionset tbody>tr>td");
	for(var i=0; i<cells.length; i++){
		row = cells[i].textContent.trim();
		if(row){
			properties.push(row);
		}
	}
	XHR.push({
		"addressee":"/store/actions/save-options/"+(path[2] || "store"),
		"body":JSON.encode(properties)
	});
}
function JsonToOptions(json){
	var rows="";
	JSON.parse(json).forEach(function(item){
		rows += 
		"<tr>"+
		"<th bgcolor='white'><span title='Add row' class='tool' onclick='addRow(this)'>&#xe908;</span></th>"+
		"<td contenteditable='true'>"+item+"</td>"+
		"<th bgcolor='white'><span title='Delete row' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>"+
		"</tr>";
	});
	doc.querySelector("#optionset tbody").innerHTML = rows;
}
function jsontofilters(json){
	var sobj = JSON.parse(json);
	var sets="",color=16777215;
	for(var key in sobj){
		sets += "<table class='set' rules='cols' width='100%' cellpadding='4' cellspacing='0' bordercolor='#CCC'><colgroup><col width='30'><col><col width='30'></colgroup><thead><tr class='panel'><th title='Raise set' onclick='raiseSet(this.parent(3))'><div class='raise'>&#xe045;</div></th><td align='center' contenteditable='true'>"+key+"</td><th title='Remove set' onclick='removeSet(this.parent(3))'><span class='tool'>&#xf05f;</span></th></tr></thead><tbody>";
		for(var i=0; i<sobj[key].length; i++){
			sets += "<tr bgcolor='#"+((color^=2037018).toString(16))+"'>"+
			"	<th title='Add value' onclick='addFilterValue(this.parentNode)' bgcolor='white'><span class='tool'>&#xe908;</span></th><td onfocus='focusCell(this)' contenteditable='true'>"+sobj[key][i]+"</td><th bgcolor='white' title='Delete row' onclick='deleteRow(this)'><span class='tool red'>&#xe907;</span></th>"+
			"</tr>";
		}
		sets += "</tbody></table>";
	}
	doc.querySelector("#filterset>form").innerHTML = sets;
}
function filtersToJSON(owner){
	owner = owner || doc;
	var cells, set, setName, filters={};
	set = owner.querySelectorAll("table.set");
	for(var i=0; i<set.length; i++){
		setName = set[i].querySelector("thead>tr>td").textContent.trim();
		cells = set[i].querySelectorAll("tbody>tr>td");
		filters[setName] = [];
		for(var j=0; j<cells.length; j++){
			filters[setName].push(cells[j].textContent.trim());
		}
	}
	return JSON.encode(filters);
}
function patternWithoutValidate(owner){
	owner = owner || doc;
	var row, cells, options=[];
	cells = owner.querySelectorAll("tbody>tr>td");
	for(var i=0; i<cells.length; i++){
		row = cells[i].textContent.trim();
		if(row) options.push(row);
	}
	return JSON.encode(options);
}
function addFilterSet(){
	promptBox("Please enter  name of filter set", function(value){
		var set = doc.create("table", 
			{ class:"set", width:"100%", cellpadding:"4", cellspacing:"0", bordercolor:"#CCC", rules:"cols" },
			"<colgroup><col width='30'><col><col width='30'></colgroup>"+
			"<thead>"+
			"<tr class='panel'>"+
			"	<th title='Raise set' onclick='raiseSet(this.parent(3))'><div class='raise'>&#xe045;</div></th>"+
			"	<td align='center' contenteditable='true'>"+value+"</td>"+
			"	<th title='Remove set' onclick='removeSet(this.parent(3))'><span class='tool'>&#xf05f;</span></th>"+
			"</tr>"+
			"</thead>"
		);
		set.appendChild(doc.create("tbody", {},
			"<tr bgcolor='#EFEFEF'>"+
			"	<th title='Add value' onclick='addFilterValue(this.parentNode)' bgcolor='white'><span class='tool'>&#xe908;</span></th><td onfocus='focusCell(this)' contenteditable='true'></td><th bgcolor='white' title='Delete row' onclick='deleteRow(this)'><span class='tool red'>&#xe907;</span></th>"+
			"</tr>"
		));
		doc.querySelector("#filterset>form").appendChild(set);
	});
}

function raiseSet(set){
	var prev = set.previous();
	if(prev){
		set.parentNode.insertBefore(set, prev);
	}else return false;
}
function removeSet(set){
	set.parentNode.removeChild(set);
}
function focusCell(cell){
	var inp = doc.create("input", {class:"input-cell", list:"filters-list", value:cell.textContent.trim(), onblur:"this.parentNode.textContent=this.value"});
	cell.innerHTML = "";
	cell.appendChild(inp);
	inp.focus();
	inp.select();
}
function addFilterValue(row){
	row.insertAfter( doc.create("tr", {"bgcolor":"#DEF"}, "<th title='Add value' onclick='addFilterValue(this.parentNode)' bgcolor='white'><span class='tool'>&#xe908;</span><td onfocus='focusCell(this)' contenteditable='true'></td></th><th bgcolor='white'><span title='Drop row' class='tool red' onclick='deleteRow(this)'>&#xe907;</span></th>") );
}