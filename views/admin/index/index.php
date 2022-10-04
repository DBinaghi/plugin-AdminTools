<?php
	$head = array(
		'bodyclass' => 'admin-tools index',
		'title' => html_escape(__('Admin Tools')),
		'content_class' => 'horizontal-nav'
	);
	if (isset($_GET['view'])) {
		$active = $_GET['view'];
	}
	echo head($head);
?>

<?php
	if (get_option('admin_tools_maintenance_active')) {
		$sumOperation = 'disable';
		$sumLabel = __('Stop Maintenance');
		$sumColor = 'red';
	} else {
		$sumOperation = 'enable';
		$sumLabel = __('Start Maintenance');
		$sumColor = 'green';
	}
?>

<?php echo flash(); ?>

<div class="field">
	<div id="SUM-label" class="two columns alpha">
		<label for="SUM"><?php echo __('Site Under Maintenance'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Block out from Public interface not-logged in users (and also from Admin interface some logged-in users), displaying instead an "Under Maintenance" sign.'); ?></p>
		<a id="SUM" class="add-page button <?php echo $sumColor; ?>" href="?op=SUM-<?php echo $sumOperation; ?>"><?php echo $sumLabel; ?></a>
	</div>
</div>

<div class="field">
	<div id="RC-label" class="two columns alpha">
		<label for="RC"><?php echo __('Languages Cache'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Reset all translations after language files have been changed manually.'); ?></p>
		<a id="RC" class="add-page button green" href="?op=RC"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="BD-label" class="two columns alpha">
		<label for="BD"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Backup the entire Omeka database into an SQL file and download it.'); ?></p>
		<?php if (get_option('admin_tools_backup_download')): ?>
			<a id="BD" class="add-page button green" href="<?php echo ADMIN_TOOLS_BACKUP_FILENAME; ?>?op=BD" download="<?php echo 'OmekaDB-backup_' . date('Ymd_His') . '.sql' ?>"><?php echo __('Backup Database'); ?></a>
		<?php else: ?>
			<a id="BD" class="add-page button green" href="?op=BD"><?php echo __('Backup Database'); ?></a>
		<?php endif; ?>
	</div>
</div>

<?php echo foot(); ?>
