<?php

/*
code to be run on activation
*/
class Aodsin_Activate {


	public static function activate() {

		if ( ! current_user_can( 'activate_plugins' ) ) {
	        return;
	    }

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {	
			// deactivate_plugins( plugin_basename( __FILE__ ) );
			// unset( $_GET['activate'] ); // Input var okay.	
			echo '<p>' . __( 'You must activate/install woocommerce!', 'sequential-invoice-numbers' ) . '</p>';
        	exit;	
		}

		flush_rewrite_rules();

		if ( ! get_option('aodsi_si_what_statuses' ) ) {
		//creates default for aodsi_si_what_statuses option if needed

			add_option(
				'aodsi_si_what_statuses',
				array(
					'wc-processing',
					'wc-completed',
				)
			);


		}


		if ( ! get_option( 'aodsi_si_email_number' ) ) {
		//creates default for aodsi_si_email_number option if needed

			add_option( 'aodsi_si_email_number', 'yes' );

		}


	}

}
//Alter table
