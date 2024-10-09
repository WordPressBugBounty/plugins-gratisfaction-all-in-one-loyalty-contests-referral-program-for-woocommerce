<?php
/**
 * Gratisfaction
 *
 * @package     Gratisfaction/Classes
 * @author      AppsMav
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class GR_WOO_Discount {
	/**
	 * Add coupon-related filters to help generate the custom coupon
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		$this->hooks( 'add' );
	}

	/**
	 * Add or remove callbacks to/from the hooks.
	 *
	 * @since 1.6.5
	 * @version 1.6.5
	 *
	 * @param string $verb What operation to perform (either 'add' or 'remove').
	 */
	protected function hooks( $verb ) {
            $filters = array(
                    array( 'woocommerce_get_shop_coupon_data', array( $this, 'get_discount_data' ), 10, 2 ),
                    array( 'woocommerce_coupon_message', array( $this, 'get_discount_applied_message' ), 10, 3 ),
                    array( 'woocommerce_coupon_get_discount_amount', array( $this, 'get_discount_amount' ), 10, 5 ),
            );

            $func = 'add' === $verb ? 'add_filter' : 'remove_filter';
            foreach ( $filters as $filter ) {
                    call_user_func_array( $func, $filter );
            }
	}

	public function get_discount_data( $data, $code ) {
		if ( strtolower( $code ) != $this->get_discount_code() ) {
			return $data;
		}

		// note: we make our points discount "greedy" so as many points as possible are
		//   applied to the order.  However we also want to play nice with other discounts
		//   so if another coupon is applied we want to use less points than otherwise.
		//   The solution is to make this discount apply post-tax so that both pre-tax
		//   and post-tax discounts can be considered.  At the same time we use the cart
		//   subtotal excluding tax to calculate the maximum points discount, so it
		//   functions like a pre-tax discount in that sense.
		$data = array(
			'id'                         => true,
			'type'                       => 'fixed_cart',
			'amount'                     => 0,
			'coupon_amount'              => 0, // 2.2
			'individual_use'             => false,
			'usage_limit'                => '',
			'usage_count'                => '',
			'expiry_date'                => '',
			'apply_before_tax'           => true,
			'free_shipping'              => false,
			'product_categories'         => array(),
			'exclude_product_categories' => array(),
			'exclude_sale_items'         => false,
			'minimum_amount'             => '',
			'maximum_amount'             => '',
			'customer_email'             => '',
		);

		return $data;
	}

	public function get_discount_total_from_existing_coupons() {
            $coupons = WC()->cart->get_coupons();

            $total_discount = 0;
            foreach ( WC()->cart->get_cart() as $item ) {
                $total_discount += $this->get_cart_item_discount_total( $item );
            }

            return $total_discount;
	}

	public function get_cart_item_discount_total( $item ) {
		// Since we call get_discount_amount this could potentially result in
		// a loop.
		$this->hooks( 'remove' );

		$discount = 0;
		foreach ( WC()->cart->get_coupons() as $coupon ) {
			if ( strtolower( $coupon->get_code() ) === $this->get_discount_code() || ! $coupon->is_type( 'fixed_product' ) ) {
				continue;
			}

			if ( ! $coupon->is_valid() ) {
				continue;
			}

			if ( $coupon->is_valid_for_product( $item['data'], $item ) || $coupon->is_valid_for_cart() ) {
				$discount += (float) $coupon->get_discount_amount( $item['data']->get_price(), $item, true ) * $item['quantity'];
			}
		}

		// Add the hooks back.
		$this->hooks( 'add' );

		return $discount;
	}

	public function get_discount_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ) {
            if ( strtolower( $coupon->get_code() ) != $this->get_discount_code() ) {
                return $discount;
            }

            $existing_discount_amounts = $this->get_discount_total_from_existing_coupons();

            $discount_percent = 0;
            $cart_item_qty    = $cart_item['quantity'];
            $cart_item_data   = $cart_item['data'];

            if ( wc_prices_include_tax() ) {
                    $sub_total_inc_tax = WC()->cart->subtotal - $existing_discount_amounts;

                    $discount_percent = (
                            wc_get_price_including_tax( $cart_item_data ) * $cart_item_qty - $this->get_cart_item_discount_total( $cart_item )
                    ) / $sub_total_inc_tax;
            } else {
                    $sub_total_ex_tax = WC()->cart->subtotal_ex_tax - $existing_discount_amounts;

                    $discount_percent = (
                            wc_get_price_excluding_tax( $cart_item_data ) * $cart_item_qty - $this->get_cart_item_discount_total( $cart_item )
                    ) / $sub_total_ex_tax;
            }

            $total_discount                 = 10;
            $total_with_discount_percent    = (float) $total_discount * $discount_percent;

            if ( version_compare( WC_VERSION, '3.2.0', '<' ) ) {
                    $total_with_discount_percent = $total_with_discount_percent / $cart_item['quantity'];
            }

            $total_discount = round( min( $total_with_discount_percent, $discounting_amount ) );

            return $total_discount;
	}

	/**
	 * Change the "Coupon applied successfully" message
	 */
	public function get_discount_applied_message( $message, $message_code, $coupon ) {
            if ( WC_Coupon::WC_COUPON_SUCCESS === $message_code && $coupon->get_code() === $this->get_discount_code() ) {
                return __( 'TODO:Discount Applied Successfully', 'gratisfaction');
            } else {
                return $message;
            }
	}

	/**
	 * Generates a unique discount code
	 */
	public static function generate_discount_code() {
            
            $discount_code = sprintf( 'grwoo_points_redemption_%s_%s', get_current_user_id(), current_time('timestamp'));

            WC()->session->set( 'gratisfaction_discount_code', $discount_code );

            return $discount_code;
	}

	public static function get_discount_code() {
		if ( WC()->session !== null ) {
			return WC()->session->get( 'gratisfaction_discount_code' );
		}
	}
}