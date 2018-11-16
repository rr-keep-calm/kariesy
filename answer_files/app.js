app.addModule('all-clinic', function () {
	this.init = function () {
		$('.all-clinic_carts-clone').append(
			$('.all-clinic_carts').clone(true)
		);
	};
});
app.addModule('rating', function () {
	this.init = function () {
		try {
			$('.rating').barrating({
				theme: 'css-stars',
				allowEmpty: true
			});
		} catch (e) {}
	};
});
app.addModule('clinic', function () {
	this.init = function () {
		$('.clinic_slider').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			adaptiveHeight: true
		});
	};
});
app.addModule('clinics', function () {
	var city = $('.js-city');
	var cities = $('.js-cities');
	var maps = $('.js-map');
	
	this.init = function () {
		openCitiesEvent();
		changeMapEvent();
	};
	
	function openCitiesEvent() {
		city.click(function () {
			cities.toggleClass('active');
		});
	}
	
	function changeMapEvent() {
		$('.clinics_cities a').click(function (e) {
			e.preventDefault();
			city.html($(this).html());
			closeCities();
			changeMap( $( $(this).attr('href') ) );
			reloadMaps();
		});
		
		$(document).click(function (e) {
			if ($(e.target).closest(city).length) {
				return;
			}
			
			if (!$(e.target).closest(cities).length) {
				closeCities();
			}
		});
	}
	
	function closeCities() {
		cities.removeClass('active');
	}
	
	function changeMap(map) {
		maps.removeClass('active');
		map.addClass('active');
	}
});
app.addModule('doctor', function () {
	var images  = $('.doctor_images');
	var plan = $('.doctor_plan');
	var slickCreated = false;
	
	this.init = function () {
		plan.append(
			plan.clone().addClass('__cloned')
		);
		
		$('.doctor_more').click(function () {
			$('.doctor_list li').addClass('__mobile-visible');
			$(this).remove();
		});
		
		doSlick();
		
		$(window).on('resize', function () {
			doSlick();
		});
	};
	
	
	function createSlick() {
		if (slickCreated) {
			return false;
		}
		
		images.slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			
			responsive: [
				{
					breakpoint: 767,
					settings: {
						slidesToShow: 2
					}
				},
				{
					breakpoint: 480,
					settings: {
						slidesToShow: 1
					}
				},
			]
		});
		slickCreated = true;
	}
	
	function removeSlick() {
		if (!slickCreated) {
			return false;
		}
		
		images.slick('unslick');
		
		slickCreated = false;
	}
	
	function doSlick() {
		if ($(window).width() < 1220) {
			createSlick();
		} else {
			removeSlick();
		}
	}
});
app.addModule('doctors', function () {
	this.init = function () {
		$('.doctors_more').click(function (e) {
			e.preventDefault();
			$(this).detach();
			$('.doctors_item').addClass('__mobile-visible');
		});
	};
});
app.addModule('form', function () {
	this.init = function () {
		if ($.fn.datepicker) {
			var day = new Date();
			
			if (day.getDay() === 0) {
				day = tomorrow(day);
			} else if (day.getDay() === 6) {
				day = tomorrow(tomorrow(day));
			}
			
			$('.date').datepicker({
				language: "ru",
				autoclose:true,
				daysOfWeekDisabled: [0,6],
				startDate: new Date()
			}).datepicker('setDate', day);
		}
	};
	
	function tomorrow(date) {
		return new Date(date.getTime() + 24 * 60 * 60 * 1000);
	}
});
var map1 = {
	id: 'map-1',
	zoom: 14,
	markers: [
		{
			lat: 55.753913,
			lng: 37.603319,
			
			content: '<p>Большая Никитская ул., 3</p>'
		},
		{
			lat: 55.755474,
			lng: 37.610123,
			
			content: '<p>Большая Никитская ул., 3</p>'
		}
	]
};

var map2 = {
	id: 'map-2',

	zoom: 14,
	markers: [
		{
			lat: 56.297379,
			lng: 43.926924,

			content: '<p>Большая Никитская ул., 3</p>'
		},
		{
			lat: 55.755474,
			lng: 37.610123,

			content: '<p>Большая Никитская ул., 3</p>'
		}
	]
};

var map3 = {
	id: 'map-3',
	zoom: 14,
	markers: [
		{
			lat: 55.753913,
			lng: 37.603319,

			content: '<p>Большая Никитская ул., 3</p>'
		},
		{
			lat: 55.755474,
			lng: 37.610123,

			content: '<p>Большая Никитская ул., 3</p>'
		}
	]
};

