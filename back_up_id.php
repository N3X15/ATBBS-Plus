<?php
require('includes/header.php');
update_activity('back_up_id');
Output::Assign('sidebar',$sidebar);
Output::$tpl->fetch('dashhead.tpl.php');
force_id();

$page_title = 'Back up ID';

if($_GET['action'] === 'generate_id_card')
{
	header('Content-type: text/plain');
	header('Content-Disposition: attachment; filename="ATBBS_ID.crd"');
	echo $_SESSION['UID'] . "\n" . $_COOKIE['Password'];
	exit;
}

else 
{
	?>

	<table>
		<tr>
			<th class="minimal">Your unique ID</th>
			<td><code><?php echo $User->ID ?></code></td>
		</tr>
		<tr>
			<th class="minimal">Your password</th>
			<td><code><?php echo $_COOKIE['Password'] ?></code></td>
		</tr>
	</table>

	<p>You may want to <a href="/generate_ID_card">download your ID card as a file</a>.</p>

	<?php
}

Output::$tpl->display('dashfooter.tpl.php');
require('includes/footer.php');

