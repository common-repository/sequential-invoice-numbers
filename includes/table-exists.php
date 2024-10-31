<?php

global $wpdb;
$aodsi_table_name = $wpdb->prefix.'artofdata_order_invoice';

if( $wpdb->get_var("SHOW TABLES LIKE '$aodsi_table_name'") != $aodsi_table_name) {
//if the table does not exist it is created

	$aodsi_charset = $wpdb->get_charset_collate();
	$aodsi_sql = "CREATE TABLE IF NOT EXISTS $aodsi_table_name (
		order_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		tds TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		ecom_status VARCHAR(10) NOT NULL,
		ecom_status_detail VARCHAR(255) NOT NULL,
		invoice_amount DECIMAL(13, 4),
		invoice_delivery DECIMAL(13, 4),
		invoice_basket_tax DECIMAL(13, 4),
		invoice_shipping_tax DECIMAL(13, 4),
		bill_company VARCHAR(255),
		bill_name VARCHAR(255) NOT NULL,
		bill_surname VARCHAR(255) NOT NULL,
		bill_address_1 VARCHAR(255) NOT NULL,
		bill_address_2 VARCHAR(255),
		bill_city VARCHAR(255) NOT NULL,
		bill_state_county VARCHAR(255),
		bill_country VARCHAR(255) NOT NULL,
		bill_postcode_zip VARCHAR(255) NOT NULL,
		bill_telephone VARCHAR(255),
		bill_email VARCHAR(255),
		shipping_company VARCHAR(255),
		shipping_name VARCHAR(255) NOT NULL,
		shipping_surname VARCHAR(255) NOT NULL,
		shipping_address_1 VARCHAR(255) NOT NULL,
		shipping_address_2 VARCHAR(255) NOT NULL,
		shipping_city VARCHAR(255) NOT NULL,
		shipping_state_county VARCHAR(255),
		shipping_country VARCHAR(255) NOT NULL,
		shipping_postcode_zip VARCHAR(255) NOT NULL,
		shipping_telephone VARCHAR(255),
		shipping_note VARCHAR(255),
		woocom_order_id_f INT,
		the_order_string VARCHAR(5000)
		) AUTO_INCREMENT = 1 $aodsi_charset;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $aodsi_sql );

	update_option( 'aodsi_first_si_number', 1 );
	//puts the option for the next invoice number (back) to 1	
}