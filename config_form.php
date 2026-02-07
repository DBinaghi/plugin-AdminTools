<?php 
	$view = get_view();
	$bExhibit = (bool)(plugin_is_active('ExhibitBuilder'));
?>

<?php echo js_tag('vendor/tinymce/tinymce.min'); ?>
<?php echo js_tag('tabs'); ?>

<script type="text/javascript">
	jQuery(document).ready(function () {
		Omeka.Tabs.initialize();
		
		Omeka.wysiwyg({
			selector: '.html-editor'
		});
	});
</script>

<style type = "text/css">
	.boxes, .boxes-left {
		vertical-align: middle;
	}
	.boxes {
		text-align: center;
	}
	.field select {
		margin-bottom: 0;
	}
</style>

<p><?php echo flash(); ?></p>

<ul id="section-nav" class="navigation tabs">
	<li><a href="#tab1"><?php echo __('Database Backup'); ?></a></li>
	<li><a href="#tab2"><?php echo __('Cookie Bar'); ?></a></li>
	<li><a href="#tab3"><?php echo __('Edit Link'); ?></a></li>
	<li><a href="#tab4"><?php echo __('Limit Visibility'); ?></a></li>
	<li><a href="#tab5"><?php echo __('Site Maintenance'); ?></a></li>
	<li><a href="#tab6"><?php echo __('Plugins'); ?></a></li>
	<li><a href="#tab7"><?php echo __('Sessions'); ?></a></li>
	<li><a href="#tab8"><?php echo __('Tags'); ?></a></li>
	<li><a href="#tab9"><?php echo __('Translations'); ?></a></li>
	<li><a href="#tab10"><?php echo __('User Manual'); ?></a></li>
</ul>

