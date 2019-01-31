$(document).ready(function() {
    if ($(".price").length) {
        $(".price").tabs();
        $(".price_select").on('change', '.select2-hidden-accessible', function () {
            var valueSelected = this.value;
            $('.price_content').hide();
            $('.price_content' + valueSelected).show();
        })
    }
});