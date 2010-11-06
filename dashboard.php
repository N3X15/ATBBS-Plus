<?php

require('includes/header.php');
force_id();
update_activity('dashboard');
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');
$page_title = $User->Level.' Dashboard';

// Get our user's settings from the database.
// Done by User class now

if($_POST['form_sent'])
{
	
	$name 	= POST::GetEString('memorable_name',true);
	$pass 	= POST::GetEString('memorable_password',true);
	$pass2	= POST::GetEString('memorable_password2',true);
	$email	= POST::GetEString('email',true);
	$theme 	= POST::GetEString('theme',true,'atbbs');
	
	$flag_topics	= POST::GetInt('topics_mode')==1;
	$flag_ostrich	= POST::GetInt('ostrich_mode')==1;
	$flag_spoiler	= POST::GetInt('spoiler_mode')==1;
	$snippet_len	= POST::GetInt('snippet_length');
	
	// Make some specific validations ...
	if( ! empty($_POST['form']['memorable_name']) && $_POST['form']['memorable_name'] != $user_config['memorable_name'])
	{
		// Check if the name is already being used.
		$res = DB::Execute('SELECT 1 FROM {P}UserSettings WHERE LOWER(usrName) = LOWER('.DB::Q($_POST['form']['memorable_name']).')');

		if($res->RecordCount() > 0)
		{
			add_error('The memorable name "' . htmlspecialchars($_POST['memorable_name']) . '" is already being used.');
		}
	}
	if($pass!=$pass2) add_error(' Both password fields must match.');
	if(!array_key_exists($theme,getAvailableThemes()))
		Output::HardError($theme.' isn\'t a valid theme.');

	if( ! $erred)
	{
		$User->UserName=$name;
		$User->Email=$email;
		$User->Flags=0;
		if($flag_topics)
			$User->Flags|=FLAG_TOPICS;
		if($flag_ostrich)
			$User->Flags|=FLAG_OSTRICH;
		if($flag_spoiler)
			$User->Flags|=FLAG_SPOILER;
		$User->SnippetLength=$snippet_len;
		$User->Theme=$theme;
		$User->Save();
		if(strlen($pass)>0) 
		$User->SetPassword($pass);
	}
	$_SESSION['notice'] = 'Settings updated.';
}

print_errors();

Output::$tpl->display('dashboard.tpl.php');

?>

<?php

require('includes/footer.php');

?>
