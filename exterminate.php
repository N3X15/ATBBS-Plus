<?php

require('includes/header.php');

if( ! $administrator && ! $moderator || $moderator && !ALLOW_MODS_EXTERMINATE)
{
	add_error('You are not wise enough.', true);
}
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');

$page_title = 'Exterminate Trolls by Phrase';

if($_POST['exterminate'])
{
	$_POST['phrase'] = str_replace("\r", '', $_POST['phrase']);
	
	// Prevent CSRF
	if(empty($_POST['start_time']) || $_POST['start_time'] != $_SESSION['exterminate_start_time'])
	{
		add_error('Session error.', true);
	}

	if(strlen($_POST['phrase']) < 4)
	{
		add_error('That phrase is too short.', true);
	}

	$phrase = '%' . $_POST['phrase'] . '%';

	if(ctype_digit($_POST['range']))
	{
		$affect_posts_after = $_SERVER['REQUEST_TIME'] - $_POST['range'];
	
		// Delete replies.
		$sql=DB::Prepare('SELECT id, parent_id FROM {P}Replies WHERE body LIKE ? AND time > ?');
		$res=DB::Execute($sql,array($phrase, $affect_posts_after));
		
		$victim_parents = array();
		while(list($parent_id)=$res->FetchRow()) // $reply_id?
		{
			$victim_parents[] = $parent_id;
		}
		$fetch_parents->close();
		
		$sql=DB::Prepare('DELETE FROM {P}Replies WHERE body LIKE ? AND time > ?');
		DB::Execute($sql,array($phrase, $affect_posts_after));
		
		$sql=DB::Prepare('UPDATE {P}Topics SET replies = replies - 1 WHERE id = ?');
		foreach($victim_parents as $parent_id)
		{
			DB::Execute($sql,array($parent_id));
		}
		
		// Delete topics.
		$sql=DB::Prepare('DELETE FROM topics WHERE body LIKE ? OR headline LIKE ? AND time > ?');
		DB::Execute($sql,array($phrase, $phrase, $affect_posts_after));
		
		$_SESSION['notice'] = 'Finished.';
	}
}

$start_time = $_SERVER['REQUEST_TIME'];
$_SESSION['exterminate_start_time'] = $start_time;

?>

<p>This features removes all posts that contain anywhere in the body or headline the exact phrase that you specify.</p>

<form action="" method="post">
	<div class="noscreen">
		<input type="hidden" name="start_time" value="<?php echo $start_time ?>" />
	</div>

	<div class="row">
		<label for="phrase">Phrase</label>
		<textarea id="phrase" name="phrase"></textarea>
	</div>
	
	<div class="row">
		<label for="range" class="inline">Affect posts made within:</label>
		<select id="range" name="range" class="inline">
			<option value="28800">Last 8 hours</option>
			<option value="86400">Last 24 hours</option>
			<option value="259200">Last 72 hours</option>
			<option value="604800">Last week</option>
			<option value="2629743">Last month</option>
		</select>
	</div>
	<div class="row">
		<input type="submit" name="exterminate" value="Clean up this fucking mess" onclick="confirm('Really?')" />
	</div>
</form>

<?php

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
