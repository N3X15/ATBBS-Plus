<?php

// Bitwise flags
define('PERMISSION_MOD',	1);	// 1st bit
define('PERMISSION_SYSOP',	2);	// 2nd bit

define('FLAG_SPOILER',		4);	// 3rd bit
define('FLAG_TOPICS',		8);	// 4th bit
define('FLAG_OSTRICH',		16);	// 5th bit

/**
* User Class
*
* Mostly handles user settings and crap.
*/
class User
{
	public $ID 		= '';
	public $Password 	= '';
	public $Flags		= 0; 
	public $BannedUntil	= -1;

	public $Username	= '';
	public $Email		= '';
	public $SnippetLength	= 80;

	public $Theme		= 'atbbs'; 	// Savant3 theme directory

	public $IgnoreList=array();

	function User($id=false)
	{
		if(!$id)
		{
			global $moderators,$administrators,$moderator,$administrator;
			if(!empty($_COOKIE['password']))
			{
				$p=$_COOKIE['password'];
				DeleteCookie('password');
				CreateCookie('Password',$p);
			}
			if(empty($_COOKIE['UID']))
			{
				$this->CreateID();
				$this->ID=$_SESSION['UID'];
				$this->Password=$_SESSION['Password'];
			} else if( ! empty($_COOKIE['Password'])) {
				// Log in those who have just began their session.
				if(!isset($_SESSION['IDActivated']))
				{
					$this->Activate();
				}
				$this->ID=$_SESSION['UID'];
				$this->Password=$_SESSION['Password'];
				$this->Load();
				// ...and check for mod/admin privileges from the cache.

				if(is_array($moderators) && (!$this->isMod() || !$this->isAdmin()))
				{
					if(in_array($_SESSION['UID'], $moderators))
					{
						$this->Flags|=PERMISSION_MOD;
					}
					else if(in_array($_SESSION['UID'], $administrators))
					{
						$this->Flags|=PERMISSION_SYSOP;
					}
					$this->Save();
				}
				if(ROOT_ADMIN==$_SESSION['UID'])
				{
					$this->Flags|=PERMISSION_SYSOP;
				}
				$_SESSION['Flags']=0;
			}
				$this->ID=$_SESSION['UID'];
				$this->Password=$_SESSION['Password'];
			$this->LoadReadTopics();
			$this->Level = ($this->isAdmin()) ? ADMIN_NAME : (($this->isMod())? 'Moderator' : 'User');
			$this->IgnoreList=fetch_ignore_list();
			$this->LoadActions();

		} else {
			$this->ID = $id;
			$this->Load();
		}
	}


	function LoadActions()
	{
		// Get most recent actions to see if there's anything new
		$this->LastActions = array();
		$res=DB::Execute('SELECT feature, time FROM {P}LastActions');
		if($res->RecordCount()>0)
		while(list($feature,$time)=$res->FetchRow())
		{
			$this->LastActions[$feature] = $time;
		}

		// Now load our last actions (If the cookie exists...)
		if(empty($_SESSION['MyLastActions']))
			$_SESSION['MyLastActions']=$this->LastActions;
	}

	function HasNewStuff($action)
	{
		if($this->LastActions[$action]>$_SESSION['MyLastActions'][$action])
			return true;
		return false;
	}

	function Load()
	{
		$sql="SELECT usrID, usrName, usrEmail, usrFlags, usrSnipLen, usrTheme FROM {P}UserSettings WHERE usrID='{$_SESSION['UID']}'";
		$res = DB::Execute($sql);
		if($res->RecordCount()==0) return;
		$wtf=$res->FetchRow();
		if($wtf[0]=='') 
		{
			DB::Execute("DELETE FROM {P}UserSettings WHERE usrID=''");
			return;
		}
		list($devnull,$this->UserName,$this->Email,$this->Flags,$this->SnippetLength,$this->Theme)=$wtf;
	}

	// CLEAN INPUT PRIOR TO USING THIS!
	function SetPassword($pass)
	{
		DB::Execute("UPDATE {P}UserSettings SET usrPasshash=SHA1(CONCAT(usrID,'{$pass}'))");
	}

