function loadFile(handle, path){
	var environment = doc.querySelector("#wrapper>main");
	var inp = doc.create("input", {
		id:handle,
		type:"radio",
		name:"tab",
		hidden:"hidden"
	});
	inp.onchange = function(){
		(TABBAR.querySelector("label.selected") || {}).classList.remove("selected");
		TABBAR.querySelector("label[for='"+handle+"']").classList.add("selected");
	}
	environment.appendChild(inp);
	let file = path.split(/\//).pop();
	let modes = ["txt","html","css","less","js","php","sql","json","xml"];
	let mode = modes[modes.inArray( file.split(/\./).pop() ) || 0];
	
	reauth();
	var editor = doc.create("iframe",{ "src":"/code-editor/"+mode+"?path="+path+"&handle="+handle});
	environment.appendChild(editor);
	
	var tab_btn = doc.create("label", {class:"tab selected","for":handle, "title":path}, file);
	let close_tab = doc.create("sup", {"title":"Close"}, "✕");
	close_tab.onclick = function(){
		delete(editable[handle]);
		if(inp.checked) (tab_btn.previous() || tab_btn.next() || TABBAR).click();			
		TABBAR.removeChild(tab_btn);
		environment.removeChild(inp);
		environment.removeChild(editor);		
	}
	tab_btn.appendChild(close_tab);
	TABBAR.appendChild(tab_btn);
	tab_btn.click();
}
/*************************************************************************/

var actions = {
	createFolder:function(obj){
		promptBox("new folder name", function(form){
			XHR.push({
				addressee:"/actions/developer/ct_folder",
				body:obj.dataset.path+"/"+form.field.value.trim().translite(),
				onsuccess:function(response){
					obj.nextElementSibling.innerHTML = response;
				}					
			});
		});
	},
	createFile:function(obj){
		promptBox("enter file name", function(form){
			XHR.push({
				addressee:"/actions/developer/ct_file",
				body:obj.dataset.path+"/"+form.field.value.trim().translite(),
				onsuccess:function(response){
					obj.nextElementSibling.innerHTML = response;
				}					
			});
		});
	},
	remove:function(obj){
		confirmBox('Delete "'+((obj.classList.contains("file"))?obj.dataset.name:obj.textContent)+'"?', function(){
			XHR.push({
				addressee:"/actions/developer/remove",
				body:obj.dataset.path,
				onsuccess:function(response){
					obj.parentNode.innerHTML = response;
				}					
			});
		});
	},
	download:function(obj){
		location.href = "/actions/navigator/download?path="+obj.dataset.path;
	},
	upload:function(obj){
		var inp = doc.create("input",{type:"file",name:"files[]",accept:"*.*",multiple:"multiple"});
		inp.onchange = function(){
			XHR.uploader(inp.files, "/actions/navigator/upload?path="+obj.dataset.path, function(response){
				XHR.push({
					addressee:"/actions/developer/ld_folder",
					body:obj.dataset.path,
					onsuccess:function(response){
						obj.nextElementSibling.innerHTML = response;
						obj.previousElementSibling.checked = true;
					}
				});
			});
		}
		inp.click();
	},
	rename:function(obj){
		let path = obj.dataset.path.split(/\//);
		let old = path.pop();
		let box = promptBox("", function(form){
			path.push( form.field.value.trim().translite() );
			XHR.push({
				addressee:"/actions/developer/rename",
				body:JSON.stringify({
					old:obj.dataset.path,
					new:path.join("/")
				}),
				onsuccess:function(response){
					obj.parentNode.innerHTML = response;		
				}
			});
		}).onopen = function(form){
			form.field.value = old;
			form.alert.value = translate['rename'] || "Rename";
			form.field.focus();
		}
	},
	copy:function(obj){
		STANDBY.movelist = [];
		STANDBY.copylist = [obj.dataset.path];
	},
	cut:function(obj){
		STANDBY.movelist = [obj.dataset.path];
		STANDBY.copylist = [];

		let root = obj.nextElementSibling;
		if(root.classList.contains("root")) root.style.opacity=
		obj.style.opacity=0.3;
	},
	paste:function(obj){
		if(STANDBY.movelist.length){
			var addressee = "/actions/developer/move";
			var body = {
				src:STANDBY.movelist,
				dest:obj.dataset.path
			}
		}else if(STANDBY.copylist.length){
			var addressee = "/actions/developer/copy";
			var body = {
				src:STANDBY.copylist,
				dest:obj.dataset.path
			}
		}
		XHR.push({
			addressee:addressee,
			body:JSON.encode(body),
			onsuccess:function(response){
				obj.nextElementSibling.innerHTML = response;
				obj.previousElementSibling.checked = true;

				STANDBY.movelist =
				STANDBY.copylist = [];
			}
		});
	},
	zip:function(obj){
		XHR.push({
			addressee:"/actions/developer/tozip",
			body:obj.dataset.path,
			onsuccess:function(response){
				obj.parentNode.innerHTML = response;
			}
		});
	},
	unzip:function(obj){
		XHR.push({
			addressee:"/actions/developer/unzip",
			body:obj.dataset.path,
			onsuccess:function(response){
				obj.parentNode.innerHTML = response;
			}					
		});
	}
}