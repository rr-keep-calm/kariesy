$(document).ready(function() {
    if ($(".price").length) {
        $(".price").tabs();
        $(".price_select").on('change', '.select2-hidden-accessible', function () {
            var valueSelected = this.value;
            $('.price_content').hide();
            $('.price_content' + valueSelected).show();
        })
    }

    // Если мы на главной странице и есть текст больше одного абзаца
    if ($('.home-text.page .wrapper').length && $('.home-text.page .wrapper').children().length <= 1) {
        $('.home-text.page .wrapper .pagination-block_more').hide();
    }
    $('.home-text.page .wrapper').on('click', '.pagination-block_more.open', function(){
        $(this).removeClass('open');
        $(this).addClass('close');
        $(this).text('Свернуть');
        $.each($('.home-text.page .wrapper').children(), function(){
            if (!$(this).hasClass('pagination-block_more')) {
                $(this).show('slow');
            }
        });
    });
    $('.home-text.page .wrapper').on('click', '.pagination-block_more.close', function(){
        $(this).removeClass('close');
        $(this).addClass('open');
        $(this).text('Развернуть');
        var i = 0;
        $.each($('.home-text.page .wrapper').children(), function(){
            if (!$(this).hasClass('pagination-block_more') && i != 0) {
                $(this).hide('slow');
            }
            i++;
        });
    });

    // Сортировка на странице всех отзывов срабатывает сразу же при смене значения
    $('#views-exposed-form-reviews-page-1 select').change(function () {
        $('#views-exposed-form-reviews-page-1').submit();
    })
});