<?php
	queue_js_file('chart.umd', 'javascripts');
	queue_css_file('admin-tools');
	$db = get_db();
	
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
	<div id="RC-label" class="two columns alpha">
		<label for="RC"><?php echo __('Translations Cache'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Update all translations after language files have been changed manually, either in the active theme (if the relative Plugin setting is on) or in any active Plugin.'); ?></p>
		<a id="RC" class="button green" href="<?php echo url('admin-tools/index/reset-cache'); ?>"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="BD-label" class="two columns alpha">
		<label for="BD"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation"><?php echo __('Backup the entire Omeka database into a SQL file') . $this->lastBackupDateTime . '.' .(get_option('admin_tools_backup_sessions_ignore') ? ' ' . __('During the backup, data from "Sessions" table will be <b>ignored</b>.') : '') . (get_option('admin_tools_backup_download') ? ' ' . __('A copy of the file will be available for download') . (get_option('admin_tools_backup_compress') ? __(', <b>compressed</b> in GZip format.') : '.') : '.'); ?></p>
		<a id="BD" class="button green" href="<?php echo url('admin-tools/index/backup'); ?>"><?php echo __('Backup Database'); ?></a>
	</div>
</div>

<div class="field">
	<div id="PLU-label" class="two columns alpha">
		<label for="PLU"><?php echo __('Plugins'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<?php
			// looks for patly installed plugins
			$path = PLUGIN_DIR;
			$directories = str_replace($path . '/', '', glob($path . '/*', GLOB_ONLYDIR));
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Plugin') . " WHERE name NOT IN ('" . implode("','", $directories) . "')";
			$total_invalid = $db->fetchOne($sql);
			
			$sql = 'SELECT COUNT(*) AS total, SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active FROM ' . $db->getTableName('Plugin');
			$row = $db->fetchRow($sql);

			echo '<p class="explanation">';
			if ($row['total'] == 0) {
				// case no installed plugin
				echo __('No Plugin is installed in the system.');
				$plu_btns = '<a id="PLU_ON" class="button at_disabled" disabled>' . __('Activate All Plugins') . '</a>
							<a id="PLU_OFF" class="button at_disabled" disabled>' . __('Deactivate All Plugins') . '</a>';
			} else {
				echo __(plural('The system contains <b>1</b> installed Plugin', 'The system contains <b>%s</b> installed Plugins', $row['total']), $row['total']);
				echo ', ';
				if ($row['total'] == $row['active']) {
					// case all installed plugins are active
					echo __(plural('which is already active.', 'which are all already active.', $row['total']));
					$plu_btns = '<a id="PLU_ON" class="button at_disabled" disabled>' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button green" href="' . url('admin-tools/index/plugins-deactivate') . '">' . __('Deactivate All Plugins') . '</a>';
				} elseif ($row['active'] == 0) {
					// case all installed plugins are inactive
					echo __(plural('which is not active.', 'which are all not active.', $row['total']));
					$plu_btns = '<a id="PLU_ON" class="button green" href="' . url('admin-tools/index/plugins-activate') . '">' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button at_disabled" disabled>' . __('Deactivate All Plugins') . '</a>';
				} else {
					// case else
					echo __(plural('of which <b>1</b> active.', 'of which <b>%d</b> active.', $row['active']), $row['active']);
					$plu_btns = '<a id="PLU_ON" class="button green" href="' . url('admin-tools/index/plugins-activate') . '">' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button green" href="' . url('admin-tools/index/plugins-deactivate') . '">' . __('Deactivate All Plugins') . '</a>';
				}
			}
			
			echo ' ';
			if ($total_invalid > 0) {
				echo __(plural('<b>1</b> Plugin appears to be invalid/damaged, and can be safely removed.', '<b>%d</b> Plugins appear to be invalid/damaged, and can be safely removed.', $total_invalid), $total_invalid);
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
		<?php if ($this->sessionsCount > 0 && get_option('admin_tools_sessions_graph')): ?>
			<canvas id="sessionsChart" style="width:100%; height: 200px; margin-bottom: 1em"></canvas>
			<script>
				<?php
					// get number of sessions grouped by date
					$sql = "SELECT count(id) AS total, DATE(FROM_UNIXTIME(modified)) AS session_date FROM omeka_sessions GROUP BY session_date";
					$sessions = $db->query($sql)->fetchall();
					// limit number of entried to sessionMaxLifeTime
					if (count($sessions) > $this->sessionMaxLifeTime) {
						array_splice($sessions, 0, count($sessions) - $this->sessionMaxLifeTime);
					}
					// create coordinaters for graph
					foreach ($sessions as $row) {
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
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Session') . " WHERE modified < UNIX_TIMESTAMP(NOW() - INTERVAL 1 YEAR)";
			if ($db->fetchOne($sql) > 0) {
				echo '<a id="TSTY" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/year') . '">' . __('Trim sessions (+1 year)') . '</a>';
			} else {
				echo '<a id="TSTY" class="button at_disabled" disabled>' . __('Trim sessions (+1 year)') . '</a>';
			}

			// adds button to prune sessions over 1 month old - disabled if there are none
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Session') . " WHERE modified < UNIX_TIMESTAMP(NOW() - INTERVAL 1 MONTH)";
			if ($db->fetchOne($sql) > 0) {
				echo '<a id="TSTM" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/month') . '">' . __('Trim sessions (+1 month)') . '</a>';
			} else {
				echo '<a id="TSTM" class="button at_disabled" disabled>' . __('Trim sessions (+1 month)') . '</a>';
			}

			// adds button to prune sessions over 1 week old - disabled if there are none
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Session') . " WHERE modified < UNIX_TIMESTAMP(NOW() - INTERVAL 1 WEEK)";
			if ($db->fetchOne($sql) > 0) {
				echo '<a id="TSTW" class="button green" href="' . url('admin-tools/index/trim-sessions/rng/week') . '">' . __('Trim sessions (+1 week)') . '</a>';
			} else {
				echo '<a id="TSTM" class="button at_disabled" disabled>' . __('Trim sessions (+1 week)') . '</a>';
			}

			// adds button to prune sessions over 1 day old - disabled if there are none
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Session') . " WHERE modified < UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY)";
			if ($db->fetchOne($sql) > 0) {
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
			$sql = 'SELECT COUNT(*) FROM ' . $db->getTableName('Tag') . ' WHERE id IN (SELECT id FROM (SELECT t1.id FROM ' . $db->getTableName('Tag') . ' t1 LEFT OUTER JOIN ' . $db->getTableName('RecordsTag') . ' rt ON t1.id = rt.tag_id GROUP BY t1.id HAVING COUNT(rt.id) = 0) tmp)';
			$total_unused_tags = $db->fetchOne($sql);
			$sql = 'SELECT COUNT(*) FROM ' . $db->getTableName('Item') . ' AS `items` LEFT OUTER JOIN ' . $db->getTableName('RecordsTag') . ' AS `records_tags` ON `items`.id = `records_tags`.`record_id` WHERE `records_tags`.`tag_id` IS NULL';
			$total_untagged_items = $db->fetchOne($sql);

			echo '<p class="explanation">';
			if ($total_unused_tags > 0) {
				echo __(plural('<b>1</b> Tag has no correspondence to any Items', '<b>%d</b> Tags have no correspondence to any Item', $total_unused_tags), $total_unused_tags);
				if ($total_untagged_items > 0) {
					echo ', ' . __('and') . ' ' . __(plural('<b>1</b> Item has no Tags associated.', '<b>%d</b> Items have no Tags associated.', $total_untagged_items), $total_untagged_items);
				} else {
					echo ', ' . __('but') . ' ' . __('all Items have at least one Tag associated.');
				}
			} else {
				echo __('All Tags are associated to at least one Item');
				if ($total_untagged_items > 0) {
					echo ', ' . __('but') . ' ' . __(plural('<b>1</b> Item has no Tags associated.', '<b>%d</b> Items have no Tags associated.', $total_untagged_items), $total_untagged_items);
				} else {
					echo ', ' . __('and') . ' ' . __('all Items have at least one Tag associated.');
				}
			}
			echo '</p>';

			if ($total_unused_tags > 0) {
				echo '<a id="DUT" class="button green" href="' . url('admin-tools/index/delete-tags') . '">' . __(plural('Delete Unused Tag', 'Delete Unused Tags', $total_unused_tags)) . '</a>';
			} else {
				echo '<a id="DUT" class="button at_disabled" disabled>' . __('Delete Unused Tags') . '</a>';
			}

			if ($total_untagged_items > 0) {
				echo '<a id="SUI" class="button green" href="' . url('items/browse?search=&advanced-joiner=and&advanced-element_id=&advanced-type=&advanced-terms=&has-tags=0') . '">' . __(plural('Show Untagged Item', 'Show Untagged Items', $total_untagged_items)) . '</a>';
			} else {
				echo '<a id="SUI" class="button at_disabled" disabled>' . __('Show Untagged Items') . '</a>';
			}
		?>
	</div>
</div>

<?php echo foot(); ?>
