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

$base = $this->offering->link() . '&active=pages';
?>
	<div id="attachments">
		<form action="<?php echo JRoute::_($base); ?>" id="adminForm" method="post" enctype="multipart/form-data">
			<fieldset>
				<div id="themanager" class="manager">
					<iframe style="border:1px solid #eee;margin-top: 0;overflow-y:auto;" src="<?php echo JRoute::_($base . '&action=list&tmpl=component&page=' . $this->page->get('id') . '&section_id=' . $this->page->get('section_id')); ?>" name="imgManager" id="imgManager" width="98%" height="180"></iframe>
				</div>
			</fieldset>

			<fieldset>
				<table>
					<tbody>
						<tr>
							<td><input type="file" name="upload" id="upload" /></td>
						</tr>
						<tr>
							<td><input type="submit" value="<?php echo JText::_('PLG_COURSES_PAGES_UPLOAD'); ?>" /></td>
						</tr>
					</tbody>
				</table>

				<?php echo JHTML::_('form.token'); ?>

				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
				<input type="hidden" name="gid" value="<?php echo $this->escape($this->course->get('alias')); ?>" />
				<input type="hidden" name="page" value="<?php echo $this->escape($this->page->get('id')); ?>" />
				<input type="hidden" name="section_id" value="<?php echo $this->escape($this->page->get('section_id')); ?>" />
				<input type="hidden" name="active" value="pages" />
				<input type="hidden" name="action" value="upload" />
				<input type="hidden" name="offering" value="<?php echo $this->offering->alias(); ?>" />
			</fieldset>
		</form>
<?php if ($this->getError()) { ?>
		<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>
	</div>