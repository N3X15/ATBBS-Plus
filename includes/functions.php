<?php
/**
* ATBBS Functions
* 
* Copyright (c) 2009-2010 ATBBS Contributors
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

$errors = array();
$erred = false;

/* ==============================================
                                  USER FUNCTIONS
  ===============================================*/ 

function an($in)
{
	$vowels='aeiou';
	if(stristr(substr($in,0,1),$vowels)>-1)
		return 'an';
	else
		return 'a';
}
function CreateCookie($name,$value)
{
	setcookie($name,$value, $_SERVER['REQUEST_TIME'] + 315569260, '/');
}
function DeleteCookie($name)
{
	setcookie($name,'', $_SERVER['REQUEST_TIME'] - 315569260, '/');
}


/*** BAN-RELATED SHIT ***/
define('BANF_STEALTH',	1);
define('BANF_LIST',	2);
define('BANF_MARK',	4);
define('BANF_NO_READ',	8);
define('BANF_APPEAL_DENIED',	16);
function Check4Ban($posting=false)
{
	global $User;
	$uid=	Input::ToString($User->ID);
	$ip = 	$_SERVER['REMOTE_ADDR'];
	
	$res = DB::Execute("SELECT flags,reason,expiry,appeal FROM {P}Bans WHERE ip='$ip' OR uid='$uid'");
	if($res->RecordCount()==0) return; // NO BAN

	list($flags,$reason,$expiry,$appeal)=$res->FetchRow();

	$td=$expiry-$_SERVER['REQUEST_TIME'];
//	echo "TD: $td";
	if($td<0)
	{
		remove_id_ban($uid);
		remove_ip_ban($ip);
		return;
	}

	if(!empty($_POST['A_PEEL']) && !($flags&BANF_APPEAL_DENIED))
	{
		$appeal=$_POST['A_PEEL'];
		DB::Execute("UPDATE {P}Bans SET appeal=".DB::Q($appeal)." WHERE uid='$id' OR ip='$ip'");
		redirect('Appeal updated.','/');
	}
	if($flags&BANF_NO_READ || $posting)
	{
		if($flags&BANF_STEALTH) die(''); // WSoD

		Output::Assign('flags',$flags);
		Output::Assign('reason',$reason);
		Output::Assign('expiry',$expiry);
		Output::Assign('appeal',$appeal);
		Output::$tpl->display('banned.tpl.php');
		exit;
		$derp=1/0; // Just in case.
	}
}

function GetBanFromUID($uid)
{
	$res = DB::Execute("SELECT flags,reason,expiry,appeal FROM {P}Bans WHERE uid='$uid'");
	if($res->RecordCount()==0) return array(); // NO BAN

	$row=$res->FetchRow();
	list($flags,$reason,$expiry,$appeal)=$row;

	$td=$expiry-$_SERVER['REQUEST_TIME'];
//	echo "TD: $td";
	if($td<0)
	{
		remove_id_ban($uid);
		remove_ip_ban($ip);
		return array();
	}

	return $row;	
}
/* Handled by the User class now.
function create_id()
{
	global $User;

	$user_id = uniqid('', true);
	$password = generate_password();
	
	$stmt = DB::Prepare('INSERT INTO {P}Users (uid, password, ip_address, first_seen) VALUES (?, ?, ?, UNIX_TIMESTAMP())');
	DB::Execute($stmt,array($user_id, $password, $_SERVER['REMOTE_ADDR']));
	
	$_SESSION['first_seen'] = $_SERVER['REQUEST_TIME'];
	$_SESSION['notice'] = 'Welcome to <strong>' . SITE_TITLE . '</strong>. An account has automatically been created and assigned to you. You don\'t have to register or log in to use the board. Please don\'t clear your cookies unless you have <a href="/dashboard">set a memorable name and password</a>.';
	
	//setcookie('UID', $user_id, $_SERVER['REQUEST_TIME'] + 315569260, '/');
	//setcookie('password', $password, $_SERVER['REQUEST_TIME'] + 315569260, '/');
	$_SESSION['UID'] = $user_id;
}

function generate_password()
{
	$characters = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
	$password = '';

	for($i = 0; $i < 32; ++$i) 
	{
		$password .= $characters[array_rand($characters)];
	}
	return $password;
}

function activate_id()
{
	global $link;
	
	$sql=sprintf('SELECT password, first_seen FROM {P}Users WHERE uid = %s',DB::Q($_COOKIE['UID']));
	$res=DB::Execute($sql);

	list($db_password, $first_seen)=$res->fields;
	
	if( ! empty($db_password) && $_COOKIE['password'] === $db_password)
	{
		// The password is correct!
		$_SESSION['UID'] = $_COOKIE['UID'];
		// Our ID wasn't just created.
		$_SESSION['IDActivated'] = true;
		// For post.php
		$_SESSION['first_seen'] = $first_seen;
		
		return true;
	}
	
	// If the password was wrong, create a new ID.
	create_id();
}
*/
function force_id()
{
	if( ! isset($_SESSION['IDActivated']))
	{
		add_error('The page that you tried to access requires that you have a valid internal ID. This is supposed to be automatically created the first time you load a page here. Maybe you were linked directly to this page? Upon loading this page, assuming that you have cookies supported and enabled in your Web browser, you have been assigned a new ID. If you keep seeing this page, something is wrong with your setup; stop refusing/modifying/deleting cookies!', true);
	}
}

