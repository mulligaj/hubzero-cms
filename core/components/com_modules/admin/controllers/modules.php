<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_HZEXEC_') or die();

jimport('joomla.application.component.controlleradmin');

/**
 * Modules list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesControllerModules extends JControllerAdmin
{
	/**
	 * Method to clone an existing module.
	 * @since	1.6
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Session::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = Request::getVar('cid', array(), 'post', 'array');
		\Hubzero\Utility\Arr::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Lang::txt('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Lang::txts('COM_MODULES_N_MODULES_DUPLICATED', count($pks)));
		}
		catch (Exception $e)
		{
			Notify::error($e->getMessage());
		}

		$this->setRedirect(Route::url('index.php?option=com_modules&view=modules', false));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Module', $prefix = 'ModulesModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
