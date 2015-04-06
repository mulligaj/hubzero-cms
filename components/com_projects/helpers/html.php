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

if (!defined('n')) {

/**
 * Description for ''n''
 */
	define('n',"\n");

/**
 * Description for ''t''
 */
	define('t',"\t");

/**
 * Description for ''r''
 */
	define('r',"\r");

/**
 * Description for ''a''
 */
	define('a','&amp;');
}

/**
 * Html helper class
 */
class ProjectsHtml
{
	//----------------------------------------------------------
	// Time format
	//----------------------------------------------------------

	/**
	 * Show time since present moment or an actual date
	 *
	 * @param      string 	$time
	 * @param      boolean 	$utc	UTC
	 * @return     string
	 */
	public static function showTime($time, $utc = false)
	{
		$parsed 		= date_parse($time);
		$timestamp		= strtotime($time);
		$current_time 	= $utc ? JFactory::getDate() : date('c');
		$current  		= date_parse($current_time);
		$lapsed 		= strtotime($current_time) - $timestamp;
		if ($lapsed < 30)
		{
			return JText::_('just now');
		}
		elseif ($lapsed > 86400 && $current['year'] != $parsed['year'])
		{
			return JHTML::_('date', $timestamp, 'M j, Y', false);
		}
		elseif ($lapsed > 86400)
		{
			return JHTML::_('date', $timestamp, 'M j', false) . ' at ' . JHTML::_('date', $timestamp, 'h:ia', false);
		}
		else
		{
			return ProjectsHtml::timeDifference($lapsed);
		}
	}

	/**
	 * Specially formatted time display
	 *
	 * @param      string 	$time
	 * @param      boolean 	$full	Return detailed date/time?
	 * @param      boolean 	$utc	UTC
	 * @return     string
	 */
	public static function formatTime($time, $full = false, $utc = false)
	{
		$parsed 	= date_parse($time);
		$timestamp	= strtotime($time);

		$now 		= $utc ? JFactory::getDate()->toSql() : date('c');
		$current  	= date_parse($now);

		if ($full)
		{
			return JHTML::_('date', $timestamp, 'M d, Y H:i:s', false);
		}

		if ($current['year'] == $parsed['year'])
		{
			if ($current['month'] == $parsed['month'] && $current['day'] == $parsed['day'])
			{
				return JHTML::_('date', $timestamp, 'g:i A', false);
			}
			else
			{
				return JHTML::_('date', $timestamp, 'M j', false);
			}
		}
		else
		{
			return JHTML::_('date', $timestamp, 'M j, Y', false);
		}
	}

	/**
	 * Time elapsed from moment
	 *
	 * @param      string 	$timestamp
	 * @param      boolean 	$utc	UTC
	 * @return     string
	 */
	public static function timeAgo($timestamp, $utc = true)
	{
		$timestamp = strtotime($timestamp);

		// Get current time
		$current_time = $utc ? strtotime(JFactory::getDate()) : strtotime(date('c'));

		$text = ProjectsHtml::timeDifference($current_time - $timestamp);

		return $text;
	}

	/**
	 * Get time difference
	 *
	 * @param      string $difference
	 * @return     string
	 */
	public static function timeDifference ($difference)
	{
		// Set the periods of time
		$periods = array('sec', 'min', 'hr', 'day', 'week', 'month', 'year', 'decade');

		// Set the number of seconds per period
		$lengths = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

		// Determine which period we should use, based on the number of seconds lapsed.
		// If the difference divided by the seconds is more than 1, we use that. Eg 1 year / 1 decade = 0.1, so we move on
		// Go from decades backwards to seconds
		for ($val = sizeof($lengths) - 1; ($val >= 0) && (($number = $difference / $lengths[$val]) <= 1); $val--);

		// Ensure the script has found a match
		if ($val < 0)
		{
			$val = 0;
		}

		// Set the current value to be floored
		$number = floor($number);

		// If required create a plural
		if ($number != 1)
		{
			$periods[$val] .= 's';
		}

		// Return text
		$text = sprintf("%d %s ", $number, $periods[$val]);

		$parts = explode(' ', $text);

		$text  = $parts[0] . ' ' . $parts[1];
		if ($text == '0 seconds')
		{
			$text = JText::_('COM_PROJECTS_JUST_A_MOMENT');
		}

		return $text;
	}

	/**
	 * Time current moment
	 *
	 * @param      string $timestamp
	 * @return     string
	 */
	public static function timeFromNow ($timestamp)
	{
		// Get current UTC time
		$current_time = strtotime(JFactory::getDate());

		// Determine the difference, between the time now and the timestamp
		$difference =  strtotime($timestamp) - $current_time;

		return ProjectsHtml::timeDifference($difference);
	}

	//----------------------------------------------------------
	// File management
	//----------------------------------------------------------

	/**
	 * Get file attributes
	 *
	 * @param      string $path
	 * @param      string $base_path
	 * @param      string $get
	 * @param      string $prefix
	 * @return     string
	 */
	public static function getFileAttribs( $path = '', $base_path = '', $get = '', $prefix = JPATH_ROOT )
	{
		if (!$path)
		{
			return '';
		}

		// Get extension
		if ($get == 'ext')
		{
			$ext = explode('.', basename($path));
			$ext = count($ext) > 1 ? end($ext) : '';
			return strtoupper($ext);
		}

		$path = DS . trim($path, DS);
		if ($base_path)
		{
			$base_path = DS . trim($base_path, DS);
		}

		if (substr($path, 0, strlen($base_path)) == $base_path)
		{
			// Do nothing
		}
		else
		{
			$path = $base_path . $path;
		}
		$path = $prefix . $path;

		$fs = '';

		// Get the file size if the file exist
		if (file_exists( $path ))
		{
			try
			{
				$fs = filesize( $path );
			}
			catch (Exception $e)
			{
				// could not get file size
			}
		}
		$fs = ProjectsHtml::formatSize($fs);
		return ($fs) ? $fs : '';

	}

