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
defined('_JEXEC') or die('Restricted access');

$this->css();

$sef = JRoute::_('index.php?option=' . $this->option . '&id=' . $this->model->resource->id);

// Collect data for coins
$coins = array(
	'doi' => NULL, 'pages' => NULL, 'journaltitle' => NULL, 
	'category' => NULL, 'issn' => NULL, 'isbn' => NULL,
	'volume' => NULL, 'number' => NULL, 'url' => NULL
);

// Set the display date
switch ($this->model->params->get('show_date'))
{
	case 0: $thedate = ''; break;
	case 1: $thedate = $this->model->resource->created;    break;
	case 2: $thedate = $this->model->resource->modified;   break;
	case 3: $thedate = $this->model->resource->publish_up; break;
}

$dateFormat = '%d %b %Y';
$yearFormat = '%Y';
$timeFormat = '%I:%M %p';
$tz = 0;
if (version_compare(JVERSION, '1.6', 'ge'))
{
	$dateFormat = 'd M Y';
	$yearFormat = 'Y';
	$timeFormat = 'h:M a';
	$tz = true;
}

$this->model->resource->introtext = stripslashes($this->model->resource->introtext);
$this->model->resource->fulltxt = stripslashes($this->model->resource->fulltxt);
$this->model->resource->fulltxt = ($this->model->resource->fulltxt) ? trim($this->model->resource->fulltxt) : trim($this->model->resource->introtext);

// Parse for <nb:field> tags
$type = new ResourcesType($this->database);
$type->load($this->model->resource->type);

$data = array();
preg_match_all("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", $this->model->resource->fulltxt, $matches, PREG_SET_ORDER);
if (count($matches) > 0) 
{
	foreach ($matches as $match)
	{
		$data[$match[1]] = $match[2];
	}
}
$this->model->resource->fulltxt = preg_replace("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", '', $this->model->resource->fulltxt);
$this->model->resource->fulltxt = trim($this->model->resource->fulltxt);

include_once(JPATH_ROOT . DS . 'components' . DS . 'com_resources' . DS . 'models' . DS . 'elements.php');
$elements = new ResourcesElements($data, $this->model->type->customFields);
$schema = $elements->getSchema();

// Set the document description
if ($this->model->resource->introtext) 
{
	$document = JFactory::getDocument();
	$document->setDescription($this->escape(strip_tags($this->model->resource->introtext)));
}

// Check if there's anything left in the fulltxt after removing custom fields
// If not, set it to the introtext
$maintext = $this->model->resource->fulltxt;
$maintext = preg_replace('/&(?!(?i:\#((x([\dA-F]){1,5})|(104857[0-5]|10485[0-6]\d|1048[0-4]\d\d|104[0-7]\d{3}|10[0-3]\d{4}|0?\d{1,6}))|([A-Za-z\d.]{2,31}));)/i',"&amp;",$maintext);
$maintext = str_replace('<blink>', '', $maintext);
$maintext = str_replace('</blink>', '', $maintext);

/*
|--------------------------------------------------------------------------
| Google Scholar
|--------------------------------------------------------------------------
|
| Here we add the google scholar meta tags.
|
*/
// get doc
$document = JFactory::getDocument();

// set title
$document->setMetaData('citation_title', trim($this->model->resource->title));

// set authors
foreach ($this->model->contributors('!submitter') as $contributor)
{
	$document->setMetaData('citation_author', trim($contributor->name));
}

// array to handle different date keys per resource type
$publicationDateMap = array(
	'audio'                   => 'datepublished',
	'booksection'             => 'publicationyear',
	'books'                   => 'publicationdate',
	'conferencepapers'        => 'yearpublished',
	'conferenceproceedings'   => 'publicationdate',
	'datasets'                => 'publicationdate',
	'governmentdocuments'     => 'dateofpublication',
	'journalarticles'         => 'yearofpublication',
	'magazinearticle'         => 'dateofpublication',
	'newspaperarticle'        => 'dateofpublication',
	'pamphlets'               => 'dateofpublication',
	'posters'                 => 'datepresented',
	'presentations'           => 'datepresented',
	'programs'                => 'date',
	'reports'                 => 'dateofpublication',
	'softliteraturenarrative' => 'dateofpublication',
	'theses'                  => 'dateawarded',
	'videos'                  => 'yearofpublication'
);

