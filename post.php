<?php

require('includes/header.php');
force_id();
$erred=false;
$reply=false;
$inserted_id=0;
$image_data=array();
$authorname='';
$editing=false;
Check4Ban(true);
if($_GET['reply'])
{
	$reply = true;
	$onload_javascript = 'focusId(\'body\'); init();';
	
	if( ! ctype_digit($_GET['reply'])) 
	{
		add_error('Invalid topic ID.', true);
	}
	
	$sql = sprintf('SELECT headline, author, replies FROM {P}Topics WHERE id = %d',intval($_GET['reply']));
	$res=DB::Execute($sql);
	if(!$res)
	{
		$page_title = 'Non-existent topic';
		add_error('There is no such topic. It may have been deleted.', true);
	}
	list($replying_to, $topic_author, $topic_replies)=$res->fields;
	
	update_activity('replying', $_GET['reply']);
	$page_title = 'New reply in topic: <a href="/topic/' . htmlspecialchars($_GET['reply']) . '">' . htmlspecialchars($replying_to) . '</a>';
	
	$n=DB::GetOne(sprintf('SELECT COUNT(*) FROM {P}Watchlists WHERE uid =\'%s\'AND topic_id = %d',$_SESSION['UID'], $_GET['reply']));

	if($n > 0)
	{
		$watching_topic = true;
	}
}
else // this is a topic
{
	$reply = false;
	$onload_javascript = 'focusId(\'headline\'); init();';
	update_activity('new_topic');
	
	$page_title = 'New topic';
	
	if( ! empty($_POST['headline']))
	{
		$page_title .= ': ' . htmlspecialchars(Post::GetEString('headline'));
	}
}

// If we're trying to edit and it's not disabled in the configuration ...
if(ALLOW_EDIT && ctype_digit($_GET['edit']))
{
	$editing = true;
	
	if($reply)
	{
		$sql = 'SELECT author, name, time, body, flags FROM {P}Replies WHERE id = %d';
	}
	else
	{
		$sql = 'SELECT author, name, time, body, flags, headline FROM {P}Topics WHERE id = %d';
	}
		
	$res=DB::Execute(sprintf($sql,$_GET['edit']));
	if($res->RecordCount()==0)
	{
		add_error('There is no such post. It may have been deleted.', true);
	}
	
	$edit_data=$res->FetchRow();
	if($reply)
	{
		//$edit_data=$res->FetchAssoc();//$edit_data['author'], $edit_data['time'], $edit_data['body'], $edit_data['mod']);
		$page_title = 'Editing <a href="/topic/' . intval($_GET['reply']) . '#reply_' . $_GET['edit'] . '">reply</a> to topic: <a href="/topic/' . $_GET['reply'] . '">' . htmlspecialchars($replying_to) . '</a>';
	}
	else
	{
		$page_title = 'Editing topic';
	}
	
	if($edit_data['author'] === $_SESSION['UID'])
	{
		$edit_mod = 0;
		
		if( ! $administrator && ! $moderator)
		{
			if(TIME_TO_EDIT != 0 && ( $_SERVER['REQUEST_TIME'] - $edit_data['time'] > TIME_TO_EDIT ))
			{
				add_error('You can no longer edit your post.', true);
			}
			
			// HELPS TO USE THE CORRECT CONSTANTS :|
			if((intval($edit_data['flags'])&REPLY_EDIT_BY_MOD)==REPLY_EDIT_BY_MOD)
			{
				add_error('You cannot edit a post that has been edited by a moderator.');
			}
		}
	}
	else if($administrator || $moderator)
	{
		$edit_mod = 1;
	}
	else
	{
		add_error('You are not allowed to edit that post.', true);
	}
	
	if( ! $_POST['form_sent'])
	{
		$body = $edit_data['body'];
		$authorname=$edit_data['name'];
		if( ! $reply)
		{
			$page_title .= ': <a href="/topic/' . $_GET['edit'] . '">' . htmlspecialchars($edit_data['headline']) . '</a>';
			$headline = $edit_data['headline'];
		}
	}
	else if( ! empty($_POST['headline']))
	{
		$page_title .= ':  <a href="/topic/' . $_GET['edit'] . '">' . htmlspecialchars(Post::GetEString('headline')) . '</a>';
	}
}


