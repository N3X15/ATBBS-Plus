<?php

// This file is for non-content actions.
require('includes/header.php');
force_id();

// Take the action ...
switch($_GET['action'])
{
	// Normal actions ...
	case 'watch_topic':
	
		if( ! ctype_digit($_GET['id']))
		{
			add_error('Invalid ID.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Watch topic';
		
		if(isset($_POST['id']))
		{
			$res=DB::Execute(sprintf('SELECT 1 FROM {P}Watchlists WHERE uid = \'%s\' AND topic_id = %d', $_SESSION['UID'], $id));
			if($res->RecordCount() == 0)
			{
				DB::Execute(sprintf('INSERT INTO {P}Watchlists (uid, topic_id) VALUES (\'%s\', %d)', $_SESSION['UID'], $_POST['id']));
			}
			
			redirect('Topic added to your watchlist.');
		}
		
	break;
	
	//Priveleged actions.
	
	case 'delete_page':
	
		if( ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		
		if( ! ctype_digit($_GET['id']))
		{
			add_error('Invalid ID.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Delete page';
		
		if(isset($_POST['id']))
		{
			DB::Execute('DELETE FROM {P}Pages WHERE id = '.intval($id));
			redirect('Page deleted.');
		}
		
	break;
	
	case 'ban_uid':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
	
		if( ! id_exists($_GET['id']))
		{
			add_error('There is no such user.', true);
		}
		
		$uid = $_GET['id'];
		$page_title = 'Ban poster ' . $id;
		
		$slt=new TablePrinter('Bans');
		$slt->DefineColumns(array('Confirm','UID','IP'),'IP');

		$i=0;
		$slt->Row(array(
			'<input type="hidden" name="uid['.$i.']" value="'.$uid.'"><input type="hidden" name="ip['.$i.']" value="-"><input type="checkbox" name="confirm['.$i.']" value="1" checked="checked" />',
			$uid,
			$ip));

		Output::$tpl->display('controlpanel/banform.start.php');
		echo $slt->Output('ban-ees');
		Output::$tpl->display('controlpanel/banform.end.php');
		include('includes/footer.php');
		exit;
		break;
		
	break;
		
	case 'unban_uid':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		
		if( ! id_exists($_GET['id']))
		{
			add_error('There is no such user.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Unban poster ' . $id;
		
		if(isset($_POST['id']))
		{
			remove_id_ban($id);
			
			redirect('User ID unbanned.');
		}
		
	break;
		
	case 'unban_ip':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		
		if( ! filter_var($_GET['id'], FILTER_VALIDATE_IP))
		{
			add_error('That is not a valid IP address.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Unban IP address ' . $id;
		
		if(isset($_POST['id']))
		{
			remove_ip_ban($id);
			
			redirect('IP address unbanned.');
		}
		
	break;
	
	case 'delete_topic':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		if( ! ctype_digit($_GET['id']))
		{
			add_error('Invalid topic ID.', true);
		}
		
		$id = intval($_GET['id']);
		$page_title = 'Delete topic';
	
		if(isset($_POST['id']))
		{
			// Move record to user's trash.
			DB::Execute(DB::Prepare('INSERT INTO {P}Trash (uid, headline, body, time) SELECT topics.author, topics.headline, topics.body, UNIX_TIMESTAMP() FROM {P}Topics as topics WHERE topics.id = ?;'),array($id));
		
			// And delete it from the main table.
			DB::Execute('DELETE FROM {P}Topics WHERE id = '.$id);
			
			redirect('Topic archived and deleted.', '');
		}
		
	break;
		
	case 'delete_reply':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		if( ! ctype_digit($_GET['id']))
		{
			add_error('Invalid reply ID.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Delete reply';
	
		if(isset($_POST['id']))
		{
			$res=DB::Execute('SELECT parent_id FROM {P}Replies WHERE id = '.$id);

			list($parent_id)=$res->FetchRow();
			
			if( ! $parent_id)
			{
				add_error('No such reply.', true);
			}
		
			// Move record to user's trash.
			DB::Execute('INSERT INTO trash (uid, body, time) SELECT replies.author, replies.body, UNIX_TIMESTAMP() FROM {P}Replies as replies WHERE replies.id = '.$id);
		
			// And delete it from the main table.
			DB::Execute('DELETE FROM {P}Replies WHERE id = '.$id);
			
			// Reduce the parent's reply count.
			DB::Execute('UPDATE {P}Topics SET replies = replies - 1 WHERE id = '.$parent_id);
			
			redirect('Reply archived and deleted.');
		}
		
	break;
	
	case 'delete_ip_ids':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		
		if( ! filter_var($_GET['id'], FILTER_VALIDATE_IP))
		{
			add_error('That is not a valid IP address.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Delete IDs assigned to <a href="/IP_address/' . $id . '">' . $id . '</a>';
		
		if(isset($_POST['id']))
		{
			DB::Execute("DELETE FROM {P}Users WHERE ip_address = '{$id}'");
			
			redirect('IDs deleted.');
		}
		
	break;
	
	case 'nuke_id':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		
		if( ! id_exists($_GET['id']))
		{
			add_error('There is no such user.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Nuke all posts by <a href="/profile/' . $id . '">' . $id . '</a>';
		
		if(isset($_POST['id']))
		{
			// Delete replies.
			$res=DB::Execute("SELECT parent_id FROM {P}Replies WHERE author = '{$id}'");
			
			$victim_parents = array();
			while(list($parent_id)=$res->FetchRow())
			{
				$victim_parents[] = $parent_id;
			}
			
			DB::Execute("DELETE FROM {P}Replies WHERE author = '{$id}'");
			
			$sql = DB::Prepare('UPDATE {P}Topics SET replies = replies - 1 WHERE id = ?');
			foreach($victim_parents as $parent_id)
			{
				DB::Execute($sql, array($parent_id));
			}
			
			// Delete topics.
			DB::Execute("DELETE FROM {P}Topics WHERE author = '{$id}'");
			
			redirect('All topics and replies by ' . $id . ' have been deleted.');
		}
		
	break;
	
	case 'nuke_ip':
	
		if( ! $moderator && ! $administrator)
		{
			add_error('You are not wise enough.', true);
		}
		
		if( ! filter_var($_GET['id'], FILTER_VALIDATE_IP))
		{
			add_error('That is not a valid IP address.', true);
		}
		
		$id = $_GET['id'];
		$page_title = 'Nuke all posts by <a href="/IP_address/' . $id . '">' . $id . '</a>';
		
		if(isset($_POST['id']))
		{
			// Delete replies.
			$res=DB::Execute("SELECT parent_id FROM {P}Replies WHERE author_ip = '{$id}'");
			
			$victim_parents = array();
			while(list($parent_id)=$res->FetchRow())
			{
				$victim_parents[] = $parent_id;
			}
			
			DB::Execute("DELETE FROM {P}Replies WHERE author_ip = '{$id}'");
			
			$sql=DB::Prepare('UPDATE {P}Topics SET replies = replies - 1 WHERE id = ?');
			foreach($victim_parents as $parent_id)
			{
				DB::Execute($sql,array($parent_id));
			}
			
			// Delete topics.
			DB::Execute("DELETE FROM {P}Topics WHERE author_ip = '{$id}'");
			
			redirect('All topics and replies by ' . $id . ' have been deleted.');
		}
		
	break;
	
	default:
		add_error('No valid action specified.', true);	
}

echo '<p>Really?</p> <form action="" method="post"> <div> <input type="hidden" name="id" value="' . $id . '" /> <input type="submit" value="Do it" /> </div>';

require('includes/footer.php');

?>
