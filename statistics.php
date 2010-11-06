<?php
/**
* Statistics "Widget" for user dashboard
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
force_id();
update_activity('statistics');
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');
$page_title = 'Statistics';
$uid=DB::Q($_SESSION['UID']);

$num_topics 	= DB::GetOne('SELECT count(*) FROM {P}Topics');
$num_replies 	= DB::GetOne('SELECT count(*) FROM {P}Replies');
$replies_per_topic = round($num_replies / $num_topics);
$num_bans 	= DB::GetOne('SELECT count(*) FROM {P}UIDBans');
$your_topics 	= DB::GetOne("SELECT count(*) FROM {P}Topics WHERE author = {$uid}");
$your_replies 	= DB::GetOne("SELECT count(*) FROM {P}Replies WHERE author = {$uid}");
$your_posts = $your_topics + $your_replies;
$num_ip_bans 	= DB::GetOne('SELECT count(*) FROM {P}IPBans');

$total_posts = $num_topics + $num_replies; 
$days_since_start = floor(( $_SERVER['REQUEST_TIME'] - SITE_FOUNDED ) / 86400);
$posts_per_day = ($days_since_start>0) ? round($total_posts / $days_since_start) : 0;
$topics_per_day = ($days_since_start>0) ? round($num_topics / $days_since_start) : 0;
$replies_per_day = ($days_since_start>0) ? round($num_replies / $days_since_start) : 0;

?>

<table>
	<tr>
		<th></th>
		<th class="minimal">Amount</th>
		<th>Comment</th>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Total existing posts</th>
		<td class="minimal"><?php echo format_number($total_posts) ?></td>
		<td>-</td>
	</tr>
	
	<tr>
		<th class="minimal">Existing topics</th>
		<td class="minimal"><?php echo format_number($num_topics) ?></td>
		<td>-</td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Existing replies</th>
		<td class="minimal"><?php echo format_number($num_replies) ?></td>
		<td>That's about <?php echo $replies_per_topic ?> replies/topic.</td>
	</tr>
	
	<tr>
		<th class="minimal">Posts/day</th>
		<td class="minimal">~<?php echo format_number($posts_per_day) ?></td>
		<td>-</td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Topics/day</th>
		<td class="minimal">~<?php echo format_number($topics_per_day) ?></td>
		<td>-</td>
	</tr>
	
	<tr>
		<th class="minimal">Replies/day</th>
		<td class="minimal">~<?php echo format_number($replies_per_day) ?></td>
		<td>-</td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Temporarily banned IDs</th>
		<td class="minimal"><?php echo format_number($num_bans) ?></td>
		<td>-</td>
	</tr>
	
	<tr>
		<th class="minimal">Banned IP addresses</th>
		<td class="minimal"><?php echo format_number($num_ip_bans) ?></td>
		<td>-</td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Days since launch</th>
		<td class="minimal"><?php echo number_format($days_since_start) ?></td>
		<td>Went live on <?php echo date('Y-m-d', SITE_FOUNDED) . ', ' . calculate_age(SITE_FOUNDED) ?> ago.</td>
	</tr>
</table>

<table>
	<tr>
		<th></th>
		<th>Amount</th>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Total posts by you</th>
		<td><?php echo format_number($your_posts) ?></td>
	</tr>
	
	<tr>
		<th class="minimal">Topics started by you</th>
		<td><?php echo format_number($your_topics) ?></td>
	</tr>
	
	<tr class="odd">
		<th class="minimal">Replies by you</th>
		<td><?php echo format_number($your_replies) ?></td>
	</tr>
</table>

<?php
Output::$tpl->display('dashfooter.tpl.php');

require('includes/footer.php');

?>
