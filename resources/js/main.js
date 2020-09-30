const IMAGE_FILTER = ['image/jpg', 'image/jpeg', 'image/png', 'image/bmp', 'image/gif'];
const VIDEO_FILTER = ['video/mp4', 'video/webm', 'video/ogg'];
const DEFAULT_LAT = 30.0120306;
const DEFAULT_LNG = 31.3000488;
var marker, map = null;


function warnUser()
{
	return confirm('Are you sure?');
}

function mapResize()
{
	if(!map) return;
	
	var has_pos = true;
	const name = document.getElementById('map_name').value.trim();
	var latitude = document.getElementById('map_latitude').value;
	var longitude = document.getElementById('map_longitude').value;
	
	if(latitude == '' || longitude == '')
	{
		latitude = DEFAULT_LAT;
		longitude = DEFAULT_LNG;
		has_pos = false;
	}
	
	latitude = parseFloat(latitude);
	longitude = parseFloat(longitude);
	
	google.maps.event.trigger(map, 'resize');
	map.setCenter({lat: latitude, lng: longitude});
	if(has_pos) pinOnMap(marker, name, latitude, longitude);
}

function initMap()
{
	map = new google.maps.Map(document.getElementById('map_container'), {
		center: {lat: DEFAULT_LAT, lng: DEFAULT_LNG},
		zoom: 10
	});
		
	marker = new google.maps.Marker({
		map: map,
		position: null,
		draggable: true
	});
	
	map.addListener('click', function(event)
	{
		const name = document.getElementById('map_name').value.trim();
		pinOnMap(marker, name, event.latLng.lat(), event.latLng.lng());
	});
	
	map.addListener('rightclick', function(event)
	{
		const name = document.getElementById('map_name').value.trim();
		pinOnMap(marker, name, event.latLng.lat(), event.latLng.lng());
	});
	
	marker.addListener('position_changed', function()
	{
		const latLng = marker.getPosition();
		document.getElementById('map_latitude').value = latLng.lat();
		document.getElementById('map_longitude').value = latLng.lng();
	});
}

function pinOnMap(curr_marker, title, latitude, longitude)
{
	const latLng = new google.maps.LatLng({
		lat: parseFloat(latitude),
		lng: parseFloat(longitude)
	});
	
	curr_marker.setTitle(title);
	curr_marker.setPosition(latLng);
	curr_marker.setAnimation(google.maps.Animation.DROP);
}

function coordinateChange()
{
	var latitude = document.getElementById('map_latitude').value;
	var longitude = document.getElementById('map_longitude').value;
	
	if(latitude == '' || longitude == '') return;
	
	const latLng = new google.maps.LatLng({
		lat: parseFloat(latitude),
		lng: parseFloat(longitude)
	});
	
	marker.setPosition(latLng);
}

function showMedia(elem, gallery_div)
{
	gallery_div = document.getElementById(gallery_div);
	gallery_div.innerHTML = '';
	
	var i, path, name, type, size, content;
	
	for(i = 0; i < elem.files.length; i++)
	{
		path = window.URL.createObjectURL(elem.files[i]);
		type = elem.files[i].type;
		
		if(IMAGE_FILTER.indexOf(type) > -1)
		{
			content = `
				<a class="img-link img-thumb" href="`+ path +`">
					<img class="img-responsive" src="`+ path +`" />
				</a>
			`;
		}
		else if(VIDEO_FILTER.indexOf(type) > -1)
		{
			content = `
				<video class="img-responsive" controls>
					<source src="`+ path +`" type="`+ type +`" />
				</video>
			`;
		}
		else continue;
		
		name = elem.files[i].name.trim();
		size = elem.files[i].size / (1024 * 1024);
		size = parseFloat(size.toFixed(2));
		
		gallery_div.innerHTML += `
			<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 animated fadeIn">
				`+ content +`
				<div align="center">`+ name +` (`+ size +` MB)</div>
			</div>
		`;
	}
}

function lazyLoadMedia(class_name)
{
	var i;
	var elems = document.querySelectorAll('.'+ class_name);
	
	for(i = 0; i < elems.length; i++) elems[i].src = elems[i].dataset.src.trim();
}

function toggleCheckboxes(state, class_name)
{
	var inputs = document.querySelectorAll('input.' + class_name);

	var i;
	for (i = 0; i < inputs.length; i++) inputs[i].checked = state;
}

function addFormNotify(class_name)
{
	var i;
	var elems = document.querySelectorAll('form.'+ class_name);
	
	for(i = 0; i < elems.length; i++)
	{
		elems[i].addEventListener('submit', function()
		{
			fireNotification({
				message: 'Saving data in progress...',
				icon: 'fa fa-spinner fa-spin',
				type: 'success',
				delay: 0
			});
		});
	}
}

function fireNotification(data)
{
	$.notify({
		// options
		message: data.message,
		icon: data.icon
	}, {
		// settings
		type: data.type,
		delay: data.delay
	});
}