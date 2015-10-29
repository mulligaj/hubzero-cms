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

$database = App::get('db');

$submissions = null;
if (!User::get('guest'))
{
	$query  = "SELECT DISTINCT R.id, R.title, R.type, R.logical_type AS logicaltype,
						AA.subtable, R.created, R.created_by, R.published, R.publish_up, R.standalone,
						R.rating, R.times_rated, R.alias, R.ranking, rt.type AS typetitle ";
	$query .= "FROM #__author_assoc AS AA, #__resource_types AS rt, #__resources AS R ";
	$query .= "LEFT JOIN #__resource_types AS t ON R.logical_type=t.id ";
	$query .= "WHERE AA.authorid = ". User::get('id') ." ";
	$query .= "AND R.id = AA.subid ";
	$query .= "AND AA.subtable = 'resources' ";
	$query .= "AND R.standalone=1 AND R.type=rt.id AND (R.published=2 OR R.published=3) AND R.type!=7 ";
	$query .= "ORDER BY published ASC, title ASC";

	$database->setQuery($query);
	$submissions = $database->loadObjectList();
}

$this->css('introduction.css', 'system')
     ->css('create.css');
?>
<div id="content-header" class="full">
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<section id="introduction" class="contribute section">
	<div class="grid">
		<div class="col span9">
			<div class="col span6">
				<h3>Present your work!</h3>
			<p>Upload your work to the HABRI Central repository to share your contributions with the entire human-animal bond community.</p>
			</div>
			<div class="col span6 omega">
				<h3>What do I need?</h3>
			<p>To submit your work, all you need are the original file(s) and some basic information about them; we will guide you through the rest of the process.</p>
			</div>
		</div>
		<div class="col span3 omega">
			<p id="getstarted">
				<a class="btn btn-primary" href="<?php echo Route::url('index.php?option='.$option.'&task=draft'); ?>">Get Started ›</a>
			</p>
		</div><!-- / .aside -->
	</div>
</section>

<div class="section">

