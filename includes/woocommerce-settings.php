<?php

if ( ! class_exists( 'WC_Settings_Aodsi_Invoices' ) ) :


function aodsi_invoices_add_woo_settings() {

	/**
	 * Settings class
	 *
	 * @since 1.0.0
	 */
	class WC_Settings_Aodsi_Invoices extends WC_Settings_Page {


		/**
		 * Setup settings class
		 *
		 * @since  1.0
		 */
		public function __construct() {
		global $wpdb;

			$this->id    = 'aodsi_invoices'; //tab id
			$this->label = __( 'Invoices', 'sequential-invoice-numbers' ); //tab label

			$this->aodsi_si_woo_status_desc = __( 'On what statuses should an invoice number be created if the order does not already have one? (Default: Processing and Completed, this should only be changed if a plugin is installed that adds more statuses or it is otherwise absolutely neccesary)', 'sequential-invoice-numbers' );

			$this->aodsi_si_woo_clear_desc = __( 'Clear the AOD order invoices table and remove the invoice numbers from all prior orders. The next invoice number will then return to 1.', 'sequential-invoice-numbers' );

			$this->aodsi_si_woo_status_desc = apply_filters( 'aodsi_si_woo_status_desc', $this->aodsi_si_woo_status_desc );
			$this->aodsi_si_woo_clear_desc = apply_filters( 'aodsi_si_woo_clear_desc', $this->aodsi_si_woo_clear_desc );
			//description of clear table button, may do more in future plugins


			$this->aodsi_last_id = $wpdb->get_var( 'SELECT order_id FROM ' . $wpdb->prefix . 'artofdata_order_invoice' . ' ORDER BY order_id DESC LIMIT 1') + 1;

			add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );

		}


		/**
		 * Get sections
		 *
		 * @return array
		 */
		public function get_sections() {



			$aodsi_sections = array(
				''         => __( 'Sequential Invoicing', 'sequential-invoice-numbers' ),
			);

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $aodsi_sections );
		}


		/**
		 * Get settings array
		 *
		 * @since 1.0.0
		 * @param string $current_section Optional. Defaults to empty string.
		 * @return array Array of settings
		 */
		public function get_settings( $current_section = '' ) {


				/**
				 * Filter Sequential Invoicing Settings
				 *
				 * @since 1.0.0
				 * @param array $settings Array of the plugin settings
				 */
				$settings = apply_filters( 'aodsi_invoices_section2_settings', array(

					array(
						'name' => __( 'Reset Sequential Invoice Numbers', 'sequential-invoice-numbers' ),
						'type' => 'title',
						'desc' => $this->aodsi_si_woo_clear_desc,
						'id'   => 'aodsi_invoices_sequential_invoicing_clear_options',
					),



					array(
						'type'     => 'title',
						'id'       => 'aodsi_invoices__sequential_invoicing_clear_button',
						'name'     => '',
						'desc'     => '<button class="button button-link-delete" id="aodsi_button_to_clear_si_data">' . __( 'Reset Sequential Invoice Numbers', 'sequential-invoice-numbers' ) . '</button>
						<br><br>',

					),




					array(
						'type' => 'sectionend',
						'id'   => 'aodsi_invoices_sequential_invoicing_clear_options',
					),


					array(
						'name' => __( 'Usage', 'sequential-invoice-numbers' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'aodsi_invoices_sequential_invoicing_usage',
					),


					array(
						'name'     => __( 'Include Invoice No. In Woocommerce Emails', 'sequential-invoice-numbers' ),
						'id'       => 'aodsi_si_email_number',
						'type'     => 'checkbox',
						'default' => 'yes',
					),


					array(
						'type' => 'sectionend',
						'id'   => 'aodsi_invoices_sequential_invoicing_usage',
					),


					array(
						'name' => __( 'Invoice Progression', 'sequential-invoice-numbers' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'aodsi_invoices_sequential_invoicing_progression_options',
					),


					array(
						'type'     => 'number',
						'id'       => 'aodsi_first_si_number',
						'name'     => __( 'Next Invoice Number', 'sequential-invoice-numbers' ),
						'custom_attributes'=> array( 
							'min' => $this->aodsi_last_id, 
							'max' => 2000000000,
							'step' => '1',
						),
					),


					array(
						'type' => 'multiselect',
						'id' => 'aodsi_si_what_statuses',
						'name' => __( 'Create Invoice Number On', 'sequential-invoice-numbers' ),
						'options'=> wc_get_order_statuses(),
						'default' => array('wc-processing', 'wc-completed'),
						'desc' => $this->aodsi_si_woo_status_desc,
					),


					array(
						'type' => 'sectionend',
						'id'   => 'aodsi_invoices_sequential_invoicing_progression_options',
					),
				) 
			);


			/**
			 * Filter MyPlugin Settings
			 *
			 * @since 1.0.0
			 * @param array $settings Array of the plugin settings
			 */
			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

		}


		/**
		 * Output the settings
		 *
		 * @since 1.0
		 */
		public function output() {

			global $current_section;

			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
		}


		/**
	 	 * Save settings
	 	 *
	 	 * @since 1.0
		 */
		public function save() {

			global $current_section;

			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::save_fields( $settings );
		}

	}

	return new WC_Settings_aodsi_invoices();

}
add_filter( 'woocommerce_get_settings_pages', 'aodsi_invoices_add_woo_settings', 15 );

endif;