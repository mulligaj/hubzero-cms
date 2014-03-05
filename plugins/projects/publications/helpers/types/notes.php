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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 * NOTES master type helper class
 */
class typeNotes extends JObject
{	
	/**
	 * JDatabase
	 * 
	 * @var object
	 */
	var $_database       	= NULL;
	
	/**
	 * Project
	 * 
	 * @var object
	 */
	var $_project      	 	= NULL;
	
	/**
	 * Base alias
	 * 
	 * @var integer
	 */
	var $_base   		 	= 'notes';

	/**
	 * Attachment type
	 * 
	 * @var string
	 */
	var $_attachmentType 	= 'note';
	
	/**
	 * Selection type (single/multi)
	 * 
	 * @var boolean
	 */
	var $_multiSelect 	 	= false;
	
	/**
	 * Allow change to selection after draft is started?
	 * 
	 * @var boolean
	 */
	var $_changeAllowed  	= true;
	
	/**
	 * Allow to create a new publication with exact same content?
	 * 
	 * @var boolean
	 */
	var $_allowDuplicate  	= false;			
	
	/**
	 * Unique attachment properties
	 * 
	 * @var array
	 */
	var $_attProperties  	= array('object_id', 'object_revision');
	
	/**
	 * Data
	 * 
	 * @var array
	 */
	var $_data   		 = array();	
	
	/**
	 * Serve as (default value)
	 * 
	 * @var string
	 */
	var $_serveas   	= 'external';
	
	/**
	 * Serve as choices
	 * 
	 * @var string
	 */
	var $_serveChoices  = array('external');
		
	/**
	 * Constructor
	 * 
	 * @param      object  &$db      	 JDatabase
	 * @return     void
	 */	
	public function __construct( &$db, $project = NULL, $data = array() )
	{
		$this->_database = $db;
		$this->_project  = $project;
		$this->_data 	 = $data;
	}
	
	/**
	 * Set
	 * 
	 * @param      string 	$property
	 * @param      string 	$value
	 * @return     mixed	
	 */	
	public function __set($property, $value)
	{
		$this->_data[$property] = $value;
	}
	
	/**
	 * Get
	 * 
	 * @param      string 	$property
	 * @return     mixed	
	 */	
	public function __get($property)
	{
		if (isset($this->_data[$property])) 
		{
			return $this->_data[$property];
		}
	}
	
	/**
	 * Dispatch task
	 * 
	 * @param      string  $task 
	 * @return     void
	 */	
	public function dispatch( $task = NULL )
	{
		$output 		 = NULL;
		
		switch ( $task ) 
		{
			case 'getServeAs': 								
				$output = $this->_getServeAs(); 		
				break;
				
			case 'checkContent': 								
				$output = $this->_checkContent(); 		
				break;
				
			case 'checkMissing': 								
				$output = $this->_checkMissing(); 		
				break;
				
			case 'drawItem': 								
				$output = $this->_drawItem(); 		
				break;
				
			case 'saveAttachments': 								
				$output = $this->_saveAttachments(); 		
				break;
				
			case 'cleanupAttachments':
				$output = $this->_cleanupAttachments(); 		
				break;
				
			case 'getPubTitle':
				$output = $this->_getPubTitle();
	
			default:
				break;
		}
		
		return $output;
	}
	
	/**
	 * Get serveas options (_showOptions function in plg_projects_publications)
	 * 
	 * @return     void
	 */	
	protected function _getServeAs()
	{
		$result = array('serveas' => $this->_serveas, 'choices' => $this->_serveChoices);
		
		return $result;
	}
	
	/**
	 * Get publication title for newly created draft
	 * 
	 * @return     void
	 */	
	protected function _getPubTitle($title = '')
	{
		// Incoming data
		$item = $this->__get('item');
		
		// Get helper
		$projectsHelper = new ProjectsHelper( $this->_database );
		
		$masterscope = 'projects' . DS . $this->_project->alias . DS . 'notes';
		$group = $this->_config->get('group_prefix', 'pr-') . $this->_project->alias;

		$note = $projectsHelper->getSelectedNote($item, $group, $masterscope);
		$title = $note ? $note->title : '';
		
		return $title;
		
	}
	
