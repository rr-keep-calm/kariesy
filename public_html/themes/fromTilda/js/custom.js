$(document).ready(function () {
    if ($(".price").length) {
        $(".price").tabs();
        $(".price_select").on('change', '.select2-hidden-accessible', function () {
            var valueSelected = this.value;
            $('.price_content').hide();
            $('.price_content' + valueSelected).show();
        })
    }

    // Если мы на главной странице и есть текст больше одного абзаца
    if ($('.home-text.page .wrapper').length && $('.home-text.page .wrapper').children().length <= 2) {
        $('.home-text.page .wrapper .pagination-block_more').hide();
    }
    $('.home-text.page .wrapper').on('click', '.pagination-block_more.open', function () {
        $(this).removeClass('open');
        $(this).addClass('close');
        $(this).text('Свернуть');
        $.each($('.home-text.page .wrapper').children(), function () {
            if (!$(this).hasClass('pagination-block_more')) {
                $(this).show('slow');
            }
        });
    });
    $('.home-text.page .wrapper').on('click', '.pagination-block_more.close', function () {
        $(this).removeClass('close');
        $(this).addClass('open');
        $(this).text('Развернуть');
        var i = 0;
        $.each($('.home-text.page .wrapper').children(), function () {
            if (!$(this).hasClass('pagination-block_more') && i != 0 && i != 1) {
                $(this).hide('slow');
            }
            i++;
        });
    });

    // Сортировка на странице всех отзывов срабатывает сразу же при смене значения
    $('#views-exposed-form-reviews-page-1 select').change(function () {
        $('#views-exposed-form-reviews-page-1').submit();
    });

    if ($('.ya-map-container').length > 0) {
        var mapsData = {};
        // Обходим все контейнеры предназначенные для отображения карт и собираем данные
        $('.ya-map-container').each(function () {
            let id = $(this).attr('id');
            mapsData[id] = {};

            var center = $(this).attr('data-ym_center');
            if (typeof center !== typeof undefined && center !== false) {
                mapsData[id]['center'] = center;
            }

            var coords = $(this).attr('data-ym_coords');
            if (typeof coords !== typeof undefined && coords !== false) {
                coords = coords.split('|||');
                mapsData[id]['coords'] = coords;
            }

            var balloonContent = $(this).attr('data-ym_balloon_content');
            if (typeof balloonContent !== typeof undefined && balloonContent !== false) {
                balloonContent = balloonContent.split('|||');
                mapsData[id]['balloons'] = balloonContent;
            }
        });

        // Если есть контейнеры с данными для формирования карт, то передаём работу в генератор
        if (Object.keys(mapsData).length > 0) {
            checkYamapsReady(mapsData);
        }
    }

    // Работа с формой записи на приём в части даты и времени
    // Фильтруем доступные на старте временные рамки для записи
    if ($('.time_intervals').length > 0) {
      const today = new Date();
      const nowHour = today.getUTCHours() + 3;
      const nowMinutes =  today.getUTCMinutes();
      $('.time_intervals option').each(function() {
        let timeMark = $(this).text().split(':');
        if (parseInt(timeMark[0]) < nowHour) {
          $(this).remove();
        }
        if (parseInt(timeMark[1]) < nowMinutes) {
          $(this).remove();
        }
      });
    }
});

var jivo_onLoadCallback = function () {
    var setTokenForJivo = '';
    var credentials = Comagic.getCredentials();
    for (var field in credentials) {
        if (credentials.hasOwnProperty(field)) {
            setTokenForJivo += field + '---' + credentials[field] + '|||';
        }
    }
    jivo_api.setUserToken(setTokenForJivo);
};

function checkYamapsReady(mapsData) {
    if (typeof ymaps !== typeof undefined) {
        ymaps.ready(generateYaMaps(mapsData));
    } else {
        window.setTimeout(function () {
            checkYamapsReady(mapsData);
        }, 100);
    }
}

function generateYaMaps(mapsData) {
    return function () {
        var generalMarkOptions = {
            iconLayout: 'default#image',
            iconImageHref: '/themes/fromTilda/img/ico/location.png',
            iconImageSize: [50, 52],
            iconImageOffset: [-25, -52]
        };

        // Генерируем карту для каждого элемента контейнера
        for (var key in mapsData) {
            // Если нет координат то генерировать нечего
            if ('coords' in mapsData[key] === false || mapsData[key]['coords'].length <= 0) {
                continue;
            }

            // Если центр задан то берём его значение.
            // В противном случае центром будут являться первые координаты из набора.
            // Это нужно для инициализации, так как при наличии набора координат центр будет определён автоматически
            var center = '';
            var forceCenter = false;
            if ('center' in mapsData[key]) {
                center = mapsData[key]['center'];
                forceCenter = true;
            } else {
                center = mapsData[key]['coords'][0];
            }

            var map = new ymaps.Map(key, {center: center.split(','), zoom: 13});
            collection = new ymaps.GeoObjectCollection();

            // Создаём метки для отображения на карте
            for (var i = 0; i < mapsData[key]['coords'].length; i++) {
                let placeMarkOptions = {};
                if (typeof mapsData[key]['balloons'][i] !== typeof undefined && mapsData[key]['balloons'][i] !== false && mapsData[key]['balloons'][i] !== "") {
                    placeMarkOptions.balloonContent = mapsData[key]['balloons'][i];
                }
                let placemark = new ymaps.Placemark(mapsData[key]['coords'][i].split(','), placeMarkOptions, generalMarkOptions);
                collection.add(placemark);
            }

            map.geoObjects.add(collection);

            if (mapsData[key]['coords'].length > 1) {
                map.setBounds(collection.getBounds());
            }

            if (forceCenter) {
                map.setCenter(center.split(','));
            }

            map.setZoom(13);
        }
    }
}

$.fn.once = function(processed_class)
{
    if (typeof processed_class == 'undefined')
    {
        processed_class = 'processed';
    }
    return this.not('.' + processed_class).addClass(processed_class);
};
