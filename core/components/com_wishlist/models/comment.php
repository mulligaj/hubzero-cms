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

namespace Components\Wishlist\Models;

use Components\Wishlist\Tables;
use Hubzero\User\Profile;
use Hubzero\Base\ItemList;
use Hubzero\Utility\String;
use Date;
use Lang;

require_once(__DIR__ . DS . 'base.php');

/**
 * Wishlist class for a wish comment model
 */
class Comment extends Base
{
	/**
	 * Table
	 *
	 * @var string
	 */
	protected $_tbl_name = '\\Hubzero\\Item\\Comment';

	/**
	 * Model context
	 *
	 * @var string
	 */
	protected $_context = 'com_wishlist.comment.content';

	/**
	 * User
	 *
	 * @var object
	 */
	private $_creator = NULL;

	/**
	 * WishlistModelPlan
	 *
	 * @var object
	 */
	private $_cache = array(
		'comments.count' => null,
		'comments.list'  => null
	);

	/**
	 * Returns a reference to this model
	 *
	 * @param   mixed   $oid  ID (int) or alias (string)
	 * @return  object
	 */
	static function &getInstance($oid=0)
	{
		static $instances;

		if (!isset($instances))
		{
			$instances = array();
		}

		if (!isset($instances[$oid]))
		{
			$instances[$oid] = new self($oid);
		}

		return $instances[$oid];
	}

	/**
	 * Has this comment been reported
	 *
	 * @return  boolean  True if reported, False if not
	 */
	public function isReported()
	{
		if ($this->get('state') == self::APP_STATE_FLAGGED)
		{
			return true;
		}
		return false;
	}

	/**
	 * Return a formatted timestamp
	 *
	 * @param   string  $as  What format to return
	 * @return  string
	 */
	public function created($as='')
	{
		switch (strtolower($as))
		{
			case 'date':
				return Date::of($this->get('created'))->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
			break;

			case 'time':
				return Date::of($this->get('created'))->toLocal(Lang::txt('TIME_FORMAT_HZ1'));
			break;

			default:
				return $this->get('created');
			break;
		}
	}

	/**
	 * Get the creator of this entry
	 *
	 * Accepts an optional property name. If provided
	 * it will return that property value. Otherwise,
	 * it returns the entire user object
	 *
	 * @param   string  $property  What data to return
	 * @param   mixed   $default   Default value
	 * @return  mixed
	 */
	public function creator($property=null, $default=null)
	{
		if (!($this->_creator instanceof Profile))
		{
			$this->_creator = Profile::getInstance($this->get('created_by'));
			if (!$this->_creator)
			{
				$this->_creator = new Profile();
			}
		}
		if ($property)
		{
			$property = ($property == 'id') ? 'uidNumber' : $property;
			if ($property == 'picture')
			{
				return $this->_creator->getPicture($this->get('anonymous'));
			}
			return $this->_creator->get($property, $default);
		}
		return $this->_creator;
	}

	/**
	 * Get a list or count of replies
	 *
	 * @param   string   $rtrn     Data format to return
	 * @param   array    $filters  Filters to apply to data fetch
	 * @param   boolean  $clear    Clear cached data?
	 * @return  mixed
	 */
	public function replies($rtrn='list', $filters=array(), $clear=false)
	{
		if (!isset($filters['parent']))
		{
			$filters['parent'] = $this->get('id');
		}
		if (!isset($filters['item_type']))
		{
			$filters['item_type'] = $this->get('item_type');
		}
		if (!isset($filters['item_id']))
		{
			$filters['item_id'] = $this->get('item_id');
		}
		if (!isset($filters['state']))
		{
			$filters['state'] = array(static::APP_STATE_PUBLISHED, static::APP_STATE_FLAGGED);
		}

		switch (strtolower($rtrn))
		{
			case 'count':
				if (!is_numeric($this->_cache['comments.count']) || $clear)
				{
					$this->_cache['comments.count'] = 0;

					if (!$this->_cache['comments.list'])
					{
						$c = $this->comments('list', $filters);
					}
					foreach ($this->_cache['comments.list'] as $com)
					{
						$this->_cache['comments.count']++;
						if ($com->replies())
						{
							foreach ($com->replies() as $rep)
							{
								$this->_cache['comments.count']++;
								if ($rep->replies())
								{
									$this->_cache['comments.count'] += $rep->replies()->total();
								}
							}
						}
					}
				}
				return $this->_cache['comments.count'];
			break;

			case 'list':
			case 'results':
			default:
				if (!($this->_cache['comments.list'] instanceof ItemList) || $clear)
				{
					if ($this->get('replies', null) !== null)
					{
						$results = $this->get('replies');
					}
					else
					{
						$results = $this->_tbl->find($filters);
					}

					if ($results)
					{
						foreach ($results as $key => $result)
						{
							$results[$key] = new self($result);
						}
					}
					else
					{
						$results = array();
					}
					$this->_cache['comments.list'] = new ItemList($results);
				}
				return $this->_cache['comments.list'];
			break;
		}
	}

