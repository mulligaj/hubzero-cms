<?php
// No direct access
defined('_HZEXEC_') or die();

$href = rtrim(Request::root(), '/') . '/pressforward/?pf=opml';
?>
<div class="pftab">
	<p>You can share your subscription list as an OPML file by linking people to <a rel="external" href="<?php echo $href; ?>"><?php echo $href; ?></a>.</p>
</div>