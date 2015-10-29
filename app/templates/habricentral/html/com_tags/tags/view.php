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

$name  = Lang::txt('COM_TAGS_ALL_CATEGORIES');
$total = $this->total;
$here  = 'index.php?option=' . $this->option . '&tag=' . $this->tagstring . ($this->filters['sort'] ? '&sort=' . $this->filters['sort'] : '');

// Add the "all" category
$all = array(
	'name'    => '',
	'title'   => Lang::txt('COM_TAGS_ALL_CATEGORIES'),
	'total'   => $this->total,
	'results' => null,
	'sql'     => ''
);
$cats = $this->categories;
array_unshift($cats, $all);

// An array for storing all the links we make
$links = array();

// Loop through each category
foreach ($cats as $cat)
{
	// Only show categories that have returned search results
	if (!$cat['total'] > 0)
	{
		continue;
	}

	// If we have a specific category, prepend it to the search term
	$blob = '';
	if ($cat['name'])
	{
		$blob = $cat['name'];
	}

	$sef = Route::url($here . ($blob ? '&area=' . stripslashes($blob) : ''));
	$sef = str_replace('%20', ',', $sef);
	$sef = str_replace(' ', ',', $sef);
	$sef = str_replace('+', ',', $sef);

	// Is this the active category?
	$a = '';
	if ($cat['name'] == $this->active)
	{
		$a = ' class="active"';

		$name  = $cat['title'];
		$total = $cat['total'];

		Pathway::append($cat['title'], $here . '&area=' . stripslashes($blob));
	}

	// Build the HTML
	$l = "\t".'<li><a' . $a . ' href="' . $sef . '">' . $this->escape(stripslashes($cat['title'])) . ' <span class="item-count">' . $cat['total'] . '</span></a>';

	// Are there sub-categories?
	if (isset($cat['children']) && is_array($cat['children']))
	{
		// An array for storing the HTML we make
		$k = array();
		// Loop through each sub-category
		foreach ($cat['children'] as $subcat)
		{
			// Only show sub-categories that returned search results
			if ($subcat['total'] > 0)
			{
				// If we have a specific category, prepend it to the search term
				$blob = ($subcat['name'] ? $subcat['name'] : '');

				// Is this the active category?
				$a = '';
				if ($subcat['name'] == $this->active)
				{
					$a = ' class="active"';

					$name  = $cat['title'];
					$total = $cat['total'];

					Pathway::append($subcat['title'], $here . '&area=' . stripslashes($blob));
				}

				// Build the HTML
				$sef = Route::url($here . '&area='. stripslashes($blob));
				$sef = str_replace('%20', ',', $sef);
				$sef = str_replace(' ', ',', $sef);
				$sef = str_replace('+', ',', $sef);

				$k[] = "\t\t\t".'<li><a' . $a . ' href="' . $sef . '">' . $this->escape(stripslashes($subcat['title'])) . ' <span class="item-count">' . $subcat['total'] . '</span></a></li>';
			}
		}
		// Do we actually have any links?
		// NOTE: this method prevents returning empty list tags "<ul></ul>"
		if (count($k) > 0)
		{
			$l .= "\t\t".'<ul>'."\n";
			$l .= implode("\n", $k);
			$l .= "\t\t".'</ul>'."\n";
		}
	}
	$l .= '</li>';

	$links[] = $l;
}
?>
<header id="content-header">
	<h2><?php echo $this->title; ?></h2>

	<div id="content-header-extra">
		<ul id="useroptions">
			<li class="last">
				<a class="icon-tag tag btn" href="<?php echo Route::url('index.php?option=' . $this->option); ?>">
					<?php echo Lang::txt('COM_TAGS_MORE_TAGS'); ?>
				</a>
			</li>
		</ul>
	</div><!-- / #content-header-extra -->
</header><!-- / #content-header -->

