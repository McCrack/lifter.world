
function saveSettings(){
	var path = location.pathname.split(/\//);
	if(path[2]){
		if(settings = settingsToJson()){
			XHR.push({
				addressee:"/actions/settings/save/"+path[2]+"/"+(path[3] || ""),
				body:JSON.encode(settings),
				onsuccess:function(response){
					isNaN(response) ? alert(response) : location.reload();
				}
			});
		}
	}
}

function settingsToJson(owner){
	owner = owner || doc;
	var key, section, settings={};
	
	var cells = owner.querySelectorAll("table.settings>tbody>tr>td");
	for(var i=0; i<cells.length; i++){
		if(cells[i].classList.contains("section")){
			section = cells[i].dataset.section || cells[i].textContent.trim();
			settings[section] = {};
		}else{
			key = cells[i].dataset.key || cells[i].textContent.trim();
			settings[section][key] = {
				"type":cells[i].parentNode.dataset.type || prompt('Please enter type of parameter "'+key+'" (enum, set or string)', "srting") || "string",
				"value":cells[++i].textContent.trim(),
				"valid":cells[++i].textContent.trim().split(/,\s*/)
			};
			
			if(settings[section][key]['type']=="enum"){
				if(isNaN(settings[section][key]['valid'].inArray(settings[section][key]['value']))){
					alert(settings[section][key]['value']+" is not valid value");
					return false;
				}
			}else if(settings[section][key]['type']=="set"){
				settings[section][key]['value'].split(/,\s*/).forEach(function(value){
					if(isNaN(settings[section][key]['valid'].inArray(value))){
						alert(value+" is not valid value");
						return false;
					}
				});
			}
		}
	}
	return settings;
}

var jsontosettings = function(json){
	var sobj = JSON.parse(json);
	var rows="";
	for(var section in sobj){
		rows += "<tr align='center' style='color:#EEE;background-color:#069'><td colspan='5' class='section'>"+section+"</td></tr>";
		for(var key in sobj[section]){
			rows += 
			"<tr data-type='"+sobj[section][key]['type']+"'>"+
			"<th title='Add Row' class='tool' onclick='addRow(this)'>+</th>"+
			"<td align='center' contenteditable='true' data-key='"+key+"'>"+key+"</td>"+
			"<td contenteditable='true'>"+sobj[section][key]['value']+"</td>"+
			"<td contenteditable='true'>"+sobj[section][key]['valid']+"</td>"+
			"<th title='Delete Row' class='tool' onclick='deleteRow(this.parentNode)'>✕</th>"+
			"</tr>";
		}
	}
	doc.querySelector("#wrapper>main>table>tbody").innerHTML = rows;
}