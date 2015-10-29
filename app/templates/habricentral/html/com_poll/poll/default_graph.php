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

// No direct access.
defined('_HZEXEC_') or die();
?>
<br />
<table class="pollstableborder" cellspacing="0" cellpadding="0" border="0">
<thead>
	<tr>
		<th colspan="3" class="sectiontableheader">
			<img src="<?php echo $this->baseurl; ?>/core/components/com_poll/assets/img/poll.png" align="middle" border="0" width="12" height="14" alt="" />
			<?php echo $this->poll->title; ?>
		</th>
	</tr>
</thead>
<tbody>
<?php foreach($this->votes as $vote) : ?>
	<tr class="sectiontableentry<?php echo $vote->odd; ?>">
		<td width="100%" colspan="3">
			<?php echo $vote->text; ?>
		</td>
	</tr>
	<tr class="sectiontableentry<?php echo $vote->odd; ?>">
		<td align="right" width="25">
			<strong><?php echo $vote->hits; ?></strong>&nbsp;
		</td>
		<td width="30" >
			<?php echo $vote->percent; ?>%
		</td>
		<td width="300" >
			<div class="<?php echo $vote->class; ?>" style="height:<?php echo $vote->barheight; ?>px;width:<?php echo $vote->percent; ?>%"></div>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0">
<tbody>
	<tr>
		<td class="smalldark">
			<?php echo Lang::txt( 'Number of Voters' ); ?>
		</td>
		<td class="smalldark">
			&nbsp;:&nbsp;
			<?php if(isset($this->votes[0])) echo $this->votes[0]->voters; ?>
		</td>
	</tr>
	<tr>
		<td class="smalldark">
			<?php echo Lang::txt( 'First Vote' ); ?>
		</td>
		<td class="smalldark">
			&nbsp;:&nbsp;
			<?php echo $this->first_vote; ?>
		</td>
	</tr>
	<tr>
		<td class="smalldark">
			<?php echo Lang::txt( 'Last Vote' ); ?>
		</td>
		<td class="smalldark">
			&nbsp;:&nbsp;
			<?php echo $this->last_vote; ?>
		</td>
	</tr>
</tbody>
</table>