function update_activity($action_name, $action_id = '')
{
	if( ! isset($_SESSION['UID']))
	{
		return false;
	}
	
	$sql = DB::Prepare('INSERT INTO {P}Activity (time, uid, action_name, action_id) VALUES (UNIX_TIMESTAMP(), ?, ?, ?) ON DUPLICATE KEY UPDATE time = UNIX_TIMESTAMP(), action_name = ?, action_id = ?;');
	DB::Execute($sql, array($_SESSION['UID'], $action_name, $action_id, $action_name, $action_id));
	$_SESSION['MyLastActions'][$action_name]=time();
}

function Tripcode($post_name)
{
	// Name!Fag0Tt.l = array('Name','Fag0Tt.l');

	//Thanks for the salted tripcode script, Futallaby!
	if(preg_match("/(#|!)(.*)/",$post_name,$regs)){
		$cap = $regs[2];
		$cap = strtr($cap,"&amp;", "&");
		$cap = strtr($cap,"&#44;", ",");
		$name = preg_replace("/(#|!)(.*)/","",$post_name);
		$salt = substr($cap."H.",1,2);
		$salt = preg_replace("/[^\.-z]/",".",$salt);
		$salt = strtr($salt,":;<=>?@[\\]^_`","ABCDEFGabcdef"); 
		$tripcode = substr(crypt($cap,$salt),-10)."";
	} else {
		$name = $post_name;
	}

	return array($name,$tripcode);
}

function fmtTripcode($postname)
{
	$trip=tripcode($postname);
	$str=htmlentities($trip[0]);
	if(strlen($trip[1])>0)
		$str.='<span class="tripcode">!'.htmlentities($trip[1]).'</span>';
	return $str;
}
// Does the Anonymous A thing
// 0 = Anonymous A
// 1 = Anonymous B
// ...
// 25 = Anonymous Z
// 26 = Anonymous AA
// 27 = Anonymous AB
// ...
// 51 = Anonymous AZ
// 52 = Anonymous BA
function AnonySeqID($num)
{
	$num++;
	$anum = '';
        while( $num >= 1) {
            $num = $num - 1;
            $anum = chr(($num % 26)+65).$anum;
            $num = $num / 26;
        }
        return $anum;
}

function id_exists($id)
{
	global $link;

	$res=DB::Execute(sprintf('SELECT COUNT(*) FROM {P}Users WHERE uid = %s',DB::Q($id)));
	
	if($res->RecordCount() < 1)
	{
		return false;
	}
	return true;
}

function remove_id_ban($id)
{
	DB::Execute('DELETE FROM {P}Bans WHERE uid = '.DB::Q($id));
}

