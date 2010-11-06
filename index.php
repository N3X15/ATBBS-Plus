<?php

require('includes/header.php');

// Should we sort by topic creation date or last bump?
if($_COOKIE['topics_mode'] == 1 && ! $_GET['bumps'])
{
	$topics_mode = true;
}

// Are we on the first page?
if ($_GET['p'] < 2 || ! ctype_digit($_GET['p'])) 
{
	$current_page = 1;
	
	if($topics_mode)
	{
		update_activity('latest_topics');
		$page_title = 'Latest Topics';
		$last_seen = $_COOKIE['last_topic'];
	}
	else
	{
		update_activity('latest_bumps');
		$page_title = 'Latest bumps';
		$last_seen = $_COOKIE['last_bump'];
	}
}
// The page number is greater than one.
else 
{
	$current_page = $_GET['p'];
	update_activity('topics', $current_page);
	$page_title = 'Topics, page #' . number_format($current_page);
}

// Update the last_bump and last_topic cookies. These control
// both the last seen marker and the exclamation mark in main menu.
if($_COOKIE['last_bump'] <= $last_actions['last_bump']) 
{
	setcookie('last_bump', $_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME'] + 315569260, '/');
}
if($_COOKIE['last_topic'] <= $last_actions['last_topic'])
{
	setcookie('last_topic', $_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME'] + 315569260, '/');
}

// If ostrich mode is enabled, fetch a list of blacklisted phrases.
$ignored_phrases = fetch_ignore_list();

// Fetch the {P}Topics appropriate to this page.
$items_per_page = ITEMS_PER_PAGE;
$start_listing_at = $items_per_page * ($current_page - 1);
if($topics_mode)
{
	$sql = "SELECT id, time, replies, visits, headline, body, last_post FROM {P}Topics ORDER BY id DESC LIMIT $start_listing_at, $items_per_page";
}
else
{
	$sql = "SELECT id, time, replies, visits, headline, body, last_post FROM {P}Topics ORDER BY last_post DESC LIMIT $start_listing_at, $items_per_page";
}
$res=DB::Execute($sql);
//$stmt->bind_result($topic_id, $topic_time, $topic_replies, $topic_visits, $topic_headline, $topic_body, $topic_last_post);

// Print the {P}Topics we just fetched in a table.
$table = new TablePrinter('tblBumps');

$order_name = ($topics_mode) ? 'Age' : 'Last bump';
$columns = array
(
	'Headline',
	'Snippet',
	'Replies',
	'Visits',
	$order_name . ' ▼'
);
			
if($_COOKIE['spoiler_mode'] != 1)
{
	// If spoiler mode is disabled, remove the snippet column.	
	array_splice($columns, 1, 1);
}

$table->DefineColumns($columns, 'Headline');
$table->SetTDClass('Headline', 'topic_headline');
$table->SetTDClass('Snippet', 'snippet');

if($res)
while(list($topic_id, $topic_time, $topic_replies, $topic_visits, $topic_headline, $topic_body, $topic_last_post)=$res->FetchRow()) 
{
	// Should we even bother?
	if($_COOKIE['ostrich_mode'] == 1)
	{
		foreach($ignored_phrases as $ignored_phrase)
		{
			if(stripos($topic_headline, $ignored_phrase) !== false || stripos($topic_body, $ignored_phrase) !== false)
			{
				// We've encountered an ignored phrase, so skip the rest of this while() iteration.
				$table->num_rows_fetched++;
				continue 2;
			}
		}
	}
	
	// Decide what to use for the last seen marker and the age/last bump column.
	if($topics_mode)
	{
		$order_time = $topic_time;
	}
	else
	{
		$order_time = $topic_last_post;
	}
	
	// Process the values for this row of our table. 
	$values = array (
						'<a href="/topic/' . $topic_id . '">' . htmlspecialchars($topic_headline) . '</a>',
						snippet($topic_body),
						replies($topic_id, $topic_replies),
						format_number($topic_visits),
						'<span class="help" title="' . format_date($order_time) . '">' . calculate_age($order_time) . '</span>'
					);
	if($_COOKIE['spoiler_mode'] != 1)
	{	
		array_splice($values, 1, 1);
	}
	
	$table->LastSeenMarker($last_seen, $order_time);
	$table->Row($values);
}

$num_rows_fetched = $table->num_rows_fetched;
echo $table->Output('topics');

// Navigate backward or forward ...
$navigation_path = 'topics';
if($_GET['bumps'])
{
	$navigation_path = 'bumps';
}
page_navigation($navigation_path, $current_page, $num_rows_fetched);

require('includes/footer.php');

?>
