jQuery(document).ready(function() {
    jQuery('.first-start-time').change(function(){
        showFirstClinicSelector();
    });
    jQuery('.first-start-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showFirstClinicSelector();
        }
    });

    jQuery('.first-end-time').change(function(){
        showFirstClinicSelector();
    });
    jQuery('.first-end-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showFirstClinicSelector();
        }
    });

    jQuery('.second-start-time').change(function(){
        showSecondClinicSelector();
    });
    jQuery('.second-start-time').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showSecondClinicSelector();
        }
    });

    jQuery('.second-end-time').change(function(){
        showSecondClinicSelector();
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

    jQuery('.first-start-time-weekends').change(function(){
        showFirstWeekendsClinicSelector();
    });
    jQuery('.first-start-time-weekends').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showFirstWeekendsClinicSelector();
        }
    });

    jQuery('.first-end-time-weekends').change(function(){
        showFirstWeekendsClinicSelector();
    });
    jQuery('.first-end-time-weekends').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showFirstWeekendsClinicSelector();
        }
    });

    jQuery('.second-start-time-weekends').change(function(){
        showSecondWeekendsClinicSelector();
    });
    jQuery('.second-start-time-weekends').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showSecondWeekendsClinicSelector();
        }
    });

    jQuery('.second-end-time-weekends').change(function(){
        showSecondWeekendsClinicSelector();
    });
    jQuery('.second-end-time-weekends').clockpicker({
        afterDone: function() {
            // Если не пусты оба поля, то открываем выбор клиник по дням недели
            showSecondWeekendsClinicSelector();
        }
    });

    function showFirstWeekendsClinicSelector() {
        if (jQuery('.first-start-time-weekends').val() == '' || jQuery('.first-end-time-weekends').val() == '') {
            jQuery('.first-schedule-weekends-block .clinic-select-wrapper').css('display', 'none');
        } else {
            jQuery('.first-schedule-weekends-block .clinic-select-wrapper').css('display', 'block');
        }
    }

    function showSecondWeekendsClinicSelector() {
        if (jQuery('.second-start-time-weekends').val() == '' || jQuery('.second-end-time-weekends').val() == '') {
            jQuery('.second-schedule-weekends-block .clinic-select-wrapper').css('display', 'none');
        } else {
            jQuery('.second-schedule-weekends-block .clinic-select-wrapper').css('display', 'block');
        }
    }
});