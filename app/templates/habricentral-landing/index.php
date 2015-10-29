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


//include the constant contact stuff
include("constant_contact/contact.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Introducing HABRI Central: A New Resource for the Study of the Human-Animal Bond</title>
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/habricentral-landing/css/main.css" />
		<!--[if IE 8]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/hubbasic/css/ie8.css" />
		<![endif]-->
		<!--[if lte IE 7]>
			<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl ?>/templates/hubbasic/css/ie7.css" />
		<![endif]-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script src="<?php echo $this->baseurl ?>/templates/habricentral-landing/js/landing.js"></script>
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
			<br />
			<div id="affiliates">
				<div id="about">
					<span>The founding partners of HABRI Central are Purdue Veterinary Medicine and Purdue University Libraries. While it is owned by Purdue University and editorially independent, generous initial funding for the project has been provided by the HABRI Foundation.</span>
				</div>
				<div id="logos">
					<a href="http://www.habri.org/"><img src="/app/templates/habricentral-landing/images/habrifoundation.png" alt="" /></a>
					<a href="http://www.lib.purdue.edu/"><img src="/app/templates/habricentral-landing/images/purduelib.png" alt="" /></a>
					<a href="http://www.vet.purdue.edu/"><img src="/app/templates/habricentral-landing/images/vetmed.png" alt="" /></a>
				</div>
			</div>
			<br class="clear" />
			<!--End Affiliates Section-->

			<?php if (!User::get("guest")) { ?>
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
	$this->setTitle(Config::get('sitename').' - '.$title);
