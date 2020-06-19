
/* Initialization ****************************************/

var SECTION = "showcase";

var Standby = (window.localStorage[SECTION] || "undefined").jsonToObj() || {
	"bodymode":"rightmode",
	"leftbar":"items",
	"rightbar":"images"
};
var standby = new Proxy(Standby,{
	get(target, name){ return target[name] || null; },
	set(target, name, value){
		target[name] = value;
		window.localStorage[SECTION] = JSON.stringify(Standby);
	}
});

window.onload=function(){
	translate.fragment();
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
	doc.querySelector("#rightbar #"+standby.rightbar+".tab").style.display = "block";
	
	Slideshow(doc.querySelector("#images"));
}

/*********************************************************/

function Slideshow(tab){
	var slideshow = tab.querySelector("#slideshow");
	var onSrlTmr, tmr, slides = [];
	tab.setSlide = function(sld){
		var speed, offset = slides[sld].offsetLeft - slideshow.offsetLeft;
		tmr = setInterval(function(){
			speed = (offset - slideshow.scrollLeft) / 4;
			if((offset - slideshow.scrollLeft) > 4){
				slideshow.scrollLeft += speed;
			}else if((slideshow.scrollLeft - offset) > 4){
				slideshow.scrollLeft += speed;
			}else{
				slideshow.scrollLeft = offset;
				clearInterval(tmr);
			}
		}, 20);
	}
	tab.querySelector(".leftpoint").onclick = tab.querySelector(".rightpoint").onclick = function(){
		var sld = Number(slideshow.dataset.current)+Number(this.dataset.dir);
		if(sld < 0){
			tab.setSlide(0);
		}else if(sld>(slides.length-1)){
			tab.setSlide(slides.length-1);
		}else tab.setSlide(sld);
	}
	tab.querySelector(".imagelist").onclick = function(event){
		var images = tab.querySelectorAll(".imagelist>img");
		for(var i=0; i<images.length; i++){
			if(images[i].contains(event.target)) tab.setSlide(i);
		}
	}
	slideshow.onscroll=function(){
		clearTimeout(onSrlTmr);
		onSrlTmr = setTimeout(function(){
			slideshow.dataset.current = ((slideshow.scrollLeft+10) / slideshow.offsetWidth)>>0;
		}, 400);
	}
	tab.fillSlideshow = function(){
		var images = tab.querySelectorAll(".imagelist>img");
		slideshow.innerHTML = "";
		slides = [];
		for(var i=0; i<images.length; i++){
			var slide = doc.create("div", {"class":"slide", "style":"background-image:url("+images[i].src+")"});
			slideshow.appendChild(slide);
			slides.push(slide);
		}
	}
	tab.removeSlide = function(){
		var image = tab.querySelectorAll(".imagelist>img")[slideshow.dataset.current];
		image.parentNode.removeChild(image);
		tab.fillSlideshow();
	}
	tab.fillSlideshow();
}
function addImages(){
	var box = new Box('{}', "store/imagesbox", false);
	var tab = doc.querySelector("#images.tab");
	var imagelist = tab.querySelector(".imagelist");
	box.onsubmit = function(form){
		var items = box.getModuleContent(form);
		for(var i=0; i<items.length; i++){
			imagelist.appendChild(doc.create("img", {src:items[i]}));
		}
		tab.fillSlideshow();
		box.drop();
	}
}
function discountBox(field){
	var box = modalBox('{}', "store/discountbox/"+field.dataset.id, function(form){
		field.value = form.label.value;
		field.dataset.id = form.DiscountID.value;
		box.drop();
	}, true);
	box.onopen = function(){
		box.window.onreset = function(){
			field.value = "";
			field.dataset.id = 0;
			box.drop();
		}
	}
}
function stockBox(field){
	var environment = doc.querySelector("#environment");
	var box = modalBox('{}', "store/stockbox/"+environment.item.value, function(form){
		var params = {};
		var cells = form.querySelectorAll("tbody>tr>td");
		for(var i=0; i<cells.length; i+=2){
			var key = cells[i].textContent.trim();
			var val = cells[i+1].textContent.trim();
			if(key && val) params[key] = val;
		}
		box.drop();
		XHR.push({
			"addressee":"/store/actions/save-stock/"+environment.item.value,
			"body":JSON.stringify(params),
			"onsuccess":function(response){
				if(isNaN(response)){
					alertBox(response);
				}else field.value = response;
			}
		});
	}, true);
}
function createModel(){
	var environment = doc.querySelector("#environment");
	var box = modalBox('{}', "store/createbox/"+environment.category.value, function(form){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/store/actions/create/"+form.category.value,
			"onsuccess":function(response){
				if(parseInt(response)){
					location.pathname = "/store/showcase/"+response;
				}else alertBox(response);
			}
		});
	}, true);
}
function addItem(){
	var form = doc.querySelector("#environment");
	if(form.ModelID.value){
		XHR.push({
			"Content-Type":"text/plain",
			"addressee":"/store/actions/add/"+form.ModelID.value,
			"onsuccess":function(response){
				if(isNaN(response)){
					alertBox(response);
				}else location.pathname = "/store/showcase/"+form.ModelID.value+"/"+response;
			}
		});
	}else alertBox("select model");
}
function saveItem(){
	var form = doc.querySelector("#environment");
	var params = {
		"ItemID":form.item.value,
		"PageID":form.ModelID.value,
		"CategoryID":form.category.value,
		"RelatedCategoryID":form.related.value,
		"DiscountID":form.discount.dataset.id || 0,
		"template":form.template.value,
		"purchase":form.purchase.value,
		"selling":form.selling.value,
		"currency":form.currency.value,
		"units":form.units.value,
		"dumping":parseInt(form.dumping.value) || 0,
		"status":form.status.value, 
		"outstock":form.outstock.value, 
		"preview":form.querySelector("#preview").contentWindow.document.getImage(),
		"label":form.label.value,
		"name":form.pName.value,
		"brand":form.brand.value,
		"images":(function(){
			var imglist = {};
			var img = doc.querySelectorAll("#images>.imagelist>img");
			for(var i=0; i<img.length; i++){
				imglist[i] = img[i].src;
			}
			return imglist;
		})(),
		"options":(function(){
			var properties = [];
			var cells = doc.querySelectorAll("table#properties>tbody>tr>td");
			for(var i=0; i<cells.length; i++){
				var val = cells[i].textContent.trim();
				if(val){ properties.push(val); }
			}
			return properties;
		})(),
		"filters":(function(){
			var filterset = [];
			var set = 	doc.querySelectorAll("#filters>fieldset");
			for(var j=0; j<set.length; j++){
				var offset, section = [0];
				var inp = set[j].querySelectorAll("label>input:checked");
				for(var i=inp.length; i--;){
					offset = ((inp[i].value-1) / 32)>>0;
					for(var y=0; y<offset; y++) section[y] = section[y] || 0;
					section[offset] |= Math.pow(2, (inp[i].value-1) % 32);
				}
				for(var i=section.length; i--;) section[i] = (section[i]>>>0).toString(10);
				filterset.push( section || 0);
			}
			return filterset;
		})()
	}
	if(form.reference.checked) params['reference'] = true;
	XHR.push({
		"addressee":"/store/actions/save",
		"body":JSON.encode(params),
		"onsuccess":function(response){
			saveDescription(form.ModelID.value);
		}
	});
	return false;
}
function saveDescription(PageID){
	if(PageID){
		var content = doc.querySelector("#description.tab>iframe.HTMLDesigner").contentWindow.document.getValue();
		XHR.push({
			"Content-Type":"text/html",
			"addressee":"/store/actions/save-description/"+PageID,
			"body":content,
			"onsuccess":function(response){
				if(isNaN(response)){
					alertBox(response);
				}else location.reload();
			}
		});
	}
}
function remove(){
	var form = doc.querySelector("#environment");
	if(form.item.value){
		confirmBox("remove item", function(){
			XHR.push({
				"Content-Type":"text/html",
				"addressee":"/store/actions/remove-item/"+form.item.value,
				"onsuccess":function(response){
					if(response>0){
						location.pathname = "/store/showcase/"+form.ModelID.value;
					}
				}
			});
		});
	}else if(form.ModelID.value){
		confirmBox("remove model", function(){
			XHR.push({
				"Content-Type":"text/html",
				"addressee":"/store/actions/remove-model/"+form.ModelID.value,
				"onsuccess":function(response){
					if(response>0) location.pathname = "/store";
				}
			});
		});
	}else{
		alertBox("nothing selected");
		return false;
	}
}
function backToCatalog(){
	var path = window.localStorage.getItem("catalog");
	if(path){
		location.pathname = path;
	}else location.pathname = "/store";
}

/* Optionset *********************************************/

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
	doc.querySelector("#properties>tbody").innerHTML = rows;
}