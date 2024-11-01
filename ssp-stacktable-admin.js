jQuery(document).ready(function($) {
    var $pageSelect = $('#ssp_stacktable_page_ids');
    $pageSelect.select2();
    $pageSelect.on('select2:select', function () {
        var values = $pageSelect.val();
        if(null !== values) {
            if(values.length > 1) {
                var none = $.inArray('none', values);
                var all = $.inArray('all', values);
                if(all > -1) {
                    values = 'all';
                    $pageSelect.val(values).trigger('change');
                } else if(none > -1) {
                    values.splice(none, 1);
                    $pageSelect.val(values).trigger('change');
                }
            }
        }
    });
    $('#reset-pages').on('click', function(e){
        e.preventDefault();
        $pageSelect.val('none').trigger('change');
    });
   
});