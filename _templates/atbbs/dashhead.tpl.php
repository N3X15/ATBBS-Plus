<div id="dasharea">
<div id="sidebar">
<?foreach($this->sidebar as $pname => $portlet):?>
	<div class="portlet pt_<?=str_replace(' ','_',strtolower($pname))?>">
		<h4><?=$pname?></h4>
		<ul>
<?foreach($portlet as $link):?>
			<li>
				<a href="<?=rel2Abs($link[0])?>" title="<?=$link[2]?>"><?=$link[1]?></a>
			</li>
<?endforeach;?>
		</ul>
	</div>
<?endforeach;?>
</div>
<div id="dashbody">
