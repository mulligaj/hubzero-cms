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

//citation params
$label = $this->config->get("citation_label", "number");
$rollover = $this->config->get("citation_rollover", "no");
$template = $this->config->get("citation_format", "");

//batch downloads
$batch_download = $this->config->get("citation_batch_download", 1);

//Include COinS
$coins = $this->config->get("citation_coins", 1);

//do we want to number li items
if ($label == "none") {
	$citations_label_class = "no-label";
} elseif ($label == "number") {
	$citations_label_class = "number-label";
} elseif ($label == "type") {
	$citations_label_class = "type-label";
} elseif ($label == "both") {
	$citations_label_class = "both-label";
}

?>
<div id="content-header" class="full">
	<h2><?php //echo $this->title; ?>Bibliography: Browse</h2>
</div>

<div id="content-header-extra">
	<ul id="useroptions">
		<?php if ($this->allow_import == 1 || ($this->allow_import == 2 && $this->isAdmin)) : ?>
			<li class="last">
				<a class="add btn" href="<?php echo Route::url('index.php?option=com_citations&task=add'); ?>"><?php echo Lang::txt('Submit a Citation'); ?></a>
			</li>
		<?php endif; ?>
	</ul>
</div>

<div class="main section">
	<form action="<?php echo Route::url('index.php?option='.$this->option.'&controller=citations&task=browse'); ?>" id="citeform" method="GET" class="<?php if ($batch_download) { echo " withBatchDownload"; } ?>">
		<div class="aside">
			<fieldset>
				<h4>Refine Your Search</h4>
				<label>
					<?php echo Lang::txt('Resource Type'); ?>:
					<select name="type" id="type">
						<option value="">All</option>
						<?php foreach($this->types as $t) : ?>
							<?php $sel = ($this->filters['type'] == $t['id']) ? "selected=\"selected\"" : ""; ?>
 							<option <?php echo $sel; ?> value="<?php echo $t['id']; ?>"><?php echo $t['type_title']; ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<?php echo Lang::txt('Tags (topic)'); ?>:
					<?php 
						$tf = Event::trigger('hubzero.onGetMultiEntry', array(array('tags', 'tag', 'actags', '', $this->filters['tag'])));  // type, field name, field id, class, value
						if (count($tf) > 0) : ?>
							<?php echo $tf[0]; ?>
						<?php else: ?>
							<input type="text" name="tag" value="<?php echo $this->filters['tag']; ?>" />
						<?php endif; ?>
				</label>
				<label>
					<?php echo Lang::txt('Authored By'); ?>:
					<input type="text" name="author" value="<?php echo $this->filters['author']; ?>" />
				</label>
				<label>
					<?php echo Lang::txt('Published In'); ?>:
					<input type="text" name="publishedin" value="<?php echo $this->filters['publishedin']; ?>" />
				</label>
				<label for="year_start">
					<?php echo Lang::txt('Year'); ?>:<br />
					<input type="text" name="year_start" class="half" value="<?php echo $this->filters['year_start']; ?>" />
					to
					<input type="text" name="year_end" class="half" value="<?php echo $this->filters['year_end']; ?>" />
				</label>
				<label>
					<?php echo Lang::txt('Sort by'); ?>:
					<select name="sort" id="sort" class="">
						<?php foreach($this->sorts as $k => $v) : ?>
							<?php if($k == 'sec_cnt DESC' || $k == 'created DESC') continue; ?>
							<?php $sel = ($k == $this->filters['sort']) ? "selected" : ""; ?>
							<option <?php echo $sel; ?> value="<?php echo $k; ?>"><?php echo $v; ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				
				<p class="submit">
					<input type="submit" value="Filter" />
				</p>
			</fieldset>
			
			<?php if ($batch_download) : ?>
				<fieldset id="download-batch">
					<strong><?php echo Lang::txt('Export Multiple Citations'); ?></strong>
					<p><?php echo Lang::txt('Check the citations that you would like to have exported.'); ?></p>
					
					<input type="submit" name="download" class="download-endnote" value="EndNote" /> 
					| 
					<input type="submit" name="download" class="download-bibtex" value="BibTex" />
					<input type="hidden" name="task" value="downloadbatch" id="download-batch-input" />
				</fieldset>
			<?php endif; ?>
		</div><!-- /.aside -->
		
		<div class="subject">
			<div class="container data-entry">
				<input class="entry-search-submit" type="submit" value="Search" />
				<fieldset class="entry-search">
					<legend>Search Citations</legend>
					<input type="text" name="search" id="entry-search-field" value="<?php echo $this->filters['search']; ?>" placeholder="Search Citations by Title, Author, ISBN, DOI, Publisher, and Abstract" />
				</fieldset>
			</div><!-- /.container .data-entry -->
			<div class="container">
				<ul class="entries-menu filter-options">
					<?php
						$queryString = "";
						$exclude = array("filter", "reftype", "geo", "aff", "tag");
						foreach($this->filters as $k => $v)
						{
							if($v != "" && !in_array($k, $exclude))
							{
								if(is_array($v))
								{
									foreach($v as $k2 => $v2)
									{
										$queryString .= "&{$k}[{$k2}]={$v2}";
									}
								}
								else
								{
									$queryString .= "&{$k}={$v}";
								}
							}
						}
						
						//get the old tags
						$old_tags = array_filter(array_values(explode(",", $this->filters['tag'])));
						
						//tags without peer reviewed and evidence based in there
						$new_tags = array_diff($old_tags, array("peerreviewed", "evidencebased"));
					?>
					<li>
						<a <?php if($this->filters['tag'] == '') { echo 'class="active"'; } ?> href="<?php echo Route::url('index.php?option=com_citations&task=browse'.$queryString.'&tag='.implode(",",$new_tags)); ?>">All</a>
					</li>
					<?php 
						$new_tags[] = "peerreviewed"; 
					?>
					<li>
						<a <?php if(strstr($this->filters['tag'], "peerreviewed")) { echo 'class="active"'; } ?> href="<?php echo Route::url('index.php?option=com_citations&task=browse'.$queryString.'&tag='.implode(",",$new_tags)); ?>">Peer Reviewed</a>
					</li>
					<?php 
						$new_tags = array_diff($new_tags, array("peerreviewed"));
						$new_tags[] = "evidencebased"; 
					?>
					<li>
						<a <?php if(strstr($this->filters['tag'], "evidencebased")) { echo 'class="active"'; } ?> href="<?php echo Route::url('index.php?option=com_citations&task=browse'.$queryString.'&tag='.implode(",",$new_tags)); ?>">Evidence Based</a>
					</li>
				</ul>
				<div class="clearfix"></div>
					
				<?php if(count($this->citations) > 0) : ?>
					<?php
						$formatter = new \Components\Citations\Helpers\Format();
						$formatter->setTemplate($template);

						$counter = 1;
					?>
					<table class="citations entries">
						<thead>
							<tr>
								<?php if ($batch_download) : ?>
									<th class="batch">
										<input type="checkbox" class="checkall-download" />
									</th>
								<?php endif; ?>
								<th colspan="2">Citations</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($this->citations as $cite) : ?>
								<tr>
									<?php if ($batch_download) : ?>
										<td class="batch">
											<input type="checkbox" class="download-marker" name="download_marker[]" value="<?php echo $cite->id; ?>" />
										</td>
									<?php endif; ?>

									<?php if ($label != "none") : ?>
										<td class="citation-label <?php echo $citations_label_class; ?>">
											<?php 
												$type = "";
												foreach($this->types as $t) {
													if ($t['id'] == $cite->type) {
														$type = $t['type_title'];
													}
												}
												$type = ($type != "") ? $type : "Generic";

												switch($label)
												{
													case "number":
														echo "<span class=\"number\">{$counter}.</span>";
														break;
													case "type":
														echo "<span class=\"type\">{$type}</span>";
														break;
													case "both":
														echo "<span class=\"number\">{$counter}.</span>";
														echo "<span class=\"type\">{$type}</span>";
														break;
												}
											?>
										</td>
									<?php endif; ?>
									<td class="citation-container">
										<?php echo $formatter->formatCitation($cite, $this->filters['search'], $coins, $this->config); ?>

										<?php if ($rollover == "yes" && $cite->abstract != "") : ?>
											<div class="citation-notes">
												<?php
													$cs = new \Components\Citations\Tables\Sponsor($this->database);
													$sponsors = $cs->getCitationSponsor($cite->id);
													$final = "";
													if ($sponsors)
													{
														foreach($sponsors as $s)
														{
															$sp = $cs->getSponsor($s);
															if ($sp)
															{
																$final .= '<a rel="external" href="'.$sp[0]['link'].'">'.$sp[0]['sponsor'].'</a>, ';
															}
														}
													}
												?>
												<?php if ($final != '' && $this->config->get("citation_sponsors", "yes") == 'yes') : ?>
													<?php $final = substr($final, 0, -2); ?>
													<p class="sponsor">Abstract courtesy of <?php echo $final; ?></p>
												<?php endif; ?>
												<p><?php echo nl2br($cite->abstract); ?></p>
											</div>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td colspan="<?php if ($label == "none") { echo 2; } else { echo 3; }; ?>" class="citation-details">
										<?php //echo $formatter->citationDetails($cite, $this->database, $this->config, $this->openurl); ?>

										<?php if ($this->config->get("citation_show_badges","no") == "yes") : ?>
											<?php echo \Components\Citations\Helpers\Format::citationBadges($cite, $this->database); ?>
										<?php endif; ?>

										<?php if ($this->config->get("citation_show_tags","no") == "yes") : ?>
											<?php echo \Components\Citations\Helpers\Format::citationTags($cite, $this->database); ?>
										<?php endif; ?>
									</td>
								</tr>
								<?php $counter++; ?>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="warning"><?php echo Lang::txt('COM_CITATIONS_NO_CITATIONS_FOUND'); ?></p>
				<?php endif; ?>
				<?php 
					$this->pageNav->setAdditionalUrlParam('task', 'browse');
					foreach ($this->filters as $key => $value)
					{
						switch ($key)
						{
							case 'limit':
							case 'start':
							break;

							case 'reftype':
							case 'aff':
							case 'geo':
								if ($value)
								{
									foreach ($value as $k => $v)
									{
										$this->pageNav->setAdditionalUrlParam($key . '[' . $k . ']', $v);
									}
								}
							break;

							default:
								$this->pageNav->setAdditionalUrlParam($key, $value);
							break;
						}
					}
					echo $this->pageNav->getListFooter();
				?>
				<div class="clearfix"></div>
			</div><!-- /.container -->
		</div><!-- /.subject -->
	</form>
</div>