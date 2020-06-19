<script>
(function(bar){
	bar.onmousedown=function(event){
		event.preventDefault();
		var mount = bar.parentNode.parentNode,
			y = event.clientY - mount.offsetTop,
			x = event.clientX - mount.offsetLeft;
		var substrate = document.create("div",{id:"substrate"});
		document.body.appendChild(substrate);
		document.onmousemove=function(event){
			let top = event.clientY - y;
			let left = event.clientX - x;
			mount.style.top = (top > 0) ? top+"px" : "0";
			mount.style.left = (left > 0) ? left+"px" : "0";
		}
		document.onmouseup=function(){
			document.onmousemove = null;
			document.body.removeChild(substrate);
		}
	}
})(document.currentScript.parentNode)
</script>