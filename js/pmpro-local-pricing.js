// Function to make an AJAX request using the fetch API
function pmpro_local_makeAjaxRequest(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams(data).toString()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(callback)
    .catch(error => {
        console.error('Fetch error:', error);
    });
}

// Function to handle the AJAX response
function pmpro_local_handleResponse(response) {
    // If there's a response, let's show it.
    if (response) {
        document.getElementById('pmpro-local-price').innerHTML = response;
    }
}

// On load, get the local price via AJAX.
document.addEventListener("DOMContentLoaded", function() {
    var data = {
        'action': 'pmpro_local_get_local_cost_text',
        'pmpro_level_id': document.getElementById('level').value,
        'pmpro_discount_code': document.getElementById('other_discount_code').value,
    };

    // We'll use the fetch API to make the request.
    pmpro_local_makeAjaxRequest(ajaxurl, data, pmpro_local_handleResponse);
});