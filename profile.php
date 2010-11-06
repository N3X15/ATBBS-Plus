<?php
/**
* UID Tracking
* 
* Copyright (c) 2009-2010 ATBBS Contributors
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

require('includes/header.php');

// If you're not a mod, fuck off.
if( ! $moderator && ! $administrator)
{
	add_error('You are not wise enough.', true);
}

// Demand UID.
if( ! isset($_GET['uid']))
{
	add_error('No UID specified.', true);
}
$uid=DB::Q($_GET['uid']);
// Demand a _valid_ UID, fetch first_seen, IP address, and hostname.
$res=DB::Execute('SELECT first_seen, ip_address FROM {P}Users WHERE uid = '.$uid);

if($res->RecordCount() < 1)
{
	add_error('There is no such user.', true);
}
list($id_first_seen, $id_ip_address)=$res->FetchRow();

$id_hostname = @gethostbyaddr($id_ip_address);
if($id_hostname === $id_ip_address)
{
	$id_hostname = false;
}


$ban=GetBanFromUID($_GET['uid']);
if($ban!=array())
{
	$banned = true;
}

// Fetch number of topics and replies.
$id_num_topics = DB::GetOne("SELECT count(*) FROM {P}Topics WHERE author = {$uid}");
$id_num_replies= DB::GetOne("SELECT count(*) FROM {P}Replies WHERE author = {$uid}");


// Now print everything.
$page_title = 'Profile of poster ' . $_GET['uid'];
dummy_form();

$usr=new User($_GET['UID']);
$usr->Load();

echo '<p><b>'.($usr->Level).'</b>. First seen <strong class="help" title="' . format_date($id_first_seen) . '">' . calculate_age($id_first_seen) . ' ago</strong> using the IP address <strong><a href="/IP_address/' . $id_ip_address . '">' . $id_ip_address . '</a></strong> (';
//If there's a valid host name ...
if($id_hostname)
{
	echo 'host name <strong>' . $id_hostname . '</strong>';
}
else
{
	echo 'no valid host name';
}
echo '), has started <strong>' . $id_num_topics . '</strong> existing topic' . ($id_num_topics == 1 ? '' : 's') . ' and posted <strong>' . $id_num_replies . '</strong> existing repl' . ($id_num_replies == 1 ? 'y' : 'ies') . '.</p>';
?>
<?if($banned):?>
	<p>This poster is currently <strong>banned</strong> until <?=format_date($ban['expiry'])?> for the following reason:<br /><blockquote><?=$ban['reason']?></blockquote>
<?endif;?>
<ul class="menu">
<?if(!$banned):?>
	<li>
		<a href="/ban_poster/<?=$_GET['uid']?>" onclick="return submitDummyForm(\'/ban_poster/<?=$_GET['uid']?>\', \'id\', \'<?=$_GET['uid']?>\', \'Really ban this poster?\');">Ban ID</a>
	</li>
	<li>
		<a href="/private_messages.php/compose/<?=$_GET['uid']?>">Send PM</a>
	</li>
<?else:?>
	<li>
		<a href="/unban_poster/<?=$_GET['uid']?>" onclick="return submitDummyForm(\'/unban_poster/<?=$_GET['uid']?>\', \'id\', \'<?=$_GET['uid']?>\', \'Really unban this poster?\');">Unban ID</a>
	</li>';
<?endif;?>
	<li>
		<a href="/nuke_ID/<?=$_GET['uid']?>" onclick="return submitDummyForm(\'/nuke_ID/<?=$_GET['uid']?>\', \'id\', \'<?=$_GET['uid']?>\', \'Really delete all topics and replies by this poster?\');">Delete all posts</a>
	</li>
</ul>
<?if(!$banned):?>
<form action="/controlpanel.php/powerusers/" method="post">
	<?=csrf_token()?>
	<input type="hidden" name="form_sent" value="1" />
	<table>
		<thead>
			<tr><th colspan="2">Permission</th><tr>
		</thead>
		<tbody>
			<tr>
				<td class="minimal"><input type="checkbox" name="add_sysop" value="<?=$_GET['uid']?>" class="inline" /></td>
				<td><label for="add_sysop"><?=ADMIN_NAME?></label></td>
			</tr>
			<tr>
				<td class="minimal"><input type="checkbox" name="add_mod" value="<?=$_GET['uid']?>" class="inline" /></td>
				<td><label for="add_sysop"><?=MOD_NAME?></label></td>
			</tr>
		</tbody>
	</table>
	<input type="submit" value="Go" />
</form>
<?endif;?>
<?
if($id_num_topics > 0)
{
	echo '<h4 class="section">Topics</h4>';

	$sql=DB::Prepare('SELECT id, time, replies, visits, headline, author_ip FROM {P}Topics WHERE author = ? ORDER BY id DESC');
	$res=DB::Execute($sql,array($_GET['uid']));

	$topics = new TablePrinter('tblTopics');
	$columns = array
	(
		'Headline',
		'IP address',
		'Replies',
		'Visits',
		'Age ▼'
	);
	$topics->DefineColumns($columns, 'Headline');
	$topics->SetTDClass('Headline', 'topic_headline');
	
	while($row=$res->FetchRow()) 
	{
		list($topic_id, $topic_time, $topic_replies, $topic_visits, $topic_headline, $topic_ip_address)=$row;
		$values = array 
		(
			'<a href="/topic/' . $topic_id . '">' . htmlspecialchars($topic_headline) . '</a>',
			'<a href="/IP_address/' . $topic_ip_address . '">' . $topic_ip_address . '</a>',
			replies($topic_id, $topic_replies),
			format_number($topic_visits),
			'<span class="help" title="' . format_date($topic_time) . '">' . calculate_age($topic_time) . '</span>'
		);
								
		$topics->Row($values);
	}
	echo $topics;
}

if($id_num_replies > 0)
{
	echo '<h4 class="section">Replies</h4>';

	$sql=DB::Prepare('SELECT replies.id, replies.parent_id, replies.time, replies.body, replies.author_ip, topics.headline, topics.time FROM {P}Replies as replies INNER JOIN {P}Topics as topics ON replies.parent_id = topics.id WHERE replies.author = ? ORDER BY id DESC');
	$res=DB::Execute($sql,array($_GET['uid']));

	$stmt->bind_result;
	
	$replies = new TablePrinter('tblReplies');
	$columns = array
	(
		'Reply snippet',
		'Topic',
		'IP address',
		'Age ▼'
	);
	$replies->DefineColumns($columns, 'Topic');
	$replies->SetTDClass('Topic', 'topic_headline');
	$replies->SetTDClass('Reply snippet', 'reply_body_snippet');

	while($row=$res->FetchRow()) 
	{
		list($reply_id, $parent_id, $reply_time, $reply_body, $reply_ip_address, $topic_headline, $topic_time)=$row;
		$values = array 
		(
			'<a href="/topic/' . $parent_id . '#reply_' . $reply_id . '">' . snippet($reply_body) . '</a>',
			'<a href="/topic/' . $parent_id . '">' . htmlspecialchars($topic_headline) . '</a> <span class="help unimportant" title="' . format_date($topic_time) . '">(' . calculate_age($topic_time) . ' old)</span>',
			'<a href="/IP_address/' . $reply_ip_address . '">' . $reply_ip_address . '</a>',
			'<span class="help" title="' . format_date($reply_time) . '">' . calculate_age($reply_time) . '</span>'
		);
									
		$replies->Row($values);
	}
	echo $replies;
}

if($trash = show_trash($_GET['uid']))
{
	echo '<h4 class="section">Trash</h4>' . $trash;
}

require('includes/footer.php');

?>
