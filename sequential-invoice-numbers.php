<?php
/*


Plugin Name: Sequential Invoice Numbers
Plugin URI: https://www.artofdata.com/wp-plugins/woocommerce/sequential-invoice-numbers-plugin/     

Description: A plugin that adds sequential invoice numbers to your woocommerce orders, also serves as a foundation for other AOD invoice plugins.

Version:           1.0.2
Requires at least: 5.5
Requires PHP: 7.0
Author:            Joseph Parry, Peter Lister
Author URI:    https://www.artofdata.com/wp-plugins/authors/
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       sequential-invoice-numbers
Domain Path:       /languages



*/
 


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


require_once plugin_dir_path( __FILE__ ).'includes/activation.php';
register_activation_hook( __FILE__, array( 'Aodsin_Activate', 'activate' ) );
//activation hook

require_once plugin_dir_path( __FILE__ ).'includes/deactivate.php';
register_deactivation_hook( __FILE__, array( 'Aodsin_Deactivate', 'deactivate' ) );
//deactivation hook


if ( class_exists( 'Aodsi_Sequential_Invoicing' ) ) {	

	$aodsi_sequential_invoices = new Aodsi_Sequential_Invoicing();
	$aodsi_sequential_invoices->register();
	//initialises the class and runs the register function 

}





class Aodsi_Sequential_Invoicing {

	public $aodsi_si_plugin_name, $aodsi_first_si, $aodsi_si_statuses, $aodsi_si_table_name;

		

	function __construct() {
		global $wpdb;

		$this->aodsi_si_table_name = $wpdb->prefix . 'artofdata_order_invoice';
		//invoice table name

		$this->aodsi_si_plugin_name = plugin_basename( __FILE__ );
		// plugin file location

		$this->aodsi_first_si = '';
		//aodsi first si setting value

		$this->aodsi_si_statuses = array();
		// aodsi si statuses setting value

	}



	function register() {
	// hook functions and require files


		add_action( 'admin_enqueue_scripts', array( $this, 'aodsi_enqueue' ) );
		//enqueue styles/scripts

		add_action( 'wp_ajax_aodsi_clear_ajax_request', array( $this, 'aodsi_clear_ajax_request' ) ); //function for clear table ajax call

		add_filter( "plugin_action_links_$this->aodsi_si_plugin_name", array( $this, 'aodsi_settings_link' ) );
		//settings link

		add_action( 'update_option_aodsi_first_si_number', array( $this, 'aodsi_update_first_in_no' ) );
		//when next invoice number is changed applies it


		add_action( 'admin_menu', array( $this, 'aodsi_suite_page' ), 900 );
		add_action( 'admin_init', array( $this, 'aodsi_add_separators_admin_menu' ) );
			//adds aod suite plugin page and positions it correctly


		require_once plugin_dir_path( __FILE__ ).'includes/table-exists.php';
		//checks if the table exists, if not takes action

		require_once plugin_dir_path( __FILE__ ).'includes/woocommerce-settings.php';
		//settings in woocommerce

		require_once plugin_dir_path( __FILE__ ).'includes/main.php';
		//adds main code for functionality

		require_once plugin_dir_path( __FILE__ ).'includes/woo-si-order-info.php';
		//adds invoice info to order page
	}



	function aodsi_plugin_list(){
		//aod plugins array

		$aodsi_plugin_list = array(
				
			array(
				'plugin_name'=> __( 'Sequential Invoice Numbers', 'sequential-invoice-numbers' ),
				'description'=> __(  'A plugin that adds sequential invoice numbers to your woocommerce orders, also serves as a foundation for other AOD invoice plugins.', 'sequential-invoice-numbers' ),
				'status'=> $this->aodsi_plugin_status( $this->aodsi_si_plugin_name ), 
				'link' => 'admin.php?page=wc-settings&tab=aodsi_invoices&section=', //settings link
				'install_link'=>'irrelevant',
			),

			array('plugin_name'=> __( 'Coming Soon', 'sequential-invoice-numbers' ),
				'description'=> __( 'Coming Soon', 'sequential-invoice-numbers' ),
				'status'=> __( 'Coming Soon', 'sequential-invoice-numbers' ),
				'link'=>'irrelevant',
				'install_link'=>'irrelevant',
			),//delete coming soon once all released		

		);


		$aodsi_plugin_list = apply_filters( 'aodsi_plugin_list', $aodsi_plugin_list );
		//filter for array of plugins


		return $aodsi_plugin_list;

	}




