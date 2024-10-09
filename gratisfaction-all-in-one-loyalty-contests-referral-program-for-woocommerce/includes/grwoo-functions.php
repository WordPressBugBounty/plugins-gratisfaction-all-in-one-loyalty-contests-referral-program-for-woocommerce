<?php

/**
 * Common functions.
 *
 * @author  Gratisfaction
 * @package GRWOO
 * @since   3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Is WooCommerce active?
 *
 * @since 3.0
 *
 * @return bool
 */
function grwoo_woocommerce_active() {
    if ( function_exists( 'woocommerce_active_check' ) ) {
        return woocommerce_active_check();
    }
    
    $active_plugins = (array) get_option( 'active_plugins', array() );

    if ( is_multisite() )
        $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

    return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommmerce.php', $active_plugins );
}

/**
 * Woo plugin inactive notice
 */
if ( ! function_exists( 'grwoo_plugin_inactive_notice' ) ) {
    function grwoo_plugin_inactive_notice() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        
        $notice = '<strong>' . __( 'Gratisfaction is inactive.', 'gratisfaction' ) . '</strong> ' . __( 'WooCommerce is required for Gratisfaction to work.', 'gratisfaction' );

        printf( "<div class='error'><p>%s</p></div>", $notice );
    }
}

/**
 * Woocommerce coupon disabled notice
 */
if ( ! function_exists( 'grwoo_coupon_disabled_notice' ) ) {
    function grwoo_coupon_disabled_notice() {
        $notice = '<strong>' . __( 'Woocommerce Coupon is disabled.', 'gratisfaction' ) . '</strong> ' . __( 'Enable it to work Gratisfaction coupon.', 'gratisfaction' );

        printf( "<div class='error'><p>%s</p></div>", $notice );
    }
}

if(!function_exists('gr_get_app_config')) {
    function gr_get_app_config() {
        $config         =   array();
        
        try {
            $config_file    =   GR_PLUGIN_BASE_PATH.'/configs/app.json';
            
            if(file_exists($config_file)) {
                $config_json    =   file_get_contents($config_file);
                
                if(!empty($config_json))
                    $config     =   json_decode($config_json, true);
            }
        } catch (Exception $e) {

        }
        
        return $config;
    }
}

if(!function_exists('gr_get_app_config')) {
    function gr_app_error_log($msg) {
        try {
                        
            $log_file    =   GR_PLUGIN_BASE_PATH.'/configs/error.log';

            if(!is_writable($log_file))
                throw new Exception('Config file is not created. Permission issue');

            if(file_put_contents($log_file, $msg) == FALSE) {
               
                throw new Exception('log file is not created');
            }
            
            $ret = TRUE;
        } catch (Exception $e) {
            $ret = FALSE;
        }
        
        return $ret;
    }
}

if(!function_exists('gr_set_app_config')) {
    function gr_set_app_config($config) {
        try {
            $config_json    =   json_encode($config);
            $config_file    =   GR_PLUGIN_BASE_PATH.'/configs/app.json';

            if(!is_writable($config_file))
                throw new Exception('Config file is not created. Permission issue');

            if(file_put_contents($config_file, $config_json) == FALSE) {
                $data   =   json_encode(array(
                   'config'      => $config,
                   'config_file' => $config_file,
                   'shop_id'     => get_option('grconnect_shop_id')
                ));
                
                throw new Exception('Config file is not created');
            }
            
            $ret = TRUE;
        } catch (Exception $e) {
            $ret = FALSE;
        }
        
        return $ret;
    }
}


/**
 * This function is used to get the tax for the particular product
 */
if( ! function_exists('get_formatted_product_tax_amount') ) {

    function get_formatted_product_tax_amount( $atts ) {
        // Attributes
        $atts = shortcode_atts( array(
            'id' => '0',
        ), $atts, 'tax_amount' );

        global $product, $post;

        if( ! is_object( $product ) || $atts['id'] != 0 ){
            if( is_object( $post ) && $atts['id'] == 0 )
                $product_id = $post->ID;
            else
                $product_id = $atts['id'];

            $product = wc_get_product( $product_id );
        }

        if( is_object( $product ) ){
            $price_excl_tax = wc_get_price_excluding_tax($product);
            $price_incl_tax = wc_get_price_including_tax($product);
            $tax_amount = $price_incl_tax - $price_excl_tax;

            // return wc_price($tax_amount);
            return number_format((float) $tax_amount, wc_get_price_decimals(), '.', '');
        }
    }

    add_shortcode( 'tax_amount', 'get_formatted_product_tax_amount' );
}

/*
* Round off type changes
*/
if(!function_exists('gr_roundoff')) {
    function gr_roundoff($number, $type = 'ROUND')
    {
        if ($type == 'ROUND' || empty($type)) {
            $number = round($number);
        } else if ($type == 'CEIL') {
            $number = ceil($number);
        } else if ($type == 'FLOOR') {
            $number = floor($number);
        } else if ($type == 'DEC_TWO_DIGIT') {
            $number = floor($number * 100) / 100;
        }

        return $number;
    }
}
