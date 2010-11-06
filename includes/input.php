<?php

class Input
{
	private static $EmailRegex='^(([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+([;.](([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+)*$';
	
	static function CheckEmail($in)
	{
		$m=array();
		$r=(ereg(Input::$EmailRegex,$in,$m));
		//Output::DebugVar($m);
		return $r;
	}
	static function ToString($in)
	{
		return mysql_real_escape_string($in);
	}
	
	static function ToInt($in)
	{
		return intval($in);
	}
}
class Post
{
	static function GetDate($controlname)
	{
		$d=POST::GetInt($controlname.'_d');
		$m=POST::GetInt($controlname.'_m');
		$y=POST::GetInt($controlname.'_y');
		//echo __LINE__.":'$d,$m,$y'";
		return mktime(0,0,0,$m,$d,$y);
	}
	static function WasUsed()
	{
		return ($_SERVER['REQUEST_METHOD']=='POST');
	}
	static function FetchIndex($i)
	{
		if(!isset($_POST[$i])) return FALSE;
		return $_POST[$i];
	}
	
	static function IsString($i)
	{
		return (Post::FetchIndex($i)!=FALSE && strlen(Post::FetchIndex($i))>0);
	}
	
	static function GetEString($index,$allownull=false)
	{
		$r=Post::FetchIndex($index);
		if(!$r && !$allownull)
			Output::HardError(sprintf('Index %s unavailable.',$index));
		Output::CheckBuffer();
		return Input::ToString($r);
	}
	
	static function ToInt($i)
	{
		return (intval(Post::FetchIndex($i)));
	}
	static function GetInt($i)
	{
		return (intval(Post::FetchIndex($i)));
	}
	
	static function ToFloat($i)
	{
		return floatval(Post::FetchIndex($i));
	}
	
	static function Raw()
	{
		return file_get_contents("php://input");
	}
}

class Get
{
	static function GetDate($controlname)
	{
		$d=GET::GetInt($controlname.'_d');
		$m=GET::GetInt($controlname.'_m');
		$y=GET::GetInt($controlname.'_y');
		//echo __LINE__.":'$d,$m,$y'";
		return mktime(0,0,0,$m,$d,$y);
	}
	static function WasUsed()
	{
		return ($_SERVER['REQUEST_METHOD']=='POST'); // WIll probably always say true.
	}
	static function FetchIndex($i)
	{
		if(!isset($_GET[$i])) return FALSE;
		return $_GET[$i];
	}
	static function IsString($i)
	{
		return (GET::FetchIndex($i)!=FALSE && strlen(GET::FetchIndex($i))>0);
	}
	
	static function GetEString($index,$allownull=false)
	{
		$r=GET::FetchIndex($index);
		if(!$r && !$allownull)
			Output::HardError(sprintf('Index %s unavailable.',$index));
		Output::CheckBuffer();
		return Input::ToString($r);
	}
	
	static function ToInt($i)
	{
		return (intval(GET::FetchIndex($i)));
	}
	static function GetInt($i)
	{
		return (intval(GET::FetchIndex($i)));
	}
	
	static function ToFloat($i)
	{
		return floatval(GET::FetchIndex($i));
	}
}

class Path
{
	private static $Chunks=array();
	static function FetchIndex($i)
	{
		if(count(self::$Chunks)==0) 
			self::$Chunks=array_values(array_filter(explode('/',$_SERVER['PATH_INFO'])));
		if(empty(self::$Chunks[$i])) return FALSE;
		return self::$Chunks[$i];
	}
	static function IsString($i)
	{
		return (GET::FetchIndex($i)!=FALSE && strlen(GET::FetchIndex($i))>0);
	}
	
	static function GetEString($index,$allownull=false)
	{
		$r=GET::FetchIndex($index);
		if(!$r && !$allownull)
			Output::HardError(sprintf('Index %s unavailable.',$index));
		Output::CheckBuffer();
		return Input::ToString($r);
	}
	
	static function ToInt($i)
	{
		return (intval(GET::FetchIndex($i)));
	}
	static function GetInt($i)
	{
		return (intval(GET::FetchIndex($i)));
	}
	
	static function ToFloat($i)
	{
		return floatval(GET::FetchIndex($i));
	}
}
