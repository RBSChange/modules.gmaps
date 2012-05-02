document.write('<script src="'+ window.location.protocol + '//maps.googleapis.com/maps/api/js?libraries=geometry&sensor=false" type="text/javascript"></script>');

jQuery(document).ready(function() {
	if (navigator.geolocation)
	{
		jQuery('.hidden-without-geolocation').removeClass('hidden-without-geolocation');
	}
});

/**
 * @param DOMNode mapNode
 * @param float latitude
 * @param float longitude
 * @param integer zoom
 * @return google.maps.Map
 */
function gmaps_initializeMap(mapNode, latitude, longitude, zoom)
{
	var latlng = new google.maps.LatLng(latitude, longitude);
	var myOptions = { zoom: zoom, center: latlng, mapTypeId: google.maps.MapTypeId.ROADMAP };
	return new google.maps.Map(mapNode, myOptions);
}

/**
 * @param google.maps.Map map
 * @param float latitude
 * @param float longitude
 * @param string label
 * @return google.maps.Marker
 */
function gmaps_addMarkerToMap(map, latitude, longitude, label, options)
{
	var latlng = new google.maps.LatLng(latitude, longitude);
	var moptions = { position: latlng, map: map, title: label };
	for (var i in options) { moptions[i] = options[i]; }
	return new google.maps.Marker(moptions);
}

/**
 * @param google.maps.Map map
 */
function gmaps_locateMeInMap(map, options)
{
	if (navigator.geolocation)
	{
		navigator.geolocation.getCurrentPosition(function (position) {
			gmaps_addMarkerToMap(map, position.coords.latitude, position.coords.longitude, "${trans:m.gmaps.fo.you-are-here,ucf,attr}", options);
		});
	}
}

/**
 * @param google.maps.Map map
 */
function gmaps_centerMapOnMe(map, callback)
{
	gmaps_centerMapOnMeCallback(map, null);
}

/**
 * @param google.maps.Map map
 * @param function callback
 */
function gmaps_centerMapOnMeCallback(map, callback)
{
	if (navigator.geolocation)
	{
		navigator.geolocation.getCurrentPosition(function (position) {
			var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			map.setCenter(latlng);
			if (callback !== null) { callback(); }
		});
	}
}