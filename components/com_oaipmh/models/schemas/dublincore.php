<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Components\Oaipmh\Models\Schemas;

use Components\Oaipmh\Models\Xml\Response;
use Components\Oaipmh\Models\Service;
use Components\Oaipmh\Models\Schema;

require_once(__DIR__ . '/../schema.php');

/**
 * Dublin Core schema handler
 */
class DublinCore implements Schema
{
	/**
	 * Schema prefix
	 * 
	 * @var  string
	 */
	public static $prefix = 'oai_dc';

	/**
	 * Schema description
	 * 
	 * @var  string
	 */
	public static $schema = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

	/**
	 * Schema namespace
	 * 
	 * @var  string
	 */
	public static $ns = 'http://www.openarchives.org/OAI/2.0/oai_dc/';

	/**
	 * Parent service
	 * 
	 * @var  object
	 */
	protected $service = null;

	/**
	 * XML Response
	 * 
	 * @var  object
	 */
	protected $response = null;

	/**
	 * Callback for escaping.
	 *
	 * @var  string
	 */
	protected $_escape = 'htmlspecialchars';

	/**
	 * Charset to use in escaping mechanisms; defaults to utf8 (UTF-8)
	 *
	 * @var  string
	 */
	protected $_charset = 'UTF-8';

	/**
	 * Does this adapter respond to a mime type
	 *
	 * @param   string   $type  Schema type
	 * @return  boolean
	 */
	public static function handles($type)
	{
		return in_array($type, array(
			'dc',
			'qdc',
			'oai_dc',
			'oai_pmh:dc',
			'dublincore',
			'qualifiedddc',
		));
	}

	/**
	 * Constructor
	 *
	 * @param   object  $service
	 * @param   object  $response
	 * @return  void
	 */
	public function __construct($service=null, $response=null)
	{
		if ($service)
		{
			$this->setService($service);
		}

		if ($response)
		{
			$this->setResponse($response);
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * If escaping mechanism is either htmlspecialchars or htmlentities, uses
	 * {@link $_encoding} setting.
	 *
	 * @param   mixed  $var  The output to escape.
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities')))
		{
			return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_charset);
		}

		return call_user_func($this->_escape, $var);
	}

	/**
	 * Sets the _escape() callback.
	 *
	 * @param   mixed  $spec  The callback for _escape() to use.
	 * @return  void
	 */
	public function setEscape($spec)
	{
		$this->_escape = $spec;
		return $this;
	}

	/**
	 * Get XML builder
	 *
	 * @return  object
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * Set service
	 *
	 * @param   object  $service
	 * @return  object
	 */
	public function setService(Service &$service)
	{
		$this->service = $service;

		return $this;
	}

	/**
	 * Get response
	 *
	 * @return  object
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Set response
	 *
	 * @param   object  $response
	 * @return  object
	 */
	public function setResponse(Response &$response)
	{
		$this->response = $response;

		return $this;
	}

	/**
	 * Process a list of sets
	 *
	 * @param   array   $iterator
	 * @return  object  $this
	 */
	public function sets($iterator)
	{
		foreach ($iterator as $index => $set)
		{
			// make sure we have a record
			if ($set === null)
			{
				continue;
			}

			$spec = '';
			if (!empty($set[0]))
			{
				$spec = $set[0];
			}
			elseif (empty($set[0]) && !empty($set[1]))
			{
				$spec = strtolower($set[1]);
				$spec = str_replace(' ', '_', $spec);
			}
			if (isset($set[3]) && !empty($set[3]))
			{
				$spec = $set[3] . ':' . $spec;
			}

			$this->response
					->element('set')
						->element('setSpec', $spec)->end();

			if (!empty($set[1]))
			{
				$this->response->element('setName', $set[1])->end();
			}

			if (!empty($set[2]))
			{
				$set[2] = html_entity_decode($set[2]);
				$set[2] = strip_tags($set[2]);

				$this->response
					->element('setDescription')
						->element('oai_dc:dc')
							->attr('xmlns:' . self::$prefix, self::$ns)
							->attr('xmlns:dc', 'http://purl.org/dc/elements/1.1/')
							->attr('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance')
							->attr('xsi:schemaLocation', self::$ns . ' ' . self::$schema)
							->element('dc:description', $this->escape($set[2]))->end()
						->end()
					->end();
			}

			$this->response
					->end();
		}

		return $this;
	}

	/**
	 * Process a list of records
	 *
	 * @param   array    $iterator
	 * @param   boolean  $metadata
	 * @return  object   $this
	 */
	public function records($iterator, $metadata=true)
	{
		// loop through each item
		foreach ($iterator as $index => $record)
		{
			// make sure we have a record
			if ($record === null)
			{
				continue;
			}

			$this->record($record, $metadata);
		}

		return $this;
	}

	/**
	 * Process a single record
	 *
	 * @param   object   $result
	 * @param   boolean  $metadata
	 * @return  object   $this
	 */
	public function record($result, $metadata=true)
	{
		$this->response
				->element('record')
					->element('header');

		if (!empty($result->identifier))
		{
			$this->response->element('identifier', $result->identifier)->end();
		}

		// we want the "T" & "Z" strings in the output NOT the UTC offset (-400)
		$gran = $this->service->get('gran', 'c');
		if ($gran == 'c')
		{
			$gran = 'Y-m-d\Th:i:s\Z';
		}

		$datestamp = strtotime($result->date);
		$datestamp = gmdate($gran, $datestamp);
		if (!empty($datestamp))
		{
			$this->response->element('datestamp', $datestamp)->end();
		}
		if (!empty($result->type))
		{
			$this->response->element('setSpec', $result->type)->end();
		}

		$this->response->end(); // end header

		if ($metadata)
		{
			$this->response
				->element('metadata')
					->element('oai_dc:dc')
						->attr('xmlns:' . self::$prefix, self::$ns)
						->attr('xmlns:dc', 'http://purl.org/dc/elements/1.1/')
						->attr('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance')
						->attr('xsi:schemaLocation', self::$ns . ' ' . self::$schema);

			$dcs = array(
				'title',
				'creator',
				'subject',
				'date',
				'identifier',
				'description',
				'type',
				'publisher',
				'rights',
				'contributor',
				'relation',
				'format',
				'coverage',
				'language',
				'source'
			);

			// loop through DC elements
			for ($i=0; $i<15; $i++)
			{
				if (!isset($result->$dcs[$i]))
				{
					continue;
				}

				if (is_array($result->$dcs[$i]))
				{
					foreach ($result->$dcs[$i] as $sub)
					{
						$sub = html_entity_decode($sub);

						$this->response->element('dc:' . $dcs[$i], $this->escape(stripslashes($sub)))->end();
					}
				}
				elseif (!empty($result->$dcs[$i]))
				{
					if ($dcs[$i] == 'date')
					{
						$this->response->element('dc:' . $dcs[$i], \JFactory::getDate($result->date)->format($gran))->end();
					}
					else
					{
						$res = html_entity_decode(stripslashes($result->$dcs[$i]));

						$this->response->element('dc:' . $dcs[$i], $this->escape($res))->end();
					}
				}
			}

			$this->response->end() // end oai_dc:dc
						->end(); // end metadata
		}

		$this->response->end(); // end record

		return $this;
	}
}
