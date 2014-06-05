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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$canDo = CitationsHelper::getActions('citation');

$text = ($this->task == 'edit' ? JText::_('EDIT') : JText::_('NEW'));

JToolBarHelper::title(JText::_('CITATION') . ': ' . $text, 'citation.png');
if ($canDo->get('core.edit')) 
{
	JToolBarHelper::save();
}
JToolBarHelper::cancel();
JToolBarHelper::spacer();
JToolBarHelper::help('citation');

jimport('joomla.html.editor');
$editor = JEditor::getInstance();

//set the escape callback
$this->setEscape("htmlentities");

//need to fix these fields
$author = html_entity_decode($this->row->author);
$author = (!preg_match('!\S!u', $author)) ? utf8_encode($author) : $author;

$ceditor = html_entity_decode($this->row->editor);
$ceditor = (!preg_match('!\S!u', $ceditor)) ? utf8_encode($ceditor) : $ceditor;

$title = html_entity_decode($this->row->title);
$title = (!preg_match('!\S!u', $title)) ? utf8_encode($title) : $title;

$booktitle = html_entity_decode($this->row->booktitle);
$booktitle = (!preg_match('!\S!u', $booktitle)) ? utf8_encode($booktitle) : $booktitle;

$short_title = html_entity_decode($this->row->short_title);
$short_title = (!preg_match('!\S!u', $short_title)) ? utf8_encode($short_title) : $short_title;

$journal = html_entity_decode($this->row->journal);
$journal = (!preg_match('!\S!u', $journal)) ? utf8_encode($journal) : $journal;
?>

<script type="text/javascript" src="../components/com_citations/assets/js/citations.jquery.js"></script>

<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.getElementById('adminForm');
	
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}
	
	// form field validation
	//if (form.title.value == '') {
	//	alert('<?php echo JText::_('CITATION_MUST_HAVE_TITLE'); ?>');
	//} else {
		submitform(pressbutton);
	//}
}
</script>
<?php
	if ($this->getError())
	{
		echo '<p class="error">' . $this->getError() . '</p>';
	}