	function aodsi_enqueue() {

		wp_register_style( 'aodsi_sequential-invoices_dashicons', plugins_url( 'assets/css/aodsi-sequential-invoices.css', __FILE__ ), array(), 1 );
		wp_enqueue_style( 'aodsi_sequential-invoices_dashicons' );
		//enqueues the aodsi logo dashicon

		wp_register_style( 'aodsi_si_styles', plugins_url( 'assets/css/aodsi-si-styles.css', __FILE__ ), array(), 1 );
		wp_enqueue_style( 'aodsi_si_styles' );
		//enqueue css for this plugin

		wp_enqueue_script(
			'aodsi_clear_ajax-script',
			plugins_url( 'assets/js/jquery.aodsi-clearing.js', __FILE__ ),
			array( 'jquery', 'wp-i18n' ),
			1
		);
		wp_enqueue_script(
			'aodsi_no_ctrl',
			plugins_url( 'assets/js/jquery.aodsi-no-ctrl.js', __FILE__ ),
			array( 'jquery', 'wp-i18n' ),
			1
		);
		// Enqueues javascript

		wp_localize_script(
			'aodsi_clear_ajax-script',
			'aodsi_clear_ajax_obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce('aodsi-clear-nonce'),
				'aodsi_clear_string' => __( 'Are you sure? This operation is not easily reversed.', 'sequential-invoice-numbers'),
			)
		);
		//localise ajax

	}



	function aodsi_si_what_statuses_render(){
	//the content for the settings field aodsi_what_statuses

		echo "<select id='aodsi_si_what_statuses' name='aodsi_si_what_statuses[]' type='multiselect'   multiple='multiple'>";
		
		$aodsi_woo_list_of_statuses = wc_get_order_statuses();
		foreach ($aodsi_woo_list_of_statuses as $aodsi_wc_status => $aodsi_status_name) {
			
			echo "<option value='$aodsi_wc_status' ";

			if ( in_array($aodsi_wc_status, $this->aodsi_si_statuses) ) {
				echo 'selected' ;
			}

			echo ">$aodsi_status_name</option>";

		}

		echo '</select>';

		$aodsi_si_status_desc = __( 'On what statuses should an invoice number be created if it does not already exist? (Default: Processing and Completed, this should really only be changed if you install a plugin that adds more statuses)', 'sequential-invoice-numbers' );

		$aodsi_si_status_desc = apply_filters( 'aodsi_si_status_desc', $aodsi_si_status_desc );

		echo "<p style=' font-weight: 400; 'class='description' >$aodsi_si_status_desc</p>";
	}



	public function aodsi_settings_link( $links ){
	//adds a settings link to the submenu  

	//woo settings: admin.php?page=wc-settings&tab=aodsi_invoices&section= (currently used)
	//dashboard: admin.php?page=aod_suite 

		$aodsi_si_settings_link_text = __( 'Settings', 'sequential-invoice-numbers' );
		$aodsi_settings_link = "<a href='admin.php?page=wc-settings&tab=aodsi_invoices&section=''>$aodsi_si_settings_link_text</a>";
		array_push( $links, $aodsi_settings_link );
		return $links;
	}


	function aodsi_suite_page(){	
	//adds aodsi suite page and its dashboard

		add_menu_page( __( 'AOD Suite', 'sequential-invoice-numbers' ), 
			__( 'AOD Suite', 'sequential-invoice-numbers' ), 
			'manage_options', 
			'aodsi_suite', 
			array( $this, 'aodsi_suite_page_content' ),
			'dashicons-aodsi-suite-icon',
			58,
		);
		//aod suite

		add_submenu_page( 'aodsi_suite',
			__( 'Dashboard', 'sequential-invoice-numbers' ),
			__( 'Dashboard', 'sequential-invoice-numbers' ),
			'manage_options', 
			'aodsi_suite', 
			array( $this, 'aodsi_suite_page_dashboard_content' ),
			);
		//dashboard
	}



	function aodsi_suite_page_content() {
		//aod suite has no content; content is in the dashboard
	}



	function aodsi_suite_page_dashboard_content(){
	//content for aod suite's dashboard

		$aodsi_plugin_list = $this->aodsi_plugin_list();
		//gets the array of aodsi plugins

		require_once plugin_dir_path( __FILE__ ).'templates/have-you-woo.php';
		//aditional html to ask for woocommerce	
 
		require_once plugin_dir_path( __FILE__ ).'templates/Dashboard.php';
		//html for the page
	}



	function aodsi_plugin_status( $directory ) {
		//retrieves plugin status, input=path to base plugin file from plugins folder

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if( is_plugin_active( $directory ) ) {
			$aodsi_activity_result = __( 'Active', 'sequential-invoice-numbers' );

		} elseif ( isset( get_plugins()[ $directory ] ) ) {
			$aodsi_activity_result = __( 'Deactivated', 'sequential-invoice-numbers' );

		} else {
			$aodsi_activity_result = __( 'Not Installed', 'sequential-invoice-numbers' );
		}

		return $aodsi_activity_result;
	}




	function aodsi_add_separators_admin_menu() {
	//adds seperator under wcmarketing if there isnt one 

	    $aodsi_separatorsAfter = ['Marketing'];
		 
	    global $menu;

	    if ( is_admin() ) {
	        foreach( $aodsi_separatorsAfter as $aodsi_s ) {
	            foreach ( $menu as $aodsi_key => $aodsi_item ) {
	                if ( strpos( $aodsi_item[0], $aodsi_s ) !== false ) {
	                	if ( $menu[ $aodsi_key+1 ] !== array( 
	                		0 => '',
	                        1 => 'read',
	                        2 => 'separator-last',
	                        3 => '',
	                        4 => 'wp-menu-separator' ) ) 
	                	{
	                	
		                    array_splice( $menu, $aodsi_key+1 , 0, array( array(
		                        0 => '',
		                        1 => 'read',
		                        2 => 'separator-last',
		                        3 => '',
		                        4 => 'wp-menu-separator'
		                    )));
		                    break;
	                	}
	                }
	            }
	        }
	    }
	}


	public function aodsi_update_first_in_no() {
	//applies setting change of next invoice number
			
		$aodsi_new_increment = get_option( 'aodsi_first_si_number' );

		global $wpdb;

		$aodsi_table_name = $wpdb->prefix.'artofdata_order_invoice';		
		$wpdb->query( "ALTER TABLE $aodsi_table_name AUTO_INCREMENT = $aodsi_new_increment;" );



	}

		
	function aodsi_clear_ajax_request() {
		//function for jquery.aodsi_clearing.js

	    if ( ! check_ajax_referer( 'aodsi-clear-nonce', 'nonce' ) ) {
	        die( __( 'Nonce value cannot be verified.', 'sequential-invoice-numbers' ) );
	    }


		global $wpdb, $aodsi_cleared_text;
		$aodsi_table_name = $wpdb->prefix.'artofdata_order_invoice';
		$wpdb->query( "TRUNCATE TABLE $aodsi_table_name" );
		//clear table		

		$aodsi_count_query = 'select count(*) from $aodsi_table_name';
		$aodsi_num = $wpdb->get_var( $aodsi_count_query );

		$aodsi_table = $wpdb->prefix.'postmeta';
   		$wpdb->delete ($aodsi_table, array('meta_key' => 'aodsi_invoice_no'));
   		
		if ( $aodsi_num == 0 ) {

			update_option( 'aodsi_first_si_number', 1 );

		} else {

			die( __( 'Could not clear table.', 'sequential-invoice-numbers' ) );

		}

		do_action( 'aodsi_si_clear_table' );

	   die();

	}


}


