<?php
// No direct access
defined('_HZEXEC_') or die();
?>
<div class="pftab">

	<div class="pf-opt-group">
		<div class="rss-box ">
			<h3 class="hndle"><span>Subscribe to Feeds</span></h3>
			<div class="inside">
				<div>Add Single Feed (RSS or Atom)</div>
				<div class="pf_feeder_input_box">
					<input id="pf_feedlist[single]" class="regular-text pf_primary_media_opml_url" type="text" name="pf_feedlist[single]" value="">
					<label class="description" for="pf_feedlist[single]">*Complete URL path</label>
					<a href="http://en.wikipedia.org/wiki/RSS">What is an RSS Feed?</a>
				</div>

				<div>Add OPML File</div>
				<div class="pf_feeder_input_box">
					<input id="pf_feedlist[opml]" class="pf_opml_file_upload_field regular-text" type="text" name="pf_feedlist[opml]" value="">
					<label class="description" for="pf_feedlist[opml]">*Drop link to OPML here. No HTTPS allowed.</label>
					or <a class="button-primary pf_primary_media_opml_upload">Upload OPML file</a>

					<p>&nbsp;Adding large OPML files may take some time.</p>
					<a href="http://en.wikipedia.org/wiki/Opml">What is an OPML file?</a>
				</div>
				<!-- <input type="submit" class="button-primary" value="Save Options" /> -->
			</div>
		</div>
	</div>

</div>