	/**
	 * Check content
	 * 
	 * @return     void
	 */	
	protected function _checkContent()
	{
		// Incoming data
		$attachments = $this->__get('attachments');
		
		if ($attachments && count($attachments) > 0)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check missing content
	 * 
	 * @return     void
	 */	
	protected function _checkMissing()
	{
		// Incoming data
		$item  	 = $this->__get('item');
		$config  = $this->__get('config');
		
		if (!$item)
		{
			return false;
		}
		
		$pageid 		= $item->object_id;		
		$masterscope 	= 'projects' . DS . $this->_project->alias . DS . 'notes';
		$group_prefix 	= $config->get('group_prefix', 'pr-');
		$group 			= $group_prefix . $this->_project->alias;
		
		// Get projects helper
		$projectsHelper = new ProjectsHelper( $this->_database );
		
		if (!$projectsHelper->getSelectedNote($pageid, $group, $masterscope))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Draw selected item html
	 * 
	 * @return     void
	 */	
	protected function _drawItem()
	{
		// Incoming data
		$att   		= $this->__get('att');
		$item   	= $this->__get('item');
		
		// Load component configs
		$config = JComponentHelper::getParams( 'com_projects' );
		
		$pageid 		= $att->id ? $att->object_id : $item;		
		$masterscope 	= 'projects' . DS . $this->_project->alias . DS . 'notes';
		$group_prefix 	= $config->get('group_prefix', 'pr-');
		$group 			= $group_prefix . $this->_project->alias;
		
		// Get projects helper
		$projectsHelper = new ProjectsHelper( $this->_database );
		$note = $projectsHelper->getSelectedNote($pageid, $group, $masterscope);
		
		if (!$note)
		{
			return false;
		}
		
		$title = $att->title ? $att->title : $note->title;
		
		$html = '<span class="' . $this->_base . '">' . $title . '</span>';
		$html.= '<span class="c-iteminfo"></span>';

		return $html;
	}	
	
	/**
	 * Save picked items as publication attachments
	 * 
	 * @return     void
	 */	
	protected function _saveAttachments()
	{
		// Incoming data
		$selections 	= $this->__get('selections');
		$option  		= $this->__get('option');
		$vid  			= $this->__get('vid');
		$pid  			= $this->__get('pid');
		$uid  			= $this->__get('uid');
		$update_hash  	= $this->__get('update_hash');
		$primary  		= $this->__get('primary');
		$added  		= $this->__get('added');
		$serveas  		= $this->__get('serveas');
		$state  		= $this->__get('state');
		$secret  		= $this->__get('secret');
		$newpub  		= $this->__get('newpub');
		
		if (isset($selections['notes']) && count($selections['notes']) > 0) 
		{
			// Get helper
			$projectsHelper = new ProjectsHelper( $this->_database );
			
			// Load component configs
			$pubconfig = JComponentHelper::getParams( 'com_publications' );
			$config    = JComponentHelper::getParams( 'com_projects' );
					
			$masterscope = 'projects' . DS . $this->_project->alias . DS . 'notes';
			$group 		 = $config->get('group_prefix', 'pr-') . $this->_project->alias;
			
			$objPA = new PublicationAttachment( $this->_database );
			
			// Attach every selected file
			foreach ($selections['notes'] as $pageId) 
			{
				// get project note						
				$note = $projectsHelper->getSelectedNote($pageId, $group, $masterscope);
			
				if (!$note)
				{
					// Can't proceed
					continue;
				}
							
				if ($objPA->loadAttachment($vid, $pageId, 'note')) 
				{
					$objPA->modified_by 			= $uid;
					$objPA->modified 				= JFactory::getDate()->toSql();
				}
				else 
				{
					$objPA 							= new PublicationAttachment( $this->_database );
					$objPA->publication_id 			= $pid;
					$objPA->publication_version_id 	= $vid;
					$objPA->path 					= '';
					$objPA->type 					= $this->_attachmentType;
					$objPA->created_by 				= $uid;
					$objPA->created 				= JFactory::getDate()->toSql();
				}
				
				// Save object information
				$objPA->object_id   	= $pageId;
				$objPA->object_name 	= $note->pagename;
				$objPA->object_revision = $note->version;
				$objPA->object_instance = $note->instance;
			
				$objPA->ordering 		= $added;
				$objPA->role 			= $primary;
				$objPA->title 			= $note->title;
				$objPA->params 			= $primary  == 1 && $serveas ? 'serveas='.$serveas : $objPA->params;
			
				if ($objPA->store()) 
				{
					$added++;
				}				
			}
		}
		
		return $added;
	}
	
	/**
	 * Cleanup publication attachments when others are picked
	 * 
	 * @return     void
	 */	
	protected function _cleanupAttachments()
	{
		// Incoming data
		$selections 	= $this->__get('selections');
		$vid  			= $this->__get('vid');
		$pid  			= $this->__get('pid');
		$uid  			= $this->__get('uid');
		$old  			= $this->__get('old');
		$secret  		= $this->__get('secret');
		
		if (empty($selections) || !isset($selections['notes']))
		{
			return false;
		}
		
		if (!in_array(trim($old->object_id), $selections['notes']))
		{
			$objPA = new PublicationAttachment( $this->_database );
			$objPA->deleteAttachment($vid, $old->object_id, $old->type);	
		}
		
		return true;
	}
}
