<?php

require('includes/header.php');
force_id();
Output::Assign('sidebar',$sidebar);
Output::$tpl->display('dashhead.tpl.php');
$page_title = 'Your trash can';

if($_POST['empty_trash'])
{
	$delete_topic = $link->prepare('DELETE FROM trash WHERE uid = ?');
	$delete_topic->bind_param('s', $_SESSION['UID']);
	$delete_topic->execute();
	$delete_topic->close();
	
	$_SESSION['notice'] = 'Trash emptied.';
}

echo '<p>Your deleted topics and replies are archived here.</p>';

if($trash = show_trash($_SESSION['UID']))
{
	echo $trash;
	?>

	<form action="" method="post">
		<div class="row">
			<input type="submit" name="empty_trash" value="Empty trash can" onclick="return confirm('Really?');" />
		</div>
	</form>

	<?php
}

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

?>
