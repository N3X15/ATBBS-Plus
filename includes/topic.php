<?php

define('REPLY_EDIT_BY_MOD',	1);
define('REPLY_EDIT_BY_USER',	2);
define('REPLY_DELETED',		4); // U.S. data retention laws suck.
define('REPLY_AUTHOR_BANNED',	8);
define('REPLY_ADMIN_POST',	16);
define('REPLY_MOD_POST',	32);

class Topic
{
	public $ID;
	public $Time;
	public $AuthorID;
	public $AuthorName;
	public $Visits;
	public $ReplyCount;
	public $Headline;
	public $Body;
	public $EditTime;
	public $Flags;
	public $FileName=null;

	public $Replies=array();

	// UID => array(Names)
	public $UIDs=array();

	function Topic($id=-1)
	{
		if($id==-1) return;
		$this->ID=intval($id);
		$this->Load();
	}

	function Load()
	{
		$sql='';
		if(ALLOW_IMAGES)
		{
			$sql='SELECT t.time, t.author, t.name, t.visits, t.replies, t.headline, t.body, t.edit_time, t.flags, i.file_name FROM {P}Topics AS t LEFT OUTER JOIN {P}Images AS i ON t.id = i.topic_id WHERE t.id = '.$this->ID;
		} else {
			$sql='SELECT time, author, name, visits, replies, headline, body, edit_time, flags, NULL FROM {P}Topics WHERE id = '.$this->ID;
		}

		$res=DB::Execute($sql);
		
		list(
			$this->Time,
			$this->AuthorID,
			$this->AuthorName,
			$this->Visits,
			$this->ReplyCount,
			$this->Headline,
			$this->Body,
			$this->EditTime,
			$this->Flags,
			$this->FileName
		)=$res->FetchRow();

		$this->UIDs=array();
		$this->Replies=array();
		
		$uid=$this->AuthorID;

		if(!array_key_exists($uid,$this->UIDs))
			$this->UIDs[$uid] = array('Anonymous '.AnonySeqID(count($this->UIDs)));

		if(empty($this->AuthorName))
			$this->AuthorName=$this->UIDs[$uid][0];
		else if($this->AuthorID=='admin')
			$this->AuthorName='Anonymous';
		else if(!in_array($this->AuthorName,$this->UIDs[$uid]))
			$this->UIDs[$uid][]=$this->AuthorName;
	}

	public function Parse()
	{

		if($_COOKIE['ostrich_mode'] == 1)
		{
			foreach($User->IgnoreList as $ignored_phrase)
			{
				if(stripos($reply_body, $ignored_phrase) !== false)
				{
					$this->Ignored=true;
					$this->ParsedBody='[This post has matched a phrase on your ignore list and is therefore hidden.]';
					return;
				}
			}
		}
		$this->ParsedBody = parse($this->Body);
	}


	public function GetName($reply,$replyID)
	{
		global $User;

		if($replyID=='OP') 
			return ($this->AuthorID==$User->ID) ? 'you' : $this->AuthorName;

		if(	 ! array_key_exists($replyID,$this->Replies) 		// Inexistant
			|| $this->Replies[$replyID]->Flags&REPLY_DELETED 	// Deleted
			|| $User->IsIgnored($this->Replies[$replyID]->Body))	// Hidden
			return 'citing a deleted, hidden, or inexistant reply';

		return ($this->Replies[$replyID]->AuthorID==$User->ID) ? 'you' : fmtTripcode($this->Replies[$replyID]->AuthorName);
	}

	public function PrintAuthorName()
	{
			if(isPowerUser())
				OpenTag('a','href="/profile/' . $this->AuthorID . '"');
			OpenTag('strong');

			if(strpos($this->AuthorName,'#')===false)
				PrintText($this->AuthorName);
			else
			{
				$tripchunks=Tripcode($this->AuthorName);
				PrintText(htmlentities($tripchunks[0]));
				OpenTag('span','class="tripcode"');
				PrintText('!'.htmlentities($tripchunks[1]));
				CloseTag();
			}
			CloseTag();
	
			if(isPowerUser())
				CloseTag();
		if(($this->Flags&REPLY_ADMIN_POST)==REPLY_ADMIN_POST || $this->AuthorID=='admin')
			echo '<span class="admin_name">##<a href="/'.ADMIN_NAME.'">'.ADMIN_NAME.'</a></span>';
		else if(($this->Flags&REPLY_MOD_POST)==REPLY_MOD_POST)
			echo '<span class="admin_name">##<a href="/'.MOD_NAME.'">'.MOD_NAME.'</a></span>';

			PrintText('&nbsp;');
	}

	function isEditable()
	{
		return	   ($this->AuthorID == $_SESSION['UID'] && TIME_TO_EDIT == 0) 
			|| ($this->AuthorID == $_SESSION['UID'] && ( $_SERVER['REQUEST_TIME'] - $this->Time < TIME_TO_EDIT ))
			|| isPowerUser();
	}

	function GetReplies()
	{
		$res=DB::Execute('SELECT r.id, r.time, r.author, r.name, r.poster_number, r.body, r.edit_time, r.flags, i.file_name FROM {P}Replies as r LEFT OUTER JOIN {P}Images as i ON r.id = i.reply_id WHERE r.parent_id = '.$this->ID.' ORDER BY id');
		
		$tuple = array
		(
			1 => 'double',
			2 => 'triple',
			3 => 'quadruple',
			4 => 'quintuple'
		);
		while($row=$res->FetchRow())
		{
			$reply = new Reply($this);
			list(
				$reply->ID,
				$reply->Time,
				$reply->AuthorID,
				$reply->AuthorName,
				$pn,			// TODO: Delete poster_number;  That shit's calculated automatically.
				$reply->Body,
				$reply->EditTime,
				$reply->Flags,
				$reply->FileName
			)=$row;

			$reply->Action='';
		
			//Protip:  If you're going to make a shortcut var, remember to populate that shortcut var :V
			$uid=$reply->AuthorID;
			
			if(!array_key_exists($uid,$this->UIDs))
			{
				$this->UIDs[$uid] = array('Anonymous '.AnonySeqID(count($this->UIDs)));
				$reply->Action='joined in and ';
			}
			if(empty($reply->AuthorName))
				$reply->AuthorName=$this->UIDs[$uid][0];
			else if(!in_array($reply->AuthorName,$this->UIDs[$uid]))
				$this->UIDs[$uid][]=$reply->AuthorName;

			// Check for samefagging.
			if($this->PreviousReply->AuthorID==$reply->AuthorID)
			{
				$reply->SFM=$this->PreviousReply->SFM+1;
				if($reply->SFM<4)
					$reply->Action=$tuple[$reply->SFM].'-posted';
				else
					$reply->Action='samefagged for the '.OrdSuffix($reply->SFM).' time with';
			}
			else 
			{
				$reply->SFM=0;
				$reply->Action='replied with';
			}

			$this->Replies[$reply->ID]=$reply;

			// DO NOT FUCKING CHANGE THE ORDER OF THESE.
			$this->Replies[$reply->ID]->Parse($this);

			$this->PreviousReply=$this->Replies[$reply->ID];
			// END DO NOT CHANGE ORDER
		}
	}

	function UpdatePostcounts()
	{
		DB::Execute("UPDATE {P}Topics SET replies=(SELECT COUNT(*) FROM {P}Replies WHERE parent_id={$this->ID}) WHERE id={$this->ID}");
	}
}
