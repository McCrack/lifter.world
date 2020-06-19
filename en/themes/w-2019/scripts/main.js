function toUpPage(){
	requestAnimationFrame(function animateAncor(){
		window.scrollBy(0, -50);
		if(doc.currentScrollTop>20){
			requestAnimationFrame(animateAncor);
		}else window.scrollTo(0, 0);
	});
}

/* INVITE *********************************************************************/

function ShowInvite(){
	var box = modalBox('{}',"xhr/community/invitebox");
	box.onopen=function(){
		FB.XFBML.parse(substrate, function(){});
	}
}

/* LOGIN *********************************************************************/

function fb_login(box){
	FB.login(function(response){
		FB.api("/me?fields=id,name,gender,email,picture", "get", function(response){
			if(box)	box.parentNode.removeChild(box);
			var btn = doc.querySelector("#toolbar .fb.login");
			btn.parentNode.replaceChild(doc.create("img","",{
				style:"height:38px;border-radius:3px;cursor:pointer",
				hspace:"6",
				vspace:"2",
				align:"top",
				onclick:"fb_logout()",
				src:response.picture.data.url
			}), btn);
			citizen = {
				App:"facebook",
				ID:response.id,
				Name:response.name,
				Email:(response.email || "n/a"),
				options:{ gender:response.gender }
			}
			XHR.request("/xhr/community/login", function(){}, JSON.stringify(params));
		});
	},{scope: 'public_profile,email'});
	return false;
}

/* SHARESS *********************************************************************/

var share = {
	facebook:function(){
		FB.ui({
			method	: "share",
			href	: window.location.href,
		}, function(response){ });
	},
	twitter:function(){
		window.open("https://twitter.com/share?url="+location.href+"&text="+doc.querySelector("meta[property='og:title']").content, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=550,width=500");
	}
}
var send = {
	messenger:function(){
		FB.ui({
			method:"send",
			link:location.href,
			display:"iframe"
		});
	},
	whatsapp:function(){
		window.open("whatsapp://send?text="+location.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=320");
	}
}

/*******************************************************************************/