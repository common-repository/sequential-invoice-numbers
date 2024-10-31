<?php

/*adds main functionality of the plugin, i.e inserts into the table for plugin*/


defined('ABSPATH') or die();

add_filter( 'woocommerce_email_styles', 'aodsi_add_css_to_emails', 9999, 2 );
function aodsi_add_css_to_emails( $css, $email ) { 
//additional css for woocommerce order emails so that content is on the same line as other content

   $css .= '
      h2 { display:inline-block;  }
   ';
   return $css;

}



add_action( 'woocommerce_email_before_order_table', 'aodsi_email_add_seq_invoices', 999, 1 );
function aodsi_email_add_seq_invoices( $order ) {
//adds invoice number to woocommerce emails

    global $wpdb;
    if ( get_option( 'aodsi_si_email_number' ) == 'yes' ) {

        $aodsi_o_id = $order->get_id();
        $aodsi_table_name = $wpdb->prefix."artofdata_order_invoice";

        $aodsi_invoice_no = $wpdb->get_var( $wpdb->get_results( "SELECT order_id FROM $aodsi_table_name WHERE woocom_order_id_f = $aodsi_o_id", $aodsi_o_id) );


        if ( $aodsi_invoice_no !== null ) {

            $aodsi_email_invoice_number_text = __( '[Invoice No. ' . $aodsi_invoice_no . ']', 'sequential-invoice-numbers' ); 

            echo '<h2>' . $aodsi_email_invoice_number_text . '</h2>';


        }

    }
    
}



add_action( 'after_setup_theme', 'aodsi_mytheme_add_woocommerce_support' );
function aodsi_mytheme_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}




function artofdata_add_new_order_admin_list_column( $columns ) {
//adds Invoice number to columns on orders list
    $columns['Invoice_no'] = __( 'Invoice No.', 'sequential-invoice-numbers' );
    return $columns;
}
add_filter( 'manage_edit-shop_order_columns', 'artofdata_add_new_order_admin_list_column' );





function artofdata_add_new_order_admin_list_column_content( $column, $post_id ) {
//puts the invoice numbers into the column

    global $wpdb ,$aodsi_table_name ,$aodsi_invoice_no ,$aodsi_o_id;

    if ( 'Invoice_no' === $column ) {

        $aodsi_order = wc_get_order( $post_id );
        $aodsi_o_id = $aodsi_order->get_id();

        if ( $aodsi_o_id > 0 ) {
            $aodsi_table_name = $wpdb->prefix."artofdata_order_invoice";
            $aodsi_invoice_no = $wpdb->get_var( $wpdb->get_results( "SELECT order_id FROM $aodsi_table_name WHERE woocom_order_id_f = $aodsi_o_id", $aodsi_o_id) );

            if ( $aodsi_invoice_no > 0 ) {


                if ( ! add_post_meta( $post_id, 'aodsi_invoice_no', $aodsi_invoice_no, true ) ) {
                   update_post_meta ( $post_id, 'aodsi_invoice_no', $aodsi_invoice_no );
                }
                $aodsi_invoice_col_value = $aodsi_invoice_no;
                $aodsi_invoice_col_value = apply_filters( 'aodsi_invoice_col_value', $aodsi_invoice_col_value );
 
            
            } else {
                if ( ! add_post_meta( $post_id, 'aodsi_invoice_no', 0, true ) ) {
                   update_post_meta ( $post_id, 'aodsi_invoice_no', 0 );
                }
                $aodsi_invoice_col_value = 0;
                // echo "0";
            }
        echo $aodsi_invoice_col_value;
        }



    }


}
add_action( 'manage_shop_order_posts_custom_column', 'artofdata_add_new_order_admin_list_column_content',10,2 );


add_filter( 'manage_edit-shop_order_sortable_columns', 'aodsi_si_sortable_invoice_number_column' );
function aodsi_si_sortable_invoice_number_column( $columns ) {
    $columns['Invoice_no'] = 'invoice_no';

    return $columns;
}

add_action( 'pre_get_posts', 'aodsi_invoice_number_orderby' );
function aodsi_invoice_number_orderby( $query ) {
    if( ! is_admin() )
        return;

    $aodsi_orderby = $query->get( 'orderby');

    if( 'invoice_no' == $aodsi_orderby ) {
        $query->set('meta_key','aodsi_invoice_no');
        $query->set('orderby','meta_value_num');
    }
}




