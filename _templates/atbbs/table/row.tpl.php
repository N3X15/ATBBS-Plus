<?php
/**
* TablePrinter::Row Template
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

$class=(!empty($this->TRClass)) ? sprintf('class="%s"',$this->TRClass) : '';
?>
<tr <?=$class?> <?if($this->LastSeenMarker) echo 'id="last_seen_marker"';?>>
<?
foreach($this->Values as $key => $value)
{
	$classes = array();		
	
	// If this isn't the primary column (as set in define_columns()), its length should be minimal.
	if($key !== $this->PrimaryKey)
	{
		$classes[] = 'minimal';
	}
	// Check if a class has been added via add_td_class.
	if( isset( $this->TDClasses[ $this->Columns[$key] ] ) )
	{
		$classes[] = $this->TDClasses[$this->Columns[$key]];
	}
	$c='';
	// Print any classes added by the above two conditionals.
	if( ! empty($classes))
	{
		$c='class="' . implode(' ', $classes) . '"';
	}
?>
	<td <?=$c?>>
		<?=$value?>

	</td>
<?		}
?>		
</tr>