<div id="tab1" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Database Backup') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Ignore Sessions')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, the backup will ignore the data contained in Sessions table.'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_backup_sessions_ignore', get_option('admin_tools_backup_sessions_ignore'), null, array('1', '0')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Compress Backup')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, the backup SQL file will be compressed (format: GZip).'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_backup_compress', get_option('admin_tools_backup_compress'), null, array('1', '0')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Download Backup')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, the backup SQL file will be downloadable (by default, it sits in the Omeka\'s files directory).'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_backup_download', get_option('admin_tools_backup_download'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab2" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Cookie Bar'); ?></h2>
	
	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_cookiebar_active', __('Active')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('If checked, a cookie bar with a warning will be shown to all visitors not logged in.'); ?>
			</p>
			<?php echo $view->formCheckbox('admin_tools_cookiebar_active', get_option('admin_tools_cookiebar_active'), null, array('1', '0')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_cookiebar_all_users', __('Extend To All Users')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('If checked, the cookie bar will be shown to all Public interface users.'); ?>
			</p>
			<?php echo $view->formCheckbox('admin_tools_cookiebar_all_users', get_option('admin_tools_cookiebar_all_users'), null, array('1', '0')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_cookiebar_text', __('Text')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('The text to be shown in the cookie bar.'); ?>
			</p>
			<?php echo $view->formText('admin_tools_cookiebar_text', get_option('admin_tools_cookiebar_text')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_cookiebar_position', __('Position')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('The position of the cookie bar.'); ?>
			</p>
			<?php echo $view->formSelect('admin_tools_cookiebar_position', get_option('admin_tools_cookiebar_position'), null, array('top' => __('Top of page'), 'bottom' => __('Bottom of page'))); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_cookiebar_policy_url', __('Privacy Policy URL')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('The URL of the Privacy Policy document for the website; if empty, no link will be shown.'); ?>
			</p>
			<?php echo $view->formText('admin_tools_cookiebar_policy_url', get_option('admin_tools_cookiebar_policy_url')); ?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab3" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Edit Link') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Content Types')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('Content types for which a link will be added to the public UI for quick editing (only when user is logged in).'); ?></p>

			<?php
				$contentTypes = array('Items', 'Collections', 'Exhibits', 'Files', 'Simple Pages');
				$publicEditLinkTypes = array();
				
				// retrieve configuration
				if (get_option('admin_tools_public_edit_link_types') <> '') {
					$publicEditLinkTypes = unserialize(get_option('admin_tools_public_edit_link_types'));
				}
				
				foreach ($contentTypes as $contentType) {
					if ($contentType != 'Exhibits' || $bExhibit) {
						echo '<label>' . $view->formCheckbox('admin_tools_public_edit_link_types[]', $contentType, array('checked'=> (!empty($contentTypes) ? in_array($contentType, $publicEditLinkTypes) : false) ? 'checked' : '')) . __($contentType) . '</label>';
					}
				}
			?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Open in New Tab')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, opens editing page in new tab (recommended).'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_public_edit_link_blank', get_option('admin_tools_public_edit_link_blank'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab4" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Limit Visibility') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Limited Roles')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('Roles allowed to see only their own records (in Admin mode). Please note that Super User\'s role cannot be limited.'); ?></p>

			<table id="facets_elements-table">
				<thead>
					<tr>
						<th class="boxes"><?php echo __('Role'); ?></th>
						<th class="boxes"><?php echo __('Item'); ?></th>
						<th class="boxes"><?php echo __('Collection'); ?></th>
						<th class="boxes"><?php echo __('Exhibit'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						// retrieve roles
						$userRoles = get_user_roles();
						unset($userRoles['super']);
						
						$limitedRolesItems = array();
						$limitedRolesCollections = array();
						$limitedRolesExhibits = array();
						
						// retrieve configuration
						if (get_option('admin_tools_limit_visibility_to_own_items_roles') <> '') {
							$limitedRolesItems = unserialize(get_option('admin_tools_limit_visibility_to_own_items_roles'));
						}
						if (get_option('admin_tools_limit_visibility_to_own_collections_roles') <> '') {
							$limitedRolesCollections = unserialize(get_option('admin_tools_limit_visibility_to_own_collections_roles'));
						}
						if (get_option('admin_tools_limit_visibility_to_own_exhibits_roles') <> '') {
							$limitedRolesExhibits = unserialize(get_option('admin_tools_limit_visibility_to_own_exhibits_roles'));
						}
						
						// display table content
						foreach ($userRoles as $role=>$label) {
							echo '<tr>';
							echo '<td class="boxes-left">' . __($label) . '</td>';
							echo '<td class="boxes">' . $view->formCheckbox('admin_tools_limit_visibility_to_own_items_roles[]', $role, array('checked'=> (!empty($limitedRolesItems) ? in_array($role, $limitedRolesItems) : false) ? 'checked' : '')) . '</td>';
							echo '<td class="boxes">' . $view->formCheckbox('admin_tools_limit_visibility_to_own_collections_roles[]', $role, array('checked'=> (!empty($limitedRolesCollections) ? in_array($role, $limitedRolesCollections) : false) ? 'checked' : '')) . '</td>';
							echo '<td class="boxes">' . ($bExhibit ? $view->formCheckbox('admin_tools_limit_visibility_to_own_exhibits_roles[]', $role, array('checked'=> (!empty($limitedRolesExhibits) ? in_array($role, $limitedRolesExhibits) : false) ? 'checked' : '')) : 'n/a') . '</td>';
							echo '</tr>';
						}
					
					?>			
				</tbody>
			</table>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab5" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Site Maintenance') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_maintenance_title', __('Title')); ?>
		</div>
		<div class='inputs five columns omega'>
			<p class="explanation">
				<?php echo __('The title of the message to display to visitors when the site is in maintenance mode.'); ?>
			</p>
			<?php echo $view->formText('admin_tools_maintenance_title', get_option('admin_tools_maintenance_title')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_maintenance_message', __('Message')); ?>
		</div>
		<div class='inputs five columns omega'>
			<p class="explanation">
				<?php echo __('The message to display to visitors when the site is in maintenance mode.'); ?>
			</p>
			<?php echo $view->formTextarea(
				'admin_tools_maintenance_message',
				get_option('admin_tools_maintenance_message'),
				array(
					'rows' => 5,
					'cols' => 60,
					'class' => array('textinput', 'html-editor'),
				 )
			); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_maintenance_scope_extended', __('Extend Scope')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('If checked, site will not be accessible even to logged-in users (excluding Super User and Admin roles).'); ?>
			</p>
			<?php echo $view->formCheckbox('admin_tools_maintenance_scope_extended', get_option('admin_tools_maintenance_scope_extended'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab6" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Plugins') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Show Buttons')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, buttons to activate or deactivate all plugins will be shown in the Plugins Browse page.'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_plugins_btns', get_option('admin_tools_plugins_btns'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab7" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Sessions') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Sessions Count')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, the amount of actual records in the Session table will be shown (in case of a very large number of records, Admin Tool plugin page could take some extra time to load).'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_sessions_count', get_option('admin_tools_sessions_count'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Sessions Graph')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, a graph showing the sessions total per day will be shown.'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_sessions_graph', get_option('admin_tools_sessions_graph'), null, array('1', '0')); ?>
		</div>
	</div>

	<div>&nbsp;</div>
</div>

<div id="tab8" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Tags') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Show Button')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, a button to delete all unused tags will be shown in the Tags Browse page.'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_unused_tags_btn', get_option('admin_tools_unused_tags_btn'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Add Search Option')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, an extra "With Tags/Without Tags" option will be added to the Items Advanced Search page.'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_has_tags', get_option('admin_tools_has_tags'), null, array('1', '0')); ?>
		</div>
	</div>
	<div>&nbsp;</div>
</div>

<div id="tab9" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('Translations') ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Add Theme Translations')?></label>	
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __('If checked, all translation files located in the "Languages" folder of the Public Theme will be used as well.'); ?></p>
			<?php echo $view->formCheckbox('admin_tools_translations_theme', get_option('admin_tools_translations_theme'), null, array('1', '0')); ?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>

<div id="tab10" style="border: 1px solid #d8d8d8; padding: 15px 15px;">
	<h2><?php echo __('User Manual'); ?></h2>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_usermanual_url', __('URL')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('The URL of the user manual to be made available to logged-in users.'); ?>
			</p>
			<?php echo $view->formText('admin_tools_usermanual_url', get_option('admin_tools_usermanual_url')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_usermanual_label', __('Label')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('The label to be shown to logged-in users.'); ?>
			</p>
			<?php echo $view->formText('admin_tools_usermanual_label', get_option('admin_tools_usermanual_label')); ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<?php echo $view->formLabel('admin_tools_usermanual_label', __('Link Position')); ?>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('Choose where the user manual link should be displayed.'); ?>
			</p>
			<?php
				$positions = array('Sidebar', 'Topbar', 'Footer');
				$usermanualLinkPositions = array();
				
				// retrieve configuration
				if (get_option('admin_tools_usermanual_link_positions') <> '') {
					$usermanualLinkPositions = unserialize(get_option('admin_tools_usermanual_link_positions'));
				}
				
				foreach ($positions as $position) {
					echo '<label>' . $view->formCheckbox('admin_tools_usermanual_link_positions[]', $position, array('checked'=> (!empty($usermanualLinkPositions) ? in_array($position, $usermanualLinkPositions) : false) ? 'checked' : '')) . __($position) . '</label>';
				}
			?>
		</div>
	</div>
	
	<div>&nbsp;</div>
</div>
