<?php
/**
 * Date control for QuickForm
 *
 * <i>Very</i> Hackish.
 */
 
/**
 * Base class for simple HTML_QuickForm2 elements (not Containers)
 */
require_once 'HTML/QuickForm2/Element/Input.php';
require_once('input.php');

class FlexDate_Input extends HTML_QuickForm2_Element
{
	protected $attributes = array('type'=>'date');
 
    protected $value=0;
	protected $montharray=array (1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
 
	public function getType()
	{
		return 'date';
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function setValue($v)
	{
		$this->value=$v;
	}
	private function FillArray($b,$e)
	{
		$inc=1;
		if($b>$e)
			$inc=-1;
		$a=array();
		for($i=$b;$i!=($e+$inc);$i+=$inc)
		{
			$a[$i]=$i;
		}
		return $a;
	}
	
	private function GenOptions($a,$s)
	{
		$select='';
		foreach ($a as $key => $val) {
		    $select .= "\t<option value=\"".$key."\"";
		    if ($key == $s) {
		        $select .= " selected>".$val."</option>\n";
		    } else {
		        $select .= ">".$val."</option>\n";
		    }
		}
		return $select;
	}
	
	public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);
		if(POST::WasUsed())
			$this->value=$this->GetDate($name);
	}
    function GetDate($controlname)
	{
		$d=POST::GetInt($controlname.'_d');
		$m=POST::GetInt($controlname.'_m');
		$y=POST::GetInt($controlname.'_y');
		return mktime(0,0,0,$m,$d,$y);
	}
	
    public function __toString()
    {
		$name=$this->getAttribute('name');
		$v=$this->GetValue();
        if ($this->frozen) {
            return $this->getFrozenHtml();
        } else {
            return '<span class="flexDate" id="'.$this->getAttribute('id').'">'.
				'<select name="'.$name.'_m">'.$this->GenOptions($this->montharray,date('m',$v)).'</select>&nbsp;'.
				'<select name="'.$name.'_d">'.$this->GenOptions($this->FillArray(1,31),date('d',$v)).'</select>&nbsp;'.
				'<select name="'.$name.'_y">'.$this->GenOptions($this->FillArray(date('Y'),1930),date('Y',$v)).'</select></span>';
        }
    }
 
   /**
    * Returns the field's value without HTML tags
    * @return string 
    */
    protected function getFrozenHtml()
    {
        $value = $this->getValue();
        return date('F j, Y',$value);
    }
}
