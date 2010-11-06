<?php
define('SET_TYPE_TEXT',		0);
define('SET_TYPE_TEXT_ML',	1);
define('SET_TYPE_INT', 		2);
define('SET_TYPE_BOOL',		3);
define('SET_TYPE_INTERNAL,	4); // Won't be displayed.


class Settings
{
	private static $Settings=array();
	private static $OldSettings=array();

	// To be used by the installer.
	public Setup($name,$category,$ns,$type,$desc,$default)
	{
		$f=array(
			'setName'=>$name,
			'setCategory'=>$category,
			'setNamespace'=>$ns,
			'setType'=>$type,
			'setDesc'=>$desc,
			'setDefault'=>$default
		);
		DB::EasyInsert('{P}Settings',$f,true);

		return sprintf('%s.%s.%s: Created with value %s.',$ns,$category,$name,$default);
	}

	public Load()
	{
		self::$Settings=array();
		$res=DB::Execute("SELECT CONCAT(setNamespace, '.', setCategory, '.', setName) as name, setType, setDesc, setDefault, setValue FROM {P}Settings");
		while(list($name,$type,$desc,$default,$value)=$res->FetchRow())
		{
			self::$Settings[$name]=array($type,$desc,$default,$value);
		}
		self::$OldSettings=self::$Settings;
	}

	public Save()
	{
		$sql="UPDATE {P}Settings SET setValue='%s' WHERE setNamespace='%s' AND setCategory='%s' AND setName='%s'";
		foreach(self::$Settings as $k=>$v)
		{
			if(self::$OldSettings[$k]!=$v)
			{
				if(count($nc)==3)
					DB::Execute(sprintf($sql,$v,$nc[0],$nc[1],$nc[2]));
			}
		}
	}
}
