<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>

<div class="group-page group-page-notice notice-info">
	<h4>Group Page is Currently Not Published</h4>
	<p>This page has been marked <strong>unpublished</strong> by one of the group managers. If you would like to publish this page click the link below: </p>
	<?php 
		$link = JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=publish&pageid='.$this->page->get('id')); 
	?>
	<p><a href="<?php echo $link . '&return=' . base64_encode(JURI::getInstance()->toString()); ?>"><?php echo rtrim(JURI::getInstance()->base(), DS) . $link; ?></a></p>
</div>