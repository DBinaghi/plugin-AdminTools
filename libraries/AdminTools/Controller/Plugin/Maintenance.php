<?php
	class AdminTools_Controller_Plugin_Maintenance extends Zend_Controller_Plugin_Abstract
	{
		public function routeShutdown(Zend_Controller_Request_Abstract $request)
		{
			if ((bool)get_option('admin_tools_maintenance_active')) {
				$controller = $request->getControllerName();
				$action = $request->getActionName();
				$user = current_user();

				if ($controller != 'users' || ($action != 'login' && $action != 'logout')) {
					if (empty($user))  {
						// case user is not logged in
						$request->setModuleName('admin-tools');
						$request->setControllerName('maintenance');
						$request->setActionName('maintenance');
					} elseif ((bool)get_option('admin_tools_maintenance_scope_extended') && !in_array($user->role, array('admin', 'super'))) {
						// case user is logged in, but plugin is set to stop access to users without admin or super user permissions
						$request->setModuleName('admin-tools');
						$request->setControllerName('maintenance');
						$request->setActionName('maintenance');
					}
				}
			}
		}
	}
?>
