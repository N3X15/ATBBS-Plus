<?php

require('includes/header.php');
force_id();
update_activity('watchlist');
$page_title = 'Your watchlist';

if( is_array($_POST['rejects']) )
{
	$sql='DELETE FROM watchlists WHERE ';
	$i=0;
	foreach($_POST['rejects'] as $reject_id)
	{
		if($i>0) $sql.=' OR ';
		$sql.='(uid = \''.$User->UID.'\' AND topic_id = '.intval($reject_id).')';
	}
	DB::Execute($sql);
	
	$_SESSION['notice'] = 'Selected topics unwatched.';
}

echo '<form name="fuck_off" action="" method="post">';



$topics = new TablePrinter('watchlist');
$topic_column = '<script type="text/javascript"> document.write(\'<input type="checkbox" name="master_checkbox" class="inline" onclick="checkOrUncheckAllCheckboxes()" title="Check/uncheck all" /> \');</script>Topic';
$columns = array
(
	$topic_column,
	'Replies',
	'Visits',
	'Age',
	'Last Post'
);
$db_columns=array
(
	't.headline',
	't.replies',
	't.visits',
	't.time',
	'last_post'
);
$topics->DefaultSorting('last_post',SORT_DESC,$db_columns);
$topics->DefineColumns($columns, $topic_column);
$topics->SetTDClass($topic_column, 'topic_headline');

DB::ToggleDebug();
$res = DB::Execute('SELECT w.topic_id, t.headline, t.replies, t.visits, t.time, last_post FROM {P}Watchlists as w INNER JOIN {P}Topics as t ON w.topic_id = t.id WHERE w.uid = \''.$User->ID.'\' '.$topics->GetOrderSQL());
DB::ToggleDebug();
while(list($topic_id, $topic_headline, $topic_replies, $topic_visits, $topic_time,$last_post)=$res->FetchRow()) 
{
	$values = array 
	(
		'<input type="checkbox" name="rejects[]" value="' . $topic_id . '" class="inline" /> <a href="/topic/' . $topic_id . '">' . htmlspecialchars($topic_headline) . '</a>',
		replies($topic_id, $topic_replies),
		format_number($topic_visits),
		'<span class="help" title="' . format_date($topic_time) . '">' . calculate_age($topic_time) . '</span>',
		'<span class="help" title="' . format_date($last_post) . '">' . calculate_age($last_post) . '</span>'
	);
								
	$topics->Row($values);
}
$num_topics_fetched = $topics->num_rows_fetched;
echo $topics;

if($num_topics_fetched !== 0)
{
	echo '<div class="row"><input type="submit" value="Unwatch selected" onclick="return confirm(\'Really remove selected topic(s) from your watchlist?\');" class="inline" /></div>';
}
echo '</form>';

require('includes/footer.php');

?>
