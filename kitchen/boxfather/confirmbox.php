<style>
	div.alert-box{
	display:flex;
	flex-wrap:wrap;
	align-items:center;
	justify-content:center;
}
div.alert-box>output{
	font-size:22px;
}
div.alert-box>input[name='field']{
	width:100%;
	padding:10px;
	margin:8px 0;
	border-width:0;
	text-align:center;
	box-sizing:border-box;
}
</style>
<form class="box <?=implode(' ', $classes)?>" style="width:360px;">
	<div class="h-bar">
		<script>
		(function(bar){
			bar.onmousedown=function(event){
				event.preventDefault();
				var form = bar.parentNode,
					y = event.clientY - form.offsetTop,
					x = event.clientX - form.offsetLeft;
				document.onmousemove=function(event){
					let top = event.clientY - y;
					let left = event.clientX - x;
					form.style.top = (top > 0) ? top+"px" : "0";
					form.style.left = (left > 0) ? left+"px" : "0";
				}
				document.onmouseup=function(){document.onmousemove = null;}
			}
		})(document.currentScript.parentNode)
		</script>
	</div>
	<div class="box-body alert-box" align="center">
		<output name="alert"></output>
	</div>
	<div class="box-footer" align="right">
		<button class="light-btn-bg" type="submit">Ok</button>
		<button class="dark-btn-bg" type="reset" data-translate="textContent">cancel</button>
	</div>
	<script>
	(function(form){
		form.onreset=function(){form.drop()}
	})(document.currentScript.parentNode)
	</script>
</form>