function remove_ip_ban($ip)
{
	DB::Execute('DELETE FROM {P}Bans WHERE ip = '.DB::Q($ip));
}

function fetch_ignore_list() // For ostrich mode. 
{
	if($_COOKIE['ostrich_mode'] == 1)
	{
		$ignored_phrases=DB::GetOne('SELECT ignored_phrases FROM {P}IgnoreLists WHERE uid = '.DB::Q($_COOKIE['UID']));
		// To make this work with Windows input, we need to strip out the return carriage.
		$ignored_phrases = explode("\n", str_replace("\r", '', $ignored_phrases));
		
		return $ignored_phrases;
	}
}

// for 3rd, 4th, etc
function OrdSuffix($n)
{
	$daySuffixLookup = array( 'th','st','nd','rd','th',
                           'th','th','th','th','th' );
    
	if($n % 100 >= 11 && $n % 100 <= 13)
		return $n.'th';
	return $daySuffixLookup[$n % 10];
}

// TODO: Refactor trash stuff so that  there's a flag on a post that marks it as trashed.
function show_trash($uid, $silence = false) // For profile and trash can.
{
	$output = '<table><thead><tr> <th class="minimal">Headline</th> <th>Body</th> <th class="minimal">Time since deletion ▼</th> </tr></thead> <tbody>';
	
	$trash=DB::GetAll('SELECT headline, body, time FROM {P}Trash WHERE uid = '.DB::Q($uid).' ORDER BY time DESC');
	
	$table = new TablePrinter('tblTrash');
	$columns = array
	(
		'Headline',
		'Body',
		'Time since deletion ▼'
	);
	$table->DefineColumns($columns, 'Body');

	foreach($trash as $row)
	{
		if(empty($row['headline']))
		{
			$row['headline'] = '<span class="unimportant">(Reply.)</span>';
		}
		else
		{
			$row['headline'] = htmlspecialchars($row['headline']);
		}
	
		$values = array 
		(
			$row['headline'],
			nl2br(htmlspecialchars($row['body'])),
			'<span class="help" title="' . format_date($row['time']) . '">' . calculate_age($row['time']) . '</span>'
		);
								
		$table->Row($values);
	}
	
	if($table->num_rows_fetched === 0)
	{
		return false;
	}
	return $table;
}

/* ==============================================
                                         OUTPUT
  ===============================================*/ 

// Prettify dynamic mark-up
function indent($num_tabs = 1)
{
	return "\n" . str_repeat("\t", $num_tabs);
}

// Just screwing around with this;  I doubt it'll really be used.
$IndentLevel=0;
$OpenTags=array();
function OpenTag($tag, $attr='')
{
	global $IndentLevel,$OpenTags;
	if(strlen($attr)>0) 
		$attr=" $attr";
	$OpenTags[]=$tag;
	echo sprintf("\n%s<%s%s>",str_repeat("\t", $IndentLevel),$tag,$attr);
	$IndentLevel++;
}

function PrintText($t)
{
	global $IndentLevel,$OpenTags;
	echo sprintf("\n%s%s",str_repeat("\t", $IndentLevel),$t);
}

function SCTag($tag, $attr='')
{
	global $IndentLevel,$OpenTags;
	if(strlen($attr)>0) 
		$attr=" $attr";
	echo sprintf("\n%s<%s%s />",str_repeat("\t", $IndentLevel),$tag,$attr);
}

function CloseTag()
{
	global $IndentLevel,$OpenTags;
	$IndentLevel--;
	$tag=array_pop($OpenTags);
	echo sprintf("\n%s</%s>",str_repeat("\t", $IndentLevel),$tag);
}

