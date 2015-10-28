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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

//default logo
$default_logo = DS.'core'.DS.'components'.DS.$this->option.DS.'site'.DS.'assets'.DS.'img'.DS.'course_default_logo.png';

//access levels
$levels = array(
	//'anyone' => 'Enabled/On',
	'anyone' => 'Any HUB Visitor',
	'registered' => 'Only Registered User of the HUB',
	'members' => 'Only Course Members',
	'nobody' => 'Disabled/Off'
);

$this->css('course.css')
     ->js();
?>

<header id="content-header">
	<h2><?php echo $this->title; ?></h2>

	<div id="content-header-extra">
		<ul id="useroptions">
			<li class="last"><a class="course" href="<?php echo Route::url($this->course->link()); ?>"><?php echo Lang::txt('Back to Course'); ?></a></li>
		</ul>
	</div><!-- / #content-header-extra -->
</header>

	<?php
		foreach ($this->notifications as $notification) {
			echo "<p class=\"{$notification['type']}\">{$notification['message']}</p>";
		}
	?>
<section class="main section">
	<form name="customize" method="POST" action="<?php echo Route::url('index.php?option=' . $this->option); ?>" id="hubForm">
		<div class="explaination">
			<div id="asset_browser">
				<p><strong><?php echo Lang::txt('Upload files or images:'); ?></strong></p>
				<iframe width="100%" height="300" name="filer" id="filer" src="<?php echo Request::base(true); ?>/index.php?option=<?php echo $this->option; ?>&amp;no_html=1&amp;task=media&amp;listdir=<?php echo $this->course->get('id'); ?>"></iframe>
			</div><!-- / .asset_browser -->
		</div>

		<fieldset id="top_box">
			<legend>Course Logo</legend>
			<p>Upload your logo using the file upload browser to the right first then refresh your browser and select it in the drop down below.</p>
			<label>
				<select name="course[logo]" id="course_logo" rel="<?php echo $this->course->get('gidNumber'); ?>">
					<option value="">Select a course logo...</option>
					<?php foreach ($this->logos as $logo) { ?>
						<?php
							$remove = PATH_APP . DS . 'site' . DS . 'courses' . DS . $this->course->get('gidNumber') . DS;
							$sel = (str_replace($remove,"",$logo) == $this->course->get('logo')) ? 'selected' : '';
						?>
						<option <?php echo $sel; ?> value="<?php echo str_replace(JPATH_SITE,"",$logo); ?>"><?php echo str_replace($remove,"",$logo); ?></option>
					<?php } ?>
				</select>
			</label>
			<label>
				<div class="preview" id="logo">
					<div id="logo_picked">
						<?php if ($this->course->get('logo')) { ?>
							<img src="<?php echo Request::base(); ?>/site/courses/<?php echo $this->course->get('gidNumber'); ?>/<?php echo $this->course->get('logo'); ?>" alt="<?php echo $this->course->get('cn') ?>" />
						<?php } else { ?>
							<img src="<?php echo $default_logo; ?>" alt="<?php echo $this->course->get('cn') ?>" >
						<?php } ?>
					</div>
				</div>
			</label>
		</fieldset>

		<fieldset>
			<legend>Course Main Content</legend>
			<p>This is the content that appears on the main (overview tab) for each course. You can choose to use the default which is your course description and a selection of course members or you can also place custom content using wiki-syntax</p>
			<div class="preview">
				<img src="<?php echo Request::base(); ?>/core/components/com_courses/site/assets/img/course_overview_preview.jpg" alt="Course Overview Content" />
			</div>

			<fieldset>
			<legend>Pick Overview Content Type</legend>
			<p class="side-by-side<?php if ($this->course->get('overview_type') == 0) { echo ' checked'; } ?>">
				<label>
					<input type="radio" name="course[overview_type]" id="course_overview_type_default" value="0" <?php if ($this->course->get('overview_type') == 0) { echo 'checked'; } ?>> Default Content
				</label>
			</p>
			<p class="side-by-side<?php if ($this->course->get('overview_type') == 1) { echo ' checked'; } ?>">
				<label>
					<input type="radio" name="course[overview_type]" id="course_overview_type_custom" value="1" <?php if ($this->course->get('overview_type') == 1) { echo 'checked'; } ?>> Custom Content
				</label>
			</p>
			<br class="clear" />
			</fieldset>

			<fieldset id="overview_content">
				<legend>Enter Custom Overview Content</legend>
				<label for="field_description">
					<?php
						echo $this->editor('course[description]', $this->escape(stripslashes($this->course->get('description'))), 35, 50, 'field_description');
					?>
				</label>
			</fieldset>
		</fieldset>

		<fieldset>
			<legend>Course Access</legend>
			<p>Below is a list of all tabs available to courses on this HUB. You can set access permissions on a per course basis by changing the value in the dropdown corresponding with each link. If you have not previously set permissions but notice that some are pre-selected, that is because those are the defaults set until a course manager overrides them.</p>

			<fieldset class="preview">
				<legend>Set Permissions for each Tab</legend>
				<ul id="access">
					<img src="<?php echo $default_logo; ?>" alt="<?php echo $this->course->get('cn') ?>" >
					<?php for ($i=0; $i<count($this->hub_course_plugins); $i++) { ?>
						<?php if ($this->hub_course_plugins[$i]['display_menu_tab']) { ?>
							<li class="course_access_control_<?php echo strtolower($this->hub_course_plugins[$i]['title']); ?>">
								<input type="hidden" name="course_plugin[<?php echo $i; ?>][name]" value="<?php echo $this->hub_course_plugins[$i]['name']; ?>">
								<span class="menu_item_title"><?php echo $this->hub_course_plugins[$i]['title']; ?></span>
								<select name="course_plugin[<?php echo $i; ?>][access]">
									<?php foreach ($levels as $level => $name) { ?>
										<?php $sel = ($this->course_plugin_access[$this->hub_course_plugins[$i]['name']] == $level) ? 'selected' : ''; ?>
										<?php if (($this->hub_course_plugins[$i]['name'] == 'overview' && $level != 'nobody') || $this->hub_course_plugins[$i]['name'] != 'overview') { ?>
											<option <?php echo $sel; ?> value="<?php echo $level; ?>"><?php echo $name; ?></option>
										<?php } ?>
									<?php } ?>
								</select>
							</li>
						<?php } ?>
					<?php } ?>
				</ul>
			</fieldset>
		</fieldset>

		<fieldset id="bottom_box">
			<h3>Course Custom Content</h3>
			<p>Course Custom Content includes all the course pages and any course modules at also appear on those pages. Clicking the link below will take you to a different interface where you can add, edit, reorder, turn on/off any course page or module.</p>
			<p><a class="leave_area" rel="You are about to leave the course customization area, and any changes you have made will not be saved. Are you sure you want to continue?" href="<?php echo Route::url('index.php?option='.$this->option.'&gid='.$this->course->get('cn').'&task=managepages'); ?>">Manage Course Pages</a></p>
		</fieldset>

		<p class="submit">
			<input class="btn btn-success" type="submit" name="course[submit]" value="Save Course Customization" />
		</p>

		<input type="hidden" name="option" value="<?php echo $this->option; ?>">
		<input type="hidden" name="task" value="savecustomization">
		<input type="hidden" name="id" value="<?php echo $this->course->get('id'); ?>">
	</form>
</section>
