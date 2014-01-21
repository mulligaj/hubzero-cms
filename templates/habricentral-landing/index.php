<?php
/**
 * @package     hubzero-cms
 * @author      Shawn Rice <zooley@purdue.edu>
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
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
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
$config =& JFactory::getConfig();
$juser =& JFactory::getUser();                              

//include the constant contact stuff
include("constant_contact/contact.php");                              
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
        <title>Introducing HABRI Central: A New Resource for the Study of the Human-Animal Bond</title>
	<link rel="stylesheet" href="/templates/habricentral-landing/css/main.css" />
	<!--[if IE 8]>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/hubbasic/css/ie8.css" />
	<![endif]-->
	<!--[if lte IE 7]>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/hubbasic/css/ie7.css" />
	<![endif]-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script src="/templates/habricentral-landing/js/landing.js"></script>
	
</head>

<body class="landing">
	<!-- notices pannel -->
	<jdoc:include type="modules" name="notices" />
	
	<div id="container">
		<div id="header">
			<h2>Introducing</h2>
			<h1>HABRI Central</h1>
			<h3>a free, comprehensive, electronic resource for the Human-Animal Bond community. Launching 2012.</h3>
			<div id="open-access">Open Access</div>
		</div><!-- / #header -->
		
		<div id="content">
			<div id="video">
				<iframe src="https://www.youtube.com/embed/9kMviSUuUPM"></iframe>
			</div><!-- /#video -->
			<div id="about">
				<p>With a vast bibliography and repository of published and unpublished literature and media, an online outlet for publication, and a collaborative workspace built on the HUBzero platform for digital scholarship, HABRI Central will be the place to go for human-animal bond research and collaboration when it launches in 2012.</p>

				<p>While there have been a number of important previous initiatives, especially in the area of building bibliographies, HABRI Central represents a uniquely ambitious attempt to bring together the research that underpins the scientific study of the human-animal bond. This has been recognized by the community as an important but challenging task because: the literature comes from so many disciplines (psychology, nursing, veterinary medicine, epidemiology, etc.); important information is produced outside as well as inside the academy (e.g., by animal-assisted therapy and welfare organizations and by veterinary practitioners); and data is of such varying quality, ranging from anecdotal to evidence-based.</p>
				<p>Thanks to the generous support of the <a href="http://habri.org/" rel="external" title="HABRI Foundation">HABRI Foundation</a>, HABRI Central will be free-of-charge to all users at launch</p>
			</div><!-- /#about -->
		</div><!-- /#content -->
		
		<div id="social">
			<div class="facebook">
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) {return;}
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>
				<div class="fb-like" data-send="false" data-layout="box_count" data-show-faces="false"></div>
			</div>
			<div class="twitter">
				<a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical">Tweet</a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
			</div>
			<div class="googleplus">
				<!-- Place this tag where you want the +1 button to render -->
				<g:plusone size="tall"></g:plusone>

				<!-- Place this render call where appropriate -->
				<script type="text/javascript">
				  (function() {
				    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				    po.src = 'https://apis.google.com/js/plusone.js';
				    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				  })();
				</script>
			</div>
			<br class="clear" />
		</div><!-- /#social -->
		
		<div id="email">
			<h2>Want to know more?</h2>
			<p>Enter your e-mail address below and we’ll let you know when 
			HABRI Central launches and how you can get involved.</p>
		</div>
		<br class="clear" />
		
		<form id="email-form" action="" method="post">
			<input type="text" name="emailaddress" id="emailaddress" />
			<input type="text" name="honey" id="honey" />
			<input type="submit" name="submit" value="Keep Me Posted" />
		</form>
		<br class="clear" />
                
        <!--Begin Affiliates Section, "forgive my clumsy coding" [cccharle]-->
        <br>
		<div id="affiliates">
			<div id="about">
				<span>The founding partners of HABRI Central are Purdue Veterinary Medicine and Purdue University Libraries. While it is owned by Purdue University and editorially independent, generous initial funding for the project has been provided by the HABRI Foundation.</span>
			</div>
			<div id="logos">
				<a href="http://www.habri.org/"><img src="/templates/habricentral-landing/images/habrifoundation.png"></a>
				<a href="http://www.lib.purdue.edu/"><img src="/templates/habricentral-landing/images/purduelib.png"></a>
				<a href="http://www.vet.purdue.edu/"><img src="/templates/habricentral-landing/images/vetmed.png"></a>
			</div>
		</div>
		<br class="clear" />
        <!--End Affiliates Section-->

		<?php if(!$juser->get("guest")) { ?>
			<a href="/myhub" id="login">Go back to MyHUB</a>
		<?php } else { ?>
			<a href="/login" id="login">preview user login</a>
		<?php } ?>
	</div><!-- / #container -->
	<script type="text/javascript">
	 	 var _gaq = _gaq || [];
		 _gaq.push(['_setAccount', 'UA-25998614-1']);
		 _gaq.push(['_setDomainName', 'habricentral.org']);
		 _gaq.push(['_setAllowLinker', true]);
		 _gaq.push(['_trackPageview']);

		 (function() {
		 	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		 })();
	</script>
</body>
</html>
<?php
	$title = $this->getTitle();
	$this->setTitle( $config->getValue('config.sitename').' - '.$title );
?>