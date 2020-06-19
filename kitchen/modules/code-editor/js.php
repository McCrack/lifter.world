<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/modules/code-editor/tpl/code-editor.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d=base" defer charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<style>
		body>main{
			width:100%;
			height:calc(100% - 36px);
		}
		</style>
	</head>
	<body>
		<header class="h-bar dark-btn-bg">
            <div class="toolbar l">
				<label title="save" data-translate="title" class="tool" onclick="saveFile()">💾</label>
				<label class="tool" title="JS Patterns" onclick="showPatternBox('js', 'ambiance')">⌘</label>
			</div>
        </header>
		<main><?include_once($_GET['path'])?></main>
		<script>
			var editor = ace.edit(document.currentScript.previousElementSibling);
			editor.setTheme("ace/theme/ambiance");
			editor.getSession().setMode("ace/mode/javascript");
			editor.setShowInvisibles(false);
			editor.setShowPrintMargin(false);
			editor.resize();
			var noChanged = true;
			var frame_handle = "<?=$_GET['handle']?>";
			editor.session.on('change', function(event){
				if(noChanged && editor.curOp && editor.curOp.command.name){
					noChanged = false;
					window.parent.document.querySelector("#wrapper>header>div.tabbar>label[for='"+frame_handle+"']").classList.toggle("changed", true);
				}
			});

			window.addEventListener("keydown",function(event){
				if((event.ctrlKey || event.metaKey) && event.keyCode==83){
					event.preventDefault();
					saveFile();
				}
			});
		</script>
    </body>
</html>