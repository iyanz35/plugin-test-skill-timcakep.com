jQuery(document).ready(function($) {
    $('.my-ac-optin-form').on('submit', function(e) {
        e.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: my_ac_optin.ajaxurl,
            data: form_data,
            success: function(response) {
                $('.my-ac-optin-form').hide();
                $('.my-ac-optin-message').html('<p class="success">' + response.data + '</p>');
            },
            error: function(response) {
                $('.my-ac-optin-message').html('<p class="error">' + response.responseText + '</p>');
            }
        });
    });
});
