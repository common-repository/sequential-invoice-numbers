//removes the need to press ctrl for the multiselect

jQuery( document ).ready( function($) {
$( '#aodsi_si_what_statuses option' ).mousedown( function(e) {

    e.preventDefault();
    var AodsiOriginalScrollTop = $( this ).parent().scrollTop();
    console.log( AodsiOriginalScrollTop );
    $( this ).prop( 'selected', $( this ).prop( 'selected' ) ? false : true );

    var AodsiSelf = this;
    $( this ).parent().focus();
    setTimeout( function() {
        $( AodsiSelf ).parent().scrollTop( AodsiOriginalScrollTop );
    }, 0 );

    return false;
});
});