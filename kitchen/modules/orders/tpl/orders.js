
/* Initialization ****************************************/

window.onload=function(){
	translate.fragment();
	doc.body.className = standby.bodymode;
	doc.querySelector("#leftbar #"+standby.leftbar+".tab").style.display = "block";
}

/*********************************************************/

function openOrder(event){
	var row = event.target;
	if(row.nodeName==="TD"){
		row = row.parentNode;
	}else if(row.nodeName!="TR") return false;
	var path = location.pathname.split(/\//);
		path[2] = path[2] || SORTED;
		path[3] = path[3] || 1;
		path[4] = row.dataset.id;
	location.pathname = path.join("/");
}

function createOrderBox(){
	var box = modalBox("{}", "orders/createorderbox", function(form){
		var params = {
			"citizen":form.citizen.value,
			"name":form.clientname.value.trim(),
			"phone":form.phone.value.trim(),
			"email":form.email.value.trim(),
			"type":form.orderType.value,
			"payment":form.payment.value,
			"price":form.price.value.trim(),
			"delivery":{},
			"body":{}
		}
		form.querySelectorAll("div.environment>table>tbody>tr input[type='number']").forEach(function(item){
			params['body'][item.dataset.id] = item.value;
		});
		if(form.orderType.value=="delivery"){
			var key,
				cells = form.querySelectorAll("fieldset.delivery>table>tbody>tr>td");
			for(var i=0; i<cells.length; i+=2){
				key = cells[i].textContent.trim();
				if(key) params['delivery'][key] = cells[i+1].textContent.trim();
			}
		}

		XHR.push({
			"addressee":"/orders/actions/create",
			"body":JSON.encode(params),
			"onsuccess":function(response){
				if(parseInt(response)){
					box.drop();
					var path = window.location.pathname.split(/\//);
						path[2] = path[2] || "OrderID";
						path[3] = path[3] || 1;
						path[4] = response;
					window.location.pathname = path.join("/");
				}else alert(response);
			}
		});
	});
}

function printOrder(){
	window.open("/orders/blank/"+ORDERID, "Order", "dialogWidth:796px;dialogHeight:550px;center:on;resizable:off;scroll:on;");
}

/*********************************************************/

function focusCell(cell){
	var inp = doc.create("input", {class:"input-cell", "data-content":cell.textContent.trim(), value:cell.textContent.trim()});
		inp.onblur=function(){
			cell.textContent=this.value;
			if(this.value!=this.dataset.content){
				var set = {};
				var cells = cell.parent(2).querySelectorAll("td");
				for(var i=0; i<cells.length; i+=2){
					set[cells[i].textContent.trim()] = cells[i+1].textContent.trim();
				}
				XHR.push({
					"addressee":"/orders/actions/delivery/"+ORDERID,
					"body":JSON.encode(set),
					"onsuccess":function(response){
						document.querySelector("#log").innerHTML = response;
					}
				});
			}
		}
	cell.innerHTML = "";
	cell.appendChild(inp);
	inp.focus();
	inp.select();
}
