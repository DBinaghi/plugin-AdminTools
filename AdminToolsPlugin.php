<?php
	/**
	 * AdminTools plugin
	 *
	 * @package AdminTools
	 * @copyright Copyright 2022-2026 Daniele Binaghi et al.
	 * @license https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html CeCILL v2.1
	 */

	/**
	 * AdminTools plugin class
	 * 
	 * @package AdminTools
	 */

	define('ADMIN_TOOLS_BACKUP_FILENAME', '../files/DatabaseBackup.sql');

	// Helper functions for database backup
	require_once 'views/helpers/Mysqldump.php';

	class AdminToolsPlugin extends Omeka_Plugin_AbstractPlugin
	{
		protected $_hooks = array(
			'install',
			'uninstall',
			'initialize',
			'config',
			'config_form',
			'admin_footer',
			'public_head',
			'public_footer',
			'neatline_public_static',
			'define_acl',
			'admin_tags_browse',
			'admin_plugins_browse',
			'admin_items_search',
			'items_browse_sql'
		);

		protected $_filters = array(
			'items_browse_params',
			'collections_browse_params',
			'exhibits_browse_params',
			'admin_navigation_global',
			'admin_navigation_main',
			'public_navigation_admin_bar' 
		);

		public function hookInstall()
		{
			set_option('admin_tools_maintenance_active', 0);
			set_option('admin_tools_maintenance_title', 'Site under maintenance');
			set_option('admin_tools_maintenance_message', 
				'<p>Sorry for the inconvenience, but we’re performing some maintenance at the moment. We’ll be back online shortly!</p>
				<br>
				<p>- the Team -</p>'
			);
			set_option('admin_tools_maintenance_scope_extended', 0);
			set_option('admin_tools_usermanual_url', '');
			set_option('admin_tools_usermanual_label', 'User manual');
			set_option('admin_tools_usermanual_link_positions', '');
			set_option('admin_tools_cookiebar_active', 0);
			set_option('admin_tools_cookiebar_all_users', 0);
			set_option('admin_tools_cookiebar_text', 'We use cookies to track usage of this project.');
			set_option('admin_tools_cookiebar_position', 'top');
			set_option('admin_tools_cookiebar_policy_url', '');
			set_option('admin_tools_limit_visibility_to_own_items_roles', '');
			set_option('admin_tools_limit_visibility_to_own_collections_roles', '');
			set_option('admin_tools_limit_visibility_to_own_exhibits_roles', '');
			set_option('admin_tools_public_edit_link_types', '');
			set_option('admin_tools_public_edit_link_blank', 1);
			set_option('admin_tools_backup_sessions_ignore', 1);
			set_option('admin_tools_backup_compress', 0);
			set_option('admin_tools_backup_download', 1);
			set_option('admin_tools_sessions_count', 0);
			set_option('admin_tools_sessions_graph', 0);
			set_option('admin_tools_unused_tags_btn', 0);
			set_option('admin_tools_has_tags', 0);
			set_option('admin_tools_plugins_btns', 0);
			set_option('admin_tools_translations_theme', 0);
		}

		public function hookUninstall()
		{
			delete_option('admin_tools_maintenance_active');
			delete_option('admin_tools_maintenance_title');
			delete_option('admin_tools_maintenance_message');
			delete_option('admin_tools_maintenance_scope_extended');
			delete_option('admin_tools_usermanual_url');
			delete_option('admin_tools_usermanual_label');
			delete_option('admin_tools_usermanual_link_positions');
			delete_option('admin_tools_cookiebar_active');
			delete_option('admin_tools_cookiebar_all_users');
			delete_option('admin_tools_cookiebar_text');
			delete_option('admin_tools_cookiebar_position');
			delete_option('admin_tools_cookiebar_policy_url');
			delete_option('admin_tools_limit_visibility_to_own_items_roles');
			delete_option('admin_tools_limit_visibility_to_own_collections_roles');
			delete_option('admin_tools_limit_visibility_to_own_exhibits_roles');
			delete_option('admin_tools_public_edit_link_types');
			delete_option('admin_tools_public_edit_link_blank');
			delete_option('admin_tools_backup_sessions_ignore');
			delete_option('admin_tools_backup_compress');
			delete_option('admin_tools_backup_download');
			delete_option('admin_tools_sessions_count');
			delete_option('admin_tools_sessions_graph');
			delete_option('admin_tools_unused_tags_btn');
			delete_option('admin_tools_has_tags');
			delete_option('admin_tools_plugins_btns');
			delete_option('admin_tools_translations_theme');
		}

		public function hookInitialize()
		{
			add_translation_source(dirname(__FILE__) . '/languages');
			if ((bool)get_option('admin_tools_translations_theme')) add_translation_source(dirname(dirname(dirname(__FILE__))) . '/themes/' . get_option('public_theme') . '/languages');

			$front = Zend_Controller_Front::getInstance();
			$front->registerPlugin(new AdminTools_Controller_Plugin_Maintenance);
		}

		public function hookConfig($args)
		{
			$post = $args['post'];
			set_option('admin_tools_maintenance_title',							$post['admin_tools_maintenance_title']);
			set_option('admin_tools_maintenance_message',						$post['admin_tools_maintenance_message']);
			set_option('admin_tools_maintenance_scope_extended',				$post['admin_tools_maintenance_scope_extended']);
			set_option('admin_tools_usermanual_url',							$post['admin_tools_usermanual_url']);
			set_option('admin_tools_usermanual_label',							$post['admin_tools_usermanual_label']);
			set_option('admin_tools_usermanual_link_positions',					(isset($post['admin_tools_usermanual_link_positions']) ? serialize($post['admin_tools_usermanual_link_positions']) : ''));
			set_option('admin_tools_cookiebar_active',							$post['admin_tools_cookiebar_active']);
			set_option('admin_tools_cookiebar_all_users',						$post['admin_tools_cookiebar_all_users']);
			set_option('admin_tools_cookiebar_text',							$post['admin_tools_cookiebar_text']);
			set_option('admin_tools_cookiebar_position',						$post['admin_tools_cookiebar_position']);
			set_option('admin_tools_cookiebar_policy_url',						$post['admin_tools_cookiebar_policy_url']);
			set_option('admin_tools_limit_visibility_to_own_items_roles',		(isset($post['admin_tools_limit_visibility_to_own_items_roles']) ? serialize($post['admin_tools_limit_visibility_to_own_items_roles']) : ''));
			set_option('admin_tools_limit_visibility_to_own_collections_roles',	(isset($post['admin_tools_limit_visibility_to_own_collections_roles']) ? serialize($post['admin_tools_limit_visibility_to_own_collections_roles']) : ''));
			set_option('admin_tools_limit_visibility_to_own_exhibits_roles',	(isset($post['admin_tools_limit_visibility_to_own_exhibits_roles']) ? serialize($post['admin_tools_limit_visibility_to_own_exhibits_roles']) : ''));
			set_option('admin_tools_public_edit_link_types',					(isset($post['admin_tools_public_edit_link_types']) ? serialize($post['admin_tools_public_edit_link_types']) : ''));
			set_option('admin_tools_public_edit_link_blank',					$post['admin_tools_public_edit_link_blank']);
			set_option('admin_tools_backup_sessions_ignore',					$post['admin_tools_backup_sessions_ignore']);
			set_option('admin_tools_backup_compress',							$post['admin_tools_backup_compress']);
			set_option('admin_tools_backup_download',							$post['admin_tools_backup_download']);
			set_option('admin_tools_sessions_count',							$post['admin_tools_sessions_count']);
			set_option('admin_tools_sessions_graph',							$post['admin_tools_sessions_graph']);
			set_option('admin_tools_unused_tags_btn',							$post['admin_tools_unused_tags_btn']);
			set_option('admin_tools_has_tags',									$post['admin_tools_has_tags']);
			set_option('admin_tools_plugins_btns',								$post['admin_tools_plugins_btns']);
			set_option('admin_tools_translations_theme',						$post['admin_tools_translations_theme']);
		}

		public function hookConfigForm()
		{
			include 'config_form.php';
		}

		public function hookAdminFooter()
		{
			if (null !== current_user() && get_option('admin_tools_usermanual_link_positions') != '') {
				$positions = unserialize(get_option('admin_tools_usermanual_link_positions'));
				if (!empty($positions) && in_array('Footer', $positions)) {
					$url = get_option('admin_tools_usermanual_url');
					$label = get_option('admin_tools_usermanual_label');
					if ($url != '') {
						echo '<p class=\'left\' style=\'margin-right: 20px\'><a href=\'' . $url . '\' target=\'_blank\'>' . ($label != '' ? $label : __('User Manual')) . '</a></p>';
					}
				}
			}
		}

		public function hookPublicHead() 
		{
			$user = current_user();
			if (!isset($user) && (bool)get_option('admin_tools_cookiebar_active')) {
				queue_js_file('cookie_bar');
				queue_css_file('cookie_bar');
			}
		}

		public function hookPublicFooter() 
		{
			$user = current_user();
			if ((bool)get_option('admin_tools_cookiebar_active') && (!isset($user) || (bool)get_option('admin_tools_cookiebar_all_users'))) {
				echo get_view()->partial('cookie_bar.php', array(
					'message' => get_option('admin_tools_cookiebar_text'),
					'policyButton' => (get_option('admin_tools_cookiebar_policy_url') != '' ? 1 : 0),
					'policyURL' => get_option('admin_tools_cookiebar_policy_url'),
					'bottom' => (get_option('admin_tools_cookiebar_position') == 'bottom' ? 1 : 0)
				));
			}
		}

		public function hookNeatlinePublicStatic($exhibit)
		{
			$user = current_user();
			if ((bool)get_option('admin_tools_cookiebar_active') && (!isset($user) || (bool)get_option('admin_tools_cookiebar_all_users'))) {
				queue_js_file('cookie_bar');
				queue_css_file('cookie_bar');
				echo get_view()->partial('cookie_bar.php', array(
					'message' => get_option('admin_tools_cookiebar_text'),
					'policyButton' => (get_option('admin_tools_cookiebar_policy_url') != '' ? 1 : 0),
					'policyURL' => get_option('admin_tools_cookiebar_policy_url'),
					'bottom' => (get_option('admin_tools_cookiebar_position') == 'bottom' ? 1 : 0)
				));
			}
		}

		public function hookDefineAcl($args)
		{
			$acl = $args['acl']; // get the Zend_Acl

			$indexResource = new Zend_Acl_Resource('AdminTools_Index');
			$acl->add($indexResource);
			$acl->allow(array('super'), array('AdminTools_Index'));
			$acl->deny(array('admin'), array('AdminTools_Index'));
		}

		public function filterItemsBrowseParams($params)
		{
			if (!is_admin_theme()) return $params;

			$user = current_user();
			if (get_option('admin_tools_limit_visibility_to_own_items_roles') != '') {
				$limitedRoles = unserialize(get_option('admin_tools_limit_visibility_to_own_items_roles'));
				if ($user && !empty($limitedRoles) && in_array($user->role, $limitedRoles)) {
					$params['user'] = $user->id;
				}
			}
			return $params;
		}

		public function filterCollectionsBrowseParams($params)
		{
			if (!is_admin_theme()) return $params;

			$user = current_user();
			if (get_option('admin_tools_limit_visibility_to_own_collections_roles') != '') {
				$limitedRoles = unserialize(get_option('admin_tools_limit_visibility_to_own_collections_roles'));
				if ($user && !empty($limitedRoles) && in_array($user->role, $limitedRoles)) {
					$params['user'] = $user->id;
				}
			}
			return $params;
		}

		public function filterExhibitsBrowseParams($params)
		{
			if (!is_admin_theme() || !plugin_is_active('ExhibitBuilder')) return $params;

			$user = current_user();
			if (get_option('admin_tools_limit_visibility_to_own_exhibits_roles') <> '') {
				$limitedRoles = unserialize(get_option('admin_tools_limit_visibility_to_own_exhibits_roles'));
				if ($user && !empty($limitedRoles) && in_array($user->role, $limitedRoles)) {
					$params['user'] = $user->id;
				}
			}
			return $params;
		}

		/**
		 * Adds links to Admin Topbar
		 */
		public function filterAdminNavigationGlobal($nav)
		{
			if (get_option('admin_tools_usermanual_link_positions') != '') {
				$positions = unserialize(get_option('admin_tools_usermanual_link_positions'));
				if (!empty($positions) && in_array('Topbar', $positions)) {
					$url = get_option('admin_tools_usermanual_url');
					$label = get_option('admin_tools_usermanual_label');
					if ($url != '') {
						$nav[] = array(
							'label' => ($label != '' ? $label : __('User Manual')),
							'uri' => $url,
							'target' => '_blank'
						);
					}
				}
			}

			if ((bool)get_option('admin_tools_maintenance_active')) {
				array_unshift($nav, array(
					'label' => __('** Maintenance Mode Active **'),
					'uri' => admin_url('/admin-tools'),
					'id' => 'maintenance_alert'
				));
			}

			return $nav;
		}

		/**
		 * Adds links to Admin sidebar
		 */
		public function filterAdminNavigationMain($nav)
		{
			$nav[] = array(
				'label' => __('Admin Tools'),
				'uri' => url('admin-tools'),
				'resource' => 'AdminTools_Index',
				'privilege' => 'index'
			);

			if (get_option('admin_tools_usermanual_link_positions') != '') {
				$positions = unserialize(get_option('admin_tools_usermanual_link_positions'));
				if (!empty($positions) && in_array('Sidebar', $positions)) {
					$url = get_option('admin_tools_usermanual_url');
					$label = get_option('admin_tools_usermanual_label');
					if ($url != '') {
						$nav[] = array(
							'label' => ($label != '' ? $label : __('User Manual')),
							'uri' => $url,
							'target' => '_blank'
						);
					}
				}
			}

			return $nav;
		}

		/**
		 * Adds links to Public top bar
		 */
		public function filterPublicNavigationAdminBar($navLinks) 
		{
			$user = current_user();
			$view = get_view();
			$publicEditLinkTypes = unserialize(get_option('admin_tools_public_edit_link_types')); 

			if (isset($user) && !empty($publicEditLinkTypes)) {
				$acl = get_acl();
				if (isset(get_view()->item) && $acl->isAllowed($user, $view->item, 'edit') && in_array('Items', $publicEditLinkTypes)) {
					$uri = admin_url('/items/edit/' . metadata('items', 'id'));
					$navLinks = $this->updateNavlinks($navLinks, 'Item', $uri);
				} elseif (isset(get_view()->collection) && $acl->isAllowed($user, $view->collection, 'edit') && in_array('Collections', $publicEditLinkTypes)) {
					$uri = admin_url('/collections/edit/' . metadata('collections', 'id'));
					$navLinks = $this->updateNavlinks($navLinks, 'Collection', $uri);
				} elseif (isset(get_view()->file) && $acl->isAllowed($user, $view->file, 'edit') && in_array('Files', $publicEditLinkTypes)) {
					$uri = admin_url('/files/edit/' . metadata('files', 'id'));
					$navLinks = $this->updateNavlinks($navLinks, 'File', $uri);
				} elseif (isset(get_view()->exhibit) && $acl->isAllowed($user, $view->exhibit, 'edit') && in_array('Exhibits', $publicEditLinkTypes)) {
					$uri = admin_url('/exhibits/edit/' . metadata('exhibit', 'id'));
					$navLinks = $this->updateNavlinks($navLinks, 'Exhibit', $uri);
				} elseif (isset(get_view()->simple_pages_page) && $acl->isAllowed($user, $view->simple_pages_page, 'edit') && in_array('Simple Pages', $publicEditLinkTypes)) {
					$record = $view->simple_pages_page;
					$uri = admin_url('/simple-pages/index/edit/id/' . $record->id);
					$navLinks = $this->updateNavlinks($navLinks, 'Simple Page', $uri);
				}
			}

			if ((bool)get_option('admin_tools_maintenance_active')) {
				array_unshift($navLinks, array(
					'label' => __('** Maintenance Mode Active **'),
					'uri' => admin_url('/admin-tools')
				));
			}

			return $navLinks;
		}

		/**
		 * Creates link for editing record
		 */
		public function updateNavlinks($navLinks, $type, $uri)
		{
			// Saves copy of last menu item - normally, the logout one - then removes it
			$lastLink = $navLinks;
			array_splice($lastLink, 0, -1);
			array_splice($navLinks, -1);

			// Creates new menu item, then adds it and finalize with saved last one
			$element = array(
				'label' => __('Edit') . ' ' . __($type),
				'uri' => $uri,
				'target' => (get_option('admin_tools_public_edit_link_blank') ? '_blank' : '')
			);
			$navLinks[] = $element;
			return array_merge($navLinks, $lastLink);
		}

		/**
		 * Adds delete empty tags button to admin/tags
		 */
		public function hookAdminTagsBrowse($args, $deleted=0, $html=null)
		{
			if ((bool)get_option('admin_tools_unused_tags_btn')) {
				if (!$args || !isset($args['tags'])) return;

				queue_css_file('admin-tools');

				include_once(__DIR__ . '/views/admin/javascripts/admin-tags-browse.js'); 
				$html  = '<form class="at_form hidden" action="' . url('admin-tools/index/delete-tags-browse') . '">';
				$html .= '<h2 style="margin-top:1em">' . __('Delete Tags') . '</h2>';
				$html .= '<p>' . __('Delete all Tags that have no correspondence with any record.') . '</p>';
				$html .= '<button class="big green button" type="submit">' . __('Delete Unused Tags') . '</button>';
				$html .= '</form>';
				echo $html;
			}
		}

		/**
		 * Adds activate and deactivate buttons to admin/plugins
		 */
		public function hookAdminPluginsBrowse($args, $deleted=0, $html=null)
		{
			if ((bool)get_option('admin_tools_plugins_btns')) {
				if (!$args || !isset($args['plugins'])) return;

				queue_css_file('admin-tools');

				include_once(__DIR__ . '/views/admin/javascripts/admin-plugins-browse.js'); 
				$html  = '<div id="activate_deactivate_btns" class="plugin hidden" style="display: block; padding-top: 0">';
				$html .= '<p class="explanation">' . __('Activate/Deactivate all Plugins at the same time.') . ' ' . __('Also, remove invalid or damaged Plugins.') . '</p>';
				$html .= '<div style="display: flex">';
				$html .= '<form class="at_form" action="' . url('admin-tools/index/plugins-activate-browse') . '" style="display: inline; margin-right: .5em">';
				$html .= '<button class="big green button" type="submit">' . __('Activate All Plugins') . '</button>';
				$html .= '</form>';
				$html .= '<form class="at_form" action="' . url('admin-tools/index/plugins-deactivate-browse') . '" style="display: inline; margin-right: .5em">';
				$html .= '<button class="big green button" type="submit">' . __('Deactivate All Plugins') . '</button>';
				$html .= '</form>';
				$html .= '<form class="at_form" action="' . url('admin-tools/index/plugins-remove-invalid-browse') . '" style="display: inline">';
				$html .= '<button class="big green button" type="submit">' . __('Remove Invalid Plugins') . '</button>';
				$html .= '</form>';
				$html .= '</div>';
				$html .= '</div>';
				echo $html;
			}
		}

		/**
		 * Hook for admin advanced search.
		 *
		 * @return string HTML
		 */
		public function hookAdminItemsSearch($args)
		{
			if ((bool)get_option('admin_tools_has_tags')) {
				include_once(__DIR__ . '/views/admin/javascripts/admin-items-search.js'); 
				echo $this->_itemsSearch($args);
			}
		}

		/**
		 * Append items search checkbox to the advanced search page.
		 *
		 * @return string HTML
		 */
		protected function _itemsSearch($args)
		{
			return '<div class="field hidden">
						<div class="two columns alpha">
							<label for="has-tags">' . __('With Tags/Without Tags') . '</label>
						</div>
						<div class="five columns omega inputs">
							' . $args['view']->formSelect('has-tags', @$_GET['has-tags'], array(), array('' => __('Choose'), '0' => __('Has No Tags Associated'), '1' => __('Has Tags Associated'))) . '
						</div>
					</div>';
		}

		/**
		 * Hook for items browse: adds has-tags parameter
		 *
		 * @param Omeka_Db_Select $select
		 * @param array $params
		 */
		public function hookItemsBrowseSql($args)
		{
			$params = $args['params'];
			if (!isset($params['has-tags']) || $params['has-tags'] == null) return;

			$select = $args['select'];
			if ($params['has-tags'] == 0) {
				$select->joinLeft(array('records_tags' => get_db()->getTableName('RecordsTag')), 'items.id = records_tags.record_id', array());
				$select->where('records_tags.tag_id IS NULL');
			} elseif ($params['has-tags'] == 1) {
				$select->joinLeft(array('records_tags' => get_db()->getTableName('RecordsTag')), 'items.id = records_tags.record_id', array());
				$select->where('records_tags.tag_id IS NOT NULL');
			}
		}
	}
?>