// Print a <table>. 100 rows takes ~0.0035 seconds on my computer.
/*
class table
{
	public $num_rows_fetched = 0;
	
	private $output = '';
	
	private $primary_key;
	private $columns = array();
	private $td_classes = array();
	
	private $marker_printed = false;
	private $last_seen = false;
	private $order_time = false;
	
	public function define_columns($all_columns, $primary_column)
	{
		$this->columns = $all_columns;
	
		$this->output .= '<table>' . indent() . '<thead>' . indent(2) . '<tr>';
		
		foreach($all_columns as $key => $column)
		{
			$this->output .=   indent(3) . ' <th';
			if($column != $primary_column)
			{
				$this->output .= ' class="minimal"';
			}
			else
			{
				$this->primary_key = $key;
			}
			$this->output .=  '>' . $column . '</th>';
		}
		
		$this->output .=  indent(2) . '</tr>' . indent() . '</thead>' . indent() . '<tbody>';
	}
	
	public function add_td_class($column_name, $class)
	{
		$this->td_classes[$column_name] = $class;
	}
	
	public function last_seen_marker($last_seen, $order_time)
	{
		$this->last_seen = $last_seen;
		$this->order_time = $order_time;
	}
	
	public function row($values)
	{
		// Print <tr>
		$this->output .=  indent(2) . '<tr';
		if($this->num_rows_fetched & 1) 
		{
			$this->output .=  ' class="odd"';
		}
		// Print the last seen marker.
		if($this->last_seen && ! $this->marker_printed && $this->order_time <= $this->last_seen)
		{
			$this->marker_printed = true;
			if($this->num_rows_fetched != 0)
			{
				$this->output .=  ' id="last_seen_marker"';
			}
		}
		$this->output .=  '>';
		
		// Print each <td>
		foreach($values as $key => $value)
		{
			$classes = array();
		
			$this->output .=  indent(3) . '<td';
			
			// If this isn't the primary column (as set in define_columns()), its length should be minimal.
			if($key !== $this->primary_key)
			{
				$classes[] = 'minimal';
			}
			// Check if a class has been added via add_td_class.
			if( isset( $this->td_classes[ $this->columns[$key] ] ) )
			{
				$classes[] = $this->td_classes[$this->columns[$key]];
			}
			// Print any classes added by the above two conditionals.
			if( ! empty($classes))
			{
				$this->output .=  ' class="' . implode(' ', $classes) . '"';
			}
			
			$this->output .=  '>' . $value . '</td>';
		}
		
		$this->output .=  indent(2) . '</tr>';
		
		$this->num_rows_fetched++;
	}
	
	public function output($items = 'items', $silence = false)
	{
		$this->output .=  indent() . '</tbody>' . "\n" . '</table>' . "\n";
		
		if($this->num_rows_fetched > 0)
		{
			return $this->output;
		}
		else if( ! $silence)
		{
			return '<p>(No ' . $items . ' to show.)</p>';
		}
		
		// Silence.
		return '';
	}
}
*/
  
function add_error($message, $critical = false)  
{
	global $errors, $erred;
	
	$errors[] = $message;
	$erred = true;
	
	if($critical)
	{
		Output::HardError($message);
	}
}

function print_errors($critical = false) 
{
	global $errors;
	
	$number_errors = count($errors);
	
	if($number_errors > 0) 
	{
		echo '<h3 id="error">';
			if($number_errors > 1)
			{
				echo $number_errors . ' errors';
			}
			else 
			{
				echo 'Error';
			}
		echo '</h3><ul class="body standalone">';
		
		foreach($errors as $error_message) 
		{
			echo '<li>' . $error_message . '</li>';
		}
		
		echo '</ul>';
		
		if($critical) 
		{
			if( ! isset($page_title))
			{
				$page_title = 'Fatal error';
			}
			require('footer.php');
			exit;
		}
	}
}

function page_navigation($section_name, $current_page, $num_items_fetched)
{
	$output = '';
	if($current_page != 1)
	{
		$output .= indent() . '<li><a href="/' . $section_name . '">Latest</a></li>';
	}
	if($current_page != 1 && $current_page != 2)
	{
		$newer = $current_page - 1;
		$output .= indent() . '<li><a href="/' . $section_name . '/' . $newer . '">Newer</a></li>';
	}
	if($num_items_fetched == ITEMS_PER_PAGE)
	{
		$older = $current_page + 1;
		$output .= indent() . '<li><a href="/' . $section_name . '/' . $older . '">Older</a></li>';
	}
	
	if( ! empty($output))
	{
		echo "\n" . '<ul class="menu">' . $output . "\n" . '</ul>' . "\n";
	}
}

