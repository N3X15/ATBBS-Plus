<?php

require('includes/header.php');

if( ! ctype_digit($_GET['id']))
{
	add_error('Invalid ID.', true);
}

$stmt = DB::Prepare('SELECT headline, visits, replies, author FROM {P}Topics WHERE id = ?');
$stmt=DB::Execute($stmt,array($_GET['id']));

if($stmt->RecordCount() < 1)
{
	$page_title = 'Non-existent topic';
	add_error('There is no such topic. It may have been deleted.', true);
}

list($topic_headline, $topic_visits, $topic_replies, $topic_author)=$stmt->FetchRow();

update_activity('topic_trivia', $_GET['id']);

$page_title = 'Trivia for topic: <a href="/topic/' . $_GET['id'] . '">' . htmlspecialchars($topic_headline) . '</a>';

$statistics = array();

$topic_watchers=DB::GetOne("SELECT count(*) FROM {P}Watchlists WHERE topic_id = " . DB::Q($_GET['id']));
$topic_readers =DB::GetOne("SELECT count(*) FROM {P}Activity WHERE action_name = 'topic' AND action_id = " . DB::Q($_GET['id']));
$topic_writers =DB::GetOne("SELECT count(*) FROM {P}Activity WHERE action_name = 'replying' AND action_id = " . DB::Q($_GET['id']));
$topic_participants = DB::GetOne("SELECT count(DISTINCT author) FROM {P}Replies WHERE parent_id = " . DB::Q($_GET['id']) . " AND author != ".DB::Q($topic_author)); // Alternatively, we could select the most recent poster_number. I'm not sure which method would be fastest.

?>

<table>
	<tr>
		<th class="minimal">Total visits</th>
		<td><?php echo format_number($topic_visits) ?></td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Watchers</th>
		<td><?php echo format_number($topic_watchers) ?></td>
	</tr>
	
	<tr>
		<th class="minimal">Participants</th>
		<td><?php echo ($topic_participants === 1) ? '(Just the creator.)' : format_number($topic_participants) ?></td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Replies</th>
		<td><?php echo format_number($topic_replies) ?></td>
	</tr>
	
	<tr>
		<th class="minimal">Current readers</th>
		<td><?php echo format_number($topic_readers) ?></td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Current reply writers</th>
		<td><?php echo format_number($topic_writers) ?></td>
	</tr>
	
</table>

<?php

require('includes/footer.php');

?>
