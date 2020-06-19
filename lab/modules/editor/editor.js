var KEYMASK = 0;
const CTRLKEY=1,METAKEY=2,ALTKEY=4,SHIFTKEY=8;

new function(){
	var edt = this;
	this.doc = document;
	this.body = this.node = this.doc.querySelector("#content");
	this.toolbar =  this.doc.querySelector("#toolbar");;
	this.range = this.doc.createRange();
	
	this.refresh = function(callback){
		syncContent();
		edt.node = edt.doc.getSelection().focusNode.parentNode;
		//edt.node = edt.range.commonAncestorContainer.nodeType==3 ? edt.range.commonAncestorContainer.parentNode : edt.range.commonAncestorContainer;
		edt.range = edt.doc.getSelection().getRangeAt(0);
		setTimeout(function(){edt.setSelection();}, 150);
	}
	this.doc.setSelection = this.setSelection = function(){
		//edt.body.focus();
		var selection=edt.doc.getSelection();
		selection.removeAllRanges();
		selection.addRange(edt.range);
		return selection;
	}
	this.doc.spellCheck = function(){
		edt.body.spellcheck = edt.body.spellcheck.toggle();
	}
	this.doc.insertTag=function(command, tag){
		if(edt.node.nodeName!=tag){
			edt.doc.execCommand(command, false, tag);
			edt.refresh();
		}
	}
	this.doc.formatblock = function(tag){
		edt.doc.execCommand("formatblock", false, tag);
		edt.refresh();
	}
	this.doc.imgBox=function(){
		new parent.Box(null, "boxfather/imagebox", function(form){
			let src = form.querySelector("iframe").contentWindow.getSelectedURLs();

			edt.setSelection();
			edt.doc.execCommand("insertImage", false, src);
			edt.refresh();
		});
	}
	this.doc.videoBox=function(){
		new parent.Box(null, "editor/videobox", function(form){
			let src = form.querySelector("iframe").contentWindow.getSelectedURLs();
			form.drop();

			edt.setSelection();
			edt.doc.execCommand("insertHTML",false, "<video controls src='"+src+"'></video>");
			edt.refresh();
		});
	}
	this.doc.youtubeBox=function(){
		new parent.Box(null, "editor/youtubebox", function(form){
			let src = form.link.value;
			form.drop();
			
			edt.setSelection();
			edt.doc.execCommand("insertHTML",false, "<figure class='video youtube'><iframe frameborder='0' allowfullscreen src='"+form.link.value+"'></iframe></figure>");
			edt.refresh();
		});
	}
	this.doc.patternBox=function(){
		window.parent.showHTMLPattern(function(pattern){
			edt.setSelection();
			edt.doc.execCommand("insertHTML",false, pattern);
			edt.refresh();
		});
	}
	this.doc.figureBox=function(){
		new parent.Box(null, "editor/figurebox", function(form){
			let link = form.link.value.trim(),
				caption = form.caption.value.trim(),
				src = form.querySelector("iframe").contentWindow.getSelectedURLs();

			form.drop();
			edt.setSelection();
			if(link){
				edt.doc.execCommand("insertHTML",false, "<figure><img src='"+src+"'><figcaption><a href='"+link+"' target='_blank' rel='nofolow'>"+caption+"</a></figcaption></figure>");
			}else edt.doc.execCommand("insertHTML",false, "<figure><img src='"+src+"'><figcaption>"+caption+"</figcaption></figure>");
			edt.refresh();
		});
	}
	this.doc.albumBox=function(){
		var box = new parent.Box(null, "navigator/box",function(form){
			let album = [];
			box.window.quarySelector("iframe").contentWindow.document.getSelected(true).forEach(function(item){
				album.push("<figure><img src='"+item.url+"'><figcaption></figcaption></figure>");
			});
			parent.supervisor.drop();
			edt.setSelection();
			edt.doc.execCommand("insertHTML", false, album.join("\n"));
			edt.refresh();
		});
	}
	this.doc.freeTag=function(btn){
		promptBox("Insert HTML Tag", function(form){
			let node = edt.doc.create(form.field.value);
			edt.range.surroundContents(node);
			edt.range.setStartAfter(node);
			edt.refresh();
		},["dark-btn-bg"]);
	}
	this.doc.setProperty=function(property, value){
		edt.node.setAttribute(property,value);
		edt.refresh();
	}
	this.doc.drop = function(){
		if(edt.body.compareDocumentPosition(edt.node) & 16){
			if(edt.node.isContentEditable){
				edt.node.outerHTML = edt.node.innerHTML;
			}else edt.node.parentNode.removeChild(edt.node);
		}
		edt.refresh();
	}
	this.doc.list=function(type){
		edt.doc.execCommand(type);
	}
	this.doc.setFontSize=function(value){
		edt.node.style.fontSize = parseInt(value)+"px";
	}
	this.body.onpaste=function(event){
		event.preventDefault();
		var data = [];
		event.clipboardData.getData("text").split(/\n+/).forEach(function(itm){
			if(itm.trim()) data.push("<p>"+itm.trim()+"</p>");
		});
		edt.doc.execCommand("insertHTML", false, data.join("\n"));
		edt.refresh();
	}
	this.doc.createlink = function(){
		if(edt.range.collapsed) return false;
		new Box('{}', "editor/linkbox", function(form){
			form.drop();
			var node = edt.doc.create("a", {
				href:form.url.value,
				target:form.target.value
			}, edt.range.toString());
			if(form.nofolow.checked) node.rel = "nofolow";

			edt.range.surroundContents(node);
			edt.range.setStartAfter(node);
			edt.setSelection();

			edt.refresh();
		});
	}
	this.doc.breakline=function(){
		let p = doc.create("p");
		edt.node = edt.doc.getSelection().focusNode.parentNode;
		if(edt.body.compareDocumentPosition(edt.node) & 16){
			while(edt.body.compareDocumentPosition(edt.node.parentNode)){
				edt.node = edt.node.parentNode;
			}
			edt.range.setEndAfter(edt.node);
			p.textContent = edt.range.extractContents().textContent;
			edt.node.insertAfter(p);
		}else edt.doc.formatblock("p");
		edt.range.selectNodeContents(p);
		edt.range.collapse(true);
		edt.refresh();
	}
	this.doc.properties = function(){
		var box = new Box('{}', "editor/propertiesbox/"+edt.node.nodeName, function(form){
			box.body.querySelectorAll("table>tbody>tr>td:nth-child(even)").forEach(function(cell){
				if(cell.textContent.trim()){
					edt.node.setAttribute(cell.previousElementSibling.textContent, cell.textContent);
				}else edt.node.removeAttribute(cell.previousElementSibling.textContent);
			});
			box.drop();
			edt.refresh();
		});
		box.onopen = function(){
			var tbody = box.body.querySelector("tbody");
			tbody.querySelectorAll("tr>td:nth-child(odd)", true).forEach(function(cell){
				cell.nextElementSibling.textContent = edt.node.getAttribute(cell.textContent);
			});
			for(var key in edt.node.dataset){
				tbody.appendChild(doc.create("tr",{},"<td>"+key+"</td><td contenteditable='true'>"+edt.node.dataset[key]+"</td>"));
			}
		}
	}
	
	this.body.onkeydown=function(event){
		KEYMASK = (event.ctrlKey * CTRLKEY)|(event.metaKey * METAKEY)|(event.altKey * ALTKEY)|(event.shiftKey * SHIFTKEY);

		if(KEYMASK & (CTRLKEY|METAKEY)){
			switch(event.keyCode){
				case 83:														// Key "s" - Save
					event.preventDefault();
					save();
				break;
				case 66: 														// Key "b" - bold
					event.preventDefault();
					edt.doc.insertTag('bold', 'B');
				break;
				case 73: 														// Key "i" - Open image box
					event.preventDefault();
					edt.doc.imgBox();
					//edt.doc.insertTag('italic','I');
				break;

				case 76: 														// Key "l" - Create link
					event.preventDefault();
					edt.doc.createlink();
				break;

				case 85: 														// Key "u" - underline
					event.preventDefault();
					edt.doc.insertTag('underline','U')
				break;
				case 8: 														// Key "delete" and key "backspace" -
				case 46: 														// drop selected tag
					event.preventDefault();
					edt.doc.drop();
				break;
				case 13: 														// Key "Enter" - paragraph
					event.preventDefault();
					edt.doc.formatblock('p');
				break;
				default:break;
			}
		}else if(KEYMASK & SHIFTKEY){
			
		}else{
			switch(event.keyCode){
				case 13: 														// Key "Enter" - breakline
					event.preventDefault();
					edt.doc.breakline();
				break;
				case 33:
				case 34:
				case 35:
				case 36:
				case 37:
				case 38:
				case 39:
				case 40: edt.body.onkeyup = edt.body.onclick; break;
				default: break;
			}
		}
	}
	this.body.onkeyup=function(event){
		KEYMASK = (event.ctrlKey * CTRLKEY)|(event.metaKey * METAKEY)|(event.altKey * ALTKEY)|(event.shiftKey * SHIFTKEY);
	}
	this.body.onclick = function(event){
		event.preventDefault();
		
		edt.node = edt.doc.getSelection().focusNode;
		if(edt.node.nodeType==3) edt.node = edt.node.parentNode
		if(!edt.node.isContentEditable){
			while(!edt.node.parentNode.isContentEditable){
				edt.node = edt.node.parentNode;
			}
		}
		
		CODE.find(event.target.outerHTML,{});
		
		edt.range = edt.doc.getSelection().getRangeAt(0);
		edt.toolbar.fsize.value = edt.node.getCss("font-size");
		edt.body.onkeyup = null;
	}
}