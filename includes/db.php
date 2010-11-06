<?php
/**
* ADODB Interface Driver
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

/* Sanity check. */
if((ADODB_USER=='' or ADODB_PASSWORD=='') and !defined('INSTALLER'))
{
	Output::Error('Database setup is invalid.');
}
//include("adodb/adodb-exceptions.inc.php"); 
class DB
{
	public static $rdb; // True database connection (ADODB)
	public static $queries;
	public static $lastSQL;
	public static $Tables=array(
		'Activity',
		'Bans',
		'Bulletins',
		'FailedPostings',
		'Filters',
		'IgnoreLists',
		'Images',
		'LastActions',
		'Pages',
		'PMs',
		'Replies',
		'Topics',
		'Trash',
		'Users',
		'UserSettings',
		'Watchlists'
	);
	public static $TableVersionCache=array();
	public static function Connect()
	{
		//if(defined('INSTALLER')) return;
		$dsn=ADODB_DRIVER.'://'.ADODB_USER.':'.urlencode(ADODB_PASS).'@'.ADODB_HOST.'/'.ADODB_DB;
		//echo $dsn;
		try
		{
			DB::$rdb=&NewADOConnection($dsn);
		} catch(exception $e)
		{
			Output::HardError('<b>Database Connection Failure:</b><br />'.$e->gettrace());
		}

		if(!file_exists(THISDIR.'upgrade/sql/_vc.php'))
		{
			echo "Please wait, regenerating table version cache.";
			self::GenVC();
		}

		require(THISDIR.'upgrade/sql/_vc.php');

	}
	public static function Q($in)
	{
		return DB::$rdb->qstr($in);
	}
	public static function Prepare($sql)
	{
		if(!DB::$rdb->IsConnected())
			Output::HardError('<b>Database Connection Failure:</b><br />'.DB::$rdb->ErrorMsg());
		return DB::$rdb->Prepare($sql);
	}
	public static function Execute($sql,$params=false)
	{
		self::$queries++;
		self::$lastSQL=$sql;
		if(!DB::$rdb->IsConnected())
			Output::HardError('<b>Database Connection Failure:</b><br />'.DB::$rdb->ErrorMsg());
		$rs= &DB::$rdb->Execute(str_replace('{P}',ADODB_PREFIX,$sql),$params);
		if(!$rs)
			Output::HardError('<b>Database Query Failure:</b><br />'.DB::$rdb->ErrorMsg().'<br /><b>Last Query:</b><pre>'.self::$lastSQL.'</pre>');
		return $rs;
	}
	
	public static function TableExists($table)
	{
		self::$queries++;
		if(!DB::$rdb->IsConnected())
			Output::HardError('<b>Database Connection Failure:</b><br />'.DB::$rdb->ErrorMsg());
		//$table=ADODB_PREFIX.$table;
		$rs=DB::Execute("SHOW TABLES LIKE '{$table}'");
		return ($rs->RecordCount()>0);
	}
	
	public static function GetFields($table)
	{
		self::$queries++;
		$rs=self::Execute("DESCRIBE $table");
		
		$ret = array();
		foreach($rs as $row)
		{
			$ret[$row['Field']]=Field::FromRow($row);
		}
		
		return $ret;
	}
	
	public static function FieldExists($table,$fieldname)
	{
		$fields=self::GetFields($table);
		foreach($fields as $name=>$data)
		{
			if($name==$fieldname)
				return true;
		}
		return false;
	}
	
	public static function StartTransaction()
	{
		self::$queries++;
		self::$rdb->StartTrans();
	}
	
	public static function EndTransaction()
	{
		self::$queries++;
		self::$rdb->CompleteTrans();
	}
	
	public static function GetLastID()
	{
		return DB::$rdb->Insert_ID();
	}
	
	public static function GetOne($sql)
	{
		self::$queries++;
		self::$lastSQL=$sql;
		if(!DB::$rdb)
			Output::HardError('<b>Database Connection Failure.</b>');
		return DB::$rdb->GetOne(str_replace('{P}',ADODB_PREFIX,$sql));
	}
	
	public static function GetAll($sql)
	{
		self::$queries++;
		self::$lastSQL=$sql;
		if(!DB::$rdb)
			Output::HardError('<b>Database Connection Failure.</b>');
		return DB::$rdb->GetAll(str_replace('{P}',ADODB_PREFIX,$sql));
	}
	
	public static function EasyInsert($t,$a,$on_dup_update=false)
	{
		$sql="INSERT INTO $t SET ";
		foreach($a as $k=>$v)
		{
			$in[]="$k='$v'";
		}
		$sql.=implode(', ',$in);
		if($on_dup_update) 
		{
			$sql.=' ON DUPLICATE KEY UPDATE ';
			$ks=array();
			foreach($a as $k=>$v)
			{
				$ks[]="$k=VALUES($k)";
			}
			$sql.=implode(', ',$ks);
		}
		self::Execute(str_replace('{P}',ADODB_PREFIX,$sql));
	}
	public static function EasyUpdate($t,$a,$w='1')
	{
		$sql="UPDATE $t SET ";
		foreach($a as $k=>$v)
		{
			$in[]="$k='$v'";
		}
		$sql.=implode(', ',$in);
		$sql.=" WHERE $w";
		self::Execute(str_replace('{P}',ADODB_PREFIX,$sql));
	}
	
