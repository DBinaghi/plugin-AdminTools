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
		<p class="explanation"><?php echo __('Reset cache and update all translations after one or more language files have been changed manually') . (get_option('admin_tools_translations_theme') ? ', ' . __('including files that are part of the <b>active theme</b>.') : '.'); ?></p>
		<a id="TRC" class="button green" href="<?php echo url('admin-tools/index/reset-cache'); ?>"><?php echo __('Reset Cache'); ?></a>
	</div>
</div>

<div class="field">
	<div id="TDB-label" class="two columns alpha">
		<label for="TDB"><?php echo __('Database Backup'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php 
				echo __('Backup the entire Omeka database into a SQL file, saved in Omeka\'s "Files" folder') . $this->lastBackupDateTime . '.' . 
					(get_option('admin_tools_backup_sessions_ignore') ? ' ' . __('During the backup, data from "Sessions" table will be <b>ignored</b>.') : '') . 
					(get_option('admin_tools_backup_download') ? ' ' . __('A copy of the file will be available for download') . (get_option('admin_tools_backup_compress') ? __(', <b>compressed</b> in GZip format.') : '.') : '.');
			?>
		</p>
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
			echo $this->pluginsDescription;
			echo '</p>';
			
			if ($this->pluginsInstalled == 0) { // no installed plugin
				$plu_btns = '<a id="PLU_ON" class="button at_disabled" disabled>' . __('Activate All Plugins') . '</a>
							<a id="PLU_OFF" class="button at_disabled" disabled>' . __('Deactivate All Plugins') . '</a>';
			} else {
				if ($this->pluginsInstalled == $this->pluginsActive) { // all installed plugins are active
					$plu_btns = '<a id="PLU_ON" class="button at_disabled" disabled>' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button green" href="' . url('admin-tools/index/plugins-deactivate') . '">' . __('Deactivate All Plugins') . '</a>';
				} elseif ($this->pluginsActive == 0) { // all installed plugins are not active
					$plu_btns = '<a id="PLU_ON" class="button green" href="' . url('admin-tools/index/plugins-activate') . '">' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button at_disabled" disabled>' . __('Deactivate All Plugins') . '</a>';
				} else {
					$plu_btns = '<a id="PLU_ON" class="button green" href="' . url('admin-tools/index/plugins-activate') . '">' . __('Activate All Plugins') . '</a>
								<a id="PLU_OFF" class="button green" href="' . url('admin-tools/index/plugins-deactivate') . '">' . __('Deactivate All Plugins') . '</a>';
				}
			}
			
			if ($this->pluginsInvalid > 0) {
				$plu_btns .= '<a id="PLU_REMOVE" class="button green" href="' . url('admin-tools/index/plugins-remove-invalid') . '">' . __('Remove Invalid Plugins') . '</a>';
			} else {
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
		<p class="explanation"><?php echo __('Trim Omeka\'s "Sessions" table') . (get_option('admin_tools_sessions_count') ? ' ' . __('(at the moment, the table contains <strong>%s</strong> records)', number_format($this->sessionsCount)) : '') . __(', deleting sessions older than 1 year/month/week/day or all expired ones (at the moment, sessions expire after <strong>%s</strong> days).', $this->sessionsMaxLifeTime); ?></p>
		<?php if ((bool)get_option('admin_tools_sessions_graph')): ?>
			<canvas id="sessionsChart" style="width:100%; height: 200px; margin-bottom: 1em"></canvas>
			<script>
				new Chart("sessionsChart", {
					type: "line",
					data: {
						labels: <?= json_encode($this->chartLabels) ?>,
						datasets: [{
							data: <?= json_encode($this->chartData) ?>,
							borderWidth: 2,
							tension: 0.1
						}]
					},
					options: {
						plugins: {
							legend: { display: false },
							title: {
								display: true,
								text: '<?= __('Sessions in the last %d days', $this->sessionsMaxLifeTime) ?>'
							}
						},
						scales: {
							y: { beginAtZero: true }
						}
					}
				});
			</script>
		<?php endif; ?>

		<?php 
			// adds button to prune sessions over 1 year old - disabled if there are none
			if ($this->sessionsYearCount > 0) {
				echo '<a id="TSTY" class="button green" href="' . url('admin-tools/index/sessions-trim/rng/year') . '">' . __('Trim sessions (+1 year)') . '</a>';
			} else {
				echo '<a id="TSTY" class="button at_disabled" disabled>' . __('Trim sessions (+1 year)') . '</a>';
			}

			// adds button to prune sessions over 1 month old - disabled if there are none
			if ($this->sessionsMonthCount > 0) {
				echo '<a id="TSTM" class="button green" href="' . url('admin-tools/index/sessions-trim/rng/month') . '">' . __('Trim sessions (+1 month)') . '</a>';
			} else {
				echo '<a id="TSTM" class="button at_disabled" disabled>' . __('Trim sessions (+1 month)') . '</a>';
			}

			// adds button to prune sessions over 1 week old - disabled if there are none
			if ($this->sessionsWeekCount > 0) {
				echo '<a id="TSTW" class="button green" href="' . url('admin-tools/index/sessions-trim/rng/week') . '">' . __('Trim sessions (+1 week)') . '</a>';
			} else {
				echo '<a id="TSTM" class="button at_disabled" disabled>' . __('Trim sessions (+1 week)') . '</a>';
			}

			// adds button to prune sessions over 1 day old - disabled if there are none
			if ($this->sessionsDayCount > 0) {
				echo '<a id="TSTD" class="button green" href="' . url('admin-tools/index/sessions-trim/rng/day') . '">' . __('Trim sessions (+1 day)') . '</a>';
			} else {
				echo '<a id="TSTD" class="button at_disabled" disabled>' . __('Trim sessions (+1 day)') . '</a>';
			}

			// adds button to prune sessions expired - disabled if there are none
			if ($this->sessionsExpiredCount > 0) {
				echo '<a id="TSTE" class="button green" href="' . url('admin-tools/index/sessions-trim/rng/expired') . '">' . __('Trim sessions (expired)') . '</a>';
			} else {
				echo '<a id="TSTE" class="button at_disabled" disabled>' . __('Trim sessions (expired)') . '</a>';
			}
		?>   
	</div>
</div>

<div class="field">
	<div id="DUT-label" class="two columns alpha">
		<label for="DUT"><?php echo __('Tags'); ?></label>
	</div>
	<div class="inputs five columns omega">
		<?php
			// explanation
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

			// buttons
			if ($this->tagsUnused > 0) {
				echo '<a id="DUT" class="button green" href="' . url('admin-tools/index/tags-delete') . '">' . __(plural('Delete Unused Tag', 'Delete Unused Tags', $this->tagsUnused)) . '</a>';
			} else {
				echo '<a id="DUT" class="button at_disabled" disabled>' . __('Delete Unused Tags') . '</a>';
			}

			if ($this->itemsUntagged > 0) {
				echo '<a id="SUI" class="button green" href="' . url('items/browse?search=&advanced-joiner=and&advanced-element_id=&advanced-type=&advanced-terms=&has-tags=0') . '">' . __(plural('Show Untagged Item', 'Show Untagged Items', $this->itemsUntagged)) . '</a>';
			} else {
				echo '<a id="SUI" class="button at_disabled" disabled>' . __('Show Untagged Items') . '</a>';
			}
		?>

		<?php if (get_option('admin_tools_tags_similar')): ?>
			<script>
				    var AdminToolsIndex = <?php echo json_encode(array(
					'tagsSimilarURL' => url('admin-tools/index/tags-find-similar'),
					'tagsMergeURL'   => url('admin-tools/index/tags-merge'),
					'csrfToken'      => $this->csrfToken,
					'findSimilar'    => __('Find Similar Tags'),
					'searching'      => __('Searching...'),
					'noSimilar'      => __('No similar Tags found.'),
					'found'          => __('<b>%d</b> possible duplicates found'),
					'keepLeft'       => __('Keep left'),
					'keepRight'      => __('Keep right'),
					'mergeConfirm'   => __('The other Tag will be merged into "%s" and deleted. Proceed?'),
					'mergeError'     => __('An error occurred during the merge.'),
					'error'          => __('An error occurred while searching for similar Tags.'),
					'pageSize'       => (int)get_option('admin_tools_tags_similarity_results'),
					'prev'           => __('Prev'),
					'next'           => __('Next'),
					'pageInfo'       => __('page %1 of %2')
				)); ?>;
			</script>
			<a id="tags-find-similar" class="button green"><?php echo __('Find Similar Tags'); ?></a>
			<div id="tags-similar-results" style="margin-top: 1em;"></div>
		<?php endif; ?>
	</div>
</div>

<?php echo foot(); ?>
