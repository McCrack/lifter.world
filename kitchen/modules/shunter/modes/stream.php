<?

$types = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['showed types'], -1, PREG_SPLIT_NO_EMPTY);

define("STATUS", defined("ARG_2") ? ARG_2 : "new");

$type = "";

$stream = $mySQL->get("
	SELECT * FROM gb_task_timing 
	CROSS JOIN gb_task_shunter USING(TaskID)
	LEFT JOIN gb_community USING(CommunityID)
	WHERE CommunityID IS NULL AND type IN ({arr}) AND status LIKE {str}
	ORDER BY type, rank DESC", $types, STATUS
)?>
<div class="task-stream">
	<div class="h-bar light-txt">
		<?foreach(["new","in work","done","waste"] as $status):?>
		<a class="tool<?if($status==STATUS):?> selected<?endif?>" href="/shunter/stream/<?=$status?>"><?=$status?></a>
		<?endforeach?>
	</div>
	<section <?if(PRIVILEGED):?>class="privileged"<?endif?>>
		<?foreach($stream as $task):?>
		<div data-id="<?=STATUS?>/<?=$task['TaskID']?>" class="slot">
			<div class="card snippet <?=$task['type']?>">
				<div class="preview"><img src="<?=$task['image']?>" alt="&#xe94a;"></div>
				<div class="header"><span><?=$task['header']?></span></div>
				<div class="task"><?=$task['task']?></div>
				<div class="options">
					<span class="created red-txt"><?=date("d M, H:i", $task['created'])?></span>
					<span class="performer"><?=$task['Name']?></span>
					<span class="status"><?=$task['status']?></span>
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
	</section>
</div>