function CreatePost(){
	XHR.push({
		addressee:"/blogger/actions/create-post/"+LANGUAGE,
		onsuccess:function(response){
			try{
				response = JSON.parse(response);
				let path = location.pathname.split(/\//);
					path[1] = "blogger";
					path[2] = response['language'];
					path[3] = path[3] || 1;
					path[4] = response['PageID'];
				location.pathname = path.join("/");
			}catch(e){ console.log(e.name+": "+e.value) }
		}
	});
}
function SavePost(box,form){
	var TimeOffset = new Date().getTimezoneOffset()*60000;
	var mediasetform = document.querySelector("#mediaset");
		mediasetform.querySelector("iframe").contentWindow.save();
	XHR.push({
		addressee:"/blogger/actions/save-metadata",
		body:JSON.encode({
		PageID:form.PageID.value,
		ID:form.ID.value,
		Ads:form.ads.checked ? "YES" : "NO",
		published:form.published.checked ? "Published" : "Not published",
		category:form.category.value,
		preview:form.querySelector("#cover>img").currentSrc || "",
		video:(function(video){
			return (video.networkState && (video.networkState!=3)) ? video.currentSrc : "";
		})(form.querySelector("#video-cover>video")),
		header:utf8_to_b64(form.header.value.trim().replace(/"/g,"″")),
		subheader:utf8_to_b64(form.subheader.value.trim().replace(/"/g,"″")),
		UserID:form.author.value,
		created:(((form.date.valueAsNumber+form.time.valueAsNumber)+TimeOffset)/1000),
		subtemplate:form.template.value,
		SetID:mediasetform.setid.value,
		keywords:(function(keywords){
			let words = [];
			keywords.split(/,+\s*/g).filter(function(key){ return key.length }).forEach(function(itm){
				words.push(itm.translite());
			});
			return words;
		})(form.keywords.value)
	}),
	onsuccess:function(response){
		var answer = JSON.parse(response);
		for(var key in answer.log){
			box.body.appendChild(doc.create("div", {}, "<tt><b>"+key+"</b>: "+answer.log[key]+"</tt>"));
		}
		box.align();
		if(answer.log['PageID']){
			box.body.appendChild(doc.create("input",{
				value:answer.url,
				readonly:"true",
				onfocus:"copyURL(this)"
			}));
			let CONTENT = EDITOR.getContent();
			XHR.push({
				"Content-Type":"text/html",
				"addressee":"/blogger/actions/save-content/"+answer.log['PageID'],
				"body":CONTENT,
				"onsuccess":function(response){
					if(parseInt(response)){
						box.body.appendChild(doc.create("h3", {}, "Content - <span class='green-txt'>Saved</span>"));
					}else box.body.appendChild(doc.create("h3", {}, "Content - <span class='red-txt'>Failed save or not changes</span>"));

					if(form.amp.checked){ /*********** Google AMP ***********/
						convertToAMP(function(response){
							if(parseInt(response)){
								 box.body.appendChild(doc.create("h3", {}, "Google AMP - <span class='green-txt'>Saved</span>"));
							}else box.body.appendChild(doc.create("h3", {}, "Google AMP - <span class='red-txt'>Failed save or not changes</span>"));
						}, answer.log['PageID'], CONTENT);
					}else XHR.push({ addressee:"/blogger/actions/drop-amp/"+answer.log['PageID'] });

					if(form.ina.checked){ /**** Facebook Instant Articles ****/
						convertToInstantArticles(function(response){
							if(parseInt(response)){
								box.body.appendChild(doc.create("h3", {}, "Facebook Instant Articles - <span class='green-txt'>Saved</span>"));
							}else box.body.appendChild(doc.create("h3", {}, "Facebook Instant Articles - <span class='red-txt'>Failed save or not changes</span>"));
						}, answer.log['PageID'], CONTENT, form);
					}else XHR.push({ addressee:"/blogger/actions/drop-ina/"+answer.log['PageID'] });

					box.align();
				}
			});
		}
	}
	});
}

function convertToInstantArticles(onSave, PageID, content, form){
	content = doc.create("div", {}, content);
	content.querySelectorAll("h3").forEach(function(item){ item.parentNode.replaceChild(doc.create("h2", {}, item.textContent), item); });
	content.querySelectorAll("h4").forEach(function(item){ item.parentNode.replaceChild(doc.create("p", {}, "<b>"+item.textContent+"</b>"), item); });
	content.querySelectorAll("img").forEach(function(img){
		if(img.parentNode.nodeName!="FIGURE"){
			var figure = doc.create("figure");
			img.insertAfter(figure);
			figure.appendChild(img);
		}
	});
	content.querySelectorAll("video").forEach(function(video){
		if(video.parentNode.nodeName!="FIGURE"){
			var figure = doc.create("figure");
			video.insertAfter(figure);
			figure.appendChild(video);
		}
	});
	content.querySelectorAll(".video>iframe").forEach(function(frm){
		frm.width = "480px";
		frm.height = "270px";
	});
	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/blogger/actions/save-ina/"+PageID,
		"body":content.innerHTML,
		"onsuccess":onSave
	});
}
function convertToAMP(onSave, PageID, content){
	content = doc.create("div", {}, content);

	content.querySelectorAll("img").forEach(function(img){
		let amp = doc.create("amp-img",{
			src:img.src,
			width:img.naturalWidth,
			height:img.naturalHeight,
			layout:"responsive"
		})
		img.parentNode.replaceChild(amp, img);
	});
	content.querySelectorAll("video").forEach(function(video){
		let amp = doc.create("amp-video");
		video.attributes.forEach(function(attribute){
			amp.setAttribute(attribute.name, attribute.value);
		});
		amp.setAttribute("layout", "responsive");
		amp.setAttribute("width", video.videoWidth);
		amp.setAttribute("height",video.videoHeight);
		video.parentNode.replaceChild(amp, video);
	});
	content.querySelectorAll(".youtube>iframe").forEach(function(ifm){
		let amp = doc.create("amp-youtube",{
			"data-videoid":ifm.src.split(/\//).pop(),
			"width":"480",
			"height":"270",
			"layout":"responsive"
		})
		ifm.parentNode.replaceChild(amp, ifm);
	});
	content.querySelectorAll("iframe").forEach(function(frm){
		let amp = doc.create("amp-iframe",{
			src:frm.src,
			layout:"responsive"
		})
		frm.parentNode.replaceChild(amp, frm);
	});
	content.querySelectorAll("*[contenteditable]").forEach(function(obj){
		obj.removeAttribute("contenteditable");
	});
	XHR.push({
		"Content-Type":"text/html",
		"addressee":"/blogger/actions/save-amp/"+PageID,
		"body":content.innerHTML,
		"onsuccess":onSave
	});	
}

/***********************/

function copyURL(field){
	field.select();
	document.execCommand('copy');
}