<?php
namespace Components\Contracts\Models;

use Hubzero\Database\Relational;
use Hubzero\Utility\String;
use Session;
use Date;

/**
 * Drwho model class for a character
 */
class Page extends Relational
{
	/**
	 *
	 * Namespace of table.
	 *
	 * @var string
	 */
	public $namespace = 'contract';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'title';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'notempty',
		'alias' => 'notempty',
	);

	/**
	 * Defines a belongs to one relationship
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsToOne('Hubzero\User\User', 'created_by');
	}

	/**
	 * Is the character a friend of the Doctor?
	 *
	 * @return  boolean
	 */
	public function isFriend()
	{
		if ($this->get('friend'))
		{
			return true;
		}
		return false;
	}

	/**
	 * Is the character an enemt of the Doctor?
	 *
	 * @return  boolean
	 */
	public function isEnemy()
	{
		if ($this->get('enemy'))
		{
			return true;
		}
		return false;
	}

	/**
	 * Is the character an enemt of the Doctor?
	 *
	 * @return  boolean
	 */
	public function isDoctor()
	{
		if ($this->get('doctor'))
		{
			return true;
		}
		return false;
	}

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string  $type  The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		static $base;

		if (!isset($base))
		{
			$base = 'index.php?option=com_drwho&controller=characters';
		}

		$link = $base;

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'edit':
				$link .= '&task=edit&id=' . $this->get('id');
			break;

			case 'delete':
				$link .= '&task=delete&id=' . $this->get('id') . '&' . Session::getFormToken() . '=1';
			break;

			case 'view':
			case 'permalink':
			default:
				$link .= '&task=view&id=' . $this->get('id');
			break;
		}

		return $link;
	}

	/**
	 * Get the character's bio
	 *
	 * @param   string   $as       Format to return state in [text, number]
	 * @param   integer  $shorten  Number of characters to shorten text to
	 * @return  string
	 */
	public function bio($as='parsed', $shorten=0)
	{
		$as = strtolower($as);
		$options = array();

		switch ($as)
		{
			case 'parsed':
				$content = $this->get('bio.parsed', null);

				if ($content === null)
				{
					$bio = \Html::content('prepare', (string) $this->get('bio', ''));

					$this->set('bio.parsed', (string) $bio);

					return $this->bio($as, $shorten);
				}

				$options['html'] = true;
			break;

			case 'clean':
				$content = strip_tags($this->bio('parsed'));
			break;

			case 'raw':
			default:
				$content = $this->get('bio');
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;
		}

		if ($shorten)
		{
			$content = String::truncate($content, $shorten, $options);
		}
		return $content;
	}
}
