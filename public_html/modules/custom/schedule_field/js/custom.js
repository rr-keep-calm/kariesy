jQuery(document).ready(function() {
    jQuery('.first-start-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showFirstClinicSelector();
        }
    });
    jQuery('.first-end-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showFirstClinicSelector();
        }
    });
    jQuery('.second-start-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showSecondClinicSelector();
        }
    });
    jQuery('.second-end-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showSecondClinicSelector();
        }
    });

    function showFirstClinicSelector() {
        if (jQuery('.first-start-time').val() == '' || jQuery('.first-end-time').val() == '') {
            jQuery('.first-schedule-block .clinic-select-wrapper').css('display', 'none');
        } else {
            jQuery('.first-schedule-block .clinic-select-wrapper').css('display', 'block');
        }
    }

    function showSecondClinicSelector() {
        if (jQuery('.second-start-time').val() == '' || jQuery('.second-end-time').val() == '') {
            jQuery('.second-schedule-block .clinic-select-wrapper').css('display', 'none');
        } else {
            jQuery('.second-schedule-block .clinic-select-wrapper').css('display', 'block');
        }
    }
});