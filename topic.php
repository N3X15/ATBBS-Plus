<?php

require('includes/header.php');

// Validate / fetch topic info.
if( ! ctype_digit($_GET['id']))
{
	add_error('Invalid ID.', true);
}

$topic=new Topic(intval($_GET['id']));
$topic->Parse();
$topic->GetReplies(); 

Output::Assign('topic',$topic);

update_activity('topic', $_GET['id']);

$page_title = 'Topic: ' . htmlspecialchars($topic->Headline);

// Increment visit count.
if( ! isset($_SESSION['visited_topics'][$_GET['id']]) && isset($_COOKIE['SID']))
{
	$_SESSION['visited_topics'][$_GET['id']] = 1;
	
	DB::Execute('UPDATE {P}Topics SET visits = visits + 1 WHERE id = '.$_GET['id']);
}

// Set visited cookie...
$last_read_post = $User->Visited[$_GET['id']];
if($last_read_post !== $topic->Replies)
{
	// Build cookie.
	// Add the current topic:
	$User->Visited = array( $_GET['id'] => $topic_replies) + $User->Visited;
	// Readd old topics.
	foreach($User->Visited as $cur_topic_id => $num_replies)
	{
		// If the cookie is getting too long (4kb), stop.
		if(strlen($cookie_string) > 3900)
		{
			break;
		}
		
		$cookie_string .= 't' . $cur_topic_id . 'n' . $num_replies;
	}

	CreateCookie('topic_visits', $cookie_string);
}
Output::Assign('LastReadPost',$last_read_post);

// Output dummy form. (This is for JavaScript submissions to action.php.)
dummy_form();

echo Output::$tpl->Fetch('topic.tpl.php');

require('includes/footer.php');

?>