function edited_message($original_time, $edit_time, $edit_mod)
{
	if($edit_time)
	{
		echo '<p class="unimportant">(Edited ' . calculate_age($original_time, $edit_time) . ' later';
		if($edit_mod)
		{
			echo ' by a moderator';
		}
		echo '.)</p>';
	}
}

function dummy_form()
{
	echo "\n" . '<form id="dummy_form" class="noscreen" action="" method="post">' . indent() . '<div> <input type="hidden" name="some_var" value="" /> </div>' . "\n" . '</form>' . "\n";
}

// To redirect to index, use redirect($notice, ''). To redirect back to referrer, 
// use redirect($notice). To redirect to /topic/1,  use redirect($notice, 'topic/1')
function redirect($notice = '', $location = NULL)
{
	if( ! empty($notice))
	{
		$_SESSION['notice'] = $notice;
	}
	
	if( ! is_null($location) || empty($_SERVER['HTTP_REFERER']))
	{
		$location = rel2Abs($location);
	}
	else
	{
		$location = $_SERVER['HTTP_REFERER'];
	}
	
	header('Location: ' . $location);
	exit;
}

// Unused
function regenerate_config()
{
	global $link;

	$output = '<?php' . "\n\n" . '#### DO NOT EDIT THIS FILE. ####' . "\n\n";
	
	$result = $link->query('SELECT `option`, `value` FROM configuration');
	while( $row = $result->fetch_assoc() ) 
	{
		if( ! ctype_digit($row['value']))
		{
			$row['value'] = "'" . $row['value'] . "'";
		}
		$output .= "define('" . strtoupper($row['option']) . "', " . $row['value'] . ");\n";
	}
	$result->close();
	
	$output .= "\n" . '?>';
	
	file_put_contents('cache/config.php', $output, LOCK_EX);
}

/* ==============================================
                                  CHECKING
  ===============================================*/ 

function check_length($text, $name, $min_length, $max_length)
{
	$text_length = strlen($text);

	if($min_length > 0 && empty($text))
	{
		Output::HardError('The ' . $name . ' cannot be blank.');
	}
	else if($text_length > $max_length)
	{
		Output::HardError('The ' . $name . ' was ' . number_format($text_length - $max_length) . ' characters over the limit (' . number_format($max_length) . ').');
	}
	else if($text_length < $min_length) 
	{
		Output::HardError('The ' . $name . ' was too short.');
	}
}

function check_tor($ip_address) //query TorDNSEL
{
	// Reverse the octets of our IP address.
	$ip_address = implode('.', array_reverse( explode('.', $ip_address) ));
	
	 // Returns true if Tor, false if not. 80.208.77.188.166 is of no significance.
	return checkdnsrr($ip_address . '.80.208.77.188.166.ip-port.exitlist.torproject.org', 'A');
}

// Prevent cross-site redirection forgeries.
function csrf_token()
{
	if( ! isset($_SESSION['token']))
	{
		$_SESSION['token'] = md5(SALT . mt_rand());
	}
	echo '<div class="noscreen"> <input type="hidden" name="CSRF_token" value="' . $_SESSION['token'] . '" /> </div>' . "\n";
}

function check_token()
{
	if($_POST['CSRF_token'] !== $_SESSION['token'])
	{
		add_error('Session error. Try again.');
		return false;
	}
	return true;
}

/* ==============================================
                                  FORMATTING
  ===============================================*/ 
  
