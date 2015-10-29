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

$sef = Route::url('index.php?option=' . $this->option . '&id=' . $this->model->resource->id);

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
$elements = new \Components\Resources\Models\Elements($data, $type->customFields);
$schema = $elements->getSchema();

// Set the document description
if ($this->model->resource->introtext) 
{
	Document::setDescription(\Components\Resources\Helpers\Html::encode_html(strip_tags($this->model->resource->introtext)));
}

// Check if there's anything left in the fulltxt after removing custom fields
// If not, set it to the introtext
$maintext = $this->model->resource->fulltxt;
$maintext = preg_replace('/&(?!(?i:\#((x([\dA-F]){1,5})|(104857[0-5]|10485[0-6]\d|1048[0-4]\d\d|104[0-7]\d{3}|10[0-3]\d{4}|0?\d{1,6}))|([A-Za-z\d.]{2,31}));)/i',"&amp;",$maintext);
$maintext = str_replace('<blink>', '', $maintext);
$maintext = str_replace('</blink>', '', $maintext);
?>
<div class="subject abouttab">
	<table class="resource" summary="<?php echo Lang::txt('RESOURCE_TBL_SUMMARY'); ?>">
		<tbody>
			<tr>
				<th><?php echo Lang::txt('Category'); ?></th>
				<td><a href="<?php echo Route::url('index.php?option=' . $this->option . '&type=' . $this->model->resource->_type->alias); ?>"><?php echo stripslashes($this->model->resource->_type->type); ?></a></td>
			</tr>
			<tr>
				<th><?php echo Lang::txt('Published'); ?></th>
				<td><?php echo Date::of($thedate)->toLocal($tz); ?></a></td>
			</tr>
<?php
// Check how much we can display
if ($this->model->resource->access == 3 && (!in_array($this->model->resource->group_owner, $this->usersgroups) || !$this->authorized)) {
	// Protected - only show the introtext
?>
			<tr>
				<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_ABSTRACT'); ?></th>
				<td><?php echo $this->escape($this->model->resource->introtext); ?></td>
			</tr>
<?php
} else {
	if (trim($maintext)) {
?>
			<tr>
				<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_ABSTRACT'); ?></th>
				<td><?php echo $maintext; ?></td>
			</tr>
<?php
	}
	$this->helper->getSubmitters(true, 1, $this->plugin->get('badges', 0));
	if ($this->helper->contributors && $this->helper->contributors != '<br />') {
?>
			<tr>
				<th><?php echo Lang::txt('Submitter'); ?></th>
				<td>
					<span id="authorslist">
						<?php echo $this->helper->contributors; ?>
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
?>
			<tr>
				<th><?php echo $field->label; ?></th>
				<td><?php echo $value; ?></td>
			</tr>
<?php
			}
		}
	}

	if ($this->model->params->get('show_citation')) {
		if ($this->model->params->get('show_citation') == 1 || $this->model->params->get('show_citation') == 2) {
			// Citation instructions
			$this->helper->getUnlinkedContributors();

			// Build our citation object
			$cite = new stdClass();
			$cite->title = $this->model->resource->title;
			$cite->year = Date::of($thedate)->toLocal($yearFormat);
			$cite->location = Request::base() . ltrim($sef, '/');
			$cite->date = gmdate("Y-m-d H:i:s");
			$cite->url = '';
			$cite->type = '';
			$cite->author = $this->helper->ul_contributors;
			
			if ($this->model->params->get('show_citation') == 2) {
				$citations = '';
			}
		} else {
			$cite = null;
		}

		$citeinstruct  = \Components\Resources\Helpers\Html::citation( $this->option, $cite, $this->model->resource->id, $citations, $this->model->resource->type, 0 );
		$citeinstruct .= \Components\Resources\Helpers\Html::citationCOins($cite, $this->model->resource, $this->config, $this->helper);
?>
			<tr>
				<th><a name="citethis"></a><?php echo Lang::txt('PLG_RESOURCES_ABOUT_CITE_THIS'); ?></th>
				<td><?php echo $citeinstruct; ?></td>
			</tr>
<?php
	}
	else
	{
		// COinS support
		// Citation instructions
		$this->helper->getUnlinkedContributors();

		$cite = new stdClass();
		$cite->title = $this->model->resource->title;
		$cite->year = Date::of($thedate)->format($yearFormat);
		$cite->location = Request::base() . ltrim($sef, '/');
		$cite->date = date( "Y-m-d H:i:s" );
		$cite->url = '';
		$cite->type = '';
		$cite->author = $this->helper->ul_contributors;
		$coins = \Components\Resources\Helpers\Html::citationCOins($cite, $this->model->resource, $this->config, $this->helper);
		?>
		<tr>
			<td colspan="2"><?php echo $coins; ?></td>
		</tr>
		<?php
	}
}
// If the resource had a specific event date/time
if ($this->attribs->get('timeof', '')) {
	if (substr($this->attribs->get('timeof', ''), -8, 8) == '00:00:00') {
		$exp = $dateFormat; //'%B %d %Y';
	} else {
		$exp = $timeFormat . ', ' . $dateFormat; //'%I:%M %p, %B %d %Y';
	}
	if (substr($this->attribs->get('timeof', ''), 4, 1) == '-') {
		$seminarTime = ($this->attribs->get('timeof', '') != '0000-00-00 00:00:00' || $this->attribs->get('timeof', '') != '')
					  ? Date::of($this->attribs->get('timeof', ''))->format($exp)
					  : '';
	} else {
		$seminarTime = $this->attribs->get('timeof', '');
	}
?>
			<tr>
				<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_TIME'); ?></th>
				<td><?php echo $this->escape($seminarTime); ?></td>
			</tr>
<?php
}
// If the resource had a specific location
if ($this->attribs->get('location', '')) {
?>
			<tr>
				<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_LOCATION'); ?></th>
				<td><?php echo $this->escape($this->attribs->get('location', '')); ?></td>
			</tr>
<?php
}
// Tags
if ($this->model->params->get('show_assocs')) {
	$tagCloud = $this->helper->getTagCloud($this->authorized);

	if ($tagCloud) {
?>
			<tr>
				<th><?php echo Lang::txt('PLG_RESOURCES_ABOUT_TAGS'); ?></th>
				<td><?php echo $tagCloud; ?></td>
			</tr>
<?php
	}
}
?>
		</tbody>
	</table>
</div><!-- / .subject -->
<div class="clear"></div>