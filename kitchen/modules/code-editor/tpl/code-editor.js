function showPatternBox(mode, theme){
	var pedt;
	var pattern = new Box('{}', "boxfather/patternbox/"+mode);
	pattern.onopen=function(){
		mode = {"js":"javascript"}[mode] || mode;
		pedt = ace.edit(pattern.window.querySelector("main"));
		pedt.setTheme("ace/theme/"+(theme || "twilight"));
		pedt.getSession().setMode("ace/mode/"+mode);
		pedt.setShowInvisibles(false);
		pedt.setShowPrintMargin(false);
		pedt.resize();
	}
	pattern.onsubmit = function(form){
		editor.session.insert(editor.selection.getCursor(), pedt.session.getValue());
		noChanged = false;
		window.parent.document.querySelector("#wrapper>header>div.tabbar>label[for='"+frame_handle+"']").classList.toggle("changed", true);
	}
}
function saveFile(){
	XHR.push({
		addressee:"/developer/actions/save-file"+location.search,
		body:editor.session.getValue(),
		onsuccess:function(response){
			if(isNaN(response)){
				alertBox(response);
			}else{
				noChanged = true;
				window.parent.document.querySelector("#wrapper>header>div.tabbar>label[for='"+frame_handle+"']").classList.toggle("changed", false);
			}
		}
	});
}