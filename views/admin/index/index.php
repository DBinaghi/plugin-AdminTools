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
		<p class="explanation"><?php echo __('Block out from Public interface not-logged in users') . (get_option('admin_tools_maintenance_scope_extended') ? __(' (and also, from Admin interface, some logged-in users)') : '') . __(', displaying instead an "Under Maintenance" sign.'); ?></p>
		<a id="SUM" class="button <?php echo $sumColor; ?>" href="?op=SUM-<?php echo $sumOperation; ?>"><?php echo $sumLabel; ?></a>
	</div>
</div>

<div class="field">
	<div id="RC-label" class="two columns alpha">
		<label for="RC"><?php echo __('Languages Cache'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Update all translations after language files have been changed manually, either in the active theme or in any active plugin.'); ?></p>
		<a id="RC" class="button green" href="?op=RC"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="BD-label" class="two columns alpha">
		<label for="BD"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Backup the entire Omeka database into an SQL file.') . (get_option('admin_tools_backup_sessions_ignore') ? __(' During the backup, data from Sessions table will be ignored.') : '') . (get_option('admin_tools_backup_download') ? __(' A copy of the file will be available for download') . (get_option('admin_tools_backup_compress') ? __(', compressed in GZip format.') : '.') : '.'); ?></p>
		<a id="BD" class="button green" href="?op=BD"><?php echo __('Backup Database'); ?></a>
	</div>
</div>

<div class="field">
	<div id="TST-label" class="two columns alpha">
		<label for="TST"><?php echo __('Sessions Table'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Trim Omeka\'s Sessions table') . (get_option('admin_tools_sessions_count') ? ' ' . __('(at the moment, the table contains <strong>%s</strong> records)', number_format($this->sessionsCount)) : '') . __(', choosing whether to delete sessions older than 1 year/month/week or all expired ones (at the moment, sessions expire after <strong>%s</strong> days).', $this->sessionMaxLifeTime); ?></p>
		<a id="TSTY" class="button green" href="?op=TSTY"><?php echo __('Trim (+1 year)'); ?></a>
		<a id="TSTM" class="button green" href="?op=TSTM"><?php echo __('Trim (+1 month)'); ?></a>
		<a id="TSTW" class="button green" href="?op=TSTW"><?php echo __('Trim (+1 week)'); ?></a>
		<a id="TSTE" class="button green" href="?op=TSTE"><?php echo __('Trim (expired)'); ?></a>
	</div>
</div>

<?php echo foot(); ?>
