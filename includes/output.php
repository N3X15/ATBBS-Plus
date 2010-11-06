<?php
/**
* Savant3 Output Formatting
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

require_once('Savant3.php');
require_once('flexdate.php');

if(!defined('THEME'))
	define('THEME','atbbs');

class Output
{
	public static $messages=array();
	public static $cpage = "home";
	public static $theme_override = false;
	public static $tpl;
	public static $API=false;
	
	private static $_err_detected=false;
	
	static function DebugVar($var)
	{
		?><pre><?print_r($var);?></pre><?
	}
	
	static function CheckBuffer()
	{
		if(Output::$_err_detected)
			Output::Flush();
	}
	static function Redirect($newurl,$msg='')
	{
		header("Refresh:5,{$newurl}");
		Output::PrepSV3();
		self::$tpl->msg=$msg;
		self::$tpl->newurl=$newurl;
		self::$tpl->display('redirect.tpl.php');
		die('');
	}
	
	static function AddError($msg,$invalidfieldid='__')
	{
		Output::$_err_detected=true;
		Output::$messages[$invalidfieldid].="<li class=\"ErrorMessage\">$msg</li>";
	}
	
	static function AddWarning($msg,$invalidfieldid='__')
	{
		Output::$messages[$invalidfieldid].="<li class=\"WarningMessage\">$msg</li>";
	}
	
	static function AddMessage($msg,$invalidfieldid='__')
	{
		Output::$messages[$invalidfieldid].="<li class=\"InfoMessage\">$msg</li>";
	}
	
	static function GetMessages($fieldid='__')
	{
		return '<ul>'.Output::$messages[$fieldid].'</ul>';
	}
	static function HardError($err)
	{
		if(self::$API) die("ERROR "+$err);
		
		$tpl=new Savant3();
		$tpl->err=$err;
		$tpl->setPath('template',THISDIR.'/_templates/');
		$tpl->display('syserror.tpl.php');
		die('');
	}
	
	static function PrepSV3($newinstance=false)
	{
		global $User;
		$tpl=new Savant3();
		if(self::$theme_override)
			$tpl->setPath('template',THISDIR.'/_templates/'.self::$theme_override.'/');
		else if(!empty($User->Theme))
			$tpl->setPath('template',THISDIR.'/_templates/'.$User->Theme.'/');
		else
			$tpl->setPath('template',THISDIR.'/_templates/'.THEME.'/');
		
		
		if($newinstance)
		{
			return $tpl;
		}
		
		self::$tpl=$tpl;
		
	}
	
	static function Assign($var,$value)
	{
		self::$tpl->assign($var,$value);
	}
	
	static function Confirm($msg,$yesurl,$nourl)
	{
		Output::PrepSV3();
		self::$tpl->message=$msg;
		self::$tpl->yes=$yesurl;
		self::$tpl->no=$nourl;
		self::$tpl->display('confirm.tpl.php');
		die('');
	}
	static function Flush()
	{
		// Prep Savant3
		// 
		
		if(!isset(self::$tpl))
			Output::PrepSV3();
		if(defined('USING_NEW_TEMPLATE_FORMAT'))
		{		
			$mb='';
		
			$head=self::$tpl->fetch('gheader.tpl.php');
			if(count(Output::$messages['__'])>0)
			{
				//echo '<!-- messagebox.tpl.php -->';
				$mb=self::$tpl->fetch('messagebox.tpl.php');
			}
		
			$out=self::$tpl->fetch('pages/'.Output::$cpage.'.tpl.php');
			if(self::$tpl->isError($out))
				Output::HardError('Savant3 template error.<br />&quot;'.$out->code.'&quot;<br />Page ID:'.Output::$cpage);
			else
				echo $head.$mb.$out;
			self::$tpl->display('footer.tpl.php');
		} else {
			self::$tpl->display('gheader.tpl.php');
		}
		if(defined('USING_PROFILER'))
		{
			//
		}
		die('</body></html>');
	}
	
	static function RenderQF2($form)
	{
		echo '<form' . $form->getAttributes(true) . ">\n";
		try
		{
	    foreach($form as $element)
			Output::QF2_Element($element);
		}
		catch(Exception $e)
		{
			die($e);
		}
		?></form><?
	}
	
	static public $tablemade=false;
	static function QF2_Element($element)
	{
		
		echo "\n<!-- {$element->getType()} -->";
	    if ('fieldset' == $element->getType()) {
			self::$tablemade=true;
	        Output::QF2_fieldset($element);
			return;
		}
		
	    if ('hidden' == $element->getType()) {
	        echo '<tr style="display: none;"><td colspan="3">' . $element->__toString() . "</td></tr>\n";
	    } else {
			$r='';
			if($element->isRequired())$r='<span class="required">*</span>';
			
			$e='';
			$err=$element->getError();
			if(strlen($err)>0) $e="<div class=\"error\">$err</div>";
	        echo '<tr class="qfrow"><th><label class="qflabel" for="' . $element->getId() .
	             '">' . $element->getLabel() . '</label></th><td class="qfelement">' .
	             $element->__toString() . "</td><td>$e</td></tr>\n";
		}
	}
	
	static function QF2_fieldset($fieldset)
	{
?>
<fieldset <?=$fieldset->getAttributes(true)?>>
	<legend><?=$fieldset->getLabel()?></legend>
	<table>
<?
	    foreach ($fieldset as $element) {
	        Output::QF2_element($element);
	    }
	    echo "</table></fieldset>\n";
	}
	static function AddDateControl(&$form,$name = null, $attributes = null,array $data = array())
	{
		return $form->appendChild(new FlexDate_Input($name,$attributes,$data));
	}
}

function Add2Dash($tpl)
{
	global $_i;
	Output::PrepSV3();
	if($_i==0)
	{
		?>
		<tr>
<?
	}
	?>
			<td>
				<div class="widgetwrap">
					<?=Output::$tpl->display($tpl)?>
				</div>
			</td>
	<?
	if($_i==1) {
		?>
		</tr>
<?
		
	}$_i++;
}
