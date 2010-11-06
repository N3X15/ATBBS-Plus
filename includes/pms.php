<?php
/* Personal Messaging Framework */

define('DELETED_KEEP_TIME',30); // 30 days in-database before deletion

define('PMF_DELETED',1);

class PM
{
	public $ID=0;
	public $Thread=0;
	public $From=0;
	public $To=0;
	public $Title='';
	public $Body='';
	public $Flags=0;
	public $DateSent=0; // time()

	public function Load()
	{
		$res=DB::Execute("SELECT pmID,pmFrom,pmTo,pmTitle,pmBody,pmFlags,pmDateSent,pmThread FROM {P}PMs WHERE pmID={$this->ID}");
		list($this->ID,$this->From,$this->To,$this->Title,$this->Body,$this->Flags,$this->DateSent,$this->Thread)=$res->FetchRow();
	}

	public function Save()
	{
		$a=array(
			'pmFrom'	=> $this->From,
			'pmTo'		=> mysql_real_escape_string($this->To),
			'pmThread'	=> $this->Thread,
			'pmTitle'	=> mysql_real_escape_string($this->Title),
			'pmBody'	=> mysql_real_escape_string($this->Body),
			'pmFlags'	=> $this->Flags,
			'pmDateSent'	=> time()
		);
		DB::EasyInsert('{P}PMs',$a);
	}

	public function Delete()
	{
		DB::Execute("UPDATE {P}PMs SET pmFlags=pmFlags|".PMF_DELETED." WHERE pmID={$this->ID} LIMIT 1");
	}
}


function CheckPMs()
{
	global $User;
	$d=PMF_DELETED;
	DB::Execute("DELETE FROM {P}PMs WHERE (pmFlags&$d)=$d AND pmDateSent<".(time()-mktime(0,0,0,0,DELETED_KEEP_TIME)));
	$lrpm=intval($_SESSION['LastReadPMs']);
	$res=DB::Execute("SELECT pmDateSent FROM {P}PMs WHERE pmTo='{$User->ID}' AND pmDateSent>{$lrpm} ORDER BY pmID DESC LIMIT 1");
	if($res->RecordCount()>0)
	{
		$_SESSION['notice']=sprintf('You have <b><a href="/private_messages.php/list">%d unread private messages</a></b>.',$res->RecordCount());
	}
}


