<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access.
defined('_JEXEC') or die;

$app = JFactory::getApplication();

// Load base styles
$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/index.css?v=' . filemtime(JPATH_ROOT . '/administrator/templates/' . $this->template . '/css/index.css'));
$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/cpanel.css?v=' . filemtime(JPATH_ROOT . '/administrator/templates/' . $this->template . '/css/cpanel.css'));
// Load theme
if ($theme = $this->params->get('theme'))
{
	if ($theme == 'custom')
	{
		$color = $this->params->get('color');
		$this->addStyleDeclaration(include_once(__DIR__ . '/css/themes/custom.php'));
	}
	else if ($theme != 'gray')
	{
		$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/themes/' . $theme . '.css');
	}
}
// Load language direction CSS
if ($this->direction == 'rtl')
{
	$this->addStyleSheet($this->baseurl . '/templates/' . $this->template . '/css/common/rtl.css');
}

$htheme = $this->params->get('header', 'light');
$browser = new \Hubzero\Browser\Detector();
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $htheme; ?> ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $htheme; ?> ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $htheme; ?> ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $htheme; ?> ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="<?php echo $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $htheme . ' ' . $browser->name() . ' ' . $browser->name() . $browser->major(); ?>"> <!--<![endif]-->
	<head>
		<meta name="viewport" content="width=device-width" />

		<jdoc:include type="head" />

		<script type="text/javascript" src="<?php echo str_replace('/administrator', '', $this->baseurl); ?>/media/system/js/jquery.uniform.min.js"></script>
		<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/placeholder.js"></script>
		<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/index.js"></script>

		<!--[if lt IE 9]>
			<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/html5.js"></script>
		<![endif]-->

		<!--[if IE 9]>
			<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/browser/ie9.css" />
		<![endif]-->
		<!--[if IE 8]>
			<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/browser/ie8.css" />
		<![endif]-->
	</head>
	<body id="cpanel-body">
		<jdoc:include type="modules" name="notices" />

		<header id="header" role="banner">
			<h1><a href="<?php echo JURI::root(); ?>"><?php echo $app->getCfg('sitename'); ?></a></h1>

			<ul class="user-options">
					<?php
						//Display an harcoded logout
						$task = JRequest::getCmd('task');
						if ($task == 'edit' || $task == 'editA' || JRequest::getInt('hidemainmenu')) {
							$logoutLink = '';
						} else {
							$logoutLink = JRoute::_('index.php?option=com_login&task=logout&' . JUtility::getToken() . '=1');
						}
						$hideLinks	= JRequest::getBool('hidemainmenu');
						$output = array();

						// Print the logout link.
						$output[] = ($hideLinks
										? '<li data-title="' . JText::_('TPL_KAMELEON_LOG_OUT') . '" class="disabled"><span class="logout">'
										: '<li data-title="' . JText::_('TPL_KAMELEON_LOG_OUT') . '"><a class="logout" href="' . $logoutLink . '">') . JText::_('TPL_KAMELEON_LOG_OUT') . ($hideLinks ? '</span></li>' : '</a></li>');
						// Reverse rendering order for rtl display.
						if ($this->direction == "rtl") :
							$output = array_reverse($output);
						endif;
						// Output the items.
						foreach ($output as $item) :
						echo $item;
						endforeach;
					?>
			</ul>
		</header><!-- / header -->

		<div id="wrap">
			<nav role="navigation" class="main-navigation">
				<div class="inner-wrap">
					<jdoc:include type="modules" name="menu" />
				</div>
			</nav><!-- / .navigation -->

			<section id="component-content">
				<div id="toolbar-box" class="toolbar-box">
					<jdoc:include type="modules" name="title" />
					<jdoc:include type="modules" name="toolbar" />
				</div><!-- / #toolbar-box -->

				<!-- Notifications begins -->
				<jdoc:include type="message" />
				<!-- Notifications ends -->

				<?php if (!JRequest::getInt('hidemainmenu') && $this->countModules('submenu') > 1): ?>
				<nav role="navigation" class="sub-navigation">
					<jdoc:include type="modules" name="submenu" />
				</nav><!-- / .sub-navigation -->
				<?php endif; ?>

				<section id="main" class="<?php echo JRequest::getCmd('option', ''); ?>">
					<!-- Content begins -->
					<div class="hero width-100">
						<jdoc:include type="modules" name="cpanelhero" style="cpanel" />
					</div>
					<div class="cpanel-wrap">
						<div class="cpanel col width-48 fltlft">
							<jdoc:include type="modules" name="icon" style="cpanel" />
						</div>
						<div class="cpanel col width-48 fltrt">
							<jdoc:include type="modules" name="cpanel" style="cpanel" />
						</div>
					</div>
					<!-- Content ends -->

					<noscript>
						<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
					</noscript>
				</section><!-- / #main -->
			</section><!-- / #content -->
		</div><!-- / #wrap -->

		<footer id="footer">
			<section class="basement">
				<p class="copyright">
					<?php echo JText::sprintf('TPL_KAMELEON_COPYRIGHT', JURI::root(), $app->getCfg('sitename'), date("Y")); ?>
				</p>
				<p class="promotion">
					<?php echo JText::sprintf('TPL_KAMELEON_POWERED_BY', \Hubzero\Version\Version::VERSION); ?>
				</p>
			</section><!-- / .basement -->
		</footer><!-- / #footer -->
	</body>
</html>