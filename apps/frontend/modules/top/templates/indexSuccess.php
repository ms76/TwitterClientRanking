<div id="extract_form">
	<form action="<?php echo url_for('top/index')?>"  method="post" id="extract">
		<?php echo $form->renderHiddenFields(false) ?>
		<?php echo $form['log'] ?>
		<?php echo $form['lang'] ?>
		<?php echo $form['time_zone'] ?>
		<?php echo $form['utc_offset'] ?>
		<input type="submit" name="submit" value="<?php echo __('extract')?>">
	</form>
</div>

<?php if (isset($error_message)) : ?>
<div class="error_message">
	<?php echo $error_message ?>
</div>
<?php endif ?>

<?php if ($result['retval']->count() > 0) : ?>
<div id="info">
	Total:
	<?php echo number_format($result['keys']) ?> clients,
	<?php echo number_format($result['count']) ?> tweets
	<?php if (isset($result['time_millis'])) : ?>
	<!-- (<?php echo $result['time_millis'] / 1000 ?> seconds) -->
	<?php endif ?>
</div>

<div id="table">
<table class="result">
	<tr class="header">
		<th><?php echo __('Rank') ?></th>
		<th><?php echo __('Source(Client)') ?></th>
		<th><?php echo __('Count') ?></th>
		<th><?php echo __('Ratio') ?></th>
	</tr>
<?php $rank = 1; ?>
<?php $pre_count = 0; ?>
<?php $pre_rank_count = 0; ?>
<?php $graph = array(); ?>
<?php foreach ($result['retval'] as $key=>$row) : ?>
	<?php if ($pre_count == $row['count']) :
			$pre_rank_count++;
		else :
			$pre_rank_count = 0;
		endif?>

	<?php if ($rank <= 10) : ?>
		<?php $graph['source'][$rank] = rawurlencode($row['source']) ?>
		<?php $graph['ratio'][$rank] = round(($row['count'] / $result['count'] * 100), 1) ?>
	<?php endif ?>

	<tr class="record<?php echo ($rank > 16) ? ' over' : '' ?>">
		<td class="rank"><?php echo($rank - $pre_rank_count) ?></td>
		<td class="source"><?php echo(strpos($row['source_url'], 'http://', 0) === 0) ? '<a href="' . $row['source_url'] . '" rel="nofollow">' . $row['source'] . '</a>' : $row['source'] ?></td>
		<td class="count"><?php echo number_format($row['count']) ?></td>
		<td class="ratio"><?php echo round(($row['count'] / $result['count'] * 100), 2) ?>%</td>
	</tr>

	<?php $rank++ ?>
	<?php $pre_count = $row['count']; ?>
<?php endforeach ?>
</table>

<div id="view_all">
	<span>view all</span>
</div>

</div>

<?php $etc = (100 - array_sum($graph['ratio'])) ?>
<div id="chart">
	<div id="compare">
		<div id="target"></div>
		<button id="submit">compare</button>
	</div>
	<div id="graph">
		<img src="http://chart.apis.google.com/chart?chco=3072F3,7E2DF1,D1078E,C83360,FD2020,FCAB31,FFDA6A,69FD69,399D39,3C60BB,BEBEBE&chdlp=r&cht=p&chd=t:<?php echo implode(',',$graph['ratio'])?><?php echo ($etc) ? ','.$etc : ''?>&chs=400x350&chp=4.7&chdl=<?php echo implode('|',$graph['source'])?><?php echo ($etc) ? '|Others' : ''?>" alt="" width="400" height="350"/>
	</div>
</div>
<div id="chart_overlay"></div>
<div id="overlay_image"><img src="/images/loader.gif" alt="" width="32" height="32"/><br/>loading</div>

<div class="clear"></div>
<?php else : ?>
<div>
	No data.
</div>
<?php endif ?>