	/**
	 * Get directory size
	 *
	 * @param      string $directory
	 * @return     string
	 */
	public static function getDirSize ($directory = '')
	{
		if (!$directory)
		{
			return 0;
		}
		$dirSize=0;

		if (!$dh=opendir($directory))
		{
			return false;
		}

		while ($file = readdir($dh))
		{
			if ($file == "." || $file == "..")
			{
				continue;
			}

			if (is_file($directory."/".$file))
			{
				$dirSize += filesize($directory."/".$file);
			}

			if (is_dir($directory."/".$file))
			{
				$dirSize += ProjectsHtml::getDirSize($directory."/".$file);
			}
		}

		closedir($dh);

		return $dirSize;
	}

	/**
	 * Format size
	 *
	 * @param      int $file_size
	 * @param      int $round
	 * @return     string
	 */
	public static function formatSize($file_size, $round = 0)
	{
		if ($file_size >= 1073741824)
		{
			$file_size = round(($file_size / 1073741824 * 100), $round) / 100 . 'GB';
		}
		elseif ($file_size >= 1048576)
		{
			$file_size = round(($file_size / 1048576 * 100), $round) / 100 . 'MB';
		}
		elseif ($file_size >= 1024)
		{
			$file_size = round(($file_size / 1024 * 100) / 100, $round) . 'KB';
		}
		elseif ($file_size < 1024)
		{
			$file_size = $file_size . 'b';
		}

		return $file_size;
	}

	/**
	 * Convert file size
	 *
	 * @param      int $file_size
	 * @param      string $from
	 * @param      string $to
	 * @param      string $round
	 * @return     string
	 */
	public static function convertSize($file_size, $from = 'b', $to = 'GB', $round = 0)
	{
		$file_size = str_replace(' ', '', $file_size);

		if ($from == 'b')
		{
			if ($to == 'GB')
			{
				$file_size = round(($file_size / 1073741824 * 100), $round) / 100;
			}
			elseif ($to == 'MB')
			{
				$file_size = round(($file_size / 1048576 * 100), $round) / 100 ;
			}
			elseif ($to == 'KB')
			{
				$file_size = round(($file_size / 1024 * 100) / 100, $round);
			}
		}
		elseif ($from == 'GB')
		{
			if ($to == 'b')
			{
				$file_size = $file_size * 1073741824;
			}
			if ($to == 'KB')
			{
				$file_size = $file_size * 1048576;
			}
			if ($to == 'MB')
			{
				$file_size = $file_size * 1024;
			}
		}

		return $file_size;
	}

	/**
	 * Get google icon image
	 *
	 * @param      string $mimeType
	 * @param      boolean $include_dir
	 * @param      string $icon
	 * @return     string
	 */
	public static function getGoogleIcon ($mimeType, $include_dir = 1, $icon = '')
	{
		switch (strtolower($mimeType))
		{
			case 'application/vnd.google-apps.presentation':
				$icon = 'presentation';
				break;

			case 'application/vnd.google-apps.spreadsheet':
				$icon = 'sheet';
				break;

			case 'application/vnd.google-apps.document':
				$icon = 'doc';
				break;

			case 'application/vnd.google-apps.drawing':
				$icon = 'drawing';
				break;

			case 'application/vnd.google-apps.form':
				$icon = 'form';
				break;

			case 'application/vnd.google-apps.folder':
				$icon = 'folder';
				break;

			default:
				$icon = 'gdrive';
				break;
		}

		if ($include_dir)
		{
			$icon = "/plugins/projects/files/images/google/" . $icon . '.gif';
		}
		return $icon;

	}

	/**
	 * Fix up some mimetypes
	 *
	 * @param      string $file
	 * @param      string $mimeType
	 * @return     string
	 */
	public static function fixUpMimeType ($file = NULL, $mimeType = NULL)
	{
		if ($file)
		{
			// Get file extention
			$parts = explode('.', $file);
			$ext   = count($parts) > 1 ? array_pop($parts) : '';
			$ext   = strtolower($ext);

			switch (strtolower($ext))
			{
				case 'key':
					$mimeType = 'application/x-iwork-keynote-sffkey';
					break;

				case 'ods':
					$mimeType = 'application/vnd.oasis.opendocument.spreadsheet';
					break;

				case 'wmf':
					$mimeType = 'application/x-msmetafile';
					break;

				case 'tex':
					$mimeType = 'application/x-tex';
					break;
			}
		}

		return $mimeType;
	}

	/**
	 * Get name for a number
	 *
	 * @param      integer $int
	 * @return     string
	 */
	public static function getNumberName($int = 0)
	{
		$name = '';

		switch ($int)
		{
			case 1:
				$name = 'one';
			break;

			case 2:
				$name = 'two';
			break;

			case 3:
				$name = 'three';
			break;

			case 4:
				$name = 'four';
			break;

			case 5:
				$name = 'five';
			break;

			case 6:
				$name = 'six';
			break;

			case 7:
				$name = 'seven';
			break;

			case 8:
				$name = 'eight';
			break;

			case 9:
				$name = 'nine';
			break;

			case 10:
				$name = 'ten';
			break;
		}

		return $name;
	}

