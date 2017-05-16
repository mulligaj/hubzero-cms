<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('items');

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_NOMINATED'));
if ($canDo->get('core.admin'))
{
	Toolbar::preferences($this->option);
	Toolbar::spacer();
}
Toolbar::custom('archive', 'archive', 'archive', 'Archive all');
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
				<label for="filter-filter"><?php echo Lang::txt('PF_FILTER'); ?>:</label>
				<select name="filter" id="filter-filter" onchange="this.form.submit()">
					<option value="">Filter</option>
					<option value="starredonly">My starred</option>
					<option value="archived">Toggle visibility of archived</option>
					<option value="archiveonly">Only archived</option>
					<option value="unreadonly">Unread</option>
					<option value="drafted">Drafted</option>
				</select>

				<label for="filter-sort"><?php echo Lang::txt('PF_SORT'); ?>:</label>
				<select name="sort" id="filter-sort" onchange="this.form.submit()">
					<option value="reset">Sort</option>
					<option value="itemdate">Date of item</option>
					<option value="feedindate">Date retrieved</option>
					<option value="nomdate">Date nominated</option>
					<option value="nomcount">Nominations receieved</option>
				</select>

				<label for="gofolders"><?php echo Lang::txt('PF_FOLDER'); ?>:</label>
				<a class="button btn-small" id="gofolders" href="#feed-folders"><?php echo __('Folders', 'pf'); ?></a>
			</div>
		</div>
	</fieldset>
	<div class="clr"></div>

	<div class="pf_container pf-all-content list full">
		<div class="grid-inner">
			<div id="feed-folders">
				<h3><?php echo __('Folders', 'pf'); ?></h3>
				<?php
				$this->view('_folders')
					->set('folders', $this->folders)
					->set('active', $this->filters['folder'])
					->set('depth', 0)
					->display();
				?>
			</div>
			<div id="entries">
				<?php
				$k = 0;
				$i = 0;
				$format = 'nomination';
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

					if (!isset($metadata['pf_item_post_id']))
					{
						$metadata['pf_item_post_id'] = Components\PressForward\Models\Post::all()
							->whereEquals('post_name', $row->get('post_name'))
							->whereEquals('post_type', Components\PressForward\Models\Post::$post_type)
							->where('ID', '!=', $row->get('ID'))
							->row()
							->get('ID');

						if ($metadata['pf_item_post_id'])
						{
							$meta = Components\PressForward\Models\Postmeta::blank();
							$meta->set('post_id', $row->get('ID'));
							$meta->set('meta_key', 'pf_item_post_id');
							$meta->set('meta_value', $metadata['pf_item_post_id']);
							$meta->save();
						}
					}

					$item_id = App::hash($row->get('ID'));

					$relationships = $row->relationships()
						->whereEquals('user_id', User::get('id'))
						->rows();

					$isRead = false;
					$isStarred = false;
					$isNominated = true;
					$isArchived = false;
					$isDrafted = false;

					foreach ($relationships as $rel)
					{
						if ($rel->relationship_type == Components\PressForward\Models\Relationship::stringToInteger('read'))
						{
							$isRead = true;
						}
						if ($rel->relationship_type == Components\PressForward\Models\Relationship::stringToInteger('star'))
						{
							$isStarred = true;
						}
						if ($rel->relationship_type == Components\PressForward\Models\Relationship::stringToInteger('nominate'))
						{
							$isNominated = true;
						}
						if ($rel->relationship_type == Components\PressForward\Models\Relationship::stringToInteger('archive'))
						{
							$isArchived = true;
						}
					}

					$metadata['permalink'] = (isset($metadata['item_link']) ? $metadata['item_link'] : $row->get('guid'));

					$urlArray = parse_url($metadata['permalink']);
					//Source Site
					$metadata['source_link'] = isset($urlArray['host']) ? 'http://' . $urlArray['host'] : '';
					//Source site slug
					$metadata['source_slug'] = $sourceSlug = isset($urlArray['host']) ? pf_slugger($urlArray['host'], true, false, true) : '';

					//UNIX datetime last modified.
					$metadata['timestamp_nom_last_modified'] = $row->get('post_modified_gmt');
					//UNIX datetime added to nominations.
					$metadata['timestamp_unix_date_nomed'] = (isset($metadata['date_nominated']) ? strtotime($metadata['date_nominated']) : '');
					//UNIX datetime item was posted to its home RSS.
					$metadata['timestamp_item_posted'] = strtotime($metadata['item_date']);
					$metadata['submitters'] = array();
					$metadata['nom_id']    = $row->get('ID');
					$nominations = Components\PressForward\Models\Relationship::all()
						->whereEquals('item_id', $metadata['pf_item_post_id'])
						->whereEquals('relationship_type', Components\PressForward\Models\Relationship::stringToInteger('nominate'))
						->rows();
					$metadata['nom_count'] = count($nominations);
					foreach ($nominations as $nom)
					{
						$metadata['submitters'][] = User::getInstance($nom->get('user_id'))->get('name');
					}
					$metadata['submitters'] = implode(', ', $metadata['submitters']);
					$isDrafted = $row->relationships()
						->whereEquals('relationship_type', Components\PressForward\Models\Relationship::stringToInteger('draft'))
						->total();
					?>
					<article class="feed-item entry" id="<?php echo $item_id; ?>" pf-post-id="<?php echo $row->get('ID'); ?>" pf-feed-item-id="" pf-item-post-id="<?php echo $row->get('ID'); ?>" data-url="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>">
						<?php if ($canDo->get('core.manage')): ?>
							<div class="box-controls">
								<?php if ($canDo->get('core.delete')): ?>
									<?php
									$postid = $row->get('ID'); //$id_for_comments;
									if ($format == 'nomination'):
										$postid = $metadata['nom_id'];
									endif;
									?>
									<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=delete&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>">
										<i class="icon-remove pf-item-remove" pf-post-id="<?php echo $postid; ?>" title="Delete"></i>
									</a>
								<?php endif; ?>
								<?php if ($canDo->get('core.edit.state')): ?>
									<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=' . ($isRead ? 'un' : '') . 'read&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>">
										<i class="icon-ok-sign schema-read schema-actor schema-switchable <?php if ($isRead) { echo 'marked-read'; } ?>" pf-item-post-id="8" pf-schema="read" pf-schema-class="marked-read" title="Mark as Read"></i>
									</a>
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
									echo __('Nominated on', 'pf') . ': <span class="nominated_on">' . (isset($metadata['date_nominated']) ? date('M j, Y; g:ia' , strtotime($metadata['date_nominated'])) : 'n/a') . '</span><br />';
									echo __('Nominated by', 'pf') . ': <span class="nominated_by">' . $metadata['submitters'] . '</span><br />';
									?>
								<?php endif; ?>
							</div>
							<script type="text/javascript">
								var pop_title_<?php echo $row->get('ID'); ?> = '';
								var pop_html_<?php echo $row->get('ID'); ?> = jQuery('#<?php echo 'info-box-' . $row->get('ID'); ?>');
							</script>
							<div class="actions pf-btns article-btns">
								<div data-name="form-<?php echo $item_id; ?>">
									<div class="nominate-result-<?php echo $item_id; ?>">
										<img class="loading-<?php echo $item_id; ?>" src="<?php echo $this->img('ajax-loader.gif'); ?>" alt="Loading..." style="display: none" />
									</div>
									<input type="hidden" name="item_title" id="item_title_<?php echo $item_id; ?>" value="<?php echo $row->get('post_title'); ?>">
									<input type="hidden" name="source_title" id="source_title_<?php echo $item_id; ?>" value="Google Alert - pets health">
									<input type="hidden" name="item_date" id="item_date_<?php echo $item_id; ?>" value="<?php echo $row->get('post_date_gmt'); ?>">
									<input type="hidden" name="item_author" id="item_author_<?php echo $item_id; ?>" value="<?php echo $row->get('post_author'); ?>">
									<input type="hidden" name="item_content" id="item_content_<?php echo $item_id; ?>" value="<?php echo $row->get('post_content'); ?>">
									<input type="hidden" name="item_link" id="item_link_<?php echo $item_id; ?>" value="<?php echo $row->get('guid'); ?>">
									<input type="hidden" name="item_feat_img" id="item_feat_img_<?php echo $item_id; ?>" value="">
									<input type="hidden" name="item_id" id="item_id_<?php echo $item_id; ?>" value="<?php echo $item_id; ?>">
									<input type="hidden" name="item_wp_date" id="item_wp_date_<?php echo $item_id; ?>" value="2016-10-27">
									<input type="hidden" name="item_tags" id="item_tags_<?php echo $item_id; ?>" value="">
									<input type="hidden" name="item_added_date" id="item_added_date_<?php echo $item_id; ?>" value="2016-10-27T00:00:00+0000">
									<input type="hidden" name="source_repeat" id="source_repeat_<?php echo $item_id; ?>" value="0">
									<input type="hidden" name="post_id" id="post_id_<?php echo $item_id; ?>" value="<?php echo $row->get('ID'); ?>">
									<input type="hidden" name="readable_status" id="readable_status_<?php echo $item_id; ?>" value="">
									<input type="hidden" name="obj" id="obj_<?php echo $item_id; ?>" value="">
								</div>
								<button class="btn btn-small itemInfobutton" data-toggle="tooltip" id="info-<?php echo $item_id; ?>-top" data-placement="top" data-class="info-box-popover" data-title="" data-target="info-box-<?php echo $row->get('ID'); ?>" data-original-title="Info"><i class="icon-info-sign"></i></button>
								<a role="button" class="btn btn-small <?php if ($isStarred) { echo 'btn-warning'; } ?> star-item" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=' . ($isStarred ? 'un' : '') . 'star&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>" data-toggle="tooltip" data-original-title="Star"><i class="icon-star"></i></a>
								<?php if ($this->config->get('pf_comments_enable')) { ?>
									<a role="button" class="btn btn-small <?php if ($row->comments()->total() > 0) { echo 'btn-info'; } ?> itemCommentModal comments-expander" data-toggle="modal" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=comments&post_id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>" id="comments-expander-51" data-original-title="Comment"><span class="comments-expander-count"><?php echo $row->comments()->total(); ?></span><i class="icon-comment"></i></a>
								<?php } ?>
								<a role="button" class="btn btn-small <?php if ($isNominated) { echo 'btn-info'; } ?> nom-count schema-actor schema-switchable" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=' . ($isNominated ? 'un' : '') . 'nominate&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>" data-original-title="Nomination Count"><?php echo $metadata['nom_count']; ?><i class="icon-nominate"></i></a>
								<a role="button" class="btn btn-small nom-to-archive schema-actor schema-switchable" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=archive&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>" data-original-title="Archive"><i class="icon-archive"></i></a>
								<a role="button" class="btn btn-small <?php if ($isDrafted) { echo 'btn-success'; } ?> nom-to-draft schema-actor schema-switchable" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=draft&id=' . $row->get('ID') . '&' . Session::getFormToken() . '=1'); ?>" data-original-title="Draft"><i class="icon-draft"></i></a>
								<!-- <button class="btn btn-small nominate-now schema-actor schema-switchable" pf-schema="nominate" pf-schema-class="btn-success" form="4c7cb99ec634c6054943d6bb70caae0f" data-original-title="Nominate"><i class="icon-nominate"></i></button>
								<div class="dropdown btn-group amplify-group" role="group">
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
