<?

if(in_array(USER_GROUP, $privileged)){

	$hidden = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden performers'], -1, PREG_SPLIT_NO_EMPTY);
	$hidden[] = 0;

	$community = $mySQL->get("SELECT CommunityID,Name FROM gb_staff CROSS JOIN gb_community USING(CommunityID) WHERE CommunityID NOT IN ({arr})", $hidden);

}else $community = [[
	"CommunityID"=>COMMUNITY_ID,
	"Name"=>USER_NAME
]];

$types = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['showed types'], -1, PREG_SPLIT_NO_EMPTY);
$statuses = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden statuses'], -1, PREG_SPLIT_NO_EMPTY);
$statuses[] = 'hidden';
?>
<section class="task-feed">
	<div class="h-bar dark-btn-bg">STREAM ❯</div>
	<aside <?if(PRIVILEGED):?>class="privileged"<?endif?> data-performer="NULL" ondragover="event.preventDefault()" ondrop="drop(event)">
	<?$feed = $mySQL->get("
	SELECT * FROM gb_task_timing 
	CROSS JOIN gb_task_shunter USING(TaskID)
	WHERE CommunityID IS NULL AND type IN ({arr}) AND status NOT IN ({arr})
	ORDER BY type, rank DESC", $types, $statuses);

		foreach($feed as $task):?>
		<div id="task-<?=$task['TaskID']?>" class="slot" data-id="<?=$task['TaskID']?>" draggable="true" ondragstart="drag(event)">
			<div class="card snippet <?=$task['type']?>">
				<div class="preview"><img src="<?=$task['image']?>" alt="&#xe94a;"></div>
				<div class="header"><span><?=$task['header']?></span></div>
				<div class="task"><?=$task['task']?></div>
				<div class="options">
					<span class="created red-txt"><?=date("d M, H:i", $task['created'])?></span>
					<span class="status <?=$task['status']?>"><?=$task['status']?></span>
				</div>
				<span class="rank <?=$task['type']?>"><?=$task['rank']?></span>
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
<?foreach($community as $user):?>
<section class="task-feed">
	<div class="h-bar logo-bg"><?=$user['Name']?> ❯</div>
	<aside <?if(PRIVILEGED):?>class="privileged"<?endif?> data-performer="<?=$user['CommunityID']?>" ondragover="event.preventDefault()" ondrop="drop(event)">
	<?$feed = $mySQL->get("
	SELECT * FROM gb_task_timing 
	CROSS JOIN gb_task_shunter USING(TaskID)
	WHERE CommunityID = {int} AND type IN ({arr}) AND status NOT IN ({arr})
	ORDER BY SortID", $user['CommunityID'], $types, $statuses);

	foreach($feed as $task):?>
		<div id="task-<?=$task['TaskID']?>" class="slot" data-id="<?=$task['TaskID']?>" draggable="true" ondragstart="drag(event)">
			<div class="card snippet <?=$task['type']?>" data-id="<?=$task['TaskID']?>">
				<div class="preview"><img src="<?=$task['image']?>" alt="&#xe94a;"></div>
				<div class="header"><span><?=$task['header']?></span></div>
				<div class="task"><?=$task['task']?></div>
				<div class="options">
					<span class="created red-txt"><?=date("d M, H:i", $task['created'])?></span>
					<span class="status <?=$task['status']?>"><?=$task['status']?></span>
				</div>
				<span class="rank <?=$task['type']?>"><?=$task['rank']?></span>
				<?if($task['link']):?><a href="<?=$task['link']?>" target="_blank" title="follow">❯</a><?endif?>
				<label data-id="<?=$task['TaskID']?>" class="drop-task">✕</label>
			</div>
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
	});
})(document.currentScript.parentNode)

function drag(event){
	event.currentTarget.classList.toggle("grabbing", true);
	event.dataTransfer.effectAllowed = "move";
	event.dataTransfer.setData("text", event.target.id);
}
function drop(event){
	event.preventDefault();
	var	data = event.dataTransfer.getData("text"),
		card = document.getElementById(data),
		section = event.currentTarget;

	section.insertToBegin(card);
	XHR.push({addressee:"/shunter/actions/change-performer/"+card.dataset.id+"/"+section.dataset.performer});
}
</script>