<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$this->css();
?>

<div class="<?php echo $this->module->module; ?>">
	<?php if (count($this->unapproved) > 0) : ?>
		<div class="pending-users">
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=users&filter.approved=0'); ?>">
				<span class="count"><?php echo count($this->unapproved); ?></span>
				<?php echo JText::plural('MOD_USERS_REQUIRE_APPROVAL', count($this->unapproved)); ?>
			</a>
		</div>
	<?php else : ?>
		<div class="none"><?php echo JText::_('MOD_USERS_ALL_CLEAR'); ?></div>
	<?php endif; ?>
</div>