<?php if (!User::get('guest')) { ?>
	<div class="four columns first">
		<h2><?php echo Lang::txt('In Progress'); ?></h2>
	</div><!-- / .four columns first -->
	<div class="four columns second third fourth">
<?php
		if ($submissions) {
?>
		<table id="submissions" summary="<?php echo Lang::txt('Contributions in progress'); ?>">
			<thead>
				<tr>
					<th scope="col"><?php echo Lang::txt('Title'); ?></th>
					<th scope="col" colspan="3"><?php echo Lang::txt('Associations'); ?></th>
					<th scope="col" colspan="2"><?php echo Lang::txt('Status'); ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			$ra = new \Components\Resources\Tables\Assoc( $database );
			$rc = new \Components\Resources\Tables\Contributor( $database );
			$rt = new \Components\Resources\Tables\Tags( $database );
			$cls = 'even';
			foreach ($submissions as $submission)
			{
				$cls = ($cls == 'even') ? 'odd' : 'even';

				switch ($submission->published)
				{
					case 1: $state = 'published';  break;  // published
					case 2: $state = 'draft';      break;  // draft
					case 3: $state = 'pending';    break;  // pending
				}

				$attachments = $ra->getCount( $submission->id );

				$authors = $rc->getCount( $submission->id, 'resources' );

				$tags = $rt->tags( $submission->id );
?>
				<tr class="<?php echo $cls; ?>">
						<td><?php if ($submission->published == 2) { ?><a href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft&step=1&id='.$submission->id); ?>"><?php } ?><?php echo stripslashes($submission->title); ?><?php if ($submission->published == 2) { ?></a><?php } ?><br /><span class="type"><?php echo stripslashes($submission->typetitle); ?></span></td>
						<td><?php if ($submission->published == 2) { ?><a href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft&step=2&id='.$submission->id); ?>"><?php } ?><?php echo $attachments; ?> attachment(s)<?php if ($submission->published == 2) { ?></a><?php } ?></td>
						<td><?php if ($submission->published == 2) { ?><a href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft&step=3&id='.$submission->id); ?>"><?php } ?><?php echo $authors; ?> author(s)<?php if ($submission->published == 2) { ?></a><?php } ?></td>
						<td><?php if ($submission->published == 2) { ?><a href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft&step=4&id='.$submission->id); ?>"><?php } ?><?php echo count($tags); ?> tag(s)<?php if ($submission->published == 2) { ?></a><?php } ?></td>
						<td>
							<span class="<?php echo $state; ?> status"><?php echo $state; ?></span>
							<?php if ($submission->published == 2) { ?>
							<br /><a class="review" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=draft&step=5&id='.$submission->id); ?>"><?php echo Lang::txt('Review &amp; Submit &rsaquo;'); ?></a>
							<?php } elseif ($submission->published == 3) { ?>
							<br /><a class="retract" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=retract&id='.$submission->id); ?>"><?php echo Lang::txt('&lsaquo; Retract'); ?></a>
							<?php } ?>
						</td>
						<td><a class="icon-delete" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=discard&id='.$submission->id); ?>" title="<?php echo Lang::txt('Delete'); ?>"><?php echo Lang::txt('Delete'); ?></a></td>
					</tr>
<?php
			}
?>
			</tbody>
		</table>
<?php
		} else {
?>
		<p class="info">
			<strong>You currently have no contributions in progress.</strong><br /><br />
			Once you've started a new contribution, you can proceed at your leisure. Stop half-way through and watch a presentation, go to lunch, even close the browser and come back a different day! Your contribution will be waiting just as you left it, ready to continue at any time.
		</p>
<?php
		}
?>
	</div><!-- / .four columns second third fourth -->
<?php } ?>

	<div class="four columns first">
		<h2>Before starting</h2>
	</div><!-- / .four columns first -->
	<div class="four columns second third fourth">
		<div class="two columns first">
			<h3>Intellectual Property Considerations</h3>
			<p>To submit content to the HABRI Central repository, you must hold the right to distribute the content to be submitted. Some items, such as journal articles and other previously published works, may require publisher authorization as well. For more information, see our <a href="/legal/licensing">Intellectual Property Guide for Submitters</a> or <a href="/feedback/suggestions">contact us</a> for more information.</p>
		</div>
		<div class="two columns second">
			<h3>Questions or concerns?</h3>
			<p>Should you encounter any problems during the submission process, <a href="/feedback/suggestions">let us know</a> or submit a <a href="/feedback/report_problems">support ticket</a> and we will work with you to resolve the issue.</p>
		</div>
	</div><!-- / .four columns second third fourth -->
	<div class="clear"></div>

<?php
$t = new \Components\Resources\Tables\Type( $database );
$categories = $t->getMajorTypes();
if ($categories) {
?>
	<div class="four columns first">
		<h2>What can I contribute?</h2>
		<!-- <p>If you have a contribution that does not seem to fit one of these categories, please contact our <a href="/support">support</a> for further assistance.</p> -->
	</div><!-- / .four columns first -->
	<div class="four columns second third fourth">
<?php
	$i = 0;
	$clm = '';
	/*if (count($categories)%3!=0) {
	    ;
	}*/
	foreach ($categories as $category)
	{
		if ($category->contributable != 1) {
			continue;
		}

		$i++;
		switch ($clm)
		{
			case 'second': $clm = 'third'; break;
			case 'first': $clm = 'second'; break;
			case '':
			default: $clm = 'first'; break;
		}

		//$normalized = preg_replace("/[^a-zA-Z0-9]/", "", $category->type);
		//$normalized = strtolower($normalized);

		if (substr($category->alias, -3) == 'ies') {
			$cls = $category->alias;
		} else {
			$cls = substr($category->alias, 0, -1);
		}
?>
		<div class="three columns <?php echo $clm; ?>">
			<div class="<?php echo $cls; ?>">
				<h3><a href="<?php echo Route::url('index.php?option='.$this->option.'&task=draft&step=1&type='.$category->id); ?>"><?php echo stripslashes($category->type); ?></a></h3>
				<p><?php echo $this->escape(stripslashes(strip_tags($category->description))); ?></p>
			</div>
		</div><!-- / .three columns <?php echo $clm; ?> -->
<?php
		if ($clm == 'third') {
			echo '<div class="clear"></div>';
			$clm = '';
			$i = 0;
		}
	}
	if ($i == 1) {
		?>
		<div class="three columns second">
			<p> </p>
		</div><!-- / .three columns second -->
		<?php
	}
	if ($i == 1 || $i == 2) {
		?>
		<div class="three columns third">
			<p> </p>
		</div><!-- / .three columns third -->
		<?php
	}
?>
	</div><!-- / .four columns second third fourth -->
	<div class="clear"></div>
<?php
}
?>

</div><!-- / .section -->