var map4 = {
	id: 'map-4',
	zoom: 5,
	center: {
		lat: 54.391143,
		lng: 41.561887
	},
	markers: [
		{
			lat: 55.888526,
			lng: 37.558169,

			content: '' +
			'<h3 class="__no-offset">Москва</h3>' +
			'<p>3 клиники</p>'
		},
		{
			lat: 56.344310,
			lng: 43.920870,

			content: '' +
			'<h3 class="__no-offset">Нижний Новгород</h3>' +
			'<p>3 клиники</p>'
		},
		{
			lat: 51.836865,
			lng: 39.169186,

			content: '' +
			'<h3 class="__no-offset">Воронеж</h3>' +
			'<p>3 клиники</p>'
		}
	]
};

var map5 = jQuery.extend({}, map4);
map5['id'] = 'map-5';

var map6 = {
	id: 'map-6',
	zoom: 14,
	center: $('#map-6').attr('data-center'),
	markers: [
		{
			placeholder: $('#map-6').attr('data-placeholder'),
			content: 
			'<div class="map_content">' +
				'<h4>Адрес</h4>' +
				'<p>' + $('#map-6').attr('data-address') + '</p>' +
				'<a href="' + $('#map-6').attr('data-link') + '">Как проехать</a>' +
			'</div>'
		}
	]
};

if ($(window).width() < 768) {
	delete map6.center;
}


var mapsData = [map1, map2, map3, map4, map5, map6];

var maps = [];
function initMap() {
	mapsData.forEach(function (map) {
		var domElement = document.getElementById(map.id);
		
		if (!domElement) {
			return false;
		}
		
		var center;
		
		if (map.center) {
			if (typeof map.center == 'string') {
				center = getPlaceholderFromString(map.center);
			} else {
				center = map.center;
			}
		} else {
			if (map.markers[0].placeholder) {
				var position = getPlaceholderFromString(map.markers[0].placeholder);
				center = {
					lat: position.lat,
					lng: position.lng
				};
			}
			else {
				center = {
					lat: map.markers[0].lat,
					lng: map.markers[0].lng
				};
			}
		}

		var currentMap = new google.maps.Map(domElement, {
			zoom: map.zoom,
			center: center,
			gestureHandling: 'greedy',
			scrollwheel: false
		});
		
		maps.push(currentMap);

		google.maps.event.addListenerOnce(currentMap, 'idle', function () {
			afterMapsInitialized();
		});
		
		map.markers.forEach(function (marker) {
			if (marker.placeholder) {
				var position = getPlaceholderFromString(marker.placeholder);
				marker.lat = position.lat;
				marker.lng = position.lng;
			}
			
			var currentMarker = new google.maps.Marker({
				map: currentMap,
				position: {
					lat: marker.lat,
					lng: marker.lng
				},
				icon: {
					url: 'img/ico/map.svg',
					size: new google.maps.Size(50, 53)
				}
			});
			
			if (marker.content) {
				var infowindow = new google.maps.InfoWindow({
					content: getContent(marker.content)
				});

				google.maps.event.addListener(infowindow, 'domready', function () {
					var iwOuter = $('.gm-style-iw');
					var iwBackground = iwOuter.prev();
					iwBackground.children(':nth-child(2)').css({'display': 'none'});
					iwBackground.children(':nth-child(4)').css({'display': 'none'});
					
					iwBackground.children(':nth-child(3)').find('div').children().css({'box-shadow': 'none', 'z-index' : '1'});

					var iwCloseBtn = iwOuter.next();

					iwCloseBtn.css({
						opacity: 0
					});
				});

				currentMarker.addListener('click', function () {
					
					if (isInfoWindowOpen(infowindow)) {
						infowindow.close(map, currentMarker);
					} else {
						infowindow.open(map, currentMarker);
					}
				});
			}
		});
	});
}

function isInfoWindowOpen(infoWindow){
    var map = infoWindow.getMap();
    return (map !== null && typeof map !== "undefined");
}

function getContent(content) {
	return '<div class="map">' + content + '</div>';
}

