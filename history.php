<?php

require('includes/header.php');
update_activity('history');
force_id();

if ( ! ctype_digit($_GET['p']) || $_GET['p'] < 2) 
{
	$current_page = 1;
	$page_title = 'Your latest post history';
}
else
{
	$current_page = $_GET['p'];
	$page_title = 'Your post history, page #' . number_format($current_page);
}

$items_per_page = ITEMS_PER_PAGE;
$start_listing_at = $items_per_page * ($current_page - 1);  

/* TOPICS */
$res=DB::Execute('SELECT id, time, replies, visits, headline FROM {P}Topics WHERE author = ? ORDER BY id DESC LIMIT ?, ?',array($_SESSION['UID'], $start_listing_at, $items_per_page));

$topics = new TablePrinter('tblTopics');
$columns = array
(
	'Headline',
	'Replies',
	'Visits',
	'Age ▼'
);
$topics->DefineColumns($columns, 'Headline');
$topics->SetTDClass('Headline', 'topic_headline');

while(list($topic_id, $topic_time, $topic_replies, $topic_visits, $topic_headline)=$res->FetchRow()) 
{
	$values = array 
	(
		'<a href="/topic/' . $topic_id . '">' . htmlspecialchars($topic_headline) . '</a>',
		replies($topic_id, $topic_replies),
		format_number($topic_visits),
		'<span class="help" title="' . format_date($topic_time) . '">' . calculate_age($topic_time) . '</span>'
	);
								
	$topics->Row($values);
}
$num_topics_fetched = $topics->num_rows_fetched;
echo $topics->Output('topics');

/* REPLIES */
$res=DB::Execute('SELECT replies.id, replies.parent_id, replies.time, replies.body, topics.headline, topics.time FROM {P}Replies as replies INNER JOIN {P}Topics as topics ON replies.parent_id = topics.id WHERE replies.author = ? ORDER BY id DESC LIMIT ?, ?',array($_SESSION['UID'], $start_listing_at, $items_per_page));

$replies = new TablePrinter('tblReplies');
$columns = array
(
	'Reply snippet',
	'Topic',
	'Age ▼'
);
$replies->DefineColumns($columns, 'Topic');
$replies->SetTDClass('Topic', 'topic_headline');
$replies->SetTDClass('Reply snippet', 'reply_body_snippet');

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
echo $replies->Output('replies');

page_navigation('history', $current_page, $num_replies_fetched);

require('includes/footer.php');

