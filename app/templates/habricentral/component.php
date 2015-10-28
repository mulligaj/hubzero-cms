<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$browser = new \Hubzero\Browser\Detector();
$b = $browser->name();
$v = $browser->major();

$template = 'habricentral';
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="<?php echo  $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="<?php echo $this->direction; ?>" lang="<?php echo  $this->language; ?>" class="<?php echo $b . ' ' . $b . $v; ?>"> <!--<![endif]-->
	<head>
		<jdoc:include type="head" />

		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \Hubzero\Document\Assets::getSystemStylesheet(array('reset', 'fontcons', 'columns', 'notifications', 'layout')); /* reset MUST come before all others except fontcons */ ?>" />

		<!-- <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" /> -->
<?php if ($this->direction == 'rtl' && (!file_exists(JPATH_THEMES . DS . $template . DS . 'css/component_rtl.css') || !file_exists(JPATH_THEMES . DS . $template . DS . 'css/component.css'))) : ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/system/css/template_rtl.css" />
<?php elseif ($this->direction == 'rtl') : ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $template; ?>/css/component.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $template; ?>/css/component_rtl.css" />
<?php elseif ($this->direction == 'ltr' && !file_exists(JPATH_THEMES . DS . $template . DS . 'css/component.css')) : ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/system/css/template.css" />
<?php elseif ($this->direction == 'ltr') : ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $template; ?>/css/component.css" />
<?php endif; ?>
		<!--[if IE 9]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $template; ?>/css/browser/ie9.css" />
		<![endif]-->
		<!--[if IE 8]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $template; ?>/css/browser/ie8.css" />
		<![endif]-->
		<!--[if IE 7]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $template; ?>/css/browser/ie7.css" />
		<![endif]-->
	</head>
	<body class="<?php echo JRequest::getCmd('option', 'contentpane'); ?>" id="component-body">
		<jdoc:include type="message" />
		<jdoc:include type="component" />
	</body>
</html>