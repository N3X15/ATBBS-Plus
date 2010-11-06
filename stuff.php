<?php

require('includes/header.php');
update_activity('stuff');
$page_title = 'Stuff';
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');
?>
	<h1>Your Dashboard</h1>
	<p>
		Welcome to the ATBBS Dashboard.  Here, you will find an assload of
		tools to help you manage your account, personalize the board, and,
		if you have sufficient permissions, administrate the board.
	</p>
	<h2>You In A Nutshell</h2>
<?php
$mytopics = DB::GetOne("SELECT COUNT(*) FROM {P}Topics WHERE author='{$User->ID}'");
$myreplies = DB::GetOne("SELECT COUNT(*) FROM {P}Replies WHERE author='{$User->ID}'");
$topics = DB::GetOne("SELECT COUNT(*) FROM {P}Topics");
$replies = DB::GetOne("SELECT COUNT(*) FROM {P}Replies");
?>
	<p>You are <span style="font-weight:bold;" title="Your ID"><code><?=$User->ID?></code></span>, <?=an($User->Level)?> <b><?=strtolower($User->Level)?></b> who has been around since <b><?=format_date($_SESSION['first_seen'])?></b>.</p>
	<p>You have posted <?=$mytopics+$myreplies?> times (including topics), which means you have contributed to <b><?=sprintf('%0.2f',(($mytopics+$myreplies)/($topics+$replies))*100.0)?>%</b> (<?=$mytopics+$myreplies?>/<?=$topics+$replies?>) of <?=SITE_TITLE?>'s post count.</p>
<?if(FALSE):?>
	<h2>Debugging Shit ($_SESSION)</h2>
<?
$table=new TablePrinter('tblSession');
$table->DefineColumns(array('Variable','Value'),'Value');

foreach($_SESSION as $k=>$v) $table->Row(array($k,$v));

echo $table;
?>
	<h2>Debugging Shit ($User)</h2>
<?
$table=new TablePrinter('tblUser');
$table->DefineColumns(array('Variable','Value'),'Value');

foreach($User as $k=>$v)
{
	if(is_array($v))
		$v='<pre>'.htmlentities(var_export($v,true)).'</pre>';
	$table->Row(array($k,$v));
}

echo $table;
endif;
Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
