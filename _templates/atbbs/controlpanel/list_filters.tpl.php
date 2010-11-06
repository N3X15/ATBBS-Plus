
	<fieldset>
		<legend>New Filter</legend>				
		<p><b>BEFORE YOU GET STARTED, READ THIS VERY FUCKING CAREFULLY.</b></p>
		<p>
			This system uses a special translator to turn Shift-JIS, and other ASCII-like characters
			into something the system can easily identify. It also changes some common avoidance
			characters into their "original" ones ( "{" will be changed to "C", same with "<"). 
		</p>
		<p>
			THIS MEANS THAT YOUR FILTERS WILL BE
			CAPITALIZED AND USUALLY A BIT DIFFERENT THAN WHAT YOU MAY HAVE ORIGINALLY ENTERED.
		</p>
		<p>
			<i>This system is NOT perfect.</i> When you mass-add shit, <b>you will need to strip out 
			any random padding at the beginning or end of the post</b>.  The point is to <b>try and find
			a common string within each spam post.</b>  This means that <b>if the post is completely 
			random, DO NOT CONFIRM IT.</b>
		</p>	
	</fieldset>
	<form name="filters" action="/controlpanel.php/add_filters/" method="post">
	<table>
		<caption>Filters</caption>
		<tr>
			<th class="chkboxes">(NEW)<input type="hidden" name="confirm[]" value="1"/></th>
			<td>
				<label for="filter[]">Text to filter:</label>
				<textarea name="filter[]" class="filter"></textarea>
				<label for="type[]">Reaction:</label>
				<select name="type[]" id="filter_type">
<?	foreach($this->Options as $value=>$label):?>
					<option value="<?=$value?>"><?=$label?></option>
<?	endforeach;?>
				</select>

				<label for="reason[]">Reason for Filter:</label><input class="inline" type="textbox" name="reason[]" />
				<br />
				<br />
				<h3>Replacement Settings</h3>
				<p>
					<label for="replace[]">Replace with:</label>
					<textarea name="replace[]"></textarea>
				</p>
				<h3>Ban Settings</h3>
				<p>
					<table>
						<tr>
							<td><label for="duration[]">Ban Duration:</label></td>
							<td><input class="inline" type="textbox" name="duration[]" /></td>
						</tr>
					</table>
				</p>
			</td>
		</tr>
<? foreach($_SESSION['2BFiltered'] as $k=>$filter): $i=$k+1;?>
		<tr>
			<th class="chkboxes">
				<span title="Confirm the addition of this filter.">
					<input class="inline" type="checkbox" name="confirm[<?=$i?>]" value="1" />
				</span>
			</th>
			<td class="<?=(($i%2)==1) ? 'odd' : 'even'?>"><!--<?=$k?>-->
					<label for="filter[]">Text to filter:</label>
					<textarea name="filter[]" class="filter"><?=htmlspecialchars($filter)?></textarea>
					<label for="type[]">Reaction:</label>
					<select name="type[]" id="filter_type">
<?	foreach($this->Options as $value=>$label):?>
						<option value="<?=$value?>"><?=$label?></option>
<?	endforeach;?>
					</select>

					<label for="reason[]">Reason for Filter:</label><input class="inline" type="textbox" name="reason[]" />
					<br />
					<br />
					<h3>Replacement Settings</h3>
					<p>
						<label for="replace[]">Replace with:</label>
						<textarea name="replace[]"></textarea>
					</p>
					<h3>Ban Settings</h3>
					<p>
						<table>
							<tr>
								<td><label for="duration[]">Ban Duration:</label></td>
								<td><input class="inline" type="textbox" name="duration[]" /></td>
							</tr>
						</table>
					</p>
				</form>
			</td>
		</tr>
<? endforeach;?>
		<tr>
			<th>&nbsp;</th>
			<td><p><input type="submit" value="Add Filter" /></p></td>
		</tr>
	</table>
	</form>
	<form name="filters" action="/controlpanel.php/add_filters/" method="post">
<?=$this->tbl?>
		<p><input type="submit" value="Delete Filters" /></p>
	</form>
