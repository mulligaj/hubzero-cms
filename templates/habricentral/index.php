<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

$config = JFactory::getConfig();
$juser = JFactory::getUser();

$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/hub.js?v=' . filemtime(JPATH_ROOT . '/templates/' . $this->template . '/js/hub.js'));

$browser = new \Hubzero\Browser\Detector();
$b = $browser->name();
$v = $browser->major();

$this->setTitle($config->getValue('config.sitename') . ' - ' . $this->getTitle());
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie9"> <![endif]-->
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
		<?php if ($this->countModules( 'accessibility' )) : ?>
		<jdoc:include type="modules" name="accessibility" /> <?php endif; ?>
			<h1>
				<a href="<?php echo $this->baseurl ?>" title="<?php echo $config->getValue('config.sitename'); ?>">
					<?php echo $config->getValue('config.sitename'); ?>
				</a>
			</h1>

			<ul id="toolbar" class="<?php if (!$juser->get('guest')) { echo 'loggedin'; } else { echo 'loggedout'; } ?>">
				<?php
					if (!$juser->get('guest')) {
						// Find the user's most recent support tickets
						$database = JFactory::getDBO();
						$recipient = new \Hubzero\Message\Recipient( $database );
						$rows = $recipient->getUnreadMessages( $juser->get('id'), 0 );

						echo "\t\t\t".'<li id="myaccount"><a href="/members/'.$juser->get('id').'"><span>'.$juser->get('name').'</span></a></li>'."\n";
						echo "\t\t\t".'<li class="sep">|</li>'."\n";
						echo "\t\t\t".'<li id="logout"><a href="/logout"><span>'.JText::_('Logout').'</span></a></li>'."\n";
					} else {
						echo "\t\t\t".'<li id="login"><a href="/login" title="'.JText::_('Login').'">'.JText::_('Login').'</a></li>'."\n";
						echo "\t\t\t".'<li class="sep">|</li>'."\n";
						echo "\t\t\t".'<li id="register"><a href="/register" title="'.JText::_('Sign up for a free account').'">'.JText::_('Register').'</a></li>'."\n";
					}
				?>
			</ul>

			<jdoc:include type="modules" name="search" />
			
			<?php if ($this->countModules( 'helppane' )) : ?>
				<p id="tab">
					<a href="/support" title="<?php echo JText::_('Need help? Send a trouble report to our support team.'); ?>">
						<span><?php echo JText::_('Need Help?'); ?></span>
					</a>
				</p>
			<?php endif; ?>
		</div><!-- / #header -->

		<div id="nav">
			<h2>Navigation</h2>
			<jdoc:include type="modules" name="user3" />
			<jdoc:include type="modules" name="introblock" />
			<div class="clear"></div>
		</div><!-- / #nav -->


		<?php if ($this->countModules( 'banner' )) : ?>
			<div id="home-banner">
				<jdoc:include type="modules" name="banner" />
			</div>
		<?php endif; ?>

		<?php if (!$this->countModules( 'banner' )) : ?>
			<div id="trail">You are here: 
				<?php
					$app = JFactory::getApplication();
					$pathway = $app->getPathway();

					$items = $pathway->getPathWay();
					$l = array();

					foreach ($items as $item) 
					{
						$text = trim(stripslashes($item->name));
						if (strlen($text) > 50) {
							$text = $text.' ';
							$text = substr($text,0,50);
							$text = substr($text,0,strrpos($text,' '));
							$text = $text.' &#8230;';
						}
						$url = JRoute::_($item->link);
						$url = str_replace('%20','+',$url);
						$l[] = '<a href="'.$url.'">'.$text.'</a>';
					}
					echo implode(' &rsaquo; ',$l);
				?>
			</div><!-- / #trail -->
		<?php endif; ?>

		<div id="wrap">
			<div id="content" class="<?php echo JRequest::getVar('option', ''); ?>">
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
			
				<?php if ($this->getBuffer('message')) : ?>
					<jdoc:include type="message" />
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
