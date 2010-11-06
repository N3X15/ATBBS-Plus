<form action="<?=rel2Abs('/controlpanel.php/topic/')?>" method="post" name="modstuff">
<?
	TopicBar($_GET['id'],$this->topic->AuthorID==$_SESSION['UID']); 
?>
<h3>
	<?=$this->topic->PrintAuthorName()?> started this discussion <strong><span class="help" title="<?=format_date($this->topic->Time)?>"><?=calculate_age($this->topic->Time)?></span> ago</strong>.
	<span class="reply_id unimportant">
		<a href="<?=rel2Abs('/topic/'.$_GET['id'])?>">#<?=number_format($_GET['id'])?></a>
	</span>
	<?ModToolbox($_GET['id'],$this->topic->AuthorID == $_SESSION['UID'],true);?>
</h3>
<div class="body">	
<?if($this->topic->FileName!=null):?>
	<a href="<?=htmlspecialchars(rel2Abs('/img/'.$this->topic->FileName));?>">
		<img src="<?=htmlspecialchars(rel2Abs('/thumbs/'.$this->topic->FileName))?>" alt="" />
	</a>
<?endif;?>

<?=$this->topic->ParsedBody?>

<?edited_message($this->topic->Time, $this->topic->EditTime, ($this->topic->Flags&REPLY_EDITED_BY_MOD)==REPLY_EDITED_BY_MOD);?>

	<ul class="menu">
<?if($this->topic->isEditable()):?>
		<li>
			<a href="<?=rel2Abs('/edit_topic/'.$_GET['id'])?>">Edit</a>
		</li>
<?endif;?>
		<li>
			<a href="<?=rel2Abs('/watch_topic/'.$_GET['id'])?>" onclick="return submitDummyForm('<?=rel2Abs('/watch_topic/'.$_GET['id'])?>', 'id', <?=$_GET['id']?>, 'Really watch this topic?');">
				Watch
			</a>
		</li>
		<li>
			<a href="<?=rel2Abs('/new_reply/'.$_GET['id'].'/quote_topic')?>">
				Quote
			</a>
		</li>
		<li>
			<a href="<?=rel2Abs('/trivia_for_topic/'.$_GET['id'])?>" class="help" title="<?=$this->topic->ReplyCount?> repl<?=($this->topic->ReplyCount == 1 ? 'y' : 'ies')?>">
				<?=$this->topic->Visits?> visit<?=($this->topic->Visits == 1 ? '' : 's')?>
			</a>
		</li>
	</ul>
</div>

<!-- Output replies. -->
<? $prevtime=$this->topic->Time;
$c=0;
foreach($this->topic->Replies as $reply):?>
<h3 id="reply_<?=$reply->ID?>">
		
<?	// If this is the newest unread post, let the #new anchor highlight it.
	if(count($this->topic->Replies) == $this->LastReadPost):?>
		<span id="new"></span><input type="hidden" id="new_id" class="noscreen" value="<?=$reply->ID?>" />
<?endif;?>

	<?$reply->PrintAuthorName()?> <?=$reply->Action?> this <strong><span class="help" title="<?=format_date($reply->Time)?>"><?=calculate_age($reply->Time)?> ago</span></strong>, <?=calculate_age($reply->Time, $prevtime)?> later<? 
	if($c>0)
		echo ', ' . calculate_age($reply->Time, $this->topic->Time) . ' after the original post';
?>
	<span class="reply_id unimportant">
		<a href="#reply_<?=$reply->ID?>" onclick="highlightReply('<?=$reply->ID?>'); removeSnapbackLink">
			#<?=number_format($reply->ID)?>
		</a>
	</span>

<?	ModToolbox($reply->ID,$reply->AuthorID == $_SESSION['UID'],false);?>

</h3>
<div class="body" id="reply_box_<?=$reply->ID?>">
<? if($reply->FileName!=null):?>
	<a href="<?=rel2Abs('/img/'.htmlspecialchars($reply->FileName))?>">
		<img src="<?=rel2Abs('/thumbs/'.htmlspecialchars($reply->FileName))?>" alt="" />
	</a>
<? endif;?>
	
	<?=$reply->ParsedBody?>

<?	edited_message($reply->Time, $reply->EditTime, ($reply->Flags&REPLY_EDITED_BY_MOD)==REPLY_EDITED_BY_MOD); ?>
	
	<ul class="menu">
<?if($reply->isEditable()):?>
		<li>
			<a href="<?=rel2Abs('/edit_reply/'.$_GET['id'])?>/<?=$reply->ID?>">
				Edit
			</a>
		</li>
<?endif;?>
		<li>
			<a href="<?=rel2Abs('/new_reply/'.$_GET['id'].'/quote_reply/'.$reply->ID)?>">
				Quote
			</a>
		</li>
		<li>
			<a href="<?=rel2Abs('/new_reply/'.$_GET['id'].'/cite_reply/'.$reply->ID)?>">
				Cite
			</a>
		</li>
	</ul>
</div>
<?
	// Store information for the next round.
	$c++;
	$prevtime=$reply->Time;
endforeach;
if(isPowerUser()):?>
<h2>UID Dump</h2>
<?
$uidt=new TablePrinter('tblUIDs');
$uidt->DefineColumns(array('UID','Names'),'Names');
foreach($this->topic->UIDs as $uid=>$names)
{
	if($uid!='admin')
		$uid=sprintf('<a href="%1$sprofile/%2$s">%2$s</a>',THISURL,$uid);
	$uidt->Row(array($uid,implode(', ',$names)));
}
echo $uidt;
?>
<?endif;?>

<ul class="menu">
	<li>
		<a href="<?=rel2Abs('/new_reply/'.$_GET['id'])?>">
			New reply
		</a>
	</li>
</ul>

</form>
