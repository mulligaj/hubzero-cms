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


// Get parameters
$rparams = new \Hubzero\Config\Registry($this->resource->params);
$params = $this->config;
$params->merge($rparams);
?>
<div id="content-header">
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<div id="content-header-extra">
	<p>
		<a class="add btn" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft'); ?>">
			<?php echo Lang::txt('COM_CONTRIBUTE_NEW_SUBMISSION'); ?>
		</a>
	</p>
</div><!-- / #content-header -->

<div class="main section">
<?php
	$view = $this->view('steps', 'steps');
	$view->option = $this->option;
	$view->step = $this->step;
	$view->steps = $this->steps;
	$view->id = $this->id;
	$view->resource = $this->resource;
	$view->progress = $this->progress;
	$view->display();
?>

<?php if ($this->getError()) { ?>
	<p class="warning"><?php echo $this->getError(); ?></p>
<?php } ?>
<?php

if ($this->progress['submitted'] == 1) {
	if (substr($params->get('license'), 0, 2) == 'cc') {
		/*?>
		<p>This resource is licensed under the <a class="popup" href="legal/cc/">Creative Commons 3.0</a> license recommended by <?php echo $hubShortName; ?>. 
		The <a class="popup" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">license terms</a> support 
		non-commercial use, require attribution, and require sharing derivative works under the same license.</p>
		<?php*/
	} else {
		?>
		<form action="<?php echo Route::url('index.php?option='.$this->option); ?>" method="post" id="hubForm">
			<div class="explaination">
				<h4>What happens after I submit?</h4>
				<p>Your submission will be reviewed. If it is accepted, the submission will be given a "live" status and will appear 
				in our <a href="<?php echo Route::url('index.php?option=com_resources'); ?>">resources</a> and at the top of our <a href="<?php echo Route::url('index.php?option=com_whatsnew'); ?>">What's New</a> listing.</p>
			</div>
			<fieldset>
				<h3>Licensing</h3>
				<label for="license">
					<?php echo Lang::txt('Additional License'); ?>
					<select name="license" id="license">
						<option value=""><?php echo Lang::txt('Select license...'); ?></option>
<?php 
				$l = array();
				$c = false;
				foreach ($this->licenses as $license) 
				{
					if (substr($license->name, 0, 6) == 'custom') 
					{
					?>
						<option value="custom"<?php if ($params->get('license') == $license->name) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('Custom'); ?></option>
					<?php 
						$l[] = '<input type="hidden" id="license-custom" value="' . $this->escape(nl2br($license->text)) . '" />';
						$c = $this->escape(nl2br($license->text));
					} 
				}
				if (!$c && $this->config->get('cc_license_custom'))
				{
					?>
						<option value="custom"><?php echo Lang::txt('Custom'); ?></option>
					<?php
					$c = $this->escape(Lang::txt('[ENTER LICENSE HERE]'));
					$l[] = '<input type="hidden" id="license-custom" value="' . $this->escape(Lang::txt('[ENTER LICENSE HERE]')) . '" />';
				}
				foreach ($this->licenses as $license) 
				{
					//if (substr($license, 0, 6) == 'custom' && intval(substr($license, 7)) != $this->id) 
					//if ($license->name == 'custom' && intval($license->info) != $this->id) 
					if (substr($license->name, 0, 6) == 'custom') 
					{
						continue;
					}
					else 
					{
					?>
						<option value="<?php echo $this->escape($license->name); ?>"<?php if ($params->get('license') == $license->name) { echo ' selected="selected"'; } ?>><?php echo $this->escape($license->title); ?></option>
					<?php
					} 
					$l[] = '<input type="hidden" id="license-' . $this->escape($license->name) . '" value="' . $this->escape(nl2br($license->text)) . '" />';
				} 
?>
					</select>
					<div id="license-preview" style="display:none;"><?php echo Lang::txt('License preview.'); ?></div>
					<?php echo implode("\n", $l); ?>
				</label>
				<?php if ($this->config->get('cc_license_custom')) { ?>
				<textarea name="license-text" id="license-text" cols="35" rows="10" style="display:none;"><?php echo $c; ?></textarea>
				<?php } ?>
				
				<!-- <label><input class="option" type="checkbox" name="license" value="1" /> <span class="optional">optional</span> 
				License the work under the <a class="popup" href="legal/cc/">Creative Commons 3.0</a> license recommended by <?php echo $jconfig->getValue('config.sitename'); ?>. 
				The <a class="popup" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">license terms</a> support 
				non-commercial use, require attribution, and require sharing derivative works under the same license.</label> -->
			
				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
				<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
				<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
				<input type="hidden" name="published" value="1" />
		 	</fieldset><div class="clear"></div>
			<p class="submit">
				<input type="submit" value="Save" />
			</p>
		</form>
		<?php
	}
	?>
	<p class="help">This contribution has already been submitted and passed review. <a href="<?php echo Route::url('index.php?option=com_resources&id='.$this->id); ?>">View it here</a></p>
	<?php
} else {
	?>
	<form action="index.php" method="post" id="hubForm">
		<p class="info">
			In order for HABRI Central to display your content, we must be given legal license to do so. At the very least, HABRI Central must be authorized to 
			hold, copy, distribute, and perform (play back) your material according to <a class="popup" href="/legal/license">this agreement</a>. 
			You will retain any copyrights to the materials and decide how they should be licensed for end-user access. We encourage you to <a class="popup" href="/legal/licensing">license your contributions</a> 
			so that others can build upon them.
		</p>
		<div class="explaination">
			<h4>What happens after I submit?</h4>
			<p>Your submission will be reviewed. If it is accepted, the submission will be given a "live" status and will appear 
			in our <a href="<?php echo Route::url('index.php?option=com_resources'); ?>">resources</a> and at the top of our <a href="<?php echo Route::url('index.php?option=com_whatsnew'); ?>">What's New</a> listing.</p>
		</div>
		<fieldset>
			<h3>Authorization</h3>
			<label><input class="option" type="checkbox" name="authorization" value="1" /> <span class="required">required</span> I hereby grant to Purdue University a non-exclusive perpetual royalty free license to use, duplicate and distribute the work (“Work”) in whole or in part. The Work is to be deposited in the Purdue University's HABRI Central repository. I further grant to Purdue University the right to transfer the Work to any format or medium now known or later developed for preservation and access in accordance with this agreement. This agreement does not represent a transfer of copyright to Purdue University.
				<br /><br />
			I represent and warrant to Purdue University that the Work is my original work and does not, to the best of my knowledge, infringe or violate any rights of others nor does the deposit violate any applicable laws. I further represent and warrant that I have the authority and/or have obtained all necessary rights to permit Purdue University to use, duplicate and distribute the Work and that any third-party owned content is clearly identified and acknowledged within the Work.
				<br /><br />
			By granting this non-exclusive license, I acknowledge that I have read and agreed to the terms of this agreement and all related Purdue University policies.</label>
			
<?php if ($this->config->get('cc_license')) { ?>
			<label for="license">
				<?php echo Lang::txt('Additional License:'); ?> <span class="optional">optional</span>
				<select name="license" id="license">
					<option value=""><?php echo Lang::txt('No additional license'); ?></option>
			<?php if ($this->config->get('cc_license_custom')) { ?>
					<option value="custom"><?php echo Lang::txt('Custom'); ?></option>
			<?php } ?>
			<?php 
			$l = array();
			$l[] = '<input type="hidden" id="license-custom" value="' . $this->escape(Lang::txt('[ENTER LICENSE HERE]')) . '" />';
			foreach ($this->licenses as $license) 
			{ 
				?>
					<option value="<?php echo $this->escape($license->name); ?>"><?php echo $this->escape($license->title); ?></option>
				<?php 
				$l[] = '<input type="hidden" id="license-' . $this->escape($license->name) . '" value="' . $this->escape(nl2br($license->text)) . '" />';
			} 
			?>
				</select>
				<div id="license-preview" style="display:none;"><?php echo Lang::txt('License preview.'); ?></div>
				<?php echo implode("\n", $l); ?>
			</label>
			<?php if ($this->config->get('cc_license_custom')) { ?>
				<textarea name="license-text" id="license-text" cols="35" rows="10" style="display:none;"><?php echo $this->escape(Lang::txt('[ENTER LICENSE HERE]')); ?></textarea>
			<?php } ?>
<?php } ?>
			
			<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
			<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
			<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
			<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
			<input type="hidden" name="published" value="0" />
	 	</fieldset><div class="clear"></div>
		<div class="submit">
			<input type="submit" value="<?php echo Lang::txt('COM_CONTRIBUTE_SUBMIT_CONTRIBUTION'); ?>" />
		</div>
	</form>
	
	<h1 id="preview-header"><?php echo Lang::txt('COM_CONTRIBUTE_REVIEW_PREVIEW'); ?></h1>
	<div id="preview-pane">
		<iframe id="preview-frame" name="preview-frame" width="100%" frameborder="0" src="<?php echo Route::url('index.php?option=com_resources&id=' . $this->id . '&tmpl=component&mode=preview'); ?>"></iframe>
	</div>
	<?php 
}
?>
</div><!-- / .main section -->
