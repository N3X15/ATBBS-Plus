<?php

require('includes/header.php');

if( ! $administrator && ! $moderator)
{
	add_error('You are not wise enough.', true);
}

// Validate IP address.
if( ! filter_var($_GET['ip'], FILTER_VALIDATE_IP))
{
	add_error('That is not a valid IP address.', true);
}

$ip_address = $_GET['ip'];
$hostname = gethostbyaddr($ip_address);
if($hostname === $ip_address)
{
	$hostname = false;
}

$page_title = 'Information on IP address ' . $ip_address;

// If a ban request has been submitted...
if( ! empty($_POST['ban_length']))
{
	if($_POST['ban_length'] == 'indefinite' | $_POST['ban_length'] == 'infinite')
	{
		$new_ban_expiry = 0;
	}
	else if(strtotime($_POST['ban_length']) > $_SERVER['REQUEST_TIME'])
	{
		$new_ban_expiry = strtotime($_POST['ban_length']);
	}
	else
	{
		add_error('Invalid ban length.');
	}
	
	if( ! $erred)
	{
		$sql=DB::Prepare('INSERT INTO {P}IPBans (ip_address, expiry, filed) VALUES (?, ?, UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE expiry = ?, filed = UNIX_TIMESTAMP()');
		DB::Execute($sql,array(($ip_address, $new_ban_expiry, $new_ban_expiry);

		$_SESSION['notice'] = 'IP address banned.';
	}
}

// Check for ban.
$sql=DB::Prepare('SELECT filed, expiry FROM {P}IPBans WHERE ip_address = ?');
$res=DB::Execute($sql,array($ip_address));

list($ban_filed, $ban_expiry)=$res->FetchRow();

$banned = false;
if( ! empty($ban_filed))
{
	if($ban_expiry == 0 || $ban_expiry > $_SERVER['REQUEST_TIME'])
	{
		$banned = true;
	}
	else // the ban has already expired
	{
		remove_ip_ban($ip_address);
	}
}

// Get statistics.
$q_ip=DB::Q($ip_address);

$ip_num_topics	=DB::GetOne("SELECT count(*) FROM {P}Topics WHERE author_ip = {$q_ip}");
$ip_num_replies	=DB::GetOne("SELECT count(*) FROM {P}Replies WHERE author_ip = {$q_ip}");
$ip_num_ids	=DB::GetOne("SELECT count(*) FROM {P}Users WHERE ip_address = {$q_ip}");

echo '<p>This IP address (';
if($hostname)
{
	echo 'host name <strong>' . $hostname . '</strong>';
}
else
{
	echo 'no valid host name';
}
echo') is associated with <strong>' . $ip_num_ids . '</strong> ID' . ($ip_num_ids == 1 ? '' : 's') . ' and has been used to post <strong>' . $ip_num_topics . '</strong> existing topic' . ($ip_num_topics == 1 ? '' : 's') . ' and <strong>' . $ip_num_replies . '</strong> existing repl' . ($ip_num_replies == 1 ? 'y' : 'ies') . '.</p>';
if($banned)
{
	echo '<p>It is currently <strong>banned</strong>. The ban was filed <span class="help" title="' . format_date($ban_filed) . '">' . calculate_age($ban_filed) . ' ago</span> and will ';
	if($ban_expiry == 0)
	{
		echo 'last indefinitely';
	}
	else
	{
		echo 'expire in ' . calculate_age($ban_expiry);
	}
	echo '.</p>';
}
?>

<form action="" method="post">
	<div class="row">
		<label for="ban_length" class="inline">Ban length</label>
		<input type="text" name="ban_length" id="ban_length" value="<?php if( ! $banned) echo '1 week' ?>" class="inline" />
		<input type="submit" value="<?php echo ($banned) ? 'Update ban length' : 'Ban' ?>" class="inline" />
		<span class="unimportant">(A ban length of "indefinite" will never expire.)</span>
	</div>
</form>

<ul class="menu">
	<?php if($banned) echo '<li><a href="/unban_IP/' . $ip_address . '">Unban</a></li>' ?>
	<li><a href="/delete_IP_IDs/<?php echo $ip_address ?>">Delete all IDs</a></li>
	<li><a href="/nuke_IP/<?php echo $ip_address ?>">Delete all posts</a></li>
	<li><a href="http://toolserver.org/~chm/whois.php?ip=<?php echo $ip_address ?>">Whois</a></li>
</ul>


<?php

if($ip_num_ids > 0)
{
	echo '<h4 class="section">IDs</h4>';

	$sql=DB::Prepare('SELECT uid, first_seen FROM {P}Users WHERE ip_address = ? ORDER BY first_seen DESC LIMIT 5000');
	$res=DB::Execute($sql,array($ip_address));
	
	$id_table = new table();
	$columns = array
	(
		'ID',
		'First seen ▼'
	);
	$id_table->define_columns($columns, 'ID');

	while(list($id,$id_first_seen)=$res->FetchRow()) {
		$values = array 
		(
			'<a href="/profile/' . $id . '">' . $id . '</a>',
			'<span class="help" title="' . format_date($id_first_seen) . '">' . calculate_age($id_first_seen) . '</span>'
		);
									
		$id_table->row($values);
	}
	echo $id_table->output();
}

require('includes/footer.php');

?>
