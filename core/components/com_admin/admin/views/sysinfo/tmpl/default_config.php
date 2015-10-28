<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_HZEXEC_') or die();
?>
<fieldset class="adminform">
	<legend><?php echo Lang::txt('COM_ADMIN_CONFIGURATION_FILE'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="300">
						<?php echo Lang::txt('COM_ADMIN_SETTING'); ?>
					</th>
					<th>
						<?php echo Lang::txt('COM_ADMIN_VALUE'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&#160;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->config as $key=>$value):?>
					<tr>
						<td>
							<?php echo $key;?>
						</td>
						<td>
							<?php
							if (is_array($value))
							{
								foreach ($value as $ky => $val)
								{
									echo htmlspecialchars($ky, ENT_QUOTES) .' = ' . htmlspecialchars($val, ENT_QUOTES) . '<br />';
								}
							}
							else
							{
								echo htmlspecialchars($value, ENT_QUOTES);
							}
							?>
						</td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
</fieldset>