<form action="<?php echo Route::url('index.php?option=' . $this->option); ?>" method="get">
	<section class="main section">
		<div class="subject">
			<div class="container data-entry">
				<input class="entry-search-submit" type="submit" value="<?php echo Lang::txt('COM_TAGS_SEARCH'); ?>" />
				<fieldset class="entry-search">
					<?php
					$tf = Event::trigger( 'hubzero.onGetMultiEntry', array(array('tags', 'tag', 'actags','',$this->search)) );
					?>
					<label for="actags">
						<?php echo Lang::txt('COM_TAGS_SEARCH_WITH_TAGS'); ?>
					</label>
					<?php if (count($tf) > 0) {
						echo $tf[0];
					} else { ?>
					<input type="text" name="tag" id="actags" value="<?php echo $this->escape($this->search); ?>" />
					<?php } ?>
				</fieldset>
			</div><!-- / .container -->

			<?php
				$dbh = App::get('db');

				$ids = implode(', ', array_map(create_function('$a', 'return $a->get(\'id\');'), $this->tags));

				$dbh->setQuery('SELECT tag, raw_tag FROM #__tags_object jto INNER JOIN #__tags jt ON jt.id = jto.objectid WHERE jto.label = \'parent\' AND jto.tbl = \'tags\' AND jto.tagid IN ('.$ids.') AND jt.id NOT IN ('.$ids.')');
				$children = $dbh->loadAssocList('tag');

				$dbh->setQuery('SELECT tag, raw_tag FROM #__tags_object jto INNER JOIN #__tags jt ON jt.id = jto.tagid WHERE jto.label = \'parent\' AND jto.tbl = \'tags\' AND jto.objectid IN ('.$ids.') AND jt.id NOT IN ('.$ids.')');
				$parents = $dbh->loadAssocList('tag');

				$dbh->setQuery('SELECT jt.id ,tag, raw_tag FROM #__tags_object jto LEFT JOIN #__tags_object jto2 ON jto2.tbl = jto.tbl AND jto2.objectid = jto.objectid AND jto2.label = \'\' AND jto2.tagid NOT IN ('.$ids.') INNER JOIN #__tags jt ON jt.id = jto2.tagid WHERE jto.label = \'\' AND jto.tagid IN ('.$ids.') AND jt.id NOT IN ('.$ids.') GROUP BY tag, raw_tag ORDER BY count(*) DESC LIMIT 5');
				$related = array_filter($dbh->loadAssocList(), function($tag) use($parents, $children) {
					return !isset($parents[$tag['tag']]) && !isset($children[$tag['tag']]);
				});
				$baseTags = $this->tagstring;
				$linkTag = function($tag) use($baseTags) {
					echo '<a onclick="var tags = []; $(\'.token-input-token-act p\').each(function(_idx, el) { tags.push($(el).text().toLowerCase().replace(/[^-_a-z0-9]/g, \'\')); }); tags.push(\''.$tag['tag'].'\'); this.setAttribute(\'href\', \'/tags/view?tag=\' + tags.join(\',\')); return true;" href="/tags/view?tag='.$baseTags.','.$tag['tag'].'">'.stripslashes($tag['raw_tag']).'</a></li>';
				};
			?>

			<?php if ($parents || $related || $children): ?>
				<div class="container related">
					<div class="container-block">
						<?php if ($parents): echo '<h3>Parent Tags</h3>'; ?><ol class="tags"><?php array_map($linkTag, $parents); ?></ol><?php endif; ?>
						<?php if ($related): echo '<h3>Related Tags</h3>'; ?><ol class="tags"><?php array_map($linkTag, $related); ?></ol><?php endif; ?>
						<?php if ($children): echo '<h3>Child Tags</h3>'; ?><ol class="tags"><?php array_map($linkTag, $children); ?></ol><?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php foreach ($this->tags as $tagobj) { ?>
				<?php if ($tagobj->get('description') != '') { ?>
					<div class="container">
						<div class="container-block">
							<h4><?php echo Lang::txt('COM_TAGS_DESCRIPTION'); ?></h4>
							<div class="tag-description">
								<?php echo stripslashes($tagobj->get('description')); ?>
								<div class="clearfix"></div>
							</div>
						</div>
					</div><!-- / .container -->
				<?php } ?>
			<?php } ?>

			<div class="container">
				<ul class="entries-menu">
					<li>
						<a<?php echo ($this->filters['sort'] == 'title') ? ' class="active"' : ''; ?> href="<?php
							$sef = Route::url('index.php?option='.$this->option.'&tag='.$this->tagstring.'&area='.$this->active.'&sort=title');
							$sef = str_replace('%20',',',$sef);
							$sef = str_replace(' ',',',$sef);
							$sef = str_replace('+',',',$sef);
							echo $sef;
						?>" title="<?php echo Lang::txt('COM_TAGS_OPT_SORT_BY_TITLE'); ?>">
							<?php echo Lang::txt('COM_TAGS_OPT_TITLE'); ?>
						</a>
					</li>
					<li>
						<a<?php echo ($this->filters['sort'] == 'date' || $this->filters['sort'] == '') ? ' class="active"' : ''; ?> href="<?php
							$sef = Route::url('index.php?option='.$this->option.'&tag='.$this->tagstring.'&area='.$this->active.'&sort=date');
							$sef = str_replace('%20',',',$sef);
							$sef = str_replace(' ',',',$sef);
							$sef = str_replace('+',',',$sef);
							echo $sef;
						?>" title="<?php echo Lang::txt('COM_TAGS_OPT_SORT_BY_DATE'); ?>">
							<?php echo Lang::txt('COM_TAGS_OPT_DATE'); ?>
						</a>
					</li>
				</ul>

				<div class="container-block">
					<?php
						$ttl = ($total > ($this->filters['limit'] + $this->filters['start'])) ? ($this->filters['limit'] + $this->filters['start']) : $total;
						if ($total && !$ttl)
						{
							$ttl = $total;
						}

						$base = rtrim(Request::base(), '/');

						$html  = '<h3>' . $this->escape(stripslashes($name)) . ' <span>(' . Lang::txt('%s-%s of %s', ($this->filters['start'] + 1), $ttl, $total) . ')</span></h3>'."\n";

						if ($this->results)
						{
							$html .= '<ol class="results">'."\n";
							foreach ($this->results as $row)
							{
								$obj = 'plgTags' . ucfirst($row->section);

								if (method_exists($obj, 'out'))
								{
									$html .= call_user_func(array($obj, 'out'), $row);
								}
								else
								{
									if (strstr($row->href, 'index.php'))
									{
										$row->href = Route::url($row->href);
									}

									$html .= "\t".'<li>'."\n";
									$html .= "\t\t".'<p class="title"><a href="' . $row->href . '">'.\Hubzero\Utility\Sanitize::clean($row->title) . '</a></p>'."\n";
									if ($row->ftext)
									{
										$html .= "\t\t".'<p>'.\Hubzero\Utility\String::truncate(strip_tags($row->ftext), 200)."</p>\n";
									}
									$html .= "\t\t".'<p class="href">' . $base . $row->href . '</p>'."\n";
									$html .= "\t".'</li>'."\n";
								}
							}
							$html .= '</ol>'."\n";
						}
						else
						{
							$html = '<p class="warning">' . Lang::txt('COM_TAGS_NO_RESULTS') . '</p>';
						}
						echo $html;
					?>
				</div><!-- / .container-block -->
				<?php
					$pageNav = $this->pagination(
						$this->total,
						$this->filters['start'],
						$this->filters['limit']
					);
					$pageNav->setAdditionalUrlParam('task', '');
					$pageNav->setAdditionalUrlParam('tag', $this->tagstring);
					$pageNav->setAdditionalUrlParam('active', $this->active);
					$pageNav->setAdditionalUrlParam('sort', $this->filters['sort']);

					echo $pageNav->render() . '<div class="clearfix"></div>';
				?>
			</div><!-- / .container -->
		</div><!-- / .subject -->
		<aside class="aside">
			<div class="container">
				<h3><?php echo Lang::txt('COM_TAGS_CATEGORIES'); ?></h3>
				<?php
				// Do we actually have any links?
				// NOTE: this method prevents returning empty list tags "<ul></ul>"
				if (count($links) > 0)
				{
					// Yes - output the necessary HTML
					$html  = '<ul>'."\n";
					$html .= implode("\n", $links);
					$html .= '</ul>'."\n";
				}
				else
				{
					// No - nothing to output
					$html = '';
				}
				$html .= "\t" . '<input type="hidden" name="area" value="' . $this->escape($this->active) . '" />' . "\n";

				echo $html;
				?>
				<p class="info">
					<?php echo Lang::txt('COM_TAGS_RESULTS_NOTE'); ?>
				</p>
			</div>
		</aside><!-- / .aside -->
	</section><!-- / .main section -->
	<input type="hidden" name="task" value="view" />
</form>