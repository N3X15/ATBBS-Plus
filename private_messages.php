<?php

include('includes/header.php');

switch($_POST['act'])
{
	case 'Send': // Reply

		if(!check_token()) Output::HardError('Session error. Try again.');
		
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
		list($_POST['title'],$_POST['body'])=Check4Filtered($_POST['title'],$_POST['body']);
		$reply=new PM();
		$reply->To	= $_POST['to'];
		$reply->Thread	= intval($_POST['thread']);
		$reply->From	=$User->ID;
		$reply->Title	= $_POST['title'];
		$reply->Body	= $_POST['body'];
		$reply->Save();
		$_SESSION['notice']='PM sent.';
		break;
}

switch(Path::FetchIndex(0))
{
	default:
	case 'list':
		$page_title='List Private Messages';
		$_SESSION['LastReadPMs']=time();
		$res=DB::Execute("SELECT pmID,pmThread,pmTitle,pmDateSent,pmFrom,pmFlags FROM {P}PMs WHERE pmTo='{$User->ID}' AND (pmFlags&1)=0 ORDER BY pmDateSent DESC");
		$pms=array();
		while(list($id,$thread,$title,$date,$from,$flags)=$res->FetchRow())
		{
			if(!array_key_exists($thread,$pms))
				$pms[$thread]=array($date,$title);

			if($date>$pms[$thread][0])
				$pms[$thread][0]=$date;
			if($thread==0)
			{
				$pms[$thread][1]=$title;
				$pms[$thread][2]=$from;
			}
		}
		$pt=new TablePrinter('tblPMs');
		$pt->DefineColumns(array('Subject','From','Sent'),'Subject');
		foreach($pms as $id=>$details)
		{
			//var_dump($pms);
			$pt->Row(array(
				'<a href="'.THISURL.'/private_messages.php/thread/'.$id.'/">'.htmlspecialchars($details[1]).'</a>',
				'<a href="'.THISURL.'/private_messages.php/compose/'.$details[2].'/">'.$details[2].'</a>',
				calculate_age($details[0]).' ago'
			));
		}
		echo $pt->Output();
		?>
		<ul class="menu"><li><a href="<?=THISURL?>/private_messages.php/compose/">Compose New Message</a></li></ul>
<?
		break;
	case 'compose':
		$page_title="Compose a PM.";
		?>
		<form action="" method="post">
			<h3>Send a Private Message</h3>
			<div class="body">
				<label for="to" class="common">To:<label><input type="text" name="to" value="<?=htmlentities(Path::FetchIndex(1))?>" />
				<label for="title" class="common">Title:<label><input type="text" name="title" value="" />
				<?=csrf_token()?>
				<label for="body">Body:<label>
				<textarea name="body"></textarea>
				<input type="submit" value="Send" name="act" />
			</div>
		</form>
<?
		break;
	case 'thread':
		$view=intval(Path::FetchIndex(1));
		
		$res=DB::Execute("SELECT pmID,pmThread,pmTitle,pmDateSent,pmFrom,pmTo,pmFlags,pmBody FROM {P}PMs WHERE (pmFlags&1)=0 AND (pmID=$view OR pmThread={$view}) ORDER BY pmDateSent ASC");
		$page_title='';
		$participants=array();
		//$thread.="NIGGERS ".$res->RecordCount();
		//error_reporting(E_ALL);
		if($res->RecordCount()==0)
			Output::HardError("Thread #{$view} not found.");
		while(list($id,$thread,$title,$date,$from,$to,$flags,$body)=$res->FetchRow())
		{
			$action="posted";
			if(!in_array($from,$participants))
			{
				$participants[]=$from;
				$action=($thread==0)?'began the conversation':'joined in';
				if($thread==0)
				{
					$page_title=htmlentities($title);
					$OP=$from;
				}
			}
			if(!in_array($to,$participants))
				$participants[]=$to;

			if($from==$User->ID) $from='<a href="#">You</a>';
			else $from="User $from";
			
			if($to==$User->ID) $to='<a href="#">yourself</a>';

			$title=htmlentities($title);
			$date=calculate_age($date);
			$url=THISURL.'/private_messages.php/thread/'.(($thread==0)?$id:$thread).'/#pm'.$id;
			$body=parse($body);
			echo "
			<h3>$from sent $to &quot;$title&quot; $date ago.<span class=\"reply_id\"><a name=\"pm{$id}\" href=\"{$url}\">#{$id}</a></span></h3>
			<div class=\"body\">
				{$body}
			</div>
";
		}

		if(!in_array($User->ID,$participants)) Output::HardError('You\'re not invited.');
?>
		<form action="" method="post">
			<h3><b>Reply to Private Thread:</b></h3>
			<div class="body">
				<input type="hidden" name="thread" value="<?=$view?>" />
				<label for="to" class="common">To:<label><input type="text" name="to" value="<?=htmlentities($OP)?>" />
				<label for="title" class="common">Title:<label><input type="text" name="title" value="<?=htmlentities('RE: '.$page_title)?>" />
				<?=csrf_token()?>
				<label for="body">Body:<label>
				<textarea name="body"></textarea>
				<input type="submit" value="Send" name="act" />
			</div>
		</form>
<?
		$page_title="PM Thread: ".$page_title;
		break;
	default:
}

include ('includes/footer.php');
