$(document).ready(function() {
    // Отправка формы "Записаться на приём"
    $('#form-order, #form-question, #form-order-doctor-page, #form-recall').on('submit', 'form', function (e) {
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

    $('#form-order, #form-order-doctor-page').on('change', 'select.service-type', function () {
        // Получаем всех докторов, которые оказывают выбранную услугу
        var doctors = $("option:selected", this).data('doctor_list').split('|');

        // Актуализируем список докторов
        $('select.doctors-select').html('');
        $.each(doctors, function (index, value) {
            $('select.doctors-select').append('<option value="' + value + '">' + value + '</option>');
        });
    });
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