$aodsi_si_status_check = get_option( 'aodsi_si_what_statuses' );
if ( !empty( $aodsi_si_status_check ) ) {
//fixes unexpected output error as not an array on activation

    foreach ( $aodsi_si_status_check as &$aodsi_si_status_check_minus_3 ) {
    //hooks the following function to status change hooks indicated by the above option
        if (substr($aodsi_si_status_check_minus_3, 0, 3) === 'wc-') {
            $aodsi_si_status_check_minus_3 = substr( $aodsi_si_status_check_minus_3, 3 );
        }

        add_action( "woocommerce_order_status_" . $aodsi_si_status_check_minus_3, "aodsi_order_status_change" , 1, 1 );
    }

}


function aodsi_order_status_change( $order_id ) {
//adds data to table if changed to relevant status
    
    global $wpdb, $aodsi_order, $aodsi_orderstring, $aodsi_table_name, $aodsi_o_id, $aodsi_ecomstatus, $aodsi_invoice_no;

    $aodsi_order      = new WC_Order( $order_id );
    $aodsi_o_id       = $aodsi_order->get_id();
    $aodsi_ecomstatus = $aodsi_order->get_status();
    $aodsi_table_name = $wpdb->prefix."artofdata_order_invoice";

    $aodsi_invoice_no = $wpdb->get_var( $wpdb->get_results( "SELECT order_id FROM $aodsi_table_name WHERE woocom_order_id_f = $aodsi_o_id", $aodsi_o_id) );


    if( $aodsi_invoice_no === null ) {
    //retrieves data and puts it into table

        $aodsi_ecomstatus               = wc_get_order_statuses()[ 'wc-' . $aodsi_ecomstatus ];
        $aodsi_billingcompany           = $aodsi_order->get_billing_company();
        $aodsi_billingname              = $aodsi_order->get_billing_first_name();
        $aodsi_billingsurname           = $aodsi_order->get_billing_last_name();
        $aodsi_billingaddress1          = $aodsi_order->get_billing_address_1();
        $aodsi_billingaddress2          = $aodsi_order->get_billing_address_2();
        $aodsi_billingcity              = $aodsi_order->get_billing_city();
        $aodsi_billingstate             = $aodsi_order->get_billing_state();
        $aodsi_billingpostcode          = $aodsi_order->get_billing_postcode();
        $aodsi_billingcountry           = $aodsi_order->get_billing_country();
        $aodsi_billingemail             = $aodsi_order->get_billing_email();
        $aodsi_billingphone             = $aodsi_order->get_billing_phone();
        $aodsi_billingphone             = strval( $aodsi_billingphone );
        $aodsi_shippingname             = $aodsi_order->get_shipping_first_name();
        $aodsi_shippingsurname          = $aodsi_order->get_shipping_last_name();
        $aodsi_shippingcompany          = $aodsi_order->get_shipping_company();
        $aodsi_shippingaddress1         = $aodsi_order->get_shipping_address_1();
        $aodsi_shippingaddress2         = $aodsi_order->get_shipping_address_2();
        $aodsi_shippingcity             = $aodsi_order->get_shipping_city();
        $aodsi_shippingstate            = $aodsi_order->get_shipping_state();
        $aodsi_shippingpostcode         = $aodsi_order->get_shipping_postcode();
        $aodsi_shippingcountry          = $aodsi_order->get_shipping_country();
        $aodsi_shippingtelephone = get_post_meta( $aodsi_o_id, '_shipping_phone', true );
        $aodsi_ecommethod               = $aodsi_order->get_payment_method();




        $aodsi_baskettotal               = number_format($aodsi_order->get_total(), 2, '.', ''); //TOTAL ORDER GROSS
        $aodsi_baskettax                 = number_format($aodsi_order->get_cart_tax(), 2, '.', ''); //PRODUCT TAX
        $aodsi_basketshipping            = number_format($aodsi_order->get_shipping_total(), 2, '.', ''); //NET
        $aodsi_basketshippingtax         = number_format($aodsi_order->get_shipping_tax(), 2, '.', ''); //TAX ONLY
        $aodsi_basketshipping_with_tax = $aodsi_basketshipping + $aodsi_basketshippingtax;
        $aodsi_shippingnote              = $aodsi_order->get_customer_note();

        // Iterate Through tax Items
        $aodsi_rate_label = array();
        foreach( $aodsi_order->get_items('tax') as $aodsi_item ){

            $aodsi_item_name = $aodsi_item->get_name();

            // array of tax codes labeled by rate ID, used later in orderstring
            $aodsi_rate_id   = $aodsi_item->get_rate_id();
            $aodsi_rate_label [ $aodsi_rate_id ]  = $aodsi_item->get_label();
        }

        //iterate through products
        $aodsi_items = $aodsi_order->get_items();
        $aodsi_orderstring = '';
        foreach ( $aodsi_items as $aodsi_item ) {
            // Store Product ID
            $aodsi_product_id              = $aodsi_item['product_id'];
            $aodsi_product                 = new WC_Product($aodsi_item['product_id']);
            $aodsi_projectsku              = $aodsi_product->get_sku();
            $aodsi_productquantity         = $aodsi_item->get_quantity();
            $aodsi_productname             = $aodsi_item->get_name();
            $aodsi_producturl              = $aodsi_product->get_permalink();
            $aodsi_productlinepricenet     = $aodsi_item->get_total();
            $aodsi_productunitpricenet     = ($aodsi_productlinepricenet)/$aodsi_productquantity;
            $aodsi_productlinepricenet     = number_format($aodsi_productlinepricenet, 2, '.', '');
            $aodsi_productunitpricenet     = number_format($aodsi_productunitpricenet, 2, '.', '');


            $aodsi_terms = get_the_terms( $aodsi_product_id, 'product_cat' );
            foreach ( $aodsi_terms as $aodsi_term ) {
                // Categories by slug
                $aodsi_product_cat_slug= $aodsi_term->slug;
            }
            $aodsi_taxes = $aodsi_item->get_taxes();
    // Loop through taxes array to get the right label

            $aodsi_tax_codes = '';
            foreach( $aodsi_taxes['subtotal'] as $aodsi_rate_id => $aodsi_tax ){

                $aodsi_tax_label = $aodsi_tax_items_labels[$aodsi_rate_id];

                if ( !empty( $aodsi_tax ) ) {
                    $aodsi_tax_codes .= $aodsi_rate_label[ $aodsi_rate_id ] . '|';

                }

            }

            $aodsi_tax_codes = substr($aodsi_tax_codes, 0, -1);

            // Concatenate order item values
            $aodsi_orderstring = $aodsi_orderstring.$aodsi_projectsku.'|'.$aodsi_productname.'|'.$aodsi_productquantity.'|'.$aodsi_productunitpricenet.'|'.$aodsi_productlinepricenet.'|'.$aodsi_tax_codes.'#';

            $aodsi_orderstring = utf8_encode($aodsi_orderstring);
        }




        $aodsi_table_name = $wpdb->prefix . "artofdata_order_invoice";
        $wpdb->insert(
            $aodsi_table_name,
            array (
                'ecom_status' => 'ok',
                'ecom_status_detail' => $aodsi_ecomstatus,
                'invoice_amount' => $aodsi_baskettotal,
                'invoice_delivery' => $aodsi_basketshipping_with_tax,
                'invoice_basket_tax' => $aodsi_baskettax,
                'invoice_shipping_tax' => $aodsi_basketshippingtax,
                'bill_company' => $aodsi_billingcompany,
                'bill_name' => $aodsi_billingname,
                'bill_surname' => $aodsi_billingsurname,
                'bill_address_1' => $aodsi_billingaddress1,
                'bill_address_2' => $aodsi_billingaddress2,
                'bill_city' => $aodsi_billingcity,
                'bill_state_county' => $aodsi_billingstate,
                'bill_country' => $aodsi_billingcountry,
                'bill_postcode_zip' => $aodsi_billingpostcode,
                'bill_telephone' => $aodsi_billingphone,
                'bill_email' => $aodsi_billingemail,
                'shipping_company' => $aodsi_shippingcompany,
                'shipping_name' => $aodsi_shippingname,
                'shipping_surname' => $aodsi_shippingsurname,
                'shipping_address_1' => $aodsi_shippingaddress1,
                'shipping_address_2' => $aodsi_shippingaddress2,
                'shipping_city' => $aodsi_shippingcity,
                'shipping_state_county' => $aodsi_shippingstate,
                'shipping_country' => $aodsi_shippingcountry,
                'shipping_postcode_zip' => $aodsi_shippingpostcode,
                'shipping_telephone' => $aodsi_shippingtelephone,
                'shipping_note' => $aodsi_shippingnote,
                'woocom_order_id_f' => $aodsi_o_id,
                'the_order_string' => $aodsi_orderstring,
            )
        );


        update_option( 'aodsi_first_si_number', $wpdb->get_var( 'SELECT order_id FROM ' . $wpdb->prefix . 'artofdata_order_invoice' . ' ORDER BY order_id DESC LIMIT 1') + 1);

    }


}









