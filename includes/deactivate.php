<?php
/*
code to be run on deactivation
*/

class Aodsin_Deactivate {
	public static function deactivate() {

		if ( ! current_user_can( 'activate_plugins' ) ) {
	        return;
	    }
	    
		remove_menu_page( 'aodsi-suite' );
		flush_rewrite_rules();

	}
}