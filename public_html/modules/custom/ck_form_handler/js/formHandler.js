$(document).ready(function() {

    window.data = false;
    window.dataIsReady = false;

    // Скрыть ссылку на загрузку файлов если технология не поддерживается
    if (!window.File || !window.FileList || !window.FileReader) {
        if ($('.jfilestyle').length) {
            $('.jfilestyle').hide();
        }
    }

    function checkDataReadyAndSendForm(action, successForm) {
        if(window.dataIsReady == false) {
            window.setTimeout(function() {
                checkDataReadyAndSendForm(action, successForm);
            }, 100);
        } else {
            sendForm(action, successForm)
        }
    }

    function readFile(files, length, step, readForce)
    {
        if(readForce == true) {
            if (files[step].type.indexOf("image") == 0) {
                window.fileReader = new FileReader();
                window.fileReader.readAsDataURL(files[step]);
                readFile(files, length, step, false);
            }
        } else if(window.fileReader.readyState != 2) {
            window.setTimeout(function() {
                readFile(files, length, step, false);
            }, 100);
        } else {
            window.data['files'+step+'base'] = window.fileReader.result;
            window.data['files'+step+'name'] = files[step].name;
            step++;
            if (step < length) {
                readFile(files, length, step, true);
            } else {
                window.dataIsReady = true;
            }
        }
    }

    // Отправка форм
    $('#form-order, #form-question, #form-order-doctor-page, #form-recall, #form-recall-price, #form-review').on('submit', 'form', function (e) {
        e.preventDefault();

        // Собираем информацию для отправки
        var form = this;
        var action = $(form).attr('action');
        var successForm = $(form).data('success_form');
        var id = $(form).attr('id');
        window.data = serializeFormJSON(form);

        // Если происходит отправка формы со страницы прайса, то дополняем данные из формы активным табом
        if (id == 'recall-form-on-price-page') {
            // Вычисляем активный таб
            var link = $(".price .price_types .ui-state-active a");
            window.data.whatPriceTab = $(link).text() + $(link).attr('href');
        }

        if (id == 'review-form-on-doctor-page') {
            var files = $("#photos").prop("files");
            if (files.length > 0) {
                readFile(files, files.length, 0, true);
            } else {
                window.dataIsReady = true;
            }
        } else {
            window.dataIsReady = true;
        }

        checkDataReadyAndSendForm(action, successForm);

    });

    function sendForm (action, successForm)
    {
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
                    data: JSON.stringify(window.data),
                    success: response => {
                        response = $.parseJSON(response);
                        if (response.text == 'OK') {
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
                        window.dataIsReady = false;
                        window.data = false;
                    },
                });
            },
        );
    }

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
