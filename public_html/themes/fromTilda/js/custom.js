$(document).ready(function() {
    $('.price_item .price_button').on('click', '.button', function (e) {
        e.preventDefault();
        // Помещаем в форму название выбранной услуги
        $('#dialog-form-appointment input.what_exactly').val($(this).parents('.price_item').find('.price_what').text());
        $("#dialog-form-appointment").dialog({
            modal: true,
            closeOnEscape: true,
            draggable: false,
            classes: {
                "ui-dialog": "appointment-form"
            },
            open: function (event, ui) {
                jQuery('.ui-widget-overlay').on('click', function () {
                    jQuery('#dialog-form-appointment').dialog('close');
                });
            }
        });
    });
    // Отправка формы "Записаться на приём" со страницы таксономии
    $('#dialog-form-appointment').on('submit', 'form', function (e) {
        e.preventDefault();
        // Получаем путь на который отправляются данные формы
        var action = $(this).attr('action');
        var data = $(this).serialize();
        $.ajax({
            data: data,
            url: action,
            type: 'post',
            dataType: 'text',
            success: function (resp) {
                alert(resp);
                if (resp == 'Спасибо за обращение! Наш менеджер свяжется с вами в ближайшее время.') {
                    jQuery('#dialog-form-appointment').dialog('close');
                    $('#dialog-form-appointment form').find("input[type=text], textarea").val("");
                }
            }
        });
    });

    // Отправка формы "Записаться на приём"
    $('#form-order, #form-question').on('submit', 'form', function (e) {
        e.preventDefault();
        var form = this;
        // Получаем путь на который отправляются данные формы
        var action = $(form).attr('action');
        var data = $(form).serialize();
        $.ajax({
            data: data,
            url: action,
            type: 'post',
            dataType: 'text',
            success: function (resp) {
                if (resp == 'OK') {
                    var successForm = $(form).data('success_form');
                    if (successForm) {
                        openForm(successForm);
                    } else {
                        closeForm();
                    }
                } else {
                    alert(resp);
                }
            }
        });
    });

    if ($(".price").length) {
        $(".price").tabs();
        $(".price_select").on('change', '.select2-hidden-accessible', function () {
            var optionSelected = $("option:selected", this);
            var valueSelected = this.value;
            $('.price_content').hide();
            $('.price_content' + valueSelected).show();
        })
    }

    $('#form-order').on('change', 'select.service-type', function () {
        // Получаем всех докторов, которые оказывают выбранную услугу
        var doctors = $("option:selected", this).data('doctor_list').split('|');

        // Актуализируем список докторов
        $('select.doctors-select').html('');
        $.each(doctors, function (index, value) {
            $('select.doctors-select').append('<option value="' + value + '">' + value + '</option>');
        });
    });
});