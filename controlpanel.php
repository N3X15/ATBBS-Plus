<?php

require('includes/header.php');
//DB::ToggleDebug();
if(!isPowerUser()) 
	Output::HardError("You are not wise enough.");

Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');

switch(Path::FetchIndex(0))
{
	case 'powerusers':
		update_activity('admins');

		if(!$User->isAdmin()) 
			Output::HardError('You are not wise enough.');

		if($_POST['form_sent'] && check_token())
		{
			//var_dump($_POST); exit;
			$lvl=$User->getMACLevel();
			if(!empty($_POST['add_sysop']))
			{
				if($lvl<999) die('No.');
				DB::Execute('UPDATE {P}UserSettings SET usrFlags=usrFlags|'.PERMISSION_SYSOP.' WHERE usrID='.DB::Q($_POST['add_sysop']));
				$_SESSION['notice'].='<li>'.$_POST['add_sysop'].' added as a '.ADMIN_NAME.'.</li>';
			}
			if(!empty($_POST['add_mod']))
			{
				if($lvl<100) die('No.');
				DB::Execute('UPDATE {P}UserSettings SET usrFlags=usrFlags|'.PERMISSION_MOD.' WHERE usrID='.DB::Q($_POST['add_mod']));
				$_SESSION['notice'].='<li>'.$_POST['add_mod'].' added as a '.MOD_NAME.'.</li>';
			}
			if(!empty($_POST['revoke']) && is_array($_POST['revoke']) && count($_POST['revoke'])>0)
			{
				if($lvl<999) die('No.');
				$c=0;
				foreach($_POST['revoke'] as $modid)
				{
					$u=new User($modid);
					$u->Load();
					if($u->getMACLevel()>=$lvl) 
						die('No.');
					$u->Flags&=~PERMISSION_MOD; // Remove mod flag
					$u->Flags&=~PERMISSION_SYSOP; // Remove admin flag
					$u->Save();
					$c++;
				}
				$_SESSION['notice'].='<li>'.$c.' power users revoked.</li>';
			}
			$_SESSION['notice']="<ul>{$_SESSION['notice']}</ul>";
		}
		$page_title = 'Manage Users';

		$mods=new TablePrinter('tblModerators');
		$mods->DefineColumns(array('&nbsp;','UID','Last Action'),'Last Action');
		$mods->SetTDClass('UID','uid');

		$admins=new TablePrinter('tblAdmins');
		$admins->DefineColumns(array('&nbsp;','UID','Last Action'),'Last Action');
		$admins->SetTDClass('UID','uid');

		$users=new TablePrinter('tblUsers');
		$users->DefineColumns(array('UID','Posts','Last Action'),'Last Action');
		$users->SetTDClass('UID','uid');

		$res=DB::Execute("SELECT usrID, usrFlags, 'Not Implemented Yet' FROM {P}UserSettings");
		while($row=$res->FetchRow())
		{
			list($id,$flags,$lastact) = $row;
	
			$chk='';
			if($User->ID==$id)
			{
				$id='(You)';
				$chk='';
			}
			else 
			{
				$chk='<input type="checkbox" name="revoke[]" value="'.$id.'" />';
				$id="<a href=\"/profile/$id\">$id</a>";
			}
			if((intval($flags)&PERMISSION_SYSOP)==PERMISSION_SYSOP)
				$admins->Row(array($chk,$id,$lastact));
			if((intval($flags)&PERMISSION_MOD)==PERMISSION_MOD)
				$mods->Row(array($chk,$id,$lastact));
		}

		// IT'S GAINED SELF-AWARENESS
		$res=DB::Execute("SELECT * FROM (SELECT u.usrID, COUNT(r.author)+COUNT(t.author) as postCount, 'Not Implemented Yet' FROM {P}UserSettings as u INNER JOIN {P}Topics as t INNER JOIN {P}Replies as r WHERE u.usrID=r.author AND u.usrID=t.author) as DICKS WHERE postCount>1000");
		while($row=$res->FetchRow())
		{
			$users->Row(array($row[0],$row[1],$row[2]));
		}
		?>
		<form action="" method="post">
			<?=csrf_token()?>
			<input type="hidden" name="form_sent" value="1" />
			<h2>Administrators</h2>
			<?=$admins->Output(ADMIN_NAME.'s')?>
			<input type="submit" value="REVOKE" />
			<h2>Moderators</h2>
			<?=$mods->Output(MOD_NAME.'s')?>
			<input type="submit" value="REVOKE" />
		</form>
		<h2>Potential Powerusers</h2>
		<?=$users->Output('worthy candidates')?>
		<?
		break;
	// POST from TOPIC.
	case 'topic':
		$page_title="Bulk Moderation Action";
		// Delete, Ban, and Filter checkbox contents.

		// Ban first.
		$cbt=count($_POST['bt']);
		$cb=count($_POST['b']);

		// Array:  topic => replies
		$tpc=array();

		$out="<ul>";

		if($cbt+$cb>0)
		{
			if($cbt>0)
			{
				$sql="SELECT DISTINCT id, author, author_ip, body FROM {P}Topics WHERE ";
				$i=0;
				foreach($_POST['bt'] as $topic)
				{
					if($i>0) $sql.=' OR ';
					$sql.="id=".intval($topic);
					$i++;
				}
				$res=DB::Execute($sql);
				while(list($id,$uid,$ip,$body)=$res->FetchRow())
				{
					$bans[$uid]=$ip;
					if($_POST['ft'] && in_array($id,$_POST['ft']))
						$_SESSION['2BFiltered'][]=defuck_comment($body);
				}
			}
			
			if($cb>0)
			{
				$sql="SELECT DISTINCT id, author, author_ip, body FROM {P}Replies WHERE ";
				$i=0;
				foreach($_POST['b'] as $reply)
				{
					if($i>0) $sql.=' OR ';
					$sql.="id=".intval($reply);
					$i++;
				}
				$res=DB::Execute($sql);
				while(list($id, $uid,$ip,$body)=$res->FetchRow())
				{
					$bans[$uid]=$ip;
					if($_POST['f'] && in_array($id,$_POST['f']))
						$_SESSION['2BFiltered'][]=defuck_comment($body);
				}
			}
			$slt=new TablePrinter('Bans');
			$slt->DefineColumns(array('Confirm','UID','IP'),'IP');

			$i=0;
			foreach($bans as $uid=>$ip)
			{
				$slt->Row(array(
					'<input type="hidden" name="uid['.$i.']" value="'.$uid.'"><input type="hidden" name="ip['.$i.']" value="'.$ip.'"><input type="checkbox" name="confirm['.$i.']" value="1" checked="checked" />',
					$uid,
					$ip));

				$i++;
			}

			Output::$tpl->display('controlpanel/banform.start.php');
			echo $slt->Output();
			Output::$tpl->display('controlpanel/banform.end.php');
		}
		// Filter second.
		// This just adds the post contents to a buffer to be processed later.
		$cft=count($_POST['ft']);
		$cf=count($_POST['f']);
		if($cft+$cf>0)
		{
			if($cft>0)
			{
				$sql="SELECT body FROM {P}Topics WHERE ";
				$i=0;
				foreach($_POST['ft'] as $topic)
				{
					if($i>0) $sql.=' OR ';
					$sql.="id=".intval($topic);
					$i++;
				}
				$res=DB::Execute($sql);
				while(list($body)=$res->FetchRow())
				{
					$b=defuck_comment($body);
					if(!in_array($b,$_SESSION['2BFiltered']))
						$_SESSION['2BFiltered'][]=$b;
				}
			}
			
			if($cf>0)
			{
				$sql="SELECT body FROM {P}Replies WHERE ";
				$i=0;
				foreach($_POST['b'] as $reply)
				{
					if($i>0) $sql.=' OR ';
					$sql.="id=".intval($reply);
					$i++;
				}
				$res=DB::Execute($sql);
				while(list($body)=$res->FetchRow())
				{
					$b=defuck_comment($body);
					if(!in_array($b,$_SESSION['2BFiltered']))
						$_SESSION['2BFiltered'][]=$b;
				}
			}
		}
		// Delete last.
		$cdt=count($_POST['dt']);
		$cd=count($_POST['d']);
		if($cdt+$cd>0)
		{
			if($cdt>0)
			{
				$out.= '<li>Deleted '.DeleteTopics($_POST['dt']).' topics.</li>';
			}
			if($cd>0)
			{
				$i=DeleteReplies($_POST['d']);
				$out.= '<li>Deleted '.$i.' replies.</li>';
			}
		}
		$fq=count($_SESSION['2BFiltered']);
		 echo "$out<li>{$fq} items are queued to be filtered.  After processing bans, you will be redirected to a page to add these filters. [<a href=\"/controlpanel/filters/\">I've decided I want to skip bans and go right to filtering.</a>]</li></ul>";
		break;
	case 'process_ban':
		$page_title="Processing Bans";
		
		$flags=0;
		if($_POST['stealth']=='1')
			$flags|=BANF_STEALTH;
		if($_POST['flag']=='1')
			$flags|=BANF_MARK;
		if($_POST['list']=='1')
			$flags|=BANF_LIST;
		if($_POST['read']=='1')
			$flags|=BANF_NO_READ;

		$topics = $replies = array();

		// BAN TIEM
		$expiry = ParseExpiry($_POST['time']);
		$expiry+=$_SERVER['REQUEST_TIME'];

		$sql="INSERT INTO {P}Bans (uid, ip, expiry, reason, flags) VALUES";
		$dsql='';
		$i=0;
		foreach($_POST['confirm'] as $idx=>$confirmed)
		{
			if($confirmed!='1') continue;

			$id=DB::Q($_POST['uid'][$idx]);
			$ip=DB::Q($_POST['ip'][$idx]);

			if($i>0) {$sql.=', ';$dsql.=' OR ';}
			$i++;
			$sql.=sprintf('(%s, %s, %s, %s, %s)',$id, $ip, $expiry, DB::Q($_POST['reason']),$flags);
			$dsql.=sprintf('author=%s OR author_ip=%s',$id,$ip);
		}
		$sql.=' ON DUPLICATE KEY UPDATE expiry='.$expiry;
		DB::Execute($sql);
		$notice = '<p>'.count($i).' UID bans filed</p>';

		if($_POST['nuke']=='1')
		{
			$res=DB::Execute("SELECT id FROM {P}Topics WHERE $dsql");
			if($res->RecordCount()>0)
			{
				while(list($id)=$res->FetchRow())
					$topics[]=$id;
			}
			$res=DB::Execute("SELECT id FROM {P}Replies WHERE $dsql");
			if($res->RecordCount()>0)
			{
				while(list($id)=$res->FetchRow())
					$replies[]=$id;
			}
			$notice.='<p>Also deleted '.DeleteReplies($replies).' replies and '.DeleteTopics($topics).' topics.';
		}
	
		if(count($_SESSION['2BFiltered'])>0)
		{
			//redirect($notice);
			$_SESSION['notice']=$notice;
			Output::$tpl->Display('controlpanel/add_filters.tpl.php');
		}
		break;
	case 'filters':
		$page_title="Word and Phrase Filters";

		$tbl=new TablePrinter('tblFilters');
		$tbl->DefineColumns(array('Remove','#','Forbidden Text','Why','Punishment'),'Forbidden Text');
		$tbl->SetTDClass('Remove','chkboxes');

		$res=DB::Execute('SELECT filID,filText,filReason,filPunishType,filPunishDuration,filReplacement FROM {P}Filters ORDER BY filText ASC');
		while(list($id,$txt,$reason,$punish,$expiry,$replacement) = $res->FetchRow())
		{
			$puntxt='';
			switch($punish)
			{
				// Replace text with "x"
				case PUNISH_REPLACE:
					$puntxt='<b>Replace text with:</b><br />'.$replacement;
					break;
				// Ban user for timespec
				case PUNISH_BAN:
					$puntxt='<b>Ban user for '.calculate_age($expiry+time(),time()).'.</b>';
					break;
				case PUNISH_ERROR:
					$puntxt='<b>Display a 403 and stop posting.</b>';
					break;
				default:
					$puntxt="<b>Unknown: \$punish=$punish; \$expiry=$expiry; \$replacement=$replacement;</b>";
					break;
			}
			$r = array('<input type="checkbox" name="del_filter[]" value="'.$id.'" />',$id,"<code>$txt</code",$reason,$puntxt);
			$tbl->Row($r);
		}
		Output::Assign('tbl',$tbl);
		Output::Assign('Options',array(
				PUNISH_REPLACE	=> 'Replace',
				PUNISH_BAN	=> 'Ban',
				PUNISH_ERROR	=> 'Display Error')
		);
		Output::$tpl->display('controlpanel/list_filters.tpl.php');

		break;
	case 'add_filters':
		$page_title="Process Filters";

/*		
			filter[]	Text to filter
			type[]		filter_type
			replace[]	Replace with
			duration[]	Ban Duration
			reason[]	Ban Reason
			del_filter[]	Delete Filter
*/
		// Delete, then add (in case of too much disk space)
		if(count($_POST['del_filter'])>0)
		{
			$sql='DELETE FROM {P}Filters WHERE ';
			$i=0;
			foreach($_POST['del_filter'] as $fid)
			{
				$id=intval($fid);
				if($i>0) $sql.=' OR ';
				$sql.=' filID='.$id;
			}
			DB::Execute($sql);
		}

		if(count($_POST['filter'])>0)
		{
			// NOW add.
			$sql='INSERT INTO {P}Filters (filText,filReason,filPunishType,filPunishDuration,filReplacement) VALUES ';
			$c=0;
			for($i=0;$i<count($_POST['filter']);$i++)
			{
				if(strlen($_POST['filter'][$i])==0)
					continue;

				if(!(intval($_POST['confirm'][$i])==1))
					continue;

				if(DB::Execute('SELECT 1 FROM {P}Filters WHERE filText='.DB::Q($_POST['filter'][$i]))->RecordCount()>0)
					Output::HardError('A filter for '.htmlentities($_POST['filter'][$i]).' already exists.');
				if($c>0) $sql.=',';
				$sql.=sprintf('(%s,%s,%d,%s,%s)',
					defuck_comment(DB::Q($_POST['filter'][$i])),
					DB::Q($_POST['reason'][$i]),
					intval($_POST['type'][$i]),
					ParseExpiry($_POST['duration'][$i]),
					DB::Q($_POST['replace'][$i]));
				$c++;
			}
			if($c>0)DB::Execute($sql);
		}
		redirect('Done.');
		break;
	case 'read_appeals':
		$page_title="Appeals";
		echo '<form action="/controlpanel/process_appeals/" method="post">';
		
		$slt=new TablePrinter('UIDAppeals');
		$slt->DefineColumns(array('Unban/Deny Appeal','UID','IP Address','Banned For','Appeal'),'UID/IP');
		$slt->SetTDClass('Appeal','appeal');

		$i=0;
		$res=DB::Execute("SELECT uid, ip, reason, appeal FROM {P}Bans WHERE appeal!=''");

		while(list($uid,$reason,$appeal)=$res->FetchRow())
		{
			$slt->Row(array(
					'<input type="checkbox" name="unban_uid[]" value="'.$uid.'" /><input type="checkbox" name="deny_appeal[]" value="'.$uid.'" />',
					$uid,$ip,
					$reason,
					htmlspecialchars($appeal)));
		}
		echo $slt.'<input type="submit" value="Unban"></forms>';
		break;
	case 'process_appeals':
		$page_title="Processing appeals";
		
		if(count($_POST['unban_uid'])>0)
		{
			$sql="DELETE FROM {P}Bans WHERE ";
			$i=0;
			foreach($_POST['unban_uid'] as $uid)
			{
				if($i>0) $sql.=" OR ";
				$i++;
				$sql.='uid='.DB::Q($uid);
			}
			DB::Execute($sql);
			?>
			<p><?=$i?> UID bans removed.</p>
			<?
		}
		
		if(count($_POST['unban_ip'])>0)
		{
			$sql="DELETE FROM {P}Bans WHERE ";
			$i=0;
			foreach($_POST['unban_ip'] as $uid)
			{
				if($i>0) $sql.=" OR ";
				$i++;
				$sql.='ip='.DB::Q($uid);
			}
			DB::Execute($sql);
			?>
			<p><?=$i?> IP bans removed.</p>
			<?
		}
		
		if(count($_POST['deny_appeal'])>0)
		{
			$sql='UPDATE {P}Bans SET flags=flags|'.BANF_APPEAL_DENIED.' WHERE ';
			$i=0;
			foreach($_POST['deny_appeal'] as $uid)
			{
				if($i>0) $sql.=" OR ";
				$i++;
				$sql.='uid='.DB::Q($uid);
			}
			DB::Execute($sql);
			?>
			<p><?=$i?> appeals denied.</p>
			<?
		}
		break;
	default:
		Output::HardError(htmlentities(Path::FetchIndex(0)).' is an unrecognized method.');
		exit;
		break;
}
Output::$tpl->display('dashfooter.tpl.php');

