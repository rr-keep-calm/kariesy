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
		$('.doctors-more').on('click', '.doctors-more_btn', function(e){
		  if ($(this).hasClass('roll-up')) {
        // Проходим по всем открытым строкам и скрываем все кроме первой
        $('.doctors-more .doctors-more_items').not(':first').hide();

        if ($('.doctors-more .doctors-more_items:hidden').length > 0) {
          $('.doctors-more .doctors-more_btn:not(.roll-up)').show();
          $('.doctors-more .roll-up').hide();
        }
      } else {
        // Проходим по всем скрытым строкам и открываем одну
        $('.doctors-more .doctors-more_items:hidden:first').show();

        // Убираем кнопку открытия дополнительных элементов, если скрытых больше не осталось.
        // Показываем кнопку "Свернуть"
        if ($('.doctors-more .doctors-more_items:hidden').length < 1) {
          $('.doctors-more .doctors-more_btn:not(.roll-up)').hide();
          $('.doctors-more .roll-up').css('display', 'block');
        }
      }
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
	var plan = $('.doctor_plan:not(.weekends)');
	var planWeekends = $('.doctor_plan.weekends');
	var slickCreated = false;

	this.init = function () {
		plan.append(
			plan.clone().addClass('__cloned')
		);
		planWeekends.append(
			planWeekends.clone().addClass('__cloned')
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

			$('.date').datepicker({
				language: "ru",
				autoclose:true,
				startDate: day
			}).datepicker('setDate', day);
		}
	};
});
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


var mapsData = [map2, map3, map6];

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
					url: '/' + drupalSettings.path.themeUrl + '/img/ico/map.svg',
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
						var eventLabel = $(this.items[this.index]).attr('data-eventLabel');
						var containerId = $(this.items[this.index]).attr('href');
						if (typeof eventLabel === typeof undefined || eventLabel === false) {
							eventLabel = '';
						}
						if (eventLabel != '') {
							$(containerId).find('form').attr('data-eventLabel', eventLabel);
						} else {
							$(containerId).find('form').attr('data-eventLabel', '');
						}
					},
          open: function(){
            $('body').on('touchmove', function (e) {
              e.preventDefault();
            });
          },
					afterClose: function () {
						var containerId = $(this.items[this.index]).attr('href');
						$(containerId).find('form').attr('data-eventLabel', '');
						$('html').removeClass('hidden');
					},
          resize: function() {
            let check = false;
            (function (a) {
              if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true;
            })(navigator.userAgent || navigator.vendor || window.opera);
            if (check) {
              $(this.wrap).css('top', window.pageYOffset);
            }
          },
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
