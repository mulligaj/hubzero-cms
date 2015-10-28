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

namespace Hubzero\Error\Renderer;

use Hubzero\Error\RendererInterface;
use Hubzero\Notification\Handler;
use Exception;

/**
 * Displays the custom error page when an uncaught exception occurs.
 */
class Notification implements RendererInterface
{
	/**
	 * Notification handler
	 *
	 * @var  object
	 */
	protected $notifier;

	/**
	 * Create a new Notification exception displayer.
	 *
	 * @param   object  $notifier
	 * @return  void
	 */
	public function __construct(Handler $notifier)
	{
		$this->notifier = $notifier;
	}

	/**
	 * Render the error page based on an exception.
	 *
	 * @param   object  $error  The exception for which to render the error page.
	 * @return  void
	 */
	public function render(Exception $error)
	{
		$this->notifier->message($error->getMessage(), ($error->getCode() == 500 ? 'error' : 'warning'));
	}
}
