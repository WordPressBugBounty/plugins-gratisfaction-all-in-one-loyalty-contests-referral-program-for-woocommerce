<?php

/**
 * @package Gratisfaction Connect
 * @version 4.4.4
 */
/*
  Plugin Name: Gratisfaction- Loyalty Rewards Referral Birthday and Giveaway Program
  Plugin URI: http://appsmav.com
  Description: Loyalty + Referral + Rewards + Birthdays and Anniversaries + Giveaways + Sweepstakes.
  Version: 4.4.4
  Author: Appsmav
  Author URI: http://appsmav.com
  License: GPL2
 */
/*  Copyright 2021  Appsmav  (email : support@appsmav.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
defined('ABSPATH') or die('No script kiddies please!');
define('GR_PLUGIN_BASE_PATH', dirname(__FILE__));

if(!class_exists('GR_Connect'))
{
    class GR_Connect
    {
        const ENDPOINT = 'gr-loyalty';
        const REDEEM_COUPON = 'GRPAYPOINTS';

        public static $_plugin_version  = '4.4.4';
        public static $_callback_url = 'https://gratisfaction.appsmav.com/';
        public static $_api_version  = 'newapi/v2/';
        protected static $_api_url   = 'https://clients.appsmav.com/api_v1.php';
        protected static $_c_sdk_url = '//cdn.appsmav.com/gr/assets/js/gr-widget-sdk.js';

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            try {
                // register actions
                add_action('admin_init', array(&$this, 'admin_init'));
                add_action('admin_menu', array(&$this, 'add_menu'));
                add_action('plugins_loaded', array(&$this, 'woohook_init'));

                register_activation_hook( __FILE__, array( $this, 'activate_endpoints' ) );
                register_deactivation_hook( __FILE__, array( $this, 'activate_endpoints' ) );

                // register actions for Blog Comments
                add_action('plugins_loaded', array(&$this, 'commenthook_init'), 1);
                add_action('admin_enqueue_scripts', array(&$this, 'gr_font_styles'));
                add_action('parse_request', array(&$this, 'apmgr_create_discount'));
                add_action('save_post', array(&$this,'gr_save_post'), 10, 3);
                add_action('after_switch_theme', array($this, 'admin_init'));
                add_filter('woocommerce_get_shop_coupon_data', array($this, 'get_coupon'), 12, 2);
                add_filter('woocommerce_coupon_message', array($this, 'get_discount_applied_message'), 10, 3);
                add_filter('woocommerce_cart_totals_coupon_label', array($this, 'coupon_label'));
                add_filter('woocommerce_coupon_is_valid', array($this, 'validate_apply_coupon'));

                // display points on a separate tab on user's account page
                add_action('init', array($this, 'add_endpoints'));
                add_filter('query_vars', array($this, 'add_query_vars'), 0);

                add_action('woocommerce_account_menu_items', array($this, 'add_menu_items'));
                add_action('woocommerce_account_' . self::ENDPOINT . '_endpoint', array($this, 'gratisfaction_loyalty_activites'));

                add_action('after_switch_theme', array($this, 'activate_endpoints'));

                add_action('rest_api_init', array($this, 'register_rest_routes'), 10);
                
            } catch (Exception $ex) {
            }

        }// END public function __construct

        public function register_rest_routes()
        {
            try {
                $route = new Grwoo_API();
                $route->register_apis();
            } catch (Exception $ex) {

            }
        }

        public function gr_font_styles($hook)
        {
            try {
                if('settings_page_grconnect' != $hook)
                    return;

                // register styles
                wp_register_style('bootstrap_css', plugins_url('/css/bootstrap.min.css', __FILE__));
                wp_register_style('gr_connect_css', plugins_url('/css/grconnect.css', __FILE__));

                // enqueue styles
                wp_enqueue_style('bootstrap_css');
                wp_enqueue_style('gr_connect_css');

                // enqueue scripts
                wp_enqueue_script('jquery_validity_script', plugins_url('/js/jquery.validity.js', __FILE__), array(), self::$_plugin_version, true);
                wp_enqueue_script('gr_connect_script', plugins_url('/js/grconnect.js', __FILE__), array(), self::$_plugin_version, true);
            } catch (Exception $ex) {

            }
        }

        public function get_discount_error_message($message, $message_code, $coupon)
        {
            try {
                if (empty(WC()->session))
                    return $message;

                $coupon = strtolower($coupon->get_code());
                $redeem_coupon = strtolower(WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON));
                if($coupon === $redeem_coupon)
                    return __('', 'gratiscation');
                else
                    return $message;
            } catch (Exception $ex) {

            }
        }

        public function get_discount_applied_message($message, $message_code, $coupon) {
            try {
                if (empty(WC()->session))
                    return $message;

                $coupon = strtolower($coupon->get_code());
                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if ($coupon === strtolower($redeem_coupon)) {
                    if (WC_Coupon::WC_COUPON_SUCCESS === $message_code)
                        return __(WC()->session->get('gr_redeemed_status_msg'), 'gratiscation');
                }
                else {
                    return $message;
                }
            } catch (Exception $ex) {

            }
        }

        /**
         * Make the label for the coupon look nicer
         * @param  string $label
         * @return string
         */
        public function coupon_label($label)
        {
            try
            {
                if (empty(WC()->cart) || empty(WC()->session))
                    return $label;

                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if(strstr(strtolower($label), strtolower($redeem_coupon)) && !empty(WC()->session))
                {
                    $deduct_points_str  =   '';
                    $deduct_points  =   WC()->session->get('gr_user_deduct_points');

                    if(!empty($deduct_points))
                    {
                        $point_lable = ($deduct_points > 1) ? WC()->session->get('gr_points_lable') : WC()->session->get('gr_point_lable');
                        $deduct_points_str  =   ' ('.$deduct_points.' '.$point_lable.')';
                    }
                    else
                    {
                        if(!empty(WC()->cart) && WC()->cart->has_discount($redeem_coupon))
                            WC()->cart->remove_coupon($redeem_coupon);//remove_discount

                        WC()->session->set('gr_user_max_discount', 0);
                        WC()->session->set('gr_user_deduct_points', 0);
                    }

                    return WC()->session->get('label_redeemed_points').$deduct_points_str;
                }
            }
            catch(Exception $e)
            {

            }

            return $label;
        }

        public function get_coupon($coupon, $coupon_code)
        {
            try
            {
                /*
                 * Return default coupon info, under the following conditions.
                 * 1) If coupon is not 'REDEEM_COUPON' coupon
                 * 2) If user is not logged in
                 * 3) Is request is admin page - Need to check and remove
                 * 4) If session object is empty
                 * 5) If applied discount is lesser than or equal to 0
                 * 6) If cart is empty
                 */
                if(!is_user_logged_in() || empty(WC()->session) || empty(WC()->cart))
                    return $coupon;

                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if (strtolower($coupon_code) != strtolower($redeem_coupon))
                    return $coupon;

                // Dokan coupon verification disabled to apply pay by points
                if(class_exists('WeDevs_Dokan'))
                    add_filter ('dokan_ensure_vendor_coupon', '__return_false');

                $items = WC()->cart->get_cart();

                if(empty($items))
                    return $coupon;

                self::gr_calc_point_value();

                // Get Currency ratio
                if (class_exists('WOOCS')) {
                    global $WOOCS;

                    if (!empty($WOOCS)) {
                        $ratio = 1;
                    }
                }

                if (empty($ratio))
                    $ratio = self::gr_get_currency_ratio();

                // Set redeem points in descriptions
                $coupon_discount = WC()->session->get('gr_user_applied_discount')/$ratio;
                $points = WC()->session->get('gr_user_deduct_points', 0);

                $cart_subtotal = WC()->cart->get_subtotal();
                if (wc_tax_enabled() && !wc_prices_include_tax() && !empty($cart_subtotal))
                {
                    $cart_subtotal_tax = WC()->cart->get_subtotal_tax();
                    $tax_rate = ($cart_subtotal_tax / $cart_subtotal);

                    $redeem_tax = $coupon_discount * $tax_rate;
                    $redeem_amount = $redeem_tax * (1 - $tax_rate);
                    $coupon_discount = $coupon_discount - $redeem_amount;
                }

                $is_redeem_individual = WC()->session->get('gr_is_redeem_individual', 0);
                $data = array(
                    'id' => true,
                    'type' => 'fixed_cart',
                    'amount' => $coupon_discount,
                    'coupon_amount' => $coupon_discount,
                    'individual_use' => (!empty($is_redeem_individual) && $is_redeem_individual == 1) ? true : false,
                    'usage_limit' => '',
                    'usage_count' => '',
                    'expiry_date' => '',
                    'apply_before_tax' => true,
                    'free_shipping' => false,
                    'product_categories' => array(),
                    'exclude_product_categories' => array(),
                    'exclude_sale_items' => false,
                    'minimum_amount' => '',
                    'maximum_amount' => '',
                    'customer_email' => '',
                );

                if (!empty($points)) {
                    $description = $redeem_coupon . '_' . $points . '_' . $coupon_discount;
                    WC()->session->set('gr_applied_points', $description);
                }

                return $data;
            }
            catch(Exception $e)
            {
                 return $coupon;
            }
        }

        public function activate_endpoints()
        {
            try {
                $this->add_endpoints();
                flush_rewrite_rules();
            } catch (Exception $ex) {

            }
        }

        /**
         * Register new endpoint to use inside My Account page.
         * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
         */
        public function add_endpoints()
        {
            try {
                add_rewrite_endpoint(self::ENDPOINT, EP_ROOT | EP_PAGES);
            } catch (Exception $ex) {

            }
        }

        public function gratisfaction_loyalty_activites()
        {
            try
            {
                if(is_user_logged_in())
                {
                    $current_user = wp_get_current_user();

                    // Check the user role is allowed to proceed
                    $is_blocked_role = self::is_restricted_user_role($current_user->roles);
                    if ($is_blocked_role)
                    {
                        echo WC()->session->get('no_records_found', 'No Activites Found');
                        return;
                    }
                }
                else if (empty(WC()->session) || WC()->session->get('gr_disable_non_loggedin', 0) == 1)
                {
                    return;
                }

                $id_site = get_option('grconnect_appid');
                $current_user = wp_get_current_user();
                $email = $current_user->user_email;

                if(empty($email) || empty($id_site) || empty(WC()->session))
                    throw new Exception();

                $httpObj = (new HttpRequestHandler)
                                ->setPostData(array('user_email' => $email, 'id_site' => $id_site))
                                ->exec(self::$_callback_url.self::$_api_version.'getUserPoints');
                $resp = $httpObj->getResponse();

                if(!empty($resp))
                    $resp = json_decode($resp, true);

                if(!empty($resp['error']))
                    throw new Exception();

                $resp['user_points']        =   empty($resp['user_points']) ? 0 : $resp['user_points'];
                $resp['exclusion_points']   =   empty($resp['exclusion_points']) ? 0 : $resp['exclusion_points'];
                $resp['total_points']       =   empty($resp['total_points']) ? 0 : $resp['total_points'];
                $resp['redeem_points']      =   empty($resp['redeem_points']) ? 0 : $resp['redeem_points'];

                $style  =   '<style>
                            .rewardsActivities ul{list-style: none;margin: 0;padding: 0;overflow: hidden;}
                            .rewardsActivities li{float: left; background:#fff; border: 1px solid #f0f0f0; padding: 30px 10px; text-align: center; margin: 5px; width: 45%;}
                            .rewardsActivities li label{display: block; color:#111;}
                            .rewardsActivities li label + span {color: #aaa;}
                            @media only screen and (max-width: 418px) {
                                .rewardsActivities li {float: none;width: 100%;margin: 10px 0;padding: 25px 10px;}
                            }
                            </style>';

                $label_life_time_points = WC()->session->get('label_life_time_points');

                if(empty($label_life_time_points))
                    $this->get_settings_api();

                echo $style.'<div class="rewardsActivities"><h3>'.WC()->session->get('label_life_time_points', 'My Life Time Points').'</h3><ul class="pointsCon clearfix">
                        <li><label>'.WC()->session->get('label_available_points', 'Redeemable points').'</label><span class="titlePoints">'.$resp['user_points'].'</span></li>
                        <li><label>'.WC()->session->get('label_exclusion_points', 'Latest Exclusion period points').'</label><span class="titlePoints">'.$resp['exclusion_points'].'</span></li>
                        <li><label>'.WC()->session->get('label_total_points', 'Total points').'</label><span class="titlePoints">'.$resp['total_points'].'</span></li>
                        <li><label>'.WC()->session->get('label_redeemed_points', 'Redeemed points').'</label><span class="titlePoints">'.$resp['redeem_points'].'</span></li>
                        </ul></div>';
            }
            catch(Exception $e)
            {
                echo WC()->session->get('no_records_found', 'No Activites Found');
            }

            return;
        }

        /**
         * Add new query var.
         *
         * @param array $vars
         * @return array
         */
        public function add_query_vars($vars)
        {
            try {
                $vars[] = self::ENDPOINT;
                return $vars;
            } catch (Exception $ex) {

            }
        }

        /**
         * Insert the new endpoint into the My Account menu.
         * @param array $menu_items
         * @return array
         */
        public function add_menu_items($menu_items)
        {
            try
            {
                if(is_admin() || empty(WC()->session) || self::_isActiveCampaign() === false || WC()->session->get('gr_loyalty_campaign_enabled', 0) != 1)
                    return $menu_items;

                if(is_user_logged_in())
                {
                    $current_user = wp_get_current_user();

                    // Check the user role is allowed to proceed
                    $is_blocked_role = self::is_restricted_user_role($current_user->roles);
                    if ($is_blocked_role)
                        return $menu_items;
                }
                else if (WC()->session->get('gr_disable_non_loggedin', 0) == 1)
                {
                    return $menu_items;
                }

                if( ! empty($menu_items['customer-logout']))
                {
                    // Remove logout menu item.
                    $logout = $menu_items['customer-logout'];
                    unset($menu_items['customer-logout']);

                    $this->get_settings_api();

                    //add loyalty menu
                    $menu_items[self::ENDPOINT] = empty(WC()->session) ? 'Loyalty Reward' : WC()->session->get('gr_loyalty_menu_name', 'Loyalty Rewards');

                    // Insert back logout item.
                    $menu_items['customer-logout'] = $logout;
                }
            }
            catch(Exception $e)
            {

            }

            return $menu_items;
        }

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            try {
                if ( !class_exists( 'WooCommerce' ) ) {
                    wp_die(__("<div class='highErrormsg'>
                            WooCommerce plugin not found! Please install WooCommerce plugin & then activate Gratisfaction.
                            If you do not want to install the WooCommerce plugin but still use Gratisfaction, then install Gratisfaction from WordPress plugin directory <a href='https://wordpress.org/plugins/gratisfaction-social-contests-referral-loyalty-rewards-program-by-appsmav/'>https://wordpress.org/plugins/gratisfaction-social-contests-referral-loyalty-rewards-program-by-appsmav/</a>
                        <div>", "gratisfaction-all-in-one-loyalty-contests-referral-program-for-woocommerce"));
                }

                if(class_exists('GR_Appsmav'))
                {
                    if(is_plugin_active(plugin_basename(__FILE__)))
                        deactivate_plugins(plugin_basename(__FILE__));

                    wp_die(__('A Gratisfaction plugin is already running on your website. Only one installation can be active at any one time. If you want to install this version of Gratisfaction, then first deactivate the current Gratisfaction plugin.', 'gratisfaction-all-in-one-loyalty-contests-referral-program-for-woocommerce'));
                }

                //Do nothing
                update_option('grconnect_register', 2);
            } catch (Exception $ex) {

            }
        }

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            try
            {
                // Do nothing
                if(!class_exists('WC_Integration'))
                    return false;

                remove_action('woocommerce_checkout_order_processed', array('GR_Connect', 'send_connect_init'));
                //remove_action('woocommerce_order_edit_status', array('GR_Connect','send_status_init'));
                remove_action('woocommerce_order_status_changed', array('GR_Connect', 'send_status_init'));
                remove_action('woocommerce_order_refunded', array('GR_Connect', 'send_refund_init'));
                remove_action('woocommerce_created_customer', array('GR_Connect', 'send_customer_init'));
                remove_action('profile_update', array('GR_Connect', 'customer_profile_update'));
                remove_action('before_delete_post', array('GR_Connect', 'send_refund_delete_post_init'));

                remove_action('woocommerce_single_product_summary', array('GR_Connect', 'gr_show_single_product_lable'));
                remove_action('woocommerce_after_add_to_cart_button', array('GR_Connect', 'gr_show_single_product_buy_lable'));
                remove_action('woocommerce_before_cart_totals', array('GR_Connect', 'gr_show_redeem_points_lable'));
                remove_action('template_redirect', array('GR_Connect', 'gr_before_cart'));
                remove_action('woocommerce_before_checkout_form', array('GR_Connect', 'gr_show_redeem_points_lable'));
                remove_action('woocommerce_cart_calculate_fees', array('GR_Connect', 'gr_custom_discount'));
                remove_action('wp_ajax_check_redeem_update', array('GR_Connect', 'gr_update_lable_carts_page'));
                remove_action('wp_ajax_gr_get_cart_details', array('GR_Connect', 'gr_get_cart_details'));
                remove_action('wp_ajax_apply_gr_discount', array('GR_Connect', 'gr_custom_discount_ajax'));
                remove_action('wp_footer', array('GR_Connect', 'gr_widget'));

                // Blog Comments
                remove_action('comment_post', array('GR_Connect', 'send_comment_to_appsmav'));
                remove_action('init', array('GR_Connect', 'init_page_load'));
                remove_action('comment_form_before', array('GR_Connect', 'gr_show_product_review_lable'));
                remove_action('comment_unapproved_review', array('GR_Connect', 'gr_send_comment_status_change'));
                remove_action('comment_approved_review', array('GR_Connect', 'gr_send_comment_status_change'));
                remove_action('comment_spam_review', array('GR_Connect', 'gr_send_comment_status_change'));
                remove_action('comment_trash_review', array('GR_Connect', 'gr_send_comment_status_change'));
                remove_action('woocommerce_checkout_process', array('GR_Connect', 'validate_applied_coupon_checkout'));

                // Delete stored informations
                delete_option('grconnect_secret');

                // Deactivate shop
                $id_shop = get_option('grconnect_shop_id', 0);
                $id_site = get_option('grconnect_appid', 0);
                $payload = get_option('grconnect_payload', 0);
                $param = array('app' => 'gr', 'plugin_type' => 'WP', 'status' => 'deactivate', 'id_shop' => $id_shop, 'id_site' => $id_site, 'payload' => $payload);
                $url = self::$_callback_url . self::$_api_version . 'pluginStatus';

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($param)
                                ->exec($url);
                $resp = $httpObj->getResponse();
            }
            catch(Exception $e)
            {}
        }// END public static function deactivate

        public function send_status_init($order_id)
        {
            try {

                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    return;

                $order = new WC_Order($order_id);

                $param = [];
                $param['status'] = $param['order_status'] = strtolower($order->get_status());

                $user_email = '';
                $ordered_user = $order->get_user();
                if(!empty($ordered_user))
                    $user_email = $ordered_user->get('user_email');

                //$param['user_email'] = $user_email;
                $param['roles'] = empty($ordered_user) ? [] : $ordered_user->roles;

                if (version_compare( WC_VERSION, '3.7', '<' ))
                    $couponsArr = $order->get_used_coupons();
                else
                    $couponsArr = $order->get_coupon_codes();

                if(!empty($couponsArr))
                {
                    $param['coupon'] = $couponsArr;
                }

                // Check points redeemed for this order - PayByPoints
                $gr_applied_points = get_post_meta($order_id, 'gr_applied_points', true);
                if (!empty($gr_applied_points))
                {
                    $points = explode('_', $gr_applied_points);
                    $param['redeem_points']  = empty($points['1']) ? 0 : $points['1'];
                    $param['redeem_charges'] = empty($points['2']) ? 0 : $points['2'];
                }

                $param['line_items'] = [];
                foreach ($order->get_items('line_item') as $key => $item) {
                    $param['line_items'][] = $item->get_data(); // Only product_id & total are required now
                }

                $param['discount'] = $order->get_total_discount();
                $param['subtotal'] = $order->get_subtotal();
                $param['total'] = $order->get_total();
                //$param['total'] = $order->get_total() - $order->get_total_refunded();

                // Full refund, set total amount for points deduction.
//                if ($param['total'] <= 0)
//                    $param['total'] = $order->get_total();

                $param['refunded'] = $order->get_total_refunded();
                $param['shipping'] = $order->get_shipping_total();
                $param['shipping_tax'] = $order->get_shipping_tax();
                $param['tax'] = $order->get_total_tax();

                // Set refund data for order change also
                $refundData = array();
                foreach ($order->get_refunds() as $key => $refund) {
                    $refundData[$key]['refund'] = $refund->get_data();
                    foreach ($refund->get_items() as $item_id => $item) {
                        $refundData[$key]['line_items'][$item_id] = $item->get_data();
                    }
                }
                $param['refund_data'] = $refundData;

                if(version_compare( WC_VERSION, '3.0', '<' ))
                    $curOrder = $order->get_order_currency();
                else
                    $curOrder = $order->get_currency();

                $curShop = get_option('woocommerce_currency', 'USD');

                // Currency conversion starts here
                if($curOrder != $curShop)
                {
                    // Get currency ratio for this order
                    $param['ratio'] = self::gr_get_currency_ratio_from_order($order);

                }

                if(version_compare( WC_VERSION, '3.0', '<' ))
                {
                    $param['name'] = $order->get_billing_first_name();
                    $param['number'] = $order->get_order_number();
                    $param['first_name'] = $order->get_billing_first_name();
                    $param['last_name'] = $order->get_billing_last_name();
                    $param['postcode'] = $order->get_billing_postcode();
                    $param['country'] = $order->get_billing_country();
                }
                else
                {
                    $order_data = $order->get_data();
                    $param['name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                    $param['number'] = empty($order_data['number']) ? '' : $order_data['number'];
                    $param['first_name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                    $param['last_name'] = empty($order_data['billing']['last_name']) ? '' : $order_data['billing']['last_name'];
                    $param['postcode'] = empty($order_data['billing']['postcode']) ? '' : $order_data['billing']['postcode'];
                    $param['country'] = empty($order_data['billing']['country']) ? '' : $order_data['billing']['country'];
                }

                $param['created_date'] = $order->get_date_created()->format('c');
                $param['user_ip'] = $order->get_customer_ip_address();
                $param['email'] = !empty($user_email) ? $user_email : $order->get_billing_email();
                $param['customer_id'] = $order->get_user_id();
                $param['order'] = 0;
                $param['id_order'] = $order_id;

                try {
                    //We are skipping parent order id if it has sub orders

                    if(class_exists('WCMp'))
                    {

                        if( $order->get_parent_id() === 0 && get_post_meta( $order_id, 'has_wcmp_sub_order', true ) == '1'){
                            $param['comment'] = 'Main WCMp Order Id ' . str_replace('wc-', '', sanitize_text_field($_REQUEST['order_status'])) . ' - ' . $order_id . ' From ' . get_option('grconnect_shop_id', 0).' total '.$param['total'];
                            $param['total'] = 0;
                            $param['subtotal'] = 0;
                            $param['shipping'] = 0;
                            $param['tax'] = 0;
                        }
                    }

                    if(class_exists('WeDevs_Dokan'))
                    {
                        if( $order->get_parent_id() === 0 && get_post_meta( $order_id, 'has_sub_order', true ) == '1'){
                            $param['comment'] = 'Main Dokan Order Id ' . str_replace('wc-', '', sanitize_text_field($_REQUEST['order_status'])) . ' - ' . $order_id . ' From ' . get_option('grconnect_shop_id', 0).' total '.$param['total'];
                            $param['total'] = 0;
                            $param['subtotal'] = 0;
                            $param['shipping'] = 0;
                            $param['tax'] = 0;
                        }
                    }
                }
                catch(Exception $e){ }

                $param['plugin_version'] = self::$_plugin_version;
                $urlApi = self::$_callback_url . self::$_api_version . 'addEntry';
                $this->callGrConnectApi($param, $urlApi);
            }
            catch(Exception $e)
            {}
        }

        /**
         * hook into WP's woocommerce checkout order processed action hook
         */
        public function send_connect_init($order_id)
        {
            try {
                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    return;

                // Set up the settings for this plugin
                $order = new WC_Order($order_id);

                $param = [];
                $param['status'] = $param['order_status'] = strtolower($order->get_status());

                $user_email = '';
                $ordered_user = $order->get_user();
                if(!empty($ordered_user))
                    $user_email = $ordered_user->get('user_email');
                
                $param['roles'] = empty($ordered_user) ? [] : $ordered_user->roles;
                if (version_compare( WC_VERSION, '3.7', '<' ))
                    $couponsArr = $order->get_used_coupons();
                else
                    $couponsArr = $order->get_coupon_codes();

                if(!empty($couponsArr))
                {
                    $param['coupon'] = $couponsArr;
                }

                $param['discount'] = $order->get_total_discount();
                $param['subtotal'] = $order->get_subtotal();
                $param['total'] = $order->get_total();
                $param['refunded'] = $order->get_total_refunded();
                $param['shipping'] = $order->get_shipping_total();
                $param['shipping_tax'] = $order->get_shipping_tax();
                $param['tax'] = $order->get_total_tax();

                $param['line_items'] = [];
                foreach ($order->get_items('line_item') as $key => $item) {
                    $param['line_items'][] = $item->get_data();
                }

                //Points discount
                $points_discount_val = 0;

                if(!empty($couponsArr))
                {
                    $coupons_data = $order->get_items('coupon');

                    if(!empty($coupons_data))
                    {
                        foreach($coupons_data as $item_data)
                        {
                            $redeem_coupon = empty(WC()->session) ? '' : WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                            if(!empty($item_data['name']) && !empty($item_data['discount'])
                                && strtolower($item_data['name']) == strtolower($redeem_coupon))
                                $points_discount_val    =   $item_data['discount'];
                        }
                    }
                }

                if(empty($points_discount_val))
                {
                    $param['redeem_charges'] = 0;
                    $param['redeem_points'] = 0;
                }
                else if( ! empty(WC()->session))
                {
                    $param['redeem_charges'] = WC()->session->get('gr_user_applied_discount', 0);
                    $param['redeem_points'] = ($param['redeem_charges'] == 0) ? 0 : WC()->session->get('gr_user_deduct_points', 0);
                }

                if( ! empty(WC()->session))
                {
                    WC()->session->set('gr_user_deduct_points', 0);
                    WC()->session->set('gr_user_applied_discount', 0);
                }

               if(strtolower($param['order_status']) == 'pending' && empty($param['redeem_points']))
                   return;

                if(version_compare( WC_VERSION, '3.0', '<' ))
                {
                    $param['name'] = $order->get_billing_first_name();
                    $param['number'] = $order->get_order_number();
                    $param['first_name'] = $order->get_billing_first_name();
                    $param['last_name'] = $order->get_billing_last_name();
                    $param['postcode'] = $order->get_billing_postcode();
                    $param['country'] = $order->get_billing_country();
                }
                else
                {
                    $order_data = $order->get_data();
                    $param['name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                    $param['number'] = empty($order_data['number']) ? '' : $order_data['number'];
                    $param['first_name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                    $param['last_name'] = empty($order_data['billing']['last_name']) ? '' : $order_data['billing']['last_name'];
                    $param['postcode'] = empty($order_data['billing']['postcode']) ? '' : $order_data['billing']['postcode'];
                    $param['country'] = empty($order_data['billing']['country']) ? '' : $order_data['billing']['country'];
                }

                $param['email'] = !empty($user_email) ? $user_email : $order->get_billing_email();
                $param['customer_id'] = $order->get_user_id();
                $param['order'] = 1;
                $param['createaccount'] = 0;
                $param['id_order'] = $order_id;
                $param['comment'] = 'Order Id - ' . $order_id . ' From ' . get_option('siteurl');
                $param['status'] = 'Add';
                $param['created_date'] = $order->get_date_created()->format('c');
                $param['user_ip'] = $order->get_customer_ip_address();

                if(version_compare( WC_VERSION, '3.0', '<' ))
                    $curOrder = $order->get_order_currency();
                else
                    $curOrder = $order->get_currency();

                $curShop = get_option('woocommerce_currency', 'USD');
                if($curOrder != $curShop)
                {
                    // Get currency ratio for this order
                    $param['ratio'] = self::gr_get_currency_ratio_from_order($order);
                }

                try {
                    //We are skipping parent order id if it has sub orders

                    if(class_exists('WCMp'))
                    {

                        if( $order->get_parent_id() === 0 && get_post_meta( $order_id, 'has_wcmp_sub_order', true ) == '1'){
                            $param['comment'] = 'Main WCMp Order Id ' . str_replace('wc-', '', sanitize_text_field($_REQUEST['order_status'])) . ' - ' . $order_id . ' From ' . get_option('grconnect_shop_id', 0).' total '.$param['total'];
                            $param['total'] = 0;
                            $param['subtotal'] = 0;
                            $param['shipping'] = 0;
                            $param['tax'] = 0;
                        }
                    }

                    if(class_exists('WeDevs_Dokan'))
                    {
                        if( $order->get_parent_id() === 0 && get_post_meta( $order_id, 'has_sub_order', true ) == '1'){
                            $param['comment'] = 'Main Dokan Order Id ' . str_replace('wc-', '', sanitize_text_field($_REQUEST['order_status'])) . ' - ' . $order_id . ' From ' . get_option('grconnect_shop_id', 0).' total '.$param['total'];
                            $param['total'] = 0;
                            $param['subtotal'] = 0;
                            $param['shipping'] = 0;
                            $param['tax'] = 0;
                        }
                    }
                }
                catch(Exception $e){ }

                $param['plugin_version'] = self::$_plugin_version;
                $urlApi = self::$_callback_url . self::$_api_version . 'addEntry';
                $this->callGrConnectApi($param, $urlApi);
            }
            catch(Exception $e)
            {
                gr_app_error_log($e->getTraceAsString());
            }
        }

        /**
         * hook into WP's woocommerce checkout order processed action hook
         */
        public function send_customer_init($customer_id)
        {
            try
            {
                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    return;

                // Set up the settings for this plugin
                $user = get_userdata($customer_id);
                $shop_id = get_option('grconnect_shop_id', 0);

                if($shop_id == 0)
                    return;

                // Check the user role is allowed to proceed
                $is_blocked_role = self::is_restricted_user_role($user->roles);
                if ($is_blocked_role)
                        return;

                $param['email'] = $user->user_email;
                $param['name'] = $user->user_nicename;
                $param['first_name'] = empty($user->first_name) ? '' : $user->first_name;
                $param['last_name'] = empty($user->last_name) ? '' : $user->last_name;
                $param['user_ip'] = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
                $param['customer_id'] = $customer_id;
                $param['id_shop'] = $shop_id;
                $param['id_site'] = $grAppId;
                $param['id_campaign'] = $grCampId;
                $param['payload'] = get_option('grconnect_payload', 0);
                $param['plugin_version'] = self::$_plugin_version;
                $param['user_roles'] = $user->roles;
                $urlApi = self::$_callback_url . self::$_api_version . 'addWelcomeBonus';

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($param)
                                ->exec($urlApi);
                $resp = $httpObj->getResponse();

                if(!empty($resp))
                    $resp = json_decode($resp, true);

                if(!empty($resp['error']))
                    return;
            }
            catch(Exception $e)
            {
                gr_app_error_log($e->getTraceAsString());
                return;
            }
        }

        /**
         * Send customer details when user profile updated
        */
        public function customer_profile_update( $customer_id )
        {
            try
            {
                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    return;

                $user = get_userdata($customer_id);

                $param['email'] = $user->user_email;
                $param['name'] = $user->user_nicename;
                $param['first_name'] = empty($user->first_name) ? '' : $user->first_name;
                $param['last_name'] = empty($user->last_name) ? '' : $user->last_name;

                $param['user_ip'] = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
                $param['customer_id'] = $customer_id;
                $param['id_shop'] = $grShopId;
                $param['id_site'] = $grAppId;
                $param['id_campaign'] = $grCampId;
                $param['payload'] = $grPayload;
                $param['plugin_version'] = self::$_plugin_version;
                $param['user_roles'] = $user->roles;
                $urlApi = self::$_callback_url . self::$_api_version . 'updateUserDetails';

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($param)
                                ->exec($urlApi);
                $resp = $httpObj->getResponse();

                if(!empty($resp))
                    $resp = json_decode($resp, true);

                if(!empty($resp['error']))
                    return;
            }
            catch(Exception $e)
            {
                gr_app_error_log($e->getTraceAsString());
                return;
            }
        }

        /**
         * hook into WP's woocommerce before delete post action hook
         */
        public function send_refund_delete_post_init($refund_id)
        {
            try
            {
                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    return;

                // Set up the settings for this plugin
                if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'woocommerce_delete_refund')
                {
                    $refund = new WC_Order_Refund($refund_id);
                    $order = new WC_Order($refund->post->post_parent);

                    $param['discount'] = $order->get_total_discount();
                    $param['subtotal'] = $order->get_subtotal() - $order->get_total_discount();
                    $param['total'] = $order->get_total() - $order->get_total_refunded();
                    $param['shipping'] = $order->get_shipping_total();
                    $param['shipping_tax'] = $order->get_shipping_tax();
                    $param['tax'] = $order->get_total_tax();

                    if(version_compare( WC_VERSION, '3.0', '<' ))
                        $curOrder = $order->get_order_currency();
                    else
                        $curOrder = $order->get_currency();

                    $curShop = get_option('woocommerce_currency', 'USD');

                    $email = '';
                    $ordered_user = $order->get_user();

                    if(!empty($ordered_user))
                        $email = $ordered_user->get('user_email');

                    if($curOrder != $curShop)
                    {
                        $param['currency_notmatch'] = 1;

                        // Get currency ratio for this order
                        $param['ratio'] = self::gr_get_currency_ratio_from_order($order);
                    }

                    $param['created_date'] = $order->get_date_created()->format('c');
                    $param['user_ip'] = $order->get_customer_ip_address();
                    $param['email'] = !empty($email) ? $email : $order->get_billing_email();
                    $param['customer_id'] = $order->get_user_id();
                    $param['order'] = 0;
                    $param['id_order'] = $refund->post->post_parent;
                    $urlApi = self::$_callback_url . self::$_api_version . 'addEntry';

                    if(version_compare( WC_VERSION, '3.0', '<' ))
                    {
                        $param['name'] = $order->get_billing_first_name();
                        $param['first_name'] = $order->get_billing_first_name();
                        $param['last_name'] = $order->get_billing_last_name();
                        $param['postcode'] = $order->get_billing_postcode();
                        $param['country'] = $order->get_billing_country();
                    }
                    else
                    {
                        $order_data = $order->get_data();
                        $param['name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                        $param['number'] = empty($order_data['number']) ? '' : $order_data['number'];
                        $param['first_name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                        $param['last_name'] = empty($order_data['billing']['last_name']) ? '' : $order_data['billing']['last_name'];
                        $param['postcode'] = empty($order_data['billing']['postcode']) ? '' : $order_data['billing']['postcode'];
                        $param['country'] = empty($order_data['billing']['country']) ? '' : $order_data['billing']['country'];
                    }

                    $param['comment'] = 'Order Id Refund Restore - ' . $refund->post->post_parent . ' From ' . get_option('siteurl');
                    $param['status'] = 'Add';
                    $param['order_status'] = $order->get_status();
                    $param['plugin_version'] = self::$_plugin_version;

                    $this->callGrConnectApi($param, $urlApi);
                }
                else
                {
                    $post = get_post($refund_id);
                    if ('page' !== $post->post_type) {
                        return;
                    }

                    $is_embed_landing_url = get_post_meta($refund_id, 'is_embed_landing_url', true);
                    if ($is_embed_landing_url != 1) {
                        return;
                    }

                    $url     = self::$_callback_url . self::$_api_version . 'wooInstallTabDelete';
                    update_post_meta($refund_id, 'is_embed_landing_url', 0);

                    $param = array(
                        'id_site' => $grAppId,
                        'payload' => $grPayload,
                        'id'      => $refund_id
                    );

                    $res = self::_curlResp($param, $url);
                    if (empty($res) || $res['error'] == 1) {
                        throw new Exception('VerificationFailed');
                    }
                }

            }
            catch(Exception $e)
            {
            }
        }

        /**
         * hook into WP's woocommerce order refunded action hook
         */
        public function send_refund_init($order_id)
        {
            try
            {
                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload) || empty($_REQUEST['refund_amount']))
                    return;

                // Set up the settings for this plugin
                $order = new WC_Order($order_id);

                try {
                    //We are skipping parent order id if it has sub orders
                    if(class_exists('WCMp'))
                    {
                        if( $order->get_parent_id() === 0 && get_post_meta( $order_id, 'has_wcmp_sub_order', true ) == '1')
                            return;
                    }
                    if(class_exists('WeDevs_Dokan'))
                    {
                        if( $order->get_parent_id() === 0 && get_post_meta( $order_id, 'has_sub_order', true ) == '1')
                            return;
                    }
                }
                catch(Exception $e){ }

                $user_email = '';
                $ordered_user = $order->get_user();
                if(!empty($ordered_user))
                    $user_email = $ordered_user->get('user_email');

                $param = [];
                $param['roles'] = empty($ordered_user) ? [] : $ordered_user->roles;

                $param['line_items'] = [];
                foreach ($order->get_items('line_item') as $key => $item) {
                    $param['line_items'][] = $item->get_data(); // TODO: Only product_id & total are required now
                }

                $refundData = array();
                foreach ($order->get_refunds() as $key => $refund) {
                    $refundData[$key]['refund'] = $refund->get_data();
                    foreach ($refund->get_items() as $item_id => $item) {
                        $refundData[$key]['line_items'][$item_id] = $item->get_data();
                    }
                }
                $param['refund_data'] = $refundData;

                // Currency conversion starts here
                if(version_compare( WC_VERSION, '3.0', '<' ))
                    $curOrder = $order->get_order_currency();
                else
                    $curOrder = $order->get_currency();

                $curShop = get_option('woocommerce_currency', 'USD');
                if($curOrder != $curShop)
                {
                    // Get currency ratio for this order
                    $param['ratio'] = self::gr_get_currency_ratio_from_order($order);
                }

                $param['refunded'] = $order->get_total_refunded();
                $param['total'] = $order->get_total();
                $param['discount'] = $order->get_total_discount();
                $param['subtotal'] = $order->get_subtotal();
                $param['shipping'] = $order->get_shipping_total();
                $param['shipping_tax'] = $order->get_shipping_tax();
                $param['tax'] = $order->get_total_tax();

                $param['created_date'] = $order->get_date_created()->format('c');
                $param['user_ip'] = $order->get_customer_ip_address();
                $param['email'] = !empty($user_email) ? $user_email : $order->get_billing_email();
                $param['customer_id'] = $order->get_user_id();
                $param['order'] = 0;
                $param['id_order'] = $order_id;
                $param['wc_version'] = WC_VERSION;

                if(version_compare( WC_VERSION, '3.0', '<' ))
                {
                    $param['name'] = $order->get_billing_first_name();
                    $param['number'] = $order->get_order_number();
                    $param['first_name'] = $order->get_billing_first_name();
                    $param['last_name'] = $order->get_billing_last_name();
                    $param['postcode'] = $order->get_billing_postcode();
                    $param['country'] = $order->get_billing_country();
                }
                else
                {
                    $order_data = $order->get_data();
                    $param['name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                    $param['number'] = empty($order_data['number']) ? '' : $order_data['number'];
                    $param['first_name'] = empty($order_data['billing']['first_name']) ? '' : $order_data['billing']['first_name'];
                    $param['last_name'] = empty($order_data['billing']['last_name']) ? '' : $order_data['billing']['last_name'];
                    $param['postcode'] = empty($order_data['billing']['postcode']) ? '' : $order_data['billing']['postcode'];
                    $param['country'] = empty($order_data['billing']['country']) ? '' : $order_data['billing']['country'];
                }

                $param['comment'] = 'Order Id Refunded - ' . $order_id . ' From ' . get_option('siteurl');
                $param['status'] = 'partial_refund';
                $param['order_status'] = strtolower($order->get_status());

                $param['plugin_version'] = self::$_plugin_version;
                $urlApi = self::$_callback_url . self::$_api_version . 'removeEntryNew';
                $this->callGrConnectApi($param, $urlApi);
            }
            catch(Exception $ex)
            {
            }
            // Possibly do additional admin_init tasks
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            try
            {
                // Set up the settings for this plugin
                $this->init_settings();
                // Possibly do additional admin_init tasks
            }
            catch(Exception $ex){ }
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function woohook_init()
        {
            try {
                // Set up the settings for this plugin
                if(class_exists('WC_Integration'))
                {
                    add_action('woocommerce_checkout_order_processed', array(&$this, 'send_connect_init'));
                    //add_action('woocommerce_order_edit_status', array(&$this, 'send_connect_init'));
                    add_action('woocommerce_order_status_changed', array(&$this, 'send_status_init'));
                    add_action('before_delete_post', array(&$this, 'send_refund_delete_post_init'));
                    //add_action('woocommerce_order_status_refunded', array(&$this, 'send_refund_init'));
                    add_action('woocommerce_order_refunded', array(&$this, 'send_refund_init'));
                    add_action('woocommerce_created_customer', array(&$this, 'send_customer_init'));
                    add_action('profile_update', array(&$this, 'customer_profile_update'));
                    add_action('woocommerce_single_product_summary', array(&$this, 'gr_show_single_product_lable'));
                    add_action('woocommerce_after_add_to_cart_button', array(&$this, 'gr_show_single_product_buy_lable'));
                    add_action('woocommerce_before_cart_totals', array(&$this, 'gr_show_redeem_points_lable'));
                    add_action('template_redirect', array(&$this, 'gr_before_cart'));
                    add_action('woocommerce_before_checkout_form', array(&$this, 'gr_show_redeem_points_lable'));
                    add_action('woocommerce_cart_calculate_fees', array(&$this, 'gr_custom_discount'));
                    add_action('wp_ajax_check_redeem_update', array(&$this, 'gr_update_lable_carts_page'));
                    add_action('wp_ajax_gr_get_cart_details', array(&$this, 'gr_get_cart_details'));
                    add_action('wp_ajax_apply_gr_discount', array(&$this, 'gr_custom_discount_ajax'));
                    // WC AJAX can be used for frontend ajax requests.
                    add_action('wp_footer', array(&$this, 'gr_widget'));
                    add_action('comment_form_before', array(&$this, 'gr_show_product_review_lable'));
                    add_action('comment_unapproved_review', array(&$this, 'gr_send_comment_status_change'));
                    add_action('comment_approved_review', array(&$this, 'gr_send_comment_status_change'));
                    add_action('comment_spam_review', array(&$this, 'gr_send_comment_status_change'));
                    add_action('comment_trash_review', array(&$this, 'gr_send_comment_status_change'));
                    add_action('woocommerce_checkout_process', array(&$this, 'validate_applied_coupon_checkout'));
                }
            } catch(Exception $ex){ }
            // Possibly do additional admin_init tasks
        }

        /**
         * Initialize some custom settings
         */
        public function init_settings()
        {
            try {
                // register the settings for this plugin
                add_action('wp_ajax_create_account', array(&$this, 'gr_ajax_create_account'));
                add_action('wp_ajax_check_settings', array(&$this, 'gr_ajax_check_settings'));
                add_action('wp_ajax_check_login', array(&$this, 'gr_ajax_check_login'));
            } catch (Exception $ex) {

            }
        }

        function gr_widget()
        {
            try
            {
                $app_id = get_option('grconnect_appid', 0);
                if(empty($app_id) || empty(WC()->session))
                    return false;

                $this->get_settings_api();

                $id_site = get_option('grconnect_appid');
                $arr['id_site'] = $id_site;
                $arr['error'] = 0;
                $cid = $cemail = $cname = $first_name = $last_name = '';

                $user_roles = '';
                if(is_user_logged_in())
                {
                    $current_user = wp_get_current_user();
                    $cid = $current_user->ID;
                    $cemail = $current_user->user_email;
                    $cname = $current_user->display_name;
                    $first_name = $current_user->user_firstname;
                    $last_name = $current_user->user_lastname;

                    if (!empty($current_user->roles))
                        $user_roles = ',user_roles : ' . json_encode ($current_user->roles);
                }

                $orderConfig = '';
                if (is_order_received_page()) {

                    // Reset auto apply points flag in order thank you page.
                    WC()->session->set('gr_pbp_auto_apply_done', 0);

                    $orderConfig = ', is_thankyou_page: "true"';
                    $order_id = self::_getOrderID();
                    if (!empty($order_id)) {
                        $orderConfig .= ', order_id: "'.$order_id.'"';

                        try {
                            WC()->session->set('gr_discount_applied', 0);
                            $gr_applied_points = WC()->session->get('gr_applied_points', '');
                            if (empty($gr_applied_points))
                                throw new Exception('Points not used for this order');

                            $order = new WC_Order($order_id);
                            if (empty($order))
                                throw new Exception('Order details not found');

                            if (version_compare( WC_VERSION, '3.7', '<' ))
                                $couponsArr = $order->get_used_coupons();
                            else
                                $couponsArr = $order->get_coupon_codes();

                            if(empty($couponsArr))
                                throw new Exception('Coupon not used');

                            // Check redeemed points from coupon description
                            foreach($couponsArr as $coupon_code)
                            {
                                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                                if(strtolower($coupon_code) === strtolower($redeem_coupon))
                                {
                                    WC()->session->set('gr_applied_points', '');
                                    update_post_meta($order_id, 'gr_applied_points', $gr_applied_points);
                                }
                            }
                        }
                        catch (Exception $ex)
                        { }
                    }
                }

                //pbp appeding more variable is the config files
                $cart_count = 0;
                if (!empty(WC()->cart)) {
                    $cart_count =  count(WC()->cart->get_cart());

                    //Reset the session values if discount is not there
                    if (empty(WC()->session->get('gr_user_max_discount', 0))) {
                        self::gr_calc_point_value();
                    }
                }

                // Do not move this to above
                $discounted_amount = WC()->session->get('gr_user_max_discount', 0);
                $is_discount_applied  = WC()->session->get('gr_discount_applied', 0);
                $cart_url = !empty(wc_get_cart_url()) ? esc_url(wc_get_cart_url()) : '';
                $gr_sdk_version = !empty(WC()->session->get('gr_sdk_version', 0)) ? WC()->session->get('gr_sdk_version', 0): self::$_plugin_version;
		        $gr_widget_config_version = !empty(WC()->session->get('gr_widget_config_version', 0)) ? WC()->session->get('gr_widget_config_version', 0): WC()->session->get('gr_api_sess', 0);

                echo '<script>var AMGRConfig = {user : {name : "' . $cname . '", first_name : "' . $first_name . '", last_name : "' . $last_name . '", email : "' . $cemail . '", id : "' . $cid . '", country : ""' . $user_roles . $orderConfig . ',gr_applied_points: "' . WC()->session->get('gr_user_deduct_points', 0) . '",discounted_amount:"' . $discounted_amount . '",is_discount_applied:"' . $is_discount_applied . '", extra_pbp: "' . WC()->session->get('gr_user_extra_pay_points', 0) . '"}, site : {id : "' . $id_site . '", domain : "' . get_option('siteurl') . '", cart_count: "' . $cart_count . '",cart_url: "' . $cart_url . '",platform : "WP", sdk_version: "'.$gr_sdk_version.'", version : "'.$gr_widget_config_version.'"}};

                (function(d, s, id) {
                    var js, amjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id; js.async = true;
                    js.src = "'.self::$_c_sdk_url.'?v='.$gr_sdk_version.'";
                    amjs.parentNode.insertBefore(js, amjs);
                }(document, "script", "gratisfaction-sdk"));
                </script>';
            }
            catch(Exception $e)
            {
                return;
            }
        }

        /*
         * Get Order Id on Order Success page
         */
        function _getOrderID()
        {
            try
            {
                global $wp;
                $order_id = '';
                if (isset($wp->query_vars['order-received']) && !empty($wp->query_vars['order-received'])) {
                    $order_id = $wp->query_vars['order-received'];
                } else if(isset($_GET['view-order']) && !empty($_GET['view-order'])) {
                    //check if on view-order page and get parameter is available
                    $order_id = $_GET['view-order'];
                } else if(isset($_GET['order-received']) && !empty($_GET['order-received'])) {
                    //check if on view order-received page and get parameter is available
                    $order_id = $_GET['order-received'];
                } elseif (isset($_GET['key']) && !empty($_GET['key']) && version_compare( WC_VERSION, '5.9', '>=' )) {
                    $order_id = wc_get_order_id_by_order_key( $_GET['key'] );
                } else {
                    $url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                    $template_name = strpos($url,'/order-received/') === false ? '/view-order/' : '/order-received/';
                    if (strpos($url,$template_name) !== false) {
                        $start = strpos($url,$template_name);
                        $first_part = substr($url, $start+strlen($template_name));
                        $order_id = substr($first_part, 0, strpos($first_part, '/'));
                    }
                }
            }
            catch(Exception $e)
            { }

            return $order_id;
        }

        protected static function _curlResp($param,$url)
        {
            $response = wp_remote_post($url,array('body'=> $param,'timeout' => 10));
            if (is_array($response) && !empty($response['body'])) {
               $resp = json_decode($response['body'], true);
            } else {
               $resp['error']  = 1;
            }

            return $resp;
        }

        function gr_save_post($post_id, $post, $update) {
            try
            {
                if (empty($post) || empty($post->post_type) || empty($post_id))
                    return;

                // Give points for testimonial
                if ($post->post_type == 'wpm-testimonial') {
                    self::send_testimonial_to_appsmav($post_id, $post);
                    return;
                }

                // Only want to set if this is a old post!
                if (!$update || 'page' !== $post->post_type) {
                    return;
                }

                $is_embed_landing_url = get_post_meta($post->ID, 'is_embed_landing_url', true);
                if ($is_embed_landing_url != 1) {
                    return;
                }

                $url     = self::$_callback_url . self::$_api_version . 'wooInstallTabChange';

                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    return;

                $param = array(
                    'id_site'   => $grAppId,
                    'payload'   => $grPayload,
                    'id'        => $post->ID,
                    'title'     => $post->post_title,
                    'url'       => get_permalink($post->ID),
                    'publish'   => $post->post_status == 'publish' ? 1 : 0,
                    'is_embed_landing_url' => $is_embed_landing_url
                );

                $res = self::_curlResp($param, $url);
                if(empty($res) || $res['error'] == 1) {
                    throw new Exception('VerificationFailed');
                }
            }
            catch (Exception $ex)
            {
                $resp['error'] = 1;
                $resp['msg']   = $ex->getMessage();
            }
        }

        // Function to send Testimonial to appsmav
        private function send_testimonial_to_appsmav($post_id, $post) {
            try {
                $app_config = gr_get_app_config();
                if (empty($app_config['points']['loyalty_campaign_enabled']) || empty($app_config['reviews']['global_review_enabled']) || empty($app_config['reviews']['is_testimonial_enabled'])) {
                    return;
                }

                $post_meta = get_post_meta($post_id);
                if (!isset($post_meta['email']) || empty($post_meta['email'][0]) || self::_isActiveCampaign() === false)
                    return;

                //Post Stauts: pending, publish, draft, trash
                $post_status = array(
                    'pending' => 'unapproved',
                    'publish' => 'approved',
                    'trash' => 'trash',
                    'draft' => 'unapproved'
                );
                $comment_status = isset($post_status[$post->post_status]) ? $post_status[$post->post_status] : 'unapproved';
                $testimonial_key = !empty($post->post_name) ? $post->post_name : '';
                $testimonial_url = get_post_field('guid', $post->ID);

                $review_details['comment_ID'] = !empty($post->ID) ? $post->ID : 0;
                $review_details['comment_post_ID'] = !empty($post->ID) ? $post->ID : 0;
                $review_details['comment_author_email'] = !empty($post_meta['email'][0]) ? $post_meta['email'][0] : '';
                $review_details['comment_date'] = !empty($post->post_date) ? $post->post_date : '';

                $review_details['comment_content'] = !empty($post->post_content) ? $post->post_content : '';
                $review_details['comment_approved'] = !empty($post->post_status == 'publish') ? 1 : 0;
                $review_details['comment_status'] = $comment_status;
                $review_details['rating'] = !empty($post_meta['star_rating'][0]) ? $post_meta['star_rating'][0] : '';
                $review_details['product_key'] = !empty($testimonial_key) ? $testimonial_key : '';
                $review_details['product_url'] = !empty($testimonial_url) ? $testimonial_url : '';
                $review_details['sub_type'] = 'wpm-testimonial';

                // Check the user role is allowed to proceed
                $user = get_user_by('email', $post_meta['email'][0]);
                if (!empty($user)) {
                    $is_blocked_role = self::is_restricted_user_role($user->roles);
                    if ($is_blocked_role)
                        return;
                }

                $urlApi = self::$_callback_url . self::$_api_version . 'addReviewEntry';
                $this->callGrApiReview($review_details, $urlApi);
            } catch (Exception $ex) {

            }
        }

        public static function gr_woo_app_show_func($atts) {
            $content = '';
            try {
                $id = isset($atts['id']) ? trim($atts['id']) : '';
                $patternAlphaNum = '/^[a-zA-Z0-9_]+$/';
                if (empty($id) || !preg_match($patternAlphaNum, $id))
                    return '';

                $type = isset($atts['type']) ? trim($atts['type']) : '';
                $url = ($type == 'referral') ? 'contest/referral' : 'promo';
                $url = self::$_callback_url . $url . '/' . $id;

                if (isset($atts['rtype']) && $atts['rtype'] == 'link') {
                    $content = '<a class="gr-widget ec-widget" href="' . $url . '" >Rewards</a>';
                } else {
                    $content = '<div class="GREmbedContainer"><iframe data-grclass="gr_iframe_widget" class="gr_iframe_widget" vspace="0" hspace="0" width="100%" height="400px" src="' . $url . '" frameborder="0" allow="clipboard-read; clipboard-write">Rewards</iframe></div>
                    <script>
                    try{if("URLSearchParams"in window){var mavtoken,params={},searchParams=new URLSearchParams(window.location.search);searchParams.has("id_ref")?(params.id_ref=searchParams.get("id_ref"),searchParams.has("mavtoken")&&(params.mavtoken=searchParams.get("mavtoken"))):"undefined"==typeof Storage||void 0!==(mavtoken=localStorage.GRmavtoken)&&""!=mavtoken&&null!=mavtoken&&"null"!=mavtoken&&"NULL"!=mavtoken&&(params.mavtoken=mavtoken);for(var app_url,elems=document.querySelectorAll("[data-grclass]"),sParams=new URLSearchParams(params),i=0;i<elems.length;i++)elems[i].id="ec_iframe_"+i,0<Object.keys(params).length&&(app_url=elems[i].src,app_url+=(-1==app_url.indexOf("?")?"?":"&")+sParams,elems[i].src=app_url),void 0!==elems[i].className&&""!=elems[i].className||(elems[i].className="gr_iframe_widget")}}catch(a){}
                    </script>';
                }
            } catch (Exception $ex) {
            }

            return $content;
        }

        public static function gr_woo_points_balance($atts)
        {
            $content = '';
            try
            {
                $content = '<span class="gr_total_points">0</span>';
            } catch (Exception $ex) {
            }

            return $content;
        }
        /**
         * add a menu
         */
        public function add_menu()
        {
            try {
                add_options_page('GR Connect Settings', 'Gratisfaction', 'manage_options', 'grconnect', array(&$this, 'gr_plugin_settings_page'));
            } catch (Exception $ex) {

            }
        }

        /**
         * Menu Callback
         */
        public function gr_plugin_settings_page()
        {
            try {
                if(!current_user_can('manage_options'))
                    wp_die(__('You do not have sufficient permissions to access this page.'));

                // Render the settings template
                if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
                {
                    $frame_url = 'about:blank';
                    if(get_option('grconnect_register', 0) == 1)
                    {
                        $arr['id_shop'] = get_option('grconnect_shop_id', 0);
                        $arr['admin_email'] = get_option('grconnect_admin_email');
                        $arr['payload'] = get_option('grconnect_payload', 0);

                        if(empty($arr['payload']))
                            update_option('grconnect_register', 2);

                        $frame_url = self::$_callback_url . 'autologin?id_shop=' . $arr['id_shop'] . '&admin_email=' . urlencode($arr['admin_email']) . '&payload=' . $arr['payload'] . '&cur=' . get_option('woocommerce_currency', 'USD');
                    }

                    include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
                }
                else
                {
                    echo "<div class='highErrormsg'>
                            WooCommerce plugin not found! Please install WooCommerce plugin & then activate Gratisfaction.
                            If you do not want to install the WooCommerce plugin but still use Gratisfaction, then install Gratisfaction from WordPress plugin directory <a href='https://wordpress.org/plugins/gratisfaction-social-contests-referral-loyalty-rewards-program-by-appsmav/'>https://wordpress.org/plugins/gratisfaction-social-contests-referral-loyalty-rewards-program-by-appsmav/</a>
                        <div>";
                }
            } catch (Exception $ex) {

            }
        }

        public function gr_before_cart()
        {
            try
            {
                if (empty(WC()->cart))
                    throw new Exception("Empty Cart");

                if (empty(WC()->session))
                    throw new Exception("Invalid session");

                $items = WC()->cart->get_cart();

                if (!empty($items) && is_user_logged_in() && (is_cart() || is_checkout()))
                {
                    $user_point = WC()->session->get('gr_user_points', 'not_fetched');
                    if (is_cart() || $user_point == 'not_fetched') {
                        $this->get_settings_api(1);
                    }
                    self::gr_calc_point_value();
                }

                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if((empty($items) && WC()->cart->has_discount($redeem_coupon))  || WC()->session->get('gr_pbp_mode', 'points') == 'coupon')
                {
                    if(WC()->cart->has_discount($redeem_coupon)) {
                        WC()->cart->remove_coupon($redeem_coupon);//remove_discount
                    }
                    WC()->session->set('gr_user_max_discount', 0);
                    WC()->session->set('gr_user_deduct_points', 0);
                }
                else if(WC()->session->get('gr_user_points', 0) <= 0)
                {
                    if (WC()->cart->has_discount($redeem_coupon))
                        WC()->cart->remove_coupon($redeem_coupon);//remove_discount

                    WC()->session->set('gr_user_max_discount', 0);
                    WC()->session->set('gr_user_deduct_points', 0);
                }

                // PBP Auto apply
                $max_discount = WC()->session->get('gr_user_max_discount', 0);
                if (!empty($items) && is_user_logged_in() && (is_cart() || is_checkout()) && $max_discount > 0 && WC()->session->get('gr_pbp_mode', 'points') == 'points')
                {
                    $gr_pbp_auto_apply = WC()->session->get('gr_pbp_auto_apply', 0);
                    $gr_pbp_auto_apply_done = WC()->session->get('gr_pbp_auto_apply_done', 0); // First time only we need to set automatically
                    if (empty($gr_pbp_auto_apply_done) && !empty($gr_pbp_auto_apply))
                    {
                        $applied_coupons = WC()->cart->applied_coupons;
                        if (empty($applied_coupons))
                        {
                            WC()->cart->add_discount($redeem_coupon);
                            WC()->session->set('gr_pbp_auto_apply_done', 1);
                            WC()->session->set('gr_discount_applied', 1);
                        }
                    }

                }

            }
            catch(Exception $e)
            { }
        }

        public function gr_ajax_check_login()
        {
            try
            {
                $email = sanitize_email( $_POST['grconnect_login_email'] );
                if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                    throw new Exception("Please enter valid email");

                if(empty($_POST['grconnect_login_pwd']))
                    throw new Exception("Please enter password");

                $res = array();
                $params = array();
                $adminEmailTemp   = get_option('grconnect_admin_email');
                $adminEmail       = empty($adminEmailTemp) ? $email : $adminEmailTemp;
                $params["action"] = 'login';
                $params["app"] = 'gr';
                $params['email'] = $email;
                $params['admin_email'] = $adminEmail;
                $params['password'] = sanitize_text_field( $_POST['grconnect_login_pwd'] );
                $params['shop_url'] = get_option('siteurl');

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($params)
                                ->exec(self::$_api_url);
                $resp = $httpObj->getResponse();

                if(!empty($resp))
                    $resp = json_decode($resp, true);

                if(empty($resp['error']) && !empty($resp['id_shop']))
                {
                    update_option('grconnect_admin_email', $adminEmail);
                    update_option('grconnect_shop_id', $resp['id_shop']);
                    update_option('grconnect_appid', $resp['id_site']);
                    update_option('grconnect_secret', $resp['secret']);
                    update_option('grconnect_payload', $resp['pay_load']);
                    update_option('grconnect_register', 1);

                    $res['error'] = 0;
                    $res['frame_url'] = self::$_callback_url . 'autologin?id_shop=' . $resp['id_shop'] . '&admin_email=' . urlencode($adminEmail) . '&payload=' . $resp['pay_load'] . '&cur=' . get_option('woocommerce_currency', 'USD');

                    // Update WP plugin status
                    $paramStatus = array('app' => 'gr', 'plugin_type' => 'WP', 'status' => 'activate', 'id_shop' => $resp['id_shop'], 'id_site' => $resp['id_site'], 'payload' => $resp['pay_load']);
                    $urlStatus = self::$_callback_url . self::$_api_version . 'pluginStatus';

                    $httpObj = (new HttpRequestHandler)
                                    ->setPostData($paramStatus)
                                    ->exec($urlStatus);
                    $httpObj->getResponse();
                }
                else
                {
                    $res['error'] = 1;
                    $res['message'] = (!empty($resp['message'])) ? $resp['message'] : "Invalid Email / Password";
                }
            }
            catch(Exception $ex)
            {
                $res['error'] = 1;
                $res['message'] = $ex->getMessage();
            }

            die(json_encode($res));
        }

        public function gr_ajax_check_settings()
        {
            try
            {
                $raffd = !empty($_POST['raffd']) ? sanitize_text_field($_POST['raffd']) : '';
                $email = get_option('grconnect_admin_email');
                if(isset($_POST['admin_email']))
                    $email = sanitize_email($_POST['admin_email']);

                $param['email'] = $email;
                $param['raffd'] = $raffd;
                $param['shop_url'] = get_option('siteurl');
                $param["app"] = 'gr';
                $param["action"] = 'verifyShopExists';
                $param['payload'] = get_option('grconnect_payload', 0);
                $param['plugin_type'] = 'WP';
                $param['plugin_version'] = self::$_plugin_version;

                $res = array();
                $httpObj = (new HttpRequestHandler)
                                ->setPostData($param)
                                ->exec(self::$_api_url);
                $res = $httpObj->getResponse();

                if(!empty($res))
                    $res = json_decode($res, true);

                if(!empty($res['is_shop']) && $res['is_shop'] == 1)
                {
                    update_option('grconnect_admin_email', $email);
                    update_option('grconnect_shop_id', $res['id_shop']);
                    update_option('grconnect_appid', $res['id_site']);
                    update_option('grconnect_secret', $res['secret']);
                    update_option('grconnect_payload', $res['pay_load']);
                    update_option('grconnect_register', 1);

                    $res['gr_reg'] = 0;
                    $res['frame_url'] = self::$_callback_url . 'autologin?id_shop=' . $res['id_shop'] . '&admin_email=' . urlencode($email) . '&payload=' . $res['pay_load'] . '&cur=' . get_option('woocommerce_currency', 'USD');

                    // Update WP plugin status
                    $paramStatus = array('app' => 'gr', 'plugin_type' => 'WP', 'status' => 'activate', 'id_shop' => $res['id_shop'], 'id_site' => $res['id_site'], 'payload' => $res['pay_load']);
                    $urlStatus = self::$_callback_url . self::$_api_version . 'pluginStatus';

                    $httpObj = (new HttpRequestHandler)
                                    ->setPostData($paramStatus)
                                    ->exec($urlStatus);
                    $httpObj->getResponse();

                }
                else if(!empty($res['is_shop']) && $res['is_shop'] == 2)
                {
                    $params = array();
                    $ip_info = self::_getIPDetails();
                    $current_user = wp_get_current_user();

                    $params['action'] = 'createaccount';
                    $params['firstname'] = $current_user->user_firstname;
                    $params['lastname'] = $current_user->user_lastname;
                    $params['companyname'] = get_bloginfo('name');
                    $params['companyname'] = !empty($params['companyname']) ? get_bloginfo('name') : 'Your Business name';
                    $params['address1'] = '***'; //Dummy
                    $params['city'] = empty($ip_info['city']) ? '***' : $ip_info['city'];
                    $params['state'] = empty($ip_info['region_name']) ? '***' : $ip_info['region_name'];
                    $params['postcode'] = '1'; //Dummy;
                    $params['country'] = empty($ip_info['country_code']) ? 'US' : $ip_info['country_code'];
                    $params['currency'] = ($params["country"] === 'AU') ? 3 : 1;
                    $params['currency_code'] = get_option('woocommerce_currency', 'USD');
                    $params['phonenumber'] = '1234567890'; //Dummy
                    $params['notes'] = 'Wordpress';
                    $params['app'] = 'gr';
                    $params['email'] = $email;
                    $params["email_user"] = $email;
                    $params['raffd'] = $raffd;
                    $params['url'] = get_option('siteurl');
                    $params["name"] = $params["companyname"];
                    $params['type'] = 'url';
                    $params['plugin_type'] = 'WP';
                    $params['shop_url'] = get_option('siteurl');
                    $params['shop_name'] = $params['companyname'];

                    $params['campaign_name'] = 'REWARDS';
                    $params['timezone'] = 'America/Chicago'; //Dummy $p['grappsmav_reg_timezone'];
                    $params['date_format'] = 'd/m/Y'; //Dummy$p['grappsmav_reg_date_format'];
                    $params['exclusion_period'] = 0; //$p['grconnect_reg_exclusion_period'];
                    $params['app_lang'] = str_replace('-', '_', get_bloginfo('language'));

                    $myaccount_page_id = get_option('woocommerce_myaccount_page_id');
                    $myaccount_page_url = get_permalink($myaccount_page_id);
                    $params['login_url'] = $myaccount_page_url;
                    $params['payload'] = get_option('grconnect_payload', 0);
                    $params['plugin_version'] = self::$_plugin_version;

                    $res = array();
                    $httpObj = (new HttpRequestHandler)
                                    ->setPostData($params)
                                    ->exec(self::$_api_url);
                    $res = $httpObj->getResponse();

                    if(!empty($res))
                        $res = json_decode($res, true);

                    if(empty($res['error']) && !empty($res['id_shop']))
                    {
                        update_option('grconnect_admin_email', $email);
                        update_option('grconnect_shop_id', $res['id_shop']);
                        update_option('grconnect_appid', $res['id_site']);
                        update_option('grconnect_secret', $res['secret']);
                        update_option('grconnect_payload', $res['pay_load']);
                        update_option('grconnect_register', 1);

                        $res['frame_url'] = self::$_callback_url . 'autologin?id_shop=' . $res['id_shop'] . '&admin_email=' . urlencode($email) . '&payload=' . $res['pay_load'] . '&cur=' . get_option('woocommerce_currency', 'USD');
                        $res['gr_reg'] = 0;
                    }
                    else if($res['error'] == 1)
                    {
                        $res['gr_reg'] = 1;
                    }
                    else if($res['error'] == 2 || $res['error'] == 3)
                    {
                        update_option('grconnect_register', 3);
                        $res['gr_reg'] = 2;
                    }
                    else
                    {
                        $res['gr_reg'] = 4;
                    }
                }
                else
                {
                    $res['gr_reg'] = 1;
                }
            }
            catch(Exception $e)
            {
                $res['gr_reg'] = 6;
                $res['error']       = $e->getMessage();
            }

            die(json_encode($res));
        }

        public function gr_show_redeem_points_lable()
        {
            try
            {
                if (
                    empty(WC()->session) || WC()->session->get('gr_loyalty_campaign_enabled', 0) != 1 ||
                    WC()->session->get('redeem_point_enabled') == 0 || self::_isActiveCampaign() === false
                    || empty(WC()->cart)
                ) {
                    return;
                }

                //Show redeem label only for points
                if(WC()->session->get('gr_pbp_mode', 'points') != 'points') {
                    return;
                }

                $is_checkout_page = 0;

                if(is_user_logged_in() && is_checkout()) // Removed because before cart we r callign is_cart() ||
                {
                    $is_checkout_page = 1;
                    $this->get_settings_api(1);
                }

                if(is_user_logged_in())
                {
                    $user = wp_get_current_user();

                    // Check the user role is allowed to proceed
                    $is_blocked_role = self::is_restricted_user_role($user->roles);
                    if ($is_blocked_role)
                        return;
                }
                else if (WC()->session->get('gr_disable_non_loggedin', 0) == 1)
                {
                    return;
                }

                $redeem_point_lable = WC()->session->get('gr_redeem_point_per_dollar_lable');

                $items = WC()->cart->get_cart();

                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if(!WC()->cart->has_discount($redeem_coupon))
                {
                    WC()->session->set('gr_user_applied_discount', 0);
                    WC()->session->set('gr_discount_applied', 0);
                }
                else if(WC()->session->get('gr_user_applied_discount') <= 0 || empty($items))
                {
                    WC()->cart->remove_coupon($redeem_coupon);
                    WC()->session->set('gr_user_max_discount', 0);
                    WC()->session->set('gr_user_deduct_points', 0);
                }

                self::gr_calc_point_value();

                echo '<style>.gr_rewards_remove_discount{opacity:.6}#gr_checkout_redeem_lable{text-align:right;}.grPointsRedeem{padding:10px;border:1px dashed;}</style>';

                if(WC()->session->get('gr_user_max_discount', 0) > 0 && WC()->session->get('gr_user_deduct_points', 0) >= 1)
                {
                    $discount = WC()->session->get('gr_user_max_discount');
                    $points = WC()->session->get('gr_user_deduct_points');
                    $redeem_point_lable = str_replace('{points}', $points, $redeem_point_lable);
                    $redeem_point_lable = str_replace('{points_value}', wc_price($discount), $redeem_point_lable);

                    $point_lable = ($points > 1) ? WC()->session->get('gr_points_lable') : WC()->session->get('gr_point_lable');
                    $redeem_point_lable = '<p class="grPointsRedeem" id="gr_checkout_lable_top">' . str_replace('{points_label}', $point_lable, $redeem_point_lable);

                    $extra_pay_points = WC()->session->get('gr_user_extra_pay_points', 0);
                    $extra_pay_apply = '';
                    $extra_pay_confirm = 'display:none;';
                    if (!empty($extra_pay_points))
                    {
                        $extra_pay_apply = 'display:none;';
                        $extra_pay_confirm = '';
                        $extra_point_lable = ($extra_pay_points > 1) ? WC()->session->get('gr_points_lable') : WC()->session->get('gr_point_lable');

                        $extra_points_info = WC()->session->get('gr_redeem_extra_point_info', '');
                        $extra_points_info = str_replace('{points}', $extra_pay_points, $extra_points_info);
                        $extra_points_info = str_replace('{points_label}', $extra_point_lable, $extra_points_info);
                        $redeem_point_lable .= '<span style="color:red;"><br>'.$extra_points_info.'</span>';
                    }

                    $redeem_point_lable .= '</p>';

                    // add 'Apply Discount' button
                    if(WC()->session->get('gr_user_applied_discount') == 0)
                    {
                        $btn_agree = WC()->session->get('gr_btn_redeem_confirm', '');
                        $redeem_point_lable .= '<form class="gr_apply_discount" action="' . esc_url(get_permalink(wc_get_page_id('cart'))) . '" method="post">';
                        $redeem_point_lable .= '<input type="hidden" name="gr_rewards_apply_discount" class="gr_rewards_apply_discount" value="1" />';
                        $redeem_point_lable .= '<input type="submit" class="button gr_rewards_apply_discount_confirm" style="'.$extra_pay_confirm.'" value="'.$btn_agree.'" />';
                        $redeem_point_lable .= '<input type="submit" class="button gr_rewards_apply_discount" style="'.$extra_pay_apply.'" value="' . WC()->session->get('gr_redeem_btn_text') . '" />';
                        $redeem_point_lable .= '</form>';

                        WC()->session->set('gr_user_max_discount', 0);
                        WC()->session->set('gr_user_deduct_points', 0);
                    }
                    else
                    {
                        $redeem_point_lable = '';

                        if(WC()->session->get('gr_user_applied_discount') != $discount)
                        {
                            WC()->session->set('gr_user_max_discount', $discount);
                            WC()->session->set('gr_user_deduct_points', $points);

                            $gr_user_max_discount = WC()->session->get('gr_user_max_discount');
                            WC()->session->set('gr_user_applied_discount', (!empty($gr_user_max_discount) ? $gr_user_max_discount : 0));
                        }

                    }

                    echo '<div id="gr_checkout_redeem_lable">' . $redeem_point_lable . '</div>';

                    wc_enqueue_js("
                        var gr_busy = false;
                        jQuery('body').on('click', '.gr_rewards_apply_discount', function(e) {
                            e.preventDefault();

                            if(gr_busy)
                                return false;

                            gr_busy = true;
                            jQuery.post(
                                '" . admin_url('admin-ajax.php') . "',
                                {action:'apply_gr_discount'},
                                function(response){
                                    gr_busy = false;

                                    if('".$is_checkout_page."' == '1')
                                    {
                                        jQuery('#gr_checkout_redeem_lable').hide();
                                        jQuery('body').trigger('update_checkout');
                                        return false;
                                    }
                                    else
                                    {
                                        var obj = jQuery(\"[name='update_cart']\");
                                        jQuery('body').trigger('wc_update_cart');

                                        if(obj.length > 0)
                                            jQuery('body').trigger('wc_update_cart');
                                    }
                                }, 'json');
                            return false;
                        });

                        jQuery('body').on('click', '.gr_rewards_apply_discount_confirm', function(e) {
                            e.preventDefault();

                            jQuery('.gr_rewards_apply_discount_confirm').hide();
                            jQuery('.gr_rewards_apply_discount').fadeIn('slow');
                            return false;
                        });
                    ");
                }
            }
            catch(Exception $e)
            {

            }
        }

        public function gr_rewards_apply_discount()
        {
            try
            {
                // only apply on cart and from apply discount action
                if(!is_cart() || empty(WC()->session) || (!isset($_POST['gr_rewards_apply_discount']) && !isset($_POST['gr_rewards_remove_discount']) ))
                    return;

                // Get discount amount if set and store in session
                if(isset($_POST['gr_rewards_remove_discount']))
                {
                    WC()->session->set('gr_user_applied_discount', 0);
                    WC()->session->set('gr_discount_applied', 0);
                }
                else
                {
                    $gr_user_max_discount = WC()->session->get('gr_user_max_discount');
                    WC()->session->set('gr_user_applied_discount', (!empty($gr_user_max_discount) ? $gr_user_max_discount : 0));
                }
            }
            catch(Exception $e)
            {

            }
        }

        public function gr_custom_discount()
        {
            try
            {
                if(empty(WC()->session))
                    return;

                self::gr_calc_point_value();

                if(WC()->session->get('gr_user_max_discount', 0) > 0 && WC()->session->get('gr_user_deduct_points', 0) >= 1)
                {
                    $discount = WC()->session->get('gr_user_max_discount');
                    if(WC()->session->get('gr_user_applied_discount') != $discount)
                    {
                        WC()->session->set('gr_user_applied_discount', (empty($discount) ? 0 : $discount));
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }

        public function gr_custom_discount_ajax()
        {
            try
            {
                if(empty(WC()->session) || empty(WC()->cart))
                    return;

                if(WC()->session->get('gr_discount_applied') != 1)
                {
                    $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                    WC()->cart->add_discount($redeem_coupon);
                    WC()->session->set('gr_discount_applied', 1);
                }
            }
            catch(Exception $e)
            {
            }
        }

        public function gr_update_lable_carts_page()
        {
            try {

            if(empty(WC()->session) || WC()->session->get('redeem_point_enabled') == 0)
                return;

            $redeem_point_lable = WC()->session->get('gr_redeem_point_per_dollar_lable');
            $redeem_point_lable = WC()->session->get('gr_redeem_point_per_dollar_lable');

            self::gr_calc_point_value();

            $discount = WC()->session->get('gr_user_max_discount', 0);
            $points = WC()->session->get('gr_user_deduct_points', 0);
            $redeem_point_lable = str_replace('{points}', $points, $redeem_point_lable);
            $redeem_point_lable = str_replace('{points_value}', wc_price($discount), $redeem_point_lable);
            $point_lable = ($points > 1) ? WC()->session->get('gr_points_lable') : WC()->session->get('gr_point_lable');
            $redeem_point_lable = str_replace('{points_label}', $point_lable, $redeem_point_lable);
            $res['msg'] = $redeem_point_lable;

            die(json_encode($res));

            } catch (Exception $ex) {

            }
        }

        public function gr_get_cart_details()
        {
            try
            {
                if (empty(WC()->session) || WC()->session->get('redeem_point_enabled') == 0)
                return;

                self::gr_calc_point_value();

                $res = array(
                    'discounted_amount'   => WC()->session->get('gr_user_max_discount', 0),
                    'points'              => WC()->session->get('gr_user_deduct_points', 0),
                    'cart_count'          => count(WC()->cart->get_cart()),
                    'is_discount_applied' => 0,
                    'extra_pbp'           => WC()->session->get('gr_user_extra_pay_points', 0)
                );

                if ($res['cart_count']  == 0) {
                    //while cart is empty this session should set to 0
                    WC()->session->set('gr_discount_applied', 0);
                }
                //To update the popup in checkut page
                if ($_POST['event_type'] == 'remove_coupon') {
                    $res['is_discount_applied'] = 0;
                } else if (!empty($res['discounted_amount'])) {
                    $res['is_discount_applied'] =  WC()->session->get('gr_discount_applied', 0);
                }

                die(json_encode($res));
            }
            catch (Exception $ex)
            { }
        }

        public function gr_show_product_review_lable()
        {
            try
            {
                if(
                    empty(WC()->session) ||
                    WC()->session->get('gr_loyalty_campaign_enabled', 0) != 1 ||
                    WC()->session->get('gr_global_review_enabled', 0) == 0 ||
                    WC()->session->get('gr_global_review_lable_enabled', 0) == 0 ||
                    !is_product() ||
                    self::_isActiveCampaign() === false
                ) {
                    return;
                }

                // Check the user role is allowed to proceed
                if(is_user_logged_in())
                {
                    $user = wp_get_current_user();

                    $is_blocked_role = self::is_restricted_user_role($user->roles);
                    if ($is_blocked_role)
                        return;
                }
                else if (WC()->session->get('gr_disable_non_loggedin', 0) == 1)
                {
                    return;
                }

                $label_color = WC()->session->get('gr_label_color', '#4bb543');
                echo '<style>.grPointsReview{background:#eaffea;padding:7px 15px;clear:both;color:'.$label_color.';margin:12px 0;display:table;font-size:100%;border:1px solid #dff3df}.grPointsReview small{color:inherit;text-transform:none;font-size:85%}</style>';
                echo '<div id="gr_product_points_review_lable" class="grPointsReview"><small>'.WC()->session->get('gr_global_review_lable', '').'</small></div>';
            }
            catch(Exception $ex)
            { }
        }

        public function gr_show_single_product_buy_lable()
        {
            try
            {
                if ( !is_product() )
                    return;

                $product = wc_get_product( get_the_ID() );
                if ( empty($product) )
                    return;

                $display_typ = $product->get_type();
                if($display_typ == 'external')
                    return;

                $this->get_settings_api();

                if(
                    empty(WC()->session) ||
                    WC()->session->get('gr_loyalty_campaign_enabled', 0) != 1 ||
                    WC()->session->get('redeem_point_enabled', 0) == 0 ||
                    WC()->session->get('gr_redeem_theme_status', 0) == 0 ||
                    self::_isActiveCampaign() === false) {
                    return;
                }

                // Check the user role is allowed to proceed
                if(is_user_logged_in())
                {
                    $user = wp_get_current_user();

                    $is_blocked_role = self::is_restricted_user_role($user->roles);
                    if ($is_blocked_role)
                        return;
                }
                else if (WC()->session->get('gr_disable_non_loggedin', 0) == 1)
                {
                    return;
                }

                // Check redeem points restricted for this product
                $is_blocked_redeem = self::is_redeem_restricted_products($product->get_id());
                if ($is_blocked_redeem)
                    return;

                $restrict_status = WC()->session->get('gr_redeem_restrict_status', 0);
                $redeem_max_point = WC()->session->get('gr_redeem_max_point', 0);
                $redeem_point = WC()->session->get('gr_redeem_point_per_dollar');

                if($display_typ == 'variable' || $display_typ == 'grouped')
                {
                    $min_price = $max_price = 0;
                    if ($display_typ == 'grouped') {
                        $list_products = $product->get_children();
                    }
                    else if ($display_typ == 'variable') {
                        $available_variations = $product->get_available_variations();
                        if (empty($available_variations))
                            return;
                        $list_products = array_column($available_variations, 'variation_id');
                    }

                    if ( is_array($list_products) && count($list_products)>0 )
                    {
                        $product_price_range = array();
                        foreach ($list_products as $value)
                        {
                            $p_price = get_post_meta($value, '_price', true);

                            if (wc_tax_enabled() && !wc_prices_include_tax())
                                $p_price = wc_get_price_including_tax($product, array('qty' => 1, 'price' => $p_price));

                            $product_price_range[] = ($p_price>0) ? $p_price : 0;
                        }

                        if (!empty($product_price_range))
                        {
                            $min_price = min($product_price_range);
                            $max_price = max($product_price_range);
                        }
                    }

                    if (empty($min_price) && empty($max_price))
                        return;

                    $min_points = ceil($min_price * $redeem_point);
                    $max_points = ceil($max_price * $redeem_point);

                    if (!empty($restrict_status)) {
                        $min_points = (!empty($redeem_max_point) && $min_points>$redeem_max_point) ? $redeem_max_point : $min_points;
                        $max_points = (!empty($redeem_max_point) && $max_points>$redeem_max_point) ? $redeem_max_point : $max_points;
                    }

                    $discount = ($min_points == $max_points) ? $max_points : $min_points . ' - ' . $max_points;
                }
                else
                {
                    $price = get_post_meta($product->get_id(), '_price', true);
                    if (empty($price))
                        return;

                    $price = round($price, 2);
                    if (wc_tax_enabled() && !wc_prices_include_tax())
                    {
                        $tax_inc = wc_get_price_including_tax($product, array('qty' => 1, 'price' => $price));
                        $tax_exc = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $price));
                        $p_tax = $tax_inc - $tax_exc;

                        $price = $price + $p_tax;
                    }

                    $discount = ceil($price * $redeem_point);
                    $max_points = $discount;

                    // update maximum usable points
                    if (!empty($restrict_status) && !empty($redeem_max_point) && $discount > $redeem_max_point)
                    {
                        $discount   = $redeem_max_point;
                        $max_points = $redeem_max_point;
                    }
                }

                if(empty($discount))
                    return;

                $redeem_point_lable = WC()->session->get('gr_redeem_point_product_per_dollar_lable');
                $redeem_point_lable = str_replace('{points}', $discount, $redeem_point_lable);
                $point_lable = ($max_points > 1) ? WC()->session->get('gr_points_lable') : WC()->session->get('gr_point_lable');
                $redeem_point_lable = str_replace('{points_label}', $point_lable, $redeem_point_lable);

                $redeem_restrict_terms = WC()->session->get('gr_pay_redeem_restrict_terms', '');
                $label_redeem_restriction_apply = WC()->session->get('gr_label_redeem_restriction_apply', '');
                if (!empty($redeem_restrict_terms))
                {
                    $redeem_point_lable .= ' <span style="position:relative;"><span class="gr_restriction_apply_title" style="cursor: pointer;border-bottom: 1px dashed;color: initial;display: inline-block;"><b>'.$label_redeem_restriction_apply.'</b></span>';
                    $redeem_point_lable .= '<span style="display:none;" class="gr_restriction_apply_message"><span>'.$redeem_restrict_terms.'</span><span class="gr_restriction_apply_close">&times;</span></span></span>';

                    wc_enqueue_js("
                        jQuery('body').on('click', '.gr_restriction_apply_title', function(e) {
                            e.preventDefault();
                            jQuery('.gr_restriction_apply_message').toggle();
                            return false;
                        });

                        jQuery('body').on('click', '.gr_restriction_apply_close', function(e) {
                            e.preventDefault();
                            jQuery('.gr_restriction_apply_message').hide();
                            return false;
                        });
                    ");
                }

                $label_color = WC()->session->get('gr_label_color', '#4bb543');
                echo '<style>.grPointsPay{clear:both;color:'.$label_color.'} span.gr_restriction_apply_message{color: #333;text-decoration:none!important;position:absolute;right:0;top:100%;width:300px;background:#fff;padding:20px;box-shadow:0 10px 20px rgb(0 0 0 / 10%);font-size:14px;margin-top:8px;border:1px solid #f5f5f5;z-index: 1040;border-radius:4px} span.gr_restriction_apply_message:before{content:"";display:inline-block;position:absolute;right:23px;top:-7px;background:#fff;width:14px;height:14px;transform:rotate(45deg);border:1px solid #f5f5f5;border-bottom:0;border-right:0} span.gr_restriction_apply_close {cursor:pointer;position: absolute;right: 6px;top: 6px;font-family: serif;} @media only screen and (max-width:600px){span.gr_restriction_apply_message{position:relative;display:block;width:auto;padding:10px 15px;margin:5px 0;box-shadow:1px 2px 3px rgba(0,0,0,.1);font-size:13px}span.gr_restriction_apply_message:before{display:none}}</style>';
                echo '<div id="gr_product_points_buy_lable" class="grPointsPay"><small>' . $redeem_point_lable . '</small></div>';
            }
            catch(Exception $ex)
            {

            }
        }

        public function gr_show_single_product_lable()
        {
            try
            {
                if ( !is_product() )
                    return;

                $product = wc_get_product( get_the_ID() );
                if ( empty($product) )
                    return;

                $display_typ = $product->get_type();
                if ($display_typ == 'external')
                    return;

                $this->get_settings_api();

                $earn_exclude_products = array();
                if(!empty(WC()->session->get('gr_earn_exclude_products', ''))){
                    $earn_exclude_products = explode(',', trim(WC()->session->get('gr_earn_exclude_products', '')));
                    $earn_exclude_products = array_map('trim', $earn_exclude_products);
                }

                if(
                    empty(WC()->session) ||
                    WC()->session->get('gr_loyalty_campaign_enabled', 0) != 1 ||
                    WC()->session->get('earn_point_enabled', 0) == 0 ||
                    WC()->session->get('gr_purchase_theme_status', 0) == 0 ||
                    self::_isActiveCampaign() === false ||
                    in_array($product->get_id(), $earn_exclude_products)
                )
                    return;

                if(is_user_logged_in())
                {
                    $user = wp_get_current_user();

                    // Check the user role is allowed to proceed
                    $is_blocked_role = self::is_restricted_user_role($user->roles);
                    if ($is_blocked_role)
                        return;
                }
                else if (WC()->session->get('gr_disable_non_loggedin', 0) == 1)
                {
                    return;
                }

                $earn_point = WC()->session->get('gr_earn_point_per_dollar');
                if (empty($earn_point) || !is_numeric($earn_point))
                    return;

                $_roundoff_type = WC()->session->get('gr_roundoff_type', 'ROUND');

                $reward_type = WC()->session->get('gr_reward_points_type', '');
                if($display_typ == 'variable' || $display_typ == 'grouped')
                {
                    $min_price = $max_price = 0;
                    if ($display_typ == 'grouped') {
                        $list_products = $product->get_children();
                    }
                    else if ($display_typ == 'variable') {
                        $available_variations = $product->get_available_variations();
                        $list_products = array_column($available_variations, 'variation_id');
                    }

                    if ( is_array($list_products) && count($list_products)>0 )
                    {
                        $product_price_range = array();
                        foreach ($list_products as $value)
                        {
                            $p_price = get_post_meta($value, '_price', true);

                            if (wc_tax_enabled())
                            {
                                if (wc_prices_include_tax() && in_array($reward_type, array('TOTAL_EXCLUDE_TAX','SUBTOTAL')))
                                    $p_price = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $p_price));
                                else if (!wc_prices_include_tax() && !in_array($reward_type, array('TOTAL_EXCLUDE_TAX','SUBTOTAL')))
                                    $p_price = wc_get_price_including_tax($product, array('qty' => 1, 'price' => $p_price));
                            }

                            $product_price_range[] = ($p_price>0) ? $p_price : 0;
                        }

                        if (!empty($product_price_range))
                        {
                            $min_price = min($product_price_range);
                            $max_price = max($product_price_range);
                        }
                    }

                    if (empty($min_price) || empty($max_price) || $max_price < WC()->session->get('minimum_order_value', 0))
                        return;

                    $min_points = gr_roundoff(($min_price * $earn_point), $_roundoff_type);
                    $max_points = gr_roundoff(($max_price * $earn_point), $_roundoff_type);

                    $point_earn = ($min_points == $max_points) ? $max_points : $min_points . ' - ' . $max_points;
                }
                else
                {
                    $p_price = get_post_meta($product->get_id(), '_price', true);
                    if(empty($p_price))
                        return;

                    if (wc_tax_enabled())
                    {
                        $tax_inc = wc_get_price_including_tax($product, array('qty' => 1, 'price' => $p_price));
                        $tax_exc = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $p_price));
                        $p_tax = $tax_inc - $tax_exc;

                        if (wc_prices_include_tax() && in_array($reward_type, array('TOTAL_EXCLUDE_TAX','SUBTOTAL')))
                            $p_price = $p_price - $p_tax;
                        else if (!wc_prices_include_tax() && !in_array($reward_type, array('TOTAL_EXCLUDE_TAX','SUBTOTAL')))
                            $p_price = $p_price + $p_tax;
                    }

                    if ($p_price < WC()->session->get('minimum_order_value', 0))
                        return;

                    $point_earn = gr_roundoff(($p_price * $earn_point), $_roundoff_type);
                }

                if(empty($point_earn))
                    return;

                $point_lable = ($point_earn > 1) ? WC()->session->get('gr_points_lable') : WC()->session->get('gr_point_lable');

                $earn_point_lable = WC()->session->get('gr_earn_point_per_dollar_lable');
                $earn_point_lable = str_replace('{points}', $point_earn, $earn_point_lable);
                $earn_point_lable = str_replace('{points_label}', $point_lable, $earn_point_lable);

                $label_color = WC()->session->get('gr_label_color', '#4bb543');
                echo '<style>.grPointsEarn {display: table;position: relative;z-index: 0;padding: 6px 12px;color:'.$label_color.';}.grPointsEarn:before {content: "";display: block;position: absolute;left: 0;top: 0;width: 100%;height: 100%;background: '.$label_color.';z-index: -1;opacity: 0.2;border-radius: 4px;}</style>';
                echo '<p id="gr_product_points_lable" class="grPointsEarn">' . $earn_point_lable . '</p>';
            }
            catch(Exception $ex)
            {

            }
        }

        private function gr_calc_point_value()
        {
            try
            {
                if(empty(WC()->session) || WC()->session->get('redeem_point_enabled') == 0 || empty(WC()->cart))
                    return;

                // Delete old pay by points coupon after customer updated new paybypoints coupon
                $pay_coupon_old = WC()->session->get('gr_paybypoints_coupon_old', self::REDEEM_COUPON);
                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if(!empty(WC()->cart) && WC()->cart->has_discount($pay_coupon_old) && $redeem_coupon != $pay_coupon_old)
                {
                    WC()->cart->remove_coupon($pay_coupon_old);
                    WC()->session->set('gr_paybypoints_coupon_old', '');
                }

                // Get Currency ratio
                $ratio = self::gr_get_currency_ratio();
                WC()->session->set('gr_user_extra_pay_points', 0);

                $redeem_point = WC()->session->get('gr_redeem_point_per_dollar')/$ratio;
                $cart_total = WC()->cart->subtotal;
                $discount = floor(WC()->session->get('gr_user_points') / $redeem_point);

                $applied_coupons = WC()->cart->applied_coupons;
                if (!empty($applied_coupons))
                {
                    foreach($applied_coupons as $cart_coupon)
                    {
                        if (strtolower($cart_coupon) != strtolower($redeem_coupon))
                            $cart_total -= WC()->cart->get_coupon_discount_amount($cart_coupon);
                    }
                }

                // Redeem points restriction calculation starts here
                $restrict_status = WC()->session->get('gr_redeem_restrict_status', 0);
                $min_point = WC()->session->get('gr_redeem_min_point', 0);
                if (!empty($restrict_status))
                {
                    // Restriction by Exclude product & Exclude category
                    $restriction_cart_total = array();
                    $exclude_products = WC()->session->get('gr_pay_exclude_products', '');
                    $exclude_categories = WC()->session->get('gr_pay_exclude_categories', '');

                    if (!empty($exclude_products) || !empty($exclude_categories))
                    {
                        $total_restrict_amount = 0;
                        $items = WC()->cart->get_cart();
                        foreach ($items as $product)
                        {
                            $is_blocked_redeem = self::is_redeem_restricted_products($product['product_id']);
                            if ($is_blocked_redeem) {
                                $product['line_subtotal'] = empty($product['line_subtotal']) ? 0 : $product['line_subtotal'];
                                $product['line_subtotal_tax'] = empty($product['line_subtotal_tax']) ? 0 : $product['line_subtotal_tax'];
                                $total_restrict_amount += $product['line_subtotal'] + $product['line_subtotal_tax'];
                            }
                        }

                        if (!empty($total_restrict_amount) && $total_restrict_amount > 0)
                        {
                            $restriction_cart_total['product_category'] = $cart_total - $total_restrict_amount;
                        }
                    }

                    // Restriction by cart total amount percentage
                    $redeem_cart_percent = WC()->session->get('gr_redeem_cart_percent', 0);
                    if (!empty($redeem_cart_percent) && is_numeric($redeem_cart_percent) && $redeem_cart_percent > 0)
                    {
                        $restriction_cart_total['cart_percent'] = ($redeem_cart_percent / 100) * $cart_total;
                    }

                    // Restriction by Maximum usage point check
                    $max_point = WC()->session->get('gr_redeem_max_point', 0);
                    if (!empty($max_point) )
                    {
                        $discount_max_point = floor($max_point / $redeem_point);
                        if ($discount > $discount_max_point)
                        {
                            $restriction_cart_total['max_point'] = $discount_max_point;
                        }
                    }

                    // Check if user having less points than minimum use points
                    if (WC()->session->get('gr_user_points', 0) < $min_point)
                        $restriction_cart_total['min_point'] = 0;

                    // Assign the least restriction value as the cart total
                    if (!empty($restriction_cart_total))
                    {
                        $lowest_restricted_total = min($restriction_cart_total);
                        $cart_total = ($lowest_restricted_total < $cart_total) ? $lowest_restricted_total : $cart_total;
                        $cart_total = ($cart_total < 0) ? 0 : $cart_total;  // Check to avoid negative value
                    }
                }

                if($cart_total > $discount)
                {
                    $points = ceil($discount * $redeem_point);
                }
                else
                {
                    $points = ceil($cart_total * $redeem_point);
                    $discount = $cart_total;
                }

                // Minimum required point check
                if (!empty($restrict_status) && !empty($min_point) && !empty($cart_total))
                {
                    $cart_total_points = ceil($cart_total * $redeem_point);
                    if ($cart_total_points < $min_point)
                    {
                        $points = $min_point;

                        $extra_points = $min_point - $cart_total_points;
                        WC()->session->set('gr_user_extra_pay_points', $extra_points);
                    }
                }

                if($discount > 0 && $points >= 1)
                {
                    WC()->session->set('gr_user_max_discount', $discount);
                    WC()->session->set('gr_user_deduct_points', $points);

                    if(WC()->cart->has_discount($redeem_coupon) && WC()->session->get('gr_user_applied_discount') != $discount)
                    {
                        WC()->session->set('gr_user_applied_discount', (empty($discount) ? 0 : $discount));
                    }

                }
                else
                {
                    WC()->session->set('gr_user_max_discount', 0);
                    WC()->session->set('gr_user_deduct_points', 0);
                }
            } catch (Exception $ex) {
                WC()->session->set('gr_user_max_discount', 0);
                WC()->session->set('gr_user_deduct_points', 0);
            }
        }

        private function set_app_settings_session($app_config)
        {
            try
            {
                if(empty(WC()->session))
                    return;

                WC()->session->set('gr_api_sess', $app_config['date_updated']);
                WC()->session->set('gr_loyalty_campaign_enabled', $app_config['points']['loyalty_campaign_enabled']);
                WC()->session->set('earn_point_enabled', $app_config['points']['earn_point_enabled']);
                WC()->session->set('gr_purchase_theme_status', $app_config['points']['purchase_theme_status']);
                WC()->session->set('redeem_point_enabled', $app_config['points']['redeem_purchase_status']);
                WC()->session->set('gr_roundoff_type', $app_config['points']['gr_roundoff_type']);
                WC()->session->set('gr_redeem_point_per_dollar', $app_config['points']['redeem_point_per_dollar']);
                WC()->session->set('gr_redeem_theme_status', $app_config['points']['redeem_theme_status']);
                WC()->session->set('minimum_order_value', $app_config['points']['minimum_order_value']);
                WC()->session->set('gr_redeem_point_per_dollar_lable', stripslashes($app_config['lang']['redeem_point_per_dollar_lable']));
                WC()->session->set('gr_redeem_point_product_per_dollar_lable', stripslashes($app_config['lang']['redeem_point_product_per_dollar_lable']));
                WC()->session->set('gr_earn_point_per_dollar', stripslashes($app_config['points']['earn_point_per_dollar']));
                WC()->session->set('gr_earn_point_per_dollar_lable', stripslashes($app_config['lang']['earn_point_per_dollar_lable']));
                WC()->session->set('gr_point_lable', $app_config['lang']['point_lable']);
                WC()->session->set('gr_points_lable', $app_config['lang']['points_lable']);
                WC()->session->set('gr_redeem_btn_text', $app_config['lang']['redeem_btn_text']);
                WC()->session->set('gr_redeemed_btn_text', $app_config['lang']['redeemed_btn_text']);
                WC()->session->set('gr_redeemed_status_msg', $app_config['lang']['redeemed_status_msg']);
                WC()->session->set('label_redeemed_points', $app_config['lang']['label_redeemed_points']);
                WC()->session->set('label_life_time_points', $app_config['lang']['label_life_time_points']);
                WC()->session->set('label_available_points', $app_config['lang']['label_available_points']);
                WC()->session->set('error_more_points_required', $app_config['lang']['error_more_points_required']);
                WC()->session->set('label_exclusion_points', $app_config['lang']['label_exclusion_points']);
                WC()->session->set('label_total_points', $app_config['lang']['label_total_points']);
                WC()->session->set('no_records_found', $app_config['lang']['no_records_found']);
                WC()->session->set('gr_loyalty_menu_name', stripslashes($app_config['lang']['loyalty_menu_name']));
                WC()->session->set('gr_loyalty_date_start', $app_config['loyalty']['date_start']);
                WC()->session->set('gr_loyalty_date_end', $app_config['loyalty']['date_end']);
                WC()->session->set('gr_loyalty_timezone', $app_config['loyalty']['timezone']);
                WC()->session->set('gr_loyalty_is_open', $app_config['loyalty']['is_open']);
                WC()->session->set('gr_global_review_enabled', $app_config['reviews']['global_review_enabled']);
                WC()->session->set('gr_global_review_points', $app_config['reviews']['global_review_points']);
                WC()->session->set('gr_global_review_lable_enabled', $app_config['reviews']['global_review_lable_enabled']);
                WC()->session->set('gr_global_review_lable', $app_config['reviews']['global_review_lable']);
                WC()->session->set('gr_restricted_user_roles', empty($app_config['points']['gr_restricted_user_roles'])?array():$app_config['points']['gr_restricted_user_roles'] );
                WC()->session->set('gr_roles_restrict_type', empty($app_config['points']['gr_roles_restrict_type'])?'restrict':$app_config['points']['gr_roles_restrict_type'] );
                WC()->session->set('gr_disable_non_loggedin', empty($app_config['points']['gr_disable_non_loggedin'])?0:$app_config['points']['gr_disable_non_loggedin'] );
                WC()->session->set('gr_reward_points_type', empty($app_config['points']['reward_points_type']) ? 'SUBTOTAL' : $app_config['points']['reward_points_type']);
                WC()->session->set('gr_redeem_restrict_status', isset($app_config['points']['gr_redeem_restrict_status']) ? $app_config['points']['gr_redeem_restrict_status'] : 0);
                WC()->session->set('gr_redeem_min_point', isset($app_config['points']['gr_redeem_min_point']) ? $app_config['points']['gr_redeem_min_point'] : 0);
                WC()->session->set('gr_redeem_max_point', isset($app_config['points']['gr_redeem_max_point']) ? $app_config['points']['gr_redeem_max_point'] : '');
                WC()->session->set('gr_redeem_cart_percent', isset($app_config['points']['gr_redeem_cart_percent']) ? $app_config['points']['gr_redeem_cart_percent'] : '');
                WC()->session->set('gr_label_color', isset($app_config['points']['gr_label_color']) ? $app_config['points']['gr_label_color'] : '#4bb543');
                WC()->session->set('gr_is_redeem_individual', empty($app_config['points']['gr_is_redeem_individual'])?0:$app_config['points']['gr_is_redeem_individual'] );
                WC()->session->set('gr_pay_exclude_products', empty($app_config['points']['gr_pay_exclude_products'])?array():$app_config['points']['gr_pay_exclude_products'] );
                WC()->session->set('gr_pay_exclude_categories', empty($app_config['points']['gr_pay_exclude_categories'])?array():$app_config['points']['gr_pay_exclude_categories'] );
                WC()->session->set('gr_pay_redeem_restrict_terms', empty($app_config['points']['gr_pay_redeem_restrict_terms'])?'':$app_config['points']['gr_pay_redeem_restrict_terms'] );
                WC()->session->set('gr_label_redeem_restriction_apply', empty($app_config['lang']['gr_label_redeem_restriction_apply'])?'':stripslashes($app_config['lang']['gr_label_redeem_restriction_apply']) );
                WC()->session->set('gr_btn_redeem_confirm', empty($app_config['lang']['gr_btn_redeem_confirm'])?'':stripslashes($app_config['lang']['gr_btn_redeem_confirm']) );
                WC()->session->set('gr_redeem_extra_point_info', empty($app_config['lang']['gr_redeem_extra_point_info'])?'':stripslashes($app_config['lang']['gr_redeem_extra_point_info']) );
                WC()->session->set('gr_pbp_auto_apply', empty($app_config['points']['gr_pbp_auto_apply'])?0:$app_config['points']['gr_pbp_auto_apply'] );
                WC()->session->set('gr_earn_exclude_products', empty($app_config['points']['gr_earn_exclude_products'])?'':$app_config['points']['gr_earn_exclude_products'] );
                WC()->session->set('gr_pbp_mode', empty($app_config['points']['gr_pbp_mode'])?'points':$app_config['points']['gr_pbp_mode'] );

                $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                if (!empty($redeem_coupon) && !empty($app_config['points']['gr_paybypoints_coupon']) &&
                    $redeem_coupon != $app_config['points']['gr_paybypoints_coupon'])
                {
                    WC()->session->set('gr_paybypoints_coupon_old', $redeem_coupon);
                }

                WC()->session->set('gr_paybypoints_coupon', empty($app_config['points']['gr_paybypoints_coupon']) ? self::REDEEM_COUPON : $app_config['points']['gr_paybypoints_coupon']);
                WC()->session->set('gr_widget_config_version', empty($app_config['widget_config_version'])? '' : $app_config['widget_config_version']);
                WC()->session->set('gr_sdk_version', empty($app_config['sdk_version'])? '' : $app_config['sdk_version']);
            }
            catch(Exception $e)
            {}
        }

        private function get_settings_api($pull_user_points = 0)
        {
            try
            {
                if (empty(WC()->session))
                    return;

                $session_created_time   = WC()->session->get('gr_api_sess', 0);
                $fetch_user_points      = WC()->session->get('gr_fetch_user_points', 0);
                $app_config             = gr_get_app_config();

                /*
                 * API request shall be sent to server,
                 * 1) If local config is empty
                 * 2) If local config date_updated is empty
                 * 3) If Cart / Checkout & session created time is crossed 20 seconds (to avoid frequent calls. cart send request 2 times)

                 */

                 if(
                    empty($app_config) || empty($app_config['date_updated'])  || (is_user_logged_in() && empty($fetch_user_points))
                    || ( $pull_user_points == 1 && $session_created_time + 10 < time() )
                )
                {
                    if(empty($pull_user_points) || empty($app_config['date_updated']))
                        $urlApi = self::$_callback_url . self::$_api_version . 'getRedeemSettings';
                    else
                        $urlApi = self::$_callback_url . self::$_api_version . 'getUserPointsSettings';

                    $shop_id = get_option('grconnect_shop_id');
                    $grAppId = get_option('grconnect_appid');
                    $grCampId = get_option('grconnect_secret');
                    $params['payload'] = get_option('grconnect_payload');

                    if (!empty($shop_id) && !empty($grAppId) && !empty($grCampId) && !empty($params['payload']))
                    {
                        $params['admin_email'] = get_option('grconnect_admin_email');
                        $params['id_site'] = $grAppId;
                        $params['id_campaign'] = $grCampId;
                        $params['app'] = 'WP';
                        $params['id_shop'] = $shop_id;
                        $params['status'] = 'Get';
                        $params['plugin_version'] = self::$_plugin_version;

                        if(is_user_logged_in())
                        {
                            $user = wp_get_current_user();
                            $params['user_email'] = $user->user_email;
                            $params['first_name'] = empty($user->user_firstname) ? '' : $user->user_firstname;
                            $params['last_name'] = empty($user->user_lastname) ? '' : $user->user_lastname;
                            // Flag to avoid repeated user points fetch API
                            WC()->session->set('gr_fetch_user_points', 1);
                        }

                        $response = wp_remote_post($urlApi, array('body' => $params, 'timeout' => 10));//timeout reduced from 180 to 10

                        if(is_array($response) && !empty($response['body']))
                            $ret = json_decode($response['body'], true);
                        else
                            $ret['error'] = 1;

                        if(isset($ret['error']) && $ret['error'] != 1)
                        {
                            if(empty($pull_user_points))
                            {
                                try
                                {
                                    //User points are not storing in the config file.
                                    $app_config_new =   array(
                                        'date_updated'  =>  time(),
                                        'points'        =>  array(
                                            'loyalty_campaign_enabled'  => $ret['loyalty_campaign_enabled'],
                                            'earn_point_enabled'        => $ret['earn_point_enabled'],
                                            'earn_point_per_dollar'     => $ret['earn_point_per_dollar'],
                                            'purchase_theme_status'     => $ret['purchase_theme_status'],
                                            'redeem_theme_status'       => $ret['redeem_theme_status'],
                                            'redeem_point_per_dollar'   => $ret['redeem_point_per_dollar'],
                                            'redeem_purchase_status'    => $ret['redeem_purchase_status'],
                                            'gr_roundoff_type'          => $ret['gr_roundoff_type'],
                                            'currency'                  => empty($ret['currency']) ? 'USD' : $ret['currency'],
                                            'minimum_order_value'       => (empty($ret['minimum_order_value']) || !is_numeric($ret['minimum_order_value'])) ? 0 : $ret['minimum_order_value'],
                                            'gr_restricted_user_roles'  => empty($ret['gr_restricted_user_roles'])?array():$ret['gr_restricted_user_roles'],
                                            'gr_roles_restrict_type'    => empty($ret['gr_roles_restrict_type'])?'restrict':$ret['gr_roles_restrict_type'],
                                            'reward_points_type'        => empty($ret['reward_points_type']) ? 'SUBTOTAL' : $ret['reward_points_type'],
                                            'gr_disable_non_loggedin'   => isset($ret['gr_disable_non_loggedin']) ? $ret['gr_disable_non_loggedin'] : 0,
                                            'gr_redeem_restrict_status' => isset($ret['gr_redeem_restrict_status']) ? $ret['gr_redeem_restrict_status'] : 0,
                                            'gr_redeem_min_point'       => isset($ret['gr_redeem_min_point']) ? $ret['gr_redeem_min_point'] : 0,
                                            'gr_redeem_max_point'       => isset($ret['gr_redeem_max_point']) ? $ret['gr_redeem_max_point'] : '',
                                            'gr_redeem_cart_percent'    => isset($ret['gr_redeem_cart_percent']) ? $ret['gr_redeem_cart_percent'] : '',
                                            'gr_label_color'            => isset($ret['gr_label_color']) ? $ret['gr_label_color'] : '#4bb543',
                                            'gr_pay_exclude_products'   => isset($ret['gr_pay_exclude_products']) ? $ret['gr_pay_exclude_products'] : array(),
                                            'gr_pay_exclude_categories' => isset($ret['gr_pay_exclude_categories']) ? $ret['gr_pay_exclude_categories'] : array(),
                                            'gr_is_redeem_individual'   => isset($ret['gr_is_redeem_individual']) ? $ret['gr_is_redeem_individual'] : 0,
                                            'gr_pay_redeem_restrict_terms' => isset($ret['gr_pay_redeem_restrict_terms']) ? $ret['gr_pay_redeem_restrict_terms'] : '',
                                            'gr_paybypoints_coupon'     => empty($ret['gr_paybypoints_coupon']) ? self::REDEEM_COUPON : $ret['gr_paybypoints_coupon'],
                                            'gr_pbp_auto_apply'         => empty($ret['gr_pbp_auto_apply']) ? 0 : $ret['gr_pbp_auto_apply'],
                                            'gr_earn_exclude_products'   => isset($ret['gr_earn_exclude_products']) ? $ret['gr_earn_exclude_products'] : '',
                                            'gr_pbp_mode'                => empty($ret['gr_pbp_mode']) ? 'points' : $ret['gr_pbp_mode'],
                                        ),
                                        'lang'          =>  array(
                                            'point_lable'   =>  $ret['point_lable'],
                                            'points_lable'  =>  $ret['points_lable'],
                                            'earn_point_per_dollar_lable'   =>  $ret['earn_point_per_dollar_lable'],
                                            'redeem_point_per_dollar_lable'   =>  $ret['redeem_point_per_dollar_lable'],
                                            'redeem_point_product_per_dollar_lable'   =>  $ret['redeem_point_product_per_dollar_lable'],
                                            'redeem_btn_text'   =>  $ret['redeem_btn_text'],
                                            'redeemed_btn_text'   =>  $ret['redeemed_btn_text'],
                                            'redeemed_status_msg'   =>  $ret['redeemed_status_msg'],
                                            'label_redeemed_points'   =>  $ret['label_redeemed_points'],
                                            'label_life_time_points'   =>  $ret['label_life_time_points'],
                                            'label_available_points'   =>  $ret['label_available_points'],
                                            'error_more_points_required'   =>  $ret['error_more_points_required'],
                                            'label_exclusion_points'   =>  $ret['label_exclusion_points'],
                                            'label_total_points'   =>  $ret['label_total_points'],
                                            'no_records_found'   =>  $ret['no_records_found'],
                                            'gr_label_redeem_restriction_apply' => $ret['gr_label_redeem_restriction_apply'],
                                            'gr_btn_redeem_confirm' => $ret['gr_btn_redeem_confirm'],
                                            'loyalty_menu_name' =>  empty($ret['loyalty_menu_name']) ? 'GR Loyalty' : stripslashes($ret['loyalty_menu_name'])
                                        ),
                                        'reviews' => array(
                                            'global_review_enabled'       => (!empty($ret['global_review_enabled'])) ? $ret['global_review_enabled'] : 0,
                                            'global_review_points'        => (!empty($ret['global_review_points'])) ? $ret['global_review_points'] : 0,
                                            'global_review_lable_enabled' => (!empty($ret['global_review_lable_enabled'])) ? $ret['global_review_lable_enabled'] : 0,
                                            'global_review_lable'         => (!empty($ret['global_review_lable'])) ? $ret['global_review_lable'] : ''
                                        ),
                                        'loyalty' => array(
                                            'date_start'        => !empty($ret['date_start']) ? $ret['date_start'] : 0,
                                            'date_end'          => !empty($ret['date_end']) ? $ret['date_end'] : 0,
                                            'timezone'          => !empty($ret['timezone']) ? $ret['timezone'] : 'America/Chicago',
                                            'is_open'           => !empty($ret['is_open']) ? $ret['is_open'] : 0
                                        )
                                    );

                                    gr_set_app_config($app_config_new);
                                    $app_config = $app_config_new;
                                }
                                catch(Exception $e)
                                {}
                            }

                            WC()->session->set('gr_user_points', $ret['gr_user_points']);
                            WC()->session->set('gr_api_sess', time());
                        }
                    }
                }

                $this->set_app_settings_session($app_config);

            } catch (Exception $ex) {

            }
        }

        public function gr_ajax_create_account()
        {
            self::callAcctRegister($_POST);
        }

        /*
         * Loyalty Upcoming / End campaign is active
         */
        protected static function _isActiveCampaign()
        {
            try
            {
                if (!empty(WC()->session)) {
                    $date_start = WC()->session->get('gr_loyalty_date_start', 0);
                    $date_end = WC()->session->get('gr_loyalty_date_end', 0);
                    $timezone = WC()->session->get('gr_loyalty_timezone', 'America/Chicago');
                    $is_open = WC()->session->get('gr_loyalty_is_open', 0);
                } else {
                    $app_config = gr_get_app_config();
                    $date_start = !empty($app_config['loyalty']['date_start']) ? $app_config['loyalty']['date_start'] : 0;
                    $date_end = !empty($app_config['loyalty']['date_end']) ? $app_config['loyalty']['date_end'] : 0;
                    $timezone = !empty($app_config['loyalty']['timezone']) ? $app_config['loyalty']['timezone'] : 'America/Chicago';
                    $is_open = !empty($app_config['loyalty']['is_open']) ? $app_config['loyalty']['is_open'] : 0;
                }

                if (!empty($date_start)) {
                    $currentDate = new \DateTime("now");
                    $currentDate->setTimezone(new DateTimeZone($timezone));

                    $startDate = DateTime::createFromFormat('U', $date_start);
                    $startDate->setTimezone(new DateTimeZone($timezone));

                    $endDate = 0;
                    if (empty($is_open)) {
                        $endDate = DateTime::createFromFormat('U', $date_end);
                        $endDate->setTimezone(new DateTimeZone($timezone));
                    }

                    if ($startDate > $currentDate || (!empty($endDate) && $currentDate > $endDate)) {
                        return false;
                    }
                }
            }
            catch(Exception $e)
            {}

            return true;
        }

        protected static function _getIPDetails()
        {
            try
            {
                $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                $ip_details = array('ip' => $ip, 'city' => '', 'region_name' => '', 'country_code' => 'US');

                if (empty($ip)) {
                    throw new Exception('Invalid IP');
                }

                $url = 'http://www.geoplugin.net/json.gp?ip=' . $ip;

                $httpObj = (new HttpRequestHandler)
                        ->setTimeout(5)
                        ->exec($url);
                $res = $httpObj->getResponse();

                if (!empty($res)) {
                    $ipLocArr = json_decode($res, true);

                    if (!empty($ipLocArr['geoplugin_request']) && $ipLocArr['geoplugin_request'] == $ip && in_array($ipLocArr['geoplugin_status'], array(200, 206))) {
                        $ip_details['ip'] = empty($ipLocArr['geoplugin_request']) ? $ip : $ipLocArr['geoplugin_request'];
                        $ip_details['city'] = empty($ipLocArr['geoplugin_city']) ? null : $ipLocArr['geoplugin_city'];
                        $ip_details['region_name'] = empty($ipLocArr['geoplugin_regionName']) ? null : $ipLocArr['geoplugin_regionName'];
                        $ip_details['country_code'] = empty($ipLocArr['geoplugin_countryCode']) ? 'US' : $ipLocArr['geoplugin_countryCode'];
                    }
                }
            }
            catch(Exception $e)
            {}

            return $ip_details;
        }

        private function callAcctRegister($p)
        {
            try
            {
                $p['grconnect_reg_email_user'] = sanitize_email( $p['grconnect_reg_email_user'] );
                if(empty($p['grconnect_reg_email_user']))
                {
                    $resArr = array('gr_reg' => 4, 'message' => 'Enter valid email address');
                    die(json_encode($resArr));
                }

                $ip_info = self::_getIPDetails();
                $params["action"] = 'createaccount';
                $params["firstname"] = sanitize_text_field($p['grconnect_reg_firstname']);
                $params["lastname"] = sanitize_text_field($p['grconnect_reg_lastname']);
                $params["email"] = $p['grconnect_reg_email_user'];
                $params["email_user"] = $p['grconnect_reg_email_user'];
                $params["raffd"] = !empty($p['raffd']) ? $p['raffd'] : '';
                $params["companyname"] = get_bloginfo('name');
                $params["companyname"] = !empty($params["companyname"]) ? get_bloginfo('name') : 'Your Business name';
                $params["address1"] = '***'; //Dummy
                $params["city"] = empty($ip_info['city']) ? '***' : $ip_info['city'];
                $params["state"] = empty($ip_info['region_name']) ? '***' : $ip_info['region_name'];
                $params["postcode"] = '1'; //Dummy;
                $params["country"] = empty($ip_info["country_code"]) ? 'US' : $ip_info["country_code"];
                $params["currency"] = ($params["country"] === 'AU') ? 3 : 1;
                $params["currency_code"] = get_option('woocommerce_currency', 'USD');
                $params["phonenumber"] = '1234567890'; //Dummy
                $params["notes"] = 'Wordpress';
                $params["app"] = 'gr';
                $params['url'] = get_option('siteurl');
                $params["name"] = $params["companyname"];
                $params['type'] = 'url';
                $params['plugin_type'] = 'WP';
                $params['shop_url'] = get_option('siteurl');
                $params['shop_name'] = $params["companyname"];
                $params['campaign_name'] = 'REWARDS';
                $params['timezone'] = 'America/Chicago'; //Dummy $p['grappsmav_reg_timezone'];
                $params['date_format'] = 'd/m/Y'; //Dummy$p['grappsmav_reg_date_format'];
                $params['exclusion_period'] = 0; //$p['grconnect_reg_exclusion_period'];
                $params["app_lang"] = str_replace('-', '_', get_bloginfo('language'));
                $myaccount_page_id = get_option('woocommerce_myaccount_page_id');
                $myaccount_page_url = get_permalink($myaccount_page_id);
                $params['login_url'] = $myaccount_page_url;
                $params['plugin_version'] = self::$_plugin_version;

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($params)
                                ->exec(self::$_api_url);
                $res = $httpObj->getResponse();

                if(!empty($res))
                    $resArr = json_decode($res, true);

                if(isset($resArr['error']) && $resArr['error'] == 0)
                {
                    update_option('grconnect_admin_email', $params["email"]);
                    update_option('grconnect_shop_id', $resArr['id_shop']);
                    update_option('grconnect_appid', $resArr['id_site']);
                    update_option('grconnect_secret', $resArr['secret']);
                    update_option('grconnect_payload', $resArr['pay_load']);
                    update_option('grconnect_register', 1);
                    $resArr['frame_url'] = self::$_callback_url . 'autologin?id_shop=' . $resArr['id_shop'] . '&admin_email=' . urlencode($params["email"]) . '&payload=' . $resArr['pay_load'] . '&cur=' . get_option('woocommerce_currency', 'USD');
                    $resArr['gr_reg'] = 0;
                }
                else if(isset($resArr['error']) && $resArr['error'] == 1)
                {
                    $resArr['gr_reg'] = 1;
                }
                else if(isset($resArr['error']) && $resArr['error'] == 2)
                {
                    update_option('grconnect_register', 3);
                    $resArr['gr_reg'] = 2;
                }
                else
                {
                    $resArr['gr_reg'] = 4;
                }

                die(json_encode($resArr));
            } catch (Exception $ex) {

            }
        }

        private function callGrConnectApi($param, $urlApi)
        {
            $msg = '';
            try
            {
                $shop_id = get_option('grconnect_shop_id', 0);
                if($shop_id == 0)
                    throw new Exception('Invalid Request');

                $grAppIdArr = get_option('grconnect_appid');
                $grAppId = !empty($grAppIdArr) ? $grAppIdArr : '';
                $grCampIdArr = get_option('grconnect_secret');
                $grCampId = !empty($grCampIdArr) ? $grCampIdArr : '';
                $paramSalt = array();
                $paramSalt['id_site'] = $params['id_site'] = $grAppId;
                $paramSalt['points'] = $params['points'] = 0;
                $paramSalt['id_campaign'] = $params['id_campaign'] = $grCampId;
                $paramSalt['email'] = $params['email'] = $param['email'];

                $params['app'] = 'WP';
                $params['name'] = isset($param['name']) ? $param['name'] : '';
                $params['first_name'] = empty($param['first_name']) ? '' : $param['first_name'];
                $params['last_name'] = empty($param['last_name']) ? '' : $param['last_name'];
				$params['comment'] = isset($param['comment']) ? $param['comment'] : '';
                $params["app_lang"] = str_replace('-', '_', get_bloginfo('language'));
                $allparam = implode('#WP#', $paramSalt);
                $params['salt'] = md5($allparam);
                $params['id_shop'] = $shop_id;
                $params['coupon'] = isset($param['coupon']) ? $param['coupon'] : '';
                $params['id_order'] = isset($param['id_order']) ? $param['id_order'] : 0;
                $params['amount'] = isset($param['total']) ? $param['total'] : 0;
                $params['subtotal'] = isset($param['subtotal']) ? $param['subtotal'] : 0;
                $params['total'] = isset($param['total']) ? $param['total'] : 0;
                $params['line_items'] = empty($param['line_items']) ? [] : $param['line_items'];
                $params['shipping'] = isset($param['shipping']) ? $param['shipping'] : 0;
                $params['shipping_tax'] = isset($param['shipping_tax']) ? $param['shipping_tax'] : 0;
                $params['tax'] = isset($param['tax']) ? $param['tax'] : 0;
                $params['discount'] = isset($param['discount']) ? $param['discount'] : 0;
                $params['roles']   = isset($param['roles']) ? $param['roles'] : array();
				$params['ratio']   = isset($param['ratio']) ? $param['ratio'] : 1;
                $params['customer_id'] = !empty($param['customer_id']) ? $param['customer_id'] : 0;
                $params['refunded'] = !empty($param['refunded']) ? $param['refunded'] : 0;
                $params['refund_data'] = !empty($param['refund_data']) ? $param['refund_data'] : array();
                $params['order_data'] = !empty($param['order_data']) ? $param['order_data'] : array();
                $params['plugin_version'] = self::$_plugin_version;

                $params['currency'] = get_option('woocommerce_currency', 'USD');
                $params['status'] = isset($param['status']) ? $param['status'] : '';
                $params['order_status'] = !empty($param['order_status']) ? $param['order_status'] : '';
                $params['redeem_points'] = !empty($param['redeem_points']) ? $param['redeem_points'] : 0;
                $params['redeem_charges'] = !empty($param['redeem_charges']) ? $param['redeem_charges'] : 0;
                $params['payload'] = get_option('grconnect_payload', 0);
                $params['created_date'] = !empty($param['created_date']) ? $param['created_date'] : '';
                $params['user_ip'] = !empty($param['user_ip']) ? $param['user_ip'] : '';
                $params['number'] = !empty($param['number']) ? $param['number'] : '';
                $params['postcode'] = !empty($param['postcode']) ? $param['postcode'] : '';
                $params['country'] = !empty($param['country']) ? $param['country'] : '';

                if($grAppId != '' && $grCampId != '')
                {
                    $httpObj = (new HttpRequestHandler)
                                    ->setPostData($params)
                                    ->exec($urlApi);
                    $res = $httpObj->getResponse();

                    if(!empty($res))
                        $res = json_decode($res, true);

                    if(!empty($res['error']))
                        $msg = 'Unexpected error occur. Please check with administrator.';
                }
                else
                {
                    $msg = 'Gr app id or secret is missing';
                }
            } catch (Exception $ex) {
                $msg = 'Error : ' . $ex->getMessage();
            }

            return $msg;
        }


        private function callGrApiReview($params, $urlApi)
        {
            $msg = '';
            try
            {
                // Validate the shop details
                $grShopId  = get_option('grconnect_shop_id');
                $grAppId   = get_option('grconnect_appid');
                $grCampId  = get_option('grconnect_secret');
                $grPayload = get_option('grconnect_payload');
                if (empty($grShopId) || empty($grAppId) || empty($grCampId) || empty($grPayload))
                    throw new Exception('Invalid Request');

                // Create salt for Security validation
                $paramSalt = array();
                $paramSalt['id_site'] = $params['id_site'] = $grAppId;
                $paramSalt['points'] = $params['points'] = 0;
                $paramSalt['id_campaign'] = $params['id_campaign'] = $grCampId;
                $paramSalt['email'] = $params['comment_author_email'];
                $allparam = implode('#WP#', $paramSalt);
                $params['salt'] = md5($allparam);

                // Data for product review
                $params['app'] = 'WP';
                $params["app_lang"] = str_replace('-', '_', get_bloginfo('language'));
                $params['id_shop'] = $grShopId;
                $params['currency'] = get_option('woocommerce_currency', 'USD');
                $params['payload'] = get_option('grconnect_payload', 0);

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($params)
                                ->exec($urlApi);
                $res = $httpObj->getResponse();

                if(!empty($res))
                    $res = json_decode($res, true);

                if(!empty($res['error']))
                    $msg = 'Unexpected error occur. Please check with administrator.';

            } catch (Exception $ex) {
                $msg = 'Error : ' . $ex->getMessage();
            }
            return $msg;
        }

        public function apmgr_create_discount()
        {
            try
            {
                global $wp_rest_server;
                global $wpdb;

                if(is_admin())
                    return;

                $useragent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
                if(!strpos($useragent, 'Appsmav'))
                    return;

                if( !has_action( 'rest_api_init' ) ){
                    $wp_rest_server = new WP_REST_Server();
                    do_action('rest_api_init', $wp_rest_server);
                }

                add_filter('wpss_misc_form_spam_check_bypass', FALSE, 10);

                //user email verification
                if(!empty($_POST['verify_user']))
                {
                    $email = sanitize_email( $_POST['verify_user'] );
                    $user = get_user_by('email', $email);
                    $resp['error'] = 1;
                    $resp['msg'] = 'No User Exist';

                    if(!empty($user))
                    {
                        $resp['error'] = 0;
                        $resp['msg'] = 'User Exist';
                        $resp['name'] = $user->first_name . ' ' . $user->last_name;
                        $resp['first_name'] = $user->first_name;
                        $resp['last_name']  = $user->last_name;
                        $resp['id'] = $user->ID;
                        $resp['customer_group'] = $user->roles;
                    }

                    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                    header("Content-Type: application/json; charset=UTF-8");
                    die(json_encode($resp));
                }

                // Verify Product review is enabled or not
                if(!empty($_POST['verify_review_enabled']))
                    die( get_option('woocommerce_enable_reviews', 'no') );

                if(empty($_POST['cpn_type']) || empty($_POST['grcpn_code']))
                    return;

                if(!isset($_POST['cpn_value']) || !isset($_POST['free_ship']) || !isset($_POST['min_order']) || !isset($_POST['cpn_descp']))
                    throw new Exception('InvalidRequest2');

                if(!class_exists('WC_Integration'))
                    throw new Exception('WooPluginNotFound');

                if(!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
                    throw new Exception('PluginDeactivated');

                // Validate coupon types
                if(!in_array(wc_clean($_POST['cpn_type']), array_keys(wc_get_coupon_types())))
                    throw new WC_CLI_Exception('woocommerce_cli_invalid_coupon_type', sprintf(__('Invalid coupon type - the coupon type must be any of these: %s', 'woocommerce'), implode(', ', array_keys(wc_get_coupon_types()))));

                $assoc_args = array(
                    'code' => sanitize_text_field($_POST['grcpn_code']),
                    'type' => sanitize_text_field($_POST['cpn_type']),
                    'amount' => empty($_POST['cpn_value']) ? 0 : sanitize_text_field($_POST['cpn_value']),
                    'individual_use' => true,
                    'usage_limit' => 1,
                    'usage_limit_per_user' => 1,
                    'enable_free_shipping' => sanitize_text_field($_POST['free_ship']),
                    'minimum_amount' => sanitize_text_field($_POST['min_order']),
                    'product_ids' => !empty($_POST['product_ids']) ? sanitize_text_field($_POST['product_ids']) : '',
                    'exclude_product_ids' => !empty($_POST['exclude_product_ids']) ? sanitize_text_field($_POST['exclude_product_ids']) : '',
                    'product_category_ids' => !empty($_POST['product_category_ids']) ? sanitize_text_field($_POST['product_category_ids']) : '',
                    'exclude_product_category_ids' => !empty($_POST['exclude_product_category_ids']) ? sanitize_text_field($_POST['exclude_product_category_ids']) : '',
                    'maximum_amount' => !empty($_POST['maximum_amount']) ? sanitize_text_field($_POST['maximum_amount']) : '',
                    'exclude_sale_items' => !empty($_POST['exclude_sale_items']) ? sanitize_text_field($_POST['exclude_sale_items']) : '',
                    'customer_emails' => !empty($_POST['email_restrictions']) ? sanitize_text_field($_POST['email_restrictions']) : '',
                    'description' => sanitize_text_field($_POST['cpn_descp']),
                    'expiry_date' => empty($_POST['expiry_date']) ? '' : sanitize_text_field($_POST['expiry_date'])
                );

                $assoc_args['product_ids'] = !empty($assoc_args['product_ids']) ? json_decode($assoc_args['product_ids'], true) : [];
                $assoc_args['exclude_product_ids'] = !empty($assoc_args['exclude_product_ids']) ? json_decode($assoc_args['exclude_product_ids'], true) : [];
                $assoc_args['product_category_ids'] = !empty($assoc_args['product_category_ids']) ? json_decode($assoc_args['product_category_ids'], true) : [];
                $assoc_args['exclude_product_category_ids'] = !empty($assoc_args['exclude_product_category_ids']) ? json_decode($assoc_args['exclude_product_category_ids'], true) : [];
                $assoc_args['customer_emails'] = !empty($assoc_args['customer_emails']) ? json_decode(stripslashes($assoc_args['customer_emails']), true) : [];

                if(!empty($_POST['usage_limit_per_user']))
                    $assoc_args['usage_limit'] = '';

                if(get_option('woocommerce_enable_coupons') !== 'yes')
                    update_option('woocommerce_enable_coupons', 'yes');

                $coupon_code = apply_filters('woocommerce_coupon_code', $assoc_args['code']);

                // Check for duplicate coupon codes.
                $coupon_found = $wpdb->get_var($wpdb->prepare("
                        SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                        WHERE $wpdb->posts.post_type = 'shop_coupon'
                        AND $wpdb->posts.post_status = 'publish'
                        AND $wpdb->posts.post_title = '%s'
                 ", $coupon_code));

                if($coupon_found)
                    throw new Exception('DuplicateCoupon');

                $url = self::$_callback_url . self::$_api_version . 'wooCpnValidate';

                $app_id = get_option('grconnect_appid');
                $payload = get_option('grconnect_payload', 0);

                if(empty($app_id) || empty($payload))
                    throw new Exception('IntegrationMissing');

                $param = array(
                    'id_coupon' => sanitize_text_field( $_POST['id_coupon']),
                    'grcpn_code' => sanitize_text_field( $_POST['grcpn_code']),
                    'hash' => sanitize_text_field( $_POST['hash']),
                    'amount' => sanitize_text_field( $_POST['cpn_value']),
                    'type' => sanitize_text_field( $_POST['cpn_type']),
                    'minimum_amount' => sanitize_text_field( $_POST['min_order']),
                    'id_site' => $app_id,
                    'payload' => $payload,
                    'plugin_version' => self::$_plugin_version
                );

                $httpObj = (new HttpRequestHandler)
                                ->setPostData($param)
                                ->exec($url);
                $res = $httpObj->getResponse();

                if(!empty($res))
                    $res = json_decode($res, true);

                if(empty($res) || !empty($res['error']))
                    throw new Exception('VerificationFailed');

                $defaults = array(
                    'type' => 'fixed_cart',
                    'amount' => 0,
                    'individual_use' => false,
                    'product_ids' => array(),
                    'exclude_product_ids' => array(),
                    'usage_limit' => '',
                    'usage_limit_per_user' => '',
                    'limit_usage_to_x_items' => '',
                    'usage_count' => '',
                    'expiry_date' => '',
                    'enable_free_shipping' => false,
                    'product_category_ids' => array(),
                    'exclude_product_category_ids' => array(),
                    'exclude_sale_items' => false,
                    'minimum_amount' => '',
                    'maximum_amount' => '',
                    'customer_emails' => array(),
                    'description' => ''
                );

                $coupon_data = wp_parse_args($assoc_args, $defaults);

                $new_coupon = array(
                    'post_title' => $coupon_code,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => get_current_user_id(),
                    'post_type' => 'shop_coupon',
                    'post_excerpt' => $coupon_data['description']
                );

                $id = wp_insert_post($new_coupon, $wp_error = false);

                if(is_wp_error($id))
                    throw new WC_CLI_Exception('woocommerce_cli_cannot_create_coupon', $id->get_error_message());

                // Set coupon meta
                update_post_meta($id, 'discount_type', $coupon_data['type']);
                update_post_meta($id, 'coupon_amount', wc_format_decimal($coupon_data['amount']));
                update_post_meta($id, 'individual_use', (!empty($coupon_data['individual_use']) ) ? 'yes' : 'no' );
                update_post_meta($id, 'product_ids', implode(',', array_filter(array_map('intval', $coupon_data['product_ids']))));
                update_post_meta($id, 'exclude_product_ids', implode(',', array_filter(array_map('intval', $coupon_data['exclude_product_ids']))));
                update_post_meta($id, 'usage_limit', absint($coupon_data['usage_limit']));
                update_post_meta($id, 'usage_limit_per_user', absint($coupon_data['usage_limit_per_user']));
                update_post_meta($id, 'limit_usage_to_x_items', absint($coupon_data['limit_usage_to_x_items']));
                update_post_meta($id, 'usage_count', absint($coupon_data['usage_count']));

                if('' !== wc_clean($coupon_data['expiry_date']))
                    $coupon_data['expiry_date'] = date('Y-m-d', strtotime($coupon_data['expiry_date']));

                update_post_meta($id, 'expiry_date', wc_clean($coupon_data['expiry_date']));
                update_post_meta($id, 'free_shipping', (!empty($coupon_data['enable_free_shipping']) ) ? 'yes' : 'no' );
                update_post_meta($id, 'product_categories', array_filter(array_map('intval', $coupon_data['product_category_ids'])));
                update_post_meta($id, 'exclude_product_categories', array_filter(array_map('intval', $coupon_data['exclude_product_category_ids'])));
                update_post_meta($id, 'exclude_sale_items', (!empty($coupon_data['exclude_sale_items']) ) ? 'yes' : 'no' );
                update_post_meta($id, 'minimum_amount', wc_format_decimal($coupon_data['minimum_amount']));
                update_post_meta($id, 'maximum_amount', wc_format_decimal($coupon_data['maximum_amount']));
                update_post_meta($id, 'customer_email', array_filter(array_map('sanitize_email', $coupon_data['customer_emails'])));

                if (!empty($_POST['custom_attributes']))
                {
                    $custom_attributes = stripslashes(sanitize_text_field($_POST['custom_attributes']));
                    $custom_attributes = json_decode($custom_attributes, true);
                    if (!empty($custom_attributes) && is_array($custom_attributes))
                    {
                        foreach ($custom_attributes as $prop_name => $prop_value) {
                            update_post_meta($id, $prop_name, wc_clean($prop_value));
                        }
                    }
                }

                $resp['error'] = 0;
                $resp['code'] = $coupon_code;
                $resp['id'] = $id;
                $resp['msg'] = 'Success';
            }
            catch(Exception $ex)
            {
                $resp['error'] = 1;
                $resp['msg'] = $ex->getMessage();
            }

            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Content-Type: application/json; charset=UTF-8");
            die(json_encode($resp));
        }

        public function init_page_load()
        {
            try {

                if(is_admin() || empty(WC()->session))
                      throw new Exception();

                if( ! empty($_REQUEST['grc']))
                {
                    $grc = empty($_REQUEST['grc']) ? null : sanitize_text_field($_REQUEST['grc']);
                    $gre = empty($_REQUEST['gre']) ? null : sanitize_text_field($_REQUEST['gre']);
                    $typ = empty($_REQUEST['type']) ? 'gr' : sanitize_text_field($_REQUEST['type']);
                    $scopeid = empty($_REQUEST['scopeid']) ? null : sanitize_text_field($_REQUEST['scopeid']);
                    $mavtoken = empty($_REQUEST['mavtoken']) ? null : sanitize_text_field($_REQUEST['mavtoken']);

                    WC()->session->set('gr_campaign_id', $grc);
                    WC()->session->set('gr_entry_id', $gre);
                    WC()->session->set('gr_app_type', $typ);
                    WC()->session->set('gr_app_scopeid', $scopeid);
                    WC()->session->set('gr_app_mavtoken', $mavtoken);
                } else if(!empty($_REQUEST['gr_coupon_code'])) {
                    $coupon_code = sanitize_text_field($_REQUEST['gr_coupon_code']);
                    if(!empty(WC()->cart) && !empty($coupon_code)) {
                        WC()->cart->add_discount($coupon_code);
                    }
                }
            } catch (Exception $ex) {

            }
        }

        private function is_restricted_user_role($roles)
        {
            try
            {
                $is_restricted = false;
                $app_config = gr_get_app_config();
                $restrict_type = empty($app_config['points']['gr_roles_restrict_type']) ? 'restrict' : $app_config['points']['gr_roles_restrict_type'];
                if (!empty($roles) && !empty($app_config['points']['gr_restricted_user_roles']))
                {
                    $blocked_roles = array_intersect ($roles, $app_config['points']['gr_restricted_user_roles']);
                    if ($restrict_type == 'restrict' && !empty($blocked_roles) && count($blocked_roles) > 0)
                        $is_restricted = true;
                    else if ($restrict_type == 'allow' && empty($blocked_roles) && count($blocked_roles) == 0)
                        $is_restricted = true;
                }

            } catch (Exception $ex) { }

            return $is_restricted;
        }

        // Check this product is restricted to redeem points by product or category
        private function is_redeem_restricted_products($id_product)
        {
            try
            {
                $is_restricted = false;
                if (empty($id_product))
                    throw new Exception('Invalid product');

                $product = new WC_Product($id_product);
                if (empty($product))
                    throw new Exception('Invalid product');

                $restrict_status = WC()->session->get('gr_redeem_restrict_status', 0);
                if (!empty($restrict_status) && $restrict_status == 1)
                {
                    $app_config = gr_get_app_config();
                    $ex_products = empty($app_config['points']['gr_pay_exclude_products']) ? array() : explode(',', $app_config['points']['gr_pay_exclude_products']);
                    $ex_category = empty($app_config['points']['gr_pay_exclude_categories']) ? array() : explode(',', $app_config['points']['gr_pay_exclude_categories']);

                    // Check this product is restricted
                    if (!empty($ex_products) && in_array($id_product, $ex_products)) {
                        throw new Exception('Restricted by product');
                    }


                    // Check category is restricted
                    if (!empty($ex_category))
                    {
                        $list_category = get_the_terms($id_product, 'product_cat');
                        foreach ( $list_category as $category ) {
                            if (in_array($category->term_id, $ex_category)) {
                                throw new Exception('Restricted by category');
                            }
                        }
                    }
                }

            } catch (Exception $ex) {
                $is_restricted = true;
            }

            return $is_restricted;
        }

        // Function to send Product reviews to appsmav
        private function send_review_to_appsmav($comment_ID, $comment)
        {
            try
            {
                if (empty($comment->comment_author_email) || self::_isActiveCampaign() === false)
                    return;

                $app_config = gr_get_app_config();
                if(empty($app_config['points']['loyalty_campaign_enabled']))
                    return;

                // Check the user role is allowed to proceed

                $user = get_user_by('email', $comment->comment_author_email);
                if (!empty($user))
                {
                    $is_blocked_role = self::is_restricted_user_role($user->roles);
                    if($is_blocked_role)
                        return;
                }

                $comment_status = wp_get_comment_status( $comment_ID );
                $commentDetails = get_comment_meta( $comment_ID);
                $product_key = get_post_field( 'post_name' , $comment->comment_post_ID);
                $product_url = get_post_field( 'guid' , $comment->comment_post_ID);

                $review_details['comment_ID'] = !empty($comment->comment_ID) ? $comment->comment_ID : 0;
                $review_details['comment_post_ID'] = !empty($comment->comment_post_ID) ? $comment->comment_post_ID : 0;
                $review_details['comment_author_email'] = !empty($comment->comment_author_email) ? $comment->comment_author_email : '';
                $review_details['comment_date'] = !empty($comment->comment_date) ? $comment->comment_date : '';

                $review_details['comment_content'] = !empty($comment->comment_content) ? $comment->comment_content : '';
                $review_details['comment_approved'] = !empty($comment->comment_approved) ? $comment->comment_approved : 0;
                $review_details['comment_status'] = $comment_status;
                $review_details['rating'] = !empty($commentDetails['rating'][0]) ? $commentDetails['rating'][0] : '';
                $review_details['product_key'] = !empty($product_key) ? $product_key : '';
                $review_details['product_url'] = !empty($product_url) ? $product_url : '';

                $urlApi = self::$_callback_url . self::$_api_version . 'addReviewEntry';
                $this->callGrApiReview($review_details, $urlApi);
            } catch (Exception $ex) {

            }
        }

        public function gr_send_comment_status_change( $comment_ID ) {

            try
            {
                $comment = get_comment( $comment_ID );

                if ( !empty($comment->comment_type) && $comment->comment_type == 'review')
                {
                    self::send_review_to_appsmav($comment_ID, $comment);
                }
            } catch (Exception $ex) {

            }
        }

        /**
         * hook into WP's woocommerce payment made action hook
         */
        public function send_comment_to_appsmav($comment_ID)
        {
             try {

                // Product Review
                $comment = get_comment( $comment_ID );
                if ( !empty($comment->comment_type) && $comment->comment_type == 'review')
                {
                    self::send_review_to_appsmav($comment_ID, $comment);
                }
                else
                {
                    if(is_admin() || empty(WC()->session))
                          throw new Exception();

                    $grc =  WC()->session->get('gr_campaign_id');
                    $typ     = WC()->session->get('gr_app_type');

                    if( ! empty($grc) && $typ == 'gr')
                    {
                        $gre     = WC()->session->get('gr_entry_id');
                        $scopeid = WC()->session->get('gr_app_scopeid');
                        $mavtoken = WC()->session->get('gr_app_mavtoken', '');

                        WC()->session->set('gr_campaign_id', '');
                        WC()->session->set('gr_entry_id', '');
                        WC()->session->set('gr_app_type', '');
                        WC()->session->set('gr_app_scopeid', '');
                        WC()->session->set('gr_app_mavtoken', '');

                        $params = '?grc=' . $grc . '&gre=' . $gre . '&scopeid=' . $scopeid . '&cid=' . $comment_ID . '&mavtoken=' . $mavtoken;
                        wp_redirect(self::$_callback_url . 'promo/' . $grc . '/' . $params);
                        exit();
                    }
                }
            } catch (Exception $ex) {

            }
        }

        // Get logged in user's Referral code from GR app and set in session variable
        private function get_referral_coupon($email = '')
        {
            try
            {
                if (is_user_logged_in() || !empty($email))
                {
                    $urlApi = self::$_callback_url . self::$_api_version . 'getReferralCoupon';

                    $params['id_site'] 	   = get_option('grconnect_appid');
                    $params['id_campaign'] = get_option('grconnect_secret');
                    $params['app'] 		   = 'WP';
                    $params['id_shop']     = get_option('grconnect_shop_id');
                    $params['payload']     = get_option('grconnect_payload');
                    $params['plugin_version'] = self::$_plugin_version;

                    if (!empty($email)) {
                        $params['user_email'] = $email;
                    } else {
                        $user = wp_get_current_user();
                        $params['user_email'] = $user->user_email;
                    }

                    $response = wp_remote_post($urlApi, array('body' => $params, 'timeout' => 10));

                    if (is_array($response) && !empty($response['body']))
                    {
                        $ret = json_decode($response['body'], true);
                        if (isset($ret['error']) && $ret['error'] == 0 && !empty(WC()->session) && !empty($ret['gr_loyalty_referral_coupon']))
                            WC()->session->set('gr_loyalty_referral_coupon', $ret['gr_loyalty_referral_coupon']);
                    }
                }
            }
            catch(Exception $e)
            { }
        }

        // Get coupon's description
        private function get_coupon_description( $coupon_code )
        {
            try
            {
                $description = "";
                $coupon = new WC_Coupon($coupon_code);
                if ( is_callable( array( $coupon, 'get_description' ) ) ) { // WC 3.0+ compatibility
                    $description = $coupon->get_description();
                } else {
                    $coupon_post = get_post( $coupon->id );
                    $description = ! empty( $coupon_post->post_excerpt ) ? $coupon_post->post_excerpt : null;
                }
            }
            catch(Exception $ex)
            { }

            return $description;
        }

        private function gr_get_currency_ratio()
        {
            try
            {
                $ratio = 1;

                if (empty(WC()->cart))
                    return $ratio;

                //Multicurrency support
                $base_currency = get_option('woocommerce_currency', 'USD');
                $current_currency = get_woocommerce_currency();

                if($base_currency != $current_currency)
                {
                    $items = WC()->cart->get_cart();

                    foreach($items as $item)
                    {
                        $id_product = $item['data']->get_id();

                        $_product = wc_get_product($id_product);
                        $current_price = $_product->get_price();

                        $prices = get_post_meta($id_product, '_price');
                        $base_price = current($prices);

                        if(!empty($base_price) && !empty($current_price))
                        {
                            $ratio = $current_price / $base_price;
                            break;
                        }
                    }
                }

            } catch (Exception $ex) {
            }

            return $ratio;
        }

        // This function will return the currency ratio for the given order
        private function gr_get_currency_ratio_from_order($order)
        {
            $ratio = 1;
            try {

                $prodArr = $order->get_items();
                if (empty($prodArr))
                    return $ratio;

                foreach($prodArr as $prod)
                {
                    // Product amount from order data - this will come with order currency
                    $subtotal = $prod['subtotal'] / $prod['quantity'];

                    // Product amount from meta data - actual product price in base currency
                    if(!empty($prod['variation_id']))
                    {
                        $product = new WC_Product_Variation($prod['variation_id']);
                        $p_price = get_post_meta($prod['variation_id'], '_price', true);
                    }
                    else
                    {
                        $product = new WC_Product($prod['product_id']);
                        $p_price = get_post_meta($prod['product_id'], '_price', true);
                    }

                    if (wc_prices_include_tax())
                        $p_price = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $p_price));

                    $ratio = $subtotal / $p_price;

                    break;
                }
            }
            catch(Exception $e)
            { }

            return $ratio;
        }

        public function validate_apply_coupon( $true )
        {
            try
            {
                if ( empty($_POST['coupon_code']) )
                    return true;

                // Get the applied coupon code
                $coupon_code = sanitize_text_field( $_POST['coupon_code'] );

                // Get coupon description to validation GR created
                $description = $this->get_coupon_description( $coupon_code );
                if (!empty($description) && strpos($description, 'Gratisfaction Referral Invite') !== FALSE)
                {
                    if (!empty(WC()->session) && empty(WC()->session->get('gr_loyalty_referral_coupon', ''))) {
                        // Get the User's GR referral code
                        $email  = '';
                        if (is_user_logged_in()) {
                            $user = wp_get_current_user();
                            $email = $user->user_email;
                        }
                        if (!empty($_POST['billing_email']) && (!is_user_logged_in() || empty($email))) {
                            $email = sanitize_email($_POST['billing_email']);
                        }
                        if (!empty($email)) {
                            $this->get_referral_coupon($email);
                        }
                    }

                    // Check the user applied their own referral code
                    if (!empty(WC()->session) && strtolower(WC()->session->get('gr_loyalty_referral_coupon', '')) == strtolower($coupon_code))
                        return false;

                    // Check customer already placed orders
                    $is_order_exists = $this->_is_order_exists();
                    if ($is_order_exists === TRUE) {
                        return false;
                    }
                }
            }
            catch(Exception $ex)
            { }

            return $true;
        }

        // Validation the applied coupon is valid referral code
        public function validate_applied_coupon_checkout()
        {
            try {

                global $woocommerce;
                $applied_coupons = $woocommerce->cart->applied_coupons;
                if (empty($applied_coupons) || !is_array($applied_coupons))
                    return;

                foreach ($applied_coupons as $coupon_code) {
                    // Get coupon description to validation GR created
                    $description = $this->get_coupon_description($coupon_code);
                    if (!empty($description) && strpos($description, 'Gratisfaction Referral Invite') !== FALSE) {
                        if (!empty(WC()->session) && empty(WC()->session->get('gr_loyalty_referral_coupon', ''))) {
                            // Get the User's GR referral code
                            $email = '';
                            if (is_user_logged_in()) {
                                $user = wp_get_current_user();
                                $email = $user->user_email;
                            }
                            if (!empty($_POST['billing_email']) && (!is_user_logged_in() || empty($email))) {
                                $email = sanitize_email($_POST['billing_email']);
                            }
                            if (!empty($email)) {
                                $this->get_referral_coupon($email);
                            }
                        }

                        // Check the user applied their own referral code
                        if (!empty(WC()->session) && strtolower(WC()->session->get('gr_loyalty_referral_coupon', '')) == strtolower($coupon_code)) {
                            wc_add_notice(__("Coupon is not valid."), 'error');
                            return false;
                        }

                        // Check customer already placed orders
						$is_order_exists = $this->_is_order_exists();
						if ($is_order_exists === TRUE) {
                            wc_add_notice(__("Coupon is not valid."), 'error');
                            return false;
                        }
                    }
                }

                // Validate the points availabe if "pay by points" applied
                if(!empty(WC()->session) && WC()->session->get('gr_user_applied_discount') > 0)
                {

                    if(is_user_logged_in())
                        $this->get_settings_api(1);


                    if (WC()->session->get('gr_user_points', 0) < WC()->session->get('gr_user_deduct_points', 0))
                    {
                        $redeem_coupon = WC()->session->get('gr_paybypoints_coupon', self::REDEEM_COUPON);
                        WC()->cart->remove_coupon($redeem_coupon);//remove_discount
                        WC()->session->set('gr_user_max_discount', 0);
                        WC()->session->set('gr_user_deduct_points', 0);

                        $msg_error = !empty(WC()->session->get('error_more_points_required')) ? WC()->session->get('error_more_points_required') : 'Oops! More points required';
                        wc_add_notice( __( $msg_error ), 'error' );
                    }

                }

            }
            catch(Exception $ex)
            { }
        }

		// Get current user's order details, Both Guests and 
        private function _is_order_exists()
        {
			$is_order_exists = FALSE;
            try {
                $id_user         = get_current_user_id();
				// Get order of logged in user
				if (!empty($id_user)) {
                    // Get logged in user's order list
                    $customer_orders = get_posts( array(
                        'meta_key'    => '_customer_user',
                        'meta_value'  => $id_user,
                        'post_type'   => wc_get_order_types(),
                        'post_status' => array('wc-pending','wc-processing', 'wc-completed','wc-on-hold'), //,wc-cancelled,wc-refunded,wc-failed
                        'numberposts' => -1
                    ) );
					
					if (count($customer_orders) > 0)  {
						return TRUE;
					}						
                }
				// Get order from billing emails in Checkout page coupon validate.
				$billing_email = empty($_POST['billing_email']) ? '' : sanitize_email($_POST['billing_email']);
				if (!empty($billing_email)) {
                    // Get guest order order list
                    $customer_orders = get_posts(array(
                        'meta_key' => '_billing_email',
                        'meta_value' => $billing_email,
                        'post_type' => wc_get_order_types(),
                        'post_status' => array('wc-pending', 'wc-processing', 'wc-completed', 'wc-on-hold'), //,wc-cancelled,wc-refunded,wc-failed
                        'numberposts' => -1
                    ));
					if (count($customer_orders) > 0)  {
						return TRUE;
					}
                }

            }
            catch(Exception $e)
            { }
			
			return $is_order_exists;
        }
		
        public function include_files()
        {
            try
            {
                include(sprintf("%s/includes/grwoo-http-request-handler.php", GR_PLUGIN_BASE_PATH));
                include(sprintf("%s/includes/grwoo-functions.php", GR_PLUGIN_BASE_PATH));
                include(sprintf("%s/includes/grwoo-api.php", GR_PLUGIN_BASE_PATH));
            } catch (Exception $ex) {

            }
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function commenthook_init()
        {
            try
            {
                // Set up the settings for this plugin
                add_action('comment_post', array(&$this, 'send_comment_to_appsmav'));
                add_action('wp', array(&$this, 'init_page_load'));
            } catch (Exception $ex) {

            }
            // Possibly do additional admin_init tasks
        }// END public static function activate
    }// END class GR_Connect
} // END if(!class_exists('GR_Connect'))

if(class_exists('GR_Connect'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('GR_Connect', 'activate'));
    register_deactivation_hook(__FILE__, array('GR_Connect', 'deactivate'));

    // instantiate the plugin class
    $gr_connect = new GR_Connect();

    // Add the settings link to the plugins page
    function gr_plugin_settings_link($links)
    {
        try {
            $settings_link = '<a href="options-general.php?page=grconnect">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        } catch (Exception $ex) {

        }
    }

    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'gr_plugin_settings_link');
    add_shortcode('gr-campaign', array('GR_Connect', 'gr_woo_app_show_func'));
    add_shortcode('gr-points-balance', array('GR_Connect', 'gr_woo_points_balance'));

    $gr_connect->include_files();

    global $pagenow;

    if($pagenow == 'plugins.php')
    {
        if(function_exists('grwoo_woocommerce_active') && !grwoo_woocommerce_active())
            add_action('admin_notices', 'grwoo_plugin_inactive_notice');
        else if(function_exists('wc_coupons_enabled') && !wc_coupons_enabled())
            add_action('admin_notices', 'grwoo_coupon_disabled_notice');
    }
}