function afterMapsInitialized() {	
	hooks['maps'].forEach(function (hook) { 
		hook();
	});
}
function reloadMaps() {
	maps.forEach(function (map) {
		google.maps.event.trigger(map, 'resize');
	})
}
function getPlaceholderFromString(placeholder) {
	var arr = placeholder.split(',');
	
	return {
		lat: parseFloat(arr[0]),
		lng: parseFloat(arr[1])
	}
}
app.addModule('main', function () {
	this.init = function () {
		createSlider();
	};
	
	function createSlider() {
		if (!$.fn.slick) {
			return;
		}
		
		$('.main_slider').slick({
			slidesToScroll: 1,
			slidesToShow: 1,
			appendArrows: '.main_arrows',
			autoplay: true,
			autoplaySpeed: 5000
		});
	}
});
app.addModule('mask', function () {
	this.init = function () {
		$('input[type=tel]').inputmask("+7 999 999 99 99");
	};
});
app.addModule('menu', function () {
	var menu = $('.menu');
	var isActive = false;
	
	this.init = function () {
		fillMenu();
		toggleMenuEvent();
	};
	
	function fillMenu() {
		$('[data-menu-id]').each(function () {
			var element = $($(this).attr('data-menu-id'));
			
			if (element.length) {
				$(this).append(element.clone(true, true));
			}
		});
	}
	
	function toggleMenuEvent() {
		$('.header_mobile-nav').click(function (e) {
			e.preventDefault();
			menu.addClass('active');
			isActive = true;
		});
		
		$('.menu_close').click(function (e) {
			e.preventDefault();
			menu.removeClass('active');
			isActive = false;
		});
	}
});
app.addModule('nav', function () {
	var navSearch  = $('.nav_search');
	
	this.init = function () {
		navSearchSubmitEvent();
	};
	
	function navSearchSubmitEvent() {
		var active = false;
		
		navSearch.on('submit', function (e) {
			if (!active) {
				e.preventDefault();
				$(this).find('.nav_input').get(0).focus();
			}
			
			$(this).toggleClass('active');
			
			active = !active;
		});
		
		$(document).click(function (e) {
			if ( !$(e.target).closest(navSearch).length ) {
				navSearch.removeClass('active');
				active = false;
			}
		});
	}
});
app.addModule('page', function () {
	this.init = function () {
		$('.page table').each(function () {
			$(this).wrap('<div class="page_table"></div>');
		});
	};
});
app.addModule('popup', function () {
	this.init = function () {
		initPopups();
	};
	
	function initPopups() {
		try {
			$('.popup').magnificPopup({
				preloader: false,
				showCloseBtn: false,
				removalDelay: 300,
				mainClass: 'mfp-fade',
				callbacks: {
					beforeOpen: function () {
						$('html').addClass('hidden');
					},
					afterClose: function () {
						$('html').removeClass('hidden');
					}
				}
			});
			$('.popup-image').magnificPopup({
				preloader: false,
				showCloseBtn: false,
				removalDelay: 300,
				mainClass: 'mfp-fade',
				type: 'image'
			});
		}
		catch(e) {}
		
		$('.popup-close').click(function (e) {
			closeForm();
		});
	}
});
app.addModule('price', function () {
	this.init = function () {
		if (!$('.price').length) return false;
		
		createTypesSelect();
		
		$('.price_header').click(function () {
			$(this).closest('.price_block').toggleClass('active');
		});
	};
	
	function createTypesSelect() {
		var select = $('<select />');
		
		$('.price_types a').each(function () {
			var option = $('<option />');
			option.val($(this).attr('href'));
			option.html($(this).html());
			
			if ($(this).hasClass('active')) {
				option.attr('selected', 'selected');
			}
			
			select.append(option);
		});
		
		select.on('change', function () {
			location.href = this.value;
		});
		
		$('.price_select').append(select);
	}
});
app.addModule('reviews', function () {
	this.init = function () {
		reviewsSortEvent();
	};

	function reviewsSortEvent() {
		$('.reviews_sort-link').click(function (e) {
			e.preventDefault();
			$('.reviews_sort-items').toggleClass('active');
		});

		$(document).click(function (e) {
			if (!$(e.target).closest('.reviews_sort-container').length) {
				$('.reviews_sort-items').removeClass('active');
			}
		});
	}
});
app.addModule('select', function () {
	this.init = function () {
		try {
			$('select:not(.no-select)').select2({
				minimumResultsForSearch: -1,
				dropdownAutoWidth: true,
				width: 'auto'
			});
		} catch (e) {
		}
	};
});
app.addModule('service-detail', function () {
	this.init = function () {
		$('.service-detail_read-more').click(function (e) {
			e.preventDefault();
			
			$('.service-detail_mobile').removeClass('service-detail_mobile');
			$(this).remove();
		})
	};
});
app.addModule('service', function () {
	this.init = function () {
		$('.service_show-btn').click(function (e) {
			e.preventDefault();
			$(this).detach();
			$('.service_item').addClass('__mobile-visible');
		});
		
		$('.service_item').hover(mouseenter, mouseleave);
		
		function mouseenter() {
			var object = $(this).find('object').get(0);
			
			addSvgClass(object, '.st0', 'active');
		}
		
		function mouseleave() {
			var object = $(this).find('object').get(0);
			
			removeSvgClass(object, '.st0', 'active');
		}
	};
});
var hooks = {
	maps: []
};

jQuery(function () {
	app.modulesInit();
	
	var modules = app.getModules();

	for (var module in modules) {
		app.callModule(module);
	}
});