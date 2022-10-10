<?php
	$head = array(
		'bodyclass' => 'admin-tools index',
		'title' => html_escape(__('Admin Tools')),
		'content_class' => 'horizontal-nav'
	);

	if (get_option('admin_tools_maintenance_active')) {
		$sumOperation = 'disable';
		$sumLabel = __('Stop Maintenance');
		$sumColor = 'red';
	} else {
		$sumOperation = 'enable';
		$sumLabel = __('Start Maintenance');
		$sumColor = 'green';
	}

	echo head($head);
?>

<?php echo flash(); ?>

<div class="field">
	<div id="SUM-label" class="two columns alpha">
		<label for="SUM"><?php echo __('Site Under Maintenance'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Block out from Public interface not-logged in users (and also from Admin interface some logged-in users), displaying instead an "Under Maintenance" sign.'); ?></p>
		<a id="SUM" class="button <?php echo $sumColor; ?>" href="?op=SUM-<?php echo $sumOperation; ?>"><?php echo $sumLabel; ?></a>
	</div>
</div>

<div class="field">
	<div id="RC-label" class="two columns alpha">
		<label for="RC"><?php echo __('Languages Cache'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Update all translations after language files have been changed manually.'); ?></p>
		<a id="RC" class="button green" href="?op=RC"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="BD-label" class="two columns alpha">
		<label for="BD"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Backup the entire Omeka database into an SQL file.'); ?></p>
		<a id="BD" class="button green" href="?op=BD"><?php echo __('Backup Database'); ?></a>
	</div>
</div>

<div class="field">
	<div id="TST-label" class="two columns alpha">
		<label for="TST"><?php echo __('Sessions Table'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Trim Omeka\'s Sessions table') . (get_option('admin_tools_sessions_count') ? ' ' . __('(at the moment, the table contains %s records)', number_format($this->sessionsCount)) : '') . __('. Choose whether to delete sessions older than 1 week/month/year or all expired ones.'); ?></p>
		<a id="TSTW" class="button green" href="?op=TSTW"><?php echo __('Trim (+1 week)'); ?></a>
		<a id="TSTM" class="button green" href="?op=TSTM"><?php echo __('Trim (+1 month)'); ?></a>
		<a id="TSTY" class="button green" href="?op=TSTY"><?php echo __('Trim (+1 year)'); ?></a>
		<a id="TSTE" class="button green" href="?op=TSTE"><?php echo __('Trim (expired)'); ?></a>
	</div>
</div>

<?php echo foot(); ?>
