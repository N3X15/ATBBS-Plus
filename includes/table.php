<?php
/**
* Table Printer Class
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

define('SORT_NONE',	0);
define('SORT_ASC',	1);
define('SORT_DESC',	2);

// New table with database sorting capabilities.
class TablePrinter
{
	public $num_rows_fetched = 0;
	
	private $output = '';
	
	private $primary_key;
	private $SortKey=-1;
	private $DBColumns=array();
	private $columns = array();
	private $td_classes = array();
	
	private $marker_printed = false;
	private $last_seen = false;
	private $order_time = false;

	private $Ascending=true; // ASC or DESC
	private $Name='';

	private $ST2Sort=array(
		SORT_NONE=>'', // Shouldn't be used. Ever.
		SORT_ASC =>'ASC',
		SORT_DESC=>'DESC'
	);
	// Name controls sort column links.
	public function TablePrinter($Name)
	{
		$this->Name=$Name;
		$this->tpl=Output::$tpl; // Get template engine
		$this->tpl->assign('Name',$Name); // Table name
		$this->tpl->assign('Sorting',false);
	}
	public function DefineColumns($all_columns, $primary_column)
	{
		$this->columns = $all_columns;
		$this->primary_key=array_search($primary_column,$this->columns);
		$this->tpl->assign('AllColumns',	$all_columns);
		$this->tpl->assign('PrimaryColumn',	$primary_column);
		$this->output .= $this->tpl->fetch('table/thead.tpl.php');
	}
	
	public function DefaultSorting($column,$type,$dbc)
	{
		//echo "DefaultSorting($column,$type)";
		//$this->tpl->assign('Sorting',true); The redirects are fucking this up.
		$this->tpl->assign('DBColumns',$dbc);
		$this->DBColumns=$dbc;
		var_dump($_GET);
		$sk=$_GET[$this->Name.'sk'];
		$st=$_GET[$this->Name.'st'];
		$this->SortKey =(empty($sk)) ? array_search($column,$dbc) 	: intval($sk);
		$this->SortType=(empty($st)) ? $type 				: intval($st);
		$this->tpl->assign('SortKey',$this->SortKey);
		$this->tpl->assign('SortType',$this->SortType);
	}

	public function GetOrderSQL()
	{
		$sk=$this->SortKey;
		$st=$this->SortType;
		echo "Key: $sk, Type: $st";
		
		if($st==SORT_NONE) return '';

		$column=$this->DBColumns[$sk];
		return sprintf('ORDER BY `%s` %s',$column,$this->ST2Sort[$st]);
	}

	public function SetTDClass($column_name, $class)
	{
		$this->td_classes[$column_name] = $class;
	}
	
	public function LastSeenMarker($last_seen, $order_time)
	{
		$this->last_seen = $last_seen;
		$this->order_time = $order_time;
	}
	
	public function Row($values)
	{
		$printLastSeen=false;

		// Print the last seen marker?
		if($this->last_seen && ! $this->marker_printed && $this->order_time <= $this->last_seen)
		{
			$this->marker_printed = true;
			if($this->num_rows_fetched != 0)
			{
				$printLastSeen=true;
			}
		}
		$this->tpl->assign('PrintLastSeen',	$printLastSeen);
		$this->tpl->assign('Values',		$values);
		$this->tpl->assign('RowClass',		($this->num_rows_fetched&1)? 'odd' : 'even');
		$this->tpl->assign('LastSeen',		$this->last_seen);
		$this->tpl->assign('PrimaryKey',	$this->primary_key);
		$this->tpl->assign('TDClasses',		$this->td_classes);
		$this->tpl->assign('Columns',		$this->columns);
		$this->output .= $this->tpl->fetch('table/row.tpl.php');
		
		
		$this->num_rows_fetched++;
	}
	
	public function Output($items = 'items', $silence = false)
	{
		$this->output .=  '</tbody></table>';
		
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

	public function __toString() { return $this->Output(); }
}

// Legacy Table
class table
{
	public function table()
	{
		global $TABLE_COUNT;
		$i=$TABLE_COUNT++;
		$n='abcdefghijklmnopqrstuvwxyz';
		$this->t=new TablePrinter($n[$i],'_');
	}
	
	public function define_columns($all_columns, $primary_column)
	{
		$this->t->DefineColumns($all_columns,$primary_column);
	}
	
	public function add_td_class($column_name, $class)
	{
		$this->t->SetTDClass($column_name,$class);
	}
	
	public function last_seen_marker($last_seen, $order_time)
	{
		$this->t->LastSeenMarker($last_seen,$order_time);
	}
	
	public function row($values)
	{
		$this->t->Row($values);
	}
	
	public function output($items = 'items', $silence = false)
	{
		echo "<b>WARNING: class table is Depreciated!</b><br />";
		$bt=debug_backtrace();
		printf('Backtrace: file %s: line %d<br />',$bt[0]['file'],$bt[0]['line']);
		return $this->t->Output($items,$silence);
	}
}
