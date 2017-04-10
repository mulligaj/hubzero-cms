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

// No direct access
defined('_HZEXEC_') or die();

$item_id = 0;
$user = wp_get_current_user();
$user_id = $user->ID;
if ($format == 'nomination')
{
	$item_id = $metadata['item_id'];
}
else
{
	$item_id = $item['item_id'];
}
?>

<div class="actions pf-btns <?php if($modal){ echo 'modal-btns '; } else { echo ' article-btns '; } ?>">
	<?php
	$infoPop = 'top';
	$infoModalClass = ' modal-popover';
	if ($modal == false)
	{
		#$infoPop = 'bottom';
		$infoModalClass = '';
		if ($format === 'nomination')
		{
			?>
			<form name="form-<?php echo $metadata['item_id']; ?>" pf-form="<?php echo $metadata['item_id']; ?>">
				<?php
				pf_prep_item_for_submit($metadata);
				wp_nonce_field('nomination', PF_SLUG . '_nomination_nonce', false);
		}
		else
		{
			echo '<form name="form-' . $item['item_id'] . '">'
			 . '<div class="nominate-result-' . $item['item_id'] . '">'
			 . '<img class="loading-' . $item['item_id'] . '" src="' . PF_URL . 'assets/images/ajax-loader.gif" alt="' . __('Loading', 'pf') . '..." style="display: none" />'
			 . '</div>';
			pf_prep_item_for_submit($item);
			wp_nonce_field('nomination', PF_SLUG . '_nomination_nonce', false);
		}
		echo '</form>';
	}

	echo '<button class="btn btn-small itemInfobutton" data-toggle="tooltip" title="' . __('Info', 'pf') .  '" id="info-' . $item['item_id'] . '-' . $infoPop . '" data-placement="' . $infoPop . '" data-class="info-box-popover'.$infoModalClass.'" data-title="" data-target="'.$item['item_id'].'"><i class="icon-info-sign"></i></button>';

	if (pf_is_item_starred_for_user($id_for_comments, $user_id))
	{
		echo '<!-- item_id selected = ' . $item_id . ' -->';
		echo '<button class="btn btn-small star-item btn-warning" data-toggle="tooltip" title="' . __('Star', 'pf') .  '"><i class="icon-star"></i></button>';
	}
	else
	{
		echo '<button class="btn btn-small star-item" data-toggle="tooltip" title="' . __('Star', 'pf') .  '"><i class="icon-star"></i></button>';
	}

	if (has_action('pf_comment_action_button'))
	{
		$commentModalCall = '#modal-comments-' . $item['item_id'];
		$commentSet = array(
			'id' => $id_for_comments,
			'modal_state' => $modal
		);

		do_action('pf_comment_action_button', $commentSet);
	}

	if ($format === 'nomination')
	{
		$nom_count_classes = 'btn btn-small nom-count';
		$metadata['nom_count'] = get_the_nomination_count();
		if ($metadata['nom_count'] > 0)
		{
			$nom_count_classes .= ' btn-info';
		}

		echo '<a class="'.$nom_count_classes.'" data-toggle="tooltip" title="' . __('Nomination Count', 'pf') .  '" form="' . $metadata['nom_id'] . '">'.$metadata['nom_count'].'<i class="icon-play"></i></button></a>';
		$archive_status = '';
		if ( 1 == pressforward('controller.metas')->get_post_pf_meta( $metadata['nom_id'], 'pf_archive', true ) ){
			$archive_status = 'btn-warning';
		}
		echo '<a class="btn btn-small nom-to-archive schema-switchable schema-actor '.$archive_status.'" pf-schema="archive" pf-schema-class="archived" pf-schema-class="btn-warning" data-toggle="tooltip" title="' . __('Archive', 'pf') .  '" form="' . $metadata['nom_id'] . '"><img src="' . PF_URL . 'assets/images/archive.png" /></button></a>';
		$draft_status = "";
		if ( ( 1 == pf_get_relationship_value( 'draft', $metadata['nom_id'], $user_id ) ) || ( 1 == pf_get_relationship_value( 'draft', $id_for_comments, $user_id ) ) ){
			$draft_status = 'btn-success';
		}
		echo '<a href="#nominate" class="btn btn-small nom-to-draft schema-actor '. $draft_status .'" pf-schema="draft" pf-schema-class="btn-success" form="' . $metadata['item_id'] . '" data-original-title="' . __('Draft', 'pf') .  '"><img src="' . PF_URL . 'assets/images/pressforward-licon.png" /></a>';
	}
	else
	{
		#var_dump(pf_get_relationship('nominate', $id_for_comments, $user_id));
		if ( ( 1 == pf_get_relationship_value('nominate', $id_for_comments, $user_id) ) || ( 1 == pf_get_relationship_value( 'draft', $id_for_comments, $user_id ) ) ){
			echo '<button class="btn btn-small nominate-now btn-success schema-actor schema-switchable" pf-schema="nominate" pf-schema-class="btn-success" form="' . $item['item_id'] . '" data-original-title="' . __('Nominated', 'pf') .  '"><img src="' . PF_URL . 'assets/images/pressforward-single-licon.png" /></button>';
			# Add option here for admin-level users to send items direct to draft.
		}
		else
		{
			echo '<button class="btn btn-small nominate-now schema-actor schema-switchable" pf-schema="nominate" pf-schema-class="btn-success" form="' . $item['item_id'] . '" data-original-title="' . __('Nominate', 'pf') .  '"><img src="' . PF_URL . 'assets/images/pressforward-single-licon.png" /></button>';
			# Add option here for admin-level users to send items direct to draft.
		}
	}

	$amplify_group_classes = 'dropdown btn-group amplify-group';
	$amplify_id = 'amplify-'.$item['item_id'];
	if ($modal)
	{
		$amplify_group_classes .= ' dropup';
		$amplify_id .= '-modal';
	}
	?>
	<div class="<?php echo $amplify_group_classes; ?>" role="group">
		<button type="button" class="btn btn-default btn-small dropdown-toggle pf-amplify" data-toggle="dropdown" aria-expanded="true" id="<?php echo $amplify_id; ?>"><i class="icon-bullhorn"></i><span class="caret"></button>
		<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="amplify-<?php echo $item['item_id']; ?>">
			<?php
			if (current_user_can('edit_others_posts') && 'nomination' != $format)
			{
				$send_to_draft_classes = 'amplify-option amplify-draft schema-actor';

				if (1 == pf_get_relationship_value('draft', $id_for_comments, $user_id))
				{
					$send_to_draft_classes .= ' btn-success';
				}

				self::dropdown_option(__('Send to ', 'pf') . ucwords(get_option(PF_SLUG.'_draft_post_status', 'draft')), "amplify-draft-".$item['item_id'], $send_to_draft_classes, $item['item_id'], 'draft', 'btn-success');

			?>
					<li class="divider"></li>
			<?php
				}
				$tweet_intent = self::tweet_intent($id_for_comments);
				self::dropdown_option(__('Tweet', 'pf'), "amplify-tweet-".$item['item_id'], 'amplify-option', $item['item_id'], '', '', $tweet_intent, '_blank' );
				#self::dropdown_option(__('Facebook', 'pf'), "amplify-facebook-".$item['item_id'], 'amplify-option', $item['item_id'] );
				#self::dropdown_option(__('Instapaper', 'pf'), "amplify-instapaper-".$item['item_id'], 'amplify-option', $item['item_id'] );
				#self::dropdown_option(__('Tumblr', 'pf'), "amplify-tumblr-".$item['item_id'], 'amplify-option', $item['item_id'] );
				do_action('pf_amplify_buttons');
			?>
		</ul>
	</div>

	<?php if ($modal === true) { ?>
		<button class="btn btn-small" data-dismiss="modal" aria-hidden="true">Close</button>
	<?php } ?>
</div>