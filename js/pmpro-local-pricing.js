// On load, get the local price via AJAX.
function pmpro_local_handleLevelCostChange() {
    // The level ID can be in #pmpro_level or #level. Check $pmpro_level first.
    var level_id = jQuery('#pmpro_level').val();
    if ( ! level_id ) {
        level_id = jQuery('#level').val();
    }

    // The discount code can be in #pmpro_other_discount_code or #other_discount_code. Check $pmpro_other_discount_code first.
    var discount_code = jQuery('#pmpro_other_discount_code').val();
    if ( ! discount_code ) {
        discount_code = jQuery('#other_discount_code').val();
    }

    var data = {
        'action': 'pmpro_local_get_local_cost_text',
        'pmpro_level': level_id,
        'pmpro_discount_code': discount_code,
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