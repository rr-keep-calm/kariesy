$(document).ready(function () {
  let form_handler = {
    /* Идентификаторы контейнеров с формами */
    formContainersId: '#form-order, #form-question, #form-order-doctor-page, #form-recall, #form-recall-price, ' +
      '#form-review, #recall-form-on-service-page, #form-order-doctor-page-popup, #question-form-wrapper, ' +
      '#free-consult-form-on-service-page',
    /* Идентификаторы форм записи на приём */
    orderFormsId: '#form-order, #form-order-doctor-page, #form-order-doctor-page-popup-form',
    /* Идентификаторы форм отзывов */
    reviewFormsId: '#review-form-on-doctor-page, #review-form-on-clinic-page',
    formProcessPool: {},
    formData: false,
    fileReader: new FileReader(),
    photoIsReady: false,
    allowedForm: [
      "form-recall-form",
      "form-order-form",
      "form-order-doctor-page-form",
      "form-order-doctor-page-popup-form",
      "recall-form-on-price-page",
      "recall-form-on-service-page-form",
      "question-for-doctor-form",
      "free-consult-form-on-service-page-form"
    ],
    allowedOrderForm: [
      "form-order",
      "form-order-doctor-page",
      "form-order-doctor-page-popup-form"
    ],
    unbusy_slots: {},
    allDaysIntervals: '<option value="10:00">10:00</option>\n' +
      '<option value="10:15">10:15</option>\n' +
      '<option value="10:30">10:30</option>\n' +
      '<option value="10:45">10:45</option>\n' +
      '<option value="11:00">11:00</option>\n' +
      '<option value="11:15">11:15</option>\n' +
      '<option value="11:30">11:30</option>\n' +
      '<option value="11:45">11:45</option>\n' +
      '<option value="12:00">12:00</option>\n' +
      '<option value="12:15">12:15</option>\n' +
      '<option value="12:30">12:30</option>\n' +
      '<option value="12:45">12:45</option>\n' +
      '<option value="13:00">13:00</option>\n' +
      '<option value="13:15">13:15</option>\n' +
      '<option value="13:30">13:30</option>\n' +
      '<option value="13:45">13:45</option>\n' +
      '<option value="14:00">14:00</option>\n' +
      '<option value="14:15">14:15</option>\n' +
      '<option value="14:30">14:30</option>\n' +
      '<option value="14:45">14:45</option>\n' +
      '<option value="15:00">15:00</option>\n' +
      '<option value="15:15">15:15</option>\n' +
      '<option value="15:30">15:30</option>\n' +
      '<option value="15:45">15:45</option>\n' +
      '<option value="16:00">16:00</option>\n' +
      '<option value="16:15">16:15</option>\n' +
      '<option value="16:30">16:30</option>\n' +
      '<option value="16:45">16:45</option>\n' +
      '<option value="17:00">17:00</option>\n' +
      '<option value="17:15">17:15</option>\n' +
      '<option value="17:30">17:30</option>\n' +
      '<option value="17:45">17:45</option>\n',
    weekdaysDaysIntervalsBefore: '<option value="09:00">09:00</option>\n' +
      '<option value="09:15">09:15</option>\n' +
      '<option value="09:30">09:30</option>\n' +
      '<option value="09:45">09:45</option>\n',
    weekdaysDaysIntervalsAfter: '<option value="18:00">18:00</option>' +
      '<option value="18:15">18:15</option>\n' +
      '<option value="18:30">18:30</option>\n' +
      '<option value="18:45">18:45</option>\n' +
      '<option value="19:00">19:00</option>\n' +
      '<option value="19:15">19:15</option>\n' +
      '<option value="19:30">19:30</option>\n' +
      '<option value="19:45">19:45</option>\n' +
      '<option value="20:00">20:00</option>\n' +
      '<option value="20:15">20:15</option>\n' +
      '<option value="20:30">20:30</option>\n' +
      '<option value="20:45">20:45</option>',
    MINIMUM_SLOT_TIME_INTERVAL: 15,
    init() {
      // Скрыть ссылку на загрузку файлов если технология не поддерживается
      if (!window.File || !window.FileList || !window.FileReader) {
        if ($('.jfilestyle').length) {
          $('.jfilestyle').hide();
        }
      }

      let self = this;

      $('.popup-close').click(function (e) {
        self.closeForm();
      });

      // Отправка форм
      $(self.formContainersId).on('submit', 'form', function (e) {
        e.preventDefault();
        let form = this;

        // Если есть экран блокирующий форму пока она не ответила, то активируем его
        let wait = $(form).siblings('.wait-form');
        if (!$(wait).length) {
          wait = $(form).find('.wait-form');
        }
        if ($(wait).length) {
          $(wait).show();
        }

        // прописываем токен от google reCaptcha в значение формы и только после этого продолжаем процедуру отправки
        let captchaAction = 'kariesy_net_forms';

        grecaptcha.execute('6LcDTI0UAAAAAOgjhsEqD0k4r0ct4jMFUeijTiq3', {action: captchaAction})
          .then(function (token) {
            $(form).find('input.token').val(token);
            $(form).find('input.captcha-action').val(captchaAction);

            // Собираем информацию для отправки
            let formId = $(form).attr('id');

            // Если форма уже запущена в работу, то повторно её запускать не нужно
            if (typeof self.formProcessPool[formId] === typeof undefined || self.formProcessPool[formId] === false) {
              self.formProcessPool[formId] = true;

              let action = $(form).attr('action');
              self.formData = self.serializeFormJSON(form);

              // Если происходит отправка формы со страницы услуги то пытаемся получить название самой услуги
              if (formId === 'recall-form-on-service-page-form') {
                let magnificPopup = $.magnificPopup.instance;
                self.formData.whatExactlyService = $(magnificPopup.st.el).parents('.price_item').find('.price_what').text();
              }

              if (formId === 'free-consult-form-on-service-page-form') {
                self.formData.whatExactlyService = $('.top h1').text();
              }

              // Если происходит отправка формы со страницы прайса, то дополняем данные из формы активным табом
              if (formId === 'recall-form-on-price-page') {
                let link = $(".price .price_types .ui-tabs-active a");
                self.formData.whatPriceTab = $(link).text() + $(link).attr('href');
              }

              if (
                formId === 'review-form-on-doctor-page'
                || formId === 'review-form-on-clinic-page'
                || formId === 'review-form-on-service-page'
              ) {
                let files = $("#photos").prop("files");
                if (files.length > 0) {
                  self.readFile(files, files.length, 0, true);
                } else {
                  self.photoIsReady = true;
                }
              } else {
                self.photoIsReady = true;
              }

              self.checkDataReadyAndSendForm(action, form);
            }
          }, function () {
            alert('Произошёл сбой, пожалуйста повторите попытку позже.');
          });
      });

      // Актуализируем список врачей при изменении типа услуги
      $(self.orderFormsId).on('change', 'select.service-type', function () {
        self.actualizationDoctorList($(this).closest('form'));
        self.resetDoctorDateTimeSelect($(this).closest('form'));
      });

      // Актуализируем список клиник
      $(self.reviewFormsId).on('change', 'select[name="doctor"]', function () {
        self.actualizationClinicList($(this).closest('form'));
      });

      // Работа с формой записи на приём в части даты и времени
      // Фильтруем доступные на старте временные рамки для записи
      if ($('.time_intervals').length > 0) {
        $('.time_intervals').each(function () {
          self.filterTimeIntervals(this);

          // Если после фильтрации временных интервалов не осталось, то переставляем дату на следующую и заполняем временные
          // интервалы заново
          if (!$(this).find('option').length) {
            let tomorrow = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
            let dateInput = $(this).closest('form');
            dateInput = $(dateInput).find('.date');
            $(dateInput).datepicker('setDate', tomorrow);
            $(dateInput).datepicker('setStartDate', tomorrow);
            self.updateTimeIntervals(tomorrow, this);
          }
        });

        // При смене специалиста получаем его слоты из IDENT
        $(self.orderFormsId).on('change', '.doctors-select', function () {
          // показываем заглушку
          let stub = $(this).closest('form');
          stub = $(stub).find('.data_time_stub');
          $(stub).each(function () {
            $(this).width($(this).parent('div').width());
            $(this).height($(this).parent('div').height());
            $(this).show();
          });

          let busyText = $(this).closest('form').find('.not_desired_date_time');
          let dateTimeRow = $(this).closest('form').find('.desired_date_time');
          let doctorNid = $("option:selected", this).attr('data-doctor-nid');
          let dateInput = $(this).closest('form');
          dateInput = $(dateInput).find('.date');
          if (doctorNid === 'no_matter') {
            // выставляем значения по умолчанию как для всех специалистов
            $(dateTimeRow).show();
            $(busyText).hide();
            if ($(dateInput).closest('form').find('.busy_order_input').length) {
              $(dateInput).closest('form').find('.busy_order_input').remove();
            }
            self.resetDoctorDateTimeSelect($(dateInput).closest('form'));
            $(stub).each(function () {
              $(this).hide();
            });
          } else {
            $.ajax({
              url: '/get/doctor-slots',
              dataType: 'json',
              type: 'GET',
              data: {
                '_format': 'json',
                'nid': doctorNid,
              },
              success: response => {
                if (response !== '') {
                  // Парсим json слотов
                  let slots = JSON.parse(response);
                  let unbusy_slots = {};

                  // собираем свободные слоты
                  $.each(slots, function (index, value) {
                    if (value.IsBusy === false) {
                      let splited_date = value.StartDateTime.split('T');
                      let time_without_date = splited_date[1].split(':');
                      let date_without_time = splited_date[0].split('-');
                      if (typeof undefined === typeof unbusy_slots[date_without_time[2] + '.' + date_without_time[1] + '.' + date_without_time[0]]) {
                        unbusy_slots[date_without_time[2] + '.' + date_without_time[1] + '.' + date_without_time[0]] = {};
                      }
                      unbusy_slots[date_without_time[2] + '.' + date_without_time[1] + '.' + date_without_time[0]][time_without_date[0] + ':' + time_without_date[1]] = value.LengthInMinutes;
                    }
                  });

                  if (!$.isEmptyObject(unbusy_slots)) {
                    $(dateTimeRow).show();
                    $(busyText).hide();
                    if ($(dateInput).closest('form').find('.busy_order_input').length) {
                      $(dateInput).closest('form').find('.busy_order_input').remove();
                    }

                    // записываем свободные слоты для каждой даты по выбранному доктору
                    self.unbusy_slots = unbusy_slots;

                    // ищем первую и последнюю дату
                    let first_loop_element = true;
                    let first_date = new Date();
                    let last_date = new Date();
                    $.each(unbusy_slots, function (index, value) {
                      let construct_data_for_date = index.toString().split('.');
                      let date_from_index = new Date(construct_data_for_date[2], parseInt(construct_data_for_date[1]) - 1, construct_data_for_date[0]);
                      if (first_loop_element === true) {
                        first_date = date_from_index;
                        first_loop_element = false;
                      }
                      if (first_date > date_from_index) {
                        first_date = date_from_index;
                      }
                      if (last_date < date_from_index) {
                        last_date = date_from_index;
                      }
                    });

                    let datepickerConf = {};
                    // Устанавливаем начальную дату
                    datepickerConf['startDate'] = first_date;

                    // Устанавливаем конечную дату
                    datepickerConf['endDate'] = last_date;

                    // Исключаем дни без свободных слотов
                    let disabledDate = [];
                    for (let d = new Date(first_date.getTime()); d <= last_date; d.setDate(d.getDate() + 1)) {
                      let property = ('0' + d.getDate()).slice(-2) + '.'
                        + ('0' + (d.getMonth() + 1)).slice(-2) + '.'
                        + d.getFullYear();
                      if (!unbusy_slots.hasOwnProperty(property)) {
                        disabledDate.push(property);
                      }
                    }
                    if (disabledDate.length > 0) {
                      datepickerConf['datesDisabled'] = disabledDate;
                    }

                    // инициализируем datepicker заново
                    $(dateInput).datepicker('destroy');
                    datepickerConf['language'] = 'ru';
                    datepickerConf['autoclose'] = 'true';
                    $(dateInput).datepicker(datepickerConf).datepicker('setDate', first_date);
                  } else {
                    // Если свободных слотов у специалиста нет, то выводим сообщенеи о занятости
                    $(dateTimeRow).hide();
                    $(busyText).show();
                    $(dateTimeRow).closest('form').append('<input type="hidden" name="busy_order" value="1" class="busy_order_input">');
                  }

                  $(stub).each(function () {
                    $(this).hide();
                  });
                } else {
                  // Если у доктора нет слотов, то выставляем значения по умолчанию как для любого специалиста
                  $(dateTimeRow).show();
                  $(busyText).hide();
                  if ($(dateInput).closest('form').find('.busy_order_input').length) {
                    $(dateInput).closest('form').find('.busy_order_input').remove();
                  }
                  self.resetDoctorDateTimeSelect($(dateInput).closest('form'));
                  $(stub).each(function () {
                    $(this).hide();
                  });
                }
              },
              error: response => {
                alert('Не удалось получить расписание доктора, пожалуйста повторите попытку.');
              },
            });
          }
        });

        // ловим событие изменения даты и актуализируем временные интервалы
        $('.date').datepicker().on('changeDate', function () {
          let timeSelect = $(this).closest('form');
          timeSelect = $(timeSelect).find('.time_intervals');
          // показываем заглушку для времени
          let stub = $(timeSelect).parent('div.form_col');
          stub = $(stub).find('.data_time_stub');
          $(stub).width($(this).parent('div').width());
          $(stub).height($(this).parent('div').height());
          $(stub).show();

          let selectedDate = $(this).datepicker('getDate');
          let property = ('0' + selectedDate.getDate()).slice(-2) + '.'
            + ('0' + (selectedDate.getMonth() + 1)).slice(-2) + '.'
            + selectedDate.getFullYear();
          let now = new Date();
          let nowProperty = ('0' + now.getDate()).slice(-2) + '.'
            + ('0' + (now.getMonth() + 1)).slice(-2) + '.'
            + now.getFullYear();

          // Актуализируем время
          if (
            typeof self.unbusy_slots !== typeof undefined
            && typeof self.unbusy_slots[property] !== typeof undefined
          ) {
            let options_and_value_for_select = self.create_time_options_and_value_for_select(self.unbusy_slots[property]);
            $(timeSelect).html(options_and_value_for_select[0]);
            $(timeSelect).val(options_and_value_for_select[1]);
            if (property === nowProperty) {
              self.filterTimeIntervals(timeSelect);
              if (!$(timeSelect).find('option').length) {
                let endDate = $(this).datepicker('getEndDate');
                for (let d = new Date(selectedDate.getTime()); d <= endDate; d.setDate(d.getDate() + 1)) {
                  let propertyForCheck = ('0' + d.getDate()).slice(-2) + '.'
                    + ('0' + (d.getMonth() + 1)).slice(-2) + '.'
                    + d.getFullYear();
                  if (property !== propertyForCheck && self.unbusy_slots.hasOwnProperty(propertyForCheck)) {
                    $(this).datepicker('setDate', d);
                    $(this).datepicker('setStartDate', d);
                    break;
                  }
                }
              }
            }
            $(stub).hide();
          } else {
            // иначе выставляем доступные временные интервалы по умолчанию согласно текущему дню
            $(timeSelect).html('');
            self.updateTimeIntervals(selectedDate, timeSelect);
            if (property === nowProperty) {
              self.filterTimeIntervals(timeSelect);
              if (!$(timeSelect).find('option').length) {
                let tomorrow = new Date(selectedDate.getTime() + 24 * 60 * 60 * 1000);
                self.updateTimeIntervals(tomorrow, timeSelect);
                $(this).datepicker('setDate', tomorrow);
                $(this).datepicker('setStartDate', tomorrow);
              }
            }
            $(stub).hide();
          }
        });
      }
      self.afterInit();
    },
    afterInit() {
      let self = this;
      $(self.orderFormsId).each(function () {
        let doctorList = $(this).find('.doctors-select');
        if (doctorList.length) {
          if ($("option:selected", doctorList).attr('data-doctor-nid') !== 'no_matter') {
            $(doctorList).change();
          }
        }
      })
    },
    readFile(files, length, step, readForce) {
      let self = this;
      if (readForce === true) {
        if (files[step].type.indexOf("image") == 0) {
          self.fileReader.readAsDataURL(files[step]);
          self.readFile(files, length, step, false);
        }
      } else if (this.fileReader.readyState != 2) {
        window.setTimeout(function () {
          self.readFile(files, length, step, false);
        }, 100);
      } else {
        self.formData['files' + step + 'base'] = this.fileReader.result;
        self.formData['files' + step + 'name'] = files[step].name;
        step++;
        if (step < length) {
          self.readFile(files, length, step, true);
        } else {
          self.photoIsReady = true;
        }
      }
    },
    checkDataReadyAndSendForm(action, form) {
      let self = this;
      if (this.photoIsReady == false) {
        window.setTimeout(function () {
          self.checkDataReadyAndSendForm(action, form);
        }, 100);
      } else {
        self.sendForm(action, form)
      }
    },
    sendForm(action, form) {
      $.ajax({
        url: action,
        type: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        dataType: 'json',
        data: JSON.stringify(this.formData),
        success: response => {
          if (response.text == 'OK') {
            // смотрим есть ли метка в атрибутах самой формы
            let parentContainerId = $(form).closest('div').attr('id');
            let formId = $(form).attr('id');
            let eventLabel = '';
            let eventLabelAttr = $(form).attr('data-eventLabel');
            if (typeof eventLabelAttr !== typeof undefined && eventLabelAttr !== false) {
              eventLabel = eventLabelAttr;
            }

            if (eventLabel === '') {
              if (parentContainerId === 'form-recall') {
                eventLabel = 'form-recall';
              } else if (parentContainerId === 'form-order' || parentContainerId === 'form-order-doctor-page') {
                eventLabel = 'form-order';
              } else if (parentContainerId === 'form-recall-price') {
                eventLabel = 'form-price-main';
              } else if (parentContainerId === 'form-question') {
                eventLabel = 'form-question';
              }
            }

            if (eventLabel !== '') {
              dataLayer.push({
                'event': 'event-to-ua',
                'eventCategory': 'Form',
                'eventAction': 'Send',
                'eventLabel': eventLabel
              });
            }

            // Отправляем данные в coMagic
            if (typeof Comagic !== typeof undefined && Comagic !== null && this.allowedForm.indexOf(formId) != -1) {
              let name = typeof this.formData.name !== typeof undefined && this.formData.name !== null ? this.formData.name : 'не передаётся в форме';
              let email = typeof this.formData.email !== typeof undefined && this.formData.email !== null ? this.formData.email : 'не передаётся в форме';
              let phone = typeof this.formData.phone !== typeof undefined && this.formData.phone !== null ? this.formData.phone : 'не передаётся в форме';
              let coMagicMessage = '';
              if (formId === 'form-recall-form') {
                coMagicMessage += 'Обратный звонок\n';
              }
              if (
                formId === 'form-order-form'
                || formId === 'form-order-doctor-page-form'
                || formId === 'form-order-doctor-page-popup-form'
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
                coMagicMessage += 'Услуга - ' + this.formData.service + '\n';
                coMagicMessage += 'Врач - ' + this.formData.doctor + '\n';
                if (typeof this.formData.comment !== typeof undefined &&
                  this.formData.comment !== null &&
                  this.formData.comment !== '') {
                  coMagicMessage += 'Комментарий:\n ' + this.formData.comment;
                }

              }
              if (formId == 'recall-form-on-price-page') {
                coMagicMessage += 'Запись на приём на странице прайса\n';
              }
              if (formId == 'recall-form-on-service-page-form') {
                coMagicMessage += 'Запись на приём на странице услуги через блок с ценами\n';
              }
              if (formId == 'free-consult-form-on-service-page-form') {
                coMagicMessage += 'Запись на бесплатную консультацию\n';
                coMagicMessage += 'Услуга — ' + $('.top h1').text();
              }
              if (formId == 'question-for-doctor-form') {
                coMagicMessage += 'Вопрос врачу\n';
                if (
                  typeof this.formData.question !== typeof undefined
                  && this.formData.question !== null
                  && this.formData.question !== ''
                ) {
                  coMagicMessage += 'Вопрос:\n ' + this.formData.question;
                }
              }
              Comagic.addOfflineRequest({
                name: name,
                email: email,
                phone: phone.replace(/\D+/g, ''),
                message: coMagicMessage
              });
            }

            let successForm = $(form).data('success_form');
            if (successForm) {
              this.openForm(successForm);
            } else {
              this.closeForm();
            }
            // актуализировать список докторов и виджеты даты и времени нужно только на формах записи на приём
            if (this.allowedOrderForm.indexOf(formId) != -1) {
              this.actualizationDoctorList(form);
              this.resetDoctorDateTimeSelect(form);
            }
          } else {
            alert(response.text);
          }
        },
        error: response => {
          alert('Произошла ошибка, пожалуйста повторите попытку позже.');
        },
        complete: response => {
          this.formData = false;

          // Если есть экран блокирующий форму скрываем его
          let wait = $(form).siblings('.wait-form');
          if (!$(wait).length) {
            wait = $(form).find('.wait-form');
          }
          if ($(wait).length) {
            $(wait).hide();
          }
          let id = $(form).attr('id');
          this.formProcessPool[id] = false;
        }
      });
    },
    actualizationDoctorList(form) {
      let serviceTypeList = $(form).find('select.service-type');
      // Получаем всех докторов, которые оказывают выбранную услугу
      let doctors = $("option:selected", serviceTypeList).data('doctor_list').split('|');

      // Актуализируем список докторов
      let doctorsSelect = $(form).find('select.doctors-select');
      $(doctorsSelect).html('');
      $.each(doctors, function (index, value) {
        let doctorData = value.split(':::');
        $(doctorsSelect).append('<option value="' + doctorData[0] + '" data-doctor-nid="' + doctorData[1] + '">' + doctorData[0] + '</option>');
      });
    },
    actualizationClinicList(form) {
      let doctorList = $(form).find('select[name="doctor"]');
      // Получаем все клиники в которых работает выбранный доктор
      let clinics = $("option:selected", doctorList).data('clinic_list').split('|');

      // Актуализируем список клиник
      let clinicSelect = $(form).find('select[name="clinic"]');
      $(clinicSelect).html('');
      $.each(clinics, function (index, value) {
        let clinicData = value.split(':::');
        $(clinicSelect).append('<option value="' + clinicData[1] + '">' + clinicData[0] + '</option>');
      });
    },
    resetDoctorDateTimeSelect(form) {
      this.unbusy_slots = {};
      let dateInput = $(form).find('.date');
      $(dateInput).datepicker('destroy');
      let day = new Date();
      $(dateInput).datepicker({
        language: "ru",
        autoclose: true,
        startDate: day
      }).datepicker('setDate', day);
    },
    openForm(form) {
      $.magnificPopup.open({
        items: {
          src: form,
          type: 'inline'
        },
        showCloseBtn: false,
        removalDelay: 300,
        mainClass: 'mfp-fade'
      });
    },
    closeForm() {
      $.magnificPopup.close();
    },
    serializeFormJSON(form) {
      let o = {};
      let a = $(form).serializeArray();
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
    },
    filterTimeIntervals(timeIntervalSelect) {
      const today = new Date();
      const nowHour = today.getUTCHours() + 3;
      const nowMinutes = today.getUTCMinutes();
      const nowDay = today.getDay();
      $(timeIntervalSelect).find('option').each(function () {
        let timeMark = $(this).text().split(':');
        if ((nowDay === 0 || nowDay === 6) && (parseInt(timeMark[0]) < 10 || parseInt(timeMark[0]) > 17)) {
          $(this).remove();
        }
        if (parseInt(timeMark[0]) > nowHour) {
          return true;
        }
        if (parseInt(timeMark[0]) < nowHour) {
          $(this).remove();
          return true;
        }
        if (parseInt(timeMark[1]) > nowMinutes) {
          return true;
        }
        if (parseInt(timeMark[1]) <= nowMinutes) {
          $(this).remove();
          return true;
        }
      });
    },
    updateTimeIntervals(date, timeIntervalSelect) {
      $(timeIntervalSelect).append(this.allDaysIntervals);
      const day = date.getDay();
      $(timeIntervalSelect).val('10:00');
      if (day !== 0 && day !== 6) {
        $(timeIntervalSelect).prepend(this.weekdaysDaysIntervalsBefore);
        $(timeIntervalSelect).append(this.weekdaysDaysIntervalsAfter);
        $(timeIntervalSelect).val('09:00');
      }
    },
    create_time_options_and_value_for_select(time_object) {
      let self = this;
      let time_marks = [];
      $.each(time_object, function (index, value) {
        let splited_time = index.toString().split(':');
        let hour = parseInt(splited_time[0]);
        let hour_mark = splited_time[0].toString();

        let min = parseInt(splited_time[1]);
        let min_mark = splited_time[1].toString();

        for (let i = 0; i < value / self.MINIMUM_SLOT_TIME_INTERVAL; i++) {
          if (i !== 0) {
            min += self.MINIMUM_SLOT_TIME_INTERVAL;
            min_mark = min.toString();
          }
          if (min < 10 && min_mark !== '00' && min_mark !== '05') {
            min_mark = '0' + min_mark;
          }

          if (min === 60) {
            hour++;
            hour_mark = hour.toString();
            if (hour < 10) {
              hour_mark = '0' + hour_mark;
            }

            min = 0;
            min_mark = '00';
          }
          time_marks.push(hour_mark + ':' + min_mark);
        }
      });

      time_marks.sort(function (a, b) {
        let splited_a = a.toString().split(':');
        let splited_b = b.toString().split(':');
        if (parseInt(splited_a[0]) > parseInt(splited_b[0])) return 1;
        if (parseInt(splited_a[0]) < parseInt(splited_b[0])) return -1;
        if (parseInt(splited_a[1]) > parseInt(splited_b[1])) return 1;
        if (parseInt(splited_a[1]) < parseInt(splited_b[1])) return -1;
        if (parseInt(splited_a[1]) === parseInt(splited_b[1])) return 0;
      });

      let options = '';
      $.each(time_marks, function (index, value) {
        options += '<option value="' + value + '">' + value + '</option>\n';
      });

      return [options, time_marks[0]];
    }
  };

  form_handler.init();
});
