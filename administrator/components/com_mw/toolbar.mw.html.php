<?php
/**
 * @package		HUBzero CMS
 * @author		Shawn Rice <zooley@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

//----------------------------------------------------------
// Class for toolbar generation
//----------------------------------------------------------

class MwToolbar
{
	public function _CANCEL() 
	{
		JToolBarHelper::title( JText::_( 'Middleware' ).': <small><small>[ New ]</small></small>', 'user.png' );
		JToolBarHelper::cancel();
	}

	//-----------
	
	public function _EDIT_LICENSE_ASSOC($edit) 
	{
		$text = ( $edit ? JText::_( 'EDIT_LICENSE_ASSOC' ) : JText::_( 'NEW_LICENSE_ASSOC' ) );
		
		JToolBarHelper::title( JText::_( 'Middleware' ).': <small><small>[ '. $text.' ]</small></small>', 'user.png' );
		JToolBarHelper::save('savelicenseassoc');
		JToolBarHelper::cancel('cancellicense');
	}

	//-----------
	
	public function _LICENSE_ASSOC() 
	{
		JToolBarHelper::title( JText::_( 'Middleware' ).': <small><small>[ '. JText::_('LICENSE_ASSOC').' ]</small></small>', 'user.png' );
		JToolBarHelper::addNew('addlicenseassoc');
		//JToolBarHelper::editList('editlicenseassoc');
		JToolBarHelper::deleteList('Remove entry?', 'removelicenseassoc');
	}
	
	//-----------
	
	public function _EDIT_LICENSE($edit) 
	{
		$text = ( $edit ? JText::_( 'EDIT_LICENSE' ) : JText::_( 'NEW_LICENSE' ) );
		
		JToolBarHelper::title( JText::_( 'Middleware' ).': <small><small>[ '. $text.' ]</small></small>', 'user.png' );
		JToolBarHelper::save('savelicense');
		JToolBarHelper::cancel('cancellicense');
	}
	
	//-----------
	
	public function _LICENSES() 
	{
		JToolBarHelper::title( JText::_( 'Middleware' ).': <small><small>[ '. JText::_('LICENSES').' ]</small></small>', 'user.png' );
		JToolBarHelper::addNew('addlicense');
		JToolBarHelper::editList('editlicense');
		JToolBarHelper::deleteList('Remove license?','removelicense');
	}

	//-----------
	
	public function _DEFAULT() 
	{
		JToolBarHelper::title( JText::_( 'Middleware' ), 'user.png' );
		JToolBarHelper::preferences('com_mw', '550');
	}
}
?>