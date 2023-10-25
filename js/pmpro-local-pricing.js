// On load, get the local price via AJAX.
function pmpro_local_handleLevelCostChange() {
    var data = {
        'action': 'pmpro_local_get_local_cost_text',
        'level': jQuery('#level').val(),
        'discount_code': jQuery('#other_discount_code').val(),
    };

    // We'll use AJAX to get the local price.
    jQuery.get(pmpro_local.ajaxurl, data, function(response) {
        // If there's a response, let's show it.
        if (response) {
            jQuery('#pmpro-local-price').html(response);
        }
    });
}

jQuery(document).ready(function($) {
    pmpro_local_handleLevelCostChange();
});