<?php

require('includes/header.php');
update_activity('failed_postings');
$page_title = 'Failed postings';
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');

$items_per_page = ITEMS_PER_PAGE;
$res=DB::Execute('SELECT time, uid, reason, headline, body FROM {P}FailedPostings ORDER BY time DESC LIMIT '.$items_per_page);

$table = new TablePrinter('tblFail');

$columns = array
(
	'Error message',
	'Poster',
	'Age ▼'
);
if(isPowerUser())
{
	array_splice($columns, 1, 1);
}

$table->DefineColumns($columns, 'Error message');

while(list($fail_time, $fail_uid, $fail_reason, $fail_headline, $fail_body)=$res->FetchRow()) 
{
	if(strlen($fail_body) > 600)
	{
		$fail_body = substr($fail_body, 0, 600) . ' …';
	}

	$tooltip = '';
	if(empty($fail_headline))
	{
		$tooltip = $fail_body;
	}
	else if( ! empty($fail_body))
	{
		$tooltip = 'Headline: ' . $fail_headline . ' Body: ' . $fail_body;
	}

	$fail_reasons = unserialize($fail_reason);
	$error_message = '<ul class="error_message';
	if( ! empty($tooltip))
	{
		$error_message .= ' help';
	}
	$error_message .= '" title="' . htmlspecialchars($tooltip) . '">';
	foreach($fail_reasons as $reason)
	{
		$error_message .= '<li>' . $reason . '</li>';
	}
	$error_message .= '</ul>';
	
	$values = array 
	(
		$error_message,
		'<a href="/profile/' . $fail_uid . '">' . $fail_uid . '</a>',
		'<span class="help" title="' . format_date($fail_time) . '">' . calculate_age($fail_time) . '</span>'
	);
	if( ! $moderator && ! $administrator)
	{
		array_splice($values, 1, 1);
	}
								
	$table->Row($values);
}
echo $table->Output('failed postings');

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
