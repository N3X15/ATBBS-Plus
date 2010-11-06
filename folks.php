<?php
	
require('includes/header.php');
	
update_activity('folks', 1);

$res=DB::Execute('SELECT a.action_name, a.action_id, a.uid, a.time, t.headline FROM {P}Activity as a LEFT OUTER JOIN {P}Topics as t ON a.action_id = t.id WHERE a.time > '.intval($_SERVER['REQUEST_TIME']).' - 960 ORDER BY time DESC');

$count = $res->RecordCount();

$page_title = 'Folks online (' . $count . ')';

$table = new table();

$columns = array
(
	'Doing',
	'Poster',
	'Last sign of life ▼',
);

$table->define_columns($columns, 'Doing');
$table->add_td_class('Poster', 'minimal');
$table->add_td_class('Last sign of life ▼', 'minimal');

$i = 0;


	
// Array key based off
$actions = array(
	'advertise' 	=> 'Inquiring about advertising.',
	'statistics' 	=> 'Looking at board statistics.',
	'hot_topics' 	=> 'Looking at the hottest topics.',
	'bulletins' 	=> 'Reading latest bulletins.',
	'bulletins_old'	=> 'Reading latest bulletins.',
	'folks' 	=> 'Looking at what other people are doing.',
	'topics' 	=> 'Looking at older topics.',
	'dashboard'	=> 'Modifying their dashboard',
	'latest_replies'
			=> 'Looking at latest replies.',
	'latest_bumps'	=> 'Checking out latest bumps.',
	'latest_topics'	=> 'Checking out latest topics.',
	'search'	=> 'Searching for a topic.',
	'stuff'		=> 'Looking at stuff.',
	'history'	=> 'Looking at post history.',
	'failed_postings' 
			=> 'Looking at post failures.',
	'watchlist'	=> 'Checking out their watchlist.',
	'restore_id'	=> 'Logging in.',
	'new_topic'	=> 'Creating a new topic.',
	'nonexistent_topic'
			=> 'Trying to look at a non-existant topic.',
	'topic' 	=> "Reading in topic: <strong><a href=\"/topic/$action_id\">$headline</a></strong>",
	'replying'	=> "Replying to topic: <strong><a href=\"/topic/$action_id\">$headline</a></strong>",
	'topic_trivia'	=> "Reading <a href=\"/trivia_for_topic/$action_id\">trivia for topic</a>: <strong><a href=\"/topic/$action_id\">$headline</a></strong>",
	'banned'	=> 'Being banned.'
);

while(list($action, $action_id, $uid, $age, $headline)=$res->FetchRow())
{
	// Maximum amount of actions to be shown (100 by default)
	if(++$i == 100)
	{
		break;
	}
	
	if($uid == $_SESSION['UID'])
	{
		$uid = 'You!';
	}
	else
	{
		if(isPowerUser())
		{
			$uid = '<a href="/profile/' . $uid . '">' . $uid . '</a>';
		}
		else
		{
			$uid = '?';
		}
	}
	
	$bump = calculate_age($age, $_SERVER['REQUEST_TIME']);
	$headline = htmlspecialchars($headline);
	
	$action = $actions[$action];
	
	// Unknown or unrecorded actions are bypassed
	if($action == null)
	{
		continue;
	}
	
	// Repeated actions are listed as (See above)
	if($action == $old_action)
	{
		$temp = '<span class="unimportant">(See above)</span>';
	}
	else
	{
		$old_action = $action;
		$temp = $action;
	}
	
	$values = array ($action,
			$uid,
			'<span class="help" title="' . format_date($age) . '">' . calculate_age($age) . '</span>');
	$table->row($values);
}
echo $table->output();
if($count > 100)
{
	echo '<p class="unimportant">(There are <b>a lot</b> of people active right now. Not all are shown here.)</p>';
}
			
require ('includes/footer.php');

?>
