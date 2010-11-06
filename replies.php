<?php

require('includes/header.php');

// Check if we're on a specific page.
if ( ! ctype_digit($_GET['p']) || $_GET['p'] < 2) 
{
	$current_page = 1;
	$page_title = 'Latest replies';
	
	update_activity('latest_replies');
}
else 
{
	$current_page = $_GET['p'];
	$page_title = 'Replies, page #' . number_format($current_page);
	
	update_activity('replies', $current_page);
}

// Print out the appropriate replies.
$items_per_page = ITEMS_PER_PAGE;
$start_listing_replies_at = $items_per_page * ($current_page - 1);  

$sql = 'SELECT {P}Replies.id, {P}Replies.parent_id, {P}Replies.time, {P}Replies.body, {P}Topics.headline, {P}Topics.time FROM {P}Replies INNER JOIN {P}Topics ON {P}Replies.parent_id = {P}Topics.id ORDER BY id DESC LIMIT %d, %d';
$sql=sprintf($sql, $start_listing_replies_at, $items_per_page);
$res=DB::Execute($sql);

$replies = new TablePrinter('tblReplies');
$columns = array
(
	'Snippet',
	'Topic',
	'Age ▼'
);
$replies->DefineColumns($columns, 'Topic');
$replies->SetTDClass('Topic', 'topic_headline');
$replies->SetTDClass('Snippet', 'snippet');
if($res)
while(list($reply_id, $parent_id, $reply_time, $reply_body, $topic_headline, $topic_time)=$res->FetchRow()) 
{
	$values = array 
	(
		'<a href="/topic/' . $parent_id . '#reply_' . $reply_id . '">' . snippet($reply_body) . '</a>',
		'<a href="/topic/' . $parent_id . '">' . htmlspecialchars($topic_headline) . '</a> <span class="help unimportant" title="' . format_date($topic_time) . '">(' . calculate_age($topic_time) . ' old)</span>',
		'<span class="help" title="' . format_date($reply_time) . '">' . calculate_age($reply_time) . '</span>'
	);
								
	$replies->Row($values);
}
$num_replies_fetched = $replies->num_rows_fetched;
echo $replies;

// Navigate backward or forward ...
page_navigation('replies', $current_page, $num_replies_fetched);

require('includes/footer.php');

?>
