<?php

require('includes/header.php');
$page_title = 'Latest bulletins';
$tinymce=true;


// Require Administrative pow-hurrs.
if($User->isAdmin())
{
	if($_POST['form_sent'])
	{	
		if($_POST['post'])
		{		
			//Determine author	
			if(isset($_POST['admin']) && $User->isAdmin())
			{		
				$author = '<b><u>Sysop</u></b>';		
			} else {
				$author = "?";
			}
			if(!isset($_POST['body']))
			{
				Output::HardError("It appears you did not actually type anything. Stopping here...");	
			} else {
				$body = $_POST['body'];
			}	
			//Actually do the posting... pretty messy but I don't really care
			// I DO.  PRETTIFIED.
			DB::Execute('INSERT INTO {P}Bulletins (time, author, body) VALUES (UNIX_TIMESTAMP(),'.DB::Q($author).','.DB::Q($body).')');
			
			redirect("Bulletin posted."); 
		}
	} else {
?>
	<form action="" method="post">
		<h3>Add new bulletin</h3>
		<div class="body">
			<div class="noscreen">
				<input name="form_sent" type="hidden" value="1" />
				<input name="start_time" type="hidden" value="<?php echo $start_time ?>" />
			</div>
			<div class="row">
				<label for="body" class="noscreen">Post body</label> (Use of HTML allowed.)
				<textarea name="body" cols="120" rows="18" tabindex="2" id="body" class="mceEditor"></textarea>
			</div>
			<div class="row">
				<label for="admin" class="inline">Post as Sysop</label><input type="checkbox" name="admin" id="admin" class="inline" checked="checked" />
			</div>
			<div class="row">
				<input type="submit" name="post" tabindex="4" value="Post bulletin" class="inline" />
			</div>
		</div>
	</form>		
<?
	}
}

// We don't need to prepare it unless we're executing it multiple fucking times (which we shouldn't be)
$res=DB::Execute('SELECT id, time, author, body FROM {P}Bulletins ORDER BY id DESC LIMIT 20'); // Limit added to limit CPU load

while(list($bulletin_id, $bulletin_time, $bulletin_author, $bulletin_body)=$res->FetchRow()) 
{
?>
	<h3>
		<?=$bulletin_author?> posted a bulletin <?=calculate_age($bulletin_time)?> ago.
		<span class="reply_id unimportant">
			<a name="b<?=number_format($bulletin_id)?>" href="<?=THISURL?>/bulletins#b<?=number_format($bulletin_id)?>">#<?=number_format($bulletin_id)?></a>
		</span>
	</h3>
	<div class="body">
		<?=stripslashes($bulletin_body)?>
	</div>
<?
}

require('includes/footer.php');