function parse($text)
{
	$text = htmlspecialchars($text);
	$text = str_replace("\r", '', $text);

	$inlist=false;
	$otxt='';
	$tag='';
	foreach(explode("\n",$text) as $line)
	{
		$line=trim($line);
		//echo substr($line,0,1);
		switch(substr($line,0,1))
		{
			case '#':
				if(!$inlist)
				{
					$inlist=true;
					$tag='ol';
					$otxt.='<ol><li>'.substr($line,1).'</li>';
				} else $otxt.='<li>'.substr($line,1).'</li>';
				break;
			case '*':
				if(!$inlist)
				{
					$inlist=true;
					$tag='ul';
					$otxt.='<ul><li>'.substr($line,1).'</li>';
				} else $otxt.='<li>'.substr($line,1).'</li>';
				break;
			default:
				if($inlist===true)
				{
					$otxt.='</'.$tag.">\n";
					$inlist=false;
				}
				if(strlen($line)>0) $otxt.="\n".$line;
		}
	}
	if($inlist)
		$otxt.='</'.$tag.">\n";
	
	$markup = array 
	( 
		// Strong emphasis.
		"/'''(.+?)'''/",
		// Emphasis.
		"/''(.+?)''/",
		// Linkify URLs.
		'@\b(?<!\[)(https?|ftp)://(www\.)?([A-Z0-9.-]+)(/)?([A-Z0-9/&#+%~=_|?.,!:;-]*[A-Z0-9/&#+%=~_|])?@i',
		// Linkify text in the form of [http://example.org text]
		'@\[(https?|ftp)://([A-Z0-9/&#+%~=_|?.,!:;-]+) (.+?)\]@i',
		// Quotes.
		'/^&gt;(.+)/m',
		// Headers.
		'/^==(.+?)==/m',
		// Box quotes
		'/\[\[(.+)\]\]/s'
	);
	
	$html   = array 
	(
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<a href="$0">$1://$2<strong>$3</strong>$4$5</a>',
		'<a href="$1://$2">$3</a>',
		'<span class="quote"><strong>&gt;</strong> $1</span>',
		'<h4 class="user">$1</h4>',
		'<div class="boxquote">\1</div>'
	);
	
	$text = preg_replace($markup, $html, $otxt);
	$text = preg_replace_callback('/\[code\](.+?)\[\/code\]/s','OutputWithLineNumbers',$text);

	return str_replace('<br>','<br />',nl2br($text));
}

function snippet($text, $snippet_length = 80)
{
	$patterns     = array
	(
		"/'''?(.*?)'''?/", // strip formatting
		"/\[code\](.+)\[\/code\]/s",
		'/^(@|>)(.*)/m' //replace quotes and citations
	);
	
	$replacements = array
	(
		'$1',
		'$1',
		' ~ '
	);
	
	$text = preg_replace($patterns, $replacements, $text); 
	$text = str_replace( array("\r", "\n"), ' ', $text ); // strip line breaks
	$text = htmlspecialchars($text);
	
	if(ctype_digit($_COOKIE['snippet_length']))
	{
		$snippet_length = $_COOKIE['snippet_length'];
	}
	if(strlen($text) > $snippet_length)
	{
		$text = substr($text, 0, $snippet_length) . '&hellip;';
	}
	return $text;
}

function super_trim($text)
{
	// Strip return carriage and non-printing characters.
	$nonprinting_characters = array
	(
		"\r",
		'­', //soft hyphen ( U+00AD)
		'﻿', // zero width no-break space ( U+FEFF)
		'​', // zero width space (U+200B)
		'‍', // zero width joiner (U+200D)
		'‌' // zero width non-joiner (U+200C)
	);
	$text = str_replace($nonprinting_characters, '', $text);
	 //Trim and kill excessive newlines (maximum of 3)
	return preg_replace( '/(\r?\n[ \t]*){3,}/', "\n\n\n", trim($text) );
}

function sanitize_for_textarea($text)
{
	$text = str_ireplace('/textarea', '&#47;textarea', $text);
	$text = str_replace('<!--', '&lt;!--', $text);
	return $text;
}

