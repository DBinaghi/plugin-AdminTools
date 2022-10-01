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
						$request->setModuleName('admin-tools');
						$request->setControllerName('index');
						$request->setActionName('index');
					} elseif ((bool)get_option('admin_tools_maintenance_scope_extended') && !in_array($user->role, array('admin','super'))) {
						$request->setModuleName('admin-tools');
						$request->setControllerName('index');
						$request->setActionName('index');
					}
				}
			}
		}
	}
?>