?>
<form action="index.php" method="post" name="adminForm" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('DETAILS'); ?></span></legend>

			<div class="input-wrap">
				<label for="type"><?php echo JText::_('TYPE'); ?>:</label><br />
				<select name="citation[type]" id="type">
					<?php foreach ($this->types as $t) : ?>
						<?php $sel = ($t['id'] == $this->row->type) ? 'selected="selected"' : ''; ?>
						<option <?php echo $sel; ?> value="<?php echo $t['id']; ?>"><?php echo $this->escape(stripslashes($t['type_title'])); ?> (<?php echo $this->escape($t['type']); ?>)</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="col width-50 fltlft">
				<div class="input-wrap">
					<label for="cite"><?php echo JText::_('CITE_KEY'); ?>:</label><br />
					<input type="text" name="citation[cite]" id="cite" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->cite)); ?>" />
					<span class="hint"><?php echo JText::_('CITE_KEY_EXPLANATION'); ?></span>
				</div>
			</div>
			<div class="col width-50 fltrt">
				<div class="input-wrap">
					<label for="ref_type"><?php echo JText::_('REF_TYPE'); ?>:</label><br />
					<input type="text" name="citation[ref_type]" id="ref_type" size="11" maxlength="50" value="<?php echo $this->escape(stripslashes($this->row->ref_type)); ?>" />
				</div>
			</div>
			<div class="clr"></div>

			<div class="col width-50 fltlft">
				<div class="input-wrap">
					<label for="date_submit"><?php echo JText::_('DATE_SUBMITTED'); ?>:</label><br />
					<input type="text" name="citation[date_submit]" id="date_submit" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->date_submit)); ?>" />
				</div>
			</div>
			<div class="col width-50 fltrt">
				<div class="input-wrap">
					<label for="date_accept"><?php echo JText::_('DATE_ACCEPTED'); ?>:</label><br />
					<input type="text" name="citation[date_accept]" id="date_accept" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->date_accept)); ?>" />
				</div>
			</div>
			<div class="clr"></div>

			<div class="col width-50 fltlft">
				<div class="input-wrap">
					<label for="date_publish"><?php echo JText::_('DATE_PUBLISHED'); ?>:</label><br />
					<input type="text" name="citation[date_publish]" id="date_publish" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->date_publish)); ?>" />
				</div>
			</div>
			<div class="col width-50 fltlft">
				<div class="col width-50 fltlft">
					<div class="input-wrap">
						<label for="year"><?php echo JText::_('YEAR'); ?>:</label><br />
						<input type="text" name="citation[year]" id="year" size="4" maxlength="4" value="<?php echo $this->escape(stripslashes($this->row->year)); ?>" />
					</div>
				</div>
				<div class="col width-50 fltrt">
					<div class="input-wrap">
						<label for="month"><?php echo JText::_('MONTH'); ?>:</label><br />
						<input type="text" name="citation[month]" id="month" size="11" maxlength="50" value="<?php echo $this->escape(stripslashes($this->row->month)); ?>" />
					</div>
				</div>
				<div class="clr"></div>
			</div>
			<div class="clr"></div>

			<div class="input-wrap">
				<label for="author"><?php echo JText::_('AUTHORS'); ?>:</label><br />
				<input type="text" name="citation[author]" id="author" value="<?php echo $this->escape($author); ?>" />
			</div>
			<div class="input-wrap">
				<label for="author_address"><?php echo JText::_('Author Address'); ?>:</label><br />
				<input type="text" name="citation[author_address]" id="author_address" value="<?php echo $this->escape(stripslashes($this->row->author_address)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="editor"><?php echo JText::_('EDITORS'); ?>:</label><br />
				<input type="text" name="citation[editor]" id="editor" maxlength="250" value="<?php echo $this->escape($ceditor); ?>" />
			</div>
			<div class="input-wrap">
				<label for="title"><?php echo JText::_('TITLE_CHAPTER'); ?>:</label><br />
				<input type="text" name="citation[title]" id="title" maxlength="250" value="<?php echo $this->escape($title); ?>" />
			</div>
			<div class="input-wrap">
				<label for="booktitle"><?php echo JText::_('BOOK_TITLE'); ?>:</label><br />
				<input type="text" name="citation[booktitle]" id="booktitle" maxlength="250" value="<?php echo $this->escape($booktitle); ?>" />
			</div>
			<div class="input-wrap">
				<label for="shorttitle"><?php echo JText::_('Short Title'); ?>:</label><br />
				<input type="text" name="citation[short_title]" id="shorttitle" maxlength="250" value="<?php echo $this->escape($short_title); ?>" />
			</div>
			<div class="input-wrap">
				<label for="journal"><?php echo JText::_('JOURNAL'); ?>:</label><br />
				<input type="text" name="citation[journal]" id="journal" maxlength="250" value="<?php echo $this->escape($journal); ?>" />
			</div>

			<div class="col width-50 fltlft">
				<div class="input-wrap">
					<label for="volume"><?php echo JText::_('VOLUME'); ?>:</label><br />
					<input type="text" name="citation[volume]" id="volume" maxlength="11" value="<?php echo $this->escape(stripslashes($this->row->volume)); ?>" />
				</div>
			</div>
			<div class="col width-50 fltrt">
				<div class="input-wrap">
					<label for="number"><?php echo JText::_('ISSUE'); ?>:</label><br />
					<input type="text" name="citation[number]" id="number" maxlength="50" value="<?php echo $this->escape(stripslashes($this->row->number)); ?>" />
				</div>
			</div>
			<div class="clr"></div>

			<div class="col width-50 fltlft">
				<div class="input-wrap">
					<label for="pages"><?php echo JText::_('PAGES'); ?>:</label><br />
					<input type="text" name="citation[pages]" id="pages" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->pages)); ?>" />
				</div>
			</div>
			<div class="col width-50 fltrt">
				<div class="input-wrap">
					<label for="isbn"><?php echo JText::_('ISBN'); ?>:</label><br />
					<input type="text" name="citation[isbn]" id="isbn" maxlength="50" value="<?php echo $this->escape(stripslashes($this->row->isbn)); ?>" />
				</div>
			</div>
			<div class="clr"></div>

			<div class="input-wrap">
				<label for="doi"><?php echo JText::_('DOI'); ?>:</label><br />
				<input type="text" name="citation[doi]" id="doi" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->doi)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="callnumber"><?php echo JText::_('Call Number'); ?>:</label><br />
				<input type="text" name="citation[call_number]" id="callnumber" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->call_number)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="accessionnumber"><?php echo JText::_('Accession Number'); ?>:</label><br />
				<input type="text" name="citation[accession_number]" id="accessionnumber" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->accession_number)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="series"><?php echo JText::_('SERIES'); ?>:</label><br />
				<input type="text" name="citation[series]" id="series" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->series)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="edition"><?php echo JText::_('EDITION'); ?>:</label><br />
				<input type="text" name="citation[edition]" id="edition" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->edition)); ?>" /> 
				<br /><span style="font-size: 90%;color:#aaa;"><?php echo JText::_('EDITION_EXPLANATION'); ?></span>
			</div>
			<div class="input-wrap">
				<label for="school"><?php echo JText::_('SCHOOL'); ?>:</label><br />
				<input type="text" name="citation[school]" id="school" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->school)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="publisher"><?php echo JText::_('PUBLISHER'); ?>:</label><br />
				<input type="text" name="citation[publisher]" id="publisher" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->publisher)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="institution"><?php echo JText::_('INSTITUTION'); ?>:</label><br />
				<input type="text" name="citation[institution]" id="institution" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->institution)); ?>" /> 
				<br /><span style="font-size: 90%;color:#aaa;"><?php echo JText::_('INSTITUTION_EXPLANATION'); ?></span>
			</div>
			<div class="input-wrap">
				<label for="address"><?php echo JText::_('ADDRESS'); ?>:</label><br />
				<input type="text" name="citation[address]" id="address" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->address)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="location"><?php echo JText::_('LOCATION'); ?>:</label><br />
				<input type="text" name="citation[location]" id="location" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->location)); ?>" /> 
				<span style="font-size: 90%;color:#aaa;"><?php echo JText::_('LOCATION_EXPLANATION'); ?></span>
			</div>
			<div class="input-wrap">
				<label for="howpublished"><?php echo JText::_('PUBLISH_METHOD'); ?>:</label><br />
				<input type="text" name="citation[howpublished]" id="howpublished" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->howpublished)); ?>" /> 
				<br /><span style="font-size: 90%;color:#aaa;"><?php echo JText::_('PUBLISH_METHOD_EXPLANATION'); ?></span>
			</div>
			<div class="input-wrap">
				<label for="url"><?php echo JText::_('URL'); ?>:</label><br />
				<input type="text" name="citation[url]" id="url" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->url)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="eprint"><?php echo JText::_('EPRINT'); ?>:</label><br />
				<input type="text" name="citation[eprint]" id="eprint" maxlength="250" value="<?php echo $this->escape(stripslashes($this->row->eprint)); ?>" />
				<br /><span class="hint"><?php echo JText::_('EPRINT_EXPLANATION'); ?></span>
			</div>
			<div class="input-wrap">
				<label for="abstract"><?php echo JText::_('Abstract'); ?>:</label><br />
				<?php echo $editor->display('citation[abstract]', stripslashes($this->row->abstract), '', '', '50', '10'); ?>
			</div>
			<div class="input-wrap">
				<label for="note"><?php echo JText::_('NOTES'); ?>:</label><br />
				<?php echo $editor->display('citation[note]', stripslashes($this->row->note), '', '', '50', '10'); ?>
			</div>
			<div class="input-wrap">
				<label for="keywords"><?php echo JText::_('Keywords'); ?>:</label><br />
				<?php echo $editor->display('citation[keywords]', stripslashes($this->row->keywords), '', '', '50', '10'); ?>
			</div>
			<div class="input-wrap">
				<label for="research_notes"><?php echo JText::_('Research Notes'); ?>:</label><br />
				<?php echo $editor->display('citation[research_notes]', stripslashes($this->row->research_notes), '', '', '50', '10'); ?>
			</div>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('CITATION_FOR'); ?></span></legend>

			<table class="admintable" id="assocs">
				<thead>
					<tr>
						<th><?php echo JText::_('TYPE'); ?></th>
						<th><?php echo JText::_('ID'); ?></th>
						<!--<th><?php //echo JText::_('TABLE'); ?></th>-->
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="3"><a href="#" onclick="HUB.Citations.addRow('assocs');return false;"><?php echo JText::_('ADD_A_ROW'); ?></a></td>
					</tr>
				</tfoot>
				<tbody>
				<?php
						$assocs = $this->assocs;
						$r = count($assocs);
						if ($r > 5) {
							$n = $r;
						} else {
							$n = 5;
						}
						for ($i=0; $i < $n; $i++)
						{
							if ($r == 0 || !isset($assocs[$i])) {
								$assocs[$i] = new stdClass;
								$assocs[$i]->id = NULL;
								$assocs[$i]->cid = NULL;
								$assocs[$i]->oid = NULL;
								$assocs[$i]->type = NULL;
								$assocs[$i]->tbl = NULL;
							}
							echo '  <tr>'."\n";
							//echo '   <td><input type="text" name="assocs['.$i.'][type]" value="'.$assocs[$i]->type.'" size="5" /></td>'."\n";
							echo '   <td><select name="assocs['.$i.'][tbl]">'."\n";
							echo ' <option value=""';
							echo ($assocs[$i]->tbl == '') ? ' selected="selected"': '';
							echo '>'.JText::_('SELECT').'</option>'."\n";
							//echo ' <option value="content"';
							//echo ($assocs[$i]->tbl == 'content') ? ' selected="selected"': '';
							//echo '>'.JText::_('CONTENT').'</option>'."\n";
							echo ' <option value="resource"';
							echo ($assocs[$i]->tbl == 'resource') ? ' selected="selected"': '';
							echo '>'.JText::_('RESOURCE').'</option>'."\n";
							echo ' <option value="publication"';
							echo ($assocs[$i]->tbl == 'publication') ? ' selected="selected"': '';
							echo '>'.JText::_('Publication').'</option>'."\n";
							//echo ' <option value="topic"';
							//echo ($assocs[$i]->tbl == 'topic') ? ' selected="selected"': '';
							//echo '>'.JText::_('TOPIC').'</option>'."\n";
							echo '</select>'."\n";
							echo '   <td><input type="text" name="assocs['.$i.'][oid]" value="'.$assocs[$i]->oid.'" size="5" /></td>'."\n";
							echo '<input type="hidden" name="assocs['.$i.'][id]" value="'.$assocs[$i]->id.'" />'."\n";
							echo '<input type="hidden" name="assocs['.$i.'][cid]" value="'.$assocs[$i]->cid.'" /></td>'."\n";
							echo '  </tr>'."\n";
						}
				?>
				</tbody>
			</table>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('AFFILIATION'); ?></span></legend>

			<div class="input-wrap">
				<input type="checkbox" name="citation[affiliated]" id="affiliated" value="1"<?php if ($this->row->affiliated) { echo ' checked="checked"'; } ?> />
				<label for="affiliated"><?php echo JText::_('AFFILIATED_WITH_YOUR_ORG'); ?></label>
			</div>
			<div class="input-wrap">
				<input type="checkbox" name="citation[fundedby]" id="fundedby" value="1"<?php if ($this->row->fundedby) { echo ' checked="checked"'; } ?> />
				<label for="fundedby"><?php echo JText::_('FUNDED_BY_YOUR_ORG'); ?></label>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('Citation Sponsors'); ?></span></legend>

			<div class="input-wrap">
				<select name="sponsors[]" multiple="multiple">
					<!--<option value="sponsors">- Select Citation Sponsor &mdash;</option>-->
					<?php foreach ($this->sponsors as $s) : ?>
						<?php $sel = (in_array($s['id'], $this->row_sponsors)) ? 'selected="selected"': ''; ?>
						<option <?php echo $sel; ?> value="<?php echo $s['id']; ?>"><?php echo $this->escape(stripslashes($s['sponsor'])); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="hint">Select multiple sponsors by Ctrl + click.</span>
			</div>
		</fieldset>

		<?php if ($this->config->get('citation_allow_tags', 'no') == 'yes') : ?>
			<fieldset class="adminform">
				<legend><span><?php echo JText::_('Tags'); ?></span></legend>

				<div class="input-wrap">
					<?php
						$t = array();
						foreach ($this->tags as $tag) 
						{
							$t[] = stripslashes($tag['raw_tag']);
						}
					?>
					<textarea name="tags" rows="10"><?php echo implode(',', $t); ?></textarea>
				</div>
			</fieldset>
		<?php endif; ?>
		
		<?php if ($this->config->get('citation_allow_badges', 'no') == 'yes') : ?>
			<fieldset class="adminform">
				<legend><span><?php echo JText::_('Badges'); ?></span></legend>

				<div class="input-wrap">
					<?php
						$b = array();
						foreach ($this->badges as $badge) 
						{
							$b[] = stripslashes($badge['raw_tag']);
						}
					?>
					<textarea name="badges" rows="10"><?php echo implode(',', $b); ?></textarea>
				</div>
			</fieldset>
		<?php endif; ?>
		
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('Exclude from Export'); ?></span></legend>

			<div class="input-wrap">
				<textarea name="exclude" rows="10"><?php echo $this->params->get('exclude'); ?></textarea>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('Abstract Rollover'); ?></span></legend>

			<?php
				$rollovers = $this->config->get("citation_rollover", "no");
				$rollover  = $this->params->get("rollover");

				//check the the global setting
				if ($rollovers == 'yes')
				{
					$ckd = 'checked="checked"';
				}
				else
				{
					$ckd = '';
				}

				//check this citations setting
				if ($rollover == 1) 
				{
					$ckd = 'checked="checked"';
				}
				elseif ($rollover == 0 && is_numeric($rollover))
				{
					$ckd = '';
				}
				else
				{
					$ckd = $ckd;
				}
			?>
			<div class="input-wrap">
				<input type="checkbox" name="rollover" id="rollover" value="1" <?php echo $ckd; ?> /> <label for="rollover">Show Abstract in Rollover</label>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="citation[uid]" value="<?php echo $this->row->uid; ?>" />
	<input type="hidden" name="citation[created]" value="<?php echo $this->row->created; ?>" />
	<input type="hidden" name="citation[id]" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo JHTML::_('form.token'); ?>
</form>
