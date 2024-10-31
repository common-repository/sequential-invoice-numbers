<?php
//asks the user to install/activate woocommerce if neccesary

defined('ABSPATH') or die();

echo '<div class="aodsi-have-you-woo">';

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$aodsi_woo_path='woocommerce/woocommerce.php';
			if( is_plugin_active( $aodsi_woo_path ) ) {
				//Active, this is fine;
			} elseif (isset(get_plugins()[$aodsi_woo_path])){
				//Deactivated, need to activate;
				echo '<h3>' . __( 'Please Activate Woocommerce', 'sequential-invoice-numbers
					' ) . '</h3>';
			} else{
				//Not Installed, need to install;
				echo '<h3>' .__( 'Please Install Woocommerce', 'sequential-invoice-numbers
					' ) . '</h3>';
			}



echo '</div>';