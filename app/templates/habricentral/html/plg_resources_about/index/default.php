<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();

$this->css();

$sef = Route::url('index.php?option=' . $this->option . '&id=' . $this->model->resource->id);

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

$dateFormat = 'd M Y';
$yearFormat = 'Y';
$timeFormat = 'h:M a';
$tz = true;

$this->model->resource->introtext = stripslashes($this->model->resource->introtext);
$this->model->resource->fulltxt = stripslashes($this->model->resource->fulltxt);
$this->model->resource->fulltxt = ($this->model->resource->fulltxt) ? trim($this->model->resource->fulltxt) : trim($this->model->resource->introtext);

// Parse for <nb:field> tags
$type = new \Components\Resources\Tables\Type($this->database);
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

include_once(PATH_CORE . DS . 'components' . DS . 'com_resources' . DS . 'models' . DS . 'elements.php');
$elements = new \Components\Resources\Models\Elements($data, $this->model->type->customFields);
$schema = $elements->getSchema();

// Set the document description
if ($this->model->resource->introtext)
{
	$abstract = strip_tags($this->model->resource->introtext);
	Document::setDescription($this->escape($abstract));
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
$document = App::get('document');

// set title
$document->setMetaData('citation_title', trim($this->model->resource->title));

// set abstract
if ($this->model->resource->introtext)
{
	$document->setMetaData('citation_abstract', $this->escape($abstract));
}

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

	// $document->setMetaData('eprints:abstract', 'eprints:abstract test');
	// $document->setMetaData('prism:teaser', 'prism:teaser test');
	// $document->setMetaData('og:description', 'og:description test');
	// $document->setMetaData('bibo:abstract', 'bibo:abstract test');
	// $document->setMetaData('dcterms:abstract', 'dcterms:abstract test');
	// $document->setMetaData('dc:description.abstract', 'dc:description.abstract test');
	// $document->setMetaData('dcterms:description.abstract', 'dcterms:description.abstract test');
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
	if (isset($data['magazinetitle']) && $data['magazinetitle'] != '')
	{
		$document->setMetaData('citation_journal_title', trim($data['magazinetitle']));
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
	if (isset($data['newspapertitle']) && $data['newspapertitle'] != '')
	{
		$document->setMetaData('citation_journal_title', trim($data['newspapertitle']));
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
			<th><?php echo Lang::txt('Category'); ?></th>
			<td class="resource-content">
				<a href="<?php echo Route::url('index.php?option=' . $this->option . '&type=' . $this->model->type->alias); ?>">
					<?php echo $this->escape(stripslashes($this->model->type->type)); ?>
				</a>
			</td>
		</tr>
<?php if ($thedate) { ?>
		<tr>
			<th><?php echo Lang::txt('Published on'); ?></th>
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
		<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_ABSTRACT'); ?></th>
		<td class="resource-content">
			<?php echo $this->escape($this->model->resource->introtext); ?>
		</td>
		</tr>
<?php
} else {
	if (trim($maintext) && $maintext != '<p></p>') {
?>
<tr>
		<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_ABSTRACT'); ?></th>
		<td class="resource-content">
			<?php echo $maintext; ?>
		</td>
	</tr>
<?php
	}

	if ($this->model->contributors('submitter')) {
?>
<tr>
			<th><?php echo Lang::txt('Submitter'); ?></th>
			<td class="resource-content">
				<span id="submitterlist">
					<?php
					$view = new \Hubzero\Component\View(array(
						'base_path' => PATH_CORE . DS . 'components' . DS . 'com_resources' . DS . 'site',
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
	$cite = new stdClass();
	$cite->title = $this->model->resource->title;
	$cite->year = Date::of($thedate)->toLocal($yearFormat);
	$cite->location = Request::base() . ltrim($sef, DS);
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

		$citeinstruct  = \Components\Resources\Helpers\Html::citation($this->option, $cite, $this->model->resource->id, $citations, $this->model->resource->type, 0);
		$citeinstruct .= \Components\Resources\Helpers\Html::citationCOins($cite, $this->model); //->resource, $this->model->params, $this->helper);
?>
<tr>
			<th><a name="citethis"></a><?php echo Lang::txt('PLG_RESOURCES_ABOUT_CITE_THIS'); ?></th>
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
					  ? Date::of($this->model->attribs->get('timeof', ''))->toLocal($exp)
					  : '';
	} else {
		$seminarTime = $this->model->attribs->get('timeof', '');
	}
?>
<tr>
			<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_TIME'); ?></th>
			<td class="resource-content"><time><?php echo $this->escape($seminarTime); ?></time></td>
			</tr>
<?php
}
// If the resource had a specific location
if ($this->model->attribs->get('location', '')) {
?>
<tr>
			<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_LOCATION'); ?></th>
			<td class="resource-content"><?php echo $this->escape($this->model->attribs->get('location', '')); ?></td>
		</tr>
<?php
}
// Tags
if ($this->model->params->get('show_assocs')) {
	$tags = $this->model->tags();

	if ($tags) {
		$tagger = new \Components\Resources\Helpers\Tags($this->model->resource->id);
		$tagCloudtags = $tagger->render('cloud', ($this->model->access('edit') ? array() : array('admin' => 0, 'label' => '')));
?>
<?php if (count($tagCloudtags) > 0 && $tagCloudtags != "") : ?>
<tr>
			<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_TAGS'); ?></th>
			<td class="resource-content">
				<?php echo $tagger->render('cloud', ($this->model->access('edit') ? array() : array('admin' => 0, 'label' => ''))); ?>
			</td>
</tr>
<?php endif; ?>
<?php
	}
}
?>
			<?php
				$tagger = new \Components\Resources\Helpers\Tags($this->model->resource->id);
				$badges = $tagger->render('array', array('label' => 'badge'));
			?>
			<?php if (count($badges) > 0) : ?>
				<tr>
					<th>Badges</th>
					<td class="badges-list">
						<?php echo $tagger->render('cloud', (array('label' => 'badge'))); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table><!-- / .resource -->
</div><!-- / .subject -->
<?php if (!$this->model->params->get('show_citation')) {

	// Show coins
	include_once(PATH_CORE . DS . 'components' . DS . 'com_citations' . DS . 'helpers' . DS . 'format.php' );
	include_once(PATH_CORE . DS . 'components' . DS . 'com_citations' . DS . 'tables' . DS . 'type.php' );
	$cconfig  = Component::params( 'com_citations' );

	$formatter = new \Components\Citations\Helpers\Format();
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

	//echo $formatter->formatCitation($cite, false, true, $cconfig, true);

} ?>
<div class="clear"></div>