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

Html::behavior('framework', true);
Html::behavior('modal');

$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/hub.js?v=' . filemtime(__DIR__ . '/js/hub.js'));

$browser = new \Hubzero\Browser\Detector();
$b = $browser->name();
$v = $browser->major();

$this->setTitle(Config::get('sitename') . ' - ' . $this->getTitle());
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="<?php echo $this->direction; ?>" lang="<?php echo $this->language; ?>" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="<?php echo $this->direction; ?>" lang="<?php echo $this->language; ?>" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="<?php echo $this->direction; ?>" lang="<?php echo $this->language; ?>" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="<?php echo $this->direction; ?>" lang="<?php echo $this->language; ?>" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="<?php echo $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $b . ' ' . $b . $v; ?>"> <!--<![endif]-->
	<head>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \Hubzero\Document\Assets::getSystemStylesheet(); ?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/main.css" />
		<link rel="stylesheet" type="text/css" media="print"  href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/print.css" />

		<jdoc:include type="head" />

		<!--[if IE 8]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie8win.css" />
		<![endif]-->
		<!--[if lte IE 7]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie7win.css" />
		<![endif]-->
		<!--[if lte IE 6]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie6win.css" />
			<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/ie.js"></script>
		<![endif]-->
	</head>

	<body<?php if ($this->countModules( 'banner or welcome' )) : echo ' id="frontpage"'; endif; ?>>
		<jdoc:include type="modules" name="notices" />

		<jdoc:include type="modules" name="helppane" />
		<div id="afterclear">&nbsp;</div>

		<div id="header">
			<?php if ($this->countModules('accessibility')) : ?>
				<jdoc:include type="modules" name="accessibility" />
			<?php endif; ?>
			<h1>
				<a href="<?php echo Request::base(); ?>" title="<?php echo Config::get('sitename'); ?>">
					<?php echo Config::get('sitename'); ?>
				</a>
			</h1>

			<ul id="toolbar" class="<?php if (!User::get('guest')) { echo 'loggedin'; } else { echo 'loggedout'; } ?>">
				<?php if (!User::get('guest')) : ?>
					<li id="myaccount"><a href="/members/<?php echo User::get('id'); ?>"><span><?php echo User::get('name'); ?></span></a></li>
					<li class="sep">|</li>
					<li id="logout"><a href="/logout"><span><?php echo Lang::txt('Logout'); ?></span></a></li>
				<?php else : ?>
					<li id="login"><a href="/login" title="<?php echo Lang::txt('Login'); ?>"><?php echo Lang::txt('Login'); ?></a></li>
					<li class="sep">|</li>
					<li id="register"><a href="/register" title="<?php echo Lang::txt('Sign up for a free account'); ?>"><?php echo Lang::txt('Register'); ?></a></li>
				<?php endif; ?>
			</ul>

			<jdoc:include type="modules" name="search" />

			<?php if ($this->countModules('helppane')) : ?>
				<p id="tab">
					<a href="/support" title="<?php echo Lang::txt('Need help? Send a trouble report to our support team.'); ?>">
						<span><?php echo Lang::txt('Need Help?'); ?></span>
					</a>
				</p>
			<?php endif; ?>
		</div><!-- / #header -->

		<div id="nav">
			<h2><?php echo Lang::txt('Navigation'); ?></h2>
			<jdoc:include type="modules" name="user3" />
			<jdoc:include type="modules" name="introblock" />
			<div class="clear"></div>
		</div><!-- / #nav -->

		<?php if ($this->countModules('banner')) : ?>
			<?php if ($this->getBuffer('message')) : ?>
				<jdoc:include type="message" />
			<?php endif; ?>
			<div id="home-banner">
				<jdoc:include type="modules" name="banner" />
			</div>
		<?php endif; ?>

		<?php if (!$this->countModules('banner')) : ?>
			<div id="trail">
				<jdoc:include type="modules" name="breadcrumbs" />
				<jdoc:include type="modules" name="collectBtn" />
			</div><!-- / #trail -->
		<?php endif; ?>

		<div id="wrap">
			<div id="content" class="<?php echo Request::getCmd('option', ''); ?>">
				<?php if ($this->countModules( 'left' )) : ?>
					<div class="main section withleft">
						<div class="aside">
							<jdoc:include type="modules" name="left" />
						</div><!-- / #column-left -->
						<div class="subject">
				<?php endif; ?>
				<?php if ($this->countModules('right')) : ?>
					<div class="main section">
						<div class="aside">
							<jdoc:include type="modules" name="right" />
						</div><!-- / .aside -->
						<div class="subject">
				<?php endif; ?>

				<?php if (!$this->countModules('banner')) : ?>
					<?php if ($this->getBuffer('message')) : ?>
						<jdoc:include type="message" />
					<?php endif; ?>
				<?php endif; ?>

				<jdoc:include type="component" />

				<?php if ($this->countModules('left or right')) : ?>
						</div><!-- / .subject -->
						<div class="clear"></div>
					</div><!-- / .main section -->
				<?php endif; ?>
			</div><!-- / #content -->

			<jdoc:include type="modules" name="footer" />
		</div><!-- / #wrap -->

		<jdoc:include type="modules" name="endpage" />
	</body>
</html>
