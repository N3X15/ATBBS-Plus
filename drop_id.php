<?php

require('includes/header.php');
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');
$page_title = 'Drop ID';

if($_POST['drop_ID'])
{
	unset($_SESSION['UID']);
	unset($_SESSION['ID_activated']);
	setcookie('UID', '', $_SERVER['REQUEST_TIME'] - 3600, '/');
	setcookie('password', '', $_SERVER['REQUEST_TIME'] - 3600, '/');
	setcookie('topics_mode', '', $_SERVER['REQUEST_TIME'] - 3600, '/');
	setcookie('spoiler_mode', '', $_SERVER['REQUEST_TIME'] - 3600, '/');
	setcookie('snippet_length', '', $_SERVER['REQUEST_TIME'] - 3600, '/');
	
	$_SESSION['notice'] = 'Your ID has been dropped.';
}

?>

<p>"Dropping" your ID will simply remove the UID, password, and mode cookies from your browser, effectively logging you out. If you want to keep your post history, settings, etc., <a href="/back_up_ID">back up your ID</a> or <a href="/dashboard">set a memorable password</a> before doing this.</p>

<form action="" method="post">
	<input type="submit" name="drop_ID" value="Drop my ID" />
</form>

<?php

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
