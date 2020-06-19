
function saveWordlist(){
	var path = window.location.pathname.split(/\//);
	if(path[3]){
		XHR.push({
			addressee:"/actions/wordlist/sv_wordlist/"+path[2]+"/"+path[3],
			body:wordlistToJson(),
			onsuccess:function(response){
				isNaN(response) ? alert(response) : location.reload();
			}
		});
	}else alertBox("wordlist not selected");	
	return false;
}
function createWordlist(){
	var path = window.location.pathname.split(/\//);
	if(path[2]){
		promptBox("wordlist name", function(form){
			XHR.push({
				addressee:"/actions/wordlist/ad_wordlist",
				body:JSON.stringify({
					"domain":path[2],
					"name":form.field.value.trim()
				}),
				onsuccess:function(response){
					if(parseInt(response)){
						window.location.pathname = "/wordlist/"+path[2]+"/"+form.field.value.trim(); 
					}else alert(response);
				}
			});
		},["dark-btn-bg"]);
	}else alertBox("domain not selected",["logo-bg"]);
	return false;
}
function removeWordlist(){
	var path = window.location.pathname.split(/\//);
	if(path[3]){
		confirmBox("delete wordlist", function(){
			XHR.push({
				addressee:"/actions/wordlist/rm_wordlist/"+path[2]+"/"+path[3],
				onsuccess:function(response){
					parseInt(response) ? location.pathname = "wordlist" : alert(response);
				}
			});
		});
	}else alertBox("wordlist not selected");
}
function addLanguage(){
	promptBox("language index", function(form){
		if(/^[a-z]{2}$/.test(form.field.value)){
			doc.querySelector("#wordlist>thead>tr").lastElementChild.insertAdjacentElement("beforebegin", doc.create("th",{},form.field.value));
			doc.querySelectorAll("#wordlist>tbody>tr").forEach(function(row){
				row.lastElementChild.insertAdjacentElement("beforebegin", doc.create("td",{contenteditable:"true"}));
			});
		}else alertBox("incorrect language",["logo-bg","large-txt"]);
	},["active-bg"]);
}
function wordlistToJson(){
	var wordlist={};
	var langs=doc.querySelectorAll("#wordlist>thead>tr>th");
	langs.forEach(function(cell){ wordlist[cell.textContent.trim()]={} });

	doc.querySelectorAll("#wordlist>tbody>tr").forEach(function(row){
		var key;
		row.querySelectorAll("td").forEach(function(cell, j){
			if(j>0){
				wordlist[langs[j-1].textContent][key]=cell.textContent.trim();
			}else key = cell.textContent.trim();
		});
	});
	return JSON.encode(wordlist);
}
var jsontowordlist = function(json){
	var wobj = JSON.parse(json);
	var keys=[];
	var headrow = doc.querySelector("#wordlist>thead>tr");
		headrow.innerHTML = "";

	headrow.appendChild( doc.create("td",{class:"l",width:"36"},"<label class='tool' title='Show Pattern' onclick='showPattern(wordlistToJson(), `jsontowordlist`)'>⌘</label>") );
	headrow.appendChild( doc.create("td", {align:"center"}, "<b>Keys</b>"));
	for(var language in wobj){
		headrow.appendChild(doc.create("th", {}, language));
		keys = keys.concat(wobj[language]);
	}
	headrow.appendChild( doc.create("td",{class:"r",width:"36"},"<label class='tool' title='Add Language' onclick='addLanguage()'>🌎</label>") );
	
	var tbody = doc.querySelector("#wordlist>tbody");
		tbody.innerHTML = "";
	for(var key in keys[0]){
		var row = doc.create("tr");
		row.appendChild(doc.create("th",{title:"Add Row",class:"tool",onclick:"addRow(this)"},"+"));
		row.appendChild(doc.create("td",{align:"center",contenteditable:"true"},key));
		for(var lang in wobj){
			row.appendChild(doc.create("td",{contenteditable:"true"},wobj[lang][key]));
		}
		row.appendChild(doc.create("th",{title:"Delete Row",class:"tool",onclick:"deleteRow(this)"},"✕"));
		tbody.appendChild(row);
	}
}