	public static function ToggleDebug()
	{
		self::$rdb->debug= !self::$rdb->debug;
	}
		
	function CheckForChanges($table)
	{
		if(!self::TableExists(ADODB_PREFIX.$table))
		{
			echo ("<li class=\"bad\">{$table} does not exist.</li>");
			return true;
		}
		$rev=self::GetOne("SELECT revision FROM ".ADODB_PREFIX."Migrations WHERE name='{$table}'");
		$ctrev=self::GetLatestTableRevision($table);
		if($ctrev>intval($rev))
		{
			$rev=intval($rev);
			echo ("<li class=\"bad\">{$table} needs update! ($ctrev > $rev)</li>");
			return true;
		}
		echo ("<li class=\"good\">{$table} is OK.</li>");
		return false;
	}

	function GetLatestTableRevision($table)
	{
		$updfiles=glob(THISDIR.'upgrade/sql/'.$table.'/*.php');
		$tablerev=0;
		foreach($updfiles as $tablefix)
		{
			$rev=intval(basename($tablefix,'.php'));
			if($rev>$tablerev)
				$tablerev=$rev;
		}
		return $tablerev;
	}

	// Version cache of the migrations system
	function GenVC()
	{
		$vc=array();
		foreach(self::$Tables as $t)
		{
			$vc[$t]=self::GetLatestTableRevision($t);
		}
		$vct=<<<EOF
<?php
/*******************************************
 AUTOMATICALLY GENERATED.  DO NOT HAND-EDIT.
 
  To Regenerate, delete this file (_vc.php)
 *******************************************/
//This file holds a cache of the latest table versions stored in this directory.
// ATBBS will then use this cache to determine if it requires an update.

EOF;
		$vct.='DB::$TableVersionCache='.var_export($vc,true).';';
		file_put_contents(THISDIR.'/upgrade/sql/_vc.php',$vct);
	}

	function NeedsUpgrade()
	{
		$res=DB::Execute("SELECT name,revision FROM {P}Migrations");
		$ourv=array();
		while(list($table,$rev)=$res->FetchRow())
		{
			$ourv[$table]=$rev;
		}
		foreach(self::$TableVersionCache as $table=>$rev)
		{
			if($ourv[$table]<$rev)
				return true;
		}
		return false;
	}
	function ApplyTableFixes($table)
	{
		self::$queries++;
		$maxrev = self::GetLatestTableRevision($table)+1;
		$minrev = (int)self::$rdb->GetOne("SELECT revision FROM ".ADODB_PREFIX."Migrations WHERE `name`='{$table}'");
		echo $minrev.' '.$maxrev;
		$c=0;
		for($i=$minrev+1;$i<$maxrev;$i++)
		{
			echo $i;
			// /var/www/localhost/htdocs/flexcp/upgrade/sql/Users/1.php
			require(THISDIR.'upgrade/sql/'.$table.'/'.$i.'.php');
			$c++;
		}
		return '<li class="good">'.$table.' is now at revision #'.($maxrev-1).' ('.$c.' fixes applied).</li>';
	}
	
	function SetTableRevision($table,$rev)
	{
		self::EasyInsert(ADODB_PREFIX.'Migrations',array('name'=>$table,'revision'=>$rev),true);
	}
}

class Field
{
	public $Name = '';
	public $Type = 'int';
	public $Size = 0;
	public $Unsigned = false;
	public $AllowNull = false;
	public $KeyType = '';
	public $Default = '';
	public $Extra = '';
	
	public function Field($name)
	{
		$this->Name=$name;
	}
	
	public static function FromRow($row)
	{
		$f = new Field('');
		foreach($row as $k=>$v)
		{
			switch(strtolower($k))
			{
				// MySQL fields
				case 'field':
					$f->Name=$v;
					break;
				case 'type':
					if(strpos('(',$v)>-1)
					{
						$ta=explode('(',$v);
						$f->Type=$ta[0];
						$f->Size=intval(str_replace(')','',$ta[1]));
					} else {
						$f->Type=$v;
						$f->Size=0;
					}
				break;
				case 'null':
					$f->AllowNull=($v=='YES');
					break;
				case 'key':
					$f->KeyType=$v;
					break;
				case 'default':
					$f->Default=$v;
					break;
				case 'extra':
					$f->Extra=$v;
					break;
			}
		}
		return $f;
	}
	
	public function _AlterSQL()
	{
		$sql=' '.$this->Type;
		if($this->Size>0)
			$sql.="({$this->Size})";
		if($this->Unsigned)
			$sql.=' UNSIGNED';
		if($this->AllowNull)
			$sql.=' NULL';
		else
			$sql.=' NOT NULL';
			
		$sql.=' '.$this->Extras;
		
		if($this->Default!='')
		{
			switch(strtolower($this->Type))
			{
				case 'int':
				case 'tinyint':
					$sql.=' DEFAULT {$this->Default}';
				break;
				case 'text':
				case 'blob':
					// Causes errors.
				break;
				default:
					$sql.=' DEFAULT \'{$this->Default}\'';
				break;
			}
		}
	}
}
