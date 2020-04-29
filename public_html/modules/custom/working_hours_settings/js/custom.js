jQuery(document).ready(function() {
    initClockPicker();
    initRangeDatePicker();
    jQuery('form').on('click', '.add-exception', function (event) {
      var dataIndexNumber = 1;
      if (jQuery('.period-range-time-wrapper .one-peiod-wrapper').length > 0) {
        dataIndexNumber = jQuery('.period-range-time-wrapper .one-peiod-wrapper').last().attr('data-index_number');
        dataIndexNumber++;
      }
      let add_exceptions = "<div class=\"one-peiod-wrapper\" data-index_number=\"" + dataIndexNumber + "\">\n" +
        "    <div class=\"remove-period\">x</div> \n" +
        "    <input type=\"text\" class=\"period-date-picker\" name=\"exception-period-" + dataIndexNumber + "-dates\"> -\n" +
        "    <input type=\"text\" class=\"clock-picker\" name=\"exception-period-" + dataIndexNumber + "-start\"> :\n" +
        "    <input type=\"text\" class=\"clock-picker\" name=\"exception-period-" + dataIndexNumber + "-end\">\n" +
        "  </div>";
      jQuery('.period-range-time-wrapper').append(add_exceptions);
      initClockPicker();
      initRangeDatePicker();
    });
    jQuery('form').on('click', '.remove-period', function (event) {
      jQuery(jQuery(this).parent('.one-peiod-wrapper')).remove();
    });

    function initClockPicker() {
      jQuery('.clock-picker').clockpicker({
        donetext: 'Установить',
        autoclose: true
      });
    }

    function initRangeDatePicker() {
      if (jQuery('.period-date-picker').length > 0) {
        jQuery('.period-date-picker').daterangepicker({
          locale: {
            format: 'DD.MM.YYYY',
            "daysOfWeek": [
              "Вс",
              "Пн",
              "Вт",
              "Ср",
              "Чт",
              "Пт",
              "Сб"
            ],
            "monthNames": [
              "Январь",
              "Февраль",
              "Март",
              "Апрель",
              "Май",
              "Июнь",
              "Июль",
              "Август",
              "Сентябрь",
              "Октябрь",
              "Ноябрь",
              "Декабрь"
            ],
            "firstDay": 1
          }
        });
      }
    }
});
