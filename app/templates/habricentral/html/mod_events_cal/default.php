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
?>
<?php if ($this->error) : ?>
	<p class="error"><?php echo $this->error; ?></p>
<?php else : ?>
	<div class="event-cal">
		<div class="event-cal-caption"> 
			<span><a href="/events/<?php echo date("Y") . DS . date("m"); ?>"><?php echo date("F"); ?></a></span>
			<a class="back" href="/events/<?php echo date("Y") . DS . date("m", strtotime("-1 MONTH")); ?>"><?php echo date("F", strtotime("-1 MONTH")); ?></a>
			<a class="forward" href="/events/<?php echo date("Y") . DS . date("m", strtotime("+1 MONTH")); ?>"><?php echo date("F", strtotime("+1 MONTH")); ?></a>
		</div>
		<?php echo $this->content; ?>
	</div>
<?php endif; ?>