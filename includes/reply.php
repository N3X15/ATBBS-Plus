<?php

class Reply
{
	public $ID;
	public $Time;
	public $AuthorID;
	public $AuthorName;
	public $Body;
	public $EditTime;
	public $Flags;
	public $FileName;

	private $Ignored=false;
	
	public function Reply()
	{
	}

	public function isHidden()
	{
		return $this->Ignored;
	}
	public function Parse($topic)
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
		$body = parse($this->Body);
	
		// Linkify citations. (This might be updated to use preg_replace_callback in the future.)
		preg_match_all('/^@([0-9,]+)/m', $body, $matches);
		foreach($matches[0] as $formatted_id)
		{
			$name = '';
	
			$pure_id = str_replace(array('@', ','), '', $formatted_id);
			if(!array_key_exists($pure_id, $topic->Replies))
			{
				$body = str_replace($formatted_id, '<span class="unimportant">(Citing a deleted or non-existent reply.)</span>', $body);
			}
			else if($topic->Replies[$pure_id]->isHidden())
			{
				$body = str_replace($formatted_id, '<span class="unimportant help" title="' . snippet($topic->Replies[$pure_id]->Body) . '">@hidden</span>', $body);
			}
			else
			{
				if($pure_id == $topic->PreviousReply->ID)
				{
					$link_text = '@previous';
				}
				else
				{
					$link_text = $formatted_id;
				}
			
				$body = str_replace($formatted_id, '<a href="#reply_' . $pure_id . '" onclick="highlightReply(\'' . $pure_id . '\'); createSnapbackLink(\'' . $reply_id . '\')" class="unimportant help" title="' . snippet($topic->Replies[$pure_id]->Body) . '">' . $link_text . '</a> <span class="unimportant citation">(' . $topic->GetName($this,$pure_id).')</span>', $body);
			}
		}
		$this->ParsedBody = preg_replace('/^@OP/', '<span class="unimportant">@OP</span>', $body);
	}

	function isEditable()
	{
		return	   ($this->AuthorID == $_SESSION['UID'] && TIME_TO_EDIT == 0) 
			|| ($this->AuthorID == $_SESSION['UID'] && ( $_SERVER['REQUEST_TIME'] - $this->Time < TIME_TO_EDIT ))
			|| isPowerUser();
	}

	public function PrintAuthorName()
	{

		if($this->AuthorID=='admin' && empty($this->AuthorName))
			$this->AuthorName='Anonymous';

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
}

