<?php

require('includes/header.php');

$page_title = "Hot topics";

$res = DB::Execute('SELECT headline, id, time, replies FROM {P}Topics ORDER BY replies DESC LIMIT 0, 100');

$t=new TablePrinter('hot_topics');
$t->DefineColumns(array('#','Topic','Replies'),'Topic');
$t->SetTDClass('Topic','topic_headline');
$i=0;
while(list($hot_headline, $hot_id, $hot_time, $hot_replies)=$res->FetchRow())
{
	$t->Row(array(
		++$i,
		'<a href="/topic/'.$hot_id.'">'.$hot_headline.'</a>',
		$hot_replies
	));
}
echo $t;
require('includes/footer.php');

?>