function calculate_age($timestamp, $comparison = '')
{
	$units = array(
					'second' => 60,
					'minute' => 60,
					'hour' => 24,
					'day' => 7,
					'week' => 4.25, // FUCK YOU GREGORIAN CALENDAR
					'month' => 12
					);
	
	if(empty($comparison))
	{
		$comparison = $_SERVER['REQUEST_TIME'];
	}
	$age_current_unit = abs($comparison - $timestamp);
	foreach($units as $unit => $max_current_unit) 
	{
		$age_next_unit = $age_current_unit / $max_current_unit;
		if($age_next_unit < 1) // are there enough of the current unit to make one of the next unit?
		{
			$age_current_unit = floor($age_current_unit);
			$formatted_age = $age_current_unit . ' ' . $unit;
			return $formatted_age . ($age_current_unit == 1 ? '' : 's');
		}
		$age_current_unit = $age_next_unit;
	}

	$age_current_unit = round($age_current_unit, 1);
	$formatted_age = $age_current_unit . ' year';
	return $formatted_age . (floor($age_current_unit) == 1 ? '' : 's');
	
}

function format_date($timestamp)
{
	return date('Y-m-d H:i:s \U\T\C — l \t\h\e jS \o\f F Y, g:i A', $timestamp);
}

function format_number($number)
{
	if($number == 0)
	{
		return '-';
	}
	return number_format($number);
}

function number_to_letter($number)
{
	$alphabet = range('A', 'Y');
	if($number < 24)
	{
		return $alphabet[$number];
	}
	$number = $number - 23;
	return 'Z-' . $number;
}

function replies($topic_id, $topic_replies)
{
	global $visited_topics;
		
	$output = '';
	if( ! isset($visited_topics[$topic_id]))
	{
		$output = '<strong>';
	}
	$output .= format_number($topic_replies);
	
	if( ! isset($visited_topics[$topic_id]))
	{
		$output .= '</strong>';
	}
	else if($visited_topics[$topic_id] < $topic_replies)
	{
		$output .= ' (<a href="/topic/' . $topic_id . '#new">';
		$new_replies = $topic_replies - $visited_topics[$topic_id];
		if($new_replies != $topic_replies)
		{
			$output .= '<strong>' . $new_replies . '</strong> ';
		}
		else
		{
			$output .= 'all-';
		}
		$output .= 'new</a>)';
	}
		
	return $output;
}

function thumbnail($source, $dest_name, $type)
{
	switch($type)
	{
		case 'jpg':
			$image = imagecreatefromjpeg($source);
		break;
									
		case 'gif':
			$image = imagecreatefromgif($source);
		break;
									
		case 'png':
			$image = imagecreatefrompng($source);
	}
		
	$width = imagesx($image);
	$height = imagesy($image);
	
	if($width > MAX_IMAGE_DIMENSIONS || $height > MAX_IMAGE_DIMENSIONS)
	{
		$percent = MAX_IMAGE_DIMENSIONS / ( ($width > $height) ? $width : $height );
								
		$new_width = $width * $percent;
		$new_height = $height * $percent;
								
		$thumbnail = imagecreatetruecolor($new_width, $new_height) ; 
		imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	}
	else
	{
		$thumbnail = $image;
	}
							
	switch($type)
	{
		case 'jpg':
			imagejpeg($thumbnail, 'thumbs/' . $dest_name, 70);
		break;
								
		case 'gif':
			imagegif($thumbnail, 'thumbs/' . $dest_name);
		break;
								
		case 'png':
			imagepng($thumbnail, 'thumbs/' . $dest_name);
	}
							
	imagedestroy($thumbnail);
	imagedestroy($image);
}



function DeleteReplies($replies)
{
	$t=array();
	$i=0;
	$sql=$tsql='';
	foreach($replies as $reply)
	{
		if($i>0)
		{
			$tsql.=' OR ';
			$sql.=' OR ';
		}
		$sql.="id=".intval($reply);
		$tsql.="r.id=".intval($reply);
		$i++;
	}

	$res=DB::Execute("SELECT parent_id FROM {P}Replies WHERE $sql");
	while(list($id)=$res->FetchRow())
	{
		$t[$id]=intval($t[$id])+1;
	}

	// Move record to user's trash.
	DB::Execute('INSERT INTO {P}Trash (uid, body, time) SELECT r.author, r.body, UNIX_TIMESTAMP() FROM {P}Replies as r WHERE '.$tsql);

	// And delete it from the main table.
	DB::Execute('DELETE FROM {P}Replies WHERE '.$sql);

	foreach($t as $topic=>$count)
	{
		// Reduce the parent's reply count.
		DB::Execute('UPDATE {P}Topics SET replies = replies - '.($count-1).' WHERE id = '.$topic);
	}
	return $i;
}

