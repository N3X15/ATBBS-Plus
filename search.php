<?php

require('includes/header.php');
update_activity('search');
$page_title = 'Search';
$onload_javascript = 'focusId(\'phrase\'); init();';

if( ! empty($_POST['phrase'])) 
{
	if($_POST['deep_search'])
	{
		$redirect_to = DOMAIN . 'deep_search/' . urlencode($_POST['phrase']);
	}
	else
	{
		$redirect_to = DOMAIN . 'quick_search/' . urlencode($_POST['phrase']);
	}
	
	header('Location: ' . $redirect_to);
	exit;
}

?>

<p>The "quick" option searches only topic headlines, while the "deep" option searches both headlines and bodies.</p>

<form action="" method="post">
	<div class="row">
		<input id="phrase" name="phrase" type="text" size="80" maxlength="255" value="<?php echo htmlspecialchars($_GET['q']) ?>" class="inline" />
		<input type="submit" value="Quick" class="inline" />
		<input type="submit" value="Deep" name="deep_search" class="inline" />
	</div>
</form>

<?php

if( ! empty($_GET['q']))
{
	$search_query = addcslashes( trim($_GET['q']), '%_' );
	$common_words = array('the', 'and', 'are', 'that', 'for', 'with', 'lol', 'what', 'where', 'when', 'why');
	
	if(strlen($search_query) < 3)
	{
		add_error('Your query must be at least 3 characters.');
	}
	else if(in_array($search_query, $common_words))
	{
		add_error('Your search query is too common a word.');
	}
	if($_SERVER['REQUEST_TIME'] - $_SESSION['last_search'] < 5)
	{
		add_error('Wait at least 5 seconds between searches.');
	}
	
	if( ! $erred)
	{
		$_SESSION['last_search'] = $_SERVER['REQUEST_TIME'];
	
		$search_query = '%' . $search_query . '%';
		$res=null;
		if($_GET['deep_search'])
		{
			$sql=DB::Prepare('SELECT id, time, replies, visits, headline FROM {P}Topics WHERE headline LIKE ? OR body LIKE ? ORDER BY id DESC LIMIT 50');
			$res=DB::Execute($sql,array($search_query, $search_query));
		}
		else
		{
			$sql=DB::Prepare('SELECT id, time, replies, visits, headline FROM {P}Topics WHERE headline LIKE ? ORDER BY id DESC LIMIT 50');
			$res=DB::Execute($sql,array($search_query));
		}
		
		if($res->RecordCount() > 0)
		{
			echo '<h4 class="section">Topics</h3>';
			
			$search_topics->bind_result;
			
			$topics = new table();
			$columns = array(
				'Headline',
				'Replies',
				'Visits',
				'Age ▼'
			);
			$topics->define_columns($columns, 'Headline');
			$topics->add_td_class('Headline', 'topic_headline');
	
			while($row=$res->FetchRow()) 
			{
				list($topic_id, $topic_time, $topic_replies, $topic_visits, $topic_headline)=$row;
				$values = array 
				(
					'<a href="/topic/' . $topic_id . '">' . str_ireplace( $_GET['q'], '<em class="marked">' . htmlspecialchars($_GET['q']) . '</em>', htmlspecialchars($topic_headline) ) . '</a>',
					replies($topic_id, $topic_replies),
					format_number($topic_visits),
					'<span class="help" title="' . format_date($topic_time) . '">' . calculate_age($topic_time) . '</span>'
				);
										
				$topics->row($values);
			}
			$num_topics_fetched = $topics->num_rows_fetched;
			echo $topics->output('', true);
			
			if($num_topics_fetched == 50)
			{
				echo '<p class="unimportant">(Tons of results found; stopping here.)</p>';
			}
		}
		else
		{
			echo '<p>(No matching topic headlines';
			if($_GET['deep_search'])
			{
				echo ' or bodies';
			}
			echo '.)</p>';
		}
		
		if($_GET['deep_search'])
		{
			$sql=DB::Prepare('SELECT replies.id, replies.parent_id, replies.time, replies.body, topics.headline, topics.time FROM {P}Replies as replies INNER JOIN {P}Topics as topics ON replies.parent_id = topics.id WHERE replies.body LIKE ? ORDER BY id DESC LIMIT 50');
			$res=DB::Execute($sql,array($search_query));
			
			if($res->RecordCount() > 0)
			{
				$search_replies->bind_result;
				
				$replies = new table();
				$columns = array
				(
					'Reply snippet',
					'Topic',
					'Age ▼'
				);
				$replies->define_columns($columns, 'Topic');
				$replies->add_td_class('Topic', 'topic_headline');
				$replies->add_td_class('Reply snippet', 'reply_body_snippet');
				
				while($row=$res->FetchRow()) 
				{
					list($reply_id, $parent_id, $reply_time, $reply_body, $topic_headline, $topic_time)=$row;
					$values = array 
					(
						'<a href="/topic/' . $parent_id . '#reply_' . $reply_id . '">' . str_ireplace( $_GET['q'], '<em class="marked">' . htmlspecialchars($_GET['q']) .  '</em>', snippet($reply_body) ) .'</a>',
						'<a href="/topic/' . $parent_id . '">' . htmlspecialchars($topic_headline) . '</a> <span class="help unimportant" title="' . format_date($topic_time) . '">(' . calculate_age($topic_time) . ' old)</span>',
						'<span class="help" title="' . format_date($reply_time) . '">' . calculate_age($reply_time) . '</span>'
					);
												
					$replies->row($values);
				}
				$num_replies_fetched = $replies->num_rows_fetched;
				echo $replies->output('', true);
				
				
				if($num_replies_fetched == 50)
				{
					echo '<p class="unimportant">(Tons of results found; stopping here.)</p>';
				}
			}
			else
			{
				echo '<p>(No matching replies.)</p>';
			}
		}
	}
}

print_errors();

require('includes/footer.php');

?>
