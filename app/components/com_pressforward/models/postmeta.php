<?php
namespace Components\PressForward\Models;

use Hubzero\Database\Relational;

/**
 * Model class for a post's meta data
 */
class Postmeta extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'pf';

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = '#__pf_postmeta';

	/**
	 * The table primary key name
	 *
	 * It defaults to 'id', but can be overwritten by a subclass.
	 *
	 * @var  string
	 **/
	protected $pk = 'meta_id';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'meta_key';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'meta_key' => 'notempty',
		'post_id'  => 'positive|nonzero'
	);

	/**
	 * All the approved post_meta objects
	 *
	 * @var  array
	 */
	public static $metas = array(
		'item_id' => array(
			'name'       => 'item_id',
			'definition' => 'Unique PressForward ID',
			'function'   => 'Stores hashed ID based on title and URL of retrieved item',
			'type'       => array('struc'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'pf_meta' => array(
			'name'       => 'pf_meta',
			'definition' => 'Serialized PF data',
			'function'   => 'Array of PF data that can be serialized',
			'type'       => array('struc'),
			'use'        => array('req'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		/*'origin_item_ID' => array(
			'name'       => 'origin_item_ID',
			'definition' => 'DUPE Soon to be depreciated version of item_id',
			'function'   => 'Stores hashed ID based on title and URL of retrieved item',
			'type'       => array('struc', 'dep'),
			'use'        => array('req'),
			'move'       => 'item_id',
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),*/
		'pf_item_post_id' => array(
			'name'       => 'pf_item_post_id',
			'definition' => 'The WordPress postID associated with the original item',
			'function'   => 'Stores hashed WP post_ID associated with the original item',
			'type'       => array('struc'),
			'use'        => array('req'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'nom_id' => array(
			'name'       => 'nom_id',
			'definition' => 'The WordPress postID associated with the nomination item',
			'function'   => 'Stores nomination id',
			'type'       => array('struc'),
			'use'        => array('req'),
			'level'      => array('nomination', 'post'),
			'serialize'  => false
		),
		'pf_nomination_post_id' => array(
			'name'       => 'pf_nomination_post_id',
			'definition' => 'The WordPress postID associated with the nomination',
			'function'   => 'Stores postID associated with the nominated item',
			'type'       => array('struc'),
			'use'        => array(),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		/*'item_feed_post_id' => array(
			'name'       => 'item_feed_post_id',
			'definition' => 'DUPE Soon to be depreciated version of pf_item_post_id',
			'function'   => 'Stores hashed ID based on title and URL of retrieved item',
			'type'       => array('struc', 'dep'),
			'use'        => array('req'),
			'move'       => 'pf_item_post_id',
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),*/
		'source_title' => array(
			'name'       => 'source_title',
			'definition' => 'Title of the item\'s source',
			'function'   => 'Stores the title retrieved from the feed.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'pf_source_link' => array(
			'name'       => 'pf_source_link',
			'definition' => 'URL of the item\'s source',
			'function'   => 'Stores the url of feed source.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		/*'pf_feed_item_source' => array(
			'name'       => 'pf_feed_item_source',
			'definition' => 'DUPE Soon to be depreciate version of source_title.',
			'function'   => 'Stores the title retrieved from the feed.',
			'type'       => array('desc','dep'),
			'use'        => array('req'),
			'move'       => 'source_title',
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),*/
		'item_date' => array(
			'name'       => 'item_date',
			'definition' => 'Date posted on the original site',
			'function'   => 'Stores the date the item was posted on the original site',
			'type'       => array('desc'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		/*'posted_date' => array(
			'name'       => 'posted_date',
			'definition' => 'DUPE The soon to be depreciated version of item_date',
			'function'   => 'Stores the date given by the source.',
			'type'       => array('struc', 'dep'),
			'use'        => array('req'),
			'move'       => 'item_date',
			'level'      => array('nomination', 'post'),
			'serialize'  => true
		),*/
		'item_author' => array(
			'name'       => 'item_author',
			'definition' => 'Author(s) listed on the original site',
			'function'   => 'Stores array value containing authors listed in the source feed.',
			'type'       => array('struc'),
			'use'        => array('api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		/*'authors' => array(
			'name'       => 'authors',
			'definition' => 'DUPE The soon to be depreciated version of item_author',
			'function'   => 'Stores a comma-separated set of authors as listed in the source feed',
			'type'       => array('struc','dep'),
			'use'        => array(),
			'move'       => 'item_author',
			'level'      => array('nomination', 'post'),
			'serialize'  => true
		),*/
		'item_link' => array(
			'name'       => 'item_link',
			'definition' => 'Source link',
			'function'   => 'Stores link to the origonal post.',
			'type'       => array('struc'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		/*'nomination_permalink' => array(
			'name'       => 'item_link',
			'definition' => 'Source link',
			'function'   => 'DUPE Soon to be depreciated version of item_link',
			'type'       => array('struc','dep'),
			'use'        => array('req'),
			'move'       => 'item_link',
			'level'      => array('nomination', 'post'),
			'serialize'  => true
		),*/
		'item_feat_img' => array(
			'name'       => 'item_feat_img',
			'definition' => 'Featured image from source',
			'function'   => 'A featured image associated with the item, when it is available',
			'type'       => array('struc'),
			'use'        => array(),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'item_wp_date' => array(
			'name'       => 'item_wp_date',
			'definition' => 'Time item was retrieved',
			'function'   => 'The datetime an item was added to WordPress via PressForward',
			'type'       => array('desc'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'date_nominated' => array(
			'name'       => 'date_nominated',
			'definition' => 'Time nominated',
			'function'   => 'The datetime the item was made a nomination',
			'type'       => array('desc'),
			'use'        => array('req', 'api'),
			'level'      => array('nomination', 'post'),
			'serialize'  => true
		),
		'item_tags' => array(
			'name'       => 'item_tags',
			'definition' => 'Tags associated with the item by source',
			'function'   => 'An array of tags associated with the item, as created in the feed',
			'type'       => array('desc'),
			'use'        => array(),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'source_repeat' => array(
			'name'       => 'source_repeat',
			'definition' => 'Times retrieved',
			'function'   => 'Counts number of times the item has been collected from the multiple feeds (Ex: from origin feed and Twitter)',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'nomination_count' => array(
			'name'       => 'nomination_count',
			'definition' => 'Nominations',
			'function'   => 'Counts number of times users have nominated an item',
			'type'       => array('adm'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'submitted_by' => array(
			'name'       => 'submitted_by',
			'definition' => 'The user who submitted the nomination',
			'function'   => 'The first user who submitted the nomination (if it has been nominated). User ID number',
			'type'       => array('adm'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'nominator_array' => array(
			'name'       => 'nominator_array',
			'definition' => 'Users who nominated this item',
			'function'   => 'Stores and array of all userIDs that nominated the item in an array',
			'type'       => array('adm'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'sortable_item_date' => array(
			'name'       => 'sortable_item_date',
			'definition' => 'Timestamp for the item',
			'function'   => 'A version of the item_date meta that\'s ready for sorting. Should be a Unix timestamp',
			'type'       => array('adm'),
			'use'        => array('req'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'readable_status' => array(
			'name'       => 'readable_status',
			'definition' => 'If the content is readable',
			'function'   => 'A check to determine if the content of the item has been made readable',
			'type'       => array('desc'),
			'use'        => array('req', 'api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'revertible_feed_text' => array(
			'name'       => 'revertible_feed_text',
			'definition' => 'The originally retrieved description',
			'function'   => 'The original description, excerpt or content text given by the feed',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'pf_feed_item_word_count' => array(
			'name'       => 'pf_feed_item_word_count',
			'definition' => 'Word count of original item text',
			'function'   => 'Stores the count of the original words retrieved with the feed item',
			'type'       => array('desc'),
			'use'        => array('api'),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => true
		),
		'pf_archive' => array(
			'name'       => 'pf_archive',
			'definition' => 'Archive state of the item',
			'function'   => 'Stores if the item has been archived',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'_thumbnail_id' => array(
			'name'       => '_thumbnail_id',
			'definition' => 'Thumbnail id',
			'function'   => 'The ID of the featured item',
			'type'       => array('adm','struc'),
			'use'        => array(),
			'level'      => array('item', 'nomination', 'post'),
			'serialize'  => false
		),
		'archived_by_user_status' => array(
			'name'       => 'archived_by_user_status',
			'definition' => 'Users who have archived',
			'function'   => 'Stores users who have archived.',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('item', 'nomination'),
			'serialize'  => true
		),
		'pf_feed_error_count' => array(
			'name'       => 'pf_feed_error_count',
			'definition' => 'Count of feed errors',
			'function'   => 'Stores a count of the number of errors a feed has experianced',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('feed', 'post'),
			'serialize'  => true
		),
		'pf_forward_to_origin' => array(
			'name'       => 'pf_forward_to_origin',
			'definition' => 'User override for forwarding to origin of link',
			'function'   => 'Stores forwarding override for posts',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('post'),
			'serialize'  => false
		),
		'pf_feed_last_retrieved' => array(
			'name'       => 'pf_feed_last_retrieved',
			'definition' => 'Last time feed was retrieved',
			'function'   => 'Stores last timestamp feed was retrieved.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'pf_feed_last_retrieved' => array(
			'name'       => 'pf_feed_last_retrieved',
			'definition' => 'Last time feed was retrieved',
			'function'   => 'Stores last timestamp feed was retrieved.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'feedUrl' => array(
			'name'       => 'feedUrl',
			'definition' => 'URL for a feed',
			'function'   => 'Stores location online for feed.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'pf_feed_last_checked' => array(
			'name'       => 'pf_feed_last_checked',
			'definition' => 'Last time feed was checked',
			'function'   => 'Stores last timestamp feed was checked.',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'pf_no_feed_alert' => array(
			'name'       => 'pf_no_feed_alert',
			'definition' => 'Feed Alert Status',
			'function'   => 'A check to see if an alert is on the feed.',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'feed_type' => array(
			'name'       => 'feed_type',
			'definition' => 'Type of feed',
			'function'   => 'Field stores the type of feed (like RSS or OPML) the object holds.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'htmlUrl' => array(
			'name'       => 'htmlUrl',
			'definition' => 'Site URL of a feed.',
			'function'   => 'The home URL of a feed.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'user_added' => array(
			'name'       => 'user_added',
			'definition' => 'User who added a feed.',
			'function'   => 'Track who added a subscribed or under review feed.',
			'type'       => array('adm','struc'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'module_added' => array(
			'name'       => 'module_added',
			'definition' => 'Module to process a feed.',
			'function'   => 'The feed should be processed with this module.',
			'type'       => array('adm','struc'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'ab_alert_msg' => array(
			'name'       => 'ab_alert_msg',
			'definition' => 'Alert Message processing and storage.',
			'function'   => 'Stores a feed alert to be processed.',
			'type'       => array('adm'),
			'use'        => array('api'),
			'level'      => array('feed'),
			'serialize'  => false
		),
		'pf_meta_data_check' => array(
			'name'       => 'pf_meta_data_check',
			'definition' => 'Has metadata been completely added to a feed?',
			'function'   => 'Store a value to indicate the meta-processing of a feed has completed.',
			'type'       => array('adm'),
			'use'        => array(),
			'level'      => array('feed'),
			'serialize'  => false
		),
	);

	/**
	 * Get parent post
	 *
	 * @return  object
	 */
	public function post()
	{
		return $this->belongsToOne('Post', 'post_id');
	}
}