// add publication date
$typeAlias = $this->model->type->alias;
if (isset($publicationDateMap[$typeAlias]))
{
	$field = $publicationDateMap[$typeAlias];
	if (isset($data[$field]))
	{
		function _getValue($tag='lat', $text)
		{
			$pattern = "/<$tag>(.*?)<\/$tag>/i";
			preg_match($pattern, $text, $matches);
			return (isset($matches[1]) ? $matches[1] : '');
		}
		$year  = _getValue('year', $data[$field]);
		$month = _getValue('month', $data[$field]);
		$day   = _getValue('day', $data[$field]);

		$publicationDate  = $year;
		$publicationDate .= ($day) ? '/' . $day : '';
		$publicationDate .= ($month) ? '/' . $month : '';
		$document->setMetaData('citation_publication_date', trim($publicationDate));
	}
}

// handle individual types
if ($typeAlias == 'audio')
{
	// do nothing
}
else if ($typeAlias == 'booksection')
{
	if (isset($data['isbn']) && $data['isbn'] != '')
	{
		$document->setMetaData('citation_isbn', trim($data['isbn']));
	}
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
	if (isset($data['pagenumbers']) && $data['pagenumbers'] != '')
	{
		list($first, $last) = array_map('trim', explode('-', $data['pagenumbers']));
		$document->setMetaData('citation_firstpage', $first);
		$document->setMetaData('citation_lastpage', $last);
	}
}
else if ($typeAlias == 'books')
{
	if (isset($data['isbn']) && $data['isbn'] != '')
	{
		$document->setMetaData('citation_isbn', trim($data['isbn']));
	}
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
}
else if ($typeAlias == 'conferencepapers')
{
	if (isset($data['conferencename']) && $data['conferencename'] != '')
	{
		$document->setMetaData('citation_conference_title', trim($data['conferencename']));
	}
	if (isset($data['isbnissn']) && $data['isbnissn'] != '')
	{
		$document->setMetaData('citation_issn', trim($data['isbnissn']));
	}
}
else if ($typeAlias == 'conferenceproceedings')
{
	if (isset($data['conferencename']) && $data['conferencename'] != '')
	{
		$document->setMetaData('citation_conference_title', trim($data['conferencename']));
	}
	if (isset($data['isbnissn']) && $data['isbnissn'] != '')
	{
		$document->setMetaData('citation_issn', trim($data['isbnissn']));
	}
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
	if (isset($data['pages']) && $data['pages'] != '')
	{
		list($first, $last) = array_map('trim', explode('-', $data['pages']));
		$document->setMetaData('citation_firstpage', $first);
		$document->setMetaData('citation_lastpage', $last);
	}
}
else if ($typeAlias == 'datasets')
{
	// nothing
}
else if ($typeAlias == 'governmentdocuments')
{
	if (isset($data['issuenomonthandday']) && $data['issuenomonthandday'] != '')
	{
		$document->setMetaData('citation_issue', trim($data['issuenomonthandday']));
	}
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
}
else if ($typeAlias == 'journalarticles')
{
	if (isset($data['journaltitle']) && $data['journaltitle'] != '')
	{
		$document->setMetaData('citation_journal_title', trim($data['journaltitle']));
	}
	if (isset($data['issn']) && $data['issn'] != '')
	{
		$document->setMetaData('citation_issn', trim($data['issn']));
	}
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
	if (isset($data['issuenomonth']) && $data['issuenomonth'] != '')
	{
		$document->setMetaData('citation_issue', trim($data['issuenomonth']));
	}
	if (isset($data['pagenumbers']) && $data['pagenumbers'] != '')
	{
		list($first, $last) = array_map('trim', explode('-', $data['pagenumbers']));
		$document->setMetaData('citation_firstpage', $first);
		$document->setMetaData('citation_lastpage', $last);
	}
}
else if ($typeAlias == 'magazinearticle')
{
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
	if (isset($data['issuenomonthandday']) && $data['issuenomonthandday'] != '')
	{
		$document->setMetaData('citation_issue', trim($data['issuenomonthandday']));
	}
	if (isset($data['pagenumbers']) && $data['pagenumbers'] != '')
	{
		list($first, $last) = array_map('trim', explode('-', $data['pagenumbers']));
		$document->setMetaData('citation_firstpage', $first);
		$document->setMetaData('citation_lastpage', $last);
	}
}
else if ($typeAlias == 'newspaperarticle')
{
	if (isset($data['isbnissn']) && $data['isbnissn'] != '')
	{
		$document->setMetaData('citation_issn', trim($data['isbnissn']));
	}
	if (isset($data['volume']) && $data['volume'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volume']));
	}
	if (isset($data['issue']) && $data['issue'] != '')
	{
		$document->setMetaData('citation_issue', trim($data['issue']));
	}
	if (isset($data['pagenumbers']) && $data['pagenumbers'] != '')
	{
		list($first, $last) = array_map('trim', explode('-', $data['pagenumbers']));
		$document->setMetaData('citation_firstpage', $first);
		$document->setMetaData('citation_lastpage', $last);
	}
}
else if ($typeAlias == 'pamphlets')
{
	if (isset($data['isbnissn']) && $data['isbnissn'] != '')
	{
		$document->setMetaData('citation_issn', trim($data['isbnissn']));
	}
}
else if ($typeAlias == 'posters')
{
	if (isset($data['conferencename']) && $data['conferencename'] != '')
	{
		$document->setMetaData('citation_conference_title', trim($data['conferencename']));
	}
}
else if ($typeAlias == 'presentations')
{
	if (isset($data['conferencename']) && $data['conferencename'] != '')
	{
		$document->setMetaData('citation_conference_title', trim($data['conferencename']));
	}
	if (isset($data['presentationsponsorexpurdueuniversity']) && $data['presentationsponsorexpurdueuniversity'] != '')
	{
		$document->setMetaData('citation_dissertation_institution', trim($data['presentationsponsorexpurdueuniversity']));
	}
}
else if ($typeAlias == 'programs')
{
	// nothing
}
else if ($typeAlias == 'reports')
{
	if (isset($data['institution']) && $data['institution'] != '')
	{
		$document->setMetaData('citation_technical_report_institution', trim($data['institution']));
	}
	if (isset($data['reportno']) && $data['reportno'] != '')
	{
		$document->setMetaData('citation_technical_report_number', trim($data['reportno']));
	}
	if (isset($data['isbnissn']) && $data['isbnissn'] != '')
	{
		$document->setMetaData('citation_issn', trim($data['isbnissn']));
	}
	if (isset($data['volumeno']) && $data['volumeno'] != '')
	{
		$document->setMetaData('citation_volume', trim($data['volumeno']));
	}
	if (isset($data['issueno']) && $data['issueno'] != '')
	{
		$document->setMetaData('citation_issue', trim($data['issueno']));
	}
	if (isset($data['pagenumbers']) && $data['pagenumbers'] != '')
	{
		list($first, $last) = array_map('trim', explode('-', $data['pagenumbers']));
		$document->setMetaData('citation_firstpage', $first);
		$document->setMetaData('citation_lastpage', $last);
	}
}
else if ($typeAlias == 'softliteraturenarrative')
{
	// nothing	
}
else if ($typeAlias == 'theses')
{
	if (isset($data['university']) && $data['university'] != '')
	{
		$document->setMetaData('citation_dissertation_institution', trim($data['university']));
	}
}
else if ($typeAlias == 'video')
{
	if (isset($data['conferencename']) && $data['conferencename'] != '')
	{
		$document->setMetaData('citation_conference_title', trim($data['conferencename']));
	}
	if (isset($data['presentationsponsorexpurdueuniversity']) && $data['presentationsponsorexpurdueuniversity'] != '')
	{
		$document->setMetaData('citation_technical_report_institution', trim($data['presentationsponsorexpurdueuniversity']));
	}
}

?>
<div class="subject abouttab">
	<table class="resource">
		<tbody>
			<tr>
			<th><?php echo JText::_('Category'); ?></th>
			<td class="resource-content">
				<a href="<?php echo JRoute::_('index.php?option=' . $this->option . '&type=' . $this->model->type->alias); ?>">
					<?php echo $this->escape(stripslashes($this->model->type->type)); ?>
				</a>
			</td>
		</tr>
<?php if ($thedate) { ?>
		<tr>
			<th><?php echo JText::_('Published on'); ?></th>
			<td class="resource-content">
				<time datetime="<?php echo $thedate; ?>"><?php echo JHTML::_('date', $thedate, $dateFormat, $tz); ?></time>
			</td>
		</tr>
<?php } ?>
<?php
// Check how much we can display
if (!$this->model->access('view-all')) {
	// Protected - only show the introtext
?>
<tr>
		<th><?php echo JText::_('PLG_RESOURCES_ABOUT_ABSTRACT'); ?></th>
		<td class="resource-content">
			<?php echo $this->escape($this->model->resource->introtext); ?>
		</td>
		</tr>
<?php
} else {
	if (trim($maintext)) {
?>
<tr>
		<th><?php echo JText::_('PLG_RESOURCES_ABOUT_ABSTRACT'); ?></th>
		<td class="resource-content">
			<?php echo $maintext; ?>
		</td>
	</tr>
<?php
	}

	if ($this->model->contributors('submitter')) {
?>
<tr>
			<th><?php echo JText::_('Submitter'); ?></th>
			<td class="resource-content">
				<span id="submitterlist">
					<?php 
					$view = new JView(array(
						'base_path' => JPATH_ROOT . DS . 'components' . DS . 'com_resources',
						'name'   => 'view',
						'layout' => '_submitters',
					));
					$view->option = $this->option;
					$view->contributors = $this->model->contributors('submitter');
					$view->badges = $this->plugin->get('badges', 0);
					$view->showorgs = 1;
					$view->display();
					?>
				</span>
			</td>
		</tr>
<?php
	}
	$citations = '';
	foreach ($schema->fields as $field)
	{
		if (isset($data[$field->name])) {


			if ($field->name == 'citations') {
				$citations = $data[$field->name];
			} else if ($value = $elements->display($field->type, $data[$field->name])) {
				
				// Add to coins
				$fname = $field->name == 'pagenumbers' ? 'pages' : $field->name;
				$fname = $fname == 'volumeno' ? 'volume' : $fname;
				$fname = $fname == 'issuenomonth' ? 'number' : $fname;
				$fname = $fname == 'linktopublishedworkurl' ? 'url' : $fname;
				
				if (array_key_exists($fname, $coins))
				{
					$coins[$fname] = $value;
				}
?>
<tr>
			<th><?php echo $field->label; ?></th>
			<td class="resource-content">
				<?php echo $value; ?>
			</td>
		</tr>
<?php
			}
		}
	}
	
	// Build our citation object
	$juri = JURI::getInstance();
	
	$cite = new stdClass();
	$cite->title = $this->model->resource->title;
	$cite->year = JHTML::_('date', $thedate, $yearFormat, $tz);
	$cite->location = $juri->base() . ltrim($sef, DS);
	$cite->date = date("Y-m-d H:i:s");
	$cite->url = '';
	$cite->type = '';
	$cite->author = implode(';', $this->model->contributors('name')); //$this->helper->ul_contributors;

	if ($this->model->params->get('show_citation')) {
		if ($this->model->params->get('show_citation') == 1 || $this->model->params->get('show_citation') == 2) {
			// Citation instructions
			//$this->helper->getUnlinkedContributors();
			
			if ($this->model->params->get('show_citation') == 2) {
				$citations = '';
			}
		} else {
			$cite = null;
		}

		$citeinstruct  = ResourcesHtml::citation($this->option, $cite, $this->model->resource->id, $citations, $this->model->resource->type, 0);
		$citeinstruct .= ResourcesHtml::citationCOins($cite, $this->model); //->resource, $this->model->params, $this->helper);
?>
<tr>
			<th><a name="citethis"></a><?php echo JText::_('PLG_RESOURCES_ABOUT_CITE_THIS'); ?></th>
			<td class="resource-content">
				<?php echo $citeinstruct; ?>
			</td>
		</tr>
<?php
	}
}
// If the resource had a specific event date/time
if ($this->model->attribs->get('timeof', '')) {
	if (substr($this->model->attribs->get('timeof', ''), -8, 8) == '00:00:00') {
		$exp = $dateFormat; //'%B %d %Y';
	} else {
		$exp = $timeFormat . ', ' . $dateFormat; //'%I:%M %p, %B %d %Y';
	}
	if (substr($this->model->attribs->get('timeof', ''), 4, 1) == '-') {
		$seminarTime = ($this->model->attribs->get('timeof', '') != '0000-00-00 00:00:00' || $this->model->attribs->get('timeof', '') != '')
					  ? JHTML::_('date', $this->model->attribs->get('timeof', ''), $exp)
					  : '';
	} else {
		$seminarTime = $this->model->attribs->get('timeof', '');
	}
?>
<tr>
			<th><?php echo JText::_('PLG_RESOURCES_ABOUT_TIME'); ?></th>
			<td class="resource-content"><time><?php echo $this->escape($seminarTime); ?></time></td>
			</tr>
<?php
}
// If the resource had a specific location
if ($this->model->attribs->get('location', '')) {
?>
<tr>
			<th><?php echo JText::_('PLG_RESOURCES_ABOUT_LOCATION'); ?></th>
			<td class="resource-content"><?php echo $this->escape($this->model->attribs->get('location', '')); ?></td>
		</tr>
<?php
}
// Tags
if ($this->model->params->get('show_assocs')) {
	$tags = $this->model->tags();
	//$tagCloud = $this->helper->getTagCloud($this->authorized);
	if ($tags) {
		$tagger = new ResourcesTags($this->database);
?>
<tr>
			<th><?php echo JText::_('PLG_RESOURCES_ABOUT_TAGS'); ?></th>
			<td class="resource-content">
				<?php echo $tagger->buildCloud($tags); ?>
			</td>
			</tr>
<?php
	}
}
?>
			<?php
				$tagger = new ResourcesTags($this->database);
				$allTags = $tagger->getTags($this->model->resource->id, 0, 0, 1);
				$badges = array_filter($allTags, function($tag)
				{
					return ($tag->admin == 1 && $tag->label == 'badge');
				});
			?>
			<?php if (count($badges) > 0) : ?>
				<tr>
					<th>Badges</th>
					<td class="badges-list">
						<?php echo $tagger->buildCloud($badges); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table><!-- / .resource -->
</div><!-- / .subject -->
<?php if (!$this->model->params->get('show_citation')) {
	
	// Show coins
	include_once( JPATH_ROOT . DS . 'components' . DS . 'com_citations' . DS . 'helpers' . DS . 'format.php' );
	include_once( JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_citations' . DS . 'tables' . DS . 'type.php' );
	$cconfig  = JComponentHelper::getParams( 'com_citations' );
	
	$formatter = new CitationFormat();
	$formatter->setTemplate('ieee');
	
	$cite->date_publish = date("Y-m-d", strtotime($this->model->resource->publish_up));
	
	/*
	$cat = 'journal';	
	switch ($this->model->type->alias)
	{
		case 'books':
			$cat = "book";
			break;
			
		case 'booksection':
			$cat = "bookitem";
			break;
		
		case 'reports':
			$cat = "report";
			break;
			
		case 'pamphlets':
		case 'posters':
		case 'governmentdocuments':
			$cat = "document";
			break;
						
		case 'conferenceproceedings':
		case 'conferencepapers':
			$cat = "proceeding";
			break;
			
		case 'journalarticles':
		case 'magazinearticle':
		case 'newspaperarticle':
			$cat = "article";
			break;
			
		default:
			$cat = "unknown";
			break;	
	}
	*/
	$cite->type = $this->model->type->alias;
	
	foreach ($coins as $cname => $cvalue)
	{
		if ($cvalue)
		{
			$cite->$cname = $cvalue;
		}
	}
	
	echo $formatter->formatCitation($cite, false, true, $cconfig, true);
	
} ?>
<div class="clear"></div>