add_action( 'woocommerce_update_order',  'aodsi_update_old_invoice' );

function aodsi_update_old_invoice( $order_id ) {
//updates data in table when it has already been created


    global $wpdb, $aodsi_order, $aodsi_table_name, $aodsi_o_id, $aodsi_ecomstatus, $aodsi_invoice_no, $aodsi_orderstring;

    $aodsi_order      = new WC_Order($order_id);
    $aodsi_o_id       = $aodsi_order->get_id();
    $aodsi_ecomstatus = $aodsi_order->get_status();
    $aodsi_table_name = $wpdb->prefix."artofdata_order_invoice";

    $aodsi_invoice_no = $wpdb->get_var( $wpdb->get_results( "SELECT order_id FROM $aodsi_table_name WHERE woocom_order_id_f = $aodsi_o_id", $aodsi_o_id ) );


    if ( $aodsi_invoice_no !== null ) {
    //not null i.e an invoice number and table has already been created
                                                                                                                                               
        $aodsi_ecomstatus = wc_get_order_statuses()[ 'wc-' . $aodsi_ecomstatus ];
        

        // Initializing variables


        $aodsi_billingcompany           = $aodsi_order->get_billing_company();
        $aodsi_billingname              = $aodsi_order->get_billing_first_name();
        $aodsi_billingsurname           = $aodsi_order->get_billing_last_name();
        $aodsi_billingaddress1          = $aodsi_order->get_billing_address_1();
        $aodsi_billingaddress2          = $aodsi_order->get_billing_address_2();
        $aodsi_billingcity              = $aodsi_order->get_billing_city();
        $aodsi_billingstate             = $aodsi_order->get_billing_state();
        $aodsi_billingpostcode          = $aodsi_order->get_billing_postcode();
        $aodsi_billingcountry           = $aodsi_order->get_billing_country();
        $aodsi_billingemail             = $aodsi_order->get_billing_email();   
        $aodsi_billingphone             = $aodsi_order->get_billing_phone();
        $aodsi_billingphone             = strval($aodsi_billingphone);
        $aodsi_shippingname             = $aodsi_order->get_shipping_first_name();
        $aodsi_shippingsurname          = $aodsi_order->get_shipping_last_name();
        $aodsi_shippingcompany          = $aodsi_order->get_shipping_company();
        $aodsi_shippingaddress1         = $aodsi_order->get_shipping_address_1();
        $aodsi_shippingaddress2         = $aodsi_order->get_shipping_address_2();
        $aodsi_shippingcity             = $aodsi_order->get_shipping_city();
        $aodsi_shippingstate            = $aodsi_order->get_shipping_state();
        $aodsi_shippingpostcode         = $aodsi_order->get_shipping_postcode();
        $aodsi_shippingcountry          = $aodsi_order->get_shipping_country();
        $aodsi_shippingtelephone        = get_post_meta( $aodsi_o_id, '_shipping_phone', true );
        $aodsi_ecommethod               = $aodsi_order->get_payment_method();



        $aodsi_baskettotal           = number_format($aodsi_order->get_total(), 2, '.', ''); //TOTAL ORDER GROSS
        $aodsi_baskettax             = number_format($aodsi_order->get_cart_tax(), 2, '.', ''); //PRODUCT TAX
        $aodsi_basketshipping        = number_format($aodsi_order->get_shipping_total(), 2, '.', ''); //NET
        $aodsi_basketshippingtax     = number_format($aodsi_order->get_shipping_tax(), 2, '.', ''); //TAX ONLY
        $aodsi_basketshipping_with_tax = $aodsi_basketshipping + $aodsi_basketshippingtax;
        $aodsi_shippingnote          = $aodsi_order->get_customer_note();

        // Iterate Through tax Items
        $aodsi_rate_label = array();
        foreach( $aodsi_order->get_items('tax') as $aodsi_item ){

            $aodsi_item_name = $aodsi_item->get_name();

            // array of tax codes labeled by rate ID, used later in orderstring
            $aodsi_rate_id   = $aodsi_item->get_rate_id();
            $aodsi_rate_label [ $aodsi_rate_id ]  = $aodsi_item->get_label();
        }

        //iterate through products
        $aodsi_items = $aodsi_order->get_items();
        $aodsi_orderstring = '';
        foreach ( $aodsi_items as $aodsi_item ) {
            // Store Product ID
            $aodsi_product_id              = $aodsi_item['product_id'];
            $aodsi_product                 = new WC_Product($aodsi_item['product_id']);
            $aodsi_projectsku              = $aodsi_product->get_sku();
            $aodsi_productquantity         = $aodsi_item->get_quantity();
            $aodsi_productname             = $aodsi_item->get_name();
            $aodsi_producturl              = $aodsi_product->get_permalink();
            $aodsi_productlinepricenet     = $aodsi_item->get_total();
            $aodsi_productunitpricenet     = ($aodsi_productlinepricenet)/$aodsi_productquantity;
            $aodsi_productlinepricenet     = number_format($aodsi_productlinepricenet, 2, '.', '');
            $aodsi_productunitpricenet     = number_format($aodsi_productunitpricenet, 2, '.', '');


            $aodsi_terms = get_the_terms( $aodsi_product_id, 'product_cat' );
            foreach ( $aodsi_terms as $aodsi_term ) {
                // Categories by slug
                $aodsi_product_cat_slug= $aodsi_term->slug;
            }
            $aodsi_taxes = $aodsi_item->get_taxes();
    // Loop through taxes array to get the right label

            $aodsi_tax_codes = '';
            foreach( $aodsi_taxes['subtotal'] as $aodsi_rate_id => $aodsi_tax ){

                $aodsi_tax_label = $aodsi_tax_items_labels[$aodsi_rate_id];

                if ( !empty( $aodsi_tax ) ) {
                    $aodsi_tax_codes .= $aodsi_rate_label[ $aodsi_rate_id ] . '|';

                }

            }

            $aodsi_tax_codes = substr($aodsi_tax_codes, 0, -1);

            // Concatenate order item values
            $aodsi_orderstring = $aodsi_orderstring.$aodsi_projectsku.'|'.$aodsi_productname.'|'.$aodsi_productquantity.'|'.$aodsi_productunitpricenet.'|'.$aodsi_productlinepricenet.'|'.$aodsi_tax_codes.'#';

            $aodsi_orderstring = utf8_encode($aodsi_orderstring);
        }






        $aodsi_table_name = $wpdb->prefix . "artofdata_order_invoice";
        $wpdb->update(
            $aodsi_table_name, 
            array (
                'ecom_status' => 'ok',
                'ecom_status_detail' => $aodsi_ecomstatus,
                'invoice_amount' => $aodsi_baskettotal,
                'invoice_delivery' => $aodsi_basketshipping_with_tax,
                'invoice_basket_tax' => $aodsi_baskettax,
                'invoice_shipping_tax' => $aodsi_basketshippingtax,
                'bill_company' => $aodsi_billingcompany,
                'bill_name' => $aodsi_billingname,
                'bill_surname' => $aodsi_billingsurname,
                'bill_address_1' => $aodsi_billingaddress1,
                'bill_address_2' => $aodsi_billingaddress2,
                'bill_city' => $aodsi_billingcity,
                'bill_state_county' => $aodsi_billingstate,
                'bill_country' => $aodsi_billingcountry,
                'bill_postcode_zip' => $aodsi_billingpostcode,
                'bill_telephone' => $aodsi_billingphone,
                'bill_email' => $aodsi_billingemail,
                'shipping_company' => $aodsi_shippingcompany,
                'shipping_name' => $aodsi_shippingname,
                'shipping_surname' => $aodsi_shippingsurname,
                'shipping_address_1' => $aodsi_shippingaddress1,
                'shipping_address_2' => $aodsi_shippingaddress2,
                'shipping_city' => $aodsi_shippingcity,
                'shipping_state_county' => $aodsi_shippingstate,
                'shipping_country' => $aodsi_shippingcountry,
                'shipping_postcode_zip' => $aodsi_shippingpostcode,
                'shipping_telephone' => $aodsi_shippingtelephone,
                'shipping_note' => $aodsi_shippingnote,
                'woocom_order_id_f' => $aodsi_o_id,
                'the_order_string' => $aodsi_orderstring,
            ),
            array('woocom_order_id_f' => $aodsi_o_id),
            array('%s',
                '%s',
                '%f',
                '%f',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
            ),
            array( '%d' )
        ); 

    }

}
