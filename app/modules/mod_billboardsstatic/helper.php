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

namespace Modules\BillboardsStatic;

use Hubzero\Module\Module;

/**
 * Mod_Billboards helper class, used to query for billboards and contains the display method
 */
class Helper extends Module
{
	private static $multiple_instances = 0;

	/**
	 * Get the list of billboads in the selected collection
	 * 
	 * @return retrieved rows
	 */
	private function _getList()
	{
		$db = \App::get('db');

		// Get the correct billboards collection to display from the parameters
		$collection = (int) $this->params->get('collection', 1);

		// Query to grab all the buildboards associated with the selected collection
		// Make sure we only grab published billboards
		$query = 'SELECT b.*, c.*' .
			' FROM #__billboards_billboards as b, #__billboards_collections as c' .
			' WHERE c.id = b.collection_id' .
			' AND published = 1' .
			' AND b.collection_id = ' . $collection .
			' ORDER BY ' . ($this->params->get('random', 0) ? 'RAND() LIMIT 1' : '`ordering` ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 * Display method
	 * Used to add CSS for each slide as well as the javascript file(s) and the parameterized function
	 * 
	 * @return void
	 */
	public function display()
	{
		// Check if we have multiple instances of the module running
		// If so, we only want to push the CSS and JS to the template once
		if (!self::$multiple_instances)
		{
			// Push some CSS to the template
			$this->css('mod_billboards.css', 'mod_billboards');
			if (!\Plugin::isEnabled('system', 'jquery'))
			{
				\Document::addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
			}
			$this->js('mod_billboards.js', 'mod_billboards');
		}

		self::$multiple_instances++;

		// Get the billboard slides
		$this->slides = $this->_getList();

		// Get some parameters
		$transition       = $this->params->get('transition', 'scrollHorz');
		$random           = $this->params->get('random', 0);
		$timeout          = $this->params->get('timeout', 5) * 1000;
		$speed            = $this->params->get('speed', 1) * 1000;
		$this->collection = $this->params->get('collection', 1);
		$this->pager      = $this->params->get('pager', 'pager');

		// Get the billboard background location from the billboards parameters
		$params = \Component::params('com_billboards');
		$image_location = $params->get('image_location', '/site/media/images/billboards/');
		if ($image_location == '/site/media/images/billboards/')
		{
			$image_location = '/app'. $image_location;
		}

		// Add the CSS to the template for each billboard
		foreach ($this->slides as $slide)
		{
			$background = (!empty($slide->background_img)) ? "background: url('$image_location$slide->background_img') no-repeat 0 0;" : '';
			$padding    = (!empty($slide->padding)) ? "padding: $slide->padding;" : '';

			$css = 
				"#$slide->alias {
					$background
					}
				#$slide->alias p {
					$padding
					}";
			$this->css($css);
			$this->css($slide->css);
		}

		// Add the CSS to give the pager a unique ID per billboard collection
		// We need this to manage multiple buildboard pagers potentially moving at different speeds
		// @TODO: there should be a better way of doing this
		if ($this->pager != 'null')
		{
			$js_pager    = "'#$this->pager$this->collection'";
			$this->pager = $this->pager . $this->collection;
			$pager = 
				".slider #$this->pager a.activeSlide {
					opacity:1.0;
					}";
			$this->css($pager);
		}
		else 
		{
			$js_pager = $this->pager;
		}

		// Add the javascript ready function with variables based on this specific billboard
		// Pause: true - means the billbaord stops scrolling on hover
		/*if(!\Plugin::isEnabled('system', 'jquery'))
		{
			$js = '
				var $jQ = jQuery.noConflict();

				$jQ(document).ready(function() {
					$jQ(\'#' . $this->collection . '\').cycle({
						fx: "' . $transition . '",
						timeout: ' . $timeout .',
						pager: ' . $js_pager . ',
						speed: ' . $speed . ',
						random: ' . $random . ',
						cleartypeNoBg: true,
						slideResize: 0,
						pause: true
					});
				});';
		}
		else
		{
			$js = '
				if (!HUB) {
					var HUB = {};
				}

				if (!jq) {
					var jq = $;
				}

				HUB.Billboards = {
					jQuery: jq,

					initialize: function() {
						var $ = this.jQuery;

						$(\'#' . $this->collection . '\').cycle({
							fx: "' . $transition . '",
							timeout: ' . $timeout .',
							pager: ' . $js_pager . ',
							speed: ' . $speed . ',
							random: ' . $random . ',
							cleartypeNoBg: true,
							slideResize: 0,
							pause: true
						});
					}
				}

				jQuery(document).ready(function($){
					HUB.Billboards.initialize();
				});';
		}

		$this->js($js);*/

		parent::display();
	}
}