	/**
	 * Get file icon image
	 *
	 * @param      string $ext
	 * @param      boolean $include_dir
	 * @param      string $icon
	 * @return     string
	 */
	public static function getFileIcon ($ext, $include_dir = 1, $icon = '')
	{
		switch (strtolower($ext))
		{
			case 'pdf':
				$icon = 'page_white_acrobat';
				break;
			case 'txt':
			case 'css':
			case 'rtf':
			case 'sty':
			case 'cls':
			case 'log':
				$icon = 'page_white_text';
				break;
			case 'sql':
				$icon = 'page_white_sql';
				break;
			case 'm':
				$icon = 'page_white_matlab';
				break;
			case 'dmg':
			case 'exe':
			case 'va':
			case 'ini':
				$icon = 'page_white_gear';
				break;
			case 'eps':
			case 'ai':
			case 'wmf':
				$icon = 'page_white_vector';
				break;
			case 'php':
				$icon = 'page_white_php';
				break;
			case 'tex':
			case 'ltx':
				$icon = 'page_white_tex';
				break;
			case 'swf':
				$icon = 'page_white_flash';
				break;
			case 'key':
				$icon = 'page_white_keynote';
				break;
			case 'numbers':
				$icon = 'page_white_numbers';
				break;
			case 'pages':
				$icon = 'page_white_pages';
				break;
			case 'html':
			case 'htm':
				$icon = 'page_white_code';
				break;
			case 'xls':
			case 'xlsx':
			case 'tsv':
			case 'csv':
			case 'ods':
				$icon = 'page_white_excel';
				break;
			case 'ppt':
			case 'pptx':
			case 'pps':
				$icon = 'page_white_powerpoint';
				break;
			case 'mov':
			case 'mp4':
			case 'm4v':
			case 'avi':
				$icon = 'page_white_film';
				break;
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'tiff':
			case 'bmp':
			case 'png':
				$icon = 'page_white_picture';
				break;
			case 'mp3':
			case 'aiff':
			case 'm4a':
			case 'wav':
				$icon = 'page_white_sound';
				break;
			case 'zip':
			case 'rar':
			case 'gz':
			case 'sit':
			case 'sitx':
			case 'zipx':
			case 'tar':
			case '7z':
				$icon = 'page_white_compressed';
				break;
			case 'doc':
			case 'docx':
				$icon = 'page_white_word';
				break;
			case 'folder':
				$icon = 'folder';
				break;
			default:
				$icon = 'page_white';
				break;
		}

		if ($include_dir)
		{
			$icon = "/plugins/projects/files/images/" . $icon . '.gif';
		}
		return $icon;
	}

	/**
	 * Get array of available emotion icons
	 *
	 * @return     array
	 */
	public static function getEmoIcons()
	{
		$icons = array(
				':)'    =>  'happy',
				':-)'   =>  'grin',
				':D'    =>  'laugh',
				':d'    =>  'laugh',
				';)'    =>  'wink',
				':P'    =>  'tongue',
				':-P'   =>  'tongue',
				':-p'   =>  'tongue',
				':p'    =>  'tongue',
				':('    =>  'unhappy',
				':\'('	=>	'cry',
				':o'    =>  'surprised',
				':O'    =>  'surprised',
				':0'    =>  'surprised',
				':|'    =>  'displeased',
				':-|'   =>  'displeased',
				':/'    =>  'displeased',
				'8|'    =>  'sunglasses',
				'O:)'   =>  'saint',
				'>:O'   =>  'angry',
				':-/'   =>  'surprised',
				'l-)'   =>  'sleep',
				'(y)'   =>  'thumbsup',
				'^_^'   =>  'squint',
				'-_-'   =>  'squint',
				'3:)'   =>  'devil'
		);

		return $icons;
	}

	/**
	 * Replace with emotion icons
	 *
	 * @param      string $text
	 * @return     string
	 */
	public static function replaceEmoIcons($text = NULL)
	{
		$icons = ProjectsHtml::getEmoIcons();

		foreach ($icons as $icon => $image)
		{
			$pat 	=  '#(?<=\s|^)(' . preg_quote($icon) .')(?=\s|$)#';
			$rep  	= '<span class="icon-emo-' . $image . '"></span>';
			$text 	= preg_replace($pat, $rep, $text);
		}

		return $text;
	}

	//----------------------------------------------------------
	// Project page elements
	//----------------------------------------------------------

	/**
	 * Get project image source
	 *
	 * @param      string $alias
	 * @param      string $picture
	 * @param      array $config
	 * @return     string HTML
	 */
	public static function getProjectImageSrc( $alias = '', $picture = '', $config = '' )
	{
		if ($alias === NULL || !$picture)
		{
			return false;
		}
		if (!$config)
		{
			$config = JComponentHelper::getParams('com_projects');
		}
		$path = trim($config->get('imagepath', '/site/projects'), DS)
				. DS . $alias . DS . 'images';

		$src  = file_exists( JPATH_ROOT . DS . $path . DS . $picture )
					? $path . DS . $picture
					: NULL;
		return $src;
	}

	/**
	 * Get project thumbnail source
	 *
	 * @param      string $alias
	 * @param      string $picname
	 * @param      array $config
	 * @return     string
	 */
	public static function getThumbSrc( $alias = '', $picture = '', $config = '' )
	{
		if ($alias === NULL)
		{
			return false;
		}
		if (!$config)
		{
			$config = JComponentHelper::getParams('com_projects');
		}

		$src  = '';
		$path = DS . trim($config->get('imagepath', '/site/projects'), DS) . DS . $alias . DS . 'images';

		if (file_exists( JPATH_ROOT . $path . DS . 'thumb.png' ))
		{
			return $path . DS . 'thumb.png';
		}

		if ($picture)
		{
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_projects' . DS
				. 'helpers' . DS . 'imghandler.php' );

			$ih = new ProjectsImgHandler();
			$thumb = $ih->createThumbName($picture);
			$src = $thumb && file_exists( JPATH_ROOT . $path . DS . $thumb ) ? $path . DS . $thumb :  NULL;
			// Rename to thumb.png
			if ($thumb && file_exists( JPATH_ROOT . $path . DS . $thumb ) && !file_exists( JPATH_ROOT . $path . DS . 'thumb.png' ))
			{
				jimport('joomla.filesystem.file');
				if (JFile::copy(JPATH_ROOT . $path . DS . $thumb, JPATH_ROOT . $path . DS . 'thumb.png'))
				{
					return $path . DS . 'thumb.png';
				}
			}
		}
		if (!$src)
		{
			$src = $config->get('defaultpic');
		}

