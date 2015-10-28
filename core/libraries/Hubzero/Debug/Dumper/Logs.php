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

namespace Hubzero\Debug\Dumper;

/**
 * Monolog renderer
 */
class Logs extends AbstractRenderer
{
	/**
	 * Logger
	 *
	 * @var  object
	 */
	protected $_logger = null;

	/**
	 * Constructor
	 *
	 * @param   array  $messages
	 * @return  void
	 */
	public function __construct($messages = null)
	{
		parent::__construct($messages);

		$this->_logger = \Log::getRoot();
	}

	/**
	 * Returns renderer name
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'logs';
	}

	/**
	 * Render a list of messages
	 *
	 * @param   array  $messages
	 * @return  string
	 */
	public function render($messages = null)
	{
		if ($messages)
		{
			$this->setMessages($messages);
		}

		$messages = $this->getMessages();

		foreach ($messages as $item)
		{
			$this->_logger->debug(print_r($item['var'], true));
		}
	}

	/**
	 * Turn an array into a pretty print format
	 *
	 * @param   array  $arr
	 * @return  string
	 */
	protected function _deflate($arr)
	{
		$output = 'Array( ' . "\n";
		$a = array();
		foreach ($arr as $key => $val)
		{
			if (is_array($val))
			{
				$a[] = "\t" . '<code class="ky">' . $key . '</code> <code class="op">=></code> <code class="vl">' . $this->_deflate($val) . '</code>';
			}
			else
			{
				$a[] = "\t" . '<code class="ky">' . $key . '</code> <code class="op">=></code> <code class="vl">' . htmlentities($val, ENT_COMPAT, 'UTF-8') . '</code>';
			}
		}
		$output .= implode(", \n", $a) . "\n" . ' )' . "\n";

		return $output;
	}
}