//var_dump($_POST);

if($_POST['form_sent'])
{
	// Trimming.
	$headline = super_trim(Post::GetEString('headline',true));
	$body = super_trim($_POST['body']);
	$authorname= super_trim(Post::GetEString('name',true));

	if(!empty($authorname))
		$_SESSION['PostName']=$authorname;

	// Parse for mass quote tag ([quote]). I'm not sure about create_function, it seems kind of slow.
	$body = preg_replace_callback(
		'/\[quote\](.+?)\[\/quote\]/s',
		create_function(
			'$matches', 
			'return preg_replace(\'/.*[^\s]$/m\', \'> $0\', $matches[1]);'
		),
		$body
	);

	list($headline,$body)= Check4Filtered($headline,$body);

	if($_POST['post']) 
	{
		// Check for poorly made bots.
		if( ! $editing && $_SERVER['REQUEST_TIME'] - Post::GetInt('start_time') < 3 )
		{
			add_error('Wait a few seconds between starting to compose a post and actually submitting it.');
		}
		if( ! empty($_POST['e-mail']))
		{
			add_error('Bot detected.');
		}
		if( ! is_array($_SESSION['random_posting_hashes']) ) 
		{
			add_error('Session error (no hash values stored). Try again.');
		}
		else foreach($_SESSION['random_posting_hashes'] as $name => $value) 
		{
			if( ! isset($_POST[$name]) || $_POST[$name] != $value) 
			{
				add_error('Session error (wrong hash value sent). Try again.');
				break;
			}
		}

		
		
		if(strlen($body)<MIN_LENGTH_BODY || strlen($body) > MAX_LENGTH_BODY)
		{
			$isTooShort=(strlen($body)<MIN_LENGTH_BODY);
			$too_wat= $isTooShort ? 'too short (minimum' : 'too long (maximum';
			$figure = $isTooShort ? MIN_LENGTH_BODY : MAX_LENGTH_BODY;
			Output::HardError("Your post is ".strlen($body)." characters in size, which is {$too_wat} length is {$figure} characters).");
		}
		if(count( explode("\n", $body) ) > MAX_LINES)
		{
			add_error('Your post has too many lines.');
		}
		if(ALLOW_IMAGES && ! empty($_FILES['image']['name']) && ! $editing)
		{
			$image_data = array();
			
			 switch($_FILES['image']['error']) 
			 {
				case UPLOAD_ERR_OK:
					$uploading = true;
				break;
				
				case UPLOAD_ERR_PARTIAL:
					add_error('The image was only partially uploaded.');
				break;
				
				case UPLOAD_ERR_INI_SIZE:
					add_error('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
				break;
				
				case UPLOAD_ERR_NO_FILE:
					add_error('No file was uploaded.');
				break;
				
				case UPLOAD_ERR_NO_TMP_DIR:
					add_error('Missing a temporary directory.');
				break;
				
				case UPLOAD_ERR_CANT_WRITE:
					add_error('Failed to write image to disk.');
				break;
				
				default:
					add_error('Unable to upload image.');
			}
			
			if($uploading)
			{
				$uploading = false; // until we make our next checks
				$valid_types = array
				(
					'jpg',
					'gif',
					'png'
				);
					
				$valid_name = preg_match('/(.+)\.([a-z0-9]+)$/i', $_FILES['image']['name'], $match);
				$image_data['type']		= strtolower($match[2]);
				$image_data['md5'] 		= md5_file($_FILES['image']['tmp_name']);
				$image_data['name'] 	= str_replace( array('.', '/', '<', '>', '"', "'", '%') , '', $match[1]);
				$image_data['name'] 	= substr( trim($image_data['name']) , 0, 35);
				
				if($image_data['type'] == 'jpeg')
				{
					$image_data['type'] = 'jpg';
				}
				
				if(file_exists('img/' . $image_data['name'] . '.' . $image_data['type']))
				{
					$image_data['name'] = $_SERVER['REQUEST_TIME'] . mt_rand(0, 99);
				}

				if($valid_name === 0 || empty($image_data['name']))
				{
					add_error('The image has an invalid file name.');
				}
				else if( ! in_array($image_data['type'], $valid_types))
				{
					$vtype=implode(', ',$valid_types);
					add_error('You uploaded an invalid file.  '.SITE_NAME.' only allows <strong>'.$vtype.'</strong> files.');
				}
				else if($_FILES['image']['size'] > MAX_IMAGE_SIZE)
				{
					add_error('Uploaded images can be no greater than ' . round(MAX_IMAGE_SIZE / 1048576, 2) . ' MB. ');
				}
				else
				{
					$uploading = true;
					$image_data['name'] = $image_data['name'] . '.' . $image_data['type'];
				}
			}
		}
		
		// Set the author (internal use only)
		$author = $_SESSION['UID'];
		if(isset($_POST['admin']) && $administrator)
		{
			$author = 'admin';
		}
		
		// If this is a reply...
		if($reply) 
		{	
			if( ! $editing)
			{
				//Lurk more?
				if($_SERVER['REQUEST_TIME'] - $_SESSION['first_seen'] < REQUIRED_LURK_TIME_REPLY)
				{
					add_error('Lurk for at least ' . REQUIRED_LURK_TIME_REPLY . ' seconds before posting your first reply.');
				}
				
				// Flood control.
				$too_early = $_SERVER['REQUEST_TIME'] - FLOOD_CONTROL_REPLY;
				$res=DB::Execute(sprintf('SELECT 1 FROM {P}Replies WHERE author_ip = \'%s\' AND time > %d',$_SERVER['REMOTE_ADDR'], $too_early));

				if($res->RecordCount() > 0)
				{
					add_error('Wait at least ' . FLOOD_CONTROL_REPLY . ' seconds between each reply. ');
				}
			
				// Get letter, if applicable.
				if($_SESSION['UID'] == $topic_author)
				{
					$poster_number = 0;
				}
				else // we are not the topic author
				{
					$res=DB::Execute(sprintf('SELECT poster_number FROM {P}Replies WHERE parent_id = %d AND author = \'%s\' LIMIT 1',$_GET['reply'], $author));

					list($poster_number)=$res->FetchRow();
					
					// If the user has not already replied to this thread, get a new letter.
					if(empty($poster_number))
					{
						// We need to lock the table to prevent others from selecting the same letter.
						$unlock_table = true;
						DB::Execute('LOCK TABLE {P}Replies WRITE');
						
						DB::Execute(sprintf('SELECT poster_number FROM {P}Replies WHERE parent_id = %d ORDER BY poster_number DESC LIMIT 1', $_GET['reply']));
						
						list($last_number)=$res->FetchRow();
						
						if(empty($last_number))
						{
							$poster_number = 1;
						}
						else
						{
							$poster_number = $last_number + 1;
						}
					}
				}
		
				DB::Execute(sprintf('INSERT INTO {P}Replies (author, name, author_ip, poster_number, parent_id, body, time) VALUES (\'%s\', \'%s\',\'%s\', %d, %d, %s, UNIX_TIMESTAMP())',$author,$authorname, $_SERVER['REMOTE_ADDR'], $poster_number, $_GET['reply'], DB::Q($body)));
				$congratulation = 'Reply posted.';
			}
			else // editing
			{
				//(mysql): UPDATE atbbs_Replies SET body = 'No more sysop powers for me, sniff.\r\n\r\ndaflkasdflafld', flags = 1, edit_time = UNIX_TIMESTAMP(), name='2' WHERE id = 0  
				DB::ToggleDebug();
				DB::Execute(sprintf(
					'UPDATE {P}Replies SET body =%s, flags = %d, edit_time = UNIX_TIMESTAMP(), name=\'%s\' WHERE id = %d', 
					DB::Q($body), 
					0|(1*$edit_mod),
					$authorname,
					$_GET['edit']));
//				exit;
				$congratulation = 'Reply edited.';
			}
		}
		else { // or a topic...
			check_length($headline, 'headline', MIN_LENGTH_HEADLINE, MAX_LENGTH_HEADLINE);

			if( ! $editing)
			{
				//Lurk more?
				if($_SERVER['REQUEST_TIME'] - $_SESSION['first_seen'] < REQUIRED_LURK_TIME_TOPIC)
				{
					Output::HardError('Lurk for at least ' . REQUIRED_LURK_TIME_TOPIC . ' seconds before posting your first topic.');
				}
				
				// Flood control.
				$too_early = $_SERVER['REQUEST_TIME'] - FLOOD_CONTROL_TOPIC;
				$res=DB::Execute(sprintf('SELECT 1 FROM {P}Topics WHERE author_ip = \'%s\' AND time > %d', $_SERVER['REMOTE_ADDR'], $too_early));

				if($res->RecordCount() > 0)
				{
					Output::HardError('Wait at least ' . FLOOD_CONTROL_TOPIC . ' seconds before creating another topic. ');
				}
				
				// Prepare our query...
				DB::Execute(sprintf('INSERT INTO {P}Topics (author, name, author_ip, headline, body, last_post, time) VALUES (\'%s\', \'%s\',\'%s\', \'%s\', %s, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())',$author, $authorname, $_SERVER['REMOTE_ADDR'], $headline, DB::Q($body)));
				$congratulation = 'Topic created.';
			}
			else // editing
			{
				$sql = sprintf('UPDATE {P}Topics SET headline = \'%s\', name=\'%s\', body = %s, flags = %d, edit_time = UNIX_TIMESTAMP() WHERE id = %d', $headline, $authorname, DB::Q($body), 0|(1*$edit_mod), $_GET['edit']);
				DB::Execute($sql);
				$congratulation = 'Topic edited.';
			}
		}
		
		// If all is well, execute!
		if( ! $erred) {
			
			if($unlock_table)
			{
				DB::Execute('UNLOCK TABLE');
			}
			
			//if($stmt->affected_rows > 0)
			//{
				// We did it!
				if( ! $editing)
				{
					setcookie('last_bump', time(), $_SERVER['REQUEST_TIME'] + 315569260, '/');
					if($reply)
					{
						// Update last bump.
						DB::Execute("UPDATE {P}LastActions SET time = UNIX_TIMESTAMP() WHERE feature = 'last_bump'");
					
						// wat?  use COUNT(*), dumbfuck.
						$sql=DB::Prepare('UPDATE {P}Topics SET replies = replies + 1, last_post = UNIX_TIMESTAMP() WHERE id = ?');
						DB::Execute($sql,array(intval($_GET['reply'])));
					}
					else // if topic
					{
						// Do not change the time() below to REQUEST_TIME. The script execution may have taken a second.
						setcookie('last_topic', time(), $_SERVER['REQUEST_TIME'] + 315569260, '/');
						//Update last topic and last bump, for people using the "date created" order option in the dashboard.
						DB::Execute("UPDATE {P}LastActions SET time = UNIX_TIMESTAMP() WHERE feature = 'last_topic' OR feature = 'last_bump'");
					}
				}
				
				// Sort out what topic we're affecting and where to go next. Way too fucking long.
				if( ! $editing)
				{
					$inserted_id = DB::GetLastID();
					
					if($reply)
					{
						$target_topic = $_GET['edit'];
						$redir_loc = $_GET['reply'] . '#reply_' . $inserted_id;
					}
					else // if topic
					{
						$target_topic = $inserted_id;
						$redir_loc = $inserted_id;
					}
				}
				else // if editing
				{
					if($reply)
					{
						$target_topic = $_GET['reply'];
						$redir_loc = $_GET['reply'] . '#reply_' . $_GET['edit'];
					}
					else // if topic
					{
						$target_topic = $_GET['edit'];
						$redir_loc = $_GET['edit'];
					}
				}
				
				// Take care of the upload.
				if($uploading)
				{
					// Check if this image is already on the server.
					$sql = DB::Prepare('SELECT file_name FROM {P}Images WHERE md5 = ?');

					$res=DB::Execute($sql,array($image_data['md5']));

					list($previous_image)=$res->FetchRow();
					
					// If the file has been uploaded before this, just link the old version.
					if($previous_image)
					{
						$image_data['name'] = $previous_image;
					}
					// Otherwise, keep the new image and make a thumbnail.
					else
					{
						thumbnail($_FILES['image']['tmp_name'], $image_data['name'], $image_data['type']);
						move_uploaded_file($_FILES['image']['tmp_name'], 'img/' . $image_data['name']);
					}
					
					$sql='';
					if($reply)
					{
						$sql = DB::Prepare('INSERT INTO {P}Images (file_name, md5, reply_id) VALUES (?, ?, ?)');
					}
					else
					{
						$sql = DB::Prepare('INSERT INTO {P}Images (file_name, md5, topic_id) VALUES (?, ?, ?)');
					}
					DB::Execute($sql, array($image_data['name'], $image_data['md5'], $inserted_id));
				}
				
				// Add topic to watchlist if desired.
				if($_POST['watch_topic'] && ! $watching_topic)
				{
					DB::Execute("INSERT INTO {P}Watchlists (uid, topic_id) VALUES ('{$_SESSION['uid']}', {$target_topic})");
				}
				
				// The random shit is only good for one post to prevent spambots from reusing the same form data again and again.
				unset($_SESSION['random_posting_hashes']);
				// Set the congratulation notice and redirect to affected topic or reply.
				redirect($congratulation, 'topic/' . $redir_loc);
			//}
			//else // Our query failed ;_;
			//{
			//	add_error('Database error.');
			//}
		}
		// If we erred, insert this into failed postings.
		else
		{
			if($unlock_table)
			{
				DB::Execute('UNLOCK TABLE');
			}
			
			if($reply)
			{
				$sql = DB::Prepare('INSERT INTO {P}FailedPostings (time, uid, reason, body) VALUES (UNIX_TIMESTAMP(), ?, ?, ?)');
				DB::Execute($sql, array($_SESSION['UID'], serialize($errors), substr($body, 0, MAX_LENGTH_BODY)));
			}
			else
			{
				$sql = DB::Prepare('INSERT INTO {P}FailedPostings (time, uid, reason, body, headline) VALUES (UNIX_TIMESTAMP(), ?, ?, ?, ?)');
				DB::Execute($sql, array($_SESSION['UID'], serialize($errors), substr($body, 0, MAX_LENGTH_BODY), substr($headline, 0, MAX_LENGTH_HEADLINE)));
			}
		}
	}
}

print_errors();

// For the bot check.
$start_time = $_SERVER['REQUEST_TIME'];
if( ctype_digit($_POST['start_time']) )
{
	$start_time = Post::GetInt('start_time',true);
}

echo '<div>';

// Check if OP.
if($reply && ! $editing) 
{
		echo '<p>You <strong>are';
		if($_SESSION['UID'] !== $topic_author)
		{
			echo ' not';
		}
		echo '</strong> recognized as the original poster of this topic.</p>';
}

// Print deadline for edit submission.
if($editing && TIME_TO_EDIT != 0 && ! $moderator && ! $administrator)
{
	echo '<p>You have <strong>' . calculate_age( $_SERVER['REQUEST_TIME'], $edit_data['time'] + TIME_TO_EDIT ) . '</strong> left to finish editing this post.</p>';
}

// Print preview.
if($_POST['preview'] && ! empty($body))
{
	$preview_body = parse($body);
	$preview_body = preg_replace('/^@([0-9,]+|OP)/m', '<span class="unimportant"><a href="#">$0</a></span>', $preview_body);
	echo '<h3 id="preview">Preview</h3><div class="body standalone">' . $preview_body . '</div>';
}

// Check if any new {P}Replies have been posted since we last viewed the topic.
if($reply && isset($visited_topics[ $_GET['reply'] ]) && $visited_topics[ $_GET['reply'] ] < $topic_replies)
{
	$new_replies = $topic_replies - $visited_topics[$_GET['reply']];
	echo '<p><a href="/topic/' . $_GET['reply'] . '#new"><strong>' . $new_replies . '</strong> new repl' . ($new_replies == 1 ? 'y</a> has' : 'ies</a> have') . ' been posted in this topic since you last checked!</p>';
}

// Print the main form.
	
?>
	
	<form action="" method="post"<?php if(ALLOW_IMAGES) echo ' enctype="multipart/form-data"' ?>>
		<div class="noscreen">
			<input name="form_sent" type="hidden" value="1" />
			<input name="e-mail" type="hidden" />
			<input name="start_time" type="hidden" value="<?php echo $start_time ?>" />
			<?php
			// For the bot check.
			if( ! is_array($_SESSION['random_posting_hashes']) )
			{
				for($i = 0, $max = mt_rand(3, 12); $i < $max; ++$i) 
				{
					$_SESSION['random_posting_hashes'][ dechex(mt_rand()) ] =  dechex(mt_rand());
				}
			}
			
			foreach($_SESSION['random_posting_hashes'] as $name => $value)
			{
				$attributes = array
				(
					'name="' . $name . '"',
					'value="' . $value . '"',
					'type="hidden"'
				);
				// To make life harder for bots, print the elements in a random order.
				shuffle($attributes);
				echo '<input ' . implode(' ', $attributes) . ' />' . "\n\t\t\t";
			}
			?>
			
		</div>
		
		<?php if( ! $reply): ?>
		<div class="row">
			<label for="headline">Headline</label> <script type="text/javascript"> printCharactersRemaining('headline_remaining_characters', 100); </script>
			<input id="headline" name="headline" tabindex="1" type="text" size="124" maxlength="100" onkeydown="updateCharactersRemaining('headline', 'headline_remaining_characters', 100);" onkeyup="updateCharactersRemaining('headline', 'headline_remaining_characters', 100);" value="<?php if($_POST['form_sent'] || $editing) echo htmlspecialchars($headline) ?>">
		</div>
		<?php endif; ?>
		<? if(!$editing || $_SESSION['UID']==$author):?>
		<div class="row">
			<label for="name">Name<span class="tripcode">#Tripcode</span></label>
			<input id="name" name="name" tabindex="2" type="text" size="25" maxlength="25" value="<?=($_POST['form_sent']||$editing) ? htmlspecialchars($authorname) : htmlspecialchars($_SESSION['PostName'])?>" />
		</div>
		<?endif;?>
		<div class="row">
			<label for="body" class="noscreen">Post body</label> 
			<textarea name="body" cols="120" rows="18" tabindex="3" id="body"><?php
			// If we've had an error or are previewing, print the submitted text.
			if($_POST['form_sent'] || $editing)
			{
				echo sanitize_for_textarea($body);
			}
			
			// Otherwise, fetch any text we may be quoting.
			else if(isset($_GET['quote_topic']) || ctype_digit($_GET['quote_reply']))
			{
				// Fetch the topic...
				$res=false;
				if(isset($_GET['quote_topic']))
				{
					$res=DB::Execute('SELECT body FROM {P}Topics WHERE id = '.$_GET['reply']);
				}
				// ... or a reply.
				else
				{
					echo '@' . number_format($_GET['quote_reply']) . "\n\n";
					
					$res=DB::Execute('SELECT body FROM {P}Replies WHERE id = '.$_GET['quote_reply']);
				}
				
				// Execute it.
				list($quoted_text)=$res->FetchRow();
				
				// Snip citations from quote.
				$quoted_text = trim( preg_replace('/^@([0-9,]+|OP)/m', '', $quoted_text) );
				
				//Prefix newlines with >
				$quoted_text = preg_replace('/^/m', '> ', $quoted_text);
				
				echo sanitize_for_textarea($quoted_text) . "\n\n";
			}
			
			// If we're just citing, print the citation.
			else if(ctype_digit($_GET['cite']))
			{
				echo '@' . number_format($_GET['cite']) . "\n\n";
			}
			
			echo '</textarea>';
			
			if(ALLOW_IMAGES && ! $editing)
			{
				echo '<label for="image" class="noscreen">Image</label> <input type="file" name="image" id="image" />';
			}
			?>
			
			<p><a href="/markup_syntax">Markup syntax</a>: <kbd>''</kbd> on each side of a word or part of text = <em>emphasis</em>. <kbd>'''</kbd> = <strong>strong emphasis</strong>. <kbd>></kbd> on the beginning of a line = quote. To mass quote a long section of text, surround it with <kbd>[quote]</kbd> tags. <abbr>URL</abbr>s are automatically linkified.  To display code, surround it in <kbd>[code]</kbd> tags.</p>
		</div>
		
		<?php 
		if( ! $watching_topic) 
		{ 	
			echo '<div class="row"><label for="watch_topic" class="inline">Watch topic</label> <input type="checkbox" name="watch_topic" id="watch_topic" class="inline"';
			if($_POST['watch_topic'])
			{
				echo ' checked="checked"';
			}
			echo ' /></div>';
		}
		if($administrator && ! $editing)
		{
			echo '<div class="row"><label for="admin" class="inline">Post as admin</label> <input type="checkbox" name="admin" id="admin" class="inline"></div>';
		}
		?>
			
		
		<div class="row">
			<input type="submit" name="preview" tabindex="3" value="Preview" class="inline"<?php if(ALLOW_IMAGES) echo ' onclick="document.getElementById(\'image\').value=\'\'"' ?> /> 
			<input type="submit" name="post" tabindex="4" value="<?php echo ($editing) ? 'Update' : 'Post' ?>" class="inline">
		</div>
	</form>
</div>

<?php

// If citing, fetch and display the reply in question.
if(ctype_digit($_GET['cite']))
{
	$res=DB::Execute('SELECT body, poster_number FROM {P}Replies WHERE id = '.$_GET['cite']);
	list($cited_text, $poster_number)=$res->FetchRow();
	
	if( ! empty($cited_text))
	{
		$cited_text = parse($cited_text);
	
		// Linkify citations within the text.
		preg_match_all('/^@([0-9,]+)/m', $cited_text, $matches);
		foreach($matches[0] as $formatted_id)
		{
			$pure_id = str_replace( array('@', ',') , '', $formatted_id);

			$cited_text = str_replace($formatted_id, '<a href="/topic/' . $_GET['reply'] . '#reply_' . $pure_id . '" class="unimportant">' . $formatted_id . '</a>', $cited_text);
		}
		
		// And output it!
		echo '<h3 id="replying_to">Replying to Anonymous ' . AnonySeqID($poster_number) . '&hellip;</h3> <div class="body standalone">' . $cited_text . '</div>';
	}
}
// If we're not citing or quoting, display the original post.
else if($reply && ! isset($_GET['quote_topic']) && ! isset($_GET['quote_reply']) && ! $editing)
{
	$res=DB::Execute('SELECT body FROM {P}Topics WHERE id = '.$_GET['reply']);
	
	list($cited_text)=$res->FetchRow();
		
	echo '<h3 id="replying_to">Original post</h3> <div class="body standalone">' . parse($cited_text) . '</div>';
}

require('includes/footer.php');

?>
