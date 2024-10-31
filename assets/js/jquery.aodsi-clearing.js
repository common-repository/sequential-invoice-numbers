/* 
on button press and confirmation performs an ajax call to the function that clears the table
*/

jQuery( document ).ready( function($) {
    $( document ).on( 'click', '#aodsi_button_to_clear_si_data', function() {

        if (confirm( aodsi_clear_ajax_obj.aodsi_clear_string ) ) {

            // This does the ajax request
            $.ajax({
                url: aodsi_clear_ajax_obj.ajaxurl,
                data: {
                    'action': 'aodsi_clear_ajax_request',
                    'nonce' : aodsi_clear_ajax_obj.nonce
                },

                success:function(data) {
                location.reload();
                },

                error: function(errorThrown){
                    console.log(errorThrown);
                }

            });
        }
    });
});
