<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

defined('_JEXEC') or die('Restricted access');

$config = JFactory::getConfig();
$juser = JFactory::getUser();

JHTML::_('behavior.framework', true);
JHTML::_('behavior.modal');

$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/hub.js?v=' . filemtime(JPATH_ROOT . '/templates/' . $this->template . '/js/hub.js'));

$browser = new \Hubzero\Browser\Detector();
$cls = array(
	$browser->name(),
	$browser->name() . $browser->major()
);

$this->setTitle($config->getValue('config.sitename') . ' - ' . $this->getTitle());
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo implode(' ', $cls); ?> ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo implode(' ', $cls); ?> ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo implode(' ', $cls); ?> ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo implode(' ', $cls); ?> ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="<?php echo $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo implode(' ', $cls); ?>"> <!--<![endif]-->
	<head>
		<meta name="viewport" content="width=device-width" />

		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \Hubzero\Document\Assets::getSystemStylesheet(); ?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/main.css" />
		<link rel="stylesheet" type="text/css" media="print" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/print.css" />

		<jdoc:include type="head" />

		<!--[if lt IE 9]><script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/html5.js"></script><![endif]-->

		<!--[if IE 10]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/browser/ie10.css" /><![endif]-->
		<!--[if IE 9]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/browser/ie9.css" /><![endif]-->
		<!--[if IE 8]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/browser/ie8.css" /><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/browser/ie7.css" /><![endif]-->
	</head>
	<body>
		<jdoc:include type="modules" name="notices" />
		<jdoc:include type="modules" name="helppane" />
		<div id="top">
			<div class="inner-wrap">
				<div class="inner">
					<div id="topbar">
						<ul>
							<li><a href="#content"><?php echo JText::_('TPL_HUBBASIC_SKIP'); ?></a></li>
							<li><a href="<?php echo $this->baseurl; ?>/about/contact"><?php echo JText::_('TPL_HUBBASIC_CONTACT'); ?></a></li>
						</ul>
						<jdoc:include type="modules" name="search" />
					<?php if ($this->countModules('helppane')) : ?>
						<p id="tab">
							<a href="<?php echo JRoute::_('index.php?option=com_support'); ?>" title="<?php echo JText::_('TPL_HUBBASIC_NEED_HELP'); ?>">
								<span><?php echo JText::_('TPL_HUBBASIC_HELP'); ?></span>
							</a>
						</p>
					<?php endif; ?>
					</div><!-- / #topbar -->
					<header id="masthead" role="banner">
						<div class="inner">
							<h1>
								<a href="<?php echo $this->baseurl; ?>" title="<?php echo $config->getValue('config.sitename'); ?>">
									<span><?php echo $config->getValue('config.sitename'); ?></span>
								</a>
								<span class="tagline"><?php echo JText::_('TPL_HUBBASIC_TAGLINE'); ?></span>
							</h1>

							<ul id="account" class="<?php echo (!$juser->get('guest')) ? 'loggedin' : 'loggedout'; ?>">
							<?php if (!$juser->get('guest')) {
									$profile = \Hubzero\User\Profile::getInstance($juser->get('id'));
							?>
								<li id="account-info">
									<img src="<?php echo $profile->getPicture(); ?>" alt="<?php echo $juser->get('name'); ?>" width="30" height="30" />
									<a class="account-details" href="<?php echo JRoute::_($profile->getLink()); ?>">
										<?php echo stripslashes($juser->get('name')); ?> 
										<span class="account-email"><?php echo $juser->get('email'); ?></span>
									</a>
									<span class="account-sep"></span>
									<ul>
										<li id="account-dashboard">
											<a href="<?php echo JRoute::_($profile->getLink() . '&active=dashboard'); ?>"><span><?php echo JText::_('TPL_HUBBASIC_ACCOUNT_DASHBOARD'); ?></span></a>
										</li>
										<li id="account-profile">
											<a href="<?php echo JRoute::_($profile->getLink() . '&active=profile'); ?>"><span><?php echo JText::_('TPL_HUBBASIC_ACCOUNT_PROFILE'); ?></span></a>
										</li>
										<li id="account-messages">
											<a href="<?php echo JRoute::_($profile->getLink() . '&active=messages'); ?>"><span><?php echo JText::_('TPL_HUBBASIC_ACCOUNT_MESSAGES'); ?></span></a>
										</li>
										<li id="account-logout">
											<a href="<?php echo JRoute::_('index.php?option=com_users&view=logout'); ?>"><span><?php echo JText::_('TPL_HUBBASIC_LOGOUT'); ?></span></a>
										</li>
									</ul>
								</li>
							<?php } else { ?>
								<li id="account-login">
									<a href="<?php echo JRoute::_('index.php?option=com_users&view=login'); ?>" title="<?php echo JText::_('TPL_HUBBASIC_LOGIN'); ?>"><?php echo JText::_('TPL_HUBBASIC_LOGIN'); ?></a>
								</li>
								<li id="account-register">
									<a href="<?php echo JRoute::_('index.php?option=com_members&controller=register'); ?>" title="<?php echo JText::_('TPL_HUBBASIC_SIGN_UP'); ?>"><?php echo JText::_('TPL_HUBBASIC_REGISTER'); ?></a>
								</li>
							<?php } ?>
							</ul><!-- / #account -->

							<nav id="nav" role="menu">
								<jdoc:include type="modules" name="user3" />
							</nav><!-- / #nav -->
						</div><!-- / .inner -->
					</header><!-- / #header -->

					<div id="sub-masthead">
					<?php if (!$this->countModules('welcome')) : ?>
						<div id="trail">
							<jdoc:include type="modules" name="breadcrumbs" />
							<div class="clear"></div>
						</div>
					<?php else: ?>
						<div id="splash">
							<jdoc:include type="modules" name="welcome" />
						</div><!-- / #splash -->
					<?php endif; ?>
					<?php if ($this->getBuffer('message')) : ?>
						<jdoc:include type="message" />
					<?php endif; ?>
					</div><!-- / #sub-masthead -->
				</div><!-- / .inner -->
			</div><!-- / .inner-wrap -->
		</div><!-- / #top -->
		<div id="wrap">
			<main id="content" class="<?php echo JRequest::getVar('option', ''); ?>" role="main">
				<div class="inner">
					<?php if ($this->countModules('left or right')) : ?>
						<section class="main section">
					<?php endif; ?>

					<?php if ($this->countModules('left')) : ?>
							<aside class="aside">
								<jdoc:include type="modules" name="left" />
							</aside><!-- / .aside -->
					<?php endif; ?>
					<?php if ($this->countModules('left or right')) : ?>
							<div class="subject">
					<?php endif; ?>

								<!-- start component output -->
								<jdoc:include type="component" />
								<!-- end component output -->

					<?php if ($this->countModules('left or right')) : ?>
							</div><!-- / .subject -->
					<?php endif; ?>
					<?php if ($this->countModules('right')) : ?>
							<aside class="aside">
								<jdoc:include type="modules" name="right" />
							</aside><!-- / .aside -->
					<?php endif; ?>

					<?php if ($this->countModules('left or right')) : ?>
						</section><!-- / .main section -->
					<?php endif; ?>
				</div><!-- / .inner -->
			</main><!-- / #content -->
		</div><!-- / #wrap -->

		<footer id="footer">
			<jdoc:include type="modules" name="footer" />
		</footer><!-- / #footer -->

		<jdoc:include type="modules" name="endpage" />
	</body>
</html>