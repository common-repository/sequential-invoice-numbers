<?php 
/*
code to be run on uninstall 
*/
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}


global $wpdb;
$aodsi_table_name = $wpdb->prefix . 'artofdata_order_invoice';
$aodsi_sql = "DROP TABLE IF EXISTS $aodsi_table_name";
$wpdb->query( $aodsi_sql );
//deletes the table


delete_option( 'aodsi_first_si_number' );
delete_option( 'aodsi_si_what_statuses' );
delete_option( 'aodsi_si_email_number' );


//deletes the options
$aodsi_table = $wpdb->prefix.'postmeta';
$wpdb->delete ($aodsi_table, array('meta_key' => 'aodsi_invoice_no'));