<?php
	$handle = "s:".time();

	$group = "";
	$staff = $mySQL->get("SELECT `CommunityID`,`Name`,`Group` FROM gb_staff CROSS JOIN gb_community USING(CommunityID) ORDER BY `Group`");
	$types = $mySQL->getGroup("SELECT type FROM gb_task_shunter GROUP BY type")['type'];
?>
<div id="<?=$handle?>" class="mount" style="width:720px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.questions-box div.box-body{
		
	}
	.questions-box>.box-caption{
		font-size:18px;
	}
	.questions-box input{
		padding:4px;
		font-size:12px;
		border-radius:3px;
		vertical-align:middle;
		border:1px solid #AAA;
		box-sizing:border-box;
		box-shadow:inset 0 0 5px -2px rgba(0,0,0, .5);
		background-image:linear-gradient(to top, #FFF, #DDD);
	}
	tr.question{
		font-weight:bold;
		outline:1px solid #AAA;
	}
	th.drop-question{
		cursor:pointer;
	}
	th.drop-question:hover{
		background-color:#111;
	}
	</style>
	<form class="box questions-box light-btn-bg" autocomplete="off">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption dark-btn-bg gold-txt">&#xe9d7;<?include_once("components/movebox.php")?></div>
		<div class="h-bar">Questions</div>
		<div class="box-body">
			<table width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
				<colgroup><col width="28"><col><col width="60"><col width="28"></colgroup>
				<thead>
					<tr class="dark-btn-bg" height="38">
						<th class="tool" title="add question" data-translate="title" onclick="addQuestion(this.parent(3))">+</th>
						<th><span data-translate="textContent">question</span> ❯ <span data-translate="textContent">answer</span></th>
						<th colspan="2" data-translate="textContent">value</th>
					</tr>
				</thead>
				<tbody>
					<?
					$questionnaire = JSON::load("patterns/json/questionnaire.json");
					foreach($questionnaire['questions'] as $variable=>$obj):?>
						<tr class="question light-btn-bg">
							<th class="logo-bg">❯</th>
							<td contenteditable="true"><?=$obj['question']?></td>
							<td contenteditable="true" class="white-bg" align="center"><?=$variable?></td>
							<th class="drop-question logo-bg" title="delete question" data-translate="title" onclick="deleteQuestion(this.parentNode)">✕</th>
						</tr>
						<?foreach($obj['answers'] as $answer=>$val):?>
						<tr>
							<th class="tool" title="add answer" data-translate="title" onclick="addRow(this.parentNode)">+</th>
							<td contenteditable="true"><?=$answer?></td>
							<td contenteditable="true" align="center"><?=$val?></td>
							<th class="tool" title="delete answer" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
						</tr>
						<?endforeach?>
					<?endforeach?>
				</tbody>
			</table>
		</div>
		<div class="box-footer dark-btn-bg" align="right">
			<div class="toolbar left">
				<span data-translate="textContent">formula</span>: <input name="formula" value="<?=$questionnaire['formula']?>" size="30">
			</div>
			<button type="submit" class="light-btn-bg" data-translate="textContent" name="send">save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			var timeout;
			form.onreset=function(event){form.drop()}
			form.onsubmit=function(){
				XHR.push({
					addressee:"/shunter/actions/questionnaire",
					body:JSON.encode({
						formula:form.formula.value,
						questions:(function(questions,variable){
							var cells = form.querySelectorAll("tbody>tr>td");
							for(var i=0; i<cells.length; i++){
								if(cells[i].parentNode.classList.contains("question")){
									variable = cells[i+1].textContent.trim();
									questions[variable] = {
										question:cells[i].textContent.trim(),
										answers:{}
									};
									i++;
								}else{
									questions[variable]['answers'][cells[i].textContent.trim()] = cells[++i].textContent.trim();
								}
							}
							return questions;
						})({})
					}),
					onsuccess:function(response){
						if(parseInt(response)) form.drop();
					}
				});
			}
		})(document.currentScript.parentNode);
		function addQuestion(table){
			var tbody = table.querySelector("tbody");
			let question = doc.create("tr",{class:"question light-btn-bg"});
			[
				doc.create("th",{class:"logo-bg"},"❯"),
				doc.create("td",{contenteditable:"true"}),
				doc.create("td",{contenteditable:"true",class:"white-bg",align:"center"}),
				doc.create("th",{class:"drop-question logo-bg",title:"Delete Question",onclick:"deleteQuestion(this.parentNode)"},"✕"),
			].forEach(function(cell){
				question.appendChild(cell);
			});
			tbody.appendChild(question);
			let row = doc.create("tr");
			[
				doc.create("th",{class:"tool",title:"Add Answer",onclick:"addRow(this.parentNode)"},"+"),
				doc.create("td",{contenteditable:"true"}),
				doc.create("td",{contenteditable:"true"}),
				doc.create("th",{class:"tool",title:"Delete Answer",onclick:"deleteRow(this.parentNode)"},"✕"),
			].forEach(function(cell){
				row.appendChild(cell);
			});
			tbody.appendChild(row);
		}
		function deleteQuestion(question){
			row = question.nextElementSibling;
			while(row && !row.classList.contains("question")){
				row.parentNode.removeChild(row);
				row = question.nextElementSibling;
			}
			question.parentNode.removeChild(question);
		}
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>