function DeleteTopics($topics)
{
	$sql='';
	$i=0;
	foreach($topics as $reply)
	{
		if($i>0)
		{
			$tsql.=' OR ';
			$gsql.=' OR ';
			$gsql_r.=' OR ';
			$sql.=' OR ';
		}
		$sql.="id=".intval($reply);
		$tsql.="parent_id=".intval($reply);
		$gsql.="topics.id=".intval($reply);
		$gsql_r.="r.parent_id=".intval($reply);
		$i++;
	}

	// Move record to user's trash.
	DB::Execute('INSERT INTO {P}Trash (uid, headline, body, time) SELECT topics.author, topics.headline, topics.body, UNIX_TIMESTAMP() FROM {P}Topics as topics WHERE '.$gsql);
	
	DB::Execute('INSERT INTO {P}Trash (uid, body, time) SELECT r.author, r.body, UNIX_TIMESTAMP() FROM {P}Replies as r WHERE '.$gsql_r);

	DB::Execute("DELETE FROM {P}Topics WHERE $sql");
	DB::Execute("DELETE FROM {P}Replies WHERE $tsql");
	return $i;
}

// Turns relative links (/stuff) into absolutes (http://bbs.nexisonline.net/stuff)
function rel2Abs($in)
{
	return str_replace(THISURL.'/',THISURL,THISURL.$in);
}

if(!function_exists('date_diff'))
{
	function date_diff($start, $end="NOW")
	{
			$sdate = strtotime($start);
			$edate = strtotime($end);

			$time = $edate - $sdate;
			if($time>=0 && $time<=59) {
					// Seconds
					$timeshift = $time.' seconds ';

			} elseif($time>=60 && $time<=3599) {
					// Minutes + Seconds
					$pmin = ($edate - $sdate) / 60;
					$premin = explode('.', $pmin);
				   
					$presec = $pmin-$premin[0];
					$sec = $presec*60;
				   
					$timeshift = $premin[0].' min '.round($sec,0).' sec ';

			} elseif($time>=3600 && $time<=86399) {
					// Hours + Minutes
					$phour = ($edate - $sdate) / 3600;
					$prehour = explode('.',$phour);
				   
					$premin = $phour-$prehour[0];
					$min = explode('.',$premin*60);
				   
					$presec = '0.'.$min[1];
					$sec = $presec*60;

					$timeshift = $prehour[0].' hrs '.$min[0].' min '.round($sec,0).' sec ';

			} elseif($time>=86400) {
					// Days + Hours + Minutes
					$pday = ($edate - $sdate) / 86400;
					$preday = explode('.',$pday);

					$phour = $pday-$preday[0];
					$prehour = explode('.',$phour*24);

					$premin = ($phour*24)-$prehour[0];
					$min = explode('.',$premin*60);
				   
					$presec = '0.'.$min[1];
					$sec = $presec*60;
				   
					$timeshift = $preday[0].' days '.$prehour[0].' hrs '.$min[0].' min '.round($sec,0).' sec ';

			}
			return $timeshift;
	}
}

function getAvailableThemes()
{
	$t=array();
	foreach(glob('_templates/*/theme.txt') as $file)
	{
		$name='';
		$folders=explode('/',$file.'');
		$folder=$folders[1];
		$author='';
		foreach(file($file) as $line)
		{
			$lc = explode(' ',$line);
			switch($lc[0])
			{
				case '@name':
					$name=trim(str_replace('@name','',$line));
					break;
				case '@author':
					$author=trim(str_replace('@author','',$line));
					break;
			}
		}
		$t[$folder]=$name.' by '.$author;
	}
	return $t;
}
