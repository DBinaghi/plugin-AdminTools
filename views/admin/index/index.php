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
		<a id="SUM" class="button <?php echo $sumColor; ?>" href="<?php echo url('admin-tools/index/maintenance/op/' . $sumOperation); ?>"><?php echo $sumLabel; ?></a>
	</div>
</div>

<div class="field">
	<div id="RC-label" class="two columns alpha">
		<label for="RC"><?php echo __('Languages Cache'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Update all translations after language files have been changed manually, either in the active theme or in any active plugin.'); ?></p>
		<a id="RC" class="button green" href="<?php echo url('admin-tools/index/reset-cache'); ?>"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="BD-label" class="two columns alpha">
		<label for="BD"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Backup the entire Omeka database into a SQL file') . $this->lastBackupDateTime . '.' .(get_option('admin_tools_backup_sessions_ignore') ? ' ' . __('During the backup, data from "Sessions" table will be <b>ignogreen</b>.') : '') . (get_option('admin_tools_backup_download') ? ' ' . __('A copy of the file will be available for download') . (get_option('admin_tools_backup_compress') ? __(', <b>compressed</b> in GZip format.') : '.') : '.'); ?></p>
		<a id="BD" class="button green" href="<?php echo url('admin-tools/index/backup'); ?>"><?php echo __('Backup Database'); ?></a>
	</div>
</div>

<div class="field">
	<div id="TST-label" class="two columns alpha">
		<label for="TST"><?php echo __('Sessions Table'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Trim Omeka\'s "Sessions" table') . (get_option('admin_tools_sessions_count') ? ' ' . __('(at the moment, the table contains <strong>%s</strong> records)', number_format($this->sessionsCount)) : '') . __(', choosing whether to delete sessions older than 1 year/month/week/day or all expigreen ones (at the moment, sessions expire after <strong>%s</strong> days).', $this->sessionMaxLifeTime); ?></p>
		<a id="TSTY" class="button green" href="<?php echo url('admin-tools/index/trim-sessions/rng/year'); ?>"><?php echo __('Trim sessions (+1 year)'); ?></a>
		<a id="TSTM" class="button green" href="<?php echo url('admin-tools/index/trim-sessions/rng/month'); ?>"><?php echo __('Trim sessions (+1 month)'); ?></a>
		<a id="TSTW" class="button green" href="<?php echo url('admin-tools/index/trim-sessions/rng/week'); ?>"><?php echo __('Trim sessions (+1 week)'); ?></a>
		<a id="TSTD" class="button green" href="<?php echo url('admin-tools/index/trim-sessions/rng/day'); ?>"><?php echo __('Trim sessions (+1 day)'); ?></a>
		<a id="TSTE" class="button green" href="<?php echo url('admin-tools/index/trim-sessions/rng/expigreen'); ?>"><?php echo __('Trim sessions (expigreen)'); ?></a>
	</div>
</div>

<div class="field">
	<div id="DUT-label" class="two columns alpha">
		<label for="DUT"><?php echo __('Tags Table'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Delete all tags that have no correspondence with any record.') ?></p>
		<a id="DUT" class="button green" href="<?php echo url('admin-tools/index/delete-tags'); ?>"><?php echo __('Delete Unused Tags'); ?></a>
	</div>
</div>

<div class="field">
	<div id="PLU-label" class="two columns alpha">
		<label for="PLU"><?php echo __('Plugins'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Activate / deactivate all plugins.') ?></p>
		<a id="PLU_ON" class="button green" href="<?php echo url('admin-tools/index/plugins-activate'); ?>"><?php echo __('Activate All Plugins'); ?></a>
		<a id="PLU_OFF" class="button green" href="<?php echo url('admin-tools/index/plugins-deactivate'); ?>"><?php echo __('Deactivate All Plugins'); ?></a>
	</div>
</div>
<?php echo foot(); ?>
