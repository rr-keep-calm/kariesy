$(document).ready(function() {

    window.reToken = '';
    window.data = false;
    window.dataIsReady = false;
    window.photoIsReady = false;
    window.formProcessPool = {};

    // Скрыть ссылку на загрузку файлов если технология не поддерживается
    if (!window.File || !window.FileList || !window.FileReader) {
        if ($('.jfilestyle').length) {
            $('.jfilestyle').hide();
        }
    }

    function checkDataReadyAndSendForm(action, form) {
        // Првоеряем был ли получен токен от Google хотя бы один раз
        // Токен может не успеть подгрузиться у особо торопливых пользователей
        if (window.reToken == '') {
            window.setTimeout(function() {
                checkDataReadyAndSendForm(action, form);
            }, 100);
        } else {
            if (!window.data.action || !window.data.token) {
                // Проверить установлены ли у формы token и action?
                // Если нет, то установить их.
                window.data.action = $(form).find('input.captcha-action').val();
                window.data.token = window.reToken
                window.dataIsReady = true;
            } else {
                window.dataIsReady = true;
            }
            if (window.dataIsReady == false || window.photoIsReady == false) {
                window.setTimeout(function () {
                    checkDataReadyAndSendForm(action, form);
                }, 100);
            } else {
                sendForm(action, form)
            }
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
                window.photoIsReady = true;
            }
        }
    }

    // Отправка форм
    $('#form-order, #form-question, #form-order-doctor-page, #form-recall, #form-recall-price, #form-review, #recall-form-on-service-page, #form-order-doctor-page-popup, #question-form-wrapper').on('submit', 'form', function (e) {
        e.preventDefault();

        // Собираем информацию для отправки
        var form = this;
        var id = $(form).attr('id');

        // Если форма уже запущена в работу, то повторно её запускать не нужно
        if (typeof window.formProcessPool.id === typeof undefined || window.formProcessPool.id === false) {
            window.formProcessPool.id = true;

            // Если есть экран блокирующий форму пока она не ответила, то активируем его
            var wait = $(form).siblings('.wait-form');
            if (!$(wait).length) {
                wait = $(form).find('.wait-form');
            }
            if ($(wait).length) {
                $(wait).show();
            }

            var action = $(form).attr('action');
            window.data = serializeFormJSON(form);

            // Если происходит отправка формы со страницы услуги то пытаемся получить название самой услуги
            if (id == 'recall-form-on-service-page-form') {
                var magnificPopup = $.magnificPopup.instance;
                window.data.whatExactlyService = $(magnificPopup.st.el).parents('.price_item').find('.price_what').text();
            }

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
                    window.photoIsReady = true;
                }
            } else {
                window.photoIsReady = true;
            }

            checkDataReadyAndSendForm(action, form);
        }
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
                            var parentContainerId = $(form).closest('div').attr('id');
                            var formId = $(form).attr('id');
                            var eventLabel = '';
                            var eventLabelAttr = $(form).attr('data-eventLabel');
                            if (typeof eventLabelAttr !== typeof undefined && eventLabelAttr !== false) {
                                eventLabel = eventLabelAttr;
                            }

                            if (eventLabel == '') {
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

                            // Отправляем данные в coMagic
                            // Массив разрешённых идентификаторов форм
                            var allowedForm = new Array("form-recall-form", "form-order-form", "form-order-doctor-page-form", "form-order-doctor-page-popup-form", "recall-form-on-price-page", "recall-form-on-service-page-form", "question-for-doctor-form");
                            if (typeof Comagic !== typeof undefined && Comagic !== null && allowedForm.indexOf(formId) != -1) {
                                var name = typeof window.data.name !== typeof undefined && window.data.name !== null ? window.data.name : 'не передаётся в форме';
                                var email = typeof window.data.email !== typeof undefined && window.data.email !== null ? window.data.email : 'не передаётся в форме';
                                var phone = typeof window.data.phone !== typeof undefined && window.data.phone !== null ? window.data.phone : 'не передаётся в форме';
                                var coMagicMessage = '';
                                if (formId == 'form-recall-form') {
                                    coMagicMessage += 'Обратный звонок\n';
                                }
                                if (formId == 'form-order-form' ||
                                    formId == 'form-order-doctor-page-form' ||
                                    formId == 'form-order-doctor-page-popup-form'
                                ) {
                                    switch (formId) {
                                        case 'form-order-form':
                                            coMagicMessage += 'Запись на приём в шапке\n';
                                            break;
                                        case 'form-order-doctor-page-form':
                                        case 'form-order-doctor-page-popup-form':
                                            coMagicMessage += 'Запись на приём на странице врача\n';
                                            break;
                                    }
                                    coMagicMessage += 'Услуга - ' + window.data.service + '\n';
                                    coMagicMessage += 'Врач - ' + window.data.doctor + '\n';
                                    if (typeof window.data.comment !== typeof undefined &&
                                        window.data.comment !== null &&
                                        window.data.comment !== '') {
                                        coMagicMessage += 'Комментарий:\n ' + window.data.comment;
                                    }

                                }
                                if (formId == 'recall-form-on-price-page') {
                                    coMagicMessage += 'Запись на приём на странице прайса\n';
                                }
                                if (formId == 'recall-form-on-service-page-form') {
                                    coMagicMessage += 'Запись на приём на странице услуги через блок с ценами\n';
                                }
                                if (formId == 'question-for-doctor-form') {
                                    coMagicMessage += 'Вопрос врачу\n';
                                    if (typeof window.data.question !== typeof undefined &&
                                        window.data.question !== null &&
                                        window.data.question !== '') {
                                        coMagicMessage += 'Вопрос:\n ' + window.data.question;
                                    }
                                }
                                Comagic.addOfflineRequest({
                                    name: name,
                                    email: email,
                                    phone: phone.replace(/\D+/g, ''),
                                    message: coMagicMessage
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
                        if (!$(wait).length) {
                            wait = $(form).find('.wait-form');
                        }
                        if ($(wait).length) {
                            $(wait).hide();
                        }
                        var id = $(form).attr('id');
                        window.formProcessPool.id = false;
                    },
                });
            },
        );
    }

    $('#form-order, #form-order-doctor-page, #form-order-doctor-page-popup-form').on('change', 'select.service-type', function () {
        // Получаем всех докторов, которые оказывают выбранную услугу
        var doctors = $("option:selected", this).data('doctor_list').split('|');

        // Актуализируем список докторов
        let doctorsSelect = $(this).closest('form');
        doctorsSelect = $(doctorsSelect).find('select.doctors-select');
        $(doctorsSelect).html('');
        $.each(doctors, function (index, value) {
            let doctorData = value.split(':::');
            $(doctorsSelect).append('<option value="' + doctorData[0] + '" data-doctor-nid="' + doctorData[1] + '">' + doctorData[0] + '</option>');
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

    // При смене специалиста получаем его слоты из IDENT
    $('#form-order-doctor-page-form, #form-order-doctor-page-popup-form, #form-order-form').on('change', '.doctors-select', function () {
      console.log($(this).val());
    })
  }
});


function redyRecaptcha() {
    let captchaAction = 'kariesy_net_forms';

    grecaptcha.execute('6LcDTI0UAAAAAOgjhsEqD0k4r0ct4jMFUeijTiq3', {action: captchaAction})
        .then(function (token) {
            if (token) {
                $('input.token').val(token);
                $('input.captcha-action').val(captchaAction);
                window.reToken = token;
            }
        });
}
