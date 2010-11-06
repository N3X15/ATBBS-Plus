<?php
if(IN_ATBBS!=1) 
	die('Thou art not wise enough.');

//DB::ToggleDebug();

$badtables=array();

$needupdate=false;
?>
<p>Please wait, checking to see if your database requires updates...</p>
<ul class="statuslist">
<?

if(!DB::TableExists(ADODB_PREFIX.'Migrations'))
{	
	include('sql/Migrations/1.php');
} else {
	echo '<li class="good">Migrations table exists...</li>';
}
foreach(DB::$Tables as $table)
{
	if(DB::CheckForChanges($table))
	{
		$badtables[]=$table;
		$needupdate=true;
	}
}

?>
</ul>

<? if($needupdate): $c=0;?>
<h2>Database Upgrade in Progress</h2>
<ul>
<? foreach($badtables as $table):$c++?>
	<?=DB::ApplyTableFixes($table)?>
<? endforeach;?>
	<li class="good"><?=$c?> tables updated.</li>
</ul>
<?endif;?>
