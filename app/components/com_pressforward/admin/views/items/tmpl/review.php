<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('items');

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_CONTENT'));
if ($canDo->get('core.admin'))
{
	Toolbar::preferences($this->option);
	Toolbar::spacer();
}
if ($canDo->get('core.edit.state'))
{
	Toolbar::publishList();
	Toolbar::unpublishList();
	Toolbar::spacer();
}
if ($canDo->get('core.create'))
{
	Toolbar::addNew();
}
if ($canDo->get('core.edit'))
{
	Toolbar::editList();
}
if ($canDo->get('core.delete'))
{
	Toolbar::deleteList();
}
Toolbar::spacer();
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');

Html::behavior('framework');

$this->css('pressforward.css');
//$this->css('susy.css');
$this->js('views.js');
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="grid">
			<div class="col span6">
				<label for="filter_search"><?php echo Lang::txt('JSEARCH_FILTER'); ?>:</label>
				<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('JSEARCH_FILTER'); ?>" />

				<input type="submit" value="<?php echo Lang::txt('PF_GO'); ?>" />
				<button type="button" onclick="$('#filter_search').val('');this.form.submit();"><?php echo Lang::txt('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="col span6">
				<label for="filter-display"><?php echo Lang::txt('PF_VIEW'); ?>:</label>
				<select name="display" id="filter-display">
					<option value="gogrid">View</option>
					<option value="golist">List</option>
					<option value="paginate">Paginate</option>
				</select>

				<label for="filter-filter"><?php echo Lang::txt('PF_FILTER'); ?>:</label>
				<select name="filter" id="filter-filter" onchange="this.form.submit()">
					<option value="">Filter</option>
					<option value="">My starred</option>
					<option value="">Show hidden</option>
					<option value="">My nominations</option>
					<option value="">Unread</option>
					<option value="">Drafted</option>
				</select>

				<label for="filter-sort"><?php echo Lang::txt('PF_SORT'); ?>:</label>
				<select name="sort" id="filter-sort" onchange="this.form.submit()">
					<option value="reset">Sort</option>
					<option value="dateofitem">Date of Item</option>
					<option value="dateretrieved">Date Retrieved</option>
				</select>

				<label for="filter-category"><?php echo Lang::txt('PF_FOLDER'); ?>:</label>
				<?php //echo \Components\PressForward\Helpers\Html::categories($this->categories, $this->filters['category'], 'category', 'filter-category', 'onchange="this.form.submit()"'); ?>

				<a class="button btn-small" id="gofolders" href="#feed-folders"><?php echo __('Folders', 'pf'); ?></a>
			</div>
		</div>
	</fieldset>
	<div class="clr"></div>

	<div class="pf_container pf-all-content grid full">
		<div class="grid-inner">
			<div id="feed-folders">
				<h3><?php echo __('Folders', 'pf'); ?></h3>
				<?php
				$this->view('_folders')
					->set('folders', $this->folders)
					->set('depth', 0)
					->display();
				?>
			</div>
			<div id="entries">
				<?php
				$k = 0;
				$i = 0;
				$format = 'standard';
				$readStat = false;

				/*foreach ($this->rows as $row)
				{
					$template = PressForward\Core\Admin\PFTemplater()
					$emplate->form_of_an_item();
				}*/

				foreach ($this->rows as $row) :
					// Compile metadata
					$metadata = array();
					foreach ($row->meta as $meta) :
						$metadata[$meta->get('meta_key')] = $meta->get('meta_value');
					endforeach;
					?>
					<article class="feed-item entry" id="" pf-post-id="" pf-feed-item-id="" pf-item-post-id="">
						<?php if ($canDo->get('core.manage')): ?>
							<div class="box-controls">
								<?php if ($canDo->get('core.delete')): ?>
									<?php
									$postid = $row->get('ID'); //$id_for_comments;
									if ($format == 'nomination'):
										$postid = $metadata['nom_id'];
									endif;
									?>
									<i class="icon-remove pf-item-remove" pf-post-id="<?php echo $postid; ?>" title="Delete"></i>
								<?php endif; ?>
								<?php if ($canDo->get('core.edit.state')): ?>
									<?php if ($format != 'nomination'): ?>
										<?php
										$archiveStat = false;
										$extra_classes = '';
										if ($archiveStat)
										{
											$extra_classes .= ' schema-active relationship-button-active';
										}
										?>
										<i class="icon-eye-close hide-item pf-item-archive schema-archive schema-switchable schema-actor<?php echo $extra_classes; ?>" pf-schema-class="relationship-button-active" pf-item-post-id="8" title="Hide" pf-schema="archive"></i>
									<?php endif; ?>
									<i class="icon-ok-sign schema-read schema-actor schema-switchable <?php if ($readStat) { echo $readClass = 'marked-read'; } ?>" pf-item-post-id="8" pf-schema="read" pf-schema-class="marked-read" title="Mark as Read"></i>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<header>
							<?php if ($format == 'nomination'): ?>
								<div class="sortable-hidden-meta" style="display:none;">
									<?php
									_e('UNIX timestamp from source RSS', 'pf');
									echo ': <span class="sortable_source_timestamp sortableitemdate">' . $metadata['timestamp_item_posted'] . '</span><br />';

									_e('UNIX timestamp last modified', 'pf');
									echo ': <span class="sortable_mod_timestamp">' . $metadata['timestamp_nom_last_modified'] . '</span><br />';

									_e('UNIX timestamp date nominated', 'pf');
									echo ': <span class="sortable_nom_timestamp">' . $metadata['timestamp_unix_date_nomed'] . '</span><br />';

									_e('Slug for origin site', 'pf');
									echo ': <span class="sortable_origin_link_slug">' . $metadata['source_slug'] . '</span><br />';
									?>
								</div>
							<?php endif; ?>
							<h1 class="item_title">
								<a href="#modal-<?php echo $row->get('ID'); ?>" class="item-expander schema-actor" role="button" data-toggle="modal" data-backdrop="false" pf-schema="read" pf-schema-targets="schema-read">
									<?php echo $row->get('post_title'); ?>
								</a>
							</h1>
							<p class="source_title"><?php echo $row->parent->get('post_title'); ?></p>
							<div class="feed-item-info-box" id="info-box-<?php echo $row->get('ID'); ?>">
								<?php
								$sourceLink = $row->parent->get('guid');
								$url_array = parse_url($sourceLink);
								if (!$url_array || empty($url_array['host'])) :
									pf_log('Could not find the source link for ' . $id_for_comments . ' Got: ' . $sourceLink);
									$sourceLink = "Source URL not found.";
								else :
									$sourceLink = 'http://' . $url_array['host'];
								endif;

								echo __('Feed', 'pf') . ': <span class="feed_title">' . $this->escape($row->get('post_title')) . '</span><br />';
								echo __('Posted', 'pf') . ': <span class="feed_posted">' . date('M j, Y; g:ia' , strtotime($row->get('post_date_gmt'))) . '</span><br />';
								echo __('Retrieved', 'pf') . ': <span class="item_meta item_meta_added_date">' . date('M j, Y; g:ia' , strtotime($row->get('post_date_gmt'))) . '</span><br />';
								echo __('Authors', 'pf') . ': <span class="item_authors">' . $this->escape($row->get('post_author')) . '</span><br />';
								echo __('Origin', 'pf') . ': <span class="source_name"><a target ="_blank" href="' . $sourceLink . '">' . $sourceLink . '</a></span><br />';
								echo __('Original Item', 'pf') . ': <span class="source_link"><a href="' . $row->get('guid') . '" class="item_url" target ="_blank">' . $this->escape($row->get('post_title')) . '</a></span><br />';
								echo __('Tags', 'pf') . ': <span class="item_tags">' . '' . '</span><br />';
								echo __('Times repeated in source', 'pf') . ': <span class="feed_repeat sortable_sources_repeat">' . $row->get('source_repeat', 0) . '</span><br />';
								?>
								<?php if ($format == 'nomination'): ?>
									<?php
									echo __('Number of nominations received', 'pf') . ': <span class="sortable_nom_count">' . $metadata['nom_count'] . '</span><br />';
									echo __('First submitted by', 'pf') . ': <span class="first_submitter">' . $metadata['submitters'] . '</span><br />';
									echo __('Nominated on', 'pf') . ': <span class="nominated_on">' . date('M j, Y; g:ia' , strtotime($metadata['date_nominated'])) . '</span><br />';
									echo __('Nominated by', 'pf') . ': <span class="nominated_by">' . get_the_nominating_users() . '</span><br />';
									?>
								<?php endif; ?>
							</div>
							<script type="text/javascript">
								var pop_title_<?php echo $row->get('ID'); ?> = '';
								var pop_html_<?php echo $row->get('ID'); ?> = jQuery('#<?php echo 'info-box-' . $row->get('ID'); ?>');
							</script>
							<div class="actions pf-btns article-btns">
								<!-- <form name="form-4c7cb99ec634c6054943d6bb70caae0f">
									<div class="nominate-result-4c7cb99ec634c6054943d6bb70caae0f">
										<img class="loading-4c7cb99ec634c6054943d6bb70caae0f" src="http://localhost/sandbox/wordpress/wp-content/plugins/pressforward-2/assets/images/ajax-loader.gif" alt="Loading..." style="display: none">
									</div>
									<input type="hidden" name="item_title" id="item_title_4c7cb99ec634c6054943d6bb70caae0f" value="Briggs Lake deemed public health risk">
									<input type="hidden" name="source_title" id="source_title_4c7cb99ec634c6054943d6bb70caae0f" value="Google Alert - pets health">
									<input type="hidden" name="item_date" id="item_date_4c7cb99ec634c6054943d6bb70caae0f" value="Thu, 27 Oct 2016 18:02:54 +0000">
									<input type="hidden" name="item_author" id="item_author_4c7cb99ec634c6054943d6bb70caae0f" value="Google Alert - pets health">
									<input type="hidden" name="item_content" id="item_content_4c7cb99ec634c6054943d6bb70caae0f" value="The recreational public health watch area includes the entire lake. ... pregnant or nursing women, those with certain medical conditions, and pets.">
									<input type="hidden" name="item_link" id="item_link_4c7cb99ec634c6054943d6bb70caae0f" value="https://www.google.com/url?rct=j&amp;sa=t&amp;url=http://newsdemocratleader.com/news/7035/briggs-lake-deemed-public-health-risk&amp;ct=ga&amp;cd=CAIyGjJlZjZjMDA3MWM1OTliOTM6Y29tOmVuOlVT&amp;usg=AFQjCNE0lVubni9K8wJwuDxdqj_sm55txg">
									<input type="hidden" name="item_feat_img" id="item_feat_img_4c7cb99ec634c6054943d6bb70caae0f" value="">
									<input type="hidden" name="item_id" id="item_id_4c7cb99ec634c6054943d6bb70caae0f" value="4c7cb99ec634c6054943d6bb70caae0f">
									<input type="hidden" name="item_wp_date" id="item_wp_date_4c7cb99ec634c6054943d6bb70caae0f" value="2016-10-27">
									<input type="hidden" name="item_tags" id="item_tags_4c7cb99ec634c6054943d6bb70caae0f" value="">
									<input type="hidden" name="item_added_date" id="item_added_date_4c7cb99ec634c6054943d6bb70caae0f" value="2016-10-27T00:00:00+0000">
									<input type="hidden" name="source_repeat" id="source_repeat_4c7cb99ec634c6054943d6bb70caae0f" value="0">
									<input type="hidden" name="post_id" id="post_id_4c7cb99ec634c6054943d6bb70caae0f" value="51">
									<input type="hidden" name="readable_status" id="readable_status_4c7cb99ec634c6054943d6bb70caae0f" value="">
									<input type="hidden" name="obj" id="obj_4c7cb99ec634c6054943d6bb70caae0f" value="">
									<?php echo Html::input('token'); ?>
								</form> -->
								<button class="btn btn-small itemInfobutton" data-toggle="tooltip" id="info-4c7cb99ec634c6054943d6bb70caae0f-top" data-placement="top" data-class="info-box-popover" data-title="" data-target="4c7cb99ec634c6054943d6bb70caae0f" data-original-title="Info"><i class="icon-info-sign"></i></button>
								<button class="btn btn-small star-item" data-toggle="tooltip" data-original-title="Star"><i class="icon-star"></i></button>
								<a role="button" class="btn btn-small itemCommentModal comments-expander" data-toggle="modal" href="#comment_modal_51" id="comments-expander-51" data-original-title="Comment"><span class="comments-expander-count">0</span><i class="icon-comment"></i></a>
								<button class="btn btn-small nominate-now schema-actor schema-switchable" pf-schema="nominate" pf-schema-class="btn-success" form="4c7cb99ec634c6054943d6bb70caae0f" data-original-title="Nominate"><i class="icon-nominate"></i></button>
								<!-- <div class="dropdown btn-group amplify-group" role="group">
									<button type="button" class="btn btn-default btn-small dropdown-toggle pf-amplify" data-toggle="dropdown" aria-expanded="true" id="amplify-4c7cb99ec634c6054943d6bb70caae0f" data-original-title=""><i class="icon-bullhorn"></i><span class="caret"></span></button>
									<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="amplify-4c7cb99ec634c6054943d6bb70caae0f">
										<li role="presentation"><a role="menuitem" id="amplify-draft-4c7cb99ec634c6054943d6bb70caae0f" tabindex="-1" class="amplify-option amplify-draft schema-actor" href="#" data-form="4c7cb99ec634c6054943d6bb70caae0f" pf-schema="draft" pf-schema-class="btn-success">Send to Draft</a></li>
										<li class="divider"></li>
										<li role="presentation"><a role="menuitem" id="amplify-tweet-4c7cb99ec634c6054943d6bb70caae0f" tabindex="-1" class="amplify-option" href="https://twitter.com/intent/tweet?text=Briggs+Lake+deemed+public+health+risk&amp;url=https%3A%2F%2Fwww.google.com%2Furl%3Frct%3Dj%26sa%3Dt%26url%3Dhttp%3A%2F%2Fnewsdemocratleader.com%2Fnews%2F7035%2Fbriggs-lake-deemed-public-health-risk%26ct%3Dga%26cd%3DCAIyGjJlZjZjMDA3MWM1OTliOTM6Y29tOmVuOlVT%26usg%3DAFQjCNE0lVubni9K8wJwuDxdqj_sm55txg&amp;via=pressfwd" target="_blank" data-form="4c7cb99ec634c6054943d6bb70caae0f">Tweet</a></li>
									</ul>
								</div> -->
							</div>
						</header>
						<div class="content">
							<div class="item_meta item_meta_date">
							</div>
							<?php if ($content = $row->get('post_content')): ?>
								<div class="item_exceprt" id="excerpt<?php echo $row->get('ID'); ?>">
									<?php if ($format == 'nomination'): ?>
										<p><?php echo pf_noms_excerpt($content); ?></p>
									<?php else: ?>
										<p><?php echo pf_feed_excerpt($content); ?></p>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div><!-- End content -->
						<footer>
							<p class="pubdate"><?php echo date('F j, Y; g:i a' , strtotime($row->get('post_date_gmt'))); ?></p>
						</footer>
						<!-- Begin Modal -->
						<!-- End Modal -->
					</article>
					<?php
					$i++;
					$k = 1 - $k;
				endforeach;
				?>
			</div>
		</div>

		<?php echo $this->rows->pagination; ?>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->filters['sort']); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->filters['sort_Dir']); ?>" />

	<?php echo Html::input('token'); ?>
</form>