		return $src;
	}

	/**
	 * Embed project image
	 *
	 * @param      object $view
	 * @return     string HTML
	 */
	public static function embedProjectImage( $view )
	{
		$source = ProjectsHtml::getProjectImageSrc($view->project->alias, $view->project->picture, $view->config); ?>
		<div id="pimage" class="pimage">
			<a href="<?php echo JRoute::_('index.php?option=' . $view->option . a . 'alias='
			.$view->project->alias); ?>" title="<?php echo $view->project->title . ' - '
			. JText::_('COM_PROJECTS_VIEW_UPDATES'); ?>">
	<?php
		if ($source) {
		?>
			<img src="<?php echo JRoute::_('index.php?option=' . $view->option . '&alias='
			. $view->project->alias . '&controller=media&media=master');  ?>" alt="<?php echo $view->project->title; ?>" />
	<?php
		}
		else
		{ ?>
			<span class="defaultimage">&nbsp;</span>
	<?php } ?>
		</a></div>
	<?php }

	/**
	 * Write member options
	 *
	 * @param      object $view
	 * @return     string HTML
	 */
	public static function writeMemberOptions ( $view )
	{
		$options = '';
		$role    = JText::_('COM_PROJECTS_PROJECT') . ' <span>';

		switch ($view->project->role)
		{
			case 1:
				$role .= JText::_('COM_PROJECTS_LABEL_OWNER');
				$options .= '<li><a href="' . JRoute::_('index.php?option='
						 . $view->option . a . 'task=edit' . a . 'alias='
						 . $view->project->alias) . '">' . JText::_('COM_PROJECTS_EDIT_PROJECT') . '</a></li>';
				$options .= '<li><a href="' . JRoute::_('index.php?option='
						 . $view->option . a . 'task=edit' . a . 'alias='
						 . $view->project->alias) . '?edit=team">' . JText::_('COM_PROJECTS_INVITE_PEOPLE') . '</a></li>';
				break;
			default:
				$role .= JText::_('COM_PROJECTS_LABEL_COLLABORATOR');
		}
		$role 	.= '</span>';

		if (!$view->project->private)
		{
			$options .= '<li><a href="' . JRoute::_('index.php?option='
					 . $view->option . a . 'alias='
					 . $view->project->alias) . '?preview=1">'
					 . JText::_('COM_PROJECTS_PREVIEW_PUBLIC_PROFILE') . '</a></li>';
		}
		if (isset($view->project->counts['team']) && $view->project->counts['team'] > 1)
		{
			$options .= '<li><a href="' . JRoute::_('index.php?option='
					 . $view->option . a . 'alias='
					 . $view->project->alias . a. 'active=team') . '?action=quit">'
					 . JText::_('COM_PROJECTS_LEAVE_PROJECT') . '</a></li>';
		}

		$html = "\n" . t.t. '<ul id="member_options">' . "\n";
		$html.= t.t.' <li>' . ucfirst($role) . "\n";
		$html.= t.t.' 	<div id="options-dock">' . "\n";
		$html.= t.t.' 		<div>' . "\n";
		$html.= t.t.' 			<p>' . JText::_('COM_PROJECTS_JOINED')
				. ' ' . JHTML::_('date', $view->project->since, 'M d, Y') . '</p>' . "\n";
		if ($options)
		{
			$html.= t.t.'			<ul>' . "\n";
			$html.= t.t.t.t.t.t. $options . "\n";
			$html.= t.t.'			</ul>' . "\n";
		}
		$html.= t.t.' 		</div>' . "\n";
		$html.= t.t.' 	</div>' . "\n";
		$html.= t.t.' </li>' . "\n";
		$html.= t.t.'</ul>' . "\n";
		echo $html;
	}

	/**
	 * Write project header
	 *
	 * @param      object $view
	 * @return     string HTML
	 */
	public static function drawProjectHeader ($view, $publicView = false)
	{
		if ($view->project->private)
		{
			$privacy = '<span class="private">' . ucfirst(JText::_('COM_PROJECTS_PRIVATE')) . '</span>';
		}
		else
		{
			$privacy = '<a href="' . JRoute::_('index.php?option=' . $view->option . a . 'alias=' . $view->project->alias) . '/?preview=1" title="' . JText::_('COM_PROJECTS_PREVIEW_PUBLIC_PROFILE') . '">' . ucfirst(JText::_('COM_PROJECTS_PUBLIC')) . '</a>';
		}

		$start = ($view->project->owner && $publicView == false)
				? '<span class="h-privacy">' .$privacy . '</span> ' . strtolower(JText::_('COM_PROJECTS_PROJECT'))
				: ucfirst(JText::_('COM_PROJECTS_PROJECT'));

		$assets = array('files', 'databases', 'tools');
		$assetTabs = array();
		if ($publicView || !isset($view->tabs))
		{
			$view->tabs = array();
		}
		if ($view->active == 'edit')
		{
			$view->tabs[] = array('name' => 'edit', 'title' => 'Edit');
		}

		// Sort tabs so that asset tabs are together
		foreach ($view->tabs as $tab)
		{
			if (!isset($tab['name']))
			{
				continue;
			}
			if (in_array($tab['name'], $assets))
			{
				$assetTabs[] = $tab;
			}
		}
		$a = 0;
		if (count($assetTabs) > 1)
		{
			array_splice( $view->tabs, 3, 0, array(0 => array('name' => 'assets', 'title' => 'Assets')) );
		}
?>
		<div id="project-header" class="project-header">
			<div class="grid">
				<div class="col span10">
					<div class="pimage-container">
					<?php echo ProjectsHtml::embedProjectImage($view); ?>
					</div>
					<div class="ptitle-container">
						<h2><a href="<?php echo JRoute::_('index.php?option=' . $view->option . a . 'alias=' . $view->project->alias); ?>"><?php echo \Hubzero\Utility\String::truncate($view->project->title, 50); ?> <span>(<?php echo $view->project->alias; ?>)</span></a></h2>
						<p>
						<?php echo $start .' '.JText::_('COM_PROJECTS_BY').' ';
						if ($view->project->owned_by_group)
						{
							$group = \Hubzero\User\Group::getInstance( $view->project->owned_by_group );
							if ($group)
							{
								echo ' '.JText::_('COM_PROJECTS_GROUP').' <a href="/groups/'.$group->get('cn').'">'.$group->get('cn').'</a>';
							}
							else
							{
								echo JText::_('COM_PROJECTS_UNKNOWN').' '.JText::_('COM_PROJECTS_GROUP');
							}
						}
						else
						{
							echo '<a href="/members/'.$view->project->owned_by_user.'">'.$view->project->fullname.'</a>';
						}
						?>
						</p>
					</div>
				</div>
				<div class="col span2 omega">
					<?php echo $publicView == false ? ProjectsHtml::writeMemberOptions($view) : ''; ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="menu-wrapper">
		<?php if ($publicView == false && isset($view->tabs) && $view->tabs) { ?>
			<ul>
			<?php foreach ($view->tabs as $tab)
			{
				if (!isset($tab['name']))
				{
					continue;
				}
				if (in_array($tab['name'], $assets) && count($assetTabs) > 1)
				{
					continue;
				}
				if ($tab['name'] == 'blog')
				{
					$tab['name'] = 'feed';
				}
				$gopanel = $tab['name'] == 'assets' ? 'files' : $tab['name'];
				$active = (($tab['name'] == $view->active) || ($tab['name'] == 'assets' && in_array($view->active, $assets)))
				?>
				<li<?php if ($active) { echo ' class="active"'; } ?> id="tab-<?php echo $tab['name']; ?>">
					<a class="<?php echo $tab['name']; ?>" href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . 'alias=' . $view->project->alias . '&active=' . $gopanel); ?>/" title="<?php echo ucfirst(JText::_('COM_PROJECTS_PROJECT')) . ' ' . ucfirst($tab['title']); ?>">
						<span class="label"><?php echo $tab['title']; ?></span>
					<?php if ($tab['name'] != 'feed' && isset($view->project->counts[$tab['name']]) && $view->project->counts[$tab['name']] != 0) { ?>
						<span class="mini" id="c-<?php echo $tab['name']; ?>"><span id="c-<?php echo $tab['name']; ?>-num"><?php echo $view->project->counts[$tab['name']]; ?></span></span>
					<?php } elseif ($tab['name'] == 'feed') { ?>
						<span id="c-new" class="mini highlight <?php if ($view->project->counts['newactivity'] == 0) { echo 'hidden'; } ?>"><span id="c-new-num"><?php echo $view->project->counts['newactivity'];?></span></span>
					<?php } ?>
					</a>
					<?php if ($tab['name'] == 'assets') { ?>
					<div id="asset-selection" class="submenu-wrap">
						<?php foreach ($assetTabs as $aTab) { ?>
							<p><a class="<?php echo $aTab['name']; ?>" href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . 'alias=' . $view->project->alias . '&active=' . $aTab['name']); ?>/" title="<?php echo ucfirst(JText::_('COM_PROJECTS_PROJECT')) . ' ' . ucfirst($aTab['title']); ?>" id="tab-<?php echo $aTab['name']; ?>"><span class="label"><?php echo $aTab['title']; ?></span><?php if (isset($view->project->counts[$aTab['name']]) && $view->project->counts[$aTab['name']] != 0) { ?>
								<span class="mini" id="c-<?php echo $aTab['name']; ?>"><span id="c-<?php echo $aTab['name']; ?>-num"><?php echo $view->project->counts[$aTab['name']]; ?></span></span>
							<?php } ?>
								</a>
							</p>
						<?php } ?>
					</div>
					<?php } ?>
				</li>
			<?php  } ?>
			<li class="sideli <?php if ($view->active == 'info') { echo ' active'; } ?>" id="tab-info"><a href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . 'alias=' . $view->project->alias . '&active=info'); ?>/" title="<?php echo ucfirst(JText::_('COM_PROJECTS_ABOUT')); ?>">
				<span class="label"><?php echo JText::_('COM_PROJECTS_ABOUT'); ?></span></a></li>
			</ul>
		<?php } else {  ?>
			<?php if (isset($view->guest) && $view->guest) { ?>
			<p><?php echo JText::_('COM_PROJECTS_ARE_YOU_MEMBER'); ?> <a href="<?php echo JRoute::_('index.php?option=' . $view->option . '&alias=' . $view->project->alias . '&task=view') . '?action=login'; ?>"><?php echo ucfirst(JText::_('COM_PROJECTS_LOGIN')).'</a> '.JText::_('COM_PROJECTS_LOGIN_TO_PRIVATE_AREA'); ?></p>
			<?php } ?>
		<?php } ?>
		</div>
	<?php }

	/**
	 * Write project left-hand side (traditional layout)
	 *
	 * @param      object $view
	 * @return     string HTML
	 */
	public static function drawLeftPanel ($view)
	{
		?>
		<div class="main-menu">
			<?php echo ProjectsHtml::embedProjectImage($view); ?>
			<?php echo ProjectsHtml::drawProjectMenu($view); ?>
		</div><!-- / .main-menu -->
<?php	}

	/**
	 * Write project menu
	 *
	 * @param      object $view
	 * @return     string HTML
	 */
	public static function drawProjectMenu ($view)
	{
		$goto  = 'alias=' . $view->project->alias;
		$assets = array('files', 'databases', 'tools');
		$assetTabs = array();

		// Sort tabs so that asset tabs are together
		foreach ($view->tabs as $tab)
		{
			if (in_array($tab['name'], $assets))
			{
				$assetTabs[] = $tab;
			}
		}
		$a = 0;

		?>
		<ul class="projecttools">
			<li<?php if ($view->active == 'feed') { echo ' class="active"'; }?>>
				<a class="newsupdate" href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . $goto . '&active=feed'); ?>" title="<?php echo JText::_('COM_PROJECTS_VIEW_UPDATES'); ?>"><span><?php echo JText::_('COM_PROJECTS_TAB_FEED'); ?></span>
				<span id="c-new" class="mini highlight <?php if ($view->project->counts['newactivity'] == 0) { echo 'hidden'; } ?>"><span id="c-new-num"><?php echo $view->project->counts['newactivity'];?></span></span></a>
			</li>
			<li<?php if ($view->active == 'info') { echo ' class="active"'; }?>><a href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . $goto . '&active=info'); ?>" class="inform" title="<?php echo JText::_('COM_PROJECTS_VIEW') . ' ' . strtolower(JText::_('COM_PROJECTS_PROJECT')) . ' ' . strtolower(JText::_('COM_PROJECTS_TAB_INFO')); ?>">
				<span><?php echo JText::_('COM_PROJECTS_TAB_INFO'); ?></span></a>
			</li>
