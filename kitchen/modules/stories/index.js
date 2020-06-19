function CreatePost(){
	XHR.push({
		addressee:"/stories/actions/create-post",
		onsuccess:function(response){
			let path = location.pathname.split(/\//);
				path[1] = "stories";
				path[2] = path[2] || 1;
				path[3] = response;
			location.pathname = path.join("/");
		}
	});
}

/***********************/

function hexToRgba(hex, alpha){
	hex = hex || "#000000";
	alpha = alpha || "1.0";
	
	var c = hex.substring(1).split('');
	if(c.length==3) c = [c[0], c[0], c[1], c[1], c[2], c[2]];
	c = "0x"+c.join('');
	return "rgba("+[
		(c>>16)&255,
		(c>>8)&255,
		c&255
	].join(",")+", "+alpha+")";
}

/***********************/

function copyURL(field){
	field.select();
	document.execCommand('copy');
}