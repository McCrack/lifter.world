<?php

$statuses = $mySQL->getRow("SHOW COLUMNS FROM gb_task_shunter LIKE 'status'")['Type'];
eval("\$statuses = ".preg_replace("/^enum/", "array", $statuses).";");

$prohibited = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden statuses'], -1, PREG_SPLIT_NO_EMPTY);
$statuses = array_diff($statuses, $prohibited);
$statuses[] = 'hidden';

if(PRIVILEGED){
	$community = $mySQL->getGroup("SELECT CommunityID FROM gb_staff")['CommunityID'];
	$community = array_diff($community, preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden performers'], -1, PREG_SPLIT_NO_EMPTY));
}else $community = [COMMUNITY_ID];

$types = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['showed types'], -1, PREG_SPLIT_NO_EMPTY);

if(PRIVILEGED):?>
<section class="task-feed">
	<div class="h-bar dark-btn-bg" data-translate="textContent">STREAM ❯</div>
	<aside data-status="new" ondragover="event.preventDefault()" ondrop="drop(event)">
	<?$stream = $mySQL->get("
	SELECT * FROM gb_task_timing 
	CROSS JOIN gb_task_shunter USING(TaskID) 
	LEFT JOIN gb_community USING(CommunityID) 
	WHERE type IN ({arr}) AND CommunityID IS NULL AND status NOT IN ({arr})
	ORDER BY type, rank DESC", $types, $community, $statuses);

		foreach($stream as $task): $performer = empty($task['CommunityID']) ? COMMUNITY_ID : $task['CommunityID']?>
		<div id="task-<?=$task['TaskID']?>" class="slot" draggable="true" ondragstart="drag(event)" data-id="<?=$task['TaskID']?>" data-performer="<?=$performer?>">
			<div class="card snippet <?=$task['type']?>">
				<div class="preview"><img src="<?=$task['image']?>" alt="&#xe94a;"></div>
				<div class="header"><span><?=$task['header']?></span></div>
				<div class="task"><?=$task['task']?></div>
				<div class="options">
					<span class="created red-txt"><?=date("d M, H:i", $task['created'])?></span>
					<span class="performer"><?=$task['Name']?></span>
				</div>
				<span class="type <?=$task['type']?>"></span>
			</div>
			<?if($task['link']):?><a href="<?=$task['link']?>" target="_blank" title="follow">❯</a><?endif?>
			<label data-id="<?=$task['TaskID']?>" class="drop-task">✕</label>
		</div>
		<?endforeach?>
		<script>
		(function(stream){
			stream.onscroll=function(){STANDBY.streamScrollTop = stream.scrollTop}
			if(STANDBY.streamScrollTop) stream.scrollTop = STANDBY.streamScrollTop;
		})(document.currentScript.parentNode)
		</script>
	</aside>
</section>
<?endif;
foreach($statuses as $status):?>
<section class="task-feed">
	<div class="h-bar active-bg" data-translate="textContent"><?=$status?> ❯</div>
	<aside <?if(PRIVILEGED):?>class="privileged"<?endif?> data-status="<?=$status?>" ondragover="event.preventDefault()" ondrop="drop(event)">
	<?$stream = $mySQL->get("
	SELECT * FROM gb_task_timing 
	CROSS JOIN gb_task_shunter USING(TaskID) 
	LEFT JOIN gb_community USING(CommunityID) 
	WHERE status LIKE {str} AND type IN ({arr}) AND CommunityID IN ({arr})
	ORDER BY SortID", $status, $types, $community);

	foreach($stream as $task):?>
		<div id="task-<?=$task['TaskID']?>" class="slot" draggable="true" ondragstart="drag(event)" data-id="<?=$task['TaskID']?>" data-performer="<?=$task['CommunityID']?>">
			<div class="card snippet <?=$task['type']?>">
				<div class="preview"><img src="<?=$task['image']?>" alt="&#xe94a;"></div>
				<div class="header"><span><?=$task['header']?></span></div>
				<div class="task"><?=$task['task']?></div>
				<div class="options">
					<span class="created red-txt"><?=date("d M, H:i", $task['created'])?></span>
					<span class="performer"><?=$task['Name']?></span>
				</div>
				<span class="rank <?=$task['type']?>"><?=$task['rank']?></span>
			</div>
			<?if($task['link']):?><a href="<?=$task['link']?>" target="_blank" title="follow">❯</a><?endif?>
			<label data-id="<?=$task['TaskID']?>" class="drop-task">✕</label>
		</div>
	<?endforeach?>
	</aside>
</section>
<?endforeach?>
<script>
(function(desk){
	desk.querySelectorAll(".slot").forEach(function(slot){
		slot.ondragend=function(event){
			slot.classList.toggle("grabbing", false);
		}
		slot.ondragover=function(){
			slot.style.padding = "40px 0";
		}
		slot.ondragleave=function(event){
			event.preventDefault();
			slot.removeAttribute("style");
		}
	});
})(document.currentScript.parentNode)

function drag(event){
	event.currentTarget.classList.toggle("grabbing", true);
	event.dataTransfer.effectAllowed = "move";
	event.dataTransfer.setData("text", event.target.id);
	event.currentTarget.ondragover = null;
}
function drop(event){
	event.preventDefault();
	var slot = event.target,
		data = event.dataTransfer.getData("text"),
		card = document.getElementById(data),
		section = event.currentTarget;
	card.ondragover=function(){
		card.style.padding = "40px 0";
	}
	if(slot.classList.contains("slot")){
		if((event.clientY-slot.offsetTop) < (slot.offsetHeight/2)){
			slot.insertAdjacentElement("beforeBegin", card);
		}else slot.insertAdjacentElement("afterEnd", card);
		slot.removeAttribute("style");
	}else section.appendChild(card);

	XHR.push({
		addressee:"/shunter/actions/status/"+card.dataset.id,
		body:JSON.encode({
			status:section.dataset.status,
			performer:card.dataset.performer,
			list:(function(lst){
				section.querySelectorAll("div.slot").forEach(function(slot){
					lst.push(slot.dataset.id);
				});
				return lst;
			})([])
		})
	});
}
</script>