	function Save()
	{
		$f=array(
			'usrID'=>$this->ID,
			'usrName'=>$this->UserName,
			'usrEmail'=>$this->Email,
			'usrFlags'=>$this->Flags,
			'usrSnipLen'=>$this->SnippetLength,
			'usrTheme'=>$this->Theme
		);
		DB::EasyInsert('{P}UserSettings',$f,true);
	}
	function CreateID()
	{
		$this->ID = uniqid('', true); // Maybe switch to UUIDs;  Easier to validate.
		$this->Password = $this->GenPass();

		$stmt = DB::Prepare('INSERT INTO {P}Users (uid, password, ip_address, first_seen) VALUES (?, ?, ?, UNIX_TIMESTAMP())');
		DB::Execute($stmt,array($this->ID, $this->Password, $_SERVER['REMOTE_ADDR']));

		$_SESSION['first_seen'] = $_SERVER['REQUEST_TIME'];
		$_SESSION['notice'] = 'Welcome to <strong>' . SITE_TITLE . '</strong>. An account has automatically been created and assigned to you. You don\'t have to register or log in to use the board. Please don\'t clear your cookies unless you have <a href="/dashboard">set a memorable name and password</a>.';

		//setcookie('UID', 	$this->ID, $_SERVER['REQUEST_TIME'] + 315569260, '/');
		//setcookie('password', 	$this->Password, $_SERVER['REQUEST_TIME'] + 315569260, '/');


		CreateCookie('UID',		$this->ID);
		CreateCookie('Password',	$this->Password);

		$_SESSION['UID'] = $this->ID;
	}

	function GenPass()
	{
		$characters = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
		$password = '';

		for($i = 0; $i < 32; ++$i) 
		{
			$password .= $characters[array_rand($characters)];
		}
		return $password;
	}

	function Activate()
	{
		$sql=sprintf('SELECT password, first_seen FROM {P}Users WHERE uid = %s',DB::Q($_COOKIE['UID']));
		$res=DB::Execute($sql);

		list($db_password, $first_seen)=$res->fields;
	
		if( ! empty($db_password) && $_COOKIE['Password'] === $db_password)
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
		$this->CreateID();
	}

	function LoadReadTopics()
	{
		// Get visited topics from cookie.
		$visited_cookie = explode('t', $_COOKIE['topic_visits']);
		$this->Visited = array();
		foreach($visited_cookie as $topic_info)
		{
			if(empty($topic_info))
			{
				continue;
			}
			list($cur_topic_id, $num_replies) = explode('n', $topic_info);
			$this->Visited[$cur_topic_id] = $num_replies;
		}
	}
	function ClearRead()
	{
		DeleteCookie('topic_visits');
		$this->Visited=array();
	}

	function TopicRead($id)
	{
		// Set visited cookie...
		$last_read_post = $visited_topics[$id];
		if($last_read_post !== $topic_replies)
		{
			// Build cookie.
			// Add the current topic:
			$visited_topics = array( $_GET['id'] => $topic_replies) + $visited_topics;
			// Readd old topics.
			foreach($visited_topics as $cur_topic_id => $num_replies)
			{
				// If the cookie is getting too long (4kb), stop.
				if(strlen($cookie_string) > 3900)
				{
					break;
				}
		
				$cookie_string .= 't' . $cur_topic_id . 'n' . $num_replies;
			}

			setcookie('topic_visits', $cookie_string, $_SERVER['REQUEST_TIME'] + 604800, '/');
		}
	}

	function IsIgnored($body)
	{
		if(count($this->IgnoreList)==0) return false;
		$f=false;
		foreach($this->IgnoreList as $ignore)
		{
			if(stripos($ignore,$body)!==false) $f=true;
		}
		return $f;
	}
	function isAdmin()
	{ 	
		return ($this->Flags&PERMISSION_SYSOP)==PERMISSION_SYSOP; 
	}
	function isMod() 
	{ 	
		return ($this->Flags&PERMISSION_MOD)==PERMISSION_MOD;
	}

	//Determine our level.
	// Yes, this is Mandatory Access Control.  Whee.
	function getMACLevel()
	{
		$level=0;
		if(ROOT_ADMIN==$this->ID) 	$level	= 999;
		else if($this->isAdmin()) 	$level	= 100;
		else if($this->isMod())		$level 	= 50;
		else				$level	= 0;
		return $level;
	}
}
