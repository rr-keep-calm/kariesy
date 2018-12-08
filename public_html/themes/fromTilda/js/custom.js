$(document).ready(function() {
    $('.price_item .price_button').on('click', '.button', function (e) {
        e.preventDefault();
        // Помещаем в орму название выбранной услуги
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
    // Отправка формы "Записаться на приём"
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
});