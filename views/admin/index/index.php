<?php
	queue_js_file('chart.umd', 'javascripts');
	queue_css_file('admin-tools');
	
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
		<label for="SUM"><?php echo __('Site Maintenance'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Block out from Public interface not-logged in users') . (get_option('admin_tools_maintenance_scope_extended') ? __(' (and also, from Admin interface, some logged-in users)') : '') . __(', displaying instead an "Under Maintenance" sign.'); ?></p>
		<a id="SUM" class="button <?php echo $sumColor; ?>" href="<?php echo url('admin-tools/index/maintenance/op/' . $sumOperation); ?>"><?php echo $sumLabel; ?></a>
	</div>
</div>

<div class="field">
	<div id="TRC-label" class="two columns alpha">
		<label for="TRC"><?php echo __('Translations'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Update all translations after language files have been changed manually') . ((bool)get_option('admin_tools_translations_theme') ? ', ' . __('including files that are part of the <b>active theme</b>.') : '.'); ?></p>
		<a id="TRC" class="button green" href="<?php echo url('admin-tools/index/reset-cache'); ?>"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="TDB-label" class="two columns alpha">
		<label for="TDB"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Backup the entire Omeka database into a SQL file') . $this->lastBackupDateTime . '.' .(get_option('admin_tools_backup_sessions_ignore') ? ' ' . __('During the backup, data from "Sessions" table will be <b>ignored</b>.') : '') . (get_option('admin_tools_backup_download') ? ' ' . __('A copy of the file will be available for download') . (get_option('admin_tools_backup_compress') ? __(', <b>compressed</b> in GZip format.') : '.') : '.'); ?></p>
		<a id="TDB" class="button green" href="<?php echo url('admin-tools/index/backup'); ?>"><?php echo __('Backup Database'); ?></a>
	</div>
</div>

<div class="field">
	<div id="PLU-label" class="two columns alpha">
		<label for="PLU"><?php echo __('Plugins'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<?php
			echo '<p class="explanation">';
			if ($this->plugins == 0) {
				// case no installed plugin
				echo __('No Plugin is installed in the system.');
				$plu_btns = '<a id="PLU_ON" class="button at_disabled" disabled>' . __('Activate All Plugins') . '</a>
							<a id="PLU_OFF" class="button at_disabled" disabled>' . __('Deactivate All Plugins') . '</a>';
			} else {
				echo __(plural('The system contains <b>1</b> installed Plugin', 'The system contains <b>%s</b> installed Plugins', $this->plugins), $this->plugins);
				echo ', ';
				if ($this->plugins == $this->pluginsActive) {
					// case all installed plugins are active
					echo __(plural('which is already active.', 'which are all already active.', $this->plugins));
					$plu_btns = '<a id="PLU_ON" class="button at_disabled" disabled>' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button green" href="' . url('admin-tools/index/plugins-deactivate') . '">' . __('Deactivate All Plugins') . '</a>';
				} elseif ($this->pluginsActive == 0) {
					// case all installed plugins are inactive
					echo __(plural('which is not active.', 'which are all not active.', $this->plugins));
					$plu_btns = '<a id="PLU_ON" class="button green" href="' . url('admin-tools/index/plugins-activate') . '">' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button at_disabled" disabled>' . __('Deactivate All Plugins') . '</a>';
				} else {
					// case else
					echo __(plural('of which <b>1</b> active.', 'of which <b>%d</b> active.', $this->pluginsActive), $this->pluginsActive);
					$plu_btns = '<a id="PLU_ON" class="button green" href="' . url('admin-tools/index/plugins-activate') . '">' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button green" href="' . url('admin-tools/index/plugins-deactivate') . '">' . __('Deactivate All Plugins') . '</a>';
				}
			}
			
			echo ' ';
			if ($this->pluginsInvalid > 0) {
				echo __(plural('<b>1</b> Plugin appears to be invalid/damaged, and can be safely removed.', '<b>%d</b> Plugins appear to be invalid/damaged, and can be safely removed.', $this->pluginsInvalid), $this->pluginsInvalid);
				$plu_btns .= '<a id="PLU_REMOVE" class="button green" href="' . url('admin-tools/index/plugins-remove-invalid') . '">' . __('Remove Invalid Plugins') . '</a>';
			} else {
				echo __('No Plugin appears to be invalid/damaged.');
				$plu_btns .= '<a id="PLU_REMOVE" class="button at_disabled" disabled>' . __('Remove Invalid Plugins') . '</a>';
			}
			
			echo '</p>' . $plu_btns;
		?>
	</div>
</div>

<div class="field">
	<div id="TST-label" class="two columns alpha">
		<label for="TST"><?php echo __('Sessions'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Trim Omeka\'s "Sessions" table') . (get_option('admin_tools_sessions_count') ? ' ' . __('(at the moment, the table contains <strong>%s</strong> records)', number_format($this->sessionsCount)) : '') . __(', choosing whether to delete sessions older than 1 year/month/week/day or all expired ones (at the moment, sessions expire after <strong>%s</strong> days).', $this->sessionMaxLifeTime); ?></p>
		<?php if ($this->sessionsCount > 0 && (bool)get_option('admin_tools_sessions_graph')): ?>
			<canvas id="sessionsChart" style="width:100%; height: 200px; margin-bottom: 1em"></canvas>
			<script>
				<?php
					// define variables
					$ascisse = [];
					$ordinate = [];
					
					// get number of sessions grouped by date
					$sql = "SELECT count(id) AS total, DATE(FROM_UNIXTIME(modified)) AS session_date FROM omeka_sessions GROUP BY session_date";
					$rows = get_db()->query($sql)->fetchall();
					
					// limit number of entries to sessionMaxLifeTime
					if (count($rows) > $this->sessionMaxLifeTime) {
						array_splice($rows, 0, count($rows) - $this->sessionMaxLifeTime);
					}
					
					// create coordinates for graph
					foreach ($rows as $row) {
						$ascisse[] = date_format(date_create($row['session_date']), 'd/m');
						$ordinate[] = $row['total'];
					}
				?>
				new Chart("sessionsChart", {
					type: "line",
					data: {
						labels: <?= json_encode($ascisse) ?>,
						datasets: [{
							data: <?= json_encode($ordinate) ?>,
							borderWidth: 2,
							tension: 0.1
						}]
					},
					options: {
						plugins: {
							legend: {
								display: false
							},
							title: {
								display: true,
								text: '<?= __('Sessions in the last %d days', $this->sessionMaxLifeTime) ?>'
							}
						},
						scales: {
							y: {
								beginAtZero: true
							}
						}
					}
				});
			</script>
		<?php endif; ?>

		<?php 
			// adds button to prune sessions over 1 year old - disabled if there are none
			if ($this->sessionsYearCount > 0) {
				echo '<a id="TSTY" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/year') . '">' . __('Trim sessions (+1 year)') . '</a>';
			} else {
				echo '<a id="TSTY" class="button at_disabled" disabled>' . __('Trim sessions (+1 year)') . '</a>';
			}

			// adds button to prune sessions over 1 month old - disabled if there are none
			if ($this->sessionsMonthCount > 0) {
				echo '<a id="TSTM" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/month') . '">' . __('Trim sessions (+1 month)') . '</a>';
			} else {
				echo '<a id="TSTM" class="button at_disabled" disabled>' . __('Trim sessions (+1 month)') . '</a>';
			}

			// adds button to prune sessions over 1 week old - disabled if there are none
			if ($this->sessionsWeekCount > 0) {
				echo '<a id="TSTW" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/week') . '">' . __('Trim sessions (+1 week)') . '</a>';
			} else {
				echo '<a id="TSTM" class="button at_disabled" disabled>' . __('Trim sessions (+1 week)') . '</a>';
			}

			// adds button to prune sessions over 1 day old - disabled if there are none
			if ($this->sessionsDayCount > 0) {
				echo '<a id="TSTD" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/day') . '">' . __('Trim sessions (+1 day)') . '</a>';
			} else {
				echo '<a id="TSTD" class="button at_disabled" disabled>' . __('Trim sessions (+1 day)') . '</a>';
			}
		?>   
		<a id="TSTE" class="button green" href="<?php echo url('admin-tools/index/trim-sessions/rng/expired'); ?>"><?php echo __('Trim sessions (expired)'); ?></a>
	</div>
