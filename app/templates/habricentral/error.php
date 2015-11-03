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

$this->template = 'habricentral';

$browser = new \Hubzero\Browser\Detector();
$b = $browser->name();
$v = $browser->major();

?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="<?php echo $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $b . ' ' . $b . $v; ?>"> <!--<![endif]-->
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />

		<title><?php echo Config::get('sitename') . ' - ' . $this->error->getCode(); ?></title>

		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \Hubzero\Document\Assets::getSystemStylesheet(array('fontcons', 'reset', 'columns', 'notifications')); /* reset MUST come before all others except fontcons */ ?>" />
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/main.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/error.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/html/mod_reportproblems/mod_reportproblems.css" type="text/css" />
		<link rel="stylesheet" type="text/css" media="print" href="<?php echo $this->baseurl ?>/templates/habricentral/css/print.css" />

		<script type="text/javascript" src="/core/assets/js/jquery.js"></script>
		<script type="text/javascript" src="/core/assets/js/jquery.ui.js"></script>
		<script type="text/javascript" src="/core/assets/js/jquery.fancybox.js"></script>
		<script type="text/javascript" src="/core/assets/js/jquery.tools.js"></script>
		<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/hub.jquery.js"></script>
		<script type="text/javascript" src="/core/modules/mod_reportproblems/assets/js/mod_reportproblems.jquery.js"></script>

		<!--[if IE 8]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/ie8win.css" />
		<![endif]-->
		<!--[if IE 7]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie7win.css" />
		<![endif]-->
		<!--[if lte IE 6]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie6win.css" />
			<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/ie.js"></script>
		<![endif]-->
	</head>
	<body>
		<?php \Hubzero\Module\Helper::displayModules('notices'); ?>

		<?php \Hubzero\Module\Helper::displayModules('helppane'); ?>
		<div id="afterclear">&nbsp;</div>

		<div id="header">
			<h1>
				<a href="<?php echo Request::base(); ?>" title="<?php echo Config::get('sitename'); ?>">
					<?php echo Config::get('sitename'); ?>
				</a>
			</h1>

			<ul id="toolbar" class="<?php if (!User::get('guest')) { echo 'loggedin'; } else { echo 'loggedout'; } ?>">
				<?php
					if (!User::get('guest')) {
						// Find the user's most recent support tickets
						$database = App::get('db');
						$recipient = new \Hubzero\Message\Recipient($database);
						$rows = $recipient->getUnreadMessages( User::get('id'), 0 );

						echo "\t\t\t".'<li id="myaccount"><a href="/members/'.User::get('id').'"><span>'.User::get('name').'</span></a></li>'."\n";
						echo "\t\t\t".'<li class="sep">|</li>'."\n";
						echo "\t\t\t".'<li id="logout"><a href="/logout"><span>'.Lang::txt('Logout').'</span></a></li>'."\n";
					} else {
						echo "\t\t\t".'<li id="login"><a href="/login" title="'.Lang::txt('Login').'">'.Lang::txt('Login').'</a></li>'."\n";
						echo "\t\t\t".'<li class="sep">|</li>'."\n";
						echo "\t\t\t".'<li id="register"><a href="/register" title="'.Lang::txt('Sign up for a free account').'">'.Lang::txt('Register').'</a></li>'."\n";
					}
				?>
			</ul>

			<?php \Hubzero\Module\Helper::displayModules('search'); ?>

			<p id="tab">
				<a href="/support" title="<?php echo Lang::txt('Need help? Send a trouble report to our support team.'); ?>">
					<span><?php echo Lang::txt('Need Help?'); ?></span>
				</a>
			</p>
		</div><!-- / #header -->

		<div id="nav">
			<h2>Navigation</h2>
			<?php \Hubzero\Module\Helper::displayModules('user3'); ?>
			<?php \Hubzero\Module\Helper::displayModules('introblock'); ?>
			<div class="clear"></div>
		</div><!-- / #nav -->

		<div id="trail">You are here:
			<?php
				$items = Pathway::items();
				$l = array();
				foreach ($items as $item) 
				{
					$text = trim(stripslashes($item->name));
					if (strlen($text) > 50)
					{
						$text = $text.' ';
						$text = substr($text,0,50);
						$text = substr($text,0,strrpos($text,' '));
						$text = $text.' &#8230;';
					}
					$url = Route::url($item->link);
					$url = str_replace('%20','+',$url);
					$l[] = '<a href="'.$url.'">'.$text.'</a>';
				}
				echo implode(' &rsaquo; ',$l);
			?>
		</div><!-- / #trail -->

		<div id="wrap">
			<div id="content" class="<?php echo Request::getCmd('option', ''); ?>">
				<div id="outline">
					<div id="errorbox" class="code-<?php echo $this->error->getCode() ?>">
						<h2><?php echo $this->error->getMessage() ?></h2>
						<p><?php echo Lang::txt('You may not be able to visit this page because of:'); ?></p>

						<ol>
							<?php if ($this->error->getCode() != 403) : ?>
								<li><?php echo Lang::txt('An out-of-date bookmark/favourite.'); ?></li>
								<li><?php echo Lang::txt('A search engine that has an out-of-date listing for this site.'); ?></li>
								<li><?php echo Lang::txt('A mis-typed address.'); ?></li>
								<li><?php echo Lang::txt('The requested resource was not found.'); ?></li>
							<?php endif; ?>
							
							<li><?php echo Lang::txt('This page may belong to a group with restricted access.  Only members of the group can view the contents.'); ?></li>
							<li><?php echo Lang::txt('An error has occurred while processing your request.'); ?></li>
						</ol>
						
						<?php if ($this->error->getCode() != 403) : ?>
							<p><?php echo Lang::txt('If difficulties persist, please contact the system administrator of this site.'); ?></p>
						<?php else : ?>
							<p><?php echo Lang::txt('If difficulties persist and you feel that you should have access to the page, please file a trouble report by clicking on the Help! option on the menu above.'); ?></p>
						<?php endif; ?>
					</div><!-- / #errorbox -->

					<form method="get" action="/search">
						<fieldset>
							<?php echo Lang::txt('Please try the'); ?> 

							<a href="/index.php" title="<?php echo Lang::txt('Go to the home page'); ?>">
								<?php echo Lang::txt('Home Page'); ?>
							</a>

							<span><?php echo Lang::txt('or'); ?></span> 

							<label>
								<?php echo Lang::txt('Search:'); ?> 
								<input type="text" name="searchword" value="" />
							</label>
							<input type="submit" value="<?php echo Lang::txt('Go'); ?>" />
						</fieldset>
					</form><!-- / # search box -->
				</div><!-- / #outline -->

				<?php if ($this->debug) { ?>
					<div id="techinfo">
						<?php echo $this->renderBacktrace(); ?>
					</div>
				<?php } ?>
			</div><!-- / #content -->

			<?php \Hubzero\Module\Helper::displayModules('footer'); ?>
		</div><!-- / #wrap -->

		<?php \Hubzero\Module\Helper::displayModules('endpage'); ?>
	</body>
</html>
