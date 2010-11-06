<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<title><?php echo strip_tags($page_title) . ' — ' . SITE_TITLE ?></title>
	<script type="text/javascript" src="/javascript/main.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="/style/global.css" />
	<link rel="icon" type="image/png" href="/favicon.png" />
	<?php echo $additional_head ?>
	
</head>
<?php
echo '<body';
if( ! empty($onload_javascript))
{
	echo ' onload="' . $onload_javascript . '"';
}
echo '>';

if( ! empty($_SESSION['notice']))
{
	echo '<div id="notice" onclick="this.parentNode.removeChild(this);"><strong>Notice</strong>: ' . $_SESSION['notice'] . '</div>';
	unset($_SESSION['notice']);
}
?>


<h1 id="logo">
	<a rel="index" href="/"><?php echo SITE_TITLE ?></a>
</h1>

<ul id="main_menu" class="menu"><?php


// Items in last_action_check need to be checked for updates.
$last_action_check = array();
if($_COOKIE['topics_mode'] == 1)
{
	$last_action_check['Topics'] = 'last_topic';
	$last_action_check['Bumps'] = 'last_bump';
}
else
{
	$last_action_check['Topics'] = 'last_bump';
	
	// Remove the "Bumps" link if bumps mode is default.
	array_splice($main_menu, 1, 1);
}
					
foreach($main_menu as $linked_text => $path)
{
	// Output the link if we're not already on that page.
	if($path != $_SERVER['REQUEST_URI'])
	{
		echo indent() . '<li><a href="' . $path . '">' . $linked_text;
		
		// If we need to check for new stuff...
		if( isset($last_action_check[ $linked_text ]) )
		{
			$last_action_name = $last_action_check[ $linked_text ];
			//If there's new stuff, print an exclamation mark.
			if(isset($_COOKIE[$last_action_name]) && $_COOKIE[ $last_action_name ] < $last_actions[ $last_action_name ])
			{
				echo '!';
			}
		}
		
		echo '</a></li>';
	}
}
?>

</ul>

<h2><?php echo $page_title ?></h2>

<?php
echo $buffered_content;
?> 

</body>
</html>
