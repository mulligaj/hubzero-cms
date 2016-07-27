<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 * All rights reserved.
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
 * @author    Kevin Wojkovich <kevinw@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die;
?>

<?php if ($posts != false): ?>
<div class="news-feed">

<?php
	$actualItems = count($posts);
	$setItems    = $params->get('itemcount', 5);

	if ($setItems > $actualItems)
	{
		$totalItems = $actualItems;
	}
	else
	{
		$totalItems = $setItems;
	}
	?>
	<ul class="feed">
			<?php
			$words = $params->def('word_count', 0);
			foreach ($posts as $currItem)
			{
				// item title
				?>
				<li class="newsfeed-item">
					<div class="item">
						<h5 class="feed-link" style="padding: 15px;">
							<a href="<?php echo $currItem->link; ?>" target="_blank">
								<?php
								$currItem->title = html_entity_decode($currItem->title);
								$currItem->title = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $currItem->title);
								echo preg_replace("/[^A-Za-z0-9'\"_\- ]/", '', $currItem->title); ?>
							</a>
						</h5>

					<?php
					// item description
					if ($params->get('showdescription', 1))
					{
						// item description
						$text = $currItem->description;
						$text = html_entity_decode($text);
						$text = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $text);
						$text = strip_tags($text);
						// word limit check
						if ($words)
						{
							$texts = explode(' ', $text);
							$count = count($texts);
							if ($count > $words)
							{
								$text = '';
								for ($i = 0; $i < $words; $i ++)
								{
									$text .= ' '.$texts[$i];
								}
								$text .= '...';
							}
						}
						?>

							<h4 style="padding-top:10px;"><?php echo preg_replace("/[^A-Za-z0-9'\"_\- ]/", '',$text); ?></h4>

						<?php
					}
					?>
					</div>
				</li>
				<?php
			}
			?>
			</ul>
	</div>
<?php endif; ?>