</div>

<div class="field">
	<div id="DUT-label" class="two columns alpha">
		<label for="DUT"><?php echo __('Tags'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<?php
			echo '<p class="explanation">';
			if ($this->tagsUnused > 0) {
				echo __(plural('<b>1</b> Tag has no correspondence to any Items', '<b>%d</b> Tags have no correspondence to any Item', $this->tagsUnused), $this->tagsUnused);
				if ($this->itemsUntagged > 0) {
					echo ', ' . __('and') . ' ' . __(plural('<b>1</b> Item has no Tags associated.', '<b>%d</b> Items have no Tags associated.', $this->itemsUntagged), $this->itemsUntagged);
				} else {
					echo ', ' . __('but') . ' ' . __('all Items have at least one Tag associated.');
				}
			} else {
				echo __('All Tags are associated to at least one Item');
				if ($this->itemsUntagged > 0) {
					echo ', ' . __('but') . ' ' . __(plural('<b>1</b> Item has no Tags associated.', '<b>%d</b> Items have no Tags associated.', $this->itemsUntagged), $this->itemsUntagged);
				} else {
					echo ', ' . __('and') . ' ' . __('all Items have at least one Tag associated.');
				}
			}
			echo '</p>';

			if ($this->tagsUnused > 0) {
				echo '<a id="DUT" class="button green" href="' . url('admin-tools/index/delete-tags') . '">' . __(plural('Delete Unused Tag', 'Delete Unused Tags', $this->total_unused_tags)) . '</a>';
			} else {
				echo '<a id="DUT" class="button at_disabled" disabled>' . __('Delete Unused Tags') . '</a>';
			}

			if ($this->itemsUntagged > 0) {
				echo '<a id="SUI" class="button green" href="' . url('items/browse?search=&advanced-joiner=and&advanced-element_id=&advanced-type=&advanced-terms=&has-tags=0') . '">' . __(plural('Show Untagged Item', 'Show Untagged Items', $this->itemsUntagged)) . '</a>';
			} else {
				echo '<a id="SUI" class="button at_disabled" disabled>' . __('Show Untagged Items') . '</a>';
			}
		?>
	</div>
</div>

<?php echo foot(); ?>
