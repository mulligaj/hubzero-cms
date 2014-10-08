// JavaScript Document
var map;
var bounds;
var infoWindow;
var oms;
var markersCount = 0;
var markers = [];

var membersDisplayed = 0;
var eventsDisplayed  = 0;
var jobsDisplayed    = 0;
var orgsDisplayed    = 0;

/**
 * @package     hubzero-cms
 * @file        components/com_groups/assets/js/groups.jquery.js
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

//-----------------------------------------------------------
//  Ensure we have our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}

//----------------------------------------------------------
//  Members scripts
//----------------------------------------------------------
if (!jq) {
	var jq = $;
}

String.prototype.repeat = function( n )
{
	n= n || 1;
    return Array(n+1).join(this);
}

HUB.Geosearch = {
	jQuery: jq,
	
	initialize: function(latlng,uids,jids,eids,oids) 
	{
		var $ = this.jQuery;

		// show loading
		HUB.Geosearch.showLoader();

		var mapOptions = {
			scrollwheel: false,
			zoom: 2,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);
		bounds = new google.maps.LatLngBounds();
		infoWindow = new google.maps.InfoWindow();
		oms = new OverlappingMarkerSpiderfier(map, {keepSpiderfied:true});

		// create info windows
		oms.addListener('click', function(marker, event) {
			  infoWindow.setContent(marker.html);
			  infoWindow.open(map, marker);
		});

		// get markers
		this.loadMarkers();
	},

	loadMarkers: function()
	{
		var $ = this.jQuery;

		// get markers
		var checked = [];
		for (var x = 0; x < $(".resck:checked").length; x++) 
		{
			checked.push($(".resck:checked")[x].value);
		}

		// get tags
		var tags = $("#actags").val();

		// make request
		$.post("index.php?option=com_geosearch&task=getmarkers", {
			checked:checked,
			uids:uids,
			jids:jids,
			eids:eids,
			oids:oids,
			tags:tags
		},
		function(data)
		{	
			var markerNodes = data.getElementsByTagName("marker");
			markersCount = markerNodes.length;

			// no markers found
			if (markersCount == 0)
			{
				HUB.Geosearch.hideLoader();
				$('#map_results span').html('No results found');
			}
			
			for (var i = 0; i < markerNodes.length; i++) 
			{
				var uid = markerNodes[i].getAttribute("uid");
				var lat = parseFloat(markerNodes[i].getAttribute("lat"));
				var lng = parseFloat(markerNodes[i].getAttribute("lng"));
				var type = markerNodes[i].getAttribute("type");
				if (lat != 0 || lng != 0) 
				{
					var mlatlng = new google.maps.LatLng(lat,lng);
					HUB.Geosearch.createMarker(mlatlng, uid, type);

					// add marker to bounds
					bounds.extend(mlatlng);
				}
			}

			// fit map to bounds only if were doing a location search
			if ($('#idist').val() != 0 && $('#iloc').val() != '')
			{
				var center = bounds.getCenter();
				if (center.k != 0)
				{
					map.fitBounds(bounds);
				}
			}
		},"xml");
	},

	displayCheckedMarkers: function()
	{
		// get types to display
		var checked = [];
		for (var x = 0; x < $(".resck:checked").length; x++) 
		{
			checked.push($(".resck:checked")[x].value);
		}

		// reset counts
		membersDisplayed = 0;
		eventsDisplayed  = 0;
		jobsDisplayed    = 0;
		orgsDisplayed    = 0;

		// hide markers not wanting to be displayed
		for (var i = 0; i < markers.length; i++)
		{
			var marker = markers[i],
				markerType = marker.type + 's';

			// if we have it checked show marker
			if (checked.indexOf(markerType) > -1)
			{
				marker.setVisible(true);

				switch (markerType)
				{
					case 'jobs':
						jobsDisplayed++;
						break;
					case 'members':
						membersDisplayed++;
						break;
					case 'events':
						eventsDisplayed++;
						break;
					case 'orgs':
						orgsDisplayed++;
						break;
				}
			}
			else
			{
				marker.setVisible(false);
			}
		}

		var resultsString = membersDisplayed + ' members, ' + jobsDisplayed + ' jobs, ' + eventsDisplayed + ' events, and ' + orgsDisplayed + ' organizations';
		$('#map_results span').html(resultsString);
	},
	
	createMarker: function(mlatlng, uid, type)
	{
		var $ = this.jQuery;

		$.post("index.php?option=com_geosearch&task=getaddyxml",{uid:uid,type:type},function(data){	
			var profile = data.getElementsByTagName("profile")[0];
			var url = profile.getAttribute("url");
			var name = profile.getAttribute("name");
			var bio = profile.getAttribute("bio");
			switch (type) {
				case "member":
					type = 'member';
					var jid = profile.getAttribute("jid");
					var org = profile.getAttribute("org");
					var photo = profile.getAttribute("photo");
					var icon = "/components/com_geosearch/assets/img/icn_member.png";
					break;
				case "event":
					type = 'event';
					var start = profile.getAttribute("start");
					var end = profile.getAttribute("end");
					var tz = profile.getAttribute("tz");
					var icon = "/components/com_geosearch/assets/img/icn_event.png";
					break;
				case "job":
					type = 'job';
					var org = profile.getAttribute("org");
					var code = profile.getAttribute("code");
					var jobtype = profile.getAttribute("jobtype");
					var icon = "/components/com_geosearch/assets/img/icn_job.png";
					break;
				case "org":
					type = 'org';
					var org = profile.getAttribute("org");
					var icon = "/components/com_geosearch/assets/img/icn_org.png";
					break;
			}
			
			var html = "<div class=\"marker\">";
			if (type == "member") 
			{
				html += "<div class='member-img'><img src='"+photo+"' /></div>";
				//var plink = "/index.php?option=com_members&id="+uid;
			} 
			else 
			{
				if (type == "event") 
				{
					var plink = "/events/details/"+uid;
				}
				else if (type == "job") 
				{
					var plink = "/jobs/job/"+code
				} 
				else 
				{
					var plink = "/resources/"+uid;
				}
			}

			html += "<div class='marker-title'><a href='"+plink+"' title='"+name+"' target='_blank'>"+name+"</a></div>";

			if (type != "event") 
			{
				if (org != "") 
				{
					html += org + "<br />";
				}
				if (type == "job") 
				{
					html += jobtype + "<br />";
				}
			} 
			else 
			{
				html += start + " to " + end + "<br />";
			}

			if (url != "" && url != null) 
			{
				if (url.indexOf("http") < 0) { url = "http://"+url; }
				html += "<a href='"+url+"' title='"+org+"' target='_blank'>"+url+"</a>";
			}

			if (bio != null) 
			{
				html += "<div class=\"marker-bio\">"+bio+"</div>";
			}

			html += "<div id=\"marker-buttons\">"
			if (type == "member")
			{
				// if (jid != null) {
				// 	var mlink = "/members/"+jid+"/messages/new"; /*?to="+uid;*/	
				// } else {
				// 	var mlink = "/login";
				// }
				
				var profilelink = profile.getAttribute('profilelink');
				var messagelink = profile.getAttribute('messagelink');
				html += "<div class=\"button message\" onclick=\"window.open('"+messagelink+"')\"><div class=\"content\">Message</div></div>" 
				html += "<div class=\"button profile\" onclick=\"window.open('"+profilelink+"')\"><div class=\"content\">Profile</div></div>";
			} 
			else 
			{
				var link = profile.getAttribute('link');
				html += "<div class=\"button moreinfo\" onclick=\"window.open('"+link+"')\"><div class=\"content\">More Info</div></div>";
			}
			html += "</div>";
			html += "</div>";

			var marker = new google.maps.Marker({
				position: mlatlng, 
				map: map, 
				title: name,
				icon: icon,
				type: type
			});
			marker.html = html;
			oms.addMarker(marker);
			markers.push(marker);

			//check to see if markers fetch = markers added
			if (markers.length == markersCount)
			{
				HUB.Geosearch.hideLoader();
				// display checked
				HUB.Geosearch.displayCheckedMarkers();
			}
		},"xml");
	},

	showLoader: function()
	{
		var $ = this.jQuery;

		$('#map_container').append('<div class="loading"><span>Loading Map Markers</span></div>');
		var dotCount    = 0,
			dotString   = '.',
			loadingText = 'Loading Map Markers';

		setInterval(function(){
			if (dotCount < 4)
			{
				dotCount++
			}
			else
			{
				dotCount = 0;
			}
			$('.loading span').html(loadingText + dotString.repeat(dotCount));
		}, 500);
	},

	hideLoader: function()
	{
		var $ = this.jQuery;

		$('#map_container .loading').fadeOut('slow', function() {
			$(this).remove();
		});
	},
};

//-----------------------------------------------------------

jQuery(document).ready(function($){

	HUB.Geosearch.initialize(latlng,uids,jids,eids,oids);

	$("#clears").click(function() {
		$("#actags").tokenInput("clear");
		$("input[type=text]").val("");
	});

	$('.resck').on('click', function(event){
		HUB.Geosearch.displayCheckedMarkers();
	});
});
