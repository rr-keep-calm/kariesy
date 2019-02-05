$(document).ready(function() {

    window.data = false;
    window.dataIsReady = false;

    // Скрыть ссылку на загрузку файлов если технология не поддерживается
    if (!window.File || !window.FileList || !window.FileReader) {
        if ($('.jfilestyle').length) {
            $('.jfilestyle').hide();
        }
    }

    function checkDataReadyAndSendForm(action, form) {
        if(window.dataIsReady == false) {
            window.setTimeout(function() {
                checkDataReadyAndSendForm(action, form);
            }, 100);
        } else {
            sendForm(action, form)
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

        // Если есть экран блокирующий форму пока она не отвветила, то активируем его
        var wait = $(form).siblings('.wait-form');
        if ($(wait).length) {
            $(wait).show();
        }

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

        if (id == 'review-form-on-doctor-page' || id == 'review-form-on-clinic-page') {
            var files = $("#photos").prop("files");
            if (files.length > 0) {
                readFile(files, files.length, 0, true);
            } else {
                window.dataIsReady = true;
            }
        } else {
            window.dataIsReady = true;
        }

        checkDataReadyAndSendForm(action, form);

    });

    function sendForm (action, form)
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
                            // смотрим есть ли метка в атрибутах самой формы
                            var eventLabel = '';
                            var eventLabelAttr = $(form).attr('data-eventLabel');
                            if (typeof eventLabelAttr !== typeof undefined && eventLabelAttr !== false) {
                                eventLabel = eventLabelAttr;
                            }

                            if (eventLabel == '') {
                                var parentContainerId = $(form).closest('div').attr('id');
                                if (parentContainerId == 'form-recall') {
                                    eventLabel = 'form-recall';
                                } else if (parentContainerId == 'form-order' || parentContainerId == 'form-order-doctor-page') {
                                    eventLabel = 'form-order';
                                } else if (parentContainerId == 'form-recall-price') {
                                    eventLabel = 'form-price-main';
                                } else if (parentContainerId == 'form-question') {
                                    eventLabel = 'form-question';
                                }
                            }

                            if (eventLabel != '') {
                                dataLayer.push({
                                    'event': 'event-to-ua',
                                    'eventCategory': 'Form',
                                    'eventAction': 'Send',
                                    'eventLabel': eventLabel
                                });
                            }
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
                        window.dataIsReady = false;
                        window.data = false;

                        // Если есть экран блокирующий форму скрываем его
                        var wait = $(form).siblings('.wait-form');
                        if ($(wait).length) {
                            $(wait).hide();
                        }
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
