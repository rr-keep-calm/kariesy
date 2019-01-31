$(document).ready(function() {
    // Отправка формы "Записаться на приём"
    $('#form-order, #form-question, #form-order-doctor-page, #form-recall, #form-recall-price').on('submit', 'form', function (e) {
        e.preventDefault();
        var form = this;
        var action = $(form).attr('action');
        var data = serializeFormJSON(form);

        // Если происходит отправка формы со страницы прайса, то дополняем данные из формы активным табом
        var id = $(form).attr('id');
        if (id == 'recall-form-on-price-page') {
            // Вычисляем активный таб
            var link = $(".price .price_types .ui-state-active a");
            data.whatPriceTab = $(link).text() + $(link).attr('href');
        }

        let sessionToken = new Promise((resolve, reject) => {
            $.ajax({
                url: '/rest/session/token',
                dataType: 'text',
                type: 'GET',
                success: response => {
                    resolve(response);
                },
                error: response => {
                    reject(response);
                },
            });
        });

        sessionToken.then(
            token => {
                $.ajax({
                    url: action,
                    type: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': token,
                    },
                    dataType: 'json',
                    data: JSON.stringify(data),
                    success: response => {
                        response = $.parseJSON(response);
                        if (response.text == 'OK') {
                            var successForm = $(form).data('success_form');
                            if (successForm) {
                                openForm(successForm);
                            } else {
                                closeForm();
                            }
                        } else {
                            alert(response.text);
                        }
                    },
                    error: response => {
                        alert('Произошла ошибка, пожалуйста повторите попытку позже.');
                    },
                    complete: response => {
                        redyRecaptcha();
                    },
                });
            },
        );
    });

    $('#form-order, #form-order-doctor-page').on('change', 'select.service-type', function () {
        // Получаем всех докторов, которые оказывают выбранную услугу
        var doctors = $("option:selected", this).data('doctor_list').split('|');

        // Актуализируем список докторов
        $('select.doctors-select').html('');
        $.each(doctors, function (index, value) {
            $('select.doctors-select').append('<option value="' + value + '">' + value + '</option>');
        });
    });

    function serializeFormJSON (form) {
        var o = {};
        var a = $(form).serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
});


function redyRecaptcha() {
    let captchaAction = 'kariesy_net_forms';

    grecaptcha.execute('6LcDTI0UAAAAAOgjhsEqD0k4r0ct4jMFUeijTiq3', {action: captchaAction})
        .then(function (token) {
            if (token) {
                $('input.token').val(token);
                $('input.captcha-action').val(captchaAction);
            }
        });
}