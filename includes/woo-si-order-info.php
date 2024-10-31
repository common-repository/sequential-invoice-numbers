<?php

add_action( 'woocommerce_admin_order_data_after_order_details', 'aodsi_editable_order_meta_general' );
 
function aodsi_editable_order_meta_general( $order ){ 
//adds invoice info to edit/add order page 

 	global $post,$wpdb;


    $aodsi_o_id = $order->get_id();

    $aodsi_table_name = $wpdb->prefix.'artofdata_order_invoice';
    $aodsi_order_invoice_number = '#'.$wpdb->get_var( $wpdb->get_results( "SELECT order_id FROM $aodsi_table_name WHERE woocom_order_id_f = $aodsi_o_id", $aodsi_o_id ) );



    if ( $aodsi_order_invoice_number === '#' ) {

    	$aodsi_si_status_delivery = '';

        if ( get_option( 'aodsi_si_what_statuses' ) ) {

	        $aodsi_si_status_list = get_option( 'aodsi_si_what_statuses' );
	        $aodsi_woo_list_of_statuses = wc_get_order_statuses();

	    	foreach ( $aodsi_si_status_list as $aodsi_si_status_list_item ) {   
	        	$aodsi_si_status_delivery .= $aodsi_woo_list_of_statuses[ $aodsi_si_status_list_item ] . '/';    
	    	}

	    	$aodsi_si_status_delivery = substr( $aodsi_si_status_delivery, 0, -1 );
	    }
	    $aodsi_order_invoice_number = '<span style="font-style: italic;"" >' . __( 'An invoice number will be generated when the status is first updated to ', 'sequential-invoice-numbers' ) . $aodsi_si_status_delivery . __( ' (this can be changed in settings)', 'sequential-invoice-numbers' ) . '</span>';
    }
    //aodsi_order_invoice_number = invoice number or instructions if there isnt one


    echo '<br class="clear" />';
   	echo '<h4>' . __( 'Invoices', 'sequential-invoice-numbers' ) . '</h4>';

    echo '<div class="address">';
    echo '<p><strong>' . __( 'Invoice Number:', 'sequential-invoice-numbers' ) . "</strong>$aodsi_order_invoice_number</p>";

 	do_action( 'aodsi_invoice_field' );

	echo '</div>';
}