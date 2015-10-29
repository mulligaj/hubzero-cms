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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
	<title><?php echo Lang::txt('COM_CONTRIBUTE'); ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" type="text/css" media="screen" href="/templates/<?php echo $app->getTemplate(); ?>/css/main.css" />
	<?php
		if (is_file(JPATH_ROOT.DS.'templates'.DS. $app->getTemplate() .DS.'html'.DS.$this->option.DS.'contribute.css')) {
			echo '<link rel="stylesheet" type="text/css" media="screen" href="'.DS.'templates'.DS. $app->getTemplate() .DS.'html'.DS.$this->option.DS.'contribute.css" />'."\n";
		} else {
			echo '<link rel="stylesheet" type="text/css" media="screen" href="'.DS.'components'.DS.$this->option.DS.'contribute.css" />'."\n";
		}
	?>
	
    <script type="text/javascript" src="/media/system/js/mootools.js"></script>
	<script type="text/javascript" src="/components/<?php echo $this->option; ?>/contribute.js"></script>
 </head>
 <body id="small-page">
<?php if ($this->getError()) { ?>
		<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>
		<form action="index.php" id="authors-form" method="post" enctype="multipart/form-data">
			<fieldset>
				<label>
					<select name="authid" id="authid">
						<option value=""><?php echo Lang::txt('COM_CONTRIBUTE_AUTHORS_SELECT'); ?></option>
					<?php
					foreach ($this->rows as $row) 
					{
						if ($row->lname || $row->fname) {
							$name  = stripslashes($row->lname).', ';
							$name .= stripslashes($row->fname);
							if ($row->mname != NULL) {
								$name .= ' '.stripslashes($row->mname);
							}
						} else {
							$name = stripslashes($row->name);
						}
						
						echo '<option value="'.$row->uidNumber.'">'.$name.'</option>'."\n";
					}
					?> 
					</select>
					<?php echo Lang::txt('COM_CONTRIBUTE_OR'); ?>
				</label>
				
				<label>
					<input type="text" name="new_authors" value="" />
					<?php echo Lang::txt('COM_CONTRIBUTE_AUTHORS_ENTER_LOGINS'); ?>
				</label>
				
				<p class="submit">
					<input type="submit" value="<?php echo Lang::txt('COM_CONTRIBUTE_ADD'); ?>" />
				</p>

				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="no_html" value="1" />
				<input type="hidden" name="pid" id="pid" value="<?php echo $this->id; ?>" />
				<input type="hidden" name="task" value="saveauthor" />
			</fieldset>
		</form>
<?php		
// Do we have any contributors associated with this resource?
if ($this->contributors) {
	$i = 0;
	$n = count( $this->contributors );
	
?>
		<table class="list">
			<tbody>
<?php
	foreach ($this->contributors as $contributor) 
	{
		if ($contributor->lastname || $contributor->firstname) {
			$name  = stripslashes($contributor->firstname) .' ';
			if ($contributor->middlename != NULL) {
				$name .= stripslashes($contributor->middlename) .' ';
			}
			$name .= stripslashes($contributor->lastname);
		} else {
			$name  = stripslashes($contributor->name);
		}
?>
				<tr>
					<td width="100%">
						<?php echo $name; ?>
						<?php echo ($contributor->org) ? ' <span class="caption">('.$contributor->org.')</span>' : ''; ?>
					</td>
					<td class="u"><?php
					if ($i > 0 || ($i+0 > 0)) {
					    echo '<a href="index.php?option=com_contribute&amp;no_html=1&amp;pid='.$this->id.'&amp;id='.$contributor->id.'&amp;task=orderupc" class="order up" title="'.Lang::txt('COM_CONTRIBUTE_MOVE_UP').'"><span>'.Lang::txt('COM_CONTRIBUTE_MOVE_UP').'</span></a>';
			  		} else {
			  		    echo '&nbsp;';
					}
					?></td>
					<td class="d"><?php
					if ($i < $n-1 || $i+0 < $n-1) {
						echo '<a href="index.php?option=com_contribute&amp;no_html=1&amp;pid='.$this->id.'&amp;id='.$contributor->id.'&amp;task=orderdownc" class="order down" title="'.Lang::txt('COM_CONTRIBUTE_MOVE_DOWN').'"><span>'.Lang::txt('COM_CONTRIBUTE_MOVE_DOWN').'</span></a>';
			  		} else {
			  		    echo '&nbsp;';
					}
					?></td>
					<td class="t"><a href="index.php?option=<?php echo $this->option; ?>&amp;task=removeauthor&amp;no_html=1&amp;id=<?php echo $contributor->id; ?>&amp;pid=<?php echo $this->id; ?>"><img src="/components/<?php echo $this->option; ?>/images/trash.gif" alt="<?php echo Lang::txt('COM_CONTRIBUTE_DELETE'); ?>" /></a></td>
				</tr>
<?php
		$i++;
	}
?>
			</tbody>
		</table>
<?php } else { ?>
		<p><?php echo Lang::txt('COM_CONTRIBUTE_AUTHORS_NONE_FOUND'); ?></p>
<?php } ?>
 </body>
</html>