require('includes/footer.php');

// 3m2d = 3 months, 2 days from now
// 3000 = 3000s.
function ParseExpiry($str)
{
	$tb = 0;
	$sb = '';
	while(strlen($str)>0)
	{
		$c=substr($str,0,1);
		$str=substr($str,1,strlen($str)-1);
		switch($c)
		{
			default:
				break;
			case '0':
			case '1':
			case '2':
			case '3':
			case '4':
			case '5':
			case '6':
			case '7':
			case '8':
			case '9':
				$sb.=$c;
				break;
			case 's':  // Seconds
				$tb+=intval($sb);
				$sb='';
				break;
			case 'h':  // Hours
				$tb+=intval($sb)*60*60;
				$sb='';
				break;
			case 'd':  // Days
				$tb+=intval($sb)*60*60*24;
				$sb='';
				break;
			case 'w':  // Weeks
				$tb+=intval($sb)*60*60*24*7;
				$sb='';
				break;
			case 'm':  // Months
				$tb+=intval($sb)*60*60*24*30;
				$sb='';
				break;
			case 'y':  // Years
				$tb+=intval($sb)*60*60*24*365;
				$sb='';
				break;
			case 'P':  // 150 years :o
				$tb+=60*60*24*365*150;
				$sb='';
				break;
		}
	}
	if(strlen($sb)!='') $tb+=intval($sb);
	return $tb;
}
