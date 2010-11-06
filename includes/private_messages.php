<?php

include('include/header.php');

switch($_POST['act'])
{
	case 'Send': // Reply

		if(!csrf_check()) Output::HardError('Session error. Try again.');
		
		//Lurk more?
		if($_SERVER['REQUEST_TIME'] - $_SESSION['first_seen'] < REQUIRED_LURK_TIME_REPLY)
		{
			add_error('Lurk for at least ' . REQUIRED_LURK_TIME_REPLY . ' seconds before posting your first reply.');
		}
		
		// Flood control.
		$too_early = $_SERVER['REQUEST_TIME'] - FLOOD_CONTROL_REPLY;
		$res=DB::Execute(sprintf('SELECT 1 FROM {P}PMs WHERE pmFrom = \'%s\' AND pmDateSent > %d',$_SERVER['REMOTE_ADDR'], $too_early));

		if($res->RecordCount() > 0)
		{
			add_error('Wait at least ' . FLOOD_CONTROL_REPLY . ' seconds between each reply. ');
		}
		//Check inputs
		list($_POST['title'],$_POST['body'])=Check4Filter($_POST['title'],$_POST['body']);
		$reply=new PM();
		$reply->To	= $_POST['to'];
		$reply->From	=$User->ID;
		$reply->Title	= $_POST['title'];
		$reply->Body	= $_POST['body'];
		$reply->Save();
		break;
}

$view=intval($_GET['id']);
if($view>0)
{
	$pm=new PM();
	$pm->ID=$view;
	$pm->Load();
?>
	<h3>User <?=$pm->From?> sent you <?=$pm->Title?> at <?=format_date($pm->SendDate)?></h3>
	<div class="body">
		<?=parse($pm->Body)?>
	</div>
	<form action="" method="post">
		<h3>Reply:</h3>
		<div class="body">
			<label class="common">Subject:</label><input type="text" name="title" value="<?=htmlentities('RE: '.$pm->Title)?> />
			<label class="common">Message:</label>
			<textarea name="body"></textarea>
			<input type="submit" name="act" value="Send" />
		</div>
	</form>
<?
}