<?php if ($view->tabs) {
foreach ($view->tabs as $tab)
{
	if ($tab['name'] == 'blog')
	{
		continue;
	}

	if (in_array($tab['name'], $assets) && count($assetTabs) > 1)
	{
		$a++; // counter for asset tabs

		// Header tab
		if ($a == 1)
		{
			?>
			<li class="assets">
				<span><?php echo JText::_('COM_PROJECTS_TAB_ASSETS'); ?></span>
			</li>
		</ul>
		<ul class="projecttools assetlist">
		<?php
		foreach ($assetTabs as $aTab)
		{
			?>
			<li<?php if ($aTab['name'] == $view->active) { echo ' class="active"'; } ?>>
				<a class="<?php echo $aTab['name']; ?>" href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . $goto . '&active=' . $aTab['name']); ?>/" title="<?php echo JText::_('COM_PROJECTS_VIEW') . ' ' . strtolower(JText::_('COM_PROJECTS_PROJECT')) . ' ' . strtolower($aTab['title']); ?>">
					<span><?php echo $aTab['title']; ?></span>
				<?php if (isset($view->project->counts[$aTab['name']]) && $view->project->counts[$aTab['name']] != 0) { ?>
					<span class="mini" id="c-<?php echo $aTab['name']; ?>"><span id="c-<?php echo $aTab['name']; ?>-num"><?php echo $view->project->counts[$aTab['name']]; ?></span></span>
				<?php } ?>
				</a>
			</li>
		<?php } ?>
		</ul>
		<ul class="projecttools">
	<?php
	}
	continue;
}
?>
			<li<?php if ($tab['name'] == $view->active) { echo ' class="active"'; } ?>>
				<a class="<?php echo $tab['name']; ?>" href="<?php echo JRoute::_('index.php?option=' . $view->option . '&' . $goto . '&active=' . $tab['name']); ?>/" title="<?php echo JText::_('COM_PROJECTS_VIEW') . ' ' . strtolower(JText::_('COM_PROJECTS_PROJECT')) . ' ' . strtolower($tab['title']); ?>">
					<span><?php echo $tab['title']; ?></span>
				<?php if (isset($view->project->counts[$tab['name']]) && $view->project->counts[$tab['name']] != 0) { ?>
					<span class="mini" id="c-<?php echo $tab['name']; ?>"><span id="c-<?php echo $tab['name']; ?>-num"><?php echo $view->project->counts[$tab['name']]; ?></span></span>
				<?php } ?>
				</a>
			</li>
<?php }
} ?>
		</ul>
	<?php }

	/**
	 * Write project header
	 *
	 * @param      object $view
	 * @param      boolean $back
	 * @param      boolean $underline
	 * @param      int $show_privacy
	 * @param      boolean $show_pic
	 * @return     string HTML
	 */
	public static function writeProjectHeader ($view, $back = 0, $underline = 0, $show_privacy = 0, $show_pic = 1)
	{
		// Use alias or id in urls?
		$goto  = 'alias=' . $view->project->alias;
		$privacy_txt = $view->project->private ? JText::_('COM_PROJECTS_PRIVATE') : JText::_('COM_PROJECTS_PUBLIC');

		if ($view->project->private)
		{
			$privacy = '<span class="private">' . ucfirst($privacy_txt) . '</span>';
		}
		else
		{
			$privacy = '<a href="' . JRoute::_('index.php?option=' . $view->option . a . $goto)
					. '/?preview=1" title="' . JText::_('COM_PROJECTS_PREVIEW_PUBLIC_PROFILE') . '">'
					. ucfirst($privacy_txt) . '</a>';
		}

		$start = ($show_privacy == 2 && $view->project->owner)
				? '<span class="h-privacy">' .$privacy . '</span> ' . strtolower(JText::_('COM_PROJECTS_PROJECT'))
				: ucfirst(JText::_('COM_PROJECTS_PROJECT'));
	?>
	<div id="content-header" <?php if (!$show_pic) { echo 'class="nopic"'; } ?>>
		<?php if ($show_pic) { ?>
		<div class="pthumb"><a href="<?php echo JRoute::_('index.php?option='.$view->option.a.$goto); ?>" title="<?php echo JText::_('COM_PROJECTS_VIEW_UPDATES'); ?>"><img src="<?php echo	JRoute::_('index.php?option=' . $view->option . '&alias='
			. $view->project->alias . '&controller=media&media=thumb'); ?>" alt="<?php echo $view->project->title; ?>" /></a></div>
		<?php } ?>
		<div class="ptitle">
			<h2><a href="<?php echo JRoute::_('index.php?option='.$view->option.a.$goto); ?>"><?php echo \Hubzero\Utility\String::truncate($view->project->title, 50); ?> <span>(<?php echo $view->project->alias; ?>)</span></a></h2>
			<?php if ($back)  { ?>
			<h3 class="returnln"><?php echo JText::_('COM_PROJECTS_RETURN_TO'); ?> <a href="<?php echo JRoute::_('index.php?option='.$view->option.a.$goto); ?>"><?php echo JText::_('COM_PROJECTS_PROJECT_PAGE'); ?></a></h3>
			<?php } else { ?>
			<h3 <?php if ($underline) { echo 'class="returnln"'; } ?>><?php echo $start .' '.JText::_('COM_PROJECTS_BY').' ';
			if ($view->project->owned_by_group)
			{
				$group = \Hubzero\User\Group::getInstance( $view->project->owned_by_group );
				if ($group)
				{
					echo ' '.JText::_('COM_PROJECTS_GROUP').' <a href="/groups/'.$group->get('cn').'">'.$group->get('cn').'</a>';
				}
				else
				{
					echo JText::_('COM_PROJECTS_UNKNOWN').' '.JText::_('COM_PROJECTS_GROUP');
				}
			}
			else
			{
				echo '<a href="/members/'.$view->project->owned_by_user.'">'.$view->project->fullname.'</a>';
			//	echo '<span class="prominent">'.$view->project->fullname.'</span>';
			}
			?>
			<?php if ($show_privacy == 1) { ?>
				<span class="privacy <?php if ($view->project->private) { echo 'private'; } ?>"><?php if (!$view->project->private) {  ?><a href="<?php echo JRoute::_('index.php?option='.$view->option.a.$goto).'/?preview=1'; ?>"><?php } ?><?php echo $privacy_txt; ?><?php if (!$view->project->private) {  ?></a><?php } ?> <?php echo strtolower(JText::_('COM_PROJECTS_PROJECT')); ?>
				</span>
			<?php } ?>
			</h3>
			<?php } ?>
		</div>
	</div><!-- / #content-header -->
	<?php
	}

	//----------------------------------------------------------
	// Misc
	//----------------------------------------------------------

	/**
	 * Generate random code
	 *
	 * @param      int $minlength
	 * @param      int $maxlength
	 * @param      boolean $usespecial
	 * @param      boolean $usenumbers
	 * @param      boolean $useletters
	 * @return     string HTML
	 */
	public static function generateCode( $minlength = 10, $maxlength = 10, $usespecial = 0, $usenumbers = 0, $useletters = 1, $mixedcaps = false )
	{
		$key = '';
		$charset = '';
		if ($useletters) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($useletters && $mixedcaps) $charset .= "abcdefghijklmnopqrstuvwxyz";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|][";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}

	/**
	 * Clean up text
	 *
	 * @param      string $in
	 * @return     string
	 */
	public static function cleanText ($in = '')
	{
		$in = stripslashes($in);
		$in = str_replace('&quote;','&quot;',$in);
		$in = htmlspecialchars($in);

		if (!strstr( $in, '</p>' ) && !strstr( $in, '<pre class="wiki">' ))
		{
			$in = str_replace("<br />","",$in);
		}
		return $in;
	}

	/**
	 * Replace urls in text
	 *
	 * @param      string $string
	 * @param      string $rel
	 * @return     string HTML
	 */
	public static function replaceUrls($string, $rel = 'nofollow')
	{
	    return preg_replace('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@', "<a href=\"$1\" rel=\"{$rel}\">$1</a>", $string);
	}

	/**
	 * Search for value in array
	 *
	 * @param      string $needle
	 * @param      string $haystack
	 * @return     boolean
	 */
	public static function myArraySearch( $needle, $haystack )
	{
	    if (empty($needle) || empty($haystack))
		{
			return false;
		}

		foreach ($haystack as $key => $value)
		{
			$exists = 0;
			foreach ($needle as $nkey => $nvalue)
			{
				if (!empty($value->$nkey) && $value->$nkey == $nvalue)
				{
					$exists = 1;
				}
				else
				{
					$exists = 0;
				}
			}
			if ($exists) return $key;
		}

		return false;
	}

	/**
	 * Get appended to file name random string
	 *
	 * @param      string $path
	 *
	 * @return     string
	 */
	public static function getAppendedNumber ( $path = null )
	{
		$append = '';

		$dirname 	= dirname($path);
		$filename 	= basename($path);
		$name 		= '';
		$file = explode('.', $filename);

		$n = count($file);
		if ($n > 1)
		{
			$name = $file[$n-2];
		}
		else
		{
			$name = $path;
		}

		$parts = explode('-', $name);
		if (count($parts) > 1)
		{
			$append = intval(end($parts));
		}

		return $append;
	}

	/**
	 * Replace file ending
	 *
	 * @param      string $path
	 * @param      string $end
	 * @param      string $delim
	 * @return     string
	 */
	public static function cleanFileNum ( $path = null, $end = '', $delim = '-' )
	{
		$newpath = $path;

		if ($end)
		{
			$file = explode('.', $path);
			$n = count($file);
			$ext = '';
			if ($n > 1)
			{
				$name = $file[$n-2];
				$ext  = array_pop($file);
			}
			else
			{
				$name = $path;
			}

			$parts = explode($delim, $name);
			if (count($parts) > 1)
			{
				$oldnum = intval(end($parts));
				if ($oldnum == $end)
				{
					$out = array_pop($parts);
					$name = implode('', $parts);
				}
			}

			$newpath = $ext ? $name . '.' . $ext : $name;
		}

		return $newpath;
	}

	/**
	 * Append string to file name
	 *
	 * @param      string $path
	 * @param      string $append
	 * @param      string $ext
	 * @return     string
	 */
	public static function fixFileName ( $path = null, $append = '', $ext = '' )
	{
		if (!$path)
		{
			return false;
		}

		if (!$append)
		{
			return $path;
		}

		$newname 	= '';
		$dirname 	= dirname($path);
		$filename 	= basename($path);

		$file = explode('.', $filename);
		$n = count($file);
		if ($n > 1)
		{
			$file[$n-2] .= $append;

			$end = array_pop($file);
			$file[] = $end;
			$filename = implode('.',$file);
		}
		else
		{
			$filename = $filename . $append;
		}

		if ($ext)
		{
			$filename = $filename . '.' . $ext;
		}

		$newname = $dirname && $dirname != '.' ? $dirname . DS . $filename : $filename;

		return $newname;
	}

	/**
	 * Return filename without extension
	 *
	 * @param      string  $file      String to shorten
	 * @return     string
	 */
	public static function takeOutExt($file = '')
	{
		// Take out extention
		if ($file)
		{
			$parts = explode('.', $file);

			if (count($parts) > 1)
			{
				$end = array_pop($parts);
			}

			if (count($parts) > 1)
			{
				$end = array_pop($parts);
			}

			$file = implode($parts);
		}

		return $file;
	}

	/**
	 * Shorten a string to a max length, preserving whole words
	 *
	 * @param      string  $text      String to shorten
	 * @param      integer $chars     Max length to allow
	 * @return     string
	 */
	public static function shortenText($text, $chars=300)
	{
		$text = trim($text);

		if (strlen($text) > $chars)
		{
			$text = $text . ' ';
			$text = substr($text, 0, $chars);
		}

		return $text;
	}

	/**
	 * Shorten user full name
	 *
	 * @param      string $name
	 * @param      int $chars
	 * @return     string
	 */
	public static function shortenName( $name, $chars = 12 )
	{
		$name = trim($name);

		if (strlen($name) > $chars)
		{
			$names = explode(' ',$name);
			$name = $names[0];
			if (count($names) > 0 && $names[1] != '')
			{
				$name  = $name.' ';
				$name .= substr($names[1], 0, 1);
				$name .= '.';
			}
		}
		if ($name == '')
		{
			$name = JText::_('COM_PROJECTS_UNKNOWN');
		}

		return $name;
	}

	/**
	 * Shorten user full name
	 *
	 * @param      string $name
	 * @param      int $chars
	 * @return     string
	 */
	public static function shortenUrl( $name, $chars = 40 )
	{
		$name = trim($name);

		if (strlen($name) > $chars)
		{
			$name = substr($name, 0, $chars);
			$name = $name . '...';
		}

		return $name;
	}

	/**
	 * Shorten file name
	 *
	 * @param      string $name
	 * @param      int $chars
	 * @return     string
	 */
	public static function shortenFileName( $name, $chars = 30 )
	{
		$name = trim($name);
		$original = $name;

		$chars = $chars < 10 ? 10 : $chars;
		$cutter = $chars > 40 ? 25 : 10;

		if (strlen($name) > $chars)
		{
			$cutFront = $chars - $cutter;
			$name = substr($name, 0, $cutFront);
			$name = $name . '&#8230;';
			$name = $name . substr($original, -$cutter, $cutter);
		}
		if ($name == '')
		{
			$name = '&#8230;';
		}

		return $name;
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param string $file The name of the file [not full path]
	 * @return string The sanitized string
	 */
	public static function makeSafeFile($file)
	{
	//	$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#');
		return preg_replace($regex, '', $file);
	}

	/**
	 * Makes path name safe to use.
	 *
	 * @access	public
	 * @param	string The full path to sanitise.
	 * @return	string The sanitised string.
	 */
	public static function makeSafeDir($path)
	{
		$ds = (DS == '\\') ? '\\' . DS : DS;
		$regex = array('#[^A-Za-z0-9:\_\-' . $ds . ' ]#');
		return preg_replace($regex, '', $path);
	}

	//----------------------------------------------------------
	// Reviewers
	//----------------------------------------------------------

	/**
	 * Get admin notes
	 *
	 * @param      string $notes
	 * @param      string $reviewer
	 * @return     string
	 */
	public static function getAdminNotes($notes = '', $reviewer = '')
	{
		preg_match_all("#<nb:".$reviewer.">(.*?)</nb:".$reviewer.">#s", $notes, $matches);
		$ntext = '';
		if (count($matches) > 0)
		{
			$notes = $matches[0];
			if (count($notes) > 0)
			{
				krsort($notes);
				foreach ($notes as $match)
				{
					$ntext .= ProjectsHtml::parseAdminNote($match, $reviewer);
				}
			}
		}

		return $ntext;
	}

	/**
	 * Get admin notes count
	 *
	 * @param      string $notes
	 * @param      string $reviewer
	 * @return     string
	 */
	public static function getAdminNoteCount($notes = '', $reviewer = '')
	{
		preg_match_all("#<nb:".$reviewer.">(.*?)</nb:".$reviewer.">#s", $notes, $matches);

		if (count($matches) > 0)
		{
			$notes = $matches[0];
			return count($notes);
		}

		return 0;
	}

	/**
	 * Parse admin notes
	 *
	 * @param      string $note
	 * @param      string $reviewer
	 * @param      boolean $showmeta
	 * @param      int $shorten
	 * @return     string
	 */
	public static function parseAdminNote($note = '', $reviewer = '', $showmeta = 1, $shorten = 0)
	{
		$note = str_replace('<nb:'.$reviewer.'>','', $note);
		$note = str_replace('</nb:'.$reviewer.'>','', $note);

		preg_match("#<meta>(.*?)</meta>#s", $note, $matches);
		if (count($matches) > 0)
		{
			$meta = $matches[0];
			$note   = preg_replace( '#<meta>(.*?)</meta>#s', '', $note );

			if ($shorten)
			{
				$note   = \Hubzero\Utility\String::truncate($note, $shorten);
			}
			if ($showmeta)
			{
				$meta = str_replace('<meta>','' , $meta);
				$meta = str_replace('</meta>','', $meta);

				$note  .= '<span class="block mini faded">' . $meta . '</span>';
			}
		}
		$note = $note ? '<p class="admin-note">' . $note . '</p>' : '';

		return $note;
	}

	/**
	 * Get last admin note
	 *
	 * @param      string $notes
	 * @param      string $reviewer
	 * @return     string
	 */
	public static function getLastAdminNote($notes = '', $reviewer = '')
	{
		$match = '';
		preg_match_all("#<nb:".$reviewer.">(.*?)</nb:".$reviewer.">#s", $notes, $matches);

		if (count($matches) > 0)
		{
			$notes = $matches[0];
			if (count($notes) > 0)
			{
				$match = ProjectsHtml::parseAdminNote(end($notes), $reviewer, 1, 100);
			}
		}
		else
		{
			$match = '';
		}

		return $match;
	}

	/**
	 * Email
	 *
	 * @param      string $email
	 * @param      string $subject
	 * @param      string $body
	 * @param      array $from
	 * @return     void
	 */
	public static function email($email, $subject, $body, $from)
	{
		if ($from)
		{
			$body_plain = is_array($body) && isset($body['plaintext']) ? $body['plaintext'] : $body;
			$body_html  = is_array($body) && isset($body['multipart']) ? $body['multipart'] : NULL;

			$message = new \Hubzero\Mail\Message();
			$message->setSubject($subject)
				->addTo($email, $email)
				->addFrom($from['email'], $from['name'])
				->setPriority('normal');

			$message->addPart($body_plain, 'text/plain');

			if ($body_html)
			{
				$message->addPart($body_html, 'text/html');
			}

			$message->send();
			return true;
		}
		return false;
	}
}