	/**
	 * Get the content of the entry
	 *
	 * @param   string   $as       Format to return state in [text, number]
	 * @param   integer  $shorten  Number of characters to shorten text to
	 * @return  string
	 */
	public function content($as='parsed', $shorten=0)
	{
		$as = strtolower($as);
		$options = array();

		switch ($as)
		{
			case 'parsed':
				$content = $this->get('content.parsed', null);

				if ($content == null)
				{
					$config = array(
						'option'   => 'com_wishlist',
						'scope'    => 'wishlist',
						'pagename' => $this->get('category'),
						'pageid'   => 0,
						'filepath' => '',
						'domain'   => '',
						'camelcase' => false
					);

					$this->set('content', stripslashes($this->get('content')));
					$content = $this->get('content');
					$this->importPlugin('content')->trigger('onContentPrepare', array(
						$this->_context,
						&$this,
						&$config
					));

					$attach = new Tables\Wish\Attachment($this->_db);
					$attach->output = 'web';
					$attach->uppath = PATH_APP . '/' . trim($this->config('webpath'), '/') . '/' . $this->get('item_id');
					$attach->webpath = $this->config('webpath');

					$this->set('content.parsed', $attach->parse($this->get('content')));
					$this->set('attachment', $attach->description);
					$this->set('content', $content);

					return $this->content($as, $shorten);
				}

				$options['html'] = true;
			break;

			case 'clean':
				$content = strip_tags($this->content('parsed'));
			break;

			case 'raw':
			default:
				$content = stripslashes($this->get('content'));
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;
		}

		if ($shorten)
		{
			$content = String::truncate($content, $shorten, $options);
		}
		return $content;
	}

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string $type The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		if (!isset($this->_base))
		{
			$this->_base = 'index.php?option=com_wishlist&task=wish&category=' . $this->get('listcategory') . '&rid=' . $this->get('listreference') . '&wishid=' . $this->get('item_id');
		}
		$link = $this->_base;

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'edit':
				$link .= 'index.php?option=com_wishlist&task=deletereply&replyid=' . $this->get('id');
			break;

			case 'delete':
				$link .= 'index.php?option=com_wishlist&task=deletereply&replyid=' . $this->get('id');
			break;

			case 'reply':
				$link .= 'index.php?option=com_wishlist&task=reply&cat=' . $this->get('listcategory') . '&id=' . $this->get('listid') . '&refid=' . $this->get('item_id') . '&wishid=' . $this->get('item_id');
			break;

			case 'report':
				$link = 'index.php?option=com_support&task=reportabuse&category=itemcomment&id=' . $this->get('id') . '&parent=' . $this->get('item_id');
			break;

			case 'permalink':
			default:
				$link .= '#c' . $this->get('id');
			break;
		}

		return $link;
	}

	/**
	 * Delete an entry and its associated content
	 *
	 * @return  boolean  True on success, false if not
	 */
	public function delete()
	{
		// Can't delete what doesn't exist
		if (!$this->exists())
		{
			return true;
		}

		// Remove comments
		foreach ($this->replies() as $reply)
		{
			if (!$reply->delete())
			{
				$this->setError($reply->getError());
				return false;
			}
		}

		return parent::delete();
	}
}

