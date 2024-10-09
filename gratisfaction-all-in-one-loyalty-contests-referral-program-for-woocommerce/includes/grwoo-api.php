<?php

if( ! defined('ABSPATH'))
    exit;

class Grwoo_API extends WP_REST_Controller
{
    public function register_apis()
    {
        register_rest_route('grwoo/v1', '/setSettings', array(
            array(
                'methods'               =>  WP_REST_Server::EDITABLE,
                'callback'              =>  array($this, 'set_settings'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));

        register_rest_route('grwoo/v1', '/getuserpoints', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getuserpoints'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/redeemuserpoints', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'redeemuserpoints'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/cancelRedeemedCoupon', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'cancelRedeemedCoupon'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getuserroles', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getuserroles'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getproductcategories', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getproductcategories'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getorderdetails', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getorderdetails'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getorders', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getorders'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getOrdersByDateRange', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getOrdersByDateRange'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getproductdetails', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getproductdetails'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getcustomerorders', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getcustomerorders'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getversion', array(
            'methods' => 'POST',
            'callback'=>  array($this, 'getversion'),
            'permission_callback'   =>  array($this, 'check_api_permission'),
            'args'                  =>  array()
        ));

        register_rest_route('grwoo/v1', '/getPage', array(
            array(
                'methods'               =>  WP_REST_Server::READABLE,
                'callback'              =>  array($this, 'get_page'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/addPage', array(
            array(
                'methods'               =>  WP_REST_Server::CREATABLE,
                'callback'              =>  array($this, 'add_page'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/editPage', array(
            array(
                'methods'               =>  WP_REST_Server::EDITABLE,
                'callback'              =>  array($this, 'edit_page'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/deletePage', array(
            array(
                'methods'               =>  WP_REST_Server::EDITABLE,
                'callback'              =>  array($this, 'delete_page'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
            register_rest_route('grwoo/v1', '/verifyUser', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'verify_user'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/verifyReviewEnabled', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'verify_review_enabled'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/verifyCouponCode', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'verify_coupon_code'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/updateCouponCode', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'update_coupon_code'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/deleteCouponCode', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'delete_coupon_code'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/resetInstallation', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'reset_installation'),
                'permission_callback'   =>  array($this, 'check_api_permission_lite'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/createCouponGR', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'gr_create_coupon'),
                'permission_callback'   =>  array($this, 'check_api_permission'),
                'args'                  =>  array()
            )
        ));
        register_rest_route('grwoo/v1', '/verifyRestApiType', array(
            array(
                'methods'               =>  'POST',
                'callback'              =>  array($this, 'verify_rest_api_type'),
                'permission_callback'   =>  '__return_true',
                'args'                  =>  array()
            )
        ));

        register_rest_route('grwoo/v1', '/createcustomer', array(
            'methods'                   =>  'POST',
            'callback'                  =>  array($this, 'createcustomer'),
            'permission_callback'       =>  array($this, 'check_api_permission'),
            'args'                      =>  array()
        ));
    }

    public function getversion($request)
    {
        try {
            $version = '';
            if (class_exists('GR_Connect')) {
                $version = GR_Connect::$_plugin_version;
            }

            $data = array('error' => 0, 'plugin_version' => $version);
        } catch (Exception $e) {
            $data['error'] = 1;
            $data['msg'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function check_api_permission($request)
    {
        $msg = '';
        try
        {
            if (empty($_POST['payload'])) {
                throw new Exception('Error: ');
            }

            $payload = get_option('grconnect_payload', 0);
            $post_payload = sanitize_text_field($_POST['payload']);

            if ($payload != $post_payload) {
                throw new Exception('Warning: ');
            }

            return true;
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }

        return new WP_Error(
            'gr_rest_forbidden',
            __( $msg . 'Sorry, you are not allowed! '),
            array( 'status' => rest_authorization_required_code() )
        );
    }

    public function check_api_permission_lite($request)
    {
        if (strpos($request->get_header('user_agent'), 'Appsmav') === false) {
            return false;
        }
        return true;
    }

    public function getproductcategories()
    {
        $data = array();
        try
        {
            $cat_args = array(
                'orderby'    => 'name',
                'order'      => 'asc',
                'hide_empty' => false,
            );
            $categories = get_terms( 'product_cat', $cat_args );

            $data = array(
                'error' => 0,
                'product_categories' => !empty($categories) ? $categories : array()
            );
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = "Something went wrong";
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function createcustomer()
    {
        $data = array();
        try
        {
            $email = sanitize_text_field(trim($_POST['email']));
            $user_name = sanitize_text_field(trim($_POST['user_name']));
            $first_name = sanitize_text_field(trim($_POST['first_name']));
            $last_name = sanitize_text_field(trim($_POST['last_name']));

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address");
            }

            if (empty($user_name)) {
                throw new Exception("Invalid user name");
            }

            $user = get_user_by('email', $email);
            if (!empty($user)) {
                throw new Exception("Email id already exists");
            }

            $user = get_user_by('login', $user_name);
            if (!empty($user)) {
                $user_name = $email;
                $user = get_user_by('login', $user_name);
                if (!empty($user)) {
                    throw new Exception("Username already exists");
                }
            }

            $user_details = array(
                'user_email' => $email,
                'user_login' => $user_name,
                'first_name' => $first_name,
                'last_name' => $last_name
            );

            $user_id = wp_insert_user($user_details);
            if (is_wp_error($user_id)) {
                throw new Exception($user_id->get_error_message());
            }

            $user = get_user_by('id', $user_id);
            if (!empty($user) && !empty($user->data) && !empty($user->data->user_email) && $user->data->user_email == $email) {
                $data = array(
                    'error' => 0,
                    'id' => $user_id,
                );
            } else {
                throw new Exception("User creation failed");
            }
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);

        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getorderdetails()
    {
        $data = array();
        try
        {
            $order_id = sanitize_text_field($_POST['order_id']);
            $order = new WC_Order($order_id);
            if (empty($order)) {
                throw new Exception("Order not found");
            }

            $customer = new WC_Customer($order->get_customer_id());

            if (version_compare( WC_VERSION, '3.7', '<' ))
                $couponsArr = $order->get_used_coupons();
            else
                $couponsArr = $order->get_coupon_codes();

            $coupons = [];
            if(!empty($couponsArr))
            {
                $coupons_data = $order->get_items('coupon');

                if(!empty($coupons_data))
                {
                    foreach($coupons_data as $item_data)
                    {
                        $coupons[] = [
                            'code' => $item_data['code'],
                            'discount_tax' => $item_data['discount_tax'],
                            'discount' => $item_data['discount']
                        ];
                    }
                }
            }

            $total    = $order->get_total();
            $subtotal = $order->get_subtotal();
            $tax      = $order->get_total_tax();
            $discount = $order->get_total_discount();
            $shipping = $order->get_shipping_total();
            $shipping_tax = $order->get_shipping_tax();

            // Currency conversion logic starts here
            $curShop = get_option('woocommerce_currency', 'USD');
            if(version_compare( WC_VERSION, '3.0', '<' ))
                $curOrder = $order->get_order_currency();
            else
                $curOrder = $order->get_currency();

            $ratio = 1;
            if ($curOrder != $curShop) {
                try {

                    $prodArr = $order->get_items();

                    foreach ($prodArr as $prod) {
                        // Product amount from order data - this will come with order currency
                        $subtotalRatio = $prod['subtotal'] / $prod['quantity'];

                        // Product amount from meta data - actual product price in base currency
                        if (!empty($prod['variation_id'])) {
                            $product = new WC_Product_Variation($prod['variation_id']);
                            $p_price = get_post_meta($prod['variation_id'], '_price', true);
                        } else {
                            $product = new WC_Product($prod['product_id']);
                            $p_price = get_post_meta($prod['product_id'], '_price', true);
                        }

                        if (wc_prices_include_tax())
                            $p_price = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $p_price));

                        $ratio = $subtotalRatio / $p_price;

                        break;
                    }
                } catch (Exception $e) {

                }
            }

            $ordered_user = $order->get_user();

            $data = array(
                'error' => 0,
                'order' => $order->get_data(),
                'currency' => $curShop,
                'total' => $total,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'shipping' => $shipping,
                'shipping_tax' => $shipping_tax,
                'refund' => $order->get_total_refunded(),
                'roles' => empty($ordered_user) ? [] : $ordered_user->roles,
                'coupons' => $coupons,
                'ratio' => $ratio,
                'email' => $customer->get_email(),
                'name' => $customer->get_first_name() . ' ' . $customer->get_last_name()
            );

            $param['line_items'] = [];
            foreach ($order->get_items('line_item') as $key => $item) {
                $param['line_items'][] = $item->get_data();
            }

            $refundData = array();
            foreach ($order->get_refunds() as $key => $refund) {
                $refundData[$key]['refund'] = $refund->get_data();
                foreach ($refund->get_items() as $item_id => $item) {
                    $refundData[$key]['line_items'][$item_id] = $item->get_data();
                }
            }
            $data['order']['line_items'] = $param['line_items'];
            $data['order']['refund_data'] = $refundData;
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getproductdetails()
    {
        $data = array();
        try
        {
            $products = array();

            $product_ids1 = sanitize_text_field($_POST['product_ids']);
            $product_ids = explode(',', $product_ids1);

            foreach($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $products[$product_id] = $product->get_name();
                }
            }

            $data = array(
                'error' => 0,
                'products' => $products
            );
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getcustomerorders()
    {
        $data = array();
        try
        {
            $email = sanitize_text_field($_POST['email']);
            $user = get_user_by( 'email', $email);

            // Get logged in user's order list
            $customer_orders = get_posts( array(
                'meta_key'    => '_customer_user',
                'meta_value'  => $user->id,
                'post_type'   => wc_get_order_types(),
                'post_status' => array_keys( wc_get_order_statuses() ),
                'numberposts' => -1
            ));

            $args = array(
                'limit' => 1000,
                'customer' => $user->id
            );

            $customer_orders = wc_get_orders( $args );
            foreach ( $customer_orders as $order ) {
                $orders[] = array(
                    'id_order' => $order->get_id(),
                    'amount' => $order->get_total(),
                    'discount' => $order->get_total_discount(),
                    'order_number' => $order->get_id(),
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'currency' => $order->get_currency(),
                    'coupon' => $order->get_used_coupons(),
                    'status' => $order->get_status()
                );
            }

            $data = array(
                'error' => 0,
                'order' => $orders
            );
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function redeemuserpoints($request) {
        try {

            // Validate the shop details
            $id_site = get_option('grconnect_appid', '');
            $payload = get_option('grconnect_payload', '');
            if (empty($id_site) || empty($payload))
                throw new Exception('Invalid Request');

            $email = sanitize_email($_POST['email']);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new Exception("Please enter valid email");

            $points = sanitize_text_field($_POST['points']);
            if (empty($points))
                throw new Exception("Please enter valid points");

            $urlApi = GR_Connect::$_callback_url . 'widgets/redeemPoints?request_points=' . $points . '&callback=api&acidMav=' . $id_site . '&uniqueID=&app=wp&grcli=&gremc=' . $email . '&grcln=&grfname=&grlname=&payload=' . $payload;

            $httpObj = (new HttpRequestHandler)
                    ->setTimeout(10)
                    ->exec($urlApi);
            $response = $httpObj->getResponse();

            if (empty($response)) {
                throw new Exception('Invalid Response');
            }

            $data = json_decode($response, true);
        } catch (Exception $e) {
            $data['error'] = 1;
            $data['msg'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function cancelRedeemedCoupon($request) {
        try {

            // Validate the shop details
            $id_site = get_option('grconnect_appid', '');
            $payload = get_option('grconnect_payload', '');
            if (empty($id_site) || empty($payload))
                throw new Exception('Invalid Request');

            $email = sanitize_email($_POST['email']);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new Exception("Please enter valid email");

            $coupon = sanitize_text_field($_POST['coupon']);
            if (empty($coupon))
                throw new Exception("Please enter valid coupon");

            $urlApi = GR_Connect::$_callback_url . 'widgets/cancelRedeemedCoupon?coupon=' . $coupon . '&callback=api&acidMav=' . $id_site . '&uniqueID=&app=wp&grcli=&gremc=' . $email . '&grcln=&grfname=&grlname=&payload=' . $payload;

            $httpObj = (new HttpRequestHandler)
                    ->setTimeout(10)
                    ->exec($urlApi);
            $response = $httpObj->getResponse();

            if (empty($response)) {
                throw new Exception('Invalid Response');
            }

            $data = json_decode($response, true);
        } catch (Exception $e) {
            $data['error'] = 1;
            $data['msg'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getuserpoints($request) {
        try {

            // Validate the shop details
            $id_site = get_option('grconnect_appid', '');
            $payload = get_option('grconnect_payload', '');
            if (empty($id_site) || empty($payload))
                throw new Exception('Invalid Request');

            $email = sanitize_email($_POST['email']);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new Exception("Please enter valid email");

            $params = [];
            $params['id_site'] = $id_site;
            $params['payload'] = $payload;
            $params['email'] = $email;

            $urlApi = GR_Connect::$_callback_url . 'services/v2/user/points';
            $httpObj = (new HttpRequestHandler)
                    ->setTimeout(10)
                    ->setPostData($params)
                    ->exec($urlApi);
            $response = $httpObj->getResponse();

            if (empty($response)) {
                throw new Exception('Invalid Response');
            }

            $data = json_decode($response, true);
        } catch (Exception $e) {
            $data['error'] = 1;
            $data['msg'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getuserroles($request)
    {
        try
        {
            global $wp_roles;

            $user_roles = $wp_roles->get_names();
            $data = array(
                'error' => 0,
                'user_roles' => !empty($user_roles) ? $user_roles : array()
            );
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = "Something went wrong";
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function set_settings($request)
    {
        $data   =   array('error' => 0);
        $data['review_enabled'] = (get_option('woocommerce_enable_reviews', 0) === 'yes') ? 'yes' : 'no';

        try
        {
            if(empty($_POST['data']))
                throw new Exception('No config to set');

            if(empty($_POST['data']) || !is_array($_POST['data']))
                throw new Exception('Invalid config to set');

            $config         =	$_POST['data'];
            $app_config     =   gr_get_app_config();

            if(!empty($app_config) && is_array($app_config))
                $config     =   array_merge($app_config, $config);

            $config['date_updated'] =   time();

            if(gr_set_app_config($config) == FALSE)
                throw new Exception(__('Config file is not created'));

            //$data['config'] =   $config;
            $data['msg']    =   __('Settings updated successfully');
        }
        catch(Exception $e)
        {
            $data['error']  =   1;
            $data['msg']    =   $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function get_page($request)
    {
        $data = array('error' => 0);

        try
        {
            if (empty($_POST['id'])) {
                throw new Exception('Invalid Page');
            }

            $id_post = sanitize_text_field($_POST['id']);
            if (!get_post_status($id_post)) {
                throw new Exception('Invalid Page');
            }

            $page = get_post($id_post);
            if(is_wp_error($page)) {
                throw new Exception('cannot_update_page'. $page->get_error_message());
            }

            $data['error']	= 0;
            $data['id'] 	= $page->ID;
            $data['url'] 	= get_permalink($id);
            $data['is_embed_landing_url'] = get_post_meta(get_the_ID(), 'is_embed_landing_url');
            $data['msg']	= 'Success';
        }
        catch(Exception $e)
        {
            $data['error']          =   1;
            $data['error_message']  =   $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function add_page($request)
    {
        $data   =   array('error' => 0);

        try
        {
            if (empty($_POST['title'])) {
                throw new Exception('Invalid Title');
            }

            if (empty($_POST['content'])) {
                throw new Exception('Invalid Content');
            }

            $new_page = array(
                'post_title'   => sanitize_text_field($_POST['title']),
                'post_content' => sanitize_text_field($_POST['content']),
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'meta_input'   => array(
                    'is_embed_landing_url' => 1
                )
            );

            $id = wp_insert_post( $new_page, $wp_error = false );

            if(is_wp_error($id)) {
                throw new Exception('cannot_create_page'. $id->get_error_message());
            }

            $data['error'] = 0;
            $data['id']    = $id;
            $data['url']   = get_permalink($id);
            $data['msg']   = 'Success';
        }
        catch(Exception $e)
        {
            $data['error']         = 1;
            $data['error_message'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function edit_page($request)
    {
        $data = array('error' => 0);

        try
        {
            if (isset($_POST['title']) && empty($_POST['title']) && !isset($_POST['publish'])) {
                throw new Exception('Invalid Title');
            }

            if (empty($_POST['id'])) {
                throw new Exception('Invalid Page');
            }

            $params['ID'] = sanitize_text_field($_POST['id']);
            if (!get_post_status($params['ID'])) {
                throw new Exception('Invalid Page');
            }

            if (isset($_POST['publish']))
            {
                $publish_status = sanitize_text_field($_POST['publish']);
                $params['post_status'] = ($publish_status == 1) ? 'publish' : 'draft';
                update_post_meta($params['ID'], 'is_embed_landing_url', $publish_status);
            }
            else
            {
                $params['post_title'] = sanitize_text_field($_POST['title']);
            }

            $id = wp_update_post( $params, $wp_error = true );

            if(is_wp_error($id))
                throw new Exception('cannot_update_page'. $id->get_error_message());

            $page_info = get_post($id);

            $data['error'] = 0;
            $data['id']    = $page_info->ID;
            $data['title'] = $page_info->post_title;
            $data['url']   = get_permalink($page_info->ID);
            $data['msg']   = 'Success';
        }
        catch(Exception $e)
        {
            $data['error']         = 1;
            $data['error_message'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function delete_page($request)
    {
        $data   =   array('error' => 0);

        try
        {
            if (empty($_POST['id'])) {
                throw new Exception('Invalid Page');
            }

            $id_page = sanitize_text_field($_POST['id']);
            if (!get_post_status($id_page)) {
                throw new Exception('Invalid Page');
            }

            if(!wp_delete_post($id_page, true)) {
                throw new Exception('cannot_delete_page');
            }

            $data['error'] = 0;
            $data['msg']   = 'Success';
        }
        catch(Exception $e)
        {
            $data['error']         = 1;
            $data['error_message'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function verify_user($request)
    {
        $data['error'] = 1;
        $data['msg']   = 'No User Exist';

        try
        {
            if (empty($_POST['verify_user'])) {
                throw new Exception('Invalid Email');
            }

            if (class_exists('GR_Connect')) {
                $data['plugin_version'] = GR_Connect::$_plugin_version;
            }

            $email = sanitize_email( $_POST['verify_user'] );
            $user = get_user_by('email', $email);

            if (!empty($user))
            {
                $data['error'] = 0;
                $data['msg'] = 'User Exist';
                $data['name'] = $user->first_name . ' ' . $user->last_name;
                $data['id'] = $user->ID;
                $data['customer_group'] = $user->roles;
            }

        }
        catch(Exception $e)
        {
            $data['error']         = 1;
            $data['error_message'] = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function verify_review_enabled($request)
    {
        try
        {
            $data['error'] = 0;
            $data['msg']   = get_option('woocommerce_enable_reviews', 'no');
            if (class_exists('GR_Connect')) {
                $data['plugin_version'] = GR_Connect::$_plugin_version;
            }
        }
        catch(Exception $e) {
            $data['error'] = 1;
            $data['msg']   = 'Invalid';
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function verify_coupon_code($request)
    {
        try
        {
            $data['error'] = 0;

            if (empty($_POST['coupon_code'])) {
                throw new Exception('Coupon code cannot be empty. Please check');
            }

            $coupon_code = sanitize_text_field($_POST['coupon_code']);

            $coupon = new WC_Coupon($coupon_code);
            if (!empty($coupon->id))
            {
                $data['msg'] = 'Yes';
                $data['coupon'] = json_decode($coupon, true);
            }
            else
                $data['msg'] = 'No';
        }
        catch(Exception $e) {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function update_coupon_code($request)
    {
        try
        {
            $data['error'] = 0;

            if (empty($_POST['old_coupon_code'])) {
                throw new Exception('Coupon code cannot be empty. Please check');
            }
            if (empty($_POST['new_coupon_code'])) {
                throw new Exception('Coupon code cannot be empty. Please enter a unique coupon code.');
            }
            $old_coupon_code = sanitize_text_field($_POST['old_coupon_code']);
            $new_coupon_code = sanitize_text_field($_POST['new_coupon_code']);

            $coupon = new WC_Coupon($new_coupon_code);
            if (!empty($coupon->id))
                throw new Exception('Coupon code already exists. Please check and enter a new unique coupon code');

            $coupon = new WC_Coupon($old_coupon_code);
            if (empty($coupon->id))
                throw new Exception('Coupon code not found. Please check and try again');

            // Update coupon details starts
            $my_post = array(
                'ID'         => $coupon->id,
                'post_title' => $new_coupon_code
            );

            $post_id = wp_update_post( $my_post );
            if ( is_wp_error( $post_id ) ) {
                throw new Exception( $post_id->get_error_message());
            }

            $data['msg']   = 'Successfully updated';

        }
        catch(Exception $e) {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function delete_coupon_code($request)
    {
        try
        {
            $data['error'] = 0;

            if (empty($_POST['coupon_code'])) {
                throw new Exception('Invalid coupon code');
            }

            $coupon_code = sanitize_text_field($_POST['coupon_code']);
            $coupon = new WC_Coupon($coupon_code);
            if (!empty($coupon->id))
            {
                $validate_usage = empty($_POST['validate_usage']) ? 0 : $_POST['validate_usage'];
                if(!empty($validate_usage) && (!isset($coupon->usage_count) || $coupon->usage_count != 0))
                {
                    $data['id'] = $coupon->id;
                    $data['usage_count'] = $coupon->usage_count;
                    throw new Exception('Coupon code already used');
                }

                $post_id = wp_delete_post($coupon->id, TRUE);
                if ( is_wp_error( $post_id ) ) {
                    throw new Exception( $post_id->get_error_message());
                }

                $data['msg'] = 'Successfully Deleted';
            }
            else {
                $data['msg'] = 'Coupon code not found.';
            }

        }
        catch(Exception $e) {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function reset_installation($request)
    {
        try
        {
            $data['error'] = 0;

            // Reset flags to show login screen
            update_option('grconnect_register', 3);

            $data['msg'] = 'yes';
        }
        catch(Exception $e) {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getorders() {
        $data = array();
        try {
            $orderslist = array();

            $order_ids = sanitize_text_field($_POST['order_ids']);
            $order_ids = explode(',', $order_ids);

            foreach( $order_ids as $order_id ){
                $order = new WC_Order( $order_id );

                if (empty($order->get_id()))
                        continue;

                $customer = new WC_Customer($order->get_customer_id());
                $orderslist[] = array(
                    'id_order' => $order->get_id(),
                    'amount' => $order->get_total(),
                    'discount' => $order->get_total_discount(),
                    'order_number' => $order->get_id(),
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'email' => $customer->get_email(),
                    'billing_email' => $order->get_billing_email(),
                    'currency' => $order->get_currency(),
                    'coupon' => $order->get_used_coupons(),
                    'date_created' => $order->get_date_created()->format('c'),
                    'status' => $order->get_status()
                );
            }

            if (empty($orderslist)) {
                throw new Exception('Invalid order.');
            }

            $data = array(
                'error' => 0,
                'orders' => $orderslist
            );
        } catch (Exception $e) {
            $data['error'] = 1;
            $data['msg'] = $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function getOrdersByDateRange()
    {
        $data = array();
        try
        {
            $orderslist = array();

            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = sanitize_text_field($_POST['end_date']);
            $order_status = sanitize_text_field($_POST['order_status']);

            if (!empty($order_status)) {
                $order_status = explode(',', $order_status);
            } else {
                $order_status = array( 'wc-completed','wc-processing' );
            }

            $orders = wc_get_orders(array(
                    'limit' => -1,
                    'type' => 'shop_order',
                    'status' => $order_status,
                    'date_created' => $start_date .'...'. $end_date
                )
            );

            foreach($orders as $order) {
                $customer = new WC_Customer($order->get_customer_id());
                $orderslist[] = array(
                    'id_order' => $order->get_id(),
                    'amount' => $order->get_total(),
                    'discount' => $order->get_total_discount(),
                    'order_number' => $order->get_id(),
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'email' => $customer->get_email(),
                    'billing_email' => $order->get_billing_email(),
                    'currency' => $order->get_currency(),
                    'coupon' => $order->get_used_coupons(),
                    'date_created' => $order->get_date_created()->format('c'),
                    'status' => $order->get_status()
                );
            }

            $data = array(
                'error' => 0,
                'order_status' => $order_status,
                'orders' => $orderslist
            );
        }
        catch(Exception $e)
        {
            $data['error'] = 1;
            $data['msg']   = $e->getMessage();
        }

        $data['plugin_version'] = GR_Connect::$_plugin_version;

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function gr_create_coupon($request)
    {
        try
        {
            global $wp_rest_server;
            global $wpdb;

            if(is_admin())
                throw new Exception('Admin user');

            if( !has_action( 'rest_api_init' ) ) {
                $wp_rest_server = new WP_REST_Server();
                do_action('rest_api_init', $wp_rest_server);
            }

            add_filter('wpss_misc_form_spam_check_bypass', FALSE, 10);

            if(empty($_POST['cpn_type']) || empty($_POST['grcpn_code']))
                throw new Exception('InvalidRequest1');

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

            $url = GR_Connect::$_callback_url . GR_Connect::$_api_version . 'wooCpnValidate';

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
                'plugin_version' => GR_Connect::$_plugin_version
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

            $data['error'] = 0;
            $data['code'] = $coupon_code;
            $data['id'] = $id;
            $data['msg'] = 'Success';
        }
        catch(Exception $ex)
        {
            $data['error'] = 1;
            $data['msg'] = $ex->getMessage();
        }

        $result = new WP_REST_Response($data, 200);
        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }

    public function verify_rest_api_type($request)
    {
        $data = array('error' => 0, 'msg' => 'SUCCESS');
        $result = new WP_REST_Response($data, 200);

        // Set headers.
        $result->set_headers(array('Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'));
        return $result;
    }
}
