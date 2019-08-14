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
      const nowDay = today.getDay();
      $('.time_intervals option').each(function() {
        let timeMark = $(this).text().split(':');
        if (parseInt(timeMark[0]) < nowHour) {
          $(this).remove();
          return true;
        }
        if (parseInt(timeMark[1]) < nowMinutes) {
          $(this).remove();
          return true;
        }
        if ((nowDay === 0 || nowDay === 6) && (parseInt(timeMark[0]) < 10 || parseInt(timeMark[0]) > 17)) {
          $(this).remove();
        }
      });

      // Если после фильтрации временных интервалов не осталось, то переставляем дату на слудующую и заполняем временные
      // интервалы заново
      if (!$('.time_intervals option').length) {
        const tomorrow = new Date(today.getTime() + 24 * 60 * 60 * 1000);
        let dateInput = $('.time_intervals').closest('.form_row');
        dateInput = $(dateInput).find('.date');
        $(dateInput).datepicker('setDate', tomorrow);
        $(dateInput).datepicker('setStartDate', tomorrow);
        const allDaysIntervals = '<option value="10:00">10:00</option>\n' +
          '<option value="10:10">10:10</option>\n' +
          '<option value="10:20">10:20</option>\n' +
          '<option value="10:30">10:30</option>\n' +
          '<option value="10:40">10:40</option>\n' +
          '<option value="10:50">10:50</option>\n' +
          '<option value="10:00">10:00</option>\n' +
          '<option value="11:10">11:10</option>\n' +
          '<option value="11:20">11:20</option>\n' +
          '<option value="11:30">11:30</option>\n' +
          '<option value="11:40">11:40</option>\n' +
          '<option value="11:50">11:50</option>\n' +
          '<option value="12:00">12:00</option>\n' +
          '<option value="12:10">12:10</option>\n' +
          '<option value="12:20">12:20</option>\n' +
          '<option value="12:30">12:30</option>\n' +
          '<option value="12:40">12:40</option>\n' +
          '<option value="12:50">12:50</option>\n' +
          '<option value="13:00">13:00</option>\n' +
          '<option value="13:10">13:10</option>\n' +
          '<option value="13:20">13:20</option>\n' +
          '<option value="13:30">13:30</option>\n' +
          '<option value="13:40">13:40</option>\n' +
          '<option value="13:50">13:50</option>\n' +
          '<option value="14:00">14:00</option>\n' +
          '<option value="14:10">14:10</option>\n' +
          '<option value="14:20">14:20</option>\n' +
          '<option value="14:30">14:30</option>\n' +
          '<option value="14:40">14:40</option>\n' +
          '<option value="14:50">14:50</option>\n' +
          '<option value="15:00">15:00</option>\n' +
          '<option value="15:10">15:10</option>\n' +
          '<option value="15:20">15:20</option>\n' +
          '<option value="15:30">15:30</option>\n' +
          '<option value="15:40">15:40</option>\n' +
          '<option value="15:50">15:50</option>\n' +
          '<option value="16:00">16:00</option>\n' +
          '<option value="16:10">16:10</option>\n' +
          '<option value="16:20">16:20</option>\n' +
          '<option value="16:30">16:30</option>\n' +
          '<option value="16:40">16:40</option>\n' +
          '<option value="16:50">16:50</option>\n' +
          '<option value="17:00">17:00</option>\n' +
          '<option value="17:10">17:10</option>\n' +
          '<option value="17:20">17:20</option>\n' +
          '<option value="17:30">17:30</option>\n' +
          '<option value="17:40">17:40</option>\n' +
          '<option value="17:50">17:50</option>';
        const weekdaysDaysIntervalsBefore = '<option value="09:00">09:00</option>\n' +
          '<option value="09:10">09:10</option>\n' +
          '<option value="09:20">09:20</option>\n' +
          '<option value="09:30">09:30</option>\n' +
          '<option value="09:40">09:40</option>\n' +
          '<option value="09:50">09:50</option>';
        const weekdaysDaysIntervalsAfter = '<option value="18:00">18:00</option>' +
          '<option value="18:10">18:10</option>\n' +
          '<option value="18:20">18:20</option>\n' +
          '<option value="18:30">18:30</option>\n' +
          '<option value="18:40">18:40</option>\n' +
          '<option value="18:50">18:50</option>\n' +
          '<option value="19:00">19:00</option>\n' +
          '<option value="19:10">19:10</option>\n' +
          '<option value="19:20">19:20</option>\n' +
          '<option value="19:30">19:30</option>\n' +
          '<option value="19:40">19:40</option>\n' +
          '<option value="19:50">19:50</option>\n' +
          '<option value="20:00">20:00</option>\n' +
          '<option value="20:10">20:10</option>\n' +
          '<option value="20:20">20:20</option>\n' +
          '<option value="20:30">20:30</option>\n' +
          '<option value="20:40">20:40</option>\n' +
          '<option value="20:50">20:50</option>';
        $('.time_intervals').append(allDaysIntervals);
        const tomorrowDay = tomorrow.getDay();
        $('.time_intervals').val('10:00');
        if (tomorrowDay !== 0 && tomorrowDay !== 6) {
          $('.time_intervals').prepend(weekdaysDaysIntervalsBefore);
          $('.time_intervals').append(weekdaysDaysIntervalsAfter);
          $('.time_intervals').val('09:00');
        }
      }
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
